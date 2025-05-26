<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Services\MenuService;
use App\Services\Master\CityService;
use App\Services\Master\CustomerService;
use App\Services\Master\DistrictService;
use App\Services\Master\LocationService;
use App\Services\Master\ProvinceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    protected $service;
    protected $title;
    protected $view;
    protected $menuSvc;
    protected $provinceSvc;
    protected $customerSvc;
    protected $citySvc;
    protected $districtSvc;

    public function __construct(LocationService $locationSvc, ProvinceService $provinceSvc, CustomerService $customerSvc, CityService $citySvc, DistrictService $districtSvc, MenuService $menuSvc)
    {
        $this->service = $locationSvc;
        $this->title = "Location";
        $this->view = "master.location.";
        $this->provinceSvc = $provinceSvc;
        $this->customerSvc = $customerSvc;
        $this->citySvc = $citySvc;
        $this->districtSvc = $districtSvc;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view($this->view . 'index')
            ->with('view', $this->view)
            ->with('title', $this->title);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $province = $this->provinceSvc->findAll();
        $customer = $this->customerSvc->findAll();

        return view($this->view . 'create')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('province', $province)
            ->with('customer', $customer);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            // 'customerCode' => 'required',
            // 'provinceId' => 'required',
            // 'cityId' => 'required',
            // 'districtId' => 'required',
            // 'address' => 'required',
            // 'latitude' => 'required',
            // 'longitude' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->route($this->view . 'index')->with('fail', $validator->errors()->all()[0]);
        }
        try {
            DB::beginTransaction();

            $this->service->store($request, $this->title);

            DB::commit();

            return redirect()->route($this->view . 'index')->with('success', $this->title . ' ' . __('general.data_was_save_successfully'));
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->route($this->view . 'index')->with('fail', 'Line : ' . $th->getLine() . '<br>' . $th->getMessage());
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
        $province = $this->provinceSvc->findAll();
        $customer = $this->customerSvc->findAll();
        $city = $this->citySvc->getByProvince($data->provinceId);
        $district = $this->districtSvc->getByCity($data->cityId);

        if (!$data) {
            return redirect()->route($this->view . 'index')->with('fail', 'Data not found');
        }

        return view($this->view . 'edit')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('data', $data)
            ->with('province', $province)
            ->with('customer', $customer)
            ->with('city', $city)
            ->with('district', $district);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            // 'customerCode' => 'required',
            // 'provinceId' => 'required',
            // 'cityId' => 'required',
            // 'districtId' => 'required',
            // 'address' => 'required',
            // 'latitude' => 'required',
            // 'longitude' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->route($this->view . 'index')->with('fail', $validator->errors()->all()[0]);
        }

        try {
            DB::beginTransaction();

            $this->service->update($request, $id, $this->title);

            DB::commit();

            return redirect()->route($this->view . 'index')->with('success', $this->title .  ' ' . __('general.data_was_update_succesfully'));
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->route($this->view . 'index')->with('fail', 'Line : ' . $th->getLine() . '<br>' . $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->service->destroy($id, $this->title);

        return redirect()->route($this->view . 'index')->with('success', 'Delete Data Success');
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->findAll();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('province', function ($row) {
                    $province = '';
                    if (isset($row->province->name)) {
                        $province = $row->province->name;
                    }

                    return $province;
                })
                ->addColumn('city', function ($row) {
                    $city = '';
                    if (isset($row->city->name)) {
                        $city = $row->city->name;
                    }

                    return $city;
                })

                ->addColumn('district', function ($row) {
                    $district = '';
                    if (isset($row->district->name)) {
                        $district = $row->district->name;
                    }

                    return $district;
                })
                ->addColumn('action', function ($row) {
                    $btn = '<td>
        <a href="' . route($this->view . 'edit', $row->id) . '"
           class="btn btn-icon btn-sm bg-primary-subtle me-1"
           data-bs-toggle="tooltip" title="Edit">
            <i class="mdi mdi-pencil-outline fs-14 text-primary"></i>
        </a>

        <a href="javascript:deleteData(\'' . $row->id . '\')"
           class="btn btn-icon btn-sm bg-danger-subtle"
           data-bs-toggle="tooltip" title="Delete">
            <i class="mdi mdi-delete fs-14 text-danger"></i>
        </a>
    </td>';

                    return $btn;
                })
                ->rawColumns(['action', 'province', 'city', 'district'])
                ->toJson();
        }
    }

    public function cityByProvince($id)
    {
        return $this->citySvc->getByProvince($id);
    }

    public function districtByCity($id)
    {
        return $this->districtSvc->getByCity($id);
    }
}
