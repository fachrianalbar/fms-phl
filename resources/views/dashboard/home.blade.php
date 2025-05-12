@extends('layouts.main', [
    'title' => 'Dashboard',
    'pageTitle' => 'Dashboard',
    'firstSegment' => 'Dashboard',
    'secondSegment' => 'Home',
])

@push('style')
    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/datatables.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">
@endpush

@section('content')
    <div class="col-xl-12 col-md-12 box-col-none">

        <div class="row mb-3">
            <div class="col-md-2 ms-auto">
                <select class="form-select js-example-basic-single" id="year-select">
                    @for ($year = now()->year; $year >= now()->year - 5; $year--)
                        <option value="{{ $year }}" {{ request('year', now()->year) == $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endfor
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 col-sm-4">
                <div class="card">
                    <div class="card-header card-no-border pb-0 d-block">
                        <div class="d-flex justify-content-between align-items-center ">
                            <h4>{{ $currentMonthName }} Order</h4>
                            <h2><i class="icofont icofont-tasks"></i></h2>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive custom-scrollbar deliveries-percentage">
                            <table class="percentage-data w-100">
                                <thead>

                                </thead>
                                <tbody>
                                    <td class="f-w-400 f-10">
                                        <span>{{ $totalOrders }} Data</span>
                                    </td>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>


            <div class="col-md-4 col-sm-4">
                <div class="card">
                    <div class="card-header card-no-border pb-0 d-block">
                        <div class="d-flex justify-content-between align-items-center ">
                            <h4>Average Order</h4>
                            <h2><i class="icofont icofont-truck-loaded"></i></h2>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive custom-scrollbar deliveries-percentage">
                            <table class="percentage-data w-100">
                                <thead>

                                </thead>
                                <tbody>
                                    <td class="f-w-400 f-10">
                                        <span>{{ round($totalOrders) }} Data</span>
                                    </td>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4 col-sm-4">
                <div class="card">
                    <div class="card-header card-no-border pb-0 d-block">
                        <div class="d-flex justify-content-between align-items-center ">
                            <h4>Jan S/d Des Order</h4>
                            <h2><i class="icofont icofont-ui-calendar"></i></h2>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive custom-scrollbar deliveries-percentage">
                            <table class="percentage-data w-100">
                                <thead>
                                </thead>
                                <tbody>
                                    <td class="f-w-400 f-10">
                                        <span>{{ $totalOrders }} Data</span>
                                    </td>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="row">
            <div class="col-md-6 col-sm-4">
                <div class="card">
                    <div class="card-header card-no-border pb-0 d-block">
                        <div class="d-flex justify-content-between align-items-center ">
                            <h4>Total Order</h4>
                            <h4>{{ $totalOrders }}</h4>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive custom-scrollbar deliveries-percentage">
                            <table class="percentage-data w-100">
                                <thead>
                                    <tr>
                                        <th class="f-light f-12 f-w-500" scope="col">Order Status</th>
                                        <th class="f-light f-12 f-w-500 text-end" scope="col">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($ordersByStatus as $item)
                                        <tr>
                                            <td class="f-w-400 f-10">{{ $item->name }}</td>
                                            <td class="f-w-500 f-10 text-end">{{ $item->total }}</td>
                                        </tr>
                                    @endforeach

                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-md-6 col-sm-6">
                <div class="card">
                    <div class="card-header">

                        <div class="d-flex justify-content-between align-items-center ">
                            <h4>Yearly Report Order </h4>
                            <h4>{{ $currentYear }}</h4>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="chart-order"></div>
                    </div>
                </div>
            </div>


        </div>
    </div>

    <div class="col-xl-12 col-md-12">

        <div class="row">
            <div class="col-md-4 col-sm-4">
                <div class="card">
                    <div class="card-header card-no-border pb-0 d-block">
                        <div class="d-flex justify-content-between align-items-center ">
                            <h4>{{ $currentMonthName }} Purchase</h4>
                            <h2><i class="icofont icofont-tasks"></i></h2>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive custom-scrollbar deliveries-percentage">
                            <table class="percentage-data w-100">
                                <thead>

                                </thead>
                                <tbody>
                                    <td class="f-w-400 f-10">
                                        <span>{{ $totalPurchases }} Data</span>
                                    </td>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>


            <div class="col-md-4 col-sm-4">
                <div class="card">
                    <div class="card-header card-no-border pb-0 d-block">
                        <div class="d-flex justify-content-between align-items-center ">
                            <h4>Average Purchase</h4>
                            <h2> <i class="icofont icofont-list"></i></h2>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive custom-scrollbar deliveries-percentage">
                            <table class="percentage-data w-100">
                                <thead>

                                </thead>
                                <tbody>
                                    <td class="f-w-400 f-10">
                                        <span>{{ round($totalPurchases) }} Data</span>
                                    </td>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4 col-sm-4">
                <div class="card">
                    <div class="card-header card-no-border pb-0 d-block">
                        <div class="d-flex justify-content-between align-items-center ">
                            <h4>Jan S/d Des Purchase</h4>
                            <h2><i class="icofont icofont-ui-calendar"></i></h2>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive custom-scrollbar deliveries-percentage">
                            <table class="percentage-data w-100">
                                <thead>
                                </thead>
                                <tbody>
                                    <td class="f-w-400 f-10">
                                        <span>{{ $totalPurchases }} Data</span>
                                    </td>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="row">


            <div class="col-md-6 col-sm-4">
                <div class="card">
                    <div class="card-header card-no-border pb-0 d-block">
                        <div class="d-flex justify-content-between align-items-center ">
                            <h4>Total Purchase</h4>
                            <h4>{{ $totalPurchases }}</h4>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive custom-scrollbar deliveries-percentage">
                            <table class="percentage-data w-100">
                                <thead>
                                    <tr>
                                        <th class="f-light f-12 f-w-500" scope="col">Purchase Status</th>
                                        <th class="f-light f-12 f-w-500 text-end" scope="col">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($purchaseByStatus as $item)
                                        <tr>
                                            <td class="f-w-400 f-10">{{ $item->name }}</td>
                                            <td class="f-w-500 f-10 text-end">{{ $item->total }}</td>
                                        </tr>
                                    @endforeach

                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-md-6 col-sm-6">
                <div class="card">
                    <div class="card-header">

                        <div class="d-flex justify-content-between align-items-center ">
                            <h4>Yearly Report Purchase </h4>
                            <h4>{{ $currentYear }}</h4>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="chart-purchase"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Data Maintenance --}}
    <div class="col-xl-12 col-md-12">

        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Maintenance Data</h4>

                    <div class="d-flex align-items-center gap-3">
                        <div class="accordion-item ">

                            <button class=" collapsed btn btn-light active" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo"><i
                                    class="icofont icofont-search-alt-1"></i></i>
                            </button>

                            <a href="{{ route($view . 'pdf-fleet-maintenance') }}" target="_blank" id="print-pdf"
                                class="btn btn-danger"> <i class="icofont icofont-file-pdf"></i> </a>

                            <a href="{{ route($view . 'excel-fleet-maintenance') }}" class="btn btn-success"
                                id="export-data">
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
                                <div class="row mt-4">
                                    <div class="col-md-6">
                                        <label class="form-label" for="name">Plate Number</label>
                                        <select class="js-example-basic-single" name="plateNumber" id="plateNumber">
                                            <option selected="" value="">Choose...</option>
                                            @foreach ($fleet as $item)
                                                <option value="{{ $item->code }}">
                                                    {{ $item->plateNumber }}</option>
                                            @endforeach
                                        </select>
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
                        <table class="display" id="dt-maintenance">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Fleet Number</th>
                                    <th>Total Maintenance</th>
                                    <th>Total Price</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>


    </div>

    {{-- Data Truck Order Status --}}
    <div class="col-xl-12 col-md-12">

        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Truck Order Status Data</h4>
                </div>

                <div class="card-body">
                    @include('partials.alert')
                    <div class="table-responsive custom-scrollbar">
                        <table class="display" id="dt-truck-order">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Truck Plate Number</th>
                                    <th>Truck Type</th>
                                    <th>Driver Name</th>
                                    <th>Shipment Assign</th>
                                    <th>Truck Status</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>


    </div>
@endsection


@push('script')
    <script src="{{ asset('assets/js/datatable/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/js/chart/apex-chart/apex-chart.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src=" {{ asset('assets/js/select2/select2-custom.js') }}"></script>
    {{-- <script src="{{ asset('assets/js/chart/apex-chart/stock-prices.js') }}"></script> --}}
    {{-- <script src="{{ asset('assets/js/chart/apex-chart/chart-custom.js') }}"></script> --}}
    {{-- <script src="../assets/js/chart/apex-chart/chart-custom.js"></script> --}}

    <script src="{{ asset('assets/js/chart/apex-chart/moment.min.js') }}"></script>
    <script src="{{ asset('assets/js/chart/echart/data/symbols.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/custom-flatpickr.js') }}"></script>
    {{-- <script src="{{ asset('assets/js/dashboard/dashboard_3.js') }}"></script> --}}

    <script>
        $(document).ready(function() {
            const maintenanceTable = $('#dt-maintenance').DataTable({
                "processing": true,
                "serverSide": true,
                "destroy": true,
                "ajax": {
                    "url": "{{ route('dt.dashboard-maintenance') }}",
                    "data": function(d) {
                        d.plateNumber = $('select[name="plateNumber"]').val();
                        d.startDate = $('input[name="startDate"]').val();
                        d.endDate = $('input[name="endDate"]').val();
                    }
                },
                "columns": [{
                        "data": 'DT_RowIndex'
                    },
                    {
                        "data": 'plateNumber'
                    },
                    {
                        "data": 'total'
                    },
                    {
                        "data": 'price'
                    }
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
                    [0, 'asc']
                ]
            })

            const truckOrderTable = $('#dt-truck-order').DataTable({
                "processing": true,
                "serverSide": true,
                "destroy": true,
                "ajax": {
                    "url": "{{ route('dt.dashboard-truck-order') }}",
                    "data": function(d) {
                        d.plateNumber = $('select[name="plateNumber"]').val();
                        d.startDate = $('input[name="startDate"]').val();
                        d.endDate = $('input[name="endDate"]').val();
                    }
                },
                "columns": [{
                        "data": 'DT_RowIndex'
                    },
                    {
                        "data": "plateNumber"
                    },
                    {
                        "data": "fleetTypeName"
                    },
                    {
                        "data": "driverName"
                    },
                    {
                        "data": "shipmentNumber"
                    },
                    {
                        "data": "status"
                    }
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
                    [0, 'asc']
                ]
            })

            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                let queryParams = $(this).serialize();

                let exportUrl = "{{ route($view . 'excel-fleet-maintenance') }}?" + queryParams;

                $('#export-data').attr('href', exportUrl);

                const printPdf = "{{ route($view . 'pdf-fleet-maintenance') }}?" + queryParams;

                $('#print-pdf').attr('href', printPdf);


                maintenanceTable.ajax.reload();
            });
        });

        function deleteData(uuid) {
            var url = '{{ route('bank.bank-account.index') }}/' + uuid;
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

    <script>
        const chartOrder = {
            chart: {
                height: 350,
                type: "bar",
                toolbar: {
                    show: false,
                },
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    endingShape: "rounded",
                    columnWidth: "55%",
                },
            },
            dataLabels: {
                enabled: false,
            },
            stroke: {
                show: true,
                width: 2,
                colors: ["transparent"],
            },
            series: [{
                    name: "Total Orders",
                    data: @json($monthlyOrders),
                },

            ],
            xaxis: {
                categories: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Des"],
            },
            yaxis: {

            },
            fill: {
                opacity: 1,
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return val + " Orders";
                    },
                },
            },
            colors: [RihoAdminConfig.primary],
        };

        const chartPurchase = {
            chart: {
                height: 350,
                type: "bar",
                toolbar: {
                    show: false,
                },
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    endingShape: "rounded",
                    columnWidth: "55%",
                },
            },
            dataLabels: {
                enabled: false,
            },
            stroke: {
                show: true,
                width: 2,
                colors: ["transparent"],
            },
            series: [{
                    name: "Total Purchases",
                    data: @json($monthlyPurchases),
                },

            ],
            xaxis: {
                categories: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Des"],
            },
            yaxis: {

            },
            fill: {
                opacity: 1,
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return val + " Orders";
                    },
                },
            },
            colors: [RihoAdminConfig.secondary],
        };

        const chartOrderView = new ApexCharts(document.querySelector("#chart-order"), chartOrder);
        const chartPurchaseView = new ApexCharts(document.querySelector("#chart-purchase"), chartPurchase);

        chartOrderView.render();
        chartPurchaseView.render();
    </script>

    <script>
        $('#year-select').on('change', function() {
            const selectedYear = $(this).val();
            window.location.href = '{{ route('dashboard') }}?year=' + selectedYear;
        });
    </script>
@endpush
