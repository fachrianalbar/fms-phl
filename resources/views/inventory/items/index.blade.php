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
    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/select2.css') }}">

    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/custom-select2.css') }}">
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }} Data</h4>

                <div class="d-flex align-items-center gap-3">
                    <div class="accordion-item ">

                        <a href="#" class="btn btn-icon btn-sm bg-dark-subtle" data-bs-toggle="collapse"
                            data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            <i class="mdi mdi-magnify fs-14 text-dark"></i>
                        </a>

                    </div>

                    <button type="button" class="btn btn-success" onclick="syncLatestPurchasePrice()">
                        <i class="mdi mdi-sync me-1"></i> Sinkron Harga
                    </button>

                    <a href="{{ route('inventory.items.create') }}" class="btn btn-primary">{{ __('general.add_data') }}</a>
                </div>
            </div>

            <div class="card-header">
                <div class="accordion-collapse collapse" id="collapseTwo" aria-labelledby="headingTwo"
                    data-bs-parent="#simpleaccordion">
                    <div class="accordion-body col-md-12">
                        <form id="filterForm" class=" g-3">
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <label class="form-label" for="name">Item</label>
                                    <select class="js-example-basic-single" name="itemCode" id="itemCode">
                                        <option selected="" value="">{{ __('general.choose') }}...</option>
                                        @foreach ($items as $item)
                                            <option value="{{ $item->code }}">
                                                {{ $item->code . ' - ' . $item->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <button class="btn btn-primary mt-3" type="submit">Filter</button>
                        </form>
                    </div>
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
                                <th>Code</th>
                                <th>Name</th>
                                <th>Unit</th>
                                <th>Harga</th>
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

    <form id="sync-price-form" method="post" action="{{ route('inventory.items.sync-latest-purchase-price') }}">
        @csrf
    </form>

    <!-- Loading Preloader Modal -->
    <div class="modal fade" id="loadingModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="loadingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-body text-center p-5">
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h5 class="mt-3 mb-2">Sinkronisasi Harga</h5>
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
                        <h5 class="modal-title" id="purchaseHistoryModalLabel">History Pembelian Item</h5>
                        <p class="text-muted mb-0 fs-6"><strong id="historyItemInfo">-</strong></p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm table-striped align-middle mb-0 w-100"
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
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src=" {{ asset('assets/js/select2/select2-custom.js') }}"></script>

    <script>
        let loadingModal = null;
        let historyModal = null;
        let purchaseHistoryTable = null;

        $(document).ready(function() {
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
                    "url": "{{ route('dt.items') }}",
                    "data": function(d) {
                        d.itemCode = $('select[name="itemCode"]').val();
                    }
                },
                "columns": [{
                        "data": 'action'
                    }, {
                        "data": 'DT_RowIndex'
                    },
                    {
                        "data": 'code'
                    },
                    {
                        "data": 'name'
                    },
                    {
                        "data": 'unit.name'
                    },
                    {
                        "data": 'price',
                        "render": function(data, type) {
                            const nominal = Number(data || 0);
                            if (type === 'display' || type === 'filter') {
                                return 'Rp ' + new Intl.NumberFormat('id-ID').format(nominal);
                            }
                            return nominal;
                        }
                    }
                ],
                "columnDefs": [{
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
            })

            // Event untuk form filter
            $('#filterForm').on('submit', function(e) {
                e.preventDefault();

                table.ajax.reload(); // Reload DataTable dengan filter baru
            });

            $(document).on('click', '.btn-purchase-history', function() {
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
                    columns: [{
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
                            render: function(data) {
                                return 'Rp ' + data;
                            }
                        },
                        {
                            data: 'total',
                            className: 'text-end',
                            render: function(data) {
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

        function deleteData(uuid) {
            var url = '/inventory/items/' + uuid;
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

        function syncLatestPurchasePrice() {
            swal({
                title: "Sinkronisasi harga terbaru?",
                text: "Harga master item akan diperbarui dari history pembelian terbaru.",
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
