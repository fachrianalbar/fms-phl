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
                        <i class="mdi mdi-cube-outline text-primary fs-20"></i>
                        {{ $title }} Data
                    </h4>
                    <small class="text-muted">Daftar master material / jenis muatan barang</small>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <a href="{{ route('master.material.export-excel') }}" target="_blank" id="export-excel"
                        class="btn btn-icon btn-sm bg-success-subtle" data-bs-toggle="tooltip" title="Export Excel">
                        <i class="mdi mdi-file-excel fs-14 text-success"></i>
                    </a>
                    <a href="{{ route('master.material.export-pdf') }}" target="_blank" id="export-pdf"
                        class="btn btn-icon btn-sm bg-danger-subtle" data-bs-toggle="tooltip" title="Export PDF">
                        <i class="mdi mdi-file-pdf-box fs-14 text-danger"></i>
                    </a>
                    <a href="{{ route($view . 'create') }}" class="btn btn-sm btn-primary d-flex align-items-center gap-1"
                        style="border-radius: 8px; font-weight: 600; padding: 7px 14px;">
                        <i class="mdi mdi-plus"></i> {{ __('general.add_data') }}
                    </a>
                </div>
            </div>

            <div class="card-body p-4">
                @include('partials.alert')

                {{-- Filter Bar (§3 PHL, tertutup default) --}}
                <div class="filter-card">
                    <button type="button" class="filter-card-header" data-bs-toggle="collapse"
                        data-bs-target="#materialFilterCollapse" aria-expanded="false"
                        aria-controls="materialFilterCollapse">
                        <span class="filter-card-heading">
                            <i class="mdi mdi-filter-variant"></i>
                            <strong>Filter Data</strong>
                            <small>Gunakan filter kode, nama material, atau tanggal dibuat</small>
                        </span>
                        <i class="mdi mdi-chevron-down filter-card-chevron"></i>
                    </button>

                    <div class="collapse filter-collapse" id="materialFilterCollapse">
                        <div class="filter-collapse-body">
                            <div id="filterForm">
                                <div class="row g-3">
                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="keyword">Kode / Nama Material</label>
                                        <input class="form-control" name="keyword" id="keyword" type="text"
                                            placeholder="Cari kode atau nama material...">
                                    </div>
                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="startDate">Dari Tanggal</label>
                                        <input class="form-control filter-control" name="startDate" id="startDate"
                                            type="text" placeholder="Pilih tanggal mulai">
                                    </div>
                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="endDate">Sampai Tanggal</label>
                                        <input class="form-control filter-control" name="endDate" id="endDate" type="text"
                                            placeholder="Pilih tanggal akhir">
                                    </div>
                                    <div class="col-xl-3 col-md-6 d-flex align-items-end justify-content-md-end gap-2">
                                        <button class="btn btn-filter-primary flex-grow-1 flex-md-grow-0" type="button"
                                            id="btnFilter">
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
                    <table class="table align-middle w-100 mb-0" id="dt">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 80px;">#</th>
                                <th class="text-center" style="width: 60px;">No</th>
                                <th>Nama Material</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
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
    <script src="{{ asset('assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-select/js/dataTables.select.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-select-bs5/js/select.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/libs/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>

    <script>
        $(document).ready(function () {
                let filterStartPicker;
                let filterEndPicker;

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
                    keyword: $('#keyword').val() || '',
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
                    $('#export-excel').attr('href', "{{ route('master.material.export-excel') }}" + query);
                    $('#export-pdf').attr('href', "{{ route('master.material.export-pdf') }}" + query);
                }

                const table = $('#dt').DataTable({
                    "processing": true,
                    "serverSide": true,
                    "destroy": true,
                    "ajax": {
                        "url": "{{ route('dt.material') }}",
                        "data": function (d) {
                            Object.assign(d, getFilters());
                        }
                    },
                        "columns": [
                        { "data": 'action', "className": 'text-center align-middle' },
                        { "data": 'DT_RowIndex', "className": 'text-center align-middle' },
                        { "data": 'name', "className": 'align-middle fw-semibold text-dark' }
                    ],
                    "columnDefs": [
                            { "searchable": false, "targets": [0, 1] },
                            { "orderable": false, "targets": [0, 1] }
                        ],
            "order": [[2, 'asc']]
        });

        function reloadWithFilters() {
            syncExportUrls();
            table.ajax.reload(null, true);
        }

        $('#btnFilter').on('click', reloadWithFilters);
        $('#keyword').on('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                reloadWithFilters();
            }
        });
        $('#btnResetFilter').on('click', function () {
            $('#filterForm').find('input').val('');
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

        function deleteData(uuid) {
            var url = '{{ route('master.material.index') }}/' + uuid;
            $('#delete-form').attr('action', url);

            swal({
                title: "{{ __('general.are_you_sure') }}",
                text: "{{ __('general.want_to_delete_this_data') }}",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    $('#delete-form').submit();
                } else {
                    swal("{{ __('general.your_data_is_save') }}");
                }
            });
        }
    </script>
@endpush