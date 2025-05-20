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

                <a href="{{ route($view . 'index') }}" class="btn btn-info">{{ __('general.back_to_list') }}</a>

            </div>
            <div class="card-body col-md-12">
                <form class="row g-3" method="post" action="{{ route($view . 'update', $data->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <label class="form-label" for="name">Name <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <input class="form-control" name="name" id="name" type="text" required
                                placeholder="Name" value="{{ $data->name }}">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="picName">Pic Name</label>
                            <input class="form-control" name="picName" id="picName" type="text" placeholder="Pic Name"
                                value="{{ $data->picName }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="nickname">Nickname</label>
                            <input class="form-control" name="nickname" id="nickname" type="text" placeholder="Nickname"
                                value="{{ $data->nickname }}">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="email">Email <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <input class="form-control" name="email" id="email" type="email" placeholder="Email"
                                required value="{{ $data->email }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="phone">Phone <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <input class="form-control" name="phone" id="phone" type="number" required
                                placeholder="Phone" value="{{ $data->phone }}">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="address1">Address 1</label>
                            <textarea class="form-control" name="address1" id="address1" placeholder=" Address 1" rows="4">{{ $data->address1 }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="address2">Address 2</label>
                            <textarea class="form-control" name="address2" id="address2" placeholder=" Address 2" rows="4">{{ $data->address2 }}</textarea>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="npwp">Npwp</label>
                            <input class="form-control" name="npwp" id="npwp" type="text" placeholder="Npwp"
                                value="{{ $data->npwp }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="accountNumber"> Account Number</label>
                            <input class="form-control" name="accountNumber" id="accountNumber" type="number"
                                placeholder="Account Number" value="{{ $data->accountNumber }}">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="ppn">Ppn</label>
                            <input class="form-control" name="ppn" id="ppn" type="number" placeholder="Ppn"
                                value="{{ $data->ppn }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="pph"> Pph</label>
                            <input class="form-control" name="pph" id="pph" type="number" placeholder="Pph"
                                value="{{ $data->pph }}">
                        </div>
                    </div>

                    {{-- <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="telegramUsername">Telegram Username</label>
                            <input class="form-control" name="telegramUsername" id="telegramUsername" type="text"
                                placeholder="Telegram Username" value="{{ $data->telegramUsername }}">
                        </div>
                    </div> --}}

                    <div class="row mt-4">
                        <div class="col-md-6 position-relative">
                            <label class="form-label" for="companyCode"> {{ __('menu_customer.company') }}</label>
                            <select class="js-example-basic-single" name="companyCode" id="companyCode" required="">
                                <option selected="" disabled="" value="">
                                    {{ __('general.choose') }}...</option>
                                @foreach ($company as $item)
                                    <option value="{{ $item->code }}"
                                        {{ $data->companyCode == $item->code ? 'selected' : '' }}>{{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="due_date_duration">Due Date Duration</label>
                            <input class="form-control" name="due_date_duration" id="due_date_duration" type="number"
                                min="1" placeholder="Telegram Username" value="{{ $data->due_date_duration }}">
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
@endpush
