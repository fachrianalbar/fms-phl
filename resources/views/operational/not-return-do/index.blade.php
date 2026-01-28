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
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/sweetalert2.css') }}">

    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/select2.css') }}">

    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/custom-select2.css') }}">

    <style>
        /* Modal */
        #detailModal .modal-dialog {
            max-width: 1200px;
        }

        #detailModal .modal-content {
            border: none;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        #detailModal .modal-header {
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            padding: 16px 20px;
        }

        #detailModal .modal-title {
            font-size: 1rem;
            font-weight: 600;
            color: #212529;
        }

        #detailModal .btn-close {
            opacity: 0.5;
        }

        #detailModal .btn-close:hover {
            opacity: 0.7;
        }

        #detailModal .modal-body {
            padding: 16px 20px;
            background: #fff;
        }

        /* Detail Order Card */
        .detail-order-card {
            background: transparent;
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 0;
            border: 1px solid #dee2e6;
        }

        .card-header-custom {
            background: #f8f9fa;
            color: #212529;
            padding: 12px 16px;
            font-weight: 600;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #dee2e6;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .card-body-custom {
            padding: 0;
        }

        .info-item {
            background: transparent;
            padding: 8px 16px;
            border-radius: 0;
            border-left: none;
            margin-bottom: 0;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            min-height: 38px;
        }

        .info-item:last-child {
            margin-bottom: 0;
            border-bottom: none;
        }

        .info-item:nth-child(odd) {
            background: #fafbfc;
        }

        .info-item label {
            display: block;
            font-size: 0.75rem;
            color: #6c757d;
            margin-bottom: 0;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            font-weight: 600;
            min-width: 140px;
            padding-right: 16px;
        }

        .info-item p {
            margin: 0;
            color: #212529;
            font-weight: 500;
            font-size: 0.9rem;
            text-align: right;
            flex: 1;
        }

        /* Form Section */
        .form-section {
            background: #f8f9fa;
            border-radius: 6px;
            padding: 16px;
            border: 1px solid #e9ecef;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }

        .form-group-custom {
            margin-bottom: 12px;
        }

        .form-group-custom:last-child {
            margin-bottom: 0;
        }

        .form-label-custom {
            display: flex;
            align-items: center;
            font-weight: 600;
            font-size: 0.85rem;
            color: #495057;
            margin-bottom: 6px;
        }

        .form-label-custom i {
            color: #667eea;
            font-size: 1rem;
            margin-right: 6px;
        }

        .form-label-custom .required {
            color: #e74c3c;
            margin-left: 2px;
        }

        .form-control-custom {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            background: white;
        }

        .form-control-custom:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.08);
        }

        .form-control-custom::placeholder {
            color: #adb5bd;
        }

        textarea.form-control-custom {
            min-height: 80px;
            resize: vertical;
            font-family: inherit;
        }

        .form-hint {
            display: block;
            margin-top: 4px;
            font-size: 0.75rem;
            color: #6c757d;
        }

        #detailModal .modal-footer {
            padding: 12px 20px;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
            gap: 6px;
        }

        .btn-cancel {
            padding: 7px 16px;
            border: 1px solid #dee2e6;
            background: white;
            border-radius: 4px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .btn-confirm {
            padding: 7px 16px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 4px;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .btn-confirm:hover {
            background: #5568d3;
        }

        /* Flatpickr Custom Style */
        .flatpickr-calendar {
            border-radius: 6px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
        }

        .flatpickr-day.selected {
            background: #667eea;
            border-color: #667eea;
        }

        .flatpickr-day.selected:hover {
            background: #5568d3;
            border-color: #5568d3;
        }

        /* Legacy support */
        .bg-teal {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        }

        .text-teal {
            color: #667eea !important;
        }
    </style>
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

                    <button type="button" id="saveOrder" class="btn btn-primary" style="display: none;">
                        <i class="mdi mdi-calendar-check"></i> {{ __('menu_not_return_do.confirm_return') }}
                    </button>

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
                    <style>
                        /* Make driver column uppercase */
                        #dt td:nth-child(5) {
                            text-transform: uppercase;
                        }

                        /* Make shipment number uppercase */
                        #dt td:nth-child(7) {
                            text-transform: uppercase;
                        }
                    </style>
                    <table class="table table-striped w-100 nowrap" id="dt">
                        <thead>
                            <tr>
                                <th>Aksi</th>
                                <th>No</th>
                                <th>{{ __('menu_order.order_date') }}</th>
                                <th>{{ __('menu_order.plate_number') }}</th>
                                <th>Route Name</th>
                                <th>{{ __('menu_order.driver') }}</th>
                                <th>Order Type</th>
                                <th>{{ __('menu_order.shipment_no') }}</th>
                                <th>{{ __('menu_order.customer') }}</th>
                                <th>{{ __('menu_order.origin') }}</th>
                                <th>{{ __('menu_order.destination') }}</th>
                                <th>Price</th>
                                <th>Harga Vendor</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                </div>
            </div>
        </div>

        <div class="modal fade" id="detailModal" tabindex="-1" role="dialog"
            aria-labelledby="detailModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <!-- Header -->
                    <div class="modal-header">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle me-3">
                                <i class="mdi mdi-arrow-u-left-bottom"></i>
                            </div>
                            <div>
                                <h5 class="modal-title mb-0" id="doModalLabel">Konfirmasi Return Order</h5>
                                <small class="text-muted">Lengkapi data untuk konfirmasi return</small>
                            </div>
                        </div>
                        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <!-- Single Item - Full Details -->
                        <div id="singleItemDetails" style="display: none;">
                            <div class="detail-order-card">
                                <div class="card-header-custom">
                                    <i class="mdi mdi-file-document-outline me-2"></i>
                                    <span>Detail Order</span>
                                </div>
                                <div class="card-body-custom">
                                    <div class="info-item">
                                        <label>Shipment No.</label>
                                        <p id="singleShipmentNo">-</p>
                                    </div>
                                    <div class="info-item">
                                        <label>Order Date</label>
                                        <p id="singleOrderDate">-</p>
                                    </div>
                                    <div class="info-item">
                                        <label>Customer</label>
                                        <p id="singleCustomerName">-</p>
                                    </div>
                                    <div class="info-item">
                                        <label>Fleet</label>
                                        <p id="singleFleet">-</p>
                                    </div>
                                    <div class="info-item">
                                        <label>Origin</label>
                                        <p id="singleOrigin">-</p>
                                    </div>
                                    <div class="info-item">
                                        <label>Destination</label>
                                        <p id="singleDestination">-</p>
                                    </div>
                                    <div class="info-item">
                                        <label>Driver</label>
                                        <p id="singleDriver">-</p>
                                    </div>
                                    <div class="info-item">
                                        <label>Order Type</label>
                                        <p id="singleOrderType">-</p>
                                    </div>
                                    <div class="info-item">
                                        <label>Harga</label>
                                        <p id="singlePrice">-</p>
                                    </div>
                                    <div class="info-item" id="vendorPriceContainer" style="display: none;">
                                        <label>Harga Vendor</label>
                                        <p id="singleVendorPrice">-</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Section -->
                        <div class="form-section">
                            <div class="row g-3">
                                <!-- Date & Time -->
                                <div class="col-12">
                                    <div class="form-group-custom">
                                        <label class="form-label-custom">
                                            <i class="mdi mdi-calendar-clock me-2"></i>
                                            Tanggal & Waktu Return
                                            <span class="required">*</span>
                                        </label>
                                        <input class="form-control-custom" name="returnDate" id="returnDate"
                                            type="text" placeholder="Pilih tanggal dan waktu" required>
                                        <small class="form-hint">Format: DD/MM/YYYY, HH:MM</small>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="col-12">
                                    <div class="form-group-custom">
                                        <label class="form-label-custom">
                                            <i class="mdi mdi-text-box-outline me-2"></i>
                                            Keterangan
                                        </label>
                                        <textarea class="form-control-custom" name="returnDescription" id="description"
                                            placeholder="Masukkan keterangan (opsional)" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="modal-footer">
                        <button class="btn btn-cancel" type="button" data-bs-dismiss="modal">
                            <i class="mdi mdi-close-circle-outline me-2"></i>
                            Batal
                        </button>
                        <a href="#" class="btn btn-secondary" id="editOrderBtn">
                            <i class="mdi mdi-pencil me-2"></i>
                            Edit Order
                        </a>
                        <button class="btn btn-confirm" type="submit" id="submitReturnBtn">
                            <i class="mdi mdi-check-circle-outline me-2"></i>
                            Konfirmasi Return
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Modal for Return Processing -->
    <div class="modal fade" id="returnModal" tabindex="-1" aria-labelledby="returnModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="returnModalLabel">
                        <i class="mdi mdi-calendar-clock"></i> {{ __('menu_not_return_do.process_return') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="returnForm">
                    @csrf
                    <input type="hidden" id="returnOrderCode" name="orderCode">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="returnDatetime">
                                <i class="mdi mdi-calendar-clock"></i> {{ __('menu_not_return_do.return_datetime') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control flatpickr-datetime" id="returnDatetime" 
                                name="returnDate" placeholder="{{ __('general.choose') }}" required>
                            <small class="text-muted">Format: {{ __('menu_not_return_do.date') }} & Time</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="returnDesc">
                                <i class="mdi mdi-text-box"></i> {{ __('menu_not_return_do.return_description') }}
                            </label>
                            <textarea class="form-control" id="returnDesc" name="returnDescription" 
                                rows="4" placeholder="{{ __('menu_not_return_do.return_description') }}"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="mdi mdi-close"></i> {{ __('menu_not_return_do.cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-check"></i> {{ __('menu_not_return_do.save_return') }}
                        </button>
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
    
    <!-- Flatpickr for date-time picker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    {{-- <script src="../assets/js/sweet-alert/app.js"></script> --}}

    <script>
        $(document).ready(function() {
            // Initialize Flatpickr for date-time picker in modal
            flatpickr("#returnDate", {
                enableTime: true,
                dateFormat: "d/m/Y, H:i",
                time_24hr: true,
                minuteIncrement: 1,
                placeholder: "DD/MM/YYYY, HH:MM"
            });

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
                columns: [
                    { data: 'action', orderable: false, searchable: false },
                    { data: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'orderDate' },
                    { data: 'fleet.plateNumber' },
                    { data: 'route.name' },
                    { data: 'driver.name' },
                    { data: 'orderType' },
                    { data: "shipmentNumber" },
                    { data: 'customer.name' },
                    { data: 'route.originLocation.name' },
                    { data: 'route.destinationLocation.name' },
                    { data: 'price' },
                    { data: 'harga_vendor' },
                    { data: 'status' }
                ],
                columnDefs: [
                    {
                        searchable: false,
                        targets: [0,1]
                    },
                    {
                        orderable: false,
                        targets: [0,1,3,4,5,6,7,8,9,10,11,12,13]
                    },
                ],
                order: [
                    [2, 'asc']
                ],
            });
        });


        // ---------- Helpers ----------
        function setAllOnPage(checked) {
            // No longer needed with button actions
        }

        function syncHeaderState() {
            // No longer needed with button actions
        }

        // ---------- Submit handler for bulk action ----------
        const saveOrderBtn = document.getElementById('saveOrder');
        if (saveOrderBtn) {
            saveOrderBtn.addEventListener('click', function(event) {
                event.preventDefault();
                swal({
                    title: "{{ __('general.warning') }}",
                    text: "Please use action button on each row to process returns",
                    icon: "info",
                });
            });
        }

        // ---------- Action button click handler ----------
        $(document).on('click', '.action-btn', function(e) {
            e.preventDefault();
            
            const orderCode = $(this).data('code');
            const shipmentNo = $(this).data('shipment');
            const customerName = $(this).data('customer');
            const fleetPlate = $(this).data('fleet');
            const driverName = $(this).data('driver');
            const orderDate = $(this).data('order-date');
            const price = $(this).data('price');
            const vendorPrice = $(this).data('vendor-price');
            const orderType = $(this).data('order-type');
            const origin = $(this).data('origin');
            const destination = $(this).data('destination');
            
            // Populate the modal with single item details
            $('#singleOrderDate').text(orderDate || '-');
            $('#singleShipmentNo').text(shipmentNo || '-');
            $('#singleCustomerName').text(customerName || '-');
            $('#singleFleet').text(fleetPlate || '-');
            $('#singleDriver').text(driverName || '-');
            $('#singleOrderType').text(orderType || '-');
            $('#singlePrice').text(price !== '-' ? 'Rp ' + parseInt(price).toLocaleString('id-ID') : '-');
            
            // Show Vendor Price only if External
            if (orderType.toLowerCase() === 'external') {
                $('#singleVendorPrice').text(vendorPrice !== '-' ? 'Rp ' + parseInt(vendorPrice).toLocaleString('id-ID') : '-');
                $('#vendorPriceContainer').show();
            } else {
                $('#vendorPriceContainer').hide();
            }
            
            $('#singleOrigin').text(origin || '-');
            $('#singleDestination').text(destination || '-');
            
            // Store the order code in a hidden input for form submission
            $('input[name="selectedOrders"]').remove();
            $('<input>').attr({
                type: 'hidden',
                name: 'selectedOrders',
                value: JSON.stringify([orderCode])
            }).appendTo('form');
            
            // Show single item details
            $('#singleItemDetails').show();
            $('#detailModal').modal('show');
            
            // Set the edit button href
            $('#editOrderBtn').attr('href', "{{ route('operational.not-return-do.edit-order', ':code') }}".replace(':code', orderCode));
        });

        // Function to show the modal (no longer fetching via AJAX)
        function showDoModal() {
            $('#detailModal').modal('show');
        }

        // ---------- DataTable init ----------
        $(function() {


            // Filter button
            $('#btnFilter').on('click', function() {
                table.ajax.reload();
            });

            // When table draws (paging/sorting/filter), no need to restore checkbox states
            $('#dt').on('draw.dt', function() {
                // No checkbox restoration needed
            });
        });

        // ---------- Header CheckAll (no longer needed) ----------
        $(document).on('click', '#checkAll', function() {
            // No longer needed
        });



        // ---------- Return Form Submit ----------
        $('#returnForm').on('submit', function(e) {
            e.preventDefault();
            
            const orderCode = $('#returnOrderCode').val();
            const returnDateString = $('#returnDatetime').val();
            const returnDescription = $('#returnDesc').val();
            
            if (!returnDateString) {
                swal({
                    title: "{{ __('general.warning') }}",
                    text: "{{ __('menu_not_return_do.return_datetime') }} {{ __('general.must_filled') }}",
                    icon: "warning",
                });
                return;
            }

            // Convert to MySQL format YYYY-MM-DD HH:mm:ss
            const dateObj = new Date(returnDateString);
            if (isNaN(dateObj.getTime())) {
                // If parsing fails, try manual parsing for Y-m-d H:i format from flatpickr
                const parts = returnDateString.split(' ');
                const dateParts = parts[0].split('-');
                const timeParts = parts.length > 1 ? parts[1].split(':') : ['00', '00'];
                
                const year = parseInt(dateParts[0]);
                const month = parseInt(dateParts[1]);
                const day = parseInt(dateParts[2]);
                const hours = parseInt(timeParts[0]) || 0;
                const minutes = parseInt(timeParts[1]) || 0;
                
                const formattedDate = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')} ${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:00`;
                
                performAjaxUpdate(orderCode, formattedDate, returnDescription);
            } else {
                const year = dateObj.getFullYear();
                const month = String(dateObj.getMonth() + 1).padStart(2, '0');
                const day = String(dateObj.getDate()).padStart(2, '0');
                const hours = String(dateObj.getHours()).padStart(2, '0');
                const minutes = String(dateObj.getMinutes()).padStart(2, '0');
                
                const formattedDate = `${year}-${month}-${day} ${hours}:${minutes}:00`;
                performAjaxUpdate(orderCode, formattedDate, returnDescription);
            }
        });

        // ---------- Modal Form Submit (detailModal submit) ----------
        // Function to convert date format from d/m/Y, H:i to Y-m-d H:i:s
        function convertDateFormat(dateString) {
            // dateString format: "24/01/2026, 12:00"
            const parts = dateString.split(', ');
            const datePart = parts[0]; // "24/01/2026"
            const timePart = parts[1]; // "12:00"
            
            const dateParts = datePart.split('/');
            const day = dateParts[0];
            const month = dateParts[1];
            const year = dateParts[2];
            
            // Return format: "2026-01-24 12:00:00"
            return `${year}-${month}-${day} ${timePart}:00`;
        }

        $('#submitReturnBtn').on('click', function(e) {
            e.preventDefault();
            
            const returnDateString = $('#returnDate').val();
            const returnDescription = $('#description').val();
            
            if (!returnDateString) {
                swal({
                    title: "{{ __('general.warning') }}",
                    text: "Tanggal & Waktu Return harus diisi",
                    icon: "warning",
                });
                return;
            }

            // Convert date format for database
            const formattedDate = convertDateFormat(returnDateString);

            // Submit the form directly via POST (regular form submission)
            const form = $('form[action="{{ route('operational.not-return-do.confirm-do') }}"]');
            form.find('input[name="returnDate"]').val(formattedDate);
            form.find('textarea[name="returnDescription"]').val(returnDescription);
            form.submit();
        });

        function performAjaxUpdate(orderCode, formattedDate, returnDescription) {
            $.ajax({
                url: "{{ route('operational.not-return-do.confirm-return', ':code') }}".replace(':code', orderCode),
                type: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    returnDate: formattedDate,
                    returnDescription: returnDescription
                },
                success: function(response) {
                    if (response.success) {
                        $('#returnModal').modal('hide');
                        swal({
                            title: "{{ __('general.success') }}",
                            text: response.message,
                            icon: "success",
                        }).then(() => {
                            // Reload table
                            table.ajax.reload();
                        });
                    } else {
                        swal({
                            title: "{{ __('general.error') }}",
                            text: response.message,
                            icon: "error",
                        });
                    }
                },
                error: function(xhr) {
                    let message = "{{ __('general.error_occurred') }}";
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    swal({
                        title: "{{ __('general.error') }}",
                        text: message,
                        icon: "error",
                    });
                }
            });
        }
    </script>
@endpush
