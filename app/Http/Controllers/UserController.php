<?php

namespace App\Http\Controllers;

use App\Exports\UserExport;
use App\Models\Role;
use App\Models\User;
use App\Services\MenuService;
use App\Services\RoleService;
use App\Services\UserService;
use App\Traits\RendersPdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;

class UserController extends Controller
{
    use RendersPdf;
    protected $service;

    protected $roleSvc;

    protected $title;

    protected $view;

    protected $menuSvc;

    public function __construct(UserService $userSvc, RoleService $roleSvc, MenuService $menuSvc)
    {
        $this->service = $userSvc;
        $this->title = 'User';
        $this->view = 'administrator.user.';
        $this->roleSvc = $roleSvc;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = $this->roleSvc->findAll();

        return view($this->view.'index')
            ->with('view', $this->view)
            ->with('roles', $roles)
            ->with('title', $this->title);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $role = $this->roleSvc->findAll();

        return view($this->view.'create')
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
            return redirect()->route($this->view.'index')->with('fail', $validator->errors()->all()[0]);
        }
        try {
            DB::beginTransaction();

            $this->service->store($request, $this->title);
            DB::commit();

            return redirect()->route($this->view.'index')->with('success', $this->title.' '.__('general.data_was_save_successfully'));
        } catch (\Throwable $th) {
            DB::rollback();
            report($th);

            return redirect()->route($this->view.'index')->with('fail', $this->safeErrorMessage($th));
        }
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

        if (! $data) {
            return redirect()->route($this->view.'index')->with('fail', 'Data not found');
        }

        $role = $this->roleSvc->findAll();

        return view($this->view.'edit')
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
            return redirect()->route($this->view.'index')->with('fail', $validator->errors()->all()[0]);
        }
        try {
            DB::beginTransaction();

            $this->service->update($request, $id, $this->title);

            DB::commit();

            return redirect()->route($this->view.'index')->with('success', $this->title.' '.__('general.data_was_update_succesfully'));
        } catch (\Throwable $th) {
            DB::rollback();
            report($th);

            return redirect()->route($this->view.'index')->with('fail', $this->safeErrorMessage($th));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->service->destroy($id, $this->title);

        return redirect()->route($this->view.'index')->with('success', 'Delete Data Success');
    }

    public function reset(string $id)
    {
        $data = $this->service->getById($id);

        if (! $data) {
            return redirect()->route($this->view.'index')->with('fail', 'Data not found');
        }

        $this->service->reset($id);

        return redirect()->route($this->view.'index')->with('success', 'Reset Password Success');
    }

    public function changeLanguange(Request $request)
    {
        $this->service->changeLanguange($request->languange);

        return redirect()->back();
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->filteredQuery($request);

            return Datatables::of($data)
                ->addIndexColumn()
                ->filterColumn('DT_RowIndex', function ($query, $keyword) {
                    return $query;
                })
                ->addColumn('action', function ($row) {
                    $btn = ' <td>
                            <a href="javascript:resetData(\''.$row->id.'\')"
                            class="btn btn-icon btn-sm bg-success-subtle me-1"
                            data-bs-toggle="tooltip" title="Reset Password">
                                <i class="mdi mdi-key fs-14 text-success"></i>
                            </a>

                            <a href="'.route($this->view.'edit', $row->id).'"
                            class="btn btn-icon btn-sm bg-primary-subtle me-1"
                            data-bs-toggle="tooltip" title="Edit">
                                <i class="mdi mdi-pencil-outline fs-14 text-primary"></i>
                            </a>

                            <a href="javascript:deleteData(\''.$row->id.'\')"
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

    public function exportExcel(Request $request)
    {
        return Excel::download(
            new UserExport($this->filteredQuery($request)),
            'Data-Pengguna-'.now()->format('YmdHis').'.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $rows = $this->filteredQuery($request)->get();

        $filterSummary = [];
        if ($request->filled('keyword')) {
            $filterSummary[] = 'Kata Kunci: '.$request->keyword;
        }
        if ($request->filled('roleCode')) {
            $roleName = Role::where('code', $request->roleCode)->value('name');
            $filterSummary[] = 'Role: '.($roleName ?: $request->roleCode);
        }
        if ($request->filled('startDate') || $request->filled('endDate')) {
            $start = $request->filled('startDate') ? Carbon::parse($request->startDate)->format('d-m-Y') : 'Awal';
            $end = $request->filled('endDate') ? Carbon::parse($request->endDate)->format('d-m-Y') : 'Sekarang';
            $filterSummary[] = 'Periode Dibuat: '.$start.' s/d '.$end;
        }

        return $this->renderPdfResponse(
            $this->view.'report.user-pdf',
            [
                'rows' => $rows,
                'title' => 'Laporan Data Pengguna',
                'filterSummary' => $filterSummary,
                'printedAt' => now(),
            ],
            'Data-Pengguna-'.now()->format('YmdHis').'.pdf',
            'L'
        );
    }

    private function filteredQuery(Request $request)
    {
        $query = User::query()->with(['role'])->orderBy('name');

        if ($request->filled('keyword')) {
            $keyword = strtolower(trim((string) $request->keyword));
            $query->where(function ($q) use ($keyword) {
                $q->whereRaw('LOWER(COALESCE(code, \'\')) LIKE ?', ['%'.$keyword.'%'])
                    ->orWhereRaw('LOWER(name) LIKE ?', ['%'.$keyword.'%'])
                    ->orWhereRaw('LOWER(COALESCE(username, \'\')) LIKE ?', ['%'.$keyword.'%']);
            });
        }

        if ($request->filled('roleCode')) {
            $query->where('roleCode', $request->roleCode);
        }

        if ($request->filled('startDate')) {
            $query->whereDate('created_at', '>=', $request->startDate);
        }

        if ($request->filled('endDate')) {
            $query->whereDate('created_at', '<=', $request->endDate);
        }

        return $query;
    }

    private function safeErrorMessage(\Throwable $th): string
    {
        return app()->hasDebugModeEnabled()
            ? 'Line : '.$th->getLine().' - '.$th->getMessage()
            : 'Terjadi kesalahan pada sistem. Silakan coba lagi.';
    }
}
