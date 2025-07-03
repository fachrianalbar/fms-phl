<?php

namespace App\Services\Operational;

use App\Models\Operational\Order;
use App\Traits\LogActivity;

class ReturnDoService
{
    use LogActivity;

    protected $service;

    public function __construct(Order $notReturnDo)
    {
        $this->service = $notReturnDo;
    }

    public function findAll()
    {
        return $this->service
            ->whereHas('customer', function ($query) {
                $query->where(function ($q) {
                    $q->where('isDo', 1)
                        ->whereIn('status', [4, 5]);
                })->orWhere(function ($q) {
                    $q->where('isDo', 0)
                        ->whereIn('status', [3, 4, 5]);
                });
            })
            ->with([
                'fleetDriver.fleet',
                'driver',
                'customer',
                'route.originLocation',
                'route.destinationLocation',
                'material',
                'route.routeDetail',
                'fleet',
                'fleet.type'
            ])
            ->get();
    }

    public function datatable()
    {
        return $this->service
            ->whereHas('customer', function ($query) {
                $query->where(function ($q) {
                    $q->where('isDo', 1)
                        ->whereIn('status', [4, 5]);
                })->orWhere(function ($q) {
                    $q->where('isDo', 0)
                        ->whereIn('status', [3, 4, 5]);
                });
            })
            ->with([
                'fleetDriver.fleet',
                'driver',
                'customer',
                'route.originLocation',
                'route.destinationLocation',
                'material',
                'route.routeDetail',
                'fleet',
                'fleet.type'
            ])->orderBy('order.created_at', 'desc');
    }
}
