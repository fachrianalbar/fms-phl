<?php

namespace App\Http\Controllers\Finance;

use App\Helpers\FilterHelper;
use App\Http\Controllers\Controller;
use App\Models\Finance\InvoicePayment;
use App\Services\Finance\InvoicePaymentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;
use Yajra\DataTables\DataTables;

class InvoicePaymentController extends Controller
{
    protected $service;

    protected $title;

    protected $view;

    public function __construct(InvoicePaymentService $invoicePaymentSvc)
    {
        $this->service = $invoicePaymentSvc;
        $this->title = 'Invoice Payment';
        $this->view = 'invoice.payment.';
    }

    /**
     * Daftar pembayaran faktur — 1 baris = 1 pembayaran (DP / cicilan / pelunasan).
     * Input pembayaran dilakukan lewat menu Transaksi Pembayaran.
     */
    public function index()
    {
        $paymentCount = DB::table('invoice_payment')->whereNull('deleted_at')->count();
        $paymentSum = (float) DB::table('invoice_payment')->whereNull('deleted_at')->sum('amount');
        $paidInvoiceCount = DB::table('invoice_payment')->whereNull('deleted_at')->distinct()->count('invoiceCode');

        $stats = [
            'paymentCount' => $paymentCount,
            'paymentSum' => $paymentSum,
            'paidInvoiceCount' => $paidInvoiceCount,
        ];

        return view($this->view.'index')
            ->with('view', $this->view)
            ->with('title', $this->title)
            ->with('stats', $stats);
    }

    /**
     * Datatable daftar pembayaran (per pembayaran, bukan per faktur).
     */
    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->service->datatable();
            $labels = $this->service->paymentLabels($data);

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('DT_RowIndex', function ($row) {
                    return '<span class="text-muted fw-semibold fs-12">'.($row->DT_RowIndex ?? '').'</span>';
                })
                ->editColumn('transactionCode', function ($row) {
                    if ($row->transaction) {
                        $url = route('invoice.payment-transaction.show', $row->transaction->id);

                        return '<a href="'.$url.'" class="font-monospace fw-bold text-primary fs-13 text-nowrap text-decoration-none" title="Lihat detail transaksi">'.htmlspecialchars($row->transactionCode ?? '-').'</a>';
                    }

                    return '<span class="text-muted font-monospace fs-12">-</span>';
                })
                ->addColumn('invoiceNumber', function ($row) {
                    $invoice = $row->invoice;

                    if (! $invoice) {
                        return '<span class="text-muted">-</span>';
                    }

                    $number = htmlspecialchars($invoice->invoiceNumber ?: $invoice->code);
                    $html = '<div class="text-start"><span class="font-monospace fw-bold text-primary fs-13 text-nowrap">'.$number.'</span>';

                    if ($invoice->invoiceDate) {
                        $html .= '<div class="text-muted fs-11 text-nowrap"><i class="mdi mdi-calendar-blank-outline me-1"></i>Tgl faktur: '.Carbon::parse($invoice->invoiceDate)->format('d M Y').'</div>';
                    }

                    return $html.'</div>';
                })
                ->addColumn('customerName', function ($row) {
                    $invoice = $row->invoice;
                    $customer = $invoice->customer->name ?? null;
                    $customerCode = $invoice->customerCode ?? null;

                    if (! $customer && ! $customerCode) {
                        return '<span class="text-muted">-</span>';
                    }

                    $name = htmlspecialchars($customer ?: $customerCode);
                    $code = $customerCode ? '<div class="text-muted font-monospace fs-11"><i class="mdi mdi-account-outline me-1"></i>'.htmlspecialchars($customerCode).'</div>' : '';

                    return '<div class="text-start"><span class="fw-semibold text-dark fs-13 d-block text-truncate" style="max-width: 200px;" title="'.$name.'">'.$name.'</span>'.$code.'</div>';
                })
                ->editColumn('paymentDate', function ($row) {
                    if (! $row->paymentDate) {
                        return '<span class="text-muted">-</span>';
                    }

                    return '<span class="fw-medium text-dark fs-12 text-nowrap">'.Carbon::parse($row->paymentDate)->format('d M Y').'</span>';
                })
                ->addColumn('receivingBank', function ($row) {
                    if ($row->userBank) {
                        $bankName = $row->userBank->bank->name ?? 'Bank';
                        $acc = $row->userBank->accountNumber ?? '';

                        return '<div class="text-start"><span class="badge bg-light text-dark border font-monospace fs-12"><i class="mdi mdi-bank me-1 text-primary"></i>'.htmlspecialchars($bankName).'</span><div class="text-muted fs-11 font-monospace mt-1">'.htmlspecialchars($acc).'</div></div>';
                    }

                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('paymentLabel', function ($row) use ($labels) {
                    $label = $labels[$row->id] ?? 'Pembayaran';

                    if (str_contains($label, 'Pelunasan')) {
                        $class = 'bg-success-subtle text-success border border-success-subtle';
                        $icon = 'mdi-check-circle';
                    } elseif ($label === 'DP') {
                        $class = 'bg-info-subtle text-info border border-info-subtle';
                        $icon = 'mdi-cash-100';
                    } else {
                        $class = 'bg-primary-subtle text-primary border border-primary-subtle';
                        $icon = 'mdi-cash-multiple';
                    }

                    return '<span class="badge '.$class.' rounded-pill px-2 py-1 fw-semibold fs-11"><i class="mdi '.$icon.' me-1"></i>'.htmlspecialchars($label).'</span>';
                })
                ->editColumn('amount', function ($row) {
                    return '<div class="text-end fw-bold text-success fs-13 font-monospace">Rp '.number_format((float) ($row->amount ?? 0), 0, ',', '.').'</div>';
                })
                ->editColumn('description', function ($row) {
                    if (! $row->description) {
                        return '<span class="text-muted">-</span>';
                    }

                    $desc = htmlspecialchars($row->description);

                    return '<div class="text-start" style="max-width: 220px;" title="'.$desc.'"><span class="fs-12 text-secondary">'.htmlspecialchars(Str::limit($row->description, 60)).'</span></div>';
                })
                ->addColumn('action', function ($row) {
                    $buttons = [];

                    if ($row->transaction) {
                        $url = route('invoice.payment-transaction.show', $row->transaction->id);
                        $buttons[] = '<a href="'.$url.'" class="btn btn-icon btn-sm bg-info-subtle text-info border border-info-subtle hover-scale" data-bs-toggle="tooltip" title="Detail Transaksi"><i class="mdi mdi-eye-outline fs-14"></i></a>';
                    }

                    if ($row->paymentReceipt) {
                        $receiptUrl = Storage::disk('public')->url('invoice-payment/'.$row->paymentReceipt);
                        $buttons[] = '<a href="'.$receiptUrl.'" target="_blank" class="btn btn-icon btn-sm bg-primary-subtle text-primary border border-primary-subtle hover-scale" data-bs-toggle="tooltip" title="Bukti Transfer"><i class="mdi mdi-file-download-outline fs-14"></i></a>';
                    }

                    if (empty($buttons)) {
                        return '<span class="text-muted fs-12">-</span>';
                    }

                    return '<div class="d-inline-flex align-items-center gap-1">'.implode('', $buttons).'</div>';
                })
                ->rawColumns(['DT_RowIndex', 'transactionCode', 'invoiceNumber', 'customerName', 'paymentDate', 'receivingBank', 'paymentLabel', 'amount', 'description', 'action'])
                ->toJson();
        }
    }

    /**
     * Export PDF daftar pembayaran (per pembayaran).
     */
    public function exportPdf(Request $request)
    {
        $filters = [
            'invoiceNumber' => $request->invoiceNumber,
            'customer_name' => $request->customerName,
        ];

        $relations = [
            'invoiceNumber' => 'invoice.invoiceNumber',
            'customer_name' => 'invoice.customer.name',
        ];

        $dateFilters = [
            'paymentDate' => [
                'start' => $request->startDate,
                'end' => $request->endDate,
            ],
        ];

        $query = InvoicePayment::with(['invoice.customer', 'userBank.bank'])
            ->orderBy('paymentDate', 'desc')
            ->orderBy('created_at', 'desc');

        $data = FilterHelper::applyFilters($query, $filters, $relations, $dateFilters)->get();

        // Label dihitung dari seluruh pembayaran agar urutan DP/cicilan tetap benar
        $labels = $this->service->paymentLabels($this->service->datatable());

        $mpdf = new Mpdf([
            'orientation' => 'P',
            'format' => 'A4',
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
                    ->with('labels', $labels)
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
        return Excel::download(new \App\Exports\InvoicePaymentExport($request), 'invoice-payment-list-'.Carbon::now()->format('Y-m-d H:i:s').'.xlsx');
    }
}
