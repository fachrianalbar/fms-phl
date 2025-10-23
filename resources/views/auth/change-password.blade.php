@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => $title,
    'secondSegment' => __('general.add'),
])

@section('content')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header ">
                {{-- <h4>{{ $title }} </h4> --}}

                {{-- <a href="{{ route($view . 'index') }}" class="btn btn-info">{{ __('general.back_to_list') }}</a> --}}

                @include('partials.alert')


            </div>
            <div class="card-body col-md-6">
                <form class="row g-3" method="post" action="{{ route('change-password.store') }}">
                    @csrf
                    <div class="col-md-12">
                        <label class="form-label" for="old_password">{{ __('change_password.old_password') }} <i
                                class="icofont icofont-warning-alt text-danger"></i></label>
                        <input class="form-control" name="old_password" id="old_password" type="password" required
                            placeholder="{{ __('change_password.old_password') }}">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label" for="new_password">{{ __('change_password.new_password') }} <i
                                class="icofont icofont-warning-alt text-danger"></i></label>
                        <input class="form-control" name="new_password" id="new_password" type="password" required
                            placeholder="{{ __('change_password.new_password') }}">
                    </div>


                    <div class="col-md-12">
                        <label class="form-label"
                            for="new_password_confirmation">{{ __('change_password.confirm_new_password') }} <i
                                class="icofont icofont-warning-alt text-danger"></i></label>
                        <input class="form-control" name="new_password_confirmation" id="new_password_confirmation"
                            type="password" required placeholder="{{ __('change_password.confirm_new_password') }}">
                    </div>

                    <div class="col-12">
                        <button class="btn btn-primary" type="submit">{{ __('general.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
