<?php

namespace App\Exports;

use App\Models\Master\Fleet;
use App\Models\Master\FleetCompany;
use App\Services\Report\MaintenancePaymentStatusService;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class MaintenanceFleetReport implements FromView, ShouldAutoSize, WithColumnFormatting
{
    use Exportable;

    public function columnFormats(): array
    {
        return [
            'E' => '#,##0',
            'F' => '#,##0.0',
            'G' => '#,##0',
            'H' => '#,##0',
            'I' => '#,##0',
        ];
    }

    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function view(): View
    {
        $request = $this->request;

        $rows = app(MaintenancePaymentStatusService::class)
            ->fleetSummaryQuery([
                'fleetCode' => $request->input('fleetCode') ?: null,
                'fleetCompanyCode' => $request->input('fleetCompanyCode') ?: null,
                'startDate' => $request->input('startDate') ?: null,
                'endDate' => $request->input('endDate') ?: null,
            ])
            ->orderBy('fleet.plateNumber')
            ->toBase()
            ->get();

        $fleetName = $request->filled('fleetCode')
            ? Fleet::query()->where('code', $request->fleetCode)->value('plateNumber')
            : null;

        $fleetCompanyName = $request->filled('fleetCompanyCode')
            ? FleetCompany::query()->where('code', $request->fleetCompanyCode)->value('name')
            : null;

        return view('report.maintenance-fleet.report.maintenance-fleet-excel')
            ->with('rows', $rows)
            ->with('fleetName', $fleetName)
            ->with('fleetCompanyName', $fleetCompanyName)
            ->with('startDate', $request->startDate)
            ->with('endDate', $request->endDate);
    }
}
