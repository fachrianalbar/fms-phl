<?php

namespace App\Http\Controllers\Purchasing;

use App\Helpers\FilterHelper;
use App\Helpers\GenerateCode;
use App\Http\Controllers\Controller;
use App\Models\Inventory\Item;
use App\Models\Inventory\Stock;
use App\Models\Purchasing\Purchase;
use App\Models\Purchasing\PurchaseDetail;
use App\Models\StockTransaction;
use App\Services\Inventory\SupplierService;
use App\Services\MenuService;
use App\Services\Purchasing\PurchaseService;
use App\Services\UniqueCodeService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class PurchaseController extends Controller
{
    protected $service;

    protected $supplierSvc;

    protected $title;

    protected $view;

    protected $menuSvc;

    public function __construct(PurchaseService $purchaseSvc, SupplierService $supplierSvc, MenuService $menuSvc)
    {
        $this->service = $purchaseSvc;
        $this->supplierSvc = $supplierSvc;
        $this->title = 'Purchase';
        $this->menuSvc = $menuSvc->getByName('Purchase');
        $this->title = Auth::user()->languange == 'en' ? $this->menuSvc->name : $this->menuSvc->nama;
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
        $items = Item::OrderBy('name', 'asc')->with(['latestPurchase'])->get();

        return view($this->view.'create')
            ->with('view', $this->view)
            ->with('supplier', $supplier)
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
            'date' => 'required',
            'time' => 'required',
            'itemCode' => 'required|array',
            'itemCode.*' => 'required',
            'description' => 'sometimes|array',
            'description.*' => 'nullable|string',
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
        $items = Item::OrderBy('name', 'asc')->with(['latestPurchase'])->get();

        return view($this->view.'edit')
            ->with('view', $this->view)
            ->with('supplier', $supplier)
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
            $data = $this->service->datatable();

            // Definisikan kolom filter dengan alias
            $filters = [
                'code' => $request->code,
                'supplierCode' => $request->supplierCode,
            ];

            // Hubungkan alias ke relasi dan kolom yang sesuai
            $relations = [];

            $dateFilters = [
                'date' => [
                    'start' => $request->startDate,
                    'end' => $request->endDate,
                ],
            ];

            $data = FilterHelper::applyFilters($data, $filters, $relations, $dateFilters);

            if ($request->paymentStatus === 'Paid') {
                $data->where('paymentStatus', 'Paid');
            } elseif ($request->paymentStatus === 'unpaid') {
                $data->where('paymentStatus', '!=', 'Paid');
            }

            // Pencarian global DataTables (kotak pencarian) — kode, supplier, gudang.
            $search = $request->input('search.value');

            if (! empty($search)) {
                $data->where(function ($query) use ($search) {
                    $query->where('purchase.code', 'like', '%'.$search.'%')
                        ->orWhereHas('supplier', fn ($supplier) => $supplier->where('name', 'like', '%'.$search.'%'))
                        ->orWhereHas('warehouse', fn ($warehouse) => $warehouse->where('name', 'like', '%'.$search.'%'));
                });
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('purchaseDate', function ($row) {
                    $date = Carbon::parse($row->date)->format('d-M-Y');
                    $time = Carbon::parse($row->time)->format('H:i');

                    return $date;
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
                ->addColumn('totalPrice', function ($row) {
                    // Use nominal from header (saved from detail price * qty)
                    $totalPrice = $row->nominal ?? 0;

                    // Fallback: recalculate from details if nominal is empty
                    if (! $totalPrice) {
                        foreach ($row->details as $item) {
                            $totalPrice += intval($item->price) * $item->qty;
                        }
                    }

                    return number_format($totalPrice, 0, ',', '.');
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
                    $isDetail = ($row->paidAmount > 0 || $row->status == 3);
                    $icon = $isDetail ? 'mdi-eye' : 'mdi-pencil-outline';
                    $title = $isDetail ? 'Detail' : 'Edit';

                    $btn = '<a href="'.route($this->view.($isDetail ? 'show' : 'edit'), $row->id).'" '
                        .'class="btn btn-icon btn-sm bg-primary-subtle me-1" '
                        .'data-bs-toggle="tooltip" title="'.$title.'">'
                        .'<i class="mdi '.$icon.' fs-14 text-primary"></i></a>';

                    if (! (in_array($row->status, [1, 2, 3]) || $row->paidAmount > 0)) {
                        $btn .= '<a href="javascript:deleteData(\''.$row->id.'\')" '
                            .'class="btn btn-icon btn-sm bg-danger-subtle" '
                            .'data-bs-toggle="tooltip" title="Delete">'
                            .'<i class="mdi mdi-delete fs-14 text-danger"></i></a>';
                    }

                    return $btn;
                })
                ->rawColumns(['purchaseDate', 'dueDate', 'supplier.name', 'warehouse.name', 'totalPrice', 'paymentStatusHtml', 'action'])
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
}
