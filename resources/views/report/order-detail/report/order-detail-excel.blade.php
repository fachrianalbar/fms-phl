<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Order Detail Report</title>
</head>

<body>
    <table style="width: 100%; border-collapse: collapse; border: 1px solid black;">
        <thead>
            <tr>
                <th colspan="26" style="font-weight: bold; font-size: 20px; text-align: center; padding: 10px;">
                    Order Detail Report
                </th>
            </tr>
            <tr>
                <th style="font-size: 13px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">No</th>
                <th style="font-size: 13px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Shipment No</th>
                <th style="font-size: 13px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Order Date</th>
                <th style="font-size: 13px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Customer</th>
                <th style="font-size: 13px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Origin</th>
                <th style="font-size: 13px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Destination</th>
                <th style="font-size: 13px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Plate Number</th>
                <th style="font-size: 13px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Driver</th>
                <th style="font-size: 13px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Qty</th>
                <th style="font-size: 13px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Harga Satuan</th>
                <th style="font-size: 13px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Total Omset</th>
                <th style="font-size: 13px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Nama Komponen (Biaya Ditagihkan)</th>
                <th style="font-size: 13px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Nominal Biaya Ditagihkan</th>
                <th style="font-size: 13px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Total Biaya Ditagihkan</th>
                <th style="font-size: 13px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Nama Komponen Komisi</th>
                <th style="font-size: 13px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Nominal Komisi</th>
                <th style="font-size: 13px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Total Komisi</th>
                <th style="font-size: 13px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Nama Komponen Gaji</th>
                <th style="font-size: 13px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Nominal Gaji</th>
                <th style="font-size: 13px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Total Gaji</th>
                <th style="font-size: 13px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Nama Komponen (Biaya Lainnya Tidak Ditagihkan)</th>
                <th style="font-size: 13px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Nominal Biaya Lainnya</th>
                <th style="font-size: 13px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Total Biaya Tidak Ditagihkan</th>
                <th style="font-size: 13px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Laba Kotor</th>
                <th style="font-size: 13px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Potongan Pajak (PPh)</th>
                <th style="font-size: 13px; font-weight: bold; text-align: center; border: 1px solid black; background-color: #f2f2f2;">Laba Bersih</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($rows as $row)
                @php
                    $rowNumber = $loop->iteration;
                    $incomeDetails = $row['income_details'] ?? [];
                    $commissionDetails = $row['commission_details'] ?? [];
                    $salaryDetails = $row['salary_details'] ?? [];
                    $otherCostDetails = $row['other_cost_details'] ?? [];

                    $incomeCount = count($incomeDetails);
                    $commissionCount = count($commissionDetails);
                    $salaryCount = count($salaryDetails);
                    $otherCostCount = count($otherCostDetails);

                    $maxRows = max($incomeCount, $commissionCount, $salaryCount, $otherCostCount, 1);
                @endphp

                @for ($rowIdx = 0; $rowIdx < $maxRows; $rowIdx++)
                    <tr>
                        @if ($rowIdx === 0)
                            <td rowspan="{{ $maxRows }}" style="text-align: center; border: 1px solid black; vertical-align: middle;">{{ $rowNumber }}</td>
                            <td rowspan="{{ $maxRows }}" style="text-align: center; border: 1px solid black; vertical-align: middle;">{{ $row['shipment_number'] ?? '' }}</td>
                            <td rowspan="{{ $maxRows }}" style="text-align: center; border: 1px solid black; vertical-align: middle;">{{ $row['order_date'] ?? '' }}</td>
                            <td rowspan="{{ $maxRows }}" style="text-align: left; border: 1px solid black; vertical-align: middle;">{{ $row['customer_name'] ?? '' }}</td>
                            <td rowspan="{{ $maxRows }}" style="text-align: left; border: 1px solid black; vertical-align: middle;">{{ $row['origin_name'] ?? '' }}</td>
                            <td rowspan="{{ $maxRows }}" style="text-align: left; border: 1px solid black; vertical-align: middle;">{{ $row['destination_name'] ?? '' }}</td>
                            <td rowspan="{{ $maxRows }}" style="text-align: center; border: 1px solid black; vertical-align: middle;">{{ $row['plate_number'] ?? '' }}</td>
                            <td rowspan="{{ $maxRows }}" style="text-align: left; border: 1px solid black; vertical-align: middle;">{{ $row['driver_name'] ?? '' }}</td>
                            <td rowspan="{{ $maxRows }}" style="text-align: right; border: 1px solid black; vertical-align: middle;">{{ (float) ($row['qty'] ?? 0) }}</td>
                            <td rowspan="{{ $maxRows }}" style="text-align: right; border: 1px solid black; vertical-align: middle;">{{ (float) ($row['unit_price'] ?? 0) }}</td>
                            <td rowspan="{{ $maxRows }}" style="text-align: right; border: 1px solid black; vertical-align: middle;">{{ (float) ($row['omset'] ?? 0) }}</td>
                        @endif

                        {{-- L & M: Biaya Ditagihkan --}}
                        @if ($rowIdx < $incomeCount)
                            <td style="text-align: left; border: 1px solid black; vertical-align: middle;">{{ $incomeDetails[$rowIdx]['name'] }}</td>
                            <td style="text-align: right; border: 1px solid black; vertical-align: middle;">{{ (float) $incomeDetails[$rowIdx]['nominal'] }}</td>
                        @else
                            <td style="border: 1px solid black;"></td>
                            <td style="border: 1px solid black;"></td>
                        @endif

                        @if ($rowIdx === 0)
                            <td rowspan="{{ $maxRows }}" style="text-align: right; border: 1px solid black; vertical-align: middle;">{{ (float) ($row['income_total'] ?? 0) }}</td>
                        @endif

                        {{-- O & P: Komisi --}}
                        @if ($rowIdx < $commissionCount)
                            <td style="text-align: left; border: 1px solid black; vertical-align: middle;">{{ $commissionDetails[$rowIdx]['name'] }}</td>
                            <td style="text-align: right; border: 1px solid black; vertical-align: middle;">{{ (float) $commissionDetails[$rowIdx]['nominal'] }}</td>
                        @else
                            <td style="border: 1px solid black;"></td>
                            <td style="border: 1px solid black;"></td>
                        @endif

                        @if ($rowIdx === 0)
                            <td rowspan="{{ $maxRows }}" style="text-align: right; border: 1px solid black; vertical-align: middle;">{{ (float) ($row['commission_total'] ?? 0) }}</td>
                        @endif

                        {{-- R & S: Gaji --}}
                        @if ($rowIdx < $salaryCount)
                            <td style="text-align: left; border: 1px solid black; vertical-align: middle;">{{ $salaryDetails[$rowIdx]['name'] }}</td>
                            <td style="text-align: right; border: 1px solid black; vertical-align: middle;">{{ (float) $salaryDetails[$rowIdx]['nominal'] }}</td>
                        @else
                            <td style="border: 1px solid black;"></td>
                            <td style="border: 1px solid black;"></td>
                        @endif

                        @if ($rowIdx === 0)
                            <td rowspan="{{ $maxRows }}" style="text-align: right; border: 1px solid black; vertical-align: middle;">{{ (float) ($row['salary_total'] ?? 0) }}</td>
                        @endif

                        {{-- U & V: Biaya Lainnya Tidak Ditagihkan --}}
                        @if ($rowIdx < $otherCostCount)
                            <td style="text-align: left; border: 1px solid black; vertical-align: middle;">{{ $otherCostDetails[$rowIdx]['name'] }}</td>
                            <td style="text-align: right; border: 1px solid black; vertical-align: middle;">{{ (float) $otherCostDetails[$rowIdx]['nominal'] }}</td>
                        @else
                            <td style="border: 1px solid black;"></td>
                            <td style="border: 1px solid black;"></td>
                        @endif

                        @if ($rowIdx === 0)
                            <td rowspan="{{ $maxRows }}" style="text-align: right; border: 1px solid black; vertical-align: middle;">{{ (float) ($row['total_cost'] ?? 0) }}</td>
                            <td rowspan="{{ $maxRows }}" style="text-align: right; border: 1px solid black; vertical-align: middle;">{{ (float) ($row['gross_profit'] ?? 0) }}</td>
                            <td rowspan="{{ $maxRows }}" style="text-align: right; border: 1px solid black; vertical-align: middle;">{{ (float) ($row['tax'] ?? 0) }}</td>
                            <td rowspan="{{ $maxRows }}" style="text-align: right; border: 1px solid black; vertical-align: middle;">{{ (float) ($row['net_profit'] ?? 0) }}</td>
                        @endif
                    </tr>
                @endfor
            @endforeach

            <tr>
                <th colspan="10" style="font-weight: bold; text-align: right; border: 1px solid black; background-color: #f2f2f2;">TOTAL</th>
                <th style="font-weight: bold; text-align: right; border: 1px solid black; background-color: #f2f2f2;">{{ (float) ($totals['omset'] ?? 0) }}</th>
                <th style="border: 1px solid black; background-color: #f2f2f2;"></th>
                <th style="border: 1px solid black; background-color: #f2f2f2;"></th>
                <th style="font-weight: bold; text-align: right; border: 1px solid black; background-color: #f2f2f2;">{{ (float) ($totals['income_total'] ?? 0) }}</th>
                <th style="border: 1px solid black; background-color: #f2f2f2;"></th>
                <th style="border: 1px solid black; background-color: #f2f2f2;"></th>
                <th style="font-weight: bold; text-align: right; border: 1px solid black; background-color: #f2f2f2;">{{ (float) ($totals['commission_total'] ?? 0) }}</th>
                <th style="border: 1px solid black; background-color: #f2f2f2;"></th>
                <th style="border: 1px solid black; background-color: #f2f2f2;"></th>
                <th style="font-weight: bold; text-align: right; border: 1px solid black; background-color: #f2f2f2;">{{ (float) ($totals['salary_total'] ?? 0) }}</th>
                <th style="border: 1px solid black; background-color: #f2f2f2;"></th>
                <th style="border: 1px solid black; background-color: #f2f2f2;"></th>
                <th style="font-weight: bold; text-align: right; border: 1px solid black; background-color: #f2f2f2;">{{ (float) ($totals['total_cost'] ?? 0) }}</th>
                <th style="font-weight: bold; text-align: right; border: 1px solid black; background-color: #f2f2f2;">{{ (float) ($totals['gross_profit'] ?? 0) }}</th>
                <th style="font-weight: bold; text-align: right; border: 1px solid black; background-color: #f2f2f2;">{{ (float) ($totals['tax'] ?? 0) }}</th>
                <th style="font-weight: bold; text-align: right; border: 1px solid black; background-color: #f2f2f2;">{{ (float) ($totals['net_profit'] ?? 0) }}</th>
            </tr>
        </tbody>
    </table>
</body>

</html>
