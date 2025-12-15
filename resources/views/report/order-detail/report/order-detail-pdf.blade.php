<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Order Detail Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid black;
            padding: 4px;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }

        .title {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            padding: 10px;
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
    </style>
</head>

<body>
    <div class="title">Order Detail Report</div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Shipment No</th>
                <th>Order Date</th>
                <th>Customer</th>
                <th>Origin</th>
                <th>Destination</th>
                <th>Plate Number</th>
                <!-- Fleet Type removed from exports -->
                <th>Driver</th>
                <th>Material</th>
                <th>Qty</th>
                <th>Sales</th>
                <th>Cost Detail</th>
                <th>Total Cost</th>
                <th>Profit</th>
            </tr>
        </thead>

        <tbody>
            @php
            $grandTotalSales = 0;
            $grandTotalCost = 0;
            $grandTotalProfit = 0;
            @endphp
            @foreach ($data as $item)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td class="text-center">{{ $item->shipmentNumber }}</td>
                <td class="text-center">{{ $item->orderDate ? date('d-m-Y', strtotime($item->orderDate)) : '' }}</td>
                <td class="text-left">{{ $item->customer->name ?? '' }}</td>
                <td class="text-left">{{ $item->route->originLocation->name ?? '' }}</td>
                <td class="text-left">{{ $item->route->destinationLocation->name ?? '' }}</td>
                <td class="text-center">{{ $item->fleet->plateNumber ?? '' }}</td>
                <!-- Fleet Type removed from exports -->
                <td class="text-left">{{ $item->driver->name ?? '' }}</td>
                <td class="text-left">{{ $item->material->name ?? '' }}</td>
                <td class="text-center">{{ $item->qty }}</td>
                <td class="text-right">
                    @php
                    // `routeAmount` stored as total for the order
                    $sales = $item->routeAmount;
                    $grandTotalSales += $sales;
                    @endphp
                    {{ number_format($sales, 0, ',', '.') }}
                </td>
                <td class="text-left">
                    @php
                    $costDetails = [];
                    if ($item->cost) {
                    foreach ($item->cost as $cost) {
                    $costDetails[] = [
                    'name' => ($cost->costComponent->name ?? 'N/A'),
                    'nominal' => $cost->nominal,
                    ];
                    }
                    }
                    @endphp
                    @if(empty($costDetails))
                    -
                    @else
                    @foreach($costDetails as $k => $c)
                    <div>{{ $k+1 }}. {{ $c['name'] }}: {{ number_format($c['nominal'],0,',','.') }}</div>
                    @endforeach
                    @endif
                </td>
                <td class="text-right">
                    @php
                    $totalCost = 0;
                    if ($item->cost) {
                    foreach ($item->cost as $cost) {
                    $totalCost += $cost->nominal;
                    }
                    }
                    $grandTotalCost += $totalCost;
                    @endphp
                    {{ number_format($totalCost, 0, ',', '.') }}
                </td>
                <td class="text-right">
                    @php
                    $profit = $sales - $totalCost;
                    $grandTotalProfit += $profit;
                    @endphp
                    {{ number_format($profit, 0, ',', '.') }}
                </td>
            </tr>
            @endforeach
            <tr>
                <th colspan="10" class="text-right">TOTAL</th>
                <th class="text-right">{{ number_format($grandTotalSales, 0, ',', '.') }}</th>
                <th></th>
                <th class="text-right">{{ number_format($grandTotalCost, 0, ',', '.') }}</th>
                <th class="text-right">{{ number_format($grandTotalProfit, 0, ',', '.') }}</th>
            </tr>
        </tbody>
    </table>
</body>

</html>