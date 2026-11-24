<?php

namespace App\Http\Controllers\Report;

use App\Exports\OmsetReportExport;
use App\Http\Controllers\Controller;
use App\Models\Master\Company;
use App\Models\Master\Customer;
use App\Models\Master\Employee;
use App\Models\Master\Fleet;
use App\Models\Master\Location;
use App\Models\Operational\OrderStatus;
use App\Services\Report\OmsetReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;
use Yajra\DataTables\DataTables;

class OmsetReportController extends Controller
{
    protected string $title;

    protected string $view;

    protected OmsetReportService $service;

    public function __construct(OmsetReportService $service)
    {
        $this->title = 'Laporan Omset';
        $this->view = 'report.omset.';
        $this->service = $service;
    }

    public function index()
    {
        $customers = Customer::whereNull('deleted_at')->orderBy('name')->get();
        $companies = Company::whereNull('deleted_at')->orderBy('name')->get();
        $fleets = Fleet::whereNull('deleted_at')->orderBy('plateNumber')->get();
        $drivers = Employee::whereNull('deleted_at')->orderBy('name')->get();
        $locations = Location::whereNull('deleted_at')->orderBy('name')->get();
        $orderStatuses = OrderStatus::orderBy('code')->get();

        return view($this->view . 'index')
            ->with('title', $this->title)
            ->with('view', $this->view)
            ->with('customers', $customers)
            ->with('companies', $companies)
            ->with('fleets', $fleets)
            ->with('drivers', $drivers)
            ->with('locations', $locations)
            ->with('orderStatuses', $orderStatuses);
    }

    public function datatable(Request $request)
    {
        if (! $request->ajax()) {
            abort(404);
        }

        $query = $this->service->buildQuery($request)->orderByDesc('o.orderDate')->orderByDesc('o.code');
        $totals = $this->service->computeTotals($request);

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('orderDate', function ($row) {
                return $row->orderDate ? Carbon::parse($row->orderDate)->format('d-m-Y') : '-';
            })
            ->addColumn('orderNumber', function ($row) {
                $ship = ($row->shipmentNumber && $row->shipmentNumber !== '-') ? $row->shipmentNumber : $row->orderCode;
                $sub = ($row->shipmentNumber && $row->shipmentNumber !== '-' && $row->orderCode)
                    ? '<div class="text-muted" style="font-size:11px;">' . e($row->orderCode) . '</div>'
                    : '';

                return '<div class="fw-bold text-primary" style="font-size:12.5px;">' . e($ship) . '</div>' . $sub;
            })
            ->addColumn('customerCompany', function ($row) {
                $catBadge = $row->fleetCategory === 'External'
                    ? '<span class="badge bg-warning-subtle text-warning ms-1" style="font-size:10px;">External</span>'
                    : '<span class="badge bg-primary-subtle text-primary ms-1" style="font-size:10px;">Internal</span>';

                return '<div class="fw-semibold" style="font-size:12.5px;">' . e($row->customerName) . '</div>'
                    . '<div class="text-muted" style="font-size:11px;">' . e($row->companyName) . $catBadge . '</div>';
            })
            ->addColumn('fleetDriver', function ($row) {
                return '<span class="badge bg-light text-dark border fw-semibold" style="font-size:11px;">' . e($row->plateNumber) . '</span>'
                    . '<div class="text-muted mt-1" style="font-size:11.5px;">' . e($row->driverName) . '</div>';
            })
            ->addColumn('routeInfo', function ($row) {
                $sub = ($row->originName || $row->destinationName)
                    ? '<div class="text-muted" style="font-size:11px;">' . e($row->originName) . ' &rarr; ' . e($row->destinationName) . '</div>'
                    : '';

                return '<div class="fw-semibold" style="font-size:12px;">' . e($row->routeName) . '</div>' . $sub;
            })
            ->addColumn('statusBadges', function ($row) {
                $orderBadge = '<span class="badge bg-light text-dark border" style="font-size:10.5px;">' . e($row->orderStatusName) . '</span>';

                if ($row->paymentStatus === 'paid') {
                    $payBadge = '<span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:10.5px;">Lunas</span>';
                } elseif ($row->paymentStatus === 'partial') {
                    $payBadge = '<span class="badge bg-info-subtle text-info border border-info-subtle" style="font-size:10.5px;">DP / Sebagian</span>';
                } else {
                    $payBadge = '<span class="badge bg-warning-subtle text-warning border border-warning-subtle" style="font-size:10.5px;">Belum Dibayar</span>';
                }

                return '<div class="d-flex flex-column gap-1 align-items-center">' . $orderBadge . $payBadge . '</div>';
            })
            ->addColumn('omsetFormatted', function ($row) {
                return '<span class="fw-bold text-primary font-monospace" style="font-size:12.5px;">Rp ' . number_format((float) $row->omset, 0, ',', '.') . '</span>';
            })
            ->addColumn('biayaFormatted', function ($row) {
                return '<span class="fw-semibold text-danger font-monospace" style="font-size:12.5px;">Rp ' . number_format((float) $row->biaya, 0, ',', '.') . '</span>';
            })
            ->addColumn('profitFormatted', function ($row) {
                $val = (float) $row->profit;
                $cls = $val >= 0 ? 'text-success' : 'text-danger';
                $prefix = $val < 0 ? '-Rp ' : 'Rp ';

                return '<span class="fw-bold ' . $cls . ' font-monospace" style="font-size:12.5px;">' . $prefix . number_format(abs($val), 0, ',', '.') . '</span>';
            })
            ->rawColumns(['orderNumber', 'customerCompany', 'fleetDriver', 'routeInfo', 'statusBadges', 'omsetFormatted', 'biayaFormatted', 'profitFormatted'])
            ->with(['totals' => $totals])
            ->toJson();
    }

    public function exportExcel(Request $request)
    {
        $filename = 'Laporan_Omset_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new OmsetReportExport($request, $this->service), $filename);
    }

    public function exportPdf(Request $request)
    {
        $rows = $this->service->buildQuery($request)
            ->orderByDesc('o.orderDate')
            ->orderByDesc('o.code')
            ->get();

        $totals = $this->service->computeTotals($request);

        $periodLabel = ($request->filled('startDate') ? Carbon::parse($request->startDate)->format('d-m-Y') : 'Awal')
            . ' s/d '
            . ($request->filled('endDate') ? Carbon::parse($request->endDate)->format('d-m-Y') : 'Sekarang');

        $mpdf = new Mpdf([
            'orientation' => 'L',
            'format' => 'A4',
            'tempDir' => storage_path('app/mpdf-temp'),
        ]);
        $mpdf->setAutoTopMargin = 'stretch';
        $mpdf->setAutoBottomMargin = 'stretch';

        $html = view($this->view . 'report.omset-pdf', [
            'rows' => $rows,
            'totals' => $totals,
            'periodLabel' => $periodLabel,
        ])->render();

        $mpdf->WriteHTML($html);

        return $mpdf->Output('Laporan_Omset.pdf', 'I');
    }
}
