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
                <h4>{{ $title }} Add Data</h4>

                <a href="{{ route($view . 'index') }}" class="btn btn-info">Back To List</a>

            </div>
            <div class="card-body col-md-6">
                <form class="row g-3" method="post" novalidate="" action="{{ route($view . 'store') }}">
                    @csrf
                    <div class="col-md-12">
                        <label class="form-label" for="name">Name <i
                                class="icofont icofont-warning-alt text-danger"></i></label>
                        <input class="form-control" name="name" id="name" type="text" required
                            placeholder="Name">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label" for="address">Address</label>
                        <textarea class="form-control" name="address" id="address" placeholder=" Address" rows="4"></textarea>
                    </div>

                    <div class="col-12">
                        <button class="btn btn-primary" type="submit">Add</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
