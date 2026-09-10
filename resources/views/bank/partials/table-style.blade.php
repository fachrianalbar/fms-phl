{{-- Gaya bersama halaman se-modul Bank (kartu KPI, filter pills, kartu container
    tabel, sel ellipsis & styling DataTables).
    Di-include di dalam @push('style') oleh halaman se-modul bank.
    Sumber: diadaptasi dari direct-payment/partials/table-style.blade.php (gold standard
    resources/views/direct-payment/order/unpaid.blade.php). --}}

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

    /* Bank Table Styling */
    .bank-table {
        width: 100% !important;
        margin-bottom: 0 !important;
        border-collapse: collapse !important;
    }
    .bank-table thead th {
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
    .bank-table tbody td {
        padding: 10px 14px !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f1f5f9 !important;
        font-size: 12px;
        color: #334155;
        white-space: nowrap !important;
    }
    .bank-table tbody tr {
        transition: background-color 0.15s ease-in-out;
    }
    .bank-table tbody tr:hover {
        background-color: #f8fafc !important;
    }
    .bank-table tbody tr.table-active {
        background-color: #eff6ff !important;
    }

    /* Sel teks panjang: potong dengan ellipsis (dipakai render kolom server) */
    .cell-ellipsis {
        display: inline-block;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        vertical-align: middle;
    }

    /* Action buttons */
    .btn-icon {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        border: 1px solid transparent;
        padding: 0;
        transition: all 0.2s ease;
    }
    .hover-scale:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
    }

    /* Scrollbar halus untuk wrapper tabel */
    .custom-scrollbar::-webkit-scrollbar {
        height: 8px;
        width: 8px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f5f9;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 8px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
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
        font-size: 12.5px;
    }

    /* Select2 untuk dropdown "Tampilkan _MENU_ data" milik DataTables */
    .dataTables_length .select2-container { width: 88px !important; }
    .dataTables_length .select2-container .select2-selection--single {
        height: 34px !important;
        padding: 4px 10px !important;
        border-radius: 8px !important;
    }
    .dataTables_length .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 24px !important;
        font-size: 13px !important;
    }
    .dataTables_length .select2-container--default .select2-selection--single .select2-selection__arrow { height: 32px !important; }
</style>
