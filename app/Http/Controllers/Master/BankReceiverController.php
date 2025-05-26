<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Services\MenuService;
use App\Models\User;
use App\Services\Master\BankReceiverService;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;


class BankReceiverController extends Controller
{
    protected $service;
    protected $title;
    protected $view;
    protected $menuSvc;

    public function __construct(BankReceiverService $bankReceiverSvc, MenuService $menuSvc)
    {
        $this->service = $bankReceiverSvc;
        $this->title = "Bank Receiver";
        $this->view = "master.bank-receiver.";
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

        return view($this->view . 'create')
            ->with('view', $this->view)
            ->with('user', $user)
            ->with('title', $this->title);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bankName' => 'required',
            'accountNumber' => 'required',
            'userCode' => 'required',
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

        // Super Admin, Owner, Koordinator
        $role = ['SPRADMIN', 'TRL241113052444', 'TRL250120070026'];
        $user = User::whereIn('roleCode', $role)->with(['role'])->get();

        return view($this->view . 'edit')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('user', $user)
            ->with('data', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'bankName' => 'required',
            'accountNumber' => 'required',
            'userCode' => 'required',
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

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->findAll();
            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('userCode', function ($row) {
                    $user = '';

                    if ($row->user) {
                        $user = $row->user->name;
                    }

                    return $user;
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
                ->rawColumns(['action', 'userCode'])
                ->toJson();
        }
    }
}
