<?php

namespace App\Exports;

use App\Models\Master\Customer;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomerExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
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
        $query = Customer::query()->with(['company'])->orderBy('name');

        if ($this->request->filled('companyCode')) {
            $query->where('companyCode', $this->request->companyCode);
        }

        if ($this->request->filled('type')) {
            $query->where('type', $this->request->type);
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
            'Email',
            'Perusahaan',
            'Tipe',
            'PPN (%)',
            'PPh 23 (%)',
            'Basis PPh Default',
            'Durasi Jatuh Tempo (Hari)',
        ];
    }

    public function map($customer): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $customer->code,
            $customer->name,
            $customer->email ?: '-',
            $customer->company->name ?? '-',
            $customer->type ?: '-',
            (float) ($customer->ppn ?? 0),
            (float) ($customer->pph ?? 0),
            $customer->pphBaseType === 'route' ? 'Tarif Rute Saja' : 'DPP Total (Tarif Rute + On Charge)',
            $customer->dueDateDuration ?? '-',
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
