<?php

namespace App\Exports;

use App\Services\Report\OmsetReportService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class OmsetReportExport implements FromView, ShouldAutoSize
{
    public function __construct(
        protected Request $request,
        protected OmsetReportService $service
    ) {}

    public function view(): View
    {
        $rows = $this->service->buildQuery($this->request)
            ->orderByDesc('o.orderDate')
            ->orderByDesc('o.code')
            ->get();

        $totals = $this->service->computeTotals($this->request);

        $periodLabel = ($this->request->filled('startDate') ? Carbon::parse($this->request->startDate)->format('d-m-Y') : 'Awal')
            . ' s/d '
            . ($this->request->filled('endDate') ? Carbon::parse($this->request->endDate)->format('d-m-Y') : 'Sekarang');

        return view('report.omset.report.omset-excel', [
            'rows' => $rows,
            'totals' => $totals,
            'periodLabel' => $periodLabel,
        ]);
    }
}
