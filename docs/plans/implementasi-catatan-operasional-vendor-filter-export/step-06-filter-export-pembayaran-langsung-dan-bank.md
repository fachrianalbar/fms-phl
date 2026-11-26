# Step 06: Filter Card (§3 PHL) & Export Excel + PDF — Pembayaran Langsung & Kas/Bank

- **Plan Utama:** [implementasi-catatan-operasional-vendor-filter-export.md](../implementasi-catatan-operasional-vendor-filter-export.md)
- **Prasyarat:** Step 05 selesai
- **Status:** `done`

---

## Tujuan Langkah

Menambahkan Filter Card standar (`phl-table-design` §3, light + dark mode) serta Export Excel & PDF pada seluruh menu di modul **Pembayaran Langsung** (4 submenu) dan **Kas & Bank** (3 submenu):
- **Pembayaran Langsung (`direct-payment/*`):**
  1. `direct-payment/order/waiting` (*Order Menunggu Nota*)
  2. `direct-payment/order/unpaid` (*Nota Belum Lunas*)
  3. `direct-payment/order/paid` (*Pembayaran Lunas*)
  4. `direct-payment/payment` (*Daftar Pembayaran*)
- **Kas & Bank (`bank/*`):**
  5. `bank/bank-account` (*Rekening Bank*)
  6. `bank/user-bank` (*Bank Pengguna*)
  7. `bank/bank-book` (*Buku Bank*)

---

## Daftar File yang Disentuh

- `[MODIFY]` `routes/direct-payment.php`, `routes/bank.php`
- `[MODIFY]` `app/Http/Controllers/Finance/DirectPaymentController.php`, `app/Services/Finance/DirectPaymentService.php`
- `[MODIFY]` `app/Http/Controllers/Bank/BankAccountController.php`, `app/Http/Controllers/Bank/UserBankController.php`, `app/Http/Controllers/Bank/BankBookController.php`
- `[MODIFY]` `resources/views/direct-payment/order/waiting.blade.php`, `unpaid.blade.php`, `paid.blade.php`, `resources/views/direct-payment/payment/index.blade.php`
- `[MODIFY]` `resources/views/bank/bank-account/index.blade.php`, `resources/views/bank/user-bank/index.blade.php`, `resources/views/bank/bank-book/index.blade.php`
- `[NEW]` Export classes (`DirectPaymentWaitingExport`, `DirectPaymentNotaExport`, `DirectPaymentListExport`, `BankAccountExport`, `UserBankExport`, `BankBookExport`) & Blade PDF templates masing-masing.

---

## Action Checklist & Detail Perubahan

- [x] **1. Pembayaran Langsung (4 Submenu):**
  - Tambah route `export/excel` & `export/pdf` untuk `waiting`, `unpaid`, `paid`, dan `payment` di `routes/direct-payment.php`.
  - Tambahkan parameter filter di `DirectPaymentService` (tanggal `startDate`/`endDate`, `customerCode`, `companyCode`, `plateNumber` / `notaNumber` / `userBankCode`).
  - Pasang `.filter-card` §3 (termasuk CSS dark mode) + tombol Export Excel & PDF + `syncExportUrls()` di keempat view.

- [x] **2. Kas & Bank (3 Submenu):**
  - Tambah route `export/excel` & `export/pdf` di `routes/bank.php` untuk `bank-account`, `user-bank`, dan `bank-book`.
  - Tambahkan filter yang relevan:
    - `bank-account`: Nama/Kode Bank.
    - `user-bank`: Bank, Tipe Rekening (Perusahaan/Pribadi), Nama Pemilik/No. Rekening.
    - `bank-book`: Rentang Tanggal, Rekening Bank, Tipe Mutasi (In/Out).
  - Pasang `.filter-card` §3 (termasuk CSS dark mode) + tombol Export Excel & PDF + `syncExportUrls()` di ketiga view.

---

## Kriteria Selesai & Verifikasi Langkah

- [x] Cek route:
  ```bash
  php artisan route:list --path=direct-payment
  php artisan route:list --path=bank
  ```
- [x] Lint semua file PHP baru & diubah (`php -l`).
- [ ] Uji manual 7 menu (filter, reset, Excel, PDF, terang & gelap).
