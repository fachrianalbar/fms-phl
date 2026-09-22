<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Detail Aging Supplier</title>
</head>

<body>
    @php
        $startLabel = $startDate ? \Carbon\Carbon::parse($startDate)->format('d-m-Y') : '-';
        $endLabel = $endDate ? \Carbon\Carbon::parse($endDate)->format('d-m-Y') : '-';
        $today = \Carbon\Carbon::today();
        $no = 1;
        $detailTotalQty = 0;
        $detailTotalAmount = 0;
    @endphp

    <table style="width: 100%; border-collapse: collapse; border: 1px solid black;">
        <thead>
            <tr>
                <th colspan="15" style="font-weight: bold; font-size: 18px; text-align: center; padding: 10px;">
                    Detail Aging Supplier
                </th>
            </tr>
            <tr>
                <th colspan="15" style="text-align: left; padding: 6px; font-size: 12px;">
                    Supplier: {{ $supplier->name }} | Date: {{ $startLabel }} s/d {{ $endLabel }}
                </th>
            </tr>
            <tr>
                <th colspan="15" style="text-align: left; padding: 6px; font-size: 12px;">
                    Total Purchase: {{ number_format($totalPurchase, 0, ',', '.') }} |
                    Total Item: {{ number_format($totalItem, 0, ',', '.') }} |
                    Total Qty: {{ number_format($totalQty, 1, ',', '.') }} |
                    Tagihan: Rp {{ number_format($totalAmount, 0, ',', '.') }} |
                    Terbayar: Rp {{ number_format($totalPaid, 0, ',', '.') }} |
                    Sisa Hutang: Rp {{ number_format($totalRemaining, 0, ',', '.') }}
                </th>
            </tr>
            <tr>
                <th style="font-weight: bold; text-align: center;">No</th>
                <th style="font-weight: bold; text-align: center;">Purchase Code</th>
                <th style="font-weight: bold; text-align: center;">Date</th>
                <th style="font-weight: bold; text-align: center;">Effective Due Date</th>
                <th style="font-weight: bold; text-align: center;">Due Status</th>
                <th style="font-weight: bold; text-align: center;">Warehouse</th>
                <th style="font-weight: bold; text-align: center;">Item Code</th>
                <th style="font-weight: bold; text-align: center;">Item Name</th>
                <th style="font-weight: bold; text-align: center;">Description</th>
                <th style="font-weight: bold; text-align: center;">Qty</th>
                <th style="font-weight: bold; text-align: center;">Price</th>
                <th style="font-weight: bold; text-align: center;">Subtotal</th>
                <th style="font-weight: bold; text-align: center;">Tagihan</th>
                <th style="font-weight: bold; text-align: center;">Terbayar</th>
                <th style="font-weight: bold; text-align: center;">Sisa Hutang</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $purchase)
                @php
                    $dueDate = \Carbon\Carbon::parse($purchase->effectiveDueDate)->startOfDay();
                    $remaining = (float) $purchase->remainingAmount;
                    $daysToDue = (int) $today->diffInDays($dueDate, false);
                    $isOverdue = $remaining > 0 && $daysToDue < 0;
                    $isDueSoon = $remaining > 0 && $daysToDue >= 0 && $daysToDue <= 7;
                    $rowStyle = $isOverdue
                        ? 'background-color: #f4cccc;'
                        : ($isDueSoon ? 'background-color: #fff2cc;' : '');
                    $dueLabel = $dueDate->format('d-m-Y');
                    if ($purchase->dueDateSource === 'purchase_date') {
                        $dueLabel .= ' (estimasi tgl PO)';
                    } elseif ($purchase->dueDateSource === 'today') {
                        $dueLabel .= ' (fallback hari ini)';
                    }
                    if ($remaining <= 0) {
                        $dueStatus = 'Lunas';
                    } elseif ($daysToDue < 0) {
                        $dueStatus = 'Overdue ' . abs($daysToDue) . ' hari';
                    } elseif ($daysToDue === 0) {
                        $dueStatus = 'Jatuh tempo hari ini';
                    } elseif ($daysToDue <= 7) {
                        $dueStatus = 'Jatuh tempo ' . $daysToDue . ' hari';
                    } else {
                        $dueStatus = 'Belum jatuh tempo';
                    }
                @endphp

                @if ($purchase->details->count() > 0)
                    @foreach ($purchase->details as $detail)
                        @php
                            $qty = (float) (($detail->receivedQty ?: $detail->qty) ?? 0);
                            $price = (float) ($detail->price ?? 0);
                            $subtotal = $price * $qty;
                            $detailTotalQty += $qty;
                            $detailTotalAmount += $subtotal;
                        @endphp
                        <tr style="{{ $rowStyle }}">
                            <td style="text-align: center;">{{ $no++ }}</td>
                            <td style="text-align: center;">{{ $purchase->code }}</td>
                            <td style="text-align: center;">
                                {{ $purchase->date ? \Carbon\Carbon::parse($purchase->date)->format('d-m-Y') : '-' }}
                                {{ $purchase->time ? \Carbon\Carbon::parse($purchase->time)->format('H:i') : '' }}
                            </td>
                            <td style="text-align: center;">{{ $dueLabel }}</td>
                            <td style="text-align: center; font-weight: bold;">{{ $dueStatus }}</td>
                            <td style="text-align: left;">{{ $purchase->warehouse?->name ?? '-' }}</td>
                            <td style="text-align: center;">{{ $detail->itemCode }}</td>
                            <td style="text-align: left;">{{ $detail->item?->name ?? '-' }}</td>
                            <td style="text-align: left;">{{ $detail->description ?? '-' }}</td>
                            <td style="text-align: center;">{{ number_format($qty, 1, ',', '.') }}</td>
                            <td style="text-align: right;">Rp {{ number_format($price, 0, ',', '.') }}</td>
                            <td style="text-align: right;">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                            <td style="text-align: right;">{{ $loop->first ? 'Rp ' . number_format((float) $purchase->billingAmount, 0, ',', '.') : '' }}</td>
                            <td style="text-align: right;">{{ $loop->first ? 'Rp ' . number_format((float) $purchase->paidAmountValue, 0, ',', '.') : '' }}</td>
                            <td style="text-align: right; font-weight: bold;">{{ $loop->first ? 'Rp ' . number_format($remaining, 0, ',', '.') : '' }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr style="{{ $rowStyle }}">
                        <td style="text-align: center;">{{ $no++ }}</td>
                        <td style="text-align: center;">{{ $purchase->code }}</td>
                        <td style="text-align: center;">{{ $purchase->date ? \Carbon\Carbon::parse($purchase->date)->format('d-m-Y') : '-' }}</td>
                        <td style="text-align: center;">{{ $dueLabel }}</td>
                        <td style="text-align: center; font-weight: bold;">{{ $dueStatus }}</td>
                        <td style="text-align: left;">{{ $purchase->warehouse?->name ?? '-' }}</td>
                        <td style="text-align: center;">-</td>
                        <td style="text-align: left;">-</td>
                        <td style="text-align: left;">-</td>
                        <td style="text-align: center;">0,0</td>
                        <td style="text-align: right;">Rp 0</td>
                        <td style="text-align: right;">Rp 0</td>
                        <td style="text-align: right;">Rp {{ number_format((float) $purchase->billingAmount, 0, ',', '.') }}</td>
                        <td style="text-align: right;">Rp {{ number_format((float) $purchase->paidAmountValue, 0, ',', '.') }}</td>
                        <td style="text-align: right; font-weight: bold;">Rp {{ number_format($remaining, 0, ',', '.') }}</td>
                    </tr>
                @endif
            @empty
                <tr><td colspan="15" style="text-align: center;">No data found</td></tr>
            @endforelse
            <tr style="font-weight: bold; background-color: #d9eaf7;">
                <td colspan="9" style="text-align: right;">TOTAL</td>
                <td style="text-align: center;">{{ number_format($detailTotalQty, 1, ',', '.') }}</td>
                <td></td>
                <td style="text-align: right;">Rp {{ number_format($detailTotalAmount, 0, ',', '.') }}</td>
                <td style="text-align: right;">Rp {{ number_format($totalAmount, 0, ',', '.') }}</td>
                <td style="text-align: right;">Rp {{ number_format($totalPaid, 0, ',', '.') }}</td>
                <td style="text-align: right;">Rp {{ number_format($totalRemaining, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</body>

</html>
