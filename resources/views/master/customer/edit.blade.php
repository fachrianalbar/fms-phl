@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => $title,
    'secondSegment' => __('general.edit'),
])

@push('style')
    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/select2.css') }}">

    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/custom-select2.css') }}">
    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/sweetalert2.css') }} ">


    <style>
        #dt {
            border-spacing: 0 15px !important;
            border-collapse: separate !important;
        }
    </style>
@endpush

@section('content')
    <form class="row g-3" method="post" action="{{ route($view . 'update', $data->id) }}">
        @include('partials.alert')

        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>{{ $title }} {{ __('general.edit_data') }}</h4>

                    <a href="{{ route($view . 'index') }}" class="btn btn-info">{{ __('general.back_to_list') }}</a>

                </div>
                <div class="card-body col-md-12">
                    @csrf
                    @method('PUT')
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <label class="form-label" for="code">Code <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <input class="form-control" name="code" id="code" type="text" required
                                placeholder="Code" value="{{ $data->code }}">
                        </div>


                    </div>

                    <div class="row mt-4">
                        {{-- <div class="col-md-6">
                            <label class="form-label" for="nickname">{{ __('menu_customer.nickname') }}</label>
                            <input class="form-control" name="nickname" id="nickname" type="text"
                                placeholder="{{ __('menu_customer.nickname') }}" value="{{ $data->nickname }}">
                        </div> --}}

                        <div class="col-md-6">
                            <label class="form-label" for="name">{{ __('menu_customer.name') }} <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <input class="form-control" name="name" id="name" type="text" required
                                placeholder="{{ __('menu_customer.name') }}" value="{{ $data->name }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="email">Email </label>
                            <input class="form-control" name="email" id="email" type="email" placeholder="Email"
                                value="{{ $data->email }}">
                        </div>

                        {{-- <div class="col-md-6">
                            <label class="form-label" for="phone">Phone </label>
                            <input class="form-control" name="phone" id="phone" type="number" placeholder="Phone"
                                value="{{ $data->phone }}">
                        </div> --}}
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="officeAddress"> {{ __('menu_customer.office_address') }}
                            </label>
                            <textarea class="form-control" name="officeAddress" id="officeAddress"
                                placeholder=" {{ __('menu_customer.office_address') }}" rows="4">{{ $data->officeAddress }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label"
                                for="billingAddress">{{ __('menu_customer.billing_address') }}</label>
                            <textarea class="form-control" name="billingAddress" id="billingAddress"
                                placeholder=" {{ __('menu_customer.billing_address') }}" rows="4">{{ $data->billingAddress }}</textarea>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="npwp">Npwp</label>
                            <input class="form-control" name="npwp" id="npwp" type="text" placeholder="Npwp"
                                value="{{ $data->npwp }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="accountNumber">{{ __('menu_customer.account_number') }}</label>
                            <input class="form-control" name="accountNumber" id="accountNumber" type="number"
                                placeholder="{{ __('menu_customer.account_number') }}" value="{{ $data->accountNumber }}">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="ppn">Ppn</label>
                            <input class="form-control" name="ppn" id="ppn" type="number" placeholder="Ppn"
                                value="{{ $data->ppn }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="pph"> Pph</label>
                            <input class="form-control" name="pph" id="pph" type="number" placeholder="Pph"
                                value="{{ $data->pph }}">
                        </div>
                    </div>

                    {{-- <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="telegramUsername">Telegram Username</label>
                            <input class="form-control" name="telegramUsername" id="telegramUsername" type="text"
                                placeholder="Telegram Username" value="{{ $data->telegramUsername }}">
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
                                    <option value="{{ $item->code }}"
                                        {{ $data->companyCode == $item->code ? 'selected' : '' }}>{{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label"
                                for="due_date_duration">{{ __('menu_customer.due_date_duration') }}</label>
                            <input class="form-control" name="due_date_duration" id="due_date_duration" type="number"
                                min="1" placeholder="{{ __('menu_customer.due_date_duration') }}"
                                value="{{ $data->due_date_duration }}">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6 position-relative">
                            <label class="form-label" for="type"> {{ __('menu_customer.type') }} <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <select class="js-example-basic-single" name="type" id="type" required="">
                                <option selected="" disabled="" value="">
                                    {{ __('general.choose') }}...</option>
                                <option value="Individual" {{ $data->type == 'Individual' ? 'selected' : '' }}>
                                    {{ __('menu_customer.person') }}</option>
                                <option value="Company" {{ $data->type == 'Company' ? 'selected' : '' }}>
                                    {{ __('menu_customer.company') }}</option>

                            </select>
                        </div>

                        <div class="col-md-6 position-relative">
                            <label class="form-label" for="isDo"> {{ __('menu_customer.is_do') }} <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <select class="js-example-basic-single" name="isDo" id="idDo" required="">
                                <option selected="" disabled="" value="">
                                    {{ __('general.choose') }}...</option>
                                <option value=1 {{ $data->isDo == 1 ? 'selected' : '' }}>
                                    {{ __('general.no') }}</option>
                                <option value=0 {{ $data->isDo == 0 || $data->isDo != null ? 'selected' : '' }}>
                                    {{ __('general.yes') }}</option>

                            </select>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="d-flex justify-content-between col-md-6">
                            <label class="form-label" for="picName">{{ __('menu_customer.pic_data') }}</label>

                            <button id="addInputFile" type="button" class="btn btn-sm btn-info mb-2  font-weight-bold"
                                href="#" target="_blank"><i class="mdi mdi-plus"></i></button>

                        </div>


                        @forelse ($data->pic as $item)
                            <div>
                                <div class="row g-1 {{ $loop->iteration != 1 ? 'mt-4' : '' }}">
                                    <div class="col-md-3">
                                        <input class="form-control" id="picName" type="text"
                                            placeholder="{{ __('menu_customer.pic_name') }}"
                                            name="picName[{{ $loop->iteration - 1 }}]" value="{{ $item->picName }}">
                                    </div>

                                    <div class="col-md-3">
                                        <input class="form-control" id="phone" type="text" inputmode="numeric"
                                            maxlength="15" placeholder="Phone" name="phone[{{ $loop->iteration - 1 }}]"
                                            value="{{ $item->phone }}">
                                    </div>

                                    @if ($loop->iteration != 1)
                                        <div class="col-md-1">
                                            <button type="button" onclick="deleteCustomerPic('{{ $item->id }}')"
                                                class="btn btn-sm btn-danger mt-2">
                                                <i class="mdi mdi-delete"></i>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
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
                        @endforelse



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
                </div>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>{{ $title }} Detail Data</h4>

                        <button class="btn btn-primary" type="button"
                            id="save">{{ __('general.add_data') }}</button>


                    </div>

                    <div class="card-body col-md-12">
                        <table class="table table-sm" id="dt">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th style="width: 90%">{{ __('menu_customer.name') }}</th>
                                </tr>
                            </thead>
                            <tbody id="customerDetails">


                                @if (isset($data->details))
                                    @foreach ($data->details as $item)
                                        <tr>
                                            <td>
                                                <a href="javascript:deleteCustomerDetail('{{ $item->id }}')"
                                                    class="btn btn-icon btn-sm bg-danger-subtle" data-bs-toggle="tooltip"
                                                    title="Delete">
                                                    <i class="mdi mdi-delete fs-14 text-danger"></i>
                                                </a>
                                            </td>

                                            <td>
                                                <input class="form-control" name="nameDetail[]" type="text"
                                                    style="width: 500px" value="{{ $item->name }}">
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td></td>
                                        <td>
                                            <input type="text" class="form-control" name="nameDetail[]"
                                                style="width: 500px">
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                </div>

                <div class="card">
                    <div class="col-12">
                        <div class="card-body">
                            <button class="btn btn-primary" id="submit"
                                type="submit">{{ __('general.save_changes') }}</button>
                        </div>
                    </div>
                </div>
            </div>
    </form>
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
        function deleteCustomerPic(id) {
            var url = '{{ route('master.customer-pic.destroy', ':id') }}';
            url = url.replace(':id', id);

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
                    placeholder="{{ __('menu_customer.pic_name') }}">
            </div>

            <div class="col-md-3">
                <input class="form-control" name="phone[${num}]"  type="number" required
                    placeholder="Phone">
            </div>

            <div class="col-md-1">
                <button type="button" onclick="removeFile(${num})" class="btn btn-sm btn-danger mt-1">
                    <i class="mdi mdi-delete"></i>
                </button>
            </div>
            `
            return div;
        }
    </script>


    <script>
        $('#save').on('click', function() {
            let row = $('#customerDetails tr').length + 1;

            let newRow = `<tr>
                            <td class="remove-btn">
                                  <a href="javascript:removeDetailRow(${row})"
                                class="btn btn-icon btn-sm bg-danger-subtle"
                                data-bs-toggle="tooltip" title="Delete">
                                    <i class="mdi mdi-delete fs-14 text-danger"></i>
                                </a>

                            </td>
                             <td>
                                    <input type="text" class="form-control" name="nameDetail[]" id="nameDetail_${row}" style="width: 500px">
                            </td>
                          </tr>`;
            $('#customerDetails').append(newRow);
        });

        function removeDetailRow(row) {
            $(`#nameDetail_${row}`).closest('tr').remove();
        }


        function removeFile(num) {
            let content = document.querySelector(`.file${num}`);
            content.remove();
        }

        function deleteCustomerDetail(id) {
            var url = '{{ route('master.customer-detail.destroy', ':id') }}';
            url = url.replace(':id', id);

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
@endpush
