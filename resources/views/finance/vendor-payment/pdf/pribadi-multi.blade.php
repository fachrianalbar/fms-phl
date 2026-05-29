<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Nota Pembayaran - Multi</title>
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
        @include('finance.invoice.pdf.header.prb')
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
            <td style="width: 50%;">{{ $orders->first()->fleet->company->name ?? '-' }}</td>
            <td style="width: 15%;">Tanggal :</td>
            <td style="width: 20%; font-weight: bold;">{{ Carbon::now()->format('d/m/Y') }}</td>
        </tr>
        @if(isset($notaNumber) && $notaNumber)
        <tr>
            <td></td>
            <td></td>
            <td>No Nota :</td>
            <td style="font-weight: bold;">{{ $notaNumber }}</td>
        </tr>
        @endif
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
            @php
                $subtotalAll = 0;
                $additionalCostAll = 0;
                $pphAmountAll = 0;
            @endphp
            @foreach ($orders as $order)
                @php
                    $isOrderPaymentPdf = $isOrderPaymentPdf ?? false;
                    $qty = (float) ($order->qty ?? 0);
                    $routeAmount = (float) ($order->routeAmount ?? 0);
                    $unitPrice = $isOrderPaymentPdf
                        ? (float) ($order->price ?? ($qty > 0 ? $routeAmount / $qty : $routeAmount))
                        : (float) ($order->route->personalVendorPrice ?? 0);
                    $subtotal = $isOrderPaymentPdf ? $routeAmount : $qty * $unitPrice;
                    $additionalCost = $isOrderPaymentPdf ? 0 : ($order->cost ? $order->cost->sum('nominal') : 0);
                    $totalBefore = $subtotal + $additionalCost;
                    $pph = $isOrderPaymentPdf ? $order->customer->pph ?? 0 : $order->fleet->company->pph ?? 0;
                    $pphAmount = ($totalBefore * $pph) / 100;
                    $grandTotal = $isOrderPaymentPdf ? $totalBefore + $pphAmount : $totalBefore - $pphAmount;

                    $subtotalAll += $subtotal;
                    $additionalCostAll += $additionalCost;
                    $pphAmountAll += $pphAmount;
                @endphp
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
                    <td>{{ number_format($unitPrice, 0, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            @if ($totalAdditionalCost > 0 || $totalPphAmount > 0 || (!empty($paymentHistories) && $paymentHistories->isNotEmpty()))
                <tr>
                    <td colspan="7" style="text-align: center; font-weight: bold;">Jumlah</td>
                    <td style="text-align: right; font-weight: bold;">
                        {{ number_format($totalSubtotal, 0, ',', '.') }}</td>
                </tr>
            @endif
            @if ($totalAdditionalCost > 0)
                <tr>
                    <td colspan="7" style="text-align: center;">Biaya Tambahan</td>
                    <td style="text-align: right;">
                        {{ number_format($totalAdditionalCost, 0, ',', '.') }}</td>
                </tr>
            @endif
            @if ($totalPphAmount > 0)
                <tr>
                    <td colspan="7" style="text-align: center;">PPH</td>
                    <td style="text-align: right;">
                        {{ number_format($totalPphAmount, 0, ',', '.') }}</td>
                </tr>
            @endif
            @if (!empty($paymentHistories) && $paymentHistories->isNotEmpty())
                <tr>
                    <td colspan="7" style="text-align: center; font-weight: bold;">Total Tagihan</td>
                    <td style="text-align: right; font-weight: bold;">
                        {{ number_format($totalGrandTotal, 0, ',', '.') }}
                    </td>
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
                    {{ number_format($totalGrandTotal - ($paymentHistoryTotal ?? 0), 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="8" style="text-align: center; font-style: italic; padding: 5px;">
                    <strong>Terbilang:</strong> {{ ($totalGrandTotal - ($paymentHistoryTotal ?? 0)) > 0 ? TerbilangHelper::terbilang($totalGrandTotal - ($paymentHistoryTotal ?? 0)) : 'Nol' }} Rupiah
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Tanda Tangan -->
    <div class="mt-60 text-right">
        <br><br><br>
        <p class="" style="font-weight: bold; font-size: 11pt;">EVI IRAWATI</p>
    </div>

</body>

</html>
