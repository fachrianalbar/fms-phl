@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Pembayaran Langsung',
    'secondSegment' => 'Nota Belum Lunas',
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

@include('direct-payment.partials.table-style')

<style>
    /* Bar perintah pilihan */
    .selection-command-bar {
        position: sticky;
        top: 74px;
        z-index: 1010;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        margin-bottom: 18px;
        padding: 13px 16px;
        border-radius: 12px;
        background: #1e293b;
        color: #fff;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.25);
    }

    .selection-command-bar .selection-facts {
        display: flex;
        flex-wrap: wrap;
        gap: 8px 18px;
        font-size: 12px;
    }

    .selection-command-bar .selection-facts strong { font-size: 14px; }
    .selection-command-bar .selection-facts span { color: #94a3b8; }
    .selection-command-bar .selection-actions { display: flex; flex-wrap: wrap; gap: 8px; }
    .selection-command-bar .btn-light { color: #1e293b; }

    .table-scroll-wrap {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .table-scroll-wrap .direct-payment-table {
        min-width: 1360px;
        width: max-content !important;
    }

    /* Select2 untuk dropdown "Tampilkan _MENU_ data" milik DataTables */
    .dataTables_length .select2-container { width: 88px !important; }
    .dataTables_length .select2-container .select2-selection--single {
        height: 34px !important;
        padding: 4px 10px !important;
        border-radius: 8px !important;
    }
    .dataTables_length .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 24px !important;
        font-size: 13px !important;
    }
    .dataTables_length .select2-container--default .select2-selection--single .select2-selection__arrow { height: 32px !important; }

    @media (max-width: 575.98px) {
        .selection-command-bar { top: 8px; align-items: stretch; flex-direction: column; }
        .selection-command-bar .selection-actions { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .selection-command-bar .selection-actions .btn:last-child { grid-column: 1 / -1; }
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
                        {{ number_format($stats['totalCount'] ?? 0) }} Nota
                    </span>
                </h4>
                <p class="text-muted mb-0 fs-12">
                    Pembayaran nota tagihan multi-DO (DP, Cicilan &amp; Pelunasan) dengan PPN/PPh, claim, dan riwayat transaksi.
                </p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('direct-payment.order.waiting') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm fw-semibold">
                <i class="mdi mdi-tray-full me-1"></i> Order Menunggu Nota
            </a>
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm" id="btn-refresh-table" title="Muat Ulang Data Tabel">
                <i class="mdi mdi-refresh me-1" id="refresh-icon"></i> Refresh
            </button>
        </div>
    </div>

    <!-- 4 KPI Executive Metric Cards -->
    <div class="row g-3 mb-4">
        <!-- Card 1: Total Nota Belum Lunas -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card" data-filter="all" title="Klik untuk tampilkan semua nota">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Total Nota Belum Lunas</div>
                        <div class="stat-value text-primary">
                            {{ number_format($stats['totalCount'] ?? 0) }}
                            <span class="fs-13 text-muted fw-normal">Nota ({{ number_format($stats['orderCount'] ?? 0) }} DO)</span>
                        </div>
                    </div>
                    <div class="stat-icon-wrapper bg-primary-subtle text-primary">
                        <i class="mdi mdi-receipt-text-outline"></i>
                    </div>
                </div>
                <div class="stat-desc text-truncate">
                    <i class="mdi mdi-calculator me-1 text-primary"></i>Total Tagihan: <strong>Rp {{ number_format($stats['totalBilling'] ?? 0, 0, ',', '.') }}</strong>
                </div>
            </div>
        </div>

        <!-- Card 2: Belum Bayar -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card" data-filter="unpaid" title="Klik untuk filter Belum Bayar">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Belum Bayar</div>
                        <div class="stat-value text-danger">
                            {{ number_format($stats['unpaidCount'] ?? 0) }}
                            <span class="fs-13 text-muted fw-normal">Nota</span>
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
            <div class="stat-card" data-filter="partial" title="Klik untuk filter Belum Lunas (DP/Cicilan)">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Belum Lunas (DP)</div>
                        <div class="stat-value text-warning">
                            {{ number_format($stats['partialCount'] ?? 0) }}
                            <span class="fs-13 text-muted fw-normal">Nota</span>
                        </div>
                    </div>
                    <div class="stat-icon-wrapper bg-warning-subtle text-warning">
                        <i class="mdi mdi-progress-clock"></i>
                    </div>
                </div>
                <div class="stat-desc text-warning-emphasis text-truncate">
                    <i class="mdi mdi-clock-outline me-1"></i>Terbayar sebagian
                </div>
            </div>
        </div>

        <!-- Card 4: Sisa Tagihan -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Sisa Tagihan</div>
                        <div class="stat-value text-info">
                            Rp {{ number_format($stats['totalRemaining'] ?? 0, 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="stat-icon-wrapper bg-info-subtle text-info">
                        <i class="mdi mdi-cash-check"></i>
                    </div>
                </div>
                <div class="stat-desc text-truncate text-success">
                    <i class="mdi mdi-check-circle-outline me-1"></i>Terbayar: <strong>Rp {{ number_format($stats['totalPaid'] ?? 0, 0, ',', '.') }}</strong>
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
                    <span>Semua Nota</span>
                    <span class="badge-pill-count">{{ number_format($stats['totalCount'] ?? 0) }}</span>
                </button>
                <button type="button" class="filter-pill-btn" data-status="unpaid">
                    <i class="mdi mdi-close-circle-outline text-danger"></i>
                    <span>Belum Bayar</span>
                    <span class="badge-pill-count">{{ number_format($stats['unpaidCount'] ?? 0) }}</span>
                </button>
                <button type="button" class="filter-pill-btn" data-status="partial">
                    <i class="mdi mdi-clock-outline text-warning"></i>
                    <span>Belum Lunas (DP)</span>
                    <span class="badge-pill-count">{{ number_format($stats['partialCount'] ?? 0) }}</span>
                </button>
            </div>
        </div>

        <!-- Selection Command Bar -->
        <div class="selection-command-bar d-none" id="selection-bar" aria-live="polite">
            <div>
                <strong id="selected-headline">0 nota terpilih</strong>
                <div class="selection-facts mt-1">
                    <span id="selection-order-fact">0 DO</span>
                    <span id="selection-remaining-fact">Sisa Rp 0</span>
                    <span id="selection-bank-fact" class="d-none"></span>
                </div>
            </div>
            <div class="selection-actions">
                <button type="button" class="btn btn-outline-light btn-sm" id="btn-clear-selection">
                    <i class="mdi mdi-close me-1" aria-hidden="true"></i>Hapus Pilihan
                </button>
                <button type="button" class="btn btn-success btn-sm fw-semibold" id="btn-batch-pay">
                    <i class="mdi mdi-bank-transfer-in me-1" aria-hidden="true"></i>Bayar Nota (<span id="payment-selection-count">0</span>)
                </button>
            </div>
        </div>

        <div class="card-body p-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <h6 class="fw-bold text-dark mb-1">Daftar Nota Tagihan Belum Lunas</h6>
                    <div class="text-muted fs-12">
                        <i class="mdi mdi-information-outline me-1 text-primary"></i>
                        Centang satu atau beberapa nota lalu klik <strong>Bayar Nota</strong> untuk pembayaran DP/cicilan/pelunasan. Gunakan tombol aksi pada baris untuk cetak nota, lihat rincian nota, atau membatalkan nota.
                    </div>
                </div>
            </div>
            <div class="table-scroll-wrap custom-scrollbar">
                <table class="table table-striped nowrap direct-payment-table" id="dt">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 40px;">
                                <input class="form-check-input" type="checkbox" id="check-all" title="Pilih semua data pada halaman ini">
                            </th>
                            <th class="text-center" style="width: 130px;">Aksi</th>
                            <th class="text-center" style="width: 45px;">No</th>
                            <th>No Nota</th>
                            <th>Tanggal Nota</th>
                            <th>Customer</th>
                            <th>Nopol Armada</th>
                            <th class="text-end">Ongkos</th>
                            <th class="text-end">Biaya Tamb.</th>
                            <th class="text-end">PPN</th>
                            <th class="text-end">PPh</th>
                            <th class="text-end">Claim</th>
                            <th class="text-end">Total Tagihan</th>
                            <th class="text-end">Terbayar</th>
                            <th class="text-end">Sisa</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@include('direct-payment.partials.modals')
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

{{-- Urutan SweetAlert: sweetalert2 dulu, lalu sweetalert v1 --}}
<script src="{{ asset('assets/js/sweet-alert/sweetalert2.min.js') }}"></script>
<script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}?v=20260923-2"></script>

<script>
    (function syncSweetAlertTheme() {
        if (window.swal && typeof window.swal.setDefaults === 'function') {
            window.swal.setDefaults({ className: 'swal-dark-mode' });
        }

        const style = document.createElement('style');
        style.textContent = `
            html[data-bs-theme="dark"] .swal-modal.swal-dark-mode,
            html[data-bs-theme="dark"] .swal-modal.swal-dark-mode * {
                color: #e4e9f0 !important;
                -webkit-text-fill-color: #e4e9f0 !important;
            }
            html[data-bs-theme="dark"] .swal-modal.swal-dark-mode .swal-title,
            html[data-bs-theme="dark"] .swal-modal.swal-dark-mode .swal-text {
                color: #f8fafc !important;
                -webkit-text-fill-color: #f8fafc !important;
            }
        `;
        document.head.appendChild(style);

        const sync = function() {
            let savedTheme = null;
            try {
                savedTheme = JSON.parse(localStorage.getItem('__CONFIG__') || '{}').theme;
            } catch (error) {
                savedTheme = null;
            }

            const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark' || savedTheme === 'dark';
            const color = isDark ? '#f8fafc' : '';
            const secondaryColor = isDark ? '#e4e9f0' : '';

            document.querySelectorAll('.swal-modal').forEach(function(modal) {
                modal.style.setProperty('color', secondaryColor, 'important');
                modal.querySelectorAll('.swal-title, .swal-text').forEach(function(element) {
                    element.style.setProperty('color', color, 'important');
                    element.style.setProperty('-webkit-text-fill-color', color, 'important');
                });
                modal.querySelectorAll('.swal-content, .swal-footer').forEach(function(element) {
                    element.style.setProperty('color', secondaryColor, 'important');
                    element.style.setProperty('-webkit-text-fill-color', secondaryColor, 'important');
                });
            });

            document.querySelectorAll('.swal2-popup').forEach(function(popup) {
                const background = getComputedStyle(popup).backgroundColor.match(/\d+(?:\.\d+)?/g) || [];
                const hasDarkClass = popup.classList.contains('swal-dark-popup') || popup.classList.contains('swal-readable-dark');
                const hasDarkSurface = background.length >= 3 && (
                    Number(background[0]) + Number(background[1]) + Number(background[2]) < 260
                );
                const popupIsDark = isDark || hasDarkClass || hasDarkSurface;
                const popupColor = popupIsDark ? '#f8fafc' : '';

                if (!popupIsDark) return;

                popup.classList.add('swal-readable-dark');
                popup.style.setProperty('--swal2-color', popupColor, 'important');
                popup.style.setProperty('--swal2-background', '#1f2028', 'important');
                popup.style.setProperty('background', '#1f2028', 'important');
                popup.style.setProperty('background-color', '#1f2028', 'important');
                popup.style.setProperty('color', popupColor, 'important');
                popup.style.setProperty('-webkit-text-fill-color', popupColor, 'important');
                popup.querySelectorAll('h2.swal2-title, .swal2-html-container, .swal2-footer').forEach(function(element) {
                    element.classList.add('swal-readable-dark-text');
                    element.style.setProperty('color', popupColor, 'important');
                    element.style.setProperty('-webkit-text-fill-color', popupColor, 'important');
                    element.style.setProperty('opacity', '1', 'important');
                });
            });
        };

        new MutationObserver(sync).observe(document.body, { childList: true, subtree: true });
        new MutationObserver(sync).observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['data-bs-theme']
        });
        sync();
    }());
</script>

{{-- Flash message server → SweetAlert2 --}}
@include('direct-payment.partials.flash-swal')

<script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
<script src="{{ asset('assets/js/select2/select2-custom.js') }}"></script>
<script src="{{ asset('assets/js/helper.js') }}"></script>

<script>
    // =====================================================================
    // STATE GLOBAL
    // =====================================================================
    let dataTableInstance;
    let currentStatusFilter = 'all';

    // Pilihan checkbox nota untuk pembayaran batch (key = notaNumber)
    const selectedPayNotas = {};

    // State modal bayar nota (batch)
    let batchSubmissionInFlight = false;
    let batchRequestKey = null;

    // =====================================================================
    // HELPER
    // =====================================================================
    function formatNumber(value) {
        return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Math.round(Number(value) || 0));
    }

    function formatCurrency(value) {
        return 'Rp ' + formatNumber(value);
    }

    function formatRate(value) {
        return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 4 }).format(Number(value) || 0);
    }

    function escapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function(character) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;',
            }[character];
        });
    }

    function formatNotaDate(value) {
        if (!value) return '-';
        const date = new Date(value);
        if (isNaN(date.getTime())) return String(value);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        return day + '-' + month + '-' + date.getFullYear();
    }

    function localDateValue() {
        const date = new Date();
        return date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0');
    }

    function generateRequestKey() {
        if (window.crypto && typeof window.crypto.randomUUID === 'function') {
            return window.crypto.randomUUID();
        }
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(character) {
            const random = Math.floor(Math.random() * 16);
            const value = character === 'x' ? random : (random & 0x3) | 0x8;
            return value.toString(16);
        });
    }

    function paymentStatusLabel(status) {
        if (status === 'partial') return 'Dibayar sebagian';
        if (status === 'paid') return 'Lunas';
        return 'Belum dibayar';
    }

    function swalLoader(title, html) {
        return Swal.fire({
            title: title,
            html: html,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: function() {
                Swal.showLoading();
            },
        });
    }

    // =====================================================================
    // MODAL DETAIL NOTA (multi-DO)
    // =====================================================================
    function paintNotaStatusTone(statusCode) {
        let tone = 'neutral';
        if (statusCode === 'paid') tone = 'paid';
        else if (statusCode === 'partial') tone = 'partial';
        else if (statusCode === 'pending') tone = 'pending';
        $('#nota-detail-status-pill').attr('data-tone', tone);
    }

    function notaOrderStatusBadge(statusCode, label) {
        let cls = 'bg-secondary-subtle text-secondary border border-secondary-subtle';
        if (statusCode === 'paid') {
            cls = 'bg-success-subtle text-success border border-success-subtle';
        } else if (statusCode === 'partial') {
            cls = 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
        } else if (statusCode === 'unpaid') {
            cls = 'bg-danger-subtle text-danger border border-danger-subtle';
        }
        return '<span class="badge rounded-pill ' + cls + ' fs-11 fw-semibold">' + escapeHtml(label || '-') + '</span>';
    }

    function showNotaDetailModal(orderCode) {
        $('#nota-detail-nota-number').text('-');
        $('#nota-detail-status').val('Memuat...');
        paintNotaStatusTone('neutral');
        $('#nota-detail-tagihan, #nota-detail-terbayar, #nota-detail-sisa, #nota-detail-ppn, #nota-detail-pph, #nota-detail-claim').text('-');
        $('#nota-detail-customer, #nota-detail-tanggal, #nota-detail-order-count, #nota-detail-bank').text('-');
        $('#nota-detail-orders-body').html('<tr><td colspan="15" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>Memuat daftar order...</td></tr>');
        $('#nota-detail-orders-foot').html('');
        $('#nota-detail-history-body').html('<tr><td colspan="6" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>Memuat riwayat pembayaran...</td></tr>');

        $('#nota-detail-modal').modal('show');

        $.get("{{ url('ajax/direct-payment-nota-detail') }}/" + encodeURIComponent(orderCode), function(data) {
            if (!data || !data.nota_number) {
                $('#nota-detail-modal').modal('hide');
                Swal.fire({
                    title: 'Gagal!',
                    text: 'Data nota tidak ditemukan.',
                    icon: 'error',
                    confirmButtonText: 'Mengerti',
                });
                return;
            }

            const statusCode = String(data.payment_status || 'pending').toLowerCase();
            $('#nota-detail-nota-number').text(data.nota_number);
            $('#nota-detail-status').val(paymentStatusLabel(statusCode));
            paintNotaStatusTone(statusCode);

            const totals = data.totals || {};
            $('#nota-detail-tagihan').text(formatCurrency(totals.grand_total));
            $('#nota-detail-terbayar').text(formatCurrency(totals.payment));
            $('#nota-detail-sisa').text(formatCurrency(totals.remaining));
            $('#nota-detail-ppn').text((data.ppn_percent !== null && data.ppn_percent !== undefined ? formatRate(data.ppn_percent) + '% · ' : '') + formatCurrency(totals.ppn));
            $('#nota-detail-pph').text((data.pph_percent !== null && data.pph_percent !== undefined ? formatRate(data.pph_percent) + '% · ' : '') + formatCurrency(totals.pph));
            $('#nota-detail-claim').text(formatCurrency(totals.claim));
            $('#nota-detail-customer').text(data.customer_name || '-');
            $('#nota-detail-tanggal').text(data.nota_date || '-');
            $('#nota-detail-order-count').text(formatNumber(data.order_count) + ' DO');

            let bankText = '-';
            if (data.user_bank) {
                bankText = (data.user_bank.name || 'Bank') + ' · ' + (data.user_bank.account_number || '-') + ' a/n ' + (data.user_bank.account_name || '-');
            }
            $('#nota-detail-bank').text(bankText);

            const orders = Array.isArray(data.orders) ? data.orders : [];
            if (orders.length > 0) {
                let html = '';
                orders.forEach(function(order) {
                    html += '<tr>' +
                        '<td class="font-monospace fw-semibold text-primary">' + escapeHtml(order.code || '-') + '</td>' +
                        '<td class="text-nowrap">' + escapeHtml(order.order_date || '-') + '</td>' +
                        '<td class="font-monospace">' + escapeHtml(order.shipment_number || '-') + '</td>' +
                        '<td class="font-monospace">' + escapeHtml(order.plate_number || '-') + '</td>' +
                        '<td>' + escapeHtml(order.driver_name || '-') + '</td>' +
                        '<td class="text-muted small">' + escapeHtml(order.rute || '-') + '</td>' +
                        '<td class="text-end font-monospace">' + formatNumber(order.cost) + '</td>' +
                        '<td class="text-end font-monospace">' + formatNumber(order.additional_cost) + '</td>' +
                        '<td class="text-end font-monospace">' + formatNumber(order.ppn) + '</td>' +
                        '<td class="text-end font-monospace">' + formatNumber(order.pph) + '</td>' +
                        '<td class="text-end font-monospace">' + formatNumber(order.claim) + '</td>' +
                        '<td class="text-end font-monospace fw-bold">' + formatNumber(order.grand_total) + '</td>' +
                        '<td class="text-end font-monospace text-success fw-semibold">' + formatNumber(order.payment) + '</td>' +
                        '<td class="text-end font-monospace text-danger">' + formatNumber(order.remaining) + '</td>' +
                        '<td class="text-center">' + notaOrderStatusBadge(order.status_code, order.status_label) + '</td>' +
                    '</tr>';
                });
                $('#nota-detail-orders-body').html(html);

                $('#nota-detail-orders-foot').html(
                    '<tr class="table-light fw-bold">' +
                        '<td colspan="6" class="text-end text-uppercase fs-11">Total</td>' +
                        '<td class="text-end font-monospace">' + formatNumber(totals.cost) + '</td>' +
                        '<td class="text-end font-monospace">' + formatNumber(totals.additional_cost) + '</td>' +
                        '<td class="text-end font-monospace">' + formatNumber(totals.ppn) + '</td>' +
                        '<td class="text-end font-monospace">' + formatNumber(totals.pph) + '</td>' +
                        '<td class="text-end font-monospace">' + formatNumber(totals.claim) + '</td>' +
                        '<td class="text-end font-monospace text-dark">' + formatNumber(totals.grand_total) + '</td>' +
                        '<td class="text-end font-monospace text-success">' + formatNumber(totals.payment) + '</td>' +
                        '<td class="text-end font-monospace text-danger">' + formatNumber(totals.remaining) + '</td>' +
                        '<td></td>' +
                    '</tr>'
                );
            } else {
                $('#nota-detail-orders-body').html('<tr><td colspan="15" class="text-center py-4 text-muted"><i class="mdi mdi-file-document-outline fs-3 d-block mb-1"></i>Belum ada order pada nota ini.</td></tr>');
                $('#nota-detail-orders-foot').html('');
            }

            const histories = Array.isArray(data.histories) ? data.histories : [];
            if (histories.length > 0) {
                let historyHtml = '';
                histories.forEach(function(item) {
                    let typeBadge = item.type === 'Full'
                        ? '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill fs-11">Full</span>'
                        : '<span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill fs-11">DP</span>';

                    historyHtml += '<tr>' +
                        '<td class="text-nowrap">' + escapeHtml(item.date || '-') + '</td>' +
                        '<td class="text-center">' + typeBadge + '</td>' +
                        '<td><span class="text-dark small fw-semibold">' + escapeHtml(item.bank || '-') + '</span></td>' +
                        '<td><span class="text-muted small">' + escapeHtml(item.description || '-') + '</span></td>' +
                        '<td class="text-center text-nowrap">' + escapeHtml(String(item.order_count || 0)) + ' DO</td>' +
                        '<td class="text-end fw-bold text-success font-monospace">Rp ' + formatNumber(item.total) + '</td>' +
                    '</tr>';
                });
                $('#nota-detail-history-body').html(historyHtml);
            } else {
                $('#nota-detail-history-body').html('<tr><td colspan="6" class="text-center py-4 text-muted"><i class="mdi mdi-credit-card-off fs-3 d-block mb-1"></i>Belum ada riwayat pembayaran</td></tr>');
            }
        }).fail(function() {
            $('#nota-detail-modal').modal('hide');
            Swal.fire({
                title: 'Gagal!',
                text: 'Rincian nota gagal dimuat. Silakan coba lagi.',
                icon: 'error',
                confirmButtonText: 'Mengerti',
            });
        });
    }

    // =====================================================================
    // SELEKSI CHECKBOX NOTA
    // =====================================================================
    function readPaymentCheckbox(checkbox) {
        return {
            notaNumber: String(checkbox.attr('data-nota-number') || ''),
            notaDate: String(checkbox.attr('data-nota-date') || ''),
            customerName: String(checkbox.attr('data-customer-name') || '-'),
            customerCode: String(checkbox.attr('data-customer-code') || ''),
            orderCodes: String(checkbox.attr('data-order-codes') || '').split(',').map(c => c.trim()).filter(Boolean),
            orderCount: Number(checkbox.attr('data-order-count') || 0),
            paymentStatus: String(checkbox.attr('data-payment-status') || 'pending'),
            billingAmount: Math.round(Number(checkbox.attr('data-billing-amount') || 0)),
            paidAmount: Math.round(Number(checkbox.attr('data-paid-amount') || 0)),
            remainingAmount: Math.round(Number(checkbox.attr('data-remaining-amount') || 0)),
            userBankCode: String(checkbox.attr('data-user-bank-code') || ''),
            userBankLabel: String(checkbox.attr('data-user-bank-label') || ''),
        };
    }

    function selectedPaymentItems() {
        return Object.values(selectedPayNotas);
    }

    function calculateSelectedPaymentTotals() {
        return selectedPaymentItems().reduce(function(totals, item) {
            totals.billing += item.billingAmount;
            totals.paid += item.paidAmount;
            totals.remaining += item.remainingAmount;
            totals.orderCount += item.orderCount || item.orderCodes.length;
            return totals;
        }, { billing: 0, paid: 0, remaining: 0, orderCount: 0 });
    }

    /**
     * Rekening nota mengikuti pilihan bank saat generate nota
     * (menu Order Menunggu Nota) — user tidak perlu memilih lagi di sini.
     * Status:
     *   ok       → semua nota memakai satu rekening yang sama
     *   missing  → ada nota tanpa rekening (data lama)
     *   conflict → nota terpilih memakai rekening berbeda
     *   empty    → belum ada nota terpilih
     */
    function selectedPaymentBank() {
        const items = selectedPaymentItems();
        if (items.length === 0) return { status: 'empty' };

        const missing = items.filter(item => !item.userBankCode);
        if (missing.length > 0) {
            return { status: 'missing', missingCount: missing.length, firstNota: missing[0].notaNumber };
        }

        const codes = [...new Set(items.map(item => item.userBankCode))];
        if (codes.length > 1) {
            return { status: 'conflict', codes: codes };
        }

        return {
            status: 'ok',
            code: codes[0],
            label: items[0].userBankLabel || '-',
        };
    }

    function syncSelectAllState() {
        const checkboxes = $('.row-payment-checkbox:visible:not(:disabled)');
        const checkedCount = checkboxes.filter(':checked').length;
        const selectAll = $('#check-all');

        selectAll.prop('checked', checkboxes.length > 0 && checkedCount === checkboxes.length);
        selectAll.prop('indeterminate', checkedCount > 0 && checkedCount < checkboxes.length);
    }

    function restoreSelectedCheckboxes() {
        $('.row-payment-checkbox').each(function() {
            const checkbox = $(this);
            const notaNumber = String(checkbox.attr('data-nota-number') || '');
            const selected = !!selectedPayNotas[notaNumber];
            checkbox.prop('checked', selected);
            checkbox.closest('tr').toggleClass('table-active', selected);

            if (selected) {
                const refreshed = readPaymentCheckbox(checkbox);
                refreshed.allocationAmount = selectedPayNotas[notaNumber].allocationAmount;
                selectedPayNotas[notaNumber] = refreshed;
            }
        });

        syncSelectAllState();
    }

    function updateSelectionUI() {
        const payNotas = selectedPaymentItems();
        const totals = calculateSelectedPaymentTotals();
        const bank = selectedPaymentBank();
        const count = payNotas.length;

        $('#selected-headline').text(count + ' nota terpilih');
        $('#selection-order-fact').text(totals.orderCount + ' DO');
        $('#selection-remaining-fact').text('Sisa ' + formatCurrency(totals.remaining));
        $('#payment-selection-count').text(count);

        const bankFact = $('#selection-bank-fact');
        if (bankFact.length > 0) {
            if (bank.status === 'ok') {
                bankFact.text(bank.label).removeAttr('title').removeAttr('data-bs-original-title').removeClass('d-none');
            } else if (bank.status === 'conflict') {
                bankFact.text('Rekening nota berbeda — bayar per kelompok rekening').attr('title', 'Nota terpilih menggunakan rekening bank yang berbeda.').removeClass('d-none');
            } else if (bank.status === 'missing') {
                bankFact.text('Ada nota tanpa rekening bank').attr('title', 'Nota ' + bank.firstNota + ' belum memiliki rekening. Batalkan nota lalu generate ulang dari menu Order Menunggu Nota.').removeClass('d-none');
            } else {
                bankFact.text('').removeAttr('title').removeAttr('data-bs-original-title').addClass('d-none');
            }
        }

        $('#selection-bar').toggleClass('d-none', count === 0);
        $('#btn-batch-pay').prop('disabled', count === 0 || totals.remaining < 1 || bank.status !== 'ok');

        syncSelectAllState();
    }

    function clearSelection() {
        Object.keys(selectedPayNotas).forEach(function(key) { delete selectedPayNotas[key]; });
        $('.row-payment-checkbox').prop('checked', false).closest('tr').removeClass('table-active');
        $('#check-all').prop('checked', false).prop('indeterminate', false);
        updateSelectionUI();
    }

    // =====================================================================
    // BAYAR NOTA (batch pembayaran DP / cicilan / pelunasan)
    // =====================================================================
    /**
     * Tampilkan rekening nota (read-only). Bank sudah dipilih saat generate
     * nota di menu Order Menunggu Nota — tidak dipilih lagi di halaman ini.
     */
    function renderBatchBankInfo() {
        const bank = selectedPaymentBank();
        const info = $('#batchBankInfo');
        const status = $('#batchBankStatus');

        if (bank.status === 'ok') {
            $('#batchUserBankCode').val(bank.code);
            info.html('<i class="mdi mdi-bank-outline me-1" aria-hidden="true"></i><span>' + escapeHtml(bank.label) + '</span>').removeClass('payment-bank-display-invalid');
            status.text('Mengikuti rekening yang dipilih saat generate nota.').toggleClass('text-danger', false).toggleClass('text-success', true);
            return true;
        }

        $('#batchUserBankCode').val('');
        if (bank.status === 'conflict') {
            info.html('<i class="mdi mdi-alert-outline me-1" aria-hidden="true"></i><span>Rekening nota berbeda</span>').addClass('payment-bank-display-invalid');
            status.text('Nota terpilih menggunakan rekening berbeda — bayar per kelompok rekening.').toggleClass('text-danger', true).toggleClass('text-success', false);
        } else if (bank.status === 'missing') {
            info.html('<i class="mdi mdi-alert-outline me-1" aria-hidden="true"></i><span>Nota tanpa rekening bank</span>').addClass('payment-bank-display-invalid');
            status.text('Nota ' + bank.firstNota + ' belum memiliki rekening. Batalkan nota lalu generate ulang dari menu Order Menunggu Nota.').toggleClass('text-danger', true).toggleClass('text-success', false);
        } else {
            info.html('<span class="text-muted">-</span>').removeClass('payment-bank-display-invalid');
            status.text('Pilih nota terlebih dahulu.').toggleClass('text-danger', false).toggleClass('text-success', false);
        }

        return false;
    }

    function renderBatchPaymentAllocations() {
        const body = $('#batchPaymentAllocationBody').empty();
        const fullPayment = $('#batchPaymentModeFull').is(':checked');
        const items = selectedPaymentItems().sort(function(first, second) {
            return String(first.notaDate).localeCompare(String(second.notaDate));
        });

        items.forEach(function(item) {
            if (fullPayment || item.allocationAmount === undefined) {
                item.allocationAmount = item.remainingAmount;
            }

            const row = $('<tr>').attr('data-nota-number', item.notaNumber);
            const identity = $('<td>')
                .append($('<strong>').text(item.notaNumber))
                .append($('<span>', { class: 'allocation-vendor' }).text(item.customerName + ' · ' + item.orderCodes.length + ' DO · ' + formatNotaDate(item.notaDate)));
            const status = $('<td>', { class: 'text-center' }).append($('<span>', {
                class: 'badge ' + (item.paymentStatus === 'partial' ? 'text-bg-info' : 'text-bg-warning')
            }).text(paymentStatusLabel(item.paymentStatus)));
            const before = $('<td>', { class: 'text-end payment-money' }).text(formatCurrency(item.remainingAmount));
            const input = $('<input>', {
                type: 'text',
                inputmode: 'numeric',
                autocomplete: 'off',
                class: 'form-control form-control-sm allocation-amount-input',
                'aria-label': 'Nominal pembayaran nota ' + item.notaNumber,
            })
                .attr('data-nota-number', item.notaNumber)
                .prop('readonly', fullPayment)
                .val(formatNumber(item.allocationAmount));
            const inputGroup = $('<div>', { class: 'input-group input-group-sm' })
                .append($('<span>', { class: 'input-group-text' }).text('Rp'))
                .append(input);
            const amountCell = $('<td>').append(inputGroup).append($('<span>', { class: 'allocation-message text-muted' }));
            const after = $('<td>', { class: 'text-end payment-money allocation-after' });

            row.append(identity, status, before, amountCell, after);
            body.append(row);
        });

        $('#batchPaymentAllocationCount').text(items.length + ' nota');
        updateBatchPaymentSummary();
    }

    function allocationValidation(item) {
        const amount = Math.round(Number(item.allocationAmount) || 0);
        if (amount < 1) return 'Nominal minimal Rp 1.';
        if (amount > item.remainingAmount) return 'Melebihi sisa ' + formatCurrency(item.remainingAmount) + '.';
        return '';
    }

    function updateBatchPaymentSummary() {
        const items = selectedPaymentItems();
        let totalPayment = 0;
        let totalAfter = 0;
        let firstError = '';

        items.forEach(function(item) {
            item.allocationAmount = Math.round(Number(item.allocationAmount) || 0);
            totalPayment += item.allocationAmount;
            totalAfter += Math.max(0, item.remainingAmount - item.allocationAmount);

            const row = $('#batchPaymentAllocationBody tr').filter(function() {
                return $(this).attr('data-nota-number') === item.notaNumber;
            });
            const error = allocationValidation(item);
            const input = row.find('.allocation-amount-input');
            const message = row.find('.allocation-message');

            input.toggleClass('is-invalid', error !== '').attr('aria-invalid', error !== '' ? 'true' : 'false');
            message.text(error || 'Maks. ' + formatCurrency(item.remainingAmount))
                .toggleClass('text-danger', error !== '')
                .toggleClass('text-muted', error === '');
            row.find('.allocation-after').text(formatCurrency(Math.max(0, item.remainingAmount - item.allocationAmount)));

            if (!firstError && error) {
                firstError = item.notaNumber + ': ' + error;
            }
        });

        const totals = calculateSelectedPaymentTotals();
        const customerCount = new Set(items.map(item => item.customerCode || item.customerName)).size;
        const bank = selectedPaymentBank();
        const bankReady = bank.status === 'ok';
        const dateValid = $('#batchDate').val() !== '';
        const ready = items.length > 0 && !firstError && totalPayment > 0 && bankReady && dateValid && !batchSubmissionInFlight;

        $('#batchPaymentGrandTotal').text(formatCurrency(totalPayment));
        $('#batchPaymentAfterSummary').text('Sisa setelah pembayaran: ' + formatCurrency(totalAfter));
        $('#batchPaymentFactNotas').text(items.length);
        $('#batchPaymentFactCustomers').text(customerCount);
        $('#batchPaymentFactOrders').text(totals.orderCount);
        $('#batchPaymentFactRemaining').text(formatCurrency(totals.remaining));
        $('#batchSubmitLabel').text(totalPayment > 0 ? 'Bayar ' + formatCurrency(totalPayment) : 'Proses Pembayaran');
        $('#batchPaymentAllocationError').toggleClass('d-none', firstError === '').text(firstError);
        $('#batchDate').toggleClass('is-invalid', !dateValid).attr('aria-invalid', dateValid ? 'false' : 'true');
        $('#batchBankInfo').toggleClass('payment-bank-display-invalid', !bankReady);
        $('#submitBatchPaymentBtn').prop('disabled', !ready);

        let hint = 'Siap diproses sebagai satu batch pembayaran.';
        if (firstError) hint = 'Perbaiki nominal pembayaran yang ditandai.';
        else if (bank.status === 'conflict') hint = 'Nota terpilih menggunakan rekening berbeda — bayar per kelompok rekening.';
        else if (bank.status === 'missing') hint = 'Ada nota tanpa rekening bank — batalkan & generate ulang nota terkait.';
        else if (!dateValid) hint = 'Isi tanggal pembayaran.';
        $('#batchSubmitHint').text(hint);
    }

    function applyBatchPaymentMode() {
        const fullPayment = $('#batchPaymentModeFull').is(':checked');

        selectedPaymentItems().forEach(function(item) {
            if (fullPayment) item.allocationAmount = item.remainingAmount;
        });

        $('.allocation-amount-input').each(function() {
            const input = $(this);
            const item = selectedPayNotas[String(input.attr('data-nota-number') || '')];
            input.prop('readonly', fullPayment);
            if (item) input.val(formatNumber(item.allocationAmount));
        });

        updateBatchPaymentSummary();
    }

    function setBatchPaymentSubmitting(isSubmitting) {
        batchSubmissionInFlight = isSubmitting;
        $('#batchSubmitSpinner').toggleClass('d-none', !isSubmitting);
        $('#batchSubmitIcon').toggleClass('d-none', isSubmitting);
        $('#batch-payment-modal [data-bs-dismiss]').prop('disabled', isSubmitting);
        $('#batchPaymentModeFull, #batchPaymentModeCustom, #batchDate, #batchDescription, .allocation-amount-input').prop('disabled', isSubmitting);
        updateBatchPaymentSummary();
    }

    function batchPaymentPayload() {
        return {
            requestKey: batchRequestKey,
            payments: selectedPaymentItems().map(function(item) {
                return {
                    nota_number: item.notaNumber,
                    amount: Math.round(Number(item.allocationAmount) || 0),
                    expected_remaining: item.remainingAmount,
                };
            }),
            date: $('#batchDate').val(),
            userBankCode: $('#batchUserBankCode').val(),
            description: $('#batchDescription').val().trim(),
        };
    }

    function openBatchPaymentModal() {
        const items = selectedPaymentItems();
        const totals = calculateSelectedPaymentTotals();

        if (items.length === 0 || totals.remaining < 1) {
            Swal.fire({
                title: 'Pilih nota',
                text: 'Pilih minimal satu nota yang masih memiliki sisa tagihan.',
                icon: 'warning',
            });
            return;
        }

        const bank = selectedPaymentBank();
        if (bank.status === 'conflict') {
            Swal.fire({
                title: 'Rekening nota berbeda',
                text: 'Nota terpilih menggunakan rekening bank yang berbeda. Batalkan pilihan lalu bayar nota per kelompok rekening.',
                icon: 'warning',
            });
            return;
        }
        if (bank.status === 'missing') {
            Swal.fire({
                title: 'Nota tanpa rekening bank',
                text: 'Nota ' + bank.firstNota + ' belum memiliki rekening. Batalkan nota lalu generate ulang dari menu Order Menunggu Nota.',
                icon: 'warning',
            });
            return;
        }

        batchRequestKey = generateRequestKey();
        items.forEach(function(item) {
            item.allocationAmount = item.remainingAmount;
        });
        $('#batchPaymentModeFull').prop('checked', true);
        $('#batchDate').val(localDateValue());
        $('#batchDescription').val('');
        $('#batchDescriptionCount').text('0/255');
        renderBatchBankInfo();
        renderBatchPaymentAllocations();
        setBatchPaymentSubmitting(false);
        $('#batch-payment-modal').modal('show');
    }

    $('input[name="paymentMode"]').on('change', applyBatchPaymentMode);

    $(document).on('input', '.allocation-amount-input', function() {
        const input = $(this);
        const notaNumber = String(input.attr('data-nota-number') || '');
        const item = selectedPayNotas[notaNumber];
        const numericValue = String(input.val() || '').replace(/\D/g, '');

        if (!item) return;

        item.allocationAmount = numericValue === '' ? 0 : Number(numericValue);
        input.val(numericValue === '' ? '' : formatNumber(item.allocationAmount));
        updateBatchPaymentSummary();
    });

    $('#batchDate').on('change', updateBatchPaymentSummary);
    $('#batchDescription').on('input', function() {
        $('#batchDescriptionCount').text(String($(this).val()).length + '/255');
    });

    $('#batch-payment-form').on('submit', function(event) {
        event.preventDefault();
        updateBatchPaymentSummary();

        if ($('#submitBatchPaymentBtn').prop('disabled') || batchSubmissionInFlight) return;

        const payload = batchPaymentPayload();
        const bank = selectedPaymentBank();
        const selectedBankText = bank.status === 'ok' ? bank.label : '-';
        const totalPayment = payload.payments.reduce((t, p) => t + p.amount, 0);
        const confirmationHtml = '<strong>' + escapeHtml(formatCurrency(totalPayment)) + '</strong> untuk '
            + payload.payments.length + ' nota.<br>Sumber dana: ' + escapeHtml(selectedBankText)
            + '<br>Tanggal: ' + escapeHtml(formatNotaDate(payload.date + 'T00:00:00'));

        Swal.fire({
            title: 'Konfirmasi pembayaran nota',
            html: confirmationHtml,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Proses Pembayaran',
            cancelButtonText: 'Periksa Lagi',
            confirmButtonColor: '#198754',
        }).then(function(result) {
            if (!result.isConfirmed) return;

            setBatchPaymentSubmitting(true);
            swalLoader('Memproses pembayaran', 'Jangan menutup halaman. Sistem sedang mengunci nota dan mencatat mutasi bank.');

            $.ajax({
                url: $('#batch-payment-form').attr('action'),
                type: 'POST',
                data: JSON.stringify(payload),
                contentType: 'application/json; charset=utf-8',
                dataType: 'json',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': $('#batch-payment-form input[name="_token"]').val(),
                },
                success: function(response) {
                    Swal.close();
                    setBatchPaymentSubmitting(false);
                    $('#batch-payment-modal').modal('hide');

                    const res = response.result || {};
                    const outcome = (res.fully_paid_count || 0) + ' nota lunas' + ((res.partial_count || 0) > 0 ? ' · ' + res.partial_count + ' nota masih sebagian' : '');
                    const successHtml = '<strong>' + escapeHtml(formatCurrency(res.payment_amount || 0)) + '</strong> berhasil dicatat.<br>'
                        + escapeHtml(outcome) + '<br>Kode: <strong>' + escapeHtml(res.batch_code || '-') + '</strong>';

                    Swal.fire({
                        title: 'Pembayaran berhasil',
                        html: successHtml,
                        icon: 'success',
                        confirmButtonText: 'Lihat Daftar Terbaru',
                        confirmButtonColor: '#198754',
                    }).then(function() {
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.close();
                    setBatchPaymentSubmitting(false);

                    const message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Pembayaran gagal diproses. Data tetap tersimpan di form dan dapat dicoba kembali.';
                    const isConflict = xhr.status === 409;

                    if (isConflict) {
                        $('#batch-payment-modal').modal('hide');
                        clearSelection();
                        dataTableInstance.ajax.reload(null, false);
                    }

                    Swal.fire({
                        title: isConflict ? 'Data nota berubah' : 'Pembayaran gagal',
                        text: message,
                        icon: isConflict ? 'warning' : 'error',
                        confirmButtonText: 'Tutup',
                    });
                }
            });
        });
    });

    // =====================================================================
    // BATAL PEMBAYARAN / BATAL NOTA
    // =====================================================================
    function confirmCancelPayment(orderCode, batchCode) {
        if (!batchCode) {
            Swal.fire({
                title: 'Tidak dapat dibatalkan',
                text: 'Kode batch pembayaran tidak tersedia. Transaksi lama tidak dibatalkan otomatis demi keamanan.',
                icon: 'warning',
            });
            return;
        }

        $('#delete-form').attr('action', "{{ url('direct-payment/order/payment') }}/" + encodeURIComponent(orderCode));
        $('#cancel-payment-batch-code').val(batchCode);
        Swal.fire({
            title: 'Batalkan batch pembayaran terakhir?',
            text: 'Batch dapat mencakup beberapa order dalam satu atau beberapa nota. Pembayaran dikembalikan untuk batch ' + batchCode + '.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Batalkan Batch',
            cancelButtonText: 'Kembali',
            confirmButtonColor: '#dc3545',
        }).then(function(result) {
            if (result.isConfirmed) {
                swalLoader('Membatalkan pembayaran', 'Sedang mengembalikan pembayaran dan memperbarui nota...');
                $('#delete-form').submit();
            }
        });
    }

    function confirmCancelNota(orderCode) {
        $('#cancel-nota-form').attr('action', "{{ url('direct-payment/order/cancel-nota') }}/" + encodeURIComponent(orderCode));
        Swal.fire({
            title: 'Batalkan nota?',
            text: 'Seluruh DO di dalam nota akan kembali ke menu Order Menunggu Nota.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Batalkan Nota',
            cancelButtonText: 'Kembali',
            confirmButtonColor: '#dc3545',
        }).then(function(result) {
            if (result.isConfirmed) {
                swalLoader('Membatalkan nota', 'Sedang mengembalikan order ke Order Menunggu Nota...');
                $('#cancel-nota-form').submit();
            }
        });
    }

    // =====================================================================
    // INIT
    // =====================================================================
    $(document).ready(function() {
        dataTableInstance = $('#dt').DataTable({
            "processing": true,
            "serverSide": true,
            "destroy": true,
            "scrollX": true,
            "autoWidth": false,
            "pageLength": 25,
            "ajax": {
                "url": "{{ route('dt.direct-payment.unpaid') }}",
                "data": function(d) {
                    d.status = currentStatusFilter;
                }
            },
            "columns": [
                { "data": 'select', "className": 'text-center align-middle', "orderable": false, "searchable": false },
                { "data": 'action', "className": 'text-center align-middle', "orderable": false, "searchable": false },
                { "data": 'DT_RowIndex', "className": 'text-center align-middle', "orderable": false, "searchable": false },
                { "data": 'code', "className": 'align-middle' },
                { "data": 'date', "className": 'align-middle text-center' },
                { "data": 'customer_name', "className": 'align-middle' },
                { "data": 'plate', "className": 'align-middle' },
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
                [4, 'desc']
            ],
            "drawCallback": function() {
                restoreSelectedCheckboxes();
                updateSelectionUI();

                if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                    let tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                    tooltipTriggerList.map(function(el) {
                        return new bootstrap.Tooltip(el);
                    });
                }
            },
            "language": {
                "processing": "Memuat data...",
                "search": "",
                "searchPlaceholder": "Cari no nota, customer, nopol armada...",
                "lengthMenu": "Tampilkan _MENU_ data",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                "infoEmpty": "Tidak ada data",
                "zeroRecords": "Tidak ditemukan data nota yang sesuai",
                "paginate": {
                    "next": "<i class='mdi mdi-chevron-right'></i>",
                    "previous": "<i class='mdi mdi-chevron-left'></i>"
                }
            }
        });

        // Filter status klik
        $('.filter-pill-btn').on('click', function() {
            $('.filter-pill-btn').removeClass('active');
            $(this).addClass('active');

            currentStatusFilter = $(this).data('status');
            dataTableInstance.ajax.reload();
        });

        // Metric Card klik
        $('.stat-card[data-filter]').on('click', function() {
            let filter = $(this).data('filter');
            if (filter) {
                $('.filter-pill-btn').removeClass('active');
                $(`.filter-pill-btn[data-status="${filter}"]`).addClass('active');
                currentStatusFilter = filter;
                dataTableInstance.ajax.reload();
            }
        });

        // Refresh Button
        $('#btn-refresh-table').on('click', function() {
            let icon = $('#refresh-icon');
            icon.addClass('mdi-spin');
            dataTableInstance.ajax.reload(function() {
                setTimeout(() => icon.removeClass('mdi-spin'), 400);
            });
        });

        // Check All
        $('#check-all').on('change', function() {
            let isChecked = $(this).is(':checked');
            $('.row-payment-checkbox:visible:not(:disabled)').each(function() {
                if ($(this).is(':checked') !== isChecked) {
                    $(this).prop('checked', isChecked).trigger('change');
                }
            });
        });

        // Row Checkbox
        $(document).on('change', '.row-payment-checkbox', function() {
            const checkbox = $(this);
            const item = readPaymentCheckbox(checkbox);
            if (!item.notaNumber) return;

            if (checkbox.is(':checked')) {
                selectedPayNotas[item.notaNumber] = item;
            } else {
                delete selectedPayNotas[item.notaNumber];
            }

            checkbox.closest('tr').toggleClass('table-active', checkbox.is(':checked'));
            updateSelectionUI();
        });

        // Clear Selection
        $('#btn-clear-selection').on('click', clearSelection);

        // Bayar Nota
        $('#btn-batch-pay').on('click', openBatchPaymentModal);

        // Tombol aksi baris nota
        $(document).on('click', '.js-dp-nota-detail', function() {
            showNotaDetailModal(String($(this).attr('data-order-code') || ''));
        });

        $(document).on('click', '.js-dp-payment-cancel', function() {
            confirmCancelPayment(
                String($(this).attr('data-order-code') || ''),
                String($(this).attr('data-batch-code') || '')
            );
        });

        $(document).on('click', '.js-dp-nota-cancel', function() {
            confirmCancelNota(String($(this).attr('data-order-code') || ''));
        });

        // Select2 untuk dropdown "Tampilkan _MENU_ data" milik DataTables
        $('#dt_wrapper .dataTables_length select').select2({
            minimumResultsForSearch: Infinity,
            width: '88px',
            dropdownAutoWidth: true,
        });

        updateSelectionUI();
    });
</script>
@endpush
