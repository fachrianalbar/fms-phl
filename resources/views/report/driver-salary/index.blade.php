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

        /* ── Modal premium styling ── */
        #processSalaryModal .modal-content {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0,0,0,.15);
        }
        #processSalaryModal .modal-header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #a855f7 100%);
            border-bottom: none;
            padding: 20px 24px;
            position: relative;
        }
        #processSalaryModal .modal-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #fbbf24, #f59e0b, #fbbf24);
        }
        #processSalaryModal .modal-title {
            font-weight: 700;
            font-size: 18px;
            letter-spacing: -0.3px;
        }
        #processSalaryModal .modal-body {
            padding: 24px;
            background: #f8fafc;
            max-height: calc(100vh - 210px);
            overflow-y: auto !important;
        }

        /* ── Custom elegant scrollbar for modal body ── */
        #processSalaryModal .modal-body::-webkit-scrollbar {
            width: 8px;
        }
        #processSalaryModal .modal-body::-webkit-scrollbar-track {
            background: #f8fafc;
        }
        #processSalaryModal .modal-body::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        #processSalaryModal .modal-body::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        #processSalaryModal .modal-footer {
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

        /* ── Order preview table inside modal ── */
        #orderPreviewTable {
            font-size: 13px;
        }
        #orderPreviewTable thead th {
            background: #f1f5f9;
            border-bottom: 2px solid #cbd5e1;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #475569;
            padding: 10px 12px;
        }
        #orderPreviewTable tbody tr {
            transition: background-color 0.15s ease;
        }
        #orderPreviewTable tbody tr:hover {
            background-color: #eef2ff;
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
        .btn-remove-adj {
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
        }
        .btn-remove-adj:hover {
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
            transition: transform 0.2s ease;
        }
        .summary-card:hover {
            transform: translateY(-2px);
        }
        .summary-card h5 {
            font-size: 12px;
            margin-bottom: 6px;
            opacity: 0.75;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        .summary-card h3 {
            font-size: 20px;
            font-weight: 800;
            margin: 0;
            letter-spacing: -0.5px;
        }
        .summary-salary { background: linear-gradient(135deg, #eef2ff, #e0e7ff); border-color: #c7d2fe; color: #3730a3; }
        .summary-adj    { background: linear-gradient(135deg, #ecfdf5, #d1fae5); border-color: #a7f3d0; color: #065f46; }
        .summary-grand  { background: linear-gradient(135deg, #fefce8, #fef9c3); border-color: #fde68a; color: #92400e; }

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
        #processSalaryModal .input-group {
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        #processSalaryModal .input-group:focus-within {
            box-shadow: 0 0 0 0.25rem rgba(79, 70, 229, 0.15) !important;
        }
        #processSalaryModal .input-group:focus-within .input-group-text,
        #processSalaryModal .input-group:focus-within input.form-control,
        #processSalaryModal .input-group:focus-within .select2-container--default .select2-selection--single {
            border-color: #818cf8 !important;
        }
        #processSalaryModal .input-group .input-group-text {
            border-color: #cbd5e1;
            transition: all 0.2s ease;
        }
        #processSalaryModal .input-group input.form-control {
            border-color: #cbd5e1;
            transition: all 0.2s ease;
        }
        #processSalaryModal .input-group input.form-control:focus {
            box-shadow: none !important;
        }

        /* ── Select2 inside input-group styling overrides ── */
        #processSalaryModal .input-group .select2-wrapper {
            flex: 1 1 auto;
            width: 1%;
            display: flex;
        }
        #processSalaryModal .input-group .select2-wrapper .select2-container {
            width: 100% !important;
        }
        #processSalaryModal .input-group .select2-wrapper .select2-selection--single {
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
        #processSalaryModal .input-group .select2-wrapper .select2-selection--single .select2-selection__rendered {
            color: #334155 !important;
            padding: 0 !important;
            font-size: 14px !important;
            line-height: normal !important;
        }
        #processSalaryModal .input-group .select2-wrapper .select2-selection--single .select2-selection__placeholder {
            color: #94a3b8 !important;
        }
        #processSalaryModal .input-group .select2-wrapper .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
            right: 8px !important;
        }
    </style>
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">{{ $title }} Data</h4>

                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-process" data-bs-toggle="modal" data-bs-target="#processSalaryModal">
                        <i class="mdi mdi-cash-plus me-1"></i> Proses Gaji
                    </button>
                </div>
            </div>

            <div class="card-body">
                @include('partials.alert')

                {{-- Main datatable: Processed salaries --}}
                <div class="table-responsive custom-scrollbar">
                    <table class="table table-bordered table-striped w-100" id="dtProcessed">
                        <thead>
                            <tr>
                                <th style="width: 4%;">No</th>
                                <th style="width: 11%;">Kode</th>
                                <th style="width: 17%;">Nama Supir</th>
                                <th style="width: 10%;">Dari</th>
                                <th style="width: 10%;">Sampai</th>
                                <th style="width: 14%;">Total Gaji</th>
                                <th style="width: 12%;">Penyesuaian</th>
                                <th style="width: 14%;">Grand Total</th>
                                <th style="width: 8%;">Aksi</th>
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
                                    <label class="form-label fw-semibold" style="font-size: 13px; color: #475569;">Nama Supir <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light" style="border-radius: 8px 0 0 8px; border-color: #cbd5e1; height: 38px; color: #64748b; padding: 0 12px; display: flex; align-items: center;">
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
                                    <label class="form-label fw-semibold" style="font-size: 13px; color: #475569;">Tanggal Mulai <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0" style="border-radius: 8px 0 0 8px; border-color: #cbd5e1; height: 38px; color: #64748b; padding: 0 12px; display: flex; align-items: center;">
                                            <i class="mdi mdi-calendar-range fs-16"></i>
                                        </span>
                                        <input class="form-control border-start-0 bg-white" name="startDate" id="modalStartDate" type="text" placeholder="Pilih Tanggal" required style="border-radius: 0 8px 8px 0; border-color: #cbd5e1; height: 38px; font-size: 14px; color: #334155;">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold" style="font-size: 13px; color: #475569;">Tanggal Akhir <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0" style="border-radius: 8px 0 0 8px; border-color: #cbd5e1; height: 38px; color: #64748b; padding: 0 12px; display: flex; align-items: center;">
                                            <i class="mdi mdi-calendar-range fs-16"></i>
                                        </span>
                                        <input class="form-control border-start-0 bg-white" name="endDate" id="modalEndDate" type="text" placeholder="Pilih Tanggal" required style="border-radius: 0 8px 8px 0; border-color: #cbd5e1; height: 38px; font-size: 14px; color: #334155;">
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="button" class="btn btn-search-order w-100" id="btnSearchOrders" style="height: 38px; border-radius: 8px; font-size: 14px; padding: 0;">
                                        <i class="mdi mdi-magnify me-1"></i> Cari
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Section 2: Order preview --}}
                        <div id="orderPreviewSection" style="display: none;">
                            <div class="modal-section">
                                <div class="modal-section-title">
                                    <span class="section-badge">2</span>
                                    Daftar Order
                                    <span class="ms-auto badge bg-primary rounded-pill" id="orderCount">0 order</span>
                                </div>
                                <div class="table-responsive custom-scrollbar" style="max-height: 280px;">
                                    <table class="table table-bordered table-sm mb-0" id="orderPreviewTable">
                                        <thead>
                                            <tr>
                                                <th style="width: 5%;">No</th>
                                                <th style="width: 12%;">Tanggal</th>
                                                <th style="width: 14%;">No. Polisi</th>
                                                <th style="width: 45%;">Rute</th>
                                                <th style="width: 16%;" class="text-end">Gaji</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
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
                                <textarea class="form-control" name="notes" rows="2" placeholder="Catatan tambahan..." style="border-radius:8px;"></textarea>
                            </div>
                        </div>

                        {{-- No data message --}}
                        <div id="noOrderMessage" style="display: none;">
                            <div class="modal-section">
                                <div class="empty-state">
                                    <i class="mdi mdi-truck-remove-outline"></i>
                                    <p>Tidak ada order dengan komponen gaji untuk driver dan periode yang dipilih.</p>
                                </div>
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

        $(document).ready(function() {

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

            // Reset modal state when closed
            $('#processSalaryModal').on('hidden.bs.modal', function () {
                totalSalaryFromOrders = 0;
                $('#orderPreviewSection').hide();
                $('#noOrderMessage').hide();
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

            // ============================================================
            // Main DataTable: Processed salaries
            // ============================================================
            const processedTable = $('#dtProcessed').DataTable({
                "processing": true,
                "serverSide": true,
                "destroy": true,
                "pageLength": 25,
                "ajax": {
                    "url": "{{ route('dt.driver-salary-processed') }}"
                },
                "columns": [
                    { "data": "DT_RowIndex", "className": "text-center" },
                    { "data": "code" },
                    { "data": "driverName" },
                    { "data": "startDate" },
                    { "data": "endDate" },
                    { "data": "totalSalaryFormatted", "className": "text-end" },
                    { "data": "totalAdjustmentFormatted", "className": "text-end" },
                    { "data": "grandTotalFormatted", "className": "text-end fw-bold" },
                    { "data": "action", "className": "text-center" },
                ],
                "columnDefs": [
                    { "searchable": false, "targets": [0, 3, 4, 5, 6, 7, 8] },
                    { "orderable": false, "targets": [0, 8] }
                ]
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
                        if (response.orders.length === 0) {
                            $('#orderPreviewSection').hide();
                            $('#noOrderMessage').show();
                            $('#btnSubmitSalary').hide();
                            totalSalaryFromOrders = 0;
                            return;
                        }

                        totalSalaryFromOrders = response.totalSalary;

                        // Populate table
                        let tbody = '';
                        response.orders.forEach(function(order, idx) {
                            tbody += '<tr>' +
                                '<td class="text-center">' + (idx + 1) + '</td>' +
                                '<td>' + order.orderDate + '</td>' +
                                '<td>' + order.plateNumber + '</td>' +
                                '<td>' + order.routeName + '</td>' +
                                '<td class="text-end fw-semibold">Rp ' + order.salaryFormatted + '</td>' +
                                '</tr>';
                        });
                        tbody += '<tr style="background:linear-gradient(135deg,#eef2ff,#e0e7ff);">' +
                            '<td colspan="4" class="text-end fw-bold" style="color:#3730a3;">Total Gaji dari Order</td>' +
                            '<td class="text-end fw-bold" style="color:#3730a3;">Rp ' + response.totalSalaryFormatted + '</td>' +
                            '</tr>';

                        $('#orderPreviewTable tbody').html(tbody);
                        $('#orderCount').text(response.orders.length + ' order');
                        $('#orderPreviewSection').show();
                        $('#noOrderMessage').hide();
                        $('#btnSubmitSalary').show();
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

            // ============================================================
            // Modal: Adjustment rows
            // ============================================================
            let adjIndex = 0;

            $('#btnAddAdjustment').on('click', function() {
                $('#noAdjustmentHint').hide();
                const html = `
                    <div class="adjustment-row" data-index="${adjIndex}">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-2">
                                <label class="form-label" style="font-size:12px;font-weight:600;color:#64748b;">Tanggal</label>
                                <input type="date" class="form-control form-control-sm" name="adjustments[${adjIndex}][date]" required style="border-radius:6px;">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" style="font-size:12px;font-weight:600;color:#64748b;">Deskripsi</label>
                                <input type="text" class="form-control form-control-sm" name="adjustments[${adjIndex}][description]" placeholder="Contoh: Potong utang, Bonus, dll" required style="border-radius:6px;">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label" style="font-size:12px;font-weight:600;color:#64748b;">Tipe</label>
                                <select class="form-select form-select-sm adj-type" name="adjustments[${adjIndex}][type]" required style="border-radius:6px;">
                                    <option value="addition">➕ Penambah</option>
                                    <option value="deduction">➖ Pengurang</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" style="font-size:12px;font-weight:600;color:#64748b;">Nominal (Rp)</label>
                                <input type="text" class="form-control form-control-sm adj-nominal" name="adjustments[${adjIndex}][nominal]" placeholder="0" oninput="formatAngka(this)" required style="border-radius:6px;">
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
            });

            // Remove adjustment row
            $(document).on('click', '.btn-remove-adj', function() {
                $(this).closest('.adjustment-row').remove();
                if ($('.adjustment-row').length === 0) {
                    $('#noAdjustmentHint').show();
                }
                recalcSummary();
            });

            // Recalculate on change
            $(document).on('change keyup input', '.adj-nominal, .adj-type', function() {
                recalcSummary();
            });

            // ============================================================
            // Submit validation
            // ============================================================
            $('#processSalaryForm').on('submit', function(e) {
                if (totalSalaryFromOrders <= 0) {
                    e.preventDefault();
                    swal('Peringatan', 'Cari order terlebih dahulu sebelum memproses gaji.', 'warning');
                    return;
                }

                // Clean dots from adjustments nominal fields so the backend receives raw numbers
                $('.adj-nominal').each(function() {
                    const rawVal = $(this).val() || '';
                    const cleanVal = rawVal.replace(/\./g, '');
                    $(this).val(cleanVal);
                });
            });
        });

        // ============================================================
        // Helper functions
        // ============================================================

        function recalcSummary() {
            let totalAdj = 0;
            $('.adjustment-row').each(function() {
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
    </script>
@endpush
