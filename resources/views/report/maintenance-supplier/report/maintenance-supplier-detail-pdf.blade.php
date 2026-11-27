<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Supplier Detail Report</title>
    <style>
        @page {
            margin: 10px;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fff;
        }

        .container {
            width: 100%;
            margin: 20px auto;
            padding: 10px;
        }

        h1 {
            text-align: center;
            font-size: 17px;
            margin-bottom: 10px;
        }

        .info,
        .summary {
            margin-bottom: 10px;
            font-size: 11px;
        }

        .info p,
        .summary p {
            margin: 2px 0;
            line-height: 1.3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table th,
        table td {
            border: 1px solid #000;
            text-align: center;
            padding: 5px;
            font-size: 9px;
        }

        table th {
            font-weight: bold;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }
    </style>
</head>

<body>
    @php
        $startLabel = $startDate ? \Carbon\Carbon::parse($startDate)->format('d-m-Y') : '-';
        $endLabel = $endDate ? \Carbon\Carbon::parse($endDate)->format('d-m-Y') : '-';
        $detailTotalQty = 0;
        $detailTotalCost = 0;
    @endphp

    <div class="container">
        <h1>LAPORAN DETAIL PERAWATAN PER SUPLIER</h1>

        <div class="info">
            <p><strong>Suplier:</strong> {{ $supplier->name }} ({{ $supplier->code }})</p>
            <p><strong>Perusahaan:</strong> {{ $fleetCompanyName ?? 'All' }}</p>
            <p><strong>No Polisi:</strong> {{ $fleetPlate ?? 'All' }}</p>
            <p><strong>Tanggal:</strong> {{ $startLabel }} s/d {{ $endLabel }}</p>
        </div>

        <div class="summary">
            <p><strong>Total Maint:</strong> {{ number_format($summary['totalMaintenance'], 0, ',', '.') }}</p>
            <p><strong>Total Qty:</strong> {{ number_format($summary['totalQty'], 1, ',', '.') }}</p>
            <p><strong>Total Pembayaran:</strong> Rp {{ number_format($summary['totalCost'], 0, ',', '.') }}</p>
            <p><strong>Utang Jth Tempo:</strong> Rp {{ number_format($summary['utangJatuhTempo'], 0, ',', '.') }}</p>
            <p><strong>Total Utang:</strong> Rp {{ number_format($summary['totalUtang'], 0, ',', '.') }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode MNT</th>
                    <th>Tgl &amp; Jam Pemakaian</th>
                    <th>Kode Item</th>
                    <th>Nama Item</th>
                    <th>Perusahaan</th>
                    <th>No Polisi</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Total</th>
                    <th>Tgl Jth Tempo</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    @php
                        $detailTotalQty += (float) $row->qty;
                        $detailTotalCost += (float) $row->total;
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $row->code }}</td>
                        <td>{{ \Carbon\Carbon::parse($row->date)->format('d-m-Y') }} {{ \Carbon\Carbon::parse($row->time)->format('H:i') }}</td>
                        <td>{{ $row->itemCode }}</td>
                        <td class="text-left">{{ $row->itemName ?: '-' }}</td>
                        <td class="text-left">{{ $row->fleetCompanyName ?: '-' }}</td>
                        <td>{{ $row->plateNumber ?: '-' }}</td>
                        <td>{{ number_format((float) $row->qty, 1, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format((float) $row->price, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format((float) $row->total, 0, ',', '.') }}</td>
                        <td>{{ $row->dueDate ? \Carbon\Carbon::parse($row->dueDate)->format('d-m-Y') : '-' }}</td>
                        <td>{{ \App\Services\Report\MaintenancePaymentStatusService::statusLabel($row->paymentStatus) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12">No data found</td>
                    </tr>
                @endforelse

                <tr class="bold">
                    <td colspan="7" class="text-right">TOTAL ITEM USAGE</td>
                    <td>{{ number_format($detailTotalQty, 1, ',', '.') }}</td>
                    <td></td>
                    <td class="text-right">Rp {{ number_format($detailTotalCost, 0, ',', '.') }}</td>
                    <td colspan="2"></td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>
