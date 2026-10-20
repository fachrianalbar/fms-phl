@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Supplier',
    'secondSegment' => $title,
])

@push('style')
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}">

    @include('purchasing.purchase-payment.partials.table-style')

    <style>
        /* Baris rekap dapat diklik untuk membuka detail PO supplier. */
        #dt tbody tr {
            cursor: pointer;
        }
        .supplier-name-cell {
            font-weight: 600;
            color: #0f172a;
        }
        .supplier-count-badge {
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 20px;
            font-weight: 700;
        }
        html[data-bs-theme="dark"] .supplier-name-cell {
            color: var(--bs-heading-color);
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
                    </h4>
                    <p class="text-muted mb-0 fs-12">
                        Rekap utang per supplier. Klik supplier untuk melihat daftar PO &amp; proses pembayarannya.
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
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Total Hutang</div>
                            <div class="stat-value text-primary">
                                <span class="fs-16">Rp</span> {{ number_format($stats['totalBilling'] ?? 0, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="stat-icon-wrapper bg-primary-subtle text-primary">
                            <i class="mdi mdi-calculator"></i>
                        </div>
                    </div>
                    <div class="stat-desc text-truncate">
                        <i class="mdi mdi-cart-outline me-1 text-primary"></i>
                        <strong>{{ number_format($stats['totalCount'] ?? 0) }}</strong> PO belum lunas
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card">
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
                <div class="stat-card">
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
                <div class="stat-card">
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
                        <span>Semua Supplier</span>
                    </button>
                </div>
            </div>

            <div class="card-body p-3">
                <div class="text-muted fs-12 mb-2">
                    <i class="mdi mdi-information-outline me-1 text-primary"></i>
                    Klik baris supplier atau tombol <strong>Detail</strong> untuk melihat daftar PO
                    dan membayar hutang (DP, cicilan &amp; pelunasan).
                </div>
                <div class="table-responsive custom-scrollbar">
                    <table class="table table-striped nowrap purchase-payment-table" id="dt">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 45px;">No</th>
                                <th>Supplier</th>
                                <th class="text-center">Jumlah PO</th>
                                <th class="text-center">Belum Bayar</th>
                                <th class="text-center">Belum Lunas</th>
                                <th class="text-end">Total Tagihan</th>
                                <th class="text-end">Terbayar</th>
                                <th class="text-end">Sisa Hutang</th>
                                <th class="text-center" style="width: 110px;">Aksi</th>
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

    <script>
        $(document).ready(function() {
            const dt = $('#dt').DataTable({
                "processing": true,
                "serverSide": true,
                "destroy": true,
                "scrollX": true,
                "autoWidth": false,
                "pageLength": 25,
                "ajax": {
                    "url": "{{ route('dt.purchase-payment.unpaid-supplier') }}"
                },
                "columns": [{
                        "data": 'DT_RowIndex',
                        "className": 'text-center align-middle',
                        "orderable": false,
                        "searchable": false
                    },
                    {
                        "data": 'supplierName',
                        "className": 'align-middle supplier-name-cell'
                    },
                    {
                        "data": 'poCount',
                        "className": 'text-center align-middle',
                        "searchable": false,
                        "render": function(data) {
                            return '<span class="supplier-count-badge bg-primary-subtle text-primary">' + data + ' PO</span>';
                        }
                    },
                    {
                        "data": 'unpaidCount',
                        "className": 'text-center align-middle',
                        "searchable": false,
                        "render": function(data) {
                            return data > 0
                                ? '<span class="supplier-count-badge bg-danger-subtle text-danger">' + data + '</span>'
                                : '<span class="text-muted">0</span>';
                        }
                    },
                    {
                        "data": 'partialCount',
                        "className": 'text-center align-middle',
                        "searchable": false,
                        "render": function(data) {
                            return data > 0
                                ? '<span class="supplier-count-badge bg-warning-subtle text-warning-emphasis">' + data + '</span>'
                                : '<span class="text-muted">0</span>';
                        }
                    },
                    {
                        "data": 'totalBillingHtml',
                        "className": 'text-end align-middle payment-money',
                        "searchable": false
                    },
                    {
                        "data": 'totalPaidHtml',
                        "className": 'text-end align-middle payment-money',
                        "searchable": false
                    },
                    {
                        "data": 'totalRemainingHtml',
                        "className": 'text-end align-middle payment-money',
                        "searchable": false
                    },
                    {
                        "data": 'action',
                        "className": 'text-center align-middle',
                        "orderable": false,
                        "searchable": false
                    }
                ],
                "order": [
                    [7, 'desc']
                ],
                "language": {
                    "processing": "Memuat data...",
                    "search": "",
                    "searchPlaceholder": "Cari nama supplier...",
                    "lengthMenu": "Tampilkan _MENU_ data",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ supplier",
                    "infoEmpty": "Tidak ada data",
                    "zeroRecords": "Tidak ditemukan supplier yang sesuai",
                    "paginate": {
                        "next": "<i class='mdi mdi-chevron-right'></i>",
                        "previous": "<i class='mdi mdi-chevron-left'></i>"
                    }
                }
            });

            // Klik baris → buka detail PO supplier
            $(document).on('click', '#dt tbody tr', function(event) {
                if ($(event.target).closest('a, button, input, select').length) {
                    return;
                }

                const rowData = dt.row(this).data();

                if (!rowData || !rowData.supplierCode) return;

                window.location.href = '{{ route("purchasing.purchase-payment.index") }}' + '/supplier/' + encodeURIComponent(rowData.supplierCode);
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
        });
    </script>
@endpush
