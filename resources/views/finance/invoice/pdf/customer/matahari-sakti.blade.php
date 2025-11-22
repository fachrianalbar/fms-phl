<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Invoice - PT MATAHARI SAKTI</title>
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

        .left {
            text-align: left;
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

        .mt-40 {
            margin-top: 40px;
        }

        .mt-60 {
            margin-top: 60px;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .inline-table {
            display: inline-block;
            text-align: left;
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

    <!-- Header Info -->
    <table style="margin-top: 20px;">
        <tr>
            <td class="left" style="width: 60%;">
                <strong>No. Tagihan</strong> : {{ $data->invoiceNumber }}
            </td>
            <td class="text-right" style="width: 40%;">
                B Lampung, {{ Carbon::parse($data->invoiceDate)->format('d F Y') }}
            </td>
        </tr>
    </table>

    <p class="bold">Tagihan Expedisi Ke : PT MATAHARI SAKTI</p>

    <!-- Table Data -->
    <table class="bordered" style="margin-top: 10px;">
        <thead>
            <tr>
                <th>No.</th>
                <th>Tanggal</th>
                <th>No. SJ</th>
                <th>No. Kendaraan</th>
                <th>Dari</th>
                <th>Tujuan</th>
                <th>Total KG</th>
                <th>Harga/KG (Rp)</th>
                <th>Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoiceDetails as $index => $detail)
                @php
                    $order = $detail->order;
                    $route = $order->route;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ Carbon::parse($order->orderDate)->format('d/m/y') }}</td>
                    <td>{{ $order->shipmentNumber }}</td>
                    <td>{{ $order->fleet->plateNumber ?? '-' }}</td>
                    <td>{{ $route->originLocation->name ?? '-' }}</td>
                    <td>{{ $route->destinationLocation->name ?? '-' }}</td>
                    <td>{{ $detail->order->qty ?? 0 }}</td>
                    <td>{{ $detail->order->route->price ?? 0 }}</td>
                    <td>{{ number_format(($detail->order->qty ?? 0) * ($detail->order->route->price ?? 0), 0, ',', '.') }}
                </tr>
                @php
                    $totalQty += $detail->order->qty ?? 0;
                    $totalPrice += ($detail->order->qty ?? 0) * ($detail->order->route->price ?? 0);
                @endphp
                @foreach ($detail->order->onChargeCost as $cost)
                    <tr>
                        <td></td>
                        <td></td>
                        <td>{{ $cost->costComponent->name ?? null }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
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
                <td colspan="6" class="text-left bold">Pembulatan Tonase</td>
                <td>{{ number_format($totalQty, 0, ',', '.') }}</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td colspan="8" class="text-right bold">TOTAL</td>
                <td class="bold">{{ number_format($totalPrice, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Tagihan -->
    <table style="margin-top: 20px;">
        <tr>
            <td style="width: 15%;">TAGIHAN</td>
            <td>: Rp {{ number_format($totalPrice, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>TERBILANG</td>
            <td>: {{ \App\Helpers\TerbilangHelper::terbilang($totalPrice) }} Rupiah
            </td>
        </tr>
    </table>

    <!-- Tanda Tangan -->
    <div class="mt-60">
        <p>Hormat Kami,</p>
        <br><br><br>
        <p class="underline bold">HENDRI WIJAYA</p>
    </div>

    <!-- Info Transfer -->
    <p class="mt-20 bold">MOHON TRANSFER KE REKENING :</p>
    <table style="margin-top: 5px;">
        <tr>
            <td style="width: 20%;">NO REKENING</td>
            <td>: 0209918899</td>
        </tr>
        <tr>
            <td>NAMA</td>
            <td>: PT. Wijaya Trans Makmur Sejahtera</td>
        </tr>
        <tr>
            <td>BANK</td>
            <td>: BCA</td>
        </tr>
    </table>

</body>

</html>
