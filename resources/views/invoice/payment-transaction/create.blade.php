@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Faktur',
    'secondSegment' => 'Transaksi Pembayaran',
])

@push('style')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/select2.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom-select2.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/sweetalert2.css') }}">

@include('invoice.partials.table-style')

<style>
    /* Styling Eksekutif Modern untuk Transaksi Pembayaran */
    :root {
        --trx-primary: #2563eb;
        --trx-primary-soft: #eff6ff;
        --trx-success: #10b981;
        --trx-success-soft: #ecfdf5;
        --trx-warning: #f59e0b;
        --trx-warning-soft: #fffbeb;
        --trx-danger: #ef4444;
        --trx-danger-soft: #fef2f2;
    }

    .card-modern {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 4px 18px -2px rgba(15, 23, 42, 0.05);
        background: #ffffff;
        transition: all 0.2s ease;
    }

    .card-header-modern {
        background: #ffffff;
        border-bottom: 1px solid #f1f5f9;
        padding: 1.15rem 1.4rem;
        border-top-left-radius: 14px !important;
        border-top-right-radius: 14px !important;
    }

    .step-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.76rem;
        font-weight: 700;
        padding: 0.32rem 0.75rem;
        border-radius: 20px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    /* Customer Quick Info Box */
    .customer-summary-banner {
        background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
        border: 1px solid #cbd5e1;
        border-left: 4px solid var(--trx-primary);
        border-radius: 12px;
        padding: 1rem 1.25rem;
        animation: fadeIn 0.25s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-4px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Table Toolbar */
    .table-toolbar {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.75rem 1rem;
        margin-bottom: 1rem;
    }

    .table-search-input {
        border-radius: 20px !important;
        padding: 0.4rem 1rem 0.4rem 2.2rem !important;
        border: 1px solid #cbd5e1 !important;
        font-size: 13px !important;
        background-color: #ffffff !important;
        min-width: 250px;
        transition: all 0.2s ease;
    }

    .table-search-input:focus {
        border-color: var(--trx-primary) !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important;
    }

    /* Row States */
    #dt-invoices tbody tr {
        transition: background-color 0.15s ease;
    }

    #dt-invoices tbody tr.row-selected {
        background-color: rgba(37, 99, 235, 0.04) !important;
    }

    #dt-invoices tbody tr.row-disabled {
        opacity: 0.55;
        background-color: #fafafa !important;
    }

    /* Currency Inputs */
    .currency-input-group .input-group-text {
        background-color: #f1f5f9;
        border-color: #cbd5e1;
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
        padding: 0.35rem 0.55rem;
    }

    .currency-input {
        text-align: right;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
        font-size: 13px;
        font-weight: 600;
        border-color: #cbd5e1;
    }

    .currency-input:focus {
        border-color: var(--trx-primary);
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15);
    }

    .btn-quick-fill {
        padding: 0.25rem 0.5rem;
        font-size: 11px;
        font-weight: 700;
        border-radius: 6px;
        line-height: 1.2;
    }

    /* Sticky Executive Summary Card */
    .sticky-summary-sidebar {
        position: sticky;
        top: 85px;
        z-index: 10;
    }

    .summary-card {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 8px 24px -4px rgba(15, 23, 42, 0.08);
        background: #ffffff;
    }

    .summary-card-header {
        background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
        padding: 1.25rem 1.4rem;
        color: #ffffff;
        position: relative;
    }

    .summary-metric-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.65rem 0;
        border-bottom: 1px dashed #e2e8f0;
        font-size: 13px;
    }

    .summary-metric-row:last-child {
        border-bottom: none;
    }

    /* Grand Total Callout Box */
    .grand-total-callout {
        background: linear-gradient(145deg, #ecfdf5 0%, #d1fae5 100%);
        border: 1.5px solid #6ee7b7;
        border-radius: 12px;
        padding: 1.15rem 1.25rem;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.08);
        transition: all 0.2s ease;
    }

    .grand-total-value {
        font-size: 1.65rem;
        font-weight: 800;
        color: #065f46;
        letter-spacing: -0.02em;
        line-height: 1.15;
    }

    /* File Upload Box */
    .custom-file-box {
        border: 1.5px dashed #cbd5e1;
        border-radius: 10px;
        padding: 0.75rem 1rem;
        background: #f8fafc;
        transition: all 0.2s ease;
        position: relative;
    }

    .custom-file-box:hover {
        border-color: var(--trx-primary);
        background: #f0f7ff;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">
    <!-- Top Action Header Banner -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="text-white rounded-3 d-flex align-items-center justify-content-center shadow-sm"
                style="width: 48px; height: 48px; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;">
                <i class="mdi mdi-cash-register fs-24"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    Transaksi Pembayaran Baru
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill fs-11 px-2 py-0">
                        Multi-Faktur & Claim
                    </span>
                </h4>
                <p class="text-muted mb-0 fs-12">Catat satu transaksi transfer dana untuk melunasi banyak faktur sekaligus serta mencatat potongan/claim tagihan.</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('invoice.payment-transaction.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm">
                <i class="mdi mdi-arrow-left me-1"></i> {{ __('general.back_to_list') }}
            </a>
        </div>
    </div>

    @include('partials.alert')

    <form method="post" action="{{ route('invoice.payment-transaction.store') }}" enctype="multipart/form-data" id="trx-form">
        @csrf

        <!-- Row 1: Informasi Transaksi (Col 8 / Col 7) + Ringkasan Pembayaran (Col 4 / Col 5) -->
        <div class="row g-4 mb-4">
            <!-- Left Column: Informasi Transaksi -->
            <div class="col-xl-8 col-lg-7">
                <div class="card card-modern h-100 mb-0">
                    <div class="card-header card-header-modern d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="step-pill bg-primary-subtle text-primary border border-primary-subtle">
                                <i class="mdi mdi-numeric-1-circle fs-14"></i> Langkah 1
                            </span>
                            <h5 class="mb-0 fw-bold text-dark fs-15">Informasi Transaksi & Bank Penerima</h5>
                        </div>
                        <span class="badge bg-light text-muted border px-2 py-1 fs-11">Wajib diisi</span>
                    </div>
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div class="row g-3">
                            <!-- Customer Selection -->
                            <div class="col-md-6 position-relative">
                                <label class="form-label fw-semibold text-dark fs-13" for="customerCode">
                                    Customer / Pelanggan <i class="icofont icofont-warning-alt text-danger"></i>
                                </label>
                                <select class="js-example-basic-single form-select" id="customerCode" name="customerCode" required>
                                    <option selected disabled value="">{{ __('general.choose') }} Customer...</option>
                                    @foreach ($customer as $item)
                                    <option value="{{ $item->code }}" {{ old('customerCode') == $item->code ? 'selected' : '' }}>
                                        {{ $item->name }} ({{ $item->code }})
                                    </option>
                                    @endforeach
                                </select>
                                <div class="form-text text-muted fs-11">Pilih customer untuk otomatis memuat daftar tagihan belum lunas.</div>
                            </div>

                            <!-- Payment Date -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark fs-13" for="paymentDate">
                                    Tanggal Pembayaran <i class="icofont icofont-warning-alt text-danger"></i>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted border-end-0">
                                        <i class="mdi mdi-calendar-range"></i>
                                    </span>
                                    <input class="form-control border-start-0" name="paymentDate" id="paymentDate" type="date" required
                                        value="{{ old('paymentDate', now()->toDateString()) }}">
                                </div>
                            </div>

                            <!-- Customer Summary Info (Dynamic) -->
                            <div class="col-12" id="customer-summary-container" style="display: none;">
                                <div class="customer-summary-banner">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                        <div>
                                            <span class="text-muted fs-11 fw-bold text-uppercase letter-spacing-1">Customer Terpilih:</span>
                                            <h6 class="fw-bold text-primary mb-0 fs-14" id="banner-customer-name">-</h6>
                                        </div>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="text-end">
                                                <div class="text-muted fs-11">Total Faktur Terbuka:</div>
                                                <div class="fw-bold text-dark fs-13" id="banner-invoice-count">0 Faktur</div>
                                            </div>
                                            <div class="border-start ps-3 text-end">
                                                <div class="text-muted fs-11">Total Saldo Tagihan:</div>
                                                <div class="fw-bold text-danger fs-14" id="banner-total-remaining">Rp 0</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Destination Bank -->
                            <div class="col-md-6 position-relative">
                                <label class="form-label fw-semibold text-dark fs-13" for="userBankCode">
                                    Rekening Bank Penerima <i class="icofont icofont-warning-alt text-danger"></i>
                                </label>
                                <select class="js-example-basic-single form-select" name="userBankCode" id="userBankCode" required>
                                    <option selected disabled value="">{{ __('general.choose') }} Rekening...</option>
                                    @foreach ($userBank as $item)
                                    <option value="{{ $item->code }}"
                                        data-bank="{{ $item->bank->name ?? '' }}"
                                        data-accno="{{ $item->accountNumber }}"
                                        data-accname="{{ $item->accountName }}"
                                        {{ old('userBankCode') == $item->code ? 'selected' : '' }}>
                                        {{ ($item->bank->name ?? 'Bank') . ' - ' . $item->accountNumber . ' a/n ' . $item->accountName }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Payment Receipt File -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark fs-13" for="paymentReceipt">
                                    Bukti Transfer / Pembayaran
                                    <span class="text-muted fs-11 fw-normal">(JPG, PNG, PDF maks 4MB)</span>
                                </label>
                                <input class="form-control" name="paymentReceipt" id="paymentReceipt" type="file"
                                    accept="image/jpeg,image/png,image/jpg,application/pdf">
                                <div id="file-chosen-info" class="text-muted fs-11 mt-1" style="display: none;">
                                    <i class="mdi mdi-paperclip text-primary me-1"></i><span id="file-name-text"></span>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="col-12">
                                <label class="form-label fw-semibold text-dark fs-13" for="description">Catatan / Keterangan Transaksi</label>
                                <textarea class="form-control" name="description" id="description" rows="2"
                                    placeholder="Contoh: Pembayaran invoice batch September via transfer BCA">{{ old('description') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Ringkasan Pembayaran -->
            <div class="col-xl-4 col-lg-5">
                <div class="summary-card h-100 d-flex flex-column justify-content-between mb-0">
                    <!-- Header -->
                    <div class="summary-card-header">
                        <div class="d-flex align-items-center justify-content-between">
                            <h5 class="mb-0 text-white fw-bold d-flex align-items-center gap-2 fs-15">
                                <i class="mdi mdi-calculator-variant-outline fs-18"></i>
                                Ringkasan Pembayaran
                            </h5>
                            <span class="badge bg-white text-primary fw-bold fs-11" id="summary-badge-count">0 Faktur</span>
                        </div>
                        <p class="text-white-50 fs-11 mb-0 mt-1">Kalkulasi real-time kas masuk dan potongan tagihan.</p>
                    </div>

                    <!-- Body -->
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <!-- Metrics Breakdown -->
                        <div class="mb-3">
                            <div class="summary-metric-row">
                                <span class="text-muted">Total Sisa Tagihan Terpilih</span>
                                <span class="fw-semibold text-dark font-monospace fs-13" id="sum-billing">Rp 0</span>
                            </div>
                            <div class="summary-metric-row">
                                <span class="text-muted">Total Claim (Potongan Biaya)</span>
                                <span class="fw-semibold text-warning-emphasis font-monospace fs-13" id="sum-claim">- Rp 0</span>
                            </div>
                            <div class="summary-metric-row">
                                <span class="text-muted">Sisa Tagihan Belum Terbayar</span>
                                <span class="fw-semibold text-muted font-monospace fs-13" id="sum-unpaid-remaining">Rp 0</span>
                            </div>
                        </div>

                        <!-- Grand Total Box: Uang Diterima / Kas Masuk -->
                        <div class="grand-total-callout mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-uppercase fw-bold text-success fs-11 letter-spacing-1">
                                    <i class="mdi mdi-cash-check me-1"></i> Uang Diterima (Kas Masuk)
                                </span>
                                <span class="badge bg-success text-white fs-10 px-2 py-0">Dana Riil</span>
                            </div>
                            <div class="grand-total-value font-monospace" id="sum-received">
                                Rp 0
                            </div>
                            <small class="text-muted fs-11 mt-1 d-block">
                                Nominal ini yang akan dicatat sebagai mutasi kas masuk ke rekening bank.
                            </small>
                        </div>

                        <!-- Bank Info Card Preview -->
                        <div class="p-2 px-3 bg-light rounded-3 border mb-3" id="summary-bank-preview">
                            <div class="text-muted fs-11 fw-bold text-uppercase mb-0">
                                <i class="mdi mdi-bank me-1 text-primary"></i> Rekening Penerima:
                            </div>
                            <div class="fw-bold text-dark fs-12" id="preview-bank-name">Pilih bank di Formulir...</div>
                            <div class="text-muted font-monospace fs-11" id="preview-bank-account">-</div>
                        </div>

                        <!-- Form Action Buttons (Top) -->
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary py-2 px-4 fw-bold shadow-sm" type="submit" id="btn-submit"
                                style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
                                <i class="mdi mdi-check-circle me-1"></i> Simpan Transaksi Pembayaran
                            </button>
                            <a href="{{ route('invoice.payment-transaction.index') }}" class="btn btn-outline-secondary py-1 fs-13">
                                {{ __('general.cancel') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 2: FULL WIDTH (Col 12) - Daftar Faktur Belum Lunas & Alokasi Pembayaran -->
        <div class="row g-4">
            <div class="col-12">
                <div class="card card-modern mb-4">
                    <div class="card-header card-header-modern d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="step-pill bg-success-subtle text-success border border-success-subtle">
                                <i class="mdi mdi-numeric-2-circle fs-14"></i> Langkah 2
                            </span>
                            <h5 class="mb-0 fw-bold text-dark fs-15">Daftar Faktur Belum Lunas & Alokasi Pembayaran</h5>
                        </div>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill fs-11 px-2 py-1" id="invoice-count-badge">
                            0 Faktur Terpilih
                        </span>
                    </div>

                    <div class="card-body p-4">
                        <!-- State 1: Empty State (Belum Pilih Customer) -->
                        <div id="invoice-empty-state" class="text-center py-5">
                            <div class="avatar-lg bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                                <i class="mdi mdi-folder-search-outline text-primary fs-32"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-1">Belum Ada Customer yang Dipilih</h6>
                            <p class="text-muted mb-0 fs-13 max-w-sm mx-auto">
                                Silakan pilih Customer pada bagian Langkah 1 di atas untuk memuat daftar seluruh faktur tagihan yang belum lunas.
                            </p>
                        </div>

                        <!-- State 2: Loading State -->
                        <div id="invoice-loading-state" class="text-center py-5" style="display: none;">
                            <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <h6 class="fw-semibold text-dark mb-1">Memuat Faktur Belum Lunas...</h6>
                            <p class="text-muted fs-12 mb-0">Sedang mengambil data tagihan, pembayaran sebelumnya, dan claim.</p>
                        </div>

                        <!-- State 3: Tabel Faktur & Controls (Full Width) -->
                        <div id="invoice-table-wrapper" style="display: none;">
                            <!-- Table Toolbar (Search & Bulk Actions) -->
                            <div class="table-toolbar d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div class="position-relative">
                                    <i class="mdi mdi-magnify position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                                    <input type="text" id="table-search-box" class="form-control form-control-sm table-search-input"
                                        placeholder="Cari No. Faktur atau Tanggal...">
                                </div>

                                <div class="d-flex align-items-center flex-wrap gap-1">
                                    <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold" id="btn-pay-all-full" title="Isi semua nominal bayar sesuai sisa tagihan">
                                        <i class="mdi mdi-check-all me-1"></i> Bayar Penuh Semua
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-2" id="btn-select-all" title="Centang semua faktur">
                                        Pilih Semua
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-2" id="btn-deselect-all" title="Hilangkan centang semua faktur">
                                        Batal Semua
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-2" id="btn-reset-amounts" title="Kembalikan semua nominal bayar ke 0">
                                        Reset Nominal
                                    </button>
                                </div>
                            </div>

                            <!-- Responsive Invoices Table (Full Width) -->
                            <div class="table-responsive custom-scrollbar border rounded-3 mb-3">
                                <table class="table table-hover align-middle w-100 invoice-table mb-0" id="dt-invoices">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width: 44px;">
                                                <input type="checkbox" id="check-all" class="form-check-input" checked title="Pilih Semua">
                                            </th>
                                            <th style="min-width: 140px;">No. Faktur</th>
                                            <th style="min-width: 100px;">Tgl Faktur</th>
                                            <th class="text-end" style="min-width: 130px;">Total Tagihan</th>
                                            <th class="text-end" style="min-width: 130px;">Terbayar + Claim</th>
                                            <th class="text-end" style="min-width: 130px;">Sisa Tagihan</th>
                                            <th style="min-width: 220px;">Nominal Bayar (Rp)</th>
                                            <th style="min-width: 170px;">Claim / Potongan (Rp)</th>
                                            <th style="min-width: 200px;">Keterangan Claim</th>
                                            <th class="text-center" style="min-width: 130px;">Sisa Akhir</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>

                            <div class="d-flex align-items-center justify-content-between text-muted fs-12 px-1">
                                <div>
                                    <i class="mdi mdi-information-outline text-primary me-1"></i>
                                    Format nominal menggunakan pemisah ribuan titik (contoh: <code>1.500.000</code>).
                                </div>
                                <div id="table-filtered-counter">
                                    Menampilkan <span id="visible-row-count" class="fw-bold text-dark">0</span> faktur
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Action Bar for Table -->
                    <div class="card-footer bg-light-subtle border-top px-4 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2" id="bottom-action-bar" style="display: none !important;">
                        <div class="d-flex align-items-center gap-2 text-muted fs-13">
                            <i class="mdi mdi-check-circle-outline text-success fs-16"></i>
                            <span>Pastikan alokasi pembayaran dan claim sudah sesuai sebelum menyimpan.</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('invoice.payment-transaction.index') }}" class="btn btn-outline-secondary px-3">
                                {{ __('general.cancel') }}
                            </a>
                            <button class="btn btn-primary px-4 fw-bold shadow-sm" type="submit" id="btn-submit-bottom"
                                style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
                                <i class="mdi mdi-check-circle me-1"></i> Simpan Transaksi Pembayaran
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('script')
<script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
<script src="{{ asset('assets/js/select2/select2-custom.js') }}"></script>
<script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>

<script>
    var invoicesUrlTemplate = "{{ route('invoice.payment-transaction.customer-invoices', 'CUSTOMER_CODE') }}";
    var loadedInvoices = [];

    // Helper Rupiah Formatter
    function formatRupiah(val) {
        if (val === '' || val === null || isNaN(val)) return '0';
        var num = Math.round(Number(val));
        return num.toLocaleString('id-ID');
    }

    // Helper Unformat Rupiah String to Number
    function unformatRupiah(str) {
        if (!str) return 0;
        var clean = String(str).replace(/[^0-9]/g, '');
        return clean === '' ? 0 : parseInt(clean, 10);
    }

    // Escape HTML to prevent XSS
    function escapeHtml(str) {
        return String(str == null ? '' : str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Format input field value while preserving cursor
    function formatInputField(input) {
        var cursorPosition = input.selectionStart;
        var originalValue = input.value;
        if (originalValue === '') {
            return;
        }
        var rawNumber = unformatRupiah(originalValue);
        
        var formatted = rawNumber > 0 ? rawNumber.toLocaleString('id-ID') : '0';
        input.value = formatted;

        // Reposition cursor nicely
        if (cursorPosition !== null && originalValue.length > 0) {
            var diff = formatted.length - originalValue.length;
            var newPos = Math.max(0, cursorPosition + diff);
            input.setSelectionRange(newPos, newPos);
        }
    }

    // Load Invoices via AJAX
    function loadInvoices(customerCode) {
        if (!customerCode) {
            $('#invoice-table-wrapper').hide();
            $('#bottom-action-bar').attr('style', 'display: none !important;');
            $('#customer-summary-container').hide();
            $('#invoice-empty-state').show();
            $('#invoice-count-badge').text('0 Faktur Terpilih');
            $('#summary-badge-count').text('0 Faktur');
            loadedInvoices = [];
            recalcTotals();
            return;
        }

        $('#invoice-empty-state').hide();
        $('#invoice-table-wrapper').hide();
        $('#bottom-action-bar').attr('style', 'display: none !important;');
        $('#invoice-loading-state').show();

        // Update Customer Summary Banner Title
        var customerText = $('#customerCode option:selected').text();
        $('#banner-customer-name').text(customerText);

        $.ajax({
            url: invoicesUrlTemplate.replace('CUSTOMER_CODE', encodeURIComponent(customerCode)),
            type: 'GET',
            success: function(response) {
                loadedInvoices = response || [];
                $('#invoice-loading-state').hide();

                if (loadedInvoices.length === 0) {
                    $('#invoice-empty-state').find('h6').text('Tidak Ada Faktur Belum Lunas');
                    $('#invoice-empty-state').find('p').text('Seluruh faktur untuk customer terpilih telah lunas atau belum ada faktur dibuat.');
                    $('#invoice-empty-state').show();
                    $('#customer-summary-container').hide();
                    $('#bottom-action-bar').attr('style', 'display: none !important;');
                    $('#invoice-count-badge').text('0 Faktur Terpilih');
                    $('#summary-badge-count').text('0 Faktur');
                } else {
                    renderRows(loadedInvoices);
                    $('#invoice-table-wrapper').show();
                    $('#bottom-action-bar').attr('style', 'display: flex !important;');
                    $('#customer-summary-container').show();

                    // Update banner stats
                    var totalRem = loadedInvoices.reduce(function(acc, item) { return acc + (item.remaining || 0); }, 0);
                    $('#banner-invoice-count').text(loadedInvoices.length + ' Faktur');
                    $('#banner-total-remaining').text('Rp ' + formatRupiah(totalRem));
                }
                recalcTotals();
            },
            error: function() {
                $('#invoice-loading-state').hide();
                $('#invoice-empty-state').find('h6').text('Gagal Memuat Faktur');
                $('#invoice-empty-state').find('p').text('Terjadi kesalahan saat memuat daftar faktur. Silakan pilih ulang atau refresh halaman.');
                $('#invoice-empty-state').show();
                $('#bottom-action-bar').attr('style', 'display: none !important;');
            }
        });
    }

    // Render Table Rows
    function renderRows(invoices) {
        var tbody = $('#dt-invoices tbody');
        tbody.empty();

        invoices.forEach(function(inv, idx) {
            var tr = document.createElement('tr');
            tr.id = 'row-inv-' + idx;
            tr.className = 'row-selected';

            var formattedBilling = formatRupiah(inv.totalBilling);
            var formattedPaid = formatRupiah(inv.totalPaid + inv.totalClaim);
            var formattedRemaining = formatRupiah(inv.remaining);

            tr.innerHTML = ''
                + '<td class="text-center">'
                + '   <input type="checkbox" class="form-check-input row-check" data-idx="' + idx + '" checked>'
                + '   <input type="hidden" name="invoices[' + idx + '][code]" value="' + escapeHtml(inv.code) + '">'
                + '</td>'
                + '<td>'
                + '   <div class="d-flex align-items-center gap-1">'
                + '       <i class="mdi mdi-file-document-outline text-primary fs-14"></i>'
                + '       <span class="font-monospace fw-bold text-primary fs-13">' + escapeHtml(inv.invoiceNumber) + '</span>'
                + '   </div>'
                + '</td>'
                + '<td class="fs-12 text-nowrap text-muted">' + escapeHtml(inv.invoiceDate || '-') + '</td>'
                + '<td class="text-end fw-semibold text-dark fs-13">Rp ' + formattedBilling + '</td>'
                + '<td class="text-end text-muted fs-12">Rp ' + formattedPaid + '</td>'
                + '<td class="text-end fw-bold text-danger fs-13" data-remaining="' + inv.remaining + '">Rp ' + formattedRemaining + '</td>'
                + '<td>'
                + '   <div class="input-group input-group-sm currency-input-group">'
                + '       <span class="input-group-text">Rp</span>'
                + '       <input type="text" class="form-control currency-input input-amount" '
                + '              name="invoices[' + idx + '][amount]" '
                + '              value="' + formattedRemaining + '" '
                + '              placeholder="0" data-idx="' + idx + '">'
                + '       <button type="button" class="btn btn-outline-primary btn-quick-fill btn-fill-full" data-idx="' + idx + '" title="Isi penuh sisa tagihan">'
                + '           Penuh'
                + '       </button>'
                + '   </div>'
                + '</td>'
                + '<td>'
                + '   <div class="input-group input-group-sm currency-input-group">'
                + '       <span class="input-group-text">Rp</span>'
                + '       <input type="text" class="form-control currency-input input-claim" '
                + '              name="invoices[' + idx + '][claim]" '
                + '              value="0" placeholder="0" data-idx="' + idx + '">'
                + '   </div>'
                + '</td>'
                + '<td>'
                + '   <input type="text" class="form-control form-control-sm input-claim-desc" '
                + '          name="invoices[' + idx + '][claimDescription]" placeholder="Catatan claim...">'
                + '</td>'
                + '<td class="text-center status-col" id="status-cell-' + idx + '">'
                + '   <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill fs-11 px-2 py-1">'
                + '       <i class="mdi mdi-check-circle-outline me-1"></i>Lunas'
                + '   </span>'
                + '</td>';

            tbody.append(tr);
        });

        $('#visible-row-count').text(invoices.length);
    }

    // Recalculate Live Totals & Per-Row Status
    function recalcTotals() {
        var sumBilling = 0;
        var sumClaim = 0;
        var sumReceived = 0;
        var selectedCount = 0;

        $('#dt-invoices tbody tr').each(function() {
            var tr = $(this);
            var check = tr.find('.row-check');
            var checked = check.is(':checked');
            var idx = parseInt(check.data('idx'), 10);
            var inv = loadedInvoices[idx];
            if (!inv) return;

            var amountInput = tr.find('.input-amount');
            var claimInput = tr.find('.input-claim');
            var descInput = tr.find('.input-claim-desc');
            var fullBtn = tr.find('.btn-fill-full');
            var codeInput = tr.find('input[name$="[code]"]');
            var statusCell = $('#status-cell-' + idx);

            // Enable/disable inputs based on row selection
            amountInput.prop('disabled', !checked);
            claimInput.prop('disabled', !checked);
            descInput.prop('disabled', !checked);
            fullBtn.prop('disabled', !checked);
            codeInput.prop('disabled', !checked);

            if (!checked) {
                tr.removeClass('row-selected').addClass('row-disabled');
                statusCell.html('<span class="badge bg-light text-muted border fs-11">Tidak Dipilih</span>');
                return;
            }

            tr.addClass('row-selected').removeClass('row-disabled');
            selectedCount++;

            var amount = unformatRupiah(amountInput.val());
            var claim = unformatRupiah(claimInput.val());

            var finalRemaining = Math.max(0, inv.remaining - amount - claim);

            // Update row status badge
            if (finalRemaining === 0) {
                statusCell.html('<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill fs-11 px-2 py-1"><i class="mdi mdi-check-circle-outline me-1"></i>Lunas</span>');
            } else {
                statusCell.html('<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill fs-11 px-2 py-1">Sisa Rp ' + formatRupiah(finalRemaining) + '</span>');
            }

            sumBilling += inv.remaining;
            sumClaim += claim;
            sumReceived += amount;
        });

        var unpaidRemaining = Math.max(0, sumBilling - sumReceived - sumClaim);

        // Update Summary Card
        $('#sum-billing').text('Rp ' + formatRupiah(sumBilling));
        $('#sum-claim').text('- Rp ' + formatRupiah(sumClaim));
        $('#sum-received').text('Rp ' + formatRupiah(sumReceived));
        $('#sum-unpaid-remaining').text('Rp ' + formatRupiah(unpaidRemaining));

        $('#invoice-count-badge').text(selectedCount + ' Faktur Terpilih');
        $('#summary-badge-count').text(selectedCount + ' Faktur');

        // Check-all header sync
        var totalCheckboxes = $('.row-check').length;
        if (totalCheckboxes > 0) {
            $('#check-all').prop('checked', selectedCount === totalCheckboxes);
        }
    }

    // Update Destination Bank Info Preview
    function updateBankPreview() {
        var opt = $('#userBankCode option:selected');
        if (opt.val()) {
            var bank = opt.data('bank') || 'Bank';
            var accno = opt.data('accno') || '-';
            var accname = opt.data('accname') || '';
            $('#preview-bank-name').text(bank + ' - ' + accno);
            $('#preview-bank-account').text('a/n ' + accname);
        } else {
            $('#preview-bank-name').text('Pilih bank di Formulir...');
            $('#preview-bank-account').text('-');
        }
    }

    $(document).ready(function() {
        // Initial setup
        updateBankPreview();
        $('#userBankCode').on('change', updateBankPreview);

        // File upload indicator
        $('#paymentReceipt').on('change', function() {
            var file = this.files[0];
            if (file) {
                var sizeMb = (file.size / (1024 * 1024)).toFixed(2);
                $('#file-name-text').text(file.name + ' (' + sizeMb + ' MB)');
                $('#file-chosen-info').fadeIn();
            } else {
                $('#file-chosen-info').hide();
            }
        });

        // Load customer invoices if customer selected initially
        var initialCustomer = $('#customerCode').val();
        if (initialCustomer) {
            loadInvoices(initialCustomer);
        }

        $('#customerCode').on('change', function() {
            loadInvoices($(this).val());
        });

        // Auto-select text on focus if '0'
        $(document).on('focus', '.currency-input', function() {
            if ($(this).val() === '0') {
                $(this).select();
            }
        });

        // Set '0' on blur if empty
        $(document).on('blur', '.currency-input', function() {
            if ($(this).val().trim() === '') {
                $(this).val('0');
                recalcTotals();
            }
        });

        // Input handler for Claim (Manual input dan mengurangi nominal bayar)
        $(document).on('input', '.input-claim', function() {
            var tr = $(this).closest('tr');
            var idx = parseInt(tr.find('.row-check').data('idx'), 10);
            var inv = loadedInvoices[idx];
            if (!inv) return;

            formatInputField(this);
            var claim = unformatRupiah($(this).val());

            // Claim maksimal sebesar sisa tagihan faktur
            if (claim > inv.remaining) {
                claim = inv.remaining;
                $(this).val(formatRupiah(claim));
            }

            // Claim mengurangi Nominal Bayar jika (Nominal Bayar + Claim > Sisa Tagihan)
            var amountInput = tr.find('.input-amount');
            var currentAmount = unformatRupiah(amountInput.val());

            if (currentAmount + claim > inv.remaining) {
                var newAmount = Math.max(0, inv.remaining - claim);
                amountInput.val(formatRupiah(newAmount));
            }

            recalcTotals();
        });

        // Input handler for Nominal Bayar
        $(document).on('input', '.input-amount', function() {
            var tr = $(this).closest('tr');
            var idx = parseInt(tr.find('.row-check').data('idx'), 10);
            var inv = loadedInvoices[idx];
            if (!inv) return;

            formatInputField(this);
            var amount = unformatRupiah($(this).val());
            var claim = unformatRupiah(tr.find('.input-claim').val());

            // Nominal bayar maksimal sebesar (sisa tagihan - claim)
            var maxAllowed = Math.max(0, inv.remaining - claim);
            if (amount > maxAllowed) {
                amount = maxAllowed;
                $(this).val(formatRupiah(amount));
            }

            recalcTotals();
        });

        // Quick "Penuh" button per row (Mengisi penuh sisa tagihan dikurangi claim)
        $(document).on('click', '.btn-fill-full', function() {
            var idx = parseInt($(this).data('idx'), 10);
            var inv = loadedInvoices[idx];
            if (!inv) return;

            var tr = $('#row-inv-' + idx);
            tr.find('.row-check').prop('checked', true);
            var claim = unformatRupiah(tr.find('.input-claim').val());
            var fullAmount = Math.max(0, inv.remaining - claim);
            tr.find('.input-amount').val(formatRupiah(fullAmount));
            recalcTotals();
        });

        // Row checkbox toggle
        $(document).on('change', '.row-check', function() {
            recalcTotals();
        });

        // Header Check-all toggle
        $('#check-all').on('change', function() {
            var checked = $(this).is(':checked');
            $('.row-check').prop('checked', checked);
            recalcTotals();
        });

        // Bulk: Pilih Semua
        $('#btn-select-all').on('click', function() {
            $('.row-check').prop('checked', true);
            $('#check-all').prop('checked', true);
            recalcTotals();
        });

        // Bulk: Batal Semua
        $('#btn-deselect-all').on('click', function() {
            $('.row-check').prop('checked', false);
            $('#check-all').prop('checked', false);
            recalcTotals();
        });

        // Bulk: Bayar Penuh Semua (Sisa tagihan dikurangi claim masing-masing faktur)
        $('#btn-pay-all-full').on('click', function() {
            $('#dt-invoices tbody tr').each(function() {
                var tr = $(this);
                var idx = parseInt(tr.find('.row-check').data('idx'), 10);
                var inv = loadedInvoices[idx];
                if (inv) {
                    tr.find('.row-check').prop('checked', true);
                    var claim = unformatRupiah(tr.find('.input-claim').val());
                    var fullAmount = Math.max(0, inv.remaining - claim);
                    tr.find('.input-amount').val(formatRupiah(fullAmount));
                }
            });
            $('#check-all').prop('checked', true);
            recalcTotals();
        });

        // Bulk: Reset Semua Nominal
        $('#btn-reset-amounts').on('click', function() {
            $('#dt-invoices tbody tr').each(function() {
                var tr = $(this);
                tr.find('.input-amount').val('0');
                tr.find('.input-claim').val('0');
            });
            recalcTotals();
        });

        // Table Live Search / Filter
        $('#table-search-box').on('keyup', function() {
            var query = $(this).val().toLowerCase().trim();
            var visibleCount = 0;

            $('#dt-invoices tbody tr').each(function() {
                var rowText = $(this).text().toLowerCase();
                if (rowText.indexOf(query) > -1) {
                    $(this).show();
                    visibleCount++;
                } else {
                    $(this).hide();
                }
            });

            $('#visible-row-count').text(visibleCount);
        });

        // Form Submit Handler
        $('#trx-form').on('submit', function(e) {
            var selectedRows = $('#dt-invoices tbody tr').filter(function() {
                return $(this).find('.row-check').is(':checked');
            });

            if (selectedRows.length === 0) {
                e.preventDefault();
                swal({
                    title: "Perhatian",
                    text: "Pilih minimal satu faktur untuk melakukan transaksi pembayaran.",
                    icon: "warning",
                });
                return false;
            }

            var hasPaymentOrClaim = false;
            selectedRows.each(function() {
                var amount = unformatRupiah($(this).find('.input-amount').val());
                var claim = unformatRupiah($(this).find('.input-claim').val());
                if (amount > 0 || claim > 0) {
                    hasPaymentOrClaim = true;
                }
            });

            if (!hasPaymentOrClaim) {
                e.preventDefault();
                swal({
                    title: "Nominal Belum Diisi",
                    text: "Minimal salah satu faktur yang dipilih harus memiliki Nominal Bayar atau Claim lebih dari 0.",
                    icon: "warning",
                });
                return false;
            }

            // Unformat all inputs to raw numbers before POST submission
            $('.input-amount, .input-claim').each(function() {
                var rawVal = unformatRupiah($(this).val());
                $(this).val(rawVal);
            });

            // Loading state
            $('#btn-submit, #btn-submit-bottom').prop('disabled', true)
                .html('<i class="mdi mdi-loading mdi-spin me-1"></i> Menyimpan Transaksi...');
        });
    });
</script>
@endpush
