@extends('layouts.main', [
'title' => $title,
'pageTitle' => $title,
'firstSegment' => 'Master',
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
<style>
    /* Small adjustments for detail modal */
    #detailModal .table th,
    #detailModal .table td {
        font-size: 13px;
    }

    #detailModal .created-at {
        font-size: 11px;
        color: #6c757d;
    }

    #detailModal .badge-status {
        padding: .35em .5em;
        font-size: .75em;
    }

    #detailTableFooter th,
    #detailTableFooter td {
        font-weight: 600;
    }

    #detailModal .text-end,
    #detailModal .text-right {
        text-align: right;
    }
</style>
@endpush

@section('content')
<div class="col-sm-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>{{ $title }} Data</h4>

            <div class="d-flex align-items-center gap-3">
                <a href="{{ route($view . 'pdf-stock') }}" target="_blank" id="print-pdf"
                    class="btn btn-sm btn-danger">
                    <i class="mdi mdi-file-pdf-box me-1"></i> Download PDF
                </a>

                {{-- <a href="{{ route($view . 'create') }}" class="btn btn-primary">{{ __('general.add_data') }}</a> --}}
            </div>
        </div>
        <div class="card-body">
            @include('partials.alert')
            <div class="table-responsive custom-scrollbar">
                <table class="table table-striped w-100 nowrap" id="dt">
                    <thead>
                        <tr>
                            <th>Aksi</th>
                            <th>No</th>
                            <th>Item Code</th>
                            <th>Item Name</th>
                            <th>Warehouse</th>
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

<form id="delete-form" method="post">
    @csrf
    @method('DELETE')
</form>

<!-- Modal Edit Stock Awal -->
<div class="modal fade" id="editStockModal" tabindex="-1" aria-labelledby="editStockModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editStockModalLabel">Edit Stock Awal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editStockForm">
                <div class="modal-body">
                    <input type="hidden" id="editItemCode" name="itemCode">
                    <input type="hidden" id="editWarehouseCode" name="warehouseCode">

                    <div class="mb-3">
                        <label for="editItemName" class="form-label">Nama Item</label>
                        <input type="text" class="form-control" id="editItemName" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="editWarehouseName" class="form-label">Warehouse</label>
                        <input type="text" class="form-control" id="editWarehouseName" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="editQty" class="form-label">Qty Stock Awal</label>
                        <input type="number" class="form-control" id="editQty" name="qty" required min="0"
                            step="1">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detail Transaksi -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="detailModalLabel">Detail Transaksi Stock</h5>
                    <p class="text-muted mb-0 fs-6" id="headerItemInfo">
                        <strong><span id="headerItemCode"></span> - <span id="headerItemName"></span></strong>
                    </p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Stock Summary Card -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6 class="card-title text-muted mb-2">Total Masuk</h6>
                                <h4 class="text-success" id="summaryTotalIn">0</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6 class="card-title text-muted mb-2">Total Keluar</h6>
                                <h4 class="text-danger" id="summaryTotalOut">0</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6 class="card-title text-muted mb-2">Stock Saat Ini</h6>
                                <h4 class="text-primary" id="summaryCurrentStock">0</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm table-striped table-hover align-middle" id="detailDataTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Kode Transaksi</th>
                                <th>Tipe Transaksi</th>
                                <th>Qty In</th>
                                <th>Qty Out</th>
                                <th>Current Stock</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" id="printDetailPdf" class="btn btn-sm btn-danger" target="_blank">
                    <i class="mdi mdi-file-pdf-box me-1"></i> Download PDF
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>

<!-- dataTables.bootstrap5 -->
<script src="{{ asset('assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>

<!-- dataTables.keyTable -->
<script src="{{ asset('assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-keytable-bs5/js/keyTable.bootstrap5.min.js') }}"></script>

<!-- dataTable.responsive -->
<script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>

<!-- dataTables.select -->
<script src="{{ asset('assets/libs/datatables.net-select/js/dataTables.select.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-select-bs5/js/select.bootstrap5.min.js') }}"></script>
<script src="../assets/js/sweet-alert/sweetalert.min.js"></script>

<script>
    $(document).ready(function() {
        const table = $('#dt').DataTable({
            "processing": true,
            "serverSide": true,
            "destroy": true,
            "ajax": {
                "url": "{{ route('dt.stock') }}"
            },
            "columns": [{
                    "data": 'action',
                    "orderable": false,
                    "searchable": false
                },
                {
                    "data": 'DT_RowIndex'
                },
                {
                    "data": 'itemCode'
                },
                {
                    "data": 'itemName',
                    "orderable": false
                },
                {
                    "data": 'warehouseName',
                    "orderable": false
                },
                {
                    "data": 'stock'
                }
            ],
            "columnDefs": [{
                    "searchable": false,
                    "targets": [0, 1, 5]
                },
                {
                    "orderable": false,
                    "targets": [0, 1, 3, 4]
                }
            ],
            "order": [
                [2, 'asc']
            ]
        })

        $('#print-pdf').click(function(e) {
            const printPdf = "{{ route($view . 'pdf-stock') }}";
            window.open(printPdf);
        });

        // Handle modal edit stock awal
        $(document).on('click', '.btn-edit-stock-awal', function() {
            const itemCode = $(this).data('item-code');
            const itemName = $(this).data('item-name');
            const warehouseCode = $(this).data('warehouse-code');
            const warehouseName = $(this).data('warehouse-name');

            $('#editItemCode').val(itemCode);
            $('#editItemName').val(itemName);
            $('#editWarehouseCode').val(warehouseCode);
            $('#editWarehouseName').val(warehouseName);
            $('#editQty').val('');

            // Load existing INITIAL (Stock Awal) if any
            $.ajax({
                url: "{{ route('inventory.stock.detail') }}",
                type: "GET",
                data: {
                    itemCode: itemCode,
                    warehouseCode: warehouseCode
                },
                success: function(response) {
                    if (response.success) {
                        // Find the INITIAL transaction (mapped to 'Stock Awal')
                        const initial = response.data.find(t => t.transactionType === 'Stock Awal');
                        if (initial) {
                            $('#editQty').val(initial.qtyIn);
                        } else {
                            $('#editQty').val('');
                        }
                    }

                    $('#editStockModal').modal('show');
                },
                error: function() {
                    // On error, still show modal with empty qty
                    $('#editQty').val('');
                    $('#editStockModal').modal('show');
                }
            });
        });

        // Handle form submit edit stock awal
        $('#editStockForm').on('submit', function(e) {
            e.preventDefault();

            const formData = {
                itemCode: $('#editItemCode').val(),
                warehouseCode: $('#editWarehouseCode').val(),
                qty: $('#editQty').val(),
                _token: '{{ csrf_token() }}'
            };

            $.ajax({
                url: "{{ route('inventory.stock.update-initial') }}",
                type: "POST",
                data: formData,
                success: function(response) {
                    if (response.success) {
                        swal("Berhasil", response.message, "success");
                        $('#editStockModal').modal('hide');
                        table.ajax.reload();
                    } else {
                        swal("Error", response.message, "error");
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    if (response && response.message) {
                        swal("Error", response.message, "error");
                    } else {
                        swal("Error", "Terjadi kesalahan, silakan coba lagi", "error");
                    }
                }
            });
        });

        // Handle modal detail transaksi
        let detailDataTable = null;

        $(document).on('click', '.btn-detail', function() {
            const itemCode = $(this).data('item-code');
            const warehouseCode = $(this).data('warehouse-code');
            const itemName = $(this).closest('tr').find('td:eq(3)').text(); // Get item name from table row

            // Set header info
            $('#headerItemCode').text(itemCode);
            $('#headerItemName').text(itemName);

            // Update PDF download link
            $('#printDetailPdf').attr('href', "{{ route('inventory.stock.pdf-stock-detail') }}?itemCode=" + itemCode + "&warehouseCode=" + warehouseCode);

            // Load summary data
            $.ajax({
                url: "{{ route('inventory.stock.detail-summary') }}",
                type: "GET",
                data: {
                    itemCode: itemCode,
                    warehouseCode: warehouseCode
                },
                success: function(response) {
                    if (response.success) {
                        const fmt = new Intl.NumberFormat('id-ID');
                        $('#summaryTotalIn').text(fmt.format(response.totalIn));
                        $('#summaryTotalOut').text(fmt.format(response.totalOut));
                        $('#summaryCurrentStock').text(fmt.format(response.currentStock));
                    }
                }
            });

            // Initialize or reinitialize DataTable
            if (detailDataTable) {
                detailDataTable.destroy();
            }

            detailDataTable = $('#detailDataTable').DataTable({
                "processing": true,
                "serverSide": true,
                "destroy": true,
                "ajax": {
                    "url": "{{ route('inventory.stock.detail-datatable') }}",
                    "type": "GET",
                    "data": {
                        "itemCode": itemCode,
                        "warehouseCode": warehouseCode
                    }
                },
                "pageLength": 10,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "columns": [
                    {
                        "data": "DT_RowIndex",
                        "orderable": false,
                        "searchable": false,
                        "className": "text-center"
                    },
                    {
                        "data": "date",
                        "orderable": true
                    },
                    {
                        "data": "transactionCode",
                        "orderable": true
                    },
                    {
                        "data": "transactionTypeHtml",
                        "orderable": false,
                        "searchable": true
                    },
                    {
                        "data": "qtyIn",
                        "orderable": true,
                        "className": "text-end"
                    },
                    {
                        "data": "qtyOut",
                        "orderable": true,
                        "className": "text-end"
                    },
                    {
                        "data": "currentStock",
                        "orderable": false,
                        "searchable": false,
                        "className": "text-end"
                    },
                    {
                        "data": "createdAt",
                        "orderable": true,
                        "className": "text-end"
                    }
                ],
                "columnDefs": [
                    {
                        "searchable": false,
                        "orderable": false,
                        "targets": [0]
                    }
                ],
                "order": [[1, 'desc']],
                "language": {
                    "search": "Cari:",
                    "lengthMenu": "Tampilkan _MENU_ data",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    "paginate": {
                        "first": "Pertama",
                        "last": "Terakhir",
                        "next": "Berikutnya",
                        "previous": "Sebelumnya"
                    },
                    "processing": "Memproses...",
                    "emptyTable": "Tidak ada data transaksi"
                }
            });

            $('#detailModal').modal('show');
        });
    });
</script>
@endpush
