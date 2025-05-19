<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Central Pertiwi Bahari</title>
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

        .bordered,
        .bordered th,
        .bordered td {
            border: 1px solid black;
        }

        .bordered th {
            font-weight: bold;
            text-align: center;
            padding: 5px;
        }

        .bordered td {
            text-align: center;
            padding: 5px;
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

    <!-- Info Invoice -->
    <table style="margin-top: 20px;">
        <tr>
            <td style="width: 60%;">NO. INVOICE : 31-24</td>
            <td class="text-right">BANDAR LAMPUNG, 22 Februari 2024</td>
        </tr>
        <tr>
            <td colspan="2">Kepada YTH :</td>
        </tr>
        <tr>
            <td colspan="2"><strong>PT. CENTRAL PERTIWI BAHARI</strong></td>
        </tr>
    </table>

    <!-- Info Rekening -->
    <table style="margin-top: 10px;">
        <tr>
            <td colspan="2"><strong>Mohon di bayarkan ke Rekening BCA</strong></td>
        </tr>
        <tr>
            <td style="width: 20%;">A/N</td>
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

    <!-- Tabel Data -->
    <table class="bordered" style="margin-top: 20px;">
        <thead>
            <tr>
                <th>TGL</th>
                <th>NO. KENDARAAN</th>
                <th>DARI</th>
                <th>TUJUAN</th>
                <th>JENIS MUATAN</th>
                <th>TONASE (KG)</th>
                <th>ONGKOS/KG</th>
                <th>JUMLAH</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>16-Feb-24</td>
                <td>BE 8149 ALB</td>
                <td>TJ. BINTANG</td>
                <td>CIKAMPEK</td>
                <td>Pakan UDANG</td>
                <td>16,500</td>
                <td>0</td>
                <td>0</td>
            </tr>
            <tr>
                <td>12-Feb-24</td>
                <td>BE 8349 AMB</td>
                <td>TJ. BINTANG</td>
                <td>TJ PRIOK</td>
                <td>Pakan UDANG</td>
                <td>20,000</td>
                <td>0</td>
                <td>0</td>
            </tr>
            <tr>
                <td colspan="7" class="text-right bold">TOTAL:</td>
                <td class="bold">0</td>
            </tr>
        </tbody>
    </table>

    <!-- TTD -->
    <div class="mt-60 text-right">
        <p>HORMAT KAMI</p>
        <br><br><br>
        <p class="underline bold">HENDRI WIJAYA</p>
    </div>

</body>

</html>
