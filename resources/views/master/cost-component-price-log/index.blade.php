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
<link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/sweetalert2.css') }} ">
<style>
    .export-loader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .export-loader-content {
        background: white;
        padding: 40px 60px;
        border-radius: 12px;
        text-align: center;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    }

    .export-loader-spinner {
        width: 60px;
        height: 60px;
        border: 5px solid #f3f3f3;
        border-top: 5px solid #28a745;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 20px;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    .export-loader-text {
        font-size: 18px;
        color: #333;
        font-weight: 500;
    }

    .export-loader-subtext {
        font-size: 14px;
        color: #666;
        margin-top: 8px;
    }

    .export-success {
        display: none;
        background: #d4edda;
        color: #155724;
        padding: 15px;
        border-radius: 8px;
        margin-top: 20px;
        border: 1px solid #c3e6cb;
        text-align: center;
        font-weight: 500;
    }
</style>
@endpush

@section('content')
<!-- Export Loader -->
<div class="export-loader" id="exportLoader">
    <div class="export-loader-content">
        <div class="export-loader-spinner"></div>
        <div class="export-loader-text">Exporting Data...</div>
        <div class="export-loader-subtext">Please wait while we prepare your Excel file</div>
    </div>
</div>

<div class="col-sm-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>{{ $title }} Data</h4>

            <div class="d-flex gap-2">
                <a href="javascript:void(0)" onclick="exportExcel()" class="btn btn-success" id="btn-export">
                    <i class="mdi mdi-file-excel"></i> Export Excel
                </a>
            </div>

        </div>
        <div class="card-body">
            @include('partials.alert')
            <div id="exportSuccessMessage" class="export-success">
                <i class="mdi mdi-check-circle"></i> Export completed successfully! Your Excel file has been downloaded.
            </div>
            <div class="table-responsive custom-scrollbar">
                <table class="table table-striped w-100 nowrap" id="dt">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Date</th>
                            <th>Cost Component Code</th>
                            <th>Cost Component Name</th>
                            <th>Old Price</th>
                            <th>New Price</th>
                            <th>Changed By</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
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
<script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>

<script>
    $(document).ready(function() {
        $('#dt').DataTable({
            "processing": true,
            "serverSide": true,
            "destroy": true,
            "ajax": {
                "url": "{{ route('dt.cost-component-price-log') }}",
            },
            "columns": [{
                    "data": 'DT_RowIndex'
                },
                {
                    "data": 'formatted_date'
                },
                {
                    "data": 'costComponentCode'
                },
                {
                    "data": 'costComponentName'
                },
                {
                    "data": 'formatted_old_price'
                },
                {
                    "data": 'formatted_new_price'
                },
                {
                    "data": 'changedBy'
                },
            ],
            "columnDefs": [{
                    "searchable": false,
                    "targets": [0]
                },
                {
                    "orderable": false,
                    "targets": [0]
                }
            ],
            "order": [
                [1, 'desc']
            ]
        })
    });

    function exportExcel() {
        // Hide any existing success message
        document.getElementById('exportSuccessMessage').style.display = 'none';

        // Show loader
        document.getElementById('exportLoader').style.display = 'flex';

        // Create a hidden iframe to handle the download
        var iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        iframe.src = "{{ route('master.cost-component-price-log.export-excel') }}";
        document.body.appendChild(iframe);

        // Hide loader and show success message after download starts
        setTimeout(function() {
            document.getElementById('exportLoader').style.display = 'none';
            document.getElementById('exportSuccessMessage').style.display = 'block';
            document.body.removeChild(iframe);

            // Hide success message after 5 seconds
            setTimeout(function() {
                document.getElementById('exportSuccessMessage').style.display = 'none';
            }, 5000);
        }, 3000);
    }
</script>
@endpush