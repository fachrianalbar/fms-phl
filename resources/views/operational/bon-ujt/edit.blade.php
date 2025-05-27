@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => $title,
    'secondSegment' => __('general.edit'),
])

@php
    use App\Models\Data\Route;
    use App\Models\Data\TonaseBonus;
    use Carbon\Carbon;
@endphp

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
    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/select2.css') }}">

    <link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/custom-select2.css') }}">
@endpush

@section('content')
    <div class="col-sm-12">
        @include('partials.alert')
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }} {{ __('general.edit_data') }}</h4>

                <a href="{{ route($view . 'index') }}" class="btn btn-info">{{ __('general.back_to_list') }}</a>

            </div>
            <div class="card-body col-md-12">
                <form class="row g-3" method="post" action="{{ route($view . 'update', $data->id) }}"
                    onsubmit="return submitForm('note')">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label" for="name">Code <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <input class="form-control" type="text" required placeholder="Name" readonly disabled
                                value="{{ $data->code }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label" for="name">Date <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <input class="form-control" name="date" id="datetime-local" type="date" required
                                placeholder="Order Date" value="{{ $data->date }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Time</label>
                            <input class="form-control digits" name="time" type="time" value="{{ $data->time }}">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="name">Date Submitted <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <input class="form-control" name="submitDate" id="datetime-local" type="date" required
                                placeholder="Order Date" value="{{ $data->submitDate }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="handover">Serah Terima Oleh <i
                                    class="mdi mdi-information text-danger"></i></label>

                            <input type="hidden" name="fleetTypeCode" id="fleetTypeCode"
                                value="{{ $data->fleetTypeCode }}" readonly>
                            <select class="js-example-basic-single" required="" disabled>
                                <option selected="" disabled="" value="">{{ __('general.choose') }}...
                                </option>
                                @foreach ($fleetType as $item)
                                    <option value="{{ $item->code }}"
                                        {{ $data->fleetTypeCode == $item->code ? 'selected' : '' }}>
                                        {{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="bon">Nomor Bon <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <input class="form-control" name="bon" id="bon" type="text" required
                                placeholder="Nomor Bon" value="{{ $data->bon }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="note">Catatan</label>
                            <input class="form-control" name="note" id="note" type="text"
                                placeholder="Catatan" oninput="formatAngka(this)" value="{{ $data->note }}">
                        </div>
                    </div>

                    <div class="col-12">
                        <button class="btn btn-primary" type="submit">Edit</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Order Data</h4>

                <div class="d-flex gap-5">
                    <a target="_blank" href="{{ route($view . 'pdf-bon-ujt', $data->id) }}" class="btn btn-danger"><i
                            class="icofont icofont-file-pdf"></i></a>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target=".bd-example-modal-xl" id="openModalButton">{{ __('general.add_data') }}</button>
                </div>
            </div>
            <div class="card-body col-md-12">
                <table class="table table-bordered dt-responsive table-responsive nowrap" id="dt-order">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>No</th>
                            {{-- <th>Shipment No</th> --}}
                            <th>Order Date</th>
                            <th>Origin</th>
                            <th>Destination</th>
                            <th>Shipment No</th>
                            <th>Plate No</th>
                            <th>Order Type</th>
                            <th>Allowance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalAllowance = 0;
                        @endphp
                        @foreach ($order as $item)
                            <tr>
                                <td>
                                    <ul class="action">
                                        <li class="delete"><a
                                                href="javascript:deleteBonUjtDetail('{{ $item->id }}')"><i
                                                    class="icon-trash"></i></a>
                                        </li>
                                    </ul>
                                </td>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ Carbon::parse($item->orderDate)->format('d-M-Y') }}</td>
                                <td>{{ $item->route->originLocation->name }}</td>
                                <td>{{ $item->route->destinationLocation->name }}</td>
                                <td>{{ $item->shipmentNumber }}</td>
                                <td>{{ $item->fleet->plateNumber }}</td>
                                <td>{{ $item->orderType->name }}</td>
                                <td>
                                    @php
                                        $details = $item->route->routeDetail;

                                        $price = 0;
                                        foreach ($details as $detail) {
                                            if ($detail->costComponent->type == 'Allowance') {
                                                if ($detail->amount != 0) {
                                                    $price += $detail->amount;
                                                }

                                                if ($detail->percentage) {
                                                    $route = Route::where('code', $detail->routeCode)->first();

                                                    $price = $route->price * ($detail->percentage / 100);
                                                }
                                            }
                                        }
                                        $totalAllowance += $price;
                                        $bonus = TonaseBonus::where('min', '<=', $item->qty)
                                            ->where('max', '>=', $item->qty)
                                            ->first();

                                        if ($bonus) {
                                            $price += $bonus->value;
                                            $totalAllowance += $bonus->value;
                                        }

                                        $cost = 0;
                                        if (isset($item->cost)) {
                                            foreach ($item->cost as $itCost) {
                                                $cost += $itCost->nominal;
                                            }
                                        }
                                        $price += $cost;
                                        $totalAllowance += $cost;
                                        $allowance = 'Rp ' . number_format($price, 0, ',', '.');
                                    @endphp
                                    {{ $allowance }}
                                </td>


                            </tr>
                        @endforeach
                        {{-- <tr>
                            <td colspan="7" class="fw-bold text-start h5">Total:</td>
                            <td>Rp {{ number_format($totalAllowance, 0, ',', '.') }}</td>
                        </tr> --}}
                    </tbody>
                </table>
                <p class="h5">Total Allowance: Rp {{ number_format($totalAllowance, 0, ',', '.') }}</p>
            </div>
        </div>


        <div class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
            aria-hidden="true">
            <form method="post" action="{{ route('operational.bon-ujt-detail.store', $data->id) }}">
                @csrf
                @method('PUT')
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Data Order</h4>
                            <button class="btn-close py-0" type="button" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="card">
                            <div class="card-body col-md-12">
                                <div class="row g-3">
                                    <table class="table table-bordered dt-responsive table-responsive nowrap"
                                        id="dt">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>No</th>
                                                <th>Shipment No</th>
                                                <th>Order Date</th>
                                                <th>Origin</th>
                                                <th>Destination</th>
                                                <th>Plate No</th>
                                                <th>Fleet Type</th>
                                                <th>Order Type</th>
                                                {{-- <th>Allowance</th> --}}
                                                {{-- <th>Qty</th>
                                                <th>Cost</th>
                                                <th>Tonase</th>
                                                <th>Add Cost</th> --}}
                                                <th>Total Cost</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer justify-content-start">
                            <button type="submit" id="saveBonUjt" class="btn btn-primary">Save</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <form id="delete-form" method="post">
        @csrf
        @method('DELETE')
    </form>
@endsection


@push('script')
    <script src="{{ asset('assets/js/flat-pickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/custom-flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src=" {{ asset('assets/js/select2/select2-custom.js') }}"></script>
    <script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>
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
    <script src=" {{ asset('assets/js/helper.js') }}"></script>
    <script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>


    <script>
        let selectedOrders = [];

        document.getElementById('saveBonUjt').addEventListener('click', function(event) {
            // Get all checkboxes
            if (selectedOrders.length === 0) {
                event.preventDefault();
                swal({
                    title: "Warning",
                    text: "Please select at least one item",
                    icon: "warning",
                });
                return;
            }

            // Tambahkan array ke form
            $('<input>').attr({
                type: 'hidden',
                name: 'selectedOrders',
                value: JSON.stringify(selectedOrders)
            }).appendTo('form');
        });
        $(document).ready(function() {

            // Event handler untuk checkbox
            $(document).on('change', '.order-checkbox', function() {
                const orderId = $(this).val();
                if ($(this).is(':checked')) {
                    if (!selectedOrders.includes(orderId)) {
                        selectedOrders.push(orderId);
                    }
                } else {
                    selectedOrders = selectedOrders.filter(id => id !== orderId);
                }

            });

            // Simpan state saat DataTable di-reload (misalnya saat pindah halaman)
            $('#dt').on('draw.dt', function() {
                $('.order-checkbox').each(function() {
                    const orderId = $(this).val();
                    if (selectedOrders.includes(orderId)) {
                        $(this).prop('checked', true);
                    }
                });
            });

            const table = $('#dt').DataTable({
                "processing": true,
                "serverSide": true,
                "destroy": true,
                "ajax": {
                    "url": "{{ route('dt.bon-ujt-order') }}",
                    "data": function(d) {
                        d.fleetTypeCode = $('input[name="fleetTypeCode"]').val();
                    }
                },
                "columns": [{
                        "data": 'action'
                    },
                    {
                        "data": 'DT_RowIndex'
                    },
                    {
                        "data": 'shipmentNumber'
                    },
                    {
                        "data": 'orderDate'
                    },
                    {
                        "data": 'route.originLocation.name'
                    },
                    {
                        "data": 'route.destinationLocation.name'
                    },
                    {
                        "data": 'fleet.plateNumber'
                    },
                    {
                        "data": 'fleet.type.name'
                    },
                    {
                        "data": 'orderType.name'
                    },
                    // {
                    //     "data": 'allowance'
                    // },
                    // {
                    //     "data": 'qty'
                    // },
                    // {
                    //     "data": 'cost',
                    // },
                    // {
                    //     "data": 'bonus'
                    // },
                    // {
                    //     "data": 'addCost'
                    // },
                    {
                        "data": 'totalPrice'
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
                    [3, 'desc']
                ]
            })

            $('#openModalButton').click(function() {
                table.ajax.reload();
            });
        });

        $('#dt-order').DataTable()

        function deleteBonUjtDetail(id) {
            var url = '{{ route('operational.bon-ujt-detail.destroy', ':id') }}'; // Use placeholder ':id'
            url = url.replace(':id', id); // Replace the placeholder with actual id

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
