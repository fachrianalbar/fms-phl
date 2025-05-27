@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => $title,
    'secondSegment' => __('general.add'),
])

@push('style')
    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/select2.css') }}">

    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/custom-select2.css') }}">
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }} {{ __('general.add_data') }}</h4>

                <a href="{{ route($view . 'index') }}" class="btn btn-info">{{ __('general.back_to_list') }}</a>

            </div>
            <div class="card-body col-md-12">
                <form class="row g-3" method="post" action="{{ route($view . 'store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label" for="plateNumber">Plate Number <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <input class="form-control" name="plateNumber" id="plateNumber" type="text"
                                placeholder="Plate Number">
                        </div>


                        <div class="col-md-6">
                            <label class="form-label" for="fleetTypeCode">Type <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <select class="js-example-basic-single" name="fleetTypeCode" id="fleetTypeCode" required>
                                <option value="">{{ __('general.choose') }}...</option>
                                @foreach ($type as $item)
                                    <option value="{{ $item->code }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="engineNumber">Engine Number</label>
                            <input class="form-control" name="engineNumber" id="engineNumber" type="text"
                                placeholder="Engine Number">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="frameNumber">Frame Number</label>
                            <input class="form-control" name="frameNumber" id="frameNumber" type="text"
                                placeholder="Frame Number">
                        </div>
                    </div>

                    <div class="row mt-4">

                        <div class="col-md-6">
                            <label class="form-label" for="year">Year</label>
                            <input class="form-control" name="year" id="year" type="number" placeholder="Year">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="fleetBrandCode">Brand</label>
                            <select class="js-example-basic-single" name="fleetBrandCode" id="fleetBrandCode">
                                <option value="">{{ __('general.choose') }}...</option>
                                @foreach ($brand as $item)
                                    <option value="{{ $item->code }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>



                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="barcode">Barcode</label>
                            <input class="form-control file" id="barcode" name="barcode" type="file"
                                accept=".jpg, .jpeg, .png" aria-label="file example">
                            <div class="invalid-feedback">Invalid form file selected</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="vehicleRegistrationNumber">Vehicle Registration Number
                                (STNK)</label>
                            <input class="form-control" name="vehicleRegistrationNumber" id="vehicleRegistrationNumber"
                                type="file" accept=".jpg, .jpeg, .png">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center">

                                <label class="form-label" for="fleetPicture">Vehicle</label>

                                <button id="addInputFile" type="button"
                                    class="btn btn-icon btn-sm btn-sm btn-info font-weight-bold mb-2" href="#"
                                    target="_blank">
                                    <i class="mdi mdi-plus fs-14 text-white"></i>
                                </button>
                            </div>
                            <input class="form-control" name="fleetPicture[0]" id="fleetPicture" type="file"
                                accept=".jpg, .jpeg, .png">

                            <div id="listInputFile">

                            </div>
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

    <script>
        const addInputFile = document.getElementById("addInputFile");
        const listInputFile = document.getElementById("listInputFile");

        let inputFileCount = 1;
        addInputFile.addEventListener('click', () => {
            listInputFile.appendChild(addFile(inputFileCount));
            inputFileCount++;
        })

        function addFile(num) {
            const div = document.createElement('div');
            div.classList.add('d-flex', 'form-group', 'justify-content-center', 'align-items-center', 'mx-auto', 'gap-3',
                'my-3',
                `file${num}`)
            div.innerHTML = `
            <input type="file" class="form-control mr-2" name="fleetPicture[${num}]">
            <div>
                    <button type="button" onclick="removeFile(${num})" class="btn btn-sm btn-icon btn-danger mt-2">
                        <i class="mdi mdi-delete fs-14 text-white"></i>
                    </button>
                </div>
            `
            return div;
        }

        function removeFile(num) {
            let content = document.querySelector(`.file${num}`);
            content.remove();
        }
    </script>
@endpush
