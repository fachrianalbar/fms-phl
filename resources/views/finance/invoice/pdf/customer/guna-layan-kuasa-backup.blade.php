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
            $totalPrice += $detail->order_cost->calculateTotalCost();
        }
    @endphp

    <table style="margin-top: 20px;">
        <tr>
            <td style="width: 60%;">NO. INVOICE : {{ $data->invoice_number }}</td>
            <td class="text-right">{{ strtoupper($company->city ?? 'B LAMPUNG') }},
                {{ Carbon::parse($data->invoice_date)->locale('id')->translatedFormat('d F Y') }}</td>
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
            <td>: {{ $company->name ?? 'PT PRIMA HARAPAN LAMPUNG' }}</td>
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
                <th>No</th>
                <th>Tanggal</th>
                <th>No Kendaraan</th>
                <th>Dari</th>
                <th>Tujuan</th>
                <th>Nama Barang</th>
                <th>Tonase</th>
                <th>Tarif/Kg</th>
                <th>Ongkos Angkut</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data->details as $index => $detail)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ Carbon::parse($detail->order_cost->order->start_date)->format('d/m/y') }}</td>
                    <td>{{ $detail->order_cost->order->fleet->plate_number ?? '-' }}</td>
                    <td>{{ $detail->order_cost->order->from_address ?? '-' }}</td>
                    <td>{{ $detail->order_cost->order->to_address ?? '-' }}</td>
                    <td>{{ $detail->order_cost->order->orderMaterials->first()->material->name ?? '-' }}</td>
                    <td>{{ number_format($detail->order_cost->order->orderMaterials->sum('quantity') / 1000, 3, '.', ',') }}
                    </td>
                    <td>{{ number_format($detail->order_cost->cost_per_kg ?? 0, 0, ',', '.') }}</td>
                    <td>{{ number_format($detail->order_cost->calculateTotalCost(), 0, ',', '.') }}</td>
                </tr>
            @endforeach

            <tr>
                <td colspan="8" class="text-right bold">TOTAL :</td>
                <td class="bold">{{ number_format($totalPrice, 0, ',', '.') }}</td>
            </tr>
            @if ($customer && $customer->ppn > 0)
                @php
                    $ppnAmount = $totalPrice * ($customer->ppn / 100);
                    $grandTotal = $totalPrice + $ppnAmount;
                @endphp
                <tr>
                    <td colspan="8" class="text-right">PPN {{ $customer->ppn }}% :</td>
                    <td>{{ number_format($ppnAmount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="8" class="text-right bold">GRAND TOTAL :</td>
                    <td class="bold">{{ number_format($grandTotal, 0, ',', '.') }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    @if ($data->notes)
        <div style="margin-top: 20px;">
            <strong>Catatan:</strong><br>
            {{ $data->notes }}
        </div>
    @endif

    <div class="mt-60 text-right">
        <p>HORMAT KAMI</p>
        <br><br><br>
        <p class="underline bold">{{ strtoupper($company->director_name ?? 'DIREKTUR') }}</p>
    </div>

</body>

</html>
