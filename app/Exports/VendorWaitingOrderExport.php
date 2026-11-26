<?php

namespace App\Exports;

use App\Services\Finance\VendorPaymentService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VendorWaitingOrderExport implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    protected int $rowNumber = 0;

    public function __construct(
        protected array $filters,
        protected VendorPaymentService $service
    ) {}

    public function collection(): Collection
    {
        return $this->service->findWaitingOrders($this->filters);
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal Order',
            'Order Code',
            'No. Shipment',
            'No. Polisi',
            'Perusahaan Kendaraan',
            'Supir',
            'Customer',
            'Perusahaan',
            'Asal',
            'Tujuan',
            'Harga Vendor',
            'Cost Component',
            'Total Tagihan',
            'Terbayar',
            'Sisa',
            'Status',
        ];
    }

    public function map($order): array
    {
        $this->rowNumber++;
        $vendorPayment = $order->vendorPayments->sortByDesc('created_at')->first();
        $baseAmount = $this->service->vendorBaseAmount($order, $vendorPayment);
        $onChargeAmount = (float) $this->service->vendorOnChargeCosts($order)->sum('nominal');
        $billingAmount = $this->service->vendorBillingAmount($order, $vendorPayment);
        $paidAmount = (float) ($vendorPayment->paid_amount ?? 0);
        $remainingAmount = max(0, $billingAmount - $paidAmount);

        $isId = (Auth::user()?->languange ?? 'id') === 'id';
        $statusText = $order->orderStatus
            ? ($isId ? ($order->orderStatus->nama ?? $order->orderStatus->name) : $order->orderStatus->name)
            : '-';

        return [
            $this->rowNumber,
            $order->orderDate ? Carbon::parse($order->orderDate)->format('d-m-Y') : '-',
            $order->code ?: '-',
            $order->shipmentNumber ?: '-',
            $order->plateNumber ?: ($order->fleet->plateNumber ?? '-'),
            $order->fleet->company->name ?? '-',
            $order->driverName ?: ($order->driver->name ?? '-'),
            $order->customer->name ?? '-',
            $order->customer->company->name ?? '-',
            $order->route->originLocation->name ?? '-',
            $order->route->destinationLocation->name ?? '-',
            $baseAmount,
            $onChargeAmount,
            $billingAmount,
            $paidAmount,
            $remainingAmount,
            $statusText ?: '-',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'L' => '#,##0',
            'M' => '#,##0',
            'N' => '#,##0',
            'O' => '#,##0',
            'P' => '#,##0',
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
