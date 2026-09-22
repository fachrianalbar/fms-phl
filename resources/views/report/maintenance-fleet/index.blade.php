@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Report',
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
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom-select2.css') }}">

    <style>
        /* Scoped table styling matching docs/table-standard.md */
        #dt {
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        #dt thead th {
            background-color: #f8fafc;
            color: #475569;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 13px 12px;
            border-bottom: 2px solid #e2e8f0;
            border-top: none;
            white-space: nowrap;
            vertical-align: middle;
        }

        #dt tbody td {
            padding: 11px 12px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            font-size: 12.5px;
            white-space: nowrap;
            vertical-align: middle;
        }

        #dt tbody tr {
            transition: background-color 0.15s ease;
        }

        #dt tbody tr:hover {
            background-color: #f8fafc !important;
        }

        .btn-icon {
            border-radius: 8px !important;
            padding: 6px 10px;
            font-size: 13px;
            transition: all 0.2s ease;
        }

        .btn-icon:hover {
            transform: translateY(-1px);
        }

        /* Select2 Theme Matching */
        .select2-container--default .select2-selection--single {
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            height: 38px !important;
            display: flex !important;
            align-items: center !important;
            background-color: #ffffff !important;
            transition: all 0.2s ease !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #334155 !important;
            font-size: 13px !important;
            line-height: normal !important;
            padding-left: 10px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #94a3b8 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
            right: 8px !important;
        }

        .select2-container--default.select2-container--open .select2-selection--single,
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #818cf8 !important;
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.15) !important;
        }

        .select2-dropdown {
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
            overflow: hidden !important;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #4f46e5 !important;
            color: #ffffff !important;
        }

        #dt_wrapper .dataTables_filter input {
            border-radius: 8px;
            font-size: 12px;
        }

        #dt_wrapper .dataTables_length select {
            border-radius: 8px;
            font-size: 12px;
        }

        #dt_wrapper .dataTables_info,
        #dt_wrapper .dataTables_length,
        #dt_wrapper .dataTables_filter {
            color: #64748b;
            font-size: 12px;
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
                        <i class="mdi mdi-truck-check-outline text-primary fs-20"></i>
                        {{ $title }} Data
                    </h4>
                    <small class="text-muted">Ringkasan pemeliharaan armada kendaraan, kuantitas item, dan total biaya</small>
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

                {{-- Filter Bar --}}
                <div class="card border-0 mb-4"
                    style="background: #f8fafc; border: 1px solid #e2e8f0 !important; border-radius: 12px;">
                    <div class="card-body p-3">
                        <form id="filterForm">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold text-muted mb-1" style="font-size: 12px;"
                                        for="fleetCode">Nomor Polisi (Plat)</label>
                                    <select class="form-select form-select-sm select2-filter" name="fleetCode"
                                        id="fleetCode" style="width: 100%;">
                                        <option value="">Semua Armada</option>
                                        @foreach ($fleet as $item)
                                            <option value="{{ $item->code }}">{{ $item->plateNumber }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-semibold text-muted mb-1" style="font-size: 12px;"
                                        for="fleetCompanyCode">Fleet Company</label>
                                    <select class="form-select form-select-sm select2-filter" name="fleetCompanyCode"
                                        id="fleetCompanyCode" style="width: 100%;">
                                        <option value="">Semua Perusahaan</option>
                                        @foreach ($fleetCompany as $item)
                                            <option value="{{ $item->code }}">
                                                {{ $item->name }}{{ $item->type ? ' (' . $item->type . ')' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label fw-semibold text-muted mb-1" style="font-size: 12px;"
                                        for="startDate">Dari Tanggal</label>
                                    <input class="form-control form-control-sm" name="startDate" id="startDate"
                                        type="text" placeholder="Pilih Tanggal Mulai"
                                        style="border-radius: 8px; background: #fff;">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label fw-semibold text-muted mb-1" style="font-size: 12px;"
                                        for="endDate">Sampai Tanggal</label>
                                    <input class="form-control form-control-sm" name="endDate" id="endDate"
                                        type="text" placeholder="Pilih Tanggal Akhir"
                                        style="border-radius: 8px; background: #fff;">
                                </div>

                                <div class="col-md-2 d-flex gap-2">
                                    <button class="btn btn-sm btn-primary w-100" style="border-radius: 8px; font-weight: 600;"
                                        type="submit" id="btnFilter">
                                        <i class="mdi mdi-filter me-1"></i> Filter
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px;"
                                        id="btnResetFilter" title="Reset Filter">
                                        <i class="mdi mdi-refresh"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Table --}}
                <div class="table-responsive custom-scrollbar">
                    <table class="table align-middle w-100 mb-0" id="dt">
                        <thead>
                            <tr>
                                <th style="width: 6%;" class="text-center">Aksi</th>
                                <th style="width: 5%;" class="text-center">No</th>
                                <th style="width: 15%;">Nomor Polisi</th>
                                <th style="width: 24%;">Fleet Company</th>
                                <th style="width: 10%;" class="text-center">Tipe</th>
                                <th style="width: 12%;" class="text-end">Total MNT</th>
                                <th style="width: 12%;" class="text-end">Total Qty</th>
                                <th style="width: 16%;" class="text-end">Total Biaya</th>
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
    <script src="{{ asset('assets/libs/datatables.net-keytable-bs5/js/keyTable.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-select/js/dataTables.select.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-select-bs5/js/select.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2-custom.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/custom-flatpickr.js') }}"></script>
    <script>
        let filterStartPicker;
        let filterEndPicker;

        $(document).ready(function() {
            // Select2 initialization
            $('#fleetCode').select2({
                placeholder: 'Semua Armada',
                allowClear: true
            });

            $('#fleetCompanyCode').select2({
                placeholder: 'Semua Perusahaan',
                allowClear: true
            });

            // Flatpickr initialization
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
                onChange: function(selectedDates, dateStr) {
                    if (filterStartPicker) filterStartPicker.set('maxDate', dateStr || null);
                }
            });

            function syncExportUrls() {
                const params = new URLSearchParams();
                const fleetCode = $('#fleetCode').val();
                const fleetCompanyCode = $('#fleetCompanyCode').val();
                const startDate = $('#startDate').val();
                const endDate = $('#endDate').val();

                if (fleetCode) params.set('fleetCode', fleetCode);
                if (fleetCompanyCode) params.set('fleetCompanyCode', fleetCompanyCode);
                if (startDate) params.set('startDate', startDate);
                if (endDate) params.set('endDate', endDate);

                const qs = params.toString() ? '?' + params.toString() : '';
                $('#export-pdf').attr('href', "{{ route($view . 'pdf-maintenance-fleet') }}" + qs);
                $('#export-excel').attr('href', "{{ route($view . 'excel-maintenance-fleet') }}" + qs);
            }

            const table = $('#dt').DataTable({
                processing: true,
                serverSide: true,
                destroy: true,
                pageLength: 25,
                ajax: {
                    url: "{{ route('dt.maintenance-fleet') }}",
                    data: function(d) {
                        d.fleetCode = $('#fleetCode').val();
                        d.fleetCompanyCode = $('#fleetCompanyCode').val();
                        d.startDate = $('#startDate').val();
                        d.endDate = $('#endDate').val();
                    }
                },
                columns: [
                    { data: 'action', className: 'text-center align-middle' },
                    { data: 'DT_RowIndex', className: 'text-center align-middle' },
                    { data: 'plateNumber', className: 'align-middle fw-bold text-dark font-monospace' },
                    { data: 'fleetCompanyName', className: 'align-middle' },
                    {
                        data: 'fleetCompanyType',
                        className: 'align-middle text-center',
                        render: function(data) {
                            if (!data) return '-';
                            const badgeClass = data.toLowerCase() === 'internal'
                                ? 'bg-primary-subtle text-primary'
                                : 'bg-warning-subtle text-warning';
                            return '<span class="badge ' + badgeClass + ' px-2 py-1" style="border-radius: 6px; font-weight: 600; font-size: 11px;">' + data + '</span>';
                        }
                    },
                    { data: 'totalMaintenance', className: 'text-end align-middle font-monospace' },
                    { data: 'totalQty', className: 'text-end align-middle font-monospace' },
                    {
                        data: 'totalCost',
                        className: 'text-end align-middle font-monospace fw-bold text-dark',
                        render: function(data) {
                            return data ? 'Rp ' + data : 'Rp 0';
                        }
                    }
                ],
                columnDefs: [
                    { searchable: false, targets: [0, 1, 5, 6, 7] },
                    { orderable: false, targets: [0, 1] }
                ],
                order: [
                    [2, 'asc']
                ],
                language: {
                    search: 'Cari (Plat / Perusahaan):',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ armada',
                    infoEmpty: 'Tidak ada data armada',
                    zeroRecords: 'Data armada tidak ditemukan',
                    processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Memuat data...'
                }
            });

            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                syncExportUrls();
                table.ajax.reload();
            });

            $('#btnResetFilter').on('click', function() {
                $('#fleetCode').val('').trigger('change');
                $('#fleetCompanyCode').val('').trigger('change');
                if (filterStartPicker) filterStartPicker.clear();
                if (filterEndPicker) filterEndPicker.clear();
                syncExportUrls();
                table.ajax.reload();
            });
        });
    </script>
@endpush
