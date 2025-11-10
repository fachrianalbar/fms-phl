<?php

namespace App\Exports;

use App\Helpers\FilterHelper;
use App\Models\Operational\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class DriverTonaseReport implements FromView, ShouldAutoSize
{
    use Exportable;

    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function title(): string
    {
        return 'Driver Tonase Report';
    }

    public function view(): View
    {

        $request = $this->request;

        $data = Order::select('driverCode', 'customerCode', DB::raw('SUM('.env('DB_PREFIX').'order.qty) as total_tonase'))
            ->join('employee', 'employee.code', '=', 'order.driverCode')
            ->join('customer', 'customer.code', '=', 'order.customerCode')
            ->with(['driver', 'customer'])
            ->whereNull('order.deleted_at')
            ->groupBy('driverCode', 'employee.code')
            ->groupBy('customerCode', 'customer.code');

        // Definisikan kolom filter dengan alias
        $filters = [
            'customerCode' => $request->customerCode,
            'driverCode' => $request->driverCode,
        ];

        // Hubungkan alias ke relasi dan kolom yang sesuai
        $relations = [];

        $dateFilters = [
            'orderDate' => [
                'start' => $request->startDate,
                'end' => $request->endDate,
            ],
        ];

        $data = FilterHelper::applyFilters($data, $filters, $relations, $dateFilters);

        return view('report.driver-tonase.report.driver-tonase-excel')
            ->with('data', $data->get());
    }
}
