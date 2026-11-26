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
        .center { text-align: center; }
        .right { text-align: right; }
        .nowrap { white-space: nowrap; }
        tfoot td { background: #f1f5f9; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <div class="meta">Dicetak pada: {{ $printedAt->format('d-m-Y H:i') }} | Total Armada: {{ $rows->count() }}</div>
    </div>

    @if (! empty($filterSummary))
        <div class="filter-summary">
            <strong>Filter Aktif:</strong> {{ implode(' | ', $filterSummary) }}
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th colspan="3">Data Kendaraan</th>
                <th>Penjualan (A)</th>
                <th colspan="2">Biaya (B)</th>
                <th>Pajak (C)</th>
                <th>Laba Bersih (A - B - C)</th>
            </tr>
            <tr>
                <th style="width: 30px;">No</th>
                <th>Plat Nomor</th>
                <th>Perusahaan Kendaraan</th>
                <th>Penjualan Dasar</th>
                <th>Biaya Order</th>
                <th>Biaya Perawatan</th>
                <th>Pajak (PPh)</th>
                <th>Laba Bersih</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                @php
                    $fleet = $row['fleet'];
                    $summary = $row['summary'];
                @endphp
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td class="center nowrap">{{ $fleet->plateNumber ?? '-' }}</td>
                    <td>{{ $fleet->company?->name ?? '-' }}</td>
                    <td class="right nowrap">Rp {{ number_format($summary['basicSales'], 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format($summary['orderCost'], 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format($summary['maintenance'], 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format($summary['tax'], 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format($summary['netProfit'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="center">Tidak ada data laporan laba rugi.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="right">Total Keseluruhan</td>
                <td class="right nowrap">Rp {{ number_format($grandTotals['basicSales'] ?? 0, 0, ',', '.') }}</td>
                <td class="right nowrap">Rp {{ number_format($grandTotals['orderCost'] ?? 0, 0, ',', '.') }}</td>
                <td class="right nowrap">Rp {{ number_format($grandTotals['maintenance'] ?? 0, 0, ',', '.') }}</td>
                <td class="right nowrap">Rp {{ number_format($grandTotals['tax'] ?? 0, 0, ',', '.') }}</td>
                <td class="right nowrap">Rp {{ number_format($grandTotals['netProfit'] ?? 0, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
