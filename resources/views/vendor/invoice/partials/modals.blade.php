{{-- Modals & hidden forms untuk halaman Vendor -> Invoice Belum Lunas.
    Di-include dari vendor/invoice/unpaid.blade.php dan vendor/order/waiting.blade.php.
    Ported dari finance/vendor-payment/index.blade.php (modal pembayaran, detail,
    multi PDF, generate nota, batal pembayaran, batal nota). --}}

@include('vendor.invoice.partials.nota-modal-style')

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

                            <div class="payment-bank-balance mb-3 d-none" id="selectedBankBalancePanel">
                                <span>Saldo tersedia</span>
                                <strong id="selectedBankBalance">Rp 0</strong>
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
<div class="modal fade bd-example-modal-lg" id="detail-modal" tabindex="-1" role="dialog"
    aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="detailModalLabel">{{ __('menu_vendor_payment.payment_detail') }}</h4>
                <button class="btn-close py-0" type="button" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="card">
                <div class="card-body col-md-12">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">{{ __('menu_vendor_payment.payment_code') }}</label>
                            <input class="form-control" id="detail-code" type="text" readonly>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Status Pembayaran</label>
                            <input class="form-control" id="detail-payment-status" type="text" readonly>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Nomor Nota Kalender</label>
                            <input class="form-control" id="detail-nota-number" type="text" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ __('menu_vendor_payment.order_code') }}</label>
                            <input class="form-control" id="detail-order-code" type="text" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Shipment Number / No Pengiriman</label>
                            <input class="form-control" id="detail-shipment-number" type="text" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ __('menu_vendor_payment.plate_number') }}</label>
                            <input class="form-control" id="detail-plate-number" type="text" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Perusahaan Kendaraan</label>
                            <input class="form-control" id="detail-fleet-company" type="text" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ __('menu_vendor_payment.driver') }}</label>
                            <input class="form-control" id="detail-driver" type="text" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ __('menu_vendor_payment.customer') }}</label>
                            <input class="form-control" id="detail-customer" type="text" readonly>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Tagihan</label>
                            <input class="form-control" id="detail-billing-amount" type="text" readonly>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-primary">PPN (rate → nominal)</label>
                            <input class="form-control text-primary fw-semibold" id="detail-ppn-amount" type="text" readonly>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-danger">PPh (rate → nominal, dipotong)</label>
                            <input class="form-control text-danger fw-semibold" id="detail-pph-amount" type="text" readonly>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Terbayar</label>
                            <input class="form-control" id="detail-paid-amount" type="text" readonly>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Sisa</label>
                            <input class="form-control" id="detail-remaining-amount" type="text" readonly>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">{{ __('menu_vendor_payment.bank_source') }}</label>
                            <input class="form-control" id="detail-bank" type="text" readonly>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">{{ __('menu_vendor_payment.description') }}</label>
                            <textarea class="form-control" id="detail-description" rows="2" readonly></textarea>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Riwayat Pembayaran</label>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered" id="payment-history-table">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Jumlah</th>
                                            <th>Bank</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody id="payment-history-body">
                                    </tbody>
                                </table>
                            </div>
                        </div>
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

                        {{-- Grand total --}}
                        <div class="nota-grand-total">
                            <div class="nota-grand-total-label">
                                <i class="mdi mdi-cash-check me-2"></i>TOTAL BAYAR
                                <small class="d-block fw-normal">Subtotal + PPN &minus; PPh</small>
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
