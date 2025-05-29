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
    <form method="post" method="post" action="{{ route($view . 'update', $data->id) }}">
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
                                <label class="form-label" for="name">{{ __('menu_purchase.date') }} </label>
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
                    {{-- <button class="btn btn-primary" type="button" id="save">{{ __('general.add_data') }}</button> --}}
                </div>
                <div class="card-body col-md-12">
                    @include('partials.alert')
                    <table class="table table-striped w-100 nowrap" id="dt">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Code</th>
                                <th class="text-center">Item/Part</th>
                                <th style="width: 10%">Qty</th>
                                <th>{{ __('menu_purchase.prices') }}</th>
                                <th>{{ __('menu_purchase.total_prices') }}</th>
                            </tr>
                        </thead>
                        <tbody id="purchaseDetails">

                            @foreach ($data->details as $item)
                                <tr>
                                    {{-- <td class="remove-btn"></td> --}}
                                    <td>
                                        <a href="javascript:deletePurchaseDetail('{{ $item->id }}')"
                                            class="btn btn-icon btn-sm bg-danger-subtle" data-bs-toggle="tooltip"
                                            title="Delete">
                                            <i class="mdi mdi-delete fs-14 text-danger"></i>
                                        </a>
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
                                            <input class="form-control" type="text" id="itemName_{{ $loop->iteration }}"
                                                required readonly value="{{ $item->item->name }}">
                                        </div>
                                    </td>

                                    <td>
                                        <input class="form-control w-50 " type="number" min="0" name="qty[]"
                                            id="qty_{{ $loop->iteration }}" value="{{ $item->qty }}" readonly
                                            onchange="updateTotalPrice({{ $loop->iteration }})">
                                    </td>
                                    <td>
                                        <input class="form-control w-75" type="text" name="price[]"
                                            id="price_{{ $loop->iteration }}" oninput="formatAngka(this)" readonly
                                            required value="{{ number_format($item->price, 0, ',', '.') }}">
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
                            type="submit">{{ __('menu_purchase.verify_data') }}</button>
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
        });

        function deletePurchaseDetail(id) {
            var url = '{{ route('purchasing.purchase-verification-detail.destroy', ':id') }}'; // Use placeholder ':id'
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

        // Attach validation to the save button
        $('#submit').on('click', function(e) {
            let isValid = true;
            let errorMessage = '';
            let codes = [];
            let isDuplicate = false;

            // Loop through each row to validate quantities
            $('#purchaseDetails tr').each(function() {
                let code = $(this).find('select[name="itemCode[]"]').val();
                let itemName = $(this).find('input[id^="itemName_"]').val(); // Get the item name


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
                    title: "Warning",
                    text: errorMessage,
                    icon: "warning",
                })
                return
            }
        });

        // Update total price based on quantity
        function updateTotalPrice(row) {
            let qty = $(`#qty_${row}`).val();
            let price = $(`#price_${row}`).val();
            let totalPrice = qty * parseInt(price.replace(/\./g, ''));

            totalPrice = Intl.NumberFormat('id-ID').format(Math.round(totalPrice))


            $(`#totalPrice_${row}`).val(totalPrice);
        }


        $('#save').on('click', function() {
            let row = $('#purchaseDetails tr').length + 1;

            let options = '<option selected="" disabled="" value="">{{ __('general.choose') }}...</option>';

            // Mengisi options berdasarkan data items
            itemsData.forEach(item => {
                options += `<option value="${item.code}" data-name="${item.name}" data-price="${item.price}">
                        ${item.code} - ${item.name}
                    </option>`;
            });

            let newRow = `<tr>
                            <td class="remove-btn">
                                 <ul class="action">
                                    <li class="delete"><a href="javascript:removeDetailRow(${row})"><i class="icon-trash"></i></a></li>
                                </ul>

                            </td>
                            <td>
                                <select class="js-example-basic-single" name="itemCode[]" id="itemCode_${row}" required onchange="loadItemDetails(${row})">
                                    <option selected="" disabled="" value="">{{ __('general.choose') }}...</option>
                                </select>
                            </td>
                            <td>
                                <div class="mx-5">
                                    <input class="form-control" type="text" id="itemName_${row}" required readonly>
                                </div>
                            </td>
                            <td>
                                <input class="form-control w-50" type="number" min="1" name="qty[]" id="qty_${row}" value="1" onchange="updateTotalPrice(${row})">
                            </td>
                            <td>
                                <input class="form-control w-75" type="text" id="price_${row}" name="price[]" onchange="updateTotalPrice(${row})" required oninput="formatAngka(this)" value="0">
                            </td>
                            <td>
                                <input class="form-control w-75" type="text" id="totalPrice_${row}" readonly value="0">
                            </td>
                          </tr>`;
            $('#purchaseDetails').append(newRow);
            // itemBySupplier($('#supplierCode').val());


            // let html = '<option selected="" disabled="" value="">{{ __('general.choose') }}...</option>';

            // dataItem.forEach(i => {
            //     html +=
            //         `<option value="${i.code}" data-name="${i.name}" data-price="${i.price}">${i.code} - ${i.name}</option>`;
            // });

            $(`#itemCode_${row}`).html(options);
            // Reinitialize select2 for newly added select elements
            $(`#itemCode_${row}`).select2();
        });

        // Remove row from purchase detail
        function removeDetailRow(row) {
            $(`#itemCode_${row}`).closest('tr').remove();
        }

        // Hide remove button on the first row
        $(document).on('click', '.remove-btn', function() {
            if ($('#purchaseDetails tr').length > 1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    </script>
@endpush
