@extends('layouts.main', [
'title' => $title,
'pageTitle' => $title,
'firstSegment' => $title,
'secondSegment' => 'Detail',
])

@push('style')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}">
<style>
    .detail-label { font-weight: 600; color: #6c757d; font-size: 0.85rem; text-transform: uppercase; margin-bottom: 0.25rem; }
    .detail-value { font-size: 1rem; font-weight: 500; margin-bottom: 1rem; color: #212529; }
</style>
@endpush

@section('content')
<div class="col-sm-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Detail {{ $title }}</h4>
            <a href="{{ route($view . 'index') }}" class="btn btn-info"><i class="mdi mdi-arrow-left me-1"></i>{{ __('general.back_to_list') }}</a>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-12">
                            <div class="detail-label">Code</div>
                            <div class="detail-value">{{ $data->code }}</div>
                        </div>
                        <div class="col-6">
                            <div class="detail-label">{{ __('menu_purchase.date') }}</div>
                            <div class="detail-value">{{ \Carbon\Carbon::parse($data->date)->format('d F Y') }}</div>
                        </div>
                        <div class="col-6">
                            <div class="detail-label">{{ __('menu_purchase.time') }}</div>
                            <div class="detail-value">{{ $data->time }}</div>
                        </div>
                        <div class="col-12">
                            <div class="detail-label">Supplier</div>
                            <div class="detail-value">{{ $data->supplier->name ?? '-' }}</div>
                        </div>
                        <div class="col-12">
                            <div class="detail-label">{{ __('menu_purchase.due_date') }}</div>
                            <div class="detail-value">{{ \Carbon\Carbon::parse($data->dueDate)->format('d F Y') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-12">
                            <div class="detail-label">{{ __('menu_purchase.total_prices') }}</div>
                            <div class="detail-value text-primary fs-4">Rp {{ number_format($totalPrice, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-6">
                            <div class="detail-label">Sudah Dibayar (Paid)</div>
                            <div class="detail-value text-success">Rp {{ number_format($data->paidAmount, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-6">
                            <div class="detail-label">Sisa (Remaining)</div>
                            <div class="detail-value text-danger">Rp {{ number_format($totalPrice - $data->paidAmount, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-12 mt-3">
                            <div class="detail-label">Status Pembayaran</div>
                            <div class="detail-value">
                                @if($data->paymentStatus == 'Paid')
                                    <span class="badge bg-success fs-6">Paid</span>
                                @elseif($data->paymentStatus == 'Partial')
                                    <span class="badge bg-warning text-dark fs-6">Partial</span>
                                @else
                                    <span class="badge bg-secondary fs-6">Unpaid</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h4>Detail Item</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="5%" class="text-center">#</th>
                            <th>Item/Part</th>
                            <th width="15%" class="text-center">Qty</th>
                            <th width="20%" class="text-end">{{ __('menu_purchase.prices') }}</th>
                            <th width="25%" class="text-end">{{ __('menu_purchase.total_prices') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data->details as $item)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $item->itemCode }} - {{ $item->item->name ?? '' }}</td>
                            <td class="text-center">{{ $item->qty }}</td>
                            <td class="text-end">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($item->price * $item->qty, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">No items found</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="2" class="text-end">Total</td>
                            <td class="text-center">{{ $totalQty }}</td>
                            <td></td>
                            <td class="text-end">Rp {{ number_format($totalPrice, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
