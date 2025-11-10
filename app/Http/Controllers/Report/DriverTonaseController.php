<?php

namespace App\Http\Controllers\Report;

use App\Exports\DriverTonaseReport;
use App\Helpers\FilterHelper;
use App\Http\Controllers\Controller;
use App\Models\Operational\Order;
use App\Services\Master\CustomerService;
use App\Services\Master\EmployeeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;

class DriverTonaseController extends Controller
{
    protected $service;

    protected $title;

    protected $view;

    protected $menuSvc;

    protected $driverSvc;

    protected $customerSvc;

    public function __construct(
        EmployeeService $driverSvc,
        CustomerService $customerSvc,

    ) {
        $this->service = '';
        $this->title = 'Driver Tonase';
        $this->view = 'report.driver-tonase.';
        $this->driverSvc = $driverSvc;
        $this->customerSvc = $customerSvc;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $driver = $this->driverSvc->findAll();
        $customer = $this->customerSvc->findAll();

        return view($this->view.'index')
            ->with('view', $this->view)
            ->with('driver', $driver)
            ->with('customer', $customer)
            ->with('title', $this->title);
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = Order::select('driverCode', 'customerCode', DB::raw('SUM('.env('DB_PREFIX').'order.qty) as total_tonase'))
                ->join('employee', 'employee.code', '=', 'order.driverCode')
                ->join('customer', 'customer.code', '=', 'order.customerCode')
                ->with(['driver', 'customer'])
                ->whereNull('order.deleted_at')
                ->groupBy('driverCode', 'employee.code')
                ->groupBy('customerCode', 'customer.code');
            // Use fleet.plateNumber
            // ->orderBy('fleet.plateNumber');  // Order by plateNumber

            // Definisikan kolom filter dengan alias
            $filters = [
                'customerCode' => $request->customerCode,
                'driverCode' => $request->driverCode,
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
                ->editColumn('driver.name', function ($row) {
                    $driver = '';

                    if (isset($row->driver->name)) {
                        $driver = $row->driver->name;
                    }

                    return $driver;
                })
                ->editColumn('customer.name', function ($row) {
                    $customer = '';

                    if (isset($row->customer->name)) {
                        $customer = $row->customer->name;
                    }

                    return $customer;
                })
                ->editColumn('total_tonase', function ($row) {

                    return number_format($row->total_tonase, 2);
                })

                ->rawColumns(['driver.name', 'customer.name', 'total_tonase'])
                ->toJson();
        }
    }

    public function excelDriverTonase(Request $request)
    {
        return Excel::download(new DriverTonaseReport($request), 'Driver-Tonase-Report.xlsx');
    }
}
