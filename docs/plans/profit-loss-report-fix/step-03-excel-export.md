# Step 3: Pembaruan Export Excel Laporan Laba Rugi

> **Referensi Plan:** [../profit-loss-report-fix.md](../profit-loss-report-fix.md)  
> **Prasyarat:** [step-01-profit-loss-service.md](./step-01-profit-loss-service.md) selesai  
> **Status:** `done`

## Tujuan Langkah
Menyelaraskan export Excel (`ProfitLossReport` dan view `profit-loss-excel.blade.php`) agar memakai filter `fleetCompanyCode`, perhitungan `ProfitLossService` yang sama (vendor price, biaya order lengkap, pajak PPh, laba bersih setelah pajak), kolom `Perusahaan Kendaraan`, serta bahasa Indonesia dan baris jumlah total.

## Daftar File yang Disentuh
- `[MODIFY]` `app/Exports/ProfitLossReport.php`
- `[MODIFY]` `resources/views/report/profit-loss/report/profit-loss-excel.blade.php`
- `[MODIFY]` `app/Http/Controllers/Report/ProfitLossController.php` (`excelProfitLoss` filename menjadi `Laporan-Laba-Rugi.xlsx`)

## Action Checklist & Detail Perubahan

- [x] Perbarui `ProfitLossReport.php`:
  - Inject / resolve `ProfitLossService`.
  - Ubah `title()` menjadi `'Laporan Laba Rugi'`.
  - Terapkan filter `plateNumber` dan `fleetCompanyCode` pada query `Fleet`.
  - Eager-load relasi dari `$service->fleetReportRelations($request->startDate, $request->endDate)`.
  - Hitung summary per baris armada melalui `$service->calculateFleetSummary($row, $tonaseBonuses)` serta total akumulasi keseluruhan (`$grandTotals`) dan kirim ke view Excel.
- [x] Perbarui `resources/views/report/profit-loss/report/profit-loss-excel.blade.php`:
  - Ganti `<html lang="id">` dan `<title>Laporan Laba Rugi</title>`.
  - Ubah header tabel ke Bahasa Indonesia:
    - Grup: `Data Kendaraan` (`colspan="3"`), `Penjualan (A)`, `Biaya (B)` (`colspan="2"`), `Pajak (C)`, `Laba Bersih (A - B - C)`.
    - Sub-header: `No`, `Plat Nomor`, `Perusahaan Kendaraan`, `Penjualan Dasar`, `Biaya Order`, `Biaya Perawatan`, `Pajak (PPh)`, `Laba Bersih`.
  - Render angka dari `$rowSummary` hasil `ProfitLossService` (bukan `@php` loop manual yang rawan null-error).
  - Tambahkan baris `<tfoot>` **Total Keseluruhan** yang menampilkan jumlah masing-masing kolom nominal (`Penjualan Dasar`, `Biaya Order`, `Biaya Perawatan`, `Pajak (PPh)`, `Laba Bersih`).

## Kriteria Selesai & Verifikasi Langkah
- `php -l app/Exports/ProfitLossReport.php` lolos tanpa error.
- Nama file download Excel adalah `Laporan-Laba-Rugi.xlsx` dan perhitungan di view Excel tidak mengandung akses properti null-unsafe.
