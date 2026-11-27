<?php

namespace App\Exports;

use App\Models\Inventory\Supplier;
use App\Services\Report\MaintenancePaymentStatusService;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class MaintenanceSupplierReport implements FromView, ShouldAutoSize
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
        $supplierCode = $request->input('supplierCode') ?: null;

        $rows = app(MaintenancePaymentStatusService::class)
            ->supplierSummaryQuery([
                'supplierCode' => $supplierCode,
                'startDate' => $request->input('startDate') ?: null,
                'endDate' => $request->input('endDate') ?: null,
            ])
            ->orderBy('supplier.name')
            ->toBase()
            ->get();

        $supplierName = $supplierCode
            ? Supplier::query()->where('code', $supplierCode)->value('name')
            : null;

        return view('report.maintenance-supplier.report.maintenance-supplier-excel')
            ->with('rows', $rows)
            ->with('supplierName', $supplierName)
            ->with('startDate', $request->startDate)
            ->with('endDate', $request->endDate);
    }
}
