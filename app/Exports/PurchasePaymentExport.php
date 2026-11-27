<?php

namespace App\Exports;

use App\Services\Purchasing\PurchasePaymentService;
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

class PurchasePaymentExport implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    protected int $rowNumber = 0;

    /** @var array<string, array<int, array<string, mixed>>> */
    protected array $vehicleBreakdowns = [];

    public function __construct(
        protected array $filters,
        protected string $mode,
        protected PurchasePaymentService $service
    ) {}

    public function collection(): Collection
    {
        $purchases = $this->mode === 'paid'
            ? $this->service->filteredPaid($this->filters)->get()
            : $this->service->filteredUnpaid($this->filters)->get();

        $this->vehicleBreakdowns = $this->service->resolveVehicleBreakdownForPurchases($purchases);

        return $purchases;
    }

    public function headings(): array
    {
        if ($this->mode === 'paid') {
            return [
                'No',
                'Kode Pembelian',
                'Tanggal PO',
                'Tanggal Bayar',
                'Mobil',
                'Supplier',
                'Total Tagihan',
                'Terbayar',
                'Status',
            ];
        }

        return [
            'No',
            'Kode Pembelian',
            'Tanggal PO',
            'Jatuh Tempo',
            'Mobil',
            'Supplier',
            'Total Tagihan',
            'Terbayar',
            'Sisa Hutang',
            'Status',
        ];
    }

    public function map($row): array
    {
        $this->rowNumber++;
        $billing = (float) $this->service->billingOf($row);
        $paid = (float) ($row->paidAmount ?? 0);

        $vehicleText = $this->service->formatVehicleBreakdownText(
            $this->vehicleBreakdowns[$row->code] ?? [],
            $this->mode,
            $row->plate_number_display
        );

        if ($this->mode === 'paid') {
            return [
                $this->rowNumber,
                $row->code ?: '-',
                $row->date ? Carbon::parse($row->date)->format('d-m-Y') : '-',
                $row->paymentDate ? Carbon::parse($row->paymentDate)->format('d-m-Y') : '-',
                $vehicleText,
                $row->supplier->name ?? '-',
                $billing,
                $paid,
                'Lunas',
            ];
        }

        $statusLabel = $row->paymentStatus === 'Partial' ? 'Belum Lunas (DP)' : 'Belum Bayar';

        return [
            $this->rowNumber,
            $row->code ?: '-',
            $row->date ? Carbon::parse($row->date)->format('d-m-Y') : '-',
            $row->dueDate ? Carbon::parse($row->dueDate)->format('d-m-Y') : '-',
            $vehicleText,
            $row->supplier->name ?? '-',
            $billing,
            $paid,
            max(0, $billing - $paid),
            $statusLabel,
        ];
    }

    public function columnFormats(): array
    {
        if ($this->mode === 'paid') {
            return [
                'G' => '#,##0',
                'H' => '#,##0',
            ];
        }

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
