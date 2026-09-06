@extends('layouts.main', [
'title' => $title,
'pageTitle' => $title,
'firstSegment' => 'Vendor',
'secondSegment' => 'Invoice Belum Lunas',
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

@include('vendor.invoice.partials.table-style')
<style>
    /* Hallmark · genre: modern-minimal · macrostructure: Workbench · tone: utilitarian · designed-as-app */
    .vendor-payment-workbench {
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
        --vp-warning-soft: oklch(96% 0.05 90);
        --vp-focus: oklch(64% 0.18 250);
        color: var(--vp-ink);
    }

    .vendor-payment-workbench .workbench-intro {
        display: grid;
        grid-template-columns: minmax(0, 1.35fr) minmax(320px, .65fr);
        gap: 24px;
        padding: 22px 24px;
        margin-bottom: 18px;
        background: var(--vp-paper);
        border: 1px solid var(--vp-rule);
        border-radius: 14px;
    }

    .vendor-payment-workbench .workbench-eyebrow {
        color: var(--vp-accent);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .vendor-payment-workbench .payment-steps {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        align-self: center;
    }

    .vendor-payment-workbench .payment-step {
        min-width: 0;
        padding: 12px;
        background: var(--vp-paper-soft);
        border-left: 3px solid var(--vp-rule);
        border-radius: 0 10px 10px 0;
    }

    .vendor-payment-workbench .payment-step strong,
    .vendor-payment-workbench .payment-step span {
        display: block;
    }

    .vendor-payment-workbench .payment-step strong {
        font-size: 12px;
        margin-bottom: 3px;
    }

    .vendor-payment-workbench .payment-step span {
        color: var(--vp-muted);
        font-size: 11px;
        line-height: 1.35;
    }

    .vendor-payment-workbench .overview-strip {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        margin-bottom: 18px;
        background: var(--vp-paper);
        border: 1px solid var(--vp-rule);
        border-radius: 14px;
        overflow: hidden;
    }

    .vendor-payment-workbench .overview-item {
        min-width: 0;
        padding: 17px 20px;
        border-right: 1px solid var(--vp-rule);
    }

    .vendor-payment-workbench .overview-item:last-child { border-right: 0; }
    .vendor-payment-workbench .overview-item span,
    .vendor-payment-workbench .overview-item small { display: block; }
    .vendor-payment-workbench .overview-item span { color: var(--vp-muted); font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
    .vendor-payment-workbench .overview-item strong { display: block; margin: 5px 0 2px; font-size: 19px; font-variant-numeric: tabular-nums; }
    .vendor-payment-workbench .overview-item small { color: var(--vp-muted); font-size: 11px; }
    .vendor-payment-workbench .overview-item.is-emphasis { background: var(--vp-warning-soft); }
    .vendor-payment-workbench .overview-item.is-emphasis strong { color: var(--vp-danger); }

    .vendor-payment-workbench .selection-empty-help,
    .vendor-payment-workbench .selection-command-bar {
        margin-bottom: 18px;
        border-radius: 12px;
    }

    .vendor-payment-workbench .selection-empty-help {
        padding: 12px 16px;
        background: var(--vp-accent-soft);
        border: 1px dashed var(--vp-accent);
        color: var(--vp-muted);
    }

    .vendor-payment-workbench .selection-command-bar {
        position: sticky;
        top: 74px;
        z-index: 1010;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 13px 16px;
        background: var(--vp-ink);
        color: var(--vp-paper);
        box-shadow: 0 10px 28px oklch(20% 0.025 255 / .16);
    }

    .vendor-payment-workbench .selection-command-bar .selection-facts {
        display: flex;
        flex-wrap: wrap;
        gap: 8px 18px;
        font-size: 12px;
    }

    .vendor-payment-workbench .selection-command-bar .selection-facts strong { font-size: 14px; }
    .vendor-payment-workbench .selection-command-bar .selection-facts span { color: var(--vp-rule); }
    .vendor-payment-workbench .selection-actions { display: flex; flex-wrap: wrap; gap: 8px; }
    .vendor-payment-workbench .selection-command-bar .btn-light { color: var(--vp-ink); }

    .payment-review-modal .payment-step-label { color: var(--vp-accent, oklch(55% 0.18 255)); font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; }
    .payment-review-modal .payment-guidance { display: flex; gap: 12px; padding: 13px 20px; background: var(--vp-success-soft, oklch(96% 0.03 155)); border-bottom: 1px solid var(--vp-rule, oklch(90% 0.018 250)); font-size: 12px; }
    .payment-review-modal .payment-guidance i { color: var(--vp-success, oklch(55% 0.14 155)); font-size: 22px; }
    .payment-review-modal .payment-guidance strong,
    .payment-review-modal .payment-guidance span { display: block; }
    .payment-review-modal .payment-guidance span { color: var(--vp-muted, oklch(52% 0.025 255)); }
    .payment-review-main, .payment-review-sidebar { padding: 22px; }
    .payment-review-sidebar { background: var(--vp-paper-soft, oklch(97% 0.008 250)); border-left: 1px solid var(--vp-rule, oklch(90% 0.018 250)); }

    .payment-mode-switch { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
    .payment-mode-switch label { display: flex; align-items: center; gap: 10px; min-width: 0; min-height: 64px; padding: 12px 14px; border: 1px solid var(--vp-rule, oklch(90% 0.018 250)); border-radius: 10px; cursor: pointer; background: var(--vp-paper, oklch(100% 0 0)); }
    .payment-mode-switch label i { font-size: 23px; color: var(--vp-muted, oklch(52% 0.025 255)); }
    .payment-mode-switch label span,
    .payment-mode-switch label small { display: block; min-width: 0; }
    .payment-mode-switch label small { margin-top: 2px; color: var(--vp-muted, oklch(52% 0.025 255)); font-size: 11px; }
    .payment-mode-switch .btn-check:checked + label { border-color: var(--vp-accent, oklch(55% 0.18 255)); background: var(--vp-accent-soft, oklch(95% 0.025 255)); box-shadow: inset 0 0 0 1px var(--vp-accent, oklch(55% 0.18 255)); }
    .payment-mode-switch .btn-check:focus-visible + label { outline: 3px solid var(--vp-focus, oklch(64% 0.18 250)); outline-offset: 2px; }

    .payment-allocation-table-wrap { border: 1px solid var(--vp-rule, oklch(90% 0.018 250)); border-radius: 10px; }
    .payment-allocation-table th { padding: 10px 12px; background: var(--vp-paper-soft, oklch(97% 0.008 250)); color: var(--vp-muted, oklch(52% 0.025 255)); font-size: 10px; text-transform: uppercase; letter-spacing: .04em; white-space: nowrap; }
    .payment-allocation-table td { padding: 12px; border-color: var(--vp-rule, oklch(90% 0.018 250)); font-size: 12px; }
    .payment-allocation-table .allocation-vendor { display: block; margin-top: 2px; color: var(--vp-muted, oklch(52% 0.025 255)); font-size: 11px; }
    .payment-allocation-table .allocation-amount-input { min-width: 155px; font-variant-numeric: tabular-nums; }
    .payment-allocation-table .allocation-message { display: block; min-height: 15px; margin-top: 3px; font-size: 10px; }
    .payment-money { font-variant-numeric: tabular-nums; white-space: nowrap; }

    .payment-total-block { padding: 16px; background: var(--vp-ink, oklch(25% 0.025 255)); color: var(--vp-paper, oklch(100% 0 0)); border-radius: 12px; }
    .payment-total-block span, .payment-total-block small { display: block; color: var(--vp-rule, oklch(90% 0.018 250)); }
    .payment-total-block strong { display: block; margin: 5px 0; font-size: 24px; font-variant-numeric: tabular-nums; }
    .payment-facts { display: grid; gap: 8px; }
    .payment-facts div { display: flex; justify-content: space-between; gap: 12px; padding-bottom: 8px; border-bottom: 1px solid var(--vp-rule, oklch(90% 0.018 250)); }
    .payment-facts dt { color: var(--vp-muted, oklch(52% 0.025 255)); font-weight: 400; }
    .payment-facts dd { margin: 0; font-weight: 600; text-align: right; font-variant-numeric: tabular-nums; }
    .payment-bank-status { min-height: 18px; margin-top: 5px; color: var(--vp-muted, oklch(52% 0.025 255)); font-size: 11px; }
    .payment-review-footer { position: sticky; bottom: 0; background: var(--vp-paper, oklch(100% 0 0)); border-top: 1px solid var(--vp-rule, oklch(90% 0.018 250)); }

    .vendor-payment-workbench .invoice-table-scroll {
        width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
    }
    .vendor-payment-workbench .invoice-table-scroll .invoice-table {
        min-width: 1180px;
        width: max-content !important;
    }
    .vendor-payment-workbench .invoice-table tbody td:nth-child(n+9):nth-child(-n+13) { font-variant-numeric: tabular-nums; text-align: right; white-space: nowrap; }
    .vendor-payment-workbench .form-check-input:focus-visible,
    .vendor-payment-workbench .btn:focus-visible,
    .payment-review-modal .form-control:focus-visible,
    .payment-review-modal .form-select:focus-visible { outline: 3px solid var(--vp-focus, oklch(64% 0.18 250)); outline-offset: 2px; box-shadow: none; }

    /* Select2 untuk dropdown "Tampilkan _MENU_ data" milik DataTables */
    .vendor-payment-workbench .dataTables_length .select2-container { width: 88px !important; }
    .vendor-payment-workbench .dataTables_length .select2-container .select2-selection--single {
        height: 34px !important;
        padding: 4px 10px !important;
        border-radius: 8px !important;
    }
    .vendor-payment-workbench .dataTables_length .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 24px !important;
        font-size: 13px !important;
    }
    .vendor-payment-workbench .dataTables_length .select2-container--default .select2-selection--single .select2-selection__arrow { height: 32px !important; }

    @media (max-width: 991.98px) {
        .vendor-payment-workbench .workbench-intro { grid-template-columns: minmax(0, 1fr); }
        .vendor-payment-workbench .overview-strip { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .vendor-payment-workbench .overview-item:nth-child(2) { border-right: 0; }
        .vendor-payment-workbench .overview-item:nth-child(-n+2) { border-bottom: 1px solid var(--vp-rule); }
        .payment-review-sidebar { border-left: 0; border-top: 1px solid var(--vp-rule, oklch(90% 0.018 250)); }
    }

    @media (max-width: 575.98px) {
        .vendor-payment-workbench .workbench-intro { padding: 18px; }
        .vendor-payment-workbench .payment-steps,
        .vendor-payment-workbench .overview-strip,
        .payment-mode-switch { grid-template-columns: minmax(0, 1fr); }
        .vendor-payment-workbench .overview-item { border-right: 0; border-bottom: 1px solid var(--vp-rule); }
        .vendor-payment-workbench .overview-item:last-child { border-bottom: 0; }
        .vendor-payment-workbench .selection-command-bar { top: 8px; align-items: stretch; flex-direction: column; }
        .vendor-payment-workbench .selection-actions { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .vendor-payment-workbench .selection-actions .btn:last-child { grid-column: 1 / -1; }
        .payment-review-main, .payment-review-sidebar { padding: 16px; }
        .payment-review-footer { align-items: stretch; }
        .payment-review-footer #paymentSubmitHint { width: 100%; }
    }

    @media (prefers-reduced-motion: reduce) {
        .vendor-payment-workbench *, .payment-review-modal * { scroll-behavior: auto !important; transition-duration: 0.01ms !important; }
    }
</style>
@endpush

@section('content')
<div class="col-sm-12 vendor-payment-workbench">
    <section class="workbench-intro" aria-labelledby="paymentWorkflowTitle">
        <div>
            <span class="workbench-eyebrow">Pembayaran vendor</span>
            <h4 class="fw-bold mt-1 mb-2" id="paymentWorkflowTitle">Pilih, review, lalu proses</h4>
            <p class="text-muted mb-3">Pilih satu atau beberapa nota. Pada tahap review, Anda dapat melunasi semuanya atau mengatur nominal DP/cicilan berbeda untuk setiap nota.</p>
            <a href="{{ route('vendor.order.waiting') }}" class="btn btn-outline-primary btn-sm fw-semibold">
                <i class="mdi mdi-tray-full me-1" aria-hidden="true"></i> Order Menunggu Nota
            </a>
        </div>
        <div class="payment-steps" aria-label="Tiga langkah pembayaran">
            <div class="payment-step"><strong>1 · Pilih nota</strong><span>Pilihan tetap tersimpan saat berpindah halaman tabel.</span></div>
            <div class="payment-step"><strong>2 · Review nominal</strong><span>Periksa alokasi, sisa, tanggal, dan sumber dana.</span></div>
            <div class="payment-step"><strong>3 · Konfirmasi</strong><span>Sistem membuat satu kode pembayaran untuk seluruh pilihan.</span></div>
        </div>
    </section>

    {{-- Flash message (success/fail/error) kini ditampilkan sebagai dialog SweetAlert2
        lewat partial flash-swal (di-include pada @push('script') di bawah). --}}

    <section class="overview-strip" aria-label="Ringkasan invoice vendor">
        <div class="overview-item">
            <span>Nota belum lunas</span>
            <strong>{{ number_format($stats['notaCount'] ?? 0) }} Nota</strong>
            <small>{{ $stats['pendingCount'] ?? 0 }} belum dibayar · {{ $stats['partialCount'] ?? 0 }} sebagian</small>
        </div>
        <div class="overview-item">
            <span>Order dalam nota</span>
            <strong>{{ number_format($stats['orderCount'] ?? 0) }} Order</strong>
            <small>Vendor armada eksternal</small>
        </div>
        <div class="overview-item">
            <span>Sudah terbayar</span>
            <strong>Rp {{ number_format($stats['totalPaid'] ?? 0, 0, ',', '.') }}</strong>
            <small>Dari nota yang belum lunas</small>
        </div>
        <div class="overview-item is-emphasis">
            <span>Sisa harus dibayar</span>
            <strong>Rp {{ number_format($stats['totalRemaining'] ?? 0, 0, ',', '.') }}</strong>
            <small>Total tagihan Rp {{ number_format($stats['totalBilling'] ?? 0, 0, ',', '.') }}</small>
        </div>
    </section>

    <div class="selection-empty-help" id="selectionEmptyHelp">
        <i class="mdi mdi-checkbox-marked-outline me-1" aria-hidden="true"></i>
        Centang nota pada tabel untuk pembayaran (bisa banyak nota). Cetak nota dilakukan lewat tombol aksi pada tiap baris.
    </div>

    <div class="selection-command-bar d-none" id="selectionCommandBar" aria-live="polite">
        <div>
            <strong id="selectionHeadline">0 nota dipilih</strong>
            <div class="selection-facts mt-1">
                <span id="selectionVendorFact">0 vendor</span>
                <span id="selectionOrderFact">0 order</span>
                <span id="selectionRemainingFact">Sisa Rp 0</span>
            </div>
        </div>
        <div class="selection-actions">
            <button type="button" class="btn btn-outline-light btn-sm" id="clearSelectionBtn">
                <i class="mdi mdi-close me-1" aria-hidden="true"></i>Bersihkan
            </button>
            <button type="button" class="btn btn-success btn-sm fw-semibold" id="openPaymentModalBtn">
                <i class="mdi mdi-bank-transfer-out me-1" aria-hidden="true"></i>Review Pembayaran
            </button>
        </div>
    </div>

    <!-- Nota Belum Lunas (1 baris = 1 nota hasil generate di Order Menunggu Nota) -->
    <div class="table-container-card">
        <div class="table-top-bar d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h6 class="fw-bold text-dark mb-1">Nota Belum Lunas</h6>
                <div class="text-muted fs-12">
                    <i class="mdi mdi-information-outline me-1 text-primary"></i>Satu nota dapat memuat beberapa order. Pembayaran dapat dilunasi atau DP/cicilan. Order baru digabungkan lewat menu <strong>Order Menunggu Nota</strong>.
                </div>
            </div>
        </div>
        <div class="card-body p-3">
            <div class="invoice-table-scroll custom-scrollbar">
                <table class="table table-striped nowrap invoice-table" id="dtUnpaid">
                    <thead>
                        <tr>
                            <th class="text-center"><input class="form-check-input" type="checkbox" id="selectAllNotas" aria-label="Pilih semua nota pada halaman tabel ini" title="Pilih semua nota pada halaman ini"></th>
                            <th class="text-center" style="width: 170px;">Aksi</th>
                            <th class="text-center" style="width: 45px;">No</th>
                            <th>No Nota</th>
                            <th>Tanggal Nota</th>
                            <th>Vendor (Perusahaan Kendaraan)</th>
                            <th class="text-center">Jumlah Order</th>
                            <th>Nopol</th>
                            <th class="text-end">Tagihan</th>
                            <th class="text-end">PPN</th>
                            <th class="text-end">PPh</th>
                            <th class="text-end">Claim</th>
                            <th class="text-end">Terbayar</th>
                            <th class="text-end">Sisa</th>
                            <th class="text-center">Status Bayar</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@include('vendor.invoice.partials.modals')
@endsection

@push('script')
<script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/js/sweet-alert/sweetalert2.min.js') }}"></script>
<script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>

{{-- Flash message server → SweetAlert2 (harus setelah sweetalert2.min.js) --}}
@include('vendor.invoice.partials.flash-swal')

@include('vendor.invoice.partials.unpaid-payment-script')
@endpush
