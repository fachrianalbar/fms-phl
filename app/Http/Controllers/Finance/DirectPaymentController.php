<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Data\Route;
use App\Services\Bank\UserBankService;
use App\Services\Finance\DirectPaymentService;
use App\Services\Master\MenuService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class DirectPaymentController extends Controller
{
    protected $service;

    protected $title;

    protected $view;

    protected $menuSvc;

    protected $userBankSvc;

    public function __construct(DirectPaymentService $directPaymentSvc, MenuService $menuSvc, UserBankService $userBankSvc)
    {
        $this->service = $directPaymentSvc;
        $this->title = 'Direct Payment';
        $this->menuSvc = $menuSvc->getByName('Direct Payment');
        $this->userBankSvc = $userBankSvc;
        $this->title = $this->menuSvc
            ? (Auth::user()?->languange == 'en' ? $this->menuSvc->name : $this->menuSvc->nama)
            : 'Pembayaran Langsung';
        $this->view = 'direct-payment.';
    }

    public function index()
    {
        $userBank = $this->userBankSvc->findAll();
        $orders = $this->service->findAllIsDoZero();

        $stats = [
            'totalCount' => $orders->count(),
            'unpaidCount' => 0,
            'partialCount' => 0,
            'paidCount' => 0,
            'totalBilling' => 0,
            'totalPaid' => 0,
            'totalRemaining' => 0,
        ];

        foreach ($orders as $order) {
            $fin = $this->calculateOrderFinancials($order);

            $stats['totalBilling'] += $fin['grandTotal'];
            $stats['totalPaid'] += $fin['payment'];

            if ($fin['statusCode'] === 'unpaid') {
                $stats['unpaidCount']++;
            } elseif ($fin['statusCode'] === 'partial') {
                $stats['partialCount']++;
            } else {
                $stats['paidCount']++;
            }
        }

        $stats['totalRemaining'] = max(0, $stats['totalBilling'] - $stats['totalPaid']);

        return view($this->view . 'index')
            ->with('view', $this->view)
            ->with('userBank', $userBank)
            ->with('stats', $stats)
            ->with('title', $this->title);
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

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

    /**
     * Format persentase pajak: 11.00 -> "11", 2.50 -> "2.5".
     */
    private function formatPercent($percent): string
    {
        $formatted = number_format((float) $percent, 2, '.', '');

        $formatted = rtrim(rtrim($formatted, '0'), '.');

        return $formatted === '' ? '0' : $formatted;
    }

    /**
     * Hitung kalkulasi tagihan finansial order untuk tabel & statistik.
     */
    private function calculateOrderFinancials($order): array
    {
        $cost = $this->getRouteAmount($order);
        $additionalCost = 0;
        if (isset($order->orderPayment) && isset($order->orderPayment->additional_cost)) {
            $additionalCost = (float) $order->orderPayment->additional_cost;
        } else {
            $additionalCost = (float) $order->cost->filter(fn($c) => strtolower($c->type ?? '') === 'on charge')->sum('nominal');
        }
        $subtotal = $cost + $additionalCost;

        $ppn = 0;
        $ppnPercent = null;
        if (isset($order->orderPayment) && isset($order->orderPayment->ppn)) {
            $ppn = (float) $order->orderPayment->ppn;
            if (isset($order->orderPayment->ppn_percent) && (float) $order->orderPayment->ppn_percent > 0) {
                $ppnPercent = (float) $order->orderPayment->ppn_percent;
            }
        } else {
            if (isset($order->customer->ppn)) {
                $ppnPercent = (float) $order->customer->ppn;
                $ppn = $subtotal * ($ppnPercent / 100);
            }
        }

        $pph = 0;
        $pphPercent = null;
        if (isset($order->orderPayment) && isset($order->orderPayment->pph)) {
            $pph = (float) $order->orderPayment->pph;
            if (isset($order->orderPayment->pph_percent) && (float) $order->orderPayment->pph_percent > 0) {
                $pphPercent = (float) $order->orderPayment->pph_percent;
            }
        } else {
            if (isset($order->customer->pph)) {
                $pphPercent = (float) $order->customer->pph;
                $pph = $subtotal * ($pphPercent / 100);
            }
        }

        $claim = 0;
        $claimDescription = '';
        if (isset($order->orderPayment) && isset($order->orderPayment->claim)) {
            $claim = (float) $order->orderPayment->claim;
            $claimDescription = $order->orderPayment->claim_description ?? '';
        }

        $grandTotal = $subtotal + $ppn - $pph - $claim;
        $payment = isset($order->orderPayment) ? (float) $order->orderPayment->total : 0;
        $remaining = max(0, $grandTotal - $payment);

        $statusCode = 'unpaid';
        $statusLabel = 'Belum Bayar';
        if ($payment > 0) {
            if ($payment == $grandTotal) {
                $statusCode = 'paid';
                $statusLabel = 'Lunas';
            } elseif ($payment > $grandTotal) {
                $statusCode = 'overpaid';
                $statusLabel = 'Kelebihan Bayar';
            } else {
                $statusCode = 'partial';
                $statusLabel = 'Belum Lunas';
            }
        }

        return [
            'cost' => $cost,
            'additionalCost' => $additionalCost,
            'subtotal' => $subtotal,
            'claim' => $claim,
            'claimDescription' => $claimDescription,
            'ppn' => $ppn,
            'ppnPercent' => $ppnPercent,
            'pph' => $pph,
            'pphPercent' => $pphPercent,
            'grandTotal' => $grandTotal,
            'payment' => $payment,
            'remaining' => $remaining,
            'statusCode' => $statusCode,
            'statusLabel' => $statusLabel,
        ];
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->findAllIsDoZero();

            $statusFilter = $request->input('status');
            if ($statusFilter && in_array($statusFilter, ['unpaid', 'partial', 'paid', 'overpaid'])) {
                $data = $data->filter(function ($row) use ($statusFilter) {
                    $fin = $this->calculateOrderFinancials($row);
                    if ($statusFilter === 'unpaid') {
                        return $fin['statusCode'] === 'unpaid';
                    } elseif ($statusFilter === 'partial') {
                        return $fin['statusCode'] === 'partial';
                    } elseif ($statusFilter === 'paid') {
                        return in_array($fin['statusCode'], ['paid', 'overpaid']);
                    } elseif ($statusFilter === 'overpaid') {
                        return $fin['statusCode'] === 'overpaid';
                    }
                    return true;
                })->values();
            }

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('raw_code', function ($row) {
                    return $row->code;
                })
                ->editColumn('code', function ($row) {
                    return '<span class="fw-semibold text-primary font-monospace fs-12 d-inline-flex align-items-center gap-1">
                                <i class="mdi mdi-file-document-outline fs-14"></i>' . e($row->code) . '
                            </span>';
                })
                ->editColumn('orderDate', function ($row) {
                    return '<span class="text-nowrap text-secondary fs-12 font-monospace">' . Carbon::parse($row->orderDate)->format('d/m/Y') . '</span>';
                })
                ->editColumn('fleet.plateNumber', function ($row) {
                    $fleet = $row->fleet->plateNumber ?? null;
                    if (! $fleet) {
                        return '<span class="text-muted">-</span>';
                    }
                    return '<span class="badge bg-light text-dark border px-2 py-1 font-monospace fw-semibold fs-11 text-nowrap">
                                <i class="mdi mdi-truck-outline text-primary me-1"></i>' . e($fleet) . '
                            </span>';
                })
                ->editColumn('driver.name', function ($row) {
                    $driver = $row->driver->name ?? null;
                    if (! $driver) {
                        return '<span class="text-muted">-</span>';
                    }
                    return '<span class="cell-ellipsis text-dark fs-12 fw-medium text-nowrap" style="max-width: 120px;" title="' . e($driver) . '">
                                <i class="mdi mdi-account-circle-outline text-muted fs-13 me-1"></i>' . e($driver) . '
                            </span>';
                })
                ->editColumn('shipmentNumber', function ($row) {
                    $sj = $row->shipmentNumber;
                    if (! $sj) {
                        return '<span class="text-muted">-</span>';
                    }
                    return '<span class="badge bg-light text-secondary border px-2 py-1 font-monospace fs-11 text-nowrap">
                                <span class="cell-ellipsis" style="max-width: 120px;" title="' . e($sj) . '">' . e($sj) . '</span>
                            </span>';
                })
                ->editColumn('customer.name', function ($row) {
                    $customer = $row->customer->name ?? '-';
                    return '<div class="fw-semibold text-dark fs-12 text-nowrap">
                                <span class="cell-ellipsis" style="max-width: 140px;" title="' . e($customer) . '">' . e($customer) . '</span>
                            </div>';
                })
                ->addColumn('rute', function ($row) {
                    $origin = $row->route->originLocation->name ?? '-';
                    $dest = $row->route->destinationLocation->name ?? '-';

                    if ($origin === '-' && $dest === '-') {
                        return '<span class="text-muted">-</span>';
                    }

                    return '<div class="d-inline-flex align-items-center gap-1 text-nowrap fs-12">
                                <span class="cell-ellipsis text-secondary" style="max-width: 95px;" title="' . e($origin) . '">' . e($origin) . '</span>
                                <i class="mdi mdi-arrow-right text-muted fs-12 mx-1 flex-shrink-0"></i>
                                <span class="cell-ellipsis text-secondary" style="max-width: 95px;" title="' . e($dest) . '">' . e($dest) . '</span>
                            </div>';
                })
                ->addColumn('cost', function ($row) {
                    $fin = $this->calculateOrderFinancials($row);
                    return '<span class="font-monospace text-dark fs-12">' . number_format($fin['cost'], 0, ',', '.') . '</span>';
                })
                ->addColumn('additional_cost', function ($row) {
                    $fin = $this->calculateOrderFinancials($row);
                    if ($fin['additionalCost'] > 0) {
                        return '<span class="font-monospace text-warning-emphasis fw-semibold fs-12">+' . number_format($fin['additionalCost'], 0, ',', '.') . '</span>';
                    }
                    return '<span class="text-muted font-monospace fs-12">-</span>';
                })
                ->addColumn('ppn', function ($row) {
                    $fin = $this->calculateOrderFinancials($row);
                    if ($fin['ppn'] > 0) {
                        $label = '+' . number_format($fin['ppn'], 0, ',', '.');
                        if ($fin['ppnPercent'] !== null && $fin['ppnPercent'] > 0) {
                            $label .= ' (' . $this->formatPercent($fin['ppnPercent']) . '%)';
                        }
                        return '<span class="badge bg-success-subtle text-success border border-success-subtle font-monospace fs-11">' . $label . '</span>';
                    }
                    return '<span class="text-muted font-monospace fs-12">-</span>';
                })
                ->addColumn('pph', function ($row) {
                    $fin = $this->calculateOrderFinancials($row);
                    if ($fin['pph'] > 0) {
                        $label = '-' . number_format($fin['pph'], 0, ',', '.');
                        if ($fin['pphPercent'] !== null && $fin['pphPercent'] > 0) {
                            $label .= ' (' . $this->formatPercent($fin['pphPercent']) . '%)';
                        }
                        return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle font-monospace fs-11">' . $label . '</span>';
                    }
                    return '<span class="text-muted font-monospace fs-12">-</span>';
                })
                ->addColumn('claim', function ($row) {
                    $fin = $this->calculateOrderFinancials($row);
                    if ($fin['claim'] > 0) {
                        $desc = $fin['claimDescription'] ? ' title="' . e($fin['claimDescription']) . '"' : '';
                        return '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle font-monospace fs-11"' . $desc . '>-' . number_format($fin['claim'], 0, ',', '.') . '</span>';
                    }
                    return '<span class="text-muted font-monospace fs-12">-</span>';
                })
                ->addColumn('grand_total', function ($row) {
                    $fin = $this->calculateOrderFinancials($row);
                    return '<span class="fw-bold text-dark font-monospace fs-12">' . number_format($fin['grandTotal'], 0, ',', '.') . '</span>';
                })
                ->addColumn('paymentAmount', function ($row) {
                    $fin = $this->calculateOrderFinancials($row);
                    if ($fin['payment'] > 0) {
                        return '<span class="fw-semibold text-success font-monospace fs-12">' . number_format($fin['payment'], 0, ',', '.') . '</span>';
                    }
                    return '<span class="text-muted font-monospace fs-12">0</span>';
                })
                ->addColumn('total', function ($row) {
                    $fin = $this->calculateOrderFinancials($row);
                    if ($fin['remaining'] > 0) {
                        return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle font-monospace fw-bold fs-11">Rp ' . number_format($fin['remaining'], 0, ',', '.') . '</span>';
                    }
                    return '<span class="badge bg-success-subtle text-success border border-success-subtle font-monospace fs-11"><i class="mdi mdi-check me-1"></i>Lunas</span>';
                })
                ->addColumn('paymentStatus', function ($row) {
                    $fin = $this->calculateOrderFinancials($row);
                    if ($fin['statusCode'] === 'unpaid') {
                        return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill fw-semibold fs-11"><i class="mdi mdi-close-circle-outline me-1"></i>Belum Bayar</span>';
                    } elseif ($fin['statusCode'] === 'paid') {
                        return '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill fw-semibold fs-11"><i class="mdi mdi-check-circle-outline me-1"></i>Lunas</span>';
                    } elseif ($fin['statusCode'] === 'overpaid') {
                        return '<span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 rounded-pill fw-semibold fs-11"><i class="mdi mdi-alert-circle-outline me-1"></i>Kelebihan</span>';
                    }
                    return '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 rounded-pill fw-semibold fs-11"><i class="mdi mdi-clock-outline me-1"></i>Belum Lunas</span>';
                })
                ->addColumn('action', function ($row) {
                    $fin = $this->calculateOrderFinancials($row);
                    $isLunas = $fin['statusCode'] === 'paid' || (isset($row->orderPayment->status) && $row->orderPayment->status == 1);

                    $paymentBtn = '';
                    if (! $isLunas) {
                        $paymentBtn = '<button type="button" onclick="showModal(\'' . e($row->code) . '\')"
                                            class="btn btn-sm btn-icon bg-success-subtle text-success hover-scale me-1"
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="Input Pembayaran">
                                            <i class="mdi mdi-credit-card-outline fs-15"></i>
                                       </button>';
                    }

                    $detailBtn = '<button type="button" onclick="showDetailModal(\'' . e($row->code) . '\')"
                                     class="btn btn-sm btn-icon bg-primary-subtle text-primary hover-scale"
                                     data-bs-toggle="tooltip" data-bs-placement="top" title="Rincian Pembayaran">
                                     <i class="mdi mdi-eye-outline fs-15"></i>
                                  </button>';

                    return '<div class="d-inline-flex align-items-center justify-content-center">' . $paymentBtn . $detailBtn . '</div>';
                })
                ->rawColumns([
                    'code', 'orderDate', 'fleet.plateNumber', 'driver.name', 'shipmentNumber',
                    'customer.name', 'rute',
                    'cost', 'additional_cost', 'ppn', 'pph', 'claim', 'grand_total', 'paymentAmount',
                    'total', 'paymentStatus', 'action'
                ])
                ->toJson();
        }
    }

    public function pdfMulti(Request $request)
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

            $claim = isset($order->orderPayment->claim) ? (float) $order->orderPayment->claim : 0;

            $grandTotal = $totalBefore + $ppnAmount - $pphAmount - $claim;

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
