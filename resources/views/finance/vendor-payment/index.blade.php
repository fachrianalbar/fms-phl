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

    <form class="row g-3" method="post" action="{{ route($view . 'store') }}" onsubmit="return submitForm('amount')">
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
                                    <label class="form-label"
                                        for="itemNameModal">{{ __('menu_vendor_payment.payment_date') }}</label>
                                    <input class="form-control" name="date" id="date" type="date"
                                        placeholder="{{ __('menu_vendor_payment.payment_date') }}" required>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label"
                                        for="amount">{{ __('menu_vendor_payment.amount') }}</label>
                                    <input class="form-control" name="amount" id="amount" type="text"
                                        oninput="formatAngka(this); validatePaymentAmount()"
                                        placeholder="{{ __('menu_vendor_payment.amount') }}" required>
                                    <small class="form-text text-muted" id="amountWarning"></small>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label" for="userBankCode">Sumber Dana (Bank) <span class="text-danger">*</span></label>
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
                            <div class="col-md-6">
                                <label class="form-label">{{ __('menu_vendor_payment.payment_code') }}</label>
                                <input class="form-control" id="detail-code" type="text" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Status Pembayaran</label>
                                <input class="form-control" id="detail-payment-status" type="text" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">{{ __('menu_vendor_payment.order_code') }}</label>
                                <input class="form-control" id="detail-order-code" type="text" readonly>
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
<script src=" {{ asset('assets/js/helper.js') }}"></script>

{{-- <script src="../assets/js/sweet-alert/app.js"></script> --}}

<script>
    $(document).ready(function() {
        $('#dt').DataTable({
            "processing": true,
            "serverSide": true,
            "destroy": true,
            "ajax": {
                "url": "{{ route('dt.vendor-payment') }}",
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
        });

        // Load bank data saat halaman dimuat
        loadBankData();
    });

    function loadBankData() {
        $.ajax({
            url: "{{ route('api.user-bank.company') }}",
            type: "GET",
            success: function(response) {
                console.log('Bank data loaded:', response);
                let options = '<option value="">Pilih Bank</option>';
                if (response && response.length > 0) {
                    response.forEach(function(bank) {
                        let bankLabel = bank.bank_name || 'Unknown Bank';
                        options += `<option value="${bank.code}">${bankLabel} - ${bank.account_number} (${bank.account_name})</option>`;
                    });
                } else {
                    console.warn('Tidak ada data bank yang ditemukan');
                    options += '<option value="" disabled>Tidak ada data bank</option>';
                }
                $('#userBankCode').html(options);
            },
            error: function(xhr) {
                console.error('Gagal memuat data bank:', xhr.status, xhr.statusText);
                console.error('Response:', xhr.responseText);
                let options = '<option value="">Pilih Bank</option>';
                options += '<option value="" disabled>Error memuat data</option>';
                $('#userBankCode').html(options);
            }
        });
    }

    function showModal(orderCode, billingAmount, remainingAmount) {
        console.log('Order Code:', orderCode, 'Billing Amount:', billingAmount, 'Remaining:', remainingAmount);

        $('#payment-modal').modal('show');
        $('#orderCode').val(orderCode);
        $('#billingAmount').val(new Intl.NumberFormat('id-ID').format(billingAmount));
        $('#remainingAmount').val(new Intl.NumberFormat('id-ID').format(remainingAmount));
        $('#paidAmount').val(new Intl.NumberFormat('id-ID').format(billingAmount - remainingAmount));
        $('#amount').val('');
        $('#date').val(new Date().toISOString().split('T')[0]);
        $('#description').val('');
        $('#userBankCode').val('');
        $('#amountWarning').text('').html('');
    }

    function validatePaymentAmount() {
        const amountInput = $('#amount').val();
        // Remove formatting
        const amount = parseInt(amountInput.replace(/[^\d]/g, '')) || 0;
        const remainingAmount = parseInt($('#remainingAmount').val().replace(/[^\d]/g, '')) || 0;

        const warningEl = $('#amountWarning');

        if (amount > remainingAmount) {
            warningEl.html('<span class="text-danger">⚠️ Jumlah pembayaran melebihi sisa tagihan!</span>');
        } else if (amount > 0 && amount < remainingAmount) {
            warningEl.html('<span class="text-info">ℹ️ Ini adalah pembayaran partial. Sisa tagihan: Rp ' + new Intl.NumberFormat('id-ID').format(remainingAmount - amount) + '</span>');
        } else {
            warningEl.text('');
        }
    }

    function showDetailModal(orderCode) {
        $.ajax({
            url: "{{ route('ajax.vendor-payment-detail', ':orderCode') }}".replace(':orderCode', orderCode),
            type: "GET",
            success: function(data) {
                if (data) {
                    $('#detail-code').val(data.code || '');
                    $('#detail-order-code').val(data.order ? data.order.code : '');
                    $('#detail-plate-number').val(data.order && data.order.fleet ? data.order.fleet.plateNumber : '');
                    $('#detail-driver').val(data.order && data.order.driver ? data.order.driver.name : '');
                    $('#detail-customer').val(data.order && data.order.customer ? data.order.customer.name : '');

                    // Menampilkan amount details
                    const billingAmount = data.amount || 0;
                    const paidAmount = data.paid_amount || 0;
                    const remainingAmount = data.remaining_amount || 0;

                    $('#detail-billing-amount').val('Rp ' + new Intl.NumberFormat('id-ID').format(billingAmount));
                    $('#detail-paid-amount').val('Rp ' + new Intl.NumberFormat('id-ID').format(paidAmount));
                    $('#detail-remaining-amount').val('Rp ' + new Intl.NumberFormat('id-ID').format(remainingAmount));

                    // Payment status
                    const statusMapping = {
                        'pending': 'Menunggu Pembayaran',
                        'partial': 'Pembayaran Sebagian',
                        'paid': 'Sudah Dibayar Penuh'
                    };
                    $('#detail-payment-status').val(statusMapping[data.payment_status] || data.payment_status);

                    let bankInfo = '';
                    if (data.bankInfo) {
                        bankInfo = data.bankInfo.bank_name + ' - ' + data.bankInfo.account_number + ' (' + data.bankInfo.account_name + ')';
                    }
                    $('#detail-bank').val(bankInfo);

                    $('#detail-description').val(data.description || '');

                    // Populate payment history
                    const historyBody = $('#payment-history-body');
                    historyBody.html('');

                    if (data.payment_histories && data.payment_histories.length > 0) {
                        data.payment_histories.forEach(function(history) {
                            const paymentDate = new Date(history.payment_date).toLocaleDateString('id-ID', {
                                year: 'numeric',
                                month: '2-digit',
                                day: '2-digit'
                            });
                            const row = `<tr>
                                <td>${paymentDate}</td>
                                <td>Rp ${new Intl.NumberFormat('id-ID').format(history.amount)}</td>
                                <td>${history.user_bank_code || '-'}</td>
                                <td>${history.description || '-'}</td>
                            </tr>`;
                            historyBody.append(row);
                        });
                    } else {
                        historyBody.html('<tr><td colspan="4" class="text-center">Belum ada riwayat pembayaran</td></tr>');
                    }

                    $('#detail-modal').modal('show');
                } else {
                    alert('Data pembayaran tidak ditemukan.');
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                alert('Gagal memuat data pembayaran.');
            }
        });
    }
</script>
@endpush