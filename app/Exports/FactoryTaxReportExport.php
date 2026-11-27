<?php

namespace App\Exports;

use App\Http\Controllers\Report\FactoryTaxController;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FactoryTaxReportExport extends DefaultValueBinder implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithCustomValueBinder, WithEvents, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    protected int $rowNumber = 0;

    protected float $totalTonase = 0;

    protected float $totalDpp = 0;

    protected float $totalDppLain = 0;

    protected float $totalPpn = 0;

    protected float $totalWithholding = 0;

    /**
     * @param  array{customerCode: ?string, taxStatus: ?string, startDate: ?string, endDate: ?string}  $filters
     */
    public function __construct(protected array $filters) {}

    public function collection(): Collection
    {
        return app(FactoryTaxController::class)
            ->filteredQuery($this->filters)
            ->orderByDesc('invoice.invoiceDate')
            ->orderBy('invoice.invoiceNumber')
            ->toBase()
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal Cetak Invoice',
            'NPWP',
            'Nama Pabrik',
            'Tgl Pesanan',
            'Jenis Muatan',
            'Alamat',
            'No Invoice',
            'Tonase',
            'OA',
            'Nominal Invoice/DPP',
            'DPP Lain',
            'PPN',
            'No Faktur Pajak',
            'Tgl Bayar Invoice',
            'No Bukti Potong',
            'Nominal Bukti Potong',
            'Tgl Bukti Potong',
        ];
    }

    public function map($row): array
    {
        $this->rowNumber++;

        $tonase = (float) $row->tonase;
        $dpp = (float) $row->dpp;
        $dppLain = round($dpp * 11 / 12, 2);
        $ppn = round($dppLain * 0.12, 2);
        $withholding = $row->withholding_tax_amount !== null ? (float) $row->withholding_tax_amount : 0.0;

        $this->totalTonase += $tonase;
        $this->totalDpp += $dpp;
        $this->totalDppLain += $dppLain;
        $this->totalPpn += $ppn;
        $this->totalWithholding += $withholding;

        return [
            $this->rowNumber,
            $row->invoiceDate ? Carbon::parse($row->invoiceDate)->format('d-m-Y') : '-',
            $row->npwp ?: '-',
            $row->customerName ?: '-',
            FactoryTaxController::formatOrderDateRange($row->minOrderDate, $row->maxOrderDate),
            $row->materials ?: '-',
            $row->customerAddress ?: '-',
            $row->invoiceNumber ?: ($row->code ?: '-'),
            $tonase,
            (float) $row->oa,
            $dpp,
            $dppLain,
            $ppn,
            $row->tax_invoice_number ?: '-',
            $row->lastPaymentDate ? Carbon::parse($row->lastPaymentDate)->format('d-m-Y') : '-',
            $row->withholding_tax_number ?: '-',
            $withholding,
            $row->withholding_tax_date ? Carbon::parse($row->withholding_tax_date)->format('d-m-Y') : '-',
        ];
    }

    public function bindValue(Cell $cell, $value): bool
    {
        if (in_array($cell->getColumn(), ['C', 'H', 'N', 'P'], true) && $cell->getRow() > 1) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_TEXT,
            'H' => NumberFormat::FORMAT_TEXT,
            'I' => '#,##0.00',
            'J' => '#,##0',
            'K' => '#,##0',
            'L' => '#,##0.00',
            'M' => '#,##0.00',
            'N' => NumberFormat::FORMAT_TEXT,
            'P' => NumberFormat::FORMAT_TEXT,
            'Q' => '#,##0',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $totalRow = $this->rowNumber + 2;

                $sheet->setCellValue("H{$totalRow}", 'GRAND TOTAL');
                $sheet->setCellValue("I{$totalRow}", $this->totalTonase);
                $sheet->setCellValue("K{$totalRow}", $this->totalDpp);
                $sheet->setCellValue("L{$totalRow}", $this->totalDppLain);
                $sheet->setCellValue("M{$totalRow}", $this->totalPpn);
                $sheet->setCellValue("Q{$totalRow}", $this->totalWithholding);

                $sheet->getStyle("A{$totalRow}:R{$totalRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFE2E8F0'],
                    ],
                ]);
                $sheet->getStyle("I{$totalRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle("K{$totalRow}")->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle("L{$totalRow}:M{$totalRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle("Q{$totalRow}")->getNumberFormat()->setFormatCode('#,##0');
            },
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
