<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Danitama Niaga Prima</title>
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
            border: 1px solid black !important;
        }

        .bordered th {
            padding: 5px;
            text-align: center;
            font-weight: bold;
        }

        .bordered td {
            padding: 5px;
            text-align: center;
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

    <!-- Header Informasi -->
    <table style="margin-top: 20px;">
        <tr>
            <td style="width: 60%;">NO. INVOICE : 1-25</td>
            <td class="text-right">BANDAR LAMPUNG, 21 Januari 2025</td>
        </tr>
        <tr>
            <td colspan="2">Kepada YTH :</td>
        </tr>
        <tr>
            <td colspan="2"><strong>PT Danitama Niaga Prima</strong></td>
        </tr>
        <tr>
            <td colspan="2">Di Tempat</td>
        </tr>
    </table>

    <!-- Info Rekening -->
    <table style="margin-top: 10px;">
        <tr>
            <td colspan="2"><strong>Mohon dibayarkan ke Rek BCA :</strong></td>
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
            <td>: BUMI WARAS - B LAMPUNG</td>
        </tr>
    </table>

    <!-- Pernyataan -->
    <p class="mt-20">
        Untuk Pembayaran : Ongkos Angkut Tepung Dari Semarang ke PT. Surya Tsabat Mandiri
    </p>

    <!-- Tabel utama -->
    <table class="bordered" style="margin-top: 10px;">
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
            <!-- Entry 1 -->
            <tr>
                <td>18/01/25</td>
                <td>BE 8204 ALB</td>
                <td>Semarang</td>
                <td>PT Surya Tsabat</td>
                <td>800 Bag Terigu</td>
                <td>20,000</td>
                <td></td>
                <td>0</td>
            </tr>
            <tr>
                <td colspan="8" class="text-left">Mandiri - Metro, Tali Emas, Lampung @25 kg</td>
            </tr>
            <tr>
                <td colspan="8" class="text-left">No SO.Induk : 25,000555</td>
            </tr>
            <tr>
                <td colspan="8" class="text-left">No. PO : 0146/STM/I/25 (2501,0119)</td>
            </tr>
            <tr>
                <td colspan="8" class="text-left">No. Sub SO : SS2501,000022</td>
            </tr>

            <!-- Entry 2 -->
            <tr>
                <td>18/01/25</td>
                <td>BE 8204 ALB</td>
                <td>Semarang</td>
                <td>PT Surya Tsabat</td>
                <td>16 Bag Terigu</td>
                <td>400</td>
                <td></td>
                <td>0</td>
            </tr>
            <tr>
                <td colspan="8" class="text-left">Mandiri - Metro, Tali Emas, Lampung @25 kg</td>
            </tr>
            <tr>
                <td colspan="8" class="text-left">No SO.Induk : 25,000555</td>
            </tr>
            <tr>
                <td colspan="8" class="text-left">No. PO : 0146/STM/I/25 (2501,0119)</td>
            </tr>
            <tr>
                <td colspan="8" class="text-left">No. Sub SO : SS2501,000022</td>
            </tr>

            <!-- Total -->
            <tr>
                <td colspan="7" class="text-right bold">TOTAL :</td>
                <td class="bold">0</td>
            </tr>
        </tbody>
    </table>

    <!-- Catatan -->
    <p class="mt-20">
        Berikut kami lampirkan tanda terima dari tagihan di atas.
    </p>

    <!-- Tanda Tangan -->
    <div class="mt-60 text-right">
        <p>HORMAT KAMI</p>
        <br><br><br>
        <p class="underline bold">Hendri Wijaya</p>
    </div>

</body>

</html>
