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
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }} Data</h4>
                <div class="d-flex gap-2">
                    <button class="btn btn-warning" type="button" id="printMultiPdfBtn" disabled>
                        <i class="mdi mdi-printer"></i> Cetak Terpilih
                    </button>
                    <button class="btn btn-primary" type="button" id="generateNotaBtn" disabled>
                        <i class="mdi mdi-file-document-outline"></i> Generate Nota
                    </button>
                    <button class="btn btn-success" type="button" id="openPaymentModalBtn" disabled>
                        Bayar Order Terpilih
                    </button>
                </div>
            </div>
            <div class="card-body">
                @include('partials.alert')
                <p class="text-muted mb-3" id="selectionSummary">Belum ada order dipilih.</p>
                <div class="table-responsive custom-scrollbar">
                    <table class="table table-striped w-100 nowrap" id="dt">
                        <thead>
                            <tr>
                                <th class="text-center"><input class="form-check-input" type="checkbox"
                                        id="selectAllPayments"></th>
                                <th>Aksi</th>
                                <th>No</th>
                                <th>No Nota</th>
                                <th>{{ __('menu_vendor_payment.order_date') }}</th>
                                <th>{{ __('menu_vendor_payment.plate_number') }}</th>
                                <th>{{ __('menu_vendor_payment.driver') }}</th>
                                <th>{{ __('menu_vendor_payment.shipment_no') }}</th>
                                <th>{{ __('menu_vendor_payment.customer') }}</th>
                                <th>{{ __('menu_vendor_payment.origin') }}</th>
                                <th>{{ __('menu_vendor_payment.destination') }}</th>
                                <th>Tagihan</th>
                                <th>Terbayar</th>
                                <th>Sisa</th>
                                <th>Status Bayar</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <form class="row g-3" method="post" action="{{ route($view . 'store') }}" id="batch-payment-form">
            @csrf
            <div class="modal fade bd-example-modal-lg" id="payment-modal" tabindex="-1" role="dialog"
                aria-labelledby="myLargeModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div id="selectedOrderCodesContainer"></div>
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
                                        <label class="form-label">Jumlah Order Dipilih</label>
                                        <input class="form-control" id="selectedOrderCount" type="text" readonly>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label">Kode Order Dipilih</label>
                                        <textarea class="form-control" id="selectedOrderList" rows="2" readonly></textarea>
                                    </div>

                                    <div class="col-md-12" id="notaNumberContainer" style="display: none;">
                                        <label class="form-label">Nomor Nota Kalender</label>
                                        <input class="form-control" id="selectedNotaNumber" type="text" readonly>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label">Tagihan Vendor</label>
                                        <input class="form-control" id="billingAmount" type="text" readonly>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label">Sisa Tagihan</label>
                                        <input class="form-control" id="remainingAmount" type="text" readonly>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label">Sudah Terbayar</label>
                                        <input class="form-control" id="paidAmount" type="text" readonly>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label" id="paymentAmountLabel">Total Pembayaran</label>
                                        <input class="form-control" id="totalPaymentAmount" type="text" required>
                                        <input type="hidden" name="paymentAmount" id="hiddenPaymentAmount">
                                        <small class="form-text text-muted" id="paymentAmountHelp">Nominal
                                            pembayaran.</small>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label"
                                            for="itemNameModal">{{ __('menu_vendor_payment.payment_date') }}</label>
                                        <input class="form-control" name="date" id="date" type="date"
                                            placeholder="{{ __('menu_vendor_payment.payment_date') }}" required>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label" for="userBankCode">Sumber Dana (Bank) <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" name="userBankCode" id="userBankCode" required>
                                            <option value="">Pilih Bank</option>
                                            <option value="" disabled>-- Loading data bank --</option>
                                        </select>
                                        <small class="form-text text-muted">Pilih bank sumber dana untuk pembayaran</small>
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

        <!-- Detail Modal -->
        <div class="modal fade bd-example-modal-lg" id="detail-modal" tabindex="-1" role="dialog"
            aria-labelledby="detailModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="detailModalLabel">{{ __('menu_vendor_payment.payment_detail') }}</h4>
                        <button class="btn-close py-0" type="button" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="card">
                        <div class="card-body col-md-12">
                            <div class="row g-3">
                                 <div class="col-md-4">
                                     <label class="form-label">{{ __('menu_vendor_payment.payment_code') }}</label>
                                     <input class="form-control" id="detail-code" type="text" readonly>
                                 </div>

                                 <div class="col-md-4">
                                     <label class="form-label">Status Pembayaran</label>
                                     <input class="form-control" id="detail-payment-status" type="text" readonly>
                                 </div>

                                 <div class="col-md-4">
                                     <label class="form-label">Nomor Nota Kalender</label>
                                     <input class="form-control" id="detail-nota-number" type="text" readonly>
                                 </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('menu_vendor_payment.order_code') }}</label>
                                    <input class="form-control" id="detail-order-code" type="text" readonly>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Shipment Number / No Pengiriman</label>
                                    <input class="form-control" id="detail-shipment-number" type="text" readonly>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('menu_vendor_payment.plate_number') }}</label>
                                    <input class="form-control" id="detail-plate-number" type="text" readonly>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('menu_vendor_payment.driver') }}</label>
                                    <input class="form-control" id="detail-driver" type="text" readonly>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('menu_vendor_payment.customer') }}</label>
                                    <input class="form-control" id="detail-customer" type="text" readonly>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Tagihan</label>
                                    <input class="form-control" id="detail-billing-amount" type="text" readonly>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Terbayar</label>
                                    <input class="form-control" id="detail-paid-amount" type="text" readonly>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Sisa</label>
                                    <input class="form-control" id="detail-remaining-amount" type="text" readonly>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">{{ __('menu_vendor_payment.bank_source') }}</label>
                                    <input class="form-control" id="detail-bank" type="text" readonly>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">{{ __('menu_vendor_payment.description') }}</label>
                                    <textarea class="form-control" id="detail-description" rows="2" readonly></textarea>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Riwayat Pembayaran</label>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered" id="payment-history-table">
                                            <thead>
                                                <tr>
                                                    <th>Tanggal</th>
                                                    <th>Jumlah</th>
                                                    <th>Bank</th>
                                                    <th>Keterangan</th>
                                                </tr>
                                            </thead>
                                            <tbody id="payment-history-body">
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Form untuk Cetak Multi PDF -->
    <form id="multi-pdf-form" method="post" action="{{ route($view . 'pdf-multi') }}" target="_blank"
        style="display: none;">
        @csrf
        <div id="multiPdfOrderCodesContainer"></div>
    </form>

    <!-- Form & Modal untuk Generate Nota -->
    <form id="generate-nota-form" method="post" action="{{ route('finance.vendor-payment.generate-nota') }}">
        @csrf
        <div class="modal fade bd-example-modal-lg" id="nota-modal" tabindex="-1" role="dialog"
            aria-labelledby="notaModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div id="notaOrderCodesContainer"></div>
                    <div class="modal-header">
                        <h4 class="modal-title" id="notaModalLabel">Generate Nota Pembayaran</h4>
                        <button class="btn-close py-0" type="button" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="card">
                        <div class="card-body col-md-12">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label">Jumlah Order Dipilih</label>
                                    <input class="form-control" id="notaOrderCount" type="text" readonly>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Kode Order Dipilih</label>
                                    <textarea class="form-control" id="notaOrderList" rows="2" readonly></textarea>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label" for="notaUserBankCode">Akun Bank Pembayaran <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" name="userBankCode" id="notaUserBankCode" required>
                                        <option value="">Pilih Bank</option>
                                        <option value="" disabled>-- Loading data bank --</option>
                                    </select>
                                    <small class="form-text text-muted">Pilih bank yang ditargetkan untuk pembayaran nota ini</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer justify-content-start">
                        <button class="btn btn-primary" type="submit">Generate Nota Sekarang!</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <form id="delete-form" method="post">
        @csrf
        @method('DELETE')
    </form>

    <!-- Form untuk Batal Nota -->
    <form id="cancel-nota-form" method="post" style="display: none;">
        @csrf
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
    <script src=" {{ asset('assets/js/helper.js') }}"></script>

    {{-- <script src="../assets/js/sweet-alert/app.js"></script> --}}

    <script>
        let vendorPaymentTable;
        const selectedOrders = {};

        function formatCurrency(value) {
            return new Intl.NumberFormat('id-ID').format(Math.round(Number(value) || 0));
        }

        function calculateSelectedTotals() {
            const selectedList = Object.values(selectedOrders);

            return selectedList.reduce((totals, item) => {
                totals.billing += Number(item.billingAmount || 0);
                totals.paid += Number(item.paidAmount || 0);
                totals.remaining += Number(item.remainingAmount || 0);

                return totals;
            }, {
                billing: 0,
                paid: 0,
                remaining: 0,
            });
        }

        function populateSelectedOrderInputs() {
            const container = $('#selectedOrderCodesContainer');
            container.html('');

            Object.keys(selectedOrders).forEach(function(orderCode) {
                container.append(`<input type="hidden" name="orderCodes[]" value="${orderCode}">`);
            });
        }

        function syncSelectAllCheckbox() {
            const selectAllEl = $('#selectAllPayments');
            const enabledCheckboxes = $('.row-payment-checkbox:not(:disabled)');
            const checkedCheckboxes = enabledCheckboxes.filter(':checked');

            if (enabledCheckboxes.length === 0) {
                selectAllEl.prop('checked', false).prop('indeterminate', false).prop('disabled', true);

                return;
            }

            selectAllEl.prop('disabled', false);

            if (checkedCheckboxes.length === 0) {
                selectAllEl.prop('checked', false).prop('indeterminate', false);
            } else if (checkedCheckboxes.length === enabledCheckboxes.length) {
                selectAllEl.prop('checked', true).prop('indeterminate', false);
            } else {
                selectAllEl.prop('checked', false).prop('indeterminate', true);
            }
        }

        function restoreSelectedCheckboxes() {
            $('.row-payment-checkbox').each(function() {
                const checkbox = $(this);
                const orderCode = checkbox.attr('data-order-code');

                checkbox.prop('checked', !!selectedOrders[orderCode]);
            });
        }

        function updateSelectionSummary() {
            const selectedCount = Object.keys(selectedOrders).length;
            const summaryEl = $('#selectionSummary');
            const openModalButton = $('#openPaymentModalBtn');
            const printMultiPdfBtn = $('#printMultiPdfBtn');
            const generateNotaBtn = $('#generateNotaBtn');

            if (selectedCount === 0) {
                summaryEl.text('Belum ada order dipilih.');
                openModalButton.prop('disabled', true);
                printMultiPdfBtn.prop('disabled', true);
                generateNotaBtn.prop('disabled', true);
                syncSelectAllCheckbox();

                return;
            }

            // Pisahkan berdasarkan tipe checkbox
            const selectedList = Object.values(selectedOrders);
            const paymentOrders = selectedList.filter(item => item.checkboxType === 'payment');
            const notaOrders = selectedList.filter(item => item.checkboxType === 'nota');

            const totals = calculateSelectedTotals();

            let summaryText = selectedCount + ' order dipilih.';
            if (paymentOrders.length > 0) {
                summaryText += ' ' + paymentOrders.length + ' siap untuk bayar/cetak (sisa: Rp ' + formatCurrency(totals.remaining) + ').';
            }
            if (notaOrders.length > 0) {
                summaryText += ' ' + notaOrders.length + ' siap untuk generate nota.';
            }
            summaryEl.text(summaryText);

            // Tombol bayar aktif hanya jika ada payment-type (sudah punya nota) dan tidak bercampur dengan nota-type
            openModalButton.prop('disabled', paymentOrders.length === 0 || notaOrders.length > 0);
            
            // Tombol cetak terpilih aktif hanya jika ada payment-type (sudah punya nota) dan tidak bercampur dengan nota-type
            printMultiPdfBtn.prop('disabled', paymentOrders.length === 0 || notaOrders.length > 0);

            // Tombol generate nota aktif hanya jika ada nota-type (belum punya nota) dan tidak bercampur dengan payment-type
            generateNotaBtn.prop('disabled', notaOrders.length === 0 || paymentOrders.length > 0);

            syncSelectAllCheckbox();
        }

        $(document).ready(function() {
            vendorPaymentTable = $('#dt').DataTable({
                "processing": true,
                "serverSide": true,
                "destroy": true,
                "ajax": {
                    "url": "{{ route('dt.vendor-payment') }}",
                },
                "columns": [{
                        "data": 'select'
                    }, {
                        "data": 'action'
                    }, {
                        "data": 'DT_RowIndex'
                    },
                    {
                        "data": 'notaNumber'
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
                        "data": 'shipmentNumber'
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
                        "data": 'billingAmount'
                    },
                    {
                        "data": 'paidAmount'
                    },
                    {
                        "data": 'remainingAmount'
                    },
                    {
                        "data": 'paymentStatus'
                    },
                    {
                        "data": 'status'
                    }
                ],
                "columnDefs": [{
                        "searchable": false,
                        "targets": [0, 1, 2]
                    },
                    {
                        "orderable": false,
                        "targets": [0, 1, 2]
                    }
                ],
                "order": [
                    [4, 'asc']
                ],
                "drawCallback": function() {
                    restoreSelectedCheckboxes();
                    updateSelectionSummary();
                }
            });

            // Load bank data saat halaman dimuat
            loadBankData();

            // Selection per baris
            $(document).on('change', '.row-payment-checkbox', function() {
                const checkbox = $(this);
                const orderCode = checkbox.attr('data-order-code');
                const orderFormat = String(checkbox.attr('data-order-format') || '').toUpperCase().trim();
                const customerCode = String(checkbox.attr('data-customer-code') || '');
                const notaNumber = String(checkbox.attr('data-nota-number') || '');

                if (checkbox.is(':checked')) {
                    selectedOrders[orderCode] = {
                        orderCode: orderCode,
                        orderFormat: orderFormat,
                        customerCode: customerCode,
                        notaNumber: notaNumber,
                        checkboxType: String(checkbox.attr('data-checkbox-type') || 'payment'),
                        billingAmount: Number(checkbox.attr('data-billing-amount') || 0),
                        paidAmount: Number(checkbox.attr('data-paid-amount') || 0),
                        remainingAmount: Number(checkbox.attr('data-remaining-amount') || 0),
                    };
                } else {
                    delete selectedOrders[orderCode];
                }

                updateSelectionSummary();
            });

            // Select all untuk halaman saat ini
            $('#selectAllPayments').on('change', function() {
                const isChecked = $(this).is(':checked');

                $('.row-payment-checkbox:not(:disabled)').each(function() {
                    if ($(this).is(':checked') !== isChecked) {
                        $(this).prop('checked', isChecked).trigger('change');
                    }
                });
            });

            // Tombol buka modal pembayaran batch
            $('#openPaymentModalBtn').on('click', function() {
                const selectedCodes = Object.keys(selectedOrders);

                if (selectedCodes.length === 0) {
                    swal('Peringatan', 'Pilih minimal satu order untuk dibayar.', 'warning');

                    return;
                }

                const totals = calculateSelectedTotals();
                if (totals.remaining <= 0) {
                    swal('Peringatan', 'Tidak ada sisa tagihan pada order yang dipilih.', 'warning');

                    return;
                }

                populateSelectedOrderInputs();

                $('#selectedOrderCount').val(selectedCodes.length + ' order');
                $('#selectedOrderList').val(selectedCodes.join(', '));
                $('#billingAmount').val(formatCurrency(totals.billing));
                $('#paidAmount').val(formatCurrency(totals.paid));
                $('#remainingAmount').val(formatCurrency(totals.remaining));

                // Ambil unique nomor nota yang dipilih
                const selectedList = Object.values(selectedOrders);
                const uniqueNotas = [...new Set(selectedList.map(item => item.notaNumber).filter(n => n !== ''))];
                if (uniqueNotas.length > 0) {
                    $('#selectedNotaNumber').val(uniqueNotas.join(', '));
                    $('#notaNumberContainer').show();
                } else {
                    $('#selectedNotaNumber').val('');
                    $('#notaNumberContainer').hide();
                }

                $('#totalPaymentAmount').val(formatCurrency(totals.remaining));
                $('#hiddenPaymentAmount').val(totals.remaining);
                if (selectedCodes.length === 1) {
                    $('#totalPaymentAmount').prop('readonly', false);
                    $('#totalPaymentAmount').attr('data-max', totals.remaining);
                    $('#paymentAmountLabel').text('Total Pembayaran (Bisa bayar sebagian/DP)');
                    $('#paymentAmountHelp').text(
                        'Anda dapat mengubah nominal ini untuk membayar sebagian (DP). Maksimal: Rp ' +
                        formatCurrency(totals.remaining));
                } else {
                    $('#totalPaymentAmount').prop('readonly', true);
                    $('#totalPaymentAmount').removeAttr('data-max');
                    $('#paymentAmountLabel').text('Total Pembayaran (Otomatis Lunas)');
                    $('#paymentAmountHelp').text(
                        'Nominal ini otomatis mengikuti total sisa tagihan order yang dipilih.');
                }

                $('#date').val(new Date().toISOString().split('T')[0]);
                $('#description').val('');
                $('#userBankCode').val('');

                $('#payment-modal').modal('show');
            });

            // Format input total pembayaran
            $('#totalPaymentAmount').on('input', function() {
                let val = $(this).val().replace(/\./g, '').replace(/\D/g, '');
                if (val === '') val = 0;

                if (!$(this).prop('readonly')) {
                    let max = parseInt($(this).attr('data-max')) || 0;
                    if (parseInt(val) > max) {
                        val = max.toString();
                    }
                }

                if (val > 0) {
                    $(this).val(formatCurrency(val));
                    $('#hiddenPaymentAmount').val(val);
                } else {
                    $(this).val('');
                    $('#hiddenPaymentAmount').val('');
                }
            });

            $('#batch-payment-form').on('submit', function() {
                if (Object.keys(selectedOrders).length === 0) {
                    swal('Peringatan', 'Pilih minimal satu order untuk dibayar.', 'warning');

                    return false;
                }

                populateSelectedOrderInputs();

                return true;
            });
        });

        function loadBankData() {
            $.ajax({
                url: "{{ route('api.user-bank.company') }}",
                type: "GET",
                success: function(response) {
                    let options = '<option value="">Pilih Bank</option>';
                    if (response && response.length > 0) {
                        response.forEach(function(bank) {
                            let bankLabel = bank.bank_name || 'Unknown Bank';
                            options +=
                                `<option value="${bank.code}">${bankLabel} - ${bank.account_number} (${bank.account_name})</option>`;
                        });
                    } else {
                        options += '<option value="" disabled>Tidak ada data bank</option>';
                    }
                    $('#userBankCode').html(options);
                    $('#notaUserBankCode').html(options);
                },
                error: function(xhr) {
                    let options = '<option value="">Pilih Bank</option>';
                    options += '<option value="" disabled>Error memuat data</option>';
                    $('#userBankCode').html(options);
                    $('#notaUserBankCode').html(options);
                }
            });
        }

        function showDetailModal(orderCode) {
            $.ajax({
                url: "{{ route('ajax.vendor-payment-detail', ':orderCode') }}".replace(':orderCode', orderCode),
                type: "GET",
                success: function(data) {
                    if (data) {
                        $('#detail-code').val(data.batch_code || data.code || '');
                        $('#detail-nota-number').val(data.nota_number || '-');
                        $('#detail-order-code').val(data.order ? data.order.code : '');
                        $('#detail-shipment-number').val(data.shipmentNumber || data.shipment_number || (data
                            .order ? data.order.shipmentNumber : '') || '');
                        $('#detail-plate-number').val(data.order && data.order.fleet ? data.order.fleet
                            .plateNumber : '');
                        $('#detail-driver').val(data.order && data.order.driver ? data.order.driver.name : '');
                        $('#detail-customer').val(data.order && data.order.customer ? data.order.customer.name :
                            '');

                        // Menampilkan amount details
                        const billingAmount = data.amount || 0;
                        const paidAmount = data.paid_amount || 0;
                        const remainingAmount = data.remaining_amount || 0;

                        $('#detail-billing-amount').val('Rp ' + new Intl.NumberFormat('id-ID').format(
                            billingAmount));
                        $('#detail-paid-amount').val('Rp ' + new Intl.NumberFormat('id-ID').format(paidAmount));
                        $('#detail-remaining-amount').val('Rp ' + new Intl.NumberFormat('id-ID').format(
                            remainingAmount));

                        // Payment status
                        const statusMapping = {
                            'pending': 'Menunggu Pembayaran',
                            'partial': 'Pembayaran Sebagian',
                            'paid': 'Sudah Dibayar Penuh'
                        };
                        $('#detail-payment-status').val(statusMapping[data.payment_status] || data
                            .payment_status);

                        let bankInfo = '';
                        if (data.bankInfo) {
                            bankInfo = data.bankInfo.bank_name + ' - ' + data.bankInfo.account_number + ' (' +
                                data.bankInfo.account_name + ')';
                        }
                        $('#detail-bank').val(bankInfo);

                        $('#detail-description').val(data.description || '');

                        // Populate payment history
                        const historyBody = $('#payment-history-body');
                        historyBody.html('');

                        if (data.payment_histories && data.payment_histories.length > 0) {
                            data.payment_histories.forEach(function(history) {
                                const paymentDate = new Date(history.payment_date).toLocaleDateString(
                                    'id-ID', {
                                        year: 'numeric',
                                        month: '2-digit',
                                        day: '2-digit'
                                    });
                                const row = `<tr>
                                <td>${paymentDate}</td>
                                <td>Rp ${new Intl.NumberFormat('id-ID').format(history.amount)}</td>
                                <td>${history.bank_info || history.user_bank_code || '-'}</td>
                                <td>${history.description || '-'}</td>
                            </tr>`;
                                historyBody.append(row);
                            });
                        } else {
                            historyBody.html(
                                '<tr><td colspan="4" class="text-center">Belum ada riwayat pembayaran</td></tr>'
                            );
                        }

                        $('#detail-modal').modal('show');
                    } else {
                        alert('Data pembayaran tidak ditemukan.');
                    }
                },
                error: function(xhr) {
                    alert('Gagal memuat data pembayaran.');
                }
            });
        }

        // Handler untuk tombol Cetak Terpilih (Multi PDF)
        $('#printMultiPdfBtn').on('click', function() {
            const selectedCodes = Object.keys(selectedOrders);

            if (selectedCodes.length === 0) {
                swal('Peringatan', 'Pilih minimal satu order untuk dicetak.', 'warning');
                return;
            }

            const selectedFormats = [...new Set(Object.values(selectedOrders)
                .map(function(item) {
                    return String(item.orderFormat || '').toUpperCase().trim();
                })
                .filter(function(format) {
                    return format !== '';
                }))];

            if (selectedFormats.length > 1) {
                swal('Peringatan', 'Jenis order berbeda, harus yang sama untuk dicetak bersama.', 'warning');
                return;
            }

            // Populate form dengan selected order codes
            const container = $('#multiPdfOrderCodesContainer');
            container.html('');
            selectedCodes.forEach(function(orderCode) {
                container.append(`<input type="hidden" name="orderCodes[]" value="${orderCode}">`);
            });

            // Submit form untuk membuka PDF di tab baru
            $('#multi-pdf-form').submit();
        });

        // Function untuk konfirmasi pembatalan pembayaran
        function confirmCancelPayment(orderCode) {
            const url = '{{ route('finance.vendor-payment.index') }}/' + orderCode;
            $('#delete-form').attr('action', url);

            swal({
                title: "Apakah Anda yakin?",
                text: "Seluruh pembayaran untuk order " + orderCode + " akan dibatalkan secara permanen!",
                icon: "warning",
                buttons: ["Batal", "Ya, Batalkan!"],
                dangerMode: true,
            }).then((willCancel) => {
                if (willCancel) {
                    $('#delete-form').submit();
                }
            });
        }

        // Function untuk konfirmasi pembatalan nota pembayaran
        function confirmCancelNota(orderCode) {
            const url = '{{ route('finance.vendor-payment.index') }}/cancel-nota/' + orderCode;
            $('#cancel-nota-form').attr('action', url);

            swal({
                title: "Apakah Anda yakin?",
                text: "Nota pembayaran untuk order " + orderCode + " akan dibatalkan!",
                icon: "warning",
                buttons: ["Batal", "Ya, Batalkan Nota!"],
                dangerMode: true,
            }).then((willCancel) => {
                if (willCancel) {
                    $('#cancel-nota-form').submit();
                }
            });
        }

        // Handler untuk tombol Generate Nota
        $('#generateNotaBtn').on('click', function() {
            const notaOrders = Object.values(selectedOrders).filter(item => item.checkboxType === 'nota');
            const selectedCodes = notaOrders.map(item => item.orderCode);

            if (selectedCodes.length === 0) {
                swal('Peringatan', 'Pilih minimal satu order yang belum memiliki nota.', 'warning');
                return;
            }

            // Validasi 1: Pelanggan (customer) yang berbeda tidak boleh digabung dalam satu nota
            const uniqueCustomers = [...new Set(notaOrders.map(item => item.customerCode).filter(c => c !== ''))];
            if (uniqueCustomers.length > 1) {
                swal('Peringatan', 'Gagal: Order yang dipilih memiliki pelanggan (customer) yang berbeda. Satu nota hanya diperbolehkan untuk pelanggan yang sama.', 'warning');
                return;
            }

            // Validasi 3: Format Perusahaan (Pribadi, PHL, WTMS) yang berbeda tidak boleh digabung dalam satu nota
            const uniqueFormats = [...new Set(notaOrders.map(item => item.orderFormat).filter(f => f !== ''))];
            if (uniqueFormats.length > 1) {
                swal('Peringatan', 'Gagal: Order yang dipilih memiliki format perusahaan yang berbeda (' + uniqueFormats.join(', ') + '). Semua order dalam satu nota harus memiliki format perusahaan yang sama.', 'warning');
                return;
            }

            // Populate modal fields
            $('#notaOrderCount').val(selectedCodes.length + ' order');
            $('#notaOrderList').val(selectedCodes.join(', '));
            $('#notaUserBankCode').val(''); // Reset bank selection

            // Populate hidden inputs
            const container = $('#notaOrderCodesContainer');
            container.html('');
            selectedCodes.forEach(function(orderCode) {
                container.append(`<input type="hidden" name="orderCodes[]" value="${orderCode}">`);
            });

            // Tampilkan modal generate nota
            $('#nota-modal').modal('show');
        });

        // Handler untuk submit form generate nota
        $('#generate-nota-form').on('submit', function(e) {
            e.preventDefault();

            const notaOrders = Object.values(selectedOrders).filter(item => item.checkboxType === 'nota');
            const selectedCodes = notaOrders.map(item => item.orderCode);
            const selectedBank = $('#notaUserBankCode').val();

            if (!selectedBank) {
                swal('Peringatan', 'Pilih bank pembayaran terlebih dahulu.', 'warning');
                return false;
            }

            swal({
                title: "Generate Nota Pembayaran?",
                text: selectedCodes.length + " order akan dikelompokkan ke dalam satu nota resmi dan ditargetkan ke akun bank yang dipilih. Order yang sudah di-nota tidak bisa dipindahkan ke nota lain.",
                icon: "info",
                buttons: ["Batal", "Ya, Generate Nota!"],
            }).then((willGenerate) => {
                if (willGenerate) {
                    $('#generate-nota-form').off('submit').submit();
                }
            });
        });
    </script>
@endpush
