<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Harga Barang</title>
    <style>
        @page {
            margin: 12px;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fff;
        }

        .container {
            width: 100%;
            margin: 10px auto;
        }

        h1 {
            text-align: center;
            font-size: 16px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .info {
            margin-bottom: 10px;
            font-size: 11px;
        }

        .info p {
            margin: 2px 0;
            line-height: 1.3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        table th,
        table td {
            border: 1px solid #000;
            padding: 5px 6px;
            font-size: 10px;
            vertical-align: top;
        }

        table th {
            background-color: #f1f5f9;
            font-weight: bold;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>

<body>
    @php
        $typeLabel = match ($type) {
            'part' => 'Barang (Part)',
            'jasa' => 'Jasa',
            default => 'Semua',
        };
        $startLabel = $startDate ? \Carbon\Carbon::parse($startDate)->format('d-m-Y') : '-';
        $endLabel = $endDate ? \Carbon\Carbon::parse($endDate)->format('d-m-Y') : '-';
    @endphp

    <div class="container">
        <h1>DAFTAR HARGA BARANG</h1>

        <div class="info">
            <p>
                <strong>Suplier:</strong> {{ $supplierName ?? 'Semua' }} |
                <strong>Tipe:</strong> {{ $typeLabel }} |
                <strong>Tgl Input:</strong> {{ $startLabel }} s/d {{ $endLabel }} |
                <strong>Total Item:</strong> {{ number_format($rows->count(), 0, ',', '.') }}
            </p>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 6%;">No</th>
                    <th style="width: 26%;">Suplier</th>
                    <th style="width: 38%;">Item Barang</th>
                    <th style="width: 13%;">Tgl Input</th>
                    <th style="width: 17%;">Harga Item</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td class="text-left">{{ $row->supplierName ?: '-' }}</td>
                        <td class="text-left">{{ $row->itemName ?: '-' }}</td>
                        <td class="text-center">{{ $row->inputDate ? \Carbon\Carbon::parse($row->inputDate)->format('d-m-Y') : '-' }}</td>
                        <td class="text-right">Rp {{ number_format((float) $row->price, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada data harga barang</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>

</html>
