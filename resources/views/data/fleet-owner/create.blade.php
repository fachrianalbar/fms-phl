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

                <a href="{{ route($view . 'index') }}" class="btn btn-info">Back To List</a>

            </div>
            <div class="card-body col-md-12">
                <form class="row g-3" method="post" action="{{ route($view . 'store') }}">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label" for="fleetCode">Fleet Name <i
                                    class="icofont icofont-warning-alt text-danger"></i></label>
                            <select class="js-example-basic-single" name="fleetCode" id="fleetCode" required="">
                                <option selected="" disabled="" value="">Choose...</option>
                                @foreach ($fleet as $item)
                                    <option value="{{ $item->code }}">{{ $item->plateNumber }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="fleetTypeCode">Fleet Type <i
                                    class="icofont icofont-warning-alt text-danger"></i></label>
                            <select class="js-example-basic-single" name="fleetTypeCode" id="fleetTypeCode" required="">
                                <option selected="" disabled="" value="">Choose...</option>
                                @foreach ($fleetType as $item)
                                    <option value="{{ $item->code }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="vehicleRegistrationNumber">Vehicle Registration Number <i
                                    class="icofont icofont-warning-alt text-danger"></i></label>
                            <input class="form-control" name="vehicleRegistrationNumber" id="vehicleRegistrationNumber"
                                type="text" required placeholder="Vehicle Registration Number">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="vehicleRegistrationNumberExpDate">Vehicle Registration Number
                                Expired Date <i class="icofont icofont-warning-alt text-danger"></i></label>
                            <div class="input-group flatpicker-calender">
                                <input class="form-control" id="datetime-local" type="date"
                                    name="vehicleRegistrationNumberExpDate"
                                    placeholder="Vehicle Registration Number Expired Date">
                            </div>
                        </div>
                    </div>


                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="kir">KIR <i
                                    class="icofont icofont-warning-alt text-danger"></i></label>
                            <input class="form-control" name="kir" id="kir" type="text" required
                                placeholder="KIR">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="kirExpDate">KIR
                                Expired Date <i class="icofont icofont-warning-alt text-danger"></i></label>
                            <div class="input-group flatpicker-calender">
                                <input class="form-control" id="datetime-local" type="date" name="kirExpDate"
                                    placeholder="KIR Expired Date">
                            </div>
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
@endpush
