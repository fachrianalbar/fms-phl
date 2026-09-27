@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Warehouse',
    'secondSegment' => $title,
])

@push('style')
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/libs/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/sweetalert2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom-select2.css') }}">

    <style>
        /* ── Maintenance table ── */
        #dt {
            border: 1px solid #e2e8f0;
            border-collapse: separate;
            border-radius: 12px;
            border-spacing: 0;
            overflow: hidden;
        }

        #dt thead th {
            background-color: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            border-top: 0;
            color: #475569;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .5px;
            padding: 13px 12px;
            text-transform: uppercase;
            vertical-align: middle;
            white-space: nowrap;
        }

        #dt tbody td {
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            font-size: 12.5px;
            padding: 11px 12px;
            vertical-align: middle;
            white-space: nowrap;
        }

        #dt tbody tr { transition: background-color .15s ease; }
        #dt tbody tr:hover { background-color: #f8fafc !important; }
        #dt tbody tr:last-child td { border-bottom: 0; }

        .maintenance-card-header {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
        }

        .btn-icon {
            border-radius: 8px !important;
            padding: 6px 10px;
            transition: all .2s ease;
        }

        .btn-icon:hover { transform: translateY(-1px); }

        /* ── Standard filter card ── */
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

        .filter-label {
            color: #64748b;
            display: block;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .filter-control,
        .filter-card .form-control,
        .filter-card .form-select {
            background-color: #fff !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            color: #334155 !important;
            font-size: 13px !important;
            height: 38px !important;
        }

        .filter-control {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='4' width='18' height='18' rx='2'%3E%3C/rect%3E%3Cline x1='16' y1='2' x2='16' y2='6'%3E%3C/line%3E%3Cline x1='8' y1='2' x2='8' y2='6'%3E%3C/line%3E%3Cline x1='3' y1='10' x2='21' y2='10'%3E%3C/line%3E%3C/svg%3E");
            background-position: 12px center;
            background-repeat: no-repeat;
            background-size: 15px 15px;
            padding: 0 12px 0 36px !important;
        }

        .filter-control::placeholder { color: #94a3b8 !important; font-size: 13px !important; }
        .filter-control:hover { border-color: #94a3b8 !important; }
        .filter-control:focus {
            background-color: #fff !important;
            border-color: #818cf8 !important;
            box-shadow: 0 0 0 .2rem rgba(79, 70, 229, .15) !important;
        }

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

        .btn-filter-primary:hover {
            background: linear-gradient(135deg, #4338ca, #4f46e5) !important;
            color: #fff !important;
            transform: translateY(-1px);
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

        .btn-filter-reset:hover { background: #fff1f2 !important; color: #e11d48 !important; }
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
        .select2-dropdown { border-radius: 8px !important; }

        #dt_wrapper .dataTables_filter input,
        #dt_wrapper .dataTables_length select { border-radius: 8px; font-size: 12px; }
        #dt_wrapper .dataTables_info,
        #dt_wrapper .dataTables_length,
        #dt_wrapper .dataTables_filter { color: #64748b; font-size: 12px; }

        /* ── Detail modal ── */
        #detailModal .modal-dialog { max-width: 1000px; }
        #detailModal .modal-content {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(15, 23, 42, .12);
        }
        #detailModal .modal-header {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 14px 20px;
        }
        #detailModal .modal-body { background: #fff; padding: 24px; }
        #detailModal .modal-footer {
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 12px 20px;
        }
        .detail-icon {
            align-items: center;
            background: #eef2ff;
            border-radius: 50%;
            color: #4f46e5;
            display: inline-flex;
            height: 36px;
            justify-content: center;
            width: 36px;
        }
        .detail-meta-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            min-height: 68px;
            padding: 11px 14px;
        }
        .detail-meta-label {
            color: #64748b;
            display: block;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .4px;
            margin-bottom: 4px;
            text-transform: uppercase;
        }
        .detail-meta-value { color: #334155; font-size: 13px; font-weight: 600; }
        .modal-table-wrap {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
        }
        .modal-table { border-collapse: separate; border-spacing: 0; width: 100%; }
        .modal-table thead th {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            color: #475569;
            font-size: 12px;
            font-weight: 700;
            padding: 10px 12px;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .modal-table tbody td {
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            font-size: 12.5px;
            padding: 9px 12px;
            vertical-align: middle;
        }
        .modal-table tbody tr:last-child td { border-bottom: 0; }

        /* ── Dark mode ── */
        html[data-bs-theme="dark"] .maintenance-card-header,
        html[data-bs-theme="dark"] #detailModal .modal-header,
        html[data-bs-theme="dark"] #detailModal .modal-body { background-color: var(--bs-card-bg) !important; }
        html[data-bs-theme="dark"] .maintenance-card-header { border-bottom-color: var(--bs-border-color); }
        html[data-bs-theme="dark"] .filter-card { background: var(--bs-secondary-bg); border-color: var(--bs-border-color); }
        html[data-bs-theme="dark"] .filter-card-header { background: var(--bs-secondary-bg); color: var(--bs-body-color); }
        html[data-bs-theme="dark"] .filter-card-header:hover { background: var(--bs-tertiary-bg); }
        html[data-bs-theme="dark"] .filter-card .filter-collapse { border-top-color: var(--bs-border-color); }
        html[data-bs-theme="dark"] .filter-label { color: var(--bs-secondary-color); }
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
        html[data-bs-theme="dark"] #dt,
        html[data-bs-theme="dark"] #dt thead th,
        html[data-bs-theme="dark"] #dt tbody td { border-color: var(--bs-border-color) !important; }
        html[data-bs-theme="dark"] #dt thead th,
        html[data-bs-theme="dark"] .modal-table thead th { background-color: var(--bs-tertiary-bg) !important; color: var(--bs-emphasis-color) !important; }
        html[data-bs-theme="dark"] #dt tbody td,
        html[data-bs-theme="dark"] .modal-table tbody td { color: var(--bs-body-color) !important; }
        html[data-bs-theme="dark"] #dt tbody tr:hover { background-color: rgba(255, 255, 255, .05) !important; }
        html[data-bs-theme="dark"] #dt_wrapper .dataTables_info,
        html[data-bs-theme="dark"] #dt_wrapper .dataTables_length,
        html[data-bs-theme="dark"] #dt_wrapper .dataTables_filter { color: var(--bs-secondary-color); }
        html[data-bs-theme="dark"] #detailModal .modal-content { background-color: var(--bs-card-bg) !important; border-color: var(--bs-border-color) !important; }
        html[data-bs-theme="dark"] #detailModal .modal-header { border-bottom-color: var(--bs-border-color); }
        html[data-bs-theme="dark"] #detailModal .modal-footer { background-color: var(--bs-secondary-bg) !important; border-top-color: var(--bs-border-color); }
        html[data-bs-theme="dark"] .detail-icon { background: rgba(99, 102, 241, .2); color: #a5b4fc; }
        html[data-bs-theme="dark"] .detail-meta-item { background: var(--bs-tertiary-bg); border-color: var(--bs-border-color); }
        html[data-bs-theme="dark"] .detail-meta-label { color: var(--bs-secondary-color); }
        html[data-bs-theme="dark"] .detail-meta-value { color: var(--bs-body-color); }
        html[data-bs-theme="dark"] .modal-table-wrap { border-color: var(--bs-border-color); }
        html[data-bs-theme="dark"] .modal-table tbody td { border-bottom-color: var(--bs-border-color) !important; }

        @media (max-width: 575.98px) {
            #detailModal .modal-body { padding: 16px; }
            .filter-card .filter-collapse-body { padding: 12px; }
        }
    </style>
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card border-0 shadow-sm maintenance-page-card" style="border-radius: 16px; overflow: hidden;">
            <div class="card-header maintenance-card-header py-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h4 class="mb-1 fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="mdi mdi-wrench-clock-outline text-primary fs-20"></i>
                        {{ $title }} Data
                    </h4>
                    <small class="text-muted">Kelola riwayat pemeliharaan kendaraan dan biaya item maintenance</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route($view . 'pdf-maintenance') }}" target="_blank" id="print-pdf"
                        class="btn btn-icon btn-sm bg-danger-subtle" data-bs-toggle="tooltip" title="Export PDF">
                        <i class="mdi mdi-file-pdf-box fs-14 text-danger"></i>
                    </a>
                    <a href="{{ route($view . 'create') }}" class="btn btn-primary d-flex align-items-center gap-1"
                        style="border-radius: 8px; font-weight: 600;">
                        <i class="mdi mdi-plus"></i> {{ __('general.add_data') }}
                    </a>
                </div>
            </div>

            <div class="card-body p-4">
                @include('partials.alert')

                <div class="filter-card">
                    <button type="button" class="filter-card-header" data-bs-toggle="collapse"
                        data-bs-target="#maintenanceFilterCollapse" aria-expanded="false"
                        aria-controls="maintenanceFilterCollapse">
                        <span class="filter-card-heading">
                            <i class="mdi mdi-filter-variant"></i>
                            <strong>Filter Data</strong>
                            <small>Gunakan filter untuk mempersempit daftar maintenance</small>
                        </span>
                        <i class="mdi mdi-chevron-down filter-card-chevron"></i>
                    </button>

                    <div class="collapse filter-collapse" id="maintenanceFilterCollapse">
                        <div class="filter-collapse-body">
                            <div id="filterForm">
                                <div class="row g-3">
                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="plateNumber">{{ __('menu_maintenance.plate_no') }}</label>
                                        <select class="form-select select2-filter" name="plateNumber" id="plateNumber">
                                            <option value="">Semua Armada</option>
                                            @foreach ($fleet as $item)
                                                <option value="{{ $item->plateNumber }}">{{ $item->plateNumber }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="itemCode">Item</label>
                                        <select class="form-select select2-filter" name="itemCode" id="itemCode">
                                            <option value="">Semua Item</option>
                                            @foreach ($stock as $item)
                                                <option value="{{ $item->itemCode }}">
                                                    {{ $item->itemCode . ' - ' . ($item->item->name ?? '') }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-xl-2 col-md-6">
                                        <label class="filter-label" for="startDate">Dari Tanggal</label>
                                        <input class="form-control filter-control" name="startDate" id="startDate"
                                            type="text" placeholder="Pilih tanggal mulai">
                                    </div>

                                    <div class="col-xl-2 col-md-6">
                                        <label class="filter-label" for="endDate">Sampai Tanggal</label>
                                        <input class="form-control filter-control" name="endDate" id="endDate"
                                            type="text" placeholder="Pilih tanggal akhir">
                                    </div>

                                    <div class="col-xl-2 col-md-6 d-flex align-items-end justify-content-md-end gap-2">
                                        <div class="flex-grow-1 flex-md-grow-0">
                                            <label class="filter-label d-none d-md-block">&nbsp;</label>
                                            <button class="btn btn-filter-primary w-100" type="button" id="btnFilter">
                                                <i class="mdi mdi-filter-outline"></i> Terapkan Filter
                                            </button>
                                        </div>
                                        <div>
                                            <label class="filter-label d-none d-md-block">&nbsp;</label>
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

                <div class="table-responsive custom-scrollbar">
                    <table class="table align-middle w-100 mb-0" id="dt">
                        <thead>
                            <tr>
                                <th class="text-center">Aksi</th>
                                <th class="text-center">No</th>
                                <th>Kode Maintenance</th>
                                <th>No. PO</th>
                                <th class="text-center">{{ __('menu_maintenance.date') }}</th>
                                <th>{{ __('menu_maintenance.plate_no') }}</th>
                                <th class="text-end">Total Biaya</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <form id="delete-form" method="post">
        @csrf
        @method('DELETE')
    </form>

    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="detail-icon"><i class="mdi mdi-wrench-clock-outline fs-18"></i></span>
                        <div>
                            <h5 class="modal-title mb-0 fw-bold" id="detailModalLabel">Maintenance Detail</h5>
                            <small class="text-muted">Informasi transaksi dan rincian item maintenance</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6 col-lg-3">
                            <div class="detail-meta-item">
                                <span class="detail-meta-label">Kode</span>
                                <span class="detail-meta-value font-monospace" id="detail-code">-</span>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="detail-meta-item">
                                <span class="detail-meta-label">Tanggal</span>
                                <span class="detail-meta-value" id="detail-date">-</span>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="detail-meta-item">
                                <span class="detail-meta-label">Armada</span>
                                <span class="detail-meta-value font-monospace" id="detail-fleet">-</span>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="detail-meta-item">
                                <span class="detail-meta-label">Gudang</span>
                                <span class="detail-meta-value" id="detail-warehouse">-</span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="mdi mdi-file-document-outline text-primary"></i>
                            <strong class="small text-uppercase">Purchase Order</strong>
                        </div>
                        <div id="detail-po"><span class="text-muted">-</span></div>
                    </div>

                    <div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="mdi mdi-package-variant-closed text-primary"></i>
                            <strong class="small text-uppercase">Rincian Item</strong>
                        </div>
                        <div class="table-responsive modal-table-wrap">
                            <table class="table align-middle mb-0 modal-table">
                                <thead>
                                    <tr>
                                        <th class="text-center">No</th>
                                        <th>Item Code</th>
                                        <th>Item Name</th>
                                        <th>Description</th>
                                        <th class="text-end">Qty</th>
                                        <th class="text-end">Price</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="detail-items"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="modal-footer d-flex justify-content-end">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"
                        style="border-radius: 8px; padding: 6px 16px;">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/custom-flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2-custom.js') }}"></script>

    <script>
        let filterStartPicker;
        let filterEndPicker;

        $(document).ready(function() {
            $('.select2-filter').select2({
                placeholder: 'Semua pilihan',
                allowClear: true,
                width: '100%'
            });

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

            function getFilters() {
                return {
                    plateNumber: $('#plateNumber').val() || '',
                    itemCode: $('#itemCode').val() || '',
                    startDate: $('#startDate').val() || '',
                    endDate: $('#endDate').val() || ''
                };
            }

            function syncExportUrls() {
                const params = new URLSearchParams();
                Object.entries(getFilters()).forEach(function(entry) {
                    if (entry[1]) params.set(entry[0], entry[1]);
                });

                const query = params.toString() ? '?' + params.toString() : '';
                $('#print-pdf').attr('href', "{{ route($view . 'pdf-maintenance') }}" + query);
            }

            function reloadWithFilters() {
                syncExportUrls();
                table.ajax.reload(null, true);
            }

            syncExportUrls();

            const table = $('#dt').DataTable({
                processing: true,
                serverSide: true,
                destroy: true,
                pageLength: 25,
                ajax: {
                    url: "{{ route('dt.maintenance') }}",
                    data: function(d) {
                        Object.assign(d, getFilters());
                    }
                },
                columns: [
                    { data: 'action', className: 'text-center align-middle' },
                    { data: 'DT_RowIndex', className: 'text-center align-middle' },
                    { data: 'code', className: 'align-middle font-monospace fw-semibold' },
                    { data: 'po_numbers', className: 'align-middle' },
                    { data: 'maintenanceDate', className: 'text-center align-middle' },
                    { data: 'fleet.plateNumber', className: 'align-middle font-monospace' },
                    {
                        data: 'grand_total',
                        className: 'text-end align-middle font-monospace fw-bold',
                        render: function(data) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(data || 0);
                        }
                    }
                ],
                columnDefs: [
                    { searchable: false, targets: [0, 1, 4, 6] },
                    { orderable: false, targets: [0, 1, 3] }
                ],
                order: [[2, 'asc']],
                language: {
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                    infoEmpty: 'Tidak ada data',
                    zeroRecords: 'Data tidak ditemukan',
                    processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Memuat data...'
                }
            });

            $('#btnFilter').on('click', reloadWithFilters);
            $('#print-pdf').on('click', function(e) {
                const filters = getFilters();
                if (!filters.plateNumber || !filters.startDate || !filters.endDate) {
                    e.preventDefault();
                    swal({
                        title: 'Filter belum lengkap',
                        text: 'Pilih nomor polisi, tanggal mulai, dan tanggal akhir sebelum export PDF.',
                        icon: 'warning'
                    });
                }
            });
            $('#filterForm input').on('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    reloadWithFilters();
                }
            });

            $('#btnResetFilter').on('click', function() {
                $('#filterForm').find('input').val('');
                $('.select2-filter').val('').trigger('change');
                if (filterStartPicker) filterStartPicker.clear();
                if (filterEndPicker) filterEndPicker.clear();
                if (filterStartPicker) filterStartPicker.set('maxDate', null);
                if (filterEndPicker) filterEndPicker.set('minDate', null);
                reloadWithFilters();
            });
        });

        function escapeHtml(value) {
            return $('<div>').text(value == null ? '-' : value).html();
        }

        function formatNumber(value) {
            return new Intl.NumberFormat('id-ID').format(value || 0);
        }

        function showDetail(id) {
            $.ajax({
                url: '{{ route('warehouse.maintenance.show', ':id') }}'.replace(':id', id),
                method: 'GET',
                success: function(response) {
                    $('#detail-code').text(response.code || '-');
                    $('#detail-date').text((response.date || '-') + ' ' + (response.time || ''));
                    $('#detail-fleet').text(response.fleet ? response.fleet.plateNumber : '-');
                    $('#detail-warehouse').text(response.warehouse ? response.warehouse.name : '-');

                    let poHtml = '<span class="text-muted">-</span>';
                    if (response.purchases && response.purchases.length > 0) {
                        poHtml = response.purchases.map(function(purchase) {
                            let text = purchase.code || '-';
                            if (purchase.supplier && purchase.supplier.name) {
                                text += ' (' + purchase.supplier.name + ')';
                            }
                            return '<span class="badge bg-primary-subtle text-primary border border-primary-subtle me-1 mb-1" style="border-radius: 6px;">' + escapeHtml(text) + '</span>';
                        }).join(' ');
                    }
                    $('#detail-po').html(poHtml);

                    let itemsHtml = '';
                    if (response.details && response.details.length > 0) {
                        response.details.forEach(function(item, index) {
                            itemsHtml += '<tr>';
                            itemsHtml += '<td class="text-center">' + (index + 1) + '</td>';
                            itemsHtml += '<td class="font-monospace">' + escapeHtml(item.itemCode) + '</td>';
                            itemsHtml += '<td>' + escapeHtml(item.item ? item.item.name : '-') + '</td>';
                            itemsHtml += '<td>' + escapeHtml(item.description || '-') + '</td>';
                            itemsHtml += '<td class="text-end font-monospace">' + escapeHtml(parseFloat(item.qty) || 0) + '</td>';
                            itemsHtml += '<td class="text-end font-monospace">Rp ' + formatNumber(item.price) + '</td>';
                            itemsHtml += '<td class="text-end font-monospace fw-semibold">Rp ' + formatNumber(item.total) + '</td>';
                            itemsHtml += '</tr>';
                        });
                    } else {
                        itemsHtml = '<tr><td colspan="7" class="text-center text-muted py-4">Tidak ada item ditemukan</td></tr>';
                    }
                    $('#detail-items').html(itemsHtml);

                    $('#detailModal').modal('show');
                },
                error: function() {
                    swal({
                        title: 'Error',
                        text: 'Failed to load maintenance details',
                        icon: 'error'
                    });
                }
            });
        }

        function deleteData(uuid) {
            const url = '{{ route('warehouse.maintenance.destroy', ':uuid') }}'.replace(':uuid', uuid);
            $('#delete-form').attr('action', url);

            swal({
                title: "{{ __('general.are_you_sure') }}",
                text: "{{ __('general.want_to_delete_this_data') }}",
                icon: 'warning',
                buttons: true,
                dangerMode: true
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
