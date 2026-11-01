<?php

namespace Tests\Unit;

use App\Models\Data\Route;
use App\Models\Finance\VendorPayment;
use App\Models\Master\Customer;
use App\Models\Master\Fleet;
use App\Models\Master\FleetCompany;
use App\Models\Operational\Order;
use App\Services\Operational\NotReturnDoService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

class NotReturnDoVendorNotaSyncTest extends TestCase
{
    use DatabaseTransactions;

    public function test_editing_order_from_not_return_do_updates_vendor_nota_amount(): void
    {
        Customer::create([
            'code' => 'CUST-VNOTA-TEST',
            'name' => 'Customer VNota Test',
        ]);

        $company = FleetCompany::create([
            'code' => 'FC-VNOTA-TEST',
            'name' => 'Vendor Nota Test',
            'type' => 'external',
        ]);

        $fleet = Fleet::create([
            'code' => 'FLEET-VNOTA-TEST',
            'plateNumber' => 'B 1234 VNT',
            'fleetCompanyCode' => $company->code,
        ]);

        Route::create([
            'code' => 'RT-VNOTA-TEST',
            'name' => 'Route VNota Test',
            'customerCode' => 'CUST-VNOTA-TEST',
            'price' => 1_000_000,
            'vendorPrice' => 0,
            'personalVendorPrice' => 0,
        ]);

        $order = Order::create([
            'code' => 'ORD-VNOTA-TEST',
            'orderDate' => '2026-08-01',
            'customerCode' => 'CUST-VNOTA-TEST',
            'fleetCode' => $fleet->code,
            'routeCode' => 'RT-VNOTA-TEST',
            'qty' => 1,
            'price' => 1_000_000,
            'routeAmount' => 1_000_000,
            'vendorPriceSingle' => 500_000,
            'vendorPrice' => 500_000,
            'personalVendorPrice' => 0,
            'personalVendorPriceSingle' => 0,
            'status' => 3,
        ]);

        VendorPayment::create([
            'code' => 'VP-VNOTA-TEST',
            'orderCode' => $order->code,
            'nota_number' => 'NOTA-VNOTA-TEST',
            'amount' => 500_000,
            'paid_amount' => 0,
            'remaining_amount' => 500_000,
            'payment_status' => 'pending',
            'ppn_rate' => 0,
            'pph_rate' => 0,
            'ppn_amount' => 0,
            'pph_amount' => 0,
            'claim_amount' => 0,
        ]);

        // Edit qty dari 1 ke 2 lewat menu Surat Jalan Kembali.
        app(NotReturnDoService::class)->updateOrder(new Request([
            'customerCode' => 'CUST-VNOTA-TEST',
            'fleetCode' => $fleet->code,
            'routeData' => 'RT-VNOTA-TEST',
            'orderDate' => '2026-08-01',
            'qty' => 2,
        ]), $order->id, 'Not Return Do');

        $this->assertSame(1_000_000.0, (float) $order->fresh()->vendorPrice);
        $this->assertSame(1_000_000, (int) VendorPayment::where('orderCode', $order->code)->value('amount'));
    }
}