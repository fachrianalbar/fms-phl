@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Report',
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
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom-select2.css') }}">
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }} Data</h4>

                <div class="accordion-item">
                    <a href="#" class="btn btn-icon btn-sm bg-dark-subtle" data-bs-toggle="collapse"
                        data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                        <i class="mdi mdi-magnify fs-14 text-dark"></i>
                    </a>
                </div>
            </div>

            <div class="card-header">
                <div class="accordion-collapse collapse" id="collapseTwo" aria-labelledby="headingTwo"
                    data-bs-parent="#simpleaccordion">
                    <div class="accordion-body col-md-12">
                        <form id="filterForm" class="g-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label" for="fleetCode">Plate Number</label>
                                    <select class="js-example-basic-single" name="fleetCode" id="fleetCode">
                                        <option selected value="">{{ __('general.choose') }}...</option>
                                        @foreach ($fleet as $item)
                                            <option value="{{ $item->code }}">{{ $item->plateNumber }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="fleetCompanyCode">Fleet Company</label>
                                    <select class="js-example-basic-single" name="fleetCompanyCode" id="fleetCompanyCode">
                                        <option selected value="">{{ __('general.choose') }}...</option>
                                        @foreach ($fleetCompany as $item)
                                            <option value="{{ $item->code }}">
                                                {{ $item->name }}{{ $item->type ? ' (' . $item->type . ')' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-3">
                                    <label class="form-label" for="startDate">Start Date</label>
                                    <input class="form-control" name="startDate" id="startDate" type="date"
                                        placeholder="Start Date">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label" for="endDate">End Date</label>
                                    <input class="form-control" name="endDate" id="endDate" type="date"
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
                    <table class="table table-striped w-100 nowrap" id="dt">
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>No</th>
                                <th>Plate Number</th>
                                <th>Fleet Company</th>
                                <th>Type</th>
                                <th>Total Maintenance</th>
                                <th>Total Qty</th>
                                <th>Total Cost</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
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
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2-custom.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/custom-flatpickr.js') }}"></script>
    <script>
        $(document).ready(function() {
            const table = $('#dt').DataTable({
                processing: true,
                serverSide: true,
                destroy: true,
                ajax: {
                    url: "{{ route('dt.maintenance-fleet') }}",
                    data: function(d) {
                        d.fleetCode = $('select[name="fleetCode"]').val();
                        d.fleetCompanyCode = $('select[name="fleetCompanyCode"]').val();
                        d.startDate = $('input[name="startDate"]').val();
                        d.endDate = $('input[name="endDate"]').val();
                    }
                },
                columns: [{
                        data: 'action'
                    },
                    {
                        data: 'DT_RowIndex'
                    },
                    {
                        data: 'plateNumber'
                    },
                    {
                        data: 'fleetCompanyName'
                    },
                    {
                        data: 'fleetCompanyType'
                    },
                    {
                        data: 'totalMaintenance'
                    },
                    {
                        data: 'totalQty'
                    },
                    {
                        data: 'totalCost'
                    }
                ],
                columnDefs: [{
                        searchable: false,
                        targets: [0, 1, 5, 6, 7]
                    },
                    {
                        orderable: false,
                        targets: [0, 1]
                    }
                ],
                order: [
                    [2, 'asc']
                ]
            });

            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                table.ajax.reload();
            });
        });
    </script>
@endpush
