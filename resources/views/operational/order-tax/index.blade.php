@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Operational',
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
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }} Data</h4>
            </div>
            <div class="card-body">
                @include('partials.alert')
                <div class="table-responsive custom-scrollbar">
                    <table class="table table-striped w-100 nowrap" id="dt">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>No</th>
                                <th>{{ __('menu_order_tax.order_date') }}</th>
                                <th>{{ __('menu_order_tax.plate_number') }}</th>
                                <th>{{ __('menu_order_tax.driver') }}</th>
                                <th>{{ __('menu_order_tax.shipment_no') }}</th>
                                <th>{{ __('menu_order_tax.customer') }}</th>
                                <th>{{ __('menu_order_tax.origin') }}</th>
                                <th>{{ __('menu_order_tax.destination') }}</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <form class="row g-3" method="post" action="{{ route($view . 'store') }}">
            @csrf
            <div class="modal fade bd-example-modal-lg" id="tax-modal" tabindex="-1" role="dialog"
                aria-labelledby="myLargeModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <input type="hidden" name="orderCode" id="orderCode">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">{{ $title }} Modal
                            </h4>
                            <button class="btn-close py-0" type="button" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="card">
                            <div class="card-body col-md-12">
                                <div class="row g-3">

                                    <div class="col-md-12">
                                        <label class="form-label"
                                            for="description">{{ __('menu_order_tax.description') }}</label>
                                        <textarea class="form-control" name="description" id="description" rows="3"
                                            placeholder="{{ __('menu_order_tax.description') }}"></textarea>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="modal-footer justify-content-start">
                            <button class="btn btn-primary" type="submit">{{ __('general.save') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>

    <form id="delete-form" method="post">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('script')
    <script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>

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
    <script src=" {{ asset('assets/js/helper.js') }}"></script>

    {{-- <script src="../assets/js/sweet-alert/app.js"></script> --}}

    <script>
        $(document).ready(function() {
            $('#dt').DataTable({
                "processing": true,
                "serverSide": true,
                "destroy": true,
                "ajax": {
                    "url": "{{ route('dt.order-tax') }}",
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
                        "data": 'driver.name'
                    },
                    {
                        "data": "shipmentNumber"
                    },
                    {
                        "data": 'customer.name'
                    },
                    {
                        "data": 'route.originLocation.name'
                    },
                    {
                        "data": 'route.destinationLocation.name'
                    },
                ],
                "columnDefs": [{
                        "searchable": false,
                        "targets": [0, 2]
                    },
                    {
                        "orderable": false,
                        "targets": [0, 2]
                    }
                ],
                "order": [
                    [1, 'asc']
                ]
            })
        });

        function showModal(id) {
            $('#tax-modal').modal('show');
            $('#orderCode').val(id);
        }
    </script>
@endpush
