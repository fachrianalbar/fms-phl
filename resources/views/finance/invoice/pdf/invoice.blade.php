<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Invoice</title>

    <style>
        th {
            padding: 5px;
        }

        td {
            padding: 2px;
        }

        @page {
            margin: 20px 0 0 0;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>



@php
    use App\Models\Data\TonaseBonus;
    use Carbon\Carbon;
    use App\Helpers\TerbilangHelper;
    $totalPrice = 0;
@endphp

<body style="font-family: Arial, sans-serif; font-size: 14px;">

    <table style="width: 90%; border-collapse: collapse; margin: auto; margin-top: 100px;">
        <tr>
            <!-- Kolom Kiri: Logo + Info Perusahaan -->
            <td style="width: 65%; vertical-align: top;">
                <p style="font-size: 14px; font-weight: bold; margin-bottom: 5px;">{{ $company->name }}</p>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 100px;">
                            @if ($company->logo)
                                <img src="storage/company_setting/logo/{{ $company->logo }}" alt="logo"
                                    style="width: 100px;">
                            @endif
                        </td>
                        <td style="font-size: 12px; line-height: 1.5; padding-left: 10px ">
                            <div>
                                {{ $company->address }} <br>
                                Phone : {{ $company->phone }} <br>
                                Email : {{ $company->email }}
                            </div>

                        </td>

                    </tr>
                </table>
            </td>

            <!-- Kolom Kanan: Detail Invoice -->
            <td style="width: 35%; vertical-align: top;">
                <p style="font-size: 16px; font-weight: bold; margin-bottom: 5px; text-align: left">INVOICE <span
                        style="color: black;">#{{ $data->code }}</span></p>
                <table style="font-size: 12px; border-collapse: collapse;">
                    <tr>
                        <td style="text-align: left">Invoice No</td>
                        <td style="font-weight: bold;">{{ $data->invoiceNumber }}</td>
                    </tr>
                    <tr>
                        <td style="text-align: left">Invoice Date</td>
                        <td style="font-weight: bold;">
                            {{ Carbon::parse($data->invoiceDate)->translatedFormat('d M Y') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>



    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <th style="background-color: #0073C6; color: white; padding: 10px; text-align: left;">Customer</th>
            <th style="background-color: #0073C6; color: white; padding: 10px; text-align: left;">Billed To</th>
        </tr>
        <tr>
            <td style="vertical-align: top; padding: 10px; padding-left: 30px;">
                <strong>{{ $data->customer->name }}</strong><br>
                {{ $data->customer->address1 }}<br><br>
                <strong>Phone:</strong> {{ $data->customer->phone }}<br>
                <strong>Email:</strong> {{ $data->customer->email }}
            </td>
            <td style="vertical-align: top; padding: 10px; padding-left: 30px;">
                <strong>Address To:</strong><br>
                {{ $data->customer->address1 }}<br><br>
                <strong>Name:</strong> {{ $data->customer->picName }}<br>
                <strong>Phone:</strong> {{ $data->customer->phone }}
            </td>
        </tr>
    </table>



    <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0;">
        <tr>
            <td style="background-color: #0076BC; color: white; font-weight: bold; padding: 10px; width: 100%;">Detail
                invoices:</td>
        </tr>
    </table>

    <table style="width: 90%; border-collapse: collapse; border: 1px solid black; margin: auto" border="1">
        <thead>
            <tr>
                <th style="font-size: 14px; font-weight: bold; text-align: center">No</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Order Date</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Origin</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Destination</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Shipment No</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Plate No</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Total Cost</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data->details as $item)
                <tr>
                    <td style="text-align: center">{{ $loop->iteration }}</td>
                    <td style="text-align: center">{{ Carbon::parse($item->order->orderDate)->format('d-m-Y') }}
                    </td>
                    <td style="text-align: center">{{ $item->order->route->originLocation->name }}</td>
                    <td style="text-align: center">{{ $item->order->route->destinationLocation->name }}</td>
                    <td style="text-align: center">{{ $item->order->shipmentNumber }}</td>
                    <td style="text-align: center">{{ $item->order->fleet->plateNumber }}</td>
                    <td style="text-align: center">

                        @php
                            $datas = $item->order->route->routeDetail;

                            $allowance = 0;
                            foreach ($datas as $items) {
                                if ($items->costComponent->type == 'Allowance') {
                                    if ($items->amount != 0) {
                                        $allowance += $items->amount;
                                    }

                                    if ($items->percentage) {
                                        $route = Route::where('code', $items->routeCode)->first();

                                        $allowance += $route->price * ($items->percentage / 100);
                                    }
                                }
                            }

                            $totalPrice += $allowance;

                            $tonaseBonus = TonaseBonus::where('min', '<=', $item->order->qty)
                                ->where('max', '>=', $item->order->qty)
                                ->first();

                            $bonus = 0;

                            if ($tonaseBonus) {
                                $bonus = number_format($tonaseBonus->value, 0, '.', ',');
                                $totalPrice += $tonaseBonus->value;
                            }

                            $cost = 0;
                            if (isset($item->order->cost)) {
                                foreach ($item->order->cost as $costs) {
                                    $cost += $costs->nominal;
                                }
                            }
                            $totalPrice += $cost;
                        @endphp
                        {{ number_format($totalPrice, 0, '.', ',') }}
                    </td>
                </tr>
            @endforeach

        </tbody>

    </table>

    <table style="width: 90%; border-collapse: collapse; border: 1px solid black; margin: auto">
        <tr>
            <td style="padding: 10px; border-right: 1px solid black; vertical-align: top;">
                <strong>Terbilang:</strong>
                {{ $totalPrice * 0.11 + $totalPrice > 0 ? TerbilangHelper::terbilang($totalPrice * 0.11 + $totalPrice) : 'Nol' }}
                Rupiah
            </td>
            <td style="width: 30%; border-left: 1px solid black; vertical-align: top;">
                <table style="width: 100%; border-collapse: collapse; border: 1px solid black;">
                    <tr>
                        <td style="padding: 5px 10px; border: 1px solid black;">Total Item</td>
                        <td style="padding: 5px 10px; border: 1px solid black; text-align: right;">
                            {{ $data->details->count() }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 10px; border: 1px solid black;">Sub Total</td>
                        <td style="padding: 5px 10px; border: 1px solid black; text-align: right; font-weight: bold;">
                            {{ number_format($totalPrice, 0, '.', ',') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 10px; border: 1px solid black;">PPN 11%</td>
                        <td style="padding: 5px 10px; border: 1px solid black; text-align: right; font-weight: bold;">
                            {{ number_format($totalPrice * 0.11, 0, '.', ',') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 10px; border: 1px solid black;">Grand Total</td>
                        <td style="padding: 5px 10px; border: 1px solid black; text-align: right; font-weight: bold;">
                            {{ number_format($totalPrice * 0.11 + $totalPrice, 0, '.', ',') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="page-break"></div>


    <div style="width: 90%; margin: auto">


        <h2 style="text-align: left; font-weight: bold;">INVOICE</h2>

        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 50%;">Sudah Terima dari</td>
                <td style="width: 50%; font-weight: bold;">{{ $company->name }}</td>
            </tr>
            <tr>
                <td>Untuk Pembayaran Invoice dengan No.</td>
                <td style="font-weight: bold;">{{ $data->invoiceNumber }}</td>
            </tr>
        </table>

        <br>

        <table style="width: 100%; border-collapse: collapse; border: 1px solid black;">
            <tr>
                <th style="border: 1px solid black; padding: 8px; text-align: left;">Total Tagihan</th>
                <td style="border: 1px solid black; padding: 8px; text-align: right;">Rp.
                    {{ number_format($totalPrice, 0, '.', ',') }}</td>
            </tr>
            <tr>
                <th style="border: 1px solid black; padding: 8px; text-align: left;">PPN 11%</th>
                <td style="border: 1px solid black; padding: 8px; text-align: right;">Rp.
                    {{ number_format($totalPrice * 0.11, 0, '.', ',') }}</td>
            </tr>
            <tr>
                <th style="border: 1px solid black; padding: 8px; text-align: left;">Jumlah Tagihan</th>
                <td style="border: 1px solid black; padding: 8px; text-align: right;">Rp.
                    {{ number_format($totalPrice * 0.11 + $totalPrice, 0, '.', ',') }}</td>
            </tr>
        </table>

        <br>

        <div style="border: 1px solid black; padding: 10px; width: 100%; text-align: center;">
            <strong>Terbilang:</strong>
            {{ $totalPrice * 0.11 + $totalPrice > 0 ? TerbilangHelper::terbilang($totalPrice * 0.11 + $totalPrice) : 'Nol' }}
            Rupiah
        </div>

        <br>

        <p style="text-align: right; font-weight: bold;">{{ Carbon::now()->format('d F Y') }}
        </p>
        <p style="text-align: right; font-weight: bold;">{{ $company->name }}</p>

        <br>

        <h2 style="text-align: center; font-size: 24px;">Rp.
            {{ number_format($totalPrice * 0.11 + $totalPrice, 0, '.', ',') }}</h2>

        <br>

        {{-- <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 30%;">Pembayaran ke Rekening Bank:</td>
                <td style="width: 70%;"></td>
            </tr>
            <tr>
                <td>Bank</td>
                <td>: Bank Mandiri - Roxy</td>
            </tr>
            <tr>
                <td>Account No</td>
                <td>: </td>
            </tr>
            <tr>
                <td>Account Name</td>
                <td>: <strong>David Sutoyo</strong></td>
            </tr>
        </table> --}}

        {{-- <br><br> --}}

        <p style="text-align: right;">Director</p>
        <p style="text-align: right; font-weight: bold;">{{ $company->owner }}</p>

    </div>

</body>

</html>
