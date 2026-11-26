<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DriverSalaryExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    protected int $rowNumber = 0;

    public function __construct(protected Builder $query) {}

    public function collection(): Collection
    {
        return $this->query->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Gaji',
            'Kode Supir',
            'Nama Supir',
            'Periode Mulai',
            'Periode Selesai',
            'Gaji Order',
            'Penyesuaian',
            'Grand Total',
            'Catatan',
        ];
    }

    public function map($row): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $row->code ?: '-',
            $row->driverCode ?: '-',
            $row->driver?->name ?? '-',
            $row->startDate ? Carbon::parse($row->startDate)->format('d-m-Y') : '-',
            $row->endDate ? Carbon::parse($row->endDate)->format('d-m-Y') : '-',
            (float) ($row->totalSalary ?? 0),
            (float) ($row->totalAdjustment ?? 0),
            (float) ($row->grandTotal ?? 0),
            $row->notes ?: '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF4F46E5'],
                ],
            ],
        ];
    }
}
