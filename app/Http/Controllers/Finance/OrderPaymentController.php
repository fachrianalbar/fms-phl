<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Data\Route;
use App\Services\Bank\UserBankService;
use App\Services\Finance\OrderPaymentService;
use App\Services\Master\MenuService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

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
        $this->title = 'Order Payment';
        $this->menuSvc = $menuSvc->getByName('Order Payment');
        $this->userBankSvc = $userBankSvc;
        $this->title = Auth::user()->languange == 'en' ? $this->menuSvc->name : $this->menuSvc->nama;
        $this->view = 'finance.order-payment.';
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

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $this->title . ' ' . __('general.data_was_save_successfully')
                ]);
            }

            return redirect()->route($this->view . 'index')->with('success', $this->title . ' ' . __('general.data_was_save_successfully'));
        } catch (\Throwable $th) {
            DB::rollback();

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Line : ' . $th->getLine() . ' - ' . $th->getMessage()
                ], 500);
            }

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
            $data = $this->service->findAllIsDoZero();

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
                    $cost = $this->getRouteAmount($row);

                    return '' . number_format($cost, 0, ',', '.');
                })
                ->addColumn('additional_cost', function ($row) {
                    $additionalCost = 0;
                    if (isset($row->orderPayment) && isset($row->orderPayment->additional_cost)) {
                        $additionalCost = (float) $row->orderPayment->additional_cost;
                    } else {
                        $additionalCost = (float) $row->cost->filter(fn($c) => strtolower($c->type ?? '') === 'on charge')->sum('nominal');
                    }
                    return '' . number_format($additionalCost, 0, ',', '.');
                })
                ->addColumn('ppn', function ($row) {
                    $ppn = 0;
                    if (isset($row->orderPayment) && isset($row->orderPayment->ppn)) {
                        $ppn = (float) $row->orderPayment->ppn;
                    } else {
                        $cost = $this->getRouteAmount($row);
                        $additionalCost = (float) $row->cost->filter(fn($c) => strtolower($c->type ?? '') === 'on charge')->sum('nominal');
                        $subtotal = $cost + $additionalCost;
                        $ppn = isset($row->customer->ppn) ? $subtotal * ($row->customer->ppn / 100) : 0;
                    }
                    return '' . number_format($ppn, 0, ',', '.');
                })
                ->addColumn('pph', function ($row) {
                    $pph = 0;
                    if (isset($row->orderPayment) && isset($row->orderPayment->pph)) {
                        $pph = (float) $row->orderPayment->pph;
                    } else {
                        $cost = $this->getRouteAmount($row);
                        $additionalCost = (float) $row->cost->filter(fn($c) => strtolower($c->type ?? '') === 'on charge')->sum('nominal');
                        $subtotal = $cost + $additionalCost;
                        $pph = isset($row->customer->pph) ? $subtotal * ($row->customer->pph / 100) : 0;
                    }
                    return '' . number_format($pph, 0, ',', '.');
                })
                ->addColumn('grand_total', function ($row) {
                    $cost = $this->getRouteAmount($row);
                    $additionalCost = 0;
                    if (isset($row->orderPayment) && isset($row->orderPayment->additional_cost)) {
                        $additionalCost = (float) $row->orderPayment->additional_cost;
                    } else {
                        $additionalCost = (float) $row->cost->filter(fn($c) => strtolower($c->type ?? '') === 'on charge')->sum('nominal');
                    }
                    $subtotal = $cost + $additionalCost;

                    $ppn = 0;
                    if (isset($row->orderPayment) && isset($row->orderPayment->ppn)) {
                        $ppn = (float) $row->orderPayment->ppn;
                    } else {
                        $ppn = isset($row->customer->ppn) ? $subtotal * ($row->customer->ppn / 100) : 0;
                    }

                    $pph = 0;
                    if (isset($row->orderPayment) && isset($row->orderPayment->pph)) {
                        $pph = (float) $row->orderPayment->pph;
                    } else {
                        $pph = isset($row->customer->pph) ? $subtotal * ($row->customer->pph / 100) : 0;
                    }

                    $grandTotal = $subtotal + $ppn - $pph;
                    return '' . number_format($grandTotal, 0, ',', '.');
                })
                ->addColumn('paymentAmount', function ($row) {
                    return '' . number_format($row->orderPayment->total ?? 0, 0, ',', '.');
                })
                ->addColumn('total', function ($row) {
                    $cost = $this->getRouteAmount($row);
                    $additionalCost = 0;
                    if (isset($row->orderPayment) && isset($row->orderPayment->additional_cost)) {
                        $additionalCost = (float) $row->orderPayment->additional_cost;
                    } else {
                        $additionalCost = (float) $row->cost->filter(fn($c) => strtolower($c->type ?? '') === 'on charge')->sum('nominal');
                    }
                    $subtotal = $cost + $additionalCost;

                    $ppn = 0;
                    if (isset($row->orderPayment) && isset($row->orderPayment->ppn)) {
                        $ppn = (float) $row->orderPayment->ppn;
                    } else {
                        $ppn = isset($row->customer->ppn) ? $subtotal * ($row->customer->ppn / 100) : 0;
                    }

                    $pph = 0;
                    if (isset($row->orderPayment) && isset($row->orderPayment->pph)) {
                        $pph = (float) $row->orderPayment->pph;
                    } else {
                        $pph = isset($row->customer->pph) ? $subtotal * ($row->customer->pph / 100) : 0;
                    }

                    $payment = $row->orderPayment->total ?? 0;
                    $total = $subtotal + $ppn - $pph - $payment;

                    return '' . number_format($total, 0, ',', '.');
                })
                ->addColumn('paymentStatus', function ($row) {
                    $cost = $this->getRouteAmount($row);
                    $additionalCost = 0;
                    if (isset($row->orderPayment) && isset($row->orderPayment->additional_cost)) {
                        $additionalCost = (float) $row->orderPayment->additional_cost;
                    } else {
                        $additionalCost = (float) $row->cost->filter(fn($c) => strtolower($c->type ?? '') === 'on charge')->sum('nominal');
                    }
                    $subtotal = $cost + $additionalCost;

                    $ppn = 0;
                    if (isset($row->orderPayment) && isset($row->orderPayment->ppn)) {
                        $ppn = (float) $row->orderPayment->ppn;
                    } else {
                        $ppn = isset($row->customer->ppn) ? $subtotal * ($row->customer->ppn / 100) : 0;
                    }

                    $pph = 0;
                    if (isset($row->orderPayment) && isset($row->orderPayment->pph)) {
                        $pph = (float) $row->orderPayment->pph;
                    } else {
                        $pph = isset($row->customer->pph) ? $subtotal * ($row->customer->pph / 100) : 0;
                    }

                    $grandTotal = $subtotal + $ppn - $pph;
                    $payment = isset($row->orderPayment) ? (float) $row->orderPayment->total : 0;

                    $status = 'Belum Bayar';
                    $badgeClass = 'danger';

                    if ($payment > 0) {
                        if ($payment == $grandTotal) {
                            $status = 'Lunas';
                            $badgeClass = 'success';
                        } elseif ($payment > $grandTotal) {
                            $status = 'Kelebihan Bayar';
                            $badgeClass = 'info';
                        } else {
                            $status = 'Belum Lunas';
                            $badgeClass = 'warning';
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
                ->rawColumns(['action', 'fleet.plateNumber', 'customer.name', 'route.originLocation.name', 'route.destinationLocation.name', 'cost', 'additional_cost', 'ppn', 'pph', 'grand_total', 'paymentAmount', 'total', 'paymentStatus'])
                ->toJson();
        }
    }

    public function pdfOrderPaymentMulti(Request $request)
    {
        $orderCodes = $request->input('orderCodes', []);

        if (is_string($orderCodes)) {
            $orderCodes = array_filter(array_map('trim', explode(',', $orderCodes)));
        }

        if (empty($orderCodes)) {
            return redirect()->route($this->view . 'index')->with('fail', 'Tidak ada order yang dipilih');
        }

        $orders = \App\Models\Operational\Order::with([
            'fleet.company',
            'driver',
            'customer.company',
            'route.originLocation',
            'route.destinationLocation',
            'orderMaterial.material',
            'cost',
        ])->whereIn('code', $orderCodes)->get();

        if ($orders->isEmpty()) {
            return redirect()->route($this->view . 'index')->with('fail', 'Data order tidak ditemukan');
        }

        $groupedByFormat = $orders->groupBy(function ($order) {
            return $order->customer->company->format ?? 'P';
        });

        $firstFormat = $groupedByFormat->keys()->first();
        $useGeneralTemplate = count($groupedByFormat) > 1;

        $pdfTemplate = 'finance.vendor-payment.pdf.general-phl';

        if (! $useGeneralTemplate) {
            if ($firstFormat === 'P') {
                $pdfTemplate = 'finance.vendor-payment.pdf.pribadi';
            } elseif (in_array($firstFormat, ['WTMS', 'WT'])) {
                $pdfTemplate = 'finance.vendor-payment.pdf.general-wt';
            }
        }

        $totalCost = 0;
        $totalAdditionalCost = 0;
        $totalPpnAmount = 0;
        $totalPphAmount = 0;
        $totalGrandTotal = 0;

        foreach ($orders as $order) {
            $routeAmount = $this->getRouteAmount($order);
            $additionalCost = $order->cost ? $order->cost->filter(fn($c) => strtolower($c->type ?? '') === 'on charge')->sum('nominal') : 0;
            $totalBefore = $routeAmount + $additionalCost;

            $ppn = $order->customer->ppn ?? 0;
            $ppnAmount = ($totalBefore * $ppn) / 100;

            $pph = $order->customer->pph ?? 0;
            $pphAmount = ($totalBefore * $pph) / 100;

            $grandTotal = $totalBefore + $ppnAmount - $pphAmount;

            $totalCost += $routeAmount;
            $totalAdditionalCost += $additionalCost;
            $totalPpnAmount += $ppnAmount;
            $totalPphAmount += $pphAmount;
            $totalGrandTotal += $grandTotal;
        }

        $company = \App\Models\CompanySetting::first();
        $customerFirst = $orders->first()->customer;

        $mpdf = new \Mpdf\Mpdf([
            'orientation' => 'P',
            'format' => [215, 330],
            'tempDir' => storage_path('app/mpdf-temp'),
        ]);

        $mpdf->setAutoTopMargin = 'stretch';
        $mpdf->setAutoBottomMargin = 'stretch';

        $mpdf->WriteHTML(
            view($pdfTemplate . '-multi')
                ->with('orders', $orders)
                ->with('customer', $customerFirst)
                ->with('company', $company)
                ->with('totalSubtotal', $totalCost)
                ->with('totalAdditionalCost', $totalAdditionalCost)
                ->with('totalPpnAmount', $totalPpnAmount)
                ->with('totalPphAmount', $totalPphAmount)
                ->with('totalGrandTotal', $totalGrandTotal)
                ->with('isOrderPaymentPdf', true)
        );

        return $mpdf->Output('Nota-Pembayaran-Multi-' . now()->format('YmdHis') . '.pdf', 'I');
    }

    public function orderDetailPayment($orderCode)
    {
        return $this->service->orderPaymentDetail($orderCode);
    }

    private function getRouteAmount($order): float
    {
        return (float) ($order->routeAmount ?? 0);
    }
}
