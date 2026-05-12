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
    <form class="row g-3" method="post" action="{{ route($view . 'update', $data->id) }}" id="edit-form">
        @csrf
        @method('PUT')
        <div class="col-sm-12">
            @include('partials.alert')

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>{{ $title }} {{ __('general.edit_data') }}</h4>

                    <a href="{{ route($view . 'index') }}" class="btn btn-info">{{ __('general.back_to_list') }}</a>

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
                                            data-fleet-type="{{ isset($item->company->type) && strtolower((string) $item->company->type) === 'external' ? 'external' : 'internal' }}"
                                            {{ $data->fleetCode == $item->code ? 'selected' : '' }}>
                                            {{ strtoupper($item->plateNumber) }} -
                                            {{ isset($item->company->type) ? ucfirst(strtolower((string) $item->company->type)) : 'Internal' }}
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
                                    required placeholder="{{ __('menu_order.shipment_no') }}"
                                    value="{{ mb_strtoupper($data->shipmentNumber ?? '') }}" readonly>
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
                                            {{ mb_strtoupper($item->name) }}
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
                                <label class="form-label" for="orderTypeCode">{{ __('menu_order.load_type') }} <i
                                        class="mdi mdi-information text-danger"></i></label>
                                <select class="js-example-basic-single" name="routeTypeCode" id="routeTypeCode"
                                    required="">
                                    <option selected="" disabled="" value="">
                                        {{ __('general.choose') }}...
                                    </option>
                                    @foreach ($routeType as $item)
                                        <option value="{{ $item->code }}"
                                            {{ $data->route?->routeTypeCode == $item->code ? 'selected' : '' }}>
                                            {{ $item->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- <div class="row mt-4">
                                <div class="col-md-6 position-relative">
                                    <label class="form-label"
                                        for="originLocationCode">{{ __('menu_order.origin_location') }} <i
                        class="mdi mdi-information text-danger"></i></label>
                    <select class="js-example-basic-single" name="originLocationCode"
                        id="originLocationCode" required="">
                        <option selected="" disabled="" value="">
                            {{ __('general.choose') }}...
                        </option>
                        @foreach ($origin as $item)
                        <option value="{{ $item->originLocationCode }}"
                            {{ $route->originLocationCode == $item->originLocationCode ? 'selected' : '' }}>
                            {{ $item->originLocation->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 position-relative">
                    <label class="form-label"
                        for="destinationLocationCode">{{ __('menu_order.destination_location') }}<i
                            class="mdi mdi-information text-danger"></i> </label>
                    <select class="js-example-basic-single" name="destinationLocationCode"
                        id="destinationLocationCode" required="">
                        <option selected="" disabled="" value="">
                            {{ __('general.choose') }}...
                        </option>
                        @foreach ($destination as $item)
                        <option value="{{ $item->destinationLocationCode }}"
                            {{ $route->destinationLocationCode == $item->destinationLocationCode ? 'selected' : '' }}>
                            {{ $item->destinationLocation->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div> --}}

                        <div class="row mt-4">
                            @php
                                $label = match ($data->route?->routeTypeCode) {
                                    'TONASE' => 'Tonase',
                                    'TRIP' => 'Trip',
                                    'KUBIK' => 'Kubik',
                                    default => '-',
                                };
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

                            <div class="col-md-6 position-relative " id="qtyField">
                                <label class="form-label" id="qtyLabel" for="qty">{{ $label }}</label>
                                <input class="form-control" name="qty" id="qty" step="any" min="1"
                                    min="1" type="number" value="{{ $data->qty }}" placeholder="" required>
                            </div>
                        </div>

                        {{-- @role('SPRADMIN', 'SPRUSER', 'DO')
            <div class="row mt-4">
                <div class="col-md-6">
                    <label class="form-label" for="routeAmount">{{ __('menu_order.route_price') }}</label>
                    <input class="form-control" name="routeAmount" id="routeAmount"
                        oninput="formatAngka(this)" type="text"
                        placeholder="{{ __('menu_order.route_price') }}"
                        value="{{ number_format($data->routeAmount, 2, ',', '.') }}">
                </div>
            </div>
            @endrole --}}

                        <!-- Hidden input untuk semua role -->
                        <input type="hidden" name="routeAmount" value="{{ $data->routeAmount }}">
                        <input type="hidden" name="price" id="priceHidden" value="{{ $data->price }}">
                        <input type="hidden" name="vendorPrice" id="vendorPriceHidden"
                            value="{{ $data->vendorPrice ?? 0 }}">
                        <input type="hidden" name="vendorPriceSingle" id="vendorPriceSingleHidden"
                            value="{{ $data->vendorPriceSingle ?? 0 }}">
                        <input type="hidden" name="personalVendorPrice" id="personalVendorPriceHidden"
                            value="{{ $data->personalVendorPrice }}">
                        <input type="hidden" name="personalVendorPriceSingle" id="personalVendorPriceSingleHidden"
                            value="{{ $data->personalVendorPriceSingle }}">

                        {{-- <div class="row mt-4">
                            <div class="col-md-6 position-relative">
                                <label class="form-label" for="materialCode">Material</label>
                                <select class="js-example-basic-single" name="materialCode" id="materialCode">
                                    <option selected="" disabled="" value="">{{ __('general.choose') }}...
            </option>
            @foreach ($material as $item)
            <option value="{{ $item->code }}"
                {{ $data->materialCode == $item->code ? 'selected' : '' }}>
                {{ $item->name }}
            </option>
            @endforeach
            </select>`
        </div>

        <div class="col-md-6 position-relative">
            <label class="form-label" for="unitCode">Unit </label>
            <select class="js-example-basic-single" name="unitCode" id="unitCode">
                <option selected="" disabled="" value="">{{ __('general.choose') }}...
                </option>
                @foreach ($unit as $item)
                <option value="{{ $item->code }}"
                    {{ $data->unitCode == $item->code ? 'selected' : '' }}>{{ $item->name }}
                </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <label class="form-label" for="materialQty">Material Qty </label>
            <input class="form-control" name="materialQty" value="{{ $data->materialQty }}"
                id="materialQty" type="number" min="1" placeholder="Material Qty">
        </div>
    </div> --}}

                    </div>

                </div>
            </div>

            <!-- Card Informasi Harga -->
            <div class="card shadow-sm border-0" id="priceInfoCard">
                <div class="card-header bg-gradient-primary text-white">
                    <h5 class="mb-0">
                        <i class="mdi mdi-cash-multiple"></i> Informasi Harga
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <!-- Fleet Type Info -->
                        <div class="col-md-4">
                            <div class="p-3 rounded"
                                style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-white-50 mb-1 small">Tipe Fleet</p>
                                        @php
                                            $fleetTypeLabel = '-';
                                            if ($data->fleet) {
                                                $isExternalFleetType =
                                                    isset($data->fleet->company->type) &&
                                                    strtolower((string) $data->fleet->company->type) === 'external';
                                                $fleetTypeLabel = $isExternalFleetType ? 'External' : 'Internal';
                                            }
                                        @endphp
                                        <h4 class="text-white mb-0" id="fleetTypeDisplay">
                                            {{ $fleetTypeLabel }}</h4>
                                    </div>
                                    <div class="bg-white bg-opacity-25 p-3 rounded">
                                        <i class="mdi mdi-truck fs-2 text-white"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Price Info -->
                        <div class="col-md-4">
                            <div class="p-3 rounded"
                                style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="w-100">
                                        <p class="text-white-50 mb-1 small">Route Amount</p>
                                        <h4 class="text-white mb-0" id="priceDisplay">Rp
                                            {{ number_format($data->routeAmount ?? 0, 0, ',', '.') }}</h4>
                                        <p class="text-white-50 mb-0 small" id="priceDetailDisplay">{{ $data->qty }} ×
                                            Rp {{ number_format($data->price ?? 0, 0, ',', '.') }} = Rp
                                            {{ number_format($data->routeAmount ?? 0, 0, ',', '.') }}</p>
                                    </div>
                                    <div class="bg-white bg-opacity-25 p-3 rounded">
                                        <i class="mdi mdi-currency-usd fs-2 text-white"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Personal Vendor Price Info -->
                        <div class="col-md-4" id="vendorPriceCard">
                            <div class="p-3 rounded"
                                style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="w-100">
                                        @php
                                            $isExternalFleet =
                                                $data->fleet &&
                                                $data->fleet->company &&
                                                strtolower($data->fleet->company->type) === 'external';
                                            $vendorLabel = $isExternalFleet ? 'Vendor Price' : 'Personal Vendor Price';
                                            $vendorSingle = $isExternalFleet
                                                ? $data->vendorPriceSingle ?? 0
                                                : $data->personalVendorPriceSingle ?? 0;
                                            $vendorTotal = $isExternalFleet
                                                ? $data->vendorPrice ?? 0
                                                : $data->personalVendorPrice ?? 0;
                                        @endphp
                                        <p class="text-white-50 mb-1 small" id="vendorPriceLabel">{{ $vendorLabel }}</p>
                                        <h4 class="text-white mb-0" id="vendorPriceDisplay">Rp
                                            {{ number_format($vendorTotal, 0, ',', '.') }}</h4>
                                        <p class="text-white-50 mb-0 small" id="vendorPriceDetailDisplay">
                                            {{ $data->qty }} × Rp
                                            {{ number_format($vendorSingle, 0, ',', '.') }} = Rp
                                            {{ number_format($vendorTotal, 0, ',', '.') }}</p>
                                    </div>
                                    <div class="bg-white bg-opacity-25 p-3 rounded">
                                        <i class="mdi mdi-account-cash fs-2 text-white"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Info -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="alert alert-info mb-0" role="alert">
                                <i class="mdi mdi-information"></i>
                                <strong>Catatan:</strong>
                                <span id="priceNote">
                                    Harga dihitung berdasarkan route yang dipilih × qty. Fleet type:
                                    <strong>{{ $fleetTypeLabel }}</strong>
                                </span>
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
                            @if (isset($data->orderMaterial))
                                @foreach ($data->orderMaterial as $ordm)
                                    <tr>
                                        <td>
                                            <a href="javascript:deleteOrderMaterial('{{ $ordm->id }}')"
                                                class="btn btn-icon btn-sm bg-danger-subtle" data-bs-toggle="tooltip"
                                                title="Delete">
                                                <i class="mdi mdi-delete fs-14 text-danger"></i>
                                            </a>
                                        </td>
                                        <td>
                                            <select class="form-control js-example-basic-single" name="materialCode[]"
                                                id="materialCode_{{ $loop->iteration }}">
                                                @foreach ($material as $item)
                                                    <option value="{{ $item->code }}"
                                                        {{ $ordm->materialCode == $item->code ? 'selected' : '' }}>
                                                        {{ $item->name }}
                                                    </option>
                                                @endforeach
                                            </select>`
                                        </td>
                                        <td>
                                            <select class="form-control js-example-basic-single" name="unitCode[]"
                                                id="unitCode_{{ $loop->iteration }}">

                                                <option selected="" disabled="" value="">
                                                    {{ __('general.choose') }}...
                                                </option>
                                                @foreach ($unit as $item)
                                                    <option value="{{ $item->code }}"
                                                        {{ $ordm->unitCode == $item->code ? 'selected' : '' }}>
                                                        {{ $item->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input class="form-control" name="materialQty[]"
                                                id="materialQty_{{ $loop->iteration }}" type="number"
                                                value="{{ $ordm->materialQty }}" placeholder="Material Qty">
                                        </td>
                                        <td>
                                            <select class="form-control js-example-basic-single" name="unitCode2[]"
                                                id="unitCode2_{{ $loop->iteration }}">

                                                <option selected="" disabled="" value="">
                                                    {{ __('general.choose') }}...
                                                </option>
                                                @foreach ($unit as $item)
                                                    <option value="{{ $item->code }}"
                                                        {{ $ordm->unitCode2 == $item->code ? 'selected' : '' }}>
                                                        {{ $item->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input class="form-control" name="materialQty2[]"
                                                id="materialQty2_{{ $loop->iteration }}" type="number"
                                                value="{{ $ordm->materialQty2 }}" placeholder="Qty">
                                        </td>

                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td class="remove-btn"></td>
                                    <td>
                                        <select class="js-example-basic-single" name="materialCode[]" id="materialCode_1"
                                            d>
                                            <option selected="" disabled="" value="">
                                                {{ __('general.choose') }}...
                                            </option>
                                            @foreach ($material as $item)
                                                <option value="{{ $item->code }}">
                                                    {{ $item->name }}
                                                </option>
                                            @endforeach
                                        </select>`
                                    </td>
                                    <td>
                                        <select class="js-example-basic-single" name="unitCode[]" id="unitCode_1">
                                            <option selected="" disabled="" value="">
                                                {{ __('general.choose') }}...
                                            </option>
                                            @foreach ($unit as $item)
                                                <option value="{{ $item->code }}">{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input class="form-control" name="materialQty[]" id="materialQty_1"
                                            type="number" placeholder="Material Qty">
                                    </td>
                                    <td>
                                        <select class="js-example-basic-single" name="unitCode2[]" id="unitCode2_1">
                                            <option selected="" disabled="" value="">
                                                {{ __('general.choose') }}...
                                            </option>
                                            @foreach ($unit as $item)
                                                <option value="{{ $item->code }}">{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input class="form-control" name="materialQty2[]" id="materialQty_1"
                                            type="number" placeholder="Qty">
                                    </td>

                                </tr>
                            @endif



                        </tbody>
                    </table>
                </div>

            </div>

            @if ($customerDetailOrder->count() > 0)
                <div class="card" id="card-customer-detail">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4> {{ __('menu_order.customer_detail_data') }}</h4>

                    </div>
                    <div class="card-body col-md-6">
                        @foreach ($customerDetailOrder as $item)
                            <input type="hidden" name="customerDetailCode[]" value="{{ $item->customerDetailCode }}">

                            <div class="mb-3">
                                <label for="{{ $item->customerDetail?->name }}"
                                    class="form-label">{{ $item->customerDetail?->name }}</label>
                                <input type="text" class="form-control"
                                    placeholder="{{ $item->customerDetail?->name }}" name="value[]"
                                    value="{{ $item->value }}">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @php
                // Cek apakah fleet adalah external
                $fleetData = $data->fleet;
                $isExternalFleet =
                    $fleetData && $fleetData->company && strtolower($fleetData->company->type) === 'external';
            @endphp

            <div class="card {{ $isExternalFleet ? 'd-none' : '' }}" id="costComponentCard">
                <div class="card-body col-md-12">
                    <ul class="nav nav-tabs" id="icon-tab" role="tablist">
                        <li class="nav-item"><a class="nav-link active txt-success" id="icon-home-tab"
                                data-bs-toggle="tab" href="#icon-home" role="tab" aria-controls="icon-home"
                                aria-selected="true">{{ __('menu_order.additional_cost') }}</a>
                        </li>
                        <li class="nav-item"><a class="nav-link txt-success" id="profile-icon-tabs" data-bs-toggle="tab"
                                href="#profile-icon" role="tab" aria-controls="profile-icon"
                                aria-selected="false">{{ __('menu_order.add_cost') }}</a>
                        </li>
                    </ul>
                    <div class="tab-content" id="icon-tabContent">
                        @include('operational.order.components.cost-edit')
                        @include('operational.order.components.cost-component-add')
                    </div>
                </div>
            </div>

            <div class="alert alert-info {{ $isExternalFleet ? '' : 'd-none' }}" id="externalCostNote" role="alert">
                <strong>Catatan:</strong> Biaya tidak dapat ditambahkan untuk kendaraan eksternal. Biaya dikelola secara
                terpisah.
            </div>

            @if ($data->status == 0 || in_array(auth()->user()->roleCode, ['SPRADMIN', 'SPRUSER']))
                <div class="card">
                    <div class="col-12">
                        <div class="card-body ">
                            <button class="btn btn-primary" id="save"
                                type="button">{{ __('general.save_changes') }}</button>
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

    <!-- Preloader -->
    <div id="preloader"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 9999; align-items: center; justify-content: center;">
        <div style="text-align: center; color: white;">
            <div class="spinner-border" role="status" style="width: 3rem; height: 3rem; margin-bottom: 1rem;">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p style="font-size: 1.2rem; margin: 0;">Sedang menyimpan data...</p>
        </div>
    </div>

@endsection

@push('script')
    <script src=" {{ asset('assets/js/helper.js') }}"></script>

    <script src="{{ asset('assets/js/flat-pickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/custom-flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src=" {{ asset('assets/js/select2/select2-custom.js') }}"></script>
    <script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>
    <!-- DataTables Core MUST be loaded first -->
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


    <script>
        $(document).ready(function() {
            const selectedType = $('#routeTypeCode').select2('val');
            // loadQty(selectedType)

            syncCostComponentVisibility();

            let submitFromSaveButton = false;

            $('#save').on('click', function() {
                submitFromSaveButton = true;
                $('#edit-form').trigger('submit');
            });

            // Handle form submit dengan confirmation dan preloader
            $('#edit-form').on('submit', function(e) {
                e.preventDefault();

                // Jangan tampilkan konfirmasi simpan untuk aksi selain klik tombol Save.
                if (!submitFromSaveButton) {
                    return false;
                }

                submitFromSaveButton = false;

                const form = $(this);
                const submitButton = $('#save');

                // Show confirmation dialog
                swal({
                    title: "Konfirmasi Simpan",
                    text: "Apakah Anda yakin ingin menyimpan perubahan order ini?",
                    icon: "warning",
                    buttons: {
                        cancel: "Batal",
                        confirm: "Ya, Simpan"
                    },
                    dangerMode: false,
                }).then((willSave) => {
                    if (willSave) {
                        // Show preloader
                        showPreloader();

                        // Validate routeAmount (bisa dari input atau hidden)
                        let routeAmount = $('input[name="routeAmount"]').val();
                        if (!routeAmount || routeAmount === '') {
                            hidePreloader();
                            swal({
                                title: "Error",
                                text: "Route Amount tidak boleh kosong",
                                icon: "error",
                                button: "OK"
                            });
                            return false;
                        }

                        // Submit form menggunakan AJAX
                        const formData = new FormData(form[0]);

                        $.ajax({
                            url: form.attr('action'),
                            method: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                hidePreloader();
                                swal({
                                    title: "Sukses",
                                    text: "Data order berhasil disimpan",
                                    icon: "success",
                                    buttons: false,
                                    timer: 1500
                                }).then(() => {
                                    window.location.href =
                                        "{{ route($view . 'index') }}";
                                });
                            },
                            error: function(xhr) {
                                hidePreloader();
                                let errorMessage =
                                    'Terjadi kesalahan saat menyimpan data';

                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                } else if (xhr.responseText) {
                                    // Try to extract error from HTML response
                                    const match = xhr.responseText.match(
                                        /<h1[^>]*>([^<]+)<\/h1>/);
                                    if (match) {
                                        errorMessage = match[1];
                                    }
                                }

                                swal({
                                    title: "Error",
                                    text: errorMessage,
                                    icon: "error",
                                    button: "OK"
                                });

                                submitButton.prop('disabled', false);
                            }
                        });
                    }
                });
            });
        });

        function deleteCost(id) {
            var url = '{{ route('operational.order-cost.destroy', ':id') }}'; // Use placeholder ':id'
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

        function checkAndLoadOriginLocation() {
            const customerCode = $('#customerCode').select2('val'); // Use select2 to get the value
            const routeTypeCode = $('#routeTypeCode').select2('val'); // Use select2 to get the value

            if (customerCode && routeTypeCode) {
                let html = '<option selected="" disabled="" value="">{{ __('general.choose') }}...</option>';
                $('#originLocationCode').html(html);

                $.get("{{ url('ajax/origin-by-customer') }}/" + customerCode + "/" + routeTypeCode, function(data) {
                    data.forEach(i => {
                        html += '<option value="' + i.code + '">' + i.name + '</option>';
                    });
                    $('#originLocationCode').html(html);
                    // Reinitialize Select2 for origin location dropdown after updating options
                });
            }
        }

        function loadQty(selectedType) {
            // Show the correct field based on the selection after 1 second (simulating processing time)
            if (selectedType === 'TONASE') {
                $('#qtyLabel').html(
                    'Tonase <i class="icofont icofont-warning-alt text-danger"></i>'
                ); // Update the label with icon
                $('#qty').attr('placeholder', 'Enter Tonase'); // Update placeholder
                $('#qty').val(1); // Set default value to 1
                $('#qty').removeAttr('readonly'); // Remove readonly if it was set
                $('#qtyField').removeClass('d-none'); // Show the field
            } else if (selectedType === 'TRIP') {
                $('#qtyLabel').html(
                    'Ritase <i class="icofont icofont-warning-alt text-danger"></i>'
                ); // Update the label with icon
                $('#qty').attr('placeholder', 'Enter Ritase'); // Update placeholder
                $('#qty').val(1); // Set default value to 1
                $('#qty').attr('readonly', true); // Make the field readonly
                $('#qtyField').removeClass('d-none'); // Show the field
            } else if (selectedType == 'KUBIKASE') {
                $('#qtyLabel').html(
                    'Kubikase <i class="icofont icofont-warning-alt text-danger"></i>'
                ); // Update the label with icon
                $('#qty').attr('placeholder', 'Enter Kubikase'); // Update placeholder
                $('#qty').val(1); // Set default value to 1
                $('#qty').removeAttr('readonly'); // Remove readonly if it was set
                $('#qtyField').removeClass('d-none'); // Show the field
            } else {
                $('#qtyField').addClass('d-none'); // Hide the field if neither is selected
            }
        }

        // When routeTypeCode is changed
        $('#routeTypeCode').on('change', function() {
            $('body').append(`
            <div class="loader-wrapper">
                <div class="loader">
                    <div class="loader4"></div>
                </div>
            </div>
        `);

            const selectedType = $('#routeTypeCode').select2('val'); // Get the selected value from select2

            setTimeout(function() {

                loadQty(selectedType)
                // Remove the loader once the logic is complete
                $('.loader-wrapper').remove();
            }, 1000); // Simulate 1-second delay for the loader
        });


        function checkAndLoadDestinationLocation() {
            const customerCode = $('#customerCode').select2('val'); // Use select2 to get the value
            const routeTypeCode = $('#routeTypeCode').select2('val'); // Use select2 to get the value
            const originLocationCode = $('#originLocationCode').select2('val'); // Use select2 to get the value

            if (customerCode && routeTypeCode && originLocationCode) {
                let html = '<option selected="" disabled="" value="">{{ __('general.choose') }}...</option>';
                $('#destinationLocationCode').html(html);

                $.get("{{ url('ajax/destination-by-customer') }}/" + customerCode + "/" + routeTypeCode + "/" +
                    originLocationCode,
                    function(data) {
                        data.forEach(i => {
                            html += '<option value="' + i.code + '">' + i.name + '</option>';
                        });
                        $('#destinationLocationCode').html(html);
                        // Reinitialize Select2 for destination location dropdown after updating options
                    });
            }
        }


        // function checkAndLoadRoute() {
        //     const customerCode = $('#customerCode').select2('val');
        //     const originLocationCode = $('#originLocationCode').select2('val');
        //     const destinationLocationCode = $('#destinationLocationCode').select2('val');

        //     if (customerCode && originLocationCode && destinationLocationCode) {
        //         $.get("{{ url('ajax/route-by-customer') }}/" + customerCode + "/" + originLocationCode + "/" +
        //             destinationLocationCode,
        //             function(data) {

        //             });
        //     }
        // }

        function checkAndLoadRoute() {
            const routeData = $('#routeData').select2('val');

            if (routeData) {
                $.get("{{ url('ajax/route-order-detail') }}/" + routeData,
                    function(data) {
                        const componentList = document.getElementById('component-list');
                        if (componentList) {
                            componentList.innerHTML = '';
                            index = 0;

                            data.forEach((item, i) => {
                                let row = `
                            <tr>
                                <td>
                                    <a href="javascript:void(0)" onclick="removeRow(this)"
                                        class="btn btn-icon btn-sm bg-danger-subtle me-1"
                                        data-bs-toggle="tooltip" title="Delete">
                                        <i class="mdi mdi-delete fs-14 text-danger"></i>
                                    </a>
                                </td>
                                <td><input type="hidden"  name="componentName[]" readonly value="${item.cost_component.code}"> ${item.cost_component.name}</td>
                                <td><input class="form-control" name="description[]" value=""></td>
                                 <td>
             <input class="form-control"  name="nominal[]" oninput="formatAngka(this)" type="text" min=1 value="${new Intl.NumberFormat('id-ID').format(item.amount)}">
        </td>
                                <td><input class="form-control" name="type[]" value="Tidak Ditagihkan" readonly></td>
                            </tr>`;
                                componentList.insertAdjacentHTML('beforeend', row);
                                index++;
                            });
                        }
                    });
            }
        }

        function checkAndLoadRouteOrder() {
            let customerId = $('#customerCode option:selected').data('id');
            const customerCode = $('#customerCode').select2('val');
            const routeTypeCode = $('#routeTypeCode').select2('val');

            if (customerCode && routeTypeCode) {
                let html = '<option selected="" disabled="" value="">{{ __('general.choose') }}...</option>';
                $('#originLocationCode').html(html);

                $.get("{{ url('ajax/route-order') }}/" + customerId + "/" + routeTypeCode, function(data) {
                    data.forEach(i => {
                        let originName = i.origin_location ? i.origin_location.name : '-';
                        let destName = i.destination_location ? i.destination_location.name : '-';
                        html +=
                            `<option value="${i.code}">${i.name} (${originName} - ${destName})</option>`;

                    });
                    $('#routeData').html(html);
                    // Reinitialize Select2 for origin location dropdown after updating options
                });
            }
        }

        // Trigger origin location when both customer and route type are selected
        $('#customerCode, #routeTypeCode').on('change', function() {
            checkAndLoadRouteOrder();
        });

        // Trigger destination location when origin location is also selected
        $('#originLocationCode').on('select2:select', function() {
            checkAndLoadDestinationLocation();
        });

        $('#routeData').on('select2:select', function() {
            checkAndLoadRoute();
        });

        $('#customerCode').on('change', function() {
            let customerCode = $(this).val();
            let customerId = $('#customerCode option:selected').data('id');

            $.get("/ajax/order-shipment-format/" + customerId, function(data) {
                $('#shipmentNumber').val(String(data).toUpperCase());
            });

            if (customerCode) {
                $.get("/ajax/customer-detail/" + customerId, function(data) {
                    const $detailCard = $('#card-customer-detail');
                    const $cardBody = $detailCard.find('.card-body');
                    $cardBody.empty(); // Bersihkan isinya

                    if (data.length > 0) {
                        $detailCard.removeClass('d-none');

                        // Disable input #notes
                        $('#notes').prop('disabled', true);

                        data.forEach(item => {
                            let html = `
                    <input type="hidden" name="customerDetailCode[]" value="${item.code}">
                    <div class="mb-3">
                        <label class="form-label">${item.name} <i class="mdi mdi-information text-danger"></i></label>
                        <input class="form-control" name="value[]" type="text" required placeholder="${item.name}">
                    </div>`;
                            $cardBody.append(html);
                        });
                    } else {
                        $detailCard.addClass('d-none');
                        $cardBody.empty();

                        // Enable input #notes
                        $('#notes').prop('disabled', false);
                    }
                });
            } else {
                $('#card-customer-detail').addClass('d-none');
                $('#card-customer-detail .card-body').empty();

                // Enable input #notes
                $('#notes').prop('disabled', false);
            }
        });

        $('#add-material').on('click', function() {
            let row = $('#materialForm tr').length + 1;

            let newRow = `
    <tr>
        <td class="remove-btn">
            <a href="javascript:removeDetailRow(${row})"
                class="btn btn-icon btn-sm bg-danger-subtle"
                data-bs-toggle="tooltip" title="Delete">
                <i class="mdi mdi-delete fs-14 text-danger"></i>
            </a>
        </td>
        <td>
            <select class="form-control js-example-basic-single" name="materialCode[]" id="materialCode_${row}" >
                <option selected disabled value="">
                    {{ __('general.choose') }}...
                </option>
                @foreach ($material as $item)
                    <option value="{{ $item->code }}">{{ $item->name }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <select class="form-control js-example-basic-single" name="unitCode[]" id="unitCode_${row}" >
                <option selected disabled value="">
                    {{ __('general.choose') }}...
                </option>
                @foreach ($unit as $item)
                    <option value="{{ $item->code }}">{{ $item->name }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <input class="form-control" name="materialQty[]" id="materialQty_${row}" type="number"
                min="1" placeholder="Material Qty">
        </td>
        <td>
            <select class="form-control js-example-basic-single" name="unitCode2[]" id="unitCode2_${row}" >
                <option selected disabled value="">
                    {{ __('general.choose') }}...
                </option>
                @foreach ($unit as $item)
                    <option value="{{ $item->code }}">{{ $item->name }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <input class="form-control" name="materialQty2[]" id="materialQty2_${row}" type="number"
                min="1" placeholder="Qty">
        </td>
    </tr>
`;

            $('#materialForm').append(newRow);

            // Reinitialize select2 (jika pakai select2)
            $(`#materialCode_${row}`).select2();
            $(`#unitCode_${row}`).select2();
            $(`#materialCode2_${row}`).select2();
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

        // Initialize DataTable only if element exists
        $(document).ready(function() {
            if ($('#dt').length) {
                $('#dt').DataTable();
            }
            // Initialize price info on page load
            updatePriceInfo();
        });

        // Preloader functions
        function showPreloader() {
            $('#preloader').css('display', 'flex');
        }

        function hidePreloader() {
            $('#preloader').css('display', 'none');
        }

        function resolveSelectedFleetType() {
            const selectedType = ($('#fleetCode option:selected').data('fleet-type') || '').toString().toLowerCase();

            if (selectedType === 'external' || selectedType === 'internal') {
                return selectedType;
            }

            const displayType = ($('#fleetTypeDisplay').text() || '').toString().trim().toLowerCase();
            if (displayType === 'external' || displayType === 'internal') {
                return displayType;
            }

            return '';
        }

        function syncCostComponentVisibility(fleetType = null) {
            const normalizedType = (fleetType || resolveSelectedFleetType()).toString().toLowerCase();
            const isExternalFleet = normalizedType === 'external';

            $('#costComponentCard').toggleClass('d-none', isExternalFleet);
            $('#externalCostNote').toggleClass('d-none', !isExternalFleet);
            $('#costComponentCard').find('input, select, textarea').prop('disabled', isExternalFleet);
        }

        // Function to format number with thousand separator
        function formatNumber(num) {
            if (!num) return '0';
            return new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2
            }).format(num);
        }

        // Function to update price information
        function updatePriceInfo() {
            const fleetCode = $('#fleetCode').val();
            const routeCode = $('#routeData').val();
            const qty = $('#qty').val() || 1;

            syncCostComponentVisibility();

            // Check if we have the required data
            if (!fleetCode || !routeCode) {
                return;
            }

            // Make AJAX request
            $.ajax({
                url: '{{ route('ajax.order-calculate-price') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    fleetCode: fleetCode,
                    routeCode: routeCode,
                    qty: qty
                },
                success: function(response) {
                    if (response.success) {
                        const fleetTypeRaw = (response.fleetType || (response.isExternal ? 'external' :
                                'internal'))
                            .toString().toLowerCase();
                        const fleetTypeLabel = fleetTypeRaw === 'external' ? 'External' : 'Internal';

                        syncCostComponentVisibility(fleetTypeRaw);

                        // Update fleet type
                        $('#fleetTypeDisplay').text(fleetTypeLabel);

                        // Update price (routeAmount = qty × price satuan)
                        $('#priceDisplay').text('Rp ' + formatNumber(response.routeAmount));
                        $('#priceDetailDisplay').text(qty + ' × Rp ' + formatNumber(response.price) + ' = Rp ' +
                            formatNumber(response.routeAmount));

                        const isExternal = response.isExternal === true;
                        const vendorSingle = isExternal ? response.vendorPriceSingle : response
                            .personalVendorPriceSingle;
                        const vendorTotal = isExternal ? response.vendorPrice : response.personalVendorPrice;
                        const vendorLabel = isExternal ? 'Vendor Price' : 'Personal Vendor Price';

                        $('#vendorPriceLabel').text(vendorLabel);
                        $('#vendorPriceDisplay').text('Rp ' + formatNumber(vendorTotal));
                        $('#vendorPriceDetailDisplay').text(qty + ' × Rp ' + formatNumber(vendorSingle) +
                            ' = Rp ' + formatNumber(vendorTotal));

                        // Update hidden routeAmount input with calculated price
                        $('input[name="price"]').val(response.price);
                        $('input[name="routeAmount"]').val(response.routeAmount);
                        $('input[name="vendorPrice"]').val(response.vendorPrice);
                        $('input[name="vendorPriceSingle"]').val(response.vendorPriceSingle);
                        $('input[name="personalVendorPrice"]').val(response.personalVendorPrice);
                        $('input[name="personalVendorPriceSingle"]').val(response.personalVendorPriceSingle);

                        // Always show vendor price card
                        $('#vendorPriceCard').show();
                        $('#priceNote').html(
                            'Harga dihitung berdasarkan route yang dipilih × qty. Fleet type: <strong>' +
                            fleetTypeLabel + '</strong>');

                        if (response.priceNotSet) {
                            swal({
                                title: "Warning",
                                text: "Route ini dengan vendor ini belum di setting harga nya, silahkan setting harga.",
                                icon: "warning",
                            });
                        } else if (response.priceNotSetInternal) {
                            swal({
                                title: "Warning",
                                text: "Route ini belum di setting harga vendor internal nya, silahkan setting harga.",
                                icon: "warning",
                            });
                        }
                    }
                },
                error: function(xhr) {
                    console.error('Error calculating price:', xhr);
                }
            });
        }

        // Attach event listeners for real-time updates
        $('#fleetCode, #routeData, #qty').on('change keyup', function() {
            updatePriceInfo();
        });

        // Also update when route is selected
        $('#routeData').on('select2:select', function() {
            updatePriceInfo();
        });

        // Update when fleet is selected
        $('#fleetCode').on('select2:select', function() {
            updatePriceInfo();
        });

        // Initialize Select2 and set values
        $(document).ready(function() {
            $('.js-example-basic-single').select2({
                placeholder: "{{ __('general.choose') }}...",
                allowClear: true,
                width: '100%'
            });

            // Set selected values setelah Select2 init
            $('#fleetCode').val('{{ $data->fleetCode }}').trigger('change');
            $('#driverCode').val('{{ $data->driverCode }}').trigger('change');
            $('#customerCode').val('{{ $data->customerCode }}').trigger('select2:select');
            $('#routeTypeCode').val('{{ $data->route?->routeTypeCode }}');

            // Set route langsung dengan delay kecil untuk pastikan DOM siap
            setTimeout(function() {
                $('#routeData').val('{{ $data->routeCode }}');
                $('#routeData').trigger('change');
            }, 100);

            syncCostComponentVisibility();
        });
    </script>
@endpush
