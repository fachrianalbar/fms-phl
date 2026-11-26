<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: sans-serif; font-size: 8.5px; color: #1f2937; }
        .header { text-align: center; margin-bottom: 10px; }
        .header h1 { font-size: 14px; margin: 0 0 3px; text-transform: uppercase; }
        .header .meta { font-size: 8px; color: #475569; }
        .filter-summary { margin-bottom: 8px; padding: 5px 8px; background: #f8fafc; border: 1px solid #cbd5e1; font-size: 8px; color: #334155; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #64748b; padding: 4.5px 4px; vertical-align: middle; }
        th { background: #e2e8f0; font-weight: bold; text-align: center; font-size: 8px; }
        tfoot td { background: #f1f5f9; font-weight: bold; }
        .center { text-align: center; }
        .right { text-align: right; }
        .nowrap { white-space: nowrap; }
    </style>
</head>
<body>
    @php
        $isPaid = ($type ?? 'unpaid') === 'paid';
        $sumOrders = 0;
        $sumCost = 0;
        $sumAdd = 0;
        $sumPpn = 0;
        $sumPph = 0;
        $sumClaim = 0;
        $sumAmount = 0;
        $sumPaid = 0;
        $sumRemaining = 0;
    @endphp

    <div class="header">
        <h1>{{ $title }}</h1>
        <div class="meta">Dicetak pada: {{ $printedAt->format('d-m-Y H:i') }} | Total Data: {{ $items->count() }} {{ $isPaid ? 'Unit' : 'Nota' }}</div>
    </div>

    @if (! empty($filterSummary))
        <div class="filter-summary">
            <strong>Filter Aktif:</strong> {{ implode(' | ', $filterSummary) }}
        </div>
    @endif

    <table>
        <thead>
            @if ($isPaid)
                <tr>
                    <th>No</th>
                    <th>Kode / No. Nota</th>
                    <th>Tgl Order</th>
                    <th>Tgl Bayar</th>
                    <th>Customer</th>
                    <th>Perusahaan</th>
                    <th>Nopol</th>
                    <th>Total Tagihan</th>
                    <th>Terbayar</th>
                    <th>Status</th>
                </tr>
            @else
                <tr>
                    <th>No</th>
                    <th>No. Nota</th>
                    <th>Tgl Nota</th>
                    <th>Customer</th>
                    <th>Perusahaan</th>
                    <th>Jml DO</th>
                    <th>Nopol</th>
                    <th>Ongkos</th>
                    <th>Biaya Tamb.</th>
                    <th>PPN</th>
                    <th>PPh</th>
                    <th>Claim</th>
                    <th>Total Tagihan</th>
                    <th>Terbayar</th>
                    <th>Sisa</th>
                    <th>Status</th>
                </tr>
            @endif
        </thead>
        <tbody>
            @forelse ($items as $row)
                @if ($isPaid)
                    @php
                        $sumAmount += (float) ($row->grand_total ?? 0);
                        $sumPaid += (float) ($row->payment ?? 0);
                        $orderDateText = ! empty($row->order_date_range)
                            ? $row->order_date_range
                            : ($row->order_date ? \Carbon\Carbon::parse($row->order_date)->format('d-m-Y') : '-');
                        $paymentDateText = $row->payment_date
                            ? \Carbon\Carbon::parse($row->payment_date)->format('d-m-Y')
                            : '-';
                        $statusLabel = ($row->status_code ?? '') === 'overpaid' ? 'Kelebihan' : 'Lunas';
                    @endphp
                    <tr>
                        <td class="center">{{ $loop->iteration }}</td>
                        <td class="nowrap">{{ $row->code ?: '-' }}</td>
                        <td class="center nowrap">{{ $orderDateText }}</td>
                        <td class="center nowrap">{{ $paymentDateText }}</td>
                        <td>{{ $row->customer_name ?: '-' }}</td>
                        <td>{{ $row->company_name ?: '-' }}</td>
                        <td>{{ $row->plate ?: '-' }}</td>
                        <td class="right nowrap">Rp {{ number_format((float) ($row->grand_total ?? 0), 0, ',', '.') }}</td>
                        <td class="right nowrap">Rp {{ number_format((float) ($row->payment ?? 0), 0, ',', '.') }}</td>
                        <td class="center">{{ $statusLabel }}</td>
                    </tr>
                @else
                    @php
                        $sumOrders += (int) ($row->order_count ?? 0);
                        $sumCost += (float) ($row->cost_amount ?? 0);
                        $sumAdd += (float) ($row->additional_cost_amount ?? 0);
                        $sumPpn += (float) ($row->ppn_amount ?? 0);
                        $sumPph += (float) ($row->pph_amount ?? 0);
                        $sumClaim += (float) ($row->claim_amount ?? 0);
                        $sumAmount += (float) ($row->amount ?? 0);
                        $sumPaid += (float) ($row->paid_amount ?? 0);
                        $sumRemaining += (float) ($row->remaining_amount ?? 0);
                        $statusLabel = match ($row->payment_status) {
                            'paid' => 'Lunas',
                            'partial' => 'Belum Lunas (DP)',
                            default => 'Belum Bayar',
                        };
                        $plates = $row->plate_numbers->implode(', ') ?: '-';
                    @endphp
                    <tr>
                        <td class="center">{{ $loop->iteration }}</td>
                        <td class="nowrap">{{ $row->nota_number ?: '-' }}</td>
                        <td class="center nowrap">{{ $row->date ? \Carbon\Carbon::parse($row->date)->format('d-m-Y') : '-' }}</td>
                        <td>{{ $row->customer_name ?: '-' }}</td>
                        <td>{{ $row->company_name ?: '-' }}</td>
                        <td class="center">{{ (int) ($row->order_count ?? 0) }}</td>
                        <td>{{ $plates }}</td>
                        <td class="right nowrap">Rp {{ number_format((float) ($row->cost_amount ?? 0), 0, ',', '.') }}</td>
                        <td class="right nowrap">Rp {{ number_format((float) ($row->additional_cost_amount ?? 0), 0, ',', '.') }}</td>
                        <td class="right nowrap">Rp {{ number_format((float) ($row->ppn_amount ?? 0), 0, ',', '.') }}</td>
                        <td class="right nowrap">Rp {{ number_format((float) ($row->pph_amount ?? 0), 0, ',', '.') }}</td>
                        <td class="right nowrap">Rp {{ number_format((float) ($row->claim_amount ?? 0), 0, ',', '.') }}</td>
                        <td class="right nowrap">Rp {{ number_format((float) ($row->amount ?? 0), 0, ',', '.') }}</td>
                        <td class="right nowrap">Rp {{ number_format((float) ($row->paid_amount ?? 0), 0, ',', '.') }}</td>
                        <td class="right nowrap">Rp {{ number_format((float) ($row->remaining_amount ?? 0), 0, ',', '.') }}</td>
                        <td class="center">{{ $statusLabel }}</td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="{{ $isPaid ? 10 : 16 }}" class="center">Tidak ada data yang sesuai filter.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($items->isNotEmpty())
            <tfoot>
                @if ($isPaid)
                    <tr>
                        <td colspan="7" class="right">TOTAL</td>
                        <td class="right nowrap">Rp {{ number_format($sumAmount, 0, ',', '.') }}</td>
                        <td class="right nowrap">Rp {{ number_format($sumPaid, 0, ',', '.') }}</td>
                        <td></td>
                    </tr>
                @else
                    <tr>
                        <td colspan="5" class="right">TOTAL</td>
                        <td class="center">{{ $sumOrders }}</td>
                        <td></td>
                        <td class="right nowrap">Rp {{ number_format($sumCost, 0, ',', '.') }}</td>
                        <td class="right nowrap">Rp {{ number_format($sumAdd, 0, ',', '.') }}</td>
                        <td class="right nowrap">Rp {{ number_format($sumPpn, 0, ',', '.') }}</td>
                        <td class="right nowrap">Rp {{ number_format($sumPph, 0, ',', '.') }}</td>
                        <td class="right nowrap">Rp {{ number_format($sumClaim, 0, ',', '.') }}</td>
                        <td class="right nowrap">Rp {{ number_format($sumAmount, 0, ',', '.') }}</td>
                        <td class="right nowrap">Rp {{ number_format($sumPaid, 0, ',', '.') }}</td>
                        <td class="right nowrap">Rp {{ number_format($sumRemaining, 0, ',', '.') }}</td>
                        <td></td>
                    </tr>
                @endif
            </tfoot>
        @endif
    </table>
</body>
</html>
