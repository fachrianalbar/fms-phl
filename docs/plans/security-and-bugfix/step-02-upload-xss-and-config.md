# Step 02 — Validasi Upload, Sanitasi Error/XSS, & Config Hardening

> **Plan Utama:** [../security-and-bugfix-plan.md](../security-and-bugfix-plan.md)
> **Prasyarat:** Step 01 selesai
> **Status:** `done`

---

## Tujuan Langkah
1. Menutup celah upload file sembarangan pada modul Company Setting dan Master Fleet.
2. Menghilangkan kebocoran pesan exception internal dan potensi JS string break / XSS pada alert flash message.
3. Memindahkan seluruh pemanggilan `env()` di luar `config/` ke `config/services.php` agar aman untuk `php artisan config:cache`, serta mengaktifkan verifikasi SSL pada request WhatsApp.

---

## Daftar File yang Disentuh
- `[MODIFY]` `app/Http/Controllers/CompanySettingController.php`
- `[MODIFY]` `app/Services/CompanySettingService.php`
- `[MODIFY]` `app/Http/Controllers/Master/FleetController.php`
- `[MODIFY]` `resources/views/partials/alert.blade.php`
- `[MODIFY]` `resources/views/data/route/create.blade.php`
- `[MODIFY]` `config/services.php`
- `[MODIFY]` `app/Helpers/SendNotif.php`
- `[MODIFY]` `app/Helpers/GetTokenHelper.php`

---

## Action Checklist & Detail Perubahan

### 1. Perketat Validasi Upload & Rapikan `CompanySetting`
- [x] Pada `CompanySettingController::store()` dan `update()`, tambahkan rule validasi logo:
  ```php
  'logo' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
  ```
- [x] Pada `CompanySettingService::store()` dan `update()`:
  - Normalisasi/acak nama file aman (`$file->hashName()` atau `time().'_'.Str::slug(...)` + extension valid) dan konsisten antara `store()` dan `update()`.
  - Hapus duplikasi key `'name' => $request->name` di `CompanySettingService::update()`.

### 2. Aktifkan Validasi Upload di `FleetController`
- [x] Pada `FleetController::store()` dan `update()`, aktifkan validasi file upload untuk `barcode`, `vehicleRegistrationNumber`, dan `fleetPicture.*`:
  ```php
  'barcode' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
  'vehicleRegistrationNumber' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
  'fleetPicture' => 'nullable|array',
  'fleetPicture.*' => 'file|mimes:jpg,jpeg,png,webp|max:4096',
  ```

### 3. Sanitasi Flash Error & Perbaiki JS String Injection
- [x] Pada `resources/views/data/route/create.blade.php` L227, ganti literal `"{!! Session::get('fail') !!}"` menjadi `@json(Session::get('fail'))`.
- [x] Pada `resources/views/partials/alert.blade.php`, ubah `{!! Session::get('fail') !!}`, `{!! Session::get('error') !!}`, dan `{!! Session::get('success') !!}` menjadi escaped `{{ ... }}` (kecuali ada flash pesan yang memang wajib format HTML terkontrol; audit seluruh `->with('fail'|'error'|'success', ...)` yang memakai `<br>` agar tetap rapi).
- [x] Di controller seperti `UserController` dan `CompanySettingController`, log error detail via `report($th)` dan kirim pesan error yang aman ke user (tanpa membocorkan `'Line : '.$th->getLine()` di production).

### 4. Pindahkan `env()` ke `config/services.php` & Aktifkan SSL Verify
- [x] Tambahkan konfigurasi di `config/services.php`:
  ```php
  'whatsapp' => [
      'url' => env('WA_URL'),
      'user_key' => env('WA_USER_KEY'),
      'pass_key' => env('WA_PASS_KEY'),
      'verify_ssl' => env('WA_VERIFY_SSL', true),
  ],
  'telegram' => [
      'bot_url' => env('TELE_BOT_URL'),
      'bot_key' => env('TELE_BOT_KEY'),
      'webhook_secret' => env('TELEGRAM_WEBHOOK_SECRET'),
  ],
  'total_kilat' => [
      'url' => env('API_TOTAL_KILAT'),
  ],
  ```
- [x] Ubah `SendNotif.php`, `GetTokenHelper.php`, dan `CompanySettingController.php` agar memakai `config('services....')` menggantikan `env(...)`.
- [x] Hapus hardcode `'verify' => false` pada `SendNotif::sendWa()` dan gunakan `config('services.whatsapp.verify_ssl', true)`.

---

## Kriteria Selesai & Verifikasi Langkah
- [x] Tidak ada lagi pemanggilan `env(` di `app/` (`grep -rn "env(" app/` menghasilkan kosong).
- [x] Upload file `.php`/`.svg`/`.txt` pada Company Setting & Fleet ditolak oleh validator.
- [x] Jalankan verifikasi:
  ```bash
  grep -rn "env(" app/ || echo "No env() in app/"
  php artisan view:cache && php artisan view:clear
  php artisan test
  ```
