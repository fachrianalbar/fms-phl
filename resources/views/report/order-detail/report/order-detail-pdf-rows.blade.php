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
        @endphp
        {{ number_format($totalCost, 0, ',', '.') }}
    </td>
    <td class="text-right">
        @php
        $profit = $item->routeAmount - $totalCost;
        @endphp
        {{ number_format($profit, 0, ',', '.') }}
    </td>
</tr>
@endforeach