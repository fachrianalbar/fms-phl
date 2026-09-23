<?php

namespace App\Http\Controllers\Master;

use App\Exports\FleetExport;
use App\Http\Controllers\Controller;
use App\Models\Master\Fleet;
use App\Models\Master\FleetBrand;
use App\Models\Master\FleetCompany;
use App\Models\Master\FleetPicture;
use App\Models\Master\FleetType;
use App\Models\Operational\Order;
use App\Models\Warehouse\Maintenance;
use App\Services\Master\EmployeeService;
use App\Services\Master\FleetBrandService;
use App\Services\Master\FleetCompanyService;
use App\Services\Master\FleetService;
use App\Services\Master\FleetTypeService;
use App\Services\MenuService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;
use Yajra\DataTables\DataTables;

class FleetController extends Controller
{
    protected $service;

    protected $fleetBrandSvc;

    protected $fleetTypeSvc;

    protected $driverSvc;

    protected $fleetCompanySvc;

    protected $title;

    protected $view;

    protected $menuSvc;

    public function __construct(FleetService $fleetSvc, FleetBrandService $fleetBrandSvc, FleetTypeService $fleetTypeSvc, MenuService $menuSvc, EmployeeService $driverSvc, FleetCompanyService $fleetCompanySvc)
    {
        $this->service = $fleetSvc;
        $this->fleetBrandSvc = $fleetBrandSvc;
        $this->fleetTypeSvc = $fleetTypeSvc;
        $this->driverSvc = $driverSvc;
        $this->fleetCompanySvc = $fleetCompanySvc;
        $this->title = 'Fleet';
        $this->menuSvc = $menuSvc->getByName('Fleet');
        $this->title = Auth::user()->languange == 'en' ? $this->menuSvc->name : $this->menuSvc->nama;
        $this->view = 'master.fleets.';
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $brands = $this->fleetBrandSvc->findAll();
        $types = $this->fleetTypeSvc->findAll();
        $companies = $this->fleetCompanySvc->findAll();

        return view($this->view.'index')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('brands', $brands)
            ->with('types', $types)
            ->with('companies', $companies);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $brand = $this->fleetBrandSvc->findAll();
        $type = $this->fleetTypeSvc->findAll();
        $driver = $this->driverSvc->findDriver();
        $company = $this->fleetCompanySvc->findAll();

        return view($this->view.'create')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('type', $type)
            ->with('driver', $driver)
            ->with('company', $company)
            ->with('brand', $brand);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // 'year' => 'required|integer|digits:4|min:1900|max:' . date('Y'),
            // 'fleetBrandCode' => 'required',
            // 'fleetTypeCode' => 'required',
            // 'frameNumber' => 'required',
            // 'engineNumber' => 'required'
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
        $fleet = Fleet::where('id', $id)
            ->with(['brand', 'type', 'company', 'driver.position', 'pictures'])
            ->first();

        if (! $fleet) {
            return redirect()->route($this->view.'index')->with('fail', 'Data not found');
        }

        $totalOrders = Order::where('fleetCode', $fleet->code)->count();

        $maintenanceStats = DB::table('maintenance')
            ->leftJoin('maintenance_detail', function ($join) {
                $join->on('maintenance_detail.maintenanceCode', '=', 'maintenance.code')
                    ->whereNull('maintenance_detail.deleted_at');
            })
            ->where('maintenance.fleetCode', $fleet->code)
            ->whereNull('maintenance.deleted_at')
            ->selectRaw('COUNT(DISTINCT maintenance.code) as totalMaintenance, COALESCE(SUM(maintenance_detail.price * maintenance_detail.qty), 0) as totalCost')
            ->first();

        $totalMaintenance = $maintenanceStats->totalMaintenance ?? 0;
        $totalMaintenanceCost = $maintenanceStats->totalCost ?? 0;

        $recentMaintenances = DB::table('maintenance')
            ->where('fleetCode', $fleet->code)
            ->whereNull('deleted_at')
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get();

        $recentOrders = DB::table('order')
            ->where('fleetCode', $fleet->code)
            ->whereNull('deleted_at')
            ->orderBy('orderDate', 'desc')
            ->limit(10)
            ->get();

        return view($this->view.'show')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('fleet', $fleet)
            ->with('totalOrders', $totalOrders)
            ->with('totalMaintenance', $totalMaintenance)
            ->with('totalMaintenanceCost', $totalMaintenanceCost)
            ->with('recentMaintenances', $recentMaintenances)
            ->with('recentOrders', $recentOrders);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data = $this->service->getById($id);

        $brand = $this->fleetBrandSvc->findAll();
        $type = $this->fleetTypeSvc->findAll();
        $driver = $this->driverSvc->findDriver();
        $company = $this->fleetCompanySvc->findAll();

        if (! $data) {
            return redirect()->route($this->view.'index')->with('fail', 'Data not found');
        }

        return view($this->view.'edit')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('data', $data)
            ->with('type', $type)
            ->with('driver', $driver)
            ->with('company', $company)
            ->with('brand', $brand);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            // 'year' => 'required|integer|digits:4|min:1900|max:' . date('Y'),
            // 'fleetBrandCode' => 'required',
            // 'fleetTypeCode' => 'required',
            // 'frameNumber' => 'required',
            // 'engineNumber' => 'required'
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

    public function deleteFleetPicture($id)
    {
        $data = FleetPicture::where('id', $id)->first();

        $path = 'public/fleet/fleetPicture/';
        if ($data->fleetPicture) {
            Storage::delete($path.$data->fleetPicture);
        }

        $data->delete();

        return redirect()->back()->with('success', 'Delete Data Success');
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $query = $this->buildFilteredQuery($request);

            return Datatables::of($query)
                ->addIndexColumn()
                ->editColumn('plateNumber', function ($row) {
                    return '<span class="font-monospace fw-bold text-dark">'.$row->plateNumber.'</span>';
                })
                ->editColumn('code', function ($row) {
                    return '<span class="font-monospace text-muted" style="font-size: 11.5px;">'.$row->code.'</span>';
                })
                ->editColumn('vehicleRegistrationDueDate', function ($row) {
                    if (! $row->vehicleRegistrationDueDate) {
                        return '-';
                    }

                    return '<span class="font-monospace text-dark">'.\Carbon\Carbon::parse($row->vehicleRegistrationDueDate)->format('d/m/Y').'</span>';
                })
                ->editColumn('company.name', function ($row) {
                    return $row->company?->name ?? '-';
                })
                ->editColumn('company.type', function ($row) {
                    $type = $row->company?->type;
                    if (! $type) {
                        return '-';
                    }

                    $badgeClass = strtolower($type) === 'internal'
                        ? 'bg-primary-subtle text-primary'
                        : 'bg-warning-subtle text-warning';

                    return '<span class="badge '.$badgeClass.' px-2 py-1" style="border-radius: 6px; font-weight: 600; font-size: 11px;">'.$type.'</span>';
                })
                ->editColumn('company.address', function ($row) {
                    return $row->company?->address ?? '-';
                })
                ->editColumn('brand.name', function ($row) {
                    return $row->brand?->name
                        ? '<span class="badge bg-light text-dark px-2 py-1 border" style="border-radius: 6px; font-weight: 600; font-size: 11px;">'.$row->brand->name.'</span>'
                        : '-';
                })
                ->editColumn('type.name', function ($row) {
                    return $row->type?->name ?? '-';
                })
                ->editColumn('frameNumber', function ($row) {
                    return $row->frameNumber ? '<span class="font-monospace text-muted" style="font-size: 11.5px;">'.$row->frameNumber.'</span>' : '-';
                })
                ->editColumn('engineNumber', function ($row) {
                    return $row->engineNumber ? '<span class="font-monospace text-muted" style="font-size: 11.5px;">'.$row->engineNumber.'</span>' : '-';
                })
                ->addColumn('action', function ($row) {
                    $showUrl = route($this->view.'show', $row->id);
                    $editUrl = route($this->view.'edit', $row->id);
                    $escapedId = e($row->id);

                    $btn = '<div class="d-flex align-items-center justify-content-center gap-1">'
                        .'<a href="'.$showUrl.'" class="btn btn-icon btn-sm bg-info-subtle" data-bs-toggle="tooltip" title="Detail">'
                        .'<i class="mdi mdi-eye-outline fs-14 text-info"></i></a>'
                        .'<a href="'.$editUrl.'" class="btn btn-icon btn-sm bg-primary-subtle" data-bs-toggle="tooltip" title="Edit">'
                        .'<i class="mdi mdi-pencil-outline fs-14 text-primary"></i></a>'
                        .'<button type="button" class="btn btn-icon btn-sm bg-danger-subtle" onclick="deleteData(\''.$escapedId.'\')" data-bs-toggle="tooltip" title="Delete">'
                        .'<i class="mdi mdi-delete fs-14 text-danger"></i></button>'
                        .'<input class="form-check-input fleet-checkbox ms-1" type="checkbox" name="fleet[]" data-id="'.$escapedId.'" value="'.$escapedId.'" style="cursor: pointer;">'
                        .'</div>';

                    return $btn;
                })
                ->rawColumns(['action', 'plateNumber', 'code', 'vehicleRegistrationDueDate', 'company.name', 'company.type', 'company.address', 'brand.name', 'type.name', 'frameNumber', 'engineNumber'])
                ->toJson();
        }
    }

    /**
     * Build filtered query for Fleet list.
     */
    private function buildFilteredQuery(Request $request)
    {
        $query = $this->service->findAllQuery();

        if ($request->filled('fleetBrandCode')) {
            $query->where('fleetBrandCode', $request->fleetBrandCode);
        }

        if ($request->filled('fleetTypeCode')) {
            $query->where('fleetTypeCode', $request->fleetTypeCode);
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

        return $query;
    }

    /**
     * Export Excel for Fleet list.
     */
    public function exportExcel(Request $request)
    {
        return Excel::download(new FleetExport($request), 'Fleet-Report.xlsx');
    }

    /**
     * Export PDF for Fleet list.
     */
    public function exportPdf(Request $request)
    {
        $rows = $this->buildFilteredQuery($request)->get();

        $brandFilterName = null;
        if ($request->filled('fleetBrandCode')) {
            $b = FleetBrand::where('code', $request->fleetBrandCode)->first();
            $brandFilterName = $b ? $b->name : null;
        }

        $typeFilterName = null;
        if ($request->filled('fleetTypeCode')) {
            $t = FleetType::where('code', $request->fleetTypeCode)->first();
            $typeFilterName = $t ? $t->name : null;
        }

        $companyFilterName = null;
        if ($request->filled('fleetCompanyCode')) {
            $c = FleetCompany::where('code', $request->fleetCompanyCode)->first();
            $companyFilterName = $c ? $c->name : null;
        }

        $mpdf = new Mpdf([
            'orientation' => 'L',
            'format' => 'A4',
            'tempDir' => storage_path('app/mpdf-temp'),
        ]);

        $mpdf->WriteHTML(
            view($this->view.'report.fleet-pdf')
                ->with('rows', $rows)
                ->with('brandFilterName', $brandFilterName)
                ->with('typeFilterName', $typeFilterName)
                ->with('companyFilterName', $companyFilterName)
                ->with('startDate', $request->startDate)
                ->with('endDate', $request->endDate)
                ->render()
        );

        return $mpdf->Output('Fleet-Report.pdf', 'I');
    }

    /**
     * Export PDF for Fleet individual profile.
     */
    public function exportDetailPdf(string $id)
    {
        $fleet = Fleet::where('id', $id)
            ->with(['brand', 'type', 'company', 'driver.position', 'pictures'])
            ->first();

        if (! $fleet) {
            return redirect()->route($this->view.'index')->with('fail', 'Data not found');
        }

        $totalOrders = Order::where('fleetCode', $fleet->code)->count();

        $maintenanceStats = DB::table('maintenance')
            ->leftJoin('maintenance_detail', function ($join) {
                $join->on('maintenance_detail.maintenanceCode', '=', 'maintenance.code')
                    ->whereNull('maintenance_detail.deleted_at');
            })
            ->where('maintenance.fleetCode', $fleet->code)
            ->whereNull('maintenance.deleted_at')
            ->selectRaw('COUNT(DISTINCT maintenance.code) as totalMaintenance, COALESCE(SUM(maintenance_detail.price * maintenance_detail.qty), 0) as totalCost')
            ->first();

        $totalMaintenance = $maintenanceStats->totalMaintenance ?? 0;
        $totalMaintenanceCost = $maintenanceStats->totalCost ?? 0;

        $mpdf = new Mpdf([
            'orientation' => 'P',
            'format' => 'A4',
            'tempDir' => storage_path('app/mpdf-temp'),
        ]);

        $mpdf->WriteHTML(
            view($this->view.'report.fleet-detail-pdf')
                ->with('fleet', $fleet)
                ->with('totalOrders', $totalOrders)
                ->with('totalMaintenance', $totalMaintenance)
                ->with('totalMaintenanceCost', $totalMaintenanceCost)
                ->render()
        );

        return $mpdf->Output('Fleet-'.$fleet->plateNumber.'.pdf', 'I');
    }

    public function fleetDriver($code)
    {
        $fleetDriver = Fleet::where('code', $code)->first();

        return $fleetDriver->driverCode;
    }

    public function destroyMultiple(Request $request)
    {
        try {
            DB::beginTransaction();

            $this->service->destroyMultiple($request, $this->title);

            DB::commit();

            return redirect()->route($this->view.'index')->with('success', 'Delete Data Success');
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->route($this->view.'index')->with('fail', 'Line : '.$th->getLine().'<br>'.$th->getMessage());
        }
    }
}
