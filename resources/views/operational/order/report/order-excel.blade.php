<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ __('menu_order.order_report') }}</title>
</head>

<body>
    <table style="width: 100%; border-collapse: collapse; border: 1px solid black;">
        <thead>
            <tr>
                <th colspan="10" style="font-weight: bold; font-size: 20px; text-align: center; padding: 10px;">
                    {{ __('menu_order.order_report') }} Data
                </th>
            </tr>
            <tr>
                <th style="font-size: 14px; font-weight: bold; text-align: center">No</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">{{ __('menu_order.order_date') }}
                </th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">{{ __('menu_order.plate_number') }}
                </th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">{{ __('menu_order.driver') }}</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">{{ __('menu_order.shipment_no') }}
                </th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">{{ __('menu_order.customer') }}</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">{{ __('menu_order.destination') }}
                </th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">{{ __('menu_order.load_type') }}</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">{{ __('menu_order.route_price') }}
                </th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Qty</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">{{ __('menu_order.total_price') }}
                <th style="font-size: 14px; font-weight: bold; text-align: center">{{ __('menu_order.cost') }}</th>

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
                    {{ isset($item->fleet->plateNumber) ? $item->fleet->plateNumber : '' }}
                </td>
                <td style="text-align: center">{{ isset($item->driver->name) ? $item->driver->name : '' }}</td>
                <td style="text-align: center">{{ mb_strtoupper($item->shipmentNumber ?? '') }}</td>
                <td style="text-align: center">{{ isset($item->customer->name) ? $item->customer->name : '' }}</td>
                <td style="text-align: center">
                    {{ isset($item->route->destinationLocation->name) ? $item->route->destinationLocation->name : '' }}
                </td>
                <td style="text-align: center">{{ $item->route->routeType->name ?? '' }}</td>
                <td style="text-align: center">{{ $item->route->price ?? '' }}</td>
                <td style="text-align: center">{{ $item->qty }}</td>
                <td style="text-align: center">{{ $item->route->price * $item->qty }}</td>

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
                    {{ $cost }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>