{{-- Modals & hidden forms untuk halaman Vendor -> Invoice Belum Lunas.
    Di-include dari vendor/invoice/unpaid.blade.php dan vendor/order/waiting.blade.php.
    Ported dari finance/vendor-payment/index.blade.php (modal pembayaran, detail,
    multi PDF, generate nota, batal pembayaran, batal nota). --}}

@include('vendor.invoice.partials.nota-modal-style')

{{-- Form & Modal: pembayaran batch ke vendor (lunas / DP / cicilan) --}}
<form class="row g-3" method="post" action="{{ route('vendor.invoice.payment.store') }}" id="batch-payment-form">
    @csrf
    <div class="modal fade bd-example-modal-lg" id="payment-modal" tabindex="-1" role="dialog"
        aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div id="selectedOrderCodesContainer"></div>
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">{{ __('menu_vendor_payment.payment_data') }}
                    </h4>
                    <button class="btn-close py-0" type="button" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="card">
                    <div class="card-body col-md-12">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Jumlah Order Dipilih</label>
                                <input class="form-control" id="selectedOrderCount" type="text" readonly>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Kode Order Dipilih</label>
                                <textarea class="form-control" id="selectedOrderList" rows="2" readonly></textarea>
                            </div>

                            <div class="col-md-12" id="notaNumberContainer" style="display: none;">
                                <label class="form-label">Nomor Nota Kalender</label>
                                <input class="form-control" id="selectedNotaNumber" type="text" readonly>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Tagihan Vendor <span class="text-muted fw-normal">(sudah termasuk pajak)</span></label>
                                <input class="form-control" id="billingAmount" type="text" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-primary">PPN</label>
                                <input class="form-control text-primary fw-semibold" id="paymentPpnAmount" type="text" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-danger">PPh <span class="text-muted fw-normal">(dipotong)</span></label>
                                <input class="form-control text-danger fw-semibold" id="paymentPphAmount" type="text" readonly>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Sisa Tagihan</label>
                                <input class="form-control" id="remainingAmount" type="text" readonly>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Sudah Terbayar</label>
                                <input class="form-control" id="paidAmount" type="text" readonly>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label" id="paymentAmountLabel">Total Pembayaran</label>
                                <input class="form-control" id="totalPaymentAmount" type="text" required>
                                <input type="hidden" name="paymentAmount" id="hiddenPaymentAmount">
                                <small class="form-text text-muted" id="paymentAmountHelp">Nominal
                                    pembayaran.</small>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label" for="date">{{ __('menu_vendor_payment.payment_date') }}</label>
                                <input class="form-control" name="date" id="date" type="date"
                                    placeholder="{{ __('menu_vendor_payment.payment_date') }}" required>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label" for="userBankCode">Sumber Dana (Bank) <span
                                        class="text-danger">*</span></label>
                                <select class="js-example-basic form-select" name="userBankCode" id="userBankCode" required>
                                    <option value="">Pilih Bank</option>
                                    <option value="" disabled>-- Loading data bank --</option>
                                </select>
                                <small class="form-text text-muted">Pilih bank sumber dana untuk pembayaran</small>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label"
                                    for="description">{{ __('menu_vendor_payment.description') }}</label>
                                <textarea class="form-control" name="description" id="description" rows="3"
                                    placeholder="{{ __('menu_vendor_payment.description') }}"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer justify-content-start">
                    <button class="btn btn-primary" type="submit">{{ __('general.save') }}</button>
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
                            <label class="form-label text-primary">PPN</label>
                            <input class="form-control text-primary fw-semibold" id="detail-ppn-amount" type="text" readonly>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-danger">PPh (dipotong)</label>
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

{{-- Form & Modal untuk Generate Nota (dengan input manual PPN & PPh) --}}
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
                            <span class="nota-tax-hint ms-auto"><i class="mdi mdi-information-outline me-1"></i>PPN &amp;
PPh diinput manual</span>
                        </div>

                        {{-- Subtotal / DPP --}}
                        <div class="d-flex justify-content-between align-items-center nota-calc-row">
                            <div class="nota-calc-label"><i class="mdi mdi-sigma me-2 text-secondary"></i>Subtotal
                                (DPP)
                                <small class="d-block text-muted">Total tagihan order terpilih</small>
                            </div>
                            <div class="nota-calc-value fw-semibold" id="notaSubtotal">Rp 0</div>
                        </div>

                        {{-- PPN manual --}}
                        <div class="nota-calc-row">
                            <div class="nota-calc-label mb-2"><label class="form-label nota-field-label mb-0"
                                    for="notaPpnAmount"><i class="mdi mdi-percent-outline me-2 text-primary"></i>PPN
                                    (Pajak Pertambahan Nilai)</label></div>
                            <div class="input-group nota-tax-input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control nota-tax-input" id="notaPpnAmount"
                                    name="ppnAmount" value="0" inputmode="numeric" autocomplete="off"
                                    placeholder="0">
                                <span class="input-group-text nota-tax-preview" id="notaPpnPreview">Rp 0</span>
                            </div>
                            <small class="form-text text-muted">Kosongkan / 0 apabila nota tidak dikenakan PPN</small>
                        </div>

                        {{-- PPh manual --}}
                        <div class="nota-calc-row">
                            <div class="nota-calc-label mb-2"><label class="form-label nota-field-label mb-0"
                                    for="notaPphAmount"><i class="mdi mdi-cash-refund me-2 text-danger"></i>PPh
                                    (Pajak Penghasilan &mdash; dipotong)</label></div>
                            <div class="input-group nota-tax-input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control nota-tax-input" id="notaPphAmount"
                                    name="pphAmount" value="0" inputmode="numeric" autocomplete="off"
                                    placeholder="0">
                                <span class="input-group-text nota-tax-preview" id="notaPphPreview">Rp 0</span>
                            </div>
                            <small class="form-text text-muted">PPh dipotong dari pembayaran ke vendor (isi 0 apabila
                                tidak ada)</small>
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
</form>

{{-- Form untuk Batal Nota (action di-set via JS) --}}
<form id="cancel-nota-form" method="post" style="display: none;">
    @csrf
</form>
