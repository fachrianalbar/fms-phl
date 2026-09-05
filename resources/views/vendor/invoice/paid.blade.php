@extends('layouts.main', [
'title' => $title,
'pageTitle' => $title,
'firstSegment' => 'Vendor',
'secondSegment' => 'Invoice Lunas',
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

@include('vendor.invoice.partials.table-style')
@endpush

@section('content')
<div class="col-sm-12">
    <!-- Page Header & Action Bar -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-success text-white rounded-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px; background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;">
                <i class="mdi mdi-check-decagram fs-24"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    {{ $title }}
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill fs-12 px-2 py-1">
                        {{ number_format($stats['notaCount'] ?? 0) }} Lunas
                    </span>
                </h4>
                <p class="text-muted mb-0 fs-12">Arsip nota pembayaran ke vendor armada eksternal yang telah lunas 100%. Pembayaran dapat dibatalkan bila terjadi kesalahan.</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm" id="btn-refresh-table" title="Muat Ulang Data Tabel">
                <i class="mdi mdi-refresh me-1"></i> Refresh
            </button>
            <a href="{{ route('vendor.invoice.unpaid') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm fw-semibold">
                <i class="mdi mdi-receipt-text-clock me-1"></i> Lihat Invoice Belum Lunas
            </a>
            <button type="button" class="btn btn-warning btn-sm rounded-pill px-3 shadow-sm fw-semibold" id="printMultiPdfBtn" disabled>
                <i class="mdi mdi-printer me-1"></i> Cetak Terpilih
            </button>
        </div>
    </div>

    @include('partials.alert')

    <p class="text-muted mb-3" id="selectionSummary">Belum ada nota dipilih.</p>

    <!-- 3 KPI Summary Cards -->
    <div class="row g-3 mb-4">
        <!-- Card 1: Nota Lunas -->
        <div class="col-12 col-sm-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Nota Lunas</div>
                        <div class="stat-value text-success">{{ number_format($stats['notaCount'] ?? 0) }} <span class="fs-13 text-muted fw-normal">Nota</span></div>
                    </div>
                    <div class="stat-icon-wrapper bg-success-subtle text-success">
                        <i class="mdi mdi-check-all"></i>
                    </div>
                </div>
                <div class="stat-desc text-success mt-2">
                    <i class="mdi mdi-shield-check-outline me-1"></i>Seluruh tagihan telah diselesaikan
                </div>
            </div>
        </div>

        <!-- Card 2: Total Order Terbayar -->
        <div class="col-12 col-sm-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Total Order Terbayar</div>
                        <div class="stat-value text-primary">{{ number_format($stats['orderCount'] ?? 0) }} <span class="fs-13 text-muted fw-normal">Order</span></div>
                    </div>
                    <div class="stat-icon-wrapper bg-primary-subtle text-primary">
                        <i class="mdi mdi-truck-check-outline"></i>
                    </div>
                </div>
                <div class="stat-desc text-muted mt-2">
                    <i class="mdi mdi-truck-fast-outline me-1"></i>Order armada eksternal yang telah dibayar
                </div>
            </div>
        </div>

        <!-- Card 3: Total Pembayaran Keluar -->
        <div class="col-12 col-sm-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Total Pembayaran Keluar</div>
                        <div class="stat-value text-info">Rp {{ number_format($stats['totalPaid'] ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <div class="stat-icon-wrapper bg-info-subtle text-info">
                        <i class="mdi mdi-cash-minus"></i>
                    </div>
                </div>
                <div class="stat-desc text-muted mt-2">
                    <i class="mdi mdi-arrow-up-bold-circle-outline me-1"></i>Akumulasi dana yang dibayarkan ke vendor
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="table-container-card mb-4">
        <div class="table-top-bar d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-bold fs-12">
                    <i class="mdi mdi-check-circle me-1"></i> Nota Lunas
                </span>
            </div>
            <div class="text-muted fs-12">
                <i class="mdi mdi-information-outline me-1 text-primary"></i>Centang nota untuk mencetak, atau gunakan aksi per baris.
            </div>
        </div>

        <div class="card-body p-3">
            <div class="table-responsive custom-scrollbar">
                <table class="table table-striped w-100 nowrap invoice-table" id="dtPaid">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 40px;">
                                <input class="form-check-input" type="checkbox" id="selectAllPaid">
                            </th>
                            <th class="text-center" style="width: 90px;">Aksi</th>
                            <th class="text-center" style="width: 45px;">No</th>
                            <th>No Nota</th>
                            <th>Tanggal Nota</th>
                            <th>Vendor (Perusahaan Kendaraan)</th>
                            <th class="text-center">Jumlah Order</th>
                            <th>Nopol</th>
                            <th class="text-end">Tagihan</th>
                            <th class="text-end">Terbayar</th>
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

<script>
    let vendorPaidTable;
    const selectedNotas = {}; // key: notaNumber

    function formatCurrency(value) {
        const num = Number(value) || 0;

        return 'Rp ' + num.toLocaleString('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatDateDMY(value) {
        if (!value) {
            return '-';
        }

        const dateParts = String(value).substring(0, 10).split('-');
        const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];

        if (dateParts.length === 3) {
            const monthIndex = parseInt(dateParts[1], 10) - 1;

            if (monthNames[monthIndex] !== undefined) {
                return dateParts[2] + ' ' + monthNames[monthIndex] + ' ' + dateParts[0];
            }
        }

        return String(value);
    }

    function uniqueValues(values) {
        return values.filter(function(value, index, self) {
            return value && self.indexOf(value) === index;
        });
    }

    function setDetailField(selector, value) {
        const field = $(selector);

        if (field.length === 0) {
            return;
        }

        if (field.is('input, textarea, select')) {
            field.val(value);
        } else {
            field.text(value);
        }
    }

    function updateSelectionSummary() {
        const count = Object.keys(selectedNotas).length;
        const summaryEl = $('#selectionSummary');
        const printBtn = $('#printMultiPdfBtn');

        if (count === 0) {
            summaryEl.text('Belum ada nota dipilih.');
            printBtn.prop('disabled', true);
            return;
        }

        summaryEl.text(count + ' nota dipilih.');
        printBtn.prop('disabled', false);
    }

    function showDetailModal(orderCode) {
        try {
            $.ajax({
                url: "{{ route('ajax.vendor-invoice-detail', ':orderCode') }}".replace(':orderCode', orderCode),
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    if (!data) {
                        swal('Gagal!', 'Data pembayaran tidak ditemukan.', 'error');
                        return;
                    }

                    const associated = Array.isArray(data.associated_payments) ? data.associated_payments : [];
                    const order = data.order || null;

                    // Basic info
                    setDetailField('#detail-code', data.batch_code || data.code || '-');

                    const statusLabels = { pending: 'Pending', partial: 'Partial', paid: 'Paid' };
                    const rawStatus = (data.payment_status || '').toString().toLowerCase();
                    setDetailField('#detail-payment-status', statusLabels[rawStatus] || (data.payment_status || '-'));

                    setDetailField('#detail-nota-number', data.nota_number || '-');

                    // Order codes of the nota
                    const orderCodeList = uniqueValues(associated.map(function(payment) {
                        if (!payment) {
                            return null;
                        }

                        return payment.orderCode || (payment.order ? payment.order.code : null);
                    }));
                    setDetailField('#detail-order-code', orderCodeList.join(', ') || data.orderCode || '-');

                    // Shipment numbers
                    const shipmentList = uniqueValues(associated.map(function(payment) {
                        return payment && payment.order ? payment.order.shipmentNumber : null;
                    }));
                    setDetailField('#detail-shipment-number', shipmentList.join(', ') || data.shipmentNumber || data.shipment_number || '-');

                    // Plate numbers
                    const plateList = uniqueValues(associated.map(function(payment) {
                        return payment && payment.order && payment.order.fleet ? payment.order.fleet.plateNumber : null;
                    }));
                    setDetailField('#detail-plate-number', plateList.join(', ') || (order && order.fleet ? order.fleet.plateNumber : null) || '-');

                    // Fleet company (first found)
                    let fleetCompanyName = order && order.fleet && order.fleet.company ? order.fleet.company.name : null;

                    if (!fleetCompanyName) {
                        for (let i = 0; i < associated.length; i++) {
                            const assocOrder = associated[i] && associated[i].order;

                            if (assocOrder && assocOrder.fleet && assocOrder.fleet.company && assocOrder.fleet.company.name) {
                                fleetCompanyName = assocOrder.fleet.company.name;
                                break;
                            }
                        }
                    }
                    setDetailField('#detail-fleet-company', fleetCompanyName || '-');

                    // Driver names
                    const driverList = uniqueValues(associated.map(function(payment) {
                        return payment && payment.order && payment.order.driver ? payment.order.driver.name : null;
                    }));
                    setDetailField('#detail-driver', driverList.join(', ') || (order && order.driver ? order.driver.name : null) || '-');

                    // Customer names
                    const customerList = uniqueValues(associated.map(function(payment) {
                        if (!payment || !payment.order || !payment.order.customer) {
                            return null;
                        }

                        return payment.order.customer.name || (payment.order.customer.company ? payment.order.customer.company.name : null);
                    }));
                    setDetailField('#detail-customer', customerList.join(', ') || (order && order.customer ? (order.customer.name || (order.customer.company ? order.customer.company.name : null)) : null) || '-');

                    // Amounts
                    setDetailField('#detail-billing-amount', formatCurrency(data.total_billing || data.amount || 0));
                    setDetailField('#detail-paid-amount', formatCurrency(data.total_paid || data.paid_amount || 0));
                    setDetailField('#detail-remaining-amount', formatCurrency(data.total_remaining || data.remaining_amount || 0));

                    // Bank info
                    const bankInfo = data.bankInfo;

                    if (bankInfo && bankInfo.bank_name) {
                        let bankText = bankInfo.bank_name;

                        if (bankInfo.account_number) {
                            bankText += ' - ' + bankInfo.account_number;
                        }

                        if (bankInfo.account_name) {
                            bankText += ' (' + bankInfo.account_name + ')';
                        }

                        setDetailField('#detail-bank', bankText);
                    } else {
                        setDetailField('#detail-bank', '-');
                    }

                    // Description
                    setDetailField('#detail-description', data.description || '-');

                    // Payment history rows
                    const historyBody = $('#payment-history-body');
                    historyBody.empty();

                    const histories = Array.isArray(data.payment_histories) ? data.payment_histories : [];

                    if (histories.length > 0) {
                        histories.forEach(function(history) {
                            historyBody.append(
                                '<tr>' +
                                    '<td class="text-center text-nowrap">' + escapeHtml(formatDateDMY(history.payment_date)) + '</td>' +
                                    '<td class="text-end text-nowrap fw-semibold">' + escapeHtml(formatCurrency(history.amount)) + '</td>' +
                                    '<td class="text-nowrap">' + escapeHtml(history.bank_info || '-') + '</td>' +
                                    '<td class="text-muted">' + escapeHtml(history.description || '-') + '</td>' +
                                '</tr>'
                            );
                        });
                    } else {
                        historyBody.append('<tr><td colspan="4" class="text-center text-muted py-3">Tidak ada riwayat pembayaran.</td></tr>');
                    }

                    $('#detail-modal').modal('show');
                },
                error: function(xhr) {
                    let message = 'Gagal memuat data pembayaran.';

                    if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }

                    swal('Gagal!', message, 'error');
                }
            });
        } catch (error) {
            console.error('showDetailModal error:', error);
            swal('Gagal!', 'Terjadi kesalahan saat memuat detail pembayaran.', 'error');
        }
    }

    function confirmCancelPayment(orderCode) {
        $('#delete-form').attr('action', "{{ url('vendor/invoice/payment') }}/" + orderCode);

        swal({
            title: "Apakah Anda yakin?",
            text: "Seluruh pembayaran untuk nota ini akan dibatalkan secara permanen! Saldo bank akan dikembalikan dan nota kembali belum lunas.",
            icon: "warning",
            buttons: ["Batal", "Ya, Batalkan!"],
            dangerMode: true,
        }).then((willCancel) => {
            if (willCancel) {
                $('#delete-form').submit();
            }
        });
    }

    $(document).ready(function() {
        vendorPaidTable = $('#dtPaid').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            pageLength: 25,
            ajax: "{{ route('dt.vendor-invoice.paid') }}",
            columns: [
                { data: 'select', className: 'text-center' },
                { data: 'action', className: 'text-center' },
                { data: 'DT_RowIndex', className: 'text-center' },
                { data: 'nota_number' },
                { data: 'nota_date' },
                { data: 'fleet_company_name' },
                { data: 'order_count', className: 'text-center' },
                { data: 'plate_numbers' },
                { data: 'amount', className: 'text-end' },
                { data: 'paid_amount', className: 'text-end' },
                { data: 'payment_status', className: 'text-center' }
            ],
            columnDefs: [
                { searchable: false, targets: [0, 1, 2] },
                { orderable: false, targets: [0, 1, 2] }
            ],
            order: [[4, 'asc']],
            drawCallback: function() {
                $('.row-payment-checkbox').each(function() {
                    const key = $(this).attr('data-nota-number');
                    $(this).prop('checked', !!selectedNotas[key]);
                });
                updateSelectionSummary();
            },
            language: {
                search: "",
                searchPlaceholder: "Cari no nota, vendor, nopol...",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ nota",
                infoEmpty: "Tidak ada data nota",
                zeroRecords: "Tidak ditemukan data yang sesuai",
                paginate: {
                    next: "<i class='mdi mdi-chevron-right'></i>",
                    previous: "<i class='mdi mdi-chevron-left'></i>"
                }
            }
        });

        // Row checkbox selection
        $(document).on('change', '.row-payment-checkbox', function() {
            const notaNumber = $(this).attr('data-nota-number');

            if (!notaNumber) {
                return;
            }

            if ($(this).is(':checked')) {
                const orderCodes = ($(this).attr('data-order-codes') || '')
                    .split(',')
                    .map(function(code) { return code.trim(); })
                    .filter(function(code) { return code !== ''; });

                selectedNotas[notaNumber] = {
                    orderCodes: orderCodes,
                    orderFormat: $(this).attr('data-order-format') || '',
                    amounts: {
                        billing: parseFloat($(this).attr('data-billing-amount')) || 0,
                        paid: parseFloat($(this).attr('data-paid-amount')) || 0
                    }
                };
            } else {
                delete selectedNotas[notaNumber];
            }

            updateSelectionSummary();
        });

        // Select all notas on the current page
        $('#selectAllPaid').on('change', function() {
            const isChecked = $(this).is(':checked');

            $('.row-payment-checkbox').each(function() {
                if ($(this).is(':checked') !== isChecked) {
                    $(this).prop('checked', isChecked).trigger('change');
                }
            });
        });

        // Refresh table button
        $('#btn-refresh-table').on('click', function() {
            vendorPaidTable.ajax.reload();
        });

        // Print selected notas (multi PDF)
        $('#printMultiPdfBtn').on('click', function() {
            const orderCodes = [];
            const orderFormats = [];

            Object.keys(selectedNotas).forEach(function(notaNumber) {
                const nota = selectedNotas[notaNumber] || {};

                (nota.orderCodes || []).forEach(function(code) {
                    if (code && orderCodes.indexOf(code) === -1) {
                        orderCodes.push(code);
                    }
                });

                if (nota.orderFormat && orderFormats.indexOf(nota.orderFormat) === -1) {
                    orderFormats.push(nota.orderFormat);
                }
            });

            if (orderCodes.length === 0) {
                swal('Peringatan', 'Belum ada nota yang dipilih.', 'warning');
                return;
            }

            if (orderFormats.length > 1) {
                swal('Peringatan', 'Jenis order berbeda, harus yang sama untuk dicetak bersama.', 'warning');
                return;
            }

            const container = $('#multiPdfOrderCodesContainer');
            container.empty();

            orderCodes.forEach(function(code) {
                container.append('<input type="hidden" name="orderCodes[]" value="' + escapeHtml(code) + '">');
            });

            $('#multi-pdf-form').submit();
        });
    });
</script>
@endpush
