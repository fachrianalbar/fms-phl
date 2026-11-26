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
        h2 { font-size: 10.5px; margin: 12px 0 5px; color: #1e293b; border-bottom: 1px solid #cbd5e1; padding-bottom: 3px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        th, td { border: 1px solid #64748b; padding: 4px; vertical-align: middle; }
        th { background: #e2e8f0; font-weight: bold; text-align: center; font-size: 8px; }
        .center { text-align: center; }
        .right { text-align: right; }
        .nowrap { white-space: nowrap; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <div class="meta">
            Dicetak pada: {{ $printedAt->format('d-m-Y H:i') }} |
            Missing: {{ $audit['summary']['missing'] }} |
            Tidak Sinkron: {{ $audit['summary']['mismatched'] }} |
            Orphan: {{ $audit['summary']['orphans'] }}
        </div>
    </div>

    @if (! empty($filterSummary))
        <div class="filter-summary">
            <strong>Filter Aktif:</strong> {{ implode(' | ', $filterSummary) }}
        </div>
    @endif

    <h2>1. Missing Stock Transaction ({{ $audit['summary']['missing'] }})</h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Sumber</th>
                <th>Transaction Code</th>
                <th>Detail Code</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Gudang</th>
                <th>Qty In</th>
                <th>Qty Out</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($audit['missing'] as $row)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td class="center">{{ $row['source'] }}</td>
                    <td class="nowrap">{{ $row['transactionCode'] }}</td>
                    <td class="nowrap">{{ $row['transactionDetailCode'] }}</td>
                    <td class="nowrap">{{ $row['itemCode'] }}</td>
                    <td>{{ $row['itemName'] }}</td>
                    <td>{{ $row['warehouseName'] }} ({{ $row['warehouseCode'] ?? '-' }})</td>
                    <td class="right nowrap">{{ $row['qtyIn'] > 0 ? number_format($row['qtyIn'], 1, ',', '.') : '-' }}</td>
                    <td class="right nowrap">{{ $row['qtyOut'] > 0 ? number_format($row['qtyOut'], 1, ',', '.') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="center">Tidak ada missing stock transaction.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>2. Stock Transaction Tidak Sinkron ({{ $audit['summary']['mismatched'] }})</h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Sumber</th>
                <th>Transaction Code</th>
                <th>Detail Code</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Gudang Sekarang</th>
                <th>Gudang Sumber</th>
                <th>Qty In</th>
                <th>Qty Out</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($audit['mismatched'] as $row)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td class="center">{{ $row['source'] }}</td>
                    <td class="nowrap">{{ $row['transactionCode'] }}</td>
                    <td class="nowrap">{{ $row['transactionDetailCode'] }}</td>
                    <td class="nowrap">{{ $row['itemCode'] }}</td>
                    <td>{{ $row['itemName'] }}</td>
                    <td class="center">{{ $row['currentWarehouseCode'] ?? '-' }}</td>
                    <td class="center">{{ $row['warehouseCode'] ?? '-' }}</td>
                    <td class="right nowrap">{{ number_format($row['currentQtyIn'], 1, ',', '.') }} &rarr; {{ number_format($row['qtyIn'], 1, ',', '.') }}</td>
                    <td class="right nowrap">{{ number_format($row['currentQtyOut'], 1, ',', '.') }} &rarr; {{ number_format($row['qtyOut'], 1, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="center">Tidak ada stock transaction tidak sinkron.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>3. Orphan Stock Transaction ({{ $audit['summary']['orphans'] }})</h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Type</th>
                <th>Transaction Code</th>
                <th>Detail Code</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Gudang</th>
                <th>Qty In</th>
                <th>Qty Out</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($audit['orphans'] as $row)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td class="center">{{ $row['transactionType'] }}</td>
                    <td class="nowrap">{{ $row['transactionCode'] ?? '-' }}</td>
                    <td class="nowrap">{{ $row['transactionDetailCode'] ?? '-' }}</td>
                    <td class="nowrap">{{ $row['itemCode'] ?? '-' }}</td>
                    <td>{{ $row['itemName'] }}</td>
                    <td>{{ $row['warehouseName'] }} ({{ $row['warehouseCode'] ?? '-' }})</td>
                    <td class="right nowrap">{{ $row['qtyIn'] > 0 ? number_format($row['qtyIn'], 1, ',', '.') : '-' }}</td>
                    <td class="right nowrap">{{ $row['qtyOut'] > 0 ? number_format($row['qtyOut'], 1, ',', '.') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="center">Tidak ada orphan stock transaction.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
