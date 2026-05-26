<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji Driver</title>
    <style>
        @page {
            margin: 15mm 10mm 10mm 10mm;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 12px;
        }

        .slip-container {
            width: 100%;
            padding: 5mm;
            page-break-after: always;
        }

        .slip-container:last-child {
            page-break-after: auto;
        }

        /* Header */
        .header {
            width: 100%;
            margin-bottom: 10px;
        }

        .header-left {
            float: left;
            width: 30%;
            text-align: center;
        }

        .header-center {
            float: left;
            width: 40%;
        }

        .header-right {
            float: right;
            width: 30%;
            text-align: center;
        }

        .header::after {
            content: "";
            display: table;
            clear: both;
        }

        .company-name {
            font-size: 14px;
            font-weight: bold;
            margin-top: 5px;
        }

        .slip-title {
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 10px;
        }

        .info-table {
            width: 100%;
            margin-bottom: 10px;
        }

        .info-table td {
            padding: 2px 5px;
            font-size: 12px;
        }

        .info-label {
            width: 100px;
            font-weight: normal;
        }

        .info-separator {
            width: 15px;
            text-align: center;
        }

        /* Main table */
        .salary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .salary-table th,
        .salary-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            font-size: 12px;
        }

        .salary-table th {
            font-weight: bold;
            text-align: center;
            background-color: #f5f5f5;
        }

        .salary-table .col-no {
            width: 8%;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
        }

        .salary-table .col-date {
            width: 18%;
        }

        .salary-table .col-route {
            width: 50%;
        }

        .salary-table .col-salary {
            width: 24%;
            text-align: right;
        }

        .total-row td {
            font-weight: bold;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer-table td {
            border: 1px solid #000;
            padding: 4px 8px;
            font-size: 11px;
        }

        .footer-label {
            font-weight: bold;
        }

        .signature-area {
            text-align: right;
            margin-top: 15px;
            font-weight: bold;
            font-size: 12px;
        }
    </style>
</head>

<body>
    @foreach ($driverData as $driver)
        <div class="slip-container">
            {{-- Header --}}
            <div class="header">
                <div class="header-right">
                    <div class="slip-title">SLIP GAJI</div>
                </div>
            </div>

            {{-- Info --}}
            <table class="info-table">
                <tr>
                    <td class="info-label">Nama</td>
                    <td class="info-separator">=</td>
                    <td><strong>{{ $driver['driverName'] }}</strong></td>
                </tr>
                <tr>
                    <td class="info-label">No. Polisi</td>
                    <td class="info-separator">=</td>
                    <td><strong>{{ $driver['plateNumber'] }}</strong></td>
                </tr>
                <tr>
                    <td class="info-label">Bulan</td>
                    <td class="info-separator">=</td>
                    <td><strong>{{ $driver['month'] }}</strong></td>
                </tr>
            </table>

            {{-- Main salary table --}}
            <table class="salary-table">
                <thead>
                    <tr>
                        <th>No.RIT</th>
                        <th>Tanggal</th>
                        <th>Rute</th>
                        <th>Jumlah Gaji</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($driver['rows'] as $row)
                        <tr>
                            <td class="col-no">{{ $row['no'] }}</td>
                            <td class="col-date">{{ $row['date'] }}</td>
                            <td class="col-route">{{ $row['route'] }}</td>
                            <td class="col-salary">Rp. {{ number_format($row['salary'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach

                    {{-- Empty rows to fill up to 8 minimum --}}
                    @for ($i = count($driver['rows']); $i < 8; $i++)
                        <tr>
                            <td class="col-no">{{ $i + 1 }}</td>
                            <td class="col-date">&nbsp;</td>
                            <td class="col-route">&nbsp;</td>
                            <td class="col-salary">&nbsp;</td>
                        </tr>
                    @endfor

                    {{-- Total row --}}
                    <tr class="total-row">
                        <td colspan="3" style="text-align: left;">Total Gaji</td>
                        <td class="col-salary">Rp. {{ number_format($driver['totalSalary'] ?? $driver['grandTotal'], 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>

            {{-- Footer info with adjustments --}}
            <table class="footer-table">
                @php
                    $adjustments = $driver['adjustments'] ?? [];
                    $deductions = array_filter($adjustments, fn($a) => $a['type'] === 'deduction');
                    $additions = array_filter($adjustments, fn($a) => $a['type'] === 'addition');
                @endphp

                {{-- Deductions --}}
                @if(count($deductions) > 0)
                    @foreach($deductions as $idx => $adj)
                        <tr>
                            <td class="footer-label" style="width: 25%;">{{ $idx === array_key_first($deductions) ? 'Potongan :' : '' }}</td>
                            <td style="width: 20%;">Tgl : {{ $adj['date'] }}</td>
                            <td style="width: 35%;">{{ $adj['description'] }}</td>
                            <td style="width: 20%; text-align: right;">- Rp. {{ number_format($adj['nominal'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td class="footer-label" style="width: 25%;">Potongan :</td>
                        <td style="width: 20%;"></td>
                        <td style="width: 35%;"></td>
                        <td style="width: 20%;"></td>
                    </tr>
                @endif

                {{-- Additions --}}
                @if(count($additions) > 0)
                    @foreach($additions as $idx => $adj)
                        <tr>
                            <td class="footer-label">{{ $idx === array_key_first($additions) ? 'Tambahan :' : '' }}</td>
                            <td>Tgl : {{ $adj['date'] }}</td>
                            <td>{{ $adj['description'] }}</td>
                            <td style="text-align: right;">+ Rp. {{ number_format($adj['nominal'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td class="footer-label">Tambahan :</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                @endif

                <tr>
                    <td colspan="2"></td>
                    <td style="text-align: center; font-weight: bold;">Total Gaji :</td>
                    <td style="text-align: right; font-weight: bold;">Rp. {{ number_format($driver['grandTotal'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="2"></td>
                    <td style="text-align: center; font-weight: bold;">Diterima Tanggal :</td>
                    <td></td>
                </tr>
            </table>

            <div class="signature-area">
                Tanda Tangan
            </div>
        </div>
    @endforeach
</body>

</html>
