@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Administrator',
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

    <style>
        #dt-detail-activity pre {
            background-color: transparent !important;
            padding: 0 !important;
            margin: 0 !important;
            border: none !important;
            font-family: inherit !important;
            white-space: nowrap !important;
            display: inline !important;
        }
    </style>
@endpush

@section('content')
    <div class="col-sm-12">
                <div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
                    <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2"
                        style="border-color: #e2e8f0;">
                        <div>
                            <h4 class="mb-1 fw-bold text-dark d-flex align-items-center gap-2">
                                <i class="mdi mdi-history text-primary fs-20"></i>
                                {{ $title }} Data
                            </h4>
                            <small class="text-muted">Catatan riwayat aktivitas dan perubahan data oleh pengguna
                                sistem</small>
                        </div>

                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <a href="{{ route('administrator.activity-log.export-excel') }}" target="_blank"
                                    id="export-excel" class="btn btn-icon btn-sm bg-success-subtle" data-bs-toggle="tooltip"
                                    title="Export Excel">
                                    <i class="mdi mdi-file-excel fs-14 text-success"></i>
                                </a>
                                <a href="{{ route('administrator.activity-log.export-pdf') }}" target="_blank"
                                    id="export-pdf" class="btn btn-icon btn-sm bg-danger-subtle" data-bs-toggle="tooltip"
                                    title="Export PDF">
                                    <i class="mdi mdi-file-pdf-box fs-14 text-danger"></i>
                                </a>
                            </div>
                        </div>

                                        <div class="card-body p-4">
                                            @include('partials.alert')

                                                {{-- Filter Bar (§3 PHL, tertutup default) --}}
                                                <div class="filter-card">
                                                    <button type="button" class="filter-card-header"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#activityLogFilterCollapse" aria-expanded="false"
                                                        aria-controls="activityLogFilterCollapse">
                                                        <span class="filter-card-heading">
                                                            <i class="mdi mdi-filter-variant"></i>
                                                            <strong>Filter Data</strong>
                                                            <small>Gunakan filter data, rentang tanggal, aktivitas, aksi,
                                                                pengguna, atau peran</small>
                                                        </span>
                                                        <i class="mdi mdi-chevron-down filter-card-chevron"></i>
                                                    </button>

                                                        <div class="collapse filter-collapse"
                                                            id="activityLogFilterCollapse">
                                                            <div class="filter-collapse-body">
                                                                <div id="filterForm">
                                                                    <div class="row g-3">
                                                                        <div class="col-xl-3 col-md-6">
                                                                            <label class="filter-label"
                                                                                for="data">Data</label>
                                                                            <input class="form-control" name="data"
                                                                                id="data" type="text"
                                                                                placeholder="Cari isi data / properti...">
                                                                        </div>
                                                                        <div class="col-xl-3 col-md-6">
                                                                            <label class="filter-label" for="startDate">Dari
                                                                                Tanggal</label>
                                                                            <input class="form-control filter-control"
                                                                                name="startDate" id="startDate" type="text"
                                                                                placeholder="Pilih tanggal mulai">
                                                                        </div>
                                                                        <div class="col-xl-3 col-md-6">
                                                                            <label class="filter-label" for="endDate">Sampai
                                                                                Tanggal</label>
                                                                            <input class="form-control filter-control"
                                                                                name="endDate" id="endDate" type="text"
                                                                                placeholder="Pilih tanggal akhir">
                                                                        </div>
                                                                        <div class="col-xl-3 col-md-6">
                                                                            <label class="filter-label"
                                                                                for="activity">Activity</label>
                                                                            <select class="form-select select2-filter"
                                                                                name="activity" id="activity">
                                                                                <option value="">Semua Activity</option>
                                                                                @foreach ($activity as $item)
                                                                                    <option value="{{ $item }}">{{ $item }}
                                                                                    </option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-xl-3 col-md-6">
                                                                            <label class="filter-label"
                                                                                for="action">Action</label>
                                                                            <select class="form-select select2-filter"
                                                                                name="action" id="action">
                                                                                <option value="">Semua Action</option>
                                                                                @foreach ($action as $item)
                                                                                    <option value="{{ $item }}">{{ $item }}
                                                                                    </option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-xl-3 col-md-6">
                                                                            <label class="filter-label"
                                                                                for="causer_id">User</label>
                                                                            <select class="form-select select2-filter"
                                                                                name="causer_id" id="causer_id">
                                                                                <option value="">Semua User</option>
                                                                                @foreach ($user as $item)
                                                                                    <option value="{{ $item->id }}">
                                                                                        {{ $item->name }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-xl-3 col-md-6">
                                                                            <label class="filter-label"
                                                                                for="roleCode">Role</label>
                                                                            <select class="form-select select2-filter"
                                                                                name="roleCode" id="roleCode">
                                                                                <option value="">Semua Role</option>
                                                                                @foreach ($role as $item)
                                                                                    <option value="{{ $item->code }}">
                                                                                        {{ $item->name }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                        <div
                                                                            class="col-xl-3 col-md-6 d-flex align-items-end justify-content-md-end gap-2">
                                                                            <button
                                                                                class="btn btn-filter-primary flex-grow-1 flex-md-grow-0"
                                                                                type="button" id="btnFilter">
                                                                                <i class="mdi mdi-filter-outline"></i>
                                                                                Terapkan Filter
                                                                            </button>
                                                                            <button class="btn btn-filter-reset"
                                                                                type="button" id="btnResetFilter"
                                                                                data-bs-toggle="tooltip"
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
                                            <th class="text-center">#</th>
                                            <th>Activity</th>
                                            <th>Action</th>
                                            <th>User</th>
                                            <th>Role</th>
                                            <th>Data</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                        </div>
                    </div>
                </div>

                <div class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-xl" style="padding-left: 100px">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="myLargeModalLabel">Detail Data</h4>
                                <button class="btn-close py-0" type="button" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="card">
                                <div class="card-body col-md-12">
                                        <div class="table-responsive">
                                            <table class="display" id="dt-detail-activity">
                                                <thead id="properties-table-head"></thead>
                                                <tbody id="properties-table-body"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <form id="delete-form" method="post">
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
                    <script
                        src="{{ asset('assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>
                    <script src="{{ asset('assets/libs/datatables.net-select/js/dataTables.select.min.js') }}"></script>
                    <script src="{{ asset('assets/libs/datatables.net-select-bs5/js/select.bootstrap5.min.js') }}"></script>
                    <script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>
                    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
                    <script src="{{ asset('assets/js/select2/select2-custom.js') }}"></script>
                    <script src="{{ asset('assets/libs/flatpickr/flatpickr.min.js') }}"></script>

                    <script>
                        $(document).on('click', '.btn-show-detail', function () {
                            const encoded = $(this).attr('data-properties');
                            const decoded = $("<textarea/>").html(encoded).text();

                            try {
                                const firstParse = JSON.parse(decoded);
                                const data = typeof firstParse === 'string' ? JSON.parse(firstParse) : firstParse;
                                const properties = data.attributes ?? data;

                                let headRow = '<tr>';
                                let bodyRow = '<tr>';

                                $.each(properties, function (key, value) {
                                    headRow += `<th>${key}</th>`;
                                    bodyRow += `<td><pre class="mb-0">${typeof value === 'object' ? JSON.stringify(value, null, 2) : value}</pre></td>`;
                                });

                                headRow += '</tr>';
                                bodyRow += '</tr>';

                                $('#properties-table-head').html(headRow);
                                $('#properties-table-body').html(bodyRow);

                                $('#dt-detail-activity').DataTable();
                            } catch (e) {
                                console.error('Failed to parse JSON from data-properties:', e);
                                $('#properties-table-head').html('<tr><th>Error</th></tr>');
                                $('#properties-table-body').html('<tr><td>Invalid JSON Data</td></tr>');
                            }
                        });

                    $(document).ready(function() {
                    let filterStartPicker;
                    let filterEndPicker;

                    $('.select2-filter').select2({
                    placeholder: 'Semua pilihan',
                    allowClear: true,
                    width: '100%'
                    });

                            filterStartPicker = flatpickr('#startDate', {
                                dateFormat: 'Y-m-d',
                                allowInput: true,
                                onChange: function (selectedDates, dateStr) {
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
                                    data: $('#data').val() || '',
                                    activity: $('#activity').val() || '',
                                    action: $('#action').val() || '',
                                    roleCode: $('#roleCode').val() || '',
                                    causer_id: $('#causer_id').val() || '',
                                    startDate: $('#startDate').val() || '',
                                    endDate: $('#endDate').val() || ''
                                };
                            }

                            function syncExportUrls() {
                                const params = new URLSearchParams();
                                Object.entries(getFilters()).forEach(function (entry) {
                                    if (entry[1]) params.set(entry[0], entry[1]);
                                });
                                const query = params.toString() ? '?' + params.toString() : '';
                                $('#export-excel').attr('href', "{{ route('administrator.activity-log.export-excel') }}" + query);
                                $('#export-pdf').attr('href', "{{ route('administrator.activity-log.export-pdf') }}" + query);
                            }

                            const table = $('#dt').DataTable({
                                "processing": true,
                                "serverSide": true,
                                "destroy": true,
                                "ajax": {
                                    "url": "{{ route('dt.activity-log') }}",
                                    "data": function (d) {
                                        Object.assign(d, getFilters());
                                    }
                                },
                        "columns": [
                                    { "data": 'DT_RowIndex', "className": 'text-center align-middle' },
                                    { "data": 'log_name', "className": 'align-middle' },
                                    { "data": 'description', "className": 'align-middle' },
                                    { "data": 'user.name', "className": 'align-middle', "defaultContent": "-" },
                                    { "data": 'user.role.name', "className": 'align-middle', "defaultContent": "-" },
                                    { "data": 'properties', "className": 'align-middle' },
                                    { "data": 'created_at', "className": 'align-middle font-monospace' }
                                ],
                                    "columnDefs": [
                                    { "searchable": false, "targets": [0, 4, 5, 6] },
                                    { "orderable": false, "targets": [0, 1, 2, 3, 4, 5, 6] }
                                ]
                    });

                        function reloadWithFilters() {
                            syncExportUrls();
                            table.ajax.reload(null, true);
                        }

                            $('#btnFilter').on('click', reloadWithFilters);
                            $('#data').on('keydown', function (e) {
                                if (e.key === 'Enter') {
                                    e.preventDefault();
                                    reloadWithFilters();
                                }
                            });
                            $('#btnResetFilter').on('click', function () {
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