<?php

namespace App\Http\Controllers\Inventory;

use App\Helpers\FilterHelper;
use App\Helpers\GenerateCode;
use App\Http\Controllers\Controller;
use App\Models\Inventory\Stock;
use App\Models\Inventory\Warehouse;
use App\Models\StockTransaction;
use App\Services\Inventory\StockService;
use App\Services\Inventory\WarehouseService;
use App\Services\MenuService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;
use Yajra\DataTables\DataTables;

class StockController extends Controller
{
    protected $service;

    protected $title;

    protected $view;

    protected $menuSvc;

    protected $warehouseSvc;

    public function __construct(StockService $stockSvc, MenuService $menuSvc, WarehouseService $warehouseSvc)
    {
        $this->service = $stockSvc;
        $this->title = 'Stock';
        $this->view = 'inventory.stock.';
        $this->menuSvc = $menuSvc;
        $this->warehouseSvc = $warehouseSvc;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $warehouse = $this->warehouseSvc->findAll();

        return view($this->view.'index')
            ->with('view', $this->view)
            ->with('warehouse', $warehouse)
            ->with('title', $this->title);
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $prefix = DB::getTablePrefix();

            // Use Query Builder with joins to get real-time stock per item per warehouse (avoid Eloquent aliasing/soft delete mismatches)
            $query = DB::table('item')
                ->select([
                    'item.code as itemCode',
                    'item.name as itemName',
                    'warehouse.code as warehouseCode',
                    'warehouse.name as warehouseName',
                    DB::raw('COALESCE(SUM('.$prefix.'stock_transaction.qtyIn), 0) as totalIn'),
                    DB::raw('COALESCE(SUM('.$prefix.'stock_transaction.qtyOut), 0) as totalOut'),
                    DB::raw('COALESCE(SUM('.$prefix.'stock_transaction.qtyIn), 0) - COALESCE(SUM('.$prefix.'stock_transaction.qtyOut), 0) as stock'),
                ])
                ->crossJoin(DB::raw($prefix.'warehouse'))
                ->whereNull('warehouse.deleted_at')
                ->whereNull('item.deleted_at')
                ->leftJoin(DB::raw($prefix.'stock_transaction'), function ($join) use ($prefix) {
                    $join->on(DB::raw('CAST('.$prefix.'stock_transaction.itemCode AS CHAR)'), '=', DB::raw('CAST('.$prefix.'item.code AS CHAR)'))
                        ->on(DB::raw('CAST('.$prefix.'stock_transaction.warehouseCode AS CHAR)'), '=', DB::raw('CAST('.$prefix.'warehouse.code AS CHAR)'));
                })
                ->whereNull('stock_transaction.deleted_at')
                ->groupBy('item.code', 'item.name', 'warehouse.code', 'warehouse.name')
                ->orderBy('item.name', 'asc');

            // No FilterHelper (Query Builder), apply search filter manually via DataTables filter closure

            return Datatables::of($query)
                ->addIndexColumn()
                ->filterColumn('DT_RowIndex', function ($query, $keyword) {
                    return $query;
                })
                ->filter(function ($query) use ($request, $prefix) {
                    if ($request->has('search') && ! empty($request->search['value'])) {
                        $search = strtolower($request->search['value']);
                        $query->where(function ($q) use ($search, $prefix) {
                            $q->whereRaw('LOWER('.$prefix.'item.code) LIKE ?', ['%'.$search.'%'])
                                ->orWhereRaw('LOWER('.$prefix.'item.name) LIKE ?', ['%'.$search.'%'])
                                ->orWhereRaw('LOWER('.$prefix.'warehouse.name) LIKE ?', ['%'.$search.'%']);
                        });
                    }
                })
                ->editColumn('stock', function ($row) {
                    return (int) $row->stock;
                })
                ->addColumn('action', function ($row) {
                    return '<div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-primary btn-detail" 
                                    data-item-code="'.$row->itemCode.'" 
                                    data-warehouse-code="'.$row->warehouseCode.'"
                                    title="Detail Transaksi">
                                    <i class="mdi mdi-eye"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-warning btn-edit-stock-awal" 
                                    data-item-code="'.$row->itemCode.'" 
                                    data-item-name="'.$row->itemName.'"
                                    data-warehouse-code="'.$row->warehouseCode.'"
                                    data-warehouse-name="'.$row->warehouseName.'"
                                    title="Edit Stock Awal">
                                    <i class="mdi mdi-database-edit"></i>
                                </button>
                            </div>';
                })
                ->rawColumns(['action'])
                ->toJson();
        }
    }

    public function getItemDetail(Request $request)
    {
        $itemCode = $request->itemCode;
        $warehouseCode = $request->warehouseCode;

        // Get all transactions for this item and warehouse
        $transactions = StockTransaction::with(['item'])
            ->where('itemCode', $itemCode)
            ->where('warehouseCode', $warehouseCode)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($item) {
                // Map transaction type to readable labels
                $typeLabel = $item->transactionType ?? '-';

                if ($item->transactionType === 'INITIAL') {
                    $typeLabel = 'Stock Awal';
                } elseif ($item->transactionType === 'IN') {
                    $typeLabel = 'Pembelian';
                } elseif ($item->transactionType === 'OUT') {
                    $typeLabel = 'Pemeliharaan';
                }

                return [
                    'date' => $item->date ? date('d/m/Y', strtotime($item->date)) : '-',
                    'transactionCode' => $item->transactionCode ?? '-',
                    'transactionType' => $typeLabel,
                    'itemCode' => $item->itemCode,
                    'itemName' => $item->item->name ?? '-',
                    'qtyIn' => (int) $item->qtyIn,
                    'qtyOut' => (int) $item->qtyOut,
                    'createdAt' => date('d/m/Y H:i', strtotime($item->created_at)),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }

    public function pdfStock(Request $request)
    {
        $mpdf = new Mpdf(
            [
                'orientation' => 'P',
                'format' => 'A4',
                'tempDir' => storage_path('app/mpdf-temp'),
            ]
        );

        $prefix = DB::getTablePrefix();
        $warehouseCode = $request->warehouseCode;

        // Use same query as datatable for consistency
        $query = DB::table('item')
            ->select([
                'item.code as itemCode',
                'item.name as itemName',
                'warehouse.code as warehouseCode',
                'warehouse.name as warehouseName',
                DB::raw('COALESCE(SUM('.$prefix.'stock_transaction.qtyIn), 0) as totalIn'),
                DB::raw('COALESCE(SUM('.$prefix.'stock_transaction.qtyOut), 0) as totalOut'),
                DB::raw('COALESCE(SUM('.$prefix.'stock_transaction.qtyIn), 0) - COALESCE(SUM('.$prefix.'stock_transaction.qtyOut), 0) as stock'),
            ])
            ->crossJoin(DB::raw($prefix.'warehouse'))
            ->whereNull('warehouse.deleted_at')
            ->whereNull('item.deleted_at')
            ->leftJoin(DB::raw($prefix.'stock_transaction'), function ($join) use ($prefix) {
                $join->on(DB::raw('CAST('.$prefix.'stock_transaction.itemCode AS CHAR)'), '=', DB::raw('CAST('.$prefix.'item.code AS CHAR)'))
                    ->on(DB::raw('CAST('.$prefix.'stock_transaction.warehouseCode AS CHAR)'), '=', DB::raw('CAST('.$prefix.'warehouse.code AS CHAR)'));
            })
            ->whereNull('stock_transaction.deleted_at')
            ->groupBy('item.code', 'item.name', 'warehouse.code', 'warehouse.name')
            ->orderBy('item.name', 'asc');

        // Filter by warehouse if specified
        if ($warehouseCode) {
            $query->where('warehouse.code', $warehouseCode);
        }

        $stocks = $query->get();

        $reportData = [
            'stocks' => $stocks,
        ];

        $mpdf->WriteHTML(
            view($this->view.'report.stock-pdf')
                ->with('reportData', $reportData)
        );

        return $mpdf->Output('Laporan Stock.pdf', 'I');
    }

    public function updateInitialStock(Request $request)
    {
        try {
            $request->validate([
                'itemCode' => 'required|string',
                'warehouseCode' => 'required|string',
                'qty' => 'required|numeric|min:0',
            ]);

            $itemCode = $request->itemCode;
            $warehouseCode = $request->warehouseCode;
            $qty = $request->qty;

            // Cek apakah sudah ada INITIAL transaction
            $existingInitial = StockTransaction::where('itemCode', $itemCode)
                ->where('warehouseCode', $warehouseCode)
                ->where('transactionType', 'INITIAL')
                ->first();

            $date = now()->format('ymd');
            $randomDigits = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

            if ($existingInitial) {
                // Update existing INITIAL transaction
                $existingInitial->update([
                    'qtyIn' => $qty,
                    'qtyOut' => 0,
                    'date' => now(),
                ]);
            } else {
                // Insert new INITIAL transaction
                StockTransaction::create([
                    'code' => GenerateCode::generateCode('FST'),
                    'itemCode' => $itemCode,
                    'warehouseCode' => $warehouseCode,
                    'qtyIn' => $qty,
                    'qtyOut' => 0,
                    'transactionCode' => 'INITIAL-'.$date.$randomDigits,
                    'transactionDetailCode' => 'INITIALD-'.$itemCode.$randomDigits,
                    'transactionType' => 'INITIAL',
                    'date' => now(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Stock awal berhasil diperbarui',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }

    public function getDetailDatatable(Request $request)
    {
        $itemCode = $request->itemCode;
        $warehouseCode = $request->warehouseCode;

        if ($request->ajax()) {
            // Query ordered by newest first for display
            $query = StockTransaction::with(['item'])
                ->where('itemCode', $itemCode)
                ->where('warehouseCode', $warehouseCode)
                ->orderBy('created_at', 'desc');

            // Get all transactions in ascending order to calculate running balance correctly
            $allTransactions = StockTransaction::where('itemCode', $itemCode)
                ->where('warehouseCode', $warehouseCode)
                ->orderBy('created_at', 'asc')
                ->get();

            return Datatables::of($query)
                ->addIndexColumn()
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && ! empty($request->search['value'])) {
                        $search = strtolower($request->search['value']);
                        $query->where(function ($q) use ($search) {
                            $q->whereRaw('LOWER(itemCode) LIKE ?', ['%'.$search.'%'])
                                ->orWhereRaw('LOWER(transactionCode) LIKE ?', ['%'.$search.'%'])
                                ->orWhereRaw('LOWER(transactionType) LIKE ?', ['%'.$search.'%']);
                        });
                    }
                })
                ->addColumn('date', function ($row) {
                    return $row->date ? date('d/m/Y', strtotime($row->date)) : '-';
                })
                ->addColumn('transactionType', function ($row) {
                    $typeLabel = $row->transactionType ?? '-';

                    if ($row->transactionType === 'INITIAL') {
                        $typeLabel = 'Stock Awal';
                    } elseif ($row->transactionType === 'IN') {
                        $typeLabel = 'Pembelian';
                    } elseif ($row->transactionType === 'OUT') {
                        $typeLabel = 'Pemeliharaan';
                    }

                    return $typeLabel;
                })
                ->addColumn('transactionTypeHtml', function ($row) {
                    $typeLabel = $row->transactionType ?? '-';
                    $badgeClass = 'bg-secondary';

                    if ($row->transactionType === 'INITIAL') {
                        $typeLabel = 'Stock Awal';
                        $badgeClass = 'bg-info';
                    } elseif ($row->transactionType === 'IN') {
                        $typeLabel = 'Pembelian';
                        $badgeClass = 'bg-success';
                    } elseif ($row->transactionType === 'OUT') {
                        $typeLabel = 'Pemeliharaan';
                        $badgeClass = 'bg-warning text-dark';
                    }

                    return '<span class="badge badge-status '.$badgeClass.'">'.$typeLabel.'</span>';
                })
                ->addColumn('itemName', function ($row) {
                    return $row->item->name ?? '-';
                })
                ->addColumn('qtyIn', function ($row) {
                    return number_format((int) $row->qtyIn, 0, ',', '.');
                })
                ->addColumn('qtyOut', function ($row) {
                    return number_format((int) $row->qtyOut, 0, ',', '.');
                })
                ->addColumn('currentStock', function ($row) use ($allTransactions) {
                    // Calculate running balance up to this transaction
                    $runningBalance = 0;
                    foreach ($allTransactions as $transaction) {
                        $runningBalance += $transaction->qtyIn - $transaction->qtyOut;
                        if ($transaction->id === $row->id) {
                            break;
                        }
                    }

                    return number_format($runningBalance, 0, ',', '.');
                })
                ->addColumn('createdAt', function ($row) {
                    return date('d/m/Y H:i', strtotime($row->created_at));
                })
                ->rawColumns(['transactionTypeHtml'])
                ->toJson();
        }
    }

    public function getDetailSummary(Request $request)
    {
        $itemCode = $request->itemCode;
        $warehouseCode = $request->warehouseCode;

        $transactions = StockTransaction::where('itemCode', $itemCode)
            ->where('warehouseCode', $warehouseCode)
            ->get();

        $totalIn = $transactions->sum('qtyIn');
        $totalOut = $transactions->sum('qtyOut');
        $currentStock = $totalIn - $totalOut;

        return response()->json([
            'success' => true,
            'totalIn' => (int) $totalIn,
            'totalOut' => (int) $totalOut,
            'currentStock' => (int) $currentStock,
        ]);
    }

    public function pdfStockDetail(Request $request)
    {
        $mpdf = new Mpdf(
            [
                'orientation' => 'P',
                'format' => 'A4',
                'tempDir' => storage_path('app/mpdf-temp'),
            ]
        );

        $itemCode = $request->itemCode;
        $warehouseCode = $request->warehouseCode;

        // Get item info
        $item = \App\Models\Inventory\Item::where('code', $itemCode)->first();
        $warehouse = Warehouse::where('code', $warehouseCode)->first();

        // Get all transactions for this item and warehouse
        $transactions = StockTransaction::with(['item'])
            ->where('itemCode', $itemCode)
            ->where('warehouseCode', $warehouseCode)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($trans) {
                $typeLabel = $trans->transactionType ?? '-';

                if ($trans->transactionType === 'INITIAL') {
                    $typeLabel = 'Stock Awal';
                } elseif ($trans->transactionType === 'IN') {
                    $typeLabel = 'Pembelian';
                } elseif ($trans->transactionType === 'OUT') {
                    $typeLabel = 'Pemeliharaan';
                }

                return [
                    'id' => $trans->id,
                    'date' => $trans->date ? date('d/m/Y', strtotime($trans->date)) : '-',
                    'transactionCode' => $trans->transactionCode ?? '-',
                    'transactionType' => $typeLabel,
                    'qtyIn' => (int) $trans->qtyIn,
                    'qtyOut' => (int) $trans->qtyOut,
                    'createdAt' => date('d/m/Y H:i', strtotime($trans->created_at)),
                ];
            });

        // Calculate running balance for each transaction
        $transactionsWithBalance = [];
        $runningBalance = 0;
        foreach ($transactions as $trans) {
            $runningBalance += $trans['qtyIn'] - $trans['qtyOut'];
            $trans['currentStock'] = $runningBalance;
            $transactionsWithBalance[] = $trans;
        }

        // Calculate totals
        $totalIn = collect($transactions)->sum('qtyIn');
        $totalOut = collect($transactions)->sum('qtyOut');
        $currentStock = $totalIn - $totalOut;

        $reportData = [
            'item' => $item,
            'warehouse' => $warehouse,
            'transactions' => $transactionsWithBalance,
            'totalIn' => $totalIn,
            'totalOut' => $totalOut,
            'currentStock' => $currentStock,
        ];

        $mpdf->WriteHTML(
            view($this->view.'report.stock-detail-pdf')
                ->with('reportData', $reportData)
        );

        $filename = 'Detail_Stock_'.$itemCode.'_'.date('Y-m-d_His').'.pdf';

        return $mpdf->Output($filename, 'I');
    }
}
