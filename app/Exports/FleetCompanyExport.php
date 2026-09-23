<?php

namespace App\Exports;

use App\Models\Master\FleetCompany;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FleetCompanyExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
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
        $query = FleetCompany::query()->withCount('fleets')->orderBy('name');

        if ($this->request->filled('companyCode')) {
            $query->where('code', $this->request->companyCode);
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
            'Kode Perusahaan',
            'Nama Perusahaan',
            'Tipe',
            'No. Rekening',
            'Nama Bank',
            'PPh (%)',
            'Jumlah Armada Terdaftar',
            'Tanggal Dibuat',
        ];
    }

    public function map($company): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $company->code,
            $company->name,
            $company->type ?? '-',
            $company->accountNumber ?? '-',
            $company->bankName ?? '-',
            $company->pph ? number_format($company->pph, 2, ',', '.') . '%' : '0,00%',
            $company->fleets_count ?? 0,
            $company->created_at ? $company->created_at->format('d/m/Y H:i') : '-',
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
