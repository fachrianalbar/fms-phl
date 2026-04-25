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
    @endphp

    <table style="width: 100%; border-collapse: collapse; border: 1px solid black;">
        <thead>
            <tr>
                <th colspan="7" style="font-weight: bold; font-size: 18px; text-align: center; padding: 10px;">
                    Maintenance Fleet Report Data
                </th>
            </tr>
            <tr>
                <th colspan="7" style="text-align: left; padding: 6px; font-size: 12px;">
                    Fleet: {{ $fleetName ?? 'All' }} | Fleet Company: {{ $fleetCompanyName ?? 'All' }} | Date:
                    {{ $startLabel }} s/d {{ $endLabel }}
                </th>
            </tr>
            <tr>
                <th style="font-size: 12px; font-weight: bold; text-align: center;">No</th>
                <th style="font-size: 12px; font-weight: bold; text-align: center;">Plate Number</th>
                <th style="font-size: 12px; font-weight: bold; text-align: center;">Fleet Company</th>
                <th style="font-size: 12px; font-weight: bold; text-align: center;">Type</th>
                <th style="font-size: 12px; font-weight: bold; text-align: center;">Total Maintenance</th>
                <th style="font-size: 12px; font-weight: bold; text-align: center;">Total Qty</th>
                <th style="font-size: 12px; font-weight: bold; text-align: center;">Total Cost</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                @php
                    $grandMaintenance += (float) $row->totalMaintenance;
                    $grandQty += (float) $row->totalQty;
                    $grandCost += (float) $row->totalCost;
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $loop->iteration }}</td>
                    <td style="text-align: center;">{{ $row->plateNumber }}</td>
                    <td style="text-align: center;">{{ $row->fleetCompanyName ?: '-' }}</td>
                    <td style="text-align: center;">{{ $row->fleetCompanyType ?: 'Internal' }}</td>
                    <td style="text-align: center;">{{ number_format((float) $row->totalMaintenance, 0, ',', '.') }}</td>
                    <td style="text-align: center;">{{ number_format((float) $row->totalQty, 1, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format((float) $row->totalCost, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center;">No data found</td>
                </tr>
            @endforelse
            <tr>
                <td colspan="4" style="text-align: right; font-weight: bold;">TOTAL</td>
                <td style="text-align: center; font-weight: bold;">{{ number_format($grandMaintenance, 0, ',', '.') }}</td>
                <td style="text-align: center; font-weight: bold;">{{ number_format($grandQty, 1, ',', '.') }}</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($grandCost, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</body>

</html>
