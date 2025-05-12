@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Operational',
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
                                    <label class="form-label" for="name">Code</label>
                                    <input class="form-control" name="code" type="text" placeholder="Code">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="name">Tanggal</label>
                                    <input class="form-control" name="date" id="datetime-local" type="date"
                                        placeholder="Tanggal">
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <label class="form-label" for="fleetType">Serah Terima</label>
                                    <select class="js-example-basic-single" name="fleetType" id="fleetType">
                                        <option selected="" value="">Choose...</option>
                                        @foreach ($fleetType as $item)
                                            <option value="{{ $item->name }}">{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="name">Nomor Bon</label>
                                    <input class="form-control" name="bon" type="text" placeholder="Nomor Bon">
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
                                <th>Tanggal</th>
                                <th>Serah Terima Oleh</th>
                                <th>Nomor Bon</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog" id="modal-null"
                aria-labelledby="myLargeModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Empty Data</h4>
                            <button class="btn-close py-0" type="button" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="card">
                            <div class="card-body col-md-12">

                            </div>
                        </div>
                    </div>
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

    <script>
        $(document).ready(function() {
            const table = $('#dt').DataTable({
                "processing": true,
                "serverSide": true,
                "destroy": true,
                "ajax": {
                    "url": "{{ route('dt.bon-ujt') }}",
                    "data": function(d) {
                        d.code = $('input[name="code"]').val();
                        d.date = $('input[name="date"]').val();
                        d.fleetType = $('select[name="fleetType"]').val();
                        d.bon = $('input[name="bon"]').val();
                    }
                },
                "columns": [{
                        "data": 'action'
                    },
                    {
                        "data": 'DT_RowIndex'
                    },
                    {
                        "data": 'code'
                    },
                    {
                        "data": 'date'
                    },
                    {
                        "data": 'fleetType.name'
                    },
                    {
                        "data": 'bon'
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

            $('#filterForm').on('submit', function(e) {
                e.preventDefault();

                table.ajax.reload(); // Reload DataTable dengan filter baru
            });

            $('#check-null').on('click', function() {
                $.ajax({
                    url: "{{ route('operational.order.check-null-relation') }}", // Endpoint untuk mengecek relasi null
                    method: "GET",
                    success: function(response) {
                        if (response.success) {
                            let data = response.data;

                            // Buat tabel dengan Bootstrap
                            let content = `
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Shipment Number</th>
                                <th>Empty Data</th>
                            </tr>
                        </thead>
                        <tbody>
                `;

                            data.forEach((item, index) => {
                                content += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${item.shipmentNumber}</td>
                            <td>${item.nullRelations}</td>
                        </tr>
                    `;
                            });

                            content += `
                        </tbody>
                    </table>
                `;

                            $('#modal-null .card-body').html(content); // Isi modal dengan data
                            $('#modal-null').modal('show'); // Tampilkan modal
                        } else {
                            swal({
                                title: "Info",
                                text: "No empty data found",
                                icon: "info",
                            })
                        }
                    },
                    error: function() {
                        swal({
                            title: "Warning",
                            text: "An error occurred while checking null relations.",
                            icon: "warning",
                        })
                    }
                });
            });
        });

        function deleteData(uuid) {
            var url = '{{ route('operational.bon-ujt.index') }}/' + uuid;
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
