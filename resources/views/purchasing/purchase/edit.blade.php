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
                                <label class="form-label" for="code">Code <i
                                        class="icofont icofont-warning-alt text-danger"></i></label>
                                <input class="form-control" name="code" type="text" readonly disabled
                                    placeholder="Code" value="{{ $data->code }}">
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <label class="form-label" for="name">{{ __('menu_purchase.date') }} <i
                                        class="icofont icofont-warning-alt text-danger"></i></label>
                                <input class="form-control" name="date" id="datetime-local" type="date" required
                                    placeholder="Order Date" value="{{ $data->date }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">{{ __('menu_purchase.time') }}</label>
                                <input class="form-control digits" name="time" type="time"
                                    value="{{ $data->time }}">
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
                                        <option value="{{ $item->code }}"
                                            {{ $data->supplierCode == $item->code ? 'selected' : '' }}>{{ $item->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <label class="form-label" for="name">{{ __('menu_purchase.due_date') }}<i
                                        class="icofont icofont-warning-alt text-danger"></i></label>
                                <input class="form-control" name="dueDate" id="datetime-local" type="date" required
                                    value="{{ $data->dueDate }}" placeholder="{{ __('menu_purchase.due_date') }}">
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Detail {{ $title }}</h4>
                    @if ($data->status == 0)
                        <button class="btn btn-primary" type="button" id="save">{{ __('general.add_data') }}</button>
                    @endif
                </div>
                <div class="card-body col-md-12">
                    @include('partials.alert')
                    <table class="table table-sm" id="dt">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Item/Part</th>
                                <th>Qty</th>
                                <th>{{ __('menu_purchase.prices') }}</th>
                                <th>{{ __('menu_purchase.total_prices') }}</th>
                            </tr>
                        </thead>
                        <tbody id="purchaseDetails">

                            @foreach ($data->details as $item)
                                <tr>
                                    {{-- <td class="remove-btn"></td> --}}
                                    <td>
                                        @if ($data->status == 0)
                                            <a href="javascript:deletePurchaseDetail('{{ $item->id }}')"
                                                class="btn btn-icon btn-sm bg-danger-subtle" data-bs-toggle="tooltip"
                                                title="Delete">
                                                <i class="mdi mdi-delete fs-14 text-danger"></i>
                                            </a>
                                        @endif
                                    </td>
                                    <td>
                                        <input type="hidden" name="purchaseDetailCode[]" value="{{ $item->code }}">

                                        <select class="js-example-basic-single" name="itemCode[]"
                                            id="itemCode_{{ $loop->iteration }}" required
                                            onchange="loadItemDetails({{ $loop->iteration }})">
                                            @foreach ($items as $it)
                                                <option value="{{ $it->code }}" data-name="{{ $it->name }}"
                                                    data-price="{{ $it->price }}"
                                                    {{ $item->itemCode == $it->code ? 'selected' : '' }}>
                                                    {{ $it->code . ' - ' . $it->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td>
                                        <input class="form-control" type="number" min="0" name="qty[]"
                                            id="qty_{{ $loop->iteration }}" value="{{ $item->qty }}"
                                            onchange="updateTotalPrice({{ $loop->iteration }})">
                                    </td>
                                    <td>
                                        <input class="form-control" type="text" name="price[]"
                                            id="price_{{ $loop->iteration }}" oninput="formatAngka(this)" required
                                            value="{{ number_format($item->price, 0, ',', '.') }}"
                                            onchange="updateTotalPrice({{ $loop->iteration }})">
                                    </td>
                                    <td>
                                        <input class="form-control" type="text"
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
                        @if ($data->status == 0)
                            <button class="btn btn-primary" id="submit"
                                type="submit">{{ __('general.save_changes') }}</button>
                        @endif
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
        let dataItem;
        let itemsData = @json($items);

        $(document).ready(function() {
            $('#dt').DataTable();

            // let supplierCode = $('#supplierCode').val();

            dataItem = <?php echo json_encode($items); ?>;

            // itemBySupplier(supplierCode)
        });

        function deletePurchaseDetail(id) {
            var url = '{{ route('purchasing.purchase-detail.destroy', ':id') }}'; // Use placeholder ':id'
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
                for (let i = 1; i <= row; i++) {
                    $(`#itemCode_${i}`).html(html);
                    $(`#itemName_${i}`).val("");
                    $(`#qty_${i}`).val(1);
                    $(`#price_${i}`).val(0)
                    $(`#totalPrice_${i}`).val(0)
                    // Reinitialize select2 for newly added select elements
                    $(`#itemCode_${i}`).select2();
                }

                // $('#itemCode_1').html(html);
                // $('#itemName_1').val("");
                // $('#qty_1').val(1);
                // $('#price_1').val(0)
                // $('#totalPrice_1').val(0)

                // Reinitialize select2 for the first row
                // $('#itemCode_1').select2();
            });
        }

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
                console.log(item);
                const price = item.latest_purchase?.price ?? 0;

                options += `<option value="${item.code}" data-name="${item.name}" data-price="${price}">
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
                                    <option selected="" disabled="" value="">{{ __('general.choose') }}...</option>
                                </select>
                            </td>
                            <td>
                                <input class="form-control" type="number" min="1" name="qty[]" id="qty_${row}" value="1" onchange="updateTotalPrice(${row})">
                            </td>
                            <td>
                                <input class="form-control" type="text" id="price_${row}" name="price[]" onchange="updateTotalPrice(${row})" required oninput="formatAngka(this)" value="0">
                            </td>
                            <td>
                                <input class="form-control" type="text" id="totalPrice_${row}" readonly value="0">
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
