<?php

namespace App\Http\Controllers\Report;

use App\Exports\OverallReportExport;
use App\Http\Controllers\Controller;
use App\Models\Master\Customer;
use App\Models\Master\FleetCompany;
use App\Models\Operational\Order;
use App\Services\Report\OverallReportService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;
use Yajra\DataTables\DataTables;

class OverallReportController extends Controller
{
    protected string $title = 'Laporan Keseluruhan';

    protected string $view = 'report.overall.';

    public function __construct(private readonly OverallReportService $service) {}

    public function index()
    {
        $customers = Customer::query()->orderBy('name')->get(['code', 'name']);
        $fleetCompanies = FleetCompany::query()->orderBy('name')->get(['code', 'name', 'type']);

        return view($this->view.'index')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('customers', $customers)
            ->with('fleetCompanies', $fleetCompanies);
    }

    public function datatable(Request $request)
    {
        if (! $request->ajax()) {
            abort(404);
        }

        $query = $this->service->filteredQuery($this->extractFilters($request))
            ->orderByDesc('order.orderDate')
            ->orderByDesc('order.created_at');

        $search = trim((string) $request->input('search.value', ''));
        if ($search !== '') {
            $query->where(function (Builder $q) use ($search) {
                $q->where('order.code', 'like', "%{$search}%")
                    ->orWhere('order.plateNumber', 'like', "%{$search}%")
                    ->orWhere('order.notes', 'like', "%{$search}%")
                    ->orWhere('order.returnDescription', 'like', "%{$search}%")
                    ->orWhereRaw("DATE_FORMAT(order.orderDate, '%d-%m-%Y') LIKE ?", ["%{$search}%"])
                    ->orWhereHas('customer', fn (Builder $c) => $c->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('material', fn (Builder $m) => $m->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('fleet', fn (Builder $f) => $f->where('plateNumber', 'like', "%{$search}%")
                        ->orWhereHas('company', fn (Builder $fc) => $fc->where('name', 'like', "%{$search}%")))
                    ->orWhereHas('route.originLocation', fn (Builder $l) => $l->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('route.destinationLocation', fn (Builder $l) => $l->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('invoiceDetail.invoice', fn (Builder $inv) => $inv->where('invoiceNumber', 'like', "%{$search}%"))
                    ->orWhereHas('vendorPayments', fn (Builder $vp) => $vp->where('nota_number', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
                    ->orWhereHas('orderPayment', fn (Builder $op) => $op->where('nota_number', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
            });
        }

        $rupiah = fn (float $v): string => 'Rp '.number_format($v, 0, ',', '.');
        $statusBadge = function (string $status): string {
            if ($status === '-') {
                return '<span class="text-muted">-</span>';
            }
            $class = $status === 'Lunas' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger';

            return '<span class="badge '.$class.' px-2 py-1" style="border-radius: 6px; font-weight: 600; font-size: 11px;">'.e($status).'</span>';
        };

        $rowsCache = [];
        $getRow = function (Order $order) use (&$rowsCache): array {
            return $rowsCache[$order->id] ??= $this->service->buildRow($order);
        };

        return DataTables::of($query)
            ->filter(function () {
                // Global search sudah ditangani eksplisit di atas agar tidak memicu Unknown column pada kolom agregat.
            }, true)
            ->addIndexColumn()
            ->addColumn('order_date', fn (Order $o) => $getRow($o)['order_date'])
            ->addColumn('plate_number', fn (Order $o) => $getRow($o)['plate_number'])
            ->addColumn('customer_name', fn (Order $o) => $getRow($o)['customer_name'])
            ->addColumn('material_name', fn (Order $o) => $getRow($o)['material_name'])
            ->addColumn('origin', fn (Order $o) => $getRow($o)['origin'])
            ->addColumn('destination', fn (Order $o) => $getRow($o)['destination'])
            ->addColumn('fleet_owner', fn (Order $o) => $getRow($o)['fleet_owner'])
            ->addColumn('expedition_name', fn (Order $o) => $getRow($o)['expedition_name'])
            ->addColumn('factory_tonase', fn (Order $o) => number_format($getRow($o)['factory_tonase'], 2, ',', '.'))
            ->addColumn('factory_oa', fn (Order $o) => $rupiah($getRow($o)['factory_oa']))
            ->addColumn('factory_amount', fn (Order $o) => $rupiah($getRow($o)['factory_amount']))
            ->addColumn('factory_tax', fn (Order $o) => $rupiah($getRow($o)['factory_tax']))
            ->addColumn('factory_claim', fn (Order $o) => $rupiah($getRow($o)['factory_claim']))
            ->addColumn('factory_total', fn (Order $o) => $rupiah($getRow($o)['factory_total']))
            ->addColumn('factory_invoice_no', fn (Order $o) => $getRow($o)['factory_invoice_no'])
            ->addColumn('factory_dp', fn (Order $o) => $rupiah($getRow($o)['factory_dp']))
            ->addColumn('factory_payment_date', fn (Order $o) => $getRow($o)['factory_payment_date'])
            ->addColumn('factory_status', fn (Order $o) => $statusBadge($getRow($o)['factory_status']))
            ->addColumn('commission_name', fn (Order $o) => $getRow($o)['commission_name'])
            ->addColumn('commission_amount', fn (Order $o) => $rupiah($getRow($o)['commission_amount']))
            ->addColumn('commission_payment_date', fn (Order $o) => $getRow($o)['commission_payment_date'])
            ->addColumn('commission_status', fn (Order $o) => $statusBadge($getRow($o)['commission_status']))
            ->addColumn('vendor_tonase', fn (Order $o) => number_format($getRow($o)['vendor_tonase'], 2, ',', '.'))
            ->addColumn('vendor_oa', fn (Order $o) => $rupiah($getRow($o)['vendor_oa']))
            ->addColumn('vendor_amount', fn (Order $o) => $rupiah($getRow($o)['vendor_amount']))
            ->addColumn('vendor_tax', fn (Order $o) => $rupiah($getRow($o)['vendor_tax']))
            ->addColumn('vendor_total', fn (Order $o) => $rupiah($getRow($o)['vendor_total']))
            ->addColumn('vendor_return_date', fn (Order $o) => $getRow($o)['vendor_return_date'])
            ->addColumn('vendor_nota_no', fn (Order $o) => $getRow($o)['vendor_nota_no'])
            ->addColumn('vendor_dp', fn (Order $o) => $rupiah($getRow($o)['vendor_dp']))
            ->addColumn('vendor_payment_date', fn (Order $o) => $getRow($o)['vendor_payment_date'])
            ->addColumn('vendor_status', fn (Order $o) => $statusBadge($getRow($o)['vendor_status']))
            ->addColumn('profit', function (Order $o) use ($getRow) {
                $val = $getRow($o)['profit'];
                $class = $val < 0 ? 'text-danger fw-bold' : 'text-success fw-bold';

                return '<span class="'.$class.'">Rp '.number_format($val, 0, ',', '.').'</span>';
            })
            ->addColumn('notes', fn (Order $o) => $getRow($o)['notes'])
            ->rawColumns(['factory_status', 'commission_status', 'vendor_status', 'profit'])
            ->toJson();
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(
            new OverallReportExport($this->extractFilters($request)),
            'Laporan-Keseluruhan-'.date('Ymd').'.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $filters = $this->extractFilters($request);
        $rows = $this->service->filteredQuery($filters)
            ->orderByDesc('order.orderDate')
            ->orderByDesc('order.created_at')
            ->get()
            ->map(fn (Order $o) => $this->service->buildRow($o));

        $customerName = $filters['customerCode']
            ? Customer::query()->where('code', $filters['customerCode'])->value('name')
            : null;
        $fleetCompanyName = $filters['fleetCompanyCode']
            ? FleetCompany::query()->where('code', $filters['fleetCompanyCode'])->value('name')
            : null;

        $mpdf = new Mpdf([
            'orientation' => 'L',
            'format' => 'A3',
            'tempDir' => storage_path('app/mpdf-temp'),
        ]);

        $mpdf->WriteHTML(
            view($this->view.'report.overall-pdf')
                ->with('rows', $rows)
                ->with('customerName', $customerName)
                ->with('fleetCompanyName', $fleetCompanyName)
                ->with('startDate', $filters['startDate'])
                ->with('endDate', $filters['endDate'])
        );

        $filename = 'Laporan-Keseluruhan-'.date('Ymd').'.pdf';

        return response($mpdf->Output($filename, 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }

    /**
     * @return array{startDate: ?string, endDate: ?string, customerCode: ?string, fleetCompanyCode: ?string, factoryStatus: ?string, vendorStatus: ?string}
     */
    private function extractFilters(Request $request): array
    {
        return [
            'startDate' => $request->input('startDate') ?: null,
            'endDate' => $request->input('endDate') ?: null,
            'customerCode' => $request->input('customerCode') ?: null,
            'fleetCompanyCode' => $request->input('fleetCompanyCode') ?: null,
            'factoryStatus' => $request->input('factoryStatus') ?: null,
            'vendorStatus' => $request->input('vendorStatus') ?: null,
        ];
    }
}
