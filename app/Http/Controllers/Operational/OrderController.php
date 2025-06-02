<?php

namespace App\Http\Controllers\Operational;

use App\Enums\OrderCostType;
use App\Exports\OrderReport;
use App\Helpers\FilterHelper;
use App\Http\Controllers\Controller;
use App\Services\MenuService;
use App\Models\Data\Route;
use App\Models\Data\TonaseBonus;
use App\Models\Master\CostComponent;
use App\Models\Master\Location;
use App\Models\Operational\OrderCost;
use App\Services\Data\FleetDriverService;
use App\Services\Master\CustomerService;
use App\Services\Master\EmployeeService;
use App\Services\Master\FleetService;
use App\Services\Master\FleetTypeService;
use App\Services\Master\LocationService;
use App\Services\Master\MaterialService;
use App\Services\Master\OrderTypeService;
use App\Services\Master\RouteTypeService;
use App\Services\Operational\OrderService;
use App\Services\CompanySettingService;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;


class OrderController extends Controller
{
    protected $service;
    protected $title;
    protected $view;
    protected $menuSvc;
    protected $materialSvc;
    protected $fleetDriverSvc;
    protected $customerSvc;
    protected $orderTypeSvc;
    protected $routeTypeSvc;
    protected $fleetTypeSvc;
    protected $driverSvc;
    protected $totalPrice;
    protected $fleetSvc;
    protected $locationSvc;
    protected $companySvc;

    public function __construct(
        OrderService $orderSvc,
        MaterialService $materialSvc,
        FleetDriverService $fleetDriverSvc,
        CustomerService $customerSvc,
        OrderTypeService $orderTypeSvc,
        RouteTypeService $routeTypeSvc,
        FleetTypeService $fleetTypeSvc,
        EmployeeService $driverSvc,
        FleetService $fleetSvc,
        LocationService $locationSvc,
        CompanySettingService $companySvc,
        MenuService $menuSvc

    ) {
        $this->service = $orderSvc;
        $this->title = "Order";
        $this->view = "operational.order.";
        $this->materialSvc = $materialSvc;
        $this->fleetDriverSvc = $fleetDriverSvc;
        $this->customerSvc = $customerSvc;
        $this->orderTypeSvc = $orderTypeSvc;
        $this->routeTypeSvc = $routeTypeSvc;
        $this->fleetTypeSvc = $fleetTypeSvc;
        $this->driverSvc = $driverSvc;
        $this->fleetSvc = $fleetSvc;
        $this->locationSvc = $locationSvc;
        $this->companySvc = $companySvc;
        $this->totalPrice = 0;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customer = $this->customerSvc->findAll();
        $fleetType = $this->fleetTypeSvc->findAll();
        $location = $this->locationSvc->findAll();
        $driver = $this->driverSvc->findAll();
        $fleet = $this->fleetSvc->findAll();
        $orderType = $this->orderTypeSvc->findAll();


        return view($this->view . 'index')
            ->with('view', $this->view)
            ->with('fleet', $fleet)
            ->with('customer', $customer)
            ->with('driver', $driver)
            ->with('location', $location)
            ->with('fleetType', $fleetType)
            ->with('orderType', $orderType)
            ->with('title', $this->title);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $material = $this->materialSvc->findAll();
        $fleetDriver = $this->fleetDriverSvc->findAll();
        $customer = $this->customerSvc->findAll();
        $orderType = $this->orderTypeSvc->findAll();
        $routeType = $this->routeTypeSvc->findAll();
        $fleetType = $this->fleetTypeSvc->findAll();
        $driver = $this->driverSvc->findAll();
        $fleet = $this->fleetSvc->findAll();
        $company = $this->companySvc->findAll();
        $component = CostComponent::whereIn('type', ['Mandatory', 'Non Mandatory'])->get();

        return view($this->view . 'create')
            ->with('view', $this->view)
            ->with('material', $material)
            ->with('fleetDriver', $fleetDriver)
            ->with('customer', $customer)
            ->with('orderType', $orderType)
            ->with('routeType', $routeType)
            ->with('fleetType', $fleetType)
            ->with('driver', $driver)
            ->with('orderCost', OrderCostType::cases())
            ->with('component', $component)
            ->with('fleet', $fleet)
            ->with('company', $company)
            ->with('title', $this->title);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'code' => 'required',
            'shipmentNumber' => ['required', Rule::unique('order', 'shipmentNumber')->whereNull('deleted_at')],
            // 'salesOrder' => ['required', Rule::unique('order', 'salesOrder')],
            'orderDate' => 'required|date',
        ]);
        if ($validator->fails()) {
            return redirect()->route($this->view . 'index')->with('fail', $validator->errors()->all()[0]);
        }
        try {
            DB::beginTransaction();

            // dd($request->code);

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
        $data = $this->service->getById($id);


        return response()->json($data);
    }

    public function showOrder(string $id)
    {
        $data = $this->service->getById($id);
        $route = Route::where('code', $data->routeCode)->first();
        $component = CostComponent::whereIn('type', ['Mandatory', 'Non Mandatory'])->get();
        $cost = OrderCost::where('orderCode', $data->code)->get();
        $totalPrice = 0;
        $allowance = 0;

        if (isset($data->route->routeDetail)) {
            $dataRoute = $data->route->routeDetail;

            foreach ($dataRoute as $item) {
                if ($item->costComponent->type == 'Allowance') {
                    if ($item->amount != 0) {
                        $allowance = $item->amount;
                    }

                    if ($item->percentage) {
                        $route = Route::where('code', $item->routeCode)->first();

                        $allowance = $route->price * ($item->percentage / 100);
                    }
                }
            }
            $totalPrice = $allowance;
        }

        $bonus = TonaseBonus::where('min', '<=', $data->qty)->where('max', '>=', $data->qty)->first();

        if ($bonus) {
            $totalPrice += $bonus->value;
        }

        $addCost = 0;
        if (isset($data->cost)) {
            foreach ($data->cost as $item) {
                $addCost += $item->nominal;
            }
        }

        $totalPrice += $addCost;

        return view($this->view . 'show')
            ->with('view', $this->view)
            ->with('data', $data)
            ->with('route', $route)
            ->with('component', $component)
            ->with('orderCost', OrderCostType::cases())
            ->with('cost', $cost)
            ->with('title', $this->title);
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

        $material = $this->materialSvc->findAll();
        $fleetDriver = $this->fleetDriverSvc->findAll();
        $customer = $this->customerSvc->findAll();
        $orderType = $this->orderTypeSvc->findAll();
        $routeType = $this->routeTypeSvc->findAll();
        $fleetType = $this->fleetTypeSvc->findAll();
        $driver = $this->driverSvc->findAll();
        $company = $this->companySvc->findAll();
        $route = Route::where('code', $data->routeCode)->first();
        $origin = Route::where('customerCode', $route->customerCode)->where('routeTypeCode', $route->routeTypeCode)->get();
        $destination = Route::where('customerCode', $route->customerCode)->where('routeTypeCode', $route->routeTypeCode)->where('originLocationCode', $route->originLocationCode)->get();
        $cost = OrderCost::where('orderCode', $data->code)->get();
        $fleet = $this->fleetSvc->findAll();
        $component = CostComponent::whereIn('type', ['Mandatory', 'Non Mandatory'])->get();


        return view($this->view . 'edit')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('material', $material)
            ->with('fleetDriver', $fleetDriver)
            ->with('customer', $customer)
            ->with('orderType', $orderType)
            ->with('routeType', $routeType)
            ->with('fleetType', $fleetType)
            ->with('driver', $driver)
            ->with('origin', $origin)
            ->with('destination', $destination)
            ->with('route', $route)
            ->with('cost', $cost)
            ->with('orderCost', OrderCostType::cases())
            ->with('component', $component)
            ->with('fleet', $fleet)
            ->with('company', $company)
            ->with('data', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $this->service->getById($id);

        $validator = Validator::make($request->all(), [
            'shipmentNumber' => ['required', Rule::unique('order', 'shipmentNumber')->ignore($data->id)->whereNull('deleted_at')],
            // 'salesOrder' => ['required', Rule::unique('order', 'salesOrder')->ignore($data->id)],
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
            $data = $this->service->datatable();

            // Definisikan kolom filter dengan alias
            $filters = [
                'fleet_plateNumber' => $request->plateNumber,
                'customer_name' => $request->customerName,
                'driver_name' => $request->driverName,
                'fleetType_name' => $request->fleetTypeName,
                'shipmentNumber' => $request->shipmentNumber,
                // 'origin' => $request->origin,
                'destination' => $request->destination,
                'orderTypeCode' => $request->orderTypeCode,

            ];

            // Hubungkan alias ke relasi dan kolom yang sesuai
            $relations = [
                'fleet_plateNumber' => 'fleet.plateNumber',
                'customer_name' => 'customer.name',
                'driver_name' => 'driver.name',
                'fleetType_name' => 'fleet.type.name',
                // 'origin' => 'route.originLocation.name',
                'destination' => 'route.destinationLocation.name'
            ];

            $dateFilters = [
                'orderDate' => [
                    'start' => $request->startDate,
                    'end' => $request->endDate,
                ],
            ];

            $data = FilterHelper::applyFilters($data, $filters, $relations, $dateFilters);

            return Datatables::of($data)
                ->addIndexColumn()

                ->editColumn('fleet.plateNumber', function ($row) {
                    $fleet = '';

                    if (isset($row->fleet->plateNumber)) {
                        $fleet = $row->fleet->plateNumber;
                    }

                    return $fleet;
                })

                ->editColumn('customer.name', function ($row) {
                    $customer = '';

                    if (isset($row->customer->name)) {
                        $customer = $row->customer->name;
                    }

                    return $customer;
                })

                ->editColumn('driver.name', function ($row) {
                    $driver = '';

                    if (isset($row->driver->name)) {
                        $driver = $row->driver->name;
                    }

                    return $driver;
                })

                ->editColumn('fleet.type.name', function ($row) {
                    $fleetType = '';

                    if (isset($row->fleet->type->name)) {
                        $fleetType = $row->fleet->type->name;
                    }

                    return $fleetType;
                })

                ->editColumn('material.name', function ($row) {
                    $material = '';

                    if (isset($row->material->name)) {
                        $material = $row->material->name;
                    }

                    return $material;
                })

                ->editColumn('status', function ($row) {
                    $status = '';

                    if (isset($row->orderStatus->name)) {
                        $status = Auth::user()->languange == 'id' ? $row->orderStatus->nama : $row->orderStatus->name;
                    }

                    return $status;
                })

                ->editColumn('route.destinationLocation.name', function ($row) {
                    $destination = '';

                    if (isset($row->route->destinationLocation->name)) {
                        $destination = $row->route->destinationLocation->name;
                    }

                    return $destination;
                })

                ->addColumn('cost', function ($row) {
                    $allowance = 0;

                    if (isset($row->route->routeDetail)) {
                        $data = $row->route->routeDetail;

                        foreach ($data as $item) {
                            if ($item->costComponent->type == 'Allowance') {
                                if ($item->amount != 0) {
                                    $allowance = $item->amount;
                                }

                                if ($item->percentage) {
                                    $route = Route::where('code', $item->routeCode)->first();

                                    $allowance = $route->price * ($item->percentage / 100);
                                }
                            }
                        }
                        $this->totalPrice = $allowance;
                    }

                    return '' . number_format($allowance, 0, ',', '.');
                })
                ->addColumn('tonase', function ($row) {
                    if (isset($row->route->routeTypeCode)) {
                        if ($row->route->routeTypeCode == 'TONASE') {
                            return '' . number_format($row->route->price, 0, ',', '.');
                        }
                    }
                    return '' . 0;
                })

                ->addColumn('bonus', function ($row) {
                    $bonus = TonaseBonus::where('min', '<=', $row->qty)->where('max', '>=', $row->qty)->first();

                    if ($bonus) {
                        $this->totalPrice += $bonus->value;
                        return '' . number_format($bonus->value, 0, ',', '.');
                    }
                    return '' . 0;
                })
                ->addColumn('addCost', function ($row) {
                    $cost = 0;
                    if (isset($row->cost)) {
                        foreach ($row->cost as $item) {
                            $cost += $item->nominal;
                        }
                    }
                    $this->totalPrice += $cost;
                    return '' . number_format($cost, 0, ',', '.');
                })
                ->addColumn('totalPrice', function () {
                    return '' . number_format($this->totalPrice, 0, ',', '.');
                })


                ->addColumn('action', function ($row) {

                    $note = '';
                    $finishOrder = '';



                    if ($row->notes) {
                        $note = '<a href="javascript:showModal(\'' . $row->id . '\')"
                                class="btn btn-icon btn-sm bg-info-subtle me-1"
                                data-bs-toggle="tooltip" title="Note">
                                    <i class="mdi mdi-text-box fs-14 text-info"></i>
                                </a>';
                    }

                    if ($row->status == 0) {
                        $finishOrder =  '<a href="javascript:finishOrder(\'' . $row->id . '\')"
                                class="btn btn-icon btn-sm bg-success-subtle me-1"
                                data-bs-toggle="tooltip" title="Finish Order">
                                    <i class="mdi mdi-check-bold fs-14 text-success"></i>
                                </a>';
                    }
                    $btn = '<td>
                      <a href="' . route($this->view . 'show-order', $row->id) . '"
                        class="btn btn-icon btn-sm bg-success-subtle me-1"
                        data-bs-toggle="tooltip" title="Show">
                            <i class="mdi mdi-eye fs-14 text-success"></i>
                        </a>
                        <a href="' . route($this->view . 'edit', $row->id) . '"
                        class="btn btn-icon btn-sm bg-primary-subtle me-1"
                        data-bs-toggle="tooltip" title="Edit">
                            <i class="mdi mdi-pencil-outline fs-14 text-primary"></i>
                        </a>

                        <a href="javascript:deleteData(\'' . $row->id . '\')"
                        class="btn btn-icon btn-sm bg-danger-subtle me-1"
                        data-bs-toggle="tooltip" title="Delete">
                            <i class="mdi mdi-delete fs-14 text-danger"></i>
                        </a>
                        ' . $note . '    
                        ' . $finishOrder . '                                   
                    </td>';

                    return $btn;
                })
                ->editColumn('orderDate', function ($row) {
                    return Carbon::parse($row->orderDate)->format('d-m-Y');
                })
                ->rawColumns(['action', 'status', 'fleet.type.name', 'fleet.plateNumber', 'customer.name', 'route.destinationLocation.name', 'material.name', 'driver.name', 'cost', 'bonus', 'tonase', 'addCost', 'totalPrice'])
                ->toJson();
        }
    }

    public function finishOrder(string $id)
    {
        $data = $this->service->getById($id);

        if (!$data) {
            return redirect()->route($this->view . 'index')->with('fail', 'Data not found');
        }

        $this->service->finishOrder($id);

        return redirect()->route($this->view . 'index')->with('success',  __('menu_order.finish_order_was_successfull'));
    }

    public function checkNullRelations()
    {
        // Ambil semua data order
        $orders = $this->service->findAll();

        $nullData = [];

        foreach ($orders as $order) {
            $nullRelations = [];

            // Cek relasi null
            if (!$order->fleet) {
                $nullRelations[] = 'Plate Number';
            }
            if (!$order->fleet->type) {
                $nullRelations[] = 'Fleet Type';
            }
            if (!$order->driver) {
                $nullRelations[] = 'Driver';
            }
            if (!$order->customer) {
                $nullRelations[] = 'Customer';
            }
            if (!$order->route->destinationLocation) {
                $nullRelations[] = 'Destination';
            }
            if (!$order->material) {
                $nullRelations[] = 'material';
            }
            if (!$order->route) {
                $nullRelations[] = 'route';
            }


            // Jika ada relasi null, tambahkan ke array hasil
            if (count($nullRelations) > 0) {
                $nullData[] = [
                    'shipmentNumber' => $order->shipmentNumber,
                    'nullRelations' => implode(', ', $nullRelations)
                ];
            }
        }

        // Return hasil pengecekan
        if (count($nullData) > 0) {
            return response()->json([
                'success' => true,
                'data' => $nullData
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No null data found.'
        ]);
    }


    public function excelOrder(Request $request)
    {
        return Excel::download(new OrderReport($request), 'Order-Report.xlsx');
    }


    public function deleteCost($id)
    {
        $cost = OrderCost::where('id', $id)->with(['order'])->first();
        $cost->delete();

        return redirect()->route($this->view . 'edit', $cost->order->id)->with('success', 'Delete Data Success');
    }

    public function originCustomer($customerCode, $routeTypeCode)
    {
        $originLocationArr = Route::where('customerCode', $customerCode)
            ->where('routeTypeCode', $routeTypeCode)
            ->pluck('originLocationCode');

        return Location::whereIn('code', $originLocationArr)->get();
    }

    public function destinationCustomer($customerCode, $routeTypeCode, $originLocationCode)
    {
        $destinationLocationArr = Route::where('customerCode', $customerCode)
            ->where('routeTypeCode', $routeTypeCode)
            ->where('originLocationCode', $originLocationCode)
            ->pluck('destinationLocationCode');

        return Location::whereIn('code', $destinationLocationArr)->get();
    }

    public function routeCustomer($customerCode, $originLocationCode, $destinationLocation)
    {
        $route = Route::where('customerCode', $customerCode)
            ->where('originLocationCode', $originLocationCode)
            ->where('destinationLocationCode', $destinationLocation)
            ->with('routeDetail')
            ->first();

        return $route->routeDetail;
    }
}
