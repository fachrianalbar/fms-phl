{{-- Modals & hidden forms untuk menu Pembayaran Langsung.
    Di-include dari direct-payment/order/unpaid.blade.php (dan order/paid.blade.php).

    Isi partial:
      A. Modal pembayaran tunggal  : #payment-form + #payment-modal (order standalone)
      B. Modal detail order        : #detail-modal (+ badge nota #detail-nota-badge)
      C. Modal detail nota multi-DO: #nota-detail-modal
      D. Modal review bayar nota   : #batch-payment-form + #batch-payment-modal
      E. Modal generate nota       : #generate-nota-form + #nota-modal
      F. Form tersembunyi          : #pdf-multi-form, #delete-form, #cancel-nota-form

    Catatan ID: seluruh ID modal pembayaran tunggal & detail order dipertahankan
    sama dengan direct-payment/index.blade.php lama karena JS halaman
    (order/unpaid.blade.php) bergantung padanya. ID modal batch memakai prefix
    "batch" agar tidak bentrok dengan modal pembayaran tunggal. --}}

@include('direct-payment.partials.nota-modal-style')

{{-- Styling modal detail nota (kelas .detail-modal-* dipakai #nota-detail-modal),
    modal pembayaran tunggal (#payment-modal) & modal review pembayaran nota
    (#batch-payment-modal / .payment-review-modal). --}}
<style>
    /* =====================================================================
       1. Modal Detail Nota (.detail-modal-*) — dipakai #nota-detail-modal
       ===================================================================== */
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

    /* Grid nota detail memuat 6 tile (Tagihan/Terbayar/Sisa/PPN/PPh/Claim) */
    #nota-detail-modal .detail-amount-grid {
        grid-template-columns: repeat(6, minmax(0, 1fr));
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

    /* ===== Tables (daftar DO & riwayat) ===== */
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

    @media (max-width: 1199.98px) {
        #nota-detail-modal .detail-amount-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }

    @media (max-width: 991.98px) {
        .detail-body-grid { grid-template-columns: minmax(0, 1fr); }
    }

    @media (max-width: 767.98px) {
        .detail-modal-hero { padding: 16px 18px; }
        .detail-modal-body { padding: 14px; }
        .detail-amount-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        #nota-detail-modal .detail-amount-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .detail-field-grid { grid-template-columns: minmax(0, 1fr); }
        .detail-status-pill { margin-left: auto; }
    }

    /* =====================================================================
       2. Modal Pembayaran Tunggal (#payment-modal) & kartu receipt
       (dipakai juga kolom ringkasan keuangan #detail-modal)
       ===================================================================== */
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

    /* =====================================================================
       3. Modal Review Pembayaran Nota (#batch-payment-modal)
       ===================================================================== */
    .payment-review-modal {
        --vp-paper: oklch(100% 0 0);
        --vp-paper-soft: oklch(97% 0.008 250);
        --vp-ink: oklch(25% 0.025 255);
        --vp-muted: oklch(52% 0.025 255);
        --vp-rule: oklch(90% 0.018 250);
        --vp-accent: oklch(55% 0.18 255);
        --vp-accent-soft: oklch(95% 0.025 255);
        --vp-success: oklch(55% 0.14 155);
        --vp-success-soft: oklch(96% 0.03 155);
        --vp-danger: oklch(54% 0.2 25);
        --vp-focus: oklch(64% 0.18 250);
    }

    .payment-review-modal .payment-step-label {
        color: var(--vp-accent, oklch(55% 0.18 255));
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .07em;
    }

    .payment-review-modal .payment-guidance {
        display: flex;
        gap: 12px;
        padding: 13px 20px;
        background: var(--vp-success-soft, oklch(96% 0.03 155));
        border-bottom: 1px solid var(--vp-rule, oklch(90% 0.018 250));
        font-size: 12px;
    }

    .payment-review-modal .payment-guidance i {
        color: var(--vp-success, oklch(55% 0.14 155));
        font-size: 22px;
    }

    .payment-review-modal .payment-guidance strong,
    .payment-review-modal .payment-guidance span { display: block; }

    .payment-review-modal .payment-guidance span { color: var(--vp-muted, oklch(52% 0.025 255)); }

    .payment-review-main,
    .payment-review-sidebar { padding: 22px; }

    .payment-review-sidebar {
        background: var(--vp-paper-soft, oklch(97% 0.008 250));
        border-left: 1px solid var(--vp-rule, oklch(90% 0.018 250));
    }

    .payment-mode-switch {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .payment-mode-switch label {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
        min-height: 64px;
        padding: 12px 14px;
        border: 1px solid var(--vp-rule, oklch(90% 0.018 250));
        border-radius: 10px;
        cursor: pointer;
        background: var(--vp-paper, oklch(100% 0 0));
    }

    .payment-mode-switch label i {
        font-size: 23px;
        color: var(--vp-muted, oklch(52% 0.025 255));
    }

    .payment-mode-switch label span,
    .payment-mode-switch label small {
        display: block;
        min-width: 0;
    }

    .payment-mode-switch label small {
        margin-top: 2px;
        color: var(--vp-muted, oklch(52% 0.025 255));
        font-size: 11px;
    }

    .payment-mode-switch .btn-check:checked + label {
        border-color: var(--vp-accent, oklch(55% 0.18 255));
        background: var(--vp-accent-soft, oklch(95% 0.025 255));
        box-shadow: inset 0 0 0 1px var(--vp-accent, oklch(55% 0.18 255));
    }

    .payment-mode-switch .btn-check:focus-visible + label {
        outline: 3px solid var(--vp-focus, oklch(64% 0.18 250));
        outline-offset: 2px;
    }

    .payment-allocation-table-wrap {
        border: 1px solid var(--vp-rule, oklch(90% 0.018 250));
        border-radius: 10px;
    }

    .payment-allocation-table th {
        padding: 10px 12px;
        background: var(--vp-paper-soft, oklch(97% 0.008 250));
        color: var(--vp-muted, oklch(52% 0.025 255));
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: .04em;
        white-space: nowrap;
    }

    .payment-allocation-table td {
        padding: 12px;
        border-color: var(--vp-rule, oklch(90% 0.018 250));
        font-size: 12px;
    }

    .payment-allocation-table .allocation-vendor {
        display: block;
        margin-top: 2px;
        color: var(--vp-muted, oklch(52% 0.025 255));
        font-size: 11px;
    }

    .payment-allocation-table .allocation-amount-input {
        min-width: 155px;
        font-variant-numeric: tabular-nums;
    }

    .payment-allocation-table .allocation-message {
        display: block;
        min-height: 15px;
        margin-top: 3px;
        font-size: 10px;
    }

    .payment-money {
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .payment-total-block {
        padding: 16px;
        background: var(--vp-ink, oklch(25% 0.025 255));
        color: var(--vp-paper, oklch(100% 0 0));
        border-radius: 12px;
    }

    .payment-total-block span,
    .payment-total-block small {
        display: block;
        color: var(--vp-rule, oklch(90% 0.018 250));
    }

    .payment-total-block strong {
        display: block;
        margin: 5px 0;
        font-size: 24px;
        font-variant-numeric: tabular-nums;
    }

    .payment-facts { display: grid; gap: 8px; }

    .payment-facts div {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding-bottom: 8px;
        border-bottom: 1px solid var(--vp-rule, oklch(90% 0.018 250));
    }

    .payment-facts dt { color: var(--vp-muted, oklch(52% 0.025 255)); font-weight: 400; }

    .payment-facts dd {
        margin: 0;
        font-weight: 600;
        text-align: right;
        font-variant-numeric: tabular-nums;
    }

    .payment-bank-status {
        min-height: 18px;
        margin-top: 5px;
        color: var(--vp-muted, oklch(52% 0.025 255));
        font-size: 11px;
    }

    .payment-review-footer {
        position: sticky;
        bottom: 0;
        background: var(--vp-paper, oklch(100% 0 0));
        border-top: 1px solid var(--vp-rule, oklch(90% 0.018 250));
    }

    .payment-review-modal .form-control:focus-visible,
    .payment-review-modal .form-select:focus-visible {
        outline: 3px solid var(--vp-focus, oklch(64% 0.18 250));
        outline-offset: 2px;
        box-shadow: none;
    }

    @media (max-width: 991.98px) {
        .payment-review-sidebar {
            border-left: 0;
            border-top: 1px solid var(--vp-rule, oklch(90% 0.018 250));
        }
    }

    @media (max-width: 575.98px) {
        .payment-mode-switch { grid-template-columns: minmax(0, 1fr); }
        .payment-review-main,
        .payment-review-sidebar { padding: 16px; }
        .payment-review-footer { align-items: stretch; }
        .payment-review-footer #batchSubmitHint { width: 100%; }
    }

    @media (prefers-reduced-motion: reduce) {
        .payment-review-modal * { scroll-behavior: auto !important; transition-duration: 0.01ms !important; }
    }
</style>

{{-- =====================================================================
     A. MODAL PEMBAYARAN TUNGGAL (order standalone)
     Salinan #payment-form dari direct-payment/index.blade.php — hanya action
     form yang diubah ke route payment-single.
     ===================================================================== --}}
<form id="payment-form" method="post" action="{{ route('direct-payment.order.payment-single.store') }}">
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
                                            <i class="mdi mdi-tune-vertical-variant text-primary me-1"></i>Penyesuaian Pajak &amp; Potongan Claim
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

{{-- =====================================================================
     B. MODAL DETAIL ORDER (#detail-modal)
     Salinan dari direct-payment/index.blade.php + tambahan badge nota
     (#detail-nota-badge) yang ditampilkan bila order tergabung dalam nota.
     ===================================================================== --}}
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
                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill fs-11 d-none" id="detail-nota-badge"></span>
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
                                    <i class="mdi mdi-truck-delivery text-primary fs-18"></i>Detail Operasional &amp; Rute
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
                                            <span class="text-muted d-block fs-11 mb-1" id="detail-op-qty-title">Tipe Rute &amp; Qty</span>
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
                                                <th>Pajak &amp; Claim</th>
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

{{-- =====================================================================
     C. MODAL DETAIL NOTA MULTI-DO (#nota-detail-modal)
     Rincian satu nota (gabungan beberapa DO milik satu customer):
     ringkasan nominal, informasi nota, daftar DO, total & riwayat pembayaran.
     Seluruh field diisi oleh JS (showNotaDetailModal) dari endpoint
     ajax/direct-payment-nota-detail/{orderCode}.
     ===================================================================== --}}
<div class="modal fade detail-modal" id="nota-detail-modal" tabindex="-1" role="dialog"
    aria-labelledby="notaDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content detail-modal-card">
            {{-- Hero --}}
            <div class="detail-modal-hero">
                <div class="detail-hero-icon" aria-hidden="true">
                    <i class="mdi mdi-receipt-text-outline"></i>
                </div>
                <div class="detail-hero-copy">
                    <span class="detail-hero-eyebrow">Nota Pembayaran Langsung</span>
                    <h4 class="detail-hero-title" id="notaDetailModalLabel">Rincian Nota</h4>
                    <span class="detail-hero-sub">Rincian nota multi-DO customer, status pembayaran, dan riwayat transaksi.</span>
                </div>
                <div class="detail-status-pill" id="nota-detail-status-pill" data-tone="neutral" role="status">
                    <input class="detail-status-input" id="nota-detail-status" type="text" value="-" readonly tabindex="-1">
                </div>
                <button class="btn-close detail-hero-close" type="button" data-bs-dismiss="modal"
                    aria-label="Tutup"></button>
            </div>

            <div class="modal-body detail-modal-body">
                {{-- Nomor nota --}}
                <div class="d-flex align-items-center flex-wrap gap-2 mb-3">
                    <span class="badge text-bg-primary font-monospace px-3 py-2 fs-12" id="nota-detail-nota-number">-</span>
                </div>

                {{-- Ringkasan nominal --}}
                <div class="detail-amount-grid">
                    <div class="detail-amount-tile" data-role="billing">
                        <span class="detail-tile-label"><i class="mdi mdi-cash-multiple" aria-hidden="true"></i>Tagihan</span>
                        <input class="detail-tile-value" id="nota-detail-tagihan" type="text" value="-" readonly tabindex="-1">
                    </div>
                    <div class="detail-amount-tile" data-role="paid">
                        <span class="detail-tile-label"><i class="mdi mdi-check-decagram-outline" aria-hidden="true"></i>Terbayar</span>
                        <input class="detail-tile-value" id="nota-detail-terbayar" type="text" value="-" readonly tabindex="-1">
                    </div>
                    <div class="detail-amount-tile" data-role="remaining">
                        <span class="detail-tile-label"><i class="mdi mdi-alert-circle-outline" aria-hidden="true"></i>Sisa</span>
                        <input class="detail-tile-value" id="nota-detail-sisa" type="text" value="-" readonly tabindex="-1">
                    </div>
                    <div class="detail-amount-tile" data-role="ppn">
                        <span class="detail-tile-label"><i class="mdi mdi-percent-outline" aria-hidden="true"></i>PPN</span>
                        <input class="detail-tile-value" id="nota-detail-ppn" type="text" value="-" readonly tabindex="-1">
                    </div>
                    <div class="detail-amount-tile" data-role="pph">
                        <span class="detail-tile-label"><i class="mdi mdi-percent" aria-hidden="true"></i>PPh</span>
                        <input class="detail-tile-value" id="nota-detail-pph" type="text" value="-" readonly tabindex="-1">
                    </div>
                    <div class="detail-amount-tile" data-role="claim">
                        <span class="detail-tile-label"><i class="mdi mdi-credit-card-off-outline" aria-hidden="true"></i>Claim</span>
                        <input class="detail-tile-value" id="nota-detail-claim" type="text" value="-" readonly tabindex="-1">
                    </div>
                </div>

                <div class="detail-body-grid">
                    {{-- Informasi nota --}}
                    <div class="detail-main-stack">
                        <div class="detail-section-card">
                            <div class="detail-section-title">
                                <span class="detail-section-icon"><i class="mdi mdi-file-document-outline" aria-hidden="true"></i></span>
                                Informasi Nota
                            </div>
                            <div class="detail-field-grid">
                                <div class="detail-field">
                                    <label class="detail-field-label"><i class="mdi mdi-account-outline" aria-hidden="true"></i>Customer</label>
                                    <input class="detail-field-value" id="nota-detail-customer" type="text" value="-" readonly tabindex="-1">
                                </div>
                                <div class="detail-field">
                                    <label class="detail-field-label"><i class="mdi mdi-calendar-blank-outline" aria-hidden="true"></i>Tanggal Nota</label>
                                    <input class="detail-field-value" id="nota-detail-tanggal" type="text" value="-" readonly tabindex="-1">
                                </div>
                                <div class="detail-field">
                                    <label class="detail-field-label"><i class="mdi mdi-truck-fast-outline" aria-hidden="true"></i>Jumlah DO</label>
                                    <input class="detail-field-value" id="nota-detail-order-count" type="text" value="-" readonly tabindex="-1">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Bank nota --}}
                    <div class="detail-sidebar-grid">
                        <div class="detail-section-card">
                            <div class="detail-section-title">
                                <span class="detail-section-icon"><i class="mdi mdi-bank-outline" aria-hidden="true"></i></span>
                                Bank Nota
                            </div>
                            <div class="detail-bank-value">
                                <i class="mdi mdi-account-cash-outline" aria-hidden="true"></i>
                                <input class="detail-field-value" id="nota-detail-bank" type="text" value="-" readonly tabindex="-1">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Daftar DO dalam nota --}}
                <div class="detail-section-card mb-3">
                    <div class="detail-history-title">
                        <span class="detail-section-icon"><i class="mdi mdi-format-list-bulleted" aria-hidden="true"></i></span>
                        Daftar DO dalam Nota
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle detail-history-table" id="nota-detail-orders-table">
                            <thead>
                                <tr>
                                    <th class="text-nowrap">Kode Order</th>
                                    <th class="text-nowrap">Tanggal</th>
                                    <th class="text-nowrap">No SJ</th>
                                    <th class="text-nowrap">Nopol</th>
                                    <th class="text-nowrap">Driver</th>
                                    <th class="text-nowrap">Rute</th>
                                    <th class="text-end text-nowrap">Ongkos</th>
                                    <th class="text-end text-nowrap">Biaya Tamb.</th>
                                    <th class="text-end text-nowrap">PPN</th>
                                    <th class="text-end text-nowrap">PPh</th>
                                    <th class="text-end text-nowrap">Claim</th>
                                    <th class="text-end text-nowrap">Total</th>
                                    <th class="text-end text-nowrap">Terbayar</th>
                                    <th class="text-end text-nowrap">Sisa</th>
                                    <th class="text-center text-nowrap">Status</th>
                                </tr>
                            </thead>
                            <tbody id="nota-detail-orders-body"></tbody>
                            <tfoot id="nota-detail-orders-foot"></tfoot>
                        </table>
                    </div>
                </div>

                {{-- Riwayat pembayaran --}}
                <div class="detail-section-card">
                    <div class="detail-history-title">
                        <span class="detail-section-icon"><i class="mdi mdi-history" aria-hidden="true"></i></span>
                        Riwayat Pembayaran
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle detail-history-table" id="nota-detail-history-table">
                            <thead>
                                <tr>
                                    <th class="text-nowrap">Tanggal</th>
                                    <th class="text-nowrap">Tipe</th>
                                    <th class="text-nowrap">Bank</th>
                                    <th class="text-nowrap">Keterangan</th>
                                    <th class="text-center text-nowrap">Jumlah DO</th>
                                    <th class="text-end text-nowrap">Nominal</th>
                                </tr>
                            </thead>
                            <tbody id="nota-detail-history-body"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- =====================================================================
     D. MODAL REVIEW PEMBAYARAN NOTA (#batch-payment-form + #batch-payment-modal)
     Adaptasi modal review pembayaran vendor; seluruh ID diberi prefix
     "batch" agar tidak bentrok dengan modal pembayaran tunggal.
     ===================================================================== --}}
<form method="post" action="{{ route('direct-payment.order.payment.store') }}" id="batch-payment-form" novalidate>
    @csrf
    <div id="batchPaymentPayloadContainer"></div>
    <div class="modal fade" id="batch-payment-modal" tabindex="-1" role="dialog"
        aria-labelledby="batchPaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-sm-down">
            <div class="modal-content payment-review-modal">
                <div class="modal-header align-items-start">
                    <div>
                        <div class="payment-step-label">Tahap akhir · periksa sebelum diproses</div>
                        <h4 class="modal-title mb-1" id="batchPaymentModalLabel">Review Pembayaran Nota</h4>
                        <p class="text-muted mb-0 fs-12" id="batchPaymentModalSubtitle">Pastikan nominal setiap nota dan sumber dana sudah benar.</p>
                    </div>
                    <button class="btn-close py-0" type="button" data-bs-dismiss="modal"
                        aria-label="Tutup review pembayaran"></button>
                </div>

                <div class="modal-body p-0">
                    <div class="payment-guidance" role="note">
                        <i class="mdi mdi-shield-check-outline" aria-hidden="true"></i>
                        <div>
                            <strong>Satu submit menghasilkan satu kode pembayaran.</strong>
                            <span>Setiap nota memiliki alokasi nominal sendiri — lunasi sekaligus atau bayar DP/cicilan per nota.</span>
                        </div>
                    </div>

                    <div class="row g-0">
                        <div class="col-lg-8 payment-review-main">
                            <fieldset class="mb-4">
                                <legend class="form-label fw-semibold mb-2">Cara pembayaran</legend>
                                <div class="payment-mode-switch" role="radiogroup" aria-label="Cara pembayaran nota">
                                    <input class="btn-check" type="radio" name="paymentMode" id="batchPaymentModeFull" value="full" checked>
                                    <label for="batchPaymentModeFull">
                                        <i class="mdi mdi-check-all" aria-hidden="true"></i>
                                        <span><strong>Lunasi semua nota</strong><small>Bayar seluruh sisa nota terpilih</small></span>
                                    </label>

                                    <input class="btn-check" type="radio" name="paymentMode" id="batchPaymentModeCustom" value="custom">
                                    <label for="batchPaymentModeCustom">
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
                                <span class="badge text-bg-light border" id="batchPaymentAllocationCount">0 nota</span>
                            </div>

                            <div class="table-responsive payment-allocation-table-wrap">
                                <table class="table payment-allocation-table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Nota / Customer</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-end">Sisa Sebelum</th>
                                            <th style="min-width: 190px;">Bayar Sekarang</th>
                                            <th class="text-end">Sisa Setelah</th>
                                        </tr>
                                    </thead>
                                    <tbody id="batchPaymentAllocationBody"></tbody>
                                </table>
                            </div>
                            <div class="alert alert-danger mt-3 mb-0 d-none" id="batchPaymentAllocationError" role="alert"></div>
                        </div>

                        <div class="col-lg-4 payment-review-sidebar">
                            <div class="payment-total-block mb-4">
                                <span>Total transaksi</span>
                                <strong id="batchPaymentGrandTotal">Rp 0</strong>
                                <small id="batchPaymentAfterSummary">Sisa setelah pembayaran: Rp 0</small>
                            </div>

                            <dl class="payment-facts mb-4">
                                <div><dt>Nota</dt><dd id="batchPaymentFactNotas">0</dd></div>
                                <div><dt>Customer</dt><dd id="batchPaymentFactCustomers">0</dd></div>
                                <div><dt>DO</dt><dd id="batchPaymentFactOrders">0</dd></div>
                                <div><dt>Sisa dipilih</dt><dd id="batchPaymentFactRemaining">Rp 0</dd></div>
                            </dl>

                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="batchDate">Tanggal pembayaran <span class="text-danger">*</span></label>
                                <input class="form-control" name="date" id="batchDate" type="date"
                                    value="{{ now()->format('Y-m-d') }}" required>
                                <div class="invalid-feedback">Tanggal pembayaran wajib diisi.</div>
                            </div>

                            <div class="mb-2">
                                <div class="d-flex align-items-center justify-content-between gap-2">
                                    <label class="form-label fw-semibold mb-1" for="batchUserBankCode">Sumber dana <span class="text-danger">*</span></label>
                                    <button class="btn btn-link btn-sm p-0 text-decoration-none d-none" type="button" id="reloadBatchBanksBtn">
                                        <i class="mdi mdi-refresh" aria-hidden="true"></i> Muat ulang
                                    </button>
                                </div>
                                <select class="js-example-basic form-select" name="userBankCode" id="batchUserBankCode" required disabled>
                                    <option value="">Memuat rekening...</option>
                                </select>
                                <div class="payment-bank-status" id="batchBankStatus" role="status">Memuat rekening perusahaan...</div>
                                <div class="invalid-feedback">Pilih rekening sumber dana.</div>
                            </div>

                            <div>
                                <label class="form-label fw-semibold" for="batchDescription">Keterangan</label>
                                <textarea class="form-control" name="description" id="batchDescription" rows="3" maxlength="255"
                                    placeholder="Contoh: Pelunasan nota customer periode September"></textarea>
                                <div class="d-flex justify-content-between text-muted fs-11 mt-1">
                                    <span>Opsional, berlaku untuk seluruh nota.</span>
                                    <span id="batchDescriptionCount">0/255</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer payment-review-footer">
                    <div class="text-muted fs-12 me-auto" id="batchSubmitHint">Periksa alokasi dan pilih sumber dana.</div>
                    <button class="btn btn-light" type="button" data-bs-dismiss="modal">Kembali</button>
                    <button class="btn btn-success" type="submit" id="submitBatchPaymentBtn" disabled>
                        <span class="spinner-border spinner-border-sm me-1 d-none" id="batchSubmitSpinner" aria-hidden="true"></span>
                        <i class="mdi mdi-bank-transfer-in me-1" id="batchSubmitIcon" aria-hidden="true"></i>
                        <span id="batchSubmitLabel">Proses Pembayaran</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- =====================================================================
     E. MODAL GENERATE NOTA (#generate-nota-form + #nota-modal)
     Gabungkan beberapa DO milik satu customer menjadi satu nota DP/xxxx/yyyy.
     PPN & PPh diinput sebagai persentase, Biaya Claim sebagai nominal.
     ===================================================================== --}}
<form id="generate-nota-form" method="post" action="{{ route('direct-payment.order.generate-nota') }}">
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
                            <div class="nota-modal-header-sub">Nota pembayaran untuk customer (gabungan beberapa DO)</div>
                        </div>
                        <button class="btn-close" type="button" data-bs-theme="dark"
                            data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>

                <div class="modal-body p-0">
                    {{-- Section: Ringkasan DO Terpilih --}}
                    <div class="nota-modal-section">
                        <div class="nota-modal-section-title">
                            <span class="nota-modal-section-badge bg-primary-subtle text-primary"><i
                                    class="mdi mdi-format-list-checks"></i></span>
                            Ringkasan DO Terpilih
                        </div>

                        <div class="row g-2 g-md-3 mb-3">
                            <div class="col-6">
                                <div class="nota-info-tile">
                                    <div class="nota-info-tile-label"><i class="mdi mdi-truck-fast-outline me-1"></i>Jumlah DO</div>
                                    <div class="nota-info-tile-value" id="notaOrderCount">-</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="nota-info-tile">
                                    <div class="nota-info-tile-label"><i class="mdi mdi-account-group-outline me-1"></i>Customer</div>
                                    <div class="nota-info-tile-value text-truncate" id="notaCustomerName" title="">-</div>
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
                                <small class="d-block text-muted">Total harga rute + biaya tambahan DO terpilih</small>
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
                            <textarea class="form-control form-control-sm mt-2" id="notaClaimDescription"
                                name="claimDescription" rows="2" maxlength="255"
                                placeholder="Contoh: Klaim keterlambatan pengiriman"></textarea>
                            <small class="form-text text-muted">Keterangan potongan claim (opsional)</small>
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
                        <i class="mdi mdi-alert-circle-outline me-1"></i>Order yang sudah masuk nota tidak bisa dipindah
                        ke nota lain. Order yang sudah dibayar tidak bisa digabung ke nota.
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

{{-- =====================================================================
     F. FORM TERSEMBUNYI
     ===================================================================== --}}
{{-- Cetak multi PDF nota order terpilih (orderCodes[] diisi via JS) --}}
<form id="pdf-multi-form" action="{{ route('direct-payment.pdf-multi') }}" method="POST" target="_blank" class="d-none">
    @csrf
    <div id="multiPdfOrderCodesContainer"></div>
</form>

{{-- Batal batch pembayaran nota (action & expected_batch_code di-set via JS) --}}
<form id="delete-form" method="post">
    @csrf
    @method('DELETE')
    <input type="hidden" name="expected_batch_code" id="cancel-payment-batch-code">
</form>

{{-- Batal nota (action di-set via JS) --}}
<form id="cancel-nota-form" method="post" style="display: none;">
    @csrf
</form>
