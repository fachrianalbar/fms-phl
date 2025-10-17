<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <style>
        @page {
            header: page-header;
        }

        body {
            font-family: Calibri, sans-serif;
            font-size: 10pt;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        .info-table td {
            padding: 2px 4px;
            vertical-align: top;
        }

        .rekening-info td {
            font-size: 9pt;
            padding: 2px 4px;
        }

        .data-table,
        .data-table td,
        .data-table th {
            border: 1px solid black;
            text-align: center;
            padding: 5px;
        }

        .data-table th {
            font-weight: bold;
        }

        .footer-sign {
            margin-top: 40px;
            text-align: right;
        }

        .signature {
            margin-top: 60px;
            text-align: right;
        }

        .signature-name {
            text-decoration: underline;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <htmlpageheader name="page-header">
        @include('finance.invoice.pdf.header.wt')
    </htmlpageheader>

    @php
        use Carbon\Carbon;
        $totalPrice = 0;
        $totalQty = 0;
    @endphp

    <!-- Informasi invoice + rekening (gabungan) -->
    <table style="width: 100%; margin-top: 20px; font-size: 10pt;">
        <tr>
            <td style="width: 45%; vertical-align: top;">
                <table style="width: 100%;">
                    <tr>
                        <td>NO. INVOICE : {{ $data->invoiceNumber }}</td>
                    </tr>
                    <tr>
                        <td>Kepada YTH :</td>
                    </tr>
                    <tr>
                        <td><strong>{{ strtoupper($customer->name ?? 'PT. ASIA MAKMUR') }}</strong></td>
                    </tr>
                    @if ($customer && $customer->officeAddress)
                        <tr>
                            <td>{{ $customer->officeAddress }}</td>
                        </tr>
                    @endif
                </table>
            </td>

            <td style="width: 55%; vertical-align: top">
                <div style="text-align: right;">
                    <p>
                        {{ strtoupper($company->city ?? 'BANDAR LAMPUNG') }},
                        {{ Carbon::parse($data->invoiceDate)->format('d F Y') }}
                    </p>

                    <table style="font-size: 10pt; display: inline-block; text-align: left;">
                        <tr>
                            <td colspan="2" style="font-weight: bold;">Mohon dibayarkan ke Rekening BCA</td>
                        </tr>
                        <tr>
                            <td style="width: 35%;">A/N</td>
                            <td>: PT WIJAYA TRANS MAKMUR SEJAHTERA</td>
                        </tr>
                        <tr>
                            <td>NO. REKENING</td>
                            <td>: 0-209918899</td>
                        </tr>
                        <tr>
                            <td>CABANG</td>
                            <td>: BUMI WARAS</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <!-- Tabel data utama -->
    <table class="data-table" style="margin-top: 20px;">
        <thead>
            <tr>
                <th>TGL</th>
                <th>NO. KENDARAAN</th>
                <th>DARI</th>
                <th>TUJUAN</th>
                <th>JENIS MUATAN</th>
                <th>TONASE (KG)</th>
                <th>ONGKOS / KG</th>
                <th>JUMLAH</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoiceDetails as $index => $detail)
                @php
                    $order = $detail->order;
                    $route = $order->route;
                @endphp
                <tr>
                    <td>{{ Carbon::parse($order->orderDate)->format('d/m/y') }}</td>
                    <td>{{ $order->fleet->plateNumber ?? '-' }}</td>
                    <td>{{ $route->originLocation->name ?? '-' }}</td>
                    <td>{{ $route->destinationLocation->name ?? '-' }}</td>
                    <td>
                        @foreach ($detail->order->orderMaterial as $i => $mtr)
                            {{ $mtr->material->name }}@if (!$loop->last)
                                ,
                            @endif
                        @endforeach
                    </td>
                    <td>{{ $detail->order->qty }}</td>
                    <td>{{ number_format($detail->order->route->price ?? 0, 0, ',', '.') }}</td>
                    <td>{{ number_format(($detail->order->qty ?? 0) * ($detail->order->route->price ?? 0), 0, ',', '.') }}
                </tr>
                @php
                    $totalPrice += ($detail->order->qty ?? 0) * ($detail->order->route->price ?? 0);
                @endphp
            @endforeach

            <tr>
                <td colspan="7" style="text-align: right;"><strong>TOTAL :</strong></td>
                <td><strong>{{ number_format($totalPrice, 0, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>

    <!-- TTD -->
    <div class="footer-sign">
        <p>HORMAT KAMI</p>
    </div>

    <div class="signature">
        <p class="signature-name">HENDRI WIJAYA</p>
    </div>

</body>

</html>
