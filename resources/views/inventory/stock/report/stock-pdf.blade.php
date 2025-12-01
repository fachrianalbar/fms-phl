<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Stock Per Warehouse</title>
    <style>
        @page {
            margin: 10px
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fff;
        }

        .container {
            width: 100%;
            margin: 20px auto;
            padding: 10px;
        }

        h1 {
            text-align: center;
            font-size: 20px;
            margin-bottom: 20px;
        }

        h2 {
            font-size: 16px;
            margin-top: 30px;
            margin-bottom: 10px;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 20px;
        }

        table th,
        table td {
            border: 1px solid #000;
            text-align: center;
            padding: 6px;
            font-size: 12px;
        }

        table th {
            font-weight: bold;
            background-color: #f0f0f0;
        }

        .text-left {
            text-align: left !important;
        }

        .text-right {
            text-align: right !important;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>LAPORAN STOCK PER WAREHOUSE</h1>

        @foreach ($reportData as $data)
        <h2>Warehouse: {{ $data['warehouse']->name }} ({{ $data['warehouse']->code }})</h2>

        <table>
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="15%">Item Code</th>
                    <th width="40%">Item Name</th>
                    <th width="13%">Total In</th>
                    <th width="13%">Total Out</th>
                    <th width="14%">Stock</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data['stocks'] as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->itemCode }}</td>
                    <td class="text-left">{{ $item->itemName }}</td>
                    <td class="text-right">{{ number_format($item->totalIn, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->totalOut, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->stock, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">Tidak ada data stock</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @endforeach
    </div>
</body>

</html>
</tbody>
</table>
</div>
</body>

</html>