<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Services\Master\CompanyService;
use App\Services\Master\CustomerService;
use App\Services\MenuService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class CustomerController extends Controller
{
    protected $service;

    protected $title;

    protected $view;

    protected $menuSvc;

    protected $companySvc;

    public function __construct(CustomerService $customerSvc, MenuService $menuSvc, CompanyService $companySvc)
    {
        $this->service = $customerSvc;
        $this->title = 'Customer';
        $this->menuSvc = $menuSvc->getByName('Customer');
        $this->title = Auth::user()->languange == 'en' ? $this->menuSvc->name : $this->menuSvc->nama;
        $this->companySvc = $companySvc;
        $this->view = 'master.customer.';
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view($this->view.'index')
            ->with('view', $this->view)
            ->with('title', $this->title);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $company = $this->companySvc->findAll();

        return view($this->view.'create')
            ->with('view', $this->view)
            ->with('company', $company)
            ->with('title', $this->title);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            // 'phone' => [
            //     Rule::unique('customer', 'phone')->whereNull('deleted_at')
            // ],
            // 'email' => [Rule::unique('customer', 'email')->whereNull('deleted_at')],
            // 'telegramUsername' => [Rule::unique('customer', 'telegramUsername')->whereNull('deleted_at')],

            // 'nickname' => ['required', 'nickname', 'unique:users,nickname'],
            // 'ppn' => ['required', 'numeric'],
            // 'pph' => ['required', 'numeric'],
            // 'accountNumber' => ['required', 'numeric'],
            // 'picName' => 'required',
            // 'nickname' => 'required',
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
        $data = $this->service->getById($id);

        if (! $data) {
            return redirect()->route($this->view.'index')->with('fail', 'Data not found');
        }

        $company = $this->companySvc->findAll();

        return view($this->view.'edit')
            ->with('view', $this->view)
            ->with('company', $company)
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
            // 'email' => [Rule::unique('customer', 'email')->ignore($data->id)->whereNull('deleted_at')],
            // 'telegramUsername' => [Rule::unique('customer', 'telegramUsername')->ignore($data->id)->whereNull('deleted_at')],
            // 'phone' => [
            //     Rule::unique('customer', 'phone')
            //         ->ignore($data->id)
            //         ->whereNull('deleted_at')
            // ],
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

            return redirect()->route($this->view.'index')->with('fail', 'Line : '.$th->getLine().'<br>'.$th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->service->destroy($id, $this->title);

        return redirect()->route($this->view.'index')->with('success', __('general.delete_data_success'));
    }

    public function deleteCustomerDetail($id)
    {
        $this->service->deleteCustomerDetail($id);

        return redirect()->back()->with('success', __('general.delete_data_success'));
    }

    public function customerDetail($customerId)
    {
        return $this->service->customerDetail($customerId);
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->findAll();

            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('company.name', function ($row) {
                    return isset($row->company->name) ? $row->company->name : '';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<td>
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
                ->rawColumns(['action', 'company.name'])
                ->toJson();
        }
    }

    public function customerCompanyFormat($code)
    {
        return $this->service->customerCompanyFormat($code);
    }

    public function deleteCustomerPic($id)
    {
        $this->service->deleteCustomerPic($id);

        return redirect()->back()->with('success', __('general.delete_data_success'));
    }
}
