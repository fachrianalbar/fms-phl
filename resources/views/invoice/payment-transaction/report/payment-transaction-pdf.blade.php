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
        th, td { border: 1px solid #64748b; padding: 4.5px 4px; vertical-align: middle; }
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
        <div class="meta">Dicetak pada: {{ $printedAt->format('d-m-Y H:i') }} | Total Transaksi: {{ $transactions->count() }}</div>
    </div>

    @if (! empty($filterSummary))
        <div class="filter-summary">
            <strong>Filter Aktif:</strong> {{ implode(' | ', $filterSummary) }}
        </div>
    @endif

    @php
        $sumInvoices = 0;
        $sumClaim = 0;
        $sumAmount = 0;
    @endphp

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tgl Bayar</th>
                <th>Kode Transaksi</th>
                <th>Pelanggan</th>
                <th>Bank Penerima</th>
                <th>Jml Faktur</th>
                <th>No. Faktur</th>
                <th>Total Claim</th>
                <th>Total Diterima</th>
                <th>Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transactions as $tx)
                @php
                    $invCount = (int) $tx->involvedInvoiceCodes()->count();
                    $claim = (float) ($tx->totalClaim ?? 0);
                    $amount = (float) ($tx->amount ?? 0);
                    $sumInvoices += $invCount;
                    $sumClaim += $claim;
                    $sumAmount += $amount;

                    $bankInfo = '-';
                    if ($tx->userBank) {
                        $bankName = $tx->userBank->bank->name ?? 'Bank';
                        $accNo = $tx->userBank->accountNumber ?? '';
                        $accName = $tx->userBank->accountName ?? '';
                        $bankInfo = trim($bankName . ($accNo ? ' - ' . $accNo : '') . ($accName ? ' (' . $accName . ')' : ''));
                    }

                    $invoices = $tx->involvedInvoices();
                    $invoiceNumbers = $invoices->map(fn ($inv) => $inv->invoiceNumber ?: $inv->code)->filter()->implode(', ') ?: '-';

                    if ($invoices->isNotEmpty() && $invoices->every(fn ($inv) => (int) ($inv->status ?? \App\Models\Finance\Invoice::STATUS_CREATE) === \App\Models\Finance\Invoice::STATUS_FULL)) {
                        $statusLabel = 'Selesai';
                    } elseif ($invoices->isNotEmpty()) {
                        $statusLabel = 'Sebagian';
                    } else {
                        $statusLabel = 'Draft';
                    }
                @endphp
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td class="center nowrap">{{ $tx->paymentDate ? \Carbon\Carbon::parse($tx->paymentDate)->format('d-m-Y') : '-' }}</td>
                    <td class="nowrap">{{ $tx->code ?: '-' }}</td>
                    <td>{{ $tx->customer->name ?? ($tx->customerCode ?: '-') }}</td>
                    <td>{{ $bankInfo }}</td>
                    <td class="center">{{ $invCount }}</td>
                    <td>{{ $invoiceNumbers }}</td>
                    <td class="right nowrap">Rp {{ number_format($claim, 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format($amount, 0, ',', '.') }}</td>
                    <td class="center">{{ $statusLabel }}</td>
                    <td>{{ $tx->description ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="center">Tidak ada data yang sesuai filter.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($transactions->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="5" class="right">TOTAL</td>
                    <td class="center">{{ $sumInvoices }}</td>
                    <td></td>
                    <td class="right nowrap">Rp {{ number_format($sumClaim, 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format($sumAmount, 0, ',', '.') }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
