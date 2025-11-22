<?php

namespace App\Exports;

use App\Helpers\FilterHelper;
use App\Models\Operational\Order;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class OrderDetailReport implements FromView, ShouldAutoSize, WithColumnFormatting
{
    use Exportable;

    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function columnFormats(): array
    {
        return [
            'L' => '#,##0_);(#,##0)',
            'M' => '#,##0_);(#,##0)',
            'N' => '#,##0_);(#,##0)',
        ];
    }

    public function title(): string
    {
        return 'Order Detail Report';
    }

    public function view(): View
    {
        $request = $this->request;

        $data = Order::with([
            'fleet',
            'fleet.type',
            'driver',
            'customer',
            'route.destinationLocation',
            'route.originLocation',
            'material',
            'cost.costComponent',
        ])->orderBy('orderDate', 'desc');

        // Define filters
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

        // Map filters to relations
        $relations = [
            'fleet_plateNumber' => 'fleet.plateNumber',
            'customer_name' => 'customer.name',
            'driver_name' => 'driver.name',
            'fleetType_name' => 'fleet.type.name',
            'origin' => 'route.originLocation.name',
            'destination' => 'route.destinationLocation.name',
        ];

        $dateFilters = [
            'orderDate' => [
                'start' => $request->startDate,
                'end' => $request->endDate,
            ],
        ];

        $order = FilterHelper::applyFilters($data, $filters, $relations, $dateFilters);

        return view('report.order-detail.report.order-detail-excel')
            ->with('order', $order->get());
    }
}
