<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Order Detail Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid black;
            padding: 4px;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }

        .title {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            padding: 10px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }
    </style>
</head>

<body>
    <div class="title">Order Detail Report</div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Shipment No</th>
                <th>Order Date</th>
                <th>Customer</th>
                <th>Origin</th>
                <th>Destination</th>
                <th>Plate Number</th>
                <th>Driver</th>
                <th>Sales</th>
                <th>Pendapatan</th>
                <th>Cost Detail</th>
                <th>Total Cost</th>
                <th>Profit</th>
            </tr>
        </thead>
        <tbody>