<?php

namespace Tests\Feature;

use App\Models\Data\Route;
use App\Models\Finance\Invoice;
use App\Models\Finance\InvoiceDetail;
use App\Models\Finance\OrderPayment;
use App\Models\Master\Company;
use App\Models\Master\CostComponent;
use App\Models\Master\Customer;
use App\Models\Master\Employee;
use App\Models\Master\Fleet;
use App\Models\Master\FleetCompany;
use App\Models\Master\FleetType;
use App\Models\Master\Location;
use App\Models\Master\Material;
use App\Models\Master\OrderType;
use App\Models\Master\Unit;
use App\Models\Operational\Order;
use App\Models\Operational\OrderCost;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrderDetailReportTest extends TestCase
{
    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        $this->createMinimalSchema();
        $this->seedReportFixtures();
    }

    protected function tearDown(): void
    {
        $tables = [
            'order_payment',
            'invoice_detail',
            'invoice',
            'order_cost',
            'order',
            'route',
            'cost_component',
            'order_type',
            'unit',
            'material',
            'location',
            'district',
            'city',
            'province',
            'customer',
            'employee',
            'position',
            'fleet',
            'fleet_type',
            'fleet_brand',
            'fleet_company',
            'company',
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

    public function test_index_page_renders_new_columns(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get(route('report.order-detail.index'));
        $response->assertOk();

        $response->assertSee('Qty');
        $response->assertSee('Harga Satuan');
        $response->assertSee('Total Omset');
        $response->assertSee('Komisi');
        $response->assertSee('Gaji');
        $response->assertSee('Laba Kotor');
        $response->assertSee('Potongan Pajak (PPh)');
        $response->assertSee('Laba Bersih');
    }

    public function test_datatable_computes_omset_cross_internal_costs_tax_and_net_profit(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->getJson(route('dt.order-detail', [
            'startDate' => '2026-11-01',
            'endDate' => '2026-11-30',
        ]), ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertOk();
        $rows = collect($response->json('data'))->keyBy(
            fn (array $row) => strip_tags((string) $row['shipmentNumber'])
        );

        $this->assertCount(3, $rows);

        // 1. Internal Sama PT (PHL -> PHL): routeAmount 2.500.000, On Charge 200.000, Komisi 100.000, Gaji 300.000, Solar 250.000
        //    total_cost = 650.000, gross_profit = 2.500.000 + 200.000 - 650.000 = 2.050.000
        //    Invoice PPh 45.000 prorated across ORD-SAME (weight 2.700.000) + ORD-PEER (weight 1.800.000) => 45.000 * 2.7 / 4.5 = 27.000
        //    net_profit = 2.050.000 - 27.000 = 2.023.000
        $samePt = $rows->get('SHP-SAME');
        $this->assertNotNull($samePt);
        $this->assertSame('10', $samePt['qty']);
        $this->assertStringContainsString('Rp 250.000', $samePt['unit_price']);
        $this->assertStringContainsString('Rp 2.500.000', $samePt['sales']);
        $this->assertStringNotContainsString('Antar PT', $samePt['sales']);
        $this->assertStringContainsString('Komisi Sales', $samePt['commission']);
        $this->assertStringContainsString('Rp 100.000', $samePt['commission']);
        $this->assertStringContainsString('Gaji Supir', $samePt['salary']);
        $this->assertStringContainsString('Rp 300.000', $samePt['salary']);
        $this->assertStringContainsString('Uang Solar', $samePt['cost_detail']);
        $this->assertStringNotContainsString('Komisi Sales', $samePt['cost_detail']);
        $this->assertStringNotContainsString('Gaji Supir', $samePt['cost_detail']);
        $this->assertStringContainsString('Rp 650.000', $samePt['total_cost']);
        $this->assertStringContainsString('Rp 2.050.000', $samePt['gross_profit']);
        $this->assertStringContainsString('Rp 27.000', $samePt['tax']);
        $this->assertStringContainsString('Rp 2.023.000', $samePt['profit']);

        // 2. Internal Beda PT (PHL customer + WTMS fleet): personalVendorPrice 1.600.000 (4 x 400.000), Direct Payment PPh 32.000
        $crossPt = $rows->get('SHP-CROSS');
        $this->assertNotNull($crossPt);
        $this->assertSame('4', $crossPt['qty']);
        $this->assertStringContainsString('Rp 400.000', $crossPt['unit_price']);
        $this->assertStringContainsString('Rp 1.600.000', $crossPt['sales']);
        $this->assertStringContainsString('Antar PT', $crossPt['sales']);
        $this->assertStringContainsString('Rp 1.600.000', $crossPt['gross_profit']);
        $this->assertStringContainsString('Rp 32.000', $crossPt['tax']);
        $this->assertStringContainsString('Rp 1.568.000', $crossPt['profit']);

        // 3. Peer order in multi-order invoice (SHP-PEER): tax = 45.000 - 27.000 = 18.000
        $peer = $rows->get('SHP-PEER');
        $this->assertNotNull($peer);
        $this->assertStringContainsString('Rp 18.000', $peer['tax']);
        $this->assertStringContainsString('Rp 1.782.000', $peer['profit']);
    }

    public function test_datatable_global_search_supports_text_cost_components_numbers_and_empty_results(): void
    {
        $this->actingAs($this->adminUser);

        $searchEndpoint = function (string $keyword) {
            $columns = [
                'DT_RowIndex',
                'shipmentNumber',
                'orderDate',
                'customer_name',
                'origin_name',
                'destination_name',
                'plate_number',
                'driver_name',
                'qty',
                'unit_price',
                'sales',
                'income',
                'commission',
                'salary',
                'cost_detail',
                'total_cost',
                'gross_profit',
                'tax',
                'profit',
            ];

            $queryColumns = [];
            foreach ($columns as $idx => $name) {
                $queryColumns[] = [
                    'data' => $name,
                    'name' => $name,
                    'searchable' => $idx === 0 ? 'false' : 'true',
                    'orderable' => in_array($idx, [0, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18], true) ? 'false' : 'true',
                ];
            }

            return $this->getJson(route('dt.order-detail', [
                'draw' => 1,
                'start' => 0,
                'length' => 25,
                'columns' => $queryColumns,
                'search' => ['value' => $keyword, 'regex' => 'false'],
            ]), ['X-Requested-With' => 'XMLHttpRequest']);
        };

        // 1. Plain text search (shipment number / driver)
        $textRes = $searchEndpoint('SHP-CROSS');
        $textRes->assertOk()->assertJsonMissingPath('error');
        $this->assertCount(1, $textRes->json('data'));

        // 2. Cost component name search
        $costRes = $searchEndpoint('Komisi Sales');
        $costRes->assertOk()->assertJsonMissingPath('error');
        $this->assertCount(1, $costRes->json('data'));
        $this->assertStringContainsString('SHP-SAME', $costRes->json('data.0.shipmentNumber'));

        // 3. Numeric amount search
        $numRes = $searchEndpoint('1600000');
        $numRes->assertOk()->assertJsonMissingPath('error');
        $this->assertCount(1, $numRes->json('data'));
        $this->assertStringContainsString('SHP-CROSS', $numRes->json('data.0.shipmentNumber'));

        // 4. Non-existent keyword returns 0 results with HTTP 200 (no SQL error)
        $emptyRes = $searchEndpoint('TIDAK-ADA-DATA-XYZ');
        $emptyRes->assertOk()->assertJsonMissingPath('error');
        $this->assertCount(0, $emptyRes->json('data'));
    }

    public function test_excel_direct_chunked_and_pdf_exports_succeed(): void
    {
        $this->actingAs($this->adminUser);

        // 1. Direct Excel export
        $directExcel = $this->get(route('report.order-detail.excel-order-detail'));
        $directExcel->assertOk();
        $this->assertStringContainsString(
            'Order-Detail-Report.xlsx',
            (string) $directExcel->headers->get('content-disposition')
        );

        // 2. Chunked Excel export flow
        $initRes = $this->getJson(route('report.order-detail.excel-order-detail-init'));
        $initRes->assertOk();
        $downloadId = $initRes->json('download_id');
        $this->assertSame(3, $initRes->json('total'));
        $this->assertNotEmpty($downloadId);

        $chunkRes = $this->getJson(route('report.order-detail.excel-order-detail-chunk', [
            'download_id' => $downloadId,
            'offset' => 0,
            'limit' => 100,
        ]));
        $chunkRes->assertOk()->assertJson(['success' => true]);

        $cachedRows = Cache::get('excel_export_orders_'.$downloadId);
        $this->assertIsArray($cachedRows);
        $this->assertCount(3, $cachedRows);
        $this->assertIsArray($cachedRows[0]);
        $this->assertArrayHasKey('net_profit', $cachedRows[0]);

        $downloadRes = $this->get(route('report.order-detail.excel-order-detail-download', [
            'download_id' => $downloadId,
            'download_token' => 'tok_test_123',
        ]));
        $downloadRes->assertOk();
        $this->assertStringContainsString(
            'Order-Detail-Report.xlsx',
            (string) $downloadRes->headers->get('content-disposition')
        );
        $downloadRes->assertPlainCookie('download_token', 'tok_test_123');

        // 3. PDF export
        $pdfRes = $this->get(route('report.order-detail.pdf-order-detail'));
        $pdfRes->assertOk();
        $this->assertStringContainsString(
            'application/pdf',
            strtolower((string) $pdfRes->headers->get('content-type'))
        );
        $this->assertStringStartsWith('%PDF', $pdfRes->getContent());
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

        Schema::create('company', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('name');
            $table->string('format')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('fleet_company', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('name');
            $table->string('type')->nullable();
            $table->string('companyCode')->nullable();
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

        Schema::create('position', function (Blueprint $table) {
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
            $table->string('positionCode')->nullable();
            $table->integer('status')->default(1);
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
            $table->string('companyCode')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('province', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('name');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('city', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('name');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('district', function (Blueprint $table) {
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
            $table->string('provinceCode')->nullable();
            $table->string('cityCode')->nullable();
            $table->string('districtCode')->nullable();
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

        Schema::create('unit', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('name');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('order_type', function (Blueprint $table) {
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
            $table->decimal('vendorPrice', 15, 2)->nullable();
            $table->decimal('personalVendorPrice', 15, 2)->nullable();
            $table->string('routeTypeCode')->nullable();
            $table->string('originLocationCode')->nullable();
            $table->string('destinationLocationCode')->nullable();
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
            $table->string('unitCode')->nullable();
            $table->string('routeCode')->nullable();
            $table->float('qty')->nullable();
            $table->string('orderTypeCode')->nullable();
            $table->string('fleetTypeCode')->nullable();
            $table->string('driverCode')->nullable();
            $table->string('driverName')->nullable();
            $table->string('fleetCode')->nullable();
            $table->string('plateNumber')->nullable();
            $table->integer('status')->nullable();
            $table->decimal('routeAmount', 15, 2)->nullable();
            $table->decimal('price', 15, 2)->nullable();
            $table->decimal('vendorPrice', 15, 2)->nullable();
            $table->decimal('vendorPriceSingle', 15, 2)->nullable();
            $table->decimal('personalVendorPrice', 15, 2)->nullable();
            $table->decimal('personalVendorPriceSingle', 15, 2)->nullable();
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
    }

    private function seedReportFixtures(): void
    {
        Role::create(['code' => 'SPRADMIN', 'name' => 'System Administrator']);

        $this->adminUser = User::create([
            'code' => 'USR-SPRADMIN',
            'name' => 'Super Admin',
            'username' => 'superadmin_od',
            'password' => bcrypt('password'),
            'roleCode' => 'SPRADMIN',
            'languange' => 'id',
        ]);

        $companyPhl = Company::create(['code' => 'PHL', 'name' => 'PT PHL']);
        $companyWtms = Company::create(['code' => 'WTMS', 'name' => 'PT WTMS']);

        $fcPhl = FleetCompany::create([
            'code' => 'FC-PHL',
            'name' => 'Armada PHL',
            'type' => 'Internal',
            'companyCode' => $companyPhl->code,
        ]);

        $fcWtms = FleetCompany::create([
            'code' => 'FC-WTMS',
            'name' => 'Armada WTMS',
            'type' => 'Internal',
            'companyCode' => $companyWtms->code,
        ]);

        $fleetType = FleetType::create(['code' => 'FT-01', 'name' => 'Tronton']);
        $driver = Employee::create(['code' => 'EMP-01', 'name' => 'Ahmad Driver']);
        $material = Material::create(['code' => 'MAT-01', 'name' => 'Batu Split']);
        $unit = Unit::create(['code' => 'UNT-01', 'name' => 'Ton']);
        $orderType = OrderType::create(['code' => 'OT-01', 'name' => 'Reguler']);

        $origin = Location::create(['code' => 'LOC-ORI', 'name' => 'Jakarta']);
        $dest = Location::create(['code' => 'LOC-DST', 'name' => 'Surabaya']);

        $route = Route::create([
            'code' => 'RT-01',
            'name' => 'Jakarta - Surabaya',
            'price' => 250_000,
            'originLocationCode' => $origin->code,
            'destinationLocationCode' => $dest->code,
        ]);

        $fleetSame = Fleet::create([
            'code' => 'FLT-SAME',
            'plateNumber' => 'B 1111 PHL',
            'fleetTypeCode' => $fleetType->code,
            'fleetCompanyCode' => $fcPhl->code,
        ]);

        $fleetCross = Fleet::create([
            'code' => 'FLT-CROSS',
            'plateNumber' => 'B 2222 WTM',
            'fleetTypeCode' => $fleetType->code,
            'fleetCompanyCode' => $fcWtms->code,
        ]);

        $customerPhl = Customer::create([
            'code' => 'CUST-PHL',
            'name' => 'PT Customer PHL',
            'companyCode' => $companyPhl->code,
        ]);

        $compOnCharge = CostComponent::create(['code' => 'CC-INC', 'name' => 'Bongkar Muat', 'type' => 'On Charge', 'price' => 200_000]);
        $compCommission = CostComponent::create(['code' => 'CC-COM', 'name' => 'Komisi Sales', 'type' => 'commission', 'price' => 100_000]);
        $compSalary = CostComponent::create(['code' => 'CC-SAL', 'name' => 'Gaji Supir', 'type' => 'salary', 'price' => 300_000]);
        $compSolar = CostComponent::create(['code' => 'CC-SOL', 'name' => 'Uang Solar', 'type' => 'Allowance', 'price' => 250_000]);

        // Order 1: Internal Sama PT (status 4), multi-order invoice
        $orderSame = Order::create([
            'code' => 'ORD-SAME',
            'shipmentNumber' => 'SHP-SAME',
            'orderDate' => '2026-11-10',
            'customerCode' => $customerPhl->code,
            'materialCode' => $material->code,
            'unitCode' => $unit->code,
            'routeCode' => $route->code,
            'orderTypeCode' => $orderType->code,
            'fleetTypeCode' => $fleetType->code,
            'driverCode' => $driver->code,
            'driverName' => $driver->name,
            'fleetCode' => $fleetSame->code,
            'plateNumber' => $fleetSame->plateNumber,
            'status' => 4,
            'qty' => 10,
            'price' => 250_000,
            'routeAmount' => 2_500_000,
        ]);

        OrderCost::create(['code' => 'OC-1', 'orderCode' => $orderSame->code, 'componentType' => $compOnCharge->code, 'componentName' => 'Bongkar Muat', 'nominal' => 200_000, 'type' => 'On Charge']);
        OrderCost::create(['code' => 'OC-2', 'orderCode' => $orderSame->code, 'componentType' => $compCommission->code, 'componentName' => 'Komisi Sales', 'nominal' => 100_000, 'type' => 'Operational']);
        OrderCost::create(['code' => 'OC-3', 'orderCode' => $orderSame->code, 'componentType' => $compSalary->code, 'componentName' => 'Gaji Supir', 'nominal' => 300_000, 'type' => 'Operational']);
        OrderCost::create(['code' => 'OC-4', 'orderCode' => $orderSame->code, 'componentType' => $compSolar->code, 'componentName' => 'Uang Solar', 'nominal' => 250_000, 'type' => 'Operational']);

        // Order 2: Peer order in same multi-order invoice (status 5)
        $orderPeer = Order::create([
            'code' => 'ORD-PEER',
            'shipmentNumber' => 'SHP-PEER',
            'orderDate' => '2026-11-12',
            'customerCode' => $customerPhl->code,
            'materialCode' => $material->code,
            'unitCode' => $unit->code,
            'routeCode' => $route->code,
            'orderTypeCode' => $orderType->code,
            'fleetTypeCode' => $fleetType->code,
            'driverCode' => $driver->code,
            'driverName' => $driver->name,
            'fleetCode' => $fleetSame->code,
            'plateNumber' => $fleetSame->plateNumber,
            'status' => 5,
            'qty' => 6,
            'price' => 300_000,
            'routeAmount' => 1_800_000,
        ]);

        $invoice = Invoice::create([
            'code' => 'INV-01',
            'invoiceNumber' => 'INV/2026/001',
            'pphAmount' => 45_000,
            'pphBaseType' => 'subtotal',
        ]);
        InvoiceDetail::create(['code' => 'INVD-1', 'invoiceCode' => $invoice->code, 'orderCode' => $orderSame->code]);
        InvoiceDetail::create(['code' => 'INVD-2', 'invoiceCode' => $invoice->code, 'orderCode' => $orderPeer->code]);

        // Order 3: Internal Beda PT (PHL customer + WTMS fleet), Direct Payment PPh 32.000
        $orderCross = Order::create([
            'code' => 'ORD-CROSS',
            'shipmentNumber' => 'SHP-CROSS',
            'orderDate' => '2026-11-15',
            'customerCode' => $customerPhl->code,
            'materialCode' => $material->code,
            'unitCode' => $unit->code,
            'routeCode' => $route->code,
            'orderTypeCode' => $orderType->code,
            'fleetTypeCode' => $fleetType->code,
            'driverCode' => $driver->code,
            'driverName' => $driver->name,
            'fleetCode' => $fleetCross->code,
            'plateNumber' => $fleetCross->plateNumber,
            'status' => 4,
            'qty' => 4,
            'price' => 500_000,
            'routeAmount' => 2_000_000,
            'vendorPrice' => 0,
            'vendorPriceSingle' => 0,
            'personalVendorPrice' => 1_600_000,
            'personalVendorPriceSingle' => 400_000,
        ]);

        OrderPayment::create([
            'code' => 'OP-CROSS',
            'orderCode' => $orderCross->code,
            'pph' => 32_000,
            'total' => 1_968_000,
            'status' => 1,
        ]);

        // Order 4: Draft order (status 1) - must be excluded from Order Detail Report
        Order::create([
            'code' => 'ORD-DRAFT',
            'shipmentNumber' => 'SHP-DRAFT',
            'orderDate' => '2026-11-18',
            'customerCode' => $customerPhl->code,
            'fleetCode' => $fleetSame->code,
            'status' => 1,
            'qty' => 2,
            'routeAmount' => 999_999,
        ]);
    }
}
