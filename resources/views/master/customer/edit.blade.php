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
                            <label class="form-label" for="name">Name <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <input class="form-control" name="name" id="name" type="text" required
                                placeholder="Name" value="{{ $data->name }}">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="picName">Pic Name</label>
                            <input class="form-control" name="picName" id="picName" type="text" placeholder="Pic Name"
                                value="{{ $data->picName }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="nickname">Nickname</label>
                            <input class="form-control" name="nickname" id="nickname" type="text" placeholder="Nickname"
                                value="{{ $data->nickname }}">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="email">Email </label>
                            <input class="form-control" name="email" id="email" type="email" placeholder="Email"
                                value="{{ $data->email }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="phone">Phone </label>
                            <input class="form-control" name="phone" id="phone" type="number" placeholder="Phone"
                                value="{{ $data->phone }}">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="address1">Address 1</label>
                            <textarea class="form-control" name="address1" id="address1" placeholder=" Address 1" rows="4">{{ $data->address1 }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="address2">Address 2</label>
                            <textarea class="form-control" name="address2" id="address2" placeholder=" Address 2" rows="4">{{ $data->address2 }}</textarea>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="npwp">Npwp</label>
                            <input class="form-control" name="npwp" id="npwp" type="text" placeholder="Npwp"
                                value="{{ $data->npwp }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="accountNumber"> Account Number</label>
                            <input class="form-control" name="accountNumber" id="accountNumber" type="number"
                                placeholder="Account Number" value="{{ $data->accountNumber }}">
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
                            <label class="form-label" for="due_date_duration">Due Date Duration</label>
                            <input class="form-control" name="due_date_duration" id="due_date_duration" type="number"
                                min="1" placeholder="Telegram Username" value="{{ $data->due_date_duration }}">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6 position-relative">
                            <label class="form-label" for="type"> {{ __('menu_customer.type') }} <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <select class="js-example-basic-single" name="type" id="type" required="">
                                <option selected="" disabled="" value="">
                                    {{ __('general.choose') }}...</option>
                                <option value="Person" {{ $data->type == 'Person' ? 'selected' : '' }}>
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
                                    {{ __('general.yes') }}</option>
                                <option value=0 {{ $data->isDo == 0 || $data->isDo != null ? 'selected' : '' }}>
                                    {{ __('general.no') }}</option>

                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>{{ $title }} Detail Data</h4>

                    <button class="btn btn-primary" type="button" id="save">{{ __('general.add_data') }}</button>


                </div>

                <div class="card-body col-md-12">
                    @include('partials.alert')
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
