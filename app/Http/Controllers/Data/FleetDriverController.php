<?php

namespace App\Http\Controllers\Data;

use App\Http\Controllers\Controller;
use App\Models\Master\Employee;
use App\Services\Data\FleetDriverService;
use App\Services\Master\FleetService;
use App\Services\Master\FleetTypeService;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class FleetDriverController extends Controller
{
    protected $service;
    protected $fleetSvc;
    protected $fleetTypeSvc;
    protected $title;
    protected $view;

    public function __construct(FleetDriverService $fleetDriverSvc, FleetService $fleetSvc, FleetTypeService $fleetTypeSvc)
    {
        $this->service = $fleetDriverSvc;
        $this->fleetSvc = $fleetSvc;
        $this->fleetTypeSvc = $fleetTypeSvc;
        $this->title = "Fleet Owner";
        $this->view = "data.fleet-owner.";
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
        $fleet = $this->fleetSvc->findAll();
        // $driver = Employee::where('positionCode', 'KP_240823034043')->get();
        $fleetType = $this->fleetTypeSvc->findAll();
        return view($this->view . 'create')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('fleetType', $fleetType)
            // ->with('driver', $driver)
            ->with('fleet', $fleet);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // 'driverCode' => 'required|unique:fleet_driver,driverCode',
            'fleetCode' => 'required|unique:fleet_driver,fleetCode',
            'vehicleRegistrationNumber' => 'required|unique:fleet_driver,vehicleRegistrationNumber',
            'vehicleRegistrationNumberExpDate' => 'required',
            'kir' => 'required|unique:fleet_driver,kir',
            'kirExpDate' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->route($this->view . 'index')->with('fail', $validator->errors()->all()[0]);
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
        // $driver = Employee::where('positionCode', 'KP_240823034043')->get();
        $fleet = $this->fleetSvc->findAll();
        $fleetType = $this->fleetTypeSvc->findAll();


        if (!$data) {
            return redirect()->route($this->view . 'index')->with('fail', 'Data not found');
        }

        return view($this->view . 'edit')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('data', $data)
            ->with('fleetType', $fleetType)
            // ->with('driver', $driver)
            ->with('fleet', $fleet);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            // 'driverCode' => 'required',
            'fleetCode' => 'required',
            'vehicleRegistrationNumber' => 'required',
            'vehicleRegistrationNumberExpDate' => 'required',
            'kir' => 'required',
            'kirExpDate' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->route($this->view . 'index')->with('fail', $validator->errors()->all()[0]);
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
}
