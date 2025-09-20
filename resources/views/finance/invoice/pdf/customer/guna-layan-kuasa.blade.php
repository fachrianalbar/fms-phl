<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Invoice - PT GUNA LAYAN KUASA</title>
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

        .bordered th,
        .bordered td {
            border: 1px solid black;
            padding: 4px;
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

        .underline {
            text-decoration: underline;
        }

        .mt-20 {
            margin-top: 20px;
        }

        .mt-60 {
            margin-top: 60px;
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
        foreach ($data->details as $detail) {
            // $totalPrice += $detail->order_cost->calculateTotalCost();
        }
    @endphp

    <table style="margin-top: 20px;">
        <tr>
            <td style="width: 60%;">NO. INVOICE : {{ $data->invoiceNumber }}</td>
            <td class="text-right">{{ strtoupper($company->city ?? 'B LAMPUNG') }},
                {{ Carbon::parse($data->invoiceDate)->locale('id')->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td colspan="2">Kepada YTH :</td>
        </tr>
        <tr>
            <td colspan="2"><strong>{{ strtoupper($customer->name) }}</strong></td>
        </tr>
    </table>

    <table style="margin-top: 10px;">
        <tr>
            <td colspan="2"><strong>Mohon di bayarkan ke Rekening {{ $company->bank_name ?? 'BCA' }}</strong></td>
        </tr>
        <tr>
            <td style="width: 20%;">A/N</td>
            <td>: PT PUTRI HOKI LOGISTIK</td>
        </tr>
        <tr>
            <td>NO. REKENING</td>
            <td>: {{ $company->bank_account ?? '0208888351' }}</td>
        </tr>
        <tr>
            <td>CABANG</td>
            <td>: {{ $company->bank_branch ?? 'KCU - Bumi Waras - B. Lampung' }}</td>
        </tr>
    </table>

    <table class="bordered mt-20">
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 7%;">Tanggal</th>
                <th style="width: 9%;">No Kendaraan</th>
                <th style="width: 12%;">No. SPPB</th>
                <th style="width: 8%;">Gudang Muat</th>
                <th style="width: 8%;">Nama Pembeli</th>
                <th style="width: 8%;">Tujuan</th>
                <th style="width: 13%;">Nama Barang</th>
                <th style="width: 7%;">Kg/Box</th>
                <th style="width: 6%;">Box</th>
                {{-- <th style="width: 2%;">Tonase</th> --}}
                <th style="width: 7%;">Total Tonase</th>
                <th style="width: 7%;">Tarif/Kg</th>
                <th style="width: 10%;">Ongkos Angkut</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data->details as $index => $detail)
                <tr>
                    <td rowspan="{{ $detail->order->orderMaterial->count() }}">{{ $index + 1 }}</td>
                    <td rowspan="{{ $detail->order->orderMaterial->count() }}">
                        {{ Carbon::parse($detail->order->orderDate)->format('d/m/y') }}</td>
                    <td rowspan="{{ $detail->order->orderMaterial->count() }}">
                        {{ $detail->order->fleet->plateNumber ?? '-' }}</td>
                    <td rowspan="{{ $detail->order->orderMaterial->count() }}">
                        {{ $detail->order->shipmentNumber ?? '-' }}</td>
                    <td rowspan="{{ $detail->order->orderMaterial->count() }}">PT SIL</td>
                    <td rowspan="{{ $detail->order->orderMaterial->count() }}">
                        {{ $detail->order->customer->name ?? '' }}</td>
                    <td rowspan="{{ $detail->order->orderMaterial->count() }}">
                        {{ $detail->order->route->destinationLocation->name ?? '-' }}</td>
                    <td>{{ $detail->order->orderMaterial->first()->material->name }}</td>
                    <td>
                        {{-- KG --}}
                        @if ($detail->order->orderMaterial->first()->unitCode === 'KU240831233156')
                            {{ $detail->order->orderMaterial->first()->materialQty }}
                        @elseif ($detail->order->orderMaterial->first()->unitCode2 === 'KU240831233156')
                            {{ $detail->order->orderMaterial->first()->materialQty2 }}
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        {{-- Box --}}
                        @if ($detail->order->orderMaterial->first()->unitCode === 'TE250801132342')
                            {{ $detail->order->orderMaterial->first()->materialQty }}
                        @elseif ($detail->order->orderMaterial->first()->unitCode2 === 'TE250801132342')
                            {{ $detail->order->orderMaterial->first()->materialQty2 }}
                        @else
                            -
                        @endif
                    </td>

                    <td rowspan="{{ $detail->order->orderMaterial->count() }}">
                        {{ number_format($detail->order->qty ?? 0, 0, ',', '.') }}</td>
                    <td rowspan="{{ $detail->order->orderMaterial->count() }}">
                        {{ number_format($detail->order->route->price ?? 0, 0, ',', '.') }}</td>
                    <td rowspan="{{ $detail->order->orderMaterial->count() }}">
                        {{ number_format(($detail->order->qty ?? 0) * ($detail->order->route->price ?? 0), 0, ',', '.') }}
                    </td>
                    @if ($detail->order->orderMaterial->count() > 1)
                        @foreach ($detail->order->orderMaterial->skip(1) as $material)
                <tr>
                    <td class="text-left">{{ $material->material->name ?? '-' }}</td>
                    <td>{{ $material->materialQty }}</td>
                    <td>{{ $material->materialQty2 }}</td>
                </tr>

                @php
                    $totalPrice += ($detail->order->qty ?? 0) * ($detail->order->route->price ?? 0);
                @endphp
            @endforeach
            @endif
            @endforeach

            <tr>
                <td colspan="12" class="text-right bold">TOTAL :</td>
                <td class="bold">{{ number_format($totalPrice, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="mt-60 text-right">
        <p>HORMAT KAMI</p>
        <br><br><br>
        <p class="underline bold">EVI IRAWATI</p>
    </div>

</body>

</html>
