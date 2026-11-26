# Step 05: Filter Card (§3 PHL) & Export Excel + PDF — Modul Operasional & Piutang (Faktur)

- **Plan Utama:** [implementasi-catatan-operasional-vendor-filter-export.md](../implementasi-catatan-operasional-vendor-filter-export.md)
- **Prasyarat:** Step 04 selesai
- **Status:** `done`

---

## Tujuan Langkah

Melengkapi Filter Card standar (`phl-table-design` §3, light + dark mode) serta Export Excel & PDF untuk seluruh menu yang belum lengkap di modul **Operasional** dan **Piutang (Faktur)**:
- **Operasional:**
  1. `operational/order` (*Pesanan*): sudah punya Filter + Excel -> **tambah Export PDF**.
  2. `operational/return-do` (*DO Kembali*): sudah punya Filter Status Faktur -> **lengkapi Filter (tanggal, customer, nopol, supir) + tambah Export Excel & PDF**.
  *(Catatan: `operational/not-return-do` sudah lengkap Filter + Excel + PDF).*
- **Piutang (Faktur):**
  3. `invoice/unpaid` (*Faktur Belum Lunas*): **tambah Filter Card + Export Excel & PDF**.
  4. `invoice/partial` (*Faktur Pembayaran Sebagian*): **tambah Filter Card + Export Excel & PDF**.
  5. `invoice/paid` (*Faktur Lunas*): **tambah Filter Card + Export Excel & PDF**.
  6. `invoice/payment` (*Pembayaran Faktur*): sudah punya Excel & PDF -> **tambah/standarisasi Filter Card §3** yang tersinkronisasi dengan tombol export.
  7. `invoice/payment-transaction` (*Transaksi Pembayaran*): **tambah Filter Card + Export Excel & PDF**.

---

## Daftar File yang Disentuh

- `[MODIFY]` `routes/operational.php`, `routes/invoice.php`
- `[MODIFY]` `app/Http/Controllers/Operational/OrderController.php`
- `[MODIFY]` `app/Http/Controllers/Operational/ReturnDoController.php`
- `[MODIFY]` `app/Services/Operational/ReturnDoService.php`
- `[MODIFY]` `app/Http/Controllers/Finance/InvoiceController.php`
- `[MODIFY]` `app/Http/Controllers/Finance/InvoicePaymentController.php`
- `[MODIFY]` `app/Http/Controllers/Finance/InvoicePaymentTransactionController.php`
- `[MODIFY]` `app/Services/Finance/InvoiceService.php`
- `[MODIFY]` `resources/views/operational/order/index.blade.php`
- `[MODIFY]` `resources/views/operational/return-do/index.blade.php`
- `[MODIFY]` `resources/views/invoice/unpaid.blade.php`
- `[MODIFY]` `resources/views/invoice/paid.blade.php`
- `[MODIFY]` `resources/views/invoice/payment/index.blade.php`
- `[MODIFY]` `resources/views/invoice/payment-transaction/index.blade.php`
- `[NEW]` `app/Exports/ReturnDoReport.php`, `app/Exports/InvoiceListExport.php`, `app/Exports/InvoicePaymentTransactionExport.php`
- `[NEW]` `resources/views/operational/order/report/order-pdf.blade.php`, `resources/views/operational/return-do/report/return-do-pdf.blade.php`, `resources/views/invoice/report/invoice-list-pdf.blade.php`, `resources/views/invoice/payment-transaction/report/payment-transaction-pdf.blade.php`

---

## Action Checklist & Detail Perubahan

- [x] **1. Operasional (`Order` & `Return DO`):**
  - Tambah route `GET operational/order/export/pdf` -> `OrderController@pdfOrder`, tambah tombol `#export-pdf` di `operational/order/index.blade.php`, sinkronkan di `syncExportUrls()`.
  - Tambah route `GET operational/return-do/export/excel` & `export/pdf` -> `ReturnDoController@exportExcel` / `exportPdf`.
  - Perluas filter di `ReturnDoService::datatable()` (tanggal `startDate`/`endDate`, `customerName`, `plateNumber`, `driverName`, `invoiceStatus`) dan tambahkan kontrolnya ke `.filter-card` di `operational/return-do/index.blade.php` beserta tombol `#export-excel` & `#export-pdf`.

- [x] **2. Piutang / Faktur (`unpaid`, `partial`, `paid`, `payment`, `payment-transaction`):**
  - Tambah route export Excel & PDF di `routes/invoice.php` untuk `unpaid`, `partial`, `paid`, dan `payment-transaction`.
  - Dukung filter (`startDate`, `endDate`, `customerCode`, `companyCode`, `invoiceNumber`) di `InvoiceService` (`findUnpaid`, `findPartial`, `findPaid`) dan `InvoicePaymentTransactionService` (`startDate`, `endDate`, `customerCode`, `userBankCode`).
  - Pasang `.filter-card` §3 (termasuk CSS dark mode) + tombol `#export-excel` & `#export-pdf` + `syncExportUrls()` di `invoice/unpaid.blade.php`, `invoice/paid.blade.php`, `invoice/payment/index.blade.php`, dan `invoice/payment-transaction/index.blade.php`.

---

## Kriteria Selesai & Verifikasi Langkah

- [x] Cek route:
  ```bash
  php artisan route:list --path=operational
  php artisan route:list --path=invoice
  ```
- [x] Lint semua file PHP baru & diubah (`php -l`).
- [ ] Uji manual seluruh menu di atas (filter, reset, download Excel, download PDF, dan mode terang + gelap).
