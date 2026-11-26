<?php

namespace App\Http\Controllers\Inventory;

use App\Exports\StockTransactionExport;
use App\Http\Controllers\Controller;
use App\Models\Inventory\Warehouse;
use App\Models\StockTransaction;
use App\Services\Inventory\StockTransactionService;
use App\Services\Inventory\WarehouseService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;
use Yajra\DataTables\DataTables;

class StockTransactionController extends Controller
{
    protected $service;

    protected $warehouseSvc;

    protected $title;

    protected $view;

    public function __construct(StockTransactionService $stockTransactionSvc, WarehouseService $warehouseSvc)
    {
        $this->service = $stockTransactionSvc;
        $this->warehouseSvc = $warehouseSvc;
        $this->title = 'Stock Transaction';
        $this->view = 'inventory.transaction-stock.';
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $warehouses = $this->warehouseSvc->findAll();

        return view($this->view.'index')
            ->with('view', $this->view)
            ->with('warehouses', $warehouses)
            ->with('title', $this->title);
    }

    /**
     * Datatable untuk list warehouse dengan summary total IN/OUT
     */
    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $prefix = DB::getTablePrefix();
            $alias = $prefix.'st';
            $warehouseTable = $prefix.'warehouse';
            $isSqlite = DB::connection()->getDriverName() === 'sqlite';

            $data = Warehouse::select('warehouse.*')
                ->selectRaw('COALESCE(SUM('.$alias.'.qtyIn), 0) as totalIn')
                ->selectRaw('COALESCE(SUM('.$alias.'.qtyOut), 0) as totalOut')
                ->leftJoin('stock_transaction as st', function ($join) use ($warehouseTable, $alias, $isSqlite, $request) {
                    if ($isSqlite) {
                        $join->on($warehouseTable.'.code', '=', $alias.'.warehouseCode');
                    } else {
                        $join->on(DB::raw($warehouseTable.'.code COLLATE utf8mb4_unicode_ci'), '=', DB::raw($alias.'.warehouseCode COLLATE utf8mb4_unicode_ci'));
                    }

                    $join->whereNull('st.deleted_at');

                    if ($request->filled('transactionType')) {
                        $join->where('st.transactionType', $request->transactionType);
                    }
                    if ($request->filled('startDate')) {
                        $join->whereDate('st.date', '>=', $request->startDate);
                    }
                    if ($request->filled('endDate')) {
                        $join->whereDate('st.date', '<=', $request->endDate);
                    }
                })
                ->whereNull('warehouse.deleted_at')
                ->when($request->filled('warehouseCode'), fn ($q) => $q->where('warehouse.code', $request->warehouseCode))
                ->groupBy('warehouse.id', 'warehouse.code', 'warehouse.name', 'warehouse.address', 'warehouse.created_at', 'warehouse.updated_at', 'warehouse.deleted_at');

            return DataTables::of($data)
                ->addIndexColumn()
                ->filterColumn('DT_RowIndex', function ($query, $keyword) {
                    return $query;
                })
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && ! empty($request->search['value'])) {
                        $search = strtolower($request->search['value']);
                        $query->where(function ($q) use ($search) {
                            $q->whereRaw('LOWER(warehouse.code) LIKE ?', ['%'.$search.'%'])
                                ->orWhereRaw('LOWER(warehouse.name) LIKE ?', ['%'.$search.'%']);
                        });
                    }
                })
                ->addColumn('totalStock', function ($row) {
                    return $row->totalIn - $row->totalOut;
                })
                ->addColumn('action', function ($row) {
                    return '<button type="button" class="btn btn-sm btn-primary btn-detail" 
                            data-warehouse-code="'.$row->code.'" 
                            data-warehouse-name="'.$row->name.'"
                            title="Lihat Detail">
                            <i class="mdi mdi-eye"></i> Detail
                          </button>';
                })
                ->rawColumns(['action'])
                ->toJson();
        }
    }

    /**
     * Get detail transaksi per warehouse
     */
    public function getWarehouseDetail(Request $request)
    {
        $warehouseCode = $request->warehouseCode;
        $applyTxFilters = function ($query) use ($request) {
            if ($request->filled('transactionType')) {
                $query->where('transactionType', $request->transactionType);
            }
            if ($request->filled('startDate')) {
                $query->whereDate('date', '>=', $request->startDate);
            }
            if ($request->filled('endDate')) {
                $query->whereDate('date', '<=', $request->endDate);
            }
        };

        // Summary per item
        $summaryQuery = StockTransaction::select('itemCode')
            ->selectRaw('SUM(qtyIn) as totalIn')
            ->selectRaw('SUM(qtyOut) as totalOut')
            ->where('warehouseCode', $warehouseCode);
        $applyTxFilters($summaryQuery);

        $summary = $summaryQuery
            ->groupBy('itemCode')
            ->with('item')
            ->get()
            ->map(function ($item) {
                return [
                    'itemCode' => $item->itemCode,
                    'itemName' => $item->item->name ?? '-',
                    'totalIn' => (float) $item->totalIn,
                    'totalOut' => (float) $item->totalOut,
                    'stock' => (float) $item->totalIn - (float) $item->totalOut,
                ];
            });

        // Detail transaksi
        $detailsQuery = StockTransaction::with(['item'])
            ->where('warehouseCode', $warehouseCode);
        $applyTxFilters($detailsQuery);

        $details = $detailsQuery
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($item) {
                // Determine transaction type based on transactionCode prefix
                $transactionType = '-';
                $transactionCode = $item->transactionCode ?? '';

                if (str_starts_with($transactionCode, 'PO')) {
                    $transactionType = 'Pembelian';
                } elseif (str_starts_with($transactionCode, 'MNT')) {
                    $transactionType = 'Pemeliharaan';
                } elseif ($item->transactionType === 'INITIAL') {
                    $transactionType = 'Stock Awal';
                }

                return [
                    'date' => Carbon::parse($item->date)->format('d-m-Y'),
                    'itemCode' => $item->itemCode,
                    'itemName' => $item->item->name ?? '-',
                    'transactionCode' => $item->transactionCode ?? '-',
                    'transactionType' => $transactionType,
                    'qtyIn' => (float) $item->qtyIn,
                    'qtyOut' => (float) $item->qtyOut,
                ];
            });

        return response()->json([
            'success' => true,
            'summary' => $summary,
            'details' => $details,
        ]);
    }

    public function excelStockTransaction(Request $request)
    {
        $reportData = $this->buildReportData($request);

        return Excel::download(
            new StockTransactionExport($reportData),
            'Kartu-Stok-'.now()->format('YmdHis').'.xlsx'
        );
    }

    /**
     * Generate PDF report
     */
    public function pdfStockTransaction(Request $request)
    {
        $reportData = $this->buildReportData($request);
        $filterSummary = $this->buildFilterSummary($request);

        $tempDir = storage_path('app/mpdf-temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $mpdf = new Mpdf([
            'orientation' => 'P',
            'format' => [215, 330],
            'tempDir' => $tempDir,
        ]);

        $mpdf->WriteHTML(
            view($this->view.'report.transaction-stock-pdf')
                ->with('reportData', $reportData)
                ->with('filterSummary', $filterSummary)
                ->with('printDate', Carbon::now()->format('d-m-Y H:i'))
                ->render()
        );

        return response($mpdf->Output('', 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="Laporan-Transaksi-Stock-'.now()->format('YmdHis').'.pdf"');
    }

    private function buildReportData(Request $request): array
    {
        $warehouseCode = $request->warehouseCode;
        $warehouses = $warehouseCode
            ? Warehouse::where('code', $warehouseCode)->get()
            : Warehouse::all();

        $applyTxFilters = function ($query) use ($request) {
            if ($request->filled('transactionType')) {
                $query->where('transactionType', $request->transactionType);
            }
            if ($request->filled('startDate')) {
                $query->whereDate('date', '>=', $request->startDate);
            }
            if ($request->filled('endDate')) {
                $query->whereDate('date', '<=', $request->endDate);
            }
        };

        $reportData = [];

        foreach ($warehouses as $warehouse) {
            $summaryQuery = StockTransaction::select('itemCode')
                ->selectRaw('SUM(qtyIn) as totalIn')
                ->selectRaw('SUM(qtyOut) as totalOut')
                ->where('warehouseCode', $warehouse->code);
            $applyTxFilters($summaryQuery);

            $summary = $summaryQuery
                ->groupBy('itemCode')
                ->with('item')
                ->get()
                ->map(function ($item) {
                    return [
                        'itemCode' => $item->itemCode,
                        'itemName' => $item->item->name ?? '-',
                        'totalIn' => (float) $item->totalIn,
                        'totalOut' => (float) $item->totalOut,
                        'stock' => (float) $item->totalIn - (float) $item->totalOut,
                    ];
                });

            $detailsQuery = StockTransaction::with(['item'])
                ->where('warehouseCode', $warehouse->code);
            $applyTxFilters($detailsQuery);

            $details = $detailsQuery
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($item) {
                    $transactionType = '-';
                    $transactionCode = $item->transactionCode ?? '';

                    if (str_starts_with($transactionCode, 'PO') || str_starts_with($transactionCode, 'PL')) {
                        $transactionType = 'Pembelian';
                    } elseif (str_starts_with($transactionCode, 'MNT')) {
                        $transactionType = 'Pemeliharaan';
                    } elseif ($item->transactionType === 'INITIAL') {
                        $transactionType = 'Stock Awal';
                    } elseif ($item->transactionType === 'IN') {
                        $transactionType = 'Pembelian';
                    } elseif ($item->transactionType === 'OUT') {
                        $transactionType = 'Pemeliharaan';
                    }

                    return [
                        'date' => Carbon::parse($item->date)->format('d-m-Y'),
                        'itemCode' => $item->itemCode,
                        'itemName' => $item->item->name ?? '-',
                        'transactionCode' => $item->transactionCode ?? '-',
                        'transactionType' => $transactionType,
                        'qtyIn' => (float) $item->qtyIn,
                        'qtyOut' => (float) $item->qtyOut,
                    ];
                });

            $totalIn = $summary->sum('totalIn');
            $totalOut = $summary->sum('totalOut');

            $reportData[] = [
                'warehouse' => $warehouse,
                'summary' => $summary,
                'details' => $details,
                'totalIn' => $totalIn,
                'totalOut' => $totalOut,
                'totalStock' => $totalIn - $totalOut,
            ];
        }

        return $reportData;
    }

    private function buildFilterSummary(Request $request): array
    {
        $summary = [];

        if ($request->filled('warehouseCode')) {
            $whName = Warehouse::query()->where('code', $request->warehouseCode)->value('name');
            $summary[] = 'Gudang: '.($whName ?: $request->warehouseCode);
        }
        if ($request->filled('transactionType')) {
            $typeLabel = match ($request->transactionType) {
                'INITIAL' => 'Stock Awal',
                'IN' => 'Masuk (IN)',
                'OUT' => 'Keluar (OUT)',
                default => $request->transactionType,
            };
            $summary[] = 'Tipe Transaksi: '.$typeLabel;
        }
        if ($request->filled('startDate') || $request->filled('endDate')) {
            $start = $request->filled('startDate') ? Carbon::parse($request->startDate)->format('d-m-Y') : 'Awal';
            $end = $request->filled('endDate') ? Carbon::parse($request->endDate)->format('d-m-Y') : 'Sekarang';
            $summary[] = 'Periode: '.$start.' s/d '.$end;
        }

        return $summary;
    }
}
