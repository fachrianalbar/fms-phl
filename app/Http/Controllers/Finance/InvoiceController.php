<?php

namespace App\Http\Controllers\Finance;

use App\Helpers\FilterHelper;
use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Models\Finance\Invoice as InvoiceModel;
use App\Services\Finance\InvoiceService;
use App\Services\Master\CustomerService;
use App\Services\MenuService;
use App\Services\UniqueCodeService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Mpdf\Mpdf;
use Yajra\DataTables\DataTables;

class InvoiceController extends Controller
{
    protected $service;

    protected $title;

    protected $view;

    protected $menuSvc;

    protected $customerSvc;

    protected $totalPrice;

    protected $totalPriceInvoice;

    public function __construct(InvoiceService $invoiceSvc, CustomerService $customerSvc, MenuService $menuSvc)
    {
        $this->service = $invoiceSvc;
        $this->title = 'Invoice';
        $this->view = 'invoice.';
        $this->customerSvc = $customerSvc;
        $this->totalPrice = 0;
        $this->totalPriceInvoice = 0;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return redirect()->route('invoice.unpaid');
    }

    /**
     * Display listing of unpaid invoices.
     */
    public function indexUnpaid()
    {
        $unpaidQuery = InvoiceModel::where(function ($q) {
            $q->whereNull('status')->orWhere('status', '!=', InvoiceModel::STATUS_FULL);
        });

        $totalCount = (clone $unpaidQuery)->count();
        $createdCount = (clone $unpaidQuery)->where(function ($q) {
            $q->whereNull('status')->orWhere('status', InvoiceModel::STATUS_CREATE);
        })->count();
        $partialCount = (clone $unpaidQuery)->where('status', InvoiceModel::STATUS_PARTIAL)->count();

        $totals = (clone $unpaidQuery)->selectRaw('
            COALESCE(SUM(invoiceAmount), 0) as sum_invoice,
            COALESCE(SUM(ppnAmount), 0) as sum_ppn,
            COALESCE(SUM(pphAmount), 0) as sum_pph
        ')->first();

        $totalBilling = (float) $totals->sum_invoice + (float) $totals->sum_ppn - (float) $totals->sum_pph;

        $totalPaid = (float) DB::table('invoice_payment')
            ->join('invoice', 'invoice_payment.invoiceCode', '=', 'invoice.code')
            ->whereNull('invoice.deleted_at')
            ->whereNull('invoice_payment.deleted_at')
            ->where(function ($q) {
                $q->whereNull('invoice.status')->orWhere('invoice.status', '!=', InvoiceModel::STATUS_FULL);
            })
            ->sum('invoice_payment.amount');

        $totalClaim = (float) DB::table('invoice_payment_claim')
            ->join('invoice', 'invoice_payment_claim.invoiceCode', '=', 'invoice.code')
            ->whereNull('invoice.deleted_at')
            ->whereNull('invoice_payment_claim.deleted_at')
            ->where(function ($q) {
                $q->whereNull('invoice.status')->orWhere('invoice.status', '!=', InvoiceModel::STATUS_FULL);
            })
            ->sum('invoice_payment_claim.amount');

        $totalRemaining = $totalBilling - $totalPaid - $totalClaim;

        $stats = [
            'totalCount' => $totalCount,
            'totalBilling' => $totalBilling,
            'totalPaid' => $totalPaid,
            'totalClaim' => $totalClaim,
            'totalRemaining' => $totalRemaining,
            'partialCount' => $partialCount,
            'createdCount' => $createdCount,
        ];

        return view('invoice.unpaid')
            ->with('view', 'invoice.')
            ->with('title', 'Unpaid Invoice')
            ->with('stats', $stats);
    }

    /**
     * Display listing of paid invoices.
     */
    public function indexPaid()
    {
        $paidQuery = InvoiceModel::where('status', InvoiceModel::STATUS_FULL);
        $totalCount = (clone $paidQuery)->count();
        $totals = (clone $paidQuery)->selectRaw('
            COALESCE(SUM(invoiceAmount), 0) as sum_invoice,
            COALESCE(SUM(ppnAmount), 0) as sum_ppn,
            COALESCE(SUM(pphAmount), 0) as sum_pph
        ')->first();
        $totalBilling = (float) $totals->sum_invoice + (float) $totals->sum_ppn - (float) $totals->sum_pph;

        $stats = [
            'totalCount' => $totalCount,
            'totalBilling' => $totalBilling,
        ];

        return view('invoice.paid')
            ->with('view', 'invoice.')
            ->with('title', 'Paid Invoice')
            ->with('stats', $stats);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customer = $this->customerSvc->findAll();

        return view('invoice.create')
            ->with('view', 'invoice.')
            ->with('customer', $customer)
            ->with('title', 'Create Invoice');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $selectedOrders = json_decode($request->input('selectedOrders'), true);
        $selectedOrders = is_array($selectedOrders) ? $selectedOrders : [];

        $validator = Validator::make([
            'customerCode' => $request->customerCode,
            'invoiceNumber' => $request->invoiceNumber,
            'selectedOrders' => $selectedOrders,
        ], [
            'customerCode' => 'required',
            'invoiceNumber' => 'required',
            'selectedOrders' => 'required|array|min:1',
        ]);
        if ($validator->fails()) {
            return redirect()->route('invoice.create')->with('fail', $validator->errors()->all()[0]);
        }
        try {
            $code = app(UniqueCodeService::class)->runWithDuplicateRetry(function () use ($request, $selectedOrders) {
                return DB::transaction(fn () => $this->service->store($request, $this->title, $selectedOrders));
            });

            $redirect = redirect()->route('invoice.unpaid')
                ->with('success', 'Faktur ' . __('general.data_was_save_successfully'));

            return $code->wasChanged ? $redirect->with('code_replaced', $code->flashPayload()) : $redirect;
        } catch (\Throwable $th) {
            return redirect()->route('invoice.create')->with('fail', 'Line : ' . $th->getLine() . '<br>' . $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data = $this->service->getById($id);

        if (! $data) {
            return redirect()->route('invoice.unpaid')->with('fail', 'Data not found');
        }

        $customer = $this->customerSvc->findAll();
        $customerData = $this->customerSvc->getByCode($data->customerCode);
        $order = $this->service->getOrderDetail($id);

        $status = 0;
        if (count($data->payments) > 0) {
            $status = 1;
        }
        // invoiceStatus is the numeric status for invoice
        $invoiceStatus = (int) ($data->status ?? 1);

        return view('invoice.edit')
            ->with('view', 'invoice.')
            ->with('title', 'Edit Invoice')
            ->with('customer', $customer)
            ->with('order', $order)
            ->with('customerData', $customerData)
            ->with('status', $status)
            ->with('invoiceStatus', $invoiceStatus)
            ->with('data', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            // 'customerCode' => 'required',
            'invoiceNumber' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->route('invoice.edit', $id)->with('fail', $validator->errors()->all()[0]);
        }
        try {
            DB::beginTransaction();

            $this->service->update($request, $id, $this->title);

            DB::commit();

            return redirect()->route('invoice.unpaid')->with('success', 'Faktur ' . __('general.data_was_update_succesfully'));
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->route('invoice.edit', $id)->with('fail', 'Line : ' . $th->getLine() . '<br>' . $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->service->destroy($id, $this->title);

        return redirect()->route('invoice.unpaid')->with('success', 'Delete Data Success');
    }

    /**
     * Recalculate invoice amount and PPN
     */
    public function recalculate(Request $request, string $id)
    {
        try {
            DB::beginTransaction();

            $result = $this->service->recalculate($id);

            DB::commit();

            // Jika request dari AJAX (dari list), return JSON
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Invoice amount recalculated successfully. Invoice Amount: Rp ' . number_format($result['invoiceAmount'], 0, ',', '.') . ', PPN: Rp ' . number_format($result['ppnAmount'], 0, ',', '.') . '. Semua pembayaran untuk invoice ini telah dibatalkan.',
                    'invoiceAmount' => $result['invoiceAmount'],
                    'ppnAmount' => $result['ppnAmount'],
                    'total' => $result['total'],
                ]);
            }

            // Jika request dari form (dari edit page), redirect dengan message
            $message = 'Invoice amount recalculated successfully. Invoice Amount: Rp ' . number_format($result['invoiceAmount'], 0, ',', '.') . ', PPN: Rp ' . number_format($result['ppnAmount'], 0, ',', '.') . '. <br><br><strong>Semua pembayaran untuk invoice ini telah dibatalkan.</strong> Silakan input ulang pembayaran invoice.';

            return redirect()->route('invoice.edit', $id)->with('success', $message);
        } catch (\Throwable $th) {
            DB::rollback();

            // Jika request dari AJAX, return JSON error
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Line : ' . $th->getLine() . ' - ' . $th->getMessage(),
                ], 400);
            }

            return redirect()->back()->with('fail', 'Line : ' . $th->getLine() . '<br>' . $th->getMessage());
        }
    }

    public function datatableUnpaid(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->findUnpaid();

            return $this->formatInvoiceDatatable($data, false);
        }
    }

    public function datatablePaid(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->findPaid();

            return $this->formatInvoiceDatatable($data, true);
        }
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->findAll();

            return $this->formatInvoiceDatatable($data, false);
        }
    }

    private function formatInvoiceDatatable($data, bool $isPaidOnly)
    {
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('orderCount', function ($row) {
                $count = $row->details ? $row->details->count() : 0;
                return '<span class="badge bg-light text-primary border border-primary-subtle rounded-pill px-2 py-1 fw-semibold fs-11"><i class="mdi mdi-truck-fast me-1"></i>' . $count . ' SJ</span>';
            })
            ->editColumn('DT_RowIndex', function ($row) {
                return '<span class="text-muted fw-semibold fs-12">' . ($row->DT_RowIndex ?? '') . '</span>';
            })
            ->editColumn('invoiceNumber', function ($row) {
                $number = htmlspecialchars($row->invoiceNumber ?? '-');
                return '<span class="font-monospace fw-bold text-primary fs-13 text-nowrap">' . $number . '</span>';
            })
            ->editColumn('customer.name', function ($row) {
                $customer = isset($row->customer->name) ? $row->customer->name : '-';
                $code = $row->customerCode ? '<div class="text-muted font-monospace fs-11"><i class="mdi mdi-account-outline me-1"></i>' . htmlspecialchars($row->customerCode) . '</div>' : '';
                return '<div class="text-start"><span class="fw-semibold text-dark fs-13 d-block text-truncate" style="max-width: 220px;" title="' . htmlspecialchars($customer) . '">' . htmlspecialchars($customer) . '</span>' . $code . '</div>';
            })
            ->editColumn('invoiceDate', function ($row) {
                if (! $row->invoiceDate) {
                    return '<span class="text-muted">-</span>';
                }
                $formatted = Carbon::parse($row->invoiceDate)->format('d M Y');
                $dueInfo = '';
                if ($row->overdueDate) {
                    $dueInfo = '<div class="text-muted fs-11"><i class="mdi mdi-calendar-clock me-1 text-warning"></i>Jth: ' . Carbon::parse($row->overdueDate)->format('d M Y') . '</div>';
                }
                return '<div class="text-start text-nowrap"><span class="fw-medium text-dark fs-12">' . $formatted . '</span>' . $dueInfo . '</div>';
            })
            ->addColumn('price', function ($row) {
                $subtotal = (float) ($row->invoiceAmount ?? 0);
                $totalRoute = 0;
                $totalOnCharge = 0;
                $onChargeSummary = [];
                $ordersList = [];

                if (isset($row->details)) {
                    foreach ($row->details as $detail) {
                        $order = $detail->order;
                        if ($order) {
                            $routeAmt = (float) ($order->routeAmount ?? $order->price ?? 0);
                            $totalRoute += $routeAmt;
                            $orderOnCharge = 0;
                            $orderCosts = [];
                            if (isset($order->cost)) {
                                foreach ($order->cost as $c) {
                                    if (isset($c->type) && strtolower($c->type) === 'on charge') {
                                        $nom = (float) $c->nominal;
                                        $orderOnCharge += $nom;
                                        $cName = $c->costComponent->name ?? ($c->description ?? 'Biaya Tambahan');
                                        $onChargeSummary[$cName] = ($onChargeSummary[$cName] ?? 0) + $nom;
                                        $orderCosts[] = [
                                            'component' => $cName,
                                            'nominal' => $nom,
                                            'nominalFormatted' => 'Rp ' . number_format($nom, 0, ',', '.'),
                                        ];
                                    }
                                }
                            }
                            $totalOnCharge += $orderOnCharge;
                            $ordersList[] = [
                                'code' => $order->code,
                                'shipment' => $order->shipmentNumber ?? $order->code,
                                'route' => ($order->route && $order->route->originLocation ? $order->route->originLocation->name : '-') . ' ➔ ' . ($order->route && $order->route->destinationLocation ? $order->route->destinationLocation->name : '-'),
                                'plate' => $order->fleet->plateNumber ?? '-',
                                'basePrice' => $routeAmt,
                                'basePriceFormatted' => 'Rp ' . number_format($routeAmt, 0, ',', '.'),
                                'onCharge' => $orderOnCharge,
                                'onChargeFormatted' => 'Rp ' . number_format($orderOnCharge, 0, ',', '.'),
                                'costs' => $orderCosts,
                                'total' => $routeAmt + $orderOnCharge,
                                'totalFormatted' => 'Rp ' . number_format($routeAmt + $orderOnCharge, 0, ',', '.'),
                            ];
                        }
                    }
                }

                $html = '<div class="text-end">';
                $html .= '<span class="fw-bold text-dark fs-13">Rp ' . number_format($subtotal, 0, ',', '.') . '</span>';

                if ($totalOnCharge > 0) {
                    $breakdownData = [
                        'invoiceNumber' => $row->invoiceNumber ?? '-',
                        'customerName' => $row->customer->name ?? '-',
                        'invoiceDate' => $row->invoiceDate ? Carbon::parse($row->invoiceDate)->format('d-M-Y') : '-',
                        'totalRoute' => $totalRoute,
                        'totalRouteFormatted' => 'Rp ' . number_format($totalRoute, 0, ',', '.'),
                        'totalOnCharge' => $totalOnCharge,
                        'totalOnChargeFormatted' => 'Rp ' . number_format($totalOnCharge, 0, ',', '.'),
                        'subtotal' => $subtotal,
                        'subtotalFormatted' => 'Rp ' . number_format($subtotal, 0, ',', '.'),
                        'ppn' => (float) ($row->ppnAmount ?? 0),
                        'ppnFormatted' => 'Rp ' . number_format((float) ($row->ppnAmount ?? 0), 0, ',', '.'),
                        'pph' => (float) ($row->pphAmount ?? 0),
                        'pphFormatted' => 'Rp ' . number_format((float) ($row->pphAmount ?? 0), 0, ',', '.'),
                        'grandTotal' => $subtotal + (float) ($row->ppnAmount ?? 0) - (float) ($row->pphAmount ?? 0),
                        'grandTotalFormatted' => 'Rp ' . number_format($subtotal + (float) ($row->ppnAmount ?? 0) - (float) ($row->pphAmount ?? 0), 0, ',', '.'),
                        'components' => $onChargeSummary,
                        'orders' => $ordersList,
                    ];
                    $jsonAttr = htmlspecialchars(json_encode($breakdownData), ENT_QUOTES, 'UTF-8');

                    $html .= '<br><button type="button" class="btn btn-xs btn-outline-warning border-warning-subtle py-0 px-2 mt-1 rounded-pill btn-view-invoice-breakdown" data-breakdown=\'' . $jsonAttr . '\' title="Klik untuk rincian biaya On Charge">';
                    $html .= '<i class="mdi mdi-cash-multiple me-1"></i>+ On Charge: Rp ' . number_format($totalOnCharge, 0, ',', '.');
                    $html .= '</button>';
                }

                $html .= '</div>';
                return $html;
            })
            ->addColumn('ppn', function ($row) {
                $ppnAmount = (float) ($row->ppnAmount ?? 0);
                if ($ppnAmount > 0) {
                    return '<div class="text-end font-monospace fs-12 text-dark fw-medium">Rp ' . number_format($ppnAmount, 0, ',', '.') . '</div>';
                }
                return '<div class="text-end text-muted font-monospace fs-12 opacity-50">-</div>';
            })
            ->addColumn('pph', function ($row) {
                $pphAmount = (float) ($row->pphAmount ?? 0);
                if ($pphAmount > 0) {
                    return '<div class="text-end font-monospace fs-12 text-danger fw-medium">- Rp ' . number_format($pphAmount, 0, ',', '.') . '</div>';
                }
                return '<div class="text-end text-muted font-monospace fs-12 opacity-50">-</div>';
            })
            ->addColumn('totalBilling', function ($row) {
                $total = (float) ($row->invoiceAmount ?? 0) + (float) ($row->ppnAmount ?? 0) - (float) ($row->pphAmount ?? 0);
                $totalPaid = (float) ($row->payments->sum('amount') ?? 0);
                $totalClaim = (float) ($row->claims->sum('amount') ?? 0);
                $remaining = $total - $totalPaid - $totalClaim;

                $html = '<div class="text-end">';
                $html .= '<span class="fw-bold text-dark fs-13">Rp ' . number_format($total, 0, ',', '.') . '</span>';
                if ($remaining > 0 && ($totalPaid > 0 || $totalClaim > 0)) {
                    $html .= '<div class="text-danger fs-11 fw-semibold mt-0" title="Sisa Belum Dibayar"><i class="mdi mdi-alert-circle-outline me-1"></i>Sisa: Rp ' . number_format($remaining, 0, ',', '.') . '</div>';
                }
                $html .= '</div>';
                return $html;
            })
            ->addColumn('status', function ($row) {
                $status = (int) ($row->status ?? InvoiceModel::STATUS_CREATE);
                if ($status === InvoiceModel::STATUS_FULL) {
                    return '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 fw-semibold fs-11"><i class="mdi mdi-check-circle me-1"></i>Lunas</span>';
                } elseif ($status === InvoiceModel::STATUS_PARTIAL) {
                    return '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-1 fw-semibold fs-11"><i class="mdi mdi-clock-check-outline me-1"></i>Sebagian</span>';
                } else {
                    return '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-1 fw-semibold fs-11"><i class="mdi mdi-file-document-outline me-1"></i>Belum Bayar</span>';
                }
            })
            ->addColumn('action', function ($row) use ($isPaidOnly) {
                $status = (int) ($row->status ?? InvoiceModel::STATUS_CREATE);
                $totalBilling = (float) ($row->invoiceAmount ?? 0) + (float) ($row->ppnAmount ?? 0) - (float) ($row->pphAmount ?? 0);
                $totalPaid = (float) ($row->payments->sum('amount') ?? 0);
                $totalClaim = (float) ($row->claims->sum('amount') ?? 0);
                $remaining = $totalBilling - $totalPaid - $totalClaim;

                $btn = '<div class="d-inline-flex align-items-center gap-1">
                        <a target="_blank" href="' . route('invoice.pdf', $row->id) . '"
                        class="btn btn-icon btn-sm bg-success-subtle text-success border border-success-subtle hover-scale"
                        data-bs-toggle="tooltip" title="Cetak PDF">
                            <i class="mdi mdi-printer-outline fs-14"></i>
                        </a>';

                // Riwayat pembayaran (DP/cicilan/claim) — hanya jika pernah ada pembayaran/claim
                if (count($row->payments) > 0 || count($row->claims) > 0) {
                    $btn .= '
                        <a href="javascript:void(0)"
                        class="btn btn-icon btn-sm bg-info-subtle text-info border border-info-subtle hover-scale btn-payment-history"
                        data-bs-toggle="tooltip" title="Riwayat Pembayaran (DP/Cicilan)"
                        data-id="' . $row->id . '"
                        data-invoice-number="' . htmlspecialchars($row->invoiceNumber ?? '-') . '">
                            <i class="mdi mdi-cash-clock fs-14"></i>
                        </a>';
                }

                if (! $isPaidOnly) {
                    // Tombol edit hanya muncul jika belum full payment
                    if ($status !== InvoiceModel::STATUS_FULL) {
                        $btn .= '
                            <a href="' . route('invoice.edit', $row->id) . '"
                            class="btn btn-icon btn-sm bg-primary-subtle text-primary border border-primary-subtle hover-scale"
                            data-bs-toggle="tooltip" title="Edit Faktur">
                                <i class="mdi mdi-pencil-outline fs-14"></i>
                            </a>';

                        $btn .= '
                            <a href="javascript:void(0)" 
                            class="btn btn-icon btn-sm bg-secondary-subtle text-secondary border border-secondary-subtle hover-scale btn-suggest-number"
                            data-bs-toggle="tooltip" title="Saran Nomor Baru"
                            data-id="' . $row->id . '"
                            data-invoice-number="' . htmlspecialchars($row->invoiceNumber) . '">
                                <i class="mdi mdi-auto-fix fs-14"></i>
                            </a>';
                    }
                }

                // Tombol recalculate
                $btn .= '
                    <a href="javascript:recalculateInvoice(\'' . $row->id . '\')"
                    class="btn btn-icon btn-sm bg-warning-subtle text-warning-emphasis border border-warning-subtle hover-scale"
                    data-bs-toggle="tooltip" title="Hitung Ulang Nilai Faktur">
                        <i class="mdi mdi-calculator fs-14"></i>
                    </a>';

                // Tombol delete hanya muncul jika belum ada pembayaran dan belum lunas
                if (! $isPaidOnly && count($row->payments) == 0) {
                    $btn .= '
                        <a href="javascript:deleteData(\'' . $row->id . '\')"
                        class="btn btn-icon btn-sm bg-danger-subtle text-danger border border-danger-subtle hover-scale"
                        data-bs-toggle="tooltip" title="Hapus Faktur">
                            <i class="mdi mdi-trash-can-outline fs-14"></i>
                        </a>';
                }

                $btn .= '</div>';

                return $btn;
            })
            ->rawColumns(['action', 'DT_RowIndex', 'invoiceNumber', 'orderCount', 'customer.name', 'invoiceDate', 'price', 'ppn', 'pph', 'totalBilling', 'status'])
            ->toJson();
    }

    public function datatableOrder(Request $request)
    {
        if ($request->ajax()) {
            $customerCode = $request->customerCode;

            if (empty($customerCode)) {
                return DataTables::of(collect([]))->toJson();
            }

            $data = $this->service->getOrder()
                ->whereHas('customer', function ($q) use ($customerCode) {
                    $q->where('code', $customerCode);
                });

            return DataTables::of($data->get())
                ->addIndexColumn()
                ->editColumn('fleet.plateNumber', function ($row) {
                    $fleet = '';

                    if ($row->fleet && isset($row->fleet->plateNumber)) {
                        $fleet = $row->fleet->plateNumber;
                    }

                    return $fleet;
                })
                ->editColumn('route.originLocation.name', function ($row) {
                    $origin = '';

                    if ($row->route && $row->route->originLocation && isset($row->route->originLocation->name)) {
                        $origin = $row->route->originLocation->name;
                    }

                    return $origin;
                })

                ->editColumn('orderType.name', function ($row) {
                    $type = '';

                    if ($row->orderType && isset($row->orderType->name)) {
                        $type = $row->orderType->name;
                    }

                    return $type;
                })
                ->editColumn('route.destinationLocation.name', function ($row) {
                    $destination = '';

                    if ($row->route && $row->route->destinationLocation && isset($row->route->destinationLocation->name)) {
                        $destination = $row->route->destinationLocation->name;
                    }

                    return $destination;
                })
                ->addColumn('basePrice', function ($row) {
                    $basePrice = (float) ($row->routeAmount ?? $row->price ?? 0);
                    return '<span class="text-dark fw-medium">Rp ' . number_format($basePrice, 0, ',', '.') . '</span>';
                })
                ->addColumn('addCost', function ($row) {
                    $onChargeCost = 0;
                    $onChargeItems = [];
                    if (isset($row->cost)) {
                        foreach ($row->cost as $item) {
                            if (isset($item->type) && strtolower($item->type) === 'on charge') {
                                $nom = (float) $item->nominal;
                                $onChargeCost += $nom;
                                $onChargeItems[] = [
                                    'component' => $item->costComponent->name ?? ($item->description ?? 'Biaya Tambahan'),
                                    'nominal' => $nom,
                                    'nominalFormatted' => 'Rp ' . number_format($nom, 0, ',', '.'),
                                    'description' => $item->description ?? '',
                                ];
                            }
                        }
                    }

                    if ($onChargeCost > 0) {
                        $jsonCosts = htmlspecialchars(json_encode($onChargeItems), ENT_QUOTES, 'UTF-8');
                        $shipment = $row->shipmentNumber ?? $row->code;
                        $basePrice = (float) ($row->routeAmount ?? $row->price ?? 0);

                        $html = '<div class="d-flex flex-column align-items-end">';
                        $html .= '<span class="badge bg-warning-subtle text-warning border border-warning-subtle fw-semibold mb-1">+ Rp ' . number_format($onChargeCost, 0, ',', '.') . '</span>';
                        $html .= '<button type="button" class="btn btn-xs btn-outline-info py-0 px-2 btn-order-cost-detail" '
                            . 'data-code="' . $row->code . '" '
                            . 'data-shipment="' . $shipment . '" '
                            . 'data-base-price="' . number_format($basePrice, 0, ',', '.') . '" '
                            . 'data-on-charge="' . number_format($onChargeCost, 0, ',', '.') . '" '
                            . 'data-total="' . number_format($basePrice + $onChargeCost, 0, ',', '.') . '" '
                            . 'data-costs=\'' . $jsonCosts . '\' title="Lihat Rincian Biaya On Charge">';
                        $html .= '<i class="mdi mdi-receipt-text-outline me-1"></i>' . count($onChargeItems) . ' Rincian';
                        $html .= '</button>';
                        $html .= '</div>';
                        return $html;
                    }

                    return '<span class="text-muted fs-12">-</span>';
                })
                ->addColumn('totalPrice', function ($row) {
                    $onChargeCost = 0;
                    if (isset($row->cost)) {
                        foreach ($row->cost as $item) {
                            if (isset($item->type) && strtolower($item->type) === 'on charge') {
                                $onChargeCost += (float) $item->nominal;
                            }
                        }
                    }
                    $basePrice = (float) ($row->routeAmount ?? $row->price ?? 0);
                    $orderPrice = $basePrice + $onChargeCost;

                    return '<span class="fw-bold text-primary fs-13">Rp ' . number_format($orderPrice, 0, ',', '.') . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $onChargeCost = 0;
                    $onChargeItems = [];
                    if (isset($row->cost)) {
                        foreach ($row->cost as $item) {
                            if (isset($item->type) && strtolower($item->type) === 'on charge') {
                                $nom = (float) $item->nominal;
                                $onChargeCost += $nom;
                                $onChargeItems[] = [
                                    'component' => $item->costComponent->name ?? ($item->description ?? 'Biaya Tambahan'),
                                    'nominal' => $nom,
                                    'nominalFormatted' => 'Rp ' . number_format($nom, 0, ',', '.'),
                                    'description' => $item->description ?? '',
                                ];
                            }
                        }
                    }
                    $basePrice = (float) ($row->routeAmount ?? $row->price ?? 0);
                    $orderPrice = $basePrice + $onChargeCost;
                    $costsJson = htmlspecialchars(json_encode($onChargeItems), ENT_QUOTES, 'UTF-8');

                    $btn = '<input class="order-checkbox form-check-input" type="checkbox" name="order[]" '
                        . 'data-id="' . $row->code . '" '
                        . 'data-price="' . $orderPrice . '" '
                        . 'data-base-price="' . $basePrice . '" '
                        . 'data-on-charge="' . $onChargeCost . '" '
                        . 'data-costs=\'' . $costsJson . '\' '
                        . 'data-shipment="' . ($row->shipmentNumber ?? $row->code) . '" '
                        . 'data-plate="' . ($row->fleet->plateNumber ?? '-') . '" '
                        . 'value="' . $row->code . '">';

                    return $btn;
                })
                ->editColumn('orderDate', function ($row) {
                    return Carbon::parse($row->orderDate)->format('d-M-Y');
                })
                ->rawColumns(['action', 'orderDate', 'fleet.plateNumber', 'route.originLocation.name', 'route.destinationLocation.name', 'orderType.name', 'basePrice', 'addCost', 'totalPrice'])
                ->toJson();
        }
    }

    public function storeInvoiceDetail(Request $request, $id)
    {
        $selectedOrders = json_decode($request->input('selectedOrders'), true);
        $selectedOrders = is_array($selectedOrders) ? $selectedOrders : [];

        $validator = Validator::make([
            'selectedOrders' => $selectedOrders,
        ], [
            'selectedOrders' => 'required|array|min:1',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('fail', $validator->errors()->all()[0]);
        }

        try {
            DB::beginTransaction();

            $this->service->storeInvoiceDetail($request, $id, $selectedOrders);

            DB::commit();

            return redirect()->back()->with('success', $this->title . ' ' . __('general.data_was_save_successfully'));
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->back()->with('fail', 'Line : ' . $th->getLine() . '<br>' . $th->getMessage());
        }
    }

    public function destroyInvoiceDetail($id)
    {
        $this->service->destroyInvoiceDetail($id, $this->title);

        return redirect()->back()->with('success', 'Delete Order Data Success');
    }

    public function pdfInvoice($id)
    {
        $data = $this->service->getById($id);

        if (! $data) {
            return redirect()->route('invoice.unpaid')->with('fail', 'Data not found');
        }

        $company = CompanySetting::first();

        // Get invoice details with related data, sorted by order date
        $invoiceDetails = $data->details()->with([
            'order.orderMaterial.material',
            'order.orderMaterial.unit',
            'order.cost',
            'order.customer',
            'order.fleet',
            'order.driver',
            'order.route.originLocation',
            'order.route.destinationLocation',
        ])->get()->sortBy(function ($detail) {
            return $detail->order->orderDate ?? '';
        })->values();

        // Set sorted details on data so templates using $data->details get sorted data
        $data->setRelation('details', $invoiceDetails);

        // Tentukan template PDF berdasarkan customer invoicePdf field
        $customer = $data->customer;
        $pdfTemplate = 'finance.invoice.pdf.general-phl'; // Default template

        // pribadi
        if ($customer->company->format == 'P') {
            $pdfTemplate = 'finance.invoice.pdf.pribadi';
        }

        // wijaya trans
        if ($customer->company->format == 'WTMS' || $customer->company->format == 'WT') {
            $pdfTemplate = 'finance.invoice.pdf.general-wt';
        }

        if ($customer && $customer->invoicePdf) {
            $pdfTemplatePath = 'finance.invoice.pdf.customer.' . $customer->invoicePdf;

            // Cek apakah view-nya ada, kalau tidak gunakan default general
            if (view()->exists($pdfTemplatePath)) {
                $pdfTemplate = $pdfTemplatePath;
            } else {
                $pdfTemplate = 'finance.invoice.pdf.general';
            }
        }

        $orientation = 'P'; // Default portrait
        if ($pdfTemplate == 'finance.invoice.pdf.customer.guna-layan-kuasa') {
            $orientation = 'L'; // Landscape for specific template
        }

        $mpdf = new Mpdf(
            [
                'orientation' => $orientation,
                'format' => [215, 330],
                'tempDir' => storage_path('app/mpdf-temp'),
            ]
        );

        $mpdf->setAutoTopMargin = 'stretch';
        $mpdf->setAutoBottomMargin = 'stretch';

        $mpdf->WriteHTML(
            view($pdfTemplate)
                ->with('data', $data)
                ->with('company', $company)
                ->with('invoiceDetails', $invoiceDetails)
                ->with('customer', $customer)
        );

        return $mpdf->Output('Invoice-' . $data->invoiceNumber . '.pdf', 'I');
    }

    public function customerInvoice($customerCode)
    {
        return $this->customerSvc->getByCode($customerCode);
    }

    public function invoiceNumberFormat($id)
    {
        $invoiceDate = request()->query('invoiceDate');
        $data = $this->service->invoiceNumberFormat($id, $invoiceDate);

        return $data;
    }

    // Catatan: proses pembayaran invoice (method processPayment + route
    // invoice/{id}/payment) telah dihapus. Pembayaran kini dilakukan lewat menu
    // "Transaksi Pembayaran" (multi invoice + claim dalam satu transaksi).

    /**
     * Riwayat pembayaran satu faktur (DP / cicilan / pelunasan / claim) — JSON
     * untuk modal riwayat di halaman unpaid & paid.
     */
    public function paymentHistory($id)
    {
        $invoice = InvoiceModel::with([
            'customer',
            'payments' => function ($q) {
                $q->orderBy('paymentDate', 'asc')->orderBy('created_at', 'asc');
            },
            'payments.userBank.bank',
            'payments.transaction',
            'claims' => function ($q) {
                $q->orderBy('created_at', 'asc');
            },
            'claims.transaction',
        ])->whereNull('deleted_at')->find($id);

        if (! $invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Faktur tidak ditemukan',
            ], 404);
        }

        $billing = (float) (($invoice->invoiceAmount ?? 0) + ($invoice->ppnAmount ?? 0) - ($invoice->pphAmount ?? 0));
        $totalPaid = (float) $invoice->payments->sum('amount');
        $totalClaim = (float) $invoice->claims->sum('amount');

        $payments = $invoice->payments->values()->map(function ($payment, $i) {
            $transaction = $payment->transaction;
            $bank = '-';
            if ($payment->userBank) {
                $bank = ($payment->userBank->bank->name ?? 'Bank').' • '.($payment->userBank->accountNumber ?? '-');
            }

            return [
                'seq' => $i + 1,
                'date' => $payment->paymentDate ? Carbon::parse($payment->paymentDate)->format('d M Y') : '-',
                'amount' => (float) $payment->amount,
                'bank' => $bank,
                'description' => $payment->description,
                'transactionCode' => $transaction->code ?? null,
                'transactionUrl' => $transaction ? route('invoice.payment-transaction.show', $transaction->id) : null,
                'receiptUrl' => $payment->paymentReceipt ? Storage::disk('public')->url('invoice-payment/'.$payment->paymentReceipt) : null,
            ];
        });

        $claims = $invoice->claims->values()->map(function ($claim) {
            $trxDate = $claim->transaction->paymentDate ?? null;

            return [
                'date' => $trxDate
                    ? Carbon::parse($trxDate)->format('d M Y')
                    : ($claim->created_at ? Carbon::parse($claim->created_at)->format('d M Y') : '-'),
                'amount' => (float) $claim->amount,
                'description' => $claim->description,
                'transactionCode' => $claim->transaction->code ?? null,
                'transactionUrl' => $claim->transaction ? route('invoice.payment-transaction.show', $claim->transaction->id) : null,
            ];
        });

        return response()->json([
            'success' => true,
            'invoiceNumber' => $invoice->invoiceNumber ?: $invoice->code,
            'customerName' => $invoice->customer->name ?? ($invoice->customerCode ?? '-'),
            'invoiceDate' => $invoice->invoiceDate ? Carbon::parse($invoice->invoiceDate)->format('d M Y') : '-',
            'status' => (int) ($invoice->status ?? InvoiceModel::STATUS_CREATE),
            'billing' => $billing,
            'totalPaid' => $totalPaid,
            'totalClaim' => $totalClaim,
            'remaining' => max($billing - $totalPaid - $totalClaim, 0),
            'payments' => $payments,
            'claims' => $claims,
        ]);
    }

    public function updateInvoiceNumber(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'invoiceNumber' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $code = app(UniqueCodeService::class)->runWithDuplicateRetry(function () use ($id, $request) {
                return DB::transaction(fn () => $this->service->updateInvoiceNumber($id, $request->invoiceNumber));
            });

            return response()->json([
                'success' => true,
                'message' => $code->wasChanged
                    ? "Nomor invoice yang dimasukkan sudah pernah digunakan. Sistem otomatis menggunakan {$code->resolvedCode}."
                    : 'Nomor invoice berhasil diperbarui.',
                'meta' => [
                    'code_changed' => $code->wasChanged,
                    'requested_code' => $code->requestedCode,
                    'resolved_code' => $code->resolvedCode,
                ],
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $th->getMessage(),
            ], 500);
        }
    }

    public function suggestInvoiceNumber($id)
    {
        try {
            $suggested = $this->service->getSuggestedInvoiceNumber($id);
            return response()->json([
                'success' => true,
                'suggestedNumber' => $suggested,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $th->getMessage(),
            ], 500);
        }
    }

    public function recalculateAll(Request $request)
    {
        if (auth()->user()->roleCode !== 'SPRADMIN') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya System Administrator yang diizinkan untuk melakukan tindakan ini.'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $invoices = $this->service->findAll();
            $count = 0;

            foreach ($invoices as $invoice) {
                // Sync usePpn and usePph to customer defaults for recalculate all
                $usePpn = $invoice->usePpn || (isset($invoice->customer->ppn) && $invoice->customer->ppn > 0);
                $usePph = $invoice->usePph || (isset($invoice->customer->pph) && $invoice->customer->pph > 0);

                InvoiceModel::where('id', $invoice->id)->update([
                    'usePpn' => $usePpn,
                    'usePph' => $usePph,
                ]);

                $invoice->usePpn = $usePpn;
                $invoice->usePph = $usePph;

                $totals = $this->service->calculateInvoiceAmount($invoice);
                
                InvoiceModel::where('id', $invoice->id)->update([
                    'invoiceAmount' => $totals['subtotal'],
                    'ppnAmount' => $totals['ppn'],
                    'pphAmount' => $totals['pph'],
                ]);

                // Update invoice status based on new totals and existing payments
                $sumPayments = (int) $invoice->payments()->sum('amount');
                $invoiceTotal = (int) $totals['total'];
                $nextStatus = InvoiceModel::STATUS_CREATE;
                if ($invoiceTotal > 0 && $sumPayments >= $invoiceTotal) {
                    $nextStatus = InvoiceModel::STATUS_FULL;
                } elseif ($sumPayments > 0) {
                    $nextStatus = InvoiceModel::STATUS_PARTIAL;
                }
                
                InvoiceModel::where('id', $invoice->id)->update([
                    'status' => $nextStatus
                ]);

                $count++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil menghitung ulang {$count} invoice.",
            ]);
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Error di baris ' . $th->getLine() . ': ' . $th->getMessage(),
            ], 500);
        }
    }
}
