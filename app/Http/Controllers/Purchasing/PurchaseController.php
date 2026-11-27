<?php

namespace App\Http\Controllers\Purchasing;

use App\Exports\PurchaseExport;
use App\Helpers\GenerateCode;
use App\Http\Controllers\Controller;
use App\Models\Inventory\Item;
use App\Models\Inventory\Stock;
use App\Models\Inventory\Supplier;
use App\Models\Purchasing\Purchase;
use App\Models\Purchasing\PurchaseDetail;
use App\Models\StockTransaction;
use App\Services\Inventory\SupplierService;
use App\Services\Master\FleetService;
use App\Services\MenuService;
use App\Services\Purchasing\PurchaseService;
use App\Services\UniqueCodeService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;
use Yajra\DataTables\DataTables;

class PurchaseController extends Controller
{
    protected $service;

    protected $supplierSvc;

    protected FleetService $fleetSvc;

    protected $title;

    protected $view;

    protected $menuSvc;

    public function __construct(PurchaseService $purchaseSvc, SupplierService $supplierSvc, FleetService $fleetSvc, MenuService $menuSvc)
    {
        $this->service = $purchaseSvc;
        $this->supplierSvc = $supplierSvc;
        $this->fleetSvc = $fleetSvc;
        $this->menuSvc = $menuSvc->getByName('Purchase');
        $this->title = (Auth::user()?->languange ?? 'id') === 'en'
            ? ($this->menuSvc?->name ?? 'Purchase')
            : ($this->menuSvc?->nama ?? 'Purchase');
        $this->view = 'purchasing.purchase.';
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $supplier = $this->supplierSvc->findAll();
        $stats = $this->service->stats();

        return view($this->view.'index')
            ->with('view', $this->view)
            ->with('supplier', $supplier)
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
        $items = Item::OrderBy('name', 'asc')->with(['latestPurchase'])->get();

        return view($this->view.'create')
            ->with('view', $this->view)
            ->with('supplier', $supplier)
            ->with('fleet', $fleet)
            ->with('items', $items)
            ->with('title', $this->title);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => ['required', 'string', 'max:30'],
            'supplierCode' => 'required',
            'fleetCode' => ['nullable', 'string'],
            'ppn_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'date' => 'required',
            'time' => 'required',
            'itemCode' => 'required|array',
            'itemCode.*' => 'required',
            'description' => 'sometimes|array',
            'description.*' => 'nullable|string',
            'price' => ['required', 'array', function ($attribute, $value, $fail) {
                foreach ($value as $price) {
                    if ($price == '0') {
                        $fail('The price cannot be 0.');
                    }
                }
            }],
            'qty' => ['required', 'array', function ($attribute, $value, $fail) {
                foreach ($value as $qty) {
                    if (! is_numeric($qty) || (float) $qty <= 0) {
                        $fail('The qty must be greater than 0.');
                    }

                    if (abs(((float) $qty * 2) - round((float) $qty * 2)) > 0.0001) {
                        $fail('The qty must be an integer or .5 increment.');
                    }
                }
            }],
        ]);
        if ($validator->fails()) {
            return redirect()->route($this->view.'index')->with('fail', $validator->errors()->all()[0]);
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
        $data = $this->service->getById($id);

        if (! $data) {
            return redirect()->route($this->view.'index')->with('fail', 'Data not found');
        }

        $totalPrice = 0;
        $totalQty = 0;
        foreach ($data->details as $item) {
            $totalPrice += intval($item->price) * $item->qty;
            $totalQty += $item->qty;
        }

        return view($this->view.'show')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('totalPrice', $totalPrice)
            ->with('totalQty', $totalQty)
            ->with('data', $data);
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

        $totalPrice = 0;
        $totalQty = 0;
        foreach ($data->details as $item) {
            $totalPrice += intval($item->price) * $item->qty;
            $totalQty += $item->qty;
        }

        $supplier = $this->supplierSvc->findAll();
        $fleet = $this->fleetSvc->findAll();
        $items = Item::OrderBy('name', 'asc')->with(['latestPurchase'])->get();

        return view($this->view.'edit')
            ->with('view', $this->view)
            ->with('supplier', $supplier)
            ->with('fleet', $fleet)
            ->with('items', $items)
            ->with('title', $this->title)
            ->with('totalPrice', $totalPrice)
            ->with('totalQty', $totalQty)
            ->with('data', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            // 'code' => 'required',
            'supplierCode' => 'required',
            'fleetCode' => ['nullable', 'string'],
            'ppn_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'date' => 'required',
            'time' => 'required',
            'itemCode' => 'required|array',
            'itemCode.*' => 'required',
            'price' => ['required', 'array', function ($attribute, $value, $fail) {
                foreach ($value as $price) {
                    if ($price == '0') {
                        $fail('The price cannot be 0.');
                    }
                }
            }],
            'qty' => ['required', 'array', function ($attribute, $value, $fail) {
                foreach ($value as $qty) {
                    if (! is_numeric($qty) || (float) $qty <= 0) {
                        $fail('The qty must be greater than 0.');
                    }

                    if (abs(((float) $qty * 2) - round((float) $qty * 2)) > 0.0001) {
                        $fail('The qty must be an integer or .5 increment.');
                    }
                }
            }],
        ]);

        if ($validator->fails()) {
            return redirect()->route($this->view.'index')->with('fail', $validator->errors()->all()[0]);
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
        $this->service->destroy($id, $this->title);

        return redirect()->route($this->view.'index')->with('success', 'Delete Data Success');
    }

    public function deletePurchaseDetail($id)
    {
        $pd = PurchaseDetail::where('id', $id)->first();

        // Rollback stock transaction
        $stockTransaction = StockTransaction::where('transactionDetailCode', $pd->code)->first();
        if ($stockTransaction) {
            Stock::where('itemCode', $stockTransaction->itemCode)->decrement('stockIn', $stockTransaction->qtyIn);
            $stockTransaction->delete();
        }

        $pd->delete();

        return redirect()->route($this->view.'edit', $pd->purchase->id)->with('success', 'Delete Data Success');
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->filteredDatatable($request->all());

            // Pencarian global DataTables — kode, supplier, gudang, nopol, pemilik, tanggal, nominal/PPN, status.
            $search = trim((string) $request->input('search.value', ''));

            if ($search !== '') {
                $lower = mb_strtolower($search);
                $data->where(function ($query) use ($search, $lower) {
                    $query->where('purchase.code', 'like', '%'.$search.'%')
                        ->orWhere('purchase.date', 'like', '%'.$search.'%')
                        ->orWhere('purchase.dueDate', 'like', '%'.$search.'%')
                        ->orWhereRaw('CAST(purchase.subtotal AS CHAR) LIKE ?', ['%'.$search.'%'])
                        ->orWhereRaw('CAST(purchase.ppn_amount AS CHAR) LIKE ?', ['%'.$search.'%'])
                        ->orWhereRaw('CAST(purchase.ppn_rate AS CHAR) LIKE ?', ['%'.$search.'%'])
                        ->orWhereRaw('CAST(purchase.nominal AS CHAR) LIKE ?', ['%'.$search.'%'])
                        ->orWhereHas('supplier', fn ($supplier) => $supplier->where('name', 'like', '%'.$search.'%'))
                        ->orWhereHas('warehouse', fn ($warehouse) => $warehouse->where('name', 'like', '%'.$search.'%'))
                        ->orWhereHas('fleet', fn ($fleet) => $fleet->where('plateNumber', 'like', '%'.$search.'%')
                            ->orWhereHas('company', fn ($company) => $company->where('name', 'like', '%'.$search.'%')));

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
                    return Carbon::parse($row->date)->format('d-M-Y');
                })
                ->editColumn('receivedDate', function ($row) {
                    $receivedDate = '';
                    if ($row->receivedDate) {
                        $receivedDate = Carbon::parse($row->receivedDate)->format('d-M-Y');
                    }

                    return $receivedDate;
                })
                ->editColumn('dueDate', function ($row) {
                    $dueDate = '';
                    if ($row->dueDate) {
                        $dueDate = Carbon::parse($row->dueDate)->format('d-M-Y');
                    }

                    return $dueDate;
                })
                ->editColumn('paymentDate', function ($row) {
                    $paymentDate = '';
                    if ($row->paymentDate) {
                        $paymentDate = Carbon::parse($row->paymentDate)->format('d-M-Y');
                    }

                    return $paymentDate;
                })
                ->addColumn('fleetInfo', function ($row) {
                    if (! $row->fleet) {
                        return '<span class="text-muted">-</span>';
                    }
                    $owner = optional($row->fleet->company)->name;

                    return '<span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace fs-11 px-2 py-1">'
                        .e($row->fleet->plateNumber).'</span>'
                        .($owner ? '<small class="text-muted d-block mt-1">'.e($owner).'</small>' : '');
                })
                ->addColumn('subtotalAmount', function ($row) {
                    $subtotal = (float) ($row->subtotal ?? 0);
                    if ($subtotal <= 0) {
                        $subtotal = (float) ($row->nominal ?? 0);
                        if ($subtotal <= 0) {
                            $subtotal = (float) $row->details->sum(fn ($item) => (float) $item->price * (float) $item->qty);
                        }
                    }

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
                    $totalPrice = (float) ($row->nominal ?? 0);

                    if ($totalPrice <= 0) {
                        $totalPrice = (float) $row->details->sum(fn ($item) => (float) $item->price * (float) $item->qty);
                    }

                    return '<span class="fw-semibold">Rp '.number_format($totalPrice, 0, ',', '.').'</span>';
                })
                ->editColumn('supplier.name', function ($row) {
                    $supplier = '';

                    if (isset($row->supplier->name)) {
                        $supplier = e($row->supplier->name);
                    }

                    return $supplier;
                })
                ->editColumn('warehouse.name', function ($row) {
                    $warehouse = '';

                    if (isset($row->warehouse->name)) {
                        $warehouse = e($row->warehouse->name);
                    }

                    return $warehouse;
                })
                ->addColumn('paymentStatusHtml', function ($row) {
                    if ($row->paymentStatus == 'Paid') {
                        return '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill fs-11">Lunas</span>';
                    }

                    if ($row->paymentStatus == 'Partial') {
                        return '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill fs-11">Sebagian</span>';
                    }

                    return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill fs-11">Belum Bayar</span>';
                })
                ->addColumn('action', function ($row) {
                    $canEdit = ! ($row->paidAmount > 0 || $row->status == 3);
                    $canDelete = ! (in_array($row->status, [1, 2, 3]) || $row->paidAmount > 0);

                    $btn = '<div class="d-inline-flex align-items-center justify-content-center gap-1">';
                    $btn .= '<a href="'.route($this->view.'show', $row->id).'" '
                        .'class="btn btn-icon btn-sm bg-info-subtle" '
                        .'data-bs-toggle="tooltip" title="Detail">'
                        .'<i class="mdi mdi-eye fs-14 text-info"></i></a>';

                    if ($canEdit) {
                        $btn .= '<a href="'.route($this->view.'edit', $row->id).'" '
                            .'class="btn btn-icon btn-sm bg-primary-subtle" '
                            .'data-bs-toggle="tooltip" title="Edit">'
                            .'<i class="mdi mdi-pencil-outline fs-14 text-primary"></i></a>';
                    }

                    if ($canDelete) {
                        $btn .= '<a href="javascript:deleteData(\''.$row->id.'\')" '
                            .'class="btn btn-icon btn-sm bg-danger-subtle" '
                            .'data-bs-toggle="tooltip" title="Delete">'
                            .'<i class="mdi mdi-delete fs-14 text-danger"></i></a>';
                    }
                    $btn .= '</div>';

                    return $btn;
                })
                ->rawColumns(['purchaseDate', 'dueDate', 'supplier.name', 'warehouse.name', 'fleetInfo', 'subtotalAmount', 'ppnInfo', 'totalPrice', 'paymentStatusHtml', 'action'])
                ->toJson();
        }
    }

    public function itemBySupplier($supplierCode)
    {
        return Item::where('supplierCode', $supplierCode)->get();
    }

    public function generateCode(Request $request)
    {
        $date = $request->date;

        $code = GenerateCode::generateCodeAscDate(
            'PO',
            Purchase::class,
            'date',
            $date,
        );

        return response()->json(['code' => $code]);
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(
            new PurchaseExport($request->all(), $this->service),
            'Pembelian-'.now()->format('YmdHis').'.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $purchases = $this->service->filteredDatatable($request->all())->get();

        $filterSummary = [];
        if ($request->filled('code')) {
            $filterSummary[] = 'Kode PO: '.$request->code;
        }
        if ($request->filled('supplierCode')) {
            $supplierName = Supplier::query()->where('code', $request->supplierCode)->value('name');
            $filterSummary[] = 'Supplier: '.($supplierName ?: $request->supplierCode);
        }
        if ($request->filled('paymentStatus')) {
            $statusLabel = match ($request->paymentStatus) {
                'Paid' => 'Sudah Lunas',
                'unpaid' => 'Belum Lunas',
                'Partial' => 'Sebagian',
                'Unpaid' => 'Belum Bayar',
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

        $mpdf->WriteHTML(view($this->view.'report.purchase-pdf', [
            'purchases' => $purchases,
            'title' => 'Laporan Pembelian (PO)',
            'filterSummary' => $filterSummary,
            'printedAt' => now(),
        ])->render());

        return response($mpdf->Output('', 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="Pembelian-'.now()->format('YmdHis').'.pdf"');
    }
}
