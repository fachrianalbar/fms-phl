# Step 01: Migrasi Skema Pajak, SIM Supir & NPWP Vendor

- **Plan Utama:** [laporan-lengkap-phl.md](../laporan-lengkap-phl.md)
- **Prasyarat:** Tidak ada (langkah pertama)
- **Status:** `done`

## Tujuan Langkah

Menyiapkan seluruh kolom database yang dibutuhkan oleh 8 laporan dan belum tersedia di skema saat ini:
1. `employee.sim` untuk **Daftar Supir**.
2. `fleet_company.npwp` untuk **Laporan Pajak Vendor**.
3. Kolom input pajak tersendiri (`tax_invoice_number`, `withholding_tax_number`, `withholding_tax_amount`, `withholding_tax_date`) pada tabel `invoice` (Pajak Pabrik) dan `vendor_payment` (Pajak Vendor).

## Daftar File yang Disentuh

- `[NEW]` `database/migrations/2026_11_27_130000_add_sim_npwp_and_tax_fields_for_reports.php`
- `[MODIFY]` `app/Models/Master/Employee.php`
- `[MODIFY]` `app/Models/Master/FleetCompany.php`
- `[MODIFY]` `app/Models/Finance/Invoice.php`
- `[MODIFY]` `app/Models/Finance/VendorPayment.php`

## Action Checklist & Detail Perubahan

- [x] **1. Buat migration `add_sim_npwp_and_tax_fields_for_reports.php`**
  - Tambah `sim` (`string(50)->nullable()->after('nik')`) di tabel `employee` (cek `Schema::hasColumn` agar idempotent).
  - Tambah `npwp` (`string(50)->nullable()->after('name')`) di tabel `fleet_company`.
  - Tambah kolom pajak di tabel `invoice`:
    - `tax_invoice_number` (`string(100)->nullable()`) — No Faktur Pajak
    - `withholding_tax_number` (`string(100)->nullable()`) — No Bukti Potong
    - `withholding_tax_amount` (`decimal(15, 2)->nullable()`) — Nominal Bukti Potong
    - `withholding_tax_date` (`date->nullable()`) — Tgl Bukti Potong
  - Tambah kolom pajak yang sama di tabel `vendor_payment`:
    - `tax_invoice_number` (`string(100)->nullable()`)
    - `withholding_tax_number` (`string(100)->nullable()`)
    - `withholding_tax_amount` (`decimal(15, 2)->nullable()`)
    - `withholding_tax_date` (`date->nullable()`)
  - Implementasikan method `down()` untuk drop kolom-kolom tersebut dengan aman.

- [x] **2. Update `$fillable` & `$casts` di Model**
  - `app/Models/Master/Employee.php`: tambahkan `'sim'` ke `$fillable`, serta helper `getWorkDurationAttribute(): string` untuk menghitung Masa Kerja dari `joinDate` (mis. `2 Tahun 3 Bulan`).
  - `app/Models/Master/FleetCompany.php`: tambahkan `'npwp'` ke `$fillable`.
  - `app/Models/Finance/Invoice.php`: tambahkan 4 kolom pajak ke `$fillable` dan cast `withholding_tax_amount => 'decimal:2'`, `withholding_tax_date => 'date'`.
  - `app/Models/Finance/VendorPayment.php`: tambahkan 4 kolom pajak ke `$fillable` dan cast `withholding_tax_amount => 'decimal:2'`, `withholding_tax_date => 'date'`.

## Kriteria Selesai & Verifikasi Langkah

```bash
php artisan migrate
php artisan migrate:rollback --step=1 && php artisan migrate
php -l app/Models/Master/Employee.php app/Models/Master/FleetCompany.php app/Models/Finance/Invoice.php app/Models/Finance/VendorPayment.php
```

- Migrasi naik & rollback berjalan bersih tanpa error.
- Seluruh model lolos `php -l`.
