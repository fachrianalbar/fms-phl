<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: sans-serif; font-size: 8.5px; color: #1f2937; }
        .header { text-align: center; margin-bottom: 10px; }
        .header h1 { font-size: 14px; margin: 0 0 3px; text-transform: uppercase; }
        .header .meta { font-size: 8px; color: #475569; }
        .filter-summary { margin-bottom: 8px; padding: 5px 8px; background: #f8fafc; border: 1px solid #cbd5e1; font-size: 8px; color: #334155; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #64748b; padding: 4.5px 4px; vertical-align: middle; }
        th { background: #e2e8f0; font-weight: bold; text-align: center; font-size: 8px; }
        tfoot td { background: #f1f5f9; font-weight: bold; }
        .center { text-align: center; }
        .right { text-align: right; }
        .nowrap { white-space: nowrap; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <div class="meta">Dicetak pada: {{ $printedAt->format('d-m-Y H:i') }} | Total Nota: {{ $notas->count() }} Nota</div>
    </div>

    @if (! empty($filterSummary))
        <div class="filter-summary">
            <strong>Filter Aktif:</strong> {{ implode(' | ', $filterSummary) }}
        </div>
    @endif

    @php
        $isPaid = ($type ?? 'pending') === 'paid';
        $sumOrders = 0;
        $sumAmount = 0;
        $sumPpn = 0;
        $sumPph = 0;
        $sumClaim = 0;
        $sumPaid = 0;
        $sumRemaining = 0;
    @endphp

    <table>
        <thead>
            <tr>
                <th>No</th>
                @if (! $isPaid)
                    <th>Status Bayar</th>
                @endif
                <th>No. Nota</th>
                <th>Tanggal Nota</th>
                <th>Vendor (Perusahaan Kendaraan)</th>
                <th>Perusahaan</th>
                <th>Order</th>
                <th>Nopol</th>
                <th>Tagihan</th>
                @if (! $isPaid)
                    <th>PPN</th>
                    <th>PPh</th>
                    <th>Claim</th>
                @endif
                <th>Terbayar</th>
                @if (! $isPaid)
                    <th>Sisa</th>
                @else
                    <th>Status Bayar</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse ($notas as $nota)
                @php
                    $sumOrders += (int) ($nota->order_count ?? 0);
                    $sumAmount += (float) ($nota->amount ?? 0);
                    $sumPpn += (float) ($nota->ppn_amount ?? 0);
                    $sumPph += (float) ($nota->pph_amount ?? 0);
                    $sumClaim += (float) ($nota->claim_amount ?? 0);
                    $sumPaid += (float) ($nota->paid_amount ?? 0);
                    $sumRemaining += (float) ($nota->remaining_amount ?? 0);

                    $statusLabel = match ($nota->payment_status) {
                        'paid' => 'Lunas',
                        'partial' => 'Dibayar sebagian',
                        default => 'Belum dibayar',
                    };
                    $plates = $nota->plate_numbers->implode(', ') ?: '-';
                @endphp
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    @if (! $isPaid)
                        <td class="center">{{ $statusLabel }}</td>
                    @endif
                    <td class="nowrap">{{ $nota->nota_number ?: '-' }}</td>
                    <td class="center nowrap">{{ $nota->nota_date ? \Carbon\Carbon::parse($nota->nota_date)->format('d-m-Y') : '-' }}</td>
                    <td>{{ $nota->fleet_company_name ?: '-' }}</td>
                    <td>{{ $nota->company_name ?: '-' }}</td>
                    <td class="center">{{ (int) ($nota->order_count ?? 0) }}</td>
                    <td>{{ $plates }}</td>
                    <td class="right nowrap">Rp {{ number_format((float) ($nota->amount ?? 0), 0, ',', '.') }}</td>
                    @if (! $isPaid)
                        <td class="right nowrap">Rp {{ number_format((float) ($nota->ppn_amount ?? 0), 0, ',', '.') }}</td>
                        <td class="right nowrap">Rp {{ number_format((float) ($nota->pph_amount ?? 0), 0, ',', '.') }}</td>
                        <td class="right nowrap">Rp {{ number_format((float) ($nota->claim_amount ?? 0), 0, ',', '.') }}</td>
                    @endif
                    <td class="right nowrap">Rp {{ number_format((float) ($nota->paid_amount ?? 0), 0, ',', '.') }}</td>
                    @if (! $isPaid)
                        <td class="right nowrap">Rp {{ number_format((float) ($nota->remaining_amount ?? 0), 0, ',', '.') }}</td>
                    @else
                        <td class="center">{{ $statusLabel }}</td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $isPaid ? 10 : 14 }}" class="center">Tidak ada data yang sesuai filter.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($notas->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="{{ $isPaid ? 5 : 6 }}" class="right">TOTAL</td>
                    <td class="center">{{ $sumOrders }}</td>
                    <td></td>
                    <td class="right nowrap">Rp {{ number_format($sumAmount, 0, ',', '.') }}</td>
                    @if (! $isPaid)
                        <td class="right nowrap">Rp {{ number_format($sumPpn, 0, ',', '.') }}</td>
                        <td class="right nowrap">Rp {{ number_format($sumPph, 0, ',', '.') }}</td>
                        <td class="right nowrap">Rp {{ number_format($sumClaim, 0, ',', '.') }}</td>
                    @endif
                    <td class="right nowrap">Rp {{ number_format($sumPaid, 0, ',', '.') }}</td>
                    @if (! $isPaid)
                        <td class="right nowrap">Rp {{ number_format($sumRemaining, 0, ',', '.') }}</td>
                    @else
                        <td></td>
                    @endif
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
