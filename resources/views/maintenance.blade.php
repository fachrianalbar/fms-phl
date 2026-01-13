<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Maintenance</title>
  <style>
    /* Theme */
    :root{--bg1:#071427;--bg2:#0b1b2b;--card:#0f172a;--accent1:#7c3aed;--accent2:#06b6d4;--muted:rgba(230,238,248,0.7)}

    html,body{height:100%;margin:0;background:radial-gradient(1200px 700px at 10% 10%, rgba(124,58,237,0.06), transparent), linear-gradient(180deg,var(--bg1),var(--bg2));color:#e6eef8;font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial}

    /* Centering */
    .wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:0 1.5rem}

    /* Card */
    .card{background:linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));border-radius:18px;padding:32px 44px;max-width:980px;width:100%;box-shadow:0 20px 50px rgba(2,6,23,0.7);border:1px solid rgba(255,255,255,0.03);display:flex;gap:32px;align-items:center;margin:auto}

    /* Logo with subtle animation */
    .logo{width:128px;height:128px;display:flex;align-items:center;justify-content:center;border-radius:20px;background:linear-gradient(135deg,var(--accent1),var(--accent2));box-shadow:0 14px 40px rgba(2,6,23,0.6);border:1px solid rgba(255,255,255,0.06);flex-shrink:0}
    .logo img{width:80px;height:80px;object-fit:contain;display:block;filter:drop-shadow(0 8px 20px rgba(2,6,23,0.45));transition:transform .5s ease}
    .logo:hover img{transform:scale(1.04)}

    /* Content */
    .content{min-width:0}
    .content h1{margin:0;font-size:20px;letter-spacing:0.6px;font-weight:700;color:#fff}
    .content p{margin:12px 0 0;color:var(--muted);line-height:1.6;font-size:14px}

    /* Action */
    .actions{margin-top:18px;display:flex;gap:12px}
    .btn{background:transparent;color:#fff;border:1px solid rgba(255,255,255,0.06);padding:8px 14px;border-radius:8px;font-weight:600;cursor:pointer}
    .btn-primary{background:linear-gradient(90deg,var(--accent1),var(--accent2));box-shadow:0 8px 24px rgba(124,58,237,0.14);border:none}

    /* Small note */
    .note{margin-top:10px;color:rgba(230,238,248,0.45);font-size:13px}

    @media (max-width:720px){
      .card{flex-direction:column;align-items:flex-start;padding:20px}
      .logo{width:96px;height:96px}
      .logo svg{width:48px;height:48px}
    }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <div class="logo" aria-hidden="true" title="Logo Aplikasi">
        <img src="{{ asset('assets/images/logo-phl.png') }}" alt="Logo" />
      </div>

      <div class="content">
        <h1>APLIKASI SEDANG DALAM PEMELIHARAAN</h1>
        <p>{{ $message ?? 'APLIKASI ANDA TIDAK DAPAT DI AKSES. SILAHKAN HUBUNGI ADMINISTRATOR UNTUK INFORMASI LEBIH LANJUT.' }}</p>

        <div class="actions">
          <button class="btn" onclick="location.href='/'">Kembali</button>
          <button class="btn btn-primary" onclick="location.reload()">Muat Ulang</button>
        </div>

        <div class="note">Maaf atas ketidaknyamanan — coba lagi beberapa saat kemudian.</div>
      </div>
    </div>
  </div>
</body>
</html>