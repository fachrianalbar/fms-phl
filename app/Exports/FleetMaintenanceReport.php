<?php

namespace App\Exports;

use App\Helpers\FilterHelper;
use App\Models\Master\Fleet;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class FleetMaintenanceReport implements FromView, ShouldAutoSize, WithColumnFormatting
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
            'D' => '#,##0_);(#,##0)',
        ];
    }

    public function title(): string
    {
        return 'Fleet Maintenance Report';
    }

    public function view(): View
    {
        $data = Fleet::select([
            'fleet.code',
            'fleet.plateNumber',
            DB::raw('COUNT(DISTINCT maintenance.code) as total'),
            DB::raw('SUM(item.price * maintenance_detail.qty) as price'),
        ])
            ->leftjoin('maintenance', 'maintenance.fleetCode', 'fleet.code')
            ->leftjoin('maintenance_detail', 'maintenance_detail.maintenanceCode', 'maintenance.code')
            ->leftjoin('item', 'item.code', 'maintenance_detail.itemCode')
            ->with(['maintenances'])
            ->orderBy('total', 'DESC')
            ->groupBy(['fleet.code', 'fleet.plateNumber']);

        $filters = [
            'fleet.code' => $this->request->plateNumber,
        ];

        $relations = [];

        $dateFilters = [
            'maintenance.date' => [
                'start' => $this->request->startDate,
                'end' => $this->request->endDate,
            ],
        ];

        $data = FilterHelper::applyFilters($data, $filters, $relations, $dateFilters);

        return view('dashboard.report.fleet-maintenance-excel')
            ->with('data', $data->get());
    }
}
