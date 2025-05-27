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
            <div class="card-body col-md-12">
                <form class="row g-3" method="post" novalidate="" action="{{ route($view . 'update', $data->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="codes">Code <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <input class="form-control" name="code" id="code" type="text" required
                                placeholder="Code" value="{{ $data->code }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="name">Name <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <input class="form-control" name="name" id="name" type="text" required
                                value="{{ $data->name }}" placeholder="Name">
                        </div>
                    </div>


                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="pic">PIC</label>
                            <input class="form-control" name="pic" id="pic" type="text" required
                                placeholder="PIC" value="{{ $data->pic }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="address">Address</label>
                            <input class="form-control" name="address" id="address" type="text" placeholder="Address"
                                value="{{ $data->address }}">
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
                            <label class="form-label" for="ppn">PPN</label>
                            <input class="form-control" name="ppn" id="ppn" type="number" placeholder="PPN"
                                value="{{ $data->ppn }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="pph">PPH</label>
                            <input class="form-control" name="pph" id="pph" type="number" placeholder="PPH"
                                value="{{ $data->pph }}">
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
