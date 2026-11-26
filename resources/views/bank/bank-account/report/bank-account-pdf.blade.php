<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: sans-serif; font-size: 9px; color: #1f2937; }
        .header { text-align: center; margin-bottom: 10px; }
        .header h1 { font-size: 14px; margin: 0 0 3px; text-transform: uppercase; }
        .header .meta { font-size: 8px; color: #475569; }
        .filter-summary { margin-bottom: 8px; padding: 5px 8px; background: #f8fafc; border: 1px solid #cbd5e1; font-size: 8px; color: #334155; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #64748b; padding: 5px 6px; vertical-align: middle; }
        th { background: #e2e8f0; font-weight: bold; text-align: center; font-size: 8.5px; }
        .center { text-align: center; }
        .nowrap { white-space: nowrap; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <div class="meta">Dicetak pada: {{ $printedAt->format('d-m-Y H:i') }} | Total Data: {{ $banks->count() }} Bank</div>
    </div>

    @if (! empty($filterSummary))
        <div class="filter-summary">
            <strong>Filter Aktif:</strong> {{ implode(' | ', $filterSummary) }}
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width: 45px;">No</th>
                <th>Kode Sistem</th>
                <th>Kode Bank</th>
                <th>Nama Bank</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($banks as $bank)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td class="nowrap">{{ $bank->code ?: '-' }}</td>
                    <td class="nowrap">{{ $bank->bankCode ?: '-' }}</td>
                    <td>{{ $bank->name ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="center">Tidak ada data yang sesuai filter.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
