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
                <th colspan="16" style="font-weight: bold; font-size: 20px; text-align: center; padding: 10px;">
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
                <th style="font-size: 14px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Nama Komponen (Pendapatan)</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Nominal Pendapatan</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Total Pendapatan</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Nama Komponen (Biaya)</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Nominal Biaya</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Total Biaya</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Profit</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($order as $item)
                @php
                    $sales = $item->routeAmount;

                    // On Charge = Pendapatan
                    $incomeDetails = [];
                    $incomeTotal = 0;
                    // Off Charge = Biaya
                    $costDetails = [];
                    $totalCost = 0;

                    if ($item->cost) {
                        foreach ($item->cost as $cost) {
                            if (strtolower($cost->type) === 'on charge') {
                                $incomeDetails[] = [
                                    'name'    => ($cost->costComponent->name ?? 'N/A'),
                                    'nominal' => $cost->nominal,
                                ];
                                $incomeTotal += $cost->nominal;
                            } else {
                                $costDetails[] = [
                                    'name'    => ($cost->costComponent->name ?? 'N/A'),
                                    'nominal' => $cost->nominal,
                                ];
                                $totalCost += $cost->nominal;
                            }
                        }
                    }

                    // Profit = Sales + Income - Total Cost
                    $profit = $sales + $incomeTotal - $totalCost;

                    $incomeCount = count($incomeDetails);
                    $costCount   = count($costDetails);
                    // Total rows = max of income rows and cost rows (at least 1)
                    $maxRows = max($incomeCount, $costCount, 1);
                @endphp

                @for ($rowIdx = 0; $rowIdx < $maxRows; $rowIdx++)
                    <tr>
                        {{-- Order info columns only on first row --}}
                        @if ($rowIdx == 0)
                            <td rowspan="{{ $maxRows }}" style="text-align: center; border: 1px solid black; vertical-align: middle;">{{ $loop->iteration }}</td>
                            <td rowspan="{{ $maxRows }}" style="text-align: center; border: 1px solid black; vertical-align: middle;">{{ mb_strtoupper($item->shipmentNumber ?? '') }}</td>
                            <td rowspan="{{ $maxRows }}" style="text-align: center; border: 1px solid black; vertical-align: middle;">{{ $item->orderDate ? date('d-m-Y', strtotime($item->orderDate)) : '' }}</td>
                            <td rowspan="{{ $maxRows }}" style="text-align: left; border: 1px solid black; vertical-align: middle;">{{ $item->customer->name ?? '' }}</td>
                            <td rowspan="{{ $maxRows }}" style="text-align: left; border: 1px solid black; vertical-align: middle;">{{ $item->route->originLocation->name ?? '' }}</td>
                            <td rowspan="{{ $maxRows }}" style="text-align: left; border: 1px solid black; vertical-align: middle;">{{ $item->route->destinationLocation->name ?? '' }}</td>
                            <td rowspan="{{ $maxRows }}" style="text-align: center; border: 1px solid black; vertical-align: middle;">{{ $item->fleet->plateNumber ?? '' }}</td>
                            <td rowspan="{{ $maxRows }}" style="text-align: left; border: 1px solid black; vertical-align: middle;">{{ $item->driver->name ?? '' }}</td>
                            <td rowspan="{{ $maxRows }}" style="text-align: right; border: 1px solid black; vertical-align: middle;">{{ $sales }}</td>
                        @endif

                        {{-- Income (On Charge) breakdown --}}
                        @if ($rowIdx < $incomeCount)
                            <td style="text-align: left; border: 1px solid black; vertical-align: middle;">{{ $incomeDetails[$rowIdx]['name'] }}</td>
                            <td style="text-align: right; border: 1px solid black; vertical-align: middle;">{{ $incomeDetails[$rowIdx]['nominal'] }}</td>
                        @else
                            <td style="border: 1px solid black;"></td>
                            <td style="border: 1px solid black;"></td>
                        @endif

                        {{-- Total Pendapatan only on first row --}}
                        @if ($rowIdx == 0)
                            <td rowspan="{{ $maxRows }}" style="text-align: right; border: 1px solid black; vertical-align: middle;">{{ $incomeTotal }}</td>
                        @endif

                        {{-- Cost (Off Charge) breakdown --}}
                        @if ($rowIdx < $costCount)
                            <td style="text-align: left; border: 1px solid black; vertical-align: middle;">{{ $costDetails[$rowIdx]['name'] }}</td>
                            <td style="text-align: right; border: 1px solid black; vertical-align: middle;">{{ $costDetails[$rowIdx]['nominal'] }}</td>
                        @else
                            <td style="border: 1px solid black;"></td>
                            <td style="border: 1px solid black;"></td>
                        @endif

                        {{-- Total Biaya & Profit only on first row --}}
                        @if ($rowIdx == 0)
                            <td rowspan="{{ $maxRows }}" style="text-align: right; border: 1px solid black; vertical-align: middle;">{{ $totalCost }}</td>
                            <td rowspan="{{ $maxRows }}" style="text-align: right; border: 1px solid black; vertical-align: middle;">{{ $profit }}</td>
                        @endif
                    </tr>
                @endfor
            @endforeach
        </tbody>
    </table>
</body>

</html>