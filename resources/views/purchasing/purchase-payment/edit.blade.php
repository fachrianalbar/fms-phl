@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => $title,
    'secondSegment' => 'Edit',
])

@push('style')
    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/select2.css') }}">

    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/custom-select2.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/libs/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/libs/datatables.net-keytable-bs5/css/keyTable.bootstrap5.min.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/libs/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/libs/datatables.net-select-bs5/css/select.bootstrap5.min.css') }}">
    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/sweetalert2.css') }} ">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">
@endpush

@section('content')
    <form method="post" method="post" action="{{ route($view . 'update', $data->id) }}">
        @csrf
        @method('PUT')
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>{{ $title }} Edit Data</h4>
                    <a href="{{ route($view . 'index') }}" class="btn btn-info">{{ __('general.back_to_list') }}</a>
                </div>
                <div class="card-body col-md-6">
                    <div class="row g-3">
                        <div class="row">
                            <div class="col-md-12">
                                <label class="form-label" for="code">Code</label>
                                <input class="form-control" name="code" type="text" required placeholder="Code"
                                    value="{{ $data->code }}" readonly>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <label class="form-label" for="name">Date </label>
                                <input class="form-control" name="date" type="text" required placeholder="Order Date"
                                    value="{{ $data->date }}" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Time</label>
                                <input class="form-control digits" name="time" type="time"
                                    value="{{ $data->time }}" readonly>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <label class="form-label" for="supplierCode">Supplier </label>
                                <select class="js-example-basic-single" name="supplierCode" id="supplierCode" disabled>
                                    <option selected="" disabled="" value="">Choose...</option>
                                    @foreach ($supplier as $item)
                                        <option value="{{ $item->code }}"
                                            {{ $data->supplierCode == $item->code ? 'selected' : '' }}>{{ $item->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- <div class="row mt-4">
                            <div class="col-md-12">
                                <label class="form-label">Total Qty</label>
                                <input class="form-control" name="qty" type="number" value="{{ $totalQty }}"
                                    readonly>
                            </div>
                        </div> --}}

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <label class="form-label" for="name">Received Date</label>
                                <input class="form-control" name="receivedDate" type="text"
                                    value="{{ $data->receivedDate }}" required placeholder="Received Date">
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <label class="form-label">Total Price</label>
                                <input class="form-control" name="nominal" type="text"
                                    value="{{ number_format($totalPrice, 0, ',', '.') }}" readonly>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <label class="form-label" for="name">Payment Date <i
                                        class="icofont icofont-warning-alt text-danger"></i></label>
                                <input class="form-control" name="paymentDate" id="datetime-local" type="date"
                                    value="{{ $data->paymentDate }}" required placeholder="Payment Date">
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <label class="form-label" for="userBankCode">User Bank <i
                                        class="icofont icofont-warning-alt text-danger"></i> </label>
                                <select class="js-example-basic-single" name="userBankCode" id="userBankCode" required>
                                    <option selected="" disabled="" value="">Choose...</option>
                                    @foreach ($userBank as $item)
                                        <option value="{{ $item->code }}"
                                            {{ $data->userBankCode == $item->code ? 'selected' : '' }}>
                                            {{ $item->bank->name . ' - ' . $item->accountNumber . ' - ' . $item->accountName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label" for="description">Description</label>
                            <textarea class="form-control" name="description" id="description" placeholder="Description" rows="4"></textarea>
                        </div>
                    </div>




                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Detail Purchasing</h4>
                {{-- <button class="btn btn-primary" type="button" id="save">{{ __('general.add_data') }}</button> --}}
            </div>
            <div class="card-body col-md-12">
                @include('partials.alert')
                <table class="display " id="dt">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Code</th>
                            <th>Item/Part</th>
                            <th style="width: 10%">Qty</th>
                            <th>Prices</th>
                            <th>Total Prices</th>
                        </tr>
                    </thead>
                    <tbody id="purchaseDetails">

                        @foreach ($data->details as $item)
                            <tr>
                                {{-- <td class="remove-btn"></td> --}}
                                <td>
                                    <ul class="action">
                                        {{-- <li class="delete"><a
                                                    href="javascript:deletePurchaseDetail('{{ $item->id }}')"><i
                                                        class="icon-trash"></i></a>
                                            </li> --}}
                                    </ul>
                                </td>
                                <td>
                                    <input type="hidden" name="purchaseDetailCode[]" value="{{ $item->code }}">

                                    <select class="js-example-basic-single" name="itemCode[]"
                                        id="itemCode_{{ $loop->iteration }}" required>
                                        <option value="{{ $item->itemCode }}" data-name="{{ $item->item->name }}">
                                            {{ $item->itemCode . ' - ' . $item->item->name }}
                                        </option>

                                    </select>
                                </td>
                                <td>
                                    <div class="me-5">
                                        <input class="form-control" type="text" id="itemName_{{ $loop->iteration }}"
                                            required readonly value="{{ $item->item->name }}">
                                    </div>
                                </td>

                                <td>
                                    <input class="form-control w-50 " type="number" min="0" name="qty[]"
                                        id="qty_{{ $loop->iteration }}" value="{{ $item->receivedQty }}">
                                </td>
                                <td>
                                    <input class="form-control w-75" type="text" name="price[]"
                                        id="price_{{ $loop->iteration }}" oninput="formatAngka(this)" readonly required
                                        value="{{ number_format($item->item->price, 0, ',', '.') }}">
                                </td>
                                <td>
                                    <input class="form-control w-75" type="text"
                                        id="totalPrice_{{ $loop->iteration }}" readonly
                                        value="{{ number_format($item->item->price * $item->receivedQty, 0, ',', '.') }}">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if ($data->status == 2)
            <div class="card">
                <div class="col-12">
                    <div class="card-body">
                        <button class="btn btn-primary" id="submit" type="submit">Save Data</button>
                    </div>
                </div>
            </div>
        @endif

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
    <script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>

    <!-- dataTables.bootstrap5 -->
    <script src="{{ asset('assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>

    <!-- dataTables.keyTable -->
    <script src="{{ asset('assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-keytable-bs5/js/keyTable.bootstrap5.min.js') }}"></script>

    <!-- dataTable.responsive -->
    <script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>

    <!-- dataTables.select -->
    <script src="{{ asset('assets/libs/datatables.net-select/js/dataTables.select.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-select-bs5/js/select.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/custom-flatpickr.js') }}"></script>
    <script src=" {{ asset('assets/js/helper.js') }}"></script>


    <script>
        $(document).ready(function() {
            $('#dt').DataTable();
            // Initialize Select2 on the first row
            $(".js-example-basic-single").select2();
        });
    </script>
@endpush
