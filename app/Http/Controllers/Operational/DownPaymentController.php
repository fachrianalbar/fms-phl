<?php

namespace App\Http\Controllers\Operational;

use App\Enums\DownPaymentType;
use App\Http\Controllers\Controller;
use App\Services\MenuService;
use App\Services\Master\EmployeeService;
use App\Services\Operational\DownPaymentDetailService;
use App\Services\UserService;
use App\Services\Operational\DownPaymentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;
use Illuminate\Validation\Rules\Enum;


class DownPaymentController extends Controller
{
    protected $service;
    protected $title;
    protected $view;
    protected $menuSvc;
    protected $userSvc;
    protected $isPaid;
    protected $driverSvc;
    protected $downPaymentDetailSvc;

    public function __construct(DownPaymentService $downPaymentSvc, UserService $userSvc, EmployeeService $driverSvc, DownPaymentDetailService $downPaymentDetailSvc, MenuService $menuSvc)
    {
        $this->service = $downPaymentSvc;
        $this->title = "Down Payment";
        $this->view = "operational.down-payment.";
        $this->userSvc = $userSvc;
        $this->driverSvc = $driverSvc;
        $this->downPaymentDetailSvc = $downPaymentDetailSvc;
        $this->isPaid = 0;
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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $driver = $this->driverSvc->findAll();
        return view($this->view . 'create')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('driver', $driver);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'price' => 'required|numeric',
            'date' => 'required|date',
            'time' => 'required',
            'driverCode' => 'required'
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

        $data = $this->service->getById($id);


        if (!$data) {
            return redirect()->route($this->view . 'index')->with('fail', 'Data not found');
        }

        $detail = $data->details;

        $paid = 0;
        foreach ($detail as $item) {
            $paid += $item->price;
        }

        if ($paid == $data->price) {
            $this->isPaid = true;
        }

        $driver = $this->driverSvc->findAll();

        return view($this->view . 'show')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('driver', $driver)
            ->with('isPaid', $this->isPaid)
            ->with('data', $data);
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

        $driver = $this->driverSvc->findAll();

        return view($this->view . 'edit')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('driver', $driver)
            ->with('data', $data);
    }

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'price' => 'required|numeric',
            'date' => 'required|date',
            'time' => 'required',
            'driverCode' => 'required'
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

    public function data(string $id)
    {
        $data = $this->downPaymentDetailSvc->getById($id);


        return response()->json($data);
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->findAll();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('loanDate', function ($row) {
                    $date = Carbon::parse($row->date)->format('d-M-Y');
                    $time = Carbon::parse($row->time)->format('H:i');
                    return $date . ' ' . $time;
                })
                ->addColumn('price', function ($row) {
                    return 'Rp ' . number_format($row->price, 0, ',', '.');
                })
                ->addColumn('paid', function ($row) {
                    $this->isPaid = false;
                    $data = $row->details;

                    $paid = 0;
                    foreach ($data as $item) {
                        $paid += $item->price;
                    }

                    if ($paid == $row->price) {
                        $this->isPaid = true;
                    }
                    return 'Rp ' . number_format($paid, 0, ',', '.');
                })
                ->addColumn('isPaid', function ($row) {
                    if ($this->isPaid) {
                        return 'Yes';
                    }
                    return 'No';
                })

                ->addColumn('action', function ($row) {
                    $btn = '<ul class="action">
                                        <li class="edit"> <a href="' . route($this->view . 'edit', $row->id) . '"><i class="icon-pencil-alt"></i></a></li>
                                        <li class="delete"><a href="javascript:deleteData(\'' . $row->id . '\')"><i class="icon-trash"></i></a></li>
                                        <li> <a href="' . route($this->view . 'show', $row->id) . '"><i class="icon-receipt"></i></a></li>
                                    </ul>';

                    return $btn;
                })
                ->rawColumns(['action', 'loanDate', 'paid', 'isPaid'])
                ->toJson();
        }
    }

    public function datatableDetail(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->detail($request->code);
            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('date', function ($row) {
                    $date = Carbon::parse($row->date)->format('d-M-Y');
                    $time = Carbon::parse($row->time)->format('H:i');
                    return $date . ' ' . $time;
                })
                ->editColumn('price', function ($row) {
                    return 'Rp ' . number_format($row->price, 0, ',', '.');
                })
                ->addColumn('action', function ($row) {
                    $btn = '<ul class="action">
                                        <li class="edit"> <a href="javascript:editData(\'' . $row->id . '\')"><i class="icon-pencil-alt"></i></a></li>
                                        <li class="delete"><a href="javascript:deleteData(\'' . $row->id . '\')"><i class="icon-trash"></i></a></li>
                            </ul>';

                    return $btn;
                })
                ->rawColumns(['action', 'date', 'nominal'])
                ->toJson();
        }
    }

    public function pdfDownPayment($id)
    {
        $data = $this->service->getById($id);

        if (!$data) {
            return redirect()->route($this->view . 'index')->with('fail', 'Data not found');
        }

        $mpdf = new Mpdf(
            [
                'orientation' => 'P',
                'format' => [215, 330],
            ]
        );

        $mpdf->WriteHTML(
            view($this->view . 'pdf.down-payment')
                ->with('data', $data)

        );

        return $mpdf->Output('Laporan Down Payment.pdf', 'I');
    }
}
