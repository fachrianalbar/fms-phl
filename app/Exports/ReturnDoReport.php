<?php

namespace App\Exports;

use App\Services\Operational\ReturnDoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReturnDoReport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    protected int $rowNumber = 0;

    public function __construct(
        protected Request|array $filters,
        protected ReturnDoService $service
    ) {}

    public function collection(): Collection
    {
        return $this->service->datatable($this->filters)->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal Order',
            'Kode Order',
            'No. Shipment',
            'No. Faktur',
            'Customer',
            'Asal',
            'Tujuan',
            'No. Polisi',
            'Supir',
            'Tipe Order',
            'Tanggal Kembali',
        ];
    }

    public function map($order): array
    {
        $this->rowNumber++;

        $invoice = $order->invoiceDetail->invoice ?? null;
        if ($invoice && ! empty($invoice->invoiceNumber)) {
            $invoiceLabel = $invoice->invoiceNumber;
        } elseif ((int) ($order->customer->isDo ?? 1) === 0) {
            $invoiceLabel = 'Tanpa Faktur';
        } else {
            $invoiceLabel = 'Belum Ada Invoice';
        }

        $orderType = '';
        if (isset($order->fleet->company->type)) {
            $orderType = strtolower($order->fleet->company->type) === 'external' ? 'External' : 'Internal';
        } elseif (isset($order->orderType->name)) {
            $orderType = $order->orderType->name;
        } elseif ($order->orderTypeCode) {
            $orderType = $order->orderTypeCode == 'Ext' ? 'External' : ($order->orderTypeCode == 'Int' ? 'Internal' : $order->orderTypeCode);
        }

        return [
            $this->rowNumber,
            $order->orderDate ? Carbon::parse($order->orderDate)->format('d-m-Y') : '-',
            $order->code ?: '-',
            $order->shipmentNumber ? mb_strtoupper($order->shipmentNumber) : '-',
            $invoiceLabel,
            $order->customer->name ?? '-',
            $order->route->originLocation->name ?? '-',
            $order->route->destinationLocation->name ?? '-',
            $order->plateNumber ?: ($order->fleet->plateNumber ?? '-'),
            $order->driverName ?: ($order->driver->name ?? '-'),
            $orderType ?: '-',
            $order->returnDate ? Carbon::parse($order->returnDate)->format('d-m-Y') : '-',
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
