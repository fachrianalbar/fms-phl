@extends('layouts.main', [
'title' => $title,
'pageTitle' => $title,
'firstSegment' => 'Data',
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

<!-- Select2 CSS -->
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/select2.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom-select2.css') }}">
@endpush

@section('content')
<div class="col-sm-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>{{ $title }} Data</h4>

            <div class="d-flex align-items-center gap-3">
                <a href="{{ route($view . 'create') }}" class="btn btn-primary btn-md d-inline-flex align-items-center gap-2" title="{{ __('general.add_data') }}" aria-label="{{ __('general.add_data') }}">
                    <i class="mdi mdi-plus fs-16"></i>
                    <span class="d-none d-md-inline">{{ __('general.add_data') }}</span>
                </a>

                <a href="#" class="btn btn-outline-primary btn-md d-inline-flex align-items-center gap-2" data-bs-toggle="collapse"
                    data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo" title="{{ __('general.filter') }}" aria-label="{{ __('general.filter') }}">
                    <i class="mdi mdi-magnify fs-16 text-primary"></i>
                    <span class="d-none d-md-inline">{{ __('general.filter') }}</span>
                </a>

            </div>
        </div>

        <div class="card-header">
            <div class="accordion-collapse collapse" id="collapseTwo" aria-labelledby="headingTwo"
                data-bs-parent="#simpleaccordion">
                <div class="accordion-body col-md-12">
                    <form id="filterForm" class=" g-3">
                        <div class="row gy-3">
                            <div class="col-md-6">
                                <label class="form-label" for="customerName">{{ __('menu_route.customer') }}</label>
                                <select class="js-example-basic-single form-control" name="customerName" id="customerName">
                                    <option selected="" value="">{{ __('general.choose') }}...</option>
                                    @foreach ($customer as $item)
                                    <option value="{{ $item->name }}">{{ $item->code . ' - ' . $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="origin">{{ __('menu_route.origin') }}</label>
                                <select class="js-example-basic-single form-control" name="origin" id="origin">
                                    <option selected="" value="">{{ __('general.choose') }}...</option>
                                    @foreach ($location as $item)
                                    <option value="{{ $item->name }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="destination">{{ __('menu_route.destination') }}</label>
                                <select class="js-example-basic-single form-control" name="destination" id="destination">
                                    <option selected="" value="">{{ __('general.choose') }}...</option>
                                    @foreach ($location as $item)
                                    <option value="{{ $item->name }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="fleetTypeName">{{ __('menu_route.fleet_type') }}</label>
                                <select class="js-example-basic-single form-control" name="fleetTypeName" id="fleetTypeName">
                                    <option selected="" value="">{{ __('general.choose') }}...</option>
                                    @foreach ($fleetType as $item)
                                    <option value="{{ $item->name }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="routeTypeName">{{ __('menu_route.load_type') }}</label>
                                <select class="js-example-basic-single form-control" name="routeTypeName" id="routeTypeName">
                                    <option selected="" value="">{{ __('general.choose') }}...</option>
                                    @foreach ($routeType as $item)
                                    <option value="{{ $item->name }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-12 d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-primary btn-md d-inline-flex align-items-center gap-2" id="filterBtn" title="{{ __('general.filter') }}">
                                    <i class="mdi mdi-magnify fs-16"></i>
                                    <span>{{ __('general.filter') }}</span>
                                </button>

                                <button type="button" class="btn btn-outline-secondary btn-md d-inline-flex align-items-center gap-2" id="resetBtn" title="{{ __('general.reset') }}">
                                    <i class="mdi mdi-refresh fs-16"></i>
                                    <span>{{ __('general.reset') }}</span>
                                </button>
                            </div>
                        </div>
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
                            <th>{{ __('menu_route.name') }}</th>
                            <th>{{ __('menu_route.description') }}</th>
                            <th>{{ __('menu_route.customer') }}</th>
                            <th>{{ __('menu_route.origin') }}</th>
                            <th>{{ __('menu_route.destination') }}</th>
                            {{-- <th>Fleet Type</th> --}}
                            <th>{{ __('menu_route.load_type') }}</th>
                            <th>{{ __('menu_route.price') }}</th>
                            <th>{{ __('menu_route.vendor_price') }}</th>
                            <th>{{ __('menu_route.personal_vendor_price') }}</th>
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
<script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>

<!-- Select2 JS -->
<script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
<script src="{{ asset('assets/js/select2/select2-custom.js') }}"></script>
<script>
    $(document).ready(function() {
        $('#dt').DataTable({
            "processing": true,
            "serverSide": true,
            "destroy": true,
            "ajax": {
                "url": "{{ route('dt.route') }}",
                "data": function(d) {
                    d.customerName = $('#customerName').val();
                    d.origin = $('#origin').val();
                    d.destination = $('#destination').val();
                    d.fleetTypeName = $('#fleetTypeName').val();
                    d.routeTypeName = $('#routeTypeName').val();
                }
            },
            "columns": [{
                    "data": 'action'
                },
                {
                    "data": 'DT_RowIndex'
                },
                {
                    "data": 'name'
                },
                {
                    "data": 'description'
                },
                {
                    "data": 'customer.name'
                },
                {
                    "data": 'origin_location.name'
                },
                {
                    "data": 'destination_location.name'
                },
                {
                    "data": 'route_type.name'
                },
                // {
                // "data": 'fleet_type.name'
                // },
                {
                    "data": 'price'
                },
                {
                    "data": 'vendorPrice'
                },
                {
                    "data": 'personalVendorPrice'
                },

            ],
            "columnDefs": [{
                    "searchable": false,
                    "targets": [0, 1]
                },
                {
                    "orderable": false,
                    "targets": [0]
                }
            ],
            "order": [
                [2, 'asc']
            ]
        })

        // Init Select2 for filters
        $('.js-example-basic-single').select2({
            placeholder: "{{ __('general.choose') }}...",
            allowClear: true,
            width: '100%'
        });
        $('#dt').DataTable().ajax.reload();

        // Event untuk filter button
        $('#filterBtn').click(function() {
            $('#dt').DataTable().ajax.reload();
        });
    });

    // Event untuk reset button
    $('#resetBtn').click(function() {
        $('#filterForm')[0].reset();
        $('#customerName, #origin, #destination, #fleetTypeName, #routeTypeName').val('').trigger('change');
        $('#dt').DataTable().ajax.reload();
    });

    function deleteData(uuid) {
        var url = '{{ route("data.route.index") }}/' + uuid;
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