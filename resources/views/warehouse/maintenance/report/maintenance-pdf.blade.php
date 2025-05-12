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
        <h1>TRANSAKSI PEMAKAIAN PER TRUK</h1>
        <div class="info">
            <p><strong>NO.TRUK: {{ $plateNumber }} </strong></p>
            <p><strong>TANGGAL: {{ $startDate }} s/d {{ $endDate }}</strong></p>
        </div>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Kelompok</th>
                    <th>Kode Barang</th>
                    <th style="white-space: nowrap">Nama Barang</th>
                    <th>Qty</th>
                    <th>Hg.Satuan</th>
                    <th>Jumlah</th>
                    <th style="white-space: nowrap">Supplier</th>
                </tr>
            </thead>
            <tbody>
                @php
                    use Carbon\Carbon;
                    $totalPrice = 0;
                @endphp
                @foreach ($data as $item)
                    @foreach ($item->details as $it)
                        @php
                            $totalPrice += $it->item->price * $it->qty;
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ Carbon::parse($item->created_at)->format('d-m-Y') }}</td>
                            <td>{{ isset($it->item->category->name) ? $it->item->category->name : '' }}</td>
                            <td>{{ isset($it->item->code) ? $it->item->code : '' }}</td>
                            <td>{{ isset($it->item->name) ? $it->item->name : '' }}</td>
                            <td>{{ $it->qty }}</td>
                            <td>{{ isset($it->item->price) ? number_format($it->item->price, 0, ',', '.') : '' }}</td>
                            <td>{{ isset($it->item->price) ? number_format($it->item->price * $it->qty, 0, ',', '.') : 0 }}
                            </td>
                            <td>{{ isset($it->item->supplier->name) ? $it->item->supplier->name : '' }}</td>
                        </tr>
                    @endforeach
                @endforeach
                <tr>
                    <td colspan="7" class="total-label">TOTAL</td>
                    <td class="total-amount">Rp {{ number_format($totalPrice, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>
