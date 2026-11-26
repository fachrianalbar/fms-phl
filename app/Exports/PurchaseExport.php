<?php

namespace App\Exports;

use App\Services\Purchasing\PurchaseService;
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

class PurchaseExport implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    protected int $rowNumber = 0;

    public function __construct(
        protected array $filters,
        protected PurchaseService $service
    ) {}

    public function collection(): Collection
    {
        return $this->service->filteredDatatable($this->filters)->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Pembelian',
            'Tanggal',
            'Jatuh Tempo',
            'Supplier',
            'Gudang',
            'Total Tagihan',
            'Terbayar',
            'Sisa',
            'Status Pembayaran',
        ];
    }

    public function map($row): array
    {
        $this->rowNumber++;
        $total = (float) ($row->nominal ?? 0);
        if ($total <= 0 && $row->relationLoaded('details')) {
            $total = (float) $row->details->sum(fn ($d) => (float) $d->price * (float) ($d->qty ?: $d->receivedQty));
        }
        $paid = (float) ($row->paidAmount ?? 0);
        $remaining = max(0, $total - $paid);

        $statusLabel = match ($row->paymentStatus) {
            'Paid' => 'Lunas',
            'Partial' => 'Sebagian',
            default => 'Belum Bayar',
        };

        return [
            $this->rowNumber,
            $row->code ?: '-',
            $row->date ? Carbon::parse($row->date)->format('d-m-Y') : '-',
            $row->dueDate ? Carbon::parse($row->dueDate)->format('d-m-Y') : '-',
            $row->supplier->name ?? '-',
            $row->warehouse->name ?? '-',
            $total,
            $paid,
            $remaining,
            $statusLabel,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'G' => '#,##0',
            'H' => '#,##0',
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
