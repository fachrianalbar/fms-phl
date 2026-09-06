{{-- Modals & hidden forms untuk halaman Vendor -> Invoice Belum Lunas.
    Di-include dari vendor/invoice/unpaid.blade.php dan vendor/order/waiting.blade.php.
    Ported dari finance/vendor-payment/index.blade.php (modal pembayaran, detail,
    multi PDF, generate nota, batal pembayaran, batal nota). --}}

@include('vendor.invoice.partials.nota-modal-style')

{{-- Styling Modal Detail Pembayaran (unpaid & paid). Semua #detail-* tetap <input>/<textarea>
    agar .val() dari unpaid-payment-script & paid.blade.php tidak berubah. --}}
<style>
    /* Hallmark · component: payment-detail-modal · genre: modern-minimal · tone: technical
     * states: readonly display (no interactive states needed) · contrast: pass
     */
    .detail-modal {
        --dt-paper: oklch(100% 0 0);
        --dt-paper-soft: oklch(97% 0.008 250);
        --dt-canvas: oklch(96% 0.012 250);
        --dt-ink: oklch(25% 0.025 255);
        --dt-muted: oklch(52% 0.025 255);
        --dt-rule: oklch(90% 0.018 250);
        --dt-accent: oklch(55% 0.18 255);
        --dt-success: oklch(55% 0.14 155);
        --dt-danger: oklch(54% 0.2 25);
        --dt-warning: oklch(64% 0.14 70);
    }

    .detail-modal .modal-content {
        border: 0;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 30px 70px oklch(22% 0.03 255 / 0.28);
    }

    /* ===== Hero ===== */
    .detail-modal-hero {
        position: relative;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 14px 18px;
        padding: 20px 24px;
        color: #fff;
        background:
            radial-gradient(120% 170% at 100% -40%, oklch(70% 0.15 255 / 0.5), transparent 55%),
            linear-gradient(120deg, oklch(31% 0.08 265) 0%, oklch(44% 0.12 263) 55%, oklch(54% 0.16 258) 100%);
    }

    .detail-hero-icon {
        flex: 0 0 auto;
        width: 52px;
        height: 52px;
        display: grid;
        place-items: center;
        font-size: 26px;
        border-radius: 14px;
        background: oklch(100% 0 0 / 0.14);
        border: 1px solid oklch(100% 0 0 / 0.28);
    }

    .detail-hero-copy {
        min-width: 0;
        flex: 1 1 280px;
    }

    .detail-hero-eyebrow {
        display: block;
        font-size: 10.5px;
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: oklch(92% 0.03 250 / 0.9);
    }

    .detail-hero-title {
        margin: 2px 0 0;
        color: #fff;
        font-size: 20px;
        font-weight: 800;
        letter-spacing: -0.01em;
    }

    .detail-hero-sub {
        display: block;
        font-size: 12px;
        color: oklch(100% 0 0 / 0.72);
    }

    .detail-hero-close {
        align-self: flex-start;
        filter: brightness(0) invert(1);
        opacity: 0.85;
        margin: 2px 0 0 auto;
    }

    .detail-hero-close:hover { opacity: 1; }

    /* Status pill */
    .detail-status-pill {
        display: inline-flex;
        align-items: center;
        min-height: 34px;
        padding: 5px 14px;
        border-radius: 999px;
        background: oklch(100% 0 0 / 0.12);
        border: 1px solid oklch(100% 0 0 / 0.3);
        backdrop-filter: blur(3px);
    }

    .detail-status-input {
        width: auto;
        min-width: 92px;
        padding: 0;
        background: transparent;
        border: 0;
        color: #fff;
        font-size: 13px;
        font-weight: 800;
        text-align: center;
        letter-spacing: 0.01em;
        font-variant-numeric: tabular-nums;
    }

    .detail-status-input:focus { box-shadow: none; }

    .detail-status-pill[data-tone='paid'] {
        background: oklch(78% 0.13 155 / 0.24);
        border-color: oklch(88% 0.11 155 / 0.6);
    }

    .detail-status-pill[data-tone='partial'] {
        background: oklch(85% 0.11 85 / 0.24);
        border-color: oklch(90% 0.1 85 / 0.6);
    }

    .detail-status-pill[data-tone='pending'] {
        background: oklch(70% 0.17 25 / 0.3);
        border-color: oklch(82% 0.13 25 / 0.55);
    }

    /* ===== Body & amount tiles ===== */
    .detail-modal-body {
        background: var(--dt-canvas);
        padding: 18px 20px 22px;
    }

    .detail-amount-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 16px;
    }

    .detail-amount-tile {
        --tile-bg: var(--dt-paper);
        --tile-ink: var(--dt-ink);
        --tile-acc: var(--dt-muted);
        min-width: 0;
        padding: 12px 14px;
        background: var(--tile-bg);
        border: 1px solid var(--dt-rule);
        border-top: 3px solid var(--tile-acc);
        border-radius: 13px;
    }

    .detail-tile-label {
        display: flex;
        align-items: center;
        gap: 5px;
        margin-bottom: 6px;
        color: var(--dt-muted);
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.07em;
        white-space: nowrap;
    }

    .detail-tile-label i { font-size: 14px; }

    .detail-tile-value {
        display: block;
        width: 100%;
        padding: 0;
        background: transparent;
        border: 0;
        color: var(--tile-ink);
        font-size: 14px;
        font-weight: 800;
        letter-spacing: -0.005em;
        font-variant-numeric: tabular-nums;
    }

    .detail-tile-value:focus { box-shadow: none; }

    .detail-amount-tile[data-role='billing'] {
        --tile-bg: linear-gradient(135deg, oklch(33% 0.07 265), oklch(23% 0.03 262));
        --tile-ink: oklch(100% 0 0);
        --tile-acc: oklch(72% 0.16 255);
        border-color: transparent;
    }

    .detail-amount-tile[data-role='billing'] .detail-tile-label { color: oklch(90% 0.03 250 / 0.8); }

    .detail-amount-tile[data-role='ppn'] {
        --tile-acc: var(--dt-accent);
        --tile-ink: oklch(45% 0.16 260);
        background: oklch(96% 0.02 250);
    }

    .detail-amount-tile[data-role='pph'] {
        --tile-acc: var(--dt-danger);
        --tile-ink: oklch(50% 0.18 25);
        background: oklch(96.5% 0.02 25);
    }

    .detail-amount-tile[data-role='claim'] {
        --tile-acc: oklch(70% 0.17 80);
        --tile-ink: oklch(50% 0.15 75);
        background: oklch(97% 0.02 85);
    }

    .detail-amount-tile[data-role='remaining'] {
        --tile-acc: oklch(64% 0.15 60);
        --tile-ink: oklch(50% 0.16 45);
        background: oklch(97% 0.025 85);
    }

    .detail-amount-tile[data-role='paid'][data-tone='positive'] {
        --tile-acc: var(--dt-success);
        --tile-ink: oklch(46% 0.12 155);
        background: oklch(97% 0.025 155);
    }

    .detail-amount-tile[data-role='remaining'][data-tone='settled'] {
        --tile-acc: var(--dt-success);
        --tile-ink: oklch(46% 0.12 155);
        background: oklch(97% 0.025 155);
    }

    /* ===== Section cards & fields ===== */
    .detail-body-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.35fr) minmax(0, 1fr);
        gap: 14px;
        margin-bottom: 14px;
    }

    .detail-section-card {
        min-width: 0;
        padding: 15px 16px;
        background: var(--dt-paper);
        border: 1px solid var(--dt-rule);
        border-radius: 14px;
    }

    .detail-section-title {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 13px;
        color: var(--dt-ink);
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .detail-section-title::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--dt-rule);
    }

    .detail-section-icon {
        flex: 0 0 auto;
        width: 28px;
        height: 28px;
        display: grid;
        place-items: center;
        border-radius: 8px;
        background: oklch(95% 0.02 250);
        color: var(--dt-accent);
        font-size: 15px;
    }

    .detail-field-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px 14px;
    }

    .detail-sidebar-grid {
        display: grid;
        gap: 14px;
    }

    .detail-main-stack {
        display: grid;
        gap: 14px;
    }

    .detail-field-label {
        display: flex;
        align-items: center;
        gap: 5px;
        margin-bottom: 5px;
        color: var(--dt-muted);
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        white-space: nowrap;
    }

    .detail-field-label i { font-size: 13px; }

    .detail-field-value {
        width: 100%;
        padding: 9px 11px;
        background: var(--dt-paper-soft);
        border: 1px solid var(--dt-rule);
        border-radius: 9px;
        color: var(--dt-ink);
        font-size: 13px;
        font-weight: 600;
        line-height: 1.3;
    }

    .detail-field-value:focus {
        border-color: var(--dt-accent);
        box-shadow: 0 0 0 3px oklch(55% 0.18 255 / 0.15);
    }

    .detail-bank-value {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 11px 12px;
        background: oklch(96.5% 0.02 250);
        border: 1px solid var(--dt-rule);
        border-radius: 10px;
    }

    .detail-bank-value i {
        flex: 0 0 auto;
        font-size: 19px;
        color: var(--dt-accent);
    }

    .detail-bank-value .detail-field-value {
        padding: 0;
        background: transparent;
        border: 0;
    }

    .detail-bank-value .detail-field-value:focus { box-shadow: none; }

    /* ===== History table ===== */
    .detail-history-title {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
        color: var(--dt-ink);
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .detail-history-title::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--dt-rule);
    }

    .detail-history-table {
        margin: 0;
        --bs-table-bg: transparent;
        border: 1px solid var(--dt-rule);
        border-radius: 12px;
        overflow: hidden;
        width: 100%;
    }

    .detail-history-table thead th {
        padding: 9px 12px;
        background: var(--dt-paper-soft);
        border: 0;
        color: var(--dt-muted);
        font-size: 10.5px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        white-space: nowrap;
    }

    .detail-history-table tbody td {
        padding: 10px 12px;
        border-color: var(--dt-rule);
        color: var(--dt-ink);
        font-size: 12.5px;
        vertical-align: middle;
    }

    .detail-history-table tbody tr:hover { background: oklch(97.5% 0.008 250); }

    .detail-history-table .payment-money { font-weight: 700; }

    @media (max-width: 991.98px) {
        .detail-body-grid { grid-template-columns: minmax(0, 1fr); }
    }

    @media (max-width: 767.98px) {
        .detail-modal-hero { padding: 16px 18px; }
        .detail-modal-body { padding: 14px; }
        .detail-amount-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .detail-field-grid { grid-template-columns: minmax(0, 1fr); }
        .detail-status-pill { margin-left: auto; }
    }
</style>

<script>
    /* Dibagi oleh unpaid-payment-script & paid.blade.php untuk mewarnai status + tile uang
       sesuai nilai yang sudah diisi lewat .val() / setDetailField(). */
    function detailNumberValue(selector) {
        var el = $(selector);

        if (!el.length) {
            return 0;
        }

        return Number(String(el.val() || '0').replace(/[^\d]/g, '')) || 0;
    }

    function paintDetailTones() {
        if (!$('#detail-status-pill').length) {
            return;
        }

        var statusText = String($('#detail-payment-status').val() || '').toLowerCase();
        var tone = 'neutral';

        if (statusText.indexOf('lunas') !== -1 || statusText === 'paid') {
            tone = 'paid';
        } else if (statusText.indexOf('sebagian') !== -1 || statusText === 'partial') {
            tone = 'partial';
        } else if (statusText !== '' && statusText !== '-') {
            tone = 'pending';
        }

        $('#detail-status-pill').attr('data-tone', tone);

        $('#tile-paid').attr('data-tone', detailNumberValue('#detail-paid-amount') > 0 ? 'positive' : 'zero');
        $('#tile-remaining').attr('data-tone', detailNumberValue('#detail-remaining-amount') > 0 ? 'open' : 'settled');
    }
</script>

{{-- Form & Modal: review pembayaran multi-nota (lunas / DP / cicilan per nota) --}}
<form method="post" action="{{ route('vendor.invoice.payment.store') }}" id="batch-payment-form" novalidate>
    @csrf
    <div id="paymentPayloadContainer"></div>
    <div class="modal fade" id="payment-modal" tabindex="-1" role="dialog"
        aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-sm-down">
            <div class="modal-content payment-review-modal">
                <div class="modal-header align-items-start">
                    <div>
                        <div class="payment-step-label">Tahap akhir · periksa sebelum diproses</div>
                        <h4 class="modal-title mb-1" id="paymentModalLabel">Review Pembayaran Vendor</h4>
                        <p class="text-muted mb-0 fs-12" id="paymentModalSubtitle">Pastikan nominal setiap nota dan sumber dana sudah benar.</p>
                    </div>
                    <button class="btn-close py-0" type="button" data-bs-dismiss="modal"
                        aria-label="Tutup review pembayaran"></button>
                </div>

                <div class="modal-body p-0">
                    <div class="payment-guidance" role="note">
                        <i class="mdi mdi-shield-check-outline" aria-hidden="true"></i>
                        <div>
                            <strong>Satu submit menghasilkan satu kode pembayaran.</strong>
                            <span>Setiap nota memiliki alokasi sendiri. Sistem tidak lagi membagi satu nominal secara tersembunyi ke seluruh nota.</span>
                        </div>
                    </div>

                    <div class="row g-0">
                        <div class="col-lg-8 payment-review-main">
                            <fieldset class="mb-4">
                                <legend class="form-label fw-semibold mb-2">Cara pembayaran</legend>
                                <div class="payment-mode-switch" role="radiogroup" aria-label="Cara pembayaran nota">
                                    <input class="btn-check" type="radio" name="paymentMode" id="paymentModeFull" value="full" checked>
                                    <label for="paymentModeFull">
                                        <i class="mdi mdi-check-all" aria-hidden="true"></i>
                                        <span><strong>Lunasi semua nota</strong><small>Bayar seluruh sisa nota terpilih</small></span>
                                    </label>

                                    <input class="btn-check" type="radio" name="paymentMode" id="paymentModeCustom" value="custom">
                                    <label for="paymentModeCustom">
                                        <i class="mdi mdi-cash-edit" aria-hidden="true"></i>
                                        <span><strong>Nominal per nota</strong><small>DP/cicilan berbeda untuk setiap nota</small></span>
                                    </label>
                                </div>
                            </fieldset>

                            <div class="d-flex flex-wrap align-items-end justify-content-between gap-2 mb-2">
                                <div>
                                    <h6 class="mb-1 fw-semibold">Alokasi pembayaran</h6>
                                    <p class="text-muted fs-12 mb-0">Nominal maksimum adalah sisa tagihan masing-masing nota.</p>
                                </div>
                                <span class="badge text-bg-light border" id="paymentAllocationCount">0 nota</span>
                            </div>

                            <div class="table-responsive payment-allocation-table-wrap">
                                <table class="table payment-allocation-table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Nota / Vendor</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-end">Sisa Sebelum</th>
                                            <th style="min-width: 190px;">Bayar Sekarang</th>
                                            <th class="text-end">Sisa Setelah</th>
                                        </tr>
                                    </thead>
                                    <tbody id="paymentAllocationBody"></tbody>
                                </table>
                            </div>
                            <div class="alert alert-danger mt-3 mb-0 d-none" id="paymentAllocationError" role="alert"></div>
                        </div>

                        <div class="col-lg-4 payment-review-sidebar">
                            <div class="payment-total-block mb-4">
                                <span>Total transaksi</span>
                                <strong id="paymentGrandTotal">Rp 0</strong>
                                <small id="paymentAfterSummary">Sisa setelah pembayaran: Rp 0</small>
                            </div>

                            <dl class="payment-facts mb-4">
                                <div><dt>Nota</dt><dd id="paymentFactNotas">0</dd></div>
                                <div><dt>Vendor</dt><dd id="paymentFactVendors">0</dd></div>
                                <div><dt>Order</dt><dd id="paymentFactOrders">0</dd></div>
                                <div><dt>Sisa dipilih</dt><dd id="paymentFactRemaining">Rp 0</dd></div>
                            </dl>

                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="date">Tanggal pembayaran <span class="text-danger">*</span></label>
                                <input class="form-control" name="date" id="date" type="date"
                                    value="{{ now()->format('Y-m-d') }}" required>
                                <div class="invalid-feedback">Tanggal pembayaran wajib diisi.</div>
                            </div>

                            <div class="mb-2">
                                <div class="d-flex align-items-center justify-content-between gap-2">
                                    <label class="form-label fw-semibold mb-1" for="userBankCode">Sumber dana <span class="text-danger">*</span></label>
                                    <button class="btn btn-link btn-sm p-0 text-decoration-none d-none" type="button" id="reloadPaymentBanksBtn">
                                        <i class="mdi mdi-refresh" aria-hidden="true"></i> Muat ulang
                                    </button>
                                </div>
                                <select class="js-example-basic form-select" name="userBankCode" id="userBankCode" required disabled>
                                    <option value="">Memuat rekening...</option>
                                </select>
                                <div class="payment-bank-status" id="paymentBankStatus" role="status">Memuat rekening perusahaan...</div>
                                <div class="invalid-feedback">Pilih rekening sumber dana.</div>
                            </div>

                            <div>
                                <label class="form-label fw-semibold" for="description">Keterangan</label>
                                <textarea class="form-control" name="description" id="description" rows="3" maxlength="255"
                                    placeholder="Contoh: Pelunasan nota vendor periode September"></textarea>
                                <div class="d-flex justify-content-between text-muted fs-11 mt-1">
                                    <span>Opsional, berlaku untuk seluruh nota.</span>
                                    <span id="paymentDescriptionCount">0/255</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer payment-review-footer">
                    <div class="text-muted fs-12 me-auto" id="paymentSubmitHint">Periksa alokasi dan pilih sumber dana.</div>
                    <button class="btn btn-light" type="button" data-bs-dismiss="modal">Kembali</button>
                    <button class="btn btn-success" type="submit" id="submitPaymentBtn" disabled>
                        <span class="spinner-border spinner-border-sm me-1 d-none" id="paymentSubmitSpinner" aria-hidden="true"></span>
                        <i class="mdi mdi-bank-transfer-out me-1" id="paymentSubmitIcon" aria-hidden="true"></i>
                        <span id="paymentSubmitLabel">Proses Pembayaran</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- Modal: detail pembayaran vendor per nota --}}
<div class="modal fade detail-modal" id="detail-modal" tabindex="-1" role="dialog"
    aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content detail-modal-card">
            {{-- Hero --}}
            <div class="detail-modal-hero">
                <div class="detail-hero-icon" aria-hidden="true">
                    <i class="mdi mdi-receipt-text-outline"></i>
                </div>
                <div class="detail-hero-copy">
                    <span class="detail-hero-eyebrow">Vendor Invoice</span>
                    <h4 class="detail-hero-title" id="detailModalLabel">{{ __('menu_vendor_payment.payment_detail') }}</h4>
                    <span class="detail-hero-sub">Rincian tagihan nota, status, dan riwayat pembayaran vendor.</span>
                </div>
                <div class="detail-status-pill" id="detail-status-pill" data-tone="neutral" role="status">
                    <input class="detail-status-input" id="detail-payment-status" type="text" value="-" readonly tabindex="-1">
                </div>
                <button class="btn-close detail-hero-close" type="button" data-bs-dismiss="modal"
                    aria-label="Tutup"></button>
            </div>

            <div class="modal-body detail-modal-body">
                {{-- Ringkasan nominal --}}
                <div class="detail-amount-grid">
                    <div class="detail-amount-tile" data-role="billing">
                        <span class="detail-tile-label"><i class="mdi mdi-cash-multiple" aria-hidden="true"></i>Tagihan</span>
                        <input class="detail-tile-value" id="detail-billing-amount" type="text" value="-" readonly tabindex="-1">
                    </div>
                    <div class="detail-amount-tile" data-role="ppn">
                        <span class="detail-tile-label"><i class="mdi mdi-percent-outline" aria-hidden="true"></i>PPN</span>
                        <input class="detail-tile-value" id="detail-ppn-amount" type="text" value="-" readonly tabindex="-1">
                    </div>
                    <div class="detail-amount-tile" data-role="pph">
                        <span class="detail-tile-label"><i class="mdi mdi-percent" aria-hidden="true"></i>PPh</span>
                        <input class="detail-tile-value" id="detail-pph-amount" type="text" value="-" readonly tabindex="-1">
                    </div>
                    <div class="detail-amount-tile" data-role="claim" id="tile-claim">
                        <span class="detail-tile-label"><i class="mdi mdi-credit-card-off-outline" aria-hidden="true"></i>Claim</span>
                        <input class="detail-tile-value" id="detail-claim-amount" type="text" value="-" readonly tabindex="-1">
                    </div>
                    <div class="detail-amount-tile" data-role="paid" id="tile-paid">
                        <span class="detail-tile-label"><i class="mdi mdi-check-decagram-outline" aria-hidden="true"></i>Terbayar</span>
                        <input class="detail-tile-value" id="detail-paid-amount" type="text" value="-" readonly tabindex="-1">
                    </div>
                    <div class="detail-amount-tile" data-role="remaining" id="tile-remaining">
                        <span class="detail-tile-label"><i class="mdi mdi-alert-circle-outline" aria-hidden="true"></i>Sisa</span>
                        <input class="detail-tile-value" id="detail-remaining-amount" type="text" value="-" readonly tabindex="-1">
                    </div>
                </div>

                <div class="detail-body-grid">
                    <div class="detail-main-stack">
                        <div class="detail-section-card">
                            <div class="detail-section-title">
                                <span class="detail-section-icon"><i class="mdi mdi-file-document-outline" aria-hidden="true"></i></span>
                                Informasi Nota
                            </div>
                            <div class="detail-field-grid">
                                <div class="detail-field">
                                    <label class="detail-field-label">{{ __('menu_vendor_payment.payment_code') }}</label>
                                    <input class="detail-field-value" id="detail-code" type="text" value="-" readonly tabindex="-1">
                                </div>
                                <div class="detail-field">
                                    <label class="detail-field-label">Nomor Nota Kalender</label>
                                    <input class="detail-field-value" id="detail-nota-number" type="text" value="-" readonly tabindex="-1">
                                </div>
                                <div class="detail-field">
                                    <label class="detail-field-label">{{ __('menu_vendor_payment.order_code') }}</label>
                                    <input class="detail-field-value" id="detail-order-code" type="text" value="-" readonly tabindex="-1">
                                </div>
                                <div class="detail-field">
                                    <label class="detail-field-label">Shipment Number / No Pengiriman</label>
                                    <input class="detail-field-value" id="detail-shipment-number" type="text" value="-" readonly tabindex="-1">
                                </div>
                            </div>
                        </div>

                        <div class="detail-section-card">
                            <div class="detail-section-title">
                                <span class="detail-section-icon"><i class="mdi mdi-truck-outline" aria-hidden="true"></i></span>
                                Kendaraan &amp; Pihak Terkait
                            </div>
                            <div class="detail-field-grid">
                                <div class="detail-field">
                                    <label class="detail-field-label">{{ __('menu_vendor_payment.plate_number') }}</label>
                                    <input class="detail-field-value" id="detail-plate-number" type="text" value="-" readonly tabindex="-1">
                                </div>
                                <div class="detail-field">
                                    <label class="detail-field-label">Perusahaan Kendaraan</label>
                                    <input class="detail-field-value" id="detail-fleet-company" type="text" value="-" readonly tabindex="-1">
                                </div>
                                <div class="detail-field">
                                    <label class="detail-field-label">{{ __('menu_vendor_payment.driver') }}</label>
                                    <input class="detail-field-value" id="detail-driver" type="text" value="-" readonly tabindex="-1">
                                </div>
                                <div class="detail-field">
                                    <label class="detail-field-label">{{ __('menu_vendor_payment.customer') }}</label>
                                    <input class="detail-field-value" id="detail-customer" type="text" value="-" readonly tabindex="-1">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="detail-sidebar-grid">
                        <div class="detail-section-card">
                            <div class="detail-section-title">
                                <span class="detail-section-icon"><i class="mdi mdi-bank-outline" aria-hidden="true"></i></span>
                                {{ __('menu_vendor_payment.bank_source') }}
                            </div>
                            <div class="detail-bank-value">
                                <i class="mdi mdi-account-cash-outline" aria-hidden="true"></i>
                                <input class="detail-field-value" id="detail-bank" type="text" value="-" readonly tabindex="-1">
                            </div>
                        </div>

                        <div class="detail-section-card">
                            <div class="detail-section-title">
                                <span class="detail-section-icon"><i class="mdi mdi-note-text-outline" aria-hidden="true"></i></span>
                                {{ __('menu_vendor_payment.description') }}
                            </div>
                            <textarea class="detail-field-value" id="detail-description" rows="2" readonly tabindex="-1">-</textarea>
                        </div>
                    </div>
                </div>

                {{-- Riwayat pembayaran --}}
                <div class="detail-section-card">
                    <div class="detail-history-title">
                        <span class="detail-section-icon"><i class="mdi mdi-history" aria-hidden="true"></i></span>
                        Riwayat Pembayaran
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle detail-history-table" id="payment-history-table">
                            <thead>
                                <tr>
                                    <th class="text-nowrap">Tanggal</th>
                                    <th class="text-end">Jumlah</th>
                                    <th>Bank</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody id="payment-history-body"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Form untuk Cetak Multi PDF --}}
<form id="multi-pdf-form" method="post" action="{{ route('vendor.invoice.pdf-multi') }}" target="_blank"
    style="display: none;">
    @csrf
    <div id="multiPdfOrderCodesContainer"></div>
</form>

{{-- Form & Modal untuk Generate Nota (dengan input persentase PPN & PPh) --}}
<form id="generate-nota-form" method="post" action="{{ route('vendor.invoice.generate-nota') }}">
    @csrf
    <div class="modal fade bd-example-modal-lg" id="nota-modal" tabindex="-1" role="dialog"
        aria-labelledby="notaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content nota-modal-content">
                <div id="notaOrderCodesContainer"></div>

                {{-- Header gradient --}}
                <div class="nota-modal-header">
                    <div class="d-flex align-items-center gap-3 w-100">
                        <div class="nota-modal-header-icon">
                            <i class="mdi mdi-file-document-edit-outline"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h4 class="modal-title fw-bold mb-0" id="notaModalLabel">Generate Nota Pembayaran</h4>
                            <div class="nota-modal-header-sub">Nota / invoice resmi pembayaran ke vendor armada eksternal</div>
                        </div>
                        <button class="btn-close" type="button" data-bs-theme="dark"
                            data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>

                <div class="modal-body p-0">
                    {{-- Section: Ringkasan Order Terpilih --}}
                    <div class="nota-modal-section">
                        <div class="nota-modal-section-title">
                            <span class="nota-modal-section-badge bg-primary-subtle text-primary"><i
                                    class="mdi mdi-format-list-checks"></i></span>
                            Ringkasan Order Terpilih
                        </div>

                        <div class="row g-2 g-md-3 mb-3">
                            <div class="col-4">
                                <div class="nota-info-tile">
                                    <div class="nota-info-tile-label"><i class="mdi mdi-truck-fast-outline me-1"></i>Jumlah Order</div>
                                    <div class="nota-info-tile-value" id="notaOrderCount">-</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="nota-info-tile">
                                    <div class="nota-info-tile-label"><i class="mdi mdi-tag-outline me-1"></i>Format</div>
                                    <div class="nota-info-tile-value" id="notaOrderFormat">-</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="nota-info-tile">
                                    <div class="nota-info-tile-label"><i class="mdi mdi-office-building-outline me-1"></i>Vendor</div>
                                    <div class="nota-info-tile-value text-truncate" id="notaFleetCompanyName" title="">-</div>
                                </div>
                            </div>
                        </div>

                        <label class="form-label nota-field-label">Kode Order dalam Nota Ini</label>
                        <div class="nota-order-codes custom-scrollbar" id="notaOrderList">-</div>
                    </div>

                    {{-- Section: Rincian Nilai & Pajak (PPN/PPh input manual) --}}
                    <div class="nota-modal-section">
                        <div class="nota-modal-section-title">
                            <span class="nota-modal-section-badge bg-success-subtle text-success"><i
                                    class="mdi mdi-cash-multiple"></i></span>
                            Rincian Nilai Nota &amp; Pajak
                            <span class="nota-tax-hint ms-auto"><i class="mdi mdi-information-outline me-1"></i>Masukkan persentase, nominal dihitung otomatis</span>
                        </div>

                        {{-- Subtotal / DPP --}}
                        <div class="d-flex justify-content-between align-items-center nota-calc-row">
                            <div class="nota-calc-label"><i class="mdi mdi-sigma me-2 text-secondary"></i>Subtotal
                                (DPP)
                                <small class="d-block text-muted">Total tagihan order terpilih</small>
                            </div>
                            <div class="nota-calc-value fw-semibold" id="notaSubtotal">Rp 0</div>
                        </div>

                        {{-- PPN berdasarkan persentase --}}
                        <div class="nota-calc-row">
                            <div class="nota-calc-label mb-2"><label class="form-label nota-field-label mb-0"
                                    for="notaPpnRate"><i class="mdi mdi-percent-outline me-2 text-primary"></i>PPN
                                    (Pajak Pertambahan Nilai)</label></div>
                            <div class="input-group nota-tax-input-group">
                                <input type="text" class="form-control nota-tax-input" id="notaPpnRate"
                                    name="ppnRate" value="0" inputmode="decimal" autocomplete="off"
                                    placeholder="0">
                                <span class="input-group-text">%</span>
                                <span class="input-group-text nota-tax-preview" id="notaPpnPreview">Rp 0</span>
                            </div>
                            <small class="form-text text-muted">Masukkan rate PPN; nominal yang ditambahkan: <strong id="notaPpnAmountPreview">Rp 0</strong></small>
                        </div>

                        {{-- PPh berdasarkan persentase --}}
                        <div class="nota-calc-row">
                            <div class="nota-calc-label mb-2"><label class="form-label nota-field-label mb-0"
                                    for="notaPphRate"><i class="mdi mdi-cash-refund me-2 text-danger"></i>PPh
                                    (Pajak Penghasilan &mdash; dipotong)</label></div>
                            <div class="input-group nota-tax-input-group">
                                <input type="text" class="form-control nota-tax-input" id="notaPphRate"
                                    name="pphRate" value="0" inputmode="decimal" autocomplete="off"
                                    placeholder="0">
                                <span class="input-group-text">%</span>
                                <span class="input-group-text nota-tax-preview" id="notaPphPreview">Rp 0</span>
                            </div>
                            <small class="form-text text-muted">Masukkan rate PPh; nominal yang dipotong: <strong id="notaPphAmountPreview">Rp 0</strong></small>
                        </div>

                        {{-- Biaya Claim berdasarkan nominal --}}
                        <div class="nota-calc-row">
                            <div class="nota-calc-label mb-2"><label class="form-label nota-field-label mb-0"
                                    for="notaClaimAmount"><i class="mdi mdi-credit-card-off-outline me-2 text-warning"></i>Biaya Claim (dipotong)</label></div>
                            <input type="text" class="form-control nota-tax-input text-end" id="notaClaimAmount"
                                name="claimAmount" value="0" inputmode="numeric" autocomplete="off"
                                placeholder="0">
                            <small class="form-text text-muted">Nominal potongan yang mengurangi total bayar nota</small>
                        </div>

                        {{-- Grand total --}}
                        <div class="nota-grand-total">
                            <div class="nota-grand-total-label">
                                <i class="mdi mdi-cash-check me-2"></i>TOTAL BAYAR
                                <small class="d-block fw-normal">Subtotal + PPN &minus; PPh &minus; Claim</small>
                            </div>
                            <div class="nota-grand-total-value" id="notaGrandTotal">Rp 0</div>
                        </div>
                    </div>

                    {{-- Section: Akun Bank --}}
                    <div class="nota-modal-section">
                        <div class="nota-modal-section-title">
                            <span class="nota-modal-section-badge bg-warning-subtle text-warning"><i
                                    class="mdi mdi-bank-outline"></i></span>
                            Akun Bank Pembayaran <span class="text-danger">*</span>
                        </div>
                        <select class="js-example-basic form-select" name="userBankCode" id="notaUserBankCode" required>
                            <option value="">Pilih Bank</option>
                            <option value="" disabled>-- Loading data bank --</option>
                        </select>
                        <small class="form-text text-muted"><i class="mdi mdi-information-outline me-1"></i>Pilih
                            rekening perusahaan yang dituju untuk pembayaran nota ini</small>
                    </div>
                </div>

                <div class="modal-footer nota-modal-footer">
                    <div class="nota-modal-footer-info d-none d-md-block">
                        <i class="mdi mdi-alert-circle-outline me-1"></i>Order yang sudah di-nota tidak bisa dipindahkan
                        ke nota lain.
                    </div>
                    <div class="d-flex gap-2 ms-auto">
                        <button class="btn btn-light rounded-pill px-3" type="button" data-bs-dismiss="modal">
                            <i class="mdi mdi-close me-1"></i>Batal
                        </button>
                        <button class="btn btn-success rounded-pill px-4 text-white fw-semibold" type="submit">
                            <i class="mdi mdi-check-circle-outline me-1"></i>Generate Nota Sekarang!
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- Form untuk batal pembayaran (action di-set via JS) --}}
<form id="delete-form" method="post">
    @csrf
    @method('DELETE')
    <input type="hidden" name="expected_batch_code" id="cancel-payment-batch-code">
</form>

{{-- Form untuk Batal Nota (action di-set via JS) --}}
<form id="cancel-nota-form" method="post" style="display: none;">
    @csrf
</form>
