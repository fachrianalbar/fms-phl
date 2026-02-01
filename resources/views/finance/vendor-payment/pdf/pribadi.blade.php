<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Nota Pembayaran - {{ $order->code }}</title>
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
    @endphp

    <!-- Header Nota Pembayaran -->
    <div style="margin-top: 20px;">
        <h3 style="text-align: center; margin: 5px 0;">NOTA PEMBAYARAN</h3>
    </div>

    <!-- Info Pembayaran -->
    <table style="margin-top: 15px;">
        <tr>
            <td style="width: 15%;">TGL :</td>
            <td style="width: 35%;">{{ Carbon::parse($vendorPayment->date)->format('d/m/Y') }}</td>
            <td style="width: 15%;">Untuk :</td>
            <td style="width: 35%;">{{ $order->code }}</td>
        </tr>
        <tr>
            <td>No :</td>
            <td>{{ $vendorPayment->code ?? '-' }}</td>
            <td>Arif</td>
            <td style="font-weight: bold;">{{ $order->code }}</td>
        </tr>
    </table>

    <!-- Tabel Data Pembayaran -->
    <table class="bordered mt-20">
        <thead>
            <tr>
                <th>TGL MUAT</th>
                <th>NO KEND</th>
                <th>MUATAN</th>
                <th>DARI</th>
                <th>TUJUAN</th>
                <th>Tonase</th>
                <th>Ongkos</th>
                <th style="width: 15%;">TOTAL JUMLAH</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ Carbon::parse($order->orderDate)->format('d/m/y') }}</td>
                <td>{{ $order->fleet->plateNumber ?? '-' }}</td>
                <td>
                    @foreach ($order->orderMaterial as $i => $mtr)
                        {{ $mtr->material->name }}@if (!$loop->last),@endif
                    @endforeach
                </td>
                <td>{{ $order->route->originLocation->name ?? '-' }}</td>
                <td>{{ $order->route->destinationLocation->name ?? '-' }}</td>
                <td>{{ number_format($order->qty ?? 0, 0, ',', '.') }}</td>
                <td>{{ number_format($order->route->personalVendorPrice ?? 0, 0, ',', '.') }}</td>
                <td style="text-align: right;">{{ number_format(($order->qty ?? 0) * ($order->route->personalVendorPrice ?? 0), 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="6" style="border-bottom: 1px solid white; border-left: 1px solid white;"></td>
                <td style="text-align: right; font-weight: bold;">Jumlah</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format(($order->qty ?? 0) * ($order->route->personalVendorPrice ?? 0), 0, ',', '.') }}</td>
            </tr>
            @if ($order->cost && $order->cost->count() > 0)
                @foreach ($order->cost as $cost)
                    <tr>
                        <td colspan="6"></td>
                        <td style="text-align: left;">{{ $cost->costComponent->name ?? 'Biaya Tambahan' }}</td>
                        <td style="text-align: right;">{{ number_format($cost->nominal ?? 0, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            @endif
            <tr>
                <td colspan="6" style="border-bottom: 1px solid white; border-left: 1px solid white;"></td>
                <td style="border-bottom: 1px solid white;"></td>
                <td style="border-bottom: 1px solid white;"></td>
            </tr>
        </tbody>
    </table>

    <!-- Info Pembayaran -->
    <table style="margin-top: 20px; border: none;">
        <tr>
            <td style="width: 50%; border: none; vertical-align: top;">
                <table style="border: none;">
                    <tr>
                        <td style="border: none; width: 30%;">Total Bayar :</td>
                        <td style="border: none; font-weight: bold;">{{ number_format($vendorPayment->amount ?? 0, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="border: none;">Pembayaran Via :</td>
                        <td style="border: none; font-weight: bold;">
                            @if ($vendorPayment->paymentHistory && $vendorPayment->paymentHistory->count() > 0)
                                @php
                                    $lastPayment = $vendorPayment->paymentHistory->last();
                                @endphp
                                {{ $lastPayment->userBank->bank->name ?? 'Transfer' }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="border: none;">Tgl :</td>
                        <td style="border: none;">
                            @if ($vendorPayment->paymentHistory && $vendorPayment->paymentHistory->count() > 0)
                                @php
                                    $lastPayment = $vendorPayment->paymentHistory->last();
                                @endphp
                                {{ Carbon::parse($lastPayment->payment_date)->format('d/m/Y') }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 50%; border: none; vertical-align: top;">
                <table style="border: none;">
                    <tr>
                        <td style="border: none; font-weight: bold;">BCA Pribadi</td>
                    </tr>
                    <tr>
                        <td style="border: none;">0200514866</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Tanda Tangan -->
    <div class="mt-60 text-right">
        <p>HORMAT KAMI</p>
        <br><br><br>
        <p class="">HENDRI WIJAYA</p>
    </div>

</body>

</html>
