<?php

namespace App\Http\Controllers\Purchasing;

use App\Helpers\FilterHelper;
use App\Http\Controllers\Controller;
use App\Services\MenuService;
use App\Models\Inventory\Item;
use App\Models\LiveMutation;
use App\Services\Bank\UserBankService;
use App\Services\Inventory\SupplierService;
use App\Services\Purchasing\PurchasePaymentService;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PurchasePaymentController extends Controller
{
    protected $service;
    protected $supplierSvc;
    protected $userBankSvc;
    protected $title;
    protected $view;
    protected $menuSvc;


    public function __construct(PurchasePaymentService $purchasePaymentSvc, SupplierService $supplierSvc, UserBankService $userBankSvc, MenuService $menuSvc)
    {
        $this->service = $purchasePaymentSvc;
        $this->supplierSvc = $supplierSvc;
        $this->userBankSvc = $userBankSvc;
        $this->title = "Purchase Payment";
        $this->menuSvc = $menuSvc->getByName("Purchase Payment");
        $this->title = Auth::user()->languange == 'en' ? $this->menuSvc->name : $this->menuSvc->nama;
        $this->view = "purchasing.purchase-payment.";
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
            $totalPrice += intval($item->price) * $item->receivedQty;
            $totalQty += $item->receivedQty;
        }

        $supplier = $this->supplierSvc->findAll();
        // $items = Item::OrderBy('name', 'asc')->get();

        $userBank = $this->userBankSvc->findCompany();

        return view($this->view . 'edit')
            ->with('view', $this->view)
            ->with('supplier', $supplier)
            // ->with('items', $items)
            ->with('title', $this->title)
            ->with('totalPrice', $totalPrice)
            ->with('userBank', $userBank)
            ->with('totalQty', $totalQty)
            ->with('data', $data);
    }

    public function update(Request $request, string $id)
    {
        $data = $this->service->getById($id);

        $validator = Validator::make($request->all(), [
            'paymentDate' => ['required', 'date', 'after_or_equal:' . $data->receivedDate],
        ]);


        if ($validator->fails()) {
            return redirect()->route($this->view . 'index')->with('fail', $validator->errors()->all()[0]);
        }

        $totalPrice = 0;
        // foreach ($data->details as $item) {
        //     $itemStock = Item::where('code', $item->itemCode)->first();

        //     $totalPrice += intval($itemStock->price) * $item->receivedQty;
        // }

        // $liveMutation = LiveMutation::where('userBankCode', $request->userBankCode)->first();

        // if ($totalPrice > $liveMutation->balance) {
        //     return redirect()->route($this->view . 'index')->with('fail', 'Balance is not enough');
        // }

        try {
            DB::beginTransaction();

            $this->service->update($request, $id, $this->title, $totalPrice);

            DB::commit();

            return redirect()->route($this->view . 'index')->with('success', $this->title .  ' ' . __('general.data_was_update_succesfully'));
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
                        if ($item->receivedQty) {
                            $totalPrice += intval($item->price) * $item->receivedQty;
                        } else {
                            $totalPrice += intval($item->price) * $item->qty;
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
                    $status = '';
                    $total = $row->details->count();

                    $count = 0;


                    if (isset($row->purchaseStatus->name)) {
                        $status = Auth::user()->languange == 'id' ? $row->purchaseStatus->nama : $row->purchaseStatus->name;

                        if ($row->status == 2) {
                            foreach ($row->details as $item) {
                                if ($item->status == 1) {
                                    $count++;
                                }
                            }

                            if ($count == $total) {
                                $status =  'Stored Full';
                            }

                            if ($count > 0 && $count < $total) {
                                $status =  'Stored Half';
                            }
                        }
                    }
                    return $status;
                })
                ->addColumn('action', function ($row) {
                    $btn = '<td>
                                <a href="' . route($this->view . 'edit', $row->id) . '"
                                class="btn btn-icon btn-sm bg-primary-subtle me-1"
                                data-bs-toggle="tooltip" title="Payment">
                                    <i class="mdi mdi-credit-card fs-14 text-primary"></i>
                                </a>
                            </td>';

                    return $btn;
                })
                ->rawColumns(['purchaseDate', 'supplier.name', 'warehouse.name', 'totalPrice', 'purchaseStatus', 'action'])
                ->toJson();
        }
    }
}
