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
                        <div class="col-md-6">
                            <label class="form-label" for="name">Name <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <input class="form-control" name="name" id="name" type="text"
                                placeholder="Employee Name" required>
                        </div>

                        <div class="col-md-6 position-relative">
                            <label class="form-label" for="positionCode">Position Name <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <select class="js-example-basic-single" name="positionCode" id="positionCode" required>
                                <option selected="" disabled="" value="">{{ __('general.choose') }}...</option>
                                @foreach ($position as $item)
                                    <option value="{{ $item->code }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="nik">NIK </label>
                            <input class="form-control" name="nik" id="nik" type="text" placeholder="NIK">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="ktp">KTP </label>
                            <input class="form-control" name="ktp" id="ktp" type="number" placeholder="KTP">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="email">Email </label>
                            <input class="form-control" name="email" id="email" type="email" placeholder="Email">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="phone">Phone </label>
                            <input class="form-control" name="phone" id="phone" type="number" placeholder="Phone">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="col-xxl-3 box-col-6 text-start">Join Date </label>
                            <div class="input-group flatpicker-calender">
                                <input class="form-control" id="datetime-local" type="date" name="joinDate"
                                    placeholder="Join Date">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="npwp">NPWP </label>
                            <input class="form-control" name="npwp" id="npwp" type="text" placeholder="NPWP">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="col-xxl-3 box-col-6 text-start">Birth Date </label>
                            <div class="input-group flatpicker-calender">
                                <input class="form-control" id="datetime-local" type="date" name="birthDate"
                                    placeholder="Birth Date">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="birthPlace">Birth Place </label>
                            <input class="form-control" name="birthPlace" id="birthPlace" type="text"
                                placeholder="Birth Place">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6 position-relative">
                            <label class="form-label" for="provinceId">Province Name </label>
                            <select class="js-example-basic-single" name="provinceId" id="provinceId"
                                onchange="cityByProvince(this.value)">
                                <option selected="" disabled="" value="">{{ __('general.choose') }}...
                                </option>
                                @foreach ($province as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 position-relative">
                            <label class="form-label" for="provinceId">City Name </label>
                            <select class="js-example-basic-single" name="cityId" id="cityId"
                                onchange="districtByCity(this.value)">
                                <option selected="" disabled="" value="">{{ __('general.choose') }}...
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6 position-relative">
                            <label class="form-label" for="districtId">District Name </label>
                            <select class="js-example-basic-single" name="districtId" id="districtId">
                                <option selected="" disabled="" value="">{{ __('general.choose') }}...
                                </option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="address">Address </label>
                            <textarea class="form-control" name="address" id="address" placeholder=" Address" rows="4"></textarea>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6 position-relative">
                            <label class="form-label" for="gender">Gender</label>
                            <select class="js-example-basic-single" name="gender" id="gender"="">
                                <option selected="" disabled="" value="">{{ __('general.choose') }}...
                                </option>
                                @foreach ($gender as $item)
                                    <option value="{{ $item->value }}">{{ $item->value }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 position-relative">
                            <label class="form-label" for="citizenship">Citizenship</label>
                            <select class="js-example-basic-single" name="citizenship" id="citizenship">
                                <option selected="" disabled="" value="">{{ __('general.choose') }}...
                                </option>
                                @foreach ($citizenship as $item)
                                    <option value="{{ $item->value }}">{{ $item->value }}</option>
                                @endforeach
                            </select>
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
    <script src="{{ asset('assets/js/flat-pickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/custom-flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src=" {{ asset('assets/js/select2/select2-custom.js') }}"></script>

    <script>
        function cityByProvince(id) {
            let html = '<option selected="" disabled="" value="">{{ __('general.choose') }}...</option>'

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
            let html = '<option selected="" disabled="" value="">{{ __('general.choose') }}...</option>'

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
