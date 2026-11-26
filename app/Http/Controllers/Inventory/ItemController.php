<?php

namespace App\Http\Controllers\Inventory;

use App\Exports\ItemExport;
use App\Http\Controllers\Controller;
use App\Models\Inventory\Item;
use App\Models\Inventory\ItemCategory;
use App\Models\Master\Unit;
use App\Services\Inventory\ItemCategoryService;
use App\Services\Inventory\ItemService;
use App\Services\Inventory\SupplierService;
use App\Services\Inventory\WarehouseService;
use App\Services\Master\UnitService;
use App\Services\UniqueCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;
use Yajra\DataTables\DataTables;

class ItemController extends Controller
{
    protected $service;

    protected $unitSvc;

    protected $categorySvc;

    protected $warehouseSvc;

    protected $supplierSvc;

    protected $title;

    protected $view;

    protected $menuSvc;

    public function __construct(
        ItemService $itemSvc,
        UnitService $unitSvc,
        ItemCategoryService $categorySvc,
        WarehouseService $warehouseSvc,
        SupplierService $supplierSvc
    ) {
        $this->service = $itemSvc;
        $this->unitSvc = $unitSvc;
        $this->categorySvc = $categorySvc;
        $this->warehouseSvc = $warehouseSvc;
        $this->supplierSvc = $supplierSvc;
        $this->title = 'Item';
        $this->view = 'inventory.items.';
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Item::orderBy('code', 'asc')->get();
        $categories = $this->categorySvc->findAll();
        $units = $this->unitSvc->findAllInventory();
        $types = Item::types();

        return view($this->view.'index')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('items', $items)
            ->with('categories', $categories)
            ->with('units', $units)
            ->with('types', $types);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $unit = $this->unitSvc->findAllInventory();
        $category = $this->categorySvc->findAll();
        $warehouse = $this->warehouseSvc->findAll();
        $supplier = $this->supplierSvc->findAll();
        $types = Item::types();

        return view($this->view.'create')
            ->with('view', $this->view)
            ->with('unit', $unit)
            ->with('category', $category)
            ->with('warehouse', $warehouse)
            ->with('supplier', $supplier)
            ->with('types', $types)
            ->with('title', $this->title);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->normalizePriceInput($request);

        $validator = Validator::make($request->all(), [
            'code' => ['required', 'string', 'max:30'],
            'name' => 'required',
            // 'brandName' => 'required',
            // 'categoryCode' => 'required',
            // 'itemLocationCode' => 'required',
            // 'warehouseCode' => 'required',
            'unitCode' => 'required',
            'type' => ['required', Rule::in(array_keys(Item::types()))],
            // 'supplierCode' => 'required',
            'price' => ['nullable', 'numeric', 'min:0'],
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        try {
            $code = app(UniqueCodeService::class)->runWithDuplicateRetry(function () use ($request) {
                return DB::transaction(fn () => $this->service->store($request, $this->title));
            });

            $redirect = redirect()->route($this->view.'index')
                ->with('success', $this->title.' '.__('general.data_was_save_successfully'));

            return $code->wasChanged ? $redirect->with('code_replaced', $code->flashPayload()) : $redirect;
        } catch (\Throwable $th) {
            return redirect()->route($this->view.'index')->with('fail', 'Line : '.$th->getLine().'<br>'.$th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data = $this->service->getById($id);

        if (! $data) {
            return redirect()->route($this->view.'index')->with('fail', 'Data not found');
        }

        $unit = $this->unitSvc->findAllInventory();
        $category = $this->categorySvc->findAll();
        $warehouse = $this->warehouseSvc->findAll();
        $supplier = $this->supplierSvc->findAll();
        $types = Item::types();

        return view($this->view.'edit')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('unit', $unit)
            ->with('category', $category)
            ->with('warehouse', $warehouse)
            ->with('supplier', $supplier)
            ->with('types', $types)
            ->with('data', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->normalizePriceInput($request);

        $validator = Validator::make($request->all(), [
            // 'code' => 'required',
            'name' => 'required',
            // 'brandName' => 'required',
            // 'categoryCode' => 'required',
            // 'itemLocationCode' => 'required',
            // 'warehouseCode' => 'required',
            'unitCode' => 'required',
            'type' => ['required', Rule::in(array_keys(Item::types()))],
            // 'supplierCode' => 'required',
            'price' => ['nullable', 'numeric', 'min:0'],
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        try {
            DB::beginTransaction();

            $this->service->update($request, $id, $this->title);

            DB::commit();

            return redirect()->route($this->view.'index')->with('success', $this->title.' '.__('general.data_was_update_succesfully'));
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->route($this->view.'index')->with('fail', 'Line : '.$th->getLine().'<br>'.$th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->service->destroy($id, $this->title);

            return redirect()->route($this->view.'index')->with('success', 'Delete Data Success');
        } catch (\DomainException|\RuntimeException $th) {
            return redirect()->route($this->view.'index')->with('fail', $th->getMessage());
        } catch (\Throwable $th) {
            return redirect()->route($this->view.'index')->with('fail', 'Line : '.$th->getLine().'<br>'.$th->getMessage());
        }
    }

    public function syncLatestPurchasePrice()
    {
        try {
            DB::beginTransaction();

            $latestPriceByItem = [];

            $rows = DB::table('purchase_detail')
                ->join('purchase', 'purchase.code', '=', 'purchase_detail.purchaseCode')
                ->whereNull('purchase_detail.deleted_at')
                ->whereNull('purchase.deleted_at')
                ->whereNotNull('purchase_detail.itemCode')
                ->orderBy('purchase.date', 'desc')
                ->orderBy('purchase.time', 'desc')
                ->orderBy('purchase_detail.created_at', 'desc')
                ->select([
                    'purchase_detail.itemCode',
                    'purchase_detail.price',
                ])
                ->cursor();

            foreach ($rows as $row) {
                if (isset($latestPriceByItem[$row->itemCode])) {
                    continue;
                }

                if ($row->price === null) {
                    continue;
                }

                $latestPriceByItem[$row->itemCode] = (float) $row->price;
            }

            if (empty($latestPriceByItem)) {
                DB::commit();

                return redirect()->route($this->view.'index')->with('fail', 'Tidak ada history pembelian untuk sinkronisasi harga.');
            }

            $updatedCount = 0;

            foreach ($latestPriceByItem as $itemCode => $price) {
                $updatedCount += Item::where('code', $itemCode)
                    ->whereNull('deleted_at')
                    ->update([
                        'price' => $price,
                    ]);
            }

            DB::commit();

            return redirect()->route($this->view.'index')->with('success', 'Sinkronisasi harga berhasil. '.$updatedCount.' item diperbarui dari pembelian terbaru.');
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->route($this->view.'index')->with('fail', 'Line : '.$th->getLine().'<br>'.$th->getMessage());
        }
    }

    public function purchaseHistory(Request $request, string $itemCode)
    {
        $item = Item::where('code', $itemCode)->first();

        if (! $item) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found',
                'data' => [],
            ], 404);
        }

        $histories = DB::table('purchase_detail')
            ->join('purchase', 'purchase.code', '=', 'purchase_detail.purchaseCode')
            ->whereNull('purchase_detail.deleted_at')
            ->whereNull('purchase.deleted_at')
            ->where('purchase_detail.itemCode', $itemCode)
            ->orderBy('purchase.date', 'desc')
            ->orderBy('purchase.time', 'desc')
            ->select([
                'purchase.date',
                'purchase.time',
                'purchase.code as purchaseCode',
                'purchase_detail.qty',
                'purchase_detail.price',
                DB::raw('(purchase_detail.qty * purchase_detail.price) as total'),
            ]);

        return DataTables::of($histories)
            ->addIndexColumn()
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && ! empty($request->search['value'])) {
                    $search = strtolower($request->search['value']);
                    $query->where(function ($q) use ($search) {
                        $q->whereRaw('LOWER(purchase.code) LIKE ?', ['%'.$search.'%'])
                            ->orWhereRaw('CAST(purchase_detail.qty AS CHAR) LIKE ?', ['%'.$search.'%'])
                            ->orWhereRaw('CAST(purchase_detail.price AS CHAR) LIKE ?', ['%'.$search.'%'])
                            ->orWhereRaw('DATE_FORMAT(purchase.date, "%d/%m/%Y") LIKE ?', ['%'.$search.'%']);
                    });
                }
            })
            ->editColumn('date', function ($row) {
                return $row->date ? date('d/m/Y', strtotime($row->date)) : '-';
            })
            ->editColumn('qty', function ($row) {
                $qty = (float) $row->qty;

                return number_format($qty, abs($qty - round($qty)) > 0.0001 ? 1 : 0, ',', '.');
            })
            ->editColumn('price', function ($row) {
                return number_format((float) $row->price, 0, ',', '.');
            })
            ->editColumn('total', function ($row) {
                return number_format((float) $row->total, 0, ',', '.');
            })
            ->make(true);
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->filteredQuery($request);

            return DataTables::of($data)
                ->addIndexColumn()
                ->filterColumn('DT_RowIndex', function ($query, $keyword) {
                    return $query;
                })
                ->filterColumn('unit.name', function ($query, $keyword) {
                    $query->whereHas('unit', function ($unit) use ($keyword) {
                        $unit->whereRaw('LOWER(name) LIKE ?', ['%'.strtolower($keyword).'%']);
                    });
                })
                ->filterColumn('price', function ($query, $keyword) {
                    $digits = preg_replace('/[^\d]/', '', (string) $keyword);

                    if ($digits === '') {
                        $query->whereRaw('1 = 0');

                        return;
                    }

                    $query->whereRaw('CAST(item.price AS CHAR) LIKE ?', ['%'.$digits.'%']);
                })
                ->orderColumn('code', function ($query, $order) {
                    $query->orderBy('item.code', $order);
                })
                ->orderColumn('name', function ($query, $order) {
                    $query->orderBy('item.name', $order);
                })
                ->orderColumn('type', function ($query, $order) {
                    $query->orderBy('item.type', $order);
                })
                ->orderColumn('price', function ($query, $order) {
                    $query->orderBy('item.price', $order);
                })
                ->orderColumn('unit.name', function ($query, $order) {
                    // Yajra sudah melakukan left join `unit`, cukup arahkan ke kolom relasinya.
                    $query->orderBy('unit.name', $order);
                })
                ->filter(function ($query) use ($request) {
                    if (! $request->has('search') || empty($request->search['value'])) {
                        return;
                    }

                    $terms = preg_split('/\s+/', strtolower(trim($request->search['value']))) ?: [];

                    foreach ($terms as $term) {
                        // Abaikan kata mata uang agar "Rp 1.500.000" tetap menemukan datanya.
                        if ($term === '' || in_array($term, ['rp', 'rp.', 'idr'], true)) {
                            continue;
                        }

                        $priceTerm = preg_replace('/[^\d]/', '', $term);

                        $query->where(function ($group) use ($term, $priceTerm) {
                            $group->whereRaw('LOWER(item.code) LIKE ?', ['%'.$term.'%'])
                                ->orWhereRaw('LOWER(item.name) LIKE ?', ['%'.$term.'%'])
                                ->orWhereRaw('LOWER(item.type) LIKE ?', ['%'.$term.'%'])
                                ->orWhereHas('unit', function ($unit) use ($term) {
                                    $unit->whereRaw('LOWER(name) LIKE ?', ['%'.$term.'%']);
                                });

                            if ($priceTerm !== '') {
                                $group->orWhereRaw('CAST(item.price AS CHAR) LIKE ?', ['%'.$priceTerm.'%']);
                            }
                        });
                    }
                })
                ->editColumn('type', function ($row) {
                    return Item::types()[$row->type] ?? ucfirst($row->type ?? '-');
                })
                ->escapeColumns(['code', 'name', 'unit.name'])
                ->addColumn('action', function ($row) {
                    $btn = '<td>
        <a href="javascript:void(0)"
           class="btn btn-icon btn-sm bg-info-subtle me-1 btn-purchase-history"
           data-item-code="'.e($row->code).'"
           data-item-name="'.e($row->name).'"
           data-url="'.e(route($this->view.'purchase-history', $row->code)).'"
           data-bs-toggle="tooltip" title="History Pembelian">
            <i class="mdi mdi-history fs-14 text-info"></i>
        </a>

        <a href="'.route($this->view.'edit', $row->id).'"
           class="btn btn-icon btn-sm bg-primary-subtle me-1"
           data-bs-toggle="tooltip" title="Edit">
            <i class="mdi mdi-pencil-outline fs-14 text-primary"></i>
        </a>

        <a href="javascript:deleteData(\''.e($row->id).'\')"
           class="btn btn-icon btn-sm bg-danger-subtle"
           data-bs-toggle="tooltip" title="Delete">
            <i class="mdi mdi-delete fs-14 text-danger"></i>
        </a>
    </td>';

                    return $btn;
                })
                ->rawColumns(['action', 'type'])
                ->make();
        }
    }

    /**
     * Normalisasi input price agar format ribuan/decimal Indonesia ("1.500.000", "1.500,50")
     * tetap tersimpan benar, dan nilai kosong dikirim sebagai null.
     */
    private function normalizePriceInput(Request $request): void
    {
        if (! $request->has('price')) {
            return;
        }

        $raw = trim((string) $request->input('price'));

        if ($raw === '') {
            $request->merge(['price' => null]);

            return;
        }

        $parsed = $this->parseRupiah($raw);

        // Biarkan nilai asli bila tidak bisa di-parse supaya rule numeric menolaknya.
        $request->merge(['price' => $parsed ?? $raw]);
    }

    private function parseRupiah(string $value): ?float
    {
        $value = preg_replace('/[^\d,.\-]/', '', $value) ?? '';
        $value = rtrim($value, '.,');

        if ($value === '' || preg_match('/-?\d/', $value) !== 1) {
            return null;
        }

        $hasComma = str_contains($value, ',');
        $hasDot = str_contains($value, '.');

        if ($hasComma && $hasDot) {
            // Format id-ID: 1.500.000,50
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif ($hasComma) {
            // Format desimal koma: 1500,50
            $value = str_replace(',', '.', $value);
        } elseif ($hasDot && preg_match('/^-?\d{1,3}(\.\d{3})+$/', $value) === 1) {
            // Pemisah ribuan: 1.500.000
            $value = str_replace('.', '', $value);
        }

        return is_numeric($value) ? (float) $value : null;
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(
            new ItemExport($this->filteredQuery($request)->orderBy('item.code', 'asc')),
            'Data-Barang-'.now()->format('YmdHis').'.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $items = $this->filteredQuery($request)->orderBy('item.code', 'asc')->get();

        $filterSummary = [];
        if ($request->filled('itemCode')) {
            $itemName = Item::query()->where('code', $request->itemCode)->value('name');
            $filterSummary[] = 'Barang: '.$request->itemCode.($itemName ? ' - '.$itemName : '');
        }
        if ($request->filled('categoryCode')) {
            $catName = ItemCategory::query()->where('code', $request->categoryCode)->value('name');
            $filterSummary[] = 'Kategori: '.($catName ?: $request->categoryCode);
        }
        if ($request->filled('unitCode')) {
            $unitName = Unit::query()->where('code', $request->unitCode)->value('name');
            $filterSummary[] = 'Satuan: '.($unitName ?: $request->unitCode);
        }
        if ($request->filled('type')) {
            $filterSummary[] = 'Tipe: '.(Item::types()[$request->type] ?? $request->type);
        }

        $tempDir = storage_path('app/mpdf-temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $mpdf = new Mpdf([
            'orientation' => 'L',
            'format' => 'A4',
            'tempDir' => $tempDir,
            'default_font' => 'sans-serif',
        ]);

        $mpdf->WriteHTML(view($this->view.'report.items-pdf', [
            'items' => $items,
            'title' => 'Laporan Master Barang',
            'filterSummary' => $filterSummary,
            'printedAt' => now(),
        ])->render());

        return response($mpdf->Output('', 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="Data-Barang-'.now()->format('YmdHis').'.pdf"');
    }

    private function filteredQuery(Request $request)
    {
        $query = $this->service->findAll();

        if ($request->filled('itemCode')) {
            $query->where('item.code', $request->itemCode);
        }

        if ($request->filled('categoryCode')) {
            $query->where('item.categoryCode', $request->categoryCode);
        }

        if ($request->filled('unitCode')) {
            $query->where('item.unitCode', $request->unitCode);
        }

        if ($request->filled('type')) {
            $query->where('item.type', $request->type);
        }

        return $query;
    }
}
