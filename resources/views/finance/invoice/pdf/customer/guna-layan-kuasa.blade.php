<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Invoice - PT GUNA LAYAN KUASA</title>
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

        .bordered th,
        .bordered td {
            border: 1px solid black;
            padding: 4px;
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

    <table style="margin-top: 20px;">
        <tr>
            <td style="width: 60%;">NO. INVOICE : PHL/GULAKU/5-25</td>
            <td class="text-right">B LAMPUNG, 28 Januari 2025</td>
        </tr>
        <tr>
            <td colspan="2">Kepada YTH :</td>
        </tr>
        <tr>
            <td colspan="2"><strong>PT. GUNA LAYAN KUASA</strong></td>
        </tr>
    </table>

    <table style="margin-top: 10px;">
        <tr>
            <td colspan="2"><strong>Mohon di bayarkan ke Rekening BCA</strong></td>
        </tr>
        <tr>
            <td style="width: 20%;">A/N</td>
            <td>: PT PUTRI HOKI LOGISTIK</td>
        </tr>
        <tr>
            <td>NO. REKENING</td>
            <td>: 0208888351</td>
        </tr>
        <tr>
            <td>CABANG</td>
            <td>: KCU - Bumi Waras - B. Lampung</td>
        </tr>
    </table>

    <table class="bordered mt-20">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>No Kendaraan</th>
                <th>No. SPPB</th>
                <th>Gudang Muat</th>
                <th>Nama Pembeli</th>
                <th>Tujuan</th>
                <th>Nama Barang</th>
                <th>Kg/Box</th>
                <th>Box</th>
                <th>Tonase</th>
                <th>Total Tonase</th>
                <th>Tarif/Kg</th>
                <th>Ongkos Angkut</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>16/01/25</td>
                <td>B 9702 TXR</td>
                <td>1573</td>
                <td>PT GPM</td>
                <td>SinarMas Indramayu</td>
                <td>Indramayu</td>
                <td>Gulaku Premium 24 Kg @1 Kg</td>
                <td>24</td>
                <td>833</td>
                <td>19,992</td>
                <td>19,992</td>
                <td>-</td>
                <td>-</td>
            </tr>
            <tr>
                <td>2</td>
                <td>14/01/25</td>
                <td>D 9750 AD</td>
                <td>810</td>
                <td>PT SIL</td>
                <td>Midi DC Boyolali</td>
                <td>Boyolali</td>
                <td>Gulaku Premium 24 Kg @1 Kg</td>
                <td>24</td>
                <td>833</td>
                <td>19,992</td>
                <td>19,992</td>
                <td>-</td>
                <td>458,150</td>
            </tr>
            <tr>
                <td>3</td>
                <td>09/01/25</td>
                <td>B 9209 KXR</td>
                <td>784</td>
                <td>PT SIL</td>
                <td>Indogrosir Surabaya</td>
                <td>Surabaya</td>
                <td>Gulaku Premium 24 Kg @1 Kg</td>
                <td>24</td>
                <td>833</td>
                <td>19,992</td>
                <td>19,992</td>
                <td>-</td>
                <td>458,150</td>
            </tr>
            <tr>
                <td>4</td>
                <td>14/01/25</td>
                <td>BE 8207 ACU</td>
                <td>1559</td>
                <td>PT GPM</td>
                <td>Midi DC Boyolali</td>
                <td>Boyolali</td>
                <td>Gulaku Premium 24 Kg @1 Kg</td>
                <td>24</td>
                <td>833</td>
                <td>19,992</td>
                <td>19,992</td>
                <td>-</td>
                <td>458,150</td>
            </tr>
            <tr>
                <td>5</td>
                <td>18/01/25</td>
                <td>B 9656 UXS</td>
                <td>1572</td>
                <td>PT GPM</td>
                <td>SAT Semarang</td>
                <td>Semarang</td>
                <td>Gulaku Tebu 24 Kg @1 Kg</td>
                <td>24</td>
                <td>833</td>
                <td>19,992</td>
                <td>19,992</td>
                <td>-</td>
                <td>-</td>
            </tr>
            <tr>
                <td>6</td>
                <td>14/01/25</td>
                <td>B 9480 UXS</td>
                <td>813</td>
                <td>PT SIL</td>
                <td>SAT Sidoarjo</td>
                <td>Sidoarjo</td>
                <td>Gulaku Premium 24 Kg @1 Kg</td>
                <td>24</td>
                <td>833</td>
                <td>19,992</td>
                <td>19,992</td>
                <td>-</td>
                <td>-</td>
            </tr>
            <tr>
                <td>7</td>
                <td>16/01/25</td>
                <td>BE 8815 AUD</td>
                <td>825</td>
                <td>PT SIL</td>
                <td>SinarMas Pati</td>
                <td>Pati</td>
                <td>Gulaku Premium 24 Kg @1 Kg</td>
                <td>24</td>
                <td>24</td>
                <td>576</td>
                <td>576</td>
                <td>-</td>
                <td>-</td>
            </tr>
            <tr>
                <td>8</td>
                <td>13/01/25</td>
                <td>B 9581 SXR</td>
                <td>802</td>
                <td>PT SIL</td>
                <td>Indomaret DCS Klaten</td>
                <td>Klaten</td>
                <td>Gulaku Premium 24 Kg @1 Kg</td>
                <td>24</td>
                <td>833</td>
                <td>19,992</td>
                <td>19,992</td>
                <td>-</td>
                <td>458,150</td>
            </tr>
            <tr>
                <td>9</td>
                <td>18/01/25</td>
                <td>B 9472 UXS</td>
                <td>857</td>
                <td>PT SIL</td>
                <td>SAT Semarang</td>
                <td>Semarang</td>
                <td>Gulaku Premium 24 Kg @1 Kg</td>
                <td>24</td>
                <td>833</td>
                <td>19,992</td>
                <td>19,992</td>
                <td>-</td>
                <td>-</td>
            </tr>
            <tr>
                <td>10</td>
                <td>20/01/25</td>
                <td>BE 8198 BP</td>
                <td>1593</td>
                <td>PT GPM</td>
                <td>GLK Bandung</td>
                <td>Bandung</td>
                <td>Gulaku Tebu 24 Kg @1 Kg</td>
                <td>24</td>
                <td>833</td>
                <td>19,992</td>
                <td>19,992</td>
                <td>-</td>
                <td>-</td>
            </tr>
            <tr>
                <td>11</td>
                <td>15/01/25</td>
                <td>BE 8387 AUE</td>
                <td>807</td>
                <td>PT SIL</td>
                <td>GLK Surabaya</td>
                <td>Surabaya</td>
                <td>Gulaku Premium 24 Kg @1 Kg</td>
                <td>24</td>
                <td>784</td>
                <td>18,816</td>
                <td>20,016</td>
                <td>-</td>
                <td>-</td>
            </tr>
            <tr>
                <td>12</td>
                <td>17/01/25</td>
                <td>BE 8381 AUC</td>
                <td>1586</td>
                <td>PT GPM</td>
                <td>SAT Malang</td>
                <td>Malang</td>
                <td>Gulaku Tebu 24 Kg @1 Kg</td>
                <td>24</td>
                <td>833</td>
                <td>19,992</td>
                <td>19,992</td>
                <td>-</td>
                <td>-</td>
            </tr>
            <tr>
                <td>13</td>
                <td>21/01/25</td>
                <td>B 9077 KXR</td>
                <td>1597</td>
                <td>PT GPM</td>
                <td>GLK Yogyakarta</td>
                <td>Yogyakarta</td>
                <td>Gulaku Tebu 20 Kg @1/2 Kg</td>
                <td>20</td>
                <td>200</td>
                <td>4,000</td>
                <td>20,008</td>
                <td>-</td>
                <td>-</td>
            </tr>
            <tr>
                <td>14</td>
                <td>17/01/25</td>
                <td>BE 8054 AUD</td>
                <td>1582</td>
                <td>PT GPM</td>
                <td>GLK Surabaya</td>
                <td>Surabaya</td>
                <td>Gulaku Tebu 24 Kg @1 Kg</td>
                <td>24</td>
                <td>667</td>
                <td>16,008</td>
                <td>20,008</td>
                <td>-</td>
                <td>-</td>
            </tr>
            <tr>
                <td>15</td>
                <td>20/01/25</td>
                <td>B 9209 KXR</td>
                <td>852</td>
                <td>PT SIL</td>
                <td>GLK Surabaya</td>
                <td>Surabaya</td>
                <td>Gulaku Premium 20 Kg @1/2 Kg</td>
                <td>20</td>
                <td>100</td>
                <td>2,000</td>
                <td>2,000</td>
                <td>-</td>
                <td>-</td>
            </tr>
            <tr>
                <td>16</td>
                <td>21/01/25</td>
                <td>BE 8204 ALB</td>
                <td>1596</td>
                <td>PT GPM</td>
                <td>GLK Yogyakarta</td>
                <td>Yogyakarta</td>
                <td>Gulaku Tebu 24 Kg @1 Kg</td>
                <td>24</td>
                <td>833</td>
                <td>19,992</td>
                <td>19,992</td>
                <td>-</td>
                <td>-</td>
            </tr>
            <tr>
                <td>17</td>
                <td>21/01/25</td>
                <td>B 9649 TXR</td>
                <td>862</td>
                <td>PT SIL</td>
                <td>GLK Purwokerto</td>
                <td>Purwokerto</td>
                <td>Gulaku Premium 20 Kg @1/2 Kg</td>
                <td>20</td>
                <td>100</td>
                <td>2,000</td>
                <td>20,000</td>
                <td>-</td>
                <td>-</td>
            </tr>
            <tr>
                <td>18</td>
                <td>21/01/25</td>
                <td>B 9021 WEH</td>
                <td>1594</td>
                <td>PT GPM</td>
                <td>GLK Bandung</td>
                <td>Bandung</td>
                <td>Gulaku Tebu 24 Kg @1 Kg</td>
                <td>24</td>
                <td>833</td>
                <td>19,992</td>
                <td>19,992</td>
                <td>-</td>
                <td>-</td>
            </tr>
            <tr>
                <td>19</td>
                <td>22/01/25</td>
                <td>B 9480 CXR</td>
                <td>1617</td>
                <td>PT GPM</td>
                <td>Indomaret Depo Cirebon</td>
                <td>Tegal</td>
                <td>Gulaku Tebu 24 Kg @1 Kg</td>
                <td>24</td>
                <td>833</td>
                <td>19,992</td>
                <td>19,992</td>
                <td>-</td>
                <td>458,150</td>
            </tr>

            <tr>
                <td colspan="13" class="text-right bold">TOTAL :</td>
                <td class="bold">2,290,750</td>
            </tr>
        </tbody>
    </table>

    <div class="mt-60 text-right">
        <p>HORMAT KAMI</p>
        <br><br><br>
        <p class="underline bold">EVI IRAWATI</p>
    </div>

</body>

</html>
