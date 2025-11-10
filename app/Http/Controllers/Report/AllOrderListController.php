<?php

namespace App\Http\Controllers\Report;

use App\Exports\AllOrderListReport;
use App\Helpers\FilterHelper;
use App\Http\Controllers\Controller;
use App\Models\Data\Route;
use App\Models\Data\TonaseBonus;
use App\Models\Operational\Order;
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
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;

class AllOrderListController extends Controller
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

    protected $totalCost;

    protected $totalMargin;

    protected $fleetSvc;

    protected $locationSvc;

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
        LocationService $locationSvc
    ) {
        $this->service = $orderSvc;
        $this->title = 'All Order List';
        $this->view = 'report.all-order-list.';
        $this->materialSvc = $materialSvc;
        $this->fleetDriverSvc = $fleetDriverSvc;
        $this->customerSvc = $customerSvc;
        $this->orderTypeSvc = $orderTypeSvc;
        $this->routeTypeSvc = $routeTypeSvc;
        $this->fleetTypeSvc = $fleetTypeSvc;
        $this->driverSvc = $driverSvc;
        $this->fleetSvc = $fleetSvc;
        $this->locationSvc = $locationSvc;
        $this->totalPrice = 0;
        $this->totalCost = 0;
        $this->totalMargin = 0;
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

        return view($this->view.'index')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('fleet', $fleet)
            ->with('customer', $customer)
            ->with('driver', $driver)
            ->with('location', $location)
            ->with('fleetType', $fleetType)
            ->with('orderType', $orderType);
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = Order::with([
                'fleetDriver.fleet',
                'driver',
                // 'fleetDriver.employee',
                'customer',
                'route.destinationLocation',
                'material',
                'route.routeDetail',
                'fleet',
                'fleet.type',
            ])->orderBy('created_at', 'desc');

            // if ($request->startDate && $request->endDate) {
            //     $data = Order::with([
            //         'fleetDriver.fleet',
            //         'driver',
            //         // 'fleetDriver.employee',
            //         'customer',
            //         'route.destinationLocation',
            //         'material',
            //         'route.routeDetail',
            //         'fleet',
            //         'fleet.type'
            //     ])->orderBy('created_at', 'desc');
            // }

            // Definisikan kolom filter dengan alias
            $filters = [
                'fleet_plateNumber' => $request->plateNumber,
                'customer_name' => $request->customerName,
                'driver_name' => $request->driverName,
                'fleetType_name' => $request->fleetTypeName,
                'shipmentNumber' => $request->shipmentNumber,
                'origin' => $request->origin,
                'destination' => $request->destination,
                'orderTypeCode' => $request->orderTypeCode,

            ];

            // Hubungkan alias ke relasi dan kolom yang sesuai
            $relations = [
                'fleet_plateNumber' => 'fleet.plateNumber',
                'customer_name' => 'customer.name',
                'driver_name' => 'driver.name',
                'fleetType_name' => 'fleet.type.name',
                'origin' => 'route.originLocation.name',
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

                ->addColumn('basic_allowance', function ($row) {
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

                            if ($item->costComponent->type == 'Allowance Office') {
                                if ($item->amount != 0) {
                                    $allowance += $item->amount;
                                }

                                if ($item->percentage) {
                                    $route = Route::where('code', $item->routeCode)->first();

                                    $allowance += $route->price * ($item->percentage / 100);
                                }
                            }
                        }
                        $this->totalCost = $allowance;
                    }

                    return ''.number_format($allowance, 0, ',', '.');
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
                        $this->totalCost += $bonus->value;

                        return ''.number_format($bonus->value, 0, ',', '.');
                    }

                    return ''. 0;
                })
                ->addColumn('addCost', function ($row) {
                    $cost = 0;
                    if (isset($row->cost)) {
                        foreach ($row->cost as $item) {
                            $cost += $item->nominal;
                        }
                    }
                    $this->totalCost += $cost;

                    return ''.number_format($cost, 0, ',', '.');
                })
                ->addColumn('totalPrice', function () {
                    return ''.number_format($this->totalPrice, 0, ',', '.');
                })

                ->editColumn('orderDate', function ($row) {
                    // return Carbon::parse($row->orderDate)->format('d-M-Y');

                    return Carbon::parse($row->orderDate)->format('d-m-Y');
                })
                ->addColumn('gaji', function ($row) {
                    $this->totalCost += 140000;

                    return number_format(140000, 0, ',', '.');
                })
                ->addColumn('basic_sales', function ($row) {
                    $basicSales = $row->qty * $row->route->price;

                    $this->totalMargin = $basicSales;

                    return number_format($basicSales, 0, ',', '.');
                })
                ->addColumn('total_cost', function ($row) {
                    $this->totalMargin -= $this->totalCost;

                    return number_format($this->totalCost, 0, ',', '.');
                })
                ->addColumn('total_margin', function ($row) {
                    return number_format($this->totalMargin, 0, ',', '.');
                })

                ->rawColumns(['fleet.type.name', 'total_margin', 'total_cost', 'basic_sales', 'fleet.plateNumber', 'customer.name', 'route.destinationLocation.name', 'route.originLocation.name', 'material.name', 'driver.name', 'basic_allowance', 'bonus', 'tonase', 'addCost', 'totalPrice', 'gaji'])
                ->toJson();
        }
    }

    public function excelAllOrderList(Request $request)
    {
        return Excel::download(new AllOrderListReport($request), 'All-Order-List-Report.xlsx');
    }
}
