<?php

namespace Tests\Unit;

use App\Models\Finance\Invoice;
use App\Models\Finance\InvoiceDetail;
use App\Models\Operational\Order;
use App\Models\Operational\OrderCost;
use App\Services\Finance\InvoiceService;
use Illuminate\Support\Collection;
use Tests\TestCase;

class InvoiceTaxCalculationTest extends TestCase
{
    public function test_pph_can_use_route_amount_only(): void
    {
        $invoice = $this->invoiceWithTaxBasis('route');

        $totals = app(InvoiceService::class)->calculateInvoiceAmount($invoice);

        $this->assertSame(1_000_000, $totals['routeTotal']);
        $this->assertSame(200_000, $totals['onChargeTotal']);
        $this->assertSame(1_200_000, $totals['subtotal']);
        $this->assertSame(132_000, $totals['ppn']);
        $this->assertSame(1_000_000, $totals['pphBaseAmount']);
        $this->assertSame(20_000, $totals['pph']);
        $this->assertSame(1_312_000, $totals['total']);
    }

    public function test_pph_can_use_total_dpp_including_on_charge(): void
    {
        $invoice = $this->invoiceWithTaxBasis('subtotal');

        $totals = app(InvoiceService::class)->calculateInvoiceAmount($invoice);

        $this->assertSame(1_200_000, $totals['pphBaseAmount']);
        $this->assertSame(24_000, $totals['pph']);
        $this->assertSame(1_308_000, $totals['total']);
    }

    private function invoiceWithTaxBasis(string $basis): Invoice
    {
        $onCharge = new OrderCost([
            'type' => 'On Charge',
            'nominal' => 200_000,
        ]);

        $ignoredCost = new OrderCost([
            'type' => 'Operational',
            'nominal' => 50_000,
        ]);

        $order = new Order(['routeAmount' => 1_000_000]);
        $order->setRelation('cost', new Collection([$onCharge, $ignoredCost]));

        $detail = new InvoiceDetail();
        $detail->setRelation('order', $order);

        $invoice = new Invoice([
            'usePpn' => true,
            'usePph' => true,
            'ppnRate' => 11,
            'pphRate' => 2,
            'pphBaseType' => $basis,
        ]);
        $invoice->setRelation('details', new Collection([$detail]));

        return $invoice;
    }
}
