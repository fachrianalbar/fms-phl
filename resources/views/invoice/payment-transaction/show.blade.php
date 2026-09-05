@extends('layouts.main', [
'title' => $title,
'pageTitle' => $title,
'firstSegment' => 'Faktur',
'secondSegment' => 'Detail Transaksi Pembayaran',
])

@push('style')
@include('invoice.partials.table-style')

<style>
    .trx-info-label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        font-weight: 700;
    }
    .trx-info-value {
        font-size: 14px;
        font-weight: 600;
        color: #0f172a;
    }
</style>
@endpush

@section('content')
<div class="col-sm-12">
    @include('partials.alert')

    <!-- Card 1: Informasi Transaksi -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="mdi mdi-receipt-text-check-outline me-2 text-primary"></i>Detail Transaksi Pembayaran</h4>
            <div class="d-flex gap-2">
                <a href="{{ route('invoice.payment-transaction.index') }}" class="btn btn-info btn-sm">{{ __('general.back_to_list') }}</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="trx-info-label">Kode Transaksi</div>
                    <div class="trx-info-value font-monospace text-primary">{{ $transaction->code }}</div>
                </div>
                <div class="col-md-3">
                    <div class="trx-info-label">Tanggal Pembayaran</div>
                    <div class="trx-info-value">
                        {{ $transaction->paymentDate ? \Carbon\Carbon::parse($transaction->paymentDate)->format('d M Y') : '-' }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="trx-info-label">Customer</div>
                    <div class="trx-info-value">{{ $transaction->customer->name ?? '-' }}</div>
                    <div class="text-muted font-monospace fs-11">{{ $transaction->customerCode }}</div>
                </div>
                <div class="col-md-3">
                    <div class="trx-info-label">Bank Penerima</div>
                    <div class="trx-info-value">
                        {{ $transaction->userBank->bank->name ?? '-' }}
                    </div>
                    <div class="text-muted font-monospace fs-11">{{ $transaction->userBank->accountNumber ?? '' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="trx-info-label">Keterangan</div>
                    <div class="trx-info-value fw-normal">{{ $transaction->description ?: '-' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="trx-info-label">Bukti Transfer / Pembayaran</div>
                    <div class="trx-info-value fw-normal">
                        @if ($transaction->paymentReceipt)
                            <a href="{{ Storage::disk('public')->url('invoice-payment/' . $transaction->paymentReceipt) }}"
                                target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                <i class="mdi mdi-file-download-outline me-1"></i>{{ $transaction->paymentReceipt }}
                            </a>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 2: Daftar Faktur dalam Transaksi -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="mdi mdi-file-document-multiple-outline me-2 text-primary"></i>Daftar Faktur ({{ count($rows) }})</h4>
        </div>
        <div class="card-body p-3">
            <div class="table-responsive custom-scrollbar">
                <table class="table table-hover align-middle w-100 invoice-table">
                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th>No. Faktur</th>
                            <th>Tgl Faktur</th>
                            <th class="text-end">Total Tagihan</th>
                            <th class="text-end">Dibayar (Transaksi Ini)</th>
                            <th class="text-end">Claim (Transaksi Ini)</th>
                            <th class="text-end">Sisa Tagihan</th>
                            <th class="text-center">Status Faktur</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $i => $row)
                        <tr>
                            <td class="text-center text-muted">{{ $i + 1 }}</td>
                            <td><span class="font-monospace fw-bold text-primary fs-13">{{ $row['invoiceNumber'] }}</span></td>
                            <td class="fs-12 text-nowrap">
                                {{ $row['invoiceDate'] ? \Carbon\Carbon::parse($row['invoiceDate'])->format('d M Y') : '-' }}
                            </td>
                            <td class="text-end fw-semibold text-dark fs-13">Rp {{ number_format($row['billing'], 0, ',', '.') }}</td>
                            <td class="text-end fw-semibold text-success font-monospace fs-13">
                                Rp {{ number_format($row['paidInTrx'], 0, ',', '.') }}
                            </td>
                            <td class="text-end fw-semibold text-warning-emphasis font-monospace fs-13">
                                @if ($row['claimInTrx'] > 0)
                                    - Rp {{ number_format($row['claimInTrx'], 0, ',', '.') }}
                                @else
                                    <span class="text-muted opacity-50">-</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold font-monospace fs-13 {{ $row['remaining'] > 0 ? 'text-danger' : 'text-success' }}">
                                Rp {{ number_format($row['remaining'], 0, ',', '.') }}
                            </td>
                            <td class="text-center">
                                @if ($row['status'] == \App\Models\Finance\Invoice::STATUS_FULL)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 fw-semibold fs-11"><i class="mdi mdi-check-circle me-1"></i>Lunas</span>
                                @elseif ($row['status'] == \App\Models\Finance\Invoice::STATUS_PARTIAL)
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-1 fw-semibold fs-11"><i class="mdi mdi-clock-check-outline me-1"></i>Sebagian</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-1 fw-semibold fs-11"><i class="mdi mdi-file-document-outline me-1"></i>Belum Bayar</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Tidak ada faktur dalam transaksi ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Card 3: Rincian Claim -->
    @if ($transaction->claims->count() > 0)
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="mdi mdi-cash-refund me-2 text-warning"></i>Rincian Claim (Biaya Lain-lain Pengurang Tagihan)</h4>
        </div>
        <div class="card-body p-3">
            <div class="table-responsive custom-scrollbar">
                <table class="table table-hover align-middle w-100 invoice-table">
                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th>No. Faktur</th>
                            <th>Keterangan</th>
                            <th class="text-end">Nominal Claim</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transaction->claims as $i => $claim)
                        <tr>
                            <td class="text-center text-muted">{{ $i + 1 }}</td>
                            <td><span class="font-monospace fw-bold text-primary fs-13">{{ $claim->invoice->invoiceNumber ?? $claim->invoiceCode }}</span></td>
                            <td class="fs-12">
                                @if ($claim->description)
                                    {{ $claim->description }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end fw-semibold text-warning-emphasis font-monospace fs-13">Rp {{ number_format($claim->amount, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Card 4: Ringkasan -->
    <div class="card mb-4">
        <div class="card-header">
            <h4 class="mb-0"><i class="mdi mdi-calculator-variant-outline me-2 text-primary"></i>Ringkasan Transaksi</h4>
        </div>
        <div class="card-body">
            <div class="row g-3 justify-content-end">
                <div class="col-md-6">
                    <div class="trx-summary-box p-3" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px;">
                        <div class="d-flex justify-content-between py-1 fs-13">
                            <span class="text-muted">Total Tagihan Faktur</span>
                            <span class="fw-semibold text-dark font-monospace">Rp {{ number_format($sumBilling, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1 fs-13">
                            <span class="text-muted">Dibayar pada Transaksi Ini</span>
                            <span class="fw-semibold text-success font-monospace">Rp {{ number_format($sumPaidInTrx, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1 fs-13">
                            <span class="text-muted">Claim (Pengurang Tagihan)</span>
                            <span class="fw-semibold text-warning-emphasis font-monospace">- Rp {{ number_format($sumClaimInTrx, 0, ',', '.') }}</span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between py-1">
                            <span class="fw-bold text-dark fs-14">Total Uang Diterima (Kas Masuk)</span>
                            <span class="fw-bold text-success font-monospace fs-15">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
