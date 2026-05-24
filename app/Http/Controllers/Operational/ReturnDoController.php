<?php

namespace App\Http\Controllers\Operational;

use App\Http\Controllers\Controller;
use App\Models\Data\Route;
use App\Models\Master\CostComponent;
use App\Models\Operational\Order;
use App\Models\Operational\OrderCost;
use App\Services\Master\CustomerService;
use App\Services\Master\EmployeeService;
use App\Services\Master\FleetService;
use App\Services\Master\FleetTypeService;
use App\Services\Master\MaterialService;
use App\Services\Master\OrderTypeService;
use App\Services\Master\RouteTypeService;
use App\Services\Master\UnitService;
use App\Services\MenuService;
use App\Services\Operational\NotReturnDoService;
use App\Services\Operational\ReturnDoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class ReturnDoController extends Controller
{
    protected $service;

    protected $title;

    protected $view;

    protected $menuSvc;

    public function __construct(ReturnDoService $returnDoService, MenuService $menuSvc)
    {
        $this->service = $returnDoService;
        $this->title = 'Return Do';
        $this->menuSvc = $menuSvc->getByName('Return Do');
        $this->title = Auth::user()->languange == 'en' ? $this->menuSvc->name : $this->menuSvc->nama;
        $this->view = 'operational.return-do.';
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view($this->view.'index')
            ->with('view', $this->view)
            ->with('title', $this->title);
    }

    public function cancelDo(Request $request)
    {
        $selectedOrders = json_decode($request->input('selectedOrders'), true);

        try {
            DB::beginTransaction();

            Order::whereIn('code', $selectedOrders)->update([
                'status' => 3,
                'returnDate' => null,
                'returnDescription' => null,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Data was save succesfully');
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->back()->with('fail', 'Line : '.$th->getLine().'<br>'.$th->getMessage());
        }
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->datatable();

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
                ->editColumn('customer.name', function ($row) {
                    $customer = '';

                    if (isset($row->customer->name)) {
                        $customer = $row->customer->name;
                    }

                    return $customer;
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

                ->editColumn('returnDate', function ($row) {
                    // $returnDate = '';

                    // if (isset($row->returnDate)) {
                    //     $returnDate = Carbon::parse($row->returnDate)->format('d-m-Y H:i ');
                    // }

                    return $row->returnDate;
                })

                ->addColumn('action', function ($row) {
                    $editBtn = '<a href="' . route('operational.return-do.edit-order', $row->code) . '" class="btn btn-sm btn-primary" title="Edit"><i class="mdi mdi-pencil"></i></a>';
                    return $editBtn;
                })
                ->addColumn('detail', function ($row) {
                    $onChargeCosts = $row->onChargeCost;
                    $buttons = '';

                    // Filter only costs that actually have a costComponent relation
                    $validCosts = collect([]);
                    if ($onChargeCosts && $onChargeCosts->count() > 0) {
                        $validCosts = $onChargeCosts->filter(function ($cost) {
                            return isset($cost->costComponent) && ! is_null($cost->costComponent->name);
                        });
                    }

                    if ($validCosts->count() > 0) {
                        $costsData = $validCosts->map(function ($cost) {
                            return [
                                'component' => $cost->costComponent->name ?? '-',
                                'nominal' => 'Rp '.number_format($cost->nominal, 0, ',', '.'),
                            ];
                        })->toArray();

                        $costsJson = htmlspecialchars(json_encode($costsData), ENT_QUOTES, 'UTF-8');
                        $buttons .= '<button type="button" class="btn btn-sm btn-outline-success btn-detail-cost me-2" data-costs="'.$costsJson.'" data-shipment="'.$row->shipmentNumber.'" title="Lihat detail biaya">
                            <i class="mdi mdi-cash-multiple me-1"></i> Biaya
                        </button>';
                    }

                    // Cek apakah ada file yang diupload
                    $filesCount = \App\Models\OrderDetail::where('order_id', $row->id)
                        ->where('type', 'surat_jalan')
                        ->count();

                    if ($filesCount > 0) {
                        $buttons .= '<button type="button" class="btn btn-sm btn-outline-info btn-view-files" data-order-id="'.$row->id.'" data-order-code="'.$row->code.'" title="Lihat File Surat Jalan">
                            <i class="mdi mdi-file-image-multiple me-1"></i> File ('.$filesCount.')
                        </button>';
                    }

                    return $buttons ?: '-';
                })
                ->rawColumns(['action', 'detail', 'route.originLocation.name', 'customer.name', 'returnDate', 'route.destinationLocation.name', 'orderDate',  'fleet.plateNumber'])
                ->toJson();
        }
    }

    /**
     * Get uploaded files for specific order
     */
    public function getOrderFiles($orderId)
    {
        $files = \App\Models\OrderDetail::where('order_id', $orderId)
            ->where('type', 'surat_jalan')
            ->orderBy('created_at', 'desc')
            ->get(['id', 'file', 'created_at']);

        return response()->json([
            'success' => true,
            'files' => $files->map(function ($file) {
                return [
                    'id' => $file->id,
                    'url' => asset('storage/'.$file->file),
                    'name' => basename($file->file),
                    'uploaded_at' => $file->created_at->format('d-m-Y H:i'),
                ];
            }),
        ]);
    }

    /**
     * Show the form for editing the specified Return DO order.
     * Completely independent from NotReturnDoController.
     */
    public function editOrder(
        string $code,
        MaterialService $materialSvc,
        CustomerService $customerSvc,
        OrderTypeService $orderTypeSvc,
        RouteTypeService $routeTypeSvc,
        FleetTypeService $fleetTypeSvc,
        EmployeeService $employeeSvc,
        FleetService $fleetSvc,
        UnitService $unitSvc
    ) {
        $data = Order::with([
            'fleet.company',
            'customer',
            'driver',
            'route',
            'orderMaterial.material',
            'orderMaterial.unit',
        ])->where('code', $code)->firstOrFail();

        $material  = $materialSvc->findAll();
        $customer  = $customerSvc->findAll();
        $orderType = $orderTypeSvc->findAll();
        $routeType = $routeTypeSvc->findAll();
        $fleetType = $fleetTypeSvc->findAll();
        $driver    = $employeeSvc->findAll();
        $fleets    = $fleetSvc->findAll();
        $route     = Route::get();
        $cost      = OrderCost::where('orderCode', $data->code)->get();
        $component = CostComponent::get();
        $unit      = $unitSvc->findAll();

        return view('operational.return-do.edit-order')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('data', $data)
            ->with('material', $material)
            ->with('customer', $customer)
            ->with('orderType', $orderType)
            ->with('routeType', $routeType)
            ->with('fleetType', $fleetType)
            ->with('driver', $driver)
            ->with('fleets', $fleets)
            ->with('route', $route)
            ->with('cost', $cost)
            ->with('component', $component)
            ->with('unit', $unit);
    }

    /**
     * Update the specified Return DO order.
     * Completely independent from NotReturnDoController.
     * - No confirm_return logic (always stays as Return DO status=4)
     * - Redirects back to return-do index after save
     */
    public function updateOrder(Request $request, string $code, NotReturnDoService $notReturnDoSvc)
    {
        $data = Order::where('code', $code)->firstOrFail();
        $isAjaxRequest = $request->ajax() || $request->wantsJson();

        $validator = Validator::make($request->all(), [
            'fleetCode'  => 'required',
            'orderDate'  => 'required|date',
            'routeData'  => 'required',
        ]);

        if ($validator->fails()) {
            if ($isAjaxRequest) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors'  => $validator->errors(),
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Reuse the NotReturnDoService updateOrder logic (handles costs, fleet, route amount etc.)
            $notReturnDoSvc->updateOrder($request, $data->id, $this->title);

            // Keep the order as Return DO (status = 4) — do NOT change status
            // The order is already confirmed return, we just update its data

            DB::commit();

            $redirectUrl    = route('operational.return-do.index');
            $successMessage = 'Order Return DO berhasil diperbarui';

            if ($isAjaxRequest) {
                return response()->json([
                    'success'      => true,
                    'message'      => $successMessage,
                    'redirect_url' => $redirectUrl,
                ]);
            }

            return redirect()->to($redirectUrl)->with('success', $successMessage);
        } catch (\Throwable $th) {
            DB::rollback();

            if ($isAjaxRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: ' . $th->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('error', 'Error: ' . $th->getMessage())->withInput();
        }
    }
}
