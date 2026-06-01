@php
$startIndex = isset($start) ? (int) $start : 0;
@endphp
@foreach ($data as $item)
<tr>
    <td class="text-center">{{ $startIndex + $loop->iteration }}</td>
    <td class="text-center">{{ $item->shipmentNumber }}</td>
    <td class="text-center">{{ $item->orderDate ? date('d-m-Y', strtotime($item->orderDate)) : '' }}</td>
    <td class="text-left">{{ $item->customer->name ?? '' }}</td>
    <td class="text-left">{{ $item->route->originLocation->name ?? '' }}</td>
    <td class="text-left">{{ $item->route->destinationLocation->name ?? '' }}</td>
    <td class="text-center">{{ $item->fleet->plateNumber ?? '' }}</td>
    <td class="text-left">{{ $item->driver->name ?? '' }}</td>
    <td class="text-right">
        @php
        // `routeAmount` stored as total (unit price * qty)
        $sales = $item->routeAmount;
        @endphp
        {{ number_format($sales, 0, ',', '.') }}
    </td>
    <td class="text-left">
        @php
        // Pendapatan = On Charge costs breakdown
        $incomeDetails = [];
        $incomeTotal = 0;
        if ($item->cost) {
            foreach ($item->cost as $cost) {
                if (strtolower($cost->type) === 'on charge') {
                    $incomeDetails[] = [
                        'name' => ($cost->costComponent->name ?? 'N/A'),
                        'nominal' => $cost->nominal,
                    ];
                    $incomeTotal += $cost->nominal;
                }
            }
        }
        @endphp
        @if(empty($incomeDetails))
        -
        @else
        @foreach($incomeDetails as $k => $ic)
        <div>{{ $k+1 }}. {{ $ic['name'] }}: {{ number_format($ic['nominal'],0,',','.') }}</div>
        @endforeach
        <div style="border-top:1px solid #000; font-weight:bold; margin-top:2px; padding-top:2px;">Total: {{ number_format($incomeTotal,0,',','.') }}</div>
        @endif
    </td>
    <td class="text-left">
        @php
        $costDetails = [];
        if ($item->cost) {
            foreach ($item->cost as $cost) {
                // Only show Off Charge in detail
                if (strtolower($cost->type) !== 'on charge') {
                    $costDetails[] = [
                        'name' => ($cost->costComponent->name ?? 'N/A'),
                        'nominal' => $cost->nominal,
                    ];
                }
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
                // Only sum Off Charge costs
                if (strtolower($cost->type) !== 'on charge') {
                    $totalCost += $cost->nominal;
                }
            }
        }
        @endphp
        {{ number_format($totalCost, 0, ',', '.') }}
    </td>
    <td class="text-right">
        @php
        // Profit = Sales + Income (On Charge) - Total Cost (Off Charge)
        $profit = $item->routeAmount + $incomeTotal - $totalCost;
        @endphp
        {{ number_format($profit, 0, ',', '.') }}
    </td>
</tr>
@endforeach