<?php

namespace App\Exports;

use App\Models\Master\FleetType;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FleetTypeExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
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
        $query = FleetType::query()->withCount('fleets')->orderBy('name');

        if ($this->request->filled('typeCode')) {
            $query->where('code', $this->request->typeCode);
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
            'Kode Tipe',
            'Nama Tipe',
            'Jumlah Armada Terdaftar',
            'Tanggal Dibuat',
        ];
    }

    public function map($type): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $type->code,
            $type->name,
            $type->fleets_count ?? 0,
            $type->created_at ? $type->created_at->format('d/m/Y H:i') : '-',
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
