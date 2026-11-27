@php
    $routePrefix = $routePrefix ?? 'inventory.items.';
    $itemType = $itemType ?? ($data->type ?? \App\Models\Inventory\Item::TYPE_PART);
    $entityLabel = $entityLabel ?? ($itemType === \App\Models\Inventory\Item::TYPE_JASA ? 'Jasa' : 'Barang');
    $isJasa = $itemType === \App\Models\Inventory\Item::TYPE_JASA;

    $priceValue = old('price') !== null
        ? old('price')
        : ($data->price === null ? '' : number_format((float) $data->price, 0, ',', '.'));
@endphp

@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Inventori',
    'secondSegment' => __('general.edit') . ' ' . $entityLabel,
])

@push('style')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom-select2.css') }}">
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Edit {{ $entityLabel }}</h4>

                <a href="{{ route($routePrefix . 'index') }}" class="btn btn-info">{{ __('general.back_to_list') }}</a>
            </div>
            <div class="card-body col-md-12">
                @include('partials.alert')
                <form class="row g-3" method="post" novalidate="" action="{{ route($routePrefix . 'update', $data->id) }}"
                    onsubmit="return submitForm('price')">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="type" value="{{ $itemType }}">

                    <div class="row mt-4 g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="code_display">Kode {{ $entityLabel }}</label>
                            <input class="form-control font-monospace" id="code_display" type="text"
                                value="{{ $data->code }}" readonly disabled>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="name">Nama {{ $entityLabel }} <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <input class="form-control" name="name" id="name" type="text" required
                                placeholder="Nama {{ $entityLabel }}" value="{{ old('name', $data->name) }}">
                        </div>
                    </div>

                    <div class="row mt-2 g-3">
                        <div class="col-md-4">
                            <label class="form-label" for="categoryCode">Kategori</label>
                            <select class="js-example-basic-single" name="categoryCode" id="categoryCode">
                                <option value="">{{ __('general.choose') }}...</option>
                                @foreach ($category ?? [] as $cat)
                                    <option value="{{ $cat->code }}"
                                        {{ old('categoryCode', $data->categoryCode) == $cat->code ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="unitCode">Satuan {{ $entityLabel }} <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <select class="js-example-basic-single" name="unitCode" id="unitCode" required>
                                <option selected="" disabled="" value="">{{ __('general.choose') }}...</option>
                                @foreach ($unit as $item)
                                    <option value="{{ $item->code }}"
                                        {{ old('unitCode', $data->unitCode) == $item->code ? 'selected' : '' }}>
                                        {{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        @if (! $isJasa)
                            <div class="col-md-4">
                                <label class="form-label" for="warehouseCode">Gudang</label>
                                <select class="js-example-basic-single" name="warehouseCode" id="warehouseCode">
                                    <option value="">{{ __('general.choose') }}...</option>
                                    @foreach ($warehouse ?? [] as $wh)
                                        <option value="{{ $wh->code }}"
                                            {{ old('warehouseCode', $data->warehouseCode) == $wh->code ? 'selected' : '' }}>
                                            {{ $wh->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="col-md-4">
                            <label class="form-label" for="price">Harga {{ $entityLabel }}</label>
                            <input class="form-control rupiah-input text-end font-monospace" name="price" id="price"
                                type="text" inputmode="numeric" placeholder="0" value="{{ $priceValue }}"
                                oninput="formatAngka(this)">
                        </div>
                    </div>

                    <div class="col-12 mt-4">
                        <button class="btn btn-primary" type="submit">Perbarui {{ $entityLabel }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2-custom.js') }}"></script>
    <script src="{{ asset('assets/js/helper.js') }}?v={{ filemtime(public_path('assets/js/helper.js')) }}"></script>
@endpush
