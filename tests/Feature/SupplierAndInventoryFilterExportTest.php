<?php

namespace Tests\Feature;

use App\Http\Controllers\Inventory\ItemCategoryController;
use App\Http\Controllers\Inventory\ItemController;
use App\Http\Controllers\Inventory\ItemUnitController;
use App\Http\Controllers\Inventory\StockController;
use App\Http\Controllers\Inventory\StockSyncController;
use App\Http\Controllers\Inventory\StockTransactionController;
use App\Http\Controllers\Inventory\WarehouseController;
use App\Http\Controllers\Purchasing\DirectPurchaseController;
use App\Http\Controllers\Purchasing\PurchaseController;
use App\Http\Controllers\Purchasing\PurchasePaymentController;
use App\Http\Controllers\Warehouse\MaintenanceController;
use App\Models\Bank\BankAccount;
use App\Models\Bank\UserBank;
use App\Models\Inventory\Item;
use App\Models\Inventory\ItemCategory;
use App\Models\Inventory\Supplier;
use App\Models\Inventory\Warehouse;
use App\Models\Master\Fleet;
use App\Models\Master\FleetCompany;
use App\Models\Master\FleetType;
use App\Models\Master\Unit;
use App\Models\Purchasing\Purchase;
use App\Models\Purchasing\PurchaseDetail;
use App\Models\Role;
use App\Models\StockTransaction;
use App\Models\User;
use App\Models\Warehouse\Maintenance;
use App\Models\Warehouse\MaintenanceDetail;
use App\Models\Warehouse\MaintenancePurchase;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\TestCase;

class SupplierAndInventoryFilterExportTest extends TestCase
{
    protected User $user;

    protected Supplier $supplierA;

    protected Supplier $supplierB;

    protected Warehouse $warehouseA;

    protected Warehouse $warehouseB;

    protected ItemCategory $categoryA;

    protected ItemCategory $categoryB;

    protected Unit $unitPcs;

    protected Unit $unitLiter;

    protected Item $itemA;

    protected Item $itemB;

    protected Fleet $fleetA;

    protected Fleet $fleetB;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        $this->createMinimalSchema();
        $this->seedFixtures();
    }

    protected function tearDown(): void
    {
        $tables = [
            'maintenance_purchase',
            'maintenance_fifo',
            'maintenance_detail',
            'maintenance',
            'stock_transaction',
            'stock',
            'purchase_payment_history',
            'supplier_payment_batch',
            'purchase_detail',
            'purchase',
            'purchase_status',
            'item',
            'unit',
            'item_category',
            'warehouse',
            'supplier',
            'fleet',
            'fleet_type',
            'fleet_company',
            'user_bank',
            'live_mutation',
            'bank_account',
            'company_setting',
            'role_menu',
            'menu',
            'role',
            'users',
        ];

        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }

        parent::tearDown();
    }

    public function test_purchasing_four_submenus_views_filters_and_exports(): void
    {
        $this->actingAs($this->user);
        View::share('errors', new ViewErrorBag());

        // Regular PO (unpaid & paid)
        $poUnpaid = Purchase::create([
            'code' => 'PO-001',
            'supplierCode' => $this->supplierA->code,
            'warehouseCode' => $this->warehouseA->code,
            'is_direct' => false,
            'date' => '2026-10-05',
            'time' => '09:00:00',
            'dueDate' => '2026-10-20',
            'nominal' => 500000,
            'paidAmount' => 0,
            'paymentStatus' => 'Unpaid',
            'status' => 0,
        ]);
        PurchaseDetail::create([
            'code' => 'FPD-001',
            'purchaseCode' => $poUnpaid->code,
            'itemCode' => $this->itemA->code,
            'qty' => 2,
            'receivedQty' => 2,
            'price' => 250000,
            'status' => 1,
        ]);

        $poPaid = Purchase::create([
            'code' => 'PO-002',
            'supplierCode' => $this->supplierB->code,
            'warehouseCode' => $this->warehouseB->code,
            'is_direct' => false,
            'date' => '2026-10-18',
            'time' => '10:00:00',
            'dueDate' => '2026-11-01',
            'paymentDate' => '2026-10-19',
            'nominal' => 300000,
            'paidAmount' => 300000,
            'paymentStatus' => 'Paid',
            'status' => 3,
        ]);
        PurchaseDetail::create([
            'code' => 'FPD-002',
            'purchaseCode' => $poPaid->code,
            'itemCode' => $this->itemB->code,
            'qty' => 3,
            'receivedQty' => 3,
            'price' => 100000,
            'status' => 1,
        ]);

        // Direct Purchase
        $plA = Purchase::create([
            'code' => 'PL-2610050001',
            'supplierCode' => $this->supplierA->code,
            'warehouseCode' => $this->warehouseA->code,
            'fleetCode' => $this->fleetA->code,
            'is_direct' => true,
            'date' => '2026-10-05',
            'time' => '11:00:00',
            'paymentType' => 'Tunai',
            'paymentStatus' => 'Paid',
            'paymentDate' => '2026-10-05',
            'paidAmount' => 200000,
            'nominal' => 200000,
            'status' => 3,
        ]);
        PurchaseDetail::create([
            'code' => 'FPD-003',
            'purchaseCode' => $plA->code,
            'itemCode' => $this->itemA->code,
            'qty' => 1,
            'receivedQty' => 1,
            'price' => 200000,
            'status' => 1,
        ]);

        $plB = Purchase::create([
            'code' => 'PL-2610200001',
            'supplierCode' => $this->supplierB->code,
            'warehouseCode' => $this->warehouseB->code,
            'fleetCode' => $this->fleetB->code,
            'is_direct' => true,
            'date' => '2026-10-20',
            'time' => '14:00:00',
            'paymentType' => 'Tempo',
            'paymentStatus' => 'Partial',
            'dueDate' => '2026-11-05',
            'paidAmount' => 100000,
            'nominal' => 400000,
            'status' => 0,
        ]);
        PurchaseDetail::create([
            'code' => 'FPD-004',
            'purchaseCode' => $plB->code,
            'itemCode' => $this->itemB->code,
            'qty' => 4,
            'receivedQty' => 4,
            'price' => 100000,
            'status' => 1,
        ]);

        /** @var PurchaseController $purchaseCtrl */
        $purchaseCtrl = app(PurchaseController::class);
        /** @var DirectPurchaseController $directPurchaseCtrl */
        $directPurchaseCtrl = app(DirectPurchaseController::class);
        /** @var PurchasePaymentController $paymentCtrl */
        $paymentCtrl = app(PurchasePaymentController::class);

        // Views contain standard filter-card, dark mode rules, export buttons, syncExportUrls
        foreach ([
            $purchaseCtrl->index()->render(),
            $directPurchaseCtrl->index()->render(),
            $paymentCtrl->index()->render(),
            $paymentCtrl->supplierDetail($this->supplierA->code)->render(),
            $paymentCtrl->paid()->render(),
        ] as $html) {
            $this->assertFilterExportContract($html);
        }

        // DataTable filtering
        $dtPurchaseReq = Request::create('/datatable/purchase', 'GET', ['supplierCode' => $this->supplierA->code]);
        $dtPurchaseReq->headers->set('X-Requested-With', 'XMLHttpRequest');
        $dtPurchasePayload = $purchaseCtrl->datatable($dtPurchaseReq)->getData(true);
        $this->assertSame(['PO-001'], collect($dtPurchasePayload['data'])->pluck('code')->all());

        $dtDirectReq = Request::create('/datatable/direct-purchase', 'GET', ['fleetCode' => $this->fleetB->code, 'paymentStatus' => 'Partial']);
        $dtDirectReq->headers->set('X-Requested-With', 'XMLHttpRequest');
        $dtDirectPayload = $directPurchaseCtrl->datatable($dtDirectReq)->getData(true);
        $this->assertSame(['PL-2610200001'], collect($dtDirectPayload['data'])->pluck('code')->all());

        $dtSummaryReq = Request::create('/datatable/purchase-payment/unpaid-supplier', 'GET', ['supplierCode' => $this->supplierA->code]);
        $dtSummaryReq->headers->set('X-Requested-With', 'XMLHttpRequest');
        $dtSummaryPayload = $paymentCtrl->datatableSupplierSummary($dtSummaryReq)->getData(true);
        $this->assertSame([$this->supplierA->code], collect($dtSummaryPayload['data'])->pluck('supplierCode')->all());

        $dtUnpaidReq = Request::create('/datatable/purchase-payment/unpaid', 'GET', ['supplierCode' => $this->supplierB->code, 'paymentStatus' => 'Partial']);
        $dtUnpaidReq->headers->set('X-Requested-With', 'XMLHttpRequest');
        $dtUnpaidPayload = $paymentCtrl->datatableUnpaid($dtUnpaidReq)->getData(true);
        $this->assertSame(['PL-2610200001'], collect($dtUnpaidPayload['data'])->pluck('code')->all());

        $dtPaidReq = Request::create('/datatable/purchase-payment/paid', 'GET', ['supplierCode' => $this->supplierA->code]);
        $dtPaidReq->headers->set('X-Requested-With', 'XMLHttpRequest');
        $dtPaidPayload = $paymentCtrl->datatablePaid($dtPaidReq)->getData(true);
        $this->assertSame(['PL-2610050001'], collect($dtPaidPayload['data'])->pluck('code')->all());

        // Excel exports
        $this->assertInstanceOf(BinaryFileResponse::class, $purchaseCtrl->exportExcel(new Request(['supplierCode' => $this->supplierA->code])));
        $this->assertInstanceOf(BinaryFileResponse::class, $directPurchaseCtrl->exportExcel(new Request(['fleetCode' => $this->fleetA->code])));
        $this->assertInstanceOf(BinaryFileResponse::class, $paymentCtrl->exportSummaryExcel(new Request(['supplierCode' => $this->supplierA->code])));
        $this->assertInstanceOf(BinaryFileResponse::class, $paymentCtrl->exportUnpaidExcel(new Request(['supplierCode' => $this->supplierA->code])));
        $this->assertInstanceOf(BinaryFileResponse::class, $paymentCtrl->exportPaidExcel(new Request(['supplierCode' => $this->supplierB->code])));

        // PDF exports
        foreach ([
            $purchaseCtrl->exportPdf(new Request(['supplierCode' => $this->supplierA->code])),
            $directPurchaseCtrl->exportPdf(new Request(['fleetCode' => $this->fleetA->code])),
            $paymentCtrl->exportSummaryPdf(new Request(['supplierCode' => $this->supplierA->code])),
            $paymentCtrl->exportUnpaidPdf(new Request(['supplierCode' => $this->supplierA->code])),
            $paymentCtrl->exportPaidPdf(new Request(['supplierCode' => $this->supplierB->code])),
        ] as $pdfResponse) {
            $this->assertPdfResponse($pdfResponse);
        }
    }

    public function test_inventory_and_warehouse_eight_submenus_views_filters_and_exports(): void
    {
        $this->actingAs($this->user);
        View::share('errors', new ViewErrorBag());

        // Seed stock transactions + maintenance
        StockTransaction::create([
            'code' => 'FST-1',
            'itemCode' => $this->itemA->code,
            'warehouseCode' => $this->warehouseA->code,
            'transactionCode' => 'INITIAL-01',
            'transactionDetailCode' => 'INITIALD-01',
            'qtyIn' => 10,
            'qtyOut' => 0,
            'date' => '2026-10-01',
            'transactionType' => 'INITIAL',
        ]);

        StockTransaction::create([
            'code' => 'FST-2',
            'itemCode' => $this->itemB->code,
            'warehouseCode' => $this->warehouseB->code,
            'transactionCode' => 'PO-ORPHAN-1',
            'transactionDetailCode' => 'FPD-ORPHAN-1',
            'qtyIn' => 5,
            'qtyOut' => 5,
            'date' => '2026-10-15',
            'transactionType' => 'IN',
        ]);

        $mntA = Maintenance::create([
            'code' => 'MNT-26100500001',
            'date' => '2026-10-05',
            'time' => '10:00:00',
            'fleetCode' => $this->fleetA->code,
            'warehouseCode' => $this->warehouseA->code,
            'status' => 0,
            'grand_total' => 250000,
        ]);
        MaintenanceDetail::create([
            'code' => 'FMD-001',
            'maintenanceCode' => $mntA->code,
            'itemCode' => $this->itemA->code,
            'qty' => '1',
            'price' => '250000',
            'total' => '250000',
        ]);

        $mntB = Maintenance::create([
            'code' => 'MNT-26102000001',
            'date' => '2026-10-20',
            'time' => '15:00:00',
            'fleetCode' => $this->fleetB->code,
            'warehouseCode' => $this->warehouseB->code,
            'status' => 0,
            'grand_total' => 100000,
        ]);
        MaintenanceDetail::create([
            'code' => 'FMD-002',
            'maintenanceCode' => $mntB->code,
            'itemCode' => $this->itemB->code,
            'qty' => '1',
            'price' => '100000',
            'total' => '100000',
        ]);

        /** @var ItemController $itemCtrl */
        $itemCtrl = app(ItemController::class);
        /** @var ItemCategoryController $catCtrl */
        $catCtrl = app(ItemCategoryController::class);
        /** @var ItemUnitController $unitCtrl */
        $unitCtrl = app(ItemUnitController::class);
        /** @var WarehouseController $whCtrl */
        $whCtrl = app(WarehouseController::class);
        /** @var StockController $stockCtrl */
        $stockCtrl = app(StockController::class);
        /** @var StockTransactionController $stockTxCtrl */
        $stockTxCtrl = app(StockTransactionController::class);
        /** @var StockSyncController $stockSyncCtrl */
        $stockSyncCtrl = app(StockSyncController::class);
        /** @var MaintenanceController $mntCtrl */
        $mntCtrl = app(MaintenanceController::class);

        // Views check
        foreach ([
            $itemCtrl->index()->render(),
            $catCtrl->index()->render(),
            $unitCtrl->index()->render(),
            $whCtrl->index()->render(),
            $stockCtrl->index()->render(),
            $stockTxCtrl->index()->render(),
            $stockSyncCtrl->index(new Request())->render(),
            $mntCtrl->index()->render(),
        ] as $html) {
            $this->assertFilterExportContract($html);
        }

        // Datatable filtering checks
        $dtItemReq = Request::create('/datatable/items', 'GET', ['categoryCode' => $this->categoryA->code, 'type' => Item::TYPE_PART]);
        $dtItemReq->headers->set('X-Requested-With', 'XMLHttpRequest');
        $this->assertSame([$this->itemA->code], collect($itemCtrl->datatable($dtItemReq)->getData(true)['data'])->pluck('code')->all());

        $dtCatReq = Request::create('/datatable/item-category', 'GET', ['keyword' => 'Filter']);
        $dtCatReq->headers->set('X-Requested-With', 'XMLHttpRequest');
        $this->assertSame([$this->categoryA->code], collect($catCtrl->datatable($dtCatReq)->getData(true)['data'])->pluck('code')->all());

        $dtUnitReq = Request::create('/datatable/item-unit', 'GET', ['keyword' => 'Liter']);
        $dtUnitReq->headers->set('X-Requested-With', 'XMLHttpRequest');
        $this->assertSame([$this->unitLiter->code], collect($unitCtrl->datatable($dtUnitReq)->getData(true)['data'])->pluck('code')->all());

        $dtWhReq = Request::create('/datatable/warehouse', 'GET', ['keyword' => 'Surabaya']);
        $dtWhReq->headers->set('X-Requested-With', 'XMLHttpRequest');
        $this->assertSame([$this->warehouseA->code], collect($whCtrl->datatable($dtWhReq)->getData(true)['data'])->pluck('code')->all());

        $dtStockReq = Request::create('/datatable/stock', 'GET', ['warehouseCode' => $this->warehouseA->code, 'stockStatus' => 'available']);
        $dtStockReq->headers->set('X-Requested-With', 'XMLHttpRequest');
        $this->assertSame([$this->itemA->code], collect($stockCtrl->datatable($dtStockReq)->getData(true)['data'])->pluck('itemCode')->all());

        $dtStockTxReq = Request::create('/datatable/transaction-stock', 'GET', ['warehouseCode' => $this->warehouseA->code, 'transactionType' => 'INITIAL']);
        $dtStockTxReq->headers->set('X-Requested-With', 'XMLHttpRequest');
        $dtStockTxPayload = $stockTxCtrl->datatable($dtStockTxReq)->getData(true);
        $this->assertSame([$this->warehouseA->code], collect($dtStockTxPayload['data'])->pluck('code')->all());
        $this->assertSame(10.0, (float) $dtStockTxPayload['data'][0]['totalIn']);

        $dtMntReq = Request::create('/datatable/maintenance', 'GET', ['plateNumber' => 'L 1001 AA', 'itemCode' => $this->itemA->code]);
        $dtMntReq->headers->set('X-Requested-With', 'XMLHttpRequest');
        $this->assertSame([$mntA->code], collect($mntCtrl->datatable($dtMntReq)->getData(true)['data'])->pluck('code')->all());

        // StockSync filtering check
        $syncFilteredHtml = $stockSyncCtrl->index(new Request(['warehouseCode' => $this->warehouseB->code, 'issueType' => 'orphans']))->render();
        $this->assertStringContainsString('PO-ORPHAN-1', $syncFilteredHtml);

        // Excel exports
        $this->assertInstanceOf(BinaryFileResponse::class, $itemCtrl->exportExcel(new Request(['categoryCode' => $this->categoryA->code])));
        $this->assertInstanceOf(BinaryFileResponse::class, $catCtrl->exportExcel(new Request(['keyword' => 'Filter'])));
        $this->assertInstanceOf(BinaryFileResponse::class, $unitCtrl->exportExcel(new Request(['keyword' => 'Liter'])));
        $this->assertInstanceOf(BinaryFileResponse::class, $whCtrl->exportExcel(new Request(['keyword' => 'Surabaya'])));
        $this->assertInstanceOf(BinaryFileResponse::class, $stockCtrl->excelStock(new Request(['warehouseCode' => $this->warehouseA->code])));
        $this->assertInstanceOf(BinaryFileResponse::class, $stockTxCtrl->excelStockTransaction(new Request(['warehouseCode' => $this->warehouseA->code])));
        $this->assertInstanceOf(BinaryFileResponse::class, $stockSyncCtrl->exportExcel(new Request(['warehouseCode' => $this->warehouseB->code])));
        $this->assertInstanceOf(BinaryFileResponse::class, $mntCtrl->exportExcel(new Request(['plateNumber' => 'L 1001 AA'])));

        // PDF exports
        foreach ([
            $itemCtrl->exportPdf(new Request(['categoryCode' => $this->categoryA->code])),
            $catCtrl->exportPdf(new Request(['keyword' => 'Filter'])),
            $unitCtrl->exportPdf(new Request(['keyword' => 'Liter'])),
            $whCtrl->exportPdf(new Request(['keyword' => 'Surabaya'])),
            $stockCtrl->pdfStock(new Request(['warehouseCode' => $this->warehouseA->code])),
            $stockTxCtrl->pdfStockTransaction(new Request(['warehouseCode' => $this->warehouseA->code])),
            $stockSyncCtrl->exportPdf(new Request(['warehouseCode' => $this->warehouseB->code])),
            $mntCtrl->pdfMaintenance(new Request()),
            $mntCtrl->pdfMaintenance(new Request(['plateNumber' => 'L 1001 AA', 'itemCode' => $this->itemA->code, 'startDate' => '2026-10-01', 'endDate' => '2026-10-31'])),
        ] as $pdfResponse) {
            $this->assertPdfResponse($pdfResponse);
        }
    }

    private function assertFilterExportContract(string $html): void
    {
        $this->assertStringContainsString('class="filter-card"', $html);
        $this->assertStringContainsString('id="filterForm"', $html);
        $this->assertStringContainsString('id="export-excel"', $html);
        $this->assertStringContainsString('id="export-pdf"', $html);
        $this->assertStringContainsString('html[data-bs-theme="dark"] .filter-card', $html);
        $this->assertStringContainsString('syncExportUrls()', $html);
    }

    private function assertPdfResponse($response): void
    {
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    private function createMinimalSchema(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('username')->nullable();
            $table->string('password');
            $table->string('roleCode')->nullable();
            $table->string('languange')->default('id');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('role', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('menu', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('nama')->nullable();
            $table->string('parentCode')->nullable();
            $table->string('url')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('role_menu', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('roleCode');
            $table->string('menuCode');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('company_setting', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->nullable();
            $table->string('name')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('bank_account', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('user_bank', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->string('bankCode')->nullable();
            $table->string('accountName')->nullable();
            $table->string('accountNumber')->nullable();
            $table->integer('type')->default(2);
            $table->string('rekening_type')->default('internal');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('live_mutation', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->nullable();
            $table->string('userBankCode');
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->decimal('balance', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('fleet_company', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('type')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('fleet_type', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('fleet', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->string('plateNumber');
            $table->string('fleetTypeCode')->nullable();
            $table->string('fleetCompanyCode')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('supplier', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('pic')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('ppn')->nullable();
            $table->string('pph')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('warehouse', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('address')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('item_category', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('unit', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('type')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('item', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('brandName')->nullable();
            $table->string('categoryCode')->nullable();
            $table->string('warehouseCode')->nullable();
            $table->string('unitCode')->nullable();
            $table->string('supplierCode')->nullable();
            $table->string('type')->default('part');
            $table->decimal('price', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('stock', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->string('itemCode');
            $table->decimal('stockIn', 15, 2)->default(0);
            $table->decimal('stockOut', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('stock_transaction', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->string('itemCode');
            $table->string('warehouseCode')->nullable();
            $table->string('transactionCode')->nullable();
            $table->string('transactionDetailCode')->nullable();
            $table->decimal('qtyIn', 15, 2)->default(0);
            $table->decimal('qtyOut', 15, 2)->default(0);
            $table->dateTime('date')->nullable();
            $table->string('transactionType')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('purchase_status', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('code')->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('purchase', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->string('supplierCode')->nullable();
            $table->string('warehouseCode')->nullable();
            $table->boolean('is_direct')->default(false);
            $table->string('fleetCode')->nullable();
            $table->date('date')->nullable();
            $table->time('time')->nullable();
            $table->date('dueDate')->nullable();
            $table->date('receivedDate')->nullable();
            $table->date('paymentDate')->nullable();
            $table->integer('status')->default(0);
            $table->string('paymentType')->nullable();
            $table->string('transferUserBankCode')->nullable();
            $table->string('paymentCode')->nullable();
            $table->string('userBankCode')->nullable();
            $table->string('paymentStatus')->default('Unpaid');
            $table->decimal('paidAmount', 15, 2)->default(0);
            $table->decimal('nominal', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('purchase_detail', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->string('purchaseCode');
            $table->string('itemCode');
            $table->decimal('qty', 15, 2)->default(0);
            $table->decimal('receivedQty', 15, 2)->nullable();
            $table->decimal('qtyUsed', 15, 2)->default(0);
            $table->decimal('price', 15, 2)->default(0);
            $table->integer('status')->default(1);
            $table->string('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('supplier_payment_batch', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->string('request_key')->nullable();
            $table->string('payload_hash')->nullable();
            $table->string('status')->default('active');
            $table->date('payment_date')->nullable();
            $table->string('user_bank_code')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->integer('purchase_count')->default(0);
            $table->integer('fully_paid_count')->default(0);
            $table->integer('partial_count')->default(0);
            $table->text('description')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('purchase_payment_history', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('purchaseCode');
            $table->string('batch_code')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->date('paymentDate')->nullable();
            $table->string('userBankCode')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('maintenance', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->date('date')->nullable();
            $table->time('time')->nullable();
            $table->string('fleetCode')->nullable();
            $table->string('warehouseCode')->nullable();
            $table->integer('status')->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('maintenance_detail', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->string('maintenanceCode');
            $table->string('itemCode');
            $table->string('qty')->default('0');
            $table->string('price')->default('0');
            $table->string('total')->default('0');
            $table->string('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('maintenance_fifo', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->string('maintenanceDetailCode');
            $table->string('purchaseDetailCode');
            $table->decimal('qty', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('maintenance_purchase', function (Blueprint $table) {
            $table->id();
            $table->string('maintenance_id');
            $table->string('purchase_id');
            $table->timestamps();
        });
    }

    private function seedFixtures(): void
    {
        Role::create([
            'id' => (string) Str::uuid(),
            'code' => 'SPRADMIN',
            'name' => 'Super Admin',
        ]);

        $this->user = User::create([
            'code' => 'USR-01',
            'name' => 'Admin Test',
            'username' => 'admin_test',
            'password' => bcrypt('password'),
            'roleCode' => 'SPRADMIN',
            'languange' => 'id',
        ]);

        $bank = BankAccount::create(['code' => 'BCA', 'name' => 'Bank BCA']);
        UserBank::create([
            'code' => 'UB-001',
            'bankCode' => $bank->code,
            'accountName' => 'PT PHL',
            'accountNumber' => '1234567890',
            'type' => 2,
            'rekening_type' => 'internal',
        ]);

        $this->supplierA = Supplier::create(['code' => 'SUP-A', 'name' => 'Toko Sparepart Jaya']);
        $this->supplierB = Supplier::create(['code' => 'SUP-B', 'name' => 'Bengkel Sumber Makmur']);

        $this->warehouseA = Warehouse::create(['code' => 'WH-SBY', 'name' => 'Gudang Surabaya', 'address' => 'Jl. Surabaya No. 1']);
        $this->warehouseB = Warehouse::create(['code' => 'WH-JKT', 'name' => 'Gudang Jakarta', 'address' => 'Jl. Jakarta No. 2']);

        $this->categoryA = ItemCategory::create(['code' => 'CAT-01', 'name' => 'Filter & Oli']);
        $this->categoryB = ItemCategory::create(['code' => 'CAT-02', 'name' => 'Jasa Servis']);

        $this->unitPcs = Unit::create(['code' => 'UNT-01', 'name' => 'Pcs', 'type' => 'Inventory']);
        $this->unitLiter = Unit::create(['code' => 'UNT-02', 'name' => 'Liter', 'type' => 'Inventory']);

        $this->itemA = Item::create([
            'code' => 'ITM-01',
            'name' => 'Filter Oli Hino',
            'categoryCode' => $this->categoryA->code,
            'warehouseCode' => $this->warehouseA->code,
            'unitCode' => $this->unitPcs->code,
            'supplierCode' => $this->supplierA->code,
            'type' => Item::TYPE_PART,
            'price' => 250000,
        ]);

        $this->itemB = Item::create([
            'code' => 'ITM-02',
            'name' => 'Oli Mesin 15W-40',
            'categoryCode' => $this->categoryB->code,
            'warehouseCode' => $this->warehouseB->code,
            'unitCode' => $this->unitLiter->code,
            'supplierCode' => $this->supplierB->code,
            'type' => Item::TYPE_JASA,
            'price' => 100000,
        ]);

        $fc = FleetCompany::create(['code' => 'FC-01', 'name' => 'PT PHL', 'type' => 'Internal']);
        $ft = FleetType::create(['code' => 'FT-01', 'name' => 'Wingbox']);

        $this->fleetA = Fleet::create([
            'code' => 'FLT-A',
            'plateNumber' => 'L 1001 AA',
            'fleetTypeCode' => $ft->code,
            'fleetCompanyCode' => $fc->code,
        ]);

        $this->fleetB = Fleet::create([
            'code' => 'FLT-B',
            'plateNumber' => 'B 2002 BB',
            'fleetTypeCode' => $ft->code,
            'fleetCompanyCode' => $fc->code,
        ]);
    }
}
