@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Report',
    'secondSegment' => $title,
])

@push('style')
    @include('report.partials.maintenance-report-style')
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center"
                style="border-color: #e2e8f0;">
                <div>
                    <h4 class="mb-1 fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="mdi mdi-truck-check-outline text-primary fs-20"></i>
                        {{ $title }} Data
                    </h4>
                    <small class="text-muted">Ringkasan perawatan per armada: jumlah transaksi, qty item, nilai pembayaran, dan utang ke supplier</small>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route($view . 'excel-maintenance-fleet') }}" target="_blank" id="export-excel"
                        class="btn btn-icon btn-sm bg-success-subtle" data-bs-toggle="tooltip" title="Export Excel">
                        <i class="mdi mdi-file-excel fs-14 text-success"></i>
                    </a>
                    <a href="{{ route($view . 'pdf-maintenance-fleet') }}" target="_blank" id="export-pdf"
                        class="btn btn-icon btn-sm bg-danger-subtle" data-bs-toggle="tooltip" title="Export PDF">
                        <i class="mdi mdi-file-pdf-box fs-14 text-danger"></i>
                    </a>
                </div>
            </div>

            <div class="card-body p-4">
                @include('partials.alert')

                <div class="filter-card">
                    <button type="button" class="filter-card-header" data-bs-toggle="collapse"
                        data-bs-target="#maintenanceFleetFilterCollapse" aria-expanded="false"
                        aria-controls="maintenanceFleetFilterCollapse">
                        <span class="filter-card-heading">
                            <i class="mdi mdi-filter-variant"></i>
                            <strong>Filter Data</strong>
                            <small>Armada, perusahaan, dan periode perawatan</small>
                        </span>
                        <i class="mdi mdi-chevron-down filter-card-chevron"></i>
                    </button>

                    <div class="collapse filter-collapse" id="maintenanceFleetFilterCollapse">
                        <div class="filter-collapse-body">
                            <div id="filterForm">
                                <div class="row g-3">
                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="fleetCode">No Polisi</label>
                                        <select class="form-select select2-filter" id="fleetCode" data-placeholder="Semua Armada">
                                            <option value="">Semua Armada</option>
                                            @foreach ($fleet as $item)
                                                <option value="{{ $item->code }}">{{ $item->plateNumber }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="fleetCompanyCode">Perusahaan</label>
                                        <select class="form-select select2-filter" id="fleetCompanyCode" data-placeholder="Semua Perusahaan">
                                            <option value="">Semua Perusahaan</option>
                                            @foreach ($fleetCompany as $item)
                                                <option value="{{ $item->code }}">
                                                    {{ $item->name }}{{ $item->type ? ' (' . $item->type . ')' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="startDate">Dari Tanggal</label>
                                        <input class="form-control filter-control" id="startDate" type="text"
                                            placeholder="Pilih tanggal mulai">
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="endDate">Sampai Tanggal</label>
                                        <input class="form-control filter-control" id="endDate" type="text"
                                            placeholder="Pilih tanggal akhir">
                                    </div>

                                    <div class="col-12 d-flex align-items-end justify-content-md-end gap-2">
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
                    <table class="table align-middle w-100 mb-0 maintenance-report-table" id="dt">
                        <thead>
                            <tr>
                                <th class="text-center">Aksi</th>
                                <th class="text-center">No</th>
                                <th>No Polisi</th>
                                <th>Perusahaan</th>
                                <th class="text-center">Type</th>
                                <th class="text-end">Total Maint</th>
                                <th class="text-end">Total Qty</th>
                                <th class="text-end">Total Pembayaran</th>
                                <th class="text-end">Utang Jth Tempo</th>
                                <th class="text-end">Total Utang</th>
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
            const filter = initMaintenanceReportFilter(['fleetCode', 'fleetCompanyCode', 'startDate', 'endDate']);
            const rupiah = function(data) {
                return 'Rp ' + (data || '0');
            };

            function syncExportUrls() {
                const qs = buildMaintenanceReportQuery(filter.getFilters());
                $('#export-excel').attr('href', "{{ route($view . 'excel-maintenance-fleet') }}" + qs);
                $('#export-pdf').attr('href', "{{ route($view . 'pdf-maintenance-fleet') }}" + qs);
            }

            const table = $('#dt').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                ajax: {
                    url: "{{ route('dt.maintenance-fleet') }}",
                    data: function(d) {
                        Object.assign(d, filter.getFilters());
                    }
                },
                columns: [
                    { data: 'action', className: 'text-center', orderable: false, searchable: false },
                    { data: 'DT_RowIndex', className: 'text-center', orderable: false, searchable: false },
                    { data: 'plateNumber', className: 'fw-bold text-dark font-monospace' },
                    { data: 'fleetCompanyName' },
                    {
                        data: 'fleetCompanyType',
                        className: 'text-center',
                        render: function(data) {
                            if (!data) return '-';
                            const badgeClass = data.toLowerCase() === 'internal'
                                ? 'bg-primary-subtle text-primary'
                                : 'bg-warning-subtle text-warning';
                            return '<span class="badge ' + badgeClass + ' px-2 py-1" style="border-radius: 6px; font-weight: 600; font-size: 11px;">' + data + '</span>';
                        }
                    },
                    { data: 'totalMaintenance', className: 'text-end font-monospace', searchable: false },
                    { data: 'totalQty', className: 'text-end font-monospace', searchable: false },
                    { data: 'totalCost', className: 'text-end font-monospace fw-bold text-dark', searchable: false, render: rupiah },
                    { data: 'utangJatuhTempo', className: 'text-end font-monospace fw-semibold text-danger', searchable: false, render: rupiah },
                    { data: 'totalUtang', className: 'text-end font-monospace fw-semibold text-warning', searchable: false, render: rupiah }
                ],
                order: [[2, 'asc']],
                language: maintenanceReportLanguage('armada')
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
