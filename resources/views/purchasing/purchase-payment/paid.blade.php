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
@endpush

@section('content')
    <div class="col-sm-12">
        {{-- Page Header --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center shadow-sm text-white"
                    style="width: 48px; height: 48px; background: linear-gradient(135deg, #22c55e 0%, #15803d 100%) !important;">
                    <i class="mdi mdi-cash-check fs-24"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        {{ $title }}
                        <span
                            class="badge bg-success-subtle text-success border border-success-subtle rounded-pill fs-12 px-2 py-1">
                            {{ number_format($stats['totalCount'] ?? 0) }} Pembelian
                        </span>
                    </h4>
                    <p class="text-muted mb-0 fs-12">
                        Arsip pembelian yang sudah lunas. Pembayaran dapat dibatalkan untuk mengembalikan status hutang.
                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('purchasing.purchase-payment.index') }}"
                    class="btn btn-outline-warning btn-sm rounded-pill px-3 shadow-sm">
                    <i class="mdi mdi-cash-remove me-1"></i>Hutang Belum Lunas
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
                <div class="stat-card" title="Jumlah pembelian lunas">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Pembelian Lunas</div>
                            <div class="stat-value text-success">
                                {{ number_format($stats['totalCount'] ?? 0) }}
                                <span class="fs-13 text-muted fw-normal">PO</span>
                            </div>
                        </div>
                        <div class="stat-icon-wrapper bg-success-subtle text-success">
                            <i class="mdi mdi-check-decagram-outline"></i>
                        </div>
                    </div>
                    <div class="stat-desc text-success text-truncate">
                        <i class="mdi mdi-check-circle-outline me-1"></i>Seluruh tagihan sudah terbayar
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card" title="Total nilai pembayaran">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Total Dibayar</div>
                            <div class="stat-value text-dark">
                                <span class="fs-16">Rp</span>
                                {{ number_format($stats['totalPaid'] ?? 0, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="stat-icon-wrapper bg-primary-subtle text-primary">
                            <i class="mdi mdi-cash-multiple"></i>
                        </div>
                    </div>
                    <div class="stat-desc text-truncate">
                        <i class="mdi mdi-bank-outline me-1 text-primary"></i>Akumulasi pembayaran ke supplier
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Table --}}
        <div class="table-container-card mb-4">
            <div class="table-top-bar">
                <form id="filterForm" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label mb-1 fs-12" for="code">Kode Pembelian</label>
                        <input class="form-control form-control-sm" name="code" type="text" placeholder="Kode pembelian">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label mb-1 fs-12" for="supplierCode">Supplier</label>
                        <select class="form-select form-select-sm" name="supplierCode" id="supplierCode">
                            <option value="">Semua Supplier</option>
                            @foreach ($supplier as $item)
                                <option value="{{ $item->code }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label mb-1 fs-12" for="startDate">Dari Tanggal</label>
                        <input class="form-control form-control-sm" name="startDate" type="date">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label mb-1 fs-12" for="endDate">Sampai Tanggal</label>
                        <input class="form-control form-control-sm" name="endDate" type="date">
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button class="btn btn-primary btn-sm flex-fill" type="submit">
                            <i class="mdi mdi-magnify me-1"></i>Filter
                        </button>
                        <button class="btn btn-light btn-sm" type="button" id="btn-reset-filter" title="Reset filter">
                            <i class="mdi mdi-refresh"></i>
                        </button>
                    </div>
                </form>
            </div>

            <div class="card-body p-3">
                <div class="text-muted fs-12 mb-3">
                    <i class="mdi mdi-information-outline me-1 text-success"></i>
                    Daftar pembelian yang sudah lunas. Klik <i class="mdi mdi-close-circle-outline text-danger"></i>
                    untuk membatalkan pembayaran batch dan mengembalikan status hutang.
                </div>
                <div class="table-responsive custom-scrollbar">
                    <table class="table table-striped nowrap purchase-payment-table" id="dt">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 110px;">Aksi</th>
                                <th class="text-center" style="width: 45px;">No</th>
                                <th>Kode Pembelian</th>
                                <th class="text-center">Tanggal</th>
                                <th class="text-center">Tanggal Bayar</th>
                                <th>Supplier</th>
                                <th class="text-end">Total Tagihan</th>
                                <th class="text-end">Terbayar</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
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

    <script src="{{ asset('assets/js/sweet-alert/sweetalert2.min.js') }}"></script>

    @include('purchasing.purchase-payment.partials.flash-swal')

    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2-custom.js') }}"></script>

    @include('purchasing.purchase-payment.partials.detail-modal-script')

    <script>
        $(document).ready(function() {
            const cancelUrlTemplate = @json(route('purchasing.purchase-payment.batch.cancel', ['batchCode' => '__BATCH__']));
            const csrfToken = @json(csrf_token());

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

            const dt = $('#dt').DataTable({
                "processing": true,
                "serverSide": true,
                "destroy": true,
                "scrollX": true,
                "autoWidth": false,
                "pageLength": 25,
                "ajax": {
                    "url": "{{ route('dt.purchase-payment.paid') }}",
                    "data": function(d) {
                        d.code = $('input[name="code"]').val();
                        d.supplierCode = $('select[name="supplierCode"]').val();
                        d.startDate = $('input[name="startDate"]').val();
                        d.endDate = $('input[name="endDate"]').val();
                    }
                },
                "columns": [{
                        "data": 'action',
                        "className": 'text-center align-middle',
                        "orderable": false,
                        "searchable": false
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
                        "data": 'paymentDateHtml',
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
                        "className": 'text-end align-middle payment-money fw-semibold text-success'
                    },
                    {
                        "data": 'paymentStatusHtml',
                        "className": 'text-center align-middle'
                    }
                ],
                "order": [
                    [4, 'desc']
                ],
                "drawCallback": function() {
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

            // Filter
            $('#filterForm').on('submit', function(event) {
                event.preventDefault();
                dt.ajax.reload();
            });

            $('#btn-reset-filter').on('click', function() {
                $('#filterForm')[0].reset();
                $('#supplierCode').val('').trigger('change');
                dt.ajax.reload();
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

            // Batal pembayaran batch
            $(document).on('click', '.btn-cancel-row', function() {
                const batchCode = String($(this).attr('data-batch') || '');
                const code = String($(this).attr('data-code') || '');

                if (!batchCode) {
                    Swal.fire({
                        title: 'Tidak dapat dibatalkan',
                        text: 'Kode batch pembayaran tidak tersedia untuk data ini.',
                        icon: 'warning',
                    });
                    return;
                }

                Swal.fire({
                    title: 'Batalkan pembayaran?',
                    html: 'Pembayaran batch <strong>' + batchCode + '</strong> untuk pembelian <strong>' +
                        code +
                        '</strong> akan dibatalkan.<br>Saldo bank dikembalikan dan status pembelian dihitung ulang.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Batalkan',
                    cancelButtonText: 'Kembali',
                    confirmButtonColor: '#dc3545',
                }).then(function(result) {
                    if (!result.isConfirmed) {
                        return;
                    }

                    swalLoader('Membatalkan pembayaran',
                        'Sedang mengembalikan saldo dan memperbarui status pembelian...');

                    $.ajax({
                        url: cancelUrlTemplate.replace('__BATCH__', encodeURIComponent(batchCode)),
                        type: 'DELETE',
                        dataType: 'json',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        success: function(response) {
                            Swal.close();
                            Swal.fire({
                                title: 'Pembayaran dibatalkan',
                                text: (response && response.message) ? response.message :
                                    'Pembayaran berhasil dibatalkan.',
                                icon: 'success',
                                confirmButtonText: 'Lihat Daftar Terbaru',
                                confirmButtonColor: '#198754',
                            }).then(function() {
                                window.location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.close();
                            const message = (xhr.responseJSON && xhr.responseJSON.message) ?
                                xhr.responseJSON.message :
                                'Pembatalan gagal diproses. Silakan coba lagi.';

                            Swal.fire({
                                title: 'Pembatalan gagal',
                                text: message,
                                icon: 'error',
                                confirmButtonText: 'Tutup',
                            });
                        }
                    });
                });
            });

            // Select2 supplier + length menu
            $('#supplierCode').select2({
                width: '100%',
            });
            $('#dt_wrapper .dataTables_length select').select2({
                minimumResultsForSearch: Infinity,
                width: '88px',
                dropdownAutoWidth: true,
            });
        });
    </script>
@endpush
