<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
</head>
<body>
<table style="width: 100%; border-collapse: collapse; border: 1px solid #1f2937;">
    <thead>
        <tr>
            <th colspan="11" style="font-size: 18px; font-weight: bold; text-align: center; padding: 10px;">{{ $title }}</th>
        </tr>
        <tr>
            @foreach (['No', 'Tanggal Order', 'No. Polisi', 'Rute', 'Driver', 'Tipe Armada', 'Kode Order', 'Shipment No.', 'Qty', 'Harga', 'Harga Vendor'] as $heading)
                <th style="font-weight: bold; text-align: center; padding: 7px; border: 1px solid #1f2937;">{{ $heading }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($orders as $order)
            <tr>
                <td style="text-align: center; border: 1px solid #1f2937;">{{ $loop->iteration }}</td>
                <td style="text-align: center; border: 1px solid #1f2937;">{{ $order->orderDate ? \Carbon\Carbon::parse($order->orderDate)->format('d-m-Y') : '-' }}</td>
                <td style="border: 1px solid #1f2937;">{{ $order->fleet->plateNumber ?? '-' }}</td>
                <td style="border: 1px solid #1f2937;">{{ $order->route->name ?? '-' }}</td>
                <td style="border: 1px solid #1f2937;">{{ $order->driver->name ?? '-' }}</td>
                <td style="border: 1px solid #1f2937;">{{ $order->fleet->type->name ?? '-' }}</td>
                <td style="border: 1px solid #1f2937;">{{ $order->code ?? '-' }}</td>
                <td style="border: 1px solid #1f2937;">{{ $order->shipmentNumber ?? '-' }}</td>
                <td style="text-align: right; border: 1px solid #1f2937;">{{ number_format((float) ($order->qty ?? 0), 2, ',', '.') }}</td>
                <td style="text-align: right; border: 1px solid #1f2937;">{{ number_format((float) ($order->routeAmount ?? 0), 2, ',', '.') }}</td>
                <td style="text-align: right; border: 1px solid #1f2937;">{{ number_format((float) ($order->personalVendorPrice ?? $order->vendorPrice ?? 0), 2, ',', '.') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>
