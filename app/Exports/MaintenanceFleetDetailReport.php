<?php

namespace App\Exports;

use App\Models\Master\Fleet;
use App\Models\Warehouse\Maintenance;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class MaintenanceFleetDetailReport implements FromView, ShouldAutoSize
{
    use Exportable;

    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function view(): View
    {
        $request = $this->request;
        $fleetCode = $request->fleetCode;

        $fleet = Fleet::query()
            ->with(['company'])
            ->where('code', $fleetCode)
            ->whereNull('deleted_at')
            ->firstOrFail();

        $summaryQuery = Maintenance::query()
            ->select([
                DB::raw('COUNT(DISTINCT maintenance.code) as totalMaintenance'),
                DB::raw('COALESCE(SUM(maintenance_detail.qty), 0) as totalQty'),
                DB::raw('COALESCE(SUM(item.price * maintenance_detail.qty), 0) as totalCost'),
            ])
            ->leftJoin('maintenance_detail', function ($join) {
                $join->on('maintenance_detail.maintenanceCode', '=', 'maintenance.code')
                    ->whereNull('maintenance_detail.deleted_at');
            })
            ->leftJoin('item', function ($join) {
                $join->on('item.code', '=', 'maintenance_detail.itemCode')
                    ->whereNull('item.deleted_at');
            })
            ->where('maintenance.fleetCode', $fleetCode)
            ->where('maintenance.status', 0)
            ->whereNull('maintenance.deleted_at');

        $this->applyDateFilter($summaryQuery, $request->startDate, $request->endDate);

        $summary = $summaryQuery->first();

        $rows = Maintenance::query()
            ->with([
                'warehouse',
                'details',
                'details.item',
                'details.item.supplier',
            ])
            ->where('maintenance.fleetCode', $fleetCode)
            ->where('maintenance.status', 0)
            ->whereNull('maintenance.deleted_at')
            ->orderByDesc('maintenance.date')
            ->orderByDesc('maintenance.time');

        $this->applyDateFilter($rows, $request->startDate, $request->endDate);

        return view('report.maintenance-fleet.report.maintenance-fleet-detail-excel')
            ->with('fleet', $fleet)
            ->with('rows', $rows->get())
            ->with('totalMaintenance', (int) ($summary->totalMaintenance ?? 0))
            ->with('totalQty', (float) ($summary->totalQty ?? 0))
            ->with('totalCost', (float) ($summary->totalCost ?? 0))
            ->with('startDate', $request->startDate)
            ->with('endDate', $request->endDate);
    }

    private function applyDateFilter(Builder|QueryBuilder $query, ?string $startDate, ?string $endDate): void
    {
        if ($startDate && $endDate && $startDate === $endDate) {
            $query->whereDate('maintenance.date', '=', $startDate);

            return;
        }

        if ($startDate) {
            $query->whereDate('maintenance.date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('maintenance.date', '<=', $endDate);
        }
    }
}
