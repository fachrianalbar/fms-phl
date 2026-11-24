<?php

namespace App\Services\Report;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OmsetReportService
{
    /**
     * Build the filtered base query for Laporan Omset with pre-aggregated
     * omset (routeAmount + on-charge), biaya (off-charge + external vendor price),
     * profit, and payment status.
     */
    public function buildQuery(Request $request): Builder
    {
        $costAgg = DB::table('order_cost')
            ->selectRaw("
                orderCode,
                SUM(CASE WHEN LOWER(COALESCE(type, '')) = 'on charge' THEN COALESCE(nominal, 0) ELSE 0 END) as on_charge_total,
                SUM(CASE WHEN LOWER(COALESCE(type, '')) != 'on charge' THEN COALESCE(nominal, 0) ELSE 0 END) as off_charge_total
            ")
            ->groupBy('orderCode');

        $invAgg = DB::table('invoice_detail as idet')
            ->join('invoice as inv', function ($j) {
                $j->on('inv.code', '=', 'idet.invoiceCode')
                    ->whereNull('inv.deleted_at');
            })
            ->selectRaw('idet.orderCode, MAX(inv.status) as inv_status')
            ->groupBy('idet.orderCode');

        $payAgg = DB::table('order_payment')
            ->selectRaw('orderCode, MAX(status) as op_status')
            ->groupBy('orderCode');

        $omsetExpr = '(COALESCE(o.routeAmount, 0) + COALESCE(oc.on_charge_total, 0))';
        $vendorCostExpr = "(CASE WHEN fc.type = 'External' THEN (CASE WHEN COALESCE(o.vendorPrice, 0) > 0 THEN o.vendorPrice ELSE COALESCE(o.personalVendorPrice, 0) END) ELSE 0 END)";
        $biayaExpr = "({$vendorCostExpr} + COALESCE(oc.off_charge_total, 0))";
        $profitExpr = "({$omsetExpr} - {$biayaExpr})";
        $paymentStatusExpr = "(CASE
            WHEN inv.inv_status = 3 OR op.op_status = 1 THEN 'paid'
            WHEN inv.inv_status = 2 THEN 'partial'
            ELSE 'unpaid'
        END)";

        $fleetDedup = DB::table('fleet')
            ->selectRaw('code, MAX(plateNumber) as plateNumber, MAX(fleetCompanyCode) as fleetCompanyCode')
            ->whereNull('deleted_at')
            ->groupBy('code');

        $query = DB::table('order as o')
            ->leftJoinSub($costAgg, 'oc', 'oc.orderCode', '=', 'o.code')
            ->leftJoinSub($invAgg, 'inv', 'inv.orderCode', '=', 'o.code')
            ->leftJoinSub($payAgg, 'op', 'op.orderCode', '=', 'o.code')
            ->leftJoin('customer as c', function ($j) {
                $j->on('c.code', '=', 'o.customerCode')->whereNull('c.deleted_at');
            })
            ->leftJoinSub($fleetDedup, 'f', 'f.code', '=', 'o.fleetCode')
            ->leftJoin('fleet_company as fc', function ($j) {
                $j->on('fc.code', '=', 'f.fleetCompanyCode')->whereNull('fc.deleted_at');
            })
            ->leftJoin('company as comp', function ($j) {
                $j->on('comp.code', '=', 'fc.companyCode')->whereNull('comp.deleted_at');
            })
            ->leftJoin('employee as emp', function ($j) {
                $j->on('emp.code', '=', 'o.driverCode')->whereNull('emp.deleted_at');
            })
            ->leftJoin('route as r', function ($j) {
                $j->on('r.code', '=', 'o.routeCode')->whereNull('r.deleted_at');
            })
            ->leftJoin('location as orig', 'orig.code', '=', 'r.originLocationCode')
            ->leftJoin('location as dest', 'dest.code', '=', 'r.destinationLocationCode')
            ->leftJoin('order_status as os', 'os.code', '=', 'o.status')
            ->whereNull('o.deleted_at')
            ->selectRaw("
                o.id,
                o.code as orderCode,
                o.shipmentNumber,
                o.orderDate,
                o.status as orderStatusCode,
                COALESCE(os.name, '-') as orderStatusName,
                COALESCE(c.name, '-') as customerName,
                COALESCE(comp.name, fc.name, '-') as companyName,
                COALESCE(fc.type, '-') as fleetCategory,
                COALESCE(NULLIF(o.plateNumber, ''), f.plateNumber, '-') as plateNumber,
                COALESCE(NULLIF(o.driverName, ''), emp.name, '-') as driverName,
                COALESCE(r.name, '-') as routeName,
                COALESCE(orig.name, '') as originName,
                COALESCE(dest.name, '') as destinationName,
                {$omsetExpr} as omset,
                {$biayaExpr} as biaya,
                {$profitExpr} as profit,
                {$paymentStatusExpr} as paymentStatus
            ");

        // 1. Date range
        if ($request->filled('startDate')) {
            $start = Carbon::parse($request->startDate)->format('Y-m-d');
            $query->whereDate('o.orderDate', '>=', $start);
        }
        if ($request->filled('endDate')) {
            $end = Carbon::parse($request->endDate)->format('Y-m-d');
            $query->whereDate('o.orderDate', '<=', $end);
        }

        // 2. Customer (single or multi-select)
        $customerCodes = array_values(array_filter((array) $request->input('customerCodes', [])));
        if (! empty($customerCodes)) {
            $query->whereIn('o.customerCode', $customerCodes);
        }

        // 3. Company
        if ($request->filled('companyCode')) {
            $query->where('fc.companyCode', $request->companyCode);
        }

        // 4. Fleet Category (Internal vs External)
        if ($request->filled('fleetCategory')) {
            $query->where('fc.type', $request->fleetCategory);
        }

        // 5. Fleet
        if ($request->filled('fleetCode')) {
            $query->where('o.fleetCode', $request->fleetCode);
        }

        // 6. Driver
        if ($request->filled('driverCode')) {
            $query->where('o.driverCode', $request->driverCode);
        }

        // 7. Route Origin & Destination
        if ($request->filled('originCode')) {
            $query->where('r.originLocationCode', $request->originCode);
        }
        if ($request->filled('destinationCode')) {
            $query->where('r.destinationLocationCode', $request->destinationCode);
        }

        // 8. Order Status
        if ($request->filled('orderStatus') && $request->orderStatus !== '') {
            $query->where('o.status', $request->orderStatus);
        }

        // 9. Payment Status
        if ($request->filled('paymentStatus')) {
            $ps = $request->paymentStatus;
            if ($ps === 'paid') {
                $query->where(function ($q) {
                    $q->where('inv.inv_status', 3)->orWhere('op.op_status', 1);
                });
            } elseif ($ps === 'partial') {
                $query->where('inv.inv_status', 2)
                    ->where(function ($q) {
                        $q->whereNull('op.op_status')->orWhere('op.op_status', '!=', 1);
                    });
            } elseif ($ps === 'unpaid') {
                $query->where(function ($q) {
                    $q->whereNull('inv.inv_status')->orWhere('inv.inv_status', 1);
                })->where(function ($q) {
                    $q->whereNull('op.op_status')->orWhere('op.op_status', '!=', 1);
                });
            }
        }

        // 10. Shipment / Order number search
        if ($request->filled('shipmentNumber')) {
            $kw = trim((string) $request->shipmentNumber);
            $query->where(function ($q) use ($kw) {
                $q->where('o.shipmentNumber', 'like', "%{$kw}%")
                    ->orWhere('o.code', 'like', "%{$kw}%");
            });
        }

        // 11. DataTables global search box
        $globalSearch = trim((string) data_get($request->input('search'), 'value', ''));
        if ($globalSearch !== '') {
            $query->where(function ($q) use ($globalSearch) {
                $q->where('o.code', 'like', "%{$globalSearch}%")
                    ->orWhere('o.shipmentNumber', 'like', "%{$globalSearch}%")
                    ->orWhere('c.name', 'like', "%{$globalSearch}%")
                    ->orWhere('o.plateNumber', 'like', "%{$globalSearch}%")
                    ->orWhere('f.plateNumber', 'like', "%{$globalSearch}%")
                    ->orWhere('o.driverName', 'like', "%{$globalSearch}%")
                    ->orWhere('emp.name', 'like', "%{$globalSearch}%")
                    ->orWhere('r.name', 'like', "%{$globalSearch}%");
            });
        }

        return $query;
    }

    /**
     * Compute grand totals across ALL filtered rows (not just current page).
     */
    public function computeTotals(Request $request): array
    {
        $sub = $this->buildQuery($request);

        $agg = DB::query()
            ->fromSub($sub, 'omset_rows')
            ->selectRaw('
                COUNT(*) as total_orders,
                COALESCE(SUM(omset), 0) as total_omset,
                COALESCE(SUM(biaya), 0) as total_biaya,
                COALESCE(SUM(profit), 0) as total_profit
            ')
            ->first();

        $totalOrders = (int) ($agg->total_orders ?? 0);
        $totalOmset = (float) ($agg->total_omset ?? 0);
        $totalBiaya = (float) ($agg->total_biaya ?? 0);
        $totalProfit = (float) ($agg->total_profit ?? 0);

        return [
            'totalOrders' => $totalOrders,
            'totalOmset' => $totalOmset,
            'totalBiaya' => $totalBiaya,
            'totalProfit' => $totalProfit,
            'totalOmsetFormatted' => 'Rp ' . number_format($totalOmset, 0, ',', '.'),
            'totalBiayaFormatted' => 'Rp ' . number_format($totalBiaya, 0, ',', '.'),
            'totalProfitFormatted' => ($totalProfit < 0 ? '-Rp ' : 'Rp ') . number_format(abs($totalProfit), 0, ',', '.'),
        ];
    }
}
