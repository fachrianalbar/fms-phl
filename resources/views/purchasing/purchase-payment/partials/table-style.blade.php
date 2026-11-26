{{-- Gaya bersama halaman Pembayaran Hutang Supplier (KPI card, filter pill,
    container tabel, tabel DataTables, dan bar perintah pilihan).
    Di-include di dalam @push('style') oleh index.blade.php & paid.blade.php. --}}

<style>
    /* Executive Metric KPI Cards */
    .stat-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 16px 18px;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        position: relative;
        overflow: hidden;
        height: 100%;
        cursor: pointer;
        user-select: none;
    }
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.06);
        border-color: #cbd5e1;
    }
    .stat-card .stat-icon-wrapper {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }
    .stat-card .stat-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #64748b;
        margin-bottom: 4px;
    }
    .stat-card .stat-value {
        font-size: 22px;
        font-weight: 700;
        letter-spacing: -0.02em;
        line-height: 1.2;
        color: #0f172a;
    }
    .stat-card .stat-desc {
        font-size: 11.5px;
        color: #64748b;
        margin-top: 6px;
    }

    /* Main Table Container Card */
    .table-container-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        overflow: hidden;
    }
    .table-top-bar {
        padding: 14px 20px;
        background: #ffffff;
        border-bottom: 1px solid #f1f5f9;
    }

    /* Filter Navigation Pills */
    .filter-pill-btn {
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        color: #64748b;
        font-size: 12.5px;
        font-weight: 600;
        padding: 6px 14px;
        border-radius: 30px;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
    }
    .filter-pill-btn:hover {
        background: #e2e8f0;
        color: #1e293b;
        border-color: #cbd5e1;
    }
    .filter-pill-btn.active {
        background: #2563eb;
        color: #ffffff;
        border-color: #2563eb;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
    }
    .filter-pill-btn.active .badge-pill-count {
        background: rgba(255, 255, 255, 0.25);
        color: #ffffff;
    }
    .badge-pill-count {
        background: #e2e8f0;
        color: #475569;
        font-size: 11px;
        padding: 2px 7px;
        border-radius: 20px;
        font-weight: 700;
    }

    /* Tabel hutang supplier */
    .purchase-payment-table {
        width: 100% !important;
        margin-bottom: 0 !important;
        border-collapse: collapse !important;
    }
    .purchase-payment-table thead th {
        background: #f8fafc !important;
        color: #475569 !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.04em !important;
        border-top: none !important;
        border-bottom: 2px solid #e2e8f0 !important;
        padding: 12px 14px !important;
        white-space: nowrap !important;
        vertical-align: middle !important;
    }
    .purchase-payment-table tbody td {
        padding: 10px 14px !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f1f5f9 !important;
        font-size: 12px;
        color: #334155;
        white-space: nowrap !important;
    }
    .purchase-payment-table tbody tr {
        transition: background-color 0.15s ease-in-out;
    }
    .purchase-payment-table tbody tr:hover {
        background-color: #f8fafc !important;
    }
    .purchase-payment-table tbody tr.table-active {
        background-color: #eff6ff !important;
    }

    /* Bar perintah pilihan (sticky) */
    .selection-command-bar {
        position: sticky;
        top: 74px;
        z-index: 1010;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        margin: 0 20px 18px;
        padding: 13px 16px;
        border-radius: 12px;
        background: #1e293b;
        color: #fff;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.25);
    }
    .selection-command-bar .selection-facts {
        display: flex;
        flex-wrap: wrap;
        gap: 8px 18px;
        font-size: 12px;
    }
    .selection-command-bar .selection-facts strong { font-size: 14px; }
    .selection-command-bar .selection-facts span { color: #94a3b8; }
    .selection-command-bar .selection-actions { display: flex; flex-wrap: wrap; gap: 8px; }
    .selection-command-bar .btn-light { color: #1e293b; }

    /* Scrollbar halus */
    .custom-scrollbar::-webkit-scrollbar { height: 8px; width: 8px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 8px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

    /* DataTables */
    .dataTables_wrapper .dataTables_filter input {
        border-radius: 20px !important;
        padding: 6px 16px !important;
        border: 1px solid #cbd5e1 !important;
        outline: none !important;
        font-size: 13px !important;
        background-color: #f8fafc !important;
        min-width: 240px;
    }
    .dataTables_wrapper .dataTables_filter input:focus {
        background-color: #ffffff !important;
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15) !important;
    }
    .dataTables_wrapper .dataTables_length select {
        border-radius: 8px !important;
        padding: 5px 28px 5px 10px !important;
        border: 1px solid #cbd5e1 !important;
        font-size: 13px !important;
    }
    .page-item.active .page-link {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important;
        border-color: transparent !important;
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3) !important;
        border-radius: 8px !important;
    }
    .page-link {
        border-radius: 8px !important;
        margin: 0 2px !important;
        border: 1px solid #e2e8f0 !important;
        color: #475569 !important;
        font-size: 12.5px;
    }

    /* Modal pembayaran batch */
    .payment-allocation-table th {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
        background: #f8fafc;
        white-space: nowrap;
    }
    .payment-allocation-table td { vertical-align: middle; font-size: 12.5px; }
    .payment-allocation-table .allocation-vendor {
        display: block;
        font-size: 11px;
        color: #94a3b8;
        margin-top: 2px;
    }
    .payment-allocation-table .allocation-amount-input { min-width: 130px; text-align: right; }
    .payment-allocation-table .allocation-message { display: block; font-size: 10.5px; margin-top: 2px; }
    .payment-money { font-variant-numeric: tabular-nums; }
    .payment-mode-switch .btn-check:checked + label {
        background: #2563eb;
        color: #fff;
        border-color: #2563eb;
    }
    .payment-mode-switch label {
        cursor: pointer;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 6px 12px;
        font-size: 12.5px;
        font-weight: 600;
        color: #475569;
        background: #f8fafc;
    }
    .payment-total-block strong { font-size: 20px; }
    .payment-facts dt { font-size: 11px; color: #94a3b8; font-weight: 600; text-transform: uppercase; }
    .payment-facts dd { font-size: 13px; font-weight: 700; margin-bottom: 0; color: #334155; }

    /* Detail modal */
    .detail-amount-tile {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 14px;
        height: 100%;
    }
    .detail-tile-label {
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        margin-bottom: 4px;
    }
    .detail-tile-value { font-size: 17px; font-weight: 700; color: #0f172a; }
    .detail-amount-tile[data-role='remaining'] { background: #fff7ed; border-color: #fed7aa; }
    .detail-amount-tile[data-role='paid'] { background: #f0fdf4; border-color: #bbf7d0; }

    @media (max-width: 575.98px) {
        .selection-command-bar { top: 8px; align-items: stretch; flex-direction: column; margin: 0 12px 14px; }
        .selection-command-bar .selection-actions { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .selection-command-bar .selection-actions .btn:last-child { grid-column: 1 / -1; }
    }

    /* ── Dark mode ─────────────────────────────── */
    html[data-bs-theme="dark"] .stat-card {
        background-color: var(--bs-card-bg);
        border-color: var(--bs-border-color);
    }
    html[data-bs-theme="dark"] .stat-card:hover {
        border-color: var(--bs-border-color);
    }
    html[data-bs-theme="dark"] .stat-card .stat-label,
    html[data-bs-theme="dark"] .stat-card .stat-desc {
        color: var(--bs-secondary-color);
    }
    html[data-bs-theme="dark"] .stat-card .stat-value {
        color: var(--bs-heading-color);
    }
    html[data-bs-theme="dark"] .table-container-card {
        background-color: var(--bs-card-bg);
        border-color: var(--bs-border-color);
    }
    html[data-bs-theme="dark"] .table-top-bar {
        background-color: var(--bs-card-bg);
        border-bottom-color: var(--bs-border-color);
    }
    html[data-bs-theme="dark"] .filter-pill-btn:not(.active) {
        background: var(--bs-tertiary-bg);
        border-color: var(--bs-border-color);
        color: var(--bs-secondary-color);
    }
    html[data-bs-theme="dark"] .filter-pill-btn:not(.active):hover {
        background: var(--bs-secondary-bg);
        border-color: var(--bs-border-color);
        color: var(--bs-body-color);
    }
    html[data-bs-theme="dark"] .filter-pill-btn:not(.active) .badge-pill-count {
        background: var(--bs-border-color);
        color: var(--bs-body-color);
    }
    html[data-bs-theme="dark"] .purchase-payment-table thead th {
        background: var(--bs-tertiary-bg) !important;
        color: var(--bs-body-color) !important;
        border-bottom-color: var(--bs-border-color) !important;
    }
    html[data-bs-theme="dark"] .purchase-payment-table tbody td {
        color: var(--bs-body-color);
        border-bottom-color: var(--bs-border-color) !important;
    }
    html[data-bs-theme="dark"] .purchase-payment-table tbody tr:hover {
        background-color: rgba(255, 255, 255, 0.04) !important;
    }
    html[data-bs-theme="dark"] .purchase-payment-table tbody tr.table-active {
        background-color: rgba(59, 130, 246, 0.18) !important;
    }
    html[data-bs-theme="dark"] .custom-scrollbar::-webkit-scrollbar-track {
        background: var(--bs-body-bg);
    }
    html[data-bs-theme="dark"] .custom-scrollbar::-webkit-scrollbar-thumb {
        background: var(--bs-border-color);
    }
    html[data-bs-theme="dark"] .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: var(--bs-secondary-color);
    }
    html[data-bs-theme="dark"] .dataTables_wrapper .dataTables_filter input {
        background-color: var(--bs-tertiary-bg) !important;
        border-color: var(--bs-border-color) !important;
        color: var(--bs-body-color) !important;
    }
    html[data-bs-theme="dark"] .dataTables_wrapper .dataTables_filter input:focus {
        background-color: var(--bs-card-bg) !important;
        border-color: #8f86eb !important;
    }
    html[data-bs-theme="dark"] .dataTables_wrapper .dataTables_length select {
        background-color: var(--bs-tertiary-bg) !important;
        border-color: var(--bs-border-color) !important;
        color: var(--bs-body-color) !important;
    }
    html[data-bs-theme="dark"] .page-link {
        background-color: var(--bs-card-bg) !important;
        border-color: var(--bs-border-color) !important;
        color: var(--bs-body-color) !important;
    }
    /* Modal pembayaran batch */
    html[data-bs-theme="dark"] .payment-allocation-table th {
        color: var(--bs-secondary-color);
        background: var(--bs-tertiary-bg);
    }
    html[data-bs-theme="dark"] .payment-allocation-table .allocation-vendor {
        color: var(--bs-secondary-color);
    }
    html[data-bs-theme="dark"] .payment-mode-switch label {
        color: var(--bs-body-color);
        background: var(--bs-tertiary-bg);
        border-color: var(--bs-border-color);
    }
    html[data-bs-theme="dark"] .payment-facts dd {
        color: var(--bs-body-color);
    }
    /* Detail modal tiles */
    html[data-bs-theme="dark"] .detail-amount-tile {
        border-color: var(--bs-border-color);
    }
    html[data-bs-theme="dark"] .detail-tile-label {
        color: var(--bs-secondary-color);
    }
    html[data-bs-theme="dark"] .detail-tile-value {
        color: var(--bs-heading-color);
    }
    html[data-bs-theme="dark"] .detail-amount-tile[data-role='remaining'] {
        background: rgba(245, 158, 11, 0.16);
        border-color: rgba(245, 158, 11, 0.4);
    }
    html[data-bs-theme="dark"] .detail-amount-tile[data-role='paid'] {
        background: rgba(34, 197, 94, 0.16);
        border-color: rgba(34, 197, 94, 0.4);
    }

    /* ── Standar Filter Card Collapse (§3 PHL) ── */
    .filter-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        margin: 0 0 20px;
        overflow: hidden;
    }
    .filter-card-header {
        align-items: center;
        background: #f8fafc;
        border: 0;
        color: #334155;
        display: flex;
        justify-content: space-between;
        padding: 12px 16px;
        text-align: left;
        width: 100%;
    }
    .filter-card-header:hover { background: #f1f5f9; }
    .filter-card-heading { align-items: center; display: flex; gap: 8px; flex-wrap: wrap; }
    .filter-card-heading i { color: #4f46e5; font-size: 17px; }
    .filter-card-heading strong { font-size: 13px; font-weight: 700; }
    .filter-card-heading small { color: #94a3b8; font-size: 11px; font-weight: 400; }
    .filter-card-chevron { transition: transform .2s ease; }
    .filter-card-header[aria-expanded="true"] .filter-card-chevron { transform: rotate(180deg); }
    .filter-card .filter-collapse { border-top: 1px solid #e2e8f0; }
    .filter-card .filter-collapse-body { padding: 16px; }
    .filter-card .row { --bs-gutter-y: .75rem; }

    .filter-label {
        color: #64748b;
        display: block;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 6px;
    }
    .filter-control,
    .filter-card .form-control {
        background-color: #fff !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px !important;
        color: #334155 !important;
        font-size: 13px !important;
        height: 38px !important;
        transition: all 0.2s ease !important;
    }
    .filter-control {
        padding: 0 12px 0 36px !important;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='4' width='18' height='18' rx='2' ry='2'%3E%3C/rect%3E%3Cline x1='16' y1='2' x2='16' y2='6'%3E%3C/line%3E%3Cline x1='8' y1='2' x2='8' y2='6'%3E%3C/line%3E%3Cline x1='3' y1='10' x2='21' y2='10'%3E%3C/line%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: 12px center;
        background-size: 15px 15px;
    }
    .filter-card .form-control:not(.filter-control) {
        padding: 0 12px !important;
    }
    .filter-control::placeholder,
    .filter-card .form-control::placeholder {
        color: #94a3b8 !important;
        font-size: 13px !important;
    }
    .filter-control:focus,
    .filter-card .form-control:focus {
        border-color: #818cf8 !important;
        box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.15) !important;
    }

    .btn-filter-primary {
        align-items: center;
        background: linear-gradient(135deg, #4f46e5, #6366f1) !important;
        border: 0 !important;
        border-radius: 8px !important;
        color: #fff !important;
        display: inline-flex;
        font-size: 13px;
        font-weight: 600;
        gap: 6px;
        height: 38px;
        justify-content: center;
        padding: 0 16px;
        white-space: nowrap;
        box-shadow: 0 2px 6px rgba(79, 70, 229, 0.25);
        transition: all 0.2s ease;
    }
    .btn-filter-primary:hover {
        background: linear-gradient(135deg, #4338ca, #4f46e5) !important;
        color: #fff !important;
        transform: translateY(-1px);
    }
    .btn-filter-reset {
        align-items: center;
        background: #fff !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px !important;
        color: #64748b !important;
        display: inline-flex;
        height: 38px;
        width: 38px;
        min-width: 38px;
        justify-content: center;
        padding: 0 !important;
        transition: all 0.2s ease;
    }
    .btn-filter-reset:hover {
        background: #fff1f2 !important;
        color: #e11d48 !important;
        border-color: #fecdd3 !important;
        transform: translateY(-1px);
    }

    .filter-card .select2-container { width: 100% !important; }
    .filter-card .select2-container--default .select2-selection--single {
        align-items: center;
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px !important;
        display: flex;
        height: 38px !important;
        background-color: #ffffff !important;
    }
    .filter-card .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #334155 !important;
        font-size: 13px;
        line-height: 36px;
        padding-left: 12px;
        padding-right: 28px;
    }
    .filter-card .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
        right: 8px;
        top: 1px;
    }
    .filter-card .select2-container--default .select2-selection--single .select2-selection__clear {
        margin-right: 4px;
    }

    html[data-bs-theme="dark"] .filter-card {
        background: var(--bs-secondary-bg);
        border-color: var(--bs-border-color);
        color-scheme: dark;
    }
    html[data-bs-theme="dark"] .filter-card-header {
        background: var(--bs-secondary-bg);
        color: var(--bs-body-color);
    }
    html[data-bs-theme="dark"] .filter-card-header:hover {
        background: var(--bs-tertiary-bg);
    }
    html[data-bs-theme="dark"] .filter-card .filter-collapse {
        border-top-color: var(--bs-border-color);
    }
    html[data-bs-theme="dark"] .filter-label {
        color: var(--bs-secondary-color);
    }
    html[data-bs-theme="dark"] .filter-control,
    html[data-bs-theme="dark"] .filter-card .form-control {
        background-color: var(--bs-tertiary-bg) !important;
        border-color: var(--bs-border-color) !important;
        color: var(--bs-body-color) !important;
    }
    html[data-bs-theme="dark"] .filter-control:focus,
    html[data-bs-theme="dark"] .filter-card .form-control:focus {
        background-color: var(--bs-tertiary-bg) !important;
        border-color: #8f86eb !important;
    }
    html[data-bs-theme="dark"] .btn-filter-reset {
        background: var(--bs-tertiary-bg) !important;
        border-color: var(--bs-border-color) !important;
        color: var(--bs-body-color) !important;
    }
</style>
