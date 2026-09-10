@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Pembayaran Langsung',
    'secondSegment' => 'Order Belum Lunas',
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
<link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/custom-select2.css') }}">

@include('direct-payment.partials.table-style')

<style>
    /* Bar perintah pilihan (mengikuti pola vendor unpaid, dengan nuansa direct-payment) */
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
        min-width: 1560px;
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
                        {{ number_format($stats['totalCount'] ?? 0) }} Unit
                    </span>
                </h4>
                <p class="text-muted mb-0 fs-12">
                    Pembayaran order non-DO &amp; nota multi-DO (DP, Cicilan &amp; Pelunasan) dengan PPN/PPh, claim, dan riwayat transaksi.
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
        <!-- Card 1: Total Unit -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card" data-filter="all" title="Klik untuk tampilkan semua unit">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Total Unit</div>
                        <div class="stat-value text-primary">
                            {{ number_format($stats['totalCount'] ?? 0) }}
                            <span class="fs-13 text-muted fw-normal">Unit</span>
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
            <div class="stat-card" data-filter="unpaid" title="Klik untuk filter Belum Bayar">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Belum Bayar</div>
                        <div class="stat-value text-danger">
                            {{ number_format($stats['unpaidCount'] ?? 0) }}
                            <span class="fs-13 text-muted fw-normal">Unit</span>
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
                            <span class="fs-13 text-muted fw-normal">Unit</span>
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

        <!-- Card 4: Nota Belum Lunas -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card" data-filter="nota" title="Klik untuk filter nota multi-DO">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Nota Belum Lunas</div>
                        <div class="stat-value text-info">
                            {{ number_format($stats['notaCount'] ?? 0) }}
                            <span class="fs-13 text-muted fw-normal">Nota</span>
                        </div>
                    </div>
                    <div class="stat-icon-wrapper bg-info-subtle text-info">
                        <i class="mdi mdi-receipt-text-outline"></i>
                    </div>
                </div>
                <div class="stat-desc text-info text-truncate">
                    <i class="mdi mdi-file-document-multiple-outline me-1"></i>Gabungan multi-DO per customer
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
                <button type="button" class="filter-pill-btn" data-status="nota">
                    <i class="mdi mdi-receipt-text-outline text-info"></i>
                    <span>Nota</span>
                    <span class="badge-pill-count">{{ number_format($stats['notaCount'] ?? 0) }}</span>
                </button>
            </div>
        </div>

        <div class="selection-command-bar d-none" id="selection-bar" aria-live="polite">
            <div>
                <strong id="selected-headline">0 terpilih</strong>
                <div class="selection-facts mt-1">
                    <span id="selection-order-fact">0 DO</span>
                    <span id="selection-nota-fact">0 nota</span>
                    <span id="selection-remaining-fact">Sisa Rp 0</span>
                </div>
            </div>
            <div class="selection-actions">
                <button type="button" class="btn btn-outline-light btn-sm" id="btn-clear-selection">
                    <i class="mdi mdi-close me-1" aria-hidden="true"></i>Hapus Pilihan
                </button>
                <button type="button" class="btn btn-warning btn-sm fw-semibold" id="btn-generate-nota">
                    <i class="mdi mdi-file-document-plus-outline me-1" aria-hidden="true"></i>Generate Nota (<span id="nota-selection-count">0</span>)
                </button>
                <button type="button" class="btn btn-success btn-sm fw-semibold" id="btn-batch-pay">
                    <i class="mdi mdi-bank-transfer-in me-1" aria-hidden="true"></i>Bayar Nota (<span id="payment-selection-count">0</span>)
                </button>
            </div>
        </div>

        <div class="card-body p-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <h6 class="fw-bold text-dark mb-1">Order &amp; Nota Belum Lunas</h6>
                    <div class="text-muted fs-12">
                        <i class="mdi mdi-information-outline me-1 text-primary"></i>
                        Centang beberapa DO milik <strong>customer yang sama</strong> lalu <strong>Generate Nota</strong> untuk menggabungkannya. Nota dibayar DP/cicilan/lunas lewat checkbox nota.
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
                            <th>Kode / No Nota</th>
                            <th>Tanggal</th>
                            <th>Customer</th>
                            <th>Nopol</th>
                            <th>Driver</th>
                            <th>No SJ</th>
                            <th>Rute</th>
                            <th class="text-end">Ongkos</th>
                            <th class="text-end">Biaya Tamb.</th>
                            <th class="text-end">PPN</th>
                            <th class="text-end">PPh</th>
                            <th class="text-end">Claim</th>
                            <th class="text-end">Total</th>
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

{{-- Urutan PENTING: sweetalert2 (v11, class) dulu, lalu sweetalert v1 (fungsi) belakangan.
    Keduanya menimpa global 'swal' — karena v1 dimuat terakhir, window.swal = fungsi v1
    (dipakai alur tunggal/generate-nota), sedangkan Swal = class v2 (dipakai batch/cancel).
    Kalau terbalik, v2 menimpa swal dengan class -> TypeError 'class constructors must be invoked with new'. --}}
<script src="{{ asset('assets/js/sweet-alert/sweetalert2.min.js') }}"></script>
<script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>

{{-- Flash message server → SweetAlert2 (harus setelah sweetalert2.min.js) --}}
@include('direct-payment.partials.flash-swal')

<script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
<script src="{{ asset('assets/js/select2/select2-custom.js') }}"></script>
<script src="{{ asset('assets/js/helper.js') }}"></script>

<script>
    // =====================================================================
    // STATE GLOBAL
    // =====================================================================
    let dataTableInstance;
    let currentCost = 0;
    let currentAdditionalCost = 0;
    let currentPaid = 0;
    let currentDetailOrderCode = null;
    let currentDetailNotaNumber = null;

    // Mode pajak: 'percent' (hitung dari subtotal) atau 'nominal' (isi langsung)
    let ppnMode = 'percent';
    let pphMode = 'percent';

    // Filter status aktif
    let currentStatusFilter = 'all';

    // Pilihan checkbox:
    // - selectedNotaOrders: order standalone untuk generate nota (key = orderCode)
    // - selectedPayNotas  : nota untuk pembayaran batch (key = notaNumber)
    const selectedNotaOrders = {};
    const selectedPayNotas = {};

    // State modal generate nota
    const notaModalState = { subtotal: 0 };

    // State modal bayar nota (batch)
    let batchBanksLoaded = false;
    let batchSubmissionInFlight = false;
    let batchRequestKey = null;
    let batchBankRequestSequence = 0;

    // =====================================================================
    // HELPER
    // =====================================================================
    function formatAngkaValue(value) {
        if (value == null) return "";
        let angka = Math.round(value).toString().replace(/\./g, "");
        return new Intl.NumberFormat("id-ID").format(angka);
    }

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

    /** Loader SweetAlert2 untuk proses submit (non-blocking). */
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

    function closeSwalLoader() {
        if (Swal.isVisible()) {
            Swal.close();
        }
    }

    // =====================================================================
    // MODAL PEMBAYARAN TUNGGAL (order standalone)
    // =====================================================================
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
        // Hanya select di dalam modal pembayaran tunggal (hindari select modal lain)
        $('#payment-modal .js-example-basic').select2({
            dropdownParent: $('#payment-modal'),
            width: "100%",
        });
        $('#orderCode').val(code);
        getDirectPaymentDetail(code);
    }

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
            usePercent = true;
            value = percentStored;
            checked = true;
        } else if (hasPayment && storedNominal > 0) {
            usePercent = false;
            value = storedNominal;
            checked = true;
        } else if (customerPercent > 0) {
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

        $('#cost_label').text('Rp ' + formatAngkaValue(currentCost));
        $('#additional_cost_label').text('Rp ' + formatAngkaValue(currentAdditionalCost));
        $('#subtotal_label').text('Rp ' + formatAngkaValue(subtotal));
        $('#ppn_label').text('+ Rp ' + formatAngkaValue(ppn.nominal) + (ppn.percent !== null ? ' (' + ppn.percent + '%)' : ''));
        $('#pph_label').text('- Rp ' + formatAngkaValue(pph.nominal) + (pph.percent !== null ? ' (' + pph.percent + '%)' : ''));
        $('#claim_label').text('- Rp ' + formatAngkaValue(claim));
        $('#grand_total_label').text('Rp ' + formatAngkaValue(grandTotal));
        $('#payment_label').text('Rp ' + formatAngkaValue(currentPaid));

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

    $(document).on('change', '#usePpn', function() {
        applyTaxVisibility('ppn');
        updateCalculatedTotal();
    });

    $(document).on('change', '#usePph', function() {
        applyTaxVisibility('pph');
        updateCalculatedTotal();
    });

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

    $(document).on('input', '#ppnInput', function() {
        updateCalculatedTotal();
    });

    $(document).on('input', '#pphInput', function() {
        updateCalculatedTotal();
    });

    $(document).on('change', '#useClaim', function() {
        let checked = $(this).is(':checked');
        $('#claim-input-group').toggle(checked);
        $('#claim-badge').toggle(checked);
        updateCalculatedTotal();
    });

    $(document).on('input', '#claimInput', function() {
        updateCalculatedTotal();
    });

    $(document).on('click', '.quick-chip-btn', function() {
        $('.quick-chip-btn').removeClass('active');
        $(this).addClass('active');

        let pct = parseInt($(this).data('pct')) || 100;
        let sisa = Math.max(0, computeSisa());
        let nominal = Math.round(sisa * pct / 100);
        $('#nominalInput').val(formatAngkaValue(nominal));
        updatePaymentRealtime();
    });

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

    function validateAndFormatForm() {
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

        document.getElementById("paymentAmountHidden").value = paymentAmount;
        if (paymentAmount >= total) {
            document.getElementById("type").value = "Full";
        } else {
            document.getElementById("type").value = "Dp";
        }

        return true;
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

                dataTableInstance.ajax.reload(null, false);

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

    // =====================================================================
    // MODAL DETAIL ORDER (standalone) — + badge nota bila order masuk nota
    // =====================================================================
    function showDetailModal(orderCode) {
        currentDetailOrderCode = orderCode;
        currentDetailNotaNumber = null;
        $('#detail-order-badge').text(orderCode);
        $('#detail-customer-name').text('Memuat...');
        $('#detail-order-date').text('-');
        $('#detail-status-badge').attr('class', 'badge bg-secondary-subtle text-secondary px-3 py-1 fs-11 rounded-pill fw-bold').text('Memuat...');
        $('#detail-nota-badge').addClass('d-none');
        $('#detail-history-tbody').html('<tr><td colspan="7" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>Memuat data...</td></tr>');
        $('#detail-oncharge-card').hide();

        $('#detail-modal').modal('show');

        $.get("{{ url('ajax/direct-payment-detail') }}/" + orderCode, function(data) {
            $('#detail-order-badge').text(data.order_code || orderCode);
            $('#detail-customer-name').text(data.customer_name || '-');
            $('#detail-order-date').text(data.order_date || '-');

            // Badge nota (order yang tergabung dalam nota multi-DO)
            if (data.nota_number) {
                currentDetailNotaNumber = data.nota_number;
                $('#detail-nota-badge').removeClass('d-none').text('Nota ' + data.nota_number);
            } else {
                currentDetailNotaNumber = null;
                $('#detail-nota-badge').addClass('d-none');
            }

            let statusBadgeClass = 'bg-danger text-white';
            if (data.status_code === 'paid') {
                statusBadgeClass = 'bg-success text-white';
            } else if (data.status_code === 'overpaid') {
                statusBadgeClass = 'bg-info text-white';
            } else if (data.status_code === 'partial') {
                statusBadgeClass = 'bg-warning text-dark';
            }
            $('#detail-status-badge').attr('class', 'badge px-3 py-1 fs-11 rounded-pill fw-bold ' + statusBadgeClass).text(data.status);

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

            if (data.status_code !== 'paid') {
                $('#detail-btn-bayar-now').removeClass('d-none');
            } else {
                $('#detail-btn-bayar-now').addClass('d-none');
            }
        });
    }

    // Cetak PDF dari modal detail: nota → cetak nota; standalone → pdf-multi
    $(document).on('click', '#detail-btn-print-pdf', function() {
        if (!currentDetailOrderCode) return;

        if (currentDetailNotaNumber) {
            window.open("{{ route('direct-payment.pdf-nota', ':orderCode') }}".replace(':orderCode', encodeURIComponent(currentDetailOrderCode)), '_blank', 'noopener');
            return;
        }

        let container = $('#multiPdfOrderCodesContainer');
        container.empty();
        container.append(`<input type="hidden" name="orderCodes[]" value="${currentDetailOrderCode}">`);
        $('#pdf-multi-form').submit();
    });

    $(document).on('click', '#detail-btn-bayar-now', function() {
        if (!currentDetailOrderCode) return;
        let targetOrder = currentDetailOrderCode;
        $('#detail-modal').modal('hide');
        setTimeout(() => {
            showModal(targetOrder);
        }, 350);
    });

    // =====================================================================
    // MODAL DETAIL NOTA (multi-DO)
    // =====================================================================
    function paintNotaStatusTone(statusCode) {
        let tone = 'neutral';
        if (statusCode === 'paid') {
            tone = 'paid';
        } else if (statusCode === 'partial') {
            tone = 'partial';
        } else if (statusCode === 'pending') {
            tone = 'pending';
        }
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

            // Daftar order pada nota
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

            // Riwayat pembayaran (digabung per transaksi/batch)
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
    // SELEKSI CHECKBOX (order utk nota / nota utk pembayaran)
    // =====================================================================
    function readNotaCheckbox(checkbox) {
        return {
            orderCode: String(checkbox.attr('data-order-code') || ''),
            customerCode: String(checkbox.attr('data-customer-code') || ''),
            customerName: String(checkbox.attr('data-customer-name') || '-'),
            billingAmount: Math.round(Number(checkbox.attr('data-billing-amount') || 0)),
            subtotalAmount: Math.round(Number(checkbox.attr('data-subtotal-amount') || 0)),
            paidAmount: Math.round(Number(checkbox.attr('data-paid-amount') || 0)),
            remainingAmount: Math.round(Number(checkbox.attr('data-remaining-amount') || 0)),
            checkboxType: 'nota',
        };
    }

    function readPaymentCheckbox(checkbox) {
        return {
            notaNumber: String(checkbox.attr('data-nota-number') || ''),
            notaDate: String(checkbox.attr('data-nota-date') || ''),
            customerName: String(checkbox.attr('data-customer-name') || '-'),
            customerCode: String(checkbox.attr('data-customer-code') || ''),
            orderCodes: String(checkbox.attr('data-order-codes') || '').split(',').map(function(code) {
                return code.trim();
            }).filter(function(code) {
                return code !== '';
            }),
            orderCount: Number(checkbox.attr('data-order-count') || 0),
            paymentStatus: String(checkbox.attr('data-payment-status') || 'pending'),
            checkboxType: 'payment',
            billingAmount: Math.round(Number(checkbox.attr('data-billing-amount') || 0)),
            paidAmount: Math.round(Number(checkbox.attr('data-paid-amount') || 0)),
            remainingAmount: Math.round(Number(checkbox.attr('data-remaining-amount') || 0)),
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
            const type = String(checkbox.attr('data-checkbox-type') || '');

            if (type === 'payment') {
                const notaNumber = String(checkbox.attr('data-nota-number') || '');
                const selected = !!selectedPayNotas[notaNumber];
                checkbox.prop('checked', selected);
                checkbox.closest('tr').toggleClass('table-active', selected);

                if (selected) {
                    const refreshed = readPaymentCheckbox(checkbox);
                    refreshed.allocationAmount = selectedPayNotas[notaNumber].allocationAmount;
                    selectedPayNotas[notaNumber] = refreshed;
                }
            } else if (type === 'nota') {
                const orderCode = String(checkbox.attr('data-order-code') || '');
                const selected = !!selectedNotaOrders[orderCode];
                checkbox.prop('checked', selected);
                checkbox.closest('tr').toggleClass('table-active', selected);

                if (selected) {
                    selectedNotaOrders[orderCode] = readNotaCheckbox(checkbox);
                }
            }
        });

        syncSelectAllState();
    }

    function updateSelectionUI() {
        const notaOrders = Object.values(selectedNotaOrders);
        const payNotas = selectedPaymentItems();
        const totals = calculateSelectedPaymentTotals();
        const orderCount = notaOrders.length + totals.orderCount;
        const count = notaOrders.length + payNotas.length;

        $('#selected-headline').text(count + ' terpilih');
        $('#selection-order-fact').text(orderCount + ' DO');
        $('#selection-nota-fact').text(payNotas.length + ' nota');
        $('#selection-remaining-fact').text('Sisa ' + formatCurrency(totals.remaining));
        $('#nota-selection-count').text(notaOrders.length);
        $('#payment-selection-count').text(payNotas.length);

        $('#selection-bar').toggleClass('d-none', count === 0);

        // Cetak PDF hanya untuk order standalone terpilih
        $('#btn-count').text(notaOrders.length);
        $('#btn-print-multi').toggleClass('d-none', notaOrders.length === 0);

        $('#btn-generate-nota').prop('disabled', notaOrders.length === 0);
        $('#btn-batch-pay').prop('disabled', payNotas.length === 0 || totals.remaining < 1);

        syncSelectAllState();
    }

    function clearSelection() {
        Object.keys(selectedNotaOrders).forEach(function(key) { delete selectedNotaOrders[key]; });
        Object.keys(selectedPayNotas).forEach(function(key) { delete selectedPayNotas[key]; });
        $('.row-payment-checkbox').prop('checked', false).closest('tr').removeClass('table-active');
        $('#check-all').prop('checked', false).prop('indeterminate', false);
        updateSelectionUI();
    }

    // =====================================================================
    // GENERATE NOTA (gabung beberapa DO milik satu customer)
    // =====================================================================
    function parseNotaRateInput(el) {
        let value = String($(el).val() || '').replace(',', '.').replace(/[^0-9.]/g, '');
        const parts = value.split('.');
        value = parts.shift() + (parts.length ? '.' + parts.join('') : '');
        const rate = parseFloat(value);

        return Number.isFinite(rate) ? Math.min(100, Math.max(0, rate)) : 0;
    }

    function parseNotaClaimInput(el) {
        let value = String($(el).val() || '').replace(/\./g, '').replace(',', '.').replace(/[^0-9.]/g, '');
        const amount = parseFloat(value);

        return Number.isFinite(amount) ? Math.max(0, Math.round(amount)) : 0;
    }

    function formatNotaRate(value) {
        return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 4 }).format(Number(value) || 0);
    }

    function formatNotaNumber(value) {
        return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Math.round(Number(value) || 0));
    }

    function formatNotaClaimInput(el) {
        let value = String($(el).val() || '');
        if (value.endsWith('.') || value.endsWith(',')) {
            return;
        }

        let digits = value.replace(/\./g, '').replace(/,/g, '').replace(/[^0-9]/g, '');
        if (digits === '') {
            return;
        }

        let formatted = formatNotaNumber(Number(digits));
        if (value !== formatted) {
            $(el).val(formatted);
        }
    }

    function updateNotaTaxCalculation() {
        const ppnRate = parseNotaRateInput($('#notaPpnRate'));
        const pphRate = parseNotaRateInput($('#notaPphRate'));
        const claim = parseNotaClaimInput($('#notaClaimAmount'));
        const ppn = Math.round(notaModalState.subtotal * ppnRate / 100);
        const pph = Math.round(notaModalState.subtotal * pphRate / 100);
        const grandTotal = notaModalState.subtotal + ppn - pph - claim;

        $('#notaPpnPreview, #notaPpnAmountPreview').text('Rp ' + formatNotaNumber(ppn));
        $('#notaPphPreview, #notaPphAmountPreview').text('Rp ' + formatNotaNumber(pph));
        $('#notaSubtotal').text('Rp ' + formatNotaNumber(notaModalState.subtotal));

        const grandTotalEl = $('#notaGrandTotal');
        grandTotalEl.text('Rp ' + formatNotaNumber(Math.max(0, grandTotal)));
        grandTotalEl.toggleClass('nota-grand-total-negative', grandTotal < 0);

        return {
            ppnRate: ppnRate,
            pphRate: pphRate,
            ppn: ppn,
            pph: pph,
            claim: claim,
            grandTotal: grandTotal,
        };
    }

    function loadNotaBankData() {
        $.ajax({
            url: "{{ route('api.user-bank.company') }}",
            type: "GET",
            success: function(response) {
                const bankSelect = $('#notaUserBankCode').empty();
                bankSelect.append(new Option('Pilih Bank', ''));

                if (Array.isArray(response) && response.length > 0) {
                    response.forEach(function(bank) {
                        const bankLabel = (bank.bank_name || 'Unknown Bank') + ' - ' + (bank.account_number || '-') + ' (' + (bank.account_name || '-') + ')';
                        bankSelect.append(new Option(bankLabel, bank.code || ''));
                    });
                } else {
                    bankSelect.append(new Option('Tidak ada data bank', '', false, false));
                    bankSelect.find('option:last').prop('disabled', true);
                }

                bankSelect.trigger('change');
            },
            error: function() {
                const bankSelect = $('#notaUserBankCode').empty();
                bankSelect.append(new Option('Pilih Bank', ''));
                bankSelect.append(new Option('Error memuat data', '', false, false));
                bankSelect.find('option:last').prop('disabled', true).end().trigger('change');
            }
        });
    }

    function openGenerateNotaModal() {
        const notaOrders = Object.values(selectedNotaOrders);
        const selectedCodes = notaOrders.map(function(item) { return item.orderCode; });

        if (selectedCodes.length === 0) {
            swal('Peringatan', 'Pilih minimal satu order yang belum memiliki nota.', 'warning');
            return;
        }

        // Validasi: satu nota = satu customer
        const uniqueCustomers = [...new Set(notaOrders.map(function(item) { return item.customerCode; }).filter(function(code) { return code !== ''; }))];
        if (uniqueCustomers.length > 1) {
            swal('Peringatan', 'Order yang dipilih milik customer yang berbeda. Satu nota hanya diperbolehkan untuk customer yang sama.', 'warning');
            return;
        }

        const totalSubtotal = notaOrders.reduce(function(total, item) {
            return total + (item.subtotalAmount || item.billingAmount);
        }, 0);

        $('#notaOrderCount').text(selectedCodes.length + ' DO');
        const customerName = String(notaOrders[0].customerName || '-').trim();
        $('#notaCustomerName').text(customerName || '-').attr('title', customerName);

        const orderListEl = $('#notaOrderList').empty();
        selectedCodes.forEach(function(orderCode) {
            orderListEl.append($('<span>', { class: 'nota-order-chip' }).text(orderCode));
        });

        $('#notaPpnRate').val('0');
        $('#notaPphRate').val('0');
        $('#notaClaimAmount').val('0');
        $('#notaClaimDescription').val('');
        notaModalState.subtotal = totalSubtotal;
        updateNotaTaxCalculation();

        $('#notaUserBankCode').val('').trigger('change');

        const container = $('#notaOrderCodesContainer').empty();
        selectedCodes.forEach(function(orderCode) {
            container.append($('<input>', {
                type: 'hidden',
                name: 'orderCodes[]',
                value: orderCode,
            }));
        });

        $('#nota-modal').modal('show');
    }

    $('#notaPpnRate, #notaPphRate').on('input', function() {
        updateNotaTaxCalculation();
    });

    $('#notaClaimAmount').on('input', function() {
        formatNotaClaimInput(this);
        updateNotaTaxCalculation();
    });

    $('#notaPpnRate, #notaPphRate').on('blur', function() {
        $(this).val(formatNotaRate(parseNotaRateInput(this)));
        updateNotaTaxCalculation();
    });

    $('#notaClaimAmount').on('blur', function() {
        $(this).val(formatNotaNumber(parseNotaClaimInput(this)));
        updateNotaTaxCalculation();
    });

    $('#notaPpnRate, #notaPphRate, #notaClaimAmount').on('focus', function() {
        $(this).select();
    });

    $('#generate-nota-form').on('submit', function(e) {
        e.preventDefault();

        const notaOrders = Object.values(selectedNotaOrders);
        const selectedCodes = notaOrders.map(function(item) { return item.orderCode; });
        const selectedBank = $('#notaUserBankCode').val();

        if (!selectedBank) {
            swal('Peringatan', 'Pilih bank pembayaran terlebih dahulu.', 'warning');
            return false;
        }

        const tax = updateNotaTaxCalculation();
        if (tax.ppnRate < 0 || tax.pphRate < 0 || tax.ppnRate > 100 || tax.pphRate > 100) {
            swal('Peringatan', 'Persentase PPN dan PPh harus antara 0% sampai 100%.', 'warning');
            return false;
        }

        if (tax.grandTotal < 0) {
            swal('Peringatan', 'Total bayar (Subtotal + PPN − PPh − Claim) tidak boleh minus. Periksa kembali persentase PPh dan nominal Biaya Claim yang diinput.', 'warning');
            return false;
        }

        $('#notaPpnRate').val(String(tax.ppnRate).replace(',', '.'));
        $('#notaPphRate').val(String(tax.pphRate).replace(',', '.'));
        $('#notaClaimAmount').val(String(tax.claim));

        const hasClaim = tax.claim > 0;
        const hasTax = tax.ppnRate > 0 || tax.pphRate > 0 || hasClaim;
        let taxText = '\nTotal Bayar: ' + formatCurrency(tax.grandTotal);

        if (hasTax) {
            taxText = '\nSubtotal (DPP): ' + formatCurrency(notaModalState.subtotal) +
                (tax.ppnRate > 0 ? '\nPPN (' + formatNotaRate(tax.ppnRate) + '%): ' + formatCurrency(tax.ppn) : '') +
                (tax.pphRate > 0 ? '\nPPh (' + formatNotaRate(tax.pphRate) + '%): ' + formatCurrency(tax.pph) : '') +
                (hasClaim ? '\nBiaya Claim: ' + formatCurrency(tax.claim) : '') +
                '\nTotal Bayar: ' + formatCurrency(tax.grandTotal);
        }

        swal({
            title: "Generate Nota Pembayaran?",
            text: selectedCodes.length + " DO milik customer " + (notaOrders[0].customerName || '-') + " akan digabungkan ke dalam satu nota dan ditujukan ke akun bank yang dipilih." + taxText + "\n\nOrder yang sudah masuk nota tidak bisa dipindahkan ke nota lain.",
            icon: "info",
            buttons: ["Batal", "Ya, Generate Nota!"],
        }).then((willGenerate) => {
            if (willGenerate) {
                swal({
                    title: "Memproses Generate Nota...",
                    text: "Sedang membuat nota pembayaran, mohon tunggu.",
                    icon: "info",
                    buttons: false,
                    closeOnClickOutside: false,
                    closeOnEsc: false,
                });

                $('#generate-nota-form button[type="submit"]').prop('disabled', true);

                $.ajax({
                    url: $('#generate-nota-form').attr('action'),
                    type: 'POST',
                    data: new FormData($('#generate-nota-form')[0]),
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    success: function(res) {
                        $('#generate-nota-form button[type="submit"]').prop('disabled', false);

                        if (res.success) {
                            $('#nota-modal').modal('hide');
                            swal({
                                title: "Berhasil!",
                                text: res.message || ('Nota pembayaran berhasil di-generate: ' + (res.nota_number || '')),
                                icon: "success",
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            swal("Gagal!", res.message || 'Terjadi kesalahan saat membuat nota.', "error");
                        }
                    },
                    error: function(xhr) {
                        $('#generate-nota-form button[type="submit"]').prop('disabled', false);

                        let msg = 'Terjadi kesalahan saat membuat nota. Silakan coba lagi.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        swal("Gagal!", msg, "error");
                    }
                });
            } else {
                $('#notaPpnRate').val(formatNotaRate(tax.ppnRate));
                $('#notaPphRate').val(formatNotaRate(tax.pphRate));
                $('#notaClaimAmount').val(formatNotaNumber(tax.claim));
                updateNotaTaxCalculation();
            }
        });
    });

    // =====================================================================
    // BAYAR NOTA (batch pembayaran, DP/cicilan/lunas per nota)
    // =====================================================================
    function setBankLoadingState(message, isError) {
        $('#batchBankStatus')
            .text(message)
            .toggleClass('text-danger', !!isError)
            .toggleClass('text-success', false);
        $('#reloadBatchBanksBtn').toggleClass('d-none', !isError);
    }

    function loadBatchBankData(forceReload) {
        if (batchBanksLoaded && !forceReload) {
            return;
        }

        const bankSelect = $('#batchUserBankCode');
        const requestSequence = ++batchBankRequestSequence;
        batchBanksLoaded = false;
        bankSelect.prop('disabled', true).empty().append(new Option('Memuat rekening...', ''));
        bankSelect.trigger('change');
        setBankLoadingState('Memuat rekening perusahaan...', false);
        updateBatchPaymentSummary();

        $.ajax({
            url: "{{ route('api.user-bank.company') }}",
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (requestSequence !== batchBankRequestSequence) {
                    return;
                }

                bankSelect.empty().append(new Option('Pilih rekening sumber dana', ''));

                if (Array.isArray(response) && response.length > 0) {
                    response.forEach(function(bank) {
                        const label = (bank.bank_name || 'Bank') + ' · ' + (bank.account_number || '-') + ' · ' + (bank.account_name || '-');
                        bankSelect.append(new Option(label, bank.code || '', false, false));
                    });
                    batchBanksLoaded = true;
                    bankSelect.prop('disabled', false);
                    setBankLoadingState(response.length + ' rekening tersedia.', false);
                } else {
                    bankSelect.append(new Option('Tidak ada rekening perusahaan', '', false, false));
                    setBankLoadingState('Tidak ada rekening perusahaan yang dapat digunakan.', true);
                }

                bankSelect.trigger('change');
                updateBatchPaymentSummary();
            },
            error: function() {
                if (requestSequence !== batchBankRequestSequence) {
                    return;
                }

                bankSelect.empty().append(new Option('Gagal memuat rekening', ''));
                bankSelect.trigger('change');
                setBankLoadingState('Rekening gagal dimuat. Periksa koneksi lalu muat ulang.', true);
                updateBatchPaymentSummary();
            }
        });
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

        if (amount < 1) {
            return 'Nominal minimal Rp 1.';
        }
        if (amount > item.remainingAmount) {
            return 'Melebihi sisa ' + formatCurrency(item.remainingAmount) + '.';
        }

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
        const customerCount = new Set(items.map(function(item) {
            return item.customerCode || item.customerName;
        })).size;
        const bankSelected = $('#batchUserBankCode').val() !== '';
        const dateValid = $('#batchDate').val() !== '';
        const ready = items.length > 0 && !firstError && totalPayment > 0 && batchBanksLoaded && bankSelected && dateValid && !batchSubmissionInFlight;

        $('#batchPaymentGrandTotal').text(formatCurrency(totalPayment));
        $('#batchPaymentAfterSummary').text('Sisa setelah pembayaran: ' + formatCurrency(totalAfter));
        $('#batchPaymentFactNotas').text(items.length);
        $('#batchPaymentFactCustomers').text(customerCount);
        $('#batchPaymentFactOrders').text(totals.orderCount);
        $('#batchPaymentFactRemaining').text(formatCurrency(totals.remaining));
        $('#batchSubmitLabel').text(totalPayment > 0 ? 'Bayar ' + formatCurrency(totalPayment) : 'Proses Pembayaran');
        $('#batchPaymentAllocationError').toggleClass('d-none', firstError === '').text(firstError);
        $('#batchDate').toggleClass('is-invalid', !dateValid).attr('aria-invalid', dateValid ? 'false' : 'true');
        $('#batchUserBankCode').toggleClass('is-invalid', batchBanksLoaded && !bankSelected).attr('aria-invalid', bankSelected ? 'false' : 'true');
        $('#submitBatchPaymentBtn').prop('disabled', !ready);

        if (bankSelected) {
            $('#batchBankStatus')
                .text('Rekening siap digunakan.')
                .toggleClass('text-danger', false)
                .toggleClass('text-success', true);
        } else {
            $('#batchBankStatus')
                .text('Pilih rekening sumber dana.')
                .toggleClass('text-danger', false)
                .toggleClass('text-success', false);
        }

        let hint = 'Siap diproses sebagai satu batch pembayaran.';
        if (firstError) {
            hint = 'Perbaiki nominal pembayaran yang ditandai.';
        } else if (!dateValid) {
            hint = 'Isi tanggal pembayaran.';
        } else if (!batchBanksLoaded || !bankSelected) {
            hint = 'Pilih rekening sumber dana.';
        }
        $('#batchSubmitHint').text(hint);
    }

    function applyBatchPaymentMode() {
        const fullPayment = $('#batchPaymentModeFull').is(':checked');

        selectedPaymentItems().forEach(function(item) {
            if (fullPayment) {
                item.allocationAmount = item.remainingAmount;
            }
        });

        $('.allocation-amount-input').each(function() {
            const input = $(this);
            const item = selectedPayNotas[String(input.attr('data-nota-number') || '')];
            input.prop('readonly', fullPayment);
            if (item) {
                input.val(formatNumber(item.allocationAmount));
            }
        });

        updateBatchPaymentSummary();
    }

    function setBatchPaymentSubmitting(isSubmitting) {
        batchSubmissionInFlight = isSubmitting;
        $('#batchSubmitSpinner').toggleClass('d-none', !isSubmitting);
        $('#batchSubmitIcon').toggleClass('d-none', isSubmitting);
        $('#batch-payment-modal [data-bs-dismiss]').prop('disabled', isSubmitting);
        $('#batchPaymentModeFull, #batchPaymentModeCustom, #batchDate, #batchDescription, .allocation-amount-input').prop('disabled', isSubmitting);
        $('#batchUserBankCode').prop('disabled', isSubmitting || !batchBanksLoaded).trigger('change.select2');
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

        batchRequestKey = generateRequestKey();
        items.forEach(function(item) {
            item.allocationAmount = item.remainingAmount;
        });
        $('#batchPaymentModeFull').prop('checked', true);
        $('#batchDate').val(localDateValue());
        $('#batchDescription').val('');
        $('#batchDescriptionCount').text('0/255');
        $('#batchUserBankCode').val('').trigger('change');
        renderBatchPaymentAllocations();
        setBatchPaymentSubmitting(false);
        $('#batch-payment-modal').modal('show');
        loadBatchBankData(false);
    }

    $('input[name="paymentMode"]').on('change', applyBatchPaymentMode);

    $(document).on('input', '.allocation-amount-input', function() {
        const input = $(this);
        const notaNumber = String(input.attr('data-nota-number') || '');
        const item = selectedPayNotas[notaNumber];
        const numericValue = String(input.val() || '').replace(/\D/g, '');

        if (!item) {
            return;
        }

        item.allocationAmount = numericValue === '' ? 0 : Number(numericValue);
        input.val(numericValue === '' ? '' : formatNumber(item.allocationAmount));
        updateBatchPaymentSummary();
    });

    $('#batchDate, #batchUserBankCode').on('change', updateBatchPaymentSummary);
    $('#batchDescription').on('input', function() {
        $('#batchDescriptionCount').text(String($(this).val()).length + '/255');
    });
    $('#reloadBatchBanksBtn').on('click', function() {
        loadBatchBankData(true);
    });

    $('#batch-payment-form').on('submit', function(event) {
        event.preventDefault();
        updateBatchPaymentSummary();

        if ($('#submitBatchPaymentBtn').prop('disabled') || batchSubmissionInFlight) {
            return;
        }

        const payload = batchPaymentPayload();
        const selectedBankText = $('#batchUserBankCode option:selected').text();
        const totalPayment = payload.payments.reduce(function(total, payment) {
            return total + payment.amount;
        }, 0);
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
            if (!result.isConfirmed) {
                return;
            }

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

                    const result = response.result || {};
                    const outcome = (result.fully_paid_count || 0) + ' nota lunas' + ((result.partial_count || 0) > 0 ? ' · ' + result.partial_count + ' nota masih sebagian' : '');
                    const successHtml = '<strong>' + escapeHtml(formatCurrency(result.payment_amount || 0)) + '</strong> berhasil dicatat.<br>'
                        + escapeHtml(outcome) + '<br>Kode: <strong>' + escapeHtml(result.batch_code || '-') + '</strong>';

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
                text: 'Kode batch pembayaran tidak tersedia. Transaksi lama (pembayaran tunggal) tidak dibatalkan otomatis demi keamanan.',
                icon: 'warning',
            });
            return;
        }

        $('#delete-form').attr('action', "{{ url('direct-payment/order/payment') }}/" + encodeURIComponent(orderCode));
        $('#cancel-payment-batch-code').val(batchCode);
        Swal.fire({
            title: 'Batalkan batch pembayaran terakhir?',
            text: 'Batch dapat mencakup beberapa order dalam satu atau beberapa nota. Pembayaran dikembalikan hanya untuk batch ' + batchCode + '.',
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
            text: 'Seluruh DO di dalam nota akan kembali ke daftar order standalone (belum dinota-kan).',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Batalkan Nota',
            cancelButtonText: 'Kembali',
            confirmButtonColor: '#dc3545',
        }).then(function(result) {
            if (result.isConfirmed) {
                swalLoader('Membatalkan nota', 'Sedang mengembalikan DO ke daftar order standalone...');
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
                { "data": 'plate', "className": 'align-middle text-center' },
                { "data": 'driver', "className": 'align-middle' },
                { "data": 'shipment', "className": 'align-middle' },
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
                [4, 'asc']
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
                "searchPlaceholder": "Cari kode order, no nota, customer, nopol...",
                "lengthMenu": "Tampilkan _MENU_ data",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                "infoEmpty": "Tidak ada data",
                "zeroRecords": "Tidak ditemukan data yang sesuai",
                "paginate": {
                    "next": "<i class='mdi mdi-chevron-right'></i>",
                    "previous": "<i class='mdi mdi-chevron-left'></i>"
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

        // Check All Handler (semua checkbox aktif yang terlihat)
        $('#check-all').on('change', function() {
            let isChecked = $(this).is(':checked');
            $('.row-payment-checkbox:visible:not(:disabled)').each(function() {
                if ($(this).is(':checked') !== isChecked) {
                    $(this).prop('checked', isChecked).trigger('change');
                }
            });
        });

        // Row Checkbox Handler
        $(document).on('change', '.row-payment-checkbox', function() {
            const checkbox = $(this);
            const type = String(checkbox.attr('data-checkbox-type') || '');

            if (type === 'payment') {
                const item = readPaymentCheckbox(checkbox);
                if (!item.notaNumber) return;

                if (checkbox.is(':checked')) {
                    selectedPayNotas[item.notaNumber] = item;
                } else {
                    delete selectedPayNotas[item.notaNumber];
                }
            } else if (type === 'nota') {
                const item = readNotaCheckbox(checkbox);
                if (!item.orderCode) return;

                if (checkbox.is(':checked')) {
                    selectedNotaOrders[item.orderCode] = item;
                } else {
                    delete selectedNotaOrders[item.orderCode];
                }
            } else {
                return;
            }

            checkbox.closest('tr').toggleClass('table-active', checkbox.is(':checked'));
            updateSelectionUI();
        });

        // Clear Selection
        $('#btn-clear-selection').on('click', clearSelection);

        // Submit Multi PDF Print (order standalone terpilih)
        $('#btn-print-multi').on('click', function() {
            const codes = Object.keys(selectedNotaOrders);
            if (codes.length === 0) {
                swal("Peringatan", "Pilih minimal 1 order untuk dicetak.", "warning");
                return;
            }

            let container = $('#multiPdfOrderCodesContainer');
            container.empty();
            codes.forEach(code => {
                container.append(`<input type="hidden" name="orderCodes[]" value="${code}">`);
            });

            $('#pdf-multi-form').submit();
        });

        // Generate Nota & Bayar Nota
        $('#btn-generate-nota').on('click', openGenerateNotaModal);
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

        // Select2 untuk modal bayar nota & generate nota
        $('#batchUserBankCode').select2({
            dropdownParent: $('#batch-payment-modal'),
            width: '100%',
        });

        $('#notaUserBankCode').select2({
            dropdownParent: $('#nota-modal'),
            width: '100%',
        });

        // Select2 untuk dropdown "Tampilkan _MENU_ data" milik DataTables
        $('#dt_wrapper .dataTables_length select').select2({
            minimumResultsForSearch: Infinity,
            width: '88px',
            dropdownAutoWidth: true,
        });

        // Muat rekening perusahaan untuk modal generate nota
        loadNotaBankData();

        updateSelectionUI();
    });
</script>
@endpush
