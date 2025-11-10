<?php

namespace App\Http\Controllers\Master;

use App\Enums\Citizenship;
use App\Enums\Gender;
use App\Http\Controllers\Controller;
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
        return view($this->view.'index')
            ->with('view', $this->view)
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
            $data = $this->service->findAll();

            return Datatables::of($data)
                ->addIndexColumn()
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
}
