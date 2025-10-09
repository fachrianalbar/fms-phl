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

                    <a href="{{ route($view . 'pdf-stock') }}" target="_blank" id="print-pdf"
                        class="btn btn-icon btn-sm bg-danger-subtle">
                        <i class="mdi mdi-file fs-14 text-danger"></i>
                    </a>

                    {{-- <a href="{{ route($view . 'create') }}" class="btn btn-primary">{{ __('general.add_data') }}</a> --}}
                </div>
            </div>

            <div class="card-header">
                <div class="accordion-collapse collapse" id="collapseTwo" aria-labelledby="headingTwo"
                    data-bs-parent="#simpleaccordion">
                    <div class="accordion-body col-md-12">
                        <form id="filterForm" class=" g-3">

                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label" for="name">Item</label>
                                    <select class="js-example-basic-single" name="itemCode" id="itemCode">
                                        <option selected="" value="">{{ __('general.choose') }}...</option>
                                        @foreach ($stock as $item)
                                            <option value="{{ $item->itemCode }}">
                                                {{ $item->itemCode . ' - ' . ($item->item->name ?? '') }}</option>
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
                                <th>Item Code</th>
                                <th>Item Name</th>
                                {{-- <th>Stock In </th>
                                <th>Stock Out</th> --}}
                                <th>Outstanding Stock</th>
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

                        <div class="mb-3">
                            <label for="editItemName" class="form-label">Nama Item</label>
                            <input type="text" class="form-control" id="editItemName" readonly>
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
        $(document).ready(function() {
            const table = $('#dt').DataTable({
                "processing": true,
                "serverSide": true,
                "destroy": true,
                "ajax": {
                    "url": "{{ route('dt.stock') }}",
                    "data": function(d) {
                        d.itemCode = $('select[name="itemCode"]').val();
                    }
                },
                "columns": [

                    {
                        "data": 'action',
                        "orderable": false,
                        "searchable": false
                    },
                    {
                        "data": 'DT_RowIndex'
                    },
                    {
                        "data": 'code'
                    },
                    {
                        "data": 'name'
                    },
                    // {
                    //     "data": "stockIn"
                    // },
                    // {
                    //     "data": 'stockOut'
                    // },
                    {
                        "data": 'total'
                    }

                ],
                "columnDefs": [{
                        "searchable": false,
                        "targets": [0, 4]
                    },
                    {
                        "orderable": false,
                        "targets": [0, 4]
                    }
                ],
                "order": [
                    [3, 'asc']
                ]
            })

            $('#print-pdf').click(function(e) {
                const queryParams = $("#filterForm").serialize(); // Serialize the form data

                const printPdf = "{{ route($view . 'pdf-stock') }}?" + queryParams;

                $('#print-pdf').attr('href', printPdf);

                window.open(printPdf); // Use the correct URL string
            });

            // Event untuk form filter
            $('#filterForm').on('submit', function(e) {
                e.preventDefault();

                table.ajax.reload(); // Reload DataTable dengan filter baru
            });

            // Handle modal edit stock
            $(document).on('click', '.btn-edit-stock', function() {
                const itemCode = $(this).data('item-code');
                const itemName = $(this).data('item-name');

                $('#editItemCode').val(itemCode);
                $('#editItemName').val(itemName);
                $('#editQty').val('');

                // Load warehouse data

                $('#editStockModal').modal('show');
            });



            // Handle form submit
            $('#editStockForm').on('submit', function(e) {
                e.preventDefault();

                const formData = {
                    itemCode: $('#editItemCode').val(),
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
        });
    </script>
@endpush
