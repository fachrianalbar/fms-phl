@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Supplier',
    'secondSegment' => $title,
])

@push('style')
    @include('purchasing.purchase.partials.table-style')
    <style>
        #dt-supplier-purchase-detail tbody tr.table-warning-subtle td { background-color: #fffbeb !important; }
        #dt-supplier-purchase-detail tbody tr.table-danger-subtle td { background-color: #fff1f2 !important; }
        #dt-supplier-purchase-detail tbody tr.table-warning-subtle:hover td { background-color: #fef3c7 !important; }
        #dt-supplier-purchase-detail tbody tr.table-danger-subtle:hover td { background-color: #ffe4e6 !important; }
        .supplier-contact-line { color: #64748b; font-size: 12px; }
        .status-legend { font-size: 11.5px; color: #64748b; }
    </style>
@endpush

@section('content')
    @php
        $exportFilters = array_filter([
            'supplierCode' => $supplier->code,
            'purchaseCode' => $purchaseCode,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ], fn ($value) => $value !== null && $value !== '');
        $excelDetailUrl = route($view . 'excel-supplier-detail', $exportFilters);
        $pdfDetailUrl = route($view . 'pdf-supplier-detail', $exportFilters);
    @endphp

    <div class="col-sm-12">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center shadow-sm text-white"
                    style="width: 48px; height: 48px; background: linear-gradient(135deg, #0f766e 0%, #115e59 100%) !important;">
                    <i class="mdi mdi-factory fs-24"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-0 text-dark">{{ $supplier->name }}</h4>
                    <div class="supplier-contact-line">
                        <span class="me-3"><i class="mdi mdi-identifier me-1"></i>{{ $supplier->code }}</span>
                        <span><i class="mdi mdi-file-chart-outline me-1"></i>Detail hutang dan jatuh tempo pembelian</span>
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ $excelDetailUrl }}" target="_blank" class="btn btn-outline-success btn-sm rounded-pill px-3">
                    <i class="mdi mdi-file-excel me-1"></i>Excel
                </a>
                <a href="{{ $pdfDetailUrl }}" target="_blank" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                    <i class="mdi mdi-file-pdf-box me-1"></i>PDF
                </a>
                <a href="{{ route($view . 'index') }}" class="btn btn-light btn-sm rounded-pill px-3">
                    <i class="mdi mdi-arrow-left me-1"></i>Kembali
                </a>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Total Tagihan</div>
                            <div class="stat-value text-dark">Rp {{ number_format($totalAmount, 0, ',', '.') }}</div>
                        </div>
                        <div class="stat-icon-wrapper bg-primary-subtle text-primary"><i class="mdi mdi-receipt-text-outline"></i></div>
                    </div>
                    <div class="stat-desc">{{ number_format($totalPurchase, 0, ',', '.') }} PO, {{ number_format($totalItem, 0, ',', '.') }} jenis item</div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Sisa Hutang</div>
                            <div class="stat-value text-dark">Rp {{ number_format($totalRemaining, 0, ',', '.') }}</div>
                        </div>
                        <div class="stat-icon-wrapper bg-info-subtle text-info"><i class="mdi mdi-cash-multiple"></i></div>
                    </div>
                    <div class="stat-desc">Terbayar Rp {{ number_format($totalPaid, 0, ',', '.') }}</div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Jatuh Tempo ≤ 7 Hari</div>
                            <div class="stat-value text-warning-emphasis">Rp {{ number_format($dueSoonAmount, 0, ',', '.') }}</div>
                        </div>
                        <div class="stat-icon-wrapper bg-warning-subtle text-warning"><i class="mdi mdi-calendar-clock"></i></div>
                    </div>
                    <div class="stat-desc">{{ number_format($dueSoonCount, 0, ',', '.') }} PO perlu disiapkan</div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Overdue</div>
                            <div class="stat-value text-danger">Rp {{ number_format($overdueAmount, 0, ',', '.') }}</div>
                        </div>
                        <div class="stat-icon-wrapper bg-danger-subtle text-danger"><i class="mdi mdi-alert-circle-outline"></i></div>
                    </div>
                    <div class="stat-desc text-danger">{{ number_format($overdueCount, 0, ',', '.') }} PO lewat jatuh tempo</div>
                </div>
            </div>
        </div>

        <div class="table-container-card mb-4">
            <div class="table-top-bar">
                <form method="GET" action="{{ route('report.supplier.detail', ['supplierCode' => $supplier->code]) }}" class="row g-3 align-items-end">
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold" for="purchaseCode">Kode Pembelian</label>
                        <input class="form-control" name="purchaseCode" id="purchaseCode" type="text" value="{{ $purchaseCode }}" placeholder="Cari kode PO">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold" for="startDate">Tanggal Awal</label>
                        <input class="form-control" name="startDate" id="startDate" type="date" value="{{ $startDate }}">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold" for="endDate">Tanggal Akhir</label>
                        <input class="form-control" name="endDate" id="endDate" type="date" value="{{ $endDate }}">
                    </div>
                    <div class="col-12 col-md-2 d-grid">
                        <button class="btn btn-primary" type="submit"><i class="mdi mdi-filter-outline me-1"></i>Filter</button>
                    </div>
                </form>
            </div>

            <div class="p-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                    <div>
                        <h6 class="fw-bold mb-0">Daftar Pembelian</h6>
                        <span class="status-legend">
                            <span class="badge bg-warning-subtle text-warning-emphasis me-1">Kuning</span> jatuh tempo maksimal 7 hari
                            <span class="badge bg-danger-subtle text-danger-emphasis ms-2 me-1">Merah</span> overdue
                        </span>
                    </div>
                </div>
                <div class="table-responsive custom-scrollbar">
                    <table class="table purchase-table w-100 nowrap" id="dt-supplier-purchase-detail">
                        <thead>
                            <tr>
                                <th>Detail</th>
                                <th>No</th>
                                <th>Kode Pembelian</th>
                                <th>Tanggal PO</th>
                                <th>Jatuh Tempo</th>
                                <th>Status</th>
                                <th>Gudang</th>
                                <th>Item</th>
                                <th>Tagihan</th>
                                <th>Terbayar</th>
                                <th>Sisa Hutang</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="purchaseDetailModal" tabindex="-1" aria-labelledby="purchaseDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="purchaseDetailModalLabel">Detail Item Pembelian</h5>
                        <small class="text-muted" id="detail-purchase-code">-</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-3"><small class="text-muted d-block">Tanggal PO</small><strong id="detail-purchase-date">-</strong></div>
                        <div class="col-6 col-md-3"><small class="text-muted d-block">Jatuh Tempo</small><strong id="detail-purchase-due">-</strong></div>
                        <div class="col-6 col-md-3"><small class="text-muted d-block">Supplier</small><strong id="detail-purchase-supplier">-</strong></div>
                        <div class="col-6 col-md-3"><small class="text-muted d-block">Gudang</small><strong id="detail-purchase-warehouse">-</strong></div>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-4"><div class="p-3 bg-light rounded-3"><small class="text-muted d-block">Tagihan</small><strong id="detail-billing">Rp 0</strong></div></div>
                        <div class="col-12 col-md-4"><div class="p-3 bg-light rounded-3"><small class="text-muted d-block">Terbayar</small><strong id="detail-paid">Rp 0</strong></div></div>
                        <div class="col-12 col-md-4"><div class="p-3 bg-light rounded-3"><small class="text-muted d-block">Sisa Hutang</small><strong id="detail-remaining">Rp 0</strong></div></div>
                    </div>
                    <div class="table-responsive custom-scrollbar">
                        <table class="table table-bordered table-sm mb-0">
                            <thead><tr><th>No</th><th>Kode Item</th><th>Nama Item</th><th>Deskripsi</th><th>Qty</th><th>Harga</th><th>Subtotal</th></tr></thead>
                            <tbody id="purchase-detail-items"></tbody>
                            <tfoot><tr><th colspan="4" class="text-end">TOTAL</th><th id="purchase-detail-total-qty">0,0</th><th></th><th id="purchase-detail-total-amount">Rp 0</th></tr></tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        const purchaseDetailModal = new bootstrap.Modal(document.getElementById('purchaseDetailModal'));

        $(document).ready(function() {
            $('#dt-supplier-purchase-detail').DataTable({
                processing: true,
                serverSide: true,
                destroy: true,
                scrollX: true,
                ajax: {
                    url: "{{ route('dt.report-supplier-detail', ['supplierCode' => $supplier->code]) }}",
                    data: function(d) {
                        d.purchaseCode = @json($purchaseCode);
                        d.startDate = @json($startDate);
                        d.endDate = @json($endDate);
                    }
                },
                columns: [
                    { data: 'action' },
                    { data: 'DT_RowIndex' },
                    { data: 'code' },
                    { data: 'purchaseDate' },
                    { data: 'dueDate' },
                    { data: 'dueStatus' },
                    { data: 'warehouseName' },
                    { data: 'totalItem' },
                    { data: 'billingAmount' },
                    { data: 'paidAmountValue' },
                    { data: 'remainingAmount' }
                ],
                columnDefs: [
                    { searchable: false, targets: [0, 1, 4, 5, 7, 8, 9, 10] },
                    { orderable: false, targets: [0, 1, 5] }
                ],
                order: [[3, 'desc']],
                language: { emptyTable: 'Belum ada pembelian supplier pada periode ini.' }
            });
        });

        function showPurchaseItems(purchaseCode) {
            const url = "{{ route('ajax.supplier-purchase-detail-items', ['purchaseCode' => ':purchaseCode']) }}".replace(':purchaseCode', encodeURIComponent(purchaseCode));

            $.get(url, function(response) {
                const number = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 });
                const qty = new Intl.NumberFormat('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 1 });
                const rupiah = value => 'Rp ' + number.format(value || 0);

                $('#detail-purchase-code').text(response.code || '-');
                $('#detail-purchase-date').text(response.purchaseDate || '-');
                const dueDateNote = response.dueDateSource === 'purchase_date'
                    ? ' (estimasi tanggal PO)'
                    : (response.dueDateSource === 'today' ? ' (fallback hari ini)' : '');
                $('#detail-purchase-due').text((response.dueDate || '-') + dueDateNote);
                $('#detail-purchase-supplier').text(response.supplierName || '-');
                $('#detail-purchase-warehouse').text(response.warehouse || '-');
                $('#detail-billing').text(rupiah(response.billingAmount));
                $('#detail-paid').text(rupiah(response.paidAmount));
                $('#detail-remaining').text(rupiah(response.remainingAmount));

                let rows = '';
                if (response.details && response.details.length) {
                    response.details.forEach(function(item, index) {
                        rows += '<tr><td>' + (index + 1) + '</td><td>' + item.itemCode + '</td><td>' + item.itemName + '</td><td>' + item.description + '</td><td>' + qty.format(item.qty) + '</td><td>' + rupiah(item.price) + '</td><td>' + rupiah(item.subtotal) + '</td></tr>';
                    });
                } else {
                    rows = '<tr><td colspan="7" class="text-center text-muted py-4">Tidak ada detail item.</td></tr>';
                }

                $('#purchase-detail-items').html(rows);
                $('#purchase-detail-total-qty').text(qty.format(response.totalQty || 0));
                $('#purchase-detail-total-amount').text(rupiah(response.totalAmount));
                purchaseDetailModal.show();
            });
        }
    </script>
@endpush
