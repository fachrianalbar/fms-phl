<?php

namespace App\Exports;

use App\Models\Operational\Order;
use App\Services\Report\OrderDetailReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class OrderDetailReport implements FromView, ShouldAutoSize, WithColumnFormatting
{
    use Exportable;

    /**
     * @var array<int, array<string, mixed>>
     */
    protected array $rows = [];

    /**
     * @var array<string, float>
     */
    protected array $totals = [];

    public function __construct($ordersOrRequest, ?OrderDetailReportService $reportService = null)
    {
        $reportService ??= app(OrderDetailReportService::class);

        if ($ordersOrRequest instanceof Request) {
            $orders = $reportService->buildFilteredQuery($ordersOrRequest)->get();
            $this->rows = $reportService->prepareExportRows($orders);
        } elseif (is_iterable($ordersOrRequest)) {
            $items = is_array($ordersOrRequest)
                ? array_values($ordersOrRequest)
                : collect($ordersOrRequest)->values()->all();

            if (! empty($items) && $items[0] instanceof Order) {
                $this->rows = $reportService->prepareExportRows($items);
            } else {
                $this->rows = $items;
            }
        }

        $this->totals = $reportService->computeGrandTotals($this->rows);
    }

    public function columnFormats(): array
    {
        return [
            'I' => '#,##0.##', // Qty
            'J' => '#,##0',    // Harga Satuan
            'K' => '#,##0',    // Total Omset
            'M' => '#,##0',    // Nominal Biaya Ditagihkan
            'N' => '#,##0',    // Total Biaya Ditagihkan
            'P' => '#,##0',    // Nominal Komisi
            'Q' => '#,##0',    // Total Komisi
            'S' => '#,##0',    // Nominal Gaji
            'T' => '#,##0',    // Total Gaji
            'V' => '#,##0',    // Nominal Biaya Lainnya
            'W' => '#,##0',    // Total Biaya Tidak Ditagihkan
            'X' => '#,##0',    // Laba Kotor
            'Y' => '#,##0',    // Potongan Pajak PPh
            'Z' => '#,##0',    // Laba Bersih
        ];
    }

    public function title(): string
    {
        return 'Order Detail Report';
    }

    public function view(): View
    {
        return view('report.order-detail.report.order-detail-excel', [
            'rows' => $this->rows,
            'totals' => $this->totals,
        ]);
    }
}
