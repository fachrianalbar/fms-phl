@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => $title,
    'secondSegment' => __('general.edit'),
])

@push('style')
    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/select2.css') }}">

    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/custom-select2.css') }}">
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
    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/sweetalert2.css') }} ">
@endpush

@section('content')
    <div class="col-sm-12">
        @include('partials.alert')
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }} {{ __('general.edit_data') }}</h4>

                <a href="{{ route($view . 'index') }}" class="btn btn-info">{{ __('general.back_to_list') }}</a>

            </div>
            <div class="card-body col-md-12">
                <form class="row g-3" method="post" action="{{ route($view . 'update', $data->id) }}"
                    onsubmit="return submitFormRoute('price', 'vendorPrice', 'personalVendorPrice')">
                    @csrf
                    @method('PUT')
                    <div class="row">

                        <div class="col-md-6">
                            <label class="form-label" for="name">{{ __('menu_route.name') }}</label>
                            {{-- <input class="form-control" name="name" id="name" type="text" required
                                placeholder="{{ __('menu_route.name') }}" value="{{ $data->name }}"> --}}

                            <select class="js-example-basic-single" name="name" id="name" required="">
                                <option selected="" disabled="" value="">{{ __('general.choose') }}...</option>
                                <option value="Tronton" {{ $data->name == 'Tronton' ? 'selected' : '' }}>Tronton</option>
                                <option value="Engkel" {{ $data->name == 'Engkel' ? 'selected' : '' }}>Engkel</option>
                                <option value="Colt Diesel" {{ $data->name == 'Colt Diesel' ? 'selected' : '' }}>Colt
                                    Diesel</option>
                            </select>
                        </div>

                        <div class="col-md-6 position-relative">
                            <label class="form-label" for="customerCode">{{ __('menu_route.customer') }}</label>
                            <select class="js-example-basic-single" name="customerCode" id="customerCode" required=""
                                onchange="locationByCustomer(this.value)">
                                <option selected="" disabled="" value="">{{ __('general.choose') }}...</option>
                                @foreach ($customer as $item)
                                    <option value="{{ $item->code }}"
                                        {{ $data->customerCode == $item->code ? 'selected' : '' }}>{{ $item->name }}
                                    </option>
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
                                    <option value="{{ $item->code }}"
                                        {{ $data->originLocationCode == $item->code ? 'selected' : '' }}>
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
                                    <option value="{{ $item->code }}"
                                        {{ $data->destinationLocationCode == $item->code ? 'selected' : '' }}>
                                        {{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mt-4">

                        <div class="col-md-6 position-relative">
                            <label class="form-label" for="routeType">{{ __('menu_route.load_type') }}</label>
                            <select class="js-example-basic-single" name="routeType" id="routeType" required="" disabled>
                                @foreach ($routeType as $item)
                                    <option value="{{ $item->code }}"
                                        {{ $data->routeTypeCode == $item->code ? 'selected' : '' }}>{{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="price">{{ __('menu_route.price') }}</label>
                            <input class="form-control" name="price" id="price" oninput="formatAngka(this)"
                                type="text" required placeholder="{{ __('menu_route.price') }}"
                                value="{{ number_format($data->price, 0, ',', '.') }}">
                        </div>




                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="vendorPrice">{{ __('menu_route.vendor_price') }}</label>
                            <input class="form-control" name="vendorPrice" id="vendorPrice" oninput="formatAngka(this)"
                                type="text" required placeholder="{{ __('menu_route.vendor_price') }}"
                                value="{{ number_format($data->vendorPrice, 0, ',', '.') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label"
                                for="personalVendorPrice">{{ __('menu_route.personal_vendor_price') }}</label>
                            <input class="form-control" name="personalVendorPrice" id="personalVendorPrice"
                                oninput="formatAngka(this)" type="text" required
                                placeholder="{{ __('menu_route.personal_vendor_price') }}"
                                value="{{ number_format($data->personalVendorPrice, 0, ',', '.') }}">
                        </div>


                    </div>


                    <div class="col-12">
                        <button class="btn btn-primary" type="submit">{{ __('general.edit') }}</button>
                    </div>
                </form>
            </div>


        </div>

        <div class="card">
            <div class="card-body col-md-12">
                <ul class="nav nav-tabs" id="icon-tab" role="tablist">
                    <li class="nav-item"><a class="nav-link active txt-success" id="icon-home-tab" data-bs-toggle="tab"
                            href="#icon-home" role="tab" aria-controls="icon-home" aria-selected="true">
                            {{ __('menu_route.route_detail') }}</a></li>
                    <li class="nav-item"><a class="nav-link txt-success" id="profile-icon-tabs" data-bs-toggle="tab"
                            href="#profile-icon" role="tab" aria-controls="profile-icon"
                            aria-selected="false">{{ __('menu_route.cost_component') }}</a></li>

                </ul>
                <div class="tab-content" id="icon-tabContent">

                    @include('data.route.components.route-detail-index')
                    @include('data.route.components.cost-component-add')
                </div>
            </div>
        </div>
    </div>
    <form id="delete-form" method="post">
        @csrf
        @method('DELETE')
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
    <script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src=" {{ asset('assets/js/select2/select2-custom.js') }}"></script>
    <script src=" {{ asset('assets/js/helper.js') }}"></script>


    <script>
        $('#dt').DataTable()


        function deleteCostComponent(uuid) {
            var url = '{{ route('data.route-detail.index') }}/' + uuid;
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

        function submitFormRoute(priceId, vendorPriceId, personalVendorPriceId) {
            const priceInput = document.getElementById(priceId);
            const vendorPriceInput = document.getElementById(vendorPriceId);
            const personalVendorPriceInput = document.getElementById(personalVendorPriceId);

            // Hapus titik pemisah ribuan
            const price = parseInt(priceInput.value.replace(/\./g, '')) || 0;
            const vendorPrice = parseInt(vendorPriceInput.value.replace(/\./g, '')) || 0;
            const personalVendorPrice = parseInt(personalVendorPriceInput.value.replace(/\./g, '')) || 0;

            // Validasi vendorPrice tidak boleh lebih besar dari price
            if (vendorPrice > price) {
                swal({
                    title: "{{ __('general.warning') }}",
                    text: "{{ __('menu_route.vendor_price_validation') }}",
                    icon: "warning",
                })
                return false;
            }

            if (personalVendorPrice > price) {
                swal({
                    title: "{{ __('general.warning') }}",
                    text: "{{ __('menu_route.personal_vendor_price_validation') }}",
                    icon: "warning",
                })
                return false;
            }


            // Set nilai input ke angka asli tanpa titik
            priceInput.value = price;
            vendorPriceInput.value = vendorPrice;
            personalVendorPriceInput.value = personalVendorPrice;

            return true; // Lanjutkan submit
        }
    </script>
@endpush
