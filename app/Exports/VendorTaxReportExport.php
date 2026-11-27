<?php

namespace App\Exports;

use App\Http\Controllers\Report\VendorTaxController;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VendorTaxReportExport extends DefaultValueBinder implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithCustomValueBinder, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    protected int $rowNumber = 0;

    /**
     * @param  array{fleetCompanyCode: ?string, taxStatus: ?string, startDate: ?string, endDate: ?string}  $filters
     */
    public function __construct(protected array $filters) {}

    public function collection(): Collection
    {
        return app(VendorTaxController::class)
            ->filteredQuery($this->filters)
            ->orderByDesc('notaDate')
            ->orderBy('notaNumber')
            ->toBase()
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal Nota Pembayaran',
            'NPWP',
            'Nama Vendor',
            'Tgl Pesanan',
            'Jenis Muatan',
            'Alamat',
            'No Nota Pembayaran',
            'Tonase',
            'OA',
            'Nominal Invoice/DPP',
            'No Faktur Pajak',
            'Tgl Bayar Nota Pembayaran',
            'No Bukti Potong',
            'Nominal Bukti Potong',
            'Tgl Bukti Potong',
        ];
    }

    public function map($row): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $row->notaDate ? Carbon::parse($row->notaDate)->format('d-m-Y') : '-',
            $row->npwp ?: '-',
            $row->vendorName ?: '-',
            $row->orderDate ? Carbon::parse($row->orderDate)->format('d-m-Y') : '-',
            $row->materialName ?: '-',
            $row->vendorAddress ?: '-',
            $row->notaNumber ?: '-',
            (float) $row->tonase,
            (float) $row->oa,
            (float) $row->dpp,
            $row->tax_invoice_number ?: '-',
            $row->lastPaymentDate ? Carbon::parse($row->lastPaymentDate)->format('d-m-Y') : '-',
            $row->withholding_tax_number ?: '-',
            $row->withholding_tax_amount !== null ? (float) $row->withholding_tax_amount : 0,
            $row->withholding_tax_date ? Carbon::parse($row->withholding_tax_date)->format('d-m-Y') : '-',
        ];
    }

    public function bindValue(Cell $cell, $value): bool
    {
        if (in_array($cell->getColumn(), ['C', 'H', 'L', 'N'], true) && $cell->getRow() > 1) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_TEXT,
            'I' => '#,##0.00',
            'J' => '#,##0',
            'K' => '#,##0',
            'L' => NumberFormat::FORMAT_TEXT,
            'N' => NumberFormat::FORMAT_TEXT,
            'O' => '#,##0',
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
