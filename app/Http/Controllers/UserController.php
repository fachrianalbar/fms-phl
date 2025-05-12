<?php

namespace App\Http\Controllers;

use App\Helpers\GenerateCode;
use App\Models\Bank\TransferFund;
use App\Services\RoleService;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    protected $service;
    protected $roleSvc;
    protected $title;
    protected $view;

    public function __construct(UserService $userSvc, RoleService $roleSvc)
    {
        $this->service = $userSvc;
        $this->title = "User";
        $this->view = "administrator.user.";
        $this->roleSvc = $roleSvc;
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
        $role = $this->roleSvc->findAll();

        return view($this->view . 'create')
            ->with('view', $this->view)
            ->with('role', $role)
            ->with('title', $this->title);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'username' => ['required', 'unique:users,username'],
            'roleCode' => 'required',
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

    public function balance(string $id)
    {
        $data = $this->service->getById($id);

        return view($this->view . 'balance')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('data', $data);
    }



    /**
     * Display the specified resource.
     */
    public function show(string $id) {}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data = $this->service->getById($id);

        if (!$data) {
            return redirect()->route($this->view . 'index')->with('fail', 'Data not found');
        }

        $role = $this->roleSvc->findAll();

        return view($this->view . 'edit')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('role', $role)
            ->with('data', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'username' => 'required',
            'roleCode' => 'required',
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

    public function reset(string $id)
    {
        $data = $this->service->getById($id);

        if (!$data) {
            return redirect()->route($this->view . 'index')->with('fail', 'Data not found');
        }

        $this->service->reset($id);

        return redirect()->route($this->view . 'index')->with('success', 'Reset Password Success');
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->findAll();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<ul class="action">
                                        <li><a href="javascript:resetData(\'' . $row->id . '\')"><i class="icon-key"></i></a></li>
                                        <li class="edit mx-2"> <a href="' . route($this->view . 'edit', $row->id) . '"><i class="icon-pencil-alt"></i></a></li>
                                        <li class="delete"><a href="javascript:deleteData(\'' . $row->id . '\')"><i class="icon-trash"></i></a></li>
                                    </ul>';

                    return $btn;
                })
                ->rawColumns(['action'])
                ->toJson();
        }
    }
}
