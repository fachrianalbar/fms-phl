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
    <link rel="stylesheet" type="text/css" href="../assets/css/vendors/sweetalert2.css">
    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/select2.css') }}">

    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/custom-select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">

    <style>
        #dt-detail-activity pre {
            background-color: transparent !important;
            padding: 0 !important;
            margin: 0 !important;
            border: none !important;
            font-family: inherit !important;
            white-space: nowrap !important;
            /* prevent wrap */
            display: inline !important;
            /* make it behave like span */
        }
    </style>
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }} Data</h4>

                <div class="d-flex align-items-center gap-3">
                    <div class="accordion-item ">

                        <a href="#" class="btn btn-icon btn-sm bg-dark-subtle" data-bs-toggle="collapse"
                            data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            <i class="mdi mdi-magnify fs-14 text-dark"></i>
                        </a>

                    </div>
                </div>


            </div>

            <div class="card-header">
                <div class="accordion-collapse collapse" id="collapseTwo" aria-labelledby="headingTwo"
                    data-bs-parent="#simpleaccordion">
                    <div class="accordion-body col-md-12">
                        <form id="filterForm" class=" g-3">

                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label" for="data">Data</label>
                                    <input class="form-control" name="data" type="text" placeholder="Data">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label" for="startDate">Date</label>
                                    <input class="form-control" name="startDate" id="datetime-local" type="date"
                                        placeholder="Start Date">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label" for="endDate"></label>
                                    <input class="form-control" name="endDate" id="datetime-local" type="date"
                                        placeholder="End Date">
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <label class="form-label" for="activity">Activity</label>
                                    <select class="js-example-basic-single" name="activity" id="activity">
                                        <option selected="" value="">Choose...</option>
                                        @foreach ($activity as $item)
                                            <option value="{{ $item }}">
                                                {{ $item }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="action">Action</label>
                                    <select class="js-example-basic-single" name="action" id="action">
                                        <option selected="" value="">Choose...</option>
                                        @foreach ($action as $item)
                                            <option value="{{ $item }}">
                                                {{ $item }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <label class="form-label" for="causer_id">User</label>
                                    <select class="js-example-basic-single" name="causer_id" id="causer_id">
                                        <option selected="" value="">Choose...</option>
                                        @foreach ($user as $item)
                                            <option value="{{ $item->id }}">
                                                {{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="roleCode">Role</label>
                                    <select class="js-example-basic-single" name="roleCode" id="roleCode">
                                        <option selected="" value="">Choose...</option>
                                        @foreach ($role as $item)
                                            <option value="{{ $item->code }}">
                                                {{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <button class="btn btn-primary mt-3" type="submit">Filter</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @include('partials.alert')
                <div class="table-responsive custom-scrollbar">
                    <table class="table table-bordered dt-responsive table-responsive nowrap" id="dt">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Activity</th>
                                <th>Action</th>
                                <th>User</th>
                                <th>Role</th>
                                <th>Data</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
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
                            <div class="table-responsive ">
                                <table class="display" id="dt-detail-activity">
                                    <thead id="properties-table-head">
                                        <!-- Keys here -->
                                    </thead>
                                    <tbody id="properties-table-body">
                                        <!-- Values here -->
                                    </tbody>
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

    <!-- dataTables.bootstrap5 -->
    <script src="{{ asset('assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>

    <!-- dataTables.keyTable -->
    <script src="{{ asset('assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-keytable-bs5/js/keyTable.bootstrap5.min.js') }}"></script>

    <!-- dataTable.responsive -->
    <script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>

    <!-- dataTables.select -->
    <script src="{{ asset('assets/libs/datatables.net-select/js/dataTables.select.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-select-bs5/js/select.bootstrap5.min.js') }}"></script>
    <script src="../assets/js/sweet-alert/sweetalert.min.js"></script>
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src=" {{ asset('assets/js/select2/select2-custom.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/custom-flatpickr.js') }}"></script>

    <script>
        $(document).on('click', '.btn-show-detail', function() {
            const encoded = $(this).attr('data-properties');
            const decoded = $("<textarea/>").html(encoded).text();



            try {
                // 1. Decode HTML entity
                const firstParse = JSON.parse(decoded);

                // 2. Parse lagi kalau nested string
                const data = typeof firstParse === 'string' ? JSON.parse(firstParse) : firstParse;

                // 3. Ambil langsung isi attributes kalau ada
                const properties = data.attributes ?? data;

                // 4. Build thead & tbody
                let headRow = '<tr>';
                let bodyRow = '<tr>';

                $.each(properties, function(key, value) {
                    headRow += `<th>${key}</th>`;
                    bodyRow +=
                        `<td><pre class="mb-0">${typeof value === 'object' ? JSON.stringify(value, null, 2) : value}</pre></td>`;
                });

                headRow += '</tr>';
                bodyRow += '</tr>';

                $('#properties-table-head').html(headRow);
                $('#properties-table-body').html(bodyRow);

                $('#dt-detail-activity').DataTable()

            } catch (e) {
                console.error('❌ Failed to parse JSON from data-properties:', e);
                $('#properties-table-head').html('<tr><th>Error</th></tr>');
                $('#properties-table-body').html('<tr><td>Invalid JSON Data</td></tr>');
            }
        });
    </script>



    <script>
        $(document).ready(function() {
            const table = $('#dt').DataTable({
                "processing": true,
                "serverSide": true,
                "destroy": true,
                "ajax": {
                    "url": "{{ route('dt.activity-log') }}",
                    "data": function(d) {
                        d.data = $('input[name="data"]').val();
                        d.activity = $('select[name="activity"]').val();
                        d.action = $('select[name="action"]').val();
                        d.roleCode = $('select[name="roleCode"]').val();
                        d.causer_id = $('select[name="causer_id"]').val();
                        d.startDate = $('input[name="startDate"]').val();
                        d.endDate = $('input[name="endDate"]').val();
                    }
                },
                "columns": [{
                        "data": 'DT_RowIndex'
                    },
                    {
                        "data": 'log_name'
                    },
                    {
                        "data": 'description'
                    },
                    {
                        "data": 'user.name'
                    },
                    {
                        "data": 'user.role.name'
                    },
                    {
                        "data": 'properties'
                    },
                    {
                        "data": 'created_at'
                    }

                ],
                "columnDefs": [{
                        "searchable": false,
                        "targets": [0, 4, 5, 6]
                    },
                    {
                        "orderable": false,
                        "targets": [0, 1, 2, 3, 4, 5, 6]
                    }
                ],

            })

            $('#filterForm').on('submit', function(e) {
                e.preventDefault();

                let queryParams = $(this).serialize();

                table.ajax.reload();
            });
        });
    </script>
@endpush
