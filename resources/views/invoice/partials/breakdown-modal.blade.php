<!-- Modal Detail Rincian Biaya Faktur & On Charge -->
<div class="modal fade" id="modalInvoiceCostBreakdown" tabindex="-1" aria-labelledby="modalInvoiceCostBreakdownLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg mt-4">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-primary text-white py-3 px-4 border-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="avatar-sm">
                        <span class="avatar-title rounded-circle bg-white text-primary fs-18">
                            <i class="mdi mdi-receipt-text-check"></i>
                        </span>
                    </span>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0" id="modalInvoiceCostBreakdownLabel">Rincian Tagihan & Biaya On Charge</h5>
                        <small class="text-white-50" id="modalBreakdownSubtitle">Faktur: -</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Header Info Box -->
                <div class="p-3 rounded bg-light border mb-3">
                    <div class="row g-2 fs-12">
                        <div class="col-sm-4">
                            <span class="text-muted d-block">No. Faktur:</span>
                            <span class="fw-bold text-dark font-monospace fs-13" id="modalBreakdownInvoiceNo">-</span>
                        </div>
                        <div class="col-sm-5">
                            <span class="text-muted d-block">Pelanggan:</span>
                            <span class="fw-semibold text-dark fs-13" id="modalBreakdownCustomer">-</span>
                        </div>
                        <div class="col-sm-3">
                            <span class="text-muted d-block">Tanggal Faktur:</span>
                            <span class="text-dark fs-13" id="modalBreakdownDate">-</span>
                        </div>
                    </div>
                </div>

                <!-- Financial Summary Cards -->
                <div class="row g-2 mb-3">
                    <div class="col-sm-3 col-6">
                        <div class="p-2 border rounded text-center bg-light">
                            <small class="text-muted d-block fs-11">Tarif Rute (Pokok)</small>
                            <span class="fw-bold text-dark fs-13" id="modalBreakdownTotalRoute">Rp 0</span>
                        </div>
                    </div>
                    <div class="col-sm-3 col-6">
                        <div class="p-2 border border-warning-subtle rounded text-center bg-warning-subtle">
                            <small class="text-warning d-block fs-11 fw-semibold">Total On Charge</small>
                            <span class="fw-bold text-warning fs-13" id="modalBreakdownTotalOnCharge">+ Rp 0</span>
                        </div>
                    </div>
                    <div class="col-sm-3 col-6">
                        <div class="p-2 border rounded text-center bg-light">
                            <small class="text-muted d-block fs-11">Subtotal (DPP)</small>
                            <span class="fw-bold text-dark fs-13" id="modalBreakdownSubtotal">Rp 0</span>
                        </div>
                    </div>
                    <div class="col-sm-3 col-6">
                        <div class="p-2 border border-success-subtle rounded text-center bg-success-subtle">
                            <small class="text-success d-block fs-11 fw-semibold">Grand Total</small>
                            <span class="fw-bold text-success fs-13" id="modalBreakdownGrandTotal">Rp 0</span>
                        </div>
                    </div>
                </div>

                <!-- Section 1: Itemized On Charge Components -->
                <div class="mb-3" id="modalBreakdownComponentSection">
                    <h6 class="fw-bold text-dark fs-13 mb-2 d-flex align-items-center gap-2">
                        <i class="mdi mdi-cash-multiple text-warning fs-16"></i>
                        <span>Komponen Biaya On Charge yang Ditagihkan</span>
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-0">
                            <thead class="table-light fs-12">
                                <tr>
                                    <th width="35" class="text-center">No</th>
                                    <th>Nama Komponen Biaya</th>
                                    <th class="text-end">Total Nominal</th>
                                </tr>
                            </thead>
                            <tbody id="modalBreakdownComponentBody" class="fs-12"></tbody>
                        </table>
                    </div>
                </div>

                <!-- Section 2: Order List Breakdown -->
                <div>
                    <h6 class="fw-bold text-dark fs-13 mb-2 d-flex align-items-center gap-2">
                        <i class="mdi mdi-truck-fast-outline text-primary fs-16"></i>
                        <span>Daftar Pesanan / Surat Jalan dalam Faktur Ini</span>
                    </h6>
                    <div class="table-responsive custom-scrollbar" style="max-height: 220px; overflow-y: auto;">
                        <table class="table table-sm table-hover table-bordered align-middle mb-0">
                            <thead class="table-light fs-12 sticky-top">
                                <tr>
                                    <th width="35" class="text-center">No</th>
                                    <th>No. Surat Jalan</th>
                                    <th>Rute</th>
                                    <th class="text-end">Tarif Rute</th>
                                    <th class="text-end">On Charge</th>
                                    <th class="text-end">Total Tagihan</th>
                                </tr>
                            </thead>
                            <tbody id="modalBreakdownOrderBody" class="fs-12"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light px-4 py-2 border-0">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
