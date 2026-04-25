@extends('layouts.main', [
'title' => $title,
'pageTitle' => 'Sub Menu',
'firstSegment' => 'Master',
'secondSegment' => 'Menu',
'thirdSegment' => 'Sub Menu',
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
@endpush

@section('content')
<div class="col-sm-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h4>Sub Menu of: <span class="text-primary">{{ $parentMenu->name }}</span></h4>
                <small class="text-muted">Parent Code: {{ $parentCode }}</small>
            </div>

            <div>
                <a href="{{ route('master.menu.index') }}" class="btn btn-info me-2">
                    <i class="mdi mdi-arrow-left me-1"></i>Back to Main Menu
                </a>
                <a href="{{ route('master.menu.create-sub-menu', $parentCode) }}" class="btn btn-primary">
                    <i class="mdi mdi-plus me-1"></i>Add Sub Menu
                </a>
            </div>
        </div>
        <div class="card-body">
            @include('partials.alert')

            <!-- Parent Menu Info -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="alert alert-info">
                        <div class="d-flex align-items-center">
                            @if($parentMenu->icon)
                            <i class="{{ $parentMenu->icon }} me-2" style="font-size: 24px;"></i>
                            @endif
                            <div>
                                <strong>{{ $parentMenu->name }}</strong>
                                <br>
                                <small>Icon: {{ $parentMenu->icon ?? '-' }} | URL: {{ $parentMenu->url }} | Sort: {{ $parentMenu->sort }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive custom-scrollbar">
                <table class="table table-striped w-100 nowrap" id="dt">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Code</th>
                            <th>Name (EN)</th>
                            <th>Nama (ID)</th>
                            <th>Icon</th>
                            <th>URL</th>
                            <th>Sort</th>
                            <th>Action</th>
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

<script>
    const ROUTES = {
        menuIndex: @json(route('master.menu.index')),
        dtMenuSub: @json(route('dt.menu-sub', $parentCode))
    };

    $(document).ready(function() {
        $('#dt').DataTable({
            "processing": true,
            "serverSide": true,
            "destroy": true,
            "ajax": {
                "url": ROUTES.dtMenuSub,
            },
            "columns": [{
                    "data": 'DT_RowIndex'
                },
                {
                    "data": 'code'
                },
                {
                    "data": 'name'
                },
                {
                    "data": 'nama'
                },
                {
                    "data": 'icon',
                    "render": function(data, type, row) {
                        if (data) {
                            return '<i class="' + data + '"></i> ' + data;
                        }
                        return '-';
                    }
                },
                {
                    "data": 'url'
                },
                {
                    "data": 'sort'
                },
                {
                    "data": 'action'
                }
            ],
            "columnDefs": [{
                    "searchable": false,
                    "targets": [0, 7]
                },
                {
                    "orderable": false,
                    "targets": [0, 7]
                }
            ],
            "order": [
                [5, 'asc']
            ]
        })
    });

    function deleteData(uuid, parentCode) {
        var url = ROUTES.menuIndex + '/' + uuid;
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