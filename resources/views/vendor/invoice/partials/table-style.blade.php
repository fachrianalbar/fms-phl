<style>
    /* Executive Metric KPI Cards */
    .stat-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 18px 20px;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        position: relative;
        overflow: hidden;
        height: 100%;
    }
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.06);
        border-color: #cbd5e1;
    }
    .stat-card .stat-icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }
    .stat-card .stat-label {
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #64748b;
        margin-bottom: 4px;
    }
    .stat-card .stat-value {
        font-size: 20px;
        font-weight: 700;
        letter-spacing: -0.02em;
        line-height: 1.2;
        color: #0f172a;
    }
    .stat-card .stat-desc {
        font-size: 11.5px;
        color: #94a3b8;
        margin-top: 5px;
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
        padding: 16px 20px;
        background: #ffffff;
        border-bottom: 1px solid #f1f5f9;
    }

    /* Standard Table Styling */
    .invoice-table {
        margin-bottom: 0 !important;
        border-collapse: separate !important;
        border-spacing: 0 !important;
    }
    .invoice-table thead th {
        background: #f8fafc !important;
        color: #475569 !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        border-top: none !important;
        border-bottom: 2px solid #e2e8f0 !important;
        padding: 14px 12px !important;
        white-space: nowrap !important;
        vertical-align: middle !important;
    }
    .invoice-table tbody td {
        padding: 13px 12px !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f1f5f9 !important;
        font-size: 13px;
        color: #334155;
    }
    .invoice-table tbody tr {
        transition: background-color 0.15s ease-in-out;
    }
    .invoice-table tbody tr:hover {
        background-color: rgba(59, 130, 246, 0.035) !important;
    }

    /* Custom DataTables Styling */
    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 12px;
    }
    .dataTables_wrapper .dataTables_filter input {
        border-radius: 20px !important;
        padding: 6px 16px !important;
        border: 1px solid #cbd5e1 !important;
        outline: none !important;
        font-size: 13px !important;
        background-color: #f8fafc !important;
        transition: all 0.2s ease;
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
        font-size: 12.5px !important;
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
    html[data-bs-theme="dark"] .invoice-table thead th {
        background: var(--bs-tertiary-bg) !important;
        color: var(--bs-body-color) !important;
        border-bottom-color: var(--bs-border-color) !important;
    }
    html[data-bs-theme="dark"] .invoice-table tbody td {
        color: var(--bs-body-color);
        border-bottom-color: var(--bs-border-color) !important;
    }
    html[data-bs-theme="dark"] .invoice-table tbody tr:hover {
        background-color: rgba(59, 130, 246, 0.12) !important;
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
</style>
