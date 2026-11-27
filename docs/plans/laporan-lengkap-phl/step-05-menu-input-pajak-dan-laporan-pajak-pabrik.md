# Step 05: Menu Input Pajak & Laporan Pajak Pabrik

- **Plan Utama:** [laporan-lengkap-phl.md](../laporan-lengkap-phl.md)
- **Prasyarat:** [Step 01](./step-01-migrasi-skema-pajak-sim-npwp.md)
- **Status:** `done`

## Tujuan Langkah

Mengimplementasikan **Laporan Pajak Pabrik** beserta **menu inputan pajak tersendiri** sesuai catatan di sheet Excel:
- Rumus wajib:
  - `DPP Lain = DPP × 11/12`
  - `PPN = DPP Lain × 12%`
- Kolom lengkap (17 kolom):
  `Tanggal Cetak Invoice`, `NPWP`, `Nama Pabrik`, `Tgl Pesanan`, `Jenis Muatan`, `Alamat`, `No Invoice`, `Tonase`, `OA`, `Nominal Invoice/DPP`, `DPP Lain`, `PPN`, `No Faktur Pajak`, `Tgl Bayar Invoice`, `No Bukti Potong`, `Nominal Bukti Potong`, `Tgl Bukti Potong`.

## Daftar File yang Disentuh

- `[NEW]` `app/Http/Controllers/Report/FactoryTaxController.php`
- `[NEW]` `app/Http/Requests/Report/UpdateFactoryTaxRequest.php`
- `[NEW]` `app/Exports/FactoryTaxReportExport.php`
- `[NEW]` `resources/views/report/factory-tax/index.blade.php`
- `[NEW]` `resources/views/report/factory-tax/partials/tax-input-modal.blade.php`
- `[NEW]` `resources/views/report/factory-tax/report/factory-tax-pdf.blade.php`
- `[MODIFY]` `routes/report.php`

## Action Checklist & Detail Perubahan

- [x] **1. Buat `FactoryTaxController` & `UpdateFactoryTaxRequest`**
  - Query data bersumber dari `Invoice` dengan eager-load `customer`, `details.order.material`, `payments`.
  - Mapping kolom per invoice:
    - `Tanggal Cetak Invoice`: `invoice.invoiceDate`
    - `NPWP`: `customer.npwp`
    - `Nama Pabrik`: `customer.name`
    - `Tgl Pesanan`: tanggal order dari `details.order.orderDate` (rentang / daftar unik bila multi-order)
    - `Jenis Muatan`: nama material unik dari `details.order.material.name`
    - `Alamat`: `customer.billingAddress ?: customer.officeAddress`
    - `No Invoice`: `invoice.invoiceNumber`
    - `Tonase`: total `qty` order dalam invoice
    - `OA`: harga ongkos angkut (`order.price` / rata-rata jika multi-order)
    - `Nominal Invoice/DPP`: `invoice.invoiceAmount` (atau `routeAmount + onChargeAmount` sesuai DPP invoice)
    - `DPP Lain`: `round($dpp * 11 / 12, 2)`
    - `PPN`: `round($dppLain * 0.12, 2)`
    - `No Faktur Pajak`: `invoice.tax_invoice_number`
    - `Tgl Bayar Invoice`: tanggal pembayaran terakhir dari `invoice.payments`
    - `No Bukti Potong`, `Nominal Bukti Potong`, `Tgl Bukti Potong`: dari kolom baru di `invoice`.
  - Endpoint simpan input pajak tersendiri: `POST report/factory-tax/{invoice}/tax-info` (via modal input pajak di halaman laporan/menu input pajak, memakai format `rupiah-input-standard` untuk `Nominal Bukti Potong` dengan `prepareForValidation()` di `UpdateFactoryTaxRequest`).

- [x] **2. Daftarkan Route di `routes/report.php`**
  - `GET report/factory-tax` (`report.factory-tax.index`)
  - `POST report/factory-tax/{id}/tax-info` (`report.factory-tax.update-tax`)
  - `GET report/excel-factory-tax` (`report.factory-tax.excel`)
  - `GET report/pdf-factory-tax` (`report.factory-tax.pdf`)
  - `GET datatable/report-factory-tax` (`dt.report-factory-tax`)

- [x] **3. Buat View, Modal Input Pajak & Export Excel/PDF**
  - Filter: Periode Tanggal Invoice, Pabrik (`customerCode`), Status Input Pajak (Sudah / Belum diisi No Faktur Pajak atau Bukti Potong).
  - Modal input pajak: mengisi `No Faktur Pajak`, `No Bukti Potong`, `Nominal Bukti Potong` (class `rupiah-input`), `Tgl Bukti Potong` (Flatpickr).
  - Export Excel: menyertakan rumus/nilai `DPP Lain` & `PPN` serta baris Grand Total di bawah.

## Kriteria Selesai & Verifikasi Langkah

```bash
php -l app/Http/Controllers/Report/FactoryTaxController.php app/Http/Requests/Report/UpdateFactoryTaxRequest.php app/Exports/FactoryTaxReportExport.php
php artisan route:list --path=factory-tax
```

- Input pajak via modal tersimpan ke `invoice` dan langsung tampil di DataTable, Excel, dan PDF.
- Perhitungan `DPP Lain = DPP × 11/12` dan `PPN = DPP Lain × 12%` akurat.
