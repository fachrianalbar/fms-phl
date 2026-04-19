<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Maintenance Fleet Detail Report Data</title>
</head>

<body>
    @php
        $startLabel = $startDate ? \Carbon\Carbon::parse($startDate)->format('d-m-Y') : '-';
        $endLabel = $endDate ? \Carbon\Carbon::parse($endDate)->format('d-m-Y') : '-';

        $runningQty = 0;
        $runningCost = 0;
        $rowNo = 1;
    @endphp

    <table style="width: 100%; border-collapse: collapse; border: 1px solid black;">
        <thead>
            <tr>
                <th colspan="10" style="font-weight: bold; font-size: 18px; text-align: center; padding: 10px;">
                    Maintenance Fleet Detail Report Data
                </th>
            </tr>
            <tr>
                <th colspan="10" style="text-align: left; padding: 6px; font-size: 12px;">
                    Plate Number: {{ $fleet->plateNumber }} | Fleet Company: {{ $fleet->company?->name ?? '-' }} | Type:
                    {{ $fleet->company?->type ?? 'Internal' }} | Date: {{ $startLabel }} s/d {{ $endLabel }}
                </th>
            </tr>
            <tr>
                <th colspan="10" style="text-align: left; padding: 6px; font-size: 12px;">
                    Total Maintenance: {{ number_format($totalMaintenance, 0, ',', '.') }} | Total Qty:
                    {{ number_format($totalQty, 1, ',', '.') }} | Total Cost:
                    {{ number_format($totalCost, 0, ',', '.') }}
                </th>
            </tr>
            <tr>
                <th style="font-size: 12px; font-weight: bold; text-align: center;">No</th>
                <th style="font-size: 12px; font-weight: bold; text-align: center;">Maintenance Code</th>
                <th style="font-size: 12px; font-weight: bold; text-align: center;">Date</th>
                <th style="font-size: 12px; font-weight: bold; text-align: center;">Warehouse</th>
                <th style="font-size: 12px; font-weight: bold; text-align: center;">Item Code</th>
                <th style="font-size: 12px; font-weight: bold; text-align: center;">Item Name</th>
                <th style="font-size: 12px; font-weight: bold; text-align: center;">Supplier</th>
                <th style="font-size: 12px; font-weight: bold; text-align: center;">Qty</th>
                <th style="font-size: 12px; font-weight: bold; text-align: center;">Price</th>
                <th style="font-size: 12px; font-weight: bold; text-align: center;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $maintenance)
                @if ($maintenance->details->count() > 0)
                    @foreach ($maintenance->details as $detail)
                        @php
                            $qty = (float) $detail->qty;
                            $price = (float) ($detail->item->price ?? 0);
                            $subtotal = $qty * $price;

                            $runningQty += $qty;
                            $runningCost += $subtotal;
                        @endphp
                        <tr>
                            <td style="text-align: center;">{{ $rowNo++ }}</td>
                            <td style="text-align: center;">{{ $maintenance->code }}</td>
                            <td style="text-align: center;">
                                {{ \Carbon\Carbon::parse($maintenance->date)->format('d-m-Y') }}
                                {{ \Carbon\Carbon::parse($maintenance->time)->format('H:i') }}
                            </td>
                            <td style="text-align: center;">{{ $maintenance->warehouse?->name ?? '-' }}</td>
                            <td style="text-align: center;">{{ $detail->itemCode }}</td>
                            <td style="text-align: center;">{{ $detail->item?->name ?? '-' }}</td>
                            <td style="text-align: center;">{{ $detail->item?->supplier?->name ?? '-' }}</td>
                            <td style="text-align: center;">{{ number_format($qty, 1, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($price, 0, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td style="text-align: center;">{{ $rowNo++ }}</td>
                        <td style="text-align: center;">{{ $maintenance->code }}</td>
                        <td style="text-align: center;">
                            {{ \Carbon\Carbon::parse($maintenance->date)->format('d-m-Y') }}
                            {{ \Carbon\Carbon::parse($maintenance->time)->format('H:i') }}
                        </td>
                        <td style="text-align: center;">{{ $maintenance->warehouse?->name ?? '-' }}</td>
                        <td style="text-align: center;">-</td>
                        <td style="text-align: center;">-</td>
                        <td style="text-align: center;">-</td>
                        <td style="text-align: center;">0,0</td>
                        <td style="text-align: right;">0</td>
                        <td style="text-align: right;">0</td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="10" style="text-align: center;">No data found</td>
                </tr>
            @endforelse
            <tr>
                <td colspan="7" style="text-align: right; font-weight: bold;">TOTAL ITEM USAGE</td>
                <td style="text-align: center; font-weight: bold;">{{ number_format($runningQty, 1, ',', '.') }}</td>
                <td></td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($runningCost, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</body>

</html>
