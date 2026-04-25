<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Master\Fleet;
use App\Models\Master\FleetCompany;
use App\Models\Warehouse\Maintenance;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class MaintenancePerCompanyController extends Controller
{
    protected $title;

    protected $view;

    public function __construct()
    {
        $this->title = 'Maintenance Per Fleet Company (Internal)';
        $this->view = 'report.maintenance-company-internal.';
    }

    public function index()
    {
        $fleetCompany = FleetCompany::query()
            ->whereRaw("LOWER(TRIM(COALESCE(type, ''))) = ?", ['internal'])
            ->orderBy('name')
            ->get();

        return view($this->view . 'index')
            ->with('view', $this->view)
            ->with('fleetCompany', $fleetCompany)
            ->with('title', $this->title);
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = Maintenance::query()
                ->select([
                    'fleet_company.code as fleetCompanyCode',
                    'fleet_company.name as fleetCompanyName',
                    'fleet_company.type as fleetCompanyType',
                    DB::raw('COUNT(DISTINCT fleet.code) as totalFleet'),
                    DB::raw('COUNT(DISTINCT maintenance.code) as totalMaintenance'),
                    DB::raw('COALESCE(SUM(maintenance_detail.qty), 0) as totalQty'),
                    DB::raw('COALESCE(SUM(item.price * maintenance_detail.qty), 0) as totalCost'),
                ])
                ->join('fleet', function ($join) {
                    $join->on('fleet.code', '=', 'maintenance.fleetCode')
                        ->whereNull('fleet.deleted_at');
                })
                ->join('fleet_company', function ($join) {
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
                ->whereRaw("LOWER(TRIM(COALESCE(fleet_company.type, ''))) = ?", ['internal'])
                ->groupBy('fleet_company.code', 'fleet_company.name', 'fleet_company.type')
                ->orderBy('fleet_company.name');

            if ($request->filled('fleetCompanyCode')) {
                $data->where('fleet_company.code', $request->fleetCompanyCode);
            }

            $this->applyDateFilter($data, $request->startDate, $request->endDate);

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) use ($request) {
                    $detailUrl = route('report.maintenance-company-internal.detail', ['fleetCompanyCode' => $row->fleetCompanyCode]);

                    $query = array_filter([
                        'startDate' => $request->startDate,
                        'endDate' => $request->endDate,
                    ]);

                    if (! empty($query)) {
                        $detailUrl .= '?' . http_build_query($query);
                    }

                    return '<a href="' . $detailUrl . '" class="btn btn-icon btn-sm bg-info-subtle" data-bs-toggle="tooltip" title="Detail">'
                        . '<i class="mdi mdi-eye-outline fs-14 text-info"></i>'
                        . '</a>';
                })
                ->editColumn('fleetCompanyType', function ($row) {
                    return $row->fleetCompanyType ?: 'Internal';
                })
                ->editColumn('totalFleet', function ($row) {
                    return number_format((float) $row->totalFleet, 0, ',', '.');
                })
                ->editColumn('totalMaintenance', function ($row) {
                    return number_format((float) $row->totalMaintenance, 0, ',', '.');
                })
                ->editColumn('totalQty', function ($row) {
                    return number_format((float) $row->totalQty, 1, ',', '.');
                })
                ->editColumn('totalCost', function ($row) {
                    return number_format((float) $row->totalCost, 0, ',', '.');
                })
                ->rawColumns(['action', 'fleetCompanyType', 'totalFleet', 'totalMaintenance', 'totalQty', 'totalCost'])
                ->toJson();
        }
    }

    public function detail(string $fleetCompanyCode, Request $request)
    {
        $fleetCompany = FleetCompany::query()
            ->where('code', $fleetCompanyCode)
            ->whereRaw("LOWER(TRIM(COALESCE(type, ''))) = ?", ['internal'])
            ->whereNull('deleted_at')
            ->firstOrFail();

        $fleet = Fleet::query()
            ->where('fleetCompanyCode', $fleetCompanyCode)
            ->whereNull('deleted_at')
            ->orderBy('plateNumber')
            ->get();

        $summaryQuery = Maintenance::query()
            ->select([
                DB::raw('COUNT(DISTINCT fleet.code) as totalFleet'),
                DB::raw('COUNT(DISTINCT maintenance.code) as totalMaintenance'),
                DB::raw('COALESCE(SUM(maintenance_detail.qty), 0) as totalQty'),
                DB::raw('COALESCE(SUM(item.price * maintenance_detail.qty), 0) as totalCost'),
            ])
            ->join('fleet', function ($join) {
                $join->on('fleet.code', '=', 'maintenance.fleetCode')
                    ->whereNull('fleet.deleted_at');
            })
            ->leftJoin('maintenance_detail', function ($join) {
                $join->on('maintenance_detail.maintenanceCode', '=', 'maintenance.code')
                    ->whereNull('maintenance_detail.deleted_at');
            })
            ->leftJoin('item', function ($join) {
                $join->on('item.code', '=', 'maintenance_detail.itemCode')
                    ->whereNull('item.deleted_at');
            })
            ->where('fleet.fleetCompanyCode', $fleetCompanyCode)
            ->where('maintenance.status', 0)
            ->whereNull('maintenance.deleted_at');

        if ($request->filled('fleetCode')) {
            $summaryQuery->where('fleet.code', $request->fleetCode);
        }

        $this->applyDateFilter($summaryQuery, $request->startDate, $request->endDate);

        $summary = $summaryQuery->first();

        return view($this->view . 'show')
            ->with('view', $this->view)
            ->with('title', 'Maintenance Detail Per Fleet Company (Internal)')
            ->with('fleetCompany', $fleetCompany)
            ->with('fleet', $fleet)
            ->with('totalFleet', (int) ($summary->totalFleet ?? 0))
            ->with('totalMaintenance', (int) ($summary->totalMaintenance ?? 0))
            ->with('totalQty', (float) ($summary->totalQty ?? 0))
            ->with('totalCost', (float) ($summary->totalCost ?? 0))
            ->with('fleetCode', $request->fleetCode)
            ->with('startDate', $request->startDate)
            ->with('endDate', $request->endDate);
    }

    public function datatableDetail(string $fleetCompanyCode, Request $request)
    {
        if ($request->ajax()) {
            $data = Maintenance::query()
                ->select([
                    'maintenance.code',
                    'maintenance.date',
                    'maintenance.time',
                    'fleet.code as fleetCode',
                    'fleet.plateNumber',
                    'warehouse.name as warehouseName',
                    DB::raw('COUNT(DISTINCT maintenance_detail.code) as totalItem'),
                    DB::raw('COALESCE(SUM(maintenance_detail.qty), 0) as totalQty'),
                    DB::raw('COALESCE(SUM(item.price * maintenance_detail.qty), 0) as totalCost'),
                ])
                ->join('fleet', function ($join) {
                    $join->on('fleet.code', '=', 'maintenance.fleetCode')
                        ->whereNull('fleet.deleted_at');
                })
                ->leftJoin('warehouse', function ($join) {
                    $join->on('warehouse.code', '=', 'maintenance.warehouseCode')
                        ->whereNull('warehouse.deleted_at');
                })
                ->leftJoin('maintenance_detail', function ($join) {
                    $join->on('maintenance_detail.maintenanceCode', '=', 'maintenance.code')
                        ->whereNull('maintenance_detail.deleted_at');
                })
                ->leftJoin('item', function ($join) {
                    $join->on('item.code', '=', 'maintenance_detail.itemCode')
                        ->whereNull('item.deleted_at');
                })
                ->where('fleet.fleetCompanyCode', $fleetCompanyCode)
                ->where('maintenance.status', 0)
                ->whereNull('maintenance.deleted_at')
                ->groupBy('maintenance.code', 'maintenance.date', 'maintenance.time', 'fleet.code', 'fleet.plateNumber', 'warehouse.name')
                ->orderByDesc('maintenance.date')
                ->orderByDesc('maintenance.time');

            if ($request->filled('fleetCode')) {
                $data->where('fleet.code', $request->fleetCode);
            }

            $this->applyDateFilter($data, $request->startDate, $request->endDate);

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    return '<button type="button" class="btn btn-icon btn-sm bg-info-subtle" '
                        . 'onclick="showMaintenanceItems(\'' . $row->code . '\')" '
                        . 'data-bs-toggle="tooltip" title="Detail">'
                        . '<i class="mdi mdi-eye-outline fs-14 text-info"></i>'
                        . '</button>';
                })
                ->addColumn('maintenanceDate', function ($row) {
                    return Carbon::parse($row->date)->format('d-m-Y') . ' ' . Carbon::parse($row->time)->format('H:i');
                })
                ->editColumn('plateNumber', function ($row) {
                    return $row->plateNumber ?: '-';
                })
                ->editColumn('warehouseName', function ($row) {
                    return $row->warehouseName ?: '-';
                })
                ->editColumn('totalItem', function ($row) {
                    return number_format((float) $row->totalItem, 0, ',', '.');
                })
                ->editColumn('totalQty', function ($row) {
                    return number_format((float) $row->totalQty, 1, ',', '.');
                })
                ->editColumn('totalCost', function ($row) {
                    return number_format((float) $row->totalCost, 0, ',', '.');
                })
                ->rawColumns(['action', 'maintenanceDate', 'plateNumber', 'warehouseName', 'totalItem', 'totalQty', 'totalCost'])
                ->toJson();
        }
    }

    public function detailItems(string $maintenanceCode)
    {
        $maintenance = Maintenance::query()
            ->with([
                'warehouse',
                'details',
                'details.item',
                'details.item.supplier',
                'fleet',
            ])
            ->where('code', $maintenanceCode)
            ->whereNull('deleted_at')
            ->firstOrFail();

        $details = $maintenance->details->map(function ($detail) {
            $price = (float) ($detail->item->price ?? 0);
            $qty = (float) $detail->qty;

            return [
                'itemCode' => $detail->itemCode,
                'itemName' => $detail->item?->name ?? '-',
                'supplierName' => $detail->item?->supplier?->name ?? '-',
                'qty' => $qty,
                'price' => $price,
                'subtotal' => $price * $qty,
            ];
        })->values();

        return response()->json([
            'code' => $maintenance->code,
            'maintenanceDate' => Carbon::parse($maintenance->date)->format('d-m-Y') . ' ' . Carbon::parse($maintenance->time)->format('H:i'),
            'plateNumber' => $maintenance->fleet?->plateNumber ?? '-',
            'warehouse' => $maintenance->warehouse?->name ?? '-',
            'details' => $details,
            'totalQty' => (float) $details->sum('qty'),
            'totalCost' => (float) $details->sum('subtotal'),
        ]);
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
