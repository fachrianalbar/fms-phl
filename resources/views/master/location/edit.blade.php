@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => $title,
    'secondSegment' => 'Edit',
])

@push('style')
    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/select2.css') }}">

    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/custom-select2.css') }}">
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }} Edit Data</h4>

                <a href="{{ route($view . 'index') }}" class="btn btn-info">Back To List</a>

            </div>
            <div class="card-body col-md-12">
                <form class="row g-3" method="post" action="{{ route($view . 'update', $data->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-12">
                            <label class="form-label" for="name">Name <i
                                    class="icofont icofont-warning-alt text-danger"></i></label>
                            <input class="form-control" name="name" id="name" type="text" required
                                placeholder="Name" value="{{ $data->name }}">
                        </div>

                        {{-- <div class="col-md-6 position-relative">
                            <label class="form-label" for="customerCode">Customer Name</label>
                            <select class="js-example-basic-single" name="customerCode" id="customerCode" required="">
                                <option selected="" disabled="" value="">Choose...</option>
                                @foreach ($customer as $item)
                                    <option value="{{ $item->code }}"
                                        {{ $data->customerCode == $item->code ? 'selected' : '' }}>{{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div> --}}
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6 position-relative">
                            <label class="form-label" for="provinceId">Province Name</label>
                            <select class="js-example-basic-single" name="provinceId" id="provinceId"
                                onchange="cityByProvince(this.value)">
                                <option selected="" disabled="" value="">Choose...</option>
                                @foreach ($province as $item)
                                    <option value="{{ $item->id }}"
                                        {{ $data->provinceId == $item->id ? 'selected' : '' }}>{{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 position-relative">
                            <label class="form-label" for="provinceId">City Name</label>
                            <select class="js-example-basic-single" name="cityId" id="cityId"
                                onchange="districtByCity(this.value)">
                                <option selected="" disabled="" value="">Choose...</option>
                                @foreach ($city as $item)
                                    <option value="{{ $item->id }}" {{ $data->cityId == $item->id ? 'selected' : '' }}>
                                        {{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6 position-relative">
                            <label class="form-label" for="districtId">District Name</label>
                            <select class="js-example-basic-single" name="districtId" id="districtId">
                                <option selected="" disabled="" value="">Choose...</option>
                                @foreach ($district as $item)
                                    <option value="{{ $item->id }}"
                                        {{ $data->districtId == $item->id ? 'selected' : '' }}>{{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="address">Address</label>
                            <textarea class="form-control" name="address" id="address" placeholder=" Address" rows="4">{{ $data->address }}</textarea>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="latitude">Latitude <i
                                    class="icofont icofont-warning-alt text-danger"></i></label>
                            <input class="form-control" name="latitude" id="latitude" type="number" step="any"
                                placeholder="Latitude" value="{{ $data->latitude }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="longitude">Longitude <i
                                    class="icofont icofont-warning-alt text-danger"></i></label>
                            <input class="form-control" name="longitude" id="longitude" type="number" step="any"
                                placeholder="longitude" value="{{ $data->longitude }}">
                        </div>
                    </div>

                    <div class="col-12">
                        <button class="btn btn-primary" type="submit">Edit</button>
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
