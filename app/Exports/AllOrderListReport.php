<?php

namespace App\Exports;

use App\Helpers\FilterHelper;
use App\Models\Operational\Order;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;


class AllOrderListReport implements FromView, ShouldAutoSize
{
    use Exportable;

    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function title(): string
    {
        return 'All Order List Report';
    }

    public function view(): View
    {

        $request = $this->request;

        $data = Order::with([
            'fleetDriver.fleet',
            'driver',
            // 'fleetDriver.employee',
            'customer',
            'route.destinationLocation',
            'material',
            'route.routeDetail',
            'fleet',
            'fleet.type'
        ])->orderBy('created_at', 'desc');

        // if ($request->startDate && $request->endDate) {
        //     $data = Order::with([
        //         'fleetDriver.fleet',
        //         'driver',
        //         // 'fleetDriver.employee',
        //         'customer',
        //         'route.destinationLocation',
        //         'material',
        //         'route.routeDetail',
        //         'fleet',
        //         'fleet.type'
        //     ])->orderBy('created_at', 'desc');
        // }

        // Definisikan kolom filter dengan alias
        $filters = [
            'fleet_plateNumber' => $request->plateNumber,
            'customer_name' => $request->customerName,
            'driver_name' => $request->driverName,
            'fleetType_name' => $request->fleetTypeName,
            'shipmentNumber' => $request->shipmentNumber,
            'origin' => $request->origin,
            'destination' => $request->destination,
            'orderTypeCode' => $request->orderTypeCode,

        ];

        // Hubungkan alias ke relasi dan kolom yang sesuai
        $relations = [
            'fleet_plateNumber' => 'fleet.plateNumber',
            'customer_name' => 'customer.name',
            'driver_name' => 'driver.name',
            'fleetType_name' => 'fleet.type.name',
            'origin' => 'route.originLocation.name',
            'destination' => 'route.destinationLocation.name'
        ];

        $dateFilters = [
            'orderDate' => [
                'start' => $request->startDate,
                'end' => $request->endDate,
            ],
        ];


        $order = FilterHelper::applyFilters($data, $filters, $relations, $dateFilters);


        return view('report.all-order-list.report.all-order-list-excel')
            ->with('order', $order->get());
    }
}
