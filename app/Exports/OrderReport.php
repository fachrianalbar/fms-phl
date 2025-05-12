<?php

namespace App\Exports;

use App\Helpers\FilterHelper;
use App\Models\Operational\Order;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class OrderReport implements FromView, ShouldAutoSize
{

    use Exportable;

    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function view(): View
    {

        $type = $this->request->type;


        $filters = [
            'fleet_plateNumber' => $this->request->plateNumber,
            'customer_name' => $this->request->customerName,
            'driver_name' => $this->request->driverName,
            'fleetType_name' => $this->request->fleetTypeName,
            'shipmentNumber' => $this->request->shipmentNumber,
            // 'origin' => $this->request->origin,
            'destination' => $this->request->destination,
            'orderTypeCode' => $this->request->orderTypeCode,
        ];

        // Define relations for filters
        $relations = [
            'fleet_plateNumber' => 'fleet.plateNumber',
            'customer_name' => 'customer.name',
            'driver_name' => 'driver.name',
            'fleetType_name' => 'fleet.type.name',
            // 'origin' => 'route.originLocation.name',
            'destination' => 'route.destinationLocation.name'
        ];

        $dateFilters = [
            'orderDate' => [
                'start' => $this->request->startDate,
                'end' => $this->request->endDate,
            ],
        ];

        $query = Order::with([
            'fleet',
            'fleet.type',
            'driver',
            // 'fleetDriver.employee',
            'customer',
            'route.destinationLocation',
            'material',
            'route.routeDetail'
        ]);

        $query = FilterHelper::applyFilters($query, $filters, $relations, $dateFilters);

        $order = $query->orderBy('orderDate', 'desc')->get();


        return view('operational.order.report.order-excel')
            ->with('order', $order)
            ->with('type', $type);
    }

    public function title(): string
    {
        return 'Order Report';
    }
}
