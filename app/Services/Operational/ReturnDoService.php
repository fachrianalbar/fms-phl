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
                'fleet.company',
                'orderType',
            ])
            ->get();
    }

    public function datatable($request = null)
    {
        $query = $this->service
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
                'fleet.company',
                'orderType',
                'onChargeCost.costComponent',
                'invoiceDetail.invoice',
            ]);

        if ($request && $request->filled('invoiceStatus')) {
            if ($request->invoiceStatus === 'uninvoiced') {
                $query->whereDoesntHave('invoiceDetail.invoice');
            } elseif ($request->invoiceStatus === 'invoiced') {
                $query->whereHas('invoiceDetail.invoice');
            }
        }

        return $query->orderBy('order.created_at', 'desc');
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
