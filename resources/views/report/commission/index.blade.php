@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Report',
    'secondSegment' => $title,
])

@push('style')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/libs/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/sweetalert2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom-select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">

    <style>
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
        .filter-card-header:hover { background: #f1f5f9; }
        .filter-card-heading { align-items: center; display: flex; gap: 8px; }
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
        .filter-card .form-control,
        .filter-card .form-select {
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

        /* ── Main Table ── */
        #dtOrderCommission {
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        #dtOrderCommission thead th {
            background-color: #f8fafc;
            color: #475569;
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding: 12px 10px;
            border-bottom: 2px solid #e2e8f0;
            border-top: none;
            white-space: nowrap;
        }
        #dtOrderCommission tbody tr:hover {
            background-color: #f8fafc !important;
        }
        .btn-icon {
            border-radius: 8px !important;
            padding: 5px 9px;
            font-size: 13px;
            transition: all 0.2s ease;
        }
        .btn-icon:hover { transform: translateY(-1px); }

        .btn-submit-commission {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            border: none;
            color: #fff;
            font-weight: 600;
            padding: 9px 24px;
            border-radius: 8px;
        }
        .btn-submit-commission:hover { color: #fff; }

        /* ── KPI Summary Tiles ── */
        .summary-card {
            border-radius: 12px;
            padding: 16px 18px;
            border: 1px solid transparent;
        }
        .summary-card h5 {
            font-size: 11.5px;
            margin-bottom: 6px;
            opacity: 0.85;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            color: inherit !important;
        }
        .summary-card h3 {
            font-size: 20px;
            font-weight: 800;
            margin: 0;
            color: inherit !important;
        }
        .summary-card small {
            font-size: 12px;
            opacity: 0.85;
        }
        .summary-commission { background: linear-gradient(135deg, #eef2ff, #e0e7ff); border-color: #c7d2fe; color: #3730a3; }
        .summary-adj { background: linear-gradient(135deg, #ecfdf5, #d1fae5); border-color: #a7f3d0; color: #065f46; }
        .summary-grand { background: linear-gradient(135deg, #fefce8, #fef9c3); border-color: #fde68a; color: #92400e; }

        /* ── Bulk action bar ── */
        .bulk-bar {
            background: #eef2ff;
            border: 1px solid #c7d2fe;
            border-radius: 10px;
            padding: 10px 14px;
            margin-bottom: 14px;
        }

        /* ── Modal ── */
        .commission-modal .modal-content {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0, 0, 0, .15);
        }
        .commission-modal .modal-header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #a855f7 100%);
            border-bottom: none;
            padding: 18px 24px;
            position: relative;
        }
        .commission-modal .modal-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #fbbf24, #f59e0b, #fbbf24);
        }
        .commission-modal .modal-body {
            padding: 24px;
            background: #f8fafc;
        }
        .commission-modal .modal-footer {
            background: #fff;
            border-top: 1px solid #e2e8f0;
            padding: 16px 24px;
        }

        /* Select2 height matching */
        .select2-container { width: 100% !important; }
        .select2-container--default .select2-selection--single {
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            height: 38px !important;
            display: flex !important;
            align-items: center !important;
            background-color: #fff !important;
        }
        .select2-container--default .select2-selection--multiple {
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            min-height: 38px !important;
            background-color: #fff !important;
            padding: 2px 6px !important;
        }

        /* ── Dark Mode (PHL §3 + Table + Modal) ── */
        html[data-bs-theme="dark"] .filter-card { background: var(--bs-secondary-bg); border-color: var(--bs-border-color); }
        html[data-bs-theme="dark"] .filter-card-header { background: var(--bs-secondary-bg); color: var(--bs-body-color); }
        html[data-bs-theme="dark"] .filter-card-header:hover { background: var(--bs-tertiary-bg); }
        html[data-bs-theme="dark"] .filter-card .filter-collapse { border-top-color: var(--bs-border-color); }
        html[data-bs-theme="dark"] .filter-label { color: var(--bs-secondary-color); }
        html[data-bs-theme="dark"] .filter-control,
        html[data-bs-theme="dark"] .filter-card .form-control,
        html[data-bs-theme="dark"] .filter-card .form-select {
            background-color: var(--bs-tertiary-bg) !important;
            border-color: var(--bs-border-color) !important;
            color: var(--bs-body-color) !important;
        }
        html[data-bs-theme="dark"] .btn-filter-reset {
            background: var(--bs-tertiary-bg) !important;
            border-color: var(--bs-border-color) !important;
            color: var(--bs-body-color) !important;
        }
        html[data-bs-theme="dark"] .bulk-bar {
            background: rgba(79, 70, 229, 0.16);
            border-color: rgba(129, 140, 248, 0.35);
        }
        html[data-bs-theme="dark"] #dtOrderCommission { border-color: var(--bs-border-color) !important; }
        html[data-bs-theme="dark"] #dtOrderCommission thead th {
            background-color: var(--bs-tertiary-bg) !important;
            color: var(--bs-body-color) !important;
            border-color: var(--bs-border-color) !important;
        }
        html[data-bs-theme="dark"] #dtOrderCommission tbody td {
            color: var(--bs-body-color);
            border-color: var(--bs-border-color) !important;
        }
        html[data-bs-theme="dark"] #dtOrderCommission tbody tr:hover { background-color: rgba(255, 255, 255, 0.05) !important; }

        html[data-bs-theme="dark"] .commission-modal .modal-content {
            background-color: var(--bs-secondary-bg) !important;
            color: var(--bs-body-color);
            border: 1px solid var(--bs-border-color);
        }
        html[data-bs-theme="dark"] .commission-modal .modal-body { background-color: var(--bs-body-bg) !important; }
        html[data-bs-theme="dark"] .commission-modal .modal-footer {
            background-color: var(--bs-secondary-bg) !important;
            border-top-color: var(--bs-border-color) !important;
        }
        html[data-bs-theme="dark"] .commission-modal .form-control {
            background-color: var(--bs-tertiary-bg) !important;
            border-color: var(--bs-border-color) !important;
            color: var(--bs-body-color) !important;
        }
        html[data-bs-theme="dark"] .summary-commission {
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.22), rgba(99, 102, 241, 0.12));
            border-color: rgba(129, 140, 248, 0.35);
            color: #a5b4fc;
        }
        html[data-bs-theme="dark"] .summary-adj {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.22), rgba(5, 150, 105, 0.12));
            border-color: rgba(52, 211, 153, 0.35);
            color: #6ee7b7;
        }
        html[data-bs-theme="dark"] .summary-grand {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.22), rgba(217, 119, 6, 0.12));
            border-color: rgba(251, 191, 36, 0.35);
            color: #fde68a;
        }
    </style>
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
            <div class="card-header py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h4 class="mb-1 fw-bold d-flex align-items-center gap-2">
                        <i class="mdi mdi-cash-multiple text-primary fs-20"></i>
                        {{ $title }}
                    </h4>
                    <small class="text-muted">Daftar komisi dari seluruh order, pilih tanggal yang ingin di-print, dan input tanggal bayarnya</small>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <a href="{{ route('report.commission.pdf-by-date') }}" target="_blank" id="btnPrintFilteredPdf"
                        class="btn btn-danger" style="border-radius: 8px; font-weight: 600; padding: 8px 18px;">
                        <i class="mdi mdi-printer me-1"></i> Cetak Slip PDF
                    </a>
                </div>
            </div>

            <div class="card-body p-4">
                @include('partials.alert')

                {{-- KPI Summary Cards --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="summary-card summary-commission">
                            <h5>Total Order Komisi</h5>
                            <h3 id="kpiTotalOrders" class="font-monospace">0</h3>
                            <small id="kpiTotalAmount">Rp 0</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="summary-card summary-adj">
                            <h5>Sudah Dibayar</h5>
                            <h3 id="kpiPaidAmount" class="font-monospace">Rp 0</h3>
                            <small id="kpiPaidCount">0 order</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="summary-card summary-grand">
                            <h5>Belum Dibayar</h5>
                            <h3 id="kpiUnpaidAmount" class="font-monospace">Rp 0</h3>
                            <small id="kpiUnpaidCount">0 order</small>
                        </div>
                    </div>
                </div>

                {{-- Filter Bar (Standard Collapsible Panel PHL §3) --}}
                <div class="filter-card">
                    <button type="button" class="filter-card-header" data-bs-toggle="collapse"
                        data-bs-target="#orderCommissionFilterCollapse" aria-expanded="false" aria-controls="orderCommissionFilterCollapse">
                        <span class="filter-card-heading">
                            <i class="mdi mdi-filter-variant"></i>
                            <strong>Filter Data & Pilih Tanggal Cetak</strong>
                            <small>Filter berdasarkan komponen komisi, customer, status bayar, dan rentang tanggal order</small>
                        </span>
                        <i class="mdi mdi-chevron-down filter-card-chevron"></i>
                    </button>

                    <div class="collapse filter-collapse" id="orderCommissionFilterCollapse">
                        <div class="filter-collapse-body">
                            <div id="orderFilterForm">
                                <div class="row g-3">
                                    <div class="col-xl-4 col-md-6">
                                        <label class="filter-label" for="orderFilterComponents">Komponen Komisi (bisa pilih lebih dari 1)</label>
                                        <select id="orderFilterComponents" class="form-select select2-order-multi" multiple="multiple">
                                            @foreach ($commissionComponents as $comp)
                                                <option value="{{ $comp->code }}">{{ $comp->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="orderFilterCustomer">Customer</label>
                                        <select id="orderFilterCustomer" class="form-select select2-order-single">
                                            <option value="">Semua Customer</option>
                                            @foreach ($customers as $cust)
                                                <option value="{{ $cust->code }}">{{ $cust->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xl-2 col-md-4">
                                        <label class="filter-label" for="orderFilterStatus">Status Bayar</label>
                                        <select id="orderFilterStatus" class="form-select">
                                            <option value="">Semua</option>
                                            <option value="unpaid">Belum Dibayar</option>
                                            <option value="paid">Sudah Dibayar</option>
                                        </select>
                                    </div>
                                    <div class="col-xl-3 col-md-8">
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <label class="filter-label" for="orderFilterStartDate">Dari Tanggal</label>
                                                <input type="text" id="orderFilterStartDate" class="form-control filter-control" placeholder="Mulai">
                                            </div>
                                            <div class="col-6">
                                                <label class="filter-label" for="orderFilterEndDate">Sampai Tanggal</label>
                                                <input type="text" id="orderFilterEndDate" class="form-control filter-control" placeholder="Akhir">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 d-flex justify-content-end gap-2">
                                        <button type="button" id="btnApplyOrderFilter" class="btn btn-filter-primary">
                                            <i class="mdi mdi-filter-outline"></i> Terapkan Filter
                                        </button>
                                        <button type="button" id="btnResetOrderFilter" class="btn btn-filter-reset" data-bs-toggle="tooltip" title="Reset Filter">
                                            <i class="mdi mdi-refresh"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bulk Action Bar (muncul saat ada order dicentang) --}}
                <div class="bulk-bar d-flex flex-wrap align-items-center justify-content-between gap-2" id="bulkActionBar" style="display: none !important;">
                    <div class="fw-semibold" style="font-size: 13px;">
                        <i class="mdi mdi-checkbox-marked-circle-outline text-primary me-1"></i>
                        <span id="selectedOrderCount">0</span> order dipilih
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-sm btn-info text-white fw-semibold" id="btnBulkPaymentDate" style="border-radius: 8px;">
                            <i class="mdi mdi-calendar-check me-1"></i> Input Tanggal Bayar (Terpilih)
                        </button>
                        <button type="button" class="btn btn-sm btn-danger fw-semibold" id="btnBulkPrintPdf" style="border-radius: 8px;">
                            <i class="mdi mdi-file-pdf-box me-1"></i> Cetak Slip PDF (Terpilih)
                        </button>
                    </div>
                </div>

                {{-- Order Commissions DataTable --}}
                <div class="table-responsive custom-scrollbar">
                    <table class="table align-middle w-100 mb-0" id="dtOrderCommission">
                        <thead>
                            <tr>
                                <th style="width: 3%;" class="text-center">
                                    <input type="checkbox" class="form-check-input" id="selectAllOrders" title="Pilih Semua">
                                </th>
                                <th style="width: 7%;" class="text-center">Aksi</th>
                                <th style="width: 4%;" class="text-center">No</th>
                                <th style="width: 9%;">Tanggal</th>
                                <th style="width: 15%;">No. Shipment / Order</th>
                                <th style="width: 14%;">Customer</th>
                                <th style="width: 17%;">No. Polisi & Rute</th>
                                <th style="width: 14%;">Komponen Komisi</th>
                                <th style="width: 9%;" class="text-end">Komisi</th>
                                <th style="width: 8%;" class="text-center">Tgl Bayar</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Input Tanggal Bayar Order (Per Order / Massal) --}}
    <div class="modal fade commission-modal" id="orderPaymentDateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-white"><i class="mdi mdi-calendar-check me-2"></i> Input Tanggal Bayar Komisi</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="small text-muted">Order / Komponen</div>
                        <div class="fw-bold" id="orderPaymentInfo">-</div>
                    </div>
                    <label class="form-label fw-semibold" for="orderPaymentDateInput">Tanggal Bayar</label>
                    <input type="text" id="orderPaymentDateInput" class="form-control" placeholder="Pilih Tanggal Bayar">
                    <small class="text-muted d-block mt-2">Tanggal bayar akan otomatis tampil pada baris <strong>Diterima Tanggal</strong> saat mencetak Slip Komisi PDF. Kosongkan bila ingin menandai belum dibayar.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-submit-commission" id="btnSaveOrderPaymentDate">
                        <i class="mdi mdi-content-save me-1"></i> Simpan Tanggal Bayar
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/js/sweet-alert/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/flatpickr.js') }}"></script>

    <script>
        let orderCommissionTable;
        let pendingOrderPaymentIds = [];
        let orderPaymentPicker;

        function getSelectedOrderIds() {
            const ids = [];
            $('.order-commission-checkbox:checked').each(function () {
                ids.push($(this).val());
            });
            return ids;
        }

        function updateBulkBar() {
            const ids = getSelectedOrderIds();
            if (ids.length > 0) {
                $('#selectedOrderCount').text(ids.length);
                $('#bulkActionBar').attr('style', '');
            } else {
                $('#bulkActionBar').attr('style', 'display: none !important;');
            }
        }

        function openOrderPaymentModal(ids, infoText, currentDate) {
            pendingOrderPaymentIds = ids;
            $('#orderPaymentInfo').text(infoText);
            if (orderPaymentPicker) {
                orderPaymentPicker.setDate(currentDate || null, true);
            } else {
                $('#orderPaymentDateInput').val(currentDate || '');
            }
            $('#orderPaymentDateModal').modal('show');
        }

        $(document).ready(function () {
            $('.select2-order-multi').select2({ placeholder: 'Semua Komponen Komisi', width: '100%' });
            $('.select2-order-single').select2({ placeholder: 'Semua Customer', allowClear: true, width: '100%' });

            let orderStartPicker, orderEndPicker;
            orderStartPicker = flatpickr('#orderFilterStartDate', {
                dateFormat: 'Y-m-d', allowInput: true,
                onChange: function (d, s) { if (orderEndPicker) orderEndPicker.set('minDate', s || null); }
            });
            orderEndPicker = flatpickr('#orderFilterEndDate', {
                dateFormat: 'Y-m-d', allowInput: true,
                onChange: function (d, s) { if (orderStartPicker) orderStartPicker.set('maxDate', s || null); }
            });
            orderPaymentPicker = flatpickr('#orderPaymentDateInput', { dateFormat: 'Y-m-d', allowInput: true });

            function getOrderFilters() {
                return {
                    componentCodes: $('#orderFilterComponents').val() || [],
                    customerCode: $('#orderFilterCustomer').val() || '',
                    paymentStatus: $('#orderFilterStatus').val() || '',
                    startDate: $('#orderFilterStartDate').val() || '',
                    endDate: $('#orderFilterEndDate').val() || '',
                };
            }

            function syncPrintUrl() {
                const f = getOrderFilters();
                const params = new URLSearchParams();
                if (f.startDate) params.set('startDate', f.startDate);
                if (f.endDate) params.set('endDate', f.endDate);
                if (f.customerCode) params.set('customerCode', f.customerCode);
                if (f.paymentStatus) params.set('paymentStatus', f.paymentStatus);
                (f.componentCodes || []).forEach(c => params.append('componentCodes[]', c));

                const qs = params.toString() ? '?' + params.toString() : '';
                $('#btnPrintFilteredPdf').attr('href', "{{ route('report.commission.pdf-by-date') }}" + qs);
            }

            orderCommissionTable = $('#dtOrderCommission').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                ajax: {
                    url: "{{ route('dt.commission-orders') }}",
                    data: function (d) { Object.assign(d, getOrderFilters()); },
                    dataSrc: function (json) {
                        if (json.summary) {
                            $('#kpiTotalOrders').text(json.summary.totalOrders.toLocaleString('id-ID') + ' Order');
                            $('#kpiTotalAmount').text('Total: ' + json.summary.totalAmountFormatted);
                            $('#kpiPaidAmount').text(json.summary.paidAmountFormatted);
                            $('#kpiPaidCount').text(json.summary.paidCount.toLocaleString('id-ID') + ' order');
                            $('#kpiUnpaidAmount').text(json.summary.unpaidAmountFormatted);
                            $('#kpiUnpaidCount').text(json.summary.unpaidCount.toLocaleString('id-ID') + ' order');
                        }
                        $('#selectAllOrders').prop('checked', false);
                        updateBulkBar();
                        return json.data;
                    }
                },
                columns: [
                    { data: 'checkbox', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'action', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'orderDate', orderable: false, searchable: false },
                    { data: 'orderNumber', orderable: false, searchable: false },
                    { data: 'customerName', orderable: false, searchable: false },
                    { data: 'fleetRoute', orderable: false, searchable: false },
                    { data: 'componentName', orderable: false, searchable: false },
                    { data: 'amountFormatted', name: 'amount', className: 'text-end' },
                    { data: 'paymentStatusBadge', name: 'payment_date', className: 'text-center' },
                ]
            });

            syncPrintUrl();

            $('#btnApplyOrderFilter').on('click', function () {
                syncPrintUrl();
                orderCommissionTable.ajax.reload(null, true);
            });

            $('#btnResetOrderFilter').on('click', function () {
                $('#orderFilterComponents').val([]).trigger('change');
                $('#orderFilterCustomer').val('').trigger('change');
                $('#orderFilterStatus').val('');
                orderStartPicker.clear();
                orderEndPicker.clear();
                orderStartPicker.set('maxDate', null);
                orderEndPicker.set('minDate', null);
                syncPrintUrl();
                orderCommissionTable.ajax.reload(null, true);
            });

            $('#selectAllOrders').on('change', function () {
                $('.order-commission-checkbox').prop('checked', $(this).is(':checked'));
                updateBulkBar();
            });
            $(document).on('change', '.order-commission-checkbox', updateBulkBar);

            $('#btnBulkPaymentDate').on('click', function () {
                const ids = getSelectedOrderIds();
                if (ids.length === 0) return;
                openOrderPaymentModal(ids, ids.length + ' order komisi terpilih', '');
            });

            $('#btnBulkPrintPdf').on('click', function () {
                const ids = getSelectedOrderIds();
                if (ids.length === 0) return;
                const params = new URLSearchParams();
                ids.forEach(id => params.append('ids[]', id));
                window.open("{{ route('report.commission.pdf-by-date') }}?" + params.toString(), '_blank');
            });

            $('#btnSaveOrderPaymentDate').on('click', function () {
                const btn = $(this);
                btn.prop('disabled', true);

                $.ajax({
                    url: "{{ route('report.commission.order-payment-date') }}",
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        ids: pendingOrderPaymentIds,
                        paymentDate: $('#orderPaymentDateInput').val(),
                    },
                    success: function (res) {
                        $('#orderPaymentDateModal').modal('hide');
                        orderCommissionTable.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1600, showConfirmButton: false });
                    },
                    error: function (xhr) {
                        const msg = xhr.responseJSON?.message || 'Gagal menyimpan tanggal bayar.';
                        Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
                    },
                    complete: function () {
                        btn.prop('disabled', false);
                    }
                });
            });
        });
    </script>
@endpush
