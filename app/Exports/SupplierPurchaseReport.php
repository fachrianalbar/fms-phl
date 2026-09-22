<?php

namespace App\Exports;

use App\Models\Inventory\Supplier;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SupplierPurchaseReport implements FromView, ShouldAutoSize
{
    use Exportable;

    protected $request;

    /**
     * @param  mixed  $request
     */
    public function __construct($request)
    {
        $this->request = $request;
    }

    public function view(): View
    {
        $request = $this->request;
        $supplierCode = $request->filled('supplierCode') ? $request->supplierCode : null;
        $supplierName = null;

        if ($supplierCode) {
            $supplierName = Supplier::query()
                ->where('code', $supplierCode)
                ->whereNull('deleted_at')
                ->value('name');
        }

        return view('report.supplier.report.supplier-excel')
            ->with('rows', self::rowsForFilters($request->startDate, $request->endDate, $supplierCode))
            ->with('supplierName', $supplierName)
            ->with('startDate', $request->startDate)
            ->with('endDate', $request->endDate);
    }

    /**
     * Build the supplier aging rows for Excel and for the PDF view, whose
     * controller still supplies the legacy summary shape.
     *
     * @return Collection<int, object>
     */
    public static function rowsForFilters(?string $startDate, ?string $endDate, ?string $supplierCode = null): Collection
    {
        $aging = self::agingAggregate($startDate, $endDate, $supplierCode);
        $procurement = self::procurementAggregate($startDate, $endDate, $supplierCode);

        $query = DB::table('supplier')
            ->leftJoinSub($aging, 'aging', 'aging.supplierCode', '=', 'supplier.code')
            ->leftJoinSub($procurement, 'procurement', 'procurement.supplierCode', '=', 'supplier.code')
            ->whereNull('supplier.deleted_at')
            ->whereNotNull('aging.supplierCode')
            ->select([
                'supplier.code as supplierCode',
                'supplier.name as supplierName',
                'aging.totalPurchase',
                DB::raw('COALESCE(procurement.totalItem, 0) as totalItem'),
                DB::raw('COALESCE(procurement.totalQty, 0) as totalQty'),
                'aging.totalBilling',
                'aging.totalAmount',
                'aging.totalPaid',
                'aging.totalRemaining',
                'aging.unpaidCount',
                'aging.dueSoonAmount',
                'aging.dueSoonCount',
                'aging.overdueAmount',
                'aging.overdueCount',
                'aging.notDueAmount',
                'aging.aging0To30',
                'aging.aging31To60',
                'aging.aging61To90',
                'aging.agingOver90',
            ])
            ->orderBy('supplier.name');

        if ($supplierCode) {
            $query->where('supplier.code', $supplierCode);
        }

        return $query->get();
    }

    private static function agingAggregate(?string $startDate, ?string $endDate, ?string $supplierCode): QueryBuilder
    {
        $poRows = self::poLevelRows($startDate, $endDate, $supplierCode);

        return DB::query()
            ->fromSub($poRows, 'po')
            ->groupBy('po.supplierCode')
            ->selectRaw('po.supplierCode as supplierCode')
            ->selectRaw('COUNT(*) as totalPurchase')
            ->selectRaw('COALESCE(SUM(po.billingAmount), 0) as totalBilling')
            ->selectRaw('COALESCE(SUM(po.billingAmount), 0) as totalAmount')
            ->selectRaw('COALESCE(SUM(po.paidAmount), 0) as totalPaid')
            ->selectRaw('COALESCE(SUM(po.remainingAmount), 0) as totalRemaining')
            ->selectRaw('COALESCE(SUM(CASE WHEN po.remainingAmount > 0 THEN 1 ELSE 0 END), 0) as unpaidCount')
            ->selectRaw('COALESCE(SUM(CASE WHEN po.remainingAmount > 0 AND po.effectiveDueDate BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY) THEN po.remainingAmount ELSE 0 END), 0) as dueSoonAmount')
            ->selectRaw('COALESCE(SUM(CASE WHEN po.remainingAmount > 0 AND po.effectiveDueDate BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY) THEN 1 ELSE 0 END), 0) as dueSoonCount')
            ->selectRaw('COALESCE(SUM(CASE WHEN po.remainingAmount > 0 AND po.effectiveDueDate < CURRENT_DATE THEN po.remainingAmount ELSE 0 END), 0) as overdueAmount')
            ->selectRaw('COALESCE(SUM(CASE WHEN po.remainingAmount > 0 AND po.effectiveDueDate < CURRENT_DATE THEN 1 ELSE 0 END), 0) as overdueCount')
            ->selectRaw('COALESCE(SUM(CASE WHEN po.remainingAmount > 0 AND po.effectiveDueDate >= CURRENT_DATE THEN po.remainingAmount ELSE 0 END), 0) as notDueAmount')
            ->selectRaw('COALESCE(SUM(CASE WHEN po.remainingAmount > 0 AND DATEDIFF(CURRENT_DATE, po.effectiveDueDate) BETWEEN 1 AND 30 THEN po.remainingAmount ELSE 0 END), 0) as aging0To30')
            ->selectRaw('COALESCE(SUM(CASE WHEN po.remainingAmount > 0 AND DATEDIFF(CURRENT_DATE, po.effectiveDueDate) BETWEEN 31 AND 60 THEN po.remainingAmount ELSE 0 END), 0) as aging31To60')
            ->selectRaw('COALESCE(SUM(CASE WHEN po.remainingAmount > 0 AND DATEDIFF(CURRENT_DATE, po.effectiveDueDate) BETWEEN 61 AND 90 THEN po.remainingAmount ELSE 0 END), 0) as aging61To90')
            ->selectRaw('COALESCE(SUM(CASE WHEN po.remainingAmount > 0 AND DATEDIFF(CURRENT_DATE, po.effectiveDueDate) > 90 THEN po.remainingAmount ELSE 0 END), 0) as agingOver90');
    }

    private static function poLevelRows(?string $startDate, ?string $endDate, ?string $supplierCode): QueryBuilder
    {
        $billing = self::billingExpression();
        $remaining = "GREATEST({$billing} - COALESCE(purchase.paidAmount, 0), 0)";

        $query = DB::table('purchase')
            ->leftJoinSub(self::detailAggregate(), 'detail_totals', 'detail_totals.purchaseCode', '=', 'purchase.code')
            ->whereNull('purchase.deleted_at')
            ->selectRaw('purchase.supplierCode as supplierCode')
            ->selectRaw("{$billing} as billingAmount")
            ->selectRaw('COALESCE(purchase.paidAmount, 0) as paidAmount')
            ->selectRaw("{$remaining} as remainingAmount")
            ->selectRaw(self::effectiveDueDateExpression() . ' as effectiveDueDate');

        if ($supplierCode) {
            $query->where('purchase.supplierCode', $supplierCode);
        }

        self::applyDateFilter($query, $startDate, $endDate);

        return $query;
    }

    private static function procurementAggregate(?string $startDate, ?string $endDate, ?string $supplierCode): QueryBuilder
    {
        $query = DB::table('purchase')
            ->leftJoin('purchase_detail', function ($join) {
                $join->on('purchase_detail.purchaseCode', '=', 'purchase.code')
                    ->whereNull('purchase_detail.deleted_at');
            })
            ->whereNull('purchase.deleted_at')
            ->groupBy('purchase.supplierCode')
            ->selectRaw('purchase.supplierCode as supplierCode')
            ->selectRaw('COUNT(DISTINCT purchase_detail.itemCode) as totalItem')
            ->selectRaw('COALESCE(SUM(COALESCE(NULLIF(purchase_detail.receivedQty, 0), purchase_detail.qty, 0)), 0) as totalQty');

        if ($supplierCode) {
            $query->where('purchase.supplierCode', $supplierCode);
        }

        self::applyDateFilter($query, $startDate, $endDate);

        return $query;
    }

    private static function detailAggregate(): QueryBuilder
    {
        return DB::table('purchase_detail')
            ->whereNull('purchase_detail.deleted_at')
            ->groupBy('purchase_detail.purchaseCode')
            ->selectRaw('purchase_detail.purchaseCode as purchaseCode')
            ->selectRaw('COALESCE(SUM(COALESCE(purchase_detail.price, 0) * COALESCE(NULLIF(purchase_detail.qty, 0), purchase_detail.receivedQty, 0)), 0) as detailSum');
    }

    private static function billingExpression(): string
    {
        return 'COALESCE(NULLIF(purchase.nominal, 0), detail_totals.detailSum, 0)';
    }

    private static function effectiveDueDateExpression(): string
    {
        return 'COALESCE(CASE WHEN YEAR(purchase.dueDate) BETWEEN 2020 AND 2030 THEN DATE(purchase.dueDate) END, CASE WHEN YEAR(purchase.date) BETWEEN 1000 AND 9999 THEN DATE(purchase.date) END, CURRENT_DATE)';
    }

    private static function applyDateFilter(QueryBuilder $query, ?string $startDate, ?string $endDate): void
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
