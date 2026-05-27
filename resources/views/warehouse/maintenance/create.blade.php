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
            <!-- Card 1: Maintenance Information -->
            <div class="card shadow-sm border-0 mb-4 rounded-3">
                <div class="card-header bg-light border-bottom d-flex justify-content-between align-items-center py-3">
                    <h5 class="m-0 fw-bold text-dark"><i class="mdi mdi-tools me-2 text-primary"></i>{{ $title }} - {{ __('general.add_data') }}</h5>
                    <a href="{{ route($view . 'index') }}" class="btn btn-sm btn-outline-primary px-3"><i class="mdi mdi-arrow-left me-1"></i>{{ __('general.back_to_list') }}</a>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-secondary" for="code_display">Code <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="mdi mdi-barcode-scan"></i></span>
                                <input class="form-control bg-light fw-bold" type="text" placeholder="Auto Generated" id="code_display" readonly disabled>
                            </div>
                            <input type="hidden" name="code" id="code_hidden">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold text-secondary" for="datetime-local">{{ __('menu_maintenance.date') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="mdi mdi-calendar"></i></span>
                                <input class="form-control" name="date" id="datetime-local" type="date" required value="{{ now()->toDateString() }}">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold text-secondary" for="time">{{ __('menu_maintenance.time') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="mdi mdi-clock-outline"></i></span>
                                <input class="form-control" name="time" id="time" type="time" required value="{{ now()->setTimezone('Asia/Jakarta')->format('H:i') }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary" for="fleetCode">{{ __('menu_maintenance.fleet') }} <span class="text-danger">*</span></label>
                            <select class="js-example-basic-single form-select" name="fleetCode" id="fleetCode" required>
                                <option selected="" disabled="" value="">{{ __('general.choose') }}...</option>
                                @foreach ($fleet as $item)
                                    <option value="{{ $item->code }}">
                                        {{ $item->plateNumber }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary" for="warehouseCode">Warehouse <span class="text-danger">*</span></label>
                            <select class="js-example-basic-single form-select" name="warehouseCode" id="warehouseCode" required>
                                <option selected="" disabled="" value="">{{ __('general.choose') }}...</option>
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

            <!-- Card 2: Detail Items / Pemeliharaan -->
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-light border-bottom d-flex justify-content-between align-items-center py-3">
                    <h5 class="m-0 fw-bold text-dark"><i class="mdi mdi-format-list-bulleted me-2 text-primary"></i>Detail {{ $title }}</h5>
                    <button class="btn btn-sm btn-primary px-3" type="button" id="save"><i class="mdi mdi-plus-box me-1"></i>{{ __('general.add_data') }}</button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle" id="dt" style="min-width: 900px;">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;" class="text-center">#</th>
                                    <th style="width: 30%;">Item/Part <span class="text-danger">*</span></th>
                                    <th style="width: 25%;">Description / Deskripsi</th>
                                    <th style="width: 12%;" class="text-center">Stock</th>
                                    <th style="width: 10%;" class="text-center">Qty <span class="text-danger">*</span></th>
                                    <th style="text-align: right; width: 10%;">Price</th>
                                    <th style="text-align: right; width: 13%;">Total</th>
                                </tr>
                            </thead>
                            <tbody id="purchaseDetails">
                                <tr>
                                    <td class="remove-btn text-center"></td>
                                    <td>
                                        <select class="js-example-basic-single" name="itemCode[]" id="itemCode_1" required
                                            onchange="loadItemDetails(1)" disabled>
                                            <option selected="" disabled="" value="">
                                                {{ __('general.choose') }}...
                                            </option>
                                        </select>
                                        <input type="hidden" name="item_type[]" id="item_type_1" value="">
                                    </td>
                                    <td>
                                        <input class="form-control" type="text" name="description[]" id="description_1" placeholder="Catatan/Deskripsi...">
                                    </td>
                                    <td>
                                        <input class="form-control text-center bg-light" type="number" name="qty_exist[]" id="qty_exist_1"
                                            readonly value="0">
                                    </td>
                                    <td>
                                        <input class="form-control qty-input text-center fw-bold" type="number" name="qty[]" id="qty_1"
                                            required min="0.5" step="0.5" value="1">
                                    </td>
                                    <td>
                                        <input class="form-control text-end bg-light fw-bold" type="text" name="price[]" id="price_1"
                                            readonly value="0">
                                    </td>
                                    <td>
                                        <input class="form-control text-end bg-light fw-bold" type="text" name="total[]" id="total_1"
                                            readonly value="0">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <div class="card bg-primary-subtle border-0 p-3 rounded-3" style="min-width: 300px;">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-semibold text-primary fs-14">Grand Total</span>
                                <span class="fw-bold text-primary fs-16">Rp <span id="grand_total_display" class="fs-18 fw-bolder">0</span></span>
                            </div>
                            <input type="hidden" name="grand_total" id="grand_total" value="0">
                        </div>
                    </div>

                    <hr class="mt-4 mb-3 text-muted">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route($view . 'index') }}" class="btn btn-outline-secondary px-4"><i class="mdi mdi-arrow-left me-1"></i> Kembali</a>
                        <button class="btn btn-primary px-4 fw-bold" id="submit" type="submit"><i class="mdi mdi-content-save-outline me-1"></i> Simpan Data</button>
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
            $(`#qty_exist_${row}`).val(isJasa ? 1 : parseFloat(itemQty));
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
                            <td class="remove-btn text-center align-middle">
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
                                <input class="form-control" type="text" name="description[]" id="description_${row}" placeholder="Catatan/Deskripsi...">
                            </td>
                            <td>
                                <input class="form-control text-center bg-light" type="number" readonly value="0" name="qty_exist[]" id="qty_exist_${row}">
                            </td>
                            <td>
                                <input class="form-control qty-input text-center fw-bold" type="number" name="qty[]" id="qty_${row}" required min="0.5" step="0.5" value="1">
                            </td>
                            <td>
                                <input class="form-control text-end bg-light fw-bold" type="text" name="price[]" id="price_${row}" readonly value="0">
                            </td>
                            <td>
                                <input class="form-control text-end bg-light fw-bold" type="text" name="total[]" id="total_${row}" readonly value="0">
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
            updateGrandTotal();
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
