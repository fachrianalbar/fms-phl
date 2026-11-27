@php
    $routePrefix = $routePrefix ?? 'inventory.items.';
    $dtRoute = $dtRoute ?? 'dt.items';
    $itemType = $itemType ?? \App\Models\Inventory\Item::TYPE_PART;
    $entityLabel = $entityLabel ?? 'Barang';
@endphp

@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Inventori',
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
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/sweetalert2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom-select2.css') }}">
    @include('inventory.partials.table-style')
    <style>
        html[data-bs-theme="dark"] #purchaseHistoryModal .modal-content,
        html[data-bs-theme="dark"] #loadingModal .modal-content {
            background-color: var(--bs-card-bg);
            color: var(--bs-body-color);
            border-color: var(--bs-border-color);
        }
        html[data-bs-theme="dark"] #purchaseHistoryModal .modal-header,
        html[data-bs-theme="dark"] #purchaseHistoryModal .modal-footer {
            border-color: var(--bs-border-color);
        }
    </style>
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
            <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h4 class="mb-1 fw-bold d-flex align-items-center gap-2">
                        <i class="mdi {{ $itemType === \App\Models\Inventory\Item::TYPE_JASA ? 'mdi-wrench-cog-outline' : 'mdi-package-variant-closed' }} text-primary fs-20"></i>
                        Data {{ $entityLabel }}
                    </h4>
                    <small class="text-muted">Kelola master {{ strtolower($entityLabel) }}, satuan, kategori, dan sinkronisasi harga pembelian</small>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <a href="{{ route($routePrefix . 'export-excel') }}" target="_blank" id="export-excel"
                        class="btn btn-icon btn-sm bg-success-subtle" data-bs-toggle="tooltip" title="Export Excel">
                        <i class="mdi mdi-file-excel fs-14 text-success"></i>
                    </a>
                    <a href="{{ route($routePrefix . 'export-pdf') }}" target="_blank" id="export-pdf"
                        class="btn btn-icon btn-sm bg-danger-subtle" data-bs-toggle="tooltip" title="Export PDF">
                        <i class="mdi mdi-file-pdf-box fs-14 text-danger"></i>
                    </a>
                    <button type="button" class="btn btn-success btn-sm" onclick="syncLatestPurchasePrice()">
                        <i class="mdi mdi-sync me-1"></i> Sinkron Harga
                    </button>

                    <a href="{{ route($routePrefix . 'create') }}"
                        class="btn btn-primary btn-sm">{{ __('general.add_data') }}</a>
                </div>
            </div>

            <div class="card-body p-4">
                @include('partials.alert')

                <div class="filter-card">
                    <button type="button" class="filter-card-header" data-bs-toggle="collapse"
                        data-bs-target="#itemsFilterCollapse" aria-expanded="false" aria-controls="itemsFilterCollapse">
                        <span class="filter-card-heading">
                            <i class="mdi mdi-filter-variant"></i>
                            <strong>Filter Data</strong>
                            <small>Gunakan filter {{ strtolower($entityLabel) }}, kategori, atau satuan</small>
                        </span>
                        <i class="mdi mdi-chevron-down filter-card-chevron"></i>
                    </button>

                    <div class="collapse filter-collapse" id="itemsFilterCollapse">
                        <div class="filter-collapse-body">
                            <div id="filterForm">
                                <div class="row g-3">
                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="itemCode">{{ $entityLabel }}</label>
                                        <select class="form-select select2-filter" name="itemCode" id="itemCode">
                                            <option value="">Semua {{ $entityLabel }}</option>
                                            @foreach ($items as $item)
                                                <option value="{{ $item->code }}">
                                                    {{ $item->code . ' - ' . $item->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="categoryCode">Kategori</label>
                                        <select class="form-select select2-filter" name="categoryCode" id="categoryCode">
                                            <option value="">Semua Kategori</option>
                                            @foreach ($categories ?? [] as $cat)
                                                <option value="{{ $cat->code }}">{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="unitCode">Satuan</label>
                                        <select class="form-select select2-filter" name="unitCode" id="unitCode">
                                            <option value="">Semua Satuan</option>
                                            @foreach ($units ?? [] as $u)
                                                <option value="{{ $u->code }}">{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xl-3 col-md-6 d-flex align-items-end justify-content-md-end gap-2">
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
                    <table class="table align-middle w-100 nowrap mb-0" id="dt">
                        <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th class="text-center">No</th>
                                <th>Kode {{ $entityLabel }}</th>
                                <th>Nama {{ $entityLabel }}</th>
                                <th>Kategori</th>
                                <th class="text-center">Satuan</th>
                                <th class="text-end">Harga</th>
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

    <form id="sync-price-form" method="post" action="{{ route($routePrefix . 'sync-latest-purchase-price') }}">
        @csrf
    </form>

    <!-- Loading Preloader Modal -->
    <div class="modal fade" id="loadingModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="loadingModalLabel" aria-hidden="true">
        <div class="modal-dialog mt-4">
            <div class="modal-content border-0">
                <div class="modal-body text-center p-5">
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h5 class="mt-3 mb-2">Sinkronisasi Harga {{ $entityLabel }}</h5>
                    <p class="text-muted mb-0">Memproses harga terbaru dari history pembelian...</p>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="purchaseHistoryModal" tabindex="-1" aria-labelledby="purchaseHistoryModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="purchaseHistoryModalLabel">History Pembelian {{ $entityLabel }}</h5>
                        <p class="text-muted mb-0 fs-6"><strong id="historyItemInfo">-</strong></p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle mb-0 w-100"
                            id="purchaseHistoryTable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal Pembelian</th>
                                    <th>No Pembelian</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-end">Harga Satuan</th>
                                    <th class="text-end">Total Pembelian</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
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
    <script src="{{ asset('assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-keytable-bs5/js/keyTable.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-select/js/dataTables.select.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-select-bs5/js/select.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2-custom.js') }}"></script>

    <script>
        let loadingModal = null;
        let historyModal = null;
        let purchaseHistoryTable = null;

        $(document).ready(function () {
            $('.select2-filter').select2({
                placeholder: 'Semua pilihan',
                allowClear: true,
                width: '100%'
            });

            function getFilters() {
                return {
                    itemCode: $('#itemCode').val() || '',
                    categoryCode: $('#categoryCode').val() || '',
                    unitCode: $('#unitCode').val() || ''
                };
            }

            function syncExportUrls() {
                const params = new URLSearchParams();
                Object.entries(getFilters()).forEach(function (entry) {
                    if (entry[1]) params.set(entry[0], entry[1]);
                });
                const query = params.toString() ? '?' + params.toString() : '';
                $('#export-excel').attr('href', "{{ route($routePrefix . 'export-excel') }}" + query);
                $('#export-pdf').attr('href', "{{ route($routePrefix . 'export-pdf') }}" + query);
            }

            loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'), {
                backdrop: 'static',
                keyboard: false
            });

            historyModal = new bootstrap.Modal(document.getElementById('purchaseHistoryModal'));

            const table = $('#dt').DataTable({
                "processing": true,
                "serverSide": true,
                "destroy": true,
                deferRender: false,
                "ajax": {
                    "url": "{{ route($dtRoute) }}",
                    "data": function (d) {
                        Object.assign(d, getFilters());
                    }
                },
                "columns": [
                    {
                        "data": 'action',
                        "className": 'text-center align-middle'
                    },
                    {
                        "data": 'DT_RowIndex',
                        "className": 'text-center align-middle'
                    },
                    {
                        "data": 'code',
                        "className": 'align-middle font-monospace fw-semibold'
                    },
                    {
                        "data": 'name',
                        "className": 'align-middle'
                    },
                    {
                        "data": 'categoryName',
                        "className": 'align-middle'
                    },
                    {
                        "data": 'unit.name',
                        "className": 'align-middle text-center'
                    },
                    {
                        "data": 'price',
                        "className": 'text-end align-middle font-monospace fw-semibold',
                        "render": function (data, type) {
                            const nominal = Number(data || 0);
                            if (type === 'display' || type === 'filter') {
                                return 'Rp ' + new Intl.NumberFormat('id-ID').format(nominal);
                            }
                            return nominal;
                        }
                    }
                ],
                "columnDefs": [
                    {
                        "searchable": false,
                        "targets": [0, 1]
                    },
                    {
                        "orderable": false,
                        "targets": [0, 1]
                    }
                ],
                "order": [
                    [2, 'asc']
                ]
            });

            function reloadWithFilters() {
                syncExportUrls();
                table.ajax.reload(null, true);
            }

            $('#btnFilter').on('click', reloadWithFilters);
            $('#btnResetFilter').on('click', function () {
                $('.select2-filter').val('').trigger('change');
                reloadWithFilters();
            });

            syncExportUrls();

            $(document).on('click', '.btn-purchase-history', function () {
                const url = $(this).data('url');
                const itemCode = $(this).data('item-code');
                const itemName = $(this).data('item-name');

                $('#historyItemInfo').text(itemCode + ' - ' + itemName);
                historyModal.show();

                if (purchaseHistoryTable) {
                    purchaseHistoryTable.destroy();
                }

                purchaseHistoryTable = $('#purchaseHistoryTable').DataTable({
                    processing: true,
                    serverSide: true,
                    destroy: true,
                    searching: true,
                    ordering: false,
                    pageLength: 10,
                    ajax: {
                        url: url,
                        type: 'GET',
                    },
                    columns: [
                        {
                            data: 'DT_RowIndex',
                            searchable: false,
                            orderable: false,
                        },
                        {
                            data: 'date'
                        },
                        {
                            data: 'purchaseCode'
                        },
                        {
                            data: 'qty',
                            className: 'text-end'
                        },
                        {
                            data: 'price',
                            className: 'text-end',
                            render: function (data) {
                                return 'Rp ' + data;
                            }
                        },
                        {
                            data: 'total',
                            className: 'text-end',
                            render: function (data) {
                                return 'Rp ' + data;
                            }
                        }
                    ],
                    language: {
                        emptyTable: 'Belum ada history pembelian',
                    },
                });
            });
        });

        function deleteData(urlOrUuid) {
            const raw = String(urlOrUuid || '');
            const actionUrl = (raw.startsWith('http://') || raw.startsWith('https://') || raw.startsWith('/'))
                ? raw
                : "{{ url($itemType === \App\Models\Inventory\Item::TYPE_JASA ? 'inventory/jasa' : 'inventory/items') }}/" + raw;
            $('#delete-form').attr('action', actionUrl);

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

        function syncLatestPurchasePrice() {
            swal({
                title: "Sinkronisasi harga terbaru?",
                text: "Harga master {{ strtolower($entityLabel) }} akan diperbarui dari history pembelian terbaru.",
                icon: "warning",
                buttons: true,
                dangerMode: false,
            }).then((willSync) => {
                if (willSync) {
                    loadingModal.show();
                    setTimeout(() => {
                        $('#sync-price-form').submit();
                    }, 300);
                }
            });
        }
    </script>
@endpush
