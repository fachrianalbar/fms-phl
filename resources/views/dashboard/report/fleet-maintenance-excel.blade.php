<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Fleet Maintenance Report Data</title>
</head>

<body>
    <table style="width: 100%; border-collapse: collapse; border: 1px solid black;">
        <thead>
            <tr>
                <th colspan="4" style="font-weight: bold; font-size: 20px; text-align: center; padding: 10px;">
                    Fleet Maintenance Report Data</th>
            </tr>
            <tr>
                <th style="font-size: 14px; font-weight: bold; text-align: center">No</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Fleet Number</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Total Maintenance</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Total Price</th>
            </tr>
        </thead>
        <tbody>

            @foreach ($data as $item)
                <tr>
                    <td style="text-align: center">{{ $loop->iteration }}</td>
                    <td style="text-align: center">{{ $item->plateNumber }}</td>
                    <td style="text-align: center">{{ $item->total }}</td>
                    <td style="text-align: center">{{ $item->price ?? 0 }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>
