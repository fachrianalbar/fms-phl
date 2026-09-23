<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Master Perusahaan Armada</title>
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
            padding: 6px 8px;
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

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 9px;
        }

        .badge-primary {
            background-color: #e0e7ff;
            color: #3730a3;
        }

        .badge-info {
            background-color: #e0f2fe;
            color: #0369a1;
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
            <h1>LAPORAN MASTER PERUSAHAAN ARMADA (FLEET COMPANY)</h1>
            <p>PT Putra Harta Logistik - Fleet Management System</p>
        </div>

        <div class="info">
            <p><strong>Filter Perusahaan:</strong> {{ $companyFilterName ?? 'Semua Perusahaan' }}</p>
            <p><strong>Filter Tipe:</strong> {{ $typeFilterName ?? 'Semua Tipe' }}</p>
            <p><strong>Periode Dibuat:</strong> {{ $startLabel }} s/d {{ $endLabel }}</p>
            <p><strong>Total Data:</strong> {{ count($rows) }} Perusahaan</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 4%;" class="text-center">No</th>
                    <th style="width: 14%;">Kode Perusahaan</th>
                    <th style="width: 22%;">Nama Perusahaan</th>
                    <th style="width: 10%;" class="text-center">Tipe</th>
                    <th style="width: 13%;">No. Rekening</th>
                    <th style="width: 12%;">Nama Bank</th>
                    <th style="width: 8%;" class="text-center">PPh (%)</th>
                    <th style="width: 8%;" class="text-center">Armada</th>
                    <th style="width: 9%;" class="text-center">Tgl Dibuat</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td class="font-mono">{{ $row->code }}</td>
                        <td><strong>{{ $row->name }}</strong></td>
                        <td class="text-center">
                            @if ($row->type === 'Internal')
                                <span class="badge badge-primary">Internal</span>
                            @elseif ($row->type === 'External')
                                <span class="badge badge-info">External</span>
                            @else
                                {{ $row->type ?? '-' }}
                            @endif
                        </td>
                        <td class="font-mono">{{ $row->accountNumber ?? '-' }}</td>
                        <td>{{ $row->bankName ?? '-' }}</td>
                        <td class="text-center font-mono">{{ $row->pph ? number_format($row->pph, 2, ',', '.') . '%' : '0,00%' }}</td>
                        <td class="text-center">
                            <span class="badge badge-primary">{{ number_format($row->fleets_count ?? $row->fleets->count(), 0, ',', '.') }} Unit</span>
                        </td>
                        <td class="text-center font-mono">
                            {{ $row->created_at ? $row->created_at->format('d/m/Y') : '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">Tidak ada data ditemukan</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>

</html>
