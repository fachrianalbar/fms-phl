<?php

namespace App\Http\Controllers\Report;

use App\Exports\FactoryTaxReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Report\UpdateFactoryTaxRequest;
use App\Models\Finance\Invoice;
use App\Models\Master\Customer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;
use Yajra\DataTables\DataTables;

class FactoryTaxController extends Controller
{
    public const ADDRESS_EXPR = "COALESCE(NULLIF(customer.billingAddress, ''), customer.officeAddress)";

    public const DPP_EXPR = 'CASE WHEN (COALESCE(invoice.routeAmount, 0) + COALESCE(invoice.onChargeAmount, 0)) > 0 THEN (COALESCE(invoice.routeAmount, 0) + COALESCE(invoice.onChargeAmount, 0)) ELSE COALESCE(invoice.invoiceAmount, 0) END';

    public const DPP_LAIN_EXPR = 'ROUND(('.self::DPP_EXPR.') * 11 / 12, 2)';

    public const PPN_EXPR = 'ROUND(ROUND(('.self::DPP_EXPR.') * 11 / 12, 2) * 0.12, 2)';

    protected string $title = 'Laporan Pajak Pabrik';

    protected string $view = 'report.factory-tax.';

    public function index()
    {
        $customers = Customer::query()->orderBy('name')->get(['code', 'name']);

        return view($this->view.'index')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('customers', $customers);
    }

    public function datatable(Request $request)
    {
        if (! $request->ajax()) {
            abort(404);
        }

        $query = $this->filteredQuery($this->extractFilters($request));

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $hasTax = filled($row->tax_invoice_number) || filled($row->withholding_tax_number);
                $btnClass = $hasTax ? 'bg-success-subtle text-success' : 'bg-primary-subtle text-primary';
                $title = $hasTax ? 'Edit Pajak Pabrik' : 'Input Pajak Pabrik';
                $amountFormatted = $row->withholding_tax_amount !== null
                    ? number_format((float) $row->withholding_tax_amount, 0, ',', '.')
                    : '';
                $taxDate = $row->withholding_tax_date
                    ? Carbon::parse($row->withholding_tax_date)->format('Y-m-d')
                    : '';

                return '<button type="button" class="btn btn-icon btn-sm '.$btnClass.' btn-input-tax"'
                    .' data-id="'.e($row->id).'"'
                    .' data-invoice="'.e($row->invoiceNumber ?: $row->code).'"'
                    .' data-customer="'.e($row->customerName ?: '-').'"'
                    .' data-dpp="'.e('Rp '.number_format((float) $row->dpp, 0, ',', '.')).'"'
                    .' data-tax-invoice="'.e($row->tax_invoice_number ?? '').'"'
                    .' data-withholding-number="'.e($row->withholding_tax_number ?? '').'"'
                    .' data-withholding-amount="'.e($amountFormatted).'"'
                    .' data-withholding-date="'.e($taxDate).'"'
                    .' data-bs-toggle="tooltip" title="'.$title.'">'
                    .'<i class="mdi mdi-file-document-edit-outline fs-14"></i>'
                    .'</button>';
            })
            ->editColumn('invoiceDate', fn ($row) => $row->invoiceDate ? Carbon::parse($row->invoiceDate)->format('d-m-Y') : '-')
            ->editColumn('npwp', fn ($row) => $row->npwp ?: '-')
            ->editColumn('customerName', fn ($row) => $row->customerName ?: '-')
            ->addColumn('orderDateRange', fn ($row) => self::formatOrderDateRange($row->minOrderDate, $row->maxOrderDate))
            ->editColumn('materials', fn ($row) => $row->materials ?: '-')
            ->editColumn('customerAddress', fn ($row) => $row->customerAddress ?: '-')
            ->editColumn('invoiceNumber', fn ($row) => $row->invoiceNumber ?: ($row->code ?: '-'))
            ->editColumn('tonase', fn ($row) => number_format((float) $row->tonase, 2, ',', '.'))
            ->editColumn('oa', fn ($row) => 'Rp '.number_format((float) $row->oa, 0, ',', '.'))
            ->editColumn('dpp', fn ($row) => 'Rp '.number_format((float) $row->dpp, 0, ',', '.'))
            ->editColumn('dppLain', fn ($row) => 'Rp '.number_format((float) $row->dppLain, 0, ',', '.'))
            ->editColumn('ppn', fn ($row) => 'Rp '.number_format((float) $row->ppn, 0, ',', '.'))
            ->editColumn('tax_invoice_number', fn ($row) => $row->tax_invoice_number ?: '-')
            ->editColumn('lastPaymentDate', fn ($row) => $row->lastPaymentDate ? Carbon::parse($row->lastPaymentDate)->format('d-m-Y') : '-')
            ->editColumn('withholding_tax_number', fn ($row) => $row->withholding_tax_number ?: '-')
            ->editColumn('withholding_tax_amount', fn ($row) => $row->withholding_tax_amount !== null ? 'Rp '.number_format((float) $row->withholding_tax_amount, 0, ',', '.') : '-')
            ->editColumn('withholding_tax_date', fn ($row) => $row->withholding_tax_date ? Carbon::parse($row->withholding_tax_date)->format('d-m-Y') : '-')
            ->filterColumn('invoiceDate', fn (Builder $q, $k) => $this->filterDate($q, 'invoice.invoiceDate', $k))
            ->filterColumn('npwp', fn (Builder $q, $k) => $q->where('customer.npwp', 'like', "%{$k}%"))
            ->filterColumn('customerName', fn (Builder $q, $k) => $q->where('customer.name', 'like', "%{$k}%"))
            ->filterColumn('orderDateRange', function (Builder $q, $k) {
                $q->where(function (Builder $inner) use ($k) {
                    $inner->whereRaw("DATE_FORMAT(inv_ord.minOrderDate, '%d-%m-%Y') LIKE ?", ["%{$k}%"])
                        ->orWhereRaw("DATE_FORMAT(inv_ord.maxOrderDate, '%d-%m-%Y') LIKE ?", ["%{$k}%"]);
                });
            })
            ->filterColumn('materials', fn (Builder $q, $k) => $q->where('inv_ord.materials', 'like', "%{$k}%"))
            ->filterColumn('customerAddress', fn (Builder $q, $k) => $q->whereRaw(self::ADDRESS_EXPR.' LIKE ?', ["%{$k}%"]))
            ->filterColumn('invoiceNumber', fn (Builder $q, $k) => $q->where('invoice.invoiceNumber', 'like', "%{$k}%"))
            ->filterColumn('tonase', fn (Builder $q, $k) => $this->filterNumeric($q, 'COALESCE(inv_ord.tonase, 0)', $k))
            ->filterColumn('oa', fn (Builder $q, $k) => $this->filterNumeric($q, 'COALESCE(inv_ord.oa, 0)', $k))
            ->filterColumn('dpp', fn (Builder $q, $k) => $this->filterNumeric($q, self::DPP_EXPR, $k))
            ->filterColumn('dppLain', fn (Builder $q, $k) => $this->filterNumeric($q, self::DPP_LAIN_EXPR, $k))
            ->filterColumn('ppn', fn (Builder $q, $k) => $this->filterNumeric($q, self::PPN_EXPR, $k))
            ->filterColumn('tax_invoice_number', fn (Builder $q, $k) => $q->where('invoice.tax_invoice_number', 'like', "%{$k}%"))
            ->filterColumn('lastPaymentDate', fn (Builder $q, $k) => $this->filterDate($q, 'inv_pay.lastPaymentDate', $k))
            ->filterColumn('withholding_tax_number', fn (Builder $q, $k) => $q->where('invoice.withholding_tax_number', 'like', "%{$k}%"))
            ->filterColumn('withholding_tax_amount', fn (Builder $q, $k) => $this->filterNumeric($q, 'COALESCE(invoice.withholding_tax_amount, 0)', $k))
            ->filterColumn('withholding_tax_date', fn (Builder $q, $k) => $this->filterDate($q, 'invoice.withholding_tax_date', $k))
            ->orderColumn('invoiceDate', 'invoice.invoiceDate $1')
            ->orderColumn('npwp', 'customer.npwp $1')
            ->orderColumn('customerName', 'customer.name $1')
            ->orderColumn('orderDateRange', 'inv_ord.minOrderDate $1')
            ->orderColumn('materials', 'inv_ord.materials $1')
            ->orderColumn('customerAddress', 'customerAddress $1')
            ->orderColumn('invoiceNumber', 'invoice.invoiceNumber $1')
            ->orderColumn('tonase', 'tonase $1')
            ->orderColumn('oa', 'oa $1')
            ->orderColumn('dpp', 'dpp $1')
            ->orderColumn('dppLain', 'dppLain $1')
            ->orderColumn('ppn', 'ppn $1')
            ->orderColumn('tax_invoice_number', 'invoice.tax_invoice_number $1')
            ->orderColumn('lastPaymentDate', 'inv_pay.lastPaymentDate $1')
            ->orderColumn('withholding_tax_number', 'invoice.withholding_tax_number $1')
            ->orderColumn('withholding_tax_amount', 'invoice.withholding_tax_amount $1')
            ->orderColumn('withholding_tax_date', 'invoice.withholding_tax_date $1')
            ->rawColumns(['action'])
            ->toJson();
    }

    public function updateTaxInfo(UpdateFactoryTaxRequest $request, string $id): JsonResponse
    {
        $invoice = Invoice::query()
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->firstOrFail();

        $invoice->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Data pajak pabrik berhasil disimpan.',
            'data' => [
                'id' => $invoice->id,
                'tax_invoice_number' => $invoice->tax_invoice_number,
                'withholding_tax_number' => $invoice->withholding_tax_number,
                'withholding_tax_amount' => $invoice->withholding_tax_amount,
                'withholding_tax_date' => $invoice->withholding_tax_date?->format('Y-m-d'),
            ],
        ]);
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(
            new FactoryTaxReportExport($this->extractFilters($request)),
            'Laporan-Pajak-Pabrik-'.date('Ymd').'.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $filters = $this->extractFilters($request);
        $rows = $this->filteredQuery($filters)
            ->orderByDesc('invoice.invoiceDate')
            ->orderBy('invoice.invoiceNumber')
            ->toBase()
            ->get();

        $customerName = $filters['customerCode']
            ? Customer::query()->where('code', $filters['customerCode'])->value('name')
            : null;

        $mpdf = new Mpdf([
            'orientation' => 'L',
            'format' => [215, 330],
            'tempDir' => storage_path('app/mpdf-temp'),
        ]);

        $mpdf->WriteHTML(
            view($this->view.'report.factory-tax-pdf')
                ->with('rows', $rows)
                ->with('customerName', $customerName)
                ->with('taxStatus', $filters['taxStatus'])
                ->with('startDate', $filters['startDate'])
                ->with('endDate', $filters['endDate'])
        );

        $filename = 'Laporan-Pajak-Pabrik-'.date('Ymd').'.pdf';

        return response($mpdf->Output($filename, 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }

    /**
     * @param  array{customerCode: ?string, taxStatus: ?string, startDate: ?string, endDate: ?string}  $filters
     */
    public function filteredQuery(array $filters): Builder
    {
        $orderAggSub = DB::table('invoice_detail as idet')
            ->join('order as o', function (JoinClause $join) {
                $join->on('o.code', '=', 'idet.orderCode')
                    ->whereNull('o.deleted_at');
            })
            ->leftJoin('material as m', function (JoinClause $join) {
                $join->on('m.code', '=', 'o.materialCode')
                    ->whereNull('m.deleted_at');
            })
            ->whereNull('idet.deleted_at')
            ->groupBy('idet.invoiceCode')
            ->select([
                'idet.invoiceCode',
                DB::raw('MIN(o.orderDate) as minOrderDate'),
                DB::raw('MAX(o.orderDate) as maxOrderDate'),
                DB::raw("GROUP_CONCAT(DISTINCT m.name ORDER BY m.name SEPARATOR ', ') as materials"),
                DB::raw('COALESCE(SUM(o.qty), 0) as tonase'),
                DB::raw('COALESCE(AVG(NULLIF(o.price, 0)), 0) as oa'),
            ]);

        $paymentAggSub = DB::table('invoice_payment')
            ->select([
                'invoiceCode',
                DB::raw('MAX(paymentDate) as lastPaymentDate'),
            ])
            ->whereNull('deleted_at')
            ->groupBy('invoiceCode');

        $query = Invoice::query()
            ->select([
                'invoice.id',
                'invoice.code',
                'invoice.invoiceDate',
                'customer.npwp',
                'customer.name as customerName',
                'inv_ord.minOrderDate',
                'inv_ord.maxOrderDate',
                'inv_ord.materials',
                DB::raw(self::ADDRESS_EXPR.' as customerAddress'),
                'invoice.invoiceNumber',
                DB::raw('COALESCE(inv_ord.tonase, 0) as tonase'),
                DB::raw('COALESCE(inv_ord.oa, 0) as oa'),
                DB::raw(self::DPP_EXPR.' as dpp'),
                DB::raw(self::DPP_LAIN_EXPR.' as dppLain'),
                DB::raw(self::PPN_EXPR.' as ppn'),
                'invoice.tax_invoice_number',
                'inv_pay.lastPaymentDate',
                'invoice.withholding_tax_number',
                'invoice.withholding_tax_amount',
                'invoice.withholding_tax_date',
            ])
            ->leftJoin('customer', function (JoinClause $join) {
                $join->on('customer.code', '=', 'invoice.customerCode')
                    ->whereNull('customer.deleted_at');
            })
            ->leftJoinSub($orderAggSub, 'inv_ord', 'inv_ord.invoiceCode', '=', 'invoice.code')
            ->leftJoinSub($paymentAggSub, 'inv_pay', 'inv_pay.invoiceCode', '=', 'invoice.code')
            ->whereNull('invoice.deleted_at');

        if ($filters['customerCode']) {
            $query->where('invoice.customerCode', $filters['customerCode']);
        }

        if ($filters['taxStatus'] === 'filled') {
            $query->where(function (Builder $q) {
                $q->where(fn (Builder $inner) => $inner->whereNotNull('invoice.tax_invoice_number')->where('invoice.tax_invoice_number', '<>', ''))
                    ->orWhere(fn (Builder $inner) => $inner->whereNotNull('invoice.withholding_tax_number')->where('invoice.withholding_tax_number', '<>', ''));
            });
        } elseif ($filters['taxStatus'] === 'empty') {
            $query->where(function (Builder $q) {
                $q->whereNull('invoice.tax_invoice_number')
                    ->orWhere('invoice.tax_invoice_number', '');
            })->where(function (Builder $q) {
                $q->whereNull('invoice.withholding_tax_number')
                    ->orWhere('invoice.withholding_tax_number', '');
            });
        }

        if ($filters['startDate']) {
            $query->whereDate('invoice.invoiceDate', '>=', $filters['startDate']);
        }

        if ($filters['endDate']) {
            $query->whereDate('invoice.invoiceDate', '<=', $filters['endDate']);
        }

        return $query;
    }

    public static function formatOrderDateRange(?string $minDate, ?string $maxDate): string
    {
        if (! $minDate && ! $maxDate) {
            return '-';
        }

        $min = $minDate ? Carbon::parse($minDate)->format('d-m-Y') : null;
        $max = $maxDate ? Carbon::parse($maxDate)->format('d-m-Y') : null;

        if ($min && $max && $min !== $max) {
            return $min.' s/d '.$max;
        }

        return $min ?: ($max ?: '-');
    }

    /**
     * @return array{customerCode: ?string, taxStatus: ?string, startDate: ?string, endDate: ?string}
     */
    private function extractFilters(Request $request): array
    {
        return [
            'customerCode' => $request->input('customerCode') ?: null,
            'taxStatus' => $request->input('taxStatus') ?: null,
            'startDate' => $request->input('startDate') ?: null,
            'endDate' => $request->input('endDate') ?: null,
        ];
    }

    private function filterDate(Builder $query, string $expr, string $keyword): void
    {
        $keyword = trim($keyword);
        $query->whereRaw("DATE_FORMAT({$expr}, '%d-%m-%Y') LIKE ?", ["%{$keyword}%"]);
    }

    private function filterNumeric(Builder $query, string $expr, string $keyword): void
    {
        $digits = preg_replace('/\D/', '', $keyword) ?? '';
        if ($digits === '') {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereRaw("CAST({$expr} AS CHAR) LIKE ?", ["{$digits}%"]);
    }
}
