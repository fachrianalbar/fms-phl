<?php

namespace App\Exports;

use App\Models\Inventory\Supplier;
use App\Models\Master\Fleet;
use App\Services\Report\MaintenancePaymentStatusService;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class MaintenanceFleetDetailReport implements FromView, ShouldAutoSize
{
    use Exportable;

    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function view(): View
    {
        $request = $this->request;
        $fleetCode = (string) $request->fleetCode;
        $service = app(MaintenancePaymentStatusService::class);

        $fleet = Fleet::query()
            ->with(['company'])
            ->where('code', $fleetCode)
            ->whereNull('deleted_at')
            ->firstOrFail();

        $filters = [
            'supplierCode' => $request->input('supplierCode') ?: null,
            'startDate' => $request->input('startDate') ?: null,
            'endDate' => $request->input('endDate') ?: null,
        ];

        $query = $service->fleetDetailQuery($fleetCode, $filters);
        $summary = $service->summarize($query);
        $rows = $service->orderLines($query->select($service->lineSelects()))->toBase()->get();

        return view('report.maintenance-fleet.report.maintenance-fleet-detail-excel')
            ->with('fleet', $fleet)
            ->with('rows', $rows)
            ->with('summary', $summary)
            ->with('supplierName', $filters['supplierCode'] ? Supplier::query()->where('code', $filters['supplierCode'])->value('name') : null)
            ->with('startDate', $filters['startDate'])
            ->with('endDate', $filters['endDate']);
    }
}
