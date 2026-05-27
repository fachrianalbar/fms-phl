<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Report Maintenance Item</title>
</head>

<body>
    <table style="width: 100%; border-collapse: collapse; border: 1px solid black;">
        <thead>
            <tr>
                <th colspan="11" style="font-weight: bold; font-size: 20px; text-align: center; padding: 10px;">
                    Report Maintenance Item</th>
            </tr>
            <tr>
                <th style="font-size: 14px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">No</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Tanggal</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">No Maintenance</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Warehouse</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Kendaraan</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Item</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Description Item</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Qty</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Harga</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Total Harga</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Created At</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($details as $item)
                <tr>
                    <td style="text-align: center; border: 1px solid black; vertical-align: middle;">{{ $loop->iteration }}</td>
                    <td style="text-align: center; border: 1px solid black; vertical-align: middle;">{{ $item->maintenance->date ? date('d-m-Y', strtotime($item->maintenance->date)) : '' }}</td>
                    <td style="text-align: center; border: 1px solid black; vertical-align: middle;">{{ $item->maintenanceCode }}</td>
                    <td style="text-align: left; border: 1px solid black; vertical-align: middle;">{{ $item->maintenance->warehouse->name ?? '-' }}</td>
                    <td style="text-align: center; border: 1px solid black; vertical-align: middle;">{{ $item->maintenance->fleet->plateNumber ?? '-' }}</td>
                    <td style="text-align: left; border: 1px solid black; vertical-align: middle;">{{ $item->item->name ?? '-' }}</td>
                    <td style="text-align: left; border: 1px solid black; vertical-align: middle;">{{ $item->description ?? '-' }}</td>
                    <td style="text-align: right; border: 1px solid black; vertical-align: middle;">{{ $item->qty }}</td>
                    <td style="text-align: right; border: 1px solid black; vertical-align: middle;">{{ $item->price }}</td>
                    <td style="text-align: right; border: 1px solid black; vertical-align: middle;">{{ $item->total }}</td>
                    <td style="text-align: center; border: 1px solid black; vertical-align: middle;">{{ $item->created_at ? $item->created_at->format('d-m-Y H:i') : '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
