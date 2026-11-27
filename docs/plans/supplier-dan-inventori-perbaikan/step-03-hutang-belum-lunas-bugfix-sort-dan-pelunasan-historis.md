# Step 03: Hutang Belum Lunas — Perbaikan Error, Urutan Hutang Terlama, dan Pelunasan Otomatis Sebelum Bulan 9

- **Plan Utama:** [supplier-dan-inventori-perbaikan.md](../supplier-dan-inventori-perbaikan.md)
- **Prasyarat:** [Step 02](./step-02-pembelian-dan-pembelian-langsung-ppn-tempo-nopol-show.md)
- **Status:** `done`

---

## Tujuan Langkah

Menyelesaikan tiga poin pada menu **Hutang Belum Lunas (`purchasing/purchase-payment`)** dan **Hutang Lunas (`purchasing/purchase-paid`)**:
1. **Perbaikan Error ("masih error"):**
   - Memperbaiki error fatal AJAX DataTables `Invalid column name: "".` saat membuka daftar PO supplier (`/purchasing/purchase-payment/supplier/{supplierCode}`) karena kolom checkbox menggunakan `"data": null` tanpa `"name"`.
   - Memperbaiki error `MpdfException: The HTML code size is larger than pcre.backtrack_limit` pada export PDF daftar hutang (`exportUnpaidPdf` & `exportPaidPdf`) dengan *chunked* `WriteHTML()`.
   - Memperbaiki `ReferenceError: escapeHtml is not defined` dan tag `<td>` ganda pada halaman Hutang Lunas (`paid.blade.php` & `datatablePaid`).
2. **Urutan Berdasarkan Hutang Paling Lama:** Mengurutkan default tabel rekap supplier dan tabel daftar PO belum lunas dari tanggal hutang paling tua (`ASC`).
3. **Pelunasan Otomatis Sebelum Bulan 9 (`date < '2026-09-01'`):** Melunasi seluruh PO belum lunas sebelum `1 September 2026` tanpa memotong saldo Kas & Bank (`live_mutation`).

---

## Daftar File yang Disentuh

- `[NEW]` `database/migrations/2026_11_26_132000_mark_pre_september_2026_purchases_as_paid.php`
- `[MODIFY]` `app/Services/Purchasing/PurchasePaymentService.php`
- `[MODIFY]` `app/Http/Controllers/Purchasing/PurchasePaymentController.php`
- `[MODIFY]` `resources/views/purchasing/purchase-payment/supplier-summary.blade.php`
- `[MODIFY]` `resources/views/purchasing/purchase-payment/index.blade.php`
- `[MODIFY]` `resources/views/purchasing/purchase-payment/paid.blade.php`
- `[MODIFY]` `resources/views/purchasing/purchase-payment/report/purchase-unpaid-pdf.blade.php`
- `[MODIFY]` `resources/views/purchasing/purchase-payment/report/purchase-paid-pdf.blade.php`

---

## Action Checklist & Detail Perubahan

- [x] **1. Perbaikan Error DataTables, JS & Export PDF:**
  - **`resources/views/purchasing/purchase-payment/index.blade.php`:**
    - Ubah definisi kolom pertama (`columns[0]`) dari `"data": null` menjadi `"data": 'checkbox', "name": 'checkbox', "orderable": false, "searchable": false`.
    - Pada `PurchasePaymentController::datatableUnpaid()`, tambahkan `->addColumn('checkbox', fn () => '')` (render HTML checkbox tetap di client atau langsung dari server) sehingga Yajra DataTables tidak lagi melempar `Invalid column name: "".`.
  - **Standarisasi Search & Sort `datatableUnpaid` & `datatableSupplierSummary` (`datatable-search-standard`):**
    - Tambahkan `orderColumn` untuk `purchaseDate` (`purchase.date $1`), `totalPrice`, `paidAmount` (`purchase.paidAmount $1`), `remaining`, dan `paymentStatusHtml` (`purchase.paymentStatus $1`).
    - Tambahkan global/column search yang aman untuk tanggal terformat, nopol, status badge (`Belum Bayar` / `Sebagian`), dan angka nominal.
  - **`resources/views/purchasing/purchase-payment/paid.blade.php` & `PurchasePaymentController::datatablePaid`:**
    - Tambahkan helper `function escapeHtml(value)` di blok `<script>` `paid.blade.php` agar kolom `fleetPlate` tidak memicu `ReferenceError`.
    - Hapus pembungkus `'<td>'.$btn.'</td>'` pada `datatablePaid` (`return $btn;`).
  - **Chunked mPDF Rendering (`PurchasePaymentController`):**
    - Ubah `exportUnpaidPdf()` dan `exportPaidPdf()` agar memanggil `$mpdf->WriteHTML(...)` secara bertahap (header + CSS, potongan baris per 250 PO, lalu `<tfoot>` penutup) sehingga bebas limit `pcre.backtrack_limit 1000000` meskipun data ribuan baris.

- [x] **2. Urutkan Berdasarkan Hutang Paling Lama (`ASC`):**
  - **`PurchasePaymentService::datatableUnpaid()` & `index.blade.php`:**
    - Ubah urutan query default menjadi hutang paling lama terlebih dahulu:
      ```php
      ->orderBy('purchase.date', 'asc')
      ->orderBy('purchase.time', 'asc')
      ->orderByRaw(self::effectiveDueDateSql().' asc')
      ```
    - Di `index.blade.php`, set default DataTables `"order": [[3, 'asc']]` (kolom Tanggal PO paling lama di paling atas; kolom Jatuh Tempo tetap bisa diklik untuk sort).
  - **`PurchasePaymentService::filteredUnpaidBySupplier()` & `supplier-summary.blade.php`:**
    - Tambahkan agregat `MIN(purchase.date) as oldestPurchaseDate` pada query rekap supplier.
    - Tambahkan kolom **Hutang Terlama** (`oldestPurchaseDateHtml`, format `d-M-Y`) di `PurchasePaymentController::datatableSupplierSummary` (`orderColumn('oldestPurchaseDateHtml', 'oldestPurchaseDate $1')`) dan `supplier-summary.blade.php`.
    - Set default `"order"` pada `supplier-summary.blade.php` ke kolom **Hutang Terlama (`asc`)** sehingga supplier dengan hutang paling lama muncul di urutan teratas.

- [x] **3. Migration Pelunasan Otomatis Sebelum Bulan 9 (`date < '2026-09-01'`):**
  - Buat migration `2026_11_26_132000_mark_pre_september_2026_purchases_as_paid.php`:
    - Target baris: `purchase` dengan `deleted_at IS NULL`, `date < '2026-09-01'`, dan `(paymentStatus IS NULL OR paymentStatus != 'Paid')`.
    - Hitung tagihan efektif per PO: `COALESCE(NULLIF(purchase.nominal, 0), detail_sum, 0)`.
    - Update dalam batch/join SQL yang aman dan efisien:
      - `nominal = tagihan_efektif` (jika sebelumnya `NULL`/`0`)
      - `subtotal = COALESCE(NULLIF(subtotal, 0), tagihan_efektif)`
      - `paidAmount = tagihan_efektif`
      - `paymentStatus = 'Paid'`
      - `status = 3`
      - `paymentDate = COALESCE(paymentDate, CASE WHEN YEAR(dueDate) BETWEEN 2020 AND 2030 THEN dueDate END, date)`
      - `paymentCode = COALESCE(paymentCode, 'HIST-PRE-202609')` (penanda audit bahwa pelunasan berasal dari pemutihan historis pra-September 2026, sehingga `down()` hanya mengembalikan baris berpenanda `'HIST-PRE-202609'` bila diperlukan dan tombol *Batal Pembayaran* di UI tidak membatalkan kas/bank karena bukan `supplier_payment_batch` aktif).
    - **Penting:** Tidak menulis ke `mutation` dan tidak mengubah `live_mutation` (saldo Kas & Bank tidak terpotong).

---

## Kriteria Selesai & Verifikasi Langkah

- [x] Jalankan migrasi & cek syntax:
  ```bash
  php artisan migrate
  php -l app/Services/Purchasing/PurchasePaymentService.php app/Http/Controllers/Purchasing/PurchasePaymentController.php
  ```
- [x] Verifikasi query database:
  - Jumlah `purchase` aktif dengan `date < '2026-09-01'` dan `paymentStatus != 'Paid'` harus menjadi **`0`**.
  - Saldo `live_mutation` seluruh rekening bank tetap **tidak berubah** sebelum dan sesudah migrasi.
- [x] Verifikasi endpoint `datatableSupplierSummary`, `datatableUnpaid`, `datatablePaid`, `exportUnpaidPdf`, dan `exportPaidPdf` semuanya mengembalikan HTTP `200` tanpa error.
