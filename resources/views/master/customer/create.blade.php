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
            <div class="card-body col-md-12">
                <form class="row g-3" method="post" action="{{ route($view . 'store') }}">
                    @csrf
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <label class="form-label" for="name">Name <i
                                    class="icofont icofont-warning-alt text-danger"></i></label>
                            <input class="form-control" name="name" id="name" type="text" required
                                placeholder="Name">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="picName">Pic Name</label>
                            <input class="form-control" name="picName" id="picName" type="text" placeholder="Pic Name">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="nickname">Nickname</label>
                            <input class="form-control" name="nickname" id="nickname" type="text"
                                placeholder="Nickname">
                        </div>
                    </div>


                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="email">Email <i
                                    class="icofont icofont-warning-alt text-danger"></i></label>
                            <input class="form-control" name="email" id="email" type="email" placeholder="Email"
                                required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="phone">Phone <i
                                    class="icofont icofont-warning-alt text-danger"></i></label>
                            <input class="form-control" name="phone" id="phone" type="number" required
                                placeholder="Phone">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="address1">Address 1</label>
                            <textarea class="form-control" name="address1" id="address1" placeholder=" Address 1" rows="4"></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="address2">Address 2</label>
                            <textarea class="form-control" name="address2" id="address2" placeholder=" Address 2" rows="4"></textarea>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="npwp">Npwp</label>
                            <input class="form-control" name="npwp" id="npwp" type="text" placeholder="Npwp">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="accountNumber"> Account Number</label>
                            <input class="form-control" name="accountNumber" id="accountNumber" type="number"
                                placeholder="Account Number">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="ppn">Ppn</label>
                            <input class="form-control" name="ppn" id="ppn" type="number" placeholder="Ppn">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="pph"> Pph</label>
                            <input class="form-control" name="pph" id="pph" type="number" placeholder="Pph">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="telegramUsername">Telegram Username</label>
                            <input class="form-control" name="telegramUsername" id="telegramUsername" type="text"
                                placeholder="Telegram Username">
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
