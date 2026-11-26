# Step 08: Filter Card (§3 PHL) & Export Excel + PDF — Master, Laporan & Sistem

- **Plan Utama:** [implementasi-catatan-operasional-vendor-filter-export.md](../implementasi-catatan-operasional-vendor-filter-export.md)
- **Prasyarat:** Step 07 selesai
- **Status:** `done`

---

## Tujuan Langkah

Menyelesaikan sisa menu yang belum memiliki pasangan lengkap **Filter Card (§3 PHL)** + **Export Excel & PDF** di modul **Data Master**, **Laporan**, dan **Sistem**:

- **Data Master (`master/*`, `data/*`):**
  1. `master/employee` (*Karyawan*): sudah punya Filter -> tambah Export Excel & PDF.
  2. `data/route` (*Rute*): sudah punya Filter -> tambah Export Excel & PDF.
  3. `master/location` (*Lokasi*): tambah Filter Card + Export Excel & PDF.
  4. `master/material` (*Material*): tambah Filter Card + Export Excel & PDF.
  5. `master/unit` (*Unit*): tambah Filter Card + Export Excel & PDF.
  6. `master/cost-component` (*Komponen Biaya*): sudah punya Excel -> tambah Filter Card + Export PDF.
  7. `master/cost-component-price-log` (*Riwayat Harga Komponen*): sudah punya Excel -> tambah Filter Card + Export PDF.
  8. `master/company` (*Perusahaan*): tambah Filter Card + Export Excel & PDF.
  *(Sudah lengkap: `customer`, `fleet-brand`, `fleet-type`, `fleets`, `fleet-company`).*
- **Laporan (`report/*`):**
  9. `report/profit-loss` (*Laba & Rugi*): sudah punya Filter + Excel -> tambah Export PDF.
  10. `report/driver-salary` (*Gaji Sopir*): sudah punya Filter + PDF -> tambah Export Excel.
  11. `report/commission` (*Laporan Komisi*): sudah punya Filter + PDF -> tambah Export Excel.
  12. `report/maintenance-company-internal` (*Perawatan Per Perusahaan*): sudah punya Filter -> tambah Export Excel & PDF.
  *(Sudah lengkap: `order-detail`, `omset`, `maintenance-fleet`, `receivable-aging`, `supplier`).*
- **Sistem (`administrator/*`, `master/menu`):**
  13. `administrator/user` (*Pengguna*): tambah Filter Card (Role/Status) + Export Excel & PDF.
  14. `administrator/role` (*Peran*): tambah Filter Card + Export Excel & PDF.
  15. `master/menu` (*Menu*): tambah Filter Card (Parent Menu) + Export Excel & PDF.
  16. `administrator/activity-log` (*Log Aktivitas*): sudah punya Filter -> tambah Export Excel & PDF.

---

## Daftar File yang Disentuh

- `[MODIFY]` `routes/master.php`, `routes/data.php`, `routes/report.php`, `routes/administrator.php`
- `[MODIFY]` Controllers terkait di `Master\*`, `Data\RouteController`, `Report\*`, `UserController`, `RoleController`, `ActivityLogController`
- `[MODIFY]` Views `index.blade.php` terkait di `resources/views/master/`, `resources/views/data/route/`, `resources/views/report/`, `resources/views/administrator/`
- `[NEW]` Export classes (`app/Exports/`) & Blade PDF templates untuk setiap menu yang belum memilikinya.

---

## Action Checklist & Detail Perubahan

- [x] **1. Lengkapi Modul Data Master (8 Submenu tersisa):**
  - Tambah route export Excel/PDF yang belum ada di `routes/master.php` & `routes/data.php`.
  - Pasang `.filter-card` §3 + dark mode + `syncExportUrls()` pada menu yang belum punya filter (`location`, `material`, `unit`, `cost-component`, `cost-component-price-log`, `company`) dan sambungkan tombol export di `employee` & `route`.

- [x] **2. Lengkapi Modul Laporan (4 Submenu tersisa):**
  - `report/profit-loss`: tambah PDF export (`Mpdf`).
  - `report/driver-salary`: tambah Excel export (`Maatwebsite\Excel`).
  - `report/commission`: tambah Excel export (`Maatwebsite\Excel`).
  - `report/maintenance-company-internal`: tambah Excel & PDF export.
  - Sinkronkan semua tombol export dengan filter aktif via `syncExportUrls()`.

- [x] **3. Lengkapi Modul Sistem (4 Submenu):**
  - Tambah route export Excel & PDF di `routes/administrator.php` & `routes/master.php` (`menu`).
  - Pasang `.filter-card` §3 + dark mode + tombol Export Excel & PDF pada `user`, `role`, `menu`, dan tambah Export Excel & PDF pada `activity-log`.

---

## Kriteria Selesai & Verifikasi Langkah

- [x] Cek route seluruh modul:
  ```bash
  php artisan route:list --path=master
  php artisan route:list --path=data
  php artisan route:list --path=report
  php artisan route:list --path=administrator
  ```
- [x] Jalankan full test suite & audit coverage akhir:
  ```bash
  php artisan test
  ```
- [ ] Pastikan 100% menu aplikasi memiliki Filter Card §3 (teruji Light & Dark mode) serta Export Excel & PDF yang tersinkronisasi. *(Verifikasi UI manual light & dark mode di browser belum dijalankan oleh agent).*
