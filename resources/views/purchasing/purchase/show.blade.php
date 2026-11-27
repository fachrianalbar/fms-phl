@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Supplier',
    'secondSegment' => 'Detail',
])

@push('style')
    <style>
        .detail-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
            background: #ffffff;
            margin-bottom: 1.25rem;
            overflow: hidden;
        }
        .detail-card .card-header {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 1.4rem;
        }
        .detail-card .card-body {
            padding: 1.4rem;
        }
        .detail-label {
            font-weight: 700;
            color: #64748b;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 0.25rem;
        }
        .detail-value {
            font-size: 0.95rem;
            font-weight: 600;
            color: #1e293b;
        }
        .detail-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
        }
        .detail-table thead th {
            background: #f8fafc;
            color: #475569;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 10px 14px;
            border-bottom: 2px solid #e2e8f0;
        }
        .detail-table tbody td {
            padding: 10px 14px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            font-size: 13px;
            vertical-align: middle;
        }
        .detail-table tfoot td {
            padding: 10px 14px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
        }

        /* ── Dark mode ─────────────────────────────── */
        html[data-bs-theme="dark"] .detail-card {
            background: var(--bs-card-bg);
            border-color: var(--bs-border-color);
        }
        html[data-bs-theme="dark"] .detail-card .card-header {
            background: var(--bs-secondary-bg);
            border-bottom-color: var(--bs-border-color);
        }
        html[data-bs-theme="dark"] .detail-label {
            color: var(--bs-secondary-color);
        }
        html[data-bs-theme="dark"] .detail-value {
            color: var(--bs-body-color);
        }
        html[data-bs-theme="dark"] .detail-table {
            border-color: var(--bs-border-color);
        }
        html[data-bs-theme="dark"] .detail-table thead th,
        html[data-bs-theme="dark"] .detail-table tfoot td {
            background: var(--bs-tertiary-bg);
            border-color: var(--bs-border-color);
            color: var(--bs-body-color);
        }
        html[data-bs-theme="dark"] .detail-table tbody td {
            border-bottom-color: var(--bs-border-color);
            color: var(--bs-body-color);
        }
    </style>
@endpush

@section('content')
    @php
        $subtotal = (float) ($data->subtotal ?: $totalPrice);
        $ppnRate = (float) ($data->ppn_rate ?? 0);
        $ppnAmount = (float) ($data->ppn_amount ?? 0);
        $grandTotal = (float) ($data->nominal ?: ($subtotal + $ppnAmount));
        $paidAmount = (float) ($data->paidAmount ?? 0);
        $remaining = max(0, $grandTotal - $paidAmount);
        $ppnRateFormatted = rtrim(rtrim(number_format($ppnRate, 2, ',', '.'), '0'), ',');
        $canEdit = ! ($paidAmount > 0 || $data->status == 3);
    @endphp

    <div class="col-sm-12">
        <div class="card detail-card">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="mb-0 fw-bold text-dark">
                        Detail {{ $title }}: <span class="font-monospace text-primary">{{ $data->code }}</span>
                    </h5>
                    @if ($data->paymentStatus === 'Paid')
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill fs-11 px-2 py-1">Lunas</span>
                    @elseif ($data->paymentStatus === 'Partial')
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill fs-11 px-2 py-1">Sebagian</span>
                    @else
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill fs-11 px-2 py-1">Belum Bayar</span>
                    @endif
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route($view . 'index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                        <i class="mdi mdi-arrow-left me-1"></i>{{ __('general.back_to_list') }}
                    </a>
                    @if ($canEdit)
                        <a href="{{ route($view . 'edit', $data->id) }}" class="btn btn-primary btn-sm rounded-pill px-3">
                            <i class="mdi mdi-pencil-outline me-1"></i>Edit
                        </a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-3 col-sm-6">
                        <div class="detail-label">Kode Pembelian</div>
                        <div class="detail-value font-monospace">{{ $data->code }}</div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="detail-label">{{ __('menu_purchase.date') }} & {{ __('menu_purchase.time') }}</div>
                        <div class="detail-value">
                            {{ $data->date ? \Carbon\Carbon::parse($data->date)->format('d F Y') : '-' }}
                            @if ($data->time)
                                <small class="text-muted font-monospace">({{ $data->time }})</small>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="detail-label">Supplier</div>
                        <div class="detail-value">{{ $data->supplier->name ?? '-' }}</div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="detail-label">{{ __('menu_purchase.due_date') }}</div>
                        <div class="detail-value">{{ $data->dueDate ? \Carbon\Carbon::parse($data->dueDate)->format('d F Y') : '-' }}</div>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <div class="detail-label">Nopol / Armada</div>
                        <div class="detail-value">
                            @if ($data->fleet)
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace fs-12 px-2 py-1">
                                    {{ $data->fleet->plateNumber }}
                                </span>
                                @if ($data->fleet->brand)
                                    <small class="text-muted d-block mt-1">{{ $data->fleet->brand->name }}</small>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="detail-label">Pemilik Kendaraan</div>
                        <div class="detail-value">{{ optional($data->fleet?->company)->name ?? '-' }}</div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="detail-label">Subtotal (Sebelum PPN)</div>
                        <div class="detail-value font-monospace">Rp {{ number_format($subtotal, 0, ',', '.') }}</div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="detail-label">PPN ({{ $ppnRateFormatted }}%)</div>
                        <div class="detail-value font-monospace">Rp {{ number_format($ppnAmount, 0, ',', '.') }}</div>
                    </div>

                    <div class="col-md-4 col-sm-6">
                        <div class="detail-label">Jumlah Setelah PPN</div>
                        <div class="detail-value text-primary fs-5 font-monospace fw-bold">
                            Rp {{ number_format($grandTotal, 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <div class="detail-label">Sudah Dibayar (Paid)</div>
                        <div class="detail-value text-success font-monospace">Rp {{ number_format($paidAmount, 0, ',', '.') }}</div>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <div class="detail-label">Sisa Tagihan (Remaining)</div>
                        <div class="detail-value text-danger font-monospace">Rp {{ number_format($remaining, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card detail-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark">Detail Item</h5>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill fs-12 px-3 py-1">
                    {{ $data->details->count() }} Item
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table detail-table mb-0">
                        <thead>
                            <tr>
                                <th width="5%" class="text-center">#</th>
                                <th>Item / Part</th>
                                <th width="12%" class="text-center">Qty</th>
                                <th width="18%" class="text-end">{{ __('menu_purchase.prices') }}</th>
                                <th width="20%" class="text-end">{{ __('menu_purchase.total_prices') }}</th>
                                <th width="20%">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data->details as $item)
                                <tr>
                                    <td class="text-center text-muted fw-semibold">{{ $loop->iteration }}</td>
                                    <td>
                                        <span class="font-monospace text-primary fw-semibold">{{ $item->itemCode }}</span>
                                        — {{ $item->item->name ?? '' }}
                                    </td>
                                    <td class="text-center font-monospace">{{ rtrim(rtrim(number_format((float) $item->qty, 1, ',', '.'), '0'), ',') }}</td>
                                    <td class="text-end font-monospace">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                    <td class="text-end font-monospace fw-semibold">Rp {{ number_format($item->price * $item->qty, 0, ',', '.') }}</td>
                                    <td>{{ $item->description ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">Tidak ada item.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="fw-bold">
                            <tr>
                                <td colspan="2" class="text-end text-uppercase">Total Qty / Subtotal</td>
                                <td class="text-center font-monospace">{{ rtrim(rtrim(number_format((float) $totalQty, 1, ',', '.'), '0'), ',') }}</td>
                                <td class="text-end text-uppercase">Subtotal:</td>
                                <td class="text-end font-monospace">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end text-uppercase">PPN ({{ $ppnRateFormatted }}%):</td>
                                <td class="text-end font-monospace">Rp {{ number_format($ppnAmount, 0, ',', '.') }}</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end text-uppercase">Jumlah Setelah PPN:</td>
                                <td class="text-end font-monospace text-primary fs-15">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
