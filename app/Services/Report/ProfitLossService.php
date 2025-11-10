<?php

namespace App\Services\Report;

use App\Models\Operational\Order;
use App\Models\Warehouse\Maintenance;
use App\Traits\LogActivity;

class ProfitLossService
{
    use LogActivity;

    protected $order;

    protected $maintenance;

    public function __construct(Order $order, Maintenance $maintenance)
    {
        $this->order = $order;
        $this->maintenance = $maintenance;
    }

    public function datatableOrder($fleetCode)
    {
        return $this->order->where('fleetCode', $fleetCode)->with([
            'fleetDriver.fleet',
            'driver',
            // 'fleetDriver.employee',
            'customer',
            'route.destinationLocation',
            'material',
            'route.routeDetail',
            'fleet',
            'fleet.type',
        ])->latest();
    }

    public function datatableMaintenance($fleetCode)
    {
        return $this->maintenance->with([
            'fleet',
            'details',
            'details.item',
        ])->where('status', 0)->where('fleetCode', $fleetCode)->latest();
    }
}
