<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Supplier Sparepart Purchase Detail Report</title>
</head>

<body>
    @php
        $startLabel = $startDate ? \Carbon\Carbon::parse($startDate)->format('d-m-Y') : '-';
        $endLabel = $endDate ? \Carbon\Carbon::parse($endDate)->format('d-m-Y') : '-';

        $no = 1;
        $detailTotalQty = 0;
        $detailTotalAmount = 0;
    @endphp

    <table style="width: 100%; border-collapse: collapse; border: 1px solid black;">
        <thead>
            <tr>
                <th colspan="11" style="font-weight: bold; font-size: 18px; text-align: center; padding: 10px;">
                    Supplier Sparepart Purchase Detail Report
                </th>
            </tr>
            <tr>
                <th colspan="11" style="text-align: left; padding: 6px; font-size: 12px;">
                    Supplier: {{ $supplier->name }} | Date: {{ $startLabel }} s/d {{ $endLabel }}
                </th>
            </tr>
            <tr>
                <th colspan="11" style="text-align: left; padding: 6px; font-size: 12px;">
                    Total Purchase: {{ number_format($totalPurchase, 0, ',', '.') }} |
                    Total Item: {{ number_format($totalItem, 0, ',', '.') }} |
                    Total Qty: {{ number_format($totalQty, 1, ',', '.') }} |
                    Total Amount: {{ number_format($totalAmount, 0, ',', '.') }}
                </th>
            </tr>
            <tr>
                <th style="font-size: 12px; font-weight: bold; text-align: center;">No</th>
                <th style="font-size: 12px; font-weight: bold; text-align: center;">Purchase Code</th>
                <th style="font-size: 12px; font-weight: bold; text-align: center;">Date</th>
                <th style="font-size: 12px; font-weight: bold; text-align: center;">Due Date</th>
                <th style="font-size: 12px; font-weight: bold; text-align: center;">Warehouse</th>
                <th style="font-size: 12px; font-weight: bold; text-align: center;">Item Code</th>
                <th style="font-size: 12px; font-weight: bold; text-align: center;">Item Name</th>
                <th style="font-size: 12px; font-weight: bold; text-align: center;">Description</th>
                <th style="font-size: 12px; font-weight: bold; text-align: center;">Qty</th>
                <th style="font-size: 12px; font-weight: bold; text-align: center;">Price</th>
                <th style="font-size: 12px; font-weight: bold; text-align: center;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $purchase)
                @if ($purchase->details->count() > 0)
                    @foreach ($purchase->details as $detail)
                        @php
                            $qty = (float) ($detail->receivedQty ?: $detail->qty);
                            $price = (float) ($detail->price ?? 0);
                            $subtotal = $price * $qty;

                            $detailTotalQty += $qty;
                            $detailTotalAmount += $subtotal;
                        @endphp
                        <tr>
                            <td style="text-align: center;">{{ $no++ }}</td>
                            <td style="text-align: center;">{{ $purchase->code }}</td>
                            <td style="text-align: center;">
                                {{ $purchase->date ? \Carbon\Carbon::parse($purchase->date)->format('d-m-Y') : '-' }}
                                {{ $purchase->time ? \Carbon\Carbon::parse($purchase->time)->format('H:i') : '' }}
                            </td>
                            <td style="text-align: center;">
                                {{ $purchase->dueDate ? \Carbon\Carbon::parse($purchase->dueDate)->format('d-m-Y') : '-' }}
                            </td>
                            <td style="text-align: left;">{{ $purchase->warehouse?->name ?? '-' }}</td>
                            <td style="text-align: center;">{{ $detail->itemCode }}</td>
                            <td style="text-align: left;">{{ $detail->item?->name ?? '-' }}</td>
                            <td style="text-align: left;">{{ $detail->description ?? '-' }}</td>
                            <td style="text-align: center;">{{ number_format($qty, 1, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($price, 0, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td style="text-align: center;">{{ $no++ }}</td>
                        <td style="text-align: center;">{{ $purchase->code }}</td>
                        <td style="text-align: center;">
                            {{ $purchase->date ? \Carbon\Carbon::parse($purchase->date)->format('d-m-Y') : '-' }}
                            {{ $purchase->time ? \Carbon\Carbon::parse($purchase->time)->format('H:i') : '' }}
                        </td>
                        <td style="text-align: center;">
                            {{ $purchase->dueDate ? \Carbon\Carbon::parse($purchase->dueDate)->format('d-m-Y') : '-' }}
                        </td>
                        <td style="text-align: left;">{{ $purchase->warehouse?->name ?? '-' }}</td>
                        <td style="text-align: center;">-</td>
                        <td style="text-align: left;">-</td>
                        <td style="text-align: left;">-</td>
                        <td style="text-align: center;">0,0</td>
                        <td style="text-align: right;">0</td>
                        <td style="text-align: right;">0</td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="11" style="text-align: center;">No data found</td>
                </tr>
            @endforelse
            <tr>
                <td colspan="8" style="text-align: right; font-weight: bold;">TOTAL ITEM USAGE</td>
                <td style="text-align: center; font-weight: bold;">{{ number_format($detailTotalQty, 1, ',', '.') }}
                </td>
                <td></td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($detailTotalAmount, 0, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>
</body>

</html>
