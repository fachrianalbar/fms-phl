<?php

namespace App\Http\Controllers\Finance;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\MenuService;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\Bank\UserBankService;
use App\Services\Finance\InvoiceService;
use App\Services\Master\CustomerService;
use Illuminate\Support\Facades\Validator;
use App\Helpers\FilterHelper;
use Illuminate\Support\Facades\View;
use Mpdf\Mpdf;
use App\Services\Finance\InvoicePaymentService;

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
        return view($this->view . 'index')
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
            return redirect()->route($this->view . 'index')->with('fail', 'Data not found');
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

        return view($this->view . 'edit')
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
            'amount' => ['required', 'numeric', 'max:' . $remaining],
            'paymentDate' => ['required'],
            'userBankCode' => ['required'],
        ], [
            'amount.max' => 'The payment amount cannot be greater than the remaining invoice amount.',
            'userBankCode.required' => 'User bank field is required',
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

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            // Ambil data invoice yang sudah ada pembayaran
            $data = $this->service->datatable()->filter(function ($invoice) {
                return count($invoice->payments) > 0;
            });

            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('customer.name', function ($row) {
                    $customer = '';
                    if (isset($row->customer->name)) {
                        $customer = $row->customer->name;
                    }

                    return $customer;
                })
                ->addColumn('receivingBank', function ($row) {
                    $bank = '';
                    if (count($row->payments) > 0) {
                        $lastPayment = $row->payments->last();
                        if ($lastPayment && $lastPayment->userBank) {
                            $bank = $lastPayment->userBank->bank->name . ' - ' . $lastPayment->userBank->accountNumber;
                        }
                    }
                    return $bank;
                })
                ->addColumn('totalBilling', function ($row) {
                    $totalBilling = (float) ($row->invoiceAmount ?? 0) + (float) ($row->ppnAmount ?? 0);

                    return '' . number_format($totalBilling, 0, ',', '.');
                })
                ->addColumn('paymentDetails', function ($row) {
                    $details = '';
                    if (count($row->payments) > 0) {
                        foreach ($row->payments as $payment) {
                            $paymentDate = \Carbon\Carbon::parse($payment->paymentDate)->format('d-M-Y');
                            $amount = number_format($payment->amount, 0, ',', '.');
                            $details .= '<div class="mb-1">';
                            $details .= '<small><strong>Tgl:</strong> ' . $paymentDate . ' | ';
                            $details .= '<strong>Jumlah:</strong> Rp ' . $amount . '</small>';
                            if ($payment->description) {
                                $details .= '<br><small class="text-muted">Ket: ' . $payment->description . '</small>';
                            }
                            $details .= '</div>';
                        }
                    }

                    return $details;
                })
                ->addColumn('totalPayment', function ($row) {
                    $totalPayment = 0;
                    if (count($row->payments) > 0) {
                        foreach ($row->payments as $item) {
                            $totalPayment += $item->amount;
                        }
                    }

                    return number_format($totalPayment, 0, ',', '.');
                })
                ->addColumn('statusPayment', function ($row) {
                    $status = '';
                    $totalBilling = (float) ($row->invoiceAmount ?? 0) + (float) ($row->ppnAmount ?? 0);
                    $totalPaid = 0;
                    foreach ($row->payments as $item) {
                        $totalPaid += $item->amount;
                    }
                    if ($totalPaid < $totalBilling && $totalPaid > 0) {
                        $status = '<span class="badge bg-warning">Partial Payment</span>';
                    }
                    if ($totalPaid >= $totalBilling && $totalPaid > 0) {
                        $status = '<span class="badge bg-success">Full Payment</span>';
                    }
                    if (count($row->payments) == 0) {
                        $status = '<span class="badge bg-secondary">No Payment</span>';
                    }

                    return $status;
                })
                ->rawColumns(['customer.name', 'totalBilling', 'paymentDetails', 'totalPayment', 'statusPayment', 'receivingBank'])
                ->toJson();
        }
    }


    public function exportPdf(Request $request)
    {
        // Define filters - can be extended
        $filters = [
            'invoiceNumber' => $request->invoiceNumber,
            'customer_name' => $request->customerName,
        ];

        $relations = [
            'customer_name' => 'customer.name',
        ];

        $dateFilters = [
            'invoiceDate' => [
                'start' => $request->startDate,
                'end' => $request->endDate,
            ],
        ];

        $query = \App\Models\Finance\Invoice::with(['details', 'payments.userBank.bank', 'customer'])
            ->whereHas('payments')
            ->orderBy('invoiceDate', 'desc');

        $data = FilterHelper::applyFilters($query, $filters, $relations, $dateFilters)->get();

        $mpdf = new Mpdf([
            'orientation' => 'P',
            'format' => 'A4'
        ]);

        $mpdf->setAutoTopMargin = 'stretch';
        $mpdf->setAutoBottomMargin = 'stretch';

        // header
        $headerHtml = View::make('finance.invoice-payment.report.invoice-payment-pdf-header', [
            'title' => $this->title,
            'date' => Carbon::now(),
        ])->render();
        $mpdf->WriteHTML($headerHtml);

        // chunk rows
        $chunkSize = 200;
        $chunks = $data->chunk($chunkSize);
        $start = 0;
        if ($data->isEmpty()) {
            // If no data, print a single row showing 'Data Not Found' and close table
            $noDataHtml = '<tr><td colspan="9" style="text-align:center; padding: 8px;">Data Not Found</td></tr></tbody></table>';
            $mpdf->WriteHTML($noDataHtml);
        } else {
            foreach ($chunks as $chunk) {
                $rowsHtml = View::make('finance.invoice-payment.report.invoice-payment-pdf-rows')
                    ->with('data', $chunk)
                    ->with('start', $start)
                    ->render();
                $mpdf->WriteHTML($rowsHtml);
                $start += $chunk->count();
            }
        }

        // footer summary
        $footerHtml = View::make('finance.invoice-payment.report.invoice-payment-pdf-footer')
            ->with('data', $data)
            ->render();
        $mpdf->WriteHTML($footerHtml);

        return $mpdf->Output('Invoice-Payment-Report.pdf', 'I');
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new \App\Exports\InvoicePaymentExport($request), 'invoice-payment-list-' . Carbon::now()->format('Y-m-d H:i:s') . '.xlsx');
    }
}
