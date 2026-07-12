@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Finance',
    'secondSegment' => 'Order Payment Details',
])

@php
    use App\Models\Data\Route;
@endphp

@push('style')
    <style>
        .finance-card-summary {
            transition: all 0.3s ease;
        }
        .finance-card-summary:hover {
            transform: translateY(-2px);
        }
        .light-bg-detail {
            background-color: #f8f9fa;
        }
    </style>
@endpush

@section('content')
    <div class="col-sm-12">
        <!-- Header Info & Actions -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                            <span class="badge bg-primary px-3 py-2 fs-6 fw-bold">Order: {{ $data->code }}</span>
                            @php
                                $grandTotal = $orderPayment['grand_total'];
                                $payment = $orderPayment['payment'];

                                $paymentStatus = 'Belum Bayar';
                                $badgeClass = 'bg-danger text-white';

                                if ($payment > 0) {
                                    if ($payment == $grandTotal) {
                                        $paymentStatus = 'Lunas';
                                        $badgeClass = 'bg-success text-white';
                                    } elseif ($payment > $grandTotal) {
                                        $paymentStatus = 'Kelebihan Bayar';
                                        $badgeClass = 'bg-info text-white';
                                    } else {
                                        $paymentStatus = 'Belum Lunas';
                                        $badgeClass = 'bg-warning text-dark';
                                    }
                                }
                            @endphp
                            <span class="badge rounded-pill {{ $badgeClass }} px-3 py-2 fs-7 fw-bold">{{ $paymentStatus }}</span>
                        </div>
                        <h4 class="text-dark fw-bold mb-0">Rincian Pembayaran Order</h4>
                        <span class="text-muted small">Kelola dan pantau detail tagihan serta riwayat transaksi pembayaran.</span>
                    </div>
                    <div>
                        <a href="{{ route($view . 'index') }}" class="btn btn-outline-secondary px-4 py-2 fw-semibold">
                            <i class="mdi mdi-arrow-left me-1"></i> Kembali ke Daftar
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left Side: Operational & Payment Details -->
            <div class="col-lg-8">
                <!-- Operational Details -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-0 pt-4 pb-0">
                        <h5 class="card-title fw-bold text-dark mb-0">
                            <i class="mdi mdi-truck-delivery text-primary me-2"></i>Detail Operasional & Rute
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6 col-lg-4">
                                <div class="p-3 border rounded light-bg-detail h-100">
                                    <span class="text-muted d-block small mb-1">Customer</span>
                                    <span class="fw-bold text-dark">{{ $data->customer->name }}</span>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="p-3 border rounded light-bg-detail h-100">
                                    <span class="text-muted d-block small mb-1">No. Polisi / Plat</span>
                                    <span class="fw-bold text-dark">{{ $data->fleet->plateNumber ?? '-' }}</span>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="p-3 border rounded light-bg-detail h-100">
                                    <span class="text-muted d-block small mb-1">Driver</span>
                                    <span class="fw-bold text-dark">{{ $data->driver->name ?? '-' }}</span>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4">
                                <div class="p-3 border rounded light-bg-detail h-100">
                                    <span class="text-muted d-block small mb-1">Tanggal Order</span>
                                    <span class="fw-bold text-dark">{{ \Carbon\Carbon::parse($data->orderDate)->format('d-m-Y') }}</span>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="p-3 border rounded light-bg-detail h-100">
                                    <span class="text-muted d-block small mb-1">No. Surat Jalan</span>
                                    <span class="fw-bold text-dark">{{ $data->shipmentNumber ?? '-' }}</span>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="p-3 border rounded light-bg-detail h-100">
                                    <span class="text-muted d-block small mb-1">Tipe Rute & Qty</span>
                                    @php
                                        $routeTypeLabel = '';
                                        if ($data->route->routeTypeCode == 'TONASE') {
                                            $routeTypeLabel = 'Tonase';
                                        } elseif ($data->route->routeTypeCode == 'TRIP') {
                                            $routeTypeLabel = 'Trip';
                                        } else {
                                            $routeTypeLabel = 'Kubik';
                                        }
                                    @endphp
                                    <span class="fw-bold text-dark">{{ $routeTypeLabel }}: {{ number_format($data->qty, 2, ',', '.') }}</span>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="p-3 border rounded light-bg-detail">
                                    <span class="text-muted d-block small mb-1">Rute Perjalanan</span>
                                    <span class="fw-bold text-dark fs-6">
                                        {{ $route->originLocation->name ?? '-' }} 
                                        <i class="mdi mdi-arrow-right mx-2 text-primary"></i> 
                                        {{ $route->destinationLocation->name ?? '-' }}
                                    </span>
                                </div>
                            </div>

                            @if($data->notes)
                                <div class="col-md-12">
                                    <div class="p-3 border rounded light-bg-detail">
                                        <span class="text-muted d-block small mb-1">Catatan</span>
                                        <span class="text-dark">{{ $data->notes }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Biaya Tambahan (On Charge) Breakdown -->
                @php
                    $onChargeCosts = $data->cost->filter(fn($c) => strtolower($c->type ?? '') === 'on charge');
                @endphp
                @if($onChargeCosts->isNotEmpty())
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-transparent border-0 pt-4 pb-0">
                            <h5 class="card-title fw-bold text-dark mb-0">
                                <i class="mdi mdi-playlist-plus text-primary me-2"></i>Rincian Biaya Tambahan (On Charge)
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr class="table-light">
                                            <th style="width: 5%">#</th>
                                            <th>Komponen Biaya</th>
                                            <th>Keterangan</th>
                                            <th class="text-end" style="width: 25%">Nominal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($onChargeCosts as $c)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    <span class="fw-semibold text-dark">{{ $c->costComponent->name ?? 'Custom Component' }}</span>
                                                </td>
                                                <td><span class="text-muted small">{{ $c->description ?? '-' }}</span></td>
                                                <td class="text-end fw-bold text-dark">Rp {{ number_format($c->nominal, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="table-light">
                                            <td colspan="3" class="text-end fw-bold">Total Biaya Tambahan</td>
                                            <td class="text-end text-primary fw-bold">Rp {{ number_format($onChargeCosts->sum('nominal'), 0, ',', '.') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Payment History -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-0 pt-4 pb-0">
                        <h5 class="card-title fw-bold text-dark mb-0">
                            <i class="mdi mdi-history text-primary me-2"></i>Riwayat Pembayaran
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        @if($data->orderPaymentHistory->isEmpty())
                            <div class="text-center py-4">
                                <i class="mdi mdi-credit-card-off fs-1 text-muted d-block mb-2"></i>
                                <span class="text-muted small">Belum ada riwayat pembayaran untuk order ini.</span>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr class="table-light">
                                            <th style="width: 5%">No</th>
                                            <th>Tanggal Bayar</th>
                                            <th>Tipe</th>
                                            <th>Bank Akun</th>
                                            <th>Keterangan</th>
                                            <th class="text-end" style="width: 25%">Nominal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($data->orderPaymentHistory as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ \Carbon\Carbon::parse($item->date)->format('d-m-Y') }}</td>
                                                <td>
                                                    <span class="badge {{ $item->paymentType === 'Full' ? 'bg-success text-white' : 'bg-primary text-white' }} px-2 py-1 fs-8">
                                                        {{ $item->paymentType }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="text-dark small fw-semibold">
                                                        {{ $item?->userBank?->accountNumber }} - {{ $item?->userBank?->bank?->name }}
                                                    </span>
                                                    <span class="text-muted d-block small">
                                                        a/n {{ $item?->userBank?->accountName }}
                                                    </span>
                                                </td>
                                                <td><span class="text-muted small">{{ $item->description ?? '-' }}</span></td>
                                                <td class="text-end fw-bold text-success">
                                                    Rp {{ number_format($item->total, 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Side: Financial Breakdown Summary -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm finance-card-summary mb-4">
                    <div class="card-header bg-transparent border-0 pt-4 pb-0">
                        <h5 class="card-title fw-bold text-dark mb-0">
                            <i class="mdi mdi-cash-multiple text-primary me-2"></i>Rincian Keuangan
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="list-group list-group-flush mb-3">
                            <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-2">
                                <span class="text-muted">Harga Rute</span>
                                <span class="fw-semibold text-dark">Rp {{ number_format($orderPayment['cost'], 0, ',', '.') }}</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-2">
                                <span class="text-muted">Biaya Tambahan</span>
                                <span class="fw-semibold text-dark">Rp {{ number_format($orderPayment['additional_cost'] ?? 0, 0, ',', '.') }}</span>
                            </div>
                            
                            <hr class="my-2 text-muted opacity-25">
                            
                            <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-2">
                                <span class="fw-bold text-dark">Subtotal</span>
                                <span class="fw-bold text-dark">Rp {{ number_format($orderPayment['cost'] + ($orderPayment['additional_cost'] ?? 0), 0, ',', '.') }}</span>
                            </div>
                            
                            <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-2">
                                <span class="text-muted">PPN (+)</span>
                                <span class="fw-semibold text-success">+ Rp {{ number_format($orderPayment['ppn'] ?? 0, 0, ',', '.') }}</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-2">
                                <span class="text-muted">PPH (-)</span>
                                <span class="fw-semibold text-danger">- Rp {{ number_format($orderPayment['pph'] ?? 0, 0, ',', '.') }}</span>
                            </div>
                            
                            <hr class="my-2 text-muted opacity-25">
                            
                            <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-2">
                                <span class="fw-bold text-dark fs-6">Total Tagihan</span>
                                <span class="fw-bold text-dark fs-6">Rp {{ number_format($orderPayment['grand_total'], 0, ',', '.') }}</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-2">
                                <span class="text-muted">Sudah Dibayar</span>
                                <span class="fw-semibold text-success">Rp {{ number_format($orderPayment['payment'], 0, ',', '.') }}</span>
                            </div>
                            
                            <hr class="my-2 text-muted opacity-25">
                            
                            @php
                                $sisaTagihan = $orderPayment['total'];
                                if ($sisaTagihan < 0) {
                                    $sisaClass = 'text-info bg-info-subtle border border-info-subtle';
                                    $sisaLabel = 'Kelebihan Bayar';
                                    $sisaValue = 'Rp ' . number_format(abs($sisaTagihan), 0, ',', '.');
                                } elseif ($sisaTagihan > 0) {
                                    $sisaClass = 'text-danger bg-danger-subtle border border-danger-subtle';
                                    $sisaLabel = 'Sisa Tagihan';
                                    $sisaValue = 'Rp ' . number_format($sisaTagihan, 0, ',', '.');
                                } else {
                                    $sisaClass = 'text-success bg-success-subtle border border-success-subtle';
                                    $sisaLabel = 'Lunas';
                                    $sisaValue = 'Rp 0';
                                }
                            @endphp
                            <div class="list-group-item d-flex justify-content-between align-items-center rounded-3 p-3 mt-3 {{ $sisaClass }}">
                                <span class="fw-bold fs-7">{{ $sisaLabel }}</span>
                                <span class="fw-bold fs-5">{{ $sisaValue }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
