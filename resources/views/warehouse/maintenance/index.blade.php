@extends('layouts.main', [
'title' => $title,
'pageTitle' => $title,
'firstSegment' => 'Warehouse',
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
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">
<link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/select2.css') }}">

<link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/custom-select2.css') }}">
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

                {{-- <a href="{{ route($view . 'excel-order', ['type' => 'order']) }}" class="btn btn-info"
                id="export-data">Export Data</a> --}}

                {{-- <a href="#" id="print-pdf" class="btn btn-danger"> <i class="icofont icofont-file-pdf"></i> </a> --}}

                <a href="#" id="print-pdf" class="btn btn-icon btn-sm bg-danger-subtle" data-bs-toggle="tooltip">
                    <i class="mdi mdi-file fs-14 text-danger"></i>
                </a>


                <a href="{{ route($view . 'create') }}" class="btn btn-primary">{{ __('general.add_data') }}</a>
            </div>

        </div>

        <div class="card-header">
            <div class="accordion-collapse collapse" id="collapseTwo" aria-labelledby="headingTwo"
                data-bs-parent="#simpleaccordion">
                <div class="accordion-body col-md-12">
                    <form id="filterForm" class=" g-3">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label" for="name">{{ __('menu_maintenance.plate_no') }}</label>
                                <select class="form-control" name="plateNumber" id="plateNumber">
                                    <option selected="" value="">{{ __('general.choose') }}...</option>
                                    @foreach ($fleet as $item)
                                    <option value="{{ $item->plateNumber }}">
                                        {{ $item->plateNumber }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label"
                                    for="name">{{ __('menu_maintenance.maintenance_date') }}</label>
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
                                <select class="form-control" name="itemCode" id="itemCode">
                                    <option selected="" value="">{{ __('general.choose') }}...</option>
                                    @foreach ($stock as $item)
                                    <option value="{{ $item->itemCode }}">
                                        {{ $item->itemCode . ' - ' . ($item->item->name ?? '') }}
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
                            <th>{{ __('menu_maintenance.date') }}</th>
                            <th>{{ __('menu_maintenance.plate_no') }}</th>
                            <th>Warehouse</th>
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

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel">Maintenance Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Code:</strong>
                        <p id="detail-code"></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Date:</strong>
                        <p id="detail-date"></p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Fleet:</strong>
                        <p id="detail-fleet"></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Warehouse:</strong>
                        <p id="detail-warehouse"></p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <strong>Items:</strong>
                        <div class="table-responsive mt-2">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Item Code</th>
                                        <th>Item Name</th>
                                        <th>Qty</th>
                                    </tr>
                                </thead>
                                <tbody id="detail-items">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
                    "data": "warehouse"
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

        $('#print-pdf').click(function(e) {
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

    function showDetail(id) {
        // Make AJAX request to get maintenance detail
        $.ajax({
            url: '{{ route("warehouse.maintenance.index") }}/' + id,
            method: 'GET',
            success: function(response) {
                // Populate modal with data
                $('#detail-code').text(response.code);
                $('#detail-date').text(response.date + ' ' + response.time);
                $('#detail-fleet').text(response.fleet ? response.fleet.plateNumber : '-');
                $('#detail-warehouse').text(response.warehouse ? response.warehouse.name : '-');

                // Populate items table
                let itemsHtml = '';
                if (response.details && response.details.length > 0) {
                    response.details.forEach(function(item, index) {
                        itemsHtml += '<tr>';
                        itemsHtml += '<td>' + (index + 1) + '</td>';
                        itemsHtml += '<td>' + item.itemCode + '</td>';
                        itemsHtml += '<td>' + (item.item ? item.item.name : '-') + '</td>';
                        itemsHtml += '<td>' + item.qty + '</td>';
                        itemsHtml += '</tr>';
                    });
                } else {
                    itemsHtml = '<tr><td colspan="4" class="text-center">No items found</td></tr>';
                }
                $('#detail-items').html(itemsHtml);

                // Show modal
                $('#detailModal').modal('show');
            },
            error: function() {
                swal({
                    title: "Error",
                    text: "Failed to load maintenance details",
                    icon: "error",
                });
            }
        });
    }

    function deleteData(uuid) {
        var url = '{{ route('
        warehouse.maintenance.index ') }}/' + uuid;
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
</script>
@endpush