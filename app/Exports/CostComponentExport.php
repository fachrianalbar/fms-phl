<?php

namespace App\Exports;

use App\Models\Master\CostComponent;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CostComponentExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
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
        $query = CostComponent::query()->orderBy('name', 'asc');

        // Filter by name if provided
        if ($this->request->has('name') && $this->request->name) {
            $query->where('name', 'like', '%'.$this->request->name.'%');
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'No',
            'Code',
            'Name',
            'Price',
        ];
    }

    public function map($costComponent): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $costComponent->code,
            $costComponent->name,
            $costComponent->price ?: '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF4CAF50'],
                ],
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            ],
        ];
    }
}
