@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Report',
    'secondSegment' => $title,
])

@push('style')
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/libs/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/libs/datatables.net-keytable-bs5/css/keyTable.bootstrap5.min.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/libs/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">

    <style>
        /* ── Summary KPI cards ── */
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

        .summary-maintenance {
            background: linear-gradient(135deg, #eef2ff, #e0e7ff);
            border-color: #c7d2fe;
            color: #3730a3;
        }

        .summary-qty {
            background: linear-gradient(135deg, #ecfdf5, #d1fae5);
            border-color: #a7f3d0;
            color: #065f46;
        }

        .summary-cost {
            background: linear-gradient(135deg, #fefce8, #fef9c3);
            border-color: #fde68a;
            color: #92400e;
        }

        /* ── Fleet info banner ── */
        .avatar-badge-fleet {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.25);
            flex-shrink: 0;
        }

        .btn-icon {
            border-radius: 8px !important;
            padding: 6px 10px;
            font-size: 13px;
            transition: all 0.2s ease;
        }

        .btn-icon:hover {
            transform: translateY(-1px);
        }

        /* Standard report table styling. See docs/table-standard.md. */
        #dt-maintenance-detail {
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        #dt-maintenance-detail thead th {
            background-color: #f8fafc;
            color: #475569;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 13px 12px;
            border-bottom: 2px solid #e2e8f0;
            border-top: none;
            white-space: nowrap;
            vertical-align: middle;
        }

        #dt-maintenance-detail tbody td {
            padding: 11px 12px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            font-size: 12.5px;
            white-space: nowrap;
            vertical-align: middle;
        }

        #dt-maintenance-detail tbody tr {
            transition: background-color 0.15s ease;
        }

        #dt-maintenance-detail tbody tr:hover {
            background-color: #f8fafc !important;
        }

        #dt-maintenance-detail_wrapper .dataTables_filter input {
            border-radius: 8px;
            font-size: 12px;
        }

        #dt-maintenance-detail_wrapper .dataTables_length select {
            border-radius: 8px;
            font-size: 12px;
        }

        #dt-maintenance-detail_wrapper .dataTables_info,
        #dt-maintenance-detail_wrapper .dataTables_length,
        #dt-maintenance-detail_wrapper .dataTables_filter {
            color: #64748b;
            font-size: 12px;
        }

        /* ── Dark mode ─────────────────────────────── */
        html[data-bs-theme="dark"] #dt-maintenance-detail {
            border-color: var(--bs-border-color) !important;
        }
        html[data-bs-theme="dark"] #dt-maintenance-detail thead th {
            background-color: var(--bs-tertiary-bg) !important;
            color: var(--bs-body-color) !important;
            border-color: var(--bs-border-color) !important;
        }
        html[data-bs-theme="dark"] #dt-maintenance-detail tbody td {
            color: var(--bs-body-color) !important;
            border-color: var(--bs-border-color) !important;
        }
        html[data-bs-theme="dark"] #dt-maintenance-detail tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.05) !important;
        }
        html[data-bs-theme="dark"] #dt-maintenance-detail_wrapper .dataTables_info,
        html[data-bs-theme="dark"] #dt-maintenance-detail_wrapper .dataTables_length,
        html[data-bs-theme="dark"] #dt-maintenance-detail_wrapper .dataTables_filter {
            color: var(--bs-secondary-color);
        }

        /* Summary tiles */
        html[data-bs-theme="dark"] .summary-maintenance {
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.22), rgba(99, 102, 241, 0.12));
            border-color: rgba(129, 140, 248, 0.35);
            color: #a5b4fc;
        }
        html[data-bs-theme="dark"] .summary-qty {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.22), rgba(5, 150, 105, 0.12));
            border-color: rgba(52, 211, 153, 0.35);
            color: #6ee7b7;
        }
        html[data-bs-theme="dark"] .summary-cost {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.22), rgba(217, 119, 6, 0.12));
            border-color: rgba(251, 191, 36, 0.35);
            color: #fde68a;
        }

        /* Filter bar follows the dark surface. */
        html[data-bs-theme="dark"] .card.border-0.mb-4 .form-label,
        html[data-bs-theme="dark"] .card.border-0.mb-4 .text-muted {
            color: var(--bs-secondary-color) !important;
        }
        /* Fleet banner (inline white) → dark surface so its text stays readable. */
        html[data-bs-theme="dark"] .card[style*="background: #ffffff"] {
            background-color: var(--bs-card-bg) !important;
            border-color: var(--bs-border-color) !important;
        }
    </style>
@endpush

@section('content')
    @php
        $excelDetailUrl = route(
            $view . 'excel-maintenance-fleet-detail',
            array_filter(
                [
                    'fleetCode' => $fleet->code,
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                ],
                fn($value) => $value !== null && $value !== '',
            ),
        );

        $pdfDetailUrl = route(
            $view . 'pdf-maintenance-fleet-detail',
            array_filter(
                [
                    'fleetCode' => $fleet->code,
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                ],
                fn($value) => $value !== null && $value !== '',
            ),
        );
    @endphp

    <div class="col-sm-12">
        <div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center"
                style="border-color: #e2e8f0;">
                <div>
                    <h4 class="mb-1 fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="mdi mdi-truck-wrench text-primary fs-20"></i>
                        {{ $title }}
                    </h4>
                    <small class="text-muted">Rincian pemakaian item sparepart & servis armada <strong>{{ $fleet->plateNumber }}</strong></small>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <a href="{{ $excelDetailUrl }}" target="_blank" class="btn btn-icon btn-sm bg-success-subtle"
                        data-bs-toggle="tooltip" title="Export Excel">
                        <i class="mdi mdi-file-excel fs-14 text-success"></i>
                    </a>

                    <a href="{{ $pdfDetailUrl }}" target="_blank" class="btn btn-icon btn-sm bg-danger-subtle"
                        data-bs-toggle="tooltip" title="Export PDF">
                        <i class="mdi mdi-file-pdf-box fs-14 text-danger"></i>
                    </a>

                    <a href="{{ route($view . 'index') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1"
                        style="border-radius: 8px; font-weight: 600; padding: 7px 14px;">
                        <i class="mdi mdi-arrow-left"></i> {{ __('general.back_to_list') }}
                    </a>
                </div>
            </div>

            <div class="card-body p-4">
                {{-- Filter Bar --}}
                <div class="card border-0 mb-4"
                    style="background: var(--bs-tertiary-bg); border: 1px solid var(--bs-border-color) !important; border-radius: 12px;">
                    <div class="card-body p-3">
                        <form method="GET" action="{{ route('report.maintenance-fleet.detail', ['fleetCode' => $fleet->code]) }}"
                            class="row g-2 align-items-end mb-0">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-muted mb-1" style="font-size: 12px;"
                                    for="startDate">Dari Tanggal</label>
                                <input class="form-control form-control-sm" name="startDate" id="startDate" type="text"
                                    placeholder="Pilih Tanggal Mulai" value="{{ $startDate }}"
                                    style="border-radius: 8px; background: #fff;">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-muted mb-1" style="font-size: 12px;"
                                    for="endDate">Sampai Tanggal</label>
                                <input class="form-control form-control-sm" name="endDate" id="endDate" type="text"
                                    placeholder="Pilih Tanggal Akhir" value="{{ $endDate }}"
                                    style="border-radius: 8px; background: #fff;">
                            </div>

                            <div class="col-md-4 d-flex gap-2">
                                <button class="btn btn-sm btn-primary w-100" style="border-radius: 8px; font-weight: 600;"
                                    type="submit">
                                    <i class="mdi mdi-filter me-1"></i> Filter
                                </button>
                                <a class="btn btn-sm btn-outline-secondary" style="border-radius: 8px;" title="Reset Filter"
                                    href="{{ route('report.maintenance-fleet.detail', ['fleetCode' => $fleet->code]) }}">
                                    <i class="mdi mdi-refresh"></i>
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Fleet Profile Banner --}}
                <div class="card border-0 mb-4"
                    style="background: #ffffff; border: 1px solid #e2e8f0 !important; border-radius: 12px;">
                    <div class="card-body p-3">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-badge-fleet">
                                    <i class="mdi mdi-truck fs-22"></i>
                                </div>
                                <div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fs-16 fw-bold text-dark font-monospace">{{ $fleet->plateNumber }}</span>
                                        <span class="badge {{ strtolower($fleet->company?->type ?? '') === 'internal' ? 'bg-primary-subtle text-primary' : 'bg-warning-subtle text-warning' }} px-2 py-1"
                                            style="border-radius: 6px; font-weight: 600; font-size: 11px;">
                                            {{ $fleet->company?->type ?? 'Internal' }}
                                        </span>
                                    </div>
                                    <div class="text-muted small mt-1">
                                        <i class="mdi mdi-office-building me-1"></i>{{ $fleet->company?->name ?? 'Perusahaan tidak diketahui' }}
                                        <span class="mx-2">•</span>
                                        <i class="mdi mdi-identifier me-1"></i>Kode Armada: <span class="font-monospace">{{ $fleet->code }}</span>
                                    </div>
                                </div>
                            </div>

                            @if ($startDate || $endDate)
                                <div class="d-flex align-items-center gap-2 bg-light px-3 py-2 rounded-3 border" style="font-size: 12.5px;">
                                    <i class="mdi mdi-calendar-range text-primary fs-16"></i>
                                    <span>Filter Periode: <strong>{{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d/m/Y') : 'Awal' }}</strong> s/d <strong>{{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d/m/Y') : 'Sekarang' }}</strong></span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Metric KPI Summary Cards --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="summary-card summary-maintenance">
                            <i class="mdi mdi-wrench-clock-outline float-end fs-24 opacity-50"></i>
                            <h5>Total Maintenance</h5>
                            <h3>{{ number_format($totalMaintenance, 0, ',', '.') }} <span class="fs-14 fw-normal opacity-75">Transaksi</span></h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="summary-card summary-qty">
                            <i class="mdi mdi-package-variant-closed float-end fs-24 opacity-50"></i>
                            <h5>Total Qty Item</h5>
                            <h3>{{ number_format($totalQty, 1, ',', '.') }} <span class="fs-14 fw-normal opacity-75">Item</span></h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="summary-card summary-cost">
                            <i class="mdi mdi-cash-multiple float-end fs-24 opacity-50"></i>
                            <h5>Total Biaya</h5>
                            <h3>Rp {{ number_format($totalCost, 0, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>

                {{-- Table --}}
                <div class="table-responsive custom-scrollbar">
                    <table class="table align-middle w-100 mb-0" id="dt-maintenance-detail">
                        <thead>
                            <tr>
                                <th style="width: 4%;" class="text-center">No</th>
                                <th style="width: 14%;">Kode MNT</th>
                                <th style="width: 11%;" class="text-center">Tanggal & Jam</th>
                                <th style="width: 10%;">Kode Item</th>
                                <th style="width: 19%;">Nama Item / Part</th>
                                <th style="width: 14%;">Supplier</th>
                                <th style="width: 8%;" class="text-end">Qty</th>
                                <th style="width: 10%;" class="text-end">Harga Satuan</th>
                                <th style="width: 10%;" class="text-end">Subtotal</th>
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
    <script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/custom-flatpickr.js') }}"></script>

    <script>
        let maintenanceStartDatePicker;
        let maintenanceEndDatePicker;

        $(document).ready(function() {
            maintenanceStartDatePicker = flatpickr('#startDate', {
                dateFormat: 'Y-m-d',
                allowInput: true,
                onChange: function(selectedDates, dateStr) {
                    if (maintenanceEndDatePicker) {
                        maintenanceEndDatePicker.set('minDate', dateStr || null);
                    }
                }
            });

            maintenanceEndDatePicker = flatpickr('#endDate', {
                dateFormat: 'Y-m-d',
                allowInput: true,
                onChange: function(selectedDates, dateStr) {
                    if (maintenanceStartDatePicker) {
                        maintenanceStartDatePicker.set('maxDate', dateStr || null);
                    }
                }
            });

            if ($('#startDate').val() && maintenanceEndDatePicker) {
                maintenanceEndDatePicker.set('minDate', $('#startDate').val());
            }

            if ($('#endDate').val() && maintenanceStartDatePicker) {
                maintenanceStartDatePicker.set('maxDate', $('#endDate').val());
            }

            $('#dt-maintenance-detail').DataTable({
                processing: true,
                serverSide: true,
                destroy: true,
                pageLength: 25,
                ajax: {
                    url: "{{ route('dt.maintenance-fleet-detail', ['fleetCode' => $fleet->code]) }}",
                    data: function(d) {
                        d.startDate = $('input[name="startDate"]').val();
                        d.endDate = $('input[name="endDate"]').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', className: 'text-center align-middle' },
                    { data: 'code', className: 'align-middle font-monospace fw-semibold text-dark' },
                    { data: 'maintenanceDate', className: 'align-middle text-center' },
                    { data: 'itemCode', className: 'align-middle font-monospace text-muted' },
                    { data: 'itemName', className: 'align-middle fw-medium' },
                    { data: 'supplierName', className: 'align-middle' },
                    { data: 'qty', className: 'text-end align-middle font-monospace' },
                    { data: 'price', className: 'text-end align-middle font-monospace' },
                    { data: 'total', className: 'text-end align-middle font-monospace fw-bold text-dark' }
                ],
                columnDefs: [
                    { searchable: false, targets: [0, 2, 6, 7, 8] },
                    { orderable: false, targets: [0] }
                ],
                order: [[2, 'desc']],
                language: {
                    search: 'Cari (Kode / Item):',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ pemakaian item',
                    infoEmpty: 'Tidak ada data pemakaian item',
                    zeroRecords: 'Data pemakaian item tidak ditemukan',
                    processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Memuat data...'
                }
            });
        });
    </script>
@endpush
