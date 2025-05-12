@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => $title,
    'secondSegment' => 'Edit',
])

@push('style')
    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/select2.css') }}">
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }} Edit Data</h4>

                <a href="{{ route($view . 'index') }}" class="btn btn-info">Back To List</a>

            </div>
            <div class="card-body col-md-12">
                <form class="row g-3" method="post" action="{{ route($view . 'update', $data->id) }}"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="fleetBrandCode">Company Name <i
                                    class="icofont icofont-warning-alt text-danger"></i></label>
                            <input class="form-control" name="name" id="name" type="text" readonly
                                placeholder="Company Name" value="{{ $data->name }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="plateNumber">Owner </label>
                            <input class="form-control" name="owner" id="owner" type="text" placeholder="Owner"
                                value="{{ $data->owner }}">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="email">Email</label>
                            <input class="form-control" name="email" id="email" type="email" placeholder="Email"
                                value="{{ $data->email }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="phone">Phone</label>
                            <input class="form-control" name="phone" id="phone" type="text" placeholder="Phone"
                                value="{{ $data->phone }}">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between">
                                <label class="form-label" for="logo">Logo</label>
                                @if (isset($data->logo))
                                    <a class="font-weight-bold text-primary"
                                        href="{{ url('storage/company_setting/logo', $data->logo) }}" target="_blank">See
                                        Image</a>
                                @endif
                            </div>
                            <input class="form-control" id="logo" name="logo" type="file"
                                accept=".jpg, .jpeg, .png" aria-label="file example">
                            <div class="invalid-feedback">Invalid form file selected</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="address">Address</label>
                            <textarea class="form-control" name="address" id="address" placeholder=" Address" rows="4">{{ $data->address }}</textarea>
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
@endpush
