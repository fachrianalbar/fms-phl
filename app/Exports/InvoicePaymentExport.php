<?php

namespace App\Exports;

use App\Helpers\FilterHelper;
use Carbon\Carbon;
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
            'customer_name' => 'customer.name',
        ];

        $dateFilters = [
            'invoiceDate' => [
                'start' => $this->request->startDate ?? null,
                'end' => $this->request->endDate ?? null,
            ],
        ];

        $query = \App\Models\Finance\Invoice::with(['details', 'payments.userBank.bank', 'customer'])
            ->whereHas('payments')
            ->orderBy('invoiceDate', 'desc');

        $data = FilterHelper::applyFilters($query, $filters, $relations, $dateFilters)->get();

        return view('finance.invoice-payment.report.invoice-payment-excel')
            ->with('data', $data);
    }
}
