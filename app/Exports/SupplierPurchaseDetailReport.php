<?php

namespace App\Exports;

use App\Models\Inventory\Supplier;
use App\Models\Purchasing\Purchase;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SupplierPurchaseDetailReport implements FromView, ShouldAutoSize
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
        $supplierCode = $request->supplierCode;

        $supplier = Supplier::query()
            ->where('code', $supplierCode)
            ->whereNull('deleted_at')
            ->firstOrFail();

        $data = self::dataForFilters(
            $supplierCode,
            $request->startDate,
            $request->endDate,
            $request->purchaseCode
        );

        return view('report.supplier.report.supplier-detail-excel')
            ->with('supplier', $supplier)
            ->with('rows', $data['rows'])
            ->with('totalPurchase', $data['totalPurchase'])
            ->with('totalItem', $data['totalItem'])
            ->with('totalQty', $data['totalQty'])
            ->with('totalAmount', $data['totalAmount'])
            ->with('totalPaid', $data['totalPaid'])
            ->with('totalRemaining', $data['totalRemaining'])
            ->with('startDate', $request->startDate)
            ->with('endDate', $request->endDate);
    }

    /**
     * Shared by the detail page, Excel export, and PDF export so every output
     * uses the same filters and payable calculations.
     *
     * @return array{rows: Collection, totalPurchase: int, totalItem: int, totalQty: float, totalAmount: float, totalPaid: float, totalRemaining: float, dueSoonAmount: float, dueSoonCount: int, overdueAmount: float, overdueCount: int}
     */
    public static function dataForFilters(
        string $supplierCode,
        ?string $startDate,
        ?string $endDate,
        ?string $purchaseCode = null
    ): array {
        $rows = self::rowsQuery($supplierCode, $startDate, $endDate, $purchaseCode)->get();
        $today = Carbon::today();
        $unpaidRows = $rows->filter(fn ($purchase) => (float) $purchase->remainingAmount > 0);
        $dueSoonRows = $unpaidRows->filter(function ($purchase) use ($today) {
            $daysToDue = (int) $today->diffInDays(Carbon::parse($purchase->effectiveDueDate)->startOfDay(), false);

            return $daysToDue >= 0 && $daysToDue <= 7;
        });
        $overdueRows = $unpaidRows->filter(function ($purchase) use ($today) {
            return $today->diffInDays(Carbon::parse($purchase->effectiveDueDate)->startOfDay(), false) < 0;
        });

        return [
            'rows' => $rows,
            'totalPurchase' => $rows->count(),
            'totalItem' => self::totalDistinctItems($rows),
            'totalQty' => self::totalProcurementQty($rows),
            'totalAmount' => (float) $rows->sum(fn ($purchase) => (float) $purchase->billingAmount),
            'totalPaid' => (float) $rows->sum(fn ($purchase) => (float) $purchase->paidAmountValue),
            'totalRemaining' => (float) $rows->sum(fn ($purchase) => (float) $purchase->remainingAmount),
            'dueSoonAmount' => (float) $dueSoonRows->sum(fn ($purchase) => (float) $purchase->remainingAmount),
            'dueSoonCount' => $dueSoonRows->count(),
            'overdueAmount' => (float) $overdueRows->sum(fn ($purchase) => (float) $purchase->remainingAmount),
            'overdueCount' => $overdueRows->count(),
        ];
    }

    private static function rowsQuery(
        string $supplierCode,
        ?string $startDate,
        ?string $endDate,
        ?string $purchaseCode
    ): Builder {
        $billing = self::billingExpression();
        $remaining = "GREATEST({$billing} - COALESCE(purchase.paidAmount, 0), 0)";

        $query = Purchase::query()
            ->select('purchase.*')
            ->selectRaw("{$billing} as billingAmount")
            ->selectRaw('COALESCE(purchase.paidAmount, 0) as paidAmountValue')
            ->selectRaw("{$remaining} as remainingAmount")
            ->selectRaw(self::effectiveDueDateExpression() . ' as effectiveDueDate')
            ->selectRaw("CASE WHEN YEAR(purchase.dueDate) BETWEEN 2020 AND 2030 THEN 'actual' WHEN YEAR(purchase.date) BETWEEN 1000 AND 9999 THEN 'purchase_date' ELSE 'today' END as dueDateSource")
            ->leftJoinSub(self::detailAggregate(), 'detail_totals', 'detail_totals.purchaseCode', '=', 'purchase.code')
            ->with([
                'supplier',
                'warehouse',
                'details',
                'details.item',
            ])
            ->where('purchase.supplierCode', $supplierCode)
            ->whereNull('purchase.deleted_at')
            ->orderByDesc('purchase.date')
            ->orderByDesc('purchase.time');

        if ($purchaseCode) {
            $query->where('purchase.code', 'like', '%' . $purchaseCode . '%');
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

    /**
     * @param  Collection<int, Purchase>  $rows
     */
    private static function totalDistinctItems(Collection $rows): int
    {
        return $rows
            ->flatMap(fn ($purchase) => $purchase->details->pluck('itemCode'))
            ->filter()
            ->unique()
            ->count();
    }

    /**
     * @param  Collection<int, Purchase>  $rows
     */
    private static function totalProcurementQty(Collection $rows): float
    {
        return (float) $rows->sum(function ($purchase) {
            return $purchase->details->sum(function ($detail) {
                return (float) (($detail->receivedQty ?: $detail->qty) ?? 0);
            });
        });
    }

    /**
     * @param  Builder<Purchase>  $query
     */
    private static function applyDateFilter(Builder $query, ?string $startDate, ?string $endDate): void
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
