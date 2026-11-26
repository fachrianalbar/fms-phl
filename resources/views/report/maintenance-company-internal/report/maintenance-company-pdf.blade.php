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
        th, td { border: 1px solid #64748b; padding: 5px 5px; vertical-align: middle; }
        th { background: #e2e8f0; font-weight: bold; text-align: center; font-size: 8px; }
        .center { text-align: center; }
        .right { text-align: right; }
        .nowrap { white-space: nowrap; }
        tfoot td { background: #f1f5f9; font-weight: bold; }
    </style>
</head>
<body>
    @php
        $grandFleet = 0;
        $grandMaintenance = 0;
        $grandQty = 0.0;
        $grandCost = 0.0;
    @endphp
    <div class="header">
        <h1>{{ $title }}</h1>
        <div class="meta">Dicetak pada: {{ $printedAt->format('d-m-Y H:i') }} | Total Perusahaan: {{ $rows->count() }}</div>
    </div>

    @if (! empty($filterSummary))
        <div class="filter-summary">
            <strong>Filter Aktif:</strong> {{ implode(' | ', $filterSummary) }}
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width: 35px;">No</th>
                <th>Perusahaan Armada</th>
                <th>Tipe</th>
                <th>Total Armada</th>
                <th>Total Perawatan</th>
                <th>Total Qty</th>
                <th>Total Biaya</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                @php
                    $grandFleet += (int) $row->totalFleet;
                    $grandMaintenance += (int) $row->totalMaintenance;
                    $grandQty += (float) $row->totalQty;
                    $grandCost += (float) $row->totalCost;
                @endphp
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td>{{ $row->fleetCompanyName ?: '-' }}</td>
                    <td class="center">{{ $row->fleetCompanyType ?: 'Internal' }}</td>
                    <td class="right nowrap">{{ number_format((float) $row->totalFleet, 0, ',', '.') }}</td>
                    <td class="right nowrap">{{ number_format((float) $row->totalMaintenance, 0, ',', '.') }}</td>
                    <td class="right nowrap">{{ number_format((float) $row->totalQty, 1, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format((float) $row->totalCost, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="center">Tidak ada data yang sesuai filter.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="right">TOTAL</td>
                <td class="right nowrap">{{ number_format($grandFleet, 0, ',', '.') }}</td>
                <td class="right nowrap">{{ number_format($grandMaintenance, 0, ',', '.') }}</td>
                <td class="right nowrap">{{ number_format($grandQty, 1, ',', '.') }}</td>
                <td class="right nowrap">Rp {{ number_format($grandCost, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
