@extends('layouts.main', [
'title' => $title,
'pageTitle' => $title,
'firstSegment' => 'Vendor',
'secondSegment' => 'Invoice Belum Lunas',
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

@include('vendor.invoice.partials.table-style')
@endpush

@section('content')
<div class="col-sm-12">
    <!-- Page Header & Action Bar -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-primary text-white rounded-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important;">
                <i class="mdi mdi-receipt-text-clock fs-24"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    {{ $title }}
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill fs-12 px-2 py-1">
                        {{ number_format($stats['totalCount'] ?? 0) }} Aktif
                    </span>
                </h4>
                <p class="text-muted mb-0 fs-12">Nota pembayaran ke vendor armada eksternal yang belum lunas atau terbayar sebagian (DP/cicilan). Pembayaran dilakukan per nota.</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('vendor.order.waiting') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm fw-semibold">
                <i class="mdi mdi-tray-full me-1"></i> Lihat Order Menunggu Nota
            </a>
            <button type="button" class="btn btn-warning btn-sm rounded-pill px-3 shadow-sm fw-semibold" id="printMultiPdfBtn" disabled>
                <i class="mdi mdi-printer me-1"></i> Cetak Terpilih
            </button>
            <button type="button" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm text-white fw-semibold" id="openPaymentModalBtn" disabled>
                Bayar Nota Terpilih
            </button>
        </div>
    </div>

    <!-- Selection Summary & Notifications -->
    <p class="text-muted mb-3" id="selectionSummary">Belum ada order dipilih.</p>
    @include('partials.alert')

    <!-- 4 KPI Metrics Strip -->
    <div class="row g-3 mb-4">
        <!-- Card 1: Nota Belum Lunas -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Nota Belum Lunas</div>
                        <div class="stat-value">{{ number_format($stats['notaCount'] ?? 0) }} <span class="fs-13 text-muted fw-normal">Nota</span></div>
                    </div>
                    <div class="stat-icon-wrapper bg-primary-subtle text-primary">
                        <i class="mdi mdi-file-document-outline"></i>
                    </div>
                </div>
                <div class="stat-desc d-flex align-items-center gap-1 mt-2">
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-0 fs-11">
                        {{ $stats['pendingCount'] ?? 0 }} Baru
                    </span>
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0 fs-11">
                        {{ $stats['partialCount'] ?? 0 }} Parsial
                    </span>
                </div>
            </div>
        </div>

        <!-- Card 2: Total Order dalam Nota -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Total Order</div>
                        <div class="stat-value text-primary">{{ number_format($stats['orderCount'] ?? 0) }} <span class="fs-13 text-muted fw-normal">Order</span></div>
                    </div>
                    <div class="stat-icon-wrapper bg-primary-subtle text-primary">
                        <i class="mdi mdi-truck-check-outline"></i>
                    </div>
                </div>
                <div class="stat-desc mt-2 text-truncate">
                    <i class="mdi mdi-truck-fast-outline me-1"></i>Order di dalam nota belum lunas
                </div>
            </div>
        </div>

        <!-- Card 3: Sudah Terbayar -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Sudah Terbayar</div>
                        <div class="stat-value text-success">Rp {{ number_format($stats['totalPaid'] ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <div class="stat-icon-wrapper bg-success-subtle text-success">
                        <i class="mdi mdi-check-decagram-outline"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4: Sisa Harus Dibayar -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Sisa Harus Dibayar</div>
                        <div class="stat-value text-danger">Rp {{ number_format($stats['totalRemaining'] ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <div class="stat-icon-wrapper bg-danger-subtle text-danger">
                        <i class="mdi mdi-alert-circle-outline"></i>
                    </div>
                </div>
                <div class="stat-desc mt-2 text-truncate text-danger">
                    <i class="mdi mdi-cash-clock me-1"></i>Total tagihan: Rp {{ number_format($stats['totalBilling'] ?? 0, 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Card 1: Order Menunggu Nota (Belum Dibuat Invoice) -->
    <div class="table-container-card">
        <div class="table-top-bar d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h6 class="fw-bold text-dark mb-1">Nota Belum Lunas</h6>
                <div class="text-muted fs-12">
                    <i class="mdi mdi-information-outline me-1 text-primary"></i>Satu nota dapat memuat beberapa order. Pembayaran dapat dilunasi atau DP/cicilan. Order baru digabungkan lewat menu <strong>Order Menunggu Nota</strong>.
                </div>
            </div>
        </div>
        <div class="card-body p-3">
            <div class="table-responsive custom-scrollbar">
                <table class="table table-striped w-100 nowrap invoice-table" id="dtUnpaid">
                    <thead>
                        <tr>
                            <th class="text-center"><input class="form-check-input" type="checkbox" id="selectAllNotas"></th>
                            <th class="text-center" style="width: 130px;">Aksi</th>
                            <th class="text-center" style="width: 45px;">No</th>
                            <th>No Nota</th>
                            <th>Tanggal Nota</th>
                            <th>Vendor (Perusahaan Kendaraan)</th>
                            <th class="text-center">Jumlah Order</th>
                            <th>Nopol</th>
                            <th class="text-end">Tagihan</th>
                            <th class="text-end">PPN</th>
                            <th class="text-end">PPh</th>
                            <th class="text-end">Terbayar</th>
                            <th class="text-end">Sisa</th>
                            <th class="text-center">Status Bayar</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@include('vendor.invoice.partials.modals')
@endsection

@push('script')
<script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>
<script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>

<script>
    let vendorPaymentTable;
    const selectedOrders = {}; // key: notaNumber (baris nota)

    function formatCurrency(value) {
        return 'Rp ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Math.round(Number(value) || 0));
    }

    function calculateSelectedTotals() {
        const selectedList = Object.values(selectedOrders);

        return selectedList.reduce((totals, item) => {
            totals.billing += Number(item.billingAmount || 0);
            totals.paid += Number(item.paidAmount || 0);
            totals.remaining += Number(item.remainingAmount || 0);
            totals.ppn += Number(item.ppnAmount || 0);
            totals.pph += Number(item.pphAmount || 0);

            return totals;
        }, {
            billing: 0,
            paid: 0,
            remaining: 0,
            ppn: 0,
            pph: 0,
        });
    }

    // Kumpulkan seluruh order code dari nota (payment-type) yang dipilih, tanpa duplikat.
    function collectPaymentOrderCodes() {
        const orderCodes = [];

        Object.values(selectedOrders).forEach(function(item) {
            if (item.checkboxType !== 'payment') {
                return;
            }

            (item.orderCodes || []).forEach(function(code) {
                code = String(code || '').trim();

                if (code !== '' && orderCodes.indexOf(code) === -1) {
                    orderCodes.push(code);
                }
            });
        });

        return orderCodes;
    }

    // Setelah tabel digambar ulang, cek ulang checkbox yang masih terpilih.
    function restoreSelectedCheckboxes() {
        $('.row-payment-checkbox').each(function() {
            const checkbox = $(this);
            const notaNumber = String(checkbox.attr('data-nota-number') || '');

            checkbox.prop('checked', !!selectedOrders[notaNumber]);
        });
    }

    function updateSelectionSummary() {
        const selectedCount = Object.keys(selectedOrders).length;
        const summaryEl = $('#selectionSummary');
        const openModalButton = $('#openPaymentModalBtn');
        const printMultiPdfBtn = $('#printMultiPdfBtn');

        if (selectedCount === 0) {
            summaryEl.text('Belum ada nota dipilih.');
            openModalButton.prop('disabled', true);
            printMultiPdfBtn.prop('disabled', true);

            return;
        }

        const totals = calculateSelectedTotals();
        summaryEl.text(selectedCount + ' nota dipilih (sisa tagihan: ' + formatCurrency(totals.remaining) + ').');

        // Tombol bayar & cetak aktif hanya jika ada nota terpilih
        openModalButton.prop('disabled', selectedCount === 0);
        printMultiPdfBtn.prop('disabled', selectedCount === 0);
    }

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
                $('#userBankCode').html(options).trigger('change');
                $('#notaUserBankCode').html(options).trigger('change');
            },
            error: function(xhr) {
                let options = '<option value="">Pilih Bank</option>';
                options += '<option value="" disabled>Error memuat data</option>';
                $('#userBankCode').html(options).trigger('change');
                $('#notaUserBankCode').html(options).trigger('change');
            }
        });
    }

    function showDetailModal(orderCode) {
        $.ajax({
            url: "{{ route('ajax.vendor-invoice-detail', ':orderCode') }}".replace(':orderCode', orderCode),
            type: "GET",
            success: function(data) {
                if (data) {
                    $('#detail-code').val(data.batch_code || data.code || '');
                    $('#detail-nota-number').val(data.nota_number || '-');
                    if (data.associated_payments && data.associated_payments.length > 0) {
                        $('#detail-order-code').val(data.associated_payments.map(ap => ap.order ? ap.order.code : '').filter(c => c !== '').join(', '));
                        $('#detail-shipment-number').val(data.associated_payments.map(ap => ap.order ? (ap.order.shipmentNumber || '') : '').filter(s => s !== '').join(', '));
                        $('#detail-plate-number').val(data.associated_payments.map(ap => ap.order && ap.order.fleet ? ap.order.fleet.plateNumber : '').filter(p => p !== '').join(', '));
                        $('#detail-fleet-company').val([...new Set(data.associated_payments.map(ap => ap.order && ap.order.fleet && ap.order.fleet.company ? ap.order.fleet.company.name : '').filter(c => c !== ''))].join(', '));
                        $('#detail-driver').val([...new Set(data.associated_payments.map(ap => ap.order && ap.order.driver ? ap.order.driver.name : '').filter(d => d !== ''))].join(', '));
                        $('#detail-customer').val([...new Set(data.associated_payments.map(ap => ap.order && ap.order.customer ? ap.order.customer.name : '').filter(c => c !== ''))].join(', '));
                    } else {
                        $('#detail-order-code').val(data.order ? data.order.code : '');
                        $('#detail-shipment-number').val(data.shipmentNumber || data.shipment_number || (data.order ? data.order.shipmentNumber : '') || '');
                        $('#detail-plate-number').val(data.order && data.order.fleet ? data.order.fleet.plateNumber : '');
                        $('#detail-fleet-company').val(data.order && data.order.fleet && data.order.fleet.company ? data.order.fleet.company.name : '-');
                        $('#detail-driver').val(data.order && data.order.driver ? data.order.driver.name : '');
                        $('#detail-customer').val(data.order && data.order.customer ? data.order.customer.name : '');
                    }

                    // Menampilkan amount details
                    const billingAmount = data.total_billing || data.amount || 0;
                    const paidAmount = data.total_paid || data.paid_amount || 0;
                    const remainingAmount = data.total_remaining || data.remaining_amount || 0;
                    const ppnAmount = data.nota_ppn || 0;
                    const pphAmount = data.nota_pph || 0;

                    $('#detail-billing-amount').val(formatCurrency(billingAmount));
                    $('#detail-ppn-amount').val(formatCurrency(ppnAmount));
                    $('#detail-pph-amount').val(formatCurrency(pphAmount));
                    $('#detail-paid-amount').val(formatCurrency(paidAmount));
                    $('#detail-remaining-amount').val(formatCurrency(remainingAmount));

                    // Payment status
                    const statusMapping = {
                        'pending': 'Pending',
                        'partial': 'Partial',
                        'paid': 'Paid'
                    };
                    $('#detail-payment-status').val(statusMapping[data.payment_status] || data.payment_status || '-');

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
                                <td>${formatCurrency(history.amount)}</td>
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
                    swal('Gagal', "{{ __('menu_vendor_payment.payment_not_found') }}", 'error');
                }
            },
            error: function(xhr) {
                swal('Gagal', "{{ __('menu_vendor_payment.failed_load_payment') }}", 'error');
            }
        });
    }

    // Function untuk konfirmasi pembatalan pembayaran
    function confirmCancelPayment(orderCode) {
        const url = "{{ url('vendor/invoice/payment/') }}/" + orderCode;
        $('#delete-form').attr('action', url);

        swal({
            title: "Apakah Anda yakin?",
            text: "Seluruh pembayaran untuk order ini akan dibatalkan secara permanen! Saldo bank akan dikembalikan.",
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
        const url = "{{ url('vendor/invoice/cancel-nota/') }}/" + orderCode;
        $('#cancel-nota-form').attr('action', url);

        swal({
            title: "Apakah Anda yakin?",
            text: "Nota pembayaran untuk order ini akan dibatalkan. Semua order di dalam nota akan dibebaskan.",
            icon: "warning",
            buttons: ["Batal", "Ya, Batalkan Nota!"],
            dangerMode: true,
        }).then((willCancel) => {
            if (willCancel) {
                $('#cancel-nota-form').submit();
            }
        });
    }

    $(document).ready(function() {
        vendorPaymentTable = $('#dtUnpaid').DataTable({
            "processing": true,
            "serverSide": true,
            "destroy": true,
            "pageLength": 25,
            "ajax": {
                "url": "{{ route('dt.vendor-invoice.unpaid') }}",
            },
            "columns": [{
                    "data": 'select'
                }, {
                    "data": 'action'
                }, {
                    "data": 'DT_RowIndex'
                },
                {
                    "data": 'nota_number'
                },
                {
                    "data": 'nota_date'
                },
                {
                    "data": 'fleet_company_name'
                },
                {
                    "data": 'order_count'
                },
                {
                    "data": 'plate_numbers'
                },
                {
                    "data": 'amount'
                },
                {
                    "data": 'ppn_amount'
                },
                {
                    "data": 'pph_amount'
                },
                {
                    "data": 'paid_amount'
                },
                {
                    "data": 'remaining_amount'
                },
                {
                    "data": 'payment_status'
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
            },
            "language": {
                "search": "",
                "searchPlaceholder": "Cari no nota, vendor...",
                "lengthMenu": "Tampilkan _MENU_ data",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ nota",
                "infoEmpty": "Tidak ada nota belum lunas",
                "zeroRecords": "Tidak ditemukan data yang sesuai",
                "paginate": {
                    "next": "<i class='mdi mdi-chevron-right'></i>",
                    "previous": "<i class='mdi mdi-chevron-left'></i>"
                }
            }
        });

        // Select2 untuk pilihan bank di modal pembayaran & modal nota (serasi dengan halaman Pembayaran)
        $('#userBankCode').select2({
            dropdownParent: $('#payment-modal'),
            width: '100%',
        });
        $('#notaUserBankCode').select2({
            dropdownParent: $('#nota-modal'),
            width: '100%',
        });

        // Load bank data saat halaman dimuat
        loadBankData();

        // Selection per baris nota (satu checkbox mewakili seluruh order dalam nota)
        $(document).on('change', '.row-payment-checkbox', function() {
            const checkbox = $(this);
            const isChecked = checkbox.is(':checked');

            const orderFormat = String(checkbox.attr('data-order-format') || '').toUpperCase().trim();
            const fleetCompanyCode = String(checkbox.attr('data-fleet-company-code') || '');
            const billingAmount = Number(checkbox.attr('data-billing-amount') || 0);
            const paidAmount = Number(checkbox.attr('data-paid-amount') || 0);
            const remainingAmount = Number(checkbox.attr('data-remaining-amount') || 0);
            const ppnAmount = Number(checkbox.attr('data-ppn-amount') || 0);
            const pphAmount = Number(checkbox.attr('data-pph-amount') || 0);

            // Baris nota: satu checkbox mewakili seluruh order di dalam nota tersebut
            const notaNumber = String(checkbox.attr('data-nota-number') || '');
            if (notaNumber === '') {
                return;
            }

            if (isChecked) {
                selectedOrders[notaNumber] = {
                    notaNumber: notaNumber,
                    orderCodes: String(checkbox.attr('data-order-codes') || '').split(',').map(function(code) {
                        return code.trim();
                    }).filter(function(code) {
                        return code !== '';
                    }),
                    orderFormat: orderFormat,
                    fleetCompanyCode: fleetCompanyCode,
                    checkboxType: 'payment',
                    billingAmount: billingAmount,
                    paidAmount: paidAmount,
                    remainingAmount: remainingAmount,
                    ppnAmount: ppnAmount,
                    pphAmount: pphAmount,
                };
            } else {
                delete selectedOrders[notaNumber];
            }

            updateSelectionSummary();
        });

        // Pilih semua nota pada halaman saat ini
        $('#selectAllNotas').on('change', function() {
            const isChecked = $(this).is(':checked');

            $('.row-payment-checkbox[data-checkbox-type="payment"]:not(:disabled)').each(function() {
                if ($(this).is(':checked') !== isChecked) {
                    $(this).prop('checked', isChecked).trigger('change');
                }
            });
        });

        // Tombol buka modal pembayaran batch
        $('#openPaymentModalBtn').on('click', function() {
            const paymentOrders = Object.values(selectedOrders).filter(item => item.checkboxType === 'payment');
            const orderCodes = collectPaymentOrderCodes();

            if (paymentOrders.length === 0 || orderCodes.length === 0) {
                swal('Peringatan', 'Pilih minimal satu nota untuk dibayar.', 'warning');

                return;
            }

            const totals = calculateSelectedTotals();
            if (totals.remaining <= 0) {
                swal('Peringatan', 'Tidak ada sisa tagihan pada nota yang dipilih.', 'warning');

                return;
            }

            $('#selectedOrderCount').val(orderCodes.length + ' order');
            $('#selectedOrderList').val(orderCodes.join(', '));
            $('#billingAmount').val(formatCurrency(totals.billing));
            $('#paymentPpnAmount').val(formatCurrency(totals.ppn));
            $('#paymentPphAmount').val(formatCurrency(totals.pph));
            $('#paidAmount').val(formatCurrency(totals.paid));
            $('#remainingAmount').val(formatCurrency(totals.remaining));

            // Ambil unique nomor nota yang dipilih
            const uniqueNotas = paymentOrders.map(item => item.notaNumber).filter(n => n !== '');
            if (uniqueNotas.length > 0) {
                $('#selectedNotaNumber').val(uniqueNotas.join(', '));
                $('#notaNumberContainer').show();
            } else {
                $('#selectedNotaNumber').val('');
                $('#notaNumberContainer').hide();
            }

            $('#totalPaymentAmount').val(formatCurrency(totals.remaining));
            $('#hiddenPaymentAmount').val(totals.remaining);
            $('#totalPaymentAmount').attr('data-max', totals.remaining);
            $('#paymentAmountLabel').text('Total Pembayaran (Bisa bayar sebagian/DP)');
            $('#paymentAmountHelp').text(
                'Anda dapat mengubah nominal ini untuk membayar sebagian (DP). Maksimal: ' +
                formatCurrency(totals.remaining));

            $('#date').val(new Date().toISOString().split('T')[0]);
            $('#description').val('');
            $('#userBankCode').val('').trigger('change');

            $('#payment-modal').modal('show');
        });

        // Format input total pembayaran
        $('#totalPaymentAmount').on('input', function() {
            let val = $(this).val().replace(/\D/g, '');
            if (val === '') val = 0;

            if (!$(this).prop('readonly')) {
                let max = parseInt($(this).attr('data-max')) || 0;
                if (parseInt(val) > max) {
                    val = max.toString();
                }
            }

            if (parseInt(val) > 0) {
                $(this).val(formatCurrency(val));
                $('#hiddenPaymentAmount').val(val);
            } else {
                $(this).val('');
                $('#hiddenPaymentAmount').val('');
            }
        });

        // Submit form pembayaran batch
        $('#batch-payment-form').on('submit', function() {
            const orderCodes = collectPaymentOrderCodes();

            if (orderCodes.length === 0) {
                swal('Peringatan', 'Pilih minimal satu nota untuk dibayar.', 'warning');

                return false;
            }

            // Populate hidden inputs dengan seluruh order code dari nota yang dipilih
            const container = $('#selectedOrderCodesContainer');
            container.html('');
            orderCodes.forEach(function(orderCode) {
                container.append(`<input type="hidden" name="orderCodes[]" value="${orderCode}">`);
            });

            return true;
        });

        // Handler untuk tombol Cetak Terpilih (Multi PDF)
        $('#printMultiPdfBtn').on('click', function() {
            const paymentOrders = Object.values(selectedOrders).filter(item => item.checkboxType === 'payment');
            const orderCodes = collectPaymentOrderCodes();

            if (paymentOrders.length === 0 || orderCodes.length === 0) {
                swal('Peringatan', 'Pilih minimal satu nota untuk dicetak.', 'warning');

                return;
            }

            const selectedFormats = [...new Set(paymentOrders.map(function(item) {
                return String(item.orderFormat || '').toUpperCase().trim();
            }).filter(function(format) {
                return format !== '';
            }))];

            if (selectedFormats.length > 1) {
                swal('Peringatan', 'Jenis order berbeda, harus yang sama untuk dicetak bersama.', 'warning');

                return;
            }

            // Populate form dengan selected order codes
            const container = $('#multiPdfOrderCodesContainer');
            container.html('');
            orderCodes.forEach(function(orderCode) {
                container.append(`<input type="hidden" name="orderCodes[]" value="${orderCode}">`);
            });

            // Submit form untuk membuka PDF di tab baru
            $('#multi-pdf-form').submit();
        });
    });
</script>
@endpush
