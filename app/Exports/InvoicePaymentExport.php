<?php

namespace App\Exports;

use App\Helpers\FilterHelper;
use App\Models\Finance\InvoicePayment;
use App\Services\Finance\InvoicePaymentService;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class InvoicePaymentExport implements FromView, ShouldAutoSize
{
    use Exportable;

    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function view(): View
    {
        $filters = [
            'invoiceNumber' => $this->request->invoiceNumber ?? null,
            'customer_name' => $this->request->customerName ?? null,
        ];

        $relations = [
            'invoiceNumber' => 'invoice.invoiceNumber',
            'customer_name' => 'invoice.customer.name',
        ];

        $dateFilters = [
            'paymentDate' => [
                'start' => $this->request->startDate ?? null,
                'end' => $this->request->endDate ?? null,
            ],
        ];

        $query = InvoicePayment::with(['invoice.customer', 'userBank.bank'])
            ->orderBy('paymentDate', 'desc')
            ->orderBy('created_at', 'desc');

        $data = FilterHelper::applyFilters($query, $filters, $relations, $dateFilters)->get();

        // Label dihitung dari seluruh pembayaran agar urutan DP/cicilan tetap benar
        $service = app(InvoicePaymentService::class);
        $labels = $service->paymentLabels($service->datatable());

        return view('finance.invoice-payment.report.invoice-payment-excel')
            ->with('data', $data)
            ->with('labels', $labels);
    }
}
