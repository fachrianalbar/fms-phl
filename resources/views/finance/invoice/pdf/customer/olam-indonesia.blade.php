<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Invoice - PT Olam Indonesia</title>
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
            width: 100%;
            border-collapse: collapse;
        }

        .bordered,
        .bordered td,
        .bordered th {
            border: 1px solid black;
        }

        .bordered th,
        .bordered td {
            padding: 5px;
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

        .mt-20 {
            margin-top: 20px;
        }

        .mt-60 {
            margin-top: 60px;
        }

        .underline {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <htmlpageheader name="page-header">
        @include('finance.invoice.pdf.header.phl')
    </htmlpageheader>

    <!-- Informasi Header Invoice -->
    <table style="margin-top: 20px;">
        <tr>
            <td style="width: 60%;">NO. INVOICE : 1-25</td>
            <td class="text-right">B LAMPUNG, 14 Januari 2025</td>
        </tr>
        <tr>
            <td colspan="2">Kepada YTH :</td>
        </tr>
        <tr>
            <td colspan="2"><strong>PT. OLAM INDONESIA</strong></td>
        </tr>
    </table>

    <!-- Info Pembayaran -->
    <table style="margin-top: 10px;">
        <tr>
            <td colspan="2"><strong>Mohon di bayarkan ke Rekening BCA</strong></td>
        </tr>
        <tr>
            <td style="width: 20%;">A/N</td>
            <td>: PT PUTRI HOKI LOGISTIK</td>
        </tr>
        <tr>
            <td>NO. REK</td>
            <td>: 0208888351</td>
        </tr>
        <tr>
            <td>CABANG</td>
            <td>: KCU - Bumi Waras - B. Lampung</td>
        </tr>
    </table>

    <!-- Tabel Data Utama -->
    <table class="bordered mt-20">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>No Kendaraan</th>
                <th>Gudang Muat</th>
                <th>Gudang Bongkar</th>
                <th>Nama Barang</th>
                <th>Tonase</th>
                <th>Tarif/kgs</th>
                <th>Ongkos Angkut</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>07/01/25</td>
                <td>D 9750 AD</td>
                <td>B Lampung</td>
                <td>Surabaya</td>
                <td>Kopi</td>
                <td>19,981.0</td>
                <td>-</td>
                <td>-</td>
            </tr>
            <tr>
                <td>2</td>
                <td>07/01/25</td>
                <td>B 9656 UXS</td>
                <td>B Lampung</td>
                <td>Surabaya</td>
                <td>Kopi</td>
                <td>20,096.0</td>
                <td>-</td>
                <td>-</td>
            </tr>
            <tr>
                <td colspan="8" class="text-right bold">TOTAL :</td>
                <td>-</td>
            </tr>
        </tbody>
    </table>

    <!-- Tanda Tangan -->
    <div class="mt-60 text-right">
        <p>HORMAT KAMI</p>
        <br><br><br>
        <p class="underline bold">EVI IRAWATI</p>
    </div>

</body>

</html>
