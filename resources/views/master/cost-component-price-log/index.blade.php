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
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom-select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">
    @include('master.partials.table-style')
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
            {{-- Card Header --}}
            <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2"
                style="border-color: #e2e8f0;">
                <div>
                    <h4 class="mb-1 fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="mdi mdi-history text-primary fs-20"></i>
                        {{ $title }} Data
                    </h4>
                    <small class="text-muted">Riwayat dan log perubahan harga komponen biaya armada</small>
                </div>

                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <a href="{{ route('master.cost-component-price-log.export-excel') }}" target="_blank"
                                        id="export-excel" class="btn btn-icon btn-sm bg-success-subtle"
                                        data-bs-toggle="tooltip" title="Export Excel">
                                        <i class="mdi mdi-file-excel fs-14 text-success"></i>
                                    </a>
                                    <a href="{{ route('master.cost-component-price-log.export-pdf') }}" target="_blank"
                                        id="export-pdf" class="btn btn-icon btn-sm bg-danger-subtle"
                                        data-bs-toggle="tooltip" title="Export PDF">
                                        <i class="mdi mdi-file-pdf-box fs-14 text-danger"></i>
                                    </a>
                                </div>
                            </div>

                        <div class="card-body p-4">
                            @include('partials.alert')

                                    {{-- Filter Bar (§3 PHL, tertutup default) --}}
                                    <div class="filter-card">
                                        <button type="button" class="filter-card-header" data-bs-toggle="collapse"
                                            data-bs-target="#costComponentPriceLogFilterCollapse" aria-expanded="false"
                                            aria-controls="costComponentPriceLogFilterCollapse">
                                            <span class="filter-card-heading">
                                                <i class="mdi mdi-filter-variant"></i>
                                                <strong>Filter Data</strong>
                                                <small>Gunakan filter komponen biaya, pengubah, atau rentang tanggal</small>
                                            </span>
                                            <i class="mdi mdi-chevron-down filter-card-chevron"></i>
                                        </button>

                                        <div class="collapse filter-collapse" id="costComponentPriceLogFilterCollapse">
                                            <div class="filter-collapse-body">
                                                <div id="filterForm">
                                                    <div class="row g-3">
                                                        <div class="col-xl-3 col-md-6">
                                                            <label class="filter-label" for="costComponentCode">Komponen
                                                                Biaya</label>
                                                            <select class="form-select select2-filter"
                                                                name="costComponentCode" id="costComponentCode">
                                                                <option value="">Semua Komponen</option>
                                                                @foreach ($costComponents as $comp)
                                                                    <option value="{{ $comp->code }}">{{ $comp->code }} -
                                                                        {{ $comp->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-xl-3 col-md-6">
                                                            <label class="filter-label" for="keyword">Diubah Oleh /
                                                                Catatan</label>
                                                            <input class="form-control" name="keyword" id="keyword"
                                                                type="text"
                                                                placeholder="Cari nama pengubah atau catatan...">
                                                        </div>
                                                        <div class="col-xl-3 col-md-6">
                                                            <label class="filter-label" for="startDate">Dari Tanggal</label>
                                                            <input class="form-control filter-control" name="startDate"
                                                                id="startDate" type="text"
                                                                placeholder="Pilih tanggal mulai">
                                                        </div>
                                                        <div class="col-xl-3 col-md-6">
                                                            <label class="filter-label" for="endDate">Sampai Tanggal</label>
                                                            <input class="form-control filter-control" name="endDate"
                                                                id="endDate" type="text" placeholder="Pilih tanggal akhir">
                                                        </div>
                                                        <div
                                                            class="col-12 d-flex align-items-end justify-content-end gap-2">
                                                            <button
                                                                class="btn btn-filter-primary flex-grow-1 flex-md-grow-0"
                                                                type="button" id="btnFilter">
                                                                <i class="mdi mdi-filter-outline"></i> Terapkan Filter
                                                            </button>
                                                            <button class="btn btn-filter-reset" type="button"
                                                                id="btnResetFilter" data-bs-toggle="tooltip"
                                                                title="Reset Filter">
                                                                <i class="mdi mdi-refresh"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="table-responsive custom-scrollbar">
                                        <table class="table align-middle w-100 mb-0" id="dt">
                                            <thead>
                                                <tr>
                                                    <th class="text-center" style="width: 60px;">No</th>
                                                    <th class="text-center">Tanggal</th>
                                                    <th>Kode Komponen</th>
                                                    <th>Nama Komponen</th>
                                                    <th class="text-end">Harga Lama</th>
                                                    <th class="text-end">Harga Baru</th>
                                                    <th>Diubah Oleh</th>
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
                                <script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
                                <script src="{{ asset('assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
                                <script src="{{ asset('assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
                                <script src="{{ asset('assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js') }}"></script>
                                <script
                                    src="{{ asset('assets/libs/datatables.net-keytable-bs5/js/keyTable.bootstrap5.min.js') }}"></script>
                                <script
                                    src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
                                <script
                                    src="{{ asset('assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>
                                <script src="{{ asset('assets/libs/datatables.net-select/js/dataTables.select.min.js') }}"></script>
                                <script src="{{ asset('assets/libs/datatables.net-select-bs5/js/select.bootstrap5.min.js') }}"></script>
                                <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
                                <script src="{{ asset('assets/js/select2/select2-custom.js') }}"></script>
                                <script src="{{ asset('assets/libs/flatpickr/flatpickr.min.js') }}"></script>
                                <script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>

                                <script>
                                    $(document).ready(function () {
                                        let filterStartPicker;
                                        let filterEndPicker;

                                $('.select2-filter').select2({
                                placeholder: 'Semua Komponen',
                                allowClear: true,
                                width: '100%'
                                });

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
                                    onChange: function (selectedDates, dateStr) {
                                        if (filterStartPicker) filterStartPicker.set('maxDate', dateStr || null);
                                    }
                            });

                    function getFilters() {
                                return {
                        costComponentCode: $('#costComponentCode').val() || '',
                    keyword: $('#keyword').val() || '',
                    startDate: $('#startDate').val() || '',
                    endDate: $('#endDate').val() || ''
                                };
                            }

                    function syncExportUrls() {
                                const params = new URLSearchParams();
                    Object.entries(getFilters()).forEach(function(entry) {
                                    if (entry[1]) params.set(entry[0], entry[1]);
                                });
                    const query = params.toString() ? '?' + params.toString() : '';
                    $('#export-excel').attr('href', "{{ route('master.cost-component-price-log.export-excel') }}" + query);
                    $('#export-pdf').attr('href', "{{ route('master.cost-component-price-log.export-pdf') }}" + query);
                            }

                    const table = $('#dt').DataTable({
                        "processing": true,
                    "serverSide": true,
                    "destroy": true,
                    "ajax": {
                        "url": "{{ route('dt.cost-component-price-log') }}",
                    "data": function(d) {
                        Object.assign(d, getFilters());
                                    }
                                },
                    "columns": [
                    {"data": 'DT_RowIndex', "className": 'text-center align-middle' },
                    {"data": 'formatted_date', "className": 'text-center align-middle font-monospace' },
                    {"data": 'costComponentCode', "className": 'align-middle font-monospace fw-semibold' },
                    {"data": 'costComponentName', "className": 'align-middle text-dark' },
                    {"data": 'formatted_old_price', "className": 'text-end align-middle font-monospace' },
                    {"data": 'formatted_new_price', "className": 'text-end align-middle font-monospace fw-bold text-dark' },
                    {"data": 'changedBy', "className": 'align-middle' }
                    ],
                    "columnDefs": [
                    {"searchable": false, "targets": [0] },
                    {"orderable": false, "targets": [0] }
                    ],
                    "order": [[1, 'desc']]
                            });

                    function reloadWithFilters() {
                        syncExportUrls();
                    table.ajax.reload(null, true);
                            }

                                $('#btnFilter').on('click', reloadWithFilters);
                                $('#keyword').on('keydown', function(e) {
                                if (e.key === 'Enter') {
                                e.preventDefault();
                                reloadWithFilters();
                                }
                                });
                                $('#btnResetFilter').on('click', function() {
                                $('#filterForm').find('input').val('');
                                $('.select2-filter').val('').trigger('change');
                                if (filterStartPicker) {
                                filterStartPicker.clear();
                                filterStartPicker.set('maxDate', null);
                                }
                                if (filterEndPicker) {
                                filterEndPicker.clear();
                                filterEndPicker.set('minDate', null);
                                }
                                reloadWithFilters();
                                });

                                syncExportUrls();
                                });
                                </script>
                @endpush