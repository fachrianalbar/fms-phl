@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => $title,
    'secondSegment' => __('general.add'),
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
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/sweetalert2.css') }}">

    <style>
        #dt {
            border-spacing: 0 15px !important;
            border-collapse: separate !important;
        }
    </style>
@endpush

@section('content')
    <form method="post" action="{{ route($view . 'store') }}">
        @csrf
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>{{ $title }} {{ __('general.add_data') }}</h4>
                    <a href="{{ route($view . 'index') }}" class="btn btn-info">{{ __('general.back_to_list') }}</a>
                </div>
                <div class="card-body col-md-6">
                    <div class="row g-3">
                        <div class="row">
                            <div class="col-md-12">
                                <label class="form-label" for="code">Code <i
                                        class="icofont icofont-warning-alt text-danger"></i></label>
                                <input class="form-control" name="code" type="text" required placeholder="Code">
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <label class="form-label" for="name">{{ __('menu_purchase.date') }}<i
                                        class="icofont icofont-warning-alt text-danger"></i></label>
                                <input class="form-control" name="date" id="datetime-local" type="date" required
                                    placeholder="Order Date" value="{{ now()->toDateString() }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">{{ __('menu_purchase.time') }}</label>
                                <input class="form-control digits" name="time" type="time"
                                    value="{{ now()->setTimezone('Asia/Jakarta')->format('H:i') }}">
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <label class="form-label" for="supplierCode">Supplier <i
                                        class="icofont icofont-warning-alt text-danger"></i></label>
                                <select class="js-example-basic-single" name="supplierCode" id="supplierCode" required>
                                    <option selected="" disabled="" value="">{{ __('general.choose') }}...
                                    </option>
                                    @foreach ($supplier as $item)
                                        <option value="{{ $item->code }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Detail Purchasing</h4>
                    <button class="btn btn-primary" type="button" id="save">{{ __('general.add_data') }}</button>
                </div>
                <div class="card-body col-md-12">
                    <table class="table table-bordered dt-responsive table-responsive nowrap" id="dt">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Code</th>
                                <th class="text-center">Item/Part</th>
                                <th style="width: 10%">Qty</th>
                                <th>{{ __('menu_maintenance.prices') }}</th>
                                <th>{{ __('menu_maintenance.total_prices') }}</th>
                            </tr>
                        </thead>
                        <tbody id="purchaseDetails">
                            <tr>
                                <td class="remove-btn"></td> <!-- Remove button will be here -->
                                <td>
                                    <select class="js-example-basic-single" name="itemCode[]" id="itemCode_1" required
                                        onchange="loadItemDetails(1)">
                                        <option selected="" disabled="" value="">{{ __('general.choose') }}...
                                        </option>
                                        @foreach ($items as $it)
                                            <option value="{{ $it->code }}" data-name="{{ $it->name }}"
                                                data-price="{{ $it->price }}">
                                                {{ $it->code . ' - ' . $it->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <div class="mx-5">
                                        <input class="form-control" type="text" id="itemName_1" required readonly>

                                    </div>
                                </td>
                                <td>
                                    <input class="form-control w-50 " type="number" min="1" name="qty[]"
                                        id="qty_1" value="1" onchange="updateTotalPrice(1)">
                                </td>
                                <td>
                                    <input class="form-control w-75" type="text" id="price_1"
                                        oninput="formatAngka(this)" onchange="updateTotalPrice(1)" name="price[]"
                                        required value="0">
                                </td>
                                <td>
                                    <input class="form-control w-75" type="text" id="totalPrice_1" readonly
                                        value="0">
                                </td>
                            </tr>
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
    <script src="{{ asset('assets/js/flat-pickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/custom-flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>
    <script src=" {{ asset('assets/js/helper.js') }}"></script>


    <script>
        let dataItem;
        let itemsData = @json($items);

        $(document).ready(function() {
            $('#dt').DataTable();

        });

        // Load items based on supplier
        function itemBySupplier(supplierCode) {
            let html = '<option selected="" disabled="" value="">{{ __('general.choose') }}...</option>';
            $('#itemCode_1').html(html);

            $.get("{{ url('ajax/item-by-supplier') }}/" + supplierCode, function(data) {
                dataItem = data;
                data.forEach(i => {
                    html +=
                        `<option value="${i.code}" data-name="${i.name}" data-price="${i.price}">${i.code} - ${i.name}</option>`;
                });

                let row = $('#purchaseDetails tr').length + 1;

                // Update all itemCode select dropdowns with the loaded data
                for (let i = 2; i <= row; i++) {
                    $(`#itemCode_${i}`).html(html);
                    // $(`#itemName_${i}`).val()
                    // Reinitialize select2 for newly added select elements
                    $(`#itemCode_${i}`).select2();
                }

                $('#itemCode_1').html(html);
                $('#itemName_1').val("");
                $('#qty_1').val(1);
                $('#price_1').val(0)
                $('#totalPrice_1').val(0)

                // Reinitialize select2 for the first row
                $('#itemCode_1').select2();
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

        // Load item details (name, price)
        function loadItemDetails(row) {
            let itemCode = $(`#itemCode_${row}`).val();
            let itemName = $(`#itemCode_${row} option:selected`).data('name');
            let itemPrice = $(`#itemCode_${row} option:selected`).data('price');

            itemPrice = Intl.NumberFormat('id-ID').format(Math.round(itemPrice))


            $(`#itemName_${row}`).val(itemName);
            $(`#price_${row}`).val(itemPrice);
            updateTotalPrice(row);
        }

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
                              

                                <a href="javascript:removeDetailRow(${row})"
                                class="btn btn-icon btn-sm bg-danger-subtle"
                                data-bs-toggle="tooltip" title="Delete">
                                    <i class="mdi mdi-delete fs-14 text-danger"></i>
                                </a>
                         </td>
                         <td>
                             <select class="form-control js-example-basic-single" name="itemCode[]" id="itemCode_${row}" required onchange="loadItemDetails(${row})">
                                 <!-- Options yang di-generate -->
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
                             <input class="form-control w-75" type="text" id="price_${row}" name="price[]" min="1" onchange="updateTotalPrice(${row})" oninput="formatAngka(this)" required value="0">
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
