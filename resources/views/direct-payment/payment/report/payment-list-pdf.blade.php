<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: sans-serif; font-size: 8.5px; color: #1f2937; }
        .header { text-align: center; margin-bottom: 10px; }
        .header h1 { font-size: 14px; margin: 0 0 3px; text-transform: uppercase; }
        .header .meta { font-size: 8px; color: #475569; }
        .filter-summary { margin-bottom: 8px; padding: 5px 8px; background: #f8fafc; border: 1px solid #cbd5e1; font-size: 8px; color: #334155; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #64748b; padding: 5px 4px; vertical-align: middle; }
        th { background: #e2e8f0; font-weight: bold; text-align: center; font-size: 8px; }
        tfoot td { background: #f1f5f9; font-weight: bold; }
        .center { text-align: center; }
        .right { text-align: right; }
        .nowrap { white-space: nowrap; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <div class="meta">Dicetak pada: {{ $printedAt->format('d-m-Y H:i') }} | Total Transaksi: {{ $payments->count() }}</div>
    </div>

    @if (! empty($filterSummary))
        <div class="filter-summary">
            <strong>Filter Aktif:</strong> {{ implode(' | ', $filterSummary) }}
        </div>
    @endif

    @php
        $sumOrders = 0;
        $sumAmount = 0;
    @endphp

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal Bayar</th>
                <th>Kode Transaksi</th>
                <th>No. Nota / Order</th>
                <th>Jml Order</th>
                <th>Customer</th>
                <th>Nominal</th>
                <th>Bank Sumber Dana</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($payments as $tx)
                @php
                    $sumOrders += (int) ($tx->order_count ?? 0);
                    $sumAmount += (float) ($tx->amount ?? 0);
                    $code = $tx->batch_code ?: ($tx->legacy_code ?: '-');
                    $notaNumbers = $tx->notas->pluck('number')->filter()->implode(', ') ?: '-';
                    $customers = $tx->customers->implode(', ') ?: '-';
                    $bankInfo = '-';
                    if ($tx->userBank) {
                        $bankName = $tx->userBank->bank->name ?? 'Bank';
                        $accNo = $tx->userBank->accountNumber ?? '';
                        $accName = $tx->userBank->accountName ?? '';
                        $bankInfo = trim($bankName . ($accNo ? ' - ' . $accNo : '') . ($accName ? ' (' . $accName . ')' : ''));
                    }
                @endphp
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td class="center nowrap">{{ $tx->payment_date ? \Carbon\Carbon::parse($tx->payment_date)->format('d-m-Y') : '-' }}</td>
                    <td class="nowrap">{{ $code }}</td>
                    <td>{{ $notaNumbers }}</td>
                    <td class="center">{{ (int) ($tx->order_count ?? 0) }}</td>
                    <td>{{ $customers }}</td>
                    <td class="right nowrap">Rp {{ number_format((float) ($tx->amount ?? 0), 0, ',', '.') }}</td>
                    <td>{{ $bankInfo }}</td>
                    <td>{{ $tx->description ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="center">Tidak ada data yang sesuai filter.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($payments->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="4" class="right">TOTAL</td>
                    <td class="center">{{ $sumOrders }}</td>
                    <td></td>
                    <td class="right nowrap">Rp {{ number_format($sumAmount, 0, ',', '.') }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
