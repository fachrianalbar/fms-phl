<?php

namespace App\Exports;

use App\Helpers\FilterHelper;
use App\Models\Master\Fleet;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProfitLossReport implements FromView, ShouldAutoSize
{
    use Exportable;

    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function title(): string
    {
        return 'Profit & Loss Report';
    }

    public function view(): View
    {

        $request = $this->request;

        $filters = [
            'plateNumber' => $this->request->plateNumber,
        ];

        // Define relations for filters
        $relations = [];

        $dateFilters = [];

        $query = Fleet::with(['type', 'orders' => function ($query) use ($request) {
            if ($request->startDate && $request->endDate) {
                $query->whereDate('orderDate', '>=', $request->startDate)
                    ->whereDate('orderDate', '<=', $request->endDate);
            }
        }, 'maintenances' => function ($query) use ($request) {
            if ($request->startDate && $request->endDate) {
                $query->whereDate('date', '>=', $request->startDate)
                    ->whereDate('date', '<=', $request->endDate);
            }
        }, 'orders.route.routeDetail', 'maintenances.details', 'orders.cost']);

        $query = FilterHelper::applyFilters($query, $filters, $relations, $dateFilters);

        $data = $query->get();

        return view('report.profit-loss.report.profit-loss-excel')
            ->with('data', $data);
    }
}
