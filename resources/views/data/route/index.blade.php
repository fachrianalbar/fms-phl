@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Data',
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

    <!-- Select2 CSS -->
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

        /* ── Dark mode ─────────────────────────────── */
        html[data-bs-theme="dark"] .card-header {
            background-color: var(--bs-card-bg) !important;
            border-color: var(--bs-border-color) !important;
        }

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
                        <i class="mdi mdi-routes text-primary fs-20"></i>
                        {{ $title }} Data
                    </h4>
                    <small class="text-muted">Daftar master rute pengiriman beserta tarif dan harga vendor</small>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route($view . 'create') }}"
                        class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1"
                        style="border-radius: 8px; font-weight: 600; padding: 7px 14px;"
                        title="{{ __('general.add_data') }}" aria-label="{{ __('general.add_data') }}">
                        <i class="mdi mdi-plus"></i>
                        <span class="d-none d-md-inline">{{ __('general.add_data') }}</span>
                    </a>

                    <button type="button" class="btn btn-sm btn-warning d-inline-flex align-items-center gap-1"
                        id="btnBulkUpdatePrice" title="Penyesuaian Harga"
                        style="border-radius: 8px; font-weight: 600; padding: 7px 14px;">
                        <i class="mdi mdi-cash-multiple"></i>
                        <span class="d-none d-md-inline">Penyesuaian Harga</span>
                    </button>

                    <button type="button" class="btn btn-sm btn-danger d-inline-flex align-items-center gap-1"
                        id="btnBulkDelete" title="Hapus Terpilih"
                        style="border-radius: 8px; font-weight: 600; padding: 7px 14px;">
                        <i class="mdi mdi-delete"></i>
                        <span class="d-none d-md-inline">Hapus Terpilih</span>
                    </button>
                </div>
            </div>

            <div class="card-body p-4">
                @include('partials.alert')

                {{-- Filter Bar (collapse, tertutup default) --}}
                <div class="filter-card">
                    <button type="button" class="filter-card-header" data-bs-toggle="collapse"
                        data-bs-target="#routeFilterCollapse" aria-expanded="false"
                        aria-controls="routeFilterCollapse">
                        <span class="filter-card-heading">
                            <i class="mdi mdi-filter-variant"></i>
                            <strong>Filter Data</strong>
                            <small>Gunakan filter untuk mempersempit daftar rute</small>
                        </span>
                        <i class="mdi mdi-chevron-down filter-card-chevron"></i>
                    </button>

                    <div class="collapse filter-collapse" id="routeFilterCollapse">
                        <div class="filter-collapse-body">
                            <div id="filterForm">
                                <div class="row g-3">
                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="customerName">{{ __('menu_route.customer') }}</label>
                                        <select class="form-select select2-filter" name="customerName" id="customerName">
                                            <option value="">Semua Pelanggan</option>
                                            @foreach ($customer as $item)
                                                <option value="{{ $item->name }}">{{ $item->code . ' - ' . $item->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="origin">{{ __('menu_route.origin') }}</label>
                                        <select class="form-select select2-filter" name="origin" id="origin">
                                            <option value="">Semua Asal</option>
                                            @foreach ($location as $item)
                                                <option value="{{ $item->name }}">{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="destination">{{ __('menu_route.destination') }}</label>
                                        <select class="form-select select2-filter" name="destination" id="destination">
                                            <option value="">Semua Tujuan</option>
                                            @foreach ($location as $item)
                                                <option value="{{ $item->name }}">{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="fleetTypeName">{{ __('menu_route.fleet_type') }}</label>
                                        <select class="form-select select2-filter" name="fleetTypeName" id="fleetTypeName">
                                            <option value="">Semua Tipe Armada</option>
                                            @foreach ($fleetType as $item)
                                                <option value="{{ $item->name }}">{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="routeTypeName">{{ __('menu_route.load_type') }}</label>
                                        <select class="form-select select2-filter" name="routeTypeName" id="routeTypeName">
                                            <option value="">Semua Muatan</option>
                                            @foreach ($routeType as $item)
                                                <option value="{{ $item->name }}">{{ $item->name }}</option>
                                            @endforeach
                                        </select>
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
                <div class="table-responsive custom-scrollbar">
                    <table class="table align-middle w-100 mb-0" id="dt">
                        <thead>
                            <tr>
                                <th style="width: 20px;"><input type="checkbox" class="form-check-input" id="checkAll">
                                </th>
                                <th>#</th>
                                <th>No</th>
                                <th>{{ __('menu_route.name') }}</th>
                                <th>{{ __('menu_route.description') }}</th>
                                <th>{{ __('menu_route.customer') }}</th>
                                <th>{{ __('menu_route.origin') }}</th>
                                <th>{{ __('menu_route.destination') }}</th>
                                {{-- <th>Fleet Type</th> --}}
                                <th>{{ __('menu_route.load_type') }}</th>
                                <th>{{ __('menu_route.price') }}</th>
                                <th>{{ __('menu_route.personal_vendor_price') }}</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <form id="delete-form" method="post">
        @csrf
        @method('DELETE')
    </form>

    <!-- Modal Bulk Update Price -->
    <div class="modal fade" id="modalBulkUpdatePrice" tabindex="-1" aria-labelledby="modalBulkUpdatePriceLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalBulkUpdatePriceLabel">Penyesuaian Harga Masal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formBulkUpdatePrice">
                        <div class="mb-3">
                            <label class="form-label">Tipe Penyesuaian</label>
                            <select class="form-select" id="bulkUpdateType" name="type" required>
                                <option value="increase">Kenaikan</option>
                                <option value="decrease">Penurunan</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Persentase (%)</label>
                            <input type="number" class="form-control" id="bulkUpdatePercentage" name="percentage"
                                min="0.01" step="0.01" required placeholder="Contoh: 10">
                        </div>
                        <div class="mb-3">
                            <label class="form-label d-block">Target Harga (Pilih minimal 1)</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input bulk-target-checkbox" type="checkbox" id="targetPrice"
                                    value="price" checked>
                                <label class="form-check-label" for="targetPrice">Harga Tagihan</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input bulk-target-checkbox" type="checkbox"
                                    id="targetVendorPrice" value="vendorPrice" checked>
                                <label class="form-check-label" for="targetVendorPrice">Harga Vendor</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input bulk-target-checkbox" type="checkbox"
                                    id="targetPersonalVendorPrice" value="personalVendorPrice" checked>
                                <label class="form-check-label" for="targetPersonalVendorPrice">Harga Vendor
                                    Pribadi</label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnSubmitBulkUpdate">Simpan Perubahan</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalVendorPrice" tabindex="-1" aria-labelledby="modalVendorPriceLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalVendorPriceLabel">Detail Harga Vendor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <div><strong>Customer:</strong> <span id="vendorPriceCustomerName">-</span></div>
                        <div><strong>Asal - Tujuan:</strong> <span id="vendorPriceOriginDestination">-</span></div>
                        <div><strong>Nama Rute:</strong> <span id="vendorPriceRouteName">-</span></div>
                        <div><strong>Harga:</strong> <span id="vendorPriceAmount">-</span></div>
                        <div><strong>Harga Vendor Pribadi:</strong> <span id="vendorPricePersonalAmount">-</span></div>
                    </div>

                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="btnAddVendorRow">
                            Tambah Vendor
                        </button>
                        <small class="text-danger">* Gunakan koma (,) untuk desimal. Format: xxx.xxx.xxx,xx</small>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle" id="vendorPriceTable">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">No</th>
                                    <th>Nama Vendor</th>
                                    <th style="width: 260px;">Harga Vendor</th>
                                    <th style="width: 80px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="vendorPriceTableBody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" id="btnSaveVendorPrice">Simpan Harga Vendor</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>

    <!-- dataTables.bootstrap5 -->
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
    <script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>

    <!-- Select2 JS -->
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2-custom.js') }}"></script>
    <script>
        let activeRouteId = null;
        let vendorOptions = [];

        function initVendorSelect2() {
            $('.vendor-company-select').each(function() {
                if ($(this).data('select2')) {
                    $(this).select2('destroy');
                }

                $(this).select2({
                    placeholder: 'Pilih vendor...',
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $('#modalVendorPrice')
                });
            });
        }

        function formatThousands(value) {
            const parsed = Number(value);
            if (Number.isNaN(parsed)) return '';
            return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(parsed);
        }

        function parseThousands(value) {
            const normalized = String(value ?? '').replace(/\./g, '').replace(',', '.').trim();

            if (!normalized) {
                return NaN;
            }

            return Number(normalized);
        }

        function formatCurrency(value) {
            if (value === null || value === undefined || value === '') {
                return '-';
            }

            const parsed = Number(value);
            if (Number.isNaN(parsed)) {
                return '-';
            }

            return 'Rp ' + parsed.toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function renderVendorRows(rows = []) {
            if (!rows.length) {
                $('#vendorPriceTableBody').html(
                    '<tr class="vendor-empty-row"><td colspan="4" class="text-center text-muted">Belum ada vendor yang ditambahkan.</td></tr>'
                );
                return;
            }

            let html = '';
            rows.forEach((row, index) => {
                const optionsHtml = vendorOptions.map((vendor) => {
                    const selected = String(vendor.id) === String(row.fleet_company_id) ? 'selected' : '';
                    return `<option value="${escapeHtml(vendor.id)}" ${selected}>${escapeHtml(vendor.name)}</option>`;
                }).join('');

                const amountValue = row.amount !== null && row.amount !== undefined ? formatThousands(row.amount) : '';

                html += `
                    <tr class="vendor-row">
                        <td class="vendor-row-number">${index + 1}</td>
                        <td>
                            <select class="form-select vendor-company-select">
                                <option value="">Pilih vendor...</option>
                                ${optionsHtml}
                            </select>
                        </td>
                        <td>
                            <input
                                type="text"
                                inputmode="numeric"
                                class="form-control vendor-price-input"
                                value="${amountValue}"
                                placeholder="Masukkan harga vendor"
                            >
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger btn-remove-vendor-row">Hapus</button>
                        </td>
                    </tr>
                `;
            });

            $('#vendorPriceTableBody').html(html);
            initVendorSelect2();
        }

        function renumberVendorRows() {
            $('#vendorPriceTableBody .vendor-row-number').each(function(index) {
                $(this).text(index + 1);
            });
        }

        $(document).ready(function() {
            const $filterForm = $('#filterForm');

            function getFilters() {
                return {
                    customerName: $('#customerName').val() || '',
                    origin: $('#origin').val() || '',
                    destination: $('#destination').val() || '',
                    fleetTypeName: $('#fleetTypeName').val() || '',
                    routeTypeName: $('#routeTypeName').val() || ''
                };
            }

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

            const table = $('#dt').DataTable({
                "processing": true,
                "serverSide": true,
                "destroy": true,
                "ajax": {
                    "url": "{{ route('dt.route') }}",
                    "data": function(d) {
                        Object.assign(d, getFilters());
                    }
                },
                "columns": [{
                        "data": 'checkbox',
                        "orderable": false,
                        "searchable": false
                    },
                    {
                        "data": 'action'
                    },
                    {
                        "data": 'DT_RowIndex'
                    },
                    {
                        "data": 'name'
                    },
                    {
                        "data": 'description'
                    },
                    {
                        "data": 'customer.name',
                        "defaultContent": "-"
                    },
                    {
                        "data": 'origin_location.name',
                        "defaultContent": "-"
                    },
                    {
                        "data": 'destination_location.name',
                        "defaultContent": "-"
                    },
                    {
                        "data": 'route_type.name',
                        "defaultContent": "-"
                    },
                    // {
                    // "data": 'fleet_type.name'
                    // },
                    {
                        "data": 'price'
                    },
                    {
                        "data": 'personalVendorPrice'
                    },

                ],
                "columnDefs": [{
                        "searchable": false,
                        "targets": [0, 1, 2]
                    },
                    {
                        "orderable": false,
                        "targets": [0, 1]
                    }
                ],
                "order": [
                    [3, 'asc']
                ]
            })

            // Init Select2 for filters
            $('.select2-filter').select2({
                placeholder: "{{ __('general.choose') }}...",
                allowClear: true,
                width: '100%'
            });

            function reloadWithFilters() {
                syncExportUrls();
                table.ajax.reload(null, true);
            }

            // Event untuk filter button & Enter
            $('#btnFilter').on('click', reloadWithFilters);
            $filterForm.on('keydown', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    reloadWithFilters();
                }
            });

            // Event untuk reset button
            $('#btnResetFilter').on('click', function() {
                $('.select2-filter').val('').trigger('change');
                reloadWithFilters();
            });

            syncExportUrls();

            $('#dt').on('click', '.btn-show-vendor-price', function() {
                activeRouteId = $(this).data('route-id');
                const showUrlTemplate = "{{ route('ajax.route.vendor-prices.show', ['id' => '__ID__']) }}";
                const showUrl = showUrlTemplate.replace('__ID__', activeRouteId);

                $('#vendorPriceCustomerName').text('-');
                $('#vendorPriceOriginDestination').text('-');
                $('#vendorPriceRouteName').text('-');
                $('#vendorPriceAmount').text('-');
                $('#vendorPricePersonalAmount').text('-');
                $('#vendorPriceTableBody').html(
                    '<tr><td colspan="4" class="text-center text-muted">Memuat data...</td></tr>'
                );

                $('#modalVendorPrice').modal('show');

                $.get(showUrl, function(response) {
                    if (!response.success) {
                        $('#vendorPriceTableBody').html(
                            '<tr><td colspan="4" class="text-center text-danger">Gagal memuat data.</td></tr>'
                        );
                        return;
                    }

                    const routeInfo = response.data.route || {};
                    const originName = routeInfo.origin_name || '-';
                    const destinationName = routeInfo.destination_name || '-';

                    $('#vendorPriceCustomerName').text(routeInfo.customer_name || '-');
                    $('#vendorPriceOriginDestination').text(`${originName} - ${destinationName}`);
                    $('#vendorPriceRouteName').text(routeInfo.name || '-');
                    $('#vendorPriceAmount').text(formatCurrency(routeInfo.price));
                    $('#vendorPricePersonalAmount').text(formatCurrency(routeInfo
                        .personal_vendor_price));

                    vendorOptions = response.data.vendors || [];

                    const rows = response.data.rows || [];

                    renderVendorRows(rows);
                }).fail(function() {
                    $('#vendorPriceTableBody').html(
                        '<tr><td colspan="4" class="text-center text-danger">Terjadi kesalahan saat mengambil data.</td></tr>'
                    );
                });
            });

            $('#btnSaveVendorPrice').on('click', function() {
                if (!activeRouteId) {
                    swal('Peringatan', 'Rute belum dipilih.', 'warning');
                    return;
                }

                let hasInvalid = false;
                const rows = [];
                const selectedVendorIds = [];

                $('.vendor-row').each(function() {
                    const vendorId = ($(this).find('.vendor-company-select').val() ?? '').toString()
                        .trim();
                    const rawAmount = ($(this).find('.vendor-price-input').val() ?? '').toString()
                        .trim();

                    if (!vendorId && !rawAmount) {
                        return;
                    }

                    if (!vendorId || !rawAmount) {
                        hasInvalid = true;
                        return false;
                    }

                    if (selectedVendorIds.includes(vendorId)) {
                        hasInvalid = true;
                        return false;
                    }

                    const parsed = parseThousands(rawAmount);

                    if (Number.isNaN(parsed) || parsed < 0) {
                        hasInvalid = true;
                        return false;
                    }

                    selectedVendorIds.push(vendorId);
                    rows.push({
                        fleet_company_id: vendorId,
                        amount: parsed,
                    });
                });

                if (hasInvalid) {
                    swal('Peringatan',
                        'Setiap baris harus isi vendor + harga valid, dan vendor tidak boleh duplikat.',
                        'warning');
                    return;
                }

                const saveUrlTemplate = "{{ route('ajax.route.vendor-prices.save', ['id' => '__ID__']) }}";
                const saveUrl = saveUrlTemplate.replace('__ID__', activeRouteId);

                $('#btnSaveVendorPrice').prop('disabled', true).text('Menyimpan...');

                $.ajax({
                    url: saveUrl,
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        rows: rows,
                    },
                    success: function(response) {
                        $('#btnSaveVendorPrice').prop('disabled', false).text(
                            'Simpan Harga Vendor');

                        if (response.success) {
                            swal('Berhasil', response.message, 'success');
                            $('#modalVendorPrice').modal('hide');
                        } else {
                            swal('Gagal', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        $('#btnSaveVendorPrice').prop('disabled', false).text(
                            'Simpan Harga Vendor');

                        const message = xhr.responseJSON?.message ||
                            'Terjadi kesalahan sistem.';
                        swal('Gagal', message, 'error');
                    }
                });
            });

            $('#modalVendorPrice').on('hidden.bs.modal', function() {
                activeRouteId = null;
                vendorOptions = [];
                $('#vendorPriceTableBody').empty();
            });

            $('#btnAddVendorRow').on('click', function() {
                if (!vendorOptions.length) {
                    swal('Peringatan', 'Data vendor external belum tersedia.', 'warning');
                    return;
                }

                const currentRows = [];
                $('.vendor-row').each(function() {
                    currentRows.push({
                        fleet_company_id: $(this).find('.vendor-company-select').val() ||
                            '',
                        amount: $(this).find('.vendor-price-input').val() || '',
                    });
                });

                currentRows.push({
                    fleet_company_id: '',
                    amount: '',
                });

                renderVendorRows(currentRows);
                renumberVendorRows();
            });

            $('#vendorPriceTableBody').on('click', '.btn-remove-vendor-row', function() {
                $(this).closest('tr').remove();

                if (!$('#vendorPriceTableBody .vendor-row').length) {
                    renderVendorRows([]);
                    return;
                }

                renumberVendorRows();
            });

            $('#vendorPriceTableBody').on('input', '.vendor-price-input', function() {
                formatAngka(this);
            });
        });

        // Handle Check All
        $('#checkAll').on('click', function() {
            $('.route-checkbox').prop('checked', this.checked);
        });

        // Handle individual checkbox change to uncheck 'Check All' if one is unchecked
        $('#dt tbody').on('change', '.route-checkbox', function() {
            if (!this.checked) {
                $('#checkAll').prop('checked', false);
            } else {
                if ($('.route-checkbox:checked').length === $('.route-checkbox').length) {
                    $('#checkAll').prop('checked', true);
                }
            }
        });

        function getSelectedRouteIds() {
            var selectedIds = [];
            $('.route-checkbox:checked').each(function() {
                selectedIds.push($(this).val());
            });

            return selectedIds;
        }

        // Handle Bulk Update Button Click
        $('#btnBulkUpdatePrice').click(function() {
            var selectedIds = getSelectedRouteIds();

            if (selectedIds.length === 0) {
                swal("Peringatan", "Pilih minimal satu rute untuk menyesuaikan harga.", "warning");
                return;
            }

            $('#formBulkUpdatePrice')[0].reset();
            $('.bulk-target-checkbox').prop('checked', true); // default check all

            // Re-initialize select2 with dropdownParent to fix unclickable issue in modal
            $('#bulkUpdateType').select2({
                dropdownParent: $('#modalBulkUpdatePrice'),
                width: '100%',
                minimumResultsForSearch: Infinity // hide search box for this simple select
            });

            $('#modalBulkUpdatePrice').modal('show');
        });

        // Handle Submit Bulk Update
        $('#btnSubmitBulkUpdate').click(function() {
            var selectedIds = getSelectedRouteIds();

            var type = $('#bulkUpdateType').val();
            var percentage = $('#bulkUpdatePercentage').val();

            var targets = [];
            $('.bulk-target-checkbox:checked').each(function() {
                targets.push($(this).val());
            });

            if (!percentage || percentage <= 0) {
                swal("Peringatan", "Masukkan persentase yang valid.", "warning");
                return;
            }

            if (type === 'decrease' && percentage > 100) {
                swal("Peringatan",
                    "Persentase penurunan tidak boleh lebih dari 100% agar harga tidak menjadi negatif.",
                    "warning");
                return;
            }

            if (targets.length === 0) {
                swal("Peringatan", "Pilih minimal satu target harga yang akan diubah.", "warning");
                return;
            }

            $(this).prop('disabled', true).text('Menyimpan...');

            $.ajax({
                url: "{{ route('ajax.route.bulk-update-price') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    ids: selectedIds,
                    type: type,
                    percentage: percentage,
                    targets: targets
                },
                success: function(response) {
                    $('#btnSubmitBulkUpdate').prop('disabled', false).text('Simpan Perubahan');
                    if (response.success) {
                        $('#modalBulkUpdatePrice').modal('hide');
                        swal("Berhasil", response.message, "success");
                        $('#dt').DataTable().ajax.reload(null,
                            false); // Reload without resetting pagination
                        $('#checkAll').prop('checked', false);
                    } else {
                        swal("Gagal", response.message, "error");
                    }
                },
                error: function(xhr) {
                    $('#btnSubmitBulkUpdate').prop('disabled', false).text('Simpan Perubahan');
                    swal("Error", "Terjadi kesalahan sistem.", "error");
                }
            });
        });

        $('#btnBulkDelete').click(function() {
            var selectedIds = getSelectedRouteIds();

            if (selectedIds.length === 0) {
                swal("Peringatan", "Pilih minimal satu rute untuk dihapus.", "warning");
                return;
            }

            swal({
                title: "{{ __('general.are_you_sure') }}",
                text: "{{ __('general.want_to_delete_this_data') }}",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (!willDelete) {
                    swal("{{ __('general.your_data_is_save') }}");
                    return;
                }

                $.ajax({
                    url: "{{ route('ajax.route.bulk-delete') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        ids: selectedIds
                    },
                    success: function(response) {
                        if (response.success) {
                            swal("Berhasil", response.message, "success");
                            $('#dt').DataTable().ajax.reload(null, false);
                            $('#checkAll').prop('checked', false);
                        } else {
                            swal("Gagal", response.message, "error");
                        }
                    },
                    error: function(xhr) {
                        var message = xhr.responseJSON && xhr.responseJSON.message ? xhr
                            .responseJSON.message :
                            "Terjadi kesalahan sistem.";
                        swal("Error", message, "error");
                    }
                });
            });
        });

        function deleteData(uuid) {
            var url = '{{ route('data.route.index') }}/' + uuid;
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
