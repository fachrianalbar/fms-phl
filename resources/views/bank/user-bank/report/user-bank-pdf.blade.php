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
        <div class="meta">Dicetak pada: {{ $printedAt->format('d-m-Y H:i') }} | Total Data: {{ $userBanks->count() }} Rekening</div>
    </div>

    @if (! empty($filterSummary))
        <div class="filter-summary">
            <strong>Filter Aktif:</strong> {{ implode(' | ', $filterSummary) }}
        </div>
    @endif

    @php $sumBalance = 0; @endphp

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Bank</th>
                <th>No. Rekening</th>
                <th>Nama Pemilik Rekening</th>
                <th>Tipe Pemilik</th>
                <th>Tipe Rekening</th>
                <th>Saldo</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($userBanks as $row)
                @php
                    $balance = (float) ($row['balance'] ?? 0);
                    $sumBalance += $balance;
                @endphp
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td>{{ $row['bank_name'] ?: '-' }}</td>
                    <td class="nowrap">{{ $row['accountNumber'] ?: '-' }}</td>
                    <td>{{ $row['accountName'] ?: '-' }}</td>
                    <td class="center">{{ (int) ($row['type'] ?? 0) === 1 ? 'Person' : 'Company' }}</td>
                    <td class="center">{{ strtolower((string) ($row['rekening_type'] ?? '')) === 'internal' ? 'Internal' : 'External' }}</td>
                    <td class="right nowrap">Rp {{ number_format($balance, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="center">Tidak ada data yang sesuai filter.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($userBanks->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="6" class="right">TOTAL SALDO</td>
                    <td class="right nowrap">Rp {{ number_format($sumBalance, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
