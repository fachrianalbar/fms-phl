<?php

namespace App\Http\Controllers;

use App\Exports\ActivityLogExport;
use App\Helpers\FilterHelper;
use App\Models\Role;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\MenuService;
use App\Services\RoleService;
use App\Services\UserService;
use App\Traits\RendersPdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;

class ActivityLogController extends Controller
{
    use RendersPdf;
    protected $service;

    protected $title;

    protected $view;

    protected $menuSvc;

    protected $userSvc;

    protected $roleSvc;

    public function __construct(ActivityLogService $activityLogSvc, UserService $userSvc, RoleService $roleSvc, MenuService $menuSvc)
    {
        $this->service = $activityLogSvc;
        $this->title = 'Activity Log';
        $this->view = 'administrator.activity-log.';
        $this->userSvc = $userSvc;
        $this->roleSvc = $roleSvc;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = $this->userSvc->findAll();
        $role = $this->roleSvc->findAll();
        $action = ['Create', 'Update', 'Delete'];
        $activity = $this->service->getLogName();

        return view($this->view.'index')
            ->with('view', $this->view)
            ->with('user', $user)
            ->with('role', $role)
            ->with('action', $action)
            ->with('activity', $activity)
            ->with('title', $this->title);
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->buildFilteredQuery($request);

            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('user.name', function ($row) {
                    $user = '';

                    if (isset($row->user->name)) {
                        $user = $row->user->name;
                    }

                    return $user;
                })
                ->editColumn('user.role.name', function ($row) {
                    $role = '';

                    if (isset($row->user->role->name)) {
                        $role = $row->user->role->name;
                    }

                    return $role;
                })
                ->editColumn('properties', function ($row) {
                    $encodedProps = e(json_encode($row->properties));

                    return "<a href='#' 
                                class='btn-show-detail' 
                                data-properties=\"{$encodedProps}\" 
                                data-bs-toggle='modal' 
                                data-bs-target='.bd-example-modal-xl'>
                                Detail Data
                            </a>";
                })
                ->editColumn('created_at', function ($row) {
                    return Carbon::parse($row->created_at)->format('d-m-Y H:i');
                })
                ->rawColumns(['user.name', 'created_at', 'properties', 'user.role.name'])
                ->toJson();
        }
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(
            new ActivityLogExport($this->buildFilteredQuery($request)),
            'Activity-Log-'.now()->format('YmdHis').'.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $rows = $this->buildFilteredQuery($request)->get();

        $filterSummary = [];
        if ($request->filled('data')) {
            $filterSummary[] = 'Data: '.$request->data;
        }
        if ($request->filled('activity')) {
            $filterSummary[] = 'Activity: '.$request->activity;
        }
        if ($request->filled('action')) {
            $filterSummary[] = 'Action: '.$request->action;
        }
        if ($request->filled('causer_id')) {
            $userName = User::where('id', $request->causer_id)->value('name');
            $filterSummary[] = 'User: '.($userName ?: $request->causer_id);
        }
        if ($request->filled('roleCode')) {
            $roleName = Role::where('code', $request->roleCode)->value('name');
            $filterSummary[] = 'Role: '.($roleName ?: $request->roleCode);
        }
        if ($request->filled('startDate') || $request->filled('endDate')) {
            $start = $request->filled('startDate') ? Carbon::parse($request->startDate)->format('d-m-Y') : 'Awal';
            $end = $request->filled('endDate') ? Carbon::parse($request->endDate)->format('d-m-Y') : 'Sekarang';
            $filterSummary[] = 'Periode: '.$start.' s/d '.$end;
        }

        return $this->renderPdfResponse(
            $this->view.'report.activity-log-pdf',
            [
                'rows' => $rows,
                'title' => 'Laporan Log Aktivitas',
                'filterSummary' => $filterSummary,
                'printedAt' => now(),
            ],
            'Activity-Log-'.now()->format('YmdHis').'.pdf',
            'L'
        );
    }

    private function buildFilteredQuery(Request $request)
    {
        $data = $this->service->findAll();

        $filters = [
            'causer_id' => $request->causer_id,
            'userRole' => $request->roleCode,
            'description' => $request->action,
            'log_name' => $request->activity,
            'properties' => $request->data,
        ];

        $relations = [
            'userRole' => 'user.roleCode',
        ];

        $dateFilters = [
            'created_at' => [
                'start' => $request->startDate,
                'end' => $request->endDate,
            ],
        ];

        return FilterHelper::applyFilters($data, $filters, $relations, $dateFilters);
    }
}
