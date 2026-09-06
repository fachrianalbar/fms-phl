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
<link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/select2.css') }}">
<link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/custom-select2.css') }}">

@include('vendor.invoice.partials.table-style')
<style>
    .vendor-on-charge-cell {
        min-width: 150px;
        text-align: right;
        line-height: 1.25;
    }

    .vendor-on-charge-cell strong,
    .vendor-on-charge-cell small {
        display: block;
    }

    .vendor-on-charge-cell strong {
        color: #b45309;
        font-size: 12px;
    }

    .vendor-on-charge-cell small {
        max-width: 190px;
        margin-top: 3px;
        color: #64748b;
        font-size: 10px;
        white-space: normal;
    }
</style>
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
                            <th class="text-end">Harga Vendor</th>
                            <th class="text-end">Cost Component</th>
                            <th class="text-end">Total Tagihan</th>
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
<script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>

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
                const bankSelect = $('#notaUserBankCode').empty();
                bankSelect.append(new Option('Pilih Bank', ''));

                if (Array.isArray(response) && response.length > 0) {
                    response.forEach(function(bank) {
                        const bankLabel = (bank.bank_name || 'Unknown Bank') + ' - ' + (bank.account_number || '-') + ' (' + (bank.account_name || '-') + ')';
                        bankSelect.append(new Option(bankLabel, bank.code || ''));
                    });
                } else {
                    bankSelect.append(new Option('Tidak ada data bank', '', false, false));
                    bankSelect.find('option:last').prop('disabled', true);
                }

                bankSelect.trigger('change');
            },
            error: function() {
                const bankSelect = $('#notaUserBankCode').empty();
                bankSelect.append(new Option('Pilih Bank', ''));
                bankSelect.append(new Option('Error memuat data', '', false, false));
                bankSelect.find('option:last').prop('disabled', true).end().trigger('change');
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
                    "searchable": true,
                    "render": function(data, type) {
                        if (!data) {
                            return type === 'sort' || type === 'type' ? 0 : '-';
                        }

                        const isoDate = String(data).substring(0, 10);
                        const parts = isoDate.split('-');

                        // Sorting memakai YYYYMMDD dari tanggal ISO mentah.
                        if (type === 'sort' || type === 'type') {
                            return parts.length === 3 ? Number(parts.join('')) : 0;
                        }

                        // Tampilan tetap DD-MM-YYYY.
                        return parts.length === 3
                            ? parts[2] + '-' + parts[1] + '-' + parts[0]
                            : data;
                    }
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
                    "data": 'vendorPriceAmount',
                    "searchable": true,
                    "className": 'text-end'
                },
                {
                    "data": 'onChargeAmount',
                    "searchable": true,
                    "className": 'text-end'
                },
                {
                    "data": 'billingAmount',
                    "searchable": true,
                    "className": 'text-end fw-semibold'
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

        // Select2 untuk pilihan bank di modal nota (serasi dengan halaman Pembayaran)
        $('#notaUserBankCode').select2({
            dropdownParent: $('#nota-modal'),
            width: '100%',
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
            const orderListEl = $('#notaOrderList').empty();
            selectedCodes.forEach(function(orderCode) {
                orderListEl.append($('<span>', { class: 'nota-order-chip' }).text(orderCode));
            });

            // Reset rate PPN/PPh + subtotal
            $('#notaPpnRate').val('0');
            $('#notaPphRate').val('0');
            $('#notaClaimAmount').val('0');
            notaModalState.subtotal = totals.billing;
            updateNotaTaxCalculation();

            $('#notaUserBankCode').val('').trigger('change'); // Reset bank selection

            // Populate hidden inputs
            const container = $('#notaOrderCodesContainer').empty();
            selectedCodes.forEach(function(orderCode) {
                container.append($('<input>', {
                    type: 'hidden',
                    name: 'orderCodes[]',
                    value: orderCode,
                }));
            });

            // Tampilkan modal generate nota
            $('#nota-modal').modal('show');
        });

        // State perhitungan pajak modal nota
        const notaModalState = {
            subtotal: 0,
        };

        // Ambil rate desimal dari input (koma diterima sebagai pemisah desimal).
        function parseNotaRateInput(el) {
            let value = String($(el).val() || '').replace(',', '.').replace(/[^0-9.]/g, '');
            const parts = value.split('.');
            value = parts.shift() + (parts.length ? '.' + parts.join('') : '');
            const rate = parseFloat(value);

            return Number.isFinite(rate) ? Math.min(100, Math.max(0, rate)) : 0;
        }

        function parseNotaClaimInput(el) {
            // Hapus pemisah ribuan (titik) lalu normalisasi koma jadi desimal.
            let value = String($(el).val() || '').replace(/\./g, '').replace(',', '.').replace(/[^0-9.]/g, '');
            const amount = parseFloat(value);

            return Number.isFinite(amount) ? Math.max(0, Math.round(amount)) : 0;
        }

        function formatNotaRate(value) {
            return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 4 }).format(Number(value) || 0);
        }

        // Format angka rupiah gaya Indonesia.
        function formatNotaNumber(value) {
            return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Math.round(Number(value) || 0));
        }

        // Hitung nominal dari DPP x rate / 100 dan total bayar.
        function updateNotaTaxCalculation() {
            const ppnRate = parseNotaRateInput($('#notaPpnRate'));
            const pphRate = parseNotaRateInput($('#notaPphRate'));
            const claim = parseNotaClaimInput($('#notaClaimAmount'));
            const ppn = Math.round(notaModalState.subtotal * ppnRate / 100);
            const pph = Math.round(notaModalState.subtotal * pphRate / 100);
            const grandTotal = notaModalState.subtotal + ppn - pph - claim;

            $('#notaPpnPreview, #notaPpnAmountPreview').text('Rp ' + formatNotaNumber(ppn));
            $('#notaPphPreview, #notaPphAmountPreview').text('Rp ' + formatNotaNumber(pph));
            $('#notaClaimPreview').text('Rp ' + formatNotaNumber(claim));
            $('#notaSubtotal').text('Rp ' + formatNotaNumber(notaModalState.subtotal));

            const grandTotalEl = $('#notaGrandTotal');
            grandTotalEl.text('Rp ' + formatNotaNumber(Math.max(0, grandTotal)));
            grandTotalEl.toggleClass('nota-grand-total-negative', grandTotal < 0);

            return {
                ppnRate: ppnRate,
                pphRate: pphRate,
                ppn: ppn,
                pph: pph,
                claim: claim,
                grandTotal: grandTotal,
            };
        }

        // Hitung saat mengetik, tetapi jangan memformat ulang input di setiap
        // keystroke. Memformat langsung akan menghapus tanda desimal sementara
        // (misalnya `1.`), sehingga angka seperti 1.1 tidak bisa diketik.
        $('#notaPpnRate, #notaPphRate, #notaClaimAmount').on('input', function() {
            updateNotaTaxCalculation();
        });

        $('#notaPpnRate, #notaPphRate').on('blur', function() {
            $(this).val(formatNotaRate(parseNotaRateInput(this)));
            updateNotaTaxCalculation();
        });

        $('#notaClaimAmount').on('blur', function() {
            $(this).val(formatNotaNumber(parseNotaClaimInput(this)));
            updateNotaTaxCalculation();
        });

        $('#notaPpnRate, #notaPphRate').on('focus', function() {
            $(this).select();
        });

        $('#notaClaimAmount').on('focus', function() {
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

            // Validasi rate PPN/PPh, lalu tampilkan rate dan nominal hasil hitung.
            const tax = updateNotaTaxCalculation();
            if (tax.ppnRate < 0 || tax.pphRate < 0 || tax.ppnRate > 100 || tax.pphRate > 100) {
                swal('Peringatan', 'Persentase PPN dan PPh harus antara 0% sampai 100%.', 'warning');

                return false;
            }

            if (tax.grandTotal < 0) {
                swal('Peringatan', 'Total bayar (Subtotal + PPN − PPh − Claim) tidak boleh minus. Periksa kembali persentase PPh dan nominal Biaya Claim yang diinput.', 'warning');

                return false;
            }

            // Kirim rate & nominal sebagai angka bersih tanpa pemisah ribuan.
            $('#notaPpnRate').val(String(tax.ppnRate).replace(',', '.'));
            $('#notaPphRate').val(String(tax.pphRate).replace(',', '.'));
            $('#notaClaimAmount').val(String(tax.claim));

            const hasClaim = tax.claim > 0;
            const hasTax = tax.ppnRate > 0 || tax.pphRate > 0 || hasClaim;
            let taxText = '\nTotal Bayar: ' + formatCurrency(tax.grandTotal);

            if (hasTax) {
                taxText = '\nSubtotal (DPP): ' + formatCurrency(notaModalState.subtotal) +
                    (tax.ppnRate > 0 ? '\nPPN (' + formatNotaRate(tax.ppnRate) + '%): ' + formatCurrency(tax.ppn) : '') +
                    (tax.pphRate > 0 ? '\nPPh (' + formatNotaRate(tax.pphRate) + '%): ' + formatCurrency(tax.pph) : '') +
                    (hasClaim ? '\nBiaya Claim: ' + formatCurrency(tax.claim) : '') +
                    '\nTotal Bayar: ' + formatCurrency(tax.grandTotal);
            }

            swal({
                title: "Generate Nota Pembayaran?",
                text: selectedCodes.length + " order akan dikelompokkan ke dalam satu nota resmi dan ditargetkan ke akun bank yang dipilih." + taxText + "\n\nOrder yang sudah di-nota tidak bisa dipindahkan ke nota lain.",
                icon: "info",
                buttons: ["Batal", "Ya, Generate Nota!"],
            }).then((willGenerate) => {
                if (willGenerate) {
                    // Loader SweetAlert selama proses generate nota berjalan
                    swal({
                        title: "Memproses Generate Nota...",
                        text: "Sedang membuat nota pembayaran, mohon tunggu.",
                        icon: "info",
                        buttons: false,
                        closeOnClickOutside: false,
                        closeOnEsc: false,
                    });

                    // Nonaktifkan tombol submit agar tidak ada klik ganda
                    $('#generate-nota-form button[type="submit"]').prop('disabled', true);

                    $.ajax({
                        url: $('#generate-nota-form').attr('action'),
                        type: 'POST',
                        data: new FormData($('#generate-nota-form')[0]),
                        processData: false,
                        contentType: false,
                        dataType: 'json',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        success: function(res) {
                            $('#generate-nota-form button[type="submit"]').prop('disabled', false);

                            if (res.success) {
                                $('#nota-modal').modal('hide');
                                swal({
                                    title: "Berhasil!",
                                    text: res.message || ('Nota pembayaran berhasil di-generate: ' + (res.nota_number || '')),
                                    icon: "success",
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                swal("Gagal!", res.message || 'Terjadi kesalahan saat membuat nota.', "error");
                            }
                        },
                        error: function(xhr) {
                            $('#generate-nota-form button[type="submit"]').prop('disabled', false);

                            let msg = 'Terjadi kesalahan saat membuat nota. Silakan coba lagi.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            swal("Gagal!", msg, "error");
                        }
                    });
                } else {
                    // Kembalikan format rate & claim setelah konfirmasi dibatalkan.
                    $('#notaPpnRate').val(formatNotaRate(tax.ppnRate));
                    $('#notaPphRate').val(formatNotaRate(tax.pphRate));
                    $('#notaClaimAmount').val(formatNotaNumber(tax.claim));
                    updateNotaTaxCalculation();
                }
            });
        });
    });
</script>
@endpush
