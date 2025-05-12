<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Down Payment Report</title>
</head>

<style>
    .title {
        text-align: center
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table,
    .data-table th,
    .data-table td {
        border: 1px solid black;
    }

    .text-center {
        text-align: center
    }

    .text-right {
        text-align: right
    }
</style>

<body>
    <h2 class="title">Data Down Payment Report</h2>
    <h2 class="title">{{ $data->code . ' : ' . $data->name }}</h2>

    <table class="data-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Code</th>
                <th>PIC</th>
                <th>DP / Return Date</th>
                <th>Type</th>
                <th>Price</th>
            </tr>
        </thead>
        <tbody>
            @php
                use Carbon\Carbon;
                $dp = 0;
                $return = 0;
                $total = 0;
            @endphp
            @if (count($data->downPayment) > 0)
                @foreach ($data->downPayment as $item)
                    @if ($item->type == 'Dp')
                        @php
                            $dp += $item->nominal;
                            $total += $item->nominal;
                        @endphp
                    @endif
                    @if ($item->type == 'Return')
                        @php
                            $return += $item->nominal;
                            $total -= $item->nominal;
                        @endphp
                    @endif
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td class="text-center">{{ $item->code }}</td>
                        <td class="text-center">{{ $item->picUser->name }}</td>
                        <td class="text-center">
                            @php
                                $date = Carbon::parse($item->date)->format('d-M-Y');
                                $time = Carbon::parse($item->time)->format('H:i');
                            @endphp
                            {{ $date . ' ' . $time }}
                        </td>
                        <td class="text-center">{{ $item->type }}</td>
                        <td class="text-right">{{ number_format($item->nominal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="6" class="text-center">Data Not Found</td>
                </tr>
            @endif
            <tr>
                <td colspan="5">Total Pinjam: </td>
                <td class="text-right">{{ number_format($dp, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="5">Total Bayar: </td>
                <td class="text-right">{{ number_format($return, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="5">Total: </td>
                <td class="text-right">{{ number_format($total, 0, ',', '.') }}</td>
            </tr>

        </tbody>
    </table>
</body>

</html>
