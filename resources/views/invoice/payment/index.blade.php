@extends('layouts.main', [
'title' => $title,
'pageTitle' => $title,
'firstSegment' => 'Faktur',
'secondSegment' => 'Invoice Payment',
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
                <i class="mdi mdi-cash-register fs-24"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    {{ $title }}
                    <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill fs-12 px-2 py-1">
                        {{ number_format($stats['paymentCount'] ?? 0) }} Transaksi
                    </span>
                </h4>
                <p class="text-muted mb-0 fs-12">Riwayat pencatatan bukti pelunasan faktur, mutasi bank penerima, dan pelunasan bertahap.</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm" id="btn-refresh-table" title="Muat Ulang Data Tabel">
                <i class="mdi mdi-refresh me-1"></i> Refresh
            </button>
            <a href="{{ route('invoice.payment.export-pdf') }}" id="export-pdf" class="btn btn-sm btn-outline-danger rounded-pill px-3 shadow-sm fw-semibold" target="_blank">
                <i class="mdi mdi-file-pdf me-1"></i> Export PDF
            </a>
            <a href="{{ route('invoice.payment.export-excel') }}" id="export-excel" class="btn btn-sm btn-outline-success rounded-pill px-3 shadow-sm fw-semibold" target="_blank">
                <i class="mdi mdi-file-excel me-1"></i> Export Excel
            </a>
            <a href="{{ route('invoice.unpaid') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm text-white fw-semibold" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
                <i class="mdi mdi-cash-plus me-1"></i> Input Pembayaran Baru
            </a>
        </div>
    </div>

    <!-- 2 KPI Summary Cards -->
    <div class="row g-3 mb-4">
        <!-- Card 1: Total Transaksi Pembayaran -->
        <div class="col-12 col-sm-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Total Transaksi Pembayaran</div>
                        <div class="stat-value text-info">{{ number_format($stats['paymentCount'] ?? 0) }} <span class="fs-13 text-muted fw-normal">Kali Mutasi</span></div>
                    </div>
                    <div class="stat-icon-wrapper bg-info-subtle text-info">
                        <i class="mdi mdi-receipt-text-check-outline"></i>
                    </div>
                </div>
                <div class="stat-desc text-muted mt-2">
                    <i class="mdi mdi-history me-1"></i>Akumulasi bukti pembayaran tercatat
                </div>
            </div>
        </div>

        <!-- Card 2: Total Dana Masuk -->
        <div class="col-12 col-sm-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Total Dana Pembayaran Diterima</div>
                        <div class="stat-value text-success">Rp {{ number_format($stats['paymentSum'] ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <div class="stat-icon-wrapper bg-success-subtle text-success">
                        <i class="mdi mdi-cash-check"></i>
                    </div>
                </div>
                <div class="stat-desc text-success mt-2">
                    <i class="mdi mdi-arrow-down-bold-circle-outline me-1"></i>Total realisasi dana kas/bank masuk
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="table-container-card mb-4">
        <div class="table-top-bar d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3 py-1 fw-bold fs-12">
                    <i class="mdi mdi-format-list-bulleted me-1"></i> Riwayat Pembayaran Invoice
                </span>
            </div>
            <div class="text-muted fs-12">
                <i class="mdi mdi-information-outline me-1 text-primary"></i>Gunakan filter pencarian untuk menemukan riwayat pembayaran per faktur atau rekening bank.
            </div>
        </div>

        <div class="card-body p-3">
            @include('partials.alert')
            <div class="table-responsive custom-scrollbar">
                <table class="table w-100 nowrap invoice-table" id="dt">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 45px;">No</th>
                            <th>No. Faktur</th>
                            <th>Pelanggan</th>
                            <th>Tgl Faktur</th>
                            <th>Bank Penerima</th>
                            <th class="text-end">Total Tagihan</th>
                            <th>Rincian Cicilan / Pelunasan</th>
                            <th class="text-end">Total Bayar</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
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
                "url": "{{ route('dt.invoice.payment') }}",
            },
            "columns": [
                {
                    "data": 'DT_RowIndex',
                    "orderable": false,
                    "searchable": false,
                    "className": "text-center"
                },
                {
                    "data": 'invoiceNumber'
                },
                {
                    "data": 'customer.name'
                },
                {
                    "data": 'invoiceDate'
                },
                {
                    "data": 'receivingBank'
                },
                {
                    "data": 'totalBilling',
                    "className": "text-end"
                },
                {
                    "data": 'paymentDetails'
                },
                {
                    "data": 'totalPayment',
                    "className": "text-end"
                },
                {
                    "data": 'statusPayment',
                    "className": "text-center"
                },
            ],
            "columnDefs": [{
                "searchable": false,
                "targets": [0]
            }, {
                "orderable": false,
                "targets": [0]
            }],
            "order": [],
            "language": {
                "search": "",
                "searchPlaceholder": "Cari no faktur, pelanggan, bank...",
                "lengthMenu": "Tampilkan _MENU_ data",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data pembayaran",
                "infoEmpty": "Tidak ada data pembayaran",
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

    function deleteData(uuid) {
        var url = "{{ route('invoice.payment.index') }}" + '/' + uuid;
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
</script>
@endpush
