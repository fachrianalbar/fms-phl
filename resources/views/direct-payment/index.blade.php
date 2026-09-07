@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Finance',
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
        /* Executive Metric KPI Cards */
        .stat-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 16px 18px;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
            position: relative;
            overflow: hidden;
            height: 100%;
            cursor: pointer;
            user-select: none;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.06);
            border-color: #cbd5e1;
        }
        .stat-card .stat-icon-wrapper {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }
        .stat-card .stat-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #64748b;
            margin-bottom: 4px;
        }
        .stat-card .stat-value {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.02em;
            line-height: 1.2;
            color: #0f172a;
        }
        .stat-card .stat-desc {
            font-size: 11.5px;
            color: #64748b;
            margin-top: 6px;
        }

        /* Main Table Container Card */
        .table-container-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            overflow: hidden;
        }
        .table-top-bar {
            padding: 14px 20px;
            background: #ffffff;
            border-bottom: 1px solid #f1f5f9;
        }

        /* Filter Navigation Pills */
        .filter-pill-btn {
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #64748b;
            font-size: 12.5px;
            font-weight: 600;
            padding: 6px 14px;
            border-radius: 30px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }
        .filter-pill-btn:hover {
            background: #e2e8f0;
            color: #1e293b;
            border-color: #cbd5e1;
        }
        .filter-pill-btn.active {
            background: #2563eb;
            color: #ffffff;
            border-color: #2563eb;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
        }
        .filter-pill-btn.active .badge-pill-count {
            background: rgba(255, 255, 255, 0.25);
            color: #ffffff;
        }
        .badge-pill-count {
            background: #e2e8f0;
            color: #475569;
            font-size: 11px;
            padding: 2px 7px;
            border-radius: 20px;
            font-weight: 700;
        }

        /* Direct Payment Table Styling */
        .direct-payment-table {
            width: 100% !important;
            margin-bottom: 0 !important;
            border-collapse: collapse !important;
        }
        .direct-payment-table thead th {
            background: #f8fafc !important;
            color: #475569 !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.04em !important;
            border-top: none !important;
            border-bottom: 2px solid #e2e8f0 !important;
            padding: 12px 14px !important;
            white-space: nowrap !important;
            vertical-align: middle !important;
        }
        .direct-payment-table tbody td {
            padding: 10px 14px !important;
            vertical-align: middle !important;
            border-bottom: 1px solid #f1f5f9 !important;
            font-size: 12px;
            color: #334155;
            white-space: nowrap !important;
        }
        .direct-payment-table tbody tr {
            transition: background-color 0.15s ease-in-out;
        }
        .direct-payment-table tbody tr:hover {
            background-color: #f8fafc !important;
        }

        /* Action buttons */
        .btn-icon {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            border: 1px solid transparent;
            padding: 0;
            transition: all 0.2s ease;
        }
        .hover-scale:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        }

        /* Custom DataTables Styling */
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 12px;
        }
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 20px !important;
            padding: 6px 16px !important;
            border: 1px solid #cbd5e1 !important;
            outline: none !important;
            font-size: 13px !important;
            background-color: #f8fafc !important;
            transition: all 0.2s ease;
            min-width: 240px;
        }
        .dataTables_wrapper .dataTables_filter input:focus {
            background-color: #ffffff !important;
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15) !important;
        }
        .dataTables_wrapper .dataTables_length select {
            border-radius: 8px !important;
            padding: 5px 28px 5px 10px !important;
            border: 1px solid #cbd5e1 !important;
            font-size: 13px !important;
        }
        .page-item.active .page-link {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important;
            border-color: transparent !important;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3) !important;
            border-radius: 8px !important;
        }
        .page-link {
            border-radius: 8px !important;
            margin: 0 2px !important;
            border: 1px solid #e2e8f0 !important;
            color: #475569 !important;
            font-size: 12.5px;
        }

        /* Modern Payment Modal Styling */
        #payment-modal .modal-content {
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25);
        }
        #payment-modal .modal-header {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%) !important;
            padding: 18px 24px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .modal-receipt-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 20px;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .modal-receipt-card .receipt-meta-chip {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 11.5px;
            color: #475569;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .modal-adjust-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 14px;
            transition: all 0.2s ease;
        }
        .modal-adjust-card:hover {
            border-color: #cbd5e1;
        }
        .quick-chip-btn {
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #334155;
            font-size: 11.5px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 20px;
            transition: all 0.15s ease;
            cursor: pointer;
            user-select: none;
        }
        .quick-chip-btn:hover {
            background: #eff6ff;
            color: #2563eb;
            border-color: #93c5fd;
        }
        .quick-chip-btn.active {
            background: #2563eb;
            color: #ffffff;
            border-color: #2563eb;
        }
        .payment-amount-input {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
            font-size: 1.35rem !important;
            font-weight: 700 !important;
            color: #0f172a !important;
            letter-spacing: -0.01em;
            padding: 10px 14px !important;
        }
        .payment-amount-input:focus {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15) !important;
        }
        .letter-spacing-1 {
            letter-spacing: 0.05em;
        }
        .transition-all {
            transition: all 0.2s ease-in-out;
        }
        #payment-modal .select2-container--default .select2-selection--single {
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            height: 38px !important;
            padding: 4px 8px !important;
        }
        #payment-modal .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 28px !important;
            font-size: 13px !important;
            color: #1e293b !important;
        }
        #payment-modal .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
        }
    </style>
@endpush

@section('content')
    <div class="col-sm-12">
        <!-- Page Header & Action Bar -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center shadow-sm text-white"
                     style="width: 48px; height: 48px; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important;">
                    <i class="mdi mdi-cash-register fs-24"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        {{ $title }}
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill fs-12 px-2 py-1">
                            {{ number_format($stats['totalCount'] ?? 0) }} Order
                        </span>
                    </h4>
                    <p class="text-muted mb-0 fs-12">
                        Pencatatan pembayaran langsung order non-DO (DP, Cicilan & Pelunasan) dengan PPN/PPh dan riwayat transaksi.
                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-danger btn-sm rounded-pill px-3 shadow-sm d-none" id="btn-print-multi" title="Cetak Nota PDF Order Terpilih">
                    <i class="mdi mdi-file-pdf me-1"></i> Cetak PDF (<span id="btn-count">0</span>)
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm" id="btn-refresh-table" title="Muat Ulang Data Tabel">
                    <i class="mdi mdi-refresh me-1" id="refresh-icon"></i> Refresh
                </button>
            </div>
        </div>

        <!-- 4 KPI Executive Metric Cards -->
        <div class="row g-3 mb-4">
            <!-- Card 1: Total Order -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card" data-filter="all" title="Klik untuk tampilkan semua order">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Total Order Direct</div>
                            <div class="stat-value text-primary">
                                {{ number_format($stats['totalCount'] ?? 0) }}
                                <span class="fs-13 text-muted fw-normal">Order</span>
                            </div>
                        </div>
                        <div class="stat-icon-wrapper bg-primary-subtle text-primary">
                            <i class="mdi mdi-truck-fast-outline"></i>
                        </div>
                    </div>
                    <div class="stat-desc text-truncate">
                        <i class="mdi mdi-calculator me-1 text-primary"></i>Total Tagihan: <strong>Rp {{ number_format($stats['totalBilling'] ?? 0, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>

            <!-- Card 2: Belum Bayar -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card" data-filter="unpaid" title="Klik untuk filter order Belum Bayar">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Belum Bayar</div>
                            <div class="stat-value text-danger">
                                {{ number_format($stats['unpaidCount'] ?? 0) }}
                                <span class="fs-13 text-muted fw-normal">Order</span>
                            </div>
                        </div>
                        <div class="stat-icon-wrapper bg-danger-subtle text-danger">
                            <i class="mdi mdi-alert-circle-outline"></i>
                        </div>
                    </div>
                    <div class="stat-desc text-danger text-truncate">
                        <i class="mdi mdi-close-circle-outline me-1"></i>Belum ada pembayaran masuk
                    </div>
                </div>
            </div>

            <!-- Card 3: Belum Lunas (DP / Cicilan) -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card" data-filter="partial" title="Klik untuk filter order Belum Lunas (Partial)">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Belum Lunas (Partial)</div>
                            <div class="stat-value text-warning">
                                {{ number_format($stats['partialCount'] ?? 0) }}
                                <span class="fs-13 text-muted fw-normal">Order</span>
                            </div>
                        </div>
                        <div class="stat-icon-wrapper bg-warning-subtle text-warning">
                            <i class="mdi mdi-progress-clock"></i>
                        </div>
                    </div>
                    <div class="stat-desc text-warning-emphasis text-truncate">
                        <i class="mdi mdi-clock-outline me-1"></i>Sisa Tagihan: <strong>Rp {{ number_format($stats['totalRemaining'] ?? 0, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>

            <!-- Card 4: Lunas -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card" data-filter="paid" title="Klik untuk filter order Lunas">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Lunas (100%)</div>
                            <div class="stat-value text-success">
                                {{ number_format($stats['paidCount'] ?? 0) }}
                                <span class="fs-13 text-muted fw-normal">Order</span>
                            </div>
                        </div>
                        <div class="stat-icon-wrapper bg-success-subtle text-success">
                            <i class="mdi mdi-check-decagram-outline"></i>
                        </div>
                    </div>
                    <div class="stat-desc text-success text-truncate">
                        <i class="mdi mdi-cash-check me-1"></i>Terbayar: <strong>Rp {{ number_format($stats['totalPaid'] ?? 0, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Table Container Card -->
        <div class="table-container-card mb-4">
            <!-- Filter & Selection Bar -->
            <div class="table-top-bar d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <button type="button" class="filter-pill-btn active" data-status="all">
                        <i class="mdi mdi-format-list-bulleted"></i>
                        <span>Semua</span>
                        <span class="badge-pill-count">{{ number_format($stats['totalCount'] ?? 0) }}</span>
                    </button>
                    <button type="button" class="filter-pill-btn" data-status="unpaid">
                        <i class="mdi mdi-close-circle-outline text-danger"></i>
                        <span>Belum Bayar</span>
                        <span class="badge-pill-count">{{ number_format($stats['unpaidCount'] ?? 0) }}</span>
                    </button>
                    <button type="button" class="filter-pill-btn" data-status="partial">
                        <i class="mdi mdi-clock-outline text-warning"></i>
                        <span>Belum Lunas</span>
                        <span class="badge-pill-count">{{ number_format($stats['partialCount'] ?? 0) }}</span>
                    </button>
                    <button type="button" class="filter-pill-btn" data-status="paid">
                        <i class="mdi mdi-check-circle-outline text-success"></i>
                        <span>Lunas</span>
                        <span class="badge-pill-count">{{ number_format($stats['paidCount'] ?? 0) }}</span>
                    </button>
                </div>

                <div class="d-flex align-items-center gap-2" id="selection-bar" style="display: none !important;">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fs-12 fw-semibold">
                        <span id="selected-count">0</span> Order Terpilih
                    </span>
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-2 py-0 fs-11" id="btn-clear-selection">
                        <i class="mdi mdi-close"></i> Hapus Pilihan
                    </button>
                </div>
            </div>

            <div class="card-body p-3">
                @include('partials.alert')
                <div class="table-responsive custom-scrollbar">
                    <table class="table table-hover w-100 nowrap direct-payment-table" id="dt">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 50px;">
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        <input type="checkbox" class="form-check-input m-0" id="check-all" title="Pilih Semua di Halaman">
                                        <span class="fs-11 text-muted ms-1">#</span>
                                    </div>
                                </th>
                                <th class="text-center" style="width: 80px;">Aksi</th>
                                <th>No Order</th>
                                <th>Tgl Order</th>
                                <th>Nopol</th>
                                <th>Driver</th>
                                <th>Surat Jalan</th>
                                <th>Customer</th>
                                <th>Rute</th>
                                <th class="text-end">Harga Rute (Rp)</th>
                                <th class="text-end">Biaya Tambahan</th>
                                <th class="text-end">PPN</th>
                                <th class="text-end">PPh</th>
                                <th class="text-end">Claim (Rp)</th>
                                <th class="text-end">Total Tagihan (Rp)</th>
                                <th class="text-end">Sudah Bayar (Rp)</th>
                                <th class="text-end">Sisa Tagihan</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Hidden Form for Multi-PDF Print -->
        <form id="pdf-multi-form" action="{{ route('direct-payment.pdf-multi') }}" method="POST" target="_blank" class="d-none">
            @csrf
            <div id="pdf-multi-inputs"></div>
        </form>

        <!-- Payment Modal Form -->
        <form id="payment-form" method="post" action="{{ route($view . 'store') }}">
            @csrf
            <div class="modal fade" id="payment-modal" tabindex="-1" role="dialog"
                aria-labelledby="paymentModalLabel" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content border-0 shadow-lg">
                        <input type="hidden" name="orderCode" id="orderCode">

                        <!-- Modal Header -->
                        <div class="modal-header text-white d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-3 d-flex align-items-center justify-content-center text-white shadow-sm"
                                     style="width: 44px; height: 44px; background: rgba(59, 130, 246, 0.2); border: 1px solid rgba(59, 130, 246, 0.4);">
                                    <i class="mdi mdi-credit-card-check-outline fs-22 text-info"></i>
                                </div>
                                <div>
                                    <div class="d-flex align-items-center gap-2">
                                        <h5 class="modal-title fw-bold text-white mb-0" id="paymentModalLabel">
                                            Input Pembayaran Order
                                        </h5>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2 py-1 fs-12" id="modal-order-badge">
                                            -
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center gap-3 text-white-50 fs-12 mt-1">
                                        <span><i class="mdi mdi-account-outline me-1"></i><strong class="text-white" id="modal-customer-name">-</strong></span>
                                        <span>•</span>
                                        <span><i class="mdi mdi-calendar-blank-outline me-1"></i><span id="modal-order-date">-</span></span>
                                    </div>
                                </div>
                            </div>
                            <button class="btn-close btn-close-white" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <!-- Modal Body -->
                        <div class="modal-body p-4 bg-body">
                            <div class="row g-4">
                                <!-- Left Column: Receipt Card & Financial Summary -->
                                <div class="col-lg-5">
                                    <div class="modal-receipt-card">
                                        <div>
                                            <!-- Meta Chips: Nopol, Driver, Rute -->
                                            <div class="d-flex flex-wrap gap-2 mb-3">
                                                <div class="receipt-meta-chip">
                                                    <i class="mdi mdi-truck-outline text-primary"></i>
                                                    <span id="modal-plate-number" class="fw-semibold">-</span>
                                                </div>
                                                <div class="receipt-meta-chip">
                                                    <i class="mdi mdi-steering text-success"></i>
                                                    <span id="modal-driver-name" class="fw-semibold">-</span>
                                                </div>
                                                <div class="receipt-meta-chip w-100 text-truncate" title="Rute Pengiriman">
                                                    <i class="mdi mdi-map-marker-distance text-danger"></i>
                                                    <span id="modal-route" class="text-truncate fw-semibold">-</span>
                                                </div>
                                            </div>

                                            <!-- Progress Bar Pelunasan -->
                                            <div class="bg-white p-3 rounded-3 border mb-3">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span class="fs-11 fw-semibold text-muted text-uppercase letter-spacing-1">Progress Pembayaran</span>
                                                    <span class="badge bg-light text-dark font-monospace fw-bold" id="modal-progress-pct">0%</span>
                                                </div>
                                                <div class="progress" style="height: 8px; border-radius: 6px;">
                                                    <div class="progress-bar bg-success progress-bar-striped progress-bar-animated"
                                                         id="modal-progress-bar" role="progressbar" style="width: 0%"></div>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center mt-2 fs-11 text-muted">
                                                    <span>Terbayar: <strong class="text-success font-monospace" id="progress_paid_label">Rp 0</strong></span>
                                                    <span>Sisa: <strong class="text-danger font-monospace" id="progress_remaining_label">Rp 0</strong></span>
                                                </div>
                                            </div>

                                            <!-- Financial Line Items -->
                                            <div class="list-group list-group-flush bg-transparent">
                                                <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-1 bg-transparent fs-12">
                                                    <span class="text-muted">Harga Rute</span>
                                                    <span class="fw-semibold text-dark font-monospace" id="cost_label">Rp 0</span>
                                                </div>
                                                <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-1 bg-transparent fs-12">
                                                    <span class="text-muted">Biaya Tambahan</span>
                                                    <span class="fw-semibold text-dark font-monospace" id="additional_cost_label">Rp 0</span>
                                                </div>
                                                <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-1 bg-transparent fs-12">
                                                    <span class="fw-bold text-dark">Subtotal</span>
                                                    <span class="fw-bold text-dark font-monospace" id="subtotal_label">Rp 0</span>
                                                </div>

                                                <hr class="my-2 opacity-25">

                                                <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-1 bg-transparent fs-12">
                                                    <span class="text-muted">PPN (+)</span>
                                                    <span class="fw-semibold text-success font-monospace" id="ppn_label">+ Rp 0</span>
                                                </div>
                                                <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-1 bg-transparent fs-12">
                                                    <span class="text-muted">PPh (-)</span>
                                                    <span class="fw-semibold text-danger font-monospace" id="pph_label">- Rp 0</span>
                                                </div>
                                                <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-1 bg-transparent fs-12">
                                                    <span class="text-muted">Biaya Claim (-)</span>
                                                    <span class="fw-semibold text-warning-emphasis font-monospace" id="claim_label">- Rp 0</span>
                                                </div>

                                                <hr class="my-2 opacity-25">

                                                <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-1 bg-transparent">
                                                    <span class="fw-bold text-dark fs-13">Total Tagihan</span>
                                                    <span class="fw-bold text-dark fs-14 font-monospace" id="grand_total_label">Rp 0</span>
                                                </div>
                                                <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-1 bg-transparent fs-12">
                                                    <span class="text-muted">Sudah Dibayar</span>
                                                    <span class="fw-semibold text-success font-monospace" id="payment_label">Rp 0</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Sisa Tagihan Callout -->
                                        <div class="p-3 rounded-3 text-center mt-3 border transition-all" id="sisa_tagihan_container">
                                            <span class="small d-block fw-bold mb-1" id="sisa_tagihan_title">Sisa Tagihan</span>
                                            <span class="fs-4 fw-bold font-monospace" id="sisa_tagihan_value">Rp 0</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Column: Payment Inputs Form -->
                                <div class="col-lg-7">
                                    <!-- Hidden Inputs expected by backend -->
                                    <input type="hidden" name="cost" id="costHidden">
                                    <input type="hidden" name="additional_cost" id="additional_costHidden">
                                    <input type="hidden" name="ppn" id="ppnHidden">
                                    <input type="hidden" name="ppn_type" id="ppnTypeHidden" value="nominal">
                                    <input type="hidden" name="ppn_percent" id="ppnPercentHidden">
                                    <input type="hidden" name="pph" id="pphHidden">
                                    <input type="hidden" name="pph_type" id="pphTypeHidden" value="nominal">
                                    <input type="hidden" name="pph_percent" id="pphPercentHidden">
                                    <input type="hidden" name="claim" id="claimHidden" value="0">
                                    <input type="hidden" name="total" id="totalHidden">
                                    <input type="hidden" name="type" id="type" value="Full">
                                    <input type="hidden" name="paymentAmount" id="paymentAmountHidden">

                                    <div class="d-flex flex-column gap-3">
                                        <!-- Adjustment Section: PPN, PPh, Claim -->
                                        <div class="border rounded-3 p-3 bg-light">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <span class="fs-12 fw-bold text-dark text-uppercase letter-spacing-1">
                                                    <i class="mdi mdi-tune-vertical-variant text-primary me-1"></i>Penyesuaian Pajak & Potongan Claim
                                                </span>
                                                <span class="fs-11 text-muted">Opsional</span>
                                            </div>

                                            <div class="row g-2">
                                                <!-- PPN Card -->
                                                <div class="col-12">
                                                    <div class="modal-adjust-card">
                                                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                                            <div class="form-check form-switch mb-0">
                                                                <input class="form-check-input" type="checkbox" role="switch" id="usePpn">
                                                                <label class="form-check-label fw-semibold text-dark small" for="usePpn">
                                                                    Gunakan PPN (+)
                                                                </label>
                                                            </div>
                                                            <div class="btn-group btn-group-sm" id="ppn-mode-group" style="display: none;">
                                                                <button type="button" class="btn btn-outline-primary ppn-mode-btn active py-0 px-2 fs-11" data-mode="percent">Persen (%)</button>
                                                                <button type="button" class="btn btn-outline-primary ppn-mode-btn py-0 px-2 fs-11" data-mode="nominal">Nominal (Rp)</button>
                                                            </div>
                                                        </div>
                                                        <div class="input-group input-group-sm mt-2" id="ppn-input-group" style="display: none;">
                                                            <span class="input-group-text bg-light text-muted fw-bold" id="ppn-prefix">%</span>
                                                            <input class="form-control text-end font-monospace" id="ppnInput" type="text"
                                                                placeholder="Contoh: 11" inputmode="decimal" oninput="formatAngka(this)">
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- PPH Card -->
                                                <div class="col-12">
                                                    <div class="modal-adjust-card">
                                                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                                            <div class="form-check form-switch mb-0">
                                                                <input class="form-check-input" type="checkbox" role="switch" id="usePph">
                                                                <label class="form-check-label fw-semibold text-dark small" for="usePph">
                                                                    Gunakan PPh (-)
                                                                </label>
                                                            </div>
                                                            <div class="btn-group btn-group-sm" id="pph-mode-group" style="display: none;">
                                                                <button type="button" class="btn btn-outline-primary pph-mode-btn active py-0 px-2 fs-11" data-mode="percent">Persen (%)</button>
                                                                <button type="button" class="btn btn-outline-primary pph-mode-btn py-0 px-2 fs-11" data-mode="nominal">Nominal (Rp)</button>
                                                            </div>
                                                        </div>
                                                        <div class="input-group input-group-sm mt-2" id="pph-input-group" style="display: none;">
                                                            <span class="input-group-text bg-light text-muted fw-bold" id="pph-prefix">%</span>
                                                            <input class="form-control text-end font-monospace" id="pphInput" type="text"
                                                                placeholder="Contoh: 2" inputmode="decimal" oninput="formatAngka(this)">
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Claim Card -->
                                                <div class="col-12">
                                                    <div class="modal-adjust-card">
                                                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                                            <div class="form-check form-switch mb-0">
                                                                <input class="form-check-input" type="checkbox" role="switch" id="useClaim">
                                                                <label class="form-check-label fw-semibold text-dark small" for="useClaim">
                                                                    Biaya Claim Pengurang (-)
                                                                </label>
                                                            </div>
                                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill fs-11 px-2 py-0" id="claim-badge" style="display: none;">
                                                                Non-Cash
                                                            </span>
                                                        </div>
                                                        <div id="claim-input-group" style="display: none;" class="mt-2">
                                                            <div class="input-group input-group-sm mb-2">
                                                                <span class="input-group-text bg-light text-muted fw-bold">Rp</span>
                                                                <input class="form-control text-end font-monospace" id="claimInput" type="text"
                                                                    placeholder="Masukkan nominal claim" oninput="formatAngka(this)">
                                                            </div>
                                                            <input class="form-control form-control-sm" name="claim_description" id="claim_description" type="text"
                                                                placeholder="Keterangan / alasan klaim kendala/kerusakan (opsional)">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Bank & Date Inputs -->
                                        <div class="row g-3">
                                            <div class="col-md-7">
                                                <label class="form-label fw-semibold text-dark small mb-1" for="userBankCode">
                                                    Bank Tujuan Transfer <span class="text-danger">*</span>
                                                </label>
                                                <select class="js-example-basic" name="userBankCode" id="userBankCode" required>
                                                    <option value="">{{ __('general.choose') }}...</option>
                                                    @foreach ($userBank as $item)
                                                        <option value="{{ $item->code }}">
                                                            {{ $item->accountNumber . ' - ' . $item->bank->name . ' - ' . $item->accountName }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-5">
                                                <label class="form-label fw-semibold text-dark small mb-1" for="date">
                                                    Tanggal Bayar <span class="text-danger">*</span>
                                                </label>
                                                <input class="form-control form-control-sm py-2" name="date" id="date" type="date" required value="{{ date('Y-m-d') }}">
                                            </div>
                                        </div>

                                        <!-- Main Payment Input Section -->
                                        <div class="border rounded-3 p-3 bg-white shadow-sm border-primary-subtle">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <label class="form-label fw-bold text-dark mb-0 fs-13" for="nominalInput">
                                                    Nominal Pembayaran <span class="text-danger">*</span>
                                                </label>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill fs-11 px-2 py-1" id="payment-status-badge">
                                                    Pelunasan Penuh
                                                </span>
                                            </div>

                                            <div class="input-group mb-2">
                                                <span class="input-group-text bg-light text-muted fw-bold fs-5 px-3">Rp</span>
                                                <input class="form-control payment-amount-input text-end" id="nominalInput" type="text"
                                                    placeholder="0" oninput="formatAngka(this)" required>
                                            </div>

                                            <!-- Quick Fill Chips -->
                                            <div class="d-flex align-items-center gap-2 flex-wrap mt-2">
                                                <span class="text-muted fs-11 fw-semibold me-1">Pilihan Cepat:</span>
                                                <button type="button" class="quick-chip-btn active" id="btn-pay-all" data-pct="100">
                                                    Bayar Lunas (100%)
                                                </button>
                                                <button type="button" class="quick-chip-btn" data-pct="75">
                                                    75% Sisa
                                                </button>
                                                <button type="button" class="quick-chip-btn" data-pct="50">
                                                    50% Sisa
                                                </button>
                                                <button type="button" class="quick-chip-btn" data-pct="25">
                                                    25% Sisa
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Description -->
                                        <div>
                                            <label class="form-label fw-semibold text-dark small mb-1" for="description">
                                                Keterangan / Catatan Pembayaran
                                            </label>
                                            <textarea class="form-control form-control-sm" name="description" id="description" rows="2"
                                                placeholder="Tambahkan catatan transfer, nomor referensi bukti transfer, dsb. (opsional)"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="modal-footer bg-light py-3 px-4 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-3">
                                <div>
                                    <span class="text-muted fs-11 d-block">Akan Dibayar:</span>
                                    <span class="fw-bold text-primary font-monospace fs-13" id="footer-paying-amount">Rp 0</span>
                                </div>
                                <div class="vr"></div>
                                <div>
                                    <span class="text-muted fs-11 d-block">Sisa Tagihan Akhir:</span>
                                    <span class="fw-bold font-monospace fs-13" id="footer-remaining-after">Rp 0</span>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-secondary px-3" type="button" data-bs-dismiss="modal">Batal</button>
                                <button class="btn btn-primary px-4 fw-bold shadow-sm" type="submit">
                                    <i class="mdi mdi-check me-1"></i>{{ __('general.save') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Detail Modal: Rincian Pembayaran Langsung -->
        <div class="modal fade" id="detail-modal" tabindex="-1" role="dialog"
            aria-labelledby="detailModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                    <!-- Header -->
                    <div class="modal-header text-white d-flex align-items-center justify-content-between"
                         style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%) !important; padding: 18px 24px; border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-3 d-flex align-items-center justify-content-center text-white shadow-sm"
                                 style="width: 44px; height: 44px; background: rgba(59, 130, 246, 0.2); border: 1px solid rgba(59, 130, 246, 0.4);">
                                <i class="mdi mdi-receipt-text-check-outline fs-22 text-info"></i>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <h5 class="modal-title fw-bold text-white mb-0" id="detailModalLabel">
                                        Rincian Pembayaran Langsung
                                    </h5>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2 py-1 fs-12" id="detail-order-badge">
                                        -
                                    </span>
                                    <span class="badge px-3 py-1 fs-11 rounded-pill fw-bold" id="detail-status-badge">
                                        -
                                    </span>
                                </div>
                                <div class="text-white-50 fs-12 mt-1">
                                    <span><i class="mdi mdi-account-outline me-1"></i><strong class="text-white" id="detail-customer-name">-</strong></span>
                                    <span class="mx-2">•</span>
                                    <span><i class="mdi mdi-calendar-blank-outline me-1"></i><span id="detail-order-date">-</span></span>
                                </div>
                            </div>
                        </div>
                        <button class="btn-close btn-close-white" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <!-- Modal Body -->
                    <div class="modal-body p-4 bg-body">
                        <div class="row g-4">
                            <!-- Left / Main Column (Operasional, On Charge, Riwayat) -->
                            <div class="col-lg-8">
                                <!-- Card: Detail Operasional & Rute -->
                                <div class="card border border-light-subtle shadow-sm mb-4">
                                    <div class="card-header bg-transparent border-0 pt-3 pb-0">
                                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                            <i class="mdi mdi-truck-delivery text-primary fs-18"></i>Detail Operasional & Rute
                                        </h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="row g-3">
                                            <div class="col-md-4 col-sm-6">
                                                <div class="p-2 border rounded-3 bg-light">
                                                    <span class="text-muted d-block fs-11 mb-1">Customer</span>
                                                    <span class="fw-bold text-dark fs-12" id="detail-op-customer">-</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-sm-6">
                                                <div class="p-2 border rounded-3 bg-light">
                                                    <span class="text-muted d-block fs-11 mb-1">No. Polisi / Nopol</span>
                                                    <span class="fw-bold text-dark fs-12 font-monospace" id="detail-op-fleet">-</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-sm-6">
                                                <div class="p-2 border rounded-3 bg-light">
                                                    <span class="text-muted d-block fs-11 mb-1">Driver</span>
                                                    <span class="fw-bold text-dark fs-12" id="detail-op-driver">-</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-sm-6">
                                                <div class="p-2 border rounded-3 bg-light">
                                                    <span class="text-muted d-block fs-11 mb-1">Tanggal Order</span>
                                                    <span class="fw-bold text-dark fs-12" id="detail-op-date">-</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-sm-6">
                                                <div class="p-2 border rounded-3 bg-light">
                                                    <span class="text-muted d-block fs-11 mb-1">No. Surat Jalan</span>
                                                    <span class="fw-bold text-dark fs-12 font-monospace" id="detail-op-shipment">-</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-sm-6">
                                                <div class="p-2 border rounded-3 bg-light">
                                                    <span class="text-muted d-block fs-11 mb-1" id="detail-op-qty-title">Tipe Rute & Qty</span>
                                                    <span class="fw-bold text-dark fs-12 font-monospace" id="detail-op-qty">-</span>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="p-2 border rounded-3 bg-light">
                                                    <span class="text-muted d-block fs-11 mb-1">Rute Perjalanan</span>
                                                    <span class="fw-bold text-dark fs-13 d-flex align-items-center gap-2">
                                                        <span id="detail-op-origin">-</span>
                                                        <i class="mdi mdi-arrow-right text-primary"></i>
                                                        <span id="detail-op-destination">-</span>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-12" id="detail-op-notes-container" style="display: none;">
                                                <div class="p-2 border rounded-3 bg-light">
                                                    <span class="text-muted d-block fs-11 mb-1">Catatan Order</span>
                                                    <span class="text-dark fs-12" id="detail-op-notes">-</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Card: Biaya Tambahan (On Charge) -->
                                <div class="card border border-light-subtle shadow-sm mb-4" id="detail-oncharge-card" style="display: none;">
                                    <div class="card-header bg-transparent border-0 pt-3 pb-0">
                                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                            <i class="mdi mdi-playlist-plus text-primary fs-18"></i>Rincian Biaya Tambahan (On Charge)
                                        </h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover align-middle mb-0 fs-12">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th style="width: 5%">#</th>
                                                        <th>Komponen Biaya</th>
                                                        <th>Keterangan</th>
                                                        <th class="text-end" style="width: 25%">Nominal</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="detail-oncharge-tbody"></tbody>
                                                <tfoot>
                                                    <tr class="table-light fw-bold">
                                                        <td colspan="3" class="text-end">Total Biaya Tambahan</td>
                                                        <td class="text-end text-primary font-monospace" id="detail-oncharge-total">Rp 0</td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Card: Riwayat Transaksi Pembayaran -->
                                <div class="card border border-light-subtle shadow-sm">
                                    <div class="card-header bg-transparent border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                            <i class="mdi mdi-history text-primary fs-18"></i>Riwayat Pembayaran
                                        </h6>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill fs-11 px-2 py-1" id="detail-history-badge">
                                            0 Transaksi
                                        </span>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover align-middle mb-0 fs-12">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th style="width: 5%">#</th>
                                                        <th>Tanggal</th>
                                                        <th>Tipe</th>
                                                        <th>Bank Akun</th>
                                                        <th>Pajak & Claim</th>
                                                        <th>Keterangan</th>
                                                        <th class="text-end" style="width: 20%">Nominal Bayar</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="detail-history-tbody"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Financial Summary -->
                            <div class="col-lg-4">
                                <div class="modal-receipt-card h-auto shadow-sm">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                                            <i class="mdi mdi-calculator text-primary fs-18"></i>Rincian Keuangan
                                        </h6>

                                        <div class="list-group list-group-flush bg-transparent">
                                            <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-1 bg-transparent fs-12">
                                                <span class="text-muted">Harga Rute</span>
                                                <span class="fw-semibold text-dark font-monospace" id="detail-fin-cost">Rp 0</span>
                                            </div>
                                            <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-1 bg-transparent fs-12">
                                                <span class="text-muted">Biaya Tambahan</span>
                                                <span class="fw-semibold text-dark font-monospace" id="detail-fin-additional-cost">Rp 0</span>
                                            </div>
                                            <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-1 bg-transparent fs-12">
                                                <span class="fw-bold text-dark">Subtotal</span>
                                                <span class="fw-bold text-dark font-monospace" id="detail-fin-subtotal">Rp 0</span>
                                            </div>

                                            <hr class="my-2 opacity-25">

                                            <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-1 bg-transparent fs-12">
                                                <span class="text-muted" id="detail-fin-ppn-title">PPN (+)</span>
                                                <span class="fw-semibold text-success font-monospace" id="detail-fin-ppn">+ Rp 0</span>
                                            </div>
                                            <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-1 bg-transparent fs-12">
                                                <span class="text-muted" id="detail-fin-pph-title">PPh (-)</span>
                                                <span class="fw-semibold text-danger font-monospace" id="detail-fin-pph">- Rp 0</span>
                                            </div>
                                            <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-1 bg-transparent fs-12">
                                                <div>
                                                    <span class="text-muted">Biaya Claim (-)</span>
                                                    <div class="text-muted fs-11 fst-italic" id="detail-fin-claim-desc"></div>
                                                </div>
                                                <span class="fw-semibold text-warning-emphasis font-monospace" id="detail-fin-claim">- Rp 0</span>
                                            </div>

                                            <hr class="my-2 opacity-25">

                                            <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-1 bg-transparent">
                                                <span class="fw-bold text-dark fs-13">Total Tagihan</span>
                                                <span class="fw-bold text-dark fs-14 font-monospace" id="detail-fin-grandtotal">Rp 0</span>
                                            </div>
                                            <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-1 bg-transparent fs-12">
                                                <span class="text-muted">Sudah Dibayar</span>
                                                <span class="fw-semibold text-success font-monospace" id="detail-fin-payment">Rp 0</span>
                                            </div>
                                        </div>

                                        <div class="p-3 rounded-3 text-center mt-3 border transition-all" id="detail-fin-sisa-container">
                                            <span class="small d-block fw-bold mb-1" id="detail-fin-sisa-title">Sisa Tagihan</span>
                                            <span class="fs-4 fw-bold font-monospace" id="detail-fin-sisa-value">Rp 0</span>
                                        </div>
                                    </div>

                                    <div class="mt-3 pt-2 border-top d-flex flex-column gap-2">
                                        <button type="button" class="btn btn-outline-danger w-100 fw-semibold btn-sm shadow-sm" id="detail-btn-print-pdf">
                                            <i class="mdi mdi-file-pdf-box me-1"></i> Cetak Nota PDF
                                        </button>
                                        <button type="button" class="btn btn-success w-100 fw-bold btn-sm shadow-sm d-none" id="detail-btn-bayar-now">
                                            <i class="mdi mdi-credit-card-outline me-1"></i> Input Pembayaran
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="modal-footer bg-light py-2 px-4 d-flex justify-content-end">
                        <button class="btn btn-outline-secondary px-4 btn-sm" type="button" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="delete-form" method="post">
        @csrf
        @method('DELETE')
    </form>
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
    <script src="{{ asset('assets/js/helper.js') }}"></script>

    <script>
        let currentCost = 0;
        let currentAdditionalCost = 0;
        let currentPaid = 0;

        // Mode pajak: 'percent' (hitung dari subtotal) atau 'nominal' (isi langsung)
        let ppnMode = 'percent';
        let pphMode = 'percent';

        // Filter status aktif & daftar order terpilih
        let currentStatusFilter = 'all';
        let selectedOrders = [];

        $(document).ready(function() {
            let dataTableInstance = $('#dt').DataTable({
                "processing": true,
                "serverSide": true,
                "destroy": true,
                "ajax": {
                    "url": "{{ route('dt.direct-payment') }}",
                    "data": function(d) {
                        d.status = currentStatusFilter;
                    }
                },
                "columns": [
                    {
                        "data": 'DT_RowIndex',
                        "className": 'text-center align-middle',
                        "orderable": false,
                        "searchable": false,
                        "render": function(data, type, row) {
                            let rawCode = row.raw_code || '';
                            let isChecked = selectedOrders.includes(rawCode) ? 'checked' : '';
                            return `<div class="d-flex align-items-center justify-content-center gap-1">
                                        <input type="checkbox" class="form-check-input order-checkbox m-0" value="${rawCode}" ${isChecked}>
                                        <span class="text-muted fs-11 ms-1">${data}</span>
                                    </div>`;
                        }
                    },
                    { "data": 'action', "className": 'text-center align-middle', "orderable": false, "searchable": false },
                    { "data": 'code', "className": 'align-middle' },
                    { "data": 'orderDate', "className": 'align-middle text-center' },
                    { "data": 'fleet.plateNumber', "className": 'align-middle text-center' },
                    { "data": 'driver.name', "className": 'align-middle' },
                    { "data": 'shipmentNumber', "className": 'align-middle' },
                    { "data": 'customer.name', "className": 'align-middle' },
                    { "data": 'rute', "className": 'align-middle' },
                    { "data": 'cost', "className": 'text-end align-middle' },
                    { "data": 'additional_cost', "className": 'text-end align-middle' },
                    { "data": 'ppn', "className": 'text-end align-middle' },
                    { "data": 'pph', "className": 'text-end align-middle' },
                    { "data": 'claim', "className": 'text-end align-middle' },
                    { "data": 'grand_total', "className": 'text-end align-middle' },
                    { "data": 'paymentAmount', "className": 'text-end align-middle' },
                    { "data": 'total', "className": 'text-end align-middle' },
                    { "data": 'paymentStatus', "className": 'text-center align-middle' }
                ],
                "order": [
                    [2, 'asc']
                ],
                "drawCallback": function() {
                    // Sync checkboxes with selectedOrders
                    $('.order-checkbox').each(function() {
                        let code = $(this).val();
                        $(this).prop('checked', selectedOrders.includes(code));
                    });

                    let visibleCheckboxes = $('.order-checkbox');
                    if (visibleCheckboxes.length > 0) {
                        let allChecked = visibleCheckboxes.filter(':checked').length === visibleCheckboxes.length;
                        $('#check-all').prop('checked', allChecked);
                    } else {
                        $('#check-all').prop('checked', false);
                    }

                    // Init Bootstrap tooltips
                    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                        let tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                        tooltipTriggerList.map(function(el) {
                            return new bootstrap.Tooltip(el);
                        });
                    }
                }
            });

            // Status Filter Pill Click
            $('.filter-pill-btn').on('click', function() {
                $('.filter-pill-btn').removeClass('active');
                $(this).addClass('active');

                currentStatusFilter = $(this).data('status');
                dataTableInstance.ajax.reload();
            });

            // Metric Stat Card Click (Filter trigger)
            $('.stat-card').on('click', function() {
                let filter = $(this).data('filter');
                if (filter) {
                    $('.filter-pill-btn').removeClass('active');
                    $(`.filter-pill-btn[data-status="${filter}"]`).addClass('active');
                    currentStatusFilter = filter;
                    dataTableInstance.ajax.reload();
                }
            });

            // Refresh Button Click
            $('#btn-refresh-table').on('click', function() {
                let icon = $('#refresh-icon');
                icon.addClass('mdi-spin');
                dataTableInstance.ajax.reload(function() {
                    setTimeout(() => icon.removeClass('mdi-spin'), 400);
                });
            });

            // Check All Handler
            $('#check-all').on('change', function() {
                let isChecked = $(this).is(':checked');
                $('.order-checkbox').each(function() {
                    let code = $(this).val();
                    $(this).prop('checked', isChecked);
                    if (isChecked) {
                        if (!selectedOrders.includes(code)) selectedOrders.push(code);
                    } else {
                        selectedOrders = selectedOrders.filter(c => c !== code);
                    }
                });
                updateSelectionUI();
            });

            // Individual Row Checkbox Handler
            $(document).on('change', '.order-checkbox', function() {
                let code = $(this).val();
                if ($(this).is(':checked')) {
                    if (!selectedOrders.includes(code)) selectedOrders.push(code);
                } else {
                    selectedOrders = selectedOrders.filter(c => c !== code);
                }

                let visibleCheckboxes = $('.order-checkbox');
                let allChecked = visibleCheckboxes.filter(':checked').length === visibleCheckboxes.length;
                $('#check-all').prop('checked', allChecked);

                updateSelectionUI();
            });

            // Clear Selection
            $('#btn-clear-selection').on('click', function() {
                selectedOrders = [];
                $('.order-checkbox').prop('checked', false);
                $('#check-all').prop('checked', false);
                updateSelectionUI();
            });

            function updateSelectionUI() {
                let count = selectedOrders.length;
                $('#selected-count').text(count);
                $('#btn-count').text(count);

                if (count > 0) {
                    $('#selection-bar').attr('style', 'display: flex !important;');
                    $('#btn-print-multi').removeClass('d-none');
                } else {
                    $('#selection-bar').attr('style', 'display: none !important;');
                    $('#btn-print-multi').addClass('d-none');
                }
            }

            // Submit Multi PDF Print
            $('#btn-print-multi').on('click', function() {
                if (selectedOrders.length === 0) {
                    swal("Peringatan", "Pilih minimal 1 order untuk dicetak.", "warning");
                    return;
                }

                let container = $('#pdf-multi-inputs');
                container.empty();
                selectedOrders.forEach(code => {
                    container.append(`<input type="hidden" name="orderCodes[]" value="${code}">`);
                });

                $('#pdf-multi-form').submit();
            });
        });

        function showModal(code) {
            $('#modal-order-badge').text(code);
            $('#modal-customer-name').text('Memuat...');
            $('#modal-order-date').text('-');
            $('#modal-plate-number').text('-');
            $('#modal-driver-name').text('-');
            $('#modal-route').text('-');
            $('#nominalInput').val('');
            $('.quick-chip-btn').removeClass('active');
            $('#btn-pay-all').addClass('active');

            $('#payment-modal').modal('show');
            $('.js-example-basic').select2({
                dropdownParent: $('#payment-modal'),
                width: "100%",
            });
            $('#orderCode').val(code);
            getDirectPaymentDetail(code);
        }

        function formatAngkaValue(value) {
            if (value == null) return "";
            let angka = Math.round(value).toString().replace(/\./g, "");
            return new Intl.NumberFormat("id-ID").format(angka);
        }

        // Parse input angka format Indonesia (titik ribuan, koma desimal)
        function parseNumber(value) {
            if (value === null || value === undefined) return 0;
            let clean = String(value).trim().replace(/\./g, '').replace(',', '.');
            let n = parseFloat(clean);
            return isNaN(n) || n < 0 ? 0 : n;
        }

        function cap(text) {
            return text.charAt(0).toUpperCase() + text.slice(1);
        }

        // Nilai pajak aktif: nominal, persentase (null jika mode nominal / tidak dipakai),
        // dan tipe untuk dikirim ke server
        function getActiveTax(tax) {
            let mode = tax === 'ppn' ? ppnMode : pphMode;
            let checked = $('#use' + cap(tax)).is(':checked');

            if (!checked) {
                return { nominal: 0, percent: null, type: 'nominal' };
            }

            let subtotal = currentCost + currentAdditionalCost;

            if (mode === 'percent') {
                let pct = parseNumber($('#' + tax + 'Input').val());
                return { nominal: subtotal * pct / 100, percent: pct, type: 'percent' };
            }

            return { nominal: parseNumber($('#' + tax + 'Input').val()), percent: null, type: 'nominal' };
        }

        function setTaxMode(tax, mode) {
            if (tax === 'ppn') {
                ppnMode = mode;
            } else {
                pphMode = mode;
            }

            $('.' + tax + '-mode-btn').removeClass('active');
            $('.' + tax + '-mode-btn[data-mode="' + mode + '"]').addClass('active');

            if (mode === 'percent') {
                $('#' + tax + '-prefix').text('%');
                $('#' + tax + 'Input').attr('placeholder', 'Contoh: 11');
            } else {
                $('#' + tax + '-prefix').text('Rp');
                $('#' + tax + 'Input').attr('placeholder', 'Masukkan nominal');
            }
        }

        function applyTaxVisibility(tax) {
            let checked = $('#use' + cap(tax)).is(':checked');
            $('#' + tax + '-input-group').toggle(checked);
            $('#' + tax + '-mode-group').toggle(checked);
        }

        function getDirectPaymentDetail(orderCode) {
            $.get("{{ url('ajax/direct-payment-detail') }}/" + orderCode, function(data) {
                $('#modal-order-badge').text(data.order_code || orderCode);
                $('#modal-customer-name').text(data.customer_name || '-');
                $('#modal-order-date').text(data.order_date || '-');
                $('#modal-plate-number').text(data.plate_number || '-');
                $('#modal-driver-name').text(data.driver_name || '-');
                $('#modal-route').text((data.route_origin || '-') + ' → ' + (data.route_destination || '-'));

                currentCost = parseFloat(data.cost) || 0;
                currentAdditionalCost = parseFloat(data.additional_cost) || 0;
                currentPaid = parseFloat(data.payment) || 0;

                setupTaxInput('ppn', data);
                setupTaxInput('pph', data);

                let storedClaim = parseFloat(data.claim) || 0;
                let storedClaimDesc = data.claim_description || '';

                if (storedClaim > 0) {
                    $('#useClaim').prop('checked', true);
                    $('#claim-input-group').show();
                    $('#claim-badge').show();
                    $('#claimInput').val(formatAngkaValue(storedClaim));
                    $('#claim_description').val(storedClaimDesc);
                } else {
                    $('#useClaim').prop('checked', false);
                    $('#claim-input-group').hide();
                    $('#claim-badge').hide();
                    $('#claimInput').val('');
                    $('#claim_description').val('');
                }

                updateCalculatedTotal();

                // Auto-fill nominalInput with the sisa tagihan by default
                let sisaTagihan = computeSisa();
                if (sisaTagihan < 0) sisaTagihan = 0;
                $('#nominalInput').val(formatAngkaValue(sisaTagihan));
                $('.quick-chip-btn').removeClass('active');
                $('#btn-pay-all').addClass('active');

                updatePaymentRealtime();
            });
        }

        function setupTaxInput(tax, data) {
            let percentStored = data[tax + '_percent'];
            let customerPercent = parseFloat(data['customer_' + tax + '_percent']) || 0;
            let storedNominal = parseFloat(data[tax]) || 0;
            let hasPayment = (parseFloat(data.payment) || 0) > 0;

            let usePercent;
            let value;
            let checked;

            if (percentStored !== null && percentStored !== undefined) {
                // Tagihan tersimpan memakai persentase
                usePercent = true;
                value = percentStored;
                checked = true;
            } else if (hasPayment && storedNominal > 0) {
                // Pembayaran sebelumnya memakai nominal (data lama)
                usePercent = false;
                value = storedNominal;
                checked = true;
            } else if (customerPercent > 0) {
                // Default persentase dari master customer
                usePercent = true;
                value = customerPercent;
                checked = true;
            } else {
                usePercent = true;
                value = '';
                checked = false;
            }

            setTaxMode(tax, usePercent ? 'percent' : 'nominal');
            $('#' + tax + 'Input').val(usePercent ? value : formatAngkaValue(value));
            $('#use' + cap(tax)).prop('checked', checked);
            applyTaxVisibility(tax);
        }

        function getActiveClaim() {
            let checked = $('#useClaim').is(':checked');
            if (!checked) return 0;
            return parseNumber($('#claimInput').val());
        }

        function computeSisa() {
            let ppn = getActiveTax('ppn');
            let pph = getActiveTax('pph');
            let claim = getActiveClaim();
            let subtotal = currentCost + currentAdditionalCost;
            return subtotal + ppn.nominal - pph.nominal - claim - currentPaid;
        }

        function updateCalculatedTotal() {
            let ppn = getActiveTax('ppn');
            let pph = getActiveTax('pph');
            let claim = getActiveClaim();

            let subtotal = currentCost + currentAdditionalCost;
            let grandTotal = subtotal + ppn.nominal - pph.nominal - claim;
            let sisaTagihan = grandTotal - currentPaid;

            // Update labels
            $('#cost_label').text('Rp ' + formatAngkaValue(currentCost));
            $('#additional_cost_label').text('Rp ' + formatAngkaValue(currentAdditionalCost));
            $('#subtotal_label').text('Rp ' + formatAngkaValue(subtotal));
            $('#ppn_label').text('+ Rp ' + formatAngkaValue(ppn.nominal) + (ppn.percent !== null ? ' (' + ppn.percent + '%)' : ''));
            $('#pph_label').text('- Rp ' + formatAngkaValue(pph.nominal) + (pph.percent !== null ? ' (' + pph.percent + '%)' : ''));
            $('#claim_label').text('- Rp ' + formatAngkaValue(claim));
            $('#grand_total_label').text('Rp ' + formatAngkaValue(grandTotal));
            $('#payment_label').text('Rp ' + formatAngkaValue(currentPaid));

            // Update progress bar
            let pct = grandTotal > 0 ? Math.min(100, Math.max(0, Math.round((currentPaid / grandTotal) * 100))) : 0;
            $('#modal-progress-bar').css('width', pct + '%');
            $('#modal-progress-pct').text(pct + '%');
            $('#progress_paid_label').text('Rp ' + formatAngkaValue(currentPaid));
            if (sisaTagihan <= 0) {
                $('#progress_remaining_label').removeClass('text-danger').addClass('text-success').text('Lunas');
            } else {
                $('#progress_remaining_label').removeClass('text-success').addClass('text-danger').text('Rp ' + formatAngkaValue(sisaTagihan));
            }

            if (sisaTagihan < 0) {
                $('#sisa_tagihan_container')
                    .removeClass('bg-danger-subtle text-danger border-danger-subtle bg-success-subtle text-success border-success-subtle')
                    .addClass('bg-info-subtle text-info border-info-subtle');
                $('#sisa_tagihan_title').show().text('Kelebihan Bayar');
                $('#sisa_tagihan_value').text('Rp ' + formatAngkaValue(Math.abs(sisaTagihan)));
            } else if (sisaTagihan > 0) {
                $('#sisa_tagihan_container')
                    .removeClass('bg-success-subtle text-success border-success-subtle bg-info-subtle text-info border-info-subtle')
                    .addClass('bg-danger-subtle text-danger border-danger-subtle');
                $('#sisa_tagihan_title').show().text('Sisa Tagihan');
                $('#sisa_tagihan_value').text('Rp ' + formatAngkaValue(sisaTagihan));
            } else {
                $('#sisa_tagihan_container')
                    .removeClass('bg-danger-subtle text-danger border-danger-subtle bg-info-subtle text-info border-info-subtle')
                    .addClass('bg-success-subtle text-success border-success-subtle');
                $('#sisa_tagihan_title').hide();
                $('#sisa_tagihan_value').html('<i class="mdi mdi-check-circle-outline me-1"></i>Lunas');
            }

            // Update hidden fields
            $('#costHidden').val(currentCost);
            $('#additional_costHidden').val(currentAdditionalCost);
            $('#ppnHidden').val(Math.round(ppn.nominal));
            $('#ppnTypeHidden').val(ppn.type);
            $('#ppnPercentHidden').val(ppn.percent !== null ? ppn.percent : '');
            $('#pphHidden').val(Math.round(pph.nominal));
            $('#pphTypeHidden').val(pph.type);
            $('#pphPercentHidden').val(pph.percent !== null ? pph.percent : '');
            $('#claimHidden').val(Math.round(claim));
            $('#totalHidden').val(sisaTagihan < 0 ? 0 : sisaTagihan);

            updatePaymentRealtime();
        }

        function updatePaymentRealtime() {
            let sisaTagihan = computeSisa();
            let nominal = parseNumber($('#nominalInput').val());

            $('#footer-paying-amount').text('Rp ' + formatAngkaValue(nominal));

            let sisaAkhir = sisaTagihan - nominal;
            if (sisaAkhir <= 0 && (nominal > 0 || sisaTagihan <= 0)) {
                $('#footer-remaining-after').html('<i class="mdi mdi-check-circle-outline me-1"></i>Lunas').removeClass('text-danger text-dark').addClass('text-success');
            } else if (sisaAkhir > 0) {
                $('#footer-remaining-after').text('Rp ' + formatAngkaValue(sisaAkhir)).removeClass('text-success text-dark').addClass('text-danger');
            } else {
                let remainingVal = Math.max(0, sisaTagihan);
                if (remainingVal === 0) {
                    $('#footer-remaining-after').html('<i class="mdi mdi-check-circle-outline me-1"></i>Lunas').removeClass('text-danger text-dark').addClass('text-success');
                } else {
                    $('#footer-remaining-after').text('Rp ' + formatAngkaValue(remainingVal)).removeClass('text-success text-danger').addClass('text-dark');
                }
            }

            // Update status badge
            if (nominal <= 0) {
                $('#payment-status-badge')
                    .removeClass('bg-success-subtle text-success border-success-subtle bg-info-subtle text-info border-info-subtle')
                    .addClass('bg-secondary-subtle text-secondary border-secondary-subtle')
                    .text('Belum Mengisi');
            } else if (nominal >= sisaTagihan && sisaTagihan > 0) {
                $('#payment-status-badge')
                    .removeClass('bg-secondary-subtle text-secondary border-secondary-subtle bg-info-subtle text-info border-info-subtle')
                    .addClass('bg-success-subtle text-success border-success-subtle')
                    .text('Pelunasan Penuh');
            } else {
                $('#payment-status-badge')
                    .removeClass('bg-secondary-subtle text-secondary border-secondary-subtle bg-success-subtle text-success border-success-subtle')
                    .addClass('bg-info-subtle text-info border-info-subtle')
                    .text('DP / Cicilan (Partial)');
            }
        }

        // Handle PPN/PPH switch change
        $(document).on('change', '#usePpn', function() {
            applyTaxVisibility('ppn');
            updateCalculatedTotal();
        });

        $(document).on('change', '#usePph', function() {
            applyTaxVisibility('pph');
            updateCalculatedTotal();
        });

        // Handle mode change (persen <-> nominal), konversi nilai agar mulus
        $(document).on('click', '.ppn-mode-btn', function() {
            if (ppnMode === $(this).data('mode')) return;

            let subtotal = currentCost + currentAdditionalCost;
            if ($(this).data('mode') === 'nominal') {
                let pct = parseNumber($('#ppnInput').val());
                $('#ppnInput').val(formatAngkaValue(subtotal * pct / 100));
            } else {
                let nominal = parseNumber($('#ppnInput').val());
                let pct = subtotal > 0 ? (nominal / subtotal * 100) : 0;
                $('#ppnInput').val(Math.round(pct * 100) / 100);
            }

            setTaxMode('ppn', $(this).data('mode'));
            updateCalculatedTotal();
        });

        $(document).on('click', '.pph-mode-btn', function() {
            if (pphMode === $(this).data('mode')) return;

            let subtotal = currentCost + currentAdditionalCost;
            if ($(this).data('mode') === 'nominal') {
                let pct = parseNumber($('#pphInput').val());
                $('#pphInput').val(formatAngkaValue(subtotal * pct / 100));
            } else {
                let nominal = parseNumber($('#pphInput').val());
                let pct = subtotal > 0 ? (nominal / subtotal * 100) : 0;
                $('#pphInput').val(Math.round(pct * 100) / 100);
            }

            setTaxMode('pph', $(this).data('mode'));
            updateCalculatedTotal();
        });

        // Recalculate saat nilai pajak diubah
        $(document).on('input', '#ppnInput', function() {
            updateCalculatedTotal();
        });

        $(document).on('input', '#pphInput', function() {
            updateCalculatedTotal();
        });

        // Handle Claim toggle and input
        $(document).on('change', '#useClaim', function() {
            let checked = $(this).is(':checked');
            $('#claim-input-group').toggle(checked);
            $('#claim-badge').toggle(checked);
            updateCalculatedTotal();
        });

        $(document).on('input', '#claimInput', function() {
            updateCalculatedTotal();
        });

        // Handle quick-chip button clicks (100%, 75%, 50%, 25%)
        $(document).on('click', '.quick-chip-btn', function() {
            $('.quick-chip-btn').removeClass('active');
            $(this).addClass('active');

            let pct = parseInt($(this).data('pct')) || 100;
            let sisa = Math.max(0, computeSisa());
            let nominal = Math.round(sisa * pct / 100);
            $('#nominalInput').val(formatAngkaValue(nominal));
            updatePaymentRealtime();
        });

        // Input listener on #nominalInput
        $(document).on('input', '#nominalInput', function() {
            let val = parseNumber($(this).val());
            let sisa = Math.max(0, computeSisa());

            $('.quick-chip-btn').removeClass('active');
            if (val === sisa && sisa > 0) {
                $('.quick-chip-btn[data-pct="100"]').addClass('active');
            } else if (val === Math.round(sisa * 0.75) && sisa > 0) {
                $('.quick-chip-btn[data-pct="75"]').addClass('active');
            } else if (val === Math.round(sisa * 0.5) && sisa > 0) {
                $('.quick-chip-btn[data-pct="50"]').addClass('active');
            } else if (val === Math.round(sisa * 0.25) && sisa > 0) {
                $('.quick-chip-btn[data-pct="25"]').addClass('active');
            }

            updatePaymentRealtime();
        });

        // Reset modal on hide
        $('#payment-modal').on('hidden.bs.modal', function() {
            $('#payment-form')[0].reset();
            $('#userBankCode').val('').trigger('change');
            $('#modal-order-badge').text('-');
            $('#modal-customer-name').text('-');
            $('#modal-order-date').text('-');
            $('#modal-plate-number').text('-');
            $('#modal-driver-name').text('-');
            $('#modal-route').text('-');
            $('.quick-chip-btn').removeClass('active');
            $('#btn-pay-all').addClass('active');
            $('#claim-input-group').hide();
            $('#claim-badge').hide();
            $('#ppn-input-group').hide();
            $('#ppn-mode-group').hide();
            $('#pph-input-group').hide();
            $('#pph-mode-group').hide();
        });

        function validateAndFormatForm(paymentAmountId) {
            const total = parseInt(document.getElementById("totalHidden").value) || 0;
            const nominalInput = document.getElementById("nominalInput");
            const paymentAmount = parseNumber(nominalInput.value);

            if (paymentAmount <= 0) {
                swal({
                    title: "{{ __('general.warning') }}",
                    text: "Nominal pembayaran harus lebih besar dari 0",
                    icon: "warning",
                });
                return false;
            }

            // Set hidden fields
            document.getElementById("paymentAmountHidden").value = paymentAmount;
            if (paymentAmount >= total) {
                document.getElementById("type").value = "Full";
            } else {
                document.getElementById("type").value = "Dp";
            }

            return true; // Lanjut submit
        }

        $(document).on('submit', '#payment-form', function(e) {
            e.preventDefault();

            if (!validateAndFormatForm()) {
                return;
            }

            let form = $(this);
            let url = form.attr('action');
            let data = form.serialize();

            $.ajax({
                type: 'POST',
                url: url,
                data: data,
                success: function(response) {
                    $('#payment-modal').modal('hide');
                    form.trigger('reset');
                    $('#userBankCode').val('').trigger('change');
                    $('#useClaim').prop('checked', false);
                    $('#claim-input-group').hide();
                    $('#claim-badge').hide();
                    $('#claimInput').val('');
                    $('#claim_description').val('');

                    // Reload DataTable
                    $('#dt').DataTable().ajax.reload(null, false);

                    swal({
                        title: "Berhasil!",
                        text: "Pembayaran berhasil disimpan.",
                        icon: "success",
                        button: "OK",
                    });
                },
                error: function(xhr) {
                    let errorMessage = "Terjadi kesalahan saat menyimpan pembayaran.";
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    swal({
                        title: "Gagal!",
                        text: errorMessage,
                        icon: "error",
                        button: "OK",
                    });
                }
            });
        });

        let currentDetailOrderCode = null;

        function showDetailModal(orderCode) {
            currentDetailOrderCode = orderCode;
            $('#detail-order-badge').text(orderCode);
            $('#detail-customer-name').text('Memuat...');
            $('#detail-order-date').text('-');
            $('#detail-status-badge').attr('class', 'badge bg-secondary-subtle text-secondary px-3 py-1 fs-11 rounded-pill fw-bold').text('Memuat...');
            $('#detail-history-tbody').html('<tr><td colspan="7" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>Memuat data...</td></tr>');
            $('#detail-oncharge-card').hide();

            $('#detail-modal').modal('show');

            $.get("{{ url('ajax/direct-payment-detail') }}/" + orderCode, function(data) {
                $('#detail-order-badge').text(data.order_code || orderCode);
                $('#detail-customer-name').text(data.customer_name || '-');
                $('#detail-order-date').text(data.order_date || '-');

                // Status Badge
                let statusBadgeClass = 'bg-danger text-white';
                if (data.status_code === 'paid') {
                    statusBadgeClass = 'bg-success text-white';
                } else if (data.status_code === 'overpaid') {
                    statusBadgeClass = 'bg-info text-white';
                } else if (data.status_code === 'partial') {
                    statusBadgeClass = 'bg-warning text-dark';
                }
                $('#detail-status-badge').attr('class', 'badge px-3 py-1 fs-11 rounded-pill fw-bold ' + statusBadgeClass).text(data.status);

                // Operasional
                $('#detail-op-customer').text(data.customer_name || '-');
                $('#detail-op-fleet').text(data.plate_number || '-');
                $('#detail-op-driver').text(data.driver_name || '-');
                $('#detail-op-date').text(data.order_date || '-');
                $('#detail-op-shipment').text(data.shipment_number || '-');
                $('#detail-op-qty-title').text(data.route_type_label || 'Qty');
                $('#detail-op-qty').text((data.route_type_label || '') + ': ' + formatAngkaValue(data.qty || 0));
                $('#detail-op-origin').text(data.route_origin || '-');
                $('#detail-op-destination').text(data.route_destination || '-');

                if (data.notes && data.notes.trim() !== '') {
                    $('#detail-op-notes').text(data.notes);
                    $('#detail-op-notes-container').show();
                } else {
                    $('#detail-op-notes-container').hide();
                }

                // On Charge Breakdown
                if (data.additional_costs && data.additional_costs.length > 0) {
                    let onChargeHtml = '';
                    let totalOnCharge = 0;
                    data.additional_costs.forEach((item, idx) => {
                        totalOnCharge += item.nominal;
                        onChargeHtml += `<tr>
                            <td>${idx + 1}</td>
                            <td class="fw-semibold text-dark">${item.component}</td>
                            <td class="text-muted small">${item.description}</td>
                            <td class="text-end fw-bold text-dark font-monospace">Rp ${formatAngkaValue(item.nominal)}</td>
                        </tr>`;
                    });
                    $('#detail-oncharge-tbody').html(onChargeHtml);
                    $('#detail-oncharge-total').text('Rp ' + formatAngkaValue(totalOnCharge));
                    $('#detail-oncharge-card').show();
                } else {
                    $('#detail-oncharge-card').hide();
                }

                // Riwayat Pembayaran
                if (data.histories && data.histories.length > 0) {
                    let historyHtml = '';
                    data.histories.forEach((item, idx) => {
                        let ppnText = '-';
                        if (item.ppn_percent !== null && item.ppn_percent > 0) {
                            ppnText = item.ppn_percent + '% (Rp ' + formatAngkaValue(item.ppn) + ')';
                        } else if (item.ppn > 0) {
                            ppnText = 'Rp ' + formatAngkaValue(item.ppn);
                        }

                        let pphText = '-';
                        if (item.pph_percent !== null && item.pph_percent > 0) {
                            pphText = item.pph_percent + '% (Rp ' + formatAngkaValue(item.pph) + ')';
                        } else if (item.pph > 0) {
                            pphText = 'Rp ' + formatAngkaValue(item.pph);
                        }

                        let claimHtml = '';
                        if (item.claim > 0) {
                            claimHtml = `<div class="text-warning-emphasis fw-semibold small">Claim: -Rp ${formatAngkaValue(item.claim)} ${item.claim_description ? '<span class="text-muted fw-normal">(' + item.claim_description + ')</span>' : ''}</div>`;
                        }

                        let typeBadge = item.type === 'Full'
                            ? '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill fs-11">Full</span>'
                            : '<span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill fs-11">DP</span>';

                        historyHtml += `<tr>
                            <td>${idx + 1}</td>
                            <td>${item.date}</td>
                            <td>${typeBadge}</td>
                            <td><span class="text-dark small fw-semibold">${item.bank}</span></td>
                            <td>
                                <div class="small text-muted">PPN: ${ppnText}</div>
                                <div class="small text-muted">PPh: ${pphText}</div>
                                ${claimHtml}
                            </td>
                            <td><span class="text-muted small">${item.description || '-'}</span></td>
                            <td class="text-end fw-bold text-success font-monospace">Rp ${formatAngkaValue(item.total)}</td>
                        </tr>`;
                    });
                    $('#detail-history-tbody').html(historyHtml);
                    $('#detail-history-badge').text(data.histories.length + ' Transaksi');
                } else {
                    $('#detail-history-tbody').html('<tr><td colspan="7" class="text-center py-4 text-muted"><i class="mdi mdi-credit-card-off fs-3 d-block mb-1"></i>Belum ada riwayat pembayaran untuk order ini.</td></tr>');
                    $('#detail-history-badge').text('0 Transaksi');
                }

                // Financial Summary
                let subtotal = (parseFloat(data.cost) || 0) + (parseFloat(data.additional_cost) || 0);
                let grandTotal = parseFloat(data.grand_total) || 0;
                let payment = parseFloat(data.payment) || 0;
                let sisaTagihan = grandTotal - payment;

                $('#detail-fin-cost').text('Rp ' + formatAngkaValue(data.cost));
                $('#detail-fin-additional-cost').text('Rp ' + formatAngkaValue(data.additional_cost));
                $('#detail-fin-subtotal').text('Rp ' + formatAngkaValue(subtotal));

                let ppnTitle = 'PPN (+)' + (data.ppn_percent !== null ? ' ' + data.ppn_percent + '%' : '');
                $('#detail-fin-ppn-title').text(ppnTitle);
                $('#detail-fin-ppn').text('+ Rp ' + formatAngkaValue(data.ppn));

                let pphTitle = 'PPh (-)' + (data.pph_percent !== null ? ' ' + data.pph_percent + '%' : '');
                $('#detail-fin-pph-title').text(pphTitle);
                $('#detail-fin-pph').text('- Rp ' + formatAngkaValue(data.pph));

                $('#detail-fin-claim').text('- Rp ' + formatAngkaValue(data.claim));
                $('#detail-fin-claim-desc').text(data.claim_description || '');

                $('#detail-fin-grandtotal').text('Rp ' + formatAngkaValue(grandTotal));
                $('#detail-fin-payment').text('Rp ' + formatAngkaValue(payment));

                if (sisaTagihan < 0) {
                    $('#detail-fin-sisa-container')
                        .removeClass('bg-danger-subtle text-danger border-danger-subtle bg-success-subtle text-success border-success-subtle')
                        .addClass('bg-info-subtle text-info border-info-subtle');
                    $('#detail-fin-sisa-title').show().text('Kelebihan Bayar');
                    $('#detail-fin-sisa-value').text('Rp ' + formatAngkaValue(Math.abs(sisaTagihan)));
                } else if (sisaTagihan > 0) {
                    $('#detail-fin-sisa-container')
                        .removeClass('bg-success-subtle text-success border-success-subtle bg-info-subtle text-info border-info-subtle')
                        .addClass('bg-danger-subtle text-danger border-danger-subtle');
                    $('#detail-fin-sisa-title').show().text('Sisa Tagihan');
                    $('#detail-fin-sisa-value').text('Rp ' + formatAngkaValue(sisaTagihan));
                } else {
                    $('#detail-fin-sisa-container')
                        .removeClass('bg-danger-subtle text-danger border-danger-subtle bg-info-subtle text-info border-info-subtle')
                        .addClass('bg-success-subtle text-success border-success-subtle');
                    $('#detail-fin-sisa-title').hide();
                    $('#detail-fin-sisa-value').html('<i class="mdi mdi-check-circle-outline me-1"></i>Lunas');
                }

                // Bayar now button in detail modal
                if (data.status_code !== 'paid') {
                    $('#detail-btn-bayar-now').removeClass('d-none');
                } else {
                    $('#detail-btn-bayar-now').addClass('d-none');
                }
            });
        }

        // Print single PDF from detail modal
        $(document).on('click', '#detail-btn-print-pdf', function() {
            if (!currentDetailOrderCode) return;
            let container = $('#pdf-multi-inputs');
            container.empty();
            container.append(`<input type="hidden" name="orderCodes[]" value="${currentDetailOrderCode}">`);
            $('#pdf-multi-form').submit();
        });

        // Click "Input Pembayaran" inside detail modal
        $(document).on('click', '#detail-btn-bayar-now', function() {
            if (!currentDetailOrderCode) return;
            let targetOrder = currentDetailOrderCode;
            $('#detail-modal').modal('hide');
            setTimeout(() => {
                showModal(targetOrder);
            }, 350);
        });
    </script>
@endpush
