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
        $tdLeft = 'text-align: left;';
        $tdNum = 'text-align: right; mso-number-format:#,##0;';
        $tdQty = 'text-align: right; mso-number-format:#,##0.0;';
    @endphp

    <table style="width: 100%; border-collapse: collapse; border: 1px solid black;">
        <thead>
            <tr>
                <th colspan="12" style="font-weight: bold; font-size: 18px; text-align: center; padding: 10px;">
                    Maintenance Fleet Detail Report Data
                </th>
            </tr>
            <tr>
                <th colspan="12" style="text-align: left; padding: 6px; font-size: 12px;">
                    No Polisi: {{ $fleet->plateNumber }} | Perusahaan: {{ $fleet->company?->name ?? '-' }} | Type:
                    {{ $fleet->company?->type ?? 'Internal' }} | Supplier: {{ $supplierName ?? 'All' }} | Date:
                    {{ $startLabel }} s/d {{ $endLabel }}
                </th>
            </tr>
            <tr>
                <th colspan="12" style="text-align: left; padding: 6px; font-size: 12px;">
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
                <th style="{{ $th }}">Keterangan</th>
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
                    <td style="{{ $tdLeft }}">{{ $loop->iteration }}</td>
                    <td style="{{ $tdLeft }}">{{ $row->code }}</td>
                    <td style="{{ $tdLeft }}">{{ \Carbon\Carbon::parse($row->date)->format('d-m-Y') }} {{ \Carbon\Carbon::parse($row->time)->format('H:i') }}</td>
                    <td style="{{ $tdLeft }}">{{ $row->itemCode }}</td>
                    <td style="{{ $tdLeft }}">{{ $row->itemName ?: '-' }}</td>
                    <td style="{{ $tdLeft }}">{{ $row->description ?: '-' }}</td>
                    <td style="{{ $tdLeft }}">{{ $row->supplierName ?: '-' }}</td>
                    <td style="{{ $tdQty }}">{{ (float) $row->qty }}</td>
                    <td style="{{ $tdNum }}">{{ (float) $row->price }}</td>
                    <td style="{{ $tdNum }}">{{ (float) $row->total }}</td>
                    <td style="{{ $tdLeft }}">{{ $row->dueDate ? \Carbon\Carbon::parse($row->dueDate)->format('d-m-Y') : '-' }}</td>
                    <td style="{{ $tdLeft }}">{{ \App\Services\Report\MaintenancePaymentStatusService::statusLabel($row->paymentStatus) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" style="text-align: center;">No data found</td>
                </tr>
            @endforelse
            <tr>
                <td colspan="7" style="text-align: right; font-weight: bold;">TOTAL ITEM USAGE</td>
                <td style="{{ $tdQty }} font-weight: bold;">{{ $runningQty }}</td>
                <td></td>
                <td style="{{ $tdNum }} font-weight: bold;">{{ $runningCost }}</td>
                <td colspan="2"></td>
            </tr>
        </tbody>
    </table>
</body>

</html>
