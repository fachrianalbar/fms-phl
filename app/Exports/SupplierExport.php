<?php

namespace App\Exports;

use App\Models\Inventory\Supplier;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SupplierExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    protected $request;

    protected $rowNumber = 0;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = Supplier::query()->orderBy('code', 'asc');

        if ($this->request->filled('supplierCode')) {
            $query->where('code', $this->request->supplierCode);
        }

        if ($this->request->filled('startDate')) {
            $query->whereDate('created_at', '>=', $this->request->startDate);
        }

        if ($this->request->filled('endDate')) {
            $query->whereDate('created_at', '<=', $this->request->endDate);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode',
            'Nama',
            'Alamat',
            'PIC',
            'Telepon',
            'Email',
            'PPN (%)',
            'PPH (%)',
            'Dibuat Pada',
        ];
    }

    public function map($supplier): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $supplier->code,
            $supplier->name,
            $supplier->address ?: '-',
            $supplier->pic ?: '-',
            $supplier->phone ?: '-',
            $supplier->email ?: '-',
            $supplier->ppn !== null && $supplier->ppn !== '' ? (string) $supplier->ppn : '-',
            $supplier->pph !== null && $supplier->pph !== '' ? (string) $supplier->pph : '-',
            $supplier->created_at ? $supplier->created_at->format('d/m/Y H:i') : '-',
        ];
    }

    public function styles(Worksheet $sheet)
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
