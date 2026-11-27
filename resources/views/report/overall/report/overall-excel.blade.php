<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Keseluruhan</title>
</head>

<body>
    @php
        $startLabel = $startDate ? \Carbon\Carbon::parse($startDate)->format('d-m-Y') : '-';
        $endLabel = $endDate ? \Carbon\Carbon::parse($endDate)->format('d-m-Y') : '-';
        $th = 'font-weight: bold; text-align: center; border: 1px solid #000;';
        $totFactoryAmount = 0;
        $totFactoryTax = 0;
        $totFactoryClaim = 0;
        $totFactoryTotal = 0;
        $totCommission = 0;
        $totVendorAmount = 0;
        $totVendorTax = 0;
        $totVendorTotal = 0;
        $totProfit = 0;
    @endphp

    <table style="border-collapse: collapse; width: 100%;">
        <thead>
            <tr>
                <th colspan="35" style="font-weight: bold; font-size: 16px; text-align: center; padding: 8px;">
                    LAPORAN KESELURUHAN
                </th>
            </tr>
            <tr>
                <th colspan="35" style="text-align: left; font-size: 11px; padding: 4px;">
                    Periode Muat: {{ $startLabel }} s/d {{ $endLabel }} | Pabrik: {{ $customerName ?? 'Semua' }} | Pemilik Kendaraan: {{ $fleetCompanyName ?? 'Semua' }}
                </th>
            </tr>
            <tr>
                <th rowspan="2" style="{{ $th }} background-color: #e2e8f0;">No</th>
                <th colspan="8" style="{{ $th }} background-color: #e0e7ff;">Info Order</th>
                <th colspan="10" style="{{ $th }} background-color: #d1fae5;">Pabrik</th>
                <th colspan="4" style="{{ $th }} background-color: #fef3c7;">Komisi</th>
                <th colspan="10" style="{{ $th }} background-color: #ffedd5;">Customer (Nota Pembayaran Vendor)</th>
                <th colspan="2" style="{{ $th }} background-color: #f1f5f9;">Ringkasan</th>
            </tr>
            <tr>
                <th style="{{ $th }} background-color: #eef2ff;">Tanggal Muat</th>
                <th style="{{ $th }} background-color: #eef2ff;">No. Kendaraan</th>
                <th style="{{ $th }} background-color: #eef2ff;">Pabrik</th>
                <th style="{{ $th }} background-color: #eef2ff;">Jenis Barang</th>
                <th style="{{ $th }} background-color: #eef2ff;">Dari</th>
                <th style="{{ $th }} background-color: #eef2ff;">Tujuan</th>
                <th style="{{ $th }} background-color: #eef2ff;">Pemilik Kendaraan</th>
                <th style="{{ $th }} background-color: #eef2ff;">Nama Ekspedisi di Pabrik</th>

                <th style="{{ $th }} background-color: #ecfdf5;">Tonase</th>
                <th style="{{ $th }} background-color: #ecfdf5;">Ongkos</th>
                <th style="{{ $th }} background-color: #ecfdf5;">Jumlah</th>
                <th style="{{ $th }} background-color: #ecfdf5;">Pajak</th>
                <th style="{{ $th }} background-color: #ecfdf5;">Klaim</th>
                <th style="{{ $th }} background-color: #ecfdf5;">Total Jumlah</th>
                <th style="{{ $th }} background-color: #ecfdf5;">No. Invoice</th>
                <th style="{{ $th }} background-color: #ecfdf5;">DP</th>
                <th style="{{ $th }} background-color: #ecfdf5;">Tgl Bayar</th>
                <th style="{{ $th }} background-color: #ecfdf5;">Lunas/Belum</th>

                <th style="{{ $th }} background-color: #fefce8;">Nama</th>
                <th style="{{ $th }} background-color: #fefce8;">Komisi</th>
                <th style="{{ $th }} background-color: #fefce8;">Tgl Bayar</th>
                <th style="{{ $th }} background-color: #fefce8;">Lunas/Belum</th>

                <th style="{{ $th }} background-color: #fff7ed;">Tonase Barang</th>
                <th style="{{ $th }} background-color: #fff7ed;">Ongkos Barang</th>
                <th style="{{ $th }} background-color: #fff7ed;">Jumlah Harga</th>
                <th style="{{ $th }} background-color: #fff7ed;">Pajak</th>
                <th style="{{ $th }} background-color: #fff7ed;">Total Jumlah</th>
                <th style="{{ $th }} background-color: #fff7ed;">Tgl Terima SJ</th>
                <th style="{{ $th }} background-color: #fff7ed;">No. Nota Pembayaran</th>
                <th style="{{ $th }} background-color: #fff7ed;">DP</th>
                <th style="{{ $th }} background-color: #fff7ed;">Tgl Bayar</th>
                <th style="{{ $th }} background-color: #fff7ed;">Lunas/Belum</th>

                <th style="{{ $th }} background-color: #f8fafc;">Untung</th>
                <th style="{{ $th }} background-color: #f8fafc;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                @php
                    $totFactoryAmount += $row['factory_amount'];
                    $totFactoryTax += $row['factory_tax'];
                    $totFactoryClaim += $row['factory_claim'];
                    $totFactoryTotal += $row['factory_total'];
                    $totCommission += $row['commission_amount'];
                    $totVendorAmount += $row['vendor_amount'];
                    $totVendorTax += $row['vendor_tax'];
                    $totVendorTotal += $row['vendor_total'];
                    $totProfit += $row['profit'];
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $loop->iteration }}</td>
                    <td style="text-align: center;">{{ $row['order_date'] }}</td>
                    <td style="text-align: center;">{{ $row['plate_number'] }}</td>
                    <td>{{ $row['customer_name'] }}</td>
                    <td>{{ $row['material_name'] }}</td>
                    <td>{{ $row['origin'] }}</td>
                    <td>{{ $row['destination'] }}</td>
                    <td>{{ $row['fleet_owner'] }}</td>
                    <td>{{ $row['expedition_name'] }}</td>

                    <td style="text-align: right;">{{ number_format($row['factory_tonase'], 2, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($row['factory_oa'], 0, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($row['factory_amount'], 0, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($row['factory_tax'], 0, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($row['factory_claim'], 0, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($row['factory_total'], 0, ',', '.') }}</td>
                    <td style="text-align: center;">{{ $row['factory_invoice_no'] }}</td>
                    <td style="text-align: right;">{{ number_format($row['factory_dp'], 0, ',', '.') }}</td>
                    <td style="text-align: center;">{{ $row['factory_payment_date'] }}</td>
                    <td style="text-align: center;">{{ $row['factory_status'] }}</td>

                    <td>{{ $row['commission_name'] }}</td>
                    <td style="text-align: right;">{{ number_format($row['commission_amount'], 0, ',', '.') }}</td>
                    <td style="text-align: center;">{{ $row['commission_payment_date'] }}</td>
                    <td style="text-align: center;">{{ $row['commission_status'] }}</td>

                    <td style="text-align: right;">{{ number_format($row['vendor_tonase'], 2, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($row['vendor_oa'], 0, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($row['vendor_amount'], 0, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($row['vendor_tax'], 0, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($row['vendor_total'], 0, ',', '.') }}</td>
                    <td style="text-align: center;">{{ $row['vendor_return_date'] }}</td>
                    <td style="text-align: center;">{{ $row['vendor_nota_no'] }}</td>
                    <td style="text-align: right;">{{ number_format($row['vendor_dp'], 0, ',', '.') }}</td>
                    <td style="text-align: center;">{{ $row['vendor_payment_date'] }}</td>
                    <td style="text-align: center;">{{ $row['vendor_status'] }}</td>

                    <td style="text-align: right; font-weight: bold;">{{ number_format($row['profit'], 0, ',', '.') }}</td>
                    <td>{{ $row['notes'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="35" style="text-align: center;">Tidak ada data</td>
                </tr>
            @endforelse
            <tr>
                <td colspan="11" style="text-align: right; font-weight: bold;">GRAND TOTAL</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($totFactoryAmount, 0, ',', '.') }}</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($totFactoryTax, 0, ',', '.') }}</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($totFactoryClaim, 0, ',', '.') }}</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($totFactoryTotal, 0, ',', '.') }}</td>
                <td colspan="5"></td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($totCommission, 0, ',', '.') }}</td>
                <td colspan="4"></td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($totVendorAmount, 0, ',', '.') }}</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($totVendorTax, 0, ',', '.') }}</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($totVendorTotal, 0, ',', '.') }}</td>
                <td colspan="5"></td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($totProfit, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
</body>

</html>
