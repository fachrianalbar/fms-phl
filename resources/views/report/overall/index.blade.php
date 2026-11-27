@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Report',
    'secondSegment' => $title,
])

@push('style')
    @include('report.partials.maintenance-report-style')
    <style>
        #dt-overall thead tr.group-header th {
            text-align: center;
            font-size: 11.5px;
            letter-spacing: 0.6px;
            border-right: 1px solid #cbd5e1;
        }

        #dt-overall thead th.grp-order { background-color: #eef2ff; color: #3730a3; }
        #dt-overall thead th.grp-factory { background-color: #ecfdf5; color: #065f46; }
        #dt-overall thead th.grp-commission { background-color: #fefce8; color: #854d0e; }
        #dt-overall thead th.grp-vendor { background-color: #fff7ed; color: #9a3412; }
        #dt-overall thead th.grp-summary { background-color: #f1f5f9; color: #0f172a; }

        html[data-bs-theme="dark"] #dt-overall thead th.grp-order { background-color: rgba(79, 70, 229, 0.25) !important; color: #c7d2fe !important; }
        html[data-bs-theme="dark"] #dt-overall thead th.grp-factory { background-color: rgba(16, 185, 129, 0.22) !important; color: #a7f3d0 !important; }
        html[data-bs-theme="dark"] #dt-overall thead th.grp-commission { background-color: rgba(245, 158, 11, 0.22) !important; color: #fde68a !important; }
        html[data-bs-theme="dark"] #dt-overall thead th.grp-vendor { background-color: rgba(249, 115, 22, 0.22) !important; color: #fed7aa !important; }
        html[data-bs-theme="dark"] #dt-overall thead th.grp-summary { background-color: rgba(148, 163, 184, 0.2) !important; color: #f8fafc !important; }
        html[data-bs-theme="dark"] #dt-overall thead tr.group-header th { border-right-color: var(--bs-border-color) !important; }
    </style>
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center"
                style="border-color: #e2e8f0;">
                <div>
                    <h4 class="mb-1 fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="mdi mdi-table-large text-primary fs-20"></i>
                        {{ $title }}
                    </h4>
                    <small class="text-muted">Rekap end-to-end per order: Info Order, Piutang Pabrik, Komisi, Nota Pembayaran Vendor, dan Untung</small>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('report.overall.excel') }}" target="_blank" id="export-excel"
                        class="btn btn-icon btn-sm bg-success-subtle" data-bs-toggle="tooltip" title="Export Excel">
                        <i class="mdi mdi-file-excel fs-14 text-success"></i>
                    </a>
                    <a href="{{ route('report.overall.pdf') }}" target="_blank" id="export-pdf"
                        class="btn btn-icon btn-sm bg-danger-subtle" data-bs-toggle="tooltip" title="Export PDF">
                        <i class="mdi mdi-file-pdf-box fs-14 text-danger"></i>
                    </a>
                </div>
            </div>

            <div class="card-body p-4">
                @include('partials.alert')

                <div class="filter-card">
                    <button type="button" class="filter-card-header" data-bs-toggle="collapse"
                        data-bs-target="#overallReportFilterCollapse" aria-expanded="false"
                        aria-controls="overallReportFilterCollapse">
                        <span class="filter-card-heading">
                            <i class="mdi mdi-filter-variant"></i>
                            <strong>Filter Data</strong>
                            <small>Periode muat, pabrik, pemilik kendaraan, dan status pelunasan</small>
                        </span>
                        <i class="mdi mdi-chevron-down filter-card-chevron"></i>
                    </button>

                    <div class="collapse filter-collapse" id="overallReportFilterCollapse">
                        <div class="filter-collapse-body">
                            <div id="filterForm">
                                <div class="row g-3">
                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="startDate">Tanggal Muat Dari</label>
                                        <input class="form-control filter-control" id="startDate" type="text"
                                            placeholder="Pilih tanggal mulai">
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="endDate">Tanggal Muat Sampai</label>
                                        <input class="form-control filter-control" id="endDate" type="text"
                                            placeholder="Pilih tanggal akhir">
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="customerCode">Pabrik</label>
                                        <select class="form-select select2-filter" id="customerCode" data-placeholder="Semua Pabrik">
                                            <option value="">Semua Pabrik</option>
                                            @foreach ($customers as $customer)
                                                <option value="{{ $customer->code }}">{{ $customer->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="fleetCompanyCode">Pemilik Kendaraan</label>
                                        <select class="form-select select2-filter" id="fleetCompanyCode" data-placeholder="Semua Pemilik">
                                            <option value="">Semua Pemilik</option>
                                            @foreach ($fleetCompanies as $company)
                                                <option value="{{ $company->code }}">
                                                    {{ $company->name }}{{ $company->type ? ' (' . $company->type . ')' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="factoryStatus">Status Lunas Pabrik</label>
                                        <select class="form-select select2-filter" id="factoryStatus" data-placeholder="Semua Status">
                                            <option value="">Semua Status</option>
                                            <option value="paid">Lunas</option>
                                            <option value="unpaid">Belum Lunas</option>
                                        </select>
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="vendorStatus">Status Lunas Vendor</label>
                                        <select class="form-select select2-filter" id="vendorStatus" data-placeholder="Semua Status">
                                            <option value="">Semua Status</option>
                                            <option value="paid">Lunas</option>
                                            <option value="unpaid">Belum Lunas</option>
                                        </select>
                                    </div>

                                    <div class="col-xl-6 col-md-12 d-flex align-items-end justify-content-md-end gap-2">
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

                <div class="table-responsive custom-scrollbar">
                    <table class="table align-middle w-100 mb-0 maintenance-report-table" id="dt-overall">
                        <thead>
                            <tr class="group-header">
                                <th rowspan="2" class="text-center">No</th>
                                <th colspan="8" class="grp-order">Info Order</th>
                                <th colspan="10" class="grp-factory">Pabrik (Piutang)</th>
                                <th colspan="4" class="grp-commission">Komisi</th>
                                <th colspan="10" class="grp-vendor">Customer / Nota Pembayaran Vendor</th>
                                <th colspan="2" class="grp-summary">Ringkasan</th>
                            </tr>
                            <tr>
                                {{-- Info Order (8) --}}
                                <th class="text-center">Tanggal Muat</th>
                                <th>No. Kendaraan</th>
                                <th>Pabrik</th>
                                <th>Jenis Barang</th>
                                <th>Dari</th>
                                <th>Tujuan</th>
                                <th>Pemilik Kendaraan</th>
                                <th>Nama Ekspedisi di Pabrik</th>
                                {{-- Pabrik (10) --}}
                                <th class="text-end">Tonase</th>
                                <th class="text-end">Ongkos</th>
                                <th class="text-end">Jumlah</th>
                                <th class="text-end">Pajak</th>
                                <th class="text-end">Klaim</th>
                                <th class="text-end">Total Jumlah</th>
                                <th>No. Invoice</th>
                                <th class="text-end">DP</th>
                                <th class="text-center">Tgl Bayar</th>
                                <th class="text-center">Lunas/Belum</th>
                                {{-- Komisi (4) --}}
                                <th>Nama</th>
                                <th class="text-end">Komisi</th>
                                <th class="text-center">Tgl Bayar</th>
                                <th class="text-center">Lunas/Belum</th>
                                {{-- Vendor (10) --}}
                                <th class="text-end">Tonase Barang</th>
                                <th class="text-end">Ongkos Barang</th>
                                <th class="text-end">Jumlah Harga</th>
                                <th class="text-end">Pajak</th>
                                <th class="text-end">Total Jumlah</th>
                                <th class="text-center">Tgl Terima SJ</th>
                                <th>No. Nota Pembayaran</th>
                                <th class="text-end">DP</th>
                                <th class="text-center">Tgl Bayar</th>
                                <th class="text-center">Lunas/Belum</th>
                                {{-- Ringkasan (2) --}}
                                <th class="text-end">Untung</th>
                                <th>Keterangan</th>
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
            const filter = initMaintenanceReportFilter([
                'startDate',
                'endDate',
                'customerCode',
                'fleetCompanyCode',
                'factoryStatus',
                'vendorStatus'
            ]);

            function syncExportUrls() {
                const qs = buildMaintenanceReportQuery(filter.getFilters());
                $('#export-excel').attr('href', "{{ route('report.overall.excel') }}" + qs);
                $('#export-pdf').attr('href', "{{ route('report.overall.pdf') }}" + qs);
            }

            const table = $('#dt-overall').DataTable({
                processing: true,
                serverSide: true,
                scrollX: true,
                pageLength: 25,
                ordering: false,
                ajax: {
                    url: "{{ route('dt.report-overall') }}",
                    data: function(d) {
                        Object.assign(d, filter.getFilters());
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', className: 'text-center', searchable: false },
                    { data: 'order_date', className: 'text-center font-monospace' },
                    { data: 'plate_number', className: 'font-monospace fw-semibold text-dark' },
                    { data: 'customer_name', className: 'fw-medium' },
                    { data: 'material_name' },
                    { data: 'origin' },
                    { data: 'destination' },
                    { data: 'fleet_owner' },
                    { data: 'expedition_name' },
                    { data: 'factory_tonase', className: 'text-end font-monospace' },
                    { data: 'factory_oa', className: 'text-end font-monospace' },
                    { data: 'factory_amount', className: 'text-end font-monospace' },
                    { data: 'factory_tax', className: 'text-end font-monospace' },
                    { data: 'factory_claim', className: 'text-end font-monospace' },
                    { data: 'factory_total', className: 'text-end font-monospace fw-bold text-dark' },
                    { data: 'factory_invoice_no', className: 'font-monospace' },
                    { data: 'factory_dp', className: 'text-end font-monospace' },
                    { data: 'factory_payment_date', className: 'text-center font-monospace' },
                    { data: 'factory_status', className: 'text-center' },
                    { data: 'commission_name' },
                    { data: 'commission_amount', className: 'text-end font-monospace' },
                    { data: 'commission_payment_date', className: 'text-center font-monospace' },
                    { data: 'commission_status', className: 'text-center' },
                    { data: 'vendor_tonase', className: 'text-end font-monospace' },
                    { data: 'vendor_oa', className: 'text-end font-monospace' },
                    { data: 'vendor_amount', className: 'text-end font-monospace' },
                    { data: 'vendor_tax', className: 'text-end font-monospace' },
                    { data: 'vendor_total', className: 'text-end font-monospace fw-bold text-dark' },
                    { data: 'vendor_return_date', className: 'text-center font-monospace' },
                    { data: 'vendor_nota_no', className: 'font-monospace' },
                    { data: 'vendor_dp', className: 'text-end font-monospace' },
                    { data: 'vendor_payment_date', className: 'text-center font-monospace' },
                    { data: 'vendor_status', className: 'text-center' },
                    { data: 'profit', className: 'text-end font-monospace' },
                    { data: 'notes' }
                ],
                language: maintenanceReportLanguage('order')
            });

            function reloadWithFilters() {
                syncExportUrls();
                table.ajax.reload(null, true);
            }

            $('#btnFilter').on('click', reloadWithFilters);
            $('#btnResetFilter').on('click', function() {
                filter.reset();
                reloadWithFilters();
            });

            syncExportUrls();
        });
    </script>
@endpush
