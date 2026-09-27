<?php

namespace App\Http\Controllers\Warehouse;

use App\Helpers\FilterHelper;
use App\Helpers\GenerateCode;
use App\Http\Controllers\Controller;
use App\Models\Inventory\Item;
use App\Models\Inventory\Stock;
use App\Models\Purchasing\Purchase;
use App\Models\Purchasing\PurchaseDetail;
use App\Models\StockTransaction;
use App\Models\Warehouse\Maintenance;
use App\Models\Warehouse\MaintenanceDetail;
use App\Models\Warehouse\MaintenanceFifo;
use App\Services\Inventory\StockService;
use App\Services\Inventory\WarehouseService;

use App\Services\Master\FleetService;

use App\Services\MenuService;
use App\Services\UniqueCodeService;
use App\Services\Warehouse\MaintenanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Mpdf\Mpdf;
use Yajra\DataTables\DataTables;

class MaintenanceController extends Controller
{
    protected $service;

    protected $fleetSvc;

    protected $stockSvc;

    protected $warehouseSvc;

    protected $title;

    protected $view;

    protected $menuSvc;

    public function __construct(MaintenanceService $maintenanceService, FleetService $fleetSvc, StockService $stockSvc, WarehouseService $warehouseSvc, MenuService $menuSvc)
    {
        $this->service = $maintenanceService;
        $this->fleetSvc = $fleetSvc;
        $this->stockSvc = $stockSvc;
        $this->warehouseSvc = $warehouseSvc;
        $this->title = 'Maintenance';
        $this->menuSvc = $menuSvc->getByName('Maintenance');
        $this->title = Auth::user()->languange == 'en' ? $this->menuSvc->name : $this->menuSvc->nama;
        $this->view = 'warehouse.maintenance.';
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $fleet = $this->fleetSvc->findAll();
        $stock = $this->stockSvc->findAll();

        return view($this->view . 'index')
            ->with('view', $this->view)
            ->with('fleet', $fleet)
            ->with('stock', $stock)
            ->with('title', $this->title);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $fleet = $this->fleetSvc->findAll();
        $warehouse = $this->warehouseSvc->findAll();

        return view($this->view . 'create')
            ->with('view', $this->view)
            ->with('fleet', $fleet)
            ->with('warehouse', $warehouse)
            ->with('title', $this->title);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'code' => 'required',
            'fleetCode' => 'required',
            'warehouseCode' => 'required',
            'date' => 'required',
            'time' => 'required',
            'purchase_ids' => 'required|array|min:1',
            'purchase_ids.*' => 'required|exists:purchase,id',
        ]);
        if ($validator->fails()) {
            return redirect()->route($this->view . 'index')->with('fail', $validator->errors()->all()[0]);
        }

        $purchaseIds = array_filter((array) $request->input('purchase_ids', []));
        $usedPurchaseIds = $this->getUsedPurchaseIds();
        $conflictingIds = array_intersect($purchaseIds, $usedPurchaseIds);

        if (! empty($conflictingIds)) {
            $conflictingCodes = Purchase::whereIn('id', $conflictingIds)->pluck('code')->implode(', ');

            return redirect()->back()
                ->withInput()
                ->with('fail', "Purchase Order {$conflictingCodes} sudah pernah digunakan pada maintenance lain.");
        }
        try {
            $code = app(UniqueCodeService::class)->runWithDuplicateRetry(function () use ($request) {
                return DB::transaction(fn () => $this->service->store($request, $this->title));
            });

            $redirect = redirect()->route($this->view . 'index')
                ->with('success', $this->title . ' ' . __('general.data_was_save_successfully'));

            return $code->wasChanged ? $redirect->with('code_replaced', $code->flashPayload()) : $redirect;
        } catch (\Throwable $th) {
            return redirect()->route($this->view . 'index')->with('fail', 'Line : ' . $th->getLine() . '<br>' . $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = $this->service->getById($id);

        if (! $data) {
            return response()->json(['error' => 'Data not found'], 404);
        }

        // Load relationships
        $data->load(['fleet', 'warehouse', 'details.item', 'purchases.supplier']);

        return response()->json($data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data = $this->service->getById($id);

        if (! $data) {
            return redirect()->route($this->view . 'index')->with('fail', 'Data not found');
        }

        $fleet = $this->fleetSvc->findAll();
        $warehouse = $this->warehouseSvc->findAll();

        $data->load(['purchases', 'purchases.supplier']);

        return view($this->view . 'edit')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('fleet', $fleet)
            ->with('warehouse', $warehouse)
            ->with('data', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            // 'code' => 'required',
            'fleetCode' => 'required',
            'date' => 'required',
            'time' => 'required',
            'purchase_ids' => 'required|array|min:1',
            'purchase_ids.*' => 'required|exists:purchase,id',
        ]);

        if ($validator->fails()) {
            return redirect()->route($this->view . 'index')->with('fail', $validator->errors()->all()[0]);
        }

        $purchaseIds = array_filter((array) $request->input('purchase_ids', []));
        $usedPurchaseIds = $this->getUsedPurchaseIds($id);
        $conflictingIds = array_intersect($purchaseIds, $usedPurchaseIds);

        if (! empty($conflictingIds)) {
            $conflictingCodes = Purchase::whereIn('id', $conflictingIds)->pluck('code')->implode(', ');

            return redirect()->back()
                ->withInput()
                ->with('fail', "Purchase Order {$conflictingCodes} sudah pernah digunakan pada maintenance lain.");
        }

        try {
            DB::beginTransaction();

            $this->service->update($request, $id, $this->title);

            DB::commit();

            return redirect()->route($this->view . 'index')->with('success', $this->title . ' ' . __('general.data_was_update_succesfully'));
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->route($this->view . 'index')->with('fail', 'Line : ' . $th->getLine() . '<br>' . $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->service->destroy($id, $this->title);

        return redirect()->route($this->view . 'index')->with('success', 'Delete Data Success');
    }

    public function deleteMaintenanceDetail($id)
    {
        $md = MaintenanceDetail::where('id', $id)->firstOrFail();
        $isJasa = optional($md->item)->type === \App\Models\Inventory\Item::TYPE_JASA;

        if ($isJasa) {
            $maintenanceId = $md->maintenance->id;
            $md->delete();

            return redirect()->route($this->view . 'edit', $maintenanceId)
                ->with('success', 'Delete Data Success');
        }

        // Rollback qtyUsed pada PurchaseDetail berdasarkan FIFO
        $fifos = MaintenanceFifo::where('maintenanceDetailCode', $md->code)->get();
        foreach ($fifos as $fifo) {
            PurchaseDetail::where('code', $fifo->purchaseDetailCode)
                ->decrement('qtyUsed', $fifo->qty);
        }

        // Hapus data MaintenanceFifo terkait
        MaintenanceFifo::where('maintenanceDetailCode', $md->code)->delete();

        // Update stok keluar
        Stock::where('itemCode', $md->itemCode)
            ->decrement('stockOut', $md->qty);

        // Hapus transaksi stok
        StockTransaction::where('transactionDetailCode', $md->code)->delete();

        // Hapus detail maintenance
        $maintenanceId = $md->maintenance->id;
        $md->delete();

        return redirect()->route($this->view . 'edit', $maintenanceId)
            ->with('success', 'Delete Data Success');
    }

    public function datatable(Request $request)
    {
        // dd($request->all());
        if ($request->ajax()) {
            $data = $this->service->datatable();

            // Definisikan kolom filter dengan alias
            $filters = [
                'fleet_plateNumber' => $request->plateNumber,
                'item_code' => $request->itemCode,
            ];

            // Hubungkan alias ke relasi dan kolom yang sesuai
            $relations = [
                'fleet_plateNumber' => 'fleet.plateNumber',
                'item_code' => 'details.itemCode',
            ];

            $dateFilters = [
                'date' => [
                    'start' => $request->startDate,
                    'end' => $request->endDate,
                ],
            ];

            $data = FilterHelper::applyFilters($data, $filters, $relations, $dateFilters);

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('po_numbers', function ($row) {
                    if ($row->purchases && $row->purchases->isNotEmpty()) {
                        return $row->purchases->map(function ($p) {
                            return '<span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace fs-11 px-2 py-1 me-1 mb-1">'
                                . e($p->code) . '</span>';
                        })->implode(' ');
                    }

                    return '<span class="text-muted">-</span>';
                })
                ->filterColumn('po_numbers', function ($query, $keyword) {
                    $query->whereHas('purchases', function ($q) use ($keyword) {
                        $q->where('purchase.code', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('maintenanceDate', function ($row) {
                    $date = Carbon::parse($row->date)->format('d-M-Y');
                    $time = Carbon::parse($row->time)->format('H:i');

                    return $date . ' ' . $time;
                })
                ->editColumn('fleet.plateNumber', function ($row) {
                    $fleet = '';

                    if (isset($row->fleet->plateNumber)) {
                        $fleet = $row->fleet->plateNumber;
                    }

                    return $fleet;
                })

                ->addColumn('warehouse', function ($row) {
                    return $row->warehouse ? $row->warehouse->name : '-';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<td>
        <button type="button" class="btn btn-icon btn-sm bg-info-subtle me-1" 
           onclick="showDetail(\'' . $row->id . '\')"
           data-bs-toggle="tooltip" title="Detail">
            <i class="mdi mdi-eye-outline fs-14 text-info"></i>
        </button>

        <a href="' . route($this->view . 'edit', $row->id) . '"
           class="btn btn-icon btn-sm bg-primary-subtle me-1"
           data-bs-toggle="tooltip" title="Edit">
            <i class="mdi mdi-pencil-outline fs-14 text-primary"></i>
        </a>

        <a href="javascript:deleteData(\'' . $row->id . '\')"
           class="btn btn-icon btn-sm bg-danger-subtle"
           data-bs-toggle="tooltip" title="Delete">
            <i class="mdi mdi-delete fs-14 text-danger"></i>
        </a>
    </td>';

                    return $btn;
                })
                ->rawColumns(['maintenanceDate', 'fleet.plateNumber', 'po_numbers', 'warehouse', 'action'])
                ->toJson();
        }
    }

    public function pdfMaintenance(Request $request)
    {
        // Definisikan kolom filter dengan alias
        $filters = [
            'fleet_plateNumber' => $request->plateNumber,
        ];

        // Hubungkan alias ke relasi dan kolom yang sesuai
        $relations = [
            'fleet_plateNumber' => 'fleet.plateNumber',
        ];

        $dateFilters = [
            'date' => [
                'start' => $request->startDate,
                'end' => $request->endDate,
            ],
        ];

        $query = Maintenance::with([
            'fleet',
            'details',
            'details.item',
            'details.item.supplier',
            'details.item.category',

        ])->orderBy('created_at', 'desc');

        $data = FilterHelper::applyFilters($query, $filters, $relations, $dateFilters);

        $mpdf = new Mpdf(
            [
                'orientation' => 'P',
                'format' => [215, 330],
            ]
        );

        $startDate = Carbon::parse($request->startDate)->format('d-m-Y');
        $endDate = Carbon::parse($request->endDate)->format('d-m-Y');

        $mpdf->WriteHTML(
            view($this->view . 'report.maintenance-pdf')
                ->with('data', $data->get())
                ->with('plateNumber', $request->plateNumber)
                ->with('startDate', $startDate)
                ->with('endDate', $endDate)
        );

        return $mpdf->Output('Laporan Maintenance.pdf', 'I');
    }

    public function generateCode(Request $request)
    {
        $date = $request->date;

        $code = GenerateCode::generateCodeAscDate(
            'MNT',
            Maintenance::class,
            'date',
            $date,
        );

        return response()->json(['code' => $code]);
    }

    /**
     * Get stock items by warehouse
     */
    public function getStockByWarehouse(Request $request)
    {
        $warehouseCode = $request->warehouseCode;

        if (! $warehouseCode) {
            return response()->json(['success' => false, 'message' => 'Warehouse code is required'], 400);
        }

        $stockByItem = StockTransaction::select('itemCode')
            ->selectRaw('SUM(qtyIn) - SUM(qtyOut) as stock')
            ->where('warehouseCode', $warehouseCode)
            ->groupBy('itemCode')
            ->pluck('stock', 'itemCode');

        // Jika filter purchase_ids dikirim (dari create/edit maintenance)
        if ($request->has('purchase_ids')) {
            $purchaseIds = array_filter((array) $request->input('purchase_ids', []));

            if (empty($purchaseIds)) {
                return response()->json(['success' => true, 'data' => []]);
            }

            $purchaseCodes = Purchase::whereIn('id', $purchaseIds)->pluck('code');
            $purchaseDetails = PurchaseDetail::whereIn('purchaseCode', $purchaseCodes)
                ->with('item')
                ->whereNull('deleted_at')
                ->get();

            // Gabungkan item berdasarkan itemCode agar tidak duplikat bila ada di beberapa PO
            $grouped = $purchaseDetails->groupBy('itemCode');

            $items = $grouped->map(function ($details, $itemCode) use ($stockByItem) {
                $firstDetail = $details->first();
                $item = $firstDetail->item;

                // Hanya item fisik suku cadang, bukan jasa
                if (optional($item)->type === Item::TYPE_JASA) {
                    return null;
                }

                $stock = (float) ($stockByItem[$itemCode] ?? 0);

                // Hanya item yang memiliki sisa stok positif (> 0)
                if ($stock <= 0) {
                    return null;
                }

                $price = $firstDetail->price ?? (optional($item)->price ?? 0);

                return [
                    'code' => $itemCode,
                    'name' => optional($item)->name ?? $itemCode,
                    'stock' => $stock,
                    'price' => $price,
                    'type' => optional($item)->type ?? Item::TYPE_PART,
                    'po_codes' => $details->pluck('purchaseCode')->unique()->values()->all(),
                ];
            })->filter()->values();

            return response()->json(['success' => true, 'data' => $items]);
        }

        // Part items: only those with positive stock in this warehouse (bukan jasa)
        $partCodes = $stockByItem->filter(function ($s) {
            return (float) $s > 0;
        })->keys()->toArray();

        $parts = collect();
        if (! empty($partCodes)) {
            $parts = Item::whereIn('code', $partCodes)
                ->where('type', '!=', Item::TYPE_JASA)
                ->get();
        }

        $stocks = $parts->map(function ($item) use ($stockByItem) {
            $stock = (float) ($stockByItem[$item->code] ?? 0);

            return [
                'code' => $item->code,
                'name' => $item->name,
                'stock' => $stock,
                'price' => $item->price ?? 0,
                'type' => $item->type ?? Item::TYPE_PART,
            ];
        })->values();

        return response()->json(['success' => true, 'data' => $stocks]);
    }

    /**
     * Get purchase orders (PO) by warehouse untuk dropdown "No PO" di form maintenance.
     * Hanya menampilkan PO yang belum pernah digunakan dan memiliki stok suku cadang fisik > 0 (bukan jasa).
     */
    public function getPurchasesByWarehouse(Request $request)
    {
        $warehouseCode = $request->warehouseCode;

        if (! $warehouseCode) {
            return response()->json(['success' => false, 'message' => 'Warehouse code is required'], 400);
        }

        $currentMaintenanceId = $request->current_maintenance_id ?? $request->maintenance_id;
        $usedPurchaseIds = $this->getUsedPurchaseIds($currentMaintenanceId);

        // 1. Ambil stok per item di gudang ini
        $stockByItem = StockTransaction::query()
            ->select('itemCode')
            ->selectRaw('SUM(qtyIn) - SUM(qtyOut) as stock')
            ->where('warehouseCode', $warehouseCode)
            ->groupBy('itemCode')
            ->pluck('stock', 'itemCode');

        // Item fisik suku cadang (bukan jasa) yang memiliki sisa stok positif (> 0)
        $partItemCodes = Item::query()
            ->where('type', '!=', Item::TYPE_JASA)
            ->pluck('code')
            ->toArray();

        $validItemCodes = $stockByItem->filter(function ($s, $code) use ($partItemCodes) {
            return (float) $s > 0 && in_array($code, $partItemCodes);
        })->keys()->toArray();

        // 2. Ambil ID purchase yang memiliki minimal satu item suku cadang dengan stok > 0
        $posWithStock = DB::table('purchase')
            ->join('purchase_detail', 'purchase_detail.purchaseCode', '=', 'purchase.code')
            ->where('purchase.warehouseCode', $warehouseCode)
            ->whereNull('purchase.deleted_at')
            ->whereNull('purchase_detail.deleted_at')
            ->whereIn('purchase_detail.itemCode', $validItemCodes)
            ->distinct()
            ->pluck('purchase.id')
            ->toArray();

        // Jika mode edit, pastikan PO milik maintenance saat ini tetap diikutsertakan
        $currentMaintenancePurchaseIds = [];
        if ($currentMaintenanceId) {
            $currentMaintenancePurchaseIds = DB::table('maintenance_purchase')
                ->where('maintenance_id', $currentMaintenanceId)
                ->pluck('purchase_id')
                ->toArray();
        }

        $purchases = Purchase::query()
            ->with('supplier')
            ->where('warehouseCode', $warehouseCode)
            ->where(function ($q) {
                $q->whereNull('is_direct')->orWhere('is_direct', 0);
            })
            ->whereNotIn('id', $usedPurchaseIds)
            ->where(function ($q) use ($posWithStock, $currentMaintenancePurchaseIds) {
                $q->whereIn('id', $posWithStock);
                if (! empty($currentMaintenancePurchaseIds)) {
                    $q->orWhereIn('id', $currentMaintenancePurchaseIds);
                }
            })
            ->orderByDesc('date')
            ->orderByDesc('code')
            ->get()
            ->map(function ($purchase) {
                return [
                    'id' => $purchase->id,
                    'code' => $purchase->code,
                    'date' => $purchase->date,
                    'supplierCode' => $purchase->supplierCode,
                    'supplierName' => optional($purchase->supplier)->name,
                    'status' => $purchase->status,
                ];
            })
            ->values();

        return response()->json(['success' => true, 'data' => $purchases]);
    }

    /**
     * Ambil list ID Purchase (PO) yang sudah pernah digunakan pada maintenance lain.
     */
    protected function getUsedPurchaseIds(?string $excludeMaintenanceId = null): array
    {
        // 1. PO yang sudah tersimpan di pivot maintenance_purchase pada maintenance aktif
        $usedInMaintenanceQuery = DB::table('maintenance_purchase')
            ->join('maintenance', 'maintenance.id', '=', 'maintenance_purchase.maintenance_id')
            ->whereNull('maintenance.deleted_at');

        if ($excludeMaintenanceId) {
            $usedInMaintenanceQuery->where('maintenance_purchase.maintenance_id', '!=', $excludeMaintenanceId);
        }

        $usedInMaintenance = $usedInMaintenanceQuery->pluck('maintenance_purchase.purchase_id');

        // 2. PO yang sudah pernah dikonsumsi di maintenance_fifo pada maintenance aktif
        $usedInFifoQuery = DB::table('maintenance_fifo')
            ->join('maintenance_detail', 'maintenance_detail.code', '=', 'maintenance_fifo.maintenanceDetailCode')
            ->join('maintenance', 'maintenance.code', '=', 'maintenance_detail.maintenanceCode')
            ->join('purchase_detail', 'purchase_detail.code', '=', 'maintenance_fifo.purchaseDetailCode')
            ->join('purchase', 'purchase.code', '=', 'purchase_detail.purchaseCode')
            ->whereNull('maintenance.deleted_at')
            ->whereNull('purchase.deleted_at');

        if ($excludeMaintenanceId) {
            $usedInFifoQuery->where('maintenance.id', '!=', $excludeMaintenanceId);
        }

        $usedInFifo = $usedInFifoQuery->pluck('purchase.id');

        return $usedInMaintenance->concat($usedInFifo)->unique()->filter()->values()->toArray();
    }
}
