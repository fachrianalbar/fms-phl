<?php

namespace App\Http\Controllers\Operational;

use App\Http\Controllers\Controller;
use App\Models\Operational\Order;
use App\Services\Operational\ReturnDoService;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReturnDoController extends Controller
{
    protected $service;
    protected $title;
    protected $view;

    public function __construct(ReturnDoService $returnDoService)
    {
        $this->service = $returnDoService;
        $this->title = "Return Do";
        $this->view = "operational.return-do.";
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
                'returnDate' => null
            ]);

            DB::commit();

            return redirect()->back()->with('success',  'Data was save succesfully');
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
                    $returnDate = '';

                    if (isset($row->returnDate)) {
                        $returnDate = Carbon::parse($row->returnDate)->format('d-m-Y H:i ');
                    }

                    return $returnDate;
                })

                ->addColumn('action', function ($row) {
                    $btn = '';

                    if ($row->status == 4) {
                        $btn = '<input class="order-checkbox" type="checkbox" name="order[]" data-id="' . $row->code . '" value="' . $row->code . '">';
                    }

                    return $btn;
                })
                ->rawColumns(['action', 'route.originLocation.name', 'customer.name', 'returnDate', 'route.destinationLocation.name', 'orderDate',  'fleet.plateNumber'])
                ->toJson();
        }
    }
}
