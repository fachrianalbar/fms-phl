<?php

namespace App\Http\Controllers\Bank;

use App\Http\Controllers\Controller;
use App\Services\Bank\BankAccountService;
use App\Services\Bank\UserBankService;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class UserBankController extends Controller
{
    protected $service;
    protected $title;
    protected $view;
    protected $bankSvc;

    public function __construct(UserBankService $userBankSvc, BankAccountService $bankSvc)
    {
        $this->service = $userBankSvc;
        $this->bankSvc = $bankSvc;
        $this->title = "User Bank";
        $this->view = "bank.user-bank.";
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
        $bank = $this->bankSvc->findAll();
        return view($this->view . 'create')
            ->with('view', $this->view)
            ->with('bank', $bank)
            ->with('title', $this->title);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'accountNumber' => ['required', Rule::unique('user_bank', 'accountNumber')->whereNull('deleted_at')],
            'accountName' => 'required',
            'type' => 'required',
            'bankCode' => 'required',
            'balance' => 'required',
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

        $bank = $this->bankSvc->findAll();

        return view($this->view . 'edit')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('bank', $bank)
            ->with('data', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $this->service->getById($id);

        $validator = Validator::make($request->all(), [
            'accountNumber' => ['required', Rule::unique('user_bank', 'accountNumber')->ignore($data->id)->whereNull('deleted_at')],
            'accountName' => 'required',
            'type' => 'required',
            'bankCode' => 'required',
            // 'balance' => 'required',
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

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->findAll();
            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('bank.name', function ($row) {
                    $bankName = '';

                    if (isset($row->bank->name)) {
                        $bankName = $row->bank->name;
                    }

                    return $bankName;
                })
                ->editColumn('type', function ($row) {
                    if ($row->type == 1) {
                        return 'Person';
                    }

                    if ($row->type == 2) {
                        return 'Company';
                    }
                })
                ->addColumn('action', function ($row) {
                    $btn = '<ul class="action">
                                        <li class="edit"> <a href="' . route($this->view . 'edit', $row->id) . '"><i class="icon-pencil-alt"></i></a></li>
                                        <li class="delete"><a href="javascript:deleteData(\'' . $row->id . '\')"><i class="icon-trash"></i></a></li>
                                    </ul>';

                    return $btn;
                })
                ->rawColumns(['action', 'bank.name', 'type'])
                ->toJson();
        }
    }
}
