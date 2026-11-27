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

    protected string $itemType = Item::TYPE_PART;

    protected string $routePrefix = 'inventory.items.';

    protected string $dtRoute = 'dt.items';

    protected string $entityLabel = 'Barang';

    protected string $exportFilePrefix = 'Data-Barang';

    protected string $pdfReportTitle = 'Laporan Master Barang';

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
        $this->title = 'Barang';
        $this->view = 'inventory.items.';
    }

    protected function sharedViewData(): array
    {
        return [
            'view' => $this->view,
            'title' => $this->title,
            'routePrefix' => $this->routePrefix,
            'dtRoute' => $this->dtRoute,
            'itemType' => $this->itemType,
            'entityLabel' => $this->entityLabel,
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Item::query()
            ->where('type', $this->itemType)
            ->orderBy('code', 'asc')
            ->get();
        $categories = $this->categorySvc->findAll();
        $units = $this->unitSvc->findAllInventory();

        return view($this->view.'index', array_merge($this->sharedViewData(), [
            'items' => $items,
            'categories' => $categories,
            'units' => $units,
        ]));
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

        return view($this->view.'create', array_merge($this->sharedViewData(), [
            'unit' => $unit,
            'category' => $category,
            'warehouse' => $warehouse,
            'supplier' => $supplier,
        ]));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $forced = ['type' => $this->itemType];
        if ($this->itemType === Item::TYPE_JASA) {
            $forced['warehouseCode'] = null;
        }
        $request->merge($forced);

        $this->normalizePriceInput($request);
        $this->normalizeNullableForeignKeys($request);

        $validator = Validator::make($request->all(), [
            'code' => ['required', 'string', 'max:30'],
            'name' => ['required', 'string', 'max:255'],
            'unitCode' => ['required', 'string'],
            'type' => ['required', Rule::in([$this->itemType])],
            'price' => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $code = app(UniqueCodeService::class)->runWithDuplicateRetry(function () use ($request) {
                return DB::transaction(fn () => $this->service->store($request, $this->title));
            });

            $redirect = redirect()->route($this->routePrefix.'index')
                ->with('success', $this->title.' '.__('general.data_was_save_successfully'));

            return $code->wasChanged ? $redirect->with('code_replaced', $code->flashPayload()) : $redirect;
        } catch (\Throwable $th) {
            return redirect()->route($this->routePrefix.'index')->with('fail', 'Line : '.$th->getLine().'<br>'.$th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return redirect()->route($this->routePrefix.'index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data = $this->service->getById($id);

        if (! $data || $data->type !== $this->itemType) {
            return redirect()->route($this->routePrefix.'index')->with('fail', 'Data not found');
        }

        $unit = $this->unitSvc->findAllInventory();
        $category = $this->categorySvc->findAll();
        $warehouse = $this->warehouseSvc->findAll();
        $supplier = $this->supplierSvc->findAll();

        return view($this->view.'edit', array_merge($this->sharedViewData(), [
            'unit' => $unit,
            'category' => $category,
            'warehouse' => $warehouse,
            'supplier' => $supplier,
            'data' => $data,
        ]));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $existing = $this->service->getById($id);

        if (! $existing || $existing->type !== $this->itemType) {
            return redirect()->route($this->routePrefix.'index')->with('fail', 'Data not found');
        }

        $forced = ['type' => $this->itemType];
        if ($this->itemType === Item::TYPE_JASA) {
            $forced['warehouseCode'] = null;
        }
        $request->merge($forced);

        $this->normalizePriceInput($request);
        $this->normalizeNullableForeignKeys($request);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'unitCode' => ['required', 'string'],
            'type' => ['required', Rule::in([$this->itemType])],
            'price' => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $this->service->update($request, $id, $this->title);

            DB::commit();

            return redirect()->route($this->routePrefix.'index')->with('success', $this->title.' '.__('general.data_was_update_succesfully'));
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->route($this->routePrefix.'index')->with('fail', 'Line : '.$th->getLine().'<br>'.$th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $existing = $this->service->getById($id);

        if (! $existing || $existing->type !== $this->itemType) {
            return redirect()->route($this->routePrefix.'index')->with('fail', 'Data not found');
        }

        try {
            $this->service->destroy($id, $this->title);

            return redirect()->route($this->routePrefix.'index')->with('success', 'Delete Data Success');
        } catch (\DomainException|\RuntimeException $th) {
            return redirect()->route($this->routePrefix.'index')->with('fail', $th->getMessage());
        } catch (\Throwable $th) {
            return redirect()->route($this->routePrefix.'index')->with('fail', 'Line : '.$th->getLine().'<br>'.$th->getMessage());
        }
    }

    public function syncLatestPurchasePrice()
    {
        try {
            DB::beginTransaction();

            $targetCodes = Item::query()
                ->where('type', $this->itemType)
                ->whereNull('deleted_at')
                ->pluck('code')
                ->all();

            if ($targetCodes === []) {
                DB::commit();

                return redirect()->route($this->routePrefix.'index')->with(
                    'fail',
                    'Tidak ada data '.strtolower($this->entityLabel).' untuk sinkronisasi harga.'
                );
            }

            $targetSet = array_flip($targetCodes);
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
                if (! isset($targetSet[$row->itemCode]) || isset($latestPriceByItem[$row->itemCode])) {
                    continue;
                }

                if ($row->price === null) {
                    continue;
                }

                $latestPriceByItem[$row->itemCode] = (float) $row->price;
            }

            if (empty($latestPriceByItem)) {
                DB::commit();

                return redirect()->route($this->routePrefix.'index')->with(
                    'fail',
                    'Tidak ada history pembelian '.strtolower($this->entityLabel).' untuk sinkronisasi harga.'
                );
            }

            $updatedCount = 0;

            foreach ($latestPriceByItem as $itemCode => $price) {
                $updatedCount += Item::where('code', $itemCode)
                    ->where('type', $this->itemType)
                    ->whereNull('deleted_at')
                    ->update([
                        'price' => $price,
                    ]);
            }

            DB::commit();

            return redirect()->route($this->routePrefix.'index')->with(
                'success',
                'Sinkronisasi harga berhasil. '.$updatedCount.' '.strtolower($this->entityLabel).' diperbarui dari pembelian terbaru.'
            );
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->route($this->routePrefix.'index')->with('fail', 'Line : '.$th->getLine().'<br>'.$th->getMessage());
        }
    }

    public function purchaseHistory(Request $request, string $itemCode)
    {
        $item = Item::where('code', $itemCode)
            ->where('type', $this->itemType)
            ->first();

        if (! $item) {
            return response()->json([
                'success' => false,
                'message' => $this->entityLabel.' not found',
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
                ->addColumn('categoryName', function ($row) {
                    return $row->category->name ?? '-';
                })
                ->filterColumn('categoryName', function ($query, $keyword) {
                    $trimmed = trim((string) $keyword);
                    if ($trimmed === '-') {
                        $query->whereNull('item.categoryCode');

                        return;
                    }
                    $query->whereHas('category', function ($category) use ($trimmed) {
                        $category->whereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($trimmed).'%']);
                    });
                })
                ->filterColumn('unit.name', function ($query, $keyword) {
                    $query->whereHas('unit', function ($unit) use ($keyword) {
                        $unit->whereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower((string) $keyword).'%']);
                    });
                })
                ->filterColumn('price', function ($query, $keyword) {
                    $trimmed = trim((string) $keyword);
                    if (in_array(mb_strtolower($trimmed), ['rp', 'rp.', 'idr'], true)) {
                        $query->whereRaw('1 = 1');

                        return;
                    }
                    if (! preg_match('/^(?:rp\.?\s*)?([\d.,]+)$/i', $trimmed, $m)) {
                        $query->whereRaw('1 = 0');

                        return;
                    }
                    $digits = preg_replace('/\D/', '', $m[1]) ?? '';
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
                ->orderColumn('categoryName', function ($query, $order) {
                    $query->orderBy(
                        ItemCategory::select('name')
                            ->whereColumn('item_category.code', 'item.categoryCode')
                            ->limit(1),
                        $order
                    );
                })
                ->orderColumn('type', function ($query, $order) {
                    $query->orderBy('item.type', $order);
                })
                ->orderColumn('price', function ($query, $order) {
                    $query->orderBy('item.price', $order);
                })
                ->orderColumn('unit.name', function ($query, $order) {
                    $query->orderBy(
                        Unit::select('name')
                            ->whereColumn('unit.code', 'item.unitCode')
                            ->limit(1),
                        $order
                    );
                })
                ->filter(function ($query) use ($request) {
                    if (! $request->has('search') || empty($request->search['value'])) {
                        return;
                    }

                    $terms = preg_split('/\s+/', mb_strtolower(trim((string) $request->search['value']))) ?: [];

                    foreach ($terms as $term) {
                        if ($term === '' || in_array($term, ['rp', 'rp.', 'idr'], true)) {
                            continue;
                        }

                        $priceDigits = '';
                        if (preg_match('/^(?:rp\.?\s*)?([\d.,]+)$/i', $term, $m)) {
                            $priceDigits = preg_replace('/\D/', '', $m[1]) ?? '';
                        }

                        $query->where(function ($group) use ($term, $priceDigits) {
                            $group->whereRaw('LOWER(item.code) LIKE ?', ['%'.$term.'%'])
                                ->orWhereRaw('LOWER(item.name) LIKE ?', ['%'.$term.'%'])
                                ->orWhereHas('category', function ($cat) use ($term) {
                                    $cat->whereRaw('LOWER(name) LIKE ?', ['%'.$term.'%']);
                                })
                                ->orWhereHas('unit', function ($unit) use ($term) {
                                    $unit->whereRaw('LOWER(name) LIKE ?', ['%'.$term.'%']);
                                });

                            if ($priceDigits !== '') {
                                $group->orWhereRaw('CAST(item.price AS CHAR) LIKE ?', ['%'.$priceDigits.'%']);
                            }
                        });
                    }
                })
                ->editColumn('type', function ($row) {
                    return Item::types()[$row->type] ?? ucfirst($row->type ?? '-');
                })
                ->escapeColumns(['code', 'name', 'categoryName', 'unit.name'])
                ->addColumn('action', function ($row) {
                    $historyUrl = e(route($this->routePrefix.'purchase-history', $row->code));
                    $editUrl = e(route($this->routePrefix.'edit', $row->id));
                    $deleteUrl = e(route($this->routePrefix.'destroy', $row->id));

                    return '<div class="d-inline-flex align-items-center gap-1">
                        <a href="javascript:void(0)"
                           class="btn btn-icon btn-sm bg-info-subtle btn-purchase-history"
                           data-item-code="'.e($row->code).'"
                           data-item-name="'.e($row->name).'"
                           data-url="'.$historyUrl.'"
                           data-bs-toggle="tooltip" title="History Pembelian">
                            <i class="mdi mdi-history fs-14 text-info"></i>
                        </a>
                        <a href="'.$editUrl.'"
                           class="btn btn-icon btn-sm bg-primary-subtle"
                           data-bs-toggle="tooltip" title="Edit">
                            <i class="mdi mdi-pencil-outline fs-14 text-primary"></i>
                        </a>
                        <a href="javascript:deleteData(\''.$deleteUrl.'\')"
                           class="btn btn-icon btn-sm bg-danger-subtle"
                           data-bs-toggle="tooltip" title="Delete">
                            <i class="mdi mdi-delete fs-14 text-danger"></i>
                        </a>
                    </div>';
                })
                ->rawColumns(['action', 'type'])
                ->make();
        }
    }

    /**
     * Normalisasi input price agar format ribuan/decimal Indonesia ("1.500.000", "1.500,50")
     * tetap tersimpan benar, dan nilai kosong dikirim sebagai null.
     */
    protected function normalizePriceInput(Request $request): void
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

        $request->merge(['price' => $parsed ?? $raw]);
    }

    protected function normalizeNullableForeignKeys(Request $request): void
    {
        foreach (['categoryCode', 'warehouseCode', 'supplierCode', 'brandName'] as $field) {
            if ($request->has($field) && trim((string) $request->input($field)) === '') {
                $request->merge([$field => null]);
            }
        }
    }

    protected function parseRupiah(string $value): ?float
    {
        $value = preg_replace('/[^\d,.\-]/', '', $value) ?? '';
        $value = rtrim($value, '.,');

        if ($value === '' || preg_match('/-?\d/', $value) !== 1) {
            return null;
        }

        $hasComma = str_contains($value, ',');
        $hasDot = str_contains($value, '.');

        if ($hasComma && $hasDot) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif ($hasComma) {
            $value = str_replace(',', '.', $value);
        } elseif ($hasDot && preg_match('/^-?\d{1,3}(\.\d{3})+$/', $value) === 1) {
            $value = str_replace('.', '', $value);
        }

        return is_numeric($value) ? (float) $value : null;
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(
            new ItemExport(
                $this->filteredQuery($request)->orderBy('item.code', 'asc'),
                $this->entityLabel,
                $this->itemType
            ),
            $this->exportFilePrefix.'-'.now()->format('YmdHis').'.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $items = $this->filteredQuery($request)->orderBy('item.code', 'asc')->get();

        $filterSummary = [];
        if ($request->filled('itemCode')) {
            $itemName = Item::query()
                ->where('code', $request->itemCode)
                ->where('type', $this->itemType)
                ->value('name');
            $filterSummary[] = $this->entityLabel.': '.$request->itemCode.($itemName ? ' - '.$itemName : '');
        }
        if ($request->filled('categoryCode')) {
            $catName = ItemCategory::query()->where('code', $request->categoryCode)->value('name');
            $filterSummary[] = 'Kategori: '.($catName ?: $request->categoryCode);
        }
        if ($request->filled('unitCode')) {
            $unitName = Unit::query()->where('code', $request->unitCode)->value('name');
            $filterSummary[] = 'Satuan: '.($unitName ?: $request->unitCode);
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
            'title' => $this->pdfReportTitle,
            'entityLabel' => $this->entityLabel,
            'itemType' => $this->itemType,
            'filterSummary' => $filterSummary,
            'printedAt' => now(),
        ])->render());

        return response($mpdf->Output('', 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="'.$this->exportFilePrefix.'-'.now()->format('YmdHis').'.pdf"');
    }

    protected function filteredQuery(Request $request)
    {
        $query = $this->service->findAll()
            ->where('item.type', $this->itemType);

        if ($request->filled('itemCode')) {
            $query->where('item.code', $request->itemCode);
        }

        if ($request->filled('categoryCode')) {
            $query->where('item.categoryCode', $request->categoryCode);
        }

        if ($request->filled('unitCode')) {
            $query->where('item.unitCode', $request->unitCode);
        }

        return $query;
    }
}
