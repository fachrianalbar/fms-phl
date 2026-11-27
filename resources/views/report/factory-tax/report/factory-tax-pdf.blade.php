<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pajak Pabrik</title>
    <style>
        @page {
            margin: 8px;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fff;
        }

        .container {
            width: 100%;
            margin: 4px auto;
        }

        h1 {
            text-align: center;
            font-size: 15px;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        .info {
            margin-bottom: 6px;
            font-size: 9.5px;
        }

        .info p {
            margin: 2px 0;
            line-height: 1.3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        table th,
        table td {
            border: 1px solid #000;
            padding: 3px 4px;
            font-size: 7.5px;
            vertical-align: top;
        }

        table th {
            background-color: #f1f5f9;
            font-weight: bold;
            text-align: center;
        }

        .text-center {
            text-align: center;
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
        $statusLabel = match ($taxStatus) {
            'filled' => 'Sudah Input Pajak',
            'empty' => 'Belum Input Pajak',
            default => 'Semua',
        };
        $grandTonase = 0;
        $grandDpp = 0;
        $grandDppLain = 0;
        $grandPpn = 0;
        $grandWithholding = 0;
    @endphp

    <div class="container">
        <h1>LAPORAN PAJAK PABRIK</h1>

        <div class="info">
            <p>
                <strong>Pabrik:</strong> {{ $customerName ?? 'Semua' }} |
                <strong>Status Input Pajak:</strong> {{ $statusLabel }} |
                <strong>Periode Invoice:</strong> {{ $startLabel }} s/d {{ $endLabel }}
            </p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tgl Cetak Invoice</th>
                    <th>NPWP</th>
                    <th>Nama Pabrik</th>
                    <th>Tgl Pesanan</th>
                    <th>Jenis Muatan</th>
                    <th>Alamat</th>
                    <th>No Invoice</th>
                    <th>Tonase</th>
                    <th>OA</th>
                    <th>Nominal Invoice/DPP</th>
                    <th>DPP Lain</th>
                    <th>PPN</th>
                    <th>No Faktur Pajak</th>
                    <th>Tgl Bayar Invoice</th>
                    <th>No Bukti Potong</th>
                    <th>Nominal Bukti Potong</th>
                    <th>Tgl Bukti Potong</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    @php
                        $dpp = (float) $row->dpp;
                        $dppLain = round($dpp * 11 / 12, 2);
                        $ppn = round($dppLain * 0.12, 2);
                        $grandTonase += (float) $row->tonase;
                        $grandDpp += $dpp;
                        $grandDppLain += $dppLain;
                        $grandPpn += $ppn;
                        $grandWithholding += (float) ($row->withholding_tax_amount ?? 0);
                    @endphp
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td class="text-center">{{ $row->invoiceDate ? \Carbon\Carbon::parse($row->invoiceDate)->format('d-m-Y') : '-' }}</td>
                        <td class="text-center">{{ $row->npwp ?: '-' }}</td>
                        <td class="text-left">{{ $row->customerName ?: '-' }}</td>
                        <td class="text-center">{{ \App\Http\Controllers\Report\FactoryTaxController::formatOrderDateRange($row->minOrderDate, $row->maxOrderDate) }}</td>
                        <td class="text-left">{{ $row->materials ?: '-' }}</td>
                        <td class="text-left">{{ $row->customerAddress ?: '-' }}</td>
                        <td class="text-center">{{ $row->invoiceNumber ?: ($row->code ?: '-') }}</td>
                        <td class="text-right">{{ number_format((float) $row->tonase, 2, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format((float) $row->oa, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($dpp, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($dppLain, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($ppn, 0, ',', '.') }}</td>
                        <td class="text-center">{{ $row->tax_invoice_number ?: '-' }}</td>
                        <td class="text-center">{{ $row->lastPaymentDate ? \Carbon\Carbon::parse($row->lastPaymentDate)->format('d-m-Y') : '-' }}</td>
                        <td class="text-center">{{ $row->withholding_tax_number ?: '-' }}</td>
                        <td class="text-right">{{ $row->withholding_tax_amount !== null ? 'Rp ' . number_format((float) $row->withholding_tax_amount, 0, ',', '.') : '-' }}</td>
                        <td class="text-center">{{ $row->withholding_tax_date ? \Carbon\Carbon::parse($row->withholding_tax_date)->format('d-m-Y') : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="18" class="text-center">Tidak ada data pajak pabrik</td>
                    </tr>
                @endforelse
                <tr class="bold">
                    <td colspan="8" class="text-right">GRAND TOTAL</td>
                    <td class="text-right">{{ number_format($grandTonase, 2, ',', '.') }}</td>
                    <td></td>
                    <td class="text-right">Rp {{ number_format($grandDpp, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($grandDppLain, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($grandPpn, 0, ',', '.') }}</td>
                    <td colspan="3"></td>
                    <td class="text-right">Rp {{ number_format($grandWithholding, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>
