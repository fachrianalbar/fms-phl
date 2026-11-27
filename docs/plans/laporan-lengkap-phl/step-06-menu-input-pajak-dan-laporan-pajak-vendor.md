# Step 06: Menu Input Pajak & Laporan Pajak Vendor

- **Plan Utama:** [laporan-lengkap-phl.md](../laporan-lengkap-phl.md)
- **Prasyarat:** [Step 01](./step-01-migrasi-skema-pajak-sim-npwp.md), [Step 05](./step-05-menu-input-pajak-dan-laporan-pajak-pabrik.md)
- **Status:** `done`

## Tujuan Langkah

1. Menambahkan input **NPWP** pada form Master Perusahaan Armada (`master/fleet-company`) agar NPWP vendor dapat diisi dan tampil otomatis di Laporan Pajak Vendor.
2. Mengimplementasikan **Laporan Pajak Vendor** (`report/vendor-tax`) beserta **menu/modal input pajak vendor** sesuai template sheet (15 kolom):
   `Tanggal Nota Pembayaran`, `NPWP`, `Nama Vendor`, `Tgl Pesanan`, `Jenis Muatan`, `Alamat`, `No Nota Pembayaran`, `Tonase`, `OA`, `Nominal Invoice/DPP`, `No Faktur Pajak`, `Tgl Bayar Nota Pembayaran`, `No Bukti Potong`, `Nominal Bukti Potong`, `Tgl Bukti Potong`.

## Daftar File yang Disentuh

- `[MODIFY]` `app/Services/Master/FleetCompanyService.php`
- `[MODIFY]` `resources/views/master/fleet-company/create.blade.php`
- `[MODIFY]` `resources/views/master/fleet-company/edit.blade.php`
- `[NEW]` `app/Http/Controllers/Report/VendorTaxController.php`
- `[NEW]` `app/Http/Requests/Report/UpdateVendorTaxRequest.php`
- `[NEW]` `app/Exports/VendorTaxReportExport.php`
- `[NEW]` `resources/views/report/vendor-tax/index.blade.php`
- `[NEW]` `resources/views/report/vendor-tax/partials/tax-input-modal.blade.php`
- `[NEW]` `resources/views/report/vendor-tax/report/vendor-tax-pdf.blade.php`
- `[MODIFY]` `routes/report.php`

## Action Checklist & Detail Perubahan

- [x] **1. Tambah field `npwp` di Master Perusahaan Armada (`fleet_company`)**
  - Tambahkan input `npwp` pada `resources/views/master/fleet-company/create.blade.php` dan `edit.blade.php`.
  - Pastikan `FleetCompanyService::store()` dan `update()` menyimpan field `npwp`.

- [x] **2. Buat `VendorTaxController` & `UpdateVendorTaxRequest`**
  - Query data bersumber dari `VendorPayment` dengan relasi `order.fleet.company` (Vendor), `order.material`, dan `paymentHistory`.
  - Mapping 15 kolom:
    - `Tanggal Nota Pembayaran`: `vendor_payment.date` (atau `created_at`)
    - `NPWP`: `order.fleet.company.npwp`
    - `Nama Vendor`: `order.fleet.company.name`
    - `Tgl Pesanan`: `order.orderDate`
    - `Jenis Muatan`: `order.material.name`
    - `Alamat`: `order.fleet.company.address`
    - `No Nota Pembayaran`: `vendor_payment.nota_number ?: vendor_payment.code`
    - `Tonase`: `order.qty`
    - `OA`: `order.vendorPriceSingle ?: order.personalVendorPriceSingle`
    - `Nominal Invoice/DPP`: `vendor_payment.amount`
    - `No Faktur Pajak`: `vendor_payment.tax_invoice_number`
    - `Tgl Bayar Nota Pembayaran`: tanggal pembayaran terakhir dari `paymentHistory`
    - `No Bukti Potong`, `Nominal Bukti Potong`, `Tgl Bukti Potong`: dari kolom baru di `vendor_payment`.
  - Endpoint simpan pajak: `POST report/vendor-tax/{id}/tax-info` (dengan normalisasi `rupiah-input-standard` di `UpdateVendorTaxRequest`).

- [x] **3. Daftarkan Route, Views & Exports**
  - Route di `routes/report.php`:
    - `GET report/vendor-tax` (`report.vendor-tax.index`)
    - `POST report/vendor-tax/{id}/tax-info` (`report.vendor-tax.update-tax`)
    - `GET report/excel-vendor-tax` (`report.vendor-tax.excel`)
    - `GET report/pdf-vendor-tax` (`report.vendor-tax.pdf`)
    - `GET datatable/report-vendor-tax` (`dt.report-vendor-tax`)
  - Buat DataTable + filter (Periode, Vendor, Status Input Pajak), Modal Input Pajak, Excel, dan PDF.

## Kriteria Selesai & Verifikasi Langkah

```bash
php -l app/Http/Controllers/Report/VendorTaxController.php app/Http/Requests/Report/UpdateVendorTaxRequest.php app/Exports/VendorTaxReportExport.php app/Services/Master/FleetCompanyService.php
php artisan route:list --path=vendor-tax
```

- Input NPWP di Master Perusahaan Armada tersimpan dan muncul otomatis di Laporan Pajak Vendor.
- Input pajak vendor via modal tersimpan dan tampil konsisten di DataTable, Excel, dan PDF.
