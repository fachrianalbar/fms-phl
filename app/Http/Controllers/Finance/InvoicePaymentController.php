<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Services\Bank\UserBankService;
use App\Services\Finance\InvoicePaymentService;
use App\Services\Finance\InvoiceService;
use App\Services\Master\CustomerService;
use App\Services\MenuService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class InvoicePaymentController extends Controller
{
    protected $service;

    protected $title;

    protected $view;

    protected $menuSvc;

    protected $invoiceSvc;

    protected $customerSvc;

    protected $userBankSvc;

    protected $totalPrice;

    protected $totalPriceInvoice;

    public function __construct(InvoicePaymentService $invoicePaymentSvc, InvoiceService $invoiceSvc, CustomerService $customerSvc, UserBankService $userBankSvc, MenuService $menuSvc)
    {
        $this->service = $invoicePaymentSvc;
        $this->title = 'Invoice Payment';
        $this->view = 'finance.invoice-payment.';
        $this->customerSvc = $customerSvc;
        $this->invoiceSvc = $invoiceSvc;
        $this->userBankSvc = $userBankSvc;
        $this->totalPrice = 0;
        $this->totalPriceInvoice = 0;
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
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data = $this->invoiceSvc->getById($id);

        if (! $data) {
            return redirect()->route($this->view.'index')->with('fail', 'Data not found');
        }

        $customer = $this->customerSvc->findAll();
        $customerData = $this->customerSvc->getByCode($data->customerCode);
        $order = $this->invoiceSvc->getOrderDetail($id);
        $userBank = $this->userBankSvc->findCompany();

        // Use the invoice service to get the invoiceAmount (which already includes PPN)
        $totals = $this->invoiceSvc->calculateInvoiceAmount($data);
        $totalPrice = $totals['total'];

        if (count($data->payments) > 0) {
            foreach ($data->payments as $item) {
                $totalPrice -= $item->amount;
            }
        }

        $status = 0;
        if ($totalPrice == 0) {
            // Full Payment
            $status = 2;
        }

        return view($this->view.'edit')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('customer', $customer)
            ->with('order', $order)
            ->with('totalPrice', $totalPrice)
            ->with('userBank', $userBank)
            ->with('customerData', $customerData)
            ->with('status', $status)
            ->with('data', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $invoice = $this->invoiceSvc->getById($id);
        $totalsInvoice = $this->invoiceSvc->calculateInvoiceAmount($invoice);
        $invoiceAmount = $totalsInvoice['total'];
        $totalPaid = 0;
        foreach ($invoice->payments as $p) {
            $totalPaid += $p->amount;
        }
        $remaining = (int) ($invoiceAmount - $totalPaid);

        $validator = Validator::make($request->all(), [
            'amount' => ['required', 'numeric', 'max:'.$remaining],
            'paymentDate' => ['required'],
            'userBankCode' => ['required'],
        ], [
            'amount.max' => 'The payment amount cannot be greater than the remaining invoice amount.',
            'userBankCode.required' => 'User bank field is required',
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

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->datatable();

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
                ->addColumn('totalPrice', function ($row) {
                    // invoiceAmount now stores SUBTOTAL (without PPN)
                    $subtotal = (float) ($row->invoiceAmount ?? 0);

                    return ''.number_format($subtotal, 0, ',', '.');
                })
                ->addColumn('ppn', function ($row) {
                    $ppnAmount = (float) ($row->ppnAmount ?? 0);

                    return number_format($ppnAmount, 0, '.', ',');
                })
                ->addColumn('totalBilling', function ($row) {
                    $totalBilling = (float) ($row->invoiceAmount ?? 0) + (float) ($row->ppnAmount ?? 0);

                    return ''.number_format($totalBilling, 0, ',', '.');
                })
                ->addColumn('statusPayment', function ($row) {
                    $status = '';
                    $totalBilling = (float) ($row->invoiceAmount ?? 0) + (float) ($row->ppnAmount ?? 0);
                    $totalPaid = 0;
                    foreach ($row->payments as $item) {
                        $totalPaid += $item->amount;
                    }
                    if ($totalPaid < $totalBilling && $totalPaid > 0) {
                        $status = 'Half Payment';
                    }
                    if ($totalPaid == $totalBilling && $totalPaid > 0) {
                        $status = 'Full Payment';
                    }
                    if (count($row->payments) == 0) {
                        $status = 'No Payment';
                    }

                    return $status;
                })
                ->addColumn('totalPayment', function ($row) {
                    $totalPayment = 0;
                    if (count($row->payments) > 0) {
                        foreach ($row->payments as $item) {
                            $totalPayment += $item->amount;
                        }
                    }

                    return number_format($totalPayment, 0, '.', ',');
                })
                ->addColumn('action', function ($row) {
                    $btn = '<ul class="action">
                                        <li class="edit"> <a href="'.route($this->view.'edit', $row->id).'"><i class="icon-credit-card"></i></a></li>
                                    </ul>';

                    return $btn;
                })
                ->rawColumns(['action', 'orderCount', 'totalPrice', 'ppn', 'totalBilling', 'customer.name', 'statusPayment', 'totalPayment'])
                ->toJson();
        }
    }
}
