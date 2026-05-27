@foreach ($data as $item)
    <tr>
        <td class="text-center">{{ $start + $loop->iteration }}</td>
        <td class="text-center">{{ $item->maintenance->date ? \Carbon\Carbon::parse($item->maintenance->date)->format('d-m-Y') : '-' }}</td>
        <td class="text-center">{{ $item->maintenanceCode }}</td>
        <td class="text-left">{{ $item->maintenance->warehouse->name ?? '-' }}</td>
        <td class="text-center">{{ $item->maintenance->fleet->plateNumber ?? '-' }}</td>
        <td class="text-left">{{ $item->item->name ?? '-' }}</td>
        <td class="text-left">{{ $item->description ?? '-' }}</td>
        <td class="text-right">{{ number_format((float) $item->qty, 1, ',', '.') }}</td>
        <td class="text-right">Rp {{ number_format((float) $item->price, 0, ',', '.') }}</td>
        <td class="text-right">Rp {{ number_format((float) $item->total, 0, ',', '.') }}</td>
        <td class="text-center">{{ $item->created_at ? $item->created_at->format('d-m-Y H:i') : '-' }}</td>
    </tr>
@endforeach
