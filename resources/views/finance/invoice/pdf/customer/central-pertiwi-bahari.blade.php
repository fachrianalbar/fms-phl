<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Central Pertiwi Bahari</title>
    <style>
        @page {
            header: page-header;
        }

        body {
            font-family: Calibri, sans-serif;
            font-size: 10pt;
            line-height: 20px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        .bordered,
        .bordered th,
        .bordered td {
            border: 1px solid black;
        }

        .bordered th {
            font-weight: bold;
            text-align: center;
            padding: 5px;
        }

        .bordered td {
            text-align: center;
            padding: 5px;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .underline {
            text-decoration: underline;
        }

        .mt-20 {
            margin-top: 20px;
        }

        .mt-60 {
            margin-top: 60px;
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
        // foreach ($data->details as $detail) {
        //     $totalPrice += $detail->order_cost->calculateTotalCost();
        // }
    @endphp

    <!-- Info Invoice -->
    <table style="margin-top: 20px;">
        <tr>
            <td style="width: 60%;">NO. INVOICE : {{ $data->invoiceNumber }}</td>
            <td class="text-right">{{ strtoupper($company->city ?? 'BANDAR LAMPUNG') }},
                {{ Carbon::parse($data->invoiceDate)->locale('id')->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td colspan="2">Kepada YTH :</td>
        </tr>
        <tr>
            <td colspan="2"><strong>{{ strtoupper($data->customer->name) }}</strong></td>
        </tr>
    </table>

    <!-- Info Rekening -->
    <table style="margin-top: 10px;">
        <tr>
            <td colspan="2"><strong>Mohon di bayarkan ke Rekening {{ $company->bank_name ?? 'BCA' }}</strong></td>
        </tr>
        <tr>
            <td style="width: 20%;">A/N</td>
            <td>: PT Wijaya Trans Makmur Sejahtera</td>
        </tr>
        <tr>
            <td>NO. REKENING</td>
            <td>: {{ $company->bank_account ?? '0209918899' }}</td>
        </tr>
        <tr>
            <td>CABANG</td>
            <td>: {{ $company->bank_branch ?? 'BUMI WARAS' }}</td>
        </tr>
    </table>

    <!-- Tabel Data -->
    <table class="bordered" style="margin-top: 20px;">
        <thead>
            <tr>
                <th>No</th>
                <th>TGL</th>
                <th>NO. KENDARAAN</th>
                <th>DARI</th>
                <th>TUJUAN</th>
                <th>JENIS MUATAN</th>
                <th>TONASE (KG)</th>
                <th>ONGKOS/KG</th>
                <th>JUMLAH</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data->details as $index => $detail)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ Carbon::parse($detail->order->orderDate)->format('d/m/y') }}</td>
                    <td>{{ $detail->order->fleet->plateNumber ?? '-' }}</td>
                    <td>{{ $detail->order->route->originLocation->name ?? '-' }}</td>
                    <td>{{ $detail->order->route->destinationLocation->name ?? '-' }}</td>
                    <td>
                        @foreach ($detail->order->orderMaterial as $i => $mtr)
                            {{ $mtr->material->name }}@if (!$loop->last)
                                ,
                            @endif
                        @endforeach
                    </td>

                    <td>{{ number_format($detail->order->qty ?? 0, 0, ',', '.') }}</td>
                    <td>{{ number_format($detail->order->route->price ?? 0, 0, ',', '.') }}</td>
                    <td>{{ number_format(($detail->order->qty ?? 0) * ($detail->order->route->price ?? 0), 0, ',', '.') }}
                    </td>
                    @php
                        $totalPrice += ($detail->order->qty ?? 0) * ($detail->order->route->price ?? 0);
                    @endphp
                </tr>
            @endforeach
            <tr>
                <td colspan="8" class="text-right bold">TOTAL:</td>
                <td class="bold">{{ number_format($totalPrice, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- TTD -->
    <div class="mt-60 text-right">
        <p>HORMAT KAMI</p>
        <br><br><br>
        <p class="underline bold">HENDRI WIJAYA</p>
    </div>

</body>

</html>
