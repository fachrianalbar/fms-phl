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
                <form class="row g-3" method="post" action="{{ route($view . 'store') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label" for="name">Name</label>
                            <input class="form-control" name="name" id="name" type="text" required
                                placeholder="Name">
                        </div>

                        <div class="col-md-6 position-relative">
                            <label class="form-label" for="locationCode">Location Name</label>
                            <select class="js-example-basic-single" name="locationCode" id="locationCode" required="">
                                <option selected="" disabled="" value="">Choose...</option>
                                @foreach ($location as $item)
                                    <option value="{{ $item->code }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6 position-relative">
                            <label class="form-label" for="description">Description</label>
                            <textarea class="form-control" name="description" id="description" placeholder="Description" rows="4" required></textarea>

                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="address">Address</label>
                            <textarea class="form-control" name="address" id="address" placeholder=" Address" rows="4" required></textarea>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="latitude">Latitude</label>
                            <input class="form-control" name="latitude" id="latitude" type="text" required
                                placeholder="Latitude">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="longitude">longitude</label>
                            <input class="form-control" name="longitude" id="longitude" type="number" required
                                placeholder="longitude">
                        </div>
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
