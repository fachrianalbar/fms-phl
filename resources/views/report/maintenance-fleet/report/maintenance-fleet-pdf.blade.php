<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Fleet Report</title>
    <style>
        @page {
            margin: 10px;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fff;
        }

        .container {
            width: 100%;
            margin: 20px auto;
            padding: 10px;
        }

        h1 {
            text-align: center;
            font-size: 18px;
            margin-bottom: 10px;
        }

        .info {
            margin-bottom: 10px;
            font-size: 12px;
        }

        .info p {
            margin: 2px 0;
            line-height: 1.3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table th,
        table td {
            border: 1px solid #000;
            text-align: center;
            padding: 6px;
            font-size: 10px;
        }

        table th {
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }
    </style>
</head>

<body>
    @php
        $startLabel = $startDate ? \Carbon\Carbon::parse($startDate)->format('d-m-Y') : '-';
        $endLabel = $endDate ? \Carbon\Carbon::parse($endDate)->format('d-m-Y') : '-';

        $grandMaintenance = 0;
        $grandQty = 0;
        $grandCost = 0;
    @endphp

    <div class="container">
        <h1>MAINTENANCE PER FLEET REPORT</h1>

        <div class="info">
            <p><strong>Fleet:</strong> {{ $fleetName ?? 'All' }}</p>
            <p><strong>Fleet Company:</strong> {{ $fleetCompanyName ?? 'All' }}</p>
            <p><strong>Date:</strong> {{ $startLabel }} s/d {{ $endLabel }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Plate Number</th>
                    <th>Fleet Company</th>
                    <th>Type</th>
                    <th>Total Maintenance</th>
                    <th>Total Qty</th>
                    <th>Total Cost</th>
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
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $row->plateNumber }}</td>
                        <td>{{ $row->fleetCompanyName ?: '-' }}</td>
                        <td>{{ $row->fleetCompanyType ?: 'Internal' }}</td>
                        <td>{{ number_format((float) $row->totalMaintenance, 0, ',', '.') }}</td>
                        <td>{{ number_format((float) $row->totalQty, 1, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format((float) $row->totalCost, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">No data found</td>
                    </tr>
                @endforelse

                <tr class="bold">
                    <td colspan="4" class="text-right">TOTAL</td>
                    <td>{{ number_format($grandMaintenance, 0, ',', '.') }}</td>
                    <td>{{ number_format($grandQty, 1, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($grandCost, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>
