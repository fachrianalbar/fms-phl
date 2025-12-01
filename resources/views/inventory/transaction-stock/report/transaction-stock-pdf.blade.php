<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi Stock Per Warehouse</title>
    <style>
        @page {
            margin: 15px;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fff;
            font-size: 11px;
        }

        .container {
            width: 100%;
            padding: 10px;
        }

        h1 {
            text-align: center;
            font-size: 18px;
            margin-bottom: 5px;
        }

        .print-date {
            text-align: center;
            font-size: 10px;
            color: #666;
            margin-bottom: 20px;
        }

        .warehouse-section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }

        .warehouse-header {
            background-color: #333;
            color: #fff;
            padding: 8px 12px;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .section-title {
            background-color: #666;
            color: #fff;
            padding: 5px 10px;
            font-size: 12px;
            font-weight: bold;
            margin: 15px 0 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        table th,
        table td {
            border: 1px solid #000;
            padding: 5px 8px;
            font-size: 10px;
        }

        table th {
            background-color: #e0e0e0;
            font-weight: bold;
            text-align: center;
        }

        table td {
            text-align: left;
        }

        .text-center {
            text-align: center !important;
        }

        .text-right {
            text-align: right !important;
        }

        .total-row {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            color: #fff;
        }

        .badge-success {
            background-color: #28a745;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #000;
        }

        .badge-info {
            background-color: #17a2b8;
        }

        .badge-secondary {
            background-color: #6c757d;
        }

        .summary-box {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 10px;
            margin-top: 10px;
        }

        .summary-box table {
            border: none;
        }

        .summary-box td {
            border: none;
            padding: 3px 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>LAPORAN TRANSAKSI STOCK PER WAREHOUSE</h1>
        <p class="print-date">Dicetak pada: {{ $printDate }}</p>

        @foreach ($reportData as $data)
        <div class="warehouse-section">
            <div class="warehouse-header">
                {{ $data['warehouse']->code }} - {{ $data['warehouse']->name }}
            </div>

            <!-- Summary Section -->
            <div class="section-title">RINGKASAN PER ITEM</div>
            <table>
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="15%">Kode Item</th>
                        <th width="35%">Nama Item</th>
                        <th width="15%">Total Masuk</th>
                        <th width="15%">Total Keluar</th>
                        <th width="15%">Stock</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data['summary'] as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $item['itemCode'] }}</td>
                        <td>{{ $item['itemName'] }}</td>
                        <td class="text-center">{{ $item['totalIn'] }}</td>
                        <td class="text-center">{{ $item['totalOut'] }}</td>
                        <td class="text-center">{{ $item['stock'] }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">Tidak ada data</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="3" class="text-right">Total:</td>
                        <td class="text-center">{{ $data['totalIn'] }}</td>
                        <td class="text-center">{{ $data['totalOut'] }}</td>
                        <td class="text-center">{{ $data['totalStock'] }}</td>
                    </tr>
                </tfoot>
            </table>

            <!-- Detail Section -->
            <div class="section-title">DETAIL TRANSAKSI</div>
            <table>
                <thead>
                    <tr>
                        <th width="4%">No</th>
                        <th width="10%">Tanggal</th>
                        <th width="12%">Kode Item</th>
                        <th width="22%">Nama Item</th>
                        <th width="15%">No Transaksi</th>
                        <th width="12%">Jenis</th>
                        <th width="10%">Masuk</th>
                        <th width="10%">Keluar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data['details'] as $index => $item)
                    @php
                    $badgeClass = 'badge-secondary';
                    if ($item['transactionType'] === 'Pembelian') {
                    $badgeClass = 'badge-success';
                    } elseif ($item['transactionType'] === 'Pemeliharaan') {
                    $badgeClass = 'badge-warning';
                    } elseif ($item['transactionType'] === 'Stock Awal') {
                    $badgeClass = 'badge-info';
                    }
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center">{{ $item['date'] }}</td>
                        <td>{{ $item['itemCode'] }}</td>
                        <td>{{ $item['itemName'] }}</td>
                        <td>{{ $item['transactionCode'] }}</td>
                        <td class="text-center">
                            <span class="badge {{ $badgeClass }}">{{ $item['transactionType'] }}</span>
                        </td>
                        <td class="text-center">{{ $item['qtyIn'] > 0 ? $item['qtyIn'] : '-' }}</td>
                        <td class="text-center">{{ $item['qtyOut'] > 0 ? $item['qtyOut'] : '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">Tidak ada data transaksi</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @endforeach
    </div>
</body>

</html>