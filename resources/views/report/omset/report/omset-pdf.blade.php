<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Omset</title>
    <style>
        @page { margin: 12mm 10mm 12mm 10mm; }
        body { font-family: Arial, sans-serif; font-size: 9.5px; color: #1e293b; margin: 0; }
        .header { margin-bottom: 10px; border-bottom: 2px solid #4f46e5; padding-bottom: 6px; }
        .title { font-size: 16px; font-weight: bold; color: #1e293b; }
        .subtitle { font-size: 10px; color: #64748b; margin-top: 2px; }
        .summary-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .summary-table td { border: 1px solid #cbd5e1; padding: 6px 10px; background: #f8fafc; }
        .summary-label { font-size: 8.5px; text-transform: uppercase; color: #64748b; font-weight: bold; }
        .summary-val { font-size: 12px; font-weight: bold; margin-top: 2px; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #cbd5e1; padding: 4px 6px; vertical-align: top; }
        table.data th { background-color: #f1f5f9; font-weight: bold; text-align: center; font-size: 9px; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .total-row td { background-color: #eef2ff; font-weight: bold; font-size: 10px; border-top: 2px solid #4f46e5; }
    </style>
</head>

<body>
    <div class="header">
        <div class="title">LAPORAN OMSET</div>
        <div class="subtitle">Periode: {{ $periodLabel }} | Dicetak: {{ now()->format('d-m-Y H:i') }}</div>
    </div>

    <table class="summary-table">
        <tr>
            <td style="width: 25%;">
                <div class="summary-label">Total Order</div>
                <div class="summary-val">{{ number_format($totals['totalOrders'], 0, ',', '.') }} Order</div>
            </td>
            <td style="width: 25%;">
                <div class="summary-label">Total Semua Omset</div>
                <div class="summary-val" style="color: #3730a3;">{{ $totals['totalOmsetFormatted'] }}</div>
            </td>
            <td style="width: 25%;">
                <div class="summary-label">Total Semua Biaya</div>
                <div class="summary-val" style="color: #b91c1c;">{{ $totals['totalBiayaFormatted'] }}</div>
            </td>
            <td style="width: 25%;">
                <div class="summary-label">Total Net / Profit</div>
                <div class="summary-val" style="color: {{ $totals['totalProfit'] >= 0 ? '#065f46' : '#b91c1c' }};">{{ $totals['totalProfitFormatted'] }}</div>
            </td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 7%;">Tanggal</th>
                <th style="width: 13%;">No. Shipment / Order</th>
                <th style="width: 13%;">Customer / Perusahaan</th>
                <th style="width: 10%;">No. Polisi / Supir</th>
                <th style="width: 15%;">Rute</th>
                <th style="width: 9%;">Status</th>
                <th style="width: 10%;">Omset</th>
                <th style="width: 10%;">Biaya</th>
                <th style="width: 10%;">Net / Profit</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $idx => $row)
                @php
                    $payLabel = $row->paymentStatus === 'paid'
                        ? 'Lunas'
                        : ($row->paymentStatus === 'partial' ? 'DP' : 'Belum Bayar');
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-center">{{ $row->orderDate ? \Carbon\Carbon::parse($row->orderDate)->format('d-m-Y') : '-' }}</td>
                    <td>
                        <strong>{{ ($row->shipmentNumber && $row->shipmentNumber !== '-') ? $row->shipmentNumber : $row->orderCode }}</strong>
                        @if ($row->shipmentNumber && $row->shipmentNumber !== '-')
                            <br><span style="color:#64748b; font-size:8.5px;">{{ $row->orderCode }}</span>
                        @endif
                    </td>
                    <td>
                        <strong>{{ $row->customerName }}</strong>
                        <br><span style="color:#64748b; font-size:8.5px;">{{ $row->companyName }} ({{ $row->fleetCategory }})</span>
                    </td>
                    <td>
                        <strong>{{ $row->plateNumber }}</strong>
                        <br><span style="color:#64748b; font-size:8.5px;">{{ $row->driverName }}</span>
                    </td>
                    <td>
                        {{ $row->routeName }}
                        @if ($row->originName || $row->destinationName)
                            <br><span style="color:#64748b; font-size:8.5px;">{{ $row->originName }} &rarr; {{ $row->destinationName }}</span>
                        @endif
                    </td>
                    <td class="text-center">
                        {{ $row->orderStatusName }}<br>
                        <span style="color:#475569; font-size:8.5px;">{{ $payLabel }}</span>
                    </td>
                    <td class="text-end">Rp {{ number_format((float) $row->omset, 0, ',', '.') }}</td>
                    <td class="text-end">Rp {{ number_format((float) $row->biaya, 0, ',', '.') }}</td>
                    <td class="text-end" style="color: {{ (float) $row->profit >= 0 ? '#065f46' : '#b91c1c' }};">
                        {{ (float) $row->profit < 0 ? '-Rp ' : 'Rp ' }}{{ number_format(abs((float) $row->profit), 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding: 16px; color: #64748b;">Tidak ada data sesuai filter.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="7" class="text-end">GRAND TOTAL ({{ number_format($totals['totalOrders'], 0, ',', '.') }} Order)</td>
                <td class="text-end">{{ $totals['totalOmsetFormatted'] }}</td>
                <td class="text-end">{{ $totals['totalBiayaFormatted'] }}</td>
                <td class="text-end">{{ $totals['totalProfitFormatted'] }}</td>
            </tr>
        </tfoot>
    </table>
</body>

</html>
