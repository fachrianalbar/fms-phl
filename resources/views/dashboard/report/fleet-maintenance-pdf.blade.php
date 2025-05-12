<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- <title>Transaksi Pemakaian Per Truk</title> --}}
    <title>Fleet Maintenance Report Data</title>
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
        {{-- <h1>TRANSAKSI PEMAKAIAN PER TRUK</h1> --}}
        <h1>Fleet Maintenance Report Data</h1>

        <div class="info">
            {{-- <p><strong>NO.TRUK: {{ $plateNumber }} </strong></p> --}}
            {{-- <p><strong>TANGGAL: {{ $startDate }} s/d {{ $endDate }}</strong></p> --}}
        </div>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Fleet</th>
                    <th>Total Maintenance</th>
                    <th>Total Price</th>
                </tr>
            </thead>
            <tbody>
                @php
                    use Carbon\Carbon;
                    $totalPrice = 0;
                    $totalMaintenance = 0;
                @endphp
                @foreach ($data as $item)
                    @php
                        $totalMaintenance += $item->total;
                        $totalPrice += $item->price;
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->plateNumber }}</td>
                        <td>{{ $item->total }}</td>
                        <td>{{ number_format($item->price, 0, ',', '.') ?? 0 }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="2" class="total-label">TOTAL</td>
                    <td>{{ $totalMaintenance }}</td>
                    <td>Rp {{ number_format($totalPrice, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>
