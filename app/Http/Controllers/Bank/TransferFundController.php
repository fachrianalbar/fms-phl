<?php

namespace App\Http\Controllers\Bank;

use App\Enums\CashType;
use App\Enums\TransferType;
use App\Http\Controllers\Controller;
use App\Services\MenuService;
use App\Models\Bank\TransferFund;
use App\Models\LiveMutation;
use App\Models\User;
use App\Services\Bank\TransferFundService;
use App\Services\Bank\UserBankService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class TransferFundController extends Controller
{
    protected $service;
    protected $title;
    protected $view;
    protected $menuSvc;
    protected $userBankSvc;


    public function __construct(
        TransferFundService $transferFundSvc,
        UserBankService $userBankSvc,
    ) {
        $this->service = $transferFundSvc;
        $this->title = "Transfer Fund";
        $this->view = "bank.transfer-fund.";
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
        // Super Admin, Owner, Koordinator
        $role = ['SPRADMIN', 'TRL241113052444', 'TRL250120070026'];
        $user = User::whereIn('roleCode', $role)->with(['role'])->get();
        $userBankSender = $this->userBankSvc->findCompany();
        $userBankReceiver = $this->userBankSvc->findPerson();

        return view($this->view . 'create')
            ->with('view', $this->view)
            ->with('user', $user)
            ->with('userBankSender', $userBankSender)
            ->with('userBankReceiver', $userBankReceiver)
            ->with('type', CashType::cases())
            ->with('transferType', TransferType::cases())
            ->with('title', $this->title);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nominal' => 'required',
            'description' => 'required',
            'receiver' => 'required',
            'sender' => 'required',
            'date' => 'required',
            'time' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->route($this->view . 'index')->with('fail', $validator->errors()->all()[0]);
        }

        $liveMutation = LiveMutation::where('userBankCode', $request->sender)->first();

        if ((int)$request->nominal > $liveMutation->balance) {
            return redirect()->route($this->view . 'index')->with('fail', 'Balance is not enough');
        }

        if ($request->sender == $request->receiver) {
            return redirect()->route($this->view . 'index')->with('fail', 'User bank sender & receiver cannot be same');
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

        // Super Admin, Owner, Koordinator
        $role = ['SPRADMIN', 'TRL241113052444', 'TRL250120070026'];
        $user = User::whereIn('roleCode', $role)->with(['role'])->get();

        return view($this->view . 'edit')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('user', $user)
            ->with('type', CashType::cases())
            ->with('transferType', TransferType::cases())
            ->with('data', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'nominal' => 'required',
            'description' => 'required',
            'receiver' => 'required',
            'date' => 'required',
            'time' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->route($this->view . 'index')->with('fail', $validator->errors()->all()[0]);
        }

        $cash = TransferFund::where('createdBy', Auth::user()->code)->orWhere('receiver', Auth::user()->code)->get();

        $balance = 0;

        if (count($cash) > 0) {

            foreach ($cash as $item) {
                if ($item->cashType == 'Masuk') {
                    $balance += $item->nominal;
                }

                if ($item->cashType == 'Keluar') {
                    if ($item->receiver == Auth::user()->code) {
                        $balance += $item->nominal;
                    } else {
                        $balance -= $item->nominal;
                    }
                }
            }
        }

        if ((int)$request->nominal > $balance) {
            return redirect()->route($this->view . 'index')->with('fail', 'Your balance is not enough');
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
                ->editColumn('date', function ($row) {
                    return Carbon::parse($row->date)->format('d-M-Y H:i');
                })
                ->editColumn('cash.code', function ($row) {
                    $code = '';

                    if (isset($row->cash->code)) {
                        $code = $row->cash->code;
                    }
                    return $code;
                })
                ->editColumn('nominal', function ($row) {
                    return number_format($row->nominal, 0, ',', '.');
                })
                ->addColumn('receiver', function ($row) {
                    $receiver = '';

                    if (isset($row->cash->receiverUserBank->bank->name)) {
                        $receiver .= $row->cash->receiverUserBank->bank->name;
                    }

                    if (isset($row->cash->receiverUserBank)) {
                        $receiver .= " - " . $row->cash->receiverUserBank->accountNumber . " - " . $row->cash->receiverUserBank->accountName;
                    }

                    return $receiver;
                })

                ->addColumn('sender', function ($row) {
                    $sender = '';

                    if (isset($row->cash->senderUserBank->bank->name)) {
                        $sender .= $row->cash->senderUserBank->bank->name;
                    }

                    if (isset($row->cash->senderUserBank)) {
                        $sender .= " - " . $row->cash->senderUserBank->accountNumber . " - " . $row->cash->senderUserBank->accountName;
                    }

                    return $sender;
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
                ->rawColumns(['action', 'cash.code', 'date', 'nominal', 'receiver', 'sender'])
                ->toJson();
        }
    }
}
