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
                    class="btn btn-icon btn-sm bg-danger-subtle">
                    <i class="mdi mdi-file fs-14 text-danger"></i>
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
                <h5 class="modal-title" id="detailModalLabel">Detail Transaksi Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Kode Transaksi</th>
                                <th>Tipe Transaksi</th>
                                <th>Item Code</th>
                                <th>Item Name</th>
                                <th>Qty In</th>
                                <th>Qty Out</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody id="detailTableBody"></tbody>
                        <tfoot id="detailTableFooter">
                            <tr class="table-active">
                                <th colspan="6" class="text-end">Total</th>
                                <th class="text-end">0</th>
                                <th class="text-end">0</th>
                                <th class="text-end">0</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
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
                    "data": 'itemName'
                },
                {
                    "data": 'warehouseName'
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
                    "targets": [0, 1]
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
        $(document).on('click', '.btn-detail', function() {
            const itemCode = $(this).data('item-code');
            const warehouseCode = $(this).data('warehouse-code');

            $.ajax({
                url: "{{ route('inventory.stock.detail') }}",
                type: "GET",
                data: {
                    itemCode: itemCode,
                    warehouseCode: warehouseCode
                },
                success: function(response) {
                    if (response.success) {
                        let html = '';
                        let totalIn = 0;
                        let totalOut = 0;
                        const fmt = new Intl.NumberFormat('id-ID');

                        if (response.data.length > 0) {
                            response.data.forEach((item, index) => {
                                const inQty = Number(item.qtyIn) || 0;
                                const outQty = Number(item.qtyOut) || 0;
                                totalIn += inQty;
                                totalOut += outQty;

                                // badge class by type
                                let badgeClass = 'bg-secondary';
                                if (item.transactionType === 'Pembelian') {
                                    badgeClass = 'bg-success';
                                } else if (item.transactionType === 'Pemeliharaan') {
                                    badgeClass = 'bg-warning text-dark';
                                }

                                const typeHtml = `<span class="badge badge-status ${badgeClass}">${item.transactionType}</span>`;

                                html += `<tr>
                                    <td>${index + 1}</td>
                                    <td>${item.date}</td>
                                    <td class="text-nowrap">${item.transactionCode}</td>
                                    <td>${typeHtml}</td>
                                    <td class="text-nowrap">${item.itemCode}</td>
                                    <td class="text-start">${item.itemName}</td>
                                    <td class="text-end">${fmt.format(inQty)}</td>
                                    <td class="text-end">${fmt.format(outQty)}</td>
                                    <td class="created-at text-end">${item.createdAt}</td>
                                </tr>`;
                            });
                        } else {
                            html = '<tr><td colspan="9" class="text-center">Tidak ada data transaksi</td></tr>';
                        }

                        $('#detailTableBody').html(html);

                        // render footer totals
                        const net = totalIn - totalOut;
                        const footerHtml = `<tr class="table-active">
                            <th colspan="6" class="text-end">Total</th>
                            <th class="text-end">${fmt.format(totalIn)}</th>
                            <th class="text-end">${fmt.format(totalOut)}</th>
                            <th class="text-end">${fmt.format(net)}</th>
                        </tr>`;
                        $('#detailTableFooter').html(footerHtml);

                        $('#detailModal').modal('show');
                    } else {
                        swal("Error", "Gagal memuat data transaksi", "error");
                    }
                },
                error: function() {
                    swal("Error", "Terjadi kesalahan, silakan coba lagi", "error");
                }
            });
        });
    });
</script>
@endpush