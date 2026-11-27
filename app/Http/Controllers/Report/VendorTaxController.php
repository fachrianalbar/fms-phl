<?php

namespace App\Http\Controllers\Report;

use App\Exports\VendorTaxReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Report\UpdateVendorTaxRequest;
use App\Models\Finance\VendorPayment;
use App\Models\Master\FleetCompany;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;
use Yajra\DataTables\DataTables;

class VendorTaxController extends Controller
{
    public const NOTA_DATE_EXPR = 'COALESCE(vendor_payment.date, DATE(vendor_payment.created_at))';

    public const NOTA_NUMBER_EXPR = "COALESCE(NULLIF(vendor_payment.nota_number, ''), vendor_payment.code)";

    public const OA_EXPR = 'CASE WHEN COALESCE(order.vendorPriceSingle, 0) > 0 THEN order.vendorPriceSingle ELSE COALESCE(order.personalVendorPriceSingle, 0) END';

    protected string $title = 'Laporan Pajak Vendor';

    protected string $view = 'report.vendor-tax.';

    public function index()
    {
        $vendors = FleetCompany::query()->orderBy('name')->get(['code', 'name', 'type']);

        return view($this->view.'index')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('vendors', $vendors);
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
                $title = $hasTax ? 'Edit Pajak Vendor' : 'Input Pajak Vendor';
                $amountFormatted = $row->withholding_tax_amount !== null
                    ? number_format((float) $row->withholding_tax_amount, 0, ',', '.')
                    : '';
                $taxDate = $row->withholding_tax_date
                    ? Carbon::parse($row->withholding_tax_date)->format('Y-m-d')
                    : '';

                return '<button type="button" class="btn btn-icon btn-sm '.$btnClass.' btn-input-tax"'
                    .' data-id="'.e($row->id).'"'
                    .' data-nota="'.e($row->notaNumber).'"'
                    .' data-vendor="'.e($row->vendorName ?: '-').'"'
                    .' data-dpp="'.e('Rp '.number_format((float) $row->dpp, 0, ',', '.')).'"'
                    .' data-tax-invoice="'.e($row->tax_invoice_number ?? '').'"'
                    .' data-withholding-number="'.e($row->withholding_tax_number ?? '').'"'
                    .' data-withholding-amount="'.e($amountFormatted).'"'
                    .' data-withholding-date="'.e($taxDate).'"'
                    .' data-bs-toggle="tooltip" title="'.$title.'">'
                    .'<i class="mdi mdi-file-document-edit-outline fs-14"></i>'
                    .'</button>';
            })
            ->editColumn('notaDate', fn ($row) => $row->notaDate ? Carbon::parse($row->notaDate)->format('d-m-Y') : '-')
            ->editColumn('npwp', fn ($row) => $row->npwp ?: '-')
            ->editColumn('vendorName', fn ($row) => $row->vendorName ?: '-')
            ->editColumn('orderDate', fn ($row) => $row->orderDate ? Carbon::parse($row->orderDate)->format('d-m-Y') : '-')
            ->editColumn('materialName', fn ($row) => $row->materialName ?: '-')
            ->editColumn('vendorAddress', fn ($row) => $row->vendorAddress ?: '-')
            ->editColumn('notaNumber', fn ($row) => $row->notaNumber ?: '-')
            ->editColumn('tonase', fn ($row) => number_format((float) $row->tonase, 2, ',', '.'))
            ->editColumn('oa', fn ($row) => 'Rp '.number_format((float) $row->oa, 0, ',', '.'))
            ->editColumn('dpp', fn ($row) => 'Rp '.number_format((float) $row->dpp, 0, ',', '.'))
            ->editColumn('tax_invoice_number', fn ($row) => $row->tax_invoice_number ?: '-')
            ->editColumn('lastPaymentDate', fn ($row) => $row->lastPaymentDate ? Carbon::parse($row->lastPaymentDate)->format('d-m-Y') : '-')
            ->editColumn('withholding_tax_number', fn ($row) => $row->withholding_tax_number ?: '-')
            ->editColumn('withholding_tax_amount', fn ($row) => $row->withholding_tax_amount !== null ? 'Rp '.number_format((float) $row->withholding_tax_amount, 0, ',', '.') : '-')
            ->editColumn('withholding_tax_date', fn ($row) => $row->withholding_tax_date ? Carbon::parse($row->withholding_tax_date)->format('d-m-Y') : '-')
            ->filterColumn('notaDate', fn (Builder $q, $k) => $this->filterDate($q, self::NOTA_DATE_EXPR, $k))
            ->filterColumn('npwp', fn (Builder $q, $k) => $q->where('fleet_company.npwp', 'like', "%{$k}%"))
            ->filterColumn('vendorName', fn (Builder $q, $k) => $q->where('fleet_company.name', 'like', "%{$k}%"))
            ->filterColumn('orderDate', fn (Builder $q, $k) => $this->filterDate($q, 'order.orderDate', $k))
            ->filterColumn('materialName', fn (Builder $q, $k) => $q->where('material.name', 'like', "%{$k}%"))
            ->filterColumn('vendorAddress', fn (Builder $q, $k) => $q->where('fleet_company.address', 'like', "%{$k}%"))
            ->filterColumn('notaNumber', fn (Builder $q, $k) => $q->whereRaw(self::NOTA_NUMBER_EXPR.' LIKE ?', ["%{$k}%"]))
            ->filterColumn('tonase', fn (Builder $q, $k) => $this->filterNumeric($q, 'COALESCE(order.qty, 0)', $k))
            ->filterColumn('oa', fn (Builder $q, $k) => $this->filterNumeric($q, self::OA_EXPR, $k))
            ->filterColumn('dpp', fn (Builder $q, $k) => $this->filterNumeric($q, 'COALESCE(vendor_payment.amount, 0)', $k))
            ->filterColumn('tax_invoice_number', fn (Builder $q, $k) => $q->where('vendor_payment.tax_invoice_number', 'like', "%{$k}%"))
            ->filterColumn('lastPaymentDate', fn (Builder $q, $k) => $this->filterDate($q, 'vph.lastPaymentDate', $k))
            ->filterColumn('withholding_tax_number', fn (Builder $q, $k) => $q->where('vendor_payment.withholding_tax_number', 'like', "%{$k}%"))
            ->filterColumn('withholding_tax_amount', fn (Builder $q, $k) => $this->filterNumeric($q, 'COALESCE(vendor_payment.withholding_tax_amount, 0)', $k))
            ->filterColumn('withholding_tax_date', fn (Builder $q, $k) => $this->filterDate($q, 'vendor_payment.withholding_tax_date', $k))
            ->orderColumn('notaDate', 'notaDate $1')
            ->orderColumn('npwp', 'fleet_company.npwp $1')
            ->orderColumn('vendorName', 'fleet_company.name $1')
            ->orderColumn('orderDate', 'order.orderDate $1')
            ->orderColumn('materialName', 'material.name $1')
            ->orderColumn('vendorAddress', 'fleet_company.address $1')
            ->orderColumn('notaNumber', 'notaNumber $1')
            ->orderColumn('tonase', 'order.qty $1')
            ->orderColumn('oa', 'oa $1')
            ->orderColumn('dpp', 'vendor_payment.amount $1')
            ->orderColumn('tax_invoice_number', 'vendor_payment.tax_invoice_number $1')
            ->orderColumn('lastPaymentDate', 'vph.lastPaymentDate $1')
            ->orderColumn('withholding_tax_number', 'vendor_payment.withholding_tax_number $1')
            ->orderColumn('withholding_tax_amount', 'vendor_payment.withholding_tax_amount $1')
            ->orderColumn('withholding_tax_date', 'vendor_payment.withholding_tax_date $1')
            ->rawColumns(['action'])
            ->toJson();
    }

    public function updateTaxInfo(UpdateVendorTaxRequest $request, string $id): JsonResponse
    {
        $vendorPayment = VendorPayment::query()
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->firstOrFail();

        $vendorPayment->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Data pajak vendor berhasil disimpan.',
            'data' => [
                'id' => $vendorPayment->id,
                'tax_invoice_number' => $vendorPayment->tax_invoice_number,
                'withholding_tax_number' => $vendorPayment->withholding_tax_number,
                'withholding_tax_amount' => $vendorPayment->withholding_tax_amount,
                'withholding_tax_date' => $vendorPayment->withholding_tax_date?->format('Y-m-d'),
            ],
        ]);
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(
            new VendorTaxReportExport($this->extractFilters($request)),
            'Laporan-Pajak-Vendor-'.date('Ymd').'.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $filters = $this->extractFilters($request);
        $rows = $this->filteredQuery($filters)
            ->orderByDesc('notaDate')
            ->orderBy('notaNumber')
            ->toBase()
            ->get();

        $vendorName = $filters['fleetCompanyCode']
            ? FleetCompany::query()->where('code', $filters['fleetCompanyCode'])->value('name')
            : null;

        $mpdf = new Mpdf([
            'orientation' => 'L',
            'format' => [215, 330],
            'tempDir' => storage_path('app/mpdf-temp'),
        ]);

        $mpdf->WriteHTML(
            view($this->view.'report.vendor-tax-pdf')
                ->with('rows', $rows)
                ->with('vendorName', $vendorName)
                ->with('taxStatus', $filters['taxStatus'])
                ->with('startDate', $filters['startDate'])
                ->with('endDate', $filters['endDate'])
        );

        $filename = 'Laporan-Pajak-Vendor-'.date('Ymd').'.pdf';

        return response($mpdf->Output($filename, 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }

    /**
     * @param  array{fleetCompanyCode: ?string, taxStatus: ?string, startDate: ?string, endDate: ?string}  $filters
     */
    public function filteredQuery(array $filters): Builder
    {
        $latestPaymentSub = DB::table('vendor_payment_history')
            ->select([
                'vendor_payment_id',
                DB::raw('MAX(payment_date) as lastPaymentDate'),
            ])
            ->whereNull('deleted_at')
            ->groupBy('vendor_payment_id');

        $query = VendorPayment::query()
            ->select([
                'vendor_payment.id',
                DB::raw(self::NOTA_DATE_EXPR.' as notaDate'),
                'fleet_company.npwp',
                'fleet_company.name as vendorName',
                'order.orderDate',
                'material.name as materialName',
                'fleet_company.address as vendorAddress',
                DB::raw(self::NOTA_NUMBER_EXPR.' as notaNumber'),
                DB::raw('COALESCE(order.qty, 0) as tonase'),
                DB::raw(self::OA_EXPR.' as oa'),
                DB::raw('COALESCE(vendor_payment.amount, 0) as dpp'),
                'vendor_payment.tax_invoice_number',
                'vph.lastPaymentDate',
                'vendor_payment.withholding_tax_number',
                'vendor_payment.withholding_tax_amount',
                'vendor_payment.withholding_tax_date',
            ])
            ->leftJoin('order', function (JoinClause $join) {
                $join->on('order.code', '=', 'vendor_payment.orderCode')
                    ->whereNull('order.deleted_at');
            })
            ->leftJoin('fleet', function (JoinClause $join) {
                $join->on('fleet.code', '=', 'order.fleetCode')
                    ->whereNull('fleet.deleted_at');
            })
            ->leftJoin('fleet_company', function (JoinClause $join) {
                $join->on('fleet_company.code', '=', 'fleet.fleetCompanyCode')
                    ->whereNull('fleet_company.deleted_at');
            })
            ->leftJoin('material', function (JoinClause $join) {
                $join->on('material.code', '=', 'order.materialCode')
                    ->whereNull('material.deleted_at');
            })
            ->leftJoinSub($latestPaymentSub, 'vph', function (JoinClause $join) {
                $join->on(
                    DB::raw('vph.vendor_payment_id COLLATE utf8mb4_unicode_ci'),
                    '=',
                    DB::raw('vendor_payment.id COLLATE utf8mb4_unicode_ci')
                );
            })
            ->whereNull('vendor_payment.deleted_at');

        if ($filters['fleetCompanyCode']) {
            $query->where('fleet_company.code', $filters['fleetCompanyCode']);
        }

        if ($filters['taxStatus'] === 'filled') {
            $query->where(function (Builder $q) {
                $q->where(fn (Builder $inner) => $inner->whereNotNull('vendor_payment.tax_invoice_number')->where('vendor_payment.tax_invoice_number', '<>', ''))
                    ->orWhere(fn (Builder $inner) => $inner->whereNotNull('vendor_payment.withholding_tax_number')->where('vendor_payment.withholding_tax_number', '<>', ''));
            });
        } elseif ($filters['taxStatus'] === 'empty') {
            $query->where(function (Builder $q) {
                $q->whereNull('vendor_payment.tax_invoice_number')
                    ->orWhere('vendor_payment.tax_invoice_number', '');
            })->where(function (Builder $q) {
                $q->whereNull('vendor_payment.withholding_tax_number')
                    ->orWhere('vendor_payment.withholding_tax_number', '');
            });
        }

        if ($filters['startDate']) {
            $query->whereRaw('DATE('.self::NOTA_DATE_EXPR.') >= ?', [$filters['startDate']]);
        }

        if ($filters['endDate']) {
            $query->whereRaw('DATE('.self::NOTA_DATE_EXPR.') <= ?', [$filters['endDate']]);
        }

        return $query;
    }

    /**
     * @return array{fleetCompanyCode: ?string, taxStatus: ?string, startDate: ?string, endDate: ?string}
     */
    private function extractFilters(Request $request): array
    {
        return [
            'fleetCompanyCode' => $request->input('fleetCompanyCode') ?: null,
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
