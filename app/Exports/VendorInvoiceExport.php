<?php

namespace App\Exports;

use App\Services\Finance\VendorPaymentService;
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

class VendorInvoiceExport implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    protected int $rowNumber = 0;

    public function __construct(
        protected array $filters,
        protected string $type,
        protected VendorPaymentService $service
    ) {}

    public function collection(): Collection
    {
        return match ($this->type) {
            'partial' => $this->service->findPartialNotas($this->filters),
            'paid' => $this->service->findPaidNotas($this->filters),
            default => $this->service->findPendingNotas($this->filters),
        };
    }

    public function headings(): array
    {
        if ($this->type === 'paid') {
            return [
                'No',
                'No. Nota',
                'Tanggal Nota',
                'Vendor (Perusahaan Kendaraan)',
                'Perusahaan',
                'Jumlah Order',
                'Nopol',
                'Tagihan',
                'Terbayar',
                'Status Bayar',
            ];
        }

        return [
            'No',
            'Status Bayar',
            'No. Nota',
            'Tanggal Nota',
            'Vendor (Perusahaan Kendaraan)',
            'Perusahaan',
            'Order dalam Nota',
            'Nopol',
            'Tagihan',
            'PPN',
            'PPh',
            'Claim',
            'Terbayar',
            'Sisa',
        ];
    }

    public function map($nota): array
    {
        $this->rowNumber++;
        $notaDate = $nota->nota_date ? Carbon::parse($nota->nota_date)->format('d-m-Y') : '-';
        $plates = $nota->plate_numbers->implode(', ') ?: '-';
        $statusLabel = match ($nota->payment_status) {
            'paid' => 'Lunas',
            'partial' => 'Dibayar sebagian',
            default => 'Belum dibayar',
        };

        if ($this->type === 'paid') {
            return [
                $this->rowNumber,
                $nota->nota_number ?: '-',
                $notaDate,
                $nota->fleet_company_name ?: '-',
                $nota->company_name ?: '-',
                (int) ($nota->order_count ?? 0),
                $plates,
                (float) ($nota->amount ?? 0),
                (float) ($nota->paid_amount ?? 0),
                $statusLabel,
            ];
        }

        return [
            $this->rowNumber,
            $statusLabel,
            $nota->nota_number ?: '-',
            $notaDate,
            $nota->fleet_company_name ?: '-',
            $nota->company_name ?: '-',
            (int) ($nota->order_count ?? 0),
            $plates,
            (float) ($nota->amount ?? 0),
            (float) ($nota->ppn_amount ?? 0),
            (float) ($nota->pph_amount ?? 0),
            (float) ($nota->claim_amount ?? 0),
            (float) ($nota->paid_amount ?? 0),
            (float) ($nota->remaining_amount ?? 0),
        ];
    }

    public function columnFormats(): array
    {
        if ($this->type === 'paid') {
            return [
                'H' => '#,##0',
                'I' => '#,##0',
            ];
        }

        return [
            'I' => '#,##0',
            'J' => '#,##0',
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
