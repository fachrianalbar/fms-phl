<?php

namespace App\Exports;

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
        $service = app(InvoicePaymentService::class);
        $filters = is_array($this->request) ? $this->request : $this->request->all();
        $data = $service->datatable($filters);

        // Label dihitung dari seluruh pembayaran agar urutan DP/cicilan tetap benar
        $labels = $service->paymentLabels($service->datatable());

        return view('finance.invoice-payment.report.invoice-payment-excel')
            ->with('data', $data)
            ->with('labels', $labels);
    }
}
