# Step 02: Pembaruan DataTable, Pencarian Global & Tampilan Web

- **Plan Utama:** [order-detail-report-omset-komisi-gaji-pajak.md](../order-detail-report-omset-komisi-gaji-pajak.md)
- **Prasyarat:** Step 01 selesai
- **Status:** `done`

---

## Tujuan Langkah

Memperbarui halaman Web **Laporan Detail Pesanan** (`/report/order-detail`) agar menampilkan 19 kolom baru (termasuk `Qty`, `Harga Satuan`, `Total Omset`, `Komisi`, `Gaji`, `Laba Kotor`, `Potongan Pajak`, dan `Laba Bersih`), mendukung pencarian global DataTables tanpa SQL error sesuai standar `datatable-search-standard`, serta rapi pada light mode dan dark mode.

---

## Daftar File yang Disentuh

- `[MODIFY]` `resources/lang/id/menu_order_detail.php`
- `[MODIFY]` `resources/lang/en/menu_order_detail.php`
- `[MODIFY]` `app/Http/Controllers/Report/OrderDetailController.php`
- `[MODIFY]` `resources/views/report/order-detail/index.blade.php`

---

## Action Checklist & Detail Perubahan

- [ ] **1. Tambahkan key terjemahan di `resources/lang/id/menu_order_detail.php` & `resources/lang/en/menu_order_detail.php`:**
  - Key baru:
    - `unit_price`: `'Harga Satuan'` / `'Unit Price'`
    - `total_omset`: `'Total Omset'` / `'Total Revenue'`
    - `commission`: `'Komisi'` / `'Commission'`
    - `salary`: `'Gaji'` / `'Salary'`
    - `gross_profit`: `'Laba Kotor'` / `'Gross Profit'`
    - `tax`: `'Potongan Pajak (PPh)'` / `'Tax Deduction (PPh)'`
    - `net_profit`: `'Laba Bersih'` / `'Net Profit'`

- [ ] **2. Perbarui `OrderDetailController::datatable()` menggunakan `OrderDetailReportService`:**
  - Gunakan cache per-request (`$rowCache` & `$invoiceWeightCache`) di dalam closure DataTables agar `calculateOrderBreakdown($row, $invoiceWeightCache)` hanya dihitung 1 kali per baris order meskipun diakses oleh banyak `addColumn`.
  - Render kolom baru:
    - `qty`: format angka bersih (mis. `rtrim(rtrim(number_format($qty, 2, ',', '.'), '0'), ',')`).
    - `unit_price`: `Rp ...` rata kanan (`text-end text-nowrap`).
    - `sales` (`Total Omset`): `Rp ...` tebal rata kanan, disertai badge kecil `Antar PT` bila `is_cross_internal === true` agar transparan saat audit.
    - `income`: daftar komponen `On Charge` + total.
    - `commission`: daftar komponen Komisi + total (atau `-` jika kosong).
    - `salary`: daftar komponen Gaji + total (atau `-` jika kosong).
    - `cost_detail`: daftar komponen Biaya Lainnya (di luar Komisi & Gaji).
    - `total_cost`: total `Biaya Lainnya + Komisi + Gaji`.
    - `gross_profit`: badge hijau/merah `Rp ...` sebelum pajak.
    - `tax`: nominal potongan PPh (`Rp ...` atau `-` bila `0`).
    - `profit` (`Laba Bersih`): badge tegas hijau/merah `Rp ...` setelah dikurangi pajak.
  - Tambahkan `filterColumn(...)` untuk seluruh kolom virtual (`unit_price`, `sales`, `income`, `commission`, `salary`, `cost_detail`, `total_cost`, `gross_profit`, `tax`, `profit`, serta `orderDate`) agar pencarian global di kotak Search DataTables tidak pernah memicu SQL `Unknown column` error:
    - Kolom komponen biaya (`income`, `commission`, `salary`, `cost_detail`) dapat dicari berdasarkan nama komponen maupun angka nominal.
    - Kolom angka (`qty`, `unit_price`, `sales`, `total_cost`, `tax`, `gross_profit`, `profit`) mengekstrak digit pencarian untuk mencocokkan kolom numerik terkait atau mengembalikan `whereRaw('1 = 0')` bila keyword murni teks non-angka.

- [ ] **3. Perbarui `resources/views/report/order-detail/index.blade.php`:**
  - Perbarui `<thead>` menjadi 19 kolom berurutan:
    `No`, `Shipment No`, `Order Date`, `Customer`, `Origin`, `Destination`, `Plate Number`, `Driver`, `Qty`, `Harga Satuan`, `Total Omset`, `Biaya Ditagihkan`, `Komisi`, `Gaji`, `Detail Biaya (Tidak Ditagihkan)`, `Total Biaya (Tidak Ditagihkan)`, `Laba Kotor`, `Potongan Pajak (PPh)`, `Laba Bersih`.
  - Perbarui konfigurasi `columns` & `columnDefs` DataTables:
    - Hanya indeks `0` (`DT_RowIndex`) yang `searchable: false`.
    - Set `orderable: false` pada kolom kalkulasi turunan (`unit_price`, `sales`, `income`, `commission`, `salary`, `cost_detail`, `total_cost`, `gross_profit`, `tax`, `profit`) agar klik header tidak memicu error `ORDER BY` kolom virtual.
    - Tambahkan `className: 'text-end text-nowrap'` pada kolom nominal & `text-center` pada `qty`.
  - Hapus aturan CSS kaku `nth-child(9)` dan ganti dengan class `.col-money` yang kompatibel di mode terang maupun `html[data-bs-theme="dark"]`.

---

## Kriteria Selesai & Verifikasi Langkah

- [ ] Lint PHP & kompilasi Blade:
  ```bash
  php -l app/Http/Controllers/Report/OrderDetailController.php && php -l resources/lang/id/menu_order_detail.php && php -l resources/lang/en/menu_order_detail.php
  php artisan tinker --execute="app('blade.compiler')->compileString(file_get_contents('resources/views/report/order-detail/index.blade.php')); echo 'OK';"
  ```
