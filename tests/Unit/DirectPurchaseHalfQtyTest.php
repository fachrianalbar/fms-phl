<?php

namespace Tests\Unit;

use App\Models\Inventory\Item;
use App\Models\Inventory\Supplier;
use App\Models\Inventory\Warehouse;
use App\Models\Master\Fleet;
use App\Models\Purchasing\PurchaseDetail;
use App\Models\StockTransaction;
use App\Models\User;
use App\Models\Warehouse\MaintenanceDetail;
use App\Models\Warehouse\MaintenanceFifo;
use App\Services\Purchasing\DirectPurchaseService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

class DirectPurchaseHalfQtyTest extends TestCase
{
    use DatabaseTransactions;

    public function test_pembelian_langsung_qty_setengah_tersimpan_utuh(): void
    {
        $this->actingAs(User::query()->firstOrFail());

        $supplier = Supplier::create(['code' => 'SUP-TEST-PL-HALF', 'name' => 'Supplier PL Test']);
        $warehouse = Warehouse::create(['code' => 'WH-TEST-PL-HALF', 'name' => 'Gudang PL Test']);
        $fleet = Fleet::create(['code' => 'FLT-TEST-PL-HALF', 'plateNumber' => 'B 0002 HALF']);
        $item = Item::create([
            'code' => 'ITM-TEST-PL-HALF',
            'name' => 'Kampas Rem PL Test',
            'warehouseCode' => $warehouse->code,
            'type' => Item::TYPE_PART,
            'price' => 100000,
        ]);

        app(DirectPurchaseService::class)->store(new Request([
            'date' => '2026-11-01',
            'time' => '08:00:00',
            'supplierCode' => $supplier->code,
            'warehouseCode' => $warehouse->code,
            'fleetCode' => $fleet->code,
            'paymentType' => 'Tunai',
            'itemCode' => [$item->code],
            'qty' => [0.5],
            'price' => ['100.000'],
            'description' => ['Ganti kampas'],
        ]), 'Pembelian Langsung');

        $detail = PurchaseDetail::query()->where('itemCode', $item->code)->firstOrFail();

        $this->assertSame('0.5', $detail->qty);
        $this->assertSame('0.5', $detail->receivedQty);
        $this->assertSame('0.5', $detail->qtyUsed);
        $this->assertEqualsWithDelta(0, (float) $detail->receivedQty - (float) $detail->qtyUsed, 0.0001);

        // Nominal header harus tetap 0.5 x harga, bukan dibulatkan menjadi 1 x harga.
        $this->assertEqualsWithDelta(50000, (float) $detail->purchase->nominal, 0.0001);

        $maintenanceDetail = MaintenanceDetail::query()->where('itemCode', $item->code)->firstOrFail();
        $this->assertSame('0.5', $maintenanceDetail->qty);

        $this->assertDatabaseHas('maintenance_fifo', [
            'purchaseDetailCode' => $detail->code,
            'qty' => 0.5,
        ]);

        $this->assertEqualsWithDelta(
            0.5,
            (float) StockTransaction::query()
                ->where('itemCode', $item->code)
                ->where('transactionType', 'IN')
                ->sum('qtyIn'),
            0.0001
        );

        $this->assertEqualsWithDelta(
            0.5,
            (float) StockTransaction::query()
                ->where('itemCode', $item->code)
                ->where('transactionType', 'OUT')
                ->sum('qtyOut'),
            0.0001
        );

        $this->assertEqualsWithDelta(
            0.5,
            (float) MaintenanceFifo::query()
                ->where('purchaseDetailCode', $detail->code)
                ->sum('qty'),
            0.0001
        );
    }
}
