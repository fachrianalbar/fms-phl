<?php

namespace App\Services\Operational;

use App\Models\Operational\Order;
use App\Traits\LogActivity;

class OrderMonitoringService
{
    use LogActivity;

    protected $service;

    public function __construct(Order $orderMonitoring)
    {
        $this->service = $orderMonitoring;
    }

    public function findAll()
    {
        return $this->service->whereIn('status', [0, 1, 2])->with([
            'fleetDriver.fleet',
            'driver',
            // 'fleetDriver.employee',
            'customer',
            'route.originLocation',
            'route.destinationLocation',
            'material',
            'route.routeDetail',
            'fleet',
            'fleet.type',
            'orderStatus',
        ])->get();
    }

    public function datatable()
    {
        return $this->service->whereIn('status', [0, 1, 2])->with([
            'fleetDriver.fleet',
            'driver',
            // 'fleetDriver.employee',
            'customer',
            'route.originLocation',
            'route.destinationLocation',
            'material',
            'route.routeDetail',
            'fleet',
            'fleet.type',
            'orderStatus',
        ])->orderBy('order.created_at', 'desc');
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->with([
            'route',
            'route.originLocation',
            'route.destinationLocation',
            'fleet',
            'orderStatus',
        ])->first();
    }

    public function getByShipmentNumber($shipmentNumber)
    {
        return $this->service->where('shipmentNumber', $shipmentNumber)->whereIn('status', [0, 1, 2])->with([
            'route',
            'route.originLocation',
            'route.destinationLocation',
            'fleet',
            'orderStatus',
        ])->first();
    }

    public function finishOrder($id)
    {
        $this->service->where('id', $id)->update([
            'status' => 3,
        ]);
    }
}
