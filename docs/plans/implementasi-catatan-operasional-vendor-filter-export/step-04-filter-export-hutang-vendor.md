# Step 04: Filter Card (§3 PHL) & Export Excel + PDF — Modul Hutang Vendor (5 Submenu)

- **Plan Utama:** [implementasi-catatan-operasional-vendor-filter-export.md](../implementasi-catatan-operasional-vendor-filter-export.md)
- **Prasyarat:** Step 03 selesai
- **Status:** `done`

---

## Tujuan Langkah

Menambahkan **Filter Card Collapse Terstandarisasi** (sesuai skill `phl-table-design` §3, mendukung Light & Dark Mode) serta **Export Excel & PDF** yang tersinkronisasi dengan filter aktif pada seluruh **5 submenu Hutang (Vendor)**:
1. `vendor/order/waiting` (*Faktur yg Belum Dibayar*)
2. `vendor/invoice/unpaid` (*Faktur Vendor Belum Lunas*)
3. `vendor/invoice/partial` (*Invoice Dibayar Sebagian*)
4. `vendor/invoice/paid` (*Faktur Vendor Lunas*)
5. `vendor/payment` (*Daftar Pembayaran Vendor*)

---

## Daftar File yang Disentuh

- `[MODIFY]` `routes/vendor.php`
- `[MODIFY]` `app/Services/Finance/VendorPaymentService.php`
- `[MODIFY]` `app/Http/Controllers/Finance/VendorInvoiceController.php`
- `[MODIFY]` `app/Http/Controllers/Finance/VendorPaymentListController.php`
- `[MODIFY]` `resources/views/vendor/order/waiting.blade.php`
- `[MODIFY]` `resources/views/vendor/invoice/unpaid.blade.php`
- `[MODIFY]` `resources/views/vendor/invoice/partials/unpaid-payment-script.blade.php`
- `[MODIFY]` `resources/views/vendor/invoice/paid.blade.php`
- `[MODIFY]` `resources/views/vendor/payment/index.blade.php`
- `[NEW]` `app/Exports/VendorWaitingOrderExport.php`
- `[NEW]` `app/Exports/VendorInvoiceExport.php` (dipakai untuk `unpaid`, `partial`, `paid` dengan parameter tipe)
- `[NEW]` `app/Exports/VendorPaymentListExport.php`
- `[NEW]` `resources/views/vendor/order/report/waiting-pdf.blade.php`
- `[NEW]` `resources/views/vendor/invoice/report/invoice-list-pdf.blade.php`
- `[NEW]` `resources/views/vendor/payment/report/payment-list-pdf.blade.php`

---

## Action Checklist & Detail Perubahan

- [x] **1. Tambah Route Export Excel & PDF di `routes/vendor.php`:**
  - `GET vendor/order/waiting/export/excel` & `export/pdf` -> `VendorInvoiceController@exportWaitingExcel` / `exportWaitingPdf`
  - `GET vendor/invoice/unpaid/export/excel` & `export/pdf` -> `VendorInvoiceController@exportUnpaidExcel` / `exportUnpaidPdf`
  - `GET vendor/invoice/partial/export/excel` & `export/pdf` -> `VendorInvoiceController@exportPartialExcel` / `exportPartialPdf`
  - `GET vendor/invoice/paid/export/excel` & `export/pdf` -> `VendorInvoiceController@exportPaidExcel` / `exportPaidPdf`
  - `GET vendor/payment/export/excel` & `export/pdf` -> `VendorPaymentListController@exportExcel` / `exportPdf`

- [x] **2. Dukung Parameter Filter di `VendorPaymentService.php`:**
  - `findWaitingOrders(array $filters = [])`: filter `startDate`, `endDate` (`orderDate`), `fleetCompanyCode` (Perusahaan Kendaraan / Vendor), `companyCode` (Perusahaan Customer), `customerCode`, `plateNumber`.
  - `findPendingNotas(array $filters = [])`, `findPartialNotas(array $filters = [])`, `findPaidNotas(array $filters = [])`: filter `startDate`, `endDate` (`nota_date`), `fleetCompanyCode` (Vendor), `companyCode` (Perusahaan), `notaNumber`.
  - `findPayments(array $filters = [])`: filter `startDate`, `endDate` (`payment_date`), `fleetCompanyCode` (Vendor), `userBankCode` (Bank Sumber Dana).

- [x] **3. Kirim Data Opsi Dropdown Filter & Tambah Method Export di Controller:**
  - Kirim `$fleetCompanies`, `$companies`, `$customers` (atau `$userBanks` di `VendorPaymentListController`) ke masing-masing view index.
  - Pastikan DataTable (`datatableWaiting`, `datatableUnpaid`, `datatablePartial`, `datatablePaid`, `datatable`) meneruskan `$request->all()` ke service.
  - Implementasikan Export Excel (`Excel::download(...)`) dan PDF (`Mpdf` landscape A4) menggunakan data terfilter yang sama persis dengan DataTable.

- [x] **4. Terapkan Filter Card §3 + Tombol Export Excel & PDF di 4 View Blade (`waiting`, `unpaid` [untuk unpaid & partial], `paid`, `payment/index`):**
  - Tambahkan tombol `#export-excel` (`bg-success-subtle text-success`) dan `#export-pdf` (`bg-danger-subtle text-danger`) di header halaman.
  - Tambahkan panel `.filter-card` (collapse tertutup default) tepat di atas tabel dengan Flatpickr (`Y-m-d`), Select2, tinggi seragam `38px`, tombol `.btn-filter-primary` & `.btn-filter-reset`.
  - **Wajib** sertakan blok CSS `html[data-bs-theme="dark"]` untuk `.filter-card`, `.filter-card-header`, `.filter-collapse`, `.filter-label`, `.filter-control`, dan `.btn-filter-reset`.
  - Sinkronkan `getFilters()` -> `ajax.data` dan `syncExportUrls()` saat init dan setiap filter/reset.

---

## Kriteria Selesai & Verifikasi Langkah

- [x] Cek route terdaftar:
  ```bash
  php artisan route:list --path=vendor
  ```
- [x] Lint PHP semua controller, service, dan export class baru.
- [ ] Uji manual 5 submenu Hutang Vendor:
  - Filter tanggal, vendor, perusahaan bekerja dan mempersempit tabel.
  - Reset mengembalikan semua filter & memuat ulang tabel.
  - Download Excel & PDF berjalan dan isi sesuai filter aktif.
  - Tampilan filter rapi di mode Terang & Gelap (tidak putih di dark mode).
