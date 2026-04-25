<?php

namespace App\Exports;

use App\Models\Inventory\Supplier;
use App\Models\Purchasing\Purchase;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SupplierPurchaseDetailReport implements FromView, ShouldAutoSize
{
    use Exportable;

    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function view(): View
    {
        $request = $this->request;
        $supplierCode = $request->supplierCode;

        $supplier = Supplier::query()
            ->where('code', $supplierCode)
            ->whereNull('deleted_at')
            ->firstOrFail();

        $summaryQuery = Purchase::query()
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
            $summaryQuery->where('purchase.code', 'like', '%' . $request->purchaseCode . '%');
        }

        $this->applyDateFilter($summaryQuery, $request->startDate, $request->endDate);

        $summary = $summaryQuery->first();

        $rows = Purchase::query()
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
            $rows->where('code', 'like', '%' . $request->purchaseCode . '%');
        }

        $this->applyDateFilter($rows, $request->startDate, $request->endDate);

        return view('report.supplier.report.supplier-detail-excel')
            ->with('supplier', $supplier)
            ->with('rows', $rows->get())
            ->with('totalPurchase', (int) ($summary->totalPurchase ?? 0))
            ->with('totalItem', (int) ($summary->totalItem ?? 0))
            ->with('totalQty', (float) ($summary->totalQty ?? 0))
            ->with('totalAmount', (float) ($summary->totalAmount ?? 0))
            ->with('startDate', $request->startDate)
            ->with('endDate', $request->endDate);
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
