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

    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/select2.css') }}">

    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/custom-select2.css') }}">
@endpush

@section('content')
    <form class="col-sm-12" method="POST" action="{{ route('operational.not-return-do.confirm-do') }}">
        <div class="card">
            @csrf
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }} Data</h4>

                <div class="d-flex align-items-center gap-3">
                    <div class="accordion-item ">
                        <a href="#" class="btn btn-icon btn-sm bg-dark-subtle" data-bs-toggle="collapse"
                            data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            <i class="mdi mdi-magnify fs-14 text-dark"></i>
                        </a>


                    </div>

                    {{-- <button type="submit" class="btn btn-primary" s>
                        {{ __('menu_not_return_do.confirm_return') }}
                    </button> --}}

                </div>



            </div>

            <div class="card-header">
                <div class="accordion-collapse collapse" id="collapseTwo" aria-labelledby="headingTwo"
                    data-bs-parent="#simpleaccordion">
                    <div class="accordion-body col-md-12">
                        <div id="filterForm" class="g-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label" for="name">{{ __('menu_order.plate_number') }}</label>
                                    <select class="js-example-basic-single" name="plateNumber" id="plateNumber">
                                        <option selected="" value="">{{ __('general.choose') }}...</option>
                                        @foreach ($fleet as $item)
                                            <option value="{{ $item->plateNumber }}">
                                                {{ $item->plateNumber }}</option>
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
                            </div>
                            <button class="btn btn-primary mt-3" type="button" id="btnFilter">Filter</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body">
                @include('partials.alert')
                <div class="table-responsive custom-scrollbar">
                    <table class="table table-striped w-100 nowrap" id="dt">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('menu_not_return_do.no') }}</th>
                                <th>{{ __('menu_not_return_do.order_date') }}</th>
                                <th>{{ __('menu_not_return_do.shipment_no') }}</th>
                                <th>{{ __('menu_not_return_do.customer_name') }}</th>
                                <th>{{ __('menu_not_return_do.origin') }}</th>
                                <th>{{ __('menu_not_return_do.destination') }}</th>
                                <th>{{ __('menu_not_return_do.fleet') }}</th>
                                <th>{{ __('menu_not_return_do.driver') }}</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                </div>
            </div>
        </div>

        <div class="modal fade bd-example-modal-lg" id="do-modal" tabindex="-1" role="dialog"
            aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myLargeModalLabel">
                            {{ __('menu_not_return_do.not_return_data') }}
                        </h4>
                        <button class="btn-close py-0" type="button" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>

                    <div class="card">
                        <div class="card-body col-md-12">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label" for="returnDate">
                                        {{ __('menu_not_return_do.date') }} <i
                                            class="mdi mdi-information text-danger"></i>
                                    </label>
                                    <input class="form-control" name="returnDate" id="returnDate" type="date"
                                        required placeholder="{{ __('menu_not_return_do.date') }}">
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label"
                                        for="description">{{ __('menu_not_return_do.description') }}</label>
                                    <textarea class="form-control" name="returnDescription" id="description"
                                        placeholder="{{ __('menu_not_return_do.description') }}" rows="4"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer justify-content-start">
                            <button class="btn btn-primary" type="submit">
                                {{ __('menu_not_return_do.save_data') }}
                            </button>
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

    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src=" {{ asset('assets/js/select2/select2-custom.js') }}"></script>
    {{-- <script src="../assets/js/sweet-alert/app.js"></script> --}}

    <script>
        $(document).ready(function() {
            const table = $('#dt').DataTable({
                processing: true,
                serverSide: true,
                destroy: true,
                ajax: {
                    url: "{{ route('dt.not-return-do') }}",
                    data: function(d) {
                        d.plateNumber = $('select[name="plateNumber"]').val();
                        d.customerName = $('select[name="customerName"]').val();
                        d.driverName = $('select[name="driverName"]').val();
                        d.fleetTypeName = $('select[name="fleetTypeName"]').val();
                        d.shipmentNumber = $('input[name="shipmentNumber"]').val();
                        d.startDate = $('input[name="startDate"]').val();
                        d.endDate = $('input[name="endDate"]').val();
                        d.destination = $('select[name="destination"]').val();
                        d.orderTypeCode = $('select[name="orderTypeCode"]').val();
                    }
                },
                columns: [{
                        data: 'action',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'DT_RowIndex'
                    },
                    {
                        data: 'orderDate'
                    },
                    {
                        data: 'shipmentNumber'
                    },
                    {
                        data: 'customer.name'
                    },
                    {
                        data: 'route.originLocation.name'
                    },
                    {
                        data: 'route.destinationLocation.name'
                    },
                    {
                        data: 'fleet.plateNumber'
                    },
                    {
                        data: 'driver.name'
                    }
                ],
                columnDefs: [{
                        searchable: false,
                        targets: [0, 1, 8]
                    },
                    {
                        orderable: false,
                        targets: [0, 1, 2, 3, 4, 5, 6, 7, 8]
                    },
                ],
                order: [
                    [2, 'asc']
                ],
            });
        });


        let selectedOrders = [];

        // ---------- Helpers ----------
        function addSelected(id) {
            if (!selectedOrders.includes(id)) selectedOrders.push(id);
        }

        function removeSelected(id) {
            selectedOrders = selectedOrders.filter(x => x !== id);
        }

        function setAllOnPage(checked) {
            $('.order-checkbox').each(function() {
                const id = $(this).val();
                $(this).prop('checked', checked);
                if (checked) addSelected(id);
                else removeSelected(id);
            });
        }

        function syncHeaderState() {
            const $rows = $('.order-checkbox');
            const total = $rows.length;
            const checked = $rows.filter(':checked').length;
            const header = document.getElementById('checkAll');
            if (!header) return;

            if (total === 0) {
                header.checked = false;
                header.indeterminate = false;
                return;
            }

            if (checked === 0) {
                header.checked = false;
                header.indeterminate = false;
            } else if (checked === total) {
                header.checked = true;
                header.indeterminate = false;
            } else {
                header.checked = false;
                header.indeterminate = true;
            }
        }

        // ---------- Submit handler ----------
        document.getElementById('saveOrder').addEventListener('click', function(event) {
            if (selectedOrders.length === 0) {
                event.preventDefault();
                swal({
                    title: "{{ __('general.warning') }}",
                    text: "Please select at least one item",
                    icon: "warning",
                });
                return;
            }

            // inject hidden input (remove previous if any)
            $('input[name="selectedOrders"]').remove();
            $('<input>').attr({
                type: 'hidden',
                name: 'selectedOrders',
                value: JSON.stringify(selectedOrders)
            }).appendTo('form');

            event.preventDefault();
            $('#do-modal').modal('show');
        });

        // ---------- DataTable init ----------
        $(function() {


            // Filter button
            $('#btnFilter').on('click', function() {
                table.ajax.reload();
            });

            // When table draws (paging/sorting/filter), restore checkbox states on visible rows
            $('#dt').on('draw.dt', function() {
                $('.order-checkbox').each(function() {
                    const id = $(this).val();
                    $(this).prop('checked', selectedOrders.includes(id));
                });
                syncHeaderState();
            });
        });

        // ---------- Row checkbox change (delegate) ----------
        $(document).on('change', '.order-checkbox', function() {
            const id = $(this).val();
            if ($(this).is(':checked')) addSelected(id);
            else removeSelected(id);
            syncHeaderState();
        });

        // ---------- Header CheckAll (delegate in case header is re-rendered) ----------
        $(document).on('click', '#checkAll', function() {
            setAllOnPage(this.checked);
            syncHeaderState();
        });
    </script>
@endpush
