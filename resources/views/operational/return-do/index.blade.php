@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Operational',
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

        /* Make order code, shipment, plate, and driver uppercase */
        #dt td:nth-child(5),
        #dt td:nth-child(6),
        #dt td:nth-child(11),
        #dt td:nth-child(12) {
            text-transform: uppercase;
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

        .filter-card .filter-collapse-body {
            padding: 16px;
        }

        .filter-card .row {
            --bs-gutter-y: .75rem;
        }

        .filter-label {
            color: #64748b;
            display: block;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .filter-control,
        .filter-card .form-control {
            background-color: #fff !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            color: #334155 !important;
            font-size: 13px !important;
            height: 38px !important;
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

        .select2-container {
            width: 100% !important;
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
            z-index: 99999 !important;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #4f46e5 !important;
            color: #ffffff !important;
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
        html[data-bs-theme="dark"] .card-header {
            background-color: var(--bs-card-bg) !important;
            border-bottom-color: var(--bs-border-color) !important;
        }

        html[data-bs-theme="dark"] .card-header h4 {
            color: var(--bs-heading-color) !important;
        }

        html[data-bs-theme="dark"] #dt {
            border-color: var(--bs-border-color) !important;
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

        /* Filter card (dark) */
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

        /* Modal Dark Mode */
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

        html[data-bs-theme="dark"] #fileGalleryContainer .card {
            background-color: var(--bs-tertiary-bg) !important;
            border-color: var(--bs-border-color) !important;
        }
    </style>
@endpush

@section('content')
    <div class="col-sm-12">
        <form class="card border-0 shadow-sm" method="POST" action="{{ route('operational.return-do.cancel-do') }}"
            style="border-radius: 16px; overflow: hidden;">
            @csrf
            {{-- Card Header --}}
            <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3"
                style="border-color: #e2e8f0;">
                <div>
                    <h4 class="mb-1 fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="mdi mdi-truck-check-outline text-primary fs-20"></i>
                        {{ $title }} Data
                    </h4>
                    <small class="text-muted">Daftar delivery order yang telah menerima dokumen return dan status faktur</small>
                </div>

                <div class="d-flex align-items-center gap-2">
                    @if (in_array(Auth::user()->roleCode, ['SPRADMIN', 'SPRUSER']))
                        <button type="button" class="btn btn-sm btn-success d-flex align-items-center gap-1" id="btn-sync-driver-salary"
                            style="border-radius: 8px; font-weight: 600; padding: 7px 14px;">
                            <i class="mdi mdi-sync"></i> Sinkron Gaji Supir
                        </button>
                    @endif
                </div>
            </div>

            <div class="card-body p-4">
                @include('partials.alert')

                {{-- Filter Bar (collapse, tertutup default) --}}
                <div class="filter-card">
                    <button type="button" class="filter-card-header" data-bs-toggle="collapse"
                        data-bs-target="#returnDoFilterCollapse" aria-expanded="false"
                        aria-controls="returnDoFilterCollapse">
                        <span class="filter-card-heading">
                            <i class="mdi mdi-filter-variant"></i>
                            <strong>Filter Data</strong>
                            <small>Gunakan filter untuk mempersempit daftar return DO</small>
                        </span>
                        <i class="mdi mdi-chevron-down filter-card-chevron"></i>
                    </button>

                    <div class="collapse filter-collapse" id="returnDoFilterCollapse">
                        <div class="filter-collapse-body">
                            <div id="filterForm">
                                <div class="row g-3">
                                    <div class="col-xl-4 col-md-6">
                                        <label class="filter-label" for="filterInvoiceStatus">Status Faktur</label>
                                        <select class="form-select select2-filter" name="invoiceStatus" id="filterInvoiceStatus">
                                            <option value="">Semua Status Faktur</option>
                                            <option value="uninvoiced">Belum Ada Faktur</option>
                                            <option value="invoiced">Sudah Ada Faktur</option>
                                        </select>
                                    </div>

                                    <div class="col-xl-8 col-md-6 d-flex align-items-end justify-content-md-end gap-2">
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

                {{-- Table --}}
                <div class="table-responsive custom-scrollbar">
                    <table class="table align-middle w-100 mb-0" id="dt">
                        <thead>
                            <tr>
                                <th>Detail</th>
                                <th>{{ __('menu_return_do.action') }}</th>
                                <th>{{ __('menu_return_do.no') }}</th>
                                <th>{{ __('menu_return_do.order_date') }}</th>
                                <th>{{ __('menu_order.order_code') }}</th>
                                <th>{{ __('menu_return_do.shipment_no') }}</th>
                                <th>No. Faktur</th>
                                <th>{{ __('menu_return_do.customer_name') }}</th>
                                <th>{{ __('menu_return_do.origin') }}</th>
                                <th>{{ __('menu_return_do.destination') }}</th>
                                <th>{{ __('menu_return_do.fleet') }}</th>
                                <th>{{ __('menu_return_do.driver') }}</th>
                                <th>{{ __('menu_return_do.order_type') }}</th>
                                <th>{{ __('menu_return_do.return_date') }}</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </form>
    </div>

    <!-- Modal Detail On Charge Cost -->
    <div class="modal fade" id="modalDetailCost" tabindex="-1" aria-labelledby="modalDetailCostLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
                <div class="modal-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="avatar-sm d-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle" style="width: 36px; height: 36px;">
                            <i class="mdi mdi-cash-multiple fs-18"></i>
                        </span>
                        <div>
                            <h5 class="modal-title mb-0 fw-bold" id="modalDetailCostLabel">Detail Biaya On Charge</h5>
                            <small class="text-muted">Rincian komponen biaya on charge untuk shipment terkait</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="d-flex align-items-center gap-2 mb-3 bg-light p-2 rounded-3 border" style="border-color: #e2e8f0 !important;">
                        <i class="mdi mdi-truck-fast-outline text-primary fs-18"></i>
                        <span class="text-muted small">No. Shipment:</span>
                        <span class="fw-bold font-monospace text-dark" id="modalShipmentNumber"></span>
                    </div>
                    <div class="table-responsive" style="border-radius: 8px; border: 1px solid #e2e8f0;">
                        <table class="table align-middle mb-0 modal-table">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 50px; text-align: center;">No</th>
                                    <th>Komponen Biaya</th>
                                    <th style="text-align: right;">Nominal</th>
                                </tr>
                            </thead>
                            <tbody id="costTableBody">
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-4 border-top">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"
                        style="border-radius: 8px; padding: 6px 16px;">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Gallery Surat Jalan -->
    <div class="modal fade" id="modalFileGallery" tabindex="-1" aria-labelledby="modalFileGalleryLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
                <div class="modal-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="avatar-sm d-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle" style="width: 36px; height: 36px;">
                            <i class="mdi mdi-image-multiple-outline fs-18"></i>
                        </span>
                        <div>
                            <h5 class="modal-title mb-0 fw-bold" id="modalFileGalleryLabel">Gallery Surat Jalan</h5>
                            <small class="text-muted">Berkas lampiran dan bukti fisik dokumen surat jalan</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="d-flex align-items-center gap-2 mb-3 bg-light p-2 rounded-3 border" style="border-color: #e2e8f0 !important;">
                        <i class="mdi mdi-file-document-outline text-primary fs-18"></i>
                        <span class="text-muted small">No. Order:</span>
                        <span class="fw-bold font-monospace text-dark" id="galleryOrderCode"></span>
                    </div>
                    <div id="fileGalleryContainer" class="row g-3">
                        <!-- Files will be loaded here -->
                    </div>
                    <div id="galleryLoading" class="text-center py-5" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted mt-2 small">Memuat file lampiran...</p>
                    </div>
                    <div id="galleryEmpty" class="text-center py-5" style="display: none;">
                        <i class="mdi mdi-file-image-outline text-muted" style="font-size: 48px;"></i>
                        <p class="text-muted mt-2">Tidak ada file lampiran surat jalan untuk order ini</p>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-4 border-top">
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
    <script src="{{ asset('assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-keytable-bs5/js/keyTable.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-select/js/dataTables.select.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-select-bs5/js/select.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2-custom.js') }}"></script>

    <script>
        $(document).ready(function() {
            $('.select2-filter').select2({
                placeholder: 'Semua Status Faktur',
                allowClear: true,
                width: '100%'
            });

            function getFilters() {
                return {
                    invoiceStatus: $('#filterInvoiceStatus').val() || ''
                };
            }

            const table = $('#dt').DataTable({
                processing: true,
                serverSide: true,
                destroy: true,
                pageLength: 25,
                ajax: {
                    url: "{{ route('dt.return-do') }}",
                    data: function(d) {
                        Object.assign(d, getFilters());
                    }
                },
                columns: [
                    { data: 'detail', className: 'align-middle text-center' },
                    { data: 'action', className: 'align-middle text-center' },
                    { data: 'DT_RowIndex', className: 'align-middle text-center' },
                    { data: 'orderDate', className: 'align-middle text-center' },
                    { data: 'code', className: 'align-middle font-monospace fw-semibold' },
                    { data: 'shipmentNumber', className: 'align-middle font-monospace' },
                    { data: 'invoiceNumber', className: 'align-middle text-center' },
                    { data: 'customer.name', className: 'align-middle' },
                    { data: 'route.originLocation.name', className: 'align-middle' },
                    { data: 'route.destinationLocation.name', className: 'align-middle' },
                    { data: 'fleet.plateNumber', className: 'align-middle font-monospace fw-semibold' },
                    { data: 'driver.name', className: 'align-middle' },
                    { data: 'orderType', className: 'align-middle text-center' },
                    { data: 'returnDate', className: 'align-middle text-center' }
                ],
                columnDefs: [
                    { searchable: false, targets: [0, 1, 2] },
                    { orderable: false, targets: [0, 1, 2] },
                    { defaultContent: '-', targets: '_all' }
                ],
                order: [[3, 'asc']],
                language: {
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                    infoEmpty: 'Tidak ada data',
                    zeroRecords: 'Data tidak ditemukan',
                    processing: '<span class="spinner-border spinner-border-sm text-primary me-1"></span> Memuat data...'
                }
            });

            function reloadWithFilters() {
                table.ajax.reload(null, true);
            }

            $('#btnFilter').on('click', reloadWithFilters);

            $('#filterInvoiceStatus').on('change', function() {
                reloadWithFilters();
            });

            $('#btnResetFilter').on('click', function() {
                $('#filterInvoiceStatus').val('').trigger('change');
            });

            let selectedOrders = [];

            // Guard: only attach listener if the button exists (it may be commented out in the view)
            const saveOrderBtn = document.getElementById('saveOrder');
            if (saveOrderBtn) {
                saveOrderBtn.addEventListener('click', function(event) {
                    if (selectedOrders.length === 0) {
                        event.preventDefault();
                        swal({
                            title: "{{ __('general.warning') }}",
                            text: "Please select at least one item",
                            icon: "warning",
                        });
                        return;
                    }

                    $('<input>').attr({
                        type: 'hidden',
                        name: 'selectedOrders',
                        value: JSON.stringify(selectedOrders)
                    }).appendTo('form');
                });
            }

            // Event handler untuk checkbox
            $(document).on('change', '.order-checkbox', function() {
                const orderId = $(this).val();
                if ($(this).is(':checked')) {
                    if (!selectedOrders.includes(orderId)) {
                        selectedOrders.push(orderId);
                    }
                } else {
                    selectedOrders = selectedOrders.filter(id => id !== orderId);
                }
            });

            // Simpan state saat DataTable di-reload
            $('#dt').on('draw.dt', function() {
                $('.order-checkbox').each(function() {
                    const orderId = $(this).val();
                    if (selectedOrders.includes(orderId)) {
                        $(this).prop('checked', true);
                    }
                });
            });

            // Event handler untuk tombol detail cost
            $(document).on('click', '.btn-detail-cost', function(e) {
                e.preventDefault();
                const costsData = $(this).data('costs');
                const shipmentNumber = $(this).data('shipment');

                $('#modalShipmentNumber').text(shipmentNumber);
                $('#costTableBody').empty();

                if (costsData && costsData.length > 0) {
                    costsData.forEach(function(cost, index) {
                        const row = `
                            <tr>
                                <td class="text-center">${index + 1}</td>
                                <td>${cost.component}</td>
                                <td class="text-end font-monospace fw-semibold text-dark">${cost.nominal}</td>
                            </tr>
                        `;
                        $('#costTableBody').append(row);
                    });
                } else {
                    $('#costTableBody').append('<tr><td colspan="3" class="text-center text-muted py-3">Tidak ada data biaya On Charge</td></tr>');
                }

                $('#modalDetailCost').modal('show');
            });

            // Event handler untuk tombol view files
            $(document).on('click', '.btn-view-files', function(e) {
                e.preventDefault();
                const orderId = $(this).data('order-id');
                const orderCode = $(this).data('order-code');

                $('#galleryOrderCode').text(orderCode);
                $('#galleryLoading').show();
                $('#fileGalleryContainer').hide();
                $('#galleryEmpty').hide();

                $('#modalFileGallery').modal('show');

                $.ajax({
                    url: "{{ route('operational.return-do.get-files', ['orderId' => ':orderId']) }}".replace(':orderId', orderId),
                    type: 'GET',
                    success: function(response) {
                        $('#galleryLoading').hide();
                        
                        if (response.success && response.files.length > 0) {
                            $('#fileGalleryContainer').show().empty();
                            
                            response.files.forEach(function(file) {
                                const fileExt = file.name.split('.').pop().toLowerCase();
                                const isPdf = fileExt === 'pdf';
                                
                                let cardContent = '';
                                if (isPdf) {
                                    cardContent = `
                                        <div class="text-center py-4">
                                            <i class="mdi mdi-file-pdf" style="font-size: 80px; color: #dc3545;"></i>
                                            <p class="mt-2 mb-0"><small>${file.name}</small></p>
                                        </div>
                                    `;
                                } else {
                                    cardContent = `
                                        <img src="${file.url}" class="card-img-top" alt="${file.name}" style="height: 200px; object-fit: cover;">
                                    `;
                                }
                                
                                const card = `
                                    <div class="col-md-3 col-sm-6">
                                        <div class="card">
                                            ${cardContent}
                                            <div class="card-body">
                                                <p class="card-text small mb-1"><i class="mdi mdi-clock-outline me-1"></i>${file.uploaded_at}</p>
                                                <a href="${file.url}" target="_blank" class="btn btn-sm btn-primary w-100">
                                                    <i class="mdi mdi-download me-1"></i>Download
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                `;
                                $('#fileGalleryContainer').append(card);
                            });
                        } else {
                            $('#galleryEmpty').show();
                        }
                    },
                    error: function(xhr) {
                        $('#galleryLoading').hide();
                        $('#galleryEmpty').show();
                        console.error('Error loading files:', xhr);
                    }
                });
            });

            // Rollback status
            $(document).on('click', '.rollback-btn', function() {
                const id = $(this).data('id');
                const shipment = $(this).data('shipment');

                swal({
                    title: "Apakah Anda yakin?",
                    text: `Ingin mengembalikan status pesanan ${shipment} ke Not Return DO?`,
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willRollback) => {
                    if (willRollback) {
                        window.location.href =
                            "{{ route('operational.return-do.rollback-status', ':id') }}".replace(':id', id);
                    }
                });
            });

            // Sync driver salary
            @if (in_array(Auth::user()->roleCode, ['SPRADMIN', 'SPRUSER']))
            $('#btn-sync-driver-salary').click(function(e) {
                e.preventDefault();
                swal({
                    title: "Sinkronisasi Gaji Supir?",
                    text: "Sistem akan membaca semua order di Return DO dan memasukkan komponen biaya bertipe gaji ke tabel gaji supir.",
                    icon: "info",
                    buttons: {
                        cancel: "Batal",
                        confirm: {
                            text: "Ya, Sinkronkan!",
                            closeModal: false
                        }
                    },
                }).then((willSync) => {
                    if (willSync) {
                        $.ajax({
                            url: "{{ route('operational.return-do.sync-driver-salary') }}",
                            type: "POST",
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                swal("Berhasil!", response.message, "success").then(() => {
                                    $('#dt').DataTable().ajax.reload(null, false);
                                });
                            },
                            error: function(xhr) {
                                let msg = "Terjadi kesalahan saat sinkronisasi gaji supir.";
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    msg = xhr.responseJSON.message;
                                }
                                swal("Gagal!", msg, "error");
                            }
                        });
                    }
                });
            });
            @endif
        });
    </script>
@endpush
