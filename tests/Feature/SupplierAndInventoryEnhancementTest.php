<?php

namespace Tests\Feature;

use App\Exports\SupplierExport;
use App\Models\Inventory\Item;
use App\Models\Inventory\ItemCategory;
use App\Models\Inventory\Supplier;
use App\Models\Inventory\Warehouse;
use App\Models\Master\Fleet;
use App\Models\Master\FleetCompany;
use App\Models\Master\Unit;
use App\Models\Purchasing\Purchase;
use App\Models\Purchasing\PurchaseDetail;
use App\Models\StockTransaction;
use App\Models\User;
use App\Models\Warehouse\Maintenance;
use App\Models\Warehouse\MaintenanceDetail;
use App\Models\Warehouse\MaintenanceFifo;
use App\Services\Purchasing\PurchasePaymentService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\TestCase;
use Yajra\DataTables\Utilities\Request;

class SupplierAndInventoryEnhancementTest extends TestCase
{
    use DatabaseTransactions;

    protected function getDatatableJson(string $uri): TestResponse
    {
        app()->forgetInstance(Request::class);

        return $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->getJson($uri);
    }

    protected function actingAsAdmin(): User
    {
        $user = User::query()
            ->where('roleCode', 'SPRADMIN')
            ->first();

        if (! $user) {
            $user = User::create([
                'code' => 'USR-ENH-ADMIN',
                'name' => 'Test Super Admin',
                'username' => 'test_enh_admin',
                'password' => bcrypt('password'),
                'roleCode' => 'SPRADMIN',
                'languange' => 'id',
            ]);
        }

        $this->actingAs($user);

        return $user;
    }

    public function test_supplier_stores_payment_term_days_and_renders_in_datatable_and_exports(): void
    {
        $this->actingAsAdmin();

        $response = $this->post(route('inventory.supplier.store'), [
            'code' => 'SUP-ENH-001',
            'name' => 'PT Mitra Sparepart Tempo 45',
            'address' => 'Jl. Industri Raya No. 88',
            'pic' => 'Budi Santoso',
            'phone' => '08123456789',
            'email' => 'mitra@example.com',
            'ppn' => '11',
            'pph' => '0',
            'payment_term_days' => 45,
        ]);

        $response->assertRedirect(route('inventory.supplier.index'));

        $supplier = Supplier::query()->where('code', 'SUP-ENH-001')->first();
        $this->assertNotNull($supplier);
        $this->assertSame(45, (int) $supplier->payment_term_days);

        $dtResponse = $this->getDatatableJson(route('dt.supplier', ['keyword' => 'SUP-ENH-001']));
        $dtResponse->assertOk();

        $row = collect($dtResponse->json('data'))->firstWhere('code', 'SUP-ENH-001');
        $this->assertNotNull($row);
        $this->assertSame(45, (int) $row['payment_term_days']);

        $export = new SupplierExport(Supplier::query()->where('code', 'SUP-ENH-001'));
        $mapped = $export->map($supplier);
        $this->assertContains('45 Hari', $mapped);

        $pdfResponse = $this->get(route('inventory.supplier.export-pdf', ['keyword' => 'SUP-ENH-001']));
        $pdfResponse->assertOk();
        $this->assertSame('application/pdf', $pdfResponse->headers->get('Content-Type'));
    }

    public function test_purchase_and_direct_purchase_calculate_ppn_due_date_fleet_owner_and_show_button(): void
    {
        $this->actingAsAdmin();

        $warehouse = Warehouse::query()->first() ?? Warehouse::create([
            'code' => 'WH-ENH-01',
            'name' => 'Gudang Utama Enh',
        ]);
        $unit = Unit::query()->where('type', 'Inventory')->first() ?? Unit::create([
            'code' => 'UNT-ENH-01',
            'name' => 'Pcs',
            'type' => 'Inventory',
        ]);

        $supplier = Supplier::create([
            'code' => 'SUP-ENH-002',
            'name' => 'PT Sumber Baut PPN',
            'ppn' => '11',
            'pph' => '0',
            'payment_term_days' => 30,
        ]);

        $company = FleetCompany::create([
            'code' => 'FC-ENH-001',
            'name' => 'PT ARMADA LOGISTIK NUSANTARA',
            'type' => 'Internal',
        ]);

        $fleet = Fleet::create([
            'code' => 'FLT-ENH-001',
            'plateNumber' => 'BE 9988 ENH',
            'fleetCompanyCode' => $company->code,
        ]);

        $partItem = Item::create([
            'code' => 'ITM-ENH-PART',
            'name' => 'Kampas Rem Belakang',
            'unitCode' => $unit->code,
            'warehouseCode' => $warehouse->code,
            'type' => Item::TYPE_PART,
            'price' => 500000,
        ]);

        $jasaItem = Item::create([
            'code' => 'ITM-ENH-JASA',
            'name' => 'Jasa Bongkar Pasang Rem',
            'unitCode' => $unit->code,
            'type' => Item::TYPE_JASA,
            'price' => 200000,
        ]);

        // Create views render cleanly and include [Jasa] label on service items
        $this->get(route('purchasing.purchase.create'))->assertOk()->assertSee('Jasa Bongkar Pasang Rem [Jasa]');
        $this->get(route('purchasing.direct-purchase.create'))->assertOk()->assertSee('Jasa Bongkar Pasang Rem [Jasa]');

        // 1. Regular Purchase (PO) — auto dueDate (2026-11-01 + 30 days = 2026-12-01) & auto PPN 11% from supplier
        $poStore = $this->post(route('purchasing.purchase.store'), [
            'code' => 'PO-ENH-261101',
            'supplierCode' => $supplier->code,
            'fleetCode' => $fleet->code,
            'date' => '2026-11-01',
            'time' => '09:30',
            'itemCode' => [$partItem->code, $jasaItem->code],
            'qty' => [2, 1],
            'price' => ['400.000', '200.000'],
            'description' => ['Part rem', 'Ongkos pasang'],
        ]);
        $poStore->assertRedirect(route('purchasing.purchase.index'));

        $po = Purchase::query()->where('code', 'PO-ENH-261101')->first();
        $this->assertNotNull($po);
        $this->assertSame(1000000.0, (float) $po->subtotal);
        $this->assertSame(11.0, (float) $po->ppn_rate);
        $this->assertSame(110000.0, (float) $po->ppn_amount);
        $this->assertSame(1110000.0, (float) $po->nominal);
        $this->assertSame('2026-12-01', substr((string) $po->dueDate, 0, 10));
        $this->assertSame($fleet->code, $po->fleetCode);

        // Show page & DataTable Show button
        $showRes = $this->get(route('purchasing.purchase.show', $po->id));
        $showRes->assertOk();
        $showRes->assertSee('BE 9988 ENH');
        $showRes->assertSee('PT ARMADA LOGISTIK NUSANTARA');
        $showRes->assertSee('1.110.000');

        // Even if status != 0 (e.g. Paid / status = 3), Show button remains visible in DataTable
        $po->update(['status' => 3]);
        $dtPo = $this->getDatatableJson(route('dt.purchase', ['supplierCode' => $supplier->code]));
        $dtPo->assertOk();
        $poRow = collect($dtPo->json('data'))->firstWhere('code', 'PO-ENH-261101');
        $this->assertNotNull($poRow);
        $this->assertStringContainsString(route('purchasing.purchase.show', $po->id), $poRow['action']);
        $this->assertStringContainsString('mdi-eye', $poRow['action']);
        $this->assertStringContainsString('BE 9988 ENH', $poRow['fleetInfo']);
        $this->assertStringContainsString('PT ARMADA LOGISTIK NUSANTARA', $poRow['fleetInfo']);

        // 2. Direct Purchase (Pembelian Langsung) Tempo + PPN 11%
        $plStore = $this->post(route('purchasing.direct-purchase.store'), [
            'code' => 'PL-ENH-261105',
            'fleetCode' => $fleet->code,
            'supplierCode' => $supplier->code,
            'ppn_rate' => 11,
            'date' => '2026-11-05',
            'time' => '14:00',
            'paymentType' => 'Tempo',
            'dueDate' => '2026-12-05',
            'itemCode' => [$partItem->code],
            'qty' => [2],
            'price' => ['500.000'],
            'description' => ['Pasang langsung'],
        ]);
        $plStore->assertRedirect(route('purchasing.direct-purchase.index'));

        $pl = Purchase::query()->where('code', 'PL-ENH-261105')->first();
        $this->assertNotNull($pl);
        $this->assertTrue((bool) $pl->is_direct);
        $this->assertSame(1000000.0, (float) $pl->subtotal);
        $this->assertSame(11.0, (float) $pl->ppn_rate);
        $this->assertSame(110000.0, (float) $pl->ppn_amount);
        $this->assertSame(1110000.0, (float) $pl->nominal);
        $this->assertSame('2026-12-05', substr((string) $pl->dueDate, 0, 10));

        // Linked Maintenance keeps grand_total after PPN (1.110.000) and exposes fleetOwner in Maintenance DataTable
        $mnt = $pl->maintenances()->first();
        $this->assertNotNull($mnt);
        $this->assertSame(1110000.0, (float) $mnt->grand_total);

        $dtMnt = $this->getDatatableJson(route('dt.maintenance', ['plateNumber' => 'BE 9988 ENH']));
        $dtMnt->assertOk();
        $mntRow = collect($dtMnt->json('data'))->firstWhere('code', $mnt->code);
        $this->assertNotNull($mntRow);
        $this->assertSame('PT ARMADA LOGISTIK NUSANTARA', $mntRow['fleetOwner']);
    }

    public function test_unpaid_debt_datatables_oldest_sort_and_chunked_pdf_export(): void
    {
        $this->actingAsAdmin();

        $warehouse = Warehouse::query()->first() ?? Warehouse::create([
            'code' => 'WH-ENH-02',
            'name' => 'Gudang Debt Test',
        ]);

        $supOld = Supplier::create([
            'code' => 'SUP-ENH-OLD',
            'name' => 'Supplier Hutang Lama',
            'payment_term_days' => 14,
        ]);
        $supNew = Supplier::create([
            'code' => 'SUP-ENH-NEW',
            'name' => 'Supplier Hutang Baru',
            'payment_term_days' => 14,
        ]);

        Purchase::create([
            'code' => 'PO-ENH-NEW-01',
            'supplierCode' => $supNew->code,
            'warehouseCode' => $warehouse->code,
            'date' => '2027-02-20',
            'time' => '10:00',
            'dueDate' => '2027-03-06',
            'subtotal' => 300000,
            'ppn_rate' => 0,
            'ppn_amount' => 0,
            'nominal' => 300000,
            'paidAmount' => 0,
            'paymentStatus' => 'Unpaid',
            'status' => 0,
            'is_direct' => false,
        ]);

        Purchase::create([
            'code' => 'PO-ENH-OLD-01',
            'supplierCode' => $supOld->code,
            'warehouseCode' => $warehouse->code,
            'date' => '2027-01-05',
            'time' => '08:00',
            'dueDate' => '2027-01-19',
            'subtotal' => 450000,
            'ppn_rate' => 0,
            'ppn_amount' => 0,
            'nominal' => 450000,
            'paidAmount' => 0,
            'paymentStatus' => 'Unpaid',
            'status' => 0,
            'is_direct' => false,
        ]);

        // Edge case: columns[0][data] = "" AND columns[0][data] = "checkbox" both succeed without SQL error
        foreach (['', 'checkbox'] as $colZeroData) {
            $unpaidRes = $this->getDatatableJson(route('dt.purchase-payment.unpaid', [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
                'startDate' => '2027-01-01',
                'endDate' => '2027-02-28',
                'columns' => [
                    ['data' => $colZeroData, 'name' => $colZeroData, 'searchable' => 'false', 'orderable' => 'false'],
                    ['data' => 'code', 'name' => 'code', 'searchable' => 'true', 'orderable' => 'true'],
                ],
            ]));

            $unpaidRes->assertOk();
            $codes = collect($unpaidRes->json('data'))->pluck('code')->all();
            $this->assertSame(['PO-ENH-OLD-01', 'PO-ENH-NEW-01'], $codes);
        }

        // Default sort on supplier summary places oldest debt (2027-01-05) before newer debt (2027-02-20)
        $summaryRes = $this->getDatatableJson(route('dt.purchase-payment.unpaid-supplier', [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'startDate' => '2027-01-01',
            'endDate' => '2027-02-28',
        ]));
        $summaryRes->assertOk();
        $summaryCodes = collect($summaryRes->json('data'))->pluck('supplierCode')->all();
        $this->assertSame([$supOld->code, $supNew->code], $summaryCodes);
        $this->assertArrayHasKey('oldestPurchaseDateHtml', $summaryRes->json('data.0'));

        // Chunked mPDF export returns 200 PDF
        $pdfRes = $this->get(route('purchasing.purchase-payment.export-pdf', [
            'supplierCode' => $supOld->code,
        ]));
        $pdfRes->assertOk();
        $this->assertSame('application/pdf', $pdfRes->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $pdfRes->getContent());
    }

    public function test_pre_september_2026_migration_marks_old_debt_paid_without_mutating_bank(): void
    {
        $warehouse = Warehouse::query()->first() ?? Warehouse::create([
            'code' => 'WH-ENH-03',
            'name' => 'Gudang Hist Test',
        ]);
        $supplier = Supplier::create([
            'code' => 'SUP-ENH-HIST',
            'name' => 'Supplier Historis',
        ]);

        $mutationCountBefore = DB::table('live_mutation')->count();
        $mutationSumBefore = (float) DB::table('live_mutation')->sum('balance');

        $oldPo = Purchase::create([
            'code' => 'PO-ENH-202608',
            'supplierCode' => $supplier->code,
            'warehouseCode' => $warehouse->code,
            'date' => '2026-08-25',
            'time' => '09:00',
            'dueDate' => '2026-09-10',
            'subtotal' => 750000,
            'ppn_rate' => 0,
            'ppn_amount' => 0,
            'nominal' => 750000,
            'paidAmount' => 0,
            'paymentStatus' => 'Unpaid',
            'status' => 0,
            'is_direct' => false,
        ]);

        $sepPo = Purchase::create([
            'code' => 'PO-ENH-202609',
            'supplierCode' => $supplier->code,
            'warehouseCode' => $warehouse->code,
            'date' => '2026-09-01',
            'time' => '09:00',
            'dueDate' => '2026-09-15',
            'subtotal' => 500000,
            'ppn_rate' => 0,
            'ppn_amount' => 0,
            'nominal' => 500000,
            'paidAmount' => 0,
            'paymentStatus' => 'Unpaid',
            'status' => 0,
            'is_direct' => false,
        ]);

        $migration = require database_path('migrations/2026_11_26_132000_mark_pre_september_2026_purchases_as_paid.php');
        $migration->up();

        $oldPo->refresh();
        $sepPo->refresh();

        $this->assertSame('Paid', $oldPo->paymentStatus);
        $this->assertSame(750000.0, (float) $oldPo->paidAmount);
        $this->assertSame(3, (int) $oldPo->status);
        $this->assertSame('HIST-PRE-202609', $oldPo->paymentCode);

        $this->assertSame('Unpaid', $sepPo->paymentStatus);
        $this->assertSame(0.0, (float) $sepPo->paidAmount);

        $this->assertSame($mutationCountBefore, DB::table('live_mutation')->count());
        $this->assertSame($mutationSumBefore, (float) DB::table('live_mutation')->sum('balance'));
    }

    public function test_multi_vehicle_payment_breakdown_proportional_allocation(): void
    {
        $warehouse = Warehouse::query()->first() ?? Warehouse::create([
            'code' => 'WH-ENH-04',
            'name' => 'Gudang Breakdown',
        ]);
        $supplier = Supplier::create([
            'code' => 'SUP-ENH-VB',
            'name' => 'Supplier Breakdown',
            'ppn' => '11',
        ]);
        $company = FleetCompany::create([
            'code' => 'FC-ENH-VB',
            'name' => 'PT PHL LOGISTIK',
            'type' => 'Internal',
        ]);
        $truck1 = Fleet::create([
            'code' => 'FLT-ENH-T1',
            'plateNumber' => 'B 9101 ENH',
            'fleetCompanyCode' => $company->code,
        ]);
        $truck2 = Fleet::create([
            'code' => 'FLT-ENH-T2',
            'plateNumber' => 'B 9202 ENH',
            'fleetCompanyCode' => $company->code,
        ]);
        $item = Item::create([
            'code' => 'ITM-ENH-OLI',
            'name' => 'Oli Gardan SAE 90',
            'type' => Item::TYPE_PART,
            'price' => 500000,
        ]);

        // Subtotal 2.000.000 + PPN 11% (220.000) = 2.220.000, paid 50% (1.110.000)
        $po = Purchase::create([
            'code' => 'PO-ENH-MULTI-01',
            'supplierCode' => $supplier->code,
            'warehouseCode' => $warehouse->code,
            'date' => '2026-11-12',
            'time' => '10:00',
            'dueDate' => '2026-12-12',
            'subtotal' => 2000000,
            'ppn_rate' => 11,
            'ppn_amount' => 220000,
            'nominal' => 2220000,
            'paidAmount' => 1110000,
            'paymentStatus' => 'Partial',
            'status' => 0,
            'is_direct' => false,
        ]);
        $pd = PurchaseDetail::create([
            'code' => 'FPD-ENH-MULTI-01',
            'purchaseCode' => $po->code,
            'itemCode' => $item->code,
            'qty' => 4,
            'receivedQty' => 4,
            'qtyUsed' => 4,
            'price' => 500000,
            'status' => 1,
        ]);

        // Truck 1 uses 3 units (75%), Truck 2 uses 1 unit (25%)
        foreach ([
            ['suffix' => '1', 'fleet' => $truck1, 'qty' => 3, 'total' => 1500000],
            ['suffix' => '2', 'fleet' => $truck2, 'qty' => 1, 'total' => 500000],
        ] as $alloc) {
            $mnt = Maintenance::create([
                'code' => 'MNT-ENH-M'.$alloc['suffix'],
                'date' => '2026-11-12',
                'time' => '11:00',
                'fleetCode' => $alloc['fleet']->code,
                'warehouseCode' => $warehouse->code,
                'status' => 0,
                'grand_total' => $alloc['total'],
            ]);
            $md = MaintenanceDetail::create([
                'code' => 'FMD-ENH-M'.$alloc['suffix'],
                'maintenanceCode' => $mnt->code,
                'itemCode' => $item->code,
                'qty' => $alloc['qty'],
                'price' => 500000,
                'total' => $alloc['total'],
            ]);
            MaintenanceFifo::create([
                'code' => 'MF-ENH-M'.$alloc['suffix'],
                'maintenanceDetailCode' => $md->code,
                'purchaseDetailCode' => $pd->code,
                'qty' => $alloc['qty'],
            ]);
        }

        $breakdown = app(PurchasePaymentService::class)->resolveVehicleBreakdown($po->fresh('details'));

        $this->assertCount(2, $breakdown);
        $this->assertSame('B 9101 ENH', $breakdown[0]['plate_number']);
        $this->assertSame(1665000, $breakdown[0]['billing_amount']);
        $this->assertSame(832500, $breakdown[0]['paid_amount']);
        $this->assertSame(832500, $breakdown[0]['remaining_amount']);

        $this->assertSame('B 9202 ENH', $breakdown[1]['plate_number']);
        $this->assertSame(555000, $breakdown[1]['billing_amount']);
        $this->assertSame(277500, $breakdown[1]['paid_amount']);
        $this->assertSame(277500, $breakdown[1]['remaining_amount']);
    }

    public function test_separate_barang_and_jasa_menus_routes_crud_stock_and_datatables(): void
    {
        $this->actingAsAdmin();

        // 1. Verify SERVICE_ITEM menu and role_menu permissions
        $itemMenu = DB::table('menu')->where('code', 'ITEM')->whereNull('deleted_at')->first();
        $jasaMenu = DB::table('menu')->where('code', 'SERVICE_ITEM')->whereNull('deleted_at')->first();

        $this->assertNotNull($itemMenu);
        $this->assertNotNull($jasaMenu);
        $this->assertSame('INVENTORY', $jasaMenu->parentCode);
        $this->assertSame('inventory/jasa', $jasaMenu->url);
        $this->assertSame(1, (int) $itemMenu->sort);
        $this->assertSame(2, (int) $jasaMenu->sort);

        $itemRoles = DB::table('role_menu')->where('menuCode', 'ITEM')->whereNull('deleted_at')->pluck('roleCode')->all();
        foreach ($itemRoles as $roleCode) {
            $hasJasaAccess = DB::table('role_menu')
                ->where('roleCode', $roleCode)
                ->where('menuCode', 'SERVICE_ITEM')
                ->whereNull('deleted_at')
                ->exists();
            $this->assertTrue($hasJasaAccess, "Role {$roleCode} should have SERVICE_ITEM access.");
        }

        $unit = Unit::query()->where('type', 'Inventory')->first() ?? Unit::create([
            'code' => 'UNT-ENH-02',
            'name' => 'Set',
            'type' => 'Inventory',
        ]);
        $category = ItemCategory::query()->first() ?? ItemCategory::create([
            'code' => 'CAT-ENH-01',
            'name' => 'Kategori Test Enh',
        ]);
        $warehouse = Warehouse::create([
            'code' => 'WH-ENH-05',
            'name' => 'Gudang Barang Enh',
        ]);

        // 2. Pages render cleanly
        $this->get(route('inventory.items.index'))->assertOk()->assertSee('Data Barang');
        $this->get(route('inventory.items.create'))->assertOk()->assertSee('Tambah Barang')->assertSee('Gudang');
        $this->get(route('inventory.jasa.index'))->assertOk()->assertSee('Data Jasa');
        $this->get(route('inventory.jasa.create'))->assertOk()->assertSee('Tambah Jasa')->assertDontSee('name="warehouseCode"', false);

        // 3. Store Barang (locks type = part even if tampered) & Store Jasa (locks type = jasa & warehouseCode = null)
        $this->post(route('inventory.items.store'), [
            'code' => 'BRG-ENH-001',
            'name' => 'Filter Udara Truk',
            'categoryCode' => $category->code,
            'warehouseCode' => $warehouse->code,
            'unitCode' => $unit->code,
            'type' => 'jasa', // tampered input must be overridden to 'part'
            'price' => '1.250.000',
        ])->assertRedirect(route('inventory.items.index'));

        $this->post(route('inventory.jasa.store'), [
            'code' => 'JSA-ENH-001',
            'name' => 'Jasa Kalibrasi Bospom',
            'categoryCode' => $category->code,
            'warehouseCode' => $warehouse->code, // must be cleared to null for Jasa
            'unitCode' => $unit->code,
            'type' => 'part', // tampered input must be overridden to 'jasa'
            'price' => '1.750.000',
        ])->assertRedirect(route('inventory.jasa.index'));

        $createdPart = Item::query()->where('code', 'BRG-ENH-001')->first();
        $createdJasa = Item::query()->where('code', 'JSA-ENH-001')->first();

        $this->assertNotNull($createdPart);
        $this->assertSame(Item::TYPE_PART, $createdPart->type);
        $this->assertSame(1250000.0, (float) $createdPart->price);
        $this->assertSame($warehouse->code, $createdPart->warehouseCode);

        $this->assertNotNull($createdJasa);
        $this->assertSame(Item::TYPE_JASA, $createdJasa->type);
        $this->assertSame(1750000.0, (float) $createdJasa->price);
        $this->assertNull($createdJasa->warehouseCode);

        // 4. Update Barang & Jasa + Cross-type isolation
        $this->get(route('inventory.jasa.edit', $createdPart->id))->assertRedirect(route('inventory.jasa.index'));
        $this->get(route('inventory.items.edit', $createdJasa->id))->assertRedirect(route('inventory.items.index'));

        $this->put(route('inventory.items.update', $createdPart->id), [
            'name' => 'Filter Udara Truk Hino',
            'categoryCode' => $category->code,
            'warehouseCode' => $warehouse->code,
            'unitCode' => $unit->code,
            'price' => '1.350.000',
        ])->assertRedirect(route('inventory.items.index'));

        $this->put(route('inventory.jasa.update', $createdJasa->id), [
            'name' => 'Jasa Kalibrasi Bospom Full',
            'categoryCode' => $category->code,
            'unitCode' => $unit->code,
            'price' => '1.850.000',
        ])->assertRedirect(route('inventory.jasa.index'));

        $this->assertSame('Filter Udara Truk Hino', $createdPart->fresh()->name);
        $this->assertSame(1350000.0, (float) $createdPart->fresh()->price);
        $this->assertSame('Jasa Kalibrasi Bospom Full', $createdJasa->fresh()->name);
        $this->assertSame(1850000.0, (float) $createdJasa->fresh()->price);

        // 5. DataTables strict separation & global search (including formatted Rupiah & negative non-matching keyword)
        $dtItems = $this->getDatatableJson(route('dt.items', ['search' => ['value' => 'BRG-ENH-001']]));
        $dtItems->assertOk();
        $this->assertSame(['BRG-ENH-001'], collect($dtItems->json('data'))->pluck('code')->all());

        $dtItemsLookingForJasa = $this->getDatatableJson(route('dt.items', ['search' => ['value' => 'JSA-ENH-001']]));
        $dtItemsLookingForJasa->assertOk();
        $this->assertSame([], collect($dtItemsLookingForJasa->json('data'))->pluck('code')->all());

        $dtJasa = $this->getDatatableJson(route('dt.jasa', ['search' => ['value' => 'Rp 1.850.000']]));
        $dtJasa->assertOk();
        $this->assertContains('JSA-ENH-001', collect($dtJasa->json('data'))->pluck('code')->all());
        $this->assertNotContains('BRG-ENH-001', collect($dtJasa->json('data'))->pluck('code')->all());

        $dtNegative = $this->getDatatableJson(route('dt.jasa', ['search' => ['value' => 'ZZZ-TIDAK-ADA-999']]));
        $dtNegative->assertOk();
        $this->assertSame(0, count($dtNegative->json('data')));

        // 6. Stock menu strictly displays physical 'part' only
        StockTransaction::create([
            'code' => 'FST-ENH-PART',
            'itemCode' => $createdPart->code,
            'warehouseCode' => $warehouse->code,
            'qtyIn' => 5,
            'qtyOut' => 0,
            'date' => now(),
            'transactionType' => 'INITIAL',
        ]);
        StockTransaction::create([
            'code' => 'FST-ENH-JASA',
            'itemCode' => $createdJasa->code,
            'warehouseCode' => $warehouse->code,
            'qtyIn' => 5,
            'qtyOut' => 0,
            'date' => now(),
            'transactionType' => 'INITIAL',
        ]);

        $dtStock = $this->getDatatableJson(route('dt.stock', [
            'warehouseCode' => $warehouse->code,
            'search' => ['value' => ''],
        ]));
        $dtStock->assertOk();
        $stockItemCodes = collect($dtStock->json('data'))->pluck('itemCode')->all();
        $this->assertSame(['BRG-ENH-001'], $stockItemCodes);
        $this->assertNotContains('JSA-ENH-001', $stockItemCodes);

        // 7. Exports for both Barang & Jasa
        $excelPart = $this->get(route('inventory.items.export-excel', ['itemCode' => $createdPart->code]));
        $this->assertInstanceOf(BinaryFileResponse::class, $excelPart->baseResponse);

        $excelJasa = $this->get(route('inventory.jasa.export-excel', ['itemCode' => $createdJasa->code]));
        $this->assertInstanceOf(BinaryFileResponse::class, $excelJasa->baseResponse);

        $pdfPart = $this->get(route('inventory.items.export-pdf', ['itemCode' => $createdPart->code]));
        $pdfPart->assertOk();
        $this->assertSame('application/pdf', $pdfPart->headers->get('Content-Type'));

        $pdfJasa = $this->get(route('inventory.jasa.export-pdf', ['itemCode' => $createdJasa->code]));
        $pdfJasa->assertOk();
        $this->assertSame('application/pdf', $pdfJasa->headers->get('Content-Type'));
    }
}
