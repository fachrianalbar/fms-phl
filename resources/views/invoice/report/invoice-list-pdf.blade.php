<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: sans-serif; font-size: 8px; color: #1f2937; }
        .header { text-align: center; margin-bottom: 10px; }
        .header h1 { font-size: 14px; margin: 0 0 3px; text-transform: uppercase; }
        .header .meta { font-size: 8px; color: #475569; }
        .filter-summary { margin-bottom: 8px; padding: 5px 8px; background: #f8fafc; border: 1px solid #cbd5e1; font-size: 8px; color: #334155; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #64748b; padding: 4px; vertical-align: middle; }
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
        <div class="meta">Dicetak pada: {{ $printedAt->format('d-m-Y H:i') }} | Total Faktur: {{ $invoices->count() }} Faktur</div>
    </div>

    @if (! empty($filterSummary))
        <div class="filter-summary">
            <strong>Filter Aktif:</strong> {{ implode(' | ', $filterSummary) }}
        </div>
    @endif

    @php
        $sumSj = 0;
        $sumDpp = 0;
        $sumPpn = 0;
        $sumPph = 0;
        $sumBilling = 0;
        $sumPaid = 0;
        $sumClaim = 0;
        $sumRemaining = 0;
    @endphp

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No. Faktur</th>
                <th>Tgl Faktur</th>
                <th>Jth Tempo</th>
                <th>Pelanggan</th>
                <th>Perusahaan</th>
                <th>SJ</th>
                <th>Harga DPP</th>
                <th>PPN</th>
                <th>PPh 23</th>
                <th>Total Tagihan</th>
                <th>Terbayar</th>
                <th>Claim</th>
                <th>Sisa</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($invoices as $invoice)
                @php
                    $sjCount = (int) ($invoice->details ? $invoice->details->count() : 0);
                    $subtotal = (float) ($invoice->invoiceAmount ?? 0);
                    $ppn = (float) ($invoice->ppnAmount ?? 0);
                    $pph = (float) ($invoice->pphAmount ?? 0);
                    $billing = $subtotal + $ppn - $pph;
                    $paid = (float) ($invoice->payments ? $invoice->payments->sum('amount') : 0);
                    $claim = (float) ($invoice->claims ? $invoice->claims->sum('amount') : 0);
                    $remaining = max($billing - $paid - $claim, 0);

                    $sumSj += $sjCount;
                    $sumDpp += $subtotal;
                    $sumPpn += $ppn;
                    $sumPph += $pph;
                    $sumBilling += $billing;
                    $sumPaid += $paid;
                    $sumClaim += $claim;
                    $sumRemaining += $remaining;

                    if ($billing > 0 && ($paid + $claim) >= $billing) {
                        $statusLabel = 'Lunas';
                    } elseif (($paid + $claim) > 0) {
                        $statusLabel = 'Sebagian';
                    } else {
                        $statusLabel = 'Belum Bayar';
                    }
                @endphp
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td class="nowrap">{{ $invoice->invoiceNumber ?: ($invoice->code ?: '-') }}</td>
                    <td class="center nowrap">{{ $invoice->invoiceDate ? \Carbon\Carbon::parse($invoice->invoiceDate)->format('d-m-Y') : '-' }}</td>
                    <td class="center nowrap">{{ $invoice->overdueDate ? \Carbon\Carbon::parse($invoice->overdueDate)->format('d-m-Y') : '-' }}</td>
                    <td>{{ $invoice->customer->name ?? ($invoice->customerCode ?: '-') }}</td>
                    <td>{{ $invoice->customer->company->name ?? '-' }}</td>
                    <td class="center">{{ $sjCount }}</td>
                    <td class="right nowrap">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format($ppn, 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format($pph, 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format($billing, 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format($paid, 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format($claim, 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format($remaining, 0, ',', '.') }}</td>
                    <td class="center">{{ $statusLabel }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="15" class="center">Tidak ada data yang sesuai filter.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($invoices->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="6" class="right">TOTAL</td>
                    <td class="center">{{ $sumSj }}</td>
                    <td class="right nowrap">Rp {{ number_format($sumDpp, 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format($sumPpn, 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format($sumPph, 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format($sumBilling, 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format($sumPaid, 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format($sumClaim, 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format($sumRemaining, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
