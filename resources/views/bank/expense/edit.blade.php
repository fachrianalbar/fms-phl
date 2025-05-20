@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => $title,
    'secondSegment' => 'Edit',
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
                <h4>{{ $title }} Edit Data</h4>

                <a href="{{ route($view . 'index') }}" class="btn btn-info">{{ __('general.back_to_list') }}</a>

            </div>
            <div class="card-body col-md-12">
                <form class="row g-3" method="post" action="{{ route($view . 'update', $data->id) }}"
                    onsubmit="return submitForm('nominal')">
                    @csrf
                    @method('PUT')
                    <div class="row">

                        <div class="col-md-6">
                            <label class="form-label" for="name">Date <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <input class="form-control" name="date" id="datetime-local" type="date" required
                                placeholder="Order Date" value="{{ $data->date_only }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Time</label>
                            <input class="form-control digits" name="time" type="time" value="{{ $data->time_only }}">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label">User Bank <i class="icofont icofont-warning-alt text-danger"></i>
                            </label>
                            <select class="js-example-basic-single" name="userBankCode" id="userBankCode" required>
                                <option selected="" disabled="" value="">Choose...</option>
                                @foreach ($userBank as $item)
                                    <option value="{{ $item->code }}"
                                        {{ $data->userBankCode == $item->code ? 'selected' : '' }}>
                                        {{ $item->bank->name . ' - ' . $item->accountNumber . ' - ' . $item->accountName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="driverCode">Driver <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <select class="js-example-basic-single" name="driverCode" id="driverCode" required>
                                <option selected="" disabled="" value="">Choose...</option>
                                @foreach ($driver as $item)
                                    <option value="{{ $item->code }}"
                                        {{ $data->expense->driverCode == $item->code ? 'selected' : '' }}>
                                        {{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mt-4 ">
                        <div class="col-md-6">
                            <label class="form-label" for="transactionTypeCode">Expense Type <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <select class="js-example-basic-single" name="transactionTypeCode" id="transactionTypeCode"
                                required>
                                <option selected="" disabled="" value="">Choose...</option>
                                <option value="FTT250403153003"
                                    {{ $data->transactionTypeCode == 'FTT250403153003' ? 'selected' : '' }}>Expense Office
                                </option>
                                <option value="FTT250403152955"
                                    {{ $data->transactionTypeCode == 'FTT250403152955' ? 'selected' : '' }}>Expense
                                </option>

                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="nominal">Nominal <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <input class="form-control" name="nominal" id="nominal" type="text"
                                value="{{ $data->nominal }}" placeholder="Nominal" oninput="formatAngka(this)">
                        </div>
                    </div>

                    <div class="row mt-4">


                        <div class="col-md-12">
                            <label class="form-label" for="description">Deskripsi / Notes <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <textarea class="form-control" name="description" id="description" rows="3">{{ $data->description }}</textarea>
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
    <script src="{{ asset('assets/js/flat-pickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/custom-flatpickr.js') }}"></script>
    <script src=" {{ asset('assets/js/helper.js') }}"></script>
@endpush
