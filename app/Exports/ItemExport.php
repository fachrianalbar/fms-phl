<?php

namespace App\Exports;

use App\Models\Inventory\Item;
use Illuminate\Database\Eloquent\Builder;
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

class ItemExport implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    protected int $rowNumber = 0;

    public function __construct(
        protected Builder $query,
        protected string $entityLabel = 'Barang',
        protected string $itemType = Item::TYPE_PART
    ) {}

    public function collection(): Collection
    {
        return $this->query->get();
    }

    public function headings(): array
    {
        if ($this->itemType === Item::TYPE_JASA) {
            return [
                'No',
                'Kode '.$this->entityLabel,
                'Nama '.$this->entityLabel,
                'Kategori',
                'Satuan',
                'Supplier',
                'Harga',
            ];
        }

        return [
            'No',
            'Kode '.$this->entityLabel,
            'Nama '.$this->entityLabel,
            'Kategori',
            'Satuan',
            'Gudang',
            'Supplier',
            'Harga',
        ];
    }

    public function map($row): array
    {
        $this->rowNumber++;

        if ($this->itemType === Item::TYPE_JASA) {
            return [
                $this->rowNumber,
                $row->code ?: '-',
                $row->name ?: '-',
                $row->category->name ?? '-',
                $row->unit->name ?? '-',
                $row->supplier->name ?? '-',
                (float) ($row->price ?? 0),
            ];
        }

        return [
            $this->rowNumber,
            $row->code ?: '-',
            $row->name ?: '-',
            $row->category->name ?? '-',
            $row->unit->name ?? '-',
            $row->warehouse->name ?? '-',
            $row->supplier->name ?? '-',
            (float) ($row->price ?? 0),
        ];
    }

    public function columnFormats(): array
    {
        return [
            ($this->itemType === Item::TYPE_JASA ? 'G' : 'H') => '#,##0',
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
