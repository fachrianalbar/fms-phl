<?php

namespace App\Exports;

use App\Http\Controllers\Report\ItemPriceReportController;
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

class ItemPriceReportExport implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    protected int $rowNumber = 0;

    /**
     * @param  array{supplierCode: ?string, type: ?string, startDate: ?string, endDate: ?string}  $filters
     */
    public function __construct(protected array $filters) {}

    public function collection(): Collection
    {
        return app(ItemPriceReportController::class)
            ->filteredQuery($this->filters)
            ->orderBy('supplier.name')
            ->orderBy('item.name')
            ->toBase()
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Suplier',
            'Item Barang',
            'Tgl Input',
            'Harga Item',
        ];
    }

    public function map($row): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $row->supplierName ?: '-',
            $row->itemName ?: '-',
            $row->inputDate ? Carbon::parse($row->inputDate)->format('d-m-Y') : '-',
            (float) $row->price,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'E' => '#,##0',
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
