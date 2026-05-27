@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Finance',
    'secondSegment' => $title,
])

@push('style')
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

    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/select2.css') }}">

    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/custom-select2.css') }}">
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }} Data</h4>
            </div>
            <div class="card-body">
                @include('partials.alert')
                <div class="table-responsive custom-scrollbar">
                    <table class="table table-striped w-100 nowrap" id="dt">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Aksi</th>
                                <th>No Order</th>
                                <th>{{ __('menu_vendor_payment.order_date') }}</th>
                                <th>{{ __('menu_vendor_payment.plate_number') }}</th>
                                <th>{{ __('menu_vendor_payment.driver') }}</th>
                                <th>{{ __('menu_vendor_payment.shipment_no') }}</th>
                                <th>{{ __('menu_vendor_payment.customer') }}</th>
                                <th>{{ __('menu_vendor_payment.origin') }}</th>
                                <th>{{ __('menu_vendor_payment.destination') }}</th>
                                <th>{{ __('menu_order_payment.cost') }}</th>
                                <th>PPH</th>
                                <th>{{ __('menu_order_payment.payment_amount') }}</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <form class="row g-3" method="post" action="{{ route($view . 'store') }}"
            onsubmit="return validateAndFormatForm('paymentAmount')">
            @csrf
            <div class="modal fade bd-example-modal-lg" id="payment-modal" tabindex="-1" role="dialog"
                aria-labelledby="myLargeModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <input type="hidden" name="orderCode" id="orderCode">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">{{ __('menu_vendor_payment.payment_data') }}
                            </h4>
                            <button class="btn-close py-0" type="button" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="card">
                            <div class="card-body col-md-12">
                                <div class="row g-3">

                                    <div class="col-md-12">
                                        <label class="form-label"
                                            for="cost">{{ __('menu_order_payment.cost') }}</label>
                                        <input type="hidden" name="cost" id="costHidden">
                                        <input class="form-control" id="cost" type="text" placeholder="Cost"
                                            readonly disabled>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label" for="pph">PPH</label>
                                        <input type="hidden" name="pph" id="pphHidden">
                                        <input class="form-control" id="pph" type="text" placeholder="PPH" readonly
                                            disabled>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label"
                                            for="payment">{{ __('menu_order_payment.payment') }}</label>
                                        <input class="form-control" id="payment" type="text" placeholder="Payment"
                                            readonly disabled>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label" for="total">Total </label>
                                        <input type="hidden" name="total" id="totalHidden">
                                        <input class="form-control" name="total" id="total" type="text"
                                            placeholder="Total" readonly disabled>
                                    </div>

                                    <div class="col-md-12">

                                        <label class="form-label"
                                            for="userBankCode">{{ __('menu_order_payment.user_bank') }} <i
                                                class="mdi mdi-information text-danger"></i></label>


                                        <select class="js-example-basic" name="userBankCode" id="userBankCode" required>
                                            <option value="">{{ __('general.choose') }}...</option>
                                            @foreach ($userBank as $item)
                                                <option value="{{ $item->code }}">
                                                    {{ $item->accountNumber . ' - ' . $item->bank->name . ' - ' . $item->accountName }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <div id="listUserBank">

                                        </div>
                                    </div>


                                    <div class="col-md-12">
                                        <label class="form-label"
                                            for="itemNameModal">{{ __('menu_order_payment.payment_date') }}
                                            <i class="mdi mdi-information text-danger"></i></label>
                                        <input class="form-control" name="date" id="date" type="date"
                                            placeholder="Payment Date" required>
                                    </div>

                                    {{-- 
                                    <div class="col-md-12">
                                        <label class="form-label"
                                            for="amount">{{ __('menu_vendor_payment.amount') }}</label>
                                        <input class="form-control" name="amount" id="amount" type="text"
                                            oninput="formatAngka(this)"
                                            placeholder="{{ __('menu_vendor_payment.amount') }}" required>
                                    </div> --}}

                                    <!-- Use Tax Switch (PPH) -->
                                    <div class="col-md-12 mb-2" id="pph-checkbox-container" style="display: none;">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" id="usePph" checked>
                                            <label class="form-check-label" for="usePph">Gunakan Pajak (PPH)</label>
                                        </div>
                                    </div>

                                    <!-- Visible Nominal Input (like vendor-payment) -->
                                    <div class="col-md-12">
                                        <label class="form-label" for="nominalInput">Nominal Pembayaran <i
                                                class="mdi mdi-information text-danger"></i></label>
                                        <input class="form-control" id="nominalInput" type="text"
                                            placeholder="Masukkan Nominal Pembayaran" oninput="formatAngka(this)" required>
                                    </div>

                                    <!-- Hidden Inputs for type and paymentAmount expected by controller -->
                                    <input type="hidden" name="type" id="type" value="Full">
                                    <input type="hidden" name="paymentAmount" id="paymentAmountHidden">

                                    <div class="col-md-12">
                                        <label class="form-label"
                                            for="description">{{ __('menu_vendor_payment.description') }}</label>
                                        <textarea class="form-control" name="description" id="description" rows="3"
                                            placeholder="{{ __('menu_vendor_payment.description') }}"></textarea>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="modal-footer justify-content-start">
                            <button class="btn btn-primary" type="submit">{{ __('general.save') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>



    <form id="delete-form" method="post">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('script')
    <script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>

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
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src=" {{ asset('assets/js/select2/select2-custom.js') }}"></script>
    <script src=" {{ asset('assets/js/helper.js') }}"></script>

    <script>
        let currentCost = 0;
        let originalPph = 0;
        let currentPaid = 0;

        $(document).ready(function() {
            $('#dt').DataTable({
                "processing": true,
                "serverSide": true,
                "destroy": true,
                "ajax": {
                    "url": "{{ route('dt.order-payment') }}",
                },
                "columns": [
                    { "data": 'DT_RowIndex' },
                    { "data": 'action', "orderable": false, "searchable": false },
                    { "data": 'code' },
                    { "data": 'orderDate' },
                    { "data": 'fleet.plateNumber' },
                    { "data": 'driver.name' },
                    { "data": 'shipmentNumber' },
                    { "data": 'customer.name' },
                    { "data": 'route.originLocation.name' },
                    { "data": 'route.destinationLocation.name' },
                    { "data": 'cost' },
                    { "data": 'pph' },
                    { "data": 'paymentAmount' },
                    { "data": 'total' },
                    { "data": 'paymentStatus' }
                ],
                "columnDefs": [{
                        "searchable": false,
                        "targets": [0, 1]
                    },
                    {
                        "orderable": false,
                        "targets": [0, 1]
                    }
                ],
                "order": [
                    [2, 'asc']
                ]
            });
        });

        function showModal(code) {
            $('#payment-modal').modal('show');
            $('.js-example-basic').select2({
                dropdownParent: $('#payment-modal'),
                width: "100%",
            });
            $('#orderCode').val(code);
            getorderPaymentDetail(code);
        }

        function formatAngkaValue(value) {
            if (value == null) return ""; // hindari error kalau undefined/null
            let angka = Math.round(value).toString().replace(/\./g, "");
            return new Intl.NumberFormat("id-ID").format(angka);
        }

        function getorderPaymentDetail(orderCode) {
            $.get("/ajax/order-detail-payment/" + orderCode, function(data) {
                currentCost = parseFloat(data.cost) || 0;
                originalPph = parseFloat(data.pph) || 0;
                currentPaid = parseFloat(data.payment) || 0;

                $('#cost').val(formatAngkaValue(currentCost));
                $('#costHidden').val(currentCost);

                // Show/hide usePph container based on whether there is PPH in the data
                if (originalPph > 0) {
                    $('#pph-checkbox-container').show();
                    $('#usePph').prop('checked', true);
                } else {
                    $('#pph-checkbox-container').hide();
                    $('#usePph').prop('checked', false);
                }

                updateCalculatedTotal();
            });
        }

        function updateCalculatedTotal() {
            let activePph = 0;
            if ($('#usePph').is(':checked')) {
                activePph = originalPph;
            }

            let sisaTagihan = currentCost + activePph - currentPaid;
            if (sisaTagihan < 0) sisaTagihan = 0;

            $('#pph').val(formatAngkaValue(activePph));
            $('#pphHidden').val(activePph);
            $('#payment').val(formatAngkaValue(currentPaid));
            $('#total').val(formatAngkaValue(sisaTagihan));
            $('#totalHidden').val(sisaTagihan);

            // Auto-fill nominal input with the sisaTagihan by default
            $('#nominalInput').val(formatAngkaValue(sisaTagihan));
        }

        // Handle PPH switch change
        $(document).on('change', '#usePph', function() {
            updateCalculatedTotal();
        });

        function validateAndFormatForm(paymentAmountId) {
            const total = parseInt(document.getElementById("totalHidden").value) || 0;
            const nominalInput = document.getElementById("nominalInput");
            const paymentAmount = parseInt(nominalInput.value.replace(/\./g, "")) || 0;

            if (paymentAmount <= 0) {
                swal({
                    title: "{{ __('general.warning') }}",
                    text: "Nominal pembayaran harus lebih besar dari 0",
                    icon: "warning",
                });
                return false;
            }

            if (paymentAmount > total) {
                swal({
                    title: "{{ __('general.warning') }}",
                    text: "Nominal pembayaran tidak boleh lebih besar dari Total Sisa Tagihan",
                    icon: "warning",
                });
                return false; // Stop submit
            }

            // Set hidden fields
            if (paymentAmount === total) {
                document.getElementById("type").value = "Full";
                document.getElementById("paymentAmountHidden").value = total;
            } else {
                document.getElementById("type").value = "Dp";
                document.getElementById("paymentAmountHidden").value = paymentAmount;
            }

            return true; // Lanjut submit
        }
    </script>
@endpush
