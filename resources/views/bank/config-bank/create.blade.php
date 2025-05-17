@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => $title,
    'secondSegment' => 'Add',
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

                <a href="{{ route($view . 'index') }}" class="btn btn-info">Back To List</a>

            </div>
            <div class="card-body col-md-6">
                <form class="row g-3" method="post" action="{{ route($view . 'store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="col-md-12">
                        <label class="form-label" for="userCode">User <i
                                class="icofont icofont-warning-alt text-danger"></i></label>
                        <select class="js-example-basic-single" name="userCode" id="userCode" required>
                            <option value="">Choose...</option>
                            @foreach ($user as $item)
                                <option value="{{ $item->code }}">{{ $item->username . ' - ' . $item->role->name }}
                                </option>
                            @endforeach
                        </select>

                    </div>




                    <div class="col-md-12">
                        <div class="d-flex justify-content-between align-items-center">

                            <label class="form-label" for="userBankCode">User Bank <i
                                    class="icofont icofont-warning-alt text-danger"></i></label>
                            <button id="addUserBank" type="button" class="btn btn-sm btn-info  font-weight-bold mb-2"
                                href="#" target="_blank"><i class="fa fa-plus"></i></button>
                        </div>

                        <select class="js-example-basic-single" name="userBankCode[0]" id="userBankCode" required>
                            <option value="">Choose...</option>
                            @foreach ($userBank as $item)
                                <option value="{{ $item->code }}">
                                    {{ $item->accountNumber . ' - ' . $item->bank->name . ' - ' . $item->accountName }}
                                </option>
                            @endforeach
                        </select>

                        <div id="listUserBank">

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
        const addUserBank = document.getElementById("addUserBank");
        const listUserBank = document.getElementById("listUserBank");
        const userBankSelect = document.getElementById("userBankCode");

        let userBankCount = 1;

        addUserBank.addEventListener('click', () => {
            const newSelect = addUserBankSelect(userBankCount);
            listUserBank.appendChild(newSelect);
            userBankCount++;

            // Inisialisasi ulang Select2 agar tampilan tetap konsisten
            $('.js-example-basic-single').select2();
        });

        function addUserBankSelect(num) {
            const div = document.createElement('div');
            div.classList.add('d-flex', 'form-group', 'justify-content-center', 'align-items-center', 'mx-auto', 'gap-3',
                'my-3',
                `userBank${num}`);

            div.innerHTML = `
                <select class="js-example-basic-single form-control mr-2" required name="userBankCode[${num}]">
                    ${userBankSelect.innerHTML} 
                </select>
                <div>
                    <button type="button" onclick="removeUserBank(${num})" class="btn btn-sm btn-danger mt-2">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
            `;
            return div;
        }

        function removeUserBank(num) {
            let content = document.querySelector(`.userBank${num}`);
            if (content) {
                content.remove();
            }
        }

        // Inisialisasi Select2 saat halaman dimuat
        $(document).ready(function() {
            $('.js-example-basic-single').select2();
        });
    </script>
@endpush
