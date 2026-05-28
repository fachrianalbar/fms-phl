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
        @include('finance.invoice.pdf.header.phl')
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
            <td style="width: 15%;">Untuk :</td>
            <td style="width: 50%;">{{ $order->fleet->company->name ?? '-' }}</td>
            <td style="width: 15%;">No :</td>
            <td style="width: 20%; font-weight: bold;">{{ $order->shipmentNumber ?? '-' }}</td>
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
                        {{ $mtr->material->name }}@if (!$loop->last)
                            ,
                        @endif
                    @endforeach
                </td>
                <td>{{ $order->route->originLocation->name ?? '-' }}</td>
                <td>{{ $order->route->destinationLocation->name ?? '-' }}</td>
                <td>{{ number_format($order->qty ?? 0, 0, ',', '.') }}</td>
                <td>{{ number_format($order->route->personalVendorPrice ?? 0, 0, ',', '.') }}</td>
                <td style="text-align: right;">
                    {{ number_format(($order->qty ?? 0) * ($order->route->personalVendorPrice ?? 0), 0, ',', '.') }}
                </td>
            </tr>
            @php
                $subtotal = ($order->qty ?? 0) * ($order->route->personalVendorPrice ?? 0);
                $additionalCost = $order->cost ? $order->cost->sum('nominal') : 0;
                $totalBefore = $subtotal + $additionalCost;
                $pph = $order->fleet->company->pph ?? 0;
                $pphAmount = ($totalBefore * $pph) / 100;
                $grandTotal = $totalBefore - $pphAmount;
                $remainingTotal = $grandTotal - ($paymentHistoryTotal ?? 0);
            @endphp

            @if (!empty($vendorPayment) && $paymentHistories->isNotEmpty())
                @foreach ($paymentHistories as $history)
                    <tr>
                        <td colspan="7"
                            style="border-left: none; border-right: none; border-bottom: none; border-top: none; font-style: italic; color: #1f4e79; text-align: center;">
                            {{ $history->description ?? '-' }}
                            @if (!empty($history->payment_date))
                                tgl {{ Carbon::parse($history->payment_date)->format('d/m/y') }}
                            @endif
                        </td>
                        <td
                            style="text-align: right; border-bottom: none; border-top: none; font-style: italic; color: #1f4e79;">
                            {{ number_format($history->amount ?? 0, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            @endif

            <tr>
                <td colspan="6" style="border-left: none; border-right: none; border-bottom: none;"></td>
                <td style="text-align: right; font-weight: bold; border-bottom: none;">Jumlah</td>
                <td style="text-align: right; font-weight: bold; border-bottom: none;">
                    {{ number_format($remainingTotal, 0, ',', '.') }}</td>
            </tr>
            @if ($order->cost && $order->cost->count() > 0)
                @foreach ($order->cost as $cost)
                    <tr>
                        <td colspan="6"
                            style="border-left: none; border-right: none; border-bottom: none; border-top: none;"></td>
                        <td style="text-align: right; border-bottom: none; border-top: none;">
                            {{ $cost->costComponent->name ?? 'Biaya Tambahan' }}</td>
                        <td style="text-align: right; border-bottom: none; border-top: none;">
                            {{ number_format($cost->nominal ?? 0, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            @endif
            @if ($pph > 0)
                <tr>
                    <td colspan="6"
                        style="border-left: none; border-right: none; border-bottom: none; border-top: none;"></td>
                    <td style="text-align: right; border-bottom: none; border-top: none;">PPH
                        {{ number_format($pph, 2, ',', '.') }}%</td>
                    <td style="text-align: right; border-bottom: none; border-top: none;">
                        {{ number_format($pphAmount, 0, ',', '.') }}</td>
                </tr>
            @endif
            <tr>
                <td colspan="8"
                    style="border-left: none; border-right: none; border-top: 2px solid black; border-bottom: none;">
                </td>
            </tr>
            <tr>
                <td colspan="6" style="border: none;"></td>
                <td style="text-align: right; font-weight: bold; border: none;">Total</td>
                <td style="text-align: right; font-weight: bold; border: none;">
                    {{ number_format($remainingTotal, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Info Pembayaran -->
    <table style="margin-top: 20px; border: none;">
        <tr>
            <td style="width: 50%; border: none; vertical-align: top;">
                <table style="border: none;">
                    <tr>
                        <td style="border: none; width: 35%;">Pembayaran Via :</td>
                        <td style="border: none; font-weight: bold;">{{ $order->fleet->company->bankName ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="border: none;">No Rekening :</td>
                        <td style="border: none; font-weight: bold;">{{ $order->fleet->company->accountNumber ?? '-' }}
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 50%; border: none; vertical-align: top;">
            </td>
        </tr>
    </table>

    <!-- Tanda Tangan -->
    <div class="mt-60 text-right">
        <br><br><br>
        <p class="">EVI IRAWATI</p>
    </div>

</body>

</html>
