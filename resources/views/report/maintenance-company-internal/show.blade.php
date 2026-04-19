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
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom-select2.css') }}">
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }}</h4>
                <a href="{{ route($view . 'index') }}" class="btn btn-info">{{ __('general.back_to_list') }}</a>
            </div>

            <div class="card-body">
                <form method="GET"
                    action="{{ route('report.maintenance-company-internal.detail', ['fleetCompanyCode' => $fleetCompany->code]) }}"
                    class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label" for="fleetCode">Plate Number</label>
                        <select class="js-example-basic-single" name="fleetCode" id="fleetCode">
                            <option selected value="">{{ __('general.choose') }}...</option>
                            @foreach ($fleet as $item)
                                <option value="{{ $item->code }}" {{ $fleetCode == $item->code ? 'selected' : '' }}>
                                    {{ $item->plateNumber }}
                                </option>
                            @endforeach
                        </select>
                    </div>

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

                    <div class="col-md-2 align-self-end">
                        <button class="btn btn-primary w-100" type="submit">Filter</button>
                    </div>
                </form>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="d-flex justify-content-between gap-4">
                            <span class="fw-semibold">Fleet Company</span>
                            <span>{{ $fleetCompany->name }}</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex justify-content-between gap-4">
                            <span class="fw-semibold">Type</span>
                            <span>{{ $fleetCompany->type ?: 'Internal' }}</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex justify-content-between gap-4">
                            <span class="fw-semibold">Total Fleet</span>
                            <span>{{ number_format($totalFleet, 0, ',', '.') }}</span>
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
                    <table class="table table-striped w-100 nowrap" id="dt-company-maintenance-detail">
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>No</th>
                                <th>Maintenance Code</th>
                                <th>Date</th>
                                <th>Plate Number</th>
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
                        <div class="col-md-3"><strong>Code:</strong> <span id="detail-maintenance-code">-</span></div>
                        <div class="col-md-3"><strong>Date:</strong> <span id="detail-maintenance-date">-</span></div>
                        <div class="col-md-3"><strong>Plate:</strong> <span id="detail-maintenance-plate">-</span></div>
                        <div class="col-md-3"><strong>Warehouse:</strong> <span id="detail-maintenance-warehouse">-</span>
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
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2-custom.js') }}"></script>

    <script>
        const maintenanceDetailModal = new bootstrap.Modal(document.getElementById('maintenanceDetailModal'));

        $(document).ready(function() {
            $('#dt-company-maintenance-detail').DataTable({
                processing: true,
                serverSide: true,
                destroy: true,
                ajax: {
                    url: "{{ route('dt.maintenance-company-internal-detail', ['fleetCompanyCode' => $fleetCompany->code]) }}",
                    data: function(d) {
                        d.fleetCode = $('select[name="fleetCode"]').val();
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
                        data: 'plateNumber'
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
                        targets: [0, 1, 6, 7, 8]
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
            const url =
                "{{ route('ajax.maintenance-company-internal-detail-items', ['maintenanceCode' => ':maintenanceCode']) }}"
                .replace(':maintenanceCode', maintenanceCode);

            $.get(url, function(response) {
                $('#detail-maintenance-code').text(response.code || '-');
                $('#detail-maintenance-date').text(response.maintenanceDate || '-');
                $('#detail-maintenance-plate').text(response.plateNumber || '-');
                $('#detail-maintenance-warehouse').text(response.warehouse || '-');

                let rows = '';
                const qtyFormatter = new Intl.NumberFormat('id-ID', {
                    minimumFractionDigits: 1,
                    maximumFractionDigits: 1
                });
                const currencyFormatter = new Intl.NumberFormat('id-ID');
                if (response.details && response.details.length > 0) {
                    response.details.forEach(function(item, index) {
                        rows += '<tr>';
                        rows += '<td>' + (index + 1) + '</td>';
                        rows += '<td>' + item.itemCode + '</td>';
                        rows += '<td>' + item.itemName + '</td>';
                        rows += '<td>' + item.supplierName + '</td>';
                        rows += '<td>' + qtyFormatter.format(item.qty) + '</td>';
                        rows += '<td>Rp ' + currencyFormatter.format(item.price) + '</td>';
                        rows += '<td>Rp ' + currencyFormatter.format(item.subtotal) + '</td>';
                        rows += '</tr>';
                    });
                } else {
                    rows = '<tr><td colspan="7" class="text-center">No item detail found</td></tr>';
                }

                $('#maintenance-detail-items').html(rows);
                $('#maintenance-detail-total-qty').text(qtyFormatter.format(response.totalQty ?? 0));
                $('#maintenance-detail-total-cost').text('Rp ' + currencyFormatter.format(response.totalCost ?? 0));
                maintenanceDetailModal.show();
            });
        }
    </script>
@endpush
