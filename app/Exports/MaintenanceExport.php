<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MaintenanceExport implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    protected int $rowNumber = 0;

    public function __construct(protected Collection $maintenances) {}

    public function collection(): Collection
    {
        return $this->maintenances;
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Maintenance',
            'No. PO',
            'Tanggal',
            'Jam',
            'Armada',
            'Pemilik Kendaraan',
            'Gudang',
            'Item Digunakan',
            'Total Biaya',
        ];
    }

    public function map($row): array
    {
        $this->rowNumber++;
        $poCodes = $row->purchases && $row->purchases->isNotEmpty()
            ? $row->purchases->pluck('code')->implode(', ')
            : '-';

        $itemsText = $row->details && $row->details->isNotEmpty()
            ? $row->details->map(function ($d) {
                $name = $d->item->name ?? $d->itemCode;
                $qty = rtrim(rtrim(number_format((float) $d->qty, 1, ',', '.'), '0'), ',');

                return $name.' ('.$qty.')';
            })->implode('; ')
            : '-';

        return [
            $this->rowNumber,
            $row->code ?: '-',
            $poCodes,
            $row->date ? Carbon::parse($row->date)->format('d-m-Y') : '-',
            $row->time ? Carbon::parse($row->time)->format('H:i') : '-',
            $row->fleet->plateNumber ?? '-',
            $row->fleet?->company?->name ?? '-',
            $row->warehouse->name ?? '-',
            $itemsText,
            (float) ($row->grand_total ?? 0),
        ];
    }

    public function columnFormats(): array
    {
        return [
            'J' => '#,##0',
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
