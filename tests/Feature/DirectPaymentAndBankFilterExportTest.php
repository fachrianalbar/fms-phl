<?php

namespace Tests\Feature;

use App\Http\Controllers\Bank\BankAccountController;
use App\Http\Controllers\Bank\BankBookController;
use App\Http\Controllers\Bank\UserBankController;
use App\Http\Controllers\Finance\DirectPaymentController;
use App\Models\Bank\BankAccount;
use App\Models\Bank\UserBank;
use App\Models\CompanySetting;
use App\Models\Data\Route;
use App\Models\Finance\OrderPayment;
use App\Models\Finance\OrderPaymentHistory;
use App\Models\LiveMutation;
use App\Models\Master\Company;
use App\Models\Master\Customer;
use App\Models\Master\Employee;
use App\Models\Master\Fleet;
use App\Models\Master\FleetCompany;
use App\Models\Master\FleetType;
use App\Models\Master\Location;
use App\Models\Mutation;
use App\Models\Operational\Order;
use App\Models\Role;
use App\Models\User;
use App\Services\Bank\BankAccountService;
use App\Services\Bank\BankBookService;
use App\Services\Bank\UserBankService;
use App\Services\Finance\DirectPaymentService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\TestCase;

class DirectPaymentAndBankFilterExportTest extends TestCase
{
    protected User $user;

    protected Company $companyPhl;

    protected Company $companyWtms;

    protected Customer $customerA;

    protected Customer $customerB;

    protected Fleet $fleetAlpha;

    protected Fleet $fleetBeta;

    protected Employee $driverBudi;

    protected Route $routeA;

    protected Route $routeB;

    protected BankAccount $bankBca;

    protected BankAccount $bankMandiri;

    protected UserBank $userBankBca;

    protected UserBank $userBankMandiri;

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
            'order_payment_history',
            'order_payment_batch',
            'order_payment',
            'order_cost',
            'order',
            'route',
            'location',
            'customer',
            'employee',
            'fleet',
            'fleet_type',
            'fleet_company',
            'company',
            'mutation',
            'live_mutation',
            'user_bank',
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

    public function test_direct_payment_all_four_menus_filter_export_and_views(): void
    {
        $this->actingAs($this->user);
        View::share('errors', new ViewErrorBag());

        // 1. Waiting orders
        Order::create([
            'code' => 'ORD-DP-W1',
            'shipmentNumber' => 'SHP-W1',
            'orderDate' => '2026-10-05',
            'customerCode' => $this->customerA->code,
            'routeCode' => $this->routeA->code,
            'qty' => 1,
            'driverCode' => $this->driverBudi->code,
            'driverName' => $this->driverBudi->name,
            'fleetCode' => $this->fleetAlpha->code,
            'plateNumber' => 'L 1001 AA',
            'routeAmount' => 1000000,
        ]);

        Order::create([
            'code' => 'ORD-DP-W2',
            'shipmentNumber' => 'SHP-W2',
            'orderDate' => '2026-10-20',
            'customerCode' => $this->customerB->code,
            'routeCode' => $this->routeB->code,
            'qty' => 1,
            'driverCode' => $this->driverBudi->code,
            'driverName' => $this->driverBudi->name,
            'fleetCode' => $this->fleetBeta->code,
            'plateNumber' => 'B 2002 BB',
            'routeAmount' => 2000000,
        ]);

        // 2. Unpaid nota
        $ordUnpaid = Order::create([
            'code' => 'ORD-DP-U1',
            'shipmentNumber' => 'SHP-U1',
            'orderDate' => '2026-10-08',
            'customerCode' => $this->customerA->code,
            'routeCode' => $this->routeA->code,
            'qty' => 1,
            'driverCode' => $this->driverBudi->code,
            'driverName' => $this->driverBudi->name,
            'fleetCode' => $this->fleetAlpha->code,
            'plateNumber' => 'L 1001 AA',
            'routeAmount' => 1500000,
        ]);

        $opUnpaid = OrderPayment::create([
            'code' => 'FOP-U1',
            'orderCode' => $ordUnpaid->code,
            'cost' => 1500000,
            'additional_cost' => 0,
            'ppn' => 0,
            'pph' => 0,
            'claim' => 0,
            'total' => 500000,
            'status' => 0,
            'nota_number' => 'DP/00001/2026',
            'user_bank_code' => $this->userBankBca->code,
        ]);
        $opUnpaid->created_at = '2026-10-08 10:00:00';
        $opUnpaid->save();

        // 3. Paid nota + payment history
        $ordPaid = Order::create([
            'code' => 'ORD-DP-P1',
            'shipmentNumber' => 'SHP-P1',
            'orderDate' => '2026-10-12',
            'customerCode' => $this->customerB->code,
            'routeCode' => $this->routeB->code,
            'qty' => 1,
            'driverCode' => $this->driverBudi->code,
            'driverName' => $this->driverBudi->name,
            'fleetCode' => $this->fleetBeta->code,
            'plateNumber' => 'B 2002 BB',
            'routeAmount' => 2500000,
        ]);

        $opPaid = OrderPayment::create([
            'code' => 'FOP-P1',
            'orderCode' => $ordPaid->code,
            'cost' => 2500000,
            'additional_cost' => 0,
            'ppn' => 0,
            'pph' => 0,
            'claim' => 0,
            'total' => 2500000,
            'status' => 1,
            'nota_number' => 'DP/00002/2026',
            'user_bank_code' => $this->userBankMandiri->code,
        ]);
        $opPaid->created_at = '2026-10-12 10:00:00';
        $opPaid->save();

        OrderPaymentHistory::create([
            'code' => 'FOPH-1',
            'orderCode' => $ordUnpaid->code,
            'paymentType' => 'Dp',
            'total' => 500000,
            'date' => '2026-10-09',
            'userBankCode' => $this->userBankBca->code,
            'batch_code' => 'DPB-0001',
            'description' => 'Cicilan DP',
        ]);

        OrderPaymentHistory::create([
            'code' => 'FOPH-2',
            'orderCode' => $ordPaid->code,
            'paymentType' => 'Full',
            'total' => 2500000,
            'date' => '2026-10-15',
            'userBankCode' => $this->userBankMandiri->code,
            'batch_code' => 'DPB-0002',
            'description' => 'Pelunasan',
        ]);

        /** @var DirectPaymentService $service */
        $service = app(DirectPaymentService::class);

        // Service filters
        $this->assertSame(['ORD-DP-W1'], $service->findWaitingOrders([
            'startDate' => '2026-10-01',
            'endDate' => '2026-10-10',
            'customerCode' => $this->customerA->code,
            'companyCode' => $this->companyPhl->code,
            'plateNumber' => '1001',
        ])->pluck('code')->all());

        $this->assertSame(['DP/00001/2026'], $service->findUnpaidNotas([
            'startDate' => '2026-10-01',
            'endDate' => '2026-10-10',
            'customerCode' => $this->customerA->code,
            'companyCode' => $this->companyPhl->code,
            'notaNumber' => '00001',
            'status' => 'partial',
        ])->pluck('nota_number')->all());

        $this->assertSame(['DP/00002/2026'], $service->findPaidUnits([
            'startDate' => '2026-10-14',
            'endDate' => '2026-10-16',
            'customerCode' => $this->customerB->code,
            'companyCode' => $this->companyWtms->code,
            'notaNumber' => '00002',
            'plateNumber' => '2002',
        ])->pluck('code')->all());

        $this->assertSame(['DPB-0002'], $service->findPayments([
            'startDate' => '2026-10-14',
            'endDate' => '2026-10-16',
            'customerCode' => $this->customerB->code,
            'companyCode' => $this->companyWtms->code,
            'userBankCode' => $this->userBankMandiri->code,
            'notaNumber' => '00002',
        ])->pluck('batch_code')->all());

        /** @var DirectPaymentController $controller */
        $controller = app(DirectPaymentController::class);

        // Views check
        foreach ([
            $controller->indexWaiting()->render(),
            $controller->indexUnpaid()->render(),
            $controller->indexPaid()->render(),
            $controller->paymentIndex()->render(),
        ] as $html) {
            $this->assertStringContainsString('class="filter-card"', $html);
            $this->assertStringContainsString('id="filterForm"', $html);
            $this->assertStringContainsString('id="export-excel"', $html);
            $this->assertStringContainsString('id="export-pdf"', $html);
            $this->assertStringContainsString('html[data-bs-theme="dark"] .filter-card', $html);
            $this->assertStringContainsString('syncExportUrls()', $html);
        }

        // Export Excel + PDF check
        $this->assertInstanceOf(BinaryFileResponse::class, $controller->exportWaitingExcel(new Request(['customerCode' => $this->customerA->code])));
        $this->assertInstanceOf(BinaryFileResponse::class, $controller->exportUnpaidExcel(new Request(['notaNumber' => '00001'])));
        $this->assertInstanceOf(BinaryFileResponse::class, $controller->exportPaidExcel(new Request(['notaNumber' => '00002'])));
        $this->assertInstanceOf(BinaryFileResponse::class, $controller->exportPaymentExcel(new Request(['userBankCode' => $this->userBankMandiri->code])));

        foreach ([
            $controller->exportWaitingPdf(new Request()),
            $controller->exportUnpaidPdf(new Request()),
            $controller->exportPaidPdf(new Request()),
            $controller->exportPaymentPdf(new Request()),
        ] as $pdfResponse) {
            $this->assertSame(200, $pdfResponse->getStatusCode());
            $this->assertSame('application/pdf', $pdfResponse->headers->get('Content-Type'));
            $this->assertStringStartsWith('%PDF', $pdfResponse->getContent());
        }
    }

    public function test_bank_all_three_menus_filter_export_and_views(): void
    {
        $this->actingAs($this->user);
        View::share('errors', new ViewErrorBag());

        // Create mutations for bank-book filtering
        Mutation::create([
            'code' => 'FMT-1',
            'userBankCode' => $this->userBankBca->code,
            'date' => '2026-10-05',
            'nominal' => 1000000,
            'type' => 'In',
            'description' => 'Setoran',
        ]);
        Mutation::create([
            'code' => 'FMT-2',
            'userBankCode' => $this->userBankBca->code,
            'date' => '2026-10-06',
            'nominal' => 250000,
            'type' => 'Out',
            'description' => 'Biaya',
        ]);
        Mutation::create([
            'code' => 'FMT-3',
            'userBankCode' => $this->userBankMandiri->code,
            'date' => '2026-10-20',
            'nominal' => 500000,
            'type' => 'In',
            'description' => 'Transfer Masuk',
        ]);

        /** @var BankAccountService $bankAccountSvc */
        $bankAccountSvc = app(BankAccountService::class);
        $this->assertSame(['BCA'], $bankAccountSvc->findAll(['name' => 'BCA'])->pluck('name')->all());
        $this->assertSame(['Mandiri'], $bankAccountSvc->findAll(['bankCode' => '008'])->pluck('name')->all());

        /** @var UserBankService $userBankSvc */
        $userBankSvc = app(UserBankService::class);
        $this->assertSame(['UB-BCA'], $userBankSvc->findAllFiltered('all', [
            'bankCode' => $this->bankBca->code,
            'type' => '2',
            'rekeningType' => 'internal',
            'keyword' => '111222',
        ])->pluck('code')->all());

        /** @var BankBookService $bankBookSvc */
        $bankBookSvc = app(BankBookService::class);
        $filteredBooks = $bankBookSvc->findAll([
            'startDate' => '2026-10-01',
            'endDate' => '2026-10-10',
            'userBankCode' => $this->userBankBca->code,
            'mutationType' => 'In',
        ]);
        $this->assertCount(1, $filteredBooks);
        $this->assertEquals(1000000.0, (float) $filteredBooks->first()->debit);
        $this->assertEquals(0.0, (float) $filteredBooks->first()->credit);

        /** @var BankAccountController $bankAccountCtrl */
        $bankAccountCtrl = app(BankAccountController::class);
        /** @var UserBankController $userBankCtrl */
        $userBankCtrl = app(UserBankController::class);
        /** @var BankBookController $bankBookCtrl */
        $bankBookCtrl = app(BankBookController::class);

        foreach ([
            $bankAccountCtrl->index()->render(),
            $userBankCtrl->index()->render(),
            $bankBookCtrl->index()->render(),
        ] as $html) {
            $this->assertStringContainsString('class="filter-card"', $html);
            $this->assertStringContainsString('id="filterForm"', $html);
            $this->assertStringContainsString('id="export-excel"', $html);
            $this->assertStringContainsString('id="export-pdf"', $html);
            $this->assertStringContainsString('html[data-bs-theme="dark"] .filter-card', $html);
            $this->assertStringContainsString('syncExportUrls()', $html);
        }

        $this->assertInstanceOf(BinaryFileResponse::class, $bankAccountCtrl->exportExcel(new Request(['name' => 'BCA'])));
        $this->assertInstanceOf(BinaryFileResponse::class, $userBankCtrl->exportExcel(new Request(['bankCode' => $this->bankBca->code])));
        $this->assertInstanceOf(BinaryFileResponse::class, $bankBookCtrl->exportExcel(new Request(['mutationType' => 'In'])));

        foreach ([
            $bankAccountCtrl->exportPdf(new Request()),
            $userBankCtrl->exportPdf(new Request()),
            $bankBookCtrl->exportPdf(new Request(['startDate' => '2026-10-01', 'endDate' => '2026-10-10'])),
        ] as $pdfResponse) {
            $this->assertSame(200, $pdfResponse->getStatusCode());
            $this->assertSame('application/pdf', $pdfResponse->headers->get('Content-Type'));
            $this->assertStringStartsWith('%PDF', $pdfResponse->getContent());
        }
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
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('menu', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('nama')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('role_menu', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('roleCode');
            $table->string('menuCode');
            $table->timestamps();
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
            $table->string('bankCode')->nullable();
            $table->string('name');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('user_bank', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->string('accountNumber')->nullable();
            $table->string('accountName')->nullable();
            $table->integer('type')->default(2);
            $table->string('rekening_type')->default('external');
            $table->string('bankCode')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('live_mutation', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('userBankCode');
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->decimal('balance', 15, 2)->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('mutation', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('userBankCode');
            $table->date('date')->nullable();
            $table->text('description')->nullable();
            $table->decimal('nominal', 15, 2)->default(0);
            $table->string('type')->nullable();
            $table->string('transactionTypeCode')->nullable();
            $table->string('transactionCode')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('company', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('format')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('fleet_company', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('type')->default('Internal');
            $table->string('companyCode')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('fleet_type', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('fleet', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->string('plateNumber');
            $table->string('fleetTypeCode')->nullable();
            $table->string('fleetCompanyCode')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('employee', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('customer', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('companyCode')->nullable();
            $table->integer('isDo')->default(0);
            $table->decimal('ppn', 8, 2)->default(0);
            $table->decimal('pph', 8, 2)->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('location', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('route', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('customerCode')->nullable();
            $table->string('originLocationCode')->nullable();
            $table->string('destinationLocationCode')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('order', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->string('shipmentNumber')->nullable();
            $table->date('orderDate')->nullable();
            $table->string('customerCode')->nullable();
            $table->string('routeCode')->nullable();
            $table->float('qty')->default(1);
            $table->string('driverCode')->nullable();
            $table->string('driverName')->nullable();
            $table->string('fleetCode')->nullable();
            $table->string('plateNumber')->nullable();
            $table->integer('status')->default(0);
            $table->decimal('routeAmount', 15, 2)->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('order_cost', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('orderCode');
            $table->decimal('nominal', 15, 2)->default(0);
            $table->string('type')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('order_payment', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('orderCode');
            $table->decimal('cost', 15, 2)->default(0);
            $table->decimal('additional_cost', 15, 2)->default(0);
            $table->decimal('claim', 15, 2)->default(0);
            $table->string('claim_description')->nullable();
            $table->decimal('ppn', 15, 2)->default(0);
            $table->decimal('ppn_percent', 8, 2)->nullable();
            $table->decimal('pph', 15, 2)->default(0);
            $table->decimal('pph_percent', 8, 2)->nullable();
            $table->decimal('total', 15, 2)->default(0);
            $table->integer('status')->default(0);
            $table->string('nota_number')->nullable();
            $table->string('user_bank_code')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('order_payment_batch', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('request_key')->nullable();
            $table->string('payload_hash')->nullable();
            $table->string('status')->default('active');
            $table->date('payment_date')->nullable();
            $table->string('user_bank_code')->nullable();
            $table->integer('amount')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('order_payment_history', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('orderCode');
            $table->string('paymentType')->nullable();
            $table->decimal('claim', 15, 2)->default(0);
            $table->string('claim_description')->nullable();
            $table->decimal('ppn', 15, 2)->default(0);
            $table->decimal('ppn_percent', 8, 2)->nullable();
            $table->decimal('pph', 15, 2)->default(0);
            $table->decimal('pph_percent', 8, 2)->nullable();
            $table->decimal('total', 15, 2)->default(0);
            $table->date('date')->nullable();
            $table->text('description')->nullable();
            $table->string('userBankCode')->nullable();
            $table->string('batch_code')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    private function seedFixtures(): void
    {
        CompanySetting::create([
            'id' => (string) Str::uuid(),
            'code' => 'CS-01',
            'name' => 'PT PUTRI HOKI LOGISTIK',
        ]);

        Role::create([
            'id' => (string) Str::uuid(),
            'code' => 'SPRADMIN',
            'name' => 'System Administrator',
        ]);

        $this->user = User::create([
            'code' => 'USR-01',
            'name' => 'Admin Tester',
            'username' => 'admin_tester',
            'password' => bcrypt('secret'),
            'roleCode' => 'SPRADMIN',
            'languange' => 'id',
        ]);

        $this->companyPhl = Company::create([
            'code' => 'COMP-PHL',
            'name' => 'PT PUTRI HOKI LOGISTIK',
            'format' => 'PHL',
        ]);

        $this->companyWtms = Company::create([
            'code' => 'COMP-WTMS',
            'name' => 'PT WIJAYA TRANS MAKMUR SEJAHTERA',
            'format' => 'WTMS',
        ]);

        $fcInt = FleetCompany::create([
            'code' => 'FC-INT',
            'name' => 'PT PHL INTERNAL',
            'type' => 'Internal',
            'companyCode' => $this->companyPhl->code,
        ]);

        $ft = FleetType::create(['code' => 'FT-1', 'name' => 'Tronton']);

        $this->fleetAlpha = Fleet::create([
            'code' => 'FLT-ALPHA',
            'plateNumber' => 'L 1001 AA',
            'fleetTypeCode' => $ft->code,
            'fleetCompanyCode' => $fcInt->code,
        ]);

        $this->fleetBeta = Fleet::create([
            'code' => 'FLT-BETA',
            'plateNumber' => 'B 2002 BB',
            'fleetTypeCode' => $ft->code,
            'fleetCompanyCode' => $fcInt->code,
        ]);

        $this->driverBudi = Employee::create(['code' => 'DRV-1', 'name' => 'Budi Santoso']);

        $this->customerA = Customer::create([
            'code' => 'CUST-A',
            'name' => 'Customer Non DO Alpha',
            'companyCode' => $this->companyPhl->code,
            'isDo' => 0,
        ]);

        $this->customerB = Customer::create([
            'code' => 'CUST-B',
            'name' => 'Customer Non DO Beta',
            'companyCode' => $this->companyWtms->code,
            'isDo' => 0,
        ]);

        $origin = Location::create(['code' => 'LOC-1', 'name' => 'Surabaya']);
        $dest = Location::create(['code' => 'LOC-2', 'name' => 'Jakarta']);

        $this->routeA = Route::create([
            'code' => 'RTE-A',
            'name' => 'Surabaya - Jakarta',
            'customerCode' => $this->customerA->code,
            'originLocationCode' => $origin->code,
            'destinationLocationCode' => $dest->code,
            'price' => 1000000,
        ]);

        $this->routeB = Route::create([
            'code' => 'RTE-B',
            'name' => 'Surabaya - Semarang',
            'customerCode' => $this->customerB->code,
            'originLocationCode' => $origin->code,
            'destinationLocationCode' => $dest->code,
            'price' => 2000000,
        ]);

        $this->bankBca = BankAccount::create(['code' => 'BNK-BCA', 'bankCode' => '014', 'name' => 'BCA']);
        $this->bankMandiri = BankAccount::create(['code' => 'BNK-MDR', 'bankCode' => '008', 'name' => 'Mandiri']);

        $this->userBankBca = UserBank::create([
            'code' => 'UB-BCA',
            'accountNumber' => '111222333',
            'accountName' => 'PT PHL',
            'type' => 2,
            'rekening_type' => 'internal',
            'bankCode' => $this->bankBca->code,
        ]);

        $this->userBankMandiri = UserBank::create([
            'code' => 'UB-MDR',
            'accountNumber' => '444555666',
            'accountName' => 'Budi Pribadi',
            'type' => 1,
            'rekening_type' => 'external',
            'bankCode' => $this->bankMandiri->code,
        ]);

        LiveMutation::create([
            'code' => 'FLM-1',
            'userBankCode' => $this->userBankBca->code,
            'debit' => 1000000,
            'credit' => 250000,
            'balance' => 750000,
        ]);

        LiveMutation::create([
            'code' => 'FLM-2',
            'userBankCode' => $this->userBankMandiri->code,
            'debit' => 500000,
            'credit' => 0,
            'balance' => 500000,
        ]);
    }
}
