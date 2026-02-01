<?php

namespace App\Http\Controllers\Operational;

use App\Http\Controllers\Controller;
use App\Models\Operational\Order;
use App\Services\MenuService;
use App\Services\Operational\ReturnDoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class ReturnDoController extends Controller
{
    protected $service;

    protected $title;

    protected $view;

    protected $menuSvc;

    public function __construct(ReturnDoService $returnDoService, MenuService $menuSvc)
    {
        $this->service = $returnDoService;
        $this->title = 'Return Do';
        $this->menuSvc = $menuSvc->getByName('Return Do');
        $this->title = Auth::user()->languange == 'en' ? $this->menuSvc->name : $this->menuSvc->nama;
        $this->view = 'operational.return-do.';
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view($this->view . 'index')
            ->with('view', $this->view)
            ->with('title', $this->title);
    }

    public function cancelDo(Request $request)
    {
        $selectedOrders = json_decode($request->input('selectedOrders'), true);

        try {
            DB::beginTransaction();

            Order::whereIn('code', $selectedOrders)->update([
                'status' => 3,
                'returnDate' => null,
                'returnDescription' => null,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Data was save succesfully');
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->back()->with('fail', 'Line : ' . $th->getLine() . '<br>' . $th->getMessage());
        }
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->datatable();

            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('orderDate', function ($row) {
                    return Carbon::parse($row->orderDate)->format('d-m-Y');
                })
                ->editColumn('fleet.plateNumber', function ($row) {
                    $fleet = '';

                    if (isset($row->fleet->plateNumber)) {
                        $fleet = $row->fleet->plateNumber;
                    }

                    return $fleet;
                })
                ->editColumn('customer.name', function ($row) {
                    $customer = '';

                    if (isset($row->customer->name)) {
                        $customer = $row->customer->name;
                    }

                    return $customer;
                })

                ->editColumn('route.originLocation.name', function ($row) {
                    $origin = '';

                    if (isset($row->route->originLocation->name)) {
                        $origin = $row->route->originLocation->name;
                    }

                    return $origin;
                })
                ->editColumn('route.destinationLocation.name', function ($row) {
                    $destination = '';

                    if (isset($row->route->destinationLocation->name)) {
                        $destination = $row->route->destinationLocation->name;
                    }

                    return $destination;
                })

                ->editColumn('returnDate', function ($row) {
                    // $returnDate = '';

                    // if (isset($row->returnDate)) {
                    //     $returnDate = Carbon::parse($row->returnDate)->format('d-m-Y H:i ');
                    // }

                    return $row->returnDate;
                })

                ->addColumn('action', function ($row) {
                    $btn = '';

                    if ($row->status == 4) {
                        $btn = '<input class="order-checkbox" type="checkbox" name="order[]" data-id="' . $row->code . '" value="' . $row->code . '">';
                    }

                    return $btn;
                })
                ->addColumn('detail', function ($row) {
                    $onChargeCosts = $row->onChargeCost;
                    $buttons = '';

                    // Filter only costs that actually have a costComponent relation
                    $validCosts = collect([]);
                    if ($onChargeCosts && $onChargeCosts->count() > 0) {
                        $validCosts = $onChargeCosts->filter(function ($cost) {
                            return isset($cost->costComponent) && ! is_null($cost->costComponent->name);
                        });
                    }

                    if ($validCosts->count() > 0) {
                        $costsData = $validCosts->map(function ($cost) {
                            return [
                                'component' => $cost->costComponent->name ?? '-',
                                'nominal' => 'Rp ' . number_format($cost->nominal, 0, ',', '.'),
                            ];
                        })->toArray();

                        $costsJson = htmlspecialchars(json_encode($costsData), ENT_QUOTES, 'UTF-8');
                        $buttons .= '<button type="button" class="btn btn-sm btn-outline-success btn-detail-cost me-2" data-costs="' . $costsJson . '" data-shipment="' . $row->shipmentNumber . '" title="Lihat detail biaya">
                            <i class="mdi mdi-cash-multiple me-1"></i> Biaya
                        </button>';
                    }

                    // Cek apakah ada file yang diupload
                    $filesCount = \App\Models\OrderDetail::where('order_id', $row->id)
                        ->where('type', 'surat_jalan')
                        ->count();

                    if ($filesCount > 0) {
                        $buttons .= '<button type="button" class="btn btn-sm btn-outline-info btn-view-files" data-order-id="' . $row->id . '" data-order-code="' . $row->code . '" title="Lihat File Surat Jalan">
                            <i class="mdi mdi-file-image-multiple me-1"></i> File (' . $filesCount . ')
                        </button>';
                    }

                    return $buttons ?: '-';
                })
                ->rawColumns(['action', 'detail', 'route.originLocation.name', 'customer.name', 'returnDate', 'route.destinationLocation.name', 'orderDate',  'fleet.plateNumber'])
                ->toJson();
        }
    }

    /**
     * Get uploaded files for specific order
     */
    public function getOrderFiles($orderId)
    {
        $files = \App\Models\OrderDetail::where('order_id', $orderId)
            ->where('type', 'surat_jalan')
            ->orderBy('created_at', 'desc')
            ->get(['id', 'file', 'created_at']);

        return response()->json([
            'success' => true,
            'files' => $files->map(function ($file) {
                return [
                    'id' => $file->id,
                    'url' => asset('storage/' . $file->file),
                    'name' => basename($file->file),
                    'uploaded_at' => $file->created_at->format('d-m-Y H:i'),
                ];
            })
        ]);
    }
}
