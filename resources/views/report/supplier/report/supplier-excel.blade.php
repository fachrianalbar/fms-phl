<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Supplier Sparepart Purchase Report</title>
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

    <table style="width: 100%; border-collapse: collapse; border: 1px solid black;">
        <thead>
            <tr>
                <th colspan="6" style="font-weight: bold; font-size: 18px; text-align: center; padding: 10px;">
                    Supplier Sparepart Purchase Report
                </th>
            </tr>
            <tr>
                <th colspan="6" style="text-align: left; padding: 6px; font-size: 12px;">
                    Supplier: {{ $supplierName ?? 'All' }} | Date: {{ $startLabel }} s/d {{ $endLabel }}
                </th>
            </tr>
            <tr>
                <th style="font-size: 12px; font-weight: bold; text-align: center;">No</th>
                <th style="font-size: 12px; font-weight: bold; text-align: center;">Supplier</th>
                <th style="font-size: 12px; font-weight: bold; text-align: center;">Total Purchase</th>
                <th style="font-size: 12px; font-weight: bold; text-align: center;">Total Item</th>
                <th style="font-size: 12px; font-weight: bold; text-align: center;">Total Qty</th>
                <th style="font-size: 12px; font-weight: bold; text-align: center;">Total Amount</th>
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
                    <td style="text-align: center;">{{ $loop->iteration }}</td>
                    <td style="text-align: left;">{{ $row->supplierName }}</td>
                    <td style="text-align: center;">{{ number_format((float) $row->totalPurchase, 0, ',', '.') }}</td>
                    <td style="text-align: center;">{{ number_format((float) $row->totalItem, 0, ',', '.') }}</td>
                    <td style="text-align: center;">{{ number_format((float) $row->totalQty, 1, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format((float) $row->totalAmount, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center;">No data found</td>
                </tr>
            @endforelse
            <tr>
                <td colspan="2" style="text-align: right; font-weight: bold;">TOTAL</td>
                <td style="text-align: center; font-weight: bold;">{{ number_format($grandPurchase, 0, ',', '.') }}</td>
                <td style="text-align: center; font-weight: bold;">{{ number_format($grandItem, 0, ',', '.') }}</td>
                <td style="text-align: center; font-weight: bold;">{{ number_format($grandQty, 1, ',', '.') }}</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($grandAmount, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</body>

</html>
