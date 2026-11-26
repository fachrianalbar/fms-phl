<?php

namespace App\Exports;

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

class StockExport implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    protected int $rowNumber = 0;

    public function __construct(protected Collection $stocks) {}

    public function collection(): Collection
    {
        return $this->stocks;
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Barang',
            'Nama Barang',
            'Gudang',
            'Total Masuk',
            'Total Keluar',
            'Stok Saat Ini',
        ];
    }

    public function map($row): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $row->itemCode ?: '-',
            $row->itemName ?? ($row->item->name ?? '-'),
            $row->warehouseName ?? ($row->warehouse->name ?? $row->warehouseCode ?? '-'),
            (float) ($row->totalIn ?? 0),
            (float) ($row->totalOut ?? 0),
            (float) ($row->stock ?? 0),
        ];
    }

    public function columnFormats(): array
    {
        return [
            'E' => '#,##0.##',
            'F' => '#,##0.##',
            'G' => '#,##0.##',
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
