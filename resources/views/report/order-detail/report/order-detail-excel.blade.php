<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Order Detail Report</title>
</head>

<body>
    <table style="width: 100%; border-collapse: collapse; border: 1px solid black;">
        <thead>
            <tr>
                <th colspan="12" style="font-weight: bold; font-size: 20px; text-align: center; padding: 10px;">
                    Order Detail Report</th>
            </tr>
            <tr>
                <th style="font-size: 14px; font-weight: bold; text-align: center; border: 1px solid black;">No</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center; border: 1px solid black;">Shipment No</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center; border: 1px solid black;">Order Date</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center; border: 1px solid black;">Customer</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center; border: 1px solid black;">Origin</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center; border: 1px solid black;">Destination</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center; border: 1px solid black;">Plate Number</th>
                <!-- Fleet Type removed from exports -->
                <th style="font-size: 14px; font-weight: bold; text-align: center; border: 1px solid black;">Driver</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center; border: 1px solid black;">Sales</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center; border: 1px solid black;">Cost Detail</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center; border: 1px solid black;">Total Cost</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center; border: 1px solid black;">Profit</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($order as $item)
            <tr>
                <td style="text-align: center; border: 1px solid black;">{{ $loop->iteration }}</td>
                <td style="text-align: center; border: 1px solid black;">{{ mb_strtoupper($item->shipmentNumber ?? '') }}</td>
                <td style="text-align: center; border: 1px solid black;">{{ $item->orderDate ? date('d-m-Y', strtotime($item->orderDate)) : '' }}</td>
                <td style="text-align: left; border: 1px solid black;">{{ $item->customer->name ?? '' }}</td>
                <td style="text-align: left; border: 1px solid black;">{{ $item->route->originLocation->name ?? '' }}</td>
                <td style="text-align: left; border: 1px solid black;">{{ $item->route->destinationLocation->name ?? '' }}</td>
                <td style="text-align: center; border: 1px solid black;">{{ $item->fleet->plateNumber ?? '' }}</td>
                <!-- Fleet Type removed from exports -->
                <td style="text-align: left; border: 1px solid black;">{{ $item->driver->name ?? '' }}</td>
                <td style="text-align: right; border: 1px solid black;">
                    @php
                    $sales = $item->qty * $item->routeAmount;
                    @endphp
                    {{ number_format($sales, 0, ',', '.') }}
                </td>
                <td style="text-align: left; border: 1px solid black;">
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
                <td style="text-align: right; border: 1px solid black;">
                    @php
                    $totalCost = 0;
                    if ($item->cost) {
                    foreach ($item->cost as $cost) {
                    $totalCost += $cost->nominal;
                    }
                    }
                    @endphp
                    {{ number_format($totalCost, 0, ',', '.') }}
                </td>
                <td style="text-align: right; border: 1px solid black;">
                    @php
                    $profit = $sales - $totalCost;
                    @endphp
                    {{ number_format($profit, 0, ',', '.') }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>