<?php

namespace App\Http\Controllers\Report;

use App\Exports\OrderDetailReport;
use App\Http\Controllers\Controller;
use App\Models\Operational\Order;
use App\Services\Master\CustomerService;
use App\Services\Master\EmployeeService;
use App\Services\Master\FleetService;
use App\Services\Master\FleetTypeService;
use App\Services\Master\LocationService;
use App\Services\Master\OrderTypeService;
use App\Services\Report\OrderDetailReportService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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

    protected OrderDetailReportService $reportService;

    public function __construct(
        CustomerService $customerSvc,
        OrderTypeService $orderTypeSvc,
        FleetTypeService $fleetTypeSvc,
        EmployeeService $driverSvc,
        FleetService $fleetSvc,
        LocationService $locationSvc,
        OrderDetailReportService $reportService
    ) {
        $this->title = 'Order Detail Report';
        $this->view = 'report.order-detail.';
        $this->customerSvc = $customerSvc;
        $this->orderTypeSvc = $orderTypeSvc;
        $this->fleetTypeSvc = $fleetTypeSvc;
        $this->driverSvc = $driverSvc;
        $this->fleetSvc = $fleetSvc;
        $this->locationSvc = $locationSvc;
        $this->reportService = $reportService;
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
            $data = $this->reportService->buildFilteredQuery($request);
            $rowCache = [];
            $invoiceWeightCache = [];

            $getBreakdown = function (Order $row) use (&$rowCache, &$invoiceWeightCache): array {
                $cacheKey = (string) ($row->code ?: spl_object_id($row));
                if (! array_key_exists($cacheKey, $rowCache)) {
                    $rowCache[$cacheKey] = $this->reportService->calculateOrderBreakdown($row, $invoiceWeightCache);
                }

                return $rowCache[$cacheKey];
            };

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('orderDate', function ($row) {
                    return $row->orderDate ? Carbon::parse($row->orderDate)->format('d-m-Y') : '';
                })
                ->filterColumn('orderDate', function (Builder $query, string $keyword) {
                    $this->filterOrderDateColumn($query, $keyword);
                })
                ->editColumn('shipmentNumber', function ($row) {
                    return '<strong class="text-primary font-weight-bold">'.e(mb_strtoupper($row->shipmentNumber ?? '')).'</strong>';
                })
                ->addColumn('customer_name', function ($row) {
                    return $row->customer?->name ?? '';
                })
                ->filterColumn('customer_name', function ($query, $keyword) {
                    $query->whereHas('customer', function ($customerQuery) use ($keyword) {
                        $customerQuery->where('name', 'like', '%'.$keyword.'%');
                    });
                })
                ->addColumn('origin_name', function ($row) {
                    return $row->route?->originLocation?->name ?? '';
                })
                ->filterColumn('origin_name', function ($query, $keyword) {
                    $query->whereHas('route.originLocation', function ($locationQuery) use ($keyword) {
                        $locationQuery->where('name', 'like', '%'.$keyword.'%');
                    });
                })
                ->addColumn('destination_name', function ($row) {
                    return $row->route?->destinationLocation?->name ?? '';
                })
                ->filterColumn('destination_name', function ($query, $keyword) {
                    $query->whereHas('route.destinationLocation', function ($locationQuery) use ($keyword) {
                        $locationQuery->where('name', 'like', '%'.$keyword.'%');
                    });
                })
                ->addColumn('plate_number', function ($row) {
                    $plate = e(strtoupper($row->plateNumber ?: ($row->fleet?->plateNumber ?? '')));

                    return $plate ? '<span class="badge bg-light text-dark border font-weight-bold px-2 py-1">'.$plate.'</span>' : '';
                })
                ->filterColumn('plate_number', function ($query, $keyword) {
                    $query->where(function ($plateQuery) use ($keyword) {
                        $plateQuery->where('order.plateNumber', 'like', '%'.$keyword.'%')
                            ->orWhereHas('fleet', function ($fleetQuery) use ($keyword) {
                                $fleetQuery->where('plateNumber', 'like', '%'.$keyword.'%');
                            });
                    });
                })
                ->addColumn('driver_name', function ($row) {
                    return $row->driverName ?: ($row->driver?->name ?? '');
                })
                ->filterColumn('driver_name', function ($query, $keyword) {
                    $query->where(function ($driverQuery) use ($keyword) {
                        $driverQuery->where('order.driverName', 'like', '%'.$keyword.'%')
                            ->orWhereHas('driver', function ($driverQ) use ($keyword) {
                                $driverQ->where('name', 'like', '%'.$keyword.'%');
                            });
                    });
                })
                ->addColumn('qty', function ($row) use ($getBreakdown) {
                    $qty = $getBreakdown($row)['qty'];
                    $formatted = rtrim(rtrim(number_format($qty, 2, ',', '.'), '0'), ',');

                    return $formatted !== '' ? $formatted : '0';
                })
                ->filterColumn('qty', function (Builder $query, string $keyword) {
                    $digits = $this->extractSearchDigits($keyword);
                    if ($digits === '') {
                        $query->whereRaw('1 = 0');

                        return;
                    }

                    $normalized = str_replace(['.', ','], ['', '.'], trim($keyword));
                    $query->where(function (Builder $q) use ($normalized, $digits) {
                        if (is_numeric($normalized)) {
                            $q->where('order.qty', (float) $normalized);
                        }
                        $q->orWhereRaw('CAST('.$q->getGrammar()->wrap('order.qty').' AS CHAR) LIKE ?', ['%'.$digits.'%']);
                    });
                })
                ->addColumn('unit_price', function ($row) use ($getBreakdown) {
                    $unitPrice = $getBreakdown($row)['unit_price'];

                    return '<span class="fw-semibold">Rp '.number_format($unitPrice, 0, ',', '.').'</span>';
                })
                ->filterColumn('unit_price', function (Builder $query, string $keyword) {
                    $this->filterNumericColumns($query, ['order.price', 'order.vendorPriceSingle', 'order.personalVendorPriceSingle'], $keyword);
                })
                ->addColumn('sales', function ($row) use ($getBreakdown) {
                    $breakdown = $getBreakdown($row);
                    $badge = $breakdown['is_cross_internal']
                        ? ' <span class="badge bg-info bg-opacity-10 text-info ms-1" title="Menggunakan harga vendor (Antar PT)">Antar PT</span>'
                        : '';

                    return '<span class="fw-bold">Rp '.number_format($breakdown['omset'], 0, ',', '.').'</span>'.$badge;
                })
                ->filterColumn('sales', function (Builder $query, string $keyword) {
                    $digits = $this->extractSearchDigits($keyword);
                    if ($digits !== '') {
                        $this->filterNumericColumns($query, ['order.routeAmount', 'order.vendorPrice', 'order.personalVendorPrice'], $keyword);

                        return;
                    }

                    if (str_contains(mb_strtolower(trim($keyword)), 'antar')) {
                        $this->applyCrossInternalCondition($query);

                        return;
                    }

                    $query->whereRaw('1 = 0');
                })
                ->addColumn('income', function ($row) use ($getBreakdown) {
                    $breakdown = $getBreakdown($row);

                    return $this->renderCostBreakdownHtml($breakdown['income_details'], $breakdown['income_total'], 'success', true);
                })
                ->filterColumn('income', function (Builder $query, string $keyword) {
                    $this->filterCostCategoryColumn($query, 'income', $keyword);
                })
                ->addColumn('commission', function ($row) use ($getBreakdown) {
                    $breakdown = $getBreakdown($row);

                    return $this->renderCostBreakdownHtml($breakdown['commission_details'], $breakdown['commission_total'], 'info', true);
                })
                ->filterColumn('commission', function (Builder $query, string $keyword) {
                    $this->filterCostCategoryColumn($query, 'commission', $keyword);
                })
                ->addColumn('salary', function ($row) use ($getBreakdown) {
                    $breakdown = $getBreakdown($row);

                    return $this->renderCostBreakdownHtml($breakdown['salary_details'], $breakdown['salary_total'], 'primary', true);
                })
                ->filterColumn('salary', function (Builder $query, string $keyword) {
                    $this->filterCostCategoryColumn($query, 'salary', $keyword);
                })
                ->addColumn('cost_detail', function ($row) use ($getBreakdown) {
                    $breakdown = $getBreakdown($row);

                    return $this->renderCostBreakdownHtml($breakdown['other_cost_details'], $breakdown['other_cost_total'], 'warning', false);
                })
                ->filterColumn('cost_detail', function (Builder $query, string $keyword) {
                    $this->filterCostCategoryColumn($query, 'other', $keyword);
                })
                ->addColumn('total_cost', function ($row) use ($getBreakdown) {
                    $totalCost = $getBreakdown($row)['total_cost'];

                    return '<span class="fw-bold text-danger">Rp '.number_format($totalCost, 0, ',', '.').'</span>';
                })
                ->filterColumn('total_cost', function (Builder $query, string $keyword) {
                    $digits = $this->extractSearchDigits($keyword);
                    if ($digits === '') {
                        $query->whereRaw('1 = 0');

                        return;
                    }

                    $query->where(function (Builder $q) use ($digits) {
                        $q->whereRaw(
                            '(SELECT COALESCE(SUM(oc.nominal), 0) FROM order_cost oc WHERE oc.orderCode = `order`.code AND LOWER(TRIM(COALESCE(oc.type, \'\'))) != ?) LIKE ?',
                            ['on charge', '%'.$digits.'%']
                        )->orWhereHas('cost', fn (Builder $cq) => $cq
                            ->whereRaw("LOWER(TRIM(COALESCE(type, ''))) != ?", ['on charge'])
                            ->whereRaw('CAST(nominal AS CHAR) LIKE ?', ['%'.$digits.'%']));
                    });
                })
                ->addColumn('gross_profit', function ($row) use ($getBreakdown) {
                    $gp = $getBreakdown($row)['gross_profit'];
                    $color = $gp >= 0 ? 'success' : 'danger';

                    return '<span class="badge bg-'.$color.' bg-opacity-10 text-'.$color.' fw-bold px-2.5 py-1.5 fs-12">Rp '.number_format($gp, 0, ',', '.').'</span>';
                })
                ->filterColumn('gross_profit', function (Builder $query, string $keyword) {
                    $this->filterProfitColumn($query, $keyword);
                })
                ->addColumn('tax', function ($row) use ($getBreakdown) {
                    $tax = $getBreakdown($row)['tax'];
                    if ($tax <= 0) {
                        return '<span class="text-muted small">-</span>';
                    }

                    return '<span class="fw-semibold text-warning">Rp '.number_format($tax, 0, ',', '.').'</span>';
                })
                ->filterColumn('tax', function (Builder $query, string $keyword) {
                    $digits = $this->extractSearchDigits($keyword);
                    if ($digits === '') {
                        $query->whereRaw('1 = 0');

                        return;
                    }

                    $query->where(function (Builder $q) use ($digits) {
                        $q->whereHas('invoiceDetail.invoice', fn (Builder $iq) => $iq->whereRaw('CAST(pphAmount AS CHAR) LIKE ?', ['%'.$digits.'%']))
                            ->orWhereHas('orderPayment', fn (Builder $pq) => $pq->whereRaw('CAST(pph AS CHAR) LIKE ?', ['%'.$digits.'%']));
                    });
                })
                ->addColumn('profit', function ($row) use ($getBreakdown) {
                    $np = $getBreakdown($row)['net_profit'];
                    $color = $np >= 0 ? 'success' : 'danger';

                    return '<span class="badge bg-'.$color.' text-white fw-bold px-2.5 py-1.5 fs-12">Rp '.number_format($np, 0, ',', '.').'</span>';
                })
                ->filterColumn('profit', function (Builder $query, string $keyword) {
                    $this->filterProfitColumn($query, $keyword);
                })
                ->rawColumns(['shipmentNumber', 'plate_number', 'unit_price', 'sales', 'income', 'commission', 'salary', 'cost_detail', 'total_cost', 'gross_profit', 'tax', 'profit'])
                ->toJson();
        }
    }

    protected function renderCostBreakdownHtml(array $details, float $total, string $badgeColor, bool $showTotal = true): string
    {
        if (empty($details)) {
            return '<span class="text-muted small">-</span>';
        }

        $html = '<div class="cost-detail-list" style="font-size: 12px;">';
        foreach ($details as $key => $detail) {
            $html .= '<div class="cost-detail-item text-muted"><span class="badge bg-'.$badgeColor.' bg-opacity-10 text-'.$badgeColor.' me-1">'.($key + 1).'</span>'
                .e($detail['name']).': Rp '.number_format($detail['nominal'], 0, ',', '.').'</div>';
        }
        if ($showTotal) {
            $html .= '<div class="cost-detail-item text-'.$badgeColor.' font-weight-bold mt-1 pt-1 border-top border-dashed">Total: Rp '.number_format($total, 0, ',', '.').'</div>';
        }
        $html .= '</div>';

        return $html;
    }

    protected function extractSearchDigits(string $keyword): string
    {
        return preg_replace('/\D/', '', $keyword) ?? '';
    }

    protected function filterOrderDateColumn(Builder $query, string $keyword): void
    {
        $keyword = trim($keyword);
        $query->where(function (Builder $q) use ($keyword) {
            $q->where('order.orderDate', 'like', '%'.$keyword.'%');

            if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $keyword, $m) === 1) {
                $q->orWhereDate('order.orderDate', $m[3].'-'.$m[2].'-'.$m[1]);
            } elseif (preg_match('/^(\d{2})-(\d{4})$/', $keyword, $m) === 1) {
                $q->orWhere(fn (Builder $sub) => $sub
                    ->whereMonth('order.orderDate', (int) $m[1])
                    ->whereYear('order.orderDate', (int) $m[2]));
            } elseif (preg_match('/^(\d{2})-(\d{2})$/', $keyword, $m) === 1) {
                $q->orWhere(fn (Builder $sub) => $sub
                    ->whereDay('order.orderDate', (int) $m[1])
                    ->whereMonth('order.orderDate', (int) $m[2]));
            } elseif (preg_match('/^\d{4}$/', $keyword) === 1) {
                $q->orWhereYear('order.orderDate', (int) $keyword);
            }
        });
    }

    protected function filterNumericColumns(Builder $query, array $columns, string $keyword): void
    {
        $digits = $this->extractSearchDigits($keyword);
        if ($digits === '') {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where(function (Builder $q) use ($columns, $digits) {
            $grammar = $q->getGrammar();
            foreach ($columns as $index => $column) {
                $wrapped = $grammar->wrap($column);
                if ($index === 0) {
                    $q->whereRaw("CAST({$wrapped} AS CHAR) LIKE ?", ['%'.$digits.'%']);
                } else {
                    $q->orWhereRaw("CAST({$wrapped} AS CHAR) LIKE ?", ['%'.$digits.'%']);
                }
            }
        });
    }

    protected function filterProfitColumn(Builder $query, string $keyword): void
    {
        $digits = $this->extractSearchDigits($keyword);
        if ($digits === '') {
            $query->whereRaw('1 = 0');

            return;
        }

        $grossSql = '(COALESCE(`order`.routeAmount, 0) + '
            .'COALESCE((SELECT SUM(oc1.nominal) FROM order_cost oc1 WHERE oc1.orderCode = `order`.code AND LOWER(TRIM(COALESCE(oc1.type, \'\'))) = \'on charge\'), 0) - '
            .'COALESCE((SELECT SUM(oc2.nominal) FROM order_cost oc2 WHERE oc2.orderCode = `order`.code AND LOWER(TRIM(COALESCE(oc2.type, \'\'))) != \'on charge\'), 0))';

        $query->where(function (Builder $q) use ($digits, $grossSql) {
            $q->whereRaw('CAST('.$grossSql.' AS CHAR) LIKE ?', ['%'.$digits.'%'])
                ->orWhereRaw('CAST(`order`.routeAmount AS CHAR) LIKE ?', ['%'.$digits.'%'])
                ->orWhereRaw('CAST(`order`.vendorPrice AS CHAR) LIKE ?', ['%'.$digits.'%'])
                ->orWhereRaw('CAST(`order`.personalVendorPrice AS CHAR) LIKE ?', ['%'.$digits.'%']);
        });
    }

    protected function filterCostCategoryColumn(Builder $query, string $category, string $keyword): void
    {
        $digits = $this->extractSearchDigits($keyword);

        $query->whereHas('cost', function (Builder $costQuery) use ($category, $keyword, $digits) {
            if ($category === 'income') {
                $costQuery->whereRaw("LOWER(TRIM(COALESCE(type, ''))) = ?", ['on charge']);
            } else {
                $costQuery->whereRaw("LOWER(TRIM(COALESCE(type, ''))) != ?", ['on charge']);

                if ($category === 'commission') {
                    $costQuery->where(function (Builder $catQuery) {
                        $catQuery->whereHas('costComponent', fn (Builder $cc) => $cc->whereRaw("LOWER(TRIM(COALESCE(type, ''))) = ?", ['commission']))
                            ->orWhereRaw("LOWER(COALESCE(componentName, '')) LIKE ?", ['%komisi%'])
                            ->orWhereHas('costComponent', fn (Builder $cc) => $cc->whereRaw("LOWER(COALESCE(name, '')) LIKE ?", ['%komisi%']));
                    });
                } elseif ($category === 'salary') {
                    $costQuery->where(function (Builder $catQuery) {
                        $catQuery->whereHas('costComponent', fn (Builder $cc) => $cc->whereRaw("LOWER(TRIM(COALESCE(type, ''))) = ?", ['salary']))
                            ->orWhereRaw("LOWER(COALESCE(componentName, '')) LIKE ?", ['%gaji%'])
                            ->orWhereHas('costComponent', fn (Builder $cc) => $cc->whereRaw("LOWER(COALESCE(name, '')) LIKE ?", ['%gaji%']));
                    })
                        ->whereRaw("LOWER(COALESCE(componentName, '')) NOT LIKE ?", ['%komisi%'])
                        ->whereDoesntHave('costComponent', fn (Builder $cc) => $cc
                            ->whereRaw("LOWER(TRIM(COALESCE(type, ''))) = ?", ['commission'])
                            ->orWhereRaw("LOWER(COALESCE(name, '')) LIKE ?", ['%komisi%']));
                } else {
                    $costQuery->whereRaw("LOWER(COALESCE(componentName, '')) NOT LIKE ?", ['%komisi%'])
                        ->whereRaw("LOWER(COALESCE(componentName, '')) NOT LIKE ?", ['%gaji%'])
                        ->whereDoesntHave('costComponent', fn (Builder $cc) => $cc
                            ->whereRaw("LOWER(TRIM(COALESCE(type, ''))) IN ('commission', 'salary')")
                            ->orWhereRaw("LOWER(COALESCE(name, '')) LIKE ?", ['%komisi%'])
                            ->orWhereRaw("LOWER(COALESCE(name, '')) LIKE ?", ['%gaji%']));
                }
            }

            $costQuery->where(function (Builder $matchQuery) use ($keyword, $digits) {
                $matchQuery->where('componentName', 'like', '%'.$keyword.'%')
                    ->orWhereHas('costComponent', fn (Builder $cc) => $cc->where('name', 'like', '%'.$keyword.'%'));

                if ($digits !== '') {
                    $matchQuery->orWhereRaw('CAST(nominal AS CHAR) LIKE ?', ['%'.$digits.'%']);
                }
            });
        });
    }

    protected function applyCrossInternalCondition(Builder $query): void
    {
        $query->whereExists(function ($sub) {
            $sub->selectRaw('1')
                ->from('fleet')
                ->join('fleet_company', 'fleet_company.code', '=', 'fleet.fleetCompanyCode')
                ->join('customer', 'customer.code', '=', 'order.customerCode')
                ->whereColumn('fleet.code', 'order.fleetCode')
                ->whereNull('fleet.deleted_at')
                ->whereNull('fleet_company.deleted_at')
                ->whereNull('customer.deleted_at')
                ->whereRaw('LOWER(TRIM(fleet_company.type)) = ?', ['internal'])
                ->whereNotNull('fleet_company.companyCode')
                ->whereNotNull('customer.companyCode')
                ->whereRaw("TRIM(fleet_company.companyCode) != ''")
                ->whereRaw("TRIM(customer.companyCode) != ''")
                ->whereRaw('TRIM(fleet_company.companyCode) != TRIM(customer.companyCode)');
        });
    }

    private function getFilteredOrdersQuery(Request $request)
    {
        return $this->reportService->buildFilteredQuery($request);
    }

    public function excelOrderDetail(Request $request)
    {
        return Excel::download(new OrderDetailReport($request, $this->reportService), 'Order-Detail-Report.xlsx');
    }

    public function excelOrderDetailInit(Request $request)
    {
        $query = $this->reportService->buildFilteredQuery($request);
        $total = $query->count();
        $downloadId = 'dl_'.uniqid().'_'.time();

        Cache::put('excel_export_orders_'.$downloadId, [], 60);

        return response()->json([
            'download_id' => $downloadId,
            'total' => $total,
        ]);
    }

    public function excelOrderDetailChunk(Request $request)
    {
        $downloadId = $request->download_id;
        $offset = (int) $request->offset;
        $limit = (int) $request->limit;

        if (! $downloadId) {
            return response()->json(['error' => 'Missing download ID'], 400);
        }

        $orders = $this->reportService->buildFilteredQuery($request)
            ->offset($offset)
            ->limit($limit)
            ->get();

        $preparedRows = $this->reportService->prepareExportRows($orders);

        $key = 'excel_export_orders_'.$downloadId;
        $existing = Cache::get($key, []);
        $merged = array_merge($existing, $preparedRows);

        Cache::put($key, $merged, 60);

        return response()->json(['success' => true]);
    }

    public function excelOrderDetailDownload(Request $request)
    {
        $downloadId = $request->download_id;
        if (! $downloadId) {
            abort(400, 'Missing download ID');
        }

        $key = 'excel_export_orders_'.$downloadId;
        $rows = Cache::get($key);

        if ($rows === null) {
            abort(404, 'Export data expired or not found');
        }

        Cache::forget($key);

        $response = Excel::download(new OrderDetailReport($rows, $this->reportService), 'Order-Detail-Report.xlsx');
        if ($request->has('download_token')) {
            $cookie = cookie('download_token', $request->download_token, 5, null, null, false, false);
            $response->headers->setCookie($cookie);
        }

        return $response;
    }

    public function pdfOrderDetail(Request $request)
    {
        $orders = $this->reportService->buildFilteredQuery($request)->get();
        $preparedRows = $this->reportService->prepareExportRows($orders);
        $totals = $this->reportService->computeGrandTotals($preparedRows);

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
        $chunks = array_chunk($preparedRows, $chunkSize);

        $start = 0;
        foreach ($chunks as $chunk) {
            $rowHtml = View::make($this->view.'report.order-detail-pdf-rows')
                ->with('data', $chunk)
                ->with('start', $start)
                ->render();
            $mpdf->WriteHTML($rowHtml);
            $start += count($chunk);
        }

        // Write footer
        $footerHtml = View::make($this->view.'report.order-detail-pdf-footer')
            ->with('totals', $totals)
            ->render();
        $mpdf->WriteHTML($footerHtml);

        return response($mpdf->Output('Order-Detail-Report.pdf', 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Order-Detail-Report.pdf"',
        ]);
    }
}
