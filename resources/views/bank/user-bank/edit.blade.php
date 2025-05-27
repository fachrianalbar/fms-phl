@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => $title,
    'secondSegment' => __('general.edit'),
])

@push('style')
    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/select2.css') }}">

    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/custom-select2.css') }}">
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }} {{ __('general.edit_data') }}</h4>

                <a href="{{ route($view . 'index') }}" class="btn btn-info">{{ __('general.back_to_list') }}</a>

            </div>
            <div class="card-body col-md-12">
                <form class="row g-3" method="post" action="{{ route($view . 'update', $data->id) }}"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label" for="bankCode">Bank Name <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <select class="js-example-basic-single" name="bankCode" id="bankCode" required>
                                <option value="">{{ __('general.choose') }}...</option>
                                @foreach ($bank as $item)
                                    <option value="{{ $item->code }}"
                                        {{ $data->bankCode == $item->code ? 'selected' : '' }}>
                                        {{ $item->bankCode . ' - ' . $item->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="accountNumber">Account Number <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <input class="form-control" name="accountNumber" id="accountNumber" type="text" required
                                placeholder="Account Number" value="{{ $data->accountNumber }}">
                        </div>

                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="accountName">Account Name <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <input class="form-control" name="accountName" id="accountName" type="text" required
                                placeholder="Account Name" value="{{ $data->accountName }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="type">Type <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <select class="js-example-basic-single" name="type" id="type" required>
                                <option value="">{{ __('general.choose') }}...</option>
                                <option value=1 {{ $data->type == 1 ? 'selected' : '' }}>Person</option>
                                <option value=2 {{ $data->type == 2 ? 'selected' : '' }}>Company</option>
                            </select>
                        </div>
                    </div>

                    {{-- <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="balance">Balance <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <input class="form-control" name="balance" id="balance" type="text" required
                                oninput="formatAngka(this)" placeholder="Balance">
                        </div>
                    </div> --}}

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
