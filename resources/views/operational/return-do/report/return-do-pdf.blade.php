<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: sans-serif; font-size: 8px; color: #1f2937; }
        .header { text-align: center; margin-bottom: 10px; }
        .header h1 { font-size: 14px; margin: 0 0 3px; text-transform: uppercase; }
        .header .meta { font-size: 8px; color: #475569; }
        .filter-summary { margin-bottom: 8px; padding: 5px 8px; background: #f8fafc; border: 1px solid #cbd5e1; font-size: 8px; color: #334155; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #64748b; padding: 4px; vertical-align: middle; }
        th { background: #e2e8f0; font-weight: bold; text-align: center; font-size: 8px; }
        .center { text-align: center; }
        .nowrap { white-space: nowrap; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan {{ $title }}</h1>
        <div class="meta">Dicetak pada: {{ $printedAt->format('d-m-Y H:i') }} | Total Order: {{ $orders->count() }} Order</div>
    </div>

    @if (! empty($filterSummary))
        <div class="filter-summary">
            <strong>Filter Aktif:</strong> {{ implode(' | ', $filterSummary) }}
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tgl Order</th>
                <th>Kode Order</th>
                <th>No. Shipment</th>
                <th>No. Faktur</th>
                <th>Customer</th>
                <th>Asal</th>
                <th>Tujuan</th>
                <th>No. Polisi</th>
                <th>Supir</th>
                <th>Tipe Order</th>
                <th>Tgl Kembali</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orders as $order)
                @php
                    $invoice = $order->invoiceDetail->invoice ?? null;
                    if ($invoice && ! empty($invoice->invoiceNumber)) {
                        $invoiceLabel = $invoice->invoiceNumber;
                    } elseif ((int) ($order->customer->isDo ?? 1) === 0) {
                        $invoiceLabel = 'Tanpa Faktur';
                    } else {
                        $invoiceLabel = 'Belum Ada Invoice';
                    }

                    $orderType = '-';
                    if (isset($order->fleet->company->type)) {
                        $orderType = strtolower($order->fleet->company->type) === 'external' ? 'External' : 'Internal';
                    } elseif (isset($order->orderType->name)) {
                        $orderType = $order->orderType->name;
                    } elseif ($order->orderTypeCode) {
                        $orderType = $order->orderTypeCode == 'Ext' ? 'External' : ($order->orderTypeCode == 'Int' ? 'Internal' : $order->orderTypeCode);
                    }
                @endphp
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td class="center nowrap">{{ $order->orderDate ? \Carbon\Carbon::parse($order->orderDate)->format('d-m-Y') : '-' }}</td>
                    <td class="nowrap">{{ $order->code ?: '-' }}</td>
                    <td class="nowrap">{{ $order->shipmentNumber ? mb_strtoupper($order->shipmentNumber) : '-' }}</td>
                    <td class="nowrap">{{ $invoiceLabel }}</td>
                    <td>{{ $order->customer->name ?? '-' }}</td>
                    <td>{{ $order->route->originLocation->name ?? '-' }}</td>
                    <td>{{ $order->route->destinationLocation->name ?? '-' }}</td>
                    <td class="nowrap">{{ $order->plateNumber ?: ($order->fleet->plateNumber ?? '-') }}</td>
                    <td>{{ $order->driverName ?: ($order->driver->name ?? '-') }}</td>
                    <td class="center">{{ $orderType }}</td>
                    <td class="center nowrap">{{ $order->returnDate ? \Carbon\Carbon::parse($order->returnDate)->format('d-m-Y') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="center">Tidak ada data yang sesuai filter.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
