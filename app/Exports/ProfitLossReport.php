<?php

namespace App\Exports;

use App\Models\Data\TonaseBonus;
use App\Models\Master\Fleet;
use App\Services\Report\ProfitLossService;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class ProfitLossReport implements FromView, ShouldAutoSize, WithTitle
{
    use Exportable;

    protected $request;

    protected ProfitLossService $service;

    public function __construct($request, ?ProfitLossService $service = null)
    {
        $this->request = $request;
        $this->service = $service ?? app(ProfitLossService::class);
    }

    public function title(): string
    {
        return 'Laporan Laba Rugi';
    }

    public function view(): View
    {
        $request = $this->request;
        $startDate = $request->startDate ?? null;
        $endDate = $request->endDate ?? null;
        $plateNumber = $request->plateNumber ?? null;
        $fleetCompanyCode = $request->fleetCompanyCode ?? null;

        $exportData = $this->service->buildFleetExportData($plateNumber, $fleetCompanyCode, $startDate, $endDate);

        return view('report.profit-loss.report.profit-loss-excel')
            ->with('data', $exportData['fleets'])
            ->with('rows', $exportData['rows'])
            ->with('grandTotals', $exportData['grandTotals'])
            ->with('startDate', $startDate)
            ->with('endDate', $endDate);
    }
}
