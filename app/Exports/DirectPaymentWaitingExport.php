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

class DirectPaymentWaitingExport implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    protected int $rowNumber = 0;

    public function __construct(
        protected array $filters,
        protected DirectPaymentService $service
    ) {}

    public function collection(): Collection
    {
        return $this->service->findWaitingOrders($this->filters);
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Order',
            'Tanggal Order',
            'Customer',
            'Perusahaan',
            'Nopol',
            'Driver',
            'No. SJ',
            'Rute',
            'Ongkos',
            'Biaya Tambahan',
            'PPN',
            'PPh',
            'Claim',
            'Total',
            'Terbayar',
            'Sisa',
            'Status',
        ];
    }

    public function map($order): array
    {
        $this->rowNumber++;
        $orderDate = $order->date ? Carbon::parse($order->date)->format('d-m-Y') : '-';

        return [
            $this->rowNumber,
            $order->code ?: '-',
            $orderDate,
            $order->customer_name ?: '-',
            $order->company_name ?: '-',
            $order->plate ?: '-',
            $order->driver ?: '-',
            $order->shipment ?: '-',
            $order->rute ?: '-',
            (float) ($order->cost ?? 0),
            (float) ($order->additional_cost ?? 0),
            (float) ($order->ppn ?? 0),
            (float) ($order->pph ?? 0),
            (float) ($order->claim ?? 0),
            (float) ($order->grand_total ?? 0),
            (float) ($order->payment ?? 0),
            (float) ($order->remaining ?? 0),
            $order->status_label ?: 'Belum Bayar',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'J' => '#,##0',
            'K' => '#,##0',
            'L' => '#,##0',
            'M' => '#,##0',
            'N' => '#,##0',
            'O' => '#,##0',
            'P' => '#,##0',
            'Q' => '#,##0',
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
