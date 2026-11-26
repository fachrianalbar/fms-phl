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
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom-select2.css') }}">
    @include('inventory.partials.table-style')
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h4 class="mb-0">{{ $title }} - Per Warehouse</h4>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <a href="{{ route('inventory.transaction-stock.export-excel') }}" target="_blank" id="export-excel"
                        class="btn btn-icon btn-sm bg-success-subtle" data-bs-toggle="tooltip" title="Export Excel">
                        <i class="mdi mdi-file-excel fs-14 text-success"></i>
                    </a>
                    <a href="{{ route($view . 'pdf-transaction-stock') }}" target="_blank" id="export-pdf"
                        class="btn btn-icon btn-sm bg-danger-subtle" data-bs-toggle="tooltip" title="Export PDF">
                        <i class="mdi mdi-file-pdf-box fs-14 text-danger"></i>
                    </a>
                </div>
            </div>

            <div class="card-body">
                @include('partials.alert')

                <div class="filter-card">
                    <button type="button" class="filter-card-header" data-bs-toggle="collapse"
                        data-bs-target="#transactionStockFilterCollapse" aria-expanded="false"
                        aria-controls="transactionStockFilterCollapse">
                        <span class="filter-card-heading">
                            <i class="mdi mdi-filter-variant"></i>
                            <strong>Filter Data</strong>
                            <small>Gunakan filter gudang, tipe transaksi, atau rentang tanggal</small>
                        </span>
                        <i class="mdi mdi-chevron-down filter-card-chevron"></i>
                    </button>

                    <div class="collapse filter-collapse" id="transactionStockFilterCollapse">
                        <div class="filter-collapse-body">
                            <div id="filterForm">
                                <div class="row g-3">
                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="warehouseCode">Gudang</label>
                                        <select class="form-select select2-filter" name="warehouseCode" id="warehouseCode">
                                            <option value="">Semua Gudang</option>
                                            @foreach ($warehouses ?? [] as $wh)
                                                <option value="{{ $wh->code }}">{{ $wh->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="transactionType">Tipe Transaksi</label>
                                        <select class="form-select select2-filter" name="transactionType"
                                            id="transactionType">
                                            <option value="">Semua Tipe</option>
                                            <option value="INITIAL">Stock Awal</option>
                                            <option value="IN">Masuk (Pembelian)</option>
                                            <option value="OUT">Keluar (Pemeliharaan)</option>
                                        </select>
                                    </div>
                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="startDate">Dari Tanggal</label>
                                        <input class="form-control filter-control" name="startDate" id="startDate"
                                            type="text" placeholder="Pilih tanggal mulai">
                                    </div>
                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="endDate">Sampai Tanggal</label>
                                        <input class="form-control filter-control" name="endDate" id="endDate" type="text"
                                            placeholder="Pilih tanggal akhir">
                                    </div>
                                    <div class="col-12 d-flex align-items-end justify-content-md-end gap-2">
                                        <button class="btn btn-filter-primary flex-grow-1 flex-md-grow-0" type="button"
                                            id="btnFilter">
                                            <i class="mdi mdi-filter-outline"></i> Terapkan Filter
                                        </button>
                                        <button class="btn btn-filter-reset" type="button" id="btnResetFilter"
                                            data-bs-toggle="tooltip" title="Reset Filter">
                                            <i class="mdi mdi-refresh"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive custom-scrollbar">
                    <table class="table align-middle w-100 nowrap" id="dt">
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
                            <button class="nav-link active" id="summary-tab" data-bs-toggle="tab" data-bs-target="#summary"
                                type="button" role="tab">
                                Summary Per Item
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="detail-tab" data-bs-toggle="tab" data-bs-target="#detail"
                                type="button" role="tab">
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
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>

    <script>
        function formatQty(value) {
            return (parseFloat(value) || 0).toLocaleString('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 1
            });
        }

        $(document).ready(function () {
            let filterStartPicker;
            let filterEndPicker;

            $('.select2-filter').select2({
                placeholder: 'Semua pilihan',
                allowClear: true,
                width: '100%'
            });

            filterStartPicker = flatpickr('#startDate', {
                dateFormat: 'Y-m-d',
                allowInput: true,
                onChange: function (selectedDates, dateStr) {
                    if (filterEndPicker) filterEndPicker.set('minDate', dateStr || null);
                }
            });

            filterEndPicker = flatpickr('#endDate', {
                dateFormat: 'Y-m-d',
                allowInput: true,
                onChange: function (selectedDates, dateStr) {
                    if (filterStartPicker) filterStartPicker.set('maxDate', dateStr || null);
                }
            });

            function getFilters() {
                return {
                    warehouseCode: $('#warehouseCode').val() || '',
                    transactionType: $('#transactionType').val() || '',
                    startDate: $('#startDate').val() || '',
                    endDate: $('#endDate').val() || ''
                };
            }

            function syncExportUrls() {
                const params = new URLSearchParams();
                Object.entries(getFilters()).forEach(function (entry) {
                    if (entry[1]) params.set(entry[0], entry[1]);
                });
                const query = params.toString() ? '?' + params.toString() : '';
                $('#export-excel').attr('href', "{{ route('inventory.transaction-stock.export-excel') }}" + query);
                $('#export-pdf').attr('href', "{{ route($view . 'pdf-transaction-stock') }}" + query);
            }

            // Main DataTable - Warehouse List
            const table = $('#dt').DataTable({
                "processing": true,
                "serverSide": true,
                "destroy": true,
                "ajax": {
                    "url": "{{ route('dt.transaction-stock') }}",
                    "data": function (d) {
                        Object.assign(d, getFilters());
                    }
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
                    "data": 'totalIn',
                    "render": function (data, type) {
                        return type === 'display' ? formatQty(data) : data;
                    }
                },
                {
                    "data": 'totalOut',
                    "render": function (data, type) {
                        return type === 'display' ? formatQty(data) : data;
                    }
                },
                {
                    "data": 'totalStock',
                    "render": function (data, type) {
                        return type === 'display' ? formatQty(data) : data;
                    }
                }
                ],
                "order": [
                    [2, 'asc']
                ]
            });

            // Handle detail button click
            $(document).on('click', '.btn-detail', function () {
                const warehouseCode = $(this).data('warehouse-code');
                const warehouseName = $(this).data('warehouse-name');

                $('#detailModalLabel').text('Detail Transaksi Stock - ' + warehouseName);
                $('#detailWarehouseCode').val(warehouseCode);

                // Load detail data
                const activeFilters = getFilters();
                $.ajax({
                    url: "{{ route('inventory.transaction-stock.detail') }}",
                    type: "GET",
                    data: {
                        warehouseCode: warehouseCode,
                        transactionType: activeFilters.transactionType,
                        startDate: activeFilters.startDate,
                        endDate: activeFilters.endDate
                    },
                    success: function (response) {
                        if (response.success) {
                            // Populate summary table
                            let summaryHtml = '';
                            let totalIn = 0,
                                totalOut = 0,
                                totalStock = 0;

                            response.summary.forEach(function (item, index) {
                                summaryHtml += `
                                        <tr>
                                            <td>${index + 1}</td>
                                            <td>${item.itemCode}</td>
                                            <td>${item.itemName}</td>
                                            <td>${formatQty(item.totalIn)}</td>
                                            <td>${formatQty(item.totalOut)}</td>
                                            <td>${formatQty(item.stock)}</td>
                                        </tr>
                                    `;
                                totalIn += item.totalIn;
                                totalOut += item.totalOut;
                                totalStock += item.stock;
                            });

                            $('#summaryBody').html(summaryHtml);
                            $('#summaryTotalIn').text(formatQty(totalIn));
                            $('#summaryTotalOut').text(formatQty(totalOut));
                            $('#summaryTotalStock').text(formatQty(totalStock));

                            // Populate detail table
                            let detailHtml = '';
                            response.details.forEach(function (item, index) {
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
                                            <td>${item.qtyIn > 0 ? formatQty(item.qtyIn) : '-'}</td>
                                            <td>${item.qtyOut > 0 ? formatQty(item.qtyOut) : '-'}</td>
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
                    error: function (xhr) {
                        swal("Error", "Terjadi kesalahan, silakan coba lagi", "error");
                    }
                });
            });

            function reloadWithFilters() {
                syncExportUrls();
                table.ajax.reload(null, true);
            }

            $('#btnFilter').on('click', reloadWithFilters);
            $('#btnResetFilter').on('click', function () {
                $('#filterForm').find('input').val('');
                $('.select2-filter').val('').trigger('change');
                if (filterStartPicker) {
                    filterStartPicker.clear();
                    filterStartPicker.set('maxDate', null);
                }
                if (filterEndPicker) {
                    filterEndPicker.clear();
                    filterEndPicker.set('minDate', null);
                }
                reloadWithFilters();
            });

            // Print PDF for specific warehouse
            $('#printWarehousePdf').click(function () {
                const warehouseCode = $('#detailWarehouseCode').val();
                const params = new URLSearchParams();
                const f = getFilters();
                params.set('warehouseCode', warehouseCode);
                if (f.transactionType) params.set('transactionType', f.transactionType);
                if (f.startDate) params.set('startDate', f.startDate);
                if (f.endDate) params.set('endDate', f.endDate);
                const url = "{{ route($view . 'pdf-transaction-stock') }}?" + params.toString();
                window.open(url);
            });

            syncExportUrls();
        });
    </script>
@endpush