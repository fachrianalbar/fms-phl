@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => $title,
    'secondSegment' => __('general.add'),
])

@section('content')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }} {{ __('general.add_data') }}</h4>

                <a href="{{ route($view . 'index') }}" class="btn btn-info">Back To List</a>

            </div>
            <div class="card-body col-md-12">
                <form class="row g-3" method="post" novalidate="" action="{{ route($view . 'store') }}">
                    @csrf

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="codes">Code <i
                                    class="icofont icofont-warning-alt text-danger"></i></label>
                            <input class="form-control" name="code" id="code" type="text" required
                                placeholder="Code">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="name">Name <i
                                    class="icofont icofont-warning-alt text-danger"></i></label>
                            <input class="form-control" name="name" id="name" type="text" required
                                placeholder="Name">
                        </div>

                    </div>


                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="pic">PIC</label>
                            <input class="form-control" name="pic" id="pic" type="text" required
                                placeholder="PIC">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="address">Address</label>
                            <input class="form-control" name="address" id="address" type="text" placeholder="Address">
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="email">Email</label>
                            <input class="form-control" name="email" id="email" type="email" placeholder="Email">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="phone">Phone</label>
                            <input class="form-control" name="phone" id="phone" type="text" placeholder="Phone">
                        </div>
                    </div>

                    <div class="row mt-4">

                        <div class="col-md-6">
                            <label class="form-label" for="ppn">PPN</label>
                            <input class="form-control" name="ppn" id="ppn" type="number" placeholder="PPN">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="pph">PPH</label>
                            <input class="form-control" name="pph" id="pph" type="number" placeholder="PPH">
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
