<?php

namespace App\Http\Controllers\Report;

use App\Helpers\FilterHelper;
use App\Http\Controllers\Controller;
use App\Services\MenuService;
use App\Models\Operational\Order;
use App\Services\Master\EmployeeService;
use App\Services\Master\FleetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Mpdf\Mpdf;


class DriverSalaryController extends Controller
{
    protected $service;
    protected $title;
    protected $view;
    protected $menuSvc;
    protected $driverSvc;
    protected $fleetSvc;

    public function __construct(
        EmployeeService $driverSvc,
        FleetService $fleetSvc,
    ) {
        $this->service = '';
        $this->title = "Driver Salary";
        $this->view = "report.driver-salary.";
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

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = Order::select('fleetCode', 'driverCode',  DB::raw('COUNT(*) as total_orders'))
                ->join('fleet', 'fleet.code', '=', 'order.fleetCode')
                ->join('employee', 'employee.code', '=', 'order.driverCode')
                ->with(['fleet', 'driver'])
                ->whereNull('order.deleted_at')
                ->groupBy('fleetCode', 'fleet.code')
                ->groupBy('driverCode', 'employee.code');
            // Use fleet.plateNumber
            // ->orderBy('fleet.plateNumber');  // Order by plateNumber

            // Definisikan kolom filter dengan alias
            $filters = [
                'fleetCode' => $request->fleetCode,
                'driverCode' => $request->driverCode
            ];

            // Hubungkan alias ke relasi dan kolom yang sesuai
            $relations = [];

            $dateFilters = [
                'orderDate' => [
                    'start' => $request->startDate,
                    'end' => $request->endDate,
                ],
            ];

            $data = FilterHelper::applyFilters($data, $filters, $relations, $dateFilters);

            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('fleet.plateNumber', function ($row) {
                    $fleet = '';

                    if (isset($row->fleet->plateNumber)) {
                        $fleet = $row->fleet->plateNumber;
                    }

                    return $fleet;
                })

                ->editColumn('driver.name', function ($row) {
                    $driver = '';


                    if (isset($row->driver->name)) {
                        $driver = $row->driver->name;
                    }

                    return $driver;
                })
                ->addColumn('value', function ($row) {
                    $value = $row->total_orders * 100000;
                    return number_format($value, 0, ',', '.');
                })
                ->addColumn('description', function ($row) {
                    return '';
                })
                ->addColumn('ttd', function ($row) {
                    return '';
                })

                ->rawColumns(['fleet.plateNumber', 'driver.name', 'value', 'description', 'ttd'])
                ->toJson();
        }
    }

    public function pdfDriverSalary(Request $request)
    {
        // Definisikan kolom filter dengan alias
        // Definisikan kolom filter dengan alias
        $filters = [
            'fleetCode' => $request->fleetCode,
            'driverCode' => $request->driverCode
        ];

        // Hubungkan alias ke relasi dan kolom yang sesuai
        $relations = [];

        $dateFilters = [
            'orderDate' => [
                'start' => $request->startDate,
                'end' => $request->endDate,
            ],
        ];

        $query = Order::select('fleetCode', 'driverCode',  DB::raw('COUNT(*) as total_orders'))
            ->join('fleet', 'fleet.code', '=', 'order.fleetCode')
            ->join('employee', 'employee.code', '=', 'order.driverCode')
            ->with(['fleet', 'driver'])
            ->whereNull('order.deleted_at')
            ->groupBy('fleetCode', 'fleet.code')
            ->groupBy('driverCode', 'employee.code');

        $data = FilterHelper::applyFilters($query, $filters, $relations, $dateFilters);

        $mpdf = new Mpdf(
            [
                'orientation' => 'P',
                'format' => [215, 330],
            ]
        );

        $mpdf->WriteHTML(
            view($this->view . 'report.driver-salary-pdf')
                ->with('data', $data->get())
        );

        return $mpdf->Output('Driver Salary Report.pdf', 'I');
    }
}
