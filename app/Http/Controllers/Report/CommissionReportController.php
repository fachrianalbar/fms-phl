<?php

namespace App\Http\Controllers\Report;

use App\Helpers\GenerateCode;
use App\Http\Controllers\Controller;
use App\Models\Master\CostComponent;
use App\Models\Master\Customer;
use App\Models\Operational\Order;
use App\Models\Operational\OrderCommission;
use App\Models\Report\CommissionReport;
use App\Models\Report\CommissionReportDetail;
use App\Services\Operational\OrderCommissionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Mpdf\Mpdf;
use Yajra\DataTables\DataTables;

class CommissionReportController extends Controller
{
    protected string $title;

    protected string $view;

    public function __construct()
    {
        $this->title = 'Laporan Komisi';
        $this->view = 'report.commission.';
    }

    /**
     * Display listing of processed commission reports.
     */
    public function index()
    {
        $commissionComponents = OrderCommissionService::commissionComponentQuery()
            ->orderBy('name')
            ->get();

        $customers = Customer::orderBy('name')->get();

        return view($this->view . 'index')
            ->with('view', $this->view)
            ->with('commissionComponents', $commissionComponents)
            ->with('customers', $customers)
            ->with('title', $this->title);
    }

    /**
     * AJAX datatable for all existing order commissions (Tab 1: Daftar Order Komisi).
     */
    public function datatableOrders(Request $request)
    {
        if (! $request->ajax()) {
            abort(404);
        }

        $query = $this->buildOrderCommissionFilterQuery($request);

        // Compute KPI summary across filtered rows (before pagination)
        $summaryQuery = clone $query;
        $totalOrders = (clone $summaryQuery)->count();
        $totalAmount = (float) (clone $summaryQuery)->sum('amount');
        $paidAmount = (float) (clone $summaryQuery)->whereNotNull('payment_date')->sum('amount');
        $unpaidAmount = $totalAmount - $paidAmount;
        $paidCount = (clone $summaryQuery)->whereNotNull('payment_date')->count();
        $unpaidCount = $totalOrders - $paidCount;

        // Sort newest order date first (using order_id list or join-free subquery)
        $query->with([
            'order.fleet',
            'order.customer',
            'order.route.originLocation',
            'order.route.destinationLocation',
            'costComponent',
            'orderCost',
            'commissionReport',
        ])->orderByDesc('created_at');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="form-check-input order-commission-checkbox" value="' . e($row->id) . '">';
            })
            ->addColumn('orderDate', function ($row) {
                return $row->order?->orderDate
                    ? Carbon::parse($row->order->orderDate)->format('d-m-Y')
                    : '-';
            })
            ->addColumn('orderNumber', function ($row) {
                $order = $row->order;
                if (! $order) {
                    return '-';
                }
                $ship = $order->shipmentNumber && $order->shipmentNumber !== '-' ? $order->shipmentNumber : $order->code;
                $sub = ($order->shipmentNumber && $order->shipmentNumber !== '-' && $order->code)
                    ? '<div class="text-muted" style="font-size:11px;">' . e($order->code) . '</div>'
                    : '';

                return '<div class="fw-bold text-primary" style="font-size:12.5px;">' . e($ship) . '</div>' . $sub;
            })
            ->addColumn('customerName', function ($row) {
                return '<span class="fw-semibold" style="font-size:12.5px;">' . e($row->order?->customer?->name ?? '-') . '</span>';
            })
            ->addColumn('fleetRoute', function ($row) {
                $order = $row->order;
                $plate = $order?->plateNumber ?: ($order?->fleet?->plateNumber ?? '-');
                $route = '-';
                if ($order?->route) {
                    $origin = $order->route->originLocation->name ?? '';
                    $dest = $order->route->destinationLocation->name ?? '';
                    $route = $order->route->name . ($origin || $dest ? ' (' . $origin . ' - ' . $dest . ')' : '');
                }

                return '<span class="badge bg-light text-dark border fw-semibold me-1" style="font-size:11px;">' . e($plate) . '</span>'
                    . '<div class="text-muted mt-1" style="font-size:11.5px; white-space:normal; max-width:220px;">' . e($route) . '</div>';
            })
            ->addColumn('componentName', function ($row) {
                $name = $row->orderCost?->componentName ?: ($row->costComponent?->name ?? '-');
                $slipBadge = $row->commissionReport
                    ? '<div class="mt-1"><a href="' . route('report.commission.show', $row->commissionReport->id) . '" class="badge bg-primary-subtle text-primary text-decoration-none" style="font-size:10.5px;">Slip: ' . e($row->commissionReport->code) . '</a></div>'
                    : '';

                return '<span class="fw-semibold" style="font-size:12.5px; white-space:normal;">' . e($name) . '</span>' . $slipBadge;
            })
            ->addColumn('amountFormatted', function ($row) {
                return '<span class="fw-bold font-monospace" style="color:#4f46e5; font-size:13px;">Rp ' . number_format((float) $row->amount, 0, ',', '.') . '</span>';
            })
            ->addColumn('paymentStatusBadge', function ($row) {
                if ($row->payment_date) {
                    $formatted = Carbon::parse($row->payment_date)->format('d/m/Y');

                    return '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size:11.5px; border-radius:6px;">
                                <i class="mdi mdi-check-circle-outline me-1"></i> ' . $formatted . '
                            </span>';
                }

                return '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1" style="font-size:11.5px; border-radius:6px;">
                            <i class="mdi mdi-clock-outline me-1"></i> Belum Dibayar
                        </span>';
            })
            ->addColumn('action', function ($row) {
                $payDate = $row->payment_date ? Carbon::parse($row->payment_date)->format('Y-m-d') : '';
                $orderNum = e($row->order?->shipmentNumber ?: ($row->order?->code ?? '-'));
                $compName = e($row->orderCost?->componentName ?: ($row->costComponent?->name ?? '-'));
                $pdfUrl = route('report.commission.pdf-by-date', ['ids' => [$row->id]]);

                return '<div class="btn-group btn-group-sm" role="group">'
                    . '<button type="button" onclick="openOrderPaymentModal([\'' . $row->id . '\'], \'' . $orderNum . ' — ' . $compName . '\', \'' . $payDate . '\')" class="btn btn-outline-info btn-icon" title="Input Tanggal Bayar"><i class="mdi mdi-calendar-check"></i></button>'
                    . '<a href="' . $pdfUrl . '" target="_blank" class="btn btn-outline-danger btn-icon" title="Cetak Slip PDF"><i class="mdi mdi-file-pdf-box"></i></a>'
                    . '</div>';
            })
            ->rawColumns(['checkbox', 'orderNumber', 'customerName', 'fleetRoute', 'componentName', 'amountFormatted', 'paymentStatusBadge', 'action'])
            ->with([
                'summary' => [
                    'totalOrders' => $totalOrders,
                    'totalAmount' => $totalAmount,
                    'totalAmountFormatted' => 'Rp ' . number_format($totalAmount, 0, ',', '.'),
                    'paidCount' => $paidCount,
                    'paidAmountFormatted' => 'Rp ' . number_format($paidAmount, 0, ',', '.'),
                    'unpaidCount' => $unpaidCount,
                    'unpaidAmountFormatted' => 'Rp ' . number_format($unpaidAmount, 0, ',', '.'),
                ],
            ])
            ->toJson();
    }

    /**
     * Quick AJAX endpoint to input/update payment_date on 1 or multiple order_commission rows.
     */
    public function updateOrderPaymentDate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array|min:1',
            'ids.*' => 'string',
            'paymentDate' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $paymentDate = $request->filled('paymentDate')
            ? Carbon::parse($request->paymentDate)->format('Y-m-d')
            : null;

        $rows = OrderCommission::whereIn('id', $request->input('ids'))->get();
        foreach ($rows as $row) {
            $row->payment_date = $paymentDate;
            if ($paymentDate) {
                $row->status = '1';
            } elseif (! $row->commission_report_id) {
                $row->status = '0';
            }
            $row->save();
        }

        $count = $rows->count();
        $msg = $paymentDate
            ? "Tanggal bayar ({$paymentDate}) berhasil disimpan untuk {$count} order komisi."
            : "Tanggal bayar berhasil dikosongkan untuk {$count} order komisi.";

        return response()->json([
            'success' => true,
            'count' => $count,
            'message' => $msg,
        ]);
    }

    /**
     * Build collation-safe filtered query on order_commission for Tab 1.
     */
    private function buildOrderCommissionFilterQuery(Request $request)
    {
        $componentCodes = array_values(array_filter((array) $request->input('componentCodes', [])));
        $customerCode = $request->input('customerCode') ?: null;
        $startDate = $request->filled('startDate') ? Carbon::parse($request->startDate)->format('Y-m-d') : null;
        $endDate = $request->filled('endDate') ? Carbon::parse($request->endDate)->format('Y-m-d') : null;
        $paymentStatus = $request->input('paymentStatus') ?: null;
        $search = trim((string) data_get($request->input('search'), 'value', ''));

        $orderQuery = Order::query()->whereNull('deleted_at');
        $hasOrderFilter = false;

        if ($startDate && $endDate) {
            $orderQuery->whereBetween('orderDate', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            $hasOrderFilter = true;
        } elseif ($startDate) {
            $orderQuery->whereDate('orderDate', '>=', $startDate);
            $hasOrderFilter = true;
        } elseif ($endDate) {
            $orderQuery->whereDate('orderDate', '<=', $endDate);
            $hasOrderFilter = true;
        }

        if ($customerCode) {
            $orderQuery->where('customerCode', $customerCode);
            $hasOrderFilter = true;
        }

        $query = OrderCommission::query();

        if ($hasOrderFilter) {
            $matchingOrderIds = $orderQuery->pluck('id')->toArray();
            $query->whereIn('order_id', $matchingOrderIds);
        }

        if (! empty($componentCodes)) {
            $componentIds = CostComponent::withTrashed()
                ->whereIn('code', $componentCodes)
                ->pluck('id')
                ->toArray();
            $query->whereIn('cost_component_id', $componentIds);
        }

        if ($paymentStatus === 'paid') {
            $query->whereNotNull('payment_date');
        } elseif ($paymentStatus === 'unpaid') {
            $query->whereNull('payment_date');
        }

        if ($search !== '') {
            $searchCustomerCodes = Customer::where('name', 'like', "%{$search}%")->pluck('code')->toArray();
            $searchOrderIds = Order::whereNull('deleted_at')
                ->where(function ($q) use ($search, $searchCustomerCodes) {
                    $q->where('code', 'like', "%{$search}%")
                        ->orWhere('shipmentNumber', 'like', "%{$search}%")
                        ->orWhere('plateNumber', 'like', "%{$search}%");
                    if (! empty($searchCustomerCodes)) {
                        $q->orWhereIn('customerCode', $searchCustomerCodes);
                    }
                })
                ->pluck('id')
                ->toArray();

            $searchComponentIds = CostComponent::withTrashed()
                ->where('name', 'like', "%{$search}%")
                ->pluck('id')
                ->toArray();

            $query->where(function ($q) use ($searchOrderIds, $searchComponentIds) {
                if (! empty($searchOrderIds)) {
                    $q->orWhereIn('order_id', $searchOrderIds);
                }
                if (! empty($searchComponentIds)) {
                    $q->orWhereIn('cost_component_id', $searchComponentIds);
                }
                if (empty($searchOrderIds) && empty($searchComponentIds)) {
                    $q->whereRaw('1 = 0');
                }
            });
        }

        return $query;
    }

    /**
     * AJAX datatable for processed commission reports.
     */
    public function datatableProcessed(Request $request)
    {
        if (! $request->ajax()) {
            abort(404);
        }

        $query = CommissionReport::with('customer');

        if ($request->filled('filterComponentCode')) {
            $componentCode = $request->filterComponentCode;
            $query->where('componentCodes', 'like', '%"' . $componentCode . '"%');
        }

        if ($request->filled('filterCustomerCode')) {
            $query->where('customerCode', $request->filterCustomerCode);
        }

        if ($request->filled('filterPaymentStatus')) {
            if ($request->filterPaymentStatus === 'paid') {
                $query->whereNotNull('paymentDate');
            } elseif ($request->filterPaymentStatus === 'unpaid') {
                $query->whereNull('paymentDate');
            }
        }

        if ($request->filled('filterStartDate')) {
            $query->whereDate('startDate', '>=', $request->filterStartDate);
        }

        if ($request->filled('filterEndDate')) {
            $query->whereDate('endDate', '<=', $request->filterEndDate);
        }

        $query->orderBy('created_at', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('code', function ($row) {
                return '<span class="badge bg-light text-primary border fw-bold" style="font-family:monospace; font-size:12px; padding:6px 10px; border-radius:6px;">' . e($row->code) . '</span>';
            })
            ->addColumn('recipientDisplay', function ($row) {
                $name = e($row->recipientName ?: '-');
                $initials = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $row->recipientName ?: 'KM'), 0, 2) ?: 'KM');
                $sub = $row->customer?->name
                    ? '<div class="text-muted" style="font-size:11.5px;"><i class="mdi mdi-domain me-1"></i>' . e($row->customer->name) . '</div>'
                    : '';

                return '<div class="d-flex align-items-center gap-2">
                            <div class="avatar-badge-sm">' . $initials . '</div>
                            <div>
                                <div class="fw-bold text-dark" style="font-size:13.5px;">' . $name . '</div>
                                ' . $sub . '
                            </div>
                        </div>';
            })
            ->filterColumn('recipientDisplay', function ($query, $keyword) {
                $customerCodes = Customer::query()
                    ->where('name', 'like', "%{$keyword}%")
                    ->pluck('code');

                $query->where(function ($q) use ($keyword, $customerCodes) {
                    $q->where('recipientName', 'like', "%{$keyword}%")
                        ->orWhere('code', 'like', "%{$keyword}%");

                    if ($customerCodes->isNotEmpty()) {
                        $q->orWhereIn('customerCode', $customerCodes);
                    }
                });
            })
            ->addColumn('periode', function ($row) {
                $start = $row->startDate ? Carbon::parse($row->startDate)->format('d/m/Y') : '-';
                $end = $row->endDate ? Carbon::parse($row->endDate)->format('d/m/Y') : '-';

                return '<span class="badge bg-light text-dark border fw-normal" style="font-size:11.5px; padding:5px 10px; border-radius:6px;">
                            <i class="mdi mdi-calendar-range text-primary me-1"></i> ' . $start . ' &ndash; ' . $end . '
                        </span>';
            })
            ->addColumn('paymentDateFormatted', function ($row) {
                if ($row->paymentDate) {
                    $formatted = Carbon::parse($row->paymentDate)->format('d/m/Y');

                    return '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size:11.5px; border-radius:6px;">
                                <i class="mdi mdi-check-circle-outline me-1"></i> ' . $formatted . '
                            </span>';
                }

                return '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1" style="font-size:11.5px; border-radius:6px;">
                            <i class="mdi mdi-clock-outline me-1"></i> Belum Dibayar
                        </span>';
            })
            ->addColumn('totalCommissionFormatted', function ($row) {
                return '<span class="fw-semibold text-secondary font-monospace" style="font-size:13px;">Rp ' . number_format((float) $row->totalCommission, 0, ',', '.') . '</span>';
            })
            ->addColumn('totalAdjustmentFormatted', function ($row) {
                if ((float) $row->totalAdjustment > 0) {
                    return '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 font-monospace" style="font-size:11.5px;">+Rp ' . number_format((float) $row->totalAdjustment, 0, ',', '.') . '</span>';
                } elseif ((float) $row->totalAdjustment < 0) {
                    return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 font-monospace" style="font-size:11.5px;">-Rp ' . number_format(abs((float) $row->totalAdjustment), 0, ',', '.') . '</span>';
                }

                return '<span class="text-muted font-monospace" style="font-size:12px;">Rp 0</span>';
            })
            ->addColumn('grandTotalFormatted', function ($row) {
                return '<span class="fw-bold font-monospace" style="color:#4f46e5; font-size:13.5px;">Rp ' . number_format((float) $row->grandTotal, 0, ',', '.') . '</span>';
            })
            ->addColumn('action', function ($row) {
                $showUrl = route('report.commission.show', $row->id);
                $pdfUrl = route('report.commission.pdf-processed', $row->id);
                $payDate = $row->paymentDate ? Carbon::parse($row->paymentDate)->format('Y-m-d') : '';
                $safeCode = e($row->code);
                $safeName = e($row->recipientName ?: '-');

                $buttons = '<div class="btn-group btn-group-sm" role="group">';
                $buttons .= '<a href="' . $showUrl . '" class="btn btn-outline-primary btn-icon" title="Lihat Detail"><i class="mdi mdi-eye"></i></a>';
                $buttons .= '<a href="' . $pdfUrl . '" target="_blank" class="btn btn-outline-danger btn-icon" title="Cetak Slip PDF"><i class="mdi mdi-file-pdf-box"></i></a>';
                $buttons .= '<button type="button" onclick="openPaymentDateModal(\'' . $row->id . '\', \'' . $safeCode . '\', \'' . $safeName . '\', \'' . $payDate . '\')" class="btn btn-outline-info btn-icon" title="Input Tanggal Bayar"><i class="mdi mdi-calendar-check"></i></button>';
                $buttons .= '<button type="button" onclick="editCommission(\'' . $row->id . '\')" class="btn btn-outline-success btn-icon" title="Edit Komisi"><i class="mdi mdi-pencil"></i></button>';
                $buttons .= '<button type="button" onclick="deleteCommission(\'' . $row->id . '\')" class="btn btn-outline-warning btn-icon" title="Hapus Komisi"><i class="mdi mdi-delete"></i></button>';
                $buttons .= '</div>';

                return $buttons;
            })
            ->rawColumns([
                'code',
                'recipientDisplay',
                'periode',
                'paymentDateFormatted',
                'totalCommissionFormatted',
                'totalAdjustmentFormatted',
                'grandTotalFormatted',
                'action',
            ])
            ->toJson();
    }

    /**
     * AJAX - Preview unpaid commission orders for selected component(s) / customer & date range.
     */
    public function getOrderCommission(Request $request)
    {
        $componentCodes = array_values(array_filter((array) $request->input('componentCodes', [])));
        $customerCode = $request->input('customerCode');
        $rawStart = $request->input('startDate');
        $rawEnd = $request->input('endDate');

        if (! $rawStart || ! $rawEnd) {
            return response()->json(['error' => 'Tanggal mulai dan tanggal akhir wajib diisi.'], 422);
        }

        if (empty($componentCodes) && empty($customerCode)) {
            return response()->json(['error' => 'Pilih minimal satu Komponen Komisi atau Customer.'], 422);
        }

        try {
            $startDate = Carbon::parse($rawStart)->format('Y-m-d');
            $endDate = Carbon::parse($rawEnd)->format('Y-m-d');
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Format tanggal tidak valid.'], 422);
        }

        OrderCommissionService::syncOrdersInRange($startDate, $endDate, $componentCodes, $customerCode);

        $orderCommissions = $this->queryUnpaidOrderCommissions($startDate, $endDate, $componentCodes, $customerCode);

        $rows = $this->formatOrderCommissionRows($orderCommissions);
        $totalCommission = $orderCommissions->sum('amount');

        $suggestedRecipient = $this->resolveRecipientName(null, $componentCodes, $customerCode);

        return response()->json([
            'orders' => $rows,
            'totalCommission' => $totalCommission,
            'totalCommissionFormatted' => number_format((float) $totalCommission, 0, ',', '.'),
            'suggestedRecipientName' => $suggestedRecipient,
        ]);
    }

    /**
     * Store a new processed commission report.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'recipientName' => 'nullable|string|max:255',
            'customerCode' => 'nullable|string|max:50',
            'componentCodes' => 'nullable|array',
            'componentCodes.*' => 'string|max:50',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
            'paymentDate' => 'nullable|date',
            'adjustments' => 'nullable|array',
            'adjustments.*.date' => 'required|date',
            'adjustments.*.description' => 'required|string',
            'adjustments.*.type' => 'required|in:addition,deduction',
            'adjustments.*.nominal' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->route('report.commission.index')
                ->with('fail', $validator->errors()->first());
        }

        $componentCodes = array_values(array_filter((array) $request->input('componentCodes', [])));
        $customerCode = $request->input('customerCode') ?: null;

        if (empty($componentCodes) && empty($customerCode)) {
            return redirect()->route('report.commission.index')
                ->with('fail', 'Pilih minimal satu Komponen Komisi atau Customer.');
        }

        try {
            DB::beginTransaction();

            $startDate = Carbon::parse($request->startDate)->format('Y-m-d');
            $endDate = Carbon::parse($request->endDate)->format('Y-m-d');
            $paymentDate = $request->filled('paymentDate')
                ? Carbon::parse($request->paymentDate)->format('Y-m-d')
                : null;

            OrderCommissionService::syncOrdersInRange($startDate, $endDate, $componentCodes, $customerCode);

            $orderCommissions = $this->queryUnpaidOrderCommissions($startDate, $endDate, $componentCodes, $customerCode);
            $totalCommission = (float) $orderCommissions->sum('amount');

            $totalAdjustment = 0.0;
            $adjustments = $request->input('adjustments', []);
            foreach ($adjustments as $adj) {
                $nominal = (float) $adj['nominal'];
                $totalAdjustment += ($adj['type'] === 'addition') ? $nominal : -$nominal;
            }

            $grandTotal = $totalCommission + $totalAdjustment;

            // If componentCodes was empty (filtered by customer only), capture distinct component codes found
            if (empty($componentCodes) && $orderCommissions->isNotEmpty()) {
                $componentCodes = $orderCommissions
                    ->map(fn ($oc) => $oc->costComponent?->code)
                    ->filter()
                    ->unique()
                    ->values()
                    ->toArray();
            }

            $recipientName = $this->resolveRecipientName(
                $request->input('recipientName'),
                $componentCodes,
                $customerCode
            );

            $commission = CommissionReport::create([
                'code' => GenerateCode::generateCode('KM'),
                'recipientName' => $recipientName,
                'customerCode' => $customerCode,
                'componentCodes' => $componentCodes,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'paymentDate' => $paymentDate,
                'totalCommission' => $totalCommission,
                'totalAdjustment' => $totalAdjustment,
                'grandTotal' => $grandTotal,
                'notes' => $request->input('notes'),
            ]);

            foreach ($adjustments as $adj) {
                CommissionReportDetail::create([
                    'code' => GenerateCode::generateCode('KMD', true),
                    'commissionReportCode' => $commission->code,
                    'date' => $adj['date'],
                    'description' => $adj['description'],
                    'type' => $adj['type'],
                    'nominal' => (float) $adj['nominal'],
                ]);
            }

            if ($orderCommissions->isNotEmpty()) {
                OrderCommission::whereIn('id', $orderCommissions->pluck('id'))->update([
                    'status' => '1',
                    'commission_report_id' => $commission->id,
                    'payment_date' => $paymentDate,
                ]);
            }

            DB::commit();

            return redirect()->route('report.commission.index')
                ->with('success', 'Laporan komisi berhasil diproses. Kode: ' . $commission->code);
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->route('report.commission.index')
                ->with('fail', 'Gagal memproses komisi: ' . $th->getMessage());
        }
    }

    /**
     * Show detail of a processed commission report.
     */
    public function show(string $id)
    {
        $commission = CommissionReport::with(['customer', 'details'])->findOrFail($id);

        $orderCommissions = OrderCommission::with([
            'order.fleet',
            'order.customer',
            'order.route.originLocation',
            'order.route.destinationLocation',
            'costComponent',
            'orderCost',
        ])
            ->where('commission_report_id', $commission->id)
            ->get()
            ->sortBy(fn ($item) => $item->order?->orderDate ?? '9999-12-31 23:59:59')
            ->values();

        $componentNames = [];
        if (! empty($commission->componentCodes)) {
            $componentNames = CostComponent::withTrashed()
                ->whereIn('code', $commission->componentCodes)
                ->pluck('name')
                ->toArray();
        }

        return view($this->view . 'show')
            ->with('commission', $commission)
            ->with('orderCommissions', $orderCommissions)
            ->with('componentNames', $componentNames)
            ->with('title', $this->title . ' - Detail');
    }

    /**
     * Return JSON payload for Edit Modal.
     */
    public function edit(string $id)
    {
        $commission = CommissionReport::with(['customer', 'details'])->findOrFail($id);

        $orderCommissions = OrderCommission::with([
            'order.fleet',
            'order.customer',
            'order.route.originLocation',
            'order.route.destinationLocation',
            'costComponent',
            'orderCost',
        ])
            ->where('commission_report_id', $commission->id)
            ->get()
            ->sortBy(fn ($item) => $item->order?->orderDate ?? '9999-12-31 23:59:59')
            ->values();

        return response()->json([
            'commission' => $commission,
            'orders' => $this->formatOrderCommissionRows($orderCommissions),
        ]);
    }

    /**
     * Update an existing processed commission report.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'recipientName' => 'nullable|string|max:255',
            'customerCode' => 'nullable|string|max:50',
            'componentCodes' => 'nullable|array',
            'componentCodes.*' => 'string|max:50',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
            'paymentDate' => 'nullable|date',
            'adjustments' => 'nullable|array',
            'adjustments.*.date' => 'required|date',
            'adjustments.*.description' => 'required|string',
            'adjustments.*.type' => 'required|in:addition,deduction',
            'adjustments.*.nominal' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $componentCodes = array_values(array_filter((array) $request->input('componentCodes', [])));
        $customerCode = $request->input('customerCode') ?: null;

        if (empty($componentCodes) && empty($customerCode)) {
            return response()->json([
                'success' => false,
                'message' => 'Pilih minimal satu Komponen Komisi atau Customer.',
            ], 422);
        }

        try {
            DB::beginTransaction();

            $commission = CommissionReport::findOrFail($id);
            $startDate = Carbon::parse($request->startDate)->format('Y-m-d');
            $endDate = Carbon::parse($request->endDate)->format('Y-m-d');
            $paymentDate = $request->filled('paymentDate')
                ? Carbon::parse($request->paymentDate)->format('Y-m-d')
                : null;

            // Release previously linked order_commission rows
            OrderCommission::where('commission_report_id', $commission->id)->update([
                'status' => '0',
                'commission_report_id' => null,
                'payment_date' => null,
            ]);

            OrderCommissionService::syncOrdersInRange($startDate, $endDate, $componentCodes, $customerCode);

            $orderCommissions = $this->queryUnpaidOrderCommissions($startDate, $endDate, $componentCodes, $customerCode);
            $totalCommission = (float) $orderCommissions->sum('amount');

            $totalAdjustment = 0.0;
            $adjustments = $request->input('adjustments', []);
            foreach ($adjustments as $adj) {
                $nominal = (float) $adj['nominal'];
                $totalAdjustment += ($adj['type'] === 'addition') ? $nominal : -$nominal;
            }

            $grandTotal = $totalCommission + $totalAdjustment;

            if (empty($componentCodes) && $orderCommissions->isNotEmpty()) {
                $componentCodes = $orderCommissions
                    ->map(fn ($oc) => $oc->costComponent?->code)
                    ->filter()
                    ->unique()
                    ->values()
                    ->toArray();
            }

            $recipientName = $this->resolveRecipientName(
                $request->input('recipientName'),
                $componentCodes,
                $customerCode
            );

            $commission->update([
                'recipientName' => $recipientName,
                'customerCode' => $customerCode,
                'componentCodes' => $componentCodes,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'paymentDate' => $paymentDate,
                'totalCommission' => $totalCommission,
                'totalAdjustment' => $totalAdjustment,
                'grandTotal' => $grandTotal,
                'notes' => $request->input('notes'),
            ]);

            CommissionReportDetail::where('commissionReportCode', $commission->code)->delete();

            foreach ($adjustments as $adj) {
                CommissionReportDetail::create([
                    'code' => GenerateCode::generateCode('KMD', true),
                    'commissionReportCode' => $commission->code,
                    'date' => $adj['date'],
                    'description' => $adj['description'],
                    'type' => $adj['type'],
                    'nominal' => (float) $adj['nominal'],
                ]);
            }

            if ($orderCommissions->isNotEmpty()) {
                OrderCommission::whereIn('id', $orderCommissions->pluck('id'))->update([
                    'status' => '1',
                    'commission_report_id' => $commission->id,
                    'payment_date' => $paymentDate,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Laporan komisi berhasil diperbarui. Kode: ' . $commission->code,
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Quick AJAX endpoint to input/update paymentDate (Tanggal Bayar) from table or detail page.
     */
    public function updatePaymentDate(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'paymentDate' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $commission = CommissionReport::findOrFail($id);
        $paymentDate = $request->filled('paymentDate')
            ? Carbon::parse($request->paymentDate)->format('Y-m-d')
            : null;

        $commission->update(['paymentDate' => $paymentDate]);

        OrderCommission::where('commission_report_id', $commission->id)->update([
            'payment_date' => $paymentDate,
        ]);

        return response()->json([
            'success' => true,
            'paymentDate' => $paymentDate,
            'paymentDateFormatted' => $paymentDate ? Carbon::parse($paymentDate)->format('d-m-Y') : null,
            'message' => $paymentDate
                ? 'Tanggal bayar berhasil disimpan (' . Carbon::parse($paymentDate)->format('d-m-Y') . ').'
                : 'Tanggal bayar berhasil dikosongkan.',
        ]);
    }

    /**
     * Delete a processed commission report and revert linked order commissions to unpaid.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $commission = CommissionReport::findOrFail($id);

            OrderCommission::where('commission_report_id', $commission->id)->update([
                'status' => '0',
                'commission_report_id' => null,
                'payment_date' => null,
            ]);

            CommissionReportDetail::where('commissionReportCode', $commission->code)->delete();
            $commission->delete();

            DB::commit();

            return redirect()->route('report.commission.index')
                ->with('success', 'Data laporan komisi berhasil dihapus.');
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->route('report.commission.index')
                ->with('fail', 'Gagal menghapus: ' . $th->getMessage());
        }
    }

    /**
     * Generate PDF Slip Komisi for a processed commission report.
     */
    public function pdfProcessed(string $id)
    {
        $commission = CommissionReport::with(['customer', 'details'])->findOrFail($id);

        $orderCommissions = OrderCommission::with([
            'order.fleet',
            'order.customer',
            'order.route.originLocation',
            'order.route.destinationLocation',
            'costComponent',
            'orderCost',
        ])
            ->where('commission_report_id', $commission->id)
            ->get()
            ->sortBy(fn ($item) => $item->order?->orderDate ?? '9999-12-31 23:59:59')
            ->values();

        $rows = [];
        $totalCommission = 0.0;
        $customerNames = [];

        foreach ($orderCommissions as $idx => $item) {
            $order = $item->order;
            if (! $order) {
                continue;
            }

            $amount = (float) $item->amount;
            $totalCommission += $amount;

            $routeName = '-';
            if ($order->route) {
                $origin = $order->route->originLocation->name ?? '';
                $dest = $order->route->destinationLocation->name ?? '';
                $routeName = $order->route->name . ($origin || $dest ? ' (' . $origin . ' - ' . $dest . ')' : '');
            }

            $compName = $item->orderCost?->componentName ?: ($item->costComponent?->name ?? '-');
            if ($order->customer?->name) {
                $customerNames[] = $order->customer->name;
            }

            $rows[] = [
                'no' => $idx + 1,
                'orderCode' => $order->code,
                'shipmentNumber' => $order->shipmentNumber ?? '-',
                'date' => Carbon::parse($order->orderDate)->format('d-m-Y'),
                'plateNumber' => $order->plateNumber ?: ($order->fleet->plateNumber ?? '-'),
                'route' => $routeName,
                'componentName' => $compName,
                'amount' => $amount,
            ];
        }

        $adjustments = [];
        foreach ($commission->details as $detail) {
            $adjustments[] = [
                'date' => Carbon::parse($detail->date)->format('d-m-Y'),
                'description' => $detail->description,
                'type' => $detail->type,
                'nominal' => (float) $detail->nominal,
            ];
        }

        $customerLabel = $commission->customer?->name
            ?: (implode(', ', array_unique($customerNames)) ?: '-');

        $periodLabel = ($commission->startDate ? Carbon::parse($commission->startDate)->format('d-m-Y') : '-')
            . ' s/d '
            . ($commission->endDate ? Carbon::parse($commission->endDate)->format('d-m-Y') : '-');

        $commissionData = [[
            'code' => $commission->code,
            'recipientName' => $commission->recipientName ?: '-',
            'customerName' => $customerLabel,
            'period' => $periodLabel,
            'paymentDate' => $commission->paymentDate
                ? Carbon::parse($commission->paymentDate)->format('d-m-Y')
                : '',
            'rows' => $rows,
            'totalCommission' => (float) $commission->totalCommission,
            'totalAdjustment' => (float) $commission->totalAdjustment,
            'grandTotal' => (float) $commission->grandTotal,
            'adjustments' => $adjustments,
            'notes' => $commission->notes,
        ]];

        return $this->renderPdf($commissionData, 'Slip_Komisi_' . $commission->code . '.pdf');
    }

    /**
     * Direct Print PDF by selected date range / filters / checked order_commission IDs.
     */
    public function pdfByDate(Request $request)
    {
        $selectedIds = array_values(array_filter((array) $request->input('ids', [])));
        $rawStart = $request->input('startDate');
        $rawEnd = $request->input('endDate');
        $componentCodes = array_values(array_filter((array) $request->input('componentCodes', [])));
        $customerCode = $request->input('customerCode') ?: null;
        $paymentDateInput = $request->filled('paymentDate')
            ? Carbon::parse($request->input('paymentDate'))->format('d-m-Y')
            : '';

        $startDate = $rawStart ? Carbon::parse($rawStart)->format('Y-m-d') : null;
        $endDate = $rawEnd ? Carbon::parse($rawEnd)->format('Y-m-d') : null;

        if (! empty($selectedIds)) {
            $items = OrderCommission::with([
                'order.fleet',
                'order.customer',
                'order.route.originLocation',
                'order.route.destinationLocation',
                'costComponent',
                'orderCost',
            ])
                ->whereIn('id', $selectedIds)
                ->get()
                ->sortBy(fn ($item) => $item->order?->orderDate ?? '9999-12-31 23:59:59')
                ->values();
        } else {
            OrderCommissionService::syncOrdersInRange($startDate, $endDate, $componentCodes, $customerCode);

            $orderQuery = Order::query()->whereNull('deleted_at');
            if ($startDate && $endDate) {
                $orderQuery->whereBetween('orderDate', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            } elseif ($startDate) {
                $orderQuery->whereDate('orderDate', '>=', $startDate);
            } elseif ($endDate) {
                $orderQuery->whereDate('orderDate', '<=', $endDate);
            }
            if ($customerCode) {
                $orderQuery->where('customerCode', $customerCode);
            }
            $matchingOrderIds = $orderQuery->pluck('id')->toArray();

            $query = OrderCommission::with([
                'order.fleet',
                'order.customer',
                'order.route.originLocation',
                'order.route.destinationLocation',
                'costComponent',
                'orderCost',
            ])->whereIn('order_id', $matchingOrderIds);

            if (! empty($componentCodes)) {
                $componentIds = CostComponent::withTrashed()->whereIn('code', $componentCodes)->pluck('id')->toArray();
                $query->whereIn('cost_component_id', $componentIds);
            }

            $statusFilter = $request->input('paymentStatus') ?: $request->input('status');
            if ($statusFilter === 'unpaid') {
                $query->whereNull('payment_date');
            } elseif ($statusFilter === 'paid') {
                $query->whereNotNull('payment_date');
            }

            $items = $query->get()
                ->sortBy(fn ($item) => $item->order?->orderDate ?? '9999-12-31 23:59:59')
                ->values();
        }

        // Group into 1 combined slip when specific components/customer/checkboxes chosen, else group per component
        $groups = (! empty($selectedIds) || ! empty($componentCodes) || ! empty($customerCode))
            ? collect(['combined' => $items])
            : $items->groupBy(fn ($i) => $i->costComponent?->name ?? 'Komisi');

        if (! $startDate && ! $endDate && $items->isNotEmpty()) {
            $dates = $items->map(fn ($i) => $i->order?->orderDate)->filter()->sort()->values();
            $periodLabel = $dates->isNotEmpty()
                ? Carbon::parse($dates->first())->format('d-m-Y') . ' s/d ' . Carbon::parse($dates->last())->format('d-m-Y')
                : '-';
        } else {
            $periodLabel = ($startDate ? Carbon::parse($startDate)->format('d-m-Y') : 'Awal')
                . ' s/d '
                . ($endDate ? Carbon::parse($endDate)->format('d-m-Y') : 'Sekarang');
        }

        $commissionData = [];
        foreach ($groups as $groupKey => $groupItems) {
            $rows = [];
            $totalCommission = 0.0;
            $customerNames = [];
            $paymentDates = [];
            $compNamesFound = [];

            foreach ($groupItems->values() as $idx => $item) {
                $order = $item->order;
                if (! $order) {
                    continue;
                }

                $amount = (float) $item->amount;
                $totalCommission += $amount;

                $routeName = '-';
                if ($order->route) {
                    $origin = $order->route->originLocation->name ?? '';
                    $dest = $order->route->destinationLocation->name ?? '';
                    $routeName = $order->route->name . ($origin || $dest ? ' (' . $origin . ' - ' . $dest . ')' : '');
                }

                $compName = $item->orderCost?->componentName ?: ($item->costComponent?->name ?? '-');
                $compNamesFound[] = $compName;
                if ($order->customer?->name) {
                    $customerNames[] = $order->customer->name;
                }
                if ($item->payment_date) {
                    $paymentDates[] = Carbon::parse($item->payment_date)->format('d-m-Y');
                }

                $rows[] = [
                    'no' => $idx + 1,
                    'orderCode' => $order->code,
                    'shipmentNumber' => $order->shipmentNumber ?? '-',
                    'date' => Carbon::parse($order->orderDate)->format('d-m-Y'),
                    'plateNumber' => $order->plateNumber ?: ($order->fleet->plateNumber ?? '-'),
                    'route' => $routeName,
                    'componentName' => $compName,
                    'amount' => $amount,
                ];
            }

            $uniqueCompNames = array_values(array_unique($compNamesFound));
            if ($groupKey === 'combined') {
                $recipientName = ! empty($request->input('recipientName'))
                    ? trim((string) $request->input('recipientName'))
                    : (count($uniqueCompNames) === 1
                        ? $uniqueCompNames[0]
                        : $this->resolveRecipientName(null, $componentCodes, $customerCode));
            } else {
                $recipientName = $groupKey;
            }

            $customerLabel = $customerCode
                ? (Customer::where('code', $customerCode)->value('name') ?: '-')
                : (implode(', ', array_unique($customerNames)) ?: '-');

            $uniquePayDates = array_values(array_unique($paymentDates));
            $resolvedPayDate = $paymentDateInput ?: (count($uniquePayDates) === 1 ? $uniquePayDates[0] : implode(', ', $uniquePayDates));

            $commissionData[] = [
                'code' => null,
                'recipientName' => $recipientName,
                'customerName' => $customerLabel,
                'period' => $periodLabel,
                'paymentDate' => $resolvedPayDate,
                'rows' => $rows,
                'totalCommission' => $totalCommission,
                'totalAdjustment' => 0.0,
                'grandTotal' => $totalCommission,
                'adjustments' => [],
                'notes' => null,
            ];
        }

        return $this->renderPdf($commissionData, 'Slip_Komisi.pdf');
    }

    /**
     * Query unpaid order_commission records matching filters, sorted oldest order first.
     */
    private function queryUnpaidOrderCommissions(
        string $startDate,
        string $endDate,
        array $componentCodes,
        ?string $customerCode
    ) {
        $orderQuery = Order::query()
            ->whereBetween('orderDate', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->whereNull('deleted_at');

        if ($customerCode) {
            $orderQuery->where('customerCode', $customerCode);
        }

        $matchingOrderIds = $orderQuery->pluck('id')->toArray();
        if (empty($matchingOrderIds)) {
            return collect();
        }

        $query = OrderCommission::with([
            'order.fleet',
            'order.customer',
            'order.route.originLocation',
            'order.route.destinationLocation',
            'costComponent',
            'orderCost',
        ])
            ->where('status', '0')
            ->whereIn('order_id', $matchingOrderIds);

        if (! empty($componentCodes)) {
            $componentIds = CostComponent::withTrashed()
                ->whereIn('code', $componentCodes)
                ->pluck('id')
                ->toArray();
            $query->whereIn('cost_component_id', $componentIds);
        }

        return $query->get()
            ->sortBy(fn ($item) => $item->order?->orderDate ?? '9999-12-31 23:59:59')
            ->values();
    }

    /**
     * Format OrderCommission collection into JSON-friendly array rows.
     */
    private function formatOrderCommissionRows($orderCommissions): array
    {
        $rows = [];
        foreach ($orderCommissions as $item) {
            $order = $item->order;
            if (! $order) {
                continue;
            }

            $routeName = '-';
            if ($order->route) {
                $origin = $order->route->originLocation->name ?? '';
                $dest = $order->route->destinationLocation->name ?? '';
                $routeName = $order->route->name . ($origin || $dest ? ' (' . $origin . ' - ' . $dest . ')' : '');
            }

            $amount = (float) $item->amount;

            $rows[] = [
                'orderCode' => $order->code,
                'shipmentNumber' => $order->shipmentNumber ?? '-',
                'orderDate' => Carbon::parse($order->orderDate)->format('d-m-Y'),
                'customerName' => $order->customer?->name ?? '-',
                'plateNumber' => $order->plateNumber ?: ($order->fleet->plateNumber ?? '-'),
                'routeName' => $routeName,
                'componentName' => $item->orderCost?->componentName ?: ($item->costComponent?->name ?? '-'),
                'commission' => $amount,
                'commissionFormatted' => number_format($amount, 0, ',', '.'),
            ];
        }

        return $rows;
    }

    /**
     * Derive a readable recipient label when user leaves recipientName blank.
     */
    private function resolveRecipientName(?string $inputName, array $componentCodes, ?string $customerCode): string
    {
        if (! empty(trim((string) $inputName))) {
            return trim((string) $inputName);
        }

        if (! empty($componentCodes)) {
            $names = CostComponent::withTrashed()
                ->whereIn('code', $componentCodes)
                ->pluck('name')
                ->toArray();

            if (count($names) === 1) {
                return $names[0];
            }
            if (count($names) > 1) {
                return implode(', ', array_slice($names, 0, 2))
                    . (count($names) > 2 ? ' (+' . (count($names) - 2) . ' lainnya)' : '');
            }
        }

        if ($customerCode) {
            $customerName = Customer::where('code', $customerCode)->value('name');
            if ($customerName) {
                return 'Komisi ' . $customerName;
            }
        }

        return 'Komisi';
    }

    /**
     * Render Slip Komisi PDF using mPDF (same paper size & layout as Slip Gaji).
     */
    private function renderPdf(array $commissionData, string $filename)
    {
        $mpdf = new Mpdf([
            'orientation' => 'P',
            'format' => [215, 330],
            'tempDir' => storage_path('app/mpdf-temp'),
        ]);
        $mpdf->setAutoTopMargin = 'stretch';
        $mpdf->setAutoBottomMargin = 'stretch';

        $html = view($this->view . 'report.commission-pdf')
            ->with('commissionData', $commissionData)
            ->render();

        $mpdf->WriteHTML($html);

        return $mpdf->Output($filename, 'I');
    }
}
