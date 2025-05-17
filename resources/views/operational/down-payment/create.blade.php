@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => $title,
    'secondSegment' => 'Add',
])

@push('style')
    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/select2.css') }}">

    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/custom-select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }} {{ __('general.add_data') }}</h4>

                <a href="{{ route($view . 'index') }}" class="btn btn-info">Back To List</a>

            </div>
            <div class="card-body col-md-12">
                <form class="row g-3" method="post" action="{{ route($view . 'store') }}"
                    onsubmit="return submitForm('price')">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label" for="name">Code <i
                                    class="icofont icofont-warning-alt text-danger"></i></label>
                            <input type="hidden" name="code" value="TDP{{ now()->format('ymdHis') }}">
                            <input class="form-control" type="text" required placeholder="Name" readonly disabled
                                value="TDP{{ now()->format('ymdHis') }}">
                        </div>

                        <div class="col-md-6 position-relative">
                            <label class="form-label" for="driverCode">Driver Name <i
                                    class="icofont icofont-warning-alt text-danger"></i></label>
                            <select class="js-example-basic-single" id="driverCode" name="driverCode" required="">
                                <option selected="" disabled="" value="">Choose...</option>
                                @foreach ($driver as $item)
                                    <option value="{{ $item->code }}">{{ $item->code . ' - ' . $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-3">
                            <label class="form-label" for="name">Date <i
                                    class="icofont icofont-warning-alt text-danger"></i></label>
                            <input class="form-control" name="date" id="datetime-local" type="date" required
                                placeholder="Order Date" value="{{ now()->toDateString() }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Time</label>
                            <input class="form-control digits" name="time" type="time"
                                value="{{ now()->setTimezone('Asia/Jakarta')->format('H:i') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="price">Price <i
                                    class="icofont icofont-warning-alt text-danger"></i></label>
                            <input class="form-control" name="price" id="price" type="text" placeholder="Price"
                                oninput="formatAngka(this)" required>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="note">Note </label>
                            <input class="form-control" name="note" id="note" type="text" placeholder="Note">
                        </div>
                    </div>

                    <div class="col-12">
                        <button class="btn btn-primary" type="submit">Save</button>
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
    <script src=" {{ asset('assets/js/helper.js') }}"></script>
@endpush
