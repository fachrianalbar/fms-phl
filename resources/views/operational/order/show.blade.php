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
    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/sweetalert2.css') }} ">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/datatables.css') }}">
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

                        <a href="{{ route($view . 'index') }}" class="btn btn-info">Back To List</a>

                    </div>
                    <div class="card-body col-md-12">
                        <div class="row g-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label" for="name">Order Code</label>
                                    <input class="form-control" type="text" required readonly
                                        value="TO{{ now()->format('ymdHis') }}">
                                </div>

                                <div class="col-md-6 position-relative">
                                    <label class="form-label" for="fleetCode">Fleet</label>


                                    <input class="form-control" name="orderDate" id="datetime-local" type="date" readonly
                                        required placeholder="Order Date" value="{{ $data->fleet->plateNumber }}">
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <label class="form-label" for="name">Order Date</label>
                                    <input class="form-control" name="orderDate" type="text" readonly required
                                        placeholder="Order Date" value="{{ $data->orderDate }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="shipmentNumber">Shipment No</label>
                                    <input class="form-control" name="shipmentNumber" id="shipmentNumber" type="text"
                                        readonly required placeholder="Shipment No" value="{{ $data->shipmentNumber }}">
                                </div>

                            </div>

                            <div class="row mt-4">
                                <div class="col-md-6 position-relative">
                                    <label class="form-label" for="driverCode">Driver</label>
                                    <input class="form-control" name="driverCode" id="driverCode" type="text" readonly
                                        required placeholder="Shipment No" value="{{ $data->driver->name }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="notes">Notes</label>
                                    <input class="form-control" name="notes" id="notes" type="text" readonly
                                        placeholder="Notes" value="{{ $data->notes }}">
                                </div>

                            </div>

                            <div class="row mt-4">
                                <div class="col-md-6 position-relative">
                                    <label class="form-label" for="customerCode">Customer
                                        Name </label>
                                    <input class="form-control" name="customerCode" id="customerCode" type="text"
                                        readonly placeholder="Customer" value="{{ $data->customer->name }}">
                                </div>

                                <div class="col-md-6 position-relative">
                                    <label class="form-label" for="orderTypeCode">Route Type</label>
                                    <input class="form-control" name="routeTypeCode" id="routeTypeCode" type="text"
                                        readonly placeholder="Route Type" value="{{ $route->routeType->name }}">
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-6 position-relative">
                                    <label class="form-label" for="originLocationCode">Origin Location</label>
                                    <input class="form-control" name="originLocationCode" id="originLocationCode"
                                        type="text" readonly placeholder="Origin Location"
                                        value="{{ $route->originLocation->name }}">
                                </div>

                                <div class="col-md-6 position-relative">
                                    <label class="form-label" for="destinationLocationCode">Destination Location
                                    </label>
                                    <input class="form-control" name="destinationLocationCode"
                                        id="destinationLocationCode" type="text" readonly
                                        placeholder="Origin Location" value="{{ $route->destinationLocation->name }}">
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
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>{{ $title }} Cost Data</h4>
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
                        <table class="display" id="dt">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Component Name</th>
                                    <th>Component Type</th>
                                    {{-- <th>Description</th> --}}
                                    <th>Nominal</th>
                                </tr>
                            </thead>
                            <tbody>

                                @php
                                    $i = 1;
                                    $totalPrice = 0;
                                @endphp
                                @foreach ($route->routeDetail as $item)
                                    <tr>
                                        {{-- <td></td> --}}
                                        <td>{{ $i++ }}</td>
                                        <td>{{ $item->costComponent->name }}</td>
                                        <td>Mandatory</td>
                                        {{-- <td>-</td> --}}
                                        <td>
                                            @php
                                                $price = 0;
                                                if ($item->amount != 0) {
                                                    $price += $item->amount;
                                                }

                                                if ($item->percentage) {
                                                    $route = App\Models\Data\Route::where(
                                                        'code',
                                                        $item->routeCode,
                                                    )->first();

                                                    $price = $route->price * ($item->percentage / 100);
                                                }

                                                $totalPrice += $price;
                                            @endphp
                                            {{ 'Rp ' . number_format($price, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                                @if ($route->routeTypeCode == 'TONASE')
                                    <tr>
                                        {{-- <td></td> --}}
                                        <td>{{ $i++ }}</td>
                                        <td>Bonus Tonase</td>
                                        <td>Bonus</td>
                                        {{-- <td>-</td> --}}
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
                                @endif
                                @foreach ($cost as $item)
                                    @php
                                        $totalPrice += $item->nominal;
                                    @endphp
                                    <tr>
                                        <td>{{ $i++ }}</td>
                                        <td>{{ $item->costComponent->name }}</td>
                                        <td>{{ $item->type }}</td>
                                        {{-- <td>{{ $item->description }}</td> --}}
                                        <td>{{ 'Rp ' . number_format($item->nominal, 0, ',', '.') }}</td>

                                    </tr>
                                @endforeach
                                <!-- New rows will be added here -->
                            </tbody>
                        </table>
                        <h4>Total: Rp {{ number_format($totalPrice, 0, ',', '.') }} </h4>
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
        <script src="{{ asset('assets/js/datatable/datatables/jquery.dataTables.min.js') }}"></script>
        <script src=" {{ asset('assets/js/helper.js') }}"></script>


        <script>
            $(document).ready(function() {

                const selectedType = $('#routeTypeCode').select2('val');

                // loadQty(selectedType)
            });

            function deleteCost(id) {
                var url = '{{ route('operational.order-cost.destroy', ':id') }}'; // Use placeholder ':id'
                url = url.replace(':id', id); // Replace the placeholder with actual id

                $('#delete-form').attr('action', url);

                swal({
                    title: "Are you sure?",
                    text: "Want to delete this data?",
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
                // Add loader to the body
                $('body').append(`
            <div class="loader-wrapper">
                <div class="loader">
                    <div class="loader4"></div>
                </div>
            </div>
        `);

                const selectedType = $('#routeTypeCode').select2('val'); // Get the selected value from select2

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
