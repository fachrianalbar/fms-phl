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

        /* Make driver, route, and shipment columns uppercase */
        #dt td:nth-child(4),
        #dt td:nth-child(5),
        #dt td:nth-child(6),
        #dt td:nth-child(8) {
            text-transform: uppercase;
        }

        #order-driver-list td:nth-child(2) {
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

        .filter-control {
            padding: 0 12px 0 36px !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='4' width='18' height='18' rx='2' ry='2'%3E%3C/rect%3E%3Cline x1='16' y1='2' x2='16' y2='6'%3E%3C/line%3E%3Cline x1='8' y1='2' x2='8' y2='6'%3E%3C/line%3E%3Cline x1='3' y1='10' x2='21' y2='10'%3E%3C/line%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: 12px center;
            background-size: 15px 15px;
            transition: all 0.2s ease !important;
        }

        .filter-card .form-control#shipmentNumber,
        .filter-card .form-control[name="shipmentNumber"] {
            padding-left: 12px !important;
            background-image: none !important;
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

        /* Modal Select2 */
        .modal .select2-container {
            z-index: 9999;
            width: 100% !important;
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

        /* ── Modal Dark mode ── */
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

        html[data-bs-theme="dark"] #modal-null table {
            border-color: var(--bs-border-color) !important;
            color: var(--bs-body-color) !important;
        }

        html[data-bs-theme="dark"] #modal-null table th {
            background-color: var(--bs-tertiary-bg) !important;
            color: var(--bs-emphasis-color) !important;
            border-color: var(--bs-border-color) !important;
        }

        html[data-bs-theme="dark"] #modal-null table td {
            border-color: var(--bs-border-color) !important;
            color: var(--bs-body-color) !important;
        }

        html[data-bs-theme="dark"] .modal .form-control,
        html[data-bs-theme="dark"] .modal textarea {
            background-color: var(--bs-tertiary-bg) !important;
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
                        <i class="mdi mdi-clipboard-text-clock-outline text-primary fs-20"></i>
                        {{ $title }} Data
                    </h4>
                    <small class="text-muted">Daftar pesanan operasional, penugasan armada, dan rincian tarif/biaya</small>
                </div>

                <div class="d-flex align-items-center gap-2">
                    @if (Auth::user()->roleCode === 'SPRADMIN' || Auth::user()->role?->name === 'System Administrator')
                        <button type="button" class="btn btn-sm btn-warning d-flex align-items-center gap-1" id="btn-recalculate-vendor-price"
                            style="border-radius: 8px; font-weight: 600; padding: 7px 14px;">
                            <i class="mdi mdi-calculator"></i> Hitung Ulang Harga Vendor
                        </button>
                    @endif

                    {{-- Tombol Export Excel --}}
                    <a href="{{ route($view . 'excel-order', ['type' => 'order']) }}"
                        class="btn btn-icon btn-sm bg-success-subtle" id="export-data"
                        data-bs-toggle="tooltip" title="Export Excel">
                        <i class="mdi mdi-file-excel fs-14 text-success"></i>
                    </a>

                    {{-- Tombol Tambah --}}
                    <a href="{{ route($view . 'create') }}"
                        class="btn btn-sm btn-primary d-flex align-items-center gap-1"
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
                        data-bs-target="#orderFilterCollapse" aria-expanded="false"
                        aria-controls="orderFilterCollapse">
                        <span class="filter-card-heading">
                            <i class="mdi mdi-filter-variant"></i>
                            <strong>Filter Data</strong>
                            <small>Gunakan filter untuk mempersempit daftar pesanan</small>
                        </span>
                        <i class="mdi mdi-chevron-down filter-card-chevron"></i>
                    </button>

                    <div class="collapse filter-collapse" id="orderFilterCollapse">
                        <div class="filter-collapse-body">
                            <div id="filterForm">
                                <div class="row g-3">
                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="plateNumber">{{ __('menu_order.plate_number') }}</label>
                                        <select class="form-select select2-filter" name="plateNumber" id="plateNumber">
                                            <option value="">{{ __('general.choose') }}...</option>
                                            @foreach ($fleet as $item)
                                                @php $plateVal = is_array($item) ? ($item['plateNumber'] ?? '') : ($item->plateNumber ?? ''); @endphp
                                                <option value="{{ $plateVal }}">{{ $plateVal }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="driverName">{{ __('menu_order.driver') }}</label>
                                        <select class="form-select select2-filter" name="driverName" id="driverName">
                                            <option value="">{{ __('general.choose') }}...</option>
                                            @foreach ($driver as $item)
                                                @php $driverVal = is_array($item) ? ($item['name'] ?? '') : ($item->name ?? ''); @endphp
                                                <option value="{{ $driverVal }}">{{ $driverVal }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="customerName">{{ __('menu_order.customer') }}</label>
                                        <select class="form-select select2-filter" name="customerName" id="customerName">
                                            <option value="">{{ __('general.choose') }}...</option>
                                            @foreach ($customer as $item)
                                                @php $customerVal = is_array($item) ? ($item['name'] ?? '') : ($item->name ?? ''); @endphp
                                                <option value="{{ $customerVal }}">{{ $customerVal }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="fleetTypeName">{{ __('menu_order.fleet_type') }}</label>
                                        <select class="form-select select2-filter" name="fleetTypeName" id="fleetTypeName">
                                            <option value="">{{ __('general.choose') }}...</option>
                                            @foreach ($fleetType as $item)
                                                @php $fleetTypeVal = is_array($item) ? ($item['name'] ?? '') : ($item->name ?? ''); @endphp
                                                <option value="{{ $fleetTypeVal }}">{{ $fleetTypeVal }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="shipmentNumber">{{ __('menu_order.shipment_no') }}</label>
                                        <input class="form-control filter-control" name="shipmentNumber" id="shipmentNumber"
                                            type="text" placeholder="Cari shipment number...">
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="startDate">{{ __('menu_order.order_date') }} (Dari)</label>
                                        <input class="form-control filter-control" name="startDate" id="startDate"
                                            type="text" placeholder="Pilih tanggal mulai">
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="endDate">{{ __('menu_order.order_date') }} (Sampai)</label>
                                        <input class="form-control filter-control" name="endDate" id="endDate"
                                            type="text" placeholder="Pilih tanggal akhir">
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="orderTypeCode">{{ __('menu_order.order_type') }}</label>
                                        <select class="form-select select2-filter" name="orderTypeCode" id="orderTypeCode">
                                            <option value="">{{ __('general.choose') }}...</option>
                                            @foreach ($orderType as $item)
                                                @php
                                                    $typeCode = is_array($item) ? ($item['code'] ?? '') : ($item->code ?? '');
                                                    $typeName = is_array($item) ? ($item['name'] ?? '') : ($item->name ?? '');
                                                @endphp
                                                <option value="{{ $typeCode }}">{{ $typeName }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-xl-6 col-md-6">
                                        <label class="filter-label" for="destination">{{ __('menu_order.destination') }}</label>
                                        <select class="form-select select2-filter" name="destination" id="destination">
                                            <option value="">{{ __('general.choose') }}...</option>
                                            @foreach ($location as $item)
                                                @php $locVal = is_array($item) ? ($item['name'] ?? '') : ($item->name ?? ''); @endphp
                                                <option value="{{ $locVal }}">{{ $locVal }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-xl-6 col-md-6 d-flex align-items-end justify-content-md-end gap-2">
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
                                <th>#</th>
                                <th>No</th>
                                <th>{{ __('menu_order.order_date') }}</th>
                                <th>{{ __('menu_order.plate_number') }}</th>
                                <th>Route Name</th>
                                <th>{{ __('menu_order.driver') }}</th>
                                <th>Order Type</th>
                                <th>{{ __('menu_order.shipment_no') }}</th>
                                <th>{{ __('menu_order.customer') }}</th>
                                {{-- <th>Material</th> --}}
                                <th>{{ __('menu_order.origin') }}</th>
                                <th>{{ __('menu_order.destination') }}</th>
                                <th>Price</th>
                                <th>Harga Vendor Pribadi</th>
                                <th>Harga Vendor</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Note Data -->
        <div class="modal fade" id="note-modal" tabindex="-1" role="dialog"
            aria-labelledby="noteModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
                    <div class="modal-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar-sm d-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle" style="width: 36px; height: 36px;">
                                <i class="mdi mdi-note-text-outline fs-18"></i>
                            </span>
                            <div>
                                <h5 class="modal-title mb-0 fw-bold" id="noteModalLabel">Catatan Pesanan</h5>
                                <small class="text-muted">Informasi catatan tambahan untuk order ini</small>
                            </div>
                        </div>
                        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <label class="form-label text-muted small fw-semibold mb-2" for="note">Isi Catatan</label>
                        <textarea class="form-control" id="note" rows="4" readonly
                            style="border-radius: 8px; font-size: 13px; line-height: 1.5; resize: none;"></textarea>
                    </div>
                    <div class="modal-footer bg-light py-2 px-4 border-top">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"
                            style="border-radius: 8px; padding: 6px 16px;">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Data Belum Lengkap -->
        <div class="modal fade" tabindex="-1" role="dialog" id="modal-null"
            aria-labelledby="modalNullLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
                    <div class="modal-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar-sm d-flex align-items-center justify-content-center bg-warning-subtle text-warning rounded-circle" style="width: 36px; height: 36px;">
                                <i class="mdi mdi-alert-circle-outline fs-18"></i>
                            </span>
                            <div>
                                <h5 class="modal-title mb-0 fw-bold" id="modalNullLabel">Data Belum Lengkap</h5>
                                <small class="text-muted">Daftar pesanan dengan kelengkapan data yang belum terpenuhi</small>
                            </div>
                        </div>
                        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body card-body p-4">
                        {{-- Diinjeksi oleh AJAX JS --}}
                    </div>
                    <div class="modal-footer bg-light py-2 px-4 border-top">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"
                            style="border-radius: 8px; padding: 6px 16px;">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Add Cost Component -->
        <div class="modal fade" id="modal-cost-component" tabindex="-1" role="dialog"
            aria-labelledby="modalCostComponentLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
                    <div class="modal-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar-sm d-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle" style="width: 36px; height: 36px;">
                                <i class="mdi mdi-cash-multiple fs-18"></i>
                            </span>
                            <div>
                                <h5 class="modal-title mb-0 fw-bold" id="modalCostComponentLabel">Tambah Komponen Biaya</h5>
                                <small class="text-muted">Kelola dan tambahkan rincian komponen biaya operasional pesanan</small>
                            </div>
                        </div>
                        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <input type="hidden" name="orderCode" id="costOrderCode">

                        <!-- Existing Cost Components Section -->
                        <div class="mb-4">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="fw-bold mb-0 text-dark">Daftar Komponen Biaya:</h6>
                                <span class="badge bg-primary-subtle text-primary px-2 py-1" id="total-amount"
                                    style="border-radius: 6px; font-weight: 700; font-size: 12px;">Total: Rp 0</span>
                            </div>
                            <div class="table-responsive" style="border-radius: 8px; border: 1px solid #e2e8f0;">
                                <table class="table align-middle mb-0 modal-table">
                                    <thead class="bg-light">
                                        <tr>
                                            <th style="padding: 10px 12px; font-size: 12px; font-weight: 700; text-transform: uppercase;">Komponen</th>
                                            <th style="padding: 10px 12px; font-size: 12px; font-weight: 700; text-transform: uppercase; text-align: right;">Nominal</th>
                                        </tr>
                                    </thead>
                                    <tbody id="existing-cost-list">
                                        <!-- Existing costs will be loaded here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <hr class="my-3" style="border-color: #e2e8f0;">

                        <!-- Add New Cost Component Section -->
                        <div>
                            <h6 class="fw-bold mb-3 text-dark">Tambah Komponen Baru:</h6>
                            <form id="form-cost-component">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-muted">Nama Komponen Biaya</label>
                                        <select class="form-select select2-modal" name="componentType"
                                            id="componentType">
                                            <option selected="" disabled="" value="">Pilih Komponen...</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-muted">Nominal (Rp)</label>
                                        <input class="form-control" type="text" oninput="formatAngka(this)"
                                            step="0.01" name="nominal" id="nominal" placeholder="Masukkan nominal"
                                            required style="height: 38px; border-radius: 8px; font-size: 13px;">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2 px-4 border-top">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"
                            style="border-radius: 8px; padding: 6px 16px;">Batal</button>
                        <button type="button" class="btn btn-primary btn-sm d-flex align-items-center gap-1" id="btn-add-cost"
                            style="border-radius: 8px; padding: 6px 16px; font-weight: 600;">
                            <i class="mdi mdi-plus"></i> Tambah Komponen
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <form id="delete-form" method="post">
        @csrf
        @method('DELETE')
    </form>

    <form id="finish-order" method="post">
        @csrf
        @method('PUT')
    </form>

    <!-- Modal Ganti Driver -->
    <div class="modal fade" id="modal-add-driver" tabindex="-1" role="dialog"
        aria-labelledby="modalAddDriverLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
                <div class="modal-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="avatar-sm d-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle" style="width: 36px; height: 36px;">
                            <i class="mdi mdi-account-switch fs-18"></i>
                        </span>
                        <div>
                            <h5 class="modal-title mb-0 fw-bold" id="modalAddDriverLabel">{{ __('menu_order.change_driver') }}</h5>
                            <small class="text-muted">Perbarui penugasan supir untuk order terkait</small>
                        </div>
                    </div>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="{{ route('operational.store-order-driver') }}" id="form-add-driver">
                    @csrf
                    <input type="hidden" name="orderCode" id="orderCode">

                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label small fw-semibold text-muted" for="driverCode">{{ __('menu_order.driver') }}</label>
                                <select class="form-control select2-modal" name="driverCode" id="driverCode"
                                    style="width: 100%;">
                                    <option selected="" value="">{{ __('general.choose') }}...</option>
                                    @foreach ($driver as $item)
                                        <option value="{{ $item->code }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label small fw-semibold text-muted"
                                    for="description">{{ __('menu_order.description') }}</label>
                                <textarea class="form-control" name="description" id="description" rows="3"
                                    placeholder="{{ __('menu_order.description') }}"
                                    style="border-radius: 8px; font-size: 13px;"></textarea>
                            </div>
                        </div>

                        <!-- List Data yang sudah diinput -->
                        <div class="mt-4">
                            <h6 class="fw-bold mb-2 text-dark">Riwayat {{ __('menu_order.change_driver') }}</h6>
                            <div class="table-responsive" style="border-radius: 8px; border: 1px solid #e2e8f0;">
                                <table class="table align-middle mb-0 modal-table" id="order-driver-list">
                                    <thead class="bg-light">
                                        <tr>
                                            <th style="padding: 10px 12px; font-size: 12px; font-weight: 700; text-transform: uppercase;">No</th>
                                            <th style="padding: 10px 12px; font-size: 12px; font-weight: 700; text-transform: uppercase;">{{ __('menu_order.driver') }}</th>
                                            <th style="padding: 10px 12px; font-size: 12px; font-weight: 700; text-transform: uppercase;">{{ __('menu_order.description') }}</th>
                                            <th style="padding: 10px 12px; font-size: 12px; font-weight: 700; text-transform: uppercase; text-align: center;">{{ __('menu_order.action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Data akan dimuat via AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light py-2 px-4 border-top d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"
                            style="border-radius: 8px; padding: 6px 16px;">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm d-flex align-items-center gap-1"
                            style="border-radius: 8px; padding: 6px 16px; font-weight: 600;">
                            <i class="mdi mdi-content-save"></i> {{ __('general.save') }}
                        </button>
                    </div>
                </form>
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
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2-custom.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/helper.js') }}"></script>

    <script>
        $(document).ready(function() {
            let filterStartPicker;
            let filterEndPicker;

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
                    customerName: $('#customerName').val() || '',
                    driverName: $('#driverName').val() || '',
                    fleetTypeName: $('#fleetTypeName').val() || '',
                    shipmentNumber: $('#shipmentNumber').val() || '',
                    startDate: $('#startDate').val() || '',
                    endDate: $('#endDate').val() || '',
                    destination: $('#destination').val() || '',
                    orderTypeCode: $('#orderTypeCode').val() || ''
                };
            }

            function syncExportUrls() {
                const params = new URLSearchParams();
                params.set('type', 'order');
                Object.entries(getFilters()).forEach(([key, value]) => {
                    if (value) params.set(key, value);
                });
                const query = '?' + params.toString();
                $('#export-data').attr('href', "{{ route($view . 'excel-order') }}" + query);
            }

            const table = $('#dt').DataTable({
                processing: true,
                serverSide: true,
                destroy: true,
                pageLength: 25,
                ajax: {
                    url: "{{ route('dt.order') }}",
                    data: function(d) {
                        Object.assign(d, getFilters());
                    }
                },
                columns: [
                    { data: 'action', className: 'text-center align-middle', orderable: false, searchable: false },
                    { data: 'DT_RowIndex', className: 'text-center align-middle', orderable: false, searchable: false },
                    { data: 'orderDate', className: 'align-middle text-center' },
                    { data: 'fleet.plateNumber', className: 'align-middle font-monospace fw-semibold' },
                    { data: 'route.name', className: 'align-middle' },
                    { data: 'driver.name', className: 'align-middle' },
                    { data: 'orderType', className: 'align-middle text-center' },
                    { data: 'shipmentNumber', className: 'align-middle font-monospace' },
                    { data: 'customer.name', className: 'align-middle' },
                    { data: 'route.originLocation.name', className: 'align-middle' },
                    { data: 'route.destinationLocation.name', className: 'align-middle' },
                    { data: 'price', className: 'text-end align-middle font-monospace fw-bold' },
                    { data: 'harga_vendor_pribadi', className: 'text-end align-middle font-monospace fw-semibold' },
                    { data: 'harga_vendor', className: 'text-end align-middle font-monospace fw-semibold' },
                    {
                        data: 'status',
                        className: 'align-middle text-center',
                        render: function(data) {
                            if (!data) return '-';
                            const dLower = data.toLowerCase();
                            let badgeClass = 'bg-secondary-subtle text-secondary';
                            if (dLower === 'selesai' || dLower === 'completed' || dLower === 'finish') {
                                badgeClass = 'bg-success-subtle text-success';
                            } else if (dLower.includes('batal') || dLower.includes('cancel')) {
                                badgeClass = 'bg-danger-subtle text-danger';
                            } else if (dLower.includes('jalan') || dLower.includes('proses') || dLower.includes('progress')) {
                                badgeClass = 'bg-warning-subtle text-warning';
                            } else if (dLower.includes('draft') || dLower.includes('order')) {
                                badgeClass = 'bg-info-subtle text-info';
                            }
                            return '<span class="badge ' + badgeClass + ' px-2 py-1" style="border-radius: 6px; font-weight: 600; font-size: 11px;">' + data + '</span>';
                        }
                    }
                ],
                columnDefs: [
                    {
                        searchable: false,
                        targets: [0, 1]
                    },
                    {
                        orderable: false,
                        targets: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14]
                    }
                ],
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
                syncExportUrls();
                table.ajax.reload(null, true);
            }

            $('#btnFilter').on('click', reloadWithFilters);

            $('#shipmentNumber').on('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    reloadWithFilters();
                }
            });

            $('#btnResetFilter').on('click', function() {
                $('#shipmentNumber').val('');
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

            syncExportUrls();

            $('#check-null').on('click', function() {
                $.ajax({
                    url: "{{ route('operational.order.check-null-relation') }}",
                    method: "GET",
                    success: function(response) {
                        if (response.success) {
                            let data = response.data;
                            let content = `
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Shipment Number</th>
                                            <th>Empty Data</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                            `;

                            data.forEach((item, index) => {
                                content += `
                                    <tr>
                                        <td>${index + 1}</td>
                                        <td>${item.shipmentNumber ? item.shipmentNumber.toUpperCase() : ''}</td>
                                        <td>${item.nullRelations}</td>
                                    </tr>
                                `;
                            });

                            content += `
                                    </tbody>
                                </table>
                            `;

                            $('#modal-null .card-body').html(content);
                            $('#modal-null').modal('show');
                        } else {
                            swal({
                                title: "Info",
                                text: "No empty data found",
                                icon: "info",
                            });
                        }
                    },
                    error: function() {
                        swal({
                            title: "{{ __('general.warning') }}",
                            text: "An error occurred while checking null relations.",
                            icon: "warning",
                        });
                    }
                });
            });

            // Event listener untuk modal add driver
            $('#modal-add-driver').on('shown.bs.modal', function() {
                $('.select2-modal').select2({
                    dropdownParent: $('#modal-add-driver'),
                    width: '100%',
                    placeholder: '{{ __('general.choose') }}...',
                });
            });

            $('#modal-add-driver').on('hidden.bs.modal', function() {
                $('.select2-modal').select2('destroy');
            });

        });

        function finishOrder(id) {
            var url = '{{ route('operational.finish-order', ':id') }}';
            url = url.replace(':id', id);
            $('#finish-order').attr('action', url);

            swal({
                title: "{{ __('general.are_you_sure') }}",
                text: "{{ __('menu_order.want_to_finish_this_order') }}",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    $('#finish-order').submit();
                } else {
                    swal("{{ __('general.your_data_is_save') }}");
                }
            });
        }

        function showModal(id) {
            let url = '{{ route('operational.order.show', ':id') }}';
            url = url.replace(':id', id);

            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'JSON',
                cache: false,
                success: function(data) {
                    $('#note').val(data.notes);
                },
                error: function(data, ajaxOptions, thrownError) {
                }
            });
            $('#note-modal').modal('show');
        }

        function deleteData(uuid) {
            var url = '{{ route('operational.order.index') }}/' + uuid;
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

        function addOrderDriver(orderId, orderCode) {
            $('#orderCode').val(orderCode);
            $('#driverCode').val('').trigger('change');
            $('#description').val('');

            loadOrderDrivers(orderCode);

            $('#modal-add-driver').modal('show');
        }

        function loadOrderDrivers(orderCode) {
            $.ajax({
                url: "{{ route('operational.order.get-order-drivers') }}",
                method: "GET",
                data: {
                    orderCode: orderCode
                },
                success: function(response) {
                    let tbody = $('#order-driver-list tbody');
                    tbody.empty();

                    if (response.success && response.data.length > 0) {
                        response.data.forEach((item, index) => {
                            tbody.append(`
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${item.driver ? item.driver.name.toUpperCase() : '-'}</td>
                                    <td>${item.description || '-'}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-icon btn-outline-danger" onclick="deleteOrderDriver('${item.id}', '${orderCode}')">
                                            <i class="mdi mdi-delete fs-14"></i>
                                        </button>
                                    </td>
                                </tr>
                            `);
                        });
                    } else {
                        tbody.append(`
                            <tr>
                                <td colspan="4" class="text-center">Belum ada data supir</td>
                            </tr>
                        `);
                    }
                },
                error: function() {}
            });
        }

        function deleteOrderDriver(id, orderCode) {
            swal({
                title: "{{ __('general.are_you_sure') }}",
                text: "Ingin menghapus data supir ini?",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    $.ajax({
                        url: "{{ route('operational.order.delete-order-driver') }}",
                        method: "DELETE",
                        data: {
                            id: id,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                swal("Sukses", "Data supir berhasil dihapus", "success");
                                loadOrderDrivers(orderCode);
                            } else {
                                swal("Error", response.message, "error");
                            }
                        },
                        error: function() {
                            swal("Error", "Gagal menghapus data", "error");
                        }
                    });
                }
            });
        }

        $('#form-add-driver').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        swal("Sukses", "{{ __('menu_order.change_driver_success') }}", "success");
                        $('#driverCode').val('').trigger('change');
                        $('#description').val('');
                        loadOrderDrivers($('#orderCode').val());
                        $('#modal-add-driver').modal('hide');

                        if ($('#dt').length) {
                            $('#dt').DataTable().ajax.reload(null, false);
                        }
                    } else {
                        swal("Error", response.message ||
                            "{{ __('menu_order.change_driver_failed') }}", "error");
                    }
                },
                error: function() {
                    swal("Error", "{{ __('menu_order.change_driver_failed') }}", "error");
                }
            });
        });

        $('#modal-cost-component').on('shown.bs.modal', function() {
            $('.select2-modal').select2({
                dropdownParent: $('#modal-cost-component'),
                width: '100%',
                placeholder: 'Pilih...',
            });
        });

        $('#modal-cost-component').on('hidden.bs.modal', function() {
            $('.select2-modal').select2('destroy');
        });

        function manageCostComponent(orderId, orderCode) {
            $('#costOrderCode').val(orderCode);

            loadCostComponents();
            loadOrderCosts(orderCode);

            $('#componentType').val('').trigger('change');
            $('#costComponentType').val('').trigger('change');
            $('#nominal').val('');

            $('#modal-cost-component').modal('show');
        }

        function loadCostComponents() {
            $.ajax({
                url: "{{ route('ajax.cost-components-all') }}",
                method: "GET",
                success: function(response) {
                    let options = '<option selected="" disabled="" value="">Pilih...</option>';
                    if (response && response.length > 0) {
                        response.forEach(function(item) {
                            options += `<option value="${item.code}">${item.name}</option>`;
                        });
                    }
                    $('#componentType').html(options);
                },
                error: function() {}
            });
        }

        function loadOrderCosts(orderCode) {
            $.ajax({
                url: "{{ route('operational.order.get-order-costs') }}",
                method: "GET",
                data: {
                    orderCode: orderCode
                },
                success: function(response) {
                    let tbody = $('#existing-cost-list');
                    tbody.empty();

                    let total = 0;

                    if (response.success && response.data.length > 0) {
                        response.data.forEach(function(item) {
                            let nominal = parseFloat(item.nominal);
                            total += nominal;
                            let formattedNominal = new Intl.NumberFormat('id-ID').format(nominal);
                            tbody.append(`
                                <tr>
                                    <td>${item.cost_component ? item.cost_component.name : 'N/A'}</td>
                                    <td class="text-end font-monospace fw-semibold text-dark">Rp ${formattedNominal}</td>
                                </tr>
                            `);
                        });
                    } else {
                        tbody.append(`
                            <tr>
                                <td colspan="3" class="text-center">Belum ada data</td>
                            </tr>
                        `);
                    }

                    let formattedTotal = new Intl.NumberFormat('id-ID').format(total);
                    $('#total-amount').text(`Total: Rp ${formattedTotal}`);
                },
                error: function() {}
            });
        }

        function deleteOrderCost(id, orderCode) {
            swal({
                title: "Konfirmasi",
                text: "Yakin ingin menghapus komponen biaya ini?",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    $.ajax({
                        url: "{{ route('operational.order.delete-order-cost') }}",
                        method: "DELETE",
                        data: {
                            id: id,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                swal("Sukses", "Komponen biaya berhasil dihapus", "success");
                                loadOrderCosts(orderCode);
                            } else {
                                swal("Error", response.message, "error");
                            }
                        },
                        error: function() {
                            swal("Error", "Gagal menghapus komponen biaya", "error");
                        }
                    });
                }
            });
        }

        $('#btn-add-cost').on('click', function(e) {
            e.preventDefault();

            let formData = {
                orderCode: $('#costOrderCode').val(),
                componentType: $('#componentType').val(),
                costComponentType: $('#costComponentType').val(),
                nominal: $('#nominal').val(),
                _token: '{{ csrf_token() }}'
            };

            if (!formData.nominal || formData.nominal <= 0) {
                swal("Warning", "Please enter a valid amount", "warning");
                return;
            }

            $.ajax({
                url: "{{ route('operational.order.store-order-cost') }}",
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        swal("Success", "Komponen biaya berhasil ditambahkan", "success");
                        $('#componentType').val('').trigger('change');
                        $('#costComponentType').val('').trigger('change');
                        $('#nominal').val('');
                        loadOrderCosts($('#costOrderCode').val());
                    } else {
                        swal("Error", response.message || "Failed to add cost component", "error");
                    }
                },
                error: function(xhr) {
                    let message = "Failed to add cost component";
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    swal("Error", message, "error");
                }
            });
        });

        @if (Auth::user()->roleCode === 'SPRADMIN' || Auth::user()->role?->name === 'System Administrator')
        $('#btn-recalculate-vendor-price').click(function(e) {
            e.preventDefault();
            swal({
                title: "Apakah Anda yakin?",
                text: "Tindakan ini akan menghitung ulang seluruh Harga Vendor Pribadi dan Harga Vendor berdasarkan Satuan x Qty yang ada pada tabel Order saat ini.",
                icon: "warning",
                buttons: {
                    cancel: "Batal",
                    confirm: {
                        text: "Ya, Hitung Ulang!",
                        closeModal: false
                    }
                },
                dangerMode: true,
            }).then((willRecalculate) => {
                if (willRecalculate) {
                    $.ajax({
                        url: "{{ route('operational.order.recalculate-vendor-prices') }}",
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
                            let msg = "Terjadi kesalahan saat menghitung ulang harga vendor.";
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
    </script>
@endpush
