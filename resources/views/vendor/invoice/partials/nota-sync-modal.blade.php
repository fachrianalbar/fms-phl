{{-- Modal: pratinjau & konfirmasi sinkronisasi nominal nota dari harga order terkini.
     Di-include dari vendor/invoice/unpaid.blade.php (unpaid & partial) dan paid.blade.php.
     Handler JS: vendor/invoice/partials/nota-sync-script.blade.php --}}
<style>
    /* ── Modal Sinkronkan Nominal Nota (PHL Modal Standard §11) ── */
    .nota-sync-modal .modal-content {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 24px 64px rgba(15, 23, 42, 0.18);
    }

    .nota-sync-modal .nota-sync-header {
        padding: 16px 20px;
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
    }

    .nota-sync-modal .nota-sync-icon {
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

    .nota-sync-modal .nota-sync-summary {
        display: flex;
        flex-wrap: wrap;
        gap: 8px 24px;
        padding: 12px 16px;
        margin-bottom: 14px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
    }

    .nota-sync-modal .nota-sync-summary-item {
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 118px;
    }

    .nota-sync-modal .nota-sync-summary-item span {
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #94a3b8;
    }

    .nota-sync-modal .nota-sync-summary-item strong {
        font-size: 13px;
        font-weight: 700;
        color: #0f172a;
    }

    .nota-sync-modal .nota-sync-summary-item strong.nota-sync-diff-up {
        color: #047857;
    }

    .nota-sync-modal .nota-sync-summary-item strong.nota-sync-diff-down {
        color: #b91c1c;
    }

    .nota-sync-modal .nota-sync-alert {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        padding: 10px 12px;
        margin-bottom: 14px;
        border-radius: 10px;
        border: 1px solid transparent;
        font-size: 12.5px;
        line-height: 1.5;
    }

    .nota-sync-modal .nota-sync-alert i {
        font-size: 15px;
        line-height: 1.3;
        flex-shrink: 0;
    }

    .nota-sync-modal .nota-sync-alert.is-info {
        background: #eff6ff;
        border-color: #bfdbfe;
        color: #1d4ed8;
    }

    .nota-sync-modal .nota-sync-alert.is-success {
        background: #ecfdf5;
        border-color: #a7f3d0;
        color: #047857;
    }

    .nota-sync-modal .nota-sync-alert.is-warning {
        background: #fffbeb;
        border-color: #fde68a;
        color: #b45309;
    }

    .nota-sync-modal .nota-sync-alert.is-danger {
        background: #fef2f2;
        border-color: #fecaca;
        color: #b91c1c;
    }

    .nota-sync-modal .nota-sync-table-wrap {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: auto;
        max-height: 46vh;
    }

    .nota-sync-modal .nota-sync-table {
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }

    .nota-sync-modal .nota-sync-table thead th {
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

    .nota-sync-modal .nota-sync-table tbody td {
        padding: 9px 12px;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        font-size: 12.5px;
        vertical-align: middle;
    }

    .nota-sync-modal .nota-sync-table tbody tr:last-child td {
        border-bottom: none;
    }

    .nota-sync-modal .nota-sync-table tbody tr.is-changed {
        background: #f5f3ff;
    }

    .nota-sync-modal .nota-sync-table tfoot td {
        position: sticky;
        bottom: 0;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        padding: 10px 12px;
        font-size: 12.5px;
        font-weight: 700;
        color: #0f172a;
    }

    .nota-sync-modal .nota-sync-code {
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-weight: 600;
        color: #0f172a;
        white-space: nowrap;
    }

    .nota-sync-modal .nota-sync-money {
        text-align: right;
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-variant-numeric: tabular-nums;
        font-weight: 600;
        white-space: nowrap;
    }

    .nota-sync-modal .nota-sync-delta {
        text-align: right;
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-variant-numeric: tabular-nums;
        font-weight: 700;
        white-space: nowrap;
    }

    .nota-sync-modal .nota-sync-delta.is-up {
        color: #047857;
    }

    .nota-sync-modal .nota-sync-delta.is-down {
        color: #b91c1c;
    }

    .nota-sync-modal .nota-sync-empty {
        padding: 26px 12px;
        text-align: center;
        color: #94a3b8;
        font-size: 12.5px;
    }

    .nota-sync-modal .nota-sync-note {
        margin: 12px 0 0;
        font-size: 11.5px;
        color: #64748b;
        line-height: 1.6;
    }

    .nota-sync-modal .modal-footer {
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        gap: 8px;
    }

    /* ── Dark mode (wajib) ── */
    html[data-bs-theme="dark"] .nota-sync-modal .modal-content {
        background-color: var(--bs-card-bg) !important;
        border-color: var(--bs-border-color) !important;
    }

    html[data-bs-theme="dark"] .nota-sync-modal .nota-sync-header {
        background-color: var(--bs-card-bg) !important;
        border-bottom-color: var(--bs-border-color) !important;
    }

    html[data-bs-theme="dark"] .nota-sync-modal .nota-sync-icon {
        background: rgba(99, 102, 241, 0.18);
        color: #a5b4fc;
    }

    html[data-bs-theme="dark"] .nota-sync-modal .nota-sync-summary {
        background: var(--bs-secondary-bg);
        border-color: var(--bs-border-color);
    }

    html[data-bs-theme="dark"] .nota-sync-modal .nota-sync-summary-item span {
        color: var(--bs-secondary-color);
    }

    html[data-bs-theme="dark"] .nota-sync-modal .nota-sync-summary-item strong {
        color: var(--bs-body-color);
    }

    html[data-bs-theme="dark"] .nota-sync-modal .nota-sync-summary-item strong.nota-sync-diff-up {
        color: #34d399;
    }

    html[data-bs-theme="dark"] .nota-sync-modal .nota-sync-summary-item strong.nota-sync-diff-down {
        color: #f87171;
    }

    html[data-bs-theme="dark"] .nota-sync-modal .nota-sync-alert.is-info {
        background: rgba(37, 99, 235, 0.16);
        border-color: rgba(96, 165, 250, 0.35);
        color: #93c5fd;
    }

    html[data-bs-theme="dark"] .nota-sync-modal .nota-sync-alert.is-success {
        background: rgba(16, 185, 129, 0.14);
        border-color: rgba(52, 211, 153, 0.32);
        color: #6ee7b7;
    }

    html[data-bs-theme="dark"] .nota-sync-modal .nota-sync-alert.is-warning {
        background: rgba(245, 158, 11, 0.14);
        border-color: rgba(251, 191, 36, 0.32);
        color: #fcd34d;
    }

    html[data-bs-theme="dark"] .nota-sync-modal .nota-sync-alert.is-danger {
        background: rgba(239, 68, 68, 0.14);
        border-color: rgba(248, 113, 113, 0.32);
        color: #fca5a5;
    }

    html[data-bs-theme="dark"] .nota-sync-modal .nota-sync-table-wrap {
        border-color: var(--bs-border-color);
    }

    html[data-bs-theme="dark"] .nota-sync-modal .nota-sync-table thead th {
        background: var(--bs-tertiary-bg);
        color: var(--bs-body-color);
        border-bottom-color: var(--bs-border-color);
    }

    html[data-bs-theme="dark"] .nota-sync-modal .nota-sync-table tbody td {
        color: var(--bs-body-color);
        border-bottom-color: var(--bs-border-color);
    }

    html[data-bs-theme="dark"] .nota-sync-modal .nota-sync-table tbody tr.is-changed {
        background: rgba(99, 102, 241, 0.12);
    }

    html[data-bs-theme="dark"] .nota-sync-modal .nota-sync-table tfoot td {
        background: var(--bs-tertiary-bg);
        border-top-color: var(--bs-border-color);
        color: var(--bs-body-color);
    }

    html[data-bs-theme="dark"] .nota-sync-modal .nota-sync-code {
        color: var(--bs-body-color);
    }

    html[data-bs-theme="dark"] .nota-sync-modal .nota-sync-delta.is-up {
        color: #34d399;
    }

    html[data-bs-theme="dark"] .nota-sync-modal .nota-sync-delta.is-down {
        color: #f87171;
    }

    html[data-bs-theme="dark"] .nota-sync-modal .nota-sync-note {
        color: var(--bs-secondary-color);
    }

    html[data-bs-theme="dark"] .nota-sync-modal .modal-footer {
        background: var(--bs-secondary-bg) !important;
        border-top-color: var(--bs-border-color) !important;
    }
</style>

<div class="modal fade nota-sync-modal" id="nota-sync-modal" tabindex="-1" role="dialog"
    aria-labelledby="notaSyncModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header nota-sync-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <span class="nota-sync-icon" aria-hidden="true">
                        <i class="mdi mdi-sync"></i>
                    </span>
                    <div>
                        <h5 class="modal-title mb-0 fw-bold" id="notaSyncModalLabel">Sinkronkan Nominal Nota</h5>
                        <small class="text-muted">Nominal nota dihitung ulang dari harga order terkini.</small>
                    </div>
                </div>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <div class="modal-body p-4">
                <div class="nota-sync-summary">
                    <div class="nota-sync-summary-item">
                        <span>No Nota</span>
                        <strong id="nota-sync-number">-</strong>
                    </div>
                    <div class="nota-sync-summary-item">
                        <span>Vendor</span>
                        <strong id="nota-sync-vendor">-</strong>
                    </div>
                    <div class="nota-sync-summary-item">
                        <span>Total Lama</span>
                        <strong id="nota-sync-total-old">Rp 0</strong>
                    </div>
                    <div class="nota-sync-summary-item">
                        <span>Total Baru</span>
                        <strong id="nota-sync-total-new">Rp 0</strong>
                    </div>
                    <div class="nota-sync-summary-item">
                        <span>Selisih</span>
                        <strong id="nota-sync-diff">Rp 0</strong>
                    </div>
                </div>

                <div class="nota-sync-alert is-info" id="nota-sync-alert">
                    <i class="mdi mdi-information-outline" aria-hidden="true"></i>
                    <span id="nota-sync-alert-text">Memuat pratinjau sinkronisasi...</span>
                </div>

                <div class="nota-sync-table-wrap custom-scrollbar">
                    <table class="table nota-sync-table align-middle">
                        <thead>
                            <tr>
                                <th style="width: 44px;" class="text-center">No</th>
                                <th>Kode Order</th>
                                <th class="text-end">Nominal Lama</th>
                                <th class="text-end">Nominal Baru</th>
                                <th class="text-end">Selisih</th>
                                <th class="text-end">Terbayar</th>
                                <th class="text-end">Sisa Baru</th>
                            </tr>
                        </thead>
                        <tbody id="nota-sync-body">
                            <tr>
                                <td colspan="7" class="nota-sync-empty">Memuat data order...</td>
                            </tr>
                        </tbody>
                        <tfoot id="nota-sync-foot" class="d-none">
                            <tr>
                                <td colspan="2" class="text-end">Total</td>
                                <td class="nota-sync-money" id="nota-sync-foot-old">Rp 0</td>
                                <td class="nota-sync-money" id="nota-sync-foot-new">Rp 0</td>
                                <td class="nota-sync-delta" id="nota-sync-foot-diff">Rp 0</td>
                                <td class="nota-sync-money" id="nota-sync-foot-paid">Rp 0</td>
                                <td class="nota-sync-money" id="nota-sync-foot-remaining">Rp 0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <p class="nota-sync-note" id="nota-sync-note"></p>
            </div>

            <div class="modal-footer py-2 px-4 border-top d-flex justify-content-between">
                <span class="text-muted fs-12 me-auto" id="nota-sync-hint">Nomor nota dan tarif pajak tidak berubah.</span>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"
                    style="border-radius: 8px; padding: 6px 16px;">Batal</button>
                <button type="button" class="btn btn-primary btn-sm d-flex align-items-center gap-1"
                    id="nota-sync-confirm" style="border-radius: 8px; padding: 6px 16px; font-weight: 600;">
                    <i class="mdi mdi-sync"></i> Ya, Sinkronkan
                </button>
            </div>
        </div>
    </div>
</div>
