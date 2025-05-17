@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => $title,
    'secondSegment' => 'Add',
])

@section('content')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }} {{ __('general.add_data') }}</h4>

                <a href="{{ route($view . 'index') }}" class="btn btn-info">Back To List</a>

            </div>
            <div class="card-body col-md-6">
                <form class="row g-3" method="post" action="{{ route($view . 'store') }}"
                    onsubmit="return submitForm('value')">
                    @csrf
                    <div class="col-md-12">
                        <label class="form-label" for="min">Min</label>
                        <input class="form-control" name="min" id="min" step="any" type="number" required
                            placeholder="Min">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label" for="max">Max</label>
                        <input class="form-control" name="max" id="max" step="any" type="number" required
                            placeholder="Max">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label" for="value">Value</label>
                        <input class="form-control" name="value" id="value" type="text" oninput="formatAngka(this)"
                            required placeholder="Value">
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
    <script src=" {{ asset('assets/js/helper.js') }}"></script>
@endpush
