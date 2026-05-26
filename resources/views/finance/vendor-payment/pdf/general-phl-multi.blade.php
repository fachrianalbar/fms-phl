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
            <td style="width: 50%;">{{ $customer->name ?? '-' }}</td>
            <td style="width: 15%;">Tanggal :</td>
            <td style="width: 20%; font-weight: bold;">{{ Carbon::now()->format('d/m/Y') }}</td>
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
            <tr>
                <td colspan="6" style="border-left: none; border-right: none; border-bottom: none;"></td>
                <td style="text-align: right; font-weight: bold; border-bottom: none;">Jumlah</td>
                <td style="text-align: right; font-weight: bold; border-bottom: none;">
                    {{ number_format($totalSubtotal, 0, ',', '.') }}</td>
            </tr>
            @if ($totalAdditionalCost > 0)
                <tr>
                    <td colspan="6"
                        style="border-left: none; border-right: none; border-bottom: none; border-top: none;"></td>
                    <td style="text-align: right; border-bottom: none; border-top: none;">Biaya Tambahan</td>
                    <td style="text-align: right; border-bottom: none; border-top: none;">
                        {{ number_format($totalAdditionalCost, 0, ',', '.') }}</td>
                </tr>
            @endif
            @if ($totalPphAmount > 0)
                <tr>
                    <td colspan="6"
                        style="border-left: none; border-right: none; border-bottom: none; border-top: none;"></td>
                    <td style="text-align: right; border-bottom: none; border-top: none;">PPH</td>
                    <td style="text-align: right; border-bottom: none; border-top: none;">
                        {{ number_format($totalPphAmount, 0, ',', '.') }}</td>
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
                    {{ number_format($totalGrandTotal, 0, ',', '.') }}</td>
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
                        <td style="border: none; font-weight: bold;">{{ $customer->company->bankName ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="border: none;">No Rekening :</td>
                        <td style="border: none; font-weight: bold;">{{ $customer->company->accountNumber ?? '-' }}
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
        <p>HORMAT KAMI</p>
        <br><br><br>
        <p class="">EVI IRAWATI</p>
    </div>

</body>

</html>
