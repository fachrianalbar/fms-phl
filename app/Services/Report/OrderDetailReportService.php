<?php

namespace App\Services\Report;

use App\Helpers\FilterHelper;
use App\Models\Operational\Order;
use App\Services\Finance\VendorPaymentService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class OrderDetailReportService
{
    public function __construct(
        protected VendorPaymentService $vendorPaymentService,
        protected ProfitLossService $profitLossService
    ) {}

    /**
     * Build the filtered Order query for Order Detail Report (Web DataTable, Excel, and PDF).
     */
    public function buildFilteredQuery(Request $request): Builder
    {
        $query = Order::whereIn('status', [4, 5])->with([
            'fleet.type',
            'fleet.company',
            'customer.company',
            'driver',
            'route.destinationLocation',
            'route.originLocation',
            'material',
            'unit',
            'cost.costComponent',
            'invoiceDetail.invoice.details.order.cost',
            'orderPayment',
        ])->orderBy('orderDate', 'desc');

        if ($request->filled('plateNumber')) {
            $query->wherePlateNumberLike($request->plateNumber);
        }

        if ($request->filled('driverName')) {
            $query->whereDriverNameLike($request->driverName);
        }

        $filters = [
            'customer_name' => $request->customerName,
            'fleetType_name' => $request->fleetTypeName,
            'shipmentNumber' => $request->shipmentNumber,
            'origin' => $request->origin,
            'destination' => $request->destination,
            'orderTypeCode' => $request->orderTypeCode,
        ];

        $relations = [
            'customer_name' => 'customer.name',
            'fleetType_name' => 'fleet.type.name',
            'origin' => 'route.originLocation.name',
            'destination' => 'route.destinationLocation.name',
        ];

        $dateFilters = [
            'orderDate' => [
                'start' => $request->startDate,
                'end' => $request->endDate,
            ],
        ];

        return FilterHelper::applyFilters($query, $filters, $relations, $dateFilters);
    }

    /**
     * Determine whether an order uses an Internal fleet whose parent company differs from the customer's company.
     */
    public function isCrossInternalCompany(Order $order): bool
    {
        $fleetCompany = $order->fleet?->company;
        if (! $fleetCompany) {
            return false;
        }

        $type = strtolower(trim((string) ($fleetCompany->type ?? '')));
        if ($type !== 'internal') {
            return false;
        }

        $fleetParentCode = trim((string) ($fleetCompany->companyCode ?? ($fleetCompany->company?->code ?? '')));
        $customerCompanyCode = trim((string) ($order->customer?->companyCode ?? ($order->customer?->company?->code ?? '')));

        return $fleetParentCode !== '' && $customerCompanyCode !== '' && $fleetParentCode !== $customerCompanyCode;
    }

    /**
     * Resolve qty, unit price, and total omset for an order.
     * Cross-company internal orders follow vendor line amounts; others use customer route pricing.
     *
     * @return array{qty: float, unit_price: float, omset: float, is_cross_internal: bool}
     */
    public function resolveOmsetAmounts(Order $order): array
    {
        $qty = (float) ($order->qty ?? 0);
        $isCrossInternal = $this->isCrossInternalCompany($order);

        if ($isCrossInternal) {
            $vendorLine = $this->vendorPaymentService->resolveOrderVendorLineAmounts($order);

            return [
                'qty' => $qty,
                'unit_price' => (float) ($vendorLine['unit_price'] ?? 0),
                'omset' => (float) ($vendorLine['subtotal'] ?? 0),
                'is_cross_internal' => true,
            ];
        }

        $omset = (float) ($order->routeAmount ?? 0);
        $unitPrice = (float) ($order->price ?? 0);

        if ($unitPrice <= 0) {
            if ($qty > 0 && $omset > 0) {
                $unitPrice = $omset / $qty;
            } else {
                $unitPrice = (float) ($order->route?->price ?? 0);
            }
        }

        return [
            'qty' => $qty,
            'unit_price' => $unitPrice,
            'omset' => $omset,
            'is_cross_internal' => false,
        ];
    }

    /**
     * Separate order costs into On Charge income and non-On Charge categories (Commission, Salary, Other).
     *
     * @return array{
     *     income_details: array<int, array{name: string, nominal: float}>,
     *     income_total: float,
     *     commission_details: array<int, array{name: string, nominal: float}>,
     *     commission_total: float,
     *     salary_details: array<int, array{name: string, nominal: float}>,
     *     salary_total: float,
     *     other_cost_details: array<int, array{name: string, nominal: float}>,
     *     other_cost_total: float,
     *     total_cost: float
     * }
     */
    public function classifyOrderCosts(Order $order): array
    {
        $incomeDetails = [];
        $incomeTotal = 0.0;
        $commissionDetails = [];
        $commissionTotal = 0.0;
        $salaryDetails = [];
        $salaryTotal = 0.0;
        $otherCostDetails = [];
        $otherCostTotal = 0.0;

        foreach ($order->cost ?? [] as $cost) {
            $name = (string) ($cost->componentName ?: ($cost->costComponent?->name ?? 'N/A'));
            $nominal = (float) ($cost->nominal ?? 0);
            $costType = strtolower(trim((string) ($cost->type ?? '')));

            if ($costType === 'on charge') {
                $incomeDetails[] = ['name' => $name, 'nominal' => $nominal];
                $incomeTotal += $nominal;

                continue;
            }

            $masterType = strtolower(trim((string) ($cost->costComponent?->type ?? '')));
            $lowerName = mb_strtolower($name);
            $isCommission = $masterType === 'commission' || str_contains($lowerName, 'komisi');
            $isSalary = ! $isCommission && ($masterType === 'salary' || str_contains($lowerName, 'gaji'));

            $entry = ['name' => $name, 'nominal' => $nominal];

            if ($isCommission) {
                $commissionDetails[] = $entry;
                $commissionTotal += $nominal;
            } elseif ($isSalary) {
                $salaryDetails[] = $entry;
                $salaryTotal += $nominal;
            } else {
                $otherCostDetails[] = $entry;
                $otherCostTotal += $nominal;
            }
        }

        $totalCost = $otherCostTotal + $commissionTotal + $salaryTotal;

        return [
            'income_details' => $incomeDetails,
            'income_total' => $incomeTotal,
            'commission_details' => $commissionDetails,
            'commission_total' => $commissionTotal,
            'salary_details' => $salaryDetails,
            'salary_total' => $salaryTotal,
            'other_cost_details' => $otherCostDetails,
            'other_cost_total' => $otherCostTotal,
            'total_cost' => $totalCost,
        ];
    }

    /**
     * Calculate full revenue, cost breakdown, gross profit, PPh tax, and net profit for one order.
     */
    public function calculateOrderBreakdown(Order $order, array &$invoiceWeightCache = []): array
    {
        $omset = $this->resolveOmsetAmounts($order);
        $costs = $this->classifyOrderCosts($order);

        $grossProfit = $omset['omset'] + $costs['income_total'] - $costs['total_cost'];
        $tax = $this->profitLossService->resolveOrderTax($order, $invoiceWeightCache);
        $netProfit = $grossProfit - $tax;

        return array_merge($omset, $costs, [
            'gross_profit' => $grossProfit,
            'tax' => $tax,
            'net_profit' => $netProfit,
        ]);
    }

    /**
     * Transform Order models into lightweight serializable row arrays for Excel/PDF rendering and chunk caching.
     *
     * @param  iterable<int, Order>  $orders
     * @return array<int, array<string, mixed>>
     */
    public function prepareExportRows(iterable $orders): array
    {
        $rows = [];
        $invoiceWeightCache = [];

        foreach ($orders as $order) {
            $breakdown = $this->calculateOrderBreakdown($order, $invoiceWeightCache);

            $rows[] = array_merge([
                'shipment_number' => mb_strtoupper((string) ($order->shipmentNumber ?? '')),
                'order_date' => $order->orderDate ? Carbon::parse($order->orderDate)->format('d-m-Y') : '',
                'customer_name' => (string) ($order->customer?->name ?? ''),
                'origin_name' => (string) ($order->route?->originLocation?->name ?? ''),
                'destination_name' => (string) ($order->route?->destinationLocation?->name ?? ''),
                'plate_number' => strtoupper((string) ($order->plateNumber ?: ($order->fleet?->plateNumber ?? ''))),
                'driver_name' => (string) ($order->driverName ?: ($order->driver?->name ?? '')),
            ], $breakdown);
        }

        return $rows;
    }

    /**
     * Aggregate grand totals across prepared export rows.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<string, float>
     */
    public function computeGrandTotals(array $rows): array
    {
        $totals = [
            'qty' => 0.0,
            'omset' => 0.0,
            'income_total' => 0.0,
            'commission_total' => 0.0,
            'salary_total' => 0.0,
            'other_cost_total' => 0.0,
            'total_cost' => 0.0,
            'gross_profit' => 0.0,
            'tax' => 0.0,
            'net_profit' => 0.0,
        ];

        foreach ($rows as $row) {
            foreach (array_keys($totals) as $key) {
                $totals[$key] += (float) ($row[$key] ?? 0);
            }
        }

        return $totals;
    }
}
