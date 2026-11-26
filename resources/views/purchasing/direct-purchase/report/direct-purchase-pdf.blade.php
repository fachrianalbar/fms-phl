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
        th, td { border: 1px solid #64748b; padding: 5px 4px; vertical-align: middle; }
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
        <div class="meta">Dicetak pada: {{ $printedAt->format('d-m-Y H:i') }} | Total Transaksi: {{ $purchases->count() }}</div>
    </div>

    @if (! empty($filterSummary))
        <div class="filter-summary">
            <strong>Filter Aktif:</strong> {{ implode(' | ', $filterSummary) }}
        </div>
    @endif

    @php
        $sumNominal = 0;
        $sumQty = 0;
    @endphp

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No. Transaksi</th>
                <th>Tanggal</th>
                <th>Armada</th>
                <th>Supplier / Toko</th>
                <th>No. Maintenance</th>
                <th>Jenis Bayar</th>
                <th>Status</th>
                <th>Item / Qty</th>
                <th>Total Biaya</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($purchases as $row)
                @php
                    $total = (float) ($row->nominal ?: $row->details->sum(fn ($d) => (float) $d->price * (float) $d->qty));
                    $qty = (float) $row->details->sum('qty');
                    $sumNominal += $total;
                    $sumQty += $qty;
                    $mnt = $row->maintenances->first();
                    $statusLabel = match ($row->paymentStatus) {
                        'Paid' => 'Lunas',
                        'Partial' => 'Sebagian',
                        default => 'Belum Dibayar',
                    };
                @endphp
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td class="nowrap">{{ $row->code ?: '-' }}</td>
                    <td class="center nowrap">
                        {{ $row->date ? \Carbon\Carbon::parse($row->date)->format('d-m-Y') : '-' }}
                        {{ $row->time ? \Carbon\Carbon::parse($row->time)->format('H:i') : '' }}
                    </td>
                    <td class="center nowrap">{{ $row->fleet->plateNumber ?? '-' }}</td>
                    <td>{{ $row->supplier->name ?? '-' }}</td>
                    <td class="center nowrap">{{ $mnt->code ?? '-' }}</td>
                    <td class="center">{{ $row->paymentType ?: 'Tunai' }}</td>
                    <td class="center">{{ $statusLabel }}</td>
                    <td class="center nowrap">{{ $row->details->count() }} Item / {{ rtrim(rtrim(number_format($qty, 1, ',', '.'), '0'), ',') }} Qty</td>
                    <td class="right nowrap">Rp {{ number_format($total, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="center">Tidak ada data yang sesuai filter.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($purchases->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="8" class="right">TOTAL</td>
                    <td class="center nowrap">{{ rtrim(rtrim(number_format($sumQty, 1, ',', '.'), '0'), ',') }} Qty</td>
                    <td class="right nowrap">Rp {{ number_format($sumNominal, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
