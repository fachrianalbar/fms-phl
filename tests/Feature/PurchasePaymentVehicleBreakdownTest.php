<?php

namespace Tests\Feature;

use App\Http\Controllers\Purchasing\PurchasePaymentController;
use App\Models\Inventory\Item;
use App\Models\Inventory\Supplier;
use App\Models\Inventory\Warehouse;
use App\Models\Master\Fleet;
use App\Models\Master\FleetCompany;
use App\Models\Purchasing\Purchase;
use App\Models\Purchasing\PurchaseDetail;
use App\Models\Warehouse\Maintenance;
use App\Models\Warehouse\MaintenanceDetail;
use App\Models\Warehouse\MaintenanceFifo;
use App\Services\Purchasing\PurchasePaymentService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

class PurchasePaymentVehicleBreakdownTest extends TestCase
{
    use DatabaseTransactions;

    public function test_multi_vehicle_breakdown_with_ppn_and_partial_payment(): void
    {
        $warehouse = Warehouse::query()->first();
        $this->assertNotNull($warehouse, 'Warehouse seed required.');

        $supplier = Supplier::create([
            'code' => 'SUP-TEST-VB-01',
            'name' => 'PT Supplier Breakdown Test',
            'ppn' => 11,
            'pph' => 0,
            'payment_term_days' => 30,
        ]);

        $company = FleetCompany::create([
            'code' => 'FC-TEST-VB-01',
            'name' => 'PT PUTRI HOKI LOGISTIK',
            'type' => 'Internal',
        ]);

        $fleetA = Fleet::create([
            'code' => 'FMSF-VB-A',
            'plateNumber' => 'BE 8172 AEU',
            'fleetCompanyCode' => $company->code,
        ]);

        $fleetB = Fleet::create([
            'code' => 'FMSF-VB-B',
            'plateNumber' => 'BE 8307 ADU',
            'fleetCompanyCode' => $company->code,
        ]);

        $item = Item::create([
            'code' => 'ITM-TEST-VB-01',
            'name' => 'Ban Truk 1000-20',
            'price' => 100000,
        ]);

        // 1 PO senilai Rp 1.000.000 (qty 10 x Rp 100.000) + PPN 11% = Rp 1.110.000
        $purchase = Purchase::create([
            'code' => 'PO-TEST-VB-001',
            'supplierCode' => $supplier->code,
            'warehouseCode' => $warehouse->code,
            'date' => '2026-11-10',
            'time' => '09:00',
            'dueDate' => '2026-12-10',
            'subtotal' => 1000000,
            'ppn_rate' => 11,
            'ppn_amount' => 110000,
            'nominal' => 1110000,
            'paidAmount' => 0,
            'paymentStatus' => 'Unpaid',
            'status' => 0,
            'is_direct' => false,
        ]);

        $pd = PurchaseDetail::create([
            'code' => 'FPD-TEST-VB-001',
            'purchaseCode' => $purchase->code,
            'itemCode' => $item->code,
            'qty' => 10,
            'receivedQty' => 10,
            'qtyUsed' => 10,
            'price' => 100000,
            'status' => 1,
        ]);

        // Mobil A pakai 6 unit (60% = Rp 600.000 DPP -> Rp 666.000 incl. PPN)
        $mntA = Maintenance::create([
            'code' => 'MNT-TEST-VB-A',
            'date' => '2026-11-10',
            'time' => '10:00',
            'fleetCode' => $fleetA->code,
            'warehouseCode' => $warehouse->code,
            'status' => 0,
            'grand_total' => 600000,
        ]);
        $mdA = MaintenanceDetail::create([
            'code' => 'FMD-TEST-VB-A',
            'maintenanceCode' => $mntA->code,
            'itemCode' => $item->code,
            'qty' => 6,
            'price' => 100000,
            'total' => 600000,
        ]);
        MaintenanceFifo::create([
            'code' => 'MF-TEST-VB-A',
            'maintenanceDetailCode' => $mdA->code,
            'purchaseDetailCode' => $pd->code,
            'qty' => 6,
        ]);

        // Mobil B pakai 4 unit (40% = Rp 400.000 DPP -> Rp 444.000 incl. PPN)
        $mntB = Maintenance::create([
            'code' => 'MNT-TEST-VB-B',
            'date' => '2026-11-10',
            'time' => '11:00',
            'fleetCode' => $fleetB->code,
            'warehouseCode' => $warehouse->code,
            'status' => 0,
            'grand_total' => 400000,
        ]);
        $mdB = MaintenanceDetail::create([
            'code' => 'FMD-TEST-VB-B',
            'maintenanceCode' => $mntB->code,
            'itemCode' => $item->code,
            'qty' => 4,
            'price' => 100000,
            'total' => 400000,
        ]);
        MaintenanceFifo::create([
            'code' => 'MF-TEST-VB-B',
            'maintenanceDetailCode' => $mdB->code,
            'purchaseDetailCode' => $pd->code,
            'qty' => 4,
        ]);

        $service = app(PurchasePaymentService::class);
        $breakdown = $service->resolveVehicleBreakdown($purchase->fresh('details'));

        $this->assertCount(2, $breakdown);
        $this->assertSame('BE 8172 AEU', $breakdown[0]['plate_number']);
        $this->assertSame(600000, $breakdown[0]['dpp_amount']);
        $this->assertSame(66000, $breakdown[0]['ppn_amount']);
        $this->assertSame(666000, $breakdown[0]['billing_amount']);
        $this->assertSame(0, $breakdown[0]['paid_amount']);
        $this->assertSame(666000, $breakdown[0]['remaining_amount']);

        $this->assertSame('BE 8307 ADU', $breakdown[1]['plate_number']);
        $this->assertSame(400000, $breakdown[1]['dpp_amount']);
        $this->assertSame(44000, $breakdown[1]['ppn_amount']);
        $this->assertSame(444000, $breakdown[1]['billing_amount']);
        $this->assertSame(0, $breakdown[1]['paid_amount']);
        $this->assertSame(444000, $breakdown[1]['remaining_amount']);
        $this->assertSame(1110000, $breakdown[0]['billing_amount'] + $breakdown[1]['billing_amount']);

        // Bayar sebagian Rp 555.000 (50% dari Rp 1.110.000) -> Mobil A Rp 333.000, Mobil B Rp 222.000
        $purchase->update([
            'paidAmount' => 555000,
            'paymentStatus' => 'Partial',
        ]);

        $detail = $service->findPaymentDetail($purchase->code);
        $this->assertCount(2, $detail['vehicle_breakdown']);
        $this->assertSame(333000, $detail['vehicle_breakdown'][0]['paid_amount']);
        $this->assertSame(333000, $detail['vehicle_breakdown'][0]['remaining_amount']);
        $this->assertSame(222000, $detail['vehicle_breakdown'][1]['paid_amount']);
        $this->assertSame(222000, $detail['vehicle_breakdown'][1]['remaining_amount']);

        // Cek juga muncul di datatableUnpaid
        $controller = app(PurchasePaymentController::class);
        $req = Request::create('/dt/purchase-payment/unpaid', 'GET', [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'code' => 'PO-TEST-VB-001',
            'columns' => [
                ['data' => 'checkbox', 'name' => 'checkbox', 'searchable' => 'false', 'orderable' => 'false'],
                ['data' => 'code', 'name' => 'code', 'searchable' => 'true', 'orderable' => 'true'],
            ],
        ]);
        $req->headers->set('X-Requested-With', 'XMLHttpRequest');
        $json = $controller->datatableUnpaid($req)->getData(true);

        $row = collect($json['data'])->firstWhere('code', 'PO-TEST-VB-001');
        $this->assertNotNull($row);
        $this->assertCount(2, $row['vehicleBreakdown']);
        $this->assertSame(333000, $row['vehicleBreakdown'][0]['remaining_amount']);
        $this->assertSame(222000, $row['vehicleBreakdown'][1]['remaining_amount']);

        // Cek format teks Export Excel & PDF
        $exportText = $service->formatVehicleBreakdownText($row['vehicleBreakdown'], 'unpaid');
        $this->assertSame('BE 8172 AEU (Rp 333.000), BE 8307 ADU (Rp 222.000)', $exportText);
    }
}
