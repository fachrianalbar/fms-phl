@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => $title,
    'secondSegment' => __('general.add'),
])

@push('style')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/select2.css') }}">

    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/custom-select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/sweetalert2.css') }}">
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
    <form method="post" action="{{ route($view . 'store') }}">
        @csrf
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>{{ $title }} {{ __('general.add_data') }}</h4>

                    <a href="{{ route($view . 'index') }}" class="btn btn-info">{{ __('general.back_to_list') }}</a>

                </div>
                <div class="card-body col-md-12">
                    <div class="row g-3">

                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label" for="code">Code <i
                                        class="icofont icofont-warning-alt text-danger"></i></label>
                                <input class="form-control" type="text" placeholder="Code" id="code_display" readonly
                                    disabled>

                                <input type="hidden" name="code" id="code_hidden">
                            </div>

                            <div class="col-md-6 position-relative">
                                <label class="form-label" for="fleetCode">{{ __('menu_order.plate_number') }}
                                    <i class="mdi mdi-information text-danger"></i></label>

                                <select class="js-example-basic-single" name="fleetCode" id="fleetCode" required="">
                                    <option selected="" disabled="" value="">{{ __('general.choose') }}...
                                    </option>
                                    @foreach ($fleet as $item)
                                        <option value="{{ $item->code }}">
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
                                    placeholder="{{ __('menu_order.order_date') }}" value="{{ now()->toDateString() }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="shipmentNumber">{{ __('menu_order.shipment_no') }} <i
                                        class="mdi mdi-information text-danger"></i></label>
                                <input class="form-control" name="shipmentNumber" id="shipmentNumber" type="text"
                                    required placeholder="{{ __('menu_order.shipment_no') }}" readonly>
                            </div>
                        </div>



                        <div class="row mt-4">
                            <input type="hidden" name="driverCode" id="driverCodeHidden">

                            <div class="col-md-6 position-relative">

                                <div id="driverLabelWrapper">
                                    <label class="form-label" for="driverCode" id="driverLabel">
                                        {{ __('menu_order.driver') }} <i class="mdi mdi-information text-danger"></i>
                                    </label>
                                </div>



                                <select class="js-example-basic-single" id="driverCode" required="">
                                    <option selected="" disabled="" value="">{{ __('general.choose') }}...
                                    </option>

                                </select>
                            </div>


                            <div class="col-md-6">
                                <label class="form-label" for="notes">{{ __('menu_order.notes') }} </label>
                                <input class="form-control" name="notes" id="notes" type="text"
                                    placeholder="Notes">
                            </div>


                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6 position-relative">
                                <label class="form-label" for="customerCode">{{ __('menu_order.customer') }}<i
                                        class="mdi mdi-information text-danger"></i></label>
                                <select class="js-example-basic-single" name="customerCode" id="customerCode"
                                    required="">
                                    <option selected="" disabled="" value="">{{ __('general.choose') }}...
                                    </option>
                                    @foreach ($customer as $item)
                                        <option value="{{ $item->code }}" data-id="{{ $item->id }}">
                                            {{ $item->code . ' - ' . $item->name }}
                                        </option>
                                    @endforeach
                                </select>`
                            </div>

                            <div class="col-md-6 position-relative">
                                <label class="form-label" for="routeTypeCode">{{ __('menu_order.load_type') }} <i
                                        class="mdi mdi-information text-danger"></i></label>
                                <select class="js-example-basic-single" name="routeTypeCode" id="routeTypeCode"
                                    required="">
                                    <option selected="" disabled="" value="">{{ __('general.choose') }}...
                                    </option>
                                    @foreach ($routeType as $item)
                                        <option value="{{ $item->code }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>



                        {{-- <div class="row mt-4">
                            <div class="col-md-6 position-relative">
                                <label class="form-label" for="originLocationCode">{{ __('menu_order.origin_location') }}
                                    <i class="mdi mdi-information text-danger"></i></label>
                                <select class="js-example-basic-single" name="originLocationCode" id="originLocationCode"
                                    required="">
                                    <option selected="" disabled="" value="">{{ __('general.choose') }}...
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-6 position-relative">
                                <label class="form-label"
                                    for="destinationLocationCode">{{ __('menu_order.destination_location') }}
                                    <i class="mdi mdi-information text-danger"></i> </label>
                                <select class="js-example-basic-single" name="destinationLocationCode"
                                    id="destinationLocationCode" required="">
                                    <option selected="" disabled="" value="">{{ __('general.choose') }}...
                                    </option>
                                </select>
                            </div>
                        </div> --}}

                        <div class="row">

                            <div class="col-md-6 position-relative">
                                <label class="form-label" for="routeData">{{ __('menu_order.route') }}
                                    <i class="mdi mdi-information text-danger"></i> </label>
                                <select class="js-example-basic-single" name="routeData" id="routeData" required="">
                                    <option selected="" disabled="" value="">{{ __('general.choose') }}...
                                    </option>
                                </select>
                            </div>


                            <div class="col-md-6 position-relative d-none" id="qtyField">
                                <label class="form-label" id="qtyLabel" for="qty"></label>
                                <input class="form-control" name="qty" id="qty" step="any" min="1"
                                    type="number" placeholder="" required>
                            </div>
                        </div>

                        {{-- <div class="row mt-4">
                            <div class="col-md-6 position-relative">
                                <label class="form-label" for="materialCode">Material</label>
                                <select class="js-example-basic-single" name="materialCode" id="materialCode">
                                    <option selected="" disabled="" value="">{{ __('general.choose') }}...
                                    </option>
                                    @foreach ($material as $item)
                                        <option value="{{ $item->code }}">
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
                                        <option value="{{ $item->code }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label" for="materialQty">Material Qty </label>
                                <input class="form-control" name="materialQty" id="materialQty" type="number"
                                    min="1" placeholder="Material Qty">
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
                                <th>Material</th>
                                <th>Unit</th>
                                <th>Qty</th>
                                <th>Unit2</th>
                                <th>Qty2</th>
                            </tr>
                        </thead>
                        <tbody id="materialForm">
                            <tr>
                                <td class="remove-btn"></td>
                                <td>
                                    <select class="js-example-basic-single" name="materialCode[]" id="materialCode_1"d>
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
                                    <input class="form-control" name="materialQty[]" id="materialQty_1" type="number"
                                        min="1" placeholder="Qty">
                                </td>

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
                                    <input class="form-control" name="materialQty2[]" id="materialQty2_1" type="number"
                                        min="1" placeholder="Qty">
                                </td>


                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>



            <div class="card d-none" id="card-customer-detail">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4> {{ __('menu_order.customer_detail_data') }}</h4>

                </div>
                <div class="card-body col-md-6">

                </div>
            </div>

            <div class="card">
                <div class="card-body col-md-12">
                    <ul class="nav nav-tabs" id="icon-tab" role="tablist">
                        <li class="nav-item"><a class="nav-link active txt-success" id="icon-home-tab"
                                data-bs-toggle="tab" href="#icon-home" role="tab" aria-controls="icon-home"
                                aria-selected="true">{{ __('menu_order.name') }}</a></li>
                        <li class="nav-item"><a class="nav-link txt-success" id="profile-icon-tabs" data-bs-toggle="tab"
                                href="#profile-icon" role="tab" aria-controls="profile-icon"
                                aria-selected="false">{{ __('menu_order.add_cost') }}</a>
                        </li>
                    </ul>
                    <div class="tab-content" id="icon-tabContent">

                        @include('operational.order.components.cost-add')
                        @include('operational.order.components.cost-component-add')

                    </div>
                </div>
            </div>

            <div class="card">
                <div class="col-12">
                    <div class="card-body">
                        <button class="btn btn-primary" id="save"
                            type="submit">{{ __('general.save_changes') }}</button>
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
    <script src="{{ asset('assets/js/flat-pickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/custom-flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src=" {{ asset('assets/js/select2/select2-custom.js') }}"></script>
    <script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>
    <script src=" {{ asset('assets/js/helper.js') }}"></script>


    <script>
        const drivers = @json($driver);

        $(document).ready(function() {

            $('#dt').DataTable({})

            generateCode('input[name="orderDate"]', '#code_display', '#code_hidden',
                '/ajax/order-generate-code');

            // Handle form submit dengan AJAX
            $('form').on('submit', function(e) {
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
        })

        $('input[name="orderDate"]').on('change', function() {
            generateCode('input[name="orderDate"]', '#code_display', '#code_hidden',
                '/ajax/order-generate-code');
        });

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
                            `<option value="${i.code}">${i.name} (${i.origin_location.name} - ${i.destination_location.name}) - ${i.description || ''}</option>`;

                    });
                    $('#routeData').html(html);
                    // Reinitialize Select2 for origin location dropdown after updating options
                });
            }
        }

        $('#add-material').on('click', function() {
            let row = $('#materialForm tr').length + 1;

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
                    min="1" placeholder="Qty">
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

        $(document).on('click', '.remove-btn', function() {
            if ($('#materialForm tr').length > 1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });

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

            setTimeout(function() {
                // Show the correct field based on the selection after 1 second (simulating processing time)
                if (selectedType === 'TONASE') {
                    $('#qtyLabel').html(
                        'Tonase <i class="mdi mdi-information text-danger"></i>'
                    ); // Update the label with icon
                    $('#qty').attr('placeholder', 'Enter Tonase'); // Update placeholder
                    $('#qty').val(1); // Set default value to 1
                    $('#qty').removeAttr('readonly'); // Remove readonly if it was set
                    $('#qtyField').removeClass('d-none'); // Show the field
                } else if (selectedType === 'TRIP') {
                    $('#qtyLabel').html(
                        'Ritase <i class="mdi mdi-information text-danger"></i>'
                    ); // Update the label with icon
                    $('#qty').attr('placeholder', 'Enter Ritase'); // Update placeholder
                    $('#qty').val(1); // Set default value to 1
                    $('#qty').attr('readonly', true); // Make the field readonly
                    $('#qtyField').removeClass('d-none'); // Show the field
                } else if (selectedType == 'KUBIKASE') {
                    $('#qtyLabel').html(
                        'Kubikase <i class="mdi mdi-information text-danger"></i>'
                    ); // Update the label with icon
                    $('#qty').attr('placeholder', 'Enter Kubikase'); // Update placeholder
                    $('#qty').val(1); // Set default value to 1
                    $('#qty').removeAttr('readonly'); // Remove readonly if it was set
                    $('#qtyField').removeClass('d-none'); // Show the field
                } else {
                    $('#qtyField').addClass('d-none'); // Hide the field if neither is selected
                }

                // Remove the loader once the logic is complete
                $('.loader-wrapper').remove();
            }, 1000); // Simulate 1-second delay for the loader
        });

        // function checkAndLoadRoute() {
        //     const customerCode = $('#customerCode').select2('val');
        //     const originLocationCode = $('#originLocationCode').select2('val');
        //     const destinationLocationCode = $('#destinationLocationCode').select2('val');

        //     if (customerCode && originLocationCode && destinationLocationCode) {
        //         $.get("{{ url('ajax/route-by-customer') }}/" + customerCode + "/" + originLocationCode + "/" +
        //             destinationLocationCode,
        //             function(data) {
        //                 const componentList = document.getElementById('component-list');
        //                 componentList.innerHTML = '';
        //                 index = 0;

        //                 data.forEach((item, i) => {
        //                     let row = `
    //                 <tr>
    //                     <td>
    //                         <a href="javascript:removeRow(${i})"
    //                             class="btn btn-icon btn-sm bg-danger-subtle me-1"
    //                             data-bs-toggle="tooltip" title="Delete">
    //                             <i class="mdi mdi-delete fs-14 text-danger"></i>
    //                         </a>
    //                     </td>
    //                     <td><input type="hidden"  name="componentName[]" readonly value="${item.cost_component.code}"> ${item.cost_component.name}</td>
    //                     <td><input class="form-control" name="description[]" value=""></td>
    //                      <td>
    //      <input class="form-control"  name="nominal[]" oninput="formatAngka(this)" type="text" min=1 readonly  value="${formatNumber(item.amount)}">
    // </td>
    //                 </tr>`;
        //                     componentList.insertAdjacentHTML('beforeend', row);
        //                     index++;
        //                 });
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

        function formatNumber(number) {
            return new Intl.NumberFormat('id-ID').format(number);
        }

        // Trigger origin location when both customer and route type are selected
        $('#customerCode, #routeTypeCode').on('change', function() {
            // checkAndLoadOriginLocation();
            checkAndLoadRouteOrder();
        });

        // Trigger destination location when origin location is also selected
        $('#originLocationCode').on('select2:select', function() {
            checkAndLoadDestinationLocation();
        });

        $('#routeData').on('select2:select', function() {
            checkAndLoadRoute();
        });

        $('#fleetCode').on('change', function() {
            let fleetcode = $(this).val();

            if (fleetcode) {
                $.get("/ajax/fleet-driver/" + fleetcode, function(data) {
                    const defaultDriverLabel = `
                        <label class="form-label" for="driverCode" id="driverLabel">
                            Driver <i class="mdi mdi-information text-danger"></i>
                        </label>
                    `;

                    $('#driverCode').prop('disabled', false); // Aktifkan select
                    $('#driverCode').val(''); // Kosongkan value
                    $('#driverCode').find('option:gt(0)').remove(); // Hapus semua option kecuali pertama
                    $('#driverCodeHidden').val(''); // Kosongkan hidden input
                    $('#driverLabelWrapper').html(defaultDriverLabel);


                    if (data) {
                        $('#driverCode').find('option:gt(0)').remove();

                        let matchedDriver = drivers.find(driver => driver.code === data);

                        if (matchedDriver) {
                            $('#driverCode').append(
                                `<option value="${matchedDriver.code}" selected>${matchedDriver.name}</option>`
                            );

                            $('#driverCodeHidden').val(matchedDriver.code);

                            $('#driverCode').attr('disabled', true);

                            $('#driverLabelWrapper').html(`
                                <div class="d-flex justify-content-between" id="driverLabelContainer">
                                    <label class="form-label" for="driverCode">Driver <i class="mdi mdi-information text-danger"></i></label>
                                    <div>
                                        <label class="form-label" for="driverCode">{{ __('menu_order.is_leave') }} </label>
                                        <input type="checkbox" class="form-check-input" name="isLeave">
                                    </div>
                                </div>
                            `);
                        } else {
                            // Kalau fleetCode kosong (select dikosongin)
                            $('#driverCode').prop('disabled', false).val('');
                            $('#driverCode').find('option:gt(0)').remove();
                            $('#driverCodeHidden').val('');
                            $('#driverLabelWrapper').html(defaultDriverLabel);
                        }
                    }
                });
            }
        });

        // Pasang event listener pakai event delegation
        $(document).on('change', 'input[name="isLeave"]', function() {
            if ($(this).is(':checked')) {
                // Enable select
                $('#driverCode').prop('disabled', false);

                // Kosongkan dulu select dan isi ulang semua data driver
                $('#driverCode').find('option:gt(0)').remove(); // Hapus semua kecuali placeholder

                // Tambahkan semua driver dari variabel global
                drivers.forEach(driver => {
                    $('#driverCode').append(
                        `<option value="${driver.code}">${driver.name}</option>`
                    );
                });

                $('#driverCodeHidden').val('');
            } else {
                // Checkbox di-uncheck → kembali ke kondisi seperti saat memilih fleetCode

                let fleetcode = $('#fleetCode').val();

                if (fleetcode) {
                    const defaultDriverLabel = `
                        <label class="form-label" for="driverCode" id="driverLabel">
                            Driver <i class="mdi mdi-information text-danger"></i>
                        </label>
                    `;
                    $.get("/ajax/fleet-driver/" + fleetcode, function(data) {
                        // Reset kondisi
                        $('#driverCode').prop('disabled', false);
                        $('#driverCode').val('');
                        $('#driverCode').find('option:gt(0)').remove();
                        $('#driverCodeHidden').val('');
                        $('#driverLabelWrapper').html(defaultDriverLabel);

                        if (data) {
                            let matchedDriver = drivers.find(driver => driver.code === data);

                            if (matchedDriver) {
                                $('#driverCode').append(
                                    `<option value="${matchedDriver.code}" selected>${matchedDriver.name}</option>`
                                );

                                $('#driverCodeHidden').val(matchedDriver.code);
                                $('#driverCode').attr('disabled', true);

                                $('#driverLabelWrapper').html(`
                            <div class="d-flex justify-content-between" id="driverLabelContainer">
                                <label class="form-label" for="driverCode">Driver <i class="mdi mdi-information text-danger"></i></label>
                                <div>
                                    <label class="form-label" for="driverCode">{{ __('menu_order.is_leave') }} </label>
                                    <input type="checkbox" class="form-check-input" name="isLeave">
                                </div>
                            </div>
                        `);
                            }
                        }
                    });
                } else {
                    // Jika fleetCode kosong
                    $('#driverCode').prop('disabled', false);
                    $('#driverCode').val('');
                    $('#driverCode').find('option:gt(0)').remove();
                    $('#driverCodeHidden').val('');
                    $('#driverLabelWrapper').html(defaultDriverLabel);
                }
            }
        });

        $('#driverCode').on('change', function() {
            $('#driverCodeHidden').val($(this).val());
        });

        $('#customerCode').on('change', function() {

            let customerCode = $(this).val();
            let customerId = $('#customerCode option:selected').data('id');

            $.get("/ajax/order-shipment-format/" + customerId, function(data) {
                $('#shipmentNumber').val(data);
            });


            if (customerCode) {
                $.get("/ajax/customer-detail/" + customerId, function(data) {
                    console.log(data);
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
                            <label class="form-label">${item.name} </label>
                            <input class="form-control" name="value[]" type="text" placeholder="${item.name}">
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
    </script>
@endpush
