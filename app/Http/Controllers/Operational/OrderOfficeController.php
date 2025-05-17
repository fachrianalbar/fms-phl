<?php

namespace App\Http\Controllers\Operational;

use App\Helpers\FilterHelper;
use App\Http\Controllers\Controller;
use App\Services\MenuService;
use App\Models\Data\Route;
use App\Models\Data\TonaseBonus;
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
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;

class OrderOfficeController extends Controller
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


        return view('operational.office-order.index')
            ->with('view', $this->view)
            ->with('fleet', $fleet)
            ->with('customer', $customer)
            ->with('driver', $driver)
            ->with('location', $location)
            ->with('fleetType', $fleetType)
            ->with('orderType', $orderType)
            ->with('title', $this->title);
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

                ->editColumn('route.destinationLocation.name', function ($row) {
                    $destination = '';

                    if (isset($row->route->destinationLocation->name)) {
                        $destination = $row->route->destinationLocation->name;
                    }

                    return $destination;
                })

                ->addColumn('cost', function ($row) {
                    $data = $row->route->routeDetail;

                    $allowance = 0;
                    foreach ($data as $item) {
                        if ($item->costComponent->type == 'Allowance') {
                            if ($item->amount != 0) {
                                $allowance += $item->amount;
                            }

                            if ($item->percentage) {
                                $route = Route::where('code', $item->routeCode)->first();

                                $allowance += $route->price * ($item->percentage / 100);
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

                    $this->totalPrice = $allowance;

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

                    if ($row->notes) {
                        $note = '<li class=""><a href="javascript:showModal(\'' . $row->id . '\')"><i class="icon-receipt"></i></a></li>';
                    }
                    $btn = '<ul class="action">
                                        <li class="edit"> <a href="' . route($this->view . 'edit', $row->id) . '"><i class="icon-pencil-alt"></i></a></li>
                                        <li class="delete"><a href="javascript:deleteData(\'' . $row->id . '\')"><i class="icon-trash"></i></a></li>
                                        ' . $note . '                                   

                                    </ul>';

                    return $btn;
                })
                ->editColumn('orderDate', function ($row) {
                    // return Carbon::parse($row->orderDate)->format('d-M-Y');

                    return Carbon::parse($row->orderDate)->format('d-m-Y');
                })
                ->rawColumns(['action', 'fleet.type.name', 'fleet.plateNumber', 'customer.name', 'route.destinationLocation.name', 'material.name', 'driver.name', 'cost', 'bonus', 'tonase', 'addCost', 'totalPrice'])
                ->toJson();
        }
    }
}
