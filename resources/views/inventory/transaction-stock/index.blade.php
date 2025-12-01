@extends('layouts.main', [
'title' => $title,
'pageTitle' => $title,
'firstSegment' => 'Inventory',
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
<link rel="stylesheet" type="text/css" href="../assets/css/vendors/sweetalert2.css">
@endpush

@section('content')
<div class="col-sm-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>{{ $title }} - Per Warehouse</h4>

            <div class="d-flex align-items-center gap-3">
                <a href="{{ route($view . 'pdf-transaction-stock') }}" target="_blank" id="print-pdf"
                    class="btn btn-danger btn-sm">
                    <i class="mdi mdi-file-pdf-box me-1"></i> Cetak PDF
                </a>
            </div>
        </div>

        <div class="card-body">
            @include('partials.alert')
            <div class="table-responsive custom-scrollbar">
                <table class="table table-striped w-100 nowrap" id="dt">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>No</th>
                            <th>Kode Warehouse</th>
                            <th>Nama Warehouse</th>
                            <th>Total Masuk</th>
                            <th>Total Keluar</th>
                            <th>Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Warehouse -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel">Detail Transaksi Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="detailWarehouseCode">

                <!-- Nav tabs -->
                <ul class="nav nav-tabs" id="detailTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="summary-tab" data-bs-toggle="tab" data-bs-target="#summary" type="button" role="tab">
                            Summary Per Item
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="detail-tab" data-bs-toggle="tab" data-bs-target="#detail" type="button" role="tab">
                            Detail Transaksi
                        </button>
                    </li>
                </ul>

                <!-- Tab content -->
                <div class="tab-content mt-3" id="detailTabContent">
                    <!-- Summary Tab -->
                    <div class="tab-pane fade show active" id="summary" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="summaryTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>Kode Item</th>
                                        <th>Nama Item</th>
                                        <th>Total Masuk</th>
                                        <th>Total Keluar</th>
                                        <th>Stock</th>
                                    </tr>
                                </thead>
                                <tbody id="summaryBody">
                                </tbody>
                                <tfoot class="table-secondary">
                                    <tr>
                                        <th colspan="3" class="text-end">Total:</th>
                                        <th id="summaryTotalIn">0</th>
                                        <th id="summaryTotalOut">0</th>
                                        <th id="summaryTotalStock">0</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Detail Tab -->
                    <div class="tab-pane fade" id="detail" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="detailTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal</th>
                                        <th>Kode Item</th>
                                        <th>Nama Item</th>
                                        <th>No Transaksi</th>
                                        <th>Jenis</th>
                                        <th>Masuk</th>
                                        <th>Keluar</th>
                                    </tr>
                                </thead>
                                <tbody id="detailBody">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="printWarehousePdf">
                    <i class="mdi mdi-file-pdf-box"></i> Cetak PDF Warehouse Ini
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
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
<script src="../assets/js/sweet-alert/sweetalert.min.js"></script>

<script>
    $(document).ready(function() {
        // Main DataTable - Warehouse List
        const table = $('#dt').DataTable({
            "processing": true,
            "serverSide": true,
            "destroy": true,
            "ajax": {
                "url": "{{ route('dt.transaction-stock') }}"
            },
            "columns": [{
                    "data": 'action',
                    "orderable": false,
                    "searchable": false
                },
                {
                    "data": 'DT_RowIndex',
                    "orderable": false,
                    "searchable": false
                },
                {
                    "data": 'code'
                },
                {
                    "data": 'name'
                },
                {
                    "data": 'totalIn'
                },
                {
                    "data": 'totalOut'
                },
                {
                    "data": 'totalStock'
                }
            ],
            "order": [
                [2, 'asc']
            ]
        });

        // Handle detail button click
        $(document).on('click', '.btn-detail', function() {
            const warehouseCode = $(this).data('warehouse-code');
            const warehouseName = $(this).data('warehouse-name');

            $('#detailModalLabel').text('Detail Transaksi Stock - ' + warehouseName);
            $('#detailWarehouseCode').val(warehouseCode);

            // Load detail data
            $.ajax({
                url: "{{ route('inventory.transaction-stock.detail') }}",
                type: "GET",
                data: {
                    warehouseCode: warehouseCode
                },
                success: function(response) {
                    if (response.success) {
                        // Populate summary table
                        let summaryHtml = '';
                        let totalIn = 0,
                            totalOut = 0,
                            totalStock = 0;

                        response.summary.forEach(function(item, index) {
                            summaryHtml += `
                                    <tr>
                                        <td>${index + 1}</td>
                                        <td>${item.itemCode}</td>
                                        <td>${item.itemName}</td>
                                        <td>${item.totalIn}</td>
                                        <td>${item.totalOut}</td>
                                        <td>${item.stock}</td>
                                    </tr>
                                `;
                            totalIn += item.totalIn;
                            totalOut += item.totalOut;
                            totalStock += item.stock;
                        });

                        $('#summaryBody').html(summaryHtml);
                        $('#summaryTotalIn').text(totalIn);
                        $('#summaryTotalOut').text(totalOut);
                        $('#summaryTotalStock').text(totalStock);

                        // Populate detail table
                        let detailHtml = '';
                        response.details.forEach(function(item, index) {
                            let badgeClass = 'bg-secondary';
                            if (item.transactionType === 'Pembelian') {
                                badgeClass = 'bg-success';
                            } else if (item.transactionType === 'Pemeliharaan') {
                                badgeClass = 'bg-warning';
                            } else if (item.transactionType === 'Stock Awal') {
                                badgeClass = 'bg-info';
                            }

                            detailHtml += `
                                    <tr>
                                        <td>${index + 1}</td>
                                        <td>${item.date}</td>
                                        <td>${item.itemCode}</td>
                                        <td>${item.itemName}</td>
                                        <td>${item.transactionCode}</td>
                                        <td><span class="badge ${badgeClass}">${item.transactionType}</span></td>
                                        <td>${item.qtyIn > 0 ? item.qtyIn : '-'}</td>
                                        <td>${item.qtyOut > 0 ? item.qtyOut : '-'}</td>
                                    </tr>
                                `;
                        });

                        $('#detailBody').html(detailHtml);

                        // Show modal
                        $('#detailModal').modal('show');
                    } else {
                        swal("Error", "Gagal memuat data", "error");
                    }
                },
                error: function(xhr) {
                    swal("Error", "Terjadi kesalahan, silakan coba lagi", "error");
                }
            });
        });

        // Print PDF for specific warehouse
        $('#printWarehousePdf').click(function() {
            const warehouseCode = $('#detailWarehouseCode').val();
            const url = "{{ route($view . 'pdf-transaction-stock') }}?warehouseCode=" + warehouseCode;
            window.open(url);
        });

        // Print all PDF
        $('#print-pdf').click(function(e) {
            e.preventDefault();
            const url = "{{ route($view . 'pdf-transaction-stock') }}";
            window.open(url);
        });
    });
</script>
@endpush