<?php

namespace Tests\Feature;

use App\Http\Controllers\Finance\VendorInvoiceController;
use App\Models\Bank\BankAccount;
use App\Models\Bank\UserBank;
use App\Models\CompanySetting;
use App\Models\Data\Route;
use App\Models\Finance\VendorPayment;
use App\Models\LiveMutation;
use App\Models\Master\Company;
use App\Models\Master\Customer;
use App\Models\Master\Employee;
use App\Models\Master\Fleet;
use App\Models\Master\FleetCompany;
use App\Models\Master\Location;
use App\Models\Master\Material;
use App\Models\Operational\Order;
use App\Models\Operational\OrderCost;
use App\Models\Operational\OrderMaterial;
use App\Models\Role;
use App\Models\User;
use App\Services\Finance\VendorPaymentService;
use DomainException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use ReflectionMethod;
use Tests\TestCase;

class VendorInternalCompanyInvoiceTest extends TestCase
{
    protected User $user;

    protected Company $companyPhl;

    protected Company $companyWtms;

    protected FleetCompany $internalFleetWtms;

    protected FleetCompany $internalFleetPhl;

    protected FleetCompany $externalFleetVendor;

    protected Fleet $fleetWtms;

    protected Fleet $fleetPhl;

    protected Fleet $fleetExternal;

    protected Customer $customerPhl;

    protected Route $route;

    protected UserBank $userBank;

    protected LiveMutation $liveMutation;

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
            'invoice_detail',
            'vendor_payment_history',
            'vendor_payment_batch',
            'vendor_payment',
            'vendor_nota_sequence',
            'mutation',
            'live_mutation',
            'user_bank',
            'bank_account',
            'order_cost',
            'cost_component',
            'order_material',
            'order',
            'order_status',
            'route',
            'material',
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

    public function test_inter_company_internal_order_appears_in_waiting_generates_nota_syncs_renders_pdf_and_pays(): void
    {
        $this->actingAs($this->user);

        $order = Order::create([
            'code' => 'ORD-INT-DIFF-001',
            'shipmentNumber' => 'SHP-INT-001',
            'orderDate' => '2026-10-15',
            'shipmentDate' => '2026-10-15',
            'customerCode' => $this->customerPhl->code,
            'routeCode' => $this->route->code,
            'qty' => 10,
            'fleetCode' => $this->fleetWtms->code,
            'plateNumber' => $this->fleetWtms->plateNumber,
            'status' => 4,
            'companyCode' => $this->companyPhl->code,
            'routeAmount' => 1500000,
            'price' => 150000,
            'vendorPrice' => 0,
            'vendorPriceSingle' => 0,
            'personalVendorPrice' => 800000,
            'personalVendorPriceSingle' => 80000,
        ]);

        OrderCost::create([
            'code' => 'OC-001',
            'orderCode' => $order->code,
            'componentName' => 'Biaya Bongkar',
            'nominal' => 50000,
            'type' => 'On Charge',
        ]);

        /** @var VendorPaymentService $service */
        $service = app(VendorPaymentService::class);

        $waitingCodes = $service->findWaitingOrders()->pluck('code')->all();
        $allCodes = $service->findAll()->pluck('code')->all();

        $this->assertContains($order->code, $waitingCodes);
        $this->assertContains($order->code, $allCodes);
        $this->assertSame(800000.0, $service->vendorBaseAmount($order));
        $this->assertSame(850000.0, $service->vendorBillingAmount($order));

        $notaNumber = $service->assignNota([$order->code], $this->userBank->code, 'Vendor Payment', 0, 0);
        $this->assertStringStartsWith('PHL/', $notaNumber);

        $vendorPayment = VendorPayment::where('orderCode', $order->code)->firstOrFail();
        $this->assertSame($notaNumber, $vendorPayment->nota_number);
        $this->assertSame(850000, (int) round((float) $vendorPayment->amount));
        $this->assertSame(850000, (int) round((float) $vendorPayment->remaining_amount));

        // Update personalVendorPrice & sinkronkan nominal nota
        $order->update([
            'personalVendorPrice' => 900000,
            'personalVendorPriceSingle' => 90000,
        ]);
        $syncResult = $service->syncNotaByOrderCode($order->code, 'Vendor Payment');
        $this->assertTrue($syncResult['changed']);

        $vendorPayment->refresh();
        $this->assertSame(950000, (int) round((float) $vendorPayment->amount));
        $this->assertSame(950000, (int) round((float) $vendorPayment->remaining_amount));

        // Cek kalkulasi PDF Nota dan rendering PDF
        /** @var VendorInvoiceController $controller */
        $controller = app(VendorInvoiceController::class);
        $buildPdf = new ReflectionMethod($controller, 'buildNotaPdf');
        $buildPdf->setAccessible(true);
        $pdfDoc = $buildPdf->invoke($controller, [$order->code]);
        $this->assertSame($notaNumber, $pdfDoc['notaNumber']);
        $this->assertStringStartsWith('%PDF', $pdfDoc['content']);

        $renderedHtml = view('finance.vendor-payment.pdf.general-phl-multi', [
            'orders' => Order::with(['fleet.company', 'driver', 'customer.company', 'route.originLocation', 'route.destinationLocation', 'orderMaterial.material', 'cost'])
                ->where('code', $order->code)
                ->get(),
            'customer' => $this->customerPhl,
            'company' => CompanySetting::first(),
            'totalSubtotal' => 900000,
            'totalAdditionalCost' => 50000,
            'totalPpnAmount' => 0,
            'totalPphAmount' => 0,
            'totalPpnRate' => 0,
            'totalPphRate' => 0,
            'totalClaim' => 0,
            'totalGrandTotal' => 950000,
            'notaNumber' => $notaNumber,
            'userBank' => $this->userBank,
            'orderLineAmounts' => [
                $order->code => $service->resolveOrderVendorLineAmounts($order->fresh('route')),
            ],
            'paymentHistories' => collect(),
            'paymentHistoryTotal' => 0,
        ])->render();
        $this->assertStringContainsString('90.000', $renderedHtml);
        $this->assertStringContainsString('900.000', $renderedHtml);

        // Bayar lunas via store()
        $payRequest = new Request([
            'requestKey' => (string) Str::uuid(),
            'userBankCode' => $this->userBank->code,
            'date' => '2026-10-20',
            'description' => 'Pelunasan antar PT internal',
            'payments' => [
                [
                    'nota_number' => $notaNumber,
                    'amount' => 950000,
                    'expected_remaining' => 950000,
                ],
            ],
        ]);

        $batchResult = DB::transaction(fn () => $service->store($payRequest, 'Vendor Payment'));
        $this->assertSame(950000, $batchResult['payment_amount']);

        $vendorPayment->refresh();
        $order->refresh();
        $this->liveMutation->refresh();

        $this->assertSame('paid', $vendorPayment->payment_status);
        $this->assertSame(0, (int) round((float) $vendorPayment->remaining_amount));
        $this->assertSame(6, (int) $order->status);
        $this->assertSame(950000, (int) round((float) $this->liveMutation->credit));
        $this->assertSame(9050000, (int) round((float) $this->liveMutation->balance));
    }

    public function test_same_company_internal_order_is_excluded_from_waiting_and_rejected_on_assign_and_store(): void
    {
        $this->actingAs($this->user);

        $sameCompanyOrder = Order::create([
            'code' => 'ORD-INT-SAME-001',
            'shipmentNumber' => 'SHP-SAME-001',
            'orderDate' => '2026-10-15',
            'shipmentDate' => '2026-10-15',
            'customerCode' => $this->customerPhl->code,
            'routeCode' => $this->route->code,
            'qty' => 10,
            'fleetCode' => $this->fleetPhl->code,
            'plateNumber' => $this->fleetPhl->plateNumber,
            'status' => 4,
            'companyCode' => $this->companyPhl->code,
            'routeAmount' => 1500000,
            'price' => 150000,
            'vendorPrice' => 1500000,
            'vendorPriceSingle' => 150000,
            'personalVendorPrice' => 0,
            'personalVendorPriceSingle' => 0,
        ]);

        /** @var VendorPaymentService $service */
        $service = app(VendorPaymentService::class);

        $this->assertFalse($service->isEligibleVendorOrder($sameCompanyOrder->fresh(['fleet.company', 'customer.company'])));
        $this->assertNotContains($sameCompanyOrder->code, $service->findWaitingOrders()->pluck('code')->all());
        $this->assertNotContains($sameCompanyOrder->code, $service->findAll()->pluck('code')->all());

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Nota hanya dapat dibuat untuk order vendor eksternal atau antar PT internal yang berbeda perusahaan.');

        $service->assignNota([$sameCompanyOrder->code], $this->userBank->code, 'Vendor Payment', 0, 0);
    }

    public function test_same_company_internal_nota_is_rejected_on_payment_store(): void
    {
        $this->actingAs($this->user);

        $sameCompanyOrder = Order::create([
            'code' => 'ORD-INT-SAME-002',
            'shipmentNumber' => 'SHP-SAME-002',
            'orderDate' => '2026-10-15',
            'shipmentDate' => '2026-10-15',
            'customerCode' => $this->customerPhl->code,
            'routeCode' => $this->route->code,
            'qty' => 10,
            'fleetCode' => $this->fleetPhl->code,
            'plateNumber' => $this->fleetPhl->plateNumber,
            'status' => 4,
            'companyCode' => $this->companyPhl->code,
            'routeAmount' => 1500000,
            'price' => 150000,
            'vendorPrice' => 1500000,
            'vendorPriceSingle' => 150000,
            'personalVendorPrice' => 0,
            'personalVendorPriceSingle' => 0,
        ]);

        VendorPayment::create([
            'orderCode' => $sameCompanyOrder->code,
            'nota_number' => 'PHL/99999/2026',
            'user_bank_code' => $this->userBank->code,
            'amount' => 1500000,
            'paid_amount' => 0,
            'remaining_amount' => 1500000,
            'payment_status' => 'pending',
            'date' => '2026-10-15',
        ]);

        /** @var VendorPaymentService $service */
        $service = app(VendorPaymentService::class);

        $payRequest = new Request([
            'requestKey' => (string) Str::uuid(),
            'userBankCode' => $this->userBank->code,
            'date' => '2026-10-20',
            'description' => 'Should fail',
            'payments' => [
                [
                    'nota_number' => 'PHL/99999/2026',
                    'amount' => 1500000,
                    'expected_remaining' => 1500000,
                ],
            ],
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Nota hanya dapat dibayar untuk order vendor eksternal atau antar PT internal yang berbeda perusahaan.');

        $service->store($payRequest, 'Vendor Payment');
    }

    public function test_external_vendor_order_continues_to_work_without_regression(): void
    {
        $this->actingAs($this->user);

        $externalOrder = Order::create([
            'code' => 'ORD-EXT-001',
            'shipmentNumber' => 'SHP-EXT-001',
            'orderDate' => '2026-10-15',
            'shipmentDate' => '2026-10-15',
            'customerCode' => $this->customerPhl->code,
            'routeCode' => $this->route->code,
            'qty' => 10,
            'fleetCode' => $this->fleetExternal->code,
            'plateNumber' => $this->fleetExternal->plateNumber,
            'status' => 4,
            'companyCode' => $this->companyPhl->code,
            'routeAmount' => 1500000,
            'price' => 150000,
            'vendorPrice' => 1100000,
            'vendorPriceSingle' => 110000,
            'personalVendorPrice' => 0,
            'personalVendorPriceSingle' => 0,
        ]);

        /** @var VendorPaymentService $service */
        $service = app(VendorPaymentService::class);

        $this->assertContains($externalOrder->code, $service->findWaitingOrders()->pluck('code')->all());
        $this->assertSame(1100000.0, $service->vendorBaseAmount($externalOrder));

        $notaNumber = $service->assignNota([$externalOrder->code], $this->userBank->code, 'Vendor Payment', 0, 0);
        $vendorPayment = VendorPayment::where('orderCode', $externalOrder->code)->firstOrFail();
        $this->assertSame(1100000, (int) round((float) $vendorPayment->amount));

        $payRequest = new Request([
            'requestKey' => (string) Str::uuid(),
            'userBankCode' => $this->userBank->code,
            'date' => '2026-10-20',
            'description' => 'Pelunasan external',
            'payments' => [
                [
                    'nota_number' => $notaNumber,
                    'amount' => 1100000,
                    'expected_remaining' => 1100000,
                ],
            ],
        ]);

        $batchResult = DB::transaction(fn () => $service->store($payRequest, 'Vendor Payment'));
        $this->assertSame(1100000, $batchResult['payment_amount']);

        $vendorPayment->refresh();
        $this->assertSame('paid', $vendorPayment->payment_status);
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
            $table->decimal('pph', 8, 4)->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('fleet', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('plateNumber');
            $table->string('fleetCompanyCode')->nullable();
            $table->string('fleetTypeCode')->nullable();
            $table->string('fleetBrandCode')->nullable();
            $table->string('driverCode')->nullable();
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
            $table->decimal('ppn', 8, 4)->default(0);
            $table->decimal('pph', 8, 4)->default(0);
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

        Schema::create('material', function (Blueprint $table) {
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
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('vendorPrice', 15, 2)->default(0);
            $table->decimal('personalVendorPrice', 15, 2)->default(0);
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

        Schema::create('order_material', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('orderCode');
            $table->string('materialCode')->nullable();
            $table->decimal('materialQty', 15, 2)->default(0);
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

        Schema::create('order_cost', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('componentType')->nullable();
            $table->string('componentName')->nullable();
            $table->string('orderCode');
            $table->string('driverCode')->nullable();
            $table->decimal('nominal', 15, 2)->default(0);
            $table->string('type')->nullable();
            $table->string('description')->nullable();
            $table->boolean('is_route')->default(false);
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

        Schema::create('invoice_detail', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('invoiceCode');
            $table->string('orderCode');
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

        $this->internalFleetPhl = FleetCompany::create([
            'code' => 'FC-INT-PHL',
            'name' => 'PT PUTRI HOKI LOGISTIK',
            'type' => 'Internal',
            'companyCode' => $this->companyPhl->code,
            'pph' => 0,
        ]);

        $this->internalFleetWtms = FleetCompany::create([
            'code' => 'FC-INT-WTMS',
            'name' => 'PT WIJAYA TRANS MAKMUR SEJAHTERA',
            'type' => 'Internal',
            'companyCode' => $this->companyWtms->code,
            'pph' => 0,
        ]);

        $this->externalFleetVendor = FleetCompany::create([
            'code' => 'FC-EXT-01',
            'name' => 'CV VENDOR EKSTERNAL',
            'type' => 'External',
            'companyCode' => null,
            'pph' => 0,
        ]);

        $this->fleetPhl = Fleet::create([
            'code' => 'FLT-PHL-01',
            'plateNumber' => 'L 1111 PHL',
            'fleetCompanyCode' => $this->internalFleetPhl->code,
        ]);

        $this->fleetWtms = Fleet::create([
            'code' => 'FLT-WTMS-01',
            'plateNumber' => 'L 2222 WTM',
            'fleetCompanyCode' => $this->internalFleetWtms->code,
        ]);

        $this->fleetExternal = Fleet::create([
            'code' => 'FLT-EXT-01',
            'plateNumber' => 'L 3333 EXT',
            'fleetCompanyCode' => $this->externalFleetVendor->code,
        ]);

        $this->customerPhl = Customer::create([
            'code' => 'CUST-PHL-01',
            'name' => 'PT CUSTOMER PHL',
            'companyCode' => $this->companyPhl->code,
            'ppn' => 0,
            'pph' => 0,
        ]);

        $origin = Location::create(['code' => 'LOC-1', 'name' => 'Surabaya']);
        $destination = Location::create(['code' => 'LOC-2', 'name' => 'Jakarta']);
        Material::create(['code' => 'MAT-1', 'name' => 'Besi']);

        $this->route = Route::create([
            'code' => 'RTE-01',
            'name' => 'Surabaya - Jakarta',
            'customerCode' => $this->customerPhl->code,
            'originLocationCode' => $origin->code,
            'destinationLocationCode' => $destination->code,
            'price' => 150000,
            'vendorPrice' => 110000,
            'personalVendorPrice' => 80000,
        ]);

        $bank = BankAccount::create([
            'code' => 'BNK-01',
            'bankCode' => 'BCA',
            'name' => 'BCA',
        ]);

        $this->userBank = UserBank::create([
            'code' => 'UB-01',
            'accountNumber' => '1234567890',
            'accountName' => 'PT PUTRI HOKI LOGISTIK',
            'type' => 2,
            'bankCode' => $bank->code,
        ]);

        $this->liveMutation = LiveMutation::create([
            'code' => 'LM-01',
            'userBankCode' => $this->userBank->code,
            'debit' => 10000000,
            'credit' => 0,
            'balance' => 10000000,
        ]);

        $year = (int) now()->format('Y');
        foreach (['P', 'PHL', 'WTMS'] as $prefix) {
            DB::table('vendor_nota_sequence')->insert([
                'prefix' => $prefix,
                'year' => $year,
                'last_sequence' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
