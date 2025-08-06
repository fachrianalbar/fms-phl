@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => $title,
    'secondSegment' => __('general.add'),
])

@push('style')
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
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }} {{ __('general.add_data') }}</h4>

                <a href="{{ route($view . 'index') }}" class="btn btn-info">{{ __('general.back_to_list') }}</a>

            </div>
            <div class="card-body col-md-12">
                <form class="row g-3" id="routeForm" onsubmit="return submitForm('price')">
                    {{-- @csrf --}}

                    <div class="row">
                        <div class="col-md-12">
                            <label class="form-label" for="name">{{ __('menu_route.name') }}</label>
                            {{-- <input class="form-control" name="name" id="name" type="text" required
                                placeholder="{{ __('menu_route.name') }}"> --}}

                            <select class="js-example-basic-single" name="name" id="name" required="">
                                <option selected="" disabled="" value="">{{ __('general.choose') }}...</option>
                                <option value="Tronton">Tronton</option>
                                <option value="Engkel">Engkel</option>
                                <option value="Colt Diesel">Colt Diesel</option>
                                <option value="Engkel 1000">Engkel 1000</option>
                                <option value="Engkel 1500">Engkel 1500</option>
                                <option value="Engkel kurang 20 ton">Engkel kurang 20 ton</option>
                                <option value="Engkel lebih 20 ton">Engkel lebih 20 ton</option>
                                <option value="Tronton kurang 30 ton">Tronton kurang 30 ton</option>
                                <option value="Tronton lebih 30 ton">Tronton lebih 30 ton</option>


                            </select>
                        </div>
                    </div>


                    <div class="row mt-4">


                        <div class="col-md-6 position-relative">
                            <label class="form-label" for="customerCode">{{ __('menu_route.customer') }}</label>
                            <select class="js-example-basic-single" name="customerCode" id="customerCode" required="">
                                <option selected="" disabled="" value="">{{ __('general.choose') }}...</option>
                                @foreach ($customer as $item)
                                    <option value="{{ $item->code }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 position-relative">
                            <label class="form-label" for="routeType">{{ __('menu_route.load_type') }}</label>
                            <select class="js-example-basic-single" name="routeType" id="routeType" required="">
                                <option selected="" disabled="" value="">{{ __('general.choose') }}...</option>
                                @foreach ($routeType as $item)
                                    <option value="{{ $item->code }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>


                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6 position-relative">
                            <label class="form-label"
                                for="originLocationCode">{{ __('menu_route.origin_location') }}</label>
                            <select class="js-example-basic-single" name="originLocationCode" id="originLocationCode"
                                required="">
                                <option selected="" disabled="" value="">{{ __('general.choose') }}...</option>
                                @foreach ($location as $item)
                                    <option value="{{ $item->code }}">
                                        {{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 position-relative">
                            <label class="form-label"
                                for="destinationLocationCode">{{ __('menu_route.destination_location') }}</label>
                            <select class="js-example-basic-single" name="destinationLocationCode"
                                id="destinationLocationCode" required="">
                                <option selected="" disabled="" value="">{{ __('general.choose') }}...</option>
                                @foreach ($location as $item)
                                    <option value="{{ $item->code }}">
                                        {{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mt-4">
                        {{-- <div class="col-md-6 position-relative">
                            <label class="form-label" for="fleetTypeCode">Fleet Type Name</label>
                            <select class="js-example-basic-single" name="fleetTypeCode" id="fleetTypeCode" required="">
                                <option selected="" disabled="" value="">{{ __('general.choose') }}...</option>
                                @foreach ($type as $item)
                                    <option value="{{ $item->code }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div> --}}

                        {{-- <div class="col-md-6">
                            <label class="form-label" for="pricte">Price</label>
                            <input class="form-control" name="price" id="price" type="text"
                                oninput="formatAngka(this)" required placeholder="Price">
                        </div> --}}


                    </div>

                    <div class="col-12">
                        <button class="btn btn-primary" type="button"
                            onclick="addRoute()">{{ __('general.add') }}</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- List of added routes displayed as cards --}}
        <form action="{{ route($view . 'store') }}" method="POST" onsubmit="return removePriceFormatting()">
            @csrf

            <div class="col-sm-12 mt-4">
                <div class="card">

                    <div class="card-header">
                        <h4>{{ __('menu_route.list_route') }}</h4>

                    </div>

                    <div class="card-body">
                        <table class="table table-order table-bordered dt-responsive table-responsive nowrap"
                            id="routeTable">
                            <thead>
                                <tr>
                                    <th>{{ __('menu_route.action') }}</th>
                                    <th>{{ __('menu_route.route_name') }}</th>
                                    <th>{{ __('menu_route.customer') }}</th>
                                    <th>{{ __('menu_route.load_type') }}</th>
                                    <th>{{ __('menu_route.origin') }}</th>
                                    <th>{{ __('menu_route.destination') }}</th>
                                    <th>{{ __('menu_route.price') }}</th>
                                </tr>
                            </thead>
                            <tbody id="routeList">
                                <!-- Table rows will be dynamically added here -->
                            </tbody>
                        </table>

                        <div class="col-12 mt-4">
                            <button class="btn btn-primary" type="submit">{{ __('general.save') }}</button>
                        </div>
                    </div>

                </div>

            </div>

            <!-- Submit all added routes together -->

        </form>
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
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src=" {{ asset('assets/js/select2/select2-custom.js') }}"></script>
    <script src=" {{ asset('assets/js/helper.js') }}"></script>
    <script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>



    <script>
        $(document).ready(function() {
            $('#routeTable').DataTable()
        });

        // function locationByCustomer(code) {
        //     loader()
        //     let html = '<option selected="" disabled="" value="">{{ __('general.choose') }}...</option>'

        //     $('#originLocationCode').html(html)
        //     $('#destinationLocationCode').html(html)

        //     $.get("{{ url('ajax/location-by-customer') }}/" + code, function(data) {
        //         data.forEach(i => {
        //             html += '<option value="' + i.code + '">' + i.name + '</option>'
        //         });
        //         $('#originLocationCode').html(html)
        //         $('#destinationLocationCode').html(html)
        //     })
        // }
    </script>

    <script>
        let routeData = [];

        function addRoute() {
            // Get values from form inputs
            let name = document.getElementById('name');
            let customerCode = document.getElementById('customerCode');
            let routeTypeCode = document.getElementById('routeType');
            let originLocationCode = document.getElementById('originLocationCode');
            let destinationLocationCode = document.getElementById('destinationLocationCode');

            let customer = customerCode.options[customerCode.selectedIndex].text;
            let routeType = routeTypeCode.options[routeTypeCode.selectedIndex].text;
            let origin = originLocationCode.options[originLocationCode.selectedIndex].text;
            let destination = destinationLocationCode.options[destinationLocationCode.selectedIndex].text;

            let nameValue = name.value;
            let customerValue = customerCode.options[customerCode.selectedIndex].value;
            let routeTypeValue = routeTypeCode.options[routeTypeCode.selectedIndex].value;
            let originValue = originLocationCode.options[originLocationCode.selectedIndex].value;
            let destinationValue = destinationLocationCode.options[destinationLocationCode.selectedIndex].value;

            // Check if any input is empty
            if (!validateFormInputs(customerCode, routeTypeCode, originLocationCode, destinationLocationCode)) {
                swal({
                    title: "{{ __('general.warning') }}",
                    text: "{{ __('menu_route.please_fill_out_all_fields') }}",
                    icon: "warning",
                })
                return;
            }

            if (origin === destination) {
                swal({
                    title: "{{ __('general.warning') }}",
                    text: "{{ __('menu_route.origin_and_destination_cannot_be_the_same') }}",
                    icon: "warning",
                })
                return;
            }

            // Check if price is missing in any existing rows
            if (!validatePrices()) {
                swal({
                    title: "{{ __('general.warning') }}",
                    text: "{{ __('menu_route.please_enter_price_for_all_added_routes') }}",
                    icon: "warning",
                })
                return;
            }

            // Check for duplicate route
            // if (isDuplicateRoute(customer, routeType, origin, destination)) {
            //     swal({
            //         title: "{{ __('general.warning') }}",
            //         text: "{{ __('menu_route.this_route_has_aiready_been_added') }}",
            //         icon: "warning",
            //     })
            //     return;
            // }

            routeData.push({
                name: nameValue,
                customer: customer,
                routeType: routeType,
                origin: origin,
                destination: destination,
                customerValue: customerValue,
                routeTypeValue: routeTypeValue,
                originValue: originValue,
                destinationValue: destinationValue,
                price: '' // Price will be inputted later
            });

            // Render the updated list of routes in table format
            renderRoutes();
        }

        function renderRoutes() {
            const routeList = document.getElementById('routeList');
            routeList.innerHTML = ''; // Clear existing list

            routeData.forEach((route, index) => {
                let row = `
                <tr>
                      <td>
                         <a href="javascript:removeRoute(${index})"
                            class="btn btn-icon btn-sm bg-danger-subtle"
                            data-bs-toggle="tooltip" title="Delete">
                                <i class="mdi mdi-delete fs-14 text-danger"></i>
                         </a>
                         
                    </td>

                      <td>
                        <input type="hidden" name="name[]" value="${route.name}">
                        ${route.name}
                    </td>
                  
                    <td>
                        <input type="hidden" name="customerCode[]" value="${route.customerValue}">
                        ${route.customer}
                    </td>
                    <td>
                        <input type="hidden" name="routeTypeCode[]" value="${route.routeTypeValue}">
                        ${route.routeType}
                    </td>
                    <td>
                        <input type="hidden" name="originLocationCode[]" value="${route.originValue}">
                        ${route.origin}
                    </td>
                    <td>
                        <input type="hidden" name="destinationLocationCode[]" value="${route.destinationValue}">
                        ${route.destination}
                    </td>
                    <td>
                        <input type="text" class="form-control" name="price[]" id="price-${index}" value="${route.price}" oninput="updatePrice(${index}, this.value); formatAngka(this)" required>
                    </td>
                  
                </tr>
            `;
                routeList.insertAdjacentHTML('beforeend', row);
            });
        }

        function validateFormInputs(customerCode, routeTypeCode, originLocationCode, destinationLocationCode) {
            return customerCode.value && routeTypeCode.value && originLocationCode.value && destinationLocationCode.value;
        }

        function validatePrices() {
            let valid = true;
            routeData.forEach((route, index) => {
                const priceInput = document.getElementById(`price-${index}`);
                if (!priceInput.value) {
                    valid = false;
                }
            });
            return valid;
        }

        function updatePrice(index, value) {
            routeData[index].price = value;
        }

        function removeRoute(index) {
            routeData.splice(index, 1); // Remove the route from the array
            renderRoutes(); // Re-render the table
        }

        function isDuplicateRoute(customer, routeType, origin, destination) {
            return routeData.some(route =>
                route.customer === customer &&
                route.routeType === routeType &&
                route.origin === origin &&
                route.destination === destination
            );
        }

        // Function to remove format (dots) from price before form submission
        function removePriceFormatting() {
            routeData.forEach((route, index) => {
                const priceInput = document.getElementById(`price-${index}`);
                let rawPrice = priceInput.value.replace(/\./g, ''); // Remove all dots to get raw number
                priceInput.value = rawPrice; // Set the value back to the input without dots
            });
            return true; // Allow form submission after formatting is removed
        }
    </script>
@endpush
