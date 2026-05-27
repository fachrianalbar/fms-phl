<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Maintenance Item</title>
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
            text-transform: uppercase;
        }

        .info {
            margin-bottom: 15px;
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
            padding: 6px;
            font-size: 9px;
            word-wrap: break-word;
        }

        table th {
            font-weight: bold;
            background-color: #f2f2f2;
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
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

        $totalQty = 0;
        $totalCost = 0;
    @endphp

    <div class="container">
        <h1>REPORT MAINTENANCE ITEM</h1>

        <div class="info">
            <p><strong>Periode Tanggal:</strong> {{ $startLabel }} s/d {{ $endLabel }}</p>
            <p><strong>Gudang (Warehouse):</strong> {{ $warehouseName ?? 'Semua Gudang' }}</p>
            <p><strong>Kendaraan (Fleet):</strong> {{ $fleetName ?? 'Semua Kendaraan' }}</p>
            <p><strong>Item:</strong> {{ $itemName ?? 'Semua Item' }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Warehouse</th>
                    <th>Kendaraan</th>
                    <th>Item</th>
                    <th>Description Item</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Total Harga</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    @php
                        $totalQty += (float) $row->qty;
                        $totalCost += (float) $row->total;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td class="text-center">{{ $row->maintenance->date ? \Carbon\Carbon::parse($row->maintenance->date)->format('d-m-Y') : '-' }}</td>
                        <td class="text-left">{{ $row->maintenance->warehouse->name ?? '-' }}</td>
                        <td class="text-center">{{ $row->maintenance->fleet->plateNumber ?? '-' }}</td>
                        <td class="text-left">{{ $row->item->name ?? '-' }}</td>
                        <td class="text-left">{{ $row->description ?? '-' }}</td>
                        <td class="text-right">{{ number_format((float) $row->qty, 1, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format((float) $row->price, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format((float) $row->total, 0, ',', '.') }}</td>
                        <td class="text-center">{{ $row->created_at ? $row->created_at->format('d-m-Y H:i') : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center">No data found</td>
                    </tr>
                @endforelse

                @if ($rows->count() > 0)
                    <tr class="bold">
                        <td colspan="6" class="text-right">TOTAL</td>
                        <td class="text-right">{{ number_format($totalQty, 1, ',', '.') }}</td>
                        <td></td>
                        <td class="text-right">Rp {{ number_format($totalCost, 0, ',', '.') }}</td>
                        <td></td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</body>

</html>
