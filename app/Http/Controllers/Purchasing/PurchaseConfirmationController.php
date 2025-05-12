<?php

namespace App\Http\Controllers\Purchasing;

use App\Helpers\FilterHelper;
use App\Http\Controllers\Controller;
use App\Models\Inventory\Item;
use App\Models\Purchasing\PurchaseDetail;
use App\Services\Inventory\SupplierService;
use App\Services\Purchasing\PurchaseConfirmationService;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PurchaseConfirmationController extends Controller
{
    protected $service;
    protected $supplierSvc;
    protected $title;
    protected $view;


    public function __construct(PurchaseConfirmationService $purchaseConfirmationSvc, SupplierService $supplierSvc)
    {
        $this->service = $purchaseConfirmationSvc;
        $this->supplierSvc = $supplierSvc;
        $this->title = "Purchase Confirmation";
        $this->view = "purchasing.purchase-confirmation.";
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
        // $items = Item::OrderBy('name', 'asc')->get();

        return view($this->view . 'edit')
            ->with('view', $this->view)
            ->with('supplier', $supplier)
            // ->with('items', $items)
            ->with('title', $this->title)
            ->with('totalPrice', $totalPrice)
            ->with('totalQty', $totalQty)
            ->with('data', $data);
    }

    public function update(Request $request, string $id)
    {
        $selectedPurchase = $request->input('confirm');

        $data = $this->service->getById($id);

        if (count($selectedPurchase) == 1) {
            $validator = Validator::make($request->all(), [
                'receivedDate' => ['required', 'date', 'after_or_equal:' . $data->date],
                'receivedQty' => ['required'],
            ]);
        }

        $validator = Validator::make($request->all(), [
            'receivedDate' => ['required', 'date', 'after_or_equal:' . $data->date],
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
                ->addColumn('purchaseStatus', function ($row) {
                    $total = $row->details->count();

                    $count = 0;

                    foreach ($row->details as $item) {
                        if ($item->status == 1) {
                            $count++;
                        }
                    }

                    if ($count == $total) {
                        return 'Stored Full';
                    }

                    if ($count > 0 && $count < $total) {
                        return 'Stored Half';
                    }
                    return 'No Stored';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<ul class="action">
                                        <li class="edit"> <a href="' . route($this->view . 'edit', $row->id) . '"><i class="icon-pencil-alt"></i></a></li>
                                    </ul>';

                    return $btn;
                })
                ->rawColumns(['purchaseDate', 'supplier.name', 'warehouse.name', 'totalPrice', 'purchaseStatus', 'action'])
                ->toJson();
        }
    }

    public function purchaseDetail($id)
    {
        $data = PurchaseDetail::where('id', $id)->with(['item'])->first();

        return response()->json($data);
    }
}
