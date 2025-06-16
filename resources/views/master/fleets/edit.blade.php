@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => $title,
    'secondSegment' => __('general.edit'),
])

@push('style')
    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/select2.css') }}">

    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/custom-select2.css') }}">
@endpush

@section('content')
    <div class="col-sm-12">
        @include('partials.alert')
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }} {{ __('general.edit_data') }}</h4>

                <a href="{{ route($view . 'index') }}" class="btn btn-info">{{ __('general.back_to_list') }}</a>

            </div>
            <div class="card-body col-md-12">
                <form class="row g-3" method="post" action="{{ route($view . 'update', $data->id) }}"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label" for="plateNumber">{{ __('menu_fleet.plate_number') }}<i
                                    class="mdi mdi-information text-danger"></i></label>
                            <input class="form-control" name="plateNumber" id="plateNumber" type="text" required
                                placeholder="{{ __('menu_fleet.plate_number') }}" value="{{ $data->plateNumber }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="fleetTypeCode">{{ __('menu_fleet.type') }} </label>
                            <select class="js-example-basic-single" name="fleetTypeCode" id="fleetTypeCode">
                                <option value="">{{ __('general.choose') }}...</option>
                                @foreach ($type as $item)
                                    <option value="{{ $item->code }}"
                                        {{ $data->fleetTypeCode == $item->code ? 'selected' : '' }}>{{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="engineNumber">{{ __('menu_fleet.engine_number') }}</label>
                            <input class="form-control" name="engineNumber" id="engineNumber" type="text"
                                placeholder="{{ __('menu_fleet.engine_number') }}" value="{{ $data->engineNumber }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="frameNumber">{{ __('menu_fleet.frame_number') }}</label>
                            <input class="form-control" name="frameNumber" id="frameNumber" type="text"
                                placeholder="{{ __('menu_fleet.frame_number') }}" value="{{ $data->frameNumber }}">
                        </div>
                    </div>

                    <div class="row mt-4">

                        <div class="col-md-6">
                            <label class="form-label" for="year">{{ __('menu_fleet.year') }}</label>
                            <input class="form-control" name="year" id="year" type="number"
                                placeholder="{{ __('menu_fleet.year') }}" value="{{ $data->year }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="fleetBrandCode">{{ __('menu_fleet.brand') }}</label>
                            <select class="form-select" name="fleetBrandCode" id="fleetBrandCode">
                                <option value="">{{ __('general.choose') }}...</option>
                                @foreach ($brand as $item)
                                    <option value="{{ $item->code }}"
                                        {{ $data->fleetBrandCode == $item->code ? 'selected' : '' }}>{{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>



                    </div>

                    <div class="row mt-4">

                        <div class="col-md-6">
                            <div class="d-flex justify-content-between">
                                <label class="form-label" for="barcode">Barcode</label>
                                @if (isset($data->barcode))
                                    <a class="font-weight-bold text-primary"
                                        onclick="showModal('{{ url('storage/fleet/barcode', $data->barcode) }}', '{{ __('menu_fleet.barcode_image') }}')"
                                        href="#">{{ __('menu_fleet.see_image') }}</a>
                                @endif
                            </div>
                            <input class="form-control" id="barcode" name="barcode" type="file"
                                accept=".jpg, .jpeg, .png" aria-label="file example">
                            <div class="invalid-feedback">Invalid form file selected</div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex justify-content-between">
                                <label class="form-label"
                                    for="vehicleRegistrationNumber">{{ __('menu_fleet.vehicle_registration_number') }}</label>
                                @if (isset($data->vehicleRegistrationNumber))
                                    <a class="font-weight-bold text-primary"
                                        onclick="showModal('{{ url('storage/fleet/vehicleRegistrationNumber', $data->vehicleRegistrationNumber) }}', 'STNK Image')"
                                        href="#">{{ __('menu_fleet.see_image') }}</a>
                                @endif
                            </div>
                            <input class="form-control" name="vehicleRegistrationNumber" id="vehicleRegistrationNumber"
                                type="file" accept=".jpg, .jpeg, .png">

                        </div>



                    </div>

                    <div class="row mt-4">
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between align-items-center">

                                    <label class="form-label" for="fleetPicture">{{ __('menu_fleet.vehicle') }}</label>

                                    <button id="addInputFile" type="button"
                                        class="btn btn-sm btn-info  font-weight-bold mb-2" href="#"
                                        target="_blank"><i class="mdi mdi-plus"></i></button>
                                </div>

                                @forelse ($data->pictures as $item)
                                    <div class="d-flex justify-content-end">
                                        <a class="font-weight-bold text-primary float-right"
                                            onclick="showModal('{{ url('storage/fleet/fleetPicture', $item->fleetPicture) }}', 'Fleet Image')"
                                            href="#">{{ __('menu_fleet.see_image') }}</a>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center gap-1">
                                        <input class="form-control" name="fleetPicture[{{ $item->id }}]"
                                            data-id="{{ $item->id }}" type="file" accept=".jpg, .jpeg, .png">


                                        @if ($loop->iteration != 1)
                                            <button type="button" onclick="deleteFleetPicture('{{ $item->id }}')"
                                                class="btn btn-sm btn-danger mt-2">
                                                <i class="mdi mdi-delete"></i>
                                            </button>
                                        @endif


                                    </div>
                                @empty
                                    <input class="form-control" name="fleetPicture[empty]" id="fleetPicture"
                                        type="file" accept=".jpg, .jpeg, .png">
                                @endforelse



                                <div id="listInputFile">

                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="driverCode">{{ __('menu_fleet.driver') }} <i
                                        class="mdi mdi-information text-danger"></i></label>
                                <select class="js-example-basic-single" name="driverCode" id="driverCode" required>
                                    <option value="">{{ __('general.choose') }}...</option>
                                    @foreach ($driver as $item)
                                        <option value="{{ $item->code }}"
                                            {{ $item->code == $data->driverCode ? 'selected' : '' }}>{{ $item->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>


                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="barcodeNumber">{{ __('menu_fleet.barcode_number') }}</label>
                            <input class="form-control" name="barcodeNumber" id="barcodeNumber" type="text"
                                placeholder="{{ __('menu_fleet.barcode_number') }}" value="{{ $data->barcodeNumber }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label"
                                for="frameNumber">{{ __('menu_fleet.vehicle_registration_due_date') }}</label>
                            <input class="form-control"
                                placeholder="{{ __('menu_fleet.vehicle_registration_due_date') }}"
                                name="vehicleRegistrationDueDate" id="datetime-local" type="date"
                                value="{{ $data->vehicleRegistrationDueDate }}">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="fleetCompanyCode">{{ __('menu_fleet.company') }} <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <select class="js-example-basic-single" name="fleetCompanyCode" id="fleetCompanyCode"
                                required>
                                <option value="">{{ __('general.choose') }}...</option>
                                @foreach ($company as $item)
                                    <option value="{{ $item->code }}"
                                        {{ $item->code == $data->fleetCompanyCode ? 'selected' : '' }}>{{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-12">
                        <button class="btn btn-primary" type="submit">{{ __('general.edit') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-xl" style="padding-left: 100px">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modalTitle">{{ __('menu_fleet.image_data') }}</h4>
                        <button class="btn-close py-0" type="button" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="card">
                        <div class="card-body col-md-12">
                            <div class="row g-3">
                                <img id="modalImage" src="" alt="Image Preview" width="600px"
                                    height="600px" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="delete-form" method="post">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('script')
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src=" {{ asset('assets/js/select2/select2-custom.js') }}"></script>

    <script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>


    <script>
        function deleteFleetPicture(id) {
            var url = '{{ route('master.fleet-picture.destroy', ':id') }}'; // Use placeholder ':id'
            url = url.replace(':id', id); // Replace the placeholder with actual id

            $('#delete-form').attr('action', url);

            swal({
                title: "{{ __('general.are_you_sure') }}",
                text: "{{ __('general.want_to_delete_this_data') }}",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    $('#delete-form').submit();
                } else {
                    swal("{{ __('general.your_data_is_save') }}");
                }
            });
        }
    </script>

    <script>
        const addInputFile = document.getElementById("addInputFile");
        const listInputFile = document.getElementById("listInputFile");

        let inputFileCount = document.querySelectorAll("input[name^='fleetPicture']").length + 1;
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
            <input type="file" class="form-control mr-2" name="newFleetPicture[${num}]">
            <div>
                    <button type="button" onclick="removeFile(${num})" class="btn btn-sm btn-danger mt-2">
                        <i class="mdi mdi-delete"></i>
                    </button>
                </div>
            `
            return div;
        }

        function removeFile(num) {
            let content = document.querySelector(`.file${num}`);
            content.remove();
        }

        function showModal(imageUrl, title = 'Image Data') {
            document.getElementById('modalTitle').innerText = title;
            document.getElementById('modalImage').src = imageUrl; // Set the image source
            $('.bd-example-modal-xl').modal('show'); // Show the modal
        }
    </script>
@endpush
