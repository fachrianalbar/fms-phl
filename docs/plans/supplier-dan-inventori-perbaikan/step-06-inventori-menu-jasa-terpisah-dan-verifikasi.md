# Step 06: Inventori — Pemisahan Menu Jasa dan Barang serta Pengujian Terintegrasi

- **Plan Utama:** [supplier-dan-inventori-perbaikan.md](../supplier-dan-inventori-perbaikan.md)
- **Prasyarat:** [Step 05](./step-05-inventori-perawatan-pemilik-kendaraan.md)
- **Status:** `done`

---

## Tujuan Langkah

Memenuhi kebutuhan pada grup menu **Inventori**: **"buatkan menu jasa jadi jasa dan barang terpisah"**, sekaligus menjalankan verifikasi dan pengujian akhir seluruh rangkaian perubahan Step 01–06.

Saat ini tabel `item` sudah membedakan `Item::TYPE_PART` (`'part'`) dan `Item::TYPE_JASA` (`'jasa'`), tetapi keduanya masih digabung dalam satu menu **Barang** (`inventory/items`). Pada langkah ini:
1. Menu **Barang** (`inventory/items`) dikhususkan murni untuk item bertipe **Barang / Part (`part`)**.
2. Dibuatkan menu baru **Jasa** (`inventory/jasa`, kode menu `SERVICE_ITEM` di bawah `INVENTORY`) khusus untuk item bertipe **Jasa (`jasa`)** dengan fitur setara (CRUD, Filter Card standar PHL light/dark mode, History Pembelian, Sinkron Harga, Export Excel & PDF).
3. Transaksi **Pembelian**, **Pembelian Langsung**, dan **Perawatan** tetap dapat memilih baik Barang maupun Jasa secara mulus.

---

## Daftar File yang Disentuh

- `[NEW]` `database/migrations/2026_11_26_133000_add_jasa_menu_under_inventory.php`
- `[MODIFY]` `database/seeders/MenuSeeder.php`
- `[MODIFY]` `database/seeders/RoleMenuSeeder.php`
- `[MODIFY]` `routes/inventory.php`
- `[MODIFY]` `app/Http/Controllers/Inventory/ItemController.php`
- `[NEW]` `app/Http/Controllers/Inventory/ServiceItemController.php`
- `[MODIFY]` `app/Exports/ItemExport.php`
- `[MODIFY]` `resources/views/inventory/items/index.blade.php`
- `[MODIFY]` `resources/views/inventory/items/create.blade.php`
- `[MODIFY]` `resources/views/inventory/items/edit.blade.php`
- `[MODIFY]` `resources/views/inventory/items/report/items-pdf.blade.php`
- `[NEW]` `tests/Feature/SupplierAndInventoryEnhancementTest.php`

---

## Action Checklist & Detail Perubahan

- [x] **1. Migrasi Menu `SERVICE_ITEM` ("Jasa") & Sinkronisasi Seeder:**
  - Buat migration `2026_11_26_133000_add_jasa_menu_under_inventory.php`:
    - Geser `sort` submenu `INVENTORY` yang saat ini `sort >= 2` (`ITEM_CATEGORY`, `ITEM_UNIT`, `WAREHOUSE`, `STOCK`, `STOCK_TRANSACTION`, `STOCK_SYNC`, `MAINTENANCE`) bertambah `+1` agar urutan di bawah **Inventori** menjadi:
      1. `ITEM` — **Barang** (`inventory/items`, `sort = 1`)
      2. `SERVICE_ITEM` — **Jasa** (`inventory/jasa`, `sort = 2`, `name = 'Service'`, `nama = 'Jasa'`)
      3. `ITEM_CATEGORY` — Kategori Barang (`sort = 3`), dst.
    - Salin seluruh hak akses `role_menu` dari `menuCode = 'ITEM'` (ditambah `'SPRADMIN'`) ke `menuCode = 'SERVICE_ITEM'` agar semua role yang berhak mengelola Barang langsung memiliki akses ke menu Jasa.
    - Sediakan `down()` untuk rollback bersih.
  - Perbarui daftar `$children` di `database/seeders/MenuSeeder.php` dan `RoleMenuSeeder.php` agar `db:seed` tidak menghapus menu `SERVICE_ITEM`.

- [x] **2. Pisahkan Controller & Route untuk Barang (`part`) dan Jasa (`jasa`):**
  - Pada `ItemController`:
    - Tambahkan properti `protected string $itemType = Item::TYPE_PART;` (default `'part'`, route prefix `'inventory.items.'`, datatable route `'dt.items'`, label `'Barang'`).
    - Kunci `filteredQuery()`, dropdown filter daftar item, `syncLatestPurchasePrice()`, serta `store()` dan `update()` agar secara otomatis menggunakan `$this->itemType` (`'part'` di `ItemController`).
  - Buat `App\Http\Controllers\Inventory\ServiceItemController extends ItemController`:
    - Override `$itemType = Item::TYPE_JASA`, `$title = 'Jasa'`, route prefix `'inventory.jasa.'`, datatable route `'dt.jasa'`, serta nama file Export Excel/PDF (`Data-Jasa-*.xlsx` / `Laporan Master Jasa`).
    - Dengan pewarisan terstruktur ini, tidak ada duplikasi kode berlebih dan seluruh fitur (validasi Rupiah, UniqueCodeService, proteksi hapus `ensureNotInUse`, History Pembelian, Sinkron Harga, DataTables global search, Export Excel & PDF) langsung konsisten 100%.
  - Daftarkan route di `routes/inventory.php`:
    - `inventory/jasa/sync-latest-purchase-price` (`inventory.jasa.sync-latest-purchase-price`)
    - `inventory/jasa/export-excel` (`inventory.jasa.export-excel`)
    - `inventory/jasa/export-pdf` (`inventory.jasa.export-pdf`)
    - `inventory/jasa/{itemCode}/purchase-history` (`inventory.jasa.purchase-history`)
    - `Route::resource('jasa', ServiceItemController::class)` (`inventory.jasa.*`)
    - `datatable/jasa` (`dt.jasa`)

- [x] **3. Sesuaikan View `resources/views/inventory/items/*` agar Dinamis Berdasarkan Konteks Barang vs Jasa:**
  - Kirim variabel `$routePrefix` (`inventory.items.` vs `inventory.jasa.`), `$dtRoute` (`dt.items` vs `dt.jasa`), `$itemType` (`part` vs `jasa`), dan `$entityLabel` (`Barang` vs `Jasa`) dari controller ke view.
  - Pada `index.blade.php`:
    - Hapus dropdown filter **Tipe** (karena Barang dan Jasa kini sudah berada di menu masing-masing) dan hapus/sesuaikan kolom `Type` atau tampilkan badge spesifik.
    - Arahkan seluruh URL tombol Tambah, Edit, Delete, Sinkron Harga, History Pembelian, Export Excel/PDF, dan AJAX DataTables menggunakan `$routePrefix` dan `$dtRoute`.
    - Pastikan tampilan tabel & filter mengikuti standar `phl-table-design` (light + dark mode).
  - Pada `create.blade.php` & `edit.blade.php`:
    - Ganti dropdown `type` dengan `<input type="hidden" name="type" value="{{ $itemType }}">` sehingga form di menu **Barang** otomatis menyimpan `part` dan form di menu **Jasa** otomatis menyimpan `jasa`.
    - Pada mode **Jasa**, sembunyikan field gudang fisik (`warehouseCode`) jika ada, dan sesuaikan label judul/placeholder menjadi `Jasa` (mis. `Kode Jasa`, `Nama Jasa`).
  - Pastikan menu Stok (`inventory/stock`) tetap hanya menampilkan stok fisik (`part`), sedangkan dropdown item di Pembelian, Pembelian Langsung, dan Perawatan tetap menampilkan Barang maupun Jasa (mis. diberi penanda `[Jasa]` pada teks `<option>` agar mudah dibedakan).

- [x] **4. Buat Pengujian Otomatis (`tests/Feature/SupplierAndInventoryEnhancementTest.php`) & Verifikasi Akhir:**
  - Tulis pengujian otomatis mencakup seluruh poin catatan:
    1. Master Supplier menyimpan `payment_term_days` dan tampil di DataTable/Export.
    2. Pembelian & Pembelian Langsung menyimpan `subtotal`, `ppn_rate`, `ppn_amount`, `nominal` (setelah PPN), `dueDate`, dan `fleetCode`.
    3. Endpoint DataTables `dt.purchase-payment.unpaid` (dengan parameter `columns[0][data]=""` maupun `'checkbox'`), `dt.purchase-payment.unpaid-supplier` (urutan hutang terlama), dan `exportUnpaidPdf` berjalan sukses (`200 OK`).
    4. Migrasi pra-September 2026 mengubah PO `< 2026-09-01` menjadi `Paid` tanpa mengubah `live_mutation`.
    5. Kalkulasi rincian pembayaran per mobil (`resolveVehicleBreakdown`) menghitung proporsi tagihan & pembayaran tiap mobil secara akurat pada PO multi-mobil.
    6. Menu `inventory/items` hanya menampilkan `type = part` dan menu `inventory/jasa` hanya menampilkan `type = jasa`, serta keduanya dapat menambah & mengubah data masing-masing.
  - Jalankan `vendor/bin/pint --dirty` dan seluruh test terkait.

---

## Kriteria Selesai & Verifikasi Langkah

- [x] Migrasi & route terdaftar bersih:
  ```bash
  php artisan migrate
  php artisan route:list --path=inventory/jasa
  ```
- [x] Jalankan seluruh pengujian otomatis & format kode:
  ```bash
  php artisan test --filter=SupplierAndInventoryEnhancementTest
  vendor/bin/pint --dirty
  ```
- [x] Verifikasi kompilasi seluruh Blade yang diubah/dibuat tanpa error.
