<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Asia Sakti Wahid Foods Manufacture</title>
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

        .data-table,
        .data-table td,
        .data-table th {
            border: 1px solid black;
            text-align: center;
            padding: 5px;
        }

        .data-table th {
            font-weight: bold;
        }

        .footer-sign {
            margin-top: 60px;
            text-align: right;
        }

        .signature-name {
            font-weight: bold;
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <htmlpageheader name="page-header">
        @include('finance.invoice.pdf.header.wt')
    </htmlpageheader>

    <!-- Info invoice + rekening -->
    <table style="margin-top: 20px;">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <table>
                    <tr>
                        <td>NO. INVOICE : HW-3/ASW/3-24</td>
                    </tr>
                    <tr>
                        <td>Kepada YTH :</td>
                    </tr>
                    <tr>
                        <td><strong>PT ASIA SAKTI WAHID FOODS MANUFACTURE</strong></td>
                    </tr>
                    <tr>
                        <td>Di MEDAN</td>
                    </tr>
                </table>
            </td>
            <td style="width: 50%; vertical-align: top;">
                <div style="text-align: right;">
                    <p>BANDAR LAMPUNG, 26 Januari 2024</p>
                    <table style="display: inline-block; text-align: left;">
                        <tr>
                            <td colspan="2" style="font-weight: bold;">Mohon di bayarkan ke Rekening BCA</td>
                        </tr>
                        <tr>
                            <td style="width: 35%;">A/N</td>
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
                </div>
            </td>
        </tr>
    </table>

    <!-- Tabel data -->
    <table class="data-table" style="margin-top: 20px;">
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
                <td>16/01/24</td>
                <td>B 9084 KXR</td>
                <td>Surabaya</td>
                <td>Depok</td>
                <td>Biskuit Hatari</td>
                <td></td>
                <td></td>
                <td>-</td>
            </tr>
            <tr>
                <td>17/01/24</td>
                <td>B 9472 UXS</td>
                <td>Surabaya</td>
                <td>B Lampung (BAP)</td>
                <td>Biskuit Hatari</td>
                <td></td>
                <td></td>
                <td>-</td>
            </tr>
            <tr>
                <td>09/01/24</td>
                <td>B 9480 CXR</td>
                <td>Surabaya</td>
                <td>Karawang</td>
                <td>Biskuit Hatari</td>
                <td></td>
                <td></td>
                <td>-</td>
            </tr>
            <tr>
                <td>20/01/24</td>
                <td>B 9581 SXR</td>
                <td>Surabaya</td>
                <td>Pulo Gadung</td>
                <td>Biskuit Hatari</td>
                <td></td>
                <td></td>
                <td>-</td>
            </tr>
            <tr>
                <td colspan="7" style="text-align: right;"><strong>TOTAL :</strong></td>
                <td><strong>0</strong></td>
            </tr>
        </tbody>
    </table>

    <!-- Catatan dan tanda tangan -->
    <p style="margin-top: 20px;">Berikut kami lampirkan tanda terima dari tagihan di atas.</p>

    <div class="footer-sign">
        <p>HORMAT KAMI</p>
        <p class="signature-name" style="margin-top: 60px;">Hendri Wijaya</p>
    </div>

</body>

</html>
