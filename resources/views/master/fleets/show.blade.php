@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => $title,
    'secondSegment' => __('general.detail'),
])

@push('style')
    {{-- CSS Terstandarisasi phl-table-design --}}
    <style>
        /* ── Avatar Badge Entitas ── */
        .avatar-badge-entity {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.25);
            flex-shrink: 0;
        }

        /* ── Summary KPI Cards ── */
        .summary-card {
            border-radius: 12px;
            padding: 18px 20px;
            border: 1px solid transparent;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .summary-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .summary-card h5 {
            font-size: 12px;
            margin-bottom: 6px;
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .summary-card h3 {
            font-size: 22px;
            font-weight: 800;
            margin: 0;
            letter-spacing: -0.5px;
        }

        .summary-primary {
            background: linear-gradient(135deg, #eef2ff, #e0e7ff);
            border-color: #c7d2fe;
            color: #3730a3;
        }

        .summary-success {
            background: linear-gradient(135deg, #ecfdf5, #d1fae5);
            border-color: #a7f3d0;
            color: #065f46;
        }

        .summary-warning {
            background: linear-gradient(135deg, #fefce8, #fef9c3);
            border-color: #fde68a;
            color: #92400e;
        }

        /* ── Tombol Icon Ramping ── */
        .btn-icon {
            border-radius: 8px !important;
            padding: 6px 10px;
            font-size: 13px;
            transition: all 0.2s ease;
        }

        .btn-icon:hover {
            transform: translateY(-1px);
        }

        /* ── Data Spec Table ── */
        .spec-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
        }

        .spec-table th,
        .spec-table td {
            padding: 10px 14px;
            font-size: 12.5px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .spec-table tr:last-child td {
            border-bottom: none;
        }

        .spec-table td.label-cell {
            width: 35%;
            font-weight: 600;
            color: #64748b;
            background-color: #f8fafc;
            border-right: 1px solid #e2e8f0;
        }

        .spec-table td.value-cell {
            width: 65%;
            color: #1e293b;
        }

        /* ── Mini Tables for Recent Items (Non-striped) ── */
        .mini-data-table {
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            width: 100%;
        }

        .mini-data-table thead th {
            background-color: #f8fafc;
            color: #475569;
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 10px 12px;
            border-bottom: 2px solid #e2e8f0;
            border-top: none;
            white-space: nowrap;
        }

        .mini-data-table tbody td {
            padding: 9px 12px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            font-size: 12px;
            vertical-align: middle;
        }

        .mini-data-table tbody tr:hover {
            background-color: #f8fafc !important;
        }

        .mini-data-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* ── Galeri Foto Thumbnail ── */
        .fleet-gallery-card {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.2s ease;
            background: #fff;
        }

        .fleet-gallery-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border-color: #cbd5e1;
        }

        .fleet-gallery-img {
            width: 100%;
            height: 140px;
            object-fit: cover;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .fleet-gallery-card:hover .fleet-gallery-img {
            transform: scale(1.02);
        }

        /* ── Dark mode ─────────────────────────────── */
        /* Inline white cards (banner, spec, gallery, history) → dark surfaces. */
        html[data-bs-theme="dark"] .card[style*="background: #ffffff"] {
            background-color: var(--bs-card-bg) !important;
            border-color: var(--bs-border-color) !important;
        }

        html[data-bs-theme="dark"] .summary-primary {
            background: var(--bs-primary-bg-subtle) !important;
            border-color: var(--bs-primary-border-subtle) !important;
            color: var(--bs-primary-text-emphasis) !important;
        }

        html[data-bs-theme="dark"] .summary-success {
            background: var(--bs-success-bg-subtle) !important;
            border-color: var(--bs-success-border-subtle) !important;
            color: var(--bs-success-text-emphasis) !important;
        }

        html[data-bs-theme="dark"] .summary-warning {
            background: var(--bs-warning-bg-subtle) !important;
            border-color: var(--bs-warning-border-subtle) !important;
            color: var(--bs-warning-text-emphasis) !important;
        }

        html[data-bs-theme="dark"] .spec-table {
            border-color: var(--bs-border-color) !important;
        }

        html[data-bs-theme="dark"] .spec-table th,
        html[data-bs-theme="dark"] .spec-table td {
            border-color: var(--bs-border-color) !important;
        }

        html[data-bs-theme="dark"] .spec-table td.label-cell {
            background-color: var(--bs-tertiary-bg) !important;
            color: var(--bs-secondary-color) !important;
            border-color: var(--bs-border-color) !important;
        }

        html[data-bs-theme="dark"] .spec-table td.value-cell {
            color: var(--bs-body-color) !important;
        }

        html[data-bs-theme="dark"] .mini-data-table {
            border-color: var(--bs-border-color) !important;
        }

        html[data-bs-theme="dark"] .mini-data-table thead th {
            background-color: var(--bs-tertiary-bg) !important;
            color: var(--bs-emphasis-color) !important;
            border-color: var(--bs-border-color) !important;
        }

        html[data-bs-theme="dark"] .mini-data-table tbody td {
            color: var(--bs-body-color) !important;
            border-color: var(--bs-border-color) !important;
        }

        html[data-bs-theme="dark"] .mini-data-table tbody tr:hover {
            background-color: var(--bs-tertiary-bg) !important;
        }

        html[data-bs-theme="dark"] .fleet-gallery-card {
            background-color: var(--bs-card-bg) !important;
            border-color: var(--bs-border-color) !important;
        }

        /* Image preview modal keeps its light border inline; correct it here. */
        html[data-bs-theme="dark"] #imagePreviewModal .modal-content,
        html[data-bs-theme="dark"] #imagePreviewModal .modal-header,
        html[data-bs-theme="dark"] #imagePreviewModal .modal-footer {
            border-color: var(--bs-border-color) !important;
        }
    </style>
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
            {{-- Card Header --}}
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center"
                style="border-color: #e2e8f0;">
                <div>
                    <h4 class="mb-1 fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="mdi mdi-truck text-primary fs-20"></i>
                        {{ $title }} {{ __('general.detail') }} - <span class="font-monospace text-primary">{{ $fleet->plateNumber }}</span>
                    </h4>
                    <small class="text-muted">Informasi komprehensif profil armada, legalitas dokumen, performa, dan riwayat aktivitas</small>
                </div>

                <div class="d-flex align-items-center gap-2">
                    {{-- Tombol Export PDF --}}
                    <a href="{{ route($view . 'detail-export-pdf', $fleet->id) }}" target="_blank"
                        class="btn btn-icon btn-sm bg-danger-subtle" data-bs-toggle="tooltip" title="Export PDF">
                        <i class="mdi mdi-file-pdf-box fs-14 text-danger"></i>
                    </a>

                    {{-- Tombol Edit Armada (Form mandiri) --}}
                    <a href="{{ route($view . 'edit', $fleet->id) }}" class="btn btn-sm btn-primary d-flex align-items-center gap-1"
                        style="border-radius: 8px; font-weight: 600; padding: 7px 14px;">
                        <i class="mdi mdi-pencil-outline"></i> Edit Armada
                    </a>

                    {{-- Tombol Kembali --}}
                    <a href="{{ route($view . 'index') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1"
                        style="border-radius: 8px; font-weight: 600; padding: 7px 14px;">
                        <i class="mdi mdi-arrow-left"></i> {{ __('general.back_to_list') }}
                    </a>
                </div>
            </div>

            <div class="card-body p-4">
                @include('partials.alert')

                {{-- Banner Profil Entitas --}}
                <div class="card border-0 mb-4"
                    style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 12px;">
                    <div class="card-body p-3">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-badge-entity">
                                    <i class="mdi mdi-truck fs-24 text-white"></i>
                                </div>
                                <div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fs-18 fw-bold text-dark font-monospace">{{ $fleet->plateNumber }}</span>
                                        @php
                                            $typeLower = strtolower($fleet->company?->type ?? 'internal');
                                            $badgeClass = $typeLower === 'external' ? 'bg-warning-subtle text-warning' : 'bg-primary-subtle text-primary';
                                        @endphp
                                        <span class="badge {{ $badgeClass }} px-2 py-1"
                                            style="border-radius: 6px; font-weight: 600; font-size: 11px;">
                                            {{ $fleet->company?->type ?? 'Internal' }}
                                        </span>
                                        @if ($fleet->year)
                                            <span class="badge bg-light text-secondary px-2 py-1 border"
                                                style="border-radius: 6px; font-weight: 600; font-size: 11px;">
                                                Tahun {{ $fleet->year }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-muted small mt-1">
                                        <i class="mdi mdi-office-building me-1"></i>{{ $fleet->company?->name ?? '-' }}
                                        <span class="mx-2">•</span>
                                        <i class="mdi mdi-tag me-1"></i>Merek: <strong>{{ $fleet->brand?->name ?? '-' }}</strong>
                                        <span class="mx-2">•</span>
                                        <i class="mdi mdi-truck-outline me-1"></i>Tipe: <strong>{{ $fleet->type?->name ?? '-' }}</strong>
                                        <span class="mx-2">•</span>
                                        <i class="mdi mdi-identifier me-1"></i>Kode: <span class="font-monospace fw-semibold">{{ $fleet->code }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-2 bg-light px-3 py-2 rounded-3 border" style="font-size: 12.5px;">
                                <i class="mdi mdi-account-tie text-primary fs-18"></i>
                                <div>
                                    <div class="text-muted" style="font-size: 11px;">Pengemudi Ditugaskan</div>
                                    <span class="fw-bold text-dark">{{ $fleet->driver?->name ?? 'Belum Ditugaskan' }}</span>
                                    @if ($fleet->driver?->phone)
                                        <span class="text-muted">({{ $fleet->driver->phone }})</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Kartu Ringkasan KPI (Summary Tiles) --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="summary-card summary-primary">
                            <i class="mdi mdi-clipboard-text-clock-outline float-end fs-24 opacity-50"></i>
                            <h5>Total Pengiriman (Order)</h5>
                            <h3>{{ number_format($totalOrders, 0, ',', '.') }} <span class="fs-14 fw-normal opacity-75">Order</span></h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="summary-card summary-success">
                            <i class="mdi mdi-wrench-clock float-end fs-24 opacity-50"></i>
                            <h5>Total Perawatan (Servis)</h5>
                            <h3>{{ number_format($totalMaintenance, 0, ',', '.') }} <span class="fs-14 fw-normal opacity-75">Servis</span></h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="summary-card summary-warning">
                            <i class="mdi mdi-cash-multiple float-end fs-24 opacity-50"></i>
                            <h5>Total Biaya Perawatan</h5>
                            <h3>Rp {{ number_format($totalMaintenanceCost, 0, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>

                {{-- Detail Spesifikasi & Legalitas Dokumen (2 Kolom) --}}
                <div class="row g-4 mb-4">
                    {{-- Kolom Kiri: Spesifikasi Teknis --}}
                    <div class="col-lg-6">
                        <div class="card border-0 h-100" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2" style="border-color: #e2e8f0;">
                                <i class="mdi mdi-car-info text-primary fs-18"></i>
                                <h6 class="mb-0 fw-bold text-dark">Spesifikasi & Identitas Armada</h6>
                            </div>
                            <div class="card-body p-3">
                                <table class="spec-table">
                                    <tbody>
                                        <tr>
                                            <td class="label-cell">Nomor Polisi (Plat)</td>
                                            <td class="value-cell font-monospace fw-bold text-dark">{{ $fleet->plateNumber }}</td>
                                        </tr>
                                        <tr>
                                            <td class="label-cell">Kode Armada</td>
                                            <td class="value-cell font-monospace text-muted">{{ $fleet->code }}</td>
                                        </tr>
                                        <tr>
                                            <td class="label-cell">Merek Armada</td>
                                            <td class="value-cell">
                                                @if ($fleet->brand?->name)
                                                    <span class="badge bg-light text-dark border px-2 py-1">{{ $fleet->brand->name }}</span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="label-cell">Tipe Armada</td>
                                            <td class="value-cell fw-semibold">{{ $fleet->type?->name ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="label-cell">Tahun Pembuatan</td>
                                            <td class="value-cell font-monospace">{{ $fleet->year ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="label-cell">Nomor Rangka</td>
                                            <td class="value-cell font-monospace">{{ $fleet->frameNumber ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="label-cell">Nomor Mesin</td>
                                            <td class="value-cell font-monospace">{{ $fleet->engineNumber ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="label-cell">Perusahaan Pemilik</td>
                                            <td class="value-cell">
                                                {{ $fleet->company?->name ?? '-' }}
                                                @if ($fleet->company?->type)
                                                    <span class="badge {{ $badgeClass }} px-2 py-0 ms-1">{{ $fleet->company->type }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="label-cell">Alamat Perusahaan</td>
                                            <td class="value-cell text-muted">{{ $fleet->company?->address ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="label-cell">Pengemudi / Driver</td>
                                            <td class="value-cell">
                                                {{ $fleet->driver?->name ?? 'Belum Ditugaskan' }}
                                                @if ($fleet->driver?->position)
                                                    <small class="text-muted">({{ $fleet->driver->position->name }})</small>
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Kolom Kanan: Masa Berlaku Dokumen & Dokumen Digital --}}
                    <div class="col-lg-6">
                        <div class="card border-0 h-100" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2" style="border-color: #e2e8f0;">
                                <i class="mdi mdi-file-certificate text-primary fs-18"></i>
                                <h6 class="mb-0 fw-bold text-dark">Masa Berlaku Dokumen & Legalitas</h6>
                            </div>
                            <div class="card-body p-3">
                                @php
                                    $now = \Carbon\Carbon::now()->startOfDay();

                                    $calcStatus = function ($dateStr) use ($now) {
                                        if (!$dateStr) {
                                            return ['badge' => 'bg-secondary-subtle text-secondary', 'label' => 'Belum Diisi', 'formatted' => '-'];
                                        }
                                        $d = \Carbon\Carbon::parse($dateStr)->startOfDay();
                                        $formatted = $d->format('d/m/Y');
                                        if ($d->lt($now)) {
                                            return ['badge' => 'bg-danger-subtle text-danger', 'label' => 'Kadaluarsa', 'formatted' => $formatted];
                                        }
                                        $diffDays = $now->diffInDays($d);
                                        if ($diffDays <= 30) {
                                            return ['badge' => 'bg-warning-subtle text-warning', 'label' => 'Mendekati (' . $diffDays . ' hr)', 'formatted' => $formatted];
                                        }
                                        return ['badge' => 'bg-success-subtle text-success', 'label' => 'Aktif', 'formatted' => $formatted];
                                    };

                                    $stnkStatus = $calcStatus($fleet->vehicleRegistrationDueDate);
                                    $taxStatus = $calcStatus($fleet->vehicleTax);
                                    $kirStatus = $calcStatus($fleet->vehicleKir);
                                @endphp
                                <table class="spec-table mb-3">
                                    <tbody>
                                        <tr>
                                            <td class="label-cell">Jatuh Tempo STNK</td>
                                            <td class="value-cell">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <span class="font-monospace fw-semibold">{{ $stnkStatus['formatted'] }}</span>
                                                    <span class="badge {{ $stnkStatus['badge'] }} px-2 py-1" style="border-radius: 6px; font-weight: 600; font-size: 11px;">
                                                        {{ $stnkStatus['label'] }}
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="label-cell">Pajak Kendaraan</td>
                                            <td class="value-cell">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <span class="font-monospace fw-semibold">{{ $taxStatus['formatted'] }}</span>
                                                    <span class="badge {{ $taxStatus['badge'] }} px-2 py-1" style="border-radius: 6px; font-weight: 600; font-size: 11px;">
                                                        {{ $taxStatus['label'] }}
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="label-cell">KIR Kendaraan</td>
                                            <td class="value-cell">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <span class="font-monospace fw-semibold">{{ $kirStatus['formatted'] }}</span>
                                                    <span class="badge {{ $kirStatus['badge'] }} px-2 py-1" style="border-radius: 6px; font-weight: 600; font-size: 11px;">
                                                        {{ $kirStatus['label'] }}
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="label-cell">Lampiran Barcode</td>
                                            <td class="value-cell">
                                                @if ($fleet->barcode)
                                                    <div class="d-flex align-items-center gap-2">
                                                        <a href="javascript:void(0)"
                                                            onclick="showModal('{{ url('storage/fleet/barcode', $fleet->barcode) }}', 'Barcode Armada - {{ $fleet->plateNumber }}')"
                                                            class="btn btn-sm btn-outline-primary py-1 px-2 d-inline-flex align-items-center gap-1"
                                                            style="border-radius: 6px; font-size: 11.5px;">
                                                            <i class="mdi mdi-barcode fs-14"></i> Lihat Barcode
                                                        </a>
                                                        <a href="{{ url('storage/fleet/barcode', $fleet->barcode) }}" target="_blank"
                                                            class="btn btn-sm btn-light border py-1 px-2 text-muted"
                                                            data-bs-toggle="tooltip" title="Buka di tab baru" style="border-radius: 6px; font-size: 11.5px;">
                                                            <i class="mdi mdi-open-in-new"></i>
                                                        </a>
                                                    </div>
                                                @else
                                                    <span class="text-muted">Tidak ada lampiran barcode</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="label-cell">Lampiran STNK</td>
                                            <td class="value-cell">
                                                @if ($fleet->vehicleRegistrationNumber)
                                                    <div class="d-flex align-items-center gap-2">
                                                        <a href="javascript:void(0)"
                                                            onclick="showModal('{{ url('storage/fleet/vehicleRegistrationNumber', $fleet->vehicleRegistrationNumber) }}', 'STNK Armada - {{ $fleet->plateNumber }}')"
                                                            class="btn btn-sm btn-outline-primary py-1 px-2 d-inline-flex align-items-center gap-1"
                                                            style="border-radius: 6px; font-size: 11.5px;">
                                                            <i class="mdi mdi-file-document-outline fs-14"></i> Lihat STNK
                                                        </a>
                                                        <a href="{{ url('storage/fleet/vehicleRegistrationNumber', $fleet->vehicleRegistrationNumber) }}" target="_blank"
                                                            class="btn btn-sm btn-light border py-1 px-2 text-muted"
                                                            data-bs-toggle="tooltip" title="Buka di tab baru" style="border-radius: 6px; font-size: 11.5px;">
                                                            <i class="mdi mdi-open-in-new"></i>
                                                        </a>
                                                    </div>
                                                @else
                                                    <span class="text-muted">Tidak ada lampiran STNK</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="label-cell">Registrasi Sistem</td>
                                            <td class="value-cell font-monospace text-muted">
                                                {{ $fleet->created_at ? $fleet->created_at->format('d/m/Y H:i') : '-' }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Galeri Foto Armada --}}
                <div class="card border-0 mb-4" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 12px;">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center" style="border-color: #e2e8f0;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="mdi mdi-camera-outline text-primary fs-18"></i>
                            <h6 class="mb-0 fw-bold text-dark">Galeri Foto Fisik Armada</h6>
                        </div>
                        <span class="badge bg-light text-secondary border px-2 py-1" style="border-radius: 6px; font-size: 11px;">
                            Total {{ count($fleet->pictures) }} Foto
                        </span>
                    </div>
                    <div class="card-body p-3">
                        @if (count($fleet->pictures) > 0)
                            <div class="row g-3">
                                @foreach ($fleet->pictures as $pic)
                                    <div class="col-sm-6 col-md-4 col-lg-3">
                                        <div class="fleet-gallery-card">
                                            <img src="{{ url('storage/fleet/fleetPicture', $pic->fleetPicture) }}"
                                                alt="Foto Armada {{ $fleet->plateNumber }}" class="fleet-gallery-img"
                                                onclick="showModal('{{ url('storage/fleet/fleetPicture', $pic->fleetPicture) }}', 'Foto Armada - {{ $fleet->plateNumber }}')">
                                            <div class="p-2 d-flex justify-content-between align-items-center bg-light border-top">
                                                <small class="font-monospace text-muted" style="font-size: 11px;">{{ $pic->code }}</small>
                                                <button type="button" class="btn btn-icon btn-sm bg-white border text-primary"
                                                    onclick="showModal('{{ url('storage/fleet/fleetPicture', $pic->fleetPicture) }}', 'Foto Armada - {{ $fleet->plateNumber }}')"
                                                    data-bs-toggle="tooltip" title="Perbesar Foto">
                                                    <i class="mdi mdi-magnify-plus-outline fs-12"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4 text-muted">
                                <i class="mdi mdi-image-off-outline fs-36 d-block mb-1 opacity-50"></i>
                                <span class="fs-13">Belum ada foto fisik armada yang diunggah untuk unit ini.</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Riwayat Aktivitas Armada: 10 Pengiriman & 10 Perawatan Terakhir --}}
                <div class="row g-4">
                    {{-- Riwayat Pengiriman / Order --}}
                    <div class="col-lg-6">
                        <div class="card border-0 h-100" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center" style="border-color: #e2e8f0;">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="mdi mdi-clipboard-list-outline text-primary fs-18"></i>
                                    <h6 class="mb-0 fw-bold text-dark">10 Pengiriman (Order) Terakhir</h6>
                                </div>
                                <span class="badge bg-primary-subtle text-primary px-2 py-1" style="border-radius: 6px; font-weight: 600; font-size: 11px;">
                                    Total {{ $totalOrders }} Order
                                </span>
                            </div>
                            <div class="card-body p-3">
                                <div class="table-responsive custom-scrollbar">
                                    <table class="mini-data-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 50px;" class="text-center">No</th>
                                                <th>Kode Order</th>
                                                <th class="text-center">Tgl Order</th>
                                                <th>No Surat Jalan</th>
                                                <th class="text-center">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($recentOrders as $order)
                                                <tr>
                                                    <td class="text-center">{{ $loop->iteration }}</td>
                                                    <td class="font-monospace fw-semibold text-dark">{{ $order->code }}</td>
                                                    <td class="text-center font-monospace">
                                                        {{ $order->orderDate ? \Carbon\Carbon::parse($order->orderDate)->format('d/m/Y') : '-' }}
                                                    </td>
                                                    <td class="font-monospace text-muted">{{ $order->shipmentNumber ?? '-' }}</td>
                                                    <td class="text-center">
                                                        <span class="badge bg-light text-secondary border px-2 py-1" style="border-radius: 6px; font-size: 10.5px;">
                                                            {{ $order->status ?? 'Diproses' }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center py-3 text-muted">
                                                        Belum ada riwayat pengiriman untuk armada ini.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Riwayat Perawatan / Servis --}}
                    <div class="col-lg-6">
                        <div class="card border-0 h-100" style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center" style="border-color: #e2e8f0;">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="mdi mdi-wrench-clock-outline text-warning fs-18"></i>
                                    <h6 class="mb-0 fw-bold text-dark">10 Perawatan (Servis) Terakhir</h6>
                                </div>
                                <span class="badge bg-warning-subtle text-warning px-2 py-1" style="border-radius: 6px; font-weight: 600; font-size: 11px;">
                                    Total {{ $totalMaintenance }} Kali
                                </span>
                            </div>
                            <div class="card-body p-3">
                                <div class="table-responsive custom-scrollbar">
                                    <table class="mini-data-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 50px;" class="text-center">No</th>
                                                <th>Kode MNT</th>
                                                <th class="text-center">Tanggal</th>
                                                <th class="text-center">Jam</th>
                                                <th class="text-center">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($recentMaintenances as $mnt)
                                                <tr>
                                                    <td class="text-center">{{ $loop->iteration }}</td>
                                                    <td class="font-monospace fw-semibold text-dark">{{ $mnt->code }}</td>
                                                    <td class="text-center font-monospace">
                                                        {{ $mnt->date ? \Carbon\Carbon::parse($mnt->date)->format('d/m/Y') : '-' }}
                                                    </td>
                                                    <td class="text-center font-monospace text-muted">{{ $mnt->time ?? '-' }}</td>
                                                    <td class="text-center">
                                                        @php
                                                            $isDone = ($mnt->status === 0 || $mnt->status === '0');
                                                        @endphp
                                                        <span class="badge {{ $isDone ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }} px-2 py-1"
                                                            style="border-radius: 6px; font-weight: 600; font-size: 10.5px;">
                                                            {{ $isDone ? 'Selesai' : 'Diproses' }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center py-3 text-muted">
                                                        Belum ada riwayat perawatan untuk armada ini.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Image Preview --}}
    <div class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog" aria-labelledby="imagePreviewModalLabel"
        aria-hidden="true" id="imagePreviewModal">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px; overflow: hidden; border: 1px solid #e2e8f0;">
                <div class="modal-header py-3 px-4 bg-white border-bottom" style="border-color: #e2e8f0;">
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="modalTitle">
                        <i class="mdi mdi-image-outline text-primary"></i> Preview Gambar
                    </h5>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center bg-light">
                    <img id="modalImage" src="" alt="Image Preview" class="img-fluid rounded-3 shadow-sm"
                        style="max-height: 70vh; object-fit: contain;" />
                </div>
                <div class="modal-footer py-2 px-4 bg-white border-top d-flex justify-content-between" style="border-color: #e2e8f0;">
                    <a id="modalImageDownload" href="#" target="_blank" class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1"
                        style="border-radius: 8px;">
                        <i class="mdi mdi-download"></i> Unduh Gambar
                    </a>
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal"
                        style="border-radius: 8px;">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        function showModal(imageUrl, title = 'Preview Gambar') {
            document.getElementById('modalTitle').innerHTML = '<i class="mdi mdi-image-outline text-primary me-2"></i>' + title;
            document.getElementById('modalImage').src = imageUrl;
            document.getElementById('modalImageDownload').href = imageUrl;
            $('#imagePreviewModal').modal('show');
        }
    </script>
@endpush
