@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Supplier',
    'secondSegment' => $title,
])

@push('style')
    {{-- 1. CSS library yang belum global --}}
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

    {{-- 2. CSS bersama se-modul --}}
    @include('purchasing.purchase.partials.table-style')

    {{-- 3. CSS khusus halaman --}}
    <style>
        .stat-card.is-active {
            border-color: #2563eb;
            box-shadow: 0 8px 22px rgba(37, 99, 235, 0.15);
        }
        .purchase-table tbody td.text-end,
        .purchase-table tbody td.payment-money {
            font-variant-numeric: tabular-nums;
        }
    </style>
@endpush

@section('content')
    <div class="col-sm-12">
        {{-- 4. Page Header + aksi utama --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center shadow-sm text-white"
                    style="width: 48px; height: 48px; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important;">
                    <i class="mdi mdi-cart-outline fs-24"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        {{ $title }}
                        <span
                            class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill fs-12 px-2 py-1">
                            {{ number_format($stats['totalCount'] ?? 0) }} PO
                        </span>
                    </h4>
                    <p class="text-muted mb-0 fs-12">
                        Daftar pembelian (PO) beserta supplier, gudang, jatuh tempo, dan status pembayaran.
                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('report.supplier.index') }}" class="btn btn-outline-info btn-sm rounded-pill px-3 shadow-sm"
                    data-bs-toggle="tooltip" title="Laporan Pembelian Per Supplier">
                    <i class="mdi mdi-file-chart-outline me-1"></i> Laporan Supplier
                </a>
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm"
                    id="btn-refresh-table" title="Muat Ulang Data Tabel">
                    <i class="mdi mdi-refresh me-1" id="refresh-icon"></i> Refresh
                </button>
                <a href="{{ route($view . 'create') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                    <i class="mdi mdi-plus me-1"></i> {{ __('general.add_data') }}
                </a>
            </div>
        </div>

        {{-- 5. KPI stat cards --}}
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card is-active" data-filter="all" title="Klik untuk tampilkan semua pembelian">
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
                        <i class="mdi mdi-warehouse me-1 text-primary"></i>Seluruh transaksi pembelian
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card" data-filter="Paid" title="Klik untuk filter pembelian Lunas">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Sudah Lunas</div>
                            <div class="stat-value text-success">
                                {{ number_format($stats['paidCount'] ?? 0) }}
                                <span class="fs-13 text-muted fw-normal">PO</span>
                            </div>
                        </div>
                        <div class="stat-icon-wrapper bg-success-subtle text-success">
                            <i class="mdi mdi-check-decagram-outline"></i>
                        </div>
                    </div>
                    <div class="stat-desc text-success text-truncate">
                        <i class="mdi mdi-check-circle-outline me-1"></i>Tagihan sudah terbayar penuh
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card" data-filter="unpaid" title="Klik untuk filter pembelian Belum Lunas">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Belum Lunas</div>
                            <div class="stat-value text-danger">
                                {{ number_format(($stats['unpaidCount'] ?? 0) + ($stats['partialCount'] ?? 0)) }}
                                <span class="fs-13 text-muted fw-normal">PO</span>
                            </div>
                        </div>
                        <div class="stat-icon-wrapper bg-danger-subtle text-danger">
                            <i class="mdi mdi-alert-circle-outline"></i>
                        </div>
                    </div>
                    <div class="stat-desc text-danger text-truncate">
                        <i class="mdi mdi-close-circle-outline me-1"></i>
                        {{ number_format($stats['unpaidCount'] ?? 0) }} belum bayar ·
                        {{ number_format($stats['partialCount'] ?? 0) }} sebagian
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card" data-filter="all" title="Total nilai seluruh pembelian">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Total Nilai</div>
                            <div class="stat-value text-dark">
                                <span class="fs-16">Rp</span>
                                {{ number_format($stats['totalBilling'] ?? 0, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="stat-icon-wrapper bg-dark-subtle text-dark">
                            <i class="mdi mdi-cash-multiple"></i>
                        </div>
                    </div>
                    <div class="stat-desc text-truncate">
                        <i class="mdi mdi-sigma me-1 text-dark"></i>Nilai tagihan seluruh PO
                    </div>
                </div>
            </div>
        </div>

        {{-- 6. Kartu container tabel: filter + tabel --}}
        <div class="table-container-card mb-4">
            <div class="table-top-bar">
                <form id="filterForm" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label mb-1 fs-12" for="code">{{ __('menu_purchase.purchase_code') }}</label>
                        <input class="form-control form-control-sm" name="code" type="text"
                            placeholder="Kode pembelian">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label mb-1 fs-12" for="supplierCode">Supplier</label>
                        <select class="form-select form-select-sm" name="supplierCode"
                            id="supplierCode">
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
                    <i class="mdi mdi-information-outline me-1 text-primary"></i>
                    Daftar pembelian (PO). Klik kartu ringkasan di atas untuk memfilter berdasarkan status pembayaran,
                    atau gunakan kolom filter untuk menyaring kode, supplier, dan tanggal.
                </div>
                <div class="table-responsive custom-scrollbar">
                    <table class="table table-striped nowrap purchase-table" id="dt">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 110px;">Aksi</th>
                                <th class="text-center" style="width: 45px;">No</th>
                                <th>Kode Pembelian</th>
                                <th class="text-center">Tanggal</th>
                                <th class="text-center">Jatuh Tempo</th>
                                <th>Supplier</th>
                                <th>Gudang</th>
                                <th class="text-end">Total</th>
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
    {{-- 1. Ekstensi DataTables (core sudah global) --}}
    <script src="{{ asset('assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-select/js/dataTables.select.min.js') }}"></script>

    {{-- 2. SweetAlert2 DULU, sweetalert v1 KEDUA.
         v1 dimuat terakhir → window.swal = fungsi v1 (alur tunggal),
         Swal = class v2 (batch/loader). --}}
    <script src="{{ asset('assets/js/sweet-alert/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>

    {{-- Flash message server → SweetAlert2 --}}
    @include('purchasing.purchase.partials.flash-swal')

    {{-- 3. Select2 + helper --}}
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2-custom.js') }}"></script>
    <script src="{{ asset('assets/js/helper.js') }}"></script>

    {{-- 4. Script halaman --}}
    <script>
        // ==== STATE GLOBAL ====
        let dataTableInstance;
        let currentStatusFilter = '';

        $(document).ready(function() {
            // ==== DATATABLE ====
            dataTableInstance = $('#dt').DataTable({
                "processing": true,
                "serverSide": true,
                "destroy": true,
                "scrollX": true,
                "autoWidth": false,
                "pageLength": 25,
                "ajax": {
                    "url": "{{ route('dt.purchase') }}",
                    "data": function(d) {
                        d.code = $('input[name="code"]').val();
                        d.supplierCode = $('select[name="supplierCode"]').val();
                        d.startDate = $('input[name="startDate"]').val();
                        d.endDate = $('input[name="endDate"]').val();
                        d.paymentStatus = currentStatusFilter;
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
                        "data": 'dueDate',
                        "className": 'align-middle text-center'
                    },
                    {
                        "data": 'supplier.name',
                        "className": 'align-middle'
                    },
                    {
                        "data": 'warehouse.name',
                        "className": 'align-middle'
                    },
                    {
                        "data": 'totalPrice',
                        "className": 'text-end align-middle payment-money'
                    },
                    {
                        "data": 'paymentStatusHtml',
                        "className": 'text-center align-middle'
                    }
                ],
                "order": [
                    [3, 'desc']
                ],
                "drawCallback": function() {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                        [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                            .map(function(el) {
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

            // ==== FILTER ====
            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                dataTableInstance.ajax.reload();
            });

            $('#btn-reset-filter').on('click', function() {
                $('#filterForm')[0].reset();
                $('#supplierCode').val('').trigger('change');
                dataTableInstance.ajax.reload();
            });

            // ==== KPI CARD → FILTER STATUS ====
            $('.stat-card').on('click', function() {
                const filter = $(this).data('filter') || 'all';
                currentStatusFilter = (filter === 'all') ? '' : filter;

                $('.stat-card').removeClass('is-active');
                $(this).addClass('is-active');

                dataTableInstance.ajax.reload();
            });

            // ==== REFRESH ====
            $('#btn-refresh-table').on('click', function() {
                const icon = $('#refresh-icon');
                icon.addClass('mdi-spin');
                dataTableInstance.ajax.reload(function() {
                    setTimeout(function() {
                        icon.removeClass('mdi-spin');
                    }, 400);
                });
            });

            // ==== SELECT2 (filter supplier + length DataTables) ====
            $('#supplierCode').select2({
                width: '100%',
                dropdownAutoWidth: true,
            });

            $('#dt_wrapper .dataTables_length select').select2({
                minimumResultsForSearch: Infinity,
                width: '88px',
                dropdownAutoWidth: true,
            });
        });

        // ==== HAPUS DATA ====
        function deleteData(uuid) {
            var url = '{{ route('purchasing.purchase.index') }}/' + uuid;
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
