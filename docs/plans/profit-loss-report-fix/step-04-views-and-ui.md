# Step 4: Redesign & Bahasa Indonesia View `index.blade.php` dan `show.blade.php`

> **Referensi Plan:** [../profit-loss-report-fix.md](../profit-loss-report-fix.md)  
> **Prasyarat:** [step-02-profit-loss-controller.md](./step-02-profit-loss-controller.md) selesai  
> **Status:** `done`

## Tujuan Langkah
Menerjemahkan seluruh antarmuka web Laporan Laba Rugi (halaman Index dan Show) ke Bahasa Indonesia, mengganti kolom/filter `Fleet Type` menjadi `Perusahaan Kendaraan`, memunculkan kolom & ringkasan `Pajak (PPh)` serta `Laba Bersih`, memperbaiki path asset & opsi DataTables yang bermasalah, dan menerapkan standar UI PHL (`phl-table-design`).

## Daftar File yang Disentuh
- `[MODIFY]` `resources/views/report/profit-loss/index.blade.php`
- `[MODIFY]` `resources/views/report/profit-loss/show.blade.php`

## Action Checklist & Detail Perubahan

### A. Halaman Index (`resources/views/report/profit-loss/index.blade.php`)
- [x] Ubah layout props menjadi `firstSegment => 'Laporan'`, `secondSegment => $title`.
- [x] Perbaiki path asset (`asset('assets/css/vendors/sweetalert2.css')`, `asset('assets/js/sweet-alert/sweetalert.min.js')`).
- [x] Terapkan standar `phl-table-design` (§2, §3, §6, §7, §8):
  - Kontainer `.card.border-0.shadow-sm` (`border-radius: 16px`), header judul + deskripsi singkat Bahasa Indonesia, dan tombol Export Excel (`#export-excel`).
  - Panel filter `.filter-card` tertutup default tepat di atas tabel berisi `#filterForm` (`<div>`, bukan `<form>`) dengan kontrol tinggi seragam 38px & blok CSS dark mode `html[data-bs-theme="dark"]` lengkap:
    - `Plat Nomor` (`select2-filter`, `name="plateNumber"`)
    - `Perusahaan Kendaraan` (`select2-filter`, `name="fleetCompanyCode"`)
    - `Dari Tanggal` (`#startDate`, Flatpickr `Y-m-d`)
    - `Sampai Tanggal` (`#endDate`, Flatpickr `Y-m-d`)
    - Tombol `Terapkan Filter` (`.btn-filter-primary`) & `Reset` (`.btn-filter-reset`).
  - Kartu ringkasan KPI / baris `<tfoot>` **Jumlah Total** (menampilkan total `Penjualan Dasar`, `Biaya Order`, `Biaya Perawatan`, `Pajak (PPh)`, dan `Laba Bersih` sesuai filter aktif dari respons AJAX DataTables).
  - Tabel `#dt` (hapus `table-striped`, header uppercase `#f8fafc`, dark mode support):
    - Grup header: `Data Kendaraan` (`colspan="4"`), `Penjualan (A)`, `Biaya (B)` (`colspan="2"`), `Pajak (C)`, `Laba Bersih (A - B - C)`.
    - Kolom: `Aksi`, `No`, `Plat Nomor`, `Perusahaan Kendaraan`, `Penjualan Dasar`, `Biaya Order`, `Biaya Perawatan`, `Pajak (PPh)`, `Laba Bersih`.
    - Kolom nominal menggunakan `text-end align-middle font-monospace` dengan prefix `Rp `.
  - DataTables Bahasa Indonesia (`pageLength: 25`, `syncExportUrls()` saat init dan reload).

### B. Halaman Detail (`resources/views/report/profit-loss/show.blade.php`)
- [x] Ubah `firstSegment => 'Laporan'`, `secondSegment => 'Detail Laba Rugi'` (menggantikan `__('general.add')`).
- [x] Hapus duplikasi tag `<link>` DataTables dan ubah path relatif asset ke `asset(...)`.
- [x] Terapkan `phl-table-design` §3, §4, §5, §6:
  - Entity Profile Banner (`.avatar-badge-entity`): tampilkan `Plat Nomor`, badge tipe (`Internal` / `External`), nama `Perusahaan Kendaraan` (`$data->company?->name ?? '-'`), dan kode armada.
  - Filter Card Collapse tanggal (`#startDate`, `#endDate` Flatpickr `Y-m-d` + tombol Terapkan & Reset + blok dark mode).
  - Kartu Ringkasan KPI (5 metrik dalam grid responsif): `Penjualan Dasar`, `Biaya Order`, `Biaya Perawatan`, `Pajak (PPh)`, dan `Laba Bersih` (dengan kelas `.basicSales`, `.orderCost`, `.maintenance`, `.taxAmount`, `.netProfit` yang diperbarui saat filter tanggal AJAX sukses).
  - Tabel `Rincian Biaya Order` (`#dt-order`: `No`, `Tanggal Order`, `No. Shipment`, `Biaya`) dan `Rincian Perawatan` (`#dt-maintenance`: `No`, `Kode`, `Tanggal`, `Item`, `Biaya`) menggunakan styling tabel PHL + dark mode, `order: [[1, 'desc']]` / `[[2, 'desc']]`, dan DataTables `language` Bahasa Indonesia.

## Kriteria Selesai & Verifikasi Langkah
- Seluruh teks UI di `index.blade.php` dan `show.blade.php` berbahasa Indonesia.
- Tidak ada `table-striped`, `<input type="date">` native, maupun `../assets/...`.
- Blok `html[data-bs-theme="dark"]` mencakup panel filter, kontrol, header/isi tabel, dan kartu summary.
