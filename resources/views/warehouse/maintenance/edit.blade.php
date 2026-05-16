@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => $title,
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
    <form method="post" method="post" action="{{ route($view . 'update', $data->id) }}">
        @csrf
        @method('PUT')
        @csrf
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
                                <label class="form-label" for="code">Code <i
                                        class="icofont icofont-warning-alt text-danger"></i></label>
                                <input class="form-control" name="code" type="text" value="{{ $data->code }}"
                                    required placeholder="Code" readonly disabled>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <label class="form-label" for="name">{{ __('menu_maintenance.date') }} <i
                                        class="icofont icofont-warning-alt text-danger"></i></label>
                                <input class="form-control" name="date" id="datetime-local" type="date" required
                                    placeholder="Order Date" value="{{ $data->date }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">{{ __('menu_maintenance.time') }}</label>
                                <input class="form-control digits" name="time" type="time"
                                    value="{{ $data->time }}">
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
                                        <option value="{{ $item->code }}"
                                            {{ $data->fleetCode == $item->code ? 'selected' : '' }}>
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
                                        <option value="{{ $item->code }}"
                                            {{ $data->warehouseCode == $item->code ? 'selected' : '' }}>
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
                    @include('partials.alert')
                    {{-- <td>
                                        <div class="mx-5">
                                            <input class="form-control" type="text" value="{{ $it->item->name }}"
                            id="itemName_{{ $loop->iteration }}" required readonly>
            </div>
            </td> --}}
                    <table class="table table-sm" id="dt">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Item/Part</th>
                                {{-- <th>Item/Part</th> --}}
                                <th>Stock</th>
                                <th>Qty</th>
                                <th style="text-align: right">Price</th>
                                <th style="text-align: right">Total</th>
                            </tr>
                        </thead>
                        <tbody id="purchaseDetails">
                            @foreach ($data->details as $it)
                                <tr>
                                    <td>
                                        <a href="javascript:deleteMaintenanceDetail('{{ $it->id }}')"
                                            class="btn btn-icon btn-sm bg-danger-subtle" data-bs-toggle="tooltip"
                                            title="Delete">
                                            <i class="mdi mdi-delete fs-14 text-danger"></i>
                                        </a>
                                    </td>
                                    <td>
                                        <input type="hidden" name="maintenanceDetailCode[]"
                                            value="{{ $it->code }}">

                                        <select class="js-example-basic-single" name="itemCode[]"
                                            id="itemCode_{{ $loop->iteration }}"
                                            required onchange="loadItemDetails({{ $loop->iteration }})">
                                            <option selected="" disabled="" value="">
                                                {{ __('general.choose') }}...
                                            </option>

                                            <option value="{{ $it->item->code }}" data-name="{{ $it->item->name }}"
                                                data-qty="0" data-price="{{ $it->item->price }}"
                                                data-type="{{ $it->item->type ?? '' }}" selected>
                                                {{ $it->item->code . ' - ' . $it->item->name }}
                                            </option>
                                        </select>
                                        <input type="hidden" name="item_type[]" id="item_type_{{ $loop->iteration }}"
                                            value="{{ $it->item->type ?? '' }}">
                                    </td>

                                    <td>
                                        <input class="form-control  " type="number" name="qty_exist[]"
                                            id="qty_exist_{{ $loop->iteration }}" readonly value="0">
                                    </td>
                                    <td>
                                        <input class="form-control qty-input" type="number" name="qty[]"
                                            id="qty_{{ $loop->iteration }}" required min="1" step="1" onkeypress="return event.charCode >= 48 && event.charCode <= 57"
                                            value="{{ (int) $it->qty }}">
                                        <input type="hidden" name="original_qty[]" value="{{ (int) $it->qty }}">
                                    </td>
                                    <td>
                                        <input class="form-control text-end" type="text" name="price[]"
                                            id="price_{{ $loop->iteration }}" readonly
                                            value="{{ number_format($it->price ?? ($it->item->price ?? 0), 0, ',', '.') }}">
                                    </td>
                                    <td>
                                        <input class="form-control text-end" type="text" name="total[]"
                                            id="total_{{ $loop->iteration }}" readonly
                                            value="{{ number_format($it->total ?? $it->qty * ($it->price ?? ($it->item->price ?? 0)), 0, ',', '.') }}">
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>

                    <div class="d-flex justify-content-end mt-2">
                        <div>
                            <div class="fw-bold">Grand Total: <span
                                    id="grand_total_display">{{ number_format($data->grand_total ?? 0, 0, ',', '.') }}</span>
                            </div>
                            <input type="hidden" name="grand_total" id="grand_total"
                                value="{{ $data->grand_total ?? 0 }}">
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

    <script>
        let dataItem = [];
        let selectedWarehouse = null;

        $(document).ready(function() {
            $('#dt').DataTable();

            // Load items for existing warehouse
            selectedWarehouse = $('#warehouseCode').val();
            if (selectedWarehouse) {
                loadItemsForWarehouse(selectedWarehouse);
            }
        });

        // When warehouse is selected, load stock items
        $('#warehouseCode').on('change', function() {
            selectedWarehouse = $(this).val();

            if (!selectedWarehouse) {
                return;
            }

            loadItemsForWarehouse(selectedWarehouse);
        });

        function loadItemsForWarehouse(warehouseCode) {
            $.ajax({
                url: '/ajax/maintenance-stock-by-warehouse',
                method: 'GET',
                data: {
                    warehouseCode: warehouseCode
                },
                success: function(response) {
                    if (response.success) {
                        dataItem = response.data;

                        // Update existing qty_exist fields
                        $('#purchaseDetails tr').each(function(index) {
                            let row = index + 1;
                            let itemCode = $(`#itemCode_${row}`).val();
                            if (itemCode) {
                                let foundItem = dataItem.find(i => i.code === itemCode);
                                let itemQty = foundItem ? foundItem.stock : 0;
                                let isJasa = foundItem && foundItem.type === 'jasa';
                                $(`#item_type_${row}`).val(isJasa ? 'jasa' : 'part');
                                $(`#qty_exist_${row}`).val(isJasa ? 1 : parseInt(itemQty));
                            }
                        });
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
        }

        // Load item details (name, price)
        function loadItemDetails(row) {
            let itemCode = $(`#itemCode_${row}`).val();
            let foundItem = dataItem.find(i => i.code === itemCode || (i.item && i.item.code === itemCode));
            let itemQty = foundItem ? (foundItem.stock ?? (foundItem.stockIn - foundItem.stockOut || 0)) : 0;
            let itemPrice = foundItem ? (foundItem.price ?? (foundItem.item ? foundItem.item.price : 0)) : 0;
            let itemType = foundItem ? foundItem.type : $(`#itemCode_${row} option:selected`).data('type');

            $(`#item_type_${row}`).val(itemType === 'jasa' ? 'jasa' : 'part');
            $(`#qty_exist_${row}`).val(itemType === 'jasa' ? 1 : parseInt(itemQty));
            $(`#price_${row}`).val(new Intl.NumberFormat('id-ID').format(itemPrice));
            let qty = parseFloat($(`#qty_${row}`).val()) || 0;
            let total = qty * parseFloat(itemPrice || 0);
            $(`#total_${row}`).val(new Intl.NumberFormat('id-ID').format(total));
            updateGrandTotal();
        }

        function deleteMaintenanceDetail(id) {
            var url = '{{ route('warehouse.maintenance-detail.destroy', ':id') }}'; // Use placeholder ':id'
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

        // Update total price based on quantity (live)
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

            // Loop through each row to validate quantities
            $('#purchaseDetails tr').each(function() {
                let qtyInput = parseFloat($(this).find('input[name="qty[]"]').val());
                let qtyExisting = parseFloat($(this).find('input[name="qty_exist[]"]').val());
                let originalQty = parseInt($(this).find('input[name="original_qty[]"]').val()) || 0;

                let code = $(this).find('select[name="itemCode[]"]').val();
                let itemName = $(this).find('select[name="itemCode[]"] option:selected').data('name');
                let itemType = $(this).find('input[name="item_type[]"]').val() || $(this).find('select[name="itemCode[]"] option:selected').data('type');

                let totalAvailable = qtyExisting + originalQty;

                if (itemType !== 'jasa' && qtyInput > totalAvailable) {
                    isValid = false;
                    errorMessage =
                        `"${itemName}" quantity cannot exceed available stock (${totalAvailable}).`;
                    return false;
                }

                // Check for duplicate codes
                if (codes.includes(code)) {
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
                                <input class="form-control qty-input" type="number" name="qty[]" id="qty_${row}" required min="1" step="1" onkeypress="return event.charCode >= 48 && event.charCode <= 57" value="1">
                                <input type="hidden" name="original_qty[]" value="0">

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
                // support both shapes: {code, name, stock, price} and {item:{code,name}, stockIn, stockOut}
                let code = i.code || (i.item && i.item.code);
                let name = i.name || (i.item && i.item.name);
                let stock = parseInt(i.stock ?? (i.stockIn - i.stockOut || 0));
                let price = i.price ?? (i.item ? i.item.price : 0);
                html +=
                    `<option value="${code}" data-name="${name}" data-qty="${stock}" data-price="${price}" data-type="${i.type ?? (i.item ? i.item.type : '')}">${code} - ${name}</option>`;
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
