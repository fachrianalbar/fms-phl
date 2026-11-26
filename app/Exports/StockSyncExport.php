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

class StockSyncExport implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    protected int $rowNumber = 0;

    public function __construct(protected array $audit) {}

    public function collection(): Collection
    {
        $rows = collect();

        foreach ($this->audit['missing'] ?? [] as $row) {
            $rows->push([
                'issue' => 'Missing',
                'source' => $row['source'] ?? '-',
                'transactionCode' => $row['transactionCode'] ?? '-',
                'transactionDetailCode' => $row['transactionDetailCode'] ?? '-',
                'itemCode' => $row['itemCode'] ?? '-',
                'itemName' => $row['itemName'] ?? '-',
                'warehouse' => ($row['warehouseName'] ?? '-').' ('.($row['warehouseCode'] ?? '-').')',
                'qtyIn' => (float) ($row['qtyIn'] ?? 0),
                'qtyOut' => (float) ($row['qtyOut'] ?? 0),
                'note' => 'Belum ada di stock_transaction',
            ]);
        }

        foreach ($this->audit['mismatched'] ?? [] as $row) {
            $rows->push([
                'issue' => 'Tidak Sinkron',
                'source' => $row['source'] ?? '-',
                'transactionCode' => $row['transactionCode'] ?? '-',
                'transactionDetailCode' => $row['transactionDetailCode'] ?? '-',
                'itemCode' => $row['itemCode'] ?? '-',
                'itemName' => $row['itemName'] ?? '-',
                'warehouse' => ($row['currentWarehouseCode'] ?? '-').' -> '.($row['warehouseCode'] ?? '-'),
                'qtyIn' => (float) ($row['qtyIn'] ?? 0),
                'qtyOut' => (float) ($row['qtyOut'] ?? 0),
                'note' => 'Saat ini IN '.($row['currentQtyIn'] ?? 0).', OUT '.($row['currentQtyOut'] ?? 0),
            ]);
        }

        foreach ($this->audit['orphans'] ?? [] as $row) {
            $rows->push([
                'issue' => 'Orphan',
                'source' => $row['source'] ?? ($row['transactionType'] ?? '-'),
                'transactionCode' => $row['transactionCode'] ?? '-',
                'transactionDetailCode' => $row['transactionDetailCode'] ?? '-',
                'itemCode' => $row['itemCode'] ?? '-',
                'itemName' => $row['itemName'] ?? '-',
                'warehouse' => ($row['warehouseName'] ?? '-').' ('.($row['warehouseCode'] ?? '-').')',
                'qtyIn' => (float) ($row['qtyIn'] ?? 0),
                'qtyOut' => (float) ($row['qtyOut'] ?? 0),
                'note' => 'Transaksi sumber tidak ditemukan',
            ]);
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'No',
            'Kategori Masalah',
            'Sumber',
            'Transaction Code',
            'Detail Code',
            'Kode Barang',
            'Nama Barang',
            'Gudang',
            'Qty Masuk',
            'Qty Keluar',
            'Keterangan',
        ];
    }

    public function map($row): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $row['issue'],
            $row['source'],
            $row['transactionCode'],
            $row['transactionDetailCode'],
            $row['itemCode'],
            $row['itemName'],
            $row['warehouse'],
            $row['qtyIn'],
            $row['qtyOut'],
            $row['note'],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'I' => '#,##0.##',
            'J' => '#,##0.##',
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
