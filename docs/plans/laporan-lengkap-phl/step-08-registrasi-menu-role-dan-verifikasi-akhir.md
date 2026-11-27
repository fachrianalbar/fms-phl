# Step 08: Registrasi Menu, Role Permission & Verifikasi Akhir

- **Plan Utama:** [laporan-lengkap-phl.md](../laporan-lengkap-phl.md)
- **Prasyarat:** [Step 02](./step-02-perbaikan-perawatan-armada-dan-laporan-perawatan-suplier.md) s/d [Step 07](./step-07-laporan-keseluruhan.md)
- **Status:** `done`

## Tujuan Langkah

1. Mendaftarkan seluruh menu laporan baru di `MenuSeeder.php` (di bawah parent `REPORT`) dan memberikan hak akses default pada `RoleMenuSeeder.php`.
2. Membuat/menjalankan feature tests untuk seluruh endpoint laporan (index, datatable, global search, update input pajak, export Excel & PDF).
3. Melakukan verifikasi kualitas akhir (UI light/dark mode sesuai `phl-table-design`, global search sesuai `datatable-search-standard`, syntax check, dan test suite).

## Daftar File yang Disentuh

- `[MODIFY]` `database/seeders/MenuSeeder.php`
- `[MODIFY]` `database/seeders/RoleMenuSeeder.php`
- `[NEW]` `tests/Feature/Report/CompleteReportsTest.php`

## Action Checklist & Detail Perubahan

- [x] **1. Update `MenuSeeder.php` (Parent `REPORT`)**
  Tambahkan child menu berikut dengan kode `SCREAMING_SNAKE_CASE` & `sort` berurutan:
  - `OVERALL_REPORT` → `Overall Report` / `Laporan Keseluruhan` → `report/overall`
  - `FREIGHT_RATE_REPORT` → `Freight Rate List` / `Daftar Ongkos Angkut` → `report/freight-rates`
  - `ITEM_PRICE_REPORT` → `Item Price List` / `Daftar Harga Barang` → `report/item-prices`
  - `FACTORY_TAX_REPORT` → `Factory Tax Report` / `Laporan Pajak Pabrik` → `report/factory-tax`
  - `VENDOR_TAX_REPORT` → `Vendor Tax Report` / `Laporan Pajak Vendor` → `report/vendor-tax`
  - `DRIVER_LIST_REPORT` → `Driver List` / `Daftar Supir` → `report/drivers`
  - `MAINTENANCE_SUPPLIER` → `Maintenance Per Supplier` / `Perawatan Per Suplier` → `report/maintenance-supplier`

- [x] **2. Update `RoleMenuSeeder.php`**
  - Berikan akses default menu-menu baru di atas untuk role admin/finance sesuai pola role yang sudah ada di `RoleMenuSeeder.php`.

- [x] **3. Buat Feature Test `tests/Feature/Report/CompleteReportsTest.php`**
  - Uji akses `200 OK` ke halaman index, endpoint DataTable JSON, global search keyword (teks, angka, tanggal, status), export Excel, dan export PDF untuk seluruh 8 laporan.
  - Uji penyimpanan input pajak pabrik (`POST report/factory-tax/{id}/tax-info`) & perhitungan `DPP Lain = DPP × 11/12`, `PPN = DPP Lain × 12%`.
  - Uji penyimpanan input pajak vendor (`POST report/vendor-tax/{id}/tax-info`).
  - Uji rumus `Untung` pada `OverallReportService`.

- [x] **4. Jalankan Verifikasi Menyeluruh**
  - Jalankan seeder `MenuSeeder` & `RoleMenuSeeder` (pastikan idempotent).
  - Cek semua route & syntax PHP.
  - Jalankan `php artisan test --filter=CompleteReportsTest`.

## Kriteria Selesai & Verifikasi Langkah

```bash
php artisan db:seed --class=MenuSeeder
php artisan db:seed --class=RoleMenuSeeder
php artisan route:list --path=report
php artisan test --filter=CompleteReportsTest
```

- Seluruh 8 laporan tampil di sidebar menu sesuai hak akses role.
- Seluruh test lolos 100% tanpa error SQL atau exception.
