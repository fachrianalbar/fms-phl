@extends('layouts.main', [
'title' => $title,
'pageTitle' => $title,
'firstSegment' => 'Vendor',
'secondSegment' => 'Order Menunggu Nota',
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
            <div class="bg-primary text-white rounded-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px; background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%) !important;">
                <i class="mdi mdi-tray-full fs-24"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    {{ $title }}
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill fs-12 px-2 py-1">
                        {{ number_format($stats['waitingCount'] ?? 0) }} Order
                    </span>
                </h4>
                <p class="text-muted mb-0 fs-12">Order armada eksternal yang belum dibuat nota. Centang order lalu generate nota untuk membuat invoice pembayaran ke vendor.</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('vendor.invoice.unpaid') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm fw-semibold">
                <i class="mdi mdi-receipt-text-clock me-1"></i> Lihat Invoice Belum Lunas
            </a>
            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm text-white fw-semibold" id="generateNotaBtn" disabled>
                <i class="mdi mdi-file-document-outline me-1"></i> Generate Nota
            </button>
        </div>
    </div>

    <!-- Selection Summary & Notifications -->
    <p class="text-muted mb-3" id="selectionSummary">Belum ada order dipilih.</p>
    @include('partials.alert')

    <!-- 3 KPI Metrics Strip -->
    <div class="row g-3 mb-4">
        <!-- Card 1: Order Menunggu Nota -->
        <div class="col-12 col-sm-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Order Menunggu Nota</div>
                        <div class="stat-value">{{ number_format($stats['waitingCount'] ?? 0) }} <span class="fs-13 text-muted fw-normal">Order</span></div>
                    </div>
                    <div class="stat-icon-wrapper bg-primary-subtle text-primary">
                        <i class="mdi mdi-tray-full"></i>
                    </div>
                </div>
                <div class="stat-desc mt-2 text-truncate">
                    <i class="mdi mdi-information-outline me-1"></i>Belum dijadikan nota
                </div>
            </div>
        </div>

        <!-- Card 2: Total Nilai Tagihan -->
        <div class="col-12 col-sm-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Total Nilai Tagihan</div>
                        <div class="stat-value text-info">Rp {{ number_format($stats['totalBilling'] ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <div class="stat-icon-wrapper bg-info-subtle text-info">
                        <i class="mdi mdi-cash-multiple"></i>
                    </div>
                </div>
                <div class="stat-desc mt-2 text-truncate">
                    <i class="mdi mdi-cash-clock me-1"></i>Akumulasi tagihan order menunggu nota
                </div>
            </div>
        </div>

        <!-- Card 3: Vendor Terlibat -->
        <div class="col-12 col-sm-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Vendor Terlibat</div>
                        <div class="stat-value text-warning">{{ number_format($stats['vendorCount'] ?? 0) }} <span class="fs-13 text-muted fw-normal">Vendor</span></div>
                    </div>
                    <div class="stat-icon-wrapper bg-warning-subtle text-warning">
                        <i class="mdi mdi-truck-outline"></i>
                    </div>
                </div>
                <div class="stat-desc mt-2 text-truncate">
                    <i class="mdi mdi-truck-fast-outline me-1"></i>Perusahaan armada eksternal berbeda
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="table-container-card">
        <div class="table-top-bar d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h6 class="fw-bold text-dark mb-1">Order Menunggu Nota (Belum Dibuat Invoice)</h6>
                <div class="text-muted fs-12">
                    <i class="mdi mdi-information-outline me-1 text-primary"></i>Centang order lalu klik <strong>Generate Nota</strong> untuk membuat nota pembayaran ke vendor
                </div>
            </div>
        </div>
        <div class="card-body p-3">
            <div class="table-responsive custom-scrollbar">
                <table class="table table-striped w-100 nowrap invoice-table" id="dtWaiting">
                    <thead>
                        <tr>
                            <th class="text-center"><input class="form-check-input" type="checkbox" id="selectAllWaiting"></th>
                            <th class="text-center" style="width: 45px;">No</th>
                            <th>{{ __('menu_vendor_payment.order_date') }}</th>
                            <th>Order Code</th>
                            <th>{{ __('menu_vendor_payment.shipment_no') }}</th>
                            <th>{{ __('menu_vendor_payment.plate_number') }}</th>
                            <th>Perusahaan Kendaraan</th>
                            <th>{{ __('menu_vendor_payment.driver') }}</th>
                            <th>{{ __('menu_vendor_payment.customer') }}</th>
                            <th class="text-center">Format</th>
                            <th>{{ __('menu_vendor_payment.origin') }}</th>
                            <th>{{ __('menu_vendor_payment.destination') }}</th>
                            <th class="text-end">Tagihan</th>
                            <th class="text-end">Terbayar</th>
                            <th class="text-end">Sisa</th>
                            <th class="text-center">Status</th>
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
    let waitingTable;
    const selectedOrders = {}; // key: orderCode

    function formatCurrency(value) {
        return 'Rp ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Math.round(Number(value) || 0));
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

    // Setelah tabel digambar ulang, cek ulang checkbox yang masih terpilih.
    function restoreSelectedCheckboxes() {
        $('.row-payment-checkbox').each(function() {
            const checkbox = $(this);
            const orderCode = String(checkbox.attr('data-order-code') || '');

            checkbox.prop('checked', !!selectedOrders[orderCode]);
        });
    }

    function updateSelectionSummary() {
        const selectedCount = Object.keys(selectedOrders).length;
        const summaryEl = $('#selectionSummary');
        const generateNotaBtn = $('#generateNotaBtn');

        if (selectedCount === 0) {
            summaryEl.text('Belum ada order dipilih.');
            generateNotaBtn.prop('disabled', true);

            return;
        }

        const totals = calculateSelectedTotals();
        summaryEl.text(selectedCount + ' order dipilih (total tagihan: ' + formatCurrency(totals.billing) + ').');
        generateNotaBtn.prop('disabled', false);
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
                $('#notaUserBankCode').html(options);
            },
            error: function(xhr) {
                let options = '<option value="">Pilih Bank</option>';
                options += '<option value="" disabled>Error memuat data</option>';
                $('#notaUserBankCode').html(options);
            }
        });
    }

    $(document).ready(function() {
        waitingTable = $('#dtWaiting').DataTable({
            "processing": true,
            "serverSide": true,
            "destroy": true,
            "pageLength": 25,
            "ajax": {
                "url": "{{ route('dt.vendor-invoice.waiting') }}",
            },
            "columns": [{
                    "data": 'select'
                }, {
                    "data": 'DT_RowIndex'
                },
                {
                    "data": 'orderDate',
                    "searchable": true
                },
                {
                    "data": 'code',
                    "searchable": true
                },
                {
                    "data": 'shipmentNumber',
                    "searchable": true
                },
                {
                    "data": 'fleet.plateNumber',
                    "searchable": true
                },
                {
                    "data": 'fleet.company.name',
                    "searchable": true
                },
                {
                    "data": 'driver.name',
                    "searchable": true
                },
                {
                    "data": 'customer.name',
                    "searchable": true
                },
                {
                    "data": 'companyFormat',
                    "searchable": true,
                    "className": 'text-center',
                    "render": function(data, type) {
                        if (type !== 'display') {
                            return data;
                        }

                        if (data === 'Pribadi (P)') {
                            return '<span class="badge rounded-pill text-bg-warning">Pribadi</span>';
                        }

                        if (data === 'WTMS') {
                            return '<span class="badge rounded-pill text-bg-info">WTMS</span>';
                        }

                        if (data === 'PHL') {
                            return '<span class="badge rounded-pill text-bg-primary">PHL</span>';
                        }

                        return '<span class="badge rounded-pill text-bg-secondary">?</span>';
                    }
                },
                {
                    "data": 'route.originLocation.name',
                    "searchable": true
                },
                {
                    "data": 'route.destinationLocation.name',
                    "searchable": true
                },
                {
                    "data": 'billingAmount',
                    "searchable": true
                },
                {
                    "data": 'paidAmount',
                    "searchable": true
                },
                {
                    "data": 'remainingAmount',
                    "searchable": true
                },
                {
                    "data": 'status',
                    "searchable": true
                }
            ],
            "columnDefs": [{
                    "searchable": false,
                    "targets": [0, 1]
                },
                {
                    "orderable": false,
                    "targets": [0, 1, 9]
                }
            ],
            "order": [
                [2, 'asc']
            ],
            "drawCallback": function() {
                restoreSelectedCheckboxes();
                updateSelectionSummary();
            },
            "language": {
                "search": "",
                "searchPlaceholder": "Cari order code, shipment, nopol, perusahaan, customer, format, rute...", 
                "lengthMenu": "Tampilkan _MENU_ data",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ order",
                "infoEmpty": "Tidak ada order menunggu nota",
                "zeroRecords": "Tidak ditemukan data yang sesuai",
                "paginate": {
                    "next": "<i class='mdi mdi-chevron-right'></i>",
                    "previous": "<i class='mdi mdi-chevron-left'></i>"
                }
            }
        });

        // Load bank data saat halaman dimuat
        loadBankData();

        // Selection per baris (order menunggu nota)
        $(document).on('change', '.row-payment-checkbox', function() {
            const checkbox = $(this);
            const isChecked = checkbox.is(':checked');
            const orderCode = String(checkbox.attr('data-order-code') || '');

            if (orderCode === '') {
                return;
            }

            if (isChecked) {
                selectedOrders[orderCode] = {
                    orderCode: orderCode,
                    orderFormat: String(checkbox.attr('data-order-format') || '').toUpperCase().trim(),
                    fleetCompanyCode: String(checkbox.attr('data-fleet-company-code') || ''),
                    fleetCompanyName: String(checkbox.attr('data-fleet-company-name') || ''),
                    checkboxType: 'nota',
                    billingAmount: Number(checkbox.attr('data-billing-amount') || 0),
                    paidAmount: Number(checkbox.attr('data-paid-amount') || 0),
                    remainingAmount: Number(checkbox.attr('data-remaining-amount') || 0),
                };
            } else {
                delete selectedOrders[orderCode];
            }

            updateSelectionSummary();
        });

        // Pilih semua order menunggu nota pada halaman saat ini
        $('#selectAllWaiting').on('change', function() {
            const isChecked = $(this).is(':checked');

            $('.row-payment-checkbox:not(:disabled)').each(function() {
                if ($(this).is(':checked') !== isChecked) {
                    $(this).prop('checked', isChecked).trigger('change');
                }
            });
        });

        // Handler untuk tombol Generate Nota
        $('#generateNotaBtn').on('click', function() {
            const notaOrders = Object.values(selectedOrders).filter(item => item.checkboxType === 'nota');
            const selectedCodes = notaOrders.map(item => item.orderCode);

            if (selectedCodes.length === 0) {
                swal('Peringatan', 'Pilih minimal satu order yang belum memiliki nota.', 'warning');

                return;
            }

            // Validasi 1: Perusahaan kendaraan (fleet company) yang berbeda tidak boleh digabung dalam satu nota
            const uniqueFleetCompanies = [...new Set(notaOrders.map(item => item.fleetCompanyCode).filter(f => f !== ''))];
            if (uniqueFleetCompanies.length > 1) {
                swal('Peringatan', 'Order yang dipilih memiliki perusahaan kendaraan yang berbeda. Satu nota hanya diperbolehkan untuk perusahaan kendaraan yang sama.', 'warning');

                return;
            }

            // Validasi 2: Format Perusahaan (Pribadi, PHL, WTMS) yang berbeda tidak boleh digabung dalam satu nota
            const uniqueFormats = [...new Set(notaOrders.map(item => item.orderFormat).filter(f => f !== ''))];
            if (uniqueFormats.length > 1) {
                swal('Peringatan', 'Gagal: Order yang dipilih memiliki format perusahaan yang berbeda (' + uniqueFormats.join(', ') + '). Semua order dalam satu nota harus memiliki format perusahaan yang sama.', 'warning');

                return;
            }

            const totals = calculateSelectedTotals();

            // Label format perusahaan yang lebih ramah
            const formatLabels = {
                'P': 'Pribadi (P)',
                'PHL': 'PHL',
                'WTMS': 'WTMS',
                'WT': 'WTMS',
            };

            // Populate ringkasan modal
            $('#notaOrderCount').text(selectedCodes.length + ' order');
            $('#notaOrderFormat').text(formatLabels[notaOrders[0].orderFormat] || notaOrders[0].orderFormat || '-');

            const fleetCompanyName = String(notaOrders[0].fleetCompanyName || '').trim();
            $('#notaFleetCompanyName').text(fleetCompanyName || '-').attr('title', fleetCompanyName);

            // Render chip kode order
            const orderListEl = $('#notaOrderList');
            orderListEl.html('');
            selectedCodes.forEach(function(orderCode) {
                orderListEl.append('<span class="nota-order-chip">' + orderCode + '</span>');
            });

            // Reset input PPN/PPh (input manual) + subtotal
            $('#notaPpnAmount').val('0');
            $('#notaPphAmount').val('0');
            notaModalState.subtotal = totals.billing;
            updateNotaTaxCalculation();

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

        // State perhitungan pajak modal nota
        const notaModalState = {
            subtotal: 0,
        };

        // Ambil nominal (integer rupiah) dari input berformat ribuan
        function parseNotaTaxInput(el) {
            const digits = String($(el).val() || '').replace(/[^0-9]/g, '');

            return digits === '' ? 0 : parseInt(digits, 10);
        }

        // Format angka ribuan gaya Indonesia
        function formatNotaNumber(value) {
            return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Math.round(Number(value) || 0));
        }

        // Hitung ulang Total Bayar (DPP + PPN − PPh) di modal nota
        function updateNotaTaxCalculation() {
            const ppn = parseNotaTaxInput($('#notaPpnAmount'));
            const pph = parseNotaTaxInput($('#notaPphAmount'));
            const grandTotal = notaModalState.subtotal + ppn - pph;

            $('#notaPpnPreview').text('Rp ' + formatNotaNumber(ppn));
            $('#notaPphPreview').text('Rp ' + formatNotaNumber(pph));
            $('#notaSubtotal').text('Rp ' + formatNotaNumber(notaModalState.subtotal));

            const grandTotalEl = $('#notaGrandTotal');
            grandTotalEl.text('Rp ' + formatNotaNumber(Math.max(0, grandTotal)));
            grandTotalEl.toggleClass('nota-grand-total-negative', grandTotal < 0);

            return {
                ppn: ppn,
                pph: pph,
                grandTotal: grandTotal,
            };
        }

        // Format ribuan otomatis saat mengetik PPN / PPh (input manual)
        $('#notaPpnAmount, #notaPphAmount').on('input', function() {
            const value = parseNotaTaxInput(this);

            $(this).val(value === 0 ? '0' : formatNotaNumber(value));
            updateNotaTaxCalculation();
        });

        // Blok nilai minus / karakter aneh saat paste + pilih semua saat fokus
        $('#notaPpnAmount, #notaPphAmount').on('blur', function() {
            const value = parseNotaTaxInput(this);

            $(this).val(formatNotaNumber(value));
        });

        // Saat fokus, pilih seluruh angka agar mudah langsung diganti
        $('#notaPpnAmount, #notaPphAmount').on('focus', function() {
            $(this).select();
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

            // Validasi PPN & PPh (input manual): tidak boleh minus
            const tax = updateNotaTaxCalculation();
            if (tax.ppn < 0 || tax.pph < 0) {
                swal('Peringatan', 'Nominal PPN dan PPh tidak boleh minus.', 'warning');

                return false;
            }

            if (tax.grandTotal < 0) {
                swal('Peringatan', 'Total bayar (Subtotal + PPN − PPh) tidak boleh minus. Periksa kembali nominal PPh yang diinput.', 'warning');

                return false;
            }

            // Pastikan hidden input PPN/PPh berisi angka bersih (tanpa titik ribuan)
            $('#notaPpnAmount').val(String(tax.ppn));
            $('#notaPphAmount').val(String(tax.pph));

            const taxText = (tax.ppn > 0 || tax.pph > 0)
                ? '\nSubtotal (DPP): ' + formatCurrency(notaModalState.subtotal) +
                  '\nPPN: ' + formatCurrency(tax.ppn) +
                  '\nPPh: ' + formatCurrency(tax.pph) +
                  '\nTotal Bayar: ' + formatCurrency(tax.grandTotal)
                : '\nTotal Bayar: ' + formatCurrency(tax.grandTotal);

            swal({
                title: "Generate Nota Pembayaran?",
                text: selectedCodes.length + " order akan dikelompokkan ke dalam satu nota resmi dan ditargetkan ke akun bank yang dipilih." + taxText + "\n\nOrder yang sudah di-nota tidak bisa dipindahkan ke nota lain.",
                icon: "info",
                buttons: ["Batal", "Ya, Generate Nota!"],
            }).then((willGenerate) => {
                if (willGenerate) {
                    $('#generate-nota-form').off('submit').submit();
                } else {
                    // Kembalikan format ribuan pada input pajak (karena tadi dinormalisasi)
                    $('#notaPpnAmount').val(formatNotaNumber(tax.ppn));
                    $('#notaPphAmount').val(formatNotaNumber(tax.pph));
                }
            });
        });
    });
</script>
@endpush
