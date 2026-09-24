<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: sans-serif; font-size: 8px; color: #1f2937; }
        h1 { font-size: 15px; text-align: center; margin: 0 0 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #64748b; padding: 5px 4px; }
        th { background: #e2e8f0; font-weight: bold; text-align: center; }
        .center { text-align: center; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <table>
        <thead>
            <tr>
                @foreach (['No', 'Tanggal Order', 'No. Polisi', 'Rute', 'Driver', 'Tipe Armada', 'Kode Order', 'Shipment No.', 'Qty', 'Harga', 'Harga Vendor'] as $heading)
                    <th>{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($orders as $order)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td class="center">{{ $order->orderDate ? \Carbon\Carbon::parse($order->orderDate)->format('d-m-Y') : '-' }}</td>
                    <td>{{ $order->fleet->plateNumber ?? '-' }}</td>
                    <td>{{ $order->route->name ?? '-' }}</td>
                    <td>{{ $order->driver->name ?? '-' }}</td>
                    <td>{{ $order->fleet->type->name ?? '-' }}</td>
                    <td>{{ $order->code ?? '-' }}</td>
                    <td>{{ $order->shipmentNumber ?? '-' }}</td>
                    <td class="right">{{ number_format((float) ($order->qty ?? 0), 2, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format((float) ($order->routeAmount ?? 0), 2, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format((float) ($order->personalVendorPrice ?? $order->vendorPrice ?? 0), 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="11" class="center">Tidak ada data</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
