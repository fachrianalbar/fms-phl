@extends('layouts.main', [
'title' => $title,
'pageTitle' => $title,
'firstSegment' => 'Vendor',
'secondSegment' => 'Daftar Pembayaran',
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
            <div class="bg-primary text-white rounded-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important;">
                <i class="mdi mdi-credit-card-outline fs-24"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    {{ $title }}
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill fs-12 px-2 py-1">
                        {{ number_format($stats['paymentCount'] ?? 0) }} Transaksi
                    </span>
                </h4>
                <p class="text-muted mb-0 fs-12">Riwayat seluruh transaksi pembayaran ke vendor armada eksternal — termasuk DP, cicilan, dan pelunasan nota.</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm" id="btn-refresh-table" title="Muat Ulang Data Tabel">
                <i class="mdi mdi-refresh me-1"></i> Refresh
            </button>
        </div>
    </div>

    @include('partials.alert')

    <!-- 4 KPI Summary Cards -->
    <div class="row g-3 mb-4">
        <!-- Card 1: Jumlah Transaksi -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Jumlah Transaksi</div>
                        <div class="stat-value text-primary">{{ number_format($stats['paymentCount'] ?? 0) }}</div>
                    </div>
                    <div class="stat-icon-wrapper bg-primary-subtle text-primary">
                        <i class="mdi mdi-swap-horizontal"></i>
                    </div>
                </div>
                <div class="stat-desc text-muted mt-2">
                    <i class="mdi mdi-history me-1"></i>Termasuk DP dan cicilan
                </div>
            </div>
        </div>

        <!-- Card 2: Total Nominal Keluar -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Total Nominal Keluar</div>
                        <div class="stat-value text-success">Rp {{ number_format($stats['paymentSum'] ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <div class="stat-icon-wrapper bg-success-subtle text-success">
                        <i class="mdi mdi-cash-minus"></i>
                    </div>
                </div>
                <div class="stat-desc text-muted mt-2">
                    <i class="mdi mdi-arrow-up-bold-circle-outline me-1"></i>Total dana yang disalurkan ke vendor
                </div>
            </div>
        </div>

        <!-- Card 3: Nota Terlibat -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Nota Terlibat</div>
                        <div class="stat-value text-info">{{ number_format($stats['notaCount'] ?? 0) }}</div>
                    </div>
                    <div class="stat-icon-wrapper bg-info-subtle text-info">
                        <i class="mdi mdi-file-document-multiple-outline"></i>
                    </div>
                </div>
                <div class="stat-desc text-muted mt-2">
                    <i class="mdi mdi-file-document-outline me-1"></i>Nota unik dalam riwayat transaksi
                </div>
            </div>
        </div>

        <!-- Card 4: Vendor Berbeda -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Vendor Berbeda</div>
                        <div class="stat-value text-warning">{{ number_format($stats['vendorCount'] ?? 0) }}</div>
                    </div>
                    <div class="stat-icon-wrapper bg-warning-subtle text-warning">
                        <i class="mdi mdi-truck-check-outline"></i>
                    </div>
                </div>
                <div class="stat-desc text-muted mt-2">
                    <i class="mdi mdi-truck-outline me-1"></i>Perusahaan armada eksternal penerima dana
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="table-container-card mb-4">
        <div class="table-top-bar d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fw-bold fs-12">
                    <i class="mdi mdi-format-list-bulleted me-1"></i> Daftar Pembayaran Vendor
                </span>
            </div>
            <div class="text-muted fs-12">
                <i class="mdi mdi-information-outline me-1 text-primary"></i>Satu baris mewakili satu transaksi pembayaran (DP, cicilan, atau pelunasan).
            </div>
        </div>

        <div class="card-body p-3">
            <div class="table-responsive custom-scrollbar">
                <table class="table table-striped w-100 nowrap invoice-table" id="dt">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 45px;">No</th>
                            <th>{{ __('menu_vendor_payment.payment_date') }}</th>
                            <th>Kode Transaksi</th>
                            <th>No Nota</th>
                            <th>Order</th>
                            <th>Vendor</th>
                            <th class="text-end">Nominal</th>
                            <th>Bank Sumber Dana</th>
                            <th>Keterangan</th>
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
<script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>

<script>
    let paymentTable;

    $(document).ready(function() {
        paymentTable = $('#dt').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            pageLength: 25,
            ajax: "{{ route('dt.vendor-payment-list') }}",
            columns: [
                { data: 'DT_RowIndex', className: 'text-center' },
                { data: 'payment_date' },
                { data: 'batch_code' },
                { data: 'nota_number' },
                { data: 'order' },
                { data: 'vendor' },
                { data: 'amount', className: 'text-end' },
                { data: 'bank' },
                { data: 'description' },
            ],
            columnDefs: [
                { searchable: false, targets: [0] },
                { orderable: false, targets: [0] }
            ],
            order: [[1, 'desc']],
            language: {
                search: "",
                searchPlaceholder: "Cari kode transaksi, no nota, vendor...",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ transaksi",
                infoEmpty: "Tidak ada data transaksi",
                zeroRecords: "Tidak ditemukan data yang sesuai",
                paginate: {
                    next: "<i class='mdi mdi-chevron-right'></i>",
                    previous: "<i class='mdi mdi-chevron-left'></i>"
                }
            }
        });

        // Refresh table button
        $('#btn-refresh-table').on('click', function() {
            paymentTable.ajax.reload();
        });
    });
</script>
@endpush
