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
        tfoot td { background: #f1f5f9; font-weight: bold; }
        .center { text-align: center; }
        .right { text-align: right; }
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

    @php
        $sumPrice = 0;
        $sumPersonalVendor = 0;
        $sumVendor = 0;
        $isId = (auth()->user()?->languange ?? 'id') === 'id';
    @endphp

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tgl Order</th>
                <th>No. Polisi</th>
                <th>Rute</th>
                <th>Supir</th>
                <th>Tipe</th>
                <th>No. Shipment</th>
                <th>Customer</th>
                <th>Asal</th>
                <th>Tujuan</th>
                <th>Harga</th>
                <th>Vendor Pribadi</th>
                <th>Harga Vendor</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orders as $order)
                @php
                    $routeAmt = (float) ($order->routeAmount ?? 0);
                    $personalVendorAmt = (float) ($order->personalVendorPrice ?? 0);
                    $vendorAmt = (float) ($order->vendorPrice ?? 0);
                    $sumPrice += $routeAmt;
                    $sumPersonalVendor += $personalVendorAmt;
                    $sumVendor += $vendorAmt;

                    $orderType = '-';
                    if (isset($order->fleet->company->type)) {
                        $orderType = strtolower($order->fleet->company->type) === 'external' ? 'External' : 'Internal';
                    }

                    $statusText = '-';
                    if (isset($order->orderStatus->name)) {
                        $statusText = $isId ? ($order->orderStatus->nama ?? $order->orderStatus->name) : $order->orderStatus->name;
                    }
                @endphp
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td class="center nowrap">{{ $order->orderDate ? \Carbon\Carbon::parse($order->orderDate)->format('d-m-Y') : '-' }}</td>
                    <td class="nowrap">{{ $order->plateNumber ?: ($order->fleet->plateNumber ?? '-') }}</td>
                    <td>{{ $order->route->name ?? '-' }}</td>
                    <td>{{ $order->driverName ?: ($order->driver->name ?? '-') }}</td>
                    <td class="center">{{ $orderType }}</td>
                    <td class="nowrap">{{ $order->shipmentNumber ? mb_strtoupper($order->shipmentNumber) : '-' }}</td>
                    <td>{{ $order->customer->name ?? '-' }}</td>
                    <td>{{ $order->route->originLocation->name ?? '-' }}</td>
                    <td>{{ $order->route->destinationLocation->name ?? '-' }}</td>
                    <td class="right nowrap">Rp {{ number_format($routeAmt, 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format($personalVendorAmt, 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format($vendorAmt, 0, ',', '.') }}</td>
                    <td class="center">{{ $statusText }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="14" class="center">Tidak ada data yang sesuai filter.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($orders->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="10" class="right">TOTAL</td>
                    <td class="right nowrap">Rp {{ number_format($sumPrice, 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format($sumPersonalVendor, 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format($sumVendor, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
