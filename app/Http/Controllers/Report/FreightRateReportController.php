<?php

namespace App\Http\Controllers\Report;

use App\Exports\FreightRateReportExport;
use App\Http\Controllers\Controller;
use App\Models\Data\Route as DataRoute;
use App\Models\Master\Customer;
use App\Models\Master\FleetCompany;
use App\Models\Master\Location;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;
use Yajra\DataTables\DataTables;

class FreightRateReportController extends Controller
{
    public const INPUT_DATE_EXPR = 'COALESCE(rpe.updated_at, route.updated_at, route.created_at)';

    protected string $title = 'Daftar Ongkos Angkut';

    protected string $view = 'report.freight-rates.';

    public function index()
    {
        $customers = Customer::query()->orderBy('name')->get(['code', 'name']);
        $locations = Location::query()->orderBy('name')->get(['code', 'name']);
        $vendors = FleetCompany::query()
            ->where('type', 'External')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view($this->view.'index')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('customers', $customers)
            ->with('locations', $locations)
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
            ->editColumn('customerName', fn ($row) => $row->customerName ?: '-')
            ->editColumn('originName', fn ($row) => $row->originName ?: '-')
            ->editColumn('destinationName', fn ($row) => $row->destinationName ?: '-')
            ->editColumn('inputDate', fn ($row) => $row->inputDate ? Carbon::parse($row->inputDate)->format('d-m-Y') : '-')
            ->editColumn('price', fn ($row) => 'Rp '.number_format((float) $row->price, 0, ',', '.'))
            ->editColumn('personalVendorPrice', fn ($row) => 'Rp '.number_format((float) $row->personalVendorPrice, 0, ',', '.'))
            ->editColumn('vendorName', fn ($row) => $row->vendorName ?: '-')
            ->editColumn('externalVendorPrice', fn ($row) => 'Rp '.number_format((float) $row->externalVendorPrice, 0, ',', '.'))
            ->filterColumn('customerName', fn (Builder $q, $keyword) => $q->where('customer.name', 'like', "%{$keyword}%"))
            ->filterColumn('originName', fn (Builder $q, $keyword) => $q->where('origin_loc.name', 'like', "%{$keyword}%"))
            ->filterColumn('destinationName', fn (Builder $q, $keyword) => $q->where('dest_loc.name', 'like', "%{$keyword}%"))
            ->filterColumn('inputDate', fn (Builder $q, $keyword) => $q->whereRaw('DATE_FORMAT('.self::INPUT_DATE_EXPR.", '%d-%m-%Y') LIKE ?", ["%{$keyword}%"]))
            ->filterColumn('price', fn (Builder $q, $keyword) => $this->filterNumeric($q, 'COALESCE(route.price, 0)', $keyword))
            ->filterColumn('personalVendorPrice', fn (Builder $q, $keyword) => $this->filterNumeric($q, 'COALESCE(route.personalVendorPrice, 0)', $keyword))
            ->filterColumn('vendorName', function (Builder $q, $keyword) {
                if (in_array(trim($keyword), ['-', '—'], true)) {
                    $q->whereNull('vendor.name');

                    return;
                }
                $q->where('vendor.name', 'like', "%{$keyword}%");
            })
            ->filterColumn('externalVendorPrice', fn (Builder $q, $keyword) => $this->filterNumeric($q, 'COALESCE(rpe.amount, 0)', $keyword))
            ->orderColumn('customerName', 'customer.name $1')
            ->orderColumn('originName', 'origin_loc.name $1')
            ->orderColumn('destinationName', 'dest_loc.name $1')
            ->orderColumn('inputDate', 'inputDate $1')
            ->orderColumn('price', 'route.price $1')
            ->orderColumn('personalVendorPrice', 'route.personalVendorPrice $1')
            ->orderColumn('vendorName', 'vendor.name $1')
            ->orderColumn('externalVendorPrice', 'externalVendorPrice $1')
            ->toJson();
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(
            new FreightRateReportExport($this->extractFilters($request)),
            'Daftar-Ongkos-Angkut-'.date('Ymd').'.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $filters = $this->extractFilters($request);
        $rows = $this->filteredQuery($filters)
            ->orderBy('customer.name')
            ->orderBy('origin_loc.name')
            ->orderBy('dest_loc.name')
            ->orderBy('vendor.name')
            ->toBase()
            ->get();

        $customerName = $filters['customerCode']
            ? Customer::query()->where('code', $filters['customerCode'])->value('name')
            : null;
        $originName = $filters['originLocationCode']
            ? Location::query()->where('code', $filters['originLocationCode'])->value('name')
            : null;
        $destinationName = $filters['destinationLocationCode']
            ? Location::query()->where('code', $filters['destinationLocationCode'])->value('name')
            : null;
        $vendorName = $filters['fleetCompanyId']
            ? FleetCompany::query()->where('id', $filters['fleetCompanyId'])->value('name')
            : null;

        $mpdf = new Mpdf([
            'orientation' => 'L',
            'format' => [215, 330],
            'tempDir' => storage_path('app/mpdf-temp'),
        ]);

        $mpdf->WriteHTML(
            view($this->view.'report.freight-rates-pdf')
                ->with('rows', $rows)
                ->with('customerName', $customerName)
                ->with('originName', $originName)
                ->with('destinationName', $destinationName)
                ->with('vendorName', $vendorName)
                ->with('startDate', $filters['startDate'])
                ->with('endDate', $filters['endDate'])
        );

        $filename = 'Daftar-Ongkos-Angkut-'.date('Ymd').'.pdf';

        return response($mpdf->Output($filename, 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }

    /**
     * @param  array{customerCode: ?string, originLocationCode: ?string, destinationLocationCode: ?string, fleetCompanyId: ?string, startDate: ?string, endDate: ?string}  $filters
     */
    public function filteredQuery(array $filters): Builder
    {
        $query = DataRoute::query()
            ->select([
                'route.id',
                'route.code',
                'customer.name as customerName',
                'origin_loc.name as originName',
                'dest_loc.name as destinationName',
                DB::raw(self::INPUT_DATE_EXPR.' as inputDate'),
                DB::raw('COALESCE(route.price, 0) as price'),
                DB::raw('COALESCE(route.personalVendorPrice, 0) as personalVendorPrice'),
                'vendor.name as vendorName',
                DB::raw('COALESCE(rpe.amount, 0) as externalVendorPrice'),
            ])
            ->leftJoin('customer', function (JoinClause $join) {
                $join->on('customer.code', '=', 'route.customerCode')
                    ->whereNull('customer.deleted_at');
            })
            ->leftJoin('location as origin_loc', function (JoinClause $join) {
                $join->on('origin_loc.code', '=', 'route.originLocationCode')
                    ->whereNull('origin_loc.deleted_at');
            })
            ->leftJoin('location as dest_loc', function (JoinClause $join) {
                $join->on('dest_loc.code', '=', 'route.destinationLocationCode')
                    ->whereNull('dest_loc.deleted_at');
            })
            ->leftJoin('route_price_external as rpe', function (JoinClause $join) {
                $join->on('rpe.route_id', '=', 'route.id')
                    ->whereNull('rpe.deleted_at');
            })
            ->leftJoin('fleet_company as vendor', function (JoinClause $join) {
                $join->on('vendor.id', '=', 'rpe.fleet_company_id')
                    ->whereNull('vendor.deleted_at');
            })
            ->whereNull('route.deleted_at');

        if ($filters['customerCode']) {
            $query->where('route.customerCode', $filters['customerCode']);
        }

        if ($filters['originLocationCode']) {
            $query->where('route.originLocationCode', $filters['originLocationCode']);
        }

        if ($filters['destinationLocationCode']) {
            $query->where('route.destinationLocationCode', $filters['destinationLocationCode']);
        }

        if ($filters['fleetCompanyId']) {
            $query->where('rpe.fleet_company_id', $filters['fleetCompanyId']);
        }

        if ($filters['startDate']) {
            $query->whereRaw('DATE('.self::INPUT_DATE_EXPR.') >= ?', [$filters['startDate']]);
        }

        if ($filters['endDate']) {
            $query->whereRaw('DATE('.self::INPUT_DATE_EXPR.') <= ?', [$filters['endDate']]);
        }

        return $query;
    }

    /**
     * @return array{customerCode: ?string, originLocationCode: ?string, destinationLocationCode: ?string, fleetCompanyId: ?string, startDate: ?string, endDate: ?string}
     */
    private function extractFilters(Request $request): array
    {
        return [
            'customerCode' => $request->input('customerCode') ?: null,
            'originLocationCode' => $request->input('originLocationCode') ?: null,
            'destinationLocationCode' => $request->input('destinationLocationCode') ?: null,
            'fleetCompanyId' => $request->input('fleetCompanyId') ?: null,
            'startDate' => $request->input('startDate') ?: null,
            'endDate' => $request->input('endDate') ?: null,
        ];
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
