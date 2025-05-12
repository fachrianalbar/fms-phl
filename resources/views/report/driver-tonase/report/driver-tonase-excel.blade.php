<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Driver Tonase Report Data</title>
</head>

<body>
    <table style="width: 100%; border-collapse: collapse; border: 1px solid black;">
        <thead>
            <tr>
                <th colspan="4" style="font-weight: bold; font-size: 20px; text-align: center; padding: 10px;">
                    Driver Tonase Report Data</th>
            </tr>
            <tr>
                <th style="font-size: 14px; font-weight: bold; text-align: center">No</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Driver Name</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Customer Name</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Tonase</th>
            </tr>
        </thead>
        <tbody>

            @foreach ($data as $item)
                <tr>
                    <td style="text-align: center">{{ $loop->iteration }}</td>
                    <td style="text-align: center">
                        {{ isset($item->driver->name) ? $item->driver->name : '' }}
                    </td>
                    <td style="text-align: center">
                        {{ isset($item->customer->name) ? $item->customer->name : '' }}
                    </td>
                    <td style="text-align: center">
                        {{ number_format($item->total_tonase, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>
