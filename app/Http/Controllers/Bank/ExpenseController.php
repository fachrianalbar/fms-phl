<?php

namespace App\Http\Controllers\Bank;

use App\Http\Controllers\Controller;
use App\Models\LiveMutation;
use App\Models\User;
use App\Services\Bank\ExpenseService;
use App\Services\Bank\UserBankService;
use App\Services\Master\EmployeeService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    protected $service;
    protected $title;
    protected $view;
    protected $driverSvc;
    protected $userBankSvc;

    public function __construct(
        ExpenseService $expenseSvc,
        EmployeeService $driverSvc,
        UserBankService $userBankSvc,
    ) {
        $this->service = $expenseSvc;
        $this->title = "Expense";
        $this->view = "bank.expense.";
        $this->driverSvc = $driverSvc;
        $this->userBankSvc = $userBankSvc;
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

        // Super Admin, Owner, Koordinator 
        $role = ['SPRADMIN', 'TRL241113052444', 'TRL250120070026'];
        $user = User::whereIn('roleCode', $role)->with(['role'])->get();
        $userBank = $this->userBankSvc->findCompany();

        return view($this->view . 'create')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('driver', $driver)
            ->with('userBank', $userBank)
            ->with('user', $user);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required',
            'time' => 'required',
            'driverCode' => 'required',
            'nominal' => 'required',
            'description' => 'required',
            'userBankCode' => 'required'
        ]);
        if ($validator->fails()) {
            return redirect()->route($this->view . 'index')->with('fail', $validator->errors()->all()[0]);
        }

        $liveMutation = LiveMutation::where('userBankCode', $request->userBankCode)->first();

        if ((int)$request->nominal > $liveMutation->balance) {
            return redirect()->route($this->view . 'index')->with('fail', 'Balance is not enough');
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

        $data->date_only = Carbon::parse($data->date)->format('Y-m-d');
        $data->time_only = Carbon::parse($data->date)->format('H:i');


        $driver = $this->driverSvc->findAll();

        // Super Admin, Owner, Koordinator 
        $role = ['SPRADMIN', 'TRL241113052444', 'TRL250120070026'];
        $user = User::whereIn('roleCode', $role)->with(['role'])->get();
        $userBank = $this->userBankSvc->findCompany();


        return view($this->view . 'edit')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('data', $data)
            ->with('driver', $driver)
            ->with('userBank', $userBank)
            ->with('user', $user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required',
            'time' => 'required',
            'driverCode' => 'required',
            'nominal' => 'required',
            'description' => 'required',
            'userBankCode' => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->route($this->view . 'index')->with('fail', $validator->errors()->all()[0]);
        }

        try {
            DB::beginTransaction();

            $this->service->update($request, $id, $this->title);

            DB::commit();

            return redirect()->route($this->view . 'index')->with('success', $this->title . 'Data was updated successfully');
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->route($this->view . 'index')->with('fail', 'Line: ' . $th->getLine() . '<br>' . $th->getMessage());
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
            $data = $this->service->findAll();
            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('date', function ($row) {
                    return Carbon::parse($row->date)->format('d-M-Y H:i');
                })
                ->editColumn('nominal', function ($row) {
                    return number_format($row->nominal, 0, ',', '.');
                })
                ->editColumn('expense.code', function ($row) {
                    $code = '';

                    if (isset($row->expense->code)) {
                        $code = $row->expense->code;
                    }

                    return $code;
                })
                ->editColumn('transactionType.name', function ($row) {
                    $type = '';

                    if (isset($row->transactionType->name)) {
                        $type = $row->transactionType->name;
                    }

                    return $type;
                })
                ->addColumn("userBankName", function ($row) {
                    $name = '';

                    if (isset($row->userBank->bank->name)) {
                        $name .= $row->userBank->bank->name;
                    }

                    if (isset($row->userBank)) {
                        $name .= " - " . $row->userBank->accountNumber . " - " . $row->userBank->accountName;
                    }

                    return $name;
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
                ->rawColumns(['action', 'date', 'nominal', 'expense.code', 'transactionType.name', 'userBankName'])
                ->toJson();
        }
    }
}
