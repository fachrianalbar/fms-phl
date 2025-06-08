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
                                <label class="form-label" for="name">Order Code <i
                                        class="mdi mdi-information text-danger"></i></label>
                                <input type="hidden" name="code" value="TO{{ now()->format('ymdHis') }}">
                                <input class="form-control" type="text" required placeholder="Name" readonly disabled
                                    value="TO{{ now()->format('ymdHis') }}">
                            </div>

                            <div class="col-md-6 position-relative">
                                <label class="form-label" for="fleetCode">Fleet<i
                                        class="mdi mdi-information text-danger"></i></label>

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
                                <label class="form-label" for="name">Order Date <i
                                        class="mdi mdi-information text-danger"></i></label>
                                <input class="form-control" name="orderDate" id="datetime-local" type="date" required
                                    placeholder="Order Date" value="{{ now()->toDateString() }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="shipmentNumber">Shipment No <i
                                        class="mdi mdi-information text-danger"></i></label>
                                <input class="form-control" name="shipmentNumber" id="shipmentNumber" type="text"
                                    required placeholder="Shipment No">
                            </div>
                        </div>



                        <div class="row mt-4">

                            <div class="col-md-6 position-relative">
                                <label class="form-label" for="driverCode">Driver <i
                                        class="mdi mdi-information text-danger"></i></label>
                                <select class="js-example-basic-single" name="driverCode" id="driverCode" required="">
                                    <option selected="" disabled="" value="">{{ __('general.choose') }}...
                                    </option>
                                    @foreach ($driver as $item)
                                        <option value="{{ $item->code }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>


                            <div class="col-md-6">
                                <label class="form-label" for="notes">Notes </label>
                                <input class="form-control" name="notes" id="notes" type="text"
                                    placeholder="Notes">
                            </div>


                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6 position-relative">
                                <label class="form-label" for="customerCode">Customer
                                    Name <i class="mdi mdi-information text-danger"></i></label>
                                <select class="js-example-basic-single" name="customerCode" id="customerCode"
                                    required="">
                                    <option selected="" disabled="" value="">{{ __('general.choose') }}...
                                    </option>
                                    @foreach ($customer as $item)
                                        <option value="{{ $item->code }}">{{ $item->code . ' - ' . $item->name }}
                                        </option>
                                    @endforeach
                                </select>`
                            </div>

                            <div class="col-md-6 position-relative">
                                <label class="form-label" for="routeTypeCode">Route Type <i
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



                        <div class="row mt-4">
                            <div class="col-md-6 position-relative">
                                <label class="form-label" for="originLocationCode">Origin Location <i
                                        class="mdi mdi-information text-danger"></i></label>
                                <select class="js-example-basic-single" name="originLocationCode" id="originLocationCode"
                                    required="">
                                    <option selected="" disabled="" value="">{{ __('general.choose') }}...
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-6 position-relative">
                                <label class="form-label" for="destinationLocationCode">Destination Location <i
                                        class="mdi mdi-information text-danger"></i> </label>
                                <select class="js-example-basic-single" name="destinationLocationCode"
                                    id="destinationLocationCode" required="">
                                    <option selected="" disabled="" value="">{{ __('general.choose') }}...
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6 position-relative d-none" id="qtyField">
                                <label class="form-label" id="qtyLabel" for="qty"></label>
                                <input class="form-control" name="qty" id="qty" step="any" min="1"
                                    max="100" type="number" placeholder="" required>
                            </div>
                        </div>


                    </div>
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
                                aria-selected="false">Add
                                Cost</a>
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
        $(document).ready(function() {
            $('#dt').DataTable({})

        })

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

        $('#save').click(function(e) {
            const routeTypeCode = $('#routeTypeCode').select2('val');

            if (routeTypeCode === 'TONASE') {
                const qty = $('#qty').val();

                if (qty > 100) {
                    e.preventDefault();
                    swal({
                        title: "{{ __('general.warning') }}",
                        text: "Tonase cannot be higher than 100",
                        icon: "warning",
                    })
                    return;
                }
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

                // Remove the loader once the logic is complete
                $('.loader-wrapper').remove();
            }, 1000); // Simulate 1-second delay for the loader
        });

        function checkAndLoadRoute() {
            const customerCode = $('#customerCode').select2('val');
            const originLocationCode = $('#originLocationCode').select2('val');
            const destinationLocationCode = $('#destinationLocationCode').select2('val');

            if (customerCode && originLocationCode && destinationLocationCode) {
                $.get("{{ url('ajax/route-by-customer') }}/" + customerCode + "/" + originLocationCode + "/" +
                    destinationLocationCode,
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
            checkAndLoadOriginLocation();
        });

        // Trigger destination location when origin location is also selected
        $('#originLocationCode').on('select2:select', function() {
            checkAndLoadDestinationLocation();
        });

        $('#destinationLocationCode').on('select2:select', function() {
            checkAndLoadRoute();
        });
    </script>
@endpush
