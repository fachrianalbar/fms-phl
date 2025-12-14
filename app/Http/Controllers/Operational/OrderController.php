<?php

namespace App\Http\Controllers\Operational;

use App\Enums\OrderCostType;
use App\Exports\OrderReport;
use App\Helpers\FilterHelper;
use App\Helpers\GenerateCode;
use App\Http\Controllers\Controller;
use App\Models\Data\Route;
use App\Models\Data\TonaseBonus;
use App\Models\Master\CostComponent;
use App\Models\Master\Customer;
use App\Models\Master\Location;
use App\Models\Operational\Order;
use App\Models\Operational\OrderCost;
use App\Services\CompanySettingService;
use App\Services\Data\FleetDriverService;
use App\Services\Master\CostComponentService;
use App\Services\Master\CustomerService;
use App\Services\Master\EmployeeService;
use App\Services\Master\FleetService;
use App\Services\Master\FleetTypeService;
use App\Services\Master\LocationService;
use App\Services\Master\MaterialService;
use App\Services\Master\OrderTypeService;
use App\Services\Master\RouteTypeService;
use App\Services\Master\UnitService;
use App\Services\MenuService;
use App\Services\Operational\OrderService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;

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

    protected $costComponentSvc;

    protected $unitSvc;

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
        MenuService $menuSvc,
        CostComponentService $costComponentSvc,
        UnitService $unitSvc

    ) {
        $this->service = $orderSvc;
        $this->title = 'Order';
        $this->menuSvc = $menuSvc->getByName('Order');
        $this->title = Auth::user()->languange == 'en' ? $this->menuSvc->name : $this->menuSvc->nama;
        $this->view = 'operational.order.';
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
        $this->routeTypeSvc = $routeTypeSvc;
        $this->costComponentSvc = $costComponentSvc;
        $this->unitSvc = $unitSvc;
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
        $unit = $this->unitSvc->findAll();

        return view($this->view.'index')
            ->with('view', $this->view)
            ->with('fleet', $fleet)
            ->with('customer', $customer)
            ->with('driver', $driver)
            ->with('location', $location)
            ->with('fleetType', $fleetType)
            ->with('orderType', $orderType)
            ->with('unit', $unit)
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
        $driver = $this->driverSvc->findDriver();
        $fleet = $this->service->getFleet();
        $company = $this->companySvc->findAll();
        $component = CostComponent::get();
        $unit = $this->unitSvc->findAll();

        return view($this->view.'create')
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
            ->with('unit', $unit)
            ->with('title', $this->title);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if ($request->page == 'cost-component') {
            $this->costComponentSvc->store($request, 'Cost Component');
            if ($request->ajax()) {
                return response()->json(['message' => 'Data was saved successfully']);
            }
        }

        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'code' => 'required',
            'shipmentNumber' => ['required', Rule::unique('order', 'shipmentNumber')->whereNull('deleted_at')],
            // 'salesOrder' => ['required', Rule::unique('order', 'salesOrder')],
            'orderDate' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // dd($request->code);

            $this->service->store($request, $this->title);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $this->title.' '.__('general.data_was_save_successfully'),
                'redirect' => route($this->view.'index'),
            ]);
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Line : '.$th->getLine().'<br>'.$th->getMessage(),
            ], 500);
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
        $customerDetailOrder = $this->service->getCustomerDetailOrder($data->code);
        $route = Route::where('code', $data->routeCode)->first();
        $component = CostComponent::get();
        $cost = OrderCost::where('orderCode', $data->code)->get();
        $totalPrice = 0;
        $allowance = 0;

        // if (isset($data->route->routeDetail)) {
        //     $dataRoute = $data->route->routeDetail;

        //     foreach ($dataRoute as $item) {
        //         if ($item->costComponent->type == 'Allowance') {
        //             if ($item->amount != 0) {
        //                 $allowance = $item->amount;
        //             }

        //             if ($item->percentage) {
        //                 $route = Route::where('code', $item->routeCode)->first();

        //                 $allowance = $route->price * ($item->percentage / 100);
        //             }
        //         }
        //     }
        //     $totalPrice = $allowance;
        // }

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

        return view($this->view.'show')
            ->with('view', $this->view)
            ->with('data', $data)
            ->with('route', $route)
            ->with('component', $component)
            ->with('orderCost', OrderCostType::cases())
            ->with('cost', $cost)
            ->with('customerDetailOrder', $customerDetailOrder)
            ->with('title', $this->title);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data = $this->service->getById($id);

        if (! $data) {
            return redirect()->route($this->view.'index')->with('fail', 'Data not found');
        }

        // Check if status is 5 or 6 - cannot edit for non-authorized roles
        if (! in_array(Auth::user()->roleCode, ['SPRADMIN', 'SPRUSER']) && in_array($data->status, [5, 6])) {
            return redirect()->route($this->view.'index')->with('fail', 'Cannot edit order with status 5 or 6');
        }

        $material = $this->materialSvc->findAll();
        $fleetDriver = $this->fleetDriverSvc->findAll();
        $customer = $this->customerSvc->findAll();
        $orderType = $this->orderTypeSvc->findAll();
        $routeType = $this->routeTypeSvc->findAll();
        $fleetType = $this->fleetTypeSvc->findAll();
        $driver = $this->driverSvc->findDriver();
        $company = $this->companySvc->findAll();
        $route = Route::where('customerCode', $data->customerCode)->with(['originLocation', 'destinationLocation'])->get();
        // $route = Route::where('code', $data->routeCode)->first();
        // $origin = Route::where('customerCode', $route->customerCode)->where('routeTypeCode', $route->routeTypeCode)->get();
        // $destination = Route::where('customerCode', $route->customerCode)->where('routeTypeCode', $route->routeTypeCode)->where('originLocationCode', $route->originLocationCode)->get();
        $cost = OrderCost::where('orderCode', $data->code)->get();
        $fleet = $this->service->getFleet($data->fleetCode);
        $component = CostComponent::get();
        $customerDetailOrder = $this->service->getCustomerDetailOrder($data->code);
        $unit = $this->unitSvc->findAll();

        return view($this->view.'edit')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('material', $material)
            ->with('fleetDriver', $fleetDriver)
            ->with('customer', $customer)
            ->with('orderType', $orderType)
            ->with('routeType', $routeType)
            ->with('fleetType', $fleetType)
            ->with('driver', $driver)
            // ->with('origin', $origin)
            // ->with('destination', $destination)
            ->with('route', $route)
            ->with('cost', $cost)
            ->with('orderCost', OrderCostType::cases())
            ->with('component', $component)
            ->with('fleet', $fleet)
            ->with('company', $company)
            ->with('customerDetailOrder', $customerDetailOrder)
            ->with('unit', $unit)
            ->with('data', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $this->service->getById($id);

        // Check if status is 5 or 6 - cannot edit for non-authorized roles
        if (! in_array(Auth::user()->roleCode, ['SPRADMIN', 'SPRUSER']) && in_array($data->status, [5, 6])) {
            return redirect()->route($this->view.'index')->with('fail', 'Cannot update order with status 5 or 6');
        }

        $validator = Validator::make($request->all(), [
            'shipmentNumber' => ['required', Rule::unique('order', 'shipmentNumber')->ignore($data->id)->whereNull('deleted_at')],
            // 'salesOrder' => ['required', Rule::unique('order', 'salesOrder')->ignore($data->id)],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $this->service->update($request, $id, $this->title);

            DB::commit();

            return redirect()->route($this->view.'index')->with('success', $this->title.' '.__('general.data_was_update_succesfully'));
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->back()->with('error', 'Line : '.$th->getLine().'<br>'.$th->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Check role authorization
        if (! in_array(Auth::user()->roleCode, ['SPRADMIN', 'SPRUSER'])) {
            return redirect()->route($this->view.'index')->with('fail', 'Unauthorized');
        }

        $data = $this->service->getById($id);

        // Check if status is 5 or 6 - cannot delete
        if (in_array($data->status, [5, 6])) {
            return redirect()->route($this->view.'index')->with('fail', 'Cannot delete order with status 5 or 6');
        }

        $this->service->destroy($id, $this->title);

        return redirect()->route($this->view.'index')->with('success', 'Delete Data Success');
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->datatable();

            // Filter untuk role non-SPRADMIN/SPRUSER hanya tampilkan status 0
            $isAuthorized = in_array(Auth::user()->roleCode, ['SPRADMIN', 'SPRUSER']);
            if (! $isAuthorized) {
                $data->where('status', 0);
            }

            if ($request->has('is_order_tax')) {
                $data->where('is_order_tax', $request->is_order_tax);
            }

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
                'destination' => 'route.destinationLocation.name',
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
                ->addColumn('tonase', function ($row) {
                    if (isset($row->route->routeTypeCode)) {
                        if ($row->route->routeTypeCode == 'TONASE') {
                            return ''.number_format($row->route->price, 0, ',', '.');
                        }
                    }

                    return ''. 0;
                })

                ->addColumn('bonus', function ($row) {
                    $bonus = TonaseBonus::where('min', '<=', $row->qty)->where('max', '>=', $row->qty)->first();

                    if ($bonus) {
                        $this->totalPrice += $bonus->value;

                        return ''.number_format($bonus->value, 0, ',', '.');
                    }

                    return ''. 0;
                })
                ->addColumn('cost', function ($row) {
                    $cost = 0;
                    if (isset($row->cost)) {
                        foreach ($row->cost as $item) {
                            $cost += $item->nominal;
                        }
                    }
                    $this->totalPrice += $cost;

                    return ''.number_format($cost, 0, ',', '.');
                })

                ->addColumn('orderType', function ($row) {
                    // Determine order type from fleet's company.type
                    $type = '';

                    if (isset($row->fleet->company->type)) {
                        $isExternal = strtolower($row->fleet->company->type) === 'external';
                        $type = $isExternal ? 'External' : 'Internal';
                    }

                    return $type;
                })
                ->addColumn('totalPrice', function () {
                    return ''.number_format($this->totalPrice, 0, ',', '.');
                })
                ->addColumn('action', function ($row) {

                    $note = '';
                    $finishOrder = '';
                    $delete = '';
                    $edit = '';
                    $addDriver = '';
                    $costComponent = '';
                    $isAuthorized = in_array(Auth::user()->roleCode, ['SPRADMIN', 'SPRUSER']);
                    $canEdit = ! in_array($row->status, [5, 6]);

                    // Tombol Add Driver - muncul di semua status order
                    $addDriver = '<a href="javascript:addOrderDriver(\''.$row->id.'\', \''.$row->code.'\')"
                                class="btn btn-icon btn-sm bg-warning-subtle me-1"
                                data-bs-toggle="tooltip" title="Add Driver">
                                    <i class="mdi mdi-account-plus fs-14 text-warning"></i>
                                </a>';

                    // Tombol Cost Component - muncul untuk role authorized (SPRADMIN/SPRUSER)
                    if ($isAuthorized) {
                        $costComponent = '<a href="javascript:manageCostComponent(\''.$row->id.'\', \''.$row->code.'\')"
                                    class="btn btn-icon btn-sm bg-secondary-subtle me-1"
                                    data-bs-toggle="tooltip" title="Kelola Komponen Biaya">
                                        <i class="mdi mdi-cash fs-14 text-secondary"></i>
                                    </a>';
                    }

                    // Edit button - hanya untuk role SPRADMIN/SPRUSER
                    if ($isAuthorized) {

                        $edit = '<a href="'.route($this->view.'edit', $row->id).'"
                        class="btn btn-icon btn-sm bg-primary-subtle me-1"
                        data-bs-toggle="tooltip" title="Edit">
                            <i class="mdi mdi-pencil-outline fs-14 text-primary"></i>
                        </a>';
                    }

                    if ($row->notes) {
                        $note = '<a href="javascript:showModal(\''.$row->id.'\')"
                                class="btn btn-icon btn-sm bg-info-subtle me-1"
                                data-bs-toggle="tooltip" title="Note">
                                    <i class="mdi mdi-text-box fs-14 text-info"></i>
                                </a>';
                    }

                    // Delete dan finish order - hanya untuk role authorized (SPRADMIN/SPRUSER)
                    if ($isAuthorized) {
                        $finishOrder = '<a href="javascript:finishOrder(\''.$row->id.'\')"
                                class="btn btn-icon btn-sm bg-success-subtle me-1"
                                data-bs-toggle="tooltip" title="Finish Order">
                                    <i class="mdi mdi-check-bold fs-14 text-success"></i>
                                </a>';

                        $delete = ' <a href="javascript:deleteData(\''.$row->id.'\')"
                        class="btn btn-icon btn-sm bg-danger-subtle me-1"
                        data-bs-toggle="tooltip" title="Delete">
                            <i class="mdi mdi-delete fs-14 text-danger"></i>
                        </a>';
                    }

                    $btn = '<td>
                      <a href="'.route($this->view.'show-order', $row->id).'"
                        class="btn btn-icon btn-sm bg-success-subtle me-1"
                        data-bs-toggle="tooltip" title="Show">
                            <i class="mdi mdi-eye fs-14 text-success"></i>
                        </a>
                        '.$edit.'
                        '.$delete.'
                        '.$note.'
                        '.$finishOrder.'
                        '.$addDriver.'
                        '.$costComponent.'
                    </td>';

                    return $btn;
                })
                ->editColumn('orderDate', function ($row) {
                    return Carbon::parse($row->orderDate)->format('d-m-Y');
                })
                ->addColumn('actionTax', function ($row) {
                    $btn = '<input class="order-checkbox" type="checkbox" name="order[]" data-id="'.$row->code.'" value="'.$row->code.'">';

                    return $btn;
                })
                ->rawColumns(['action', 'actionTax', 'status', 'fleet.type.name', 'fleet.plateNumber', 'customer.name', 'route.destinationLocation.name', 'material.name', 'driver.name', 'cost', 'bonus', 'tonase', 'totalPrice'])
                ->toJson();
        }
    }

    public function finishOrder(string $id)
    {
        $data = $this->service->getById($id);

        if (! $data) {
            return redirect()->route($this->view.'index')->with('fail', 'Data not found');
        }

        $this->service->finishOrder($id);

        return redirect()->route($this->view.'index')->with('success', __('menu_order.finish_order_was_successfull'));
    }

    public function storeOrderDriver(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'orderCode' => 'required',
            'driverCode' => 'required',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->route($this->view.'index')
                ->withErrors($validator)
                ->withInput()
                ->with('fail', 'Validation failed');
        }

        try {
            DB::beginTransaction();

            $orderDriver = new \App\Models\Operational\OrderDriver;
            $orderDriver->code = GenerateCode::generateCode('ODR');
            $orderDriver->orderCode = $request->orderCode;
            $orderDriver->driverCode = $request->driverCode;
            $orderDriver->description = $request->description;
            $orderDriver->save();

            DB::commit();

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => __('menu_order.change_driver_success')]);
            }

            return redirect()->route($this->view.'index')->with('success', __('menu_order.change_driver_success'));
        } catch (\Exception $e) {
            DB::rollback();

            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => __('menu_order.change_driver_failed').': '.$e->getMessage()]);
            }

            return redirect()->route($this->view.'index')->with('fail', __('menu_order.change_driver_failed').': '.$e->getMessage());
        }
    }

    public function getOrderDrivers(Request $request)
    {
        $orderCode = $request->get('orderCode');

        if (! $orderCode) {
            return response()->json(['success' => false, 'message' => 'Order code is required']);
        }

        try {
            $orderDrivers = \App\Models\Operational\OrderDriver::with('driver')
                ->where('orderCode', $orderCode)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $orderDrivers,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function deleteOrderDriver(Request $request)
    {
        $id = $request->get('id');

        if (! $id) {
            return response()->json(['success' => false, 'message' => 'ID is required']);
        }

        try {
            DB::beginTransaction();

            $orderDriver = \App\Models\Operational\OrderDriver::find($id);

            if (! $orderDriver) {
                return response()->json(['success' => false, 'message' => 'Data not found']);
            }

            $orderDriver->delete();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Supir berhasil dihapus']);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json(['success' => false, 'message' => 'Gagal menghapus supier: '.$e->getMessage()]);
        }
    }

    public function excelOrder(Request $request)
    {
        return Excel::download(new OrderReport($request), 'Order-Report.xlsx');
    }

    public function deleteCost($id)
    {
        $cost = OrderCost::where('id', $id)->with(['order'])->first();

        if (! $cost) {
            return redirect()->back()->with('error', 'Cost not found');
        }

        $orderId = $cost->order->id;
        $cost->delete();

        return redirect()->route($this->view.'edit', $orderId)->with('success', 'Delete Data Success');
    }

    public function getOrderCosts(Request $request)
    {
        $orderCode = $request->get('orderCode');

        if (! $orderCode) {
            return response()->json(['success' => false, 'message' => 'Order code is required']);
        }

        try {
            $orderCosts = OrderCost::with('costComponent')
                ->where('orderCode', $orderCode)
                ->where('type', 'On Charge')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $orderCosts,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading order costs: '.$e->getMessage(),
            ]);
        }
    }

    public function storeOrderCost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'orderCode' => 'required',
            'nominal' => 'required|min:0',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                ]);
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('fail', 'Validation failed');
        }

        try {
            DB::beginTransaction();

            $orderCost = new OrderCost;
            $orderCost->code = GenerateCode::generateCode('OCT');
            $orderCost->orderCode = $request->orderCode;
            $orderCost->componentType = $request->componentType; // Simpan component code dari select
            $orderCost->nominal = (int) str_replace('.', '', $request->nominal);
            $orderCost->type = 'On Charge';
            $orderCost->description = $request->description ?? null;
            $orderCost->is_route = 0; // Custom/tambahan user, bukan dari route
            $orderCost->save();

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Komponen biaya berhasil ditambahkan',
                ]);
            }

            return redirect()->back()->with('success', 'Komponen biaya berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollback();

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menambahkan komponen biaya: '.$e->getMessage(),
                ]);
            }

            return redirect()->back()->with('fail', 'Gagal menambahkan komponen biaya: '.$e->getMessage());
        }
    }

    public function deleteOrderCost(Request $request)
    {
        try {
            $cost = OrderCost::find($request->id);

            if (! $cost) {
                return response()->json([
                    'success' => false,
                    'message' => 'Komponen biaya tidak ditemukan',
                ]);
            }

            $cost->delete();

            return response()->json([
                'success' => true,
                'message' => 'Komponen biaya berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus komponen biaya: '.$e->getMessage(),
            ]);
        }
    }

    public function routeOrder($customerId, $routeTypeCode)
    {
        $customer = Customer::where('id', $customerId)->first();
        $route = Route::where('customerCode', $customer->code)
            ->where('routeTypeCode', $routeTypeCode)
            ->with(['routeDetail', 'originLocation', 'destinationLocation'])
            ->get();

        return $route;
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

    public function routeOrderDetail($routeCode)
    {
        $route = Route::where('code', $routeCode)
            ->with('routeDetail', 'routeDetail.costComponent')
            ->first();

        return $route->routeDetail;
    }

    public function storeOrderTax(Request $request)
    {
        $selectedOrders = json_decode($request->input('selectedOrders'), true);

        $validator = Validator::make($request->all(), [
            'order' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('fail', $validator->errors()->all()[0]);
        }

        try {
            DB::beginTransaction();

            $this->service->storeOrderTax($selectedOrders);

            DB::commit();

            return redirect()->back()->with('success', $this->title.' '.__('general.data_was_save_successfully'));
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->back()->with('fail', 'Line : '.$th->getLine().'<br>'.$th->getMessage());
        }
    }

    public function generateCode(Request $request)
    {
        $date = $request->date;

        $code = GenerateCode::generateCodeAscDate(
            'ORD',
            Order::class,
            'orderDate',
            $date,
        );

        return response()->json(['code' => $code]);
    }

    public function shipmentFormat($id)
    {
        $data = $this->service->shipmentFormat($id);

        return $data;
    }

    public function deleteOrderMaterial($id)
    {
        $this->service->deleteOrderMaterial($id);

        return redirect()->back()->with('success', __('general.delete_data_success'));
    }
}
