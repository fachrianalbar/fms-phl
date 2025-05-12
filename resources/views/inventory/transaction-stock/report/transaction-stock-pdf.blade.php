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
            font-size: 11px;
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
        <h1>LAPORAN KARTU STOCK</h1>
        <div class="info">
            <p><strong>TANGGAL: {{ $startDate }} s/d {{ $endDate }}</strong></p>
        </div>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Item Code</th>
                    <th>Item Name</th>
                    <th>No Bon</th>
                    <th>Masuk</th>
                    <th>Keluar</th>
                </tr>
            </thead>
            <tbody>
                @php
                    use Carbon\Carbon;
                    $totalPrice = 0;
                @endphp
                @foreach ($data as $item)
                    <tr>
                        @php
                            $bon = '';

                            if ($item->type == 'IN') {
                                $bon = $item->purchase->purchaseCode;
                            } elseif ($item->type == 'OUT') {
                                $bon = $item->maintenance->maintenanceCode;
                            }

                            $in = 0;

                            if ($item->type == 'IN') {
                                $in = $item->qty;
                            }

                            $out = 0;

                            if ($item->type == 'OUT') {
                                $out = $item->qty;
                            }
                        @endphp
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ Carbon::parse($item->created_at)->format('d-m-Y') }}</td>
                        <td>{{ $item->itemCode }}</td>
                        <td>{{ isset($item->item->name) ? $item->item->name : '' }}</td>
                        <td>{{ $bon }}</td>
                        <td>{{ $in }}</td>
                        <td>{{ $out }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>

</html>
