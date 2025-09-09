<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Invoice - PT Olam Indonesia</title>
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
            width: 100%;
            border-collapse: collapse;
        }

        .bordered,
        .bordered td,
        .bordered th {
            border: 1px solid black;
        }

        .bordered th,
        .bordered td {
            padding: 5px;
            text-align: center;
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

        .mt-20 {
            margin-top: 20px;
        }

        .mt-60 {
            margin-top: 60px;
        }

        .underline {
            text-decoration: underline;
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
        $totalQty = 0;
    @endphp

    <!-- Informasi Header Invoice -->
    <table style="margin-top: 20px;">
        <tr>
            <td style="width: 60%;">NO. INVOICE : {{ $data->invoiceNumber }}</td>
            <td class="text-right">B LAMPUNG, {{ Carbon::parse($data->invoiceDate)->format('d F Y') }}</td>
        </tr>
        <tr>
            <td colspan="2">Kepada YTH :</td>
        </tr>
        <tr>
            <td colspan="2"><strong>PT. OLAM INDONESIA</strong></td>
        </tr>
    </table>

    <!-- Info Pembayaran -->
    <table style="margin-top: 10px;">
        <tr>
            <td colspan="2"><strong>Mohon di bayarkan ke Rekening BCA</strong></td>
        </tr>
        <tr>
            <td style="width: 20%;">A/N</td>
            <td>: PT PUTRI HOKI LOGISTIK</td>
        </tr>
        <tr>
            <td>NO. REK</td>
            <td>: 0208888351</td>
        </tr>
        <tr>
            <td>CABANG</td>
            <td>: KCU - Bumi Waras - B. Lampung</td>
        </tr>
    </table>

    <!-- Tabel Data Utama -->
    <table class="bordered mt-20">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>No Kendaraan</th>
                <th>Gudang Muat</th>
                <th>Gudang Bongkar</th>
                <th>Nama Barang</th>
                <th>Tonase</th>
                <th>Tarif/kgs</th>
                <th>Jumlah</th>
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
                <td colspan="8" class="text-right bold">TOTAL :</td>
                <td>{{ number_format($totalPrice, 0, ',', '.') }}</td>
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
