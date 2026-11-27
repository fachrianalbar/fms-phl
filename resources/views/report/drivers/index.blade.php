@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Report',
    'secondSegment' => $title,
])

@push('style')
    @include('report.partials.maintenance-report-style')
    <style>
        #dt-drivers tbody td.col-address {
            white-space: normal;
            min-width: 220px;
            max-width: 360px;
        }
    </style>
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center"
                style="border-color: #e2e8f0;">
                <div>
                    <h4 class="mb-1 fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="mdi mdi-account-tie-hat-outline text-primary fs-20"></i>
                        Laporan {{ $title }}
                    </h4>
                    <small class="text-muted">Daftar lengkap supir beserta tanggal masuk, NIK, No. SIM, alamat, nomor HP, dan masa kerja</small>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('report.drivers.excel') }}" target="_blank" id="export-excel"
                        class="btn btn-icon btn-sm bg-success-subtle" data-bs-toggle="tooltip" title="Export Excel">
                        <i class="mdi mdi-file-excel fs-14 text-success"></i>
                    </a>
                    <a href="{{ route('report.drivers.pdf') }}" target="_blank" id="export-pdf"
                        class="btn btn-icon btn-sm bg-danger-subtle" data-bs-toggle="tooltip" title="Export PDF">
                        <i class="mdi mdi-file-pdf-box fs-14 text-danger"></i>
                    </a>
                </div>
            </div>

            <div class="card-body p-4">
                @include('partials.alert')

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="summary-card summary-primary">
                            <i class="mdi mdi-account-group-outline float-end fs-24 opacity-50"></i>
                            <h5>Total Supir</h5>
                            <h3>{{ number_format($summary['total'], 0, ',', '.') }} <span class="fs-14 fw-normal opacity-75">Orang</span></h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="summary-card summary-success">
                            <i class="mdi mdi-account-check-outline float-end fs-24 opacity-50"></i>
                            <h5>Supir Aktif</h5>
                            <h3>{{ number_format($summary['active'], 0, ',', '.') }} <span class="fs-14 fw-normal opacity-75">Orang</span></h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="summary-card summary-danger">
                            <i class="mdi mdi-account-off-outline float-end fs-24 opacity-50"></i>
                            <h5>Supir Nonaktif</h5>
                            <h3>{{ number_format($summary['inactive'], 0, ',', '.') }} <span class="fs-14 fw-normal opacity-75">Orang</span></h3>
                        </div>
                    </div>
                </div>

                <div class="filter-card">
                    <button type="button" class="filter-card-header" data-bs-toggle="collapse"
                        data-bs-target="#driversReportFilterCollapse" aria-expanded="false"
                        aria-controls="driversReportFilterCollapse">
                        <span class="filter-card-heading">
                            <i class="mdi mdi-filter-variant"></i>
                            <strong>Filter Data</strong>
                            <small>Status supir dan periode tanggal masuk</small>
                        </span>
                        <i class="mdi mdi-chevron-down filter-card-chevron"></i>
                    </button>

                    <div class="collapse filter-collapse" id="driversReportFilterCollapse">
                        <div class="filter-collapse-body">
                            <div id="filterForm">
                                <div class="row g-3">
                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="status">Status Supir</label>
                                        <select class="form-select select2-filter" id="status" data-placeholder="Semua Status">
                                            <option value="">Semua Status</option>
                                            <option value="1">Aktif</option>
                                            <option value="0">Nonaktif</option>
                                        </select>
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="startDate">Tgl Masuk Dari</label>
                                        <input class="form-control filter-control" id="startDate" type="text"
                                            placeholder="Pilih tanggal mulai">
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="endDate">Tgl Masuk Sampai</label>
                                        <input class="form-control filter-control" id="endDate" type="text"
                                            placeholder="Pilih tanggal akhir">
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

                <div class="table-responsive custom-scrollbar">
                    <table class="table align-middle w-100 mb-0 maintenance-report-table" id="dt-drivers">
                        <thead>
                            <tr>
                                <th class="text-center">No</th>
                                <th>Nama Supir</th>
                                <th class="text-center">Tgl Masuk</th>
                                <th>NIK</th>
                                <th>SIM</th>
                                <th>Alamat</th>
                                <th>No Hp</th>
                                <th>Masa Kerja</th>
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
    @include('report.partials.maintenance-report-script')
    <script>
        $(document).ready(function() {
            const filter = initMaintenanceReportFilter(['status', 'startDate', 'endDate']);

            function syncExportUrls() {
                const qs = buildMaintenanceReportQuery(filter.getFilters());
                $('#export-excel').attr('href', "{{ route('report.drivers.excel') }}" + qs);
                $('#export-pdf').attr('href', "{{ route('report.drivers.pdf') }}" + qs);
            }

            const table = $('#dt-drivers').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                ajax: {
                    url: "{{ route('dt.report-drivers') }}",
                    data: function(d) {
                        Object.assign(d, filter.getFilters());
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', className: 'text-center', orderable: false, searchable: false },
                    { data: 'name', className: 'fw-semibold text-dark' },
                    { data: 'joinDate', className: 'text-center font-monospace' },
                    { data: 'nik', className: 'font-monospace' },
                    { data: 'sim', className: 'font-monospace' },
                    { data: 'address', className: 'col-address' },
                    { data: 'phone', className: 'font-monospace' },
                    { data: 'work_duration', className: 'fw-medium' }
                ],
                order: [[1, 'asc']],
                language: maintenanceReportLanguage('supir')
            });

            function reloadWithFilters() {
                syncExportUrls();
                table.ajax.reload(null, true);
            }

            $('#btnFilter').on('click', reloadWithFilters);
            $('#btnResetFilter').on('click', function() {
                filter.reset();
                reloadWithFilters();
            });

            syncExportUrls();
        });
    </script>
@endpush
