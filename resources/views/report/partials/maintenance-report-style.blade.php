{{-- Gaya bersama laporan Perawatan (Per Armada & Per Suplier). Standar: skill phl-table-design. --}}
<link rel="stylesheet" type="text/css" href="{{ asset('assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('assets/libs/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/select2.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom-select2.css') }}">

<style>
    /* ── Table ── */
    .maintenance-report-table {
        border-collapse: separate;
        border-spacing: 0;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }

    .maintenance-report-table thead th {
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

    .maintenance-report-table tbody td {
        padding: 11px 12px;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        font-size: 12.5px;
        white-space: nowrap;
        vertical-align: middle;
    }

    .maintenance-report-table tbody tr {
        transition: background-color 0.15s ease;
    }

    .maintenance-report-table tbody tr:hover {
        background-color: #f8fafc !important;
    }

    .maintenance-report-table td.text-end,
    .maintenance-report-table th.text-end {
        font-variant-numeric: tabular-nums;
    }

    .dataTables_wrapper .dataTables_filter input,
    .dataTables_wrapper .dataTables_length select {
        border-radius: 8px;
        font-size: 12px;
    }

    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        color: #64748b;
        font-size: 12px;
    }

    .btn-icon {
        border-radius: 8px !important;
        padding: 6px 10px;
        font-size: 13px;
        transition: all 0.2s ease;
    }

    .btn-icon:hover {
        transform: translateY(-1px);
    }

    /* ── Filter card ── */
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

    .filter-control {
        background-color: #fff !important;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='4' width='18' height='18' rx='2' ry='2'%3E%3C/rect%3E%3Cline x1='16' y1='2' x2='16' y2='6'%3E%3C/line%3E%3Cline x1='8' y1='2' x2='8' y2='6'%3E%3C/line%3E%3Cline x1='3' y1='10' x2='21' y2='10'%3E%3C/line%3E%3C/svg%3E");
        background-position: 12px center;
        background-repeat: no-repeat;
        background-size: 15px 15px;
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px !important;
        color: #334155 !important;
        font-size: 13px !important;
        height: 38px !important;
        padding: 0 12px 0 36px !important;
    }

    .filter-control:focus {
        border-color: #818cf8 !important;
        box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.15) !important;
    }

    .btn-filter-primary {
        align-items: center;
        background: linear-gradient(135deg, #4f46e5, #6366f1) !important;
        border: 0 !important;
        border-radius: 8px !important;
        box-shadow: 0 2px 6px rgba(79, 70, 229, 0.25);
        color: #fff !important;
        display: inline-flex;
        font-size: 13px;
        font-weight: 600;
        gap: 6px;
        height: 38px;
        justify-content: center;
        padding: 0 16px;
        white-space: nowrap;
    }

    .btn-filter-primary:hover {
        background: linear-gradient(135deg, #4338ca, #4f46e5) !important;
    }

    .btn-filter-reset {
        align-items: center;
        background: #fff !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px !important;
        color: #64748b !important;
        display: inline-flex;
        height: 38px;
        justify-content: center;
        min-width: 38px;
        padding: 0 !important;
        width: 38px;
    }

    .btn-filter-reset:hover {
        background: #fff1f2 !important;
        border-color: #fecdd3 !important;
        color: #e11d48 !important;
    }

    .select2-container {
        width: 100% !important;
    }

    .select2-container--default .select2-selection--single {
        align-items: center;
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px !important;
        display: flex;
        height: 38px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #334155 !important;
        font-size: 13px;
        line-height: 36px;
        padding-left: 12px;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }

    /* ── Entity banner & KPI ── */
    .entity-banner {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
    }

    .avatar-badge-entity {
        align-items: center;
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(79, 70, 229, 0.25);
        color: #ffffff;
        display: inline-flex;
        flex-shrink: 0;
        height: 44px;
        justify-content: center;
        width: 44px;
    }

    .summary-card {
        border: 1px solid transparent;
        border-radius: 12px;
        height: 100%;
        padding: 18px 20px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .summary-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        transform: translateY(-2px);
    }

    .summary-card h5 {
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
        opacity: 0.8;
        text-transform: uppercase;
    }

    .summary-card h3 {
        font-size: 20px;
        font-variant-numeric: tabular-nums;
        font-weight: 800;
        letter-spacing: -0.5px;
        margin: 0;
    }

    .summary-primary { background: linear-gradient(135deg, #eef2ff, #e0e7ff); border-color: #c7d2fe; color: #3730a3; }
    .summary-success { background: linear-gradient(135deg, #ecfdf5, #d1fae5); border-color: #a7f3d0; color: #065f46; }
    .summary-warning { background: linear-gradient(135deg, #fefce8, #fef9c3); border-color: #fde68a; color: #92400e; }
    .summary-danger { background: linear-gradient(135deg, #fff1f2, #ffe4e6); border-color: #fecdd3; color: #9f1239; }
    .summary-orange { background: linear-gradient(135deg, #fff7ed, #ffedd5); border-color: #fed7aa; color: #9a3412; }

    /* ── Dark mode ── */
    html[data-bs-theme="dark"] .maintenance-report-table { border-color: var(--bs-border-color) !important; }
    html[data-bs-theme="dark"] .maintenance-report-table thead th {
        background-color: var(--bs-tertiary-bg) !important;
        border-color: var(--bs-border-color) !important;
        color: var(--bs-body-color) !important;
    }
    html[data-bs-theme="dark"] .maintenance-report-table tbody td {
        border-color: var(--bs-border-color) !important;
        color: var(--bs-body-color) !important;
    }
    html[data-bs-theme="dark"] .maintenance-report-table tbody tr:hover { background-color: rgba(255, 255, 255, 0.05) !important; }
    html[data-bs-theme="dark"] .dataTables_wrapper .dataTables_info,
    html[data-bs-theme="dark"] .dataTables_wrapper .dataTables_length,
    html[data-bs-theme="dark"] .dataTables_wrapper .dataTables_filter { color: var(--bs-secondary-color); }

    html[data-bs-theme="dark"] .filter-card { background: var(--bs-secondary-bg); border-color: var(--bs-border-color); }
    html[data-bs-theme="dark"] .filter-card-header { background: var(--bs-secondary-bg); color: var(--bs-body-color); }
    html[data-bs-theme="dark"] .filter-card-header:hover { background: var(--bs-tertiary-bg); }
    html[data-bs-theme="dark"] .filter-card .filter-collapse { border-top-color: var(--bs-border-color); }
    html[data-bs-theme="dark"] .filter-label { color: var(--bs-secondary-color); }
    html[data-bs-theme="dark"] .filter-control,
    html[data-bs-theme="dark"] .filter-card .form-control {
        background-color: var(--bs-tertiary-bg) !important;
        border-color: var(--bs-border-color) !important;
        color: var(--bs-body-color) !important;
    }
    html[data-bs-theme="dark"] .btn-filter-reset {
        background: var(--bs-tertiary-bg) !important;
        border-color: var(--bs-border-color) !important;
        color: var(--bs-body-color) !important;
    }

    html[data-bs-theme="dark"] .entity-banner { background: var(--bs-card-bg); border-color: var(--bs-border-color); }
    html[data-bs-theme="dark"] .entity-banner .text-dark { color: var(--bs-body-color) !important; }
    html[data-bs-theme="dark"] .entity-banner .bg-light { background-color: var(--bs-tertiary-bg) !important; }

    html[data-bs-theme="dark"] .summary-primary { background: linear-gradient(135deg, rgba(79, 70, 229, 0.22), rgba(99, 102, 241, 0.12)); border-color: rgba(129, 140, 248, 0.35); color: #a5b4fc; }
    html[data-bs-theme="dark"] .summary-success { background: linear-gradient(135deg, rgba(16, 185, 129, 0.22), rgba(5, 150, 105, 0.12)); border-color: rgba(52, 211, 153, 0.35); color: #6ee7b7; }
    html[data-bs-theme="dark"] .summary-warning { background: linear-gradient(135deg, rgba(245, 158, 11, 0.22), rgba(217, 119, 6, 0.12)); border-color: rgba(251, 191, 36, 0.35); color: #fde68a; }
    html[data-bs-theme="dark"] .summary-danger { background: linear-gradient(135deg, rgba(244, 63, 94, 0.22), rgba(225, 29, 72, 0.12)); border-color: rgba(251, 113, 133, 0.35); color: #fda4af; }
    html[data-bs-theme="dark"] .summary-orange { background: linear-gradient(135deg, rgba(249, 115, 22, 0.22), rgba(234, 88, 12, 0.12)); border-color: rgba(251, 146, 60, 0.35); color: #fdba74; }
</style>
