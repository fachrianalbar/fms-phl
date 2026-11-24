<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Laporan Laba Rugi</title>
</head>

<body>
    <table style="width: 100%; border-collapse: collapse; border: 1px solid black;">
        <thead>
            <!-- Header Utama -->
            <tr>
                <th colspan="3" style="font-size: 14px; font-weight: bold; text-align: center;">Data Kendaraan</th>
                <th colspan="1" style="font-size: 14px; font-weight: bold; text-align: center;">Penjualan (A)</th>
                <th colspan="2" style="font-size: 14px; font-weight: bold; text-align: center;">Biaya (B)</th>
                <th colspan="1" style="font-size: 14px; font-weight: bold; text-align: center;">Pajak (C)</th>
                <th colspan="1" style="font-size: 14px; font-weight: bold; text-align: center;">Laba Bersih (A - B - C)</th>
            </tr>
            <!-- Sub-header -->
            <tr>
                <th style="font-size: 14px; font-weight: bold; text-align: center;">No</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center;">Plat Nomor</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center;">Perusahaan Kendaraan</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center;">Penjualan Dasar</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center;">Biaya Order</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center;">Biaya Perawatan</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center;">Pajak (PPh)</th>
                <th style="font-size: 14px; font-weight: bold; text-align: center;">Laba Bersih</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($rows as $row)
                @php
                    $fleet = $row['fleet'];
                    $summary = $row['summary'];
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $loop->iteration }}</td>
                    <td style="text-align: center;">{{ $fleet->plateNumber ?? '-' }}</td>
                    <td style="text-align: left;">{{ $fleet->company?->name ?? '-' }}</td>
                    <td style="text-align: right;">{{ number_format($summary['basicSales'], 0, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($summary['orderCost'], 0, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($summary['maintenance'], 0, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($summary['tax'], 0, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($summary['netProfit'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center;">Tidak ada data laporan laba rugi.</td>
                </tr>
            @endforelse
        </tbody>

        <tfoot>
            <tr style="font-weight: bold; background-color: #d9eaf7;">
                <td colspan="3" style="text-align: right;">Total Keseluruhan</td>
                <td style="text-align: right;">{{ number_format($grandTotals['basicSales'] ?? 0, 0, ',', '.') }}</td>
                <td style="text-align: right;">{{ number_format($grandTotals['orderCost'] ?? 0, 0, ',', '.') }}</td>
                <td style="text-align: right;">{{ number_format($grandTotals['maintenance'] ?? 0, 0, ',', '.') }}</td>
                <td style="text-align: right;">{{ number_format($grandTotals['tax'] ?? 0, 0, ',', '.') }}</td>
                <td style="text-align: right;">{{ number_format($grandTotals['netProfit'] ?? 0, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</body>

</html>
