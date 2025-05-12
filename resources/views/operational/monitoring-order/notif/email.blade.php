<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi Truk Keluar Jalur</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #d9534f;
            text-align: center;
        }

        .content {
            font-size: 16px;
            color: #333;
            line-height: 1.6;
        }

        .info {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }

        .info p {
            margin: 5px 0;
        }

        .btn {
            display: block;
            width: 100%;
            text-align: center;
            background: #28a745;
            color: white !important;
            padding: 12px;
            text-decoration: none;
            font-size: 16px;
            border-radius: 5px;
            margin-top: 20px;
        }

        .btn:hover {
            background: #218838;
        }

        .footer {
            text-align: center;
            font-size: 14px;
            color: #777;
            margin-top: 20px;
        }

        .link {
            font-weight: bold;
            color: white !important;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>⚠️ Peringatan! Truk Keluar dari Jalur</h2>
        <div class="content">
            <p>Halo <strong>{{ $name }}</strong>,</p>
            <p>Kami ingin memberitahu Anda bahwa kendaraan berikut telah keluar dari jalur yang ditentukan:</p>

            <div class="info">
                <p>🚛 <strong>Nomor Truk:</strong> {{ $plateNumber }}</p>
                <p>📍 <strong>Lokasi Terakhir:</strong> {{ $address }}</p>
                <p>🕒 <strong>Waktu Terdeteksi:</strong> {{ $dateTime }}</p>
            </div>

            <p>Silakan segera periksa atau hubungi pihak terkait untuk memastikan keadaan kendaraan.</p>

            <a href="{{ $url }}" class="btn link"
                style="color: white !important; text-decoration: none !important;">🔍 Klik di sini untuk melihat detail
                monitoring</a>

            <p class="footer">
                Terima kasih,<br>
                <strong>TOTAL KILAT SOLUTION</strong><br>
                {{-- [Kontak Support] --}}
            </p>
        </div>
    </div>
</body>

</html>
