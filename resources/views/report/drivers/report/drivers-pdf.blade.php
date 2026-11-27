<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Supir</title>
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
            font-size: 10px;
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
    </style>
</head>

<body>
    @php
        $statusLabel = match ($status) {
            '1' => 'Aktif',
            '0' => 'Nonaktif',
            default => 'Semua',
        };
        $startLabel = $startDate ? \Carbon\Carbon::parse($startDate)->format('d-m-Y') : '-';
        $endLabel = $endDate ? \Carbon\Carbon::parse($endDate)->format('d-m-Y') : '-';
    @endphp

    <div class="container">
        <h1>DAFTAR SUPIR</h1>

        <div class="info">
            <p><strong>Status:</strong> {{ $statusLabel }} | <strong>Tgl Masuk:</strong> {{ $startLabel }} s/d {{ $endLabel }} | <strong>Total Data:</strong> {{ number_format($rows->count(), 0, ',', '.') }} Supir</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 4%;">No</th>
                    <th style="width: 17%;">Nama Supir</th>
                    <th style="width: 9%;">Tgl Masuk</th>
                    <th style="width: 14%;">NIK</th>
                    <th style="width: 12%;">SIM</th>
                    <th style="width: 24%;">Alamat</th>
                    <th style="width: 10%;">No Hp</th>
                    <th style="width: 10%;">Masa Kerja</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td class="text-left">{{ $row->name ?: '-' }}</td>
                        <td class="text-center">{{ $row->joinDate ? \Carbon\Carbon::parse($row->joinDate)->format('d-m-Y') : '-' }}</td>
                        <td class="text-center">{{ $row->nik ?: '-' }}</td>
                        <td class="text-center">{{ $row->sim ?: '-' }}</td>
                        <td class="text-left">{{ $row->address ?: '-' }}</td>
                        <td class="text-center">{{ $row->phone ?: '-' }}</td>
                        <td class="text-center">{{ $row->work_duration }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">Tidak ada data supir</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>

</html>
