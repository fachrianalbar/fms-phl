@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Bank',
    'secondSegment' => $title,
])

@push('style')
    {{-- 1. CSS library yang belum global --}}
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/libs/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/libs/datatables.net-keytable-bs5/css/keyTable.bootstrap5.min.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/libs/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/libs/datatables.net-select-bs5/css/select.bootstrap5.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/sweetalert2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom-select2.css') }}">

    {{-- 2. CSS bersama se-modul --}}
    @include('bank.partials.table-style')

    {{-- 3. CSS khusus halaman --}}
    <style>
        .table-scroll-wrap {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table-scroll-wrap .bank-table {
            min-width: 920px;
            width: 100% !important;
        }

        #dt_wrapper,
        #dt_wrapper .dataTables_scroll,
        #dt_wrapper .dataTables_scrollHead,
        #dt_wrapper .dataTables_scrollBody {
            width: 100% !important;
        }

        #dt_wrapper .dataTables_scrollHeadInner,
        #dt_wrapper .dataTables_scrollHeadInner table,
        #dt_wrapper .dataTables_scrollBody table {
            width: 100% !important;
        }
    </style>
@endpush

@section('content')
    <div class="col-sm-12">
        {{-- 4. Page Header + aksi utama --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center shadow-sm text-white"
                    style="width: 48px; height: 48px; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important;">
                    <i class="mdi mdi-bank-plus fs-24"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        {{ $title }}
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill fs-12 px-2 py-1">
                            {{ number_format($stats['totalCount'] ?? 0) }} Rekening
                        </span>
                    </h4>
                    <p class="text-muted mb-0 fs-12">
                        Master rekening bank person &amp; company (internal/external) beserta saldo berjalan.
                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm" id="btn-refresh-table" title="Muat Ulang Data Tabel">
                    <i class="mdi mdi-refresh me-1" id="refresh-icon"></i> Refresh
                </button>
                <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" onclick="openCreateModal()">
                    <i class="mdi mdi-plus me-1"></i> {{ __('general.add_data') }}
                </button>
            </div>
        </div>

        {{-- 5. KPI stat cards --}}
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card" data-filter="all" title="Klik untuk tampilkan semua rekening">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Total Rekening</div>
                            <div class="stat-value text-primary">
                                <span id="stat-total">{{ number_format($stats['totalCount'] ?? 0) }}</span>
                                <span class="fs-13 text-muted fw-normal">Rekening</span>
                            </div>
                        </div>
                        <div class="stat-icon-wrapper bg-primary-subtle text-primary">
                            <i class="mdi mdi-bank-outline"></i>
                        </div>
                    </div>
                    <div class="stat-desc text-truncate">
                        <i class="mdi mdi-wallet-outline me-1 text-primary"></i>Total Saldo: <strong id="stat-balance">Rp {{ number_format($stats['totalBalance'] ?? 0, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card" data-filter="person" title="Klik untuk filter Person">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Person</div>
                            <div class="stat-value text-info">
                                <span id="stat-person">{{ number_format($stats['personCount'] ?? 0) }}</span>
                                <span class="fs-13 text-muted fw-normal">Rekening</span>
                            </div>
                        </div>
                        <div class="stat-icon-wrapper bg-info-subtle text-info">
                            <i class="mdi mdi-account-outline"></i>
                        </div>
                    </div>
                    <div class="stat-desc text-info text-truncate">
                        <i class="mdi mdi-account-circle-outline me-1"></i>Rekening atas nama pribadi
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card" data-filter="company" title="Klik untuk filter Company">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Company</div>
                            <div class="stat-value text-warning">
                                <span id="stat-company">{{ number_format($stats['companyCount'] ?? 0) }}</span>
                                <span class="fs-13 text-muted fw-normal">Rekening</span>
                            </div>
                        </div>
                        <div class="stat-icon-wrapper bg-warning-subtle text-warning">
                            <i class="mdi mdi-domain"></i>
                        </div>
                    </div>
                    <div class="stat-desc text-warning-emphasis text-truncate">
                        <i class="mdi mdi-office-building-outline me-1"></i>Rekening atas nama perusahaan
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card" data-filter="internal" title="Klik untuk filter rekening Internal">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Internal</div>
                            <div class="stat-value text-success">
                                <span id="stat-internal">{{ number_format($stats['internalCount'] ?? 0) }}</span>
                                <span class="fs-13 text-muted fw-normal">Rekening</span>
                            </div>
                        </div>
                        <div class="stat-icon-wrapper bg-success-subtle text-success">
                            <i class="mdi mdi-home-city-outline"></i>
                        </div>
                    </div>
                    <div class="stat-desc text-success text-truncate">
                        <i class="mdi mdi-shield-check-outline me-1"></i>Rekening milik entitas sendiri
                    </div>
                </div>
            </div>
        </div>

        {{-- 6. Kartu container tabel: filter pills + tabel --}}
        <div class="table-container-card mb-4">
            <div class="table-top-bar d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <button type="button" class="filter-pill-btn active" data-status="all">
                        <i class="mdi mdi-format-list-bulleted"></i>
                        <span>Semua</span>
                        <span class="badge-pill-count" id="pill-total">{{ number_format($stats['totalCount'] ?? 0) }}</span>
                    </button>
                    <button type="button" class="filter-pill-btn" data-status="person">
                        <i class="mdi mdi-account-outline text-info"></i>
                        <span>Person</span>
                        <span class="badge-pill-count" id="pill-person">{{ number_format($stats['personCount'] ?? 0) }}</span>
                    </button>
                    <button type="button" class="filter-pill-btn" data-status="company">
                        <i class="mdi mdi-domain text-warning"></i>
                        <span>Company</span>
                        <span class="badge-pill-count" id="pill-company">{{ number_format($stats['companyCount'] ?? 0) }}</span>
                    </button>
                    <button type="button" class="filter-pill-btn" data-status="internal">
                        <i class="mdi mdi-home-city-outline text-success"></i>
                        <span>Internal</span>
                        <span class="badge-pill-count" id="pill-internal">{{ number_format($stats['internalCount'] ?? 0) }}</span>
                    </button>
                    <button type="button" class="filter-pill-btn" data-status="external">
                        <i class="mdi mdi-bank-outline text-secondary"></i>
                        <span>External</span>
                        <span class="badge-pill-count" id="pill-external">{{ number_format($stats['externalCount'] ?? 0) }}</span>
                    </button>
                </div>
            </div>
            <div class="card-body p-3">
                <div class="table-scroll-wrap custom-scrollbar">
                    <table class="table table-striped nowrap bank-table" id="dt">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 130px;">Aksi</th>
                                <th class="text-center" style="width: 45px;">No</th>
                                <th>Bank Name</th>
                                <th>Account Number</th>
                                <th>Account Name</th>
                                <th class="text-center">Type</th>
                                <th class="text-center">Rekening Type</th>
                                <th class="text-end">Saldo</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- 7. Semua modal --}}
        @include('bank.partials.modals')
    </div>
@endsection

@push('script')
    {{-- 1. Ekstensi DataTables (core sudah global) --}}
    <script src="{{ asset('assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-select/js/dataTables.select.min.js') }}"></script>

    {{-- 2. SweetAlert2 DULU, sweetalert v1 KEDUA.
         Keduanya menimpa global 'swal': v1 dimuat terakhir → window.swal = fungsi v1
         (alur tunggal), Swal = class v2 (batch/loader).
         Kalau terbalik → TypeError "class constructors must be invoked with new". --}}
    <script src="{{ asset('assets/js/sweet-alert/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>

    {{-- Flash message server → SweetAlert2 (harus setelah sweetalert2.min.js) --}}
    @include('bank.partials.flash-swal')

    {{-- 3. Select2 + helper --}}
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2-custom.js') }}"></script>
    <script src="{{ asset('assets/js/helper.js') }}"></script>

    {{-- 4. Script halaman --}}
    <script>
        // ==== STATE GLOBAL ====
        let dataTableInstance;
        let currentStatusFilter = 'all';
        let submissionInFlight = false;

        const urlStore = "{{ route($view . 'store') }}";
        const urlUpdate = "{{ route($view . 'update', '') }}".replace(/\/+$/, ''); // strip trailing slash
        const csrfToken = $('meta[name="csrf-token"]').attr('content');

        // ==== HELPER ====
        function formatNumber(value) {
            return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Math.round(Number(value) || 0));
        }

        function formatCurrency(value) {
            return 'Rp ' + formatNumber(value);
        }

        // Parse input angka format Indonesia (titik ribuan, koma desimal)
        function parseNumber(value) {
            if (value === null || value === undefined) return 0;
            let clean = String(value).trim().replace(/\./g, '').replace(',', '.');
            let n = parseFloat(clean);
            return isNaN(n) || n < 0 ? 0 : n;
        }

        // ==== MODAL ====
        function resetModal() {
            $('#userBankForm')[0].reset();
            $('#userBankForm').attr('action', urlStore);
            $('#userBankForm input[name="_method"]').remove();
            $('#userBankModalLabel').text('Tambah User Bank');
            $('#userBankModalMode').text('Tambah data');
            $('#userBankModalDescription').text('Tambahkan rekening baru untuk digunakan dalam transaksi perusahaan.');
            $('#balanceField').show();
            $('#balance').prop('required', true).val('');
            $('#bankCode').val('').trigger('change');
            $('#type').val('').trigger('change');
            $('#rekening_type').val('external').trigger('change');
            $('#userBankSubmitBtn').prop('disabled', false).html('<i class="mdi mdi-content-save-outline me-1"></i>{{ __('general.save') }}');
        }

        function openCreateModal() {
            resetModal();
            $('#userBankModal').modal('show');
        }

        // Edit: tombol di dalam tabel punya data-* (data-id, data-bankcode, ...)
        $(document).on('click', '.btn-open-edit', function() {
            const data = $(this).data();

            resetModal();
            $('#userBankModalLabel').text('Edit User Bank');
            $('#userBankModalMode').text('Perbarui data');
            $('#userBankModalDescription').text('Perbarui identitas dan klasifikasi rekening yang dipilih.');
            $('#userBankForm').attr('action', urlUpdate + '/' + data.id);
            $('#userBankForm').append($('<input>').attr({ type: 'hidden', name: '_method', value: 'PUT' }));
            $('#bankCode').val(data.bankcode).trigger('change');
            $('#accountNumber').val(data.accountnumber);
            $('#accountName').val(data.accountname);
            $('#type').val(String(data.type)).trigger('change');
            $('#rekening_type').val(String(data.rekeningtype).toLowerCase()).trigger('change');
            $('#balanceField').hide();
            $('#balance').prop('required', false).val('');
            $('#userBankModal').modal('show');
        });

        $('#userBankModal').on('hidden.bs.modal', function() {
            resetModal();
        });

        // ==== FORM & AJAX ====
        $('#userBankForm').on('submit', function(e) {
            e.preventDefault();

            // Anti double-submit
            if (submissionInFlight) return;
            submissionInFlight = true;
            $('#userBankSubmitBtn').prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...'
            );

            const form = $(this);

            // Hilangkan pemisah ribuan pada input saldo sebelum dikirim
            const balanceField = $('#balance');
            const balanceRaw = balanceField.val();
            balanceField.val(String(balanceRaw).replace(/\./g, '').replace(/,/g, '.'));

            $.ajax({
                type: form.find('input[name="_method"]').val() === 'PUT' ? 'PUT' : 'POST',
                url: form.attr('action'),
                data: form.serialize(),
                success: function(response) {
                    submissionInFlight = false;
                    $('#userBankModal').modal('hide');
                    form[0].reset();
                    dataTableInstance.ajax.reload(null, false);
                    reloadStats();
                    swal({ title: "Berhasil!", text: response.message || "Data disimpan.", icon: "success", button: "OK" });
                },
                error: function(xhr) {
                    submissionInFlight = false;
                    let msg = 'Terjadi kesalahan saat menyimpan.';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    $('#userBankSubmitBtn').prop('disabled', false).html('<i class="mdi mdi-content-save-outline me-1"></i>{{ __('general.save') }}');
                    swal({ title: "Gagal!", text: msg, icon: "error", button: "OK" });
                }
            });
        });

        // ==== DELETE (konfirmasi destruktif, SweetAlert2) ====
        $(document).on('click', '.btn-open-delete', function() {
            const data = $(this).data();
            const url = urlUpdate + '/' + data.id;

            Swal.fire({
                title: "{{ __('general.are_you_sure') }}",
                html: "{{ __('general.want_to_delete_this_data') }}<br><strong>" + escapeHtml(data.accountname || '') + "</strong>",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Lanjutkan',
                cancelButtonText: 'Kembali',
                confirmButtonColor: '#dc3545',
            }).then(function(result) {
                if (!result.isConfirmed) return;

                $.ajax({
                    type: 'DELETE',
                    url: url,
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    success: function(response) {
                        dataTableInstance.ajax.reload(null, false);
                        reloadStats();
                        swal({ title: "Berhasil!", text: response.message || "Data dihapus.", icon: "success", button: "OK" });
                    },
                    error: function(xhr) {
                        let msg = 'Terjadi kesalahan saat menghapus.';
                        if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                        swal({ title: "Gagal!", text: msg, icon: "error", button: "OK" });
                    }
                });
            });
        });

        // ==== KPI & PILL (stat server direfresh setelah operasi CRUD) ====
        function reloadStats() {
            $.getJSON("{{ route('ajax.user-bank.stats') }}", function(stats) {
                $('#stat-total').text(formatNumber(stats.totalCount ?? 0));
                $('#stat-person').text(formatNumber(stats.personCount ?? 0));
                $('#stat-company').text(formatNumber(stats.companyCount ?? 0));
                $('#stat-internal').text(formatNumber(stats.internalCount ?? 0));
                $('#stat-balance').text(formatCurrency(stats.totalBalance ?? 0));
                $('#pill-total').text(formatNumber(stats.totalCount ?? 0));
                $('#pill-person').text(formatNumber(stats.personCount ?? 0));
                $('#pill-company').text(formatNumber(stats.companyCount ?? 0));
                $('#pill-internal').text(formatNumber(stats.internalCount ?? 0));
                $('#pill-external').text(formatNumber(stats.externalCount ?? 0));
            });
        }

        // Escape HTML sebelum menyisipkan data server ke DOM
        function escapeHtml(value) {
            return String(value || '').replace(/[&<>"']/g, function(c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
            });
        }

        // ==== DATATABLE ====
        $(document).ready(function() {
            dataTableInstance = $('#dt').DataTable({
                "processing": true,
                "serverSide": true,
                "destroy": true,
                "scrollX": true,
                "autoWidth": false,
                "pageLength": 25,
                "ajax": {
                    "url": "{{ route('dt.bank-user') }}",
                    "data": function(d) {
                        d.status = currentStatusFilter;
                    }
                },
                "columns": [
                    { "data": "action", "className": "text-center align-middle", "orderable": false, "searchable": false },
                    { "data": "DT_RowIndex", "className": "text-center align-middle", "orderable": false, "searchable": false },
                    { "data": "bank_name", "className": "align-middle" },
                    { "data": "accountNumber", "className": "align-middle" },
                    { "data": "accountName", "className": "align-middle" },
                    { "data": "type", "className": "text-center align-middle" },
                    { "data": "rekening_type", "className": "text-center align-middle" },
                    { "data": "balance", "className": "text-end align-middle" },
                ],
                "order": [
                    [2, 'asc']
                ],
                "drawCallback": function() {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                        [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                            .map(function(el) { return new bootstrap.Tooltip(el); });
                    }
                },
                "language": {
                    "processing": "Memuat data...",
                    "search": "",
                    "searchPlaceholder": "Cari bank, no. rekening, nama rekening...",
                    "lengthMenu": "Tampilkan _MENU_ data",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    "infoEmpty": "Tidak ada data",
                    "zeroRecords": "Tidak ditemukan data yang sesuai",
                    "paginate": {
                        "next": "<i class='mdi mdi-chevron-right'></i>",
                        "previous": "<i class='mdi mdi-chevron-left'></i>"
                    }
                }
            });

            // Filter pill click
            $('.filter-pill-btn').on('click', function() {
                $('.filter-pill-btn').removeClass('active');
                $(this).addClass('active');

                currentStatusFilter = $(this).data('status');
                dataTableInstance.ajax.reload();
            });

            // KPI stat card click (filter trigger)
            $('.stat-card').on('click', function() {
                let filter = $(this).data('filter');
                if (filter) {
                    $('.filter-pill-btn').removeClass('active');
                    $('.filter-pill-btn[data-status="' + filter + '"]').addClass('active');
                    currentStatusFilter = filter;
                    dataTableInstance.ajax.reload();
                }
            });

            // Refresh button
            $('#btn-refresh-table').on('click', function() {
                let icon = $('#refresh-icon');
                icon.addClass('mdi-spin');
                dataTableInstance.ajax.reload(function() {
                    setTimeout(() => icon.removeClass('mdi-spin'), 400);
                });
                reloadStats();
            });

            // Select2 di dalam modal WAJIB dropdownParent ke modal-nya
            $('#userBankModal .js-example-basic-single').select2({
                dropdownParent: $('#userBankModal'),
                width: '100%',
            });

            // Select2 untuk dropdown "Tampilkan _MENU_ data" milik DataTables
            $('#dt_wrapper .dataTables_length select').select2({
                minimumResultsForSearch: Infinity,
                width: '88px',
                dropdownAutoWidth: true,
            });
        });
    </script>
@endpush
