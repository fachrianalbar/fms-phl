<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Keseluruhan</title>
    <style>
        @page {
            margin: 6px;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fff;
        }

        h1 {
            text-align: center;
            font-size: 14px;
            margin: 4px 0 6px;
            text-transform: uppercase;
        }

        .info {
            font-size: 8.5px;
            margin-bottom: 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th,
        table td {
            border: 1px solid #000;
            padding: 2px 3px;
            font-size: 6.2px;
            vertical-align: top;
        }

        table th {
            font-weight: bold;
            text-align: center;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
    </style>
</head>

<body>
    @php
        $startLabel = $startDate ? \Carbon\Carbon::parse($startDate)->format('d-m-Y') : '-';
        $endLabel = $endDate ? \Carbon\Carbon::parse($endDate)->format('d-m-Y') : '-';
        $totFactoryTotal = 0;
        $totCommission = 0;
        $totVendorTotal = 0;
        $totProfit = 0;
    @endphp

    <h1>LAPORAN KESELURUHAN</h1>
    <div class="info">
        <strong>Periode Muat:</strong> {{ $startLabel }} s/d {{ $endLabel }} |
        <strong>Pabrik:</strong> {{ $customerName ?? 'Semua' }} |
        <strong>Pemilik Kendaraan:</strong> {{ $fleetCompanyName ?? 'Semua' }} |
        <strong>Total Order:</strong> {{ number_format($rows->count(), 0, ',', '.') }}
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="background: #e2e8f0;">No</th>
                <th colspan="8" style="background: #e0e7ff;">Info Order</th>
                <th colspan="10" style="background: #d1fae5;">Pabrik</th>
                <th colspan="4" style="background: #fef3c7;">Komisi</th>
                <th colspan="10" style="background: #ffedd5;">Customer (Nota Pembayaran Vendor)</th>
                <th colspan="2" style="background: #f1f5f9;">Ringkasan</th>
            </tr>
            <tr>
                <th>Tgl Muat</th>
                <th>No. Kend</th>
                <th>Pabrik</th>
                <th>Barang</th>
                <th>Dari</th>
                <th>Tujuan</th>
                <th>Pemilik</th>
                <th>Ekspedisi</th>

                <th>Ton</th>
                <th>Ongkos</th>
                <th>Jumlah</th>
                <th>Pajak</th>
                <th>Klaim</th>
                <th>Total</th>
                <th>No. Inv</th>
                <th>DP</th>
                <th>Tgl Byr</th>
                <th>Status</th>

                <th>Nama</th>
                <th>Komisi</th>
                <th>Tgl Byr</th>
                <th>Status</th>

                <th>Ton</th>
                <th>Ongkos</th>
                <th>Jumlah</th>
                <th>Pajak</th>
                <th>Total</th>
                <th>Tgl SJ</th>
                <th>No. Nota</th>
                <th>DP</th>
                <th>Tgl Byr</th>
                <th>Status</th>

                <th>Untung</th>
                <th>Ket</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                @php
                    $totFactoryTotal += $row['factory_total'];
                    $totCommission += $row['commission_amount'];
                    $totVendorTotal += $row['vendor_total'];
                    $totProfit += $row['profit'];
                @endphp
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="text-center">{{ $row['order_date'] }}</td>
                    <td class="text-center">{{ $row['plate_number'] }}</td>
                    <td>{{ $row['customer_name'] }}</td>
                    <td>{{ $row['material_name'] }}</td>
                    <td>{{ $row['origin'] }}</td>
                    <td>{{ $row['destination'] }}</td>
                    <td>{{ $row['fleet_owner'] }}</td>
                    <td>{{ $row['expedition_name'] }}</td>

                    <td class="text-right">{{ number_format($row['factory_tonase'], 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($row['factory_oa'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($row['factory_amount'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($row['factory_tax'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($row['factory_claim'], 0, ',', '.') }}</td>
                    <td class="text-right bold">{{ number_format($row['factory_total'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ $row['factory_invoice_no'] }}</td>
                    <td class="text-right">{{ number_format($row['factory_dp'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ $row['factory_payment_date'] }}</td>
                    <td class="text-center">{{ $row['factory_status'] }}</td>

                    <td>{{ $row['commission_name'] }}</td>
                    <td class="text-right">{{ number_format($row['commission_amount'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ $row['commission_payment_date'] }}</td>
                    <td class="text-center">{{ $row['commission_status'] }}</td>

                    <td class="text-right">{{ number_format($row['vendor_tonase'], 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($row['vendor_oa'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($row['vendor_amount'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($row['vendor_tax'], 0, ',', '.') }}</td>
                    <td class="text-right bold">{{ number_format($row['vendor_total'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ $row['vendor_return_date'] }}</td>
                    <td class="text-center">{{ $row['vendor_nota_no'] }}</td>
                    <td class="text-right">{{ number_format($row['vendor_dp'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ $row['vendor_payment_date'] }}</td>
                    <td class="text-center">{{ $row['vendor_status'] }}</td>

                    <td class="text-right bold">{{ number_format($row['profit'], 0, ',', '.') }}</td>
                    <td>{{ $row['notes'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="35" class="text-center">Tidak ada data</td>
                </tr>
            @endforelse
            <tr class="bold">
                <td colspan="14" class="text-right">GRAND TOTAL</td>
                <td class="text-right">{{ number_format($totFactoryTotal, 0, ',', '.') }}</td>
                <td colspan="5"></td>
                <td class="text-right">{{ number_format($totCommission, 0, ',', '.') }}</td>
                <td colspan="6"></td>
                <td class="text-right">{{ number_format($totVendorTotal, 0, ',', '.') }}</td>
                <td colspan="5"></td>
                <td class="text-right">{{ number_format($totProfit, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
</body>

</html>
