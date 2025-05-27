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
                <h4>{{ $title }} {{ __('general.edit_data') }}</h4>

                <a href="{{ route($view . 'index') }}" class="btn btn-info">{{ __('general.back_to_list') }}</a>

            </div>
            <div class="card-body col-md-6">
                <form class="row g-3" method="post" action="{{ route($view . 'update', $data->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="col-md-12">
                        <label class="form-label" for="bankName">Bank Name <i
                                class="icofont icofont-warning-alt text-danger"></i></label>
                        <input class="form-control" name="bankName" id="bankName" type="text" required
                            placeholder="Bank Name" value="{{ $data->bankName }}">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label" for="accountNumber">Account Number <i
                                class="icofont icofont-warning-alt text-danger"></i></label>
                        <input class="form-control" name="accountNumber" id="accountNumber" type="text" required
                            placeholder="Account Number" value="{{ $data->accountNumber }}">
                    </div>

                    <div class="col-12">
                        <button class="btn btn-primary" type="submit">Edit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
