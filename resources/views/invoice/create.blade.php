@extends('layouts.main', [
    'title' => 'Buat Faktur Baru',
    'pageTitle' => 'Buat Faktur',
    'firstSegment' => 'Faktur',
    'secondSegment' => 'Pembuatan Faktur',
])

@push('style')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/select2.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom-select2.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/sweetalert2.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('assets/libs/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css') }}">
@include('invoice.partials.table-style')

<style>
    /* Modern Styling for Invoice Create Page */
    :root {
        --invoice-primary: #3051d3;
        --invoice-primary-soft: #eef2ff;
        --invoice-dark: #1e293b;
    }

    .card-modern {
        border: 1px solid #e9edf4;
        border-radius: 12px;
        box-shadow: 0 4px 20px -2px rgba(30, 41, 59, 0.05);
        transition: all 0.2s ease;
        background: #ffffff;
    }

    .card-header-modern {
        background: #ffffff;
        border-bottom: 1px solid #f1f5f9;
        padding: 1.1rem 1.4rem;
        border-top-left-radius: 12px !important;
        border-top-right-radius: 12px !important;
    }

    .form-section-title {
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        font-weight: 700;
        color: #64748b;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-section-title::after {
        content: "";
        flex: 1;
        height: 1px;
        background: #e2e8f0;
    }

    /* Tax Toggle Card */
    .tax-toggle-card {
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        padding: 1rem;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: space-between;
        user-select: none;
    }

    .tax-toggle-card:hover {
        border-color: #cbd5e1;
        background: #f1f5f9;
        transform: translateY(-1px);
    }

    .tax-toggle-card.active {
        border-color: var(--invoice-primary);
        background: var(--invoice-primary-soft);
        box-shadow: 0 2px 8px rgba(48, 81, 211, 0.12);
    }

    .tax-toggle-card.active .tax-title {
        color: var(--invoice-primary);
        font-weight: 600;
    }

    /* Customer Quick Info Card */
    .customer-info-box {
        background: linear-gradient(145deg, #f8fafc 0%, #f1f5f9 100%);
        border: 1px solid #e2e8f0;
        border-left: 4px solid var(--invoice-primary);
        border-radius: 10px;
        padding: 1rem 1.25rem;
        animation: fadeInDown 0.3s ease;
    }

    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-8px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Sticky Summary Sidebar */
    .sticky-summary {
        position: sticky;
        top: 85px;
        z-index: 9;
    }

    .summary-card {
        border: none;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 10px 30px -5px rgba(30, 41, 59, 0.12);
        background: #ffffff;
    }

    .summary-header {
        background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
        padding: 1.35rem 1.5rem;
        color: #ffffff;
        position: relative;
    }

    .summary-header::after {
        content: "";
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #60a5fa, #93c5fd);
    }

    .grand-total-box {
        background: #f0fdf4;
        border: 1.5px solid #86efac;
        border-radius: 10px;
        padding: 1rem 1.25rem;
        transition: all 0.3s ease;
    }

    .grand-total-amount {
        font-size: 1.65rem;
        font-weight: 800;
        color: #15803d;
        letter-spacing: -0.02em;
    }

    /* Selected Order Chips */
    .chip-item {
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        color: #334155;
        border-radius: 20px;
        padding: 0.25rem 0.65rem;
        font-size: 0.75rem;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        transition: all 0.15s ease;
    }

    .chip-item:hover {
        background: #e2e8f0;
    }

    .chip-remove {
        cursor: pointer;
        color: #ef4444;
        font-weight: bold;
        display: flex;
        align-items: center;
    }

    /* Datatable Row Highlight when Selected */
    table#dtOrders tbody tr.selected-row {
        background-color: rgba(37, 99, 235, 0.07) !important;
    }

    table#dtOrders tbody tr {
        transition: background-color 0.15s ease;
    }

    /* Floating Step Badge */
    .step-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.78rem;
        font-weight: 600;
        padding: 0.35rem 0.8rem;
        border-radius: 20px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">
    <!-- Top Action Banner -->
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-1 text-dark fw-bold d-flex align-items-center gap-2">
                <i class="mdi mdi-file-document-edit text-primary fs-22"></i>
                Pembuatan Faktur Baru
            </h4>
            <p class="text-muted fs-13 mb-0">Pilih pelanggan, tentukan tanggal faktur, dan tandai pesanan yang siap ditagihkan.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('invoice.unpaid') }}" class="btn btn-outline-secondary px-3">
                <i class="mdi mdi-arrow-left me-1"></i> Kembali ke Daftar
            </a>
        </div>
    </div>

    @include('partials.alert')

    <form id="invoiceForm" method="post" action="{{ route('invoice.store') }}">
        @csrf
        <input type="hidden" name="selectedOrders" id="selectedOrdersInput" value="[]">

        <div class="row g-4">
            <!-- Left Column: Form Details & Order Table (Col 8) -->
            <div class="col-xl-8 col-lg-7">
                <!-- Card 1: Customer & Invoice Details -->
                <div class="card card-modern mb-4">
                    <div class="card-header card-header-modern d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="step-pill bg-primary-subtle text-primary border border-primary-subtle">
                                <i class="mdi mdi-numeric-1-circle fs-15"></i> Langkah 1
                            </span>
                            <h5 class="mb-0 fw-bold text-dark fs-15">Informasi Faktur & Pelanggan</h5>
                        </div>
                        <span class="badge bg-light text-muted border px-2 py-1 fs-11">Wajib diisi</span>
                    </div>

                    <div class="card-body p-4">
                        <!-- Customer & Invoice Number Row -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark fs-13" for="customerCode">
                                    Nama Pelanggan <span class="text-danger">*</span>
                                </label>
                                <select class="js-example-basic-single form-select" name="customerCode" id="customerCode" required>
                                    <option selected disabled value="">Pilih Pelanggan...</option>
                                    @foreach ($customer as $item)
                                    <option value="{{ $item->code }}" data-id="{{ $item->id }}">
                                        {{ $item->name }} ({{ $item->code }})
                                    </option>
                                    @endforeach
                                </select>
                                <small class="text-muted fs-11">Pilih pelanggan untuk memuat format nomor faktur & daftar order.</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark fs-13" for="invoiceNumber">
                                    Nomor Faktur / Invoice <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted border-end-0">
                                        <i class="mdi mdi-pound"></i>
                                    </span>
                                    <input class="form-control font-monospace border-start-0 fw-semibold" name="invoiceNumber" id="invoiceNumber"
                                        type="text" required readonly placeholder="Pilih pelanggan dahulu...">
                                    <button class="btn btn-outline-secondary" type="button" id="btnRefreshInvoiceNumber" title="Generate Ulang Nomor">
                                        <i class="mdi mdi-refresh"></i>
                                    </button>
                                </div>
                                <small class="text-muted fs-11">Otomatis digenerate berdasarkan format pelanggan & tanggal faktur.</small>
                            </div>
                        </div>

                        <!-- Customer Details Preview Banner (Shows when customer selected) -->
                        <div id="customerInfoBox" class="customer-info-box mb-3 d-none">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="fw-bold text-dark fs-14" id="infoCustomerName">-</div>
                                <span class="badge bg-primary text-white fs-11" id="infoCustomerCode">-</span>
                            </div>
                            <div class="row g-2 text-muted fs-12">
                                <div class="col-md-7">
                                    <i class="mdi mdi-map-marker-outline text-danger me-1"></i>
                                    <span class="fw-semibold text-dark">Alamat Penagihan:</span>
                                    <span id="infoBillingAddress" class="ms-1">-</span>
                                </div>
                                <div class="col-md-5">
                                    <i class="mdi mdi-clock-outline text-warning me-1"></i>
                                    <span class="fw-semibold text-dark">Termin:</span>
                                    <span id="infoDueDateDuration" class="ms-1">-</span>
                                </div>
                            </div>
                            <div id="picSection" class="mt-2 pt-2 border-top border-light-subtle d-none">
                                <span class="text-muted fs-11 fw-semibold text-uppercase">Kontak PIC:</span>
                                <div id="picBadges" class="d-flex flex-wrap gap-2 mt-1"></div>
                            </div>
                        </div>

                        <!-- Date Row -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark fs-13" for="invoiceDate">
                                    Tanggal Faktur <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted">
                                        <i class="mdi mdi-calendar"></i>
                                    </span>
                                    <input class="form-control" name="invoiceDate" id="invoiceDate" type="date" required
                                        value="{{ now()->toDateString() }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark fs-13" for="overdueDate">
                                    Tanggal Jatuh Tempo
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted">
                                        <i class="mdi mdi-calendar-clock"></i>
                                    </span>
                                    <input class="form-control bg-light" name="overdueDate" id="overdueDate" type="date" readonly
                                        value="{{ now()->addDays(30)->toDateString() }}">
                                </div>
                                <small class="text-muted fs-11">Dihitung otomatis sesuai durasi jatuh tempo pelanggan.</small>
                            </div>
                        </div>

                        <!-- Tax Switches (PPN & PPh) -->
                        <div class="form-section-title mt-4">
                            <i class="mdi mdi-percent-outline text-primary"></i> Pengaturan Pajak (PPN & PPh)
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <input type="hidden" name="usePpn" value="0">
                                <div class="tax-toggle-card active" id="ppnCard">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar-xs">
                                            <span class="avatar-title rounded-circle bg-primary text-white fs-14">
                                                <i class="mdi mdi-receipt"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <div class="tax-title fs-14 fw-bold" id="ppnLabel">PPN (11%)</div>
                                            <div class="text-muted fs-11">Pajak Pertambahan Nilai</div>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" id="usePpn" name="usePpn" value="1" checked>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <input type="hidden" name="usePph" value="0">
                                <div class="tax-toggle-card" id="pphCard">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar-xs">
                                            <span class="avatar-title rounded-circle bg-warning text-white fs-14">
                                                <i class="mdi mdi-shield-percent"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <div class="tax-title fs-14 fw-bold" id="pphLabel">PPh 23 (2%)</div>
                                            <div class="text-muted fs-11">Pemotongan PPh Pasal 23</div>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" id="usePph" name="usePph" value="1">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Optional References: PO & Kwitansi (Accordion / Collapse) -->
                        <div class="form-section-title mt-4">
                            <i class="mdi mdi-note-text-outline text-primary"></i> Referensi & Catatan Tambahan
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-dark fs-13" for="poNumber">Nomor PO (Opsional)</label>
                                <input class="form-control" name="poNumber" id="poNumber" type="text" placeholder="Contoh: PO-2026-001">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-dark fs-13" for="receiptNumber">Nomor Kwitansi (Opsional)</label>
                                <input class="form-control" name="receiptNumber" id="receiptNumber" type="text" placeholder="Contoh: KWT-001">
                            </div>
                            <div class="col-12">
                                <label class="form-label text-dark fs-13" for="notes">Catatan Faktur</label>
                                <textarea class="form-control" name="notes" id="notes" rows="2" placeholder="Catatan opsional yang dicetak pada invoice..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Order Selection Table -->
                <div class="card card-modern">
                    <div class="card-header card-header-modern d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="step-pill bg-success-subtle text-success border border-success-subtle">
                                <i class="mdi mdi-numeric-2-circle fs-15"></i> Langkah 2
                            </span>
                            <h5 class="mb-0 fw-bold text-dark fs-15">Pilih Pesanan untuk Ditagihkan</h5>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fs-12" id="badgeOrderCount">
                                0 Pesanan Terpilih
                            </span>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnSelectAllOnPage" disabled>
                                <i class="mdi mdi-checkbox-multiple-marked-outline me-1"></i>Pilih Semua di Halaman
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger d-none" id="btnClearAllSelection">
                                <i class="mdi mdi-close-circle-outline me-1"></i>Hapus Pilihan
                            </button>
                        </div>
                    </div>

                    <div class="card-body p-3">
                        <!-- Empty State Placeholder (when no customer selected) -->
                        <div id="noCustomerPlaceholder" class="text-center py-5">
                            <div class="avatar-lg mx-auto mb-3">
                                <span class="avatar-title rounded-circle bg-light text-muted fs-32">
                                    <i class="mdi mdi-truck-check-outline"></i>
                                </span>
                            </div>
                            <h5 class="text-dark fw-bold mb-1">Belum Ada Pelanggan yang Dipilih</h5>
                            <p class="text-muted fs-13 mb-0" style="max-width: 420px; margin: 0 auto;">
                                Silakan pilih pelanggan pada form di atas terlebih dahulu untuk menampilkan daftar order yang belum difakturkan.
                            </p>
                        </div>

                        <!-- Orders Datatable Wrapper -->
                        <div id="orderTableContainer" class="d-none">
                            <div class="alert alert-info border-0 bg-info-subtle py-2 px-3 mb-3 d-flex align-items-center gap-2 fs-12 text-info">
                                <i class="mdi mdi-information-outline fs-16"></i>
                                <span>Centang pesanan yang ingin dimasukkan ke dalam faktur ini. Anda dapat berpindah halaman atau mencari tanpa kehilangan pilihan.</span>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle w-100 invoice-table" id="dtOrders">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="35" class="text-center">
                                                <input type="checkbox" class="form-check-input" id="checkAllHeader" title="Pilih Semua di Halaman Ini">
                                            </th>
                                            <th width="35">No</th>
                                            <th>Tanggal Order</th>
                                            <th>Rute (Asal ➔ Tujuan)</th>
                                            <th>No. Surat Jalan</th>
                                            <th>No. Polisi</th>
                                            <th class="text-end">Tarif Rute</th>
                                            <th class="text-end">Biaya On Charge</th>
                                            <th class="text-end">Total Tagihan</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Sticky Summary & Actions (Col 4) -->
            <div class="col-xl-4 col-lg-5">
                <div class="sticky-summary">
                    <div class="card summary-card">
                        <div class="summary-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-white text-primary fw-bold px-2 py-1 fs-10 mb-1">RINGKASAN FAKTUR</span>
                                    <h5 class="text-white mb-0 fw-bold">Kalkulasi Tagihan</h5>
                                </div>
                                <div class="avatar-sm">
                                    <span class="avatar-title rounded-circle bg-white text-primary fs-18 shadow-sm">
                                        <i class="mdi mdi-calculator"></i>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-4">
                            <!-- Quick Header Preview -->
                            <div class="p-3 bg-light rounded-3 mb-3 border">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-muted fs-12">No. Faktur:</span>
                                    <span class="fw-bold text-primary font-monospace fs-12" id="previewInvoiceNumber">-</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-muted fs-12">Pelanggan:</span>
                                    <span class="fw-semibold text-dark text-truncate fs-12 ms-2" style="max-width: 170px;" id="previewCustomerName">-</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted fs-12">Jatuh Tempo:</span>
                                    <span class="text-dark fs-12 fw-medium" id="previewOverdueDate">-</span>
                                </div>
                            </div>

                            <!-- Cost Breakdown -->
                            <div class="cost-breakdown mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted fs-13">Pesanan Terpilih:</span>
                                    <span class="fw-bold text-dark fs-13" id="summaryOrderCount">0 Pesanan</span>
                                </div>

                                <!-- Total Tarif Rute Pokok -->
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted fs-13">Total Tarif Rute:</span>
                                    <span class="fw-semibold text-dark fs-14" id="summaryBasePrice">Rp 0</span>
                                </div>

                                <!-- Total Biaya On Charge -->
                                <div class="mb-2" id="rowOnCharge">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted fs-13 d-flex align-items-center gap-1">
                                            <span>Biaya On Charge:</span>
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle fs-10 px-1 py-0 rounded" id="summaryOnChargeBadge">0 item</span>
                                        </span>
                                        <span class="fw-semibold text-warning fs-14" id="summaryOnCharge">+ Rp 0</span>
                                    </div>
                                    <!-- Collapsible itemized list of on charge components -->
                                    <div id="onChargeBreakdownCard" class="mt-2 p-2 rounded bg-light border border-warning-subtle fs-12 d-none">
                                        <div class="d-flex justify-content-between align-items-center mb-1 text-muted fw-bold fs-11">
                                            <span>RINCIAN ON CHARGE:</span>
                                            <span id="onChargeTotalComponentCount">(0)</span>
                                        </div>
                                        <div id="onChargeBreakdownList" class="d-flex flex-column gap-1 custom-scrollbar" style="max-height: 120px; overflow-y: auto;"></div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-2 pt-1 border-top">
                                    <span class="text-dark fw-bold fs-13">Subtotal DPP:</span>
                                    <span class="fw-bold text-dark fs-14" id="summarySubtotal">Rp 0</span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-2" id="rowPpn">
                                    <span class="text-muted fs-13 d-flex align-items-center">
                                        <span class="badge bg-primary-subtle text-primary me-1 fs-10" id="summaryPpnBadge">11%</span> PPN:
                                    </span>
                                    <span class="fw-semibold text-primary fs-14" id="summaryPpn">+ Rp 0</span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-2" id="rowPph">
                                    <span class="text-muted fs-13 d-flex align-items-center">
                                        <span class="badge bg-warning-subtle text-warning me-1 fs-10" id="summaryPphBadge">2%</span> PPh 23:
                                    </span>
                                    <span class="fw-semibold text-danger fs-14" id="summaryPph">- Rp 0</span>
                                </div>

                                <hr class="my-3 border-dashed">

                                <!-- Grand Total Box -->
                                <div class="grand-total-box mb-3">
                                    <div class="text-muted fs-11 fw-bold text-uppercase mb-1">TOTAL TAGIHAN (GRAND TOTAL)</div>
                                    <div class="grand-total-amount" id="summaryGrandTotal">Rp 0</div>
                                </div>
                            </div>

                            <!-- Selected Order Chips Section -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fs-11 fw-bold text-muted text-uppercase">Rincian Order Terpilih:</span>
                                    <small class="text-muted" id="selectedChipsCount">(0)</small>
                                </div>
                                <div id="selectedOrdersChips" class="d-flex flex-wrap gap-1 custom-scrollbar" style="max-height: 110px; overflow-y: auto;">
                                    <span class="text-muted fs-12 fst-italic">Belum ada pesanan yang dipilih</span>
                                </div>
                            </div>

                            <!-- Submit Action Button -->
                            <button type="button" class="btn btn-primary w-100 py-2 fs-15 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2" id="btnSubmitInvoice" disabled>
                                <i class="mdi mdi-check-decagram fs-18"></i>
                                <span>Simpan & Buat Faktur</span>
                            </button>

                            <div class="text-center mt-2">
                                <small class="text-muted fs-11" id="submitHelperText">
                                    <i class="mdi mdi-information-outline me-1"></i>Pilih minimal 1 pesanan untuk menyimpan
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Modal Detail Biaya On Charge per Order -->
<div class="modal fade" id="modalOrderCostDetail" tabindex="-1" aria-labelledby="modalOrderCostDetailLabel" aria-hidden="true">
    <div class="modal-dialog mt-4">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-warning-subtle py-3 px-4 border-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="avatar-sm">
                        <span class="avatar-title rounded-circle bg-warning text-white fs-16">
                            <i class="mdi mdi-cash-multiple"></i>
                        </span>
                    </span>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="modalOrderCostDetailLabel">Rincian Biaya On Charge</h5>
                        <small class="text-muted" id="modalOrderShipmentTitle">Surat Jalan: -</small>
                    </div>
                </div>
                <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Order summary badges -->
                <div class="d-flex justify-content-between align-items-center p-2 rounded bg-light border mb-3 fs-12">
                    <div>
                        <span class="text-muted">Tarif Rute:</span>
                        <span class="fw-bold text-dark ms-1" id="modalOrderBasePrice">Rp 0</span>
                    </div>
                    <div>
                        <span class="text-muted">Total Tagihan:</span>
                        <span class="fw-bold text-primary ms-1" id="modalOrderTotalPrice">Rp 0</span>
                    </div>
                </div>

                <p class="text-muted fs-12 mb-2">Berikut komponen biaya tambahan (On Charge) yang ditagihkan kepada pelanggan untuk order ini:</p>
                
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead class="table-light fs-12">
                            <tr>
                                <th width="35" class="text-center">No</th>
                                <th>Komponen Biaya</th>
                                <th class="text-end">Nominal</th>
                            </tr>
                        </thead>
                        <tbody id="modalOrderCostBody" class="fs-12">
                        </tbody>
                        <tfoot class="table-light fw-bold fs-12">
                            <tr>
                                <td colspan="2" class="text-end">Total Biaya On Charge:</td>
                                <td class="text-end text-warning" id="modalOrderOnChargeTotal">Rp 0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light px-4 py-2 border-0">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
<script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>

<script>
    // State storage for selected orders across pages
    // Structure: { orderCode: { price: Number, basePrice: Number, onCharge: Number, shipment: String, plate: String, costs: Array } }
    let selectedOrdersMap = {};
    let customerDueDateDuration = 30;
    let customerPpnRate = 0;
    let customerPphRate = 0;
    let ordersTable = null;

    // Currency Formatter
    function formatRupiah(amount) {
        return 'Rp ' + Math.round(amount).toLocaleString('id-ID');
    }

    // Live Calculation Function
    function recalculateLiveSummary() {
        const orderCodes = Object.keys(selectedOrdersMap);
        const orderCount = orderCodes.length;

        let subtotal = 0;
        let totalBase = 0;
        let totalOnCharge = 0;
        let componentsMap = {};

        orderCodes.forEach(code => {
            const item = selectedOrdersMap[code];
            const p = Number(item.price || 0);
            const bp = Number(item.basePrice || 0);
            const oc = Number(item.onCharge || 0);

            subtotal += p;
            totalBase += bp;
            totalOnCharge += oc;

            if (item.costs && Array.isArray(item.costs)) {
                item.costs.forEach(c => {
                    const name = c.component || 'Biaya Tambahan';
                    if (!componentsMap[name]) {
                        componentsMap[name] = { count: 0, nominal: 0 };
                    }
                    componentsMap[name].count += 1;
                    componentsMap[name].nominal += Number(c.nominal || 0);
                });
            }
        });

        const usePpn = $('#usePpn').is(':checked');
        const usePph = $('#usePph').is(':checked');

        const ppnAmount = usePpn && customerPpnRate > 0 ? subtotal * (customerPpnRate / 100) : 0;
        const pphAmount = usePph && customerPphRate > 0 ? subtotal * (customerPphRate / 100) : 0;
        const grandTotal = subtotal + ppnAmount - pphAmount;

        // Update DOM
        $('#badgeOrderCount').text(orderCount + ' Pesanan Terpilih');
        $('#summaryOrderCount').text(orderCount + ' Pesanan');
        $('#selectedChipsCount').text('(' + orderCount + ')');

        $('#summaryBasePrice').text(formatRupiah(totalBase));
        $('#summaryOnCharge').text((totalOnCharge > 0 ? '+ ' : '') + formatRupiah(totalOnCharge));
        $('#summarySubtotal').text(formatRupiah(subtotal));
        $('#summaryPpn').text((usePpn ? '+ ' : '') + formatRupiah(ppnAmount));
        $('#summaryPph').text((usePph ? '- ' : '') + formatRupiah(pphAmount));
        $('#summaryGrandTotal').text(formatRupiah(grandTotal));

        // Render On Charge breakdown list in sidebar
        const compNames = Object.keys(componentsMap);
        if (compNames.length > 0 && totalOnCharge > 0) {
            $('#summaryOnChargeBadge').text(compNames.length + ' jenis');
            $('#onChargeTotalComponentCount').text('(' + compNames.length + ')');
            $('#onChargeBreakdownCard').removeClass('d-none');
            const listCont = $('#onChargeBreakdownList');
            listCont.empty();
            compNames.forEach(name => {
                const cData = componentsMap[name];
                listCont.append(`
                    <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light">
                        <span class="text-truncate me-2" title="${name}">• ${name} <small class="text-muted">(${cData.count}x)</small></span>
                        <span class="fw-semibold text-warning text-nowrap">${formatRupiah(cData.nominal)}</span>
                    </div>
                `);
            });
        } else {
            $('#summaryOnChargeBadge').text('0 item');
            $('#onChargeBreakdownCard').addClass('d-none');
        }

        // Dim or show PPN/PPh rows
        if (usePpn) {
            $('#rowPpn').removeClass('opacity-50 text-muted');
        } else {
            $('#rowPpn').addClass('opacity-50 text-muted');
        }

        if (usePph) {
            $('#rowPph').removeClass('opacity-50 text-muted');
        } else {
            $('#rowPph').addClass('opacity-50 text-muted');
        }

        // Render chips
        const chipsContainer = $('#selectedOrdersChips');
        chipsContainer.empty();

        if (orderCount === 0) {
            chipsContainer.html('<span class="text-muted fs-12 fst-italic">Belum ada pesanan yang dipilih</span>');
            $('#btnClearAllSelection').addClass('d-none');
        } else {
            $('#btnClearAllSelection').removeClass('d-none');
            orderCodes.forEach(code => {
                const item = selectedOrdersMap[code];
                const label = item.shipment ? item.shipment : code;
                const chipHtml = `
                    <span class="chip-item" data-code="${code}" title="${code} - Rute: ${formatRupiah(item.basePrice || item.price)}${item.onCharge > 0 ? ' + OnCharge: ' + formatRupiah(item.onCharge) : ''} (Total: ${formatRupiah(item.price)})">
                        <span>${label}</span>
                        ${item.onCharge > 0 ? '<i class="mdi mdi-cash-plus text-warning" title="Ada Biaya On Charge"></i>' : ''}
                        <span class="chip-remove" data-code="${code}" title="Hapus order">&times;</span>
                    </span>
                `;
                chipsContainer.append(chipHtml);
            });
        }

        // Update hidden input
        $('#selectedOrdersInput').val(JSON.stringify(orderCodes));

        // Submit Button State
        const hasCustomer = $('#customerCode').val() !== '' && $('#customerCode').val() !== null;
        if (orderCount > 0 && hasCustomer) {
            $('#btnSubmitInvoice').prop('disabled', false);
            $('#submitHelperText').html('<span class="text-success"><i class="mdi mdi-check-circle-outline me-1"></i>Siap untuk disimpan</span>');
        } else {
            $('#btnSubmitInvoice').prop('disabled', true);
            if (!hasCustomer) {
                $('#submitHelperText').html('<span class="text-warning"><i class="mdi mdi-alert-circle-outline me-1"></i>Pilih pelanggan terlebih dahulu</span>');
            } else {
                $('#submitHelperText').html('<span class="text-muted"><i class="mdi mdi-information-outline me-1"></i>Pilih minimal 1 pesanan untuk menyimpan</span>');
            }
        }
    }

    // Initialize Select2 & Flatpickr
    $(document).ready(function() {
        $('.js-example-basic-single').select2({
            placeholder: "Pilih Pelanggan...",
            width: '100%'
        });

        // Initialize DataTable
        initOrderDataTable();

        // Customer Selection Change Handler
        $('#customerCode').on('change', function() {
            const customerCode = $(this).val();
            const customerName = $('#customerCode option:selected').text().trim();
            const customerId = $('#customerCode option:selected').data('id');

            // Reset selected orders when switching customer
            selectedOrdersMap = {};
            recalculateLiveSummary();

            if (!customerCode) {
                $('#noCustomerPlaceholder').removeClass('d-none');
                $('#orderTableContainer').addClass('d-none');
                $('#customerInfoBox').addClass('d-none');
                $('#previewCustomerName').text('-');
                $('#previewInvoiceNumber').text('-');
                $('#invoiceNumber').val('');
                $('#btnSelectAllOnPage').prop('disabled', true);
                return;
            }

            $('#previewCustomerName').text(customerName);
            $('#btnSelectAllOnPage').prop('disabled', false);

            // Fetch Customer Details & Settings
            fetchCustomerData(customerCode);

            // Fetch Auto-generated Invoice Number
            generateInvoiceNumber(customerId);

            // Show table container, hide placeholder
            $('#noCustomerPlaceholder').addClass('d-none');
            $('#orderTableContainer').removeClass('d-none');

            // Reload DataTable
            if (ordersTable) {
                ordersTable.ajax.reload();
            }
        });

        // Invoice Date Change Handler
        $('#invoiceDate').on('change', function() {
            updateOverdueDate();
            const customerId = $('#customerCode option:selected').data('id');
            if (customerId) {
                generateInvoiceNumber(customerId);
            }
        });

        // Refresh Invoice Number button
        $('#btnRefreshInvoiceNumber').on('click', function() {
            const customerId = $('#customerCode option:selected').data('id');
            if (customerId) {
                const btn = $(this);
                btn.find('i').addClass('mdi-spin');
                generateInvoiceNumber(customerId, function() {
                    btn.find('i').removeClass('mdi-spin');
                });
            } else {
                swal('Perhatian', 'Pilih pelanggan terlebih dahulu!', 'warning');
            }
        });

        // Tax Card Toggle Handlers
        $('#ppnCard').on('click', function(e) {
            if (e.target.tagName !== 'INPUT') {
                const chk = $('#usePpn');
                chk.prop('checked', !chk.is(':checked')).trigger('change');
            }
        });

        $('#pphCard').on('click', function(e) {
            if (e.target.tagName !== 'INPUT') {
                const chk = $('#usePph');
                chk.prop('checked', !chk.is(':checked')).trigger('change');
            }
        });

        $('#usePpn').on('change', function() {
            if ($(this).is(':checked')) {
                $('#ppnCard').addClass('active');
            } else {
                $('#ppnCard').removeClass('active');
            }
            recalculateLiveSummary();
        });

        $('#usePph').on('change', function() {
            if ($(this).is(':checked')) {
                $('#pphCard').addClass('active');
            } else {
                $('#pphCard').removeClass('active');
            }
            recalculateLiveSummary();
        });

        // Order Checkbox Click Handler (delegated)
        $(document).on('change', '.order-checkbox', function() {
            const code = $(this).val();
            const price = Number($(this).data('price') || 0);
            const basePrice = Number($(this).data('base-price') || 0);
            const onCharge = Number($(this).data('on-charge') || 0);
            const shipment = $(this).data('shipment') || code;
            const plate = $(this).data('plate') || '';
            const costs = $(this).data('costs') || [];

            if ($(this).is(':checked')) {
                selectedOrdersMap[code] = { price, basePrice, onCharge, shipment, plate, costs };
                $(this).closest('tr').addClass('selected-row');
            } else {
                delete selectedOrdersMap[code];
                $(this).closest('tr').removeClass('selected-row');
                $('#checkAllHeader').prop('checked', false);
            }

            recalculateLiveSummary();
        });

        // Chip Remove Click Handler
        $(document).on('click', '.chip-remove', function(e) {
            e.stopPropagation();
            const code = $(this).data('code');
            delete selectedOrdersMap[code];
            // Uncheck in DOM if currently visible
            $(`.order-checkbox[value="${code}"]`).prop('checked', false).closest('tr').removeClass('selected-row');
            recalculateLiveSummary();
        });

        // Select All on current page button
        $('#btnSelectAllOnPage').on('click', function() {
            const checkboxes = $('.order-checkbox');
            if (checkboxes.length === 0) return;

            const allCheckedOnPage = checkboxes.filter(':checked').length === checkboxes.length;
            const targetState = !allCheckedOnPage;

            checkboxes.each(function() {
                const chk = $(this);
                const code = chk.val();
                const price = Number(chk.data('price') || 0);
                const basePrice = Number(chk.data('base-price') || 0);
                const onCharge = Number(chk.data('on-charge') || 0);
                const shipment = chk.data('shipment') || code;
                const plate = chk.data('plate') || '';
                const costs = chk.data('costs') || [];

                chk.prop('checked', targetState);
                if (targetState) {
                    selectedOrdersMap[code] = { price, basePrice, onCharge, shipment, plate, costs };
                    chk.closest('tr').addClass('selected-row');
                } else {
                    delete selectedOrdersMap[code];
                    chk.closest('tr').removeClass('selected-row');
                }
            });

            $('#checkAllHeader').prop('checked', targetState);
            recalculateLiveSummary();
        });

        // Header Checkbox Click
        $('#checkAllHeader').on('change', function() {
            const isChecked = $(this).is(':checked');
            $('.order-checkbox').each(function() {
                const chk = $(this);
                const code = chk.val();
                const price = Number(chk.data('price') || 0);
                const basePrice = Number(chk.data('base-price') || 0);
                const onCharge = Number(chk.data('on-charge') || 0);
                const shipment = chk.data('shipment') || code;
                const plate = chk.data('plate') || '';
                const costs = chk.data('costs') || [];

                chk.prop('checked', isChecked);
                if (isChecked) {
                    selectedOrdersMap[code] = { price, basePrice, onCharge, shipment, plate, costs };
                    chk.closest('tr').addClass('selected-row');
                } else {
                    delete selectedOrdersMap[code];
                    chk.closest('tr').removeClass('selected-row');
                }
            });
            recalculateLiveSummary();
        });

        // Clear All Selection
        $('#btnClearAllSelection').on('click', function() {
            selectedOrdersMap = {};
            $('.order-checkbox').prop('checked', false).closest('tr').removeClass('selected-row');
            $('#checkAllHeader').prop('checked', false);
            recalculateLiveSummary();
        });

        // Event handler to view order On Charge detail modal
        $(document).on('click', '.btn-order-cost-detail', function(e) {
            e.preventDefault();
            const code = $(this).data('code');
            const shipment = $(this).data('shipment');
            const basePrice = $(this).data('base-price');
            const onCharge = $(this).data('on-charge');
            const total = $(this).data('total');
            const costs = $(this).data('costs');

            $('#modalOrderShipmentTitle').text('Surat Jalan: ' + shipment + ' (' + code + ')');
            $('#modalOrderBasePrice').text('Rp ' + basePrice);
            $('#modalOrderTotalPrice').text('Rp ' + total);
            $('#modalOrderOnChargeTotal').text('Rp ' + onCharge);

            const tbody = $('#modalOrderCostBody');
            tbody.empty();
            if (costs && costs.length > 0) {
                costs.forEach((c, idx) => {
                    tbody.append(`
                        <tr>
                            <td class="text-center">${idx + 1}</td>
                            <td>
                                <span class="fw-semibold text-dark">${c.component}</span>
                                ${c.description ? `<br><small class="text-muted">${c.description}</small>` : ''}
                            </td>
                            <td class="text-end fw-semibold text-warning">${c.nominalFormatted}</td>
                        </tr>
                    `);
                });
            } else {
                tbody.append('<tr><td colspan="3" class="text-center text-muted">Tidak ada data biaya On Charge</td></tr>');
            }

            $('#modalOrderCostDetail').modal('show');
        });

        // Form Submit Confirmation
        $('#btnSubmitInvoice').on('click', function(e) {
            e.preventDefault();

            const orderCount = Object.keys(selectedOrdersMap).length;
            if (orderCount === 0) {
                swal('Peringatan', 'Silakan pilih minimal 1 pesanan untuk ditagihkan!', 'warning');
                return;
            }

            const invoiceNum = $('#invoiceNumber').val();
            const customerName = $('#customerCode option:selected').text().trim();
            const grandTotalText = $('#summaryGrandTotal').text();

            swal({
                title: "Simpan & Buat Faktur?",
                text: `Faktur ${invoiceNum} untuk ${customerName} dengan total ${grandTotalText} (${orderCount} order) akan diterbitkan.`,
                icon: "info",
                buttons: ["Batal", "Ya, Simpan Faktur!"],
                dangerMode: false,
            }).then((willSave) => {
                if (willSave) {
                    // Show loading
                    swal({
                        title: "Menyimpan Faktur...",
                        text: "Mohon tunggu sebentar",
                        icon: "info",
                        buttons: false,
                        closeOnClickOutside: false,
                        closeOnEsc: false,
                    });

                    $('#invoiceForm').submit();
                }
            });
        });
    });

    // Helper: Initialize DataTable
    function initOrderDataTable() {
        ordersTable = $('#dtOrders').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('dt.invoice.invoice-order') }}",
                data: function(d) {
                    d.customerCode = $('#customerCode').val();
                }
            },
            columns: [
                {
                    data: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
                {
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'orderDate',
                    render: function(data) {
                        return `<span class="fw-semibold text-dark">${data || '-'}</span>`;
                    }
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        const origin = (row.route && row.route.originLocation && row.route.originLocation.name) ? row.route.originLocation.name : '-';
                        const destination = (row.route && row.route.destinationLocation && row.route.destinationLocation.name) ? row.route.destinationLocation.name : '-';
                        return `
                            <div class="d-flex align-items-center gap-1 fs-12">
                                <span class="badge bg-light text-dark border">${origin}</span>
                                <i class="mdi mdi-arrow-right text-primary"></i>
                                <span class="badge bg-light text-dark border">${destination}</span>
                            </div>
                        `;
                    }
                },
                {
                    data: 'shipmentNumber',
                    render: function(data, type, row) {
                        const code = data || row.code || '-';
                        return `<span class="badge bg-light text-dark border font-monospace">${code}</span>`;
                    }
                },
                {
                    data: 'fleet.plateNumber',
                    render: function(data) {
                        return data ? `<span class="badge bg-secondary-subtle text-secondary"><i class="mdi mdi-truck me-1"></i>${data}</span>` : '-';
                    }
                },
                {
                    data: 'basePrice',
                    className: 'text-end'
                },
                {
                    data: 'addCost',
                    className: 'text-end'
                },
                {
                    data: 'totalPrice',
                    className: 'text-end'
                }
            ],
            order: [[2, 'desc']],
            drawCallback: function() {
                // Re-apply checked status for orders currently in selectedOrdersMap
                let allOnPageChecked = true;
                const checkboxes = $('.order-checkbox');

                if (checkboxes.length === 0) {
                    allOnPageChecked = false;
                } else {
                    checkboxes.each(function() {
                        const code = $(this).val();
                        if (selectedOrdersMap[code]) {
                            $(this).prop('checked', true);
                            $(this).closest('tr').addClass('selected-row');
                        } else {
                            $(this).prop('checked', false);
                            $(this).closest('tr').removeClass('selected-row');
                            allOnPageChecked = false;
                        }
                    });
                }

                $('#checkAllHeader').prop('checked', allOnPageChecked);
            },
            language: {
                processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Memuat pesanan...',
                emptyTable: 'Tidak ada pesanan yang siap difakturkan untuk pelanggan ini',
                zeroRecords: 'Tidak ditemukan pesanan yang sesuai',
                search: "Cari Pesanan:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ pesanan",
                infoEmpty: "Menampilkan 0 pesanan",
                paginate: {
                    first: '<i class="mdi mdi-chevron-double-left"></i>',
                    last: '<i class="mdi mdi-chevron-double-right"></i>',
                    next: '<i class="mdi mdi-chevron-right"></i>',
                    previous: '<i class="mdi mdi-chevron-left"></i>'
                }
            }
        });
    }

    // Helper: Update Overdue Date based on invoice date & duration
    function updateOverdueDate() {
        const invDateVal = $('#invoiceDate').val();
        if (invDateVal) {
            const invoiceDate = new Date(invDateVal);
            if (!isNaN(invoiceDate.getTime())) {
                const overdueDate = new Date(invoiceDate);
                overdueDate.setDate(overdueDate.getDate() + (customerDueDateDuration || 30));
                const formatted = overdueDate.toISOString().split('T')[0];
                $('#overdueDate').val(formatted);
                $('#previewOverdueDate').text(formatted);
            }
        }
    }

    // Helper: Fetch Customer Info via AJAX
    function fetchCustomerData(customerCode) {
        $.get("{{ url('ajax/customer-invoice') }}/" + customerCode, function(data) {
            if (!data) return;

            $('#customerInfoBox').removeClass('d-none');
            $('#infoCustomerName').text(data.name || '-');
            $('#infoCustomerCode').text(data.code || '-');
            $('#infoBillingAddress').text(data.billingAddress || data.officeAddress || 'Tidak ada alamat tercatat');

            customerDueDateDuration = data.dueDateDuration ? Number(data.dueDateDuration) : 30;
            $('#infoDueDateDuration').text(customerDueDateDuration + ' Hari');

            // Read customer tax rates
            customerPpnRate = data.ppn !== null && data.ppn !== undefined && data.ppn !== '' ? Number(data.ppn) : 0;
            customerPphRate = data.pph !== null && data.pph !== undefined && data.pph !== '' ? Number(data.pph) : 0;

            // Update tax badges and labels
            $('#ppnLabel').text(customerPpnRate > 0 ? `PPN (${customerPpnRate}%)` : 'PPN (0% / Non-PPN)');
            $('#pphLabel').text(customerPphRate > 0 ? `PPh 23 (${customerPphRate}%)` : 'PPh (0%)');
            $('#summaryPpnBadge').text(customerPpnRate + '%');
            $('#summaryPphBadge').text(customerPphRate + '%');

            // Auto-check taxes based on customer settings
            const hasPpn = customerPpnRate > 0;
            const hasPph = customerPphRate > 0;

            $('#usePpn').prop('checked', hasPpn).trigger('change');
            $('#usePph').prop('checked', hasPph).trigger('change');

            // Recalculate Overdue Date
            updateOverdueDate();

            // Render PIC Badges if available
            const picSection = $('#picSection');
            const picBadges = $('#picBadges');
            picBadges.empty();

            if (data.pic && data.pic.length > 0) {
                let hasPic = false;
                data.pic.forEach(item => {
                    if (item.picName || item.phone) {
                        hasPic = true;
                        picBadges.append(`
                            <span class="badge bg-light text-dark border px-2 py-1 fs-12">
                                <i class="mdi mdi-account-circle text-primary me-1"></i>${item.picName || 'PIC'} 
                                ${item.phone ? `<span class="text-muted">(${item.phone})</span>` : ''}
                            </span>
                        `);
                    }
                });

                if (hasPic) {
                    picSection.removeClass('d-none');
                } else {
                    picSection.addClass('d-none');
                }
            } else {
                picSection.addClass('d-none');
            }
        });
    }

    // Helper: Fetch Auto-Generated Invoice Number
    function generateInvoiceNumber(customerId, callback) {
        const invoiceDate = $('#invoiceDate').val();
        if (!customerId) return;

        $.get("{{ url('ajax/invoice-number-format') }}/" + customerId, {
            invoiceDate: invoiceDate
        }, function(data) {
            $('#invoiceNumber').val(data);
            $('#previewInvoiceNumber').text(data || '-');
            if (typeof callback === 'function') callback();
        }).fail(function() {
            if (typeof callback === 'function') callback();
        });
    }
</script>
@endpush
