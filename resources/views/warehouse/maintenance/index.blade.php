@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Master',
    'secondSegment' => $title,
])

@push('style')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/datatables.css') }}">
    <link rel="stylesheet" type="text/css" href="../assets/css/vendors/sweetalert2.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">
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

                    {{-- <a href="{{ route($view . 'excel-order', ['type' => 'order']) }}" class="btn btn-info"
                        id="export-data">Export Data</a> --}}

                    <a href="#" id="print-pdf" class="btn btn-danger"> <i class="icofont icofont-file-pdf"></i> </a>


                    <a href="{{ route($view . 'create') }}" class="btn btn-primary">Add Data</a>
                </div>

            </div>

            <div class="card-header">
                <div class="accordion-collapse collapse" id="collapseTwo" aria-labelledby="headingTwo"
                    data-bs-parent="#simpleaccordion">
                    <div class="accordion-body col-md-12">
                        <form id="filterForm" class=" g-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label" for="name">Plate Number</label>
                                    <select class="js-example-basic-single" name="plateNumber" id="plateNumber">
                                        <option selected="" value="">Choose...</option>
                                        @foreach ($fleet as $item)
                                            <option value="{{ $item->plateNumber }}">
                                                {{ $item->plateNumber }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label" for="name">Maintenance Date</label>
                                    <input class="form-control" name="startDate" id="datetime-local" type="date"
                                        placeholder="Start Date">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label" for="name"></label>
                                    <input class="form-control" name="endDate" id="datetime-local" type="date"
                                        placeholder="End Date">
                                </div>
                            </div>

                            <div class="row mt-4">
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
                                <th>#</th>
                                <th>No</th>
                                <th>Code</th>
                                <th>Date</th>
                                <th>Plate No</th>
                                <th>Items</th>
                                <th>Cost</th>
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
    <script src="{{ asset('assets/js/flat-pickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/custom-flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src=" {{ asset('assets/js/select2/select2-custom.js') }}"></script>
    {{-- <script src="../assets/js/sweet-alert/app.js"></script> --}}

    <script>
        $(document).ready(function() {
            const table = $('#dt').DataTable({
                "processing": true,
                "serverSide": true,
                "destroy": true,
                "ajax": {
                    "url": "{{ route('dt.maintenance') }}",
                    "data": function(d) {
                        d.plateNumber = $('select[name="plateNumber"]').val();
                        d.startDate = $('input[name="startDate"]').val();
                        d.endDate = $('input[name="endDate"]').val();
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
                        "data": 'maintenanceDate'
                    },
                    {
                        "data": 'fleet.plateNumber'
                    },
                    {
                        "data": "items"
                    },
                    {
                        "data": "price"
                    },

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

            // Add click event for the Print PDF button
            $('.btn-danger').click(function(e) {
                e.preventDefault(); // Prevent the default action of the link

                let plateNumber = $('#plateNumber').val();
                let startDate = $('input[name="startDate"]').val();
                let endDate = $('input[name="endDate"]').val();

                if (!plateNumber || !startDate || !endDate) {
                    swal({
                        title: "Incomplete Data",
                        icon: "warning",
                        content: {
                            element: "div",
                            attributes: {
                                innerHTML: "<p style='text-align: center;'>Please fill in the Plate Number, Start Date, and End Date before printing the PDF.</p>"
                            },
                        },
                    });

                } else {
                    const queryParams = $("#filterForm").serialize(); // Serialize the form data

                    const printPdf = "{{ route($view . 'pdf-maintenance') }}?" + queryParams;

                    $('#print-pdf').attr('href', printPdf);


                    // If all fields are filled, proceed to the PDF generation
                    window.open(printPdf); // Use the correct URL string
                }
            });
        });

        function deleteData(uuid) {
            var url = '{{ route('warehouse.maintenance.index') }}/' + uuid;
            $('#delete-form').attr('action', url);

            swal({
                title: "Are you sure?",
                text: "Want to delete this data?",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    $('#delete-form').submit();
                } else {
                    swal("Your data is safe!");
                }
            });
        }
    </script>
@endpush
