# Laporan Umur Piutang (AR Aging) per Customer — Rencana Eksekusi

> **Status:** ✅ selesai (2026-11-17)
> **Sumber alignment:** `/grill-me` (2026-11-16) — 2 keputusan dikunci (Q1=A, Q2=A) + 6 keputusan turunan
> **Modul/target:** Report (Piutang / AR)
> **Aturan eksekusi:** kerjakan berurutan; setiap langkah divalidasi dengan
> `php artisan test` dan `vendor/bin/pint --dirty --format agent`

---

## 1. Context (verified)

**Laporan yang sudah ada** — `routes/report.php` (`report.*`, `dt.*`, `ajax.*`):

- Order Detail, Profit & Loss, Driver Salary, Maintenance Per Fleet, Maintenance Per Company, Supplier Purchase — `routes/report.php` L11-38.
- Sisi **Hutang (AP)** sudah punya aging lengkap: `App\Exports\SupplierPurchaseReport::agingAggregate` (`aging0To30`, `aging31To60`, `aging61To90`, `agingOver90`, `dueSoonAmount`, `overdueAmount`) — `app/Exports/SupplierPurchaseReport.php` L94-117.
- Pola halaman AP: KPI 4 kartu + filter + DataTable server-side + PDF/Excel — `resources/views/report/supplier/index.blade.php` L43-120, `app/Http/Controllers/Report/SupplierPurchaseController.php` (`index`, `datatable`, `excelSupplier`, `pdfSupplier`, `summaryStats`).

**Sisi Piutang (AR)** — hanya daftar + dashboard, belum ada aging:

- Daftar faktur per status: `invoice/unpaid`, `invoice/partial`, `invoice/paid` — `routes/invoice.php` L14-20; datatable-nya `InvoiceController::datatableUnpaid|datatablePartial|datatablePaid`.
- Dashboard hanya menampilkan 20 faktur jatuh tempo terdekat — `app/Http/Controllers/DashboardController.php` L91-118 (`limit(20)`, tanpa agregasi per customer).
- Tidak ada satu pun route/halaman `report/*` untuk piutang — `routes/report.php` (tidak ada `receivable`, `piutang`, atau `aging`).

**Rumus tagihan yang sudah jadi standar di repo** (dipakai ulang, jangan bikin rumus baru):

- Billing = `invoiceAmount + ppnAmount - pphAmount` — `app/Http/Controllers/Finance/InvoiceController.php` L328-337 (`effectivePaymentStatus`) dan L61-72 (`paymentStateStats`).
- Settled = `SUM(invoice_payment.amount) + SUM(invoice_payment_claim.amount)`; sisa = `max(billing - settled, 0)` — `InvoiceController::paymentStateStats` L72-82.
- Status faktur: `Invoice::STATUS_CREATE = 1`, `STATUS_PARTIAL = 2`, `STATUS_FULL = 3` — `app/Models/Finance/Invoice.php` L19-23.

**Kolom data yang tersedia:**

- `invoice`: `invoiceNumber`, `invoiceDate`, `overdueDate`, `invoiceAmount`, `ppnAmount`, `pphAmount`, `customerCode`, `status`, soft deletes — `app/Models/Finance/Invoice.php` L25-60.
- `invoice_payment`: `paymentDate`, `invoiceCode`, `transactionCode`, `amount`, `nominal` — `app/Models/Finance/InvoicePayment.php` L19-31.
- `invoice_payment_claim`: `invoiceCode`, `transactionCode`, `description`, `amount` — `app/Models/Finance/InvoicePaymentClaim.php` L18-28.
- `customer`: `name`, `code`, `dueDateDuration`, `overdueInvoice` — `app/Models/Master/Customer.php` L19-41.

**Infrastruktur pendukung:**

- mPDF dipakai dengan konfigurasi `['orientation' => 'L', 'format' => [215, 330], 'tempDir' => storage_path('app/mpdf-temp')]` — `SupplierPurchaseController::pdfSupplier` L249-253.
- `Maatwebsite\Excel` via `FromView` + `ShouldAutoSize` — `app/Exports/SupplierPurchaseReport.php` L14-16.
- Timezone aplikasi `Asia/Jakarta` (bukan UTC) — `config/app.php` L73; penting untuk batas "hari ini" pada aging.
- Route report hanya dijaga middleware `auth`; middleware `access` dinonaktifkan (di-comment) — `routes/web.php` L12-30, `routes/master.php` L20. Artinya gate akses = baris `role_menu`, bukan middleware.
- Menu & permission dikelola seeder idempotent: `database/seeders/MenuSeeder.php` (`$children`, grup `REPORT` L113-121) dan `database/seeders/RoleMenuSeeder.php` (`$access` per role L42-110). `SPRADMIN` otomatis semua menu.
- Ikon submenu sidebar dipetakan manual per URL di `$childIcons` — `resources/views/layouts/sidebar.blade.php` L12-89.
- Standar tampilan tabel/filter/KPI: `docs/table-standard.md` L1-60 dan skill `phl-table-design` §3 (filter card), §5 (KPI tiles), §6 (CSS tabel).
- Test yang ada memakai SQLite in-memory yang di-set manual di dalam test (`config()->set('database.default', 'sqlite')`) — `tests/Feature/UniqueCodeServiceTest.php` L17-28. `phpunit.xml` **tidak** memaksa SQLite (L25-26 di-comment), jadi test yang menyentuh MySQL berisiko menabrak DB dev.

**Belum diverifikasi (cek sebelum langkah terkait):**

- Apakah ada invoice lama dengan `overdueDate` NULL atau tahun tidak wajar (mis. `0026`) seperti temuan pada `purchase.dueDate` (`SupplierPurchaseReport::effectiveDueDateExpression` L178-181). Wajib dicek dengan query sampling di Langkah 1.
- Volume baris `invoice` + `invoice_payment` di produksi (menentukan apakah agregasi PHP masih layak atau harus pindah ke SQL).

---

## 2. Keputusan Desain

| ID | Keputusan | Alasan | Dampak |
|---|---|---|---|
| D1 | Laporan yang dibangun: **Laporan Umur Piutang (AR Aging) per customer**, satu halaman read-only. (Q1=A) | Sisi AP sudah punya aging; AR belum punya sama sekali, padahal `overdueDate` + pembayaran sudah tersimpan | `routes/report.php`, `resources/views/report/receivable-aging/` |
| D2 | Cakupan **1 laporan tuntas**: KPI + filter + Excel + PDF + menu/permission + test. (Q2=A) | Standar filter/export di repo menuntut kelengkapan; setengah jadi memicu revisi | 1 controller, 1 export class, 1 helper, 4 view, 2 seeder |
| D3 | Sumber kebenaran nilai = **angka**, bukan kolom `invoice.status`: `billing - paid - claim`, `max(...,0)` | Rumus ini sudah dipakai `InvoiceController::paymentStateStats`; `status` bisa drift (mis. status 3 tapi sisa > 0) | `App\Exports\ReceivableAgingReport` |
| D4 | Jatuh tempo efektif = `overdueDate` bila tahunnya wajar (2020–2030); jika tidak → `invoiceDate + customer.dueDateDuration` hari; jika `dueDateDuration` kosong → `invoiceDate` | Meniru guard `SupplierPurchaseReport::effectiveDueDateExpression`; melindungi dari data tanggal rusak | helper `App\Support\ReceivableAging::effectiveDueDate` |
| D5 | Bucket aging dihitung dari **hari terlambat** terhadap jatuh tempo efektif: `notDue` (belum jatuh tempo), `dueSoon` (≤ 7 hari), `d0_30` (1–30), `d31_60` (31–60), `d61_90` (61–90), `dOver90` (> 90). Overdue = hari terlambat ≥ 1 | Konsisten dengan definisi AP (`dueSoon` = 0..7 hari, `overdue` = `dueDate < CURRENT_DATE`) agar angka AR dan AP bisa dibandingkan | helper `ReceivableAging::bucket` |
| D6 | Filter halaman: `startDate`/`endDate` (berbasis `invoice.invoiceDate`, perilaku sama dengan `applyDateFilter` di `SupplierPurchaseReport` L183-198), `customerCode`, dan `status` (`outstanding` default / `overdue` / `all`) | Menjaga konsistensi filter dengan laporan AP; default hanya faktur bersisa | view index + controller |
| D7 | Aging bersifat **as-of hari ini** (bukan as-of tanggal akhir filter); filter periode hanya membatasi faktur mana yang masuk laporan | Menghindari kebingungan ganda: filter periode ≠ tanggal potret aging | label di UI ("posisi per hari ini") |
| D8 | Drill-down **detail per customer** (daftar faktur + umur + sisa + cetak PDF faktur) disertakan, tapi **tanpa** export PDF/Excel detail di iterasi ini | Tanpa drill-down, tim collection tidak bisa menagih; export detail bisa menyusul | route `report.receivable-aging.detail`, view `show.blade.php` |

---

## 3. File yang Disentuh

| Aksi | Path | Keterangan |
|---|---|---|
| baru | `app/Support/ReceivableAging.php` | Helper murni: `effectiveDueDate`, `daysToDue`, `bucket`, `bucketLabel` (testable tanpa DB) |
| baru | `app/Exports/ReceivableAgingReport.php` | `customerRows()` (agregat per customer) + `invoiceRows()` (detail per customer) + `view()` untuk Excel |
| baru | `app/Http/Controllers/Report/ReceivableAgingController.php` | `index`, `datatable`, `excel`, `pdf`, `detail`, `datatableDetail`, `summary` |
| ubah | `routes/report.php` | 4 route `report.*` + 2 route `dt.*` + 1 route `ajax.*` |
| baru | `resources/views/report/receivable-aging/index.blade.php` | Judul, tombol PDF/Excel/Refresh, 4 KPI, filter card standar, DataTable, JS |
| baru | `resources/views/report/receivable-aging/show.blade.php` | Detail per customer (banner profil + KPI kecil + tabel faktur) |
| baru | `resources/views/report/receivable-aging/report/aging-excel.blade.php` | Tabel polos untuk `FromView` |
| baru | `resources/views/report/receivable-aging/report/aging-pdf.blade.php` | Tabel untuk mPDF landscape |
| ubah | `database/seeders/MenuSeeder.php` | Child `RECEIVABLE_AGING` (`report/receivable-aging`, sort 6, parent `REPORT`) |
| ubah | `database/seeders/RoleMenuSeeder.php` | Tambah `RECEIVABLE_AGING` ke `$access` role yang sudah punya `FAKTUR_*` |
| ubah | `resources/views/layouts/sidebar.blade.php` | Entri `$childIcons['report/receivable-aging']` |
| baru | `tests/Unit/ReceivableAgingTest.php` | Unit test bucket & fallback jatuh tempo (tanpa DB) |

---

## 4. Langkah Eksekusi

### Langkah 1 — Helper aging murni + unit test ✅
- **File:** `app/Support/ReceivableAging.php` (baru), `tests/Unit/ReceivableAgingTest.php` (baru)
- **Perubahan:**
  - `final class ReceivableAging` dengan method statis:
    - `effectiveDueDate(?string $overdueDate, ?string $invoiceDate, ?int $dueDateDuration, Carbon $today): Carbon` — pakai `overdueDate` hanya bila `YEAR` antara 2020–2030; jika tidak, `invoiceDate->copy()->addDays(max((int)$dueDateDuration, 0))`; jika `invoiceDate` kosong → `$today`.
    - `daysToDue(Carbon $due, Carbon $today): int` — `$today->copy()->startOfDay()->diffInDays($due->copy()->startOfDay(), false)` (negatif = sudah lewat; konvensi sama dengan `SupplierPurchaseDetailReport::dataForFilters` L79-83).
    - `bucket(int $daysToDue): string` — `'notDue'` bila > 7, `'dueSoon'` bila 0..7, `'d0_30'` bila −1..−30, `'d31_60'`, `'d61_90'`, `'dOver90'`.
    - `bucketLabel(string $key): string` — label Indonesia (`Belum jatuh tempo`, `Jatuh tempo ≤ 7 hari`, `1–30 hari`, `31–60 hari`, `61–90 hari`, `> 90 hari`).
  - Unit test menembak batas: `daysToDue` = 8/7/0/−1/−30/−31/−60/−61/−90/−91, plus `effectiveDueDate` untuk (a) `overdueDate` valid, (b) `overdueDate` tahun `0026` + `dueDateDuration` 30, (c) semua kosong.
- **Sebelum mulai:** jalankan sampling data untuk memastikan dugaan tanggal rusak:
  `php artisan tinker --execute="dump(DB::table('invoice')->whereNull('overdueDate')->count(), DB::table('invoice')->whereRaw('YEAR(overdueDate) NOT BETWEEN 2020 AND 2030')->whereNotNull('overdueDate')->count());"`
  Catat hasilnya di ringkasan langkah (dipakai untuk Edge Case).
- **Hasil sampling (2026-11-17):** `invoice` = 938 baris; `overdueDate` NULL = **0**; `overdueDate` di luar 2020–2030 = **0**; `customer` aktif = 84; `invoice_payment` (non-deleted) = 735; `invoice_payment_claim` = 11; invoice tanpa `customerCode` = 0. Verifikasi silang `customerRows('outstanding')` = 43 customer dengan total sisa **4.283.891.035** (identik dengan query pembanding `GREATEST(billing - settled, 0)`).
- **Validasi:** `php artisan test --filter=ReceivableAging` lalu `vendor/bin/pint --dirty --format agent`
- **Selesai bila:** semua boundary test hijau dan helper tidak mengimpor model/DB sama sekali.

### Langkah 2 — Export/service class (satu sumber angka) ✅
- **File:** `app/Exports/ReceivableAgingReport.php` (baru)
- **Perubahan:**
  - `public static function invoiceRows(?string $startDate, ?string $endDate, ?string $customerCode, string $status = 'outstanding'): Collection`
    - Query `DB::table('invoice')` + `leftJoin('customer', ...)` + `leftJoinSub` agregat pembayaran dan claim (masing-masing dengan `whereNull('deleted_at')` dan `groupBy('invoiceCode')`), `whereNull('invoice.deleted_at')`.
    - Billing dihitung di SQL: `(COALESCE(invoice.invoiceAmount,0) + COALESCE(invoice.ppnAmount,0) - COALESCE(invoice.pphAmount,0))`.
    - Ambil mentah: `invoice.overdueDate`, `invoice.invoiceDate`, `COALESCE(customer.dueDateDuration,0) as dueDateDuration`, `COALESCE(customer.name, invoice.customerCode) as customerName`.
    - Filter tanggal on `invoice.invoiceDate` (pola `applyDateFilter`: bila `start === end` pakai `= `).
    - Filter `customerCode` bila diisi.
    - Filter `status`: `outstanding` → hanya `remaining > 0`; `overdue` → `remaining > 0` dan hari terlambat ≥ 1; `all` → semua.
    - Setiap baris mendapat `dueDate`, `daysToDue`, `overdueDays = max(-daysToDue, 0)`, `remaining`, `bucket` lewat `ReceivableAging`.
  - `public static function customerRows(...)`: group `invoiceRows()` per `customerCode`, hasilkan `invoiceCount`, `totalBilling`, `totalPaid`, `totalClaim`, `totalRemaining`, `notDueAmount`, `dueSoonAmount`+`dueSoonCount`, `overdueAmount`+`overdueCount`, `aging0To30`, `aging31To60`, `aging61To90`, `agingOver90`, `maxOverdueDays`; urut `customerName`.
  - `public static function invoiceRowsForCustomer(string $customerCode, ...)`: detail diurut `invoiceDate` lalu `daysToDue` menaik.
  - `public function view(): View`: kirim `rows` + `startDate`/`endDate`/`customerName`/`status` ke `report.receivable-aging.report.aging-excel`.
- **Validasi:** `php artisan tinker --execute="dump(\App\Exports\ReceivableAgingReport::customerRows(null,null,null)->take(3)->toArray());"`
- **Selesai bila:** angka `totalRemaining` yang dijumlahkan semua customer **sama** dengan total sisa pada halaman `invoice/unpaid` + `invoice/partial` untuk data yang sama (cek manual di browser), dan tidak ada N+1 query per faktur.

### Langkah 3 — Controller ✅
- **File:** `app/Http/Controllers/Report/ReceivableAgingController.php` (baru)
- **Perubahan:**
  - Properti `$title = 'Laporan Umur Piutang'`, `$view = 'report.receivable-aging.'` (pola `SupplierPurchaseController` L21-29).
  - `index()` — kirim daftar customer (`whereNull('deleted_at')`, `orderBy('name')`) + `summaryStats(customerRows(null,null,null))` ke view index.
  - `datatable(Request $request)` — `abort(404)` bila bukan ajax (pola L49-51); `DataTables::of(customerRows(...))` + `editColumn` format rupiah/hitungan + `addColumn('action')` link ke route detail (bawa `startDate`/`endDate` di query string) + `with(['stats' => ...])` + `rawColumns`.
  - `detail(string $customerCode, Request $request)` — `Customer::where('code',...)->firstOrFail()`, kirim ringkasan + view `show`.
  - `datatableDetail(string $customerCode, Request $request)` — `DataTables::of(invoiceRowsForCustomer(...))`.
  - `excel(Request $request)` — `Excel::download(new ReceivableAgingReport($request), 'AR-Aging-Report.xlsx')`.
  - `pdf(Request $request)` — mPDF `['orientation' => 'L', 'format' => [215, 330], 'tempDir' => storage_path('app/mpdf-temp')]` + view `report.aging-pdf`.
  - `summary(Request $request)` — JSON KPI untuk refresh setelah filter (pola `DashboardController::getOrderStatsByYear` L345-360).
  - Helper privat `formatCurrency`, `summaryStats`, `overdueBadge(int $daysToDue)`, `filterParams(Request)`.
- **Validasi:** `php artisan route:list --name=receivable` (setelah Langkah 4) dan `vendor/bin/pint --dirty --format agent`
- **Selesai bila:** controller tidak menghitung bisnis apa pun sendiri — semua angka berasal dari `ReceivableAgingReport`.

### Langkah 4 — Route ✅
- **File:** `routes/report.php`
- **Perubahan:**
  - Di grup `report` (L11-38):
    - `Route::get('receivable-aging', [ReceivableAgingController::class, 'index'])->name('receivable-aging.index');`
    - `Route::get('receivable-aging/detail/{customerCode}', [ReceivableAgingController::class, 'detail'])->name('receivable-aging.detail');`
    - `Route::get('excel-receivable-aging', [ReceivableAgingController::class, 'excel'])->name('receivable-aging.excel');`
    - `Route::get('pdf-receivable-aging', [ReceivableAgingController::class, 'pdf'])->name('receivable-aging.pdf');`
  - Di grup `datatable` (L40-53): `dt.receivable-aging` dan `dt.receivable-aging-detail/{customerCode}`.
  - Di grup `ajax` (L55-61): `ajax.receivable-aging-summary`.
  - Letakkan sebelum route yang memakai prefix serupa bila ada konflik; path `excel-*`/`pdf-*` sengaja mengikuti pola AP agar konsisten.
- **Validasi:** `php artisan route:list --name=receivable` dan `php artisan route:clear`
- **Selesai bila:** 7 route muncul tanpa error dan `php artisan route:list` tidak menabrak route lain.

### Langkah 5 — View index (KPI + filter + tabel) ✅
- **File:** `resources/views/report/receivable-aging/index.blade.php` (baru)
- **Perubahan:**
  - `@extends('layouts.main', ['title' => $title, 'pageTitle' => $title, 'firstSegment' => 'Laporan', 'secondSegment' => $title])`.
  - Header: judul + subtitle "Pantau sisa piutang, jatuh tempo, dan umur piutang per customer (posisi per hari ini)"; tombol PDF (`#export-pdf`), Excel (`#export-excel`), Refresh (`#btn-refresh-table`).
  - 4 KPI card (`stat-card`): Total Sisa Piutang, Jatuh Tempo ≤ 7 Hari, Sudah Overdue, Aging > 90 Hari — masing-masing dengan id (`#stat-total-remaining`, dst.) agar bisa di-update via `ajax.receivable-aging-summary` saat filter berubah.
  - **Filter card standar** sesuai `phl-table-design` §3 (header collapse + `.filter-collapse-body` + CSS light/dark): `startDate`/`endDate` (Flatpickr `d-m-Y`), `customerCode` (Select2), `status` (Select2: `outstanding`/`overdue`/`all`), tombol Terapkan + Reset. Default periode kosong, status `outstanding`.
  - Tabel `#dtAging` kolom: No, Customer, Jumlah Faktur, Total Tagihan, Sudah Dibayar, **Sisa Piutang**, Belum Jatuh Tempo, Jatuh Tempo ≤ 7 Hari, Overdue, 1–30, 31–60, 61–90, > 90, Aksi. Angka rata kanan, header uppercase (aturan `docs/table-standard.md` L40-60).
  - JS: DataTables server-side `ajax` ke `route('dt.receivable-aging')` + kirim filter; `syncExportUrls()` memperbarui `#export-excel`/`#export-pdf` dengan query string filter; `dataSrc` tetap default; `order` default kolom Sisa Piutang desc.
  - **Wajib** menyertakan CSS dark mode untuk filter card, tabel, dan KPI (skill `phl-table-design` §3 & §6).
- **Validasi:** `php artisan view:clear && php artisan view:cache`
- **Selesai bila:** halaman render tanpa error, filter collapse berfungsi, dan tampilan benar di light + dark mode.

### Langkah 6 — View detail per customer ✅
- **File:** `resources/views/report/receivable-aging/show.blade.php` (baru)
- **Perubahan:**
  - Banner profil customer (pola `phl-table-design` §4): nama, kode, `dueDateDuration` hari, tombol Kembali.
  - 4 KPI kecil: Total Sisa, Overdue, Jatuh Tempo ≤ 7 Hari, Faktur Belum Lunas.
  - Tabel `#dtAgingDetail` kolom: No, No Faktur, Tgl Faktur, Jatuh Tempo, Total Tagihan, Dibayar, Claim, Sisa, Umur (hari), Status (badge: `Belum jatuh tempo` / `Jatuh tempo ≤ 7 hari` / `Overdue N hari`), Aksi (cetak PDF faktur → `route('invoice.pdf', $row->id)`).
  - JS DataTables memakai `route('dt.receivable-aging-detail', $customer->code)` + filter `startDate`/`endDate` yang diteruskan dari query string.
- **Validasi:** `php artisan view:cache`
- **Selesai bila:** klik ikon aksi dari halaman index membuka detail dengan filter periode yang sama, dan badge umur cocok dengan kolom Sisa.

### Langkah 7 — View export (Excel & PDF) ✅
- **File:** `resources/views/report/receivable-aging/report/aging-excel.blade.php`, `resources/views/report/receivable-aging/report/aging-pdf.blade.php` (baru)
- **Perubahan:**
  - Excel: header judul + periode + nama customer (bila difilter), baris header kolom lengkap (customer + semua bucket), baris data, baris total di akhir (`<tfoot>`/baris bold). Tanpa CSS eksternal — `FromView` hanya membaca HTML.
  - PDF: tabel landscape rapi dengan font kecil, kolom sama, footer nomor halaman via `{PAGENO}` mPDF, format rupiah `Rp x.xxx.xxx`.
- **Validasi:** buka `route('report.receivable-aging.excel')` dan `route('report.receivable-aging.pdf')` di browser (`php artisan serve`), lalu `php artisan view:cache`
- **Selesai bila:** file Excel/PDF terunduh, total di export sama dengan total KPI di halaman.

### Langkah 8 — Menu, permission, ikon sidebar ✅
- **File:** `database/seeders/MenuSeeder.php`, `database/seeders/RoleMenuSeeder.php`, `resources/views/layouts/sidebar.blade.php`
- **Perubahan:**
  - `MenuSeeder::$children`: `['code' => 'RECEIVABLE_AGING', 'name' => 'Receivable Aging', 'nama' => 'Umur Piutang', 'parentCode' => 'REPORT', 'url' => 'report/receivable-aging', 'sort' => 6]` (setelah `MAINTENANCE_COMPANY` yang `sort => 5`, L113-121).
  - `RoleMenuSeeder::$access`: tambahkan `'RECEIVABLE_AGING'` ke `SPRUSER`, `DO`, `TRL250604212202`, `TRL250604212231`, `TRL250604212242` (semua role ini sudah punya `FAKTUR_*`). `SPRADMIN` otomatis.
  - `sidebar.blade.php` `$childIcons`: `'report/receivable-aging' => 'mdi-account-cash-outline'`.
  - Jalankan seeder berurutan (wajib, didahului backup `role_menu`):
    - `mysqldump` / `php artisan tinker --execute="dump(DB::table('role_menu')->count());"`
    - `php artisan db:seed --class=MenuSeeder`
    - `php artisan db:seed --class=RoleMenuSeeder`
    - `php artisan view:clear && php artisan config:clear && php artisan route:clear`
- **Validasi:** `php artisan tinker --execute="dump(DB::table('menu')->where('code','RECEIVABLE_AGING')->first(), DB::table('role_menu')->where('menuCode','RECEIVABLE_AGING')->count());"` dan login sebagai `SPRUSER` untuk melihat menu muncul.
- **Selesai bila:** menu "Umur Piutang" tampil di grup Laporan untuk role yang ditentukan, dan tidak ada menu lain yang hilang (bandingkan jumlah baris `role_menu` sebelum/sesudah: harus +1 per role yang ditambah).
- **Hasil (2026-11-17):** `role_menu` sebelum = **291** baris (SPRADMIN 67, SPRUSER 60, DO 41, TRL250604212202 44, TRL250604212231 38, TRL250604212242 41); sesudah = **298** baris (SPRADMIN 68, SPRUSER 61, DO 42, TRL250604212202 45, TRL250604212231 40, TRL250604212242 42). Diff terhadap snapshot sebelum seeding (dibaca dari binlog MySQL): **7 baris ditambah, 0 baris hilang** — 6 baris `RECEIVABLE_AGING` (5 role + SPRADMIN) dan 1 baris parent `REPORT` untuk TRL250604212231 (wajib agar menu child muncul). `menu` = **68** baris.
- **Temuan saat seeding:** `MenuSeeder`/`RoleMenuSeeder` ternyata ketinggalan 3 menu yang masih dipakai fitur dan sempat terhapus oleh penulisan ulang: `FAKTUR_PEMBAYARAN_SEBAGIAN` (`invoice/partial`), `VENDOR_INV_PARTIAL` (`vendor/invoice/partial`), dan `DIRECT_PURCHASE` (`purchasing/direct-purchase`, akses 5 role). Semua dikembalikan ke `$children`/`$access` (Faktur partial sort 3, Vendor partial sort 3, `FAKTUR_LUNAS`/`VENDOR_INV_PAID` digeser ke 4, dst.). Diverifikasi: tidak ada baris `role_menu` lama yang hilang dan ketiga menu tampil lagi di sidebar `SPRUSER`.

### Langkah 9 — Uji end-to-end manual (data nyata) ✅
- **File:** tidak ada perubahan kode (checklist eksekusi)
- **Perubahan:** jalankan skenario berikut di browser:
  1. Buka `report/receivable-aging` tanpa filter → KPI Total Sisa = penjumlahan kolom Sisa Piutang semua baris.
  2. Filter customer yang punya faktur `STATUS_PARTIAL` (`invoice.partial`) → angka Sisa baris tersebut = Sisa di halaman `invoice/partial` untuk faktur yang sama.
  3. Filter periode 1 bulan → hanya faktur dengan `invoiceDate` di rentang itu; pastikan KPI berubah bersamaan (dari `ajax.receivable-aging-summary`).
  4. Buka detail customer → badge umur konsisten dengan tanggal jatuh tempo; buka satu PDF faktur dari detail.
  5. Unduh Excel & PDF → total cocok dengan KPI.
  6. Aktifkan dark mode → filter card, tabel, KPI terbaca.
- **Validasi:** `php artisan test` (seluruh suite) dan `vendor/bin/pint --dirty --format agent`
- **Selesai bila:** keenam skenario lulus dan tidak ada error di `storage/logs/laravel.log`.
- **Hasil uji (2026-11-17):** semua endpoint (index, datatable, detail, datatable detail, summary) = HTTP 200; KPI total sisa **4.283.891.035** sama dengan penjumlahan kolom; 11 faktur `partial` dicocokkan dengan `InvoiceService::findPartial()` → **0 mismatch**; filter periode Agustus 2026 → 8 customer / 256.599.321 dan sama antara DataTable dan `ajax.summary`; detail menampilkan badge umur konsisten (mis. 48 hari untuk jatuh tempo 30-09-2026) dan PDF faktur = 200 `%PDF`; Excel 11.689 byte (`PK`) dan PDF 98.088 byte (`%PDF`); dark mode: filter card, tabel, dan KPI terbaca (0 error console). `php artisan test` = **43 passed**; `vendor/bin/pint --dirty` hanya merapikan format controller.

### Langkah 10 — Tutup pekerjaan ✅
- **File:** `docs/plans/ar-aging-report.md`
- **Perubahan:** tandai setiap langkah `✅` pada file rencana ini (satu-satunya edit yang diizinkan), catat hasil sampling `overdueDate` dari Langkah 1 dan jumlah baris `role_menu` sebelum/sesudah Langkah 8.
- **Validasi:** `git --no-pager diff --stat` (pastikan hanya file yang terdaftar di §3 yang berubah)
- **Selesai bila:** diff bersih dari file tak terduga dan tidak ada file sisa debug (`dump`, `dd`, review manual).

---

## 5. Edge Case yang Wajib Ditangani

- **`overdueDate` NULL / tahun rusak** (mis. `0026`): jatuh tempo dihitung dari `invoiceDate + customer.dueDateDuration`; bila `dueDateDuration` NULL/0 → jatuh tempo = `invoiceDate` (faktur langsung masuk bucket sesuai umur).
- **`invoiceDate` NULL/kosong**: pakai `today` sebagai basis, ditandai umur 0 (tidak crash, tidak masuk `dOver90`).
- **Overpay / claim > tagihan**: `remaining = max(billing - paid - claim, 0)` → tidak pernah negatif, dan faktur keluar dari `outstanding`.
- **Soft delete**: faktur, pembayaran, dan claim yang `deleted_at` tidak NULL tidak dihitung (konsisten dengan `InvoiceController::paymentStateStats` L56-60).
- **Customer terhapus**: baris tetap muncul dengan nama fallback `customerCode` (pakai `COALESCE(customer.name, invoice.customerCode)`), tidak di-join-kan hilang oleh `whereNull` pada tabel customer.
- **Faktur dengan `ppnAmount`/`pphAmount` NULL**: diperlakukan 0.
- **Faktur bernilai 0** (mis. dibuat lalu dibatalkan sebelum ada detail): tidak muncul di `outstanding`, tapi muncul di `all`.
- **Filter `startDate` = `endDate`**: pakai `whereDate('invoice.invoiceDate', '=', $tanggal)` (meniru `applyDateFilter`), bukan `BETWEEN` yang bisa memotong jam.
- **Timezone**: "hari ini" harus dari `Carbon::today()` (timezone aplikasi `Asia/Jakarta`), bukan `CURRENT_DATE` MySQL yang bergantung timezone server.
- **Angka besar**: semua nominal `float` di backend tapi dirender dengan `number_format(..., 0, ',', '.')`; jangan pakai `intval` untuk total agregat.
- **Footer DataTables**: `Sisa Piutang` di kolom tabel diurutkan sebagai teks berformat ("Rp 1.234") → set `columnDefs` `type: 'num'` atau kirim nilai mentah pada `data-order` agar sorting benar.

---

## 6. Risiko & Mitigasi

| Risiko | Dampak | Mitigasi |
|---|---|---|
| `RoleMenuSeeder` menulis ulang `role_menu` dari daftar hardcode, sehingga perubahan permission manual di produksi hilang | User kehilangan akses tak terduga | Backup `role_menu` (dump) sebelum seed, bandingkan jumlah baris per role, dan tambahkan `RECEIVABLE_AGING` ke **semua** role yang punya `FAKTUR_*` agar tidak ada yang justru kehilangan akses lain |
| Angka aging tidak cocok dengan halaman `invoice/unpaid` + `invoice/partial` | Laporan tidak dipercaya, tim finance balik pakai Excel manual | Satu sumber rumus (`ReceivableAgingReport`) + verifikasi silang Langkah 9 skenario 2 |
| Volume `invoice` besar → agregasi PHP berat | Halaman lambat/timeout | Default hanya `outstanding`, filter periode/customer, agregasi pembayaran & claim di SQL (`leftJoinSub`), dan **jangan** memuat relasi Eloquent per faktur. Bila > 50k baris, pindahkan bucket ke SQL (dokumentasikan saat itu) |
| `overdueDate` di data lama tidak wajar (pola sama dengan `purchase.dueDate`) | Bucket umur ngawur (mis. > 90 hari padahal baru) | Guard `YEAR BETWEEN 2020 AND 2030` + fallback `dueDateDuration`; sampling di Langkah 1 dicatat |
| Definisi "overdue" berbeda antara laporan AR baru dan halaman invoice | Dua angka berbeda untuk hal yang sama | Overdue = hari terlambat ≥ 1 (bukan ≥ 0); faktur jatuh tempo hari ini masuk `dueSoon`, sama seperti AP |
| mPDF gagal karena direktori temp tidak ada | Export PDF error 500 | Pastikan `storage/app/mpdf-temp` ada (`mkdir` bila perlu) sebelum Langkah 7 |
| Test suite menyentuh DB dev (phpunit tanpa SQLite) | Test menghapus/mengotori data dev | Test yang ditulis hanya unit test tanpa DB; `php artisan test` dijalankan dengan `--filter` lebih dulu sebelum suite penuh |

---

## 7. Rollback

1. Hapus 7 route di `routes/report.php` (grup `report`, `dt`, `ajax`) → `php artisan route:clear`.
2. Hapus file baru: `app/Support/ReceivableAging.php`, `app/Exports/ReceivableAgingReport.php`, `app/Http/Controllers/Report/ReceivableAgingController.php`, `tests/Unit/ReceivableAgingTest.php`, folder `resources/views/report/receivable-aging/`.
3. Kembalikan `database/seeders/MenuSeeder.php`, `database/seeders/RoleMenuSeeder.php`, `resources/views/layouts/sidebar.blade.php` dari git (`git checkout -- <path>`).
4. Hapus baris menu & permission (aman, tanpa menyentuh data transaksi):
   `DELETE FROM role_menu WHERE menuCode = 'RECEIVABLE_AGING'; DELETE FROM menu WHERE code = 'RECEIVABLE_AGING';`
5. `php artisan view:clear && php artisan route:clear && php artisan config:clear`.
6. Tidak ada perubahan skema database dan tidak ada data transaksi yang ditulis → rollback tidak berisiko kehilangan data operasional.

---

## 8. Di Luar Scope

- Export Excel/PDF untuk halaman **detail per customer** (D8) — menyusul bila diminta.
- Laporan Kas & Bank per periode, laporan order outstanding, rekap pajak PPN/PPh, laporan stok — kandidat iterasi berikutnya dari analisis `/grill-me`, sengaja tidak dikerjakan sekarang (Q2=A).
- Widget AR aging di dashboard `dashboard/home` — belum.
- Notifikasi/reminder penagihan (WhatsApp/Telegram/email) dan fitur kirim statement ke customer.
- Perubahan rumus tagihan, status faktur, atau alur pembayaran (`invoice/payment-transaction`) — tidak disentuh.
- Perbaikan bug `DashboardController.php` L160-163 (`$purchases` tidak terdefinisi, grafik pembelian bulanan selalu 0) — dicatat sebagai temuan terpisah, bukan bagian rencana ini.
