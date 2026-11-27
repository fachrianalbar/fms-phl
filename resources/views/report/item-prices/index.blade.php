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
                        <i class="mdi mdi-tag-multiple-outline text-primary fs-20"></i>
                        Laporan {{ $title }}
                    </h4>
                    <small class="text-muted">Daftar harga item barang &amp; jasa per suplier beserta tanggal input terakhir</small>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('report.item-prices.excel') }}" target="_blank" id="export-excel"
                        class="btn btn-icon btn-sm bg-success-subtle" data-bs-toggle="tooltip" title="Export Excel">
                        <i class="mdi mdi-file-excel fs-14 text-success"></i>
                    </a>
                    <a href="{{ route('report.item-prices.pdf') }}" target="_blank" id="export-pdf"
                        class="btn btn-icon btn-sm bg-danger-subtle" data-bs-toggle="tooltip" title="Export PDF">
                        <i class="mdi mdi-file-pdf-box fs-14 text-danger"></i>
                    </a>
                </div>
            </div>

            <div class="card-body p-4">
                @include('partials.alert')

                <div class="filter-card">
                    <button type="button" class="filter-card-header" data-bs-toggle="collapse"
                        data-bs-target="#itemPricesFilterCollapse" aria-expanded="false"
                        aria-controls="itemPricesFilterCollapse">
                        <span class="filter-card-heading">
                            <i class="mdi mdi-filter-variant"></i>
                            <strong>Filter Data</strong>
                            <small>Suplier, tipe item, dan periode tanggal input</small>
                        </span>
                        <i class="mdi mdi-chevron-down filter-card-chevron"></i>
                    </button>

                    <div class="collapse filter-collapse" id="itemPricesFilterCollapse">
                        <div class="filter-collapse-body">
                            <div id="filterForm">
                                <div class="row g-3">
                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="supplierCode">Suplier</label>
                                        <select class="form-select select2-filter" id="supplierCode" data-placeholder="Semua Suplier">
                                            <option value="">Semua Suplier</option>
                                            @foreach ($suppliers as $supplier)
                                                <option value="{{ $supplier->code }}">{{ $supplier->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="type">Tipe Item</label>
                                        <select class="form-select select2-filter" id="type" data-placeholder="Semua Tipe">
                                            <option value="">Semua Tipe</option>
                                            <option value="part" selected>Barang (Part)</option>
                                            <option value="jasa">Jasa</option>
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
                    <table class="table align-middle w-100 mb-0 maintenance-report-table" id="dt-item-prices">
                        <thead>
                            <tr>
                                <th class="text-center">No</th>
                                <th>Suplier</th>
                                <th>Item Barang</th>
                                <th class="text-center">Tgl Input</th>
                                <th class="text-end">Harga Item</th>
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
            const filter = initMaintenanceReportFilter(['supplierCode', 'type', 'startDate', 'endDate']);

            function syncExportUrls() {
                const qs = buildMaintenanceReportQuery(filter.getFilters());
                $('#export-excel').attr('href', "{{ route('report.item-prices.excel') }}" + qs);
                $('#export-pdf').attr('href', "{{ route('report.item-prices.pdf') }}" + qs);
            }

            const table = $('#dt-item-prices').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                ajax: {
                    url: "{{ route('dt.report-item-prices') }}",
                    data: function(d) {
                        Object.assign(d, filter.getFilters());
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', className: 'text-center', orderable: false, searchable: false },
                    { data: 'supplierName', className: 'fw-semibold text-dark' },
                    { data: 'itemName' },
                    { data: 'inputDate', className: 'text-center font-monospace' },
                    { data: 'price', className: 'text-end font-monospace fw-bold text-dark' }
                ],
                order: [[1, 'asc']],
                language: maintenanceReportLanguage('item barang')
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
