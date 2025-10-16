<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Ekspedisi Berdikari</title>
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
        .bordered td,
        .bordered th {
            border: 1px solid black;
        }

        .bordered th {
            text-align: center;
            font-weight: bold;
            padding: 4px;
        }

        .bordered td {
            text-align: center;
            padding: 4px;
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
        @include('finance.invoice.pdf.header.phl')
    </htmlpageheader>

    @php
        use Carbon\Carbon;

        $totalPrice = 0;
    @endphp

    <!-- Info Invoice -->
    <table style="margin-top: 20px;">
        <tr>
            <td style="width: 60%;">NO. INVOICE : {{ $data->invoiceNumber }}</td>
            <td class="text-right">{{ strtoupper($company->city ?? 'B LAMPUNG') }},
                {{ Carbon::parse($data->invoiceDate)->locale('id')->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td colspan="2">Kepada YTH :</td>
        </tr>
        <tr>
            <td colspan="2"><strong>Berdikari Exp</strong></td>
        </tr>
    </table>

    <!-- Rekening -->
    <table style="margin-top: 10px;">
        <tr>
            <td colspan="2"><strong>Mohon di bayarkan ke Rekening {{ $company->bank_name ?? 'BCA' }}</strong></td>
        </tr>
        <tr>
            <td style="width: 20%;">A/N</td>
            <td>: {{ $company->account_name ?? ($company->director_name ?? 'HENDRI WIJAYA') }}</td>
        </tr>
        <tr>
            <td>NO. REK</td>
            <td>: {{ $company->bank_account ?? '0-200514866' }}</td>
        </tr>
        <tr>
            <td>CABANG</td>
            <td>: {{ $company->bank_branch ?? 'BUMI WARAS - B Lampung' }}</td>
        </tr>
    </table>

    <!-- Tabel Utama -->
    <table class="bordered" style="margin-top: 20px;">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>No Kendaraan</th>
                <th>Gudang Muat</th>
                <th>Gudang Bongkar</th>
                <th>Nama Barang</th>
                <th>Kubikasi</th>
                <th>Tarif/m3</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data->details as $detail)
                <tr>
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

                    <td> {{ $detail->order->qty }} </td>
                    <td>{{ number_format($detail->order->route->price ?? 0, 0, ',', '.') }}</td>
                    <td>{{ number_format(($detail->order->qty ?? 0) * ($detail->order->route->price ?? 0), 0, ',', '.') }}
                    </td>
                    @php
                        $totalPrice += ($detail->order->qty ?? 0) * ($detail->order->route->price ?? 0);
                    @endphp
                </tr>
            @endforeach

            <tr>
                <td colspan="7" class="text-right bold">TOTAL :</td>
                <td class="bold">{{ number_format($totalPrice, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Tanda Tangan -->
    <div class="mt-60 text-right">
        <p>HORMAT KAMI</p>
        <br><br><br>
        <p class="underline bold">EVI IRAWATI</p>
    </div>

</body>

</html>
