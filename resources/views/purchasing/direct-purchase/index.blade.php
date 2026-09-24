@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Supplier',
    'secondSegment' => $title,
])

@push('style')
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
        /* ── Summary KPI Cards (PHL Standard §5) ── */
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

        /* ── Filter Card Standard PHL §3 ── */
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
            cursor: pointer;
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

        .filter-label { color: #64748b; display: block; font-size: 12px; font-weight: 600; margin-bottom: 6px; }
        .filter-control,
        .filter-card .form-control {
            background-color: #fff !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            color: #334155 !important;
            font-size: 13px !important;
            height: 38px !important;
            padding: 0 12px 0 36px !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='4' width='18' height='18' rx='2' ry='2'%3E%3C/rect%3E%3Cline x1='16' y1='2' x2='16' y2='6'%3E%3C/line%3E%3Cline x1='8' y1='2' x2='8' y2='6'%3E%3C/line%3E%3Cline x1='3' y1='10' x2='21' y2='10'%3E%3C/line%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: 12px center;
            background-size: 15px 15px;
            transition: all 0.2s ease !important;
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
            box-shadow: 0 2px 6px rgba(79, 70, 229, 0.25) !important;
            transition: all 0.2s ease !important;
        }
        .btn-filter-primary:hover {
            background: linear-gradient(135deg, #4338ca, #4f46e5) !important;
            color: #fff !important;
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.35) !important;
            transform: translateY(-1px) !important;
        }
        .btn-filter-primary:active {
            transform: translateY(0) !important;
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
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
            transition: all 0.2s ease !important;
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

        /* ── Select2 Theme Matching Seragam 38px ── */
        .select2-container { width: 100% !important; }
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

        /* ── Scoped Table Styling (PHL Standard §6) ── */
        #direct-purchase-table {
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            width: 100%;
        }
        #direct-purchase-table thead th {
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
        #direct-purchase-table tbody td {
            padding: 11px 12px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            font-size: 12.5px;
            white-space: nowrap;
            vertical-align: middle;
        }
        #direct-purchase-table tbody tr {
            transition: background-color 0.15s ease;
        }
        #direct-purchase-table tbody tr:hover {
            background-color: #f8fafc !important;
        }

        /* ── Tombol Icon Ramping (§6) ── */
        .btn-icon {
            border-radius: 8px !important;
            padding: 6px 10px;
            font-size: 13px;
            transition: all 0.2s ease;
        }
        .btn-icon:hover {
            transform: translateY(-1px);
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
        .modal-table tfoot td {
            padding: 10px 12px;
            background: #f8fafc;
        }

        /* ── Dark Mode Wajib (§3, §6) ── */
        html[data-bs-theme="dark"] .card-header.bg-white {
            background-color: var(--bs-card-bg) !important;
            border-color: var(--bs-border-color) !important;
        }
        html[data-bs-theme="dark"] .summary-primary {
            background: rgba(99, 102, 241, 0.15);
            border-color: rgba(99, 102, 241, 0.3);
            color: #a5b4fc;
        }
        html[data-bs-theme="dark"] .summary-success {
            background: rgba(16, 185, 129, 0.15);
            border-color: rgba(16, 185, 129, 0.3);
            color: #6ee7b7;
        }
        html[data-bs-theme="dark"] .summary-warning {
            background: rgba(245, 158, 11, 0.15);
            border-color: rgba(245, 158, 11, 0.3);
            color: #fcd34d;
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
        html[data-bs-theme="dark"] #direct-purchase-table {
            border-color: var(--bs-border-color);
        }
        html[data-bs-theme="dark"] #direct-purchase-table thead th {
            background-color: var(--bs-tertiary-bg);
            border-bottom-color: var(--bs-border-color);
            color: var(--bs-body-color);
        }
        html[data-bs-theme="dark"] #direct-purchase-table tbody td {
            border-bottom-color: var(--bs-border-color);
            color: var(--bs-body-color);
        }
        html[data-bs-theme="dark"] #direct-purchase-table tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.04) !important;
        }

        /* ── Modal Dark Mode Wajib (§11) ── */
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
        html[data-bs-theme="dark"] .modal-table tfoot td {
            background-color: var(--bs-tertiary-bg) !important;
            color: var(--bs-body-color) !important;
            border-top-color: var(--bs-border-color) !important;
        }
        html[data-bs-theme="dark"] .modal-info-box {
            background-color: var(--bs-secondary-bg) !important;
            border-color: var(--bs-border-color) !important;
        }
    </style>
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
            {{-- Card Header Standar PHL (§2) --}}
            <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3"
                style="border-color: #e2e8f0;">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center shadow-sm text-white"
                        style="width: 44px; height: 44px; background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;">
                        <i class="mdi mdi-cart-arrow-right fs-22"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                            {{ $title }}
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill fs-12 px-2 py-1">
                                {{ number_format($stats['totalCount'] ?? 0) }} Transaksi
                            </span>
                        </h4>
                        <small class="text-muted">Pengadaan suku cadang langsung digunakan/dipasang pada armada mobil dan tercatat sebagai pemeliharaan (Lunas seketika).</small>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('warehouse.maintenance.index') }}" class="btn btn-outline-info btn-sm rounded-pill px-3 shadow-sm"
                        data-bs-toggle="tooltip" title="Lihat Data Pemeliharaan / Perawatan">
                        <i class="mdi mdi-tools me-1"></i> Data Maintenance
                    </a>
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm"
                        id="btn-refresh-table" title="Muat Ulang Data Tabel">
                        <i class="mdi mdi-refresh me-1" id="refresh-icon"></i> Refresh
                    </button>
                    <a href="{{ route($view . 'create') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm"
                        style="font-weight: 600;">
                        <i class="mdi mdi-plus me-1"></i> {{ __('general.add_data') }}
                    </a>
                </div>
            </div>

            <div class="card-body p-4">
                @include('partials.alert')

                {{-- KPI Summary Cards (PHL Standard §5) --}}
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-4">
                        <div class="summary-card summary-primary">
                            <i class="mdi mdi-cart-check float-end fs-24 opacity-50"></i>
                            <h5>Total Pembelian Langsung</h5>
                            <h3>{{ number_format($stats['totalCount'] ?? 0, 0, ',', '.') }} <span class="fs-14 fw-normal opacity-75">Transaksi</span></h3>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="summary-card summary-success">
                            <i class="mdi mdi-cash-multiple float-end fs-24 opacity-50"></i>
                            <h5>Total Biaya Suku Cadang</h5>
                            <h3>Rp {{ number_format($stats['totalNominal'] ?? 0, 0, ',', '.') }}</h3>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="summary-card summary-warning">
                            <i class="mdi mdi-truck-check-outline float-end fs-24 opacity-50"></i>
                            <h5>Total Armada Ditangani</h5>
                            <h3>{{ number_format($stats['fleetCount'] ?? 0, 0, ',', '.') }} <span class="fs-14 fw-normal opacity-75">Armada</span></h3>
                        </div>
                    </div>
                </div>

                {{-- Filter Card Collapse Terstandarisasi (§3) --}}
                <div class="filter-card">
                    <button type="button" class="filter-card-header" data-bs-toggle="collapse"
                        data-bs-target="#directPurchaseFilterCollapse" aria-expanded="false" aria-controls="directPurchaseFilterCollapse">
                        <span class="filter-card-heading">
                            <i class="mdi mdi-filter-variant"></i>
                            <strong>Filter Data</strong>
                            <small>Gunakan filter armada, supplier, atau rentang tanggal untuk mempersempit daftar</small>
                        </span>
                        <i class="mdi mdi-chevron-down filter-card-chevron"></i>
                    </button>

                    <div class="collapse filter-collapse" id="directPurchaseFilterCollapse">
                        <div class="filter-collapse-body">
                            <div id="filterForm">
                                <div class="row g-3">
                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="filterFleetCode">Armada / Plat Nomor</label>
                                        <select class="form-select select2-filter" name="fleetCode" id="filterFleetCode">
                                            <option value="">Semua Armada</option>
                                            @foreach ($fleet as $f)
                                                <option value="{{ $f->code }}">{{ $f->plateNumber }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="filterSupplierCode">Supplier / Toko</label>
                                        <select class="form-select select2-filter" name="supplierCode" id="filterSupplierCode">
                                            <option value="">Semua Supplier</option>
                                            @foreach ($supplier as $s)
                                                <option value="{{ $s->code }}">{{ $s->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="startDate">Dari Tanggal</label>
                                        <input class="form-control filter-control" name="startDate" id="startDate"
                                            type="text" placeholder="Pilih tanggal mulai">
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="endDate">Sampai Tanggal</label>
                                        <input class="form-control filter-control" name="endDate" id="endDate"
                                            type="text" placeholder="Pilih tanggal akhir">
                                    </div>

                                    <div class="col-12 d-flex align-items-center justify-content-end gap-2 mt-2">
                                        <button class="btn btn-filter-primary" type="button" id="btnFilter">
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

                {{-- Table (Posisi Aksi di Kiri sesuai Standar PHL §8) --}}
                <div class="table-responsive custom-scrollbar">
                    <table class="table align-middle w-100" id="direct-purchase-table">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 100px;">Aksi</th>
                                <th class="text-center" style="width: 45px;">No</th>
                                <th>No. Transaksi</th>
                                <th class="text-center" style="width: 120px;">Tgl & Jam</th>
                                <th class="text-center" style="width: 110px;">Armada</th>
                                <th>Supplier / Toko</th>
                                <th class="text-center" style="width: 140px;">Pemeliharaan (MNT)</th>
                                <th class="text-center" style="width: 130px;">Metode Bayar</th>
                                <th class="text-center" style="width: 100px;">Item / Qty</th>
                                <th class="text-end" style="width: 130px;">Total Biaya</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Modal Detail Pembelian Langsung (§11 B2B Modal Standard - modal-xl) --}}
        <div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
                    {{-- Header Modal (§11) --}}
                    <div class="modal-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar-sm d-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle"
                                style="width: 36px; height: 36px;">
                                <i class="mdi mdi-receipt-text-outline fs-18"></i>
                            </span>
                            <div>
                                <h5 class="modal-title mb-0 fw-bold" id="detailModalLabel">Rincian Pembelian Langsung</h5>
                                <small class="text-muted" id="modalSubtitle">Detail suku cadang & pemeliharaan armada</small>
                            </div>
                        </div>
                        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    {{-- Body Modal --}}
                    <div class="modal-body p-4">
                        {{-- Loading Spinner --}}
                        <div id="modalLoading" class="text-center py-4">
                            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                            <span class="text-muted small ms-2">Memuat rincian data...</span>
                        </div>

                        {{-- Modal Content (Hidden saat loading) --}}
                        <div id="modalContentWrapper" style="display: none;">
                            {{-- Info Grid --}}
                            <div class="modal-info-box p-3 rounded-3 mb-3 border" style="background: #f8fafc; border-color: #e2e8f0;">
                                <div class="row g-3">
                                    <div class="col-lg-2 col-md-4 col-sm-6">
                                        <small class="text-muted text-uppercase fw-semibold" style="font-size: 11px;">No. Transaksi</small>
                                        <div class="font-monospace fw-bold text-dark fs-13" id="modalCode">-</div>
                                    </div>
                                    <div class="col-lg-2 col-md-4 col-sm-6">
                                        <small class="text-muted text-uppercase fw-semibold" style="font-size: 11px;">Tanggal & Jam</small>
                                        <div class="fw-semibold text-dark fs-13" id="modalDate">-</div>
                                    </div>
                                    <div class="col-lg-2 col-md-4 col-sm-6">
                                        <small class="text-muted text-uppercase fw-semibold" style="font-size: 11px;">Armada / Plat Nomor</small>
                                        <div id="modalFleet">-</div>
                                    </div>
                                    <div class="col-lg-2 col-md-4 col-sm-6">
                                        <small class="text-muted text-uppercase fw-semibold" style="font-size: 11px;">Supplier / Toko</small>
                                        <div class="fw-semibold text-dark fs-13" id="modalSupplier">-</div>
                                    </div>
                                    <div class="col-lg-2 col-md-4 col-sm-6">
                                        <small class="text-muted text-uppercase fw-semibold" style="font-size: 11px;">Servis Maintenance</small>
                                        <div id="modalMnt">-</div>
                                    </div>
                                    <div class="col-lg-2 col-md-4 col-sm-6">
                                        <small class="text-muted text-uppercase fw-semibold" style="font-size: 11px;">Metode Pembayaran</small>
                                        <div class="fw-semibold text-dark fs-13" id="modalPayment">-</div>
                                    </div>
                                </div>
                            </div>

                            {{-- Divider & Table Header (§11) --}}
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="fw-bold mb-0 text-dark">Rincian Suku Cadang Terpasang:</h6>
                                <span class="badge bg-primary-subtle text-primary px-3 py-1" id="modalTotalBadge"
                                    style="border-radius: 6px; font-weight: 700; font-size: 13px;">Total: Rp 0</span>
                            </div>

                            {{-- Table of Items (§11) --}}
                            <div class="table-responsive" style="border-radius: 8px; border: 1px solid #e2e8f0;">
                                <table class="table align-middle mb-0 modal-table">
                                    <thead class="bg-light">
                                        <tr>
                                            <th style="padding: 10px 12px; font-size: 12px; font-weight: 700; text-transform: uppercase; width: 45px;" class="text-center">No</th>
                                            <th style="padding: 10px 12px; font-size: 12px; font-weight: 700; text-transform: uppercase; width: 140px;">Kode Part</th>
                                            <th style="padding: 10px 12px; font-size: 12px; font-weight: 700; text-transform: uppercase;">Nama Suku Cadang</th>
                                            <th style="padding: 10px 12px; font-size: 12px; font-weight: 700; text-transform: uppercase; width: 90px; text-align: center;">Qty</th>
                                            <th style="padding: 10px 12px; font-size: 12px; font-weight: 700; text-transform: uppercase; width: 150px; text-align: right;">Harga Satuan</th>
                                            <th style="padding: 10px 12px; font-size: 12px; font-weight: 700; text-transform: uppercase; width: 160px; text-align: right;">Subtotal</th>
                                            <th style="padding: 10px 12px; font-size: 12px; font-weight: 700; text-transform: uppercase; width: 220px;">Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modalItemsBody"></tbody>
                                    <tfoot>
                                        <tr style="background: #f8fafc; font-weight: 700;">
                                            <td colspan="3" class="text-end fw-bold text-uppercase" style="padding: 10px 12px; font-size: 12px;">Total Kuantitas:</td>
                                            <td class="text-center font-monospace fw-bold" style="padding: 10px 12px;" id="modalTotalQty">0</td>
                                            <td class="text-end fw-bold text-uppercase" style="padding: 10px 12px; font-size: 12px;">Grand Total:</td>
                                            <td class="text-end font-monospace fw-bold text-success fs-14" style="padding: 10px 12px;" id="modalGrandTotal">Rp 0</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Footer Modal (§11) --}}
                    <div class="modal-footer bg-light py-2 px-4 border-top d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"
                            style="border-radius: 8px; padding: 6px 16px;">
                            Tutup
                        </button>
                        <a href="#" id="modalBtnEdit" class="btn btn-primary btn-sm d-flex align-items-center gap-1"
                            style="border-radius: 8px; padding: 6px 16px; font-weight: 600;">
                            <i class="mdi mdi-pencil"></i> Edit Data
                        </a>
                    </div>
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
    <script src="{{ asset('assets/js/sweet-alert/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>

    {{-- Flash message server → SweetAlert2 --}}
    @include('purchasing.purchase.partials.flash-swal')

    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>

    <script>
        let startPicker, endPicker;

        $(document).ready(function() {
            // Select2 Filter
            $('.select2-filter').select2({
                placeholder: 'Semua pilihan',
                allowClear: true,
                width: '100%'
            });

            // Flatpickr
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

            // Initialize DataTable (Aksi di Kiri sesuai Standar PHL §8)
            const table = $('#direct-purchase-table').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                ajax: {
                    url: "{{ route('dt.direct-purchase') }}",
                    data: function(d) {
                        d.fleetCode = $('#filterFleetCode').val();
                        d.supplierCode = $('#filterSupplierCode').val();
                        d.startDate = $('#startDate').val();
                        d.endDate = $('#endDate').val();
                    }
                },
                columns: [
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center align-middle' },
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center align-middle' },
                    { data: 'code', name: 'code', className: 'align-middle font-monospace fw-semibold text-dark' },
                    { data: 'purchaseDate', name: 'date', className: 'align-middle text-center' },
                    { data: 'fleetInfo', name: 'fleet.plateNumber', className: 'align-middle text-center' },
                    { data: 'supplierInfo', name: 'supplier.name', className: 'align-middle' },
                    { data: 'maintenanceBadge', name: 'maintenances.code', className: 'align-middle text-center' },
                    { data: 'bankInfo', name: 'userBank.accountName', className: 'align-middle text-center' },
                    { data: 'itemSummary', name: 'itemSummary', orderable: false, searchable: false, className: 'text-center align-middle' },
                    { data: 'totalPrice', name: 'nominal', className: 'text-end align-middle font-monospace fw-bold text-dark' },
                ],
                columnDefs: [
                    { searchable: false, targets: [0, 1, 8] },
                    { orderable: false, targets: [0, 1, 8] }
                ],
                order: [[3, 'desc']], // Urutkan default berdasarkan tanggal pembelian (kolom index 3)
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
                    infoEmpty: "Menampilkan 0 data",
                    zeroRecords: "Tidak ada data yang cocok",
                    processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Memuat data...'
                }
            });

            // Filter actions
            $('#btnFilter').on('click', function() {
                table.ajax.reload();
            });

            $('#btnResetFilter').on('click', function() {
                $('.select2-filter').val('').trigger('change');
                if (startPicker) startPicker.clear();
                if (endPicker) endPicker.clear();
                table.ajax.reload();
            });

            $('#btn-refresh-table').on('click', function() {
                const icon = $('#refresh-icon');
                icon.addClass('mdi-spin');
                table.ajax.reload(function() {
                    icon.removeClass('mdi-spin');
                }, false);
            });
        });

        // Delete Function with SweetAlert2
        function deleteData(id) {
            Swal.fire({
                title: 'Hapus Pembelian Langsung?',
                html: 'Tindakan ini akan <b>membatalkan pemakaian suku cadang</b>, menghapus servis maintenance terkait, serta merollback mutasi stok dan kas.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus Data',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = "{{ url('purchasing/direct-purchase') }}/" + id;
                    form.innerHTML = `
                        @csrf
                        @method('DELETE')
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        // Show Detail Modal Function (§11 B2B Modal Standard)
        function showDetailModal(id) {
            const modalEl = document.getElementById('detailModal');
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

            $('#modalLoading').show();
            $('#modalContentWrapper').hide();
            modal.show();

            $.ajax({
                url: "{{ url('purchasing/direct-purchase') }}/" + id,
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res && res.success && res.data) {
                        const d = res.data;

                        $('#modalCode').text(d.code);
                        $('#modalDate').html(d.date + (d.time ? ' <small class="text-muted font-monospace">(' + d.time + ')</small>' : ''));

                        let fleetHtml = '<span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace fs-12 px-2 py-1">'
                            + escapeHtml(d.fleetPlate) + '</span>';
                        if (d.fleetBrand) {
                            fleetHtml += ' <small class="text-muted d-block">' + escapeHtml(d.fleetBrand) + '</small>';
                        }
                        $('#modalFleet').html(fleetHtml);

                        $('#modalSupplier').text(d.supplierName);

                        let mntHtml = '<span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill font-monospace fs-11 px-2 py-1">'
                            + '<i class="mdi mdi-tools me-1"></i>' + escapeHtml(d.maintenanceCode) + '</span>';
                        $('#modalMnt').html(mntHtml);

                        $('#modalPayment').html('<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill fs-11">'
                            + '<i class="mdi mdi-cash-check me-1"></i>' + escapeHtml(d.paymentMethod) + '</span>');

                        $('#modalBtnEdit').attr('href', d.editUrl);
                        $('#modalTotalBadge').text('Total: ' + d.nominalFormatted);

                        // Populate items
                        let rowsHtml = '';
                        let totalQty = 0;
                        if (d.details && d.details.length > 0) {
                            d.details.forEach(function(item, idx) {
                                totalQty += parseFloat(item.qty) || 0;
                                rowsHtml += `
                                    <tr>
                                        <td class="text-center text-muted fw-semibold">${idx + 1}</td>
                                        <td><span class="font-monospace text-primary fw-semibold">${escapeHtml(item.itemCode)}</span></td>
                                        <td><strong>${escapeHtml(item.itemName)}</strong></td>
                                        <td class="text-center font-monospace">${item.qty}</td>
                                        <td class="text-end font-monospace">${item.priceFormatted}</td>
                                        <td class="text-end font-monospace fw-bold text-dark">${item.subtotalFormatted}</td>
                                        <td>${escapeHtml(item.description)}</td>
                                    </tr>
                                `;
                            });
                        } else {
                            rowsHtml = '<tr><td colspan="7" class="text-center text-muted py-3">Tidak ada rincian suku cadang.</td></tr>';
                        }

                        $('#modalItemsBody').html(rowsHtml);
                        $('#modalTotalQty').text(totalQty.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 1 }));
                        $('#modalGrandTotal').text(d.nominalFormatted);

                        $('#modalLoading').hide();
                        $('#modalContentWrapper').fadeIn(150);
                    } else {
                        $('#modalLoading').html('<p class="text-danger py-3">Gagal memuat rincian data.</p>');
                    }
                },
                error: function() {
                    $('#modalLoading').html('<p class="text-danger py-3">Terjadi kesalahan saat memuat rincian.</p>');
                }
            });
        }

        function escapeHtml(text) {
            if (!text) return '';
            return $('<div>').text(text).html();
        }
    </script>
@endpush
