@extends('layouts.main', [
'title' => $title,
'pageTitle' => $title,
'firstSegment' => $title,
'secondSegment' => __('general.add'),
])

@php
use App\Models\Data\Route;
use App\Models\Data\TonaseBonus;
use Carbon\Carbon;
@endphp


@push('style')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">
<link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/select2.css') }}">

<link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/custom-select2.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/sweetalert2.css') }}">
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
<div class="col-sm-12">
    @include('partials.alert')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>{{ $title }} {{ __('general.edit_data') }}</h4>
            <a href="{{ route($view . 'index') }}" class="btn btn-info">{{ __('general.back_to_list') }}</a>
        </div>

        <div class="card-body col-md-12">
            <form class="row g-3 mt-1" method="post" action="{{ route($view . 'update', $data->id) }}">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 position-relative">
                        <label class="form-label" for="customerCode">
                            {{ __('menu_invoice.customer_name') }} <i class="mdi mdi-information text-danger"></i>
                        </label>
                        <select class="js-example-basic-single" name="customerCode" id="customerCode" required disabled>
                            <option selected disabled value="">{{ __('general.choose') }}...</option>
                            @foreach ($customer as $item)
                            <option value="{{ $item->code }}"
                                {{ $data->customerCode == $item->code ? 'selected' : '' }}>
                                {{ $item->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="invoiceNumber">
                            {{ __('menu_invoice.invoice_number') }} <i class="mdi mdi-information text-danger"></i>
                        </label>
                        <input class="form-control" name="invoiceNumber" id="invoiceNumber" type="text" required
                            placeholder="{{ __('menu_invoice.invoice_number') }}" value="{{ $data->invoiceNumber }}"
                            readonly>
                    </div>
                </div>

                {{-- <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="receiptNumber">
                                {{ __('menu_invoice.receipt_number') }} <i class="mdi mdi-information text-danger"></i>
                </label>
                <input class="form-control" name="receiptNumber" id="receiptNumber" type="text" required
                    placeholder="{{ __('menu_invoice.receipt_number') }}" value="{{ $data->receiptNumber }}">
        </div>

        <div class="col-md-6">
            <label class="form-label" for="poNumber">{{ __('menu_invoice.po_number') }}</label>
            <input class="form-control" name="poNumber" id="poNumber" type="text"
                placeholder="{{ __('menu_invoice.po_number') }}" value="{{ $data->poNumber }}">
        </div>
    </div> --}}

    <div class="row mt-4">
        <div class="col-md-6">
            <label class="form-label" for="invoiceDate">
                {{ __('menu_invoice.invoice_date') }} <i class="mdi mdi-information text-danger"></i>
            </label>
            <input class="form-control" name="invoiceDate" id="invoiceDate" type="date" required
                placeholder="{{ __('menu_invoice.invoice_date') }}" value="{{ $data->invoiceDate }}">
        </div>

        <div class="col-md-6">
            <label class="form-label" for="overdueDate">{{ __('menu_invoice.overdue_date') }}</label>
            <input class="form-control" name="overdueDate" id="overdueDate" type="date" readonly
                placeholder="{{ __('menu_invoice.overdue_date') }}" value="{{ $data->overdueDate }}">
        </div>
    </div>
    <div class="row mt-2">
        <div class="col-md-6">
            <div class="form-check form-switch">
                <input type="hidden" name="usePpn" value="0">
                <input class="form-check-input" type="checkbox" role="switch" id="usePpn" name="usePpn"
                    value="1" {{ $data->usePpn ? 'checked' : '' }}>
                <label class="form-check-label" for="usePpn">{{ __('menu_invoice.use_ppn') }}</label>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <hr>
    </div>

    {{-- <div class="row">
                        <div class="col-md-6">
                            <label class="form-label" for="picName">{{ __('menu_invoice.to_pic_name') }}</label>
    <input class="form-control" name="picName" id="picName" type="text"
        value="{{ $customerData->picName }}" readonly
        placeholder="{{ __('menu_invoice.to_pic_name') }}">
</div>

<div class="col-md-6">
    <label class="form-label" for="picPhone">{{ __('menu_invoice.to_pic_phone_number') }}</label>
    <input class="form-control" name="picPhone" id="picPhone" type="text"
        value="{{ $data->phone }}" readonly
        placeholder="{{ __('menu_invoice.to_pic_phone_number') }}">
</div>
</div> --}}

<div class="row">
    {{--
          <div class="col-md-6">
            <label class="form-label" for="invoiceAddress">{{ __('menu_invoice.billing_address') }}</label>
    <textarea class="form-control" placeholder="{{ __('menu_invoice.billing_address') }}" rows="4" disabled readonly>
    {{ $data->customer->billingAddress }}
    </textarea>
</div>
--}}

<div class="col-md-6">
    <label class="form-label" for="notes">{{ __('menu_invoice.notes') }}</label>
    <textarea class="form-control" name="notes" id="notes" placeholder="{{ __('menu_invoice.notes') }}"
        rows="4">{{ $data->notes }}</textarea>
</div>
</div>

<div class="col-12">
    <button class="btn btn-primary" type="submit">{{ __('menu_invoice.edit') }}</button>
</div>
</form>
</div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Summary</h4>

        <button type="button" class="btn btn-warning btn-sm" onclick="recalculateInvoice()" title="Recalculate invoice amount based on current order data">
            <i class="mdi mdi-calculator"></i> Hitung Ulang
        </button>
    </div>

    <div class="card-body col-md-12">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">{{ __('menu_invoice.price') }}</label>
                <input type="text" class="form-control" readonly value="Rp {{ number_format($data->invoiceAmount, 0, ',', '.') }}">
            </div>

            <div class="col-md-6">
                <label class="form-label">{{ __('menu_invoice.ppn') }}</label>
                <input type="text" class="form-control" readonly value="Rp {{ number_format($data->ppnAmount, 0, ',', '.') }}">
            </div>

            <div class="col-md-6">
                <label class="form-label"><strong>Total Tagihan</strong></label>
                <input type="text" class="form-control" readonly value="Rp {{ number_format($data->invoiceAmount + $data->ppnAmount, 0, ',', '.') }}">
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>{{ __('menu_invoice.order_data_title') }}</h4>

        <div class="d-flex gap-5">
            <a target="_blank" href="{{ route($view . 'pdf-invoice', $data->id) }}"
                class="btn btn-icon btn-sm bg-danger-subtle"><i class="mdi mdi-file fs-14 text-danger"></i></a>

            @if ($status == 0)
            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                data-bs-target=".bd-example-modal-xl" id="openModalButton">
                {{ __('general.add_data') }}
            </button>
            @endif
        </div>
    </div>

    {{-- usePpn toggle moved into form near overdue date --}}

    <div class="card-body col-md-12">
        <table class="table table-striped w-100 nowrap" id="dt-order">
            <thead>
                <tr>
                    <th>{{ __('menu_invoice.hash') }}</th>
                    <th>{{ __('menu_invoice.no') }}</th>
                    {{-- <th>{{ __('menu_invoice.shipment_no') }}</th> --}}
                    <th>{{ __('menu_invoice.order_date') }}</th>
                    <th>{{ __('menu_invoice.origin') }}</th>
                    <th>{{ __('menu_invoice.destination') }}</th>
                    <th>{{ __('menu_invoice.shipment_no') }}</th>
                    <th>{{ __('menu_invoice.plate_no') }}</th>
                    {{-- <th>{{ __('menu_invoice.total_cost') }}</th> --}}
                </tr>
            </thead>
            <tbody>
                @php
                $totalAllowance = 0;
                @endphp
                @foreach ($order as $item)
                <tr>
                    <td>
                        @if ($status == 0)
                        <a href="javascript:deleteInvoiceDetail('{{ $item->id }}')"
                            class="btn btn-icon btn-sm bg-danger-subtle" data-bs-toggle="tooltip"
                            title="Delete">
                            <i class="mdi mdi-delete fs-14 text-danger"></i>
                        </a>
                        @endif

                    </td>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ Carbon::parse($item->orderDate)->format('d-M-Y') }}</td>
                    <td>{{ $item->route->originLocation->name ?? '' }}</td>
                    <td>{{ $item->route->destinationLocation->name ?? '' }}</td>
                    <td>{{ $item->shipmentNumber ?? '' }}</td>
                    <td>{{ $item->fleet->plateNumber ?? '' }}</td>
                    {{-- <td>{{ $item->orderType->name }}</td> --}}
                </tr>
                @endforeach
                {{-- <tr>
                                <td colspan="7" class="fw-bold text-start h5">Total:</td>
                                <td>Rp {{ number_format($totalAllowance, 0, ',', '.') }}</td>
                </tr> --}}
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
    aria-hidden="true">
    <form method="post" action="{{ route('finance.invoice-detail.store', $data->id) }}">
        @csrf
        @method('PUT')
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">{{ __('menu_invoice.order_data_title') }}</h4>
                    <button class="btn-close py-0" type="button" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <div class="card">
                    <div class="card-body col-md-12">
                        <div class="row g-3">
                            <table class="table table-striped w-100 nowrap" id="dt">
                                <thead>
                                    <tr>
                                        <th>{{ __('menu_invoice.hash') }}</th>
                                        <th>{{ __('menu_invoice.no') }}</th>
                                        <th>{{ __('menu_invoice.order_date') }}</th>
                                        <th>{{ __('menu_invoice.origin') }}</th>
                                        <th>{{ __('menu_invoice.destination') }}</th>
                                        <th>{{ __('menu_invoice.shipment_no') }}</th>
                                        <th>{{ __('menu_invoice.plate_no') }}</th>
                                        <th>{{ __('menu_invoice.total_cost') }}</th>
                                        {{-- <th>{{ __('menu_invoice.fleet_type') }}</th> --}}
                                        {{-- <th>{{ __('menu_invoice.order_type') }}</th> --}}
                                        {{-- <th>{{ __('menu_invoice.allowance') }}</th> --}}
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="modal-footer justify-content-start">
                    <button type="submit" id="saveInvoice"
                        class="btn btn-primary">{{ __('menu_invoice.save') }}</button>
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

<form id="recalculate-form" method="post" action="{{ route($view . 'recalculate', $data->id) }}">
    @csrf
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
<script src="{{ asset('assets/js/flat-pickr/flatpickr.js') }}"></script>
<script src="{{ asset('assets/js/flat-pickr/custom-flatpickr.js') }}"></script>
<script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
<script src=" {{ asset('assets/js/select2/select2-custom.js') }}"></script>
<script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>
<script src=" {{ asset('assets/js/helper.js') }}"></script>

<script>
    $(document).ready(function() {
        $('input[name="invoiceDate"]').on('change', function() {
            const customerCode = $('#customerCode').select2('val');

            if (customerCode) {
                $.get("{{ url('ajax/customer-invoice') }}/" + customerCode, function(data) {
                    let invoiceDate = new Date($("input[name='invoiceDate']").val());
                    if (!isNaN(invoiceDate.getTime())) {
                        let overdueDate = new Date(invoiceDate);
                        overdueDate.setDate(overdueDate.getDate() + data.dueDateDuration);

                        let formattedDate = overdueDate.toISOString().split('T')[0];
                        $("input[name='overdueDate']").val(formattedDate);
                    }
                });
            }
        });

        // Inisialisasi saat halaman dimuat
        if ($("input[name='invoiceDate']").val()) {
            $("input[name='invoiceDate']").trigger("change");
        }
    });
</script>

<script>
    let selectedOrders = [];

    document.getElementById('saveInvoice').addEventListener('click', function(event) {
        // Get all checkboxes
        if (selectedOrders.length === 0) {
            event.preventDefault();
            swal({
                title: "{{ __('general.warning') }}",
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
                "url": "{{ route('dt.invoice-order') }}",
                "data": function(d) {
                    d.customerCode = $('select[name="customerCode"]').val();
                }
            },
            "columns": [{
                    "data": 'action'
                },
                {
                    "data": 'DT_RowIndex'
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
                    "data": 'shipmentNumber'
                },
                {
                    "data": 'fleet.plateNumber'
                },
                // {
                //     "data": 'fleet.type.name'
                // },
                // {
                //     "data": 'orderType.name'
                // },
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

    function deleteInvoiceDetail(id) {
        var url = "{{ route('finance.invoice-detail.destroy', ':id') }}";
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

    function recalculateInvoice() {
        swal({
            title: "{{ __('general.are_you_sure') }}",
            text: "Proses ini akan membatalkan SEMUA pembayaran untuk invoice ini dan mengembalikan status ke CREATE. Anda harus input ulang pembayaran invoice.",
            icon: "warning",
            buttons: {
                cancel: "Batal",
                confirm: "Ya, Hitung Ulang"
            },
            dangerMode: true,
        }).then((willRecalculate) => {
            if (willRecalculate) {
                document.getElementById('recalculate-form').submit();
            }
        });
    }
</script>
@endpush