{{-- Modal: daftar order yang tercakup dalam satu nota vendor.
    Di-include dari vendor/invoice/unpaid.blade.php (dipakai juga halaman partial).
    Data baris dikirim lewat kolom `order_items` pada datatable nota. --}}
<style>
    /* ── Modal Order dalam Nota (PHL Modal Standard §11) ── */
    .nota-orders-modal .modal-content {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 24px 64px rgba(15, 23, 42, 0.18);
    }

    .nota-orders-modal .nota-orders-header {
        padding: 16px 20px;
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
    }

    .nota-orders-modal .nota-orders-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #eef2ff;
        color: #4f46e5;
        font-size: 19px;
        flex-shrink: 0;
    }

    .nota-orders-modal .nota-orders-summary {
        display: flex;
        flex-wrap: wrap;
        gap: 8px 24px;
        padding: 12px 16px;
        margin-bottom: 16px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
    }

    .nota-orders-modal .nota-orders-summary-item {
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 120px;
    }

    .nota-orders-modal .nota-orders-summary-item span {
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #94a3b8;
    }

    .nota-orders-modal .nota-orders-summary-item strong {
        font-size: 13px;
        font-weight: 700;
        color: #0f172a;
    }

    .nota-orders-modal .nota-orders-table-wrap {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: auto;
        max-height: 52vh;
    }

    .nota-orders-modal .nota-orders-table {
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }

    .nota-orders-modal .nota-orders-table thead th {
        position: sticky;
        top: 0;
        z-index: 1;
        background: #f8fafc;
        color: #475569;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        padding: 10px 12px;
        border-bottom: 1px solid #e2e8f0;
        white-space: nowrap;
    }

    .nota-orders-modal .nota-orders-table tbody td {
        padding: 9px 12px;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        font-size: 12.5px;
        vertical-align: middle;
    }

    .nota-orders-modal .nota-orders-table tbody tr:last-child td {
        border-bottom: none;
    }

    .nota-orders-modal .nota-orders-table tbody tr:hover {
        background: #f8fafc;
    }

    .nota-orders-modal .nota-orders-table tfoot td {
        position: sticky;
        bottom: 0;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        padding: 10px 12px;
        font-size: 12.5px;
        font-weight: 700;
        color: #0f172a;
    }

    .nota-orders-modal .nota-orders-code {
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-weight: 600;
        color: #0f172a;
        white-space: nowrap;
    }

    .nota-orders-modal .nota-orders-money {
        text-align: right;
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-variant-numeric: tabular-nums;
        font-weight: 600;
        white-space: nowrap;
    }

    .nota-orders-modal .nota-orders-empty {
        padding: 28px 12px;
        text-align: center;
        color: #94a3b8;
        font-size: 12.5px;
    }

    .nota-orders-modal .modal-footer {
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
    }

    /* ── Dark mode (wajib) ── */
    html[data-bs-theme="dark"] .nota-orders-modal .modal-content {
        background-color: var(--bs-card-bg) !important;
        border-color: var(--bs-border-color) !important;
    }

    html[data-bs-theme="dark"] .nota-orders-modal .nota-orders-header {
        background-color: var(--bs-card-bg) !important;
        border-bottom-color: var(--bs-border-color) !important;
    }

    html[data-bs-theme="dark"] .nota-orders-modal .nota-orders-icon {
        background: rgba(99, 102, 241, 0.18);
        color: #a5b4fc;
    }

    html[data-bs-theme="dark"] .nota-orders-modal .nota-orders-summary {
        background: var(--bs-secondary-bg);
        border-color: var(--bs-border-color);
    }

    html[data-bs-theme="dark"] .nota-orders-modal .nota-orders-summary-item span {
        color: var(--bs-secondary-color);
    }

    html[data-bs-theme="dark"] .nota-orders-modal .nota-orders-summary-item strong {
        color: var(--bs-body-color);
    }

    html[data-bs-theme="dark"] .nota-orders-modal .nota-orders-table-wrap {
        border-color: var(--bs-border-color);
    }

    html[data-bs-theme="dark"] .nota-orders-modal .nota-orders-table thead th {
        background: var(--bs-tertiary-bg);
        color: var(--bs-body-color);
        border-bottom-color: var(--bs-border-color);
    }

    html[data-bs-theme="dark"] .nota-orders-modal .nota-orders-table tbody td {
        color: var(--bs-body-color);
        border-bottom-color: var(--bs-border-color);
    }

    html[data-bs-theme="dark"] .nota-orders-modal .nota-orders-table tbody tr:hover {
        background: var(--bs-tertiary-bg);
    }

    html[data-bs-theme="dark"] .nota-orders-modal .nota-orders-table tfoot td {
        background: var(--bs-tertiary-bg);
        border-top-color: var(--bs-border-color);
        color: var(--bs-body-color);
    }

    html[data-bs-theme="dark"] .nota-orders-modal .nota-orders-code {
        color: var(--bs-body-color);
    }

    html[data-bs-theme="dark"] .nota-orders-modal .modal-footer {
        background: var(--bs-secondary-bg) !important;
        border-top-color: var(--bs-border-color) !important;
    }
</style>

<div class="modal fade nota-orders-modal" id="nota-orders-modal" tabindex="-1" role="dialog"
    aria-labelledby="notaOrdersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header nota-orders-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <span class="nota-orders-icon" aria-hidden="true">
                        <i class="mdi mdi-format-list-bulleted"></i>
                    </span>
                    <div>
                        <h5 class="modal-title mb-0 fw-bold" id="notaOrdersModalLabel">Order dalam Nota</h5>
                        <small class="text-muted">Daftar order yang sudah dibuatkan nota vendor ini.</small>
                    </div>
                </div>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <div class="modal-body p-4">
                <div class="nota-orders-summary" id="notaOrdersSummary">
                    <div class="nota-orders-summary-item">
                        <span>No Nota</span>
                        <strong id="nota-orders-number">-</strong>
                    </div>
                    <div class="nota-orders-summary-item">
                        <span>Vendor</span>
                        <strong id="nota-orders-vendor">-</strong>
                    </div>
                    <div class="nota-orders-summary-item">
                        <span>Jumlah Order</span>
                        <strong id="nota-orders-count">0 order</strong>
                    </div>
                    <div class="nota-orders-summary-item">
                        <span>Total Tagihan</span>
                        <strong id="nota-orders-total">Rp 0</strong>
                    </div>
                </div>

                <div class="nota-orders-table-wrap custom-scrollbar">
                    <table class="table nota-orders-table align-middle">
                        <thead>
                            <tr>
                                <th style="width: 44px;" class="text-center">No</th>
                                <th>Kode Order</th>
                                <th>No Pengiriman</th>
                                <th>Nopol</th>
                                <th>Customer</th>
                                <th>Rute</th>
                                <th class="text-end">Tagihan</th>
                            </tr>
                        </thead>
                        <tbody id="nota-orders-body">
                            <tr>
                                <td colspan="7" class="nota-orders-empty">Belum ada data order.</td>
                            </tr>
                        </tbody>
                        <tfoot id="nota-orders-foot" class="d-none">
                            <tr>
                                <td colspan="6" class="text-end">Total tagihan order</td>
                                <td class="nota-orders-money" id="nota-orders-foot-total">Rp 0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="modal-footer py-2 px-4 border-top d-flex justify-content-between">
                <span class="text-muted fs-12 me-auto" id="nota-orders-hint">Klik Cetak Nota pada baris tabel untuk mengunduh dokumen.</span>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"
                    style="border-radius: 8px; padding: 6px 16px;">Tutup</button>
            </div>
        </div>
    </div>
</div>
