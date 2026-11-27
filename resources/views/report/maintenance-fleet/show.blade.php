@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Report',
    'secondSegment' => $title,
])

@push('style')
    @include('report.partials.maintenance-report-style')
@endpush

@section('content')
    @php
        $exportQuery = array_filter(
            ['fleetCode' => $fleet->code, 'supplierCode' => $supplierCode, 'startDate' => $startDate, 'endDate' => $endDate],
            fn($value) => $value !== null && $value !== '',
        );
        $filterActive = $supplierCode || $startDate || $endDate;
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
                    <small class="text-muted">Rincian pemakaian item sparepart &amp; servis armada <strong>{{ $fleet->plateNumber }}</strong> beserta status pembayarannya</small>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route($view . 'excel-maintenance-fleet-detail', $exportQuery) }}" target="_blank"
                        class="btn btn-icon btn-sm bg-success-subtle" data-bs-toggle="tooltip" title="Export Excel">
                        <i class="mdi mdi-file-excel fs-14 text-success"></i>
                    </a>
                    <a href="{{ route($view . 'pdf-maintenance-fleet-detail', $exportQuery) }}" target="_blank"
                        class="btn btn-icon btn-sm bg-danger-subtle" data-bs-toggle="tooltip" title="Export PDF">
                        <i class="mdi mdi-file-pdf-box fs-14 text-danger"></i>
                    </a>
                    <a href="{{ route($view . 'index') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1"
                        style="border-radius: 8px; font-weight: 600; padding: 7px 14px;">
                        <i class="mdi mdi-arrow-left"></i> {{ __('general.back_to_list') }}
                    </a>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="filter-card">
                    <button type="button" class="filter-card-header {{ $filterActive ? '' : 'collapsed' }}" data-bs-toggle="collapse"
                        data-bs-target="#maintenanceFleetDetailFilterCollapse" aria-expanded="{{ $filterActive ? 'true' : 'false' }}"
                        aria-controls="maintenanceFleetDetailFilterCollapse">
                        <span class="filter-card-heading">
                            <i class="mdi mdi-filter-variant"></i>
                            <strong>Filter Data</strong>
                            <small>Supplier dan periode perawatan</small>
                        </span>
                        <i class="mdi mdi-chevron-down filter-card-chevron"></i>
                    </button>

                    <div class="collapse filter-collapse {{ $filterActive ? 'show' : '' }}" id="maintenanceFleetDetailFilterCollapse">
                        <div class="filter-collapse-body">
                            <div id="filterForm">
                                <div class="row g-3">
                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="supplierCode">Supplier</label>
                                        <select class="form-select select2-filter" id="supplierCode" data-placeholder="Semua Supplier">
                                            <option value="">Semua Supplier</option>
                                            @foreach ($suppliers as $supplier)
                                                <option value="{{ $supplier->code }}" @selected($supplierCode === $supplier->code)>{{ $supplier->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="startDate">Dari Tanggal</label>
                                        <input class="form-control filter-control" id="startDate" type="text"
                                            placeholder="Pilih tanggal mulai" value="{{ $startDate }}">
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="endDate">Sampai Tanggal</label>
                                        <input class="form-control filter-control" id="endDate" type="text"
                                            placeholder="Pilih tanggal akhir" value="{{ $endDate }}">
                                    </div>

                                    <div class="col-xl-3 col-md-6 d-flex align-items-end justify-content-md-end gap-2">
                                        <button class="btn btn-filter-primary flex-grow-1 flex-md-grow-0" type="button" id="btnFilter">
                                            <i class="mdi mdi-filter-outline"></i> Terapkan Filter
                                        </button>
                                        <button class="btn btn-filter-reset" type="button" id="btnResetFilter"
                                            data-bs-toggle="tooltip" title="Reset Filter">
                                            <i class="mdi mdi-refresh"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 mb-4 entity-banner">
                    <div class="card-body p-3">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-badge-entity">
                                    <i class="mdi mdi-truck fs-22"></i>
                                </div>
                                <div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fs-16 fw-bold text-dark font-monospace">{{ $fleet->plateNumber }}</span>
                                        <span class="badge {{ strtolower($fleet->company?->type ?? 'internal') === 'internal' ? 'bg-primary-subtle text-primary' : 'bg-warning-subtle text-warning' }} px-2 py-1"
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

                @include('report.partials.maintenance-report-summary', ['summary' => $summary])

                <div class="table-responsive custom-scrollbar">
                    <table class="table align-middle w-100 mb-0 maintenance-report-table" id="dt-maintenance-detail">
                        <thead>
                            <tr>
                                <th class="text-center">No</th>
                                <th>Kode MNT</th>
                                <th class="text-center">Tgl &amp; Jam</th>
                                <th>Kode Item</th>
                                <th>Nama Item</th>
                                <th>Supplier</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Harga</th>
                                <th class="text-end">Total</th>
                                <th class="text-center">Tgl Jth Tempo</th>
                                <th class="text-center">Status</th>
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
    @include('report.partials.maintenance-report-script')
    <script>
        $(document).ready(function() {
            const filter = initMaintenanceReportFilter(['supplierCode', 'startDate', 'endDate']);
            const detailUrl = "{{ route('report.maintenance-fleet.detail', ['fleetCode' => $fleet->code]) }}";

            $('#dt-maintenance-detail').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                ajax: {
                    url: "{{ route('dt.maintenance-fleet-detail', ['fleetCode' => $fleet->code]) }}",
                    data: function(d) {
                        Object.assign(d, {!! json_encode(['supplierCode' => $supplierCode, 'startDate' => $startDate, 'endDate' => $endDate]) !!});
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', className: 'text-center', orderable: false, searchable: false },
                    { data: 'code', className: 'font-monospace fw-semibold text-dark' },
                    { data: 'maintenanceDate', className: 'text-center' },
                    { data: 'itemCode', className: 'font-monospace text-muted' },
                    { data: 'itemName', className: 'fw-medium' },
                    { data: 'supplierName' },
                    { data: 'qty', className: 'text-end font-monospace' },
                    { data: 'price', className: 'text-end font-monospace' },
                    { data: 'total', className: 'text-end font-monospace fw-bold text-dark' },
                    { data: 'dueDate', className: 'text-center' },
                    { data: 'paymentStatus', className: 'text-center' }
                ],
                order: [[2, 'desc']],
                language: maintenanceReportLanguage('pemakaian item')
            });

            $('#btnFilter').on('click', function() {
                window.location.href = detailUrl + buildMaintenanceReportQuery(filter.getFilters());
            });

            $('#btnResetFilter').on('click', function() {
                filter.reset();
                window.location.href = detailUrl;
            });
        });
    </script>
@endpush
