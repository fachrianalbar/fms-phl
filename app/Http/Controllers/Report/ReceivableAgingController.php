<?php

namespace App\Http\Controllers\Report;

use App\Exports\ReceivableAgingReport;
use App\Http\Controllers\Controller;
use App\Models\Master\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;
use Yajra\DataTables\DataTables;

class ReceivableAgingController extends Controller
{
    protected $title;

    protected $view;

    public function __construct()
    {
        $this->title = 'Laporan Umur Piutang';
        $this->view = 'report.receivable-aging.';
    }

    public function index()
    {
        $customers = Customer::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        $rows = ReceivableAgingReport::customerRows(null, null);

        return view($this->view.'index')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('customers', $customers)
            ->with('status', ReceivableAgingReport::STATUS_OUTSTANDING)
            ->with('stats', $this->summaryStats($rows));
    }

    public function datatable(Request $request)
    {
        if (! $request->ajax()) {
            abort(404);
        }

        $rows = ReceivableAgingReport::customerRows(
            $request->startDate,
            $request->endDate,
            $request->customerCode,
            $request->status
        );

        return DataTables::of($rows)
            ->addIndexColumn()
            ->addColumn('action', function ($row) use ($request) {
                $query = array_filter([
                    'startDate' => $request->startDate,
                    'endDate' => $request->endDate,
                    'status' => ReceivableAgingReport::normalizeStatus($request->status),
                ], fn ($value) => $value !== null && $value !== '');

                $detailUrl = route('report.receivable-aging.detail', ['customerCode' => $row->customerCode]);

                if ($query !== []) {
                    $detailUrl .= '?'.http_build_query($query);
                }

                return '<a href="'.e($detailUrl).'" class="btn btn-icon btn-sm bg-primary-subtle hover-scale" data-bs-toggle="tooltip" title="Lihat detail customer">'
                    .'<i class="mdi mdi-arrow-right fs-14 text-primary"></i>'
                    .'</a>';
            })
            ->editColumn('customerName', fn ($row) => e($row->customerName))
            ->with(['stats' => $this->summaryStats($rows)])
            ->rawColumns(['action'])
            ->toJson();
    }

    public function detail(string $customerCode, Request $request)
    {
        $customer = Customer::query()
            ->withTrashed()
            ->where('code', $customerCode)
            ->firstOrFail();

        $status = ReceivableAgingReport::normalizeStatus($request->status);

        $summary = ReceivableAgingReport::customerRows(
            $request->startDate,
            $request->endDate,
            $customerCode,
            $status
        )->first() ?? $this->emptyCustomerSummary($customerCode, $customer->name);

        return view($this->view.'show')
            ->with('view', $this->view)
            ->with('title', 'Detail Umur Piutang')
            ->with('customer', $customer)
            ->with('summary', $summary)
            ->with('status', $status)
            ->with('startDate', $request->startDate)
            ->with('endDate', $request->endDate);
    }

    public function datatableDetail(string $customerCode, Request $request)
    {
        if (! $request->ajax()) {
            abort(404);
        }

        $rows = ReceivableAgingReport::invoiceRowsForCustomer(
            $customerCode,
            $request->startDate,
            $request->endDate,
            $request->status
        );

        return DataTables::of($rows)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                return '<a href="'.e(route('invoice.pdf', $row->id)).'" target="_blank" class="btn btn-icon btn-sm bg-danger-subtle hover-scale" data-bs-toggle="tooltip" title="Cetak PDF faktur">'
                    .'<i class="mdi mdi-file-pdf-box fs-14 text-danger"></i>'
                    .'</a>';
            })
            ->editColumn('invoiceNumber', fn ($row) => e($row->invoiceNumber ?: $row->code))
            ->editColumn('invoiceDate', fn ($row) => $row->invoiceDate ?: '')
            ->editColumn('dueDate', fn ($row) => $row->dueDate->format('Y-m-d'))
            ->addColumn('statusBadge', fn ($row) => $this->overdueBadge($row->daysToDue, $row->remainingAmount))
            ->rawColumns(['action', 'statusBadge'])
            ->toJson();
    }

    public function excel(Request $request)
    {
        return Excel::download(new ReceivableAgingReport($request), 'AR-Aging-Report.xlsx');
    }

    public function pdf(Request $request)
    {
        $customerCode = $request->filled('customerCode') ? $request->customerCode : null;
        $status = ReceivableAgingReport::normalizeStatus($request->status);

        $rows = ReceivableAgingReport::customerRows(
            $request->startDate,
            $request->endDate,
            $customerCode,
            $status
        );

        $customerName = $customerCode
            ? Customer::query()->withTrashed()->where('code', $customerCode)->value('name')
            : null;

        $mpdf = new Mpdf([
            'orientation' => 'L',
            'format' => [215, 330],
            'tempDir' => storage_path('app/mpdf-temp'),
        ]);

        $mpdf->WriteHTML(
            view($this->view.'report.aging-pdf')
                ->with('rows', $rows)
                ->with('customerName', $customerName)
                ->with('startDate', $request->startDate)
                ->with('endDate', $request->endDate)
                ->with('status', $status)
        );

        return $mpdf->Output('AR-Aging-Report.pdf', 'I');
    }

    public function summary(Request $request)
    {
        $rows = ReceivableAgingReport::customerRows(
            $request->startDate,
            $request->endDate,
            $request->customerCode,
            $request->status
        );

        return response()->json($this->summaryStats($rows));
    }

    /**
     * @param  Collection<int, object>  $rows
     * @return array<string, int|float>
     */
    private function summaryStats(Collection $rows): array
    {
        return [
            'customerCount' => $rows->count(),
            'invoiceCount' => (int) $rows->sum('invoiceCount'),
            'unpaidCount' => (int) $rows->sum('unpaidCount'),
            'totalBilling' => (float) $rows->sum('totalBilling'),
            'totalPaid' => (float) $rows->sum('totalPaid'),
            'totalClaim' => (float) $rows->sum('totalClaim'),
            'totalRemaining' => (float) $rows->sum('totalRemaining'),
            'dueSoonAmount' => (float) $rows->sum('dueSoonAmount'),
            'dueSoonCount' => (int) $rows->sum('dueSoonCount'),
            'overdueAmount' => (float) $rows->sum('overdueAmount'),
            'overdueCount' => (int) $rows->sum('overdueCount'),
            'agingOver90' => (float) $rows->sum('agingOver90'),
        ];
    }

    private function overdueBadge(int $daysToDue, float $remainingAmount): string
    {
        if ($remainingAmount <= 0) {
            return '<span class="badge bg-success-subtle text-success-emphasis">Lunas</span>';
        }

        if ($daysToDue < 0) {
            return '<span class="badge bg-danger-subtle text-danger-emphasis">Overdue '.abs($daysToDue).' hari</span>';
        }

        if ($daysToDue === 0) {
            return '<span class="badge bg-danger-subtle text-danger-emphasis">Jatuh tempo hari ini</span>';
        }

        if ($daysToDue <= 7) {
            return '<span class="badge bg-warning-subtle text-warning-emphasis">Jatuh tempo '.$daysToDue.' hari</span>';
        }

        return '<span class="badge bg-info-subtle text-info-emphasis">Belum jatuh tempo</span>';
    }

    private function emptyCustomerSummary(string $customerCode, ?string $customerName): object
    {
        return (object) [
            'customerCode' => $customerCode,
            'customerName' => $customerName ?: $customerCode,
            'invoiceCount' => 0,
            'unpaidCount' => 0,
            'totalBilling' => 0.0,
            'totalPaid' => 0.0,
            'totalClaim' => 0.0,
            'totalRemaining' => 0.0,
            'notDueAmount' => 0.0,
            'dueSoonAmount' => 0.0,
            'dueSoonCount' => 0,
            'overdueAmount' => 0.0,
            'overdueCount' => 0,
            'aging0To30' => 0.0,
            'aging31To60' => 0.0,
            'aging61To90' => 0.0,
            'agingOver90' => 0.0,
            'maxOverdueDays' => 0,
        ];
    }
}
