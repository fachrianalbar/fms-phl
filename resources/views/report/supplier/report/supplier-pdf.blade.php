<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Aging Hutang</title>
    <style>
        @page { margin: 8px; }
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #fff; }
        .container { width: 100%; margin: 8px auto; }
        h1 { text-align: center; font-size: 16px; margin: 0 0 8px; }
        .info { margin-bottom: 8px; font-size: 10px; }
        .info p { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 3px; font-size: 7px; text-align: center; }
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

        $totals = [
            'purchase' => 0, 'item' => 0, 'qty' => 0, 'billing' => 0, 'paid' => 0,
            'remaining' => 0, 'dueSoon' => 0, 'dueSoonCount' => 0, 'overdue' => 0,
            'overdueCount' => 0, 'notDue' => 0, 'aging0To30' => 0, 'aging31To60' => 0,
            'aging61To90' => 0, 'agingOver90' => 0, 'unpaid' => 0,
        ];
    @endphp

    <div class="container">
        <h1>SUPPLIER AGING HUTANG</h1>
        <div class="info">
            <p><strong>Supplier:</strong> {{ $supplierName ?? 'All' }}</p>
            <p><strong>Date:</strong> {{ $startLabel }} s/d {{ $endLabel }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Supplier</th>
                    <th>Purchase</th>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Tagihan</th>
                    <th>Terbayar</th>
                    <th>Sisa Hutang</th>
                    <th>JT &lt;=7 Hari</th>
                    <th>Overdue</th>
                    <th>Belum JT</th>
                    <th>0-30</th>
                    <th>31-60</th>
                    <th>61-90</th>
                    <th>&gt;90</th>
                    <th>Belum Lunas</th>
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
                        <td>{{ $loop->iteration }}</td>
                        <td class="text-left">{{ $row->supplierName }}</td>
                        <td>{{ number_format((int) $row->totalPurchase, 0, ',', '.') }}</td>
                        <td>{{ number_format((int) $row->totalItem, 0, ',', '.') }}</td>
                        <td>{{ number_format((float) $row->totalQty, 1, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format((float) $row->totalBilling, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format((float) $row->totalPaid, 0, ',', '.') }}</td>
                        <td class="text-right bold">Rp {{ number_format((float) $row->totalRemaining, 0, ',', '.') }}</td>
                        <td class="text-right due-soon">Rp {{ number_format((float) $row->dueSoonAmount, 0, ',', '.') }}<br>({{ (int) $row->dueSoonCount }} PO)</td>
                        <td class="text-right overdue">Rp {{ number_format((float) $row->overdueAmount, 0, ',', '.') }}<br>({{ (int) $row->overdueCount }} PO)</td>
                        <td class="text-right">Rp {{ number_format((float) $row->notDueAmount, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format((float) $row->aging0To30, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format((float) $row->aging31To60, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format((float) $row->aging61To90, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format((float) $row->agingOver90, 0, ',', '.') }}</td>
                        <td>{{ number_format((int) $row->unpaidCount, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="16">No data found</td></tr>
                @endforelse
                <tr class="bold total">
                    <td colspan="2" class="text-right">TOTAL</td>
                    <td>{{ number_format($totals['purchase'], 0, ',', '.') }}</td>
                    <td>{{ number_format($totals['item'], 0, ',', '.') }}</td>
                    <td>{{ number_format($totals['qty'], 1, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($totals['billing'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($totals['paid'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($totals['remaining'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($totals['dueSoon'], 0, ',', '.') }}<br>({{ $totals['dueSoonCount'] }} PO)</td>
                    <td class="text-right">Rp {{ number_format($totals['overdue'], 0, ',', '.') }}<br>({{ $totals['overdueCount'] }} PO)</td>
                    <td class="text-right">Rp {{ number_format($totals['notDue'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($totals['aging0To30'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($totals['aging31To60'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($totals['aging61To90'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($totals['agingOver90'], 0, ',', '.') }}</td>
                    <td>{{ number_format($totals['unpaid'], 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>
