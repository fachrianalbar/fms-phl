<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class MaintenanceCompanyInternalReport implements FromView, ShouldAutoSize, WithTitle
{
    use Exportable;

    public function __construct(
        protected Collection $rows,
        protected ?string $fleetCompanyName = null,
        protected ?string $startDate = null,
        protected ?string $endDate = null
    ) {}

    public function title(): string
    {
        return 'Perawatan Perusahaan';
    }

    public function view(): View
    {
        return view('report.maintenance-company-internal.report.maintenance-company-excel', [
            'rows' => $this->rows,
            'fleetCompanyName' => $this->fleetCompanyName,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ]);
    }
}
