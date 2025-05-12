@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => $title,
    'secondSegment' => 'Add',
])

@push('style')
    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/select2.css') }}">
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }} Add Data</h4>

                <a href="{{ route($view . 'index') }}" class="btn btn-info">Back To List</a>

            </div>
            <div class="card-body col-md-12">
                <form class="row g-3" method="post" action="{{ route($view . 'store') }}" enctype="multipart/form-data"
                    onsubmit="return submitForm('balance')">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label" for="bankCode">Bank Name <i
                                    class="icofont icofont-warning-alt text-danger"></i></label>
                            <select class="js-example-basic-single" name="bankCode" id="bankCode" required>
                                <option value="">Choose...</option>
                                @foreach ($bank as $item)
                                    <option value="{{ $item->code }}">{{ $item->bankCode . ' - ' . $item->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="accountNumber">Account Number <i
                                    class="icofont icofont-warning-alt text-danger"></i></label>
                            <input class="form-control" name="accountNumber" id="accountNumber" type="text" required
                                placeholder="Account Number">
                        </div>

                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="accountName">Account Name <i
                                    class="icofont icofont-warning-alt text-danger"></i></label>
                            <input class="form-control" name="accountName" id="accountName" type="text" required
                                placeholder="Account Name">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="type">Type <i
                                    class="icofont icofont-warning-alt text-danger"></i></label>
                            <select class="js-example-basic-single" name="type" id="type" required>
                                <option value="">Choose...</option>
                                <option value=1>Person</option>
                                <option value=2>Company</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="balance">Balance <i
                                    class="icofont icofont-warning-alt text-danger"></i></label>
                            <input class="form-control" name="balance" id="balance" type="text" required
                                oninput="formatAngka(this)" placeholder="Balance">
                        </div>
                    </div>


                    <div class="col-12">
                        <button class="btn btn-primary" type="submit">Add</button>
                    </div>
            </div>
            </form>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src=" {{ asset('assets/js/select2/select2-custom.js') }}"></script>
    <script src=" {{ asset('assets/js/helper.js') }}"></script>
@endpush
