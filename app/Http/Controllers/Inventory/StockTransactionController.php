<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Warehouse;
use App\Models\StockTransaction;
use App\Services\Inventory\StockTransactionService;
use App\Services\Inventory\WarehouseService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        return view($this->view.'index')
            ->with('view', $this->view)
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

            $data = Warehouse::select('warehouse.*')
                ->selectRaw('COALESCE(SUM('.$alias.'.qtyIn), 0) as totalIn')
                ->selectRaw('COALESCE(SUM('.$alias.'.qtyOut), 0) as totalOut')
                ->leftJoin('stock_transaction as st', function ($join) use ($warehouseTable, $alias) {
                    $join->on(DB::raw($warehouseTable.'.code COLLATE utf8mb4_unicode_ci'), '=', DB::raw($alias.'.warehouseCode COLLATE utf8mb4_unicode_ci'))
                        ->whereNull('st.deleted_at');
                })
                ->whereNull('warehouse.deleted_at')
                ->groupBy('warehouse.id', 'warehouse.code', 'warehouse.name', 'warehouse.address', 'warehouse.created_at', 'warehouse.updated_at', 'warehouse.deleted_at');

            return Datatables::of($data)
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

        // Summary per item
        $summary = StockTransaction::select('itemCode')
            ->selectRaw('SUM(qtyIn) as totalIn')
            ->selectRaw('SUM(qtyOut) as totalOut')
            ->where('warehouseCode', $warehouseCode)
            ->groupBy('itemCode')
            ->with('item')
            ->get()
            ->map(function ($item) {
                return [
                    'itemCode' => $item->itemCode,
                    'itemName' => $item->item->name ?? '-',
                    'totalIn' => (int) $item->totalIn,
                    'totalOut' => (int) $item->totalOut,
                    'stock' => (int) $item->totalIn - (int) $item->totalOut,
                ];
            });

        // Detail transaksi
        $details = StockTransaction::with(['item'])
            ->where('warehouseCode', $warehouseCode)
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
                    'qtyIn' => (int) $item->qtyIn,
                    'qtyOut' => (int) $item->qtyOut,
                ];
            });

        return response()->json([
            'success' => true,
            'summary' => $summary,
            'details' => $details,
        ]);
    }

    /**
     * Generate PDF report
     */
    public function pdfStockTransaction(Request $request)
    {
        $warehouseCode = $request->warehouseCode;

        // Get all warehouses or specific one
        if ($warehouseCode) {
            $warehouses = Warehouse::where('code', $warehouseCode)->get();
        } else {
            $warehouses = Warehouse::all();
        }

        $reportData = [];

        foreach ($warehouses as $warehouse) {
            // Summary per item
            $summary = StockTransaction::select('itemCode')
                ->selectRaw('SUM(qtyIn) as totalIn')
                ->selectRaw('SUM(qtyOut) as totalOut')
                ->where('warehouseCode', $warehouse->code)
                ->groupBy('itemCode')
                ->with('item')
                ->get()
                ->map(function ($item) {
                    return [
                        'itemCode' => $item->itemCode,
                        'itemName' => $item->item->name ?? '-',
                        'totalIn' => (int) $item->totalIn,
                        'totalOut' => (int) $item->totalOut,
                        'stock' => (int) $item->totalIn - (int) $item->totalOut,
                    ];
                });

            // Detail transaksi
            $details = StockTransaction::with(['item'])
                ->where('warehouseCode', $warehouseCode)
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($item) {
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
                        'qtyIn' => (int) $item->qtyIn,
                        'qtyOut' => (int) $item->qtyOut,
                    ];
                });

            // Total summary
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

        $mpdf = new Mpdf([
            'orientation' => 'P',
            'format' => [215, 330],
            'tempDir' => storage_path('app/mpdf-temp'),
        ]);

        $mpdf->WriteHTML(
            view($this->view.'report.transaction-stock-pdf')
                ->with('reportData', $reportData)
                ->with('printDate', Carbon::now()->format('d-m-Y H:i'))
        );

        return $mpdf->Output('Laporan Transaksi Stock.pdf', 'I');
    }
}
