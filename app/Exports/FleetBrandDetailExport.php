<?php

namespace App\Exports;

use App\Models\Master\Fleet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FleetBrandDetailExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    protected $brandCode;

    protected $request;

    protected $rowNumber = 0;

    public function __construct(string $brandCode, $request)
    {
        $this->brandCode = $brandCode;
        $this->request = $request;
    }

    public function query()
    {
        $query = Fleet::query()
            ->with(['type', 'company'])
            ->where('fleetBrandCode', $this->brandCode)
            ->orderBy('plateNumber');

        if ($this->request->filled('fleetTypeCode')) {
            $query->where('fleetTypeCode', $this->request->fleetTypeCode);
        }

        if ($this->request->filled('fleetCompanyCode')) {
            $query->where('fleetCompanyCode', $this->request->fleetCompanyCode);
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
            'Nomor Polisi (Plat)',
            'Kode Armada',
            'Tipe Armada',
            'Perusahaan Armada',
            'Tahun',
            'Nomor Mesin',
            'Nomor Rangka',
            'Tanggal Registrasi',
        ];
    }

    public function map($fleet): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $fleet->plateNumber ?? '-',
            $fleet->code ?? '-',
            $fleet->type->name ?? '-',
            $fleet->company->name ?? '-',
            $fleet->year ?? '-',
            $fleet->engineNumber ?? '-',
            $fleet->frameNumber ?? '-',
            $fleet->created_at ? $fleet->created_at->format('d/m/Y') : '-',
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
