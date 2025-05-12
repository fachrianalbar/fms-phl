@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Report',
    'secondSegment' => $title,
])

@push('style')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/datatables.css') }}">
    <link rel="stylesheet" type="text/css" href="../assets/css/vendors/sweetalert2.css">
    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">
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

                        <a href="{{ route($view . 'excel-fleet-tonase') }}" class="btn btn-success" id="export-data">
                            <i class="icofont icofont-file-excel"></i>
                        </a>

                    </div>

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
                                    <select class="js-example-basic-single" name="fleetCode" id="fleetCode">
                                        <option selected="" value="">Choose...</option>
                                        @foreach ($fleet as $item)
                                            <option value="{{ $item->code }}">
                                                {{ $item->plateNumber }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="name">Fleet Type Name</label>
                                    <select class="js-example-basic-single" name="fleetTypeName" id="fleetTypeName">
                                        <option selected="" value="">Choose...</option>
                                        @foreach ($fleetType as $item)
                                            <option value="{{ $item->name }}">{{ $item->name }}
                                            </option>
                                        @endforeach
                                    </select>`
                                </div>


                            </div>

                            <div class="row mt-4">

                                <div class="col-md-6">
                                    <label class="form-label" for="name">Customer Name</label>
                                    <select class="js-example-basic-single" name="customerCode" id="customerCode">
                                        <option selected="" value="">Choose...</option>
                                        @foreach ($customer as $item)
                                            <option value="{{ $item->code }}">{{ $item->name }}
                                            </option>
                                        @endforeach
                                    </select>`
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label" for="name">Date</label>
                                    <input class="form-control" name="startDate" id="datetime-local" type="date"
                                        placeholder="Start Date">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label" for="name"></label>
                                    <input class="form-control" name="endDate" id="datetime-local" type="date"
                                        placeholder="End Date">
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
                                <th>Plate Number</th>
                                <th>Fleet Type</th>
                                <th>Customer Name</th>
                                <th>Tonase</th>
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
    <script src="{{ asset('assets/js/flat-pickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/custom-flatpickr.js') }}"></script>
    <script>
        $(document).ready(function() {
            const table = $('#dt').DataTable({
                "processing": true,
                "serverSide": true,
                "destroy": true,
                "ajax": {
                    "url": "{{ route('dt.fleet-tonase') }}",
                    "data": function(d) {
                        d.customerCode = $('select[name="customerCode"]').val();
                        d.fleetCode = $('select[name="fleetCode"]').val();
                        d.fleetTypeName = $('select[name="fleetTypeName"]').val();
                        d.startDate = $('input[name="startDate"]').val();
                        d.endDate = $('input[name="endDate"]').val();
                    }
                },
                "columns": [{
                        "data": 'DT_RowIndex'
                    },
                    {
                        "data": 'fleet.plateNumber'
                    },
                    {
                        "data": 'fleet.type.name'
                    },
                    {
                        "data": 'customer.name'
                    },
                    {
                        "data": 'total_tonase'
                    }
                ],
                "columnDefs": [{
                        "searchable": false,
                        "targets": [0, 4]
                    },
                    {
                        "orderable": false,
                        "targets": [0, 1, 2, 3, 4]
                    }
                ],
                "order": [
                    [0, 'asc']
                ]
            })

            $('#filterForm').on('submit', function(e) {
                e.preventDefault();

                let queryParams = $(this).serialize(); // Serialize the form data

                let exportUrl = "{{ route($view . 'excel-fleet-tonase') }}" + "?" + queryParams;

                $('#export-data').attr('href', exportUrl);

                table.ajax.reload();
            });
        });

        function deleteData(uuid) {
            var url = '{{ route('master.unit.index') }}/' + uuid;
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
