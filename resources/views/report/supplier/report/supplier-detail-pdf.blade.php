<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Aging Supplier</title>
    <style>
        @page { margin: 8px; }
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #fff; }
        .container { width: 100%; margin: 8px auto; }
        h1 { text-align: center; font-size: 15px; margin: 0 0 7px; }
        .info, .summary { margin-bottom: 7px; font-size: 9px; }
        .info p, .summary p { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; text-align: center; padding: 3px; font-size: 6.8px; }
        th { font-weight: bold; background: #e9ecef; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .due-soon { background: #fff2cc; }
        .overdue { background: #f4cccc; }
        .total { background: #d9eaf7; }
    </style>
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

    <div class="container">
        <h1>SUPPLIER AGING HUTANG DETAIL</h1>
        <div class="info">
            <p><strong>Supplier:</strong> {{ $supplier->name }}</p>
            <p><strong>Date:</strong> {{ $startLabel }} s/d {{ $endLabel }}</p>
        </div>
        <div class="summary">
            <p>
                <strong>Purchase:</strong> {{ number_format($totalPurchase, 0, ',', '.') }} |
                <strong>Item:</strong> {{ number_format($totalItem, 0, ',', '.') }} |
                <strong>Qty:</strong> {{ number_format($totalQty, 1, ',', '.') }} |
                <strong>Tagihan:</strong> Rp {{ number_format($totalAmount, 0, ',', '.') }} |
                <strong>Terbayar:</strong> Rp {{ number_format($totalPaid, 0, ',', '.') }} |
                <strong>Sisa:</strong> Rp {{ number_format($totalRemaining, 0, ',', '.') }}
            </p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Purchase Code</th>
                    <th>Date</th>
                    <th>Effective Due Date</th>
                    <th>Due Status</th>
                    <th>Warehouse</th>
                    <th>Item Code</th>
                    <th>Item Name</th>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Subtotal</th>
                    <th>Tagihan</th>
                    <th>Terbayar</th>
                    <th>Sisa Hutang</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $purchase)
                    @php
                        $dueDate = \Carbon\Carbon::parse($purchase->effectiveDueDate)->startOfDay();
                        $remaining = (float) $purchase->remainingAmount;
                        $daysToDue = (int) $today->diffInDays($dueDate, false);
                        $rowClass = $remaining > 0 && $daysToDue < 0
                            ? 'overdue'
                            : ($remaining > 0 && $daysToDue >= 0 && $daysToDue <= 7 ? 'due-soon' : '');
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
                            <tr class="{{ $rowClass }}">
                                <td>{{ $no++ }}</td>
                                <td>{{ $purchase->code }}</td>
                                <td>
                                    {{ $purchase->date ? \Carbon\Carbon::parse($purchase->date)->format('d-m-Y') : '-' }}
                                    {{ $purchase->time ? \Carbon\Carbon::parse($purchase->time)->format('H:i') : '' }}
                                </td>
                                <td>{{ $dueLabel }}</td>
                                <td class="bold">{{ $dueStatus }}</td>
                                <td class="text-left">{{ $purchase->warehouse?->name ?? '-' }}</td>
                                <td>{{ $detail->itemCode }}</td>
                                <td class="text-left">{{ $detail->item?->name ?? '-' }}</td>
                                <td class="text-left">{{ $detail->description ?? '-' }}</td>
                                <td>{{ number_format($qty, 1, ',', '.') }}</td>
                                <td class="text-right">Rp {{ number_format($price, 0, ',', '.') }}</td>
                                <td class="text-right">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                                <td class="text-right">{{ $loop->first ? 'Rp ' . number_format((float) $purchase->billingAmount, 0, ',', '.') : '' }}</td>
                                <td class="text-right">{{ $loop->first ? 'Rp ' . number_format((float) $purchase->paidAmountValue, 0, ',', '.') : '' }}</td>
                                <td class="text-right bold">{{ $loop->first ? 'Rp ' . number_format($remaining, 0, ',', '.') : '' }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr class="{{ $rowClass }}">
                            <td>{{ $no++ }}</td>
                            <td>{{ $purchase->code }}</td>
                            <td>{{ $purchase->date ? \Carbon\Carbon::parse($purchase->date)->format('d-m-Y') : '-' }}</td>
                            <td>{{ $dueLabel }}</td>
                            <td class="bold">{{ $dueStatus }}</td>
                            <td class="text-left">{{ $purchase->warehouse?->name ?? '-' }}</td>
                            <td>-</td><td>-</td><td>-</td><td>0,0</td><td class="text-right">Rp 0</td><td class="text-right">Rp 0</td>
                            <td class="text-right">Rp {{ number_format((float) $purchase->billingAmount, 0, ',', '.') }}</td>
                            <td class="text-right">Rp {{ number_format((float) $purchase->paidAmountValue, 0, ',', '.') }}</td>
                            <td class="text-right bold">Rp {{ number_format($remaining, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                @empty
                    <tr><td colspan="15">No data found</td></tr>
                @endforelse
                <tr class="bold total">
                    <td colspan="9" class="text-right">TOTAL</td>
                    <td>{{ number_format($detailTotalQty, 1, ',', '.') }}</td>
                    <td></td>
                    <td class="text-right">Rp {{ number_format($detailTotalAmount, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($totalAmount, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($totalPaid, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($totalRemaining, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>
