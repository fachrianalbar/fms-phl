<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Supplier Aging Hutang</title>
</head>

<body>
    @php
        $startLabel = $startDate ? \Carbon\Carbon::parse($startDate)->format('d-m-Y') : '-';
        $endLabel = $endDate ? \Carbon\Carbon::parse($endDate)->format('d-m-Y') : '-';
        $totals = [
            'purchase' => 0,
            'item' => 0,
            'qty' => 0,
            'billing' => 0,
            'paid' => 0,
            'remaining' => 0,
            'dueSoon' => 0,
            'dueSoonCount' => 0,
            'overdue' => 0,
            'overdueCount' => 0,
            'notDue' => 0,
            'aging0To30' => 0,
            'aging31To60' => 0,
            'aging61To90' => 0,
            'agingOver90' => 0,
            'unpaid' => 0,
        ];
    @endphp

    <table style="width: 100%; border-collapse: collapse; border: 1px solid black;">
        <thead>
            <tr>
                <th colspan="16" style="font-weight: bold; font-size: 18px; text-align: center; padding: 10px;">
                    Supplier Aging Hutang
                </th>
            </tr>
            <tr>
                <th colspan="16" style="text-align: left; padding: 6px; font-size: 12px;">
                    Supplier: {{ $supplierName ?? 'All' }} | Date: {{ $startLabel }} s/d {{ $endLabel }}
                </th>
            </tr>
            <tr>
                <th style="font-weight: bold; text-align: center;">No</th>
                <th style="font-weight: bold; text-align: center;">Supplier</th>
                <th style="font-weight: bold; text-align: center;">Total Purchase</th>
                <th style="font-weight: bold; text-align: center;">Total Item</th>
                <th style="font-weight: bold; text-align: center;">Total Qty</th>
                <th style="font-weight: bold; text-align: center;">Total Tagihan</th>
                <th style="font-weight: bold; text-align: center;">Total Terbayar</th>
                <th style="font-weight: bold; text-align: center;">Sisa Hutang</th>
                <th style="font-weight: bold; text-align: center;">JT &lt;=7 Hari</th>
                <th style="font-weight: bold; text-align: center;">Overdue</th>
                <th style="font-weight: bold; text-align: center;">Belum JT</th>
                <th style="font-weight: bold; text-align: center;">0-30</th>
                <th style="font-weight: bold; text-align: center;">31-60</th>
                <th style="font-weight: bold; text-align: center;">61-90</th>
                <th style="font-weight: bold; text-align: center;">&gt;90</th>
                <th style="font-weight: bold; text-align: center;">Belum Lunas</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                @php
                    $totals['purchase'] += (int) $row->totalPurchase;
                    $totals['item'] += (int) $row->totalItem;
                    $totals['qty'] += (float) $row->totalQty;
                    $totals['billing'] += (float) $row->totalBilling;
                    $totals['paid'] += (float) $row->totalPaid;
                    $totals['remaining'] += (float) $row->totalRemaining;
                    $totals['dueSoon'] += (float) $row->dueSoonAmount;
                    $totals['dueSoonCount'] += (int) $row->dueSoonCount;
                    $totals['overdue'] += (float) $row->overdueAmount;
                    $totals['overdueCount'] += (int) $row->overdueCount;
                    $totals['notDue'] += (float) $row->notDueAmount;
                    $totals['aging0To30'] += (float) $row->aging0To30;
                    $totals['aging31To60'] += (float) $row->aging31To60;
                    $totals['aging61To90'] += (float) $row->aging61To90;
                    $totals['agingOver90'] += (float) $row->agingOver90;
                    $totals['unpaid'] += (int) $row->unpaidCount;
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $loop->iteration }}</td>
                    <td style="text-align: left;">{{ $row->supplierName }}</td>
                    <td style="text-align: center;">{{ number_format((int) $row->totalPurchase, 0, ',', '.') }}</td>
                    <td style="text-align: center;">{{ number_format((int) $row->totalItem, 0, ',', '.') }}</td>
                    <td style="text-align: center;">{{ number_format((float) $row->totalQty, 1, ',', '.') }}</td>
                    <td style="text-align: right;">Rp {{ number_format((float) $row->totalBilling, 0, ',', '.') }}</td>
                    <td style="text-align: right;">Rp {{ number_format((float) $row->totalPaid, 0, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">Rp {{ number_format((float) $row->totalRemaining, 0, ',', '.') }}</td>
                    <td style="text-align: right; background-color: #fff2cc;">Rp {{ number_format((float) $row->dueSoonAmount, 0, ',', '.') }} ({{ (int) $row->dueSoonCount }} PO)</td>
                    <td style="text-align: right; background-color: #f4cccc;">Rp {{ number_format((float) $row->overdueAmount, 0, ',', '.') }} ({{ (int) $row->overdueCount }} PO)</td>
                    <td style="text-align: right;">Rp {{ number_format((float) $row->notDueAmount, 0, ',', '.') }}</td>
                    <td style="text-align: right;">Rp {{ number_format((float) $row->aging0To30, 0, ',', '.') }}</td>
                    <td style="text-align: right;">Rp {{ number_format((float) $row->aging31To60, 0, ',', '.') }}</td>
                    <td style="text-align: right;">Rp {{ number_format((float) $row->aging61To90, 0, ',', '.') }}</td>
                    <td style="text-align: right;">Rp {{ number_format((float) $row->agingOver90, 0, ',', '.') }}</td>
                    <td style="text-align: center;">{{ number_format((int) $row->unpaidCount, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="16" style="text-align: center;">No data found</td>
                </tr>
            @endforelse
            <tr style="font-weight: bold; background-color: #d9eaf7;">
                <td colspan="2" style="text-align: right;">TOTAL</td>
                <td style="text-align: center;">{{ number_format($totals['purchase'], 0, ',', '.') }}</td>
                <td style="text-align: center;">{{ number_format($totals['item'], 0, ',', '.') }}</td>
                <td style="text-align: center;">{{ number_format($totals['qty'], 1, ',', '.') }}</td>
                <td style="text-align: right;">Rp {{ number_format($totals['billing'], 0, ',', '.') }}</td>
                <td style="text-align: right;">Rp {{ number_format($totals['paid'], 0, ',', '.') }}</td>
                <td style="text-align: right;">Rp {{ number_format($totals['remaining'], 0, ',', '.') }}</td>
                <td style="text-align: right;">Rp {{ number_format($totals['dueSoon'], 0, ',', '.') }} ({{ $totals['dueSoonCount'] }} PO)</td>
                <td style="text-align: right;">Rp {{ number_format($totals['overdue'], 0, ',', '.') }} ({{ $totals['overdueCount'] }} PO)</td>
                <td style="text-align: right;">Rp {{ number_format($totals['notDue'], 0, ',', '.') }}</td>
                <td style="text-align: right;">Rp {{ number_format($totals['aging0To30'], 0, ',', '.') }}</td>
                <td style="text-align: right;">Rp {{ number_format($totals['aging31To60'], 0, ',', '.') }}</td>
                <td style="text-align: right;">Rp {{ number_format($totals['aging61To90'], 0, ',', '.') }}</td>
                <td style="text-align: right;">Rp {{ number_format($totals['agingOver90'], 0, ',', '.') }}</td>
                <td style="text-align: center;">{{ number_format($totals['unpaid'], 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</body>

</html>
