<?php

namespace App\Exports;

use App\Services\Bank\UserBankService;
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

class UserBankExport implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    protected int $rowNumber = 0;

    public function __construct(
        protected array $filters,
        protected UserBankService $service
    ) {}

    public function collection(): Collection
    {
        $status = (string) ($this->filters['status'] ?? 'all');

        return $this->service->findAllFiltered($status, $this->filters);
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Bank',
            'No. Rekening',
            'Nama Pemilik Rekening',
            'Tipe Pemilik',
            'Tipe Rekening',
            'Saldo',
        ];
    }

    public function map($row): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $row['bank_name'] ?: '-',
            $row['accountNumber'] ?: '-',
            $row['accountName'] ?: '-',
            (int) ($row['type'] ?? 0) === 1 ? 'Person' : 'Company',
            strtolower((string) ($row['rekening_type'] ?? '')) === 'internal' ? 'Internal' : 'External',
            (float) ($row['balance'] ?? 0),
        ];
    }

    public function columnFormats(): array
    {
        return [
            'G' => '#,##0',
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
