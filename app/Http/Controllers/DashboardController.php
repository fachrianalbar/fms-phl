<?php

namespace App\Http\Controllers;

use App\Exports\FleetMaintenanceReport;
use App\Helpers\FilterHelper;
use App\Models\Operational\Order;
use App\Models\Operational\OrderStatus;
use App\Models\Purchasing\Purchase;
use App\Models\Purchasing\PurchaseStatus;
use App\Services\DashboardService;
use App\Services\Master\FleetService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Mpdf\Mpdf;
use Maatwebsite\Excel\Facades\Excel;



class DashboardController extends Controller
{
    protected $service;
    protected $fleetSvc;
    protected $view;


    public function __construct(
        DashboardService $dashboardService,
        FleetService $fleetSvc,
    ) {
        $this->service = $dashboardService;
        $this->fleetSvc = $fleetSvc;
        $this->view = "dashboard.";
    }
    public function index()
    {
        $currentYear = request('year', now()->year);
        $currentMonth = request('month', now()->month);
        $currentMonthName = Carbon::create()->month($currentMonth)->translatedFormat('F');

        // Total order bulan ini
        $monthlyOrderNow = Order::whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->count();

        // Statistik order per bulan untuk tahun ini
        $orders = Order::whereYear('created_at', $currentYear)
            ->select(DB::raw('MONTH(created_at) as month'), DB::raw('COUNT(*) as total'))
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->pluck('total', 'month');

        $monthlyOrders = [];
        $monthlyOrderData = [];
        for ($i = 1; $i <= 12; $i++) {
            $count = $orders[$i] ?? 0;
            $monthlyOrders[] = $count;
            $monthlyOrderData[$i] = $count;
        }

        // Customer dengan order terbanyak
        $topCustomers = Order::join('customer', 'order.customerCode', '=', 'customer.code')
            ->select('customer.name', 'customer.code', DB::raw('COUNT(*) as orders_count'), DB::raw('MAX(fms_order.created_at) as latest_order'))
            ->when($currentYear, function ($query) use ($currentYear) {
                return $query->whereYear('order.created_at', $currentYear);
            })
            ->when($currentMonth, function ($query) use ($currentMonth) {
                return $query->whereMonth('order.created_at', $currentMonth);
            })
            ->groupBy('customer.code', 'customer.name')
            ->orderBy('orders_count', 'desc')
            ->limit(10)
            ->get();

        // Fleet dengan order terbanyak
        $topFleets = Order::join('fleet', 'order.fleetCode', '=', 'fleet.code')
            ->select('fleet.plateNumber as name', 'fleet.code', DB::raw('COUNT(*) as orders_count'), DB::raw('MAX(fms_order.created_at) as latest_order'))
            ->when($currentYear, function ($query) use ($currentYear) {
                return $query->whereYear('order.created_at', $currentYear);
            })
            ->when($currentMonth, function ($query) use ($currentMonth) {
                return $query->whereMonth('order.created_at', $currentMonth);
            })
            ->groupBy('fleet.code', 'fleet.plateNumber')
            ->orderBy('orders_count', 'desc')
            ->limit(10)
            ->get();

        // Invoice jatuh tempo
        $overdueInvoices = DB::table('invoice')
            ->select(
                'invoice.id',
                'invoice.invoiceNumber',
                'invoice.invoiceDate',
                'invoice.overdueDate',
                'customer.name as customer_name',
                DB::raw('DATEDIFF(fms_invoice.overdueDate, CURDATE()) as days_remaining'),
            )
            ->join('customer', 'invoice.customerCode', '=', 'customer.code')
            ->orderBy('invoice.overdueDate', 'asc')
            ->where('invoice.deleted_at', null)
            ->limit(20)
            ->get()
            ->map(function ($invoice) {
                if ($invoice->days_remaining > 7) {
                    $invoice->status_color = 'success';
                    $invoice->status_text = 'Aman';
                } elseif ($invoice->days_remaining >= 0 && $invoice->days_remaining <= 7) {
                    $invoice->status_color = 'warning';
                    $invoice->status_text = 'Perhatian';
                } else {
                    $invoice->status_color = 'danger';
                    $invoice->status_text = 'Terlambat';
                }
                return $invoice;
            });

        // Order yang belum dibuat invoice (status bukan 5 dan kurang dari 5)
        $pendingInvoiceOrders = Order::where('status', '<', 5)
            ->where('status', '!=', 5)
            ->whereYear('created_at', $currentYear)
            ->when($currentMonth, function ($query) use ($currentMonth) {
                return $query->whereMonth('created_at', $currentMonth);
            })
            ->count();

        $totalOrders = Order::whereYear('created_at', $currentYear)->count();

        $ordersByStatus = OrderStatus::leftJoin('order', function ($join) use ($currentYear) {
            $join->on('order_status.code', '=', 'order.code')
                ->whereYear('order.created_at', $currentYear);
        })
            ->select('order_status.name', 'order_status.code', DB::raw('COUNT(fms_order.status) as total'))
            ->groupBy('order_status.name', 'order_status.code')
            ->orderBy('order_status.code')
            ->get();

        $totalPurchases = Purchase::whereYear('created_at', $currentYear)->count();

        $monthlyPurchaseNow = Purchase::whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->count();

        // $purchaseByStatus = PurchaseStatus::leftJoin('purchase', function ($join) use ($currentYear) {
        //     $join->on('purchase_status.code', '=', 'purchase.status')
        //         ->whereYear('purchase.created_at', $currentYear);
        // })
        //     ->select('purchase_status.name', 'purchase_status.code', DB::raw('COUNT(purchase.status) as total'))
        //     ->groupBy('purchase_status.name', 'purchase_status.code')
        //     ->orderBy('purchase_status.code', 'asc')
        //     ->get();

        // $purchases = Purchase::whereYear('created_at', $currentYear)
        //     ->select(DB::raw('MONTH(created_at) as month'), DB::raw('COUNT(*) as total'))
        //     ->groupBy(DB::raw('MONTH(created_at)'))
        //     ->pluck('total', 'month');

        $monthlyPurchases = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyPurchases[] = $purchases[$i] ?? 0;
        }

        $fleet = $this->fleetSvc->findAll();

        return view($this->view . 'home', [
            'monthlyOrders' => $monthlyOrders,
            'monthlyOrderData' => $monthlyOrderData,
            'monthlyPurchases' => $monthlyPurchases,
            'monthlyOrderNow' => $monthlyOrderNow,
            'monthlyPurchaseNow' => $monthlyPurchaseNow,
            'currentMonthName' => $currentMonthName,
            'totalOrders' => $totalOrders,
            'ordersByStatus' => $ordersByStatus,
            'totalPurchases' => $totalPurchases,
            // 'purchaseByStatus' => $purchaseByStatus,
            'currentYear' => $currentYear,
            'currentMonth' => $currentMonth,
            'topCustomers' => $topCustomers,
            'topFleets' => $topFleets,
            'overdueInvoices' => $overdueInvoices,
            'pendingInvoiceOrders' => $pendingInvoiceOrders,
            'fleet' => $fleet,
            'view' => $this->view
        ]);
    }

    public function datatableMaintenance(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->dashboardMaintenance();

            $filters = [
                'fleet.code' => $request->plateNumber,
            ];

            $relations = [];

            $dateFilters = [
                'maintenance.date' => [
                    'start' => $request->startDate,
                    'end' => $request->endDate,
                ],
            ];

            $data = FilterHelper::applyFilters($data, $filters, $relations, $dateFilters);

            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('price', function ($row) {

                    if (isset($row->price)) {
                        return number_format($row->price, 0, ',', '.');
                    }

                    return 0;
                })->rawColumns(['price'])
                ->toJson();
        }
    }

    public function datatableTruckOrder(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->dashboardTruckOrder();

            // $filters = [
            //     'fleet.code' => $request->plateNumber,
            // ];

            // $relations = [];

            // $dateFilters = [
            //     'maintenance.date' => [
            //         'start' => $request->startDate,
            //         'end' => $request->endDate,
            //     ],
            // ];

            // $data = FilterHelper::applyFilters($data, $filters, $relations, $dateFilters);

            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('status', function ($row) {
                    $status = '';
                    if (in_array($row->status, [0, 1, 2]) && $row->status !== null) {
                        $status = 'On the road';
                    } else {
                        $status = 'Order finish - Stand by';
                    }

                    return $status;
                })
                ->rawColumns(['status'])
                ->toJson();
        }
    }

    public function pdfFleetMaintenance(Request $request)
    {
        $mpdf = new Mpdf(
            [
                'orientation' => 'P',
                'format' => [215, 330],
            ]
        );

        $data = $this->service->dashboardMaintenance();

        $filters = [
            'fleet.code' => $request->plateNumber,
        ];

        $relations = [];

        $dateFilters = [
            'maintenance.date' => [
                'start' => $request->startDate,
                'end' => $request->endDate,
            ],
        ];

        $data = FilterHelper::applyFilters($data, $filters, $relations, $dateFilters);

        $mpdf->WriteHTML(
            view($this->view . 'report.fleet-maintenance-pdf')
                ->with('data', $data->get())
        );

        return $mpdf->Output('Fleet Maintenance Report.pdf', 'I');
    }

    public function excelFleetMaintenance(Request $request)
    {
        return Excel::download(new FleetMaintenanceReport($request), 'Fleet-Maintenance-Report.xlsx');
    }

    // API untuk filter customer berdasarkan tahun dan bulan
    public function getCustomerStats(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month');

        $query = Order::join('customer', 'order.customerCode', '=', 'customer.code')
            ->select('customer.name', 'customer.code', DB::raw('COUNT(*) as orders_count'), DB::raw('MAX(fms_order.created_at) as latest_order'))
            ->whereYear('order.created_at', $year);

        if ($month) {
            $query->whereMonth('order.created_at', $month);
        }

        $customers = $query->groupBy('customer.code', 'customer.name')
            ->orderBy('orders_count', 'desc')
            ->limit(10)
            ->get();

        return response()->json($customers);
    }

    // API untuk filter fleet berdasarkan tahun dan bulan
    public function getFleetStats(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month');

        $query = Order::join('fleet', 'order.fleetCode', '=', 'fleet.code')
            ->select('fleet.plateNumber as name', 'fleet.code', DB::raw('COUNT(*) as orders_count'), DB::raw('MAX(fms_order.created_at) as latest_order'))
            ->whereYear('order.created_at', $year);

        if ($month) {
            $query->whereMonth('order.created_at', $month);
        }

        $fleets = $query->groupBy('fleet.code', 'fleet.plateNumber')
            ->orderBy('orders_count', 'desc')
            ->limit(10)
            ->get();

        return response()->json($fleets);
    }

    // API untuk mendapatkan statistik order berdasarkan tahun
    public function getOrderStatsByYear(Request $request)
    {
        $year = $request->get('year', now()->year);

        $orders = Order::whereYear('created_at', $year)
            ->select(DB::raw('MONTH(created_at) as month'), DB::raw('COUNT(*) as total'))
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->pluck('total', 'month');

        $monthlyOrderData = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyOrderData[$i] = $orders[$i] ?? 0;
        }

        return response()->json($monthlyOrderData);
    }

    // API untuk mendapatkan jumlah order berdasarkan tahun dan bulan
    public function getOrderCount(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month');

        $query = Order::whereYear('created_at', $year);

        if ($month) {
            $query->whereMonth('created_at', $month);
        }

        $count = $query->count();

        return response()->json([
            'count' => $count,
            'year' => $year,
            'month' => $month
        ]);
    }

    // API untuk mendapatkan jumlah order yang belum dibuat invoice
    public function getPendingInvoiceOrders(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month');

        $query = Order::where('status', '<', 5)
            ->where('status', '!=', 5)
            ->whereYear('created_at', $year);

        if ($month) {
            $query->whereMonth('created_at', $month);
        }

        $count = $query->count();

        return response()->json([
            'count' => $count,
            'year' => $year,
            'month' => $month
        ]);
    }
}
