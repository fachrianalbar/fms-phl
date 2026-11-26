<?php

namespace App\Http\Controllers\Report;

use App\Exports\ProfitLossReport;
use App\Helpers\FilterHelper;
use App\Http\Controllers\Controller;
use App\Models\Data\TonaseBonus;
use App\Models\Master\Fleet;
use App\Models\Master\FleetCompany;
use App\Services\Master\FleetService;
use App\Services\Report\ProfitLossService;
use App\Traits\RendersPdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;

class ProfitLossController extends Controller
{
    use RendersPdf;
    protected $service;

    protected $fleetSvc;

    protected $title;

    protected $view;

    public function __construct(
        FleetService $fleetSvc,
        ProfitLossService $profitLossSvc
    ) {
        $this->service = $profitLossSvc;
        $this->title = 'Laporan Laba Rugi';
        $this->view = 'report.profit-loss.';
        $this->fleetSvc = $fleetSvc;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $fleet = Fleet::select('plateNumber')
            ->whereNotNull('plateNumber')
            ->orderBy('plateNumber')
            ->get();
        $fleetCompanies = FleetCompany::orderBy('name')->get();

        return view($this->view.'index')
            ->with('view', $this->view)
            ->with('fleet', $fleet)
            ->with('fleetCompanies', $fleetCompanies)
            ->with('title', $this->title);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id, Request $request)
    {
        $data = Fleet::with($this->service->fleetReportRelations($request->startDate, $request->endDate))
            ->where('id', $id)
            ->firstOrFail();

        $summary = $this->service->calculateFleetSummary($data);

        return view($this->view.'show')
            ->with('view', $this->view)
            ->with('data', $data)
            ->with('basicSales', $summary['basicSales'])
            ->with('orderCost', $summary['orderCost'])
            ->with('maintenance', $summary['maintenance'])
            ->with('tax', $summary['tax'])
            ->with('netProfit', $summary['netProfit'])
            ->with('totalMargin', $summary['netProfit'])
            ->with('title', $this->title);
    }

    public function datatableMaintenance(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->datatableMaintenance($request->fleetCode);

            $filters = [
                'fleet_plateNumber' => $request->plateNumber,
                'item_code' => $request->itemCode,
            ];

            $relations = [
                'fleet_plateNumber' => 'fleet.plateNumber',
                'item_code' => 'details.itemCode',
            ];

            $dateFilters = [
                'date' => [
                    'start' => $request->startDate,
                    'end' => $request->endDate,
                ],
            ];

            $data = FilterHelper::applyFilters($data, $filters, $relations, $dateFilters);

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('maintenanceDate', function ($row) {
                    $date = $row->date ? Carbon::parse($row->date)->format('d-M-Y') : '';
                    $time = $row->time ? Carbon::parse($row->time)->format('H:i') : '';

                    return trim($date.' '.$time);
                })
                ->editColumn('fleet.name', function ($row) {
                    return $row->fleet?->plateNumber ?? '';
                })
                ->addColumn('items', function ($row) {
                    $items = '';
                    foreach ($row->details ?? [] as $item) {
                        $items .= ($item->itemCode ?? '').': '.($item->item?->name ?? '(item dihapus)').' Qty : '.($item->qty ?? 0).'<br>';
                    }

                    return $items;
                })
                ->addColumn('price', function ($row) {
                    $price = 0.0;

                    foreach ($row->details ?? [] as $item) {
                        $price += (float) ($item->item?->price ?? 0) * (float) ($item->qty ?? 0);
                    }

                    return number_format($price, 0, ',', '.');
                })
                ->rawColumns(['maintenanceDate', 'fleet.plateNumber', 'items', 'price'])
                ->toJson();
        }
    }

    public function datatableOrder(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->datatableOrder($request->fleetCode);

            if ($request->filled('plateNumber')) {
                $data = $data->wherePlateNumberLike($request->plateNumber);
            }

            if ($request->filled('driverName')) {
                $data = $data->whereDriverNameLike($request->driverName);
            }

            $filters = [
                'customer_name' => $request->customerName,
                'fleetType_name' => $request->fleetTypeName,
                'shipmentNumber' => $request->shipmentNumber,
                'destination' => $request->destination,
                'orderTypeCode' => $request->orderTypeCode,
            ];

            $relations = [
                'customer_name' => 'customer.name',
                'fleetType_name' => 'fleet.type.name',
                'destination' => 'route.destinationLocation.name',
            ];

            $dateFilters = [
                'orderDate' => [
                    'start' => $request->startDate,
                    'end' => $request->endDate,
                ],
            ];

            $data = FilterHelper::applyFilters($data, $filters, $relations, $dateFilters);

            $tonaseBonuses = TonaseBonus::all();
            $rowBreakdown = [];
            $getBreakdown = function ($row) use (&$rowBreakdown, $tonaseBonuses): array {
                $key = (string) ($row->id ?? $row->code);
                if (! isset($rowBreakdown[$key])) {
                    $allowance = $this->service->calculateOrderAllowance($row);

                    $orderQty = (float) ($row->qty ?? 0);
                    $matchedBonus = $tonaseBonuses->first(
                        fn ($item) => (float) $item->min <= $orderQty && (float) $item->max >= $orderQty
                    );
                    $bonus = (float) ($matchedBonus->value ?? 0);

                    $addCost = 0.0;
                    foreach ($row->cost ?? [] as $item) {
                        $addCost += (float) ($item->nominal ?? 0);
                    }

                    $rowBreakdown[$key] = [
                        'allowance' => $allowance,
                        'bonus' => $bonus,
                        'addCost' => $addCost,
                        'totalPrice' => $allowance + $bonus + $addCost,
                    ];
                }

                return $rowBreakdown[$key];
            };

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('fleet.plateNumber', function ($row) {
                    return $row->plateNumber ?: ($row->fleet?->plateNumber ?? '');
                })
                ->editColumn('customer.name', function ($row) {
                    return $row->customer?->name ?? '';
                })
                ->editColumn('driver.name', function ($row) {
                    return $row->driverName ?: ($row->driver?->name ?? '');
                })
                ->editColumn('fleet.type.name', function ($row) {
                    return $row->fleet?->type?->name ?? '';
                })
                ->editColumn('material.name', function ($row) {
                    return $row->material?->name ?? '';
                })
                ->editColumn('route.destinationLocation.name', function ($row) {
                    return $row->route?->destinationLocation?->name ?? '';
                })
                ->addColumn('cost', function ($row) use ($getBreakdown) {
                    return number_format($getBreakdown($row)['allowance'], 0, ',', '.');
                })
                ->addColumn('tonase', function ($row) {
                    if ($row->route?->routeTypeCode === 'TONASE') {
                        return number_format((float) ($row->route?->price ?? 0), 0, ',', '.');
                    }

                    return '0';
                })
                ->addColumn('bonus', function ($row) use ($getBreakdown) {
                    return number_format($getBreakdown($row)['bonus'], 0, ',', '.');
                })
                ->addColumn('addCost', function ($row) use ($getBreakdown) {
                    return number_format($getBreakdown($row)['addCost'], 0, ',', '.');
                })
                ->addColumn('totalPrice', function ($row) use ($getBreakdown) {
                    return number_format($getBreakdown($row)['totalPrice'], 0, ',', '.');
                })
                ->editColumn('orderDate', function ($row) {
                    return $row->orderDate ? Carbon::parse($row->orderDate)->format('d-m-Y') : '-';
                })
                ->rawColumns(['fleet.type.name', 'fleet.plateNumber', 'customer.name', 'route.destinationLocation.name', 'material.name', 'driver.name', 'cost', 'bonus', 'tonase', 'addCost', 'totalPrice'])
                ->toJson();
        }
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $baseQuery = Fleet::query()
                ->select(['id', 'code', 'plateNumber', 'fleetCompanyCode'])
                ->when($request->filled('plateNumber'), function ($query) use ($request) {
                    $query->where('plateNumber', $request->plateNumber);
                })
                ->when($request->filled('fleetCompanyCode'), function ($query) use ($request) {
                    $query->where('fleetCompanyCode', $request->fleetCompanyCode);
                });

            $searchKeyword = trim((string) $request->input('search.value', ''));
            $summaryQuery = clone $baseQuery;
            if ($searchKeyword !== '') {
                $summaryQuery->where(function ($query) use ($searchKeyword) {
                    $query->where('plateNumber', 'like', "%{$searchKeyword}%")
                        ->orWhereHas('company', function ($companyQuery) use ($searchKeyword) {
                            $companyQuery->where('name', 'like', "%{$searchKeyword}%");
                        });
                });
            }

            $reportData = $this->service->calculatePerFleetSummaries(
                $summaryQuery,
                $request->startDate,
                $request->endDate
            );
            $perFleetSummaries = $reportData['perFleet'];
            $summaryTotals = $reportData['totals'];

            $emptySummary = [
                'basicSales' => 0.0,
                'basicAllowance' => 0.0,
                'additionalCost' => 0.0,
                'tonase' => 0.0,
                'orderCost' => 0.0,
                'maintenance' => 0.0,
                'tax' => 0.0,
                'netProfit' => 0.0,
            ];

            $getSummary = fn (Fleet $row): array => $perFleetSummaries[$row->code] ?? $emptySummary;

            $data = (clone $baseQuery)->with('company:id,code,name');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('company_name', function ($row) {
                    return $row->company?->name ?? '-';
                })
                ->filterColumn('company_name', function ($query, $keyword) {
                    $query->whereHas('company', function ($companyQuery) use ($keyword) {
                        $companyQuery->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('basic_sales', function ($row) use ($getSummary) {
                    return number_format($getSummary($row)['basicSales'], 0, ',', '.');
                })
                ->addColumn('basic_allowance', function ($row) use ($getSummary) {
                    return number_format($getSummary($row)['basicAllowance'], 0, ',', '.');
                })
                ->addColumn('additional_cost', function ($row) use ($getSummary) {
                    return number_format($getSummary($row)['additionalCost'], 0, ',', '.');
                })
                ->addColumn('tonase', function ($row) use ($getSummary) {
                    return number_format($getSummary($row)['tonase'], 0, ',', '.');
                })
                ->addColumn('order_cost', function ($row) use ($getSummary) {
                    return number_format($getSummary($row)['orderCost'], 0, ',', '.');
                })
                ->addColumn('maintenance', function ($row) use ($getSummary) {
                    return number_format($getSummary($row)['maintenance'], 0, ',', '.');
                })
                ->addColumn('tax', function ($row) use ($getSummary) {
                    return number_format($getSummary($row)['tax'], 0, ',', '.');
                })
                ->addColumn('net_profit', function ($row) use ($getSummary) {
                    return number_format($getSummary($row)['netProfit'], 0, ',', '.');
                })
                ->addColumn('total_margin', function ($row) use ($getSummary) {
                    return number_format($getSummary($row)['netProfit'], 0, ',', '.');
                })
                ->addColumn('action', function ($row) {
                    return '<td>
                                    <a href="'.route($this->view.'show', $row->id).'"
                                    class="btn btn-icon btn-sm bg-primary-subtle me-1"
                                    data-bs-toggle="tooltip" title="Lihat Detail">
                                        <i class="mdi mdi-eye fs-14 text-primary"></i>
                                    </a>
                                </td>';
                })
                ->only([
                    'DT_RowIndex',
                    'plateNumber',
                    'company_name',
                    'basic_sales',
                    'basic_allowance',
                    'additional_cost',
                    'tonase',
                    'order_cost',
                    'maintenance',
                    'tax',
                    'net_profit',
                    'total_margin',
                    'action',
                ])
                ->with(['summary' => $summaryTotals])
                ->rawColumns(['action'])
                ->toJson();
        }
    }

    public function excelProfitLoss(Request $request)
    {
        return Excel::download(new ProfitLossReport($request), 'Laporan-Laba-Rugi.xlsx');
    }

    public function pdfProfitLoss(Request $request)
    {
        $startDate = $request->startDate ?: null;
        $endDate = $request->endDate ?: null;
        $plateNumber = $request->plateNumber ?: null;
        $fleetCompanyCode = $request->fleetCompanyCode ?: null;

        $exportData = $this->service->buildFleetExportData($plateNumber, $fleetCompanyCode, $startDate, $endDate);

        $filterSummary = [];
        if ($plateNumber) {
            $filterSummary[] = 'Plat Nomor: '.$plateNumber;
        }
        if ($fleetCompanyCode) {
            $companyName = FleetCompany::where('code', $fleetCompanyCode)->value('name');
            $filterSummary[] = 'Perusahaan: '.($companyName ?: $fleetCompanyCode);
        }
        if ($startDate || $endDate) {
            $start = $startDate ? Carbon::parse($startDate)->format('d-m-Y') : 'Awal';
            $end = $endDate ? Carbon::parse($endDate)->format('d-m-Y') : 'Sekarang';
            $filterSummary[] = 'Periode: '.$start.' s/d '.$end;
        }

        return $this->renderPdfResponse(
            $this->view.'report.profit-loss-pdf',
            [
                'rows' => $exportData['rows'],
                'grandTotals' => $exportData['grandTotals'],
                'title' => 'Laporan Laba Rugi Per Armada',
                'filterSummary' => $filterSummary,
                'printedAt' => now(),
            ],
            'Laporan-Laba-Rugi-'.now()->format('YmdHis').'.pdf',
            'L'
        );
    }

    public function getProfitLossSummary(Request $request)
    {
        $fleetCode = $request->fleetCode;
        $startDate = $request->startDate;
        $endDate = $request->endDate;

        $data = Fleet::with($this->service->fleetReportRelations($startDate, $endDate))
            ->where('code', $fleetCode)
            ->firstOrFail();

        $summary = $this->service->calculateFleetSummary($data);

        return response()->json([
            'basicSales' => $summary['basicSales'],
            'orderCost' => $summary['orderCost'],
            'maintenance' => $summary['maintenance'],
            'tax' => $summary['tax'],
            'netProfit' => $summary['netProfit'],
            'totalMargin' => $summary['netProfit'],
        ]);
    }
}
