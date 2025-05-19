<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <style>
        @page {
            header: page-header;
        }

        body {
            font-family: Calibri, sans-serif;
            font-size: 10pt;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        .info-table td {
            padding: 2px 4px;
            vertical-align: top;
        }

        .rekening-info td {
            font-size: 9pt;
            padding: 2px 4px;
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
            margin-top: 40px;
            text-align: right;
        }

        .signature {
            margin-top: 60px;
            text-align: right;
        }

        .signature-name {
            text-decoration: underline;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <htmlpageheader name="page-header">
        @include('finance.invoice.pdf.header.wt')
    </htmlpageheader>

    <!-- Informasi invoice + rekening (gabungan) -->
    <table style="width: 100%; margin-top: 20px; font-size: 10pt;">
        <tr>
            <td style="width: 45%; vertical-align: top;">
                <table style="width: 100%;">
                    <tr>
                        <td>NO. INVOICE : 1-24</td>
                    </tr>
                    <tr>
                        <td>Kepada YTH :</td>
                    </tr>
                    <tr>
                        <td><strong>PT. ASIA MAKMUR</strong></td>
                    </tr>
                </table>
            </td>
            <td style="width: 55%; vertical-align: top;">
                <div style="text-align: right;">
                    <p>B LAMPUNG, 23 Januari 2024</p>
                    <table style="font-size: 10pt; display: inline-block; text-align: left;">
                        <tr>
                            <td colspan="2" style="font-weight: bold;">Mohon dibayarkan ke Rekening BCA</td>
                        </tr>
                        <tr>
                            <td style="width: 35%;">A/N</td>
                            <td>: PT WIJAYA TRANS MAKMUR SEJAHTERA</td>
                        </tr>
                        <tr>
                            <td>NO. REKENING</td>
                            <td>: 0-209918899</td>
                        </tr>
                        <tr>
                            <td>CABANG</td>
                            <td>: BUMI WARAS</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>


    <!-- Tabel data utama -->
    <table class="data-table" style="margin-top: 20px;">
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
                <td>09/01/24</td>
                <td>B 9001 UEV</td>
                <td>B Lampung</td>
                <td>Surabaya</td>
                <td>Kopi</td>
                <td>40,204.5</td>
                <td>0</td>
                <td>0</td>
            </tr>
            <tr>
                <td>10/01/24</td>
                <td>BE 8969 BM</td>
                <td>B Lampung</td>
                <td>Surabaya</td>
                <td>Kopi</td>
                <td>20,223.5</td>
                <td>0</td>
                <td>0</td>
            </tr>
            <tr>
                <td>10/01/24</td>
                <td>D 9751 AD</td>
                <td>B Lampung</td>
                <td>Surabaya</td>
                <td>Kopi</td>
                <td>20,241.0</td>
                <td>0</td>
                <td>0</td>
            </tr>
            <tr>
                <td colspan="7" style="text-align: right;"><strong>TOTAL :</strong></td>
                <td><strong>0</strong></td>
            </tr>
        </tbody>
    </table>



    <!-- TTD -->
    <div class="footer-sign">
        <p>HORMAT KAMI</p>
    </div>

    <div class="signature">
        <p class="signature-name">HENDRI WIJAYA</p>
    </div>

</body>

</html>
