<?php

namespace App\Exports;

use App\Services\Purchasing\DirectPurchaseService;
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

class DirectPurchaseExport implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    protected int $rowNumber = 0;

    public function __construct(
        protected array $filters,
        protected DirectPurchaseService $service
    ) {}

    public function collection(): Collection
    {
        return $this->service->filteredDatatable($this->filters)->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'No. Transaksi',
            'Tanggal',
            'Jam',
            'Armada',
            'Supplier / Toko',
            'No. Maintenance',
            'Jenis Bayar',
            'Status Bayar',
            'Jumlah Item',
            'Total Qty',
            'Total Biaya',
        ];
    }

    public function map($row): array
    {
        $this->rowNumber++;
        $total = (float) ($row->nominal ?: $row->details->sum(fn ($d) => (float) $d->price * (float) $d->qty));
        $mnt = $row->maintenances->first();
        $statusLabel = match ($row->paymentStatus) {
            'Paid' => 'Lunas',
            'Partial' => 'Dibayar Sebagian',
            default => 'Belum Dibayar',
        };

        return [
            $this->rowNumber,
            $row->code ?: '-',
            $row->date ? Carbon::parse($row->date)->format('d-m-Y') : '-',
            $row->time ? Carbon::parse($row->time)->format('H:i') : '-',
            $row->fleet->plateNumber ?? '-',
            $row->supplier->name ?? '-',
            $mnt->code ?? '-',
            $row->paymentType ?: 'Tunai',
            $statusLabel,
            (int) $row->details->count(),
            (float) $row->details->sum('qty'),
            $total,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'L' => '#,##0',
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
