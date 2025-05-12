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
        $currentMonth = now()->month;
        $currentMonthName = Carbon::create()->month($currentMonth)->translatedFormat('F');


        $totalOrders = Order::whereYear('created_at', $currentYear)->count();

        $monthlyOrderNow = Order::whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->count();

        $ordersByStatus = OrderStatus::leftJoin('order', function ($join) use ($currentYear) {
            $join->on('order_status.code', '=', 'order.status')
                ->whereYear('order.created_at', $currentYear);
        })
            ->select('order_status.name', 'order_status.code', DB::raw('COUNT(fms_order.status) as total'))
            ->groupBy('order_status.name', 'order_status.code')
            ->orderBy('order_status.code')
            ->get();

        $orders = Order::whereYear('created_at', $currentYear)
            ->select(DB::raw('MONTH(created_at) as month'), DB::raw('COUNT(*) as total'))
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->pluck('total', 'month');

        $monthlyOrders = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyOrders[] = $orders[$i] ?? 0;
        }

        $totalPurchases = Purchase::whereYear('created_at', $currentYear)->count();

        $monthlyPurchaseNow = Purchase::whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->count();

        $purchaseByStatus = PurchaseStatus::leftJoin('purchase', function ($join) use ($currentYear) {
            $join->on('purchase_status.code', '=', 'purchase.status')
                ->whereYear('purchase.created_at', $currentYear);
        })
            ->select('purchase_status.name', 'purchase_status.code', DB::raw('COUNT(fms_purchase.status) as total'))
            ->groupBy('purchase_status.name', 'purchase_status.code')
            ->orderBy('purchase_status.code', 'asc')
            ->get();

        $purchases = Purchase::whereYear('created_at', $currentYear)
            ->select(DB::raw('MONTH(created_at) as month'), DB::raw('COUNT(*) as total'))
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->pluck('total', 'month');

        $monthlyPurchases = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyPurchases[] = $purchases[$i] ?? 0;
        }

        $fleet = $this->fleetSvc->findAll();

        return view($this->view . 'home', [
            'monthlyOrders' => $monthlyOrders,
            'monthlyPurchases' => $monthlyPurchases,
            'monthlyOrderNow' => $monthlyOrderNow,
            'monthlyPurchaseNow' => $monthlyPurchaseNow,
            'currentMonthName' => $currentMonthName,
            'totalOrders' => $totalOrders,
            'ordersByStatus' => $ordersByStatus,
            'totalPurchases' => $totalPurchases,
            'purchaseByStatus' => $purchaseByStatus,
            'currentYear' => $currentYear,
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
}
