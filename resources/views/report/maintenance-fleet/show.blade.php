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
@endpush

@section('content')
    @php
        $excelDetailUrl = route(
            $view . 'excel-maintenance-fleet-detail',
            array_filter(
                [
                    'fleetCode' => $fleet->code,
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                ],
                fn($value) => $value !== null && $value !== '',
            ),
        );

        $pdfDetailUrl = route(
            $view . 'pdf-maintenance-fleet-detail',
            array_filter(
                [
                    'fleetCode' => $fleet->code,
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                ],
                fn($value) => $value !== null && $value !== '',
            ),
        );
    @endphp

    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }}</h4>

                <div class="d-flex align-items-center gap-2">
                    <a href="{{ $excelDetailUrl }}" target="_blank" class="btn btn-icon btn-sm bg-success-subtle">
                        <i class="mdi mdi-file-excel fs-14 text-success"></i>
                    </a>

                    <a href="{{ $pdfDetailUrl }}" target="_blank" class="btn btn-icon btn-sm bg-danger-subtle">
                        <i class="mdi mdi-file-pdf-box fs-14 text-danger"></i>
                    </a>

                    <a href="{{ route($view . 'index') }}" class="btn btn-info">{{ __('general.back_to_list') }}</a>
                </div>
            </div>

            <div class="card-body">
                <form method="GET" action="{{ route('report.maintenance-fleet.detail', ['fleetCode' => $fleet->code]) }}"
                    class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label" for="startDate">Start Date</label>
                        <input class="form-control" name="startDate" id="startDate" type="date"
                            value="{{ $startDate }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label" for="endDate">End Date</label>
                        <input class="form-control" name="endDate" id="endDate" type="date"
                            value="{{ $endDate }}">
                    </div>

                    <div class="col-md-3 align-self-end">
                        <button class="btn btn-primary" type="submit">Filter</button>
                    </div>
                </form>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="d-flex justify-content-between gap-4">
                            <span class="fw-semibold">Plate Number</span>
                            <span>{{ $fleet->plateNumber }}</span>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="d-flex justify-content-between gap-4">
                            <span class="fw-semibold">Fleet Company</span>
                            <span>{{ $fleet->company?->name ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="d-flex justify-content-between gap-4">
                            <span class="fw-semibold">Type</span>
                            <span>{{ $fleet->company?->type ?? 'Internal' }}</span>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="d-flex justify-content-between gap-4">
                            <span class="fw-semibold">Total Maintenance</span>
                            <span>{{ number_format($totalMaintenance, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="d-flex justify-content-between gap-4">
                            <span class="fw-semibold">Total Qty</span>
                            <span>{{ number_format($totalQty, 1, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="d-flex justify-content-between gap-4">
                            <span class="fw-semibold">Total Cost</span>
                            <span>Rp {{ number_format($totalCost, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <div class="table-responsive custom-scrollbar">
                    <table class="table table-striped w-100 nowrap" id="dt-maintenance-detail">
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>No</th>
                                <th>Maintenance Code</th>
                                <th>Date</th>
                                <th>Warehouse</th>
                                <th>Total Item</th>
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

    <div class="modal fade" id="maintenanceDetailModal" tabindex="-1" aria-labelledby="maintenanceDetailModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="maintenanceDetailModalLabel">Maintenance Item Detail</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Code:</strong> <span id="detail-maintenance-code">-</span></div>
                        <div class="col-md-4"><strong>Date:</strong> <span id="detail-maintenance-date">-</span></div>
                        <div class="col-md-4"><strong>Warehouse:</strong> <span id="detail-maintenance-warehouse">-</span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Item Code</th>
                                    <th>Item Name</th>
                                    <th>Supplier</th>
                                    <th>Qty</th>
                                    <th>Price</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="maintenance-detail-items"></tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-end">TOTAL</th>
                                    <th id="maintenance-detail-total-qty">0,0</th>
                                    <th></th>
                                    <th id="maintenance-detail-total-cost">Rp 0</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
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

    <script>
        const maintenanceDetailModal = new bootstrap.Modal(document.getElementById('maintenanceDetailModal'));

        $(document).ready(function() {
            $('#dt-maintenance-detail').DataTable({
                processing: true,
                serverSide: true,
                destroy: true,
                ajax: {
                    url: "{{ route('dt.maintenance-fleet-detail', ['fleetCode' => $fleet->code]) }}",
                    data: function(d) {
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
                        data: 'code'
                    },
                    {
                        data: 'maintenanceDate'
                    },
                    {
                        data: 'warehouseName'
                    },
                    {
                        data: 'totalItem'
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
                    [3, 'desc']
                ]
            });
        });

        function showMaintenanceItems(maintenanceCode) {
            const url = "{{ route('ajax.maintenance-fleet-detail-items', ['maintenanceCode' => ':maintenanceCode']) }}"
                .replace(':maintenanceCode', maintenanceCode);

            $.get(url, function(response) {
                $('#detail-maintenance-code').text(response.code || '-');
                $('#detail-maintenance-date').text(response.maintenanceDate || '-');
                $('#detail-maintenance-warehouse').text(response.warehouse || '-');

                let rows = '';
                if (response.details && response.details.length > 0) {
                    response.details.forEach(function(item, index) {
                        rows += '<tr>';
                        rows += '<td>' + (index + 1) + '</td>';
                        rows += '<td>' + item.itemCode + '</td>';
                        rows += '<td>' + item.itemName + '</td>';
                        rows += '<td>' + item.supplierName + '</td>';
                        rows += '<td>' + new Intl.NumberFormat('id-ID', {
                            minimumFractionDigits: 1,
                            maximumFractionDigits: 1
                        }).format(item.qty) + '</td>';
                        rows += '<td>Rp ' + new Intl.NumberFormat('id-ID').format(item.price) + '</td>';
                        rows += '<td>Rp ' + new Intl.NumberFormat('id-ID').format(item.subtotal) + '</td>';
                        rows += '</tr>';
                    });
                } else {
                    rows = '<tr><td colspan="7" class="text-center">No item detail found</td></tr>';
                }

                $('#maintenance-detail-items').html(rows);
                $('#maintenance-detail-total-qty').text(new Intl.NumberFormat('id-ID', {
                    minimumFractionDigits: 1,
                    maximumFractionDigits: 1
                }).format(response.totalQty ?? 0));
                $('#maintenance-detail-total-cost').text('Rp ' + new Intl.NumberFormat('id-ID').format(response
                    .totalCost ?? 0));
                maintenanceDetailModal.show();
            });
        }
    </script>
@endpush
