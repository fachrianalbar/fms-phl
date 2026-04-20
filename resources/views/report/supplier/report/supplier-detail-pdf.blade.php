<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Sparepart Purchase Detail Report</title>
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

        .info {
            margin-bottom: 10px;
            font-size: 11px;
        }

        .info p {
            margin: 2px 0;
            line-height: 1.3;
        }

        .summary {
            margin: 8px 0 12px;
            font-size: 11px;
        }

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

        $no = 1;
        $detailTotalQty = 0;
        $detailTotalAmount = 0;
    @endphp

    <div class="container">
        <h1>SUPPLIER SPAREPART PURCHASE DETAIL REPORT</h1>

        <div class="info">
            <p><strong>Supplier:</strong> {{ $supplier->name }}</p>
            <p><strong>Date:</strong> {{ $startLabel }} s/d {{ $endLabel }}</p>
        </div>

        <div class="summary">
            <p><strong>Total Purchase:</strong> {{ number_format($totalPurchase, 0, ',', '.') }}</p>
            <p><strong>Total Item:</strong> {{ number_format($totalItem, 0, ',', '.') }}</p>
            <p><strong>Total Qty:</strong> {{ number_format($totalQty, 1, ',', '.') }}</p>
            <p><strong>Total Amount:</strong> Rp {{ number_format($totalAmount, 0, ',', '.') }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Purchase Code</th>
                    <th>Date</th>
                    <th>Due Date</th>
                    <th>Warehouse</th>
                    <th>Item Code</th>
                    <th>Item Name</th>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $purchase)
                    @if ($purchase->details->count() > 0)
                        @foreach ($purchase->details as $detail)
                            @php
                                $qty = (float) ($detail->receivedQty ?: $detail->qty);
                                $price = (float) ($detail->price ?? 0);
                                $subtotal = $price * $qty;

                                $detailTotalQty += $qty;
                                $detailTotalAmount += $subtotal;
                            @endphp
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>{{ $purchase->code }}</td>
                                <td>
                                    {{ $purchase->date ? \Carbon\Carbon::parse($purchase->date)->format('d-m-Y') : '-' }}
                                    {{ $purchase->time ? \Carbon\Carbon::parse($purchase->time)->format('H:i') : '' }}
                                </td>
                                <td>
                                    {{ $purchase->dueDate ? \Carbon\Carbon::parse($purchase->dueDate)->format('d-m-Y') : '-' }}
                                </td>
                                <td class="text-left">{{ $purchase->warehouse?->name ?? '-' }}</td>
                                <td>{{ $detail->itemCode }}</td>
                                <td class="text-left">{{ $detail->item?->name ?? '-' }}</td>
                                <td class="text-left">{{ $detail->description ?? '-' }}</td>
                                <td>{{ number_format($qty, 1, ',', '.') }}</td>
                                <td class="text-right">Rp {{ number_format($price, 0, ',', '.') }}</td>
                                <td class="text-right">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td>{{ $purchase->code }}</td>
                            <td>
                                {{ $purchase->date ? \Carbon\Carbon::parse($purchase->date)->format('d-m-Y') : '-' }}
                                {{ $purchase->time ? \Carbon\Carbon::parse($purchase->time)->format('H:i') : '' }}
                            </td>
                            <td>
                                {{ $purchase->dueDate ? \Carbon\Carbon::parse($purchase->dueDate)->format('d-m-Y') : '-' }}
                            </td>
                            <td class="text-left">{{ $purchase->warehouse?->name ?? '-' }}</td>
                            <td>-</td>
                            <td class="text-left">-</td>
                            <td class="text-left">-</td>
                            <td>0,0</td>
                            <td class="text-right">Rp 0</td>
                            <td class="text-right">Rp 0</td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="11">No data found</td>
                    </tr>
                @endforelse

                <tr class="bold">
                    <td colspan="8" class="text-right">TOTAL ITEM USAGE</td>
                    <td>{{ number_format($detailTotalQty, 1, ',', '.') }}</td>
                    <td></td>
                    <td class="text-right">Rp {{ number_format($detailTotalAmount, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>
