<?php

namespace App\Http\Controllers\Report;

use App\Helpers\FilterHelper;
use App\Http\Controllers\Controller;
use App\Models\Operational\Order;
use App\Models\Operational\OrderCost;
use App\Services\Master\EmployeeService;
use App\Services\Master\FleetService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;
use Yajra\DataTables\DataTables;

class DriverSalaryController extends Controller
{
    protected $title;

    protected $view;

    protected $driverSvc;

    protected $fleetSvc;

    public function __construct(
        EmployeeService $driverSvc,
        FleetService $fleetSvc,
    ) {
        $this->title = 'Driver Salary';
        $this->view = 'report.driver-salary.';
        $this->driverSvc = $driverSvc;
        $this->fleetSvc = $fleetSvc;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $driver = $this->driverSvc->findAll();
        $fleet = $this->fleetSvc->findAll();

        return view($this->view . 'index')
            ->with('view', $this->view)
            ->with('driver', $driver)
            ->with('fleet', $fleet)
            ->with('title', $this->title);
    }

    /**
     * Build the base query for driver salary report.
     * Returns orders that have at least one OrderCost whose CostComponent.type = 'salary'.
     */
    private function buildSalaryQuery(Request $request)
    {
        $query = Order::with(['fleet', 'driver', 'route.originLocation', 'route.destinationLocation'])
            ->whereHas('cost', function ($q) {
                $q->whereHas('costComponent', function ($q2) {
                    $q2->where('type', 'salary');
                });
            })
            ->whereNull('order.deleted_at');

        // Apply filters
        $filters = [
            'driverCode' => $request->driverCode,
            'fleetCode'  => $request->fleetCode,
        ];

        $dateFilters = [
            'orderDate' => [
                'start' => $request->startDate,
                'end'   => $request->endDate,
            ],
        ];

        $query = FilterHelper::applyFilters($query, $filters, [], $dateFilters);

        return $query->orderBy('driverCode')->orderBy('orderDate', 'asc');
    }

    /**
     * Get salary total for a single order (sum of OrderCost where costComponent.type = 'salary').
     */
    private function getSalaryTotal($orderCode)
    {
        return OrderCost::where('orderCode', $orderCode)
            ->whereHas('costComponent', function ($q) {
                $q->where('type', 'salary');
            })
            ->sum('nominal');
    }

    /**
     * AJAX datatable endpoint - returns flat order rows for DataTable rendering.
     */
    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $query = $this->buildSalaryQuery($request);

            return Datatables::of($query)
                ->addIndexColumn()
                ->editColumn('orderDate', function ($row) {
                    return Carbon::parse($row->orderDate)->format('d-m-Y');
                })
                ->addColumn('driverName', function ($row) {
                    return $row->driver->name ?? '-';
                })
                ->addColumn('plateNumber', function ($row) {
                    return $row->fleet->plateNumber ?? '-';
                })
                ->addColumn('routeName', function ($row) {
                    if (! $row->route) {
                        return '-';
                    }
                    $origin = $row->route->originLocation->name ?? '';
                    $dest   = $row->route->destinationLocation->name ?? '';

                    return $row->route->name . ' (' . $origin . ' - ' . $dest . ')';
                })
                ->addColumn('salaryTotal', function ($row) {
                    $total = $this->getSalaryTotal($row->code);

                    return number_format($total, 0, ',', '.');
                })
                ->addColumn('salaryTotalRaw', function ($row) {
                    return $this->getSalaryTotal($row->code);
                })
                ->rawColumns(['driverName', 'plateNumber', 'routeName', 'salaryTotal'])
                ->toJson();
        }
    }

    /**
     * Generate PDF Slip Gaji per driver.
     */
    public function pdfDriverSalary(Request $request)
    {
        $query = $this->buildSalaryQuery($request);
        $orders = $query->get();

        // Group by driverCode
        $grouped = $orders->groupBy('driverCode');

        // Build data structure per driver
        $driverData = [];
        foreach ($grouped as $driverCode => $driverOrders) {
            $driver = $driverOrders->first()->driver;
            $fleet  = $driverOrders->first()->fleet;

            $rows = [];
            $grandTotal = 0;

            foreach ($driverOrders as $index => $order) {
                $salaryTotal = $this->getSalaryTotal($order->code);
                $grandTotal += $salaryTotal;

                $routeName = '-';
                if ($order->route) {
                    $origin = $order->route->originLocation->name ?? '';
                    $dest   = $order->route->destinationLocation->name ?? '';
                    $routeName = $order->route->name . ' (' . $origin . ' - ' . $dest . ')';
                }

                $rows[] = [
                    'no'        => $index + 1,
                    'date'      => Carbon::parse($order->orderDate)->format('d-m-Y'),
                    'route'     => $routeName,
                    'salary'    => $salaryTotal,
                ];
            }

            // Determine month label from date filter or from data
            $monthLabel = '';
            if ($request->startDate) {
                $monthLabel = Carbon::parse($request->startDate)->translatedFormat('F Y');
            } elseif ($driverOrders->count() > 0) {
                $monthLabel = Carbon::parse($driverOrders->first()->orderDate)->translatedFormat('F Y');
            }

            $driverData[] = [
                'driverName'   => $driver->name ?? '-',
                'plateNumber'  => $fleet->plateNumber ?? '-',
                'month'        => $monthLabel,
                'rows'         => $rows,
                'grandTotal'   => $grandTotal,
            ];
        }

        $mpdf = new Mpdf([
            'orientation' => 'P',
            'format'      => [215, 330],
            'tempDir'     => storage_path('app/mpdf-temp'),
        ]);
        $mpdf->setAutoTopMargin = 'stretch';
        $mpdf->setAutoBottomMargin = 'stretch';

        $html = view($this->view . 'report.driver-salary-pdf')
            ->with('driverData', $driverData)
            ->render();

        $mpdf->WriteHTML($html);

        return $mpdf->Output('Slip_Gaji_Driver.pdf', 'I');
    }
}
