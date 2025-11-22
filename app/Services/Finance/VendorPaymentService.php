<?php

namespace App\Services\Finance;

use App\Helpers\GenerateCode;
use App\Models\Finance\VendorPayment;
use App\Models\Operational\Order;
use App\Traits\LogActivity;

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

    public function store($request, $title)
    {
        $this->order->where('code', $request->orderCode)->update([
            'status' => 6,
        ]);

        $data = $this->service->create([
            'date' => $request->date,
            'amount' => (int) $request->amount,
            'description' => $request->description,
            'orderCode' => $request->orderCode,
            'code' => GenerateCode::generateCode('FVP'),
        ]);

        $this->logActivity($title, $data, 'Create');
    }
}
