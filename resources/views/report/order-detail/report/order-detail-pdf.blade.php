@php
    $service = app(\App\Services\Report\OrderDetailReportService::class);
    $preparedRows = $rows ?? [];
    if (empty($preparedRows) && isset($data) && is_iterable($data)) {
        $items = is_array($data) ? $data : collect($data)->all();
        $preparedRows = ! empty($items) && $items[0] instanceof \App\Models\Operational\Order
            ? $service->prepareExportRows($items)
            : $items;
    }
    $summaryTotals = $totals ?? $service->computeGrandTotals($preparedRows);
@endphp
@include('report.order-detail.report.order-detail-pdf-header')
@include('report.order-detail.report.order-detail-pdf-rows', ['data' => $preparedRows, 'start' => 0])
@include('report.order-detail.report.order-detail-pdf-footer', ['totals' => $summaryTotals])
