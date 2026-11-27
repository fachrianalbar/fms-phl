<?php

namespace App\Exports;

use App\Http\Controllers\Report\FreightRateReportController;
use Carbon\Carbon;
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

class FreightRateReportExport implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    protected int $rowNumber = 0;

    /**
     * @param  array{customerCode: ?string, originLocationCode: ?string, destinationLocationCode: ?string, fleetCompanyId: ?string, startDate: ?string, endDate: ?string}  $filters
     */
    public function __construct(protected array $filters) {}

    public function collection(): Collection
    {
        return app(FreightRateReportController::class)
            ->filteredQuery($this->filters)
            ->orderBy('customer.name')
            ->orderBy('origin_loc.name')
            ->orderBy('dest_loc.name')
            ->orderBy('vendor.name')
            ->toBase()
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Pabrik',
            'Asal',
            'Tujuan',
            'Tgl Input',
            'Harga Pabrik',
            'Harga Vendor Pribadi',
            'Nama Vendor',
            'Harga Vendor External',
        ];
    }

    public function map($row): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $row->customerName ?: '-',
            $row->originName ?: '-',
            $row->destinationName ?: '-',
            $row->inputDate ? Carbon::parse($row->inputDate)->format('d-m-Y') : '-',
            (float) $row->price,
            (float) $row->personalVendorPrice,
            $row->vendorName ?: '-',
            (float) $row->externalVendorPrice,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => '#,##0',
            'G' => '#,##0',
            'I' => '#,##0',
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
