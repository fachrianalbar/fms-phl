# Step 04: Pengujian Integrasi End-to-End & Verifikasi Akhir

- **Plan Utama:** [order-detail-report-omset-komisi-gaji-pajak.md](../order-detail-report-omset-komisi-gaji-pajak.md)
- **Prasyarat:** Step 01, Step 02, dan Step 03 selesai
- **Status:** `done`

---

## Tujuan Langkah

Memastikan seluruh alur **Order Detail Report** (halaman indeks, AJAX DataTables + pencarian global, Export Excel direct & chunked, serta Export PDF) teruji otomatis dari ujung ke ujung tanpa regresi pada modul laporan lainnya.

---

## Daftar File yang Disentuh

- `[NEW]` `tests/Feature/OrderDetailReportTest.php`

---

## Action Checklist & Detail Perubahan

- [ ] **1. Buat Feature Test `tests/Feature/OrderDetailReportTest.php`:**
  - Uji `GET /report/order-detail` mengembalikan HTTP 200 dan memuat header kolom baru (`Qty`, `Harga Satuan`, `Total Omset`, `Komisi`, `Gaji`, `Laba Kotor`, `Potongan Pajak`, `Laba Bersih`).
  - Uji `GET /datatable/order-detail` (AJAX):
    - Memverifikasi nilai `qty`, `unit_price`, dan `sales` untuk order **Internal Sama PT** (`routeAmount`) vs **Internal Beda PT** (`personalVendorPrice` / `vendorPrice`).
    - Memverifikasi pemisahan biaya ke kolom `commission`, `salary`, dan `cost_detail` serta `total_cost` = gabungan ketiganya.
    - Memverifikasi `gross_profit`, `tax` (prorata `Invoice` & `OrderPayment`), dan `profit` (`net_profit = gross_profit - tax`).
    - Memverifikasi pencarian global (`search[value]`) dengan kata kunci teks biasa, nama komponen biaya, angka nominal, dan kata kunci `'TIDAK-ADA-DATA-XYZ'` (0 hasil, HTTP 200 tanpa SQL error).
  - Uji Export Excel:
    - Direct export `GET /report/excel-order-detail` mengembalikan HTTP 200 dengan file `.xlsx`.
    - Chunked export flow (`excel-order-detail-init` -> `excel-order-detail-chunk` -> `excel-order-detail-download`) mengembalikan HTTP 200.
  - Uji Export PDF:
    - `GET /report/pdf-order-detail` mengembalikan HTTP 200 (`application/pdf`).

- [ ] **2. Jalankan rangkaian verifikasi penuh & code formatting:**
  - Jalankan `vendor/bin/pint --dirty`.
  - Jalankan seluruh unit & feature test terkait laporan.
  - Perbarui status seluruh step di `docs/plans/order-detail-report-omset-komisi-gaji-pajak.md` menjadi `done`.

---

## Kriteria Selesai & Verifikasi Langkah

- [ ] Jalankan pengujian otomatis & Pint:
  ```bash
  vendor/bin/pint --dirty
  php artisan test --filter="OrderDetailReport|ProfitLossCalculationTest|MasterReportSystemFilterExportTest"
  ```
