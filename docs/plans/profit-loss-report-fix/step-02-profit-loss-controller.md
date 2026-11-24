# Step 2: Perbaikan & Penyesuaian `ProfitLossController`

> **Referensi Plan:** [../profit-loss-report-fix.md](../profit-loss-report-fix.md)  
> **Prasyarat:** [step-01-profit-loss-service.md](./step-01-profit-loss-service.md) selesai  
> **Status:** `done`

## Tujuan Langkah
Memperbaiki semua penyebab error pada halaman Show dan DataTables, mengganti filter/kolom `Fleet Type` menjadi `Perusahaan Kendaraan`, memunculkan kolom `Pajak` dan `Laba Bersih`, menyelaraskan `Biaya Order`, serta mengubah seluruh teks controller ke Bahasa Indonesia.

## Daftar File yang Disentuh
- `[MODIFY]` `app/Http/Controllers/Report/ProfitLossController.php`

## Action Checklist & Detail Perubahan

- [x] Ubah `$this->title = 'Laporan Laba Rugi';` pada `__construct()`.
- [x] Update `index()`:
  - Kirim `$fleetCompanies = FleetCompany::orderBy('name')->get()` ke view (dan `$fleet = $this->fleetSvc->findAll()`), hapus kebutuhan filter `$fleetType`.
- [x] Refactor `show(string $id, Request $request)`:
  - Gunakan `Fleet::with($this->service->fleetReportRelations($request->startDate, $request->endDate))->where('id', $id)->firstOrFail()` agar ID invalid tidak crash null property.
  - Panggil `$summary = $this->service->calculateFleetSummary($data)` dan teruskan `basicSales`, `orderCost`, `maintenance`, `tax`, `netProfit` (dan `totalMargin` alias bila perlu kompatibilitas) ke view `show.blade.php`.
- [x] Refactor `datatable(Request $request)`:
  - Gunakan `$this->service->fleetReportRelations($request->startDate, $request->endDate)`.
  - Terapkan filter `plateNumber` (exact match bila dipilih dari Select2) dan `fleetCompanyCode` (`where('fleetCompanyCode', $request->fleetCompanyCode)`).
  - Pre-load `TonaseBonus::all()` sekali sebelum DataTables memproses baris.
  - Hitung `$rowSummary` per `Fleet` via `$this->service->calculateFleetSummary($row, $tonaseBonuses)`.
  - Ganti kolom `type.name` menjadi `company_name` (`$row->company?->name ?? '-'`) dengan `filterColumn` ke relasi `company`.
  - Tambahkan kolom `tax` (`number_format($summary['tax'], 0, ',', '.')`), perbaiki kolom `order_cost` (memakai `$summary['orderCost']`), dan kolom `total_margin` / `net_profit` (memakai `$summary['netProfit']`).
  - Sertakan agregat total halaman/filtered query (`->with(['summary' => ...])`) untuk kartu KPI / footer ringkasan jika diperlukan di view.
  - Terjemahkan tooltip tombol aksi Show menjadi `title="Lihat Detail"`.
- [x] Perbaiki `datatableOrder(Request $request)`:
  - Gunakan `$this->service->calculateOrderAllowance($row)` dan null-safe `?->` agar tidak crash saat `route` atau `costComponent` null.
- [x] Perbaiki `datatableMaintenance(Request $request)`:
  - Hapus `addColumn('action', ...)` yang memanggil `route($this->view.'edit', $row->id)` (route tidak terdefinisi di `routes/report.php`) karena tabel maintenance pada `show.blade.php` bersifat read-only.
  - Gunakan null-safe `$details->item?->price ?? 0`.
- [x] Refactor `getProfitLossSummary(Request $request)`:
  - Gunakan `Fleet::with(...)->where('code', $fleetCode)->firstOrFail()` dan `$this->service->calculateFleetSummary($data)`.
  - Kembalikan JSON berisi `basicSales`, `orderCost`, `maintenance`, `tax`, dan `netProfit` (`totalMargin`).

## Kriteria Selesai & Verifikasi Langkah
- `php -l app/Http/Controllers/Report/ProfitLossController.php` lolos tanpa error.
- Tidak ada lagi pemanggilan `route('report.profit-loss.edit')` atau akses langsung `->route->price` / `->costComponent->type` tanpa null-safe.
