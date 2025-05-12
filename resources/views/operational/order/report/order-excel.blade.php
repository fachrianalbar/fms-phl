<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Order Report</title>
</head>

<body>
    <table style="width: 100%; border-collapse: collapse; border: 1px solid black;">
        <thead>
            <tr>
                <th colspan="16" style="font-weight: bold; font-size: 20px; text-align: center; padding: 10px;">
                    Order Report Data</th>
            </tr>
            <tr>
                <th style="font-size: 14px; font-weight: bold; text-align: center">No</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Order Date</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Fleet</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Fleet Type</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Driver</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Shipment No</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Customer Name</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Destination</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Sales Order</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">S.T.O</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Material</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Order Type</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Qty</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Cost</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Tonase</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Add Cost</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Total Cost</th>
            </tr>
        </thead>
        <tbody>
            @php
                use App\Models\Data\TonaseBonus;
                use Carbon\Carbon;
                $totalPrice = 0;
            @endphp
            @foreach ($order as $item)
                <tr>
                    <td style="text-align: center">{{ $loop->iteration }}</td>
                    <td style="text-align: center">{{ Carbon::parse($item->orderDate)->format('d-m-Y') }}</td>
                    <td style="text-align: center">
                        {{ isset($item->fleet->plateNumber) ? $item->fleet->plateNumber : '' }}</td>
                    <td style="text-align: center">{{ isset($item->fleet->type->name) ? $item->fleet->type->name : '' }}
                    </td>
                    <td style="text-align: center">{{ isset($item->driver->name) ? $item->driver->name : '' }}</td>
                    <td style="text-align: center">{{ $item->shipmentNumber }}</td>
                    <td style="text-align: center">{{ isset($item->customer->name) ? $item->customer->name : '' }}</td>
                    <td style="text-align: center">
                        {{ isset($item->route->destinationLocation->name) ? $item->route->destinationLocation->name : '' }}
                    </td>
                    <td style="text-align: center">{{ $item->salesOrder }}</td>
                    <td style="text-align: center">{{ $item->sto }}</td>
                    <td style="text-align: center">{{ isset($item->material->name) ? $item->material->name : '' }}</td>
                    <td style="text-align: center">{{ $item->orderTypeCode }}</td>
                    <td style="text-align: center">{{ $item->qty }}</td>
                    <td style="text-align: center">
                        @php
                            $data = $item->route->routeDetail;

                            $allowance = 0;
                            foreach ($data as $items) {
                                if ($items->costComponent->type == 'Allowance') {
                                    if ($items->amount != 0) {
                                        $allowance += $items->amount;
                                    }

                                    if ($items->percentage) {
                                        $route = Route::where('code', $items->routeCode)->first();

                                        $allowance += $route->price * ($items->percentage / 100);
                                    }
                                }

                                if ($type == 'order-office') {
                                    if ($items->costComponent->type == 'Allowance Office') {
                                        if ($items->amount != 0) {
                                            $allowance += $items->amount;
                                        }

                                        if ($items->percentage) {
                                            $route = Route::where('code', $items->routeCode)->first();

                                            $allowance += $route->price * ($items->percentage / 100);
                                        }
                                    }
                                }
                            }

                            $totalPrice = $allowance;
                        @endphp
                        {{ number_format($allowance, 0, '.', ',') }}
                    </td>
                    <td style="text-align: center">
                        @php
                            $tonaseBonus = TonaseBonus::where('min', '<=', $item->qty)
                                ->where('max', '>=', $item->qty)
                                ->first();

                            $bonus = 0;

                            if ($tonaseBonus) {
                                $bonus = number_format($tonaseBonus->value, 0, '.', ',');
                                $totalPrice += $tonaseBonus->value;
                            }
                        @endphp
                        {{ $bonus }}
                    </td>
                    <td style="text-align: center">
                        @php
                            $cost = 0;
                            if (isset($item->cost)) {
                                foreach ($item->cost as $costs) {
                                    $cost += $costs->nominal;
                                }
                            }
                            $totalPrice += $cost;
                        @endphp
                        {{ number_format($cost, 0, '.', ',') }}
                    </td>
                    <td style="text-align: center">{{ number_format($totalPrice, 0, '.', ',') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>
