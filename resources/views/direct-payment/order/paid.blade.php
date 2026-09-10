@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Pembayaran Langsung',
    'secondSegment' => 'Order Lunas',
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
    /* Kartu KPI halaman ini statis (tanpa aksi filter). */
    .stat-card-static,
    .stat-card-static:hover {
        cursor: default;
        transform: none;
    }

    .table-scroll-wrap {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .table-scroll-wrap .direct-payment-table {
        min-width: 980px;
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
</style>
@endpush

@section('content')
<div class="col-sm-12">
    <!-- Page Header & Action Bar -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-3 d-flex align-items-center justify-content-center shadow-sm text-white"
                 style="width: 48px; height: 48px; background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;">
                <i class="mdi mdi-cash-check fs-24"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    {{ $title }}
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill fs-12 px-2 py-1">
                        {{ number_format($stats['totalCount'] ?? 0) }} Unit
                    </span>
                </h4>
                <p class="text-muted mb-0 fs-12">
                    Order dan nota yang pembayarannya sudah lunas. Pembatalan hanya tersedia untuk pembayaran batch nota.
                </p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm" id="btn-refresh-table" title="Muat Ulang Data Tabel">
                <i class="mdi mdi-refresh me-1" id="refresh-icon"></i> Refresh
            </button>
            <a href="{{ route('direct-payment.order.unpaid') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm">
                <i class="mdi mdi-receipt-text-clock me-1"></i> Order Belum Lunas
            </a>
        </div>
    </div>

    <!-- 4 KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card stat-card-static">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Total Unit Lunas</div>
                        <div class="stat-value text-success">
                            {{ number_format($stats['totalCount'] ?? 0) }}
                            <span class="fs-13 text-muted fw-normal">Unit</span>
                        </div>
                    </div>
                    <div class="stat-icon-wrapper bg-success-subtle text-success">
                        <i class="mdi mdi-check-decagram-outline"></i>
                    </div>
                </div>
                <div class="stat-desc text-success text-truncate">
                    <i class="mdi mdi-check-all me-1"></i>Order + nota lunas
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card stat-card-static">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Order Lunas</div>
                        <div class="stat-value text-primary">
                            {{ number_format($stats['orderCount'] ?? 0) }}
                            <span class="fs-13 text-muted fw-normal">Order</span>
                        </div>
                    </div>
                    <div class="stat-icon-wrapper bg-primary-subtle text-primary">
                        <i class="mdi mdi-file-document-check-outline"></i>
                    </div>
                </div>
                <div class="stat-desc text-primary text-truncate">
                    <i class="mdi mdi-file-document-outline me-1"></i>Order dibayar individual
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card stat-card-static">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Nota Lunas</div>
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
                    <i class="mdi mdi-file-document-multiple-outline me-1"></i>Nota multi-DO lunas
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card stat-card-static">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Total Diterima</div>
                        <div class="stat-value text-warning">
                            Rp {{ number_format($stats['totalPaid'] ?? 0, 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="stat-icon-wrapper bg-warning-subtle text-warning">
                        <i class="mdi mdi-cash-multiple"></i>
                    </div>
                </div>
                <div class="stat-desc text-warning-emphasis text-truncate">
                    <i class="mdi mdi-arrow-down-bold-circle-outline me-1"></i>Akumulasi pembayaran diterima
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table Container Card -->
    <div class="table-container-card mb-4">
        <div class="table-top-bar d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h6 class="fw-bold text-dark mb-1">Order &amp; Nota Lunas</h6>
                <div class="text-muted fs-12">
                    <i class="mdi mdi-information-outline me-1 text-success"></i>
                    Pembatalan pembayaran hanya untuk transaksi batch nota; pembayaran tunggal (arsip) tidak dapat dibatalkan.
                </div>
            </div>
        </div>
        <div class="card-body p-3">
            <div class="table-scroll-wrap custom-scrollbar">
                <table class="table table-striped nowrap direct-payment-table" id="dtPaid">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 130px;">Aksi</th>
                            <th class="text-center" style="width: 45px;">No</th>
                            <th>Kode / No Nota</th>
                            <th>Tanggal</th>
                            <th>Customer</th>
                            <th>Nopol</th>
                            <th class="text-end">Total Tagihan</th>
                            <th class="text-end">Terbayar</th>
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
<script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>

<script src="{{ asset('assets/js/sweet-alert/sweetalert2.min.js') }}"></script>

{{-- Flash message server → SweetAlert2 (harus setelah sweetalert2.min.js) --}}
@include('direct-payment.partials.flash-swal')

<script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>

<script>
    let paidTable;
    let currentDetailOrderCode = null;
    let currentDetailNotaNumber = null;

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

    function formatAngkaValue(value) {
        if (value == null) return "";
        let angka = Math.round(value).toString().replace(/\./g, "");
        return new Intl.NumberFormat("id-ID").format(angka);
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

    function paymentStatusLabel(status) {
        if (status === 'partial') return 'Dibayar sebagian';
        if (status === 'paid') return 'Lunas';
        return 'Belum dibayar';
    }

    function formatDateDMY(value) {
        if (value === null || value === undefined || value === '') {
            return '-';
        }

        const text = String(value);

        // Kolom tanggal umumnya sudah dirender server-side (HTML format d/m/Y).
        if (text.indexOf('<') !== -1) {
            return text;
        }

        const match = text.match(/^(\d{4})-(\d{2})-(\d{2})/);
        if (match) {
            return match[3] + '/' + match[2] + '/' + match[1];
        }

        return text;
    }

    // =====================================================================
    // MODAL DETAIL ORDER (standalone)
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
        // Halaman lunas: order sudah dibayar → tombol input pembayaran disembunyikan.
        $('#detail-btn-bayar-now').addClass('d-none');

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

            let statusBadgeClass = 'bg-success text-white';
            if (data.status_code === 'overpaid') {
                statusBadgeClass = 'bg-info text-white';
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
        }).fail(function() {
            $('#detail-modal').modal('hide');
            Swal.fire({
                title: 'Gagal!',
                text: 'Gagal memuat data pembayaran.',
                icon: 'error',
                confirmButtonText: 'Mengerti',
            });
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
    // BATAL PEMBAYARAN (batch nota)
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
                Swal.fire({
                    title: 'Membatalkan pembayaran',
                    text: 'Sedang mengembalikan pembayaran dan memperbarui nota...',
                    icon: 'info',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: function() {
                        Swal.showLoading();
                    },
                });
                $('#delete-form').submit();
            }
        });
    }

    // =====================================================================
    // INIT
    // =====================================================================
    $(document).ready(function() {
        paidTable = $('#dtPaid').DataTable({
            "processing": true,
            "serverSide": true,
            "destroy": true,
            "scrollX": true,
            "autoWidth": false,
            "pageLength": 25,
            "ajax": {
                "url": "{{ route('dt.direct-payment.paid') }}"
            },
            "columns": [
                { "data": 'action', "className": 'text-center align-middle', "orderable": false, "searchable": false },
                { "data": 'DT_RowIndex', "className": 'text-center align-middle', "orderable": false, "searchable": false },
                { "data": 'code', "className": 'align-middle' },
                { "data": 'date', "className": 'align-middle text-center', "render": function(data) { return formatDateDMY(data); } },
                { "data": 'customer_name', "className": 'align-middle' },
                { "data": 'plate', "className": 'align-middle text-center' },
                { "data": 'grand_total', "className": 'text-end align-middle' },
                { "data": 'paymentAmount', "className": 'text-end align-middle' },
                { "data": 'paymentStatus', "className": 'text-center align-middle' }
            ],
            "order": [
                [3, 'desc']
            ],
            "drawCallback": function() {
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

        // Rincian nota (baris unit nota)
        $(document).on('click', '.js-dp-nota-detail', function() {
            showNotaDetailModal(String($(this).attr('data-order-code') || ''));
        });

        // Batalkan batch pembayaran terakhir (baris unit nota)
        $(document).on('click', '.js-dp-payment-cancel', function() {
            confirmCancelPayment(
                String($(this).attr('data-order-code') || ''),
                String($(this).attr('data-batch-code') || '')
            );
        });

        // Refresh Button Click
        $('#btn-refresh-table').on('click', function() {
            let icon = $('#refresh-icon');
            icon.addClass('mdi-spin');
            paidTable.ajax.reload(function() {
                setTimeout(() => icon.removeClass('mdi-spin'), 400);
            });
        });

        // Select2 untuk dropdown "Tampilkan _MENU_ data" milik DataTables
        $('#dtPaid_wrapper .dataTables_length select').select2({
            minimumResultsForSearch: Infinity,
            width: '88px',
            dropdownAutoWidth: true,
        });
    });
</script>
@endpush
