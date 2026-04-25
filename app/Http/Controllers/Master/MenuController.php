<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Services\Master\MenuService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class MenuController extends Controller
{
    protected $service;

    protected $title;

    protected $view;

    public function __construct(MenuService $menuSvc)
    {
        $this->service = $menuSvc;
        $this->title = 'Menu';
        $this->view = 'master.menu.';
    }

    /**
     * Display a listing of the resource (Main Menus only).
     */
    public function index()
    {
        return view($this->view.'index')
            ->with('view', $this->view)
            ->with('title', $this->title);
    }

    /**
     * Show the form for creating a new main menu.
     */
    public function create()
    {
        return view($this->view.'create')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('isSubMenu', false)
            ->with('parentCode', '0')
            ->with('parentName', null);
    }

    /**
     * Show the form for creating a new sub menu.
     */
    public function createSubMenu($parentCode)
    {
        $parentMenu = $this->service->getByCode($parentCode);

        if (! $parentMenu) {
            return redirect()->route($this->view.'index')->with('fail', 'Parent menu not found');
        }

        return view($this->view.'create')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('isSubMenu', true)
            ->with('parentCode', $parentCode)
            ->with('parentName', $parentMenu->name);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'nama' => 'required|string|max:255',
            'url' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'sort' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('fail', $validator->errors()->all()[0])->withInput();
        }

        try {
            DB::beginTransaction();

            $menu = $this->service->store($request, $this->title);

            // If this is a sub menu, update parent URL to '#'
            if ($request->parentCode && $request->parentCode != '0') {
                $this->service->updateParentUrl($request->parentCode);
            }

            DB::commit();

            // Redirect based on whether it's a sub menu or main menu
            if ($request->parentCode && $request->parentCode != '0') {
                return redirect()->route('master.menu.sub-menu', $request->parentCode)
                    ->with('success', 'Sub Menu '.__('general.data_was_save_successfully'));
            }

            return redirect()->route($this->view.'index')
                ->with('success', $this->title.' '.__('general.data_was_save_successfully'));
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->back()->with('fail', 'Line : '.$th->getLine().'<br>'.$th->getMessage())->withInput();
        }
    }

    /**
     * Display sub menus of a main menu.
     */
    public function subMenu($parentCode)
    {
        $parentMenu = $this->service->getByCode($parentCode);

        if (! $parentMenu) {
            return redirect()->route($this->view.'index')->with('fail', 'Parent menu not found');
        }

        return view($this->view.'sub-menu')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('parentMenu', $parentMenu)
            ->with('parentCode', $parentCode);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data = $this->service->getById($id);

        if (! $data) {
            return redirect()->route($this->view.'index')->with('fail', 'Data not found');
        }

        $isSubMenu = $data->parentCode != '0' && $data->parentCode !== null;
        $parentName = null;

        if ($isSubMenu) {
            $parent = $this->service->getByCode($data->parentCode);
            $parentName = $parent ? $parent->name : null;
        }

        return view($this->view.'edit')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('data', $data)
            ->with('isSubMenu', $isSubMenu)
            ->with('parentName', $parentName);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'nama' => 'required|string|max:255',
            'url' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'sort' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('fail', $validator->errors()->all()[0])->withInput();
        }

        try {
            DB::beginTransaction();

            $menu = $this->service->getById($id);
            $this->service->update($request, $id, $this->title);

            DB::commit();

            // Redirect based on whether it's a sub menu or main menu
            if ($menu->parentCode && $menu->parentCode != '0') {
                return redirect()->route('master.menu.sub-menu', $menu->parentCode)
                    ->with('success', 'Sub Menu '.__('general.data_was_update_succesfully'));
            }

            return redirect()->route($this->view.'index')
                ->with('success', $this->title.' '.__('general.data_was_update_succesfully'));
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->back()->with('fail', 'Line : '.$th->getLine().'<br>'.$th->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $menu = $this->service->getById($id);
        $parentCode = $menu ? $menu->parentCode : null;

        $this->service->destroy($id, $this->title);

        // Redirect based on whether it's a sub menu or main menu
        if ($parentCode && $parentCode != '0') {
            return redirect()->route('master.menu.sub-menu', $parentCode)
                ->with('success', 'Delete Data Success');
        }

        return redirect()->route($this->view.'index')->with('success', 'Delete Data Success');
    }

    /**
     * DataTable for main menus.
     */
    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->findAllMainMenu();

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('has_submenu', function ($row) {
                    if ($row->hasChildren()) {
                        return '<span class="badge bg-success">Yes</span>';
                    }

                    return '<span class="badge bg-secondary">No</span>';
                })
                ->addColumn('submenu_count', function ($row) {
                    return $row->children()->count();
                })
                ->addColumn('action', function ($row) {
                    $subMenuBtn = '<a href="'.route('master.menu.sub-menu', $row->code).'"
                       class="btn btn-icon btn-sm bg-info-subtle me-1"
                       data-bs-toggle="tooltip" title="View Sub Menu">
                        <i class="mdi mdi-menu fs-14 text-info"></i>
                    </a>';

                    $addSubMenuBtn = '<a href="'.route('master.menu.create-sub-menu', $row->code).'"
                       class="btn btn-icon btn-sm bg-success-subtle me-1"
                       data-bs-toggle="tooltip" title="Add Sub Menu">
                        <i class="mdi mdi-plus fs-14 text-success"></i>
                    </a>';

                    $editBtn = '<a href="'.route('master.menu.edit', $row->id).'"
                       class="btn btn-icon btn-sm bg-primary-subtle me-1"
                       data-bs-toggle="tooltip" title="Edit">
                        <i class="mdi mdi-pencil-outline fs-14 text-primary"></i>
                    </a>';

                    $deleteBtn = '<a href="javascript:deleteData(\''.$row->id.'\')"
                       class="btn btn-icon btn-sm bg-danger-subtle"
                       data-bs-toggle="tooltip" title="Delete">
                        <i class="mdi mdi-delete fs-14 text-danger"></i>
                    </a>';

                    return '<td>'.$subMenuBtn.$addSubMenuBtn.$editBtn.$deleteBtn.'</td>';
                })
                ->rawColumns(['has_submenu', 'action'])
                ->toJson();
        }
    }

    /**
     * DataTable for sub menus.
     */
    public function datatableSubMenu(Request $request, $parentCode)
    {
        if ($request->ajax()) {
            $data = $this->service->getSubMenus($parentCode);

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $editBtn = '<a href="'.route('master.menu.edit', $row->id).'"
                       class="btn btn-icon btn-sm bg-primary-subtle me-1"
                       data-bs-toggle="tooltip" title="Edit">
                        <i class="mdi mdi-pencil-outline fs-14 text-primary"></i>
                    </a>';

                    $deleteBtn = '<a href="javascript:deleteData(\''.$row->id.'\', \''.$row->parentCode.'\')"
                       class="btn btn-icon btn-sm bg-danger-subtle"
                       data-bs-toggle="tooltip" title="Delete">
                        <i class="mdi mdi-delete fs-14 text-danger"></i>
                    </a>';

                    return '<td>'.$editBtn.$deleteBtn.'</td>';
                })
                ->rawColumns(['action'])
                ->toJson();
        }
    }
}
