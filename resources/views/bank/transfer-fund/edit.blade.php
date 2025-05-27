@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => $title,
    'secondSegment' => __('general.edit'),
])


@push('style')
    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/select2.css') }}">

    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/custom-select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }} {{ __('general.edit_data') }}</h4>

                <a href="{{ route($view . 'index') }}" class="btn btn-info">{{ __('general.back_to_list') }}</a>

            </div>
            <div class="card-body col-md-12">
                <form class="row g-3" method="post" action="{{ route($view . 'update', $data->id) }}"
                    onsubmit="return submitForm('nominal')">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label" for="name">Code <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <input class="form-control" type="text" required placeholder="Name" readonly disabled
                                value="{{ $data->code }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="name">Date <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <input class="form-control" name="date" id="datetime-local" type="date" required
                                placeholder="Order Date" value="{{ $data->date }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Time</label>
                            <input class="form-control digits" name="time" type="time" value="{{ $data->time }}">
                        </div>
                    </div>



                    <div class="col-md-6">
                        <label class="form-label" for="nominal">Nominal <i
                                class="icofont icofont-warning-alt text-danger"></i></label>
                        <input class="form-control" name="nominal" id="nominal" type="text" placeholder="Nominal"
                            oninput="formatAngka(this)" value="{{ $data->nominal }}">
                    </div>
            </div>



            <div class="row mt-4">


                <div class="col-md-12">
                    <label class="form-label" for="description">Deskripsi / Notes <i
                            class="icofont icofont-warning-alt text-danger"></i></label>
                    <textarea class="form-control" name="description" id="description" rows="3">{{ $data->description }}</textarea>
                </div>


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
    <script src="{{ asset('assets/js/flat-pickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/custom-flatpickr.js') }}"></script>
    <script src=" {{ asset('assets/js/helper.js') }}"></script>

    <script>
        $(document).ready(function() {
            const transferType = $('#transferType').val()

            if (transferType == 'Transfer') {
                $('#bank').removeClass('d-none');
                // $('#bankSender').attr('required', true);
                $('#bankReceiver').attr('required', true);


            } else if (transferType == 'Cash') {
                $('#bank').addClass('d-none');
                $('#bankSender').removeAttr('required');
                $('#bankReceiver').removeAttr('required');
            }
        });
    </script>
@endpush
