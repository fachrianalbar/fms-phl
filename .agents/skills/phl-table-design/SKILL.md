---
name: phl-table-design
description: Standard UI design system for B2B data tables, reports, filter bars, KPI summary cards, detail pages, and modal dialogs in the PHL project (Bootstrap 5, DataTables, Flatpickr, Select2). Includes the canonical filter-card standard (light + dark mode) referenced from operational/not-return-do and modal standards. Use this skill whenever the user says "perbaiki design", "perbaiki tampilan", "standarisasi tabel", "desain table", "perbaiki filter", "standarisasi filter", "desain filter", "filter masih putih", "filter tidak muncul di dark mode", "perbaiki modal", "standarisasi modal", or asks to redesign/improve any report, table, filter, or modal dialog.
---

# PHL B2B Table & Report Design Standard

Gunakan skill ini sebagai acuan standar mutlak setiap kali user meminta:
- "perbaiki design" / "perbaiki tampilan"
- "desain table nya buat yang bagus" / "standarisasi tabel"
- Pembuatan atau refactoring halaman Report (Index & Detail)
- Styling ulang halaman berbasis Bootstrap 5 + DataTables

Standar referensi visual utama proyek ini:
- `resources/views/report/driver-salary/index.blade.php`
- `resources/views/report/maintenance-fleet/index.blade.php`
- `resources/views/report/maintenance-fleet/show.blade.php`
- `resources/views/operational/not-return-do/index.blade.php` ← **referensi FILTER (standar filter card, lihat §3)**
- `docs/table-standard.md`

---

## 1. Prinsip Utama (Anti-Default Discipline)

1. **JANGAN gunakan `table-striped` default.** Gunakan garis batas tipis (`#f1f5f9`), rounded corner (`12px`), dan hover state yang halus (`#f8fafc`).
2. **Gunakan filter card collapse yang rapi.** Filter boleh tertutup secara default agar halaman tabel lebih ringkas, tetapi header toggle harus selalu terlihat, mudah dipahami, dan menggunakan background soft (`#f8fafc`).
3. **JANGAN gunakan `<input type="date">` biasa.** Selalu gunakan Flatpickr dengan format standar backend `Y-m-d`.
4. **JANGAN biarkan angka nominal/kuantitas rata kiri.** Selalu gunakan `text-end` dan font monospace/tabular-nums.
5. **Gunakan kartu KPI (Summary Tiles) bergradasi lembut** di halaman detail atau agregasi (Total Transaksi, Total Qty, Total Biaya/Gaji).
6. **Selalu sinkronkan parameter filter ke tombol Export (Excel & PDF)** agar file yang diunduh sesuai dengan data yang sedang difilter.
7. **Standarisasi tinggi elemen filter bar secara seragam (38px).** JANGAN biarkan tinggi Select2 (38px), input Flatpickr (~31px dari `form-control-sm`), dan tombol filter (~31px dari `btn-sm`) berbeda tinggi/belang-belang. Terapkan kelas terstandarisasi `.filter-control`, `.filter-label`, `.btn-filter-primary`, dan `.btn-filter-reset` (kotak 38px x 38px) agar tampilan presisi, rapi, dan seimbang.

---

## 2. Struktur Markup Kontainer Utama

```blade
<div class="col-sm-12">
    <div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
        {{-- Card Header --}}
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center"
            style="border-color: #e2e8f0;">
            <div>
                <h4 class="mb-1 fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="mdi mdi-[icon-name] text-primary fs-20"></i>
                    {{ $title }}
                </h4>
                <small class="text-muted">Deskripsi ringkas mengenai data yang disajikan</small>
            </div>

            <div class="d-flex align-items-center gap-2">
                {{-- Tombol Export Excel --}}
                <a href="{{ $excelUrl }}" target="_blank" id="export-excel"
                    class="btn btn-icon btn-sm bg-success-subtle" data-bs-toggle="tooltip" title="Export Excel">
                    <i class="mdi mdi-file-excel fs-14 text-success"></i>
                </a>

                {{-- Tombol Export PDF --}}
                <a href="{{ $pdfUrl }}" target="_blank" id="export-pdf"
                    class="btn btn-icon btn-sm bg-danger-subtle" data-bs-toggle="tooltip" title="Export PDF">
                    <i class="mdi mdi-file-pdf-box fs-14 text-danger"></i>
                </a>

                {{-- Tombol Kembali (untuk halaman detail) --}}
                @if (isset($backUrl))
                    <a href="{{ $backUrl }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1"
                        style="border-radius: 8px; font-weight: 600; padding: 7px 14px;">
                        <i class="mdi mdi-arrow-left"></i> {{ __('general.back_to_list') }}
                    </a>
                @endif
            </div>
        </div>

        <div class="card-body p-4">
            @include('partials.alert')

            {{-- Filter Bar --}}
            ...

            {{-- Profile Banner (jika detail) --}}
            ...

            {{-- KPI Summary Cards (jika agregasi) --}}
            ...

            {{-- Table --}}
            <div class="table-responsive custom-scrollbar">
                <table class="table align-middle w-100 mb-0" id="[table-id]">
                    <thead>...</thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
```

---

## 3. Komponen Filter Card Collapse Terstandarisasi

> **Standar referensi visual (mutlak):** `resources/views/operational/not-return-do/index.blade.php`
> — CSS terang `~L281–368`, CSS gelap `~L471–506`, markup `~L561–661`, JS `~L958–1143`.
> Setiap filter baru / perbaikan filter di halaman mana pun **wajib** mengikuti anatomi, ukuran, dan perilaku persis seperti halaman referensi ini, **termasuk dark mode**.

Filter ditempatkan tepat di atas tabel di dalam `.card-body`, sebagai panel collapse yang **tertutup secara default**. Header panel selalu terlihat sebagai trigger sehingga halaman tidak kehilangan akses ke filter. Gunakan `data-bs-toggle="collapse"`, `aria-expanded="false"`, dan ID target yang unik per halaman. Seluruh elemen input (Select2, Flatpickr) dan tombol aksi (Terapkan & Reset) **wajib seragam 38px tinggi dan radius 8px**.

### Anatomi standar (urutan wajib)

1. **Panel** — background `#f8fafc`, border `1px #e2e8f0`, radius `12px`, `margin: 0 0 20px`, `overflow: hidden`.
2. **Header button** — full width, `padding: 12px 16px`, flex space-between, hover `#f1f5f9`.
   - Heading: ikon `mdi-filter-variant` (#4f46e5, 17px) + `<strong>Filter Data</strong>` (13px/700) + `<small>` hint (11px, #94a3b8).
   - Chevron `mdi-chevron-down` yang berotasi 180° saat `[aria-expanded="true"]`.
3. **Collapse body** — border-top `1px #e2e8f0`, padding `16px`; `#filterForm` berisi `.row.g-3` (`--bs-gutter-y: .75rem`).
4. **Field** — `col-xl-3 col-md-6` untuk dropdown/date, label `.filter-label` + kontrol 38px.
5. **Aksi** — kolom terakhir `col-xl-6 col-md-4 d-flex align-items-end justify-content-md-end gap-2` berisi tombol Terapkan Filter + Reset.

### Markup

```blade
<div class="card-body pt-3 pb-0">
    <div class="filter-card">                        {{-- panel --}}
        <button type="button" class="filter-card-header" data-bs-toggle="collapse"
            data-bs-target="#uniqueFilterCollapse" aria-expanded="false" aria-controls="uniqueFilterCollapse">
            <span class="filter-card-heading">
                <i class="mdi mdi-filter-variant"></i>
                <strong>Filter Data</strong>
                <small>Gunakan filter untuk mempersempit daftar</small>
            </span>
            <i class="mdi mdi-chevron-down filter-card-chevron"></i>
        </button>

        <div class="collapse filter-collapse" id="uniqueFilterCollapse">
            <div class="filter-collapse-body">
                <div id="filterForm">
                    <div class="row g-3">
                        <div class="col-xl-3 col-md-6">
                            <label class="filter-label" for="fieldName">Label Dropdown</label>
                            <select class="form-select select2-filter" name="fieldName" id="fieldName">
                                <option value="">Semua Opsi</option>
                                @foreach ($options as $item)
                                    <option value="{{ $item->code }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <label class="filter-label" for="startDate">Dari Tanggal</label>
                            <input class="form-control filter-control" name="startDate" id="startDate"
                                type="text" placeholder="Pilih tanggal mulai">
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <label class="filter-label" for="endDate">Sampai Tanggal</label>
                            <input class="form-control filter-control" name="endDate" id="endDate"
                                type="text" placeholder="Pilih tanggal akhir">
                        </div>

                        <div class="col-xl-3 col-md-6 d-flex align-items-end justify-content-md-end gap-2">
                            <button class="btn btn-filter-primary flex-grow-1 flex-md-grow-0" type="button" id="btnFilter">
                                <i class="mdi mdi-filter-outline"></i> Terapkan Filter
                            </button>
                            <button class="btn btn-filter-reset" type="button" id="btnResetFilter"
                                data-bs-toggle="tooltip" title="Reset Filter">
                                <i class="mdi mdi-refresh"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
```

Catatan markup:
- `#filterForm` adalah `<div>`, **bukan** `<form>`, dan tombol Terapkan bertipe `type="button"` — handler diikat ke event `click` (bukan `submit`) agar Enter di input tidak memicu reload halaman.
- Jangan gunakan `.form-control-sm` / `.btn-sm` — tingginya ~31px dan membuat baris filter belang-belang. Semua kontrol wajib 38px.
- Jika halaman memakai prefix kelas sendiri (mis. `.not-return-do-filter*`), nilainya harus **identik** dengan standar ini.

### CSS terang (copy-paste)

```css
.filter-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    margin: 0 0 20px;
    overflow: hidden;
}
.filter-card-header {
    align-items: center;
    background: #f8fafc;
    border: 0;
    color: #334155;
    display: flex;
    justify-content: space-between;
    padding: 12px 16px;
    text-align: left;
    width: 100%;
}
.filter-card-header:hover { background: #f1f5f9; }
.filter-card-heading { align-items: center; display: flex; gap: 8px; }
.filter-card-heading i { color: #4f46e5; font-size: 17px; }
.filter-card-heading strong { font-size: 13px; font-weight: 700; }
.filter-card-heading small { color: #94a3b8; font-size: 11px; font-weight: 400; }
.filter-card-chevron { transition: transform .2s ease; }
.filter-card-header[aria-expanded="true"] .filter-card-chevron { transform: rotate(180deg); }
.filter-card .filter-collapse { border-top: 1px solid #e2e8f0; }
.filter-card .filter-collapse-body { padding: 16px; }
.filter-card .row { --bs-gutter-y: .75rem; }

.filter-label { color: #64748b; display: block; font-size: 12px; font-weight: 600; margin-bottom: 6px; }
.filter-control,
.filter-card .form-control {
    background-color: #fff !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 8px !important;
    color: #334155 !important;
    font-size: 13px !important;
    height: 38px !important;
}
.filter-control { padding: 0 12px 0 36px !important; }
.filter-card .form-control[name="shipmentNumber"] { padding-left: 12px !important; }

.btn-filter-primary {
    align-items: center;
    background: linear-gradient(135deg, #4f46e5, #6366f1) !important;
    border: 0 !important;
    border-radius: 8px !important;
    color: #fff !important;
    display: inline-flex;
    font-size: 13px;
    font-weight: 600;
    gap: 6px;
    height: 38px;
    justify-content: center;
    padding: 0 16px;
    white-space: nowrap;
}
.btn-filter-reset {
    align-items: center;
    background: #fff !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 8px !important;
    color: #64748b !important;
    display: inline-flex;
    height: 38px;
    justify-content: center;
    min-width: 38px;
    padding: 0 !important;
}

.select2-container { width: 100% !important; }
.select2-container--default .select2-selection--single {
    align-items: center;
    border: 1px solid #cbd5e1 !important;
    border-radius: 8px !important;
    display: flex;
    height: 38px !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #334155 !important;
    font-size: 13px;
    line-height: 36px;
    padding-left: 12px;
}
.select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }
```

### CSS gelap (WAJIB — jangan pernah dilewatkan)

> **Aturan keras:** setiap filter **wajib** menyertakan blok `html[data-bs-theme="dark"]` di bawah ini. Tanpa blok ini, panel filter tetap putih/terang di dark mode — bug yang paling sering muncul. Selalu scope di bawah `html[data-bs-theme="dark"]` dan jangan mengubah mode terang.

```css
html[data-bs-theme="dark"] .filter-card { background: var(--bs-secondary-bg); border-color: var(--bs-border-color); }
html[data-bs-theme="dark"] .filter-card-header { background: var(--bs-secondary-bg); color: var(--bs-body-color); }
html[data-bs-theme="dark"] .filter-card-header:hover { background: var(--bs-tertiary-bg); }
html[data-bs-theme="dark"] .filter-card .filter-collapse { border-top-color: var(--bs-border-color); }
html[data-bs-theme="dark"] .filter-label { color: var(--bs-secondary-color); }
html[data-bs-theme="dark"] .filter-control,
html[data-bs-theme="dark"] .filter-card .form-control {
    background-color: var(--bs-tertiary-bg) !important;
    border-color: var(--bs-border-color) !important;
    color: var(--bs-body-color) !important;
}
html[data-bs-theme="dark"] .btn-filter-reset {
    background: var(--bs-tertiary-bg) !important;
    border-color: var(--bs-border-color) !important;
    color: var(--bs-body-color) !important;
}
```

Catatan dark mode:
- `.btn-filter-primary` memakai gradient ungu — aman di kedua mode, **tidak perlu** override gelap.
- Kotak Select2 sudah ditangani global di `public/assets/css/dark-mode-overrides.css`; cukup andalkan token, jangan dobel-style.
- **Jangan** menulis `border: … !important` inline di blade. Inline `!important` mengalahkan semua stylesheet (termasuk `!important`), sehingga dark mode tidak bisa menimpanya. Set border lewat CSS/kelas atau gunakan `var(--bs-border-color)`.
- Token dark mode proyek: `--bs-secondary-bg` (#1f2028), `--bs-tertiary-bg` (#282e39), `--bs-body-color`, `--bs-border-color`.

### JavaScript behavior

```javascript
$('.select2-filter').select2({ placeholder: 'Semua pilihan', allowClear: true, width: '100%' });

let startPicker, endPicker;
startPicker = flatpickr('#startDate', {
    dateFormat: 'Y-m-d', allowInput: true,
    onChange: function (d, dateStr) { if (endPicker) endPicker.set('minDate', dateStr || null); }
});
endPicker = flatpickr('#endDate', {
    dateFormat: 'Y-m-d', allowInput: true,
    onChange: function (d, dateStr) { if (startPicker) startPicker.set('maxDate', dateStr || null); }
});

function getFilters() {
    return { /* satu key per field filter, '' bila kosong */ };
}

function syncExportUrls() {
    const params = new URLSearchParams();
    Object.entries(getFilters()).forEach(function (entry) {
        if (entry[1]) params.set(entry[0], entry[1]);
    });
    const query = params.toString() ? '?' + params.toString() : '';
    $('#export-excel').attr('href', '{{ route('...export-excel') }}' + query);
    $('#export-pdf').attr('href', '{{ route('...export-pdf') }}' + query);
}

function reloadWithFilters() { syncExportUrls(); table.ajax.reload(null, true); }

$('#btnFilter').on('click', reloadWithFilters);
$('#searchField').on('keydown', function (e) {
    if (e.key === 'Enter') { e.preventDefault(); reloadWithFilters(); }
});
$('#btnResetFilter').on('click', function () {
    $('#filterForm').find('input').val('');
    $('.select2-filter').val('').trigger('change');
    startPicker.clear();
    endPicker.clear();
    startPicker.set('maxDate', null);
    endPicker.set('minDate', null);
    reloadWithFilters();
});

syncExportUrls(); // sinkron saat halaman pertama dimuat
```

Perilaku wajib:
- DataTables `ajax.data` harus `Object.assign(d, getFilters())` agar parameter filter terkirim ke server.
- Export Excel/PDF selalu disinkronkan dengan filter aktif via `syncExportUrls()`, termasuk saat halaman pertama dimuat.
- Reset wajib: kosongkan input, reset Select2 (`.val('').trigger('change')`), `clear()` kedua picker, hapus batas `minDate`/`maxDate`, sinkron export, lalu reload.
- Picker dua arah dideklarasikan dalam scope yang sama agar callback bisa mengatur `minDate`/`maxDate`.
- Jangan menginisialisasi ulang DataTables saat panel dibuka/ditutup — cukup `ajax.reload`.

### Checklist penerapan filter (per halaman)

- [ ] Panel `.filter-card` di dalam `.card-body` tepat di atas tabel; collapse tertutup default; header tetap terlihat.
- [ ] Semua kontrol 38px / radius 8px; tidak ada `.form-control-sm` / `.btn-sm`.
- [ ] Tombol Terapkan = `.btn-filter-primary`; Reset = `.btn-filter-reset` (kotak 38×38, ikon `mdi-refresh`).
- [ ] Blok `html[data-bs-theme="dark"]` untuk panel, header, label, kontrol, dan tombol reset sudah ada.
- [ ] `getFilters()` mengembalikan semua field; `ajax.data` merge filter.
- [ ] `syncExportUrls()` dipanggil saat init dan tiap reload.
- [ ] Tidak ada `border … !important` inline di elemen filter.
- [ ] Diuji di mode terang **dan** gelap.

### Prompt siap pakai — perbaiki filter (konsisten)

Cukup ganti `[LINK ROUTE]` dengan URL modul yang mau diperbaiki, lalu kirim. Prompt satu paragraf ini mengunci semua perbaikan filter ke standar §3.

```text
Perbaiki filter di halaman [LINK ROUTE] agar konsisten dengan standar filter PHL (skill `phl-table-design` §3, referensi visual `resources/views/operational/not-return-do/index.blade.php`): jadikan panel collapse `.filter-card` tertutup default tepat di atas tabel (header `.filter-card-header` dengan ikon `mdi-filter-variant` + "Filter Data" + hint + chevron yang berotasi saat terbuka) berisi `.filter-collapse` > `.filter-collapse-body` > `#filterForm` (DIV, bukan `<form>`) > `.row.g-3` dengan field `col-xl-3 col-md-6` dan label `.filter-label`; semua kontrol 38px/radius 8px tanpa `.form-control-sm`/`.form-select-sm`/`.btn-sm`, date pakai Flatpickr format `Y-m-d`; tombol Terapkan `.btn-filter-primary` (ikon `mdi-filter-outline`) dan Reset `.btn-filter-reset` (kotak 38×38, ikon `mdi-refresh`); WAJIB sertakan blok `html[data-bs-theme="dark"]` untuk panel/header/label/kontrol/tombol reset memakai token `--bs-secondary-bg`/`--bs-tertiary-bg`/`--bs-body-color`/`--bs-border-color` dan hapus `border … !important` inline bila ada; JS pertahankan field & endpoint yang ada (`getFilters()` → `ajax.data`, `syncExportUrls()` ke `#export-excel`/`#export-pdf` dipanggil saat init + tiap reload, Terapkan & Enter → reload, Reset bersihkan input + Select2 + Flatpickr + min/maxDate lalu reload); jangan ubah logika backend, kolom DataTable, nama field, atau route, dan jangan sentuh halaman lain; setelah selesai jalankan checklist §3 dan konfirmasi sudah dicek di mode terang & gelap.
```

---

## 4. Banner Profil Entitas (Untuk Halaman Detail)

Gunakan banner di atas ringkasan metrik detail:

```blade
<div class="card border-0 mb-4"
    style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 12px;">
    <div class="card-body p-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="avatar-badge-entity">
                    <i class="mdi mdi-truck fs-22 text-white"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="fs-16 fw-bold text-dark font-monospace">{{ $entity->identifier }}</span>
                        <span class="badge bg-primary-subtle text-primary px-2 py-1"
                            style="border-radius: 6px; font-weight: 600; font-size: 11px;">
                            {{ $entity->type ?? 'Internal' }}
                        </span>
                    </div>
                    <div class="text-muted small mt-1">
                        <i class="mdi mdi-office-building me-1"></i>{{ $entity->name }}
                        <span class="mx-2">•</span>
                        <i class="mdi mdi-identifier me-1"></i>Kode: <span class="font-monospace">{{ $entity->code }}</span>
                    </div>
                </div>
            </div>

            @if ($startDate || $endDate)
                <div class="d-flex align-items-center gap-2 bg-light px-3 py-2 rounded-3 border" style="font-size: 12.5px;">
                    <i class="mdi mdi-calendar-range text-primary fs-16"></i>
                    <span>Filter Periode: <strong>{{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d/m/Y') : 'Awal' }}</strong> s/d <strong>{{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d/m/Y') : 'Sekarang' }}</strong></span>
                </div>
            @endif
        </div>
    </div>
</div>
```

---

## 5. Kartu Ringkasan KPI (Summary Tiles)

Gunakan 3 kartu agregasi utama dengan visual gradasi yang kontras dan elegan:

```blade
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="summary-card summary-primary">
            <i class="mdi mdi-clipboard-text-clock-outline float-end fs-24 opacity-50"></i>
            <h5>Total Transaksi</h5>
            <h3>{{ number_format($totalCount, 0, ',', '.') }} <span class="fs-14 fw-normal opacity-75">Transaksi</span></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="summary-card summary-success">
            <i class="mdi mdi-package-variant-closed float-end fs-24 opacity-50"></i>
            <h5>Total Kuantitas</h5>
            <h3>{{ number_format($totalQty, 1, ',', '.') }} <span class="fs-14 fw-normal opacity-75">Item</span></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="summary-card summary-warning">
            <i class="mdi mdi-cash-multiple float-end fs-24 opacity-50"></i>
            <h5>Total Nominal</h5>
            <h3>Rp {{ number_format($totalAmount, 0, ',', '.') }}</h3>
        </div>
    </div>
</div>
```

---

## 6. CSS Terstandarisasi

Sertakan CSS ter-scope berikut di section `@push('style')`:

```css
/* ── Scoped Table Styling ── */
#[table-id] {
    border-collapse: separate;
    border-spacing: 0;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
}

#[table-id] thead th {
    background-color: #f8fafc;
    color: #475569;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 13px 12px;
    border-bottom: 2px solid #e2e8f0;
    border-top: none;
    white-space: nowrap;
    vertical-align: middle;
}

#[table-id] tbody td {
    padding: 11px 12px;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
    font-size: 12.5px;
    white-space: nowrap;
    vertical-align: middle;
}

#[table-id] tbody tr {
    transition: background-color 0.15s ease;
}

#[table-id] tbody tr:hover {
    background-color: #f8fafc !important;
}

/* ── Tombol Icon Ramping ── */
.btn-icon {
    border-radius: 8px !important;
    padding: 6px 10px;
    font-size: 13px;
    transition: all 0.2s ease;
}
.btn-icon:hover {
    transform: translateY(-1px);
}

/* ── Avatar Badge Entitas ── */
.avatar-badge-entity {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    color: #ffffff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 10px rgba(79, 70, 229, 0.25);
    flex-shrink: 0;
}

/* ── Summary KPI Cards ── */
.summary-card {
    border-radius: 12px;
    padding: 18px 20px;
    border: 1px solid transparent;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.summary-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}
.summary-card h5 {
    font-size: 12px;
    margin-bottom: 6px;
    opacity: 0.8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
}
.summary-card h3 {
    font-size: 22px;
    font-weight: 800;
    margin: 0;
    letter-spacing: -0.5px;
}
.summary-primary { background: linear-gradient(135deg, #eef2ff, #e0e7ff); border-color: #c7d2fe; color: #3730a3; }
.summary-success { background: linear-gradient(135deg, #ecfdf5, #d1fae5); border-color: #a7f3d0; color: #065f46; }
.summary-warning { background: linear-gradient(135deg, #fefce8, #fef9c3); border-color: #fde68a; color: #92400e; }

/* ── Standarisasi Seragam Filter Bar (Tinggi Presisi: 38px) ── */
.filter-label {
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    margin-bottom: 6px;
    display: block;
}

/* Input Tanggal Flatpickr dengan Ikon Kalender Elegan */
.filter-control {
    height: 38px !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 8px !important;
    font-size: 13px !important;
    color: #334155 !important;
    background-color: #ffffff !important;
    padding: 0 12px 0 36px !important;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='4' width='18' height='18' rx='2' ry='2'%3E%3C/rect%3E%3Cline x1='16' y1='2' x2='16' y2='6'%3E%3C/line%3E%3Cline x1='8' y1='2' x2='8' y2='6'%3E%3C/line%3E%3Cline x1='3' y1='10' x2='21' y2='10'%3E%3C/line%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: 12px center;
    background-size: 15px 15px;
    transition: all 0.2s ease !important;
}

.filter-control::placeholder {
    color: #94a3b8 !important;
    font-size: 13px !important;
}

.filter-control:hover {
    border-color: #94a3b8 !important;
}

.filter-control:focus {
    border-color: #818cf8 !important;
    box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.15) !important;
    background-color: #ffffff !important;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%234f46e5' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='4' width='18' height='18' rx='2' ry='2'%3E%3C/rect%3E%3Cline x1='16' y1='2' x2='16' y2='6'%3E%3C/line%3E%3Cline x1='8' y1='2' x2='8' y2='6'%3E%3C/line%3E%3Cline x1='3' y1='10' x2='21' y2='10'%3E%3C/line%3E%3C/svg%3E");
}

/* Tombol Filter Utama (Gradient Modern) */
.btn-filter-primary {
    height: 38px !important;
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%) !important;
    color: #ffffff !important;
    border: none !important;
    border-radius: 8px !important;
    font-weight: 600 !important;
    font-size: 13px !important;
    padding: 0 16px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 6px !important;
    box-shadow: 0 2px 6px rgba(79, 70, 229, 0.25) !important;
    transition: all 0.2s ease !important;
    white-space: nowrap !important;
}

.btn-filter-primary:hover {
    background: linear-gradient(135deg, #4338ca 0%, #4f46e5 100%) !important;
    color: #ffffff !important;
    box-shadow: 0 4px 10px rgba(79, 70, 229, 0.35) !important;
    transform: translateY(-1px) !important;
}

.btn-filter-primary:active {
    transform: translateY(0) !important;
    box-shadow: 0 1px 3px rgba(79, 70, 229, 0.2) !important;
}

/* Tombol Reset Filter Presisi Kotak 38px x 38px */
.btn-filter-reset {
    height: 38px !important;
    width: 38px !important;
    min-width: 38px !important;
    padding: 0 !important;
    background: #ffffff !important;
    color: #64748b !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 8px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    transition: all 0.2s ease !important;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
}

.btn-filter-reset:hover {
    background: #fff1f2 !important;
    color: #e11d48 !important;
    border-color: #fecdd3 !important;
    box-shadow: 0 2px 6px rgba(225, 29, 72, 0.15) !important;
    transform: translateY(-1px) !important;
}

.btn-filter-reset:hover i {
    transform: rotate(-45deg);
    transition: transform 0.2s ease;
}

.btn-filter-reset:active {
    transform: translateY(0) !important;
}

/* ── Select2 Theme Matching Seragam 38px ── */
.select2-container--default .select2-selection--single {
    border: 1px solid #cbd5e1 !important;
    border-radius: 8px !important;
    height: 38px !important;
    display: flex !important;
    align-items: center !important;
    background-color: #ffffff !important;
    transition: all 0.2s ease !important;
}
.select2-container--default .select2-selection--single:hover {
    border-color: #94a3b8 !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #334155 !important;
    font-size: 13px !important;
    line-height: 36px !important;
    padding-left: 12px !important;
}
.select2-container--default .select2-selection--single .select2-selection__placeholder {
    color: #94a3b8 !important;
    font-size: 13px !important;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px !important;
    right: 10px !important;
    top: 1px !important;
}
.select2-container--default.select2-container--open .select2-selection--single,
.select2-container--default.select2-container--focus .select2-selection--single {
    border-color: #818cf8 !important;
    box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.15) !important;
}
.select2-dropdown {
    border: 1px solid #cbd5e1 !important;
    border-radius: 8px !important;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
    overflow: hidden !important;
}
.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #4f46e5 !important;
    color: #ffffff !important;
}

/* ── DataTables Inputs & Pagination ── */
#[table-id]_wrapper .dataTables_filter input {
    border-radius: 8px;
    font-size: 12px;
}
#[table-id]_wrapper .dataTables_length select {
    border-radius: 8px;
    font-size: 12px;
}
#[table-id]_wrapper .dataTables_info,
#[table-id]_wrapper .dataTables_length,
#[table-id]_wrapper .dataTables_filter {
    color: #64748b;
    font-size: 12px;
}
```

---

## 7. JavaScript: Flatpickr, Select2 & DataTables Terstandarisasi

```javascript
let filterStartPicker;
let filterEndPicker;

$(document).ready(function() {
    // 1. Inisialisasi Select2
    $('.select2-filter').select2({
        placeholder: 'Pilih Opsi...',
        allowClear: true
    });

    // 2. Inisialisasi Flatpickr
    filterStartPicker = flatpickr('#startDate', {
        dateFormat: 'Y-m-d',
        allowInput: true,
        onChange: function(selectedDates, dateStr) {
            if (filterEndPicker) filterEndPicker.set('minDate', dateStr || null);
        }
    });

    filterEndPicker = flatpickr('#endDate', {
        dateFormat: 'Y-m-d',
        allowInput: true,
        onChange: function(selectedDates, dateStr) {
            if (filterStartPicker) filterStartPicker.set('maxDate', dateStr || null);
        }
    });

    // 3. Fungsi Sinkronisasi URL Export
    function syncExportUrls() {
        const params = new URLSearchParams();
        const fieldVal = $('#fieldName').val();
        const startDate = $('#startDate').val();
        const endDate = $('#endDate').val();

        if (fieldVal) params.set('fieldName', fieldVal);
        if (startDate) params.set('startDate', startDate);
        if (endDate) params.set('endDate', endDate);

        const qs = params.toString() ? '?' + params.toString() : '';
        $('#export-pdf').attr('href', "{{ $pdfRoute }}" + qs);
        $('#export-excel').attr('href', "{{ $excelRoute }}" + qs);
    }

    // 4. Inisialisasi DataTables
    const table = $('#[table-id]').DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        pageLength: 25,
        ajax: {
            url: "{{ $dataTableRoute }}",
            data: function(d) {
                d.fieldName = $('#fieldName').val();
                d.startDate = $('#startDate').val();
                d.endDate = $('#endDate').val();
            }
        },
        columns: [
            { data: 'action', className: 'text-center align-middle' },
            { data: 'DT_RowIndex', className: 'text-center align-middle' },
            { data: 'code', className: 'align-middle font-monospace fw-semibold text-dark' },
            { data: 'date', className: 'align-middle text-center' },
            { data: 'name', className: 'align-middle' },
            {
                data: 'status',
                className: 'align-middle text-center',
                render: function(data) {
                    if (!data) return '-';
                    const badgeClass = data.toLowerCase() === 'selesai'
                        ? 'bg-success-subtle text-success'
                        : 'bg-warning-subtle text-warning';
                    return '<span class="badge ' + badgeClass + ' px-2 py-1" style="border-radius: 6px; font-weight: 600; font-size: 11px;">' + data + '</span>';
                }
            },
            { data: 'qty', className: 'text-end align-middle font-monospace' },
            {
                data: 'amount',
                className: 'text-end align-middle font-monospace fw-bold text-dark',
                render: function(data) {
                    return data ? (data.toString().startsWith('Rp') ? data : 'Rp ' + data) : 'Rp 0';
                }
            }
        ],
        columnDefs: [
            { searchable: false, targets: [0, 1, 3, 5, 6, 7] },
            { orderable: false, targets: [0, 1] }
        ],
        order: [[3, 'desc']],
        language: {
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
            infoEmpty: 'Tidak ada data',
            zeroRecords: 'Data tidak ditemukan',
            processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Memuat data...'
        }
    });

    // 5. Submit Filter
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        syncExportUrls();
        table.ajax.reload();
    });

    // 6. Reset Filter
    $('#btnResetFilter').on('click', function() {
        $('#filterForm')[0].reset();
        $('.select2-filter').val('').trigger('change');
        if (filterStartPicker) filterStartPicker.clear();
        if (filterEndPicker) filterEndPicker.clear();
        syncExportUrls();
        table.ajax.reload();
    });
});
```

---

## 8. Aturan Kolom & Perataan Data

| Tipe Data | Perataan | Kelas CSS | Catatan |
|---|---|---|---|
| Nomor urut (`DT_RowIndex`) | Tengah | `text-center align-middle` | `orderable: false`, `searchable: false` |
| Tombol aksi | Tengah | `text-center align-middle` | Gunakan `.btn-icon.btn-sm` dengan tooltip |
| Kode unik (No Order, Polisi, Faktur) | Kiri | `align-middle font-monospace fw-semibold` | Tabular / monospace font |
| Nama orang / perush / item | Kiri | `align-middle` | Teks normal |
| Tanggal & Periode | Tengah | `align-middle text-center` | Format `d-m-Y` atau `d/m/Y H:i` |
| Status / Kategori | Tengah | `align-middle text-center` | Render badge subtle (`bg-*-subtle`) |
| Kuantitas / Tonase | Kanan | `text-end align-middle font-monospace` | Format desimal (mis. `10,5`) |
| Harga / Biaya / Subtotal | Kanan | `text-end align-middle font-monospace fw-bold` | Selalu awali dengan `Rp ` |

---

## 9. Checklist "Perbaiki Design"

Saat user meminta memperbaiki desain suatu halaman tabel/report:
- [ ] Ubah kontainer menjadi `.card.border-0.shadow-sm` dengan radius 16px.
- [ ] Hapus collapse accordion; buat filter bar terbuka dengan background `#f8fafc`.
- [ ] **Pastikan tinggi seluruh kontrol filter bar seragam 38px** (Select2, Flatpickr `.filter-control`, `.btn-filter-primary`, dan `.btn-filter-reset` kotak 38px x 38px).
- [ ] Ganti semua `<input type="date">` native dengan Flatpickr (`Y-m-d`).
- [ ] Inisialisasi dropdown filter dengan Select2 terstandarisasi.
- [ ] Hapus `table-striped`, terapkan border `#e2e8f0` dan header uppercase `#f8fafc`.
- [ ] Pasang rata kanan (`text-end`) dan monospace pada kuantitas dan nominal Rupiah.
- [ ] Pasang kartu ringkasan KPI (Summary Tiles) bila ada agregasi angka atau di halaman detail.
- [ ] Tambahkan banner profil entitas (`.avatar-badge-entity`) pada halaman detail.
- [ ] Hubungkan sinkronisasi parameter URL ke tombol Export Excel & PDF.
- [ ] Setel DataTables: `pageLength: 25`, serverSide: true, bahasa Indonesia.

---

## 10. Modal CRUD Terstandarisasi (Top-Center)

Untuk CRUD sederhana pada halaman master/list, gunakan satu modal reusable di halaman index agar user tidak berpindah ke halaman create/edit terpisah. Modal harus muncul di area tengah-atas viewport: gunakan `.modal-dialog` dengan `margin: 6vh auto 1rem`, bukan `modal-dialog-centered` yang memposisikan modal tepat di tengah vertikal.

### Markup

```blade
<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#entityCrudModal">
    <i class="mdi mdi-plus"></i> Tambah Data
</button>

<div class="modal fade entity-crud-modal" id="entityCrudModal" tabindex="-1"
    aria-labelledby="entityCrudModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="entityCrudForm" method="POST" action="{{ route($view . 'store') }}">
                @csrf
                <input type="hidden" name="_method" id="entityCrudMethod" value="POST">

                <div class="modal-header entity-crud-header">
                    <div class="d-flex align-items-center gap-3">
                        <div class="entity-crud-icon"><i class="mdi mdi-database-edit-outline"></i></div>
                        <div>
                            <div class="entity-crud-eyebrow">Master data</div>
                            <h5 class="modal-title" id="entityCrudModalLabel">Tambah Data</h5>
                            <div class="entity-crud-subtitle" id="entityCrudSubtitle">Tambahkan data baru.</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body">
                    {{-- Field CRUD --}}
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn entity-crud-cancel" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn entity-crud-submit">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
```

Untuk tombol edit dari DataTables, gunakan `button` (bukan link ke halaman edit) dengan `data-bs-toggle="modal"`, `data-bs-target`, `data-name`, dan `data-action`. Event `show.bs.modal` mengisi nilai field, mengubah `action`, serta mengatur hidden method menjadi `PUT`. Endpoint `create`/`edit` lama boleh dipertahankan sebagai fallback redirect ke index.

### CSS

```css
.entity-crud-modal .modal-dialog {
    max-width: 560px;
    margin: 6vh auto 1rem;
}
.entity-crud-modal .modal-content {
    overflow: hidden;
    border: 1px solid #dbe4ef;
    border-radius: 18px;
    box-shadow: 0 24px 64px rgba(15, 23, 42, 0.22);
}
.entity-crud-modal .entity-crud-header {
    position: relative;
    overflow: hidden;
    padding: 20px 24px;
    color: #fff;
    background: linear-gradient(135deg, #312e81 0%, #4f46e5 58%, #6366f1 100%);
}
.entity-crud-modal .modal-body { padding: 24px; }
.entity-crud-modal .modal-footer {
    padding: 16px 24px;
    border-top: 1px solid #eef2f7;
    background: #f8fafc;
}
.entity-crud-modal .form-control {
    min-height: 42px;
    border: 1px solid #cbd5e1;
    border-radius: 9px;
}
```

### JavaScript

```javascript
$('#entityCrudModal').on('show.bs.modal', function(event) {
    const trigger = $(event.relatedTarget);
    const isEdit = trigger.hasClass('js-edit-entity');
    const form = $('#entityCrudForm');

    form.attr('action', isEdit ? trigger.data('action') : "{{ route($view . 'store') }}");
    $('#entityCrudMethod').val(isEdit ? 'PUT' : 'POST');
    $('#entityName').val(isEdit ? trigger.data('name') : '').trigger('focus');
});
```

Aturan UX tambahan:
- Gunakan `maxlength` dan `required` pada field yang sesuai.
- Fokuskan field utama saat modal terbuka.
- Nonaktifkan tombol submit setelah submit pertama untuk mencegah duplikasi.
- Modal wajib responsif; pada viewport kecil gunakan margin `1rem` dan padding horizontal yang lebih kecil.
- Delete tetap menggunakan konfirmasi sebelum mengirim `DELETE`; jangan hapus langsung dari tombol tabel.

---

## 11. Standar Desain Modal Dialog PHL (B2B Modal Standard)

> **Standar referensi visual:** `resources/views/operational/order/index.blade.php` & `resources/views/operational/return-do/index.blade.php`.

Gunakan panduan ini untuk setiap pembuatan, pemeriksaan, dan refactoring modal dialog di seluruh modul PHL.

### 1. Prinsip Utama Desain Modal
1. **Sizing Proporsional (Anti-XL Abuse):**
   - **`modal-md`** (max 500–560px): Untuk catatan singkat, preview teks tunggal, form konfirmasi, atau dialog upload file sederhana. JANGAN pernah menggunakan `modal-xl` hanya untuk satu input text/catatan!
   - **`modal-lg`** (max 800px): Untuk form input dengan tabel riwayat (mis. Ganti Supir, Tambah Komponen Biaya) atau tabel rincian (Detail Biaya On Charge).
   - **`modal-xl`** (max 1140–1200px): Khusus untuk dataset tabel lebar (banyak kolom) atau galeri file/lampiran dokumen.
2. **Dilarang Nested `.card` di dalam `.modal-content`:**
   Menaruh elemen `.card` di dalam `.modal-content` merupakan *anti-pattern* yang merusak konsistensi border-radius, background, dan whitespace. Gunakan `.modal-body` langsung dengan padding `p-4`, dan gunakan border pembatas halus atau section divider (`<hr class="my-3">`) jika memisahkan form dengan tabel.
3. **Header Terstandarisasi:**
   - Ikon lingkaran lembut (`avatar-sm bg-primary-subtle text-primary rounded-circle` ukuran 36×36px).
   - Title modal tebal (`fw-bold text-dark`, `font-size: 15–16px`).
   - Subtitle deskriptif (`small text-muted`).
   - Tombol close standar (`.btn-close` dengan `data-bs-dismiss="modal"`).
4. **Tabel di dalam Modal:**
   - Gunakan kelas ter-scope `.modal-table` dengan border halus `#e2e8f0`, thead abu-abu `#f8fafc`, font 12.5px.
   - **JANGAN** gunakan `table-striped` default.
   - Kolom nominal dan angka **wajib** `text-end font-monospace fw-semibold`.
5. **Kontrol Form di dalam Modal:**
   - Kontrol tinggi seragam 38px, radius 8px.
   - Select2 wajib menggunakan konfigurasi `dropdownParent: $('#modalId')` agar dropdown tidak tertutup backdrop modal dan memiliki z-index yang tepat (`z-index: 9999`).
   - Textarea menggunakan radius 8px dengan `line-height: 1.5`.

### 2. Markup Standar Modal

```blade
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
            {{-- Header --}}
            <div class="modal-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <span class="avatar-sm d-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle"
                        style="width: 36px; height: 36px;">
                        <i class="mdi mdi-[icon-name] fs-18"></i>
                    </span>
                    <div>
                        <h5 class="modal-title mb-0 fw-bold" id="exampleModalLabel">Judul Modal</h5>
                        <small class="text-muted">Deskripsi ringkas mengenai fungsi dialog ini</small>
                    </div>
                </div>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Body --}}
            <div class="modal-body p-4">
                {{-- Form atau Tabel Content --}}
                <div class="table-responsive" style="border-radius: 8px; border: 1px solid #e2e8f0;">
                    <table class="table align-middle mb-0 modal-table">
                        <thead class="bg-light">
                            <tr>
                                <th style="padding: 10px 12px; font-size: 12px; font-weight: 700; text-transform: uppercase;">No</th>
                                <th style="padding: 10px 12px; font-size: 12px; font-weight: 700; text-transform: uppercase;">Deskripsi</th>
                                <th style="padding: 10px 12px; font-size: 12px; font-weight: 700; text-transform: uppercase; text-align: right;">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data baris -->
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Footer --}}
            <div class="modal-footer bg-light py-2 px-4 border-top d-flex justify-content-between">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"
                    style="border-radius: 8px; padding: 6px 16px;">Tutup / Batal</button>
                <button type="submit" class="btn btn-primary btn-sm d-flex align-items-center gap-1"
                    style="border-radius: 8px; padding: 6px 16px; font-weight: 600;">
                    <i class="mdi mdi-content-save"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>
```

### 3. CSS Terstandarisasi (Light & Dark Mode Wajib)

Sertakan CSS ter-scope berikut di section `@push('style')` halaman yang memuat modal:

```css
/* ── Modal Styling (PHL Modal Standard §11) ── */
.modal-content {
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(15, 23, 42, 0.12);
}

.modal-header {
    padding: 14px 20px;
    background: #ffffff;
    border-bottom: 1px solid #e2e8f0;
}

.modal-footer {
    padding: 12px 20px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
}

.modal-table {
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
}

.modal-table thead th {
    background-color: #f8fafc;
    color: #475569;
    font-size: 12px;
    font-weight: 700;
    border-bottom: 1px solid #e2e8f0;
    padding: 10px 12px;
    white-space: nowrap;
}

.modal-table tbody td {
    padding: 9px 12px;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
    font-size: 12.5px;
    vertical-align: middle;
}

.modal-table tbody tr:last-child td {
    border-bottom: none;
}

.modal .select2-container {
    z-index: 9999;
    width: 100% !important;
}

/* ── Modal Dark mode (WAJIB) ── */
html[data-bs-theme="dark"] .modal-content {
    background-color: var(--bs-card-bg) !important;
    border-color: var(--bs-border-color) !important;
}

html[data-bs-theme="dark"] .modal-header {
    background-color: var(--bs-card-bg) !important;
    border-bottom-color: var(--bs-border-color) !important;
}

html[data-bs-theme="dark"] .modal-footer {
    background-color: var(--bs-secondary-bg) !important;
    border-top-color: var(--bs-border-color) !important;
}

html[data-bs-theme="dark"] .modal-body {
    background-color: var(--bs-card-bg) !important;
    color: var(--bs-body-color) !important;
}

html[data-bs-theme="dark"] .modal-body .table-responsive {
    border-color: var(--bs-border-color) !important;
}

html[data-bs-theme="dark"] .modal-table thead th {
    background-color: var(--bs-tertiary-bg) !important;
    color: var(--bs-emphasis-color) !important;
    border-bottom-color: var(--bs-border-color) !important;
}

html[data-bs-theme="dark"] .modal-table tbody td {
    color: var(--bs-body-color) !important;
    border-bottom-color: var(--bs-border-color) !important;
}

html[data-bs-theme="dark"] .modal .form-control,
html[data-bs-theme="dark"] .modal textarea {
    background-color: var(--bs-tertiary-bg) !important;
    border-color: var(--bs-border-color) !important;
    color: var(--bs-body-color) !important;
}
```

### 4. Checklist Pengecekan Modal (§11)
- [ ] Ukuran dialog proporsional (`modal-md` untuk single/short, `modal-lg` untuk form + riwayat, `modal-xl` hanya untuk multi-kolom/galeri).
- [ ] Tidak ada nested `.card` sembarangan di dalam `.modal-content`.
- [ ] Header menggunakan avatar icon, judul tebal, subtitle, dan tombol close `.btn-close`.
- [ ] Tabel di dalam modal menggunakan `.modal-table`, tanpa `table-striped`, nominal `text-end font-monospace`.
- [ ] Form input dan Select2 seragam tinggi 38px radius 8px; Select2 menggunakan `dropdownParent: $('#modalId')`.
- [ ] Tombol aksi di footer menggunakan `.btn-sm` radius 8px (`.btn-secondary` dan `.btn-primary`).
- [ ] Blok dark mode `html[data-bs-theme="dark"]` sudah mencakup header, footer, body, tabel, dan form input.
- [ ] Diuji dan dipastikan tampil elegan di mode terang **dan** mode gelap.
