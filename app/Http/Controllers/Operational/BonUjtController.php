<?php

namespace App\Http\Controllers\Operational;

use App\Helpers\FilterHelper;
use App\Http\Controllers\Controller;
use App\Services\MenuService;
use App\Models\Data\Route;
use App\Models\Data\TonaseBonus;
use App\Services\Master\FleetTypeService;
use App\Services\Operational\BonUjtService;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Mpdf\Mpdf;
use Carbon\Carbon;


class BonUjtController extends Controller
{
    protected $service;
    protected $fleetTypeSvc;
    protected $title;
    protected $view;
    protected $menuSvc;
    protected $totalPrice;



    public function __construct(BonUjtService $bonUjtSvc, FleetTypeService $fleetTypeSvc, MenuService $menuSvc)
    {
        $this->service = $bonUjtSvc;
        $this->fleetTypeSvc = $fleetTypeSvc;
        $this->title = "Bon Ujt";
        $this->view = "operational.bon-ujt.";
        $this->totalPrice = 0;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $fleetType = $this->fleetTypeSvc->findAll();

        return view($this->view . 'index')
            ->with('view', $this->view)
            ->with('fleetType', $fleetType)
            ->with('title', $this->title);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $order = $this->service->getOrder();
        $fleetType = $this->fleetTypeSvc->findAll();
        return view($this->view . 'create')
            ->with('view', $this->view)
            ->with('order', $order)
            ->with('fleetType', $fleetType)
            ->with('title', $this->title);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'submitDate' => 'required',
            'date' => 'required',
            'time' => 'required',
            // 'handover' => 'required',
            'fleetTypeCode' => 'required',
            'bon' => 'required',
            'note' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->route($this->view . 'index')->with('fail', $validator->errors()->all()[0]);
        }

        try {
            DB::beginTransaction();

            $selectedOrders = json_decode($request->input('selectedOrders'), true);


            $this->service->store($request, $this->title, $selectedOrders);

            DB::commit();

            return redirect()->route($this->view . 'index')->with('success', $this->title . ' ' . __('general.data_was_save_successfully'));
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

        $order = $this->service->getOrderDetail($id);
        $fleetType = $this->fleetTypeSvc->findAll();

        return view($this->view . 'edit')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('fleetType', $fleetType)
            ->with('order', $order)
            ->with('data', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'submitDate' => 'required',
            'date' => 'required',
            'time' => 'required',
            // 'handover' => 'required',
            'fleetTypeCode' => 'required',
            'bon' => 'required',
            'note' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('fail', $validator->errors()->all()[0]);
        }
        try {
            DB::beginTransaction();

            $this->service->update($request, $id, $this->title);

            DB::commit();

            return redirect()->back()->with('success', $this->title .  ' ' . __('general.data_was_update_succesfully'));
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->back()->with('fail', 'Line : ' . $th->getLine() . '<br>' . $th->getMessage());
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

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->datatable();

            // Definisikan kolom filter dengan alias
            $filters = [
                'code' => $request->code,
                'bon' => $request->bon,
                'date' => $request->date,
                'fleetType' => $request->fleetType,
                'shipmentNumber' => $request->shipmentNumber,
                'origin' => $request->origin,
                'destination' => $request->destination,

            ];

            // Hubungkan alias ke relasi dan kolom yang sesuai
            $relations = [
                'fleetType' => 'fleetType.name',
            ];

            $data = FilterHelper::applyFilters($data, $filters, $relations);

            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('fleetType.name', function ($row) {
                    $type = '';

                    if (isset($row->fleetType->name)) {
                        $type = $row->fleetType->name;
                    }

                    return $type;
                })
                ->addColumn('action', function ($row) {
                    $btn = '<ul class="action">
                                        <li class="mx-2 delete"> <a target="_blank" href="' . route($this->view . 'pdf-bon-ujt', $row->id) . '"><i class="fa fa-file-pdf-o"></i></a></li>
                                        <li class="edit"> <a href="' . route($this->view . 'edit', $row->id) . '"><i class="icon-pencil-alt"></i></a></li>
                                        <li class="delete"><a href="javascript:deleteData(\'' . $row->id . '\')"><i class="icon-trash"></i></a></li>
                                    </ul>';

                    return $btn;
                })
                ->editColumn('date', function ($row) {
                    return Carbon::parse($row->date)->format('d-M-Y');
                })
                ->rawColumns(['action', 'date', 'fleetType.name'])
                ->toJson();
        }
    }

    public function datatableOrder(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->getOrder();

            // Definisikan kolom filter dengan alias
            $filters = [
                'fleetType_code' => $request->fleetTypeCode,
            ];

            // Hubungkan alias ke relasi dan kolom yang sesuai
            $relations = [
                'fleetType_code' => 'fleet.type.code',
            ];

            $data = FilterHelper::applyFilters($data, $filters, $relations);

            return Datatables::of($data)
                ->addIndexColumn()

                ->editColumn('route.originLocation.name', function ($row) {
                    $origin = '';

                    if (isset($row->route->originLocation->name)) {
                        $origin = $row->route->originLocation->name;
                    }

                    return $origin;
                })

                ->editColumn('orderType.name', function ($row) {
                    $type = '';

                    if (isset($row->orderType->name)) {
                        $type = $row->orderType->name;
                    }

                    return $type;
                })
                ->editColumn('route.destinationLocation.name', function ($row) {
                    $destination = '';

                    if (isset($row->route->destinationLocation->name)) {
                        $destination = $row->route->destinationLocation->name;
                    }

                    return $destination;
                })
                ->addColumn('allowance', function ($row) {
                    $order = $row->route->routeDetail;

                    $allowance = 0;
                    foreach ($order as $item) {
                        if ($item->costComponent->type == 'Allowance') {
                            if ($item->amount != 0) {
                                $allowance += $item->amount;
                            }

                            if ($item->percentage) {
                                $route = Route::where('code', $item->routeCode)->first();

                                $allowance = $route->price * ($item->percentage / 100);
                            }
                        }
                    }
                    return  'Rp ' .  number_format($allowance, 0, ',', '.');
                })
                ->addColumn('cost', function ($row) {
                    $allowance = 0;

                    if (isset($row->route->routeDetail)) {
                        $data = $row->route->routeDetail;

                        foreach ($data as $item) {
                            if ($item->costComponent->type == 'Allowance') {
                                if ($item->amount != 0) {
                                    $allowance = $item->amount;
                                }

                                if ($item->percentage) {
                                    $route = Route::where('code', $item->routeCode)->first();

                                    $allowance = $route->price * ($item->percentage / 100);
                                }
                            }
                        }
                        $this->totalPrice = $allowance;
                    }

                    return '' . number_format($allowance, 0, ',', '.');
                })
                ->addColumn('tonase', function ($row) {
                    if (isset($row->route->routeTypeCode)) {
                        if ($row->route->routeTypeCode == 'TONASE') {
                            return '' . number_format($row->route->price, 0, ',', '.');
                        }
                    }
                    return '' . 0;
                })

                ->addColumn('bonus', function ($row) {
                    $bonus = TonaseBonus::where('min', '<=', $row->qty)->where('max', '>=', $row->qty)->first();

                    if ($bonus) {
                        $this->totalPrice += $bonus->value;
                        return '' . number_format($bonus->value, 0, ',', '.');
                    }
                    return '' . 0;
                })
                ->addColumn('addCost', function ($row) {
                    $cost = 0;
                    if (isset($row->cost)) {
                        foreach ($row->cost as $item) {
                            $cost += $item->nominal;
                        }
                    }
                    $this->totalPrice += $cost;
                    return '' . number_format($cost, 0, ',', '.');
                })
                ->addColumn('totalPrice', function () {
                    return '' . number_format($this->totalPrice, 0, ',', '.');
                })
                ->addColumn('action', function ($row) {
                    $btn = '<input class="order-checkbox" type="checkbox" name="order[]" data-id="' . $row->code . '" value="' . $row->code . '">';

                    return $btn;
                })
                ->editColumn('orderDate', function ($row) {
                    return Carbon::parse($row->orderDate)->format('d-M-Y');
                })
                ->rawColumns(['action', 'allowance', 'orderDate', 'route.originLocation.name', 'route.destinationLocation.name', 'orderType.name', 'cost', 'bonus', 'tonase', 'addCost', 'totalPrice'])
                ->toJson();
        }
    }

    public function storeBonUjtDetail(Request $request, $id)
    {

        $selectedOrders = json_decode($request->input('selectedOrders'), true);


        $validator = Validator::make($request->all(), [
            'order' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('fail', $validator->errors()->all()[0]);
        }

        try {
            DB::beginTransaction();

            $this->service->storeBonUjtDetail($request, $id, $selectedOrders);

            DB::commit();

            return redirect()->back()->with('success', $this->title . ' ' . __('general.data_was_save_successfully'));
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->back()->with('fail', 'Line : ' . $th->getLine() . '<br>' . $th->getMessage());
        }
    }

    public function destroyBonUjtDetail($id)
    {
        $this->service->destroyBonUjtDetail($id, $this->title);

        return redirect()->back()->with('success', 'Delete Order Data Success');
    }

    public function pdfBonUjt($id)
    {

        $data = $this->service->getById($id);

        if (!$data) {
            return redirect()->route($this->view . 'index')->with('fail', 'Data not found');
        }

        $order = $this->service->getOrderDetail($id);

        $int = 0;
        $ext = 0;
        $dll = 0;
        $note = 0;

        foreach ($order as $item) {
            $allowance = $item->route->routeDetail;
            $price = 0;
            foreach ($allowance as $allow) {
                if ($allow->costComponent->type == 'Allowance') {
                    $price += $allow->amount;
                }

                if ($allow->percentage) {
                    $route = Route::where('code', $allow->routeCode)->first();

                    $price = $route->price * ($allow->percentage / 100);
                }
            }

            $bonus = TonaseBonus::where('min', '<=', $item->qty)
                ->where('max', '>=', $item->qty)
                ->first();

            if ($bonus) {
                $price += $bonus->value;
            }

            $cost = 0;
            if (isset($item->cost)) {
                foreach ($item->cost as $itCost) {
                    $cost += $itCost->nominal;
                }
            }
            $price += $cost;

            if ($item->orderTypeCode == 'Int') {
                $int += $price;
            }

            if ($item->orderTypeCode == 'Ext') {
                $ext += $price;
            }

            if ($item->orderTypeCode == 'Dll') {
                $dll += $price;
            }
        }

        if ($data->note) {
            $note = $data->note;
        }

        $total = $int + $ext + $dll + $note;

        $order = $this->service->getOrderDetail($id);
        $mpdf = new Mpdf(
            [
                'orientation' => 'P',
                'format' => [215, 330],
            ]
        );

        $mpdf->WriteHTML(
            view($this->view . 'pdf.bon-ujt')
                ->with('data', $data)
                ->with('int', $int)
                ->with('ext', $ext)
                ->with('dll', $dll)
                ->with('note', $note)
                ->with('total', $total)
        );

        return $mpdf->Output('Laporan Bon Ujt.pdf', 'I');
    }
}
