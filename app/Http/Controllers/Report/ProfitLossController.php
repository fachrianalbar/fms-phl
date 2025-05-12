<?php

namespace App\Http\Controllers\Report;

use App\Helpers\FilterHelper;
use App\Http\Controllers\Controller;
use App\Models\Data\Route;
use App\Models\Data\TonaseBonus;
use App\Models\Master\Fleet;
use App\Services\Master\FleetService;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProfitLossReport;
use App\Services\Master\FleetTypeService;
use App\Services\Report\ProfitLossService;
use Carbon\Carbon;

class ProfitLossController extends Controller
{
    protected $service;
    protected $fleetSvc;
    protected $title;
    protected $view;
    protected $totalMargin;
    protected $fleetTypeSvc;
    protected $totalPrice;

    public function __construct(
        FleetService $fleetSvc,
        FleetTypeService $fleetTypeSvc,
        ProfitLossService $profitLossSvc
    ) {
        $this->service = $profitLossSvc;
        $this->title = "Profit & Loss";
        $this->view = "report.profit-loss.";
        $this->fleetSvc = $fleetSvc;
        $this->fleetTypeSvc = $fleetTypeSvc;
        $this->totalMargin = 0;
        $this->totalPrice = 0;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $fleet = $this->fleetSvc->findAll();
        $fleetType = $this->fleetTypeSvc->findAll();


        return view($this->view . 'index')
            ->with('view', $this->view)
            ->with('fleet', $fleet)
            ->with('fleetType', $fleetType)
            ->with('title', $this->title);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id, Request $request)
    {
        $data = Fleet::with(['type', 'orders' => function ($query) use ($request) {
            if ($request->startDate && $request->endDate) {
                $query->whereDate('orderDate', '>=', $request->startDate)
                    ->whereDate('orderDate', '<=', $request->endDate);
            }
        }, 'maintenances' => function ($query) use ($request) {
            if ($request->startDate && $request->endDate) {
                $query->whereDate('date', '>=', $request->startDate)
                    ->whereDate('date', '<=', $request->endDate);
            }
        }, 'orders.route.routeDetail', 'maintenances.details', 'orders.cost'])->where('code', $id)->first();

        $basicSales = 0;
        $basicAllowance = 0;
        $additionalCost = 0;
        $maintenance = 0;
        $tonase = 0;
        $orderCost = 0;



        foreach ($data->orders as $item) {
            // Basic Sales
            $basicSales += $item->qty * $item->route->price;

            $dataRoute = $item->route->routeDetail;
            $allowance = 0;

            // Additional Cost
            if (isset($item->cost)) {
                foreach ($item->cost as $cost) {
                    $additionalCost += $cost->nominal;
                }
            }

            // Basic Allowance
            foreach ($dataRoute as $itemRoute) {
                if ($itemRoute->costComponent->type == 'Allowance') {
                    if ($itemRoute->amount != 0) {
                        $allowance += $itemRoute->amount;
                    }

                    if ($itemRoute->percentage) {
                        $route = Route::where('code', $itemRoute->routeCode)->first();

                        $allowance += $route->price * ($itemRoute->percentage / 100);
                    }
                }

                if ($itemRoute->costComponent->type == 'Allowance Office') {
                    if ($itemRoute->amount != 0) {
                        $allowance += $itemRoute->amount;
                    }

                    if ($itemRoute->percentage) {
                        $route = Route::where('code', $itemRoute->routeCode)->first();

                        $allowance += $route->price * ($itemRoute->percentage / 100);
                    }
                }
            }

            $basicAllowance += $allowance;
        }
        foreach ($data->maintenances as $item) {
            foreach ($item->details as $details) {
                $maintenance += $details->qty * $details->item->price;
            }
        }

        foreach ($data->orders as $item) {
            $bonus = TonaseBonus::where('min', '<=', $item->qty)->where('max', '>=', $item->qty)->first();

            if ($bonus) {
                $tonase += $bonus->value;
            }
        }

        $orderCost = $basicAllowance + $additionalCost + $tonase;

        $totalMargin = $basicSales;
        $totalMargin -= $basicAllowance;
        $totalMargin -= $additionalCost;
        $totalMargin -= $maintenance;
        $totalMargin -= $tonase;

        return view($this->view . 'show')
            ->with('view', $this->view)
            ->with('data', $data)
            ->with('totalMargin', $totalMargin)
            ->with('maintenance', $maintenance)
            ->with('basicSales', $basicSales)
            ->with('orderCost', $orderCost)
            ->with('title', $this->title);
    }


    public function datatableMaintenance(Request $request)
    {
        // dd($request->all());
        if ($request->ajax()) {
            $data = $this->service->datatableMaintenance($request->fleetCode);

            // Definisikan kolom filter dengan alias
            $filters = [
                'fleet_plateNumber' => $request->plateNumber,
                'item_code' => $request->itemCode
            ];

            // Hubungkan alias ke relasi dan kolom yang sesuai
            $relations = [
                'fleet_plateNumber' => 'fleet.plateNumber',
                'item_code' => 'details.itemCode'
            ];

            $dateFilters = [
                'date' => [
                    'start' => $request->startDate,
                    'end' => $request->endDate,
                ],
            ];

            $data = FilterHelper::applyFilters($data, $filters, $relations, $dateFilters);

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('maintenanceDate', function ($row) {
                    $date = Carbon::parse($row->date)->format('d-M-Y');
                    $time = Carbon::parse($row->time)->format('H:i');
                    return $date . ' ' . $time;
                })
                ->editColumn('fleet.name', function ($row) {
                    $fleet = '';

                    if (isset($row->fleet->plateNumber)) {
                        $fleet = $row->fleet->plateNumber;
                    }

                    return $fleet;
                })

                ->addColumn('items', function ($row) {
                    $items = '';
                    foreach ($row->details as $item) {
                        $items .= $item->itemCode . ': ' . $item->item->name . ' Qty : ' .  $item->qty . '<br>';
                    }

                    return $items;
                })
                ->addColumn('price', function ($row) {
                    $price = 0;

                    foreach ($row->details as $item) {

                        $price += intval($item->item->price) * $item->qty;
                    }

                    return '' . number_format($price, 0, ',', '.');
                })
                ->addColumn('action', function ($row) {
                    $btn = '<ul class="action">
                                        <li class="edit"> <a href="' . route($this->view . 'edit', $row->id) . '"><i class="icon-pencil-alt"></i></a></li>
                                        <li class="delete"><a href="javascript:deleteData(\'' . $row->id . '\')"><i class="icon-trash"></i></a></li>
                                    </ul>';

                    return $btn;
                })
                ->rawColumns(['maintenanceDate', 'fleet.plateNumber', 'items', 'price', 'action'])
                ->toJson();
        }
    }

    public function datatableOrder(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->datatableOrder($request->fleetCode);

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

                ->editColumn('orderDate', function ($row) {
                    return Carbon::parse($row->orderDate)->format('d-m-Y');
                })
                ->rawColumns(['fleet.type.name', 'fleet.plateNumber', 'customer.name', 'route.destinationLocation.name', 'material.name', 'driver.name', 'cost', 'bonus', 'tonase', 'addCost', 'totalPrice'])
                ->toJson();
        }
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = Fleet::with(['type', 'orders' => function ($query) use ($request) {
                if ($request->startDate && $request->endDate) {
                    $query->whereDate('orderDate', '>=', $request->startDate)
                        ->whereDate('orderDate', '<=', $request->endDate);
                }
            }, 'maintenances' => function ($query) use ($request) {
                if ($request->startDate && $request->endDate) {
                    $query->whereDate('date', '>=', $request->startDate)
                        ->whereDate('date', '<=', $request->endDate);
                }
            }, 'orders.route.routeDetail', 'maintenances.details', 'orders.cost']);

            // Definisikan kolom filter dengan alias
            $filters = [
                'plateNumber' => $request->plateNumber,
                'fleetTypeCode' => $request->fleetTypeCode,
            ];

            // Hubungkan alias ke relasi dan kolom yang sesuai
            $relations = [];

            $dateFilters = [];

            $data = FilterHelper::applyFilters($data, $filters, $relations, $dateFilters);

            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('type.name', function ($row) {
                    $type = '';

                    if (isset($row->type->name)) {
                        $type = $row->type->name;
                    }

                    return $type;
                })
                ->addColumn('basic_sales', function ($row) {
                    $basicSales = 0;

                    foreach ($row->orders as $item) {
                        $basicSales += $item->qty * $item->route->price;
                    }
                    $this->totalMargin = $basicSales;


                    return  number_format($basicSales, 0, ',', '.');
                })

                ->addColumn('basic_allowance', function ($row) {
                    $basicAllowance = 0;

                    foreach ($row->orders as $order) {
                        $data = $order->route->routeDetail;
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

                        $basicAllowance += $allowance;
                    }
                    $this->totalMargin -= $basicAllowance;


                    return number_format($basicAllowance, 0, ',', '.');
                })
                ->addColumn('additional_cost', function ($row) {
                    $additionalCost = 0;

                    foreach ($row->orders as $item) {
                        if (isset($item->cost)) {
                            foreach ($item->cost as $cost) {
                                $additionalCost += $cost->nominal;
                            }
                        }
                    }

                    $this->totalMargin -= $additionalCost;

                    return number_format($additionalCost, 0, ',', '.');
                })
                ->addColumn('maintenance', function ($row) {
                    $maintenance = 0;

                    foreach ($row->maintenances as $item) {
                        foreach ($item->details as $details) {
                            $maintenance += $details->qty * $details->item->price;
                        }
                    }

                    $this->totalMargin -= $maintenance;

                    return number_format($maintenance, 0, ',', '.');
                })
                ->addColumn('tonase', function ($row) {
                    $tonase = 0;

                    foreach ($row->orders as $item) {
                        $bonus = TonaseBonus::where('min', '<=', $item->qty)->where('max', '>=', $item->qty)->first();

                        if ($bonus) {
                            $tonase += $bonus->value;
                        }
                    }

                    $this->totalMargin -= $tonase;


                    return number_format($tonase, 0, ',', '.');
                })
                ->addColumn('total_margin', function ($row) {

                    return number_format($this->totalMargin, 0, ',', '.');
                })
                ->addColumn('action', function ($row) {
                    $btn = '<ul class="action">
                                        <li class="edit"> <a href="' . route($this->view . 'show', $row->code) . '"><i class="icon-eye"></i></a></li>                                 
                            </ul>';

                    return $btn;
                })
                ->rawColumns(['type.name', 'basic_sales', 'basic_allowance', 'additional_cost', 'maintenance', 'tonase', 'total_margin', 'action'])
                ->toJson();
        }
    }

    public function excelProfitLoss(Request $request)
    {
        return Excel::download(new ProfitLossReport($request), 'Profit-Loss-Report.xlsx');
    }

    public function getProfitLossSummary(Request $request)
    {
        $fleetCode = $request->fleetCode;
        $startDate = $request->startDate;
        $endDate = $request->endDate;

        $data = Fleet::with(['orders' => function ($query) use ($startDate, $endDate) {
            if ($startDate && $endDate) {
                $query->whereDate('orderDate', '>=', $startDate)
                    ->whereDate('orderDate', '<=', $endDate);
            }
        }, 'maintenances' => function ($query) use ($startDate, $endDate) {
            if ($startDate && $endDate) {
                $query->whereDate('date', '>=', $startDate)
                    ->whereDate('date', '<=', $endDate);
            }
        }, 'orders.route.routeDetail', 'maintenances.details', 'orders.cost'])->where('code', $fleetCode)->first();

        $basicSales = 0;
        $basicAllowance = 0;
        $additionalCost = 0;
        $maintenance = 0;
        $tonase = 0;
        $orderCost = 0;

        foreach ($data->orders as $item) {
            $basicSales += $item->qty * $item->route->price;

            $allowance = 0;
            $dataRoute = $item->route->routeDetail;

            if (isset($item->cost)) {
                foreach ($item->cost as $cost) {
                    $additionalCost += $cost->nominal;
                }
            }

            foreach ($dataRoute as $itemRoute) {
                if (in_array($itemRoute->costComponent->type, ['Allowance', 'Allowance Office'])) {
                    if ($itemRoute->amount != 0) {
                        $allowance += $itemRoute->amount;
                    }
                    if ($itemRoute->percentage) {
                        $route = Route::where('code', $itemRoute->routeCode)->first();
                        $allowance += $route->price * ($itemRoute->percentage / 100);
                    }
                }
            }

            $basicAllowance += $allowance;
        }

        foreach ($data->maintenances as $item) {
            foreach ($item->details as $details) {
                $maintenance += $details->qty * $details->item->price;
            }
        }

        foreach ($data->orders as $item) {
            $bonus = TonaseBonus::where('min', '<=', $item->qty)->where('max', '>=', $item->qty)->first();
            if ($bonus) {
                $tonase += $bonus->value;
            }
        }

        $orderCost = $basicAllowance + $additionalCost + $tonase;

        $totalMargin = $basicSales - $basicAllowance - $additionalCost - $maintenance - $tonase;

        return response()->json([
            'basicSales' => $basicSales,
            'orderCost' => $orderCost,
            'maintenance' => $maintenance,
            'totalMargin' => $totalMargin,
        ]);
    }
}
