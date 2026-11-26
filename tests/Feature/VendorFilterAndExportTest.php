<?php

namespace Tests\Feature;

use App\Http\Controllers\Finance\VendorInvoiceController;
use App\Http\Controllers\Finance\VendorPaymentListController;
use App\Models\Bank\BankAccount;
use App\Models\Bank\UserBank;
use App\Models\CompanySetting;
use App\Models\Data\Route;
use App\Models\Finance\VendorPayment;
use App\Models\Finance\VendorPaymentHistory;
use App\Models\LiveMutation;
use App\Models\Master\Company;
use App\Models\Master\Customer;
use App\Models\Master\Fleet;
use App\Models\Master\FleetCompany;
use App\Models\Master\Location;
use App\Models\Operational\Order;
use App\Models\Role;
use App\Models\User;
use App\Services\Finance\VendorPaymentService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\TestCase;

class VendorFilterAndExportTest extends TestCase
{
    protected User $user;

    protected Company $companyPhl;

    protected Company $companyWtms;

    protected FleetCompany $vendorAlpha;

    protected FleetCompany $vendorBeta;

    protected Fleet $fleetAlpha;

    protected Fleet $fleetBeta;

    protected Customer $customerPhl;

    protected Customer $customerWtms;

    protected Route $routePhl;

    protected Route $routeWtms;

    protected UserBank $userBankPrimary;

    protected UserBank $userBankSecondary;

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
            'vendor_payment_history',
            'vendor_payment_batch',
            'vendor_payment',
            'vendor_nota_sequence',
            'mutation',
            'live_mutation',
            'user_bank',
            'bank_account',
            'order_cost',
            'order',
            'order_status',
            'route',
            'location',
            'customer',
            'employee',
            'fleet',
            'fleet_company',
            'company',
            'activity_log',
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

    public function test_waiting_orders_filter_and_exports_work(): void
    {
        $this->actingAs($this->user);

        Order::create([
            'code' => 'ORD-WAIT-001',
            'shipmentNumber' => 'SHP-WAIT-001',
            'orderDate' => '2026-10-05',
            'customerCode' => $this->customerPhl->code,
            'routeCode' => $this->routePhl->code,
            'qty' => 5,
            'fleetCode' => $this->fleetAlpha->code,
            'plateNumber' => 'L 1001 AA',
            'status' => 4,
            'companyCode' => $this->companyPhl->code,
            'vendorPrice' => 500000,
        ]);

        Order::create([
            'code' => 'ORD-WAIT-002',
            'shipmentNumber' => 'SHP-WAIT-002',
            'orderDate' => '2026-10-20',
            'customerCode' => $this->customerWtms->code,
            'routeCode' => $this->routeWtms->code,
            'qty' => 5,
            'fleetCode' => $this->fleetBeta->code,
            'plateNumber' => 'B 2002 BB',
            'status' => 4,
            'companyCode' => $this->companyWtms->code,
            'vendorPrice' => 750000,
        ]);

        /** @var VendorPaymentService $service */
        $service = app(VendorPaymentService::class);

        $this->assertCount(2, $service->findWaitingOrders());
        $this->assertSame(['ORD-WAIT-001'], $service->findWaitingOrders([
            'startDate' => '2026-10-01',
            'endDate' => '2026-10-10',
            'fleetCompanyCode' => $this->vendorAlpha->code,
            'companyCode' => $this->companyPhl->code,
            'customerCode' => $this->customerPhl->code,
            'plateNumber' => '1001',
        ])->pluck('code')->all());

        /** @var VendorInvoiceController $controller */
        $controller = app(VendorInvoiceController::class);

        $excelResponse = $controller->exportWaitingExcel(new Request([
            'fleetCompanyCode' => $this->vendorBeta->code,
        ]));
        $this->assertInstanceOf(BinaryFileResponse::class, $excelResponse);

        $pdfResponse = $controller->exportWaitingPdf(new Request([
            'fleetCompanyCode' => $this->vendorBeta->code,
        ]));
        $this->assertSame(200, $pdfResponse->getStatusCode());
        $this->assertSame('application/pdf', $pdfResponse->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $pdfResponse->getContent());
    }

    public function test_nota_filters_and_exports_for_unpaid_partial_and_paid(): void
    {
        $this->actingAs($this->user);

        $orderPending = Order::create([
            'code' => 'ORD-N-1',
            'shipmentNumber' => 'SHP-N-1',
            'orderDate' => '2026-10-01',
            'customerCode' => $this->customerPhl->code,
            'routeCode' => $this->routePhl->code,
            'qty' => 2,
            'fleetCode' => $this->fleetAlpha->code,
            'plateNumber' => 'L 1001 AA',
            'status' => 4,
            'vendorPrice' => 400000,
        ]);

        $orderPartial = Order::create([
            'code' => 'ORD-N-2',
            'shipmentNumber' => 'SHP-N-2',
            'orderDate' => '2026-10-02',
            'customerCode' => $this->customerWtms->code,
            'routeCode' => $this->routeWtms->code,
            'qty' => 2,
            'fleetCode' => $this->fleetBeta->code,
            'plateNumber' => 'B 2002 BB',
            'status' => 4,
            'vendorPrice' => 600000,
        ]);

        $orderPaid = Order::create([
            'code' => 'ORD-N-3',
            'shipmentNumber' => 'SHP-N-3',
            'orderDate' => '2026-10-03',
            'customerCode' => $this->customerPhl->code,
            'routeCode' => $this->routePhl->code,
            'qty' => 2,
            'fleetCode' => $this->fleetAlpha->code,
            'plateNumber' => 'L 1001 AA',
            'status' => 6,
            'vendorPrice' => 900000,
        ]);

        $vpPending = VendorPayment::create([
            'orderCode' => $orderPending->code,
            'nota_number' => 'PHL/00001/2026',
            'user_bank_code' => $this->userBankPrimary->code,
            'amount' => 400000,
            'paid_amount' => 0,
            'remaining_amount' => 400000,
            'payment_status' => 'pending',
            'date' => '2026-10-01',
        ]);
        $vpPending->forceFill(['created_at' => '2026-10-05 10:00:00'])->saveQuietly();

        $vpPartial = VendorPayment::create([
            'orderCode' => $orderPartial->code,
            'nota_number' => 'WTMS/00002/2026',
            'user_bank_code' => $this->userBankSecondary->code,
            'amount' => 600000,
            'paid_amount' => 200000,
            'remaining_amount' => 400000,
            'payment_status' => 'partial',
            'date' => '2026-10-12',
        ]);
        $vpPartial->forceFill(['created_at' => '2026-10-12 10:00:00'])->saveQuietly();

        $vpPaid = VendorPayment::create([
            'orderCode' => $orderPaid->code,
            'nota_number' => 'PHL/00003/2026',
            'user_bank_code' => $this->userBankPrimary->code,
            'amount' => 900000,
            'paid_amount' => 900000,
            'remaining_amount' => 0,
            'payment_status' => 'paid',
            'date' => '2026-10-18',
        ]);
        $vpPaid->forceFill(['created_at' => '2026-10-18 10:00:00'])->saveQuietly();

        /** @var VendorPaymentService $service */
        $service = app(VendorPaymentService::class);

        $this->assertSame(['PHL/00001/2026'], $service->findPendingNotas([
            'startDate' => '2026-10-01',
            'endDate' => '2026-10-10',
            'fleetCompanyCode' => $this->vendorAlpha->code,
            'companyCode' => $this->companyPhl->code,
            'notaNumber' => '00001',
        ])->pluck('nota_number')->all());

        $this->assertSame([], $service->findPendingNotas(['companyCode' => $this->companyWtms->code])->all());

        $this->assertSame(['WTMS/00002/2026'], $service->findPartialNotas([
            'fleetCompanyCode' => $this->vendorBeta->code,
            'companyCode' => $this->companyWtms->code,
            'notaNumber' => 'WTMS',
        ])->pluck('nota_number')->all());

        $this->assertSame(['PHL/00003/2026'], $service->findPaidNotas([
            'startDate' => '2026-10-15',
            'endDate' => '2026-10-20',
        ])->pluck('nota_number')->all());

        /** @var VendorInvoiceController $controller */
        $controller = app(VendorInvoiceController::class);

        foreach ([
            [$controller->exportUnpaidExcel(new Request()), $controller->exportUnpaidPdf(new Request())],
            [$controller->exportPartialExcel(new Request()), $controller->exportPartialPdf(new Request())],
            [$controller->exportPaidExcel(new Request()), $controller->exportPaidPdf(new Request())],
        ] as [$excelResponse, $pdfResponse]) {
            $this->assertInstanceOf(BinaryFileResponse::class, $excelResponse);
            $this->assertSame(200, $pdfResponse->getStatusCode());
            $this->assertSame('application/pdf', $pdfResponse->headers->get('Content-Type'));
            $this->assertStringStartsWith('%PDF', $pdfResponse->getContent());
        }
    }

    public function test_payment_list_filter_and_exports_work(): void
    {
        $this->actingAs($this->user);

        $order1 = Order::create([
            'code' => 'ORD-PAY-1',
            'shipmentNumber' => 'SHP-PAY-1',
            'orderDate' => '2026-10-01',
            'customerCode' => $this->customerPhl->code,
            'routeCode' => $this->routePhl->code,
            'qty' => 1,
            'fleetCode' => $this->fleetAlpha->code,
            'plateNumber' => 'L 1001 AA',
            'status' => 6,
            'vendorPrice' => 500000,
        ]);

        $order2 = Order::create([
            'code' => 'ORD-PAY-2',
            'shipmentNumber' => 'SHP-PAY-2',
            'orderDate' => '2026-10-02',
            'customerCode' => $this->customerWtms->code,
            'routeCode' => $this->routeWtms->code,
            'qty' => 1,
            'fleetCode' => $this->fleetBeta->code,
            'plateNumber' => 'B 2002 BB',
            'status' => 6,
            'vendorPrice' => 700000,
        ]);

        $vp1 = VendorPayment::create([
            'code' => 'FVB-001',
            'orderCode' => $order1->code,
            'nota_number' => 'PHL/00010/2026',
            'user_bank_code' => $this->userBankPrimary->code,
            'amount' => 500000,
            'paid_amount' => 500000,
            'remaining_amount' => 0,
            'payment_status' => 'paid',
            'date' => '2026-10-06',
        ]);

        $vp2 = VendorPayment::create([
            'code' => 'FVB-002',
            'orderCode' => $order2->code,
            'nota_number' => 'WTMS/00020/2026',
            'user_bank_code' => $this->userBankSecondary->code,
            'amount' => 700000,
            'paid_amount' => 700000,
            'remaining_amount' => 0,
            'payment_status' => 'paid',
            'date' => '2026-10-21',
        ]);

        VendorPaymentHistory::create([
            'vendor_payment_id' => $vp1->id,
            'batch_code' => 'FVB-001',
            'amount' => 500000,
            'payment_date' => '2026-10-06',
            'user_bank_code' => $this->userBankPrimary->code,
            'description' => 'Bayar Vendor Alpha',
        ]);

        VendorPaymentHistory::create([
            'vendor_payment_id' => $vp2->id,
            'batch_code' => 'FVB-002',
            'amount' => 700000,
            'payment_date' => '2026-10-21',
            'user_bank_code' => $this->userBankSecondary->code,
            'description' => 'Bayar Vendor Beta',
        ]);

        /** @var VendorPaymentService $service */
        $service = app(VendorPaymentService::class);

        $filtered = $service->findPayments([
            'startDate' => '2026-10-01',
            'endDate' => '2026-10-10',
            'fleetCompanyCode' => $this->vendorAlpha->code,
            'userBankCode' => $this->userBankPrimary->code,
        ]);

        $this->assertSame(['FVB-001'], $filtered->pluck('batch_code')->all());

        /** @var VendorPaymentListController $controller */
        $controller = app(VendorPaymentListController::class);

        $excelResponse = $controller->exportExcel(new Request([
            'userBankCode' => $this->userBankSecondary->code,
        ]));
        $this->assertInstanceOf(BinaryFileResponse::class, $excelResponse);

        $pdfResponse = $controller->exportPdf(new Request([
            'fleetCompanyCode' => $this->vendorBeta->code,
        ]));
        $this->assertSame(200, $pdfResponse->getStatusCode());
        $this->assertSame('application/pdf', $pdfResponse->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $pdfResponse->getContent());
    }

    public function test_views_render_filter_cards_and_datatables_apply_filters(): void
    {
        $this->actingAs($this->user);
        View::share('errors', new ViewErrorBag());

        Order::create([
            'code' => 'ORD-DT-001',
            'shipmentNumber' => 'SHP-DT-001',
            'orderDate' => '2026-10-08',
            'customerCode' => $this->customerPhl->code,
            'routeCode' => $this->routePhl->code,
            'qty' => 1,
            'fleetCode' => $this->fleetAlpha->code,
            'plateNumber' => 'L 1001 AA',
            'status' => 4,
            'vendorPrice' => 300000,
        ]);

        /** @var VendorInvoiceController $invoiceController */
        $invoiceController = app(VendorInvoiceController::class);
        /** @var VendorPaymentListController $paymentController */
        $paymentController = app(VendorPaymentListController::class);

        foreach ([
            $invoiceController->indexWaiting()->render(),
            $invoiceController->indexUnpaid()->render(),
            $invoiceController->indexPartial()->render(),
            $invoiceController->indexPaid()->render(),
            $paymentController->index()->render(),
        ] as $html) {
            $this->assertStringContainsString('class="filter-card"', $html);
            $this->assertStringContainsString('id="filterForm"', $html);
            $this->assertStringContainsString('id="export-excel"', $html);
            $this->assertStringContainsString('id="export-pdf"', $html);
            $this->assertStringContainsString('html[data-bs-theme="dark"] .filter-card', $html);
            $this->assertStringContainsString('syncExportUrls()', $html);
        }

        $waitingReq = Request::create('/datatable/vendor-invoice/waiting', 'GET', [
            'fleetCompanyCode' => $this->vendorAlpha->code,
            'plateNumber' => '1001',
        ]);
        $waitingReq->headers->set('X-Requested-With', 'XMLHttpRequest');
        $waitingPayload = $invoiceController->datatableWaiting($waitingReq)->getData(true);
        $this->assertCount(1, $waitingPayload['data']);
        $this->assertSame('ORD-DT-001', $waitingPayload['data'][0]['code']);

        $waitingMissReq = Request::create('/datatable/vendor-invoice/waiting', 'GET', [
            'fleetCompanyCode' => $this->vendorBeta->code,
        ]);
        $waitingMissReq->headers->set('X-Requested-With', 'XMLHttpRequest');
        $this->assertCount(0, $invoiceController->datatableWaiting($waitingMissReq)->getData(true)['data']);
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

        Schema::create('company_setting', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->nullable();
            $table->string('name');
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
            $table->decimal('pph', 8, 4)->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('fleet', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('plateNumber');
            $table->string('fleetCompanyCode')->nullable();
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

        Schema::create('customer', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('name');
            $table->string('companyCode')->nullable();
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

        Schema::create('route', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('name');
            $table->string('customerCode')->nullable();
            $table->string('originLocationCode')->nullable();
            $table->string('destinationLocationCode')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('order_status', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('name');
            $table->string('nama')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('order', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('shipmentNumber')->nullable();
            $table->date('orderDate')->nullable();
            $table->date('shipmentDate')->nullable();
            $table->string('customerCode')->nullable();
            $table->string('routeCode')->nullable();
            $table->decimal('qty', 15, 2)->default(0);
            $table->string('driverCode')->nullable();
            $table->string('driverName')->nullable();
            $table->string('fleetCode')->nullable();
            $table->string('plateNumber')->nullable();
            $table->integer('status')->default(4);
            $table->string('companyCode')->nullable();
            $table->decimal('routeAmount', 15, 2)->default(0);
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('vendorPrice', 15, 2)->default(0);
            $table->decimal('vendorPriceSingle', 15, 2)->default(0);
            $table->decimal('personalVendorPrice', 15, 2)->default(0);
            $table->decimal('personalVendorPriceSingle', 15, 2)->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('order_cost', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('componentName')->nullable();
            $table->string('orderCode');
            $table->decimal('nominal', 15, 2)->default(0);
            $table->string('type')->nullable();
            $table->timestamps();
        });

        Schema::create('bank_account', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('bankCode')->nullable();
            $table->string('name');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('user_bank', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('accountNumber')->nullable();
            $table->string('accountName')->nullable();
            $table->integer('type')->default(2);
            $table->string('bankCode')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('live_mutation', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('userBankCode');
            $table->decimal('debit', 18, 2)->default(0);
            $table->decimal('credit', 18, 2)->default(0);
            $table->decimal('balance', 18, 2)->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('mutation', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('userBankCode');
            $table->date('date');
            $table->text('description')->nullable();
            $table->decimal('nominal', 18, 2)->default(0);
            $table->string('type');
            $table->string('transactionTypeCode')->nullable();
            $table->string('transactionCode')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('vendor_nota_sequence', function (Blueprint $table) {
            $table->string('prefix', 10)->primary();
            $table->unsignedInteger('year');
            $table->unsignedInteger('last_sequence')->default(0);
            $table->timestamps();
        });

        Schema::create('vendor_payment', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->nullable();
            $table->string('orderCode');
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('remaining_amount', 15, 2)->default(0);
            $table->string('payment_status')->default('pending');
            $table->string('nota_number')->nullable();
            $table->string('user_bank_code')->nullable();
            $table->decimal('ppn_amount', 15, 2)->default(0);
            $table->decimal('ppn_rate', 8, 4)->default(0);
            $table->decimal('pph_amount', 15, 2)->default(0);
            $table->decimal('pph_rate', 8, 4)->default(0);
            $table->decimal('claim_amount', 15, 2)->default(0);
            $table->string('description')->nullable();
            $table->date('date')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('vendor_payment_batch', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->string('request_key')->unique();
            $table->string('payload_hash', 64);
            $table->string('status', 20)->default('active');
            $table->date('payment_date');
            $table->string('user_bank_code');
            $table->unsignedBigInteger('amount');
            $table->unsignedInteger('nota_count');
            $table->unsignedInteger('order_count');
            $table->unsignedInteger('fully_paid_count')->default(0);
            $table->unsignedInteger('partial_count')->default(0);
            $table->string('description', 255)->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('vendor_payment_history', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('vendor_payment_id');
            $table->string('batch_code')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->date('payment_date')->nullable();
            $table->string('user_bank_code')->nullable();
            $table->string('description')->nullable();
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
            'code' => 'ADMIN',
            'name' => 'Administrator',
        ]);

        $this->user = User::create([
            'code' => 'USR-01',
            'name' => 'Finance Tester',
            'username' => 'finance_tester',
            'password' => bcrypt('secret'),
            'roleCode' => 'ADMIN',
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

        $this->vendorAlpha = FleetCompany::create([
            'code' => 'FC-ALPHA',
            'name' => 'CV VENDOR ALPHA',
            'type' => 'External',
            'pph' => 0,
        ]);

        $this->vendorBeta = FleetCompany::create([
            'code' => 'FC-BETA',
            'name' => 'CV VENDOR BETA',
            'type' => 'External',
            'pph' => 0,
        ]);

        $this->fleetAlpha = Fleet::create([
            'code' => 'FLT-ALPHA',
            'plateNumber' => 'L 1001 AA',
            'fleetCompanyCode' => $this->vendorAlpha->code,
        ]);

        $this->fleetBeta = Fleet::create([
            'code' => 'FLT-BETA',
            'plateNumber' => 'B 2002 BB',
            'fleetCompanyCode' => $this->vendorBeta->code,
        ]);

        $this->customerPhl = Customer::create([
            'code' => 'CUST-PHL',
            'name' => 'PT CUSTOMER PHL',
            'companyCode' => $this->companyPhl->code,
        ]);

        $this->customerWtms = Customer::create([
            'code' => 'CUST-WTMS',
            'name' => 'PT CUSTOMER WTMS',
            'companyCode' => $this->companyWtms->code,
        ]);

        $origin = Location::create(['code' => 'LOC-1', 'name' => 'Surabaya']);
        $dest = Location::create(['code' => 'LOC-2', 'name' => 'Jakarta']);

        $this->routePhl = Route::create([
            'code' => 'RTE-PHL',
            'name' => 'Surabaya - Jakarta',
            'customerCode' => $this->customerPhl->code,
            'originLocationCode' => $origin->code,
            'destinationLocationCode' => $dest->code,
        ]);

        $this->routeWtms = Route::create([
            'code' => 'RTE-WTMS',
            'name' => 'Surabaya - Jakarta',
            'customerCode' => $this->customerWtms->code,
            'originLocationCode' => $origin->code,
            'destinationLocationCode' => $dest->code,
        ]);

        $bankBca = BankAccount::create(['code' => 'BNK-BCA', 'bankCode' => 'BCA', 'name' => 'BCA']);
        $bankMandiri = BankAccount::create(['code' => 'BNK-MDR', 'bankCode' => 'MDR', 'name' => 'Mandiri']);

        $this->userBankPrimary = UserBank::create([
            'code' => 'UB-01',
            'accountNumber' => '1111111111',
            'accountName' => 'PHL BCA',
            'type' => 2,
            'bankCode' => $bankBca->code,
        ]);

        $this->userBankSecondary = UserBank::create([
            'code' => 'UB-02',
            'accountNumber' => '2222222222',
            'accountName' => 'WTMS MANDIRI',
            'type' => 2,
            'bankCode' => $bankMandiri->code,
        ]);

        LiveMutation::create([
            'code' => 'LM-01',
            'userBankCode' => $this->userBankPrimary->code,
            'debit' => 10000000,
            'credit' => 0,
            'balance' => 10000000,
        ]);

        LiveMutation::create([
            'code' => 'LM-02',
            'userBankCode' => $this->userBankSecondary->code,
            'debit' => 10000000,
            'credit' => 0,
            'balance' => 10000000,
        ]);
    }
}
