@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => $title,
    'secondSegment' => 'Edit',
])

@push('style')
    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/select2.css') }}">

    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/custom-select2.css') }}">
    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/sweetalert2.css') }} ">
    <style>
        .swal-text {
            text-align: center;
        }

        .swal-title {
            text-align: center;
        }
    </style>
@endpush

@section('content')
    @include('partials.alert')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }} Edit Data</h4>

                <a href="{{ route($view . 'index') }}" class="btn btn-info">{{ __('general.back_to_list') }}</a>

            </div>
            <div class="card-body col-md-6">
                <form id="editForm" class="row g-3" method="post" action="{{ route($view . 'update', $data->id) }}"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="col-md-12">
                        <label class="form-label" for="userCode">User <i
                                class="icofont icofont-warning-alt text-danger"></i></label>
                        <select class="js-example-basic-single" name="userCode" id="userCode" required>
                            <option value="{{ $data->code }}">
                                {{ $data->username . ' - ' . $data->role->name }}
                            </option>
                        </select>
                    </div>

                    <div class="col-md-12">
                        <div class="d-flex justify-content-between align-items-center">

                            <label class="form-label" for="userBankCode">User Bank <i
                                    class="mdi mdi-information text-danger"></i></label>


                            <button id="addInputFile" type="button" class="btn btn-sm btn-info  font-weight-bold mb-2"
                                href="#" target="_blank"><i class="fa fa-plus"></i></button>
                        </div>


                        @foreach ($data->configBank as $item)
                            <div class="mb-4 d-flex justify-content-center align-items-center gap-3">
                                <input type="text" class="form-control" readonly
                                    value="{{ $item->userBank->accountNumber . ' - ' . $item->userBank->bank->name . ' - ' . $item->userBank->accountName }}">
                                @if (count($data->configBank) > 1)
                                    <button type="button" onclick="removeConfigBank('{{ $item->id }}')"
                                        class="btn btn-sm btn-danger mt-2">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                        @endforeach

                        <div id="listInputFile">

                        </div>

                    </div>

                    <div class="col-12">
                        <button class="btn btn-primary" type="submit">Edit</button>
                    </div>
                </form>
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

    <script src=" {{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>


    <script>
        const addInputFile = document.getElementById("addInputFile");
        const listInputFile = document.getElementById("listInputFile");

        let inputDataCount = document.querySelectorAll("select[name^='userBankCode']").length + 1;
        addInputFile.addEventListener('click', () => {
            addFile(inputDataCount); // Cuma panggil aja
            inputDataCount++;
        })

        function addFile(num) {
            $.get("{{ url('ajax/list-user-bank') }}", function(data) {
                const div = document.createElement('div');
                div.classList.add('d-flex', 'form-group', 'justify-content-center', 'align-items-center', 'mx-auto',
                    'gap-3', 'my-3', `file${num}`);

                let selectHTML = `
            <select class="js-example-basic-single" name="userBankCode[${num}]" required>
                <option value="">Choose...</option>
        `;

                data.forEach(function(item) {
                    selectHTML += `
                <option value="${item.code}">
                    ${item.accountNumber} - ${item.bank.name} - ${item.accountName}
                </option>
            `;
                });

                selectHTML += `</select>`;

                div.innerHTML = `
            ${selectHTML}
            <div>
                <button type="button" onclick="removeFile(${num})" class="btn btn-sm btn-danger mt-2">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        `;

                // ⬇️ Baru di sini append setelah semuanya siap
                listInputFile.appendChild(div);

                // ⬇️ Reinit select2 supaya select baru berubah
                $(div).find('.js-example-basic-single').select2();
            });
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

        function removeConfigBank(id) {
            var url = '{{ route('bank.config-bank.index') }}/' + id;
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
                    swal("Your data is safe!");
                }
            });
        }

        const form = document.querySelector('#editForm');


        form.addEventListener('submit', function(e) {
            // Cari semua select yang userBankCode
            const selects = document.querySelectorAll('select[name^="userBankCode"]');
            const selectedValues = [];

            let duplicateFound = false;

            selects.forEach(select => {
                if (selectedValues.includes(select.value)) {
                    duplicateFound = true;
                }
                selectedValues.push(select.value);
            });

            if (duplicateFound) {
                e.preventDefault(); // stop form submit
                swal({
                    title: "Warning",
                    text: "There are duplicate user banks selected. Please choose different banks.",
                    icon: "warning",
                })
                return
            }
        });
    </script>
@endpush
