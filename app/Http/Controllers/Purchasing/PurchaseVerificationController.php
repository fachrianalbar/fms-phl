<?php

namespace App\Http\Controllers\Purchasing;

use App\Helpers\FilterHelper;
use App\Http\Controllers\Controller;
use App\Services\MenuService;
use App\Models\Inventory\Item;
use App\Models\Purchasing\PurchaseDetail;
use App\Services\Inventory\SupplierService;
use App\Services\Purchasing\PurchaseVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class PurchaseVerificationController extends Controller
{
    protected $service;
    protected $supplierSvc;
    protected $title;
    protected $view;
    protected $menuSvc;


    public function __construct(PurchaseVerificationService $purchaseVerificationSvc, SupplierService $supplierSvc, MenuService $menuSvc)
    {
        $this->service = $purchaseVerificationSvc;
        $this->supplierSvc = $supplierSvc;
        $this->title = "Purchase Verification";
        $this->view = "purchasing.purchase-verification.";
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
        $validator = Validator::make($request->all(), [
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
                    // return $date . ' ' . $time;
                    return $date;
                })
                ->addColumn('totalPrice', function ($row) {
                    $totalPrice = 0;
                    foreach ($row->details as $item) {

                        $totalPrice += intval($item->price) * $item->qty;
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
                ->addColumn('action', function ($row) {
                    $btn = ' <td>
                        <a href="' . route($this->view . 'edit', $row->id) . '"
                        class="btn btn-icon btn-sm bg-primary-subtle me-1"
                        data-bs-toggle="tooltip" title="Edit">
                            <i class="mdi mdi-pencil-outline fs-14 text-primary"></i>
                        </a>
                    </td>';

                    return $btn;
                })
                ->rawColumns(['purchaseDate', 'supplier.name', 'warehouse.name', 'totalPrice', 'action'])
                ->toJson();
        }
    }
}
