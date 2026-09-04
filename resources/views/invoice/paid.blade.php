@extends('layouts.main', [
'title' => $title,
'pageTitle' => $title,
'firstSegment' => 'Faktur',
'secondSegment' => 'Paid Invoice',
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
            <div class="bg-success text-white rounded-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px; background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;">
                <i class="mdi mdi-check-decagram fs-24"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    {{ $title }}
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill fs-12 px-2 py-1">
                        {{ number_format($stats['totalCount'] ?? 0) }} Lunas
                    </span>
                </h4>
                <p class="text-muted mb-0 fs-12">Arsip dan riwayat seluruh faktur penagihan pelanggan yang telah lunas 100% (Full Payment).</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm" id="btn-refresh-table" title="Muat Ulang Data Tabel">
                <i class="mdi mdi-refresh me-1"></i> Refresh
            </button>
            <a href="{{ route('invoice.unpaid') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm fw-semibold">
                <i class="mdi mdi-receipt-text-clock me-1"></i> Lihat Faktur Belum Lunas
            </a>
        </div>
    </div>

    <!-- 2 KPI Summary Cards -->
    <div class="row g-3 mb-4">
        <!-- Card 1: Total Faktur Lunas -->
        <div class="col-12 col-sm-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Total Faktur Lunas</div>
                        <div class="stat-value text-success">{{ number_format($stats['totalCount'] ?? 0) }} <span class="fs-13 text-muted fw-normal">Faktur</span></div>
                    </div>
                    <div class="stat-icon-wrapper bg-success-subtle text-success">
                        <i class="mdi mdi-check-all"></i>
                    </div>
                </div>
                <div class="stat-desc text-success mt-2">
                    <i class="mdi mdi-shield-check-outline me-1"></i>Seluruh tagihan telah diselesaikan
                </div>
            </div>
        </div>

        <!-- Card 2: Total Realisasi Pendapatan -->
        <div class="col-12 col-sm-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Total Realisasi Pembayaran Lunas</div>
                        <div class="stat-value text-dark">Rp {{ number_format($stats['totalBilling'] ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <div class="stat-icon-wrapper bg-primary-subtle text-primary">
                        <i class="mdi mdi-cash-check"></i>
                    </div>
                </div>
                <div class="stat-desc mt-2 text-muted">
                    <i class="mdi mdi-bank-check me-1"></i>Total penerimaan kas faktur lunas
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="table-container-card mb-4">
        <div class="table-top-bar d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-bold fs-12">
                    <i class="mdi mdi-check-circle me-1"></i> Arsip Faktur Lunas
                </span>
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
                            <th class="text-center" style="width: 80px;">Aksi</th>
                            <th class="text-center" style="width: 45px;">No</th>
                            <th>No. Faktur</th>
                            <th>Pelanggan</th>
                            <th>Tgl Faktur</th>
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
                "url": "{{ route('dt.invoice.paid') }}",
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

        // Refresh table button
        $('#btn-refresh-table').on('click', function() {
            var btn = $(this);
            btn.find('i').addClass('mdi-spin');
            table.ajax.reload(function() {
                btn.find('i').removeClass('mdi-spin');
            }, false);
        });
    });

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
