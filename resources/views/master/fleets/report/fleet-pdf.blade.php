<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Master Data Armada (Fleets)</title>
    <style>
        @page {
            margin: 15px;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fff;
            color: #1e293b;
        }

        .container {
            width: 100%;
            margin: 10px auto;
            padding: 5px;
        }

        .header-title {
            text-align: center;
            margin-bottom: 15px;
        }

        .header-title h1 {
            font-size: 16px;
            font-weight: bold;
            margin: 0 0 4px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .header-title p {
            font-size: 11px;
            color: #64748b;
            margin: 0;
        }

        .info {
            margin-bottom: 12px;
            font-size: 10.5px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 12px;
        }

        .info p {
            margin: 2px 0;
            line-height: 1.4;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table th,
        table td {
            border: 1px solid #cbd5e1;
            padding: 5px 7px;
            font-size: 10px;
            vertical-align: middle;
        }

        table th {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #334155;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.3px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-mono {
            font-family: 'Courier New', Courier, monospace;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            background-color: #e0e7ff;
            color: #3730a3;
            font-weight: 600;
            font-size: 9px;
        }

        .badge-warning {
            background-color: #fef3c7;
            color: #92400e;
        }
    </style>
</head>

<body>
    @php
        $startLabel = $startDate ? \Carbon\Carbon::parse($startDate)->format('d/m/Y') : '-';
        $endLabel = $endDate ? \Carbon\Carbon::parse($endDate)->format('d/m/Y') : '-';
    @endphp

    <div class="container">
        <div class="header-title">
            <h1>LAPORAN MASTER DATA ARMADA (FLEETS)</h1>
            <p>PT Putra Harta Logistik - Fleet Management System</p>
        </div>

        <div class="info">
            <table style="width: 100%; border: none; margin: 0;">
                <tr style="border: none;">
                    <td style="width: 50%; border: none; padding: 2px 0;">
                        <p><strong>Filter Merek:</strong> {{ $brandFilterName ?? 'Semua Merek Armada' }}</p>
                        <p><strong>Filter Tipe:</strong> {{ $typeFilterName ?? 'Semua Tipe Armada' }}</p>
                    </td>
                    <td style="width: 50%; border: none; padding: 2px 0;">
                        <p><strong>Filter Perusahaan:</strong> {{ $companyFilterName ?? 'Semua Perusahaan' }}</p>
                        <p><strong>Periode Dibuat:</strong> {{ $startLabel }} s/d {{ $endLabel }} (Total: {{ count($rows) }} Armada)</p>
                    </td>
                </tr>
            </table>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 4%;" class="text-center">No</th>
                    <th style="width: 12%;">No. Polisi</th>
                    <th style="width: 10%;">Kode</th>
                    <th style="width: 11%;">Merek</th>
                    <th style="width: 12%;">Tipe</th>
                    <th style="width: 16%;">Perusahaan</th>
                    <th style="width: 8%;" class="text-center">Kepemilikan</th>
                    <th style="width: 5%;" class="text-center">Tahun</th>
                    <th style="width: 11%;" class="text-center">Jatuh Tempo STNK</th>
                    <th style="width: 11%;">Driver</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td class="font-mono"><strong>{{ $row->plateNumber ?? '-' }}</strong></td>
                        <td class="font-mono">{{ $row->code ?? '-' }}</td>
                        <td>{{ $row->brand?->name ?? '-' }}</td>
                        <td>{{ $row->type?->name ?? '-' }}</td>
                        <td>{{ $row->company?->name ?? '-' }}</td>
                        <td class="text-center">
                            @if(strtolower($row->company?->type ?? '') === 'internal')
                                <span class="badge">Internal</span>
                            @elseif(strtolower($row->company?->type ?? '') === 'external')
                                <span class="badge badge-warning">External</span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-center font-mono">{{ $row->year ?? '-' }}</td>
                        <td class="text-center font-mono">
                            {{ $row->vehicleRegistrationDueDate ? \Carbon\Carbon::parse($row->vehicleRegistrationDueDate)->format('d/m/Y') : '-' }}
                        </td>
                        <td>{{ $row->driver?->name ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center">Tidak ada data armada ditemukan</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>

</html>
