# Step 07: Laporan Keseluruhan (End-to-End Order)

- **Plan Utama:** [laporan-lengkap-phl.md](../laporan-lengkap-phl.md)
- **Prasyarat:** [Step 05](./step-05-menu-input-pajak-dan-laporan-pajak-pabrik.md), [Step 06](./step-06-menu-input-pajak-dan-laporan-pajak-vendor.md)
- **Status:** `done`

## Tujuan Langkah

Membuat **LAPORAN KESELURUHAN** (`report/overall`) sesuai format sheet pertama pada file Excel — menyajikan 1 baris per order dengan 5 grup kolom:
1. **Info Order (8 kolom):** `Tanggal Muat`, `No. Kendaraan`, `Pabrik`, `Jenis Barang`, `Dari`, `Tujuan`, `Pemilik Kendaraan`, `Nama Ekspedisi di Pabrik`.
2. **Blok Pabrik / Piutang (10 kolom):** `Tonase`, `Ongkos`, `Jumlah`, `Pajak`, `Klaim`, `Total Jumlah`, `No. Invoice`, `DP`, `Tgl Bayar`, `Lunas/Belum`.
3. **Blok Komisi (4 kolom):** `Nama`, `Komisi`, `Tgl Bayar`, `Lunas/Belum`.
4. **Blok Customer / Nota Pembayaran Vendor (10 kolom):** `Tonase Barang`, `Ongkos Barang`, `Jumlah Harga`, `Pajak`, `Total Jumlah`, `Tgl Terima SJ`, `No. Nota Pembayaran`, `DP`, `Tgl Bayar`, `Lunas/Belum`.
5. **Ringkasan (2 kolom):** `Untung`, `Keterangan`.

## Daftar File yang Disentuh

- `[NEW]` `app/Services/Report/OverallReportService.php`
- `[NEW]` `app/Http/Controllers/Report/OverallReportController.php`
- `[NEW]` `app/Exports/OverallReportExport.php`
- `[NEW]` `resources/views/report/overall/index.blade.php`
- `[NEW]` `resources/views/report/overall/report/overall-excel.blade.php`
- `[NEW]` `resources/views/report/overall/report/overall-pdf.blade.php`
- `[MODIFY]` `routes/report.php`

## Action Checklist & Detail Perubahan

- [x] **1. Buat `OverallReportService`**
  - Query `Order` dengan eager-load lengkap: `customer.company`, `material`, `route.originLocation`, `route.destinationLocation`, `fleet.company`, `invoiceDetail.invoice.payments`, `invoiceDetail.invoice.claims`, `vendorPayment.paymentHistory`, `orderPayment.histories`, `cost.costComponent`, `orderCommissions`.
  - Pisahkan logika perhitungan per baris ke method `buildRow(Order $order): array` agar dipakai konsisten oleh DataTable, Excel, dan PDF:
    - **Blok Pabrik:** `Total Jumlah = Jumlah - Pajak - Klaim`; `DP` = pembayaran parsial; `Status` = `Lunas` / `Belum Lunas`.
    - **Blok Komisi:** nama penerima/komponen komisi, nominal, tanggal bayar dari laporan komisi, status lunas/belum.
    - **Blok Nota Pembayaran:** ambil dari `vendorPayment` (atau `orderPayment` jika direct payment); `Tgl Terima SJ` = `order.returnDate`.
    - **Untung:** `Total Jumlah Pabrik - Komisi - Total Jumlah Vendor` (disesuaikan dengan rumus contoh di sheet Excel: baris 1 `7.840.000 - 100.000 - 7.840.000 = -100.000`, baris 2 `7.840.000 - 100.000 - 6.860.000 = 880.000`).
    - **Keterangan:** `order.notes` / `order.returnDescription`.

- [x] **2. Buat `OverallReportController` & Route**
  - Filter: Periode `Tanggal Muat` (`orderDate`), Pabrik (`customerCode`), Pemilik Kendaraan (`fleetCompanyCode`), Status Lunas Pabrik, Status Lunas Vendor.
  - Route di `routes/report.php`:
    - `GET report/overall` (`report.overall.index`)
    - `GET report/excel-overall` (`report.overall.excel`)
    - `GET report/pdf-overall` (`report.overall.pdf`)
    - `GET datatable/report-overall` (`dt.report-overall`)

- [x] **3. Buat View DataTable & Export Excel/PDF dengan Multi-Row Header**
  - DataTable menggunakan `scrollX: true` dan grouped `<thead>` 2-3 tingkat warna kontras (light + dark mode) agar mudah dibaca.
  - Export Excel menggunakan `FromView` (`overall-excel.blade.php`) sehingga merge header (`Pabrik`, `Komisi`, `Customer`, sub-header) persis menyamai template Excel.

## Kriteria Selesai & Verifikasi Langkah

```bash
php -l app/Services/Report/OverallReportService.php app/Http/Controllers/Report/OverallReportController.php app/Exports/OverallReportExport.php
php artisan route:list --path=overall
```

- Rumus `Untung` terverifikasi cocok dengan 2 contoh baris pada sheet Excel (`-100.000` dan `880.000`).
- DataTable, filter, global search, Excel (grouped header), dan PDF berjalan normal.
