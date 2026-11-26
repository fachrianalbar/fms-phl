@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Inventory',
    'secondSegment' => $title,
])

@push('style')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom-select2.css') }}">
    @include('inventory.partials.table-style')
@endpush

@section('content')
    @php
        $missing = $audit['missing'];
        $mismatched = $audit['mismatched'];
        $orphans = $audit['orphans'];
        $totalIssue = $audit['summary']['missing'] + $audit['summary']['mismatched'] + $audit['summary']['orphans'];
    @endphp

    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h4 class="mb-1">{{ $title }}</h4>
                    <div class="d-flex gap-2">
                        <span class="badge bg-warning text-dark">Missing: {{ $audit['summary']['missing'] }}</span>
                        <span class="badge bg-info">Tidak Sinkron: {{ $audit['summary']['mismatched'] }}</span>
                        <span class="badge bg-danger">Orphan: {{ $audit['summary']['orphans'] }}</span>
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <a href="{{ route('inventory.stock-sync.export-excel') }}" target="_blank" id="export-excel"
                        class="btn btn-icon btn-sm bg-success-subtle" data-bs-toggle="tooltip" title="Export Excel">
                        <i class="mdi mdi-file-excel fs-14 text-success"></i>
                    </a>
                    <a href="{{ route('inventory.stock-sync.export-pdf') }}" target="_blank" id="export-pdf"
                        class="btn btn-icon btn-sm bg-danger-subtle" data-bs-toggle="tooltip" title="Export PDF">
                        <i class="mdi mdi-file-pdf-box fs-14 text-danger"></i>
                    </a>
                    <a href="{{ route('inventory.stock.index') }}" class="btn btn-sm btn-light">
                        <i class="mdi mdi-arrow-left me-1"></i> Stock
                    </a>
                    <form action="{{ route('inventory.stock-sync.sync') }}" method="POST" id="syncStockForm">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-primary" @disabled($totalIssue === 0)>
                            <i class="mdi mdi-sync me-1"></i> Sync Stock
                        </button>
                    </form>
                </div>
            </div>

            <div class="card-body">
                @include('partials.alert')

                <div class="filter-card">
                    <button type="button" class="filter-card-header" data-bs-toggle="collapse"
                        data-bs-target="#stockSyncFilterCollapse" aria-expanded="false"
                        aria-controls="stockSyncFilterCollapse">
                        <span class="filter-card-heading">
                            <i class="mdi mdi-filter-variant"></i>
                            <strong>Filter Data</strong>
                            <small>Gunakan filter gudang, barang, kategori masalah, atau sumber transaksi</small>
                        </span>
                        <i class="mdi mdi-chevron-down filter-card-chevron"></i>
                    </button>

                    <div class="collapse filter-collapse" id="stockSyncFilterCollapse">
                        <div class="filter-collapse-body">
                            <div id="filterForm">
                                <div class="row g-3">
                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="warehouseCode">Gudang</label>
                                        <select class="form-select select2-filter" name="warehouseCode" id="warehouseCode">
                                            <option value="">Semua Gudang</option>
                                            @foreach ($warehouses ?? [] as $wh)
                                                <option value="{{ $wh->code }}" @selected(($filters['warehouseCode'] ?? '') === $wh->code)>
                                                    {{ $wh->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="itemCode">Barang</label>
                                        <select class="form-select select2-filter" name="itemCode" id="itemCode">
                                            <option value="">Semua Barang</option>
                                            @foreach ($items ?? [] as $it)
                                                <option value="{{ $it->code }}" @selected(($filters['itemCode'] ?? '') === $it->code)>
                                                    {{ $it->code }} - {{ $it->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="issueType">Kategori Masalah</label>
                                        <select class="form-select select2-filter" name="issueType" id="issueType">
                                            <option value="">Semua Kategori</option>
                                            <option value="missing" @selected(($filters['issueType'] ?? '') === 'missing')>
                                                Missing</option>
                                            <option value="mismatched" @selected(($filters['issueType'] ?? '') === 'mismatched')>Tidak Sinkron</option>
                                            <option value="orphans" @selected(($filters['issueType'] ?? '') === 'orphans')>
                                                Orphan</option>
                                        </select>
                                    </div>
                                    <div class="col-xl-3 col-md-6">
                                        <label class="filter-label" for="source">Sumber Transaksi</label>
                                        <select class="form-select select2-filter" name="source" id="source">
                                            <option value="">Semua Sumber</option>
                                            <option value="Purchase" @selected(($filters['source'] ?? '') === 'Purchase')>
                                                Purchase</option>
                                            <option value="Maintenance" @selected(($filters['source'] ?? '') === 'Maintenance')>Maintenance</option>
                                        </select>
                                    </div>
                                    <div class="col-12 d-flex align-items-end justify-content-md-end gap-2">
                                        <button class="btn btn-filter-primary flex-grow-1 flex-md-grow-0" type="button"
                                            id="btnFilter">
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

                @if ($totalIssue === 0)
                    <div class="alert alert-success mb-0">
                        Semua transaksi purchase dan maintenance sudah sinkron dengan stock transaction.
                    </div>
                @else
                    <div class="alert alert-info">
                        Missing akan diinsert, data tidak sinkron akan diupdate, dan orphan akan dihapus dari
                        <strong>stock_transaction</strong>. Data <strong>INITIAL</strong> tidak ikut dihapus.
                    </div>
                @endif

                <h5 class="mt-4 mb-3">Missing Stock Transaction</h5>
                <div class="table-responsive custom-scrollbar">
                    <table class="table table-striped table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Source</th>
                                <th>Transaction Code</th>
                                <th>Detail Code</th>
                                <th>Item Code</th>
                                <th>Item Name</th>
                                <th>Warehouse</th>
                                <th class="text-end">Qty In</th>
                                <th class="text-end">Qty Out</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($missing as $row)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <span
                                            class="badge {{ $row['source'] === 'Purchase' ? 'bg-success' : 'bg-warning text-dark' }}">
                                            {{ $row['source'] }}
                                        </span>
                                    </td>
                                    <td>{{ $row['transactionCode'] }}</td>
                                    <td>{{ $row['transactionDetailCode'] }}</td>
                                    <td>{{ $row['itemCode'] }}</td>
                                    <td>{{ $row['itemName'] }}</td>
                                    <td>{{ $row['warehouseName'] }} ({{ $row['warehouseCode'] ?? '-' }})</td>
                                    <td class="text-end">
                                        {{ $row['qtyIn'] > 0 ? number_format($row['qtyIn'], 1, ',', '.') : '-' }}</td>
                                    <td class="text-end">
                                        {{ $row['qtyOut'] > 0 ? number_format($row['qtyOut'], 1, ',', '.') : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted">Tidak ada missing stock transaction.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <h5 class="mt-4 mb-3">Stock Transaction Tidak Sinkron</h5>
                <div class="table-responsive custom-scrollbar">
                    <table class="table table-striped table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Source</th>
                                <th>Transaction Code</th>
                                <th>Detail Code</th>
                                <th>Item Code</th>
                                <th>Item Name</th>
                                <th>Warehouse Sekarang</th>
                                <th>Warehouse Source</th>
                                <th class="text-end">Qty In</th>
                                <th class="text-end">Qty Out</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($mismatched as $row)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <span
                                            class="badge {{ $row['source'] === 'Purchase' ? 'bg-success' : 'bg-warning text-dark' }}">
                                            {{ $row['source'] }}
                                        </span>
                                    </td>
                                    <td>{{ $row['transactionCode'] }}</td>
                                    <td>{{ $row['transactionDetailCode'] }}</td>
                                    <td>{{ $row['itemCode'] }}</td>
                                    <td>{{ $row['itemName'] }}</td>
                                    <td>{{ $row['currentWarehouseCode'] ?? '-' }}</td>
                                    <td>{{ $row['warehouseCode'] ?? '-' }}</td>
                                    <td class="text-end">
                                        {{ number_format($row['currentQtyIn'], 1, ',', '.') }}
                                        <i class="mdi mdi-arrow-right mx-1"></i>
                                        {{ number_format($row['qtyIn'], 1, ',', '.') }}
                                    </td>
                                    <td class="text-end">
                                        {{ number_format($row['currentQtyOut'], 1, ',', '.') }}
                                        <i class="mdi mdi-arrow-right mx-1"></i>
                                        {{ number_format($row['qtyOut'], 1, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted">Tidak ada stock transaction yang perlu
                                        diupdate.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <h5 class="mt-4 mb-3">Orphan Stock Transaction</h5>
                <div class="table-responsive custom-scrollbar">
                    <table class="table table-striped table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Type</th>
                                <th>Transaction Code</th>
                                <th>Detail Code</th>
                                <th>Item Code</th>
                                <th>Item Name</th>
                                <th>Warehouse</th>
                                <th class="text-end">Qty In</th>
                                <th class="text-end">Qty Out</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($orphans as $row)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <span
                                            class="badge {{ $row['transactionType'] === 'OUT' ? 'bg-warning text-dark' : 'bg-success' }}">
                                            {{ $row['transactionType'] }}
                                        </span>
                                    </td>
                                    <td>{{ $row['transactionCode'] ?? '-' }}</td>
                                    <td>{{ $row['transactionDetailCode'] ?? '-' }}</td>
                                    <td>{{ $row['itemCode'] ?? '-' }}</td>
                                    <td>{{ $row['itemName'] }}</td>
                                    <td>{{ $row['warehouseName'] }} ({{ $row['warehouseCode'] ?? '-' }})</td>
                                    <td class="text-end">
                                        {{ $row['qtyIn'] > 0 ? number_format($row['qtyIn'], 1, ',', '.') : '-' }}</td>
                                    <td class="text-end">
                                        {{ $row['qtyOut'] > 0 ? number_format($row['qtyOut'], 1, ',', '.') : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted">Tidak ada orphan stock transaction.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="syncProgressModal" tabindex="-1" aria-labelledby="syncProgressModalLabel"
        aria-hidden="true">
        <div class="modal-dialog mt-4">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="syncProgressModalLabel">Proses Sinkronisasi Stock</h5>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span id="syncProgressStatus">Menyiapkan data...</span>
                        <strong id="syncProgressPercent">0%</strong>
                    </div>
                    <div class="progress" style="height: 22px;">
                        <div id="syncProgressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                            role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%
                        </div>
                    </div>
                    <div class="small text-muted mt-3" id="syncProgressDetail">
                        Memulai sinkronisasi per chunk agar proses tidak berat.
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('.select2-filter').select2({
                placeholder: 'Semua pilihan',
                allowClear: true,
                width: '100%'
            });

            function getFilters() {
                return {
                    warehouseCode: $('#warehouseCode').val() || '',
                    itemCode: $('#itemCode').val() || '',
                    issueType: $('#issueType').val() || '',
                    source: $('#source').val() || ''
                };
            }

            function buildQueryString() {
                const params = new URLSearchParams();
                Object.entries(getFilters()).forEach(function (entry) {
                    if (entry[1]) params.set(entry[0], entry[1]);
                });
                return params.toString() ? '?' + params.toString() : '';
            }

            function syncExportUrls() {
                const query = buildQueryString();
                $('#export-excel').attr('href', "{{ route('inventory.stock-sync.export-excel') }}" + query);
                $('#export-pdf').attr('href', "{{ route('inventory.stock-sync.export-pdf') }}" + query);
            }

            $('.select2-filter').on('change', syncExportUrls);
            $('#btnFilter').on('click', function () {
                window.location.href = "{{ route('inventory.stock-sync.index') }}" + buildQueryString();
            });
            $('#btnResetFilter').on('click', function () {
                window.location.href = "{{ route('inventory.stock-sync.index') }}";
            });

            syncExportUrls();
        });

        const syncChunkLimit = 50;
        const syncState = {
            jobId: null,
            offset: 0,
            total: 0,
            inserted: 0,
            updated: 0,
            deleted: 0,
        };

        function setSyncProgress(percent, status, detail) {
            const normalizedPercent = Math.max(0, Math.min(100, percent));

            $('#syncProgressStatus').text(status);
            $('#syncProgressPercent').text(`${normalizedPercent}%`);
            $('#syncProgressBar')
                .css('width', `${normalizedPercent}%`)
                .attr('aria-valuenow', normalizedPercent)
                .text(`${normalizedPercent}%`);
            $('#syncProgressDetail').text(detail);
        }

        function showSyncProgress() {
            $('#syncProgressModal').modal({
                backdrop: 'static',
                keyboard: false
            });
            $('#syncProgressModal').modal('show');
        }

        function hideSyncProgress() {
            $('#syncProgressModal').modal('hide');
        }

        function prepareSyncJob() {
            setSyncProgress(0, 'Menyiapkan data...', 'Membaca data purchase, maintenance, dan stock transaction.');

            return $.ajax({
                url: "{{ route('inventory.stock-sync.prepare') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}"
                }
            });
        }

        function processSyncChunk() {
            return $.ajax({
                url: "{{ route('inventory.stock-sync.process-chunk') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    jobId: syncState.jobId,
                    offset: syncState.offset,
                    limit: syncChunkLimit
                }
            }).then(function (response) {
                const data = response.data;

                syncState.offset = data.processed;
                syncState.total = data.total;
                syncState.inserted += data.counts.inserted;
                syncState.updated += data.counts.updated;
                syncState.deleted += data.counts.deleted;

                setSyncProgress(
                    data.percent,
                    `Memproses ${data.processed} dari ${data.total} data...`,
                    `Insert: ${syncState.inserted}, Update: ${syncState.updated}, Hapus: ${syncState.deleted}`
                );

                if (data.done) {
                    return data;
                }

                return processSyncChunk();
            });
        }

        $('#syncStockForm').on('submit', function (e) {
            e.preventDefault();

            swal({
                title: 'Jalankan sinkronisasi stock?',
                text: 'Sistem akan insert missing, update data tidak sinkron, dan hapus orphan stock transaction. Data INITIAL tidak ikut dihapus.',
                icon: 'warning',
                buttons: ['Batal', 'Ya, sync sekarang'],
                dangerMode: false,
            }).then(function (willSync) {
                if (!willSync) {
                    return;
                }

                syncState.jobId = null;
                syncState.offset = 0;
                syncState.total = 0;
                syncState.inserted = 0;
                syncState.updated = 0;
                syncState.deleted = 0;

                showSyncProgress();

                prepareSyncJob()
                    .then(function (response) {
                        const data = response.data;

                        syncState.jobId = data.jobId;
                        syncState.total = data.total;

                        if (data.total === 0) {
                            setSyncProgress(100, 'Tidak ada data yang perlu disinkronkan.', 'Semua data sudah sinkron.');

                            setTimeout(function () {
                                hideSyncProgress();
                                swal('Selesai', 'Tidak ada data yang perlu disinkronkan.', 'success')
                                    .then(function () {
                                        window.location.reload();
                                    });
                            }, 500);

                            return null;
                        }

                        setSyncProgress(0, `Menyiapkan ${data.total} data...`, 'Mulai proses sinkronisasi per chunk.');

                        return processSyncChunk();
                    })
                    .then(function (result) {
                        if (!result) {
                            return;
                        }

                        setSyncProgress(100, 'Sinkronisasi selesai.', `Insert: ${syncState.inserted}, Update: ${syncState.updated}, Hapus: ${syncState.deleted}`);

                        setTimeout(function () {
                            hideSyncProgress();
                            swal(
                                'Sinkronisasi selesai',
                                `Insert: ${syncState.inserted}, Update: ${syncState.updated}, Hapus: ${syncState.deleted}`,
                                'success'
                            ).then(function () {
                                window.location.reload();
                            });
                        }, 500);
                    })
                    .catch(function (xhr) {
                        hideSyncProgress();

                        const message = xhr.responseJSON && xhr.responseJSON.message
                            ? xhr.responseJSON.message
                            : 'Terjadi kesalahan saat sinkronisasi stock.';

                        swal('Gagal', message, 'error');
                    });
            });
        });
    </script>
@endpush