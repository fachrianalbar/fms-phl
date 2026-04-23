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
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/sweetalert2.css') }}">
<link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/select2.css') }}">

<link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/custom-select2.css') }}">

<style>
    /* Custom CSS untuk Select2 di modal */
    .modal .select2-container {
        z-index: 9999;
        width: 100% !important;
    }

    /* .modal .select2-container .select2-selection {
                                                                                                        height: 38px;
                                                                                                        border: 1px solid #ced4da;
                                                                                                        border-radius: 0.375rem;
                                                                                                    }

                                                                                                    .modal .select2-container .select2-selection__rendered {
                                                                                                        line-height: 36px;
                                                                                                        padding-left: 12px;
                                                                                                    }

                                                                                                    .modal .select2-container .select2-selection__arrow {
                                                                                                        height: 36px;
                                                                                                    } */

    .select2-dropdown {
        z-index: 99999 !important;
    }
</style>
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

                <a href="{{ route($view . 'excel-order', ['type' => 'order']) }}"
                    class="btn btn-icon btn-sm bg-success-subtle" id="export-data">
                    <i class="mdi mdi-file-excel fs-14 text-success"></i>
                </a>

                <button type="button" class="btn btn-icon btn-sm bg-info-subtle" id="modal-tax-btn">
                    <i class="mdi mdi-book-check fs-14 text-info"></i>
                </button>

                <a href="{{ route($view . 'create') }}" class="btn btn-primary">{{ __('general.add_data') }}</a>
            </div>

        </div>

        <div class="card-header">
            <div class="accordion-collapse collapse" id="collapseTwo" aria-labelledby="headingTwo"
                data-bs-parent="#simpleaccordion">
                <div class="accordion-body col-md-12">
                    <form id="filterForm" class=" g-3">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label" for="name">{{ __('menu_order.plate_number') }}</label>
                                <select class="js-example-basic-single" name="plateNumber" id="plateNumber">
                                    <option selected="" value="">{{ __('general.choose') }}...</option>
                                    @foreach ($fleet as $item)
                                    <option value="{{ $item->plateNumber }}">
                                        {{ $item->plateNumber }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="name">{{ __('menu_order.driver') }}</label>
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
                                <label class="form-label" for="name">{{ __('menu_order.customer') }}</label>
                                <select class="js-example-basic-single" name="customerName" id="customerName">
                                    <option selected="" value="">{{ __('general.choose') }}...</option>
                                    @foreach ($customer as $item)
                                    <option value="{{ $item->name }}">{{ $item->name }}
                                    </option>
                                    @endforeach
                                </select>`
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="name">{{ __('menu_order.fleet_type') }}</label>
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
                                <label class="form-label" for="name">{{ __('menu_order.shipment_no') }}</label>
                                <input class="form-control" name="shipmentNumber" type="text"
                                    placeholder="Shipment Number">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label" for="name">{{ __('menu_order.order_date') }}</label>
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
                            <div class="col-md-6">
                                <label class="form-label" for="name">{{ __('menu_order.destination') }}</label>
                                <select class="js-example-basic-single" name="destination" id="destination">
                                    <option selected="" value="">{{ __('general.choose') }}...</option>
                                    @foreach ($location as $item)
                                    <option value="{{ $item->name }}">{{ $item->name }}
                                    </option>
                                    @endforeach
                                </select>`
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="name">{{ __('menu_order.order_type') }}</label>
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
            <div class="table-responsive custom-scrollbar">
                <style>
                    /* Make driver column uppercase */
                    #dt td:nth-child(5) {
                        text-transform: uppercase;
                    }

                    /* Make shipment number uppercase */
                    #dt td:nth-child(7) {
                        text-transform: uppercase;
                    }

                    /* Make driver name in order drivers modal uppercase */
                    #order-driver-list td:nth-child(2) {
                        text-transform: uppercase;
                    }
                </style>
                <table class="table table-striped w-100 nowrap" id="dt">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>No</th>
                            <th>{{ __('menu_order.order_date') }}</th>
                            <th>{{ __('menu_order.plate_number') }}</th>
                            <th>Route Name</th>
                            <th>{{ __('menu_order.driver') }}</th>
                            <th>Order Type</th>
                            <th>{{ __('menu_order.shipment_no') }}</th>
                            <th>{{ __('menu_order.customer') }}</th>
                            {{-- <th>Material</th> --}}
                            <th>{{ __('menu_order.origin') }}</th>
                            <th>{{ __('menu_order.destination') }}</th>
                            <th>Price</th>
                            <th>Harga Vendor</th>
                            <th>Harga Vendor Pribadi</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade bd-example-modal-xl" id="note-modal" tabindex="-1" role="dialog"
        aria-labelledby="myLargeModalLabel" aria-hidden="true">
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

    <form method="post" action="{{ route($view . 'store-order-tax') }}">
        @csrf
        <div class="modal fade bd-example-modal-xl" id="modal-tax" tabindex="-1" role="dialog"
            aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myLargeModalLabel">Data {{ __('menu_order.order_tax') }}</h4>
                        <button class="btn-close py-0" type="button" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="card">
                        <div class="card-body col-md-12">
                            <div class="row g-3">
                                <table class="table table-striped w-100 nowrap" id="dt-order-tax">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>No</th>
                                            <th>{{ __('menu_order.order_date') }}</th>
                                            <th>{{ __('menu_order.plate_number') }}</th>
                                            <th>{{ __('menu_order.driver') }}</th>
                                            <th>Type</th>
                                            <th>{{ __('menu_order.shipment_no') }}</th>
                                            <th>{{ __('menu_order.customer') }}</th>
                                            <th>{{ __('menu_order.destination') }}</th>
                                            <th>Price</th>
                                            <th>Harga Vendor</th>
                                            <th>Harga Vendor Pribadi</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-start">
                        <button type="submit" id="saveOrderTax"
                            class="btn btn-primary">{{ __('general.save') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Modal Add Cost Component -->
    <div class="modal fade bd-example-modal-xl" id="modal-cost-component" tabindex="-1" role="dialog"
        aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Tambah Komponen Biaya</h4>
                    <button class="btn-close py-0" type="button" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="orderCode" id="costOrderCode">

                    <!-- Existing Cost Components Section -->
                    <div class="mb-4">
                        <h5>Daftar Komponen Biaya:</h5>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Komponen</th>
                                        {{-- <th>Type</th> --}}
                                        <th>Nominal</th>
                                    </tr>
                                </thead>
                                <tbody id="existing-cost-list">
                                    <!-- Existing costs will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-2">
                            <strong id="total-amount">Total: Rp 0</strong>
                        </div>
                    </div>

                    <hr>

                    <!-- Add New Cost Component Section -->
                    <div>
                        <h5>Tambah Komponen Biaya:</h5>
                        <form id="form-cost-component">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Nama Komponen Biaya</label>
                                    <select class="form-select select2-modal" name="componentType"
                                        id="componentType">
                                        <option selected="" disabled="" value="">Choose...</option>
                                    </select>



                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Nominal</label>
                                    <input class="form-control" type="text" oninput="formatAngka(this)"
                                        step="0.01" name="nominal" id="nominal" placeholder="Enter amount"
                                        required>
                                </div>
                                {{-- <div class="col-md-6">
                                        <label class="form-label">Cost Component Type</label>
                                        <select class="form-select select2-modal" name="costComponentType"
                                            id="costComponentType">
                                            <option selected="" disabled="" value="">Choose...</option>
                                            <option value="On Charge">On Charge</option>
                                            <option value="Off Charge">Off Charge</option>
                                        </select>
                                    </div> --}}
                            </div>
                            {{-- <div class="row mt-3">
                                    
                                </div> --}}
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btn-add-cost">Tambah Komponen Biaya</button>
                </div>
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

<div class="modal fade bd-example-modal-xl" id="modal-add-driver" tabindex="-1" role="dialog"
    aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myLargeModalLabel">{{ __('menu_order.change_driver') }}</h4>
                <button class="btn-close py-0" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="{{ route('operational.store-order-driver') }}" id="form-add-driver">
                @csrf
                <div class="card">
                    <div class="card-body col-md-12">
                        <div class="row g-3">
                            <input type="hidden" name="orderCode" id="orderCode">

                            <div class="col-md-12">
                                <label class="form-label" for="driverCode">{{ __('menu_order.driver') }}</label>
                                <br>
                                <select class="form-control select2-modal" name="driverCode" id="driverCode"
                                    style="width: 100%;">
                                    <option selected="" value="">{{ __('general.choose') }}...</option>
                                    @foreach ($driver as $item)
                                    <option value="{{ $item->code }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label"
                                    for="description">{{ __('menu_order.description') }}</label>
                                <textarea class="form-control" name="description" id="description" rows="3"
                                    placeholder="{{ __('menu_order.description') }}"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- List Data yang sudah diinput -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Data {{ __('menu_order.change_driver') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="order-driver-list">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>{{ __('menu_order.driver') }}</th>
                                        <th>{{ __('menu_order.description') }}</th>
                                        <th>{{ __('menu_order.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data akan dimuat via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="modal-footer justify-content-start">
                    <button type="submit" class="btn btn-primary">{{ __('general.save') }}</button>
                </div>
            </form>
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
<script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>
<script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
<script src=" {{ asset('assets/js/select2/select2-custom.js') }}"></script>
<script src=" {{ asset('assets/js/helper.js') }}"></script>

<script>
    let selectedOrders = [];

    $('#modal-tax-btn').on('click', function() {
        $('#modal-tax').modal('show');
    });


    $(document).ready(function() {
        const table = $('#dt').DataTable({
            "processing": true,
            "serverSide": true,
            "destroy": true,
            "ajax": {
                "url": "{{ route('dt.order') }}",
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
                    "data": 'route.name'
                },
                {
                    "data": 'driver.name'
                },
                {
                    "data": 'orderType'
                },
                {
                    "data": "shipmentNumber"
                },
                {
                    "data": 'customer.name'
                },
                // {
                //     "data": 'material.name'
                // },
                {
                    "data": 'route.originLocation.name'
                },
                {
                    "data": 'route.destinationLocation.name'
                },
                {
                    "data": 'price'
                },
                {
                    "data": 'harga_vendor'
                },
                {
                    "data": 'harga_vendor_pribadi'
                },
                {
                    "data": 'status'
                }


            ],
            "columnDefs": [{
                    "searchable": false,
                    "targets": [0, 1]
                },
                {
                    "orderable": false,
                    "targets": [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14]
                }
            ],

        })

        const tableTax = $('#dt-order-tax').DataTable({
            "processing": true,
            "serverSide": true,
            "destroy": true,
            "ajax": {
                "url": "{{ route('dt.order') }}",
                "data": function(d) {
                    d.is_order_tax = 0;
                }
            },
            "columns": [{
                    "data": 'actionTax'
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
                    "data": 'driver.name'
                },
                {
                    "data": 'orderType'
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
                {
                    "data": 'price'
                },
                {
                    "data": 'harga_vendor'
                },
                {
                    "data": 'harga_vendor_pribadi'
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

        // $('#modal-tax-btn').click(function() {
        //     $('#modal-tax').modal('show');
        // });


        // Event untuk form filter
        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            let queryParams = $(this).serialize(); // Serialize the form data

            let exportUrl = "{{ route($view . 'excel-order') }}?type=order&" + queryParams;

            $('#export-data').attr('href', exportUrl);


            table.ajax.reload(); // Reload DataTable dengan filter baru
        });

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

        $('#saveOrderTax').click(function(e) {
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
        });

        // Simpan state saat DataTable di-reload (misalnya saat pindah halaman)
        $('#dt-order-tax').on('draw.dt', function() {
            $('.order-checkbox').each(function() {
                const orderId = $(this).val();
                if (selectedOrders.includes(orderId)) {
                    $(this).prop('checked', true);
                }
            });
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
                            <td>${item.shipmentNumber ? item.shipmentNumber.toUpperCase() : ''}</td>
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

        // Event listener untuk modal add driver
        $('#modal-add-driver').on('shown.bs.modal', function() {
            // Initialize select2 saat modal ditampilkan
            $('.select2-modal').select2({
                dropdownParent: $('#modal-add-driver'),
                width: '100%',
                placeholder: '{{ __('general.choose') }}...',
            });
        });

        // Destroy select2 saat modal ditutup untuk menghindari konflik
        $('#modal-add-driver').on('hidden.bs.modal', function() {
            $('.select2-modal').select2('destroy');
        });

    });

    function finishOrder(id) {
        var url = '{{ route('operational.finish-order', ':id') }}';
        url = url.replace(':id', id);
        $('#finish-order').attr('action', url);

        swal({
            title: "{{ __('general.are_you_sure') }}",
            text: "{{ __('menu_order.want_to_finish_this_order') }}",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                $('#finish-order').submit();
            } else {
                swal("{{ __('general.your_data_is_save') }}");
            }
        });
    }

    function showModal(id) {
        let url = '{{ route('operational.order.show', ':id') }}';
        url = url.replace(':id', id);

        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'JSON',
            cache: false,
            success: function(data) {
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
        $('#note-modal').modal('show');
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

    function addOrderDriver(orderId, orderCode) {
        $('#orderCode').val(orderCode);
        $('#driverCode').val('').trigger('change');
        $('#description').val('');

        // Load existing order drivers
        loadOrderDrivers(orderCode);

        $('#modal-add-driver').modal('show');
    }

    function loadOrderDrivers(orderCode) {
        $.ajax({
            url: "{{ route('operational.order.get-order-drivers') }}",
            method: "GET",
            data: {
                orderCode: orderCode
            },
            success: function(response) {
                let tbody = $('#order-driver-list tbody');
                tbody.empty();

                if (response.success && response.data.length > 0) {
                    response.data.forEach((item, index) => {
                        tbody.append(`
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${item.driver ? item.driver.name.toUpperCase() : '-'}</td>
                                    <td>${item.description || '-'}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="deleteOrderDriver('${item.id}', '${orderCode}')">
                                            <i class="mdi mdi-delete fs-14"></i>
                                        </button>
                                    </td>
                                </tr>
                            `);
                    });
                } else {
                    tbody.append(`
                            <tr>
                                <td colspan="4" class="text-center">Belum ada data supirr</td>
                            </tr>
                        `);
                }
            },
            error: function() {
            }
        });
    }

    function deleteOrderDriver(id, orderCode) {
        swal({
            title: "{{ __('general.are_you_sure') }}",
            text: "Ingin menghapus data supir ini?",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                $.ajax({
                    url: "{{ route('operational.order.delete-order-driver') }}",
                    method: "DELETE",
                    data: {
                        id: id,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            swal("Sukses", "Data supir berhasil dihapus", "success");
                            loadOrderDrivers(orderCode); // Reload data
                        } else {
                            swal("Error", response.message, "error");
                        }
                    },
                    error: function() {
                        swal("Error", "Gagal menghapus data", "error");
                    }
                });
            }
        });
    }

    // Handle form submission untuk menambah order driver
    $('#form-add-driver').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    swal("Sukses", "{{ __('menu_order.change_driver_success') }}", "success");
                    $('#driverCode').val('').trigger('change');
                    $('#description').val('');
                    loadOrderDrivers($('#orderCode').val()); // Reload modal list
                    $('#modal-add-driver').modal('hide'); // Close modal

                    // Reload main order table if present
                    if ($('#dt').length) {
                        $('#dt').DataTable().ajax.reload(null, false);
                    }
                } else {
                    swal("Error", response.message ||
                        "{{ __('menu_order.change_driver_failed') }}", "error");
                }
            },
            error: function() {
                swal("Error", "{{ __('menu_order.change_driver_failed') }}", "error");
            }
        });
    });

    // Event listener untuk modal cost component
    $('#modal-cost-component').on('shown.bs.modal', function() {
        // Initialize select2 saat modal ditampilkan
        $('.select2-modal').select2({
            dropdownParent: $('#modal-cost-component'),
            width: '100%',
            placeholder: 'Pilih...',
        });
    });

    // Destroy select2 saat modal ditutup untuk menghindari konflik
    $('#modal-cost-component').on('hidden.bs.modal', function() {
        $('.select2-modal').select2('destroy');
    });

    function manageCostComponent(orderId, orderCode) {
        $('#costOrderCode').val(orderCode);

        // Load cost components untuk dropdown
        loadCostComponents();

        // Load existing order costs
        loadOrderCosts(orderCode);

        // Clear form
        $('#componentType').val('').trigger('change');
        $('#costComponentType').val('').trigger('change');
        $('#nominal').val('');

        $('#modal-cost-component').modal('show');
    }

    function loadCostComponents() {
        $.ajax({
            url: "{{ route('ajax.cost-components-all') }}",
            method: "GET",
            success: function(response) {
                let options = '<option selected="" disabled="" value="">Pilih...</option>';
                if (response && response.length > 0) {
                    response.forEach(function(item) {
                        options += `<option value="${item.code}">${item.name}</option>`;
                    });
                }
                $('#componentType').html(options);
            },
            error: function() {
            }
        });
    }

    function loadOrderCosts(orderCode) {
        $.ajax({
            url: "{{ route('operational.order.get-order-costs') }}",
            method: "GET",
            data: {
                orderCode: orderCode
            },
            success: function(response) {
                let tbody = $('#existing-cost-list');
                tbody.empty();

                let total = 0;

                if (response.success && response.data.length > 0) {
                    response.data.forEach(function(item) {
                        let nominal = parseFloat(item.nominal);
                        total += nominal;
                        let formattedNominal = new Intl.NumberFormat('id-ID').format(nominal);
                        tbody.append(`
                                <tr>
                                    <td>${item.cost_component ? item.cost_component.name : 'N/A'}</td>
                                    <td>Rp ${formattedNominal}</td>
                                </tr>
                            `);
                    });
                } else {
                    tbody.append(`
                            <tr>
                                <td colspan="3" class="text-center">Belum ada data</td>
                            </tr>
                        `);
                }

                // Update total
                let formattedTotal = new Intl.NumberFormat('id-ID').format(total);
                $('#total-amount').text(`Total: Rp ${formattedTotal}`);
            },
            error: function() {
            }
        });
    }

    function deleteOrderCost(id, orderCode) {
        swal({
            title: "Konfirmasi",
            text: "Yakin ingin menghapus komponen biaya ini?",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                $.ajax({
                    url: "{{ route('operational.order.delete-order-cost') }}",
                    method: "DELETE",
                    data: {
                        id: id,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            swal("Sukses", "Komponen biaya berhasil dihapus", "success");
                            loadOrderCosts(orderCode); // Reload data
                        } else {
                            swal("Error", response.message, "error");
                        }
                    },
                    error: function() {
                        swal("Error", "Gagal menghapus komponen biaya", "error");
                    }
                });
            }
        });
    }

    // Handle form submission untuk menambah cost component
    $('#btn-add-cost').on('click', function(e) {
        e.preventDefault();

        let formData = {
            orderCode: $('#costOrderCode').val(),
            componentType: $('#componentType').val(),
            costComponentType: $('#costComponentType').val(),
            nominal: $('#nominal').val(),
            _token: '{{ csrf_token() }}'
        };

        // Validasi
        // if (!formData.componentType) {
        //     swal("Warning", "Please select a cost component", "warning");
        //     return;
        // }

        // if (!formData.costComponentType) {
        //     swal("Warning", "Please select cost component type", "warning");
        //     return;
        // }

        if (!formData.nominal || formData.nominal <= 0) {
            swal("Warning", "Please enter a valid amount", "warning");
            return;
        }

        $.ajax({
            url: "{{ route('operational.order.store-order-cost') }}",
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    swal("Success", "Komponen biaya berhasil ditambahkan", "success");
                    // Clear form
                    $('#componentType').val('').trigger('change');
                    $('#costComponentType').val('').trigger('change');
                    $('#nominal').val('');
                    // Reload data
                    loadOrderCosts($('#costOrderCode').val());
                } else {
                    swal("Error", response.message || "Failed to add cost component", "error");
                }
            },
            error: function(xhr) {
                let message = "Failed to add cost component";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                swal("Error", message, "error");
            }
        });
    });
</script>
@endpush