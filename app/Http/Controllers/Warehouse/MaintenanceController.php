<?php

namespace App\Http\Controllers\Warehouse;

use App\Helpers\FilterHelper;
use App\Helpers\GenerateCode;
use App\Http\Controllers\Controller;
use App\Services\MenuService;
use App\Models\Inventory\Stock;
use App\Models\Purchasing\PurchaseDetail;
use App\Models\StockTransaction;
use App\Models\Warehouse\Maintenance;
use App\Models\Warehouse\MaintenanceDetail;
use App\Models\Warehouse\MaintenanceFifo;
use App\Services\Inventory\StockService;
use App\Services\Master\FleetService;
use App\Services\Warehouse\MaintenanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;
use Illuminate\Support\Facades\Auth;


class MaintenanceController extends Controller
{
    protected $service;
    protected $fleetSvc;
    protected $stockSvc;
    protected $title;
    protected $view;
    protected $menuSvc;

    public function __construct(MaintenanceService $maintenanceService, FleetService $fleetSvc, StockService $stockSvc, MenuService $menuSvc)
    {
        $this->service = $maintenanceService;
        $this->fleetSvc = $fleetSvc;
        $this->stockSvc = $stockSvc;
        $this->title = "Maintenance";
        $this->menuSvc = $menuSvc->getByName("Maintenance");
        $this->title = Auth::user()->languange == 'en' ? $this->menuSvc->name : $this->menuSvc->nama;
        $this->view = "warehouse.maintenance.";
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $fleet = $this->fleetSvc->findAll();
        $stock = $this->stockSvc->findAll();

        return view($this->view . 'index')
            ->with('view', $this->view)
            ->with('fleet', $fleet)
            ->with('stock', $stock)
            ->with('title', $this->title);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $fleet = $this->fleetSvc->findAll();
        $stock = $this->stockSvc->findAll();

        return view($this->view . 'create')
            ->with('view', $this->view)
            ->with('fleet', $fleet)
            ->with('stock', $stock)
            ->with('title', $this->title);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'code' => 'required',
            'fleetCode' => 'required',
            'date' => 'required',
            'time' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->route($this->view . 'index')->with('fail', $validator->errors()->all()[0]);
        }
        try {
            DB::beginTransaction();

            $this->service->store($request, $this->title);

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

        $stock = $this->stockSvc->findAll();
        $fleet = $this->fleetSvc->findAll();

        return view($this->view . 'edit')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('fleet', $fleet)
            ->with('stock', $stock)
            ->with('data', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            // 'code' => 'required',
            'fleetCode' => 'required',
            'date' => 'required',
            'time' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->route($this->view . 'index')->with('fail', $validator->errors()->all()[0]);
        }

        try {
            DB::beginTransaction();

            $this->service->update($request, $id, $this->title);

            DB::commit();

            return redirect()->route($this->view . 'index')->with('success', $this->title .  ' ' . __('general.data_was_update_succesfully'));
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

    public function deleteMaintenanceDetail($id)
    {
        $md = MaintenanceDetail::where('id', $id)->firstOrFail();

        // Rollback qtyUsed pada PurchaseDetail berdasarkan FIFO
        $fifos = MaintenanceFifo::where('maintenanceDetailCode', $md->code)->get();
        foreach ($fifos as $fifo) {
            PurchaseDetail::where('code', $fifo->purchaseDetailCode)
                ->decrement('qtyUsed', $fifo->qty);
        }

        // Hapus data MaintenanceFifo terkait
        MaintenanceFifo::where('maintenanceDetailCode', $md->code)->delete();

        // Update stok keluar
        Stock::where('itemCode', $md->itemCode)
            ->decrement('stockOut', $md->qty);

        // Hapus transaksi stok
        StockTransaction::where('transactionCode', $md->code)->delete();

        // Hapus detail maintenance
        $maintenanceId = $md->maintenance->id;
        $md->delete();

        return redirect()->route($this->view . 'edit', $maintenanceId)
            ->with('success', 'Delete Data Success');
    }


    public function datatable(Request $request)
    {
        // dd($request->all());
        if ($request->ajax()) {
            $data = $this->service->datatable();

            // Definisikan kolom filter dengan alias
            $filters = [
                'fleet_plateNumber' => $request->plateNumber,
                'item_code' => $request->itemCode
            ];

            // Hubungkan alias ke relasi dan kolom yang sesuai
            $relations = [
                'fleet_plateNumber' => 'fleet.plateNumber',
                'item_code' => 'details.itemCode'
            ];

            $dateFilters = [
                'date' => [
                    'start' => $request->startDate,
                    'end' => $request->endDate,
                ],
            ];

            $data = FilterHelper::applyFilters($data, $filters, $relations, $dateFilters);

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('maintenanceDate', function ($row) {
                    $date = Carbon::parse($row->date)->format('d-M-Y');
                    $time = Carbon::parse($row->time)->format('H:i');
                    return $date . ' ' . $time;
                })
                ->editColumn('fleet.plateNumber', function ($row) {
                    $fleet = '';

                    if (isset($row->fleet->plateNumber)) {
                        $fleet = $row->fleet->plateNumber;
                    }

                    return $fleet;
                })

                ->addColumn('items', function ($row) {
                    $items = '';
                    foreach ($row->details as $item) {
                        $items .= $item->itemCode . ': ' . $item->item->name . ' Qty : ' .  $item->qty . '<br>';
                    }

                    return $items;
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

                    return $btn;
                })
                ->rawColumns(['maintenanceDate', 'fleet.plateNumber', 'items', 'action'])
                ->toJson();
        }
    }

    public function pdfMaintenance(Request $request)
    {
        // Definisikan kolom filter dengan alias
        $filters = [
            'fleet_plateNumber' => $request->plateNumber,
        ];

        // Hubungkan alias ke relasi dan kolom yang sesuai
        $relations = [
            'fleet_plateNumber' => 'fleet.plateNumber',
        ];

        $dateFilters = [
            'date' => [
                'start' => $request->startDate,
                'end' => $request->endDate,
            ],
        ];

        $query = Maintenance::with([
            'fleet',
            'details',
            'details.item',
            'details.item.supplier',
            'details.item.category'

        ])->orderBy('created_at', 'desc');

        $data = FilterHelper::applyFilters($query, $filters, $relations, $dateFilters);

        $mpdf = new Mpdf(
            [
                'orientation' => 'P',
                'format' => [215, 330],
            ]
        );

        $startDate = Carbon::parse($request->startDate)->format('d-m-Y');
        $endDate = Carbon::parse($request->endDate)->format('d-m-Y');

        $mpdf->WriteHTML(
            view($this->view . 'report.maintenance-pdf')
                ->with('data', $data->get())
                ->with('plateNumber', $request->plateNumber)
                ->with('startDate', $startDate)
                ->with('endDate', $endDate)
        );

        return $mpdf->Output('Laporan Maintenance.pdf', 'I');
    }

    public function generateCode(Request $request)
    {
        $date = $request->date;

        $code = GenerateCode::generateCodeAscDate(
            'MNT',
            Maintenance::class,
            'date',
            $date,
        );

        return response()->json(['code' => $code]);
    }
}
