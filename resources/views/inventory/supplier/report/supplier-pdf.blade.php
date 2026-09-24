<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Supplier</title>
    <style>
        @page {
            margin: 15px
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fff;
        }

        .container {
            width: 100%;
            margin: 0 auto;
            padding: 10px;
        }

        h1 {
            text-align: center;
            font-size: 22px;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .report-date {
            text-align: center;
            font-size: 11px;
            color: #666;
            margin-bottom: 6px;
        }

        .report-filter {
            text-align: center;
            font-size: 11px;
            color: #333;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table th,
        table td {
            border: 1px solid #333;
            padding: 8px;
            font-size: 11px;
        }

        table th {
            font-weight: bold;
            background-color: #e0e0e0;
            text-align: center;
        }

        table td {
            text-align: center;
        }

        .text-left {
            text-align: left !important;
        }

        .text-right {
            text-align: right !important;
        }

        .no-data {
            text-align: center;
            padding: 15px;
            color: #999;
        }

        .footer {
            margin-top: 40px;
            font-size: 10px;
            color: #666;
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>LAPORAN SUPPLIER</h1>
        <p class="report-date">Tanggal Cetak: {{ date('d/m/Y H:i') }}</p>
        <p class="report-filter">
            Supplier: {{ $supplierName ?? 'Semua' }}
            | Periode: {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d/m/Y') : 'Awal' }}
            s/d {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d/m/Y') : 'Sekarang' }}
        </p>

        @if (count($suppliers) > 0)
            <table>
                <thead>
                    <tr>
                        <th width="4%">No</th>
                        <th width="9%">Kode</th>
                        <th width="18%">Nama</th>
                        <th width="20%">Alamat</th>
                        <th width="10%">PIC</th>
                        <th width="10%">Telepon</th>
                        <th width="13%">Email</th>
                        <th width="6%">PPN</th>
                        <th width="6%">PPH</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($suppliers as $supplier)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="text-left">{{ $supplier->code }}</td>
                            <td class="text-left">{{ $supplier->name }}</td>
                            <td class="text-left">{{ $supplier->address ?: '-' }}</td>
                            <td class="text-left">{{ $supplier->pic ?: '-' }}</td>
                            <td class="text-left">{{ $supplier->phone ?: '-' }}</td>
                            <td class="text-left">{{ $supplier->email ?: '-' }}</td>
                            <td class="text-right">{{ $supplier->ppn !== null && $supplier->ppn !== '' ? $supplier->ppn : '-' }}</td>
                            <td class="text-right">{{ $supplier->pph !== null && $supplier->pph !== '' ? $supplier->pph : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-data">Tidak ada data supplier untuk ditampilkan</div>
        @endif

        <div class="footer">
            <p>Laporan ini dicetak secara otomatis oleh sistem</p>
        </div>
    </div>
</body>

</html>
