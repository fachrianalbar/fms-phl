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
                            <label class="form-label" for="plateNumber">{{ __('menu_fleet.plate_number') }} <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <input class="form-control" name="plateNumber" id="plateNumber" type="text"
                                placeholder="{{ __('menu_fleet.plate_number') }}">
                        </div>


                        <div class="col-md-6">
                            <label class="form-label" for="fleetTypeCode">{{ __('menu_fleet.type') }}</label>
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
                            <label class="form-label" for="engineNumber">{{ __('menu_fleet.engine_number') }}</label>
                            <input class="form-control" name="engineNumber" id="engineNumber" type="text"
                                placeholder="{{ __('menu_fleet.engine_number') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="frameNumber">{{ __('menu_fleet.frame_number') }}</label>
                            <input class="form-control" name="frameNumber" id="frameNumber" type="text"
                                placeholder="{{ __('menu_fleet.frame_number') }}">
                        </div>
                    </div>

                    <div class="row mt-4">

                        <div class="col-md-6">
                            <label class="form-label" for="year">{{ __('menu_fleet.year') }}</label>
                            <input class="form-control" name="year" id="year" type="number"
                                placeholder="{{ __('menu_fleet.year') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="fleetBrandCode">{{ __('menu_fleet.brand') }}</label>
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
                            <label class="form-label"
                                for="vehicleRegistrationNumber">{{ __('menu_fleet.vehicle_registration_number') }}</label>
                            <input class="form-control" name="vehicleRegistrationNumber" id="vehicleRegistrationNumber"
                                type="file" accept=".jpg, .jpeg, .png">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center">

                                <label class="form-label" for="fleetPicture">{{ __('menu_fleet.vehicle') }}</label>

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


                        <div class="col-md-6">
                            <label class="form-label" for="driverCode">{{ __('menu_fleet.driver') }} <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <select class="js-example-basic-single" name="driverCode" id="driverCode" required>
                                <option value="">{{ __('general.choose') }}...</option>
                                @foreach ($driver as $item)
                                    <option value="{{ $item->code }}">{{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mt-4">

                        <div class="col-md-6">
                            <label class="form-label" for="vehicleTax">{{ __('menu_fleet.vehicle_tax') }}</label>
                            <input class="form-control" placeholder="{{ __('menu_fleet.vehicle_tax') }}"
                                name="vehicleTax" id="datetime-local" type="date">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label"
                                for="frameNumber">{{ __('menu_fleet.vehicle_registration_due_date') }}</label>
                            <input class="form-control"
                                placeholder="{{ __('menu_fleet.vehicle_registration_due_date') }}"
                                name="vehicleRegistrationDueDate" id="datetime-local" type="date">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="vehicleKir">{{ __('menu_fleet.vehicle_kir') }}</label>
                            <input class="form-control" placeholder="{{ __('menu_fleet.vehicle_kir') }}"
                                name="vehicleKir" id="datetime-local" type="date">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="fleetCompanyCode">{{ __('menu_fleet.company') }} <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <select class="js-example-basic-single" name="fleetCompanyCode" id="fleetCompanyCode"
                                required>
                                <option value="">{{ __('general.choose') }}...</option>
                                @foreach ($company as $item)
                                    <option value="{{ $item->code }}">{{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-12">
                        <button class="btn btn-primary" type="submit">{{ __('general.add') }}</button>
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
