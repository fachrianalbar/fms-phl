<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Armada - {{ $fleet->plateNumber }}</title>
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
            margin-bottom: 20px;
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

        .banner {
            border: 1px solid #c7d2fe;
            background-color: #eef2ff;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 16px;
        }

        .banner-plate {
            font-size: 18px;
            font-weight: bold;
            font-family: 'Courier New', Courier, monospace;
            color: #312e81;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            background-color: #4f46e5;
            color: #ffffff;
            font-weight: 600;
            font-size: 10px;
        }

        .badge-warning {
            background-color: #d97706;
            color: #ffffff;
        }

        .kpi-container {
            width: 100%;
            margin-bottom: 16px;
        }

        .kpi-box {
            border-radius: 8px;
            padding: 12px;
            text-align: center;
        }

        .kpi-title {
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .kpi-value {
            font-size: 16px;
            font-weight: 800;
            margin: 0;
        }

        .kpi-primary { background-color: #eef2ff; border: 1px solid #c7d2fe; color: #3730a3; }
        .kpi-success { background-color: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; }
        .kpi-warning { background-color: #fefce8; border: 1px solid #fde68a; color: #92400e; }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #334155;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 15px;
            margin-bottom: 8px;
            border-bottom: 1.5px solid #e2e8f0;
            padding-bottom: 4px;
        }

        table.spec-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        table.spec-table td {
            padding: 6px 10px;
            font-size: 11px;
            border-bottom: 1px solid #f1f5f9;
        }

        table.spec-table td.label {
            width: 25%;
            font-weight: 600;
            color: #64748b;
            background-color: #f8fafc;
            border-right: 1px solid #e2e8f0;
        }

        table.spec-table td.value {
            width: 25%;
            color: #1e293b;
        }

        .font-mono {
            font-family: 'Courier New', Courier, monospace;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header-title">
            <h1>LEMBAR PROFIL ARMADA (FLEET DETAIL)</h1>
            <p>PT Putra Harta Logistik - Fleet Management System</p>
        </div>

        {{-- Banner --}}
        <div class="banner">
            <table style="width: 100%; border: none;">
                <tr style="border: none;">
                    <td style="border: none;">
                        <span class="banner-plate">{{ $fleet->plateNumber }}</span>
                        &nbsp;
                        <span class="badge {{ strtolower($fleet->company?->type ?? '') === 'external' ? 'badge-warning' : '' }}">
                            {{ $fleet->company?->type ?? 'Internal' }}
                        </span>
                        <div style="font-size: 11px; color: #475569; margin-top: 4px;">
                            Kode: <span class="font-mono">{{ $fleet->code }}</span> • Perusahaan: <strong>{{ $fleet->company?->name ?? '-' }}</strong>
                        </div>
                    </td>
                    <td style="text-align: right; border: none; font-size: 10.5px; color: #64748b;">
                        Dicetak pada: {{ date('d/m/Y H:i') }}
                    </td>
                </tr>
            </table>
        </div>

        {{-- KPI Summary --}}
        <table class="kpi-container" style="border-collapse: separate; border-spacing: 8px;">
            <tr>
                <td style="width: 33.33%;">
                    <div class="kpi-box kpi-primary">
                        <div class="kpi-title">Total Pengiriman (Order)</div>
                        <div class="kpi-value">{{ number_format($totalOrders, 0, ',', '.') }} Transaksi</div>
                    </div>
                </td>
                <td style="width: 33.33%;">
                    <div class="kpi-box kpi-success">
                        <div class="kpi-title">Total Perawatan (Servis)</div>
                        <div class="kpi-value">{{ number_format($totalMaintenance, 0, ',', '.') }} Kali</div>
                    </div>
                </td>
                <td style="width: 33.33%;">
                    <div class="kpi-box kpi-warning">
                        <div class="kpi-title">Total Biaya Perawatan</div>
                        <div class="kpi-value">Rp {{ number_format($totalMaintenanceCost, 0, ',', '.') }}</div>
                    </div>
                </td>
            </tr>
        </table>

        {{-- Spesifikasi Teknis --}}
        <div class="section-title">Informasi & Spesifikasi Armada</div>
        <table class="spec-table">
            <tr>
                <td class="label">Nomor Polisi (Plat)</td>
                <td class="value font-mono"><strong>{{ $fleet->plateNumber }}</strong></td>
                <td class="label">Kode Armada</td>
                <td class="value font-mono">{{ $fleet->code }}</td>
            </tr>
            <tr>
                <td class="label">Merek Armada</td>
                <td class="value">{{ $fleet->brand?->name ?? '-' }}</td>
                <td class="label">Tipe Armada</td>
                <td class="value">{{ $fleet->type?->name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Tahun Pembuatan</td>
                <td class="value font-mono">{{ $fleet->year ?? '-' }}</td>
                <td class="label">Perusahaan Armada</td>
                <td class="value">{{ $fleet->company?->name ?? '-' }} ({{ $fleet->company?->type ?? 'Internal' }})</td>
            </tr>
            <tr>
                <td class="label">Nomor Rangka</td>
                <td class="value font-mono">{{ $fleet->frameNumber ?? '-' }}</td>
                <td class="label">Nomor Mesin</td>
                <td class="value font-mono">{{ $fleet->engineNumber ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Jatuh Tempo STNK</td>
                <td class="value font-mono">
                    {{ $fleet->vehicleRegistrationDueDate ? \Carbon\Carbon::parse($fleet->vehicleRegistrationDueDate)->format('d/m/Y') : '-' }}
                </td>
                <td class="label">Pajak Kendaraan</td>
                <td class="value font-mono">
                    {{ $fleet->vehicleTax ? \Carbon\Carbon::parse($fleet->vehicleTax)->format('d/m/Y') : '-' }}
                </td>
            </tr>
            <tr>
                <td class="label">KIR Kendaraan</td>
                <td class="value font-mono">
                    {{ $fleet->vehicleKir ? \Carbon\Carbon::parse($fleet->vehicleKir)->format('d/m/Y') : '-' }}
                </td>
                <td class="label">Pengemudi / Driver</td>
                <td class="value">
                    <strong>{{ $fleet->driver?->name ?? 'Belum Ditugaskan' }}</strong>
                    @if($fleet->driver?->position)
                        ({{ $fleet->driver->position->name }})
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label">Barcode Number</td>
                <td class="value font-mono">{{ $fleet->barcodeNumber ?? '-' }}</td>
                <td class="label">Tanggal Registrasi Sistem</td>
                <td class="value font-mono">{{ $fleet->created_at ? $fleet->created_at->format('d/m/Y H:i') : '-' }}</td>
            </tr>
        </table>
    </div>
</body>

</html>
