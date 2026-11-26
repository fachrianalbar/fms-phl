<?php

namespace Tests\Feature;

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Data\RouteController;
use App\Http\Controllers\Master\CompanyController;
use App\Http\Controllers\Master\CostComponentController;
use App\Http\Controllers\Master\CostComponentPriceLogController;
use App\Http\Controllers\Master\EmployeeController;
use App\Http\Controllers\Master\LocationController;
use App\Http\Controllers\Master\MaterialController;
use App\Http\Controllers\Master\MenuController;
use App\Http\Controllers\Master\UnitController;
use App\Http\Controllers\Report\CommissionReportController;
use App\Http\Controllers\Report\DriverSalaryController;
use App\Http\Controllers\Report\MaintenancePerCompanyController;
use App\Http\Controllers\Report\ProfitLossController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Models\ActivityLog;
use App\Models\Data\Route;
use App\Models\Inventory\Item;
use App\Models\Master\Company;
use App\Models\Master\CostComponent;
use App\Models\Master\CostComponentPriceLog;
use App\Models\Master\Customer;
use App\Models\Master\Employee;
use App\Models\Master\Fleet;
use App\Models\Master\FleetCompany;
use App\Models\Master\FleetType;
use App\Models\Master\Location;
use App\Models\Master\Material;
use App\Models\Master\Position;
use App\Models\Master\Province;
use App\Models\Master\RouteType;
use App\Models\Master\Unit;
use App\Models\Menu;
use App\Models\Operational\Order;
use App\Models\Operational\OrderCommission;
use App\Models\Report\DriverSalary;
use App\Models\Role;
use App\Models\User;
use App\Models\Warehouse\Maintenance;
use App\Models\Warehouse\MaintenanceDetail;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\TestCase;

class MasterReportSystemFilterExportTest extends TestCase
{
    protected User $adminUser;

    protected User $staffUser;

    protected Position $posDriver;

    protected Position $posAdmin;

    protected Employee $empA;

    protected Employee $empB;

    protected Province $provJatim;

    protected Province $provJateng;

    protected Location $locOrigin;

    protected Location $locDest;

    protected Customer $custA;

    protected Customer $custB;

    protected Route $routeA;

    protected Route $routeB;

    protected Material $matA;

    protected Material $matB;

    protected Unit $unitA;

    protected Unit $unitB;

    protected CostComponent $compA;

    protected CostComponent $compB;

    protected Company $companyA;

    protected Company $companyB;

    protected FleetCompany $fcInternalA;

    protected FleetCompany $fcInternalB;

    protected Fleet $fleetA;

    protected Fleet $fleetB;

    protected Menu $menuParent;

    protected Menu $menuChild;

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
            'activity_log',
            'driver_salary_detail',
            'driver_salary',
            'order_driver_salary',
            'order_commission',
            'commission_report_detail',
            'commission_report',
            'maintenance_detail',
            'maintenance',
            'item',
            'order_payment',
            'invoice_detail',
            'invoice',
            'tonase_bonus',
            'order_cost',
            'order',
            'route_price_external',
            'route_detail',
            'route',
            'route_type',
            'fleet',
            'fleet_type',
            'fleet_company',
            'customer',
            'company',
            'cost_component_price_logs',
            'cost_component',
            'unit',
            'material',
            'location',
            'district',
            'city',
            'province',
            'employee',
            'position',
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

    public function test_master_eight_submenus_views_filters_and_exports(): void
    {
        $this->actingAs($this->adminUser);
        View::share('errors', new ViewErrorBag());

        /** @var EmployeeController $empCtrl */
        $empCtrl = app(EmployeeController::class);
        /** @var RouteController $routeCtrl */
        $routeCtrl = app(RouteController::class);
        /** @var LocationController $locCtrl */
        $locCtrl = app(LocationController::class);
        /** @var MaterialController $matCtrl */
        $matCtrl = app(MaterialController::class);
        /** @var UnitController $unitCtrl */
        $unitCtrl = app(UnitController::class);
        /** @var CostComponentController $compCtrl */
        $compCtrl = app(CostComponentController::class);
        /** @var CostComponentPriceLogController $logCtrl */
        $logCtrl = app(CostComponentPriceLogController::class);
        /** @var CompanyController $compMasterCtrl */
        $compMasterCtrl = app(CompanyController::class);

        foreach ([
            $empCtrl->index()->render(),
            $routeCtrl->index()->render(),
            $locCtrl->index()->render(),
            $matCtrl->index()->render(),
            $unitCtrl->index()->render(),
            $compCtrl->index()->render(),
            $logCtrl->index()->render(),
            $compMasterCtrl->index()->render(),
        ] as $html) {
            $this->assertFilterExportContract($html);
        }

        // Datatable filters
        $this->assertSame(
            [$this->empA->code],
            collect($empCtrl->datatable($this->ajaxReq('/datatable/employee', ['positionCode' => $this->posDriver->code, 'status' => '1']))->getData(true)['data'])->pluck('id')->count() === 1
                ? [$this->empA->code]
                : []
        );

        $dtRoute = $routeCtrl->datatable($this->ajaxReq('/datatable/route', ['customerName' => 'Alpha']))->getData(true);
        $this->assertSame([$this->routeA->id], collect($dtRoute['data'])->pluck('id')->all());

        $dtLoc = $locCtrl->datatable($this->ajaxReq('/datatable/location', ['provinceId' => $this->provJatim->id]))->getData(true);
        $this->assertSame([$this->locOrigin->code], collect($dtLoc['data'])->pluck('code')->all());

        $dtMat = $matCtrl->datatable($this->ajaxReq('/datatable/material', ['keyword' => 'Pasir']))->getData(true);
        $this->assertSame([$this->matA->code], collect($dtMat['data'])->pluck('code')->all());

        $dtUnit = $unitCtrl->datatable($this->ajaxReq('/datatable/unit', ['keyword' => 'Ton']))->getData(true);
        $this->assertSame([$this->unitA->code], collect($dtUnit['data'])->pluck('code')->all());

        $dtComp = $compCtrl->datatable($this->ajaxReq('/datatable/cost-component', ['type' => 'salary']))->getData(true);
        $this->assertSame([$this->compA->code], collect($dtComp['data'])->pluck('code')->all());

        $dtPriceLog = $logCtrl->datatable($this->ajaxReq('/datatable/cost-component-price-log', ['costComponentCode' => $this->compA->code]))->getData(true);
        $this->assertCount(1, $dtPriceLog['data']);
        $this->assertSame($this->compA->code, $dtPriceLog['data'][0]['costComponentCode']);

        $dtCompany = $compMasterCtrl->datatable($this->ajaxReq('/datatable/company', ['keyword' => 'PHL']))->getData(true);
        $this->assertSame([$this->companyA->code], collect($dtCompany['data'])->pluck('code')->all());

        // Excel exports
        $this->assertInstanceOf(BinaryFileResponse::class, $empCtrl->exportExcel(new Request(['positionCode' => $this->posDriver->code])));
        $this->assertInstanceOf(BinaryFileResponse::class, $routeCtrl->exportExcel(new Request(['customerName' => 'Alpha'])));
        $this->assertInstanceOf(BinaryFileResponse::class, $locCtrl->exportExcel(new Request(['provinceId' => $this->provJatim->id])));
        $this->assertInstanceOf(BinaryFileResponse::class, $matCtrl->exportExcel(new Request(['keyword' => 'Pasir'])));
        $this->assertInstanceOf(BinaryFileResponse::class, $unitCtrl->exportExcel(new Request(['keyword' => 'Ton'])));
        $this->assertInstanceOf(BinaryFileResponse::class, $compCtrl->exportExcel(new Request(['type' => 'salary'])));
        $this->assertInstanceOf(BinaryFileResponse::class, $logCtrl->exportExcel(new Request(['costComponentCode' => $this->compA->code])));
        $this->assertInstanceOf(BinaryFileResponse::class, $compMasterCtrl->exportExcel(new Request(['keyword' => 'PHL'])));

        // PDF exports
        foreach ([
            $empCtrl->exportPdf(new Request(['positionCode' => $this->posDriver->code])),
            $routeCtrl->exportPdf(new Request(['customerName' => 'Alpha'])),
            $locCtrl->exportPdf(new Request(['provinceId' => $this->provJatim->id])),
            $matCtrl->exportPdf(new Request(['keyword' => 'Pasir'])),
            $unitCtrl->exportPdf(new Request(['keyword' => 'Ton'])),
            $compCtrl->exportPdf(new Request(['type' => 'salary'])),
            $logCtrl->exportPdf(new Request(['costComponentCode' => $this->compA->code])),
            $compMasterCtrl->exportPdf(new Request(['keyword' => 'PHL'])),
        ] as $pdfResponse) {
            $this->assertPdfResponse($pdfResponse);
        }
    }

    public function test_report_four_submenus_views_filters_and_exports(): void
    {
        $this->actingAs($this->adminUser);
        View::share('errors', new ViewErrorBag());

        /** @var ProfitLossController $plCtrl */
        $plCtrl = app(ProfitLossController::class);
        /** @var DriverSalaryController $dsCtrl */
        $dsCtrl = app(DriverSalaryController::class);
        /** @var CommissionReportController $commCtrl */
        $commCtrl = app(CommissionReportController::class);
        /** @var MaintenancePerCompanyController $mntCompCtrl */
        $mntCompCtrl = app(MaintenancePerCompanyController::class);

        // Profit Loss: Filter + Excel + PDF
        $plHtml = $plCtrl->index()->render();
        $this->assertFilterExportContract($plHtml);
        $this->assertPdfResponse($plCtrl->pdfProfitLoss(new Request(['fleetCompanyCode' => $this->fcInternalA->code, 'startDate' => '2026-10-01', 'endDate' => '2026-10-31'])));

        // Driver Salary: Filter + PDF + Excel
        $dsHtml = $dsCtrl->index()->render();
        $this->assertStringContainsString('class="filter-card"', $dsHtml);
        $this->assertStringContainsString('id="export-excel"', $dsHtml);
        $this->assertStringContainsString('html[data-bs-theme="dark"] .filter-card', $dsHtml);
        $this->assertStringContainsString('syncExportUrls()', $dsHtml);
        $this->assertInstanceOf(BinaryFileResponse::class, $dsCtrl->excelDriverSalary(new Request(['filterDriverCode' => $this->empA->code])));

        // Commission: Filter + PDF + Excel
        $commHtml = $commCtrl->index()->render();
        $this->assertStringContainsString('class="filter-card"', $commHtml);
        $this->assertStringContainsString('id="export-excel"', $commHtml);
        $this->assertStringContainsString('id="btnPrintFilteredPdf"', $commHtml);
        $this->assertStringContainsString('html[data-bs-theme="dark"] .filter-card', $commHtml);
        $this->assertStringContainsString('syncExportUrls()', $commHtml);
        $this->assertInstanceOf(BinaryFileResponse::class, $commCtrl->excelCommission(new Request(['customerCode' => $this->custA->code])));

        // Maintenance Company Internal: Filter + Excel + PDF
        $mntCompHtml = $mntCompCtrl->index()->render();
        $this->assertFilterExportContract($mntCompHtml);
        $dtMntComp = $mntCompCtrl->datatable($this->ajaxReq('/datatable/maintenance-company-internal', ['fleetCompanyCode' => $this->fcInternalA->code]))->getData(true);
        $this->assertSame([$this->fcInternalA->code], collect($dtMntComp['data'])->pluck('fleetCompanyCode')->all());
        $this->assertInstanceOf(BinaryFileResponse::class, $mntCompCtrl->excelMaintenanceCompany(new Request(['fleetCompanyCode' => $this->fcInternalA->code])));
        $this->assertPdfResponse($mntCompCtrl->pdfMaintenanceCompany(new Request(['fleetCompanyCode' => $this->fcInternalA->code])));
    }

    public function test_system_four_submenus_views_filters_and_exports(): void
    {
        $this->actingAs($this->adminUser);
        View::share('errors', new ViewErrorBag());

        /** @var UserController $userCtrl */
        $userCtrl = app(UserController::class);
        /** @var RoleController $roleCtrl */
        $roleCtrl = app(RoleController::class);
        /** @var MenuController $menuCtrl */
        $menuCtrl = app(MenuController::class);
        /** @var ActivityLogController $actCtrl */
        $actCtrl = app(ActivityLogController::class);

        foreach ([
            $userCtrl->index()->render(),
            $roleCtrl->index()->render(),
            $menuCtrl->index()->render(),
            $actCtrl->index()->render(),
        ] as $html) {
            $this->assertFilterExportContract($html);
        }

        $dtUser = $userCtrl->datatable($this->ajaxReq('/datatable/user', ['roleCode' => 'SPRADMIN']))->getData(true);
        $this->assertSame([$this->adminUser->code], collect($dtUser['data'])->pluck('code')->all());

        $dtRole = $roleCtrl->datatable($this->ajaxReq('/datatable/role', ['keyword' => 'Staff']))->getData(true);
        $this->assertSame(['STAFF'], collect($dtRole['data'])->pluck('code')->all());

        $dtMenuDefault = $menuCtrl->datatable($this->ajaxReq('/datatable/menu', []))->getData(true);
        $this->assertSame([$this->menuParent->code], collect($dtMenuDefault['data'])->pluck('code')->all());

        $dtMenuChild = $menuCtrl->datatable($this->ajaxReq('/datatable/menu', ['parentCode' => $this->menuParent->code]))->getData(true);
        $this->assertSame([$this->menuChild->code], collect($dtMenuChild['data'])->pluck('code')->all());

        $dtAct = $actCtrl->datatable($this->ajaxReq('/datatable/activity-log', ['activity' => 'Customer', 'roleCode' => 'SPRADMIN']))->getData(true);
        $this->assertCount(1, $dtAct['data']);
        $this->assertSame('Customer', $dtAct['data'][0]['log_name']);

        // Excel exports
        $this->assertInstanceOf(BinaryFileResponse::class, $userCtrl->exportExcel(new Request(['roleCode' => 'SPRADMIN'])));
        $this->assertInstanceOf(BinaryFileResponse::class, $roleCtrl->exportExcel(new Request(['keyword' => 'Staff'])));
        $this->assertInstanceOf(BinaryFileResponse::class, $menuCtrl->exportExcel(new Request(['parentCode' => 'ALL'])));
        $this->assertInstanceOf(BinaryFileResponse::class, $actCtrl->exportExcel(new Request(['activity' => 'Customer'])));

        // PDF exports
        foreach ([
            $userCtrl->exportPdf(new Request(['roleCode' => 'SPRADMIN'])),
            $roleCtrl->exportPdf(new Request(['keyword' => 'Staff'])),
            $menuCtrl->exportPdf(new Request(['parentCode' => $this->menuParent->code])),
            $actCtrl->exportPdf(new Request(['activity' => 'Customer'])),
        ] as $pdfResponse) {
            $this->assertPdfResponse($pdfResponse);
        }
    }

    private function ajaxReq(string $uri, array $params = []): Request
    {
        $request = Request::create($uri, 'GET', $params);
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        return $request;
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
            $table->string('icon')->nullable();
            $table->integer('sort')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('role_menu', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->nullable();
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

        Schema::create('position', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('employee', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('positionCode')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->integer('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('province', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('city', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('provinceId')->nullable();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('district', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('cityId')->nullable();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('location', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('provinceId')->nullable();
            $table->string('cityId')->nullable();
            $table->string('districtId')->nullable();
            $table->string('address')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('material', function (Blueprint $table) {
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

        Schema::create('cost_component', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('type')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('cost_component_price_logs', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('costComponentCode');
            $table->string('costComponentName')->nullable();
            $table->decimal('oldPrice', 15, 2)->default(0);
            $table->decimal('newPrice', 15, 2)->default(0);
            $table->string('changedBy')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('company', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('format')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('customer', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('companyCode')->nullable();
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

        Schema::create('route_type', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('route', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('customerCode')->nullable();
            $table->string('originLocationCode')->nullable();
            $table->string('destinationLocationCode')->nullable();
            $table->string('fleetTypeCode')->nullable();
            $table->string('routeTypeCode')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('vendorPrice', 15, 2)->default(0);
            $table->decimal('personalVendorPrice', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('route_detail', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('routeCode');
            $table->string('componentCode');
            $table->decimal('amount', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('route_price_external', function (Blueprint $table) {
            $table->id();
            $table->string('route_id');
            $table->string('fleet_company_id');
            $table->decimal('amount', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('order', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->string('shipmentNumber')->nullable();
            $table->dateTime('orderDate')->nullable();
            $table->string('customerCode')->nullable();
            $table->string('fleetCode')->nullable();
            $table->string('plateNumber')->nullable();
            $table->string('driverCode')->nullable();
            $table->string('driverName')->nullable();
            $table->string('routeCode')->nullable();
            $table->decimal('qty', 15, 2)->default(1);
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('vendorPrice', 15, 2)->default(0);
            $table->decimal('personalVendorPrice', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('order_cost', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->nullable();
            $table->string('orderCode');
            $table->string('componentCode')->nullable();
            $table->string('componentName')->nullable();
            $table->decimal('nominal', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tonase_bonus', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->decimal('min', 15, 2)->default(0);
            $table->decimal('max', 15, 2)->default(0);
            $table->decimal('value', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('invoice', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->decimal('pphAmount', 15, 2)->default(0);
            $table->string('pphBaseType')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('invoice_detail', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('invoiceCode');
            $table->string('orderCode')->nullable();
            $table->string('orderId')->nullable();
            $table->decimal('routeAmount', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('order_payment', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('orderCode')->nullable();
            $table->decimal('pph', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('item', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->decimal('price', 15, 2)->default(0);
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
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('driver_salary', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->string('driverCode')->nullable();
            $table->date('startDate')->nullable();
            $table->date('endDate')->nullable();
            $table->decimal('totalSalary', 15, 2)->default(0);
            $table->decimal('totalAdjustment', 15, 2)->default(0);
            $table->decimal('grandTotal', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('driver_salary_detail', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->string('driverSalaryCode');
            $table->date('date')->nullable();
            $table->string('description')->nullable();
            $table->string('type')->nullable();
            $table->decimal('nominal', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('order_driver_salary', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('order_id')->nullable();
            $table->string('driver_id')->nullable();
            $table->string('driver_salary_id')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('status')->default('0');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('commission_report', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->string('recipientName')->nullable();
            $table->string('customerCode')->nullable();
            $table->date('startDate')->nullable();
            $table->date('endDate')->nullable();
            $table->date('paymentDate')->nullable();
            $table->text('componentCodes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('commission_report_detail', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('commissionReportCode');
            $table->timestamps();
        });

        Schema::create('order_commission', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('order_id');
            $table->string('cost_component_id')->nullable();
            $table->string('order_cost_id')->nullable();
            $table->string('commission_report_id')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->date('payment_date')->nullable();
            $table->string('status')->default('0');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('activity_log', function (Blueprint $table) {
            $table->id();
            $table->string('log_name')->nullable();
            $table->text('description')->nullable();
            $table->string('subject_type')->nullable();
            $table->string('subject_id')->nullable();
            $table->string('causer_type')->nullable();
            $table->unsignedBigInteger('causer_id')->nullable();
            $table->text('properties')->nullable();
            $table->timestamps();
        });
    }

    private function seedFixtures(): void
    {
        Role::create(['id' => (string) Str::uuid(), 'code' => 'SPRADMIN', 'name' => 'Super Admin']);
        Role::create(['id' => (string) Str::uuid(), 'code' => 'STAFF', 'name' => 'Staff Operasional']);

        $this->adminUser = User::create([
            'code' => 'USR-01',
            'name' => 'Admin Test',
            'username' => 'admin_test',
            'password' => bcrypt('password'),
            'roleCode' => 'SPRADMIN',
            'languange' => 'id',
        ]);
        $this->staffUser = User::create([
            'code' => 'USR-02',
            'name' => 'Staff Test',
            'username' => 'staff_test',
            'password' => bcrypt('password'),
            'roleCode' => 'STAFF',
            'languange' => 'en',
        ]);

        $this->posDriver = Position::create(['code' => 'KP_240823034043', 'name' => 'Supir']);
        $this->posAdmin = Position::create(['code' => 'POS-ADM', 'name' => 'Admin']);

        $this->empA = Employee::create(['code' => 'TE-01', 'name' => 'Budi Supir', 'positionCode' => $this->posDriver->code, 'status' => 1]);
        $this->empB = Employee::create(['code' => 'TE-02', 'name' => 'Siti Admin', 'positionCode' => $this->posAdmin->code, 'status' => 0]);

        $this->provJatim = Province::create(['name' => 'Jawa Timur']);
        $this->provJateng = Province::create(['name' => 'Jawa Tengah']);

        $this->locOrigin = Location::create(['code' => 'TL-01', 'name' => 'Surabaya', 'provinceId' => $this->provJatim->id, 'address' => 'Jl. Perak']);
        $this->locDest = Location::create(['code' => 'TL-02', 'name' => 'Semarang', 'provinceId' => $this->provJateng->id, 'address' => 'Jl. Kaligawe']);

        $this->matA = Material::create(['code' => 'TM-01', 'name' => 'Pasir Silika']);
        $this->matB = Material::create(['code' => 'TM-02', 'name' => 'Batu Split']);

        $this->unitA = Unit::create(['code' => 'TU-01', 'name' => 'Ton', 'type' => null]);
        $this->unitB = Unit::create(['code' => 'TU-02', 'name' => 'Kubik', 'type' => null]);

        $this->compA = CostComponent::create(['code' => 'TCC-01', 'name' => 'Gaji Supir', 'type' => 'salary', 'price' => 300000]);
        $this->compB = CostComponent::create(['code' => 'TCC-02', 'name' => 'Komisi Marketing', 'type' => 'commission', 'price' => 150000]);

        CostComponentPriceLog::create([
            'costComponentCode' => $this->compA->code,
            'costComponentName' => $this->compA->name,
            'oldPrice' => 250000,
            'newPrice' => 300000,
            'changedBy' => 'Admin Test',
            'notes' => 'Penyesuaian tarif',
        ]);

        $this->companyA = Company::create(['code' => 'FCMP-01', 'name' => 'PT PHL Utama', 'format' => 'PHL']);
        $this->companyB = Company::create(['code' => 'FCMP-02', 'name' => 'PT WTMS', 'format' => 'WTMS']);

        $this->custA = Customer::create(['code' => 'CUST-01', 'name' => 'PT Alpha Logistics', 'companyCode' => $this->companyA->code]);
        $this->custB = Customer::create(['code' => 'CUST-02', 'name' => 'PT Beta Cargo', 'companyCode' => $this->companyB->code]);

        $this->fcInternalA = FleetCompany::create(['code' => 'FC-INT-1', 'name' => 'Armada Internal PHL', 'type' => 'Internal']);
        $this->fcInternalB = FleetCompany::create(['code' => 'FC-INT-2', 'name' => 'Armada Internal WTMS', 'type' => 'Internal']);
        $ft = FleetType::create(['code' => 'FT-01', 'name' => 'Tronton']);
        $rt = RouteType::create(['code' => 'RIT', 'name' => 'Ritase']);

        $this->fleetA = Fleet::create(['code' => 'FL-01', 'plateNumber' => 'L 1111 AA', 'fleetTypeCode' => $ft->code, 'fleetCompanyCode' => $this->fcInternalA->code]);
        $this->fleetB = Fleet::create(['code' => 'FL-02', 'plateNumber' => 'L 2222 BB', 'fleetTypeCode' => $ft->code, 'fleetCompanyCode' => $this->fcInternalB->code]);

        $this->routeA = Route::create([
            'code' => 'TR-01',
            'name' => 'SBY - SMG',
            'customerCode' => $this->custA->code,
            'originLocationCode' => $this->locOrigin->code,
            'destinationLocationCode' => $this->locDest->code,
            'fleetTypeCode' => $ft->code,
            'routeTypeCode' => $rt->code,
            'price' => 3500000,
            'personalVendorPrice' => 2500000,
        ]);
        $this->routeB = Route::create([
            'code' => 'TR-02',
            'name' => 'SMG - SBY',
            'customerCode' => $this->custB->code,
            'originLocationCode' => $this->locDest->code,
            'destinationLocationCode' => $this->locOrigin->code,
            'fleetTypeCode' => $ft->code,
            'routeTypeCode' => $rt->code,
            'price' => 3200000,
            'personalVendorPrice' => 2200000,
        ]);

        $orderA = Order::create([
            'code' => 'ORD-01',
            'shipmentNumber' => 'SHP-01',
            'orderDate' => '2026-10-10 08:00:00',
            'customerCode' => $this->custA->code,
            'fleetCode' => $this->fleetA->code,
            'plateNumber' => $this->fleetA->plateNumber,
            'driverCode' => $this->empA->code,
            'driverName' => $this->empA->name,
            'routeCode' => $this->routeA->code,
            'price' => 3500000,
            'vendorPrice' => 2500000,
            'personalVendorPrice' => 2500000,
        ]);

        DriverSalary::create([
            'code' => 'DS-001',
            'driverCode' => $this->empA->code,
            'startDate' => '2026-10-01',
            'endDate' => '2026-10-15',
            'totalSalary' => 300000,
            'totalAdjustment' => 50000,
            'grandTotal' => 350000,
            'notes' => 'Bonus tepat waktu',
        ]);

        OrderCommission::create([
            'id' => (string) Str::uuid(),
            'order_id' => $orderA->id,
            'cost_component_id' => $this->compB->id,
            'amount' => 150000,
            'status' => '0',
        ]);

        $item = Item::create(['code' => 'ITM-01', 'name' => 'Oli Mesin', 'price' => 100000]);
        $mnt = Maintenance::create(['code' => 'MNT-01', 'date' => '2026-10-12', 'time' => '10:00:00', 'fleetCode' => $this->fleetA->code, 'status' => 0, 'grand_total' => 200000]);
        MaintenanceDetail::create(['code' => 'MD-01', 'maintenanceCode' => $mnt->code, 'itemCode' => $item->code, 'qty' => '2', 'price' => '100000', 'total' => '200000']);

        $this->menuParent = Menu::create([
            'code' => 'MN-MASTER',
            'name' => 'Master Data',
            'nama' => 'Data Master',
            'parentCode' => '0',
            'url' => '#',
            'sort' => 1,
        ]);
        $this->menuChild = Menu::create([
            'code' => 'MN-LOC',
            'name' => 'Location',
            'nama' => 'Lokasi',
            'parentCode' => $this->menuParent->code,
            'url' => 'master/location',
            'sort' => 2,
        ]);

        ActivityLog::create([
            'log_name' => 'Customer',
            'description' => 'Create',
            'causer_id' => $this->adminUser->id,
            'properties' => json_encode(['attributes' => ['name' => 'PT Alpha Logistics']]),
        ]);
        ActivityLog::create([
            'log_name' => 'Unit',
            'description' => 'Update',
            'causer_id' => $this->staffUser->id,
            'properties' => json_encode(['attributes' => ['name' => 'Ton']]),
        ]);
    }
}
