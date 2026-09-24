<?php

namespace App\Exports;

use App\Helpers\FilterHelper;
use App\Models\Operational\Order;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class NotReturnDoReport implements FromView, ShouldAutoSize, WithColumnFormatting
{
    use Exportable;

    public function __construct(protected $request)
    {
    }

    public function columnFormats(): array
    {
        return [
            'I' => '0.00',
            'J' => '#,##0.00',
            'K' => '#,##0.00',
        ];
    }

    public function view(): View
    {
        $query = Order::query()
            ->where('status', 3)
            ->with([
                'driver',
                'customer',
                'customerDetailOrders.customerDetail',
                'route.originLocation',
                'route.destinationLocation',
                'route',
                'fleet',
                'fleet.type',
                'fleet.company',
                'orderStatus',
            ]);

        $filters = [
            'fleet_plateNumber' => $this->request->plateNumber,
            'customer_name' => $this->request->customerName,
            'driver_name' => $this->request->driverName,
            'fleetType_name' => $this->request->fleetTypeName,
            'shipmentNumber' => $this->request->shipmentNumber,
            'destination' => $this->request->destination,
            'orderTypeCode' => $this->request->orderTypeCode,
        ];

        $relations = [
            'fleet_plateNumber' => 'fleet.plateNumber',
            'customer_name' => 'customer.name',
            'driver_name' => 'driver.name',
            'fleetType_name' => 'fleet.type.name',
            'destination' => 'route.destinationLocation.name',
        ];

        $dateFilters = [
            'orderDate' => [
                'start' => $this->request->startDate,
                'end' => $this->request->endDate,
            ],
        ];

        $orders = FilterHelper::applyFilters($query, $filters, $relations, $dateFilters)
            ->orderBy('orderDate', 'asc')
            ->get();

        return view('operational.not-return-do.report.not-return-do-excel', [
            'orders' => $orders,
            'title' => 'Not Return DO',
        ]);
    }

    public function title(): string
    {
        return 'Not Return DO Report';
    }
}
