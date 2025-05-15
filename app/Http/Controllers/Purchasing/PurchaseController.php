<?php

namespace App\Http\Controllers\Purchasing;

use App\Helpers\FilterHelper;
use App\Http\Controllers\Controller;
use App\Models\Inventory\Item;
use App\Models\Inventory\Stock;
use App\Models\Purchasing\PurchaseDetail;
use App\Models\StockTransaction;
use App\Services\Inventory\SupplierService;
use App\Services\Purchasing\PurchaseService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;


class PurchaseController extends Controller
{
    protected $service;
    protected $supplierSvc;
    protected $title;
    protected $view;


    public function __construct(PurchaseService $purchaseSvc, SupplierService $supplierSvc)
    {
        $this->service = $purchaseSvc;
        $this->supplierSvc = $supplierSvc;
        $this->title = "Purchase";
        $this->view = "purchasing.purchase.";
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $supplier = $this->supplierSvc->findAll();

        return view($this->view . 'index')
            ->with('view', $this->view)
            ->with('supplier', $supplier)
            ->with('title', $this->title);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $supplier = $this->supplierSvc->findAll();
        $items = Item::OrderBy('name', 'asc')->get();


        return view($this->view . 'create')
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
            'code' => ['required', Rule::unique('purchase', 'code')->whereNull('deleted_at')],
            'supplierCode' => 'required',
            'date' => 'required',
            'time' => 'required',
            'price' => ['required', 'array', function ($attribute, $value, $fail) {
                foreach ($value as $price) {
                    if ($price == "0") {
                        $fail('The price cannot be 0.');
                    }
                }
            }],
            'qty' => ['required', 'array', function ($attribute, $value, $fail) {
                foreach ($value as $price) {
                    if ($price == 0 || $price == null) {
                        $fail('The qty cannot be 0.');
                    }
                }
            }],
        ]);
        if ($validator->fails()) {
            return redirect()->route($this->view . 'index')->with('fail', $validator->errors()->all()[0]);
        }
        try {
            DB::beginTransaction();

            $this->service->store($request, $this->title);

            DB::commit();

            return redirect()->route($this->view . 'index')->with('success', $this->title . ' data was save succesfully');
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->route($this->view . 'index')->with('fail', 'Line : ' . $th->getLine() . '<br>' . $th->getMessage());
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

        if (!$data) {
            return redirect()->route($this->view . 'index')->with('fail', 'Data not found');
        }

        $totalPrice = 0;
        $totalQty = 0;
        foreach ($data->details as $item) {
            $itemStock = Item::where('code', $item->itemCode)->first();

            $totalPrice += intval($itemStock->price) * $item->qty;
            $totalQty += $item->qty;
        }

        $supplier = $this->supplierSvc->findAll();
        $items = Item::OrderBy('name', 'asc')->get();

        return view($this->view . 'edit')
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
            'code' => 'required',
            'supplierCode' => 'required',
            'date' => 'required',
            'time' => 'required',
            'price' => ['required', 'array', function ($attribute, $value, $fail) {
                foreach ($value as $price) {
                    if ($price == "0") {
                        $fail('The price cannot be 0.');
                    }
                }
            }],
            'qty' => ['required', 'array', function ($attribute, $value, $fail) {
                foreach ($value as $price) {
                    if ($price == 0 || $price == null) {
                        $fail('The qty cannot be 0.');
                    }
                }
            }]
        ]);

        if ($validator->fails()) {
            return redirect()->route($this->view . 'index')->with('fail', $validator->errors()->all()[0]);
        }

        try {
            DB::beginTransaction();

            $this->service->update($request, $id, $this->title);

            DB::commit();

            return redirect()->route($this->view . 'index')->with('success', $this->title .  ' data was update succesfully');
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

    public function deletePurchaseDetail($id)
    {
        $pd = PurchaseDetail::where('id', $id)->first();

        $pd->delete();

        return redirect()->route($this->view . 'edit', $pd->purchase->id)->with('success', 'Delete Data Success');
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

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('purchaseDate', function ($row) {
                    $date = Carbon::parse($row->date)->format('d-M-Y');
                    $time = Carbon::parse($row->time)->format('H:i');
                    return $date;
                })
                ->editColumn('receivedDate', function ($row) {
                    $receivedDate = '';
                    if ($row->receivedDate) {
                        $receivedDate =   Carbon::parse($row->receivedDate)->format('d-M-Y');
                    }

                    return $receivedDate;
                })
                ->editColumn('paymentDate', function ($row) {
                    $paymentDate = '';
                    if ($row->paymentDate) {
                        $paymentDate =   Carbon::parse($row->paymentDate)->format('d-M-Y');
                    }

                    return $paymentDate;
                })
                ->addColumn('totalPrice', function ($row) {
                    $totalPrice = 0;
                    foreach ($row->details as $item) {
                        $itemStock = Item::where('code', $item->itemCode)->first();

                        if ($item->receivedQty) {
                            $totalPrice += intval($itemStock->price) * $item->receivedQty;
                        } else {
                            $totalPrice += intval($itemStock->price) * $item->qty;
                        }
                    }

                    return number_format($totalPrice, 0, ',', '.');
                })
                ->editColumn('supplier.name', function ($row) {
                    $supplier = '';

                    if (isset($row->supplier->name)) {
                        $supplier = $row->supplier->name;
                    }

                    return $supplier;
                })
                ->editColumn('warehouse.name', function ($row) {
                    $warehouse = '';

                    if (isset($row->warehouse->name)) {
                        $warehouse = $row->warehouse->name;
                    }

                    return $warehouse;
                })
                ->editColumn('purchaseStatus.name', function ($row) {
                    $status = '';

                    if (isset($row->purchaseStatus->name)) {
                        $status = $row->purchaseStatus->name;
                    }

                    if ($row->status == 2) {
                        $total = $row->details->count();

                        $count = 0;

                        foreach ($row->details as $item) {
                            if ($item->status == 1) {
                                $count++;
                            }
                        }

                        if ($count == $total) {
                            $status = 'Stored Full';
                        }

                        if ($count > 0 && $count < $total) {
                            $status = 'Stored Half';
                        }

                        if ($count == 0) {
                            $status = 'No Stored';
                        }
                    }

                    return $status;
                })
                ->addColumn('action', function ($row) {
                    $btn = '<td>
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

                    if (in_array($row->status, [1, 2, 3])) {
                        $btn = '<td>
                                    <a href="' . route($this->view . 'edit', $row->id) . '"
                                    class="btn btn-icon btn-sm bg-primary-subtle me-1"
                                    data-bs-toggle="tooltip" title="Edit">
                                        <i class="mdi mdi-pencil-outline fs-14 text-primary"></i>
                                    </a>
                                </td>';
                    }

                    return $btn;
                })
                ->rawColumns(['purchaseDate', 'supplier.name', 'warehouse.name', 'totalPrice', 'purchaseStatus.name', 'paymentDate', 'action'])
                ->toJson();
        }
    }

    public function itemBySupplier($supplierCode)
    {
        return Item::where('supplierCode', $supplierCode)->get();
    }
}
