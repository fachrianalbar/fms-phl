<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\RoleMenu;
use App\Services\RoleService;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    protected $service;
    protected $title;
    protected $view;
    protected $menuSvc;

    public function __construct(RoleService $roleSvc, MenuService $menuSvc)
    {
        $this->service = $roleSvc;
        $this->title = "Role";
        $this->view = "administrator.role.";
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
        return view($this->view . 'create')
            ->with('view', $this->view)
            ->with('title', $this->title);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
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

    public function roleAccess(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            // Extract selected menus from the form submission
            $selectedMenus = $request->input('menu', []);

            // Build dynamic menu structure from the Menu model in the database
            $menuStructure = $this->menuStructure();


            $this->service->roleAccess($selectedMenus, $menuStructure, $id);

            DB::commit();

            return redirect()->route($this->view . 'index')->with('success', 'Role Access menu was save succesfully');
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->route($this->view . 'index')->with('fail', 'Line : ' . $th->getLine() . '<br>' . $th->getMessage());
        }
    }

    /**
     * Build the menu structure dynamically from the Menu model
     */
    private function menuStructure()
    {
        // Initialize an empty array for menu structure
        $menuStructure = [];

        // Fetch all headers (where parentCode is 0)
        $headers = Menu::where('parentCode', '0')->get();

        // Loop through each header
        foreach ($headers as $header) {
            // Fetch child menus for the current header
            $children = Menu::where('parentCode', $header->code)->pluck('code')->toArray();

            // Add to menuStructure if there are children
            if (!empty($children)) {
                $menuStructure[$header->code] = $children;
            }
        }

        return $menuStructure;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = $this->service->getById($id);

        if (!$data) {
            return redirect()->route($this->view . 'index')->with('fail', 'Data not found');
        }

        $menus = Menu::OrderBy('sort', 'asc')->get();

        $menuArr = RoleMenu::where('roleCode', $data->code)->pluck('menuCode')->toArray();


        return view($this->view . 'show')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('menus', $menus)
            ->with('menuArr', $menuArr)
            ->with('data', $data);
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

        return view($this->view . 'edit')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('data', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
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
                ->addColumn('action', function ($row) {
                    $btn = ' <td>
                            <a href="' . route($this->view . 'show', $row->id) . '"
                            class="btn btn-icon btn-sm bg-success-subtle me-1"
                            data-bs-toggle="tooltip" title="Role Access">
                                <i class="mdi mdi-lock-open fs-14 text-success"></i>
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
