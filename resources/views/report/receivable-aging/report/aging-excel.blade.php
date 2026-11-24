<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AR Aging Piutang</title>
</head>

<body>
    @php
        $startLabel = $startDate ? \Carbon\Carbon::parse($startDate)->format('d-m-Y') : '-';
        $endLabel = $endDate ? \Carbon\Carbon::parse($endDate)->format('d-m-Y') : '-';
        $statusLabel = match ($status ?? 'outstanding') {
            'overdue' => 'Overdue',
            'all' => 'Semua faktur',
            default => 'Belum lunas',
        };
        $totals = [
            'invoiceCount' => 0,
            'unpaidCount' => 0,
            'billing' => 0,
            'paid' => 0,
            'claim' => 0,
            'remaining' => 0,
            'notDue' => 0,
            'dueSoon' => 0,
            'dueSoonCount' => 0,
            'overdue' => 0,
            'overdueCount' => 0,
            'aging0To30' => 0,
            'aging31To60' => 0,
            'aging61To90' => 0,
            'agingOver90' => 0,
        ];
    @endphp

    <table style="width: 100%; border-collapse: collapse; border: 1px solid black;">
        <thead>
            <tr>
                <th colspan="15" style="font-weight: bold; font-size: 18px; text-align: center; padding: 10px;">
                    AR Aging Piutang Per Customer
                </th>
            </tr>
            <tr>
                <th colspan="15" style="text-align: left; padding: 6px; font-size: 12px;">
                    Customer: {{ $customerName ?? 'Semua customer' }} | Periode: {{ $startLabel }} s/d {{ $endLabel }} | Status: {{ $statusLabel }} | Posisi aging per hari ini
                </th>
            </tr>
            <tr>
                <th style="font-weight: bold; text-align: center;">No</th>
                <th style="font-weight: bold; text-align: center;">Customer</th>
                <th style="font-weight: bold; text-align: center;">Jumlah Faktur</th>
                <th style="font-weight: bold; text-align: center;">Total Tagihan</th>
                <th style="font-weight: bold; text-align: center;">Sudah Dibayar</th>
                <th style="font-weight: bold; text-align: center;">Claim</th>
                <th style="font-weight: bold; text-align: center;">Sisa Piutang</th>
                <th style="font-weight: bold; text-align: center;">Belum JT</th>
                <th style="font-weight: bold; text-align: center;">JT &lt;= 7 Hari</th>
                <th style="font-weight: bold; text-align: center;">Overdue</th>
                <th style="font-weight: bold; text-align: center;">1-30</th>
                <th style="font-weight: bold; text-align: center;">31-60</th>
                <th style="font-weight: bold; text-align: center;">61-90</th>
                <th style="font-weight: bold; text-align: center;">&gt;90</th>
                <th style="font-weight: bold; text-align: center;">Faktur Belum Lunas</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                @php
                    $totals['invoiceCount'] += (int) $row->invoiceCount;
                    $totals['unpaidCount'] += (int) $row->unpaidCount;
                    $totals['billing'] += (float) $row->totalBilling;
                    $totals['paid'] += (float) $row->totalPaid;
                    $totals['claim'] += (float) $row->totalClaim;
                    $totals['remaining'] += (float) $row->totalRemaining;
                    $totals['notDue'] += (float) $row->notDueAmount;
                    $totals['dueSoon'] += (float) $row->dueSoonAmount;
                    $totals['dueSoonCount'] += (int) $row->dueSoonCount;
                    $totals['overdue'] += (float) $row->overdueAmount;
                    $totals['overdueCount'] += (int) $row->overdueCount;
                    $totals['aging0To30'] += (float) $row->aging0To30;
                    $totals['aging31To60'] += (float) $row->aging31To60;
                    $totals['aging61To90'] += (float) $row->aging61To90;
                    $totals['agingOver90'] += (float) $row->agingOver90;
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $loop->iteration }}</td>
                    <td style="text-align: left;">{{ $row->customerName }}</td>
                    <td style="text-align: center;">{{ number_format((int) $row->invoiceCount, 0, ',', '.') }}</td>
                    <td style="text-align: right;">Rp {{ number_format((float) $row->totalBilling, 0, ',', '.') }}</td>
                    <td style="text-align: right;">Rp {{ number_format((float) $row->totalPaid, 0, ',', '.') }}</td>
                    <td style="text-align: right;">Rp {{ number_format((float) $row->totalClaim, 0, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">Rp {{ number_format((float) $row->totalRemaining, 0, ',', '.') }}</td>
                    <td style="text-align: right;">Rp {{ number_format((float) $row->notDueAmount, 0, ',', '.') }}</td>
                    <td style="text-align: right; background-color: #fff2cc;">Rp {{ number_format((float) $row->dueSoonAmount, 0, ',', '.') }} ({{ (int) $row->dueSoonCount }} faktur)</td>
                    <td style="text-align: right; background-color: #f4cccc;">Rp {{ number_format((float) $row->overdueAmount, 0, ',', '.') }} ({{ (int) $row->overdueCount }} faktur)</td>
                    <td style="text-align: right;">Rp {{ number_format((float) $row->aging0To30, 0, ',', '.') }}</td>
                    <td style="text-align: right;">Rp {{ number_format((float) $row->aging31To60, 0, ',', '.') }}</td>
                    <td style="text-align: right;">Rp {{ number_format((float) $row->aging61To90, 0, ',', '.') }}</td>
                    <td style="text-align: right;">Rp {{ number_format((float) $row->agingOver90, 0, ',', '.') }}</td>
                    <td style="text-align: center;">{{ number_format((int) $row->unpaidCount, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="15" style="text-align: center;">Tidak ada data piutang pada filter ini.</td>
                </tr>
            @endforelse
            <tr style="font-weight: bold; background-color: #d9eaf7;">
                <td colspan="2" style="text-align: right;">TOTAL</td>
                <td style="text-align: center;">{{ number_format($totals['invoiceCount'], 0, ',', '.') }}</td>
                <td style="text-align: right;">Rp {{ number_format($totals['billing'], 0, ',', '.') }}</td>
                <td style="text-align: right;">Rp {{ number_format($totals['paid'], 0, ',', '.') }}</td>
                <td style="text-align: right;">Rp {{ number_format($totals['claim'], 0, ',', '.') }}</td>
                <td style="text-align: right;">Rp {{ number_format($totals['remaining'], 0, ',', '.') }}</td>
                <td style="text-align: right;">Rp {{ number_format($totals['notDue'], 0, ',', '.') }}</td>
                <td style="text-align: right;">Rp {{ number_format($totals['dueSoon'], 0, ',', '.') }} ({{ $totals['dueSoonCount'] }} faktur)</td>
                <td style="text-align: right;">Rp {{ number_format($totals['overdue'], 0, ',', '.') }} ({{ $totals['overdueCount'] }} faktur)</td>
                <td style="text-align: right;">Rp {{ number_format($totals['aging0To30'], 0, ',', '.') }}</td>
                <td style="text-align: right;">Rp {{ number_format($totals['aging31To60'], 0, ',', '.') }}</td>
                <td style="text-align: right;">Rp {{ number_format($totals['aging61To90'], 0, ',', '.') }}</td>
                <td style="text-align: right;">Rp {{ number_format($totals['agingOver90'], 0, ',', '.') }}</td>
                <td style="text-align: center;">{{ number_format($totals['unpaidCount'], 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</body>

</html>
