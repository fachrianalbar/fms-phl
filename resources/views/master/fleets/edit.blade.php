@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => $title,
    'secondSegment' => 'Edit',
])

@push('style')
    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/sweetalert2.css') }} ">
@endpush

@section('content')
    <div class="col-sm-12">
        @include('partials.alert')
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }} Edit Data</h4>

                <a href="{{ route($view . 'index') }}" class="btn btn-info">{{ __('general.back_to_list') }}</a>

            </div>
            <div class="card-body col-md-12">
                <form class="row g-3" method="post" action="{{ route($view . 'update', $data->id) }}"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label" for="plateNumber">Plate Number <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <input class="form-control" name="plateNumber" id="plateNumber" type="text" required
                                placeholder="Plate Number" value="{{ $data->plateNumber }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="fleetTypeCode">Type <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <select class="form-select" name="fleetTypeCode" id="fleetTypeCode">
                                <option value="">Choose...</option>
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
                            <label class="form-label" for="engineNumber">Engine Number</label>
                            <input class="form-control" name="engineNumber" id="engineNumber" type="text"
                                placeholder="Engine Number" value="{{ $data->engineNumber }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="frameNumber">Frame Number</label>
                            <input class="form-control" name="frameNumber" id="frameNumber" type="text"
                                placeholder="Frame Number" value="{{ $data->frameNumber }}">
                        </div>
                    </div>

                    <div class="row mt-4">

                        <div class="col-md-6">
                            <label class="form-label" for="year">Year</label>
                            <input class="form-control" name="year" id="year" type="number" placeholder="Year"
                                value="{{ $data->year }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="fleetBrandCode">Brand</label>
                            <select class="form-select" name="fleetBrandCode" id="fleetBrandCode">
                                <option value="">Choose...</option>
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
                                        onclick="showModal('{{ url('storage/fleet/barcode', $data->barcode) }}', 'Barcode Image')"
                                        href="#">See
                                        Image</a>
                                @endif
                            </div>
                            <input class="form-control" id="barcode" name="barcode" type="file"
                                accept=".jpg, .jpeg, .png" aria-label="file example">
                            <div class="invalid-feedback">Invalid form file selected</div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex justify-content-between">
                                <label class="form-label" for="vehicleRegistrationNumber">Vehicle Registration
                                    Number</label>
                                @if (isset($data->vehicleRegistrationNumber))
                                    <a class="font-weight-bold text-primary"
                                        onclick="showModal('{{ url('storage/fleet/vehicleRegistrationNumber', $data->vehicleRegistrationNumber) }}', 'STNK Image')"
                                        href="#">See
                                        Image</a>
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

                                    <label class="form-label" for="fleetPicture">Vehicle</label>

                                    <button id="addInputFile" type="button"
                                        class="btn btn-sm btn-info  font-weight-bold mb-2" href="#"
                                        target="_blank"><i class="fa fa-plus"></i></button>
                                </div>

                                @forelse ($data->pictures as $item)
                                    <div class="d-flex justify-content-end">
                                        <a class="font-weight-bold text-primary float-right"
                                            onclick="showModal('{{ url('storage/fleet/fleetPicture', $item->fleetPicture) }}', 'Fleet Image')"
                                            href="#">See
                                            Image</a>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center gap-1">
                                        <input class="form-control" name="fleetPicture[{{ $item->id }}]"
                                            data-id="{{ $item->id }}" type="file" accept=".jpg, .jpeg, .png">


                                        @if ($loop->iteration != 1)
                                            <button type="button" onclick="deleteFleetPicture('{{ $item->id }}')"
                                                class="btn btn-sm btn-danger mt-2">
                                                <i class="fa fa-trash"></i>
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
                        </div>
                    </div>

                    <div class="col-12">
                        <button class="btn btn-primary" type="submit">Edit</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-xl" style="padding-left: 100px">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modalTitle">Image Data</h4>
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
                title: "Are you sure?",
                text: "Want to delete this data?",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    $('#delete-form').submit();
                } else {
                    swal("Your data is safe!");
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
                        <i class="fa fa-trash"></i>
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
