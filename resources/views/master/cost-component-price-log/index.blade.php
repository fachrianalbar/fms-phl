@extends('layouts.main', [
'title' => $title,
'pageTitle' => $title,
'firstSegment' => 'Master',
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
<link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/sweetalert2.css') }} ">
<style>
    /* ── Scoped Table Styling ── */
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

    /* ── Tombol Icon Ramping Header / Aksi Tabel ── */
    .btn-icon {
        border-radius: 8px !important;
        padding: 6px 10px;
        font-size: 13px;
        transition: all 0.2s ease;
    }

    .btn-icon:hover {
        transform: translateY(-1px);
    }

    /* ── DataTables Inputs & Pagination ── */
    #dt_wrapper .dataTables_filter input {
        border-radius: 8px;
        font-size: 12px;
    }

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

    .export-loader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .export-loader-content {
        background: white;
        padding: 40px 60px;
        border-radius: 12px;
        text-align: center;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    }

    .export-loader-spinner {
        width: 60px;
        height: 60px;
        border: 5px solid #f3f3f3;
        border-top: 5px solid #28a745;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 20px;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    .export-loader-text {
        font-size: 18px;
        color: #333;
        font-weight: 500;
    }

    .export-loader-subtext {
        font-size: 14px;
        color: #666;
        margin-top: 8px;
    }

    .export-success {
        display: none;
        background: #d4edda;
        color: #155724;
        padding: 15px;
        border-radius: 8px;
        margin-top: 20px;
        border: 1px solid #c3e6cb;
        text-align: center;
        font-weight: 500;
    }

    /* ── Dark mode ─────────────────────────────── */
    html[data-bs-theme="dark"] .card-header {
        background-color: var(--bs-card-bg) !important;
        border-color: var(--bs-border-color) !important;
    }

    html[data-bs-theme="dark"] #dt {
        border-color: var(--bs-border-color);
    }

    html[data-bs-theme="dark"] #dt thead th {
        background-color: var(--bs-tertiary-bg) !important;
        color: var(--bs-emphasis-color) !important;
        border-color: var(--bs-border-color) !important;
    }

    html[data-bs-theme="dark"] #dt tbody td {
        color: var(--bs-body-color) !important;
        border-color: var(--bs-border-color) !important;
    }

    html[data-bs-theme="dark"] #dt tbody tr:hover {
        background-color: var(--bs-tertiary-bg) !important;
    }

    html[data-bs-theme="dark"] .export-loader-content {
        background-color: var(--bs-card-bg) !important;
    }

    html[data-bs-theme="dark"] .export-loader-text {
        color: var(--bs-body-color) !important;
    }

    html[data-bs-theme="dark"] .export-loader-subtext {
        color: var(--bs-secondary-color) !important;
    }

    html[data-bs-theme="dark"] .export-success {
        background-color: var(--bs-success-bg-subtle) !important;
        color: var(--bs-success-text-emphasis) !important;
        border-color: var(--bs-success-border-subtle) !important;
    }
</style>
@endpush

@section('content')
<!-- Export Loader -->
<div class="export-loader" id="exportLoader">
    <div class="export-loader-content">
        <div class="export-loader-spinner"></div>
        <div class="export-loader-text">Exporting Data...</div>
        <div class="export-loader-subtext">Please wait while we prepare your Excel file</div>
    </div>
</div>

<div class="col-sm-12">
    <div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
        {{-- Card Header --}}
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center"
            style="border-color: #e2e8f0;">
            <div>
                <h4 class="mb-1 fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="mdi mdi-history text-primary fs-20"></i>
                    {{ $title }} Data
                </h4>
                <small class="text-muted">Riwayat dan log perubahan harga komponen biaya armada</small>
            </div>

            <div class="d-flex align-items-center gap-2">
                <a href="javascript:void(0)" onclick="exportExcel()" class="btn btn-sm btn-success d-inline-flex align-items-center gap-1"
                    id="btn-export" style="border-radius: 8px; font-weight: 600; padding: 7px 14px;">
                    <i class="mdi mdi-file-excel"></i> Export Excel
                </a>
            </div>
        </div>

        <div class="card-body p-4">
            @include('partials.alert')
            <div id="exportSuccessMessage" class="export-success">
                <i class="mdi mdi-check-circle"></i> Export completed successfully! Your Excel file has been downloaded.
            </div>
            <div class="table-responsive custom-scrollbar">
                <table class="table align-middle w-100 mb-0" id="dt">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 60px;">No</th>
                            <th class="text-center">Tanggal</th>
                            <th>Kode Komponen</th>
                            <th>Nama Komponen</th>
                            <th class="text-end">Harga Lama</th>
                            <th class="text-end">Harga Baru</th>
                            <th>Diubah Oleh</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>

<!-- dataTables.bootstrap5 -->
<script src="{{ asset('assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>

<!-- dataTables.keyTable -->
<script src="{{ asset('assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-keytable-bs5/js/keyTable.bootstrap5.min.js') }}"></script>

<!-- dataTable.responsive -->
<script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>

<!-- dataTables.select -->
<script src="{{ asset('assets/libs/datatables.net-select/js/dataTables.select.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-select-bs5/js/select.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>

<script>
    $(document).ready(function() {
        $('#dt').DataTable({
            "processing": true,
            "serverSide": true,
            "destroy": true,
            "ajax": {
                "url": "{{ route('dt.cost-component-price-log') }}",
            },
            "columns": [{
                    "data": 'DT_RowIndex',
                    "className": 'text-center align-middle',
                },
                {
                    "data": 'formatted_date',
                    "className": 'text-center align-middle font-monospace',
                },
                {
                    "data": 'costComponentCode',
                    "className": 'align-middle font-monospace fw-semibold',
                },
                {
                    "data": 'costComponentName',
                    "className": 'align-middle text-dark',
                },
                {
                    "data": 'formatted_old_price',
                    "className": 'text-end align-middle font-monospace',
                },
                {
                    "data": 'formatted_new_price',
                    "className": 'text-end align-middle font-monospace fw-bold text-dark',
                },
                {
                    "data": 'changedBy',
                    "className": 'align-middle',
                },
            ],
            "columnDefs": [{
                    "searchable": false,
                    "targets": [0]
                },
                {
                    "orderable": false,
                    "targets": [0]
                }
            ],
            "order": [
                [1, 'desc']
            ]
        })
    });

    function exportExcel() {
        // Hide any existing success message
        document.getElementById('exportSuccessMessage').style.display = 'none';

        // Show loader
        document.getElementById('exportLoader').style.display = 'flex';

        // Create a hidden iframe to handle the download
        var iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        iframe.src = "{{ route('master.cost-component-price-log.export-excel') }}";
        document.body.appendChild(iframe);

        // Hide loader and show success message after download starts
        setTimeout(function() {
            document.getElementById('exportLoader').style.display = 'none';
            document.getElementById('exportSuccessMessage').style.display = 'block';
            document.body.removeChild(iframe);

            // Hide success message after 5 seconds
            setTimeout(function() {
                document.getElementById('exportSuccessMessage').style.display = 'none';
            }, 5000);
        }, 3000);
    }
</script>
@endpush