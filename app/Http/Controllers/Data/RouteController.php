<?php

namespace App\Http\Controllers\Data;

use App\Enums\CostComponentType;
use App\Http\Controllers\Controller;
use App\Services\Data\RouteService;
use App\Services\Master\CostComponentService;
use App\Services\Master\CustomerService;
use App\Services\Master\FleetTypeService;
use App\Services\Master\LocationService;
use App\Services\Master\RouteTypeService;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RouteController extends Controller
{
    protected $service;
    protected $title;
    protected $view;
    protected $customerSvc;
    protected $locationSvc;
    protected $fleetTypeSvc;
    protected $costComponentSvc;
    protected $routeTypeSvc;

    public function __construct(
        RouteService $routeSvc,
        CustomerService $customerSvc,
        LocationService $locationSvc,
        FleetTypeService $fleetTypeSvc,
        CostComponentService $costComponentSvc,
        RouteTypeService $routeTypeSvc
    ) {
        $this->service = $routeSvc;
        $this->title = "Route";
        $this->view = "data.route.";
        $this->customerSvc = $customerSvc;
        $this->locationSvc = $locationSvc;
        $this->fleetTypeSvc = $fleetTypeSvc;
        $this->costComponentSvc = $costComponentSvc;
        $this->routeTypeSvc = $routeTypeSvc;
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
        $customer = $this->customerSvc->findAll();
        $type = $this->fleetTypeSvc->findAll();
        $routeType = $this->routeTypeSvc->findAll();
        $location = $this->locationSvc->findAll();

        return view($this->view . 'create')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('customer', $customer)
            ->with('location', $location)
            ->with('routeType', $routeType)
            ->with('type', $type);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'customerCode' => 'required',
            'originLocationCode' => 'required',
            'destinationLocationCode' => 'required',
            // 'fleetTypeCode' => 'required',
            'price' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->route($this->view . 'index')->with('fail', $validator->errors()->all()[0]);
        }

        if ($request->originLocationCode === $request->destinationLocationCode) {
            return redirect()->route($this->view . 'index')->with('fail', 'Origin and destination location cannot be same');
        }

        try {
            DB::beginTransaction();

            $this->service->store($request, $this->title);

            DB::commit();

            return redirect()->route($this->view . 'index')->with('success', $this->title . ' data was save succesfully');
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

        if (!$data) {
            return redirect()->route($this->view . 'index')->with('fail', 'Data not found');
        }

        $customer = $this->customerSvc->findAll();
        $type = $this->fleetTypeSvc->findAll();
        // $location = $this->locationSvc->getByCustomer($data->customerCode);
        $location = $this->locationSvc->findAll();
        $component = $this->costComponentSvc->findAll();
        $routeType = $this->routeTypeSvc->findAll();


        return view($this->view . 'edit')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('customer', $customer)
            ->with('type', $type)
            ->with('location', $location)
            ->with('component', $component)
            ->with('routeType', $routeType)
            ->with('componentType', CostComponentType::cases())
            ->with('data', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if ($request->page == 'cost-component') {
            $this->costComponentSvc->store($request, 'Cost Component');
            return redirect()->route($this->view . 'edit', $id)->with('success', 'Data was saved successfully');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'customerCode' => 'required',
            'originLocationCode' => 'required',
            'destinationLocationCode' => 'required',
            // 'fleetTypeCode' => 'required',
            'price' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->route($this->view . 'index')->with('fail', $validator->errors()->all()[0]);
        }

        if ($request->originLocationCode === $request->destinationLocationCode) {
            return redirect()->route($this->view . 'index')->with('fail', 'Origin and destination location cannot be same');
        }


        try {
            DB::beginTransaction();

            $this->service->update($request, $id, $this->title);

            DB::commit();

            return redirect()->route($this->view . 'index')->with('success', $this->title .  ' data was update succesfully');
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
                ->addColumn('price', function ($row) {
                    return 'Rp ' .  number_format($row->price, 0, ',', '.');
                })
                ->addColumn('action', function ($row) {
                    $btn = '<ul class="action">
                                        <li class="edit"> <a href="' . route($this->view . 'edit', $row->id) . '"><i class="icon-pencil-alt"></i></a></li>
                                        <li class="delete"><a href="javascript:deleteData(\'' . $row->id . '\')"><i class="icon-trash"></i></a></li>
                                    </ul>';

                    return $btn;
                })
                ->rawColumns(['action'])
                ->toJson();
        }
    }

    public function locationByCustomer($code)
    {
        $customer = $this->customerSvc->getByCode($code);

        return $this->locationSvc->getByCustomer($customer->code);
    }
}
