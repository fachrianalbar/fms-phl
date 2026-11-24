<?php

namespace App\Services;

use App\Exports\ReceivableAgingReport;
use App\Models\Master\Fleet;
use App\Models\Operational\Order;
use App\Services\Finance\DirectPaymentService;
use App\Services\Finance\VendorPaymentService;
use App\Services\Purchasing\PurchasePaymentService;
use App\Traits\LogActivity;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    use LogActivity;

    protected $fleet;

    protected $order;

    public function __construct(Fleet $fleet, Order $order)
    {
        $this->fleet = $fleet;
        $this->order = $order;
    }

    public function dashboardMaintenance()
    {
        return $this->fleet->select([
            'fleet.code',
            'fleet.plateNumber',
            DB::raw('COUNT(DISTINCT maintenance.code) as total'),
            DB::raw('SUM(item.price * maintenance_detail.qty) as price'),
        ])
            ->leftJoin('maintenance', 'maintenance.fleetCode', 'fleet.code')
            ->leftJoin('maintenance_detail', 'maintenance_detail.maintenanceCode', 'maintenance.code')
            ->leftJoin('item', 'item.code', 'maintenance_detail.itemCode')
            ->with(['maintenances'])
            ->orderBy('total', 'DESC')
            ->groupBy(['fleet.code', 'fleet.plateNumber']);
    }

    public function dashboardTruckOrder()
    {
        $latestOrders = DB::table('order as o1')
            ->select([
                'o1.fleetCode',
                'o1.shipmentNumber',
                'o1.status',
                'employee.name as driverName',
                'o1.created_at',
            ])
            ->join('employee', 'employee.code', '=', 'o1.driverCode')
            ->join(DB::raw('(
                SELECT fleetCode, MAX(created_at) as latest
                FROM `order`
                WHERE deleted_at IS NULL
                GROUP BY fleetCode
            ) as `o2`'), function ($join) {
                $join->on('o1.fleetCode', '=', DB::raw('o2.fleetCode'))
                    ->on('o1.created_at', '=', DB::raw('o2.latest'));
            })
            ->whereNull('o1.deleted_at');

        return $this->fleet->select([
            'fleet.code',
            'fleet.plateNumber',
            'fleet_type.name as fleetTypeName',
            'latest.shipmentNumber',
            'latest.driverName',
            'latest.status',
        ])
            ->leftJoin('fleet_type', 'fleet_type.code', 'fleet.fleetTypeCode')
            ->leftJoinSub($latestOrders, 'latest', function ($join) {
                $join->on('fleet.code', '=', 'latest.fleetCode');
            })
            ->groupBy([
                'fleet.code',
                'fleet.plateNumber',
                'fleet_type.name',
                'latest.shipmentNumber',
                'latest.driverName',
                'latest.status',
            ])
            ->get();
    }

    /**
     * Resolve filter preset atau rentang tanggal kustom menjadi periode terstandar.
     */
    public function resolvePeriod(?string $preset, ?string $startDate, ?string $endDate): array
    {
        $today = Carbon::today();
        $normalizedPreset = in_array($preset, ['this_month', 'last_month', 'this_quarter', 'this_year', 'custom'], true)
            ? $preset
            : 'this_month';

        if ($normalizedPreset === 'custom' && $startDate && $endDate) {
            try {
                $start = Carbon::parse($startDate)->startOfDay();
                $end = Carbon::parse($endDate)->startOfDay();
                if ($start->gt($end)) {
                    [$start, $end] = [$end, $start];
                }
                $label = 'Rentang Kustom';
            } catch (\Throwable $e) {
                $normalizedPreset = 'this_month';
                $start = $today->copy()->startOfMonth();
                $end = $today->copy()->endOfMonth();
                $label = 'Bulan Ini';
            }
        } else {
            switch ($normalizedPreset) {
                case 'last_month':
                    $start = $today->copy()->subMonthNoOverflow()->startOfMonth();
                    $end = $today->copy()->subMonthNoOverflow()->endOfMonth();
                    $label = 'Bulan Lalu';
                    break;
                case 'this_quarter':
                    $start = $today->copy()->firstOfQuarter();
                    $end = $today->copy()->lastOfQuarter();
                    $label = 'Kuartal Ini';
                    break;
                case 'this_year':
                    $start = $today->copy()->startOfYear();
                    $end = $today->copy()->endOfYear();
                    $label = 'Tahun Ini';
                    break;
                default:
                    $normalizedPreset = 'this_month';
                    $start = $today->copy()->startOfMonth();
                    $end = $today->copy()->endOfMonth();
                    $label = 'Bulan Ini';
                    break;
            }
        }

        return [
            'preset' => $normalizedPreset,
            'label' => $label,
            'startDate' => $start->format('Y-m-d'),
            'endDate' => $end->format('Y-m-d'),
            'displayRange' => $start->translatedFormat('d M Y').' - '.$end->translatedFormat('d M Y'),
        ];
    }

    /**
     * Ringkasan metrik periodik yang mengikuti filter tanggal (orderDate / maintenance.date).
     */
    public function getPeriodicSummary(string $startDate, string $endDate): array
    {
        $orderAgg = DB::table('order')
            ->leftJoin('fleet', function ($join) {
                $join->on('fleet.code', '=', 'order.fleetCode')
                    ->whereNull('fleet.deleted_at');
            })
            ->leftJoin('fleet_company', function ($join) {
                $join->on('fleet_company.code', '=', 'fleet.fleetCompanyCode')
                    ->whereNull('fleet_company.deleted_at');
            })
            ->whereNull('order.deleted_at')
            ->whereDate('order.orderDate', '>=', $startDate)
            ->whereDate('order.orderDate', '<=', $endDate)
            ->selectRaw('COUNT(*) as order_count')
            ->selectRaw('COALESCE(SUM(order.routeAmount), 0) as total_revenue')
            ->selectRaw("SUM(CASE WHEN LOWER(COALESCE(fleet_company.type, '')) = 'external' THEN 1 ELSE 0 END) as vendor_orders")
            ->selectRaw("SUM(CASE WHEN LOWER(COALESCE(fleet_company.type, '')) != 'external' THEN 1 ELSE 0 END) as internal_orders")
            ->first();

        $statusRows = DB::table('order_status')
            ->leftJoin('order', function ($join) use ($startDate, $endDate) {
                $join->on('order.status', '=', 'order_status.code')
                    ->whereNull('order.deleted_at')
                    ->whereDate('order.orderDate', '>=', $startDate)
                    ->whereDate('order.orderDate', '<=', $endDate);
            })
            ->select([
                'order_status.code',
                'order_status.nama',
                'order_status.name',
                DB::raw('COUNT(order.id) as total'),
            ])
            ->groupBy('order_status.code', 'order_status.nama', 'order_status.name')
            ->orderByRaw('CAST(order_status.code AS UNSIGNED) ASC')
            ->get()
            ->map(fn ($row) => [
                'code' => (int) $row->code,
                'label' => $row->nama ?: $row->name,
                'total' => (int) $row->total,
            ])
            ->values()
            ->all();

        $topCustomers = DB::table('order')
            ->join('customer', 'customer.code', '=', 'order.customerCode')
            ->whereNull('order.deleted_at')
            ->whereNull('customer.deleted_at')
            ->whereDate('order.orderDate', '>=', $startDate)
            ->whereDate('order.orderDate', '<=', $endDate)
            ->select([
                'customer.code',
                'customer.name',
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('COALESCE(SUM(order.routeAmount), 0) as total_revenue'),
                DB::raw('MAX(order.orderDate) as latest_order'),
            ])
            ->groupBy('customer.code', 'customer.name')
            ->orderByDesc('orders_count')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'code' => $row->code,
                'name' => $row->name,
                'orders_count' => (int) $row->orders_count,
                'total_revenue' => (float) $row->total_revenue,
                'latest_order' => $row->latest_order ? Carbon::parse($row->latest_order)->format('d/m/Y') : '-',
            ])
            ->values()
            ->all();

        $topFleets = DB::table('order')
            ->join('fleet', 'fleet.code', '=', 'order.fleetCode')
            ->leftJoin('fleet_type', 'fleet_type.code', '=', 'fleet.fleetTypeCode')
            ->leftJoin('fleet_company', 'fleet_company.code', '=', 'fleet.fleetCompanyCode')
            ->whereNull('order.deleted_at')
            ->whereNull('fleet.deleted_at')
            ->whereDate('order.orderDate', '>=', $startDate)
            ->whereDate('order.orderDate', '<=', $endDate)
            ->select([
                'fleet.code',
                'fleet.plateNumber as name',
                DB::raw('COALESCE(fleet_type.name, "-") as fleet_type'),
                DB::raw('COALESCE(fleet_company.type, "internal") as company_type'),
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('COALESCE(SUM(order.routeAmount), 0) as total_revenue'),
                DB::raw('MAX(order.orderDate) as latest_order'),
            ])
            ->groupBy('fleet.code', 'fleet.plateNumber', 'fleet_type.name', 'fleet_company.type')
            ->orderByDesc('orders_count')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'code' => $row->code,
                'name' => $row->name,
                'fleet_type' => $row->fleet_type,
                'company_type' => strtolower((string) $row->company_type) === 'external' ? 'Vendor' : 'Internal',
                'orders_count' => (int) $row->orders_count,
                'total_revenue' => (float) $row->total_revenue,
                'latest_order' => $row->latest_order ? Carbon::parse($row->latest_order)->format('d/m/Y') : '-',
            ])
            ->values()
            ->all();

        $maintSummary = DB::table('maintenance')
            ->leftJoin('maintenance_detail', function ($join) {
                $join->on('maintenance_detail.maintenanceCode', '=', 'maintenance.code')
                    ->whereNull('maintenance_detail.deleted_at');
            })
            ->where('maintenance.status', 0)
            ->whereNull('maintenance.deleted_at')
            ->whereDate('maintenance.date', '>=', $startDate)
            ->whereDate('maintenance.date', '<=', $endDate)
            ->selectRaw('COUNT(DISTINCT maintenance.code) as total_events')
            ->selectRaw('COALESCE(SUM(maintenance_detail.qty), 0) as total_qty')
            ->selectRaw('COALESCE(SUM(maintenance_detail.price * maintenance_detail.qty), 0) as total_cost')
            ->first();

        $topMaintenanceFleets = DB::table('maintenance')
            ->join('fleet', function ($join) {
                $join->on('fleet.code', '=', 'maintenance.fleetCode')
                    ->whereNull('fleet.deleted_at');
            })
            ->leftJoin('fleet_type', 'fleet_type.code', '=', 'fleet.fleetTypeCode')
            ->leftJoin('maintenance_detail', function ($join) {
                $join->on('maintenance_detail.maintenanceCode', '=', 'maintenance.code')
                    ->whereNull('maintenance_detail.deleted_at');
            })
            ->where('maintenance.status', 0)
            ->whereNull('maintenance.deleted_at')
            ->whereDate('maintenance.date', '>=', $startDate)
            ->whereDate('maintenance.date', '<=', $endDate)
            ->select([
                'fleet.code as fleetCode',
                'fleet.plateNumber',
                DB::raw('COALESCE(fleet_type.name, "-") as fleetTypeName'),
                DB::raw('COUNT(DISTINCT maintenance.code) as total_events'),
                DB::raw('COALESCE(SUM(maintenance_detail.qty), 0) as total_qty'),
                DB::raw('COALESCE(SUM(maintenance_detail.price * maintenance_detail.qty), 0) as total_cost'),
            ])
            ->groupBy('fleet.code', 'fleet.plateNumber', 'fleet_type.name')
            ->orderByDesc('total_cost')
            ->orderByDesc('total_events')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'fleetCode' => $row->fleetCode,
                'plateNumber' => $row->plateNumber,
                'fleetTypeName' => $row->fleetTypeName,
                'total_events' => (int) $row->total_events,
                'total_qty' => (float) $row->total_qty,
                'total_cost' => (float) $row->total_cost,
            ])
            ->values()
            ->all();

        $trend = $this->getTrendSeries($startDate, $endDate);

        return [
            'orders' => [
                'count' => (int) ($orderAgg->order_count ?? 0),
                'revenue' => (float) ($orderAgg->total_revenue ?? 0),
                'internalCount' => (int) ($orderAgg->internal_orders ?? 0),
                'vendorCount' => (int) ($orderAgg->vendor_orders ?? 0),
            ],
            'statusBreakdown' => $statusRows,
            'trend' => $trend,
            'topCustomers' => $topCustomers,
            'topFleets' => $topFleets,
            'maintenance' => [
                'events' => (int) ($maintSummary->total_events ?? 0),
                'qty' => (float) ($maintSummary->total_qty ?? 0),
                'cost' => (float) ($maintSummary->total_cost ?? 0),
                'topFleets' => $topMaintenanceFleets,
            ],
        ];
    }

    /**
     * Seri grafik tren order, omzet, dan biaya maintenance (harian jika <= 45 hari, bulanan jika lebih).
     */
    public function getTrendSeries(string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();
        $diffDays = $start->diffInDays($end);
        $isDaily = $diffDays <= 45;

        if ($isDaily) {
            $orderRows = DB::table('order')
                ->whereNull('deleted_at')
                ->whereDate('orderDate', '>=', $startDate)
                ->whereDate('orderDate', '<=', $endDate)
                ->selectRaw('DATE(orderDate) as bucket, COUNT(*) as order_count, COALESCE(SUM(routeAmount), 0) as revenue')
                ->groupBy('bucket')
                ->get()
                ->keyBy('bucket');

            $maintRows = DB::table('maintenance')
                ->leftJoin('maintenance_detail', function ($join) {
                    $join->on('maintenance_detail.maintenanceCode', '=', 'maintenance.code')
                        ->whereNull('maintenance_detail.deleted_at');
                })
                ->where('maintenance.status', 0)
                ->whereNull('maintenance.deleted_at')
                ->whereDate('maintenance.date', '>=', $startDate)
                ->whereDate('maintenance.date', '<=', $endDate)
                ->selectRaw('DATE(maintenance.date) as bucket, COALESCE(SUM(maintenance_detail.price * maintenance_detail.qty), 0) as cost')
                ->groupBy('bucket')
                ->get()
                ->keyBy('bucket');

            $period = CarbonPeriod::create($start, '1 day', $end);
            $labels = [];
            $orderCounts = [];
            $revenues = [];
            $maintenanceCosts = [];

            foreach ($period as $day) {
                $key = $day->format('Y-m-d');
                $labels[] = $day->translatedFormat('d M');
                $orderCounts[] = (int) ($orderRows[$key]->order_count ?? 0);
                $revenues[] = round((float) ($orderRows[$key]->revenue ?? 0), 0);
                $maintenanceCosts[] = round((float) ($maintRows[$key]->cost ?? 0), 0);
            }

            return [
                'granularity' => 'daily',
                'labels' => $labels,
                'orderCounts' => $orderCounts,
                'revenues' => $revenues,
                'maintenanceCosts' => $maintenanceCosts,
            ];
        }

        $orderRows = DB::table('order')
            ->whereNull('deleted_at')
            ->whereDate('orderDate', '>=', $startDate)
            ->whereDate('orderDate', '<=', $endDate)
            ->selectRaw("DATE_FORMAT(orderDate, '%Y-%m') as bucket, COUNT(*) as order_count, COALESCE(SUM(routeAmount), 0) as revenue")
            ->groupBy('bucket')
            ->get()
            ->keyBy('bucket');

        $maintRows = DB::table('maintenance')
            ->leftJoin('maintenance_detail', function ($join) {
                $join->on('maintenance_detail.maintenanceCode', '=', 'maintenance.code')
                    ->whereNull('maintenance_detail.deleted_at');
            })
            ->where('maintenance.status', 0)
            ->whereNull('maintenance.deleted_at')
            ->whereDate('maintenance.date', '>=', $startDate)
            ->whereDate('maintenance.date', '<=', $endDate)
            ->selectRaw("DATE_FORMAT(maintenance.date, '%Y-%m') as bucket, COALESCE(SUM(maintenance_detail.price * maintenance_detail.qty), 0) as cost")
            ->groupBy('bucket')
            ->get()
            ->keyBy('bucket');

        $monthStart = $start->copy()->startOfMonth();
        $monthEnd = $end->copy()->startOfMonth();
        $period = CarbonPeriod::create($monthStart, '1 month', $monthEnd);

        $labels = [];
        $orderCounts = [];
        $revenues = [];
        $maintenanceCosts = [];

        foreach ($period as $month) {
            $key = $month->format('Y-m');
            $labels[] = $month->translatedFormat('M Y');
            $orderCounts[] = (int) ($orderRows[$key]->order_count ?? 0);
            $revenues[] = round((float) ($orderRows[$key]->revenue ?? 0), 0);
            $maintenanceCosts[] = round((float) ($maintRows[$key]->cost ?? 0), 0);
        }

        return [
            'granularity' => 'monthly',
            'labels' => $labels,
            'orderCounts' => $orderCounts,
            'revenues' => $revenues,
            'maintenanceCosts' => $maintenanceCosts,
        ];
    }

    /**
     * Snapshot live terkini lintas modul (AR, AP, Bank, DO berjalan, dan stok kritis).
     */
    public function getLiveSnapshot(): array
    {
        // 1. Piutang Customer (AR) dari ReceivableAgingReport
        $arCustomerRows = ReceivableAgingReport::customerRows(null, null, null, ReceivableAgingReport::STATUS_OUTSTANDING);
        $arInvoices = ReceivableAgingReport::invoiceRows(null, null, null, ReceivableAgingReport::STATUS_OUTSTANDING)
            ->sortBy('overdueDate')
            ->values();

        $urgentInvoices = $arInvoices->take(12)->map(function ($inv) {
            $daysToDue = (int) $inv->daysToDue;
            if ($daysToDue < 0) {
                $badgeClass = 'bg-danger-subtle text-danger-emphasis';
                $statusLabel = 'Terlambat '.abs($daysToDue).' hari';
            } elseif ($daysToDue === 0) {
                $badgeClass = 'bg-danger-subtle text-danger-emphasis';
                $statusLabel = 'Jatuh tempo hari ini';
            } elseif ($daysToDue <= 7) {
                $badgeClass = 'bg-warning-subtle text-warning-emphasis';
                $statusLabel = $daysToDue.' hari lagi';
            } else {
                $badgeClass = 'bg-success-subtle text-success-emphasis';
                $statusLabel = $daysToDue.' hari lagi';
            }

            return [
                'id' => $inv->id,
                'invoiceNumber' => $inv->invoiceNumber ?: $inv->code,
                'customerCode' => $inv->customerCode,
                'customerName' => $inv->customerName,
                'invoiceDate' => $inv->invoiceDate ? Carbon::parse($inv->invoiceDate)->format('d/m/Y') : '-',
                'dueDate' => $inv->dueDate->format('d/m/Y'),
                'remainingAmount' => (float) $inv->remainingAmount,
                'daysToDue' => $daysToDue,
                'badgeClass' => $badgeClass,
                'statusLabel' => $statusLabel,
            ];
        })->all();

        $arSummary = [
            'totalRemaining' => (float) $arCustomerRows->sum('totalRemaining'),
            'unpaidCount' => (int) $arCustomerRows->sum('unpaidCount'),
            'customerCount' => $arCustomerRows->count(),
            'overdueAmount' => (float) $arCustomerRows->sum('overdueAmount'),
            'overdueCount' => (int) $arCustomerRows->sum('overdueCount'),
            'dueSoonAmount' => (float) $arCustomerRows->sum('dueSoonAmount'),
            'dueSoonCount' => (int) $arCustomerRows->sum('dueSoonCount'),
            'urgentInvoices' => $urgentInvoices,
        ];

        // 2. Hutang Vendor (AP) + Pembayaran Langsung + Hutang Supplier
        $vendorUnpaidNotas = app(VendorPaymentService::class)->findUnpaidNotas();
        $vendorAp = [
            'notaCount' => $vendorUnpaidNotas->count(),
            'orderCount' => (int) $vendorUnpaidNotas->sum('order_count'),
            'totalRemaining' => (float) $vendorUnpaidNotas->sum('remaining_amount'),
        ];

        $supplierAp = app(PurchasePaymentService::class)->statsUnpaid();
        $directWaiting = app(DirectPaymentService::class)->statsWaiting();
        $directUnpaidNota = app(DirectPaymentService::class)->statsUnpaid();

        $apSummary = [
            'totalPayableRemaining' => $vendorAp['totalRemaining'] + (float) ($supplierAp['totalRemaining'] ?? 0),
            'vendor' => $vendorAp,
            'supplier' => $supplierAp,
            'directPayment' => [
                'waitingCount' => (int) ($directWaiting['waitingCount'] ?? 0),
                'waitingRemaining' => (float) ($directWaiting['totalRemaining'] ?? 0),
                'notaCount' => (int) ($directUnpaidNota['totalCount'] ?? 0),
                'notaRemaining' => (float) ($directUnpaidNota['totalRemaining'] ?? 0),
            ],
        ];

        // 3. Saldo Rekening Internal PHL
        $bankAccounts = DB::table('user_bank')
            ->leftJoin('live_mutation', function ($join) {
                $join->on('live_mutation.userBankCode', '=', 'user_bank.code')
                    ->whereNull('live_mutation.deleted_at');
            })
            ->leftJoin('bank_account', 'bank_account.code', '=', 'user_bank.bankCode')
            ->where('user_bank.rekening_type', 'internal')
            ->whereNull('user_bank.deleted_at')
            ->select([
                'user_bank.code',
                'user_bank.accountName',
                'user_bank.accountNumber',
                DB::raw('COALESCE(bank_account.name, "-") as bankName'),
                DB::raw('COALESCE(live_mutation.balance, 0) as balance'),
            ])
            ->orderByDesc('balance')
            ->get()
            ->map(fn ($row) => [
                'code' => $row->code,
                'accountName' => $row->accountName,
                'accountNumber' => $row->accountNumber,
                'bankName' => $row->bankName,
                'balance' => (float) $row->balance,
            ])
            ->values()
            ->all();

        $totalBankBalance = array_reduce($bankAccounts, fn ($carry, $item) => $carry + $item['balance'], 0.0);

        // 4. Operasional DO & Armada Aktif
        $notReturnDoCount = (int) DB::table('order')->whereNull('deleted_at')->where('status', 3)->count();
        $readyInvoiceCount = (int) DB::table('order')->whereNull('deleted_at')->where('status', 4)->count();

        $notReturnDoList = DB::table('order')
            ->leftJoin('customer', 'customer.code', '=', 'order.customerCode')
            ->leftJoin('fleet', 'fleet.code', '=', 'order.fleetCode')
            ->leftJoin('employee', 'employee.code', '=', 'order.driverCode')
            ->whereNull('order.deleted_at')
            ->where('order.status', 3)
            ->select([
                'order.id',
                'order.code',
                'order.shipmentNumber',
                'order.orderDate',
                DB::raw('COALESCE(customer.name, "-") as customerName'),
                DB::raw('COALESCE(NULLIF(order.plateNumber, ""), fleet.plateNumber, "-") as plateNumber'),
                DB::raw('COALESCE(NULLIF(order.driverName, ""), employee.name, "-") as driverName'),
            ])
            ->orderBy('order.orderDate', 'asc')
            ->limit(8)
            ->get()
            ->map(function ($row) {
                $daysPending = $row->orderDate ? Carbon::parse($row->orderDate)->diffInDays(Carbon::today()) : 0;

                return [
                    'code' => $row->code,
                    'shipmentNumber' => $row->shipmentNumber ?: $row->code,
                    'orderDate' => $row->orderDate ? Carbon::parse($row->orderDate)->format('d/m/Y') : '-',
                    'customerName' => $row->customerName,
                    'plateNumber' => $row->plateNumber,
                    'driverName' => $row->driverName,
                    'daysPending' => (int) $daysPending,
                ];
            })
            ->values()
            ->all();

        $onRoadOrders = DB::table('order')
            ->leftJoin('customer', 'customer.code', '=', 'order.customerCode')
            ->leftJoin('fleet', 'fleet.code', '=', 'order.fleetCode')
            ->leftJoin('fleet_type', 'fleet_type.code', '=', 'fleet.fleetTypeCode')
            ->leftJoin('employee', 'employee.code', '=', 'order.driverCode')
            ->leftJoin('route', 'route.code', '=', 'order.routeCode')
            ->leftJoin('location as dest', 'dest.code', '=', 'route.destinationLocationCode')
            ->leftJoin('order_status', 'order_status.code', '=', 'order.status')
            ->whereNull('order.deleted_at')
            ->whereIn('order.status', [0, 1, 2])
            ->select([
                'order.code',
                'order.shipmentNumber',
                'order.orderDate',
                'order.status',
                DB::raw('COALESCE(order_status.nama, order_status.name, "Berjalan") as statusLabel'),
                DB::raw('COALESCE(NULLIF(order.plateNumber, ""), fleet.plateNumber, "-") as plateNumber'),
                DB::raw('COALESCE(fleet_type.name, "-") as fleetTypeName'),
                DB::raw('COALESCE(NULLIF(order.driverName, ""), employee.name, "-") as driverName'),
                DB::raw('COALESCE(customer.name, "-") as customerName'),
                DB::raw('COALESCE(dest.name, "-") as destinationName'),
            ])
            ->orderByDesc('order.orderDate')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'code' => $row->code,
                'shipmentNumber' => $row->shipmentNumber ?: $row->code,
                'orderDate' => $row->orderDate ? Carbon::parse($row->orderDate)->format('d/m/Y') : '-',
                'status' => (int) $row->status,
                'statusLabel' => $row->statusLabel,
                'plateNumber' => $row->plateNumber,
                'fleetTypeName' => $row->fleetTypeName,
                'driverName' => $row->driverName,
                'customerName' => $row->customerName,
                'destinationName' => $row->destinationName,
            ])
            ->values()
            ->all();

        $totalInternalFleets = (int) DB::table('fleet')
            ->join('fleet_company', 'fleet_company.code', '=', 'fleet.fleetCompanyCode')
            ->whereNull('fleet.deleted_at')
            ->whereNull('fleet_company.deleted_at')
            ->whereRaw("LOWER(fleet_company.type) = 'internal'")
            ->count();

        $onRoadInternalFleets = (int) DB::table('order')
            ->join('fleet', 'fleet.code', '=', 'order.fleetCode')
            ->join('fleet_company', 'fleet_company.code', '=', 'fleet.fleetCompanyCode')
            ->whereNull('order.deleted_at')
            ->whereNull('fleet.deleted_at')
            ->whereNull('fleet_company.deleted_at')
            ->whereRaw("LOWER(fleet_company.type) = 'internal'")
            ->whereIn('order.status', [0, 1, 2])
            ->distinct('order.fleetCode')
            ->count('order.fleetCode');

        $onRoadTotalCount = (int) DB::table('order')
            ->whereNull('deleted_at')
            ->whereIn('status', [0, 1, 2])
            ->count();

        // 5. Stok Sparepart Menipis / Habis (qty <= 5)
        $lowStockBase = DB::table('stock')
            ->join('item', 'item.code', '=', 'stock.itemCode')
            ->leftJoin('item_category', 'item_category.code', '=', 'item.categoryCode')
            ->leftJoin('warehouse', 'warehouse.code', '=', 'item.warehouseCode')
            ->leftJoin('unit', 'unit.code', '=', 'item.unitCode')
            ->whereNull('stock.deleted_at')
            ->whereNull('item.deleted_at')
            ->where('item.type', 'part')
            ->whereRaw('(COALESCE(stock.stockIn, 0) - COALESCE(stock.stockOut, 0)) <= 5');

        $lowStockCount = (clone $lowStockBase)->count();
        $lowStockItems = (clone $lowStockBase)
            ->select([
                'item.code',
                'item.name',
                DB::raw('COALESCE(item_category.name, "-") as categoryName'),
                DB::raw('COALESCE(warehouse.name, "-") as warehouseName'),
                DB::raw('COALESCE(unit.name, "Pcs") as unitName'),
                DB::raw('(COALESCE(stock.stockIn, 0) - COALESCE(stock.stockOut, 0)) as qty'),
            ])
            ->orderBy('qty', 'asc')
            ->orderBy('item.name', 'asc')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'code' => $row->code,
                'name' => $row->name,
                'categoryName' => $row->categoryName,
                'warehouseName' => $row->warehouseName,
                'unitName' => $row->unitName,
                'qty' => (float) $row->qty,
            ])
            ->values()
            ->all();

        return [
            'ar' => $arSummary,
            'ap' => $apSummary,
            'bank' => [
                'totalBalance' => $totalBankBalance,
                'accounts' => $bankAccounts,
            ],
            'operational' => [
                'notReturnDoCount' => $notReturnDoCount,
                'readyInvoiceCount' => $readyInvoiceCount,
                'onRoadOrderCount' => $onRoadTotalCount,
                'internalFleetTotal' => $totalInternalFleets,
                'internalFleetOnRoad' => $onRoadInternalFleets,
                'internalFleetStandby' => max(0, $totalInternalFleets - $onRoadInternalFleets),
                'notReturnDoList' => $notReturnDoList,
                'onRoadOrders' => $onRoadOrders,
            ],
            'inventory' => [
                'lowStockCount' => $lowStockCount,
                'lowStockItems' => $lowStockItems,
            ],
        ];
    }
}
