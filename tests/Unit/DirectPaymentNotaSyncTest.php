<?php

namespace Tests\Unit;

use App\Models\Data\Route;
use App\Models\Finance\OrderPayment;
use App\Models\Master\Customer;
use App\Models\Master\Fleet;
use App\Models\Operational\Order;
use App\Services\Finance\DirectPaymentService;
use App\Services\Operational\NotReturnDoService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

class DirectPaymentNotaSyncTest extends TestCase
{
    use DatabaseTransactions;

    private function makeOrder(string $suffix, array $orderAttributes = []): Order
    {
        Customer::create([
            'code' => 'CUST-DPS-'.$suffix,
            'name' => 'Customer Direct Payment Sync',
            'isDo' => 0,
        ]);

        $fleet = Fleet::create([
            'code' => 'FLEET-DPS-'.$suffix,
            'plateNumber' => 'B 5678 DPS',
        ]);

        Route::create([
            'code' => 'RT-DPS-'.$suffix,
            'name' => 'Route Direct Payment Sync',
            'customerCode' => 'CUST-DPS-'.$suffix,
            'price' => 1_000_000,
            'vendorPrice' => 0,
            'personalVendorPrice' => 0,
        ]);

        return Order::create(array_merge([
            'code' => 'ORD-DPS-'.$suffix,
            'orderDate' => '2026-08-01',
            'customerCode' => 'CUST-DPS-'.$suffix,
            'fleetCode' => $fleet->code,
            'routeCode' => 'RT-DPS-'.$suffix,
            'qty' => 1,
            'price' => 1_000_000,
            'routeAmount' => 1_000_000,
            'vendorPriceSingle' => 0,
            'vendorPrice' => 0,
            'personalVendorPrice' => 0,
            'personalVendorPriceSingle' => 0,
            'status' => 3,
        ], $orderAttributes));
    }

    private function editQty(Order $order, float $qty): void
    {
        app(NotReturnDoService::class)->updateOrder(new Request([
            'customerCode' => $order->customerCode,
            'fleetCode' => $order->fleetCode,
            'routeData' => $order->routeCode,
            'orderDate' => '2026-08-01',
            'qty' => $qty,
        ]), $order->id, 'Not Return Do');
    }

    public function test_editing_order_updates_unpaid_direct_payment_nota(): void
    {
        $order = $this->makeOrder('UNPAID');

        OrderPayment::create([
            'code' => 'FOP-DPS-UNPAID',
            'orderCode' => $order->code,
            'cost' => 1_000_000,
            'additional_cost' => 0,
            'claim' => 0,
            'ppn' => 110_000,
            'ppn_percent' => 11,
            'pph' => 0,
            'pph_percent' => 0,
            'total' => 0,
            'status' => 0,
            'nota_number' => 'DP-DPS-UNPAID',
        ]);

        // qty 1 → 2 lewat menu Surat Jalan Kembali.
        $this->editQty($order, 2);

        $payment = OrderPayment::where('orderCode', $order->code)->firstOrFail();

        $this->assertSame(2_000_000.0, (float) $payment->cost);
        $this->assertSame(220_000.0, (float) $payment->ppn);
        $this->assertSame(0, (int) $payment->status);
    }

    public function test_paid_direct_payment_nota_follows_order_amount_and_becomes_overpaid(): void
    {
        $order = $this->makeOrder('PAID');

        OrderPayment::create([
            'code' => 'FOP-DPS-PAID',
            'orderCode' => $order->code,
            'cost' => 2_000_000,
            'additional_cost' => 0,
            'claim' => 0,
            'ppn' => 0,
            'ppn_percent' => 0,
            'pph' => 0,
            'pph_percent' => 0,
            'total' => 2_000_000,
            'status' => 1,
            'nota_number' => 'DP-DPS-PAID',
        ]);

        $order->update(['qty' => 2, 'routeAmount' => 2_000_000]);

        // Harga turun menjadi 1.000.000 padahal nota sudah dibayar 2.000.000.
        // Aturan bisnis: tagihan selalu sama dengan nominal order, sehingga nota
        // menjadi kelebihan bayar — riwayat pembayaran tidak diubah.
        $this->editQty($order, 1);

        $payment = OrderPayment::where('orderCode', $order->code)->firstOrFail();

        $this->assertSame(1_000_000.0, (float) $payment->cost);
        $this->assertSame(2_000_000.0, (float) $payment->total);
        $this->assertSame(1, (int) $payment->status);
    }

    public function test_preview_detects_difference_without_changing_data(): void
    {
        $order = $this->makeOrder('PREVIEW');

        OrderPayment::create([
            'code' => 'FOP-DPS-PREVIEW',
            'orderCode' => $order->code,
            'cost' => 1_000_000,
            'additional_cost' => 0,
            'claim' => 0,
            'ppn' => 110_000,
            'ppn_percent' => 11,
            'pph' => 0,
            'pph_percent' => 0,
            'total' => 0,
            'status' => 0,
            'nota_number' => 'DP-DPS-PREVIEW',
        ]);

        // Simulasi harga order berubah tanpa sinkronisasi (mis. data lama).
        $order->update(['qty' => 2, 'routeAmount' => 2_000_000]);

        $preview = app(DirectPaymentService::class)->previewNotaSync($order->code);

        $this->assertNotNull($preview);
        $this->assertTrue($preview['has_change']);
        $this->assertSame(1_110_000, $preview['total_current']);
        $this->assertSame(2_220_000, $preview['total_new']);
        $this->assertSame(1_110_000, $preview['items'][0]['difference']);

        // Pratinjau tidak boleh mengubah data tersimpan.
        $this->assertSame(1_000_000.0, (float) OrderPayment::where('orderCode', $order->code)->value('cost'));
    }

    public function test_manual_sync_sets_nota_amount_below_recorded_payment(): void
    {
        $order = $this->makeOrder('MANUAL');

        OrderPayment::create([
            'code' => 'FOP-DPS-MANUAL',
            'orderCode' => $order->code,
            'cost' => 2_000_000,
            'additional_cost' => 0,
            'claim' => 0,
            'ppn' => 0,
            'ppn_percent' => 0,
            'pph' => 0,
            'pph_percent' => 0,
            'total' => 2_000_000,
            'status' => 1,
            'nota_number' => 'DP-DPS-MANUAL',
        ]);

        $order->update(['qty' => 1, 'routeAmount' => 1_000_000]);

        $result = app(DirectPaymentService::class)->syncNotaByOrderCode($order->code);
        $payment = OrderPayment::where('orderCode', $order->code)->firstOrFail();

        $this->assertTrue($result['changed']);
        $this->assertSame('DP-DPS-MANUAL', $result['nota_number']);
        $this->assertSame(1_000_000.0, (float) $payment->cost);
        $this->assertSame(1, (int) $payment->status);
    }
}