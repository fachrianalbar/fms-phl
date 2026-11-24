# Step 5: Pengujian Otomatis & Verifikasi Akhir

> **Referensi Plan:** [../profit-loss-report-fix.md](../profit-loss-report-fix.md)  
> **Prasyarat:** [step-03-excel-export.md](./step-03-excel-export.md) dan [step-04-views-and-ui.md](./step-04-views-and-ui.md) selesai  
> **Status:** `done`

## Tujuan Langkah
Memastikan seluruh logika bisnis baru (vendor price, pajak PPh proporsional, laba bersih, null-safety, perusahaan kendaraan) dan endpoint HTTP Laporan Laba Rugi tervalidasi dengan automated tests.

## Daftar File yang Disentuh
- `[NEW]` `tests/Unit/ProfitLossCalculationTest.php`
- `[NEW]` `tests/Feature/ProfitLossReportTest.php`

## Action Checklist & Detail Perubahan

- [x] Buat `tests/Unit/ProfitLossCalculationTest.php`:
  - `test_basic_sales_prefers_vendor_price_and_falls_back_to_personal_vendor_price`
  - `test_tax_uses_direct_invoice_pph_and_prorates_multi_order_invoice`
  - `test_tax_uses_order_payment_pph_for_direct_payment_orders`
  - `test_fleet_summary_handles_null_route_and_null_cost_component_safely`
  - `test_net_profit_subtracts_order_cost_maintenance_and_tax`
- [x] Buat `tests/Feature/ProfitLossReportTest.php`:
  - Uji akses `report.profit-loss.index`, `report.profit-loss.show`, `dt.profit-loss`, `dt.profit-loss-order`, `dt.profit-loss-maintenance`, `ajax.profit-loss-summary`, dan `report.profit-loss.excel-profit-loss` dengan user role terotorisasi (`SPRADMIN`) serta penolakan role tanpa akses.
  - Uji filter `fleetCompanyCode`, `plateNumber`, `startDate`, `endDate`.
  - Pastikan `dt.profit-loss-maintenance` tidak error route `edit` dan mengembalikan status `200`.
- [x] Jalankan seluruh test suite dan lint PHP.

## Kriteria Selesai & Verifikasi Langkah
- `php -l app/Services/Report/ProfitLossService.php app/Http/Controllers/Report/ProfitLossController.php app/Exports/ProfitLossReport.php`
- `php artisan test tests/Unit/ProfitLossCalculationTest.php tests/Feature/ProfitLossReportTest.php` — seluruh assertion lolos.
- `php artisan test` — seluruh test suite lolos tanpa regresi.
