@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Laporan',
    'secondSegment' => 'Umur Piutang',
])

@push('style')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">
    @include('purchasing.purchase.partials.table-style')

    <style>
        .avatar-badge-entity {
            align-items: center;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(79, 70, 229, .25);
            color: #fff;
            display: inline-flex;
            flex-shrink: 0;
            height: 44px;
            justify-content: center;
            width: 44px;
        }

        /* ── Standar Filter Card Collapse (PHL §3) ── */
        .filter-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            margin: 0 0 20px;
            overflow: hidden;
        }
        .filter-card-header {
            align-items: center;
            background: #f8fafc;
            border: 0;
            color: #334155;
            display: flex;
            justify-content: space-between;
            padding: 12px 16px;
            text-align: left;
            width: 100%;
        }
        .filter-card-header:hover { background: #f1f5f9; }
        .filter-card-heading { align-items: center; display: flex; gap: 8px; }
        .filter-card-heading i { color: #4f46e5; font-size: 17px; }
        .filter-card-heading strong { font-size: 13px; font-weight: 700; }
        .filter-card-heading small { color: #94a3b8; font-size: 11px; font-weight: 400; }
        .filter-card-chevron { transition: transform .2s ease; }
        .filter-card-header[aria-expanded="true"] .filter-card-chevron { transform: rotate(180deg); }
        .filter-card .filter-collapse { border-top: 1px solid #e2e8f0; }
        .filter-card .filter-collapse-body { padding: 16px; }
        .filter-card .row { --bs-gutter-y: .75rem; }

        .filter-label { color: #64748b; display: block; font-size: 12px; font-weight: 600; margin-bottom: 6px; }
        .filter-control,
        .filter-card .form-control {
            background-color: #fff !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            color: #334155 !important;
            font-size: 13px !important;
            height: 38px !important;
        }
        .filter-control { padding: 0 12px 0 36px !important; }

        .btn-filter-primary {
            align-items: center;
            background: linear-gradient(135deg, #4f46e5, #6366f1) !important;
            border: 0 !important;
            border-radius: 8px !important;
            color: #fff !important;
            display: inline-flex;
            font-size: 13px;
            font-weight: 600;
            gap: 6px;
            height: 38px;
            justify-content: center;
            padding: 0 16px;
            white-space: nowrap;
        }
        .btn-filter-reset {
            align-items: center;
            background: #fff !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            color: #64748b !important;
            display: inline-flex;
            height: 38px;
            justify-content: center;
            min-width: 38px;
            padding: 0 !important;
        }

        #dtAgingDetail thead th,
        #dtAgingDetail tbody td { white-space: nowrap; }
        .aging-legend { font-size: 11.5px; color: #64748b; }

        /* ── Dark mode filter card (WAJIB) ── */
        html[data-bs-theme="dark"] .filter-card { background: var(--bs-secondary-bg); border-color: var(--bs-border-color); }
        html[data-bs-theme="dark"] .filter-card-header { background: var(--bs-secondary-bg); color: var(--bs-body-color); }
        html[data-bs-theme="dark"] .filter-card-header:hover { background: var(--bs-tertiary-bg); }
        html[data-bs-theme="dark"] .filter-card .filter-collapse { border-top-color: var(--bs-border-color); }
        html[data-bs-theme="dark"] .filter-label { color: var(--bs-secondary-color); }
        html[data-bs-theme="dark"] .filter-control,
        html[data-bs-theme="dark"] .filter-card .form-control {
            background-color: var(--bs-tertiary-bg) !important;
            border-color: var(--bs-border-color) !important;
            color: var(--bs-body-color) !important;
        }
        html[data-bs-theme="dark"] .btn-filter-reset {
            background: var(--bs-tertiary-bg) !important;
            border-color: var(--bs-border-color) !important;
            color: var(--bs-body-color) !important;
        }
    </style>
@endpush

@section('content')
    @php
        $detailUrl = route($view . 'detail', ['customerCode' => $customer->code]);
        $filterActive = filled($startDate) || filled($endDate);
        $statusLabel = \App\Exports\ReceivableAgingReport::normalizeStatus($status);
    @endphp

    <div class="col-sm-12">
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-badge-entity">
                            <i class="mdi mdi-account-cash-outline fs-22"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="fs-16 fw-bold text-dark">{{ $customer->name }}</span>
                                <span class="badge bg-primary-subtle text-primary px-2 py-1"
                                    style="border-radius: 6px; font-weight: 600; font-size: 11px;">Piutang</span>
                            </div>
                            <div class="text-muted small mt-1">
                                <i class="mdi mdi-identifier me-1"></i>Kode: <span class="font-monospace">{{ $customer->code }}</span>
                                <span class="mx-2">•</span>
                                <i class="mdi mdi-calendar-clock me-1"></i>Tempo: <strong>{{ (int) ($customer->dueDateDuration ?? 0) }} hari</strong>
                                @if ($customer->trashed())
                                    <span class="mx-2">•</span>
                                    <span class="badge bg-danger-subtle text-danger-emphasis">Customer terhapus</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        @if ($filterActive)
                            <div class="d-flex align-items-center gap-2 bg-light px-3 py-2 rounded-3 border" style="font-size: 12.5px;">
                                <i class="mdi mdi-calendar-range text-primary fs-16"></i>
                                <span>Periode:
                                    <strong>{{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d/m/Y') : 'Awal' }}</strong>
                                    s/d
                                    <strong>{{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d/m/Y') : 'Sekarang' }}</strong>
                                </span>
                            </div>
                        @endif
                        <a href="{{ route($view . 'index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                            <i class="mdi mdi-arrow-left me-1"></i>Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Total Sisa Piutang</div>
                            <div class="stat-value text-dark">Rp {{ number_format($summary->totalRemaining, 0, ',', '.') }}</div>
                        </div>
                        <div class="stat-icon-wrapper bg-primary-subtle text-primary"><i class="mdi mdi-cash-multiple"></i></div>
                    </div>
                    <div class="stat-desc">Dari tagihan Rp {{ number_format($summary->totalBilling, 0, ',', '.') }}</div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Overdue</div>
                            <div class="stat-value text-danger">Rp {{ number_format($summary->overdueAmount, 0, ',', '.') }}</div>
                        </div>
                        <div class="stat-icon-wrapper bg-danger-subtle text-danger"><i class="mdi mdi-alert-circle-outline"></i></div>
                    </div>
                    <div class="stat-desc text-danger">
                        <strong>{{ number_format($summary->overdueCount, 0, ',', '.') }}</strong> faktur lewat jatuh tempo
                        @if ($summary->maxOverdueDays > 0)
                            · maks <strong>{{ number_format($summary->maxOverdueDays, 0, ',', '.') }}</strong> hari
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Jatuh Tempo ≤ 7 Hari</div>
                            <div class="stat-value text-warning-emphasis">Rp {{ number_format($summary->dueSoonAmount, 0, ',', '.') }}</div>
                        </div>
                        <div class="stat-icon-wrapper bg-warning-subtle text-warning"><i class="mdi mdi-calendar-clock"></i></div>
                    </div>
                    <div class="stat-desc"><strong>{{ number_format($summary->dueSoonCount, 0, ',', '.') }}</strong> faktur mendekati jatuh tempo</div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Faktur Belum Lunas</div>
                            <div class="stat-value text-dark">{{ number_format($summary->unpaidCount, 0, ',', '.') }}</div>
                        </div>
                        <div class="stat-icon-wrapper bg-info-subtle text-info"><i class="mdi mdi-receipt-text-outline"></i></div>
                    </div>
                    <div class="stat-desc">Terbayar Rp {{ number_format($summary->totalPaid, 0, ',', '.') }} · claim Rp {{ number_format($summary->totalClaim, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>

        <div class="table-container-card mb-4">
            <div class="p-3 p-md-4">
                <div class="filter-card">
                    <button type="button" class="filter-card-header" data-bs-toggle="collapse"
                        data-bs-target="#receivableAgingDetailFilter" aria-expanded="{{ $filterActive ? 'true' : 'false' }}"
                        aria-controls="receivableAgingDetailFilter">
                        <span class="filter-card-heading">
                            <i class="mdi mdi-filter-variant"></i>
                            <strong>Filter Data</strong>
                            <small>Saring faktur berdasarkan periode tanggal faktur</small>
                        </span>
                        <i class="mdi mdi-chevron-down filter-card-chevron"></i>
                    </button>

                    <div class="collapse filter-collapse {{ $filterActive ? 'show' : '' }}" id="receivableAgingDetailFilter">
                        <div class="filter-collapse-body">
                            <form method="GET" action="{{ $detailUrl }}" id="detailFilterForm">
                                <input type="hidden" name="status" value="{{ $statusLabel }}">
                                <div class="row g-3 align-items-end">
                                    <div class="col-xl-4 col-md-6">
                                        <label class="filter-label" for="startDate">Dari Tanggal</label>
                                        <input class="form-control filter-control" name="startDate" id="startDate"
                                            type="text" value="{{ $startDate }}" placeholder="Pilih tanggal mulai">
                                    </div>
                                    <div class="col-xl-4 col-md-6">
                                        <label class="filter-label" for="endDate">Sampai Tanggal</label>
                                        <input class="form-control filter-control" name="endDate" id="endDate"
                                            type="text" value="{{ $endDate }}" placeholder="Pilih tanggal akhir">
                                    </div>
                                    <div class="col-xl-4 col-md-6 d-flex align-items-end justify-content-md-end gap-2">
                                        <button class="btn btn-filter-primary flex-grow-1 flex-md-grow-0" type="submit">
                                            <i class="mdi mdi-filter-outline"></i> Terapkan Filter
                                        </button>
                                        <a href="{{ $detailUrl }}" class="btn btn-filter-reset" data-bs-toggle="tooltip" title="Reset Filter">
                                            <i class="mdi mdi-refresh"></i>
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                    <div>
                        <h6 class="fw-bold mb-0">Daftar Faktur</h6>
                        <span class="aging-legend">Umur piutang dihitung dari jatuh tempo efektif terhadap hari ini.</span>
                    </div>
                </div>

                <div class="table-responsive custom-scrollbar">
                    <table class="table purchase-table w-100 nowrap" id="dtAgingDetail">
                        <thead>
                            <tr>
                                <th style="width: 56px;">Aksi</th>
                                <th style="width: 52px;">No</th>
                                <th>No Faktur</th>
                                <th>Tgl Faktur</th>
                                <th>Jatuh Tempo</th>
                                <th>Total Tagihan</th>
                                <th>Dibayar</th>
                                <th>Claim</th>
                                <th>Sisa</th>
                                <th>Umur (hari)</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        $(document).ready(function() {
            const numberFormat = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 });
            const currency = value => 'Rp ' + numberFormat.format(value || 0);
            const displayDate = value => {
                if (!value) return '-';
                const parts = value.split('-');
                return parts.length === 3 ? parts[2] + '-' + parts[1] + '-' + parts[0] : value;
            };

            let startPicker;
            let endPicker;

            startPicker = flatpickr('#startDate', {
                dateFormat: 'Y-m-d',
                allowInput: true,
                onChange: function(selectedDates, dateStr) {
                    if (endPicker) endPicker.set('minDate', dateStr || null);
                }
            });

            endPicker = flatpickr('#endDate', {
                dateFormat: 'Y-m-d',
                allowInput: true,
                onChange: function(selectedDates, dateStr) {
                    if (startPicker) startPicker.set('maxDate', dateStr || null);
                }
            });

            $('#dtAgingDetail').DataTable({
                processing: true,
                serverSide: false,
                destroy: true,
                pageLength: 25,
                scrollX: true,
                ajax: {
                    url: "{{ route('dt.receivable-aging-detail', ['customerCode' => $customer->code]) }}",
                    data: function(d) {
                        d.startDate = @json($startDate);
                        d.endDate = @json($endDate);
                        d.status = @json($statusLabel);
                    }
                },
                columns: [
                    { data: 'action', className: 'text-center align-middle', orderable: false },
                    { data: 'DT_RowIndex', className: 'text-center align-middle', orderable: false },
                    { data: 'invoiceNumber', className: 'align-middle font-monospace fw-semibold' },
                    {
                        data: 'invoiceDate',
                        className: 'align-middle text-center',
                        render: function(data, type) {
                            return type === 'display' ? displayDate(data) : data;
                        }
                    },
                    {
                        data: 'dueDate',
                        className: 'align-middle text-center',
                        render: function(data, type) {
                            return type === 'display' ? displayDate(data) : data;
                        }
                    },
                    {
                        data: 'billingAmount',
                        className: 'text-end align-middle',
                        render: function(data, type) {
                            return type === 'display' ? currency(data) : data;
                        }
                    },
                    {
                        data: 'paidAmount',
                        className: 'text-end align-middle',
                        render: function(data, type) {
                            return type === 'display' ? currency(data) : data;
                        }
                    },
                    {
                        data: 'claimAmount',
                        className: 'text-end align-middle',
                        render: function(data, type) {
                            return type === 'display' ? currency(data) : data;
                        }
                    },
                    {
                        data: 'remainingAmount',
                        className: 'text-end align-middle',
                        render: function(data, type) {
                            return type === 'display' ? '<strong class="text-dark">' + currency(data) + '</strong>' : data;
                        }
                    },
                    {
                        data: 'overdueDays',
                        className: 'text-end align-middle',
                        render: function(data, type) {
                            return type === 'display' ? numberFormat.format(data || 0) : data;
                        }
                    },
                    { data: 'statusBadge', className: 'text-center align-middle', orderable: false }
                ],
                columnDefs: [
                    { searchable: false, targets: [0, 1, 3, 4, 5, 6, 7, 8, 9, 10] }
                ],
                order: [[9, 'desc']],
                rowCallback: function(row, data) {
                    if (data.remainingAmount > 0 && data.daysToDue < 0) {
                        $(row).addClass('table-danger-subtle');
                    } else if (data.remainingAmount > 0 && data.daysToDue <= 7) {
                        $(row).addClass('table-warning-subtle');
                    }
                },
                language: {
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ faktur',
                    infoEmpty: 'Tidak ada faktur',
                    zeroRecords: 'Tidak ada faktur pada periode ini',
                    processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Memuat data...'
                }
            });
        });
    </script>
@endpush
