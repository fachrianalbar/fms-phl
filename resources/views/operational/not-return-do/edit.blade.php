@extends('layouts.main', [
'title' => $title,
'pageTitle' => $title,
'firstSegment' => $title,
'secondSegment' => __('general.edit'),
])

@php
use App\Models\Data\Route;
@endphp

@push('style')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">
<link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/select2.css') }}">
<link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/custom-select2.css') }}">
<link rel="stylesheet" type="text/css" href=" {{ asset('assets/css/vendors/sweetalert2.css') }} ">
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

<style>
    #dt {
        border-spacing: 0 15px !important;
        border-collapse: separate !important;
    }
</style>
@endpush

@section('content')
<form class="row g-3" method="post" action="{{ route('operational.not-return-do.update', $data->code) }}"
    id="confirm-form" onsubmit="return submitForm('routeAmount')">
    @csrf
    @method('PUT')
    <div class="col-sm-12">
        <input type="hidden" name="not_return_do" value=true>
        @include('partials.alert')

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }} Data</h4>
                <div class="d-flex gap-2">
                    {{-- <button type="button" class="btn btn-success" id="confirmOrderBtn">
                            <i class="mdi mdi-check"></i> Konfirmasi Return
                        </button> --}}
                    <a href="{{ route('operational.not-return-do.index') }}" class="btn btn-info">
                        {{ __('general.back_to_list') }}
                    </a>
                </div>
            </div>

            <div class="card-body col-md-12">
                <div class="row g-3">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label" for="name">{{ __('menu_order.order_code') }} <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <input type="hidden" name="code" value="{{ $data->code }}">
                            <input class="form-control" type="text" required readonly disabled
                                value="{{ $data->code }}">
                        </div>

                        <div class="col-md-6 position-relative">
                            <label class="form-label" for="fleetCode">{{ __('menu_order.plate_number') }}<i
                                    class="mdi mdi-information text-danger"></i></label>

                            <select class="js-example-basic-single" name="fleetCode" id="fleetCode" required="">
                                <option selected="" disabled="" value="">{{ __('general.choose') }}...
                                </option>
                                @foreach ($fleet as $item)
                                <option value="{{ $item->code }}"
                                    {{ $data->fleetCode == $item->code ? 'selected' : '' }}>
                                    {{ $item->plateNumber }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="name">{{ __('menu_order.order_date') }} <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <input class="form-control" name="orderDate" id="datetime-local" type="date" required
                                placeholder="{{ __('menu_order.order_date') }}" value="{{ $data->orderDate }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="shipmentNumber">{{ __('menu_order.shipment_no') }} <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <input class="form-control" name="shipmentNumber" id="shipmentNumber" type="text"
                                readonly required placeholder="{{ __('menu_order.shipment_no') }}"
                                value="{{ $data->shipmentNumber }}">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6 position-relative">
                            <label class="form-label" for="driverCode">{{ __('menu_order.driver') }} <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <select class="js-example-basic-single" name="driverCode" id="driverCode" required="">
                                <option selected="" disabled="" value="">{{ __('general.choose') }}...
                                </option>
                                @foreach ($driver as $item)
                                <option value="{{ $item->code }}"
                                    {{ $data->driverCode == $item->code ? 'selected' : '' }}>
                                    {{ $item->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="notes">{{ __('menu_order.notes') }}</label>
                            <input class="form-control" name="notes" id="notes" type="text"
                                placeholder="{{ __('menu_order.notes') }}" value="{{ $data->notes }}">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6 position-relative">
                            <label class="form-label" for="customerCode">{{ __('menu_order.customer') }} <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <select class="js-example-basic-single" name="customerCode" id="customerCode"
                                required="">
                                <option selected="" disabled="" value="">
                                    {{ __('general.choose') }}...
                                </option>
                                @foreach ($customer as $item)
                                <option value="{{ $item->code }}" data-id="{{ $item->id }}"
                                    {{ $data->customerCode == $item->code ? 'selected' : '' }}>
                                    {{ $item->code . ' - ' . $item->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 position-relative">
                            <label class="form-label" for="routeTypeCode">{{ __('menu_order.load_type') }} <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <select class="js-example-basic-single" name="routeTypeCode" id="routeTypeCode"
                                required="">
                                <option selected="" disabled="" value="">
                                    {{ __('general.choose') }}...
                                </option>
                                @foreach ($routeType as $item)
                                <option value="{{ $item->code }}"
                                    {{ $data->route->routeTypeCode == $item->code ? 'selected' : '' }}>
                                    {{ $item->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mt-4">
                        @php
                        $label = '';
                        if ($data->route->routeTypeCode == 'TONASE') {
                        $label = 'Tonase';
                        } elseif ($data->route->routeTypeCode == 'TRIP') {
                        $label = 'Trip';
                        } else {
                        $label = 'Kubik';
                        }
                        @endphp

                        <div class="col-md-6 position-relative">
                            <label class="form-label" for="routeData">{{ __('menu_order.route') }}<i
                                    class="mdi mdi-information text-danger"></i> </label>
                            <select class="js-example-basic-single" name="routeData" id="routeData" required="">
                                <option selected="" disabled="" value="">
                                    {{ __('general.choose') }}...
                                </option>
                                @foreach ($route as $item)
                                <option value="{{ $item->code }}"
                                    {{ $item->code == $data->routeCode ? 'selected' : '' }}>
                                    {{ $item->name . ' (' . ($item->originLocation->name ?? '') . ($item->destinationLocation ? ' - ' . $item->destinationLocation->name : '') . ') - ' . $item->description }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 position-relative" id="qtyField">
                            <label class="form-label" for="qty" id="qtyLabel">{{ $label }} <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <input class="form-control" name="qty" id="qty" type="number" min="1"
                                placeholder="Enter {{ $label }}" value="{{ $data->qty }}" required>
                        </div>
                    </div>

                    @role('SPRADMIN', 'SPRUSER', 'DO')
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="routeAmount">{{ __('menu_order.route_price') }}</label>
                            <input class="form-control" name="routeAmount" id="routeAmount"
                                oninput="formatAngka(this)" type="text"
                                placeholder="{{ __('menu_order.route_price') }}"
                                value="{{ number_format($data->routeAmount, 0, ',', '.') }}">
                        </div>
                    </div>
                    @endrole

                    @unlessrole('SPRADMIN', 'SPRUSER', 'DO')
                    <input type="hidden" id="routeAmount" name="routeAmount" value="{{ $data->routeAmount }}">
                    @endunlessrole

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label" for="name">{{ __('menu_order.return_date') }} <i
                                    class="mdi mdi-information text-danger"></i></label>
                            <input class="form-control" name="returnDate" id="datetime-local" type="date"
                                required placeholder="{{ __('menu_order.return_date') }}"
                                value="{{ $data->returnDate }}">
                        </div>
                    </div>


                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Material Data</h4>
                <button class="btn btn-primary" type="button"
                    id="add-material">{{ __('general.add_data') }}</button>
            </div>

            <div class="card-body col-md-12">
                <table class="table table-sm" id="dt">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th style="width: 20%">Material</th>
                            <th style="width: 20%">Unit</th>
                            <th>Qty</th>
                            <th style="width: 20%">Unit2</th>
                            <th>Qty2</th>
                        </tr>
                    </thead>
                    <tbody id="materialForm">
                        @foreach ($data->orderMaterial as $orderMaterial)
                        <tr>
                            <td class="remove-btn">
                                <a href="javascript:deleteOrderMaterial({{ $orderMaterial->id }})"
                                    class="btn btn-icon btn-sm bg-danger-subtle" data-bs-toggle="tooltip"
                                    title="Delete">
                                    <i class="mdi mdi-delete fs-14 text-danger"></i>
                                </a>
                            </td>
                            <td>
                                <select class="form-control js-example-basic-single" name="materialCode[]"
                                    id="materialCode_{{ $loop->iteration }}">
                                    <option selected disabled value="">{{ __('general.choose') }}...
                                    </option>
                                    @foreach ($material as $item)
                                    <option value="{{ $item->code }}"
                                        {{ $orderMaterial->materialCode == $item->code ? 'selected' : '' }}>
                                        {{ $item->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select class="form-control js-example-basic-single" name="unitCode[]"
                                    id="unitCode_{{ $loop->iteration }}">
                                    <option selected disabled value="">{{ __('general.choose') }}...
                                    </option>
                                    @foreach ($unit as $item)
                                    <option value="{{ $item->code }}"
                                        {{ $orderMaterial->unitCode == $item->code ? 'selected' : '' }}>
                                        {{ $item->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input class="form-control" name="materialQty[]"
                                    id="materialQty_{{ $loop->iteration }}" type="number" min="1"
                                    placeholder="Material Qty" value="{{ $orderMaterial->materialQty }}">
                            </td>
                            <td>
                                <select class="form-control js-example-basic-single" name="unitCode2[]"
                                    id="unitCode2_{{ $loop->iteration }}">
                                    <option selected disabled value="">{{ __('general.choose') }}...
                                    </option>
                                    @foreach ($unit as $item)
                                    <option value="{{ $item->code }}"
                                        {{ $orderMaterial->unitCode2 == $item->code ? 'selected' : '' }}>
                                        {{ $item->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input class="form-control" name="materialQty2[]"
                                    id="materialQty2_{{ $loop->iteration }}" type="number" min="1"
                                    placeholder="Qty" value="{{ $orderMaterial->materialQty2 }}">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if ($customerDetailOrder->count() > 0)
        <div class="card" id="card-customer-detail">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ __('menu_order.customer_detail_data') }}</h4>
            </div>
            <div class="card-body col-md-6">
                @foreach ($customerDetailOrder as $item)
                <div class="row mt-2">
                    <div class="col-md-6">
                        <label class="form-label">{{ $item->customerDetail->name ?? '' }}</label>
                        <input type="hidden" name="customerDetailCode[]"
                            value="{{ $item->customerDetailCode }}">
                        <input class="form-control" name="value[]" type="text"
                            placeholder="{{ $item->customerDetail->name ?? '' }}"
                            value="{{ $item->value }}">
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="card">
            <div class="card-body col-md-12">
                <ul class="nav nav-tabs" id="icon-tab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active txt-success" id="icon-home-tab" data-bs-toggle="tab"
                            href="#icon-home" role="tab" aria-controls="icon-home" aria-selected="true">
                            <i class="icofont icofont-ui-home"></i>{{ __('menu_order.additional_cost') }}
                        </a>
                    </li>
                </ul>
                <div class="tab-content" id="icon-tabContent">
                    @include('operational.order.components.cost-edit-on-charge')
                </div>
            </div>
        </div>

        @if ($data->status != 4)
        <div class="card">
            <div class="col-12">
                <div class="card-body">
                    <button class="btn btn-primary" id="confirm-btn"
                        type="submit">{{ __('menu_not_return_do.confirm') }}</button>
                </div>
            </div>
        </div>
        @endif
    </div>
</form>

<form id="delete-form" method="post">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('script')
<script src=" {{ asset('assets/js/helper.js') }}"></script>
<script src="{{ asset('assets/js/flat-pickr/flatpickr.js') }}"></script>
<script src="{{ asset('assets/js/flat-pickr/custom-flatpickr.js') }}"></script>
<script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
<script src=" {{ asset('assets/js/select2/select2-custom.js') }}"></script>
<script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>
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

<script>
    $(document).ready(function() {
        const selectedType = $('#routeTypeCode').select2('val');

        $('#confirm-btn').on('click', function(e) {
            e.preventDefault();
            swal({
                title: "Apakah Anda yakin?",
                text: "Data akan disimpan dan dikonfirmasi!",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willSave) => {
                if (willSave) {
                    $('#confirm-form').submit();
                } else {
                    swal("Data Anda aman!");
                }
            });
        });
    });

    function deleteCost(id) {
        var url = '{{ route('operational.order-cost.destroy', ':id') }}';
        url = url.replace(':id', id);

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

    $('#save').click(function(e) {
        const routeTypeCode = $('#routeTypeCode').select2('val');
        if (routeTypeCode === 'TONASE') {
            const qty = $('#qty').val();
        }
    });

    function loadQty(selectedType) {
        if (selectedType === 'TONASE') {
            $('#qtyLabel').html('Tonase <i class="mdi mdi-information text-danger"></i>');
            $('#qty').attr('placeholder', 'Enter Tonase');
            $('#qty').val(1);
            $('#qty').removeAttr('readonly');
            $('#qtyField').removeClass('d-none');
        } else if (selectedType === 'TRIP') {
            $('#qtyLabel').html('Ritase <i class="mdi mdi-information text-danger"></i>');
            $('#qty').attr('placeholder', 'Enter Ritase');
            $('#qty').val(1);
            $('#qty').attr('readonly', true);
            $('#qtyField').removeClass('d-none');
        } else if (selectedType == 'KUBIKASE') {
            $('#qtyLabel').html('Kubikase <i class="mdi mdi-information text-danger"></i>');
            $('#qty').attr('placeholder', 'Enter Kubikase');
            $('#qty').val(1);
            $('#qty').removeAttr('readonly');
            $('#qtyField').removeClass('d-none');
        } else {
            $('#qtyField').addClass('d-none');
        }
    }

    $('#routeTypeCode').on('change', function() {
        $('body').append(`
            <div class="loader-wrapper">
                <div class="loader">
                    <div class="loader4"></div>
                </div>
            </div>
        `);

        const selectedType = $('#routeTypeCode').select2('val');

        setTimeout(function() {
            loadQty(selectedType)
            $('.loader-wrapper').remove();
        }, 1000);
    });

    $('#customerCode').on('change', function() {
        let customerCode = $(this).val();
        let customerId = $('#customerCode option:selected').data('id');

        $.get("/ajax/order-shipment-format/" + customerId, function(data) {
            $('#shipmentNumber').val(data);
        });

        if (customerCode) {
            // Generate new shipment number
            $.get("/operational/order-shipment-format/" + customerId, function(shipmentNumber) {
                $('#shipmentNumber').val(String(shipmentNumber).toUpperCase());
            });

            // Load customer detail
            $.get("/ajax/customer-detail/" + customerId, function(data) {
                const $detailCard = $('#card-customer-detail');
                const $cardBody = $detailCard.find('.card-body');
                $cardBody.empty();

                if (data.length > 0) {
                    $detailCard.removeClass('d-none');
                    $('#notes').prop('disabled', true);

                    data.forEach(item => {
                        let html = `
                            <div class="row mt-2">
                                /* Lines 560-565 omitted */
                            </div>`;
                        $cardBody.append(html);
                    });
                } else {
                    $detailCard.addClass('d-none');
                    $('#notes').prop('disabled', false);
                }
            });
        } else {
            $('#card-customer-detail').addClass('d-none');
            $('#card-customer-detail .card-body').empty();
            $('#notes').prop('disabled', false);
            $('#shipmentNumber').val('');
        }
    });
    $('#add-material').on('click', function() {
        let row = $('#materialForm tr').length + 1;

        let newRow = `
        <tr>
            <td class="remove-btn">
                <a href="javascript:removeDetailRow(${row})" class="btn btn-icon btn-sm bg-danger-subtle" data-bs-toggle="tooltip" title="Delete">
                    <i class="mdi mdi-delete fs-14 text-danger"></i>
                </a>
            </td>
            <td>
                <select class="form-control js-example-basic-single" name="materialCode[]" id="materialCode_${row}" >
                    <option selected disabled value="">{{ __('general.choose') }}...</option>
                    @foreach ($material as $item)
                        <option value="{{ $item->code }}">{{ $item->name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <select class="form-control js-example-basic-single" name="unitCode[]" id="unitCode_${row}" >
                    <option selected disabled value="">{{ __('general.choose') }}...</option>
                    @foreach ($unit as $item)
                        <option value="{{ $item->code }}">{{ $item->name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input class="form-control" name="materialQty[]" id="materialQty_${row}" type="number" min="1" placeholder="Material Qty">
            </td>
            <td>
                <select class="form-control js-example-basic-single" name="unitCode2[]" id="unitCode2_${row}" >
                    <option selected disabled value="">{{ __('general.choose') }}...</option>
                    @foreach ($unit as $item)
                        <option value="{{ $item->code }}">{{ $item->name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input class="form-control" name="materialQty2[]" id="materialQty2_${row}" type="number" min="1" placeholder="Qty">
            </td>
        </tr>
    `;

        $('#materialForm').append(newRow);

        $(`#materialCode_${row}`).select2();
        $(`#unitCode_${row}`).select2();
        $(`#unitCode2_${row}`).select2();
    });

    function removeDetailRow(row) {
        $(`#materialCode_${row}`).closest('tr').remove();
    }

    function deleteOrderMaterial(id) {
        var url = '{{ route('operational.order-material.destroy', ':id') }}';
        url = url.replace(':id', id);

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

    $('#dt').DataTable()
</script>
@endpush