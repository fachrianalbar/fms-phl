{{-- Modal Riwayat Pembayaran per Faktur: DP, cicilan, pelunasan, dan claim (pengurang).
     Dipakai di halaman unpaid & paid invoice (tombol .btn-payment-history). --}}

@push('style')
<style>
    #modalPaymentHistory .ph-date-chip { width: 46px; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; background: #fff; box-shadow: 0 1px 3px rgba(16, 24, 40, .08); }
    #modalPaymentHistory .ph-date-day { height: 32px; display: flex; align-items: center; justify-content: center; font-size: 15px; font-weight: 700; color: #0f172a; line-height: 1; }
    #modalPaymentHistory .ph-date-mon { font-size: 9px; font-weight: 700; letter-spacing: 1px; text-indent: 1px; text-align: center; padding: 4px 0 3px; background: #3b82f6; color: #fff; }
    #modalPaymentHistory .ph-date-chip.ph-claim-chip .ph-date-mon { background: #f59e0b; }
    #modalPaymentHistory .ph-tl-line { width: 2px; flex: 1 1 auto; min-height: 12px; background: #e2e8f0; border-radius: 2px; margin: 4px 0; }
    #modalPaymentHistory .ph-tl-card { border: 1px solid #e2e8f0; border-radius: 12px; background: #fff; padding: 12px 14px; box-shadow: 0 1px 2px rgba(16, 24, 40, .04); }
    #modalPaymentHistory .ph-tl-card.ph-claim-card { border-color: #fde68a; background: #fffbeb; }
    #modalPaymentHistory .ph-legend-dot { display: inline-block; width: 10px; height: 10px; border-radius: 3px; }
</style>
@endpush

<div class="modal fade" id="modalPaymentHistory" tabindex="-1" aria-labelledby="modalPaymentHistoryLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable mt-4">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-primary text-white py-3 px-4 border-0" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important;">
                <div class="d-flex align-items-center gap-2">
                    <span class="avatar-sm">
                        <span class="avatar-title rounded-circle bg-white text-primary fs-18">
                            <i class="mdi mdi-cash-clock"></i>
                        </span>
                    </span>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0" id="modalPaymentHistoryLabel">Riwayat Pembayaran Faktur</h5>
                        <small class="text-white-50" id="phSubtitle">Faktur: -</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="phBody">
                <div class="text-center py-5 text-muted">Memuat riwayat pembayaran...</div>
            </div>
            <div class="modal-footer bg-light px-4 py-2 border-0">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('script')
<script>
    (function() {
        var phUrlTemplate = "{{ route('invoice.payment-history', ':PHID') }}";

        function phFmtRp(n) {
            return 'Rp ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(n || 0);
        }

        function phEsc(s) {
            return String(s == null ? '' : s).replace(/[&<>"']/g, function(c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
            });
        }

        function phErrBox(msg) {
            return '<div class="alert alert-danger rounded-3 py-3 px-3 mb-0"><i class="mdi mdi-alert-circle-outline me-2"></i>' + phEsc(msg) + '</div>';
        }

        function phStatusBadge(status) {
            if (Number(status) === 3) {
                return '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 fw-semibold fs-11"><i class="mdi mdi-check-circle me-1"></i>Lunas</span>';
            }
            if (Number(status) === 2) {
                return '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-1 fw-semibold fs-11"><i class="mdi mdi-clock-check-outline me-1"></i>Bayar Sebagian</span>';
            }

            return '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-1 fw-semibold fs-11"><i class="mdi mdi-file-document-outline me-1"></i>Belum Bayar</span>';
        }

        function phDateChip(dateStr, isClaim) {
            var parts = String(dateStr || '').trim().split(/\s+/);
            var claimCls = isClaim ? ' ph-claim-chip' : '';

            if (parts.length === 3 && /^\d{1,2}$/.test(parts[0])) {
                return '<div class="ph-date-chip' + claimCls + '">'
                    + '<div class="ph-date-day">' + parts[0] + '</div>'
                    + '<div class="ph-date-mon">' + phEsc(parts[1]).toUpperCase() + '</div>'
                    + '</div>';
            }

            return '<div class="ph-date-chip' + claimCls + ' d-flex align-items-center justify-content-center" style="min-height: 50px;">'
                + '<i class="mdi mdi-calendar-blank text-muted fs-20"></i>'
                + '</div>';
        }

        function phRender(res) {
            var payments = res.payments || [];
            var claims = res.claims || [];
            var billing = Number(res.billing) || 0;
            var paid = Number(res.totalPaid) || 0;
            var claim = Number(res.totalClaim) || 0;
            var remaining = Number(res.remaining) || 0;
            var html = '';

            // ===== 1. Info Faktur =====
            html += '<div class="p-3 rounded-3 bg-light border mb-3">'
                + '<div class="row g-2 g-sm-3 align-items-center">'
                + '<div class="col-12 col-sm-4"><span class="text-muted d-block fs-11">No. Faktur</span><span class="fw-bold text-dark font-monospace fs-13">' + phEsc(res.invoiceNumber) + '</span></div>'
                + '<div class="col-7 col-sm-4"><span class="text-muted d-block fs-11">Pelanggan</span><span class="fw-semibold text-dark fs-13">' + phEsc(res.customerName) + '</span></div>'
                + '<div class="col-5 col-sm-2"><span class="text-muted d-block fs-11">Tgl Faktur</span><span class="text-dark fs-12 text-nowrap">' + phEsc(res.invoiceDate) + '</span></div>'
                + '<div class="col-12 col-sm-2 text-sm-end"><span class="text-muted d-block fs-11">Status</span>' + phStatusBadge(res.status) + '</div>'
                + '</div>'
                + '</div>';

            // ===== 2. Progres Pelunasan =====
            var paidPct = billing > 0 ? Math.min(paid / billing * 100, 100) : 0;
            var claimPct = billing > 0 ? Math.min(claim / billing * 100, Math.max(100 - paidPct, 0)) : 0;
            var totalPct = Math.min(paidPct + claimPct, 100);

            html += '<div class="mb-4">'
                + '<div class="d-flex justify-content-between align-items-center mb-1">'
                + '<span class="fw-semibold text-dark fs-12"><i class="mdi mdi-chart-donut me-1 text-primary"></i>Progres Pelunasan</span>'
                + '<span class="fw-bold text-dark fs-12">' + totalPct.toFixed(1).replace('.', ',') + '% <span class="text-muted fw-normal">dari ' + phFmtRp(billing) + '</span></span>'
                + '</div>'
                + '<div class="progress" style="height: 10px; border-radius: 6px; background: #f1f5f9;">'
                + '<div class="progress-bar" role="progressbar" style="width:' + paidPct + '%; background: #22c55e;" title="Dibayar"></div>'
                + '<div class="progress-bar" role="progressbar" style="width:' + claimPct + '%; background: #f59e0b;" title="Claim"></div>'
                + '</div>'
                + '<div class="d-flex flex-wrap gap-3 mt-2 fs-11">'
                + '<span class="text-muted"><span class="ph-legend-dot me-1" style="background:#22c55e;"></span>Dibayar <strong class="text-dark">' + phFmtRp(paid) + '</strong></span>'
                + '<span class="text-muted"><span class="ph-legend-dot me-1" style="background:#f59e0b;"></span>Claim <strong class="text-dark">' + phFmtRp(claim) + '</strong></span>'
                + '<span class="text-muted"><span class="ph-legend-dot me-1" style="background:#e2e8f0;"></span>Sisa <strong class="text-dark">' + phFmtRp(remaining) + '</strong></span>'
                + '</div>'
                + '</div>';

            // ===== 3. Kronologis Pembayaran =====
            html += '<div class="d-flex align-items-center gap-2 mb-3">'
                + '<i class="mdi mdi-timeline-clock-outline text-primary fs-16"></i>'
                + '<h6 class="fw-bold text-dark fs-13 mb-0">Kronologis Pembayaran</h6>'
                + '<span class="badge bg-light text-secondary border fs-11">' + payments.length + ' pembayaran</span>'
                + '</div>';

            if (payments.length === 0 && claims.length === 0) {
                html += '<div class="text-center text-muted py-4 border rounded-3 bg-light"><i class="mdi mdi-cash-off fs-36 d-block mb-2 opacity-50"></i>Belum ada pembayaran untuk faktur ini.</div>';
            }

            var cumPaid = 0;
            payments.forEach(function(p, idx) {
                cumPaid += Number(p.amount) || 0;

                var label = 'Pembayaran';
                var badgeClass = 'bg-primary-subtle text-primary border-primary-subtle';
                if (payments.length > 1) {
                    if (idx === 0) {
                        label = 'DP';
                        badgeClass = 'bg-info-subtle text-info border-info-subtle';
                    } else {
                        label = 'Cicilan ke-' + idx;
                    }
                }
                var isLast = idx === payments.length - 1;
                if (isLast && payments.length > 1 && remaining === 0) {
                    label += ' — Pelunasan';
                    badgeClass = 'bg-success-subtle text-success border-success-subtle';
                }

                html += '<div class="d-flex gap-3">';

                // Chip tanggal + garis penghubung
                html += '<div class="d-flex flex-column align-items-center flex-shrink-0">';
                html += phDateChip(p.date, false);
                if (!isLast) {
                    html += '<div class="ph-tl-line"></div>';
                }
                html += '</div>';

                // Kartu pembayaran
                html += '<div class="flex-grow-1' + (isLast ? '' : ' pb-3') + '">';
                html += '<div class="ph-tl-card">';
                html += '<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">';
                html += '<div class="d-flex align-items-center gap-2 flex-wrap">'
                    + '<span class="badge ' + badgeClass + ' rounded-pill fs-11 px-2 py-1 fw-semibold">' + label + '</span>'
                    + '<span class="fw-semibold text-dark fs-12">' + phEsc(p.date) + '</span>'
                    + '</div>';
                html += '<span class="fw-bold text-success font-monospace fs-15">' + phFmtRp(p.amount) + '</span>';
                html += '</div>';

                html += '<div class="d-flex flex-wrap align-items-center gap-2 mt-2 fs-12 text-muted">'
                    + '<span class="text-nowrap"><i class="mdi mdi-bank-outline me-1"></i>' + phEsc(p.bank) + '</span>';
                if (p.transactionCode && p.transactionUrl) {
                    html += '<span class="opacity-50">•</span>'
                        + '<a href="' + p.transactionUrl + '" class="text-decoration-none font-monospace text-primary text-nowrap" title="Lihat detail transaksi pembayaran"><i class="mdi mdi-swap-horizontal me-1"></i>' + phEsc(p.transactionCode) + '</a>';
                }
                if (p.receiptUrl) {
                    html += '<a href="' + p.receiptUrl + '" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill py-0 px-2 fs-11 text-nowrap"><i class="mdi mdi-file-download-outline me-1"></i>Bukti Transfer</a>';
                }
                html += '</div>';

                if (p.description) {
                    html += '<div class="fs-12 text-secondary mt-2 fst-italic"><i class="mdi mdi-comment-text-outline me-1 opacity-75"></i>' + phEsc(p.description) + '</div>';
                }

                html += '<div class="d-flex justify-content-between align-items-center flex-wrap gap-1 border-top mt-2 pt-2 fs-11">'
                    + '<span class="text-muted">Pembayaran #' + (idx + 1) + '</span>'
                    + '<span class="text-muted">Kumulatif dibayar: <strong class="text-dark">' + phFmtRp(cumPaid) + '</strong>';

                if (isLast && remaining === 0) {
                    html += ' <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill fs-10 ms-1"><i class="mdi mdi-check-circle me-1"></i>Lunas</span>';
                } else if (isLast) {
                    html += ' <span class="text-danger fw-semibold ms-1">• Sisa ' + phFmtRp(remaining) + '</span>';
                }

                html += '</span>'
                    + '</div>';
                html += '</div>'; // .ph-tl-card
                html += '</div>'; // kolom kanan
                html += '</div>'; // baris
            });

            // ===== 4. Claim / Potongan Tagihan =====
            if (claims.length > 0) {
                html += '<div class="d-flex align-items-center gap-2 mb-3 mt-4">'
                    + '<i class="mdi mdi-cash-refund text-warning fs-16"></i>'
                    + '<h6 class="fw-bold text-dark fs-13 mb-0">Claim / Potongan Tagihan</h6>'
                    + '<span class="badge bg-light text-secondary border fs-11">' + claims.length + ' claim</span>'
                    + '</div>';

                claims.forEach(function(c, idx) {
                    var isLastC = idx === claims.length - 1;

                    html += '<div class="d-flex gap-3">';
                    html += '<div class="d-flex flex-column align-items-center flex-shrink-0">';
                    html += phDateChip(c.date, true);
                    if (!isLastC) {
                        html += '<div class="ph-tl-line"></div>';
                    }
                    html += '</div>';

                    html += '<div class="flex-grow-1' + (isLastC ? '' : ' pb-3') + '">';
                    html += '<div class="ph-tl-card ph-claim-card">';
                    html += '<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">'
                        + '<div class="d-flex align-items-center gap-2 flex-wrap">'
                        + '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill fs-11 px-2 py-1 fw-semibold">Claim (Pengurang)</span>'
                        + '<span class="fw-semibold text-dark fs-12">' + phEsc(c.date) + '</span>'
                        + '</div>'
                        + '<span class="fw-bold text-warning-emphasis font-monospace fs-15">- ' + phFmtRp(c.amount) + '</span>'
                        + '</div>';

                    if (c.description) {
                        html += '<div class="fs-12 text-secondary mt-2 fst-italic"><i class="mdi mdi-comment-text-outline me-1 opacity-75"></i>' + phEsc(c.description) + '</div>';
                    }
                    if (c.transactionCode && c.transactionUrl) {
                        html += '<div class="fs-12 text-muted mt-2"><a href="' + c.transactionUrl + '" class="text-decoration-none font-monospace text-primary" title="Lihat detail transaksi pembayaran"><i class="mdi mdi-swap-horizontal me-1"></i>' + phEsc(c.transactionCode) + '</a></div>';
                    }

                    html += '</div>'; // .ph-tl-card
                    html += '</div>'; // kolom kanan
                    html += '</div>'; // baris
                });
            }

            $('#phBody').html(html);
        }

        $(document).on('click', '.btn-payment-history', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            var number = $(this).data('invoice-number') || '-';

            $('#phSubtitle').text('Faktur: ' + number);
            $('#phBody').html('<div class="text-center py-5"><i class="mdi mdi-loading mdi-spin fs-30 text-primary"></i><div class="text-muted mt-2 fs-13">Memuat riwayat pembayaran...</div></div>');
            $('#modalPaymentHistory').modal('show');

            $.getJSON(phUrlTemplate.replace(':PHID', id), function(res) {
                if (!res || !res.success) {
                    $('#phBody').html(phErrBox((res && res.message) || 'Data tidak ditemukan'));
                    return;
                }

                phRender(res);

                if (res.customerName) {
                    $('#phSubtitle').text('Faktur: ' + number + '  •  ' + res.customerName);
                }
            }).fail(function(xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Gagal memuat riwayat pembayaran';
                $('#phBody').html(phErrBox(msg));
            });
        });
    })();
</script>
@endpush
