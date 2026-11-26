<?php

namespace App\Http\Controllers\Master;

use App\Exports\CostComponentPriceLogExport;
use App\Http\Controllers\Controller;
use App\Models\Master\CostComponent;
use App\Models\Master\CostComponentPriceLog;
use App\Services\Master\CostComponentPriceLogService;
use App\Traits\RendersPdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;

class CostComponentPriceLogController extends Controller
{
    use RendersPdf;

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
        $costComponents = CostComponent::orderBy('name')->get();

        return view($this->view.'index')
            ->with('view', $this->view)
            ->with('costComponents', $costComponents)
            ->with('title', $this->title);
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->filteredQuery($request);

            return Datatables::of($data)
                ->addIndexColumn()
                ->filterColumn('DT_RowIndex', function ($query, $keyword) {
                    return $query;
                })
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

    public function exportPdf(Request $request)
    {
        $rows = $this->filteredQuery($request)->get();

        $filterSummary = [];
        if ($request->filled('costComponentCode')) {
            $compName = CostComponent::where('code', $request->costComponentCode)->value('name');
            $filterSummary[] = 'Komponen: '.($compName ?: $request->costComponentCode);
        }
        if ($request->filled('keyword')) {
            $filterSummary[] = 'Kata Kunci: '.$request->keyword;
        }
        if ($request->filled('startDate') || $request->filled('endDate')) {
            $start = $request->filled('startDate') ? Carbon::parse($request->startDate)->format('d-m-Y') : 'Awal';
            $end = $request->filled('endDate') ? Carbon::parse($request->endDate)->format('d-m-Y') : 'Sekarang';
            $filterSummary[] = 'Periode: '.$start.' s/d '.$end;
        }

        return $this->renderPdfResponse(
            $this->view.'report.cost-component-price-log-pdf',
            [
                'rows' => $rows,
                'title' => 'Laporan Riwayat Harga Komponen Biaya',
                'filterSummary' => $filterSummary,
                'printedAt' => now(),
            ],
            'Cost-Component-Price-Log-Report.pdf',
            'L'
        );
    }

    private function filteredQuery(Request $request)
    {
        $query = CostComponentPriceLog::query()->orderBy('created_at', 'desc');

        if ($request->filled('costComponentCode')) {
            $query->where('costComponentCode', $request->costComponentCode);
        }

        if ($request->filled('keyword')) {
            $term = strtolower(trim((string) $request->keyword));
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(costComponentCode) LIKE ?', ['%'.$term.'%'])
                    ->orWhereRaw('LOWER(costComponentName) LIKE ?', ['%'.$term.'%'])
                    ->orWhereRaw('LOWER(COALESCE(changedBy, \'\')) LIKE ?', ['%'.$term.'%'])
                    ->orWhereRaw('LOWER(COALESCE(notes, \'\')) LIKE ?', ['%'.$term.'%']);
            });
        }

        if ($request->filled('startDate')) {
            $query->whereDate('created_at', '>=', $request->startDate);
        }

        if ($request->filled('endDate')) {
            $query->whereDate('created_at', '<=', $request->endDate);
        }

        return $query;
    }
}
