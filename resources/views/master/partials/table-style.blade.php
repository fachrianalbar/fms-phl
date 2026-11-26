{{-- Standar CSS Tabel & Filter Card Collapse (§3 & §6 PHL) --}}
<style>
    /* ── Scoped Table Styling (§6 PHL) ── */
    #dt {
        border-collapse: separate;
        border-spacing: 0;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }

    #dt thead th {
        background-color: #f8fafc;
        color: #475569;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 13px 12px;
        border-bottom: 2px solid #e2e8f0;
        border-top: none;
        white-space: nowrap;
        vertical-align: middle;
    }

    #dt tbody td {
        padding: 11px 12px;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        font-size: 12.5px;
        white-space: nowrap;
        vertical-align: middle;
    }

    #dt tbody tr {
        transition: background-color 0.15s ease;
    }

    #dt tbody tr:hover {
        background-color: #f8fafc !important;
    }

    /* ── Tombol Icon Ramping ── */
    .btn-icon {
        border-radius: 8px !important;
        padding: 6px 10px;
        font-size: 13px;
        transition: all 0.2s ease;
    }

    .btn-icon:hover {
        transform: translateY(-1px);
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

    .filter-card-header:hover {
        background: #f1f5f9;
    }

    .filter-card-heading {
        align-items: center;
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .filter-card-heading i {
        color: #4f46e5;
        font-size: 17px;
    }

    .filter-card-heading strong {
        font-size: 13px;
        font-weight: 700;
    }

    .filter-card-heading small {
        color: #94a3b8;
        font-size: 11px;
        font-weight: 400;
    }

    .filter-card-chevron {
        transition: transform .2s ease;
    }

    .filter-card-header[aria-expanded="true"] .filter-card-chevron {
        transform: rotate(180deg);
    }

    .filter-card .filter-collapse {
        border-top: 1px solid #e2e8f0;
    }

    .filter-card .filter-collapse-body {
        padding: 16px;
    }

    .filter-card .row {
        --bs-gutter-y: .75rem;
    }

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

    .filter-card .select2-container {
        width: 100% !important;
    }

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

    /* ── DataTables Inputs ── */
    #dt_wrapper .dataTables_filter input,
    #dt_wrapper .dataTables_length select {
        border-radius: 8px;
        font-size: 12px;
    }

    #dt_wrapper .dataTables_info,
    #dt_wrapper .dataTables_length,
    #dt_wrapper .dataTables_filter {
        color: #64748b;
        font-size: 12px;
    }

    /* ── Dark mode Filter Card & Table (WAJIB) ── */
    html[data-bs-theme="dark"] .card-header.bg-white {
        background-color: var(--bs-card-bg) !important;
        border-color: var(--bs-border-color) !important;
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

    html[data-bs-theme="dark"] #dt {
        border-color: var(--bs-border-color) !important;
    }

    html[data-bs-theme="dark"] #dt thead th {
        background-color: var(--bs-tertiary-bg) !important;
        color: var(--bs-body-color) !important;
        border-color: var(--bs-border-color) !important;
    }

    html[data-bs-theme="dark"] #dt tbody td {
        color: var(--bs-body-color) !important;
        border-color: var(--bs-border-color) !important;
    }

    html[data-bs-theme="dark"] #dt tbody tr:hover {
        background-color: rgba(255, 255, 255, 0.05) !important;
    }

    html[data-bs-theme="dark"] #dt_wrapper .dataTables_info,
    html[data-bs-theme="dark"] #dt_wrapper .dataTables_length,
    html[data-bs-theme="dark"] #dt_wrapper .dataTables_filter {
        color: var(--bs-secondary-color);
    }
</style>
