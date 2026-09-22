<?php

namespace App\Http\Controllers\Master;

use App\Enums\Citizenship;
use App\Enums\Gender;
use App\Http\Controllers\Controller;
use App\Models\Master\Employee;
use App\Services\Bank\BankAccountService;
use App\Services\Master\CityService;
use App\Services\Master\DistrictService;
use App\Services\Master\EmployeeService;
use App\Services\Master\PositionService;
use App\Services\Master\ProvinceService;
use App\Services\MenuService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class EmployeeController extends Controller
{
    protected $service;

    protected $title;

    protected $view;

    protected $menuSvc;

    protected $positionSvc;

    protected $provinceSvc;

    protected $citySvc;

    protected $districtSvc;

    protected $bankSvc;

    public function __construct(EmployeeService $employeeSvc, PositionService $positionSvc, ProvinceService $provinceSvc, CityService $citySvc, DistrictService $districtSvc, MenuService $menuSvc, BankAccountService $bankSvc)
    {
        $this->service = $employeeSvc;
        $this->title = 'Employee';
        $this->view = 'master.employee.';
        $this->positionSvc = $positionSvc;
        $this->provinceSvc = $provinceSvc;
        $this->citySvc = $citySvc;
        $this->districtSvc = $districtSvc;
        $this->bankSvc = $bankSvc;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $positions = $this->positionSvc->findAll();
        $counts = [
            'total' => Employee::count(),
            'active' => Employee::where('status', 1)->count(),
            'inactive' => Employee::where('status', 0)->count(),
            'driver' => Employee::whereIn('positionCode', ['KP_240823034043', 'FPS250612034049'])->count(),
            'driver_active' => Employee::whereIn('positionCode', ['KP_240823034043', 'FPS250612034049'])->where('status', 1)->count(),
            'driver_inactive' => Employee::whereIn('positionCode', ['KP_240823034043', 'FPS250612034049'])->where('status', 0)->count(),
        ];

        return view($this->view.'index')
            ->with('view', $this->view)
            ->with('positions', $positions)
            ->with('counts', $counts)
            ->with('title', $this->title);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $position = $this->positionSvc->findAll();
        $province = $this->provinceSvc->findAll();
        $bank = $this->bankSvc->findAll();

        return view($this->view.'create')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('province', $province)
            ->with('bank', $bank)
            ->with('gender', Gender::cases())
            ->with('citizenship', Citizenship::cases())
            ->with('position', $position);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'positionCode' => 'required',
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
        $position = $this->positionSvc->findAll();
        $province = $this->provinceSvc->findAll();
        $city = $this->citySvc->getByProvince($data->provinceId);
        $district = $this->districtSvc->getByCity($data->cityId);

        if (! $data) {
            return redirect()->route($this->view.'index')->with('fail', 'Data not found');
        }

        $bank = $this->bankSvc->findAll();

        return view($this->view.'edit')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('data', $data)
            ->with('position', $position)
            ->with('gender', Gender::cases())
            ->with('citizenship', Citizenship::cases())
            ->with('province', $province)
            ->with('city', $city)
            ->with('bank', $bank)
            ->with('district', $district);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'positionCode' => 'required',
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
            $error = [
                'line : ' => $th->getLine(),
                'message : ' => $th->getMessage(),
            ];

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
            $filters = [
                'positionCode' => $request->get('positionCode'),
                'status' => $request->get('status'),
            ];

            $data = $this->service->findAllQuery($filters);

            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('code', function ($row) {
                    return '<span class="fw-semibold text-dark font-monospace fs-12">'.e($row->code ?? '-').'</span>';
                })
                ->editColumn('name', function ($row) {
                    $phone = $row->phone ? '<div class="text-muted fs-11 mt-1"><i class="mdi mdi-phone-outline me-1"></i>'.e($row->phone).'</div>' : '';
                    return '<div class="fw-bold text-dark fs-13">'.e($row->name).'</div>'.$phone;
                })
                ->addColumn('position_badge', function ($row) {
                    $posName = optional($row->position)->name ?? '-';
                    $isDriver = in_array($row->positionCode, ['KP_240823034043', 'FPS250612034049']);

                    if ($isDriver) {
                        return '<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1"><i class="mdi mdi-steering me-1"></i>'.e($posName).'</span>';
                    } elseif ($row->positionCode === 'FPS250612034043') {
                        return '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1"><i class="mdi mdi-wrench me-1"></i>'.e($posName).'</span>';
                    } else {
                        return '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1"><i class="mdi mdi-office-building me-1"></i>'.e($posName).'</span>';
                    }
                })
                ->addColumn('status_badge', function ($row) {
                    $isAktif = (int) $row->status === 1;
                    $checked = $isAktif ? 'checked' : '';
                    $labelClass = $isAktif ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle';
                    $labelText = $isAktif ? 'Aktif' : 'Nonaktif';
                    $icon = $isAktif ? 'mdi-check-circle' : 'mdi-close-circle';

                    return '<div class="d-flex align-items-center gap-2">
                        <div class="form-check form-switch m-0" style="min-height: 1.2rem;">
                            <input class="form-check-input status-toggle-switch cursor-pointer" type="checkbox" role="switch"
                                data-id="'.$row->id.'" data-name="'.e($row->name).'" data-status="'.$row->status.'" '.$checked.'
                                title="Klik untuk '.($isAktif ? 'nonaktifkan' : 'aktifkan').'">
                        </div>
                        <span class="badge '.$labelClass.' px-2 py-1 fs-11">
                            <i class="mdi '.$icon.' me-1"></i>'.$labelText.'
                        </span>
                    </div>';
                })
                ->addColumn('action', function ($row) {
                    $isAktif = (int) $row->status === 1;
                    $toggleBtnClass = $isAktif ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success';
                    $toggleIcon = $isAktif ? 'mdi-account-off-outline' : 'mdi-account-check-outline';
                    $toggleTitle = $isAktif ? 'Nonaktifkan Supir' : 'Aktifkan Supir';

                    $btn = '<div class="d-flex align-items-center gap-1">
                        <button type="button" class="btn btn-icon btn-sm '.$toggleBtnClass.'" 
                            onclick="toggleStatus(\''.$row->id.'\', \''.e($row->name).'\', '.$row->status.')"
                            data-bs-toggle="tooltip" title="'.$toggleTitle.'">
                            <i class="mdi '.$toggleIcon.' fs-14"></i>
                        </button>
                        <a href="'.route($this->view.'edit', $row->id).'"
                           class="btn btn-icon btn-sm bg-primary-subtle text-primary"
                           data-bs-toggle="tooltip" title="Edit">
                            <i class="mdi mdi-pencil-outline fs-14"></i>
                        </a>
                        <a href="javascript:deleteData(\''.$row->id.'\')"
                           class="btn btn-icon btn-sm bg-danger-subtle text-danger"
                           data-bs-toggle="tooltip" title="Delete">
                            <i class="mdi mdi-delete fs-14"></i>
                        </a>
                    </div>';

                    return $btn;
                })
                ->rawColumns(['code', 'name', 'position_badge', 'status_badge', 'action'])
                ->toJson();
        }
    }

    /**
     * Aktifkan atau nonaktifkan supir/karyawan via AJAX.
     */
    public function toggleStatus(Request $request, string $id)
    {
        try {
            $employee = $this->service->toggleStatus($id, $this->title);

            if (! $employee) {
                return response()->json(['success' => false, 'message' => 'Data supir/karyawan tidak ditemukan.'], 404);
            }

            $isAktif = (int) $employee->status === 1;
            $statusText = $isAktif ? 'Aktif' : 'Nonaktif';
            $name = $employee->name ?? 'Supir';

            return response()->json([
                'success' => true,
                'status' => $employee->status,
                'status_text' => $statusText,
                'message' => "Status {$name} berhasil diubah menjadi {$statusText}.",
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status: '.$th->getMessage(),
            ], 500);
        }
    }
}
