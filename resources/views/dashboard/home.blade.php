@extends('layouts.main', [
    'title' => 'Dashboard',
    'pageTitle' => 'Dashboard',
    'firstSegment' => 'Dashboard',
    'secondSegment' => 'Beranda',
])

@push('style')
    <style>
        .phl-dash {
            --dash-surface: #ffffff;
            --dash-surface-muted: #f8fafc;
            --dash-border: #e2e8f0;
            --dash-ink: #0f172a;
            --dash-ink-muted: #475569;
            --dash-ink-faint: #64748b;
            --dash-accent: #5b4ce6;
            --dash-accent-hover: #4839c8;
            --dash-accent-tint: rgba(91, 76, 230, 0.1);
            --dash-radius: 14px;
            --dash-radius-sm: 8px;
        }

        html[data-bs-theme="dark"] .phl-dash {
            --dash-surface: #1f2028;
            --dash-surface-muted: #252834;
            --dash-border: #33394a;
            --dash-ink: #f8fafc;
            --dash-ink-muted: #cbd5e1;
            --dash-ink-faint: #94a3b8;
            --dash-accent: #8b7ff5;
            --dash-accent-hover: #7669d3;
            --dash-accent-tint: rgba(139, 127, 245, 0.16);
        }

        .phl-num {
            font-variant-numeric: tabular-nums;
        }

        .phl-dash-card {
            background-color: var(--dash-surface);
            border: 1px solid var(--dash-border);
            border-radius: var(--dash-radius);
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }

        /* Period Filter Bar */
        .phl-filter-bar {
            padding: 0.875rem 1.25rem;
            margin-bottom: 1.25rem;
        }

        .phl-filter-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem 1rem;
        }

        .phl-preset-group {
            display: inline-flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.25rem;
            padding: 3px;
            border-radius: 10px;
            border: 1px solid var(--dash-border);
            background-color: var(--dash-surface-muted);
        }

        .phl-preset-btn {
            height: 32px;
            padding: 0 0.875rem;
            border-radius: 7px;
            border: 1px solid transparent;
            background-color: transparent;
            color: var(--dash-ink-muted);
            font-size: 0.8125rem;
            font-weight: 600;
            white-space: nowrap;
            cursor: pointer;
            transition: background-color 150ms ease, border-color 150ms ease, color 150ms ease;
        }

        .phl-preset-btn:hover {
            color: var(--dash-ink);
        }

        .phl-preset-btn.is-active {
            background-color: var(--dash-surface);
            border-color: var(--dash-border);
            color: var(--dash-accent);
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
        }

        .phl-date-controls {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.5rem;
        }

        .phl-date-range-group {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
        }

        .phl-date-sep {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--dash-ink-faint);
            white-space: nowrap;
        }

        .phl-date-input {
            width: 126px !important;
            flex: 0 0 126px;
            height: 38px !important;
            padding: 0 0.75rem !important;
            border-radius: var(--dash-radius-sm) !important;
            border: 1px solid var(--dash-border) !important;
            background-color: var(--dash-surface-muted) !important;
            color: var(--dash-ink) !important;
            font-size: 0.8125rem !important;
            font-variant-numeric: tabular-nums;
        }

        .phl-apply-btn {
            height: 38px;
            padding: 0 1rem;
            border-radius: var(--dash-radius-sm);
            border: 0;
            background-color: #5b4ce6;
            color: #ffffff;
            font-size: 0.8125rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            cursor: pointer;
            white-space: nowrap;
        }

        .phl-apply-btn:hover:not(:disabled) {
            background-color: #4839c8;
        }

        .phl-apply-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .phl-period-badge {
            height: 38px;
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--dash-ink-muted);
            background-color: var(--dash-surface-muted);
            border: 1px solid var(--dash-border);
            border-radius: var(--dash-radius-sm);
            padding: 0 0.75rem;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            white-space: nowrap;
        }

        @media (max-width: 575.98px) {
            .phl-preset-group {
                width: 100%;
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .phl-date-controls,
            .phl-date-range-group {
                width: 100%;
            }

            .phl-date-input {
                width: auto !important;
                flex: 1 1 0;
                min-width: 0;
            }

            .phl-apply-btn,
            .phl-period-badge {
                flex: 1 1 auto;
                justify-content: center;
            }
        }

        /* Executive KPI Tiles */
        .phl-kpi-card {
            height: 100%;
            padding: 1.125rem 1.25rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .phl-kpi-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .phl-kpi-label {
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--dash-ink-muted);
            margin-bottom: 0.25rem;
        }

        .phl-kpi-scope {
            font-size: 0.6875rem;
            font-weight: 600;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            padding: 0.125rem 0.45rem;
            border-radius: 6px;
            background-color: var(--dash-surface-muted);
            color: var(--dash-ink-faint);
            border: 1px solid var(--dash-border);
            white-space: nowrap;
        }

        .phl-kpi-value {
            font-size: clamp(1.25rem, 1.8vw, 1.5rem);
            font-weight: 700;
            color: var(--dash-ink);
            line-height: 1.2;
            letter-spacing: -0.02em;
        }

        .phl-kpi-sub {
            font-size: 0.78rem;
            color: var(--dash-ink-faint);
            line-height: 1.45;
        }

        .phl-kpi-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.125rem;
            flex: none;
        }

        .phl-kpi-footer {
            padding-top: 0.625rem;
            border-top: 1px solid var(--dash-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            font-size: 0.78rem;
        }

        .phl-drill-link {
            color: var(--dash-accent);
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .phl-drill-link:hover {
            text-decoration: underline;
        }

        /* Chart Container */
        .phl-chart-wrap {
            position: relative;
            height: 310px;
            width: 100%;
        }

        /* Accessible Pillar Tabs */
        .phl-tablist {
            display: flex;
            gap: 0.25rem;
            border-bottom: 1px solid var(--dash-border);
            overflow-x: auto;
            scrollbar-width: thin;
            margin-bottom: 1.25rem;
        }

        .phl-tab {
            position: relative;
            flex: none;
            padding: 0.75rem 1.125rem;
            min-height: 44px;
            border: 0;
            background: transparent;
            color: var(--dash-ink-muted);
            font-size: 0.875rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            white-space: nowrap;
        }

        .phl-tab:hover {
            color: var(--dash-ink);
        }

        .phl-tab[aria-selected="true"] {
            color: var(--dash-accent);
        }

        .phl-tab[aria-selected="true"]::after {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            border-bottom: 3px solid var(--dash-accent);
        }

        .phl-tab:focus-visible,
        .phl-preset-btn:focus-visible,
        .phl-apply-btn:focus-visible {
            outline: 2px solid var(--dash-accent);
            outline-offset: 2px;
        }

        /* Status Pipeline Strip */
        .phl-pipeline-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(135px, 1fr));
            gap: 0.75rem;
        }

        .phl-pipeline-item {
            padding: 0.75rem 0.875rem;
            border-radius: 10px;
            border: 1px solid var(--dash-border);
            background-color: var(--dash-surface-muted);
        }

        .phl-pipeline-name {
            font-size: 0.75rem;
            color: var(--dash-ink-faint);
            margin-bottom: 0.25rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .phl-pipeline-count {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--dash-ink);
        }

        /* Responsive Table Wrapper */
        .phl-table-wrap {
            overflow-x: auto;
            max-width: 100%;
        }

        .phl-table {
            width: 100%;
            margin-bottom: 0;
            font-size: 0.84rem;
        }

        .phl-table thead th {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: var(--dash-ink-faint);
            background-color: var(--dash-surface-muted);
            border-bottom: 1px solid var(--dash-border);
            padding: 0.65rem 0.75rem;
            white-space: nowrap;
        }

        .phl-table tbody td {
            padding: 0.65rem 0.75rem;
            border-bottom: 1px solid var(--dash-border);
            color: var(--dash-ink);
            vertical-align: middle;
        }

        .phl-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .phl-card-head {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--dash-border);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .phl-card-title {
            margin: 0;
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--dash-ink);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .phl-card-sub {
            font-size: 0.78rem;
            color: var(--dash-ink-faint);
            margin: 0.15rem 0 0;
        }

        .phl-bank-tile {
            padding: 0.9rem 1rem;
            border-radius: 10px;
            border: 1px solid var(--dash-border);
            background-color: var(--dash-surface-muted);
            height: 100%;
        }

        .phl-is-loading {
            opacity: 0.65;
            pointer-events: none;
            transition: opacity 150ms ease;
        }

        @media (prefers-reduced-motion: reduce) {
            .phl-preset-btn,
            .phl-is-loading {
                transition: none;
            }
        }
    </style>
@endpush

@section('content')
    <div class="phl-dash" id="phl-dashboard-root">
        {{-- Period Filter Bar --}}
        <div class="phl-dash-card phl-filter-bar">
            <div class="phl-filter-row">
                <div class="phl-preset-group" role="group" aria-label="Preset periode dashboard">
                    <button type="button" class="phl-preset-btn {{ $period['preset'] === 'this_month' ? 'is-active' : '' }}"
                        data-preset="this_month">Bulan Ini</button>
                    <button type="button" class="phl-preset-btn {{ $period['preset'] === 'last_month' ? 'is-active' : '' }}"
                        data-preset="last_month">Bulan Lalu</button>
                    <button type="button" class="phl-preset-btn {{ $period['preset'] === 'this_quarter' ? 'is-active' : '' }}"
                        data-preset="this_quarter">Kuartal Ini</button>
                    <button type="button" class="phl-preset-btn {{ $period['preset'] === 'this_year' ? 'is-active' : '' }}"
                        data-preset="this_year">Tahun Ini</button>
                </div>

                <div class="phl-date-controls">
                    <div class="phl-date-range-group">
                        <label for="filter-start-date" class="visually-hidden">Tanggal Mulai</label>
                        <input type="text" id="filter-start-date" class="form-control phl-date-input"
                            value="{{ $period['startDate'] }}" placeholder="Mulai" autocomplete="off" />
                        <span class="phl-date-sep">s.d.</span>
                        <label for="filter-end-date" class="visually-hidden">Tanggal Selesai</label>
                        <input type="text" id="filter-end-date" class="form-control phl-date-input"
                            value="{{ $period['endDate'] }}" placeholder="Selesai" autocomplete="off" />
                    </div>

                    <button type="button" id="btn-apply-period" class="phl-apply-btn">
                        <i class="mdi mdi-filter-variant" id="apply-icon"></i>
                        <span id="apply-text">Terapkan</span>
                    </button>

                    <span class="phl-period-badge phl-num" id="active-period-badge" aria-live="polite">
                        <i class="mdi mdi-calendar-range text-primary"></i>
                        <span id="active-period-text">{{ $period['displayRange'] }}</span>
                    </span>
                </div>
            </div>
        </div>

        {{-- Executive Summary: 4 Core Cross-Pillar KPIs --}}
        <div class="row g-3 mb-4" id="executive-kpi-row">
            {{-- 1. Order & Omzet Periode --}}
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="phl-dash-card phl-kpi-card">
                    <div class="phl-kpi-top">
                        <div>
                            <div class="phl-kpi-label">Order &amp; Omzet</div>
                            <div class="phl-kpi-value phl-num" id="kpi-order-count">
                                {{ number_format($periodic['orders']['count'], 0, ',', '.') }} Order
                            </div>
                        </div>
                        <span class="phl-kpi-icon bg-primary-subtle text-primary">
                            <i class="mdi mdi-truck-delivery-outline"></i>
                        </span>
                    </div>
                    <div class="phl-kpi-sub phl-num">
                        Omzet: <strong class="text-body" id="kpi-order-revenue">Rp {{ number_format($periodic['orders']['revenue'], 0, ',', '.') }}</strong><br>
                        <span id="kpi-order-split">
                            Internal {{ number_format($periodic['orders']['internalCount'], 0, ',', '.') }} · Vendor {{ number_format($periodic['orders']['vendorCount'], 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="phl-kpi-footer">
                        <span class="phl-kpi-scope" id="kpi-order-scope">{{ $period['label'] }}</span>
                        <a href="{{ route('operational.order.index') }}" class="phl-drill-link">
                            Lihat Order <i class="mdi mdi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            {{-- 2. Piutang Customer (Live AR) --}}
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="phl-dash-card phl-kpi-card">
                    <div class="phl-kpi-top">
                        <div>
                            <div class="phl-kpi-label">Piutang Aktif (AR)</div>
                            <div class="phl-kpi-value phl-num">
                                Rp {{ number_format($live['ar']['totalRemaining'], 0, ',', '.') }}
                            </div>
                        </div>
                        <span class="phl-kpi-icon bg-danger-subtle text-danger">
                            <i class="mdi mdi-cash-clock"></i>
                        </span>
                    </div>
                    <div class="phl-kpi-sub phl-num">
                        <strong class="text-danger">{{ number_format($live['ar']['overdueCount'], 0, ',', '.') }} faktur overdue</strong>
                        (Rp {{ number_format($live['ar']['overdueAmount'], 0, ',', '.') }})<br>
                        Jatuh tempo &le; 7 hari: {{ number_format($live['ar']['dueSoonCount'], 0, ',', '.') }} faktur
                    </div>
                    <div class="phl-kpi-footer">
                        <span class="phl-kpi-scope">Live</span>
                        <a href="{{ route('report.receivable-aging.index') }}" class="phl-drill-link">
                            Umur Piutang <i class="mdi mdi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            {{-- 3. Hutang Vendor & Supplier (Live AP) --}}
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="phl-dash-card phl-kpi-card">
                    <div class="phl-kpi-top">
                        <div>
                            <div class="phl-kpi-label">Hutang Vendor &amp; Supplier</div>
                            <div class="phl-kpi-value phl-num">
                                Rp {{ number_format($live['ap']['totalPayableRemaining'], 0, ',', '.') }}
                            </div>
                        </div>
                        <span class="phl-kpi-icon bg-warning-subtle text-warning">
                            <i class="mdi mdi-file-document-alert-outline"></i>
                        </span>
                    </div>
                    <div class="phl-kpi-sub phl-num">
                        Vendor: <strong>Rp {{ number_format($live['ap']['vendor']['totalRemaining'], 0, ',', '.') }}</strong> ({{ number_format($live['ap']['vendor']['notaCount'], 0, ',', '.') }} nota)<br>
                        Supplier: <strong>Rp {{ number_format($live['ap']['supplier']['totalRemaining'], 0, ',', '.') }}</strong> ({{ number_format($live['ap']['supplier']['totalCount'], 0, ',', '.') }} PO)
                    </div>
                    <div class="phl-kpi-footer">
                        <span class="phl-kpi-scope">Live</span>
                        <a href="{{ route('vendor.invoice.unpaid') }}" class="phl-drill-link">
                            Tagihan Vendor <i class="mdi mdi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            {{-- 4. DO Belum Kembali & Siap Invoice (Live Ops) --}}
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="phl-dash-card phl-kpi-card">
                    <div class="phl-kpi-top">
                        <div>
                            <div class="phl-kpi-label">DO Belum Kembali</div>
                            <div class="phl-kpi-value phl-num">
                                {{ number_format($live['operational']['notReturnDoCount'], 0, ',', '.') }} DO
                            </div>
                        </div>
                        <span class="phl-kpi-icon bg-info-subtle text-info">
                            <i class="mdi mdi-clipboard-text-clock-outline"></i>
                        </span>
                    </div>
                    <div class="phl-kpi-sub phl-num">
                        Siap Invoice (Return DO): <strong class="text-body">{{ number_format($live['operational']['readyInvoiceCount'], 0, ',', '.') }} order</strong><br>
                        Armada di jalan: {{ number_format($live['operational']['onRoadOrderCount'], 0, ',', '.') }} order aktif
                    </div>
                    <div class="phl-kpi-footer">
                        <span class="phl-kpi-scope">Live</span>
                        <a href="{{ route('operational.not-return-do.index') }}" class="phl-drill-link">
                            Cek Not Return DO <i class="mdi mdi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Executive Main Chart --}}
        <div class="phl-dash-card mb-4" id="trend-chart-card">
            <div class="phl-card-head">
                <div>
                    <h5 class="phl-card-title">
                        <i class="mdi mdi-chart-line text-primary"></i>
                        Tren Order, Omzet &amp; Biaya Maintenance
                    </h5>
                    <p class="phl-card-sub">
                        Perbandingan volume order terhadap omzet order dan biaya maintenance armada pada periode <span id="chart-period-label">{{ $period['displayRange'] }}</span>
                    </p>
                </div>
                <span class="phl-kpi-scope" id="chart-granularity-badge">
                    {{ $periodic['trend']['granularity'] === 'daily' ? 'Harian' : 'Bulanan' }}
                </span>
            </div>
            <div class="p-3 p-md-4">
                <div class="phl-chart-wrap">
                    <canvas id="orderTrendChart"></canvas>
                </div>
            </div>
        </div>

        {{-- 3 Pillar Tabs --}}
        <div class="phl-dash-card">
            <div class="px-3 px-md-4 pt-2">
                <div class="phl-tablist" role="tablist" aria-label="Detail pilar dashboard PHL">
                    <button type="button" class="phl-tab" role="tab" id="tab-btn-ops" aria-selected="true"
                        data-panel="tab-panel-ops">
                        <i class="mdi mdi-truck-fast-outline" aria-hidden="true"></i>
                        <span>Operasional &amp; Armada</span>
                    </button>
                    <button type="button" class="phl-tab" role="tab" id="tab-btn-finance" aria-selected="false"
                        data-panel="tab-panel-finance">
                        <i class="mdi mdi-bank-outline" aria-hidden="true"></i>
                        <span>Keuangan (AR / AP / Kas)</span>
                    </button>
                    <button type="button" class="phl-tab" role="tab" id="tab-btn-maint" aria-selected="false"
                        data-panel="tab-panel-maint">
                        <i class="mdi mdi-wrench-outline" aria-hidden="true"></i>
                        <span>Maintenance &amp; Gudang</span>
                    </button>
                </div>
            </div>

            {{-- TAB 1: OPERASIONAL & ARMADA --}}
            <div id="tab-panel-ops" role="tabpanel" aria-labelledby="tab-btn-ops" tabindex="0">
                <div class="p-3 p-md-4 pt-2">
                    {{-- Order Pipeline + Fleet Utilization --}}
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <h6 class="mb-0 fw-bold">Distribusi Status Order (Periode Terpilih)</h6>
                        <span class="badge bg-primary-subtle text-primary-emphasis px-3 py-2 phl-num">
                            Armada Internal: {{ number_format($live['operational']['internalFleetOnRoad'], 0, ',', '.') }} On The Road
                            · {{ number_format($live['operational']['internalFleetStandby'], 0, ',', '.') }} Standby
                            (dari {{ number_format($live['operational']['internalFleetTotal'], 0, ',', '.') }})
                        </span>
                    </div>

                    <div class="phl-pipeline-grid mb-4" id="ops-pipeline-grid">
                        @foreach ($periodic['statusBreakdown'] as $st)
                            <div class="phl-pipeline-item">
                                <div class="phl-pipeline-name" title="{{ $st['label'] }}">{{ $st['label'] }}</div>
                                <div class="phl-pipeline-count phl-num">{{ number_format($st['total'], 0, ',', '.') }}</div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Live Operational Tables --}}
                    <div class="row g-4 mb-4">
                        <div class="col-12 col-xl-6">
                            <div class="phl-dash-card h-100">
                                <div class="phl-card-head">
                                    <div>
                                        <h6 class="phl-card-title">Armada On The Road Terkini</h6>
                                        <p class="phl-card-sub">Order aktif dengan status Dibuat, Dikirim, atau Tiba (Live)</p>
                                    </div>
                                    <a href="{{ route('operational.order.index') }}" class="phl-drill-link small">
                                        Semua Order <i class="mdi mdi-arrow-right"></i>
                                    </a>
                                </div>
                                <div class="phl-table-wrap" tabindex="0" role="region" aria-label="Armada on the road terkini">
                                    <table class="table phl-table">
                                        <thead>
                                            <tr>
                                                <th>No. SJ / Order</th>
                                                <th>Armada</th>
                                                <th>Supir</th>
                                                <th>Customer</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($live['operational']['onRoadOrders'] as $ord)
                                                <tr>
                                                    <td class="fw-semibold">{{ $ord['shipmentNumber'] }}</td>
                                                    <td>
                                                        <div>{{ $ord['plateNumber'] }}</div>
                                                        <small class="text-muted">{{ $ord['fleetTypeName'] }}</small>
                                                    </td>
                                                    <td>{{ $ord['driverName'] }}</td>
                                                    <td>
                                                        <div>{{ $ord['customerName'] }}</div>
                                                        <small class="text-muted">Tujuan: {{ $ord['destinationName'] }}</small>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info-subtle text-info-emphasis">{{ $ord['statusLabel'] }}</span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-4">Tidak ada armada sedang berjalan saat ini.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-xl-6">
                            <div class="phl-dash-card h-100">
                                <div class="phl-card-head">
                                    <div>
                                        <h6 class="phl-card-title">DO Belum Kembali Terlama</h6>
                                        <p class="phl-card-sub">Prioritas tindak lanjut surat jalan status Not Return DO (Live)</p>
                                    </div>
                                    <a href="{{ route('operational.not-return-do.index') }}" class="phl-drill-link small">
                                        Kelola DO <i class="mdi mdi-arrow-right"></i>
                                    </a>
                                </div>
                                <div class="phl-table-wrap" tabindex="0" role="region" aria-label="DO belum kembali terlama">
                                    <table class="table phl-table">
                                        <thead>
                                            <tr>
                                                <th>No. SJ</th>
                                                <th>Tgl Order</th>
                                                <th>Armada &amp; Supir</th>
                                                <th>Customer</th>
                                                <th class="text-end">Umur</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($live['operational']['notReturnDoList'] as $do)
                                                <tr>
                                                    <td class="fw-semibold">{{ $do['shipmentNumber'] }}</td>
                                                    <td class="phl-num">{{ $do['orderDate'] }}</td>
                                                    <td>
                                                        <div>{{ $do['plateNumber'] }}</div>
                                                        <small class="text-muted">{{ $do['driverName'] }}</small>
                                                    </td>
                                                    <td>{{ $do['customerName'] }}</td>
                                                    <td class="text-end phl-num">
                                                        <span class="badge {{ $do['daysPending'] > 14 ? 'bg-danger-subtle text-danger-emphasis' : 'bg-warning-subtle text-warning-emphasis' }}">
                                                            {{ number_format($do['daysPending'], 0, ',', '.') }} hari
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-4">Semua DO sudah kembali.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Periodic Top Customers & Top Fleets --}}
                    <div class="row g-4">
                        <div class="col-12 col-xl-6">
                            <div class="phl-dash-card h-100">
                                <div class="phl-card-head">
                                    <div>
                                        <h6 class="phl-card-title">Top 10 Customer (Periode)</h6>
                                        <p class="phl-card-sub">Berdasarkan jumlah order dan total nilai rute</p>
                                    </div>
                                    <a href="{{ route('report.order-detail.index') }}" class="phl-drill-link small">
                                        Detail Order <i class="mdi mdi-arrow-right"></i>
                                    </a>
                                </div>
                                <div class="phl-table-wrap" tabindex="0" role="region" aria-label="Top 10 customer">
                                    <table class="table phl-table" id="top-customers-table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Customer</th>
                                                <th class="text-end">Order</th>
                                                <th class="text-end">Total Omzet</th>
                                                <th class="text-end">Order Terakhir</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($periodic['topCustomers'] as $idx => $cust)
                                                <tr>
                                                    <td class="phl-num">{{ $idx + 1 }}</td>
                                                    <td class="fw-semibold">{{ $cust['name'] }}</td>
                                                    <td class="text-end phl-num">{{ number_format($cust['orders_count'], 0, ',', '.') }}</td>
                                                    <td class="text-end phl-num">Rp {{ number_format($cust['total_revenue'], 0, ',', '.') }}</td>
                                                    <td class="text-end phl-num text-muted">{{ $cust['latest_order'] }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-4">Belum ada order pada periode ini.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-xl-6">
                            <div class="phl-dash-card h-100">
                                <div class="phl-card-head">
                                    <div>
                                        <h6 class="phl-card-title">Top 10 Armada (Periode)</h6>
                                        <p class="phl-card-sub">Utilisasi armada internal &amp; vendor pada periode terpilih</p>
                                    </div>
                                    <a href="{{ route('report.profit-loss.index') }}" class="phl-drill-link small">
                                        Profit &amp; Loss <i class="mdi mdi-arrow-right"></i>
                                    </a>
                                </div>
                                <div class="phl-table-wrap" tabindex="0" role="region" aria-label="Top 10 armada">
                                    <table class="table phl-table" id="top-fleets-table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Nopol</th>
                                                <th>Jenis</th>
                                                <th class="text-end">Order</th>
                                                <th class="text-end">Total Omzet</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($periodic['topFleets'] as $idx => $fl)
                                                <tr>
                                                    <td class="phl-num">{{ $idx + 1 }}</td>
                                                    <td>
                                                        <div class="fw-semibold">{{ $fl['name'] }}</div>
                                                        <small class="text-muted">{{ $fl['fleet_type'] }}</small>
                                                    </td>
                                                    <td>
                                                        <span class="badge {{ $fl['company_type'] === 'Vendor' ? 'bg-secondary-subtle text-secondary-emphasis' : 'bg-primary-subtle text-primary-emphasis' }}">
                                                            {{ $fl['company_type'] }}
                                                        </span>
                                                    </td>
                                                    <td class="text-end phl-num">{{ number_format($fl['orders_count'], 0, ',', '.') }}</td>
                                                    <td class="text-end phl-num">Rp {{ number_format($fl['total_revenue'], 0, ',', '.') }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-4">Belum ada aktivitas armada pada periode ini.</td>
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

            {{-- TAB 2: KEUANGAN (AR / AP / KAS) --}}
            <div id="tab-panel-finance" role="tabpanel" aria-labelledby="tab-btn-finance" hidden>
                <div class="p-3 p-md-4 pt-2">
                    {{-- Internal Bank Balances --}}
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <div>
                            <h6 class="mb-0 fw-bold">Saldo Rekening Internal PHL (Live)</h6>
                            <small class="text-muted">Total saldo aktif gabungan: <strong class="text-body phl-num">Rp {{ number_format($live['bank']['totalBalance'], 0, ',', '.') }}</strong></small>
                        </div>
                        <a href="{{ route('bank.bank-book.index') }}" class="phl-drill-link small">
                            Lihat Buku Bank <i class="mdi mdi-arrow-right"></i>
                        </a>
                    </div>

                    <div class="row g-3 mb-4">
                        @forelse ($live['bank']['accounts'] as $acc)
                            <div class="col-12 col-md-4">
                                <div class="phl-bank-tile">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                        <span class="fw-bold small">{{ $acc['accountName'] }}</span>
                                        <span class="badge bg-primary-subtle text-primary-emphasis">{{ $acc['bankName'] }}</span>
                                    </div>
                                    <div class="text-muted small phl-num mb-2">No. Rek: {{ $acc['accountNumber'] }}</div>
                                    <div class="fs-5 fw-bold phl-num">Rp {{ number_format($acc['balance'], 0, ',', '.') }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <p class="text-muted mb-0">Belum ada rekening internal yang terhubung dengan mutasi saldo.</p>
                            </div>
                        @endforelse
                    </div>

                    {{-- Payable & Direct Payment Summary Strip --}}
                    <h6 class="fw-bold mb-3">Ringkasan Kewajiban &amp; Penagihan Menunggu (Live)</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-sm-6 col-xl-3">
                            <div class="phl-bank-tile">
                                <div class="small text-muted fw-semibold mb-1">Piutang Customer (AR)</div>
                                <div class="fw-bold fs-6 phl-num mb-1">Rp {{ number_format($live['ar']['totalRemaining'], 0, ',', '.') }}</div>
                                <div class="small text-muted mb-2">{{ number_format($live['ar']['unpaidCount'], 0, ',', '.') }} faktur · {{ number_format($live['ar']['customerCount'], 0, ',', '.') }} customer</div>
                                <a href="{{ route('invoice.unpaid') }}" class="phl-drill-link small">Faktur Belum Lunas <i class="mdi mdi-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-xl-3">
                            <div class="phl-bank-tile">
                                <div class="small text-muted fw-semibold mb-1">Hutang Vendor Armada</div>
                                <div class="fw-bold fs-6 phl-num mb-1">Rp {{ number_format($live['ap']['vendor']['totalRemaining'], 0, ',', '.') }}</div>
                                <div class="small text-muted mb-2">{{ number_format($live['ap']['vendor']['notaCount'], 0, ',', '.') }} nota · {{ number_format($live['ap']['vendor']['orderCount'], 0, ',', '.') }} order</div>
                                <a href="{{ route('vendor.invoice.unpaid') }}" class="phl-drill-link small">Invoice Vendor <i class="mdi mdi-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-xl-3">
                            <div class="phl-bank-tile">
                                <div class="small text-muted fw-semibold mb-1">Pembayaran Langsung (Non-DO)</div>
                                <div class="fw-bold fs-6 phl-num mb-1">Rp {{ number_format($live['ap']['directPayment']['waitingRemaining'] + $live['ap']['directPayment']['notaRemaining'], 0, ',', '.') }}</div>
                                <div class="small text-muted mb-2">{{ number_format($live['ap']['directPayment']['waitingCount'], 0, ',', '.') }} order tunggu · {{ number_format($live['ap']['directPayment']['notaCount'], 0, ',', '.') }} nota</div>
                                <a href="{{ route('direct-payment.order.waiting') }}" class="phl-drill-link small">Pembayaran Langsung <i class="mdi mdi-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-xl-3">
                            <div class="phl-bank-tile">
                                <div class="small text-muted fw-semibold mb-1">Hutang Pembelian Supplier</div>
                                <div class="fw-bold fs-6 phl-num mb-1">Rp {{ number_format($live['ap']['supplier']['totalRemaining'], 0, ',', '.') }}</div>
                                <div class="small text-muted mb-2">{{ number_format($live['ap']['supplier']['totalCount'], 0, ',', '.') }} PO belum lunas</div>
                                <a href="{{ route('purchasing.purchase-payment.index') }}" class="phl-drill-link small">Hutang Supplier <i class="mdi mdi-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>

                    {{-- Priority Overdue Invoices Table --}}
                    <div class="phl-dash-card">
                        <div class="phl-card-head">
                            <div>
                                <h6 class="phl-card-title">Prioritas Penagihan Invoice Jatuh Tempo (Live)</h6>
                                <p class="phl-card-sub">Menampilkan faktur yang masih memiliki sisa tagihan, diurutkan dari jatuh tempo paling awal</p>
                            </div>
                            <a href="{{ route('report.receivable-aging.index') }}" class="phl-drill-link small">
                                Laporan Umur Piutang <i class="mdi mdi-arrow-right"></i>
                            </a>
                        </div>
                        <div class="phl-table-wrap" tabindex="0" role="region" aria-label="Prioritas penagihan invoice">
                            <table class="table phl-table">
                                <thead>
                                    <tr>
                                        <th>No. Invoice</th>
                                        <th>Customer</th>
                                        <th>Tgl Invoice</th>
                                        <th>Jatuh Tempo</th>
                                        <th class="text-end">Sisa Tagihan</th>
                                        <th>Status</th>
                                        <th class="text-center">PDF</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($live['ar']['urgentInvoices'] as $inv)
                                        <tr>
                                            <td class="fw-semibold">{{ $inv['invoiceNumber'] }}</td>
                                            <td>{{ $inv['customerName'] }}</td>
                                            <td class="phl-num">{{ $inv['invoiceDate'] }}</td>
                                            <td class="phl-num">{{ $inv['dueDate'] }}</td>
                                            <td class="text-end phl-num fw-semibold">Rp {{ number_format($inv['remainingAmount'], 0, ',', '.') }}</td>
                                            <td>
                                                <span class="badge {{ $inv['badgeClass'] }}">{{ $inv['statusLabel'] }}</span>
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('invoice.pdf', $inv['id']) }}" target="_blank"
                                                    class="btn btn-sm btn-outline-danger py-1 px-2" aria-label="Cetak PDF {{ $inv['invoiceNumber'] }}">
                                                    <i class="mdi mdi-file-pdf-box"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">Tidak ada invoice yang memiliki sisa tagihan saat ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 3: MAINTENANCE & GUDANG --}}
            <div id="tab-panel-maint" role="tabpanel" aria-labelledby="tab-btn-maint" hidden>
                <div class="p-3 p-md-4 pt-2">
                    {{-- Maintenance Summary Strip --}}
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-4">
                            <div class="phl-bank-tile">
                                <div class="small text-muted fw-semibold mb-1">Total Perbaikan Armada (Periode)</div>
                                <div class="fs-5 fw-bold phl-num" id="maint-total-events">{{ number_format($periodic['maintenance']['events'], 0, ',', '.') }} Kali</div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="phl-bank-tile">
                                <div class="small text-muted fw-semibold mb-1">Total Sparepart Digunakan (Periode)</div>
                                <div class="fs-5 fw-bold phl-num" id="maint-total-qty">{{ number_format($periodic['maintenance']['qty'], 0, ',', '.') }} Unit</div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="phl-bank-tile">
                                <div class="small text-muted fw-semibold mb-1">Total Biaya Maintenance (Periode)</div>
                                <div class="fs-5 fw-bold phl-num" id="maint-total-cost">Rp {{ number_format($periodic['maintenance']['cost'], 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-12 col-xl-6">
                            <div class="phl-dash-card h-100">
                                <div class="phl-card-head">
                                    <div>
                                        <h6 class="phl-card-title">Top 10 Biaya Maintenance per Armada (Periode)</h6>
                                        <p class="phl-card-sub">Armada dengan beban biaya sparepart tertinggi pada periode terpilih</p>
                                    </div>
                                    <a href="{{ route('report.maintenance-fleet.index') }}" class="phl-drill-link small">
                                        Laporan Maintenance <i class="mdi mdi-arrow-right"></i>
                                    </a>
                                </div>
                                <div class="phl-table-wrap" tabindex="0" role="region" aria-label="Biaya maintenance per armada">
                                    <table class="table phl-table" id="top-maintenance-table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Nopol</th>
                                                <th class="text-end">Perbaikan</th>
                                                <th class="text-end">Qty Part</th>
                                                <th class="text-end">Total Biaya</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($periodic['maintenance']['topFleets'] as $idx => $mf)
                                                <tr>
                                                    <td class="phl-num">{{ $idx + 1 }}</td>
                                                    <td>
                                                        <div class="fw-semibold">{{ $mf['plateNumber'] }}</div>
                                                        <small class="text-muted">{{ $mf['fleetTypeName'] }}</small>
                                                    </td>
                                                    <td class="text-end phl-num">{{ number_format($mf['total_events'], 0, ',', '.') }}x</td>
                                                    <td class="text-end phl-num">{{ number_format($mf['total_qty'], 0, ',', '.') }}</td>
                                                    <td class="text-end phl-num fw-semibold">Rp {{ number_format($mf['total_cost'], 0, ',', '.') }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-4">Belum ada catatan maintenance pada periode ini.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-xl-6">
                            <div class="phl-dash-card h-100">
                                <div class="phl-card-head">
                                    <div>
                                        <h6 class="phl-card-title">Stok Sparepart Kritis / Menipis (Live)</h6>
                                        <p class="phl-card-sub">Total {{ number_format($live['inventory']['lowStockCount'], 0, ',', '.') }} item sparepart dengan stok &le; 5</p>
                                    </div>
                                    <a href="{{ route('inventory.stock.index') }}" class="phl-drill-link small">
                                        Cek Stok Gudang <i class="mdi mdi-arrow-right"></i>
                                    </a>
                                </div>
                                <div class="phl-table-wrap" tabindex="0" role="region" aria-label="Stok sparepart kritis">
                                    <table class="table phl-table">
                                        <thead>
                                            <tr>
                                                <th>Nama Item</th>
                                                <th>Kategori</th>
                                                <th>Gudang</th>
                                                <th class="text-end">Sisa Stok</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($live['inventory']['lowStockItems'] as $item)
                                                <tr>
                                                    <td class="fw-semibold">{{ $item['name'] }}</td>
                                                    <td>{{ $item['categoryName'] }}</td>
                                                    <td>{{ $item['warehouseName'] }}</td>
                                                    <td class="text-end phl-num">
                                                        <span class="badge {{ $item['qty'] <= 0 ? 'bg-danger-subtle text-danger-emphasis' : 'bg-warning-subtle text-warning-emphasis' }}">
                                                            {{ number_format($item['qty'], 0, ',', '.') }} {{ $item['unitName'] }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted py-4">Semua stok sparepart dalam kondisi aman.</td>
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
@endsection

@push('script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        (function () {
            const summaryUrl = @json(route('dashboard.summary'));
            let trendData = @json($periodic['trend']);
            let trendChart = null;
            let activePreset = @json($period['preset']);

            const formatNumber = (val) => new Intl.NumberFormat('id-ID').format(Math.round(Number(val || 0)));
            const formatCurrency = (val) => 'Rp ' + formatNumber(val);
            const escapeHtml = (str) => String(str ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');

            // 1. Flatpickr Date Inputs
            const startInput = document.getElementById('filter-start-date');
            const endInput = document.getElementById('filter-end-date');

            if (typeof flatpickr !== 'undefined') {
                flatpickr(startInput, { dateFormat: 'Y-m-d', allowInput: true });
                flatpickr(endInput, { dateFormat: 'Y-m-d', allowInput: true });
            }

            // 2. Accessible Tab Switching + Keyboard Navigation
            const tabs = Array.from(document.querySelectorAll('.phl-tab'));
            const panels = Array.from(document.querySelectorAll('[role="tabpanel"]'));

            function activateTab(targetTab) {
                const panelId = targetTab.getAttribute('data-panel');
                tabs.forEach((tab) => {
                    const selected = tab === targetTab;
                    tab.setAttribute('aria-selected', String(selected));
                    tab.setAttribute('tabindex', selected ? '0' : '-1');
                });
                panels.forEach((panel) => {
                    const match = panel.id === panelId;
                    if (match) {
                        panel.removeAttribute('hidden');
                        panel.setAttribute('tabindex', '0');
                    } else {
                        panel.setAttribute('hidden', '');
                        panel.removeAttribute('tabindex');
                    }
                });
                targetTab.scrollIntoView({ block: 'nearest', inline: 'nearest' });
            }

            tabs.forEach((tab, index) => {
                tab.setAttribute('tabindex', tab.getAttribute('aria-selected') === 'true' ? '0' : '-1');
                tab.addEventListener('click', () => activateTab(tab));
                tab.addEventListener('keydown', (e) => {
                    let nextIndex = null;
                    if (e.key === 'ArrowRight') nextIndex = (index + 1) % tabs.length;
                    if (e.key === 'ArrowLeft') nextIndex = (index - 1 + tabs.length) % tabs.length;
                    if (e.key === 'Home') nextIndex = 0;
                    if (e.key === 'End') nextIndex = tabs.length - 1;
                    if (nextIndex !== null) {
                        e.preventDefault();
                        tabs[nextIndex].focus();
                        activateTab(tabs[nextIndex]);
                    }
                });
            });

            // 3. Chart Initialization + Theme Reactivity
            function buildChartColors() {
                const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
                return {
                    text: isDark ? '#e2e8f0' : '#334155',
                    grid: isDark ? 'rgba(203, 213, 225, 0.14)' : 'rgba(148, 163, 184, 0.22)',
                    barBg: isDark ? 'rgba(139, 127, 245, 0.35)' : 'rgba(91, 76, 230, 0.22)',
                    barBorder: isDark ? '#8b7ff5' : '#5b4ce6',
                    revColor: isDark ? '#34d399' : '#059669',
                    maintColor: isDark ? '#fbbf24' : '#d97706',
                };
            }

            function renderChart() {
                const canvas = document.getElementById('orderTrendChart');
                if (!canvas || typeof Chart === 'undefined') return;
                const c = buildChartColors();

                if (trendChart) {
                    trendChart.destroy();
                }

                trendChart = new Chart(canvas.getContext('2d'), {
                    data: {
                        labels: trendData.labels,
                        datasets: [
                            {
                                type: 'bar',
                                label: 'Jumlah Order',
                                data: trendData.orderCounts,
                                backgroundColor: c.barBg,
                                borderColor: c.barBorder,
                                borderWidth: 1,
                                borderRadius: 4,
                                yAxisID: 'yCount',
                                order: 3,
                            },
                            {
                                type: 'line',
                                label: 'Omzet Order (Rp)',
                                data: trendData.revenues,
                                borderColor: c.revColor,
                                backgroundColor: c.revColor,
                                tension: 0.25,
                                borderWidth: 2,
                                pointRadius: 2.5,
                                yAxisID: 'yMoney',
                                order: 1,
                            },
                            {
                                type: 'line',
                                label: 'Biaya Maintenance (Rp)',
                                data: trendData.maintenanceCosts,
                                borderColor: c.maintColor,
                                backgroundColor: c.maintColor,
                                tension: 0.25,
                                borderWidth: 2,
                                borderDash: [4, 4],
                                pointRadius: 2.5,
                                yAxisID: 'yMoney',
                                order: 2,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: { color: c.text, usePointStyle: true, boxWidth: 10 },
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (ctx) {
                                        if (ctx.dataset.yAxisID === 'yMoney') {
                                            return ctx.dataset.label + ': ' + formatCurrency(ctx.parsed.y);
                                        }
                                        return ctx.dataset.label + ': ' + formatNumber(ctx.parsed.y);
                                    },
                                },
                            },
                        },
                        scales: {
                            x: {
                                ticks: { color: c.text, maxRotation: 0, autoSkip: true },
                                grid: { display: false },
                            },
                            yCount: {
                                type: 'linear',
                                position: 'left',
                                beginAtZero: true,
                                ticks: {
                                    color: c.text,
                                    precision: 0,
                                },
                                grid: { color: c.grid },
                                title: { display: true, text: 'Order', color: c.text },
                            },
                            yMoney: {
                                type: 'linear',
                                position: 'right',
                                beginAtZero: true,
                                ticks: {
                                    color: c.text,
                                    callback: function (val) {
                                        if (val >= 1000000000) return 'Rp ' + (val / 1000000000).toFixed(1) + ' M';
                                        if (val >= 1000000) return 'Rp ' + (val / 1000000).toFixed(0) + ' Jt';
                                        return 'Rp ' + formatNumber(val);
                                    },
                                },
                                grid: { drawOnChartArea: false },
                                title: { display: true, text: 'Nominal (Rp)', color: c.text },
                            },
                        },
                    },
                });
            }

            renderChart();

            new MutationObserver(function () {
                renderChart();
            }).observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['data-bs-theme'],
            });

            // 4. Period Filtering via Fetch
            const presetBtns = Array.from(document.querySelectorAll('.phl-preset-btn'));
            const applyBtn = document.getElementById('btn-apply-period');
            const applyIcon = document.getElementById('apply-icon');
            const applyText = document.getElementById('apply-text');
            const kpiRow = document.getElementById('executive-kpi-row');
            const chartCard = document.getElementById('trend-chart-card');

            function setLoading(loading) {
                applyBtn.disabled = loading;
                presetBtns.forEach((b) => { b.disabled = loading; });
                if (loading) {
                    applyIcon.className = 'mdi mdi-loading mdi-spin';
                    applyText.textContent = 'Memuat...';
                    kpiRow.classList.add('phl-is-loading');
                    chartCard.classList.add('phl-is-loading');
                } else {
                    applyIcon.className = 'mdi mdi-filter-variant';
                    applyText.textContent = 'Terapkan';
                    kpiRow.classList.remove('phl-is-loading');
                    chartCard.classList.remove('phl-is-loading');
                }
            }

            function renderPeriodicSections(payload) {
                const { period, periodic } = payload;

                startInput.value = period.startDate;
                endInput.value = period.endDate;
                document.getElementById('active-period-text').textContent = period.displayRange;
                document.getElementById('chart-period-label').textContent = period.displayRange;
                document.getElementById('chart-granularity-badge').textContent =
                    periodic.trend.granularity === 'daily' ? 'Harian' : 'Bulanan';

                document.getElementById('kpi-order-count').textContent = formatNumber(periodic.orders.count) + ' Order';
                document.getElementById('kpi-order-revenue').textContent = formatCurrency(periodic.orders.revenue);
                document.getElementById('kpi-order-split').textContent =
                    'Internal ' + formatNumber(periodic.orders.internalCount) + ' · Vendor ' + formatNumber(periodic.orders.vendorCount);
                document.getElementById('kpi-order-scope').textContent = period.label;

                // Pipeline status
                const pipelineEl = document.getElementById('ops-pipeline-grid');
                if (pipelineEl) {
                    pipelineEl.innerHTML = periodic.statusBreakdown.map((st) => `
                        <div class="phl-pipeline-item">
                            <div class="phl-pipeline-name" title="${escapeHtml(st.label)}">${escapeHtml(st.label)}</div>
                            <div class="phl-pipeline-count phl-num">${formatNumber(st.total)}</div>
                        </div>
                    `).join('');
                }

                // Top Customers
                const custBody = document.querySelector('#top-customers-table tbody');
                if (custBody) {
                    if (!periodic.topCustomers.length) {
                        custBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Belum ada order pada periode ini.</td></tr>';
                    } else {
                        custBody.innerHTML = periodic.topCustomers.map((c, idx) => `
                            <tr>
                                <td class="phl-num">${idx + 1}</td>
                                <td class="fw-semibold">${escapeHtml(c.name)}</td>
                                <td class="text-end phl-num">${formatNumber(c.orders_count)}</td>
                                <td class="text-end phl-num">${formatCurrency(c.total_revenue)}</td>
                                <td class="text-end phl-num text-muted">${escapeHtml(c.latest_order)}</td>
                            </tr>
                        `).join('');
                    }
                }

                // Top Fleets
                const fleetBody = document.querySelector('#top-fleets-table tbody');
                if (fleetBody) {
                    if (!periodic.topFleets.length) {
                        fleetBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Belum ada aktivitas armada pada periode ini.</td></tr>';
                    } else {
                        fleetBody.innerHTML = periodic.topFleets.map((fl, idx) => `
                            <tr>
                                <td class="phl-num">${idx + 1}</td>
                                <td>
                                    <div class="fw-semibold">${escapeHtml(fl.name)}</div>
                                    <small class="text-muted">${escapeHtml(fl.fleet_type)}</small>
                                </td>
                                <td>
                                    <span class="badge ${fl.company_type === 'Vendor' ? 'bg-secondary-subtle text-secondary-emphasis' : 'bg-primary-subtle text-primary-emphasis'}">
                                        ${escapeHtml(fl.company_type)}
                                    </span>
                                </td>
                                <td class="text-end phl-num">${formatNumber(fl.orders_count)}</td>
                                <td class="text-end phl-num">${formatCurrency(fl.total_revenue)}</td>
                            </tr>
                        `).join('');
                    }
                }

                // Maintenance Summary & Top Fleets
                document.getElementById('maint-total-events').textContent = formatNumber(periodic.maintenance.events) + ' Kali';
                document.getElementById('maint-total-qty').textContent = formatNumber(periodic.maintenance.qty) + ' Unit';
                document.getElementById('maint-total-cost').textContent = formatCurrency(periodic.maintenance.cost);

                const maintBody = document.querySelector('#top-maintenance-table tbody');
                if (maintBody) {
                    if (!periodic.maintenance.topFleets.length) {
                        maintBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Belum ada catatan maintenance pada periode ini.</td></tr>';
                    } else {
                        maintBody.innerHTML = periodic.maintenance.topFleets.map((mf, idx) => `
                            <tr>
                                <td class="phl-num">${idx + 1}</td>
                                <td>
                                    <div class="fw-semibold">${escapeHtml(mf.plateNumber)}</div>
                                    <small class="text-muted">${escapeHtml(mf.fleetTypeName)}</small>
                                </td>
                                <td class="text-end phl-num">${formatNumber(mf.total_events)}x</td>
                                <td class="text-end phl-num">${formatNumber(mf.total_qty)}</td>
                                <td class="text-end phl-num fw-semibold">${formatCurrency(mf.total_cost)}</td>
                            </tr>
                        `).join('');
                    }
                }

                // Update Trend Chart
                trendData = periodic.trend;
                renderChart();
            }

            async function loadPeriod(preset, customStart, customEnd) {
                activePreset = preset;
                presetBtns.forEach((btn) => {
                    btn.classList.toggle('is-active', btn.getAttribute('data-preset') === preset);
                });

                const params = new URLSearchParams({ preset });
                if (preset === 'custom' && customStart && customEnd) {
                    params.set('startDate', customStart);
                    params.set('endDate', customEnd);
                }

                setLoading(true);
                try {
                    const res = await fetch(summaryUrl + '?' + params.toString(), {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    const payload = await res.json();
                    renderPeriodicSections(payload);
                } catch (err) {
                    console.error('Gagal memuat ringkasan dashboard:', err);
                } finally {
                    setLoading(false);
                }
            }

            presetBtns.forEach((btn) => {
                btn.addEventListener('click', function () {
                    loadPeriod(btn.getAttribute('data-preset'));
                });
            });

            applyBtn.addEventListener('click', function () {
                const startVal = startInput.value.trim();
                const endVal = endInput.value.trim();
                if (!startVal || !endVal) return;
                loadPeriod('custom', startVal, endVal);
            });
        })();
    </script>
@endpush
