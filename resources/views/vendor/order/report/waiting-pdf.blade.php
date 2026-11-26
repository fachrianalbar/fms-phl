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
        .filter-summary { margin-bottom: 8px; padding: 5px 8px; background: #f8fafc; border: 1px solid #cbd5e1; font-size: 7.5px; color: #334155; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #64748b; padding: 4px 3px; vertical-align: middle; }
        th { background: #e2e8f0; font-weight: bold; text-align: center; font-size: 7.5px; }
        tfoot td { background: #f1f5f9; font-weight: bold; }
        .center { text-align: center; }
        .right { text-align: right; }
        .nowrap { white-space: nowrap; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <div class="meta">Dicetak pada: {{ $printedAt->format('d-m-Y H:i') }} | Total Data: {{ $orders->count() }} Order</div>
    </div>

    @if (! empty($filterSummary))
        <div class="filter-summary">
            <strong>Filter Aktif:</strong> {{ implode(' | ', $filterSummary) }}
        </div>
    @endif

    @php
        $sumBase = 0;
        $sumOnCharge = 0;
        $sumBilling = 0;
        $sumPaid = 0;
        $sumRemaining = 0;
        $isId = (auth()->user()?->languange ?? 'id') === 'id';
    @endphp

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tgl Order</th>
                <th>Order Code</th>
                <th>Shipment</th>
                <th>No. Polisi</th>
                <th>Vendor</th>
                <th>Supir</th>
                <th>Customer</th>
                <th>Perusahaan</th>
                <th>Rute</th>
                <th>Harga Vendor</th>
                <th>Cost Component</th>
                <th>Total Tagihan</th>
                <th>Terbayar</th>
                <th>Sisa</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orders as $order)
                @php
                    $vp = $order->vendorPayments->sortByDesc('created_at')->first();
                    $baseAmount = $service->vendorBaseAmount($order, $vp);
                    $onChargeAmount = (float) $service->vendorOnChargeCosts($order)->sum('nominal');
                    $billingAmount = $service->vendorBillingAmount($order, $vp);
                    $paidAmount = (float) ($vp->paid_amount ?? 0);
                    $remainingAmount = max(0, $billingAmount - $paidAmount);

                    $sumBase += $baseAmount;
                    $sumOnCharge += $onChargeAmount;
                    $sumBilling += $billingAmount;
                    $sumPaid += $paidAmount;
                    $sumRemaining += $remainingAmount;

                    $origin = $order->route->originLocation->name ?? '';
                    $dest = $order->route->destinationLocation->name ?? '';
                    $routeText = trim($origin . ($origin && $dest ? ' - ' : '') . $dest) ?: '-';
                    $statusText = $order->orderStatus
                        ? ($isId ? ($order->orderStatus->nama ?? $order->orderStatus->name) : $order->orderStatus->name)
                        : '-';
                @endphp
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td class="center nowrap">{{ $order->orderDate ? \Carbon\Carbon::parse($order->orderDate)->format('d-m-Y') : '-' }}</td>
                    <td class="nowrap">{{ $order->code ?: '-' }}</td>
                    <td>{{ $order->shipmentNumber ?: '-' }}</td>
                    <td class="nowrap">{{ $order->plateNumber ?: ($order->fleet->plateNumber ?? '-') }}</td>
                    <td>{{ $order->fleet->company->name ?? '-' }}</td>
                    <td>{{ $order->driverName ?: ($order->driver->name ?? '-') }}</td>
                    <td>{{ $order->customer->name ?? '-' }}</td>
                    <td>{{ $order->customer->company->name ?? '-' }}</td>
                    <td>{{ $routeText }}</td>
                    <td class="right nowrap">Rp {{ number_format($baseAmount, 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format($onChargeAmount, 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format($billingAmount, 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format($paidAmount, 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format($remainingAmount, 0, ',', '.') }}</td>
                    <td class="center">{{ $statusText ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="16" class="center">Tidak ada data yang sesuai filter.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($orders->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="10" class="right">TOTAL</td>
                    <td class="right nowrap">Rp {{ number_format($sumBase, 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format($sumOnCharge, 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format($sumBilling, 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format($sumPaid, 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format($sumRemaining, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
