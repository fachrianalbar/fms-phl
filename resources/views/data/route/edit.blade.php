@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => $title,
    'secondSegment' => 'Edit',
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
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }} Edit Data</h4>

                <a href="{{ route($view . 'index') }}" class="btn btn-info">{{ __('general.back_to_list') }}</a>

            </div>
            <div class="card-body col-md-12">
                <form class="row g-3" method="post" action="{{ route($view . 'update', $data->id) }}"
                    onsubmit="return submitForm('price')">
                    @csrf
                    @method('PUT')
                    <div class="row">

                        <div class="col-md-6">
                            <label class="form-label" for="name">Name</label>
                            <input class="form-control" name="name" id="name" type="text" required
                                placeholder="Name" value="{{ $data->name }}">
                        </div>

                        <div class="col-md-6 position-relative">
                            <label class="form-label" for="customerCode">Customer Name</label>
                            <select class="js-example-basic-single" name="customerCode" id="customerCode" required=""
                                onchange="locationByCustomer(this.value)">
                                <option selected="" disabled="" value="">Choose...</option>
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
                            <label class="form-label" for="originLocationCode">Origin Location</label>
                            <select class="js-example-basic-single" name="originLocationCode" id="originLocationCode"
                                required="">
                                <option selected="" disabled="" value="">Choose...</option>
                                @foreach ($location as $item)
                                    <option value="{{ $item->code }}"
                                        {{ $data->originLocationCode == $item->code ? 'selected' : '' }}>
                                        {{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 position-relative">
                            <label class="form-label" for="destinationLocationCode">Destination Location</label>
                            <select class="js-example-basic-single" name="destinationLocationCode"
                                id="destinationLocationCode" required="">
                                <option selected="" disabled="" value="">Choose...</option>
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
                        {{-- <div class="col-md-6 position-relative">
                            <label class="form-label" for="fleetTypeCode">Fleet Type Name</label>
                            <select class="js-example-basic-single" name="fleetTypeCode" id="fleetTypeCode" required="">
                                <option selected="" disabled="" value="">Choose...</option>
                                @foreach ($type as $item)
                                    <option value="{{ $item->code }}"
                                        {{ $data->fleetTypeCode == $item->code ? 'selected' : '' }}>{{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div> --}}

                        <div class="col-md-6 position-relative">
                            <label class="form-label" for="routeType">Route Type</label>
                            <select class="js-example-basic-single" name="routeType" id="routeType" required="" disabled>
                                @foreach ($routeType as $item)
                                    <option value="{{ $item->code }}"
                                        {{ $data->routeTypeCode == $item->code ? 'selected' : '' }}>{{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="pricte">Price</label>
                            <input class="form-control" name="price" id="price" oninput="formatAngka(this)"
                                type="text" required placeholder="Price"
                                value="{{ number_format($data->price, 0, ',', '.') }}">
                        </div>
                    </div>


                    <div class="col-12">
                        <button class="btn btn-primary" type="submit">Edit</button>
                    </div>
                </form>
            </div>


        </div>

        <div class="card">
            <div class="card-body col-md-12">
                <ul class="nav nav-tabs" id="icon-tab" role="tablist">
                    <li class="nav-item"><a class="nav-link active txt-success" id="icon-home-tab" data-bs-toggle="tab"
                            href="#icon-home" role="tab" aria-controls="icon-home" aria-selected="true"> Route
                            Detail</a></li>
                    <li class="nav-item"><a class="nav-link txt-success" id="profile-icon-tabs" data-bs-toggle="tab"
                            href="#profile-icon" role="tab" aria-controls="profile-icon" aria-selected="false">Cost
                            Component</a></li>

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
    </script>
@endpush
