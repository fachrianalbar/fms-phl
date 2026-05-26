<?php

namespace App\Http\Controllers\Inventory;

use App\Helpers\GenerateCode;
use App\Http\Controllers\Controller;
use App\Models\Inventory\Item;
use App\Models\Inventory\Warehouse;
use App\Models\StockTransaction;
use App\Services\Inventory\WarehouseService;
use App\Services\MenuService;
use Illuminate\Http\Request;
use Mpdf\Mpdf;
use Yajra\DataTables\DataTables;

class StockController extends Controller
{
    protected $title;

    protected $view;

    protected $menuSvc;

    protected $warehouseSvc;

    public function __construct(MenuService $menuSvc, WarehouseService $warehouseSvc)
    {
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
            $query = $this->stockSummaryQuery();

            return DataTables::of($query)
                ->addIndexColumn()
                ->filterColumn('DT_RowIndex', function ($query, $keyword) {
                    return $query;
                })
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && ! empty($request->search['value'])) {
                        $search = strtolower($request->search['value']);
                        $itemCodes = Item::query()
                            ->whereRaw('LOWER(code) LIKE ?', ['%'.$search.'%'])
                            ->orWhereRaw('LOWER(name) LIKE ?', ['%'.$search.'%'])
                            ->pluck('code')
                            ->all();
                        $warehouseCodes = Warehouse::query()
                            ->whereRaw('LOWER(name) LIKE ?', ['%'.$search.'%'])
                            ->pluck('code')
                            ->all();

                        $query->where(function ($q) use ($itemCodes, $search, $warehouseCodes) {
                            $q->whereRaw('LOWER(stock_transaction.itemCode) LIKE ?', ['%'.$search.'%'])
                                ->orWhereRaw('LOWER(stock_transaction.warehouseCode) LIKE ?', ['%'.$search.'%']);

                            if (! empty($itemCodes)) {
                                $q->orWhereIn('stock_transaction.itemCode', $itemCodes);
                            }

                            if (! empty($warehouseCodes)) {
                                $q->orWhereIn('stock_transaction.warehouseCode', $warehouseCodes);
                            }
                        });
                    }
                })
                ->orderColumn('itemCode', function ($query, $order) {
                    $query->orderBy('stock_transaction.itemCode', $order)
                        ->orderBy('stock_transaction.warehouseCode', 'asc');
                })
                ->addColumn('itemName', function ($row) {
                    return $row->item->name ?? '-';
                })
                ->addColumn('warehouseName', function ($row) {
                    return $row->warehouse->name ?? $row->warehouseCode;
                })
                ->editColumn('stock', function ($row) {
                    return $this->formatQuantity($row->stock);
                })
                ->addColumn('action', function ($row) {
                    $itemName = $row->item->name ?? '-';
                    $warehouseName = $row->warehouse->name ?? $row->warehouseCode;

                    return '<div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-primary btn-detail" 
                                    data-item-code="'.$row->itemCode.'" 
                                    data-warehouse-code="'.$row->warehouseCode.'"
                                    title="Detail Transaksi">
                                    <i class="mdi mdi-eye"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-warning btn-edit-stock-awal" 
                                    data-item-code="'.$row->itemCode.'" 
                                    data-item-name="'.e($itemName).'"
                                    data-warehouse-code="'.$row->warehouseCode.'"
                                    data-warehouse-name="'.e($warehouseName).'"
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
        $transactions = $this->stockDetailQuery($itemCode, $warehouseCode, ['item'])
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
                    'qtyIn' => (float) $item->qtyIn,
                    'qtyOut' => (float) $item->qtyOut,
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

        $warehouseCode = $request->warehouseCode;

        $stocks = $this->stockSummaryQuery($warehouseCode)
            ->get()
            ->map(function ($row) {
                $row->itemName = $row->item->name ?? '-';
                $row->warehouseName = $row->warehouse->name ?? $row->warehouseCode;

                return $row;
            });

        $reportData = [
            'stocks' => $stocks,
        ];

        $mpdf->WriteHTML(
            view($this->view.'report.stock-pdf')
                ->with('reportData', $reportData)
        );

        return $mpdf->Output('Laporan Stock.pdf', 'I');
    }

    private function stockSummaryQuery(?string $warehouseCode = null)
    {
        $query = StockTransaction::query()
            ->select('itemCode', 'warehouseCode')
            ->selectRaw('COALESCE(SUM(qtyIn), 0) as totalIn')
            ->selectRaw('COALESCE(SUM(qtyOut), 0) as totalOut')
            ->selectRaw('COALESCE(SUM(qtyIn), 0) - COALESCE(SUM(qtyOut), 0) as stock')
            ->with(['item', 'warehouse'])
            ->whereNotNull('warehouseCode')
            ->where('warehouseCode', '!=', '')
            ->groupBy('itemCode', 'warehouseCode')
            ->orderBy('itemCode', 'asc')
            ->orderBy('warehouseCode', 'asc');

        if ($warehouseCode) {
            $query->where('warehouseCode', $warehouseCode);
        }

        return $query;
    }

    private function formatQuantity($value): string
    {
        $quantity = (float) $value;
        $decimals = abs($quantity - round($quantity)) > 0.0001 ? 1 : 0;

        return number_format($quantity, $decimals, ',', '.');
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
            $query = $this->stockDetailNewestQuery($itemCode, $warehouseCode, ['item']);
            $runningBalances = $this->runningBalancesByTransactionId($itemCode, $warehouseCode);

            return DataTables::of($query)
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
                ->editColumn('date', function ($row) {
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
                    return $this->formatQuantity($row->qtyIn);
                })
                ->addColumn('qtyOut', function ($row) {
                    return $this->formatQuantity($row->qtyOut);
                })
                ->addColumn('currentStock', function ($row) use ($runningBalances) {
                    return $this->formatQuantity($runningBalances[$row->id] ?? 0);
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
        $item = Item::where('code', $itemCode)->first();
        $warehouse = Warehouse::where('code', $warehouseCode)->first();

        // Get all transactions for this item and warehouse
        $runningBalances = $this->runningBalancesByTransactionId($itemCode, $warehouseCode);

        $transactions = $this->stockDetailNewestQuery($itemCode, $warehouseCode, ['item'])
            ->get()
            ->map(function ($trans) use ($runningBalances) {
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
                    'qtyIn' => (float) $trans->qtyIn,
                    'qtyOut' => (float) $trans->qtyOut,
                    'currentStock' => $runningBalances[$trans->id] ?? 0,
                    'createdAt' => date('d/m/Y H:i', strtotime($trans->created_at)),
                ];
            });

        // Calculate totals
        $totalIn = collect($transactions)->sum('qtyIn');
        $totalOut = collect($transactions)->sum('qtyOut');
        $currentStock = $totalIn - $totalOut;

        $reportData = [
            'item' => $item,
            'warehouse' => $warehouse,
            'transactions' => $transactions,
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

    private function stockDetailQuery(string $itemCode, string $warehouseCode, array $with = [])
    {
        $query = StockTransaction::with($with)
            ->where('itemCode', $itemCode)
            ->where('warehouseCode', $warehouseCode);

        return $this->orderStockDetailChronologically($query);
    }

    private function stockDetailNewestQuery(string $itemCode, string $warehouseCode, array $with = [])
    {
        $query = StockTransaction::with($with)
            ->where('itemCode', $itemCode)
            ->where('warehouseCode', $warehouseCode);

        return $this->orderStockDetailNewestFirst($query);
    }

    private function orderStockDetailChronologically($query)
    {
        return $query
            ->orderByRaw('CASE WHEN stock_transaction.date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('stock_transaction.date', 'asc')
            ->orderBy('stock_transaction.created_at', 'asc')
            ->orderBy('stock_transaction.id', 'asc');
    }

    private function orderStockDetailNewestFirst($query)
    {
        return $query
            ->orderByRaw('CASE WHEN stock_transaction.date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('stock_transaction.date', 'desc')
            ->orderBy('stock_transaction.created_at', 'desc')
            ->orderBy('stock_transaction.id', 'desc');
    }

    private function runningBalancesByTransactionId(string $itemCode, string $warehouseCode): array
    {
        $runningBalance = 0;
        $balances = [];

        $this->stockDetailQuery($itemCode, $warehouseCode)
            ->get(['id', 'qtyIn', 'qtyOut'])
            ->each(function ($transaction) use (&$runningBalance, &$balances) {
                $runningBalance += (float) $transaction->qtyIn - (float) $transaction->qtyOut;
                $balances[$transaction->id] = $runningBalance;
            });

        return $balances;
    }
}
