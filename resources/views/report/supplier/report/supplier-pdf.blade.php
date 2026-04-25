<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Sparepart Purchase Report</title>
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

        .text-left {
            text-align: left;
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

        $grandPurchase = 0;
        $grandItem = 0;
        $grandQty = 0;
        $grandAmount = 0;
    @endphp

    <div class="container">
        <h1>SUPPLIER SPAREPART PURCHASE REPORT</h1>

        <div class="info">
            <p><strong>Supplier:</strong> {{ $supplierName ?? 'All' }}</p>
            <p><strong>Date:</strong> {{ $startLabel }} s/d {{ $endLabel }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Supplier</th>
                    <th>Total Purchase</th>
                    <th>Total Item</th>
                    <th>Total Qty</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    @php
                        $grandPurchase += (float) $row->totalPurchase;
                        $grandItem += (float) $row->totalItem;
                        $grandQty += (float) $row->totalQty;
                        $grandAmount += (float) $row->totalAmount;
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="text-left">{{ $row->supplierName }}</td>
                        <td>{{ number_format((float) $row->totalPurchase, 0, ',', '.') }}</td>
                        <td>{{ number_format((float) $row->totalItem, 0, ',', '.') }}</td>
                        <td>{{ number_format((float) $row->totalQty, 1, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format((float) $row->totalAmount, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">No data found</td>
                    </tr>
                @endforelse

                <tr class="bold">
                    <td colspan="2" class="text-right">TOTAL</td>
                    <td>{{ number_format($grandPurchase, 0, ',', '.') }}</td>
                    <td>{{ number_format($grandItem, 0, ',', '.') }}</td>
                    <td>{{ number_format($grandQty, 1, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($grandAmount, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>
