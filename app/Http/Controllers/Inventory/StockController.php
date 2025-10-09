<?php

namespace App\Http\Controllers\Inventory;

use App\Helpers\FilterHelper;
use App\Helpers\GenerateCode;
use App\Http\Controllers\Controller;
use App\Models\Inventory\Stock;
use App\Models\Inventory\Warehouse;
use App\Models\Purchasing\PurchaseDetail;
use App\Models\StockTransaction;
use App\Services\MenuService;
use App\Services\Inventory\StockService;
use App\Services\Inventory\WarehouseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Mpdf\Mpdf;

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
        $this->title = "Stock";
        $this->view = "inventory.stock.";
        $this->menuSvc = $menuSvc;
        $this->warehouseSvc = $warehouseSvc;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stock = $this->service->findAll();
        $warehouse = $this->warehouseSvc->findAll();

        return view($this->view . 'index')
            ->with('view', $this->view)
            ->with('stock', $stock)
            ->with('warehouse', $warehouse)
            ->with('title', $this->title);
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->datatable();

            // Definisikan kolom filter dengan alias
            $filters = [
                'itemCode' => $request->itemCode,
            ];

            // Hubungkan alias ke relasi dan kolom yang sesuai
            $relations = [];

            $dateFilters = [];

            $data = FilterHelper::applyFilters($data, $filters, $relations, $dateFilters);

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('total', function ($row) {
                    $total = 0;
                    if (isset($row->stock)) {
                        $total = $row->stock->stockIn - $row->stock->stockOut;
                    }

                    return $total;
                })
                ->addColumn('action', function ($row) {
                    $action = '<button type="button" class="btn btn-sm btn-info btn-edit-stock" 
                                data-item-code="' . $row->code . '" 
                                data-item-name="' . ($row->name ?? '') . '"
                                title="Edit Stock Awal">
                                <i class="mdi mdi-pencil"></i>
                              </button>';

                    if (isset($row->hasPurchase) || isset($row->hasMaintenance)) {
                        $action = '';
                    }

                    return $action;
                })
                ->rawColumns(['item.name', 'total', 'action'])
                ->toJson();
        }
    }

    public function pdfStock(Request $request)
    {
        $mpdf = new Mpdf(
            [
                'orientation' => 'P',
                'format' => [215, 330],
                'tempDir' => storage_path('app/mpdf-temp')
            ]
        );

        $data = $this->service->findAll();

        $mpdf->WriteHTML(
            view($this->view . 'report.stock-pdf')
                ->with('data', $data)
        );

        return $mpdf->Output('Laporan Stock.pdf', 'I');
    }

    public function updateInitialStock(Request $request)
    {
        try {
            $request->validate([
                'itemCode' => 'required|string',
                'qty' => 'required|numeric|min:0',
            ]);

            $itemCode = $request->itemCode;
            $qty = $request->qty;
            $warehouseCode = Warehouse::first()->code; // Ambil warehouseCode dari warehouse pertama

            // Cek apakah sudah ada transaksi IN atau OUT
            $hasInOutTransaction = StockTransaction::where('itemCode', $itemCode)
                ->where('warehouseCode', $warehouseCode)
                ->whereIn('transactionType', ['IN', 'OUT'])
                ->exists();

            if ($hasInOutTransaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat mengubah stock awal karena sudah ada transaksi masuk/keluar untuk item ini'
                ], 422);
            }

            // Cek apakah sudah ada INITIAL transaction
            $existingInitial = StockTransaction::where('itemCode', $itemCode)
                ->where('warehouseCode', $warehouseCode)
                ->where('transactionType', 'INITIAL')
                ->first();

            if ($existingInitial) {
                // Update existing INITIAL transaction
                StockTransaction::where('id', $existingInitial->id)
                    ->update([
                        'qtyIn' => $qty,
                        'qtyOut' => 0,
                        'date' => now(),
                    ]);
                PurchaseDetail::where('itemCode', $itemCode)->whereNull('purchaseCode')
                    ->update([
                        'qty' => $qty,
                        'receivedQty' => $qty,
                    ]);
            } else {
                // Insert new INITIAL transaction
                StockTransaction::create([
                    'code' => GenerateCode::generateCode('FST'),
                    'itemCode' => $itemCode,
                    'warehouseCode' => $warehouseCode,
                    'qtyIn' => $qty,
                    'qtyOut' => 0,
                    'transactionCode' => null,
                    'transactionDetailCode' => null,
                    'transactionType' => 'INITIAL',
                    'date' => now()
                ]);

                PurchaseDetail::create([
                    'code' => GenerateCode::generateCode('FPD', true),
                    'purchaseCode' => null,
                    'itemCode' => $itemCode,
                    'qty' => $qty,
                    'receivedQty' => $qty,
                    'status' => 1,
                    'price' => null,
                    'qtyUsed' => 0,
                ]);
            }

            // Update atau insert ke table stock
            $existingStock = Stock::where('itemCode', $itemCode)
                ->first();

            if ($existingStock) {
                Stock::where('id', $existingStock->id)
                    ->update([
                        'stockIn' => $qty,
                        'stockOut' => 0,
                    ]);
            } else {
                Stock::create([
                    'itemCode' => $itemCode,
                    'stockIn' => $qty,
                    'stockOut' => 0,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Stock awal berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
