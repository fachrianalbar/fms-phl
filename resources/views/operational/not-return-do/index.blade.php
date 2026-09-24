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

    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/select2.css') }}">

    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom-select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">

    <style>
        /* Modal */
        #detailModal .modal-dialog {
            max-width: 1200px;
        }

        #detailModal .modal-content {
            border: none;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        #detailModal .modal-header {
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            padding: 16px 20px;
        }

        #detailModal .modal-title {
            font-size: 1rem;
            font-weight: 600;
            color: #212529;
        }

        #detailModal .btn-close {
            opacity: 0.5;
        }

        #detailModal .btn-close:hover {
            opacity: 0.7;
        }

        #detailModal .modal-body {
            padding: 16px 20px;
            background: #fff;
        }

        /* Detail Order Card */
        .detail-order-card {
            background: transparent;
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 0;
            border: 1px solid #dee2e6;
        }

        .card-header-custom {
            background: #f8f9fa;
            color: #212529;
            padding: 12px 16px;
            font-weight: 600;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #dee2e6;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .card-body-custom {
            padding: 0;
        }

        .info-item {
            background: transparent;
            padding: 8px 16px;
            border-radius: 0;
            border-left: none;
            margin-bottom: 0;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            min-height: 38px;
        }

        .info-item:last-child {
            margin-bottom: 0;
            border-bottom: none;
        }

        .info-item:nth-child(odd) {
            background: #fafbfc;
        }

        .info-item label {
            display: block;
            font-size: 0.75rem;
            color: #6c757d;
            margin-bottom: 0;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            font-weight: 600;
            min-width: 140px;
            padding-right: 16px;
        }

        .info-item p {
            margin: 0;
            color: #212529;
            font-weight: 500;
            font-size: 0.9rem;
            text-align: right;
            flex: 1;
        }

        /* Form Section */
        .form-section {
            background: #f8f9fa;
            border-radius: 6px;
            padding: 16px;
            border: 1px solid #e9ecef;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }

        .form-group-custom {
            margin-bottom: 12px;
        }

        .form-group-custom:last-child {
            margin-bottom: 0;
        }

        .form-label-custom {
            display: flex;
            align-items: center;
            font-weight: 600;
            font-size: 0.85rem;
            color: #495057;
            margin-bottom: 6px;
        }

        .form-label-custom i {
            color: #667eea;
            font-size: 1rem;
            margin-right: 6px;
        }

        .form-label-custom .required {
            color: #e74c3c;
            margin-left: 2px;
        }

        .form-control-custom {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            background: white;
        }

        .form-control-custom:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.08);
        }

        .form-control-custom::placeholder {
            color: #adb5bd;
        }

        textarea.form-control-custom {
            min-height: 80px;
            resize: vertical;
            font-family: inherit;
        }

        .form-hint {
            display: block;
            margin-top: 4px;
            font-size: 0.75rem;
            color: #6c757d;
        }

        #detailModal .modal-footer {
            padding: 12px 20px;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
            gap: 6px;
        }

        .btn-cancel {
            padding: 7px 16px;
            border: 1px solid #dee2e6;
            background: white;
            border-radius: 4px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .btn-confirm {
            padding: 7px 16px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 4px;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .btn-confirm:hover {
            background: #5568d3;
        }

        /* Flatpickr Custom Style */
        .flatpickr-calendar {
            border-radius: 6px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
        }

        .flatpickr-day.selected {
            background: #667eea;
            border-color: #667eea;
        }

        .flatpickr-day.selected:hover {
            background: #5568d3;
            border-color: #5568d3;
        }

        /* Legacy support */
        .bg-teal {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        }

        .text-teal {
            color: #667eea !important;
        }

        /* PHL table standard */
        .not-return-do-card {
            border-radius: 16px;
            overflow: hidden;
        }
        .not-return-do-card > .card-header {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 18px 22px;
        }
        .not-return-do-title {
            color: #0f172a;
            font-size: 18px;
            font-weight: 700;
            margin: 0;
        }
        .not-return-do-subtitle {
            color: #64748b;
            font-size: 12px;
            margin-top: 4px;
        }
        .not-return-do-filter {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            margin: 0 0 20px;
            overflow: hidden;
        }
        .not-return-do-filter-header {
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
        .not-return-do-filter-header:hover { background: #f1f5f9; }
        .not-return-do-filter-heading { align-items: center; display: flex; gap: 8px; }
        .not-return-do-filter-heading i { color: #4f46e5; font-size: 17px; }
        .not-return-do-filter-heading strong { font-size: 13px; font-weight: 700; }
        .not-return-do-filter-heading small { color: #94a3b8; font-size: 11px; font-weight: 400; }
        .not-return-do-filter-chevron { transition: transform .2s ease; }
        .not-return-do-filter-header[aria-expanded="true"] .not-return-do-filter-chevron { transform: rotate(180deg); }
        .not-return-do-filter .filter-collapse { border-top: 1px solid #e2e8f0; }
        .not-return-do-filter .filter-collapse-body { padding: 16px; }
        .not-return-do-filter .row { --bs-gutter-y: .75rem; }
        .filter-label {
            color: #64748b;
            display: block;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 6px;
        }
        .filter-control,
        .not-return-do-filter .form-control {
            background-color: #fff !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            color: #334155 !important;
            font-size: 13px !important;
            height: 38px !important;
        }
        .filter-control { padding: 0 12px 0 36px !important; }
        .not-return-do-filter .form-control[name="shipmentNumber"] { padding-left: 12px !important; }
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
        #dt {
            border: 1px solid #e2e8f0;
            border-collapse: separate;
            border-radius: 12px;
            border-spacing: 0;
            overflow: hidden;
        }
        #dt thead th {
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            color: #475569;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .45px;
            padding: 13px 12px;
            text-transform: uppercase;
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
        #dt tbody tr:hover { background: #f8fafc !important; }
        html[data-bs-theme="dark"] #dt tbody tr:hover { background: rgba(255, 255, 255, 0.04) !important; }
        #dt tbody tr:last-child td { border-bottom: 0; }
        #dt_wrapper .dataTables_info,
        #dt_wrapper .dataTables_length,
        #dt_wrapper .dataTables_filter { color: #64748b; font-size: 12px; }
        #dt_wrapper .dataTables_filter input,
        #dt_wrapper .dataTables_length select { border-radius: 8px; font-size: 12px; }
        #dt .text-end { font-variant-numeric: tabular-nums; }
        .btn-icon { border-radius: 8px !important; }
        @media (max-width: 767.98px) {
            .not-return-do-card > .card-header { padding: 16px; }
            .not-return-do-actions { width: 100%; }
        }

        /* ── Dark mode ─────────────────────────────── */
        /* Detail modal */
        html[data-bs-theme="dark"] #detailModal .modal-header {
            background: var(--bs-tertiary-bg);
            border-bottom-color: var(--bs-border-color);
        }
        html[data-bs-theme="dark"] #detailModal .modal-title {
            color: var(--bs-heading-color);
        }
        html[data-bs-theme="dark"] #detailModal .modal-body {
            background: var(--bs-card-bg);
        }
        html[data-bs-theme="dark"] #detailModal .modal-footer {
            background: var(--bs-secondary-bg);
            border-top-color: var(--bs-border-color);
        }
        html[data-bs-theme="dark"] .detail-order-card {
            border-color: var(--bs-border-color);
        }
        html[data-bs-theme="dark"] .card-header-custom {
            background: var(--bs-tertiary-bg);
            color: var(--bs-body-color);
            border-bottom-color: var(--bs-border-color);
        }
        html[data-bs-theme="dark"] .info-item {
            border-bottom-color: var(--bs-border-color);
        }
        html[data-bs-theme="dark"] .info-item:nth-child(odd) {
            background: rgba(255, 255, 255, 0.03);
        }
        html[data-bs-theme="dark"] .info-item label {
            color: var(--bs-secondary-color);
        }
        html[data-bs-theme="dark"] .info-item p {
            color: var(--bs-body-color);
        }
        html[data-bs-theme="dark"] .form-section {
            background: var(--bs-secondary-bg);
            border-color: var(--bs-border-color);
        }
        html[data-bs-theme="dark"] .form-label-custom {
            color: var(--bs-body-color);
        }
        html[data-bs-theme="dark"] .form-control-custom {
            background: var(--bs-tertiary-bg);
            border-color: var(--bs-border-color);
            color: var(--bs-body-color);
        }
        html[data-bs-theme="dark"] .form-control-custom:focus {
            background: var(--bs-secondary-bg);
            border-color: #818cf8;
        }
        html[data-bs-theme="dark"] .form-hint {
            color: var(--bs-secondary-color);
        }
        html[data-bs-theme="dark"] .btn-cancel {
            background: var(--bs-tertiary-bg);
            border-color: var(--bs-border-color);
            color: var(--bs-body-color);
        }

        /* List card & filter */
        html[data-bs-theme="dark"] .not-return-do-card > .card-header {
            background: var(--bs-card-bg);
            border-bottom-color: var(--bs-border-color);
        }
        html[data-bs-theme="dark"] .not-return-do-title {
            color: var(--bs-heading-color);
        }
        html[data-bs-theme="dark"] .not-return-do-filter {
            background: var(--bs-secondary-bg);
            border-color: var(--bs-border-color);
        }
        html[data-bs-theme="dark"] .not-return-do-filter-header {
            background: var(--bs-secondary-bg);
            color: var(--bs-body-color);
        }
        html[data-bs-theme="dark"] .not-return-do-filter-header:hover {
            background: var(--bs-tertiary-bg);
        }
        html[data-bs-theme="dark"] .not-return-do-filter .filter-collapse {
            border-top-color: var(--bs-border-color);
        }
        html[data-bs-theme="dark"] .filter-label {
            color: var(--bs-secondary-color);
        }
        html[data-bs-theme="dark"] .filter-control,
        html[data-bs-theme="dark"] .not-return-do-filter .form-control {
            background-color: var(--bs-tertiary-bg) !important;
            border-color: var(--bs-border-color) !important;
            color: var(--bs-body-color) !important;
        }
        html[data-bs-theme="dark"] .btn-filter-reset {
            background: var(--bs-tertiary-bg) !important;
            border-color: var(--bs-border-color) !important;
            color: var(--bs-body-color) !important;
        }

        /* Table */
        html[data-bs-theme="dark"] #dt {
            border-color: var(--bs-border-color) !important;
        }
        html[data-bs-theme="dark"] #dt thead th {
            background: var(--bs-tertiary-bg) !important;
            color: var(--bs-body-color) !important;
            border-color: var(--bs-border-color) !important;
        }
        html[data-bs-theme="dark"] #dt tbody td {
            color: var(--bs-body-color) !important;
            border-color: var(--bs-border-color) !important;
        }
        html[data-bs-theme="dark"] #dt tbody tr:hover {
            background: rgba(255, 255, 255, 0.05) !important;
        }
        html[data-bs-theme="dark"] #dt .shipment-detail-list {
            border-top-color: var(--bs-border-color);
        }
        html[data-bs-theme="dark"] #dt .shipment-detail-item {
            color: var(--bs-secondary-color);
        }
        html[data-bs-theme="dark"] #dt .shipment-detail-name {
            color: var(--bs-body-color);
        }
    </style>
@endpush

@section('content')
    <form class="col-sm-12" method="POST" action="{{ route('operational.not-return-do.confirm-do') }}">
        <div class="card border-0 shadow-sm not-return-do-card">
            @csrf
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h4 class="not-return-do-title"><i class="mdi mdi-file-cancel-outline text-primary me-2"></i>{{ $title }}</h4>
                    <div class="not-return-do-subtitle">Daftar delivery order yang belum menerima dokumen return.</div>
                </div>
                <div class="not-return-do-actions d-flex align-items-center gap-2">
                    <a href="{{ route('operational.not-return-do.export-excel') }}" target="_blank" id="export-excel"
                        class="btn btn-icon btn-sm bg-success-subtle" data-bs-toggle="tooltip" title="Export Excel">
                        <i class="mdi mdi-file-excel fs-14 text-success"></i>
                    </a>
                    <a href="{{ route('operational.not-return-do.export-pdf') }}" target="_blank" id="export-pdf"
                        class="btn btn-icon btn-sm bg-danger-subtle" data-bs-toggle="tooltip" title="Export PDF">
                        <i class="mdi mdi-file-pdf-box fs-14 text-danger"></i>
                    </a>
                    <button type="button" id="saveOrder" class="btn btn-primary" style="display: none;">
                        <i class="mdi mdi-calendar-check"></i> {{ __('menu_not_return_do.confirm_return') }}
                    </button>
                </div>
            </div>

            <div class="card-body pt-3 pb-0">
                <div class="not-return-do-filter">
                    <button type="button" class="not-return-do-filter-header" data-bs-toggle="collapse"
                        data-bs-target="#notReturnDoFilterCollapse" aria-expanded="false"
                        aria-controls="notReturnDoFilterCollapse">
                        <span class="not-return-do-filter-heading">
                            <i class="mdi mdi-filter-variant"></i>
                            <strong>Filter Data</strong>
                            <small>Gunakan filter untuk mempersempit daftar DO</small>
                        </span>
                        <i class="mdi mdi-chevron-down not-return-do-filter-chevron"></i>
                    </button>

                    <div class="collapse filter-collapse" id="notReturnDoFilterCollapse">
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
                                        <input class="form-control" name="shipmentNumber" id="shipmentNumber" type="text" placeholder="Cari shipment...">
                                    </div>
                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="startDate">Dari Tanggal</label>
                                        <input class="form-control filter-control" name="startDate" id="startDate" type="text" placeholder="Pilih tanggal mulai">
                                    </div>
                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="endDate">Sampai Tanggal</label>
                                        <input class="form-control filter-control" name="endDate" id="endDate" type="text" placeholder="Pilih tanggal akhir">
                                    </div>
                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="orderTypeCode">Tipe Order</label>
                                        <select class="form-select select2-filter" name="orderTypeCode" id="orderTypeCode">
                                            <option value="">Semua Tipe Order</option>
                                            @foreach ($orderType as $item)
                                                @php $typeCode = is_array($item) ? ($item['code'] ?? '') : ($item->code ?? ''); @endphp
                                                <option value="{{ $typeCode }}">{{ $typeCode }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xl-6 col-md-8">
                                        <label class="filter-label" for="destination">{{ __('menu_order.destination') }}</label>
                                        <select class="form-select select2-filter" name="destination" id="destination">
                                            <option value="">{{ __('general.choose') }}...</option>
                                            @foreach ($location as $item)
                                                @php $locVal = is_array($item) ? ($item['name'] ?? '') : ($item->name ?? ''); @endphp
                                                <option value="{{ $locVal }}">{{ $locVal }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xl-6 col-md-4 d-flex align-items-end justify-content-md-end gap-2">
                                        <button class="btn btn-filter-primary flex-grow-1 flex-md-grow-0" type="button" id="btnFilter">
                                            <i class="mdi mdi-filter-outline"></i> Terapkan Filter
                                        </button>
                                        <button class="btn btn-filter-reset" type="button" id="btnResetFilter" data-bs-toggle="tooltip" title="Reset Filter">
                                            <i class="mdi mdi-refresh"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body pt-0">
                @include('partials.alert')
                <div class="table-responsive custom-scrollbar">
                    <style>
                        /* Make driver column uppercase */
                        #dt td:nth-child(5) {
                            text-transform: uppercase;
                        }

                        /* Make shipment number uppercase */
                        #dt td:nth-child(7) {
                            text-transform: uppercase;
                        }

                        #dt .shipment-main {
                            font-weight: 600;
                        }

                        #dt .shipment-detail-list {
                            margin-top: 4px;
                            border-top: 1px dashed #d9dde3;
                            padding-top: 4px;
                        }

                        #dt .shipment-detail-item {
                            font-size: 12px;
                            line-height: 1.35;
                            color: #5b6470;
                            white-space: normal;
                        }

                        #dt .shipment-detail-name {
                            font-weight: 600;
                            color: #39424e;
                        }
                    </style>
                    <table class="table align-middle w-100 nowrap mb-0" id="dt">
                        <thead>
                            <tr>
                                <th>Aksi</th>
                                <th>No</th>
                                <th>{{ __('menu_order.order_date') }}</th>
                                <th>{{ __('menu_order.plate_number') }}</th>
                                <th>Route Name</th>
                                <th>{{ __('menu_order.driver') }}</th>
                                <th>Order Type</th>
                                <th>{{ __('menu_order.order_code') }}</th>
                                <th>{{ __('menu_order.shipment_no') }}</th>
                                <th>{{ __('menu_order.customer') }}</th>
                                <th>{{ __('menu_order.origin') }}</th>
                                <th>{{ __('menu_order.destination') }}</th>
                                <th>Price</th>
                                <th>Harga Vendor</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                </div>
            </div>
        </div>

        <div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-md mt-4">
                <div class="modal-content">
                    <!-- Header -->
                    <div class="modal-header">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle me-3">
                                <i class="mdi mdi-arrow-u-left-bottom"></i>
                            </div>
                            <div>
                                <h5 class="modal-title mb-0" id="doModalLabel">Konfirmasi Return Order</h5>
                                <small class="text-muted">Lengkapi data untuk konfirmasi return</small>
                            </div>
                        </div>
                        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <!-- Alert untuk upload wajib -->
                        <div class="alert alert-warning d-flex align-items-center" role="alert">
                            <i class="mdi mdi-alert-circle me-2" style="font-size: 20px;"></i>
                            <div>
                                <strong>Upload Surat Jalan Wajib!</strong><br>
                                <small>Minimal 1 file harus diupload sebelum mengkonfirmasi return order.</small>
                            </div>
                        </div>

                        <!-- Single Item - Full Details -->
                        <div id="singleItemDetails" style="display: none;">
                            <div class="detail-order-card">
                                <div class="card-header-custom">
                                    <i class="mdi mdi-file-document-outline me-2"></i>
                                    <span>Detail Order</span>
                                </div>
                                <div class="card-body-custom">
                                    <div class="info-item">
                                        <label>Shipment No.</label>
                                        <p id="singleShipmentNo">-</p>
                                    </div>
                                    <div class="info-item">
                                        <label>Order Date</label>
                                        <p id="singleOrderDate">-</p>
                                    </div>
                                    <div class="info-item">
                                        <label>Customer</label>
                                        <p id="singleCustomerName">-</p>
                                    </div>
                                    <div class="info-item">
                                        <label>Fleet</label>
                                        <p id="singleFleet">-</p>
                                    </div>
                                    <div class="info-item">
                                        <label>Origin</label>
                                        <p id="singleOrigin">-</p>
                                    </div>
                                    <div class="info-item">
                                        <label>Destination</label>
                                        <p id="singleDestination">-</p>
                                    </div>
                                    <div class="info-item">
                                        <label>Driver</label>
                                        <p id="singleDriver">-</p>
                                    </div>
                                    <div class="info-item">
                                        <label>Order Type</label>
                                        <p id="singleOrderType">-</p>
                                    </div>
                                    <div class="info-item">
                                        <label>Harga</label>
                                        <p id="singlePrice">-</p>
                                    </div>
                                    <div class="info-item" id="vendorPriceContainer" style="display: none;">
                                        <label>Harga Vendor</label>
                                        <p id="singleVendorPrice">-</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Section -->
                        <div class="form-section">
                            <div class="row g-3">
                                <!-- Date & Time -->
                                <div class="col-12">
                                    <div class="form-group-custom">
                                        <label class="form-label-custom">
                                            <i class="mdi mdi-calendar-clock me-2"></i>
                                            Tanggal & Waktu Return
                                            <span class="required">*</span>
                                        </label>
                                        <input class="form-control-custom" name="returnDate" id="returnDate"
                                            type="text" placeholder="Pilih tanggal dan waktu" required>
                                        <small class="form-hint">Format: DD/MM/YYYY, HH:MM</small>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="col-12">
                                    <div class="form-group-custom">
                                        <label class="form-label-custom">
                                            <i class="mdi mdi-text-box-outline me-2"></i>
                                            Keterangan
                                        </label>
                                        <textarea class="form-control-custom" name="returnDescription" id="description"
                                            placeholder="Masukkan keterangan (opsional)" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="modal-footer">
                        <button class="btn btn-cancel" type="button" data-bs-dismiss="modal">
                            <i class="mdi mdi-close-circle-outline me-2"></i>
                            Batal
                        </button>
                        <a href="#" class="btn btn-secondary" id="editOrderBtn">
                            <i class="mdi mdi-pencil me-2"></i>
                            Edit Order
                        </a>
                        <button type="button" class="btn btn-warning" id="uploadSuratJalanBtn">
                            <i class="mdi mdi-upload me-2"></i>
                            Upload Surat Jalan
                        </button>
                        <button class="btn btn-confirm" type="submit" id="submitReturnBtn" disabled
                            title="Upload minimal 1 file Surat Jalan terlebih dahulu">
                            <i class="mdi mdi-check-circle-outline me-2"></i>
                            Konfirmasi Return
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Modal for Return Processing -->
    <div class="modal fade" id="returnModal" tabindex="-1" aria-labelledby="returnModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="returnModalLabel">
                        <i class="mdi mdi-calendar-clock"></i> {{ __('menu_not_return_do.process_return') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="returnForm">
                    @csrf
                    <input type="hidden" id="returnOrderCode" name="orderCode">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="returnDatetime">
                                <i class="mdi mdi-calendar-clock"></i> {{ __('menu_not_return_do.return_datetime') }}
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control flatpickr-datetime" id="returnDatetime"
                                name="returnDate" placeholder="{{ __('general.choose') }}" required>
                            <small class="text-muted">Format: {{ __('menu_not_return_do.date') }} & Time</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="returnDesc">
                                <i class="mdi mdi-text-box"></i> {{ __('menu_not_return_do.return_description') }}
                            </label>
                            <textarea class="form-control" id="returnDesc" name="returnDescription" rows="4"
                                placeholder="{{ __('menu_not_return_do.return_description') }}"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="mdi mdi-close"></i> {{ __('menu_not_return_do.cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-check"></i> {{ __('menu_not_return_do.save_return') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Upload Surat Jalan -->
    <div class="modal fade" id="uploadSuratJalanModal" tabindex="-1" aria-labelledby="uploadSuratJalanLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadSuratJalanLabel">Upload Surat Jalan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="uploadSuratJalanForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="uploadOrderCode" name="orderCode">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="suratJalanFiles" class="form-label">Pilih File(s) <span
                                    class="text-danger">*</span></label>

                            <!-- Drag & Drop Zone -->
                            <div id="dropZone"
                                class="border-2 border-dashed border-primary rounded p-4 text-center bg-light cursor-pointer mb-3"
                                style="min-height: 120px; display: flex; align-items: center; justify-content: center;">
                                <div>
                                    <i class="mdi mdi-cloud-upload" style="font-size: 32px; color: #0d6efd;"></i>
                                    <p class="mb-0 mt-2 text-muted">Drag & drop file(s) atau klik untuk browse</p>
                                    <small class="text-muted">PDF, JPG, JPEG, PNG | Max 5MB per file</small>
                                </div>
                            </div>

                            <!-- Hidden File Input -->
                            <input class="form-control" type="file" id="suratJalanFiles" name="files[]" multiple
                                accept=".pdf,.jpg,.jpeg,.png" required style="display: none;">
                        </div>

                        <!-- File Preview -->
                        <div id="filePreview" class="mb-3">
                            <div id="fileList"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="uploadBtn">
                            <i class="mdi mdi-upload"></i> Upload
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
    <script src=" {{ asset('assets/js/select2/select2-custom.js') }}"></script>

    <link rel="stylesheet" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">
    <script src="{{ asset('assets/js/flat-pickr/flatpickr.js') }}"></script>
    {{-- <script src="../assets/js/sweet-alert/app.js"></script> --}}

    <script>
        $(document).ready(function() {
            const $filterForm = $('#filterForm');
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
                    filterEndPicker.set('minDate', dateStr || null);
                }
            });

            filterEndPicker = flatpickr('#endDate', {
                dateFormat: 'Y-m-d',
                allowInput: true,
                onChange: function(selectedDates, dateStr) {
                    filterStartPicker.set('maxDate', dateStr || null);
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
                Object.entries(getFilters()).forEach(([key, value]) => {
                    if (value) params.set(key, value);
                });
                const query = params.toString() ? '?' + params.toString() : '';
                $('#export-excel').attr('href', '{{ route('operational.not-return-do.export-excel') }}' + query);
                $('#export-pdf').attr('href', '{{ route('operational.not-return-do.export-pdf') }}' + query);
            }

            const table = $('#dt').DataTable({
                processing: true,
                serverSide: true,
                destroy: true,
                pageLength: 25,
                ajax: {
                    url: '{{ route('dt.not-return-do') }}',
                    data: function(d) {
                        Object.assign(d, getFilters());
                    }
                },
                columns: [
                    { data: 'action', className: 'text-center align-middle', orderable: false, searchable: false },
                    { data: 'DT_RowIndex', className: 'text-center align-middle', orderable: false, searchable: false },
                    { data: 'orderDate', className: 'text-center align-middle' },
                    { data: 'fleet.plateNumber', className: 'align-middle font-monospace fw-semibold' },
                    { data: 'route.name', className: 'align-middle' },
                    { data: 'driver.name', className: 'align-middle' },
                    { data: 'orderType', className: 'text-center align-middle' },
                    { data: 'code', className: 'align-middle font-monospace fw-semibold' },
                    { data: 'shipmentNumber', className: 'align-middle font-monospace' },
                    { data: 'customer.name', className: 'align-middle' },
                    { data: 'route.originLocation.name', className: 'align-middle' },
                    { data: 'route.destinationLocation.name', className: 'align-middle' },
                    { data: 'price', className: 'text-end align-middle font-monospace fw-semibold' },
                    { data: 'harga_vendor', className: 'text-end align-middle font-monospace fw-semibold' },
                    { data: 'status', className: 'text-center align-middle' }
                ],
                columnDefs: [
                    { orderable: false, targets: [0, 1, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14] }
                ],
                order: [[2, 'asc']],
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
            $('#shipmentNumber').on('keydown', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    reloadWithFilters();
                }
            });
            $('#btnResetFilter').on('click', function() {
                $filterForm.find('input').val('');
                $('.select2-filter').val('').trigger('change');
                filterStartPicker.clear();
                filterEndPicker.clear();
                filterStartPicker.set('maxDate', null);
                filterEndPicker.set('minDate', null);
                reloadWithFilters();
            });

            syncExportUrls();

            // Date-time picker for the return modal remains independent from report filters.
            flatpickr('#returnDate', {
                enableTime: true,
                dateFormat: 'd/m/Y, H:i',
                time_24hr: true,
                minuteIncrement: 1
            });

            const saveOrderBtn = document.getElementById('saveOrder');
            if (saveOrderBtn) {
                saveOrderBtn.addEventListener('click', function(event) {
                    event.preventDefault();
                    swal({
                        title: '{{ __('general.warning') }}',
                        text: 'Please use action button on each row to process returns',
                        icon: 'info'
                    });
                });
            }

            $(document).on('click', '.rollback-btn', function() {
                const id = $(this).data('id');
                const shipment = $(this).data('shipment');
                swal({
                    title: 'Apakah Anda yakin?',
                    text: `Ingin mengembalikan status pesanan ${shipment}?`,
                    icon: 'warning',
                    buttons: true,
                    dangerMode: true
                }).then((willRollback) => {
                    if (willRollback) {
                        window.location.href = '{{ route('operational.not-return-do.rollback-status', ':id') }}'.replace(':id', id);
                    }
                });
            });
        });
    </script>
@endpush
