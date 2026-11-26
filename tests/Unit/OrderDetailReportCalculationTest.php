<?php

namespace Tests\Unit;

use App\Models\Data\Route;
use App\Models\Finance\Invoice;
use App\Models\Finance\InvoiceDetail;
use App\Models\Finance\OrderPayment;
use App\Models\Master\Company;
use App\Models\Master\CostComponent;
use App\Models\Master\Customer;
use App\Models\Master\Fleet;
use App\Models\Master\FleetCompany;
use App\Models\Master\Location;
use App\Models\Operational\Order;
use App\Models\Operational\OrderCost;
use App\Services\Report\OrderDetailReportService;
use Illuminate\Support\Collection;
use Tests\TestCase;

class OrderDetailReportCalculationTest extends TestCase
{
    protected OrderDetailReportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(OrderDetailReportService::class);
    }

    protected function makeOrderWithCompanies(
        string $fleetType,
        ?string $fleetParentCompanyCode,
        ?string $customerCompanyCode,
        array $attributes = []
    ): Order {
        $fleetCompany = new FleetCompany([
            'type' => $fleetType,
            'companyCode' => $fleetParentCompanyCode,
        ]);

        $fleet = new Fleet([
            'plateNumber' => 'B 1234 XYZ',
        ]);
        $fleet->setRelation('company', $fleetCompany);

        $customer = new Customer([
            'name' => 'PT Customer Test',
            'companyCode' => $customerCompanyCode,
        ]);
        if ($customerCompanyCode !== null) {
            $customer->setRelation('company', new Company(['code' => $customerCompanyCode]));
        }

        $order = new Order(array_merge([
            'shipmentNumber' => 'SHP-001',
            'orderDate' => '2026-11-15',
            'qty' => 10,
            'price' => 250_000,
            'routeAmount' => 2_500_000,
            'vendorPrice' => 0,
            'vendorPriceSingle' => 0,
            'personalVendorPrice' => 0,
            'personalVendorPriceSingle' => 0,
        ], $attributes));

        $order->setRelation('fleet', $fleet);
        $order->setRelation('customer', $customer);
        $order->setRelation('cost', new Collection);

        return $order;
    }

    public function test_same_company_internal_order_uses_customer_route_price_and_amount(): void
    {
        $order = $this->makeOrderWithCompanies('Internal', 'PHL', 'PHL', [
            'qty' => 8,
            'price' => 300_000,
            'routeAmount' => 2_400_000,
            'personalVendorPrice' => 1_900_000,
            'personalVendorPriceSingle' => 237_500,
        ]);

        $omset = $this->service->resolveOmsetAmounts($order);

        $this->assertFalse($this->service->isCrossInternalCompany($order));
        $this->assertFalse($omset['is_cross_internal']);
        $this->assertSame(8.0, $omset['qty']);
        $this->assertSame(300_000.0, $omset['unit_price']);
        $this->assertSame(2_400_000.0, $omset['omset']);
    }

    public function test_same_company_internal_order_falls_back_unit_price_when_order_price_is_zero(): void
    {
        $order = $this->makeOrderWithCompanies('Internal', 'PHL', 'PHL', [
            'qty' => 5,
            'price' => 0,
            'routeAmount' => 1_750_000,
        ]);

        $omset = $this->service->resolveOmsetAmounts($order);

        $this->assertSame(350_000.0, $omset['unit_price']);
        $this->assertSame(1_750_000.0, $omset['omset']);

        $zeroAmountOrder = $this->makeOrderWithCompanies('Internal', 'PHL', 'PHL', [
            'qty' => 5,
            'price' => 0,
            'routeAmount' => 0,
        ]);
        $zeroAmountOrder->setRelation('route', new Route(['price' => 400_000]));

        $fallbackOmset = $this->service->resolveOmsetAmounts($zeroAmountOrder);
        $this->assertSame(400_000.0, $fallbackOmset['unit_price']);
        $this->assertSame(0.0, $fallbackOmset['omset']);
    }

    public function test_cross_company_internal_order_uses_personal_vendor_price_and_prefers_vendor_price(): void
    {
        $personalOrder = $this->makeOrderWithCompanies('Internal', 'WTMS', 'PHL', [
            'qty' => 10,
            'price' => 300_000,
            'routeAmount' => 3_000_000,
            'vendorPrice' => 0,
            'vendorPriceSingle' => 0,
            'personalVendorPrice' => 2_200_000,
            'personalVendorPriceSingle' => 220_000,
        ]);

        $personalOmset = $this->service->resolveOmsetAmounts($personalOrder);

        $this->assertTrue($this->service->isCrossInternalCompany($personalOrder));
        $this->assertTrue($personalOmset['is_cross_internal']);
        $this->assertSame(10.0, $personalOmset['qty']);
        $this->assertSame(220_000.0, $personalOmset['unit_price']);
        $this->assertSame(2_200_000.0, $personalOmset['omset']);

        $preferredVendorOrder = $this->makeOrderWithCompanies('Internal', 'WTMS', 'PHL', [
            'qty' => 10,
            'price' => 300_000,
            'routeAmount' => 3_000_000,
            'vendorPrice' => 2_500_000,
            'vendorPriceSingle' => 250_000,
            'personalVendorPrice' => 2_200_000,
            'personalVendorPriceSingle' => 220_000,
        ]);

        $preferredOmset = $this->service->resolveOmsetAmounts($preferredVendorOrder);

        $this->assertTrue($preferredOmset['is_cross_internal']);
        $this->assertSame(250_000.0, $preferredOmset['unit_price']);
        $this->assertSame(2_500_000.0, $preferredOmset['omset']);
    }

    public function test_external_fleet_order_still_uses_customer_route_price_and_amount(): void
    {
        $externalOrder = $this->makeOrderWithCompanies('External', 'VENDOR-A', 'PHL', [
            'qty' => 12,
            'price' => 450_000,
            'routeAmount' => 5_400_000,
            'vendorPrice' => 4_000_000,
            'vendorPriceSingle' => 333_333,
            'personalVendorPrice' => 3_900_000,
            'personalVendorPriceSingle' => 325_000,
        ]);

        $omset = $this->service->resolveOmsetAmounts($externalOrder);

        $this->assertFalse($this->service->isCrossInternalCompany($externalOrder));
        $this->assertFalse($omset['is_cross_internal']);
        $this->assertSame(12.0, $omset['qty']);
        $this->assertSame(450_000.0, $omset['unit_price']);
        $this->assertSame(5_400_000.0, $omset['omset']);
    }

    public function test_cost_classification_separates_on_charge_commission_salary_and_other_costs(): void
    {
        $order = new Order;

        $onChargeCost = new OrderCost(['type' => 'On Charge', 'componentName' => 'Demurrage', 'nominal' => 200_000]);

        $commissionByType = new OrderCost(['type' => 'Operational', 'componentName' => 'Insentif Sales', 'nominal' => 150_000]);
        $commissionByType->setRelation('costComponent', new CostComponent(['name' => 'Insentif Sales', 'type' => 'commission']));

        $commissionByName = new OrderCost(['type' => 'Operational', 'componentName' => 'Komisi Marketing', 'nominal' => 100_000]);
        $commissionByName->setRelation('costComponent', new CostComponent(['name' => 'Komisi Marketing', 'type' => 'Allowance']));

        $salaryByType = new OrderCost(['type' => 'Operational', 'componentName' => 'Upah Supir', 'nominal' => 350_000]);
        $salaryByType->setRelation('costComponent', new CostComponent(['name' => 'Upah Supir', 'type' => 'salary']));

        $salaryByName = new OrderCost(['type' => 'Operational', 'componentName' => 'Gaji Pokok Driver', 'nominal' => 150_000]);
        $salaryByName->setRelation('costComponent', new CostComponent(['name' => 'Gaji Pokok Driver', 'type' => 'Allowance']));

        $otherCost = new OrderCost(['type' => 'Operational', 'componentName' => 'Uang Solar', 'nominal' => 500_000]);
        $otherCost->setRelation('costComponent', new CostComponent(['name' => 'Uang Solar', 'type' => 'Allowance']));

        $order->setRelation('cost', new Collection([
            $onChargeCost,
            $commissionByType,
            $commissionByName,
            $salaryByType,
            $salaryByName,
            $otherCost,
        ]));

        $classified = $this->service->classifyOrderCosts($order);

        $this->assertCount(1, $classified['income_details']);
        $this->assertSame(200_000.0, $classified['income_total']);

        $this->assertCount(2, $classified['commission_details']);
        $this->assertSame(250_000.0, $classified['commission_total']);

        $this->assertCount(2, $classified['salary_details']);
        $this->assertSame(500_000.0, $classified['salary_total']);

        $this->assertCount(1, $classified['other_cost_details']);
        $this->assertSame(500_000.0, $classified['other_cost_total']);

        $this->assertSame(
            $classified['other_cost_total'] + $classified['commission_total'] + $classified['salary_total'],
            $classified['total_cost']
        );
        $this->assertSame(1_250_000.0, $classified['total_cost']);
    }

    public function test_breakdown_calculates_gross_profit_prorated_invoice_tax_direct_payment_tax_and_net_profit(): void
    {
        // Multi-order invoice with subtotal PPh basis (routeAmount + On Charge)
        $orderA = $this->makeOrderWithCompanies('Internal', 'PHL', 'PHL', [
            'shipmentNumber' => 'SHP-A',
            'qty' => 10,
            'price' => 80_000,
            'routeAmount' => 800_000,
        ]);

        $commissionCost = new OrderCost(['type' => 'Operational', 'componentName' => 'Komisi', 'nominal' => 50_000]);
        $commissionCost->setRelation('costComponent', new CostComponent(['name' => 'Komisi', 'type' => 'commission']));

        $salaryCost = new OrderCost(['type' => 'Operational', 'componentName' => 'Gaji', 'nominal' => 150_000]);
        $salaryCost->setRelation('costComponent', new CostComponent(['name' => 'Gaji', 'type' => 'salary']));

        $otherCost = new OrderCost(['type' => 'Operational', 'componentName' => 'Tol', 'nominal' => 100_000]);
        $otherCost->setRelation('costComponent', new CostComponent(['name' => 'Tol', 'type' => 'Allowance']));

        $orderA->setRelation('cost', new Collection([
            new OrderCost(['type' => 'On Charge', 'componentName' => 'Bongkar', 'nominal' => 200_000]),
            $commissionCost,
            $salaryCost,
            $otherCost,
        ]));

        $orderB = $this->makeOrderWithCompanies('Internal', 'PHL', 'PHL', [
            'shipmentNumber' => 'SHP-B',
            'qty' => 5,
            'price' => 100_000,
            'routeAmount' => 500_000,
        ]);

        $invoice = new Invoice([
            'pphAmount' => 30_000,
            'pphBaseType' => 'subtotal',
        ]);

        $detailA = new InvoiceDetail;
        $detailA->setRelation('order', $orderA);
        $detailA->setRelation('invoice', $invoice);

        $detailB = new InvoiceDetail;
        $detailB->setRelation('order', $orderB);
        $detailB->setRelation('invoice', $invoice);

        $invoice->setRelation('details', new Collection([$detailA, $detailB]));
        $orderA->setRelation('invoiceDetail', $detailA);
        $orderB->setRelation('invoiceDetail', $detailB);

        // Single-order direct payment order (cross-internal company)
        $orderC = $this->makeOrderWithCompanies('Internal', 'WTMS', 'PHL', [
            'shipmentNumber' => 'SHP-C',
            'qty' => 4,
            'price' => 500_000,
            'routeAmount' => 2_000_000,
            'personalVendorPrice' => 1_600_000,
            'personalVendorPriceSingle' => 400_000,
            'driverName' => 'Budi',
        ]);
        $routeC = new Route;
        $routeC->setRelation('originLocation', new Location(['name' => 'Jakarta']));
        $routeC->setRelation('destinationLocation', new Location(['name' => 'Surabaya']));
        $orderC->setRelation('route', $routeC);
        $orderC->setRelation('orderPayment', new OrderPayment(['pph' => 32_000]));

        $cache = [];
        $breakdownA = $this->service->calculateOrderBreakdown($orderA, $cache);
        $breakdownB = $this->service->calculateOrderBreakdown($orderB, $cache);
        $breakdownC = $this->service->calculateOrderBreakdown($orderC, $cache);

        // Order A: omset 800k + income 200k - totalCost 300k = gross 700k; tax = 20k; net = 680k
        $this->assertSame(700_000.0, $breakdownA['gross_profit']);
        $this->assertSame(20_000.0, $breakdownA['tax']);
        $this->assertSame(680_000.0, $breakdownA['net_profit']);

        // Order B: omset 500k + income 0 - totalCost 0 = gross 500k; tax = 10k; net = 490k
        $this->assertSame(500_000.0, $breakdownB['gross_profit']);
        $this->assertSame(10_000.0, $breakdownB['tax']);
        $this->assertSame(490_000.0, $breakdownB['net_profit']);

        // Order C (Cross-Internal): omset 1.600k + 0 - 0 = gross 1.600k; tax = 32k; net = 1.568k
        $this->assertSame(1_600_000.0, $breakdownC['omset']);
        $this->assertSame(1_600_000.0, $breakdownC['gross_profit']);
        $this->assertSame(32_000.0, $breakdownC['tax']);
        $this->assertSame(1_568_000.0, $breakdownC['net_profit']);

        // Verify prepareExportRows & computeGrandTotals
        $exportRows = $this->service->prepareExportRows([$orderA, $orderB, $orderC]);
        $totals = $this->service->computeGrandTotals($exportRows);

        $this->assertCount(3, $exportRows);
        $this->assertSame('Jakarta', $exportRows[2]['origin_name']);
        $this->assertSame('Surabaya', $exportRows[2]['destination_name']);
        $this->assertSame(2_900_000.0, $totals['omset']);
        $this->assertSame(200_000.0, $totals['income_total']);
        $this->assertSame(50_000.0, $totals['commission_total']);
        $this->assertSame(150_000.0, $totals['salary_total']);
        $this->assertSame(100_000.0, $totals['other_cost_total']);
        $this->assertSame(300_000.0, $totals['total_cost']);
        $this->assertSame(2_800_000.0, $totals['gross_profit']);
        $this->assertSame(62_000.0, $totals['tax']);
        $this->assertSame(2_738_000.0, $totals['net_profit']);
    }
}
