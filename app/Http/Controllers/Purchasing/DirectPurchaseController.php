<?php

namespace App\Http\Controllers\Purchasing;

use App\Exports\DirectPurchaseExport;
use App\Helpers\GenerateCode;
use App\Http\Controllers\Controller;
use App\Models\Bank\UserBank;
use App\Models\Inventory\Item;
use App\Models\Inventory\Supplier;
use App\Models\Master\Fleet;
use App\Models\Purchasing\Purchase;
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
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;
use Yajra\DataTables\DataTables;

class DirectPurchaseController extends Controller
{
    protected DirectPurchaseService $service;

    protected SupplierService $supplierSvc;

    protected FleetService $fleetSvc;

    protected WarehouseService $warehouseSvc;

    protected $title;

    protected $view;

    protected $menuSvc;

    public function __construct(
        DirectPurchaseService $directPurchaseSvc,
        SupplierService $supplierSvc,
        FleetService $fleetSvc,
        WarehouseService $warehouseSvc,
        MenuService $menuSvc
    ) {
        $this->service = $directPurchaseSvc;
        $this->supplierSvc = $supplierSvc;
        $this->fleetSvc = $fleetSvc;
        $this->warehouseSvc = $warehouseSvc;
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

        return view($this->view.'index')
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
        $items = Item::query()->orderBy('name', 'asc')->with(['latestPurchase'])->get();

        return view($this->view.'create')
            ->with('view', $this->view)
            ->with('supplier', $supplier)
            ->with('fleet', $fleet)
            ->with('warehouse', $warehouse)
            ->with('items', $items)
            ->with('paymentTypes', DirectPurchaseService::PAYMENT_TYPES)
            ->with('internalBanks', $this->internalBanks())
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
            'ppn_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'date' => ['required', 'date'],
            'time' => ['required'],
            'paymentType' => ['required', 'in:'.implode(',', DirectPurchaseService::PAYMENT_TYPES)],
            'dueDate' => [
                'nullable',
                'required_if:paymentType,'.DirectPurchaseService::PAYMENT_TYPE_TEMPO,
                'date',
                'after_or_equal:date',
            ],
            'transferUserBankCode' => $this->transferBankRules(),
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
        ], $this->validationMessages());

        if ($validator->fails()) {
            return redirect()->back()->withInput()->with('fail', $validator->errors()->first());
        }

        try {
            $code = app(UniqueCodeService::class)->runWithDuplicateRetry(function () use ($request) {
                return DB::transaction(fn () => $this->service->store($request, $this->title));
            });

            $redirect = redirect()->route($this->view.'index')
                ->with('success', $this->title.' '.__('general.data_was_save_successfully'));

            return $code->wasChanged ? $redirect->with('code_replaced', $code->flashPayload()) : $redirect;
        } catch (\Throwable $th) {
            return redirect()->back()->withInput()->with('fail', 'Terjadi kesalahan: '.$th->getMessage());
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

            return redirect()->route($this->view.'index')->with('fail', 'Data tidak ditemukan.');
        }

        if ($request->ajax()) {
            $subtotal = (float) ($data->subtotal ?: $data->details->sum(fn ($d) => (float) $d->qty * (float) $d->price));
            $ppnRate = (float) ($data->ppn_rate ?? 0);
            $ppnAmount = (float) ($data->ppn_amount ?? 0);
            $nominal = (float) ($data->nominal ?: ($subtotal + $ppnAmount));

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $data->id,
                    'code' => $data->code,
                    'date' => Carbon::parse($data->date)->format('d F Y'),
                    'time' => $data->time,
                    'fleetPlate' => optional($data->fleet)->plateNumber ?? '-',
                    'fleetBrand' => optional($data->fleet?->brand)->name ?? '',
                    'fleetOwner' => optional($data->fleet?->company)->name ?? '-',
                    'supplierName' => optional($data->supplier)->name ?? '-',
                    'maintenanceCode' => optional($data->maintenances->first())->code ?? '-',
                    'paymentMethod' => $data->paymentType ?: 'Tunai',
                    'paymentStatus' => $data->paymentStatus ?: ($data->paymentType === DirectPurchaseService::PAYMENT_TYPE_TEMPO ? 'Unpaid' : 'Paid'),
                    'transferBankName' => $data->transferUserBank?->bank?->name,
                    'transferBankAccountNumber' => $data->transferUserBank?->accountNumber,
                    'transferBankAccountName' => $data->transferUserBank?->accountName,
                    'dueDate' => ($data->paymentType === DirectPurchaseService::PAYMENT_TYPE_TEMPO && $data->dueDate)
                        ? Carbon::parse($data->dueDate)->format('d F Y')
                        : '-',
                    'subtotal' => $subtotal,
                    'subtotalFormatted' => 'Rp '.number_format($subtotal, 0, ',', '.'),
                    'ppnRate' => $ppnRate,
                    'ppnRateFormatted' => rtrim(rtrim(number_format($ppnRate, 2, ',', '.'), '0'), ',').'%',
                    'ppnAmount' => $ppnAmount,
                    'ppnAmountFormatted' => 'Rp '.number_format($ppnAmount, 0, ',', '.'),
                    'nominal' => $nominal,
                    'nominalFormatted' => 'Rp '.number_format($nominal, 0, ',', '.'),
                    'editUrl' => route($this->view.'edit', $data->id),
                    'details' => $data->details->map(function ($d) {
                        return [
                            'itemCode' => $d->itemCode,
                            'itemName' => optional($d->item)->name ?? '-',
                            'qty' => (float) $d->qty,
                            'price' => (float) $d->price,
                            'priceFormatted' => 'Rp '.number_format((float) $d->price, 0, ',', '.'),
                            'subtotal' => (float) ($d->qty * $d->price),
                            'subtotalFormatted' => 'Rp '.number_format((float) ($d->qty * $d->price), 0, ',', '.'),
                            'description' => $d->description ?: '-',
                        ];
                    }),
                ],
            ]);
        }

        return view($this->view.'show')
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
            return redirect()->route($this->view.'index')->with('fail', 'Data tidak ditemukan.');
        }

        $supplier = $this->supplierSvc->findAll();
        $fleet = $this->fleetSvc->findAll();
        $warehouse = $this->warehouseSvc->findAll();
        $items = Item::query()->orderBy('name', 'asc')->with(['latestPurchase'])->get();

        return view($this->view.'edit')
            ->with('view', $this->view)
            ->with('supplier', $supplier)
            ->with('fleet', $fleet)
            ->with('warehouse', $warehouse)
            ->with('items', $items)
            ->with('paymentTypes', DirectPurchaseService::PAYMENT_TYPES)
            ->with('internalBanks', $this->internalBanks())
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
            'ppn_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'date' => ['required', 'date'],
            'time' => ['required'],
            'paymentType' => ['required', 'in:'.implode(',', DirectPurchaseService::PAYMENT_TYPES)],
            'dueDate' => [
                'nullable',
                'required_if:paymentType,'.DirectPurchaseService::PAYMENT_TYPE_TEMPO,
                'date',
                'after_or_equal:date',
            ],
            'transferUserBankCode' => $this->transferBankRules(),
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
        ], $this->validationMessages());

        if ($validator->fails()) {
            return redirect()->back()->withInput()->with('fail', $validator->errors()->first());
        }

        try {
            DB::transaction(fn () => $this->service->update($request, $id, $this->title));

            return redirect()->route($this->view.'index')
                ->with('success', $this->title.' '.__('general.data_was_update_succesfully'));
        } catch (\Throwable $th) {
            return redirect()->back()->withInput()->with('fail', 'Terjadi kesalahan: '.$th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::transaction(fn () => $this->service->destroy($id, $this->title));

            return redirect()->route($this->view.'index')->with('success', 'Data Pembelian Langsung berhasil dihapus.');
        } catch (\Throwable $th) {
            return redirect()->route($this->view.'index')->with('fail', 'Gagal menghapus: '.$th->getMessage());
        }
    }

    /**
     * Daftar rekening internal (rekening_type = internal) untuk pilihan
     * sumber dana saat jenis pembayaran Transfer.
     */
    private function internalBanks()
    {
        return UserBank::query()
            ->with('bank')
            ->whereRaw('LOWER(TRIM(rekening_type)) = ?', ['internal'])
            ->orderBy('accountName')
            ->get();
    }

    /**
     * Aturan validasi rekening internal: wajib bila jenis pembayaran Transfer.
     */
    private function transferBankRules(): array
    {
        return [
            'nullable',
            'required_if:paymentType,'.DirectPurchaseService::PAYMENT_TYPE_TRANSFER,
            Rule::exists('user_bank', 'code')
                ->withoutTrashed()
                ->using(fn ($query) => $query->whereRaw('LOWER(TRIM(rekening_type)) = ?', ['internal'])),
        ];
    }

    /**
     * Pesan validasi berbahasa Indonesia.
     */
    private function validationMessages(): array
    {
        return [
            'dueDate.required_if' => 'Tanggal jatuh tempo wajib diisi untuk jenis pembayaran Tempo.',
            'dueDate.after_or_equal' => 'Tanggal jatuh tempo harus sama atau setelah tanggal pembelian.',
            'transferUserBankCode.required_if' => 'Rekening bank internal wajib dipilih untuk jenis pembayaran Transfer.',
            'transferUserBankCode.exists' => 'Rekening yang dipilih bukan rekening internal yang valid.',
        ];
    }

    /**
     * DataTables server-side feed
     */
    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->filteredDatatable($request->all());

            $search = trim((string) $request->input('search.value', ''));
            if ($search !== '') {
                $lower = mb_strtolower($search);
                $data->where(function ($query) use ($search, $lower) {
                    $query->where('purchase.code', 'like', '%'.$search.'%')
                        ->orWhere('purchase.date', 'like', '%'.$search.'%')
                        ->orWhere('purchase.paymentType', 'like', '%'.$search.'%')
                        ->orWhereRaw('CAST(purchase.subtotal AS CHAR) LIKE ?', ['%'.$search.'%'])
                        ->orWhereRaw('CAST(purchase.ppn_amount AS CHAR) LIKE ?', ['%'.$search.'%'])
                        ->orWhereRaw('CAST(purchase.ppn_rate AS CHAR) LIKE ?', ['%'.$search.'%'])
                        ->orWhereRaw('CAST(purchase.nominal AS CHAR) LIKE ?', ['%'.$search.'%'])
                        ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', '%'.$search.'%'))
                        ->orWhereHas('fleet', fn ($f) => $f->where('plateNumber', 'like', '%'.$search.'%')
                            ->orWhereHas('company', fn ($c) => $c->where('name', 'like', '%'.$search.'%')))
                        ->orWhereHas('maintenances', fn ($m) => $m->where('code', 'like', '%'.$search.'%'))
                        ->orWhereHas('warehouse', fn ($w) => $w->where('name', 'like', '%'.$search.'%'));

                    if (str_contains($lower, 'lunas') || $lower === 'paid') {
                        $query->orWhere('purchase.paymentStatus', 'Paid');
                    }
                    if (str_contains($lower, 'sebagian') || str_contains($lower, 'partial')) {
                        $query->orWhere('purchase.paymentStatus', 'Partial');
                    }
                    if (str_contains($lower, 'belum') || str_contains($lower, 'unpaid')) {
                        $query->orWhere('purchase.paymentStatus', 'Unpaid');
                    }
                });
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('purchaseDate', function ($row) {
                    $date = Carbon::parse($row->date)->format('d-M-Y');
                    $time = $row->time ? Carbon::parse($row->time)->format('H:i') : '';

                    return $date.($time ? ' <small class="text-muted">'.$time.'</small>' : '');
                })
                ->addColumn('fleetInfo', function ($row) {
                    if ($row->fleet) {
                        $owner = optional($row->fleet->company)->name;

                        return '<span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace fs-12 px-2 py-1">'
                            .e($row->fleet->plateNumber).'</span>'
                            .($owner ? '<small class="text-muted d-block mt-1">'.e($owner).'</small>' : '');
                    }

                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('supplierInfo', function ($row) {
                    return $row->supplier ? e($row->supplier->name) : '<span class="text-muted">-</span>';
                })
                ->addColumn('maintenanceBadge', function ($row) {
                    $mnt = $row->maintenances->first();
                    if ($mnt) {
                        return '<a href="'.route('warehouse.maintenance.index').'" class="badge bg-info-subtle text-info border border-info-subtle rounded-pill font-monospace fs-11 px-2 py-1 text-decoration-none" title="Buka menu Maintenance">'
                            .'<i class="mdi mdi-tools me-1"></i>'.e($mnt->code).'</a>';
                    }

                    return '<span class="badge bg-secondary-subtle text-secondary rounded-pill fs-11">Tidak Terhubung</span>';
                })
                ->addColumn('bankInfo', function ($row) {
                    $type = $row->paymentType ?: 'Tunai';
                    $map = [
                        'Tunai' => ['bg-success-subtle text-success border-success-subtle', 'mdi-cash'],
                        'Transfer' => ['bg-primary-subtle text-primary border-primary-subtle', 'mdi-bank-transfer'],
                        'Tempo' => ['bg-warning-subtle text-warning-emphasis border-warning-subtle', 'mdi-clock-outline'],
                    ];
                    [$classes, $icon] = $map[$type] ?? $map['Tunai'];

                    $status = $row->paymentStatus ?: 'Unpaid';
                    $statusMap = [
                        'Paid' => ['bg-success-subtle text-success', 'Lunas'],
                        'Partial' => ['bg-warning-subtle text-warning-emphasis', 'Dibayar Sebagian'],
                        'Unpaid' => ['bg-secondary-subtle text-secondary', 'Belum Dibayar'],
                    ];
                    [$statusClasses, $statusLabel] = $statusMap[$status] ?? $statusMap['Unpaid'];

                    return '<span class="badge '.$classes.' border rounded-pill fs-11">'
                        .'<i class="mdi '.$icon.' me-1"></i>'.e($type).'</span>'
                        .($type === 'Transfer' && $row->transferUserBank
                            ? '<small class="d-block text-muted fs-10 mt-1">'
                                .e($row->transferUserBank->bank->name ?? 'Bank').' · '
                                .e($row->transferUserBank->accountNumber).'</small>'
                            : '')
                        .'<small class="d-block mt-1"><span class="badge '.$statusClasses.' rounded-pill fs-10">'
                        .e($statusLabel).'</span></small>';
                })
                ->addColumn('itemSummary', function ($row) {
                    $count = $row->details->count();
                    $totalQty = $row->details->sum('qty');

                    return '<span class="fw-semibold">'.number_format($count, 0, ',', '.').' Item</span>'
                        .'<small class="text-muted d-block">'.rtrim(rtrim(number_format((float) $totalQty, 1, ',', '.'), '0'), ',').' Qty</small>';
                })
                ->addColumn('subtotalAmount', function ($row) {
                    $subtotal = (float) ($row->subtotal ?: $row->details->sum(fn ($d) => (float) $d->price * (float) $d->qty));

                    return 'Rp '.number_format($subtotal, 0, ',', '.');
                })
                ->addColumn('ppnInfo', function ($row) {
                    $ppnRate = (float) ($row->ppn_rate ?? 0);
                    $ppnAmount = (float) ($row->ppn_amount ?? 0);
                    if ($ppnRate <= 0 && $ppnAmount <= 0) {
                        return '<span class="text-muted">-</span>';
                    }
                    $rateFormatted = rtrim(rtrim(number_format($ppnRate, 2, ',', '.'), '0'), ',');

                    return '<span>Rp '.number_format($ppnAmount, 0, ',', '.').'</span>'
                        .'<small class="text-muted d-block">('.$rateFormatted.'%)</small>';
                })
                ->addColumn('totalPrice', function ($row) {
                    $total = (float) ($row->nominal ?: $row->details->sum(fn ($d) => (float) $d->price * (float) $d->qty));

                    return '<a href="javascript:void(0)" onclick="showDetailModal(\''.$row->id.'\')" class="font-monospace fw-bold text-dark text-decoration-none" data-bs-toggle="tooltip" title="Klik untuk lihat detail biaya">'
                        .'Rp '.number_format($total, 0, ',', '.').'</a>';
                })
                ->addColumn('action', function ($row) {
                    $editUrl = route($this->view.'edit', $row->id);

                    $btn = '<div class="d-flex align-items-center justify-content-center gap-1">';
                    $btn .= '<button type="button" class="btn btn-icon btn-sm bg-info-subtle" onclick="showDetailModal(\''.$row->id.'\')" data-bs-toggle="tooltip" title="Lihat Detail Biaya">'
                        .'<i class="mdi mdi-eye fs-14 text-info"></i></button>';
                    $btn .= '<a href="'.$editUrl.'" class="btn btn-icon btn-sm bg-primary-subtle" data-bs-toggle="tooltip" title="Edit Data">'
                        .'<i class="mdi mdi-pencil-outline fs-14 text-primary"></i></a>';
                    $btn .= '<button type="button" class="btn btn-icon btn-sm bg-danger-subtle" onclick="deleteData(\''.$row->id.'\')" data-bs-toggle="tooltip" title="Hapus Data">'
                        .'<i class="mdi mdi-delete fs-14 text-danger"></i></button>';
                    $btn .= '</div>';

                    return $btn;
                })
                ->rawColumns(['purchaseDate', 'fleetInfo', 'supplierInfo', 'maintenanceBadge', 'bankInfo', 'itemSummary', 'subtotalAmount', 'ppnInfo', 'totalPrice', 'action'])
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
        $base = 'PL-'.$carbonDate->format('ymd');

        $code = GenerateCode::generateCodeAscDate(
            $base,
            Purchase::class,
            'date',
            $date
        );

        return response()->json(['code' => $code]);
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(
            new DirectPurchaseExport($request->all(), $this->service),
            'Pembelian-Langsung-'.now()->format('YmdHis').'.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $purchases = $this->service->filteredDatatable($request->all())->get();

        $filterSummary = [];
        if ($request->filled('fleetCode')) {
            $plate = Fleet::query()->where('code', $request->fleetCode)->value('plateNumber');
            $filterSummary[] = 'Armada: '.($plate ?: $request->fleetCode);
        }
        if ($request->filled('supplierCode')) {
            $supplierName = Supplier::query()->where('code', $request->supplierCode)->value('name');
            $filterSummary[] = 'Supplier: '.($supplierName ?: $request->supplierCode);
        }
        if ($request->filled('paymentStatus')) {
            $statusLabel = match ($request->paymentStatus) {
                'Paid' => 'Lunas',
                'Partial' => 'Sebagian',
                'Unpaid' => 'Belum Dibayar',
                default => $request->paymentStatus,
            };
            $filterSummary[] = 'Status: '.$statusLabel;
        }
        if ($request->filled('startDate') || $request->filled('endDate')) {
            $start = $request->filled('startDate') ? Carbon::parse($request->startDate)->format('d-m-Y') : 'Awal';
            $end = $request->filled('endDate') ? Carbon::parse($request->endDate)->format('d-m-Y') : 'Sekarang';
            $filterSummary[] = 'Periode: '.$start.' s/d '.$end;
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

        $mpdf->WriteHTML(view($this->view.'report.direct-purchase-pdf', [
            'purchases' => $purchases,
            'title' => 'Laporan Pembelian Langsung',
            'filterSummary' => $filterSummary,
            'printedAt' => now(),
        ])->render());

        return response($mpdf->Output('', 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="Pembelian-Langsung-'.now()->format('YmdHis').'.pdf"');
    }
}
