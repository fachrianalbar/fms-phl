<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Maintenance Fleet Report Data</title>
</head>

<body>
    @php
        $startLabel = $startDate ? \Carbon\Carbon::parse($startDate)->format('d-m-Y') : '-';
        $endLabel = $endDate ? \Carbon\Carbon::parse($endDate)->format('d-m-Y') : '-';

        $grandMaintenance = 0;
        $grandQty = 0;
        $grandCost = 0;
        $grandDue = 0;
        $grandDebt = 0;
    @endphp

    <table style="width: 100%; border-collapse: collapse; border: 1px solid black;">
        <thead>
            <tr>
                <th colspan="9" style="font-weight: bold; font-size: 18px; text-align: center; padding: 10px;">
                    Maintenance Fleet Report Data
                </th>
            </tr>
            <tr>
                <th colspan="9" style="text-align: left; padding: 6px; font-size: 12px;">
                    Fleet: {{ $fleetName ?? 'All' }} | Fleet Company: {{ $fleetCompanyName ?? 'All' }} | Date:
                    {{ $startLabel }} s/d {{ $endLabel }}
                </th>
            </tr>
            <tr>
                <th style="font-size: 12px; font-weight: bold; text-align: center;">No</th>
                <th style="font-size: 12px; font-weight: bold; text-align: center;">No Polisi</th>
                <th style="font-size: 12px; font-weight: bold; text-align: center;">Perusahaan</th>
                <th style="font-size: 12px; font-weight: bold; text-align: center;">Type</th>
                <th style="font-size: 12px; font-weight: bold; text-align: center;">Total Maint</th>
                <th style="font-size: 12px; font-weight: bold; text-align: center;">Total Qty</th>
                <th style="font-size: 12px; font-weight: bold; text-align: center;">Total Pembayaran</th>
                <th style="font-size: 12px; font-weight: bold; text-align: center;">Utang Jth Tempo</th>
                <th style="font-size: 12px; font-weight: bold; text-align: center;">Total Utang</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                @php
                    $grandMaintenance += (float) $row->totalMaintenance;
                    $grandQty += (float) $row->totalQty;
                    $grandCost += (float) $row->totalCost;
                    $grandDue += (float) $row->utangJatuhTempo;
                    $grandDebt += (float) $row->totalUtang;
                @endphp
                <tr>
                    <td style="text-align: left;">{{ $loop->iteration }}</td>
                    <td style="text-align: left;">{{ $row->plateNumber }}</td>
                    <td style="text-align: left;">{{ $row->fleetCompanyName ?: '-' }}</td>
                    <td style="text-align: left;">{{ $row->fleetCompanyType ?: 'Internal' }}</td>
                    <td style="text-align: right; mso-number-format:#,##0;">{{ (float) $row->totalMaintenance }}</td>
                    <td style="text-align: right; mso-number-format:#,##0.0;">{{ (float) $row->totalQty }}</td>
                    <td style="text-align: right; mso-number-format:#,##0;">{{ (float) $row->totalCost }}</td>
                    <td style="text-align: right; mso-number-format:#,##0;">{{ (float) $row->utangJatuhTempo }}</td>
                    <td style="text-align: right; mso-number-format:#,##0;">{{ (float) $row->totalUtang }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center;">No data found</td>
                </tr>
            @endforelse
            <tr>
                <td colspan="4" style="text-align: right; font-weight: bold;">TOTAL</td>
                <td style="text-align: right; font-weight: bold; mso-number-format:#,##0;">{{ $grandMaintenance }}</td>
                <td style="text-align: right; font-weight: bold; mso-number-format:#,##0.0;">{{ $grandQty }}</td>
                <td style="text-align: right; font-weight: bold; mso-number-format:#,##0;">{{ $grandCost }}</td>
                <td style="text-align: right; font-weight: bold; mso-number-format:#,##0;">{{ $grandDue }}</td>
                <td style="text-align: right; font-weight: bold; mso-number-format:#,##0;">{{ $grandDebt }}</td>
            </tr>
        </tbody>
    </table>
</body>

</html>
