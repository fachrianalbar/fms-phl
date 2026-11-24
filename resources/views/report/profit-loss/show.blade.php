@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Laporan',
    'secondSegment' => 'Detail Laba Rugi',
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

    <style>
        /* ── Card Utama & Banner Entitas ── */
        .profit-loss-card {
            border-radius: 16px;
            overflow: hidden;
        }

        .profit-loss-card-header {
            border-bottom: 1px solid #e2e8f0;
        }

        .entity-banner-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
        }

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
            padding: 16px 18px;
            border: 1px solid transparent;
            height: 100%;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .summary-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .summary-card h5 {
            font-size: 11.5px;
            margin-bottom: 6px;
            opacity: 0.85;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
        }

        .summary-card h3 {
            font-size: 18px;
            font-weight: 800;
            margin: 0;
            letter-spacing: -0.3px;
        }

        .summary-sales {
            background: linear-gradient(135deg, #eef2ff, #e0e7ff);
            border-color: #c7d2fe;
            color: #3730a3;
        }

        .summary-order-cost {
            background: linear-gradient(135deg, #fff7ed, #ffedd5);
            border-color: #fed7aa;
            color: #9a3412;
        }

        .summary-maintenance {
            background: linear-gradient(135deg, #fefce8, #fef9c3);
            border-color: #fde68a;
            color: #92400e;
        }

        .summary-tax {
            background: linear-gradient(135deg, #fdf2f8, #fce7f3);
            border-color: #fbcfe8;
            color: #9d174d;
        }

        .summary-profit {
            background: linear-gradient(135deg, #ecfdf5, #d1fae5);
            border-color: #a7f3d0;
            color: #065f46;
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
            background-color: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            color: #334155 !important;
            font-size: 13px !important;
            height: 38px !important;
        }

        .filter-control {
            padding: 0 12px 0 36px !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='4' width='18' height='18' rx='2' ry='2'%3E%3C/rect%3E%3Cline x1='16' y1='2' x2='16' y2='6'%3E%3C/line%3E%3Cline x1='8' y1='2' x2='8' y2='6'%3E%3C/line%3E%3Cline x1='3' y1='10' x2='21' y2='10'%3E%3C/line%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: 12px center;
            background-size: 15px 15px;
        }

        .filter-control::placeholder {
            color: #94a3b8 !important;
            font-size: 13px !important;
        }

        .btn-filter-primary {
            align-items: center;
            background: linear-gradient(135deg, #4f46e5, #6366f1) !important;
            border: 0 !important;
            border-radius: 8px !important;
            color: #ffffff !important;
            display: inline-flex;
            font-size: 13px;
            font-weight: 600;
            gap: 6px;
            height: 38px;
            justify-content: center;
            padding: 0 16px;
            white-space: nowrap;
            box-shadow: 0 2px 6px rgba(79, 70, 229, 0.25);
        }

        .btn-filter-reset {
            align-items: center;
            background: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            color: #64748b !important;
            display: inline-flex;
            height: 38px;
            justify-content: center;
            min-width: 38px;
            width: 38px;
            padding: 0 !important;
        }

        /* ── Scoped Table Styling (PHL §6) ── */
        #dt-order,
        #dt-maintenance {
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        #dt-order thead th,
        #dt-maintenance thead th {
            background-color: #f8fafc;
            color: #475569;
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding: 12px;
            border-bottom: 2px solid #e2e8f0;
            border-top: none;
            white-space: nowrap;
            vertical-align: middle;
        }

        #dt-order tbody td,
        #dt-maintenance tbody td {
            padding: 11px 12px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            font-size: 12.5px;
            white-space: nowrap;
            vertical-align: middle;
        }

        #dt-order tbody tr,
        #dt-maintenance tbody tr {
            transition: background-color 0.15s ease;
        }

        #dt-order tbody tr:hover,
        #dt-maintenance tbody tr:hover {
            background-color: #f8fafc !important;
        }

        #dt-order_wrapper .dataTables_filter input,
        #dt-maintenance_wrapper .dataTables_filter input {
            border-radius: 8px;
            font-size: 12px;
        }

        #dt-order_wrapper .dataTables_length select,
        #dt-maintenance_wrapper .dataTables_length select {
            border-radius: 8px;
            font-size: 12px;
        }

        #dt-order_wrapper .dataTables_info,
        #dt-order_wrapper .dataTables_length,
        #dt-order_wrapper .dataTables_filter,
        #dt-maintenance_wrapper .dataTables_info,
        #dt-maintenance_wrapper .dataTables_length,
        #dt-maintenance_wrapper .dataTables_filter {
            color: #64748b;
            font-size: 12px;
        }

        /* ── Dark Mode (WAJIB) ── */
        html[data-bs-theme="dark"] .profit-loss-card {
            background-color: var(--bs-card-bg);
            border: 1px solid var(--bs-border-color);
        }

        html[data-bs-theme="dark"] .profit-loss-card-header {
            border-bottom-color: var(--bs-border-color) !important;
        }

        html[data-bs-theme="dark"] .entity-banner-card {
            background: var(--bs-secondary-bg);
            border-color: var(--bs-border-color);
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

        html[data-bs-theme="dark"] .summary-sales {
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.2), rgba(99, 102, 241, 0.14));
            border-color: rgba(129, 140, 248, 0.35);
            color: #c7d2fe;
        }

        html[data-bs-theme="dark"] .summary-order-cost {
            background: linear-gradient(135deg, rgba(234, 88, 12, 0.2), rgba(251, 146, 60, 0.14));
            border-color: rgba(251, 146, 60, 0.35);
            color: #fed7aa;
        }

        html[data-bs-theme="dark"] .summary-maintenance {
            background: linear-gradient(135deg, rgba(202, 138, 4, 0.2), rgba(250, 204, 21, 0.14));
            border-color: rgba(250, 204, 21, 0.35);
            color: #fde68a;
        }

        html[data-bs-theme="dark"] .summary-tax {
            background: linear-gradient(135deg, rgba(219, 39, 119, 0.2), rgba(244, 114, 182, 0.14));
            border-color: rgba(244, 114, 182, 0.35);
            color: #fbcfe8;
        }

        html[data-bs-theme="dark"] .summary-profit {
            background: linear-gradient(135deg, rgba(5, 150, 105, 0.2), rgba(52, 211, 153, 0.14));
            border-color: rgba(52, 211, 153, 0.35);
            color: #a7f3d0;
        }

        html[data-bs-theme="dark"] #dt-order,
        html[data-bs-theme="dark"] #dt-maintenance {
            border-color: var(--bs-border-color) !important;
        }

        html[data-bs-theme="dark"] #dt-order thead th,
        html[data-bs-theme="dark"] #dt-maintenance thead th {
            background-color: var(--bs-tertiary-bg) !important;
            color: var(--bs-body-color) !important;
            border-color: var(--bs-border-color) !important;
        }

        html[data-bs-theme="dark"] #dt-order tbody td,
        html[data-bs-theme="dark"] #dt-maintenance tbody td {
            color: var(--bs-body-color) !important;
            border-color: var(--bs-border-color) !important;
        }

        html[data-bs-theme="dark"] #dt-order tbody tr:hover,
        html[data-bs-theme="dark"] #dt-maintenance tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.04) !important;
        }

        html[data-bs-theme="dark"] #dt-order_wrapper .dataTables_info,
        html[data-bs-theme="dark"] #dt-order_wrapper .dataTables_length,
        html[data-bs-theme="dark"] #dt-order_wrapper .dataTables_filter,
        html[data-bs-theme="dark"] #dt-maintenance_wrapper .dataTables_info,
        html[data-bs-theme="dark"] #dt-maintenance_wrapper .dataTables_length,
        html[data-bs-theme="dark"] #dt-maintenance_wrapper .dataTables_filter {
            color: var(--bs-secondary-color);
        }
    </style>
@endpush

@section('content')
    <div class="col-sm-12">
        {{-- Card Ringkasan Armada --}}
        <div class="card border-0 shadow-sm profit-loss-card mb-4">
            <div class="card-header bg-white py-3 profit-loss-card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h4 class="mb-1 fw-bold d-flex align-items-center gap-2">
                        <i class="mdi mdi-chart-line text-primary fs-20"></i>
                        Detail {{ $title }}
                    </h4>
                    <small class="text-muted">Rincian pendapatan, biaya operasional, perawatan, pajak, dan laba bersih armada.</small>
                </div>

                <a href="{{ route($view . 'index') }}"
                    class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1"
                    style="border-radius: 8px; font-weight: 600; padding: 7px 14px;">
                    <i class="mdi mdi-arrow-left"></i> {{ __('general.back_to_list') }}
                </a>
            </div>

            <div class="card-body p-3 p-md-4">
                {{-- Banner Profil Entitas Armada --}}
                <div class="card entity-banner-card mb-4">
                    <div class="card-body p-3">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-badge-entity">
                                    <i class="mdi mdi-truck fs-22 text-white"></i>
                                </div>
                                <div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="fs-16 fw-bold font-monospace">{{ $data->plateNumber ?? '-' }}</span>
                                        <span class="badge bg-primary-subtle text-primary px-2 py-1"
                                            style="border-radius: 6px; font-weight: 600; font-size: 11px;">
                                            {{ $data->company?->type ?? 'Internal' }}
                                        </span>
                                    </div>
                                    <div class="text-muted small mt-1">
                                        <i class="mdi mdi-office-building me-1"></i>{{ $data->company?->name ?? '-' }}
                                        <span class="mx-2">•</span>
                                        <i class="mdi mdi-identifier me-1"></i>Kode: <span class="font-monospace">{{ $data->code }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="d-none align-items-center gap-2 bg-light px-3 py-2 rounded-3 border"
                                id="activePeriodBadge" style="font-size: 12.5px;">
                                <i class="mdi mdi-calendar-range text-primary fs-16"></i>
                                <span id="activePeriodText"></span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Filter Card Collapse Tanggal --}}
                <div class="filter-card">
                    <button type="button" class="filter-card-header" data-bs-toggle="collapse"
                        data-bs-target="#profitLossShowFilterCollapse" aria-expanded="false"
                        aria-controls="profitLossShowFilterCollapse">
                        <span class="filter-card-heading">
                            <i class="mdi mdi-filter-variant"></i>
                            <strong>Filter Data</strong>
                            <small>Saring rincian order dan perawatan berdasarkan rentang tanggal</small>
                        </span>
                        <i class="mdi mdi-chevron-down filter-card-chevron"></i>
                    </button>

                    <div class="collapse filter-collapse" id="profitLossShowFilterCollapse">
                        <div class="filter-collapse-body">
                            <div id="filterForm">
                                <div class="row g-3 align-items-end">
                                    <div class="col-xl-4 col-md-6">
                                        <label class="filter-label" for="startDate">Dari Tanggal</label>
                                        <input class="form-control filter-control" name="startDate" id="startDate"
                                            type="text" value="{{ request('startDate') }}"
                                            placeholder="Pilih tanggal mulai">
                                    </div>

                                    <div class="col-xl-4 col-md-6">
                                        <label class="filter-label" for="endDate">Sampai Tanggal</label>
                                        <input class="form-control filter-control" name="endDate" id="endDate"
                                            type="text" value="{{ request('endDate') }}"
                                            placeholder="Pilih tanggal akhir">
                                    </div>

                                    <div class="col-xl-4 col-md-12 d-flex align-items-end justify-content-md-end gap-2">
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

                {{-- Kartu Ringkasan KPI (5 Metrik) --}}
                <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-5 g-3">
                    <div class="col">
                        <div class="summary-card summary-sales">
                            <i class="mdi mdi-cash-plus float-end fs-22 opacity-50"></i>
                            <h5>Penjualan Dasar</h5>
                            <h3 class="font-monospace basicSales">Rp {{ number_format($basicSales, 0, ',', '.') }}</h3>
                        </div>
                    </div>
                    <div class="col">
                        <div class="summary-card summary-order-cost">
                            <i class="mdi mdi-truck-delivery-outline float-end fs-22 opacity-50"></i>
                            <h5>Biaya Order</h5>
                            <h3 class="font-monospace orderCost">Rp {{ number_format($orderCost, 0, ',', '.') }}</h3>
                        </div>
                    </div>
                    <div class="col">
                        <div class="summary-card summary-maintenance">
                            <i class="mdi mdi-wrench-outline float-end fs-22 opacity-50"></i>
                            <h5>Biaya Perawatan</h5>
                            <h3 class="font-monospace maintenance">Rp {{ number_format($maintenance, 0, ',', '.') }}</h3>
                        </div>
                    </div>
                    <div class="col">
                        <div class="summary-card summary-tax">
                            <i class="mdi mdi-percent-outline float-end fs-22 opacity-50"></i>
                            <h5>Pajak (PPh)</h5>
                            <h3 class="font-monospace taxAmount">Rp {{ number_format($tax, 0, ',', '.') }}</h3>
                        </div>
                    </div>
                    <div class="col">
                        <div class="summary-card summary-profit">
                            <i class="mdi mdi-finance float-end fs-22 opacity-50"></i>
                            <h5>Laba Bersih</h5>
                            <h3 class="font-monospace netProfit">Rp {{ number_format($netProfit, 0, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabel Rincian Biaya Order --}}
        <div class="card border-0 shadow-sm profit-loss-card mb-4">
            <div class="card-header bg-white py-3 profit-loss-card-header">
                <h5 class="mb-0 fw-bold d-flex align-items-center gap-2">
                    <i class="mdi mdi-clipboard-list-outline text-primary fs-18"></i>
                    Rincian Biaya Order
                </h5>
            </div>
            <div class="card-body p-3 p-md-4">
                <div class="table-responsive custom-scrollbar" tabindex="0" role="region"
                    aria-label="Tabel Rincian Biaya Order">
                    <table class="table align-middle w-100 nowrap mb-0" id="dt-order">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 52px;">No</th>
                                <th class="text-center">Tanggal Order</th>
                                <th>No. Shipment</th>
                                <th class="text-end">Biaya</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Tabel Rincian Perawatan --}}
        <div class="card border-0 shadow-sm profit-loss-card">
            <div class="card-header bg-white py-3 profit-loss-card-header">
                <h5 class="mb-0 fw-bold d-flex align-items-center gap-2">
                    <i class="mdi mdi-wrench-cog-outline text-primary fs-18"></i>
                    Rincian Perawatan
                </h5>
            </div>
            <div class="card-body p-3 p-md-4">
                <div class="table-responsive custom-scrollbar" tabindex="0" role="region"
                    aria-label="Tabel Rincian Perawatan">
                    <table class="table align-middle w-100 nowrap mb-0" id="dt-maintenance">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 52px;">No</th>
                                <th>Kode</th>
                                <th class="text-center">Tanggal</th>
                                <th>Item</th>
                                <th class="text-end">Biaya</th>
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
    <script src="{{ asset('assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-keytable-bs5/js/keyTable.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-select/js/dataTables.select.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-select-bs5/js/select.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/libs/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/custom-flatpickr.js') }}"></script>

    <script>
        $(document).ready(function() {
            const numberFormat = new Intl.NumberFormat('id-ID', {
                maximumFractionDigits: 0
            });
            const formatCurrency = value => 'Rp ' + numberFormat.format(Math.round(Number(value) || 0));
            const formatServerNominal = data => data ? (data.toString().startsWith('Rp') ? data : 'Rp ' + data) : 'Rp 0';

            let startPicker;
            let endPicker;

            startPicker = flatpickr('#startDate', {
                dateFormat: 'Y-m-d',
                allowInput: true,
                onChange: function(selectedDates, dateStr) {
                    if (endPicker) endPicker.set('minDate', dateStr || null);
                }
            });

            endPicker = flatpickr('#endDate', {
                dateFormat: 'Y-m-d',
                allowInput: true,
                onChange: function(selectedDates, dateStr) {
                    if (startPicker) startPicker.set('maxDate', dateStr || null);
                }
            });

            function syncPeriodBadge() {
                const start = $('#startDate').val();
                const end = $('#endDate').val();
                if (start || end) {
                    $('#activePeriodText').html(
                        'Periode: <strong>' + (start || 'Awal') + '</strong> s/d <strong>' + (end || 'Sekarang') + '</strong>'
                    );
                    $('#activePeriodBadge').removeClass('d-none').addClass('d-flex');
                } else {
                    $('#activePeriodBadge').removeClass('d-flex').addClass('d-none');
                }
            }

            const tableOrder = $('#dt-order').DataTable({
                processing: true,
                serverSide: true,
                destroy: true,
                pageLength: 25,
                ajax: {
                    url: "{{ route('dt.profit-loss-order') }}",
                    data: function(d) {
                        d.startDate = $('#startDate').val() || '';
                        d.endDate = $('#endDate').val() || '';
                        d.fleetCode = "{{ $data->code }}";
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', className: 'text-center align-middle' },
                    { data: 'orderDate', className: 'text-center align-middle' },
                    { data: 'shipmentNumber', className: 'align-middle font-monospace fw-semibold' },
                    {
                        data: 'totalPrice',
                        className: 'text-end align-middle font-monospace fw-bold',
                        render: function(data, type) {
                            return type === 'display' ? formatServerNominal(data) : data;
                        }
                    }
                ],
                columnDefs: [
                    { searchable: false, targets: [0, 3] },
                    { orderable: false, targets: [0, 3] }
                ],
                order: [[1, 'desc']],
                language: {
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                    infoEmpty: 'Tidak ada data',
                    zeroRecords: 'Data tidak ditemukan',
                    processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Memuat data...'
                }
            });

            const tableMaintenance = $('#dt-maintenance').DataTable({
                processing: true,
                serverSide: true,
                destroy: true,
                pageLength: 25,
                ajax: {
                    url: "{{ route('dt.profit-loss-maintenance') }}",
                    data: function(d) {
                        d.startDate = $('#startDate').val() || '';
                        d.endDate = $('#endDate').val() || '';
                        d.fleetCode = "{{ $data->code }}";
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', className: 'text-center align-middle' },
                    { data: 'code', className: 'align-middle font-monospace fw-semibold' },
                    { data: 'maintenanceDate', className: 'text-center align-middle' },
                    { data: 'items', className: 'align-middle' },
                    {
                        data: 'price',
                        className: 'text-end align-middle font-monospace fw-bold',
                        render: function(data, type) {
                            return type === 'display' ? formatServerNominal(data) : data;
                        }
                    }
                ],
                columnDefs: [
                    { searchable: false, targets: [0, 4] },
                    { orderable: false, targets: [0, 3, 4] }
                ],
                order: [[2, 'desc']],
                language: {
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                    infoEmpty: 'Tidak ada data',
                    zeroRecords: 'Data tidak ditemukan',
                    processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Memuat data...'
                }
            });

            function refreshDetailData() {
                syncPeriodBadge();
                tableOrder.ajax.reload(null, true);
                tableMaintenance.ajax.reload(null, true);

                $.ajax({
                    url: "{{ route('ajax.profit-loss-summary', ['id' => $data->code]) }}",
                    method: 'GET',
                    data: {
                        fleetCode: "{{ $data->code }}",
                        startDate: $('#startDate').val() || '',
                        endDate: $('#endDate').val() || ''
                    },
                    success: function(response) {
                        $('.basicSales').text(formatCurrency(response.basicSales));
                        $('.orderCost').text(formatCurrency(response.orderCost));
                        $('.maintenance').text(formatCurrency(response.maintenance));
                        $('.taxAmount').text(formatCurrency(response.tax));
                        $('.netProfit').text(formatCurrency(response.netProfit));
                    }
                });
            }

            $('#btnFilter').on('click', refreshDetailData);

            $('#filterForm input').on('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    refreshDetailData();
                }
            });

            $('#btnResetFilter').on('click', function() {
                $('#filterForm').find('input').val('');
                if (startPicker) {
                    startPicker.clear();
                    startPicker.set('maxDate', null);
                }
                if (endPicker) {
                    endPicker.clear();
                    endPicker.set('minDate', null);
                }
                refreshDetailData();
            });

            syncPeriodBadge();
        });
    </script>
@endpush
