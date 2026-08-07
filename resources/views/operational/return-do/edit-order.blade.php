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
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/libs/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/libs/datatables.net-keytable-bs5/css/keyTable.bootstrap5.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/libs/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/libs/datatables.net-select-bs5/css/select.bootstrap5.min.css') }}">

    <style>
        #dt {
            border-spacing: 0 10px !important;
            border-collapse: separate !important;
        }
        .hero-banner {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #2563eb 100%);
            border-radius: 16px;
            padding: 1.75rem 2rem;
            color: #ffffff;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.15);
            margin-bottom: 1.75rem;
            position: relative;
            overflow: hidden;
        }
        .hero-banner::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
            border-radius: 50%;
            pointer-events: none;
        }
        .card-custom {
            border: 1px solid rgba(226, 232, 240, 0.8) !important;
            border-radius: 14px !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03) !important;
            transition: all 0.25s ease;
            margin-bottom: 1.5rem;
            background: #ffffff;
        }
        .card-custom:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.06) !important;
        }
        .card-custom .card-header {
            background: #ffffff;
            border-bottom: 1px solid #f1f5f9;
            padding: 1.25rem 1.5rem;
            border-top-left-radius: 14px !important;
            border-top-right-radius: 14px !important;
        }
        .card-custom .card-body {
            padding: 1.5rem;
        }
        .section-icon-title {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: rgba(37, 99, 235, 0.1);
            color: #2563eb;
            font-size: 1.1rem;
            margin-right: 0.75rem;
        }
        .form-label-custom {
            font-size: 0.85rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.4rem;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .form-control-custom {
            border-radius: 9px;
            border: 1px solid #cbd5e1;
            padding: 0.65rem 0.9rem;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            background-color: #ffffff;
        }
        .form-control-custom:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
            background-color: #ffffff;
        }
        .form-control-custom[readonly] {
            background-color: #f8fafc;
            color: #64748b;
        }
        .price-card-box {
            border-radius: 14px;
            padding: 1.25rem;
            position: relative;
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .price-card-box:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.12);
        }
        .toggle-master-price-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 0.6rem 1.1rem;
            transition: all 0.2s ease;
        }
        .toggle-master-price-box:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }
        .sticky-bottom-bar {
            position: sticky;
            bottom: 1.5rem;
            z-index: 100;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            padding: 1rem 1.5rem;
            margin-top: 2rem;
        }
        .btn-save-gradient {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            border: none;
            color: #ffffff;
            font-weight: 600;
            border-radius: 10px;
            padding: 0.7rem 1.75rem;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
            transition: all 0.2s ease;
        }
        .btn-save-gradient:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
            color: #ffffff;
        }
    </style>
@endpush

@section('content')
    <form class="row g-3" method="post" action="{{ route('operational.return-do.update-order', $data->code) }}"
        id="edit-form" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="col-sm-12">
            @include('partials.alert')

            <!-- Hero Header Banner -->
            <div class="hero-banner d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-white bg-opacity-25 text-white px-3 py-1 rounded-pill font-weight-bold">
                            <i class="mdi mdi-tag-outline me-1"></i> {{ $data->code }}
                        </span>
                        <span class="badge bg-warning text-dark px-3 py-1 rounded-pill">
                            <i class="mdi mdi-clock-outline me-1"></i> Status: Not Return DO
                        </span>
                    </div>
                    <h3 class="text-white mb-0 font-weight-bold">
                        <i class="mdi mdi-file-document-edit me-2"></i> {{ $title }} {{ __('general.edit_data') }}
                    </h3>
                    <p class="text-white-50 mb-0 small mt-1">{{ __('menu_return_do.update_order_sub') }}</p>
                </div>

                <a href="{{ route('operational.return-do.index') }}" class="btn btn-light btn-sm px-3 rounded-pill font-weight-bold shadow-sm">
                    <i class="mdi mdi-arrow-left me-1"></i> {{ __('general.back_to_list') }}
                </a>
            </div>

            <!-- Card Informasi Utama Order -->
            <div class="card card-custom">
                <div class="card-header d-flex align-items-center">
                    <span class="section-icon-title">
                        <i class="mdi mdi-truck-fast-outline"></i>
                    </span>
                    <h4 class="mb-0 font-weight-bold text-dark">{{ __('menu_return_do.main_order_info') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label-custom" for="name">
                                <i class="mdi mdi-barcode text-primary"></i> {{ __('menu_order.order_code') }}
                                <i class="mdi mdi-information text-danger"></i>
                            </label>
                            <input type="hidden" name="code" value="{{ $data->code }}">
                            <input class="form-control form-control-custom" type="text" required readonly disabled value="{{ $data->code }}">
                        </div>

                        <div class="col-md-6 position-relative">
                            <label class="form-label-custom" for="fleetCode">
                                <i class="mdi mdi-truck-outline text-primary"></i> {{ __('menu_order.plate_number') }}
                                <i class="mdi mdi-information text-danger"></i>
                            </label>
                            <select class="js-example-basic-single" name="fleetCode" id="fleetCode" required="">
                                <option selected="" disabled="" value="">{{ __('general.choose') }}...</option>
                                @foreach ($fleets as $item)
                                    <option value="{{ $item->code }}" {{ $data->fleetCode == $item->code ? 'selected' : '' }}>
                                        {{ strtoupper($item->plateNumber) }} - {{ $item->company?->type ?? 'N/A' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label-custom" for="orderDate">
                                <i class="mdi mdi-calendar-range text-primary"></i> {{ __('menu_order.order_date') }}
                                <i class="mdi mdi-information text-danger"></i>
                            </label>
                            <input class="form-control form-control-custom" name="orderDate" id="datetime-local" type="date" required
                                placeholder="{{ __('menu_order.order_date') }}" value="{{ $data->orderDate }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label-custom" for="shipmentNumber">
                                <i class="mdi mdi-file-document-outline text-primary"></i> {{ __('menu_order.shipment_no') }}
                                <i class="mdi mdi-information text-danger"></i>
                            </label>
                            <input class="form-control form-control-custom" name="shipmentNumber" id="shipmentNumber" type="text"
                                required placeholder="{{ __('menu_order.shipment_no') }}"
                                value="{{ mb_strtoupper($data->shipmentNumber ?? '') }}" readonly>
                        </div>

                        <div class="col-md-6 position-relative">
                            <label class="form-label-custom" for="driverCode">
                                <i class="mdi mdi-account-tie-outline text-primary"></i> {{ __('menu_order.driver') }}
                                <i class="mdi mdi-information text-danger"></i>
                            </label>
                            <select class="js-example-basic-single" name="driverCode" id="driverCode" required="">
                                <option selected="" disabled="" value="">{{ __('general.choose') }}...</option>
                                @foreach ($driver as $item)
                                    <option value="{{ $item->code }}" {{ $data->driverCode == $item->code ? 'selected' : '' }}>
                                        {{ mb_strtoupper($item->name) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label-custom" for="notes">
                                <i class="mdi mdi-note-text-outline text-primary"></i> {{ __('menu_order.notes') }}
                            </label>
                            <input class="form-control form-control-custom" name="notes" id="notes" type="text"
                                placeholder="{{ __('menu_order.notes') }}" value="{{ $data->notes }}">
                        </div>

                        <div class="col-md-6 position-relative">
                            <label class="form-label-custom" for="customerCode">
                                <i class="mdi mdi-domain text-primary"></i> {{ __('menu_order.customer') }}
                                <i class="mdi mdi-information text-danger"></i>
                            </label>
                            <select class="js-example-basic-single" name="customerCode" id="customerCode" required="">
                                <option selected="" disabled="" value="">{{ __('general.choose') }}...</option>
                                @foreach ($customer as $item)
                                    <option value="{{ $item->code }}" data-id="{{ $item->id }}" {{ $data->customerCode == $item->code ? 'selected' : '' }}>
                                        {{ $item->code . ' - ' . $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 position-relative">
                            <label class="form-label-custom" for="orderTypeCode">
                                <i class="mdi mdi-format-list-bulleted-type text-primary"></i> {{ __('menu_order.load_type') }}
                                <i class="mdi mdi-information text-danger"></i>
                            </label>
                            <select class="js-example-basic-single" name="routeTypeCode" id="routeTypeCode" required="">
                                <option selected="" disabled="" value="">{{ __('general.choose') }}...</option>
                                @foreach ($routeType as $item)
                                    <option value="{{ $item->code }}" {{ $data->route?->routeTypeCode == $item->code ? 'selected' : '' }}>
                                        {{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        @php
                            $label = match ($data->route?->routeTypeCode) {
                                'TONASE' => 'Tonase',
                                'TRIP' => 'Trip',
                                'KUBIK' => 'Kubik',
                                default => '-',
                            };
                        @endphp

                        <div class="col-md-6 position-relative">
                            <label class="form-label-custom" for="routeData">
                                <i class="mdi mdi-map-marker-distance text-primary"></i> {{ __('menu_order.route') }}
                                <i class="mdi mdi-information text-danger"></i>
                            </label>
                            <select class="js-example-basic-single" name="routeData" id="routeData" required="">
                                <option selected="" disabled="" value="">{{ __('general.choose') }}...</option>
                                @foreach ($route as $item)
                                    <option value="{{ $item->code }}" {{ $item->code == $data->routeCode ? 'selected' : '' }}>
                                        {{ $item->name . ' (' . ($item->originLocation->name ?? '') . ($item->destinationLocation ? ' - ' . $item->destinationLocation->name : '') . ') - ' . $item->description }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 position-relative" id="qtyField">
                            <label class="form-label-custom" id="qtyLabel" for="qty">
                                <i class="mdi mdi-numeric text-primary"></i> {{ $label }}
                            </label>
                            <input class="form-control form-control-custom" name="qty" id="qty" step="any" min="1" type="number"
                                value="{{ $data->qty }}" placeholder="Masukkan jumlah qty" required>
                        </div>

                        <!-- Hidden price state -->
                        <input type="hidden" name="routeAmount" value="{{ $data->routeAmount }}">
                        <input type="hidden" name="price" id="priceHidden" value="{{ $data->price }}">
                        <input type="hidden" name="vendorPrice" id="vendorPriceHidden" value="{{ $data->vendorPrice ?? 0 }}">
                        <input type="hidden" name="vendorPriceSingle" id="vendorPriceSingleHidden" value="{{ $data->vendorPriceSingle ?? 0 }}">
                        <input type="hidden" name="personalVendorPrice" id="personalVendorPriceHidden" value="{{ $data->personalVendorPrice }}">
                        <input type="hidden" name="personalVendorPriceSingle" id="personalVendorPriceSingleHidden" value="{{ $data->personalVendorPriceSingle }}">
                    </div>
                </div>
            </div>

            <!-- Card Informasi Harga -->
            <div class="card card-custom" id="priceInfoCard">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="d-flex align-items-center">
                        <span class="section-icon-title bg-success bg-opacity-10 text-success">
                            <i class="mdi mdi-cash-multiple"></i>
                        </span>
                        <div>
                            <h4 class="mb-0 font-weight-bold text-dark">{{ __('menu_return_do.price_info') }}</h4>
                            <small class="text-muted">{{ __('menu_return_do.price_info_sub') }}</small>
                        </div>
                    </div>
                    
                    <div class="toggle-master-price-box d-flex align-items-center">
                        <input type="hidden" name="update_master_price" value="0">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" name="update_master_price" id="updateMasterPrice" value="1" style="cursor: pointer; width: 2.5em; height: 1.3em;">
                            <label class="form-check-label font-weight-bold text-primary ms-2" for="updateMasterPrice" style="cursor: pointer; user-select: none;">
                                <i class="mdi mdi-refresh me-1"></i> {{ __('menu_return_do.update_master_price') }}
                            </label>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <!-- Fleet Type Info -->
                        <div class="col-md-4">
                            <div class="price-card-box" style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%);">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-white-50 mb-1 small text-uppercase font-weight-bold">{{ __('menu_return_do.fleet_type') }}</p>
                                        <h4 class="text-white mb-0 font-weight-bold" id="fleetTypeDisplay">
                                            {{ $data->fleet?->company?->type ?? '-' }}
                                        </h4>
                                    </div>
                                    <div class="bg-white bg-opacity-10 p-3 rounded-circle">
                                        <i class="mdi mdi-truck-outline fs-2 text-white"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Price Info -->
                        <div class="col-md-4">
                            <div class="price-card-box" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="w-100">
                                        <p class="text-white-50 mb-1 small text-uppercase font-weight-bold">Route Amount</p>
                                        <h4 class="text-white mb-1 font-weight-bold" id="priceDisplay">
                                            Rp {{ number_format($data->routeAmount ?? 0, 0, ',', '.') }}
                                        </h4>
                                        <p class="text-white-50 mb-0 small" id="priceDetailDisplay">
                                            {{ $data->qty }} × Rp {{ number_format($data->price ?? 0, 0, ',', '.') }} = Rp {{ number_format($data->routeAmount ?? 0, 0, ',', '.') }}
                                        </p>
                                    </div>
                                    <div class="bg-white bg-opacity-10 p-3 rounded-circle ms-2">
                                        <i class="mdi mdi-currency-usd fs-2 text-white"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Personal Vendor Price Info -->
                        <div class="col-md-4" id="vendorPriceCard">
                            <div class="price-card-box" style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="w-100">
                                        @php
                                            $isExternalFleet = $data->fleet && $data->fleet->company && strtolower($data->fleet->company->type) === 'external';
                                            $vendorLabel = $isExternalFleet ? 'Vendor Price' : 'Personal Vendor Price';
                                            $vendorSingle = $isExternalFleet ? $data->vendorPriceSingle ?? 0 : $data->personalVendorPriceSingle ?? 0;
                                            $vendorTotal = $isExternalFleet ? $data->vendorPrice ?? 0 : $data->personalVendorPrice ?? 0;
                                        @endphp
                                        <p class="text-white-50 mb-1 small text-uppercase font-weight-bold" id="vendorPriceLabel">{{ $vendorLabel }}</p>
                                        <h4 class="text-white mb-1 font-weight-bold" id="vendorPriceDisplay">
                                            Rp {{ number_format($vendorTotal, 0, ',', '.') }}
                                        </h4>
                                        <p class="text-white-50 mb-0 small" id="vendorPriceDetailDisplay">
                                            {{ $data->qty }} × Rp {{ number_format($vendorSingle, 0, ',', '.') }} = Rp {{ number_format($vendorTotal, 0, ',', '.') }}
                                        </p>
                                    </div>
                                    <div class="bg-white bg-opacity-10 p-3 rounded-circle ms-2">
                                        <i class="mdi mdi-account-cash-outline fs-2 text-white"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Info Note -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="alert alert-primary bg-primary bg-opacity-10 border-0 rounded-3 mb-0 d-flex align-items-center p-3" role="alert">
                                <i class="mdi mdi-information-outline fs-4 text-primary me-2"></i>
                                <div>
                                    <strong class="text-primary me-1">{{ __('menu_return_do.calculation_note') }}:</strong>
                                    <span id="priceNote" class="text-dark">
                                        Harga dihitung berdasarkan route yang dipilih × qty. Fleet type: <strong>{{ $data->fleet?->company?->type ?? '-' }}</strong>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Material Data Card -->
            <div class="card card-custom">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <span class="section-icon-title bg-warning bg-opacity-10 text-warning">
                            <i class="mdi mdi-package-variant-closed"></i>
                        </span>
                        <h4 class="mb-0 font-weight-bold text-dark">{{ __('menu_return_do.material_data') }}</h4>
                    </div>

                    <button class="btn btn-outline-primary btn-sm rounded-pill px-3 font-weight-bold" type="button" id="add-material">
                        <i class="mdi mdi-plus-circle me-1"></i> {{ __('general.add_data') }}
                    </button>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-custom" id="dt">
                            <thead>
                                <tr>
                                    <th style="width: 5%">#</th>
                                    <th style="width: 25%">Material</th>
                                    <th style="width: 20%">Unit</th>
                                    <th style="width: 15%">Qty</th>
                                    <th style="width: 20%">Unit 2</th>
                                    <th style="width: 15%">Qty 2</th>
                                </tr>
                            </thead>
                            <tbody id="materialForm">
                                @if (isset($data->orderMaterial) && count($data->orderMaterial) > 0)
                                    @foreach ($data->orderMaterial as $ordm)
                                        <tr>
                                            <td>
                                                <a href="javascript:deleteOrderMaterial('{{ $ordm->id }}')"
                                                    class="btn btn-icon btn-sm bg-danger-subtle rounded-circle" data-bs-toggle="tooltip"
                                                    title="Delete">
                                                    <i class="mdi mdi-delete fs-14 text-danger"></i>
                                                </a>
                                            </td>
                                            <td>
                                                <select class="form-control js-example-basic-single" name="materialCode[]" id="materialCode_{{ $loop->iteration }}">
                                                    @foreach ($material as $item)
                                                        <option value="{{ $item->code }}" {{ $ordm->materialCode == $item->code ? 'selected' : '' }}>
                                                            {{ $item->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <select class="form-control js-example-basic-single" name="unitCode[]" id="unitCode_{{ $loop->iteration }}">
                                                    <option selected="" disabled="" value="">{{ __('general.choose') }}...</option>
                                                    @foreach ($unit as $item)
                                                        <option value="{{ $item->code }}" {{ $ordm->unitCode == $item->code ? 'selected' : '' }}>
                                                            {{ $item->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input class="form-control form-control-custom" name="materialQty[]" id="materialQty_{{ $loop->iteration }}" type="number"
                                                    value="{{ $ordm->materialQty }}" placeholder="Material Qty">
                                            </td>
                                            <td>
                                                <select class="form-control js-example-basic-single" name="unitCode2[]" id="unitCode2_{{ $loop->iteration }}">
                                                    <option selected="" disabled="" value="">{{ __('general.choose') }}...</option>
                                                    @foreach ($unit as $item)
                                                        <option value="{{ $item->code }}" {{ $ordm->unitCode2 == $item->code ? 'selected' : '' }}>
                                                            {{ $item->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input class="form-control form-control-custom" name="materialQty2[]" id="materialQty2_{{ $loop->iteration }}" type="number"
                                                    value="{{ $ordm->materialQty2 }}" placeholder="Qty">
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td class="remove-btn"></td>
                                        <td>
                                            <select class="js-example-basic-single" name="materialCode[]" id="materialCode_1">
                                                <option selected="" disabled="" value="">{{ __('general.choose') }}...</option>
                                                @foreach ($material as $item)
                                                    <option value="{{ $item->code }}">{{ $item->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select class="js-example-basic-single" name="unitCode[]" id="unitCode_1">
                                                <option selected="" disabled="" value="">{{ __('general.choose') }}...</option>
                                                @foreach ($unit as $item)
                                                    <option value="{{ $item->code }}">{{ $item->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input class="form-control form-control-custom" name="materialQty[]" id="materialQty_1" type="number" placeholder="Material Qty">
                                        </td>
                                        <td>
                                            <select class="js-example-basic-single" name="unitCode2[]" id="unitCode2_1">
                                                <option selected="" disabled="" value="">{{ __('general.choose') }}...</option>
                                                @foreach ($unit as $item)
                                                    <option value="{{ $item->code }}">{{ $item->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input class="form-control form-control-custom" name="materialQty2[]" id="materialQty_1" type="number" placeholder="Qty">
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Biaya Komponen Card -->
            <div class="card card-custom">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <span class="section-icon-title bg-info bg-opacity-10 text-info">
                            <i class="mdi mdi-cash-register"></i>
                        </span>
                        <h4 class="mb-0 font-weight-bold text-dark">{{ __('menu_return_do.cost_components') }}</h4>
                    </div>
                </div>
                <div class="card-body">
                    @include('operational.return-do.components.cost-edit')
                </div>
            </div>

            <!-- Return Order Section -->
            <div class="card card-custom">
                <div class="card-header d-flex align-items-center">
                    <span class="section-icon-title bg-danger bg-opacity-10 text-danger">
                        <i class="mdi mdi-calendar-check"></i>
                    </span>
                    <h4 class="mb-0 font-weight-bold text-dark">{{ __('menu_return_do.confirm_return') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label-custom" for="returnDate">
                                <i class="mdi mdi-calendar-blank text-primary"></i> {{ __('menu_return_do.return_date') }}
                            </label>
                            <input class="form-control form-control-custom" name="returnDate" id="returnDate" type="date"
                                placeholder="{{ __('menu_return_do.return_date') }}"
                                value="{{ $data->returnDate ? date('Y-m-d', strtotime($data->returnDate)) : '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom" for="returnDescription">
                                <i class="mdi mdi-text-box-outline text-primary"></i> {{ __('menu_return_do.return_description') }}
                            </label>
                            <textarea class="form-control form-control-custom" name="returnDescription" id="returnDescription" rows="3"
                                placeholder="{{ __('menu_return_do.return_description') }}...">{{ $data->returnDescription ?? '' }}</textarea>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <label class="form-label-custom" for="suratJalanFiles">
                                <i class="mdi mdi-file-upload-outline text-primary"></i> {{ __('menu_return_do.upload_surat_jalan') }}
                            </label>
                            <input class="form-control form-control-custom" name="suratJalanFiles[]" id="suratJalanFiles" type="file"
                                multiple accept=".pdf,.jpg,.jpeg,.png"
                                title="Upload file surat jalan (PDF, JPG, JPEG, PNG - Max 5MB per file)">
                            <small class="text-muted mt-1 d-block">
                                <i class="mdi mdi-information-outline me-1"></i> Upload file surat jalan dalam format PDF, JPG, JPEG, atau PNG (maksimal 5MB per file)
                            </small>
                        </div>
                    </div>

                    <!-- Hidden field untuk menandai konfirmasi return -->
                    <input type="hidden" name="confirm_return" id="confirmReturn" value="{{ old('confirm_return', '1') }}">

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="p-3 border rounded-3 bg-light d-flex align-items-center">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" id="confirmReturnCheckbox" style="cursor: pointer; width: 2.2em; height: 1.2em;"
                                        {{ (string) old('confirm_return', '1') === '1' ? 'checked' : '' }}
                                        onchange="document.getElementById('confirmReturn').value = this.checked ? '1' : '0'">
                                    <label class="form-check-label font-weight-bold text-dark ms-2" for="confirmReturnCheckbox" style="cursor: pointer;">
                                        Konfirmasi Return Order <span class="text-muted font-weight-normal">- Centang untuk mengkonfirmasi return order ini</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sticky Action Footer Bar -->
            <div class="sticky-bottom-bar d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center">
                    <div class="me-3 d-none d-md-block">
                        <span class="text-muted small d-block">{{ __('menu_order.order_code') }}</span>
                        <strong class="text-dark">{{ $data->code }}</strong>
                    </div>
                    <div class="border-start ps-3 me-3 d-none d-md-block">
                        <span class="text-muted small d-block">{{ __('menu_order.customer') }}</span>
                        <strong class="text-dark">{{ $data->customer?->name ?? '-' }}</strong>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 ms-auto">
                    <a href="{{ route('operational.return-do.index') }}" class="btn btn-light rounded-pill px-4">
                        {{ __('general.cancel') }}
                    </a>
                    <button class="btn btn-save-gradient btn-lg px-4" id="save" type="submit">
                        <i class="mdi mdi-content-save me-1"></i> {{ __('general.save_changes') }}
                    </button>
                </div>
            </div>

        </div>
    </form>
    <form id="delete-form" method="post">
        @csrf
        @method('PUT')
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
        // Format number with 2 decimal places
        function formatNumber(num) {
            return new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(num);
        }

        $(document).ready(function() {
            const selectedType = $('#routeTypeCode').select2('val');
            // loadQty(selectedType)

            // Handle form submit dengan confirmation dan preloader
            $('#edit-form').on('submit', function(e) {
                e.preventDefault();

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

                        // Validate routeAmount
                        const routeAmount = $('input[name="routeAmount"]').val();
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

                        // Sanitize nominal inputs (remove thousand separators) before submit
                        $('.nominal-input').each(function() {
                            const v = $(this).val();
                            if (typeof v === 'string') {
                                $(this).val(v.replace(/\./g, ''));
                            }
                        });

                        // Submit form menggunakan AJAX
                        const formData = new FormData(form[0]);

                        $.ajax({
                            url: form.attr('action'),
                            method: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            dataType: 'json',
                            success: function(response) {
                                const successMessage = response && response.message ?
                                    response
                                    .message : 'Data order berhasil disimpan';
                                const redirectUrl = response && response.redirect_url ?
                                    response
                                    .redirect_url :
                                    "{{ route('operational.return-do.index') }}";

                                hidePreloader();
                                swal({
                                    title: "Sukses",
                                    text: successMessage,
                                    icon: "success",
                                    buttons: false,
                                    timer: 1500
                                }).then(() => {
                                    window.location.href = redirectUrl;
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

        // Store original order price values from server
        const originalRouteCode = "{{ $data->routeCode }}";
        const originalCustomerCode = "{{ $data->customerCode }}";
        const originalOrderPrice = {{ (float)($data->price ?? ($data->qty > 0 ? $data->routeAmount / $data->qty : 0)) }};
        const originalPersonalVendorPriceSingle = {{ (float)($data->personalVendorPriceSingle ?? ($data->qty > 0 ? $data->personalVendorPrice / $data->qty : 0)) }};
        const originalVendorPriceSingle = {{ (float)($data->vendorPriceSingle ?? ($data->qty > 0 ? $data->vendorPrice / $data->qty : 0)) }};

        // Function to update price information
        function updatePriceInfo() {
            const fleetCode = $('#fleetCode').val();
            const routeCode = $('#routeData').val();
            const customerCode = $('#customerCode').val();
            const qty = parseFloat($('#qty').val()) || 1;
            const isUpdateMaster = $('#updateMasterPrice').is(':checked');

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
                        // Update fleet type
                        $('#fleetTypeDisplay').text(response.fleetType || '-');

                        const isExternal = response.isExternal === true;
                        const isRouteChanged = (routeCode !== originalRouteCode);
                        const isCustomerChanged = (customerCode && customerCode !== originalCustomerCode);
                        const useMaster = isUpdateMaster || isRouteChanged || isCustomerChanged;

                        let unitPrice = 0;
                        let routeAmount = 0;
                        let vendorSingle = 0;
                        let vendorTotal = 0;
                        let priceBadge = '';

                        if (useMaster) {
                            unitPrice = response.price || 0;
                            routeAmount = response.routeAmount || (unitPrice * qty);
                            
                            if (isExternal) {
                                vendorSingle = response.vendorPriceSingle || 0;
                                vendorTotal = response.vendorPrice || (vendorSingle * qty);
                            } else {
                                vendorSingle = response.personalVendorPriceSingle || 0;
                                vendorTotal = response.personalVendorPrice || (vendorSingle * qty);
                            }
                            priceBadge = '<span class="badge bg-success text-white ms-1"><i class="mdi mdi-check-circle me-1"></i>Harga Master</span>';
                        } else {
                            unitPrice = (originalOrderPrice > 0) ? originalOrderPrice : (response.price || 0);
                            routeAmount = unitPrice * qty;

                            if (isExternal) {
                                vendorSingle = (originalVendorPriceSingle > 0) ? originalVendorPriceSingle : (response.vendorPriceSingle || 0);
                                vendorTotal = vendorSingle * qty;
                            } else {
                                vendorSingle = (originalPersonalVendorPriceSingle > 0) ? originalPersonalVendorPriceSingle : (response.personalVendorPriceSingle || 0);
                                vendorTotal = vendorSingle * qty;
                            }

                            let masterDiffNote = '';
                            if (response.price && Math.abs(response.price - unitPrice) > 0.01) {
                                masterDiffNote = ' (Master: Rp ' + formatNumber(response.price) + ')';
                            }
                            priceBadge = '<span class="badge bg-secondary text-white ms-1"><i class="mdi mdi-history me-1"></i>Harga Order' + masterDiffNote + '</span>';
                        }

                        const vendorLabel = isExternal ? 'Vendor Price' : 'Personal Vendor Price';

                        // Update price displays
                        $('#priceDisplay').html('Rp ' + formatNumber(routeAmount) + ' ' + priceBadge);
                        $('#priceDetailDisplay').text(qty + ' × Rp ' + formatNumber(unitPrice) + ' = Rp ' + formatNumber(routeAmount));

                        $('#vendorPriceLabel').text(vendorLabel);
                        $('#vendorPriceDisplay').text('Rp ' + formatNumber(vendorTotal));
                        $('#vendorPriceDetailDisplay').text(qty + ' × Rp ' + formatNumber(vendorSingle) + ' = Rp ' + formatNumber(vendorTotal));

                        // Update hidden form inputs
                        $('input[name="price"]').val(unitPrice);
                        $('input[name="routeAmount"]').val(routeAmount);
                        if (isExternal) {
                            $('input[name="vendorPrice"]').val(vendorTotal);
                            $('input[name="vendorPriceSingle"]').val(vendorSingle);
                            $('input[name="personalVendorPrice"]').val(0);
                            $('input[name="personalVendorPriceSingle"]').val(0);
                        } else {
                            $('input[name="vendorPrice"]').val(0);
                            $('input[name="vendorPriceSingle"]').val(0);
                            $('input[name="personalVendorPrice"]').val(vendorTotal);
                            $('input[name="personalVendorPriceSingle"]').val(vendorSingle);
                        }

                        // Always show vendor price card
                        $('#vendorPriceCard').show();

                        let noteText = '';
                        if (useMaster) {
                            noteText = 'Harga dihitung dari <strong>Master Rute Terbaru</strong> (Rp ' + formatNumber(unitPrice) + ') × Qty (' + qty + '). Fleet type: <strong>' + response.fleetType + '</strong>';
                        } else {
                            noteText = 'Harga dihitung menggunakan <strong>Harga Order Satuan Lama</strong> (Rp ' + formatNumber(unitPrice) + ') × Qty (' + qty + '). Centang "Update Harga dari Master Terbaru" jika ingin memperbarui harga satuan. Fleet type: <strong>' + response.fleetType + '</strong>';
                        }
                        $('#priceNote').html(noteText);
                    }
                },
                error: function(xhr) {
                    console.error('Error calculating price:', xhr);
                }
            });
        }

        // Attach event listeners for real-time updates
        $('#fleetCode, #routeData, #qty, #updateMasterPrice, #customerCode').on('change keyup', function() {
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

        // Update when customer is selected
        $('#customerCode').on('select2:select', function() {
            updatePriceInfo();
        });

        // File upload validation for surat jalan
        $('#suratJalanFiles').on('change', function() {
            const files = this.files;
            const maxFileSize = 5 * 1024 * 1024; // 5MB in bytes
            const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];

            for (let i = 0; i < files.length; i++) {
                const file = files[i];

                // Check file size
                if (file.size > maxFileSize) {
                    swal({
                        title: "File Terlalu Besar",
                        text: `File "${file.name}" melebihi batas maksimal 5MB`,
                        icon: "error",
                        button: "OK"
                    });
                    this.value = ""; // Clear the file input
                    return;
                }

                // Check file type
                if (!allowedTypes.includes(file.type)) {
                    swal({
                        title: "Tipe File Tidak Valid",
                        text: `File "${file.name}" harus berformat PDF, JPG, JPEG, atau PNG`,
                        icon: "error",
                        button: "OK"
                    });
                    this.value = ""; // Clear the file input
                    return;
                }
            }
        });

        // Handle return confirmation checkbox
        $('#confirmReturnCheckbox').on('change', function() {
            const isChecked = this.checked;

            if (isChecked) {
                // Set current date if returnDate is empty
                if (!$('#returnDate').val()) {
                    const now = new Date();
                    const dateString = now.toISOString().slice(0, 10);
                    $('#returnDate').val(dateString);
                }

                swal({
                    title: "Konfirmasi Return",
                    text: "Dengan mencentang ini, order akan dikonfirmasi sebagai return setelah data disimpan. Pastikan data return sudah benar!",
                    icon: "warning",
                    buttons: {
                        cancel: "Batal",
                        confirm: "Ya, Saya Yakin"
                    }
                }).then((confirmed) => {
                    if (!confirmed) {
                        this.checked = false;
                        $('#confirmReturn').val('0');
                    }
                });
            }
        });
    </script>
@endpush
