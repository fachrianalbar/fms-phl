# Step 03: Pembaruan Export Excel (Direct & Chunked) dan PDF

- **Plan Utama:** [order-detail-report-omset-komisi-gaji-pajak.md](../order-detail-report-omset-komisi-gaji-pajak.md)
- **Prasyarat:** Step 01 & Step 02 selesai
- **Status:** `done`

---

## Tujuan Langkah

Menyelaraskan output **Export Excel** (baik direct download maupun chunked progress loader) dan **Export PDF** pada `Order Detail Report` agar memuat seluruh kolom baru (`Qty`, `Harga Satuan`, `Total Omset`, `Komisi`, `Gaji`, `Laba Kotor`, `Potongan Pajak`, `Laba Bersih`) beserta baris `TOTAL` keseluruhan yang akurat.

---

## Daftar File yang Disentuh

- `[MODIFY]` `app/Http/Controllers/Report/OrderDetailController.php`
- `[MODIFY]` `app/Exports/OrderDetailReport.php`
- `[MODIFY]` `resources/views/report/order-detail/report/order-detail-excel.blade.php`
- `[MODIFY]` `resources/views/report/order-detail/report/order-detail-pdf-header.blade.php`
- `[MODIFY]` `resources/views/report/order-detail/report/order-detail-pdf-rows.blade.php`
- `[MODIFY]` `resources/views/report/order-detail/report/order-detail-pdf-footer.blade.php`
- `[MODIFY]` `resources/views/report/order-detail/report/order-detail-pdf.blade.php`

---

## Action Checklist & Detail Perubahan

- [ ] **1. Perbarui metode export di `OrderDetailController.php`:**
  - `excelOrderDetailChunk()`: ubah potongan koleksi `Order` menjadi array baris terhitung lewat `$this->reportService->prepareExportRows($orders)` sebelum disimpan ke `Cache::put('excel_export_orders_' . $downloadId, ...)`, sehingga ukuran cache ringan dan pajak prorata multi-order sudah terhitung akurat.
  - `pdfOrderDetail()`: ambil data melalui `$this->reportService->buildFilteredQuery($request)->get()`, konversi ke `$preparedRows = $this->reportService->prepareExportRows($data)` dan `$totals = $this->reportService->computeGrandTotals($preparedRows)`, lalu kirim chunk `$preparedRows` ke `order-detail-pdf-rows` dan `$totals` ke `order-detail-pdf-footer`.

- [ ] **2. Perbarui `app/Exports/OrderDetailReport.php` & `order-detail-excel.blade.php`:**
  - Di `OrderDetailReport`:
    - Terima `Request` atau koleksi/array baris yang sudah diproses `OrderDetailReportService::prepareExportRows()`.
    - Kirim `$rows` dan `$totals` ke view `report.order-detail.report.order-detail-excel`.
    - Definisikan `columnFormats()` untuk kolom numerik pada rentang kolom `A` s/d `Z`:
      - `A`: No | `B`: Shipment No | `C`: Order Date | `D`: Customer | `E`: Origin | `F`: Destination | `G`: Plate Number | `H`: Driver
      - `I`: Qty (`#,##0.##`) | `J`: Harga Satuan (`#,##0`) | `K`: Total Omset (`#,##0`)
      - `L`: Nama Komponen (Biaya Ditagihkan) | `M`: Nominal Biaya Ditagihkan (`#,##0`) | `N`: Total Biaya Ditagihkan (`#,##0`)
      - `O`: Nama Komponen Komisi | `P`: Nominal Komisi (`#,##0`) | `Q`: Total Komisi (`#,##0`)
      - `R`: Nama Komponen Gaji | `S`: Nominal Gaji (`#,##0`) | `T`: Total Gaji (`#,##0`)
      - `U`: Nama Komponen (Biaya Lainnya Tidak Ditagihkan) | `V`: Nominal Biaya Lainnya (`#,##0`) | `W`: Total Biaya Tidak Ditagihkan (`#,##0`)
      - `X`: Laba Kotor (`#,##0`) | `Y`: Potongan Pajak PPh (`#,##0`) | `Z`: Laba Bersih (`#,##0`)
  - Di `order-detail-excel.blade.php`:
    - Gunakan `$maxRows = max(count($incomeDetails), count($commissionDetails), count($salaryDetails), count($otherCostDetails), 1)` dengan `rowspan="{{ $maxRows }}"` untuk kolom level order.
    - Tambahkan baris `TOTAL` di akhir tabel Excel untuk seluruh kolom total & laba.

- [ ] **3. Perbarui template PDF (`order-detail-pdf-header`, `order-detail-pdf-rows`, `order-detail-pdf-footer`, dan `order-detail-pdf`):**
  - Sesuaikan ukuran font (`7.5px`) dan padding (`3px`) agar 19 kolom muat proporsional pada halaman Landscape.
  - Render 19 kolom identik dengan tabel Web (`No` s/d `Laba Bersih`).
  - Pada `order-detail-pdf-footer.blade.php`, tampilkan baris `TOTAL` menggunakan `$totals` dari service (`Total Omset`, `Biaya Ditagihkan`, `Komisi`, `Gaji`, `Total Biaya`, `Laba Kotor`, `Potongan Pajak`, `Laba Bersih`).

---

## Kriteria Selesai & Verifikasi Langkah

- [ ] Lint PHP & kompilasi seluruh template Blade export:
  ```bash
  php -l app/Exports/OrderDetailReport.php && php -l app/Http/Controllers/Report/OrderDetailController.php
  php artisan tinker --execute="foreach (['order-detail-excel','order-detail-pdf-header','order-detail-pdf-rows','order-detail-pdf-footer','order-detail-pdf'] as \$v) { app('blade.compiler')->compileString(file_get_contents('resources/views/report/order-detail/report/'.\$v.'.blade.php')); } echo 'ALL_BLADE_OK';"
  ```
