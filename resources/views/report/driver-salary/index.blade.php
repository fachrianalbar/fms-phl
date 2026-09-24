@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Report',
    'secondSegment' => $title,
])

@push('style')
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/libs/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/libs/datatables.net-keytable-bs5/css/keyTable.bootstrap5.min.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/libs/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/libs/datatables.net-select-bs5/css/select.bootstrap5.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/sweetalert2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom-select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">

    <style>
        /* ── Main table styling ── */
        .salary-amount { text-align: right; font-weight: 500; }

        /* ── Standar Filter Card Collapse (PHL §3) ── */
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

        .filter-control,
        .filter-card .form-control {
            background-color: #fff !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            color: #334155 !important;
            font-size: 13px !important;
            height: 38px !important;
        }

        .filter-control {
            padding: 0 12px 0 36px !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='4' width='18' height='18' rx='2' ry='2'%3E%3C/rect%3E%3Cline x1='16' y1='2' x2='16' y2='6'%3E%3C/line%3E%3Cline x1='8' y1='2' x2='8' y2='6'%3E%3C/line%3E%3Cline x1='3' y1='10' x2='21' y2='10'%3E%3C/line%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: 12px center;
            background-size: 15px 15px;
            transition: all 0.2s ease !important;
        }

        .filter-control::placeholder {
            color: #94a3b8 !important;
            font-size: 13px !important;
        }

        .filter-control:hover {
            border-color: #94a3b8 !important;
        }

        .filter-control:focus {
            border-color: #818cf8 !important;
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.15) !important;
            background-color: #ffffff !important;
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
        }

        /* ── Modal premium styling ── */
        #processSalaryModal .modal-content, #editSalaryModal .modal-content {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0,0,0,.15);
        }
        #processSalaryModal .modal-header, #editSalaryModal .modal-header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #a855f7 100%);
            border-bottom: none;
            padding: 20px 24px;
            position: relative;
        }
        #processSalaryModal .modal-header::after, #editSalaryModal .modal-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #fbbf24, #f59e0b, #fbbf24);
        }
        #processSalaryModal .modal-title, #editSalaryModal .modal-title {
            font-weight: 700;
            font-size: 18px;
            letter-spacing: -0.3px;
        }
        #processSalaryModal .modal-body, #editSalaryModal .modal-body {
            padding: 24px;
            background: #f8fafc;
            max-height: calc(100vh - 210px);
            overflow-y: auto !important;
        }

        /* ── Custom elegant scrollbar for modal body ── */
        #processSalaryModal .modal-body::-webkit-scrollbar, #editSalaryModal .modal-body::-webkit-scrollbar {
            width: 8px;
        }
        #processSalaryModal .modal-body::-webkit-scrollbar-track, #editSalaryModal .modal-body::-webkit-scrollbar-track {
            background: #f8fafc;
        }
        #processSalaryModal .modal-body::-webkit-scrollbar-thumb, #editSalaryModal .modal-body::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        #processSalaryModal .modal-body::-webkit-scrollbar-thumb:hover, #editSalaryModal .modal-body::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        #processSalaryModal .modal-footer, #editSalaryModal .modal-footer {
            background: #fff;
            border-top: 1px solid #e2e8f0;
            padding: 16px 24px;
        }

        /* ── Modal form sections ── */
        .modal-section {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 20px;
            margin-bottom: 16px;
        }
        .modal-section-title {
            font-size: 14px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .modal-section-title i {
            font-size: 18px;
            color: #4f46e5;
        }
        .modal-section-title .section-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: #fff;
            font-size: 12px;
            font-weight: 700;
        }

        /* ── Modal form controls styling ── */
        #processSalaryModal .modal-section .form-label,
        #editSalaryModal .modal-section .form-label {
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 6px;
        }
        #processSalaryModal .input-group .input-group-text,
        #editSalaryModal .input-group .input-group-text {
            background-color: #f8fafc;
            border-color: #cbd5e1;
            color: #64748b;
            height: 38px;
            padding: 0 12px;
            display: flex;
            align-items: center;
            border-radius: 8px 0 0 8px;
            transition: all 0.2s ease;
        }
        #processSalaryModal .input-group input.form-control,
        #editSalaryModal .input-group input.form-control {
            background-color: #fff;
            border-color: #cbd5e1;
            color: #334155;
            height: 38px;
            font-size: 14px;
            border-radius: 0 8px 8px 0;
            transition: all 0.2s ease;
        }
        #processSalaryModal textarea.form-control,
        #editSalaryModal textarea.form-control {
            background-color: #fff;
            border-color: #cbd5e1;
            color: #334155;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        /* ── Order preview table inside modal ── */
        #orderPreviewTable, #editOrderPreviewTable {
            font-size: 13px;
        }
        #orderPreviewTable thead th, #editOrderPreviewTable thead th {
            background: #f1f5f9;
            border-bottom: 2px solid #cbd5e1;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #475569;
            padding: 10px 12px;
        }
        #orderPreviewTable tbody tr, #editOrderPreviewTable tbody tr {
            transition: background-color 0.15s ease;
        }
        #orderPreviewTable tbody tr:hover, #editOrderPreviewTable tbody tr:hover {
            background-color: #eef2ff;
        }
        #orderPreviewTable .order-summary-row td, #editOrderPreviewTable .order-summary-row td {
            background: linear-gradient(135deg, #eef2ff, #e0e7ff);
            color: #3730a3;
        }

        /* ── Adjustment rows ── */
        .adjustment-row {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 14px 16px;
            margin-bottom: 10px;
            transition: all 0.2s ease;
        }
        .adjustment-row:hover {
            border-color: #a5b4fc;
            box-shadow: 0 2px 8px rgba(79, 70, 229, 0.08);
        }
        .adjustment-label {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 4px;
            display: block;
        }
        .btn-remove-adj, .btn-remove-edit-adj {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            border: 1px solid #fca5a5;
            color: #ef4444;
            transition: all 0.2s ease;
            background: transparent;
        }
        .btn-remove-adj:hover, .btn-remove-edit-adj:hover {
            background: #ef4444;
            color: #fff;
            border-color: #ef4444;
        }

        /* ── Summary cards ── */
        .summary-card {
            border-radius: 12px;
            padding: 18px 16px;
            text-align: center;
            border: 1px solid transparent;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .summary-card:hover {
            transform: translateY(-2px);
        }
        .summary-card h5 {
            font-size: 12px;
            margin-bottom: 6px;
            opacity: 0.85;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            color: inherit !important;
        }
        .summary-card h3 {
            font-size: 20px;
            font-weight: 800;
            margin: 0;
            letter-spacing: -0.5px;
            color: inherit !important;
        }
        .summary-salary { background: linear-gradient(135deg, #eef2ff, #e0e7ff); border-color: #c7d2fe; color: #3730a3; }
        .summary-adj    { background: linear-gradient(135deg, #ecfdf5, #d1fae5); border-color: #a7f3d0; color: #065f46; }
        .summary-grand  { background: linear-gradient(135deg, #fefce8, #fef9c3); border-color: #fde68a; color: #92400e; }

        /* ── Alerts inside modal ── */
        .modal-info-alert {
            background: #e0f2fe;
            color: #0369a1;
            border-radius: 10px;
            padding: 14px 16px;
        }

        /* ── No data state ── */
        .empty-state {
            padding: 40px 20px;
            text-align: center;
        }
        .empty-state i {
            font-size: 48px;
            color: #cbd5e1;
            margin-bottom: 12px;
            display: block;
        }
        .empty-state p {
            color: #94a3b8;
            font-size: 14px;
            margin: 0;
        }

        /* ── Process button ── */
        .btn-process {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border: none;
            color: #fff;
            font-weight: 600;
            padding: 8px 20px;
            border-radius: 8px;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
        }
        .btn-process:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(79, 70, 229, 0.35);
            color: #fff;
        }
        .btn-submit-salary {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            border: none;
            color: #fff;
            font-weight: 600;
            padding: 10px 28px;
            border-radius: 8px;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25);
        }
        .btn-submit-salary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(5, 150, 105, 0.35);
            color: #fff;
        }
        .btn-search-order {
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            border: none;
            color: #fff;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        .btn-search-order:hover {
            background: linear-gradient(135deg, #4338ca 0%, #4f46e5 100%);
            color: #fff;
        }
        .btn-add-adj {
            border: 1px dashed #a5b4fc;
            color: #4f46e5;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.2s ease;
            background: transparent;
        }
        .btn-add-adj:hover {
            background: #eef2ff;
            border-color: #4f46e5;
            color: #4f46e5;
        }

        /* ── Input Group and Focus styling ── */
        #processSalaryModal .input-group, #editSalaryModal .input-group {
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        #processSalaryModal .input-group:focus-within, #editSalaryModal .input-group:focus-within {
            box-shadow: 0 0 0 0.25rem rgba(79, 70, 229, 0.15) !important;
        }
        #processSalaryModal .input-group:focus-within .input-group-text,
        #editSalaryModal .input-group:focus-within .input-group-text,
        #processSalaryModal .input-group:focus-within input.form-control,
        #editSalaryModal .input-group:focus-within input.form-control,
        #processSalaryModal .input-group:focus-within .select2-container--default .select2-selection--single,
        #editSalaryModal .input-group:focus-within .select2-container--default .select2-selection--single {
            border-color: #818cf8 !important;
        }
        #processSalaryModal .input-group input.form-control:focus, #editSalaryModal .input-group input.form-control:focus {
            box-shadow: none !important;
        }

        /* ── Select2 inside input-group styling overrides ── */
        #processSalaryModal .input-group .select2-wrapper, #editSalaryModal .input-group .select2-wrapper {
            flex: 1 1 auto;
            width: 1%;
            display: flex;
        }
        #processSalaryModal .input-group .select2-wrapper .select2-container, #editSalaryModal .input-group .select2-wrapper .select2-container {
            width: 100% !important;
        }
        #processSalaryModal .input-group .select2-wrapper .select2-selection--single, #editSalaryModal .input-group .select2-wrapper .select2-selection--single {
            border-top-left-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
            border-radius: 0 8px 8px 0 !important;
            height: 38px !important;
            border: 1px solid #cbd5e1 !important;
            border-left: none !important;
            padding: 0 12px !important;
            display: flex !important;
            align-items: center !important;
            background-color: #fff !important;
            transition: border-color 0.2s ease, box-shadow 0.2s ease !important;
        }
        #processSalaryModal .input-group .select2-wrapper .select2-selection--single .select2-selection__rendered,
        #editSalaryModal .input-group .select2-wrapper .select2-selection--single .select2-selection__rendered {
            color: #334155 !important;
            padding: 0 !important;
            font-size: 14px !important;
            line-height: normal !important;
        }
        #processSalaryModal .input-group .select2-wrapper .select2-selection--single .select2-selection__placeholder,
        #editSalaryModal .input-group .select2-wrapper .select2-selection--single .select2-selection__placeholder {
            color: #94a3b8 !important;
        }

        /* ══════════════════════════════════════════════════════════════ */
        /* ── Dark Mode Styling for Process & Edit Salary Modals ─────── */
        /* ══════════════════════════════════════════════════════════════ */
        html[data-bs-theme="dark"] #processSalaryModal .modal-content,
        html[data-bs-theme="dark"] #editSalaryModal .modal-content {
            background-color: var(--bs-secondary-bg);
            border: 1px solid var(--bs-border-color);
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.5);
        }
        html[data-bs-theme="dark"] #processSalaryModal .modal-body,
        html[data-bs-theme="dark"] #editSalaryModal .modal-body {
            background-color: var(--bs-body-bg);
        }
        html[data-bs-theme="dark"] #processSalaryModal .modal-body::-webkit-scrollbar-track,
        html[data-bs-theme="dark"] #editSalaryModal .modal-body::-webkit-scrollbar-track {
            background: var(--bs-body-bg);
        }
        html[data-bs-theme="dark"] #processSalaryModal .modal-body::-webkit-scrollbar-thumb,
        html[data-bs-theme="dark"] #editSalaryModal .modal-body::-webkit-scrollbar-thumb {
            background: var(--bs-border-color);
        }
        html[data-bs-theme="dark"] #processSalaryModal .modal-body::-webkit-scrollbar-thumb:hover,
        html[data-bs-theme="dark"] #editSalaryModal .modal-body::-webkit-scrollbar-thumb:hover {
            background: var(--bs-secondary-color);
        }
        html[data-bs-theme="dark"] #processSalaryModal .modal-footer,
        html[data-bs-theme="dark"] #editSalaryModal .modal-footer {
            background-color: var(--bs-secondary-bg);
            border-top: 1px solid var(--bs-border-color);
        }
        html[data-bs-theme="dark"] #processSalaryModal .btn-light,
        html[data-bs-theme="dark"] #editSalaryModal .btn-light {
            background-color: var(--bs-tertiary-bg);
            border: 1px solid var(--bs-border-color);
            color: var(--bs-body-color);
        }
        html[data-bs-theme="dark"] #processSalaryModal .btn-light:hover,
        html[data-bs-theme="dark"] #editSalaryModal .btn-light:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--bs-emphasis-color);
        }

        /* Dark mode sections */
        html[data-bs-theme="dark"] #processSalaryModal .modal-section,
        html[data-bs-theme="dark"] #editSalaryModal .modal-section {
            background-color: var(--bs-secondary-bg);
            border-color: var(--bs-border-color);
        }
        html[data-bs-theme="dark"] #processSalaryModal .modal-section-title,
        html[data-bs-theme="dark"] #editSalaryModal .modal-section-title {
            color: var(--bs-emphasis-color);
        }
        html[data-bs-theme="dark"] #processSalaryModal .modal-section-title i,
        html[data-bs-theme="dark"] #editSalaryModal .modal-section-title i {
            color: #818cf8;
        }

        /* Dark mode form controls */
        html[data-bs-theme="dark"] #processSalaryModal .modal-section .form-label,
        html[data-bs-theme="dark"] #editSalaryModal .modal-section .form-label {
            color: var(--bs-body-color);
        }
        html[data-bs-theme="dark"] #processSalaryModal .input-group .input-group-text,
        html[data-bs-theme="dark"] #editSalaryModal .input-group .input-group-text {
            background-color: var(--bs-tertiary-bg) !important;
            border-color: var(--bs-border-color) !important;
            color: var(--bs-secondary-color) !important;
        }
        html[data-bs-theme="dark"] #processSalaryModal .input-group input.form-control,
        html[data-bs-theme="dark"] #editSalaryModal .input-group input.form-control {
            background-color: var(--bs-secondary-bg) !important;
            border-color: var(--bs-border-color) !important;
            color: var(--bs-body-color) !important;
        }
        html[data-bs-theme="dark"] #processSalaryModal textarea.form-control,
        html[data-bs-theme="dark"] #editSalaryModal textarea.form-control {
            background-color: var(--bs-secondary-bg) !important;
            border-color: var(--bs-border-color) !important;
            color: var(--bs-body-color) !important;
        }
        html[data-bs-theme="dark"] #processSalaryModal .form-control::placeholder,
        html[data-bs-theme="dark"] #editSalaryModal .form-control::placeholder {
            color: var(--bs-secondary-color) !important;
            opacity: 0.6;
        }

        /* Dark mode Select2 inside modal */
        html[data-bs-theme="dark"] #processSalaryModal .input-group .select2-wrapper .select2-selection--single,
        html[data-bs-theme="dark"] #editSalaryModal .input-group .select2-wrapper .select2-selection--single {
            background-color: var(--bs-secondary-bg) !important;
            border-color: var(--bs-border-color) !important;
        }
        html[data-bs-theme="dark"] #processSalaryModal .input-group .select2-wrapper .select2-selection--single .select2-selection__rendered,
        html[data-bs-theme="dark"] #editSalaryModal .input-group .select2-wrapper .select2-selection--single .select2-selection__rendered {
            color: var(--bs-body-color) !important;
        }
        html[data-bs-theme="dark"] #processSalaryModal .input-group .select2-wrapper .select2-selection--single .select2-selection__placeholder,
        html[data-bs-theme="dark"] #editSalaryModal .input-group .select2-wrapper .select2-selection--single .select2-selection__placeholder {
            color: var(--bs-secondary-color) !important;
            opacity: 0.6;
        }
        html[data-bs-theme="dark"] .select2-dropdown {
            background-color: var(--bs-secondary-bg) !important;
            border-color: var(--bs-border-color) !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5), 0 8px 10px -6px rgba(0, 0, 0, 0.5) !important;
        }
        html[data-bs-theme="dark"] .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: var(--bs-secondary-color) transparent transparent transparent !important;
        }
        html[data-bs-theme="dark"] .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
            border-color: transparent transparent var(--bs-secondary-color) transparent !important;
        }
        html[data-bs-theme="dark"] .select2-container--default .select2-search--dropdown {
            background-color: var(--bs-secondary-bg) !important;
        }
        html[data-bs-theme="dark"] .select2-container--default .select2-search--dropdown .select2-search__field {
            background-color: var(--bs-tertiary-bg) !important;
            border-color: var(--bs-border-color) !important;
            color: var(--bs-body-color) !important;
        }
        html[data-bs-theme="dark"] .select2-container--default .select2-results__option {
            color: var(--bs-body-color) !important;
        }
        html[data-bs-theme="dark"] .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #4f46e5 !important;
            color: #ffffff !important;
        }
        html[data-bs-theme="dark"] .select2-container--default .select2-results__option[aria-selected="true"] {
            background-color: rgba(79, 70, 229, 0.25) !important;
            color: #a5b4fc !important;
        }

        /* Dark mode adjustments section */
        html[data-bs-theme="dark"] .btn-add-adj {
            border-color: #6366f1;
            color: #a5b4fc;
        }
        html[data-bs-theme="dark"] .btn-add-adj:hover {
            background: rgba(99, 102, 241, 0.15);
            border-color: #818cf8;
            color: #c7d2fe;
        }
        html[data-bs-theme="dark"] .adjustment-row {
            background: var(--bs-tertiary-bg);
            border-color: var(--bs-border-color);
        }
        html[data-bs-theme="dark"] .adjustment-row:hover {
            border-color: #6366f1;
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.15);
        }
        html[data-bs-theme="dark"] .adjustment-label {
            color: var(--bs-secondary-color);
        }
        html[data-bs-theme="dark"] .adjustment-row .form-control,
        html[data-bs-theme="dark"] .adjustment-row .form-select {
            background-color: var(--bs-secondary-bg);
            border-color: var(--bs-border-color);
            color: var(--bs-body-color);
        }
        html[data-bs-theme="dark"] .adjustment-row .form-control:focus,
        html[data-bs-theme="dark"] .adjustment-row .form-select:focus {
            border-color: #818cf8;
            color: var(--bs-body-color);
        }
        html[data-bs-theme="dark"] .btn-remove-adj,
        html[data-bs-theme="dark"] .btn-remove-edit-adj {
            border-color: rgba(239, 68, 68, 0.4);
            color: #f87171;
        }
        html[data-bs-theme="dark"] .btn-remove-adj:hover,
        html[data-bs-theme="dark"] .btn-remove-edit-adj:hover {
            background: #ef4444;
            color: #fff;
            border-color: #ef4444;
        }
        html[data-bs-theme="dark"] input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(1);
            opacity: 0.6;
        }

        /* Dark mode order preview table */
        html[data-bs-theme="dark"] #orderPreviewTable,
        html[data-bs-theme="dark"] #editOrderPreviewTable {
            --bs-table-bg: var(--bs-secondary-bg);
            --bs-table-border-color: var(--bs-border-color);
            --bs-table-color: var(--bs-body-color);
        }
        html[data-bs-theme="dark"] #orderPreviewTable thead th,
        html[data-bs-theme="dark"] #editOrderPreviewTable thead th {
            background-color: var(--bs-tertiary-bg) !important;
            --bs-table-bg: var(--bs-tertiary-bg);
            border-color: var(--bs-border-color) !important;
            color: var(--bs-body-color) !important;
        }
        html[data-bs-theme="dark"] #orderPreviewTable tbody td,
        html[data-bs-theme="dark"] #editOrderPreviewTable tbody td {
            border-color: var(--bs-border-color) !important;
            color: var(--bs-body-color);
        }
        html[data-bs-theme="dark"] #orderPreviewTable tbody tr:hover > td,
        html[data-bs-theme="dark"] #editOrderPreviewTable tbody tr:hover > td {
            --bs-table-bg-state: rgba(255, 255, 255, 0.05);
            background-color: rgba(255, 255, 255, 0.05) !important;
        }
        html[data-bs-theme="dark"] #orderPreviewTable .order-summary-row td,
        html[data-bs-theme="dark"] #editOrderPreviewTable .order-summary-row td {
            --bs-table-bg: rgba(79, 70, 229, 0.18) !important;
            background-color: rgba(79, 70, 229, 0.18) !important;
            /* The light rule uses the `background` shorthand with a gradient, which
               sets background-image; background-color alone can't clear it. */
            background-image: none !important;
            color: #a5b4fc !important;
            border-color: var(--bs-border-color) !important;
        }

        /* Dark mode alerts */
        html[data-bs-theme="dark"] .modal-info-alert {
            background: rgba(14, 165, 233, 0.15) !important;
            color: #7dd3fc !important;
            border: 1px solid rgba(14, 165, 233, 0.3) !important;
        }

        /* Dark mode summary KPI tiles (High Contrast & Vibrant) */
        html[data-bs-theme="dark"] .summary-salary {
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.22), rgba(99, 102, 241, 0.12));
            border-color: rgba(129, 140, 248, 0.35);
            color: #a5b4fc;
        }
        html[data-bs-theme="dark"] .summary-salary h5 {
            color: #818cf8 !important;
        }
        html[data-bs-theme="dark"] .summary-salary h3 {
            color: #c7d2fe !important;
        }

        html[data-bs-theme="dark"] .summary-adj {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.22), rgba(5, 150, 105, 0.12));
            border-color: rgba(52, 211, 153, 0.35);
            color: #6ee7b7;
        }
        html[data-bs-theme="dark"] .summary-adj h5 {
            color: #34d399 !important;
        }
        html[data-bs-theme="dark"] .summary-adj h3 {
            color: #a7f3d0 !important;
        }

        html[data-bs-theme="dark"] .summary-grand {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.22), rgba(217, 119, 6, 0.12));
            border-color: rgba(251, 191, 36, 0.35);
            color: #fde68a;
        }
        html[data-bs-theme="dark"] .summary-grand h5 {
            color: #fbbf24 !important;
        }
        html[data-bs-theme="dark"] .summary-grand h3 {
            color: #fef08a !important;
        }

        /* Dark mode Flatpickr */
        html[data-bs-theme="dark"] .flatpickr-calendar {
            background: #1f2028;
            border: 1px solid var(--bs-border-color);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }
        html[data-bs-theme="dark"] .flatpickr-calendar.arrowTop:before,
        html[data-bs-theme="dark"] .flatpickr-calendar.arrowTop:after {
            border-bottom-color: #1f2028;
        }
        html[data-bs-theme="dark"] .flatpickr-calendar.arrowBottom:before,
        html[data-bs-theme="dark"] .flatpickr-calendar.arrowBottom:after {
            border-top-color: #1f2028;
        }
        html[data-bs-theme="dark"] .flatpickr-months {
            background: transparent;
        }
        html[data-bs-theme="dark"] .flatpickr-months .flatpickr-month,
        html[data-bs-theme="dark"] .flatpickr-current-month,
        html[data-bs-theme="dark"] .flatpickr-current-month .cur-month,
        html[data-bs-theme="dark"] .flatpickr-current-month input.cur-year {
            color: var(--bs-emphasis-color);
            fill: var(--bs-emphasis-color);
        }
        html[data-bs-theme="dark"] .flatpickr-months .flatpickr-prev-month,
        html[data-bs-theme="dark"] .flatpickr-months .flatpickr-next-month {
            color: var(--bs-body-color);
            fill: var(--bs-body-color);
        }
        html[data-bs-theme="dark"] .flatpickr-months .flatpickr-prev-month:hover svg,
        html[data-bs-theme="dark"] .flatpickr-months .flatpickr-next-month:hover svg {
            fill: #818cf8;
        }
        html[data-bs-theme="dark"] span.flatpickr-weekday {
            color: var(--bs-secondary-color);
        }
        html[data-bs-theme="dark"] .flatpickr-day {
            color: var(--bs-body-color);
        }
        html[data-bs-theme="dark"] .flatpickr-day:hover,
        html[data-bs-theme="dark"] .flatpickr-day:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: transparent;
        }
        html[data-bs-theme="dark"] .flatpickr-day.today {
            border-color: #6366f1;
        }
        html[data-bs-theme="dark"] .flatpickr-day.selected,
        html[data-bs-theme="dark"] .flatpickr-day.startRange,
        html[data-bs-theme="dark"] .flatpickr-day.endRange {
            background: #4f46e5;
            border-color: #4f46e5;
            color: #ffffff;
        }
        html[data-bs-theme="dark"] .flatpickr-day.inRange {
            background: rgba(79, 70, 229, 0.25);
            border-color: transparent;
            box-shadow: -5px 0 0 rgba(79, 70, 229, 0.25), 5px 0 0 rgba(79, 70, 229, 0.25);
        }
        html[data-bs-theme="dark"] .flatpickr-day.prevMonthDay,
        html[data-bs-theme="dark"] .flatpickr-day.nextMonthDay {
            color: var(--bs-secondary-color);
            opacity: 0.35;
        }
        html[data-bs-theme="dark"] .flatpickr-day.flatpickr-disabled {
            color: var(--bs-secondary-color);
            opacity: 0.2;
        }
        /* ── Main processed table premium styling ── */
        #dtProcessed {
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        #dtProcessed thead th {
            background-color: #f8fafc;
            color: #475569;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 13px 12px;
            border-bottom: 2px solid #e2e8f0;
            border-top: none;
        }
        #dtProcessed tbody tr {
            transition: all 0.15s ease;
        }
        #dtProcessed tbody tr:hover {
            background-color: #f8fafc !important;
        }
        .avatar-badge-sm {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
            box-shadow: 0 2px 6px rgba(79, 70, 229, 0.2);
            flex-shrink: 0;
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

        /* ── Select2 Theme Matching ── */
        .select2-container--default .select2-selection--single {
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            height: 38px !important;
            display: flex !important;
            align-items: center !important;
            background-color: #ffffff !important;
            transition: all 0.2s ease !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #334155 !important;
            font-size: 13px !important;
            line-height: normal !important;
            padding-left: 10px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #94a3b8 !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
            right: 8px !important;
        }
        .select2-container--default.select2-container--open .select2-selection--single,
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #818cf8 !important;
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.15) !important;
        }
        .select2-dropdown {
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
            overflow: hidden !important;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #4f46e5 !important;
            color: #ffffff !important;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 1px solid #cbd5e1 !important;
            border-radius: 6px !important;
            padding: 6px 10px !important;
        }

        /* ── Dark mode: main processed table (id selector beats global .table) ── */
        html[data-bs-theme="dark"] #dtProcessed {
            border-color: var(--bs-border-color) !important;
        }
        html[data-bs-theme="dark"] #dtProcessed thead th {
            background-color: var(--bs-tertiary-bg) !important;
            color: var(--bs-body-color) !important;
            border-color: var(--bs-border-color) !important;
        }
        html[data-bs-theme="dark"] #dtProcessed tbody td {
            color: var(--bs-body-color);
            border-color: var(--bs-border-color) !important;
        }
        html[data-bs-theme="dark"] #dtProcessed tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.05) !important;
        }

        /* ── Dark mode: empty state ── */
        html[data-bs-theme="dark"] .empty-state i {
            color: var(--bs-border-color);
        }
        html[data-bs-theme="dark"] .empty-state p {
            color: var(--bs-secondary-color);
        }

        /* ── Dark mode: Filter card (PHL §3) ── */
        html[data-bs-theme="dark"] .filter-card {
            background: var(--bs-secondary-bg);
            border-color: var(--bs-border-color);
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

        html[data-bs-theme="dark"] .btn-filter-reset {
            background: var(--bs-tertiary-bg) !important;
            border-color: var(--bs-border-color) !important;
            color: var(--bs-body-color) !important;
        }

        /* ══════════════════════════════════════════════════════════════ */
        /* ── Dark mode: completeness net for the Process/Edit Salary  ── */
        /*    modals (catches any surface still falling back to a light  */
        /*    default, plus the un-styled modal scrollbars).             */
        /* ══════════════════════════════════════════════════════════════ */
        html[data-bs-theme="dark"] #processSalaryModal .modal-content,
        html[data-bs-theme="dark"] #editSalaryModal .modal-content {
            background-color: var(--bs-secondary-bg) !important;
            color: var(--bs-body-color);
        }
        html[data-bs-theme="dark"] #processSalaryModal .modal-body,
        html[data-bs-theme="dark"] #editSalaryModal .modal-body {
            background-color: var(--bs-body-bg) !important;
            color: var(--bs-body-color);
        }
        html[data-bs-theme="dark"] #processSalaryModal .modal-section,
        html[data-bs-theme="dark"] #editSalaryModal .modal-section {
            background-color: var(--bs-secondary-bg) !important;
            border-color: var(--bs-border-color) !important;
        }
        html[data-bs-theme="dark"] #processSalaryModal .modal-footer,
        html[data-bs-theme="dark"] #editSalaryModal .modal-footer {
            background-color: var(--bs-secondary-bg) !important;
            border-top-color: var(--bs-border-color) !important;
        }
        /* Form controls, inputs and the light footer button */
        html[data-bs-theme="dark"] #processSalaryModal .form-control,
        html[data-bs-theme="dark"] #processSalaryModal .form-select,
        html[data-bs-theme="dark"] #editSalaryModal .form-control,
        html[data-bs-theme="dark"] #editSalaryModal .form-select {
            background-color: var(--bs-secondary-bg) !important;
            border-color: var(--bs-border-color) !important;
            color: var(--bs-body-color) !important;
        }
        html[data-bs-theme="dark"] #processSalaryModal .input-group-text,
        html[data-bs-theme="dark"] #editSalaryModal .input-group-text {
            background-color: var(--bs-tertiary-bg) !important;
            border-color: var(--bs-border-color) !important;
            color: var(--bs-secondary-color) !important;
        }
        html[data-bs-theme="dark"] #processSalaryModal .btn-light,
        html[data-bs-theme="dark"] #editSalaryModal .btn-light {
            background-color: var(--bs-tertiary-bg) !important;
            border-color: var(--bs-border-color) !important;
            color: var(--bs-body-color) !important;
        }
        /* Generic leftover surfaces (skip the blue info alert) */
        html[data-bs-theme="dark"] #processSalaryModal .bg-white,
        html[data-bs-theme="dark"] #processSalaryModal .table-responsive,
        html[data-bs-theme="dark"] #processSalaryModal .card,
        html[data-bs-theme="dark"] #editSalaryModal .bg-white,
        html[data-bs-theme="dark"] #editSalaryModal .table-responsive,
        html[data-bs-theme="dark"] #editSalaryModal .card {
            background-color: transparent !important;
        }
        html[data-bs-theme="dark"] #processSalaryModal .alert:not(.modal-info-alert),
        html[data-bs-theme="dark"] #editSalaryModal .alert:not(.modal-info-alert) {
            background-color: var(--bs-tertiary-bg) !important;
            border-color: var(--bs-border-color) !important;
            color: var(--bs-body-color) !important;
        }
        html[data-bs-theme="dark"] #processSalaryModal .table,
        html[data-bs-theme="dark"] #editSalaryModal .table {
            --bs-table-bg: var(--bs-secondary-bg);
            --bs-table-border-color: var(--bs-border-color);
            --bs-table-color: var(--bs-body-color);
            color: var(--bs-body-color);
        }
        /* Scrollbars — .custom-scrollbar is not defined on this page, so the
           native (white) scrollbar of the modal's table wrapper was showing. */
        html[data-bs-theme="dark"] #processSalaryModal .modal-body,
        html[data-bs-theme="dark"] #processSalaryModal .custom-scrollbar,
        html[data-bs-theme="dark"] #processSalaryModal .table-responsive,
        html[data-bs-theme="dark"] #editSalaryModal .modal-body,
        html[data-bs-theme="dark"] #editSalaryModal .custom-scrollbar,
        html[data-bs-theme="dark"] #editSalaryModal .table-responsive {
            scrollbar-color: var(--bs-border-color) var(--bs-body-bg);
            scrollbar-width: thin;
        }
        html[data-bs-theme="dark"] #processSalaryModal .custom-scrollbar::-webkit-scrollbar,
        html[data-bs-theme="dark"] #processSalaryModal .table-responsive::-webkit-scrollbar,
        html[data-bs-theme="dark"] #editSalaryModal .custom-scrollbar::-webkit-scrollbar,
        html[data-bs-theme="dark"] #editSalaryModal .table-responsive::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        html[data-bs-theme="dark"] #processSalaryModal .custom-scrollbar::-webkit-scrollbar-track,
        html[data-bs-theme="dark"] #processSalaryModal .table-responsive::-webkit-scrollbar-track,
        html[data-bs-theme="dark"] #editSalaryModal .custom-scrollbar::-webkit-scrollbar-track,
        html[data-bs-theme="dark"] #editSalaryModal .table-responsive::-webkit-scrollbar-track {
            background: var(--bs-body-bg);
        }
        html[data-bs-theme="dark"] #processSalaryModal .custom-scrollbar::-webkit-scrollbar-thumb,
        html[data-bs-theme="dark"] #processSalaryModal .table-responsive::-webkit-scrollbar-thumb,
        html[data-bs-theme="dark"] #editSalaryModal .custom-scrollbar::-webkit-scrollbar-thumb,
        html[data-bs-theme="dark"] #editSalaryModal .table-responsive::-webkit-scrollbar-thumb {
            background: var(--bs-border-color);
            border-radius: 4px;
        }
        html[data-bs-theme="dark"] #processSalaryModal .custom-scrollbar::-webkit-scrollbar-thumb:hover,
        html[data-bs-theme="dark"] #processSalaryModal .table-responsive::-webkit-scrollbar-thumb:hover,
        html[data-bs-theme="dark"] #editSalaryModal .custom-scrollbar::-webkit-scrollbar-thumb:hover,
        html[data-bs-theme="dark"] #editSalaryModal .table-responsive::-webkit-scrollbar-thumb:hover {
            background: var(--bs-secondary-color);
        }
    </style>
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
            <div class="card-header py-3 border-bottom d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1 fw-bold d-flex align-items-center gap-2">
                        <i class="mdi mdi-cash-register text-primary fs-20"></i>
                        {{ $title }} Data
                    </h4>
                    <small class="text-muted">Kelola dan lihat seluruh riwayat proses gaji supir</small>
                </div>

                <div class="d-flex align-items-center gap-2">
                    @if (in_array(Auth::user()->roleCode, ['SPRADMIN', 'SPRUSER']))
                        <button type="button" class="btn btn-outline-success" id="btn-sync-existing-status" style="border-radius: 10px; font-weight: 600; padding: 9px 16px;">
                            <i class="mdi mdi-sync me-1"></i> Sinkron Status Gaji (Selesai)
                        </button>
                    @endif
                    <button type="button" class="btn btn-process" data-bs-toggle="modal" data-bs-target="#processSalaryModal">
                        <i class="mdi mdi-cash-plus me-1"></i> Proses Gaji Driver
                    </button>
                </div>
            </div>

            <div class="card-body p-4">
                @include('partials.alert')

                {{-- Filter Bar (Standard Collapsible Panel) --}}
                <div class="filter-card">
                    <button type="button" class="filter-card-header" data-bs-toggle="collapse"
                        data-bs-target="#driverSalaryFilterCollapse" aria-expanded="false" aria-controls="driverSalaryFilterCollapse">
                        <span class="filter-card-heading">
                            <i class="mdi mdi-filter-variant"></i>
                            <strong>Filter Data</strong>
                            <small>Gunakan filter untuk mempersempit daftar riwayat gaji supir</small>
                        </span>
                        <i class="mdi mdi-chevron-down filter-card-chevron"></i>
                    </button>

                    <div class="collapse filter-collapse" id="driverSalaryFilterCollapse">
                        <div class="filter-collapse-body">
                            <div id="filterForm">
                                <div class="row g-3">
                                    <div class="col-xl-4 col-md-6">
                                        <label class="filter-label" for="filterTableDriver">Supir</label>
                                        <select id="filterTableDriver" name="filterDriverCode" class="form-select select2-filter" style="width:100%;">
                                            <option value="">Semua Supir</option>
                                            @foreach ($driver as $item)
                                                <option value="{{ $item->code }}">{{ $item->name }} ({{ $item->code }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="filterTableStartDate">Dari Tanggal</label>
                                        <input type="text" id="filterTableStartDate" name="filterStartDate" class="form-control filter-control" placeholder="Pilih Tanggal Mulai">
                                    </div>
                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="filterTableEndDate">Sampai Tanggal</label>
                                        <input type="text" id="filterTableEndDate" name="filterEndDate" class="form-control filter-control" placeholder="Pilih Tanggal Akhir">
                                    </div>
                                    <div class="col-xl-2 col-md-6 d-flex align-items-end justify-content-md-end gap-2">
                                        <button type="button" id="btnFilterTable" class="btn btn-filter-primary flex-grow-1 flex-md-grow-0">
                                            <i class="mdi mdi-filter-outline"></i> Terapkan
                                        </button>
                                        <button type="button" id="btnResetTableFilter" class="btn btn-filter-reset" data-bs-toggle="tooltip" title="Reset Filter">
                                            <i class="mdi mdi-refresh"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Main datatable: Processed salaries --}}
                <div class="table-responsive custom-scrollbar">
                    <table class="table align-middle w-100 mb-0" id="dtProcessed">
                        <thead>
                            <tr>
                                <th style="width: 8%;" class="text-center">Aksi</th>
                                <th style="width: 4%;" class="text-center">No</th>
                                <th style="width: 12%;">Kode Gaji</th>
                                <th style="width: 22%;">Nama Supir</th>
                                <th style="width: 16%;" class="text-center">Periode Gaji</th>
                                <th style="width: 13%;" class="text-end">Gaji Order</th>
                                <th style="width: 11%;" class="text-end">Penyesuaian</th>
                                <th style="width: 14%;" class="text-end">Grand Total</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- Modal: Process Salary                                      --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="modal fade" id="processSalaryModal" tabindex="-1" aria-labelledby="processSalaryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <form action="{{ route('report.driver-salary.store') }}" method="POST" id="processSalaryForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title text-white" id="processSalaryModalLabel">
                            <i class="mdi mdi-cash-plus me-2"></i> Proses Gaji Driver
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        {{-- Section 1: Select driver & period --}}
                        <div class="modal-section">
                            <div class="modal-section-title">
                                <span class="section-badge">1</span>
                                Pilih Driver & Periode
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Nama Supir <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="mdi mdi-account fs-16"></i>
                                        </span>
                                        <div class="flex-grow-1 select2-wrapper">
                                            <select class="js-example-basic-single" name="driverCode" id="modalDriverCode" required>
                                                <option selected="" value="">{{ __('general.choose') }}...</option>
                                                @foreach ($driver as $item)
                                                    <option value="{{ $item->code }}">{{ $item->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Tanggal Mulai <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text border-end-0">
                                            <i class="mdi mdi-calendar-range fs-16"></i>
                                        </span>
                                        <input class="form-control border-start-0" name="startDate" id="modalStartDate" type="text" placeholder="Pilih Tanggal" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Tanggal Akhir <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text border-end-0">
                                            <i class="mdi mdi-calendar-range fs-16"></i>
                                        </span>
                                        <input class="form-control border-start-0" name="endDate" id="modalEndDate" type="text" placeholder="Pilih Tanggal" required>
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="button" class="btn btn-search-order w-100" id="btnSearchOrders" style="height: 38px; border-radius: 8px; font-size: 14px; padding: 0;">
                                        <i class="mdi mdi-magnify me-1"></i> Cari
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Section 2: Order preview & manual adjustment --}}
                        <div id="orderPreviewSection" style="display: none;">
                            <div class="modal-section">
                                <div class="modal-section-title">
                                    <span class="section-badge">2</span>
                                    Daftar Order
                                    <span class="ms-auto badge bg-primary rounded-pill" id="orderCount">0 order</span>
                                </div>
                                <div id="orderTableContainer">
                                    <div class="table-responsive custom-scrollbar" style="max-height: 280px;">
                                        <table class="table table-bordered table-sm mb-0" id="orderPreviewTable">
                                            <thead>
                                                <tr>
                                                    <th style="width: 5%;">No</th>
                                                    <th style="width: 22%;">No. Shipment / Order</th>
                                                    <th style="width: 11%;">Tanggal</th>
                                                    <th style="width: 12%;">No. Polisi</th>
                                                    <th style="width: 35%;">Rute</th>
                                                    <th style="width: 15%;" class="text-end">Gaji</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                                <div id="noOrderAlert" class="alert modal-info-alert border-0 shadow-sm mb-0" style="display:none;">
                                    <div class="d-flex align-items-center">
                                        <i class="mdi mdi-information-outline me-3" style="font-size:24px;"></i>
                                        <div>
                                            <strong style="font-size:14px;">Tidak ada order dengan komponen gaji pada periode ini.</strong>
                                            <div class="small mt-1 text-opacity-75">Anda tetap dapat memproses gaji supir ini dengan memasukkan item Penambah / Pengurang secara manual di bawah.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Section 3: Adjustments --}}
                            <div class="modal-section">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="modal-section-title mb-0">
                                        <span class="section-badge">3</span>
                                        Penambah / Pengurang
                                    </div>
                                    <button type="button" class="btn btn-sm btn-add-adj" id="btnAddAdjustment">
                                        <i class="mdi mdi-plus me-1"></i> Tambah Item
                                    </button>
                                </div>

                                <div id="adjustmentsContainer">
                                    <div class="text-center text-muted py-2" id="noAdjustmentHint" style="font-size:13px;">
                                        <i class="mdi mdi-information-outline me-1"></i> Belum ada item penambah/pengurang. Klik tombol di atas untuk menambahkan.
                                    </div>
                                </div>
                            </div>

                            {{-- Section 4: Summary --}}
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <div class="summary-card summary-salary">
                                        <h5>Total Gaji Order</h5>
                                        <h3 id="summaryTotalSalary">Rp 0</h3>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="summary-card summary-adj">
                                        <h5>Total Penyesuaian</h5>
                                        <h3 id="summaryTotalAdjustment">Rp 0</h3>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="summary-card summary-grand">
                                        <h5>Grand Total</h5>
                                        <h3 id="summaryGrandTotal">Rp 0</h3>
                                    </div>
                                </div>
                            </div>

                            {{-- Notes --}}
                            <div class="modal-section" style="margin-bottom: 0;">
                                <label class="form-label fw-semibold">Catatan <span class="text-muted fw-normal">(Opsional)</span></label>
                                <textarea class="form-control" name="notes" rows="2" placeholder="Catatan tambahan..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius:8px;">Tutup</button>
                        <button type="submit" class="btn btn-submit-salary" id="btnSubmitSalary" style="display: none;">
                            <i class="mdi mdi-check-circle me-1"></i> Proses Gaji
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- Modal: Edit Salary                                         --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="modal fade" id="editSalaryModal" tabindex="-1" aria-labelledby="editSalaryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <form method="POST" id="editSalaryForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editSalaryId" name="salaryId">
                    <div class="modal-header">
                        <h5 class="modal-title text-white" id="editSalaryModalLabel">
                            <i class="mdi mdi-pencil me-2"></i> Edit Gaji Driver
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        {{-- Section 1: Select driver & period --}}
                        <div class="modal-section">
                            <div class="modal-section-title">
                                <span class="section-badge">1</span>
                                Pilih Driver & Periode
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Nama Supir <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="mdi mdi-account fs-16"></i>
                                        </span>
                                        <div class="flex-grow-1 select2-wrapper">
                                            <select class="js-example-basic-single" name="driverCode" id="editDriverCode" required>
                                                <option selected="" value="">{{ __('general.choose') }}...</option>
                                                @foreach ($driver as $item)
                                                    <option value="{{ $item->code }}">{{ $item->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Tanggal Mulai <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text border-end-0">
                                            <i class="mdi mdi-calendar-range fs-16"></i>
                                        </span>
                                        <input class="form-control border-start-0" name="startDate" id="editStartDate" type="text" placeholder="Pilih Tanggal" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Tanggal Akhir <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text border-end-0">
                                            <i class="mdi mdi-calendar-range fs-16"></i>
                                        </span>
                                        <input class="form-control border-start-0" name="endDate" id="editEndDate" type="text" placeholder="Pilih Tanggal" required>
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="button" class="btn btn-search-order w-100" id="btnEditSearchOrders" style="height: 38px; border-radius: 8px; font-size: 14px; padding: 0;">
                                        <i class="mdi mdi-magnify me-1"></i> Cari
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Section 2: Order preview & manual adjustment --}}
                        <div id="editOrderPreviewSection" style="display: none;">
                            <div class="modal-section">
                                <div class="modal-section-title">
                                    <span class="section-badge">2</span>
                                    Daftar Order
                                    <span class="ms-auto badge bg-primary rounded-pill" id="editOrderCount">0 order</span>
                                </div>
                                <div id="editOrderTableContainer">
                                    <div class="table-responsive custom-scrollbar" style="max-height: 280px;">
                                        <table class="table table-bordered table-sm mb-0" id="editOrderPreviewTable">
                                            <thead>
                                                <tr>
                                                    <th style="width: 5%;">No</th>
                                                    <th style="width: 22%;">No. Shipment / Order</th>
                                                    <th style="width: 11%;">Tanggal</th>
                                                    <th style="width: 12%;">No. Polisi</th>
                                                    <th style="width: 35%;">Rute</th>
                                                    <th style="width: 15%;" class="text-end">Gaji</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                                <div id="editNoOrderAlert" class="alert modal-info-alert border-0 shadow-sm mb-0" style="display:none;">
                                    <div class="d-flex align-items-center">
                                        <i class="mdi mdi-information-outline me-3" style="font-size:24px;"></i>
                                        <div>
                                            <strong style="font-size:14px;">Tidak ada order dengan komponen gaji pada periode ini.</strong>
                                            <div class="small mt-1 text-opacity-75">Anda tetap dapat menyimpan perubahan gaji supir ini dengan memasukkan item Penambah / Pengurang secara manual di bawah.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Section 3: Adjustments --}}
                            <div class="modal-section">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="modal-section-title mb-0">
                                        <span class="section-badge">3</span>
                                        Penambah / Pengurang
                                    </div>
                                    <button type="button" class="btn btn-sm btn-add-adj" id="btnEditAddAdjustment">
                                        <i class="mdi mdi-plus me-1"></i> Tambah Item
                                    </button>
                                </div>

                                <div id="editAdjustmentsContainer">
                                    <div class="text-center text-muted py-2" id="editNoAdjustmentHint" style="font-size:13px;">
                                        <i class="mdi mdi-information-outline me-1"></i> Belum ada item penambah/pengurang. Klik tombol di atas untuk menambahkan.
                                    </div>
                                </div>
                            </div>

                            {{-- Section 4: Summary --}}
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <div class="summary-card summary-salary">
                                        <h5>Total Gaji Order</h5>
                                        <h3 id="editSummaryTotalSalary">Rp 0</h3>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="summary-card summary-adj">
                                        <h5>Total Penyesuaian</h5>
                                        <h3 id="editSummaryTotalAdjustment">Rp 0</h3>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="summary-card summary-grand">
                                        <h5>Grand Total</h5>
                                        <h3 id="editSummaryGrandTotal">Rp 0</h3>
                                    </div>
                                </div>
                            </div>

                            {{-- Notes --}}
                            <div class="modal-section" style="margin-bottom: 0;">
                                <label class="form-label fw-semibold">Catatan <span class="text-muted fw-normal">(Opsional)</span></label>
                                <textarea class="form-control" name="notes" id="editNotes" rows="2" placeholder="Catatan tambahan..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius:8px;">Tutup</button>
                        <button type="submit" class="btn btn-submit-salary" id="btnUpdateSalary" style="display: none;">
                            <i class="mdi mdi-check-circle me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Delete form (hidden) --}}
    <form id="deleteForm" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('script')
    <script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-keytable-bs5/js/keyTable.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-select/js/dataTables.select.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-select-bs5/js/select.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2-custom.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/custom-flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/helper.js') }}"></script>

    <script>
        let totalSalaryFromOrders = 0;
        let startPicker, endPicker;
        let filterTableStartPicker, filterTableEndPicker;
        
        let editTotalSalaryFromOrders = 0;
        let editStartPicker, editEndPicker;

        $(document).ready(function() {

            // Initialize Filter Bar inputs
            $('#filterTableDriver').select2({
                placeholder: 'Semua Supir',
                allowClear: true
            });

            filterTableStartPicker = flatpickr('#filterTableStartDate', {
                dateFormat: 'Y-m-d',
                allowInput: true,
                onChange: function(selectedDates, dateStr) {
                    if (filterTableEndPicker) filterTableEndPicker.set('minDate', dateStr || null);
                }
            });

            filterTableEndPicker = flatpickr('#filterTableEndDate', {
                dateFormat: 'Y-m-d',
                allowInput: true,
                onChange: function(selectedDates, dateStr) {
                    if (filterTableStartPicker) filterTableStartPicker.set('maxDate', dateStr || null);
                }
            });

            // ============================================================
            // Initialize flatpickr & select2 inside modal
            // ============================================================
            startPicker = flatpickr('#modalStartDate', {
                dateFormat: 'Y-m-d',
                allowInput: true,
                onChange: function(selectedDates, dateStr, instance) {
                    if (endPicker) endPicker.set('minDate', dateStr);
                }
            });

            endPicker = flatpickr('#modalEndDate', {
                dateFormat: 'Y-m-d',
                allowInput: true,
                onChange: function(selectedDates, dateStr, instance) {
                    if (startPicker) startPicker.set('maxDate', dateStr);
                }
            });

            $('#processSalaryModal').on('shown.bs.modal', function () {
                $('#modalDriverCode').select2({
                    dropdownParent: $('#processSalaryModal'),
                    placeholder: 'Pilih Supir...'
                });
            });

            editStartPicker = flatpickr('#editStartDate', {
                dateFormat: 'Y-m-d',
                allowInput: true,
                onChange: function(selectedDates, dateStr, instance) {
                    if (editEndPicker) editEndPicker.set('minDate', dateStr);
                }
            });

            editEndPicker = flatpickr('#editEndDate', {
                dateFormat: 'Y-m-d',
                allowInput: true,
                onChange: function(selectedDates, dateStr, instance) {
                    if (editStartPicker) editStartPicker.set('maxDate', dateStr);
                }
            });

            $('#editSalaryModal').on('shown.bs.modal', function () {
                $('#editDriverCode').select2({
                    dropdownParent: $('#editSalaryModal'),
                    placeholder: 'Pilih Supir...'
                });
            });

            // Reset modal state when closed
            $('#processSalaryModal').on('hidden.bs.modal', function () {
                totalSalaryFromOrders = 0;
                $('#orderPreviewSection').hide();
                $('#noOrderAlert').hide();
                $('#orderTableContainer').show();
                $('#btnSubmitSalary').hide();
                $('#orderPreviewTable tbody').html('');
                $('#adjustmentsContainer').html(
                    '<div class="text-center text-muted py-2" id="noAdjustmentHint" style="font-size:13px;">' +
                    '<i class="mdi mdi-information-outline me-1"></i> Belum ada item penambah/pengurang. Klik tombol di atas untuk menambahkan.' +
                    '</div>'
                );
                $('#processSalaryForm')[0].reset();
                $('#modalDriverCode').val('').trigger('change');
                
                // Clear flatpickr values
                if (startPicker) startPicker.clear();
                if (endPicker) endPicker.clear();
                
                recalcSummary();
            });

            // Reset edit modal state when closed
            $('#editSalaryModal').on('hidden.bs.modal', function () {
                editTotalSalaryFromOrders = 0;
                $('#editOrderPreviewSection').hide();
                $('#editNoOrderAlert').hide();
                $('#editOrderTableContainer').show();
                $('#btnUpdateSalary').hide();
                $('#editOrderPreviewTable tbody').html('');
                $('#editAdjustmentsContainer').html(
                    '<div class="text-center text-muted py-2" id="editNoAdjustmentHint" style="font-size:13px;">' +
                    '<i class="mdi mdi-information-outline me-1"></i> Belum ada item penambah/pengurang. Klik tombol di atas untuk menambahkan.' +
                    '</div>'
                );
                $('#editSalaryForm')[0].reset();
                $('#editDriverCode').val('').trigger('change');
                
                // Clear flatpickr values
                if (editStartPicker) editStartPicker.clear();
                if (editEndPicker) editEndPicker.clear();
                
                recalcEditSummary();
            });

            // ============================================================
            // Filter Helper & Main DataTable: Processed salaries
            // ============================================================
            function getFilters() {
                return {
                    filterDriverCode: $('#filterTableDriver').val() || '',
                    filterStartDate: $('#filterTableStartDate').val() || '',
                    filterEndDate: $('#filterTableEndDate').val() || ''
                };
            }

            function reloadWithFilters() {
                processedTable.ajax.reload(null, true);
            }

            const processedTable = $('#dtProcessed').DataTable({
                "processing": true,
                "serverSide": true,
                "destroy": true,
                "pageLength": 25,
                "ajax": {
                    "url": "{{ route('dt.driver-salary-processed') }}",
                    "data": function(d) {
                        Object.assign(d, getFilters());
                    }
                },
                "columns": [
                    { "data": "action", "className": "text-center align-middle" },
                    { "data": "DT_RowIndex", "className": "text-center align-middle" },
                    { "data": "code", "className": "align-middle" },
                    { "data": "driverName", "className": "align-middle" },
                    { "data": "periode", "className": "align-middle text-center" },
                    { "data": "totalSalaryFormatted", "className": "text-end align-middle" },
                    { "data": "totalAdjustmentFormatted", "className": "text-end align-middle" },
                    { "data": "grandTotalFormatted", "className": "text-end align-middle" },
                ],
                "columnDefs": [
                    { "searchable": false, "targets": [0, 1, 4, 5, 6, 7] },
                    { "orderable": false, "targets": [0, 1] }
                ],
                "language": {
                    "search": "Cari (Nama Supir / Kode):",
                    "lengthMenu": "Tampilkan _MENU_ data",
                    "info": "Menampilkan _START_ - _END_ dari _TOTAL_ data gaji supir",
                    "infoEmpty": "Tidak ada data gaji supir",
                    "zeroRecords": "Data gaji supir tidak ditemukan",
                    "processing": '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Memuat data...'
                }
            });

            $('#filterTableDriver').on('change', function() {
                reloadWithFilters();
            });

            $('#btnFilterTable').on('click', function() {
                reloadWithFilters();
            });

            $('#filterTableStartDate, #filterTableEndDate').on('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    reloadWithFilters();
                }
            });

            $('#btnResetTableFilter').on('click', function() {
                $('#filterTableDriver').val('').trigger('change');
                if (filterTableStartPicker) {
                    filterTableStartPicker.clear();
                    filterTableStartPicker.set('maxDate', null);
                }
                if (filterTableEndPicker) {
                    filterTableEndPicker.clear();
                    filterTableEndPicker.set('minDate', null);
                }
                reloadWithFilters();
            });

            if (typeof $.fn.tooltip === 'function') {
                $('[data-bs-toggle="tooltip"]').tooltip();
            }

            // ============================================================
            // Modal: Adjustment rows
            // ============================================================
            let adjIndex = 0;

            function addAdjustmentRow(date = '', description = '', type = 'addition', nominal = '') {
                $('#noAdjustmentHint').hide();
                const formattedDate = date ? date.substring(0, 10) : '';
                const html = `
                    <div class="adjustment-row" data-index="${adjIndex}">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-2">
                                <label class="form-label adjustment-label">Tanggal</label>
                                <input type="date" class="form-control form-control-sm" name="adjustments[${adjIndex}][date]" value="${formattedDate}" required style="border-radius:6px;">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label adjustment-label">Deskripsi</label>
                                <input type="text" class="form-control form-control-sm" name="adjustments[${adjIndex}][description]" value="${description}" placeholder="Contoh: Potong utang, Bonus, dll" required style="border-radius:6px;">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label adjustment-label">Tipe</label>
                                <select class="form-select form-select-sm adj-type" name="adjustments[${adjIndex}][type]" required style="border-radius:6px;">
                                    <option value="addition" ${type === 'addition' ? 'selected' : ''}>➕ Penambah</option>
                                    <option value="deduction" ${type === 'deduction' ? 'selected' : ''}>➖ Pengurang</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label adjustment-label">Nominal (Rp)</label>
                                <input type="text" class="form-control form-control-sm adj-nominal" name="adjustments[${adjIndex}][nominal]" value="${nominal}" placeholder="0" oninput="formatAngka(this)" required style="border-radius:6px;">
                            </div>
                            <div class="col-md-1 d-flex justify-content-center">
                                <button type="button" class="btn btn-sm btn-remove-adj" title="Hapus">
                                    <i class="mdi mdi-close"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                $('#adjustmentsContainer').append(html);
                adjIndex++;
                recalcSummary();
            }

            $('#btnAddAdjustment').on('click', function() {
                addAdjustmentRow();
            });

            // ============================================================
            // Modal: Search orders
            // ============================================================
            $('#btnSearchOrders').on('click', function() {
                const driverCode = $('#modalDriverCode').val();
                const startDate = $('#modalStartDate').val();
                const endDate = $('#modalEndDate').val();

                if (!driverCode || !startDate || !endDate) {
                    swal('Peringatan', 'Pilih driver dan periode terlebih dahulu.', 'warning');
                    return;
                }

                const btn = $(this);
                btn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin me-1"></i> Mencari...');

                $.ajax({
                    url: "{{ route('ajax.driver-salary-orders') }}",
                    data: { driverCode, startDate, endDate },
                    success: function(response) {
                        $('#orderPreviewSection').show();
                        $('#btnSubmitSalary').show();

                        if (!response.orders || response.orders.length === 0) {
                            $('#orderTableContainer').hide();
                            $('#noOrderAlert').show();
                            $('#orderCount').text('0 order');
                            totalSalaryFromOrders = 0;

                            // Automatically add 1 adjustment row if none exist
                            if ($('.adjustment-row').length === 0) {
                                $('#noAdjustmentHint').hide();
                                addAdjustmentRow(startDate, '', 'addition', '');
                            }
                        } else {
                            totalSalaryFromOrders = response.totalSalary;
                            $('#noOrderAlert').hide();
                            $('#orderTableContainer').show();

                            // Populate table
                            let tbody = '';
                            response.orders.forEach(function(order, idx) {
                                let orderNumDisplay = (order.shipmentNumber && order.shipmentNumber !== '-') 
                                    ? order.shipmentNumber 
                                    : order.orderCode;
                                let subCode = (order.shipmentNumber && order.shipmentNumber !== '-' && order.orderCode) 
                                    ? '<br><small class="text-muted">' + order.orderCode + '</small>' 
                                    : '';

                                tbody += '<tr>' +
                                    '<td class="text-center">' + (idx + 1) + '</td>' +
                                    '<td class="fw-semibold text-primary">' + orderNumDisplay + subCode + '</td>' +
                                    '<td>' + order.orderDate + '</td>' +
                                    '<td>' + order.plateNumber + '</td>' +
                                    '<td>' + order.routeName + '</td>' +
                                    '<td class="text-end fw-semibold">Rp ' + order.salaryFormatted + '</td>' +
                                    '</tr>';
                            });
                            tbody += '<tr class="order-summary-row">' +
                                '<td colspan="5" class="text-end fw-bold">Total Gaji dari Order</td>' +
                                '<td class="text-end fw-bold">Rp ' + response.totalSalaryFormatted + '</td>' +
                                '</tr>';

                            $('#orderPreviewTable tbody').html(tbody);
                            $('#orderCount').text(response.orders.length + ' order');
                        }

                        recalcSummary();
                    },
                    error: function(xhr) {
                        swal('Error', xhr.responseJSON?.error || 'Terjadi kesalahan', 'error');
                    },
                    complete: function() {
                        btn.prop('disabled', false).html('<i class="mdi mdi-magnify me-1"></i> Cari');
                    }
                });
            });

            // Remove adjustment row
            $(document).on('click', '.btn-remove-adj', function() {
                $(this).closest('.adjustment-row').remove();
                if ($('#adjustmentsContainer .adjustment-row').length === 0) {
                    $('#noAdjustmentHint').show();
                }
                recalcSummary();
            });

            $(document).on('click', '.btn-remove-edit-adj', function() {
                $(this).closest('.adjustment-row').remove();
                if ($('#editAdjustmentsContainer .adjustment-row').length === 0) {
                    $('#editNoAdjustmentHint').show();
                }
                recalcEditSummary();
            });

            $(document).on('change keyup input', '.edit-adj-nominal, .edit-adj-type', function() {
                recalcEditSummary();
            });

            // Recalculate on change
            $(document).on('change keyup input', '.adj-nominal, .adj-type', function() {
                recalcSummary();
            });

            // ============================================================
            // Submit validation
            // ============================================================
            $('#processSalaryForm').on('submit', function(e) {
                const adjCount = $('.adjustment-row').length;
                if (totalSalaryFromOrders <= 0 && adjCount === 0) {
                    e.preventDefault();
                    swal('Peringatan', 'Tidak ada order dan belum ada item penambah/pengurang yang dimasukkan. Silakan tambahkan item penambah/pengurang manual terlebih dahulu.', 'warning');
                    return;
                }

                // Clean dots from adjustments nominal fields so the backend receives raw numbers
                $('.adj-nominal').each(function() {
                    const rawVal = $(this).val() || '';
                    const cleanVal = rawVal.replace(/\./g, '');
                    $(this).val(cleanVal);
                });
            });

            // ============================================================
            // Modal Edit: Search orders
            // ============================================================
            $('#btnEditSearchOrders').on('click', function() {
                const driverCode = $('#editDriverCode').val();
                const startDate = $('#editStartDate').val();
                const endDate = $('#editEndDate').val();

                if (!driverCode || !startDate || !endDate) {
                    swal('Peringatan', 'Pilih driver dan periode terlebih dahulu.', 'warning');
                    return;
                }

                const btn = $(this);
                btn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin me-1"></i> Mencari...');

                $.ajax({
                    url: "{{ route('ajax.driver-salary-orders') }}",
                    data: { driverCode, startDate, endDate },
                    success: function(response) {
                        $('#editOrderPreviewSection').show();
                        $('#btnUpdateSalary').show();

                        if (!response.orders || response.orders.length === 0) {
                            $('#editOrderTableContainer').hide();
                            $('#editNoOrderAlert').show();
                            $('#editOrderCount').text('0 order');
                            editTotalSalaryFromOrders = 0;

                            if ($('.edit-adj-type').length === 0) {
                                $('#editNoAdjustmentHint').hide();
                                addEditAdjustmentRow(startDate, '', 'addition', '');
                            }
                        } else {
                            editTotalSalaryFromOrders = response.totalSalary;
                            $('#editNoOrderAlert').hide();
                            $('#editOrderTableContainer').show();

                            // Populate table
                            let tbody = '';
                            response.orders.forEach(function(order, idx) {
                                let orderNumDisplay = (order.shipmentNumber && order.shipmentNumber !== '-') 
                                    ? order.shipmentNumber 
                                    : order.orderCode;
                                let subCode = (order.shipmentNumber && order.shipmentNumber !== '-' && order.orderCode) 
                                    ? '<br><small class="text-muted">' + order.orderCode + '</small>' 
                                    : '';

                                tbody += '<tr>' +
                                    '<td class="text-center">' + (idx + 1) + '</td>' +
                                    '<td class="fw-semibold text-primary">' + orderNumDisplay + subCode + '</td>' +
                                    '<td>' + order.orderDate + '</td>' +
                                    '<td>' + order.plateNumber + '</td>' +
                                    '<td>' + order.routeName + '</td>' +
                                    '<td class="text-end fw-semibold">Rp ' + order.salaryFormatted + '</td>' +
                                    '</tr>';
                            });
                            tbody += '<tr class="order-summary-row">' +
                                '<td colspan="5" class="text-end fw-bold">Total Gaji dari Order</td>' +
                                '<td class="text-end fw-bold">Rp ' + response.totalSalaryFormatted + '</td>' +
                                '</tr>';

                            $('#editOrderPreviewTable tbody').html(tbody);
                            $('#editOrderCount').text(response.orders.length + ' order');
                        }

                        recalcEditSummary();
                    },
                    error: function(xhr) {
                        swal('Error', xhr.responseJSON?.error || 'Terjadi kesalahan', 'error');
                    },
                    complete: function() {
                        btn.prop('disabled', false).html('<i class="mdi mdi-magnify me-1"></i> Cari');
                    }
                });
            });

            // ============================================================
            // Modal Edit: Adjustment rows
            // ============================================================
            $('#btnEditAddAdjustment').on('click', function() {
                $('#editNoAdjustmentHint').hide();
                addEditAdjustmentRow('', '', 'addition', '');
            });

            // ============================================================
            // Edit Form Submit validation
            // ============================================================
            $('#editSalaryForm').on('submit', function(e) {
                e.preventDefault();

                const adjCount = $('.edit-adj-type').length;
                if (editTotalSalaryFromOrders <= 0 && adjCount === 0) {
                    swal('Peringatan', 'Tidak ada order dan belum ada item penambah/pengurang yang dimasukkan. Silakan tambahkan item penambah/pengurang manual terlebih dahulu.', 'warning');
                    return;
                }

                // Clean dots from adjustments nominal fields so the backend receives raw numbers
                $('.edit-adj-nominal').each(function() {
                    const rawVal = $(this).val() || '';
                    const cleanVal = rawVal.replace(/\./g, '');
                    $(this).val(cleanVal);
                });

                const form = $(this);
                const actionUrl = form.attr('action');

                $.ajax({
                    url: actionUrl,
                    method: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        if (response.success) {
                            $('#editSalaryModal').modal('hide');
                            swal('Sukses', response.message, 'success');
                            processedTable.ajax.reload(null, false);
                        } else {
                            swal('Error', response.message || 'Gagal menyimpan perubahan', 'error');
                        }
                    },
                    error: function(xhr) {
                        swal('Error', xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan data', 'error');
                    }
                });
            });
        });

        // ============================================================
        // Helper functions
        // ============================================================

        function recalcSummary() {
            let totalAdj = 0;
            $('#adjustmentsContainer .adjustment-row').each(function() {
                const type = $(this).find('.adj-type').val();
                const rawVal = $(this).find('.adj-nominal').val() || '';
                const nominal = parseFloat(rawVal.replace(/\./g, '')) || 0;
                if (type === 'addition') {
                    totalAdj += nominal;
                } else {
                    totalAdj -= nominal;
                }
            });

            const grandTotal = totalSalaryFromOrders + totalAdj;

            $('#summaryTotalSalary').text('Rp ' + new Intl.NumberFormat('id-ID').format(totalSalaryFromOrders));
            const adjPrefix = totalAdj >= 0 ? '+' : '';
            $('#summaryTotalAdjustment').text(adjPrefix + 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.abs(totalAdj)));
            $('#summaryGrandTotal').text('Rp ' + new Intl.NumberFormat('id-ID').format(grandTotal));
        }

        function editSalary(id) {
            // Show loading
            swal({
                title: "Loading...",
                text: "Mengambil data gaji...",
                buttons: false,
                closeOnClickOutside: false,
                closeOnEsc: false
            });

            $.ajax({
                url: "{{ url('report/driver-salary') }}/" + id + "/edit",
                method: "GET",
                success: function(response) {
                    swal.close();
                    
                    // Prepopulate form fields
                    $('#editSalaryId').val(response.salary.id);
                    $('#editSalaryForm').attr('action', "{{ url('report/driver-salary') }}/" + response.salary.id);
                    
                    // Set driver
                    $('#editDriverCode').val(response.salary.driverCode).trigger('change');
                    
                    // Set dates
                    if (editStartPicker) editStartPicker.setDate(response.salary.startDate);
                    if (editEndPicker) editEndPicker.setDate(response.salary.endDate);
                    
                    // Set notes
                    $('#editNotes').val(response.salary.notes || '');
                    
                    // Set orders preview
                    editTotalSalaryFromOrders = parseFloat(response.salary.totalSalary) || 0;
                    $('#editOrderPreviewSection').show();
                    $('#btnUpdateSalary').show();

                    if (response.orders.length > 0) {
                        $('#editNoOrderAlert').hide();
                        $('#editOrderTableContainer').show();

                        let tbody = '';
                        response.orders.forEach(function(order, idx) {
                            let orderNumDisplay = (order.shipmentNumber && order.shipmentNumber !== '-') 
                                ? order.shipmentNumber 
                                : (order.orderCode || '-');
                            let subCode = (order.shipmentNumber && order.shipmentNumber !== '-' && order.orderCode) 
                                ? '<br><small class="text-muted">' + order.orderCode + '</small>' 
                                : '';

                            tbody += '<tr>' +
                                '<td class="text-center">' + (idx + 1) + '</td>' +
                                '<td class="fw-semibold text-primary">' + orderNumDisplay + subCode + '</td>' +
                                '<td>' + order.orderDate + '</td>' +
                                '<td>' + order.plateNumber + '</td>' +
                                '<td>' + order.routeName + '</td>' +
                                '<td class="text-end fw-semibold">Rp ' + (order.salaryFormatted || new Intl.NumberFormat('id-ID').format(order.salary || 0)) + '</td>' +
                                '</tr>';
                        });
                        tbody += '<tr class="order-summary-row">' +
                            '<td colspan="5" class="text-end fw-bold">Total Gaji dari Order</td>' +
                            '<td class="text-end fw-bold">Rp ' + new Intl.NumberFormat('id-ID').format(editTotalSalaryFromOrders) + '</td>' +
                            '</tr>';
                        
                        $('#editOrderPreviewTable tbody').html(tbody);
                        $('#editOrderCount').text(response.orders.length + ' order');
                    } else {
                        $('#editOrderTableContainer').hide();
                        $('#editNoOrderAlert').show();
                        $('#editOrderCount').text('0 order');
                    }
                    
                    // Set adjustments
                    $('#editAdjustmentsContainer').html('');
                    editAdjIndex = 0;
                    
                    if (response.salary.details && response.salary.details.length > 0) {
                        $('#editNoAdjustmentHint').hide();
                        response.salary.details.forEach(function(detail) {
                            const formattedNominal = new Intl.NumberFormat('id-ID').format(parseFloat(detail.nominal));
                            addEditAdjustmentRow(detail.date, detail.description, detail.type, formattedNominal);
                        });
                    } else {
                        $('#editAdjustmentsContainer').html(
                            '<div class="text-center text-muted py-2" id="editNoAdjustmentHint" style="font-size:13px;">' +
                            '<i class="mdi mdi-information-outline me-1"></i> Belum ada item penambah/pengurang. Klik tombol di atas untuk menambahkan.' +
                            '</div>'
                        );
                    }
                    
                    recalcEditSummary();
                    
                    // Show modal
                    $('#editSalaryModal').modal('show');
                },
                error: function(xhr) {
                    swal('Error', 'Gagal mengambil data gaji', 'error');
                }
            });
        }

        function addEditAdjustmentRow(date, description, type, nominal) {
            const formattedDate = date ? date.substring(0, 10) : '';
            const html = `
                <div class="adjustment-row edit-adjustment-row" data-index="${editAdjIndex}">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label adjustment-label">Tanggal</label>
                            <input type="date" class="form-control form-control-sm" name="adjustments[${editAdjIndex}][date]" value="${formattedDate}" required style="border-radius:6px;">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label adjustment-label">Deskripsi</label>
                            <input type="text" class="form-control form-control-sm" name="adjustments[${editAdjIndex}][description]" value="${description}" placeholder="Contoh: Potong utang, Bonus, dll" required style="border-radius:6px;">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label adjustment-label">Tipe</label>
                            <select class="form-select form-select-sm edit-adj-type" name="adjustments[${editAdjIndex}][type]" required style="border-radius:6px;">
                                <option value="addition" ${type === 'addition' ? 'selected' : ''}>➕ Penambah</option>
                                <option value="deduction" ${type === 'deduction' ? 'selected' : ''}>➖ Pengurang</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label adjustment-label">Nominal (Rp)</label>
                            <input type="text" class="form-control form-control-sm edit-adj-nominal" name="adjustments[${editAdjIndex}][nominal]" value="${nominal}" placeholder="0" oninput="formatAngka(this)" required style="border-radius:6px;">
                        </div>
                        <div class="col-md-1 d-flex justify-content-center">
                            <button type="button" class="btn btn-sm btn-remove-edit-adj" title="Hapus">
                                <i class="mdi mdi-close"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            $('#editAdjustmentsContainer').append(html);
            editAdjIndex++;
        }

        function recalcEditSummary() {
            let totalAdj = 0;
            $('.edit-adjustment-row').each(function() {
                const type = $(this).find('.edit-adj-type').val();
                const rawVal = $(this).find('.edit-adj-nominal').val() || '';
                const nominal = parseFloat(rawVal.replace(/\./g, '')) || 0;
                if (type === 'addition') {
                    totalAdj += nominal;
                } else {
                    totalAdj -= nominal;
                }
            });

            const grandTotal = editTotalSalaryFromOrders + totalAdj;

            $('#editSummaryTotalSalary').text('Rp ' + new Intl.NumberFormat('id-ID').format(editTotalSalaryFromOrders));
            const adjPrefix = totalAdj >= 0 ? '+' : '';
            $('#editSummaryTotalAdjustment').text(adjPrefix + 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.abs(totalAdj)));
            $('#editSummaryGrandTotal').text('Rp ' + new Intl.NumberFormat('id-ID').format(grandTotal));
        }

        function deleteSalary(id) {
            swal({
                title: "Hapus Gaji?",
                text: "Data gaji yang sudah diproses akan dihapus permanen.",
                icon: "warning",
                buttons: ["Batal", "Ya, Hapus!"],
                dangerMode: true,
            }).then(function(willDelete) {
                if (willDelete) {
                    const form = document.getElementById('deleteForm');
                    form.action = "{{ url('report/driver-salary') }}/" + id;
                    form.submit();
                }
            });
        }

        @if (in_array(Auth::user()->roleCode, ['SPRADMIN', 'SPRUSER']))
        $('#btn-sync-existing-status').click(function(e) {
            e.preventDefault();
            swal({
                title: "Sinkronisasi Status Gaji?",
                text: "Sistem akan mencocokkan seluruh rekap gaji supir yang sudah tersimpan dengan data komponen gaji order, lalu memperbarui statusnya menjadi '1' (Selesai/Sudah Digaji).",
                icon: "info",
                buttons: {
                    cancel: "Batal",
                    confirm: {
                        text: "Ya, Sinkronkan!",
                        closeModal: false
                    }
                },
            }).then((willSync) => {
                if (willSync) {
                    $.ajax({
                        url: "{{ route('report.driver-salary.sync-existing-status') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            swal("Berhasil!", response.message, "success").then(() => {
                                if ($.fn.DataTable.isDataTable('#dtProcessed')) {
                                    $('#dtProcessed').DataTable().ajax.reload(null, false);
                                }
                            });
                        },
                        error: function(xhr) {
                            let msg = "Terjadi kesalahan saat sinkronisasi status gaji.";
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            swal("Gagal!", msg, "error");
                        }
                    });
                }
            });
        });
        @endif
    </script>
@endpush
