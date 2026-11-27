<div class="modal fade" id="vendorTaxModal" tabindex="-1" aria-labelledby="vendorTaxModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 14px; overflow: hidden;">
            <div class="modal-header border-bottom py-3">
                <div>
                    <h5 class="modal-title fw-bold mb-0 d-flex align-items-center gap-2" id="vendorTaxModalLabel">
                        <i class="mdi mdi-file-document-edit-outline text-primary fs-20"></i>
                        Input Pajak Vendor
                    </h5>
                    <small class="text-muted" id="vendorTaxModalSubtitle">-</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="vendorTaxForm">
                @csrf
                <input type="hidden" id="vendorTaxPaymentId" value="">

                <div class="modal-body p-4">
                    <div id="vendorTaxAlert" class="alert alert-danger d-none py-2 px-3 small mb-3"></div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small" for="tax_invoice_number">No Faktur Pajak</label>
                        <input type="text" class="form-control" id="tax_invoice_number" name="tax_invoice_number"
                            maxlength="100" placeholder="Masukkan nomor faktur pajak">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small" for="withholding_tax_number">No Bukti Potong</label>
                        <input type="text" class="form-control" id="withholding_tax_number" name="withholding_tax_number"
                            maxlength="100" placeholder="Masukkan nomor bukti potong">
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small" for="withholding_tax_amount">Nominal Bukti Potong</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" inputmode="numeric"
                                    class="form-control rupiah-input text-end font-monospace"
                                    id="withholding_tax_amount" name="withholding_tax_amount" placeholder="0">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small" for="withholding_tax_date">Tgl Bukti Potong</label>
                            <input type="text" class="form-control" id="withholding_tax_date"
                                name="withholding_tax_date" placeholder="YYYY-MM-DD">
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top py-3">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1" id="btnSaveVendorTax">
                        <i class="mdi mdi-content-save-outline"></i> Simpan Pajak
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
