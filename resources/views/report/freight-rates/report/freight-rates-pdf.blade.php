<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Ongkos Angkut</title>
    <style>
        @page {
            margin: 12px;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fff;
        }

        .container {
            width: 100%;
            margin: 10px auto;
        }

        h1 {
            text-align: center;
            font-size: 17px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .info {
            margin-bottom: 10px;
            font-size: 11px;
        }

        .info p {
            margin: 2px 0;
            line-height: 1.3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        table th,
        table td {
            border: 1px solid #000;
            padding: 5px 6px;
            font-size: 9.5px;
            vertical-align: top;
        }

        table th {
            background-color: #f1f5f9;
            font-weight: bold;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>

<body>
    @php
        $startLabel = $startDate ? \Carbon\Carbon::parse($startDate)->format('d-m-Y') : '-';
        $endLabel = $endDate ? \Carbon\Carbon::parse($endDate)->format('d-m-Y') : '-';
    @endphp

    <div class="container">
        <h1>DAFTAR ONGKOS ANGKUT</h1>

        <div class="info">
            <p>
                <strong>Pabrik:</strong> {{ $customerName ?? 'Semua' }} |
                <strong>Asal:</strong> {{ $originName ?? 'Semua' }} |
                <strong>Tujuan:</strong> {{ $destinationName ?? 'Semua' }} |
                <strong>Vendor:</strong> {{ $vendorName ?? 'Semua' }} |
                <strong>Tgl Input:</strong> {{ $startLabel }} s/d {{ $endLabel }}
            </p>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 4%;">No</th>
                    <th style="width: 16%;">Pabrik</th>
                    <th style="width: 13%;">Asal</th>
                    <th style="width: 13%;">Tujuan</th>
                    <th style="width: 8%;">Tgl Input</th>
                    <th style="width: 11%;">Harga Pabrik</th>
                    <th style="width: 11%;">Harga Vendor Pribadi</th>
                    <th style="width: 13%;">Nama Vendor</th>
                    <th style="width: 11%;">Harga Vendor External</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td class="text-left">{{ $row->customerName ?: '-' }}</td>
                        <td class="text-left">{{ $row->originName ?: '-' }}</td>
                        <td class="text-left">{{ $row->destinationName ?: '-' }}</td>
                        <td class="text-center">{{ $row->inputDate ? \Carbon\Carbon::parse($row->inputDate)->format('d-m-Y') : '-' }}</td>
                        <td class="text-right">Rp {{ number_format((float) $row->price, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format((float) $row->personalVendorPrice, 0, ',', '.') }}</td>
                        <td class="text-left">{{ $row->vendorName ?: '-' }}</td>
                        <td class="text-right">Rp {{ number_format((float) $row->externalVendorPrice, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">Tidak ada data ongkos angkut</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>

</html>
