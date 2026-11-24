<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>AR Aging Piutang</title>
    <style>
        @page {
            margin: 8px;
            footer: html_pagefooter;
        }
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
        .page-footer { text-align: right; font-size: 8px; }
    </style>
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
            'invoiceCount' => 0, 'unpaidCount' => 0, 'billing' => 0, 'paid' => 0,
            'claim' => 0, 'remaining' => 0, 'notDue' => 0, 'dueSoon' => 0,
            'dueSoonCount' => 0, 'overdue' => 0, 'overdueCount' => 0,
            'aging0To30' => 0, 'aging31To60' => 0, 'aging61To90' => 0, 'agingOver90' => 0,
        ];
    @endphp

    <htmlpagefooter name="pagefooter">
        <div class="page-footer">Halaman {PAGENO} dari {nbpg}</div>
    </htmlpagefooter>

    <div class="container">
        <h1>AR AGING PIUTANG PER CUSTOMER</h1>
        <div class="info">
            <p><strong>Customer:</strong> {{ $customerName ?? 'Semua customer' }}</p>
            <p><strong>Periode:</strong> {{ $startLabel }} s/d {{ $endLabel }} | <strong>Status:</strong> {{ $statusLabel }} | Posisi aging per hari ini</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Customer</th>
                    <th>Jumlah Faktur</th>
                    <th>Total Tagihan</th>
                    <th>Sudah Dibayar</th>
                    <th>Claim</th>
                    <th>Sisa Piutang</th>
                    <th>Belum JT</th>
                    <th>JT &lt;= 7 Hari</th>
                    <th>Overdue</th>
                    <th>1-30</th>
                    <th>31-60</th>
                    <th>61-90</th>
                    <th>&gt;90</th>
                    <th>Faktur Belum Lunas</th>
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
                        <td>{{ $loop->iteration }}</td>
                        <td class="text-left">{{ $row->customerName }}</td>
                        <td>{{ number_format((int) $row->invoiceCount, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format((float) $row->totalBilling, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format((float) $row->totalPaid, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format((float) $row->totalClaim, 0, ',', '.') }}</td>
                        <td class="text-right bold">Rp {{ number_format((float) $row->totalRemaining, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format((float) $row->notDueAmount, 0, ',', '.') }}</td>
                        <td class="text-right due-soon">Rp {{ number_format((float) $row->dueSoonAmount, 0, ',', '.') }}<br>({{ (int) $row->dueSoonCount }} faktur)</td>
                        <td class="text-right overdue">Rp {{ number_format((float) $row->overdueAmount, 0, ',', '.') }}<br>({{ (int) $row->overdueCount }} faktur)</td>
                        <td class="text-right">Rp {{ number_format((float) $row->aging0To30, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format((float) $row->aging31To60, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format((float) $row->aging61To90, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format((float) $row->agingOver90, 0, ',', '.') }}</td>
                        <td>{{ number_format((int) $row->unpaidCount, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="15">Tidak ada data piutang pada filter ini.</td>
                    </tr>
                @endforelse
                <tr class="bold total">
                    <td colspan="2" class="text-right">TOTAL</td>
                    <td>{{ number_format($totals['invoiceCount'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($totals['billing'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($totals['paid'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($totals['claim'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($totals['remaining'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($totals['notDue'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($totals['dueSoon'], 0, ',', '.') }}<br>({{ $totals['dueSoonCount'] }} faktur)</td>
                    <td class="text-right">Rp {{ number_format($totals['overdue'], 0, ',', '.') }}<br>({{ $totals['overdueCount'] }} faktur)</td>
                    <td class="text-right">Rp {{ number_format($totals['aging0To30'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($totals['aging31To60'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($totals['aging61To90'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($totals['agingOver90'], 0, ',', '.') }}</td>
                    <td>{{ number_format($totals['unpaidCount'], 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>
