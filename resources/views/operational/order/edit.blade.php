@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => $title,
    'secondSegment' => __('general.edit'),
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

    <style>
        #dt {
            border-spacing: 0 15px !important;
            border-collapse: separate !important;
        }
    </style>
@endpush

@section('content')
    <form class="row g-3" method="post" action="{{ route($view . 'update', $data->id) }}">
        @csrf
        @method('PUT')
        <div class="col-sm-12">
            @include('partials.alert')

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>{{ $title }} {{ __('general.edit_data') }}</h4>

                    <a href="{{ route($view . 'index') }}" class="btn btn-info">{{ __('general.back_to_list') }}</a>

                </div>
                <div class="card-body col-md-12">
                    <div class="row g-3">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label" for="name">{{ __('menu_order.order_code') }} <i
                                        class="mdi mdi-information text-danger"></i></label>
                                <input type="hidden" name="code" value="{{ $data->code }}">
                                <input class="form-control" type="text" required readonly disabled
                                    value="{{ $data->code }}">
                            </div>

                            <div class="col-md-6 position-relative">
                                <label class="form-label" for="fleetCode">{{ __('menu_order.plate_number') }}<i
                                        class="mdi mdi-information text-danger"></i></label>

                                <select class="js-example-basic-single" name="fleetCode" id="fleetCode" required="">
                                    <option selected="" disabled="" value="">{{ __('general.choose') }}...
                                    </option>
                                    @foreach ($fleet as $item)
                                        <option value="{{ $item->code }}"
                                            {{ $data->fleetCode == $item->code ? 'selected' : '' }}>
                                            {{ $item->plateNumber }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <label class="form-label" for="name">{{ __('menu_order.order_date') }} <i
                                        class="mdi mdi-information text-danger"></i></label>
                                <input class="form-control" name="orderDate" id="datetime-local" type="date" required
                                    placeholder="{{ __('menu_order.order_date') }}" value="{{ $data->orderDate }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="shipmentNumber">{{ __('menu_order.shipment_no') }} <i
                                        class="mdi mdi-information text-danger"></i></label>
                                <input class="form-control" name="shipmentNumber" id="shipmentNumber" type="text"
                                    required placeholder="{{ __('menu_order.shipment_no') }}"
                                    value="{{ $data->shipmentNumber }}" readonly>
                            </div>

                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6 position-relative">
                                <label class="form-label" for="driverCode">{{ __('menu_order.driver') }} <i
                                        class="mdi mdi-information text-danger"></i></label>
                                <select class="js-example-basic-single" name="driverCode" id="driverCode" required="">
                                    <option selected="" disabled="" value="">{{ __('general.choose') }}...
                                    </option>
                                    @foreach ($driver as $item)
                                        <option value="{{ $item->code }}"
                                            {{ $data->driverCode == $item->code ? 'selected' : '' }}>
                                            {{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="notes">{{ __('menu_order.notes') }}</label>
                                <input class="form-control" name="notes" id="notes" type="text"
                                    placeholder="{{ __('menu_order.notes') }}" value="{{ $data->notes }}">
                            </div>

                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6 position-relative">
                                <label class="form-label" for="customerCode">{{ __('menu_order.customer') }} <i
                                        class="mdi mdi-information text-danger"></i></label>
                                <select class="js-example-basic-single" name="customerCode" id="customerCode"
                                    required="">
                                    <option selected="" disabled="" value="">
                                        {{ __('general.choose') }}...</option>
                                    @foreach ($customer as $item)
                                        <option value="{{ $item->code }}" data-id="{{ $item->id }}"
                                            {{ $data->customerCode == $item->code ? 'selected' : '' }}>
                                            {{ $item->code . ' - ' . $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 position-relative">
                                <label class="form-label" for="orderTypeCode">{{ __('menu_order.load_type') }} <i
                                        class="mdi mdi-information text-danger"></i></label>
                                <select class="js-example-basic-single" name="routeTypeCode" id="routeTypeCode"
                                    required="">
                                    <option selected="" disabled="" value="">
                                        {{ __('general.choose') }}...</option>
                                    @foreach ($routeType as $item)
                                        <option value="{{ $item->code }}"
                                            {{ $data->route->routeTypeCode == $item->code ? 'selected' : '' }}>
                                            {{ $item->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- <div class="row mt-4">
                                <div class="col-md-6 position-relative">
                                    <label class="form-label"
                                        for="originLocationCode">{{ __('menu_order.origin_location') }} <i
                                            class="mdi mdi-information text-danger"></i></label>
                                    <select class="js-example-basic-single" name="originLocationCode"
                                        id="originLocationCode" required="">
                                        <option selected="" disabled="" value="">
                                            {{ __('general.choose') }}...</option>
                                        @foreach ($origin as $item)
                                            <option value="{{ $item->originLocationCode }}"
                                                {{ $route->originLocationCode == $item->originLocationCode ? 'selected' : '' }}>
                                                {{ $item->originLocation->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 position-relative">
                                    <label class="form-label"
                                        for="destinationLocationCode">{{ __('menu_order.destination_location') }}<i
                                            class="mdi mdi-information text-danger"></i> </label>
                                    <select class="js-example-basic-single" name="destinationLocationCode"
                                        id="destinationLocationCode" required="">
                                        <option selected="" disabled="" value="">
                                            {{ __('general.choose') }}...</option>
                                        @foreach ($destination as $item)
                                            <option value="{{ $item->destinationLocationCode }}"
                                                {{ $route->destinationLocationCode == $item->destinationLocationCode ? 'selected' : '' }}>
                                                {{ $item->destinationLocation->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div> --}}

                        <div class="row mt-4">
                            @php
                                $label = '';

                                if ($data->route->routeTypeCode == 'TONASE') {
                                    $label = 'Tonase';
                                } elseif ($data->route->routeTypeCode == 'TRIP') {
                                    $label = 'Trip';
                                } else {
                                    $label = 'Kubik';
                                }
                            @endphp

                            <div class="col-md-6 position-relative">
                                <label class="form-label" for="routeData">{{ __('menu_order.route') }}<i
                                        class="mdi mdi-information text-danger"></i> </label>
                                <select class="js-example-basic-single" name="routeData" id="routeData" required="">
                                    <option selected="" disabled="" value="">
                                        {{ __('general.choose') }}...</option>
                                    @foreach ($route as $item)
                                        <option value="{{ $item->code }}"
                                            {{ $item->code == $data->routeCode ? 'selected' : '' }}>
                                            {{ $item->name . ' (' . ($item->originLocation->name ?? '') . ($item->destinationLocation ? ' - ' . $item->destinationLocation->name : '') . ') - ' . $item->description }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 position-relative " id="qtyField">
                                <label class="form-label" id="qtyLabel" for="qty">{{ $label }}</label>
                                <input class="form-control" name="qty" id="qty" step="any" min="1"
                                    min="1" type="number" value="{{ $data->qty }}" placeholder="" required>
                            </div>
                        </div>

                        @role('SPRADMIN', 'SPRUSER')
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <label class="form-label" for="routeAmount">{{ __('menu_order.route_price') }}</label>
                                    <input class="form-control" name="routeAmount" id="routeAmount"
                                        oninput="formatAngka(this)" type="text"
                                        placeholder="{{ __('menu_order.route_price') }}"
                                        value="{{ number_format($data->routeAmount, 0, ',', '.') }}">
                                </div>
                            </div>
                        @endrole

                        @unlessrole('SPRADMIN', 'SPRUSER')
                            <input type="hidden" name="routeAmount" value="{{ $data->routeAmount }}">
                        @endunlessrole

                        {{-- <div class="row mt-4">
                            <div class="col-md-6 position-relative">
                                <label class="form-label" for="materialCode">Material</label>
                                <select class="js-example-basic-single" name="materialCode" id="materialCode">
                                    <option selected="" disabled="" value="">{{ __('general.choose') }}...
                                    </option>
                                    @foreach ($material as $item)
                                        <option value="{{ $item->code }}"
                                            {{ $data->materialCode == $item->code ? 'selected' : '' }}>
                                            {{ $item->name }}
                                        </option>
                                    @endforeach
                                </select>`
                            </div>

                            <div class="col-md-6 position-relative">
                                <label class="form-label" for="unitCode">Unit </label>
                                <select class="js-example-basic-single" name="unitCode" id="unitCode">
                                    <option selected="" disabled="" value="">{{ __('general.choose') }}...
                                    </option>
                                    @foreach ($unit as $item)
                                        <option value="{{ $item->code }}"
                                            {{ $data->unitCode == $item->code ? 'selected' : '' }}>{{ $item->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <label class="form-label" for="materialQty">Material Qty </label>
                                <input class="form-control" name="materialQty" value="{{ $data->materialQty }}"
                                    id="materialQty" type="number" min="1" placeholder="Material Qty">
                            </div>
                        </div> --}}

                    </div>

                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Material Data</h4>

                    <button class="btn btn-primary" type="button"
                        id="add-material">{{ __('general.add_data') }}</button>


                </div>

                <div class="card-body col-md-12">
                    <table class="table table-sm" id="dt">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th style="width: 20%">Material</th>
                                <th style="width: 20%">Unit</th>
                                <th>Qty</th>
                                <th style="width: 20%">Unit2</th>
                                <th>Qty2</th>
                            </tr>
                        </thead>
                        <tbody id="materialForm">
                            @if (isset($data->orderMaterial))
                                @foreach ($data->orderMaterial as $ordm)
                                    <tr>
                                        <td>
                                            <a href="javascript:deleteOrderMaterial('{{ $ordm->id }}')"
                                                class="btn btn-icon btn-sm bg-danger-subtle" data-bs-toggle="tooltip"
                                                title="Delete">
                                                <i class="mdi mdi-delete fs-14 text-danger"></i>
                                            </a>
                                        </td>
                                        <td>
                                            <select class="form-control js-example-basic-single" name="materialCode[]"
                                                id="materialCode_{{ $loop->iteration }}">
                                                @foreach ($material as $item)
                                                    <option value="{{ $item->code }}"
                                                        {{ $ordm->materialCode == $item->code ? 'selected' : '' }}>
                                                        {{ $item->name }}
                                                    </option>
                                                @endforeach
                                            </select>`
                                        </td>
                                        <td>
                                            <select class="form-control js-example-basic-single" name="unitCode[]"
                                                id="unitCode_{{ $loop->iteration }}">

                                                <option selected="" disabled="" value="">
                                                    {{ __('general.choose') }}...
                                                </option>
                                                @foreach ($unit as $item)
                                                    <option value="{{ $item->code }}"
                                                        {{ $ordm->unitCode == $item->code ? 'selected' : '' }}>
                                                        {{ $item->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input class="form-control" name="materialQty[]"
                                                id="materialQty_{{ $loop->iteration }}" type="number"
                                                value="{{ $ordm->materialQty }}" placeholder="Material Qty">
                                        </td>
                                        <td>
                                            <select class="form-control js-example-basic-single" name="unitCode2[]"
                                                id="unitCode2_{{ $loop->iteration }}">

                                                <option selected="" disabled="" value="">
                                                    {{ __('general.choose') }}...
                                                </option>
                                                @foreach ($unit as $item)
                                                    <option value="{{ $item->code }}"
                                                        {{ $ordm->unitCode2 == $item->code ? 'selected' : '' }}>
                                                        {{ $item->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input class="form-control" name="materialQty2[]"
                                                id="materialQty2_{{ $loop->iteration }}" type="number"
                                                value="{{ $ordm->materialQty2 }}" placeholder="Qty">
                                        </td>

                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td class="remove-btn"></td>
                                    <td>
                                        <select class="js-example-basic-single" name="materialCode[]"
                                            id="materialCode_1"d>
                                            <option selected="" disabled="" value="">
                                                {{ __('general.choose') }}...
                                            </option>
                                            @foreach ($material as $item)
                                                <option value="{{ $item->code }}">
                                                    {{ $item->name }}
                                                </option>
                                            @endforeach
                                        </select>`
                                    </td>
                                    <td>
                                        <select class="js-example-basic-single" name="unitCode[]" id="unitCode_1">
                                            <option selected="" disabled="" value="">
                                                {{ __('general.choose') }}...
                                            </option>
                                            @foreach ($unit as $item)
                                                <option value="{{ $item->code }}">{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input class="form-control" name="materialQty[]" id="materialQty_1"
                                            type="number" placeholder="Material Qty">
                                    </td>
                                    <td>
                                        <select class="js-example-basic-single" name="unitCode2[]" id="unitCode2_1">
                                            <option selected="" disabled="" value="">
                                                {{ __('general.choose') }}...
                                            </option>
                                            @foreach ($unit as $item)
                                                <option value="{{ $item->code }}">{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input class="form-control" name="materialQty2[]" id="materialQty_1"
                                            type="number" placeholder="Qty">
                                    </td>

                                </tr>
                            @endif



                        </tbody>
                    </table>
                </div>

            </div>

            @if ($customerDetailOrder->count() > 0)
                <div class="card" id="card-customer-detail">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4> {{ __('menu_order.customer_detail_data') }}</h4>

                    </div>
                    <div class="card-body col-md-6">
                        @foreach ($customerDetailOrder as $item)
                            <input type="hidden" name="customerDetailCode[]" value="{{ $item->customerDetailCode }}">

                            <div class="mb-3">
                                <label for="{{ $item->customerDetail?->name }}"
                                    class="form-label">{{ $item->customerDetail?->name }}</label>
                                <input type="text" class="form-control"
                                    placeholder="{{ $item->customerDetail?->name }}" name="value[]"
                                    value="{{ $item->value }}">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-body col-md-12">
                    <ul class="nav nav-tabs" id="icon-tab" role="tablist">
                        <li class="nav-item"><a class="nav-link active txt-success" id="icon-home-tab"
                                data-bs-toggle="tab" href="#icon-home" role="tab" aria-controls="icon-home"
                                aria-selected="true">{{ __('menu_order.additional_cost') }}</a>
                        </li>
                        <li class="nav-item"><a class="nav-link txt-success" id="profile-icon-tabs" data-bs-toggle="tab"
                                href="#profile-icon" role="tab" aria-controls="profile-icon"
                                aria-selected="false">{{ __('menu_order.add_cost') }}</a>
                        </li>
                    </ul>
                    <div class="tab-content" id="icon-tabContent">
                        @include('operational.order.components.cost-edit')
                        @include('operational.order.components.cost-component-add')
                    </div>
                </div>
            </div>

            @if ($data->status == 0 || in_array(auth()->user()->roleCode, ['SPRADMIN', 'SPRUSER']))
                <div class="card">
                    <div class="col-12">
                        <div class="card-body ">
                            <button class="btn btn-primary" id="save"
                                type="submit">{{ __('general.save_changes') }}</button>
                        </div>
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
    <script src=" {{ asset('assets/js/helper.js') }}"></script>

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


    <script>
        $(document).ready(function() {

            const selectedType = $('#routeTypeCode').select2('val');

            // loadQty(selectedType)

            // Handle form submit dengan AJAX
            $('form[method="post"]').not('#delete-form').on('submit', function(e) {
                e.preventDefault();

                const form = $(this);
                const formData = new FormData(this);
                const submitButton = $('#save');

                // Disable button saat proses
                submitButton.prop('disabled', true).html(
                    '<i class="mdi mdi-loading mdi-spin"></i> {{ __('general.processing') }}...');

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            swal({
                                title: "Sukses",
                                text: response.message,
                                icon: "success",
                                buttons: false,
                                timer: 1500
                            }).then(() => {
                                window.location.href = response.redirect;
                            });
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = '{{ __('general.an_error_occurred') }}';

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }

                        swal({
                            title: "Error",
                            text: errorMessage,
                            icon: "error",
                            button: "OK"
                        });

                        // Enable button kembali
                        submitButton.prop('disabled', false).html(
                            '{{ __('general.save_changes') }}');
                    }
                });
            });
        });

        // function submitFormAndi(id) {
        //     e.preventDefault();
        //     return false;
        // }

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

        function checkAndLoadOriginLocation() {
            const customerCode = $('#customerCode').select2('val'); // Use select2 to get the value
            const routeTypeCode = $('#routeTypeCode').select2('val'); // Use select2 to get the value

            if (customerCode && routeTypeCode) {
                let html = '<option selected="" disabled="" value="">{{ __('general.choose') }}...</option>';
                $('#originLocationCode').html(html);

                $.get("{{ url('ajax/origin-by-customer') }}/" + customerCode + "/" + routeTypeCode, function(data) {
                    data.forEach(i => {
                        html += '<option value="' + i.code + '">' + i.name + '</option>';
                    });
                    $('#originLocationCode').html(html);
                    // Reinitialize Select2 for origin location dropdown after updating options
                });
            }
        }

        function loadQty(selectedType) {
            // Show the correct field based on the selection after 1 second (simulating processing time)
            if (selectedType === 'TONASE') {
                $('#qtyLabel').html(
                    'Tonase <i class="icofont icofont-warning-alt text-danger"></i>'
                ); // Update the label with icon
                $('#qty').attr('placeholder', 'Enter Tonase'); // Update placeholder
                $('#qty').val(1); // Set default value to 1
                $('#qty').removeAttr('readonly'); // Remove readonly if it was set
                $('#qtyField').removeClass('d-none'); // Show the field
            } else if (selectedType === 'TRIP') {
                $('#qtyLabel').html(
                    'Ritase <i class="icofont icofont-warning-alt text-danger"></i>'
                ); // Update the label with icon
                $('#qty').attr('placeholder', 'Enter Ritase'); // Update placeholder
                $('#qty').val(1); // Set default value to 1
                $('#qty').attr('readonly', true); // Make the field readonly
                $('#qtyField').removeClass('d-none'); // Show the field
            } else if (selectedType == 'KUBIKASE') {
                $('#qtyLabel').html(
                    'Kubikase <i class="icofont icofont-warning-alt text-danger"></i>'
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

            const selectedType = $('#routeTypeCode').select2('val'); // Get the selected value from select2

            console.log(selectedType);

            setTimeout(function() {

                loadQty(selectedType)
                // Remove the loader once the logic is complete
                $('.loader-wrapper').remove();
            }, 1000); // Simulate 1-second delay for the loader
        });


        function checkAndLoadDestinationLocation() {
            const customerCode = $('#customerCode').select2('val'); // Use select2 to get the value
            const routeTypeCode = $('#routeTypeCode').select2('val'); // Use select2 to get the value
            const originLocationCode = $('#originLocationCode').select2('val'); // Use select2 to get the value

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
                        // Reinitialize Select2 for destination location dropdown after updating options
                    });
            }
        }


        // function checkAndLoadRoute() {
        //     const customerCode = $('#customerCode').select2('val');
        //     const originLocationCode = $('#originLocationCode').select2('val');
        //     const destinationLocationCode = $('#destinationLocationCode').select2('val');

        //     if (customerCode && originLocationCode && destinationLocationCode) {
        //         $.get("{{ url('ajax/route-by-customer') }}/" + customerCode + "/" + originLocationCode + "/" +
        //             destinationLocationCode,
        //             function(data) {

        //             });
        //     }
        // }

        function checkAndLoadRoute() {
            const routeData = $('#routeData').select2('val');

            if (routeData) {
                $.get("{{ url('ajax/route-order-detail') }}/" + routeData,
                    function(data) {
                        const componentList = document.getElementById('component-list');
                        componentList.innerHTML = '';
                        index = 0;

                        data.forEach((item, i) => {
                            let row = `
                        <tr>
                            <td>
                                <a href="javascript:removeRow(${i})"
                                    class="btn btn-icon btn-sm bg-danger-subtle me-1"
                                    data-bs-toggle="tooltip" title="Delete">
                                    <i class="mdi mdi-delete fs-14 text-danger"></i>
                                </a>
                            </td>
                            <td><input type="hidden"  name="componentName[]" readonly value="${item.cost_component.code}"> ${item.cost_component.name}</td>
                            <td><input class="form-control" name="description[]" value=""></td>
                             <td>
             <input class="form-control"  name="nominal[]" oninput="formatAngka(this)" type="text" min=1 readonly  value="${formatNumber(item.amount)}">
        </td>
                        </tr>`;
                            componentList.insertAdjacentHTML('beforeend', row);
                            index++;
                        });
                    });
            }
        }

        function checkAndLoadRouteOrder() {
            const customerCode = $('#customerCode').select2('val'); // Use select2 to get the value
            const routeTypeCode = $('#routeTypeCode').select2('val'); // Use select2 to get the value
            let customerId = $('#customerCode option:selected').data('id');


            if (customerCode && routeTypeCode) {
                let html = '<option selected="" disabled="" value="">{{ __('general.choose') }}...</option>';
                $('#originLocationCode').html(html);

                $.get("{{ url('ajax/route-order') }}/" + customerId + "/" + routeTypeCode, function(data) {
                    console.log(data);
                    data.forEach(i => {
                        html +=
                            `<option value="${i.code}">${i.name} (${i.origin_location.name} - ${i.destination_location.name})</option>`;

                    });
                    $('#routeData').html(html);
                    // Reinitialize Select2 for origin location dropdown after updating options
                });
            }
        }

        // Trigger origin location when both customer and route type are selected
        $('#customerCode, #routeTypeCode').on('change', function() {
            checkAndLoadRouteOrder();
        });

        // Trigger destination location when origin location is also selected
        $('#originLocationCode').on('select2:select', function() {
            checkAndLoadDestinationLocation();
        });

        $('#routeData').on('select2:select', function() {
            checkAndLoadRoute();
        });

        $('#customerCode').on('change', function() {
            let customerCode = $(this).val();
            let customerId = $('#customerCode option:selected').data('id');

            $.get("/ajax/order-shipment-format/" + customerId, function(data) {
                $('#shipmentNumber').val(data);
            });



            if (customerCode) {
                $.get("/ajax/customer-detail/" + customerId, function(data) {
                    const $detailCard = $('#card-customer-detail');
                    const $cardBody = $detailCard.find('.card-body');
                    $cardBody.empty(); // Bersihkan isinya

                    if (data.length > 0) {
                        $detailCard.removeClass('d-none');

                        // Disable input #notes
                        $('#notes').prop('disabled', true);

                        data.forEach(item => {
                            let html = `
                        <input type="hidden" name="customerDetailCode[]" value="${item.code}">
                        <div class="mb-3">
                            <label class="form-label">${item.name} <i class="mdi mdi-information text-danger"></i></label>
                            <input class="form-control" name="value[]" type="text" required placeholder="${item.name}">
                        </div>`;
                            $cardBody.append(html);
                        });
                    } else {
                        $detailCard.addClass('d-none');
                        $cardBody.empty();

                        // Enable input #notes
                        $('#notes').prop('disabled', false);
                    }
                });
            } else {
                $('#card-customer-detail').addClass('d-none');
                $('#card-customer-detail .card-body').empty();

                // Enable input #notes
                $('#notes').prop('disabled', false);
            }
        });

        $('#add-material').on('click', function() {
            let row = $('#materialForm tr').length + 1;
            console.log(row);

            let newRow = `
        <tr>
            <td class="remove-btn">
                <a href="javascript:removeDetailRow(${row})"
                    class="btn btn-icon btn-sm bg-danger-subtle"
                    data-bs-toggle="tooltip" title="Delete">
                    <i class="mdi mdi-delete fs-14 text-danger"></i>
                </a>
            </td>
            <td>
                <select class="form-control js-example-basic-single" name="materialCode[]" id="materialCode_${row}" >
                    <option selected disabled value="">
                        {{ __('general.choose') }}...
                    </option>
                    @foreach ($material as $item)
                        <option value="{{ $item->code }}">{{ $item->name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <select class="form-control js-example-basic-single" name="unitCode[]" id="unitCode_${row}" >
                    <option selected disabled value="">
                        {{ __('general.choose') }}...
                    </option>
                    @foreach ($unit as $item)
                        <option value="{{ $item->code }}">{{ $item->name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input class="form-control" name="materialQty[]" id="materialQty_${row}" type="number"
                    min="1" placeholder="Material Qty">
            </td>
            <td>
                <select class="form-control js-example-basic-single" name="unitCode2[]" id="unitCode2_${row}" >
                    <option selected disabled value="">
                        {{ __('general.choose') }}...
                    </option>
                    @foreach ($unit as $item)
                        <option value="{{ $item->code }}">{{ $item->name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input class="form-control" name="materialQty2[]" id="materialQty2_${row}" type="number"
                    min="1" placeholder="Qty">
            </td>
        </tr>
    `;

            $('#materialForm').append(newRow);

            // Reinitialize select2 (jika pakai select2)
            $(`#materialCode_${row}`).select2();
            $(`#unitCode_${row}`).select2();
            $(`#materialCode2_${row}`).select2();
            $(`#unitCode2_${row}`).select2();
        });

        function removeDetailRow(row) {
            $(`#materialCode_${row}`).closest('tr').remove();
        }

        function deleteOrderMaterial(id) {
            var url = '{{ route('operational.order-material.destroy', ':id') }}';
            url = url.replace(':id', id);

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

        $('#dt').DataTable()
    </script>
@endpush
