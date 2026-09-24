<?php

namespace App\Http\Controllers\Master;

use App\Exports\CustomerExport;
use App\Http\Controllers\Controller;
use App\Services\Master\CompanyService;
use App\Services\Master\CustomerService;
use App\Services\MenuService;
use App\Services\UniqueCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;
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
        $company = $this->companySvc->findAll();

        return view($this->view.'index')
            ->with('view', $this->view)
            ->with('company', $company)
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
            'code' => 'required|string|max:30',
            'name' => 'required',
            'ppn' => 'nullable|numeric|min:0|max:100',
            'pph' => 'nullable|numeric|min:0|max:100',
            'pphBaseType' => 'required|in:route,subtotal',
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
            'code' => 'required|string|max:30',
            'name' => 'required',
            'ppn' => 'nullable|numeric|min:0|max:100',
            'pph' => 'nullable|numeric|min:0|max:100',
            'pphBaseType' => 'required|in:route,subtotal',
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
            $data = $this->buildFilteredQuery($request);

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('companyName', function ($row) {
                    return $row->company->name ?? '-';
                })
                ->addColumn('taxPolicy', function ($row) {
                    $ppn = rtrim(rtrim(number_format((float) ($row->ppn ?? 0), 4, ',', '.'), '0'), ',');
                    $pph = rtrim(rtrim(number_format((float) ($row->pph ?? 0), 4, ',', '.'), '0'), ',');
                    $basis = $row->pphBaseType === 'route' ? 'Tarif Rute' : 'DPP Total';

                    return '<div class="d-flex flex-column gap-1">'
                        .'<span class="fs-11"><strong>PPN '.$ppn.'%</strong> · PPh '.$pph.'%</span>'
                        .'<span class="badge bg-warning-subtle text-warning align-self-start fs-10">PPh: '.$basis.'</span>'
                        .'</div>';
                })
                ->addColumn('action', function ($row) {
                    $editUrl = route($this->view.'edit', $row->id);

                    return '<a href="'.$editUrl.'" class="btn btn-icon btn-sm bg-primary-subtle me-1" data-bs-toggle="tooltip" title="Edit">'
                        .'<i class="mdi mdi-pencil-outline fs-14 text-primary"></i></a>'
                        .'<a href="javascript:deleteData(\''.$row->id.'\')" class="btn btn-icon btn-sm bg-danger-subtle" data-bs-toggle="tooltip" title="Delete">'
                        .'<i class="mdi mdi-delete fs-14 text-danger"></i></a>';
                })
                ->rawColumns(['taxPolicy', 'action'])
                ->toJson();
        }
    }

    /**
     * Bangun query customer yang sudah diterapkan filter bar (perusahaan, tipe, periode).
     */
    private function buildFilteredQuery(Request $request)
    {
        $query = $this->service->findAllQuery();

        if ($request->filled('companyCode')) {
            $query->where('companyCode', $request->companyCode);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('startDate')) {
            $query->whereDate('created_at', '>=', $request->startDate);
        }

        if ($request->filled('endDate')) {
            $query->whereDate('created_at', '<=', $request->endDate);
        }

        return $query;
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new CustomerExport($request), 'Customer-Report.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $rows = $this->buildFilteredQuery($request)->get();

        $companyName = null;
        if ($request->filled('companyCode')) {
            $companyName = optional($this->companySvc->getByCode($request->companyCode))->name;
        }

        $typeLabel = null;
        if ($request->filled('type')) {
            $typeLabel = $request->type === 'Company'
                ? __('menu_customer.company')
                : __('menu_customer.person');
        }

        $mpdf = new Mpdf([
            'orientation' => 'L',
            'format' => 'A4',
            'tempDir' => storage_path('app/mpdf-temp'),
        ]);

        $mpdf->WriteHTML(
            view($this->view.'report.customer-pdf')
                ->with('rows', $rows)
                ->with('companyName', $companyName)
                ->with('typeLabel', $typeLabel)
                ->with('startDate', $request->startDate)
                ->with('endDate', $request->endDate)
        );

        return $mpdf->Output('Customer-Report.pdf', 'I');
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
