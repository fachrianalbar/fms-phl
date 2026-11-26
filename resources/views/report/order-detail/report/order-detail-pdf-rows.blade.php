@php
    $startIndex = isset($start) ? (int) $start : 0;
@endphp
@foreach ($data as $item)
    @php
        $qty = (float) ($item['qty'] ?? 0);
        $formattedQty = rtrim(rtrim(number_format($qty, 2, ',', '.'), '0'), ',');
        $formattedQty = $formattedQty !== '' ? $formattedQty : '0';

        $incomeDetails = $item['income_details'] ?? [];
        $incomeTotal = (float) ($item['income_total'] ?? 0);

        $commissionDetails = $item['commission_details'] ?? [];
        $commissionTotal = (float) ($item['commission_total'] ?? 0);

        $salaryDetails = $item['salary_details'] ?? [];
        $salaryTotal = (float) ($item['salary_total'] ?? 0);

        $otherCostDetails = $item['other_cost_details'] ?? [];
        $totalCost = (float) ($item['total_cost'] ?? 0);

        $grossProfit = (float) ($item['gross_profit'] ?? 0);
        $tax = (float) ($item['tax'] ?? 0);
        $netProfit = (float) ($item['net_profit'] ?? 0);
    @endphp
    <tr>
        <td class="text-center">{{ $startIndex + $loop->iteration }}</td>
        <td class="text-center">{{ $item['shipment_number'] ?? '' }}</td>
        <td class="text-center">{{ $item['order_date'] ?? '' }}</td>
        <td class="text-left">{{ $item['customer_name'] ?? '' }}</td>
        <td class="text-left">{{ $item['origin_name'] ?? '' }}</td>
        <td class="text-left">{{ $item['destination_name'] ?? '' }}</td>
        <td class="text-center">{{ $item['plate_number'] ?? '' }}</td>
        <td class="text-left">{{ $item['driver_name'] ?? '' }}</td>
        <td class="text-center">{{ $formattedQty }}</td>
        <td class="text-right">{{ number_format((float) ($item['unit_price'] ?? 0), 0, ',', '.') }}</td>
        <td class="text-right">
            {{ number_format((float) ($item['omset'] ?? 0), 0, ',', '.') }}
            @if (! empty($item['is_cross_internal']))
                <div style="font-size: 6.5px; color: #0284c7;">(Antar PT)</div>
            @endif
        </td>
        <td class="text-left">
            @if (empty($incomeDetails))
                -
            @else
                @foreach ($incomeDetails as $k => $ic)
                    <div>{{ $k + 1 }}. {{ $ic['name'] }}: {{ number_format((float) $ic['nominal'], 0, ',', '.') }}</div>
                @endforeach
                <div style="border-top: 1px solid #000; font-weight: bold; margin-top: 2px; padding-top: 2px;">Total: {{ number_format($incomeTotal, 0, ',', '.') }}</div>
            @endif
        </td>
        <td class="text-left">
            @if (empty($commissionDetails))
                -
            @else
                @foreach ($commissionDetails as $k => $cm)
                    <div>{{ $k + 1 }}. {{ $cm['name'] }}: {{ number_format((float) $cm['nominal'], 0, ',', '.') }}</div>
                @endforeach
                <div style="border-top: 1px solid #000; font-weight: bold; margin-top: 2px; padding-top: 2px;">Total: {{ number_format($commissionTotal, 0, ',', '.') }}</div>
            @endif
        </td>
        <td class="text-left">
            @if (empty($salaryDetails))
                -
            @else
                @foreach ($salaryDetails as $k => $sl)
                    <div>{{ $k + 1 }}. {{ $sl['name'] }}: {{ number_format((float) $sl['nominal'], 0, ',', '.') }}</div>
                @endforeach
                <div style="border-top: 1px solid #000; font-weight: bold; margin-top: 2px; padding-top: 2px;">Total: {{ number_format($salaryTotal, 0, ',', '.') }}</div>
            @endif
        </td>
        <td class="text-left">
            @if (empty($otherCostDetails))
                -
            @else
                @foreach ($otherCostDetails as $k => $c)
                    <div>{{ $k + 1 }}. {{ $c['name'] }}: {{ number_format((float) $c['nominal'], 0, ',', '.') }}</div>
                @endforeach
            @endif
        </td>
        <td class="text-right">{{ number_format($totalCost, 0, ',', '.') }}</td>
        <td class="text-right">{{ number_format($grossProfit, 0, ',', '.') }}</td>
        <td class="text-right">{{ $tax > 0 ? number_format($tax, 0, ',', '.') : '-' }}</td>
        <td class="text-right" style="font-weight: bold;">{{ number_format($netProfit, 0, ',', '.') }}</td>
    </tr>
@endforeach
