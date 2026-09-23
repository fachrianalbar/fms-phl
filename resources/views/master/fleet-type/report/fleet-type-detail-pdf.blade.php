<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Rincian Armada Tipe {{ $type->name }}</title>
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
            font-size: 11px;
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
            font-size: 9.5px;
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
    </style>
</head>

<body>
    @php
        $startLabel = $startDate ? \Carbon\Carbon::parse($startDate)->format('d/m/Y') : '-';
        $endLabel = $endDate ? \Carbon\Carbon::parse($endDate)->format('d/m/Y') : '-';
    @endphp

    <div class="container">
        <div class="header-title">
            <h1>LAPORAN RINCIAN ARMADA TIPE {{ strtoupper($type->name) }}</h1>
            <p>PT Putra Harta Logistik - Fleet Management System</p>
        </div>

        <div class="info">
            <p><strong>Tipe Armada:</strong> {{ $type->name }} (Kode: {{ $type->code }})</p>
            <p><strong>Filter Merek Armada:</strong> {{ $fleetBrandName ?? 'Semua Merek' }}</p>
            <p><strong>Filter Perusahaan:</strong> {{ $fleetCompanyName ?? 'Semua Perusahaan' }}</p>
            <p><strong>Periode Registrasi:</strong> {{ $startLabel }} s/d {{ $endLabel }}</p>
            <p><strong>Total Armada:</strong> {{ count($rows) }} Unit</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 5%;" class="text-center">No</th>
                    <th style="width: 15%;">Plat Nomor</th>
                    <th style="width: 12%;">Kode Armada</th>
                    <th style="width: 15%;">Merek Armada</th>
                    <th style="width: 20%;">Perusahaan</th>
                    <th style="width: 7%;" class="text-center">Tahun</th>
                    <th style="width: 13%;">No. Mesin</th>
                    <th style="width: 13%;">No. Rangka</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $fleet)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td class="font-mono"><strong>{{ $fleet->plateNumber }}</strong></td>
                        <td class="font-mono">{{ $fleet->code }}</td>
                        <td>{{ $fleet->brand->name ?? '-' }}</td>
                        <td>{{ $fleet->company->name ?? '-' }}</td>
                        <td class="text-center">{{ $fleet->year ?? '-' }}</td>
                        <td class="font-mono" style="font-size: 9px;">{{ $fleet->engineNumber ?? '-' }}</td>
                        <td class="font-mono" style="font-size: 9px;">{{ $fleet->frameNumber ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">Tidak ada armada ditemukan</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>

</html>
