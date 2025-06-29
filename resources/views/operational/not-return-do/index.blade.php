@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Master',
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
    <form class="col-sm-12" method="POST" action="{{ route('operational.not-return-do.confirm-do') }}">
        <div class="card">
            @csrf
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }} Data</h4>

                <button type="submit" class="btn btn-primary" id="saveOrder">Confirm Return</button>

            </div>
            <div class="card-body">
                @include('partials.alert')
                <div class="table-responsive custom-scrollbar">
                    <table class="table table-striped w-100 nowrap" id="dt">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>No</th>
                                <th>Order Date</th>
                                <th>Code</th>
                                <th>Shipment No</th>
                                <th>Customer Name</th>
                                <th>Origin</th>
                                <th>Destination</th>
                                <th>Fleet</th>
                                <th>Fleet Type</th>
                                <th>Driver</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade bd-example-modal-lg" id="do-modal" tabindex="-1" role="dialog"
            aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myLargeModalLabel">Not Return Data</h4>
                        <button class="btn-close py-0" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="card">
                        <div class="card-body col-md-12">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label" for="returnDate">Date <i
                                            class="mdi mdi-information text-danger"></i></label>
                                    <input class="form-control" name="returnDate" id="returnDate" type="date" required
                                        placeholder="Date">
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label" for="description">Description </label>
                                    <textarea class="form-control" name="returnDescription" id="description" placeholder="Description" rows="4"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer justify-content-start">
                            <button class="btn btn-primary" type="submit">Save Data</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
    {{-- <script src="../assets/js/sweet-alert/app.js"></script> --}}

    <script>
        let selectedOrders = [];

        document.getElementById('saveOrder').addEventListener('click', function(event) {
            // Get all checkboxes
            if (selectedOrders.length === 0) {
                event.preventDefault();
                swal({
                    title: "{{ __('general.warning') }}",
                    text: "Please select at least one item",
                    icon: "warning",
                });
                return;
            }

            // Tambahkan array ke form
            $('<input>').attr({
                type: 'hidden',
                name: 'selectedOrders',
                value: JSON.stringify(selectedOrders)
            }).appendTo('form');

            event.preventDefault();

            $('#do-modal').modal('show');


        });

        $(document).ready(function() {
            // Event handler untuk checkbox
            $(document).on('change', '.order-checkbox', function() {
                const orderId = $(this).val();
                if ($(this).is(':checked')) {
                    if (!selectedOrders.includes(orderId)) {
                        selectedOrders.push(orderId);
                    }
                } else {
                    selectedOrders = selectedOrders.filter(id => id !== orderId);
                }

            });

            // Simpan state saat DataTable di-reload (misalnya saat pindah halaman)
            $('#dt').on('draw.dt', function() {
                $('.order-checkbox').each(function() {
                    const orderId = $(this).val();
                    if (selectedOrders.includes(orderId)) {
                        $(this).prop('checked', true);
                    }
                });
            });

            $('#dt').DataTable({
                "processing": true,
                "serverSide": true,
                "destroy": true,
                "ajax": {
                    "url": "{{ route('dt.not-return-do') }}",
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
                        "data": 'code'
                    },
                    {
                        "data": 'shipmentNumber'
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
                    {
                        "data": 'fleet.plateNumber'
                    },
                    {
                        "data": 'fleet.plateNumber'
                    },
                    {
                        "data": 'driver.name'
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
    </script>
@endpush
