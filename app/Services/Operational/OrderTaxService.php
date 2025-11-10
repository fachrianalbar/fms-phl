<?php

namespace App\Services\Operational;

use App\Helpers\GenerateCode;
use App\Models\Operational\Order;
use App\Models\Operational\OrderTax;
use App\Traits\LogActivity;

class OrderTaxService
{
    use LogActivity;

    protected $service;

    protected $order;

    public function __construct(OrderTax $orderTax, Order $order)
    {
        $this->service = $orderTax;
        $this->order = $order;
    }

    public function findAll()
    {
        return $this->order->with(['fleet', 'customer', 'driver', 'route', 'route.originLocation', 'route.destinationLocation', 'orderStatus'])->where('is_order_tax', 1)->get();
    }

    public function store($request, $title)
    {
        $this->order->where('code', $request->orderCode)->update([
            'status' => 7,
        ]);

        $data = $this->service->create([
            'description' => $request->description,
            'orderCode' => $request->orderCode,
            'code' => GenerateCode::generateCode('FOTAX'),
        ]);

        $this->logActivity($title, $data, 'Create');
    }
}
