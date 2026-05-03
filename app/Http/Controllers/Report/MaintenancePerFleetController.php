<?php

namespace App\Http\Controllers\Report;

use App\Exports\MaintenanceFleetDetailReport;
use App\Exports\MaintenanceFleetReport;
use App\Http\Controllers\Controller;
use App\Models\Master\Fleet;
use App\Models\Master\FleetCompany;
use App\Models\Warehouse\Maintenance;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;
use Yajra\DataTables\DataTables;

class MaintenancePerFleetController extends Controller
{
    protected $title;

    protected $view;

    public function __construct()
    {
        $this->title = 'Maintenance Per Fleet';
        $this->view = 'report.maintenance-fleet.';
    }

    public function index()
    {
        $fleet = Fleet::query()->orderBy('plateNumber')->get();
        $fleetCompany = FleetCompany::query()->orderBy('name')->get();

        return view($this->view . 'index')
            ->with('view', $this->view)
            ->with('fleet', $fleet)
            ->with('fleetCompany', $fleetCompany)
            ->with('title', $this->title);
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = Maintenance::query()
                ->select([
                    'fleet.code as fleetCode',
                    'fleet.plateNumber',
                    'fleet_company.name as fleetCompanyName',
                    'fleet_company.type as fleetCompanyType',
                    DB::raw('COUNT(DISTINCT maintenance.code) as totalMaintenance'),
                    DB::raw('COALESCE(SUM(maintenance_detail.qty), 0) as totalQty'),
                    DB::raw('COALESCE(SUM(maintenance_detail.price * maintenance_detail.qty), 0) as totalCost'),
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
                $data->where('fleet.code', $request->fleetCode);
            }

            if ($request->filled('fleetCompanyCode')) {
                $data->where('fleet_company.code', $request->fleetCompanyCode);
            }

            $this->applyDateFilter($data, $request->startDate, $request->endDate);

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) use ($request) {
                    $detailUrl = route('report.maintenance-fleet.detail', ['fleetCode' => $row->fleetCode]);

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
                ->editColumn('fleetCompanyName', function ($row) {
                    return $row->fleetCompanyName ?: '-';
                })
                ->editColumn('fleetCompanyType', function ($row) {
                    return $row->fleetCompanyType ?: 'Internal';
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
                ->rawColumns(['action', 'fleetCompanyName', 'fleetCompanyType', 'totalMaintenance', 'totalQty', 'totalCost'])
                ->toJson();
        }
    }

    public function detail(string $fleetCode, Request $request)
    {
        $fleet = Fleet::query()
            ->with(['company'])
            ->where('code', $fleetCode)
            ->whereNull('deleted_at')
            ->firstOrFail();

        $summaryQuery = Maintenance::query()
            ->select([
                DB::raw('COUNT(DISTINCT maintenance.code) as totalMaintenance'),
                DB::raw('COALESCE(SUM(maintenance_detail.qty), 0) as totalQty'),
                DB::raw('COALESCE(SUM(maintenance_detail.price * maintenance_detail.qty), 0) as totalCost'),
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

        $totalMaintenance = (int) ($summary->totalMaintenance ?? 0);
        $totalQty = (float) ($summary->totalQty ?? 0);
        $totalCost = (float) ($summary->totalCost ?? 0);

        return view($this->view . 'show')
            ->with('view', $this->view)
            ->with('title', 'Maintenance Detail Per Fleet')
            ->with('fleet', $fleet)
            ->with('totalMaintenance', $totalMaintenance)
            ->with('totalQty', $totalQty)
            ->with('totalCost', $totalCost)
            ->with('startDate', $request->startDate)
            ->with('endDate', $request->endDate);
    }

    public function pdfMaintenanceFleet(Request $request)
    {
        $data = Maintenance::query()
            ->select([
                'fleet.code as fleetCode',
                'fleet.plateNumber',
                'fleet_company.name as fleetCompanyName',
                'fleet_company.type as fleetCompanyType',
                DB::raw('COUNT(DISTINCT maintenance.code) as totalMaintenance'),
                DB::raw('COALESCE(SUM(maintenance_detail.qty), 0) as totalQty'),
                DB::raw('COALESCE(SUM(maintenance_detail.price * maintenance_detail.qty), 0) as totalCost'),
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
            $data->where('fleet.code', $request->fleetCode);
        }

        if ($request->filled('fleetCompanyCode')) {
            $data->where('fleet_company.code', $request->fleetCompanyCode);
        }

        $this->applyDateFilter($data, $request->startDate, $request->endDate);

        $rows = $data->get();

        $fleetName = null;
        if ($request->filled('fleetCode')) {
            $fleetName = Fleet::query()->where('code', $request->fleetCode)->value('plateNumber');
        }

        $fleetCompanyName = null;
        if ($request->filled('fleetCompanyCode')) {
            $fleetCompanyName = FleetCompany::query()->where('code', $request->fleetCompanyCode)->value('name');
        }

        $mpdf = new Mpdf([
            'orientation' => 'L',
            'format' => [215, 330],
            'tempDir' => storage_path('app/mpdf-temp'),
        ]);

        $mpdf->WriteHTML(
            view($this->view . 'report.maintenance-fleet-pdf')
                ->with('rows', $rows)
                ->with('fleetName', $fleetName)
                ->with('fleetCompanyName', $fleetCompanyName)
                ->with('startDate', $request->startDate)
                ->with('endDate', $request->endDate)
        );

        return $mpdf->Output('Maintenance-Fleet-Report.pdf', 'I');
    }

    public function excelMaintenanceFleet(Request $request)
    {
        return Excel::download(new MaintenanceFleetReport($request), 'Maintenance-Fleet-Report.xlsx');
    }

    public function pdfMaintenanceFleetDetail(string $fleetCode, Request $request)
    {
        $fleet = Fleet::query()
            ->with(['company'])
            ->where('code', $fleetCode)
            ->whereNull('deleted_at')
            ->firstOrFail();

        $summaryQuery = Maintenance::query()
            ->select([
                DB::raw('COUNT(DISTINCT maintenance.code) as totalMaintenance'),
                DB::raw('COALESCE(SUM(maintenance_detail.qty), 0) as totalQty'),
                DB::raw('COALESCE(SUM(maintenance_detail.price * maintenance_detail.qty), 0) as totalCost'),
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

        $maintenances = Maintenance::query()
            ->with([
                'warehouse',
                'details',
                'details.item',
                'details.item.supplier',
            ])
            ->where('fleetCode', $fleetCode)
            ->where('status', 0)
            ->whereNull('deleted_at')
            ->orderByDesc('date')
            ->orderByDesc('time');

        $this->applyDateFilter($maintenances, $request->startDate, $request->endDate);

        $rows = $maintenances->get();

        $mpdf = new Mpdf([
            'orientation' => 'L',
            'format' => [215, 330],
            'tempDir' => storage_path('app/mpdf-temp'),
        ]);

        $mpdf->WriteHTML(
            view($this->view . 'report.maintenance-fleet-detail-pdf')
                ->with('fleet', $fleet)
                ->with('rows', $rows)
                ->with('totalMaintenance', (int) ($summary->totalMaintenance ?? 0))
                ->with('totalQty', (float) ($summary->totalQty ?? 0))
                ->with('totalCost', (float) ($summary->totalCost ?? 0))
                ->with('startDate', $request->startDate)
                ->with('endDate', $request->endDate)
        );

        $filename = 'Maintenance-Fleet-Detail-' . $fleet->plateNumber . '.pdf';

        return $mpdf->Output($filename, 'I');
    }

    public function excelMaintenanceFleetDetail(string $fleetCode, Request $request)
    {
        $request->merge(['fleetCode' => $fleetCode]);

        $plateNumber = Fleet::query()->where('code', $fleetCode)->value('plateNumber') ?? $fleetCode;
        $filename = 'Maintenance-Fleet-Detail-' . $plateNumber . '.xlsx';

        return Excel::download(new MaintenanceFleetDetailReport($request), $filename);
    }

    public function datatableDetail(string $fleetCode, Request $request)
    {
        if ($request->ajax()) {
            $data = Maintenance::query()
                ->select([
                    'maintenance.code',
                    'maintenance.date',
                    'maintenance.time',
                    'maintenance.grand_total',
                    'warehouse.name as warehouseName',
                    DB::raw('COUNT(DISTINCT maintenance_detail.code) as totalItem'),
                    DB::raw('COALESCE(SUM(maintenance_detail.qty), 0) as totalQty'),
                ])
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
                ->where('maintenance.fleetCode', $fleetCode)
                ->where('maintenance.status', 0)
                ->whereNull('maintenance.deleted_at')
                ->groupBy('maintenance.code', 'maintenance.date', 'maintenance.time', 'maintenance.grand_total', 'warehouse.name')
                ->orderByDesc('maintenance.date')
                ->orderByDesc('maintenance.time');

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
                    return number_format((float) $row->grand_total, 0, ',', '.');
                })
                ->rawColumns(['action', 'maintenanceDate', 'warehouseName', 'totalItem', 'totalQty', 'totalCost'])
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
            ])
            ->where('code', $maintenanceCode)
            ->whereNull('deleted_at')
            ->firstOrFail();

        $details = $maintenance->details->map(function ($detail) {
            $price = (float) ($detail->price ?? 0);
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

        $totalQty = (float) $details->sum('qty');
        $totalCost = (float) ($maintenance->grand_total ?? 0);

        return response()->json([
            'code' => $maintenance->code,
            'maintenanceDate' => Carbon::parse($maintenance->date)->format('d-m-Y') . ' ' . Carbon::parse($maintenance->time)->format('H:i'),
            'warehouse' => $maintenance->warehouse?->name ?? '-',
            'details' => $details,
            'totalQty' => $totalQty,
            'totalCost' => $totalCost,
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
