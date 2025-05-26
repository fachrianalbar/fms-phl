@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => $title,
    'secondSegment' => 'Edit',
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
                        <h4>{{ $title }} Edit Data</h4>

                        <a href="{{ route($view . 'index') }}" class="btn btn-info">{{ __('general.back_to_list') }}</a>

                    </div>
                    <div class="card-body col-md-12">
                        <div class="row g-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label" for="name">Order Code <i
                                            class="icofont icofont-warning-alt text-danger"></i></label>
                                    <input type="hidden" name="code" value="{{ $data->code }}">
                                    <input class="form-control" type="text" required readonly disabled
                                        value="TO{{ now()->format('ymdHis') }}">
                                </div>

                                <div class="col-md-6 position-relative">
                                    <label class="form-label" for="fleetCode">Fleet<i
                                            class="icofont icofont-warning-alt text-danger"></i></label>

                                    <select class="js-example-basic-single" name="fleetCode" id="fleetCode" required="">
                                        <option selected="" disabled="" value="">Choose...</option>
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
                                    <label class="form-label" for="name">Order Date <i
                                            class="icofont icofont-warning-alt text-danger"></i></label>
                                    <input class="form-control" name="orderDate" id="datetime-local" type="date" required
                                        placeholder="Order Date" value="{{ $data->orderDate }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="shipmentNumber">Shipment No <i
                                            class="icofont icofont-warning-alt text-danger"></i></label>
                                    <input class="form-control" name="shipmentNumber" id="shipmentNumber" type="text"
                                        required placeholder="Shipment No" value="{{ $data->shipmentNumber }}">
                                </div>

                            </div>

                            <div class="row mt-4">
                                <div class="col-md-6 position-relative">
                                    <label class="form-label" for="driverCode">Driver <i
                                            class="icofont icofont-warning-alt text-danger"></i></label>
                                    <select class="js-example-basic-single" name="driverCode" id="driverCode"
                                        required="">
                                        <option selected="" disabled="" value="">Choose...</option>
                                        @foreach ($driver as $item)
                                            <option value="{{ $item->code }}"
                                                {{ $data->driverCode == $item->code ? 'selected' : '' }}>
                                                {{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="notes">Notes</label>
                                    <input class="form-control" name="notes" id="notes" type="text"
                                        placeholder="Notes" value="{{ $data->notes }}">
                                </div>

                            </div>

                            <div class="row mt-4">
                                <div class="col-md-6 position-relative">
                                    <label class="form-label" for="customerCode">Customer
                                        Name <i class="icofont icofont-warning-alt text-danger"></i></label>
                                    <select class="js-example-basic-single" name="customerCode" id="customerCode"
                                        required="">
                                        <option selected="" disabled="" value="">Choose...</option>
                                        @foreach ($customer as $item)
                                            <option value="{{ $item->code }}"
                                                {{ $data->customerCode == $item->code ? 'selected' : '' }}>
                                                {{ $item->code . ' - ' . $item->name }}</option>
                                        @endforeach
                                    </select>`
                                </div>

                                <div class="col-md-6 position-relative">
                                    <label class="form-label" for="orderTypeCode">Route Type <i
                                            class="icofont icofont-warning-alt text-danger"></i></label>
                                    <select class="js-example-basic-single" name="routeTypeCode" id="routeTypeCode"
                                        required="">
                                        <option selected="" disabled="" value="">Choose...</option>
                                        @foreach ($routeType as $item)
                                            <option value="{{ $item->code }}"
                                                {{ $route->routeTypeCode == $item->code ? 'selected' : '' }}>
                                                {{ $item->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-6 position-relative">
                                    <label class="form-label" for="originLocationCode">Origin Location <i
                                            class="icofont icofont-warning-alt text-danger"></i></label>
                                    <select class="js-example-basic-single" name="originLocationCode"
                                        id="originLocationCode" required="">
                                        <option selected="" disabled="" value="">Choose...</option>
                                        @foreach ($origin as $item)
                                            <option value="{{ $item->originLocationCode }}"
                                                {{ $route->originLocationCode == $item->originLocationCode ? 'selected' : '' }}>
                                                {{ $item->originLocation->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 position-relative">
                                    <label class="form-label" for="destinationLocationCode">Destination Location <i
                                            class="icofont icofont-warning-alt text-danger"></i> </label>
                                    <select class="js-example-basic-single" name="destinationLocationCode"
                                        id="destinationLocationCode" required="">
                                        <option selected="" disabled="" value="">Choose...</option>
                                        @foreach ($destination as $item)
                                            <option value="{{ $item->destinationLocationCode }}"
                                                {{ $route->destinationLocationCode == $item->destinationLocationCode ? 'selected' : '' }}>
                                                {{ $item->destinationLocation->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

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
                                <div class="col-md-6 position-relative " id="qtyField">
                                    <label class="form-label" id="qtyLabel" for="qty">{{ $label }}</label>
                                    <input class="form-control" name="qty" id="qty" step="any"
                                        min="1" max="100" min="1" type="number"
                                        value="{{ $data->qty }}" placeholder="" required>
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
                                    aria-selected="true">Additional
                                    Cost</a>
                            </li>
                        </ul>
                        <div class="tab-content" id="icon-tabContent">
                            @include('operational.order.components.cost-edit')
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="col-12">
                        <div class="card-body ">
                            <button class="btn btn-primary" id="save" type="submit">Save Changes</button>
                        </div>
                    </div>
                </div>
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

                const selectedType = $('#routeTypeCode').select2('val');

                loadQty(selectedType)
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
                        swal("Your data is safe!");
                    }
                });
            }

            $('#save').click(function(e) {
                const routeTypeCode = $('#routeTypeCode').select2('val');

                if (routeTypeCode === 'TONASE') {
                    const qty = $('#qty').val();

                    if (qty > 100) {
                        e.preventDefault();
                        swal({
                            title: "Warning",
                            text: "Tonase cannot be higher than 100",
                            icon: "warning",
                        })
                        return;
                    }
                }

            });


            function checkAndLoadOriginLocation() {
                const customerCode = $('#customerCode').select2('val'); // Use select2 to get the value
                const routeTypeCode = $('#routeTypeCode').select2('val'); // Use select2 to get the value

                if (customerCode && routeTypeCode) {
                    let html = '<option selected="" disabled="" value="">Choose...</option>';
                    $('#originLocationCode').html(html);

                    $.get("{{ url('ajax/origin-by-customer') }}/" + customerCode + "/" + routeTypeCode, function(data) {
                        data.forEach(i => {
                            html += '<option value="' + i.code + '">' + i.name + '</option>';
                        });
                        $('#originLocationCode').html(html);
                        // Reinitialize Select2 for origin location dropdown after updating options
                        $('#originLocationCode').select2();
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
                    let html = '<option selected="" disabled="" value="">Choose...</option>';
                    $('#destinationLocationCode').html(html);

                    $.get("{{ url('ajax/destination-by-customer') }}/" + customerCode + "/" + routeTypeCode + "/" +
                        originLocationCode,
                        function(data) {
                            data.forEach(i => {
                                html += '<option value="' + i.code + '">' + i.name + '</option>';
                            });
                            $('#destinationLocationCode').html(html);
                            // Reinitialize Select2 for destination location dropdown after updating options
                            $('#destinationLocationCode').select2();
                        });
                }
            }


            function checkAndLoadRoute() {
                const customerCode = $('#customerCode').select2('val');
                const originLocationCode = $('#originLocationCode').select2('val');
                const destinationLocationCode = $('#destinationLocationCode').select2('val');

                if (customerCode && originLocationCode && destinationLocationCode) {
                    $.get("{{ url('ajax/route-by-customer') }}/" + customerCode + "/" + originLocationCode + "/" +
                        destinationLocationCode,
                        function(data) {

                        });
                }
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

            $('#dt').DataTable()
        </script>
    @endpush
