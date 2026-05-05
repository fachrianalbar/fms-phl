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
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/sweetalert2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">

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
                                <!-- Input terlihat (tidak bisa diubah user) -->
                                <input class="form-control" type="text" placeholder="Code" id="code_display" readonly
                                    disabled>

                                <!-- Input tersembunyi yang dikirim saat submit -->
                                <input type="hidden" name="code" id="code_hidden">
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <label class="form-label" for="name">{{ __('menu_maintenance.date') }} <i
                                        class="icofont icofont-warning-alt text-danger"></i></label>
                                <input class="form-control" name="date" id="datetime-local" type="date" required
                                    placeholder="Order Date" value="{{ now()->toDateString() }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">{{ __('menu_maintenance.time') }}</label>
                                <input class="form-control digits" name="time" type="time"
                                    value="{{ now()->setTimezone('Asia/Jakarta')->format('H:i') }}">
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <label class="form-label" for="fleetCode">{{ __('menu_maintenance.fleet') }} <i
                                        class="icofont icofont-warning-alt text-danger"></i></label>
                                <select class="js-example-basic-single" name="fleetCode" id="fleetCode" required>
                                    <option selected="" disabled="" value="">{{ __('general.choose') }}...
                                    </option>
                                    @foreach ($fleet as $item)
                                        <option value="{{ $item->code }}">
                                            {{ $item->plateNumber }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <label class="form-label" for="warehouseCode">Warehouse <i
                                        class="icofont icofont-warning-alt text-danger"></i></label>
                                <select class="js-example-basic-single" name="warehouseCode" id="warehouseCode" required>
                                    <option selected="" disabled="" value="">{{ __('general.choose') }}...
                                    </option>
                                    @foreach ($warehouse as $item)
                                        <option value="{{ $item->code }}">
                                            {{ $item->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Detail {{ $title }}</h4>
                    <button class="btn btn-primary" type="button" id="save">{{ __('general.add_data') }}</button>
                </div>
                <div class="card-body col-md-12">
                    <table class="table table-sm" id="dt">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Item/Part</th>
                                <th>Stock</th>
                                <th>Qty</th>
                                <th style="text-align: right">Price</th>
                                <th style="text-align: right">Total</th>
                            </tr>
                        </thead>
                        <tbody id="purchaseDetails">
                            <tr>
                                <td class="remove-btn"></td>
                                <td>
                                    <select class="js-example-basic-single" name="itemCode[]" id="itemCode_1" required
                                        onchange="loadItemDetails(1)" disabled>
                                        <option selected="" disabled="" value="">
                                            {{ __('general.choose') }}...
                                        </option>
                                    </select>
                                </td>
                                <td>
                                    <input class="form-control" type="number" name="qty_exist[]" id="qty_exist_1"
                                        readonly value="0">
                                </td>
                                <td>
                                    <input class="form-control qty-input" type="number" name="qty[]" id="qty_1"
                                        required min="0.01" step="0.01" value="1">
                                </td>
                                <td>
                                    <input class="form-control text-end" type="text" name="price[]" id="price_1"
                                        readonly value="0">
                                </td>
                                <td>
                                    <input class="form-control text-end" type="text" name="total[]" id="total_1"
                                        readonly value="0">
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-end mt-2">
                        <div>
                            <div class="fw-bold">Grand Total: <span id="grand_total_display">0</span></div>
                            <input type="hidden" name="grand_total" id="grand_total" value="0">
                        </div>
                    </div>
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
    <script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/custom-flatpickr.js') }}"></script>
    <script src=" {{ asset('assets/js/helper.js') }}"></script>


    <script>
        let dataItem = [];
        let selectedWarehouse = null;

        $(document).ready(function() {
            $('#dt').DataTable();

            generateCode('input[name="date"]', '#code_display', '#code_hidden', '/ajax/maintenance-generate-code');

            // Disable add button initially
            $('#save').prop('disabled', true);
        });

        $('input[name="date"]').on('change', function() {
            generateCode('input[name="date"]', '#code_display', '#code_hidden', '/ajax/maintenance-generate-code');
        });

        // When warehouse is selected, load stock items
        $('#warehouseCode').on('change', function() {
            selectedWarehouse = $(this).val();

            if (!selectedWarehouse) {
                return;
            }

            // Load stock items for this warehouse
            $.ajax({
                url: '/ajax/maintenance-stock-by-warehouse',
                method: 'GET',
                data: {
                    warehouseCode: selectedWarehouse
                },
                success: function(response) {
                    if (response.success) {
                        dataItem = response.data;

                        // Enable item selects and add button
                        $('select[name="itemCode[]"]').prop('disabled', false);
                        $('#save').prop('disabled', false);

                        // Populate first row
                        let html =
                            '<option selected="" disabled="" value="">{{ __('general.choose') }}...</option>';
                        dataItem.forEach(i => {
                            html +=
                                `<option value="${i.code}" data-name="${i.name}" data-qty="${i.stock}" data-price="${i.price}" data-type="${i.type}">${i.code} - ${i.name}</option>`;
                        });
                        $('#itemCode_1').html(html);
                        $('#itemCode_1').select2();
                    } else {
                        swal({
                            title: "{{ __('general.warning') }}",
                            text: response.message,
                            icon: "warning",
                        });
                    }
                },
                error: function() {
                    swal({
                        title: "{{ __('general.error') }}",
                        text: "Failed to load stock items",
                        icon: "error",
                    });
                }
            });
        });

        // Load item details (name, price)
        function loadItemDetails(row) {
            let itemCode = $(`#itemCode_${row}`).val();
            let selectedOption = $(`#itemCode_${row} option:selected`);
            let itemName = selectedOption.data('name');
            let itemQty = selectedOption.data('qty');
            let itemType = selectedOption.data('type');
            let foundItem = dataItem.find(i => i.code === itemCode || (i.item && i.item.code === itemCode));
            let itemPrice = selectedOption.data('price') ?? (foundItem ? (foundItem.price ?? (foundItem.item ? foundItem
                .item.price : 0)) : 0);
            let isJasa = itemType === 'jasa' || (foundItem && foundItem.type === 'jasa');

            $(`#item_type_${row}`).val(isJasa ? 'jasa' : 'part');
            $(`#qty_exist_${row}`).val(isJasa ? 1 : itemQty);
            $(`#price_${row}`).val(new Intl.NumberFormat('id-ID').format(itemPrice));
            // calculate total for this row
            let qty = parseFloat($(`#qty_${row}`).val()) || 0;
            let total = qty * parseFloat(itemPrice);
            $(`#total_${row}`).val(new Intl.NumberFormat('id-ID').format(total));
            updateGrandTotal();
        }

        // Update total price based on quantity
        $(document).on('input', '.qty-input', function() {
            let id = $(this).attr('id');
            let row = id.split('_')[1];
            let qty = parseFloat($(this).val()) || 0;
            let priceText = $(`#price_${row}`).val() || '0';
            let price = parseFloat(priceText.toString().replace(/\./g, '').replace(/,/g, '.')) || 0;
            let total = qty * price;
            $(`#total_${row}`).val(new Intl.NumberFormat('id-ID').format(total));
            updateGrandTotal();
        });

        function updateGrandTotal() {
            let grand = 0;
            $('#purchaseDetails tr').each(function(index) {
                let row = index + 1;
                let totalText = $(`#total_${row}`).val() || '0';
                let total = parseFloat(totalText.toString().replace(/\./g, '').replace(/,/g, '.')) || 0;
                grand += total;
            });
            $('#grand_total_display').text(new Intl.NumberFormat('id-ID').format(grand));
            $('#grand_total').val(grand);
        }

        // Attach validation to the save button
        $('#submit').on('click', function(e) {
            e.preventDefault();

            const formElement = e.currentTarget.form;
            let isValid = true;
            let errorMessage = '';
            let codes = [];
            let isDuplicate = false;

            // Loop through each row to validate quantities
            $('#purchaseDetails tr').each(function() {
                let qtyInput = parseFloat($(this).find('input[name="qty[]"]').val());
                let qtyExisting = parseFloat($(this).find('input[name="qty_exist[]"]').val());
                let code = $(this).find('select[name="itemCode[]"]').val();
                let itemName = $(this).find('select[name="itemCode[]"] option:selected').data('name');
                let itemType = $(this).find('input[name="item_type[]"]').val() || $(this).find(
                    'select[name="itemCode[]"] option:selected').data('type');
                // Get the item name

                if (itemType !== 'jasa' && qtyInput > qtyExisting) {
                    isValid = false;
                    errorMessage =
                        `The quantity for the item "${itemName}" cannot exceed the existing quantity.`;
                    return false; // Exit loop early
                }

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
                swal({
                    title: "{{ __('general.warning') }}",
                    text: errorMessage,
                    icon: "warning",
                })
                return
            }

            swal({
                title: "{{ __('general.are_you_sure') }}",
                text: "Save this maintenance data?",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willSave) => {
                if (willSave && formElement) {
                    HTMLFormElement.prototype.submit.call(formElement);
                }
            });
        });


        $('#save').on('click', function() {
            if (!selectedWarehouse) {
                swal({
                    title: "{{ __('general.warning') }}",
                    text: "Please select a warehouse first",
                    icon: "warning",
                });
                return;
            }

            let row = $('#purchaseDetails tr').length + 1;

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
                                    <option selected="" disabled="" value="">{{ __('general.choose') }}...</option>
                                </select>
                                <input type="hidden" name="item_type[]" id="item_type_${row}" value="">
                            </td>
                            <td>
                                <input class="form-control" type="number" readony value="0" name="qty_exist[]" readonly id="qty_exist_${row}">
                            </td>
                            <td>
                                <input class="form-control qty-input" type="number" name="qty[]" id="qty_${row}" required min="0.01" step="0.01" value="1">
                            </td>
                            <td>
                                <input class="form-control text-end" type="text" name="price[]" id="price_${row}" readonly value="0">
                            </td>
                            <td>
                                <input class="form-control text-end" type="text" name="total[]" id="total_${row}" readonly value="0">
                            </td>
                          </tr>`;
            $('#purchaseDetails').append(newRow);

            let html = '<option selected="" disabled="" value="">{{ __('general.choose') }}...</option>';

            dataItem.forEach(i => {
                html +=
                    `<option value="${i.code}" data-name="${i.name}" data-qty="${i.stock}" data-price="${i.price}" data-type="${i.type}">${i.code} - ${i.name}</option>`;
            });

            $(`#itemCode_${row}`).html(html);
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
