<?php

namespace App\Exports;

use App\Models\Master\CostComponentPriceLog;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CostComponentPriceLogExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
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
        $query = CostComponentPriceLog::query()->orderBy('created_at', 'desc');

        // Filter by cost component code if provided
        if ($this->request->has('costComponentCode') && $this->request->costComponentCode) {
            $query->where('costComponentCode', $this->request->costComponentCode);
        }

        // Filter by date range if provided
        if ($this->request->has('startDate') && $this->request->startDate) {
            $query->whereDate('created_at', '>=', $this->request->startDate);
        }

        if ($this->request->has('endDate') && $this->request->endDate) {
            $query->whereDate('created_at', '<=', $this->request->endDate);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'No',
            'Date',
            'Cost Component Code',
            'Cost Component Name',
            'Old Price',
            'New Price',
            'Changed By',
            'Notes',
        ];
    }

    public function map($log): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $log->created_at ? $log->created_at->format('d-m-Y H:i:s') : '-',
            $log->costComponentCode,
            $log->costComponentName,
            $log->oldPrice ?: 0,
            $log->newPrice ?: 0,
            $log->changedBy ?: '-',
            $log->notes ?: '-',
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
