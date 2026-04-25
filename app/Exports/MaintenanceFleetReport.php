<?php

namespace App\Exports;

use App\Models\Master\Fleet;
use App\Models\Master\FleetCompany;
use App\Models\Warehouse\Maintenance;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class MaintenanceFleetReport implements FromView, ShouldAutoSize
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

        $query = Maintenance::query()
            ->select([
                'fleet.code as fleetCode',
                'fleet.plateNumber',
                'fleet_company.name as fleetCompanyName',
                'fleet_company.type as fleetCompanyType',
                DB::raw('COUNT(DISTINCT maintenance.code) as totalMaintenance'),
                DB::raw('COALESCE(SUM(maintenance_detail.qty), 0) as totalQty'),
                DB::raw('COALESCE(SUM(item.price * maintenance_detail.qty), 0) as totalCost'),
            ])
            ->join('fleet', function ($join) {
                $join->on('fleet.code', '=', 'maintenance.fleetCode')
                    ->whereNull('fleet.deleted_at');
            })
            ->leftJoin('fleet_company', function ($join) {
                $join->on('fleet_company.code', '=', 'fleet.fleetCompanyCode')
                    ->whereNull('fleet_company.deleted_at');
            })
            ->leftJoin('maintenance_detail', function ($join) {
                $join->on('maintenance_detail.maintenanceCode', '=', 'maintenance.code')
                    ->whereNull('maintenance_detail.deleted_at');
            })
            ->leftJoin('item', function ($join) {
                $join->on('item.code', '=', 'maintenance_detail.itemCode')
                    ->whereNull('item.deleted_at');
            })
            ->where('maintenance.status', 0)
            ->whereNull('maintenance.deleted_at')
            ->groupBy('fleet.code', 'fleet.plateNumber', 'fleet_company.name', 'fleet_company.type')
            ->orderBy('fleet.plateNumber');

        if ($request->filled('fleetCode')) {
            $query->where('fleet.code', $request->fleetCode);
        }

        if ($request->filled('fleetCompanyCode')) {
            $query->where('fleet_company.code', $request->fleetCompanyCode);
        }

        $this->applyDateFilter($query, $request->startDate, $request->endDate);

        $rows = $query->get();

        $fleetName = null;
        if ($request->filled('fleetCode')) {
            $fleetName = Fleet::query()->where('code', $request->fleetCode)->value('plateNumber');
        }

        $fleetCompanyName = null;
        if ($request->filled('fleetCompanyCode')) {
            $fleetCompanyName = FleetCompany::query()->where('code', $request->fleetCompanyCode)->value('name');
        }

        return view('report.maintenance-fleet.report.maintenance-fleet-excel')
            ->with('rows', $rows)
            ->with('fleetName', $fleetName)
            ->with('fleetCompanyName', $fleetCompanyName)
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
