<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CommissionReportExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    protected int $rowNumber = 0;

    public function __construct(protected Builder $query) {}

    public function collection(): Collection
    {
        return $this->query
            ->with([
                'order.fleet',
                'order.customer',
                'order.route.originLocation',
                'order.route.destinationLocation',
                'costComponent',
                'orderCost',
                'commissionReport',
            ])
            ->orderByDesc('created_at')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal Order',
            'No. Shipment',
            'Kode Order',
            'Customer',
            'No. Polisi',
            'Rute',
            'Komponen Komisi',
            'Nominal Komisi',
            'Status Bayar',
            'Tanggal Bayar',
            'Kode Slip',
        ];
    }

    public function map($row): array
    {
        $this->rowNumber++;
        $order = $row->order;
        $route = '-';
        if ($order?->route) {
            $origin = $order->route->originLocation->name ?? '';
            $dest = $order->route->destinationLocation->name ?? '';
            $route = $order->route->name.(($origin || $dest) ? ' ('.$origin.' - '.$dest.')' : '');
        }

        return [
            $this->rowNumber,
            $order?->orderDate ? Carbon::parse($order->orderDate)->format('d-m-Y') : '-',
            $order?->shipmentNumber ?: '-',
            $order?->code ?: '-',
            $order?->customer?->name ?? '-',
            $order?->plateNumber ?: ($order?->fleet?->plateNumber ?? '-'),
            $route,
            $row->orderCost?->componentName ?: ($row->costComponent?->name ?? '-'),
            (float) ($row->amount ?? 0),
            $row->payment_date ? 'Sudah Dibayar' : 'Belum Dibayar',
            $row->payment_date ? Carbon::parse($row->payment_date)->format('d-m-Y') : '-',
            $row->commissionReport?->code ?? '-',
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
