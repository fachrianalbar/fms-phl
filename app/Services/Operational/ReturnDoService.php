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
            ->whereIn('status', [4, 5])
            ->with([
                'fleetDriver.fleet',
                'driver',
                'customer',
                'route.originLocation',
                'route.destinationLocation',
                'material',
                'route.routeDetail',
                'fleet',
                'fleet.type',
            ])
            ->get();
    }

    public function datatable()
    {
        return $this->service
            ->whereIn('status', [4, 5])
            ->with([
                'fleetDriver.fleet',
                'driver',
                'customer',
                'route.originLocation',
                'route.destinationLocation',
                'material',
                'route.routeDetail',
                'fleet',
                'fleet.type',
                'onChargeCost.costComponent',
            ])->orderBy('order.created_at', 'desc');
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->first();
    }

    /**
     * Rollback order status from Return DO (4) back to Not Return DO (3).
     * Clears returnDate and returnDescription.
     */
    public function rollbackStatus($id)
    {
        $this->service->where('id', $id)->update([
            'status' => 3,
            'returnDate' => null,
            'returnDescription' => null,
        ]);
    }
}
