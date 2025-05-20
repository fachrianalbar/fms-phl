@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => $title,
    'secondSegment' => __('general.add'),
])

@push('style')
    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/select2.css') }}">

    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/custom-select2.css') }}">
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }} {{ __('general.add_data') }}</h4>

                <a href="{{ route($view . 'index') }}" class="btn btn-info">{{ __('general.back_to_list') }}</a>

            </div>
            <div class="card-body col-md-12">
                <form class="row g-3" method="post" action="{{ route($view . 'store') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-12">
                            <label class="form-label" for="name">Name <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <input class="form-control" name="name" id="name" type="text" required
                                placeholder="Name">
                        </div>
                        {{-- 
                        <div class="col-md-6 position-relative">
                            <label class="form-label" for="customerCode">Customer Name</label>
                            <select class="js-example-basic-single" name="customerCode" id="customerCode" required="">
                                <option selected="" disabled="" value="">Choose...</option>
                                @foreach ($customer as $item)
                                    <option value="{{ $item->code }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-tooltip">Please select a valid state.</div>
                        </div> --}}
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6 position-relative">
                            <label class="form-label" for="provinceId">Province Name</label>
                            <select class="js-example-basic-single" name="provinceId" id="provinceId"
                                onchange="cityByProvince(this.value)">
                                <option selected="" disabled="" value="">Choose...</option>
                                @foreach ($province as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 position-relative">
                            <label class="form-label" for="provinceId">City Name</label>
                            <select class="js-example-basic-single" name="cityId" id="cityId"
                                onchange="districtByCity(this.value)">
                                <option selected="" disabled="" value="">Choose...</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6 position-relative">
                            <label class="form-label" for="districtId">District Name</label>
                            <select class="js-example-basic-single" name="districtId" id="districtId">
                                <option selected="" disabled="" value="">Choose...</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="address">Address</label>
                            <textarea class="form-control" name="address" id="address" placeholder=" Address" rows="4"></textarea>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="latitude">Latitude <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <input class="form-control" name="latitude" id="latitude" type="number" step="any"
                                placeholder="Latitude" step="any">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="longitude">Longitude <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <input class="form-control" name="longitude" id="longitude" type="number"
                                placeholder="longitude" step="any">
                        </div>
                    </div>

                    <div class="col-12">
                        <button class="btn btn-primary" type="submit">Add</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src=" {{ asset('assets/js/select2/select2-custom.js') }}"></script>

    <script>
        function cityByProvince(id) {
            let html = '<option selected="" disabled="" value="">Choose...</option>'

            $('#cityId').html(html)
            $('#districtId').html(html)

            $.get("{{ url('ajax/city-by-province') }}/" + id, function(data) {
                data.forEach(i => {
                    html += '<option value="' + i.id + '">' + i.name + '</option>'
                });
                $('#cityId').html(html)
            })
        }

        function districtByCity(id) {
            let html = '<option selected="" disabled="" value="">Choose...</option>'

            $('#districtId').html(html)

            $.get("{{ url('ajax/district-by-city') }}/" + id, function(data) {

                data.forEach(i => {
                    html += '<option value="' + i.id + '">' + i.name + '</option>'
                });
                $('#districtId').html(html)
            })
        }
    </script>
@endpush
