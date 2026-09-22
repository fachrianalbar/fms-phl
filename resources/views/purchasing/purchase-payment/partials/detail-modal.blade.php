{{-- Modal detail pembelian + riwayat pembayaran (HTML saja). Dipakai index & paid.
    Skripnya ada di partials/detail-modal-script.blade.php (di-include di @push('script')
    SETELAH library jQuery/SweetAlert2 dimuat). --}}

<div class="modal fade" id="purchase-detail-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-0">
                        <i class="mdi mdi-receipt-text-outline me-1 text-primary"></i>Detail Pembelian
                    </h5>
                    <small class="text-muted">Kode: <strong id="pd-code">-</strong></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-3">
                    <div class="col-4">
                        <div class="detail-amount-tile" data-role="billing">
                            <div class="detail-tile-label">Total Tagihan</div>
                            <div class="detail-tile-value" id="pd-billing">Rp 0</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="detail-amount-tile" data-role="paid">
                            <div class="detail-tile-label">Sudah Dibayar</div>
                            <div class="detail-tile-value text-success" id="pd-paid">Rp 0</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="detail-amount-tile" data-role="remaining">
                            <div class="detail-tile-label">Sisa Hutang</div>
                            <div class="detail-tile-value text-danger" id="pd-remaining">Rp 0</div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="detail-tile-label">Supplier</div>
                        <div class="fw-semibold" id="pd-supplier">-</div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-tile-label">Status Pembayaran</div>
                        <div id="pd-status">-</div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-tile-label">Tanggal Pembelian</div>
                        <div class="fw-semibold" id="pd-date">-</div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-tile-label">Jatuh Tempo</div>
                        <div class="fw-semibold" id="pd-due-date">-</div>
                    </div>
                </div>

                <h6 class="fw-bold mb-2">
                    <i class="mdi mdi-history me-1 text-primary"></i>Riwayat Pembayaran
                </h6>
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 40px;">#</th>
                                <th>Tanggal</th>
                                <th>Kode Batch</th>
                                <th>Bank</th>
                                <th class="text-end">Nominal</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody id="pd-history-body"></tbody>
                    </table>
                </div>
                <div class="text-muted text-center py-3 d-none" id="pd-history-empty">
                    <i class="mdi mdi-information-outline me-1"></i>Belum ada pembayaran untuk pembelian ini.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
