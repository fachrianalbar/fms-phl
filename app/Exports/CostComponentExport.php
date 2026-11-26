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

        $keyword = $this->request->input('keyword') ?: $this->request->input('name');
        if (! empty($keyword)) {
            $term = strtolower(trim((string) $keyword));
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(code) LIKE ?', ['%'.$term.'%'])
                    ->orWhereRaw('LOWER(name) LIKE ?', ['%'.$term.'%']);
            });
        }

        if ($this->request->filled('type')) {
            $query->where('type', $this->request->input('type'));
        }

        if ($this->request->filled('startDate')) {
            $query->whereDate('created_at', '>=', $this->request->input('startDate'));
        }

        if ($this->request->filled('endDate')) {
            $query->whereDate('created_at', '<=', $this->request->input('endDate'));
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'No',
            'Code',
            'Name',
            'Type',
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
            $costComponent->type ? ucfirst((string) $costComponent->type) : '-',
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
