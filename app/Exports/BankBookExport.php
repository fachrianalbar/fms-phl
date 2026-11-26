<?php

namespace App\Exports;

use App\Services\Bank\BankBookService;
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

class BankBookExport implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    protected int $rowNumber = 0;

    public function __construct(
        protected array $filters,
        protected BankBookService $service
    ) {}

    public function collection(): Collection
    {
        return $this->service->findAll($this->filters);
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Rekening',
            'Bank',
            'No. Rekening',
            'Tipe',
            'Debit (In)',
            'Kredit (Out)',
            'Saldo',
        ];
    }

    public function map($row): array
    {
        $this->rowNumber++;
        $typeLabel = '-';
        if (isset($row->userBank->type)) {
            $typeLabel = (int) $row->userBank->type === 1 ? 'Person' : 'Company';
        }

        return [
            $this->rowNumber,
            $row->userBank->accountName ?? '-',
            $row->userBank->bank->name ?? '-',
            $row->userBank->accountNumber ?? '-',
            $typeLabel,
            (float) ($row->debit ?? 0),
            (float) ($row->credit ?? 0),
            (float) ($row->balance ?? 0),
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => '#,##0',
            'G' => '#,##0',
            'H' => '#,##0',
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
