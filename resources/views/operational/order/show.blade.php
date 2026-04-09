@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => $title,
    'secondSegment' => 'Detail',
])

@php
    use App\Models\Data\Route;
@endphp

@push('style')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/select2.css') }}">

    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/custom-select2.css') }}">
    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/sweetalert2.css') }} ">
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
@endpush

@section('content')
    <form class="row g-3" method="post" action="{{ route($view . 'update', $data->id) }}">
        @csrf
        @method('PUT')
        <form class="row g-3" method="post" action="{{ route($view . 'update', $data->id) }}">
            @csrf
            @method('PUT')
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>{{ $title }} Detail Data</h4>

                        <a href="{{ route($view . 'index') }}" class="btn btn-info">{{ __('general.back_to_list') }}</a>

                    </div>
                    <div class="card-body col-md-12">
                        <div class="row g-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label" for="name">{{ __('menu_order.order_code') }}</label>
                                    <input class="form-control" type="text" required readonly
                                        value="{{ $data->code }}">
                                </div>

                                <div class="col-md-6 position-relative">
                                    <label class="form-label" for="fleetCode">{{ __('menu_order.plate_number') }}</label>


                                    <input class="form-control" name="fleetCode" type="text" readonly required
                                        placeholder="{{ __('menu_order.plate_number') }}"
                                        value="{{ $data->fleet->plateNumber }}">
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <label class="form-label" for="name">{{ __('menu_order.order_date') }}</label>
                                    <input class="form-control" name="orderDate" type="text" readonly required
                                        placeholder="{{ __('menu_order.order_date') }}" value="{{ $data->orderDate }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label"
                                        for="shipmentNumber">{{ __('menu_order.shipment_no') }}</label>
                                    <input class="form-control" name="shipmentNumber" id="shipmentNumber" type="text"
                                        readonly required placeholder="{{ __('menu_order.shipment_no') }}"
                                        value="{{ mb_strtoupper($data->shipmentNumber ?? '') }}">
                                </div>

                            </div>

                            <div class="row mt-4">
                                <div class="col-md-6 position-relative">
                                    <label class="form-label" for="driverCode">{{ __('menu_order.driver') }}</label>
                                    <input class="form-control" name="driverCode" id="driverCode" type="text" readonly
                                        required placeholder="{{ __('menu_order.driver') }}"
                                        value="{{ mb_strtoupper($data->driver->name ?? '') }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="notes">{{ __('menu_order.notes') }}</label>
                                    <input class="form-control" name="notes" id="notes" type="text" readonly
                                        placeholder="{{ __('menu_order.notes') }}" value="{{ $data->notes }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="notes">{{ __('menu_order.notes') }}</label>
                                    <input class="form-control" name="notes" id="notes" type="text" readonly
                                        placeholder="{{ __('menu_order.notes') }}" value="{{ $data->notes }}">
                                </div>

                            </div>

                            <div class="row mt-4">
                                <div class="col-md-6 position-relative">
                                    <label class="form-label" for="customerCode">{{ __('menu_order.customer') }} </label>
                                    <input class="form-control" name="customerCode" id="customerCode" type="text"
                                        readonly placeholder="{{ __('menu_order.customer') }}"
                                        value="{{ $data->customer->name }}">
                                </div>

                                <div class="col-md-6 position-relative">
                                    <label class="form-label"
                                        for="orderTypeCode">{{ __('menu_order.load_type') }}</label>
                                    <input class="form-control" name="routeTypeCode" id="routeTypeCode" type="text"
                                        readonly placeholder="{{ __('menu_order.load_type') }}"
                                        value="{{ $route->routeType->name ?? '' }}">
                                </div>
                            </div>

                            {{-- <div class="row mt-4">
                                <div class="col-md-6 position-relative">
                                    <label class="form-label"
                                        for="originLocationCode">{{ __('menu_order.origin_location') }}</label>
                        <input class="form-control" name="originLocationCode" id="originLocationCode"
                            type="text" readonly placeholder="{{ __('menu_order.origin_location') }}"
                            value="{{ $route->originLocation->name }}">
                    </div>

                    <div class="col-md-6 position-relative">
                        <label class="form-label"
                            for="destinationLocationCode">{{ __('menu_order.destination_location') }}
                        </label>
                        <input class="form-control" name="destinationLocationCode"
                            id="destinationLocationCode" type="text" readonly
                            placeholder="{{ __('menu_order.destination_location') }}"
                            value="{{ $route->destinationLocation->name }}">
                    </div>
                </div> --}}

                            <div class="row mt-4">

                                <div class="col-md-6 position-relative">
                                    <label class="form-label" for="routeData">{{ __('menu_order.route') }}</label>
                                    <input class="form-control" name="routeData" id="routeData" type="text" readonly
                                        placeholder="{{ __('menu_order.route') }}"
                                        value="{{ $data->route?->name
                                            ? $data->route->name .
                                                ' (' .
                                                ($data->route?->originLocation?->name ?? '') .
                                                ($data->route?->destinationLocation?->name ? ' - ' . $data->route->destinationLocation->name : '') .
                                                ') - ' .
                                                ($data->route?->description ?? '')
                                            : '' }}">

                                </div>
                                @php
                                    $label = '';

                                    if ($data->route) {
                                        if ($data->route->routeTypeCode === 'TONASE') {
                                            $label = 'Tonase';
                                        } elseif ($data->route->routeTypeCode === 'TRIP') {
                                            $label = 'Trip';
                                        } else {
                                            $label = 'Kubik';
                                        }
                                    } else {
                                        $label = '-'; // atau kosong '' sesuai kebutuhan tampilan
                                    }
                                @endphp

                                <div class="col-md-6 position-relative " id="qtyField">
                                    <label class="form-label" id="qtyLabel" for="qty">{{ $label }}</label>
                                    <input class="form-control" name="qty" id="qty" step="any"
                                        min="1" min="1" type="number" value="{{ $data->qty }}"
                                        placeholder="" required>
                                </div>
                            </div>

                            {{-- <div class="row mt-4">
                                <div class="col-md-6 position-relative">
                                    <label class="form-label" for="materialCode">Material </label>
                                    <input class="form-control" name="materialCode" id="materialCode" type="text"
                                        readonly placeholder="Material" value="{{ $data->material->name ?? '' }}">
            </div>

            <div class="col-md-6 position-relative">
                <label class="form-label" for="orderTypeCode">Unit</label>
                <input class="form-control" name="routeTypeCode" id="routeTypeCode" type="text"
                    readonly placeholder="Unit" value="{{ $data->unit->name ?? '' }}">
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6 position-relative">
                <label class="form-label" for="customerCode">Material Qty </label>
                <input class="form-control" name="customerCode" id="customerCode" type="text"
                    readonly placeholder="Material Qty" value="{{ $data->materialQty }}">
            </div>


        </div> --}}
                        </div>
                    </div>
                </div>

                <!-- Card Informasi Harga -->
                <div class="card shadow-sm border-0" id="priceInfoCard">
                    <div class="card-header bg-gradient-primary text-white">
                        <h5 class="mb-0">
                            <i class="mdi mdi-cash-multiple"></i> Informasi Harga
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <!-- Fleet Type Info -->
                            <div class="col-md-4">
                                <div class="p-3 rounded"
                                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <p class="text-white-50 mb-1 small">Tipe Fleet</p>
                                            <h4 class="text-white mb-0" id="fleetTypeDisplay">
                                                {{ $data->fleet->company->type ?? '-' }}</h4>
                                        </div>
                                        <div class="bg-white bg-opacity-25 p-3 rounded">
                                            <i class="mdi mdi-truck fs-2 text-white"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Price Info -->
                            <div class="col-md-4">
                                <div class="p-3 rounded"
                                    style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="w-100">
                                            <p class="text-white-50 mb-1 small">Route Amount</p>
                                            <h4 class="text-white mb-0" id="priceDisplay">Rp
                                                {{ number_format($data->routeAmount ?? 0, 0, ',', '.') }}</h4>
                                            <p class="text-white-50 mb-0 small" id="priceDetailDisplay">
                                                {{ $data->qty }} × Rp
                                                {{ number_format($data->price ?? 0, 0, ',', '.') }} = Rp
                                                {{ number_format($data->routeAmount ?? 0, 0, ',', '.') }}</p>
                                        </div>
                                        <div class="bg-white bg-opacity-25 p-3 rounded">
                                            <i class="mdi mdi-currency-usd fs-2 text-white"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Personal Vendor Price Info -->
                            <div class="col-md-4" id="vendorPriceCard">
                                <div class="p-3 rounded"
                                    style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="w-100">
                                            <p class="text-white-50 mb-1 small" id="vendorPriceLabel">Personal Vendor
                                                Price</p>
                                            <h4 class="text-white mb-0" id="vendorPriceDisplay">Rp
                                                {{ number_format($data->personalVendorPrice ?? 0, 0, ',', '.') }}</h4>
                                            <p class="text-white-50 mb-0 small" id="vendorPriceDetailDisplay">
                                                {{ $data->qty }} × Rp
                                                {{ number_format($data->personalVendorPriceSingle ?? 0, 0, ',', '.') }} =
                                                Rp {{ number_format($data->personalVendorPrice ?? 0, 0, ',', '.') }}</p>
                                        </div>
                                        <div class="bg-white bg-opacity-25 p-3 rounded">
                                            <i class="mdi mdi-account-cash fs-2 text-white"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Info -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="alert alert-info mb-0" role="alert">
                                    <i class="mdi mdi-information"></i>
                                    <strong>Catatan:</strong>
                                    <span id="priceNote">
                                        Harga dihitung berdasarkan route yang dipilih × qty. Fleet type:
                                        <strong>{{ $data->fleet?->company?->type ?? '-' }}</strong>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>Material Data</h4>


                    </div>

                    <div class="card-body col-md-12">
                        <table class="table table-sm" id="dt-material">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Material</th>
                                    <th>Unit</th>
                                    <th>Qty</th>
                                    <th>Unit 2</th>
                                    <th>Qty 2</th>
                                    <th>Tonase</th>
                                </tr>
                            </thead>
                            <tbody id="materialForm">
                                @if (isset($data->orderMaterial))
                                    @foreach ($data->orderMaterial as $ordm)
                                        <tr>

                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $ordm->material->name ?? '' }}</td>
                                            <td>{{ $ordm->unit->name ?? '' }}</td>
                                            <td>{{ $ordm->materialQty }}</td>
                                            <td>{{ $ordm->unit2->name ?? '' }}</td>
                                            <td>{{ $ordm->materialQty2 }}</td>
                                            <td>{{ number_format($ordm->materialQty * $ordm->materialQty2, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif



                            </tbody>
                        </table>
                    </div>

                </div>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>{{ $title }} {{ __('menu_order.cost') }} Data</h4>
                    </div>
                    <div class="card-body col-md-12">
                        {{-- <ul class="nav nav-tabs" id="icon-tab" role="tablist">
                            <li class="nav-item"><a class="nav-link active txt-success" id="icon-home-tab"
                                    data-bs-toggle="tab" href="#icon-home" role="tab" aria-controls="icon-home"
                                    aria-selected="true">Additional
                                    Cost</a>
                            </li>
                        </ul>
                        <div class="tab-content" id="icon-tabContent">
                            @include('operational.order.components.cost-edit')
                        </div> --}}
                        <table class="table table-striped w-100 nowrap" id="dt">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>{{ __('menu_order.component_name') }}</th>
                                    {{-- <th>Component Type</th> --}}
                                    <th>{{ __('menu_order.description') }}</th>
                                    <th>Nominal</th>
                                    <th>Tipe</th>
                                </tr>
                            </thead>
                            <tbody>

                                @php
                                    $i = 1;
                                    $totalPrice = 0;
                                @endphp
                                {{-- @if ($route->routeTypeCode == 'TONASE')
                                    <tr>
                                        <td></td>
                                        <td>{{ $i++ }}</td>
                        <td>Bonus Tonase</td>
                        <td>Bonus</td>
                        <td>-</td>
                        <td>
                            @php
                            $bonus = 0;

                            $bonusTonase = App\Models\Data\TonaseBonus::where(
                            'min',
                            '<=',
                                $data->qty,
                                )
                                ->where('max', '>=', $data->qty)
                                ->first();

                                if ($bonusTonase) {
                                $bonus += $bonusTonase->value;
                                }

                                $totalPrice += $bonus;
                                @endphp
                                {{ 'Rp ' . number_format($bonus, 0, ',', '.') }}

                        </td>
                        </tr>
                        @endif --}}
                                @foreach ($cost as $item)
                                    @php
                                        $totalPrice += $item->nominal;
                                    @endphp
                                    <tr>
                                        <td>{{ $i++ }}</td>
                                        <td>{{ $item->costComponent ? $item->costComponent->name : 'Custom Component' }}
                                        </td>
                                        {{-- <td>{{ $item->type }}</td> --}}
                                        <td>{{ $item->description }}</td>
                                        <td>{{ 'Rp ' . number_format($item->nominal, 0, ',', '.') }}</td>
                                        <td>{{ $item->type == 'On Charge' ? 'Ditagihkan' : 'Tidak Ditagihkan' }}</td>

                                    </tr>
                                @endforeach
                                <!-- New rows will be added here -->
                            </tbody>
                        </table>
                        <h4>Total: Rp {{ number_format($totalPrice, 0, ',', '.') }} </h4>
                    </div>
                </div>


                @if ($customerDetailOrder->count() > 0)
                    <div class="card" id="card-customer-detail">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4> {{ __('menu_order.customer_detail_data') }}</h4>

                        </div>
                        <div class="card-body col-md-6">
                            @foreach ($customerDetailOrder as $item)
                                <div class="mb-3">
                                    <label for="{{ $item->customerDetail?->name }}"
                                        class="form-label">{{ $item->customerDetail?->name }}</label>
                                    <input type="text" class="form-control" readonly
                                        placeholder="{{ $item->customerDetail?->name }}" value="{{ $item->value }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>
        </form>
        <form id="delete-form" method="post">
            @csrf
            @method('DELETE')
        </form>
    @endsection

    @push('script')
        <script src="{{ asset('assets/js/flat-pickr/flatpickr.js') }}"></script>
        <script src="{{ asset('assets/js/flat-pickr/custom-flatpickr.js') }}"></script>
        <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
        <script src=" {{ asset('assets/js/select2/select2-custom.js') }}"></script>
        <script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>
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
        <script src=" {{ asset('assets/js/helper.js') }}"></script>


        <script>
            $(document).ready(function() {

                // const selectedType = $('#routeTypeCode').val();

                // loadQty(selectedType)
            });

            function deleteCost(id) {
                var url = '{{ route('operational.order-cost.destroy', ':id') }}'; // Use placeholder ':id'
                url = url.replace(':id', id); // Replace the placeholder with actual id

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

            $('#save').click(function(e) {
                const routeTypeCode = $('#routeTypeCode').val();

                // if (routeTypeCode === 'TONASE') {
                //     const qty = $('#qty').val();

                //     if (qty > 100) {
                //         e.preventDefault();
                //         swal({
                //             title: "{{ __('general.warning') }}",
                //             text: "Tonase cannot be higher than 100",
                //             icon: "warning",
                //         })
                //         return;
                //     }
                // }

            });


            function checkAndLoadOriginLocation() {
                const customerCode = $('#customerCode').val(); // Get the value
                const routeTypeCode = $('#routeTypeCode').val(); // Get the value

                if (customerCode && routeTypeCode) {
                    let html = '<option selected="" disabled="" value="">{{ __('general.choose') }}...</option>';
                    $('#originLocationCode').html(html);

                    $.get("{{ url('ajax/origin-by-customer') }}/" + customerCode + "/" + routeTypeCode, function(data) {
                        data.forEach(i => {
                            html += '<option value="' + i.code + '">' + i.name + '</option>';
                        });
                        $('#originLocationCode').html(html);
                        // Origin location is readonly input, no need to initialize select2
                        // $('#originLocationCode').select2();
                    });
                }
            }

            function loadQty(data) {
                // Show the correct field based on the selection after 1 second (simulating processing time)
                if (selectedType === 'TONASE') {
                    $('#qtyLabel').html(
                        'Tonase '
                    ); // Update the label with icon
                    $('#qty').attr('placeholder', 'Enter Tonase'); // Update placeholder
                    $('#qty').val(1); // Set default value to 1
                    $('#qty').removeAttr('readonly'); // Remove readonly if it was set
                    $('#qtyField').removeClass('d-none'); // Show the field
                } else if (selectedType === 'TRIP') {
                    $('#qtyLabel').html(
                        'Ritase '
                    ); // Update the label with icon
                    $('#qty').attr('placeholder', 'Enter Ritase'); // Update placeholder
                    $('#qty').val(1); // Set default value to 1
                    $('#qty').attr('readonly', true); // Make the field readonly
                    $('#qtyField').removeClass('d-none'); // Show the field
                } else if (selectedType == 'KUBIKASE') {
                    $('#qtyLabel').html(
                        'Kubikase '
                    ); // Update the label with icon
                    $('#qty').attr('placeholder', 'Enter Kubikase'); // Update placeholder
                    $('#qty').val(1); // Set default value to 1
                    $('#qty').removeAttr('readonly'); // Remove readonly if it was set
                    $('#qtyField').removeClass('d-none'); // Show the field
                } else {
                    $('#qtyField').addClass('d-none'); // Hide the field if neither is selected
                }
            }

            // When routeTypeCode is changed
            $('#routeTypeCode').on('change', function() {
                $('body').append(`
            <div class="loader-wrapper">
                <div class="loader">
                    <div class="loader4"></div>
                </div>
            </div>
        `);

                const selectedType = $('#routeTypeCode').val(); // Get the value

                setTimeout(function() {

                    loadQty(selectedType)
                    // Remove the loader once the logic is complete
                    $('.loader-wrapper').remove();
                }, 1000); // Simulate 1-second delay for the loader
            });


            function checkAndLoadDestinationLocation() {
                const customerCode = $('#customerCode').val(); // Get the value
                const routeTypeCode = $('#routeTypeCode').val(); // Get the value
                const originLocationCode = $('#originLocationCode').val(); // Get the value

                if (customerCode && routeTypeCode && originLocationCode) {
                    let html = '<option selected="" disabled="" value="">{{ __('general.choose') }}...</option>';
                    $('#destinationLocationCode').html(html);

                    $.get("{{ url('ajax/destination-by-customer') }}/" + customerCode + "/" + routeTypeCode + "/" +
                        originLocationCode,
                        function(data) {
                            data.forEach(i => {
                                html += '<option value="' + i.code + '">' + i.name + '</option>';
                            });
                            $('#destinationLocationCode').html(html);
                            // Destination location is readonly input, no need to initialize select2
                            // $('#destinationLocationCode').select2();
                        });
                }
            }


            function checkAndLoadRoute() {
                const customerCode = $('#customerCode').val();
                const originLocationCode = $('#originLocationCode').val();
                const destinationLocationCode = $('#destinationLocationCode').val();

                if (customerCode && originLocationCode && destinationLocationCode) {
                    $.get("{{ url('ajax/route-by-customer') }}/" + customerCode + "/" + originLocationCode + "/" +
                        destinationLocationCode,
                        function(data) {

                        });
                }
            }

            // Event listeners removed since elements are readonly inputs
            // $('#customerCode, #routeTypeCode').on('change', function() {
            //     checkAndLoadOriginLocation();
            // });

            // // Trigger destination location when origin location is also selected
            // $('#originLocationCode').on('select2:select', function() {
            //     checkAndLoadDestinationLocation();
            // });

            // $('#destinationLocationCode').on('select2:select', function() {
            //     checkAndLoadRoute();
            // });

            $('#dt').DataTable()

            $('#dt-material').DataTable()

            // Function to update price information
            function updatePriceInfo() {
                const fleetCode = '{{ $data->fleetCode }}';
                const routeCode = '{{ $data->routeCode }}';
                const qty = '{{ $data->qty }}' || 1;

                // Check if we have the required data
                if (!fleetCode || !routeCode) {
                    return;
                }

                // Make AJAX request
                $.ajax({
                    url: '{{ route('ajax.order-calculate-price') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        fleetCode: fleetCode,
                        routeCode: routeCode,
                        qty: qty
                    },
                    success: function(response) {
                        if (response.success) {
                            // Update fleet type
                            $('#fleetTypeDisplay').text(response.fleetType || '-');

                            // Update price (routeAmount = qty × price satuan)
                            $('#priceDisplay').text('Rp ' + formatNumber(response.routeAmount));
                            $('#priceDetailDisplay').text(qty + ' × Rp ' + formatNumber(response.price) + ' = Rp ' +
                                formatNumber(response.routeAmount));

                            const isExternal = response.isExternal === true;
                            const vendorSingle = isExternal ? response.vendorPriceSingle : response
                                .personalVendorPriceSingle;
                            const vendorTotal = isExternal ? response.vendorPrice : response.personalVendorPrice;
                            const vendorLabel = isExternal ? 'Vendor Price' : 'Personal Vendor Price';

                            $('#vendorPriceLabel').text(vendorLabel);
                            $('#vendorPriceDisplay').text('Rp ' + formatNumber(vendorTotal));
                            $('#vendorPriceDetailDisplay').text(qty + ' × Rp ' + formatNumber(vendorSingle) +
                                ' = Rp ' + formatNumber(vendorTotal));

                            // Always show vendor price card
                            $('#vendorPriceCard').show();
                            $('#priceNote').html(
                                'Harga dihitung berdasarkan route yang dipilih × qty. Fleet type: <strong>' +
                                response.fleetType + '</strong>');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error calculating price:', xhr);
                    }
                });
            }

            // Initialize price info on page load
            $(document).ready(function() {
                updatePriceInfo();
            });

            function formatNumber(number) {
                return new Intl.NumberFormat('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(number);
            }
        </script>
    @endpush
