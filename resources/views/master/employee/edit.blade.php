@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => $title,
    'secondSegment' => 'Edit',
]);

@push('style')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/select2.css') }}">
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
                        <div class="col-md-6">
                            <label class="form-label" for="name">Name <i
                                    class="icofont icofont-warning-alt text-danger"></i></label>
                            <input class="form-control" name="name" id="name" type="text"
                                placeholder="Employee Name" value="{{ $data->name }}" required>
                        </div>

                        <div class="col-md-6 position-relative">
                            <label class="form-label" for="positionCode">Position Name <i
                                    class="icofont icofont-warning-alt text-danger"></i></label>
                            <select class="js-example-basic-single" name="positionCode" id="positionCode" required>
                                <option selected="" disabled="" value="">Choose...</option>
                                @foreach ($position as $item)
                                    <option value="{{ $item->code }}"
                                        {{ $data->positionCode == $item->code ? 'selected' : '' }}>{{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-tooltip">Please select a valid state.</div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="nik">NIK </label>
                            <input class="form-control" name="nik" id="nik" type="text" placeholder="NIK"
                                value="{{ $data->nik }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="ktp">KTP </label>
                            <input class="form-control" name="ktp" id="ktp" type="number" placeholder="KTP"
                                value="{{ $data->ktp }}">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="email">Email </label>
                            <input class="form-control" name="email" id="email" type="email" placeholder="Email"
                                value="{{ $data->email }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="phone">Phone </label>
                            <input class="form-control" name="phone" id="phone" type="number" placeholder="Phone"
                                value="{{ $data->phone }}">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="col-xxl-3 box-col-6 text-start">Join Date </label>
                            <div class="input-group flatpicker-calender">
                                <input class="form-control" id="datetime-local" type="date" name="joinDate"
                                    placeholder="Join Date" value="{{ $data->joinDate }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="npwp">NPWP </label>
                            <input class="form-control" name="npwp" id="npwp" type="text" placeholder="NPWP"
                                value="{{ $data->npwp }}">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="col-xxl-3 box-col-6 text-start">Birth Date </label>
                            <div class="input-group flatpicker-calender">
                                <input class="form-control" id="datetime-local" type="date" name="birthDate"
                                    placeholder="Birth Date" value="{{ $data->birthDate }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="birthPlace">Birth Place </label>
                            <input class="form-control" name="birthPlace" id="birthPlace" type="text"
                                placeholder="Birth Place" value="{{ $data->birthPlace }}">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6 position-relative">
                            <label class="form-label" for="provinceId">Province Name </label>
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
                            <label class="form-label" for="provinceId">City Name </label>
                            <select class="js-example-basic-single" name="cityId" id="cityId"
                                onchange="districtByCity(this.value)">
                                <option selected="" disabled="" value="">Choose...</option>
                                @foreach ($city as $item)
                                    <option value="{{ $item->id }}"
                                        {{ $data->cityId == $item->id ? 'selected' : '' }}>{{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6 position-relative">
                            <label class="form-label" for="districtId">District Name </label>
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
                            <label class="form-label" for="address">Address </label>
                            <textarea class="form-control" name="address" id="address" placeholder=" Address" rows="4">{{ $data->address }}</textarea>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6 position-relative">
                            <label class="form-label" for="gender">Gender</label>
                            <select class="js-example-basic-single" name="gender" id="gender">
                                <option selected="" disabled="" value="">Choose...</option>
                                @foreach ($gender as $item)
                                    <option value="{{ $item->value }}"
                                        {{ $data->gender == $item->value ? 'selected' : '' }}>{{ $item->value }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 position-relative">
                            <label class="form-label" for="citizenship">Citizenship</label>
                            <select class="js-example-basic-single" name="citizenship" id="citizenship">
                                <option selected="" disabled="" value="">Choose...</option>
                                @foreach ($citizenship as $item)
                                    <option value="{{ $item->value }}"
                                        {{ $data->citizenship == $item->value ? 'selected' : '' }}>{{ $item->value }}
                                    </option>
                                @endforeach
                            </select>
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
    <script src="{{ asset('assets/js/flat-pickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/custom-flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src=" {{ asset('assets/js/select2/select2-custom.js') }}"></script>
@endpush
