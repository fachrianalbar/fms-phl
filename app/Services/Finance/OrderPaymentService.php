<?php

namespace App\Services\Finance;

use App\Helpers\GenerateCode;
use App\Models\Finance\Invoice;
use App\Models\Finance\InvoiceDetail;
use App\Models\Finance\VendorPayment;
use App\Models\Operational\Order;
use App\Traits\LogActivity;
use Carbon\Carbon;

class VendorPaymentService
{
    use LogActivity;

    protected $service;
    protected $order;

    public function __construct(VendorPayment $vendorPayment, Order $order)
    {
        $this->service = $vendorPayment;
        $this->order = $order;
    }

    public function findAll()
    {
        return $this->order->whereHas('fleet.company', function ($q) {
            $q->where('type', 'External');
        })->with(['fleet', 'customer', 'driver', 'route', 'route.originLocation', 'route.destinationLocation'])->whereIn('status', [3, 6])->get();
    }
}
