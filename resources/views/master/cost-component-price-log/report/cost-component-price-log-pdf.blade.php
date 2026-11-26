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
        <div class="meta">Dicetak pada: {{ $printedAt->format('d-m-Y H:i') }} | Total Data: {{ $rows->count() }} Riwayat</div>
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
                <th>Tanggal</th>
                <th>Kode Komponen</th>
                <th>Nama Komponen</th>
                <th>Harga Lama</th>
                <th>Harga Baru</th>
                <th>Diubah Oleh</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td class="center nowrap">{{ $row->created_at ? $row->created_at->format('d-m-Y H:i:s') : '-' }}</td>
                    <td class="nowrap">{{ $row->costComponentCode ?: '-' }}</td>
                    <td>{{ $row->costComponentName ?: '-' }}</td>
                    <td class="right nowrap">Rp {{ number_format((float) ($row->oldPrice ?? 0), 0, ',', '.') }}</td>
                    <td class="right nowrap">Rp {{ number_format((float) ($row->newPrice ?? 0), 0, ',', '.') }}</td>
                    <td>{{ $row->changedBy ?: '-' }}</td>
                    <td>{{ $row->notes ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="center">Tidak ada data yang sesuai filter.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
