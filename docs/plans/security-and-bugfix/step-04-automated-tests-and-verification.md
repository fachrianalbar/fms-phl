# Step 04 — Automated Feature, Security & Route Health Tests

> **Plan Utama:** [../security-and-bugfix-plan.md](../security-and-bugfix-plan.md)
> **Prasyarat:** Step 01, Step 02, dan Step 03 selesai
> **Status:** `done`

---

## Tujuan Langkah
Menambahkan automated test suite di `tests/Feature/` (menggunakan SQLite `:memory:` agar aman dan terisolasi dari database dev/MySQL) sehingga semua celah keamanan, webhook Telegram, dan kesehatan seluruh route diuji otomatis setiap kali `php artisan test` dijalankan.

---

## Daftar File yang Disentuh
- `[NEW]` `tests/Feature/SecurityAndAuthTest.php`
- `[NEW]` `tests/Feature/RouteHealthTest.php`

---

## Action Checklist & Detail Perubahan

### 1. Buat `tests/Feature/SecurityAndAuthTest.php`
- [x] Setup SQLite `:memory:` untuk tabel minimal (`users`, `role`, `menu`, `role_menu`, `settings`, `company_setting`, `activity_log`) tanpa menyentuh MySQL lokal.
- [x] Uji kasus berikut:
  - Guest mengakses `/home`, `/change-password`, `/logs`, `/administrator/user` → ditolak (`302` redirect ke `/`).
  - User non-admin (`roleCode !== 'SPRADMIN'`) mengakses `/logs` dan route terproteksi `access:SPRADMIN` → `403 Forbidden`.
  - User `SPRADMIN` dapat mengakses `/logs` / route admin → bukan `403`/`500` (`200 OK`).
  - Upload file beresiko (`.php`, `.svg`, `.exe`) ke `CompanySettingController` (`logo`) dan `FleetController` (`barcode`, `vehicleRegistrationNumber`, `fleetPicture`, `newFleetPicture`) ditolak validasi, dan upload PNG valid di `CompanySettingController` berhasil.

### 2. Buat `tests/Feature/RouteHealthTest.php`
- [x] Iterasi semua route dari `Route::getRoutes()`:
  - Assert seluruh controller & method target eksis (`Broken routes == 0`) serta seluruh controller `App\Http\Controllers\*` dapat di-instantiate saat `Auth::user()` bernilai `null`.
  - Assert tidak ada route non-whitelist (`/`, `/authenticate`, `/logout`, `/maintenance`, `/up`) yang berjalan tanpa middleware `auth` atau `guest`.

### 3. Verifikasi Akhir Menyeluruh
- [x] Jalankan seluruh rangkaian verifikasi akhir:
  ```bash
  php artisan optimize:clear
  php artisan view:cache && php artisan view:clear
  php artisan test
  composer audit --no-interaction
  ```
- [x] Perbarui status semua step di `docs/plans/security-and-bugfix-plan.md` dan tiap file `step-XX-*.md` menjadi `done`, lalu buat/perbarui walkthrough hasil akhir.

---

## Kriteria Selesai & Verifikasi Langkah
- [x] Seluruh file test baru lolos tanpa error maupun skipped test.
- [x] Total test suite (`43` existing + `8` test baru = `51` test, `298` assertions) **100% pass**.
- [x] `composer audit` tetap bersih (`No security vulnerability advisories found.`).
- [x] `php artisan view:cache` sukses tanpa error.
