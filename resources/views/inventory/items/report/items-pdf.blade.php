@php
    $entityLabel = $entityLabel ?? 'Barang';
    $itemType = $itemType ?? \App\Models\Inventory\Item::TYPE_PART;
    $isJasa = $itemType === \App\Models\Inventory\Item::TYPE_JASA;
    $colCount = $isJasa ? 7 : 8;
@endphp
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
        .center { text-align: center; }
        .right { text-align: right; }
        .nowrap { white-space: nowrap; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <div class="meta">Dicetak pada: {{ $printedAt->format('d-m-Y H:i') }} | Total {{ $entityLabel }}: {{ $items->count() }}</div>
    </div>

    @if (! empty($filterSummary))
        <div class="filter-summary">
            <strong>Filter Aktif:</strong> {{ implode(' | ', $filterSummary) }}
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode {{ $entityLabel }}</th>
                <th>Nama {{ $entityLabel }}</th>
                <th>Kategori</th>
                <th>Satuan</th>
                @if (! $isJasa)
                    <th>Gudang</th>
                @endif
                <th>Supplier</th>
                <th>Harga</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $row)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td class="nowrap">{{ $row->code ?: '-' }}</td>
                    <td>{{ $row->name ?: '-' }}</td>
                    <td>{{ $row->category->name ?? '-' }}</td>
                    <td class="center">{{ $row->unit->name ?? '-' }}</td>
                    @if (! $isJasa)
                        <td>{{ $row->warehouse->name ?? '-' }}</td>
                    @endif
                    <td>{{ $row->supplier->name ?? '-' }}</td>
                    <td class="right nowrap">Rp {{ number_format((float) ($row->price ?? 0), 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $colCount }}" class="center">Tidak ada data yang sesuai filter.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
