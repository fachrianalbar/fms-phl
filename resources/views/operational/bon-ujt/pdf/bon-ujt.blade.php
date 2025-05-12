<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table {
            margin-bottom: 20px;
        }

        .header-table td {
            padding: 5px;
            font-size: 16px
        }

        .transparent-table td {
            border: none;
            background-color: transparent;
        }

        .table-container {
            margin-top: 20px;
        }

        .data-table,
        .data-table th,
        .data-table td {
            border: 1px solid black;
        }

        .data-table th,
        .data-table td {
            padding: 8px;
            text-align: center;
            font-size: 16px
        }

        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 16px
                /* Align the footer text to the right */
        }

        .signature {
            margin-top: 40px;
        }

        .signature span {
            display: inline-block;
            margin-top: 60px;
        }
    </style>
    <title>Kas Bon</title>
</head>

<body>
    @php
        use Carbon\Carbon;
        Carbon::setLocale('id');
        $day = Carbon::parse($data->date)->isoFormat('dddd');
        $date = Carbon::parse($data->date)->format('d-m-Y');
        $submitDate = Carbon::parse($data->submitDate)->format('d-m-Y');
    @endphp
    <!-- Header Section -->
    <table class="header-table transparent-table">
        <tr>
            <td>Serah Terima</td>
            <td>: {{ $data->fleetType->name }}</td>
            <td></td>
            <td>Hari</td>
            <td>: {{ $day }}</td>
        </tr>
        <tr>
            <td>Kas Bon UJT</td>
            <td>: </td>
            <td></td>
            <td>Tanggal</td>
            <td>: {{ $date }}</td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td style="color: white">Element</td>
            <td>No. Bon</td>
            <td>: {{ $data->bon }}</td>
        </tr>
    </table>

    <hr>

    <!-- Data Table -->
    <div class="table-container">
        <p style="font-size: 16px">Diserahkan Kasbon UJT, Sbb:</p>
        <table class="data-table">
            <tr>
                <th>INTERNAL</th>
                <th>EXTERNAL</th>
                <th>CATATAN</th>
                <th>LAIN-LAIN</th>
            </tr>
            <tr>
                <td>{{ 'Rp ' . number_format($int, 0, ',', '.') }}</td>
                <td>{{ 'Rp ' . number_format($ext, 0, ',', '.') }}</td>
                <td>{{ 'Rp ' . number_format($note, 0, ',', '.') }}</td>
                <td>{{ 'Rp ' . number_format($dll, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="3"><strong>JUMLAH </strong></td>
                <td>{{ 'Rp ' . number_format($total, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <!-- Footer Section -->
    <div class="footer">
        <p>{{ $data->user->name }}, {{ $submitDate }}</p>
        <p>Yang Menyerahkan</p>
        <div class="signature">
            <span>(...........................)</span>
        </div>
    </div>

</body>

</html>
