<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Transaksi Stock</title>
    <style>
        @page {
            margin: 15px
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fff;
        }

        .container {
            width: 100%;
            margin: 0 auto;
            padding: 10px;
        }

        h1 {
            text-align: center;
            font-size: 22px;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .report-date {
            text-align: center;
            font-size: 11px;
            color: #666;
            margin-bottom: 20px;
        }

        .item-info {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .item-info-row {
            margin-bottom: 8px;
            font-size: 12px;
        }

        .item-info-label {
            font-weight: bold;
            width: 120px;
            display: inline-block;
        }

        .summary-cards {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            justify-content: space-between;
            flex-wrap: nowrap;
        }

        .summary-card {
            flex: 1;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 8px;
            text-align: center;
            font-size: 11px;
        }

        .summary-card-label {
            color: #666;
            margin-bottom: 3px;
            font-weight: bold;
            font-size: 10px;
        }

        .summary-card-value {
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table th,
        table td {
            border: 1px solid #333;
            padding: 8px;
            font-size: 11px;
        }

        table th {
            font-weight: bold;
            background-color: #e0e0e0;
            text-align: center;
        }

        table td {
            text-align: center;
        }

        .text-left {
            text-align: left !important;
        }

        .text-right {
            text-align: right !important;
        }

        .no-data {
            text-align: center;
            padding: 15px;
            color: #999;
        }

        .footer {
            margin-top: 40px;
            font-size: 10px;
            color: #666;
            text-align: right;
        }

        .highlight {
            font-weight: bold;
            color: #000;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>DETAIL TRANSAKSI STOCK</h1>
        <p class="report-date">Tanggal Cetak: {{ date('d/m/Y H:i') }}</p>

        <!-- Item Info -->
        <div class="item-info">
            <div class="item-info-row">
                <span class="item-info-label">Item Code:</span>
                <span class="highlight">{{ $reportData['item']->code ?? '-' }}</span>
            </div>
            <div class="item-info-row">
                <span class="item-info-label">Item Name:</span>
                <span class="highlight">{{ $reportData['item']->name ?? '-' }}</span>
            </div>
            <div class="item-info-row">
                <span class="item-info-label">Warehouse:</span>
                <span class="highlight">{{ $reportData['warehouse']->name ?? '-' }} ({{ $reportData['warehouse']->code ?? '-' }})</span>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="summary-card">
                <div class="summary-card-label">Total Masuk</div>
                <div class="summary-card-value" style="color: #28a745;">{{ number_format($reportData['totalIn'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-label">Total Keluar</div>
                <div class="summary-card-value" style="color: #dc3545;">{{ number_format($reportData['totalOut'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-label">Stock Saat Ini</div>
                <div class="summary-card-value" style="color: #007bff;">{{ number_format($reportData['currentStock'], 0, ',', '.') }}</div>
            </div>
        </div>

        <!-- Transactions Table -->
        @if(count($reportData['transactions']) > 0)
            <table>
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="12%">Tanggal</th>
                        <th width="18%">Kode Transaksi</th>
                        <th width="15%">Tipe Transaksi</th>
                        <th width="10%">Qty In</th>
                        <th width="10%">Qty Out</th>
                        <th width="12%">Current Stock</th>
                        <th width="18%">Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reportData['transactions'] as $transaction)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $transaction['date'] }}</td>
                        <td class="text-left">{{ $transaction['transactionCode'] }}</td>
                        <td>{{ $transaction['transactionType'] }}</td>
                        <td class="text-right">{{ number_format($transaction['qtyIn'], 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($transaction['qtyOut'], 0, ',', '.') }}</td>
                        <td class="text-right highlight">{{ number_format($transaction['currentStock'], 0, ',', '.') }}</td>
                        <td class="text-left">{{ $transaction['createdAt'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-data">Tidak ada data transaksi untuk ditampilkan</div>
        @endif

        <div class="footer">
            <p>Laporan ini dicetak secara otomatis oleh sistem</p>
        </div>
    </div>
</body>

</html>
