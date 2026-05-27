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

    protected $orders;

    public function __construct($ordersOrRequest)
    {
        if ($ordersOrRequest instanceof \Illuminate\Http\Request) {
            $query = Order::with([
                'fleet',
                'fleet.type',
                'driver',
                'customer',
                'route.destinationLocation',
                'route.originLocation',
                'material',
                'cost.costComponent',
            ])->orderBy('orderDate', 'desc');

            $filters = [
                'fleet_plateNumber' => $ordersOrRequest->plateNumber,
                'customer_name' => $ordersOrRequest->customerName,
                'driver_name' => $ordersOrRequest->driverName,
                'fleetType_name' => $ordersOrRequest->fleetTypeName,
                'shipmentNumber' => $ordersOrRequest->shipmentNumber,
                'origin' => $ordersOrRequest->origin,
                'destination' => $ordersOrRequest->destination,
                'orderTypeCode' => $ordersOrRequest->orderTypeCode,
            ];

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
                    'start' => $ordersOrRequest->startDate,
                    'end' => $ordersOrRequest->endDate,
                ],
            ];

            $this->orders = FilterHelper::applyFilters($query, $filters, $relations, $dateFilters)->get();
        } else {
            $this->orders = $ordersOrRequest;
        }
    }

    public function columnFormats(): array
    {
        return [
            'I' => '#,##0',
            'K' => '#,##0',
            'L' => '#,##0',
            'M' => '#,##0',
        ];
    }

    public function title(): string
    {
        return 'Order Detail Report';
    }

    public function view(): View
    {
        return view('report.order-detail.report.order-detail-excel')
            ->with('order', $this->orders);
    }
}
