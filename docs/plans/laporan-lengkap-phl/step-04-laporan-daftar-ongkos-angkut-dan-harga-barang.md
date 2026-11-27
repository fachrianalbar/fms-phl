# Step 04: Laporan Daftar Ongkos Angkut & Daftar Harga Barang

- **Plan Utama:** [laporan-lengkap-phl.md](../laporan-lengkap-phl.md)
- **Prasyarat:** [Step 01](./step-01-migrasi-skema-pajak-sim-npwp.md)
- **Status:** `done`

## Tujuan Langkah

1. Membuat **Laporan Daftar Ongkos Angkut** (`report/freight-rates`) sesuai template sheet:
   - Kolom: `Pabrik`, `Asal`, `Tujuan`, `Tgl Input`, `Harga Pabrik`, `Harga Vendor Pribadi`, `Nama Vendor`, `Harga Vendor External`.
2. Membuat **Laporan Daftar Harga Barang** (`report/item-prices`) sesuai template sheet:
   - Kolom: `Suplier`, `Item Barang`, `Tgl Input`, `Harga Item`.

## Daftar File yang Disentuh

- `[NEW]` `app/Http/Controllers/Report/FreightRateReportController.php`
- `[NEW]` `app/Exports/FreightRateReportExport.php`
- `[NEW]` `resources/views/report/freight-rates/index.blade.php`
- `[NEW]` `resources/views/report/freight-rates/report/freight-rates-pdf.blade.php`
- `[NEW]` `app/Http/Controllers/Report/ItemPriceReportController.php`
- `[NEW]` `app/Exports/ItemPriceReportExport.php`
- `[NEW]` `resources/views/report/item-prices/index.blade.php`
- `[NEW]` `resources/views/report/item-prices/report/item-prices-pdf.blade.php`
- `[MODIFY]` `routes/report.php`

## Action Checklist & Detail Perubahan

- [x] **1. Buat `FreightRateReportController` (Daftar Ongkos Angkut)**
  - Query: `Route` di-`leftJoin` dengan `route_price_external` (non-deleted) dan `fleet_company`, serta `customer`, `location` (asal & tujuan).
  - Dengan `leftJoin`, rute tanpa vendor external tetap tampil 1 baris (`Nama Vendor = '-'`, `Harga Vendor External = 0`), sedangkan rute dengan beberapa vendor external tampil 1 baris per vendor external sesuai format tabel Excel.
  - Kolom `Tgl Input`: menggunakan `COALESCE(route_price_external.updated_at, route.updated_at, route.created_at)`.
  - Filter: Pabrik (`customerCode`), Asal (`originLocationCode`), Tujuan (`destinationLocationCode`), Vendor (`fleetCompanyId`), dan rentang `Tgl Input`.
  - Route di `routes/report.php`:
    - `GET report/freight-rates` (`report.freight-rates.index`)
    - `GET report/excel-freight-rates` (`report.freight-rates.excel`)
    - `GET report/pdf-freight-rates` (`report.freight-rates.pdf`)
    - `GET datatable/report-freight-rates` (`dt.report-freight-rates`)

- [x] **2. Buat `ItemPriceReportController` (Daftar Harga Barang)**
  - Query: `Item::query()->with(['supplier'])` (filter default `type = part` atau opsi `Semua / Barang / Jasa`).
  - Kolom `Tgl Input`: `COALESCE(item.updated_at, item.created_at)` diformat `d-m-Y`.
  - Filter: Supplier (`supplierCode`), Tipe (`part`/`jasa`), dan rentang `Tgl Input`.
  - Route di `routes/report.php`:
    - `GET report/item-prices` (`report.item-prices.index`)
    - `GET report/excel-item-prices` (`report.item-prices.excel`)
    - `GET report/pdf-item-prices` (`report.item-prices.pdf`)
    - `GET datatable/report-item-prices` (`dt.report-item-prices`)

- [x] **3. Buat Export Excel & PDF untuk kedua laporan**
  - Format nominal ribuan (`#,##0`) di Excel dan `Rp x.xxx.xxx` di PDF/DataTable.
  - Terapkan filter card & DataTable global search sesuai `phl-table-design` dan `datatable-search-standard`.

## Kriteria Selesai & Verifikasi Langkah

```bash
php -l app/Http/Controllers/Report/FreightRateReportController.php app/Http/Controllers/Report/ItemPriceReportController.php app/Exports/FreightRateReportExport.php app/Exports/ItemPriceReportExport.php
php artisan route:list --path=report
```

- Kedua halaman laporan, filter, DataTable search, Excel, dan PDF berjalan tanpa error SQL/render.
