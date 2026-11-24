@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Report',
    'secondSegment' => $title,
])

@push('style')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/libs/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css') }}">
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
        .filter-card .form-control[name="shipmentNumber"] {
            padding-left: 12px !important;
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

        /* ── Select2 matching ── */
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
        .summary-card small { font-size: 12px; opacity: 0.85; }
        .summary-omset { background: linear-gradient(135deg, #eef2ff, #e0e7ff); border-color: #c7d2fe; color: #3730a3; }
        .summary-biaya { background: linear-gradient(135deg, #fef2f2, #fee2e2); border-color: #fecaca; color: #991b1b; }
        .summary-profit { background: linear-gradient(135deg, #ecfdf5, #d1fae5); border-color: #a7f3d0; color: #065f46; }

        /* ── DataTable & Footer Total ── */
        #dtOmset {
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        #dtOmset thead th {
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
        #dtOmset tbody tr:hover { background-color: #f8fafc !important; }
        #dtOmset tfoot th,
        #dtOmset tfoot td {
            background: linear-gradient(135deg, #eef2ff, #e0e7ff);
            border-top: 2px solid #818cf8;
            color: #1e293b;
            font-weight: 800;
            font-size: 13px;
            padding: 13px 10px;
            white-space: nowrap;
        }

        .btn-icon {
            border-radius: 8px !important;
            padding: 6px 10px;
            font-size: 13px;
        }

        /* ── Dark Mode (PHL §3 + KPI + Table + Footer) ── */
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
        html[data-bs-theme="dark"] #dtOmset { border-color: var(--bs-border-color) !important; }
        html[data-bs-theme="dark"] #dtOmset thead th {
            background-color: var(--bs-tertiary-bg) !important;
            color: var(--bs-body-color) !important;
            border-color: var(--bs-border-color) !important;
        }
        html[data-bs-theme="dark"] #dtOmset tbody td {
            color: var(--bs-body-color);
            border-color: var(--bs-border-color) !important;
        }
        html[data-bs-theme="dark"] #dtOmset tbody tr:hover { background-color: rgba(255, 255, 255, 0.05) !important; }
        html[data-bs-theme="dark"] #dtOmset tfoot th,
        html[data-bs-theme="dark"] #dtOmset tfoot td {
            background: rgba(79, 70, 229, 0.22) !important;
            border-top-color: #818cf8 !important;
            color: #e0e7ff !important;
        }
        html[data-bs-theme="dark"] .summary-omset {
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.22), rgba(99, 102, 241, 0.12));
            border-color: rgba(129, 140, 248, 0.35);
            color: #a5b4fc;
        }
        html[data-bs-theme="dark"] .summary-biaya {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.22), rgba(220, 38, 38, 0.12));
            border-color: rgba(248, 113, 113, 0.35);
            color: #fca5a5;
        }
        html[data-bs-theme="dark"] .summary-profit {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.22), rgba(5, 150, 105, 0.12));
            border-color: rgba(52, 211, 153, 0.35);
            color: #6ee7b7;
        }
    </style>
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
            <div class="card-header py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h4 class="mb-1 fw-bold d-flex align-items-center gap-2">
                        <i class="mdi mdi-trending-up text-primary fs-20"></i>
                        {{ $title }}
                    </h4>
                    <small class="text-muted">Laporan omset dan biaya seluruh order dengan filter fleksibel dan grand total otomatis</small>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('report.omset.excel') }}" target="_blank" id="export-excel"
                        class="btn btn-icon btn-sm bg-success-subtle" data-bs-toggle="tooltip" title="Export Excel">
                        <i class="mdi mdi-file-excel fs-16 text-success"></i>
                    </a>
                    <a href="{{ route('report.omset.pdf') }}" target="_blank" id="export-pdf"
                        class="btn btn-icon btn-sm bg-danger-subtle" data-bs-toggle="tooltip" title="Export PDF">
                        <i class="mdi mdi-file-pdf-box fs-16 text-danger"></i>
                    </a>
                </div>
            </div>

            <div class="card-body p-4">
                @include('partials.alert')

                {{-- KPI Summary Cards --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="summary-card summary-omset">
                            <h5>Total Semua Omset</h5>
                            <h3 id="kpiTotalOmset" class="font-monospace">Rp 0</h3>
                            <small id="kpiTotalOrders">0 order</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="summary-card summary-biaya">
                            <h5>Total Semua Biaya</h5>
                            <h3 id="kpiTotalBiaya" class="font-monospace">Rp 0</h3>
                            <small>Biaya order & vendor sesuai filter</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="summary-card summary-profit">
                            <h5>Total Net / Profit</h5>
                            <h3 id="kpiTotalProfit" class="font-monospace">Rp 0</h3>
                            <small>Omset &minus; Biaya</small>
                        </div>
                    </div>
                </div>

                {{-- Filter Bar (Standard Collapsible Panel PHL §3) --}}
                <div class="filter-card">
                    <button type="button" class="filter-card-header" data-bs-toggle="collapse"
                        data-bs-target="#omsetFilterCollapse" aria-expanded="false" aria-controls="omsetFilterCollapse">
                        <span class="filter-card-heading">
                            <i class="mdi mdi-filter-variant"></i>
                            <strong>Filter Data</strong>
                            <small>Filter bebas berdasarkan tanggal, customer, perusahaan, armada, supir, rute, status, atau no. shipment</small>
                        </span>
                        <i class="mdi mdi-chevron-down filter-card-chevron"></i>
                    </button>

                    <div class="collapse filter-collapse" id="omsetFilterCollapse">
                        <div class="filter-collapse-body">
                            <div id="filterForm">
                                <div class="row g-3">
                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="startDate">Dari Tanggal</label>
                                        <input type="text" id="startDate" name="startDate" class="form-control filter-control" placeholder="Pilih tanggal mulai">
                                    </div>
                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="endDate">Sampai Tanggal</label>
                                        <input type="text" id="endDate" name="endDate" class="form-control filter-control" placeholder="Pilih tanggal akhir">
                                    </div>
                                    <div class="col-xl-6 col-md-12">
                                        <label class="filter-label" for="customerCodes">Customer (bisa pilih 1 atau beberapa)</label>
                                        <select id="customerCodes" name="customerCodes[]" class="form-select select2-multi" multiple="multiple">
                                            @foreach ($customers as $cust)
                                                <option value="{{ $cust->code }}">{{ $cust->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="companyCode">Perusahaan (Company)</label>
                                        <select id="companyCode" name="companyCode" class="form-select select2-filter">
                                            <option value="">Semua Perusahaan</option>
                                            @foreach ($companies as $comp)
                                                <option value="{{ $comp->code }}">{{ $comp->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="fleetCategory">Tipe Armada</label>
                                        <select id="fleetCategory" name="fleetCategory" class="form-select">
                                            <option value="">Semua Tipe</option>
                                            <option value="Internal">Internal</option>
                                            <option value="External">External</option>
                                        </select>
                                    </div>
                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="fleetCode">Armada (No. Polisi)</label>
                                        <select id="fleetCode" name="fleetCode" class="form-select select2-filter">
                                            <option value="">Semua Armada</option>
                                            @foreach ($fleets as $fleet)
                                                <option value="{{ $fleet->code }}">{{ $fleet->plateNumber }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="driverCode">Supir</label>
                                        <select id="driverCode" name="driverCode" class="form-select select2-filter">
                                            <option value="">Semua Supir</option>
                                            @foreach ($drivers as $driver)
                                                <option value="{{ $driver->code }}">{{ $driver->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="originCode">Rute Asal</label>
                                        <select id="originCode" name="originCode" class="form-select select2-filter">
                                            <option value="">Semua Asal</option>
                                            @foreach ($locations as $loc)
                                                <option value="{{ $loc->code }}">{{ $loc->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="destinationCode">Rute Tujuan</label>
                                        <select id="destinationCode" name="destinationCode" class="form-select select2-filter">
                                            <option value="">Semua Tujuan</option>
                                            @foreach ($locations as $loc)
                                                <option value="{{ $loc->code }}">{{ $loc->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xl-2 col-md-4">
                                        <label class="filter-label" for="orderStatus">Status Order</label>
                                        <select id="orderStatus" name="orderStatus" class="form-select">
                                            <option value="">Semua Status</option>
                                            @foreach ($orderStatuses as $st)
                                                <option value="{{ $st->code }}">{{ $st->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xl-2 col-md-4">
                                        <label class="filter-label" for="paymentStatus">Status Pembayaran</label>
                                        <select id="paymentStatus" name="paymentStatus" class="form-select">
                                            <option value="">Semua Status</option>
                                            <option value="paid">Lunas</option>
                                            <option value="partial">DP / Sebagian</option>
                                            <option value="unpaid">Belum Dibayar</option>
                                        </select>
                                    </div>
                                    <div class="col-xl-2 col-md-4">
                                        <label class="filter-label" for="shipmentNumber">No. Shipment / Order</label>
                                        <input type="text" id="shipmentNumber" name="shipmentNumber" class="form-control" placeholder="Cari nomor...">
                                    </div>

                                    <div class="col-12 d-flex justify-content-end gap-2">
                                        <button type="button" id="btnFilter" class="btn btn-filter-primary">
                                            <i class="mdi mdi-filter-outline"></i> Terapkan Filter
                                        </button>
                                        <button type="button" id="btnResetFilter" class="btn btn-filter-reset" data-bs-toggle="tooltip" title="Reset Filter">
                                            <i class="mdi mdi-refresh"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Main DataTable with Grand Total Footer --}}
                <div class="table-responsive custom-scrollbar">
                    <table class="table align-middle w-100 mb-0" id="dtOmset">
                        <thead>
                            <tr>
                                <th style="width: 4%;" class="text-center">No</th>
                                <th style="width: 9%;">Tanggal</th>
                                <th style="width: 15%;">No. Shipment / Order</th>
                                <th style="width: 16%;">Customer / Perusahaan</th>
                                <th style="width: 13%;">Armada / Supir</th>
                                <th style="width: 15%;">Rute</th>
                                <th style="width: 10%;" class="text-center">Status</th>
                                <th style="width: 6%;" class="text-end">Omset</th>
                                <th style="width: 6%;" class="text-end">Biaya</th>
                                <th style="width: 6%;" class="text-end">Net / Profit</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <th colspan="7" class="text-end" id="tfootLabel">GRAND TOTAL (0 Order)</th>
                                <th class="text-end font-monospace" id="tfootTotalOmset">Rp 0</th>
                                <th class="text-end font-monospace" id="tfootTotalBiaya">Rp 0</th>
                                <th class="text-end font-monospace" id="tfootTotalProfit">Rp 0</th>
                            </tr>
                        </tfoot>
                    </table>
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
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/flatpickr.js') }}"></script>

    <script>
        $(document).ready(function () {
            $('.select2-multi').select2({ placeholder: 'Semua Customer', width: '100%' });
            $('.select2-filter').select2({ placeholder: 'Semua pilihan', allowClear: true, width: '100%' });

            let startPicker, endPicker;
            startPicker = flatpickr('#startDate', {
                dateFormat: 'Y-m-d', allowInput: true,
                onChange: function (d, dateStr) { if (endPicker) endPicker.set('minDate', dateStr || null); }
            });
            endPicker = flatpickr('#endDate', {
                dateFormat: 'Y-m-d', allowInput: true,
                onChange: function (d, dateStr) { if (startPicker) startPicker.set('maxDate', dateStr || null); }
            });

            function getFilters() {
                return {
                    startDate: $('#startDate').val() || '',
                    endDate: $('#endDate').val() || '',
                    customerCodes: $('#customerCodes').val() || [],
                    companyCode: $('#companyCode').val() || '',
                    fleetCategory: $('#fleetCategory').val() || '',
                    fleetCode: $('#fleetCode').val() || '',
                    driverCode: $('#driverCode').val() || '',
                    originCode: $('#originCode').val() || '',
                    destinationCode: $('#destinationCode').val() || '',
                    orderStatus: $('#orderStatus').val() || '',
                    paymentStatus: $('#paymentStatus').val() || '',
                    shipmentNumber: $('#shipmentNumber').val() || '',
                };
            }

            function syncExportUrls() {
                const f = getFilters();
                const params = new URLSearchParams();
                Object.entries(f).forEach(function ([key, val]) {
                    if (Array.isArray(val)) {
                        val.forEach(v => { if (v) params.append(key + '[]', v); });
                    } else if (val !== '' && val !== null && val !== undefined) {
                        params.set(key, val);
                    }
                });
                const qs = params.toString() ? '?' + params.toString() : '';
                $('#export-excel').attr('href', "{{ route('report.omset.excel') }}" + qs);
                $('#export-pdf').attr('href', "{{ route('report.omset.pdf') }}" + qs);
            }

            const table = $('#dtOmset').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                ajax: {
                    url: "{{ route('dt.omset') }}",
                    data: function (d) { Object.assign(d, getFilters()); },
                    dataSrc: function (json) {
                        if (json.totals) {
                            const t = json.totals;
                            const countText = Number(t.totalOrders).toLocaleString('id-ID');
                            $('#kpiTotalOmset').text(t.totalOmsetFormatted);
                            $('#kpiTotalOrders').text(countText + ' order sesuai filter');
                            $('#kpiTotalBiaya').text(t.totalBiayaFormatted);
                            $('#kpiTotalProfit').text(t.totalProfitFormatted);

                            $('#tfootLabel').text('GRAND TOTAL (' + countText + ' Order)');
                            $('#tfootTotalOmset').text(t.totalOmsetFormatted);
                            $('#tfootTotalBiaya').text(t.totalBiayaFormatted);
                            $('#tfootTotalProfit').text(t.totalProfitFormatted);
                        }
                        return json.data;
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'orderDate', name: 'o.orderDate' },
                    { data: 'orderNumber', orderable: false, searchable: false },
                    { data: 'customerCompany', orderable: false, searchable: false },
                    { data: 'fleetDriver', orderable: false, searchable: false },
                    { data: 'routeInfo', orderable: false, searchable: false },
                    { data: 'statusBadges', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'omsetFormatted', orderable: false, searchable: false, className: 'text-end' },
                    { data: 'biayaFormatted', orderable: false, searchable: false, className: 'text-end' },
                    { data: 'profitFormatted', orderable: false, searchable: false, className: 'text-end' },
                ]
            });

            function reloadWithFilters() {
                syncExportUrls();
                table.ajax.reload(null, true);
            }

            $('#btnFilter').on('click', reloadWithFilters);
            $('#shipmentNumber').on('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    reloadWithFilters();
                }
            });

            $('#btnResetFilter').on('click', function () {
                $('#filterForm').find('input').val('');
                $('#fleetCategory, #orderStatus, #paymentStatus').val('');
                $('.select2-multi').val([]).trigger('change');
                $('.select2-filter').val('').trigger('change');
                startPicker.clear();
                endPicker.clear();
                startPicker.set('maxDate', null);
                endPicker.set('minDate', null);
                reloadWithFilters();
            });

            syncExportUrls();
        });
    </script>
@endpush
