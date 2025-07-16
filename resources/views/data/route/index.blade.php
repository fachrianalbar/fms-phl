@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Data',
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
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }} Data</h4>

                <a href="{{ route($view . 'create') }}" class="btn btn-primary">{{ __('general.add_data') }}</a>

            </div>
            <div class="card-body">
                @include('partials.alert')
                <div class="table-responsive custom-scrollbar">
                    <table class="table table-striped w-100 nowrap" id="dt">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>No</th>
                                {{-- <th>{{ __('menu_route.name') }}</th> --}}
                                <th>{{ __('menu_route.customer') }}</th>
                                <th>{{ __('menu_route.origin') }}</th>
                                <th>{{ __('menu_route.destination') }}</th>
                                {{-- <th>Fleet Type</th> --}}
                                <th>{{ __('menu_route.load_type') }}</th>
                                <th>{{ __('menu_route.price') }}</th>
                                <th>{{ __('menu_route.vendor_price') }}</th>
                                <th>{{ __('menu_route.personal_vendor_price') }}</th>
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
                    {
                        "data": 'vendorPrice'
                    },
                    {
                        "data": 'personalVendorPrice'
                    },

                ],
                "columnDefs": [{
                        "searchable": false,
                        "targets": [0]
                    },
                    {
                        "orderable": false,
                        "targets": [0]
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
