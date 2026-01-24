<?php

namespace App\Http\Controllers\Report;

use App\Exports\OrderDetailReport;
use App\Helpers\FilterHelper;
use App\Http\Controllers\Controller;
use App\Models\Operational\Order;
use App\Services\Master\CustomerService;
use App\Services\Master\EmployeeService;
use App\Services\Master\FleetService;
use App\Services\Master\FleetTypeService;
use App\Services\Master\LocationService;
use App\Services\Master\OrderTypeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;
use Yajra\DataTables\DataTables;

class OrderDetailController extends Controller
{
    protected $service;

    protected $title;

    protected $view;

    protected $customerSvc;

    protected $orderTypeSvc;

    protected $fleetTypeSvc;

    protected $driverSvc;

    protected $fleetSvc;

    protected $locationSvc;

    public function __construct(
        CustomerService $customerSvc,
        OrderTypeService $orderTypeSvc,
        FleetTypeService $fleetTypeSvc,
        EmployeeService $driverSvc,
        FleetService $fleetSvc,
        LocationService $locationSvc
    ) {
        $this->title = 'Order Detail Report';
        $this->view = 'report.order-detail.';
        $this->customerSvc = $customerSvc;
        $this->orderTypeSvc = $orderTypeSvc;
        $this->fleetTypeSvc = $fleetTypeSvc;
        $this->driverSvc = $driverSvc;
        $this->fleetSvc = $fleetSvc;
        $this->locationSvc = $locationSvc;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customer = $this->customerSvc->findAll();
        $fleetType = $this->fleetTypeSvc->findAll();
        $location = $this->locationSvc->findAll();
        $driver = $this->driverSvc->findAll();
        $fleet = $this->fleetSvc->findAll();
        $orderType = $this->orderTypeSvc->findAll();

        return view($this->view.'index')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('fleet', $fleet)
            ->with('customer', $customer)
            ->with('driver', $driver)
            ->with('location', $location)
            ->with('fleetType', $fleetType)
            ->with('orderType', $orderType);
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = Order::with([
                'fleet',
                'fleet.type',
                'driver',
                'customer',
                'route.destinationLocation',
                'route.originLocation',
                'material',
                'cost.costComponent',
            ])->orderBy('orderDate', 'desc');

            // Define filters
            $filters = [
                'fleet_plateNumber' => $request->plateNumber,
                'customer_name' => $request->customerName,
                'driver_name' => $request->driverName,
                'fleetType_name' => $request->fleetTypeName,
                'shipmentNumber' => $request->shipmentNumber,
                'origin' => $request->origin,
                'destination' => $request->destination,
                'orderTypeCode' => $request->orderTypeCode,
            ];

            // Map filters to relations
            $relations = [
                'fleet_plateNumber' => 'fleet.plateNumber',
                'customer_name' => 'customer.name',
                'driver_name' => 'driver.name',
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

            $data = FilterHelper::applyFilters($data, $filters, $relations, $dateFilters);

            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('orderDate', function ($row) {
                    return $row->orderDate ? \Carbon\Carbon::parse($row->orderDate)->format('d-m-Y') : '';
                })
                ->editColumn('fleet.plateNumber', function ($row) {
                    return $row->fleet->plateNumber ?? '';
                })
                ->editColumn('customer.name', function ($row) {
                    return $row->customer->name ?? '';
                })
                ->addColumn('customer_name', function ($row) {
                    return $row->customer->name ?? '';
                })
                ->editColumn('driver.name', function ($row) {
                    return $row->driver->name ?? '';
                })
                ->editColumn('route.originLocation.name', function ($row) {
                    return $row->route->originLocation->name ?? '';
                })
                ->editColumn('route.destinationLocation.name', function ($row) {
                    return $row->route->destinationLocation->name ?? '';
                })
                ->addColumn('sales', function ($row) {
                    // `routeAmount` now stored as total for the order (unit price * qty)
                    $sales = $row->routeAmount;

                    return number_format($sales, 0, ',', '.');
                })
                ->addColumn('cost_detail', function ($row) {
                    $costDetails = [];
                    if ($row->cost) {
                        foreach ($row->cost as $cost) {
                            $costName = $cost->costComponent->name ?? 'N/A';
                            $costDetails[] = $costName.': '.number_format($cost->nominal, 0, ',', '.');
                        }
                    }

                    if (empty($costDetails)) {
                        return '<span class="text-muted">-</span>';
                    }

                    // Render cost details as vertical list in detail cell
                    $html = '<div class="cost-detail-list" style="font-size: 12px;">';
                    foreach ($costDetails as $key => $detail) {
                        $html .= '<div class="cost-detail-item">'.($key + 1).'. '.$detail.'</div>';
                    }
                    $html .= '</div>';

                    return $html;
                })
                ->addColumn('total_cost', function ($row) {
                    $totalCost = 0;
                    if ($row->cost) {
                        foreach ($row->cost as $cost) {
                            $totalCost += $cost->nominal;
                        }
                    }

                    return number_format($totalCost, 0, ',', '.');
                })
                ->addColumn('profit', function ($row) {
                    // `routeAmount` is total for the order
                    $sales = $row->routeAmount;
                    $totalCost = 0;
                    if ($row->cost) {
                        foreach ($row->cost as $cost) {
                            $totalCost += $cost->nominal;
                        }
                    }
                    $profit = $sales - $totalCost;

                    return number_format($profit, 0, ',', '.');
                })
                ->rawColumns(['cost_detail'])
                ->toJson();
        }
    }

    public function excelOrderDetail(Request $request)
    {
        return Excel::download(new OrderDetailReport($request), 'Order-Detail-Report.xlsx');
    }

    public function pdfOrderDetail(Request $request)
    {
        // Define filters
        $filters = [
            'fleet_plateNumber' => $request->plateNumber,
            'customer_name' => $request->customerName,
            'driver_name' => $request->driverName,
            'fleetType_name' => $request->fleetTypeName,
            'shipmentNumber' => $request->shipmentNumber,
            'origin' => $request->origin,
            'destination' => $request->destination,
            'orderTypeCode' => $request->orderTypeCode,
        ];

        // Map filters to relations
        $relations = [
            'fleet_plateNumber' => 'fleet.plateNumber',
            'customer_name' => 'customer.name',
            'driver_name' => 'driver.name',
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

        $query = Order::with([
            'fleet',
            'fleet.type',
            'driver',
            'customer',
            'route.destinationLocation',
            'route.originLocation',
            'material',
            'cost.costComponent',
        ])->orderBy('orderDate', 'desc');

        $data = FilterHelper::applyFilters($query, $filters, $relations, $dateFilters)->get();

        $mpdf = new Mpdf([
            'orientation' => 'L',
            'format' => 'A4',
        ]);

        $mpdf->setAutoTopMargin = 'stretch';
        $mpdf->setAutoBottomMargin = 'stretch';

        // Write header
        $headerHtml = View::make($this->view.'report.order-detail-pdf-header')->render();
        $mpdf->WriteHTML($headerHtml);

        // Write data in chunks and keep a running start index so numbering continues
        $chunkSize = 100;
        $chunks = $data->chunk($chunkSize);

        $start = 0;
        foreach ($chunks as $chunk) {
            $rowHtml = View::make($this->view.'report.order-detail-pdf-rows')
                ->with('data', $chunk)
                ->with('start', $start)
                ->render();
            $mpdf->WriteHTML($rowHtml);
            $start += $chunk->count();
        }

        // Write footer
        $footerHtml = View::make($this->view.'report.order-detail-pdf-footer')
            ->with('data', $data)
            ->render();
        $mpdf->WriteHTML($footerHtml);

        return $mpdf->Output('Order-Detail-Report.pdf', 'I');
    }
}
