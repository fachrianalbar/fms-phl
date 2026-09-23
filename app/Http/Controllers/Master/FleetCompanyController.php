<?php

namespace App\Http\Controllers\Master;

use App\Exports\FleetCompanyDetailExport;
use App\Exports\FleetCompanyExport;
use App\Http\Controllers\Controller;
use App\Models\Master\Fleet;
use App\Models\Master\FleetBrand;
use App\Models\Master\FleetCompany;
use App\Models\Master\FleetType;
use App\Services\Master\FleetCompanyService;
use App\Services\MenuService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;
use Yajra\DataTables\DataTables;

class FleetCompanyController extends Controller
{
    protected $service;

    protected $title;

    protected $view;

    protected $menuSvc;

    public function __construct(FleetCompanyService $fleetCompanySvc, MenuService $menuSvc)
    {
        $this->service = $fleetCompanySvc;
        $this->title = 'Fleet Company';
        $menu = $menuSvc->getByName('Fleet Company');
        if ($menu) {
            $this->title = (Auth::check() && Auth::user()->languange == 'en') ? $menu->name : $menu->nama;
        }
        $this->view = 'master.fleet-company.';
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $companies = $this->service->findAll();

        return view($this->view.'index')
            ->with('view', $this->view)
            ->with('companies', $companies)
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
            'name' => 'required|string|max:255',
            'type' => 'required|in:Internal,External',
            'accountNumber' => 'nullable|string|max:100',
            'bankName' => 'nullable|string|max:100',
            'pph' => 'nullable|numeric|min:0|max:100',
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
        $company = $this->service->getById($id);

        if (! $company) {
            return redirect()->route($this->view.'index')->with('fail', 'Data not found');
        }

        $totalFleets = Fleet::where('fleetCompanyCode', $company->code)->count();
        $totalBrands = Fleet::where('fleetCompanyCode', $company->code)
            ->whereNotNull('fleetBrandCode')
            ->distinct('fleetBrandCode')
            ->count('fleetBrandCode');
        $totalTypes = Fleet::where('fleetCompanyCode', $company->code)
            ->whereNotNull('fleetTypeCode')
            ->distinct('fleetTypeCode')
            ->count('fleetTypeCode');

        $fleetBrands = FleetBrand::orderBy('name')->get();
        $fleetTypes = FleetType::orderBy('name')->get();

        return view($this->view.'show')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('company', $company)
            ->with('totalFleets', $totalFleets)
            ->with('totalBrands', $totalBrands)
            ->with('totalTypes', $totalTypes)
            ->with('fleetBrands', $fleetBrands)
            ->with('fleetTypes', $fleetTypes);
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
            'name' => 'required|string|max:255',
            'type' => 'required|in:Internal,External',
            'accountNumber' => 'nullable|string|max:100',
            'bankName' => 'nullable|string|max:100',
            'pph' => 'nullable|numeric|min:0|max:100',
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
        try {
            DB::beginTransaction();

            $this->service->destroy($id, $this->title);

            DB::commit();

            return redirect()->route($this->view.'index')->with('success', 'Delete Data Success');
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->route($this->view.'index')->with('fail', 'Line : '.$th->getLine().'<br>'.$th->getMessage());
        }
    }

    /**
     * Datatable for Fleet Company index.
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
                    $companyName = e($row->name);
                    $companyType = e($row->type ?? 'Internal');
                    $accountNumber = e($row->accountNumber ?? '');
                    $bankName = e($row->bankName ?? '');
                    $pph = e($row->pph ?? '0');
                    $escapedUpdateUrl = e($updateUrl);

                    return '<a href="'.$showUrl.'" class="btn btn-icon btn-sm bg-info-subtle me-1" data-bs-toggle="tooltip" title="Detail">'
                        .'<i class="mdi mdi-eye-outline fs-14 text-info"></i></a>'
                        .'<button type="button" class="btn btn-icon btn-sm bg-primary-subtle me-1 js-edit-fleet-company"'
                        .' data-bs-toggle="modal" data-bs-target="#fleetCompanyCrudModal"'
                        .' data-id="'.$row->id.'"'
                        .' data-name="'.$companyName.'"'
                        .' data-type="'.$companyType.'"'
                        .' data-account-number="'.$accountNumber.'"'
                        .' data-bank-name="'.$bankName.'"'
                        .' data-pph="'.$pph.'"'
                        .' data-action="'.$escapedUpdateUrl.'"'
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
                ->editColumn('type', function ($row) {
                    if ($row->type === 'Internal') {
                        return '<span class="badge bg-primary-subtle text-primary px-2 py-1" style="border-radius: 6px; font-weight: 600; font-size: 11px;">Internal</span>';
                    } elseif ($row->type === 'External') {
                        return '<span class="badge bg-info-subtle text-info px-2 py-1" style="border-radius: 6px; font-weight: 600; font-size: 11px;">External</span>';
                    }

                    return $row->type ?? '-';
                })
                ->editColumn('accountNumber', function ($row) {
                    return $row->accountNumber ? '<span class="font-monospace">'.$row->accountNumber.'</span>' : '-';
                })
                ->editColumn('bankName', function ($row) {
                    return $row->bankName ?? '-';
                })
                ->editColumn('pph', function ($row) {
                    return $row->pph ? '<span class="font-monospace">'.number_format($row->pph, 2, ',', '.').'%</span>' : '<span class="font-monospace text-muted">0,00%</span>';
                })
                ->addColumn('fleets_count', function ($row) {
                    $count = $row->fleets_count ?? 0;

                    return '<span class="badge bg-primary-subtle text-primary font-monospace px-2 py-1" style="border-radius: 6px; font-weight: 600; font-size: 11px;">'
                        .number_format($count, 0, ',', '.').' Armada</span>';
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at ? '<span class="font-monospace text-muted">'.$row->created_at->format('d/m/Y H:i').'</span>' : '-';
                })
                ->rawColumns(['action', 'code', 'name', 'type', 'accountNumber', 'bankName', 'pph', 'fleets_count', 'created_at'])
                ->toJson();
        }
    }

    /**
     * Datatable for Fleets belonging to a specific Fleet Company.
     */
    public function datatableFleets(string $id, Request $request)
    {
        if ($request->ajax()) {
            $company = $this->service->getById($id);
            if (! $company) {
                return response()->json(['data' => []]);
            }

            $query = Fleet::query()
                ->with(['brand', 'type'])
                ->where('fleetCompanyCode', $company->code);

            if ($request->filled('fleetBrandCode')) {
                $query->where('fleetBrandCode', $request->fleetBrandCode);
            }

            if ($request->filled('fleetTypeCode')) {
                $query->where('fleetTypeCode', $request->fleetTypeCode);
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
                ->addColumn('typeName', function ($row) {
                    return $row->type ? '<span class="badge bg-light text-dark px-2 py-1 border" style="border-radius: 6px; font-weight: 600; font-size: 11px;">'.$row->type->name.'</span>' : '-';
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
                ->rawColumns(['plateNumber', 'code', 'brandName', 'typeName', 'year', 'engineNumber', 'frameNumber'])
                ->toJson();
        }
    }

    /**
     * Build filtered query for Fleet Company list.
     */
    private function buildFilteredQuery(Request $request)
    {
        $query = $this->service->findAllQuery();

        if ($request->filled('companyCode')) {
            $query->where('code', $request->companyCode);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
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
     * Export Excel for Fleet Company list.
     */
    public function exportExcel(Request $request)
    {
        return Excel::download(new FleetCompanyExport($request), 'Fleet-Company-Report.xlsx');
    }

    /**
     * Export PDF for Fleet Company list.
     */
    public function exportPdf(Request $request)
    {
        $rows = $this->buildFilteredQuery($request)->get();

        $companyFilterName = null;
        if ($request->filled('companyCode')) {
            $selectedCompany = FleetCompany::where('code', $request->companyCode)->first();
            $companyFilterName = $selectedCompany ? $selectedCompany->name : null;
        }

        $typeFilterName = $request->type ?? null;

        $mpdf = new Mpdf([
            'orientation' => 'P',
            'format' => 'A4',
            'tempDir' => storage_path('app/mpdf-temp'),
        ]);

        $mpdf->WriteHTML(
            view($this->view.'report.fleet-company-pdf')
                ->with('rows', $rows)
                ->with('companyFilterName', $companyFilterName)
                ->with('typeFilterName', $typeFilterName)
                ->with('startDate', $request->startDate)
                ->with('endDate', $request->endDate)
                ->render()
        );

        return $mpdf->Output('Fleet-Company-Report.pdf', 'I');
    }

    /**
     * Export Excel for Fleets under specific Fleet Company.
     */
    public function exportDetailExcel(string $id, Request $request)
    {
        $company = $this->service->getById($id);

        if (! $company) {
            return redirect()->route($this->view.'index')->with('fail', 'Data not found');
        }

        return Excel::download(new FleetCompanyDetailExport($company->code, $request), 'Fleet-Company-'.$company->code.'-Fleets.xlsx');
    }

    /**
     * Export PDF for Fleets under specific Fleet Company.
     */
    public function exportDetailPdf(string $id, Request $request)
    {
        $company = $this->service->getById($id);

        if (! $company) {
            return redirect()->route($this->view.'index')->with('fail', 'Data not found');
        }

        $query = Fleet::query()
            ->with(['brand', 'type'])
            ->where('fleetCompanyCode', $company->code)
            ->orderBy('plateNumber');

        if ($request->filled('fleetBrandCode')) {
            $query->where('fleetBrandCode', $request->fleetBrandCode);
        }

        if ($request->filled('fleetTypeCode')) {
            $query->where('fleetTypeCode', $request->fleetTypeCode);
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
            $brand = FleetBrand::where('code', $request->fleetBrandCode)->first();
            $fleetBrandName = $brand ? $brand->name : null;
        }

        $fleetTypeName = null;
        if ($request->filled('fleetTypeCode')) {
            $type = FleetType::where('code', $request->fleetTypeCode)->first();
            $fleetTypeName = $type ? $type->name : null;
        }

        $mpdf = new Mpdf([
            'orientation' => 'P',
            'format' => 'A4',
            'tempDir' => storage_path('app/mpdf-temp'),
        ]);

        $mpdf->WriteHTML(
            view($this->view.'report.fleet-company-detail-pdf')
                ->with('company', $company)
                ->with('rows', $rows)
                ->with('fleetBrandName', $fleetBrandName)
                ->with('fleetTypeName', $fleetTypeName)
                ->with('startDate', $request->startDate)
                ->with('endDate', $request->endDate)
                ->render()
        );

        return $mpdf->Output('Fleet-Company-'.$company->code.'-Fleets.pdf', 'I');
    }
}
