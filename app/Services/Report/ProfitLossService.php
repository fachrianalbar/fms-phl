<?php

namespace App\Services\Report;

use App\Models\Data\TonaseBonus;
use App\Models\Master\Fleet;
use App\Models\Operational\Order;
use App\Models\Warehouse\Maintenance;
use App\Traits\LogActivity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProfitLossService
{
    use LogActivity;

    protected $order;

    protected $maintenance;

    protected ?Collection $tonaseBonuses = null;

    public function __construct(Order $order, Maintenance $maintenance)
    {
        $this->order = $order;
        $this->maintenance = $maintenance;
    }

    /**
     * Standard eager-loading relations for profit & loss fleet queries.
     */
    public function fleetReportRelations(?string $startDate = null, ?string $endDate = null): array
    {
        $start = $this->normalizeDate($startDate);
        $end = $this->normalizeDate($endDate);

        return [
            'company',
            'orders' => function ($query) use ($start, $end) {
                if ($start) {
                    $query->where('orderDate', '>=', $start);
                }
                if ($end) {
                    $query->where('orderDate', '<=', $end);
                }
            },
            'maintenances' => function ($query) use ($start, $end) {
                if ($start) {
                    $query->where('date', '>=', $start);
                }
                if ($end) {
                    $query->where('date', '<=', $end);
                }
            },
            'orders.route.routeDetail.costComponent',
            'orders.cost',
            'orders.invoiceDetail.invoice.details.order.cost',
            'orders.orderPayment',
            'maintenances.details.item',
        ];
    }

    /**
     * Resolve basic sales for an order using vendorPrice or personalVendorPrice fallback.
     */
    public function resolveOrderVendorSales(Order $order): float
    {
        $vendorPrice = (float) ($order->vendorPrice ?? 0);
        $personalVendorPrice = (float) ($order->personalVendorPrice ?? 0);

        return $vendorPrice > 0 ? $vendorPrice : $personalVendorPrice;
    }

    /**
     * Resolve PPh tax for an order from its invoice (prorated for multi-order invoices) or direct payment.
     */
    public function resolveOrderTax(Order $order, array &$invoiceWeightCache = []): float
    {
        $invoice = $order->invoiceDetail?->invoice;
        if ($invoice) {
            $invoicePph = (float) ($invoice->pphAmount ?? 0);
            if ($invoicePph <= 0) {
                return 0.0;
            }

            $details = $invoice->details ?? collect();
            if ($details->count() <= 1) {
                return $invoicePph;
            }

            $baseType = $invoice->pphBaseType === 'route' ? 'route' : 'subtotal';
            $orderWeight = $this->resolveOrderInvoiceWeight($order, $baseType);

            $cacheKey = spl_object_id($invoice).'|'.$baseType.'|'.$details->count();
            if (! array_key_exists($cacheKey, $invoiceWeightCache)) {
                $totalWeight = 0.0;
                foreach ($details as $detail) {
                    if ($detail->order) {
                        $totalWeight += $this->resolveOrderInvoiceWeight($detail->order, $baseType);
                    }
                }
                $invoiceWeightCache[$cacheKey] = $totalWeight;
            }

            $totalWeight = $invoiceWeightCache[$cacheKey];

            return $totalWeight > 0 ? round($invoicePph * ($orderWeight / $totalWeight), 2) : 0.0;
        }

        if ($order->orderPayment) {
            return (float) ($order->orderPayment->pph ?? 0);
        }

        return 0.0;
    }

    /**
     * Compute the proration weight of an order within an invoice based on pphBaseType.
     */
    protected function resolveOrderInvoiceWeight(Order $order, string $baseType): float
    {
        $weight = (float) ($order->routeAmount ?? 0);

        if ($baseType === 'subtotal') {
            foreach ($order->cost ?? [] as $cost) {
                if (strtolower(trim((string) ($cost->type ?? ''))) === 'on charge') {
                    $weight += (float) ($cost->nominal ?? 0);
                }
            }
        }

        return $weight;
    }

    /**
     * Calculate basic allowance (Allowance & Allowance Office) for an order safely.
     */
    public function calculateOrderAllowance(Order $order): float
    {
        $route = $order->route;
        if (! $route) {
            return 0.0;
        }

        $allowance = 0.0;
        $routePrice = (float) ($route->price ?? 0);

        foreach ($route->routeDetail ?? [] as $itemRoute) {
            $componentType = $itemRoute->costComponent?->type;
            if (! in_array($componentType, ['Allowance', 'Allowance Office'], true)) {
                continue;
            }

            $amount = (float) ($itemRoute->amount ?? 0);
            if ($amount != 0.0) {
                $allowance += $amount;
            }

            $percentage = (float) ($itemRoute->percentage ?? 0);
            if ($percentage != 0.0) {
                $allowance += $routePrice * ($percentage / 100);
            }
        }

        return $allowance;
    }

    /**
     * Calculate full profit & loss summary metrics for a fleet.
     */
    public function calculateFleetSummary(Fleet $fleet, ?Collection $tonaseBonuses = null): array
    {
        $tonaseBonuses ??= ($this->tonaseBonuses ??= TonaseBonus::all());

        $basicSales = 0.0;
        $basicAllowance = 0.0;
        $additionalCost = 0.0;
        $tonase = 0.0;
        $tax = 0.0;
        $invoiceWeightCache = [];

        foreach ($fleet->orders ?? [] as $order) {
            $basicSales += $this->resolveOrderVendorSales($order);
            $basicAllowance += $this->calculateOrderAllowance($order);

            foreach ($order->cost ?? [] as $cost) {
                $additionalCost += (float) ($cost->nominal ?? 0);
            }

            $orderQty = (float) ($order->qty ?? 0);
            $bonus = $tonaseBonuses->first(
                fn ($item) => (float) $item->min <= $orderQty && (float) $item->max >= $orderQty
            );
            if ($bonus) {
                $tonase += (float) ($bonus->value ?? 0);
            }

            $tax += $this->resolveOrderTax($order, $invoiceWeightCache);
        }

        $maintenance = 0.0;
        foreach ($fleet->maintenances ?? [] as $item) {
            foreach ($item->details ?? [] as $details) {
                $maintenance += (float) ($details->qty ?? 0) * (float) ($details->item?->price ?? 0);
            }
        }

        $orderCost = $basicAllowance + $additionalCost + $tonase;
        $netProfit = $basicSales - $orderCost - $maintenance - $tax;

        return [
            'basicSales' => $basicSales,
            'basicAllowance' => $basicAllowance,
            'additionalCost' => $additionalCost,
            'tonase' => $tonase,
            'orderCost' => $orderCost,
            'maintenance' => $maintenance,
            'tax' => $tax,
            'netProfit' => $netProfit,
        ];
    }

    /**
     * Compute per-fleet summaries and aggregate KPI totals via lightweight SQL queries (no model hydration).
     *
     * @return array{perFleet: array<string, array<string, float>>, totals: array<string, float>}
     */
    public function calculatePerFleetSummaries(Builder $fleetQuery, ?string $startDate = null, ?string $endDate = null): array
    {
        $fleetCodeCounts = (clone $fleetQuery)
            ->pluck('fleet.code')
            ->filter()
            ->countBy();

        $emptyTotals = [
            'basicSales' => 0.0,
            'orderCost' => 0.0,
            'maintenance' => 0.0,
            'tax' => 0.0,
            'netProfit' => 0.0,
        ];

        if ($fleetCodeCounts->isEmpty()) {
            return [
                'perFleet' => [],
                'totals' => $emptyTotals,
            ];
        }

        $fleetCodes = $fleetCodeCounts->keys()->values();
        $start = $this->normalizeDate($startDate);
        $end = $this->normalizeDate($endDate);

        $orderBaseQuery = function () use ($fleetCodes, $start, $end) {
            return DB::table('order as o')
                ->whereNull('o.deleted_at')
                ->whereIn('o.fleetCode', $fleetCodes)
                ->when($start, fn ($q) => $q->where('o.orderDate', '>=', $start))
                ->when($end, fn ($q) => $q->where('o.orderDate', '<=', $end));
        };

        $basicSalesByFleet = $orderBaseQuery()
            ->groupBy('o.fleetCode')
            ->select(
                'o.fleetCode',
                DB::raw('SUM(CASE WHEN COALESCE(o.vendorPrice, 0) > 0 THEN o.vendorPrice ELSE COALESCE(o.personalVendorPrice, 0) END) as total')
            )
            ->pluck('total', 'fleetCode');

        $additionalCostByFleet = $orderBaseQuery()
            ->join('order_cost', 'order_cost.orderCode', '=', 'o.code')
            ->groupBy('o.fleetCode')
            ->select('o.fleetCode', DB::raw('SUM(COALESCE(order_cost.nominal, 0)) as total'))
            ->pluck('total', 'fleetCode');

        $basicAllowanceByFleet = $orderBaseQuery()
            ->join('route', function ($join) {
                $join->on('route.code', '=', 'o.routeCode')
                    ->whereNull('route.deleted_at');
            })
            ->join('route_detail', function ($join) {
                $join->on('route_detail.routeCode', '=', 'route.code')
                    ->whereNull('route_detail.deleted_at');
            })
            ->join('cost_component', function ($join) {
                $join->on('cost_component.code', '=', 'route_detail.componentCode')
                    ->whereNull('cost_component.deleted_at');
            })
            ->whereIn('cost_component.type', ['Allowance', 'Allowance Office'])
            ->groupBy('o.fleetCode')
            ->select(
                'o.fleetCode',
                DB::raw('SUM(COALESCE(route_detail.amount, 0) + (COALESCE(route.price, 0) * (COALESCE(route_detail.percentage, 0) / 100))) as total')
            )
            ->pluck('total', 'fleetCode');

        $tonaseBonuses = $this->tonaseBonuses ??= TonaseBonus::all();
        $tonaseByFleet = [];
        if ($tonaseBonuses->isNotEmpty()) {
            foreach ($orderBaseQuery()->select('o.fleetCode', 'o.qty')->get() as $orderRow) {
                $orderQty = (float) ($orderRow->qty ?? 0);
                $bonus = $tonaseBonuses->first(
                    fn ($item) => (float) $item->min <= $orderQty && (float) $item->max >= $orderQty
                );
                if ($bonus) {
                    $tonaseByFleet[$orderRow->fleetCode] = ($tonaseByFleet[$orderRow->fleetCode] ?? 0.0) + (float) ($bonus->value ?? 0);
                }
            }
        }

        $maintenanceByFleet = DB::table('maintenance as m')
            ->join('maintenance_detail as md', function ($join) {
                $join->on('md.maintenanceCode', '=', 'm.code')
                    ->whereNull('md.deleted_at');
            })
            ->leftJoin('item as i', 'i.code', '=', 'md.itemCode')
            ->whereNull('m.deleted_at')
            ->whereIn('m.fleetCode', $fleetCodes)
            ->when($start, fn ($q) => $q->where('m.date', '>=', $start))
            ->when($end, fn ($q) => $q->where('m.date', '<=', $end))
            ->groupBy('m.fleetCode')
            ->select('m.fleetCode', DB::raw('SUM(COALESCE(md.qty, 0) * COALESCE(i.price, 0)) as total'))
            ->pluck('total', 'fleetCode');

        $taxByFleet = $this->calculateFilteredOrderTaxByFleet($orderBaseQuery);

        $perFleet = [];
        $totals = $emptyTotals;

        foreach ($fleetCodeCounts as $fleetCode => $count) {
            $basicSales = (float) ($basicSalesByFleet->get($fleetCode, 0.0));
            $basicAllowance = (float) ($basicAllowanceByFleet->get($fleetCode, 0.0));
            $additionalCost = (float) ($additionalCostByFleet->get($fleetCode, 0.0));
            $tonase = (float) ($tonaseByFleet[$fleetCode] ?? 0.0);
            $orderCost = $basicAllowance + $additionalCost + $tonase;
            $maintenance = (float) ($maintenanceByFleet->get($fleetCode, 0.0));
            $tax = (float) ($taxByFleet[$fleetCode] ?? 0.0);
            $netProfit = $basicSales - $orderCost - $maintenance - $tax;

            $perFleet[$fleetCode] = [
                'basicSales' => $basicSales,
                'basicAllowance' => $basicAllowance,
                'additionalCost' => $additionalCost,
                'tonase' => $tonase,
                'orderCost' => $orderCost,
                'maintenance' => $maintenance,
                'tax' => $tax,
                'netProfit' => $netProfit,
            ];

            $totals['basicSales'] += $basicSales * $count;
            $totals['orderCost'] += $orderCost * $count;
            $totals['maintenance'] += $maintenance * $count;
            $totals['tax'] += $tax * $count;
            $totals['netProfit'] += $netProfit * $count;
        }

        return [
            'perFleet' => $perFleet,
            'totals' => $totals,
        ];
    }

    /**
     * Compute aggregate KPI totals for all filtered fleets via lightweight SQL queries.
     */
    public function calculateQuerySummaryTotals(Builder $fleetQuery, ?string $startDate = null, ?string $endDate = null): array
    {
        return $this->calculatePerFleetSummaries($fleetQuery, $startDate, $endDate)['totals'];
    }

    /**
     * Compute PPh tax per fleet for orders matching the filtered query without hydrating models.
     *
     * @return array<string, float>
     */
    protected function calculateFilteredOrderTaxByFleet(callable $orderBaseQuery): array
    {
        $taxByFleet = [];

        $directPaymentPerFleet = $orderBaseQuery()
            ->join('order_payment as op', 'op.orderCode', '=', 'o.code')
            ->leftJoin('invoice_detail as idet', function ($join) {
                $join->on('idet.orderCode', '=', 'o.code')
                    ->whereNull('idet.deleted_at');
            })
            ->leftJoin('invoice as inv', function ($join) {
                $join->on('inv.code', '=', 'idet.invoiceCode')
                    ->whereNull('inv.deleted_at');
            })
            ->whereNull('inv.id')
            ->groupBy('o.fleetCode')
            ->select('o.fleetCode', DB::raw('SUM(COALESCE(op.pph, 0)) as total'))
            ->pluck('total', 'fleetCode');

        foreach ($directPaymentPerFleet as $fleetCode => $amount) {
            $taxByFleet[$fleetCode] = ($taxByFleet[$fleetCode] ?? 0.0) + (float) $amount;
        }

        $invoicedOrders = $orderBaseQuery()
            ->join('invoice_detail as idet', function ($join) {
                $join->on('idet.orderCode', '=', 'o.code')
                    ->whereNull('idet.deleted_at');
            })
            ->join('invoice as inv', function ($join) {
                $join->on('inv.code', '=', 'idet.invoiceCode')
                    ->whereNull('inv.deleted_at');
            })
            ->where('inv.pphAmount', '>', 0)
            ->select([
                'o.code as orderCode',
                'o.fleetCode as fleetCode',
                'o.routeAmount',
                'inv.code as invoiceCode',
                'inv.pphAmount',
                'inv.pphBaseType',
            ])
            ->get();

        if ($invoicedOrders->isEmpty()) {
            return $taxByFleet;
        }

        $invoiceCodes = $invoicedOrders->pluck('invoiceCode')->unique()->values();
        $allInvoiceDetails = DB::table('invoice_detail as idet')
            ->leftJoin('order as o', function ($join) {
                $join->on('o.code', '=', 'idet.orderCode')
                    ->whereNull('o.deleted_at');
            })
            ->whereNull('idet.deleted_at')
            ->whereIn('idet.invoiceCode', $invoiceCodes)
            ->select([
                'idet.invoiceCode',
                'idet.orderCode',
                'o.id as orderId',
                'o.routeAmount',
            ])
            ->get();

        $detailsByInvoice = $allInvoiceDetails->groupBy('invoiceCode');

        $subtotalInvoiceCodes = $invoicedOrders
            ->filter(fn ($row) => $row->pphBaseType !== 'route')
            ->pluck('invoiceCode')
            ->unique();

        $onChargeByOrder = [];
        if ($subtotalInvoiceCodes->isNotEmpty()) {
            $involvedOrderCodes = $allInvoiceDetails
                ->whereIn('invoiceCode', $subtotalInvoiceCodes)
                ->pluck('orderCode')
                ->filter()
                ->unique()
                ->values();

            if ($involvedOrderCodes->isNotEmpty()) {
                $onChargeByOrder = DB::table('order_cost')
                    ->whereIn('orderCode', $involvedOrderCodes)
                    ->whereRaw("LOWER(TRIM(COALESCE(type, ''))) = ?", ['on charge'])
                    ->groupBy('orderCode')
                    ->select('orderCode', DB::raw('SUM(COALESCE(nominal, 0)) as total_nominal'))
                    ->pluck('total_nominal', 'orderCode')
                    ->map(fn ($value) => (float) $value)
                    ->all();
            }
        }

        $invoiceTotalWeights = [];
        $seenOrders = [];

        foreach ($invoicedOrders as $row) {
            if (isset($seenOrders[$row->orderCode])) {
                continue;
            }
            $seenOrders[$row->orderCode] = true;

            $invoicePph = (float) ($row->pphAmount ?? 0);
            $details = $detailsByInvoice->get($row->invoiceCode, collect());

            if ($details->count() <= 1) {
                $taxByFleet[$row->fleetCode] = ($taxByFleet[$row->fleetCode] ?? 0.0) + $invoicePph;
                continue;
            }

            $baseType = $row->pphBaseType === 'route' ? 'route' : 'subtotal';
            $cacheKey = $row->invoiceCode.'|'.$baseType;

            if (! array_key_exists($cacheKey, $invoiceTotalWeights)) {
                $totalWeight = 0.0;
                foreach ($details as $detail) {
                    if ($detail->orderId === null) {
                        continue;
                    }
                    $weight = (float) ($detail->routeAmount ?? 0);
                    if ($baseType === 'subtotal') {
                        $weight += $onChargeByOrder[$detail->orderCode] ?? 0.0;
                    }
                    $totalWeight += $weight;
                }
                $invoiceTotalWeights[$cacheKey] = $totalWeight;
            }

            $totalWeight = $invoiceTotalWeights[$cacheKey];
            $orderWeight = (float) ($row->routeAmount ?? 0);
            if ($baseType === 'subtotal') {
                $orderWeight += $onChargeByOrder[$row->orderCode] ?? 0.0;
            }

            if ($totalWeight > 0) {
                $taxByFleet[$row->fleetCode] = ($taxByFleet[$row->fleetCode] ?? 0.0)
                    + round($invoicePph * ($orderWeight / $totalWeight), 2);
            }
        }

        return $taxByFleet;
    }

    protected function normalizeDate(?string $date): ?string
    {
        if (! $date) {
            return null;
        }

        return Carbon::parse($date)->toDateString();
    }

    public function datatableOrder($fleetCode)
    {
        return $this->order->where('fleetCode', $fleetCode)->with([
            'driver',
            // 'fleetDriver.employee',
            'customer',
            'route.destinationLocation',
            'material',
            'route.routeDetail.costComponent',
            'cost',
            'fleet',
            'fleet.type',
        ])->latest();
    }

    public function datatableMaintenance($fleetCode)
    {
        return $this->maintenance->with([
            'fleet',
            'details',
            'details.item',
        ])->where('status', 0)->where('fleetCode', $fleetCode)->latest();
    }
}
