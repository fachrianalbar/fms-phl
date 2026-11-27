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
                        <i class="mdi mdi-map-marker-distance text-primary fs-20"></i>
                        Laporan {{ $title }}
                    </h4>
                    <small class="text-muted">Daftar tarif ongkos angkut per pabrik, asal, tujuan, harga vendor pribadi, dan vendor external</small>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('report.freight-rates.excel') }}" target="_blank" id="export-excel"
                        class="btn btn-icon btn-sm bg-success-subtle" data-bs-toggle="tooltip" title="Export Excel">
                        <i class="mdi mdi-file-excel fs-14 text-success"></i>
                    </a>
                    <a href="{{ route('report.freight-rates.pdf') }}" target="_blank" id="export-pdf"
                        class="btn btn-icon btn-sm bg-danger-subtle" data-bs-toggle="tooltip" title="Export PDF">
                        <i class="mdi mdi-file-pdf-box fs-14 text-danger"></i>
                    </a>
                </div>
            </div>

            <div class="card-body p-4">
                @include('partials.alert')

                <div class="filter-card">
                    <button type="button" class="filter-card-header" data-bs-toggle="collapse"
                        data-bs-target="#freightRatesFilterCollapse" aria-expanded="false"
                        aria-controls="freightRatesFilterCollapse">
                        <span class="filter-card-heading">
                            <i class="mdi mdi-filter-variant"></i>
                            <strong>Filter Data</strong>
                            <small>Pabrik, lokasi asal/tujuan, vendor external, dan periode input</small>
                        </span>
                        <i class="mdi mdi-chevron-down filter-card-chevron"></i>
                    </button>

                    <div class="collapse filter-collapse" id="freightRatesFilterCollapse">
                        <div class="filter-collapse-body">
                            <div id="filterForm">
                                <div class="row g-3">
                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="customerCode">Pabrik</label>
                                        <select class="form-select select2-filter" id="customerCode" data-placeholder="Semua Pabrik">
                                            <option value="">Semua Pabrik</option>
                                            @foreach ($customers as $customer)
                                                <option value="{{ $customer->code }}">{{ $customer->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="originLocationCode">Asal</label>
                                        <select class="form-select select2-filter" id="originLocationCode" data-placeholder="Semua Asal">
                                            <option value="">Semua Asal</option>
                                            @foreach ($locations as $location)
                                                <option value="{{ $location->code }}">{{ $location->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="destinationLocationCode">Tujuan</label>
                                        <select class="form-select select2-filter" id="destinationLocationCode" data-placeholder="Semua Tujuan">
                                            <option value="">Semua Tujuan</option>
                                            @foreach ($locations as $location)
                                                <option value="{{ $location->code }}">{{ $location->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="fleetCompanyId">Vendor External</label>
                                        <select class="form-select select2-filter" id="fleetCompanyId" data-placeholder="Semua Vendor">
                                            <option value="">Semua Vendor</option>
                                            @foreach ($vendors as $vendor)
                                                <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="startDate">Tgl Input Dari</label>
                                        <input class="form-control filter-control" id="startDate" type="text"
                                            placeholder="Pilih tanggal mulai">
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="endDate">Tgl Input Sampai</label>
                                        <input class="form-control filter-control" id="endDate" type="text"
                                            placeholder="Pilih tanggal akhir">
                                    </div>

                                    <div class="col-xl-6 col-md-12 d-flex align-items-end justify-content-md-end gap-2">
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
                    <table class="table align-middle w-100 mb-0 maintenance-report-table" id="dt-freight-rates">
                        <thead>
                            <tr>
                                <th class="text-center">No</th>
                                <th>Pabrik</th>
                                <th>Asal</th>
                                <th>Tujuan</th>
                                <th class="text-center">Tgl Input</th>
                                <th class="text-end">Harga Pabrik</th>
                                <th class="text-end">Harga Vendor Pribadi</th>
                                <th>Nama Vendor</th>
                                <th class="text-end">Harga Vendor External</th>
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
            const filter = initMaintenanceReportFilter([
                'customerCode',
                'originLocationCode',
                'destinationLocationCode',
                'fleetCompanyId',
                'startDate',
                'endDate'
            ]);

            function syncExportUrls() {
                const qs = buildMaintenanceReportQuery(filter.getFilters());
                $('#export-excel').attr('href', "{{ route('report.freight-rates.excel') }}" + qs);
                $('#export-pdf').attr('href', "{{ route('report.freight-rates.pdf') }}" + qs);
            }

            const table = $('#dt-freight-rates').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                ajax: {
                    url: "{{ route('dt.report-freight-rates') }}",
                    data: function(d) {
                        Object.assign(d, filter.getFilters());
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', className: 'text-center', orderable: false, searchable: false },
                    { data: 'customerName', className: 'fw-semibold text-dark' },
                    { data: 'originName' },
                    { data: 'destinationName' },
                    { data: 'inputDate', className: 'text-center font-monospace' },
                    { data: 'price', className: 'text-end font-monospace fw-bold text-dark' },
                    { data: 'personalVendorPrice', className: 'text-end font-monospace' },
                    { data: 'vendorName' },
                    { data: 'externalVendorPrice', className: 'text-end font-monospace fw-semibold text-primary' }
                ],
                order: [[1, 'asc']],
                language: maintenanceReportLanguage('ongkos angkut')
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
