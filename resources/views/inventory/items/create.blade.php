@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => $title,
    'secondSegment' => __('general.add'),
])

@push('style')
    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/select2.css') }}">

    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/custom-select2.css') }}">
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }} {{ __('general.add_data') }}</h4>

                <a href="{{ route('inventory.items.index') }}" class="btn btn-info">{{ __('general.back_to_list') }}</a>

            </div>
            <div class="card-body col-md-12">
                @include('partials.alert')
                <form class="row g-3" method="post" novalidate="" action="{{ route('inventory.items.store') }}"
                    onsubmit="return submitForm('price')">
                    @csrf
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="codes">Code <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <input class="form-control" name="code" id="code" type="text" required
                                placeholder="Code" value="{{ old('code') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="name">Item Name <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <input class="form-control" name="name" id="name" type="text" required
                                placeholder="Item Name" value="{{ old('name') }}">
                        </div>

                    </div>

                    <div class="row mt-4">
                        <div class="col-md-4">
                            <label class="form-label" for="unitCode">Item Unit <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <select class="js-example-basic-single" name="unitCode" id="unitCode" required>
                                <option selected="" disabled="" value="">{{ __('general.choose') }}...</option>
                                @foreach ($unit as $item)
                                    <option value="{{ $item->code }}"
                                        {{ old('unitCode') == $item->code ? 'selected' : '' }}>{{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="type">Type <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <select class="js-example-basic-single" name="type" id="type" required>
                                <option selected="" disabled="" value="">{{ __('general.choose') }}...</option>
                                @foreach ($types as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ old('type', 'part') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="price">Price <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <input class="form-control text-end" name="price" id="price" type="text"
                                inputmode="decimal" placeholder="Price" value="{{ old('price') }}"
                                oninput="formatAngka(this)">
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
