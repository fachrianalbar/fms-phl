<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Invoice - PT MATAHARI SAKTI</title>
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
        .bordered td,
        .bordered th {
            border: 1px solid black;
        }

        .bordered th {
            text-align: center;
            font-weight: bold;
            padding: 4px;
        }

        .bordered td {
            text-align: center;
            padding: 4px;
        }

        .left {
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

        .mt-40 {
            margin-top: 40px;
        }

        .mt-60 {
            margin-top: 60px;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .inline-table {
            display: inline-block;
            text-align: left;
        }
    </style>
</head>

<body>

    <htmlpageheader name="page-header">
        @include('finance.invoice.pdf.header.wt')
    </htmlpageheader>

    <!-- Header Info -->
    <table style="margin-top: 20px;">
        <tr>
            <td class="left" style="width: 60%;">
                <strong>No. Tagihan</strong> : 15-25
            </td>
            <td class="text-right" style="width: 40%;">
                B Lampung, 23 April 2025
            </td>
        </tr>
    </table>

    <p class="bold">Tagihan Expedisi Ke : PT MATAHARI SAKTI</p>

    <!-- Table Data -->
    <table class="bordered" style="margin-top: 10px;">
        <thead>
            <tr>
                <th>No.</th>
                <th>Tanggal</th>
                <th>No. SJ</th>
                <th>No. Kendaraan</th>
                <th>Dari</th>
                <th>Tujuan</th>
                <th>Total KG</th>
                <th>Harga/KG (Rp)</th>
                <th>Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>17/04/25</td>
                <td>TF-2504-001032</td>
                <td>B 9649 TXR</td>
                <td>Margomulyo</td>
                <td>B Lampung</td>
                <td>1,000.00</td>
                <td>0.00</td>
                <td>0.00</td>
            </tr>
            <tr>
                <td>2</td>
                <td>17/04/25</td>
                <td>TF-2504-001033</td>
                <td>B 9649 TXR</td>
                <td>Margomulyo</td>
                <td>B Lampung</td>
                <td>4,200.00</td>
                <td>0.00</td>
                <td>0.00</td>
            </tr>
            <tr>
                <td>3</td>
                <td>17/04/25</td>
                <td>TF-2504-001034</td>
                <td>B 9649 TXR</td>
                <td>Margomulyo</td>
                <td>B Lampung</td>
                <td>5,000.00</td>
                <td>0.00</td>
                <td>0.00</td>
            </tr>
            <tr>
                <td>4</td>
                <td>17/04/25</td>
                <td>TF-2504-001035</td>
                <td>B 9649 TXR</td>
                <td>Margomulyo</td>
                <td>B Lampung</td>
                <td>9,200.00</td>
                <td>0.00</td>
                <td>0.00</td>
            </tr>
            <tr>
                <td colspan="6" class="text-left bold">Pembulatan Tonase</td>
                <td>600.00</td>
                <td></td>
                <td>0.00</td>
            </tr>
            <tr>
                <td colspan="8" class="text-right bold">TOTAL</td>
                <td class="bold">0.00</td>
            </tr>
        </tbody>
    </table>

    <!-- Tagihan -->
    <table style="margin-top: 20px;">
        <tr>
            <td style="width: 15%;">TAGIHAN</td>
            <td>: Rp 8,100,000.00,-</td>
        </tr>
        <tr>
            <td>TERBILANG</td>
            <td>: Delapan Juta Seratus Ribu Rupiah</td>
        </tr>
    </table>

    <!-- Tanda Tangan -->
    <div class="mt-60">
        <p>Hormat Kami,</p>
        <br><br><br>
        <p class="underline bold">HENDRI WIJAYA</p>
    </div>

    <!-- Info Transfer -->
    <p class="mt-20 bold">MOHON TRANSFER KE REKENING :</p>
    <table style="margin-top: 5px;">
        <tr>
            <td style="width: 20%;">NO REKENING</td>
            <td>: 0209918899</td>
        </tr>
        <tr>
            <td>NAMA</td>
            <td>: PT. Wijaya Trans Makmur Sejahtera</td>
        </tr>
        <tr>
            <td>BANK</td>
            <td>: BCA</td>
        </tr>
    </table>

</body>

</html>
