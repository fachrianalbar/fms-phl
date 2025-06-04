@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => $title,
    'secondSegment' => __('general.edit'),
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

    <style>
        #dt {
            border-spacing: 0 15px !important;
            border-collapse: separate !important;
        }
    </style>
@endpush

@section('content')
    <form method="post" id="update-form" action="{{ route($view . 'update', $data->id) }}">
        @csrf
        @method('PUT')
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>{{ $title }} {{ __('general.edit_data') }}</h4>
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
                                <label class="form-label" for="name">{{ __('menu_purchase.date') }}</label>
                                <input class="form-control" name="date" type="text" required placeholder="Order Date"
                                    value="{{ $data->date }}" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">{{ __('menu_purchase.time') }}</label>
                                <input class="form-control digits" name="time" type="time"
                                    value="{{ $data->time }}" readonly>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <label class="form-label" for="supplierCode">Supplier </label>
                                <select class="js-example-basic-single" name="supplierCode" id="supplierCode" disabled>
                                    <option selected="" disabled="" value="">{{ __('general.choose') }}...
                                    </option>
                                    @foreach ($supplier as $item)
                                        <option value="{{ $item->code }}"
                                            {{ $data->supplierCode == $item->code ? 'selected' : '' }}>{{ $item->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <label class="form-label" for="name">{{ __('menu_purchase.received_date') }}<i
                                        class="icofont icofont-warning-alt text-danger"></i></label>
                                <input class="form-control" name="receivedDate" id="datetime-local" type="date"
                                    value="{{ $data->receivedDate }}" required
                                    placeholder="{{ __('menu_purchase.received_date') }}">
                            </div>
                        </div>

                        {{-- <div class="row mt-4">
                            <div class="col-md-12">
                                <label class="form-label">Total Qty</label>
                                <input class="form-control" name="qty" type="number" value="{{ $totalQty }}"
                                    readonly>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <label class="form-label">Total Price</label>
                                <input class="form-control" name="price" type="text"
                                    value="{{ number_format($totalPrice, 0, ',', '.') }}" readonly>
                            </div>
                        </div> --}}

                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Detail {{ __('menu_purchase.purchase') }}</h4>
                    <button class="btn btn-primary" type="button" id="check-all">Check All</button>
                </div>
                <div class="card-body col-md-12">
                    @include('partials.alert')
                    <table class="table table-sm" id="dt">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Code</th>
                                <th class="text-center">Item/Part</th>
                                <th style="width: 10%">Qty</th>
                                <th>{{ __('menu_purchase.prices') }}</th>
                                <th>{{ __('menu_purchase.total_prices') }}</th>
                                {{-- <th>QC</th> --}}
                            </tr>
                        </thead>
                        <tbody id="purchaseDetails">

                            @foreach ($data->details as $item)
                                <tr>
                                    {{-- <td class="remove-btn"></td> --}}
                                    <td>
                                        {{-- <ul class="action">
                                            <li class="delete"><a
                                                    href="javascript:deletePurchaseDetail('{{ $item->id }}')"><i
                                                        class="icon-trash"></i></a>
                                            </li>
                                        </ul> --}}

                                        <input class="confirm-checkbox" type="checkbox" name="confirm[]"
                                            data-id="{{ $item->id }}" value="{{ $item->id }}"
                                            {{ $item->status == 1 ? 'checked disabled' : '' }}>
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
                                        <div class="mx-5">
                                            <input class="form-control" type="text"
                                                id="itemName_{{ $loop->iteration }}" required readonly
                                                value="{{ $item->item->name }}">
                                        </div>
                                    </td>

                                    <td>
                                        <input class="form-control w-50 " type="number" min="0" name="qty[]"
                                            id="qty_{{ $loop->iteration }}" value="{{ $item->qty }}" readonly>
                                    </td>
                                    <td>
                                        <input class="form-control w-75" type="text" name="price[]"
                                            id="price_{{ $loop->iteration }}" oninput="formatAngka(this)" required
                                            value="{{ number_format($item->price, 0, ',', '.') }}" readonly>
                                    </td>
                                    <td>
                                        <input class="form-control w-75" type="text"
                                            id="totalPrice_{{ $loop->iteration }}" readonly
                                            value="{{ number_format($item->price * $item->qty, 0, ',', '.') }}">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="col-12">
                    <div class="card-body">
                        <button class="btn btn-primary" id="submit"
                            type="submit">{{ __('menu_purchase.confirm_data') }}</button>
                    </div>
                </div>
            </div>


            <div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
                aria-labelledby="myLargeModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Detail {{ __('menu_purchase.purchase') }}
                                Data</h4>
                            <button class="btn-close py-0" type="button" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="card">
                            <div class="card-body col-md-12">
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label" for="itemCodeModal">Item Code</label>
                                        <input class="form-control" name="itemCodeModal" id="itemCodeModal"
                                            type="text" readonly placeholder="Item Code">
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label"
                                            for="itemNameModal">{{ __('menu_purchase.name') }}</label>
                                        <input class="form-control" name="itemNameModal" id="itemNameModal"
                                            type="text" readonly placeholder="Name">
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label" for="qtyModal">Qty</label>
                                        <input class="form-control" name="qtyModal" id="qtyModal" type="number"
                                            readonly placeholder="Qty">
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label"
                                            for="receivedQty">{{ __('menu_purchase.received_qty') }}<i
                                                class="icofont icofont-warning-alt text-danger"></i></label>
                                        <input class="form-control" name="receivedQty" id="receivedQty" type="number"
                                            placeholder="Received Qty">
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label" for="description">Quality Control (QC)</label>
                                        <textarea class="form-control" name="description" id="description" rows="3"></textarea>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="modal-footer justify-content-start">
                            <button class="btn btn-primary"
                                type="submit">{{ __('menu_purchase.confirm_data') }}</button>
                        </div>
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

            $("#check-all").click(function() {
                let allChecked = $(".confirm-checkbox:not(:disabled)").length === $(
                    ".confirm-checkbox:not(:disabled):checked").length;

                $(".confirm-checkbox:not(:disabled)").prop("checked", !allChecked);
            });
        });

        // Attach validation to the save button
        $('#submit').on('click', function(e) {
            let isValid = true;
            let errorMessage = '';
            let codes = [];
            let isDuplicate = false;
            let checkedCount = $(".confirm-checkbox:checked:not(:disabled)").length;
            let form = $("#update-form");

            // Loop through each row to validate quantities
            $('#purchaseDetails tr').each(function() {
                let code = $(this).find('select[name="itemCode[]"]').val();
                let itemName = $(this).find('select[name="itemCode[]"] option:selected').data('name');

                // Check for duplicate codes
                if (codes.includes(code)) {
                    isDuplicate = true;
                    errorMessage =
                        `Duplicate entry detected for the item "${itemName}". Please remove the duplicate.`;
                    isValid = false;
                    return false; // Exit loop early
                }
                codes.push(code);
            });

            // If not valid, show alert and prevent form submission
            if (!isValid) {
                e.preventDefault();

                swal({
                    title: "{{ __('general.warning') }}",
                    text: errorMessage,
                    icon: "warning",
                })
                return
            }

            if (checkedCount === 1) {
                e.preventDefault();

                const id = $(".confirm-checkbox:checked").data(
                    "id");

                $.ajax({
                    url: "{{ url('ajax/purchase-detail') }}/" + id,
                    type: "GET",
                    success: function(response) {
                        console.log(response);
                        $('input[name="itemCodeModal"]').val(response.itemCode);
                        $('input[name="itemNameModal"]').val(response.item.name);
                        $('input[name="qtyModal"]').val(response.qty);
                        $('input[name="receivedQty"]').attr({
                            "min": 1,
                            "max": response.qty
                        });

                        $('.bd-example-modal-lg').modal('show');
                    }
                });

                // console.log(selectedId);

            } else if (checkedCount > 1) {
                // Jika lebih dari 1 checkbox dicentang, langsung submit form
                form.submit();
            } else if (checkedCount <= 0) {
                e.preventDefault();
                swal({
                    title: "{{ __('general.warning') }}",
                    text: 'Please select at least one data',
                    icon: "warning",
                })
                return
            }
        });
    </script>
@endpush
