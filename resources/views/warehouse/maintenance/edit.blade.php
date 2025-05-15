@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => $title,
    'secondSegment' => 'Add',
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
@endpush

@section('content')
    <form method="post" method="post" action="{{ route($view . 'update', $data->id) }}">
        @csrf
        @method('PUT')
        @csrf
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>{{ $title }} Edit Data</h4>
                    <a href="{{ route($view . 'index') }}" class="btn btn-info">Back To List</a>
                </div>
                <div class="card-body col-md-6">
                    <div class="row g-3">
                        <div class="row">
                            <div class="col-md-12">
                                <label class="form-label" for="code">Code <i
                                        class="icofont icofont-warning-alt text-danger"></i></label>
                                <input class="form-control" name="code" type="text" value="{{ $data->code }}"
                                    required placeholder="Code">
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <label class="form-label" for="name">Date <i
                                        class="icofont icofont-warning-alt text-danger"></i></label>
                                <input class="form-control" name="date" id="datetime-local" type="date" required
                                    placeholder="Order Date" value="{{ $data->date }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Time</label>
                                <input class="form-control digits" name="time" type="time"
                                    value="{{ $data->time }}">
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <label class="form-label" for="fleetCode">Fleet <i
                                        class="icofont icofont-warning-alt text-danger"></i></label>
                                <select class="js-example-basic-single" name="fleetCode" id="fleetCode" required>
                                    <option selected="" disabled="" value="">Choose...</option>
                                    @foreach ($fleet as $item)
                                        <option value="{{ $item->code }}"
                                            {{ $data->fleetCode == $item->code ? 'selected' : '' }}>
                                            {{ $item->plateNumber . ' - ' . $item->type->name }}
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
                    <h4>Detail Maintenance</h4>
                    <button class="btn btn-primary" type="button" id="save">Add Data</button>
                </div>
                <div class="card-body col-md-12">
                    @include('partials.alert')
                    <table class="display " id="dt">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Code</th>
                                <th>Item/Part</th>
                                <th style="width: 10%">Qty Existing</th>
                                <th style="width: 10%">Qty</th>
                            </tr>
                        </thead>
                        <tbody id="purchaseDetails">
                            @foreach ($data->details as $it)
                                <tr>
                                    <td>
                                        <ul class="action">
                                            <li class="delete"><a
                                                    href="javascript:deleteMaintenanceDetail('{{ $it->id }}')"><i
                                                        class="icon-trash"></i></a>
                                            </li>
                                        </ul>
                                    </td>
                                    <td>
                                        <input type="hidden" name="maintenanceDetailCode[]" value="{{ $it->code }}">

                                        <select class="js-example-basic-single" name="itemCode[]" id="itemCode_1" required
                                            onchange="loadItemDetails({{ $loop->iteration }})">
                                            <option selected="" disabled="" value="">Choose...</option>

                                            @foreach ($stock as $item)
                                                <option value="{{ $item->item->code }}"
                                                    data-name="{{ $item->item->name }}" data-qty="{{ $item->stockIn }}"
                                                    {{ $item->itemCode == $it->itemCode ? 'selected' : '' }}>
                                                    {{ $item->item->code . ' - ' . $item->item->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <div class="me-5">
                                            <input class="form-control" type="text" value="{{ $it->item->name }}"
                                                id="itemName_{{ $loop->iteration }}" required readonly>
                                        </div>
                                    </td>
                                    <td>
                                        <input class="form-control w-50 " type="number" name="qty_exist[]"
                                            id="qty_exist_{{ $loop->iteration }}" readonly
                                            value="{{ $it->stock->stockIn - $it->stock->stockOut }}">
                                    </td>
                                    <td>
                                        <input class="form-control w-75" type="number" name="qty[]"
                                            id="qty_{{ $loop->iteration }}" required min="0"
                                            value="{{ $it->qty }}">
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
                        <button class="btn btn-primary" id="submit" type="submit">Save Changes</button>
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
        let dataItem;
        $(document).ready(function() {
            $('#dt').DataTable();
            // Initialize Select2 on the first row
            $(".js-example-basic-single").select2();

            dataItem = @json($stock)

        });

        // Load item details (name, price)
        function loadItemDetails(row) {
            let itemCode = $(`#itemCode_${row}`).val();
            let itemName = $(`#itemCode_${row} option:selected`).data('name');
            let itemQty = $(`#itemCode_${row} option:selected`).data('qty');

            $(`#itemName_${row}`).val(itemName);
            $(`#qty_exist_${row}`).val(itemQty);
            // updateTotalPrice(row);
        }

        function deleteMaintenanceDetail(id) {
            var url = '{{ route('warehouse.maintenance-detail.destroy', ':id') }}'; // Use placeholder ':id'
            url = url.replace(':id', id); // Replace the placeholder with actual id

            $('#delete-form').attr('action', url);

            swal({
                title: "Are you sure?",
                text: "Want to delete this data?",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    $('#delete-form').submit();
                } else {
                    swal("Your data is safe!");
                }
            });
        }

        // Update total price based on quantity
        function updateTotalPrice(row) {
            let qty = $(`#qty_${row}`).val();
            let price = $(`#price_${row}`).val();
            let totalPrice = qty * parseInt(price.replace(/\./g, ''));

            totalPrice = Intl.NumberFormat('id-ID').format(Math.round(totalPrice))

            $(`#totalPrice_${row}`).val(totalPrice);
        }

        // Attach validation to the save button
        $('#submit').on('click', function(e) {
            let isValid = true;
            let errorMessage = '';
            let codes = [];
            let isDuplicate = false;

            // Loop through each row to validate quantities
            $('#purchaseDetails tr').each(function() {
                let qtyInput = parseInt($(this).find('input[name="qty[]"]').val());
                let qtyExisting = parseInt($(this).find('input[name="qty_exist[]"]').val());
                let code = $(this).find('select[name="itemCode[]"]').val();
                let itemName = $(this).find('input[id^="itemName_"]').val(); // Get the item name

                if (qtyInput > qtyExisting) {
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
                e.preventDefault();

                swal({
                    title: "Warning",
                    text: errorMessage,
                    icon: "warning",
                })
                return
            }
        });

        // Add new row to purchase detail
        $('#save').on('click', function() {
            let row = $('#purchaseDetails tr').length + 1;

            let newRow = `<tr>
                            <td class="remove-btn">
                                  <ul class="action">
                                    <li class="delete"><a href="javascript:removeDetailRow(${row})"><i class="icon-trash"></i></a></li>
                                </ul>
                            </td>
                            <td>
                                <select class="js-example-basic-single" name="itemCode[]" id="itemCode_${row}" required onchange="loadItemDetails(${row})">
                                    <option selected="" disabled="" value="">Choose...</option>
                                </select>
                            </td>
                            <td>
                                <div class="me-5">
                                    <input class="form-control" type="text" id="itemName_${row}" required readonly>
                                </div>
                            </td>
                            <td>
                                <input class="form-control w-50" type="number" readony value="0" name="qty_exist[]" readonly id="qty_exist_${row}">
                            </td>
                            <td>
                                <input class="form-control w-75" type="number" name="qty[]" id="qty_${row}" required value="1">
                            </td>
                          </tr>`;
            $('#purchaseDetails').append(newRow);

            let html = '<option selected="" disabled="" value="">Choose...</option>';

            dataItem.forEach(i => {
                html +=
                    `<option value="${i.item.code}" data-name="${i.item.name}" data-qty="${i.stockIn - i.stockOut}">${i.item.code} - ${i.item.name}</option>`;
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
