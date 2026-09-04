@extends('layouts.main', [
'title' => $title,
'pageTitle' => $title,
'firstSegment' => 'Faktur',
'secondSegment' => 'Edit Payment',
])

@php
use App\Models\Data\Route;
use App\Models\Data\TonaseBonus;
use Carbon\Carbon;
@endphp

@push('style')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/select2.css') }}">

<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom-select2.css') }}">
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
@include('invoice.partials.table-style')
@endpush

@section('content')
<div class="col-sm-12">
    @include('partials.alert')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>{{ $title }} Data</h4>

            <a href="{{ route('invoice.payment.index') }}" class="btn btn-info">{{ __('general.back_to_list') }}</a>

        </div>
        <div class="card-body col-md-6">
            <form class="row g-3" method="post" action="{{ route('invoice.payment.update', $data->id) }}"
                enctype="multipart/form-data" onsubmit="return submitForm('amount')">
                @csrf
                @method('PUT')

                <div class="col-md-12 position-relative">
                    <label class="form-label" for="customerCode">Customer Name</label>
                    <select class="js-example-basic-single" id="customerCode" disabled>
                        <option selected="" disabled="" value="">{{ __('general.choose') }}...</option>
                        @foreach ($customer as $item)
                        <option value="{{ $item->code }}"
                            {{ $data->customerCode == $item->code ? 'selected' : '' }}>
                            {{ $item->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="invoiceNumber">Invoice Number</label>
                    <input class="form-control" name="invoiceNumber" id="invoiceNumber" type="text" required
                        placeholder="Invoice Number" value="{{ $data->invoiceNumber }}" disabled>
                </div>

                <input type="hidden" readonly name="totalPrice" value="{{ $totalPrice }}">
                <div class="col-md-12">
                    <label class="form-label">Total Price</label>
                    <input class="form-control" type="text" disabled placeholder="Total Price" readonly
                        value="{{ number_format($totalPrice, 0, ',', '.') }}">
                </div>

                <div class="col-md-12 position-relative">
                    <label class="form-label" for="userBankCode">User Bank
                        <i class="icofont icofont-warning-alt text-danger"></i></label>
                    <select class="js-example-basic-single" name="userBankCode" id="userBankCode" required>
                        <option selected="" disabled="" value="">{{ __('general.choose') }}...</option>
                        @foreach ($userBank as $item)
                        <option value="{{ $item->code }}">
                            {{ $item->bank->name . ' - ' . $item->accountNumber . ' - ' . $item->accountName }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="amount">Payment amount <i
                            class="icofont icofont-warning-alt text-danger"></i></label>
                    <input class="form-control" name="amount" id="amount" type="text" required
                        placeholder="Payment amount" oninput="formatAngka(this)">
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="paymentDate">Invoice Payment Date <i
                            class="icofont icofont-warning-alt text-danger"></i></label>
                    <input class="form-control" name="paymentDate" id="datetime-local" type="date" required
                        placeholder="Invoice Date">
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="description">Description</label>
                    <textarea class="form-control" name="description" id="description" placeholder="Description" rows="4"></textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="paymentReceipt">Payment Receipt</label>
                    <input class="form-control" name="paymentReceipt" id="paymentReceipt" type="file"
                        accept=".jpg, .jpeg, .png">
                </div>

                @if ($status != 2)
                <div class="col-12">
                    <button class="btn btn-primary" type="submit">Save</button>
                </div>
                @endif
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Order Data</h4>
        </div>
        <div class="card-body col-md-12 p-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle w-100 nowrap invoice-table" id="dt-order">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Order Date</th>
                        <th>Origin</th>
                        <th>Destination</th>
                        <th>Shipment No</th>
                        <th>Plate No</th>
                        <th>Total Cost</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ Carbon::parse($item->orderDate)->format('d-M-Y') }}</td>
                        <td>{{ $item->route->originLocation->name }}</td>
                        <td>{{ $item->route->destinationLocation->name }}</td>
                        <td>{{ $item->shipmentNumber }}</td>
                        <td>{{ $item->fleet->plateNumber }}</td>
                        <td>{{ $item->totalPrice }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
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
<script src="{{ asset('assets/js/flat-pickr/flatpickr.js') }}"></script>
<script src="{{ asset('assets/js/flat-pickr/custom-flatpickr.js') }}"></script>
<script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
<script src="{{ asset('assets/js/select2/select2-custom.js') }}"></script>
<script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>
<script src="{{ asset('assets/js/helper.js') }}"></script>

<script>
    $('#dt-order').DataTable();
</script>
@endpush
