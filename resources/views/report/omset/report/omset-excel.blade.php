<table>
    <thead>
        <tr>
            <th colspan="13" style="font-size: 14px; font-weight: bold;">LAPORAN OMSET</th>
        </tr>
        <tr>
            <th colspan="13">Periode: {{ $periodLabel }} | Total Order: {{ number_format($totals['totalOrders'], 0, ',', '.') }}</th>
        </tr>
        <tr></tr>
        <tr>
            <th style="font-weight: bold; background-color: #e2e8f0;">No</th>
            <th style="font-weight: bold; background-color: #e2e8f0;">Tanggal</th>
            <th style="font-weight: bold; background-color: #e2e8f0;">No. Shipment</th>
            <th style="font-weight: bold; background-color: #e2e8f0;">Kode Order</th>
            <th style="font-weight: bold; background-color: #e2e8f0;">Customer</th>
            <th style="font-weight: bold; background-color: #e2e8f0;">Perusahaan</th>
            <th style="font-weight: bold; background-color: #e2e8f0;">Tipe Armada</th>
            <th style="font-weight: bold; background-color: #e2e8f0;">No. Polisi</th>
            <th style="font-weight: bold; background-color: #e2e8f0;">Supir</th>
            <th style="font-weight: bold; background-color: #e2e8f0;">Rute</th>
            <th style="font-weight: bold; background-color: #e2e8f0;">Status Order / Bayar</th>
            <th style="font-weight: bold; background-color: #e2e8f0; text-align: right;">Omset (Rp)</th>
            <th style="font-weight: bold; background-color: #e2e8f0; text-align: right;">Biaya (Rp)</th>
            <th style="font-weight: bold; background-color: #e2e8f0; text-align: right;">Net / Profit (Rp)</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rows as $idx => $row)
            @php
                $payLabel = $row->paymentStatus === 'paid'
                    ? 'Lunas'
                    : ($row->paymentStatus === 'partial' ? 'DP / Sebagian' : 'Belum Dibayar');
                $routeText = $row->routeName . (($row->originName || $row->destinationName) ? ' (' . $row->originName . ' - ' . $row->destinationName . ')' : '');
            @endphp
            <tr>
                <td>{{ $idx + 1 }}</td>
                <td>{{ $row->orderDate ? \Carbon\Carbon::parse($row->orderDate)->format('d-m-Y') : '-' }}</td>
                <td>{{ $row->shipmentNumber ?: '-' }}</td>
                <td>{{ $row->orderCode }}</td>
                <td>{{ $row->customerName }}</td>
                <td>{{ $row->companyName }}</td>
                <td>{{ $row->fleetCategory }}</td>
                <td>{{ $row->plateNumber }}</td>
                <td>{{ $row->driverName }}</td>
                <td>{{ $routeText }}</td>
                <td>{{ $row->orderStatusName }} / {{ $payLabel }}</td>
                <td style="text-align: right;">{{ (float) $row->omset }}</td>
                <td style="text-align: right;">{{ (float) $row->biaya }}</td>
                <td style="text-align: right;">{{ (float) $row->profit }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="11" style="font-weight: bold; background-color: #eef2ff; text-align: right;">
                GRAND TOTAL ({{ number_format($totals['totalOrders'], 0, ',', '.') }} Order)
            </td>
            <td style="font-weight: bold; background-color: #eef2ff; text-align: right;">{{ $totals['totalOmset'] }}</td>
            <td style="font-weight: bold; background-color: #eef2ff; text-align: right;">{{ $totals['totalBiaya'] }}</td>
            <td style="font-weight: bold; background-color: #eef2ff; text-align: right;">{{ $totals['totalProfit'] }}</td>
        </tr>
    </tfoot>
</table>
