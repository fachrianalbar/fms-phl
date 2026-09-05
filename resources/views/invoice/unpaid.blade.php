@extends('layouts.main', [
'title' => $title,
'pageTitle' => $title,
'firstSegment' => 'Faktur',
'secondSegment' => 'Unpaid Invoice',
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

@include('invoice.partials.table-style')
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
                <p class="text-muted mb-0 fs-12">Kelola dan pantau faktur penagihan piutang pelanggan yang belum lunas atau terbayar sebagian.</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm" id="btn-refresh-table" title="Muat Ulang Data Tabel">
                <i class="mdi mdi-refresh me-1"></i> Refresh
            </button>
            @if (Auth::user()->roleCode === 'SPRADMIN')
                <button type="button" class="btn btn-outline-warning btn-sm rounded-pill px-3 shadow-sm" id="btn-recalculate-all">
                    <i class="mdi mdi-calculator-variant-outline me-1"></i> Hitung Ulang Semua
                </button>
            @endif
            <a href="{{ route('invoice.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm text-white fw-semibold" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
                <i class="mdi mdi-plus-circle me-1"></i> {{ __('general.add_data') }}
            </a>
        </div>
    </div>

    <!-- 4 KPI Metrics Strip -->
    <div class="row g-3 mb-4">
        <!-- Card 1: Total Faktur -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Faktur Tertunda</div>
                        <div class="stat-value">{{ number_format($stats['totalCount'] ?? 0) }} <span class="fs-13 text-muted fw-normal">Faktur</span></div>
                    </div>
                    <div class="stat-icon-wrapper bg-primary-subtle text-primary">
                        <i class="mdi mdi-file-document-outline"></i>
                    </div>
                </div>
                <div class="stat-desc d-flex align-items-center gap-1 mt-2">
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-0 fs-11">
                        {{ $stats['createdCount'] ?? 0 }} Baru
                    </span>
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0 fs-11">
                        {{ $stats['partialCount'] ?? 0 }} Parsial
                    </span>
                </div>
            </div>
        </div>

        <!-- Card 2: Total Nilai Tagihan -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Total Tagihan (DPP+Pajak)</div>
                        <div class="stat-value">Rp {{ number_format($stats['totalBilling'] ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <div class="stat-icon-wrapper bg-info-subtle text-info">
                        <i class="mdi mdi-cash-multiple"></i>
                    </div>
                </div>
                <div class="stat-desc mt-2 text-truncate">
                    <i class="mdi mdi-information-outline me-1"></i>Akumulasi seluruh tagihan aktif
                </div>
            </div>
        </div>

        <!-- Card 3: Sudah Terbayar -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Pembayaran Masuk</div>
                        <div class="stat-value text-success">Rp {{ number_format($stats['totalPaid'] ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <div class="stat-icon-wrapper bg-success-subtle text-success">
                        <i class="mdi mdi-check-decagram-outline"></i>
                    </div>
                </div>
                <div class="stat-desc mt-2 text-truncate text-success">
                    <i class="mdi mdi-arrow-down-bold-circle-outline me-1"></i>Dana cicilan yang diterima
                </div>
            </div>
        </div>

        <!-- Card 4: Sisa Piutang Berjalan -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Sisa Piutang Aktif</div>
                        <div class="stat-value text-danger">Rp {{ number_format($stats['totalRemaining'] ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <div class="stat-icon-wrapper bg-danger-subtle text-danger">
                        <i class="mdi mdi-alert-circle-outline"></i>
                    </div>
                </div>
                <div class="stat-desc mt-2 text-truncate text-danger">
                    <i class="mdi mdi-clock-alert-outline me-1"></i>Wajib ditagih ke pelanggan
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="table-container-card mb-4">
        <!-- Top Toolbar & Status Filter Pills -->
        <div class="table-top-bar d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="text-muted fw-bold fs-11 text-uppercase me-1" style="letter-spacing: 0.5px;">Filter Status:</span>
                <button type="button" class="filter-pill-btn active" data-filter="all">
                    <i class="mdi mdi-view-grid-outline"></i> Semua
                    <span class="badge-pill-count">{{ $stats['totalCount'] ?? 0 }}</span>
                </button>
                <button type="button" class="filter-pill-btn" data-filter="created">
                    <i class="mdi mdi-file-document-outline"></i> Belum Bayar
                    <span class="badge-pill-count">{{ $stats['createdCount'] ?? 0 }}</span>
                </button>
                <button type="button" class="filter-pill-btn" data-filter="partial">
                    <i class="mdi mdi-clock-check-outline"></i> Bayar Sebagian
                    <span class="badge-pill-count">{{ $stats['partialCount'] ?? 0 }}</span>
                </button>
            </div>
            <div class="text-muted fs-12">
                <i class="mdi mdi-information-outline me-1 text-primary"></i>Klik tombol <strong>+ On Charge</strong> untuk melihat rincian biaya tambahan.
            </div>
        </div>

        <div class="card-body p-3">
            @include('partials.alert')
            <div class="table-responsive custom-scrollbar">
                <table class="table w-100 nowrap invoice-table" id="dt">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 130px;">Aksi</th>
                            <th class="text-center" style="width: 45px;">No</th>
                            <th>No. Faktur</th>
                            <th>Pelanggan</th>
                            <th>Tgl & Jatuh Tempo</th>
                            <th class="text-center">Total SJ</th>
                            <th class="text-end">Harga DPP</th>
                            <th class="text-end">PPN</th>
                            <th class="text-end">PPh 23</th>
                            <th class="text-end">Total Tagihan</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Saran Nomor Baru -->
<div class="modal fade" id="updateNumberModal" tabindex="-1" aria-labelledby="updateNumberModalLabel" aria-hidden="true">
    <div class="modal-dialog mt-4">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
            <div class="modal-header bg-secondary-subtle py-3 px-4 border-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-secondary text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="mdi mdi-auto-fix fs-18"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-secondary mb-0" id="updateNumberModalLabel">Update Nomor Faktur</h5>
                        <span class="text-muted fs-11">Sinkronisasi nomor faktur berdasarkan urutan tanggal</span>
                    </div>
                </div>
                <button type="button" class="btn-close text-secondary" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="modalInvoiceId">

                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">Nomor Invoice Saat Ini</label>
                    <div class="p-2 rounded bg-light border fw-bold text-danger font-monospace fs-14" id="modalCurrentInvoiceNumber">-</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">Nomor Invoice Baru (Saran Sistem)</label>
                    <div class="d-flex align-items-center gap-2">
                        <input type="text" class="form-control font-monospace fw-bold text-success rounded-3" id="modalNewInvoiceNumber" placeholder="Memuat saran nomor...">
                        <button type="button" class="btn btn-outline-secondary btn-sm flex-shrink-0 rounded-circle p-2" id="btnRefreshSuggest" title="Muat Ulang Saran" style="width: 36px; height: 36px;">
                            <i class="mdi mdi-refresh"></i>
                        </button>
                    </div>
                    <div class="form-text text-muted mt-2 fs-12">
                        Nomor dihitung otomatis berdasarkan tanggal invoice & urutan terakhir di bulan tersebut. Anda dapat mengubahnya secara manual jika diperlukan.
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light px-4 py-3 border-0">
                <button type="button" class="btn btn-secondary px-3 rounded-pill" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success px-4 fw-semibold rounded-pill" id="btnConfirmUpdateNumber">
                    <i class="mdi mdi-check-circle me-1"></i> Simpan Nomor Baru
                </button>
            </div>
        </div>
    </div>
</div>

<form id="delete-form" method="post">
    @csrf
    @method('DELETE')
</form>

@include('invoice.partials.breakdown-modal')
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
<script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>

<script>
    $(document).ready(function() {
        var table = $('#dt').DataTable({
            "processing": true,
            "serverSide": true,
            "destroy": true,
            "pageLength": 25,
            "ajax": {
                "url": "{{ route('dt.invoice.unpaid') }}",
            },
            "columns": [
                { "data": 'action', "className": "text-center" },
                { "data": 'DT_RowIndex', "className": "text-center" },
                { "data": 'invoiceNumber' },
                { "data": 'customer.name' },
                { "data": 'invoiceDate' },
                { "data": 'orderCount', "className": "text-center" },
                { "data": 'price', "className": "text-end" },
                { "data": 'ppn', "className": "text-end" },
                { "data": 'pph', "className": "text-end" },
                { "data": 'totalBilling', "className": "text-end" },
                { "data": 'status', "className": "text-center" }
            ],
            "columnDefs": [
                { "searchable": false, "targets": [0, 1] },
                { "orderable": false, "targets": [0, 1] }
            ],
            "order": [],
            "language": {
                "search": "",
                "searchPlaceholder": "Cari no faktur, pelanggan...",
                "lengthMenu": "Tampilkan _MENU_ data",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ faktur",
                "infoEmpty": "Tidak ada data faktur",
                "zeroRecords": "Tidak ditemukan data yang sesuai",
                "paginate": {
                    "next": "<i class='mdi mdi-chevron-right'></i>",
                    "previous": "<i class='mdi mdi-chevron-left'></i>"
                }
            }
        });

        // Quick status filter pills
        $('.filter-pill-btn').on('click', function() {
            $('.filter-pill-btn').removeClass('active');
            $(this).addClass('active');
            var filter = $(this).data('filter');
            if (filter === 'all') {
                table.column(10).search('').draw();
            } else if (filter === 'created') {
                table.column(10).search('Belum Bayar').draw();
            } else if (filter === 'partial') {
                table.column(10).search('Sebagian').draw();
            }
        });

        // Refresh table button
        $('#btn-refresh-table').on('click', function() {
            var btn = $(this);
            btn.find('i').addClass('mdi-spin');
            table.ajax.reload(function() {
                btn.find('i').removeClass('mdi-spin');
            }, false);
        });
    });

    function deleteData(uuid) {
        var url = "{{ url('invoice') }}" + '/' + uuid;
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

    function recalculateInvoice(id) {
        swal({
            title: "Hitung Ulang Invoice?",
            text: "Jumlah invoice dan PPN akan dihitung ulang berdasarkan data order saat ini. Jika Anda melanjutkan, SEMUA pembayaran untuk invoice ini akan dibatalkan!",
            icon: "warning",
            buttons: ["Batal", "Ya, Hitung Ulang!"],
            dangerMode: true,
        }).then((willRecalculate) => {
            if (willRecalculate) {
                swal({
                    title: "Memproses...",
                    text: "Sedang menghitung ulang invoice",
                    icon: "info",
                    buttons: false,
                    closeOnClickOutside: false,
                    closeOnEsc: false,
                });

                $.ajax({
                    url: "{{ route('invoice.recalculate', ':id') }}".replace(':id', id),
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            swal({
                                title: "Berhasil!",
                                text: response.message,
                                icon: "success",
                            }).then(() => {
                                $('#dt').DataTable().ajax.reload(null, false);
                            });
                        } else {
                            swal("Gagal!", response.message, "error");
                        }
                    },
                    error: function(xhr) {
                        let msg = "Terjadi kesalahan";
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        swal("Gagal!", msg, "error");
                    }
                });
            }
        });
    }

    $(document).on('click', '.btn-suggest-number', function() {
        let id = $(this).data('id');
        let currentNumber = $(this).data('invoice-number');

        $('#modalInvoiceId').val(id);
        $('#modalCurrentInvoiceNumber').text(currentNumber);
        $('#modalNewInvoiceNumber').val('').attr('placeholder', 'Memuat saran nomor...');
        $('#btnConfirmUpdateNumber').prop('disabled', true);

        $('#updateNumberModal').modal('show');
        loadSuggestedNumber(id);
    });

    function loadSuggestedNumber(id) {
        $('#btnRefreshSuggest').prop('disabled', true);
        $.ajax({
            url: "{{ url('ajax/invoice') }}/" + id + "/suggest-number",
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    $('#modalNewInvoiceNumber').val(response.suggestedNumber);
                    $('#btnConfirmUpdateNumber').prop('disabled', false);
                } else {
                    $('#modalNewInvoiceNumber').val('').attr('placeholder', 'Gagal memuat saran nomor');
                }
            },
            error: function() {
                $('#modalNewInvoiceNumber').val('').attr('placeholder', 'Gagal memuat saran nomor');
            },
            complete: function() {
                $('#btnRefreshSuggest').prop('disabled', false);
            }
        });
    }

    $('#btnRefreshSuggest').on('click', function() {
        let id = $('#modalInvoiceId').val();
        loadSuggestedNumber(id);
    });

    $('#btnConfirmUpdateNumber').on('click', function() {
        let id = $('#modalInvoiceId').val();
        let newNumber = $('#modalNewInvoiceNumber').val().trim();

        if (!newNumber) {
            swal("Perhatian!", "Nomor invoice tidak boleh kosong.", "warning");
            return;
        }

        let btn = $(this);
        let origText = btn.html();
        btn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin me-1"></i>Menyimpan...');

        $.ajax({
            url: "{{ route('invoice.update-number', ':id') }}".replace(':id', id),
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                invoiceNumber: newNumber,
            },
            success: function(response) {
                $('#updateNumberModal').modal('hide');
                swal({
                    title: "Berhasil!",
                    text: response.message || "Nomor invoice berhasil diupdate.",
                    icon: "success",
                }).then(() => {
                    $('#dt').DataTable().ajax.reload(null, false);
                });
            },
            error: function(xhr) {
                let msg = "Terjadi kesalahan saat mengupdate nomor invoice.";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                swal("Gagal!", msg, "error");
            },
            complete: function() {
                btn.prop('disabled', false).html(origText);
            }
        });
    });

    $('#btn-recalculate-all').on('click', function() {
        swal({
            title: "Hitung Ulang Semua Invoice?",
            text: "Tindakan ini akan menghitung ulang nilai invoice dan PPN untuk SEMUA invoice berdasarkan data order yang terkait saat ini.\n\nPERINGATAN: Semua pembayaran pada invoice yang dihitung ulang akan DIBATALKAN!",
            icon: "warning",
            buttons: ["Batal", "Ya, Hitung Ulang Semua!"],
            dangerMode: true,
        }).then((willRun) => {
            if (willRun) {
                swal({
                    title: "Sedang Memproses...",
                    text: "Mohon tunggu, proses ini mungkin membutuhkan beberapa saat.",
                    icon: "info",
                    buttons: false,
                    closeOnClickOutside: false,
                    closeOnEsc: false,
                });

                $.ajax({
                    url: "{{ route('invoice.recalculate-all') }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            swal({
                                title: "Selesai!",
                                text: response.message,
                                icon: "success",
                            }).then(() => {
                                $('#dt').DataTable().ajax.reload(null, false);
                            });
                        } else {
                            swal("Gagal!", response.message, "error");
                        }
                    },
                    error: function(xhr) {
                        let msg = "Terjadi kesalahan saat memproses hitung ulang massal.";
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        swal("Gagal!", msg, "error");
                    }
                });
            }
        });
    });

    // Event handler to view invoice breakdown & on charge modal
    $(document).on('click', '.btn-view-invoice-breakdown', function(e) {
        e.preventDefault();
        const data = $(this).data('breakdown');
        if (!data) return;

        $('#modalBreakdownInvoiceNo').text(data.invoiceNumber || '-');
        $('#modalBreakdownCustomer').text(data.customerName || '-');
        $('#modalBreakdownDate').text(data.invoiceDate || '-');
        $('#modalBreakdownSubtitle').text('Faktur: ' + (data.invoiceNumber || '-') + ' | ' + (data.customerName || '-'));

        $('#modalBreakdownTotalRoute').text(data.totalRouteFormatted || 'Rp 0');
        $('#modalBreakdownTotalOnCharge').text('+ ' + (data.totalOnChargeFormatted || 'Rp 0'));
        $('#modalBreakdownSubtotal').text(data.subtotalFormatted || 'Rp 0');
        $('#modalBreakdownGrandTotal').text(data.grandTotalFormatted || 'Rp 0');

        // Populate components table
        const compBody = $('#modalBreakdownComponentBody');
        compBody.empty();
        if (data.components && Object.keys(data.components).length > 0) {
            let idx = 1;
            for (const [name, nominal] of Object.entries(data.components)) {
                compBody.append(`
                    <tr>
                        <td class="text-center">${idx++}</td>
                        <td class="fw-semibold text-dark">${name}</td>
                        <td class="text-end fw-bold text-warning">Rp ${Number(nominal).toLocaleString('id-ID')}</td>
                    </tr>
                `);
            }
            $('#modalBreakdownComponentSection').removeClass('d-none');
        } else {
            $('#modalBreakdownComponentSection').addClass('d-none');
        }

        // Populate orders table
        const orderBody = $('#modalBreakdownOrderBody');
        orderBody.empty();
        if (data.orders && data.orders.length > 0) {
            data.orders.forEach((ord, idx) => {
                orderBody.append(`
                    <tr>
                        <td class="text-center">${idx + 1}</td>
                        <td><span class="badge bg-light text-dark border font-monospace">${ord.shipment}</span></td>
                        <td class="fs-11">${ord.route}</td>
                        <td class="text-end">${ord.basePriceFormatted}</td>
                        <td class="text-end ${ord.onCharge > 0 ? 'text-warning fw-semibold' : 'text-muted'}">${ord.onCharge > 0 ? '+ ' + ord.onChargeFormatted : '-'}</td>
                        <td class="text-end fw-bold text-dark">${ord.totalFormatted}</td>
                    </tr>
                `);
            });
        }

        $('#modalInvoiceCostBreakdown').modal('show');
    });
</script>
@endpush
