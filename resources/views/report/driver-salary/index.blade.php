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
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom-select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">

    <style>
        .driver-group-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            color: #fff !important;
        }
        .driver-group-header td {
            color: #fff !important;
            font-weight: 600;
            font-size: 14px;
            padding: 10px 12px !important;
        }
        .driver-group-footer {
            background-color: #f0f4ff !important;
            font-weight: 700;
        }
        .salary-amount {
            text-align: right;
            font-weight: 500;
        }
        #dt_wrapper .dataTables_length,
        #dt_wrapper .dataTables_filter {
            display: none;
        }
    </style>
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }} Data</h4>

                <div class="d-flex align-items-center gap-3">
                    <div class="accordion-item">
                        <a href="#" class="btn btn-icon btn-sm bg-dark-subtle" data-bs-toggle="collapse"
                            data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            <i class="mdi mdi-magnify fs-14 text-dark"></i>
                        </a>
                    </div>

                    <a href="#" id="print-pdf" target="_blank" class="btn btn-icon btn-sm bg-danger-subtle"
                        title="Download PDF Slip Gaji">
                        <i class="mdi mdi-file-pdf-box fs-14 text-danger"></i>
                    </a>
                </div>
            </div>

            <div class="card-header">
                <div class="accordion-collapse collapse" id="collapseTwo" aria-labelledby="headingTwo"
                    data-bs-parent="#simpleaccordion">
                    <div class="accordion-body col-md-12">
                        <form id="filterForm" class="g-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="form-label">Plate Number</label>
                                    <select class="js-example-basic-single" name="fleetCode" id="fleetCode">
                                        <option selected="" value="">{{ __('general.choose') }}...</option>
                                        @foreach ($fleet as $item)
                                            <option value="{{ $item->code }}">{{ $item->plateNumber }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Driver Name</label>
                                    <select class="js-example-basic-single" name="driverCode" id="driverCode">
                                        <option selected="" value="">{{ __('general.choose') }}...</option>
                                        @foreach ($driver as $item)
                                            <option value="{{ $item->code }}">{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Start Date</label>
                                    <input class="form-control" name="startDate" type="date">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">End Date</label>
                                    <input class="form-control" name="endDate" type="date">
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-3">
                                <button class="btn btn-primary" type="submit">Filter</button>
                                <button class="btn btn-secondary" type="button" id="resetFilter">{{ __('general.reset') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card-body">
                @include('partials.alert')
                <div class="table-responsive custom-scrollbar">
                    <table class="table table-bordered table-striped w-100" id="dt">
                        <thead>
                            <tr>
                                <th style="width: 5%;">No</th>
                                <th style="width: 12%;">No. Polisi</th>
                                <th style="width: 18%;">Nama Supir</th>
                                <th style="width: 12%;">Tanggal</th>
                                <th style="width: 35%;">Rute</th>
                                <th style="width: 18%;">Jumlah Gaji</th>
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
    <script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2-custom.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/custom-flatpickr.js') }}"></script>

    <script>
        $(document).ready(function() {
            const table = $('#dt').DataTable({
                "processing": true,
                "serverSide": true,
                "destroy": true,
                "pageLength": 100,
                "ajax": {
                    "url": "{{ route('dt.driver-salary') }}",
                    "data": function(d) {
                        d.fleetCode = $('select[name="fleetCode"]').val();
                        d.driverCode = $('select[name="driverCode"]').val();
                        d.startDate = $('input[name="startDate"]').val();
                        d.endDate = $('input[name="endDate"]').val();
                    }
                },
                "columns": [
                    { "data": "DT_RowIndex", "className": "text-center" },
                    { "data": "plateNumber" },
                    { "data": "driverName" },
                    { "data": "orderDate" },
                    { "data": "routeName" },
                    { "data": "salaryTotal", "className": "salary-amount" },
                ],
                "columnDefs": [
                    { "searchable": false, "targets": [0, 3, 5] },
                    { "orderable": false, "targets": [0, 1, 2, 3, 4, 5] }
                ],
                "drawCallback": function(settings) {
                    // Group rows by driver after draw
                    var api = this.api();
                    var rows = api.rows({ page: 'current' }).data();
                    var last = null;
                    var groupTotal = 0;
                    var groupStartIdx = null;

                    // Collect group info
                    var groups = [];
                    var currentGroup = null;

                    for (var i = 0; i < rows.length; i++) {
                        var driverName = rows[i].driverName;

                        if (last !== driverName) {
                            if (currentGroup) {
                                currentGroup.total = groupTotal;
                                groups.push(currentGroup);
                            }
                            currentGroup = {
                                driverName: driverName,
                                plateNumber: rows[i].plateNumber,
                                startRow: i,
                                total: 0
                            };
                            groupTotal = 0;
                            last = driverName;
                        }

                        groupTotal += parseFloat(rows[i].salaryTotalRaw) || 0;
                    }
                    if (currentGroup) {
                        currentGroup.total = groupTotal;
                        groups.push(currentGroup);
                    }

                    // Insert group header and footer rows
                    var tbody = $(api.table().body());
                    var allRows = tbody.find('tr');
                    var offset = 0;

                    groups.forEach(function(group) {
                        // Header row
                        var headerHtml =
                            '<tr class="driver-group-header">' +
                            '<td colspan="6">' +
                            '<i class="mdi mdi-account me-2"></i>' +
                            '<strong>' + group.driverName + '</strong>' +
                            ' &mdash; No. Polisi: <strong>' + group.plateNumber + '</strong>' +
                            '</td>' +
                            '</tr>';

                        var targetRow = allRows.eq(group.startRow + offset);
                        $(headerHtml).insertBefore(targetRow);
                        offset++;

                        // Find the last row of this group
                        var lastRowIdx = (groups.indexOf(group) < groups.length - 1)
                            ? groups[groups.indexOf(group) + 1].startRow + offset - 1
                            : allRows.length + offset - 1;

                        // Footer row (total)
                        var footerHtml =
                            '<tr class="driver-group-footer">' +
                            '<td colspan="5" class="text-end"><strong>Total Gaji:</strong></td>' +
                            '<td class="salary-amount"><strong>Rp ' + new Intl.NumberFormat('id-ID').format(group.total) + '</strong></td>' +
                            '</tr>';

                        // Insert after last data row of this group
                        var endRow;
                        if (groups.indexOf(group) < groups.length - 1) {
                            endRow = allRows.eq(groups[groups.indexOf(group) + 1].startRow + offset - 1);
                            $(footerHtml).insertBefore(endRow);
                        } else {
                            tbody.append(footerHtml);
                        }
                        offset++;

                        // Re-query rows after insertion
                        allRows = tbody.find('tr');
                    });
                }
            });

            // PDF button
            $('#print-pdf').click(function(e) {
                e.preventDefault();
                const queryParams = $("#filterForm").serialize();
                const printPdf = "{{ route($view . 'pdf-driver-salary') }}?" + queryParams;
                window.open(printPdf);
            });

            // Filter form
            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                table.ajax.reload();
            });

            // Reset filter
            $('#resetFilter').on('click', function() {
                $('#fleetCode').val('').trigger('change');
                $('#driverCode').val('').trigger('change');
                $('input[name="startDate"]').val('');
                $('input[name="endDate"]').val('');
                table.ajax.reload();
            });
        });
    </script>
@endpush
