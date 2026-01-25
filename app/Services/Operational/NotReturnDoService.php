<?php

namespace App\Services\Operational;

use App\Models\Operational\Order;
use App\Traits\LogActivity;

class NotReturnDoService
{
    use LogActivity;

    protected $service;

    public function __construct(Order $notReturnDo)
    {
        $this->service = $notReturnDo;
    }

    public function findAll()
    {
        return $this->service->where('status', 3)->with([
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
            'fleet.company',
            'orderType',
            'orderStatus',
        ])->get();
    }

    public function datatable()
    {
        return $this->service->where('status', 3)->with([
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
            'fleet.company',
            'orderType',
            'orderStatus',
        ])->orderBy('orderDate', 'asc');
    }
}
