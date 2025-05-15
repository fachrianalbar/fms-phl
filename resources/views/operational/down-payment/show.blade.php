@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => $title,
    'secondSegment' => 'Edit',
])

@push('style')
    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/select2.css') }}">

    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/custom-select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">
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
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }} Data</h4>

                <a href="{{ route($view . 'index') }}" class="btn btn-info">Back To List</a>

            </div>
            <div class="card-body col-md-12">

                <div class="row g-3">
                    <div class="row mt-4">
                        <div class="col-md-3">
                            <p class="h6">Code </p>

                        </div>
                        <div class="col-md-3">
                            <p class="h6">{{ $data->code }}</p>
                        </div>

                        <div class="col-md-3">
                            <p class="h6">Paid </p>

                        </div>
                        <div class="col-md-3">
                            <p class="h6">{{ $isPaid ? 'Yes' : 'No' }}</p>
                        </div>

                    </div>

                    <div class="row mt-4">
                        <div class="col-md-3">
                            <p class="h6">Loan Date</p>

                        </div>
                        <div class="col-md-3">
                            <p class="h6">{{ $data->date . ' ' . $data->time }}</p>

                        </div>

                        <div class="col-md-3">
                            <p class="h6">Nominal</p>

                        </div>
                        <div class="col-md-3">
                            <p class="h6">{{ 'Rp ' . number_format($data->price, 0, ',', '.') }}</p>

                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-3">
                            <p class="h6">Driver</p>

                        </div>
                        <div class="col-md-3">
                            <p class="h6">{{ $data->driver->code . ' - ' . $data->driver->name }}</p>

                        </div>

                        <div class="col-md-3">
                            <p class="h6">Description</p>

                        </div>
                        <div class="col-md-3">
                            <p class="h6">{{ $data->note }}</p>

                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-3">
                            <p class="h6">Total Payment</p>

                        </div>
                        <div class="col-md-3">
                            @php
                                $total = 0;

                                foreach ($data->details as $item) {
                                    $total += $item->price;
                                }
                            @endphp
                            <p class="h6">{{ 'Rp ' . number_format($total, 0, ',', '.') }}</p>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4> {{ $title }} Detail Data</h4>

                <button type="button" class="btn btn-primary" onclick="create()">Add Data</button>


            </div>
            <div class="card-body">
                <div class="card-body col-md-12">
                    <table class="table table-bordered dt-responsive table-responsive nowrap" id="dt">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>No</th>
                                <th>Payment Date</th>
                                <th>Prices</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
            aria-hidden="true" id="modal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myLargeModalLabel" onclick="create()">Add Data</h4>
                        <button class="btn-close py-0" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="card">
                        <div class="card-body col-md-12">

                            <div class="row g-3">
                                <form method="POST" action="{{ route('operational.down-payment-detail.store') }}"
                                    onsubmit="return submitForm('price')">
                                    @csrf
                                    <input type="hidden" name="dpCode" value="{{ $data->code }}">
                                    <div class="row mt-4">
                                        <div class="col-md-6">
                                            <label class="form-label" for="name">Date <i
                                                    class="icofont icofont-warning-alt text-danger"></i></label>
                                            <input class="form-control" name="date" id="datetime-local"
                                                type="date" required placeholder="Order Date"
                                                value="{{ now()->toDateString() }}">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Time <i
                                                    class="icofont icofont-warning-alt text-danger"></i></label>
                                            <input class="form-control digits" name="time" type="time"
                                                value="{{ now()->setTimezone('Asia/Jakarta')->format('H:i') }}">
                                        </div>
                                    </div>

                                    <div class="row mt-4">
                                        <div class="col-md-12">
                                            <label class="form-label" for="price">Price <i
                                                    class="icofont icofont-warning-alt text-danger"></i></label>
                                            <input class="form-control" name="price" id="price" type="text"
                                                placeholder="Price" oninput="formatAngka(this)" required>
                                        </div>
                                    </div>

                                    <div class="row mt-4">
                                        <div class="col-md-12">
                                            <label class="form-label" for="note">Note </label>
                                            <input class="form-control" name="note" id="note" type="text"
                                                placeholder="Note">
                                        </div>
                                    </div>
                                    <div class="modal-footer justify-content-start">
                                        <button class="btn btn-primary" type="submit">Save</button>
                                    </div>
                                </form>
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
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src=" {{ asset('assets/js/select2/select2-custom.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/custom-flatpickr.js') }}"></script>
    <script src=" {{ asset('assets/js/helper.js') }}"></script>
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
    <script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            // $('#dt').DataTable({
            //     "processing": true,
            //     "serverSide": true,
            //     "destroy": true,
            //     "ajax": {
            //         "url": "{{ route('dt.down-payment-detail') }}?code={{ $data->code }}",
            //     },
            //     "columns": [{
            //             "data": 'action'
            //         },
            //         {
            //             "data": 'DT_RowIndex'
            //         },
            //         {
            //             "data": 'date'
            //         },
            //         {
            //             "data": 'price'
            //         },
            //         {
            //             "data": 'note'
            //         }


            //     ],
            //     "columnDefs": [{
            //             "searchable": false,
            //             "targets": [0, 1]
            //         },
            //         {
            //             "orderable": false,
            //             "targets": [0, 1]
            //         }
            //     ],
            //     "order": [
            //         [2, 'asc']
            //     ]
            // })

            $('#dt').DataTable({
                "processing": true,
                "serverSide": true,
                "destroy": true,
                "ajax": {
                    "url": "{{ route('dt.down-payment-detail') }}?code={{ $data->code }}",
                },
                "columns": [{
                        "data": 'action'
                    },
                    {
                        "data": 'DT_RowIndex'
                    },
                    {
                        "data": 'date'
                    },
                    {
                        "data": 'price'
                    },
                    {
                        "data": 'note'
                    }


                ],
                "columnDefs": [{
                        "searchable": false,
                        "targets": [0, 1]
                    },
                    {
                        "orderable": false,
                        "targets": [0, 1]
                    }
                ],
                "order": [
                    [2, 'desc']
                ]
            })
        });


        function editData(id) {
            $.ajax({
                url: "{{ route('ajax.down-payment-data', ':id') }}".replace(':id', id),
                type: "GET",
                success: function(response) {
                    // loader();
                    // Assuming the response contains the data you want to populate the form fields with
                    // $('input[name="code"]').val(response.code);
                    // $('select[name="driverCode"]').val(response.driverCode).trigger('change');
                    $('input[name="date"]').val(response.date);
                    $('input[name="time"]').val(response.time);
                    $('input[name="price"]').val(response.price);
                    $('input[name="note"]').val(response.note);

                    // // Change form action to update route
                    $('form').attr('action', '{{ route('operational.down-payment-detail.update', ':id') }}'
                        .replace(':id', response.id));

                    $('form').append('<input type="hidden" name="_method" value="PUT">');

                    $('.bd-example-modal-lg').modal('show');

                }
            });
        }

        function create() {
            $('input[name="date"]').val('{{ now()->toDateString() }}');
            $('input[name="time"]').val('{{ now()->setTimezone('Asia/Jakarta')->format('H:i') }}');
            $('input[name="price"]').val('');
            $('input[name="note"]').val('');

            // Reset form action to point to the store route
            $('form').attr('action', '{{ route('operational.down-payment-detail.store') }}');

            // Remove the hidden `_method` field if it exists (from the update mode)
            $('input[name="_method"]').remove();

            $('.bd-example-modal-lg').modal('show');

        }

        function deleteData(uuid) {
            var url = '{{ route('operational.down-payment-detail.index') }}/' + uuid;
            $('#delete-form').attr('action', url);

            swal({
                title: "Are you sure?",
                text: "Want to delete this data?",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    $('#delete-form').submit();
                } else {
                    swal("Your data is safe!");
                }
            });
        }
    </script>
@endpush
