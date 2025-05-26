<?php

namespace App\Http\Controllers;

use App\Helpers\GenerateCode;
use App\Models\Bank\TransferFund;
use App\Services\MenuService;
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
    protected $menuSvc;

    public function __construct(UserService $userSvc, RoleService $roleSvc, MenuService $menuSvc)
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

            return redirect()->route($this->view . 'index')->with('success', $this->title . ' ' . __('general.data_was_save_successfully'));
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

    public function reset(string $id)
    {
        $data = $this->service->getById($id);

        if (!$data) {
            return redirect()->route($this->view . 'index')->with('fail', 'Data not found');
        }

        $this->service->reset($id);

        return redirect()->route($this->view . 'index')->with('success', 'Reset Password Success');
    }

    public function changeLanguange(Request $request)
    {
        $this->service->changeLanguange($request->languange);

        return redirect()->back();
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->findAll();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = ' <td>
                            <a href="javascript:resetData(\'' . $row->id . '\')"
                            class="btn btn-icon btn-sm bg-success-subtle me-1"
                            data-bs-toggle="tooltip" title="Reset Password">
                                <i class="mdi mdi-key fs-14 text-success"></i>
                            </a>

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
                ->rawColumns(['action'])
                ->toJson();
        }
    }
}
