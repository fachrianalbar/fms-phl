@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Inventory',
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
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom-select2.css') }}">
    <link rel="stylesheet" type="text/css" href="../assets/css/vendors/sweetalert2.css">

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

        /* ── Standarisasi Seragam Filter Bar (Tinggi Presisi: 38px) ── */
        .filter-label {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 6px;
            display: block;
        }

        /* Input Tanggal Flatpickr dengan Ikon Kalender Elegan */
        .filter-control {
            height: 38px !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            font-size: 13px !important;
            color: #334155 !important;
            background-color: #ffffff !important;
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
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%234f46e5' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='4' width='18' height='18' rx='2' ry='2'%3E%3C/rect%3E%3Cline x1='16' y1='2' x2='16' y2='6'%3E%3C/line%3E%3Cline x1='8' y1='2' x2='8' y2='6'%3E%3C/line%3E%3Cline x1='3' y1='10' x2='21' y2='10'%3E%3C/line%3E%3C/svg%3E");
        }

        /* Tombol Filter Utama (Gradient Modern) */
        .btn-filter-primary {
            height: 38px !important;
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%) !important;
            color: #ffffff !important;
            border: none !important;
            border-radius: 8px !important;
            font-weight: 600 !important;
            font-size: 13px !important;
            padding: 0 16px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
            box-shadow: 0 2px 6px rgba(79, 70, 229, 0.25) !important;
            transition: all 0.2s ease !important;
            white-space: nowrap !important;
        }

        .btn-filter-primary:hover {
            background: linear-gradient(135deg, #4338ca 0%, #4f46e5 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.35) !important;
            transform: translateY(-1px) !important;
        }

        .btn-filter-primary:active {
            transform: translateY(0) !important;
            box-shadow: 0 1px 3px rgba(79, 70, 229, 0.2) !important;
        }

        /* Tombol Reset Filter Presisi Kotak 38px x 38px */
        .btn-filter-reset {
            height: 38px !important;
            width: 38px !important;
            min-width: 38px !important;
            padding: 0 !important;
            background: #ffffff !important;
            color: #64748b !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            transition: all 0.2s ease !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
        }

        .btn-filter-reset:hover {
            background: #fff1f2 !important;
            color: #e11d48 !important;
            border-color: #fecdd3 !important;
            box-shadow: 0 2px 6px rgba(225, 29, 72, 0.15) !important;
            transform: translateY(-1px) !important;
        }

        .btn-filter-reset:hover i {
            transform: rotate(-45deg);
            transition: transform 0.2s ease;
        }

        .btn-filter-reset:active {
            transform: translateY(0) !important;
        }

        /* ── Select2 Theme Matching Seragam 38px ── */
        .select2-container {
            width: 100% !important;
        }

        .select2-container--default .select2-selection--single {
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            height: 38px !important;
            display: flex !important;
            align-items: center !important;
            background-color: #ffffff !important;
            transition: all 0.2s ease !important;
        }

        .select2-container--default .select2-selection--single:hover {
            border-color: #94a3b8 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #334155 !important;
            font-size: 13px !important;
            line-height: 36px !important;
            padding-left: 12px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #94a3b8 !important;
            font-size: 13px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
            right: 10px !important;
            top: 1px !important;
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

        /* ── Modal CRUD Terstandarisasi (Top-Center) ── */
        .entity-crud-modal .modal-dialog {
            max-width: 560px;
            margin: 6vh auto 1rem;
        }

        .entity-crud-modal .modal-content {
            overflow: hidden;
            border: 1px solid #dbe4ef;
            border-radius: 18px;
            box-shadow: 0 24px 64px rgba(15, 23, 42, 0.22);
        }

        .entity-crud-modal .entity-crud-header {
            position: relative;
            overflow: hidden;
            padding: 20px 24px;
            color: #fff;
            border-bottom: 0;
            background: linear-gradient(135deg, #312e81 0%, #4f46e5 58%, #6366f1 100%);
        }

        .entity-crud-modal .entity-crud-header::after {
            content: '';
            position: absolute;
            right: -60px;
            top: -60px;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.12);
        }

        .entity-crud-modal .entity-crud-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #fff;
            background: rgba(255, 255, 255, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.25);
            flex-shrink: 0;
        }

        .entity-crud-modal .entity-crud-eyebrow {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            opacity: 0.75;
            font-weight: 600;
        }

        .entity-crud-modal .modal-title {
            font-size: 17px;
            font-weight: 700;
            color: #fff !important;
            margin: 2px 0 0;
        }

        .entity-crud-modal .entity-crud-subtitle {
            font-size: 12.5px;
            opacity: 0.85;
        }

        .entity-crud-modal .modal-body {
            padding: 24px;
        }

        .entity-crud-modal .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid #eef2f7;
            background: #f8fafc;
        }

        .entity-crud-modal .form-label {
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 6px;
        }

        .entity-crud-modal .form-control {
            min-height: 42px;
            border: 1px solid #cbd5e1;
            border-radius: 9px;
            font-size: 13.5px;
        }

        .entity-crud-modal .form-control:focus {
            border-color: #818cf8;
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.15);
        }

        .entity-crud-modal .entity-crud-cancel {
            border: 1px solid #cbd5e1;
            border-radius: 9px;
            font-weight: 600;
            font-size: 13px;
            padding: 9px 18px;
            color: #475569;
            background: #fff;
        }

        .entity-crud-modal .entity-crud-submit {
            border: none;
            border-radius: 9px;
            font-weight: 600;
            font-size: 13px;
            padding: 9px 22px;
            color: #fff;
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            box-shadow: 0 2px 6px rgba(79, 70, 229, 0.25);
        }

        @media (max-width: 576px) {
            .entity-crud-modal .modal-dialog {
                margin: 1rem;
                max-width: calc(100% - 2rem);
            }

            .entity-crud-modal .modal-body {
                padding: 18px;
            }
        }

        /* ── Dark mode ─────────────────────────────── */
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

        /* Filter bar */
        html[data-bs-theme="dark"] .filter-label {
            color: var(--bs-secondary-color);
        }
        html[data-bs-theme="dark"] .filter-control {
            background-color: var(--bs-secondary-bg) !important;
            border-color: var(--bs-border-color) !important;
            color: var(--bs-body-color) !important;
        }
        html[data-bs-theme="dark"] .btn-filter-reset {
            background: var(--bs-tertiary-bg) !important;
            border-color: var(--bs-border-color) !important;
            color: var(--bs-body-color) !important;
        }
        html[data-bs-theme="dark"] .card.border-0.mb-4 {
            background-color: var(--bs-tertiary-bg) !important;
            border-color: var(--bs-border-color) !important;
        }

        /* CRUD modal */
        html[data-bs-theme="dark"] .entity-crud-modal .modal-content {
            border-color: var(--bs-border-color);
        }
        html[data-bs-theme="dark"] .entity-crud-modal .modal-footer {
            background: var(--bs-secondary-bg);
            border-top-color: var(--bs-border-color);
        }
        html[data-bs-theme="dark"] .entity-crud-modal .form-label {
            color: var(--bs-body-color);
        }
        html[data-bs-theme="dark"] .entity-crud-modal .form-control {
            background-color: var(--bs-secondary-bg);
            border-color: var(--bs-border-color);
            color: var(--bs-body-color);
        }
        html[data-bs-theme="dark"] .entity-crud-modal .entity-crud-cancel {
            background: var(--bs-tertiary-bg);
            border-color: var(--bs-border-color);
            color: var(--bs-body-color);
        }
    </style>
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
            {{-- Card Header --}}
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center"
                style="border-color: #e2e8f0;">
                <div>
                    <h4 class="mb-1 fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="mdi mdi-storefront-outline text-primary fs-20"></i>
                        {{ $title }} Data
                    </h4>
                    <small class="text-muted">Kelola data master supplier beserta kontak, alamat, dan komponen pajak</small>
                </div>

                <div class="d-flex align-items-center gap-2">
                    {{-- Tombol Export Excel --}}
                    <a href="{{ route('inventory.supplier.export-excel') }}" target="_blank" id="export-excel"
                        class="btn btn-icon btn-sm bg-success-subtle" data-bs-toggle="tooltip" title="Export Excel">
                        <i class="mdi mdi-file-excel fs-14 text-success"></i>
                    </a>

                    {{-- Tombol Export PDF --}}
                    <a href="{{ route('inventory.supplier.export-pdf') }}" target="_blank" id="export-pdf"
                        class="btn btn-icon btn-sm bg-danger-subtle" data-bs-toggle="tooltip" title="Export PDF">
                        <i class="mdi mdi-file-pdf-box fs-14 text-danger"></i>
                    </a>

                    {{-- Tombol Tambah via Modal CRUD --}}
                    <button type="button" class="btn btn-filter-primary" data-bs-toggle="modal"
                        data-bs-target="#supplierCrudModal">
                        <i class="mdi mdi-plus fs-14"></i> {{ __('general.add_data') }}
                    </button>
                </div>
            </div>

            <div class="card-body p-4">
                @include('partials.alert')

                {{-- Filter Bar Terbuka --}}
                <div class="card border-0 mb-4"
                    style="background: var(--bs-tertiary-bg); border: 1px solid var(--bs-border-color) !important; border-radius: 12px;">
                    <div class="card-body p-3">
                        <form id="filterForm">
                            <div class="row g-3 align-items-end">
                                <div class="col-xl-4 col-md-6">
                                    <label class="filter-label" for="supplierCode">Supplier</label>
                                    <select class="form-select select2-filter" name="supplierCode" id="supplierCode">
                                        <option value="">Semua Supplier</option>
                                        @foreach ($suppliers as $item)
                                            <option value="{{ $item->code }}">
                                                {{ $item->code }} - {{ $item->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-xl-3 col-md-6">
                                    <label class="filter-label" for="startDate">Dari Tanggal</label>
                                    <input class="form-control filter-control" name="startDate" id="startDate"
                                        type="text" placeholder="Pilih Tanggal Mulai" autocomplete="off">
                                </div>

                                <div class="col-xl-3 col-md-6">
                                    <label class="filter-label" for="endDate">Sampai Tanggal</label>
                                    <input class="form-control filter-control" name="endDate" id="endDate"
                                        type="text" placeholder="Pilih Tanggal Akhir" autocomplete="off">
                                </div>

                                <div class="col-xl-2 col-md-6 d-flex gap-2">
                                    <button class="btn btn-filter-primary flex-grow-1" type="submit" id="btnFilter">
                                        <i class="mdi mdi-filter fs-14"></i> Filter
                                    </button>
                                    <button type="button" class="btn btn-filter-reset" id="btnResetFilter"
                                        data-bs-toggle="tooltip" title="Reset Filter">
                                        <i class="mdi mdi-refresh fs-16"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Table --}}
                <div class="table-responsive custom-scrollbar">
                    <table class="table align-middle w-100 mb-0" id="dt">
                        <thead>
                            <tr>
                                <th style="width: 6%;" class="text-center">Aksi</th>
                                <th style="width: 4%;" class="text-center">No</th>
                                <th style="width: 9%;">Kode</th>
                                <th style="width: 17%;">Nama</th>
                                <th style="width: 18%;">Alamat</th>
                                <th style="width: 10%;">PIC</th>
                                <th style="width: 10%;">Telepon</th>
                                <th style="width: 14%;">Email</th>
                                <th style="width: 6%;" class="text-end">PPN</th>
                                <th style="width: 6%;" class="text-end">PPH</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal CRUD Terstandarisasi (Top-Center) --}}
    <div class="modal fade entity-crud-modal" id="supplierCrudModal" tabindex="-1"
        aria-labelledby="supplierCrudModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="supplierCrudForm" method="POST" action="{{ route($view . 'store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="supplierCrudMethod" value="POST">

                    <div class="modal-header entity-crud-header">
                        <div class="d-flex align-items-center gap-3">
                            <div class="entity-crud-icon"><i class="mdi mdi-storefront-edit-outline"></i></div>
                            <div>
                                <div class="entity-crud-eyebrow">Master data</div>
                                <h5 class="modal-title" id="supplierCrudModalLabel">Tambah Supplier</h5>
                                <div class="entity-crud-subtitle" id="supplierCrudSubtitle">Tambahkan data supplier baru.
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Tutup"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="supplierCodeInput">Kode <span
                                        class="text-danger">*</span></label>
                                <input class="form-control" name="code" id="supplierCodeInput" type="text" required
                                    maxlength="30" placeholder="cth: SUP-01" autocomplete="off">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="supplierNameInput">Nama <span
                                        class="text-danger">*</span></label>
                                <input class="form-control" name="name" id="supplierNameInput" type="text" required
                                    maxlength="100" placeholder="Nama supplier" autocomplete="off">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="supplierPicInput">PIC</label>
                                <input class="form-control" name="pic" id="supplierPicInput" type="text"
                                    maxlength="100" placeholder="Nama PIC" autocomplete="off">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="supplierPhoneInput">Telepon</label>
                                <input class="form-control" name="phone" id="supplierPhoneInput" type="text"
                                    maxlength="30" placeholder="Nomor telepon" autocomplete="off">
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="supplierAddressInput">Alamat</label>
                                <input class="form-control" name="address" id="supplierAddressInput" type="text"
                                    maxlength="255" placeholder="Alamat supplier" autocomplete="off">
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="supplierEmailInput">Email</label>
                                <input class="form-control" name="email" id="supplierEmailInput" type="email"
                                    maxlength="100" placeholder="email@supplier.com" autocomplete="off">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="supplierPpnInput">PPN</label>
                                <input class="form-control" name="ppn" id="supplierPpnInput" type="number"
                                    step="0.01" min="0" placeholder="cth: 11">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="supplierPphInput">PPH</label>
                                <input class="form-control" name="pph" id="supplierPphInput" type="number"
                                    step="0.01" min="0" placeholder="cth: 2">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn entity-crud-cancel"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn entity-crud-submit" id="supplierCrudSubmit">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <form id="delete-form" method="post">
        @csrf
        @method('DELETE')
    </form>
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
    <script src="../assets/js/sweet-alert/sweetalert.min.js"></script>
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/custom-flatpickr.js') }}"></script>

    <script>
        let filterStartPicker;
        let filterEndPicker;

        $(document).ready(function() {
            // 1. Inisialisasi Select2 filter
            $('#supplierCode').select2({
                placeholder: 'Semua Supplier',
                allowClear: true,
                width: '100%'
            });

            // 2. Inisialisasi Flatpickr (format standar backend Y-m-d)
            filterStartPicker = flatpickr('#startDate', {
                dateFormat: 'Y-m-d',
                allowInput: true,
                onChange: function(selectedDates, dateStr) {
                    if (filterEndPicker) filterEndPicker.set('minDate', dateStr || null);
                }
            });

            filterEndPicker = flatpickr('#endDate', {
                dateFormat: 'Y-m-d',
                allowInput: true,
                onChange: function(selectedDates, dateStr) {
                    if (filterStartPicker) filterStartPicker.set('maxDate', dateStr || null);
                }
            });

            // 3. Sinkronisasi parameter filter ke tombol Export (Excel & PDF)
            function syncExportUrls() {
                const params = new URLSearchParams();
                const supplierCode = $('#supplierCode').val();
                const startDate = $('#startDate').val();
                const endDate = $('#endDate').val();

                if (supplierCode) params.set('supplierCode', supplierCode);
                if (startDate) params.set('startDate', startDate);
                if (endDate) params.set('endDate', endDate);

                const qs = params.toString() ? '?' + params.toString() : '';
                $('#export-pdf').attr('href', "{{ route('inventory.supplier.export-pdf') }}" + qs);
                $('#export-excel').attr('href', "{{ route('inventory.supplier.export-excel') }}" + qs);
            }

            // 4. Inisialisasi DataTables (tanpa striped)
            const table = $('#dt').DataTable({
                processing: true,
                serverSide: true,
                destroy: true,
                pageLength: 25,
                ajax: {
                    url: "{{ route('dt.supplier') }}",
                    data: function(d) {
                        d.supplierCode = $('#supplierCode').val();
                        d.startDate = $('#startDate').val();
                        d.endDate = $('#endDate').val();
                    }
                },
                columns: [{
                        data: 'action',
                        className: 'text-center align-middle'
                    },
                    {
                        data: 'DT_RowIndex',
                        className: 'text-center align-middle'
                    },
                    {
                        data: 'code',
                        className: 'align-middle font-monospace fw-semibold text-dark'
                    },
                    {
                        data: 'name',
                        className: 'align-middle'
                    },
                    {
                        data: 'address',
                        className: 'align-middle'
                    },
                    {
                        data: 'pic',
                        className: 'align-middle'
                    },
                    {
                        data: 'phone',
                        className: 'align-middle font-monospace'
                    },
                    {
                        data: 'email',
                        className: 'align-middle'
                    },
                    {
                        data: 'ppn',
                        className: 'text-end align-middle font-monospace'
                    },
                    {
                        data: 'pph',
                        className: 'text-end align-middle font-monospace'
                    }
                ],
                columnDefs: [{
                        searchable: false,
                        targets: [0, 1, 8, 9]
                    },
                    {
                        orderable: false,
                        targets: [0, 1]
                    }
                ],
                order: [
                    [2, 'asc']
                ],
                language: {
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                    infoEmpty: 'Tidak ada data',
                    zeroRecords: 'Data tidak ditemukan',
                    processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Memuat data...'
                }
            });

            // 5. Submit Filter
            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                syncExportUrls();
                table.ajax.reload();
            });

            // 6. Reset Filter
            $('#btnResetFilter').on('click', function() {
                $('#filterForm')[0].reset();
                $('#supplierCode').val('').trigger('change');
                if (filterStartPicker) {
                    filterStartPicker.clear();
                    filterStartPicker.set('maxDate', null);
                }
                if (filterEndPicker) {
                    filterEndPicker.clear();
                    filterEndPicker.set('minDate', null);
                }
                syncExportUrls();
                table.ajax.reload();
            });

            syncExportUrls();

            // 7. Modal CRUD: isi field saat Tambah / Edit
            $('#supplierCrudModal').on('show.bs.modal', function(event) {
                const trigger = $(event.relatedTarget);
                const isEdit = trigger.hasClass('js-edit-supplier');
                const form = $('#supplierCrudForm');

                form.attr('action', isEdit ? trigger.data('action') :
                    "{{ route($view . 'store') }}");
                $('#supplierCrudMethod').val(isEdit ? 'PUT' : 'POST');
                $('#supplierCrudModalLabel').text(isEdit ? 'Edit Supplier' : 'Tambah Supplier');
                $('#supplierCrudSubtitle').text(isEdit ?
                    'Perbarui data supplier yang dipilih.' :
                    'Tambahkan data supplier baru.');

                $('#supplierCodeInput').val(isEdit ? (trigger.data('code') ?? '') : '');
                $('#supplierNameInput').val(isEdit ? (trigger.data('name') ?? '') : '');
                $('#supplierPicInput').val(isEdit ? (trigger.data('pic') ?? '') : '');
                $('#supplierAddressInput').val(isEdit ? (trigger.data('address') ?? '') :
                    '');
                $('#supplierEmailInput').val(isEdit ? (trigger.data('email') ?? '') : '');
                $('#supplierPhoneInput').val(isEdit ? (trigger.data('phone') ?? '') : '');
                $('#supplierPpnInput').val(isEdit ? (trigger.data('ppn') ?? '') : '');
                $('#supplierPphInput').val(isEdit ? (trigger.data('pph') ?? '') : '');

                $('#supplierCrudSubmit').prop('disabled', false).text('Simpan');
            });

            // Fokuskan field utama saat modal terbuka
            $('#supplierCrudModal').on('shown.bs.modal', function() {
                $('#supplierCodeInput').trigger('focus');
            });

            // Cegah duplikasi submit
            $('#supplierCrudForm').on('submit', function() {
                $('#supplierCrudSubmit').prop('disabled', true).text('Menyimpan...');
            });
        });

        function deleteData(uuid) {
            var url = '{{ route('inventory.supplier.index') }}/' + uuid;
            $('#delete-form').attr('action', url);

            swal({
                title: "{{ __('general.are_you_sure') }}",
                text: "{{ __('general.want_to_delete_this_data') }}",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    $('#delete-form').submit();
                } else {
                    swal("{{ __('general.your_data_is_save') }}");
                }
            });
        }
    </script>
@endpush
