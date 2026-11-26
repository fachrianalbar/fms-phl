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
        th, td { border: 1px solid #64748b; padding: 4px 5px; vertical-align: middle; }
        th { background: #e2e8f0; font-weight: bold; text-align: center; font-size: 8px; }
        .center { text-align: center; }
        .right { text-align: right; }
        .nowrap { white-space: nowrap; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <div class="meta">Dicetak pada: {{ $printedAt->format('d-m-Y H:i') }} | Total Data: {{ $rows->count() }} Menu</div>
    </div>

    @if (! empty($filterSummary))
        <div class="filter-summary">
            <strong>Filter Aktif:</strong> {{ implode(' | ', $filterSummary) }}
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th>Kode</th>
                <th>Parent Code</th>
                <th>Nama (EN)</th>
                <th>Nama (ID)</th>
                <th>Icon</th>
                <th>URL</th>
                <th>Sort</th>
                <th>Sub Menu</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td class="nowrap">{{ $row->code ?: '-' }}</td>
                    <td class="nowrap">{{ $row->parentCode ?: '0' }}</td>
                    <td>{{ $row->name ?: '-' }}</td>
                    <td>{{ $row->nama ?: '-' }}</td>
                    <td>{{ $row->icon ?: '-' }}</td>
                    <td>{{ $row->url ?: '-' }}</td>
                    <td class="center">{{ (int) ($row->sort ?? 0) }}</td>
                    <td class="right">{{ (int) ($row->children_count ?? 0) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="center">Tidak ada data yang sesuai filter.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
