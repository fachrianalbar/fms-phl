<?php

namespace Tests\Feature;

use App\Models\Data\Route;
use App\Models\Data\RouteDetail;
use App\Models\Data\TonaseBonus;
use App\Models\Finance\Invoice;
use App\Models\Finance\InvoiceDetail;
use App\Models\Finance\OrderPayment;
use App\Models\Inventory\Item;
use App\Models\Master\CostComponent;
use App\Models\Master\Fleet;
use App\Models\Master\FleetCompany;
use App\Models\Operational\Order;
use App\Models\Operational\OrderCost;
use App\Models\Role;
use App\Models\User;
use App\Models\Warehouse\Maintenance;
use App\Models\Warehouse\MaintenanceDetail;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProfitLossReportTest extends TestCase
{
    protected User $adminUser;

    protected User $unauthorizedUser;

    protected FleetCompany $companyA;

    protected FleetCompany $companyB;

    protected Fleet $fleetA;

    protected Fleet $fleetB;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        $this->createMinimalSchema();
        $this->seedUsersAndReportData();
    }

    protected function tearDown(): void
    {
        $tables = [
            'maintenance_detail',
            'maintenance',
            'item',
            'order_payment',
            'invoice_detail',
            'invoice',
            'tonase_bonus',
            'order_cost',
            'order',
            'route_detail',
            'route',
            'cost_component',
            'location',
            'material',
            'customer',
            'employee',
            'fleet_brand',
            'fleet_type',
            'fleet',
            'fleet_company',
            'activity_log',
            'company_setting',
            'settings',
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

    public function test_guest_and_unauthorized_role_cannot_access_profit_loss_endpoints(): void
    {
        $this->get(route('report.profit-loss.index'))
            ->assertStatus(302)
            ->assertRedirect('/');

        $this->actingAs($this->unauthorizedUser);

        $this->get(route('report.profit-loss.index'))->assertForbidden();
        $this->get(route('report.profit-loss.show', $this->fleetA->id))->assertForbidden();
        $this->get(route('report.profit-loss.excel-profit-loss'))->assertForbidden();
        $this->get(route('dt.profit-loss'), ['X-Requested-With' => 'XMLHttpRequest'])->assertForbidden();
        $this->get(route('dt.profit-loss-order', ['fleetCode' => $this->fleetA->code]), ['X-Requested-With' => 'XMLHttpRequest'])->assertForbidden();
        $this->get(route('dt.profit-loss-maintenance', ['fleetCode' => $this->fleetA->code]), ['X-Requested-With' => 'XMLHttpRequest'])->assertForbidden();
        $this->get(route('ajax.profit-loss-summary', ['id' => $this->fleetA->code, 'fleetCode' => $this->fleetA->code]))->assertForbidden();
    }

    public function test_authorized_user_can_view_index_show_and_excel_export(): void
    {
        $this->actingAs($this->adminUser);

        $indexResponse = $this->get(route('report.profit-loss.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Laporan Laba Rugi');
        $indexResponse->assertSee('Perusahaan Kendaraan');
        $indexResponse->assertSee('Pajak (PPh)');
        $indexResponse->assertSee('Laba Bersih');

        $showResponse = $this->get(route('report.profit-loss.show', $this->fleetA->id));
        $showResponse->assertOk();
        $showResponse->assertSee('B 1111 AAA');
        $showResponse->assertSee('PT Armada Satu');
        $showResponse->assertSee('Rincian Biaya Order');
        $showResponse->assertSee('Rincian Perawatan');

        $excelResponse = $this->get(route('report.profit-loss.excel-profit-loss', [
            'fleetCompanyCode' => $this->companyA->code,
        ]));
        $excelResponse->assertOk();
        $this->assertStringContainsString(
            'Laporan-Laba-Rugi.xlsx',
            (string) $excelResponse->headers->get('content-disposition')
        );
    }

    public function test_datatable_and_filters_return_accurate_summary_and_null_safe_detail_datatables(): void
    {
        $this->actingAs($this->adminUser);

        // Filter by fleetCompanyCode (Company A only) and date range (2026-10-01..2026-10-31)
        $dtResponse = $this->getJson(route('dt.profit-loss', [
            'fleetCompanyCode' => $this->companyA->code,
            'startDate' => '2026-10-01',
            'endDate' => '2026-10-31',
        ]), ['X-Requested-With' => 'XMLHttpRequest']);

        $dtResponse->assertOk();
        $payload = $dtResponse->json();

        $this->assertCount(1, $payload['data']);
        $this->assertSame('B 1111 AAA', $payload['data'][0]['plateNumber']);
        $this->assertSame('PT Armada Satu', $payload['data'][0]['company_name']);
        $this->assertSame('4.000.000', $payload['data'][0]['basic_sales']);
        $this->assertSame('450.000', $payload['data'][0]['order_cost']);
        $this->assertSame('300.000', $payload['data'][0]['maintenance']);
        $this->assertSame('80.000', $payload['data'][0]['tax']);
        $this->assertSame('3.170.000', $payload['data'][0]['net_profit']);

        $this->assertEquals(4_000_000, $payload['summary']['basicSales']);
        $this->assertEquals(450_000, $payload['summary']['orderCost']);
        $this->assertEquals(300_000, $payload['summary']['maintenance']);
        $this->assertEquals(80_000, $payload['summary']['tax']);
        $this->assertEquals(3_170_000, $payload['summary']['netProfit']);

        // Filter by plateNumber (Fleet B)
        $plateResponse = $this->getJson(route('dt.profit-loss', [
            'plateNumber' => 'B 2222 BBB',
        ]), ['X-Requested-With' => 'XMLHttpRequest']);

        $plateResponse->assertOk();
        $platePayload = $plateResponse->json();
        $this->assertCount(1, $platePayload['data']);
        $this->assertSame('B 2222 BBB', $platePayload['data'][0]['plateNumber']);
        $this->assertSame('PT Armada Dua', $platePayload['data'][0]['company_name']);

        // Order & Maintenance datatables (including orders with null route/costComponent and deleted maintenance item)
        $orderDt = $this->getJson(route('dt.profit-loss-order', [
            'fleetCode' => $this->fleetA->code,
            'startDate' => '2026-10-01',
            'endDate' => '2026-10-31',
        ]), ['X-Requested-With' => 'XMLHttpRequest']);
        $orderDt->assertOk();
        $this->assertCount(2, $orderDt->json('data'));

        $maintDt = $this->getJson(route('dt.profit-loss-maintenance', [
            'fleetCode' => $this->fleetA->code,
            'startDate' => '2026-10-01',
            'endDate' => '2026-10-31',
        ]), ['X-Requested-With' => 'XMLHttpRequest']);
        $maintDt->assertOk();
        $this->assertCount(1, $maintDt->json('data'));
        $this->assertArrayNotHasKey('action', $maintDt->json('data.0'));

        // Ajax summary endpoint
        $summaryResponse = $this->getJson(route('ajax.profit-loss-summary', [
            'id' => $this->fleetA->code,
            'fleetCode' => $this->fleetA->code,
            'startDate' => '2026-10-01',
            'endDate' => '2026-10-31',
        ]));

        $summaryResponse->assertOk()
            ->assertJson([
                'basicSales' => 4_000_000,
                'orderCost' => 450_000,
                'maintenance' => 300_000,
                'tax' => 80_000,
                'netProfit' => 3_170_000,
                'totalMargin' => 3_170_000,
            ]);
    }

    private function createMinimalSchema(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('password');
            $table->string('roleCode')->nullable();
            $table->string('languange')->default('id');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('role', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('name');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('menu', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('name');
            $table->string('nama')->nullable();
            $table->string('parentCode')->nullable();
            $table->string('url')->nullable();
            $table->string('icon')->nullable();
            $table->integer('sort')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('role_menu', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->nullable();
            $table->string('roleCode');
            $table->string('menuCode');
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('object');
            $table->string('value')->nullable();
            $table->timestamps();
        });

        Schema::create('company_setting', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->nullable();
            $table->string('name');
            $table->string('owner')->nullable();
            $table->text('address')->nullable();
            $table->string('logo')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('activity_log', function (Blueprint $table) {
            $table->id();
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->string('subject_type')->nullable();
            $table->string('subject_id')->nullable();
            $table->string('event')->nullable();
            $table->string('causer_type')->nullable();
            $table->string('causer_id')->nullable();
            $table->text('properties')->nullable();
            $table->string('batch_uuid')->nullable();
            $table->timestamps();
        });

        Schema::create('fleet_company', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('name');
            $table->string('type')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('fleet_type', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('name');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('fleet_brand', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('name');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('employee', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('name');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('fleet', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('plateNumber');
            $table->string('fleetTypeCode')->nullable();
            $table->string('fleetCompanyCode')->nullable();
            $table->string('fleetBrandCode')->nullable();
            $table->string('driverCode')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('customer', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('name');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('material', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('name');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('location', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('name');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('cost_component', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('name');
            $table->string('type')->nullable();
            $table->decimal('price', 15, 2)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('route', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('name')->nullable();
            $table->decimal('price', 15, 2)->nullable();
            $table->string('routeTypeCode')->nullable();
            $table->string('destinationLocationCode')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('route_detail', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('type')->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->decimal('percentage', 8, 2)->nullable();
            $table->string('componentCode')->nullable();
            $table->string('routeCode')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('order', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('shipmentNumber')->nullable();
            $table->date('orderDate')->nullable();
            $table->string('customerCode')->nullable();
            $table->string('materialCode')->nullable();
            $table->string('routeCode')->nullable();
            $table->float('qty')->nullable();
            $table->string('orderTypeCode')->nullable();
            $table->string('fleetTypeCode')->nullable();
            $table->string('driverCode')->nullable();
            $table->string('driverName')->nullable();
            $table->string('fleetCode')->nullable();
            $table->string('plateNumber')->nullable();
            $table->string('status')->nullable();
            $table->decimal('routeAmount', 15, 2)->nullable();
            $table->decimal('price', 15, 2)->nullable();
            $table->decimal('vendorPrice', 15, 2)->nullable();
            $table->decimal('personalVendorPrice', 15, 2)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('order_cost', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('componentType')->nullable();
            $table->string('componentName')->nullable();
            $table->string('orderCode')->nullable();
            $table->string('driverCode')->nullable();
            $table->decimal('nominal', 15, 2)->nullable();
            $table->string('type')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('tonase_bonus', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->decimal('min', 10, 2);
            $table->decimal('max', 10, 2);
            $table->decimal('value', 15, 2);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('invoice', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('invoiceNumber')->nullable();
            $table->date('invoiceDate')->nullable();
            $table->integer('invoiceAmount')->nullable();
            $table->integer('routeAmount')->nullable();
            $table->integer('onChargeAmount')->nullable();
            $table->integer('ppnAmount')->nullable();
            $table->integer('pphAmount')->nullable();
            $table->string('pphBaseType')->nullable();
            $table->string('customerCode')->nullable();
            $table->integer('status')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('invoice_detail', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('invoiceCode');
            $table->string('orderCode');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('order_payment', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('orderCode');
            $table->float('pph')->nullable();
            $table->float('total')->nullable();
            $table->integer('status')->nullable();
            $table->timestamps();
        });

        Schema::create('maintenance', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->date('date')->nullable();
            $table->string('time')->nullable();
            $table->string('fleetCode')->nullable();
            $table->string('warehouseCode')->nullable();
            $table->integer('status')->default(0);
            $table->decimal('grand_total', 15, 2)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('item', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('name');
            $table->decimal('price', 15, 2)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('maintenance_detail', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->decimal('qty', 10, 1)->nullable();
            $table->string('itemCode')->nullable();
            $table->string('maintenanceCode')->nullable();
            $table->string('status')->nullable();
            $table->decimal('price', 15, 2)->nullable();
            $table->decimal('total', 15, 2)->nullable();
            $table->text('description')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    private function seedUsersAndReportData(): void
    {
        Role::create(['code' => 'SPRADMIN', 'name' => 'System Administrator']);
        Role::create(['code' => 'DO', 'name' => 'Operational']);

        $this->adminUser = User::create([
            'code' => 'USR-SPRADMIN',
            'name' => 'Super Admin',
            'username' => 'superadmin_pl',
            'password' => bcrypt('password'),
            'roleCode' => 'SPRADMIN',
            'languange' => 'id',
        ]);

        $this->unauthorizedUser = User::create([
            'code' => 'USR-DO',
            'name' => 'Operator DO',
            'username' => 'operator_pl',
            'password' => bcrypt('password'),
            'roleCode' => 'DO',
            'languange' => 'id',
        ]);

        $this->companyA = FleetCompany::create([
            'code' => 'FC-01',
            'name' => 'PT Armada Satu',
            'type' => 'Internal',
        ]);

        $this->companyB = FleetCompany::create([
            'code' => 'FC-02',
            'name' => 'PT Armada Dua',
            'type' => 'External',
        ]);

        $this->fleetA = Fleet::create([
            'code' => 'FLT-01',
            'plateNumber' => 'B 1111 AAA',
            'fleetCompanyCode' => $this->companyA->code,
        ]);

        $this->fleetB = Fleet::create([
            'code' => 'FLT-02',
            'plateNumber' => 'B 2222 BBB',
            'fleetCompanyCode' => $this->companyB->code,
        ]);

        $allowanceComp = CostComponent::create([
            'code' => 'CC-ALW',
            'name' => 'Uang Jalan',
            'type' => 'Allowance',
            'price' => 200_000,
        ]);

        $route = Route::create([
            'code' => 'RT-01',
            'name' => 'Jakarta - Bandung',
            'price' => 3_000_000,
        ]);

        RouteDetail::create([
            'code' => 'RD-01',
            'routeCode' => $route->code,
            'componentCode' => $allowanceComp->code,
            'amount' => 200_000,
            'percentage' => 0,
        ]);

        // Missing costComponent route detail to ensure null-safety
        RouteDetail::create([
            'code' => 'RD-02',
            'routeCode' => $route->code,
            'componentCode' => 'CC-MISSING',
            'amount' => 50_000,
            'percentage' => 0,
        ]);

        TonaseBonus::create([
            'code' => 'TB-01',
            'min' => 5,
            'max' => 15,
            'value' => 100_000,
        ]);

        // Order 1 (in Oct 2026): vendorPrice 2.5M, allowance 200k, addCost 150k, tonase 100k, invoice pph 50k
        $order1 = Order::create([
            'code' => 'ORD-01',
            'shipmentNumber' => 'SHP-001',
            'orderDate' => '2026-10-10',
            'fleetCode' => $this->fleetA->code,
            'plateNumber' => $this->fleetA->plateNumber,
            'routeCode' => $route->code,
            'qty' => 10,
            'routeAmount' => 3_000_000,
            'vendorPrice' => 2_500_000,
            'personalVendorPrice' => 1_500_000,
        ]);

        OrderCost::create([
            'code' => 'OC-01',
            'orderCode' => $order1->code,
            'componentType' => $allowanceComp->code,
            'nominal' => 150_000,
            'type' => 'On Charge',
        ]);

        $invoice1 = Invoice::create([
            'code' => 'INV-01',
            'invoiceNumber' => 'INV/2026/001',
            'pphAmount' => 50_000,
            'pphBaseType' => 'subtotal',
        ]);

        InvoiceDetail::create([
            'code' => 'INVD-01',
            'invoiceCode' => $invoice1->code,
            'orderCode' => $order1->code,
        ]);

        // Order 2 (in Oct 2026): null route, personalVendorPrice fallback 1.5M, direct payment pph 30k
        $order2 = Order::create([
            'code' => 'ORD-02',
            'shipmentNumber' => 'SHP-002',
            'orderDate' => '2026-10-20',
            'fleetCode' => $this->fleetA->code,
            'plateNumber' => $this->fleetA->plateNumber,
            'routeCode' => 'RT-MISSING',
            'qty' => 2,
            'vendorPrice' => 0,
            'personalVendorPrice' => 1_500_000,
        ]);

        OrderPayment::create([
            'code' => 'OP-01',
            'orderCode' => $order2->code,
            'pph' => 30_000,
            'total' => 1_470_000,
            'status' => 1,
        ]);

        // Order 3 (outside Oct 2026 date filter)
        Order::create([
            'code' => 'ORD-03',
            'shipmentNumber' => 'SHP-003',
            'orderDate' => '2026-09-15',
            'fleetCode' => $this->fleetA->code,
            'plateNumber' => $this->fleetA->plateNumber,
            'qty' => 2,
            'vendorPrice' => 9_000_000,
        ]);

        // Fleet B order
        Order::create([
            'code' => 'ORD-04',
            'shipmentNumber' => 'SHP-004',
            'orderDate' => '2026-10-12',
            'fleetCode' => $this->fleetB->code,
            'plateNumber' => $this->fleetB->plateNumber,
            'qty' => 1,
            'vendorPrice' => 1_200_000,
        ]);

        // Maintenance for Fleet A in Oct 2026 (valid item 300k + missing item ignored)
        $item = Item::create([
            'code' => 'ITM-01',
            'name' => 'Oli Mesin',
            'price' => 150_000,
        ]);

        $maint = Maintenance::create([
            'code' => 'MNT-01',
            'date' => '2026-10-15',
            'time' => '10:00',
            'fleetCode' => $this->fleetA->code,
            'status' => 0,
        ]);

        MaintenanceDetail::create([
            'code' => 'MNTD-01',
            'maintenanceCode' => $maint->code,
            'itemCode' => $item->code,
            'qty' => 2,
            'price' => 150_000,
        ]);

        MaintenanceDetail::create([
            'code' => 'MNTD-02',
            'maintenanceCode' => $maint->code,
            'itemCode' => 'ITM-MISSING',
            'qty' => 1,
            'price' => 0,
        ]);
    }
}
