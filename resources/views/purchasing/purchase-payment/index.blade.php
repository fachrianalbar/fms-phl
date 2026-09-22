@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Supplier',
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
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom-select2.css') }}">

    @include('purchasing.purchase-payment.partials.table-style')

    <style>
        /* Baris tabel dapat diklik untuk membuka rincian pembayaran. */
        #dt tbody tr {
            cursor: pointer;
        }
        #dt tbody tr td:first-child {
            cursor: default;
        }
    </style>
@endpush

@section('content')
    <div class="col-sm-12">
        {{-- Page Header --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center shadow-sm text-white"
                    style="width: 48px; height: 48px; background: linear-gradient(135deg, #f97316 0%, #c2410c 100%) !important;">
                    <i class="mdi mdi-cash-remove fs-24"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        {{ $title }}
                        <span
                            class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill fs-12 px-2 py-1">
                            {{ number_format($stats['totalCount'] ?? 0) }} Pembelian
                        </span>
                    </h4>
                    <p class="text-muted mb-0 fs-12">
                        Bayar satu atau banyak pembelian sekaligus (DP, cicilan &amp; pelunasan) dalam satu transaksi.
                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('purchasing.purchase-payment.paid') }}" class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-sm">
                    <i class="mdi mdi-cash-check me-1"></i>Hutang Lunas
                </a>
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm"
                    id="btn-refresh-table" title="Muat Ulang Data Tabel">
                    <i class="mdi mdi-refresh me-1" id="refresh-icon"></i> Refresh
                </button>
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card" data-filter="all" title="Klik untuk tampilkan semua">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Total Pembelian</div>
                            <div class="stat-value text-primary">
                                {{ number_format($stats['totalCount'] ?? 0) }}
                                <span class="fs-13 text-muted fw-normal">PO</span>
                            </div>
                        </div>
                        <div class="stat-icon-wrapper bg-primary-subtle text-primary">
                            <i class="mdi mdi-cart-outline"></i>
                        </div>
                    </div>
                    <div class="stat-desc text-truncate">
                        <i class="mdi mdi-calculator me-1 text-primary"></i>Total Tagihan:
                        <strong>Rp {{ number_format($stats['totalBilling'] ?? 0, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card" data-filter="unpaid" title="Klik untuk filter Belum Bayar">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Belum Bayar</div>
                            <div class="stat-value text-danger">
                                {{ number_format($stats['unpaidCount'] ?? 0) }}
                                <span class="fs-13 text-muted fw-normal">PO</span>
                            </div>
                        </div>
                        <div class="stat-icon-wrapper bg-danger-subtle text-danger">
                            <i class="mdi mdi-alert-circle-outline"></i>
                        </div>
                    </div>
                    <div class="stat-desc text-danger text-truncate">
                        <i class="mdi mdi-close-circle-outline me-1"></i>Belum ada pembayaran
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card" data-filter="partial" title="Klik untuk filter Belum Lunas (DP/Cicilan)">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Belum Lunas (DP)</div>
                            <div class="stat-value text-warning">
                                {{ number_format($stats['partialCount'] ?? 0) }}
                                <span class="fs-13 text-muted fw-normal">PO</span>
                            </div>
                        </div>
                        <div class="stat-icon-wrapper bg-warning-subtle text-warning">
                            <i class="mdi mdi-progress-clock"></i>
                        </div>
                    </div>
                    <div class="stat-desc text-warning-emphasis text-truncate">
                        <i class="mdi mdi-clock-outline me-1"></i>Sudah dibayar sebagian
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card" data-filter="all" title="Total sisa hutang supplier">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Total Sisa Hutang</div>
                            <div class="stat-value text-dark">
                                <span class="fs-16">Rp</span> {{ number_format($stats['totalRemaining'] ?? 0, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="stat-icon-wrapper bg-danger-subtle text-danger">
                            <i class="mdi mdi-cash-multiple"></i>
                        </div>
                    </div>
                    <div class="stat-desc text-truncate">
                        <i class="mdi mdi-check-circle-outline me-1 text-success"></i>Terbayar:
                        <strong>Rp {{ number_format($stats['totalPaid'] ?? 0, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Table --}}
        <div class="table-container-card mb-4">
            <div class="table-top-bar d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <button type="button" class="filter-pill-btn active" data-status="all">
                        <i class="mdi mdi-format-list-bulleted"></i>
                        <span>Semua</span>
                        <span class="badge-pill-count">{{ number_format($stats['totalCount'] ?? 0) }}</span>
                    </button>
                    <button type="button" class="filter-pill-btn" data-status="Unpaid">
                        <i class="mdi mdi-close-circle-outline text-danger"></i>
                        <span>Belum Bayar</span>
                        <span class="badge-pill-count">{{ number_format($stats['unpaidCount'] ?? 0) }}</span>
                    </button>
                    <button type="button" class="filter-pill-btn" data-status="Partial">
                        <i class="mdi mdi-clock-outline text-warning"></i>
                        <span>Belum Lunas</span>
                        <span class="badge-pill-count">{{ number_format($stats['partialCount'] ?? 0) }}</span>
                    </button>
                </div>
            </div>

            {{-- Sticky selection bar --}}
            <div class="selection-command-bar d-none" id="selection-bar" aria-live="polite">
                <div>
                    <strong id="selected-headline">0 pembelian terpilih</strong>
                    <div class="selection-facts mt-1">
                        <span id="selection-count-fact">0 PO</span>
                        <span id="selection-remaining-fact">Total sisa Rp 0</span>
                    </div>
                </div>
                <div class="selection-actions">
                    <button type="button" class="btn btn-outline-light btn-sm" id="btn-clear-selection">
                        <i class="mdi mdi-close me-1" aria-hidden="true"></i>Hapus Pilihan
                    </button>
                    <button type="button" class="btn btn-success btn-sm fw-semibold" id="btn-batch-pay">
                        <i class="mdi mdi-bank-transfer-out me-1" aria-hidden="true"></i>Bayar Terpilih
                        (<span id="payment-selection-count">0</span>)
                    </button>
                </div>
            </div>

            <div class="card-body p-3">
                <div class="text-muted fs-12 mb-2">
                    <i class="mdi mdi-information-outline me-1 text-primary"></i>
                    Centang beberapa pembelian lalu klik <strong>Bayar Terpilih</strong> untuk membayarnya dalam
                    satu transaksi. Klik baris untuk melihat rincian pembayaran.
                </div>
                <div class="d-flex flex-wrap align-items-center gap-3 fs-12 mb-3">
                    <span class="text-muted">Keterangan jatuh tempo:</span>
                    <span class="d-inline-flex align-items-center gap-1">
                        <span class="badge bg-warning-subtle text-warning-emphasis">&le; 7 hari</span>
                        <span class="text-muted">mendekati jatuh tempo</span>
                    </span>
                    <span class="d-inline-flex align-items-center gap-1">
                        <span class="badge bg-danger-subtle text-danger">Lewat</span>
                        <span class="text-muted">sudah jatuh tempo</span>
                    </span>
                </div>
                <div class="table-responsive custom-scrollbar">
                    <table class="table table-striped nowrap purchase-payment-table" id="dt">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 40px;">
                                    <input class="form-check-input" type="checkbox" id="check-all"
                                        title="Pilih semua data pada halaman ini">
                                </th>
                                <th class="text-center" style="width: 45px;">No</th>
                                <th>Kode Pembelian</th>
                                <th class="text-center">Tanggal</th>
                                <th class="text-center">Jatuh Tempo</th>
                                <th>Supplier</th>
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

    {{-- Batch payment modal --}}
    <div class="modal fade" id="batch-payment-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <form id="batch-payment-form" action="{{ route('purchasing.purchase-payment.batch.store') }}"
                    method="POST" autocomplete="off">
                    @csrf
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title mb-0">
                                <i class="mdi mdi-bank-transfer-out me-1 text-success"></i>Bayar Hutang Supplier
                            </h5>
                            <small class="text-muted">Satu transaksi dapat melunasi beberapa pembelian sekaligus.</small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <fieldset class="mb-3">
                            <legend class="fs-12 text-uppercase text-muted fw-bold mb-2">Mode Pembayaran</legend>
                            <div class="d-flex flex-wrap gap-2 payment-mode-switch">
                                <input type="radio" class="btn-check" name="paymentMode" id="batchPaymentModeFull"
                                    value="full" checked>
                                <label for="batchPaymentModeFull"><i class="mdi mdi-check-all me-1"></i>Lunasi Semua</label>
                                <input type="radio" class="btn-check" name="paymentMode" id="batchPaymentModeCustom"
                                    value="custom">
                                <label for="batchPaymentModeCustom"><i class="mdi mdi-pencil-outline me-1"></i>Nominal Manual</label>
                            </div>
                        </fieldset>

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold mb-0">Rincian Alokasi</h6>
                            <span class="badge bg-primary-subtle text-primary" id="batchPaymentAllocationCount">0 pembelian</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm payment-allocation-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Kode Pembelian</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-end">Sisa Tagihan</th>
                                        <th style="min-width: 170px;">Nominal Bayar</th>
                                        <th class="text-end">Sisa Setelah</th>
                                    </tr>
                                </thead>
                                <tbody id="batchPaymentAllocationBody"></tbody>
                            </table>
                        </div>
                        <div class="alert alert-danger py-2 mt-2 mb-0 d-none" id="batchPaymentAllocationError"></div>

                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <label class="form-label" for="batchDate">Tanggal Pembayaran</label>
                                <input type="date" class="form-control" id="batchDate">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="batchUserBankCode">Sumber Dana (Bank Perusahaan)</label>
                                <select class="form-select" id="batchUserBankCode">
                                    <option value="">Pilih rekening sumber dana</option>
                                    @foreach ($userBank as $item)
                                        <option value="{{ $item->code }}">
                                            {{ ($item->bank->name ?? 'Bank') . ' · ' . $item->accountNumber . ' · ' . $item->accountName }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted" id="batchBankStatus"></small>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="batchDescription">Keterangan</label>
                                <textarea class="form-control" id="batchDescription" rows="2" maxlength="255"
                                    placeholder="Opsional"></textarea>
                                <small class="text-muted" id="batchDescriptionCount">0/255</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <div class="payment-total-block text-start">
                            <span class="text-muted fs-12 d-block">Total Pembayaran</span>
                            <strong class="text-success" id="batchPaymentGrandTotal">Rp 0</strong>
                            <small class="text-muted d-block" id="batchPaymentAfterSummary">Sisa setelah pembayaran: Rp 0</small>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-success fw-semibold" id="submitBatchPaymentBtn">
                                <span class="spinner-border spinner-border-sm me-1 d-none" id="batchSubmitSpinner"></span>
                                <i class="mdi mdi-content-save-check me-1" id="batchSubmitIcon"></i>
                                <span id="batchSubmitLabel">Proses Pembayaran</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('purchasing.purchase-payment.partials.detail-modal')
@endsection

@push('script')
    <script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-keytable-bs5/js/keyTable.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-select/js/dataTables.select.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-select-bs5/js/select.bootstrap5.min.js') }}"></script>

    {{-- SweetAlert2 (v11, class Swal) — dimuat sebelum partial flash/detail. --}}
    <script src="{{ asset('assets/js/sweet-alert/sweetalert2.min.js') }}"></script>

    @include('purchasing.purchase-payment.partials.flash-swal')

    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2-custom.js') }}"></script>

    @include('purchasing.purchase-payment.partials.detail-modal-script')

    <script>
        // =====================================================================
        // STATE
        // =====================================================================
        let dt;
        let currentStatusFilter = 'all';
        const selectedPayments = {}; // code -> { code, supplier, remaining }
        let batchRequestKey = null;
        let batchInFlight = false;

        // =====================================================================
        // HELPER
        // =====================================================================
        function formatNumber(value) {
            return new Intl.NumberFormat('id-ID', {
                maximumFractionDigits: 0
            }).format(Math.round(Number(value) || 0));
        }

        function formatCurrency(value) {
            return 'Rp ' + formatNumber(value);
        }

        function escapeHtml(value) {
            return String(value == null ? '' : value).replace(/[&<>"']/g, function(character) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                } [character];
            });
        }

        function localDateValue() {
            const date = new Date();
            return date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0') + '-' + String(date
                .getDate()).padStart(2, '0');
        }

        function generateRequestKey() {
            if (window.crypto && typeof window.crypto.randomUUID === 'function') {
                return window.crypto.randomUUID();
            }
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(character) {
                const random = Math.floor(Math.random() * 16);
                const value = character === 'x' ? random : (random & 0x3) | 0x8;
                return value.toString(16);
            });
        }

        function swalLoader(title, html) {
            return Swal.fire({
                title: title,
                html: html,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: function() {
                    Swal.showLoading();
                },
            });
        }

        // =====================================================================
        // SELEKSI
        // =====================================================================
        function readRowCheckbox(checkbox) {
            return {
                code: String(checkbox.attr('data-code') || ''),
                supplier: String(checkbox.attr('data-supplier') || ''),
                remaining: Math.round(Number(checkbox.attr('data-remaining')) || 0),
            };
        }

        function selectedItems() {
            return Object.values(selectedPayments);
        }

        function updateSelectionUI() {
            const items = selectedItems();
            const count = items.length;
            const totalRemaining = items.reduce(function(sum, item) {
                return sum + (Number(item.remaining) || 0);
            }, 0);

            $('#selection-bar').toggleClass('d-none', count === 0);
            $('#selected-headline').text(count + ' pembelian terpilih');
            $('#selection-count-fact').text(count + ' PO');
            $('#selection-remaining-fact').text('Total sisa ' + formatCurrency(totalRemaining));
            $('#payment-selection-count').text(count);
        }

        function restoreSelectedCheckboxes() {
            $('.row-payment-checkbox').each(function() {
                const checkbox = $(this);
                const code = String(checkbox.attr('data-code') || '');
                const isSelected = !!selectedPayments[code];
                checkbox.prop('checked', isSelected);
                checkbox.closest('tr').toggleClass('table-active', isSelected);
            });
        }

        function clearSelection() {
            Object.keys(selectedPayments).forEach(function(key) {
                delete selectedPayments[key];
            });
            $('.row-payment-checkbox').prop('checked', false).closest('tr').removeClass('table-active');
            $('#check-all').prop('checked', false);
            updateSelectionUI();
        }

        // =====================================================================
        // MODAL PEMBAYARAN BATCH
        // =====================================================================
        function renderBatchAllocations() {
            const body = $('#batchPaymentAllocationBody').empty();
            const fullPayment = $('#batchPaymentModeFull').is(':checked');
            const items = selectedItems().slice().sort(function(first, second) {
                return String(first.code).localeCompare(String(second.code));
            });

            items.forEach(function(item) {
                if (fullPayment || item.allocationAmount === undefined) {
                    item.allocationAmount = item.remaining;
                }

                const row = $('<tr>').attr('data-code', item.code);
                const identity = $('<td>')
                    .append($('<strong>').text(item.code))
                    .append($('<span>', {
                        class: 'allocation-vendor'
                    }).text(item.supplier || '-'));
                const status = $('<td>', {
                    class: 'text-center'
                }).append($('<span>', {
                    class: 'badge text-bg-warning'
                }).text('Belum Lunas'));
                const before = $('<td>', {
                    class: 'text-end payment-money'
                }).text(formatCurrency(item.remaining));
                const input = $('<input>', {
                        type: 'text',
                        inputmode: 'numeric',
                        autocomplete: 'off',
                        class: 'form-control form-control-sm allocation-amount-input',
                    })
                    .attr('data-code', item.code)
                    .prop('readonly', fullPayment)
                    .val(formatNumber(item.allocationAmount));
                const inputGroup = $('<div>', {
                        class: 'input-group input-group-sm'
                    })
                    .append($('<span>', {
                        class: 'input-group-text'
                    }).text('Rp'))
                    .append(input);
                const amountCell = $('<td>').append(inputGroup).append($('<span>', {
                    class: 'allocation-message text-muted'
                }));
                const after = $('<td>', {
                    class: 'text-end payment-money allocation-after'
                });

                row.append(identity, status, before, amountCell, after);
                body.append(row);
            });

            $('#batchPaymentAllocationCount').text(items.length + ' pembelian');
            updateBatchSummary();
        }

        function allocationValidation(item) {
            const amount = Math.round(Number(item.allocationAmount) || 0);

            if (amount < 1) {
                return 'Nominal minimal Rp 1.';
            }
            if (amount > item.remaining) {
                return 'Melebihi sisa ' + formatCurrency(item.remaining) + '.';
            }

            return '';
        }

        function updateBatchSummary() {
            const items = selectedItems();
            let totalPayment = 0;
            let totalAfter = 0;
            let firstError = '';

            items.forEach(function(item) {
                item.allocationAmount = Math.round(Number(item.allocationAmount) || 0);
                totalPayment += item.allocationAmount;
                totalAfter += Math.max(0, item.remaining - item.allocationAmount);

                const row = $('#batchPaymentAllocationBody tr').filter(function() {
                    return $(this).attr('data-code') === item.code;
                });
                const error = allocationValidation(item);
                const input = row.find('.allocation-amount-input');
                const message = row.find('.allocation-message');

                input.toggleClass('is-invalid', error !== '');
                message.text(error || 'Maks. ' + formatCurrency(item.remaining))
                    .toggleClass('text-danger', error !== '')
                    .toggleClass('text-muted', error === '');
                row.find('.allocation-after').text(formatCurrency(Math.max(0, item.remaining - item
                    .allocationAmount)));

                if (!firstError && error) {
                    firstError = item.code + ': ' + error;
                }
            });

            const bankSelected = $('#batchUserBankCode').val() !== '';
            const dateValid = $('#batchDate').val() !== '';
            const ready = items.length > 0 && !firstError && totalPayment > 0 && bankSelected && dateValid &&
                !batchInFlight;

            $('#batchPaymentGrandTotal').text(formatCurrency(totalPayment));
            $('#batchPaymentAfterSummary').text('Sisa setelah pembayaran: ' + formatCurrency(totalAfter));
            $('#batchSubmitLabel').text(totalPayment > 0 ? 'Bayar ' + formatCurrency(totalPayment) :
                'Proses Pembayaran');
            $('#batchPaymentAllocationError').toggleClass('d-none', firstError === '').text(firstError);
            $('#batchDate').toggleClass('is-invalid', !dateValid);
            $('#batchUserBankCode').toggleClass('is-invalid', !bankSelected);
            $('#submitBatchPaymentBtn').prop('disabled', !ready);

            if (bankSelected) {
                $('#batchBankStatus').text('Rekening siap digunakan.').removeClass('text-danger').addClass(
                    'text-success');
            } else {
                $('#batchBankStatus').text('Pilih rekening sumber dana.').removeClass('text-danger text-success');
            }
        }

        function applyBatchMode() {
            const fullPayment = $('#batchPaymentModeFull').is(':checked');

            selectedItems().forEach(function(item) {
                if (fullPayment) {
                    item.allocationAmount = item.remaining;
                }
            });

            $('.allocation-amount-input').each(function() {
                const input = $(this);
                const item = selectedPayments[String(input.attr('data-code') || '')];
                input.prop('readonly', fullPayment);
                if (item) {
                    input.val(formatNumber(item.allocationAmount));
                }
            });

            updateBatchSummary();
        }

        function setBatchSubmitting(isSubmitting) {
            batchInFlight = isSubmitting;
            $('#batchSubmitSpinner').toggleClass('d-none', !isSubmitting);
            $('#batchSubmitIcon').toggleClass('d-none', isSubmitting);
            $('#batch-payment-modal [data-bs-dismiss]').prop('disabled', isSubmitting);
            $('#batchPaymentModeFull, #batchPaymentModeCustom, #batchDate, #batchDescription, .allocation-amount-input')
                .prop('disabled', isSubmitting);
            $('#batchUserBankCode').prop('disabled', isSubmitting).trigger('change.select2');
            updateBatchSummary();
        }

        function openBatchPaymentModal() {
            const items = selectedItems();

            if (items.length === 0) {
                Swal.fire({
                    title: 'Pilih pembelian',
                    text: 'Pilih minimal satu pembelian yang akan dibayar.',
                    icon: 'warning',
                });
                return;
            }

            batchRequestKey = generateRequestKey();
            items.forEach(function(item) {
                item.allocationAmount = item.remaining;
            });
            $('#batchPaymentModeFull').prop('checked', true);
            $('#batchDate').val(localDateValue());
            $('#batchDescription').val('');
            $('#batchDescriptionCount').text('0/255');
            $('#batchUserBankCode').val('').trigger('change.select2');
            renderBatchAllocations();
            setBatchSubmitting(false);
            $('#batch-payment-modal').modal('show');
        }

        // =====================================================================
        // INIT
        // =====================================================================
        $(document).ready(function() {
            dt = $('#dt').DataTable({
                "processing": true,
                "serverSide": true,
                "destroy": true,
                "scrollX": true,
                "autoWidth": false,
                "pageLength": 25,
                "ajax": {
                    "url": "{{ route('dt.purchase-payment.unpaid') }}",
                    "data": function(d) {
                        d.paymentStatus = currentStatusFilter === 'all' ? '' : currentStatusFilter;
                    }
                },
                "columns": [{
                        "data": null,
                        "className": 'text-center align-middle',
                        "orderable": false,
                        "searchable": false,
                        "render": function(data, type, row) {
                            const supplier = (row.supplier && row.supplier.name) ? row.supplier.name : '';
                            const remaining = (row.remainingRaw != null) ? row.remainingRaw : 0;
                            const disabled = remaining < 1 ? ' disabled' : '';
                            const title = remaining < 1 ? ' title="Tidak ada tagihan"' : '';
                            return '<input class="form-check-input row-payment-checkbox" type="checkbox"' +
                                disabled + title +
                                ' data-code="' + escapeHtml(row.code) + '"' +
                                ' data-supplier="' + escapeHtml(supplier) + '"' +
                                ' data-remaining="' + remaining + '">';
                        }
                    },
                    {
                        "data": 'DT_RowIndex',
                        "className": 'text-center align-middle',
                        "orderable": false,
                        "searchable": false
                    },
                    {
                        "data": 'code',
                        "className": 'align-middle'
                    },
                    {
                        "data": 'purchaseDate',
                        "className": 'align-middle text-center'
                    },
                    {
                        "data": 'dueDateHtml',
                        "className": 'align-middle text-center'
                    },
                    {
                        "data": 'supplier.name',
                        "className": 'align-middle'
                    },
                    {
                        "data": 'totalPrice',
                        "className": 'text-end align-middle payment-money'
                    },
                    {
                        "data": 'paidAmount',
                        "className": 'text-end align-middle payment-money'
                    },
                    {
                        "data": 'remaining',
                        "className": 'text-end align-middle payment-money fw-semibold text-danger'
                    },
                    {
                        "data": 'paymentStatusHtml',
                        "className": 'text-center align-middle'
                    }
                ],
                "order": [
                    [4, 'asc']
                ],
                "drawCallback": function() {
                    restoreSelectedCheckboxes();
                    updateSelectionUI();

                    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                        let tooltipTriggerList = [].slice.call(document.querySelectorAll(
                            '[data-bs-toggle="tooltip"]'));
                        tooltipTriggerList.map(function(el) {
                            return new bootstrap.Tooltip(el);
                        });
                    }
                },
                "language": {
                    "processing": "Memuat data...",
                    "search": "",
                    "searchPlaceholder": "Cari kode pembelian / supplier...",
                    "lengthMenu": "Tampilkan _MENU_ data",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    "infoEmpty": "Tidak ada data",
                    "zeroRecords": "Tidak ditemukan data yang sesuai",
                    "paginate": {
                        "next": "<i class='mdi mdi-chevron-right'></i>",
                        "previous": "<i class='mdi mdi-chevron-left'></i>"
                    }
                }
            });

            // Filter pills
            $('.filter-pill-btn').on('click', function() {
                $('.filter-pill-btn').removeClass('active');
                $(this).addClass('active');
                currentStatusFilter = $(this).data('status');
                dt.ajax.reload();
            });

            // KPI card click → filter
            $('.stat-card').on('click', function() {
                const filter = $(this).data('filter');
                if (filter) {
                    $('.filter-pill-btn').removeClass('active');
                    $('.filter-pill-btn[data-status="' + filter + '"]').addClass('active');
                    currentStatusFilter = filter;
                    dt.ajax.reload();
                }
            });

            // Refresh
            $('#btn-refresh-table').on('click', function() {
                const icon = $('#refresh-icon');
                icon.addClass('mdi-spin');
                dt.ajax.reload(function() {
                    setTimeout(function() {
                        icon.removeClass('mdi-spin');
                    }, 400);
                });
            });

            // Check all
            $('#check-all').on('change', function() {
                const isChecked = $(this).is(':checked');
                $('.row-payment-checkbox:visible:not(:disabled)').each(function() {
                    if ($(this).is(':checked') !== isChecked) {
                        $(this).prop('checked', isChecked).trigger('change');
                    }
                });
            });

            // Row checkbox
            $(document).on('change', '.row-payment-checkbox', function() {
                const checkbox = $(this);
                const item = readRowCheckbox(checkbox);

                if (!item.code) return;

                if (checkbox.is(':checked')) {
                    selectedPayments[item.code] = item;
                } else {
                    delete selectedPayments[item.code];
                }

                checkbox.closest('tr').toggleClass('table-active', checkbox.is(':checked'));
                updateSelectionUI();
            });

            // Clear selection
            $('#btn-clear-selection').on('click', clearSelection);

            // Bayar terpilih
            $('#btn-batch-pay').on('click', openBatchPaymentModal);

            // Klik baris → buka modal detail (kecuali kolom checkbox / elemen interaktif).
            $(document).on('click', '#dt tbody tr', function(event) {
                if ($(event.target).closest('input, button, a, select, .row-payment-checkbox').length) {
                    return;
                }

                const rowData = dt.row(this).data();

                if (!rowData || !rowData.code) return;

                openPurchaseDetail(String(rowData.code));
            });

            // Mode pembayaran
            $('input[name="paymentMode"]').on('change', applyBatchMode);

            // Input nominal alokasi
            $(document).on('input', '.allocation-amount-input', function() {
                const input = $(this);
                const code = String(input.attr('data-code') || '');
                const item = selectedPayments[code];
                const numericValue = String(input.val() || '').replace(/\D/g, '');

                if (!item) return;

                item.allocationAmount = numericValue === '' ? 0 : Number(numericValue);
                input.val(numericValue === '' ? '' : formatNumber(item.allocationAmount));
                updateBatchSummary();
            });

            $('#batchDate, #batchUserBankCode').on('change', updateBatchSummary);
            $('#batchDescription').on('input', function() {
                $('#batchDescriptionCount').text(String($(this).val()).length + '/255');
            });

            // Submit batch
            $('#batch-payment-form').on('submit', function(event) {
                event.preventDefault();
                updateBatchSummary();

                if ($('#submitBatchPaymentBtn').prop('disabled') || batchInFlight) {
                    return;
                }

                const payload = {
                    requestKey: batchRequestKey,
                    payments: selectedItems().map(function(item) {
                        return {
                            purchase_code: item.code,
                            amount: Math.round(Number(item.allocationAmount) || 0),
                            expected_remaining: item.remaining,
                        };
                    }),
                    date: $('#batchDate').val(),
                    userBankCode: $('#batchUserBankCode').val(),
                    description: $('#batchDescription').val().trim(),
                };

                const totalPayment = payload.payments.reduce(function(sum, payment) {
                    return sum + payment.amount;
                }, 0);
                const selectedBankText = $('#batchUserBankCode option:selected').text();
                const confirmationHtml = '<strong>' + escapeHtml(formatCurrency(totalPayment)) +
                    '</strong> untuk ' + payload.payments.length + ' pembelian.<br>Sumber dana: ' +
                    escapeHtml(selectedBankText) + '<br>Tanggal: ' + escapeHtml(payload.date);

                Swal.fire({
                    title: 'Konfirmasi pembayaran',
                    html: confirmationHtml,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Proses Pembayaran',
                    cancelButtonText: 'Periksa Lagi',
                    confirmButtonColor: '#198754',
                }).then(function(result) {
                    if (!result.isConfirmed) {
                        return;
                    }

                    setBatchSubmitting(true);
                    swalLoader('Memproses pembayaran',
                        'Jangan menutup halaman. Sistem sedang mengunci data dan mencatat mutasi bank.');

                    $.ajax({
                        url: $('#batch-payment-form').attr('action'),
                        type: 'POST',
                        data: JSON.stringify(payload),
                        contentType: 'application/json; charset=utf-8',
                        dataType: 'json',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': $('#batch-payment-form input[name="_token"]').val(),
                        },
                        success: function(response) {
                            Swal.close();
                            setBatchSubmitting(false);
                            $('#batch-payment-modal').modal('hide');

                            const result = response.result || {};
                            const outcome = (result.fully_paid_count || 0) +
                                ' pembelian lunas' + ((result.partial_count || 0) > 0 ? ' · ' +
                                    result.partial_count + ' pembelian masih sebagian' : '');
                            const successHtml = '<strong>' + escapeHtml(formatCurrency(result
                                    .payment_amount || 0)) + '</strong> berhasil dicatat.<br>' +
                                escapeHtml(outcome) + '<br>Kode: <strong>' + escapeHtml(result
                                    .batch_code || '-') + '</strong>';

                            Swal.fire({
                                title: 'Pembayaran berhasil',
                                html: successHtml,
                                icon: 'success',
                                confirmButtonText: 'Lihat Daftar Terbaru',
                                confirmButtonColor: '#198754',
                            }).then(function() {
                                window.location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.close();
                            setBatchSubmitting(false);

                            const message = (xhr.responseJSON && xhr.responseJSON.message) ?
                                xhr.responseJSON.message :
                                'Pembayaran gagal diproses. Data tetap tersimpan di form dan dapat dicoba kembali.';
                            const isConflict = xhr.status === 409;

                            if (isConflict) {
                                $('#batch-payment-modal').modal('hide');
                                clearSelection();
                                dt.ajax.reload(null, false);
                            }

                            Swal.fire({
                                title: isConflict ? 'Data berubah' : 'Pembayaran gagal',
                                text: message,
                                icon: isConflict ? 'warning' : 'error',
                                confirmButtonText: 'Tutup',
                            });
                        }
                    });
                });
            });

            // Select2
            $('#batchUserBankCode').select2({
                dropdownParent: $('#batch-payment-modal'),
                width: '100%',
            });
            $('#dt_wrapper .dataTables_length select').select2({
                minimumResultsForSearch: Infinity,
                width: '88px',
                dropdownAutoWidth: true,
            });

            updateSelectionUI();
        });
    </script>
@endpush
