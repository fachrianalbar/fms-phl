<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Ekspedisi Berdikari</title>
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
        @include('finance.invoice.pdf.header.phl')
    </htmlpageheader>

    <!-- Info Invoice -->
    <table style="margin-top: 20px;">
        <tr>
            <td style="width: 60%;">NO. INVOICE : 1-25</td>
            <td class="text-right">B LAMPUNG, 13 Januari 2025</td>
        </tr>
        <tr>
            <td colspan="2">Kepada YTH :</td>
        </tr>
        <tr>
            <td colspan="2"><strong>Ekspedisi Berdikari</strong></td>
        </tr>
    </table>

    <!-- Rekening -->
    <table style="margin-top: 10px;">
        <tr>
            <td colspan="2"><strong>Mohon di bayarkan ke Rekening BCA</strong></td>
        </tr>
        <tr>
            <td style="width: 20%;">A/N</td>
            <td>: HENDRI WIJAYA</td>
        </tr>
        <tr>
            <td>NO. REK</td>
            <td>: 0-200514866</td>
        </tr>
        <tr>
            <td>CABANG</td>
            <td>: BUMI WARAS - B Lampung</td>
        </tr>
    </table>

    <!-- Tabel Utama -->
    <table class="bordered" style="margin-top: 20px;">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>No Kendaraan</th>
                <th>Gudang Muat</th>
                <th>Gudang Bongkar</th>
                <th>Nama Barang</th>
                <th>Kubikasi</th>
                <th>Tarif/m3</th>
                <th>Ongkos Angkut</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>02/01/25</td>
                <td>B 9702 TXR</td>
                <td>Jakarta</td>
                <td>Bengkulu</td>
                <td>Indofood</td>
                <td>54.788</td>
                <td></td>
                <td>-</td>
            </tr>
            <tr>
                <td colspan="7" class="text-left">Ongkos Bongkar</td>
                <td>688,800</td>
            </tr>

            <tr>
                <td>03/01/25</td>
                <td>BE 8252 AUC</td>
                <td>Jakarta</td>
                <td>Bengkulu</td>
                <td>Indofood</td>
                <td>56.374</td>
                <td></td>
                <td>-</td>
            </tr>
            <tr>
                <td colspan="7" class="text-left">Ongkos Bongkar</td>
                <td>767,900</td>
            </tr>

            <tr>
                <td>16/12/24</td>
                <td>BE 8684 YO</td>
                <td>Jakarta</td>
                <td>Muarabungo</td>
                <td>Tisu</td>
                <td>56.320</td>
                <td></td>
                <td>-</td>
            </tr>
            <tr>
                <td colspan="7" class="text-left">Ongkos Bongkar</td>
                <td>1,170,000</td>
            </tr>

            <tr>
                <td>03/01/25</td>
                <td>BE 8684 YO</td>
                <td>Jakarta</td>
                <td>Bengkulu</td>
                <td>Indofood</td>
                <td>55.469</td>
                <td></td>
                <td>-</td>
            </tr>
            <tr>
                <td colspan="7" class="text-left">Ongkos Bongkar</td>
                <td>672,000</td>
            </tr>

            <tr>
                <td>03/01/25</td>
                <td>B 9480 CXR</td>
                <td>Jakarta</td>
                <td>Bengkulu</td>
                <td>Indofood</td>
                <td>55.407</td>
                <td></td>
                <td>-</td>
            </tr>
            <tr>
                <td colspan="7" class="text-left">Ongkos Bongkar</td>
                <td>770,700</td>
            </tr>

            <tr>
                <td>07/01/25</td>
                <td>B 9021 WEH</td>
                <td>Jakarta</td>
                <td>Bengkulu</td>
                <td>Tisu</td>
                <td>54.266</td>
                <td></td>
                <td>-</td>
            </tr>
            <tr>
                <td colspan="7" class="text-left">Ongkos Bongkar</td>
                <td>625,800</td>
            </tr>

            <tr>
                <td colspan="7" class="text-right bold">TOTAL :</td>
                <td class="bold">4,695,200</td>
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
