<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Profit and Loss Report</title>
</head>

<body>
    <table style="width: 100%; border-collapse: collapse; border: 1px solid black;">
        <thead>
            <!-- Header Utama -->
            <tr>
                <th colspan="3" style="font-size: 14px; font-weight: bold; text-align: center">Fleet Data</th>
                <th colspan="1" style="font-size: 14px; font-weight: bold; text-align: center">Sales (A)</th>
                <th colspan="2" style="font-size: 14px; font-weight: bold; text-align: center">Cost (B)</th>
                <th rowspan="1" style="font-size: 14px; font-weight: bold; text-align: center">Margin (A - B)
                </th>
            </tr>
            <!-- Sub-header -->
            <tr>
                <th style="font-size: 14px; font-weight: bold; text-align: center">No</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Fleet</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center">Fleet Type</th>

                <th style="font-size: 14px; font-weight: bold; text-align: center">Basic Sales</th>
                {{-- <th>Total Sales</th> --}}

                <th style="font-size: 14px; font-weight: bold; text-align: center">Order Cost</th>

                <th style="font-size: 14px; font-weight: bold; text-align: center">Maintenance</th>

                <th style="font-size: 14px; font-weight: bold; text-align: center">Total Margin</th>
            </tr>
        </thead>

        <tbody>
            @php
                use App\Models\Data\TonaseBonus;
                use Carbon\Carbon;
                use App\Models\Data\Route;
                $totalMargin = 0;
            @endphp

            @foreach ($data as $row)
                <tr>
                    <td style="text-align: center">{{ $loop->iteration }}</td>
                    <td style="text-align: center">{{ $row->plateNumber }}</td>
                    <td style="text-align: center">{{ isset($row->type->name) ? $row->type->name : '' }}</td>
                    {{-- Basic Sales --}}
                    <td style="text-align: center">
                        @php
                            $basicSales = 0;

                            foreach ($row->orders as $item) {
                                $basicSales += $item->qty * $item->route->price;
                            }
                            $totalMargin = $basicSales;
                        @endphp
                        {{ number_format($basicSales, 0, '.', ',') }}
                    </td>
                    {{-- Additional Cost --}}
                    <td style="text-align: center">
                        @php
                            $additionalCost = 0;

                            foreach ($row->orders as $item) {
                                if (isset($item->cost)) {
                                    foreach ($item->cost as $cost) {
                                        $additionalCost += $cost->nominal;
                                    }
                                }
                            }

                            $totalMargin -= $additionalCost;
                        @endphp
                        {{ number_format($additionalCost, 0, '.', ',') }}
                    </td>
                    {{-- Maintenance --}}
                    <td style="text-align: center">
                        @php
                            $maintenance = 0;

                            foreach ($row->maintenances as $item) {
                                foreach ($item->details as $details) {
                                    $maintenance += $details->qty * $details->item->price;
                                }
                            }

                            $totalMargin -= $maintenance;
                        @endphp
                        {{ number_format($maintenance, 0, '.', ',') }}
                    </td>
                    {{-- Total Margin --}}
                    <td style="text-align: center">
                        {{ number_format($totalMargin, 0, '.', ',') }}
                    </td>
                </tr>
            @endforeach
        </tbody>

    </table>

</body>

</html>
