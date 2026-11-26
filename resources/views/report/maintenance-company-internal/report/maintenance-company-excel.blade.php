<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Perawatan Perusahaan</title>
</head>
<body>
    @php
        $startLabel = $startDate ? \Carbon\Carbon::parse($startDate)->format('d-m-Y') : '-';
        $endLabel = $endDate ? \Carbon\Carbon::parse($endDate)->format('d-m-Y') : '-';
        $grandFleet = 0;
        $grandMaintenance = 0;
        $grandQty = 0.0;
        $grandCost = 0.0;
    @endphp
    <table>
        <thead>
            <tr>
                <th colspan="7" style="font-weight: bold; text-align: center;">LAPORAN PERAWATAN PER PERUSAHAAN (INTERNAL)</th>
            </tr>
            <tr>
                <th colspan="7" style="text-align: left;">
                    Perusahaan: {{ $fleetCompanyName ?? 'Semua' }} | Periode: {{ $startLabel }} s/d {{ $endLabel }}
                </th>
            </tr>
            <tr>
                <th style="font-weight: bold; text-align: center;">No</th>
                <th style="font-weight: bold; text-align: center;">Perusahaan Armada</th>
                <th style="font-weight: bold; text-align: center;">Tipe</th>
                <th style="font-weight: bold; text-align: center;">Total Armada</th>
                <th style="font-weight: bold; text-align: center;">Total Perawatan</th>
                <th style="font-weight: bold; text-align: center;">Total Qty</th>
                <th style="font-weight: bold; text-align: center;">Total Biaya</th>
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
                    <td style="text-align: center;">{{ $loop->iteration }}</td>
                    <td>{{ $row->fleetCompanyName ?: '-' }}</td>
                    <td style="text-align: center;">{{ $row->fleetCompanyType ?: 'Internal' }}</td>
                    <td style="text-align: right;">{{ (int) $row->totalFleet }}</td>
                    <td style="text-align: right;">{{ (int) $row->totalMaintenance }}</td>
                    <td style="text-align: right;">{{ (float) $row->totalQty }}</td>
                    <td style="text-align: right;">{{ (float) $row->totalCost }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center;">Tidak ada data</td>
                </tr>
            @endforelse
            <tr style="font-weight: bold;">
                <td colspan="3" style="text-align: right;">TOTAL</td>
                <td style="text-align: right;">{{ $grandFleet }}</td>
                <td style="text-align: right;">{{ $grandMaintenance }}</td>
                <td style="text-align: right;">{{ $grandQty }}</td>
                <td style="text-align: right;">{{ $grandCost }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
