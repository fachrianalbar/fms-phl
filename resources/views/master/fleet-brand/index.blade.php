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
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/sweetalert2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom-select2.css') }}">

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

        /* Select2 Theme Matching Seragam 38px */
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

        /* ── DataTables Inputs & Pagination ── */
        #dt_wrapper .dataTables_filter input {
            border-radius: 8px;
            font-size: 12px;
        }

        #dt_wrapper .dataTables_length select {
            border-radius: 8px;
            font-size: 12px;
        }

        /* ── Modal CRUD Fleet Brand: top-center, compact, and polished ── */
        .fleet-brand-crud-modal .modal-dialog {
            max-width: 560px;
            margin: 6vh auto 1rem;
        }

        .fleet-brand-crud-modal .modal-content {
            overflow: hidden;
            border: 1px solid #dbe4ef;
            border-radius: 18px;
            box-shadow: 0 24px 64px rgba(15, 23, 42, 0.22);
        }

        .fleet-brand-crud-modal .modal-header {
            position: relative;
            overflow: hidden;
            padding: 20px 24px;
            border-bottom: 0;
            color: #fff;
            background: linear-gradient(135deg, #312e81 0%, #4f46e5 58%, #6366f1 100%);
        }

        .fleet-brand-crud-modal .modal-header::after {
            content: '';
            position: absolute;
            width: 180px;
            height: 180px;
            top: -110px;
            right: -45px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 50%;
            box-shadow: 0 0 0 18px rgba(255, 255, 255, 0.04), 0 0 0 36px rgba(255, 255, 255, 0.035);
        }

        .fleet-brand-crud-modal .modal-header > * {
            position: relative;
            z-index: 1;
        }

        .fleet-brand-crud-modal .modal-icon {
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border: 1px solid rgba(255, 255, 255, 0.28);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.14);
            font-size: 22px;
        }

        .fleet-brand-crud-modal .modal-eyebrow {
            margin-bottom: 3px;
            color: rgba(255, 255, 255, 0.72);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.1px;
            text-transform: uppercase;
        }

        .fleet-brand-crud-modal .modal-title {
            margin: 0;
            color: #fff;
            font-size: 18px;
            font-weight: 750;
        }

        .fleet-brand-crud-modal .modal-subtitle {
            margin-top: 4px;
            color: rgba(255, 255, 255, 0.78);
            font-size: 12px;
        }

        .fleet-brand-crud-modal .btn-close {
            position: relative;
            z-index: 2;
            opacity: 0.9;
            filter: brightness(0) invert(1);
        }

        .fleet-brand-crud-modal .modal-body {
            padding: 24px;
            background: #fff;
        }

        .fleet-brand-crud-modal .form-label {
            margin-bottom: 7px;
            color: #475569;
            font-size: 12px;
            font-weight: 700;
        }

        .fleet-brand-crud-modal .form-control {
            min-height: 42px;
            border: 1px solid #cbd5e1;
            border-radius: 9px;
            color: #1e293b;
            font-size: 13px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .fleet-brand-crud-modal .form-control:focus {
            border-color: #818cf8;
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.14);
        }

        .fleet-brand-crud-modal .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid #eef2f7;
            background: #f8fafc;
        }

        .fleet-brand-crud-modal .btn-modal-cancel,
        .fleet-brand-crud-modal .btn-modal-submit {
            min-height: 40px;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 700;
        }

        .fleet-brand-crud-modal .btn-modal-cancel {
            padding: 0 18px;
            border: 1px solid #cbd5e1;
            color: #475569;
            background: #fff;
        }

        .fleet-brand-crud-modal .btn-modal-submit {
            min-width: 124px;
            padding: 0 18px;
            border: 0;
            color: #fff;
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            box-shadow: 0 3px 8px rgba(79, 70, 229, 0.22);
        }

        .fleet-brand-crud-modal .btn-modal-submit:hover {
            background: linear-gradient(135deg, #4338ca 0%, #4f46e5 100%);
            transform: translateY(-1px);
        }

        @media (max-width: 575.98px) {
            .fleet-brand-crud-modal .modal-dialog {
                margin: 1rem;
            }

            .fleet-brand-crud-modal .modal-header,
            .fleet-brand-crud-modal .modal-body,
            .fleet-brand-crud-modal .modal-footer {
                padding-right: 18px;
                padding-left: 18px;
            }
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
                        <i class="mdi mdi-truck-flatbed text-primary fs-20"></i>
                        {{ $title }} Data
                    </h4>
                    <small class="text-muted">Daftar master merek armada (fleet brand) beserta jumlah armada terdaftar</small>
                </div>

                <div class="d-flex align-items-center gap-2">
                    {{-- Tombol Export Excel --}}
                    <a href="{{ route($view . 'export-excel') }}" target="_blank" id="export-excel"
                        class="btn btn-icon btn-sm bg-success-subtle" data-bs-toggle="tooltip" title="Export Excel">
                        <i class="mdi mdi-file-excel fs-14 text-success"></i>
                    </a>

                    {{-- Tombol Export PDF --}}
                    <a href="{{ route($view . 'export-pdf') }}" target="_blank" id="export-pdf"
                        class="btn btn-icon btn-sm bg-danger-subtle" data-bs-toggle="tooltip" title="Export PDF">
                        <i class="mdi mdi-file-pdf-box fs-14 text-danger"></i>
                    </a>

                    {{-- Tombol Tambah: buka modal CRUD reusable --}}
                    <button type="button" class="btn btn-sm btn-primary d-flex align-items-center gap-1"
                        data-bs-toggle="modal" data-bs-target="#fleetBrandCrudModal"
                        style="border-radius: 8px; font-weight: 600; padding: 7px 14px;">
                        <i class="mdi mdi-plus"></i> {{ __('general.add_data') }}
                    </button>
                </div>
            </div>

            <div class="card-body p-4">
                @include('partials.alert')

                {{-- Filter Bar (Selalu Terbuka & Ukuran Seragam Presisi 38px) --}}
                <div class="card border-0 mb-4"
                    style="background: #f8fafc; border: 1px solid #e2e8f0 !important; border-radius: 12px;">
                    <div class="card-body p-3">
                        <form id="filterForm">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-4">
                                    <label class="filter-label" for="brandCode">Merek Armada</label>
                                    <select class="form-select select2-filter" name="brandCode"
                                        id="brandCode" style="width: 100%;">
                                        <option value="">Semua Merek Armada</option>
                                        @foreach ($brands as $item)
                                            <option value="{{ $item->code }}">{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="filter-label" for="startDate">Dari Tanggal</label>
                                    <input class="form-control filter-control" name="startDate" id="startDate"
                                        type="text" placeholder="Pilih Tanggal Mulai">
                                </div>

                                <div class="col-md-3">
                                    <label class="filter-label" for="endDate">Sampai Tanggal</label>
                                    <input class="form-control filter-control" name="endDate" id="endDate"
                                        type="text" placeholder="Pilih Tanggal Akhir">
                                </div>

                                <div class="col-md-2 d-flex gap-2">
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

                {{-- Table (Tanpa striped, garis halus, header uppercase) --}}
                <div class="table-responsive custom-scrollbar">
                    <table class="table align-middle w-100 mb-0" id="dt">
                        <thead>
                            <tr>
                                <th style="width: 10%;" class="text-center">Aksi</th>
                                <th style="width: 6%;" class="text-center">No</th>
                                <th style="width: 20%;">Kode Merek</th>
                                <th style="width: 34%;">Nama Merek</th>
                                <th style="width: 15%;" class="text-center">Jumlah Armada</th>
                                <th style="width: 15%;" class="text-center">Tanggal Dibuat</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <form id="delete-form" method="post">
        @csrf
        @method('DELETE')
    </form>

    {{-- Modal CRUD: satu form untuk tambah dan edit --}}
    <div class="modal fade fleet-brand-crud-modal" id="fleetBrandCrudModal" tabindex="-1"
        aria-labelledby="fleetBrandCrudModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="fleetBrandCrudForm" method="POST" action="{{ route($view . 'store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="fleetBrandCrudMethod" value="POST">

                    <div class="modal-header">
                        <div class="d-flex align-items-center gap-3">
                            <div class="modal-icon" aria-hidden="true">
                                <i class="mdi mdi-truck-flatbed"></i>
                            </div>
                            <div>
                                <div class="modal-eyebrow">Master fleet</div>
                                <h5 class="modal-title" id="fleetBrandCrudModalLabel">Tambah Merek Armada</h5>
                                <div class="modal-subtitle" id="fleetBrandCrudModalSubtitle">Daftarkan merek armada baru ke master data.</div>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>

                    <div class="modal-body">
                        <label class="form-label" for="fleetBrandCrudName">Nama Merek Armada</label>
                        <input class="form-control" name="name" id="fleetBrandCrudName" type="text"
                            required maxlength="255" autocomplete="off" placeholder="Contoh: Toyota">
                        <div class="form-text mt-2">Gunakan nama merek yang konsisten agar mudah dicari dan dilaporkan.</div>
                    </div>

                    <div class="modal-footer d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-modal-cancel" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-modal-submit" id="fleetBrandCrudSubmit">
                            <i class="mdi mdi-content-save-outline me-1"></i><span>Simpan Merek</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
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

    <!-- Select2 & Flatpickr -->
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2-custom.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/custom-flatpickr.js') }}"></script>

    <script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>

    <script>
        let filterStartPicker;
        let filterEndPicker;

        $(document).ready(function() {
            // 1. Inisialisasi Select2
            $('#brandCode').select2({
                placeholder: 'Semua Merek Armada',
                allowClear: true
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

            // 3. Sinkronisasi parameter filter ke tombol Export Excel & PDF
            function syncExportUrls() {
                const params = new URLSearchParams();
                const brandCode = $('#brandCode').val();
                const startDate = $('#startDate').val();
                const endDate = $('#endDate').val();

                if (brandCode) params.set('brandCode', brandCode);
                if (startDate) params.set('startDate', startDate);
                if (endDate) params.set('endDate', endDate);

                const qs = params.toString() ? '?' + params.toString() : '';
                $('#export-excel').attr('href', "{{ route($view . 'export-excel') }}" + qs);
                $('#export-pdf').attr('href', "{{ route($view . 'export-pdf') }}" + qs);
            }

            // 4. Inisialisasi DataTables
            const table = $('#dt').DataTable({
                processing: true,
                serverSide: true,
                destroy: true,
                pageLength: 25,
                ajax: {
                    url: "{{ route('dt.fleet-brand') }}",
                    data: function(d) {
                        d.brandCode = $('#brandCode').val();
                        d.startDate = $('#startDate').val();
                        d.endDate = $('#endDate').val();
                    }
                },
                columns: [
                    { data: 'action', className: 'text-center align-middle' },
                    { data: 'DT_RowIndex', className: 'text-center align-middle' },
                    { data: 'code', className: 'align-middle font-monospace fw-semibold text-dark' },
                    { data: 'name', className: 'align-middle fw-semibold text-dark' },
                    { data: 'fleets_count', className: 'text-center align-middle font-monospace' },
                    { data: 'created_at', className: 'text-center align-middle font-monospace' }
                ],
                columnDefs: [
                    { searchable: false, targets: [0, 1, 4, 5] },
                    { orderable: false, targets: [0, 1] }
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
                $('#brandCode').val('').trigger('change');
                if (filterStartPicker) filterStartPicker.clear();
                if (filterEndPicker) filterEndPicker.clear();
                syncExportUrls();
                table.ajax.reload();
            });

            // 7. Reset modal ke mode tambah saat dibuka dari tombol tambah
            $('#fleetBrandCrudModal').on('show.bs.modal', function(event) {
                const trigger = $(event.relatedTarget);
                const isEdit = trigger.hasClass('js-edit-fleet-brand');
                const form = $('#fleetBrandCrudForm');
                const nameInput = $('#fleetBrandCrudName');
                const methodInput = $('#fleetBrandCrudMethod');
                const title = $('#fleetBrandCrudModalLabel');
                const subtitle = $('#fleetBrandCrudModalSubtitle');
                const submitText = $('#fleetBrandCrudSubmit span');

                if (isEdit) {
                    form.attr('action', trigger.data('action'));
                    methodInput.val('PUT');
                    nameInput.val(trigger.data('name'));
                    title.text('Edit Merek Armada');
                    subtitle.text('Perbarui identitas merek armada pada master data.');
                    submitText.text('Simpan Perubahan');
                } else {
                    form.attr('action', "{{ route($view . 'store') }}");
                    methodInput.val('POST');
                    nameInput.val('');
                    title.text('Tambah Merek Armada');
                    subtitle.text('Daftarkan merek armada baru ke master data.');
                    submitText.text('Simpan Merek');
                }
            });

            $('#fleetBrandCrudModal').on('shown.bs.modal', function() {
                $('#fleetBrandCrudName').trigger('focus');
            });

            // 8. Cegah submit ganda saat penyimpanan berlangsung
            $('#fleetBrandCrudForm').on('submit', function() {
                const submitButton = $('#fleetBrandCrudSubmit');
                submitButton.prop('disabled', true).find('span').text('Menyimpan...');
            });
        });

        // 9. Modal Konfirmasi Hapus Data
        function deleteData(uuid) {
            var url = '{{ route($view . 'index') }}/' + uuid;
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
