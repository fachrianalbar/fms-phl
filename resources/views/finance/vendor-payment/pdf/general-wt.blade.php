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
            @php
                $qty = (float) ($order->qty ?? 0);
                $unitPrice = (float) ($order->vendorPriceSingle ?? ($qty > 0 ? ($order->vendorPrice ?? 0) / $qty : ($order->vendorPrice ?? 0)));
                if ($unitPrice <= 0) {
                    $unitPrice = (float) ($order->route->vendorPrice ?? $order->route->personalVendorPrice ?? 0);
                }
                $subtotal = (float) ($order->vendorPrice ?? ($qty * $unitPrice));
                if ($subtotal <= 0 && $qty > 0) {
                    $subtotal = $qty * $unitPrice;
                }
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
                <td>{{ number_format($qty, 0, ',', '.') }}</td>
                <td>{{ number_format($unitPrice, 0, ',', '.') }}</td>
                <td style="text-align: right;">
                    {{ number_format($subtotal, 0, ',', '.') }}
                </td>
            </tr>
            @php
                $additionalCost = $order->cost
                    ? $order->cost->filter(fn ($cost) => strtolower(trim((string) ($cost->type ?? ''))) === 'on charge')->sum('nominal')
                    : 0;
                $totalBefore = $subtotal + $additionalCost;
                $pph = $order->fleet->company->pph ?? 0;
                $pphAmount = ($totalBefore * $pph) / 100;

                // PPN & PPh dari rate nota (nominal sudah dihitung saat generate nota).
                $notaPpnRate = (float) ($vendorPayment->ppn_rate ?? 0);
                $notaPphRate = (float) ($vendorPayment->pph_rate ?? 0);
                $notaPpnAmount = (float) ($vendorPayment->ppn_amount ?? 0);
                $notaPphAmount = (float) ($vendorPayment->pph_amount ?? 0);
                $notaClaimAmount = (float) ($vendorPayment->claim_amount ?? 0);

                $grandTotal = $totalBefore + $notaPpnAmount - $pphAmount - $notaPphAmount - $notaClaimAmount;
                $remainingTotal = $grandTotal - ($paymentHistoryTotal ?? 0);
            @endphp

            @if (($order->cost && $order->cost->count() > 0) || $pph > 0 || $notaPpnAmount > 0 || $notaPphAmount > 0 || $notaClaimAmount > 0 || (!empty($vendorPayment) && $paymentHistories->isNotEmpty()))
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
            @if ($notaPpnAmount > 0)
                <tr>
                    <td colspan="7" style="text-align: center;">PPN {{ rtrim(rtrim(number_format($notaPpnRate, 4, ',', '.'), '0'), ',') }}%</td>
                    <td style="text-align: right;">
                        {{ number_format($notaPpnAmount, 0, ',', '.') }}</td>
                </tr>
            @endif
            @if ($notaPphAmount > 0)
                <tr>
                    <td colspan="7" style="text-align: center;">PPh {{ rtrim(rtrim(number_format($notaPphRate, 4, ',', '.'), '0'), ',') }}% (Pajak Penghasilan)</td>
                    <td style="text-align: right;">
                        {{ number_format($notaPphAmount, 0, ',', '.') }}</td>
                </tr>
            @endif
            @if ($notaClaimAmount > 0)
                <tr>
                    <td colspan="7" style="text-align: center;">Biaya Claim</td>
                    <td style="text-align: right;">
                        {{ number_format($notaClaimAmount, 0, ',', '.') }}</td>
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

    <!-- Footer Section: Rekening & Tanda Tangan -->
    <table style="width: 100%; border: none; margin-top: 20px;">
        <tr>
            <td style="width: 60%; vertical-align: top; border: none; padding: 0; text-align: left;">
                @if(isset($userBank) && $userBank)
                    <div style="font-size: 10pt; line-height: 1.4;">
                        <strong>Pembayaran ke Rek :</strong><br>
                        <strong>{{ $userBank->bank->name ?? '' }}</strong><br>
                        {{ $userBank->accountNumber ?? $userBank->accountNUmber ?? '' }} a.n {{ $userBank->accountName ?? '' }}
                    </div>
                @endif
            </td>
            <td style="width: 40%; vertical-align: bottom; border: none; text-align: right; padding: 0;">
                <div style="font-weight: bold; font-size: 11pt;">
                    <br><br><br>
                    {{ $order->fleet->company->name ?? 'HENDRI WIJAYA' }}
                </div>
            </td>
        </tr>
    </table>

</body>

</html>
