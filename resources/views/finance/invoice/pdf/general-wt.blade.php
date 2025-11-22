<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Invoice - {{ $data->customer->name }}</title>
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

        th {
            font-weight: normal;
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

    <!-- Informasi Header Invoice -->
    <table style="margin-top: 20px;">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <table>
                    <tr>
                        <td>NO. INVOICEX : {{ $data->invoiceNumber }}</td>
                    </tr>
                    <tr>
                        <td colspan="2">Kepada YTH :</td>
                    </tr>
                    <tr>
                        <td colspan="2">{{ $data->customer->name }}</td>
                    </tr>
                </table>
            </td>
            <td style="width: 50%; vertical-align: top;">
                <p class="text-right" style="border-bottom: 2px dotted #000">B LAMPUNG,
                    {{ Carbon::parse($data->invoiceDate)->format('d F Y') }}
                </p>

                <table style="margin-top: 10px;">
                    <tr>
                        <td colspan="2">
                            Mohon di bayarkan ke Rekening BCA
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 20%;">A/N</td>
                        <td>: PT Wijaya Trans Makmur Sejahtera</td>
                    </tr>
                    <tr>
                        <td>NO. REK</td>
                        <td>: 0209918899</td>
                    </tr>
                    <tr>
                        <td>CABANG</td>
                        <td>: BUMI WARAS - B LAMPUNG</td>
                    </tr>
                </table>
            </td>
        </tr>


    </table>

    <!-- Info Pembayaran -->


    <!-- Tabel Data Utama -->
    <table class="bordered mt-20">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>No Kendaraan</th>
                <th>Gudang Muat </th>
                <th>Gudang Bongkar</th>
                <th>Nama Barang</th>
                <th>Tonase</th>
                <th>Tarif/kgs</th>
                <th>Ongkos Angkut</th>
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

                <td>{{ $detail->order->qty }}</td>
                <td>{{ number_format($detail->order->route->price ?? 0, 0, ',', '.') }}</td>
                <td>{{ number_format(($detail->order->qty ?? 0) * ($detail->order->route->price ?? 0), 0, ',', '.') }}
                </td>
                @php
                $totalPrice += ($detail->order->qty ?? 0) * ($detail->order->route->price ?? 0);
                @endphp
            </tr>

            @foreach ($detail->order->onChargeCost as $cost)
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>{{ $cost->costComponent->name ?? null }}</td>
                <td></td>
                <td></td>
                <td>
                    @php
                    $totalPrice += $cost->nominal;
                    @endphp
                    {{ number_format($cost->nominal ?? 0, 0, ',', '.') }}
                </td>
            </tr>
            @endforeach
            @endforeach
            <tr>
                <td colspan="8" style="border-bottom: 1px solid white; border-left: 1px solid white;"></td>

                <td>{{ number_format($totalPrice, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Tanda Tangan -->
    <div class="mt-60 text-right">
        <p>HORMAT KAMI</p>
        <br><br><br>
        <p class="">Hendri Wijaya</p>
    </div>

</body>

</html>