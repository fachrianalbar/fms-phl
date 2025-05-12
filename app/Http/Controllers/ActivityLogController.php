<?php

namespace App\Http\Controllers;

use App\Helpers\FilterHelper;
use App\Services\ActivityLogService;
use App\Services\RoleService;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;


class ActivityLogController extends Controller
{
    protected $service;
    protected $title;
    protected $view;
    protected $userSvc;
    protected $roleSvc;

    public function __construct(ActivityLogService $activityLogSvc, UserService $userSvc, RoleService $roleSvc)
    {
        $this->service = $activityLogSvc;
        $this->title = "Activity Log";
        $this->view = "administrator.activity-log.";
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

        return view($this->view . 'index')
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
            $data = $this->service->findAll();

            $filters = [
                'causer_id' => $request->causer_id,
                'userRole' => $request->roleCode,
                'description' => $request->action,
                'log_name' => $request->activity,
                'properties' => $request->data
            ];

            // Hubungkan alias ke relasi dan kolom yang sesuai
            $relations = [
                'userRole' => 'user.roleCode',
            ];

            $dateFilters = [
                'created_at' => [
                    'start' => $request->startDate,
                    'end' => $request->endDate,
                ],
            ];

            $data = FilterHelper::applyFilters($data, $filters, $relations, $dateFilters);

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
}
