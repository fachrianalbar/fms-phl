@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => $title,
    'secondSegment' => 'Add',
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
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">
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

                    <a href="{{ route($view . 'index') }}" class="btn btn-info">Back To List</a>
                </div>


            </div>

            <div class="card-header">
                <div class="accordion-collapse collapse" id="collapseTwo" aria-labelledby="headingTwo"
                    data-bs-parent="#simpleaccordion">
                    <div class="accordion-body col-md-12">
                        <form id="filterForm" class=" g-3">
                            <div class="row">

                                <div class="col-md-6">
                                    <label class="form-label" for="name">Date</label>
                                    <input class="form-control" name="startDate" id="datetime-local" type="date"
                                        placeholder="Start Date">
                                </div>

                                <div class="col-md-6">
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
            <div class="card-body col-md-12">
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-flex gap-5 justify-content-between">
                            <span class="h6">Fleet</span>
                            <span class="h6">{{ $data->plateNumber }}</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="d-flex gap-5 justify-content-between">
                            <span class="h6">Fleet Type</span>
                            <span class="h6">{{ $data->type?->name }}</span>
                        </div>
                    </div>


                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="d-flex gap-5 justify-content-between">
                            <span class="h6">Order Sales</span>
                            <span class="h6 basicSales">{{ number_format($basicSales, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="d-flex gap-5 justify-content-between">
                            <span class="h6">Order Cost</span>
                            <span class="h6 orderCost">{{ number_format($orderCost, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="d-flex gap-5 justify-content-between">
                            <span class="h6">Maintenance Cost</span>
                            <span class="h6 maintenance">{{ number_format($maintenance, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="d-flex gap-5 justify-content-between">
                            <span class="h6">Total Margin</span>
                            <span class="h6 totalMargin">{{ number_format($totalMargin, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Order Cost Data</h4>
            </div>
            <div class="card-body col-md-12">
                <table class="display " id="dt-order">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Order Date</th>
                            <th>Shipment No</th>
                            <th>Qty</th>
                            <th>Cost</th>
                            <th>Tonase</th>
                            <th>Add Cost</th>
                            <th>Total Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Maintenance Data</h4>
            </div>
            <div class="card-body col-md-12">
                <table class="display " id="dt-maintenance">
                    <thead>

                        <tr>
                            <th>No</th>
                            <th>Code</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>


    </div>
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
    <script src="{{ asset('assets/js/flat-pickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/custom-flatpickr.js') }}"></script>

    <script>
        $(document).ready(function() {

            const tableOrder = $('#dt-order').DataTable({
                "processing": true,
                "serverSide": true,
                "destroy": true,
                "ajax": {
                    "url": "{{ route('dt.profit-loss-order') }}",
                    "data": function(d) {
                        d.plateNumber = $('select[name="plateNumber"]').val();
                        d.customerName = $('select[name="customerName"]').val();
                        d.driverName = $('select[name="driverName"]').val();
                        d.fleetTypeName = $('select[name="fleetTypeName"]').val();
                        d.shipmentNumber = $('input[name="shipmentNumber"]').val();
                        d.startDate = $('input[name="startDate"]').val();
                        d.endDate = $('input[name="endDate"]').val();
                        // d.origin = $('input[name="origin"]').val();
                        d.destination = $('select[name="destination"]').val();
                        d.orderTypeCode = $('select[name="orderTypeCode"]').val();
                        d.fleetCode = "{{ $data->code }}";
                    }
                },
                "columns": [{
                        "data": 'DT_RowIndex'
                    },
                    {
                        "data": 'orderDate'
                    },

                    {
                        "data": "shipmentNumber"
                    },
                    {
                        "data": 'qty'
                    },
                    {
                        "data": 'cost',
                    },
                    {
                        "data": 'bonus'
                    },
                    {
                        "data": 'addCost'
                    },
                    {
                        "data": 'totalPrice'
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
                    [0, 'desc']
                ]
            })

            const tableMaintenance = $('#dt-maintenance').DataTable({
                "processing": true,
                "serverSide": true,
                "destroy": true,
                "ajax": {
                    "url": "{{ route('dt.profit-loss-maintenance') }}",
                    "data": function(d) {
                        d.plateNumber = $('select[name="plateNumber"]').val();
                        d.startDate = $('input[name="startDate"]').val();
                        d.endDate = $('input[name="endDate"]').val();
                        d.itemCode = $('select[name="itemCode"]').val();

                        d.fleetCode = "{{ $data->code }}";
                    }
                },
                "columns": [{
                        "data": 'DT_RowIndex'
                    },
                    {
                        "data": 'code'
                    },
                    {
                        "data": 'maintenanceDate'
                    },
                    {
                        "data": "items"
                    },
                    {
                        "data": "price"
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

            $('#filterForm').on('submit', function(e) {
                e.preventDefault();

                $('body').append(`
                        <div class="loader-wrapper">
                            <div class="loader">
                                <div class="loader4"></div>
                            </div>
                        </div>
                    `);

                let queryParams = $(this).serialize(); // Serialize the form data

                tableOrder.ajax.reload();
                tableMaintenance.ajax.reload();

                $.ajax({
                    url: "{{ route('ajax.profit-loss-summary', ['id' => $data->code]) }}",
                    method: 'GET',
                    data: {
                        fleetCode: "{{ $data->code }}",
                        startDate: $('input[name="startDate"]').val(),
                        endDate: $('input[name="endDate"]').val(),
                    },
                    success: function(response) {

                        // Update values in the page
                        $('.basicSales').text(new Intl.NumberFormat('id-ID').format(response
                            .basicSales));
                        $('.orderCost').text(new Intl.NumberFormat('id-ID').format(response
                            .orderCost));
                        $('.maintenance').text(new Intl.NumberFormat('id-ID').format(response
                            .maintenance));
                        $('.totalMargin').text(new Intl.NumberFormat('id-ID').format(response
                            .totalMargin));
                        $('.loader-wrapper').remove();

                    },


                    error: function(xhr) {
                        console.log(xhr.responseText);
                        $('.loader-wrapper').remove();
                    }


                });


            });
        });
    </script>
@endpush
