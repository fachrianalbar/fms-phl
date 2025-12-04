<?php

namespace App\Http\Controllers\Inventory;

use App\Helpers\FilterHelper;
use App\Helpers\GenerateCode;
use App\Http\Controllers\Controller;
use App\Models\Inventory\Stock;
use App\Models\Inventory\Warehouse;
use App\Models\Purchasing\PurchaseDetail;
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

        return view($this->view . 'index')
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
                    DB::raw('COALESCE(SUM(' . $prefix . 'stock_transaction.qtyIn), 0) as totalIn'),
                    DB::raw('COALESCE(SUM(' . $prefix . 'stock_transaction.qtyOut), 0) as totalOut'),
                    DB::raw('COALESCE(SUM(' . $prefix . 'stock_transaction.qtyIn), 0) - COALESCE(SUM(' . $prefix . 'stock_transaction.qtyOut), 0) as stock')
                ])
                ->crossJoin(DB::raw($prefix . 'warehouse'))
                ->whereNull('warehouse.deleted_at')
                ->whereNull('item.deleted_at')
                ->leftJoin(DB::raw($prefix . 'stock_transaction'), function ($join) use ($prefix) {
                    $join->on(DB::raw('CAST(' . $prefix . 'stock_transaction.itemCode AS CHAR)'), '=', DB::raw('CAST(' . $prefix . 'item.code AS CHAR)'))
                        ->on(DB::raw('CAST(' . $prefix . 'stock_transaction.warehouseCode AS CHAR)'), '=', DB::raw('CAST(' . $prefix . 'warehouse.code AS CHAR)'));
                })
                ->whereNull('stock_transaction.deleted_at')
                ->groupBy('item.code', 'item.name', 'warehouse.code', 'warehouse.name');

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
                            $q->whereRaw('LOWER(' . $prefix . 'item.code) LIKE ?', ['%' . $search . '%'])
                                ->orWhereRaw('LOWER(' . $prefix . 'item.name) LIKE ?', ['%' . $search . '%'])
                                ->orWhereRaw('LOWER(' . $prefix . 'warehouse.name) LIKE ?', ['%' . $search . '%']);
                        });
                    }
                })
                ->addColumn('action', function ($row) {
                    return '<div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-primary btn-detail" 
                                    data-item-code="' . $row->itemCode . '" 
                                    data-warehouse-code="' . $row->warehouseCode . '"
                                    title="Detail Transaksi">
                                    <i class="mdi mdi-eye"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-warning btn-edit-stock-awal" 
                                    data-item-code="' . $row->itemCode . '" 
                                    data-item-name="' . $row->itemName . '"
                                    data-warehouse-code="' . $row->warehouseCode . '"
                                    data-warehouse-name="' . $row->warehouseName . '"
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
                'orientation' => 'L',
                'format' => 'A4',
                'tempDir' => storage_path('app/mpdf-temp'),
            ]
        );

        $warehouseCode = $request->warehouseCode;

        // Get data per warehouse
        if ($warehouseCode) {
            $warehouses = Warehouse::where('code', $warehouseCode)->whereNull('deleted_at')->get();
        } else {
            $warehouses = Warehouse::whereNull('deleted_at')->get();
        }

        $reportData = [];

        foreach ($warehouses as $warehouse) {
            $prefix = DB::getTablePrefix();

            $stocks = DB::table('item')
                ->select([
                    'item.code as itemCode',
                    'item.name as itemName',
                    DB::raw('COALESCE(SUM(' . $prefix . 'stock_transaction.qtyIn), 0) as totalIn'),
                    DB::raw('COALESCE(SUM(' . $prefix . 'stock_transaction.qtyOut), 0) as totalOut'),
                    DB::raw('COALESCE(SUM(' . $prefix . 'stock_transaction.qtyIn), 0) - COALESCE(SUM(' . $prefix . 'stock_transaction.qtyOut), 0) as stock')
                ])
                ->leftJoin(DB::raw($prefix . 'stock_transaction'), function ($join) use ($warehouse, $prefix) {
                    $join->on(DB::raw('CAST(' . $prefix . 'stock_transaction.itemCode AS CHAR)'), '=', DB::raw('CAST(' . $prefix . 'item.code AS CHAR)'))
                        ->where(DB::raw('CAST(' . $prefix . 'stock_transaction.warehouseCode AS CHAR)'), '=', $warehouse->code);
                })
                ->groupBy('item.code', 'item.name')
                ->havingRaw('COALESCE(SUM(' . $prefix . 'stock_transaction.qtyIn), 0) - COALESCE(SUM(' . $prefix . 'stock_transaction.qtyOut), 0) > 0')
                ->get();

            $reportData[] = [
                'warehouse' => $warehouse,
                'stocks' => $stocks,
            ];
        }

        $mpdf->WriteHTML(
            view($this->view . 'report.stock-pdf')
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

            // Cek apakah sudah ada transaksi selain INITIAL
            $hasOtherTransaction = StockTransaction::where('itemCode', $itemCode)
                ->where('warehouseCode', $warehouseCode)
                ->where('transactionType', '!=', 'INITIAL')
                ->exists();

            if ($hasOtherTransaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat mengubah stock awal karena sudah ada transaksi lain untuk item ini',
                ], 422);
            }

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
                    'transactionCode' => 'INITIAL-' . $date . $randomDigits,
                    'transactionDetailCode' => 'INITIALD-' . $itemCode . $randomDigits,
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
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }
}
