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

class ActivityLogExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
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
            'Activity',
            'Action',
            'User',
            'Role',
            'Data',
            'Tanggal',
        ];
    }

    public function map($row): array
    {
        $this->rowNumber++;
        $properties = is_string($row->properties)
            ? $row->properties
            : json_encode($row->properties, JSON_UNESCAPED_UNICODE);

        return [
            $this->rowNumber,
            $row->log_name ?: '-',
            $row->description ?: '-',
            $row->user?->name ?? '-',
            $row->user?->role?->name ?? '-',
            $properties ?: '-',
            $row->created_at ? Carbon::parse($row->created_at)->format('d-m-Y H:i') : '-',
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
