<?php

namespace App\Http\Controllers\Report;

use App\Exports\ItemPriceReportExport;
use App\Http\Controllers\Controller;
use App\Models\Inventory\Item;
use App\Models\Inventory\Supplier;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;
use Yajra\DataTables\DataTables;

class ItemPriceReportController extends Controller
{
    public const INPUT_DATE_EXPR = 'COALESCE(item.updated_at, item.created_at)';

    protected string $title = 'Daftar Harga Barang';

    protected string $view = 'report.item-prices.';

    public function index()
    {
        $suppliers = Supplier::query()->orderBy('name')->get(['code', 'name']);

        return view($this->view.'index')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('suppliers', $suppliers);
    }

    public function datatable(Request $request)
    {
        if (! $request->ajax()) {
            abort(404);
        }

        $query = $this->filteredQuery($this->extractFilters($request));

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('supplierName', fn ($row) => $row->supplierName ?: '-')
            ->editColumn('itemName', fn ($row) => $row->itemName ?: '-')
            ->editColumn('inputDate', fn ($row) => $row->inputDate ? Carbon::parse($row->inputDate)->format('d-m-Y') : '-')
            ->editColumn('price', fn ($row) => 'Rp '.number_format((float) $row->price, 0, ',', '.'))
            ->filterColumn('supplierName', function (Builder $q, $keyword) {
                if (in_array(trim($keyword), ['-', '—'], true)) {
                    $q->whereNull('supplier.name');

                    return;
                }
                $q->where('supplier.name', 'like', "%{$keyword}%");
            })
            ->filterColumn('itemName', function (Builder $q, $keyword) {
                $q->where(function (Builder $inner) use ($keyword) {
                    $inner->where('item.name', 'like', "%{$keyword}%")
                        ->orWhere('item.code', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('inputDate', fn (Builder $q, $keyword) => $q->whereRaw('DATE_FORMAT('.self::INPUT_DATE_EXPR.", '%d-%m-%Y') LIKE ?", ["%{$keyword}%"]))
            ->filterColumn('price', function (Builder $q, $keyword) {
                $digits = preg_replace('/\D/', '', $keyword) ?? '';
                if ($digits === '') {
                    $q->whereRaw('1 = 0');

                    return;
                }
                $q->whereRaw('CAST(COALESCE(item.price, 0) AS CHAR) LIKE ?', ["{$digits}%"]);
            })
            ->orderColumn('supplierName', 'supplier.name $1')
            ->orderColumn('itemName', 'item.name $1')
            ->orderColumn('inputDate', 'inputDate $1')
            ->orderColumn('price', 'item.price $1')
            ->toJson();
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(
            new ItemPriceReportExport($this->extractFilters($request)),
            'Daftar-Harga-Barang-'.date('Ymd').'.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $filters = $this->extractFilters($request);
        $rows = $this->filteredQuery($filters)
            ->orderBy('supplier.name')
            ->orderBy('item.name')
            ->toBase()
            ->get();

        $supplierName = $filters['supplierCode']
            ? Supplier::query()->where('code', $filters['supplierCode'])->value('name')
            : null;

        $mpdf = new Mpdf([
            'orientation' => 'P',
            'format' => 'A4',
            'tempDir' => storage_path('app/mpdf-temp'),
        ]);

        $mpdf->WriteHTML(
            view($this->view.'report.item-prices-pdf')
                ->with('rows', $rows)
                ->with('supplierName', $supplierName)
                ->with('type', $filters['type'])
                ->with('startDate', $filters['startDate'])
                ->with('endDate', $filters['endDate'])
        );

        $filename = 'Daftar-Harga-Barang-'.date('Ymd').'.pdf';

        return response($mpdf->Output($filename, 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }

    /**
     * @param  array{supplierCode: ?string, type: ?string, startDate: ?string, endDate: ?string}  $filters
     */
    public function filteredQuery(array $filters): Builder
    {
        $query = Item::query()
            ->select([
                'item.id',
                'item.code as itemCode',
                'item.name as itemName',
                'item.type',
                'supplier.name as supplierName',
                DB::raw(self::INPUT_DATE_EXPR.' as inputDate'),
                DB::raw('COALESCE(item.price, 0) as price'),
            ])
            ->leftJoin('supplier', function (JoinClause $join) {
                $join->on('supplier.code', '=', 'item.supplierCode')
                    ->whereNull('supplier.deleted_at');
            })
            ->whereNull('item.deleted_at');

        if ($filters['supplierCode']) {
            $query->where('item.supplierCode', $filters['supplierCode']);
        }

        if ($filters['type'] && in_array($filters['type'], ['part', 'jasa'], true)) {
            $query->where('item.type', $filters['type']);
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
     * @return array{supplierCode: ?string, type: ?string, startDate: ?string, endDate: ?string}
     */
    private function extractFilters(Request $request): array
    {
        return [
            'supplierCode' => $request->input('supplierCode') ?: null,
            'type' => $request->input('type') ?: null,
            'startDate' => $request->input('startDate') ?: null,
            'endDate' => $request->input('endDate') ?: null,
        ];
    }
}
