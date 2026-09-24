<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Report</title>
    <style>
        @page {
            margin: 10px;
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
            font-size: 18px;
            margin-bottom: 10px;
        }

        .info {
            margin-bottom: 10px;
            font-size: 12px;
        }

        .info p {
            margin: 2px 0;
            line-height: 1.3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table th,
        table td {
            border: 1px solid #000;
            text-align: center;
            padding: 6px;
            font-size: 10px;
        }

        table th {
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }
    </style>
</head>

<body>
    @php
        $startLabel = $startDate ? \Carbon\Carbon::parse($startDate)->format('d-m-Y') : '-';
        $endLabel = $endDate ? \Carbon\Carbon::parse($endDate)->format('d-m-Y') : '-';
    @endphp

    <div class="container">
        <h1>LAPORAN DATA CUSTOMER</h1>

        <div class="info">
            <p><strong>Perusahaan:</strong> {{ $companyName ?? 'Semua Perusahaan' }}</p>
            <p><strong>Tipe:</strong> {{ $typeLabel ?? 'Semua Tipe' }}</p>
            <p><strong>Periode Dibuat:</strong> {{ $startLabel }} s/d {{ $endLabel }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Perusahaan</th>
                    <th>Tipe</th>
                    <th>PPN</th>
                    <th>PPh 23</th>
                    <th>Basis PPh</th>
                    <th>Durasi Jatuh Tempo (Hari)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $row->code }}</td>
                        <td>{{ $row->name }}</td>
                        <td>{{ $row->email ?: '-' }}</td>
                        <td>{{ $row->company->name ?? '-' }}</td>
                        <td>{{ $row->type ?: '-' }}</td>
                        <td class="text-right">{{ number_format((float) ($row->ppn ?? 0), 4, ',', '.') }}%</td>
                        <td class="text-right">{{ number_format((float) ($row->pph ?? 0), 4, ',', '.') }}%</td>
                        <td>{{ $row->pphBaseType === 'route' ? 'Tarif Rute' : 'DPP Total' }}</td>
                        <td class="text-right">{{ $row->dueDateDuration ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">Tidak ada data</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>

</html>
