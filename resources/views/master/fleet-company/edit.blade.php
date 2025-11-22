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
            <div class="card-body col-md-6">
                <form class="row g-3" method="post" action="{{ route($view . 'update', $data->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="col-md-12">
                        <label class="form-label" for="name">{{ __('menu_fleet_company.name') }}</label>
                        <input class="form-control" name="name" id="name" type="text"
                            placeholder="{{ __('menu_fleet_company.name') }}" value="{{ $data->name }}">
                    </div>

                    <div class="col-md-12 position-relative">
                        <label class="form-label" for="type"> {{ __('menu_fleet_company.type') }} <i
                                class="mdi mdi-information text-danger"></i></label>
                        <select class="js-example-basic-single" name="type" id="type" required="">
                            <option selected="" disabled="" value="">
                                {{ __('general.choose') }}...</option>
                            <option value="Internal" {{ $data->type == 'Internal' ? 'selected' : '' }}>Internal</option>
                            <option value="External" {{ $data->type == 'External' ? 'selected' : '' }}>External</option>
                        </select>
                    </div>


                    <div class="col-12">
                        <button class="btn btn-primary" type="submit">{{ __('general.edit') }}</button>
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
