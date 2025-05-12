<?php

namespace App\Exports;

use App\Helpers\FilterHelper;
use App\Models\Operational\Order;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;


class FleetTonaseReport implements FromView, ShouldAutoSize
{
    use Exportable;

    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function title(): string
    {
        return 'Fleet Tonase Report';
    }

    public function view(): View
    {

        $request = $this->request;

        $data = Order::select('fleetCode', 'customerCode',  DB::raw('SUM(' . env('DB_PREFIX') . 'order.qty) as total_tonase'))
            ->join('fleet', 'fleet.code', '=', 'order.fleetCode')
            ->join('customer', 'customer.code', '=', 'order.customerCode')
            ->with(['fleet.type', 'customer'])
            ->whereNull('order.deleted_at')
            ->groupBy('fleetCode', 'fleet.code')
            ->groupBy('customerCode', 'customer.code');

        // Definisikan kolom filter dengan alias
        $filters = [
            'fleetCode' => $request->fleetCode,
            'customerCode' => $request->customerCode,
            'fleetType_name' => $request->fleetTypeName,
        ];

        // Hubungkan alias ke relasi dan kolom yang sesuai
        $relations = [
            'fleetType_name' => 'fleet.type.name',
        ];

        $dateFilters = [
            'orderDate' => [
                'start' => $request->startDate,
                'end' => $request->endDate,
            ],
        ];

        $data = FilterHelper::applyFilters($data, $filters, $relations, $dateFilters);


        return view('report.fleet-tonase.report.fleet-tonase-excel')
            ->with('data', $data->get());
    }
}
