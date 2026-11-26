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
        <div class="meta">Dicetak pada: {{ $printedAt->format('d-m-Y H:i') }} | Total Rekening: {{ $books->count() }}</div>
    </div>

    @if (! empty($filterSummary))
        <div class="filter-summary">
            <strong>Filter Aktif:</strong> {{ implode(' | ', $filterSummary) }}
        </div>
    @endif

    @php
        $sumDebit = 0;
        $sumCredit = 0;
        $sumBalance = 0;
    @endphp

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Rekening</th>
                <th>Bank</th>
                <th>No. Rekening</th>
                <th>Tipe</th>
                <th>Debit (In)</th>
                <th>Kredit (Out)</th>
                <th>Saldo</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($books as $row)
                @php
                    $debit = (float) ($row->debit ?? 0);
                    $credit = (float) ($row->credit ?? 0);
                    $balance = (float) ($row->balance ?? 0);
                    $sumDebit += $debit;
                    $sumCredit += $credit;
                    $sumBalance += $balance;
                    $typeLabel = '-';
                    if (isset($row->userBank->type)) {
                        $typeLabel = (int) $row->userBank->type === 1 ? 'Person' : 'Company';
                    }
                @endphp
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td>{{ $row->userBank->accountName ?? '-' }}</td>
                    <td>{{ $row->userBank->bank->name ?? '-' }}</td>
                    <td class="nowrap">{{ $row->userBank->accountNumber ?? '-' }}</td>
                    <td class="center">{{ $typeLabel }}</td>
                    <td class="right nowrap">Rp {{ number_format($debit, 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format($credit, 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format($balance, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="center">Tidak ada data yang sesuai filter.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($books->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="5" class="right">TOTAL</td>
                    <td class="right nowrap">Rp {{ number_format($sumDebit, 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format($sumCredit, 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format($sumBalance, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
