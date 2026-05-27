<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Maintenance Item</title>
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
            font-size: 18px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .info {
            margin-bottom: 15px;
            font-size: 12px;
        }

        .info p {
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
            padding: 6px;
            font-size: 9px;
            word-wrap: break-word;
        }

        table th {
            font-weight: bold;
            background-color: #f2f2f2;
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
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
    @endphp

    <div class="container">
        <h1>REPORT MAINTENANCE ITEM</h1>

        <div class="info">
            <p><strong>Periode Tanggal:</strong> {{ $startLabel }} s/d {{ $endLabel }}</p>
            <p><strong>Gudang (Warehouse):</strong> {{ $warehouseName ?? 'Semua Gudang' }}</p>
            <p><strong>Kendaraan (Fleet):</strong> {{ $fleetName ?? 'Semua Kendaraan' }}</p>
            <p><strong>Item:</strong> {{ $itemName ?? 'Semua Item' }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>No Maintenance</th>
                    <th>Warehouse</th>
                    <th>Kendaraan</th>
                    <th>Item</th>
                    <th>Description Item</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Total Harga</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
