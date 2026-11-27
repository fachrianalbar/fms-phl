@php
    $section = $section ?? 'full';
    $startIndex = $startIndex ?? 0;
    $totalCount = $totalCount ?? $purchases->count();
    $isFull = $section === 'full';
@endphp

@if ($isFull || $section === 'header')
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
        <div class="meta">Dicetak pada: {{ $printedAt->format('d-m-Y H:i') }} | Total PO Lunas: {{ $totalCount }}</div>
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
                <th>Kode Pembelian</th>
                <th>Tanggal PO</th>
                <th>Tanggal Bayar</th>
                <th>Mobil</th>
                <th>Supplier</th>
                <th>Total Tagihan</th>
                <th>Terbayar</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
@endif

@if ($isFull || $section === 'rows')
    @php
        $localSumBilling = 0;
        $localSumPaid = 0;
    @endphp
    @forelse ($purchases as $row)
        @php
            $billing = (float) $service->billingOf($row);
            $paid = (float) ($row->paidAmount ?? 0);
            $localSumBilling += $billing;
            $localSumPaid += $paid;
        @endphp
        <tr>
            <td class="center">{{ $startIndex + $loop->iteration }}</td>
            <td class="nowrap">{{ $row->code ?: '-' }}</td>
            <td class="center nowrap">{{ $row->date ? \Carbon\Carbon::parse($row->date)->format('d-m-Y') : '-' }}</td>
            <td class="center nowrap">{{ $row->paymentDate ? \Carbon\Carbon::parse($row->paymentDate)->format('d-m-Y') : '-' }}</td>
            <td class="center">{{ $service->formatVehicleBreakdownText($vehicleBreakdowns[$row->code] ?? [], 'paid', $row->plate_number_display) }}</td>
            <td>{{ $row->supplier->name ?? '-' }}</td>
            <td class="right nowrap">Rp {{ number_format($billing, 0, ',', '.') }}</td>
            <td class="right nowrap">Rp {{ number_format($paid, 0, ',', '.') }}</td>
            <td class="center">Lunas</td>
        </tr>
    @empty
        <tr>
            <td colspan="9" class="center">Tidak ada data yang sesuai filter.</td>
        </tr>
    @endforelse
@endif

@if ($isFull || $section === 'footer')
        </tbody>
        @if ($totalCount > 0)
            <tfoot>
                <tr>
                    <td colspan="6" class="right">TOTAL</td>
                    <td class="right nowrap">Rp {{ number_format($sumBilling ?? ($localSumBilling ?? 0), 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format($sumPaid ?? ($localSumPaid ?? 0), 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
@endif
