@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => $title,
    'secondSegment' => 'Add',
])

@push('style')
    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/select2.css') }}">

    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/custom-select2.css') }}">
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }} Add Data</h4>

                <a href="{{ route($view . 'index') }}" class="btn btn-info">Back To List</a>

            </div>
            <div class="card-body col-md-6">
                <form class="row g-3" method="post" novalidate="" action="{{ route($view . 'store') }}">
                    @csrf
                    <div class="col-md-12">
                        <label class="form-label" for="bankName">Bank Name <i
                                class="icofont icofont-warning-alt text-danger"></i></label>
                        <input class="form-control" name="bankName" id="bankName" type="text" required
                            placeholder="Bank Name">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label" for="accountNumber">Account Number <i
                                class="icofont icofont-warning-alt text-danger"></i></label>
                        <input class="form-control" name="accountNumber" id="accountNumber" type="text" required
                            placeholder="Account Number">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label" for="userCode">User <i
                                class="icofont icofont-warning-alt text-danger"></i></label>
                        <select class="js-example-basic-single" name="userCode" id="userCode" required>
                            <option selected="" disabled="" value="">Choose...</option>
                            @foreach ($user as $item)
                                <option value="{{ $item->code }}">{{ $item->name . ' - ' . $item->role->name }}
                                </option>
                            @endforeach
                        </select>
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
@endpush
