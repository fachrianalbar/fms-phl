@extends('layouts.main', [
'title' => $title,
'pageTitle' => $title,
'firstSegment' => 'Vendor',
'secondSegment' => 'Daftar Pembayaran',
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
<link rel="stylesheet" type="text/css"
    href="{{ asset('assets/libs/datatables.net-select-bs5/css/select.bootstrap5.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/sweetalert2.css') }}">

@include('vendor.invoice.partials.table-style')
@endpush

@push('style')
<style>
    /* ===== Tombol perluas (kolom aksi kiri) ===== */
    .js-toggle-detail {
        transition: all 0.18s ease;
    }
    .js-toggle-detail .mdi {
        transition: transform 0.22s ease;
    }
    .js-toggle-detail.is-open .mdi {
        transform: rotate(180deg);
    }
    tr.row-open {
        background-color: #eff6ff !important;
    }
    tr.row-open > td {
        border-bottom: 1px solid #dbeafe !important;
    }
    tr.child-row-payment > td {
        padding: 0 16px 16px 60px !important;
        background: #f8fafc !important;
        border-bottom: 1px solid #e2e8f0 !important;
        white-space: normal !important;
        overflow-wrap: break-word;
        word-break: break-word;
    }

    /* ===== Panel rincian transaksi (inline expand) ===== */
    .tx-panel {
        border: 1px solid #dbe3ef;
        border-radius: 14px;
        overflow: hidden;
        background: #ffffff;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.07);
        animation: txPanelIn 0.22s ease;
    }
    @keyframes txPanelIn {
        from { opacity: 0; transform: translateY(-6px); }
        to { opacity: 1; transform: none; }
    }
    .tx-hero {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        padding: 16px 20px;
    }
    .tx-hero-kicker {
        color: rgba(255, 255, 255, 0.75);
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }
    .tx-hero-code {
        color: #ffffff;
        font-size: 17px;
        font-weight: 700;
        line-height: 1.3;
        word-break: break-all;
    }
    .tx-hero-meta {
        color: rgba(255, 255, 255, 0.85);
        font-size: 12px;
        margin-top: 2px;
    }
    .tx-hero-total {
        color: #ffffff;
        font-size: 20px;
        font-weight: 800;
        letter-spacing: -0.01em;
        white-space: nowrap;
    }
    .tx-chip-label {
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #94a3b8;
        margin-bottom: 2px;
    }
    .tx-chip-value {
        font-size: 13px;
        font-weight: 600;
        color: #0f172a;
    }
    .tx-nota-card {
        border-color: #e2e8f0;
    }
    .tx-nota-head {
        background: linear-gradient(135deg, #475569 0%, #1e293b 100%);
    }
    .tx-order-table th {
        font-size: 10.5px !important;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
        background: #f8fafc !important;
        padding: 8px 12px !important;
        border-bottom: 1px solid #e2e8f0 !important;
    }
    .tx-order-table td {
        font-size: 12.5px !important;
        padding: 7px 12px !important;
        vertical-align: middle;
    }
    .tx-order-table tbody tr:last-child td {
        border-bottom: none !important;
    }
</style>
@endpush

@section('content')
<div class="col-sm-12">
    <!-- Page Header & Action Bar -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-primary text-white rounded-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important;">
                <i class="mdi mdi-credit-card-outline fs-24"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    {{ $title }}
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill fs-12 px-2 py-1">
                        {{ number_format($stats['paymentCount'] ?? 0) }} Transaksi
                    </span>
                </h4>
                <p class="text-muted mb-0 fs-12">Riwayat seluruh transaksi pembayaran ke vendor armada eksternal — termasuk DP, cicilan, dan pelunasan nota.</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm" id="btn-refresh-table" title="Muat Ulang Data Tabel">
                <i class="mdi mdi-refresh me-1"></i> Refresh
            </button>
        </div>
    </div>

    @include('partials.alert')

    <!-- 4 KPI Summary Cards -->
    <div class="row g-3 mb-4">
        <!-- Card 1: Jumlah Transaksi -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Jumlah Transaksi</div>
                        <div class="stat-value text-primary">{{ number_format($stats['paymentCount'] ?? 0) }}</div>
                    </div>
                    <div class="stat-icon-wrapper bg-primary-subtle text-primary">
                        <i class="mdi mdi-swap-horizontal"></i>
                    </div>
                </div>
                <div class="stat-desc text-muted mt-2">
                    <i class="mdi mdi-history me-1"></i>Termasuk DP dan cicilan
                </div>
            </div>
        </div>

        <!-- Card 2: Total Nominal Keluar -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Total Nominal Keluar</div>
                        <div class="stat-value text-success">Rp {{ number_format($stats['paymentSum'] ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <div class="stat-icon-wrapper bg-success-subtle text-success">
                        <i class="mdi mdi-cash-minus"></i>
                    </div>
                </div>
                <div class="stat-desc text-muted mt-2">
                    <i class="mdi mdi-arrow-up-bold-circle-outline me-1"></i>Total dana yang disalurkan ke vendor
                </div>
            </div>
        </div>

        <!-- Card 3: Nota Terlibat -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Nota Terlibat</div>
                        <div class="stat-value text-info">{{ number_format($stats['notaCount'] ?? 0) }}</div>
                    </div>
                    <div class="stat-icon-wrapper bg-info-subtle text-info">
                        <i class="mdi mdi-file-document-multiple-outline"></i>
                    </div>
                </div>
                <div class="stat-desc text-muted mt-2">
                    <i class="mdi mdi-file-document-outline me-1"></i>Nota unik dalam riwayat transaksi
                </div>
            </div>
        </div>

        <!-- Card 4: Vendor Berbeda -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Vendor Berbeda</div>
                        <div class="stat-value text-warning">{{ number_format($stats['vendorCount'] ?? 0) }}</div>
                    </div>
                    <div class="stat-icon-wrapper bg-warning-subtle text-warning">
                        <i class="mdi mdi-truck-check-outline"></i>
                    </div>
                </div>
                <div class="stat-desc text-muted mt-2">
                    <i class="mdi mdi-truck-outline me-1"></i>Perusahaan armada eksternal penerima dana
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="table-container-card mb-4">
        <div class="table-top-bar d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fw-bold fs-12">
                    <i class="mdi mdi-format-list-bulleted me-1"></i> Daftar Pembayaran Vendor
                </span>
            </div>
            <div class="text-muted fs-12">
                <i class="mdi mdi-information-outline me-1 text-primary"></i>Klik tombol <i class="mdi mdi-chevron-down text-primary"></i> pada kolom kiri tiap baris untuk melihat rincian nota & order.
            </div>
        </div>

        <div class="card-body p-3">
            <div class="table-responsive custom-scrollbar">
                <table class="table table-striped w-100 nowrap invoice-table" id="dt">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 56px;">Aksi</th>
                            <th>{{ __('menu_vendor_payment.payment_date') }}</th>
                            <th>Kode Transaksi</th>
                            <th>No. Nota</th>
                            <th>Vendor</th>
                            <th class="text-end">Nominal</th>
                            <th>Bank Sumber Dana</th>
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
<script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>

<script>
    let paymentTable;

    $(document).ready(function() {
        paymentTable = $('#dt').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            pageLength: 25,
            ajax: "{{ route('dt.vendor-payment-list') }}",
            columns: [
                { data: 'action', className: 'text-center', orderable: false, searchable: false },
                { data: 'payment_date' },
                { data: 'batch_code' },
                { data: 'nota_orders' },
                { data: 'vendor' },
                { data: 'amount', className: 'text-end' },
                { data: 'bank' },
                { data: 'description' },
            ],
            columnDefs: [
                { searchable: false, targets: [0] },
                { orderable: false, targets: [0] }
            ],
            order: [[1, 'desc']],
            language: {
                search: "",
                searchPlaceholder: "Cari kode transaksi, no nota, order, vendor...",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ transaksi",
                infoEmpty: "Tidak ada data transaksi",
                zeroRecords: "Tidak ditemukan data yang sesuai",
                paginate: {
                    next: "<i class='mdi mdi-chevron-right'></i>",
                    previous: "<i class='mdi mdi-chevron-left'></i>"
                }
            }
        });

        // ====== Helper format ======
        function formatCurrency(amount) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(Number(amount) || 0));
        }

        function formatDate(date) {
            return date
                ? new Date(date + 'T00:00:00').toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
                : '-';
        }

        // ====== Expand / collapse baris rincian ======
        let $openRow = null;
        let openKey = null;
        let activeKey = null;

        function setOpen($tr, open) {
            const $btn = $tr.find('.js-toggle-detail').first();
            $tr.toggleClass('row-open', open);
            $btn.toggleClass('btn-primary is-open', open);
            $btn.toggleClass('btn-outline-primary', !open);
        }

        function collapseOpenRow() {
            if ($openRow) {
                $openRow.next('tr.child-row-payment').remove();
                setOpen($openRow, false);
                $openRow = null;
            }
            openKey = null;
            activeKey = null;
        }

        function loadingNode() {
            return $('<div>', { class: 'd-flex align-items-center justify-content-center text-muted py-4' })
                .append($('<span>', { class: 'spinner-border spinner-border-sm me-2' }))
                .append('Memuat rincian transaksi...');
        }

        function infoChip(label, value) {
            return $('<div>').append(
                $('<div>', { class: 'tx-chip-label' }).text(label),
                $('<div>', { class: 'tx-chip-value' }).text(value || '-')
            );
        }

        function buildDetailPanel(data) {
            const panel = $('<div>', { class: 'tx-panel' });

            // ----- Header gradient -----
            const hero = $('<div>', { class: 'tx-hero d-flex flex-wrap justify-content-between align-items-center gap-3' });
            const heroLeft = $('<div>').append(
                $('<div>', { class: 'tx-hero-kicker' }).text('Detail Transaksi Pembayaran'),
                $('<div>', { class: 'tx-hero-code font-monospace' }).text(data.code || '-'),
                $('<div>', { class: 'tx-hero-meta' }).text(
                    [formatDate(data.payment_date), data.is_legacy ? 'Arsip transaksi lama' : ''].filter(Boolean).join('  ·  ')
                )
            );
            const heroRight = $('<div>', { class: 'text-end' }).append(
                $('<div>', { class: 'tx-hero-kicker text-uppercase' }).text('Total Transaksi'),
                $('<div>', { class: 'tx-hero-total' }).text(formatCurrency(data.amount))
            );
            hero.append(heroLeft, heroRight);

            const body = $('<div>', { class: 'tx-body p-3' });

            // ----- Ringkasan -----
            const chips = $('<div>', { class: 'row g-3 mb-3' });
            const bankText = data.bank
                ? [data.bank.name, data.bank.account_number, data.bank.account_name].filter(Boolean).join(' · ')
                : '-';
            chips.append($('<div>', { class: 'col-sm-6 col-lg-3' }).append(infoChip('Bank Sumber Dana', bankText)));
            chips.append($('<div>', { class: 'col-sm-6 col-lg-3' }).append(infoChip('Tanggal Bayar', formatDate(data.payment_date))));
            chips.append($('<div>', { class: 'col-lg-6' }).append(infoChip('Keterangan', data.description || '-')));
            body.append(chips);

            // ----- Rincian nota & order -----
            const notas = Array.isArray(data.notas) ? data.notas : [];
            const orderCount = notas.reduce(function(total, nota) {
                return total + (Array.isArray(nota.orders) ? nota.orders.length : 0);
            }, 0);

            const sectionHead = $('<div>', { class: 'd-flex flex-wrap justify-content-between align-items-center border-top pt-3 mb-2' });
            const title = $('<h6>', { class: 'mb-0 fw-bold text-dark' })
                .append($('<i>', { class: 'mdi mdi-file-document-multiple-outline text-primary fs-16 me-1' }))
                .append('Rincian Nota & Order');
            sectionHead.append(title);
            sectionHead.append($('<span>', { class: 'text-muted fs-12' }).text(notas.length + ' nota · ' + orderCount + ' order'));
            body.append(sectionHead);

            if (notas.length === 0) {
                body.append($('<div>', { class: 'text-center text-muted py-4' }).text('Rincian transaksi tidak tersedia.'));
            } else {
                const wrapper = $('<div>', { class: 'd-flex flex-column gap-2' });
                notas.forEach(function(nota) {
                    const card = $('<div>', { class: 'tx-nota-card border rounded-3 overflow-hidden' });
                    const cardHead = $('<div>', { class: 'tx-nota-head d-flex justify-content-between align-items-center gap-2 px-3 py-2' })
                        .append($('<span>', { class: 'font-monospace fw-bold fs-13 text-white' }).text(nota.number || '-'))
                        .append($('<span>', { class: 'fw-bold text-white fs-13 text-nowrap' }).text(formatCurrency(nota.amount)));
                    card.append(cardHead);

                    const table = $('<table>', { class: 'table table-sm table-hover mb-0 align-middle tx-order-table' });
                    table.append($('<thead>').append($('<tr>')
                        .append($('<th>').text('Order'))
                        .append($('<th>').text('Vendor'))
                        .append($('<th>').text('Shipment'))
                        .append($('<th>', { class: 'text-end' }).text('Nominal'))));
                    const tbody = $('<tbody>');
                    const orders = Array.isArray(nota.orders) ? nota.orders : [];

                    if (orders.length === 0) {
                        tbody.append($('<tr>')
                            .append($('<td>', { colspan: 4, class: 'text-center text-muted py-3' }).text('Tidak ada order pada nota ini.')));
                    }
                    orders.forEach(function(order) {
                        tbody.append($('<tr>')
                            .append($('<td>', { class: 'font-monospace fw-semibold' }).text(order.code || '-'))
                            .append($('<td>').text(order.vendor_name || '-'))
                            .append($('<td>', { class: 'text-muted' }).text(order.shipment_number || '-'))
                            .append($('<td>', { class: 'text-end fw-semibold text-nowrap' }).text(formatCurrency(order.amount))));
                    });
                    table.append(tbody);
                    card.append(table);
                    wrapper.append(card);
                });
                body.append(wrapper);
            }

            panel.append(hero, body);
            return panel;
        }

        const detailUrl = "{{ route('vendor.payment.detail', ':transactionKey') }}";

        // Reset status expand ketika tabel di-redraw (pindah halaman / urutkan / cari).
        paymentTable.on('draw.dt', function() {
            $openRow = null;
            openKey = null;
            activeKey = null;
        });

        $(document).on('click', '.js-toggle-detail', function() {
            const $btn = $(this);
            const $tr = $btn.closest('tr');
            const transactionKey = String($btn.data('transaction-key') || '');
            if (!transactionKey) {
                return;
            }

            // Baris yang sama sedang terbuka -> tutup.
            if ($openRow && $openRow[0] === $tr[0]) {
                collapseOpenRow();
                return;
            }

            // Tutup baris lain yang sedang terbuka (hanya satu yang boleh terbuka).
            if ($openRow) {
                collapseOpenRow();
            }

            $openRow = $tr;
            openKey = transactionKey;
            activeKey = transactionKey;
            setOpen($tr, true);

            const colspan = paymentTable.columns().header().length;
            const $child = $('<tr>', { class: 'child-row-payment' })
                .append($('<td>', { colspan: colspan })
                    .append($('<div>', { class: 'tx-panel-holder' }).append(loadingNode())));

            $tr.after($child);

            $.getJSON(detailUrl.replace(':transactionKey', encodeURIComponent(transactionKey)))
                .done(function(data) {
                    if (activeKey !== transactionKey) {
                        return;
                    }
                    const holder = $child.find('.tx-panel-holder').first();
                    if (holder.length) {
                        holder.empty().append(buildDetailPanel(data));
                    }
                })
                .fail(function(xhr) {
                    if (activeKey !== transactionKey) {
                        return;
                    }
                    const message = xhr.responseJSON && xhr.responseJSON.message
                        ? xhr.responseJSON.message
                        : 'Rincian transaksi gagal dimuat.';
                    const holder = $child.find('.tx-panel-holder').first();
                    if (holder.length) {
                        holder.empty().append($('<div>', { class: 'alert alert-danger mb-0 d-flex align-items-center gap-2' })
                            .append($('<i>', { class: 'mdi mdi-alert-circle-outline fs-18' }))
                            .append($('<span>').text(message)));
                    }
                });
        });

        // Refresh table button
        $('#btn-refresh-table').on('click', function() {
            paymentTable.ajax.reload();
        });
    });
</script>
@endpush
