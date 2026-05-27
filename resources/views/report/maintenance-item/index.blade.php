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
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/sweetalert2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom-select2.css') }}">
    <style>
        .dataTables_wrapper {
            width: 100% !important;
            overflow: visible !important;
        }

        .table-responsive {
            overflow-x: auto !important;
            overflow-y: auto !important;
            max-height: 70vh !important;
        }

        table.table-maintenance-item {
            width: 100% !important;
            table-layout: auto !important;
        }

        table.table-maintenance-item td,
        table.table-maintenance-item th {
            word-wrap: break-word !important;
            overflow-wrap: break-word !important;
            white-space: normal !important;
        }
    </style>
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }}</h4>

                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-icon btn-sm bg-dark-subtle" data-bs-toggle="collapse"
                        data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                        <i class="mdi mdi-magnify fs-14 text-dark"></i>
                    </button>

                    <a href="{{ route($view . 'excel-maintenance-item') }}" target="_blank" id="export-excel"
                        class="btn btn-sm btn-success">
                        <i class="mdi mdi-file-excel"></i> Export Excel
                    </a>

                    <a href="{{ route($view . 'pdf-maintenance-item') }}" target="_blank" id="export-pdf"
                        class="btn btn-sm btn-danger">
                        <i class="mdi mdi-file-pdf"></i> Export PDF
                    </a>
                </div>
            </div>

            <div class="card-header border-top-0 pt-0">
                <div class="accordion-collapse collapse" id="filterCollapse" data-bs-parent="#simpleaccordion">
                    <div class="card card-body bg-light mb-0 col-md-12">
                        <form id="filterForm" class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="startDate">Tanggal Mulai</label>
                                <input class="form-control flatpickr" name="startDate" id="startDate" type="text"
                                    placeholder="Pilih Tanggal Mulai">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="endDate">Tanggal Selesai</label>
                                <input class="form-control flatpickr" name="endDate" id="endDate" type="text"
                                    placeholder="Pilih Tanggal Selesai">
                            </div>

                            <div class="col-md-4 mt-3">
                                <label class="form-label" for="fleetCode">Kendaraan (Plate Number)</label>
                                <select class="js-example-basic-single" name="fleetCode" id="fleetCode">
                                    <option selected value="">Pilih Kendaraan...</option>
                                    @foreach ($fleet as $item)
                                        <option value="{{ $item->code }}">{{ $item->plateNumber }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mt-3">
                                <label class="form-label" for="warehouseCode">Warehouse (Gudang)</label>
                                <select class="js-example-basic-single" name="warehouseCode" id="warehouseCode">
                                    <option selected value="">Pilih Gudang...</option>
                                    @foreach ($warehouse as $item)
                                        <option value="{{ $item->code }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mt-3">
                                <label class="form-label" for="itemCode">Item (Sparepart/Jasa)</label>
                                <select class="js-example-basic-single" name="itemCode" id="itemCode">
                                    <option selected value="">Pilih Item...</option>
                                    @foreach ($items as $item)
                                        <option value="{{ $item->code }}">{{ $item->code . ' - ' . $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 d-flex gap-2 justify-content-end mt-4">
                                <button type="button" id="resetFilter" class="btn btn-sm btn-secondary">
                                    <i class="mdi mdi-refresh"></i> Reset
                                </button>
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="mdi mdi-magnify"></i> Cari Data
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card-body">
                @include('partials.alert')
                <div class="table-responsive custom-scrollbar">
                    <table class="table table-striped w-100 nowrap table-maintenance-item" id="dt">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>No Maintenance</th>
                                <th>Warehouse</th>
                                <th>Kendaraan</th>
                                <th>Item</th>
                                <th>Description Item</th>
                                <th>Qty</th>
                                <th>Harga</th>
                                <th>Total Harga</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
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
    <script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/flatpickr.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Initialize flatpickr datepickers
            $(".flatpickr").flatpickr({
                dateFormat: "Y-m-d",
                allowInput: true
            });

            // Initialize select2
            $('.js-example-basic-single').select2({
                width: "100%"
            });

            // Initialize Datatable
            const table = $('#dt').DataTable({
                "processing": true,
                "serverSide": true,
                "destroy": true,
                "ajax": {
                    "url": "{{ route('dt.maintenance-item') }}",
                    "data": function(d) {
                        d.startDate = $('#startDate').val();
                        d.endDate = $('#endDate').val();
                        d.fleetCode = $('#fleetCode').val();
                        d.warehouseCode = $('#warehouseCode').val();
                        d.itemCode = $('#itemCode').val();
                    }
                },
                "columns": [
                    { "data": 'DT_RowIndex', "orderable": false, "searchable": false },
                    { "data": 'maintenance_date' },
                    { "data": 'maintenanceCode' },
                    { "data": 'maintenance.warehouse.name' },
                    { "data": 'maintenance.fleet.plateNumber' },
                    { "data": 'item.name' },
                    { "data": 'description' },
                    { "data": 'qty', "class": "text-right" },
                    { "data": 'price', "class": "text-right" },
                    { "data": 'total', "class": "text-right" },
                    { "data": 'created_at', "class": "text-center" }
                ],
                "order": [
                    [1, 'desc']
                ]
            });

            // Filter Submission
            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                const queryParams = $(this).serialize();

                // Update links for Excel and PDF
                const excelUrl = "{{ route($view . 'excel-maintenance-item') }}?" + queryParams;
                const pdfUrl = "{{ route($view . 'pdf-maintenance-item') }}?" + queryParams;

                $('#export-excel').attr('href', excelUrl);
                $('#export-pdf').attr('href', pdfUrl);

                table.ajax.reload();
            });

            // Reset filters
            $('#resetFilter').on('click', function() {
                $('#startDate').val('');
                $('#endDate').val('');
                $('#fleetCode').val('').trigger('change');
                $('#warehouseCode').val('').trigger('change');
                $('#itemCode').val('').trigger('change');

                // Restore default links
                $('#export-excel').attr('href', "{{ route($view . 'excel-maintenance-item') }}");
                $('#export-pdf').attr('href', "{{ route($view . 'pdf-maintenance-item') }}");

                table.ajax.reload();
            });
        });
    </script>
@endpush
