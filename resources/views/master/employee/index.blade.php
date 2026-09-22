@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => 'Master',
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
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/libs/datatables.net-select-bs5/css/select.bootstrap5.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/sweetalert2.css') }}">

    <style>
        /* ===== Custom Card & Header ===== */
        .master-card {
            border: 1px solid #e8ecf3;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(30, 41, 59, 0.04);
            background: #fff;
            overflow: hidden;
            transition: box-shadow 0.2s ease;
        }
        .master-card:hover {
            box-shadow: 0 4px 16px rgba(30, 41, 59, 0.07);
        }
        .master-card .card-header {
            background: #fbfcfe;
            border-bottom: 1px solid #edf1f7;
            padding: 1.1rem 1.4rem;
        }
        .master-card .card-body {
            padding: 1.4rem;
        }

        .header-icon-employee {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            flex-shrink: 0;
            background: linear-gradient(135deg, #7669D3 0%, #5a4db8 100%);
            color: #fff;
            box-shadow: 0 3px 8px rgba(118, 105, 211, 0.3);
        }

        /* ===== Stat Metric Cards ===== */
        .metric-card {
            border: 1px solid #e8ecf3;
            border-radius: 10px;
            background: #ffffff;
            padding: 0.9rem 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.85rem;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.03);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .metric-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
        }
        .metric-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }
        .metric-icon-total { background: #f1f5f9; color: #475569; }
        .metric-icon-driver { background: #eef2ff; color: #4f46e5; }
        .metric-icon-active { background: #ecfdf5; color: #059669; }
        .metric-icon-inactive { background: #fef2f2; color: #dc2626; }
        .metric-value {
            font-size: 1.35rem;
            font-weight: 800;
            line-height: 1.2;
            color: #1e293b;
        }
        .metric-label {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
        }

        /* ===== Filter Bar ===== */
        .filter-panel {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.85rem 1.1rem;
            margin-bottom: 1.25rem;
        }
        .filter-label {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 0.3rem;
        }
        .filter-select {
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.85rem;
            padding: 0.45rem 0.75rem;
            background-color: #fff;
            transition: all 0.2s ease;
        }
        .filter-select:focus {
            border-color: #7669D3;
            box-shadow: 0 0 0 3px rgba(118, 105, 211, 0.14);
        }

        /* ===== Modern Table Styling ===== */
        .employee-table-wrapper {
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #e8ecf3;
            background: #fff;
        }
        table.dataTable {
            margin-top: 0 !important;
            margin-bottom: 0 !important;
            border-collapse: collapse !important;
        }
        table.dataTable thead th {
            background: linear-gradient(135deg, #f8faff 0%, #eef2f9 100%) !important;
            border-bottom: 2px solid #dce3ed !important;
            font-size: 0.76rem !important;
            font-weight: 700 !important;
            color: #475569 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            padding: 0.75rem 0.8rem !important;
            vertical-align: middle !important;
        }
        table.dataTable tbody tr {
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.15s ease;
        }
        table.dataTable tbody tr:hover {
            background: #f9fbff !important;
        }
        table.dataTable tbody td {
            padding: 0.65rem 0.8rem !important;
            vertical-align: middle !important;
            font-size: 0.88rem !important;
        }

        /* Status Toggle Switch Styling */
        .form-check-input.status-toggle-switch {
            width: 2.3em;
            height: 1.25em;
            cursor: pointer;
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }
        .form-check-input.status-toggle-switch:checked {
            background-color: #10b981;
            border-color: #10b981;
        }

        /* Action Buttons */
        .btn-add-employee {
            background: linear-gradient(135deg, #7669D3 0%, #5a4db8 100%);
            border: none;
            color: #fff;
            font-weight: 700;
            font-size: 0.86rem;
            padding: 0.5rem 1.15rem;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(118, 105, 211, 0.3);
            text-decoration: none;
        }
        .btn-add-employee:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(118, 105, 211, 0.4);
            color: #fff;
        }
    </style>
@endpush

@section('content')
    <div class="col-sm-12">

        <!-- Stat Metric Cards -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="metric-card">
                    <div class="metric-icon metric-icon-total">
                        <i class="mdi mdi-account-multiple-outline"></i>
                    </div>
                    <div>
                        <div class="metric-value">{{ $counts['total'] ?? 0 }}</div>
                        <div class="metric-label">Total Karyawan</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="metric-card">
                    <div class="metric-icon metric-icon-driver">
                        <i class="mdi mdi-steering"></i>
                    </div>
                    <div>
                        <div class="metric-value">{{ $counts['driver'] ?? 0 }}</div>
                        <div class="metric-label">Total Supir / Driver</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="metric-card">
                    <div class="metric-icon metric-icon-active">
                        <i class="mdi mdi-account-check-outline"></i>
                    </div>
                    <div>
                        <div class="metric-value text-success" id="metric-active-count">{{ $counts['driver_active'] ?? 0 }}</div>
                        <div class="metric-label text-success">Supir Aktif</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="metric-card">
                    <div class="metric-icon metric-icon-inactive">
                        <i class="mdi mdi-account-off-outline"></i>
                    </div>
                    <div>
                        <div class="metric-value text-danger" id="metric-inactive-count">{{ $counts['driver_inactive'] ?? 0 }}</div>
                        <div class="metric-label text-danger">Supir Nonaktif</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Card -->
        <div class="card master-card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3">
                    <span class="header-icon-employee">
                        <i class="mdi mdi-account-group-outline"></i>
                    </span>
                    <div>
                        <h5 class="m-0 fw-bold text-dark">{{ $title }} Data</h5>
                        <small class="text-muted">Kelola data karyawan serta status ketersediaan supir armada</small>
                    </div>
                </div>

                <a href="{{ route($view . 'create') }}" class="btn-add-employee">
                    <i class="mdi mdi-plus-circle-outline fs-16"></i>
                    {{ __('general.add_data') }}
                </a>
            </div>

            <div class="card-body">
                @include('partials.alert')

                <!-- Filter Toolbar -->
                <div class="filter-panel">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4 col-sm-6">
                            <label class="filter-label" for="filter_position">
                                <i class="mdi mdi-briefcase-outline me-1"></i>Filter Jabatan / Posisi
                            </label>
                            <select class="form-select filter-select" id="filter_position">
                                <option value="">Semua Posisi</option>
                                @foreach ($positions as $pos)
                                    <option value="{{ $pos->code }}">{{ $pos->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 col-sm-6">
                            <label class="filter-label" for="filter_status">
                                <i class="mdi mdi-toggle-switch-outline me-1"></i>Filter Status
                            </label>
                            <select class="form-select filter-select" id="filter_status">
                                <option value="">Semua Status (Aktif & Nonaktif)</option>
                                <option value="1">Aktif Saja</option>
                                <option value="0">Nonaktif Saja</option>
                            </select>
                        </div>

                        <div class="col-md-4 col-sm-12 text-md-end text-start mt-2 mt-md-0">
                            <button type="button" class="btn btn-sm btn-outline-secondary px-3 py-2 rounded-2" id="btn-reset-filter">
                                <i class="mdi mdi-refresh me-1"></i>Reset Filter
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Modern Table Container -->
                <div class="employee-table-wrapper">
                    <div class="table-responsive custom-scrollbar">
                        <table class="table table-hover w-100 align-middle" id="dt">
                            <thead>
                                <tr>
                                    <th style="width: 105px;" class="text-center">Aksi</th>
                                    <th style="width: 50px;" class="text-center">No</th>
                                    <th style="width: 130px;">Kode</th>
                                    <th>Nama & Kontak</th>
                                    <th style="width: 150px;" class="text-center">Jabatan</th>
                                    <th style="width: 155px;" class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Hidden Delete Form -->
    <form id="delete-form" method="post">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('script')
    <script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>

    <script>
        let table;

        $(document).ready(function() {
            table = $('#dt').DataTable({
                processing: true,
                serverSide: true,
                destroy: true,
                ajax: {
                    url: "{{ route('dt.employee') }}",
                    data: function(d) {
                        d.positionCode = $('#filter_position').val();
                        d.status = $('#filter_status').val();
                    }
                },
                columns: [
                    { data: 'action', className: 'text-center' },
                    { data: 'DT_RowIndex', className: 'text-center fw-bold text-muted fs-12' },
                    { data: 'code' },
                    { data: 'name' },
                    { data: 'position_badge', className: 'text-center' },
                    { data: 'status_badge', className: 'text-center' },
                ],
                columnDefs: [
                    { searchable: false, orderable: false, targets: [0, 1, 4, 5] }
                ],
                order: [
                    [3, 'asc']
                ],
                drawCallback: function() {
                    // Re-initialize Bootstrap tooltips if available
                    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                        tooltipTriggerList.map(function(tooltipTriggerEl) {
                            return new bootstrap.Tooltip(tooltipTriggerEl);
                        });
                    }
                }
            });

            // Filter on change
            $('#filter_position, #filter_status').on('change', function() {
                table.ajax.reload();
            });

            // Reset Filter
            $('#btn-reset-filter').on('click', function() {
                $('#filter_position').val('');
                $('#filter_status').val('');
                table.ajax.reload();
            });

            // Event delegation for toggle switch clicks
            $(document).on('change', '.status-toggle-switch', function(e) {
                const switchElem = $(this);
                const id = switchElem.data('id');
                const name = switchElem.data('name');
                const currentStatus = parseInt(switchElem.data('status'));

                // Prevent instant toggle state change until confirmed
                const intendedCheck = switchElem.is(':checked');
                switchElem.prop('checked', !intendedCheck);

                triggerToggleStatus(id, name, currentStatus);
            });
        });

        // Toggle Status via Button or Switch with Confirmation
        function toggleStatus(id, name, currentStatus) {
            triggerToggleStatus(id, name, currentStatus);
        }

        function triggerToggleStatus(id, name, currentStatus) {
            const isCurrentlyActive = (parseInt(currentStatus) === 1);
            const actionText = isCurrentlyActive ? 'nonaktifkan' : 'aktifkan';
            const confirmTitle = isCurrentlyActive ? `Nonaktifkan Supir ${name}?` : `Aktifkan Supir ${name}?`;
            const confirmText = isCurrentlyActive
                ? 'Supir yang nonaktif tidak akan dapat dipilih pada penugasan armada dan order operasional.'
                : 'Supir akan kembali aktif dan tersedia untuk penugasan order dan armada.';

            swal({
                title: confirmTitle,
                text: confirmText,
                icon: "warning",
                buttons: {
                    cancel: {
                        text: "Batal",
                        visible: true,
                        className: "btn-secondary"
                    },
                    confirm: {
                        text: isCurrentlyActive ? "Ya, Nonaktifkan" : "Ya, Aktifkan",
                        visible: true,
                        className: isCurrentlyActive ? "btn-danger" : "btn-primary"
                    }
                },
                dangerMode: isCurrentlyActive,
            }).then((willChange) => {
                if (willChange) {
                    $.ajax({
                        url: '{{ url("master/employee") }}/' + id + '/toggle-status',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                swal({
                                    title: "Berhasil!",
                                    text: response.message,
                                    icon: "success",
                                    timer: 2000,
                                    buttons: false
                                });
                                table.ajax.reload(null, false); // reload without resetting pagination
                            } else {
                                swal({
                                    title: "Peringatan",
                                    text: response.message,
                                    icon: "warning"
                                });
                            }
                        },
                        error: function(xhr) {
                            const msg = (xhr.responseJSON && xhr.responseJSON.message)
                                ? xhr.responseJSON.message
                                : "Terjadi kesalahan saat mengubah status supir.";
                            swal({
                                title: "Error",
                                text: msg,
                                icon: "error"
                            });
                        }
                    });
                }
            });
        }

        function deleteData(uuid) {
            var url = '{{ route("master.employee.index") }}/' + uuid;
            $('#delete-form').attr('action', url);

            swal({
                title: "{{ __('general.are_you_sure') }}",
                text: "{{ __('general.want_to_delete_this_data') }}",
                icon: "warning",
                buttons: {
                    cancel: "Batal",
                    confirm: {
                        text: "Ya, Hapus",
                        className: "btn-danger"
                    }
                },
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    $('#delete-form').submit();
                }
            });
        }
    </script>
@endpush
