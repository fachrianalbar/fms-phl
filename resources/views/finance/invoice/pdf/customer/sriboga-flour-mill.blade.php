<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Invoice PT. SRIBOGA FLOUR MILL</title>
    <style>
        @page {
            header: page-header;
        }

        body {
            font-family: Calibri, sans-serif;
            font-size: 10pt;
            line-height: 20px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        .no-border td {
            border: none;
        }

        .invoice-table,
        .invoice-table th,
        .invoice-table td {
            border: 1px solid black;
            text-align: center;
            padding: 4px;
        }

        .invoice-table th {
            /* background-color: #f2f2f2; */
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .bold {
            font-weight: bold;
        }

        .underline {
            text-decoration: underline;
        }

        .mt-20 {
            margin-top: 20px;
        }

        .mt-60 {
            margin-top: 60px;
        }
    </style>
</head>

<body>

    <htmlpageheader name="page-header">
        @include('finance.invoice.pdf.header.wt')
    </htmlpageheader>

    <table class="no-border" style="margin-top: 20px;">
        <tr>
            <td style="width: 60%;">NO. INVOICE : 2-25</td>
            <td class="text-right">B LAMPUNG, 17 Januari 2025</td>
        </tr>
        <tr>
            <td colspan="2">Kepada YTH :</td>
        </tr>
        <tr>
            <td colspan="2"><strong>PT. SRIBOGA FLOUR MILL</strong></td>
        </tr>
    </table>

    <table class="no-border" style="margin-top: 15px;">
        <tr>
            <td colspan="2"><strong>Mohon di bayarkan ke Rekening BCA</strong></td>
        </tr>
        <tr>
            <td style="width: 25%;">A/N</td>
            <td>: PT Wijaya Trans Makmur Sejahtera</td>
        </tr>
        <tr>
            <td>NO. REKENING</td>
            <td>: 0209918899</td>
        </tr>
        <tr>
            <td>CABANG</td>
            <td>: BUMI WARAS</td>
        </tr>
    </table>

    <table class="invoice-table mt-20">
        <thead>
            <tr>
                <th>TGL</th>
                <th>NO. KENDARAAN</th>
                <th>DARI</th>
                <th>TUJUAN</th>
                <th>JENIS MUATAN</th>
                <th>TONASE (KG)</th>
                <th>ONGKOS / KG</th>
                <th>JUMLAH</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>10-Jan-25</td>
                <td>B 9480 UXS</td>
                <td>SEMARANG</td>
                <td>PALEMBANG</td>
                <td>TEPUNG</td>
                <td>20,000</td>
                <td>0</td>
                <td>0</td>
            </tr>
            <tr>
                <td colspan="6" class="text-left">Ongkos Bongkar</td>
                <td></td>
                <td>480,000</td>
            </tr>
            <tr>
                <td>6-Jan-25</td>
                <td>BE 8141 AME</td>
                <td>SEMARANG</td>
                <td>LUBUK LINGGAU</td>
                <td>TEPUNG</td>
                <td>20,000</td>
                <td>0</td>
                <td>0</td>
            </tr>
            <tr>
                <td colspan="6" class="text-left">Ongkos Bongkar</td>
                <td></td>
                <td>480,000</td>
            </tr>
            <tr>
                <td>13-Jan-25</td>
                <td>BE 8149 ALS</td>
                <td>SEMARANG</td>
                <td>NATAR</td>
                <td>TEPUNG</td>
                <td>20,000</td>
                <td>0</td>
                <td>0</td>
            </tr>
            <tr>
                <td colspan="6" class="text-left">Ongkos Bongkar</td>
                <td></td>
                <td>400,000</td>
            </tr>
            <tr>
                <td colspan="7" class="text-right bold">TOTAL :</td>
                <td class="bold">1,360,000</td>
            </tr>
        </tbody>
    </table>

    <div class="mt-60 text-right">
        <p>HORMAT KAMI</p>
        <br><br><br>
        <p class="bold underline">HENDRI WIJAYA</p>
    </div>

</body>

</html>
