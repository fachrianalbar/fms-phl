---
name: phl-table-design
description: Standard UI design system for B2B data tables, reports, filter bars, KPI summary cards, and detail pages in the PHL project (Bootstrap 5, DataTables, Flatpickr, Select2). Use this skill whenever the user says "perbaiki design", "perbaiki tampilan", "standarisasi tabel", "desain table", or asks to redesign/improve any report or table page.
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
- `docs/table-standard.md`

---

## 1. Prinsip Utama (Anti-Default Discipline)

1. **JANGAN gunakan `table-striped` default.** Gunakan garis batas tipis (`#f1f5f9`), rounded corner (`12px`), dan hover state yang halus (`#f8fafc`).
2. **JANGAN sembunyikan filter di accordion tertutup.** Gunakan panel filter terbuka yang rapi dengan background soft (`#f8fafc`).
3. **JANGAN gunakan `<input type="date">` biasa.** Selalu gunakan Flatpickr dengan format standar backend `Y-m-d`.
4. **JANGAN biarkan angka nominal/kuantitas rata kiri.** Selalu gunakan `text-end` dan font monospace/tabular-nums.
5. **Gunakan kartu KPI (Summary Tiles) bergradasi lembut** di halaman detail atau agregasi (Total Transaksi, Total Qty, Total Biaya/Gaji).
6. **Selalu sinkronkan parameter filter ke tombol Export (Excel & PDF)** agar file yang diunduh sesuai dengan data yang sedang difilter.

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

## 3. Komponen Filter Bar Terstandarisasi

Panel filter selalu terbuka, berada tepat di atas tabel:

```blade
<div class="card border-0 mb-4"
    style="background: #f8fafc; border: 1px solid #e2e8f0 !important; border-radius: 12px;">
    <div class="card-body p-3">
        <form id="filterForm">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-muted mb-1" style="font-size: 12px;">Label Dropdown</label>
                    <select class="form-select form-select-sm select2-filter" name="fieldName" id="fieldName" style="width: 100%;">
                        <option value="">Semua Opsi</option>
                        @foreach ($options as $item)
                            <option value="{{ $item->code }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold text-muted mb-1" style="font-size: 12px;">Dari Tanggal</label>
                    <input class="form-control form-control-sm" name="startDate" id="startDate"
                        type="text" placeholder="Pilih Tanggal Mulai"
                        style="border-radius: 8px; background: #fff;">
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold text-muted mb-1" style="font-size: 12px;">Sampai Tanggal</label>
                    <input class="form-control form-control-sm" name="endDate" id="endDate"
                        type="text" placeholder="Pilih Tanggal Akhir"
                        style="border-radius: 8px; background: #fff;">
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-sm btn-primary w-100" style="border-radius: 8px; font-weight: 600;"
                        type="submit" id="btnFilter">
                        <i class="mdi mdi-filter me-1"></i> Filter
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px;"
                        id="btnResetFilter" title="Reset Filter">
                        <i class="mdi mdi-refresh"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
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

/* ── Select2 Theme Matching ── */
.select2-container--default .select2-selection--single {
    border: 1px solid #cbd5e1 !important;
    border-radius: 8px !important;
    height: 38px !important;
    display: flex !important;
    align-items: center !important;
    background-color: #ffffff !important;
    transition: all 0.2s ease !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #334155 !important;
    font-size: 13px !important;
    line-height: normal !important;
    padding-left: 10px !important;
}
.select2-container--default .select2-selection--single .select2-selection__placeholder {
    color: #94a3b8 !important;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px !important;
    right: 8px !important;
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
- [ ] Ganti semua `<input type="date">` native dengan Flatpickr (`Y-m-d`).
- [ ] Inisialisasi dropdown filter dengan Select2 terstandarisasi.
- [ ] Hapus `table-striped`, terapkan border `#e2e8f0` dan header uppercase `#f8fafc`.
- [ ] Pasang rata kanan (`text-end`) dan monospace pada kuantitas dan nominal Rupiah.
- [ ] Pasang kartu ringkasan KPI (Summary Tiles) bila ada agregasi angka.
- [ ] Hubungkan sinkronisasi parameter URL ke tombol Export Excel & PDF.
- [ ] Setel DataTables: `pageLength: 25`, serverSide: true, bahasa Indonesia.
