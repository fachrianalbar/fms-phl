<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Kwitansi - PT TEGUH WIBAWA BHAKTI PERSADA</title>
    <style>
        body {
            font-family: Calibri, sans-serif;
            font-size: 10pt;
            line-height: 1.3;
            margin: 10px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        .no-border td {
            border: none;
            padding: 2px 0;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .bold {
            font-weight: bold;
        }

        .amount {
            font-size: 12pt;
            font-weight: bold;
            margin-top: 5px;
        }

        .signature {
            text-align: right;
            font-size: 10pt;
            margin-top: 25px;
        }
    </style>
</head>

<body>

    @php
        use Carbon\Carbon;

        $totalPrice = 0;
        foreach ($data->details as $detail) {
            $totalPrice += $detail->order->route->price * $detail->order->qty;
        }
    @endphp

    <table class="no-border">
        <tr>
            <td class="bold">{{ $data->invoiceNumber }}</td>
        </tr>
    </table>

    <table class="no-border">
        <tr>
            <td class="text-center bold" colspan="2">{{ strtoupper($data->customer->name) }}</td>
        </tr>
        <tr>
            <td class="text-center" colspan="2">{{ \App\Helpers\TerbilangHelper::terbilang($totalPrice) }} Rupiah
            </td>
        </tr>
    </table>

    <table class="no-border">
        <tr>
            <td colspan="2" class="bold">Ongkos Angkut
                {{ $data->details->first()->order->orderMaterial->first()->material->name ?? '' }}Dari
                {{ $data->details->first()->order->route->originLocation->name ?? '' }} -
                {{ $data->details->first()->order->route->destinationLocation->name ?? '' }}</td>
        </tr>
        @foreach ($data->details as $detail)
            <tr>
                <td colspan="2">
                    Tgl. {{ Carbon::parse($detail->order->orderDate)->format('d-m-Y') }}
                    = {{ $detail->order->fleet->plateNumber ?? '-' }}
                    = {{ $detail->order->qty }} x
                    {{ number_format($detail->order->route->price ?? 0, 0, ',', '.') }},-
                </td>
            </tr>
        @endforeach

    </table>

    <table class="no-border">
        <tr>
            <td class="bold">Pembayaran mohon ditransfer ke bank {{ $company->bank_name ?? 'BNI' }}:</td>
            <td class="text-right bold">{{ strtoupper($company->city ?? 'Bandar Lampung') }},
                {{ Carbon::parse($data->invoiceDate)->locale('id')->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td>Nama&nbsp;&nbsp;&nbsp;&nbsp;: PT Wijaya Trans Makmur Sejahtera </td>
        </tr>
        <tr>
            <td>No. Rek&nbsp;: {{ $company->bank_account ?? '1812766011' }}</td>
        </tr>
    </table>

    <div class="amount">Rp. {{ number_format($totalPrice, 0, ',', '.') }},-</div>

    <div class="signature">Hendri - PT Wijaya Trans Makmur Sejahtera</div>


</body>

</html>
