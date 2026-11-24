<?php

namespace Tests\Unit;

use App\Models\Data\Route;
use App\Models\Data\RouteDetail;
use App\Models\Data\TonaseBonus;
use App\Models\Finance\Invoice;
use App\Models\Finance\InvoiceDetail;
use App\Models\Finance\OrderPayment;
use App\Models\Inventory\Item;
use App\Models\Master\CostComponent;
use App\Models\Master\Fleet;
use App\Models\Operational\Order;
use App\Models\Operational\OrderCost;
use App\Models\Warehouse\Maintenance;
use App\Models\Warehouse\MaintenanceDetail;
use App\Services\Report\ProfitLossService;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ProfitLossCalculationTest extends TestCase
{
    protected ProfitLossService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ProfitLossService::class);
    }

    public function test_basic_sales_prefers_vendor_price_and_falls_back_to_personal_vendor_price(): void
    {
        $vendorOrder = new Order([
            'vendorPrice' => 2_500_000,
            'personalVendorPrice' => 1_800_000,
        ]);

        $personalOrder = new Order([
            'vendorPrice' => 0,
            'personalVendorPrice' => 1_800_000,
        ]);

        $nullVendorOrder = new Order([
            'vendorPrice' => null,
            'personalVendorPrice' => 950_000,
        ]);

        $this->assertSame(2_500_000.0, $this->service->resolveOrderVendorSales($vendorOrder));
        $this->assertSame(1_800_000.0, $this->service->resolveOrderVendorSales($personalOrder));
        $this->assertSame(950_000.0, $this->service->resolveOrderVendorSales($nullVendorOrder));
    }

    public function test_tax_uses_direct_invoice_pph_and_prorates_multi_order_invoice(): void
    {
        // Single-order invoice
        $singleOrder = new Order(['routeAmount' => 2_000_000]);
        $singleInvoice = new Invoice([
            'pphAmount' => 40_000,
            'pphBaseType' => 'subtotal',
        ]);
        $singleDetail = new InvoiceDetail();
        $singleDetail->setRelation('order', $singleOrder);
        $singleDetail->setRelation('invoice', $singleInvoice);
        $singleInvoice->setRelation('details', new Collection([$singleDetail]));
        $singleOrder->setRelation('invoiceDetail', $singleDetail);

        $this->assertSame(40_000.0, $this->service->resolveOrderTax($singleOrder));

        // Multi-order invoice with subtotal basis (routeAmount + On Charge only)
        $orderA = new Order(['routeAmount' => 800_000]);
        $orderA->setRelation('cost', new Collection([
            new OrderCost(['type' => 'On Charge', 'nominal' => 200_000]),
            new OrderCost(['type' => 'Operational', 'nominal' => 50_000]),
        ]));

        $orderB = new Order(['routeAmount' => 500_000]);
        $orderB->setRelation('cost', new Collection());

        $subtotalInvoice = new Invoice([
            'pphAmount' => 30_000,
            'pphBaseType' => 'subtotal',
        ]);

        $detailA = new InvoiceDetail();
        $detailA->setRelation('order', $orderA);
        $detailA->setRelation('invoice', $subtotalInvoice);
        $detailB = new InvoiceDetail();
        $detailB->setRelation('order', $orderB);
        $detailB->setRelation('invoice', $subtotalInvoice);

        $subtotalInvoice->setRelation('details', new Collection([$detailA, $detailB]));
        $orderA->setRelation('invoiceDetail', $detailA);
        $orderB->setRelation('invoiceDetail', $detailB);

        $this->assertSame(20_000.0, $this->service->resolveOrderTax($orderA));
        $this->assertSame(10_000.0, $this->service->resolveOrderTax($orderB));

        // Multi-order invoice with route basis
        $subtotalInvoice->pphBaseType = 'route';
        $this->assertSame(18_461.54, $this->service->resolveOrderTax($orderA));
        $this->assertSame(11_538.46, $this->service->resolveOrderTax($orderB));
    }

    public function test_tax_uses_order_payment_pph_for_direct_payment_orders(): void
    {
        $paidOrder = new Order();
        $paidOrder->setRelation('orderPayment', new OrderPayment(['pph' => 25_000]));

        $unpaidOrder = new Order();

        $this->assertSame(25_000.0, $this->service->resolveOrderTax($paidOrder));
        $this->assertSame(0.0, $this->service->resolveOrderTax($unpaidOrder));
    }

    public function test_fleet_summary_handles_null_route_and_null_cost_component_safely(): void
    {
        $nullRouteOrder = new Order([
            'vendorPrice' => 1_000_000,
            'qty' => 5,
        ]);
        $nullRouteOrder->setRelation('route', null);
        $nullRouteOrder->setRelation('cost', new Collection());

        $validComponent = new CostComponent(['type' => 'Allowance']);
        $officeComponent = new CostComponent(['type' => 'Allowance Office']);
        $ignoredComponent = new CostComponent(['type' => 'Other']);

        $rdNull = new RouteDetail(['amount' => 99_000, 'percentage' => null]);
        $rdNull->setRelation('costComponent', null);

        $rdAllowance = new RouteDetail(['amount' => 100_000, 'percentage' => null]);
        $rdAllowance->setRelation('costComponent', $validComponent);

        $rdOffice = new RouteDetail(['amount' => 0, 'percentage' => 10]);
        $rdOffice->setRelation('costComponent', $officeComponent);

        $rdIgnored = new RouteDetail(['amount' => 50_000, 'percentage' => null]);
        $rdIgnored->setRelation('costComponent', $ignoredComponent);

        $route = new Route(['price' => 1_000_000]);
        $route->setRelation('routeDetail', new Collection([$rdNull, $rdAllowance, $rdOffice, $rdIgnored]));

        $partialNullOrder = new Order([
            'vendorPrice' => 0,
            'personalVendorPrice' => 500_000,
            'qty' => 5,
        ]);
        $partialNullOrder->setRelation('route', $route);
        $partialNullOrder->setRelation('cost', new Collection());

        $deletedItemDetail = new MaintenanceDetail(['qty' => 3]);
        $deletedItemDetail->setRelation('item', null);

        $validItemDetail = new MaintenanceDetail(['qty' => 2]);
        $validItemDetail->setRelation('item', new Item(['price' => 50_000]));

        $maintenance = new Maintenance();
        $maintenance->setRelation('details', new Collection([$deletedItemDetail, $validItemDetail]));

        $fleet = new Fleet();
        $fleet->setRelation('orders', new Collection([$nullRouteOrder, $partialNullOrder]));
        $fleet->setRelation('maintenances', new Collection([$maintenance]));

        $summary = $this->service->calculateFleetSummary($fleet, new Collection());

        $this->assertSame(1_500_000.0, $summary['basicSales']);
        $this->assertSame(200_000.0, $summary['basicAllowance']);
        $this->assertSame(200_000.0, $summary['orderCost']);
        $this->assertSame(100_000.0, $summary['maintenance']);
        $this->assertSame(0.0, $summary['tax']);
        $this->assertSame(1_200_000.0, $summary['netProfit']);
    }

    public function test_net_profit_subtracts_order_cost_maintenance_and_tax(): void
    {
        $allowanceDetail = new RouteDetail(['amount' => 300_000, 'percentage' => null]);
        $allowanceDetail->setRelation('costComponent', new CostComponent(['type' => 'Allowance']));

        $route = new Route(['price' => 5_000_000]);
        $route->setRelation('routeDetail', new Collection([$allowanceDetail]));

        $order = new Order([
            'vendorPrice' => 5_000_000,
            'qty' => 10,
        ]);
        $order->setRelation('route', $route);
        $order->setRelation('cost', new Collection([
            new OrderCost(['type' => 'On Charge', 'nominal' => 200_000]),
        ]));
        $order->setRelation('orderPayment', new OrderPayment(['pph' => 100_000]));

        $maintDetail = new MaintenanceDetail(['qty' => 2]);
        $maintDetail->setRelation('item', new Item(['price' => 250_000]));
        $maintenance = new Maintenance();
        $maintenance->setRelation('details', new Collection([$maintDetail]));

        $fleet = new Fleet();
        $fleet->setRelation('orders', new Collection([$order]));
        $fleet->setRelation('maintenances', new Collection([$maintenance]));

        $bonuses = new Collection([
            new TonaseBonus(['min' => 5, 'max' => 15, 'value' => 150_000]),
        ]);

        $summary = $this->service->calculateFleetSummary($fleet, $bonuses);

        $this->assertSame(5_000_000.0, $summary['basicSales']);
        $this->assertSame(300_000.0, $summary['basicAllowance']);
        $this->assertSame(200_000.0, $summary['additionalCost']);
        $this->assertSame(150_000.0, $summary['tonase']);
        $this->assertSame(650_000.0, $summary['orderCost']);
        $this->assertSame(500_000.0, $summary['maintenance']);
        $this->assertSame(100_000.0, $summary['tax']);
        $this->assertSame(3_750_000.0, $summary['netProfit']);
    }
}
