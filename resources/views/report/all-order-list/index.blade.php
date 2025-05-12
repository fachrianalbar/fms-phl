@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Operational',
    'secondSegment' => $title,
])

@push('style')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/datatables.css') }}">
    <link rel="stylesheet" type="text/css" href="../assets/css/vendors/sweetalert2.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/select2.css') }}">
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }} Data</h4>

                <div class="d-flex align-items-center gap-3">
                    <div class="accordion-item ">

                        <button class=" collapsed btn btn-light active" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo"><i
                                class="icofont icofont-search-alt-1"></i></i>
                        </button>

                        <a href="{{ route($view . 'excel-all-order-list') }}" class="btn btn-success" id="export-data">
                            <i class="icofont icofont-file-excel"></i>
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
                                    <label class="form-label" for="name">Plate Number</label>
                                    <select class="js-example-basic-single" name="plateNumber" id="plateNumber">
                                        <option selected="" value="">Choose...</option>
                                        @foreach ($fleet as $item)
                                            <option value="{{ $item->plateNumber }}">
                                                {{ $item->plateNumber }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="name">Driver Name</label>
                                    <select class="js-example-basic-single" name="driverName" id="driverName">
                                        <option selected="" value="">Choose...</option>
                                        @foreach ($driver as $item)
                                            <option value="{{ $item->name }}">{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <label class="form-label" for="name">Customer Name</label>
                                    <select class="js-example-basic-single" name="customerName" id="customerName">
                                        <option selected="" value="">Choose...</option>
                                        @foreach ($customer as $item)
                                            <option value="{{ $item->name }}">{{ $item->name }}
                                            </option>
                                        @endforeach
                                    </select>`
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="name">Fleet Type Name</label>
                                    <select class="js-example-basic-single" name="fleetTypeName" id="fleetTypeName">
                                        <option selected="" value="">Choose...</option>
                                        @foreach ($fleetType as $item)
                                            <option value="{{ $item->name }}">{{ $item->name }}
                                            </option>
                                        @endforeach
                                    </select>`
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <label class="form-label" for="name">Shipment Number</label>
                                    <input class="form-control" name="shipmentNumber" type="text"
                                        placeholder="Shipment Number">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label" for="name">Order Date</label>
                                    <input class="form-control" name="startDate" id="datetime-local" type="date"
                                        placeholder="Start Date">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label" for="name"></label>
                                    <input class="form-control" name="endDate" id="datetime-local" type="date"
                                        placeholder="End Date">
                                </div>
                            </div>

                            <div class="row mt-4">
                                {{-- <div class="col-md-6">
                                    <label class="form-label" for="name">Origin</label>
                                    <input class="form-control" name="origin" type="text" placeholder="Origin">
                                </div> --}}

                                <div class="col-md-6">
                                    <label class="form-label" for="name">Origin</label>
                                    <select class="js-example-basic-single" name="origin" id="origin">
                                        <option selected="" value="">Choose...</option>
                                        @foreach ($location as $item)
                                            <option value="{{ $item->name }}">{{ $item->name }}
                                            </option>
                                        @endforeach
                                    </select>`
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="name">Destination</label>
                                    <select class="js-example-basic-single" name="destination" id="destination">
                                        <option selected="" value="">Choose...</option>
                                        @foreach ($location as $item)
                                            <option value="{{ $item->name }}">{{ $item->name }}
                                            </option>
                                        @endforeach
                                    </select>`
                                </div>


                            </div>
                            <button class="btn btn-primary mt-3" type="submit">Filter</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card-body">
                @include('partials.alert')
                <div class="table-responsive ">
                    <table class="display table-order" id="dt">
                        <thead>
                            <tr>
                                <th colspan="11" class="text-center">All Order Data</th>
                                <th colspan="1" class="text-center">Sales</th>
                                <th colspan="5" class="text-center">Cost</th>
                                <th colspan="1" class="text-center">Margin</th>
                            </tr>
                            <tr>
                                <th>No</th>
                                <th>Shipment No</th>
                                <th>Order Dates</th>
                                <th>Customer Name</th>
                                <th>Origin</th>
                                <th>Destination</th>
                                <th>Fleet</th>
                                <th>Fleet Type</th>
                                <th>Driver</th>
                                <th>Material</th>
                                <th>Qty Tonase</th>
                                <th>Basic Sales</th>
                                <th>Basic Allowance</th>
                                <th>Additional Cost</th>
                                <th>Tonase</th>
                                <th>Gaji</th>
                                <th>Total Cost</th>
                                <th>Total Margin</th>
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
    <script src="{{ asset('assets/js/flat-pickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/custom-flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src=" {{ asset('assets/js/select2/select2-custom.js') }}"></script>
    <script>
        $(document).ready(function() {
            const table = $('#dt').DataTable({
                "processing": true,
                "serverSide": true,
                "destroy": true,
                "ajax": {
                    "url": "{{ route('dt.all-order-list') }}",
                    "data": function(d) {
                        d.plateNumber = $('select[name="plateNumber"]').val();
                        d.customerName = $('select[name="customerName"]').val();
                        d.driverName = $('select[name="driverName"]').val();
                        d.fleetTypeName = $('select[name="fleetTypeName"]').val();
                        d.shipmentNumber = $('input[name="shipmentNumber"]').val();
                        d.startDate = $('input[name="startDate"]').val();
                        d.endDate = $('input[name="endDate"]').val();
                        d.origin = $('select[name="origin"]').val();
                        d.destination = $('select[name="destination"]').val();
                        d.orderTypeCode = $('select[name="orderTypeCode"]').val();
                    }
                },
                "columns": [{
                        "data": 'DT_RowIndex'
                    },
                    {
                        "data": "shipmentNumber"
                    },
                    {
                        "data": 'orderDate'
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
                        "data": 'fleet.type.name'
                    },
                    {
                        "data": 'driver.name'
                    },
                    {
                        "data": "material.name"
                    },
                    {
                        "data": 'qty'
                    },
                    {
                        "data": 'basic_sales'
                    },
                    {
                        "data": 'basic_allowance'
                    },
                    {
                        "data": 'addCost'
                    },
                    {
                        "data": 'bonus'
                    },
                    {
                        "data": 'gaji'
                    },
                    {
                        "data": "total_cost"
                    },
                    {
                        "data": 'total_margin'
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
                    [1, 'desc']
                ]
            })

            // Event untuk form filter
            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                let queryParams = $(this).serialize(); // Serialize the form data

                let exportUrl = "{{ route($view . 'excel-all-order-list') }}" + "?" + queryParams;


                $('#export-data').attr('href', exportUrl);


                table.ajax.reload(); // Reload DataTable dengan filter baru
            });
        });

        function deleteData(uuid) {
            var url = '{{ route('operational.order.index') }}/' + uuid;
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
