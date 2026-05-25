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
                    <label class="form-label" for="name">Name</label>
                    <input class="form-control" name="name" id="name" value="{{ $data->name }}" type="text"
                        required placeholder="Name">
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="price">Price</label>
                    <input class="form-control" name="price" id="price" value="{{ $data->price ? number_format($data->price, 0, ',', '.') : '' }}" type="text"
                        placeholder="Price (e.g: 1.000.000)">
                <div class="col-md-12 position-relative">
                        <label class="form-label" for="type">Type</label>
                        <select class="js-example-basic-single" name="type" id="type" required="">
                            <option selected="" disabled="" value="">{{ __('general.choose') }}...</option>
                @foreach ($type as $item)
                <option value="{{ $item->value }}" {{ $item->value == $data->type ? 'selected' : '' }}>
                    {{ ucfirst($item->value) }}
                </option>
                @endforeach
                </select>
                <div class="invalid-tooltip">Please select a valid state.</div>
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

<script>
    $(document).ready(function() {
        // Format Rupiah on input
        $('#price').on('input', function() {
            let value = $(this).val();
            $(this).val(formatRupiah(value));
        });

        // Unformat before submit
        $('form').on('submit', function() {
            let priceValue = $('#price').val();
            $('#price').val(unformatRupiah(priceValue));
        });
    });

    function formatRupiah(angka, prefix) {
        var number_string = angka.replace(/[^,\d]/g, '').toString(),
            split = number_string.split(','),
            sisa = split[0].length % 3,
            rupiah = split[0].substr(0, sisa),
            ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        return prefix == undefined ? rupiah : (rupiah ? rupiah : '');
    }

    function unformatRupiah(rupiah) {
        return rupiah.replace(/[^0-9]/g, '');
    }
</script>
@endpush