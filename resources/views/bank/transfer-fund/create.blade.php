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
                    onsubmit="return submitForm('nominal')">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label" for="name">Code <i
                                    class="icofont icofont-warning-alt text-danger"></i></label>
                            <input type="hidden" name="code" value="CA{{ now()->format('ymdHis') }}">
                            <input class="form-control" type="text" required placeholder="Name" readonly disabled
                                value="CA{{ now()->format('ymdHis') }}">
                        </div>
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
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label">Sender <i class="icofont icofont-warning-alt text-danger"></i>
                            </label>
                            <select class="js-example-basic-single" name="sender" id="sender" required>
                                <option selected="" disabled="" value="">Choose...</option>
                                @foreach ($userBankSender as $item)
                                    <option value="{{ $item->code }}">
                                        {{ $item->bank->name . ' - ' . $item->accountNumber . ' - ' . $item->accountName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Receiver <i
                                    class="icofont icofont-warning-alt text-danger"></i></label>
                            <select class="js-example-basic-single" name="receiver" id="receiver" required>
                                <option selected="" disabled="" value="">Choose...</option>
                                @foreach ($userBankReceiver as $item)
                                    <option value="{{ $item->code }}">
                                        {{ $item->bank->name . ' - ' . $item->accountNumber . ' - ' . $item->accountName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mt-4 ">
                        {{-- <div class="col-md-6">
                            <label class="form-label">Cash/Transfer <i class="icofont icofont-warning-alt text-danger"></i>
                            </label>
                            <select class="js-example-basic-single" name="transferType" id="transferType"
                                onchange="showBank(this.value)" required>
                                <option selected="" disabled="">Choose...</option>
                                @foreach ($transferType as $item)
                                    <option value="{{ $item }}">{{ $item }}</option>
                                @endforeach
                            </select>
                        </div> --}}

                        <div class="col-md-6">
                            <label class="form-label" for="nominal">Nominal <i
                                    class="icofont icofont-warning-alt text-danger"></i></label>
                            <input class="form-control" name="nominal" id="nominal" type="text" placeholder="Nominal"
                                oninput="formatAngka(this)">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="description">Deskripsi / Notes <i
                                    class="icofont icofont-warning-alt text-danger"></i></label>
                            <textarea class="form-control" name="description" id="description" rows="3"></textarea>
                        </div>
                    </div>

                    {{-- <div class="row mt-4 d-none" id="bank">
                        <div class="col-md-6">
                            <label class="form-label">Bank Pengirim <i class="icofont icofont-warning-alt text-danger"></i>
                            </label>
                            <select class="js-example-basic-single" name="bankSender" id="bankSender" required>
                                <option selected="" disabled="" value="">Choose...</option>
                                @foreach ($bankSender as $item)
                                    <option value="{{ $item->code }}">
                                        {{ $item->bankName . ' - ' . $item->accountNumber }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Bank Penerima <i class="icofont icofont-warning-alt text-danger"></i>
                            </label>
                            <select class="js-example-basic-single" name="bankReceiver" id="bankReceiver" required>
                                <option selected="" disabled="" value="">Choose...</option>
                            </select>
                        </div>
                    </div> --}}

                    {{-- <div class="row mt-4">


                     


                    </div> --}}

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
    <script src="{{ asset('assets/js/flat-pickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/custom-flatpickr.js') }}"></script>
    <script src=" {{ asset('assets/js/helper.js') }}"></script>
@endpush
