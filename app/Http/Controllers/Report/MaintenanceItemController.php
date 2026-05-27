<?php

namespace App\Http\Controllers\Report;

use App\Exports\MaintenanceItemReport;
use App\Http\Controllers\Controller;
use App\Models\Inventory\Item;
use App\Models\Inventory\Warehouse;
use App\Models\Master\Fleet;
use App\Models\Warehouse\MaintenanceDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;
use Yajra\DataTables\DataTables;

class MaintenanceItemController extends Controller
{
    protected $title;
    protected $view;

    public function __construct()
    {
        $this->title = 'Report Maintenance Item';
        $this->view = 'report.maintenance-item.';
    }

    public function index()
    {
        $fleet = Fleet::query()->orderBy('plateNumber')->get();
        $warehouse = Warehouse::query()->orderBy('name')->get();
        $items = Item::query()->orderBy('name')->get();

        return view($this->view . 'index')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('fleet', $fleet)
            ->with('warehouse', $warehouse)
            ->with('items', $items);
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->getFilteredQuery($request);

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('maintenance_date', function ($row) {
                    return $row->maintenance_date ? Carbon::parse($row->maintenance_date)->format('d-m-Y') : '-';
                })
                ->editColumn('maintenance.warehouse.name', function ($row) {
                    return $row->maintenance->warehouse->name ?? '-';
                })
                ->editColumn('maintenance.fleet.plateNumber', function ($row) {
                    return $row->maintenance->fleet->plateNumber ?? '-';
                })
                ->editColumn('item.name', function ($row) {
                    return $row->item->name ?? '-';
                })
                ->editColumn('description', function ($row) {
                    return $row->description ?: '-';
                })
                ->editColumn('qty', function ($row) {
                    return number_format((float) $row->qty, 1, ',', '.');
                })
                ->editColumn('price', function ($row) {
                    return number_format((float) $row->price, 0, ',', '.');
                })
                ->editColumn('total', function ($row) {
                    return number_format((float) $row->total, 0, ',', '.');
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at ? $row->created_at->format('d-m-Y H:i') : '-';
                })
                ->rawColumns(['description'])
                ->toJson();
        }
    }

    private function getFilteredQuery(Request $request)
    {
        $query = MaintenanceDetail::query()
            ->select([
                'maintenance_detail.*',
                'maintenance.date as maintenance_date',
                'maintenance.time as maintenance_time',
            ])
            ->join('maintenance', 'maintenance.code', '=', 'maintenance_detail.maintenanceCode')
            ->leftJoin('fleet', 'fleet.code', '=', 'maintenance.fleetCode')
            ->leftJoin('warehouse', 'warehouse.code', '=', 'maintenance.warehouseCode')
            ->leftJoin('item', 'item.code', '=', 'maintenance_detail.itemCode')
            ->with([
                'maintenance',
                'maintenance.fleet',
                'maintenance.warehouse',
                'item'
            ])
            ->whereNull('maintenance.deleted_at')
            ->whereNull('maintenance_detail.deleted_at');

        if ($request->filled('startDate')) {
            $query->whereDate('maintenance.date', '>=', $request->startDate);
        }

        if ($request->filled('endDate')) {
            $query->whereDate('maintenance.date', '<=', $request->endDate);
        }

        if ($request->filled('fleetCode')) {
            $query->where('maintenance.fleetCode', $request->fleetCode);
        }

        if ($request->filled('warehouseCode')) {
            $query->where('maintenance.warehouseCode', $request->warehouseCode);
        }

        if ($request->filled('itemCode')) {
            $query->where('maintenance_detail.itemCode', $request->itemCode);
        }

        return $query->orderBy('maintenance.date', 'desc')
            ->orderBy('maintenance_detail.created_at', 'desc');
    }

    public function excelMaintenanceItem(Request $request)
    {
        return Excel::download(new MaintenanceItemReport($request), 'Report-Maintenance-Item.xlsx');
    }

    public function pdfMaintenanceItem(Request $request)
    {
        $rows = $this->getFilteredQuery($request)->get();

        $fleetName = null;
        if ($request->filled('fleetCode')) {
            $fleetName = Fleet::query()->where('code', $request->fleetCode)->value('plateNumber');
        }

        $warehouseName = null;
        if ($request->filled('warehouseCode')) {
            $warehouseName = Warehouse::query()->where('code', $request->warehouseCode)->value('name');
        }

        $itemName = null;
        if ($request->filled('itemCode')) {
            $itemName = Item::query()->where('code', $request->itemCode)->value('name');
        }

        $totalQty = (float) $rows->sum('qty');
        $totalCost = (float) $rows->sum('total');

        $mpdf = new Mpdf([
            'orientation' => 'L',
            'format' => [215, 330],
            'tempDir' => storage_path('app/mpdf-temp'),
        ]);

        // Render & Write Header HTML
        $headerHtml = view($this->view . 'report.maintenance-item-pdf-header')
            ->with('fleetName', $fleetName)
            ->with('warehouseName', $warehouseName)
            ->with('itemName', $itemName)
            ->with('startDate', $request->startDate)
            ->with('endDate', $request->endDate)
            ->render();
        $mpdf->WriteHTML($headerHtml);

        // Render & Write Rows in Chunks to prevent PCRE backtrack limit exceptions
        $chunkSize = 50;
        $chunks = $rows->chunk($chunkSize);
        $start = 0;
        foreach ($chunks as $chunk) {
            $rowHtml = view($this->view . 'report.maintenance-item-pdf-rows')
                ->with('data', $chunk)
                ->with('start', $start)
                ->render();
            $mpdf->WriteHTML($rowHtml);
            $start += $chunk->count();
        }

        // Render & Write Footer HTML
        $footerHtml = view($this->view . 'report.maintenance-item-pdf-footer')
            ->with('totalQty', $totalQty)
            ->with('totalCost', $totalCost)
            ->render();
        $mpdf->WriteHTML($footerHtml);

        return $mpdf->Output('Report-Maintenance-Item.pdf', 'I');
    }
}
