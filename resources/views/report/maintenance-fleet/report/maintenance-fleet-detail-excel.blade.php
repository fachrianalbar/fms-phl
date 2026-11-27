<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Detail Perawatan Armada</title>
</head>

<body>
    @php
        $startLabel = $startDate ? \Carbon\Carbon::parse($startDate)->format('d-m-Y') : '-';
        $endLabel = $endDate ? \Carbon\Carbon::parse($endDate)->format('d-m-Y') : '-';
        $runningQty = 0;
        $runningCost = 0;
        $th = 'font-size: 12px; font-weight: bold; text-align: center;';
    @endphp

    <table style="width: 100%; border-collapse: collapse; border: 1px solid black;">
        <thead>
            <tr>
                <th colspan="11" style="font-weight: bold; font-size: 18px; text-align: center; padding: 10px;">
                    Maintenance Fleet Detail Report Data
                </th>
            </tr>
            <tr>
                <th colspan="11" style="text-align: left; padding: 6px; font-size: 12px;">
                    No Polisi: {{ $fleet->plateNumber }} | Perusahaan: {{ $fleet->company?->name ?? '-' }} | Type:
                    {{ $fleet->company?->type ?? 'Internal' }} | Supplier: {{ $supplierName ?? 'All' }} | Date:
                    {{ $startLabel }} s/d {{ $endLabel }}
                </th>
            </tr>
            <tr>
                <th colspan="11" style="text-align: left; padding: 6px; font-size: 12px;">
                    Total Maint: {{ number_format($summary['totalMaintenance'], 0, ',', '.') }} | Total Qty:
                    {{ number_format($summary['totalQty'], 1, ',', '.') }} | Total Pembayaran:
                    {{ number_format($summary['totalCost'], 0, ',', '.') }} | Utang Jth Tempo:
                    {{ number_format($summary['utangJatuhTempo'], 0, ',', '.') }} | Total Utang:
                    {{ number_format($summary['totalUtang'], 0, ',', '.') }}
                </th>
            </tr>
            <tr>
                <th style="{{ $th }}">No</th>
                <th style="{{ $th }}">Kode MNT</th>
                <th style="{{ $th }}">Tgl &amp; Jam</th>
                <th style="{{ $th }}">Kode Item</th>
                <th style="{{ $th }}">Nama Item</th>
                <th style="{{ $th }}">Supplier</th>
                <th style="{{ $th }}">Qty</th>
                <th style="{{ $th }}">Harga</th>
                <th style="{{ $th }}">Total</th>
                <th style="{{ $th }}">Tgl Jth Tempo</th>
                <th style="{{ $th }}">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                @php
                    $runningQty += (float) $row->qty;
                    $runningCost += (float) $row->total;
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $loop->iteration }}</td>
                    <td style="text-align: center;">{{ $row->code }}</td>
                    <td style="text-align: center;">{{ \Carbon\Carbon::parse($row->date)->format('d-m-Y') }} {{ \Carbon\Carbon::parse($row->time)->format('H:i') }}</td>
                    <td style="text-align: center;">{{ $row->itemCode }}</td>
                    <td style="text-align: center;">{{ $row->itemName ?: '-' }}</td>
                    <td style="text-align: center;">{{ $row->supplierName ?: '-' }}</td>
                    <td style="text-align: center;">{{ number_format((float) $row->qty, 1, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format((float) $row->price, 0, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format((float) $row->total, 0, ',', '.') }}</td>
                    <td style="text-align: center;">{{ $row->dueDate ? \Carbon\Carbon::parse($row->dueDate)->format('d-m-Y') : '-' }}</td>
                    <td style="text-align: center;">{{ \App\Services\Report\MaintenancePaymentStatusService::statusLabel($row->paymentStatus) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" style="text-align: center;">No data found</td>
                </tr>
            @endforelse
            <tr>
                <td colspan="6" style="text-align: right; font-weight: bold;">TOTAL ITEM USAGE</td>
                <td style="text-align: center; font-weight: bold;">{{ number_format($runningQty, 1, ',', '.') }}</td>
                <td></td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($runningCost, 0, ',', '.') }}</td>
                <td colspan="2"></td>
            </tr>
        </tbody>
    </table>
</body>

</html>
