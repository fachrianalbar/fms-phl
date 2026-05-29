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
        use App\Helpers\TerbilangHelper;
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

            @if (($order->cost && $order->cost->count() > 0) || $pph > 0 || (!empty($vendorPayment) && $paymentHistories->isNotEmpty()))
                <tr>
                    <td colspan="7" style="text-align: center; font-weight: bold;">Jumlah</td>
                    <td style="text-align: right; font-weight: bold;">
                        {{ number_format($subtotal, 0, ',', '.') }}</td>
                </tr>
            @endif
            @if ($order->cost && $order->cost->count() > 0)
                @foreach ($order->cost as $cost)
                    <tr>
                        <td colspan="7" style="text-align: center;">
                            {{ $cost->costComponent->name ?? 'Biaya Tambahan' }}</td>
                        <td style="text-align: right;">
                            {{ number_format($cost->nominal ?? 0, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            @endif
            @if ($pph > 0)
                <tr>
                    <td colspan="7" style="text-align: center;">PPH {{ number_format($pph, 2, ',', '.') }}%</td>
                    <td style="text-align: right;">
                        {{ number_format($pphAmount, 0, ',', '.') }}</td>
                </tr>
            @endif

            @if (!empty($vendorPayment) && $paymentHistories->isNotEmpty())
                <tr>
                    <td colspan="7" style="text-align: center; font-weight: bold;">Total Tagihan</td>
                    <td style="text-align: right; font-weight: bold;">
                        {{ number_format($grandTotal, 0, ',', '.') }}</td>
                </tr>
                @foreach ($paymentHistories as $history)
                    <tr>
                        <td colspan="7" style="text-align: center; font-style: italic; color: #1f4e79;">
                            {{ $history->description ?? 'DP/Partial' }}
                            @if (!empty($history->payment_date))
                                tgl {{ Carbon::parse($history->payment_date)->format('d/m/y') }}
                            @endif
                        </td>
                        <td style="text-align: right; font-style: italic; color: #1f4e79;">
                            {{ number_format($history->amount ?? 0, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            @endif
            <tr>
                <td colspan="7" style="text-align: center; font-weight: bold;">Jumlah</td>
                <td style="text-align: right; font-weight: bold;">
                    {{ number_format($remainingTotal, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="8" style="text-align: center; font-style: italic; padding: 5px;">
                    <strong>Terbilang:</strong> {{ $remainingTotal > 0 ? TerbilangHelper::terbilang($remainingTotal) : 'Nol' }} Rupiah
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Tanda Tangan -->
    <div class="mt-60 text-right">
        <br><br><br>
        <p class="" style="font-weight: bold; font-size: 11pt;">{{ $order->fleet->company->name ?? 'HENDRI WIJAYA' }}</p>
    </div>

</body>

</html>
