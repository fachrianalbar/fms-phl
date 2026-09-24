# Standar Tabel Report

Dokumen ini menjadi acuan tampilan tabel report berbasis Bootstrap 5 dan DataTables di project PHL. Referensi visual utama: `resources/views/report/driver-salary/index.blade.php`.

## Design read
Tabel internal B2B dengan visual enterprise yang rapi, padat, dan mudah dipindai. Gunakan pola Bootstrap/DataTables yang sudah ada, bukan styling tabel baru per halaman.

## Struktur markup standar

```blade
<div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center"
        style="border-color: #e2e8f0;">
        {{-- Judul dan aksi halaman --}}
    </div>

    <div class="card-body p-4">
        {{-- Filter card collapse standar — lihat bagian "Filter bar". JANGAN pakai border !important inline. --}}
        <div class="filter-card">
            {{-- .filter-card-header (trigger) + .filter-collapse > .filter-collapse-body > #filterForm --}}
        </div>

        <div class="table-responsive custom-scrollbar">
            <table class="table align-middle w-100 mb-0" id="dtReport">
                <thead>
                    <tr>
                        <th class="text-center">No</th>
                        <th>Nama / Kode</th>
                        <th class="text-center">Tanggal</th>
                        <th class="text-end">Nominal</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
```

## Visual rules

Use the following scoped CSS for each table ID. Replace `#dtReport` with the actual table ID.

```css
#dtReport {
    border-collapse: separate;
    border-spacing: 0;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
}

#dtReport thead th {
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

#dtReport tbody td {
    padding: 11px 12px;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
    font-size: 12.5px;
    white-space: nowrap;
    vertical-align: middle;
}

#dtReport tbody tr {
    transition: background-color 0.15s ease;
}

#dtReport tbody tr:hover {
    background-color: #f8fafc !important;
}
```

Do not use `table-striped` for standard report tables. Use the hover state and subtle separators instead. Keep long values on one line inside a `.table-responsive.custom-scrollbar` wrapper.

## Alignment and column rules

- Number/index columns: `text-center` and `searchable: false`.
- Dates and periods: `text-center` and `searchable: false` when formatted server-side.
- Quantities and currency: `text-end`, with `font-variant-numeric: tabular-nums` when possible.
- Action columns: `text-center` and `orderable: false`.
- Keep header and DataTables column order identical.
- Prefer a descriptive table ID, for example `dt-maintenance-detail` or `dtProcessed`.

## Filter bar

Filter memakai panel collapse `.filter-card` standar. **Referensi implementasi:** `resources/views/operational/not-return-do/index.blade.php`. Spesifikasi lengkap (markup, CSS terang + gelap, JS) ada di skill `phl-table-design` §3.

```blade
<div class="card-body pt-3 pb-0">
    <div class="filter-card">
        <button type="button" class="filter-card-header" data-bs-toggle="collapse"
            data-bs-target="#uniqueFilterCollapse" aria-expanded="false" aria-controls="uniqueFilterCollapse">
            <span class="filter-card-heading">
                <i class="mdi mdi-filter-variant"></i>
                <strong>Filter Data</strong>
                <small>Gunakan filter untuk mempersempit data</small>
            </span>
            <i class="mdi mdi-chevron-down filter-card-chevron"></i>
        </button>
        <div class="collapse filter-collapse" id="uniqueFilterCollapse">
            <div class="filter-collapse-body">
                <div id="filterForm">
                    <div class="row g-3">
                        {{-- field: .col-xl-3 col-md-6 berisi .filter-label + kontrol 38px --}}
                        {{-- kolom akhir: .btn-filter-primary (Terapkan) + .btn-filter-reset (reset) --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
```

Aturan:
- Panel: background `#f8fafc`, border `1px #e2e8f0`, radius `12px`, collapse tertutup default.
- Semua kontrol tinggi 38px / radius 8px. **Jangan** pakai `.form-control-sm` atau `.btn-sm`.
- Tombol Terapkan = `.btn-filter-primary` (gradient indigo). Reset = `.btn-filter-reset` (38×38, ikon `mdi-refresh`).
- `#filterForm` adalah `<div>`, bukan `<form>`; tombol Terapkan `type="button"` yang diikat ke handler `click`.
- **Dark mode wajib:** sertakan override `html[data-bs-theme="dark"]` (panel, header, label, kontrol, tombol reset). Jangan menulis `border … !important` inline — inline `!important` tidak bisa ditimpa dan merusak dark mode.

## DataTables standard

```javascript
$('#dtReport').DataTable({
    processing: true,
    serverSide: true,
    destroy: true,
    pageLength: 25,
    ajax: {
        url: 'DATATABLE_URL',
        data: function(d) {
            // Add page-specific filters here.
        }
    },
    columns: [
        { data: 'DT_RowIndex', className: 'text-center align-middle' },
        { data: 'name', className: 'align-middle' },
        { data: 'date', className: 'align-middle text-center' },
        { data: 'amount', className: 'text-end align-middle' }
    ],
    columnDefs: [
        { searchable: false, targets: [0, 2, 3] },
        { orderable: false, targets: [0] }
    ],
    language: {
        search: 'Cari:',
        lengthMenu: 'Tampilkan _MENU_ data',
        info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
        infoEmpty: 'Tidak ada data',
        zeroRecords: 'Data tidak ditemukan',
        processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Memuat data...'
    }
});
```

## Date picker standard

Use the local Flatpickr asset for report date filters. Do not use native `type="date"` inputs when the page follows this standard.

```blade
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">

<input class="form-control filter-control" name="startDate" id="startDate" type="text"
    placeholder="Pilih tanggal mulai" value="{{ $startDate }}">
```

Load the local scripts before the page script and use the backend-compatible `Y-m-d` format:

```blade
<script src="{{ asset('assets/js/flat-pickr/flatpickr.js') }}"></script>
<script src="{{ asset('assets/js/flat-pickr/custom-flatpickr.js') }}"></script>
```

```javascript
let startDatePicker;
let endDatePicker;

startDatePicker = flatpickr('#startDate', {
    dateFormat: 'Y-m-d',
    allowInput: true,
    onChange: function(selectedDates, dateStr) {
        if (endDatePicker) endDatePicker.set('minDate', dateStr || null);
    }
});

endDatePicker = flatpickr('#endDate', {
    dateFormat: 'Y-m-d',
    allowInput: true,
    onChange: function(selectedDates, dateStr) {
        if (startDatePicker) startDatePicker.set('maxDate', dateStr || null);
    }
});
```

If filter values are rendered from the request, initialize each picker constraint from the existing input values after both pickers are created.
## KPI Summary Cards (Metric Tiles)

Untuk halaman detail report atau dashboard yang menampilkan agregasi angka (seperti Total Transaksi, Total Kuantitas, dan Total Biaya/Gaji), gunakan kartu KPI dengan pola warna gradient lembut yang konsisten:

```blade
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="summary-card summary-primary">
            <i class="mdi mdi-wrench-clock-outline float-end fs-24 opacity-50"></i>
            <h5>Total Maintenance</h5>
            <h3>12 <span class="fs-14 fw-normal opacity-75">Transaksi</span></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="summary-card summary-success">
            <i class="mdi mdi-package-variant-closed float-end fs-24 opacity-50"></i>
            <h5>Total Qty Item</h5>
            <h3>24.0 <span class="fs-14 fw-normal opacity-75">Item</span></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="summary-card summary-warning">
            <i class="mdi mdi-cash-multiple float-end fs-24 opacity-50"></i>
            <h5>Total Biaya</h5>
            <h3>Rp 4.500.000</h3>
        </div>
    </div>
</div>
```

```css
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
```

## Accessibility and responsive behavior

- Keep visible labels above filter controls. Do not use placeholder text as the only label.
- Use semantic `<thead>`, `<tbody>`, and `<th>` elements.
- Keep horizontal scrolling enabled for wide reports on small screens.
- Do not remove focus states from inputs, buttons, pagination, or DataTables search.
- Test empty, loading, and zero-result states.

## Implementation checklist

- [ ] Card uses `border-0 shadow-sm` and 16px radius.
- [ ] Filter uses the standard `.filter-card` collapse panel (38px controls, radius 8px) — ref `operational/not-return-do`, spec in `phl-table-design` §3.
- [ ] Filter panel has a `html[data-bs-theme="dark"]` override block (no white surface in dark mode).
- [ ] Table uses `table align-middle w-100 mb-0`, not `table-striped`.
- [ ] Table ID has scoped header, row, and hover styles.
- [ ] Header uses uppercase 12px text with `#f8fafc` background.
- [ ] Currency and quantity columns are right-aligned.
- [ ] DataTables uses server-side processing, 25 rows per page, and Indonesian labels.
- [ ] Responsive wrapper uses `table-responsive custom-scrollbar`.
- [ ] No warehouse/action/modal columns are added when the report does not need them.
