@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Supplier',
    'secondSegment' => $title,
])

@push('style')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom-select2.css') }}">
    @include('purchasing.purchase.partials.table-style')
    <style>
        .report-note {
            border-left: 3px solid #f59e0b;
            background: #fffbeb;
            color: #78350f;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 12px;
        }
        .aging-table th:nth-child(1),
        .aging-table td:nth-child(1) {
            width: 64px !important;
            min-width: 64px !important;
            text-align: center !important;
        }
        .aging-table th:nth-child(2),
        .aging-table td:nth-child(2) {
            width: 52px !important;
            min-width: 52px !important;
            text-align: center !important;
        }
        .aging-table th:nth-child(3),
        .aging-table td:nth-child(3) { text-align: left !important; }
        .aging-table th:nth-child(n+4),
        .aging-table td:nth-child(n+4) { text-align: right !important; }
        .aging-table tbody tr { cursor: default; }
        .aging-legend { font-size: 11.5px; color: #64748b; }
    </style>
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center shadow-sm text-white"
                    style="width: 48px; height: 48px; background: linear-gradient(135deg, #0f766e 0%, #115e59 100%) !important;">
                    <i class="mdi mdi-chart-timeline-variant fs-24"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-0 text-dark">{{ $title }}</h4>
                    <p class="text-muted mb-0 fs-12">Pantau sisa hutang, jatuh tempo, dan umur hutang per supplier.</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route($view . 'pdf-supplier') }}" target="_blank" id="export-pdf"
                    class="btn btn-outline-danger btn-sm rounded-pill px-3 shadow-sm">
                    <i class="mdi mdi-file-pdf-box me-1"></i> PDF
                </a>
                <a href="{{ route($view . 'excel-supplier') }}" target="_blank" id="export-excel"
                    class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-sm">
                    <i class="mdi mdi-file-excel me-1"></i> Excel
                </a>
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm" id="btn-refresh-table">
                    <i class="mdi mdi-refresh me-1" id="refresh-icon"></i> Refresh
                </button>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Total Sisa Hutang</div>
                            <div class="stat-value text-dark" id="stat-total-remaining">Rp {{ number_format($stats['totalRemaining'], 0, ',', '.') }}</div>
                        </div>
                        <div class="stat-icon-wrapper bg-primary-subtle text-primary"><i class="mdi mdi-cash-multiple"></i></div>
                    </div>
                    <div class="stat-desc"><strong id="stat-unpaid-count">{{ number_format($stats['unpaidCount'], 0, ',', '.') }}</strong> PO belum lunas</div>
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
                    <div class="stat-desc"><strong id="stat-due-soon-count">{{ number_format($stats['dueSoonCount'], 0, ',', '.') }}</strong> PO perlu disiapkan</div>
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
                    <div class="stat-desc text-danger"><strong id="stat-overdue-count">{{ number_format($stats['overdueCount'], 0, ',', '.') }}</strong> PO melewati jatuh tempo</div>
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
                    <div class="stat-desc"><strong id="stat-supplier-count">{{ number_format($stats['supplierCount'], 0, ',', '.') }}</strong> supplier dalam laporan</div>
                </div>
            </div>
        </div>

        <div class="table-container-card mb-4">
            <div class="table-top-bar">
                <form id="filterForm" class="row g-3 align-items-end">
                    <div class="col-12 col-md-5">
                        <label class="form-label fw-semibold" for="supplierCode">Supplier</label>
                        <select class="js-example-basic-single" name="supplierCode" id="supplierCode">
                            <option value="">Semua supplier</option>
                            @foreach ($supplier as $item)
                                <option value="{{ $item->code }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label fw-semibold" for="startDate">Tanggal Awal</label>
                        <input class="form-control" name="startDate" id="startDate" type="date">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label fw-semibold" for="endDate">Tanggal Akhir</label>
                        <input class="form-control" name="endDate" id="endDate" type="date">
                    </div>
                    <div class="col-12 col-md-3 d-flex gap-2">
                        <button class="btn btn-primary flex-grow-1" type="submit"><i class="mdi mdi-filter-outline me-1"></i>Terapkan</button>
                        <button class="btn btn-light" type="button" id="btn-reset-filter" title="Reset filter"><i class="mdi mdi-refresh"></i></button>
                    </div>
                </form>
            </div>

            <div class="px-3 pt-3">
                <div class="report-note">
                    <i class="mdi mdi-information-outline me-1"></i>
                    Aging dihitung dari tanggal jatuh tempo. Due date yang tidak valid menggunakan tanggal pembelian sebagai estimasi.
                </div>
            </div>

            <div class="p-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                    <div>
                        <h6 class="fw-bold mb-0">Aging Hutang Per Supplier</h6>
                        <span class="aging-legend">Bucket 0-30 sampai &gt;90 hanya memuat hutang yang sudah jatuh tempo.</span>
                    </div>
                </div>
                <div class="table-responsive custom-scrollbar">
                    <table class="table purchase-table aging-table w-100 nowrap" id="dt-supplier-summary">
                        <thead>
                            <tr>
                                <th>Detail</th>
                                <th>No</th>
                                <th>Supplier</th>
                                <th>PO</th>
                                <th>Total Tagihan</th>
                                <th>Terbayar</th>
                                <th>Sisa Hutang</th>
                                <th>JT ≤ 7 Hari</th>
                                <th>Overdue</th>
                                <th>0-30 Hari</th>
                                <th>31-60 Hari</th>
                                <th>61-90 Hari</th>
                                <th>&gt;90 Hari</th>
                                <th>Belum Lunas</th>
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
            const currency = value => 'Rp ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(value || 0);
            const updateStats = stats => {
                if (!stats) return;
                $('#stat-total-remaining').text(currency(stats.totalRemaining));
                $('#stat-unpaid-count').text(new Intl.NumberFormat('id-ID').format(stats.unpaidCount || 0));
                $('#stat-due-soon').text(currency(stats.dueSoonAmount));
                $('#stat-due-soon-count').text(new Intl.NumberFormat('id-ID').format(stats.dueSoonCount || 0));
                $('#stat-overdue').text(currency(stats.overdueAmount));
                $('#stat-overdue-count').text(new Intl.NumberFormat('id-ID').format(stats.overdueCount || 0));
                $('#stat-aging-over-90').text(currency(stats.agingOver90));
                $('#stat-supplier-count').text(new Intl.NumberFormat('id-ID').format(stats.supplierCount || 0));
            };

            $('#supplierCode').select2({ width: '100%', placeholder: 'Semua supplier', allowClear: true });

            const table = $('#dt-supplier-summary').DataTable({
                processing: true,
                serverSide: false,
                destroy: true,
                scrollX: true,
                ajax: {
                    url: "{{ route('dt.report-supplier') }}",
                    data: function(d) {
                        d.supplierCode = $('#supplierCode').val();
                        d.startDate = $('#startDate').val();
                        d.endDate = $('#endDate').val();
                    },
                    dataSrc: function(json) {
                        updateStats(json.stats);
                        return json.data;
                    }
                },
                columns: [
                    { data: 'action', className: 'text-center align-middle', width: '64px' },
                    { data: 'DT_RowIndex', className: 'text-center align-middle', width: '52px' },
                    { data: 'supplierName', className: 'align-middle' },
                    { data: 'totalPurchase', className: 'text-end align-middle' },
                    { data: 'totalBilling', className: 'text-end align-middle' },
                    { data: 'totalPaid', className: 'text-end align-middle' },
                    { data: 'totalRemaining', className: 'text-end align-middle' },
                    { data: 'dueSoonAmount', className: 'text-end align-middle' },
                    { data: 'overdueAmount', className: 'text-end align-middle' },
                    { data: 'aging0To30', className: 'text-end align-middle' },
                    { data: 'aging31To60', className: 'text-end align-middle' },
                    { data: 'aging61To90', className: 'text-end align-middle' },
                    { data: 'agingOver90', className: 'text-end align-middle' },
                    { data: 'unpaidCount', className: 'text-end align-middle' }
                ],
                columnDefs: [
                    { searchable: false, targets: [0, 1, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13] },
                    { orderable: false, targets: [0, 1] }
                ],
                order: [[8, 'desc']],
                drawCallback: function() {
                    const api = this.api();
                    const pageStart = api.page.info().start;

                    api.column(1, { page: 'current', search: 'applied', order: 'applied' })
                        .nodes()
                        .each(function(cell, index) {
                            cell.innerHTML = pageStart + index + 1;
                        });
                },
                language: { emptyTable: 'Belum ada data pembelian supplier pada periode ini.' }
            });

            const syncExportUrls = () => {
                const query = $('#filterForm').serialize();
                $('#export-pdf').attr('href', "{{ route($view . 'pdf-supplier') }}" + (query ? '?' + query : ''));
                $('#export-excel').attr('href', "{{ route($view . 'excel-supplier') }}" + (query ? '?' + query : ''));
            };

            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                syncExportUrls();
                table.ajax.reload();
            });

            $('#btn-reset-filter').on('click', function() {
                $('#supplierCode').val(null).trigger('change');
                $('#startDate, #endDate').val('');
                syncExportUrls();
                table.ajax.reload();
            });

            $('#btn-refresh-table').on('click', function() {
                $('#refresh-icon').addClass('mdi-spin');
                table.ajax.reload(() => $('#refresh-icon').removeClass('mdi-spin'), false);
            });
        });
    </script>
@endpush
