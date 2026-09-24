<?php

namespace App\Http\Controllers\Inventory;

use App\Exports\SupplierExport;
use App\Http\Controllers\Controller;
use App\Models\Inventory\Supplier;
use App\Services\Inventory\SupplierService;
use App\Services\MenuService;
use App\Services\UniqueCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;
use Yajra\DataTables\DataTables;

class SupplierController extends Controller
{
    protected $service;

    protected $title;

    protected $view;

    protected $menuSvc;

    public function __construct(SupplierService $supplierSvc, MenuService $menuSvc)
    {
        $this->service = $supplierSvc;
        $this->title = 'Supplier';
        $this->view = 'inventory.supplier.';
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $suppliers = Supplier::query()
            ->orderBy('code', 'asc')
            ->select(['code', 'name'])
            ->get();

        return view($this->view.'index')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('suppliers', $suppliers);
    }

    /**
     * Show the form for creating a new resource.
     * CRUD dialihkan ke modal pada halaman index; endpoint ini dipertahankan
     * sebagai fallback redirect agar URL lama tidak 404.
     */
    public function create()
    {
        return redirect()->route($this->view.'index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required',
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->route($this->view.'index')->with('fail', $validator->errors()->all()[0]);
        }
        try {
            $code = app(UniqueCodeService::class)->runWithDuplicateRetry(function () use ($request) {
                return DB::transaction(fn () => $this->service->store($request, $this->title));
            });

            $redirect = redirect()->route($this->view.'index')
                ->with('success', $this->title.' '.__('general.data_was_save_successfully'));

            return $code->wasChanged ? $redirect->with('code_replaced', $code->flashPayload()) : $redirect;
        } catch (\Throwable $th) {
            return redirect()->route($this->view.'index')->with('fail', 'Line : '.$th->getLine().'<br>'.$th->getMessage());
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
        // CRUD dialihkan ke modal pada halaman index; endpoint ini dipertahankan
        // sebagai fallback redirect agar URL lama tidak 404.
        return redirect()->route($this->view.'index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required',
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->route($this->view.'index')->with('fail', $validator->errors()->all()[0]);
        }
        try {
            $code = app(UniqueCodeService::class)->runWithDuplicateRetry(function () use ($request, $id) {
                return DB::transaction(fn () => $this->service->update($request, $id, $this->title));
            });

            $redirect = redirect()->route($this->view.'index')
                ->with('success', $this->title.' '.__('general.data_was_update_succesfully'));

            return $code->wasChanged ? $redirect->with('code_replaced', $code->flashPayload()) : $redirect;
        } catch (\Throwable $th) {
            return redirect()->route($this->view.'index')->with('fail', 'Line : '.$th->getLine().'<br>'.$th->getMessage());
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

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $query = Supplier::query()->orderBy('code', 'asc');

            if ($request->filled('supplierCode')) {
                $query->where('code', $request->supplierCode);
            }

            if ($request->filled('startDate')) {
                $query->whereDate('created_at', '>=', $request->startDate);
            }

            if ($request->filled('endDate')) {
                $query->whereDate('created_at', '<=', $request->endDate);
            }

            return Datatables::of($query)
                ->addIndexColumn()
                ->filterColumn('DT_RowIndex', function ($query, $keyword) {
                    return $query;
                })
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && ! empty($request->search['value'])) {
                        $search = strtolower($request->search['value']);
                        $query->where(function ($q) use ($search) {
                            $q->whereRaw('LOWER(code) LIKE ?', ['%'.$search.'%'])
                                ->orWhereRaw('LOWER(name) LIKE ?', ['%'.$search.'%'])
                                ->orWhereRaw('LOWER(address) LIKE ?', ['%'.$search.'%'])
                                ->orWhereRaw('LOWER(pic) LIKE ?', ['%'.$search.'%'])
                                ->orWhereRaw('LOWER(phone) LIKE ?', ['%'.$search.'%'])
                                ->orWhereRaw('LOWER(email) LIKE ?', ['%'.$search.'%']);
                        });
                    }
                })
                ->editColumn('ppn', function ($row) {
                    return $row->ppn !== null && $row->ppn !== '' ? $row->ppn : '-';
                })
                ->editColumn('pph', function ($row) {
                    return $row->pph !== null && $row->pph !== '' ? $row->pph : '-';
                })
                ->addColumn('action', function ($row) {
                    $editUrl = route($this->view.'update', $row->id);

                    $btn = '<div class="d-inline-flex gap-1">'
                        .'<button type="button"'
                        .' class="btn btn-icon btn-sm bg-primary-subtle js-edit-supplier"'
                        .' data-bs-toggle="modal" data-bs-target="#supplierCrudModal"'
                        .' data-action="'.$editUrl.'"'
                        .' data-code="'.e($row->code).'"'
                        .' data-name="'.e($row->name).'"'
                        .' data-pic="'.e($row->pic ?? '').'"'
                        .' data-address="'.e($row->address ?? '').'"'
                        .' data-email="'.e($row->email ?? '').'"'
                        .' data-phone="'.e($row->phone ?? '').'"'
                        .' data-ppn="'.e($row->ppn ?? '').'"'
                        .' data-pph="'.e($row->pph ?? '').'"'
                        .' title="Edit">'
                        .'<i class="mdi mdi-pencil-outline fs-14 text-primary"></i>'
                        .'</button>'
                        .'<a href="javascript:deleteData(\''.$row->id.'\')"'
                        .' class="btn btn-icon btn-sm bg-danger-subtle"'
                        .' data-bs-toggle="tooltip" title="Delete">'
                        .'<i class="mdi mdi-delete fs-14 text-danger"></i>'
                        .'</a>'
                        .'</div>';

                    return $btn;
                })
                ->rawColumns(['action'])
                ->toJson();
        }
    }

    public function excelSupplier(Request $request)
    {
        return Excel::download(new SupplierExport($request), 'Supplier-Report.xlsx');
    }

    public function pdfSupplier(Request $request)
    {
        $query = Supplier::query()->orderBy('code', 'asc');

        if ($request->filled('supplierCode')) {
            $query->where('code', $request->supplierCode);
        }

        if ($request->filled('startDate')) {
            $query->whereDate('created_at', '>=', $request->startDate);
        }

        if ($request->filled('endDate')) {
            $query->whereDate('created_at', '<=', $request->endDate);
        }

        $suppliers = $query->get();
        $supplierName = null;

        if ($request->filled('supplierCode')) {
            $supplierName = Supplier::query()->where('code', $request->supplierCode)->value('name');
        }

        $mpdf = new Mpdf([
            'orientation' => 'L',
            'format' => 'A4',
            'tempDir' => storage_path('app/mpdf-temp'),
        ]);

        $mpdf->WriteHTML(
            view($this->view.'report.supplier-pdf')
                ->with('suppliers', $suppliers)
                ->with('supplierName', $supplierName)
                ->with('startDate', $request->startDate)
                ->with('endDate', $request->endDate)
        );

        return $mpdf->Output('Supplier-Report.pdf', 'I');
    }
}
