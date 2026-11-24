@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Laporan',
    'secondSegment' => $title,
])

@push('style')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom-select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">
    @include('purchasing.purchase.partials.table-style')

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

        .filter-label { color: #64748b; display: block; font-size: 12px; font-weight: 600; margin-bottom: 6px; }
        .filter-control,
        .filter-card .form-control {
            background-color: #fff !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            color: #334155 !important;
            font-size: 13px !important;
            height: 38px !important;
        }
        .filter-control { padding: 0 12px 0 36px !important; }

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

        .select2-container { width: 100% !important; }
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
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }

        /* ── Tabel Aging ── */
        #dtAging thead th,
        #dtAging tbody td { white-space: nowrap; }
        #dtAging tbody td:nth-child(n+4) { text-align: right; }
        #dtAging tbody td:nth-child(1),
        #dtAging tbody td:nth-child(2),
        #dtAging tbody td:nth-child(3),
        #dtAging thead th:nth-child(1),
        #dtAging thead th:nth-child(2),
        #dtAging thead th:nth-child(3) { text-align: center; }
        #dtAging thead th:nth-child(3) { text-align: left; }
        #dtAging tbody td:nth-child(3) { text-align: left; }
        .aging-legend { font-size: 11.5px; color: #64748b; }

        /* ── Dark mode filter card (WAJIB) ── */
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
    </style>
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
            <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h4 class="mb-1 fw-bold d-flex align-items-center gap-2">
                        <i class="mdi mdi-account-cash-outline text-primary fs-20"></i>
                        {{ $title }}
                    </h4>
                    <small class="text-muted">Pantau sisa piutang, jatuh tempo, dan umur piutang per customer (posisi per hari ini).</small>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route($view . 'pdf') }}" target="_blank" id="export-pdf"
                        class="btn btn-outline-danger btn-sm rounded-pill px-3 shadow-sm">
                        <i class="mdi mdi-file-pdf-box me-1"></i> PDF
                    </a>
                    <a href="{{ route($view . 'excel') }}" target="_blank" id="export-excel"
                        class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-sm">
                        <i class="mdi mdi-file-excel me-1"></i> Excel
                    </a>
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm" id="btn-refresh-table">
                        <i class="mdi mdi-refresh me-1" id="refresh-icon"></i> Refresh
                    </button>
                </div>
            </div>

            <div class="card-body p-3 p-md-4">
                <div class="row g-3 mb-4">
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="stat-label">Total Sisa Piutang</div>
                                    <div class="stat-value text-dark" id="stat-total-remaining">Rp {{ number_format($stats['totalRemaining'], 0, ',', '.') }}</div>
                                </div>
                                <div class="stat-icon-wrapper bg-primary-subtle text-primary"><i class="mdi mdi-cash-multiple"></i></div>
                            </div>
                            <div class="stat-desc">
                                <strong id="stat-unpaid-count">{{ number_format($stats['unpaidCount'], 0, ',', '.') }}</strong> faktur belum lunas
                                · <span id="stat-customer-count">{{ number_format($stats['customerCount'], 0, ',', '.') }}</span> customer
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="stat-label">Jatuh Tempo ≤ 7 Hari</div>
                                    <div class="stat-value text-warning-emphasis" id="stat-due-soon">Rp {{ number_format($stats['dueSoonAmount'], 0, ',', '.') }}</div>
                                </div>
                                <div class="stat-icon-wrapper bg-warning-subtle text-warning"><i class="mdi mdi-calendar-clock"></i></div>
                            </div>
                            <div class="stat-desc"><strong id="stat-due-soon-count">{{ number_format($stats['dueSoonCount'], 0, ',', '.') }}</strong> faktur mendekati jatuh tempo</div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="stat-label">Sudah Overdue</div>
                                    <div class="stat-value text-danger" id="stat-overdue">Rp {{ number_format($stats['overdueAmount'], 0, ',', '.') }}</div>
                                </div>
                                <div class="stat-icon-wrapper bg-danger-subtle text-danger"><i class="mdi mdi-alert-circle-outline"></i></div>
                            </div>
                            <div class="stat-desc text-danger"><strong id="stat-overdue-count">{{ number_format($stats['overdueCount'], 0, ',', '.') }}</strong> faktur melewati jatuh tempo</div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="stat-label">Aging Lebih 90 Hari</div>
                                    <div class="stat-value text-danger" id="stat-aging-over-90">Rp {{ number_format($stats['agingOver90'], 0, ',', '.') }}</div>
                                </div>
                                <div class="stat-icon-wrapper bg-danger-subtle text-danger"><i class="mdi mdi-timer-alert-outline"></i></div>
                            </div>
                            <div class="stat-desc">Piutang paling berisiko, perlu penagihan prioritas</div>
                        </div>
                    </div>
                </div>

                <div class="filter-card">
                    <button type="button" class="filter-card-header" data-bs-toggle="collapse"
                        data-bs-target="#receivableAgingFilterCollapse" aria-expanded="false" aria-controls="receivableAgingFilterCollapse">
                        <span class="filter-card-heading">
                            <i class="mdi mdi-filter-variant"></i>
                            <strong>Filter Data</strong>
                            <small>Gunakan filter untuk mempersempit daftar piutang customer</small>
                        </span>
                        <i class="mdi mdi-chevron-down filter-card-chevron"></i>
                    </button>

                    <div class="collapse filter-collapse" id="receivableAgingFilterCollapse">
                        <div class="filter-collapse-body">
                            <div id="filterForm">
                                <div class="row g-3 align-items-end">
                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="customerCode">Customer</label>
                                        <select class="form-select select2-filter" name="customerCode" id="customerCode">
                                            <option value="">Semua customer</option>
                                            @foreach ($customers as $item)
                                                <option value="{{ $item->code }}">{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xl-2 col-md-6">
                                        <label class="filter-label" for="startDate">Dari Tanggal</label>
                                        <input class="form-control filter-control" name="startDate" id="startDate"
                                            type="text" placeholder="Pilih tanggal mulai">
                                    </div>
                                    <div class="col-xl-2 col-md-6">
                                        <label class="filter-label" for="endDate">Sampai Tanggal</label>
                                        <input class="form-control filter-control" name="endDate" id="endDate"
                                            type="text" placeholder="Pilih tanggal akhir">
                                    </div>
                                    <div class="col-xl-2 col-md-6">
                                        <label class="filter-label" for="status">Status</label>
                                        <select class="form-select select2-filter" name="status" id="status">
                                            <option value="outstanding" selected>Belum lunas</option>
                                            <option value="overdue">Overdue</option>
                                            <option value="all">Semua faktur</option>
                                        </select>
                                    </div>
                                    <div class="col-xl-3 col-md-6 d-flex align-items-end justify-content-md-end gap-2">
                                        <button class="btn btn-filter-primary flex-grow-1 flex-md-grow-0" type="button" id="btnFilter">
                                            <i class="mdi mdi-filter-outline"></i> Terapkan Filter
                                        </button>
                                        <button class="btn btn-filter-reset" type="button" id="btnResetFilter"
                                            data-bs-toggle="tooltip" title="Reset Filter">
                                            <i class="mdi mdi-refresh"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                    <div>
                        <h6 class="fw-bold mb-0">Aging Piutang Per Customer</h6>
                        <span class="aging-legend">Bucket 0–30 sampai &gt;90 hanya memuat piutang yang sudah jatuh tempo. Aging dihitung posisi per hari ini.</span>
                    </div>
                </div>

                <div class="table-responsive custom-scrollbar">
                    <table class="table purchase-table aging-table w-100 nowrap" id="dtAging">
                        <thead>
                            <tr>
                                <th style="width: 56px;">Aksi</th>
                                <th style="width: 52px;">No</th>
                                <th>Customer</th>
                                <th>Jumlah Faktur</th>
                                <th>Total Tagihan</th>
                                <th>Sudah Dibayar</th>
                                <th>Sisa Piutang</th>
                                <th>Belum Jatuh Tempo</th>
                                <th>JT ≤ 7 Hari</th>
                                <th>Overdue</th>
                                <th>1–30 Hari</th>
                                <th>31–60 Hari</th>
                                <th>61–90 Hari</th>
                                <th>&gt; 90 Hari</th>
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
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            const numberFormat = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 });
            const currency = value => 'Rp ' + numberFormat.format(value || 0);
            const count = value => numberFormat.format(value || 0);

            const updateStats = stats => {
                if (!stats) return;
                $('#stat-total-remaining').text(currency(stats.totalRemaining));
                $('#stat-unpaid-count').text(count(stats.unpaidCount));
                $('#stat-customer-count').text(count(stats.customerCount));
                $('#stat-due-soon').text(currency(stats.dueSoonAmount));
                $('#stat-due-soon-count').text(count(stats.dueSoonCount));
                $('#stat-overdue').text(currency(stats.overdueAmount));
                $('#stat-overdue-count').text(count(stats.overdueCount));
                $('#stat-aging-over-90').text(currency(stats.agingOver90));
            };

            $('.select2-filter').select2({ width: '100%', allowClear: true });

            let startPicker;
            let endPicker;

            startPicker = flatpickr('#startDate', {
                dateFormat: 'Y-m-d',
                allowInput: true,
                onChange: function(selectedDates, dateStr) {
                    if (endPicker) endPicker.set('minDate', dateStr || null);
                }
            });

            endPicker = flatpickr('#endDate', {
                dateFormat: 'Y-m-d',
                allowInput: true,
                onChange: function(selectedDates, dateStr) {
                    if (startPicker) startPicker.set('maxDate', dateStr || null);
                }
            });

            const getFilters = () => ({
                startDate: $('#startDate').val() || '',
                endDate: $('#endDate').val() || '',
                customerCode: $('#customerCode').val() || '',
                status: $('#status').val() || 'outstanding'
            });

            const syncExportUrls = () => {
                const params = new URLSearchParams();
                Object.entries(getFilters()).forEach(function(entry) {
                    if (entry[1]) params.set(entry[0], entry[1]);
                });
                const query = params.toString() ? '?' + params.toString() : '';
                $('#export-pdf').attr('href', "{{ route($view . 'pdf') }}" + query);
                $('#export-excel').attr('href', "{{ route($view . 'excel') }}" + query);
            };

            const table = $('#dtAging').DataTable({
                processing: true,
                serverSide: false,
                destroy: true,
                pageLength: 25,
                scrollX: true,
                ajax: {
                    url: "{{ route('dt.receivable-aging') }}",
                    data: function(d) {
                        Object.assign(d, getFilters());
                    },
                    dataSrc: function(json) {
                        updateStats(json.stats);
                        return json.data;
                    }
                },
                columns: [
                    { data: 'action', className: 'text-center align-middle' },
                    { data: 'DT_RowIndex', className: 'text-center align-middle' },
                    { data: 'customerName', className: 'align-middle' },
                    {
                        data: 'invoiceCount',
                        className: 'text-end align-middle',
                        render: function(data, type) {
                            return type === 'display' ? count(data) : data;
                        }
                    },
                    {
                        data: 'totalBilling',
                        className: 'text-end align-middle',
                        render: function(data, type) {
                            return type === 'display' ? currency(data) : data;
                        }
                    },
                    {
                        data: 'totalPaid',
                        className: 'text-end align-middle',
                        render: function(data, type) {
                            return type === 'display' ? currency(data) : data;
                        }
                    },
                    {
                        data: 'totalRemaining',
                        className: 'text-end align-middle',
                        render: function(data, type) {
                            return type === 'display' ? '<strong class="text-dark">' + currency(data) + '</strong>' : data;
                        }
                    },
                    {
                        data: 'notDueAmount',
                        className: 'text-end align-middle',
                        render: function(data, type) {
                            return type === 'display' ? currency(data) : data;
                        }
                    },
                    {
                        data: 'dueSoonAmount',
                        className: 'text-end align-middle',
                        render: function(data, type, row) {
                            if (type !== 'display') return data;
                            return '<span class="text-warning-emphasis fw-semibold">' + currency(data) + '</span>'
                                + '<small class="d-block text-muted">' + count(row.dueSoonCount) + ' faktur</small>';
                        }
                    },
                    {
                        data: 'overdueAmount',
                        className: 'text-end align-middle',
                        render: function(data, type, row) {
                            if (type !== 'display') return data;
                            const cls = data > 0 ? 'text-danger' : 'text-muted';
                            return '<span class="' + cls + ' fw-semibold">' + currency(data) + '</span>'
                                + '<small class="d-block text-muted">' + count(row.overdueCount) + ' faktur</small>';
                        }
                    },
                    {
                        data: 'aging0To30',
                        className: 'text-end align-middle',
                        render: function(data, type) {
                            return type === 'display' ? currency(data) : data;
                        }
                    },
                    {
                        data: 'aging31To60',
                        className: 'text-end align-middle',
                        render: function(data, type) {
                            return type === 'display' ? currency(data) : data;
                        }
                    },
                    {
                        data: 'aging61To90',
                        className: 'text-end align-middle',
                        render: function(data, type) {
                            return type === 'display' ? currency(data) : data;
                        }
                    },
                    {
                        data: 'agingOver90',
                        className: 'text-end align-middle',
                        render: function(data, type) {
                            if (type !== 'display') return data;
                            const cls = data > 0 ? 'text-danger fw-semibold' : '';
                            return '<span class="' + cls + '">' + currency(data) + '</span>';
                        }
                    }
                ],
                columnDefs: [
                    { searchable: false, targets: [0, 1, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13] },
                    { orderable: false, targets: [0, 1] }
                ],
                order: [[6, 'desc']],
                language: {
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                    infoEmpty: 'Tidak ada data',
                    zeroRecords: 'Data piutang tidak ditemukan',
                    processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Memuat data...'
                }
            });

            const reloadWithFilters = () => {
                syncExportUrls();
                table.ajax.reload(null, true);
            };

            $('#btnFilter').on('click', reloadWithFilters);

            $('#filterForm input').on('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    reloadWithFilters();
                }
            });

            $('#btnResetFilter').on('click', function() {
                $('#customerCode').val('').trigger('change');
                $('#status').val('outstanding').trigger('change');
                startPicker.clear();
                endPicker.clear();
                startPicker.set('maxDate', null);
                endPicker.set('minDate', null);
                reloadWithFilters();
            });

            $('#btn-refresh-table').on('click', function() {
                $('#refresh-icon').addClass('mdi-spin');
                table.ajax.reload(() => $('#refresh-icon').removeClass('mdi-spin'), false);
            });

            syncExportUrls();
        });
    </script>
@endpush
