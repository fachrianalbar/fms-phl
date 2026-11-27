@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Report',
    'secondSegment' => $title,
])

@push('style')
    @include('report.partials.maintenance-report-style')
    <style>
        #dt-vendor-tax tbody td.col-address {
            white-space: normal;
            min-width: 180px;
            max-width: 280px;
        }
    </style>
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center"
                style="border-color: #e2e8f0;">
                <div>
                    <h4 class="mb-1 fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="mdi mdi-file-percent-outline text-primary fs-20"></i>
                        {{ $title }}
                    </h4>
                    <small class="text-muted">Rekap pajak nota pembayaran vendor beserta input No Faktur Pajak &amp; Bukti Potong</small>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('report.vendor-tax.excel') }}" target="_blank" id="export-excel"
                        class="btn btn-icon btn-sm bg-success-subtle" data-bs-toggle="tooltip" title="Export Excel">
                        <i class="mdi mdi-file-excel fs-14 text-success"></i>
                    </a>
                    <a href="{{ route('report.vendor-tax.pdf') }}" target="_blank" id="export-pdf"
                        class="btn btn-icon btn-sm bg-danger-subtle" data-bs-toggle="tooltip" title="Export PDF">
                        <i class="mdi mdi-file-pdf-box fs-14 text-danger"></i>
                    </a>
                </div>
            </div>

            <div class="card-body p-4">
                @include('partials.alert')
                <div id="vendorTaxPageAlert" class="alert alert-success alert-dismissible fade d-none" role="alert">
                    <span id="vendorTaxPageAlertText"></span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>

                <div class="filter-card">
                    <button type="button" class="filter-card-header" data-bs-toggle="collapse"
                        data-bs-target="#vendorTaxFilterCollapse" aria-expanded="false"
                        aria-controls="vendorTaxFilterCollapse">
                        <span class="filter-card-heading">
                            <i class="mdi mdi-filter-variant"></i>
                            <strong>Filter Data</strong>
                            <small>Periode nota pembayaran, vendor, dan status input pajak</small>
                        </span>
                        <i class="mdi mdi-chevron-down filter-card-chevron"></i>
                    </button>

                    <div class="collapse filter-collapse" id="vendorTaxFilterCollapse">
                        <div class="filter-collapse-body">
                            <div id="filterForm">
                                <div class="row g-3">
                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="fleetCompanyCode">Vendor</label>
                                        <select class="form-select select2-filter" id="fleetCompanyCode" data-placeholder="Semua Vendor">
                                            <option value="">Semua Vendor</option>
                                            @foreach ($vendors as $vendor)
                                                <option value="{{ $vendor->code }}">
                                                    {{ $vendor->name }}{{ $vendor->type ? ' (' . $vendor->type . ')' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="taxStatus">Status Input Pajak</label>
                                        <select class="form-select select2-filter" id="taxStatus" data-placeholder="Semua Status">
                                            <option value="">Semua Status</option>
                                            <option value="filled">Sudah Input Pajak</option>
                                            <option value="empty">Belum Input Pajak</option>
                                        </select>
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="startDate">Tgl Nota Dari</label>
                                        <input class="form-control filter-control" id="startDate" type="text"
                                            placeholder="Pilih tanggal mulai">
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="endDate">Tgl Nota Sampai</label>
                                        <input class="form-control filter-control" id="endDate" type="text"
                                            placeholder="Pilih tanggal akhir">
                                    </div>

                                    <div class="col-12 d-flex align-items-end justify-content-md-end gap-2">
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
                    <table class="table align-middle w-100 mb-0 maintenance-report-table" id="dt-vendor-tax">
                        <thead>
                            <tr>
                                <th class="text-center">Aksi</th>
                                <th class="text-center">No</th>
                                <th class="text-center">Tanggal Nota Pembayaran</th>
                                <th>NPWP</th>
                                <th>Nama Vendor</th>
                                <th class="text-center">Tgl Pesanan</th>
                                <th>Jenis Muatan</th>
                                <th>Alamat</th>
                                <th>No Nota Pembayaran</th>
                                <th class="text-end">Tonase</th>
                                <th class="text-end">OA</th>
                                <th class="text-end">Nominal Invoice/DPP</th>
                                <th>No Faktur Pajak</th>
                                <th class="text-center">Tgl Bayar Nota Pembayaran</th>
                                <th>No Bukti Potong</th>
                                <th class="text-end">Nominal Bukti Potong</th>
                                <th class="text-center">Tgl Bukti Potong</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('report.vendor-tax.partials.tax-input-modal')
@endsection

@push('script')
    @include('report.partials.maintenance-report-script')
    <script>
        $(document).ready(function() {
            const filter = initMaintenanceReportFilter(['fleetCompanyCode', 'taxStatus', 'startDate', 'endDate']);
            const updateUrlTemplate = "{{ route('report.vendor-tax.update-tax', ['id' => '__ID__']) }}";
            const modalEl = document.getElementById('vendorTaxModal');
            const taxModal = new bootstrap.Modal(modalEl);

            const withholdingDatePicker = flatpickr('#withholding_tax_date', {
                dateFormat: 'Y-m-d',
                allowInput: true
            });

            function syncExportUrls() {
                const qs = buildMaintenanceReportQuery(filter.getFilters());
                $('#export-excel').attr('href', "{{ route('report.vendor-tax.excel') }}" + qs);
                $('#export-pdf').attr('href', "{{ route('report.vendor-tax.pdf') }}" + qs);
            }

            const table = $('#dt-vendor-tax').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                ajax: {
                    url: "{{ route('dt.report-vendor-tax') }}",
                    data: function(d) {
                        Object.assign(d, filter.getFilters());
                    }
                },
                columns: [
                    { data: 'action', className: 'text-center', orderable: false, searchable: false },
                    { data: 'DT_RowIndex', className: 'text-center', orderable: false, searchable: false },
                    { data: 'notaDate', className: 'text-center font-monospace' },
                    { data: 'npwp', className: 'font-monospace' },
                    { data: 'vendorName', className: 'fw-semibold text-dark' },
                    { data: 'orderDate', className: 'text-center font-monospace' },
                    { data: 'materialName' },
                    { data: 'vendorAddress', className: 'col-address' },
                    { data: 'notaNumber', className: 'font-monospace fw-semibold text-dark' },
                    { data: 'tonase', className: 'text-end font-monospace' },
                    { data: 'oa', className: 'text-end font-monospace' },
                    { data: 'dpp', className: 'text-end font-monospace fw-bold text-dark' },
                    { data: 'tax_invoice_number', className: 'font-monospace' },
                    { data: 'lastPaymentDate', className: 'text-center font-monospace' },
                    { data: 'withholding_tax_number', className: 'font-monospace' },
                    { data: 'withholding_tax_amount', className: 'text-end font-monospace fw-semibold text-primary' },
                    { data: 'withholding_tax_date', className: 'text-center font-monospace' }
                ],
                order: [[2, 'desc']],
                language: maintenanceReportLanguage('pajak vendor')
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

            $('#dt-vendor-tax').on('click', '.btn-input-tax', function() {
                const btn = $(this);
                $('#vendorTaxAlert').addClass('d-none').text('');
                $('#vendorTaxPaymentId').val(btn.data('id'));
                $('#vendorTaxModalSubtitle').text(btn.data('nota') + ' • ' + btn.data('vendor') + ' (' + btn.data('dpp') + ')');
                $('#tax_invoice_number').val(btn.data('tax-invoice') || '');
                $('#withholding_tax_number').val(btn.data('withholding-number') || '');
                $('#withholding_tax_amount').val(btn.data('withholding-amount') || '');

                const dt = btn.data('withholding-date') || '';
                if (dt) {
                    withholdingDatePicker.setDate(dt, true, 'Y-m-d');
                } else {
                    withholdingDatePicker.clear();
                }

                taxModal.show();
            });

            $('#vendorTaxForm').on('submit', function(e) {
                e.preventDefault();
                const id = $('#vendorTaxPaymentId').val();
                const btn = $('#btnSaveVendorTax');
                const alertBox = $('#vendorTaxAlert');

                alertBox.addClass('d-none').text('');
                btn.prop('disabled', true);

                $.ajax({
                    url: updateUrlTemplate.replace('__ID__', id),
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        taxModal.hide();
                        $('#vendorTaxPageAlertText').text(res.message || 'Data pajak vendor berhasil disimpan.');
                        $('#vendorTaxPageAlert').removeClass('d-none').addClass('show');
                        table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON?.message || 'Gagal menyimpan data pajak vendor.';
                        alertBox.removeClass('d-none').text(msg);
                    },
                    complete: function() {
                        btn.prop('disabled', false);
                    }
                });
            });

            syncExportUrls();
        });
    </script>
@endpush
