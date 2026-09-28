@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Pembayaran Langsung',
    'secondSegment' => 'Order Menunggu Nota',
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
                 style="width: 48px; height: 48px; background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important;">
                <i class="mdi mdi-tray-full fs-24"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    {{ $title }}
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill fs-12 px-2 py-1">
                        {{ number_format($stats['waitingCount'] ?? 0) }} Order
                    </span>
                </h4>
                <p class="text-muted mb-0 fs-12">
                    Order customer non-DO yang belum dibuat nota. Setiap order (1 atau lebih) wajib dibuatkan nota terlebih dahulu sebelum dapat dibayar.
                </p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('direct-payment.order.unpaid') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm fw-semibold">
                <i class="mdi mdi-receipt-text-clock me-1"></i> Lihat Nota Belum Lunas
            </a>
            <button type="button" class="btn btn-danger btn-sm rounded-pill px-3 shadow-sm d-none" id="btn-print-multi" title="Cetak PDF Order Terpilih">
                <i class="mdi mdi-file-pdf me-1"></i> Cetak PDF (<span id="btn-count">0</span>)
            </button>
            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm text-white fw-semibold" id="btn-generate-nota" disabled>
                <i class="mdi mdi-file-document-plus-outline me-1"></i> Generate Nota (<span id="nota-selection-count">0</span>)
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm" id="btn-refresh-table" title="Muat Ulang Data Tabel">
                <i class="mdi mdi-refresh me-1" id="refresh-icon"></i> Refresh
            </button>
        </div>
    </div>

    <!-- 4 KPI Executive Metric Cards -->
    <div class="row g-3 mb-4">
        <!-- Card 1: Order Menunggu Nota -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Order Menunggu Nota</div>
                        <div class="stat-value text-primary">
                            {{ number_format($stats['waitingCount'] ?? 0) }}
                            <span class="fs-13 text-muted fw-normal">Order</span>
                        </div>
                    </div>
                    <div class="stat-icon-wrapper bg-primary-subtle text-primary">
                        <i class="mdi mdi-tray-full"></i>
                    </div>
                </div>
                <div class="stat-desc text-truncate">
                    <i class="mdi mdi-information-outline me-1 text-primary"></i>Wajib di-generate nota
                </div>
            </div>
        </div>

        <!-- Card 2: Total Nilai Tagihan -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Total Nilai Tagihan</div>
                        <div class="stat-value text-info">
                            Rp {{ number_format($stats['totalBilling'] ?? 0, 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="stat-icon-wrapper bg-info-subtle text-info">
                        <i class="mdi mdi-calculator"></i>
                    </div>
                </div>
                <div class="stat-desc text-truncate">
                    <i class="mdi mdi-cash-multiple me-1 text-info"></i>Akumulasi tagihan order belum dinota-kan
                </div>
            </div>
        </div>

        <!-- Card 3: Sisa Tagihan -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Sisa Tagihan</div>
                        <div class="stat-value text-danger">
                            Rp {{ number_format($stats['totalRemaining'] ?? 0, 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="stat-icon-wrapper bg-danger-subtle text-danger">
                        <i class="mdi mdi-alert-circle-outline"></i>
                    </div>
                </div>
                <div class="stat-desc text-danger text-truncate">
                    <i class="mdi mdi-clock-outline me-1"></i>Belum terbayar / belum lunas
                </div>
            </div>
        </div>

        <!-- Card 4: Customer Terlibat -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Customer Terlibat</div>
                        <div class="stat-value" style="color: #6366f1;">
                            {{ number_format($stats['customerCount'] ?? 0) }}
                            <span class="fs-13 text-muted fw-normal">Customer</span>
                        </div>
                    </div>
                    <div class="stat-icon-wrapper" style="background-color: #e0e7ff; color: #4f46e5;">
                        <i class="mdi mdi-account-group-outline"></i>
                    </div>
                </div>
                <div class="stat-desc text-truncate" style="color: #4f46e5;">
                    <i class="mdi mdi-domain me-1"></i>Customer non-DO berbeda
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table Container Card -->
    <div class="table-container-card mb-4">
        <!-- Selection Command Bar -->
        <div class="selection-command-bar d-none" id="selection-bar" aria-live="polite">
            <div>
                <strong id="selected-headline">0 order terpilih</strong>
                <div class="selection-facts mt-1">
                    <span id="selection-customer-fact">Customer: -</span>
                    <span id="selection-subtotal-fact">Subtotal: Rp 0</span>
                    <span id="selection-remaining-fact">Sisa: Rp 0</span>
                </div>
            </div>
            <div class="selection-actions">
                <button type="button" class="btn btn-outline-light btn-sm" id="btn-clear-selection">
                    <i class="mdi mdi-close me-1" aria-hidden="true"></i>Hapus Pilihan
                </button>
                <button type="button" class="btn btn-danger btn-sm fw-semibold" id="btn-print-multi-bar">
                    <i class="mdi mdi-file-pdf me-1" aria-hidden="true"></i>Cetak PDF
                </button>
                <button type="button" class="btn btn-warning btn-sm fw-semibold" id="btn-generate-nota-bar">
                    <i class="mdi mdi-file-document-plus-outline me-1" aria-hidden="true"></i>Generate Nota (<span id="nota-bar-count">0</span>)
                </button>
            </div>
        </div>

        <div class="card-body p-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <h6 class="fw-bold text-dark mb-1">Daftar Order Non-DO (Menunggu Nota)</h6>
                    <div class="text-muted fs-12">
                        <i class="mdi mdi-information-outline me-1 text-primary"></i>
                        Centang 1 atau lebih order milik <strong>customer yang sama</strong> lalu klik <strong>Generate Nota</strong> untuk membuat nota tagihan, atau klik ikon <strong>Generate Nota</strong> di baris order. Pembayaran dilakukan setelah nota dibuat di menu <strong>Nota Belum Lunas</strong>.
                    </div>
                </div>
            </div>
            <div class="table-scroll-wrap custom-scrollbar">
                <table class="table table-striped nowrap direct-payment-table" id="dtWaiting">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 40px;">
                                <input class="form-check-input" type="checkbox" id="check-all" title="Pilih semua data pada halaman ini">
                            </th>
                            <th class="text-center" style="width: 100px;">Aksi</th>
                            <th class="text-center" style="width: 45px;">No</th>
                            <th>Kode Order</th>
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

{{-- SweetAlert2 --}}
<script src="{{ asset('assets/js/sweet-alert/sweetalert2.min.js') }}"></script>

<script>
    (function syncSweetAlertTheme() {
        const style = document.createElement('style');
        style.textContent = `
            html[data-bs-theme="dark"] .swal2-popup.swal-readable-dark {
                background: #1f2028 !important;
                color: #f8fafc !important;
            }
            html[data-bs-theme="dark"] .swal2-popup.swal-readable-dark * {
                color: #f8fafc !important;
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
    const selectedOrders = {};
    const notaModalState = { subtotal: 0 };
    let currentDetailOrderCode = null;
    let currentDetailNotaNumber = null;

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

    function currentSwalTheme() {
        let savedTheme = null;
        try {
            savedTheme = JSON.parse(localStorage.getItem('__CONFIG__') || '{}').theme;
        } catch (error) {
            savedTheme = null;
        }

        return document.documentElement.getAttribute('data-bs-theme') === 'dark' || savedTheme === 'dark'
            ? 'dark'
            : 'light';
    }

    function applyReadableSwalText(popup) {
        const dialog = popup || Swal.getPopup();
        if (!dialog) return;

        const readableColor = '#f8fafc';
        dialog.classList.add('swal-readable-dark');
        dialog.style.setProperty('--swal2-color', readableColor, 'important');
        dialog.style.setProperty('--swal2-background', '#1f2028', 'important');
        dialog.style.setProperty('background', '#1f2028', 'important');
        dialog.style.setProperty('background-color', '#1f2028', 'important');
        dialog.style.setProperty('color', readableColor, 'important');
        dialog.style.setProperty('-webkit-text-fill-color', readableColor, 'important');

        dialog.querySelectorAll('h2.swal2-title, .swal2-html-container, .swal2-footer, h2.swal2-title *').forEach(function(element) {
            element.classList.add('swal-readable-dark-text');
            element.style.setProperty('color', readableColor, 'important');
            element.style.setProperty('-webkit-text-fill-color', readableColor, 'important');
            element.style.setProperty('opacity', '1', 'important');
        });
    }

    // =====================================================================
    // MODAL DETAIL ORDER
    // =====================================================================
    function showDetailModal(orderCode) {
        currentDetailOrderCode = orderCode;
        currentDetailNotaNumber = null;
        $('#detail-order-badge').text(orderCode);
        $('#detail-customer-name').text('Memuat...');
        $('#detail-order-date').text('-');
        $('#detail-status-badge').attr('class', 'badge bg-secondary-subtle text-secondary px-3 py-1 fs-11 rounded-pill fw-bold').text('Memuat...');
        $('#detail-nota-badge').addClass('d-none');
        $('#detail-btn-generate-nota-now').addClass('d-none');
        $('#detail-btn-view-nota').addClass('d-none');
        $('#detail-history-tbody').html('<tr><td colspan="7" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>Memuat data...</td></tr>');
        $('#detail-oncharge-card').hide();

        $('#detail-modal').modal('show');

        $.get("{{ url('ajax/direct-payment-detail') }}/" + orderCode, function(data) {
            $('#detail-order-badge').text(data.order_code || orderCode);
            $('#detail-customer-name').text(data.customer_name || '-');
            $('#detail-order-date').text(data.order_date || '-');

            if (data.nota_number) {
                currentDetailNotaNumber = data.nota_number;
                $('#detail-nota-badge').removeClass('d-none').text('Nota ' + data.nota_number);
                $('#detail-btn-generate-nota-now').addClass('d-none');
                $('#detail-btn-view-nota').removeClass('d-none');
            } else {
                currentDetailNotaNumber = null;
                $('#detail-nota-badge').addClass('d-none');
                $('#detail-btn-generate-nota-now').removeClass('d-none');
                $('#detail-btn-view-nota').addClass('d-none');
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
        });
    }

    $(document).on('click', '#detail-btn-print-pdf', function() {
        if (!currentDetailOrderCode) return;
        let container = $('#multiPdfOrderCodesContainer').empty();
        container.append(`<input type="hidden" name="orderCodes[]" value="${currentDetailOrderCode}">`);
        $('#pdf-multi-form').submit();
    });

    $(document).on('click', '#detail-btn-generate-nota-now', function() {
        if (!currentDetailOrderCode) return;
        let targetOrder = currentDetailOrderCode;
        $('#detail-modal').modal('hide');
        setTimeout(() => {
            generateNotaSingle(targetOrder);
        }, 350);
    });

    $(document).on('click', '#detail-btn-view-nota', function() {
        if (!currentDetailOrderCode) return;
        let targetOrder = currentDetailOrderCode;
        $('#detail-modal').modal('hide');
        setTimeout(() => {
            showNotaDetailModal(targetOrder);
        }, 350);
    });

    // =====================================================================
    // SELEKSI CHECKBOX
    // =====================================================================
    function readNotaCheckbox(checkbox) {
        return {
            orderCode: String(checkbox.attr('data-order-code') || ''),
            shipmentNumber: String(checkbox.attr('data-shipment') || ''),
            customerCode: String(checkbox.attr('data-customer-code') || ''),
            customerName: String(checkbox.attr('data-customer-name') || '-'),
            billingAmount: Math.round(Number(checkbox.attr('data-billing-amount') || 0)),
            subtotalAmount: Math.round(Number(checkbox.attr('data-subtotal-amount') || 0)),
            paidAmount: Math.round(Number(checkbox.attr('data-paid-amount') || 0)),
            remainingAmount: Math.round(Number(checkbox.attr('data-remaining-amount') || 0)),
        };
    }

    function calculateSelectedTotals() {
        return Object.values(selectedOrders).reduce(function(totals, item) {
            totals.billing += item.billingAmount;
            totals.subtotal += item.subtotalAmount;
            totals.paid += item.paidAmount;
            totals.remaining += item.remainingAmount;
            return totals;
        }, { billing: 0, subtotal: 0, paid: 0, remaining: 0 });
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
            const orderCode = String(checkbox.attr('data-order-code') || '');
            const selected = !!selectedOrders[orderCode];
            checkbox.prop('checked', selected);
            checkbox.closest('tr').toggleClass('table-active', selected);

            if (selected) {
                selectedOrders[orderCode] = readNotaCheckbox(checkbox);
            }
        });

        syncSelectAllState();
    }

    function updateSelectionUI() {
        const orderList = Object.values(selectedOrders);
        const count = orderList.length;
        const totals = calculateSelectedTotals();

        const uniqueCustomers = [...new Set(orderList.map(item => item.customerName).filter(Boolean))];
        const isSingleCustomer = uniqueCustomers.length <= 1;

        $('#selected-headline').text(count + ' order terpilih');
        $('#selection-customer-fact').text(
            count === 0 ? 'Customer: -' :
            (uniqueCustomers.length === 1 ? 'Customer: ' + uniqueCustomers[0] : 'Customer: Campuran (' + uniqueCustomers.length + ' customer)')
        );
        $('#selection-subtotal-fact').text('Subtotal: ' + formatCurrency(totals.subtotal));
        $('#selection-remaining-fact').text('Sisa: ' + formatCurrency(totals.remaining));

        $('#nota-selection-count').text(count);
        $('#nota-bar-count').text(count);
        $('#btn-count').text(count);

        $('#selection-bar').toggleClass('d-none', count === 0);
        $('#btn-print-multi').toggleClass('d-none', count === 0);

        // Tombol generate nota hanya aktif jika minimal 1 order dan customer sama
        const canGenerate = count > 0 && isSingleCustomer;
        $('#btn-generate-nota, #btn-generate-nota-bar').prop('disabled', !canGenerate);

        syncSelectAllState();
    }

    function clearSelection() {
        Object.keys(selectedOrders).forEach(function(key) { delete selectedOrders[key]; });
        $('.row-payment-checkbox').prop('checked', false).closest('tr').removeClass('table-active');
        $('#check-all').prop('checked', false).prop('indeterminate', false);
        updateSelectionUI();
    }

    // =====================================================================
    // GENERATE NOTA
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
        if (value.endsWith('.') || value.endsWith(',')) return;

        let digits = value.replace(/\./g, '').replace(/,/g, '').replace(/[^0-9]/g, '');
        if (digits === '') return;

        let formatted = formatNotaNumber(Number(digits));
        if (value !== formatted) $(el).val(formatted);
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
        const orderList = Object.values(selectedOrders);
        const selectedCodes = orderList.map(function(item) { return item.orderCode; });

        if (selectedCodes.length === 0) {
            Swal.fire({
                title: 'Peringatan',
                text: 'Pilih minimal satu order yang akan digabungkan ke nota.',
                icon: 'warning',
            });
            return;
        }

        const uniqueCustomers = [...new Set(orderList.map(item => item.customerCode).filter(Boolean))];
        if (uniqueCustomers.length > 1) {
            Swal.fire({
                title: 'Customer Berbeda',
                text: 'Order yang dipilih berasal dari customer yang berbeda. Satu nota hanya dapat dibuat untuk order milik customer yang sama.',
                icon: 'warning',
            });
            return;
        }

        const totalSubtotal = orderList.reduce(function(total, item) {
            return total + (item.subtotalAmount || item.billingAmount);
        }, 0);

        $('#notaOrderCount').text(selectedCodes.length + ' DO');
        const customerName = String(orderList[0].customerName || '-').trim();
        $('#notaCustomerName').text(customerName || '-').attr('title', customerName);

        const orderListEl = $('#notaOrderList').empty();
        selectedCodes.forEach(function(orderCode) {
            const item = selectedOrders[orderCode];
            const shipment = (item && item.shipmentNumber) || '';
            const label = shipment || orderCode;
            orderListEl.append($('<span>', { class: 'nota-order-chip' }).text(label));
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

    /**
     * Generate nota instan untuk 1 baris order langsung dari tombol aksi tabel
     */
    window.generateNotaSingle = function(orderCode) {
        const checkbox = $('.row-payment-checkbox[data-order-code="' + orderCode + '"]');
        let item;
        if (checkbox.length) {
            item = readNotaCheckbox(checkbox);
        } else {
            item = {
                orderCode: orderCode,
                shipmentNumber: '',
                customerCode: '',
                customerName: '',
                billingAmount: 0,
                subtotalAmount: 0,
                paidAmount: 0,
                remainingAmount: 0
            };
        }

        clearSelection();
        selectedOrders[orderCode] = item;
        if (checkbox.length) {
            checkbox.prop('checked', true).closest('tr').addClass('table-active');
        }
        updateSelectionUI();
        openGenerateNotaModal();
    };

    $('#notaPpnRate, #notaPphRate').on('input', updateNotaTaxCalculation);
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

    $('#generate-nota-form').on('submit', function(e) {
        e.preventDefault();

        const orderList = Object.values(selectedOrders);
        const selectedCodes = orderList.map(item => item.orderCode);
        const selectedBank = $('#notaUserBankCode').val();

        if (!selectedBank) {
            Swal.fire({
                title: 'Peringatan',
                text: 'Pilih bank pembayaran terlebih dahulu.',
                icon: 'warning',
                theme: currentSwalTheme(),
            });
            return false;
        }

        const tax = updateNotaTaxCalculation();
        if (tax.ppnRate < 0 || tax.pphRate < 0 || tax.ppnRate > 100 || tax.pphRate > 100) {
            Swal.fire({
                title: 'Peringatan',
                text: 'Persentase PPN dan PPh harus antara 0% sampai 100%.',
                icon: 'warning',
                theme: currentSwalTheme(),
            });
            return false;
        }

        if (tax.grandTotal < 0) {
            Swal.fire({
                title: 'Peringatan',
                text: 'Total bayar (Subtotal + PPN − PPh − Claim) tidak boleh minus. Periksa kembali persentase PPh dan nominal Biaya Claim yang diinput.',
                icon: 'warning',
                theme: currentSwalTheme(),
            });
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

        const confirmationText = selectedCodes.length + ' DO milik customer ' + (orderList[0].customerName || '-') + ' akan dibuatkan nota pembayaran.' + taxText + '\n\nSetelah nota dibuat, pembayaran dilakukan di menu Nota Belum Lunas.';

        Swal.fire({
            titleText: 'Generate Nota Pembayaran?',
            text: confirmationText,
            icon: 'info',
            theme: currentSwalTheme(),
            background: '#1f2028',
            color: '#f8fafc',
            customClass: {
                popup: 'swal-dark-popup',
                title: 'swal-dark-title',
                htmlContainer: 'swal-dark-html',
            },
            didOpen: applyReadableSwalText,
            didRender: applyReadableSwalText,
            showCancelButton: true,
            cancelButtonText: 'Batal',
            confirmButtonText: 'Ya, Generate Nota!',
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses Generate Nota...',
                    text: 'Sedang membuat nota pembayaran, mohon tunggu.',
                    icon: 'info',
                    theme: currentSwalTheme(),
                    background: '#1f2028',
                    color: '#f8fafc',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: function() { Swal.showLoading(); },
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
                            Swal.fire({
                                title: 'Berhasil!',
                                text: res.message || ('Nota pembayaran berhasil di-generate: ' + (res.nota_number || '')),
                                icon: 'success',
                                showCancelButton: true,
                                confirmButtonText: 'Buka Nota Belum Lunas',
                                cancelButtonText: 'Tetap di Halaman Ini',
                                confirmButtonColor: '#0ea5e9',
                            }).then((choice) => {
                                if (choice.isConfirmed) {
                                    window.location.href = "{{ route('direct-payment.order.unpaid') }}";
                                } else {
                                    window.location.reload();
                                }
                            });
                        } else {
                            Swal.fire({
                                title: 'Gagal!',
                                text: res.message || 'Terjadi kesalahan saat membuat nota.',
                                icon: 'error',
                                theme: currentSwalTheme(),
                            });
                        }
                    },
                    error: function(xhr) {
                        $('#generate-nota-form button[type="submit"]').prop('disabled', false);
                        let msg = 'Terjadi kesalahan saat membuat nota. Silakan coba lagi.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            title: 'Gagal!',
                            text: msg,
                            icon: 'error',
                            theme: currentSwalTheme(),
                        });
                    }
                });
            }
        });
    });

    // =====================================================================
    // INIT
    // =====================================================================
    $(document).ready(function() {
        dataTableInstance = $('#dtWaiting').DataTable({
            "processing": true,
            "serverSide": true,
            "destroy": true,
            "scrollX": true,
            "autoWidth": false,
            "pageLength": 25,
            "ajax": {
                "url": "{{ route('dt.direct-payment.waiting') }}",
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
                "searchPlaceholder": "Cari kode order, customer, nopol, driver...",
                "lengthMenu": "Tampilkan _MENU_ data",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                "infoEmpty": "Tidak ada data",
                "zeroRecords": "Tidak ditemukan data order yang sesuai",
                "paginate": {
                    "next": "<i class='mdi mdi-chevron-right'></i>",
                    "previous": "<i class='mdi mdi-chevron-left'></i>"
                }
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
            const item = readNotaCheckbox(checkbox);
            if (!item.orderCode) return;

            if (checkbox.is(':checked')) {
                selectedOrders[item.orderCode] = item;
            } else {
                delete selectedOrders[item.orderCode];
            }

            checkbox.closest('tr').toggleClass('table-active', checkbox.is(':checked'));
            updateSelectionUI();
        });

        // Clear Selection
        $('#btn-clear-selection').on('click', clearSelection);

        // Print Multi PDF
        $('#btn-print-multi, #btn-print-multi-bar').on('click', function() {
            const codes = Object.keys(selectedOrders);
            if (codes.length === 0) {
                Swal.fire({
                    title: 'Peringatan',
                    text: 'Pilih minimal satu order untuk dicetak.',
                    icon: 'warning',
                });
                return;
            }

            let container = $('#multiPdfOrderCodesContainer').empty();
            codes.forEach(code => {
                container.append(`<input type="hidden" name="orderCodes[]" value="${code}">`);
            });

            $('#pdf-multi-form').submit();
        });

        // Generate Nota Buttons (Header & Selection Bar)
        $('#btn-generate-nota, #btn-generate-nota-bar').on('click', openGenerateNotaModal);

        // Select2 di modal generate nota
        $('#notaUserBankCode').select2({
            dropdownParent: $('#nota-modal'),
            width: '100%',
        });

        // Select2 untuk dropdown "Tampilkan _MENU_ data" milik DataTables
        $('#dtWaiting_wrapper .dataTables_length select').select2({
            minimumResultsForSearch: Infinity,
            width: '88px',
            dropdownAutoWidth: true,
        });

        // Muat bank
        loadNotaBankData();

        updateSelectionUI();
    });
</script>
@endpush
