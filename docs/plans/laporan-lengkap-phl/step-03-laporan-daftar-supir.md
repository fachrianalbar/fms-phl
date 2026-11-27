# Step 03: Laporan Daftar Supir & Input SIM

- **Plan Utama:** [laporan-lengkap-phl.md](../laporan-lengkap-phl.md)
- **Prasyarat:** [Step 01](./step-01-migrasi-skema-pajak-sim-npwp.md)
- **Status:** `done`

## Tujuan Langkah

1. Menambahkan inputan **No. SIM (`sim`)** pada form Master Karyawan (`master/employee` create & edit) serta menyimpannya melalui `EmployeeService`.
2. Membuat **Laporan Daftar Supir** (`report/drivers`) sesuai template sheet:
   - Kolom: `Nama Supir`, `Tgl Masuk`, `NIK`, `SIM`, `Alamat`, `No Hp`, `Masa Kerja`.
   - Mendukung filter Status (Aktif/Nonaktif), Periode Tanggal Masuk, global search DataTable, serta export Excel & PDF.

## Daftar File yang Disentuh

- `[MODIFY]` `app/Services/Master/EmployeeService.php`
- `[MODIFY]` `resources/views/master/employee/create.blade.php`
- `[MODIFY]` `resources/views/master/employee/edit.blade.php`
- `[MODIFY]` `app/Exports/EmployeeExport.php`
- `[NEW]` `app/Http/Controllers/Report/DriverReportController.php`
- `[NEW]` `app/Exports/DriverListReportExport.php`
- `[NEW]` `resources/views/report/drivers/index.blade.php`
- `[NEW]` `resources/views/report/drivers/report/drivers-pdf.blade.php`
- `[MODIFY]` `routes/report.php`

## Action Checklist & Detail Perubahan

- [x] **1. Tambah field `sim` di Master Karyawan**
  - Tambahkan input teks `sim` (No. SIM) di `resources/views/master/employee/create.blade.php` dan `edit.blade.php`.
  - Pastikan `EmployeeService::store()` dan `EmployeeService::update()` ikut menyimpan kolom `sim`.
  - Lengkapi `EmployeeExport.php` agar opsional juga menyertakan kolom `NIK`, `SIM`, `Tgl Masuk`, `Alamat` bila diperlukan.

- [x] **2. Buat `DriverReportController` & Route**
  - Query dasar: `Employee::query()->whereIn('positionCode', ['KP_240823034043', 'FPS250612034049'])` (konsisten dengan `Employee::isDriver()`).
  - Kolom DataTable:
    - `name` (Nama Supir)
    - `joinDate` (Tgl Masuk, format `d-m-Y`)
    - `nik` (NIK)
    - `sim` (SIM)
    - `address` (Alamat)
    - `phone` (No HP)
    - `work_duration` (Masa Kerja dihitung dari `joinDate` ke hari ini, mis. `1 Thn 4 Bln` / `-` jika kosong)
  - Daftarkan route di `routes/report.php`:
    - `GET report/drivers` (`report.drivers.index`)
    - `GET report/excel-drivers` (`report.drivers.excel`)
    - `GET report/pdf-drivers` (`report.drivers.pdf`)
    - `GET datatable/report-drivers` (`dt.report-drivers`)

- [x] **3. Buat Export Excel (`DriverListReportExport`) & PDF (`drivers-pdf.blade.php`)**
  - Header Excel & PDF persis mengikuti sheet: `No`, `Nama Supir`, `Tgl Masuk`, `NIK`, `SIM`, `Alamat`, `No Hp`, `Masa Kerja`.
  - Pastikan NIK dan No HP diformat sebagai teks di Excel agar tidak berubah menjadi notasi ilmiah (`E+15`).

## Kriteria Selesai & Verifikasi Langkah

```bash
php -l app/Http/Controllers/Report/DriverReportController.php app/Exports/DriverListReportExport.php app/Services/Master/EmployeeService.php
php artisan route:list --path=drivers
```

- Input & edit `sim` di Master Karyawan tersimpan dengan baik.
- Halaman `report/drivers`, export Excel, dan PDF menampilkan seluruh 7 kolom sesuai template termasuk perhitungan Masa Kerja.
