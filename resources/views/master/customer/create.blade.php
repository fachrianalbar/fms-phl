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
                <form class="row g-3" method="post" action="{{ route($view . 'store') }}">
                    @csrf
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <label class="form-label" for="code">Code <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <input class="form-control" name="code" id="code" type="text" required
                                placeholder="Code">
                        </div>


                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="name">{{ __('menu_customer.name') }} <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <input class="form-control" name="name" id="name" type="text" required
                                placeholder="{{ __('menu_customer.name') }}">
                        </div>
                        {{-- <div class="col-md-6">
                            <label class="form-label" for="nickname">{{ __('menu_customer.nickname') }}</label>
                            <input class="form-control" name="nickname" id="nickname" type="text"
                                placeholder="{{ __('menu_customer.nickname') }}">
                        </div> --}}

                        <div class="col-md-6">
                            <label class="form-label" for="email">Email</label>
                            <input class="form-control" name="email" id="email" type="email" placeholder="Email">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="officeAddress"> {{ __('menu_customer.office_address') }} </label>
                            <textarea class="form-control" name="officeAddress" id="officeAddress"
                                placeholder=" {{ __('menu_customer.office_address') }}" rows="4"></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label"
                                for="billingAddress">{{ __('menu_customer.billing_address') }}</label>
                            <textarea class="form-control" name="billingAddress" id="billingAddress"
                                placeholder=" {{ __('menu_customer.billing_address') }}" rows="4"></textarea>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="npwp">Npwp</label>
                            <input class="form-control" name="npwp" id="npwp" type="text" placeholder="Npwp">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="accountNumber">
                                {{ __('menu_customer.account_number') }}</label>
                            <input class="form-control" name="accountNumber" id="accountNumber" type="number"
                                placeholder="{{ __('menu_customer.account_number') }}">
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

                    {{-- <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="telegramUsername">Telegram Username</label>
                            <input class="form-control" name="telegramUsername" id="telegramUsername" type="text"
                                placeholder="Telegram Username">
                        </div>
                    </div> --}}

                    <div class="row mt-4">
                        <div class="col-md-6 position-relative">
                            <label class="form-label" for="companyCode"> {{ __('menu_customer.company') }} <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <select class="js-example-basic-single" name="companyCode" id="companyCode" required="">
                                <option selected="" disabled="" value="">
                                    {{ __('general.choose') }}...</option>
                                @foreach ($company as $item)
                                    <option value="{{ $item->code }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="due_date_duration">
                                {{ __('menu_customer.due_date_duration') }}
                                ({{ __('menu_customer.days') }})</label>
                            <input class="form-control" name="due_date_duration" id="due_date_duration" type="number"
                                min="1"
                                placeholder="{{ __('menu_customer.due_date_duration') }} ({{ __('menu_customer.days') }})">
                        </div>


                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6 position-relative">
                            <label class="form-label" for="type"> {{ __('menu_customer.type') }} <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <select class="js-example-basic-single" name="type" id="type" required="">
                                <option selected="" disabled="" value="">
                                    {{ __('general.choose') }}...</option>
                                <option value="Individual">
                                    {{ __('menu_customer.person') }}</option>
                                <option value="Company">
                                    {{ __('menu_customer.company') }}</option>

                            </select>
                        </div>

                        <div class="col-md-6 position-relative">
                            <label class="form-label" for="type"> {{ __('menu_customer.is_do') }} <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <select class="js-example-basic-single" name="type" id="type" required="">
                                <option selected="" disabled="" value="">
                                    {{ __('general.choose') }}...</option>
                                <option value=1>
                                    {{ __('general.yes') }}</option>
                                <option value=0>
                                    {{ __('general.no') }}</option>

                            </select>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="d-flex justify-content-between col-md-6">
                            <label class="form-label" for="picName">{{ __('menu_customer.pic_data') }}</label>

                            <button id="addInputFile" type="button" class="btn btn-sm btn-info mb-2  font-weight-bold"
                                href="#" target="_blank"><i class="mdi mdi-plus"></i></button>

                        </div>


                        <div>
                            <div class="row g-1">
                                <div class="col-md-3">
                                    <input class="form-control" id="picName" type="text"
                                        placeholder="{{ __('menu_customer.pic_name') }}" name="picName[0]">
                                </div>

                                <div class="col-md-3">
                                    <input class="form-control" id="phone" type="number" placeholder="Phone"
                                        name="phone[0]">
                                </div>
                            </div>
                        </div>



                        <div id="listInputFile">
                            {{-- <div class="row g-1 mt-4">
                                <div class="col-md-3">
                                    <input class="form-control" name="picName[]" id="picName" type="text"
                                        placeholder="{{ __('menu_customer.pic_name') }}" value="{{ $data->picName }}">
                                </div>

                                <div class="col-md-3">
                                    <input class="form-control" name="phone[]" id="phone" type="number"
                                        placeholder="Phone" value="{{ $data->phone }}">
                                </div>

                                <div class="col-md-1">
                                    <button type="button" class="btn btn-sm btn-danger mt-1">
                                        <i class="mdi mdi-delete"></i>
                                    </button>
                                </div>

                            </div> --}}

                        </div>
                    </div>

                    <div class="col-12">
                        <button class="btn btn-primary" type="submit">{{ __('general.add') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src=" {{ asset('assets/js/select2/select2-custom.js') }}"></script>

    <script>
        const addInputFile = document.getElementById("addInputFile");
        const listInputFile = document.getElementById("listInputFile");


        let inputFileCount = document.querySelectorAll("input[name^='picName']").length;
        console.log(inputFileCount);

        addInputFile.addEventListener('click', () => {
            listInputFile.appendChild(addFile(inputFileCount));
            inputFileCount++;
        })

        function addFile(num) {
            const div = document.createElement('div');
            div.classList.add('row', 'g-1', 'mt-4',
                `file${num}`)
            div.innerHTML = `
             <div class="col-md-3">
                <input class="form-control" name="picName[${num}]"  type="text" required
                    placeholder="{{ __('menu_customer.pic_name') }}" >
            </div>

            <div class="col-md-3">
                <input class="form-control" name="phone[${num}]"  type="number" required
                    placeholder="Phone" >
            </div>

            <div class="col-md-1">
                <button type="button" onclick="removeFile(${num})" class="btn btn-sm btn-danger mt-1">
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
    </script>
@endpush
