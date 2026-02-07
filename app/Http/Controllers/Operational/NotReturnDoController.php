<?php

namespace App\Http\Controllers\Operational;

use App\Helpers\FilterHelper;
use App\Http\Controllers\Controller;
use App\Models\Data\Route;
use App\Models\Master\CostComponent;
use App\Models\Operational\Order;
use App\Models\Operational\OrderCost;
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
use App\Services\Operational\NotReturnDoService;
use App\Services\Operational\OrderService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class NotReturnDoController extends Controller
{
    protected $service;

    protected $title;

    protected $view;

    protected $menuSvc;

    protected $customerSvc;

    protected $fleetTypeSvc;

    protected $locationSvc;

    protected $driverSvc;

    protected $fleetSvc;

    protected $orderTypeSvc;

    protected $unitSvc;

    protected $orderSvc;

    protected $routeTypeSvc;

    protected $materialSvc;

    public function __construct(NotReturnDoService $notReturnDoService, MenuService $menuSvc, CustomerService $customerSvc, FleetTypeService $fleetTypeSvc, LocationService $locationSvc, EmployeeService $driverSvc, FleetService $fleetSvc, OrderTypeService $orderTypeSvc, UnitService $unitSvc, OrderService $orderSvc, RouteTypeService $routeTypeSvc, MaterialService $materialSvc)
    {
        $this->service = $notReturnDoService;
        $this->title = 'Not Return Do';
        $this->menuSvc = $menuSvc->getByName('Not Return Do');
        $this->customerSvc = $customerSvc;
        $this->fleetTypeSvc = $fleetTypeSvc;
        $this->locationSvc = $locationSvc;
        $this->driverSvc = $driverSvc;
        $this->fleetSvc = $fleetSvc;
        $this->orderTypeSvc = $orderTypeSvc;
        $this->unitSvc = $unitSvc;
        $this->orderSvc = $orderSvc;
        $this->routeTypeSvc = $routeTypeSvc;
        $this->materialSvc = $materialSvc;
        $this->title = Auth::user()->languange == 'en' ? $this->menuSvc->name : $this->menuSvc->nama;
        $this->view = 'operational.not-return-do.';
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

        return view($this->view . 'index')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('customer', $customer)
            ->with('fleetType', $fleetType)
            ->with('location', $location)
            ->with('driver', $driver)
            ->with('fleet', $fleet)
            ->with('orderType', $orderType)
            ->with('unit', $unit);
    }

    /**
     * Display the specified resource.
     */
    public function show($code)
    {
        $data = Order::with([
            'customer',
            'fleet.company',
            'driver',
            'route.originLocation',
            'route.destinationLocation',
        ])->where('code', $code)->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function confirmDo(Request $request)
    {
        $selectedOrders = json_decode($request->input('selectedOrders'), true);

        try {
            DB::beginTransaction();

            Order::whereIn('code', $selectedOrders)->update([
                'status' => 4,
                'returnDate' => $request->returnDate,
                'returnDescription' => $request->returnDescription,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Data was save succesfully');
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->back()->with('fail', 'Line : ' . $th->getLine() . '<br>' . $th->getMessage());
        }
    }

    public function edit($code)
    {
        $data = Order::with([
            'customer',
            'fleet',
            'driver',
            'route.originLocation',
            'route.destinationLocation',
            'route.routeType',
            'cost',
            'orderMaterial.material',
            'orderMaterial.unit',
        ])->where('code', $code)->firstOrFail();

        $customer = $this->customerSvc->findAll();
        $fleetType = $this->fleetTypeSvc->findAll();
        $location = $this->locationSvc->findAll();
        $driver = $this->driverSvc->findAll();
        $fleet = $this->orderSvc->getFleet($data->fleetCode);
        $orderType = $this->orderTypeSvc->findAll();
        $unit = $this->unitSvc->findAll();
        $routeType = $this->routeTypeSvc->findAll();
        $material = $this->materialSvc->findAll();
        $customerDetailOrder = $this->orderSvc->getCustomerDetailOrder($data->code);
        $component = CostComponent::get();
        $cost = OrderCost::where('orderCode', $data->code)->get();

        // abil route menggunakan code dari routeCode di order
        $route = Route::where('code', $data->routeCode)->firstOrFail();

        return view($this->view . 'edit')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('data', $data)
            ->with('customer', $customer)
            ->with('fleetType', $fleetType)
            ->with('location', $location)
            ->with('driver', $driver)
            ->with('fleet', $fleet)
            ->with('orderType', $orderType)
            ->with('unit', $unit)
            ->with('routeType', $routeType)
            ->with('material', $material)
            ->with('component', $component)
            ->with('cost', $cost)
            ->with('route', $route)
            ->with('customerDetailOrder', $customerDetailOrder);
    }

    public function update(Request $request, $code)
    {

        try {
            DB::beginTransaction();

            // Update order data sama seperti order edit
            $this->orderSvc->update($request, Order::where('code', $code)->first()->id, $this->title);

            Order::where('code', $code)->update([
                'status' => 4,
                'returnDate' => now()->format('Y-m-d'),
            ]);

            DB::commit();

            return redirect()->route('operational.not-return-do.index')
                ->with('success', 'Data berhasil diupdate');
        } catch (\Throwable $th) {
            DB::rollback();

            if ($request->has('confirm_return')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: ' . $th->getMessage(),
                ], 500);
            }

            return redirect()->back()
                ->with('fail', 'Error: ' . $th->getMessage())
                ->withInput();
        }
    }

    public function confirmReturn(Request $request, $code)
    {
        // Langsung redirect ke halaman edit order
        return redirect()->route('operational.not-return-do.edit-order', $code);
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
                ->editColumn('orderDate', function ($row) {
                    return Carbon::parse($row->orderDate)->format('d-m-Y');
                })
                ->editColumn('fleet.plateNumber', function ($row) {
                    $fleet = '';

                    if (isset($row->fleet->plateNumber)) {
                        $fleet = $row->fleet->plateNumber;
                    }

                    return $fleet;
                })
                ->editColumn('route.name', function ($row) {
                    $routeName = '';

                    if (isset($row->route->name)) {
                        $routeName = $row->route->name;
                    }

                    return $routeName;
                })
                ->editColumn('route.originLocation.name', function ($row) {
                    $origin = '';

                    if (isset($row->route->originLocation->name)) {
                        $origin = $row->route->originLocation->name;
                    }

                    return $origin;
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
                ->editColumn('route.originLocation.name', function ($row) {
                    $origin = '';

                    if (isset($row->route->originLocation->name)) {
                        $origin = $row->route->originLocation->name;
                    }

                    return $origin;
                })
                ->editColumn('route.destinationLocation.name', function ($row) {
                    $destination = '';

                    if (isset($row->route->destinationLocation->name)) {
                        $destination = $row->route->destinationLocation->name;
                    }

                    return $destination;
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
                ->addColumn('price', function ($row) {
                    return 'Rp ' . number_format($row->routeAmount ?? 0, 2, ',', '.');
                })
                ->addColumn('harga_vendor', function ($row) {
                    return 'Rp ' . number_format($row->personalVendorPrice ?? 0, 2, ',', '.');
                })
                ->editColumn('status', function ($row) {
                    $status = '';

                    if (isset($row->orderStatus->name)) {
                        $status = Auth::user()->languange == 'id' ? $row->orderStatus->nama : $row->orderStatus->name;
                    }

                    return $status;
                })
                ->addColumn('action', function ($row) {
                    $returnBtn = '<a href="' . route('operational.not-return-do.edit-order', $row->code) . '" class="btn btn-sm btn-primary me-1" title="Return"><i class="mdi mdi-calendar"></i></a>';

                    $rollbackBtn = '<button type="button" class="btn btn-sm btn-warning rollback-btn me-1" title="Rollback Status" data-id="' . $row->id . '" data-shipment="' . $row->shipmentNumber . '"><i class="mdi mdi-undo"></i></button>';

                    return $returnBtn . $rollbackBtn;
                })
                ->rawColumns(['action', 'route.originLocation.name', 'customer.name', 'route.destinationLocation.name', 'orderDate', 'fleet.plateNumber', 'driver.name', 'orderType', 'status', 'price', 'harga_vendor'])
                ->toJson();
        }
    }

    /**
     * Show the form for editing the specified order.
     */
    public function editOrder(string $code)
    {
        $data = Order::where('code', $code)->firstOrFail();

        $material = $this->materialSvc->findAll();
        $customer = $this->customerSvc->findAll();
        $orderType = $this->orderTypeSvc->findAll();
        $routeType = $this->routeTypeSvc->findAll();
        $fleetType = $this->fleetTypeSvc->findAll();
        $driver = $this->driverSvc->findAll();
        $fleet = $this->fleetSvc->findAll();
        $route = Route::get();
        $cost = OrderCost::where('orderCode', $data->code)->get();
        $fleetDetail = $this->fleetSvc->getById($data->fleetCode);
        $component = CostComponent::get();
        $unit = $this->unitSvc->findAll();

        return view($this->view . 'edit-order')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('material', $material)
            ->with('customer', $customer)
            ->with('orderType', $orderType)
            ->with('routeType', $routeType)
            ->with('fleetType', $fleetType)
            ->with('driver', $driver)
            ->with('route', $route)
            ->with('cost', $cost)
            ->with('component', $component)
            ->with('fleet', $fleetDetail)
            ->with('fleets', $fleet)
            ->with('unit', $unit)
            ->with('data', $data);
    }

    /**
     * Update the specified order in storage.
     */
    public function updateOrder(Request $request, string $code)
    {
        $data = Order::where('code', $code)->firstOrFail();

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'fleetCode' => 'required',
            'orderDate' => 'required|date',
            'routeData' => 'required',
            'suratJalanFiles.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Update order data
            $this->service->updateOrder($request, $data->id, $this->title);

            // Handle return confirmation
            if ($request->confirm_return == '1') {
                $returnDate = $request->returnDate ? $request->returnDate : now();
                $returnDescription = $request->returnDescription ?? 'Order returned via edit form';

                Order::where('code', $code)->update([
                    'status' => 4,
                    'returnDate' => $returnDate,
                    'returnDescription' => $returnDescription,
                ]);

                // Handle surat jalan file upload if files exist
                if ($request->hasFile('suratJalanFiles')) {
                    $uploadRequest = new Request();
                    $uploadRequest->files->set('files', $request->file('suratJalanFiles'));
                    $this->service->uploadSuratJalan($uploadRequest, $code);
                }

                DB::commit();
                return redirect()->route('operational.not-return-do.index')->with('success', 'Order updated and return confirmed successfully');
            }

            DB::commit();

            return redirect()->route('operational.not-return-do.index')->with('success', 'Order updated successfully');
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->back()->with('error', 'Error: ' . $th->getMessage())->withInput();
        }
    }

    /**
     * Upload surat jalan file untuk order
     */
    public function uploadSuratJalan(Request $request, string $code)
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        try {
            $result = $this->service->uploadSuratJalan($request, $code);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'count' => $result['count'],
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $th->getMessage(),
            ], 500);
        }
    }

    public function rollbackStatus(string $id)
    {
        $data = $this->service->getById($id);

        if (! $data) {
            return redirect()->route($this->view . 'index')->with('fail', 'Data not found');
        }

        $this->service->rollbackStatus($id);

        return redirect()->route($this->view . 'index')->with('success', 'Berhasil diubah');
    }
}
