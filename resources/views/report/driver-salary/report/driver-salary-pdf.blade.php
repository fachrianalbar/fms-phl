<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Salary Report</title>
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
        <h1>DRIVER SALARY REPORT</h1>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Plate Number</th>
                    <th>Driver Name</th>
                    <th>Ritase</th>
                    <th>Nilai</th>
                    <th>Keterangan</th>
                    <th>TTD</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ isset($item->fleet->plateNumber) ? $item->fleet->plateNumber : '' }}</td>
                        <td>{{ isset($item->driver->name) ? $item->driver->name : '' }}</td>
                        <td>{{ $item->total_orders }}</td>
                        <td>{{ number_format($item->total_orders * 100000, 0, ',', '.') }}</td>
                        <td></td>
                        <td></td>
                    </tr>
                @endforeach

            </tbody>
        </table>
    </div>
</body>

</html>
