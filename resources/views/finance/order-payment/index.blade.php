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

    <link rel="stylesheet" type="text/css" href="../assets/css/vendors/sweetalert2.css">

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
                                <th>No</th>
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

                                    <div class="col-md-12" id="payment-type-container">
                                        <label class="form-label" for="type">
                                            {{ __('menu_order_payment.payment_date') }} <i
                                                class="mdi mdi-information text-danger"></i></label>
                                        <select class="js-example-basic" name="type" id="type" required="">
                                            <option selected="" disabled="" value="">
                                                {{ __('general.choose') }}...</option>
                                            <option value="Full">
                                                Full</option>
                                            <option value="Dp">
                                                Dp</option>

                                        </select>
                                    </div>

                                    <div class="col-md-12 d-none">
                                        <label class="form-label"
                                            for="paymentAmount">{{ __('menu_order_payment.payment_amount') }}
                                            <i class="mdi mdi-information text-danger"></i></label>
                                        <input class="form-control" name="paymentAmount" id="paymentAmount"
                                            type="text" placeholder="{{ __('menu_order_payment.payment_amount') }}"
                                            oninput="formatAngka(this)">
                                    </div>

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

    <script src="../assets/js/sweet-alert/sweetalert.min.js"></script>
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src=" {{ asset('assets/js/select2/select2-custom.js') }}"></script>
    <script src=" {{ asset('assets/js/helper.js') }}"></script>

    {{-- <script src="../assets/js/sweet-alert/app.js"></script> --}}

    <script>
        $(document).ready(function() {
            $('#dt').DataTable({
                "processing": true,
                "serverSide": true,
                "destroy": true,
                "ajax": {
                    "url": "{{ route('dt.order-payment') }}",
                },
                "columns": [{
                        "data": 'action'
                    }, {
                        "data": 'DT_RowIndex'
                    },
                    {
                        "data": 'orderDate'
                    },
                    {
                        "data": 'fleet.plateNumber'
                    },
                    {
                        "data": 'driver.name'
                    },
                    {
                        "data": "shipmentNumber"
                    },
                    {
                        "data": 'customer.name'
                    },
                    {
                        "data": 'route.originLocation.name'
                    },
                    {
                        "data": 'route.destinationLocation.name'
                    },
                    {
                        "data": 'cost'
                    },
                    {
                        "data": 'pph'
                    },
                    {
                        "data": 'paymentAmount'
                    },
                    {
                        "data": 'total'
                    },
                    {
                        "data": 'paymentStatus'
                    }
                ],
                "columnDefs": [{
                        "searchable": false,
                        "targets": [0, 2]
                    },
                    {
                        "orderable": false,
                        "targets": [0, 2]
                    }
                ],
                "order": [
                    [1, 'asc']
                ]
            })
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
            let angka = value.toString().replace(/\./g, "");
            return new Intl.NumberFormat("id-ID").format(angka);
        }

        function getorderPaymentDetail(orderCode) {
            $.get("/ajax/order-detail-payment/" + orderCode, function(data) {
                $('#cost').val(formatAngkaValue(data.cost));
                $('#costHidden').val(data.cost);
                $('#pph').val(formatAngkaValue(data.pph));
                $('#pphHidden').val(data.pph);
                $('#payment').val(formatAngkaValue(data.payment));
                $('#total').val(formatAngkaValue(data.total));
                $('#totalHidden').val(data.total);

                const container = document.getElementById('payment-type-container');
                const existingSelect = document.getElementById('type');
                const existingHidden = document.getElementById('hidden-payment-type');
                const label = container.querySelector('label[for="type"]');

                if (data.payment > 0) {
                    // Destroy Select2 dan hapus select jika ada
                    if (existingSelect) {
                        if ($(existingSelect).hasClass('select2-hidden-accessible')) {
                            $(existingSelect).select2('destroy');
                        }
                        existingSelect.remove();
                    }

                    // Sembunyikan label
                    if (label) label.style.display = 'none';

                    // Tambah input hidden jika belum ada
                    if (!existingHidden) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'type';
                        input.value = 'Full';
                        input.id = 'hidden-payment-type';
                        container.appendChild(input);
                    }
                } else {
                    // Hapus input hidden jika ada
                    if (existingHidden) existingHidden.remove();

                    // Tampilkan kembali label
                    if (label) label.style.display = '';

                    // Tambahkan kembali select jika belum ada
                    if (!existingSelect) {
                        const select = document.createElement('select');
                        select.className = 'form-control js-example-basic';
                        select.name = 'type';
                        select.id = 'type';
                        select.required = true;
                        select.innerHTML = `
                            <option selected disabled value="">Choose...</option>
                            <option value="Full">Full</option>
                            <option value="Dp">Dp</option>
                        `;
                        container.appendChild(select);

                        // Inisialisasi Select2 ulang
                        $(select).select2({
                            dropdownParent: $('#payment-modal'),
                            width: "100%",
                        });
                    }
                }


            });
        }


        function validateAndFormatForm(paymentAmountId) {
            const type = document.getElementById("type").value;

            if (type === "Dp") {
                const total = parseInt(document.getElementById("total").value.replace(/\./g, ""));
                const paymentAmountInput = document.getElementById(paymentAmountId);
                const paymentAmount = parseInt(paymentAmountInput.value.replace(/\./g, ""));

                if (paymentAmount > total) {
                    swal({
                        title: "{{ __('general.warning') }}",
                        text: "Payment Amount tidak boleh lebih besar dari Total",
                        icon: "warning",
                    })
                    return false; // Stop submit
                }

                // Format angka: hilangkan titik sebelum submit
                paymentAmountInput.value = paymentAmount;
            }

            return true; // Lanjut submit
        }

        $('#type').on('change', function() {
            const selectedType = $(this).val();

            if (selectedType === 'Dp') {
                $('#paymentAmount').attr('required', true); // wajib diisi
                $('#paymentAmount').closest('.col-md-12').removeClass('d-none'); // tampilkan div-nya
            } else {
                $('#paymentAmount').removeAttr('required'); // hapus wajib isi
                $('#paymentAmount').val(''); // kosongkan input
                $('#paymentAmount').closest('.col-md-12').addClass('d-none'); // sembunyikan div-nya
            }
        });
    </script>
@endpush
