<?php

namespace App\Exports;

use App\Services\Finance\InvoiceService;
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

class InvoiceListExport implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    protected int $rowNumber = 0;

    public function __construct(
        protected array $filters,
        protected string $type,
        protected InvoiceService $service
    ) {}

    public function collection(): Collection
    {
        return match ($this->type) {
            'partial' => $this->service->findPartial($this->filters),
            'paid' => $this->service->findPaid($this->filters),
            default => $this->service->findUnpaid($this->filters),
        };
    }

    public function headings(): array
    {
        return [
            'No',
            'No. Faktur',
            'Tanggal Faktur',
            'Jatuh Tempo',
            'Pelanggan',
            'Perusahaan',
            'Total SJ',
            'Harga DPP',
            'PPN',
            'PPh 23',
            'Total Tagihan',
            'Terbayar',
            'Claim',
            'Sisa Tagihan',
            'Status',
        ];
    }

    public function map($invoice): array
    {
        $this->rowNumber++;
        $subtotal = (float) ($invoice->invoiceAmount ?? 0);
        $ppn = (float) ($invoice->ppnAmount ?? 0);
        $pph = (float) ($invoice->pphAmount ?? 0);
        $billing = $subtotal + $ppn - $pph;
        $paid = (float) ($invoice->payments ? $invoice->payments->sum('amount') : 0);
        $claim = (float) ($invoice->claims ? $invoice->claims->sum('amount') : 0);
        $remaining = max($billing - $paid - $claim, 0);

        if ($billing > 0 && ($paid + $claim) >= $billing) {
            $statusLabel = 'Lunas';
        } elseif (($paid + $claim) > 0) {
            $statusLabel = 'Sebagian';
        } else {
            $statusLabel = 'Belum Bayar';
        }

        return [
            $this->rowNumber,
            $invoice->invoiceNumber ?: ($invoice->code ?: '-'),
            $invoice->invoiceDate ? Carbon::parse($invoice->invoiceDate)->format('d-m-Y') : '-',
            $invoice->overdueDate ? Carbon::parse($invoice->overdueDate)->format('d-m-Y') : '-',
            $invoice->customer->name ?? ($invoice->customerCode ?: '-'),
            $invoice->customer->company->name ?? '-',
            (int) ($invoice->details ? $invoice->details->count() : 0),
            $subtotal,
            $ppn,
            $pph,
            $billing,
            $paid,
            $claim,
            $remaining,
            $statusLabel,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'H' => '#,##0',
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
