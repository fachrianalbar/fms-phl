<?php

namespace App\Http\Controllers\Inventory;

use App\Exports\WarehouseExport;
use App\Http\Controllers\Controller;
use App\Models\Inventory\Warehouse;
use App\Services\Inventory\WarehouseService;
use App\Services\MenuService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;
use Yajra\DataTables\DataTables;

class WarehouseController extends Controller
{
    protected $service;

    protected $title;

    protected $view;

    protected $menuSvc;

    public function __construct(WarehouseService $warehouseSvc, MenuService $menuSvc)
    {
        $this->service = $warehouseSvc;
        $this->title = 'Warehouse';
        $this->view = 'inventory.warehouse.';
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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view($this->view.'create')
            ->with('view', $this->view)
            ->with('title', $this->title);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            // 'address' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->route($this->view.'index')->with('fail', $validator->errors()->all()[0]);
        }
        try {
            DB::beginTransaction();

            $this->service->store($request, $this->title);
            DB::commit();

            return redirect()->route($this->view.'index')->with('success', $this->title.' '.__('general.data_was_save_successfully'));
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->route($this->view.'index')->with('fail', 'Line : '.$th->getLine().'<br>'.$th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data = $this->service->getById($id);

        if (! $data) {
            return redirect()->route($this->view.'index')->with('fail', 'Data not found');
        }

        return view($this->view.'edit')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('data', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            // 'address' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->route($this->view.'index')->with('fail', $validator->errors()->all()[0]);
        }
        try {
            DB::beginTransaction();

            $this->service->update($request, $id, $this->title);

            DB::commit();

            return redirect()->route($this->view.'index')->with('success', $this->title.' '.__('general.data_was_update_succesfully'));
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->route($this->view.'index')->with('fail', 'Line : '.$th->getLine().'<br>'.$th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->service->destroy($id, $this->title);

        return redirect()->route($this->view.'index')->with('success', 'Delete Data Success');
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
                ->addColumn('action', function ($row) {
                    $btn = '<td>
        <a href="'.route($this->view.'edit', $row->id).'"
           class="btn btn-icon btn-sm bg-primary-subtle me-1"
           data-bs-toggle="tooltip" title="Edit">
            <i class="mdi mdi-pencil-outline fs-14 text-primary"></i>
        </a>

        <a href="javascript:deleteData(\''.$row->id.'\')"
           class="btn btn-icon btn-sm bg-danger-subtle"
           data-bs-toggle="tooltip" title="Delete">
            <i class="mdi mdi-delete fs-14 text-danger"></i>
        </a>
    </td>';

                    return $btn;
                })
                ->rawColumns(['action'])
                ->toJson();
        }
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(
            new WarehouseExport($this->filteredQuery($request)),
            'Data-Gudang-'.now()->format('YmdHis').'.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $warehouses = $this->filteredQuery($request)->get();

        $filterSummary = [];
        if ($request->filled('keyword')) {
            $filterSummary[] = 'Kata Kunci: '.$request->keyword;
        }
        if ($request->filled('startDate') || $request->filled('endDate')) {
            $start = $request->filled('startDate') ? Carbon::parse($request->startDate)->format('d-m-Y') : 'Awal';
            $end = $request->filled('endDate') ? Carbon::parse($request->endDate)->format('d-m-Y') : 'Sekarang';
            $filterSummary[] = 'Periode Dibuat: '.$start.' s/d '.$end;
        }

        $tempDir = storage_path('app/mpdf-temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $mpdf = new Mpdf([
            'orientation' => 'P',
            'format' => 'A4',
            'tempDir' => $tempDir,
            'default_font' => 'sans-serif',
        ]);

        $mpdf->WriteHTML(view($this->view.'report.warehouse-pdf', [
            'warehouses' => $warehouses,
            'title' => 'Laporan Data Gudang',
            'filterSummary' => $filterSummary,
            'printedAt' => now(),
        ])->render());

        return response($mpdf->Output('', 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="Data-Gudang-'.now()->format('YmdHis').'.pdf"');
    }

    private function filteredQuery(Request $request)
    {
        $query = Warehouse::query()->orderBy('code', 'asc');

        if ($request->filled('keyword')) {
            $keyword = strtolower(trim((string) $request->keyword));
            $query->where(function ($q) use ($keyword) {
                $q->whereRaw('LOWER(code) LIKE ?', ['%'.$keyword.'%'])
                    ->orWhereRaw('LOWER(name) LIKE ?', ['%'.$keyword.'%'])
                    ->orWhereRaw('LOWER(address) LIKE ?', ['%'.$keyword.'%']);
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
