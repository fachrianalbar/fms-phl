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
            'Nopol',
            'Pemilik Kendaraan',
            'Gudang',
            'Subtotal',
            'PPN (%)',
            'Nominal PPN',
            'Jumlah Setelah PPN',
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
        $subtotal = (float) ($row->subtotal ?? 0);
        if ($subtotal <= 0) {
            $subtotal = $total;
        }
        $ppnRate = (float) ($row->ppn_rate ?? 0);
        $ppnAmount = (float) ($row->ppn_amount ?? 0);
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
            $row->fleet->plateNumber ?? '-',
            $row->fleet->company->name ?? '-',
            $row->warehouse->name ?? '-',
            $subtotal,
            $ppnRate,
            $ppnAmount,
            $total,
            $paid,
            $remaining,
            $statusLabel,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'I' => '#,##0',
            'K' => '#,##0',
            'L' => '#,##0',
            'M' => '#,##0',
            'N' => '#,##0',
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
