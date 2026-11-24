<?php

namespace App\Http\Controllers;

use App\Exports\FleetMaintenanceReport;
use App\Helpers\FilterHelper;
use App\Models\Operational\Order;
use App\Services\DashboardService;
use App\Services\Master\FleetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;
use Yajra\DataTables\DataTables;

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
        $this->view = 'dashboard.';
    }

    public function index(Request $request)
    {
        $period = $this->service->resolvePeriod(
            $request->input('preset'),
            $request->input('startDate'),
            $request->input('endDate')
        );

        $periodic = $this->service->getPeriodicSummary($period['startDate'], $period['endDate']);
        $live = $this->service->getLiveSnapshot();

        return view($this->view.'home', [
            'period' => $period,
            'periodic' => $periodic,
            'live' => $live,
            'view' => $this->view,
        ]);
    }

    /**
     * Endpoint JSON untuk memperbarui metrik periodik & grafik saat filter periode berubah.
     */
    public function summary(Request $request)
    {
        $period = $this->service->resolvePeriod(
            $request->input('preset'),
            $request->input('startDate'),
            $request->input('endDate')
        );

        $periodic = $this->service->getPeriodicSummary($period['startDate'], $period['endDate']);

        return response()->json([
            'period' => $period,
            'periodic' => $periodic,
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

            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('status', function ($row) {
                    if (in_array($row->status, [0, 1, 2]) && $row->status !== null) {
                        return 'On the road';
                    }

                    return 'Order finish - Stand by';
                })
                ->rawColumns(['status'])
                ->toJson();
        }
    }

    public function pdfFleetMaintenance(Request $request)
    {
        $mpdf = new Mpdf([
            'orientation' => 'P',
            'format' => [215, 330],
            'tempDir' => storage_path('app/mpdf-temp'),
        ]);

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
            view($this->view.'report.fleet-maintenance-pdf')
                ->with('data', $data->get())
        );

        return $mpdf->Output('Fleet Maintenance Report.pdf', 'I');
    }

    public function excelFleetMaintenance(Request $request)
    {
        return Excel::download(new FleetMaintenanceReport($request), 'Fleet-Maintenance-Report.xlsx');
    }

    public function getCustomerStats(Request $request)
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month');

        $query = Order::join('customer', 'order.customerCode', '=', 'customer.code')
            ->select('customer.name', 'customer.code', DB::raw('COUNT(*) as orders_count'), DB::raw('MAX(order.orderDate) as latest_order'))
            ->whereYear('order.orderDate', $year);

        if ($month) {
            $query->whereMonth('order.orderDate', $month);
        }

        $customers = $query->groupBy('customer.code', 'customer.name')
            ->orderBy('orders_count', 'desc')
            ->limit(10)
            ->get();

        return response()->json($customers);
    }

    public function getFleetStats(Request $request)
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month');

        $query = Order::join('fleet', 'order.fleetCode', '=', 'fleet.code')
            ->select('fleet.plateNumber as name', 'fleet.code', DB::raw('COUNT(*) as orders_count'), DB::raw('MAX(order.orderDate) as latest_order'))
            ->whereYear('order.orderDate', $year);

        if ($month) {
            $query->whereMonth('order.orderDate', $month);
        }

        $fleets = $query->groupBy('fleet.code', 'fleet.plateNumber')
            ->orderBy('orders_count', 'desc')
            ->limit(10)
            ->get();

        return response()->json($fleets);
    }

    public function getOrderStatsByYear(Request $request)
    {
        $year = $request->input('year', now()->year);

        $orders = Order::whereYear('orderDate', $year)
            ->select(DB::raw('MONTH(orderDate) as month'), DB::raw('COUNT(*) as total'))
            ->groupBy(DB::raw('MONTH(orderDate)'))
            ->pluck('total', 'month');

        $monthlyOrderData = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyOrderData[$i] = $orders[$i] ?? 0;
        }

        return response()->json($monthlyOrderData);
    }

    public function getOrderCount(Request $request)
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month');

        $query = Order::whereYear('orderDate', $year);

        if ($month) {
            $query->whereMonth('orderDate', $month);
        }

        return response()->json([
            'count' => $query->count(),
            'year' => $year,
            'month' => $month,
        ]);
    }

    public function getPendingInvoiceOrders(Request $request)
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month');

        $query = Order::where('status', '<', 5)
            ->whereYear('orderDate', $year);

        if ($month) {
            $query->whereMonth('orderDate', $month);
        }

        return response()->json([
            'count' => $query->count(),
            'year' => $year,
            'month' => $month,
        ]);
    }
}
