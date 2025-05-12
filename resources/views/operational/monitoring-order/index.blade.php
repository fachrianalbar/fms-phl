@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Operational',
    'secondSegment' => $title,
])

@push('style')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/datatables.css') }}">
    <link rel="stylesheet" type="text/css" href="../assets/css/vendors/sweetalert2.css">
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }} Data</h4>

                {{-- <a href="{{ route($view . 'create') }}" class="btn btn-primary">Add Data</a> --}}

            </div>
            <div class="card-body">
                @include('partials.alert')
                <div class="table-responsive custom-scrollbar">
                    <table class="display table-order" id="dt">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>No</th>
                                <th>Order Date</th>
                                <th>Fleet</th>
                                <th>Shipment No</th>
                                <th>Origin</th>
                                <th>Destination</th>
                                <th>Status</th>
                                <th>Estimated Time</th>
                                <th>Distance</th>
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


    <form id="finish-order" method="post">
        @csrf
        @method('PUT')
    </form>
@endsection

@push('script')
    <script src="{{ asset('assets/js/datatable/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="../assets/js/sweet-alert/sweetalert.min.js"></script>
    {{-- <script src="../assets/js/sweet-alert/app.js"></script> --}}

    <script>
        $(document).ready(function() {
            $('#dt').DataTable({
                "processing": true,
                "serverSide": true,
                "destroy": true,
                "ajax": {
                    "url": "{{ route('dt.order-monitoring') }}",
                },
                "columns": [{
                        "data": 'action'
                    }, {
                        "data": 'DT_RowIndex'
                    },
                    {
                        "data": 'orderDate'
                    },
                    {
                        "data": 'fleet.plateNumber'
                    },
                    {
                        "data": 'shipmentNumber'
                    },
                    {
                        "data": 'route.originLocation.name'
                    },
                    {
                        "data": 'route.destinationLocation.name'
                    },
                    {
                        "data": 'orderStatus.name'
                    },
                    {
                        "data": 'estimatedTime'
                    },
                    {
                        "data": 'distance'
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
                    [2, 'asc']
                ]
            })
        });

        function finishOrder(id) {
            var url = '{{ route('operational.finish-order', ':id') }}';
            url = url.replace(':id', id);
            $('#finish-order').attr('action', url);

            swal({
                title: "Are you sure?",
                text: "Want to finish this order?",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    $('#finish-order').submit();
                } else {
                    swal("Your data is safe!");
                }
            });
        }

        function deleteData(uuid) {
            var url = '{{ route('master.unit.index') }}/' + uuid;
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
