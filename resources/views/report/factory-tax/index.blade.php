@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Report',
    'secondSegment' => $title,
])

@push('style')
    @include('report.partials.maintenance-report-style')
    <style>
        #dt-factory-tax tbody td.col-address {
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
                        <i class="mdi mdi-file-chart-outline text-primary fs-20"></i>
                        {{ $title }}
                    </h4>
                    <small class="text-muted">Rekap pajak invoice pabrik (DPP Lain = DPP × 11/12, PPN = DPP Lain × 12%) beserta input Faktur Pajak &amp; Bukti Potong</small>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('report.factory-tax.excel') }}" target="_blank" id="export-excel"
                        class="btn btn-icon btn-sm bg-success-subtle" data-bs-toggle="tooltip" title="Export Excel">
                        <i class="mdi mdi-file-excel fs-14 text-success"></i>
                    </a>
                    <a href="{{ route('report.factory-tax.pdf') }}" target="_blank" id="export-pdf"
                        class="btn btn-icon btn-sm bg-danger-subtle" data-bs-toggle="tooltip" title="Export PDF">
                        <i class="mdi mdi-file-pdf-box fs-14 text-danger"></i>
                    </a>
                </div>
            </div>

            <div class="card-body p-4">
                @include('partials.alert')
                <div id="factoryTaxPageAlert" class="alert alert-success alert-dismissible fade d-none" role="alert">
                    <span id="factoryTaxPageAlertText"></span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>

                <div class="filter-card">
                    <button type="button" class="filter-card-header" data-bs-toggle="collapse"
                        data-bs-target="#factoryTaxFilterCollapse" aria-expanded="false"
                        aria-controls="factoryTaxFilterCollapse">
                        <span class="filter-card-heading">
                            <i class="mdi mdi-filter-variant"></i>
                            <strong>Filter Data</strong>
                            <small>Periode tanggal invoice, pabrik, dan status input pajak</small>
                        </span>
                        <i class="mdi mdi-chevron-down filter-card-chevron"></i>
                    </button>

                    <div class="collapse filter-collapse" id="factoryTaxFilterCollapse">
                        <div class="filter-collapse-body">
                            <div id="filterForm">
                                <div class="row g-3">
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
                                        <label class="filter-label" for="taxStatus">Status Input Pajak</label>
                                        <select class="form-select select2-filter" id="taxStatus" data-placeholder="Semua Status">
                                            <option value="">Semua Status</option>
                                            <option value="filled">Sudah Input Pajak</option>
                                            <option value="empty">Belum Input Pajak</option>
                                        </select>
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="startDate">Tgl Invoice Dari</label>
                                        <input class="form-control filter-control" id="startDate" type="text"
                                            placeholder="Pilih tanggal mulai">
                                    </div>

                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="endDate">Tgl Invoice Sampai</label>
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
                    <table class="table align-middle w-100 mb-0 maintenance-report-table" id="dt-factory-tax">
                        <thead>
                            <tr>
                                <th class="text-center">Aksi</th>
                                <th class="text-center">No</th>
                                <th class="text-center">Tanggal Cetak Invoice</th>
                                <th>NPWP</th>
                                <th>Nama Pabrik</th>
                                <th class="text-center">Tgl Pesanan</th>
                                <th>Jenis Muatan</th>
                                <th>Alamat</th>
                                <th>No Invoice</th>
                                <th class="text-end">Tonase</th>
                                <th class="text-end">OA</th>
                                <th class="text-end">Nominal Invoice/DPP</th>
                                <th class="text-end">DPP Lain</th>
                                <th class="text-end">PPN</th>
                                <th>No Faktur Pajak</th>
                                <th class="text-center">Tgl Bayar Invoice</th>
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

    @include('report.factory-tax.partials.tax-input-modal')
@endsection

@push('script')
    @include('report.partials.maintenance-report-script')
    <script>
        $(document).ready(function() {
            const filter = initMaintenanceReportFilter(['customerCode', 'taxStatus', 'startDate', 'endDate']);
            const updateUrlTemplate = "{{ route('report.factory-tax.update-tax', ['id' => '__ID__']) }}";
            const modalEl = document.getElementById('factoryTaxModal');
            const taxModal = new bootstrap.Modal(modalEl);

            const withholdingDatePicker = flatpickr('#withholding_tax_date', {
                dateFormat: 'Y-m-d',
                allowInput: true
            });

            function syncExportUrls() {
                const qs = buildMaintenanceReportQuery(filter.getFilters());
                $('#export-excel').attr('href', "{{ route('report.factory-tax.excel') }}" + qs);
                $('#export-pdf').attr('href', "{{ route('report.factory-tax.pdf') }}" + qs);
            }

            const table = $('#dt-factory-tax').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                ajax: {
                    url: "{{ route('dt.report-factory-tax') }}",
                    data: function(d) {
                        Object.assign(d, filter.getFilters());
                    }
                },
                columns: [
                    { data: 'action', className: 'text-center', orderable: false, searchable: false },
                    { data: 'DT_RowIndex', className: 'text-center', orderable: false, searchable: false },
                    { data: 'invoiceDate', className: 'text-center font-monospace' },
                    { data: 'npwp', className: 'font-monospace' },
                    { data: 'customerName', className: 'fw-semibold text-dark' },
                    { data: 'orderDateRange', className: 'text-center font-monospace' },
                    { data: 'materials' },
                    { data: 'customerAddress', className: 'col-address' },
                    { data: 'invoiceNumber', className: 'font-monospace fw-semibold text-dark' },
                    { data: 'tonase', className: 'text-end font-monospace' },
                    { data: 'oa', className: 'text-end font-monospace' },
                    { data: 'dpp', className: 'text-end font-monospace fw-bold text-dark' },
                    { data: 'dppLain', className: 'text-end font-monospace' },
                    { data: 'ppn', className: 'text-end font-monospace fw-semibold text-success' },
                    { data: 'tax_invoice_number', className: 'font-monospace' },
                    { data: 'lastPaymentDate', className: 'text-center font-monospace' },
                    { data: 'withholding_tax_number', className: 'font-monospace' },
                    { data: 'withholding_tax_amount', className: 'text-end font-monospace fw-semibold text-primary' },
                    { data: 'withholding_tax_date', className: 'text-center font-monospace' }
                ],
                order: [[2, 'desc']],
                language: maintenanceReportLanguage('pajak pabrik')
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

            $('#dt-factory-tax').on('click', '.btn-input-tax', function() {
                const btn = $(this);
                $('#factoryTaxAlert').addClass('d-none').text('');
                $('#factoryTaxInvoiceId').val(btn.data('id'));
                $('#factoryTaxModalSubtitle').text(btn.data('invoice') + ' • ' + btn.data('customer') + ' (' + btn.data('dpp') + ')');
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

            $('#factoryTaxForm').on('submit', function(e) {
                e.preventDefault();
                const id = $('#factoryTaxInvoiceId').val();
                const btn = $('#btnSaveFactoryTax');
                const alertBox = $('#factoryTaxAlert');

                alertBox.addClass('d-none').text('');
                btn.prop('disabled', true);

                $.ajax({
                    url: updateUrlTemplate.replace('__ID__', id),
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        taxModal.hide();
                        $('#factoryTaxPageAlertText').text(res.message || 'Data pajak pabrik berhasil disimpan.');
                        $('#factoryTaxPageAlert').removeClass('d-none').addClass('show');
                        table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON?.message || 'Gagal menyimpan data pajak pabrik.';
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
