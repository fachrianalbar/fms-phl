# Standarisasi Frontend — Aplikasi PHL

**Acuan emas (gold standard):** `resources/views/direct-payment/order/unpaid.blade.php`
Dokumen ini adalah standar WAJIB untuk semua halaman frontend baru maupun refactoring halaman lama. Halaman di luar standar ini harus di-cetak merah saat code review.

---

## Daftar Isi

1. [Tech Stack](#1-tech-stack)
2. [Struktur File & Penamaan](#2-struktur-file--penamaan)
3. [Kerangka Halaman List (Blade Skeleton)](#3-kerangka-halaman-list-blade-skeleton)
4. [Design Tokens](#4-design-tokens)
5. [Komponen UI Standar](#5-komponen-ui-standar)
6. [Tabel DataTables Standar](#6-tabel-datatables-standar)
7. [Modal — Bukan Halaman Form Terpisah](#7-modal--bukan-halaman-form-terpisah)
8. [Notifikasi & Konfirmasi (SweetAlert)](#8-notifikasi--konfirmasi-sweetalert)
9. [Form & AJAX](#9-form--ajax)
10. [Helper JS Wajib](#10-helper-js-wajib)
11. [Select2](#11-select2)
12. [Urutan Load Library (PENTING)](#12-urutan-load-library-penting)
13. [Checklist Halaman Baru](#13-checklist-halaman-baru)

---

## 1. Tech Stack

| Teknologi | Versi | Kegunaan |
|---|---|---|
| jQuery | 3.x | Basis semua interaksi (WAJIB, jangan vanilla JS murni) |
| Bootstrap | 5.x | Layout & komponen (grid, card, modal, badge, button) |
| DataTables | 1.13+ (BS5 theme) | Semua tabel data, selalu **server-side** |
| Select2 | 4.x | Dropdown (bank, filter, select di modal) |
| SweetAlert2 | v11 (class `Swal`) | Konfirmasi & loader batch/advanced |
| SweetAlert v1 | (fungsi `swal`) | Alert sederhana & alur tunggal |
| Material Design Icons | MDI | Semua ikon (`mdi mdi-*`) |
| Flatpickr | — | Date picker (sudah global di layout) |

**Yang sudah dimuat GLOBAL oleh `layouts/main.blade.php`** — JANGAN push ulang:
`jquery.min.js`, `bootstrap.bundle.min.js`, `datatables.net` + `dataTables.bootstrap5`, `flatpickr`, `app.js`, `sweet-alert/confirm.js`.

**Yang WAJIB di-push per halaman:** ekstensi DataTables (buttons, keytable, responsive, select), `sweetalert2.min.js`, `sweetalert.min.js`, `select2.full.min.js`, `select2-custom.js`, `helper.js`.

---

## 2. Struktur File & Penamaan

```
resources/views/{modul}/
├── index.blade.php          # daftar utama
├── partials/
│   ├── table-style.blade.php  # CSS bersama antar halaman se-modul
│   ├── modals.blade.php       # SEMUA modal se-modul dikumpulkan di sini
│   └── flash-swal.blade.php   # flash message server → SweetAlert
```

**Route & konvensi backend:**

| Jenis | Pola | Contoh |
|---|---|---|
| Halaman | `Route::resource` atau explicit | `bank/user-bank` |
| Datatable | prefix `datatable/` nama `dt.` | `dt.bank-user`, `dt.direct-payment.unpaid` |
| AJAX endpoint | prefix `ajax/` nama `ajax.` | `ajax/direct-payment-detail/{code}` |
| API JSON | prefix `api/` nama `api.` | `api/user-bank/company` |

Controller memiliki properti standar: `$title`, `$view`, `$service`. View menerima `$view` dan `$title` dari controller untuk membangun route.

---

## 3. Kerangka Halaman List (Blade Skeleton)

Setiap halaman list WAJIB mengikuti urutan section ini (lihat `direct-payment/order/unpaid.blade.php`):

```blade
@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Nama Modul',      // breadcrumb 1
    'secondSegment' => 'Nama Halaman',   // breadcrumb 2
])

@push('style')
    {{-- 1. CSS library yang belum global --}}
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/libs/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/sweetalert2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom-select2.css') }}">

    {{-- 2. CSS bersama se-modul --}}
    @include('modul.partials.table-style')

    {{-- 3. CSS khusus halaman (seminimal mungkin) --}}
    <style> ... </style>
@endpush

@section('content')
    {{-- 4. Page Header + tombol aksi utama --}}
    {{-- 5. KPI stat cards (jika ada data agregat) --}}
    {{-- 6. Kartu container tabel: filter pills + tabel --}}
    {{-- 7. Semua modal --}}
    @include('modul.partials.modals')
@endsection

@push('script')
    {{-- 8. JS library (urutan lihat bagian 12) --}}
    {{-- 9. Script halaman --}}
@endpush
```

---

## 4. Design Tokens

Warna & bentuk dasar (Slate palette + Blue primary):

| Token | Nilai | Pemakaian |
|---|---|---|
| Border card | `#e2e8f0` | border default semua kartu |
| Border kuat | `#cbd5e1` | hover / input border |
| Divider halus | `#f1f5f9` | border antar baris tabel |
| Teks gelap | `#0f172a` | judul nilai KPI |
| Teks body | `#334155` | isi sel tabel |
| Teks muted | `#64748b` | label, deskripsi |
| Background terang | `#f8fafc` | thead, hover baris |
| Primary | `#2563eb` | pill aktif, link |
| Gradient primary | `135deg, #3b82f6 → #1d4ed8` | ikon header, tombol utama |
| Radius card | `14–16px` | kartu KPI & container |
| Radius pill | `30px` | filter pill, badge bulat |
| Radius input | `8px` | form control |
| Shadow halus | `0 2px 8px rgba(0,0,0,0.02)` | kartu diam |
| Shadow hover | `0 10px 25px rgba(0,0,0,0.06)` | kartu di-hover |

**Tipografi tabel:** thead `11px bold uppercase letterspacing 0.04em`, tbody `12px`, angka uang pakai class `font-monospace`.

**Status warna semantik** (konsisten di seluruh app):

| Status | Warna BS5 |
|---|---|
| Lunas / sukses | `success` |
| Belum bayar / gagal | `danger` |
| DP / partial / proses | `warning` |
| Info / kelebihan bayar | `info` |
| Netral | `secondary` |

Badge status selalu pattern: `badge bg-{warna}-subtle text-{warna} border border-{warna}-subtle rounded-pill fs-11`.

---

## 5. Komponen UI Standar

### 5.1 Page Header + Aksi

Ikon gradient + judul + badge jumlah + deskripsi kecil di kiri; tombol aksi di kanan:

```blade
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-3 d-flex align-items-center justify-content-center shadow-sm text-white"
             style="width: 48px; height: 48px; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
            <i class="mdi mdi-cash-register fs-24"></i>
        </div>
        <div>
            <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                {{ $title }}
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill fs-12 px-2 py-1">
                    {{ number_format($stats['totalCount'] ?? 0) }} Unit
                </span>
            </h4>
            <p class="text-muted mb-0 fs-12">Deskripsi singkat fungsi halaman.</p>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm" id="btn-refresh-table">
            <i class="mdi mdi-refresh me-1"></i> Refresh
        </button>
        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" onclick="openCreateModal()">
            <i class="mdi mdi-plus me-1"></i> Tambah Data
        </button>
    </div>
</div>
```

**Aturan tombol:** aksi utama = `btn-primary` pill; aksi sekunder = `btn-outline-secondary` pill; destruktif = `btn-danger`; tombol ikon tabel = `btn btn-icon btn-sm bg-{warna}-subtle` + ikon `fs-14 text-{warna}`.

### 5.2 KPI Stat Cards (4 kolom)

Grid: `row g-3 mb-4`, tiap kartu `col-12 col-sm-6 col-xl-3`. Kartu bisa diklik untuk memfilter tabel (`data-filter`):

```blade
<div class="col-12 col-sm-6 col-xl-3">
    <div class="stat-card" data-filter="unpaid" title="Klik untuk filter">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <div class="stat-label">Belum Bayar</div>
                <div class="stat-value text-danger">
                    {{ number_format($stats['unpaidCount'] ?? 0) }}
                    <span class="fs-13 text-muted fw-normal">Unit</span>
                </div>
            </div>
            <div class="stat-icon-wrapper bg-danger-subtle text-danger">
                <i class="mdi mdi-alert-circle-outline"></i>
            </div>
        </div>
        <div class="stat-desc text-danger text-truncate">
            <i class="mdi mdi-close-circle-outline me-1"></i>Deskripsi singkat
        </div>
    </div>
</div>
```

### 5.3 Kartu Container Tabel + Filter Pills

```blade
<div class="table-container-card mb-4">
    <div class="table-top-bar d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center flex-wrap gap-2">
            <button type="button" class="filter-pill-btn active" data-status="all">
                <i class="mdi mdi-format-list-bulleted"></i>
                <span>Semua</span>
                <span class="badge-pill-count">{{ number_format($stats['totalCount'] ?? 0) }}</span>
            </button>
            {{-- pill lain: data-status="unpaid|partial|nota" --}}
        </div>
    </div>
    <div class="card-body p-3">
        <div class="table-scroll-wrap custom-scrollbar">
            <table class="table table-striped nowrap direct-payment-table" id="dt">
                <thead> ... </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
```

Kolom uang: `class="text-end"`. Kolom status: `class="text-center"`. Kolom aksi: `class="text-center" style="width: 130px;"`.

### 5.4 Selection Command Bar (jika ada checkbox massal)

Bar gelap sticky yang muncul saat ada baris terpilih:

```blade
<div class="selection-command-bar d-none" id="selection-bar" aria-live="polite">
    <div>
        <strong id="selected-headline">0 terpilih</strong>
        <div class="selection-facts mt-1">
            <span id="selection-order-fact">0 DO</span>
            <span id="selection-remaining-fact">Sisa Rp 0</span>
        </div>
    </div>
    <div class="selection-actions">
        <button type="button" class="btn btn-outline-light btn-sm" id="btn-clear-selection">Hapus Pilihan</button>
        <button type="button" class="btn btn-success btn-sm fw-semibold" id="btn-batch-pay">Bayar</button>
    </div>
</div>
```

CSS `selection-command-bar`: sticky top `74px`, background `#1e293b`, radius `12px`, text putih.

---

## 6. Tabel DataTables Standar

**Konfigurasi WAJIB** untuk semua tabel data:

```js
dataTableInstance = $('#dt').DataTable({
    "processing": true,
    "serverSide": true,          // WAJIB: data selalu server-side
    "destroy": true,
    "scrollX": true,             // tabel lebar (banyak kolom uang)
    "autoWidth": false,
    "pageLength": 25,
    "ajax": {
        "url": "{{ route('dt.modul-halaman') }}",
        "data": function(d) {
            d.status = currentStatusFilter;   // filter aktif ikut request
        }
    },
    "columns": [
        { "data": 'action', "className": 'text-center align-middle', "orderable": false, "searchable": false },
        { "data": 'DT_RowIndex', "className": 'text-center align-middle', "orderable": false, "searchable": false },
        // ... kolom data; uang → "className": 'text-end align-middle'
    ],
    "order": [[4, 'asc']],
    "drawCallback": function() {
        // restore state (checkbox terpilih dsb) + re-init tooltip
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                .map(function(el) { return new bootstrap.Tooltip(el); });
        }
    },
    "language": {
        "processing": "Memuat data...",
        "search": "",
        "searchPlaceholder": "Cari kode, nama, ...",
        "lengthMenu": "Tampilkan _MENU_ data",
        "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
        "infoEmpty": "Tidak ada data",
        "zeroRecords": "Tidak ditemukan data yang sesuai",
        "paginate": {
            "next": "<i class='mdi mdi-chevron-right'></i>",
            "previous": "<i class='mdi mdi-chevron-left'></i>"
        }
    }
});
```

**Aturan kolom:**
- Kolom aksi selalu pertama, `orderable: false, searchable: false`.
- `DT_RowIndex` (nomor urut) setelah aksi.
- Data uang dirender `text-end font-monospace` dengan format `Rp 1.234.567`.
- Teks panjang dibungkus `<span class="cell-ellipsis" title="...">`.
- HTML dari server (badge, tombol) didaftarkan di `rawColumns([...])`.

**Render kolom server (controller `datatable()`)** pakai helper `e()` untuk escape atribut data-*:

```php
->addColumn('action', function ($row) {
    $edit = '<a href="javascript:void(0)" class="btn-open-edit"
        data-id="'.e($row->id).'" data-name="'.e($row->name).'" ...>
        <i class="mdi mdi-pencil-outline"></i></a>';
    return '<td>'.$edit.'...</td>';
})
```

---

## 7. Modal — Bukan Halaman Form Terpisah

**Semua create/edit/rincian WAJIB via modal Bootstrap di dalam halaman list.** Tidak membuat `create.blade.php` / `edit.blade.php` terpisah. Modal dikumpulkan di `partials/modals.blade.php` dan di-include dari halaman.

### Struktur modal CRUD

```blade
<div class="modal fade" id="modulModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="modulForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="modulModalLabel">Tambah Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="field">Label <i class="mdi mdi-information text-danger"></i></label>
                            <input class="form-control" name="field" id="field" required>
                        </div>
                        {{-- field wajib ditandai ikon info merah --}}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
```

### Pola JS open/reset modal

```js
const urlStore  = "{{ route($view . 'store') }}";
const urlUpdate = "{{ route($view . 'update', '') }}".replace(/\/+$/, ""); // strip trailing slash

function resetModal() {
    $('#modulForm')[0].reset();
    $('#modulForm').attr('action', urlStore);
    $('#methodField').remove();                       // hapus _method=PUT
    $('#modulModalLabel').text('Tambah Data');
    $('#modulForm input[name="_method"]').remove();
}

function openCreateModal() {
    resetModal();
    $('#modulModal').modal('show');
}

// Edit: tombol di dalam tabel punya data-* (data-id, data-name, ...)
$(document).on('click', '.btn-open-edit', function() {
    resetModal();
    const data = $(this).data();
    $('#modulModalLabel').text('Edit Data');
    $('#modulForm').attr('action', urlUpdate + '/' + data.id);
    $('#modulForm').append($('<input>').attr({ type: 'hidden', name: '_method', value: 'PUT' }));
    $('#field').val(data.name);
    $('#modulModal').modal('show');
});
```

**Aturan modal:**
- Select di dalam modal WAJIB `dropdownParent` ke modal-nya (lihat bagian 11).
- Saat mode edit, field yang tidak boleh diubah (mis. saldo) disembunyikan, bukan di-disable saja.
- `hidden.bs.modal` dipakai untuk reset form + state.
- Modal detail (read-only): tampilkan loading spinner dulu (`Memuat data...`), lalu isi via AJAX.

---

## 8. Notifikasi & Konfirmasi (SweetAlert)

Dua library hidup bersama (lihat bagian 12 untuk urutan load):

| Sintaks | Library | Pemakaian |
|---|---|---|
| `swal({ title, text, icon })` | SweetAlert v1 (fungsi) | Alert hasil operasi, validasi sederhana, alur tunggal |
| `Swal.fire({...})` | SweetAlert2 v11 (class) | Konfirmasi batch, loader, HTML kaya |

**Pola sukses/gagal AJAX:**

```js
// sukses (v1)
swal({ title: "Berhasil!", text: "Data berhasil disimpan.", icon: "success", button: "OK" });

// gagal dengan pesan server
error: function(xhr) {
    let msg = 'Terjadi kesalahan saat menyimpan.';
    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
    swal({ title: "Gagal!", text: msg, icon: "error", button: "OK" });
}
```

**Pola konfirmasi destruktif (v2):**

```js
Swal.fire({
    title: 'Batalkan data ini?',
    text: 'Tindakan tidak dapat dibatalkan.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Ya, Lanjutkan',
    cancelButtonText: 'Kembali',
    confirmButtonColor: '#dc3545',
}).then(function(result) {
    if (result.isConfirmed) { /* submit */ }
});
```

**Loader proses (v2):**

```js
function swalLoader(title, html) {
    return Swal.fire({
        title: title, html: html,
        allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false,
        didOpen: function() { Swal.showLoading(); },
    });
}
```

**Flash message server** → partial `flash-swal.blade.php` yang membaca session `success` / `fail` lalu menampilkan `Swal.fire`.

---

## 9. Form & AJAX

**Semua submit form CRUD via AJAX, lalu reload DataTable** (tanpa full page reload):

```js
$('#modulForm').on('submit', function(e) {
    e.preventDefault();
    const form = $(this);

    $.ajax({
        type: form.attr('method') === 'POST' && form.find('input[name="_method"]').val() === 'PUT' ? 'PUT' : 'POST',
        url: form.attr('action'),
        data: form.serialize(),
        success: function(response) {
            $('#modulModal').modal('hide');
            form[0].reset();
            dataTableInstance.ajax.reload(null, false);   // resetPaging = false
            swal({ title: "Berhasil!", text: "Data disimpan.", icon: "success" });
        },
        error: function(xhr) { /* lihat pola gagal di bagian 8 */ }
    });
});
```

**Aturan AJAX:**
- CSRF diambil dari `@csrf` di dalam form (`form.serialize()` sudah membawanya), atau header eksplisit untuk JSON: `'X-CSRF-TOKEN': form.find('input[name="_token"]').val()`.
- Payload JSON (batch): `contentType: 'application/json; charset=utf-8'` + `data: JSON.stringify(payload)` + header `Accept: application/json`.
- Upload file / FormData: `processData: false, contentType: false`.
- Anti double-submit: flag `submissionInFlight` + disable tombol submit + spinner.
- Anti race-condition request bank/select: pakai counter `requestSequence` (lihat `loadBatchBankData`).
- Setelah operasi sukses: `dataTableInstance.ajax.reload(null, false)` BUKAN `window.location.reload()` (kecuali memang perlu sinkron penuh, mis. setelah generate nota).
- Error 409 (konflik data): tutup modal, bersihkan seleksi, reload tabel, tampilkan warning.

---

## 10. Helper JS Wajib

Setiap halaman yang mengolah angka WAJIB pakai helper ini (saling konsisten, jangan buat fungsi format sendiri):

```js
// Uang: Rp 1.234.567
function formatCurrency(value) {
    return 'Rp ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Math.round(Number(value) || 0));
}

// Angka biasa
function formatNumber(value) {
    return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Math.round(Number(value) || 0));
}

// Input teks angka Indonesia ("1.234.567") → number
function parseNumber(value) {
    if (value === null || value === undefined) return 0;
    let clean = String(value).trim().replace(/\./g, '').replace(',', '.');
    let n = parseFloat(clean);
    return isNaN(n) || n < 0 ? 0 : n;
}

// Escape HTML sebelum menyisipkan data server ke DOM
function escapeHtml(value) {
    return String(value || '').replace(/[&<>"']/g, function(c) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
}

// Tanggal dd-mm-yyyy
function formatNotaDate(value) { ... }
```

`formatAngka` untuk input on-the-fly tersedia di `assets/js/helper.js` (`oninput="formatAngka(this)"`).

**Konvensi penamaan JS:**
- State global halaman: `let currentStatusFilter`, `let dataTableInstance`, objek seleksi `const selectedItems = {}`.
- Handler elemen dinamis (hasil render DataTable) WAJIB delegated: `$(document).on('click', '.class', ...)`.
- Fungsi pembuka modal: `openCreateModal()`, `openEditModal(data)`.
- Grup script dengan komentar banner `// ==== NAMA SEKSI ====`.

---

## 11. Select2

Semua select yang perlu pencarian memakai class `js-example-basic-single`. **Select di dalam modal WAJIB set `dropdownParent`** agar dropdown tidak tertinggal di belakang backdrop:

```js
// di dalam modal
$('#bankCode').select2({
    dropdownParent: $('#modulModal'),
    width: '100%',
});

// dropdown "Tampilkan _MENU_ data" milik DataTables
$('#dt_wrapper .dataTables_length select').select2({
    minimumResultsForSearch: Infinity,
    width: '88px',
    dropdownAutoWidth: true,
});
```

Setelah mengisi value via JS, selalu `.trigger('change')` agar Select2 me-render labelnya.

---

## 12. Urutan Load Library (PENTING)

```blade
@push('script')
    {{-- 1. Ekstensi DataTables (core sudah global) --}}
    <script src="{{ asset('assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-select/js/dataTables.select.min.js') }}"></script>

    {{-- 2. SweetAlert2 DULU, sweetalert v1 KEDUA.
         Keduanya menimpa global 'swal': v1 dimuat terakhir → window.swal = fungsi v1
         (alur tunggal), Swal = class v2 (batch/loader).
         Kalau terbalik → TypeError "class constructors must be invoked with new". --}}
    <script src="{{ asset('assets/js/sweet-alert/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>

    {{-- 3. Select2 + helper --}}
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2-custom.js') }}"></script>
    <script src="{{ asset('assets/js/helper.js') }}"></script>

    {{-- 4. Script halaman --}}
    <script> ... </script>
@endpush
```

---

## 13. Checklist Halaman Baru

Sebelum merge, pastikan:

- [ ] `@extends('layouts.main')` dengan `firstSegment` / `secondSegment` breadcrumb benar
- [ ] Tidak push ulang library yang sudah global (jQuery, Bootstrap, DataTables core, flatpickr)
- [ ] Urutan library sesuai bagian 12 (terutama sweetalert2 sebelum sweetalert v1)
- [ ] Tabel: server-side, `scrollX`, bahasa Indonesia, placeholder pencarian jelas
- [ ] Kolom uang `text-end font-monospace` + `formatCurrency`/`formatAngkaValue`
- [ ] Data server yang disisipkan ke DOM di-escape (`e()` di PHP / `escapeHtml` di JS)
- [ ] Aksi baris: tombol ikon `btn-icon btn-sm bg-{warna}-subtle` + tooltip + delegated handler
- [ ] Create/edit via **modal** (bukan halaman form terpisah), reset & title berubah per mode
- [ ] Select di modal pakai `dropdownParent`
- [ ] Submit via AJAX + `dataTableInstance.ajax.reload(null, false)` + feedback swal
- [ ] Konfirmasi untuk aksi destruktif (`Swal.fire` showCancelButton)
- [ ] Anti double-submit (disable tombol / flag in-flight)
- [ ] CSS bersama dipindah ke `partials/table-style.blade.php`, bukan diduplikasi per halaman
- [ ] Warna status konsisten dengan tabel semantik bagian 4
- [ ] Halaman responsif (grid `col-12 col-sm-6 col-xl-3`, `table-scroll-wrap` untuk scroll-x)
