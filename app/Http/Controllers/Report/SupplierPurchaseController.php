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
        $this->title = 'Supplier Sparepart Purchase Report';
        $this->view = 'report.supplier.';
    }

    public function index()
    {
        $supplier = Supplier::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        return view($this->view . 'index')
            ->with('view', $this->view)
            ->with('supplier', $supplier)
            ->with('title', $this->title);
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->summaryQuery($request);

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) use ($request) {
                    $detailUrl = route('report.supplier.detail', ['supplierCode' => $row->supplierCode]);

                    $query = array_filter([
                        'startDate' => $request->startDate,
                        'endDate' => $request->endDate,
                    ]);

                    if (! empty($query)) {
                        $detailUrl .= '?' . http_build_query($query);
                    }

                    return '<a href="' . $detailUrl . '" class="btn btn-icon btn-sm bg-info-subtle" data-bs-toggle="tooltip" title="Detail">'
                        . '<i class="mdi mdi-eye-outline fs-14 text-info"></i>'
                        . '</a>';
                })
                ->editColumn('totalPurchase', function ($row) {
                    return number_format((float) $row->totalPurchase, 0, ',', '.');
                })
                ->editColumn('totalItem', function ($row) {
                    return number_format((float) $row->totalItem, 0, ',', '.');
                })
                ->editColumn('totalQty', function ($row) {
                    return number_format((float) $row->totalQty, 1, ',', '.');
                })
                ->editColumn('totalAmount', function ($row) {
                    return number_format((float) $row->totalAmount, 0, ',', '.');
                })
                ->rawColumns(['action', 'totalPurchase', 'totalItem', 'totalQty', 'totalAmount'])
                ->toJson();
        }
    }

    public function detail(string $supplierCode, Request $request)
    {
        $supplier = Supplier::query()
            ->where('code', $supplierCode)
            ->whereNull('deleted_at')
            ->firstOrFail();

        $summary = $this->detailSummaryQuery($supplierCode, $request)->first();

        return view($this->view . 'show')
            ->with('view', $this->view)
            ->with('title', 'Supplier Sparepart Purchase Detail Report')
            ->with('supplier', $supplier)
            ->with('totalPurchase', (int) ($summary->totalPurchase ?? 0))
            ->with('totalItem', (int) ($summary->totalItem ?? 0))
            ->with('totalQty', (float) ($summary->totalQty ?? 0))
            ->with('totalAmount', (float) ($summary->totalAmount ?? 0))
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
                ->addColumn('action', function ($row) {
                    return '<button type="button" class="btn btn-icon btn-sm bg-info-subtle" '
                        . 'onclick="showPurchaseItems(\'' . $row->code . '\')" '
                        . 'data-bs-toggle="tooltip" title="Detail">'
                        . '<i class="mdi mdi-eye-outline fs-14 text-info"></i>'
                        . '</button>';
                })
                ->addColumn('purchaseDate', function ($row) {
                    return $this->formatPurchaseDate($row->date, $row->time);
                })
                ->editColumn('dueDate', function ($row) {
                    if (! $row->dueDate) {
                        return '-';
                    }

                    return Carbon::parse($row->dueDate)->format('d-m-Y');
                })
                ->editColumn('warehouseName', function ($row) {
                    return $row->warehouseName ?: '-';
                })
                ->editColumn('totalItem', function ($row) {
                    return number_format((float) $row->totalItem, 0, ',', '.');
                })
                ->editColumn('totalQty', function ($row) {
                    return number_format((float) $row->totalQty, 1, ',', '.');
                })
                ->editColumn('totalAmount', function ($row) {
                    return number_format((float) $row->totalAmount, 0, ',', '.');
                })
                ->rawColumns(['action', 'purchaseDate', 'dueDate', 'warehouseName', 'totalItem', 'totalQty', 'totalAmount'])
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
        ]);
    }

    public function excelSupplier(Request $request)
    {
        return Excel::download(new SupplierPurchaseReport($request), 'Supplier-Sparepart-Purchase-Report.xlsx');
    }

    public function pdfSupplier(Request $request)
    {
        $rows = $this->summaryQuery($request)->get();

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

        $summary = $this->detailSummaryQuery($supplierCode, $request)->first();

        $rows = $this->detailRowsQuery($supplierCode, $request)->get();

        $mpdf = new Mpdf([
            'orientation' => 'L',
            'format' => [215, 330],
            'tempDir' => storage_path('app/mpdf-temp'),
        ]);

        $mpdf->WriteHTML(
            view($this->view . 'report.supplier-detail-pdf')
                ->with('supplier', $supplier)
                ->with('rows', $rows)
                ->with('totalPurchase', (int) ($summary->totalPurchase ?? 0))
                ->with('totalItem', (int) ($summary->totalItem ?? 0))
                ->with('totalQty', (float) ($summary->totalQty ?? 0))
                ->with('totalAmount', (float) ($summary->totalAmount ?? 0))
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
            ])
            ->join('supplier', function ($join) {
                $join->on('supplier.code', '=', 'purchase.supplierCode')
                    ->whereNull('supplier.deleted_at');
            })
            ->leftJoin('purchase_detail', function ($join) {
                $join->on('purchase_detail.purchaseCode', '=', 'purchase.code')
                    ->whereNull('purchase_detail.deleted_at');
            })
            ->whereNull('purchase.deleted_at')
            ->groupBy('supplier.code', 'supplier.name')
            ->orderBy('supplier.name');

        if ($request->filled('supplierCode')) {
            $query->where('supplier.code', $request->supplierCode);
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
        $query = Purchase::query()
            ->select([
                'purchase.code',
                'purchase.date',
                'purchase.time',
                'purchase.dueDate',
                'warehouse.name as warehouseName',
                DB::raw('COUNT(DISTINCT purchase_detail.itemCode) as totalItem'),
                DB::raw('COALESCE(SUM(COALESCE(NULLIF(purchase_detail.receivedQty, 0), purchase_detail.qty)), 0) as totalQty'),
                DB::raw('COALESCE(SUM(COALESCE(purchase_detail.price, 0) * COALESCE(NULLIF(purchase_detail.receivedQty, 0), purchase_detail.qty)), 0) as totalAmount'),
            ])
            ->leftJoin('warehouse', function ($join) {
                $join->on('warehouse.code', '=', 'purchase.warehouseCode')
                    ->whereNull('warehouse.deleted_at');
            })
            ->leftJoin('purchase_detail', function ($join) {
                $join->on('purchase_detail.purchaseCode', '=', 'purchase.code')
                    ->whereNull('purchase_detail.deleted_at');
            })
            ->where('purchase.supplierCode', $supplierCode)
            ->whereNull('purchase.deleted_at')
            ->groupBy('purchase.code', 'purchase.date', 'purchase.time', 'purchase.dueDate', 'warehouse.name')
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

        if (! $formattedTime) {
            return $formattedDate;
        }

        return $formattedDate . ' ' . $formattedTime;
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
