<?php

namespace App\Http\Controllers\Master;

use App\Helpers\GetTokenHelper;
use App\Http\Controllers\Controller;
use App\Services\MenuService;
use App\Models\CompanySetting;
use App\Models\Master\FleetPicture;
use App\Services\Master\FleetBrandService;
use App\Services\Master\FleetService;
use App\Services\Master\FleetTypeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;


class FleetController extends Controller
{
    protected $service;
    protected $fleetBrandSvc;
    protected $fleetTypeSvc;
    protected $title;
    protected $view;
    protected $menuSvc;

    public function __construct(FleetService $fleetSvc, FleetBrandService $fleetBrandSvc, FleetTypeService $fleetTypeSvc, MenuService $menuSvc)
    {
        $this->service = $fleetSvc;
        $this->fleetBrandSvc = $fleetBrandSvc;
        $this->fleetTypeSvc = $fleetTypeSvc;
        $this->title = "Fleet";
        $this->menuSvc = $menuSvc->getByName("Fleet");
        $this->title = Auth::user()->languange == 'en' ? $this->menuSvc->name : $this->menuSvc->nama;
        $this->view = "master.fleets.";
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
        $brand = $this->fleetBrandSvc->findAll();
        $type = $this->fleetTypeSvc->findAll();


        $response = Http::get(env('API_TOTAL_KILAT') . 'deviceInfo', [
            'grant_type' => 'totalkilatgps',
            'access_token' => GetTokenHelper::getToken(),
        ]);

        $dataApi = $response->json();

        $company = CompanySetting::groupBy('id', 'name')->pluck('name')->toArray();

        $data = [];
        foreach ($dataApi[0] as $item) {
            if (in_array($item['company_name'], $company)) {
                array_push($data, $item);
            }
        }
        // return $data;

        return view($this->view . 'create')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('data', $data)
            ->with('type', $type)
            ->with('brand', $brand);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'deviceName' => 'required',
            // 'year' => 'required|integer|digits:4|min:1900|max:' . date('Y'),
            // 'fleetBrandCode' => 'required',
            'fleetTypeCode' => 'required',
            // 'frameNumber' => 'required',
            // 'engineNumber' => 'required'
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

        $brand = $this->fleetBrandSvc->findAll();
        $type = $this->fleetTypeSvc->findAll();

        if (!$data) {
            return redirect()->route($this->view . 'index')->with('fail', 'Data not found');
        }

        return view($this->view . 'edit')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('data', $data)
            ->with('type', $type)
            ->with('brand', $brand);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            // 'deviceName' => 'required',
            // 'year' => 'required|integer|digits:4|min:1900|max:' . date('Y'),
            // 'fleetBrandCode' => 'required',
            'fleetTypeCode' => 'required',
            // 'frameNumber' => 'required',
            // 'engineNumber' => 'required'
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

    public function deleteFleetPicture($id)
    {
        $data = FleetPicture::where('id', $id)->first();

        $path = "public/fleet/fleetPicture/";
        if ($data->fleetPicture) {
            Storage::delete($path . $data->fleetPicture);
        }

        $data->delete();

        return redirect()->back()->with('success', 'Delete Data Success');
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->findAll();
            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('type.name', function ($row) {
                    $type = '';

                    if (isset($row->type->name)) {
                        $type = $row->type->name;
                    }

                    return $type;
                })
                ->editColumn('vehicleRegistrationNumber', function ($row) {
                    if (isset($row->vehicleRegistrationNumber)) {
                        $imageUrl = url('storage/fleet/vehicleRegistrationNumber/' . $row->vehicleRegistrationNumber);
                        return '<img onclick="showModal(\'' . $imageUrl . '\')" style="cursor: pointer" src="' . $imageUrl . '" width="150px" height="150px" />';
                    }

                    return '';
                })
                ->editColumn('insurance', function ($row) {
                    if (isset($row->insurance)) {
                        $imageUrl = url('storage/fleet/insurance/' . $row->insurance);
                        return '<img onclick="showModal(\'' . $imageUrl . '\')" style="cursor: pointer" src="' . $imageUrl . '" width="150px" height="150px" />';
                    }


                    return '';
                })
                ->editColumn('barcode', function ($row) {
                    if (isset($row->barcode)) {
                        $imageUrl = url('storage/fleet/barcode/' . $row->barcode);
                        return '<img onclick="showModal(\'' . $imageUrl . '\')" style="cursor: pointer" src="' . $imageUrl . '" width="150px" height="150px" />';
                    }

                    return '';
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
                ->rawColumns(['action', 'barcode', 'insurance', 'type.name', 'vehicleRegistrationNumber'])
                ->toJson();
        }
    }
}
