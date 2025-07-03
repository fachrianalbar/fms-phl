<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Data\Route;
use App\Services\Bank\UserBankService;
use App\Services\Finance\OrderPaymentService;
use App\Services\Master\MenuService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OrderPaymentController extends Controller
{
    protected $service;
    protected $title;
    protected $view;
    protected $menuSvc;
    protected $userBankSvc;

    public function __construct(OrderPaymentService $orderPaymentSvc, MenuService $menuSvc, UserBankService $userBankSvc)
    {
        $this->service = $orderPaymentSvc;
        $this->title = "Order Payment";
        $this->menuSvc = $menuSvc->getByName("Order Payment");
        $this->userBankSvc = $userBankSvc;
        $this->title = Auth::user()->languange == 'en' ? $this->menuSvc->name : $this->menuSvc->nama;
        $this->view = "finance.order-payment.";
    }

    public function index()
    {
        $userBank = $this->userBankSvc->findAll();
        return view($this->view . 'index')
            ->with('view', $this->view)
            ->with('userBank', $userBank)
            ->with('title', $this->title);
    }

    public function store(Request $request)
    {
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

    public function show(string $id)
    {
        $data = $this->service->getById($id);
        $orderPayment = $this->service->orderPaymentDetail($data->code);
        $route = Route::where('code', $data->routeCode)->first();

        return view($this->view . 'show')
            ->with('view', $this->view)
            ->with('data', $data)
            ->with('orderPayment', $orderPayment)
            ->with('route', $route)
            ->with('title', $this->title);
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->findAll();
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
                ->editColumn('orderDate', function ($row) {
                    return Carbon::parse($row->orderDate)->format('d-m-Y');
                })
                ->addColumn('cost', function ($row) {
                    $cost = 0;
                    foreach ($row->cost as $item) {
                        $cost += $item->nominal;
                    }

                    return '' . number_format($cost, 0, ',', '.');
                })
                ->addColumn('pph', function ($row) {
                    $cost = 0;
                    foreach ($row->cost as $item) {
                        $cost += $item->nominal;
                    }

                    $pph = isset($row->customer->pph) ? $cost * ($row->customer->pph / 100) : 0;


                    return '' . number_format($pph, 0, ',', '.');
                })
                ->addColumn('pph', function ($row) {
                    $cost = 0;
                    foreach ($row->cost as $item) {
                        $cost += $item->nominal;
                    }

                    $pph = isset($row->customer->pph) ? $cost * ($row->customer->pph / 100) : 0;


                    return '' . number_format($pph, 0, ',', '.');
                })
                ->addColumn('paymentAmount', function ($row) {
                    return '' . number_format($row->orderPayment->total ?? 0, 0, ',', '.');
                })
                ->addColumn('total', function ($row) {
                    $cost = 0;
                    foreach ($row->cost as $item) {
                        $cost += $item->nominal;
                    }

                    $pph = isset($row->customer->pph) ? $cost * ($row->customer->pph / 100) : 0;
                    $payment = $row->orderPayment->total ?? 0;
                    $total = $cost + $pph - $payment;

                    return '' . number_format($total, 0, ',', '.');
                })
                ->addColumn('paymentStatus', function ($row) {
                    $status = "No Payment";
                    $badgeClass = "danger";
                    if (isset($row->orderPayment)) {
                        $status = "Dp";
                        $badgeClass = "warning";

                        if ($row->orderPayment->status == 1) {
                            $status = "Full Payment";
                            $badgeClass = "success";
                        }
                    }
                    return '<span class="badge rounded-pill text-bg-' . $badgeClass . '">' . $status . '</span>';
                })
                // ->editColumn('status', function ($row) {
                //     $statusText = '';
                //     $badgeClass = 'primary';

                //     if (isset($row->orderStatus->name)) {
                //         $statusText = Auth::user()->languange == 'id' ? $row->orderStatus->nama : $row->orderStatus->name;
                //     }

                //     if ($row->status == 3) {
                //         $badgeClass = 'primary';
                //     } elseif ($row->status == 6) {
                //         $badgeClass = 'success';
                //     }

                //     return '<span class="badge rounded-pill text-bg-' . $badgeClass . '">' . $statusText . '</span>';
                // })
                ->addColumn('action', function ($row) {
                    $payment = '<a href="javascript:showModal(\'' . $row->code . '\')"
                                class="btn btn-icon btn-sm bg-success-subtle me-1"
                                data-bs-toggle="tooltip" title="Action">
                                    <i class="mdi mdi-credit-card fs-14 text-success"></i>
                             </a>';
                    $history = '';

                    if (isset($row->orderPayment->status)) {
                        if ($row->orderPayment->status == 1) {
                            $payment = '';
                        }
                        $history = '<a href="' . route($this->view . 'show', $row->id) . '"
                        class="btn btn-icon btn-sm bg-primary-subtle me-1"
                        data-bs-toggle="tooltip" title="show">
                            <i class="mdi mdi-eye fs-14 text-primary"></i>
                        </a>';
                    }


                    $btn = '<td>
                                ' . $payment . '    
                                ' . $history . '    
                            </td>';

                    return $btn;
                })
                ->rawColumns(['action', 'fleet.plateNumber', 'customer.name', 'route.originLocation.name', 'route.destinationLocation.name', 'cost', 'pph', 'paymentAmount', 'total', 'paymentStatus'])
                ->toJson();
        }
    }

    public function orderDetailPayment($orderCode)
    {
        return $this->service->orderPaymentDetail($orderCode);
    }
}
