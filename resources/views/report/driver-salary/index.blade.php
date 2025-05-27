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
    <link rel="stylesheet" type="text/css" href="../assets/css/vendors/sweetalert2.css">
    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/select2.css') }}">

    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/custom-select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">
    <style>
        #dt tbody td:nth-child(6) {
            border-right: 2px solid #ddd;
        }

        #dt tbody td:nth-child(5) {
            border-right: 2px solid #ddd;
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

                    <a href="#" id="print-pdf" target="_blank" class="btn btn-icon btn-sm bg-danger-subtle">
                        <i class="mdi mdi-file fs-14 text-danger"></i>
                    </a>

                </div>
            </div>

            <div class="card-header">
                <div class="accordion-collapse collapse" id="collapseTwo" aria-labelledby="headingTwo"
                    data-bs-parent="#simpleaccordion">
                    <div class="accordion-body col-md-12">
                        <form id="filterForm" class=" g-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label" for="name">Plate Number</label>
                                    <select class="js-example-basic-single" name="fleetCode" id="fleetCode">
                                        <option selected="" value="">{{ __('general.choose') }}...</option>
                                        @foreach ($fleet as $item)
                                            <option value="{{ $item->code }}">
                                                {{ $item->plateNumber }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="name">Driver Name</label>
                                    <select class="js-example-basic-single" name="driverCode" id="driverCode">
                                        <option selected="" value="">{{ __('general.choose') }}...</option>
                                        @foreach ($driver as $item)
                                            <option value="{{ $item->code }}">{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                            </div>

                            <div class="row mt-4">

                                <div class="col-md-3">
                                    <label class="form-label" for="name">Date</label>
                                    <input class="form-control" name="startDate" id="datetime-local" type="date"
                                        placeholder="Start Date">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label" for="name"></label>
                                    <input class="form-control" name="endDate" id="datetime-local" type="date"
                                        placeholder="End Date">
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
                    <table class="table table-bordered dt-responsive table-responsive nowrap" id="dt"
                        style="border-collapse: collapse; width: 100%;">
                        <thead>
                            <tr>
                                <th rowspan="2">No</th>
                                <th rowspan="2">Nomor Truk</th>
                                <th rowspan="2">Nama Supir</th>
                                <th class="text-center" colspan="2">Ritase</th>
                                <th class="text-center" rowspan="2">
                                    Keterangan</th>
                                <th class="text-center" rowspan="2">TTD</th>
                            </tr>
                            <tr>
                                <th>H</th>
                                <th>Nilai</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
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
    {{-- <script src="../assets/js/sweet-alert/app.js"></script> --}}

    <script>
        $(document).ready(function() {
            const table = $('#dt').DataTable({
                "processing": true,
                "serverSide": true,
                "destroy": true,
                "ajax": {
                    "url": "{{ route('dt.driver-salary') }}",
                    "data": function(d) {
                        d.fleetCode = $('select[name="fleetCode"]').val();
                        d.driverCode = $('select[name="driverCode"]').val();
                        d.startDate = $('input[name="startDate"]').val();
                        d.endDate = $('input[name="endDate"]').val();
                    }
                },
                "columns": [{
                        "data": 'DT_RowIndex'
                    },
                    {
                        "data": 'fleet.plateNumber'
                    },
                    {
                        "data": 'driver.name'
                    },
                    {
                        "data": 'total_orders'
                    },
                    {
                        "data": 'value'
                    },
                    {
                        "data": 'description'
                    },
                    {
                        "data": 'ttd'
                    }


                ],
                "columnDefs": [{
                        "searchable": false,
                        "targets": [0, 3, 4, 5, 6]
                    },
                    {
                        "orderable": false,
                        "targets": [0, 1, 2, 3, 4, 5, 6]
                    }
                ],
                // "order": [
                //     [1, 'asc']
                // ]
            })

            $('#print-pdf').click(function(e) {
                e.preventDefault(); // Prevent the default action of the link

                const queryParams = $("#filterForm").serialize(); // Serialize the form data

                const printPdf = "{{ route($view . 'pdf-driver-salary') }}?" + queryParams;

                $('#print-pdf').attr('href', printPdf);


                // If all fields are filled, proceed to the PDF generation
                window.open(printPdf); // Use the correct URL string
            });


            // Event untuk form filter
            $('#filterForm').on('submit', function(e) {
                e.preventDefault();

                table.ajax.reload();
            });
        });

        function deleteData(uuid) {
            var url = '{{ route('master.unit.index') }}/' + uuid;
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
