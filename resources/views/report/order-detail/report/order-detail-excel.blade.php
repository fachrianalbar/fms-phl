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
                <th colspan="13" style="font-weight: bold; font-size: 20px; text-align: center; padding: 10px;">
                    Order Detail Report</th>
            </tr>
            <tr>
                <th style="font-size: 14px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">No</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Shipment No</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Order Date</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Customer</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Origin</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Destination</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Plate Number</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Driver</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Sales</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Nama Komponen</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Nominal</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Total Cost</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Profit</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($order as $item)
                @php
                    $sales = $item->routeAmount;
                    $totalCost = 0;
                    if ($item->cost) {
                        foreach ($item->cost as $cost) {
                            $totalCost += $cost->nominal;
                        }
                    }
                    $profit = $sales - $totalCost;
                    
                    $costDetails = [];
                    if ($item->cost) {
                        foreach ($item->cost as $cost) {
                            $costDetails[] = [
                                'name' => ($cost->costComponent->name ?? 'N/A'),
                                'nominal' => $cost->nominal,
                            ];
                        }
                    }
                    $costCount = count($costDetails);
                @endphp

                @if ($costCount == 0)
                    <tr>
                        <td style="text-align: center; border: 1px solid black; vertical-align: middle;">{{ $loop->iteration }}</td>
                        <td style="text-align: center; border: 1px solid black; vertical-align: middle;">{{ mb_strtoupper($item->shipmentNumber ?? '') }}</td>
                        <td style="text-align: center; border: 1px solid black; vertical-align: middle;">{{ $item->orderDate ? date('d-m-Y', strtotime($item->orderDate)) : '' }}</td>
                        <td style="text-align: left; border: 1px solid black; vertical-align: middle;">{{ $item->customer->name ?? '' }}</td>
                        <td style="text-align: left; border: 1px solid black; vertical-align: middle;">{{ $item->route->originLocation->name ?? '' }}</td>
                        <td style="text-align: left; border: 1px solid black; vertical-align: middle;">{{ $item->route->destinationLocation->name ?? '' }}</td>
                        <td style="text-align: center; border: 1px solid black; vertical-align: middle;">{{ $item->fleet->plateNumber ?? '' }}</td>
                        <td style="text-align: left; border: 1px solid black; vertical-align: middle;">{{ $item->driver->name ?? '' }}</td>
                        <td style="text-align: right; border: 1px solid black; vertical-align: middle;">{{ $sales }}</td>
                        <td style="text-align: center; border: 1px solid black; vertical-align: middle;">-</td>
                        <td style="text-align: right; border: 1px solid black; vertical-align: middle;">0</td>
                        <td style="text-align: right; border: 1px solid black; vertical-align: middle;">{{ $totalCost }}</td>
                        <td style="text-align: right; border: 1px solid black; vertical-align: middle;">{{ $profit }}</td>
                    </tr>
                @else
                    @foreach ($costDetails as $index => $c)
                        <tr>
                            @if ($index == 0)
                                <td rowspan="{{ $costCount }}" style="text-align: center; border: 1px solid black; vertical-align: middle;">{{ $loop->parent->iteration }}</td>
                                <td rowspan="{{ $costCount }}" style="text-align: center; border: 1px solid black; vertical-align: middle;">{{ mb_strtoupper($item->shipmentNumber ?? '') }}</td>
                                <td rowspan="{{ $costCount }}" style="text-align: center; border: 1px solid black; vertical-align: middle;">{{ $item->orderDate ? date('d-m-Y', strtotime($item->orderDate)) : '' }}</td>
                                <td rowspan="{{ $costCount }}" style="text-align: left; border: 1px solid black; vertical-align: middle;">{{ $item->customer->name ?? '' }}</td>
                                <td rowspan="{{ $costCount }}" style="text-align: left; border: 1px solid black; vertical-align: middle;">{{ $item->route->originLocation->name ?? '' }}</td>
                                <td rowspan="{{ $costCount }}" style="text-align: left; border: 1px solid black; vertical-align: middle;">{{ $item->route->destinationLocation->name ?? '' }}</td>
                                <td rowspan="{{ $costCount }}" style="text-align: center; border: 1px solid black; vertical-align: middle;">{{ $item->fleet->plateNumber ?? '' }}</td>
                                <td rowspan="{{ $costCount }}" style="text-align: left; border: 1px solid black; vertical-align: middle;">{{ $item->driver->name ?? '' }}</td>
                                <td rowspan="{{ $costCount }}" style="text-align: right; border: 1px solid black; vertical-align: middle;">{{ $sales }}</td>
                            @endif
                            
                            <td style="text-align: left; border: 1px solid black; vertical-align: middle;">{{ $c['name'] }}</td>
                            <td style="text-align: right; border: 1px solid black; vertical-align: middle;">{{ $c['nominal'] }}</td>
                            
                            @if ($index == 0)
                                <td rowspan="{{ $costCount }}" style="text-align: right; border: 1px solid black; vertical-align: middle;">{{ $totalCost }}</td>
                                <td rowspan="{{ $costCount }}" style="text-align: right; border: 1px solid black; vertical-align: middle;">{{ $profit }}</td>
                            @endif
                        </tr>
                    @endforeach
                @endif
            @endforeach
        </tbody>
    </table>
</body>

</html>