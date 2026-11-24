<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Komisi</title>
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

        .header {
            width: 100%;
            margin-bottom: 10px;
        }

        .header-left {
            float: left;
            width: 60%;
        }

        .header-right {
            float: right;
            width: 40%;
            text-align: center;
        }

        .header::after {
            content: "";
            display: table;
            clear: both;
        }

        .slip-title {
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 4px;
        }

        .slip-code {
            font-size: 11px;
            color: #444;
            text-align: center;
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
            width: 110px;
            font-weight: normal;
        }

        .info-separator {
            width: 15px;
            text-align: center;
        }

        .commission-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .commission-table th,
        .commission-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            font-size: 11.5px;
        }

        .commission-table th {
            font-weight: bold;
            text-align: center;
            background-color: #f5f5f5;
        }

        .commission-table .col-no {
            width: 7%;
            text-align: center;
            font-size: 15px;
            font-weight: bold;
        }

        .commission-table .col-order {
            width: 20%;
            font-weight: bold;
        }

        .commission-table .col-date {
            width: 13%;
            text-align: center;
        }

        .commission-table .col-route {
            width: 40%;
        }

        .commission-table .col-amount {
            width: 20%;
            text-align: right;
        }

        .comp-sub {
            font-size: 10px;
            color: #444;
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
    @forelse ($commissionData as $slip)
        <div class="slip-container">
            <div class="header">
                <div class="header-right">
                    <div class="slip-title">SLIP KOMISI</div>
                    @if (! empty($slip['code']))
                        <div class="slip-code">{{ $slip['code'] }}</div>
                    @endif
                </div>
            </div>

            <table class="info-table">
                <tr>
                    <td class="info-label">Penerima</td>
                    <td class="info-separator">=</td>
                    <td><strong>{{ $slip['recipientName'] }}</strong></td>
                </tr>
                <tr>
                    <td class="info-label">Customer</td>
                    <td class="info-separator">=</td>
                    <td><strong>{{ $slip['customerName'] }}</strong></td>
                </tr>
                <tr>
                    <td class="info-label">Periode</td>
                    <td class="info-separator">=</td>
                    <td><strong>{{ $slip['period'] }}</strong></td>
                </tr>
            </table>

            <table class="commission-table">
                <thead>
                    <tr>
                        <th>No.RIT</th>
                        <th>No. Order / Shipment</th>
                        <th>Tanggal</th>
                        <th>Rute / Komponen</th>
                        <th>Jumlah Komisi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($slip['rows'] as $row)
                        <tr>
                            <td class="col-no">{{ $row['no'] }}</td>
                            <td class="col-order">
                                {{ ! empty($row['shipmentNumber']) && $row['shipmentNumber'] !== '-' ? $row['shipmentNumber'] : ($row['orderCode'] ?? '-') }}
                                @if (! empty($row['plateNumber']) && $row['plateNumber'] !== '-')
                                    <br><span class="comp-sub">{{ $row['plateNumber'] }}</span>
                                @endif
                            </td>
                            <td class="col-date">{{ $row['date'] }}</td>
                            <td class="col-route">
                                {{ $row['route'] }}
                                @if (! empty($row['componentName']) && $row['componentName'] !== '-')
                                    <br><span class="comp-sub">{{ $row['componentName'] }}</span>
                                @endif
                            </td>
                            <td class="col-amount">Rp. {{ number_format($row['amount'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach

                    @for ($i = count($slip['rows']); $i < 8; $i++)
                        <tr>
                            <td class="col-no">{{ $i + 1 }}</td>
                            <td class="col-order">&nbsp;</td>
                            <td class="col-date">&nbsp;</td>
                            <td class="col-route">&nbsp;</td>
                            <td class="col-amount">&nbsp;</td>
                        </tr>
                    @endfor

                    <tr class="total-row">
                        <td colspan="4" style="text-align: left;">Total Komisi</td>
                        <td class="col-amount">Rp. {{ number_format($slip['totalCommission'] ?? $slip['grandTotal'], 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>

            <table class="footer-table">
                @php
                    $adjustments = $slip['adjustments'] ?? [];
                    $deductions = array_filter($adjustments, fn ($a) => $a['type'] === 'deduction');
                    $additions = array_filter($adjustments, fn ($a) => $a['type'] === 'addition');
                @endphp

                @if (count($deductions) > 0)
                    @foreach ($deductions as $idx => $adj)
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

                @if (count($additions) > 0)
                    @foreach ($additions as $idx => $adj)
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
                    <td style="text-align: center; font-weight: bold;">Total Komisi :</td>
                    <td style="text-align: right; font-weight: bold;">Rp. {{ number_format($slip['grandTotal'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="2"></td>
                    <td style="text-align: center; font-weight: bold;">Diterima Tanggal :</td>
                    <td style="text-align: center; font-weight: bold;">{{ $slip['paymentDate'] ?: '' }}</td>
                </tr>
            </table>

            <div class="signature-area">
                Tanda Tangan
            </div>
        </div>
    @empty
        <div class="slip-container">
            <div class="slip-title">SLIP KOMISI</div>
            <p style="text-align: center; margin-top: 30px;">Tidak ada data komisi pada filter/periode yang dipilih.</p>
        </div>
    @endforelse
</body>

</html>
