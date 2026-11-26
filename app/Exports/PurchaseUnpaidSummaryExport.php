<?php

namespace App\Exports;

use App\Services\Purchasing\PurchasePaymentService;
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

class PurchaseUnpaidSummaryExport implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    protected int $rowNumber = 0;

    public function __construct(
        protected array $filters,
        protected PurchasePaymentService $service
    ) {}

    public function collection(): Collection
    {
        return $this->service->filteredUnpaidBySupplier($this->filters)->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Supplier',
            'Nama Supplier',
            'Jumlah PO',
            'Belum Bayar',
            'Belum Lunas (DP)',
            'Total Tagihan',
            'Terbayar',
            'Sisa Hutang',
        ];
    }

    public function map($row): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $row->supplierCode ?: '-',
            $row->supplierName ?: '-',
            (int) ($row->poCount ?? 0),
            (int) ($row->unpaidCount ?? 0),
            (int) ($row->partialCount ?? 0),
            (float) ($row->totalBilling ?? 0),
            (float) ($row->totalPaid ?? 0),
            max(0, (float) ($row->totalRemaining ?? 0)),
        ];
    }

    public function columnFormats(): array
    {
        return [
            'G' => '#,##0',
            'H' => '#,##0',
            'I' => '#,##0',
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
