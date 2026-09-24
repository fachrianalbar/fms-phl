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
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom-select2.css') }}">

    <style>
        /* ===== Custom Card & Header ===== */
        .master-card {
            border: 1px solid #e8ecf3;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(30, 41, 59, 0.04);
            background: #fff;
            overflow: hidden;
            transition: box-shadow 0.2s ease;
        }
        .master-card:hover {
            box-shadow: 0 4px 16px rgba(30, 41, 59, 0.07);
        }
        .master-card .card-header {
            background: #fbfcfe;
            border-bottom: 1px solid #edf1f7;
            padding: 1.1rem 1.4rem;
        }
        .master-card .card-body {
            padding: 1.4rem;
        }

        .header-icon-employee {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            flex-shrink: 0;
            background: linear-gradient(135deg, #7669D3 0%, #5a4db8 100%);
            color: #fff;
            box-shadow: 0 3px 8px rgba(118, 105, 211, 0.3);
        }

        /* ===== Stat Metric Cards ===== */
        .metric-card {
            border: 1px solid #e8ecf3;
            border-radius: 10px;
            background: #ffffff;
            padding: 0.9rem 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.85rem;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.03);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .metric-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
        }
        .metric-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }
        .metric-icon-total { background: #f1f5f9; color: #475569; }
        .metric-icon-driver { background: #eef2ff; color: #4f46e5; }
        .metric-icon-active { background: #ecfdf5; color: #059669; }
        .metric-icon-inactive { background: #fef2f2; color: #dc2626; }
        .metric-value {
            font-size: 1.35rem;
            font-weight: 800;
            line-height: 1.2;
            color: #1e293b;
        }
        .metric-label {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
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

        /* ===== Modern Table Styling ===== */
        .employee-table-wrapper {
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #e8ecf3;
            background: #fff;
        }
        table.dataTable {
            margin-top: 0 !important;
            margin-bottom: 0 !important;
            border-collapse: collapse !important;
        }
        table.dataTable thead th {
            background: linear-gradient(135deg, #f8faff 0%, #eef2f9 100%) !important;
            border-bottom: 2px solid #dce3ed !important;
            font-size: 0.76rem !important;
            font-weight: 700 !important;
            color: #475569 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            padding: 0.75rem 0.8rem !important;
            vertical-align: middle !important;
        }
        table.dataTable tbody tr {
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.15s ease;
        }
        table.dataTable tbody tr:hover {
            background: #f9fbff !important;
        }
        table.dataTable tbody td {
            padding: 0.65rem 0.8rem !important;
            vertical-align: middle !important;
            font-size: 0.88rem !important;
        }

        /* Status Toggle Switch Styling */
        .form-check-input.status-toggle-switch {
            width: 2.3em;
            height: 1.25em;
            cursor: pointer;
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }
        .form-check-input.status-toggle-switch:checked {
            background-color: #10b981;
            border-color: #10b981;
        }

        /* Action Buttons */
        .btn-add-employee {
            background: linear-gradient(135deg, #7669D3 0%, #5a4db8 100%);
            border: none;
            color: #fff;
            font-weight: 700;
            font-size: 0.86rem;
            padding: 0.5rem 1.15rem;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(118, 105, 211, 0.3);
            text-decoration: none;
        }
        .btn-add-employee:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(118, 105, 211, 0.4);
            color: #fff;
        }

        /* ── Dark mode ─────────────────────────────── */
        html[data-bs-theme="dark"] .master-card {
            background-color: var(--bs-card-bg) !important;
            border-color: var(--bs-border-color) !important;
        }

        html[data-bs-theme="dark"] .master-card .card-header {
            background-color: var(--bs-tertiary-bg) !important;
            border-color: var(--bs-border-color) !important;
        }

        html[data-bs-theme="dark"] .metric-card {
            background-color: var(--bs-card-bg) !important;
            border-color: var(--bs-border-color) !important;
        }

        html[data-bs-theme="dark"] .metric-value {
            color: var(--bs-heading-color) !important;
        }

        html[data-bs-theme="dark"] .metric-label {
            color: var(--bs-secondary-color) !important;
        }

        html[data-bs-theme="dark"] .metric-icon-total {
            background-color: var(--bs-tertiary-bg) !important;
            color: var(--bs-body-color) !important;
        }

        html[data-bs-theme="dark"] .metric-icon-driver {
            background-color: var(--bs-primary-bg-subtle) !important;
            color: var(--bs-primary-text-emphasis) !important;
        }

        html[data-bs-theme="dark"] .metric-icon-active {
            background-color: var(--bs-success-bg-subtle) !important;
            color: var(--bs-success-text-emphasis) !important;
        }

        html[data-bs-theme="dark"] .metric-icon-inactive {
            background-color: var(--bs-danger-bg-subtle) !important;
            color: var(--bs-danger-text-emphasis) !important;
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
        html[data-bs-theme="dark"] .filter-card .form-control,
        html[data-bs-theme="dark"] .filter-card .form-select {
            background-color: var(--bs-tertiary-bg) !important;
            border-color: var(--bs-border-color) !important;
            color: var(--bs-body-color) !important;
        }

        html[data-bs-theme="dark"] .btn-filter-reset {
            background: var(--bs-tertiary-bg) !important;
            border-color: var(--bs-border-color) !important;
            color: var(--bs-body-color) !important;
        }

        html[data-bs-theme="dark"] .employee-table-wrapper {
            background-color: var(--bs-card-bg) !important;
            border-color: var(--bs-border-color) !important;
        }

        html[data-bs-theme="dark"] table.dataTable thead th {
            background: var(--bs-tertiary-bg) !important;
            color: var(--bs-emphasis-color) !important;
            border-color: var(--bs-border-color) !important;
        }

        html[data-bs-theme="dark"] table.dataTable tbody tr:hover {
            background-color: var(--bs-tertiary-bg) !important;
        }
    </style>
@endpush

@section('content')
    <div class="col-sm-12">

        <!-- Stat Metric Cards -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="metric-card">
                    <div class="metric-icon metric-icon-total">
                        <i class="mdi mdi-account-multiple-outline"></i>
                    </div>
                    <div>
                        <div class="metric-value">{{ $counts['total'] ?? 0 }}</div>
                        <div class="metric-label">Total Karyawan</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="metric-card">
                    <div class="metric-icon metric-icon-driver">
                        <i class="mdi mdi-steering"></i>
                    </div>
                    <div>
                        <div class="metric-value">{{ $counts['driver'] ?? 0 }}</div>
                        <div class="metric-label">Total Supir / Driver</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="metric-card">
                    <div class="metric-icon metric-icon-active">
                        <i class="mdi mdi-account-check-outline"></i>
                    </div>
                    <div>
                        <div class="metric-value text-success" id="metric-active-count">{{ $counts['driver_active'] ?? 0 }}</div>
                        <div class="metric-label text-success">Supir Aktif</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="metric-card">
                    <div class="metric-icon metric-icon-inactive">
                        <i class="mdi mdi-account-off-outline"></i>
                    </div>
                    <div>
                        <div class="metric-value text-danger" id="metric-inactive-count">{{ $counts['driver_inactive'] ?? 0 }}</div>
                        <div class="metric-label text-danger">Supir Nonaktif</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Card -->
        <div class="card master-card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3">
                    <span class="header-icon-employee">
                        <i class="mdi mdi-account-group-outline"></i>
                    </span>
                    <div>
                        <h5 class="m-0 fw-bold text-dark">{{ $title }} Data</h5>
                        <small class="text-muted">Kelola data karyawan serta status ketersediaan supir armada</small>
                    </div>
                </div>

                <a href="{{ route($view . 'create') }}" class="btn-add-employee">
                    <i class="mdi mdi-plus-circle-outline fs-16"></i>
                    {{ __('general.add_data') }}
                </a>
            </div>

            <div class="card-body">
                @include('partials.alert')

                {{-- Filter Bar (collapse, tertutup default) --}}
                <div class="filter-card">
                    <button type="button" class="filter-card-header" data-bs-toggle="collapse"
                        data-bs-target="#employeeFilterCollapse" aria-expanded="false"
                        aria-controls="employeeFilterCollapse">
                        <span class="filter-card-heading">
                            <i class="mdi mdi-filter-variant"></i>
                            <strong>Filter Data</strong>
                            <small>Gunakan filter untuk mempersempit daftar karyawan</small>
                        </span>
                        <i class="mdi mdi-chevron-down filter-card-chevron"></i>
                    </button>

                    <div class="collapse filter-collapse" id="employeeFilterCollapse">
                        <div class="filter-collapse-body">
                            <div id="filterForm">
                                <div class="row g-3">
                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="filter_position">Jabatan / Posisi</label>
                                        <select class="form-select select2-filter" id="filter_position" name="positionCode">
                                            <option value="">Semua Posisi</option>
                                            @foreach ($positions as $pos)
                                                <option value="{{ $pos->code }}">{{ $pos->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="filter_status">Status</label>
                                        <select class="form-select select2-filter" id="filter_status" name="status">
                                            <option value="">Semua Status</option>
                                            <option value="1">Aktif Saja</option>
                                            <option value="0">Nonaktif Saja</option>
                                        </select>
                                    </div>

                                    <div class="col-xl-6 col-md-12 d-flex align-items-end justify-content-md-end gap-2">
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

                <!-- Modern Table Container -->
                <div class="employee-table-wrapper">
                    <div class="table-responsive custom-scrollbar">
                        <table class="table table-hover w-100 align-middle" id="dt">
                            <thead>
                                <tr>
                                    <th style="width: 105px;" class="text-center">Aksi</th>
                                    <th style="width: 50px;" class="text-center">No</th>
                                    <th style="width: 130px;">Kode</th>
                                    <th>Nama & Kontak</th>
                                    <th style="width: 150px;" class="text-center">Jabatan</th>
                                    <th style="width: 155px;" class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Hidden Delete Form -->
    <form id="delete-form" method="post">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('script')
    <script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2-custom.js') }}"></script>
    <script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>

    <script>
        let table;

        $(document).ready(function() {
            const $filterForm = $('#filterForm');

            // 1. Inisialisasi Select2
            $('#filter_position').select2({
                placeholder: 'Semua Posisi',
                allowClear: true,
                width: '100%'
            });

            $('#filter_status').select2({
                placeholder: 'Semua Status',
                allowClear: true,
                width: '100%'
            });

            // 2. Kumpulan parameter filter aktif
            function getFilters() {
                return {
                    positionCode: $('#filter_position').val() || '',
                    status: $('#filter_status').val() || ''
                };
            }

            // 3. Sinkronisasi parameter filter ke tombol Export Excel & PDF jika ada
            function syncExportUrls() {
                if (!$('#export-excel').length && !$('#export-pdf').length) return;
                const params = new URLSearchParams();
                Object.entries(getFilters()).forEach(function(entry) {
                    if (entry[1]) params.set(entry[0], entry[1]);
                });
                const qs = params.toString() ? '?' + params.toString() : '';
                if ($('#export-excel').length) {
                    const baseExcel = $('#export-excel').attr('href').split('?')[0];
                    $('#export-excel').attr('href', baseExcel + qs);
                }
                if ($('#export-pdf').length) {
                    const basePdf = $('#export-pdf').attr('href').split('?')[0];
                    $('#export-pdf').attr('href', basePdf + qs);
                }
            }

            // 4. Inisialisasi DataTables
            table = $('#dt').DataTable({
                processing: true,
                serverSide: true,
                destroy: true,
                ajax: {
                    url: "{{ route('dt.employee') }}",
                    data: function(d) {
                        Object.assign(d, getFilters());
                    }
                },
                columns: [
                    { data: 'action', className: 'text-center' },
                    { data: 'DT_RowIndex', className: 'text-center fw-bold text-muted fs-12' },
                    { data: 'code' },
                    { data: 'name' },
                    { data: 'position_badge', className: 'text-center' },
                    { data: 'status_badge', className: 'text-center' },
                ],
                columnDefs: [
                    { searchable: false, orderable: false, targets: [0, 1, 4, 5] }
                ],
                order: [
                    [3, 'asc']
                ],
                language: {
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                    infoEmpty: 'Tidak ada data',
                    zeroRecords: 'Data tidak ditemukan',
                    processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Memuat data...'
                },
                drawCallback: function() {
                    // Re-initialize Bootstrap tooltips if available
                    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                        tooltipTriggerList.map(function(tooltipTriggerEl) {
                            return new bootstrap.Tooltip(tooltipTriggerEl);
                        });
                    }
                }
            });

            function reloadWithFilters() {
                syncExportUrls();
                table.ajax.reload(null, true);
            }

            // 5. Submit Filter (tombol & Enter)
            $('#btnFilter').on('click', reloadWithFilters);
            $filterForm.on('keydown', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    reloadWithFilters();
                }
            });

            // 6. Reset Filter
            $('#btnResetFilter').on('click', function() {
                $('.select2-filter').val('').trigger('change');
                reloadWithFilters();
            });

            // Sinkronkan URL export saat pertama dimuat
            syncExportUrls();

            // Event delegation for toggle switch clicks
            $(document).on('change', '.status-toggle-switch', function(e) {
                const switchElem = $(this);
                const id = switchElem.data('id');
                const name = switchElem.data('name');
                const currentStatus = parseInt(switchElem.data('status'));

                // Prevent instant toggle state change until confirmed
                const intendedCheck = switchElem.is(':checked');
                switchElem.prop('checked', !intendedCheck);

                triggerToggleStatus(id, name, currentStatus);
            });
        });

        // Toggle Status via Button or Switch with Confirmation
        function toggleStatus(id, name, currentStatus) {
            triggerToggleStatus(id, name, currentStatus);
        }

        function triggerToggleStatus(id, name, currentStatus) {
            const isCurrentlyActive = (parseInt(currentStatus) === 1);
            const actionText = isCurrentlyActive ? 'nonaktifkan' : 'aktifkan';
            const confirmTitle = isCurrentlyActive ? `Nonaktifkan Supir ${name}?` : `Aktifkan Supir ${name}?`;
            const confirmText = isCurrentlyActive
                ? 'Supir yang nonaktif tidak akan dapat dipilih pada penugasan armada dan order operasional.'
                : 'Supir akan kembali aktif dan tersedia untuk penugasan order dan armada.';

            swal({
                title: confirmTitle,
                text: confirmText,
                icon: "warning",
                buttons: {
                    cancel: {
                        text: "Batal",
                        visible: true,
                        className: "btn-secondary"
                    },
                    confirm: {
                        text: isCurrentlyActive ? "Ya, Nonaktifkan" : "Ya, Aktifkan",
                        visible: true,
                        className: isCurrentlyActive ? "btn-danger" : "btn-primary"
                    }
                },
                dangerMode: isCurrentlyActive,
            }).then((willChange) => {
                if (willChange) {
                    $.ajax({
                        url: '{{ url("master/employee") }}/' + id + '/toggle-status',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                swal({
                                    title: "Berhasil!",
                                    text: response.message,
                                    icon: "success",
                                    timer: 2000,
                                    buttons: false
                                });
                                table.ajax.reload(null, false); // reload without resetting pagination
                            } else {
                                swal({
                                    title: "Peringatan",
                                    text: response.message,
                                    icon: "warning"
                                });
                            }
                        },
                        error: function(xhr) {
                            const msg = (xhr.responseJSON && xhr.responseJSON.message)
                                ? xhr.responseJSON.message
                                : "Terjadi kesalahan saat mengubah status supir.";
                            swal({
                                title: "Error",
                                text: msg,
                                icon: "error"
                            });
                        }
                    });
                }
            });
        }

        function deleteData(uuid) {
            var url = '{{ route("master.employee.index") }}/' + uuid;
            $('#delete-form').attr('action', url);

            swal({
                title: "{{ __('general.are_you_sure') }}",
                text: "{{ __('general.want_to_delete_this_data') }}",
                icon: "warning",
                buttons: {
                    cancel: "Batal",
                    confirm: {
                        text: "Ya, Hapus",
                        className: "btn-danger"
                    }
                },
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    $('#delete-form').submit();
                }
            });
        }
    </script>
@endpush
