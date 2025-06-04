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
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/select2.css') }}">

    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/custom-select2.css') }}">
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }} Data</h4>

                {{-- <a href="{{ route($view . 'create') }}" class="btn btn-primary">{{ __('general.add_data') }}</a> --}}

                <div class="d-flex align-items-center gap-3">
                    <div class="accordion-item ">

                        <a href="#" class="btn btn-icon btn-sm bg-dark-subtle" data-bs-toggle="collapse"
                            data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            <i class="mdi mdi-magnify fs-14 text-dark"></i>
                        </a>


                    </div>

                    <button type="button" class="btn btn-warning" id="check-null"><i
                            class="icofont icofont-warning-alt"></i></button>

                    {{-- <a href="{{ route($view . 'excel-order', ['type' => 'order-office']) }}" class="btn btn-info"
                        id="export-data">Export Data</a> --}}

                    <a href="{{ route($view . 'excel-order', ['type' => 'order-office']) }}" class="btn btn-success"
                        id="export-data"> <i class="icofont icofont-file-excel"></i></a>
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
                                        <option selected="" value="">{{ __('general.choose') }}...</option>
                                        @foreach ($fleet as $item)
                                            <option value="{{ $item->plateNumber }}">
                                                {{ $item->plateNumber }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="name">Driver Name</label>
                                    <select class="js-example-basic-single" name="driverName" id="driverName">
                                        <option selected="" value="">{{ __('general.choose') }}...</option>
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
                                        <option selected="" value="">{{ __('general.choose') }}...</option>
                                        @foreach ($customer as $item)
                                            <option value="{{ $item->name }}">{{ $item->name }}
                                            </option>
                                        @endforeach
                                    </select>`
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="name">Fleet Type Name</label>
                                    <select class="js-example-basic-single" name="fleetTypeName" id="fleetTypeName">
                                        <option selected="" value="">{{ __('general.choose') }}...</option>
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
                                    <label class="form-label" for="name">Destination</label>
                                    <select class="js-example-basic-single" name="destination" id="destination">
                                        <option selected="" value="">{{ __('general.choose') }}...</option>
                                        @foreach ($location as $item)
                                            <option value="{{ $item->name }}">{{ $item->name }}
                                            </option>
                                        @endforeach
                                    </select>`
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="name">Order Type</label>
                                    <select class="js-example-basic-single" name="orderTypeCode" id="orderTypeCode">
                                        <option selected="" value="">{{ __('general.choose') }}...</option>
                                        @foreach ($orderType as $item)
                                            <option value="{{ $item->code }}">{{ $item->name }}
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
                    <table class="table table-order table-bordered dt-responsive table-responsive nowrap" id="dt">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>No</th>
                                <th>Order Date</th>
                                <th>Fleet</th>
                                <th>Fleet Type</th>
                                <th>Driver</th>
                                <th>Shipment No</th>
                                <th>Customer Name</th>
                                {{-- <th>Destination</th>
                                <th>Sales Order</th>
                                <th>S.T.O</th>
                                <th>Material</th> --}}
                                <th>Order Type</th>
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
        </div>

        <div class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myLargeModalLabel">Note Data</h4>
                        <button class="btn-close py-0" type="button" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="card">
                        <div class="card-body col-md-12">
                            <div class="row g-3">
                                <input class="form-control" type="text" id="note" readonly>
                            </div>
                            {{-- <div class="modal-footer justify-content-start">
                            </div> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog" id="modal-null"
            aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myLargeModalLabel">Empty Data</h4>
                        <button class="btn-close py-0" type="button" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="card">
                        <div class="card-body col-md-12">

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
                    "url": "{{ route('dt.office-order') }}",
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
                    }
                },
                "columns": [{
                        "data": 'action'
                    },
                    {
                        "data": 'DT_RowIndex'
                    },
                    {
                        "data": 'orderDate'
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
                        "data": "shipmentNumber"
                    },
                    {
                        "data": 'customer.name'
                    },
                    {
                        "data": 'route.destinationLocation.name'
                    },
                    // {
                    //     "data": 'salesOrder'
                    // },
                    // {
                    //     "data": 'sto'
                    // },
                    // {
                    //     "data": "material.name"
                    // },
                    // {
                    //     "data": 'orderTypeCode'
                    // },
                    {
                        "data": 'qty'
                    },
                    {
                        "data": 'cost'
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

            // Event untuk form filter
            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                let queryParams = $(this).serialize(); // Serialize the form data

                let exportUrl = "{{ route($view . 'excel-order') }}?type=order-office&" + queryParams;

                $('#export-data').attr('href', exportUrl);


                table.ajax.reload(); // Reload DataTable dengan filter baru
            });

            $('#check-null').on('click', function() {
                $.ajax({
                    url: "{{ route('operational.order.check-null-relation') }}", // Endpoint untuk mengecek relasi null
                    method: "GET",
                    success: function(response) {
                        if (response.success) {
                            let data = response.data;

                            // Buat tabel dengan Bootstrap
                            let content = `
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Shipment Number</th>
                                <th>Empty Data</th>
                            </tr>
                        </thead>
                        <tbody>
                `;

                            data.forEach((item, index) => {
                                content += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${item.shipmentNumber}</td>
                            <td>${item.nullRelations}</td>
                        </tr>
                    `;
                            });

                            content += `
                        </tbody>
                    </table>
                `;

                            $('#modal-null .card-body').html(content); // Isi modal dengan data
                            $('#modal-null').modal('show'); // Tampilkan modal
                        } else {
                            swal({
                                title: "Info",
                                text: "No empty data found",
                                icon: "info",
                            })
                        }
                    },
                    error: function() {
                        swal({
                            title: "{{ __('general.warning') }}",
                            text: "An error occurred while checking null relations.",
                            icon: "warning",
                        })
                    }
                });
            });
        });

        function showModal(id) {
            let url = '{{ route('operational.order.show', ':id') }}'
            url = url.replace(':id', id)

            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'JSON',
                cache: false,
                success: function(data) {
                    console.log(data);
                    $('#note').val(data.notes)
                    // if (r.success) {
                    //     let data = r.data
                    //     $('#name').val(data.name)
                    // }
                },
                error: function(data, ajaxOptions, thrownError) {
                    // notif('{{ $title }}', thrownError, 'danger')
                }
            })
            $('.bd-example-modal-xl').modal('show');
        }

        function deleteData(uuid) {
            var url = '{{ route('operational.order.index') }}/' + uuid;
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
