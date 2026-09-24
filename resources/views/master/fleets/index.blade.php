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
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/sweetalert2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom-select2.css') }}">

    <style>
        /* ── Scoped Table Styling ── */
        #dt {
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        #dt thead th {
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

        #dt tbody td {
            padding: 11px 12px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            font-size: 12.5px;
            white-space: nowrap;
            vertical-align: middle;
        }

        #dt tbody tr {
            transition: background-color 0.15s ease;
        }

        #dt tbody tr:hover {
            background-color: #f8fafc !important;
        }

        /* ── Tombol Icon Ramping Header / Aksi Tabel ── */
        .btn-icon {
            border-radius: 8px !important;
            padding: 6px 10px;
            font-size: 13px;
            transition: all 0.2s ease;
        }

        .btn-icon:hover {
            transform: translateY(-1px);
        }

        /* ── Standar Filter Card Collapse (PHL §3) ── */
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

        .filter-card-header:hover {
            background: #f1f5f9;
        }

        .filter-card-heading {
            align-items: center;
            display: flex;
            gap: 8px;
        }

        .filter-card-heading i {
            color: #4f46e5;
            font-size: 17px;
        }

        .filter-card-heading strong {
            font-size: 13px;
            font-weight: 700;
        }

        .filter-card-heading small {
            color: #94a3b8;
            font-size: 11px;
            font-weight: 400;
        }

        .filter-card-chevron {
            transition: transform .2s ease;
        }

        .filter-card-header[aria-expanded="true"] .filter-card-chevron {
            transform: rotate(180deg);
        }

        .filter-card .filter-collapse {
            border-top: 1px solid #e2e8f0;
        }

        .filter-collapse-body {
            padding: 16px;
        }

        .filter-card .row {
            --bs-gutter-y: .75rem;
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
        #dt_wrapper .dataTables_filter input {
            border-radius: 8px;
            font-size: 12px;
        }

        #dt_wrapper .dataTables_length select {
            border-radius: 8px;
            font-size: 12px;
        }

        #dt_wrapper .dataTables_info,
        #dt_wrapper .dataTables_length,
        #dt_wrapper .dataTables_filter {
            color: #64748b;
            font-size: 12px;
        }

        /* ── Dark mode ─────────────────────────────── */
        html[data-bs-theme="dark"] #dt {
            border-color: var(--bs-border-color);
        }

        html[data-bs-theme="dark"] #dt thead th {
            background-color: var(--bs-tertiary-bg) !important;
            color: var(--bs-emphasis-color) !important;
            border-color: var(--bs-border-color) !important;
        }

        html[data-bs-theme="dark"] #dt tbody td {
            color: var(--bs-body-color) !important;
            border-color: var(--bs-border-color) !important;
        }

        html[data-bs-theme="dark"] #dt tbody tr:hover {
            background-color: var(--bs-tertiary-bg) !important;
        }

        /* ── Filter card (dark) ── */
        html[data-bs-theme="dark"] .filter-card {
            background: var(--bs-secondary-bg);
            border-color: var(--bs-border-color);
        }

        html[data-bs-theme="dark"] .filter-card-header {
            background: var(--bs-secondary-bg);
            color: var(--bs-body-color);
        }

        html[data-bs-theme="dark"] .filter-card-header:hover {
            background: var(--bs-tertiary-bg);
        }

        html[data-bs-theme="dark"] .filter-card .filter-collapse {
            border-top-color: var(--bs-border-color);
        }

        html[data-bs-theme="dark"] .filter-label {
            color: var(--bs-secondary-color);
        }

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
                        <i class="mdi mdi-truck text-primary fs-20"></i>
                        {{ $title }} Data
                    </h4>
                    <small class="text-muted">Master data armada operasional, spesifikasi teknis, kepemilikan, dan masa berlaku dokumen</small>
                </div>

                <div class="d-flex align-items-center gap-2">
                    {{-- Tombol Export Excel --}}
                    <a href="{{ route($view . 'export-excel') }}" target="_blank" id="export-excel"
                        class="btn btn-icon btn-sm bg-success-subtle" data-bs-toggle="tooltip" title="Export Excel">
                        <i class="mdi mdi-file-excel fs-14 text-success"></i>
                    </a>

                    {{-- Tombol Export PDF --}}
                    <a href="{{ route($view . 'export-pdf') }}" target="_blank" id="export-pdf"
                        class="btn btn-icon btn-sm bg-danger-subtle" data-bs-toggle="tooltip" title="Export PDF">
                        <i class="mdi mdi-file-pdf-box fs-14 text-danger"></i>
                    </a>

                    {{-- Tombol Hapus Terpilih --}}
                    <form action="{{ route($view . 'destroy-multiple') }}" method="POST" id="form-hapus-data" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1" id="btn-hapus-data"
                            style="border-radius: 8px; font-weight: 600; padding: 7px 14px;">
                            <i class="mdi mdi-trash-can-outline"></i> {{ __('general.delete_data') }}
                        </button>
                    </form>

                    {{-- Tombol Tambah Data: mengarah ke halaman FORM bukan modal --}}
                    <a href="{{ route($view . 'create') }}" class="btn btn-sm btn-primary d-flex align-items-center gap-1"
                        style="border-radius: 8px; font-weight: 600; padding: 7px 14px;">
                        <i class="mdi mdi-plus"></i> {{ __('general.add_data') }}
                    </a>
                </div>
            </div>

            <div class="card-body p-4">
                @include('partials.alert')

                {{-- Filter Bar (collapse, tertutup default) --}}
                <div class="filter-card">
                    <button type="button" class="filter-card-header" data-bs-toggle="collapse"
                        data-bs-target="#fleetFilterCollapse" aria-expanded="false"
                        aria-controls="fleetFilterCollapse">
                        <span class="filter-card-heading">
                            <i class="mdi mdi-filter-variant"></i>
                            <strong>Filter Data</strong>
                            <small>Gunakan filter untuk mempersempit daftar armada operasional</small>
                        </span>
                        <i class="mdi mdi-chevron-down filter-card-chevron"></i>
                    </button>

                    <div class="collapse filter-collapse" id="fleetFilterCollapse">
                        <div class="filter-collapse-body">
                            <div id="filterForm">
                                <div class="row g-3">
                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="fleetBrandCode">Merek Armada</label>
                                        <select class="form-select select2-filter" name="fleetBrandCode" id="fleetBrandCode">
                                            <option value="">Semua Merek Armada</option>
                                            @foreach ($brands as $item)
                                                <option value="{{ $item->code }}">{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="fleetTypeCode">Tipe Armada</label>
                                        <select class="form-select select2-filter" name="fleetTypeCode" id="fleetTypeCode">
                                            <option value="">Semua Tipe Armada</option>
                                            @foreach ($types as $item)
                                                <option value="{{ $item->code }}">{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="fleetCompanyCode">Perusahaan Armada</label>
                                        <select class="form-select select2-filter" name="fleetCompanyCode" id="fleetCompanyCode">
                                            <option value="">Semua Perusahaan</option>
                                            @foreach ($companies as $item)
                                                <option value="{{ $item->code }}">
                                                    {{ $item->name }}{{ $item->type ? ' (' . $item->type . ')' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="startDate">Dari Tanggal (Dibuat)</label>
                                        <input class="form-control filter-control" name="startDate" id="startDate"
                                            type="text" placeholder="Pilih tanggal mulai">
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="endDate">Sampai Tanggal (Dibuat)</label>
                                        <input class="form-control filter-control" name="endDate" id="endDate"
                                            type="text" placeholder="Pilih tanggal akhir">
                                    </div>

                                    <div class="col-xl-9 col-md-6 d-flex align-items-end justify-content-md-end gap-2">
                                        <button class="btn btn-filter-primary flex-grow-1 flex-md-grow-0" type="button"
                                            id="btnFilter">
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

                {{-- Table (Tanpa striped, border halus, thead uppercase) --}}
                <div class="table-responsive custom-scrollbar">
                    <table class="table align-middle w-100 mb-0" id="dt">
                        <thead>
                            <tr>
                                <th style="width: 120px;" class="text-center">Aksi</th>
                                <th style="width: 50px;" class="text-center">No</th>
                                <th style="width: 130px;">{{ __('menu_fleet.plate_number') }}</th>
                                <th style="width: 110px;">Kode</th>
                                <th style="width: 130px;" class="text-center">{{ __('menu_fleet.vehicle_registration_due_date') }}</th>
                                <th style="width: 180px;">{{ __('menu_fleet.company') }}</th>
                                <th style="width: 90px;" class="text-center">{{ __('menu_fleet.company_type') }}</th>
                                <th style="width: 180px;">{{ __('menu_fleet.address') }}</th>
                                <th style="width: 120px;">{{ __('menu_fleet.brand') }}</th>
                                <th style="width: 120px;">{{ __('menu_fleet.type') }}</th>
                                <th style="width: 130px;">{{ __('menu_fleet.frame_number') }}</th>
                                <th style="width: 130px;">{{ __('menu_fleet.engine_number') }}</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Modal Image Preview --}}
        <div class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow" style="border-radius: 16px; overflow: hidden;">
                    <div class="modal-header bg-white py-3 border-bottom">
                        <h5 class="modal-title fw-bold text-dark" id="imageModalLabel">Preview Gambar</h5>
                        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-3 text-center bg-light">
                        <img id="modalImage" src="" alt="Image Preview" class="img-fluid rounded" style="max-height: 70vh; object-fit: contain;" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Form Single Delete --}}
    <form id="delete-form" method="post">
        @csrf
        @method('DELETE')
    </form>
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

    <script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>

    <script>
        let selectedFleets = [];
        let filterStartPicker;
        let filterEndPicker;

        $(document).ready(function() {
            const $filterForm = $('#filterForm');

            // 1. Inisialisasi Select2
            $('.select2-filter').select2({
                placeholder: 'Semua Opsi',
                allowClear: true,
                width: '100%'
            });

            // 2. Inisialisasi Flatpickr (format standar backend Y-m-d)
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

            // 3. Kumpulan parameter filter aktif
            function getFilters() {
                return {
                    fleetBrandCode: $('#fleetBrandCode').val() || '',
                    fleetTypeCode: $('#fleetTypeCode').val() || '',
                    fleetCompanyCode: $('#fleetCompanyCode').val() || '',
                    startDate: $('#startDate').val() || '',
                    endDate: $('#endDate').val() || ''
                };
            }

            // 4. Sinkronisasi parameter filter ke tombol Export Excel & PDF
            function syncExportUrls() {
                const params = new URLSearchParams();
                Object.entries(getFilters()).forEach(function(entry) {
                    if (entry[1]) params.set(entry[0], entry[1]);
                });

                const qs = params.toString() ? '?' + params.toString() : '';
                $('#export-excel').attr('href', "{{ route($view . 'export-excel') }}" + qs);
                $('#export-pdf').attr('href', "{{ route($view . 'export-pdf') }}" + qs);
            }

            // 5. Inisialisasi DataTables
            const table = $('#dt').DataTable({
                processing: true,
                serverSide: true,
                destroy: true,
                pageLength: 25,
                ajax: {
                    url: "{{ route('dt.fleets') }}",
                    data: function(d) {
                        Object.assign(d, getFilters());
                    }
                },
                columns: [
                    { data: 'action', className: 'text-center align-middle', orderable: false, searchable: false },
                    { data: 'DT_RowIndex', className: 'text-center align-middle', orderable: false, searchable: false },
                    { data: 'plateNumber', className: 'align-middle' },
                    { data: 'code', className: 'align-middle' },
                    { data: 'vehicleRegistrationDueDate', className: 'text-center align-middle' },
                    { data: 'company.name', className: 'align-middle' },
                    { data: 'company.type', className: 'text-center align-middle' },
                    { data: 'company.address', className: 'align-middle' },
                    { data: 'brand.name', className: 'align-middle' },
                    { data: 'type.name', className: 'align-middle' },
                    { data: 'frameNumber', className: 'align-middle' },
                    { data: 'engineNumber', className: 'align-middle' }
                ],
                order: [
                    [2, 'asc']
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

            function reloadWithFilters() {
                syncExportUrls();
                table.ajax.reload(null, true);
            }

            // 6. Submit Filter (tombol & Enter)
            $('#btnFilter').on('click', reloadWithFilters);
            $filterForm.on('keydown', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    reloadWithFilters();
                }
            });

            // 7. Reset Filter
            $('#btnResetFilter').on('click', function() {
                $filterForm.find('input').val('');
                $('.select2-filter').val('').trigger('change');
                if (filterStartPicker) {
                    filterStartPicker.clear();
                    filterStartPicker.set('maxDate', null);
                }
                if (filterEndPicker) {
                    filterEndPicker.clear();
                    filterEndPicker.set('minDate', null);
                }
                reloadWithFilters();
            });

            // Sinkronkan URL export saat pertama dimuat
            syncExportUrls();

            // 7. Checkbox state & draw event
            $(document).on('change', '.fleet-checkbox', function() {
                const fleetId = $(this).val();

                if ($(this).is(':checked')) {
                    if (!selectedFleets.includes(fleetId)) {
                        selectedFleets.push(fleetId);
                    }
                } else {
                    selectedFleets = selectedFleets.filter(id => id !== fleetId);
                }
            });

            $('#dt').on('draw.dt', function() {
                $('.fleet-checkbox').each(function() {
                    const fleetId = $(this).val();
                    if (selectedFleets.includes(fleetId)) {
                        $(this).prop('checked', true);
                    }
                });
            });

            // 8. Hapus multiple data
            $('#btn-hapus-data').on('click', function(e) {
                e.preventDefault();
                const form = $('#form-hapus-data');

                if (selectedFleets.length === 0) {
                    swal({
                        title: "{{ __('general.warning') }}",
                        text: "{{ __('menu_fleet.delete_validation') }}",
                        icon: "warning",
                    });
                } else {
                    swal({
                        title: "{{ __('general.are_you_sure') }}",
                        text: "{{ __('general.want_to_delete_this_data') }}",
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    }).then((willDelete) => {
                        if (willDelete) {
                            form.find('input[name="fleet[]"]').remove();
                            selectedFleets.forEach(function(id) {
                                $('<input>').attr({
                                    type: 'hidden',
                                    name: 'fleet[]',
                                    value: id
                                }).appendTo(form);
                            });
                            form.submit();
                        } else {
                            swal("{{ __('general.your_data_is_save') }}");
                        }
                    });
                }
            });
        });

        // 9. Modal Image Preview
        function showModal(imageUrl) {
            document.getElementById('modalImage').src = imageUrl;
            $('.bd-example-modal-xl').modal('show');
        }

        // 10. Single Delete Data
        function deleteData(uuid) {
            var url = '{{ route($view . 'index') }}/' + uuid;
            $('#delete-form').attr('action', url);

            swal({
                title: "{{ __('general.are_you_sure') }}",
                text: "{{ __('general.want_to_delete_this_data') }}",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    $('#delete-form').submit();
                } else {
                    swal("{{ __('general.your_data_is_save') }}");
                }
            });
        }
    </script>
@endpush
