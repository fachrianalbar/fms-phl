@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Supplier',
    'secondSegment' => __('general.detail'),
])

@push('style')
    <style>
        .direct-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
            background: #ffffff;
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        .direct-card .card-header {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 1.4rem;
        }
        .direct-card .card-body {
            padding: 1.4rem;
        }
        .header-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }
        .header-icon-primary {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
        }
        .info-label {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 0.25rem;
        }
        .info-value {
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
            padding: 11px 14px;
            border-bottom: 2px solid #e2e8f0;
        }
        .detail-table tbody td {
            padding: 11px 14px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            font-size: 13px;
            vertical-align: middle;
        }
        .detail-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* ── Dark Mode ── */
        html[data-bs-theme="dark"] .direct-card {
            background: var(--bs-card-bg);
            border-color: var(--bs-border-color);
        }
        html[data-bs-theme="dark"] .direct-card .card-header {
            background: var(--bs-secondary-bg);
            border-bottom-color: var(--bs-border-color);
        }
        html[data-bs-theme="dark"] .info-label {
            color: var(--bs-secondary-color);
        }
        html[data-bs-theme="dark"] .info-value {
            color: var(--bs-body-color);
        }
        html[data-bs-theme="dark"] .detail-table {
            border-color: var(--bs-border-color);
        }
        html[data-bs-theme="dark"] .detail-table thead th {
            background: var(--bs-tertiary-bg);
            border-bottom-color: var(--bs-border-color);
            color: var(--bs-body-color);
        }
        html[data-bs-theme="dark"] .detail-table tbody td {
            border-bottom-color: var(--bs-border-color);
            color: var(--bs-body-color);
        }
    </style>
@endpush

@section('content')
    <div class="col-sm-12">
        {{-- Card Header & Aksi --}}
        <div class="card direct-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <span class="header-icon header-icon-primary">
                        <i class="mdi mdi-cart-arrow-right"></i>
                    </span>
                    <div>
                        <h5 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                            Pembelian Langsung: <span class="font-monospace text-primary">{{ $data->code }}</span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill fs-11 px-2 py-1">
                                <i class="mdi mdi-check-circle me-1"></i>Lunas (Terpasang ke Mobil)
                            </span>
                        </h5>
                        <small class="text-muted">Transaksi pembelian suku cadang yang langsung dipasang di mobil dan tercatat sebagai pemeliharaan armada</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route($view . 'index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                        <i class="mdi mdi-arrow-left me-1"></i> Kembali ke Daftar
                    </a>
                    <a href="{{ route($view . 'edit', $data->id) }}" class="btn btn-primary btn-sm rounded-pill px-3">
                        <i class="mdi mdi-pencil me-1"></i> Edit Data
                    </a>
                </div>
            </div>

            <div class="card-body">
                <div class="row g-4">
                    {{-- Baris Info 1 --}}
                    <div class="col-md-4">
                        <div class="info-label">Mobil / Plat Nomor Armada</div>
                        <div class="info-value">
                            @if ($data->fleet)
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill fs-13 font-monospace px-3 py-1">
                                    <i class="mdi mdi-truck me-1"></i>{{ $data->fleet->plateNumber }}
                                </span>
                                @if ($data->fleet->brand)
                                    <small class="text-muted d-block mt-1">{{ $data->fleet->brand->name }}</small>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-label">Tanggal & Jam Pembelian</div>
                        <div class="info-value">
                            <i class="mdi mdi-calendar-clock text-muted me-1"></i>
                            {{ \Carbon\Carbon::parse($data->date)->format('d F Y') }}
                            <small class="text-muted font-monospace">({{ $data->time }})</small>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-label">Supplier / Toko</div>
                        <div class="info-value">
                            <i class="mdi mdi-factory text-muted me-1"></i>
                            {{ optional($data->supplier)->name ?? '-' }}
                        </div>
                    </div>

                    {{-- Baris Info 2 --}}
                    <div class="col-md-4">
                        <div class="info-label">Sumber Dana / Kas-Bank</div>
                        <div class="info-value">
                            @if ($data->userBank)
                                <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill fs-12 px-3 py-1">
                                    <i class="mdi mdi-bank me-1"></i>{{ optional($data->userBank->bank)->name ?? 'Bank' }} - {{ $data->userBank->accountName }}
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary rounded-pill fs-12 px-3 py-1">
                                    <i class="mdi mdi-cash me-1"></i>Tunai Langsung
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-label">Servis Pemeliharaan (Maintenance)</div>
                        <div class="info-value">
                            @php $maintenance = $data->maintenances->first(); @endphp
                            @if ($maintenance)
                                <a href="{{ route('warehouse.maintenance.index') }}" class="badge bg-info-subtle text-info border border-info-subtle rounded-pill fs-12 font-monospace px-3 py-1 text-decoration-none">
                                    <i class="mdi mdi-tools me-1"></i>{{ $maintenance->code }}
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-label">Total Biaya Pembelian</div>
                        <div class="info-value font-monospace fw-bold fs-16 text-success">
                            Rp {{ number_format($data->nominal, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card Rincian Suku Cadang --}}
        <div class="card direct-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="header-icon header-icon-primary" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);">
                        <i class="mdi mdi-wrench"></i>
                    </span>
                    <div>
                        <h5 class="mb-0 fw-bold text-dark">Rincian Suku Cadang Terpasang</h5>
                        <small class="text-muted">Item spare part yang langsung digunakan pada unit armada</small>
                    </div>
                </div>
                <div>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill fs-12">
                        {{ count($data->details) }} Jenis Suku Cadang
                    </span>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table detail-table mb-0">
                        <thead>
                            <tr>
                                <th style="width: 50px" class="text-center">#</th>
                                <th style="width: 140px">Kode Part</th>
                                <th>Nama Suku Cadang</th>
                                <th style="width: 120px" class="text-center">Kuantitas</th>
                                <th style="width: 160px" class="text-end">Harga Satuan</th>
                                <th style="width: 180px" class="text-end">Total Biaya</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalQty = 0;
                                $grandTotal = 0;
                            @endphp
                            @foreach ($data->details as $index => $item)
                                @php
                                    $subtotal = $item->qty * $item->price;
                                    $totalQty += $item->qty;
                                    $grandTotal += $subtotal;
                                @endphp
                                <tr>
                                    <td class="text-center text-muted fw-bold">{{ $index + 1 }}</td>
                                    <td>
                                        <span class="font-monospace fw-semibold text-primary">{{ $item->itemCode }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ optional($item->item)->name ?? '-' }}</strong>
                                    </td>
                                    <td class="text-center font-monospace fw-semibold">
                                        {{ number_format($item->qty, 1, ',', '.') }}
                                    </td>
                                    <td class="text-end font-monospace">
                                        Rp {{ number_format($item->price, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end font-monospace fw-bold text-dark">
                                        Rp {{ number_format($subtotal, 0, ',', '.') }}
                                    </td>
                                    <td>
                                        {{ $item->description ?: '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background: #f8fafc; font-weight: 700;">
                                <td colspan="3" class="text-end text-uppercase" style="padding: 12px 14px;">Total Kuantitas:</td>
                                <td class="text-center font-monospace" style="padding: 12px 14px;">{{ number_format($totalQty, 1, ',', '.') }}</td>
                                <td class="text-end text-uppercase" style="padding: 12px 14px;">Grand Total:</td>
                                <td class="text-end font-monospace fs-15 text-success" style="padding: 12px 14px;">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
