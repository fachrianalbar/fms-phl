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
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #64748b; padding: 4px 5px; vertical-align: top; word-wrap: break-word; }
        th { background: #e2e8f0; font-weight: bold; text-align: center; font-size: 8px; }
        .center { text-align: center; }
        .nowrap { white-space: nowrap; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <div class="meta">Dicetak pada: {{ $printedAt->format('d-m-Y H:i') }} | Total Data: {{ $rows->count() }} Log</div>
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
                <th style="width: 90px;">Activity</th>
                <th style="width: 70px;">Action</th>
                <th style="width: 85px;">User</th>
                <th style="width: 75px;">Role</th>
                <th>Data</th>
                <th style="width: 80px;">Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                @php
                    $props = is_string($row->properties)
                        ? $row->properties
                        : json_encode($row->properties, JSON_UNESCAPED_UNICODE);
                @endphp
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td>{{ $row->log_name ?: '-' }}</td>
                    <td>{{ $row->description ?: '-' }}</td>
                    <td>{{ $row->user?->name ?? '-' }}</td>
                    <td>{{ $row->user?->role?->name ?? '-' }}</td>
                    <td>{{ \Illuminate\Support\Str::limit((string) ($props ?: '-'), 260) }}</td>
                    <td class="center nowrap">{{ $row->created_at ? \Carbon\Carbon::parse($row->created_at)->format('d-m-Y H:i') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="center">Tidak ada data yang sesuai filter.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
