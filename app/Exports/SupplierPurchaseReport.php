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

class SupplierPurchaseReport implements FromView, ShouldAutoSize
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

        $supplierName = null;
        if ($request->filled('supplierCode')) {
            $supplierName = Supplier::query()->where('code', $request->supplierCode)->value('name');
        }

        return view('report.supplier.report.supplier-excel')
            ->with('rows', $query->get())
            ->with('supplierName', $supplierName)
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
