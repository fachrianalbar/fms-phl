<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Stock</title>
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

        h2 {
            font-size: 14px;
            margin-top: 25px;
            margin-bottom: 10px;
            border-bottom: 2px solid #333;
            padding-bottom: 5px;
            font-weight: bold;
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
    </style>
</head>

<body>
    <div class="container">
        <h1>LAPORAN STOCK</h1>
        <p class="report-date">Tanggal Cetak: {{ date('d/m/Y H:i') }}</p>

        @if(count($reportData['stocks']) > 0)
            <table>
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="12%">Item Code</th>
                        <th width="30%">Item Name</th>
                        <th width="13%">Warehouse</th>
                        <th width="10%">Total In</th>
                        <th width="10%">Total Out</th>
                        <th width="10%">Current Stock</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reportData['stocks'] as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="text-left">{{ $item->itemCode }}</td>
                        <td class="text-left">{{ $item->itemName }}</td>
                        <td>{{ $item->warehouseName }}</td>
                        <td class="text-right">{{ number_format($item->totalIn, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($item->totalOut, 0, ',', '.') }}</td>
                        <td class="text-right"><strong>{{ number_format($item->stock, 0, ',', '.') }}</strong></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-data">Tidak ada data stock untuk ditampilkan</div>
        @endif

        <div class="footer">
            <p>Laporan ini dicetak secara otomatis oleh sistem</p>
        </div>
    </div>
</body>

</html>