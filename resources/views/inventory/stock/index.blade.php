@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Master',
    'secondSegment' => $title,
])

@push('style')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/datatables.css') }}">
    <link rel="stylesheet" type="text/css" href="../assets/css/vendors/sweetalert2.css">
    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/select2.css') }}">
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }} Data</h4>

                <div class="d-flex align-items-center gap-3">
                    <div class="accordion-item ">
                        <button class=" collapsed btn btn-light active" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo"><i
                                class="icofont icofont-search-alt-1"></i></i>
                        </button>
                    </div>

                    <a href="{{ route($view . 'pdf-stock') }}" target="_blank" id="print-pdf" class="btn btn-danger"> <i
                            class="icofont icofont-file-pdf"></i> </a>

                    {{-- <a href="{{ route($view . 'create') }}" class="btn btn-primary">Add Data</a> --}}
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
                                        <option selected="" value="">Choose...</option>
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
                    <table class="display" id="dt">
                        <thead>
                            <tr>
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
@endsection

@push('script')
    <script src="{{ asset('assets/js/datatable/datatables/jquery.dataTables.min.js') }}"></script>
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
                "columns": [{
                        "data": 'DT_RowIndex'
                    },
                    {
                        "data": 'itemCode'
                    },
                    {
                        "data": 'item.name'
                    },
                    // {
                    //     "data": "stockIn"
                    // },
                    // {
                    //     "data": 'stockOut'
                    // },
                    {
                        "data": 'total'
                    },
                ],
                "columnDefs": [{
                        "searchable": false,
                        "targets": [0, 2]
                    },
                    {
                        "orderable": false,
                        "targets": [0, 2]
                    }
                ],
                "order": [
                    [1, 'desc']
                ]
            })

            $('.btn-danger').click(function(e) {
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
        });
    </script>
@endpush
