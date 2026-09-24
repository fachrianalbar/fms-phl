<?php

namespace App\Http\Controllers\Purchasing;

use App\Helpers\FilterHelper;
use App\Helpers\GenerateCode;
use App\Http\Controllers\Controller;
use App\Models\Inventory\Item;
use App\Models\Purchasing\Purchase;
use App\Services\Bank\UserBankService;
use App\Services\Inventory\SupplierService;
use App\Services\Inventory\WarehouseService;
use App\Services\Master\FleetService;
use App\Services\MenuService;
use App\Services\Purchasing\DirectPurchaseService;
use App\Services\UniqueCodeService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class DirectPurchaseController extends Controller
{
    protected DirectPurchaseService $service;

    protected SupplierService $supplierSvc;

    protected FleetService $fleetSvc;

    protected WarehouseService $warehouseSvc;

    protected UserBankService $userBankSvc;

    protected $title;

    protected $view;

    protected $menuSvc;

    public function __construct(
        DirectPurchaseService $directPurchaseSvc,
        SupplierService $supplierSvc,
        FleetService $fleetSvc,
        WarehouseService $warehouseSvc,
        UserBankService $userBankSvc,
        MenuService $menuSvc
    ) {
        $this->service = $directPurchaseSvc;
        $this->supplierSvc = $supplierSvc;
        $this->fleetSvc = $fleetSvc;
        $this->warehouseSvc = $warehouseSvc;
        $this->userBankSvc = $userBankSvc;
        $this->view = 'purchasing.direct-purchase.';

        $menu = $menuSvc->getByName('Direct Purchase');
        if ($menu) {
            $this->title = Auth::user()?->languange == 'en' ? $menu->name : $menu->nama;
        } else {
            $this->title = 'Pembelian Langsung';
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $supplier = $this->supplierSvc->findAll();
        $fleet = $this->fleetSvc->findAll();
        $stats = $this->service->stats();

        return view($this->view . 'index')
            ->with('view', $this->view)
            ->with('supplier', $supplier)
            ->with('fleet', $fleet)
            ->with('stats', $stats)
            ->with('title', $this->title);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $supplier = $this->supplierSvc->findAll();
        $fleet = $this->fleetSvc->findAll();
        $warehouse = $this->warehouseSvc->findAll();
        $userBank = $this->userBankSvc->findCompany();
        $items = Item::query()->orderBy('name', 'asc')->with(['latestPurchase'])->get();

        return view($this->view . 'create')
            ->with('view', $this->view)
            ->with('supplier', $supplier)
            ->with('fleet', $fleet)
            ->with('warehouse', $warehouse)
            ->with('userBank', $userBank)
            ->with('items', $items)
            ->with('title', $this->title);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fleetCode' => ['required', 'string'],
            'supplierCode' => ['required', 'string'],
            'date' => ['required', 'date'],
            'time' => ['required'],
            'itemCode' => ['required', 'array', 'min:1'],
            'itemCode.*' => ['required'],
            'description' => ['sometimes', 'array'],
            'description.*' => ['nullable', 'string'],
            'price' => ['required', 'array', function ($attribute, $value, $fail) {
                foreach ($value as $price) {
                    $clean = (int) str_replace(['Rp', '.', ' '], '', (string) $price);
                    if ($clean <= 0) {
                        $fail('Harga suku cadang tidak boleh 0.');
                    }
                }
            }],
            'qty' => ['required', 'array', function ($attribute, $value, $fail) {
                foreach ($value as $qty) {
                    if (! is_numeric($qty) || (float) $qty <= 0) {
                        $fail('Jumlah kuantitas (qty) harus lebih besar dari 0.');
                    }
                    if (abs(((float) $qty * 2) - round((float) $qty * 2)) > 0.0001) {
                        $fail('Qty harus berupa angka bulat atau kelipatan 0.5.');
                    }
                }
            }],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->with('fail', $validator->errors()->first());
        }

        try {
            $code = app(UniqueCodeService::class)->runWithDuplicateRetry(function () use ($request) {
                return DB::transaction(fn () => $this->service->store($request, $this->title));
            });

            $redirect = redirect()->route($this->view . 'index')
                ->with('success', $this->title . ' ' . __('general.data_was_save_successfully'));

            return $code->wasChanged ? $redirect->with('code_replaced', $code->flashPayload()) : $redirect;
        } catch (\Throwable $th) {
            return redirect()->back()->withInput()->with('fail', 'Terjadi kesalahan: ' . $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $data = $this->service->getById($id);

        if (! $data) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
            }
            return redirect()->route($this->view . 'index')->with('fail', 'Data tidak ditemukan.');
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $data->id,
                    'code' => $data->code,
                    'date' => Carbon::parse($data->date)->format('d F Y'),
                    'time' => $data->time,
                    'fleetPlate' => optional($data->fleet)->plateNumber ?? '-',
                    'fleetBrand' => optional($data->fleet?->brand)->name ?? '',
                    'supplierName' => optional($data->supplier)->name ?? '-',
                    'maintenanceCode' => optional($data->maintenances->first())->code ?? '-',
                    'paymentMethod' => $data->userBank ? (optional($data->userBank->bank)->name . ' - ' . $data->userBank->accountName) : 'Tunai Langsung',
                    'nominal' => (float) $data->nominal,
                    'nominalFormatted' => 'Rp ' . number_format((float) $data->nominal, 0, ',', '.'),
                    'editUrl' => route($this->view . 'edit', $data->id),
                    'details' => $data->details->map(function ($d) {
                        return [
                            'itemCode' => $d->itemCode,
                            'itemName' => optional($d->item)->name ?? '-',
                            'qty' => (float) $d->qty,
                            'price' => (float) $d->price,
                            'priceFormatted' => 'Rp ' . number_format((float) $d->price, 0, ',', '.'),
                            'subtotal' => (float) ($d->qty * $d->price),
                            'subtotalFormatted' => 'Rp ' . number_format((float) ($d->qty * $d->price), 0, ',', '.'),
                            'description' => $d->description ?: '-',
                        ];
                    }),
                ]
            ]);
        }

        return view($this->view . 'show')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('data', $data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data = $this->service->getById($id);

        if (! $data) {
            return redirect()->route($this->view . 'index')->with('fail', 'Data tidak ditemukan.');
        }

        $supplier = $this->supplierSvc->findAll();
        $fleet = $this->fleetSvc->findAll();
        $warehouse = $this->warehouseSvc->findAll();
        $userBank = $this->userBankSvc->findCompany();
        $items = Item::query()->orderBy('name', 'asc')->with(['latestPurchase'])->get();

        return view($this->view . 'edit')
            ->with('view', $this->view)
            ->with('supplier', $supplier)
            ->with('fleet', $fleet)
            ->with('warehouse', $warehouse)
            ->with('userBank', $userBank)
            ->with('items', $items)
            ->with('title', $this->title)
            ->with('data', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'fleetCode' => ['required', 'string'],
            'supplierCode' => ['required', 'string'],
            'date' => ['required', 'date'],
            'time' => ['required'],
            'itemCode' => ['required', 'array', 'min:1'],
            'itemCode.*' => ['required'],
            'description' => ['sometimes', 'array'],
            'description.*' => ['nullable', 'string'],
            'price' => ['required', 'array', function ($attribute, $value, $fail) {
                foreach ($value as $price) {
                    $clean = (int) str_replace(['Rp', '.', ' '], '', (string) $price);
                    if ($clean <= 0) {
                        $fail('Harga suku cadang tidak boleh 0.');
                    }
                }
            }],
            'qty' => ['required', 'array', function ($attribute, $value, $fail) {
                foreach ($value as $qty) {
                    if (! is_numeric($qty) || (float) $qty <= 0) {
                        $fail('Jumlah kuantitas (qty) harus lebih besar dari 0.');
                    }
                    if (abs(((float) $qty * 2) - round((float) $qty * 2)) > 0.0001) {
                        $fail('Qty harus berupa angka bulat atau kelipatan 0.5.');
                    }
                }
            }],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->with('fail', $validator->errors()->first());
        }

        try {
            DB::transaction(fn () => $this->service->update($request, $id, $this->title));

            return redirect()->route($this->view . 'index')
                ->with('success', $this->title . ' ' . __('general.data_was_update_succesfully'));
        } catch (\Throwable $th) {
            return redirect()->back()->withInput()->with('fail', 'Terjadi kesalahan: ' . $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::transaction(fn () => $this->service->destroy($id, $this->title));

            return redirect()->route($this->view . 'index')->with('success', 'Data Pembelian Langsung berhasil dihapus.');
        } catch (\Throwable $th) {
            return redirect()->route($this->view . 'index')->with('fail', 'Gagal menghapus: ' . $th->getMessage());
        }
    }

    /**
     * DataTables server-side feed
     */
    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->datatable();

            $filters = [
                'code' => $request->code,
                'supplierCode' => $request->supplierCode,
                'fleetCode' => $request->fleetCode,
            ];

            $relations = [];

            $dateFilters = [
                'date' => [
                    'start' => $request->startDate,
                    'end' => $request->endDate,
                ],
            ];

            $data = FilterHelper::applyFilters($data, $filters, $relations, $dateFilters);

            $search = $request->input('search.value');
            if (! empty($search)) {
                $data->where(function ($query) use ($search) {
                    $query->where('purchase.code', 'like', '%' . $search . '%')
                        ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', '%' . $search . '%'))
                        ->orWhereHas('fleet', fn ($f) => $f->where('plateNumber', 'like', '%' . $search . '%'))
                        ->orWhereHas('warehouse', fn ($w) => $w->where('name', 'like', '%' . $search . '%'));
                });
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('purchaseDate', function ($row) {
                    $date = Carbon::parse($row->date)->format('d-M-Y');
                    $time = $row->time ? Carbon::parse($row->time)->format('H:i') : '';
                    return $date . ($time ? ' <small class="text-muted">' . $time . '</small>' : '');
                })
                ->addColumn('fleetInfo', function ($row) {
                    if ($row->fleet) {
                        return '<span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace fs-12 px-2 py-1">'
                            . e($row->fleet->plateNumber) . '</span>';
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('supplierInfo', function ($row) {
                    return $row->supplier ? e($row->supplier->name) : '<span class="text-muted">-</span>';
                })
                ->addColumn('maintenanceBadge', function ($row) {
                    $mnt = $row->maintenances->first();
                    if ($mnt) {
                        return '<a href="' . route('warehouse.maintenance.index') . '" class="badge bg-info-subtle text-info border border-info-subtle rounded-pill font-monospace fs-11 px-2 py-1 text-decoration-none" title="Buka menu Maintenance">'
                            . '<i class="mdi mdi-tools me-1"></i>' . e($mnt->code) . '</a>';
                    }
                    return '<span class="badge bg-secondary-subtle text-secondary rounded-pill fs-11">Tidak Terhubung</span>';
                })
                ->addColumn('bankInfo', function ($row) {
                    if ($row->userBank) {
                        $bankName = optional($row->userBank->bank)->name ?? 'Bank';
                        $accName = $row->userBank->accountName;
                        return '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill fs-11" title="' . e($accName) . '">'
                            . '<i class="mdi mdi-cash-check me-1"></i>' . e($bankName) . '</span>';
                    }
                    return '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill fs-11"><i class="mdi mdi-check me-1"></i>Lunas (Tunai)</span>';
                })
                ->addColumn('itemSummary', function ($row) {
                    $count = $row->details->count();
                    $totalQty = $row->details->sum('qty');
                    return '<span class="fw-semibold">' . number_format($count, 0, ',', '.') . ' Item</span>'
                        . '<small class="text-muted d-block">' . number_format($totalQty, 1, ',', '.') . ' Qty</small>';
                })
                ->addColumn('totalPrice', function ($row) {
                    $total = $row->nominal ?: $row->details->sum(fn ($d) => $d->price * $d->qty);
                    return '<a href="javascript:void(0)" onclick="showDetailModal(\'' . $row->id . '\')" class="font-monospace fw-bold text-dark text-decoration-none" data-bs-toggle="tooltip" title="Klik untuk lihat detail biaya">'
                        . 'Rp ' . number_format($total, 0, ',', '.') . '</a>';
                })
                ->addColumn('action', function ($row) {
                    $editUrl = route($this->view . 'edit', $row->id);

                    $btn = '<div class="d-flex align-items-center justify-content-center gap-1">';
                    $btn .= '<button type="button" class="btn btn-icon btn-sm bg-info-subtle" onclick="showDetailModal(\'' . $row->id . '\')" data-bs-toggle="tooltip" title="Lihat Detail Biaya">'
                        . '<i class="mdi mdi-eye fs-14 text-info"></i></button>';
                    $btn .= '<a href="' . $editUrl . '" class="btn btn-icon btn-sm bg-primary-subtle" data-bs-toggle="tooltip" title="Edit Data">'
                        . '<i class="mdi mdi-pencil-outline fs-14 text-primary"></i></a>';
                    $btn .= '<button type="button" class="btn btn-icon btn-sm bg-danger-subtle" onclick="deleteData(\'' . $row->id . '\')" data-bs-toggle="tooltip" title="Hapus Data">'
                        . '<i class="mdi mdi-delete fs-14 text-danger"></i></button>';
                    $btn .= '</div>';

                    return $btn;
                })
                ->rawColumns(['purchaseDate', 'fleetInfo', 'supplierInfo', 'maintenanceBadge', 'bankInfo', 'itemSummary', 'totalPrice', 'action'])
                ->toJson();
        }
    }

    /**
     * Generate code AJAX
     */
    public function generateCode(Request $request)
    {
        $date = $request->date ?? now()->toDateString();
        $carbonDate = Carbon::parse($date);
        $base = 'PL-' . $carbonDate->format('ymd');

        $code = GenerateCode::generateCodeAscDate(
            $base,
            Purchase::class,
            'date',
            $date
        );

        return response()->json(['code' => $code]);
    }
}
