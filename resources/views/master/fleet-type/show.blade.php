@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Master',
    'secondSegment' => $title,
])

@push('style')
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/libs/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/libs/datatables.net-keytable-bs5/css/keyTable.bootstrap5.min.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/libs/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/libs/datatables.net-select-bs5/css/select.bootstrap5.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom-select2.css') }}">

    <style>
        /* ── Scoped Table Styling (Tanpa Striped) ── */
        #dt-fleets {
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        #dt-fleets thead th {
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

        #dt-fleets tbody td {
            padding: 11px 12px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            font-size: 12.5px;
            white-space: nowrap;
            vertical-align: middle;
        }

        #dt-fleets tbody tr {
            transition: background-color 0.15s ease;
        }

        #dt-fleets tbody tr:hover {
            background-color: #f8fafc !important;
        }

        /* ── Tombol Icon Ramping Header ── */
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

        .summary-primary {
            background: linear-gradient(135deg, #eef2ff, #e0e7ff);
            border-color: #c7d2fe;
            color: #3730a3;
        }

        .summary-success {
            background: linear-gradient(135deg, #ecfdf5, #d1fae5);
            border-color: #a7f3d0;
            color: #065f46;
        }

        .summary-warning {
            background: linear-gradient(135deg, #fefce8, #fef9c3);
            border-color: #fde68a;
            color: #92400e;
        }

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

        /* Select2 Theme Matching Seragam 38px */
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

        /* ── DataTables Inputs & Pagination ── */
        #dt-fleets_wrapper .dataTables_filter input {
            border-radius: 8px;
            font-size: 12px;
        }

        #dt-fleets_wrapper .dataTables_length select {
            border-radius: 8px;
            font-size: 12px;
        }

        #dt-fleets_wrapper .dataTables_info,
        #dt-fleets_wrapper .dataTables_length,
        #dt-fleets_wrapper .dataTables_filter {
            color: #64748b;
            font-size: 12px;
        }

        /* ── Dark mode ─────────────────────────────── */
        html[data-bs-theme="dark"] #dt-fleets {
            border-color: var(--bs-border-color);
        }

        html[data-bs-theme="dark"] #dt-fleets thead th {
            background-color: var(--bs-tertiary-bg) !important;
            color: var(--bs-emphasis-color) !important;
            border-color: var(--bs-border-color) !important;
        }

        html[data-bs-theme="dark"] #dt-fleets tbody td {
            color: var(--bs-body-color) !important;
            border-color: var(--bs-border-color) !important;
        }

        html[data-bs-theme="dark"] #dt-fleets tbody tr:hover {
            background-color: var(--bs-tertiary-bg) !important;
        }

        /* Inline white banner card → dark surface. */
        html[data-bs-theme="dark"] .card[style*="background: #ffffff"] {
            background-color: var(--bs-card-bg) !important;
            border-color: var(--bs-border-color) !important;
        }

        html[data-bs-theme="dark"] .summary-primary {
            background: var(--bs-primary-bg-subtle) !important;
            border-color: var(--bs-primary-border-subtle) !important;
            color: var(--bs-primary-text-emphasis) !important;
        }

        html[data-bs-theme="dark"] .summary-success {
            background: var(--bs-success-bg-subtle) !important;
            border-color: var(--bs-success-border-subtle) !important;
            color: var(--bs-success-text-emphasis) !important;
        }

        html[data-bs-theme="dark"] .summary-warning {
            background: var(--bs-warning-bg-subtle) !important;
            border-color: var(--bs-warning-border-subtle) !important;
            color: var(--bs-warning-text-emphasis) !important;
        }
    </style>
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
            {{-- Card Header --}}
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center"
                style="border-color: #e2e8f0;">
                <div>
                    <h4 class="mb-1 fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="mdi mdi-truck-cargo-container text-primary fs-20"></i>
                        Detail Tipe Armada: {{ $type->name }}
                    </h4>
                    <small class="text-muted">Profil tipe armada dan daftar armada terdaftar dengan tipe ini</small>
                </div>

                <div class="d-flex align-items-center gap-2">
                    {{-- Tombol Export Excel --}}
                    <a href="{{ route($view . 'detail-export-excel', $type->id) }}" target="_blank" id="export-excel"
                        class="btn btn-icon btn-sm bg-success-subtle" data-bs-toggle="tooltip" title="Export Excel">
                        <i class="mdi mdi-file-excel fs-14 text-success"></i>
                    </a>

                    {{-- Tombol Export PDF --}}
                    <a href="{{ route($view . 'detail-export-pdf', $type->id) }}" target="_blank" id="export-pdf"
                        class="btn btn-icon btn-sm bg-danger-subtle" data-bs-toggle="tooltip" title="Export PDF">
                        <i class="mdi mdi-file-pdf-box fs-14 text-danger"></i>
                    </a>

                    {{-- Tombol Kembali --}}
                    <a href="{{ route($view . 'index') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1"
                        style="border-radius: 8px; font-weight: 600; padding: 7px 14px;">
                        <i class="mdi mdi-arrow-left"></i> {{ __('general.back_to_list') }}
                    </a>
                </div>
            </div>

            <div class="card-body p-4">
                @include('partials.alert')

                {{-- 1. Banner Profil Entitas --}}
                <div class="card border-0 mb-4"
                    style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 12px;">
                    <div class="card-body p-3">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-badge-entity">
                                    <i class="mdi mdi-truck-cargo-container fs-22 text-white"></i>
                                </div>
                                <div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fs-16 fw-bold text-dark">{{ $type->name }}</span>
                                        <span class="badge bg-primary-subtle text-primary px-2 py-1"
                                            style="border-radius: 6px; font-weight: 600; font-size: 11px;">
                                            Tipe Armada
                                        </span>
                                    </div>
                                    <div class="text-muted small mt-1">
                                        <i class="mdi mdi-identifier me-1"></i>Kode Tipe: <span class="font-monospace fw-semibold">{{ $type->code }}</span>
                                        <span class="mx-2">•</span>
                                        <i class="mdi mdi-calendar-check me-1"></i>Terdaftar: <span class="font-monospace">{{ $type->created_at ? $type->created_at->format('d/m/Y H:i') : '-' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div id="filterPeriodBadge" class="d-none align-items-center gap-2 bg-light px-3 py-2 rounded-3 border" style="font-size: 12.5px;">
                                <i class="mdi mdi-calendar-range text-primary fs-16"></i>
                                <span>Filter Periode: <strong id="filterPeriodText">-</strong></span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. Kartu KPI Summary (Summary Tiles) --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="summary-card summary-primary">
                            <i class="mdi mdi-truck float-end fs-24 opacity-50"></i>
                            <h5>Total Armada</h5>
                            <h3>{{ number_format($totalFleets, 0, ',', '.') }} <span class="fs-14 fw-normal opacity-75">Unit</span></h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="summary-card summary-success">
                            <i class="mdi mdi-truck-flatbed float-end fs-24 opacity-50"></i>
                            <h5>Merek Armada Terdaftar</h5>
                            <h3>{{ number_format($totalBrands, 0, ',', '.') }} <span class="fs-14 fw-normal opacity-75">Merek</span></h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="summary-card summary-warning">
                            <i class="mdi mdi-office-building float-end fs-24 opacity-50"></i>
                            <h5>Perusahaan Armada</h5>
                            <h3>{{ number_format($totalCompanies, 0, ',', '.') }} <span class="fs-14 fw-normal opacity-75">Perusahaan</span></h3>
                        </div>
                    </div>
                </div>

                {{-- 3. Filter Bar Terbuka (Ukuran Seragam Presisi 38px) --}}
                <div class="card border-0 mb-4"
                    style="background: var(--bs-tertiary-bg); border: 1px solid var(--bs-border-color) !important; border-radius: 12px;">
                    <div class="card-body p-3">
                        <form id="filterForm">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-3">
                                    <label class="filter-label" for="fleetBrandCode">Merek Armada</label>
                                    <select class="form-select select2-filter" name="fleetBrandCode"
                                        id="fleetBrandCode" style="width: 100%;">
                                        <option value="">Semua Merek</option>
                                        @foreach ($fleetBrands as $item)
                                            <option value="{{ $item->code }}">{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="filter-label" for="fleetCompanyCode">Perusahaan Armada</label>
                                    <select class="form-select select2-filter" name="fleetCompanyCode"
                                        id="fleetCompanyCode" style="width: 100%;">
                                        <option value="">Semua Perusahaan</option>
                                        @foreach ($fleetCompanies as $item)
                                            <option value="{{ $item->code }}">
                                                {{ $item->name }}{{ $item->type ? ' (' . $item->type . ')' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="filter-label" for="startDate">Dari Tanggal</label>
                                    <input class="form-control filter-control" name="startDate" id="startDate"
                                        type="text" placeholder="Pilih Tanggal Mulai">
                                </div>

                                <div class="col-md-2">
                                    <label class="filter-label" for="endDate">Sampai Tanggal</label>
                                    <input class="form-control filter-control" name="endDate" id="endDate"
                                        type="text" placeholder="Pilih Tanggal Akhir">
                                </div>

                                <div class="col-md-2 d-flex gap-2">
                                    <button class="btn btn-filter-primary flex-grow-1" type="submit" id="btnFilter">
                                        <i class="mdi mdi-filter fs-14"></i> Filter
                                    </button>
                                    <button type="button" class="btn btn-filter-reset" id="btnResetFilter"
                                        data-bs-toggle="tooltip" title="Reset Filter">
                                        <i class="mdi mdi-refresh fs-16"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- 4. Tabel Rincian Armada (Tanpa striped) --}}
                <div class="table-responsive custom-scrollbar">
                    <table class="table align-middle w-100 mb-0" id="dt-fleets">
                        <thead>
                            <tr>
                                <th style="width: 5%;" class="text-center">No</th>
                                <th style="width: 15%;">Nomor Polisi (Plat)</th>
                                <th style="width: 12%;">Kode Armada</th>
                                <th style="width: 15%;">Merek Armada</th>
                                <th style="width: 20%;">Perusahaan Armada</th>
                                <th style="width: 8%;" class="text-center">Tahun</th>
                                <th style="width: 12%;">No. Mesin</th>
                                <th style="width: 13%;">No. Rangka</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>

    <!-- dataTables.keyTable -->
    <script src="{{ asset('assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-keytable-bs5/js/keyTable.bootstrap5.min.js') }}"></script>

    <!-- dataTable.responsive -->
    <script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>

    <!-- dataTables.select -->
    <script src="{{ asset('assets/libs/datatables.net-select/js/dataTables.select.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-select-bs5/js/select.bootstrap5.min.js') }}"></script>

    <!-- Select2 & Flatpickr -->
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2-custom.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/custom-flatpickr.js') }}"></script>

    <script>
        let filterStartPicker;
        let filterEndPicker;

        $(document).ready(function() {
            // 1. Inisialisasi Select2
            $('#fleetBrandCode').select2({
                placeholder: 'Semua Merek',
                allowClear: true
            });

            $('#fleetCompanyCode').select2({
                placeholder: 'Semua Perusahaan',
                allowClear: true
            });

            // 2. Inisialisasi Flatpickr
            filterStartPicker = flatpickr('#startDate', {
                dateFormat: 'Y-m-d',
                allowInput: true,
                onChange: function(selectedDates, dateStr) {
                    if (filterEndPicker) filterEndPicker.set('minDate', dateStr || null);
                    updatePeriodBadge();
                }
            });

            filterEndPicker = flatpickr('#endDate', {
                dateFormat: 'Y-m-d',
                allowInput: true,
                onChange: function(selectedDates, dateStr) {
                    if (filterStartPicker) filterStartPicker.set('maxDate', dateStr || null);
                    updatePeriodBadge();
                }
            });

            function updatePeriodBadge() {
                const start = $('#startDate').val();
                const end = $('#endDate').val();
                if (start || end) {
                    const startFmt = start ? formatDate(start) : 'Awal';
                    const endFmt = end ? formatDate(end) : 'Sekarang';
                    $('#filterPeriodText').text(startFmt + ' s/d ' + endFmt);
                    $('#filterPeriodBadge').removeClass('d-none').addClass('d-flex');
                } else {
                    $('#filterPeriodBadge').removeClass('d-flex').addClass('d-none');
                }
            }

            function formatDate(str) {
                const parts = str.split('-');
                if (parts.length === 3) {
                    return parts[2] + '/' + parts[1] + '/' + parts[0];
                }
                return str;
            }

            // 3. Sinkronisasi Tombol Export Excel & PDF
            function syncExportUrls() {
                const params = new URLSearchParams();
                const fleetBrandCode = $('#fleetBrandCode').val();
                const fleetCompanyCode = $('#fleetCompanyCode').val();
                const startDate = $('#startDate').val();
                const endDate = $('#endDate').val();

                if (fleetBrandCode) params.set('fleetBrandCode', fleetBrandCode);
                if (fleetCompanyCode) params.set('fleetCompanyCode', fleetCompanyCode);
                if (startDate) params.set('startDate', startDate);
                if (endDate) params.set('endDate', endDate);

                const qs = params.toString() ? '?' + params.toString() : '';
                $('#export-pdf').attr('href', "{{ route($view . 'detail-export-pdf', $type->id) }}" + qs);
                $('#export-excel').attr('href', "{{ route($view . 'detail-export-excel', $type->id) }}" + qs);
            }

            // 4. DataTables (Tanpa Striped, ServerSide, Bahasa Indonesia)
            const table = $('#dt-fleets').DataTable({
                processing: true,
                serverSide: true,
                destroy: true,
                pageLength: 25,
                ajax: {
                    url: "{{ route('dt.fleet-type.fleets', $type->id) }}",
                    data: function(d) {
                        d.fleetBrandCode = $('#fleetBrandCode').val();
                        d.fleetCompanyCode = $('#fleetCompanyCode').val();
                        d.startDate = $('#startDate').val();
                        d.endDate = $('#endDate').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        className: 'text-center align-middle'
                    },
                    {
                        data: 'plateNumber',
                        className: 'align-middle'
                    },
                    {
                        data: 'code',
                        className: 'align-middle'
                    },
                    {
                        data: 'brandName',
                        className: 'align-middle'
                    },
                    {
                        data: 'companyName',
                        className: 'align-middle'
                    },
                    {
                        data: 'year',
                        className: 'text-center align-middle'
                    },
                    {
                        data: 'engineNumber',
                        className: 'align-middle'
                    },
                    {
                        data: 'frameNumber',
                        className: 'align-middle'
                    }
                ],
                columnDefs: [{
                        searchable: false,
                        targets: [0, 5, 6, 7]
                    },
                    {
                        orderable: false,
                        targets: [0]
                    }
                ],
                order: [
                    [1, 'asc']
                ],
                language: {
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                    infoEmpty: 'Tidak ada armada',
                    zeroRecords: 'Armada tidak ditemukan',
                    processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Memuat armada...'
                }
            });

            // 5. Submit Filter Form
            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                syncExportUrls();
                table.ajax.reload();
            });

            // 6. Reset Filter Form
            $('#btnResetFilter').on('click', function() {
                $('#filterForm')[0].reset();
                $('#fleetBrandCode').val('').trigger('change');
                $('#fleetCompanyCode').val('').trigger('change');
                if (filterStartPicker) filterStartPicker.clear();
                if (filterEndPicker) filterEndPicker.clear();
                updatePeriodBadge();
                syncExportUrls();
                table.ajax.reload();
            });
        });
    </script>
@endpush
