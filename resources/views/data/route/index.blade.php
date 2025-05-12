@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Data',
    'secondSegment' => $title,
])

@push('style')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/datatables.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/sweetalert2.css') }}">
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }} Data</h4>

                <a href="{{ route($view . 'create') }}" class="btn btn-primary">Add Data</a>

            </div>
            <div class="card-body">
                @include('partials.alert')
                <div class="table-responsive custom-scrollbar">
                    <table class="display" id="dt">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>No</th>
                                <th>Name</th>
                                <th>Customer</th>
                                <th>Origin</th>
                                <th>Destination</th>
                                {{-- <th>Fleet Type</th> --}}
                                <th>Route Type</th>
                                <th>Price</th>
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
    <script src="{{ asset('assets/js/datatable/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            $('#dt').DataTable({
                "processing": true,
                "serverSide": true,
                "destroy": true,
                "ajax": {
                    "url": "{{ route('dt.route') }}",
                },
                "columns": [{
                        "data": 'action'
                    },
                    {
                        "data": 'DT_RowIndex'
                    },
                    {
                        "data": 'name'
                    },
                    {
                        "data": 'customer.name'
                    },
                    {
                        "data": 'origin_location.name'
                    },
                    {
                        "data": 'destination_location.name'
                    },
                    {
                        "data": 'route_type.name'
                    },
                    // {
                    //     "data": 'fleet_type.name'
                    // },
                    {
                        "data": 'price'
                    },

                ],
                "columnDefs": [{
                        "searchable": false,
                        "targets": [0, 5]
                    },
                    {
                        "orderable": false,
                        "targets": [0, 5]
                    }
                ],
                "order": [
                    [2, 'asc']
                ]
            })
        });

        function deleteData(uuid) {
            var url = '{{ route('data.route.index') }}/' + uuid;
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
