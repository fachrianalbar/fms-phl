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

class FleetExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
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
        $query = Fleet::query()
            ->with(['brand', 'type', 'company', 'driver'])
            ->orderBy('plateNumber');

        if ($this->request->filled('fleetBrandCode')) {
            $query->where('fleetBrandCode', $this->request->fleetBrandCode);
        }

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
            'Merek Armada',
            'Tipe Armada',
            'Perusahaan Armada',
            'Tipe Kepemilikan',
            'Tahun',
            'Nomor Rangka',
            'Nomor Mesin',
            'Jatuh Tempo STNK',
            'Pajak Kendaraan',
            'KIR Kendaraan',
            'Driver / Pengemudi',
            'Tanggal Dibuat',
        ];
    }

    public function map($fleet): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $fleet->plateNumber ?? '-',
            $fleet->code ?? '-',
            $fleet->brand?->name ?? '-',
            $fleet->type?->name ?? '-',
            $fleet->company?->name ?? '-',
            $fleet->company?->type ?? '-',
            $fleet->year ?? '-',
            $fleet->frameNumber ?? '-',
            $fleet->engineNumber ?? '-',
            $fleet->vehicleRegistrationDueDate ? \Carbon\Carbon::parse($fleet->vehicleRegistrationDueDate)->format('d/m/Y') : '-',
            $fleet->vehicleTax ? \Carbon\Carbon::parse($fleet->vehicleTax)->format('d/m/Y') : '-',
            $fleet->vehicleKir ? \Carbon\Carbon::parse($fleet->vehicleKir)->format('d/m/Y') : '-',
            $fleet->driver?->name ?? '-',
            $fleet->created_at ? $fleet->created_at->format('d/m/Y H:i') : '-',
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
