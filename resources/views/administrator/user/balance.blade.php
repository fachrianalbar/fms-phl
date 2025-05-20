@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => $title,
    'secondSegment' => 'Nominal',
])

@push('style')
    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/select2.css') }}">

    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/custom-select2.css') }}">
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }} Add Nominal</h4>

                <a href="{{ route($view . 'index') }}" class="btn btn-info">{{ __('general.back_to_list') }}</a>

            </div>
            <div class="card-body col-md-12">
                <form class="row g-3" method="post" action="{{ route($view . 'store-balance') }}"
                    onsubmit="return submitForm('nominal')">
                    @csrf
                    <input type="hidden" value="{{ $data->id }}" name="id">

                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label" for="nominal">Nominal <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <input class="form-control" name="nominal" id="nominal" type="text" required
                                placeholder="Nominal" oninput="formatAngka(this)">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="description">Deskripsi / Notes <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <textarea class="form-control" name="description" id="description" required rows="3"></textarea>
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
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src=" {{ asset('assets/js/select2/select2-custom.js') }}"></script>
    <script src=" {{ asset('assets/js/helper.js') }}"></script>
@endpush
