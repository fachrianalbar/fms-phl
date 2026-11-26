<?php

namespace App\Exports;

use App\Services\Finance\DirectPaymentService;
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

class DirectPaymentNotaExport implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    protected int $rowNumber = 0;

    public function __construct(
        protected array $filters,
        protected string $type,
        protected DirectPaymentService $service
    ) {}

    public function collection(): Collection
    {
        return $this->type === 'paid'
            ? $this->service->findPaidUnits($this->filters)
            : $this->service->findUnpaidNotas($this->filters);
    }

    public function headings(): array
    {
        if ($this->type === 'paid') {
            return [
                'No',
                'Kode / No. Nota',
                'Tgl Order',
                'Tgl Bayar',
                'Customer',
                'Perusahaan',
                'Nopol',
                'Total Tagihan',
                'Terbayar',
                'Status',
            ];
        }

        return [
            'No',
            'No. Nota',
            'Tanggal Nota',
            'Customer',
            'Perusahaan',
            'Jumlah DO',
            'Nopol Armada',
            'Ongkos',
            'Biaya Tambahan',
            'PPN',
            'PPh',
            'Claim',
            'Total Tagihan',
            'Terbayar',
            'Sisa',
            'Status',
        ];
    }

    public function map($row): array
    {
        $this->rowNumber++;

        if ($this->type === 'paid') {
            $orderDateText = ! empty($row->order_date_range)
                ? $row->order_date_range
                : ($row->order_date ? Carbon::parse($row->order_date)->format('d-m-Y') : '-');
            $paymentDateText = $row->payment_date
                ? Carbon::parse($row->payment_date)->format('d-m-Y')
                : '-';
            $statusLabel = ($row->status_code ?? '') === 'overpaid' ? 'Kelebihan' : 'Lunas';

            return [
                $this->rowNumber,
                $row->code ?: '-',
                $orderDateText,
                $paymentDateText,
                $row->customer_name ?: '-',
                $row->company_name ?: '-',
                $row->plate ?: '-',
                (float) ($row->grand_total ?? 0),
                (float) ($row->payment ?? 0),
                $statusLabel,
            ];
        }

        $notaDate = $row->date ? Carbon::parse($row->date)->format('d-m-Y') : '-';
        $plates = $row->plate_numbers->implode(', ') ?: '-';
        $statusLabel = match ($row->payment_status) {
            'paid' => 'Lunas',
            'partial' => 'Belum Lunas (DP)',
            default => 'Belum Bayar',
        };

        return [
            $this->rowNumber,
            $row->nota_number ?: '-',
            $notaDate,
            $row->customer_name ?: '-',
            $row->company_name ?: '-',
            (int) ($row->order_count ?? 0),
            $plates,
            (float) ($row->cost_amount ?? 0),
            (float) ($row->additional_cost_amount ?? 0),
            (float) ($row->ppn_amount ?? 0),
            (float) ($row->pph_amount ?? 0),
            (float) ($row->claim_amount ?? 0),
            (float) ($row->amount ?? 0),
            (float) ($row->paid_amount ?? 0),
            (float) ($row->remaining_amount ?? 0),
            $statusLabel,
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
            'H' => '#,##0',
            'I' => '#,##0',
            'J' => '#,##0',
            'K' => '#,##0',
            'L' => '#,##0',
            'M' => '#,##0',
            'N' => '#,##0',
            'O' => '#,##0',
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
