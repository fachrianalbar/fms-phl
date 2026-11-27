# Step 02: Perawatan Per Armada (Update) & Perawatan Per Suplier (Baru)

- **Plan Utama:** [laporan-lengkap-phl.md](../laporan-lengkap-phl.md)
- **Prasyarat:** [Step 01](./step-01-migrasi-skema-pajak-sim-npwp.md)
- **Status:** `done`

## Tujuan Langkah

1. Menyesuaikan **Laporan Perawatan Per Armada** (`report/maintenance-fleet`) agar sesuai template:
   - Halaman depan: `No Polisi`, `Perusahaan`, `Type`, `Total Maint`, `Total Qty`, `Total Pembayaran`, `Utang Jth Tempo`, `Total Utang`.
   - Halaman detail: tambah **filter berdasarkan Supplier**, serta kolom `tgl Jth tempo` dan `Status` (`Lunas` / `Belum`).
2. Membuat **Laporan Perawatan Per Suplier** (`report/maintenance-supplier`) dari nol:
   - Halaman depan: `Suplier`, `Total Maint`, `Total Qty`, `Total Pembayaran`, `Utang Jth Tempo`, `Total Utang`.
   - Halaman detail: `kode mnt`, `tgl & jam pemakaian`, `kode item`, `nama item`, `Perusahaan`, `No polisi`, `Qty`, `harga`, `total`, `tgl Jth tempo`, `Status` (`Lunas` / `Belum`) + **filter berdasarkan Perusahaan dan No Polisi**.

## Daftar File yang Disentuh

- `[NEW]` `app/Services/Report/MaintenancePaymentStatusService.php` *(helper agregasi pembayaran, jatuh tempo, & utang dari relasi `maintenance_purchase` → `purchase`)*
- `[MODIFY]` `app/Http/Controllers/Report/MaintenancePerFleetController.php`
- `[MODIFY]` `app/Exports/MaintenanceFleetReport.php`
- `[MODIFY]` `app/Exports/MaintenanceFleetDetailReport.php`
- `[MODIFY]` `resources/views/report/maintenance-fleet/index.blade.php`
- `[MODIFY]` `resources/views/report/maintenance-fleet/show.blade.php`
- `[MODIFY]` `resources/views/report/maintenance-fleet/report/maintenance-fleet-pdf.blade.php`
- `[MODIFY]` `resources/views/report/maintenance-fleet/report/maintenance-fleet-detail-pdf.blade.php`
- `[NEW]` `app/Http/Controllers/Report/MaintenancePerSupplierController.php`
- `[NEW]` `app/Exports/MaintenanceSupplierReport.php`
- `[NEW]` `app/Exports/MaintenanceSupplierDetailReport.php`
- `[NEW]` `resources/views/report/maintenance-supplier/index.blade.php`
- `[NEW]` `resources/views/report/maintenance-supplier/show.blade.php`
- `[NEW]` `resources/views/report/maintenance-supplier/report/maintenance-supplier-pdf.blade.php`
- `[NEW]` `resources/views/report/maintenance-supplier/report/maintenance-supplier-detail-pdf.blade.php`
- `[MODIFY]` `routes/report.php`

## Action Checklist & Detail Perubahan

- [x] **1. Buat `MaintenancePaymentStatusService`**
  - Bangun subquery agregat per `maintenance.id` yang men-join `maintenance_purchase` → `purchase` (non-deleted) untuk menghasilkan:
    - `totalPaid` (`SUM(purchase.paidAmount)`)
    - `totalUtang` (`SUM(GREATEST(COALESCE(purchase.nominal, 0) - COALESCE(purchase.paidAmount, 0), 0))`)
    - `utangJatuhTempo` (`SUM(CASE WHEN purchase.dueDate <= CURRENT_DATE AND paymentStatus <> 'Paid' THEN sisa ELSE 0 END)`)
    - `dueDate` (tanggal jatuh tempo terdekat dari PO belum lunas, atau `MAX(dueDate)` jika semua lunas)
    - `paymentStatus` (`lunas` jika sisa `<= 0` dan ada PO, `belum` jika sisa `> 0`, `-` jika tanpa PO).

- [x] **2. Update `MaintenancePerFleetController` & Views/Exports**
  - Halaman depan (`datatable`, `pdfMaintenanceFleet`, `MaintenanceFleetReport`): tampilkan `No Polisi`, `Perusahaan`, `Type`, `Total Maint`, `Total Qty`, `Total Pembayaran`, `Utang Jth Tempo`, `Total Utang`.
  - Halaman detail (`detail`, `datatableDetail`, `pdfMaintenanceFleetDetail`, `MaintenanceFleetDetailReport`):
    - Kirim daftar `$suppliers` ke view `show.blade.php` untuk dropdown filter Supplier.
    - Tambahkan filter `supplierCode` pada `datatableDetail`, PDF, dan Excel detail.
    - Tambahkan kolom `dueDate` (`Tgl Jth Tempo`) dan `paymentStatus` (badge `Lunas` / `Belum`).
    - Pastikan semua kolom DataTable mendukung global search sesuai `datatable-search-standard` dan filter bar mengikuti `phl-table-design`.

- [x] **3. Buat `MaintenancePerSupplierController`, Exports & Views**
  - Daftarkan route di `routes/report.php`:
    - `GET report/maintenance-supplier` (`report.maintenance-supplier.index`)
    - `GET report/maintenance-supplier/detail/{supplierCode}` (`report.maintenance-supplier.detail`)
    - `GET report/excel-maintenance-supplier` & `GET report/pdf-maintenance-supplier`
    - `GET report/excel-maintenance-supplier-detail/{supplierCode}` & `GET report/pdf-maintenance-supplier-detail/{supplierCode}`
    - `GET datatable/maintenance-supplier` (`dt.maintenance-supplier`)
    - `GET datatable/maintenance-supplier-detail/{supplierCode}` (`dt.maintenance-supplier-detail`)
  - Halaman depan: group by `supplier.code`, `supplier.name` dengan filter Tanggal & Supplier.
  - Halaman detail: filter Tanggal, **Perusahaan (`fleetCompanyCode`)**, dan **No Polisi (`fleetCode`)** + kolom `kode mnt`, `tgl & jam pemakaian`, `kode item`, `nama item`, `Perusahaan`, `No polisi`, `Qty`, `harga`, `total`, `tgl Jth tempo`, `Status`.

## Kriteria Selesai & Verifikasi Langkah

```bash
php -l app/Services/Report/MaintenancePaymentStatusService.php app/Http/Controllers/Report/MaintenancePerFleetController.php app/Http/Controllers/Report/MaintenancePerSupplierController.php
php artisan route:list --path=maintenance
```

- Halaman `report/maintenance-fleet` dan detailnya menampilkan kolom utang/jatuh tempo/status serta filter supplier berfungsi (termasuk saat export Excel & PDF).
- Halaman `report/maintenance-supplier` dan detailnya berjalan normal dengan filter Perusahaan & No Polisi.
