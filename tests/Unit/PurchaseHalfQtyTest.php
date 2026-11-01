<?php

namespace Tests\Unit;

use App\Models\Inventory\Item;
use App\Models\Inventory\Supplier;
use App\Models\Inventory\Warehouse;
use App\Models\Master\Fleet;
use App\Models\Purchasing\Purchase;
use App\Models\Purchasing\PurchaseDetail;
use App\Models\StockTransaction;
use App\Models\User;
use App\Models\Warehouse\Maintenance;
use App\Models\Warehouse\MaintenanceDetail;
use App\Models\Warehouse\MaintenanceFifo;
use App\Services\Warehouse\MaintenanceService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

class PurchaseHalfQtyTest extends TestCase
{
    use DatabaseTransactions;

    public function test_qty_setengah_tersimpan_dan_terpakai_habis(): void
    {
        $this->actingAs(User::query()->firstOrFail());

        $supplier = Supplier::create(['code' => 'SUP-TEST-HALF', 'name' => 'Supplier Test']);
        $warehouse = Warehouse::create(['code' => 'WH-TEST-HALF', 'name' => 'Gudang Test']);
        $fleet = Fleet::create(['code' => 'FLT-TEST-HALF', 'plateNumber' => 'B 0000 HALF']);
        $item = Item::create([
            'code' => 'ITM-TEST-HALF',
            'name' => 'Kampas Rem Test',
            'warehouseCode' => $warehouse->code,
            'type' => Item::TYPE_PART,
            'price' => 100000,
        ]);

        $purchase = Purchase::create([
            'code' => 'PO-TEST-HALF',
            'date' => '2026-11-01',
            'time' => '08:00:00',
            'supplierCode' => $supplier->code,
            'warehouseCode' => $warehouse->code,
            'status' => 1,
        ]);

        $detail = PurchaseDetail::create([
            'code' => 'FPD-TEST-HALF',
            'purchaseCode' => $purchase->code,
            'itemCode' => $item->code,
            'qty' => 0.5,
            'receivedQty' => 0.5,
            'status' => 1,
            'price' => 100000,
        ]);

        // Nilai pecahan tidak boleh dibulatkan menjadi 1 saat disimpan.
        $this->assertSame('0.5', $detail->fresh()->receivedQty);

        StockTransaction::create([
            'code' => 'FST-TEST-HALF',
            'date' => '2026-11-01',
            'itemCode' => $item->code,
            'warehouseCode' => $warehouse->code,
            'qtyIn' => 0.5,
            'qtyOut' => 0,
            'transactionCode' => $purchase->code,
            'transactionDetailCode' => $detail->code,
            'transactionType' => 'IN',
        ]);

        $request = new Request([
            'date' => '2026-11-01',
            'time' => '09:00:00',
            'fleetCode' => $fleet->code,
            'warehouseCode' => $warehouse->code,
            'itemCode' => [$item->code],
            'qty' => [0.5],
            'purchase_ids' => [$purchase->id],
        ]);

        app(MaintenanceService::class)->store($request, 'Maintenance');

        $maintenanceDetail = MaintenanceDetail::query()
            ->where('itemCode', $item->code)
            ->firstOrFail();

        $this->assertSame('0.5', $maintenanceDetail->qty);

        $this->assertDatabaseHas('maintenance_fifo', [
            'purchaseDetailCode' => $detail->code,
            'qty' => 0.5,
        ]);

        $detail->refresh();

        $this->assertSame('0.5', $detail->qtyUsed);
        $this->assertEqualsWithDelta(
            0,
            (float) $detail->receivedQty - (float) $detail->qtyUsed,
            0.0001,
            'Sisa qty PO harus 0 setelah 0.5 dipakai.'
        );
    }

    public function test_edit_pemakaian_qty_setengah_tidak_membuat_sisa_salah(): void
    {
        $this->actingAs(User::query()->firstOrFail());

        $supplier = Supplier::create(['code' => 'SUP-TEST-HALF-2', 'name' => 'Supplier Test 2']);
        $warehouse = Warehouse::create(['code' => 'WH-TEST-HALF-2', 'name' => 'Gudang Test 2']);
        $fleet = Fleet::create(['code' => 'FLT-TEST-HALF-2', 'plateNumber' => 'B 0001 HALF']);
        $item = Item::create([
            'code' => 'ITM-TEST-HALF-2',
            'name' => 'Kampas Rem Test 2',
            'warehouseCode' => $warehouse->code,
            'type' => Item::TYPE_PART,
            'price' => 100000,
        ]);

        $purchase = Purchase::create([
            'code' => 'PO-TEST-HALF-2',
            'date' => '2026-11-01',
            'time' => '08:00:00',
            'supplierCode' => $supplier->code,
            'warehouseCode' => $warehouse->code,
            'status' => 1,
        ]);

        $detail = PurchaseDetail::create([
            'code' => 'FPD-TEST-HALF-2',
            'purchaseCode' => $purchase->code,
            'itemCode' => $item->code,
            'qty' => 1.5,
            'receivedQty' => 1.5,
            'status' => 1,
            'price' => 100000,
        ]);

        StockTransaction::create([
            'code' => 'FST-TEST-HALF-2',
            'date' => '2026-11-01',
            'itemCode' => $item->code,
            'warehouseCode' => $warehouse->code,
            'qtyIn' => 1.5,
            'qtyOut' => 0,
            'transactionCode' => $purchase->code,
            'transactionDetailCode' => $detail->code,
            'transactionType' => 'IN',
        ]);

        $service = app(MaintenanceService::class);

        $service->store(new Request([
            'date' => '2026-11-01',
            'time' => '09:00:00',
            'fleetCode' => $fleet->code,
            'warehouseCode' => $warehouse->code,
            'itemCode' => [$item->code],
            'qty' => [0.5],
            'purchase_ids' => [$purchase->id],
        ]), 'Maintenance');

        $maintenanceDetail = MaintenanceDetail::query()->where('itemCode', $item->code)->firstOrFail();
        $maintenance = Maintenance::query()->where('code', $maintenanceDetail->maintenanceCode)->firstOrFail();

        $service->update(new Request([
            'date' => '2026-11-01',
            'time' => '10:00:00',
            'fleetCode' => $fleet->code,
            'itemCode' => [$item->code],
            'qty' => [1.0],
            'original_qty' => [0.5],
            'maintenanceDetailCode' => [$maintenanceDetail->code],
            'purchase_ids' => [$purchase->id],
        ]), $maintenance->id, 'Maintenance');

        $detail->refresh();

        $this->assertSame('1.0', $maintenanceDetail->fresh()->qty);
        $this->assertSame('1.0', $detail->qtyUsed);
        $this->assertEqualsWithDelta(
            1.0,
            (float) MaintenanceFifo::query()
                ->where('purchaseDetailCode', $detail->code)
                ->sum('qty'),
            0.0001,
            'Total FIFO harus 1.0 setelah qty pemakaian diubah dari 0.5 menjadi 1.0.'
        );
        $this->assertEqualsWithDelta(
            0.5,
            (float) $detail->receivedQty - (float) $detail->qtyUsed,
            0.0001,
            'Sisa qty PO harus 0.5.'
        );
    }
}
