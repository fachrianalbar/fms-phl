<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>All Order List Report</title>
</head>

<body>
    <table style="width: 100%; border-collapse: collapse; border: 1px solid black;">
        <thead>
            <tr>
                <th colspan="19" style="font-weight: bold; font-size: 20px; text-align: center; padding: 10px;">
                    All Order List Report Data</th>
            </tr>
            <tr>
                <th colspan="11" style="font-size: 14px; font-weight: bold; text-align: center">All Order Data</th>
                <th colspan="1" style="font-size: 14px; font-weight: bold; text-align: center">Sales</th>
                <th colspan="6" style="font-size: 14px; font-weight: bold; text-align: center">Cost</th>
                <th colspan="1" style="font-size: 14px; font-weight: bold; text-align: center">Margin</th>
            </tr>
            <!-- Sub-header -->
            <tr>
                <th style="font-size: 14px; font-weight: bold; text-align: center">No</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Shipment No</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Order Dates</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Customer Name</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Origin</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Destination</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Fleet</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Fleet Type</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Driver</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Material</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Qty Tonase</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Basic Sales</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Basic Allowance</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Additional Cost</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Bonus</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Tonase</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Gaji</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Total Cost</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Total Margin</th>

            </tr>
        </thead>

        <tbody>
            @php
                use App\Models\Data\TonaseBonus;
                use App\Models\Data\Route;
                $totalCost = 0;
                $totalMargin = 0;
            @endphp
            @foreach ($order as $item)
                <tr>
                    <td style="text-align: center">{{ $loop->iteration }}</td>
                    <td style="text-align: center">{{ $item->shipmentNumber }}</td>
                    <td style="text-align: center">{{ date('d-m-Y', strtotime($item->orderDate)) }}</td>
                    <td style="text-align: center">{{ isset($item->customer->name) ? $item->customer->name : '' }}</td>
                    <td style="text-align: center">
                        {{ isset($item->route->originLocation->name) ? $item->route->originLocation->name : '' }}</td>
                    <td style="text-align: center">
                        {{ isset($item->route->destinationLocation->name) ? $item->route->destinationLocation->name : '' }}
                    </td>
                    <td style="text-align: center">
                        {{ isset($item->fleet->plateNumber) ? $item->fleet->plateNumber : '' }}
                    </td>
                    <td style="text-align: center">
                        {{ isset($item->fleet->type->name) ? $item->fleet->type->name : '' }}
                    </td>
                    <td style="text-align: center">
                        {{ isset($item->driver->name) ? $item->driver->name : '' }}
                    </td>
                    <td style="text-align: center">
                        {{ isset($item->material->name) ? $item->material->name : '' }}
                    </td>
                    <td style="text-align: center">{{ $item->qty }}</td>
                    <td style="text-align: center">
                        @php
                            $basicSales = $item->qty * $item->route->price;

                            $totalMargin = $basicSales;
                        @endphp
                        {{ number_format($basicSales, 0, '.', ',') }}
                    </td>
                    <td style="text-align: center">
                        @php
                            $allowance = 0;

                            if (isset($item->route->routeDetail)) {
                                $data = $item->route->routeDetail;

                                foreach ($data as $it) {
                                    if ($it->costComponent->type == 'Allowance') {
                                        if ($it->amount != 0) {
                                            $allowance = $it->amount;
                                        }

                                        if ($it->percentage) {
                                            $route = Route::where('code', $it->routeCode)->first();

                                            $allowance = $route->price * ($it->percentage / 100);
                                        }
                                    }

                                    if ($it->costComponent->type == 'Allowance Office') {
                                        if ($it->amount != 0) {
                                            $allowance += $it->amount;
                                        }

                                        if ($it->percentage) {
                                            $route = Route::where('code', $it->routeCode)->first();

                                            $allowance += $route->price * ($it->percentage / 100);
                                        }
                                    }
                                }
                                $totalCost = $allowance;
                            }

                        @endphp
                        {{ number_format($allowance, 0, '.', ',') }}
                    </td>
                    <td style="text-align: center">
                        @php
                            $cost = 0;
                            if (isset($item->cost)) {
                                foreach ($item->cost as $it) {
                                    $cost += $it->nominal;
                                }
                            }
                            $totalCost += $cost;
                        @endphp
                        {{ number_format($cost, 0, '.', ',') }}
                    </td>
                    <td style="text-align: center">
                        @php
                            $tonaseBonus = TonaseBonus::where('min', '<=', $item->qty)
                                ->where('max', '>=', $item->qty)
                                ->first();

                            $bonusValue = 0;

                            if ($tonaseBonus) {
                                $bonusValue = $tonaseBonus->value;
                                $totalCost += $bonusValue;
                            }

                        @endphp
                        {{ number_format($bonusValue, 0, '.', ',') }}
                    </td>
                    <td style="text-align: center">
                        @php
                            $tonaseVal = 0;
                            if (isset($item->route->routeTypeCode) && $item->route->routeTypeCode == 'TONASE') {
                                $tonaseVal = $item->route->price;
                                $totalCost += $tonaseVal;
                            }
                        @endphp
                        {{ number_format($tonaseVal, 0, '.', ',') }}
                    </td>
                    <td style="text-align: center">
                        @php
                            $totalCost += 140000;
                        @endphp
                        {{ number_format(140000, 0, '.', ',') }}
                    </td>
                    <td style="text-align: center">
                        @php
                            $totalMargin -= $totalCost;
                        @endphp
                        {{ number_format($totalCost, 0, '.', ',') }}
                    </td>
                    <td style="text-align: center">
                        {{ number_format($totalMargin, 0, '.', ',') }}
                    </td>
                </tr>
            @endforeach
        </tbody>

    </table>

</body>

</html>
