<?php

namespace App\Http\Controllers\Report;

use App\Exports\SupplierPurchaseDetailReport;
use App\Exports\SupplierPurchaseReport;
use App\Http\Controllers\Controller;
use App\Models\Inventory\Supplier;
use App\Models\Purchasing\Purchase;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;
use Yajra\DataTables\DataTables;

class SupplierPurchaseController extends Controller
{
    protected $title;

    protected $view;

    public function __construct()
    {
        $this->title = 'Laporan Supplier';
        $this->view = 'report.supplier.';
    }

    public function index()
    {
        $supplier = Supplier::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        $rows = SupplierPurchaseReport::rowsForFilters(null, null);

        return view($this->view . 'index')
            ->with('view', $this->view)
            ->with('supplier', $supplier)
            ->with('stats', $this->summaryStats($rows))
            ->with('title', $this->title);
    }

    public function datatable(Request $request)
    {
        if (! $request->ajax()) {
            abort(404);
        }

        $rows = SupplierPurchaseReport::rowsForFilters(
            $request->startDate,
            $request->endDate,
            $request->supplierCode
        );

        return DataTables::of($rows)
            ->addIndexColumn()
            ->addColumn('action', function ($row) use ($request) {
                $detailUrl = route('report.supplier.detail', ['supplierCode' => $row->supplierCode]);
                $query = array_filter([
                    'startDate' => $request->startDate,
                    'endDate' => $request->endDate,
                ]);

                if ($query !== []) {
                    $detailUrl .= '?' . http_build_query($query);
                }

                return '<a href="' . e($detailUrl) . '" class="btn btn-icon btn-sm bg-primary-subtle hover-scale" data-bs-toggle="tooltip" title="Lihat detail supplier">'
                    . '<i class="mdi mdi-arrow-right fs-14 text-primary"></i>'
                    . '</a>';
            })
            ->editColumn('supplierName', fn ($row) => e($row->supplierName))
            ->editColumn('totalPurchase', fn ($row) => number_format((int) $row->totalPurchase, 0, ',', '.'))
            ->editColumn('totalBilling', fn ($row) => $this->formatCurrency($row->totalBilling))
            ->editColumn('totalPaid', fn ($row) => $this->formatCurrency($row->totalPaid))
            ->editColumn('totalRemaining', fn ($row) => '<strong class="text-dark">' . $this->formatCurrency($row->totalRemaining) . '</strong>')
            ->editColumn('dueSoonAmount', function ($row) {
                return '<span class="text-warning-emphasis fw-semibold">' . $this->formatCurrency($row->dueSoonAmount) . '</span>'
                    . '<small class="d-block text-muted">' . number_format((int) $row->dueSoonCount, 0, ',', '.') . ' PO</small>';
            })
            ->editColumn('overdueAmount', function ($row) {
                $class = (float) $row->overdueAmount > 0 ? 'text-danger' : 'text-muted';

                return '<span class="' . $class . ' fw-semibold">' . $this->formatCurrency($row->overdueAmount) . '</span>'
                    . '<small class="d-block text-muted">' . number_format((int) $row->overdueCount, 0, ',', '.') . ' PO</small>';
            })
            ->editColumn('aging0To30', fn ($row) => $this->formatCurrency($row->aging0To30))
            ->editColumn('aging31To60', fn ($row) => $this->formatCurrency($row->aging31To60))
            ->editColumn('aging61To90', fn ($row) => $this->formatCurrency($row->aging61To90))
            ->editColumn('agingOver90', function ($row) {
                $class = (float) $row->agingOver90 > 0 ? 'text-danger fw-semibold' : '';

                return '<span class="' . $class . '">' . $this->formatCurrency($row->agingOver90) . '</span>';
            })
            ->editColumn('unpaidCount', fn ($row) => number_format((int) $row->unpaidCount, 0, ',', '.'))
            ->with(['stats' => $this->summaryStats($rows)])
            ->rawColumns([
                'action', 'totalRemaining', 'dueSoonAmount', 'overdueAmount',
                'aging0To30', 'aging31To60', 'aging61To90', 'agingOver90',
            ])
            ->toJson();
    }

    public function detail(string $supplierCode, Request $request)
    {
        $supplier = Supplier::query()
            ->where('code', $supplierCode)
            ->whereNull('deleted_at')
            ->firstOrFail();

        $summary = SupplierPurchaseDetailReport::dataForFilters(
            $supplierCode,
            $request->startDate,
            $request->endDate,
            $request->purchaseCode
        );

        return view($this->view . 'show')
            ->with('view', $this->view)
            ->with('title', 'Detail Laporan Supplier')
            ->with('supplier', $supplier)
            ->with('totalPurchase', $summary['totalPurchase'])
            ->with('totalItem', $summary['totalItem'])
            ->with('totalQty', $summary['totalQty'])
            ->with('totalAmount', $summary['totalAmount'])
            ->with('totalPaid', $summary['totalPaid'])
            ->with('totalRemaining', $summary['totalRemaining'])
            ->with('dueSoonAmount', $summary['dueSoonAmount'])
            ->with('dueSoonCount', $summary['dueSoonCount'])
            ->with('overdueAmount', $summary['overdueAmount'])
            ->with('overdueCount', $summary['overdueCount'])
            ->with('purchaseCode', $request->purchaseCode)
            ->with('startDate', $request->startDate)
            ->with('endDate', $request->endDate);
    }

    public function datatableDetail(string $supplierCode, Request $request)
    {
        if ($request->ajax()) {
            $data = $this->detailQuery($supplierCode, $request);

            return DataTables::of($data)
                ->addIndexColumn()
                ->setRowClass(function ($row) {
                    if ((float) $row->remainingAmount <= 0) {
                        return '';
                    }

                    if ((int) $row->daysToDue < 0) {
                        return 'table-danger-subtle';
                    }

                    return (int) $row->daysToDue <= 7 ? 'table-warning-subtle' : '';
                })
                ->addColumn('action', function ($row) {
                    return '<button type="button" class="btn btn-icon btn-sm bg-primary-subtle hover-scale" '
                        . 'onclick="showPurchaseItems(\'' . e($row->code) . '\')" '
                        . 'data-bs-toggle="tooltip" title="Lihat item pembelian">'
                        . '<i class="mdi mdi-eye-outline fs-14 text-primary"></i>'
                        . '</button>';
                })
                ->addColumn('purchaseDate', fn ($row) => $this->formatPurchaseDate($row->date, $row->time))
                ->editColumn('dueDate', function ($row) {
                    $label = Carbon::parse($row->effectiveDueDate)->format('d-m-Y');

                    if ($row->dueDateSource !== 'actual') {
                        $label .= '<small class="d-block text-muted">Estimasi dari tanggal PO</small>';
                    }

                    return $label;
                })
                ->addColumn('dueStatus', fn ($row) => $this->dueStatusBadge($row))
                ->editColumn('warehouseName', fn ($row) => e($row->warehouseName ?: '-'))
                ->editColumn('totalItem', fn ($row) => number_format((float) $row->totalItem, 0, ',', '.'))
                ->editColumn('billingAmount', fn ($row) => $this->formatCurrency($row->billingAmount))
                ->editColumn('paidAmountValue', fn ($row) => $this->formatCurrency($row->paidAmountValue))
                ->editColumn('remainingAmount', fn ($row) => '<strong>' . $this->formatCurrency($row->remainingAmount) . '</strong>')
                ->rawColumns(['action', 'dueDate', 'dueStatus', 'remainingAmount'])
                ->toJson();
        }
    }

    public function detailItems(string $purchaseCode)
    {
        $purchase = Purchase::query()
            ->with([
                'supplier',
                'warehouse',
                'details',
                'details.item',
            ])
            ->where('code', $purchaseCode)
            ->whereNull('deleted_at')
            ->firstOrFail();

        $details = $purchase->details->map(function ($detail) {
            $qty = (float) ($detail->receivedQty ?: $detail->qty);
            $price = (float) ($detail->price ?? 0);

            return [
                'itemCode' => $detail->itemCode,
                'itemName' => $detail->item?->name ?? '-',
                'description' => $detail->description ?: '-',
                'qty' => $qty,
                'price' => $price,
                'subtotal' => $price * $qty,
            ];
        })->values();

        return response()->json([
            'code' => $purchase->code,
            'purchaseDate' => $this->formatPurchaseDate($purchase->date, $purchase->time),
            'supplierName' => $purchase->supplier?->name ?? '-',
            'warehouse' => $purchase->warehouse?->name ?? '-',
            'details' => $details,
            'totalQty' => (float) $details->sum('qty'),
            'totalAmount' => (float) $details->sum('subtotal'),
            'billingAmount' => (float) ($purchase->nominal ?: $details->sum('subtotal')),
            'paidAmount' => (float) ($purchase->paidAmount ?? 0),
            'remainingAmount' => max(0, (float) ($purchase->nominal ?: $details->sum('subtotal')) - (float) ($purchase->paidAmount ?? 0)),
            'dueDate' => $this->effectiveDueDate($purchase->dueDate, $purchase->date)->format('d-m-Y'),
            'dueDateEstimated' => ! $this->hasValidDueDate($purchase->dueDate),
            'dueDateSource' => $this->dueDateSource($purchase->dueDate, $purchase->date),
        ]);
    }

    public function excelSupplier(Request $request)
    {
        return Excel::download(new SupplierPurchaseReport($request), 'Supplier-Sparepart-Purchase-Report.xlsx');
    }

    public function pdfSupplier(Request $request)
    {
        $rows = SupplierPurchaseReport::rowsForFilters(
            $request->startDate,
            $request->endDate,
            $request->supplierCode
        );

        $supplierName = null;
        if ($request->filled('supplierCode')) {
            $supplierName = Supplier::query()->where('code', $request->supplierCode)->value('name');
        }

        $mpdf = new Mpdf([
            'orientation' => 'L',
            'format' => [215, 330],
            'tempDir' => storage_path('app/mpdf-temp'),
        ]);

        $mpdf->WriteHTML(
            view($this->view . 'report.supplier-pdf')
                ->with('rows', $rows)
                ->with('supplierName', $supplierName)
                ->with('startDate', $request->startDate)
                ->with('endDate', $request->endDate)
        );

        return $mpdf->Output('Supplier-Sparepart-Purchase-Report.pdf', 'I');
    }

    public function excelSupplierDetail(string $supplierCode, Request $request)
    {
        $request->merge(['supplierCode' => $supplierCode]);

        $supplierName = Supplier::query()->where('code', $supplierCode)->value('name') ?? $supplierCode;
        $filename = 'Supplier-Sparepart-Purchase-Detail-' . $supplierName . '.xlsx';

        return Excel::download(new SupplierPurchaseDetailReport($request), $filename);
    }

    public function pdfSupplierDetail(string $supplierCode, Request $request)
    {
        $supplier = Supplier::query()
            ->where('code', $supplierCode)
            ->whereNull('deleted_at')
            ->firstOrFail();

        $data = SupplierPurchaseDetailReport::dataForFilters(
            $supplierCode,
            $request->startDate,
            $request->endDate,
            $request->purchaseCode
        );

        $mpdf = new Mpdf([
            'orientation' => 'L',
            'format' => [215, 330],
            'tempDir' => storage_path('app/mpdf-temp'),
        ]);

        $mpdf->WriteHTML(
            view($this->view . 'report.supplier-detail-pdf')
                ->with('supplier', $supplier)
                ->with('rows', $data['rows'])
                ->with('totalPurchase', $data['totalPurchase'])
                ->with('totalItem', $data['totalItem'])
                ->with('totalQty', $data['totalQty'])
                ->with('totalAmount', $data['totalAmount'])
                ->with('totalPaid', $data['totalPaid'])
                ->with('totalRemaining', $data['totalRemaining'])
                ->with('startDate', $request->startDate)
                ->with('endDate', $request->endDate)
        );

        return $mpdf->Output('Supplier-Sparepart-Purchase-Detail-' . $supplier->name . '.pdf', 'I');
    }

    private function summaryQuery(Request $request): Builder|QueryBuilder
    {
        $query = Purchase::query()
            ->select([
                'supplier.code as supplierCode',
                'supplier.name as supplierName',
                DB::raw('COUNT(DISTINCT purchase.code) as totalPurchase'),
                DB::raw('COUNT(DISTINCT purchase_detail.itemCode) as totalItem'),
                DB::raw('COALESCE(SUM(COALESCE(NULLIF(purchase_detail.receivedQty, 0), purchase_detail.qty)), 0) as totalQty'),
                DB::raw('COALESCE(SUM(COALESCE(purchase_detail.price, 0) * COALESCE(NULLIF(purchase_detail.receivedQty, 0), purchase_detail.qty)), 0) as totalAmount'),
                DB::raw('COALESCE(pa.totalPaid, 0) as totalPaid'),
                DB::raw('COALESCE(pa.unpaidCount, 0) as unpaidCount'),
            ])
            ->join('supplier', function ($join) {
                $join->on('supplier.code', '=', 'purchase.supplierCode')
                    ->whereNull('supplier.deleted_at');
            })
            ->leftJoin('purchase_detail', function ($join) {
                $join->on('purchase_detail.purchaseCode', '=', 'purchase.code')
                    ->whereNull('purchase_detail.deleted_at');
            })
            ->leftJoinSub($this->paymentAggregate($request), 'pa', 'pa.supplierCode', '=', 'supplier.code')
            ->whereNull('purchase.deleted_at')
            ->groupBy('supplier.code', 'supplier.name', 'pa.totalPaid', 'pa.unpaidCount')
            ->orderBy('supplier.name');

        if ($request->filled('supplierCode')) {
            $query->where('supplier.code', $request->supplierCode);
        }

        $this->applyDateFilter($query, $request->startDate, $request->endDate);

        return $query;
    }

    /**
     * Agregat pembayaran (terbayar & jumlah PO belum lunas) per supplier.
     * Dipakai agar laporan per supplier juga menampilkan status hutang.
     */
    private function paymentAggregate(Request $request): QueryBuilder
    {
        $query = DB::table('purchase')
            ->whereNull('purchase.deleted_at')
            ->groupBy('purchase.supplierCode')
            ->selectRaw('purchase.supplierCode as supplierCode')
            ->selectRaw('COALESCE(SUM(COALESCE(purchase.paidAmount, 0)), 0) as totalPaid')
            ->selectRaw("COALESCE(SUM(CASE WHEN purchase.paymentStatus <> 'Paid' THEN 1 ELSE 0 END), 0) as unpaidCount");

        if ($request->filled('supplierCode')) {
            $query->where('purchase.supplierCode', $request->supplierCode);
        }

        $this->applyDateFilter($query, $request->startDate, $request->endDate);

        return $query;
    }

    private function detailSummaryQuery(string $supplierCode, Request $request): Builder|QueryBuilder
    {
        $query = Purchase::query()
            ->select([
                DB::raw('COUNT(DISTINCT purchase.code) as totalPurchase'),
                DB::raw('COUNT(DISTINCT purchase_detail.itemCode) as totalItem'),
                DB::raw('COALESCE(SUM(COALESCE(NULLIF(purchase_detail.receivedQty, 0), purchase_detail.qty)), 0) as totalQty'),
                DB::raw('COALESCE(SUM(COALESCE(purchase_detail.price, 0) * COALESCE(NULLIF(purchase_detail.receivedQty, 0), purchase_detail.qty)), 0) as totalAmount'),
            ])
            ->leftJoin('purchase_detail', function ($join) {
                $join->on('purchase_detail.purchaseCode', '=', 'purchase.code')
                    ->whereNull('purchase_detail.deleted_at');
            })
            ->where('purchase.supplierCode', $supplierCode)
            ->whereNull('purchase.deleted_at');

        if ($request->filled('purchaseCode')) {
            $query->where('purchase.code', 'like', '%' . $request->purchaseCode . '%');
        }

        $this->applyDateFilter($query, $request->startDate, $request->endDate);

        return $query;
    }

    private function detailQuery(string $supplierCode, Request $request): Builder|QueryBuilder
    {
        $detailTotals = DB::table('purchase_detail')
            ->whereNull('purchase_detail.deleted_at')
            ->groupBy('purchase_detail.purchaseCode')
            ->selectRaw('purchase_detail.purchaseCode as purchaseCode')
            ->selectRaw('COUNT(DISTINCT purchase_detail.itemCode) as totalItem')
            ->selectRaw('COALESCE(SUM(COALESCE(NULLIF(purchase_detail.receivedQty, 0), purchase_detail.qty, 0)), 0) as totalQty')
            ->selectRaw('COALESCE(SUM(COALESCE(purchase_detail.price, 0) * COALESCE(NULLIF(purchase_detail.qty, 0), purchase_detail.receivedQty, 0)), 0) as detailSum');

        $billing = 'COALESCE(NULLIF(purchase.nominal, 0), detail_totals.detailSum, 0)';
        $remaining = "GREATEST({$billing} - COALESCE(purchase.paidAmount, 0), 0)";
        $effectiveDueDate = $this->effectiveDueDateExpression();

        $query = Purchase::query()
            ->select([
                'purchase.code',
                'purchase.date',
                'purchase.time',
                'purchase.dueDate',
                'warehouse.name as warehouseName',
            ])
            ->selectRaw('COALESCE(detail_totals.totalItem, 0) as totalItem')
            ->selectRaw('COALESCE(detail_totals.totalQty, 0) as totalQty')
            ->selectRaw("{$billing} as billingAmount")
            ->selectRaw('COALESCE(purchase.paidAmount, 0) as paidAmountValue')
            ->selectRaw("{$remaining} as remainingAmount")
            ->selectRaw("{$effectiveDueDate} as effectiveDueDate")
            ->selectRaw("DATEDIFF({$effectiveDueDate}, CURRENT_DATE) as daysToDue")
            ->selectRaw("CASE WHEN YEAR(purchase.dueDate) BETWEEN 2020 AND 2030 THEN 'actual' WHEN YEAR(purchase.date) BETWEEN 1000 AND 9999 THEN 'purchase_date' ELSE 'today' END as dueDateSource")
            ->leftJoin('warehouse', function ($join) {
                $join->on('warehouse.code', '=', 'purchase.warehouseCode')
                    ->whereNull('warehouse.deleted_at');
            })
            ->leftJoinSub($detailTotals, 'detail_totals', 'detail_totals.purchaseCode', '=', 'purchase.code')
            ->where('purchase.supplierCode', $supplierCode)
            ->whereNull('purchase.deleted_at')
            ->orderByDesc('purchase.date')
            ->orderByDesc('purchase.time');

        if ($request->filled('purchaseCode')) {
            $query->where('purchase.code', 'like', '%' . $request->purchaseCode . '%');
        }

        $this->applyDateFilter($query, $request->startDate, $request->endDate);

        return $query;
    }

    private function detailRowsQuery(string $supplierCode, Request $request): Builder|QueryBuilder
    {
        $query = Purchase::query()
            ->with([
                'supplier',
                'warehouse',
                'details',
                'details.item',
            ])
            ->where('supplierCode', $supplierCode)
            ->whereNull('deleted_at')
            ->orderByDesc('date')
            ->orderByDesc('time');

        if ($request->filled('purchaseCode')) {
            $query->where('code', 'like', '%' . $request->purchaseCode . '%');
        }

        $this->applyDateFilter($query, $request->startDate, $request->endDate);

        return $query;
    }

    private function formatPurchaseDate($date, $time): string
    {
        if (! $date) {
            return '-';
        }

        $formattedDate = Carbon::parse($date)->format('d-m-Y');
        $formattedTime = $time ? Carbon::parse($time)->format('H:i') : null;

        return $formattedTime ? $formattedDate . ' ' . $formattedTime : $formattedDate;
    }

    private function summaryStats($rows): array
    {
        return [
            'supplierCount' => $rows->count(),
            'totalRemaining' => (float) $rows->sum('totalRemaining'),
            'dueSoonAmount' => (float) $rows->sum('dueSoonAmount'),
            'dueSoonCount' => (int) $rows->sum('dueSoonCount'),
            'overdueAmount' => (float) $rows->sum('overdueAmount'),
            'overdueCount' => (int) $rows->sum('overdueCount'),
            'agingOver90' => (float) $rows->sum('agingOver90'),
            'unpaidCount' => (int) $rows->sum('unpaidCount'),
        ];
    }

    private function formatCurrency($amount): string
    {
        return 'Rp ' . number_format((float) $amount, 0, ',', '.');
    }

    private function dueStatusBadge($row): string
    {
        if ((float) $row->remainingAmount <= 0) {
            return '<span class="badge bg-success-subtle text-success-emphasis">Lunas</span>';
        }

        $days = (int) $row->daysToDue;

        if ($days < 0) {
            return '<span class="badge bg-danger-subtle text-danger-emphasis">Overdue ' . abs($days) . ' hari</span>';
        }

        if ($days === 0) {
            return '<span class="badge bg-danger-subtle text-danger-emphasis">Jatuh tempo hari ini</span>';
        }

        if ($days <= 7) {
            return '<span class="badge bg-warning-subtle text-warning-emphasis">Jatuh tempo ' . $days . ' hari</span>';
        }

        return '<span class="badge bg-info-subtle text-info-emphasis">Belum jatuh tempo</span>';
    }

    private function effectiveDueDateExpression(): string
    {
        return 'COALESCE(CASE WHEN YEAR(purchase.dueDate) BETWEEN 2020 AND 2030 THEN DATE(purchase.dueDate) END, CASE WHEN YEAR(purchase.date) BETWEEN 1000 AND 9999 THEN DATE(purchase.date) END, CURRENT_DATE)';
    }

    private function hasValidDueDate($dueDate): bool
    {
        if (! $dueDate) {
            return false;
        }

        try {
            return Carbon::parse($dueDate)->year >= 2020 && Carbon::parse($dueDate)->year <= 2030;
        } catch (\Throwable) {
            return false;
        }
    }

    private function effectiveDueDate($dueDate, $purchaseDate): Carbon
    {
        if ($this->hasValidDueDate($dueDate)) {
            return Carbon::parse($dueDate)->startOfDay();
        }

        if ($purchaseDate) {
            try {
                return Carbon::parse($purchaseDate)->startOfDay();
            } catch (\Throwable) {
                // Fall through to today when the stored purchase date is invalid.
            }
        }

        return Carbon::today();
    }

    private function dueDateSource($dueDate, $purchaseDate): string
    {
        if ($this->hasValidDueDate($dueDate)) {
            return 'actual';
        }

        if ($purchaseDate) {
            try {
                Carbon::parse($purchaseDate);

                return 'purchase_date';
            } catch (\Throwable) {
                // Fall through to today's date.
            }
        }

        return 'today';
    }

    private function applyDateFilter(Builder|QueryBuilder $query, ?string $startDate, ?string $endDate): void
    {
        if ($startDate && $endDate && $startDate === $endDate) {
            $query->whereDate('purchase.date', '=', $startDate);

            return;
        }

        if ($startDate) {
            $query->whereDate('purchase.date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('purchase.date', '<=', $endDate);
        }
    }
}
