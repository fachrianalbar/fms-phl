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
        $sumCost = 0;
        $sumAdd = 0;
        $sumPpn = 0;
        $sumPph = 0;
        $sumClaim = 0;
        $sumTotal = 0;
        $sumPaid = 0;
        $sumRemaining = 0;
    @endphp

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Order</th>
                <th>Tanggal</th>
                <th>Customer</th>
                <th>Perusahaan</th>
                <th>Nopol</th>
                <th>Driver</th>
                <th>No SJ</th>
                <th>Rute</th>
                <th>Ongkos</th>
                <th>Biaya Tamb.</th>
                <th>PPN</th>
                <th>PPh</th>
                <th>Claim</th>
                <th>Total</th>
                <th>Terbayar</th>
                <th>Sisa</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orders as $order)
                @php
                    $sumCost += (float) ($order->cost ?? 0);
                    $sumAdd += (float) ($order->additional_cost ?? 0);
                    $sumPpn += (float) ($order->ppn ?? 0);
                    $sumPph += (float) ($order->pph ?? 0);
                    $sumClaim += (float) ($order->claim ?? 0);
                    $sumTotal += (float) ($order->grand_total ?? 0);
                    $sumPaid += (float) ($order->payment ?? 0);
                    $sumRemaining += (float) ($order->remaining ?? 0);
                @endphp
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td class="nowrap">{{ $order->code ?: '-' }}</td>
                    <td class="center nowrap">{{ $order->date ? \Carbon\Carbon::parse($order->date)->format('d-m-Y') : '-' }}</td>
                    <td>{{ $order->customer_name ?: '-' }}</td>
                    <td>{{ $order->company_name ?: '-' }}</td>
                    <td class="nowrap">{{ $order->plate ?: '-' }}</td>
                    <td>{{ $order->driver ?: '-' }}</td>
                    <td>{{ $order->shipment ?: '-' }}</td>
                    <td>{{ $order->rute ?: '-' }}</td>
                    <td class="right nowrap">Rp {{ number_format((float) ($order->cost ?? 0), 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format((float) ($order->additional_cost ?? 0), 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format((float) ($order->ppn ?? 0), 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format((float) ($order->pph ?? 0), 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format((float) ($order->claim ?? 0), 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format((float) ($order->grand_total ?? 0), 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format((float) ($order->payment ?? 0), 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format((float) ($order->remaining ?? 0), 0, ',', '.') }}</td>
                    <td class="center">{{ $order->status_label ?: 'Belum Bayar' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="18" class="center">Tidak ada data yang sesuai filter.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($orders->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="9" class="right">TOTAL</td>
                    <td class="right nowrap">Rp {{ number_format($sumCost, 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format($sumAdd, 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format($sumPpn, 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format($sumPph, 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format($sumClaim, 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format($sumTotal, 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format($sumPaid, 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format($sumRemaining, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
