<h1>stock</h1>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Pemakaian Per Truk</title>
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
            margin: 40px auto;
            padding: 10px;
        }

        h1 {
            text-align: center;
            font-size: 20px;
            margin-bottom: 20px;
        }

        .info {
            margin-bottom: 20px;
            font-size: 14px;
        }

        .info p {
            margin: 0;
            line-height: 1.5;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th,
        table td {
            border: 1px solid #000;
            text-align: center;
            padding: 8px;
            font-size: 14px;
        }

        table th {
            font-weight: bold;
        }

        .total-label {
            text-align: right;
            font-weight: bold;
        }

        .total-amount {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>LAPORAN STOCK</h1>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Item Code</th>
                    <th>Item Name</th>
                    {{-- <th>Stock In</th>
                    <th>Stock Out</th> --}}
                    <th>Outstanding Stock</th>
                </tr>
            </thead>
            <tbody>
                @php
                    use Carbon\Carbon;
                    $totalPrice = 0;
                @endphp
                @foreach ($data as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->itemCode }}</td>
                        <td>{{ isset($item->item->name) ? $item->item->name : '' }}</td>
                        {{-- <td>{{ $item->stockIn }}</td>
                        <td>{{ $item->stockOut }}</td> --}}
                        <td>{{ $item->stockIn - $item->stockOut }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>

</html>
