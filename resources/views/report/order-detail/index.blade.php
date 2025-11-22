@extends('layouts.main', [
'title' => $title,
'pageTitle' => $title,
'firstSegment' => 'Report',
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
<style>
    /* Override DataTables responsive plugin to prevent horizontal scroll */
    .dataTables_wrapper {
        width: 100% !important;
        overflow: visible !important;
    }

    .table-responsive {
        overflow-x: visible !important;
        overflow-y: auto !important;
        max-height: 70vh !important;
    }

    /* Disable DataTables responsive (hide child rows) */
    table.dt-responsive tbody>tr>td.child {
        display: none !important;
    }

    /* Force table cells to not wrap based on responsive plugin */
    table.table-order {
        width: 100% !important;
        table-layout: auto !important;
    }

    /* Allow table cells to wrap and adjust height automatically */
    table.table-order td,
    table.table-order th {
        word-wrap: break-word !important;
        overflow-wrap: break-word !important;
        white-space: normal !important;
        max-width: none !important;
    }

    /* Cost detail: display as multiline vertical list */
    table.table-order td .cost-detail-list {
        display: block !important;
        line-height: 1.6 !important;
    }

    table.table-order td .cost-detail-item {
        display: block !important;
        margin-bottom: 4px !important;
    }
</style>
@endpush

@section('content')
<div class="col-sm-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>{{ __('menu_order_detail.order_detail_report') }}</h4>

            <div class="d-flex align-items-center gap-3">
                <div class="accordion-item ">
                    <a href="#" class="btn btn-icon btn-sm bg-dark-subtle" data-bs-toggle="collapse"
                        data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                        <i class="mdi mdi-magnify fs-14 text-dark"></i>
                    </a>
                </div>

                <a href="{{ route($view . 'excel-order-detail') }}" target="_blank" id="export-excel"
                    class="btn btn-icon btn-sm bg-success-subtle">
                    <i class="mdi mdi-file-excel fs-14 text-success"></i>
                </a>

                <a href="{{ route($view . 'pdf-order-detail') }}" target="_blank" id="export-pdf"
                    class="btn btn-icon btn-sm bg-danger-subtle">
                    <i class="mdi mdi-file-pdf fs-14 text-danger"></i>
                </a>
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
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="name">{{ __('menu_order.fleet_type') }}</label>
                                <select class="js-example-basic-single" name="fleetTypeName" id="fleetTypeName">
                                    <option selected="" value="">{{ __('general.choose') }}...</option>
                                    @foreach ($fleetType as $item)
                                    <option value="{{ $item->name }}">{{ $item->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <label class="form-label" for="name">{{ __('menu_order.shipment_no') }}</label>
                                <input class="form-control" name="shipmentNumber" type="text"
                                    placeholder="{{ __('menu_order.shipment_no') }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label" for="name">{{ __('menu_order.order_date') }}</label>
                                <input class="form-control" name="startDate" id="datetime-local" type="date"
                                    placeholder="{{ __('menu_order_detail.start_date') }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label" for="name"></label>
                                <input class="form-control" name="endDate" id="datetime-local" type="date"
                                    placeholder="{{ __('menu_order_detail.end_date') }}">
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <label class="form-label" for="name">{{ __('menu_order.origin') }}</label>
                                <select class="js-example-basic-single" name="origin" id="origin">
                                    <option selected="" value="">{{ __('general.choose') }}...</option>
                                    @foreach ($location as $item)
                                    <option value="{{ $item->name }}">{{ $item->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="name">{{ __('menu_order.destination') }}</label>
                                <select class="js-example-basic-single" name="destination" id="destination">
                                    <option selected="" value="">{{ __('general.choose') }}...</option>
                                    @foreach ($location as $item)
                                    <option value="{{ $item->name }}">{{ $item->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="d-flex gap-2 mt-3">
                            <button class="btn btn-primary" type="submit">{{ __('menu_order_detail.filter') }}</button>
                            <button type="button" id="resetFilter" class="btn btn-secondary">{{ __('general.reset') }}</button>
                        </div>

                    </form>
                    </form>
                </div>
            </div>
        </div>

        <div class="card-body">
            @include('partials.alert')
            <div class="table-responsive ">
                <table class="table table-order table-bordered nowrap" id="dt">
                    <thead>
                        <tr>
                            <th>{{ __('menu_order_detail.no') }}</th>
                            <th>{{ __('menu_order.shipment_no') }}</th>
                            <th>{{ __('menu_order.order_date') }}</th>
                            <th>{{ __('menu_order.customer') }}</th>
                            <th>{{ __('menu_order.origin') }}</th>
                            <th>{{ __('menu_order.destination') }}</th>
                            <th>{{ __('menu_order.plate_number') }}</th>
                            <th>{{ __('menu_order.driver') }}</th>
                            <th>{{ __('menu_order_detail.sales') }}</th>
                            <th>{{ __('menu_order_detail.cost_detail') }}</th>
                            <th>{{ __('menu_order_detail.total_cost') }}</th>
                            <th>{{ __('menu_order_detail.profit') }}</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('script')
<script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-keytable-bs5/js/keyTable.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>
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
                "url": "{{ route('dt.order-detail') }}",
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
                    "data": 'driver.name'
                },
                {
                    "data": 'sales'
                },
                {
                    "data": 'cost_detail'
                },
                {
                    "data": "total_cost"
                },
                {
                    "data": 'profit'
                }
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
                [2, 'desc']
            ]
        })

        // Event untuk form filter
        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            let queryParams = $(this).serialize();

            let exportExcelUrl = "{{ route($view . 'excel-order-detail') }}" + "?" + queryParams;
            let exportPdfUrl = "{{ route($view . 'pdf-order-detail') }}" + "?" + queryParams;

            $('#export-excel').attr('href', exportExcelUrl);
            $('#export-pdf').attr('href', exportPdfUrl);

            table.ajax.reload();
        });

        // Reset filter button
        $('#resetFilter').on('click', function() {
            // Clear plain inputs
            $('input[name="shipmentNumber"]').val('');
            $('input[name="startDate"]').val('');
            $('input[name="endDate"]').val('');

            // Clear select2/select fields and trigger change for select2
            var selects = ['plateNumber', 'driverName', 'customerName', 'fleetTypeName', 'origin', 'destination', 'orderTypeCode'];
            selects.forEach(function(name) {
                var el = $('select[name="' + name + '"]');
                if (el.length) {
                    el.val('');
                    try {
                        el.trigger('change');
                    } catch (e) {}
                }
            });

            // Reset export links to base (no query)
            $('#export-excel').attr('href', "{{ route($view . 'excel-order-detail') }}");
            $('#export-pdf').attr('href', "{{ route($view . 'pdf-order-detail') }}");

            table.ajax.reload();
        });
    });
</script>
@endpush