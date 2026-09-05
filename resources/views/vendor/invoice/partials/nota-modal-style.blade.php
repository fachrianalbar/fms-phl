{{-- Styling modal Generate Nota (input manual PPN & PPh).
    Di-include dari vendor/invoice/partials/modals.blade.php. --}}

<style>
    /* ===== Modal Generate Nota ===== */
    .nota-modal-content {
        border: none;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.25);
    }

    .nota-modal-header {
        background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 55%, #1d4ed8 100%);
        color: #ffffff;
        padding: 20px 24px;
    }

    .nota-modal-header .modal-title {
        color: #ffffff;
        font-size: 18px;
        letter-spacing: -0.01em;
    }

    .nota-modal-header-sub {
        font-size: 12px;
        color: rgba(255, 255, 255, 0.85);
        margin-top: 2px;
    }

    .nota-modal-header-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.18);
        border: 1px solid rgba(255, 255, 255, 0.35);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 27px;
        flex-shrink: 0;
    }

    /* Sections */
    .nota-modal-section {
        padding: 18px 24px;
        border-bottom: 1px dashed #e2e8f0;
    }

    .nota-modal-section:last-of-type {
        border-bottom: none;
    }

    .nota-modal-section-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 12.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #334155;
        margin-bottom: 14px;
    }

    .nota-modal-section-badge {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .nota-tax-hint {
        font-size: 11px;
        font-weight: 600;
        text-transform: none;
        letter-spacing: 0;
        color: #16a34a;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 999px;
        padding: 3px 10px;
    }

    /* Info tiles */
    .nota-info-tile {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 10px 12px;
        height: 100%;
    }

    .nota-info-tile-label {
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        margin-bottom: 3px;
        white-space: nowrap;
    }

    .nota-info-tile-value {
        font-size: 15px;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.25;
    }

    /* Order codes chip box */
    .nota-order-codes {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px;
        max-height: 108px;
        overflow-y: auto;
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        font-size: 12px;
        color: #475569;
    }

    .nota-order-codes .nota-order-chip {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 999px;
        padding: 2px 10px;
        font-weight: 600;
        font-family: inherit;
        color: #334155;
        white-space: nowrap;
    }

    /* Calculation rows */
    .nota-calc-row {
        padding: 10px 0;
        border-bottom: 1px dashed #e2e8f0;
    }

    .nota-calc-row:last-of-type {
        border-bottom: none;
    }

    .nota-calc-label {
        font-size: 13px;
        font-weight: 600;
        color: #334155;
    }

    .nota-calc-label small {
        font-size: 11px;
        font-weight: 500;
    }

    .nota-calc-value {
        font-size: 16px;
        color: #0f172a;
        font-variant-numeric: tabular-nums;
    }

    .nota-field-label {
        font-size: 13px;
        font-weight: 600;
        color: #334155;
    }

    /* Tax inputs */
    .nota-tax-input-group .input-group-text {
        background: #f1f5f9;
        border-color: #cbd5e1;
        color: #475569;
        font-weight: 700;
        font-size: 13px;
    }

    .nota-tax-input {
        border-color: #cbd5e1;
        font-weight: 700;
        font-size: 15px;
        color: #0f172a;
        text-align: right;
        font-variant-numeric: tabular-nums;
    }

    .nota-tax-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
    }

    .nota-tax-preview {
        min-width: 120px;
        text-align: right;
        background: #eff6ff !important;
        border-color: #bfdbfe !important;
        color: #1d4ed8 !important;
        font-variant-numeric: tabular-nums;
        font-size: 13px !important;
    }

    /* Grand total */
    .nota-grand-total {
        margin-top: 14px;
        background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%);
        border: 1px solid #bbf7d0;
        border-radius: 14px;
        padding: 14px 18px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
    }

    .nota-grand-total-label {
        font-size: 14px;
        font-weight: 800;
        color: #166534;
        letter-spacing: 0.03em;
    }

    .nota-grand-total-label small {
        font-size: 11px;
        color: #15803d;
        letter-spacing: 0;
    }

    .nota-grand-total-value {
        font-size: 22px;
        font-weight: 800;
        color: #166534;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    /* Total bayar minus (validasi gagal) */
    .nota-grand-total.nota-grand-total-negative {
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        border-color: #fecaca;
    }

    .nota-grand-total-negative .nota-grand-total-label,
    .nota-grand-total-negative .nota-grand-total-value {
        color: #b91c1c;
    }

    .nota-grand-total-negative .nota-grand-total-label small {
        color: #dc2626;
    }

    /* Footer */
    .nota-modal-footer {
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        padding: 14px 24px;
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .nota-modal-footer-info {
        font-size: 11.5px;
        color: #94a3b8;
    }

    @media (max-width: 575.98px) {
        .nota-modal-section {
            padding: 14px 16px;
        }

        .nota-modal-header {
            padding: 16px;
        }

        .nota-grand-total-value {
            font-size: 18px;
        }

        .nota-modal-footer {
            padding: 12px 16px;
        }
    }
</style>
