# Step 07: Filter Card (§3 PHL) & Export Excel + PDF — Modul Supplier & Inventori

- **Plan Utama:** [implementasi-catatan-operasional-vendor-filter-export.md](../implementasi-catatan-operasional-vendor-filter-export.md)
- **Prasyarat:** Step 06 selesai
- **Status:** `done`

---

## Tujuan Langkah

Melengkapi Filter Card standar (`phl-table-design` §3, light + dark mode) serta Export Excel & PDF pada seluruh menu yang belum lengkap di modul **Supplier** dan **Inventori / Gudang**:
- **Supplier (`purchasing/*`):**
  1. `purchasing/purchase` (*Pembelian*): tambah Export Excel & PDF (dan pastikan filter sesuai §3).
  2. `purchasing/direct-purchase` (*Pembelian Langsung*): tambah Export Excel & PDF.
  3. `purchasing/purchase-payment` (*Hutang Belum Lunas*): tambah Filter Card + Export Excel & PDF.
  4. `purchasing/purchase-paid` (*Hutang Lunas*): tambah Export Excel & PDF.
  *(Catatan: `inventory/supplier` & `report/supplier` sudah lengkap Filter + Excel + PDF).*
- **Inventori & Gudang (`inventory/*`, `warehouse/*`):**
  5. `inventory/items` (*Barang*): tambah Export Excel & PDF.
  6. `inventory/item-category` (*Kategori Barang*): tambah Filter Card + Export Excel & PDF.
  7. `inventory/item-unit` (*Satuan Barang*): tambah Filter Card + Export Excel & PDF.
  8. `inventory/warehouse` (*Gudang*): tambah Filter Card + Export Excel & PDF.
  9. `inventory/stock` (*Stok*): tambah Filter Card + Export Excel (PDF sudah ada -> sinkronkan dengan filter).
  10. `inventory/transaction-stock` (*Kartu Stok*): tambah Filter Card + Export Excel (PDF sudah ada -> sinkronkan dengan filter).
  11. `inventory/stock-sync` (*Sinkronisasi Stok*): tambah Filter Card + Export Excel & PDF.
  12. `warehouse/maintenance` (*Perawatan*): tambah Export Excel (Filter & PDF sudah ada -> sinkronkan).

---

## Daftar File yang Disentuh

- `[MODIFY]` `routes/purchasing.php`, `routes/inventory.php`, `routes/warehouse.php`
- `[MODIFY]` Controllers & Services terkait di `App\Http\Controllers\Purchasing\*`, `App\Http\Controllers\Inventory\*`, `App\Http\Controllers\Warehouse\MaintenanceController.php`
- `[MODIFY]` Views `index.blade.php` / `paid.blade.php` terkait di `resources/views/purchasing/`, `resources/views/inventory/`, `resources/views/warehouse/maintenance/`
- `[NEW]` Export classes (`app/Exports/`) & Blade PDF templates untuk setiap menu yang belum punya Excel/PDF.

---

## Action Checklist & Detail Perubahan

- [x] **1. Modul Supplier / Purchasing (4 Submenu):**
  - Tambahkan route `export/excel` & `export/pdf` untuk `purchase`, `direct-purchase`, `purchase-payment`, dan `purchase-paid`.
  - Pastikan semua filter (`startDate`, `endDate`, `supplierCode`, status pembayaran) bekerja seragam di DataTable & Export.
  - Terapkan `.filter-card` §3 + CSS dark mode + `syncExportUrls()` pada `purchase-payment` dan pastikan 3 halaman lainnya konsisten.

- [x] **2. Modul Inventori & Perawatan (8 Submenu):**
  - Tambahkan route `export/excel` & `export/pdf` untuk semua submenu yang belum memilikinya.
  - Tambahkan filter relevan (mis. kategori/satuan untuk `items`, gudang/barang untuk `stock` & `stock-sync`, rentang tanggal/gudang/tipe transaksi untuk `transaction-stock`, nama/kode untuk master inventori).
  - Terapkan `.filter-card` §3 + CSS dark mode + `syncExportUrls()` pada semua view.

---

## Kriteria Selesai & Verifikasi Langkah

- [x] Cek route:
  ```bash
  php artisan route:list --path=purchasing
  php artisan route:list --path=inventory
  php artisan route:list --path=warehouse
  ```
- [x] Lint semua file PHP baru & diubah (`php -l`).
- [ ] Uji manual seluruh menu di modul Supplier & Inventori (filter, reset, Excel, PDF, terang & gelap).
