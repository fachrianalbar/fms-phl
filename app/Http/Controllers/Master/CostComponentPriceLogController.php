<?php

namespace App\Http\Controllers\Master;

use App\Exports\CostComponentPriceLogExport;
use App\Http\Controllers\Controller;
use App\Services\Master\CostComponentPriceLogService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;

class CostComponentPriceLogController extends Controller
{
    protected $service;

    protected $title;

    protected $view;

    public function __construct(CostComponentPriceLogService $service)
    {
        $this->service = $service;
        $this->title = 'Cost Component Price Log';
        $this->view = 'master.cost-component-price-log.';
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view($this->view.'index')
            ->with('view', $this->view)
            ->with('title', $this->title);
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->findAll();

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('formatted_old_price', function ($row) {
                    return $row->oldPrice ? number_format($row->oldPrice, 0, ',', '.') : '-';
                })
                ->addColumn('formatted_new_price', function ($row) {
                    return $row->newPrice ? number_format($row->newPrice, 0, ',', '.') : '-';
                })
                ->addColumn('formatted_date', function ($row) {
                    return $row->created_at ? $row->created_at->format('d-m-Y H:i:s') : '-';
                })
                ->rawColumns([])
                ->toJson();
        }
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new CostComponentPriceLogExport($request), 'Cost-Component-Price-Log-Report.xlsx');
    }
}
