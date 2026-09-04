@extends('layouts.main', [
    'title' => 'Edit Faktur - ' . $data->invoiceNumber,
    'pageTitle' => 'Edit Faktur',
    'firstSegment' => 'Faktur',
    'secondSegment' => 'Edit Faktur',
])

@php
use Carbon\Carbon;

// Prepare attached orders structured data for calculations and chips
$attachedOrdersData = [];
$initialTotalBase = 0;
$initialTotalOnCharge = 0;
$initialComponents = [];

foreach ($order as $item) {
    $bp = (float) ($item->routeAmount ?? $item->price ?? 0);
    $oc = 0;
    $costsArr = [];
    if (isset($item->cost)) {
        foreach ($item->cost as $c) {
            if (isset($c->type) && strtolower($c->type) === 'on charge') {
                $nom = (float) $c->nominal;
                $oc += $nom;
                $compName = $c->costComponent->name ?? ($c->description ?? 'Biaya Tambahan');
                $initialComponents[$compName] = ($initialComponents[$compName] ?? 0) + $nom;
                $costsArr[] = [
                    'component' => $compName,
                    'nominal' => $nom,
                    'nominalFormatted' => 'Rp ' . number_format($nom, 0, ',', '.'),
                    'description' => $c->description ?? '',
                ];
            }
        }
    }
    $initialTotalBase += $bp;
    $initialTotalOnCharge += $oc;
    $attachedOrdersData[] = [
        'id' => $item->id,
        'code' => $item->code,
        'shipment' => $item->shipmentNumber ?? $item->code,
        'plate' => $item->fleet->plateNumber ?? '-',
        'route' => ($item->route && $item->route->originLocation ? $item->route->originLocation->name : '-') . ' ➔ ' . ($item->route && $item->route->destinationLocation ? $item->route->destinationLocation->name : '-'),
        'basePrice' => $bp,
        'basePriceFormatted' => 'Rp ' . number_format($bp, 0, ',', '.'),
        'onCharge' => $oc,
        'onChargeFormatted' => 'Rp ' . number_format($oc, 0, ',', '.'),
        'costs' => $costsArr,
        'total' => $bp + $oc,
        'totalFormatted' => 'Rp ' . number_format($bp + $oc, 0, ',', '.'),
    ];
}

$customerPpnRate = (float) ($customerData->ppn ?? 0);
$customerPphRate = (float) ($customerData->pph ?? 0);
$customerDueDateDuration = (int) ($customerData->dueDateDuration ?? 30);
@endphp

@push('style')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/select2.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom-select2.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/sweetalert2.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('assets/libs/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css') }}">
@include('invoice.partials.table-style')

<style>
    :root {
        --invoice-primary: #3051d3;
        --invoice-primary-soft: #eef2ff;
        --invoice-dark: #1e293b;
    }

    .card-modern {
        border: 1px solid #e9edf4;
        border-radius: 12px;
        box-shadow: 0 4px 20px -2px rgba(30, 41, 59, 0.05);
        transition: all 0.2s ease;
        background: #ffffff;
    }

    .card-header-modern {
        background: #ffffff;
        border-bottom: 1px solid #f1f5f9;
        padding: 1.1rem 1.4rem;
        border-top-left-radius: 12px !important;
        border-top-right-radius: 12px !important;
    }

    .form-section-title {
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        font-weight: 700;
        color: #64748b;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-section-title::after {
        content: "";
        flex: 1;
        height: 1px;
        background: #e2e8f0;
    }

    /* Tax Toggle Card */
    .tax-toggle-card {
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        padding: 1rem;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: space-between;
        user-select: none;
    }

    .tax-toggle-card:hover {
        border-color: #cbd5e1;
        background: #f1f5f9;
        transform: translateY(-1px);
    }

    .tax-toggle-card.active {
        border-color: var(--invoice-primary);
        background: var(--invoice-primary-soft);
        box-shadow: 0 2px 8px rgba(48, 81, 211, 0.12);
    }

    .tax-toggle-card.active .tax-title {
        color: var(--invoice-primary);
        font-weight: 600;
    }

    /* Customer Quick Info Card */
    .customer-info-box {
        background: linear-gradient(145deg, #f8fafc 0%, #f1f5f9 100%);
        border: 1px solid #e2e8f0;
        border-left: 4px solid var(--invoice-primary);
        border-radius: 10px;
        padding: 1rem 1.25rem;
    }

    /* Sticky Summary Sidebar */
    .sticky-summary {
        position: sticky;
        top: 85px;
        z-index: 9;
    }

    .summary-card {
        border: none;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 10px 30px -5px rgba(30, 41, 59, 0.12);
        background: #ffffff;
    }

    .summary-header {
        background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
        padding: 1.35rem 1.5rem;
        color: #ffffff;
        position: relative;
    }

    .summary-header::after {
        content: "";
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #60a5fa, #93c5fd);
    }

    .grand-total-box {
        background: #f0fdf4;
        border: 1.5px solid #86efac;
        border-radius: 10px;
        padding: 1rem 1.25rem;
        transition: all 0.3s ease;
    }

    .grand-total-amount {
        font-size: 1.65rem;
        font-weight: 800;
        color: #15803d;
        letter-spacing: -0.02em;
    }

    /* Selected Order Chips */
    .chip-item {
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        color: #334155;
        border-radius: 20px;
        padding: 0.25rem 0.65rem;
        font-size: 0.75rem;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        transition: all 0.15s ease;
    }

    .chip-item:hover {
        background: #e2e8f0;
    }

    /* Floating Step Badge */
    .step-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.78rem;
        font-weight: 600;
        padding: 0.35rem 0.8rem;
        border-radius: 20px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">
    <!-- Header Banner -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h3 class="fw-bold text-dark mb-0">Edit Faktur: <span class="font-monospace text-primary">{{ $data->invoiceNumber }}</span></h3>
                @if ($status == 1 || (int)$data->status == 2)
                    <span class="badge bg-success">Full Payment</span>
                @elseif ((int)$data->status == 1)
                    <span class="badge bg-warning">Partial Payment</span>
                @else
                    <span class="badge bg-secondary">Invoice Created</span>
                @endif
            </div>
            <p class="text-muted fs-13 mb-0">Perbarui parameter faktur, pajak, dan kelola daftar pesanan surat jalan yang ditagihkan.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a target="_blank" href="{{ route('invoice.pdf', $data->id) }}" class="btn btn-danger btn-sm shadow-sm px-3 d-flex align-items-center gap-1">
                <i class="mdi mdi-file-pdf-box fs-16"></i>
                <span>Cetak PDF</span>
            </a>
            <button type="button" class="btn btn-warning btn-sm shadow-sm px-3 d-flex align-items-center gap-1" onclick="recalculateInvoice()">
                <i class="mdi mdi-calculator fs-16"></i>
                <span>Hitung Ulang</span>
            </button>
            <a href="{{ route('invoice.unpaid') }}" class="btn btn-outline-secondary btn-sm px-3 d-flex align-items-center gap-1">
                <i class="mdi mdi-arrow-left fs-16"></i>
                <span>Kembali ke Daftar</span>
            </a>
        </div>
    </div>

    @include('partials.alert')

    <!-- Main Edit Form -->
    <form id="invoiceEditForm" method="post" action="{{ route('invoice.update', $data->id) }}">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <!-- Left Column: Form Fields & Order Table (Col 8) -->
            <div class="col-xl-8 col-lg-7">
                
                <!-- Card 1: Informasi Faktur & Pelanggan -->
                <div class="card card-modern mb-4">
                    <div class="card-header card-header-modern d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="step-pill bg-primary-subtle text-primary border border-primary-subtle">
                                <i class="mdi mdi-numeric-1-circle fs-15"></i> Langkah 1
                            </span>
                            <h5 class="mb-0 fw-bold text-dark fs-15">Informasi Faktur & Parameter Pajak</h5>
                        </div>
                        <span class="text-muted fs-12">Kode: <strong class="font-monospace text-dark">{{ $data->code }}</strong></span>
                    </div>

                    <div class="card-body p-4">
                        <!-- Section: Parameter Pajak -->
                        <div class="form-section-title">
                            <i class="mdi mdi-percent-outline text-primary"></i> Parameter Pajak (PPN & PPh)
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="tax-toggle-card {{ $data->usePpn ? 'active' : '' }}" id="ppnCard">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="form-check form-switch mb-0">
                                            <input type="hidden" name="usePpn" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="usePpn" name="usePpn" value="1" {{ $data->usePpn ? 'checked' : '' }}>
                                        </div>
                                        <div>
                                            <div class="tax-title fs-14 fw-bold text-dark" id="ppnLabel">
                                                {{ $customerPpnRate > 0 ? 'PPN (' . $customerPpnRate . '%)' : 'PPN' }}
                                            </div>
                                            <small class="text-muted fs-11">Pajak Pertambahan Nilai</small>
                                        </div>
                                    </div>
                                    <span class="badge bg-primary text-white rounded-pill px-2 py-1 fs-11" id="badgePpnRate">
                                        {{ $customerPpnRate }}%
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="tax-toggle-card {{ $data->usePph ? 'active' : '' }}" id="pphCard">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="form-check form-switch mb-0">
                                            <input type="hidden" name="usePph" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="usePph" name="usePph" value="1" {{ $data->usePph ? 'checked' : '' }}>
                                        </div>
                                        <div>
                                            <div class="tax-title fs-14 fw-bold text-dark" id="pphLabel">
                                                {{ $customerPphRate > 0 ? 'PPh 23 (' . $customerPphRate . '%)' : 'PPh 23' }}
                                            </div>
                                            <small class="text-muted fs-11">Potongan PPh Pasal 23</small>
                                        </div>
                                    </div>
                                    <span class="badge bg-warning text-dark rounded-pill px-2 py-1 fs-11" id="badgePphRate">
                                        {{ $customerPphRate }}%
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Section: Detail Pelanggan -->
                        <div class="form-section-title">
                            <i class="mdi mdi-domain text-primary"></i> Data Pelanggan Terkait
                        </div>

                        <div class="customer-info-box mb-4">
                            <div class="row g-2 align-items-center">
                                <div class="col-md-7">
                                    <span class="text-muted fs-11 text-uppercase fw-bold">Nama Pelanggan</span>
                                    <h6 class="fw-bold text-dark mb-1 fs-14">{{ $customerData->name ?? $data->customer->name ?? '-' }}</h6>
                                    <p class="text-muted fs-12 mb-0">
                                        <i class="mdi mdi-map-marker-outline text-primary me-1"></i>
                                        {{ $customerData->billingAddress ?? $customerData->officeAddress ?? 'Tidak ada alamat penagihan tercatat' }}
                                    </p>
                                </div>
                                <div class="col-md-5 border-start-md ps-md-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="text-muted fs-12">Kode Pelanggan:</span>
                                        <span class="fw-bold text-primary font-monospace fs-12">{{ $customerData->code ?? $data->customerCode }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted fs-12">Term of Payment:</span>
                                        <span class="fw-bold text-dark fs-12">{{ $customerDueDateDuration }} Hari</span>
                                    </div>
                                </div>
                            </div>

                            @if ($customerData && $customerData->pic && $customerData->pic->count() > 0)
                                <hr class="my-2 border-dashed">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="text-muted fs-11 fw-semibold">PIC Penagihan:</span>
                                    @foreach ($customerData->pic as $pic)
                                        @if ($pic->picName || $pic->phone)
                                            <span class="badge bg-light text-dark border px-2 py-1 fs-11">
                                                <i class="mdi mdi-account-circle text-primary me-1"></i>{{ $pic->picName ?? 'PIC' }}
                                                @if ($pic->phone) <span class="text-muted">({{ $pic->phone }})</span> @endif
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- Section: Nomor & Tanggal Faktur -->
                        <div class="form-section-title">
                            <i class="mdi mdi-calendar-clock text-primary"></i> Nomor & Tanggal Faktur
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-dark fw-semibold fs-13" for="invoiceNumber">
                                    Nomor Faktur / Invoice <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="mdi mdi-lock-outline text-muted"></i>
                                    </span>
                                    <input class="form-control font-monospace fw-bold text-dark bg-light" name="invoiceNumber" id="invoiceNumber" type="text" required value="{{ $data->invoiceNumber }}" readonly>
                                </div>
                                <small class="text-muted fs-11">Nomor faktur resmi bersifat tetap.</small>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label text-dark fw-semibold fs-13" for="invoiceDate">
                                    Tanggal Faktur <span class="text-danger">*</span>
                                </label>
                                <input class="form-control" name="invoiceDate" id="invoiceDate" type="date" required value="{{ $data->invoiceDate }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label text-dark fw-semibold fs-13" for="overdueDate">
                                    Jatuh Tempo <span class="text-danger">*</span>
                                </label>
                                <input class="form-control bg-light" name="overdueDate" id="overdueDate" type="date" required value="{{ $data->overdueDate }}">
                            </div>
                        </div>

                        <!-- Section: Referensi Tambahan -->
                        <div class="form-section-title mt-4">
                            <i class="mdi mdi-note-text-outline text-primary"></i> Referensi & Catatan Tambahan
                        </div>

                        <div class="row g-3 mb-2">
                            <div class="col-md-6">
                                <label class="form-label text-dark fs-13" for="poNumber">Nomor PO (Opsional)</label>
                                <input class="form-control" name="poNumber" id="poNumber" type="text" placeholder="Contoh: PO-2026-001" value="{{ $data->poNumber }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-dark fs-13" for="receiptNumber">Nomor Kwitansi (Opsional)</label>
                                <input class="form-control" name="receiptNumber" id="receiptNumber" type="text" placeholder="Contoh: KWT-001" value="{{ $data->receiptNumber }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label text-dark fs-13" for="notes">Catatan Faktur</label>
                                <textarea class="form-control" name="notes" id="notes" rows="2" placeholder="Catatan opsional yang dicetak pada invoice...">{{ $data->notes }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Order Management Table -->
                <div class="card card-modern">
                    <div class="card-header card-header-modern d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="step-pill bg-success-subtle text-success border border-success-subtle">
                                <i class="mdi mdi-numeric-2-circle fs-15"></i> Langkah 2
                            </span>
                            <h5 class="mb-0 fw-bold text-dark fs-15">Daftar Pesanan dalam Faktur Ini</h5>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fs-12">
                                {{ count($attachedOrdersData) }} Pesanan Terlampir
                            </span>
                            @if ($status == 0)
                                <button type="button" class="btn btn-sm btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddOrder" id="openModalButton">
                                    <i class="mdi mdi-plus-circle-outline me-1"></i>Tambah Pesanan ke Faktur
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="card-body p-3">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle w-100 invoice-table" id="dt-order">
                                <thead class="table-light">
                                    <tr>
                                        @if ($status == 0)
                                            <th width="40" class="text-center">Aksi</th>
                                        @endif
                                        <th width="35">No</th>
                                        <th>Tanggal Order</th>
                                        <th>Rute (Asal ➔ Tujuan)</th>
                                        <th>No. Surat Jalan</th>
                                        <th>No. Polisi</th>
                                        <th class="text-end">Tarif Rute</th>
                                        <th class="text-end">Biaya On Charge</th>
                                        <th class="text-end">Total Tagihan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($attachedOrdersData as $ord)
                                        <tr>
                                            @if ($status == 0)
                                                <td class="text-center">
                                                    <a href="javascript:deleteInvoiceDetail('{{ $ord['id'] }}')" class="btn btn-icon btn-sm bg-danger-subtle" data-bs-toggle="tooltip" title="Hapus dari Faktur">
                                                        <i class="mdi mdi-delete fs-14 text-danger"></i>
                                                    </a>
                                                </td>
                                            @endif
                                            <td>{{ $loop->iteration }}</td>
                                            <td><span class="fw-semibold text-dark">{{ Carbon::parse($order[$loop->index]->orderDate)->format('d-M-Y') }}</span></td>
                                            <td>
                                                <div class="d-flex align-items-center gap-1 fs-12">
                                                    <span class="badge bg-light text-dark border">{{ $order[$loop->index]->route->originLocation->name ?? '-' }}</span>
                                                    <i class="mdi mdi-arrow-right text-primary"></i>
                                                    <span class="badge bg-light text-dark border">{{ $order[$loop->index]->route->destinationLocation->name ?? '-' }}</span>
                                                </div>
                                            </td>
                                            <td><span class="badge bg-light text-dark border font-monospace">{{ $ord['shipment'] }}</span></td>
                                            <td>
                                                @if ($ord['plate'] && $ord['plate'] !== '-')
                                                    <span class="badge bg-secondary-subtle text-secondary"><i class="mdi mdi-truck me-1"></i>{{ $ord['plate'] }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-end"><span class="text-dark fw-medium">{{ $ord['basePriceFormatted'] }}</span></td>
                                            <td class="text-end">
                                                @if ($ord['onCharge'] > 0)
                                                    <div class="d-flex flex-column align-items-end">
                                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle fw-semibold mb-1">+ {{ $ord['onChargeFormatted'] }}</span>
                                                        <button type="button" class="btn btn-xs btn-outline-info py-0 px-2 btn-order-cost-detail"
                                                            data-code="{{ $ord['code'] }}"
                                                            data-shipment="{{ $ord['shipment'] }}"
                                                            data-base-price="{{ number_format($ord['basePrice'], 0, ',', '.') }}"
                                                            data-on-charge="{{ number_format($ord['onCharge'], 0, ',', '.') }}"
                                                            data-total="{{ number_format($ord['total'], 0, ',', '.') }}"
                                                            data-costs="{{ htmlspecialchars(json_encode($ord['costs']), ENT_QUOTES, 'UTF-8') }}"
                                                            title="Lihat Rincian Biaya On Charge">
                                                            <i class="mdi mdi-receipt-text-outline me-1"></i>{{ count($ord['costs']) }} Rincian
                                                        </button>
                                                    </div>
                                                @else
                                                    <span class="text-muted fs-12">-</span>
                                                @endif
                                            </td>
                                            <td class="text-end"><span class="fw-bold text-primary fs-13">{{ $ord['totalFormatted'] }}</span></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ $status == 0 ? '9' : '8' }}" class="text-center py-4 text-muted">
                                                <i class="mdi mdi-alert-circle-outline fs-24 d-block mb-1"></i>
                                                Belum ada pesanan yang terlampir pada faktur ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Sticky Summary & Action Buttons (Col 4) -->
            <div class="col-xl-4 col-lg-5">
                <div class="sticky-summary">
                    <div class="card summary-card">
                        <div class="summary-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-white text-primary fw-bold px-2 py-1 fs-10 mb-1">RINGKASAN FAKTUR</span>
                                    <h5 class="text-white mb-0 fw-bold">Kalkulasi Tagihan</h5>
                                </div>
                                <div class="avatar-sm">
                                    <span class="avatar-title rounded-circle bg-white text-primary fs-18 shadow-sm">
                                        <i class="mdi mdi-calculator"></i>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-4">
                            <!-- Quick Header Preview -->
                            <div class="p-3 bg-light rounded-3 mb-3 border">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-muted fs-12">No. Faktur:</span>
                                    <span class="fw-bold text-primary font-monospace fs-12">{{ $data->invoiceNumber }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-muted fs-12">Pelanggan:</span>
                                    <span class="fw-semibold text-dark text-truncate fs-12 ms-2" style="max-width: 170px;">{{ $customerData->name ?? $data->customer->name ?? '-' }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted fs-12">Jatuh Tempo:</span>
                                    <span class="text-dark fs-12 fw-medium" id="previewOverdueDate">{{ $data->overdueDate }}</span>
                                </div>
                            </div>

                            <!-- Cost Breakdown -->
                            <div class="cost-breakdown mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted fs-13">Pesanan Terlampir:</span>
                                    <span class="fw-bold text-dark fs-13">{{ count($attachedOrdersData) }} Pesanan</span>
                                </div>

                                <!-- Total Tarif Rute Pokok -->
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted fs-13">Total Tarif Rute:</span>
                                    <span class="fw-semibold text-dark fs-14" id="summaryBasePrice">Rp {{ number_format($initialTotalBase, 0, ',', '.') }}</span>
                                </div>

                                <!-- Total Biaya On Charge -->
                                <div class="mb-2" id="rowOnCharge">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted fs-13 d-flex align-items-center gap-1">
                                            <span>Biaya On Charge:</span>
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle fs-10 px-1 py-0 rounded" id="summaryOnChargeBadge">
                                                {{ count($initialComponents) }} jenis
                                            </span>
                                        </span>
                                        <span class="fw-semibold text-warning fs-14" id="summaryOnCharge">
                                            {{ $initialTotalOnCharge > 0 ? '+ ' : '' }}Rp {{ number_format($initialTotalOnCharge, 0, ',', '.') }}
                                        </span>
                                    </div>
                                    
                                    <!-- Collapsible itemized list of on charge components -->
                                    <div id="onChargeBreakdownCard" class="mt-2 p-2 rounded bg-light border border-warning-subtle fs-12 {{ $initialTotalOnCharge > 0 ? '' : 'd-none' }}">
                                        <div class="d-flex justify-content-between align-items-center mb-1 text-muted fw-bold fs-11">
                                            <span>RINCIAN ON CHARGE:</span>
                                            <span>({{ count($initialComponents) }})</span>
                                        </div>
                                        <div id="onChargeBreakdownList" class="d-flex flex-column gap-1 custom-scrollbar" style="max-height: 120px; overflow-y: auto;">
                                            @foreach ($initialComponents as $cName => $cNominal)
                                                <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light">
                                                    <span class="text-truncate me-2" title="{{ $cName }}">• {{ $cName }}</span>
                                                    <span class="fw-semibold text-warning text-nowrap">Rp {{ number_format($cNominal, 0, ',', '.') }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-2 pt-1 border-top">
                                    <span class="text-dark fw-bold fs-13">Subtotal DPP:</span>
                                    <span class="fw-bold text-dark fs-14" id="summarySubtotal">Rp {{ number_format($initialTotalBase + $initialTotalOnCharge, 0, ',', '.') }}</span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-2 {{ $data->usePpn ? '' : 'opacity-50 text-muted' }}" id="rowPpn">
                                    <span class="text-muted fs-13 d-flex align-items-center">
                                        <span class="badge bg-primary-subtle text-primary me-1 fs-10" id="summaryPpnBadge">{{ $customerPpnRate }}%</span> PPN:
                                    </span>
                                    <span class="fw-semibold text-primary fs-14" id="summaryPpn">+ Rp {{ number_format($data->ppnAmount, 0, ',', '.') }}</span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-2 {{ $data->usePph ? '' : 'opacity-50 text-muted' }}" id="rowPph">
                                    <span class="text-muted fs-13 d-flex align-items-center">
                                        <span class="badge bg-warning-subtle text-warning me-1 fs-10" id="summaryPphBadge">{{ $customerPphRate }}%</span> PPh 23:
                                    </span>
                                    <span class="fw-semibold text-danger fs-14" id="summaryPph">- Rp {{ number_format($data->pphAmount, 0, ',', '.') }}</span>
                                </div>

                                <hr class="my-3 border-dashed">

                                <!-- Grand Total Box -->
                                <div class="grand-total-box mb-3">
                                    <div class="text-muted fs-11 fw-bold text-uppercase mb-1">TOTAL TAGIHAN (GRAND TOTAL)</div>
                                    <div class="grand-total-amount" id="summaryGrandTotal">
                                        Rp {{ number_format(($initialTotalBase + $initialTotalOnCharge) + (float)$data->ppnAmount - (float)$data->pphAmount, 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>

                            <!-- Attached Order Chips Section -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fs-11 fw-bold text-muted text-uppercase">Pesanan Terlampir:</span>
                                    <small class="text-muted">({{ count($attachedOrdersData) }})</small>
                                </div>
                                <div class="d-flex flex-wrap gap-1 custom-scrollbar" style="max-height: 100px; overflow-y: auto;">
                                    @forelse ($attachedOrdersData as $ord)
                                        <span class="chip-item" title="{{ $ord['code'] }} - {{ $ord['totalFormatted'] }}">
                                            <span>{{ $ord['shipment'] }}</span>
                                            @if ($ord['onCharge'] > 0)
                                                <i class="mdi mdi-cash-plus text-warning" title="Ada Biaya On Charge"></i>
                                            @endif
                                        </span>
                                    @empty
                                        <span class="text-muted fs-12 fst-italic">Belum ada pesanan</span>
                                    @endforelse
                                </div>
                            </div>

                            <!-- Submit Action Button -->
                            <button type="submit" class="btn btn-primary w-100 py-2 fs-15 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2" id="btnSubmitEditInvoice">
                                <i class="mdi mdi-content-save-check-outline fs-18"></i>
                                <span>Simpan Perubahan Faktur</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Modal Tambah Pesanan ke Faktur Ini (modal-xl) -->
<div class="modal fade" id="modalAddOrder" tabindex="-1" aria-labelledby="modalAddOrderLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl mt-4">
        <form method="post" action="{{ route('invoice.detail.store', $data->id) }}" id="formAddOrderModal" class="w-100">
            @csrf
            @method('PUT')
            <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
                <div class="modal-header bg-primary text-white py-3 px-4 border-0">
                    <div class="d-flex align-items-center gap-2">
                        <span class="avatar-sm">
                            <span class="avatar-title rounded-circle bg-white text-primary fs-18">
                                <i class="mdi mdi-truck-plus-outline"></i>
                            </span>
                        </span>
                        <div>
                            <h5 class="modal-title fw-bold text-white mb-0" id="modalAddOrderLabel">Tambah Pesanan ke Faktur Ini</h5>
                            <small class="text-white-50">Pelanggan: {{ $customerData->name ?? $data->customer->name ?? '-' }} (Pilih order yang belum difakturkan)</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 bg-info-subtle py-2 px-3 mb-3 d-flex align-items-center gap-2 fs-12 text-info">
                        <i class="mdi mdi-information-outline fs-16"></i>
                        <span>Centang order yang ingin ditambahkan ke faktur ini, kemudian klik tombol <strong>Tambahkan ke Faktur</strong>.</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle w-100 invoice-table" id="dt">
                            <thead class="table-light">
                                <tr>
                                    <th width="35" class="text-center">#</th>
                                    <th width="35">No</th>
                                    <th>Tanggal Order</th>
                                    <th>Rute (Asal ➔ Tujuan)</th>
                                    <th>No. Surat Jalan</th>
                                    <th>No. Polisi</th>
                                    <th class="text-end">Tarif Rute</th>
                                    <th class="text-end">Biaya On Charge</th>
                                    <th class="text-end">Total Tagihan</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-footer bg-light px-4 py-3 border-0 d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="saveInvoice" class="btn btn-primary px-4 fw-bold shadow-sm">
                        <i class="mdi mdi-plus-circle me-1"></i>Tambahkan Pesanan ke Faktur
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Detail Biaya On Charge per Order -->
<div class="modal fade" id="modalOrderCostDetail" tabindex="-1" aria-labelledby="modalOrderCostDetailLabel" aria-hidden="true">
    <div class="modal-dialog mt-4">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-warning-subtle py-3 px-4 border-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="avatar-sm">
                        <span class="avatar-title rounded-circle bg-warning text-white fs-16">
                            <i class="mdi mdi-cash-multiple"></i>
                        </span>
                    </span>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="modalOrderCostDetailLabel">Rincian Biaya On Charge</h5>
                        <small class="text-muted" id="modalOrderShipmentTitle">Surat Jalan: -</small>
                    </div>
                </div>
                <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex justify-content-between align-items-center p-2 rounded bg-light border mb-3 fs-12">
                    <div>
                        <span class="text-muted">Tarif Rute:</span>
                        <span class="fw-bold text-dark ms-1" id="modalOrderBasePrice">Rp 0</span>
                    </div>
                    <div>
                        <span class="text-muted">Total Tagihan:</span>
                        <span class="fw-bold text-primary ms-1" id="modalOrderTotalPrice">Rp 0</span>
                    </div>
                </div>

                <p class="text-muted fs-12 mb-2">Komponen biaya tambahan (On Charge) yang ditagihkan kepada pelanggan untuk order ini:</p>
                
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead class="table-light fs-12">
                            <tr>
                                <th width="35" class="text-center">No</th>
                                <th>Komponen Biaya</th>
                                <th class="text-end">Nominal</th>
                            </tr>
                        </thead>
                        <tbody id="modalOrderCostBody" class="fs-12">
                        </tbody>
                        <tfoot class="table-light fw-bold fs-12">
                            <tr>
                                <td colspan="2" class="text-end">Total Biaya On Charge:</td>
                                <td class="text-end text-warning" id="modalOrderOnChargeTotal">Rp 0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light px-4 py-2 border-0">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Helper Forms for Delete Detail & Recalculate -->
<form id="delete-form" method="post">
    @csrf
    @method('DELETE')
</form>

<form id="recalculate-form" method="post" action="{{ route('invoice.recalculate', $data->id) }}">
    @csrf
</form>
@endsection

@push('script')
<script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
<script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>

<script>
    const customerCode = "{{ $data->customerCode }}";
    const customerPpnRate = {{ $customerPpnRate }};
    const customerPphRate = {{ $customerPphRate }};
    const customerDueDateDuration = {{ $customerDueDateDuration }};
    const totalBaseAmount = {{ $initialTotalBase }};
    const totalOnChargeAmount = {{ $initialTotalOnCharge }};
    const subtotalDpp = totalBaseAmount + totalOnChargeAmount;

    let selectedOrdersToAdd = [];

    // Currency Formatter
    function formatRupiah(amount) {
        return 'Rp ' + Math.round(amount).toLocaleString('id-ID');
    }

    // Recalculate Summary sidebar based on PPN and PPh toggles
    function recalculateSidebar() {
        const usePpn = $('#usePpn').is(':checked');
        const usePph = $('#usePph').is(':checked');

        const ppnAmount = usePpn && customerPpnRate > 0 ? subtotalDpp * (customerPpnRate / 100) : 0;
        const pphAmount = usePph && customerPphRate > 0 ? subtotalDpp * (customerPphRate / 100) : 0;
        const grandTotal = subtotalDpp + ppnAmount - pphAmount;

        $('#summaryPpn').text((usePpn ? '+ ' : '') + formatRupiah(ppnAmount));
        $('#summaryPph').text((usePph ? '- ' : '') + formatRupiah(pphAmount));
        $('#summaryGrandTotal').text(formatRupiah(grandTotal));

        if (usePpn) {
            $('#rowPpn').removeClass('opacity-50 text-muted');
        } else {
            $('#rowPpn').addClass('opacity-50 text-muted');
        }

        if (usePph) {
            $('#rowPph').removeClass('opacity-50 text-muted');
        } else {
            $('#rowPph').addClass('opacity-50 text-muted');
        }
    }

    // Update Overdue Date based on invoice date & duration
    function updateOverdueDate() {
        const invDateVal = $('#invoiceDate').val();
        if (invDateVal) {
            const invoiceDate = new Date(invDateVal);
            if (!isNaN(invoiceDate.getTime())) {
                const overdueDate = new Date(invoiceDate);
                overdueDate.setDate(overdueDate.getDate() + (customerDueDateDuration || 30));
                const formatted = overdueDate.toISOString().split('T')[0];
                $('#overdueDate').val(formatted);
                $('#previewOverdueDate').text(formatted);
            }
        }
    }

    $(document).ready(function() {
        // Init table of attached orders
        $('#dt-order').DataTable({
            responsive: true,
            paging: true,
            info: true,
            searching: true,
            pageLength: 10,
            language: {
                search: "Cari Order Terlampir:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ order",
                paginate: {
                    previous: '<i class="mdi mdi-chevron-left"></i>',
                    next: '<i class="mdi mdi-chevron-right"></i>'
                }
            }
        });

        // Tax Card Toggle Handlers
        $('#ppnCard').on('click', function(e) {
            if (e.target.tagName !== 'INPUT') {
                const chk = $('#usePpn');
                chk.prop('checked', !chk.is(':checked')).trigger('change');
            }
        });

        $('#pphCard').on('click', function(e) {
            if (e.target.tagName !== 'INPUT') {
                const chk = $('#usePph');
                chk.prop('checked', !chk.is(':checked')).trigger('change');
            }
        });

        $('#usePpn').on('change', function() {
            if ($(this).is(':checked')) {
                $('#ppnCard').addClass('active');
            } else {
                $('#ppnCard').removeClass('active');
            }
            recalculateSidebar();
        });

        $('#usePph').on('change', function() {
            if ($(this).is(':checked')) {
                $('#pphCard').addClass('active');
            } else {
                $('#pphCard').removeClass('active');
            }
            recalculateSidebar();
        });

        $('#invoiceDate').on('change', function() {
            updateOverdueDate();
        });

        // Confirm Update
        $('#btnSubmitEditInvoice').on('click', function(e) {
            e.preventDefault();
            swal({
                title: "Simpan Perubahan Faktur?",
                text: "Perubahan parameter dan catatan faktur akan disimpan ke database.",
                icon: "info",
                buttons: ["Batal", "Ya, Simpan!"],
            }).then((willSave) => {
                if (willSave) {
                    $('#invoiceEditForm').submit();
                }
            });
        });

        // Datatable in Add Order Modal
        const tableAdd = $('#dt').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            ajax: {
                url: "{{ route('dt.invoice.invoice-order') }}",
                data: function(d) {
                    d.customerCode = customerCode;
                }
            },
            columns: [
                { data: 'action', orderable: false, searchable: false, className: 'text-center' },
                { data: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'orderDate', render: function(d) { return `<span class="fw-semibold text-dark">${d || '-'}</span>`; } },
                {
                    data: null,
                    render: function(data, type, row) {
                        const origin = (row.route && row.route.originLocation && row.route.originLocation.name) ? row.route.originLocation.name : '-';
                        const destination = (row.route && row.route.destinationLocation && row.route.destinationLocation.name) ? row.route.destinationLocation.name : '-';
                        return `
                            <div class="d-flex align-items-center gap-1 fs-12">
                                <span class="badge bg-light text-dark border">${origin}</span>
                                <i class="mdi mdi-arrow-right text-primary"></i>
                                <span class="badge bg-light text-dark border">${destination}</span>
                            </div>
                        `;
                    }
                },
                {
                    data: 'shipmentNumber',
                    render: function(d, type, row) {
                        return `<span class="badge bg-light text-dark border font-monospace">${d || row.code || '-'}</span>`;
                    }
                },
                {
                    data: 'fleet.plateNumber',
                    render: function(d) {
                        return d ? `<span class="badge bg-secondary-subtle text-secondary"><i class="mdi mdi-truck me-1"></i>${d}</span>` : '-';
                    }
                },
                { data: 'basePrice', className: 'text-end' },
                { data: 'addCost', className: 'text-end' },
                { data: 'totalPrice', className: 'text-end' }
            ],
            order: [[2, 'desc']],
            drawCallback: function() {
                $('.order-checkbox').each(function() {
                    const orderId = $(this).val();
                    if (selectedOrdersToAdd.includes(orderId)) {
                        $(this).prop('checked', true);
                    }
                });
            },
            language: {
                search: "Cari Pesanan:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ pesanan",
                emptyTable: "Tidak ada pesanan lain yang siap difakturkan untuk pelanggan ini"
            }
        });

        // Add Order Modal Checkbox handlers
        $(document).on('change', '.order-checkbox', function() {
            const orderId = $(this).val();
            if ($(this).is(':checked')) {
                if (!selectedOrdersToAdd.includes(orderId)) {
                    selectedOrdersToAdd.push(orderId);
                }
            } else {
                selectedOrdersToAdd = selectedOrdersToAdd.filter(id => id !== orderId);
            }
        });

        $('#openModalButton').on('click', function() {
            selectedOrdersToAdd = [];
            tableAdd.ajax.reload();
        });

        $('#formAddOrderModal').on('submit', function(e) {
            if (selectedOrdersToAdd.length === 0) {
                e.preventDefault();
                swal('Peringatan', 'Pilih minimal 1 pesanan untuk ditambahkan ke faktur!', 'warning');
                return false;
            }

            // Append hidden input with selectedOrders
            $('<input>').attr({
                type: 'hidden',
                name: 'selectedOrders',
                value: JSON.stringify(selectedOrdersToAdd)
            }).appendTo('#formAddOrderModal');
        });

        // Event handler to view order On Charge detail modal
        $(document).on('click', '.btn-order-cost-detail', function(e) {
            e.preventDefault();
            const code = $(this).data('code');
            const shipment = $(this).data('shipment');
            const basePrice = $(this).data('base-price');
            const onCharge = $(this).data('on-charge');
            const total = $(this).data('total');
            const costs = $(this).data('costs');

            $('#modalOrderShipmentTitle').text('Surat Jalan: ' + shipment + ' (' + code + ')');
            $('#modalOrderBasePrice').text('Rp ' + basePrice);
            $('#modalOrderTotalPrice').text('Rp ' + total);
            $('#modalOrderOnChargeTotal').text('Rp ' + onCharge);

            const tbody = $('#modalOrderCostBody');
            tbody.empty();
            if (costs && costs.length > 0) {
                costs.forEach((c, idx) => {
                    tbody.append(`
                        <tr>
                            <td class="text-center">${idx + 1}</td>
                            <td>
                                <span class="fw-semibold text-dark">${c.component}</span>
                                ${c.description ? `<br><small class="text-muted">${c.description}</small>` : ''}
                            </td>
                            <td class="text-end fw-semibold text-warning">${c.nominalFormatted}</td>
                        </tr>
                    `);
                });
            } else {
                tbody.append('<tr><td colspan="3" class="text-center text-muted">Tidak ada data biaya On Charge</td></tr>');
            }

            $('#modalOrderCostDetail').modal('show');
        });
    });

    // Delete single order detail from invoice
    function deleteInvoiceDetail(id) {
        var url = "{{ route('invoice.detail.destroy', ':id') }}".replace(':id', id);
        $('#delete-form').attr('action', url);

        swal({
            title: "{{ __('general.are_you_sure') }}",
            text: "Order ini akan dikeluarkan dari faktur dan kembali berstatus siap difakturkan.",
            icon: "warning",
            buttons: ["Batal", "Ya, Keluarkan Order"],
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                $('#delete-form').submit();
            }
        });
    }

    // Recalculate invoice based on active orders
    function recalculateInvoice() {
        swal({
            title: "Hitung Ulang Nilai Faktur?",
            text: "Proses ini akan mengkalkulasi ulang subtotal DPP, PPN, dan PPh berdasarkan order yang terlampir saat ini. Jika terdapat pembayaran pada invoice ini, status akan disesuaikan kembali.",
            icon: "warning",
            buttons: ["Batal", "Ya, Hitung Ulang!"],
            dangerMode: true,
        }).then((willRecalculate) => {
            if (willRecalculate) {
                $('#recalculate-form').submit();
            }
        });
    }
</script>
@endpush
