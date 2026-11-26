<?php

namespace Tests\Feature;

use App\Http\Controllers\Finance\InvoiceController;
use App\Http\Controllers\Finance\InvoicePaymentController;
use App\Http\Controllers\Finance\InvoicePaymentTransactionController;
use App\Http\Controllers\Operational\OrderController;
use App\Http\Controllers\Operational\ReturnDoController;
use App\Models\Bank\BankAccount;
use App\Models\Bank\UserBank;
use App\Models\CompanySetting;
use App\Models\Data\Route;
use App\Models\Finance\Invoice;
use App\Models\Finance\InvoiceDetail;
use App\Models\Finance\InvoicePayment;
use App\Models\Finance\InvoicePaymentClaim;
use App\Models\Finance\InvoicePaymentTransaction;
use App\Models\Master\Company;
use App\Models\Master\Customer;
use App\Models\Master\Employee;
use App\Models\Master\Fleet;
use App\Models\Master\FleetCompany;
use App\Models\Master\FleetType;
use App\Models\Master\Location;
use App\Models\Master\OrderType;
use App\Models\Operational\Order;
use App\Models\Operational\OrderStatus;
use App\Models\Role;
use App\Models\User;
use App\Services\Finance\InvoicePaymentService;
use App\Services\Finance\InvoicePaymentTransactionService;
use App\Services\Finance\InvoiceService;
use App\Services\Operational\ReturnDoService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\TestCase;

class OperationalAndInvoiceFilterExportTest extends TestCase
{
    protected User $user;

    protected Company $companyPhl;

    protected Company $companyWtms;

    protected Customer $customerPhl;

    protected Customer $customerWtms;

    protected Fleet $fleetAlpha;

    protected Fleet $fleetBeta;

    protected Employee $driverBudi;

    protected Employee $driverAndi;

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
            'invoice_payment_claim',
            'invoice_payment',
            'invoice_payment_transaction',
            'invoice_detail',
            'invoice',
            'order_detail',
            'order_cost',
            'order_material',
            'order_driver',
            'order_status',
            'order_type',
            'order',
            'route_detail',
            'route',
            'location',
            'material',
            'unit',
            'cost_component',
            'customer_pic',
            'customer_detail',
            'customer',
            'employee',
            'fleet',
            'fleet_type',
            'fleet_company',
            'company',
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

    public function test_operational_order_pdf_export_and_view(): void
    {
        $this->actingAs($this->user);
        View::share('errors', new ViewErrorBag());

        Order::create([
            'code' => 'ORD-OP-001',
            'shipmentNumber' => 'SHP-OP-001',
            'orderDate' => '2026-10-05',
            'customerCode' => $this->customerPhl->code,
            'routeCode' => $this->routePhl->code,
            'qty' => 3,
            'driverCode' => $this->driverBudi->code,
            'driverName' => $this->driverBudi->name,
            'fleetCode' => $this->fleetAlpha->code,
            'plateNumber' => 'L 1001 AA',
            'status' => 1,
            'routeAmount' => 1500000,
            'personalVendorPrice' => 900000,
        ]);

        Order::create([
            'code' => 'ORD-OP-002',
            'shipmentNumber' => 'SHP-OP-002',
            'orderDate' => '2026-10-20',
            'customerCode' => $this->customerWtms->code,
            'routeCode' => $this->routeWtms->code,
            'qty' => 2,
            'driverCode' => $this->driverAndi->code,
            'driverName' => $this->driverAndi->name,
            'fleetCode' => $this->fleetBeta->code,
            'plateNumber' => 'B 2002 BB',
            'status' => 2,
            'routeAmount' => 2000000,
            'vendorPrice' => 1200000,
        ]);

        /** @var OrderController $controller */
        $controller = app(OrderController::class);

        $html = $controller->index()->render();
        $this->assertStringContainsString('id="export-data"', $html);
        $this->assertStringContainsString('id="export-pdf"', $html);
        $this->assertStringContainsString('syncExportUrls()', $html);

        $pdfResponse = $controller->pdfOrder(new Request([
            'startDate' => '2026-10-01',
            'endDate' => '2026-10-10',
            'plateNumber' => '1001',
            'driverName' => 'Budi',
            'customerName' => 'PHL',
        ]));

        $this->assertSame(200, $pdfResponse->getStatusCode());
        $this->assertSame('application/pdf', $pdfResponse->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $pdfResponse->getContent());
    }

    public function test_operational_return_do_filters_exports_and_view(): void
    {
        $this->actingAs($this->user);
        View::share('errors', new ViewErrorBag());

        $ordUninvoiced = Order::create([
            'code' => 'ORD-RDO-001',
            'shipmentNumber' => 'SHP-RDO-001',
            'orderDate' => '2026-10-04',
            'returnDate' => '2026-10-06',
            'customerCode' => $this->customerPhl->code,
            'routeCode' => $this->routePhl->code,
            'qty' => 1,
            'driverCode' => $this->driverBudi->code,
            'driverName' => $this->driverBudi->name,
            'fleetCode' => $this->fleetAlpha->code,
            'plateNumber' => 'L 1001 AA',
            'status' => 4,
        ]);

        $ordInvoiced = Order::create([
            'code' => 'ORD-RDO-002',
            'shipmentNumber' => 'SHP-RDO-002',
            'orderDate' => '2026-10-18',
            'returnDate' => '2026-10-20',
            'customerCode' => $this->customerWtms->code,
            'routeCode' => $this->routeWtms->code,
            'qty' => 1,
            'driverCode' => $this->driverAndi->code,
            'driverName' => $this->driverAndi->name,
            'fleetCode' => $this->fleetBeta->code,
            'plateNumber' => 'B 2002 BB',
            'status' => 5,
        ]);

        $inv = Invoice::create([
            'code' => 'INV-RDO-1',
            'invoiceNumber' => 'INV/WTMS/00001/10/2026',
            'invoiceDate' => '2026-10-21',
            'customerCode' => $this->customerWtms->code,
            'invoiceAmount' => 1000000,
            'status' => Invoice::STATUS_CREATE,
        ]);

        InvoiceDetail::create([
            'code' => 'INVD-1',
            'invoiceCode' => $inv->code,
            'orderCode' => $ordInvoiced->code,
        ]);

        /** @var ReturnDoService $service */
        $service = app(ReturnDoService::class);

        $this->assertCount(2, $service->datatable()->get());
        $this->assertSame(['ORD-RDO-001'], $service->datatable([
            'startDate' => '2026-10-01',
            'endDate' => '2026-10-10',
            'customerName' => 'PHL',
            'plateNumber' => '1001',
            'driverName' => 'Budi',
            'invoiceStatus' => 'uninvoiced',
        ])->get()->pluck('code')->all());

        $this->assertSame(['ORD-RDO-002'], $service->datatable([
            'invoiceStatus' => 'invoiced',
        ])->get()->pluck('code')->all());

        /** @var ReturnDoController $controller */
        $controller = app(ReturnDoController::class);

        $html = $controller->index()->render();
        $this->assertStringContainsString('class="filter-card"', $html);
        $this->assertStringContainsString('id="filterForm"', $html);
        $this->assertStringContainsString('id="export-excel"', $html);
        $this->assertStringContainsString('id="export-pdf"', $html);
        $this->assertStringContainsString('html[data-bs-theme="dark"] .filter-card', $html);
        $this->assertStringContainsString('syncExportUrls()', $html);

        $excelResponse = $controller->exportExcel(new Request(['invoiceStatus' => 'uninvoiced']));
        $this->assertInstanceOf(BinaryFileResponse::class, $excelResponse);

        $pdfResponse = $controller->exportPdf(new Request(['invoiceStatus' => 'invoiced']));
        $this->assertSame(200, $pdfResponse->getStatusCode());
        $this->assertSame('application/pdf', $pdfResponse->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $pdfResponse->getContent());
    }

    public function test_invoice_unpaid_partial_paid_filters_exports_and_views(): void
    {
        $this->actingAs($this->user);
        View::share('errors', new ViewErrorBag());

        $invUnpaid = Invoice::create([
            'code' => 'INV-U-01',
            'invoiceNumber' => 'INV/PHL/00001/10/2026',
            'invoiceDate' => '2026-10-03',
            'overdueDate' => '2026-10-10',
            'customerCode' => $this->customerPhl->code,
            'invoiceAmount' => 1000000,
            'ppnAmount' => 110000,
            'pphAmount' => 20000,
            'status' => Invoice::STATUS_CREATE,
        ]);

        $invPartial = Invoice::create([
            'code' => 'INV-P-01',
            'invoiceNumber' => 'INV/WTMS/00002/10/2026',
            'invoiceDate' => '2026-10-12',
            'overdueDate' => '2026-10-20',
            'customerCode' => $this->customerWtms->code,
            'invoiceAmount' => 2000000,
            'ppnAmount' => 220000,
            'pphAmount' => 40000,
            'status' => Invoice::STATUS_PARTIAL,
        ]);

        $invPaid = Invoice::create([
            'code' => 'INV-F-01',
            'invoiceNumber' => 'INV/PHL/00003/10/2026',
            'invoiceDate' => '2026-10-22',
            'overdueDate' => '2026-10-29',
            'customerCode' => $this->customerPhl->code,
            'invoiceAmount' => 500000,
            'ppnAmount' => 0,
            'pphAmount' => 0,
            'status' => Invoice::STATUS_FULL,
        ]);

        InvoicePayment::create([
            'code' => 'INVP-1',
            'invoiceCode' => $invPartial->code,
            'paymentDate' => '2026-10-14',
            'userBankCode' => $this->userBankPrimary->code,
            'amount' => 1000000,
        ]);

        InvoicePayment::create([
            'code' => 'INVP-2',
            'invoiceCode' => $invPaid->code,
            'paymentDate' => '2026-10-25',
            'userBankCode' => $this->userBankSecondary->code,
            'amount' => 500000,
        ]);

        /** @var InvoiceService $service */
        $service = app(InvoiceService::class);

        $this->assertSame(['INV-U-01'], $service->findUnpaid([
            'startDate' => '2026-10-01',
            'endDate' => '2026-10-05',
            'customerCode' => $this->customerPhl->code,
            'companyCode' => $this->companyPhl->code,
            'invoiceNumber' => '00001',
        ])->pluck('code')->all());

        $this->assertSame([], $service->findUnpaid(['companyCode' => $this->companyWtms->code])->all());

        $this->assertSame(['INV-P-01'], $service->findPartial([
            'customerCode' => $this->customerWtms->code,
            'companyCode' => $this->companyWtms->code,
            'invoiceNumber' => 'WTMS',
        ])->pluck('code')->all());

        $this->assertSame(['INV-F-01'], $service->findPaid([
            'startDate' => '2026-10-20',
            'endDate' => '2026-10-25',
            'companyCode' => $this->companyPhl->code,
        ])->pluck('code')->all());

        /** @var InvoiceController $controller */
        $controller = app(InvoiceController::class);

        foreach ([
            $controller->indexUnpaid()->render(),
            $controller->indexPartial()->render(),
            $controller->indexPaid()->render(),
        ] as $html) {
            $this->assertStringContainsString('class="filter-card"', $html);
            $this->assertStringContainsString('id="filterForm"', $html);
            $this->assertStringContainsString('id="export-excel"', $html);
            $this->assertStringContainsString('id="export-pdf"', $html);
            $this->assertStringContainsString('html[data-bs-theme="dark"] .filter-card', $html);
            $this->assertStringContainsString('syncExportUrls()', $html);
        }

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

    public function test_invoice_payment_and_payment_transaction_filters_exports_and_views(): void
    {
        $this->actingAs($this->user);
        View::share('errors', new ViewErrorBag());

        $inv1 = Invoice::create([
            'code' => 'INV-TX-1',
            'invoiceNumber' => 'INV/PHL/00010/10/2026',
            'invoiceDate' => '2026-10-01',
            'customerCode' => $this->customerPhl->code,
            'invoiceAmount' => 1000000,
            'status' => Invoice::STATUS_FULL,
        ]);

        $inv2 = Invoice::create([
            'code' => 'INV-TX-2',
            'invoiceNumber' => 'INV/WTMS/00020/10/2026',
            'invoiceDate' => '2026-10-02',
            'customerCode' => $this->customerWtms->code,
            'invoiceAmount' => 2000000,
            'status' => Invoice::STATUS_PARTIAL,
        ]);

        $trx1 = InvoicePaymentTransaction::create([
            'code' => 'INVT-001',
            'paymentDate' => '2026-10-05',
            'customerCode' => $this->customerPhl->code,
            'userBankCode' => $this->userBankPrimary->code,
            'amount' => 1000000,
            'totalClaim' => 0,
            'description' => 'Pelunasan PHL',
        ]);

        $trx2 = InvoicePaymentTransaction::create([
            'code' => 'INVT-002',
            'paymentDate' => '2026-10-20',
            'customerCode' => $this->customerWtms->code,
            'userBankCode' => $this->userBankSecondary->code,
            'amount' => 1200000,
            'totalClaim' => 100000,
            'description' => 'Cicilan WTMS',
        ]);

        InvoicePayment::create([
            'code' => 'INVP-TX-1',
            'transactionCode' => $trx1->code,
            'invoiceCode' => $inv1->code,
            'paymentDate' => '2026-10-05',
            'userBankCode' => $this->userBankPrimary->code,
            'amount' => 1000000,
            'description' => 'Pelunasan PHL',
        ]);

        InvoicePayment::create([
            'code' => 'INVP-TX-2',
            'transactionCode' => $trx2->code,
            'invoiceCode' => $inv2->code,
            'paymentDate' => '2026-10-20',
            'userBankCode' => $this->userBankSecondary->code,
            'amount' => 1200000,
            'description' => 'Cicilan WTMS',
        ]);

        InvoicePaymentClaim::create([
            'code' => 'CLM-TX-2',
            'transactionCode' => $trx2->code,
            'invoiceCode' => $inv2->code,
            'amount' => 100000,
            'description' => 'Claim selisih timbang',
        ]);

        /** @var InvoicePaymentService $paymentSvc */
        $paymentSvc = app(InvoicePaymentService::class);
        $this->assertSame(['INVP-TX-1'], $paymentSvc->datatable([
            'startDate' => '2026-10-01',
            'endDate' => '2026-10-10',
            'customerCode' => $this->customerPhl->code,
            'userBankCode' => $this->userBankPrimary->code,
            'invoiceNumber' => '00010',
        ])->pluck('code')->all());

        /** @var InvoicePaymentTransactionService $trxSvc */
        $trxSvc = app(InvoicePaymentTransactionService::class);
        $this->assertSame(['INVT-002'], $trxSvc->datatable([
            'startDate' => '2026-10-15',
            'endDate' => '2026-10-25',
            'customerCode' => $this->customerWtms->code,
            'userBankCode' => $this->userBankSecondary->code,
        ])->pluck('code')->all());

        /** @var InvoicePaymentController $payCtrl */
        $payCtrl = app(InvoicePaymentController::class);
        /** @var InvoicePaymentTransactionController $trxCtrl */
        $trxCtrl = app(InvoicePaymentTransactionController::class);

        foreach ([
            $payCtrl->index()->render(),
            $trxCtrl->index()->render(),
        ] as $html) {
            $this->assertStringContainsString('class="filter-card"', $html);
            $this->assertStringContainsString('id="filterForm"', $html);
            $this->assertStringContainsString('id="export-excel"', $html);
            $this->assertStringContainsString('id="export-pdf"', $html);
            $this->assertStringContainsString('html[data-bs-theme="dark"] .filter-card', $html);
            $this->assertStringContainsString('syncExportUrls()', $html);
        }

        $this->assertInstanceOf(BinaryFileResponse::class, $payCtrl->exportExcel(new Request(['customerCode' => $this->customerPhl->code])));
        $payPdf = $payCtrl->exportPdf(new Request(['customerCode' => $this->customerPhl->code]));
        $this->assertSame(200, $payPdf->getStatusCode());
        $this->assertSame('application/pdf', $payPdf->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $payPdf->getContent());

        $this->assertInstanceOf(BinaryFileResponse::class, $trxCtrl->exportExcel(new Request(['userBankCode' => $this->userBankSecondary->code])));
        $trxPdf = $trxCtrl->exportPdf(new Request(['userBankCode' => $this->userBankSecondary->code]));
        $this->assertSame(200, $trxPdf->getStatusCode());
        $this->assertSame('application/pdf', $trxPdf->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $trxPdf->getContent());
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

        Schema::create('fleet', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('plateNumber');
            $table->string('fleetTypeCode')->nullable();
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
            $table->integer('isDo')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('customer_detail', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('name');
            $table->string('customerCode')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('customer_pic', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->nullable();
            $table->string('picName')->nullable();
            $table->string('phone')->nullable();
            $table->string('customerCode')->nullable();
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
            $table->string('routeTypeCode')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('route_detail', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('routeCode')->nullable();
            $table->string('componentCode')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
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

        Schema::create('cost_component', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('name');
            $table->string('type')->nullable();
            $table->decimal('price', 15, 2)->default(0);
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
            $table->date('returnDate')->nullable();
            $table->text('returnDescription')->nullable();
            $table->string('customerCode')->nullable();
            $table->string('materialCode')->nullable();
            $table->string('unitCode')->nullable();
            $table->string('routeCode')->nullable();
            $table->string('orderTypeCode')->nullable();
            $table->decimal('qty', 15, 2)->default(0);
            $table->string('driverCode')->nullable();
            $table->string('driverName')->nullable();
            $table->string('fleetCode')->nullable();
            $table->string('plateNumber')->nullable();
            $table->integer('status')->default(0);
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
            $table->string('componentType')->nullable();
            $table->string('componentName')->nullable();
            $table->string('orderCode');
            $table->decimal('nominal', 15, 2)->default(0);
            $table->string('type')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('order_material', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->nullable();
            $table->string('orderCode')->nullable();
            $table->string('materialCode')->nullable();
            $table->string('unitCode')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('order_driver', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->nullable();
            $table->string('orderCode')->nullable();
            $table->string('driverCode')->nullable();
            $table->string('driverName')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('order_detail', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('order_id');
            $table->string('file')->nullable();
            $table->string('type')->nullable();
            $table->timestamps();
        });

        Schema::create('invoice', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('invoiceNumber')->nullable();
            $table->string('poNumber')->nullable();
            $table->string('receiptNumber')->nullable();
            $table->date('invoiceDate')->nullable();
            $table->date('overdueDate')->nullable();
            $table->text('notes')->nullable();
            $table->integer('invoiceAmount')->default(0);
            $table->integer('routeAmount')->default(0);
            $table->integer('onChargeAmount')->default(0);
            $table->integer('ppnAmount')->default(0);
            $table->integer('pphAmount')->default(0);
            $table->boolean('usePpn')->default(false);
            $table->boolean('usePph')->default(false);
            $table->decimal('ppnRate', 8, 4)->default(0);
            $table->decimal('pphRate', 8, 4)->default(0);
            $table->string('pphBaseType')->nullable();
            $table->integer('pphBaseAmount')->default(0);
            $table->string('customerCode')->nullable();
            $table->integer('status')->default(1);
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

        Schema::create('invoice_payment_transaction', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->date('paymentDate')->nullable();
            $table->string('customerCode')->nullable();
            $table->string('userBankCode')->nullable();
            $table->integer('amount')->default(0);
            $table->integer('totalClaim')->default(0);
            $table->text('description')->nullable();
            $table->string('paymentReceipt')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('invoice_payment', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->date('paymentDate')->nullable();
            $table->string('invoiceCode')->nullable();
            $table->string('transactionCode')->nullable();
            $table->integer('nominal')->default(0);
            $table->text('description')->nullable();
            $table->integer('amount')->default(0);
            $table->integer('ppnAmount')->default(0);
            $table->integer('pphAmount')->default(0);
            $table->string('paymentReceipt')->nullable();
            $table->string('userBankCode')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('invoice_payment_claim', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('transactionCode')->nullable();
            $table->string('invoiceCode')->nullable();
            $table->text('description')->nullable();
            $table->integer('amount')->default(0);
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

        $fcExt = FleetCompany::create([
            'code' => 'FC-EXT',
            'name' => 'CV VENDOR BETA',
            'type' => 'External',
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
            'fleetCompanyCode' => $fcExt->code,
        ]);

        $this->driverBudi = Employee::create(['code' => 'DRV-1', 'name' => 'Budi Santoso']);
        $this->driverAndi = Employee::create(['code' => 'DRV-2', 'name' => 'Andi Wijaya']);

        $this->customerPhl = Customer::create([
            'code' => 'CUST-PHL',
            'name' => 'PT CUSTOMER PHL',
            'companyCode' => $this->companyPhl->code,
            'isDo' => 1,
        ]);

        $this->customerWtms = Customer::create([
            'code' => 'CUST-WTMS',
            'name' => 'PT CUSTOMER WTMS',
            'companyCode' => $this->companyWtms->code,
            'isDo' => 1,
        ]);

        $origin = Location::create(['code' => 'LOC-1', 'name' => 'Surabaya']);
        $dest = Location::create(['code' => 'LOC-2', 'name' => 'Jakarta']);

        $this->routePhl = Route::create([
            'code' => 'RTE-PHL',
            'name' => 'Surabaya - Jakarta',
            'customerCode' => $this->customerPhl->code,
            'originLocationCode' => $origin->code,
            'destinationLocationCode' => $dest->code,
            'price' => 500000,
        ]);

        $this->routeWtms = Route::create([
            'code' => 'RTE-WTMS',
            'name' => 'Surabaya - Semarang',
            'customerCode' => $this->customerWtms->code,
            'originLocationCode' => $origin->code,
            'destinationLocationCode' => $dest->code,
            'price' => 1000000,
        ]);

        OrderType::create(['code' => 'Int', 'name' => 'Internal']);
        OrderType::create(['code' => 'Ext', 'name' => 'External']);

        OrderStatus::create(['code' => '1', 'name' => 'Created', 'nama' => 'Dibuat']);
        OrderStatus::create(['code' => '2', 'name' => 'In Progress', 'nama' => 'Proses']);
        OrderStatus::create(['code' => '4', 'name' => 'Return DO', 'nama' => 'DO Kembali']);
        OrderStatus::create(['code' => '5', 'name' => 'Invoiced', 'nama' => 'Sudah Difaktur']);

        $bankBca = BankAccount::create(['code' => 'BNK-BCA', 'name' => 'BCA']);
        $bankMandiri = BankAccount::create(['code' => 'BNK-MDR', 'name' => 'Mandiri']);

        $this->userBankPrimary = UserBank::create([
            'code' => 'UB-BCA',
            'accountNumber' => '111222333',
            'accountName' => 'PT PHL',
            'type' => 2,
            'bankCode' => $bankBca->code,
        ]);

        $this->userBankSecondary = UserBank::create([
            'code' => 'UB-MDR',
            'accountNumber' => '444555666',
            'accountName' => 'PT WTMS',
            'type' => 2,
            'bankCode' => $bankMandiri->code,
        ]);
    }
}
