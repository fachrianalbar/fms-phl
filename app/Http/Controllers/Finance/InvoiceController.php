<?php

namespace App\Http\Controllers\Finance;

use App\Helpers\FilterHelper;
use App\Http\Controllers\Controller;
use App\Services\MenuService;
use App\Models\CompanySetting;
use App\Models\Data\Route;
use App\Models\Data\TonaseBonus;
use App\Services\Finance\InvoiceService;
use App\Services\Master\CustomerService;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Mpdf\Mpdf;

class InvoiceController extends Controller
{
    protected $service;
    protected $title;
    protected $view;
    protected $menuSvc;
    protected $customerSvc;
    protected $totalPrice;
    protected $totalPriceInvoice;

    public function __construct(InvoiceService $invoiceSvc, CustomerService $customerSvc, MenuService $menuSvc)
    {
        $this->service = $invoiceSvc;
        $this->title = "Invoice";
        $this->view = "finance.invoice.";
        $this->customerSvc = $customerSvc;
        $this->totalPrice = 0;
        $this->totalPriceInvoice = 0;
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
        $customer = $this->customerSvc->findAll();

        return view($this->view . 'create')
            ->with('view', $this->view)
            ->with('customer', $customer)
            ->with('title', $this->title);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customerCode' => 'required',
            'invoiceNumber' => 'required',
            'receiptNumber' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->route($this->view . 'index')->with('fail', $validator->errors()->all()[0]);
        }
        try {
            DB::beginTransaction();

            $selectedOrders = json_decode($request->input('selectedOrders'), true);

            $this->service->store($request, $this->title, $selectedOrders);

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

        if (!$data) {
            return redirect()->route($this->view . 'index')->with('fail', 'Data not found');
        }

        $customer = $this->customerSvc->findAll();
        $customerData = $this->customerSvc->getByCode($data->customerCode);
        $order = $this->service->getOrderDetail($id);

        $status = 0;

        if (count($data->payments) > 0) {
            $status = 1;
        }

        return view($this->view . 'edit')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('customer', $customer)
            ->with('order', $order)
            ->with('customerData', $customerData)
            ->with('status', $status)
            ->with('data', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            // 'customerCode' => 'required',
            'invoiceNumber' => 'required',
            'receiptNumber' => 'required',
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

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->findAll();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('orderCount', function ($row) {
                    return $row->details->count();
                })
                ->editColumn('customer.name', function ($row) {
                    $customer = '';

                    if (isset($row->customer->name)) {
                        $customer = $row->customer->name;
                    }

                    return $customer;
                })

                ->addColumn('price', function ($row) {
                    $price = 0;

                    foreach ($row->details as $item) {
                        $price = $item->order->route->price * $item->order->qty;
                    }

                    $this->totalPriceInvoice = $price;


                    return '' . number_format($price, 0, ',', '.');
                })
                ->addColumn('ppn', function ($row) {
                    $customer = $row->details->first()->order->customer;

                    return number_format($this->totalPriceInvoice * ($customer->ppn / 100), 0, '.', ',');
                })
                ->addColumn('totalBilling', function ($row) {
                    $customer = $row->details->first()->order->customer;

                    return '' . number_format($this->totalPriceInvoice *  ($customer->ppn / 100) + $this->totalPriceInvoice, 0, ',', '.');
                })

                ->addColumn('action', function ($row) {

                    $btn = ' <td>
                            <a target="_blank" href="' . route($this->view . 'pdf-invoice', $row->id) . '"
                            class="btn btn-icon btn-sm bg-success-subtle me-1"
                            data-bs-toggle="tooltip" title="Print PDF">
                                <i class="mdi mdi-file fs-14 text-success"></i>
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

                    if (count($row->payments) > 0) {
                        $btn = ' <td>
                            <a target="_blank" href="' . route($this->view . 'pdf-invoice', $row->id) . '"
                            class="btn btn-icon btn-sm bg-success-subtle me-1"
                            data-bs-toggle="tooltip" title="Print PDF">
                                <i class="mdi mdi-file-pdf fs-14 text-success"></i>
                            </a>

                            <a href="' . route($this->view . 'edit', $row->id) . '"
                            class="btn btn-icon btn-sm bg-primary-subtle me-1"
                            data-bs-toggle="tooltip" title="Edit">
                                <i class="mdi mdi-pencil-outline fs-14 text-primary"></i>
                            </a>
                        </td>';
                    }
                    return $btn;
                })
                ->rawColumns(['action', 'orderCount', 'ppn', 'totalBilling', 'customer.name', 'price'])
                ->toJson();
        }
    }

    public function datatableOrder(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->getOrder();

            // Definisikan kolom filter dengan alias
            $filters = [
                'customer_code' => $request->customerCode,
            ];

            // Hubungkan alias ke relasi dan kolom yang sesuai
            $relations = [
                'customer_code' => 'customer.code',
            ];

            $data = FilterHelper::applyFilters($data, $filters, $relations);

            return Datatables::of($data->get())
                ->addIndexColumn()
                ->editColumn('fleet.plateNumber', function ($row) {
                    $fleet = '';

                    if (isset($row->fleet->plateNumber)) {
                        $fleet = $row->fleet->plateNumber;
                    }

                    return $fleet;
                })
                ->editColumn('route.originLocation.name', function ($row) {
                    $origin = '';

                    if (isset($row->route->originLocation->name)) {
                        $origin = $row->route->originLocation->name;
                    }

                    return $origin;
                })

                ->editColumn('orderType.name', function ($row) {
                    $type = '';

                    if (isset($row->orderType->name)) {
                        $type = $row->orderType->name;
                    }

                    return $type;
                })
                ->editColumn('route.destinationLocation.name', function ($row) {
                    $destination = '';

                    if (isset($row->route->destinationLocation->name)) {
                        $destination = $row->route->destinationLocation->name;
                    }

                    return $destination;
                })
                ->addColumn('addCost', function ($row) {
                    $cost = 0;
                    if (isset($row->cost)) {
                        foreach ($row->cost as $item) {
                            $cost += $item->nominal;
                        }
                    }
                    $this->totalPrice = $cost;
                    return '' . number_format($cost, 0, ',', '.');
                })
                ->addColumn('totalPrice', function () {
                    return '' . number_format($this->totalPrice, 0, ',', '.');
                })
                ->addColumn('action', function ($row) {
                    $btn = '<input class="order-checkbox" type="checkbox" name="order[]" data-id="' . $row->code . '" value="' . $row->code . '">';

                    return $btn;
                })
                ->editColumn('orderDate', function ($row) {
                    return Carbon::parse($row->orderDate)->format('d-M-Y');
                })
                ->rawColumns(['action', 'orderDate', 'fleet.plateNumber', 'route.originLocation.name', 'route.destinationLocation.name', 'orderType.name', 'addCost', 'totalPrice'])
                ->toJson();
        }
    }

    public function storeInvoiceDetail(Request $request, $id)
    {
        $selectedOrders = json_decode($request->input('selectedOrders'), true);

        $validator = Validator::make($request->all(), [
            'order' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('fail', $validator->errors()->all()[0]);
        }

        try {
            DB::beginTransaction();

            $this->service->storeInvoiceDetail($request, $id, $selectedOrders);

            DB::commit();

            return redirect()->back()->with('success', $this->title . ' ' . __('general.data_was_save_successfully'));
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->back()->with('fail', 'Line : ' . $th->getLine() . '<br>' . $th->getMessage());
        }
    }

    public function destroyInvoiceDetail($id)
    {
        $this->service->destroyInvoiceDetail($id, $this->title);

        return redirect()->back()->with('success', 'Delete Order Data Success');
    }

    public function pdfInvoice($id)
    {
        $data = $this->service->getById($id);

        if (!$data) {
            return redirect()->route($this->view . 'index')->with('fail', 'Data not found');
        }

        $company = CompanySetting::first();

        // Get invoice details with related data
        $invoiceDetails = $data->details()->with([
            'order.orderMaterial.material',
            'order.orderMaterial.unit',
            'order.cost',
            'order.customer',
            'order.fleet',
            'order.driver',
            'order.route.originLocation',
            'order.route.destinationLocation'
        ])->get();

        // Tentukan template PDF berdasarkan customer invoicePdf field
        $customer = $data->customer;
        $pdfTemplate = 'finance.invoice.pdf.general'; // Default template

        if ($customer && $customer->invoicePdf) {
            // Switch case untuk menentukan template berdasarkan invoicePdf field
            switch ($customer->invoicePdf) {
                case 'asia-makmur':
                    $pdfTemplate = 'finance.invoice.pdf.customer.asia-makmur';
                    break;
                case 'asia-sakti-wahid-foods-manufacture':
                    $pdfTemplate = 'finance.invoice.pdf.customer.asia-sakti-wahid-foods-manufacture';
                    break;
                case 'teguh-wibawa-bhakti-persada':
                    $pdfTemplate = 'finance.invoice.pdf.customer.teguh-wibawa-bhakti-persada';
                    break;
                case 'olam-indonesia':
                    $pdfTemplate = 'finance.invoice.pdf.customer.olam-indonesia';
                    break;
                case 'matahari-sakti':
                    $pdfTemplate = 'finance.invoice.pdf.customer.matahari-sakti';
                    break;
                case 'sriboga-flour-mill':
                    $pdfTemplate = 'finance.invoice.pdf.customer.sriboga-flour-mill';
                    break;
                case 'guna-layan-kuasa':
                    $pdfTemplate = 'finance.invoice.pdf.customer.guna-layan-kuasa';
                    break;
                case 'ekspedisi-berdikari':
                    $pdfTemplate = 'finance.invoice.pdf.customer.ekspedisi-berdikari';
                    break;
                case 'danitama-niaga-prima':
                    $pdfTemplate = 'finance.invoice.pdf.customer.danitama-niaga-prima';
                    break;
                case 'central-pertiwi-bahari':
                    $pdfTemplate = 'finance.invoice.pdf.customer.central-pertiwi-bahari';
                    break;
                default:
                    // Jika template tidak ditemukan, gunakan general
                    $pdfTemplate = 'finance.invoice.pdf.general';
                    break;
            }
        }

        $mpdf = new Mpdf(
            [
                'orientation' => 'P',
                'format' => [215, 330],
                'tempDir' => storage_path('app/mpdf-temp')
            ]
        );

        $mpdf->setAutoTopMargin = 'stretch';
        $mpdf->setAutoBottomMargin = 'stretch';

        $mpdf->WriteHTML(
            view($pdfTemplate)
                ->with('data', $data)
                ->with('company', $company)
                ->with('invoiceDetails', $invoiceDetails)
                ->with('customer', $customer)
        );

        return $mpdf->Output('Invoice-' . $data->invoiceNumber . '.pdf', 'I');
    }

    public function customerInvoice($customerCode)
    {
        return $this->customerSvc->getByCode($customerCode);
    }
}
