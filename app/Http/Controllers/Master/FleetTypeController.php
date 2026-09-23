<?php

namespace App\Http\Controllers\Master;

use App\Exports\FleetTypeDetailExport;
use App\Exports\FleetTypeExport;
use App\Http\Controllers\Controller;
use App\Models\Master\Fleet;
use App\Models\Master\FleetBrand;
use App\Models\Master\FleetCompany;
use App\Models\Master\FleetType;
use App\Services\Master\FleetTypeService;
use App\Services\MenuService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;
use Yajra\DataTables\DataTables;

class FleetTypeController extends Controller
{
    protected $service;

    protected $title;

    protected $view;

    protected $menuSvc;

    public function __construct(FleetTypeService $fleetTypeService, MenuService $menuSvc)
    {
        $this->service = $fleetTypeService;
        $this->title = 'Fleet Type';
        $menu = $menuSvc->getByName('Fleet Type');
        if ($menu) {
            $this->title = (Auth::check() && Auth::user()->languange == 'en') ? $menu->name : $menu->nama;
        }
        $this->view = 'master.fleet-type.';
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $types = $this->service->findAll();

        return view($this->view.'index')
            ->with('view', $this->view)
            ->with('types', $types)
            ->with('title', $this->title);
    }

    /**
     * Show the form for creating a new resource (Redirect to index modal).
     */
    public function create()
    {
        return redirect()->route($this->view.'index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
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
     * Display the specified resource (Detail Page with Profile Banner & KPI summary).
     */
    public function show(string $id)
    {
        $type = $this->service->getById($id);

        if (! $type) {
            return redirect()->route($this->view.'index')->with('fail', 'Data not found');
        }

        $totalFleets = Fleet::where('fleetTypeCode', $type->code)->count();
        $totalBrands = Fleet::where('fleetTypeCode', $type->code)
            ->whereNotNull('fleetBrandCode')
            ->distinct('fleetBrandCode')
            ->count('fleetBrandCode');
        $totalCompanies = Fleet::where('fleetTypeCode', $type->code)
            ->whereNotNull('fleetCompanyCode')
            ->distinct('fleetCompanyCode')
            ->count('fleetCompanyCode');

        $fleetBrands = FleetBrand::orderBy('name')->get();
        $fleetCompanies = FleetCompany::orderBy('name')->get();

        return view($this->view.'show')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('type', $type)
            ->with('totalFleets', $totalFleets)
            ->with('totalBrands', $totalBrands)
            ->with('totalCompanies', $totalCompanies)
            ->with('fleetBrands', $fleetBrands)
            ->with('fleetCompanies', $fleetCompanies);
    }

    /**
     * Show the form for editing the specified resource (Redirect to index modal).
     */
    public function edit(string $id)
    {
        if (! $this->service->getById($id)) {
            return redirect()->route($this->view.'index')->with('fail', 'Data not found');
        }

        return redirect()->route($this->view.'index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
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

    /**
     * Datatable for Fleet Type index.
     */
    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->buildFilteredQuery($request);

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $showUrl = route($this->view.'show', $row->id);
                    $updateUrl = route($this->view.'update', $row->id);
                    $typeName = e($row->name);
                    $escapedUpdateUrl = e($updateUrl);

                    return '<a href="'.$showUrl.'" class="btn btn-icon btn-sm bg-info-subtle me-1" data-bs-toggle="tooltip" title="Detail">'
                        .'<i class="mdi mdi-eye-outline fs-14 text-info"></i></a>'
                        .'<button type="button" class="btn btn-icon btn-sm bg-primary-subtle me-1 js-edit-fleet-type"'
                        .' data-bs-toggle="modal" data-bs-target="#fleetTypeCrudModal"'
                        .' data-name="'.$typeName.'" data-action="'.$escapedUpdateUrl.'"'
                        .' title="Edit">'
                        .'<i class="mdi mdi-pencil-outline fs-14 text-primary"></i></button>'
                        .'<button type="button" class="btn btn-icon btn-sm bg-danger-subtle" onclick="deleteData(\''.$row->id.'\')" data-bs-toggle="tooltip" title="Delete">'
                        .'<i class="mdi mdi-delete fs-14 text-danger"></i></button>';
                })
                ->editColumn('code', function ($row) {
                    return '<span class="font-monospace fw-semibold text-dark">'.$row->code.'</span>';
                })
                ->editColumn('name', function ($row) {
                    return '<span class="fw-semibold text-dark">'.$row->name.'</span>';
                })
                ->addColumn('fleets_count', function ($row) {
                    $count = $row->fleets_count ?? 0;

                    return '<span class="badge bg-primary-subtle text-primary font-monospace px-2 py-1" style="border-radius: 6px; font-weight: 600; font-size: 11px;">'
                        .number_format($count, 0, ',', '.').' Armada</span>';
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at ? '<span class="font-monospace text-muted">'.$row->created_at->format('d/m/Y H:i').'</span>' : '-';
                })
                ->rawColumns(['action', 'code', 'name', 'fleets_count', 'created_at'])
                ->toJson();
        }
    }

    /**
     * Datatable for Fleets belonging to a specific Fleet Type.
     */
    public function datatableFleets(string $id, Request $request)
    {
        if ($request->ajax()) {
            $type = $this->service->getById($id);
            if (! $type) {
                return response()->json(['data' => []]);
            }

            $query = Fleet::query()
                ->with(['brand', 'company'])
                ->where('fleetTypeCode', $type->code);

            if ($request->filled('fleetBrandCode')) {
                $query->where('fleetBrandCode', $request->fleetBrandCode);
            }

            if ($request->filled('fleetCompanyCode')) {
                $query->where('fleetCompanyCode', $request->fleetCompanyCode);
            }

            if ($request->filled('startDate')) {
                $query->whereDate('created_at', '>=', $request->startDate);
            }

            if ($request->filled('endDate')) {
                $query->whereDate('created_at', '<=', $request->endDate);
            }

            return Datatables::of($query)
                ->addIndexColumn()
                ->editColumn('plateNumber', function ($row) {
                    return '<span class="font-monospace fw-bold text-dark">'.$row->plateNumber.'</span>';
                })
                ->editColumn('code', function ($row) {
                    return '<span class="font-monospace">'.$row->code.'</span>';
                })
                ->addColumn('brandName', function ($row) {
                    return $row->brand ? '<span class="badge bg-light text-dark px-2 py-1 border" style="border-radius: 6px; font-weight: 600; font-size: 11px;">'.$row->brand->name.'</span>' : '-';
                })
                ->addColumn('companyName', function ($row) {
                    return $row->company ? $row->company->name : '-';
                })
                ->editColumn('year', function ($row) {
                    return $row->year ? '<span class="font-monospace">'.$row->year.'</span>' : '-';
                })
                ->editColumn('engineNumber', function ($row) {
                    return $row->engineNumber ? '<span class="font-monospace text-muted" style="font-size: 11.5px;">'.$row->engineNumber.'</span>' : '-';
                })
                ->editColumn('frameNumber', function ($row) {
                    return $row->frameNumber ? '<span class="font-monospace text-muted" style="font-size: 11.5px;">'.$row->frameNumber.'</span>' : '-';
                })
                ->rawColumns(['plateNumber', 'code', 'brandName', 'companyName', 'year', 'engineNumber', 'frameNumber'])
                ->toJson();
        }
    }

    /**
     * Build filtered query for Fleet Type list.
     */
    private function buildFilteredQuery(Request $request)
    {
        $query = $this->service->findAllQuery();

        if ($request->filled('typeCode')) {
            $query->where('code', $request->typeCode);
        }

        if ($request->filled('startDate')) {
            $query->whereDate('created_at', '>=', $request->startDate);
        }

        if ($request->filled('endDate')) {
            $query->whereDate('created_at', '<=', $request->endDate);
        }

        return $query;
    }

    /**
     * Export Excel for Fleet Type list.
     */
    public function exportExcel(Request $request)
    {
        return Excel::download(new FleetTypeExport($request), 'Fleet-Type-Report.xlsx');
    }

    /**
     * Export PDF for Fleet Type list.
     */
    public function exportPdf(Request $request)
    {
        $rows = $this->buildFilteredQuery($request)->get();

        $typeFilterName = null;
        if ($request->filled('typeCode')) {
            $selectedType = FleetType::where('code', $request->typeCode)->first();
            $typeFilterName = $selectedType ? $selectedType->name : null;
        }

        $mpdf = new Mpdf([
            'orientation' => 'P',
            'format' => 'A4',
            'tempDir' => storage_path('app/mpdf-temp'),
        ]);

        $mpdf->WriteHTML(
            view($this->view.'report.fleet-type-pdf')
                ->with('rows', $rows)
                ->with('typeFilterName', $typeFilterName)
                ->with('startDate', $request->startDate)
                ->with('endDate', $request->endDate)
                ->render()
        );

        return $mpdf->Output('Fleet-Type-Report.pdf', 'I');
    }

    /**
     * Export Excel for Fleets under specific Fleet Type.
     */
    public function exportDetailExcel(string $id, Request $request)
    {
        $type = $this->service->getById($id);

        if (! $type) {
            return redirect()->route($this->view.'index')->with('fail', 'Data not found');
        }

        return Excel::download(new FleetTypeDetailExport($type->code, $request), 'Fleet-Type-'.$type->code.'-Fleets.xlsx');
    }

    /**
     * Export PDF for Fleets under specific Fleet Type.
     */
    public function exportDetailPdf(string $id, Request $request)
    {
        $type = $this->service->getById($id);

        if (! $type) {
            return redirect()->route($this->view.'index')->with('fail', 'Data not found');
        }

        $query = Fleet::query()
            ->with(['brand', 'company'])
            ->where('fleetTypeCode', $type->code)
            ->orderBy('plateNumber');

        if ($request->filled('fleetBrandCode')) {
            $query->where('fleetBrandCode', $request->fleetBrandCode);
        }

        if ($request->filled('fleetCompanyCode')) {
            $query->where('fleetCompanyCode', $request->fleetCompanyCode);
        }

        if ($request->filled('startDate')) {
            $query->whereDate('created_at', '>=', $request->startDate);
        }

        if ($request->filled('endDate')) {
            $query->whereDate('created_at', '<=', $request->endDate);
        }

        $rows = $query->get();

        $fleetBrandName = null;
        if ($request->filled('fleetBrandCode')) {
            $fleetBrandName = optional(FleetBrand::where('code', $request->fleetBrandCode)->first())->name;
        }

        $fleetCompanyName = null;
        if ($request->filled('fleetCompanyCode')) {
            $fleetCompanyName = optional(FleetCompany::where('code', $request->fleetCompanyCode)->first())->name;
        }

        $mpdf = new Mpdf([
            'orientation' => 'L',
            'format' => 'A4',
            'tempDir' => storage_path('app/mpdf-temp'),
        ]);

        $mpdf->WriteHTML(
            view($this->view.'report.fleet-type-detail-pdf')
                ->with('type', $type)
                ->with('rows', $rows)
                ->with('fleetBrandName', $fleetBrandName)
                ->with('fleetCompanyName', $fleetCompanyName)
                ->with('startDate', $request->startDate)
                ->with('endDate', $request->endDate)
                ->render()
        );

        return $mpdf->Output('Fleet-Type-'.$type->code.'-Fleets.pdf', 'I');
    }
}
