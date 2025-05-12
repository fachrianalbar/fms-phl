@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => $title,
    'secondSegment' => 'Edit',
])

@push('style')
    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/datatables.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/sweetalert2.css') }}">
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }} Edit Data</h4>

                <a href="{{ route($view . 'index') }}" class="btn btn-info">Back To List</a>

            </div>
            <div class="card-body col-md-12">

                <form class="row g-3" method="post" novalidate="" action="{{ route($view . 'update', $data->id) }}"
                    onsubmit="return submitForm('price')">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label" for="name">Code <i
                                    class="icofont icofont-warning-alt text-danger"></i></label>
                            <input type="hidden" name="code" value="TDP{{ now()->format('ymdHis') }}">
                            <input class="form-control" type="text" required placeholder="Name" readonly disabled
                                value="TDP{{ now()->format('ymdHis') }}">
                        </div>

                        <div class="col-md-6 position-relative">
                            <label class="form-label" for="driverCode">Driver Name <i
                                    class="icofont icofont-warning-alt text-danger"></i></label>
                            <input type="hidden" name="driverCode" value="{{ $data->code }}">
                            <select class="js-example-basic-single" id="driverCode" name="driverCode" required="">
                                <option selected="" disabled="" value="">Choose...</option>
                                @foreach ($driver as $item)
                                    <option value="{{ $item->code }}"
                                        {{ $data->driverCode == $item->code ? 'selected' : '' }}>
                                        {{ $item->code . ' - ' . $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-3">
                            <label class="form-label" for="name">Date <i
                                    class="icofont icofont-warning-alt text-danger"></i></label>
                            <input class="form-control" name="date" id="datetime-local" type="date" required
                                placeholder="Order Date" value="{{ $data->date }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Time</label>
                            <input class="form-control digits" name="time" type="time" value="{{ $data->time }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="price">Price <i
                                    class="icofont icofont-warning-alt text-danger"></i></label>
                            <input class="form-control" name="price" id="price" type="text" placeholder="Price"
                                oninput="formatAngka(this)" value="{{ $data->price }}" required>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="note">Note </label>
                            <input class="form-control" name="note" id="note" type="text" placeholder="Note"
                                value="{{ $data->note }}">
                        </div>
                    </div>

                    <div class="col-12">
                        <button class="btn btn-primary" type="submit">Save</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4> {{ $title }} Data</h4>

                <a target="_blank" href="{{ route($view . 'pdf-down-payment', $data->id) }}" class="btn btn-danger">Print
                    Pdf</a>
            </div>
            <div class="card-body">
                <div class="card-body col-md-12">
                    <table class="display" id="dt">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>No</th>
                                <th>Code</th>
                                <th>PIC Name</th>
                                <th>Driver Code</th>
                                <th>Driver Name</th>
                                <th>DP / Return Date</th>
                                <th>Type</th>
                                <th>Nominal</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div> --}}
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
    <script src="{{ asset('assets/js/datatable/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>

    {{-- <script>
        $(document).ready(function() {
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
                        "data": 'code'
                    },
                    {
                        "data": 'pic_user.name'
                    },
                    {
                        "data": 'driver.code'
                    },
                    {
                        "data": 'driver.name'
                    },
                    {
                        "data": 'date'
                    },
                    {
                        "data": 'type'
                    },
                    {
                        "data": 'nominal'
                    },

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
                    [2, 'asc']
                ]
            })
        });

        function editData(id) {
            $.ajax({
                url: "{{ route('operational.down-payment.show', ':id') }}".replace(':id', id),
                type: "GET",
                success: function(response) {
                    loader();
                    // Assuming the response contains the data you want to populate the form fields with
                    $('input[name="code"]').val(response.code);
                    // $('select[name="driverCode"]').val(response.driverCode).trigger('change');
                    $('input[name="date"]').val(response.date);
                    $('input[name="time"]').val(response.time);
                    $('select[name="pic"]').val(response.pic).trigger('change');
                    $('select[name="type"]').val(response.type).trigger('change');
                    $('input[name="nominal"]').val(response.nominal);
                    $('input[name="note"]').val(response.note);

                    // // Change form action to update route
                    $('form').attr('action', '{{ route($view . 'update', ':id') }}'.replace(':id', response
                        .id));
                    $('form').append('<input type="hidden" name="_method" value="PUT">');
                }
            });
        }

        function deleteData(uuid) {
            var url = '{{ route('operational.down-payment.index') }}/' + uuid;
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
    </script> --}}
@endpush
