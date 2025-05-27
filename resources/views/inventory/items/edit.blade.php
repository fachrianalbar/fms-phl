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
                <form class="row g-3" method="post" novalidate="" action="{{ route($view . 'update', $data->id) }}"
                    onsubmit="return submitForm('price')">
                    @csrf
                    @method('PUT')
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="codes">Code <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <input class="form-control" name="code" id="code" type="text" required
                                placeholder="Code" value="{{ $data->code }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="name">Item Name <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <input class="form-control" name="name" id="name" type="text" required
                                placeholder="Item Name" value="{{ $data->name }}">
                        </div>

                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="brandName">Item Brand <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <input class="form-control" name="brandName" id="brandName" type="text" required
                                placeholder="Item Brand" value="{{ $data->brandName }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="categoryCode">Item Category <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <select class="js-example-basic-single" name="categoryCode" id="categoryCode" required>
                                <option selected="" disabled="" value="">{{ __('general.choose') }}...</option>
                                @foreach ($category as $item)
                                    <option value="{{ $item->code }}"
                                        {{ $data->categoryCode == $item->code ? 'selected' : '' }}>{{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>


                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="warehouseCode">Warehouse <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <select class="js-example-basic-single" name="warehouseCode" id="warehouseCode" required>
                                <option selected="" disabled="" value="">{{ __('general.choose') }}...</option>
                                @foreach ($warehouse as $item)
                                    <option value="{{ $item->code }}"
                                        {{ $data->warehouseCode == $item->code ? 'selected' : '' }}>
                                        {{ $item->name }}>{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="supplierCode">Supplier <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <select class="js-example-basic-single" name="supplierCode" id="supplierCode" required>
                                <option selected="" disabled="" value="">{{ __('general.choose') }}...</option>
                                @foreach ($supplier as $item)
                                    <option value="{{ $item->code }}"
                                        {{ $data->supplierCode == $item->code ? 'selected' : '' }}>{{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="unitCode">Item Unit <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <select class="js-example-basic-single" name="unitCode" id="unitCode" required>
                                <option selected="" disabled="" value="">{{ __('general.choose') }}...</option>
                                @foreach ($unit as $item)
                                    <option value="{{ $item->code }}"
                                        {{ $data->unitCode == $item->code ? 'selected' : '' }}>{{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- <div class="col-md-6">
                            <label class="form-label" for="itemLocationCode">Item Location <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <select class="js-example-basic-single" name="itemLocationCode" id="itemLocationCode" required>
                                <option selected="" disabled="" value="">{{ __('general.choose') }}...</option>
                                @foreach ($location as $item)
                                    <option value="{{ $item->code }}"
                                        {{ $data->itemLocationCode == $item->code ? 'selected' : '' }}>{{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div> --}}
                        {{-- 
                        <div class="col-md-6">
                            <label class="form-label" for="price">Price <i
                                    class="mdi mdi-information text-danger"></i></input></label>
                            <input class="form-control" name="price" id="price" type="text"
                                oninput="formatAngka(this)" required placeholder="Price"
                                value="{{ intval($data->price) }}">
                        </div> --}}
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
    <script src=" {{ asset('assets/js/helper.js') }}"></script>
@endpush
