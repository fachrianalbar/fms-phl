<?php

namespace App\Http\Controllers\Report;

use App\Exports\FleetTonaseReport;
use App\Helpers\FilterHelper;
use App\Http\Controllers\Controller;
use App\Models\Operational\Order;
use App\Services\Master\CustomerService;
use App\Services\Master\FleetService;
use App\Services\Master\FleetTypeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;

class FleetTonaseController extends Controller
{
    protected $service;

    protected $title;

    protected $view;

    protected $menuSvc;

    protected $fleetSvc;

    protected $customerSvc;

    protected $fleetTypeSvc;

    public function __construct(
        FleetService $fleetSvc,
        CustomerService $customerSvc,
        FleetTypeService $fleetTypeSvc,
    ) {
        $this->service = '';
        $this->title = 'Fleet Tonase';
        $this->view = 'report.fleet-tonase.';
        $this->fleetSvc = $fleetSvc;
        $this->customerSvc = $customerSvc;
        $this->fleetTypeSvc = $fleetTypeSvc;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $fleet = $this->fleetSvc->findAll();
        $customer = $this->customerSvc->findAll();
        $fleetType = $this->fleetTypeSvc->findAll();

        return view($this->view.'index')
            ->with('view', $this->view)
            ->with('fleet', $fleet)
            ->with('customer', $customer)
            ->with('fleetType', $fleetType)
            ->with('title', $this->title);
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = Order::select('fleetCode', 'customerCode', DB::raw('SUM('.env('DB_PREFIX').'order.qty) as total_tonase'))
                ->join('fleet', 'fleet.code', '=', 'order.fleetCode')
                ->join('customer', 'customer.code', '=', 'order.customerCode')
                ->with(['fleet.type', 'customer'])
                ->whereNull('order.deleted_at')
                ->groupBy('fleetCode', 'fleet.code')
                ->groupBy('customerCode', 'customer.code');

            // Definisikan kolom filter dengan alias
            $filters = [
                'fleetCode' => $request->fleetCode,
                'customerCode' => $request->customerCode,
                'fleetType_name' => $request->fleetTypeName,
            ];

            // Hubungkan alias ke relasi dan kolom yang sesuai
            $relations = [
                'fleetType_name' => 'fleet.type.name',
            ];

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

                ->editColumn('fleet.type.name', function ($row) {
                    $type = '';

                    if (isset($row->fleet->type->name)) {
                        $type = $row->fleet->type->name;
                    }

                    return $type;
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

                ->rawColumns(['fleet.plateNumber', 'fleet.type.name', 'customer.name', 'total_tonase'])
                ->toJson();
        }
    }

    public function excelFleetTonase(Request $request)
    {
        return Excel::download(new FleetTonaseReport($request), 'Fleet-Tonase-Report.xlsx');
    }
}
