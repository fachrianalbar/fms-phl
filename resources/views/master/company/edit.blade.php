@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => $title,
    'secondSegment' => __('general.edit'),
])

@section('content')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Edit {{ $title }}</h4>

                <a href="{{ route($view . 'index') }}" class="btn btn-info">{{ __('general.back_to_list') }}</a>

            </div>
            <div class="card-body col-md-6">
                <form class="row g-3" method="post" action="{{ route($view . 'update', $data->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="col-md-12">
                        <label class="form-label" for="name">Nama <i
                                class="mdi mdi-information text-danger"></i></label>
                        <input class="form-control" name="name" id="name" value="{{ $data->name }}" type="text"
                            required placeholder="Nama Perusahaan">
                    </div>

                    {{-- <div class="col-md-12">
                        <label class="form-label" for="format">Format <i
                                class="mdi mdi-information text-danger"></i></label>
                        <input class="form-control" name="format" id="format" value="{{ $data->format }}" type="text"
                            readonly required placeholder="Format (e.g: HW, WT)">
                    </div> --}}

                    <div class="col-12">
                        <button class="btn btn-primary" type="submit">Perbarui</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
