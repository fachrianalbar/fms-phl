<?php

namespace App\Exports;

use App\Models\Master\Customer;
use App\Models\Master\FleetCompany;
use App\Models\Operational\Order;
use App\Services\Report\OverallReportService;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class OverallReportExport implements FromView, ShouldAutoSize
{
    use Exportable;

    /**
     * @param  array{startDate: ?string, endDate: ?string, customerCode: ?string, fleetCompanyCode: ?string, factoryStatus: ?string, vendorStatus: ?string}  $filters
     */
    public function __construct(protected array $filters) {}

    public function view(): View
    {
        $service = app(OverallReportService::class);
        $rows = $service->filteredQuery($this->filters)
            ->orderByDesc('order.orderDate')
            ->orderByDesc('order.created_at')
            ->get()
            ->map(fn (Order $o) => $service->buildRow($o));

        $customerName = $this->filters['customerCode']
            ? Customer::query()->where('code', $this->filters['customerCode'])->value('name')
            : null;
        $fleetCompanyName = $this->filters['fleetCompanyCode']
            ? FleetCompany::query()->where('code', $this->filters['fleetCompanyCode'])->value('name')
            : null;

        return view('report.overall.report.overall-excel')
            ->with('rows', $rows)
            ->with('customerName', $customerName)
            ->with('fleetCompanyName', $fleetCompanyName)
            ->with('startDate', $this->filters['startDate'])
            ->with('endDate', $this->filters['endDate']);
    }
}
