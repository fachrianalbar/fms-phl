@extends('layouts.main', [
'title' => $title,
'pageTitle' => $title,
'firstSegment' => 'Faktur',
'secondSegment' => 'Transaksi Pembayaran',
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
            <div class="bg-info text-white rounded-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px; background: linear-gradient(135deg, #06b6d4 0%, #0284c7 100%) !important;">
                <i class="mdi mdi-receipt-text-check-outline fs-24"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    Transaksi Pembayaran
                    <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill fs-12 px-2 py-1">
                        {{ number_format($stats['transactionCount'] ?? 0) }} Transaksi
                    </span>
                </h4>
                <p class="text-muted mb-0 fs-12">Daftar transaksi penerimaan pembayaran. Satu transaksi dapat menutup banyak faktur sekaligus beserta claim (pengurang tagihan).</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm" id="btn-refresh-table" title="Muat Ulang Data Tabel">
                <i class="mdi mdi-refresh me-1"></i> Refresh
            </button>
            <a href="{{ route('invoice.payment-transaction.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm text-white fw-semibold" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
                <i class="mdi mdi-cash-plus me-1"></i> Transaksi Pembayaran Baru
            </a>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="row g-3 mb-4">
        <!-- Card 1: Total Transaksi -->
        <div class="col-12 col-sm-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Total Transaksi Pembayaran</div>
                        <div class="stat-value text-info">{{ number_format($stats['transactionCount'] ?? 0) }} <span class="fs-13 text-muted fw-normal">Transaksi</span></div>
                    </div>
                    <div class="stat-icon-wrapper bg-info-subtle text-info">
                        <i class="mdi mdi-swap-horizontal-bold"></i>
                    </div>
                </div>
                <div class="stat-desc text-muted mt-2">
                    <i class="mdi mdi-history me-1"></i>Akumulasi transaksi penerimaan pembayaran
                </div>
            </div>
        </div>

        <!-- Card 2: Total Dana Diterima -->
        <div class="col-12 col-sm-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Total Dana Diterima</div>
                        <div class="stat-value text-success">Rp {{ number_format($stats['totalReceived'] ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <div class="stat-icon-wrapper bg-success-subtle text-success">
                        <i class="mdi mdi-cash-check"></i>
                    </div>
                </div>
                <div class="stat-desc text-success mt-2">
                    <i class="mdi mdi-arrow-down-bold-circle-outline me-1"></i>Total realisasi kas/bank masuk
                </div>
            </div>
        </div>

        <!-- Card 3: Total Claim -->
        <div class="col-12 col-sm-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Total Claim (Pengurang)</div>
                        <div class="stat-value text-warning-emphasis">Rp {{ number_format($stats['totalClaim'] ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <div class="stat-icon-wrapper bg-warning-subtle text-warning-emphasis">
                        <i class="mdi mdi-cash-refund"></i>
                    </div>
                </div>
                <div class="stat-desc text-muted mt-2">
                    <i class="mdi mdi-alert-circle-outline me-1"></i>Biaya lain-lain pengurang tagihan faktur
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="table-container-card mb-4">
        <div class="table-top-bar d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3 py-1 fw-bold fs-12">
                    <i class="mdi mdi-format-list-bulleted me-1"></i> Daftar Transaksi Pembayaran
                </span>
            </div>
            <div class="text-muted fs-12">
                <i class="mdi mdi-information-outline me-1 text-primary"></i>Klik kode transaksi untuk melihat detail faktur yang terbayar di dalamnya.
            </div>
        </div>

        <div class="card-body p-3">
            @include('partials.alert')
            <div class="table-responsive custom-scrollbar">
                <table class="table w-100 nowrap invoice-table" id="dt">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 60px;">Aksi</th>
                            <th class="text-center" style="width: 45px;">No</th>
                            <th>Kode Transaksi</th>
                            <th>Pelanggan</th>
                            <th>Tgl Bayar</th>
                            <th>Bank Penerima</th>
                            <th class="text-center">Jml Faktur</th>
                            <th class="text-end">Total Claim</th>
                            <th class="text-end">Total Diterima</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
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
                "url": "{{ route('dt.invoice.payment-transaction') }}",
            },
            "columns": [
                {
                    "data": 'action',
                    "orderable": false,
                    "searchable": false,
                    "className": "text-center"
                },
                {
                    "data": 'DT_RowIndex',
                    "orderable": false,
                    "searchable": false,
                    "className": "text-center"
                },
                { "data": 'code' },
                { "data": 'customer.name' },
                { "data": 'paymentDate' },
                { "data": 'receivingBank' },
                {
                    "data": 'invoiceCount',
                    "className": "text-center"
                },
                {
                    "data": 'totalClaim',
                    "className": "text-end"
                },
                {
                    "data": 'amount',
                    "className": "text-end"
                },
                {
                    "data": 'status',
                    "className": "text-center"
                },
            ],
            "columnDefs": [{
                "searchable": false,
                "targets": [0, 1]
            }, {
                "orderable": false,
                "targets": [0, 1]
            }],
            "order": [],
            "language": {
                "search": "",
                "searchPlaceholder": "Cari kode transaksi, pelanggan...",
                "lengthMenu": "Tampilkan _MENU_ data",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ transaksi",
                "infoEmpty": "Tidak ada data transaksi",
                "zeroRecords": "Tidak ditemukan data yang sesuai",
                "paginate": {
                    "next": "<i class='mdi mdi-chevron-right'></i>",
                    "previous": "<i class='mdi mdi-chevron-left'></i>"
                }
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
</script>
@endpush
