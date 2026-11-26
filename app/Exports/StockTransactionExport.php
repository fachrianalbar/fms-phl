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

class StockTransactionExport implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    protected int $rowNumber = 0;

    public function __construct(protected array $reportData) {}

    public function collection(): Collection
    {
        $rows = collect();

        foreach ($this->reportData as $warehouseBlock) {
            $warehouseLabel = trim(($warehouseBlock['warehouse']->code ?? '').' - '.($warehouseBlock['warehouse']->name ?? '-'), ' -');

            foreach ($warehouseBlock['details'] as $detail) {
                $rows->push([
                    'warehouse' => $warehouseLabel,
                    'date' => $detail['date'] ?? '-',
                    'itemCode' => $detail['itemCode'] ?? '-',
                    'itemName' => $detail['itemName'] ?? '-',
                    'transactionCode' => $detail['transactionCode'] ?? '-',
                    'transactionType' => $detail['transactionType'] ?? '-',
                    'qtyIn' => (float) ($detail['qtyIn'] ?? 0),
                    'qtyOut' => (float) ($detail['qtyOut'] ?? 0),
                ]);
            }
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'No',
            'Gudang',
            'Tanggal',
            'Kode Barang',
            'Nama Barang',
            'No. Transaksi',
            'Jenis Transaksi',
            'Qty Masuk',
            'Qty Keluar',
        ];
    }

    public function map($row): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $row['warehouse'],
            $row['date'],
            $row['itemCode'],
            $row['itemName'],
            $row['transactionCode'],
            $row['transactionType'],
            $row['qtyIn'],
            $row['qtyOut'],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'H' => '#,##0.##',
            'I' => '#,##0.##',
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
