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

        $fleets = Fleet::with($this->service->fleetReportRelations($startDate, $endDate))
            ->when(! empty($plateNumber), function ($query) use ($plateNumber) {
                $query->where('plateNumber', $plateNumber);
            })
            ->when(! empty($fleetCompanyCode), function ($query) use ($fleetCompanyCode) {
                $query->where('fleetCompanyCode', $fleetCompanyCode);
            })
            ->orderBy('plateNumber')
            ->get();

        $tonaseBonuses = TonaseBonus::all();

        $grandTotals = [
            'basicSales' => 0.0,
            'orderCost' => 0.0,
            'maintenance' => 0.0,
            'tax' => 0.0,
            'netProfit' => 0.0,
        ];

        $rows = $fleets->map(function (Fleet $fleet) use ($tonaseBonuses, &$grandTotals) {
            $summary = $this->service->calculateFleetSummary($fleet, $tonaseBonuses);

            $grandTotals['basicSales'] += $summary['basicSales'];
            $grandTotals['orderCost'] += $summary['orderCost'];
            $grandTotals['maintenance'] += $summary['maintenance'];
            $grandTotals['tax'] += $summary['tax'];
            $grandTotals['netProfit'] += $summary['netProfit'];

            return [
                'fleet' => $fleet,
                'summary' => $summary,
            ];
        });

        return view('report.profit-loss.report.profit-loss-excel')
            ->with('data', $fleets)
            ->with('rows', $rows)
            ->with('grandTotals', $grandTotals)
            ->with('startDate', $startDate)
            ->with('endDate', $endDate);
    }
}
