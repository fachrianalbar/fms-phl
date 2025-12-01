<?php

namespace App\Http\Controllers\Inventory;

use App\Helpers\FilterHelper;
use App\Http\Controllers\Controller;
use App\Models\inventory\Item;
use App\Services\Inventory\ItemCategoryService;
use App\Services\Inventory\ItemLocationService;
use App\Services\Inventory\ItemService;
use App\Services\Inventory\SupplierService;
use App\Services\Inventory\WarehouseService;
use App\Services\Master\UnitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;

class ItemController extends Controller
{
    protected $service;

    protected $unitSvc;

    protected $categorySvc;

    protected $warehouseSvc;

    protected $supplierSvc;

    protected $locationSvc;

    protected $title;

    protected $view;

    protected $menuSvc;

    public function __construct(
        ItemService $itemSvc,
        UnitService $unitSvc,
        ItemCategoryService $categorySvc,
        WarehouseService $warehouseSvc,
        SupplierService $supplierSvc,
        ItemLocationService $locationSvc
    ) {
        $this->service = $itemSvc;
        $this->unitSvc = $unitSvc;
        $this->categorySvc = $categorySvc;
        $this->warehouseSvc = $warehouseSvc;
        $this->supplierSvc = $supplierSvc;
        $this->locationSvc = $locationSvc;
        $this->title = 'Item';
        $this->view = 'inventory.items.';
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Item::orderBy('code', 'asc')->get();

        return view($this->view . 'index')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('items', $items);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $unit = $this->unitSvc->findAllInventory();
        $category = $this->categorySvc->findAll();
        $warehouse = $this->warehouseSvc->findAll();
        $supplier = $this->supplierSvc->findAll();
        $location = $this->locationSvc->findAll();

        return view($this->view . 'create')
            ->with('view', $this->view)
            ->with('unit', $unit)
            ->with('location', $location)
            ->with('category', $category)
            ->with('warehouse', $warehouse)
            ->with('supplier', $supplier)
            ->with('title', $this->title);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => ['required', Rule::unique('item', 'code')->whereNull('deleted_at')],
            'name' => 'required',
            'brandName' => 'required',
            'categoryCode' => 'required',
            // 'itemLocationCode' => 'required',
            'warehouseCode' => 'required',
            'unitCode' => 'required',
            'supplierCode' => 'required',
            // 'price' => 'required',
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

        if (! $data) {
            return redirect()->route($this->view . 'index')->with('fail', 'Data not found');
        }

        $unit = $this->unitSvc->findAllInventory();
        $category = $this->categorySvc->findAll();
        $warehouse = $this->warehouseSvc->findAll();
        $supplier = $this->supplierSvc->findAll();
        $location = $this->locationSvc->findAll();

        return view($this->view . 'edit')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('unit', $unit)
            ->with('location', $location)
            ->with('category', $category)
            ->with('warehouse', $warehouse)
            ->with('supplier', $supplier)
            ->with('data', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required',
            'name' => 'required',
            'brandName' => 'required',
            'categoryCode' => 'required',
            // 'itemLocationCode' => 'required',
            'warehouseCode' => 'required',
            'unitCode' => 'required',
            'supplierCode' => 'required',
            // 'price' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->route($this->view . 'index')->with('fail', $validator->errors()->all()[0]);
        }
        try {
            DB::beginTransaction();

            $this->service->update($request, $id, $this->title);

            DB::commit();

            return redirect()->route($this->view . 'index')->with('success', $this->title . ' ' . __('general.data_was_update_succesfully'));
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

            // Definisikan kolom filter dengan alias
            $filters = [
                'code' => $request->itemCode,
            ];

            // Hubungkan alias ke relasi dan kolom yang sesuai
            $relations = [];

            $dateFilters = [];

            $data = FilterHelper::applyFilters($data, $filters, $relations, $dateFilters);

            return Datatables::of($data)
                ->addIndexColumn()
                ->filterColumn('DT_RowIndex', function ($query, $keyword) {
                    return $query;
                })
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && !empty($request->search['value'])) {
                        $search = strtolower($request->search['value']);
                        $query->where(function ($q) use ($search) {
                            $q->whereRaw('LOWER(code) LIKE ?', ['%' . $search . '%'])
                                ->orWhereRaw('LOWER(name) LIKE ?', ['%' . $search . '%'])
                                ->orWhereRaw('LOWER(brandName) LIKE ?', ['%' . $search . '%']);
                        });
                    }
                })
                ->editColumn('price', function ($row) {
                    return number_format($row->price, 0, ',', '.');
                })
                ->editColumn('category.name', function ($row) {
                    $category = '';

                    if (isset($row->category->name)) {
                        $category = $row->category->name;
                    }

                    return $category;
                })
                ->editColumn('unit.name', function ($row) {
                    $unit = '';

                    if (isset($row->unit->name)) {
                        $unit = $row->unit->name;
                    }

                    return $unit;
                })
                ->editColumn('warehouse.name', function ($row) {
                    $warehouse = '';

                    if (isset($row->warehouse->name)) {
                        $warehouse = $row->warehouse->name;
                    }

                    return $warehouse;
                })
                ->editColumn('supplier.name', function ($row) {
                    $supplier = '';

                    if (isset($row->supplier->name)) {
                        $supplier = $row->supplier->name;
                    }

                    return $supplier;
                })
                ->editColumn('location.name', function ($row) {
                    $location = '';

                    if (isset($row->location->name)) {
                        $location = $row->location->name;
                    }

                    return $location;
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
                ->rawColumns(['action', 'category', 'unit.name', 'warehouse.name', 'supplier.name', 'location.name'])
                ->make();
        }
    }
}
