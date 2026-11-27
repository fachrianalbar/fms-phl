<?php

namespace App\Exports;

use App\Models\Inventory\Supplier;
use App\Models\Master\Fleet;
use App\Models\Master\FleetCompany;
use App\Services\Report\MaintenancePaymentStatusService;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class MaintenanceSupplierDetailReport implements FromView, ShouldAutoSize
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
        $supplierCode = (string) $request->supplierCode;
        $service = app(MaintenancePaymentStatusService::class);

        $supplier = Supplier::query()
            ->where('code', $supplierCode)
            ->whereNull('deleted_at')
            ->firstOrFail();

        $filters = [
            'fleetCompanyCode' => $request->input('fleetCompanyCode') ?: null,
            'fleetCode' => $request->input('fleetCode') ?: null,
            'startDate' => $request->input('startDate') ?: null,
            'endDate' => $request->input('endDate') ?: null,
        ];

        $query = $service->supplierDetailQuery($supplierCode, $filters);
        $summary = $service->summarize($query);
        $rows = $service->orderLines(
            $query->select(array_merge(
                $service->lineSelects(),
                [
                    'fleet_company.name as fleetCompanyName',
                    'fleet.plateNumber',
                ]
            ))
        )->toBase()->get();

        $fleetCompanyName = $filters['fleetCompanyCode']
            ? FleetCompany::query()->where('code', $filters['fleetCompanyCode'])->value('name')
            : null;

        $fleetPlate = $filters['fleetCode']
            ? Fleet::query()->where('code', $filters['fleetCode'])->value('plateNumber')
            : null;

        return view('report.maintenance-supplier.report.maintenance-supplier-detail-excel')
            ->with('supplier', $supplier)
            ->with('rows', $rows)
            ->with('summary', $summary)
            ->with('fleetCompanyName', $fleetCompanyName)
            ->with('fleetPlate', $fleetPlate)
            ->with('startDate', $filters['startDate'])
            ->with('endDate', $filters['endDate']);
    }
}
