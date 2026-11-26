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
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/select2.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom-select2.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}">
        @include('master.partials.table-style')
@endpush

@section('content')
        <div class="col-sm-12">
            <div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
                {{-- Card Header --}}
                <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2"
                    style="border-color: #e2e8f0;">
                    <div>
                        <h4 class="mb-1 fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="mdi mdi-calculator-variant-outline text-primary fs-20"></i>
                            {{ $title }} Data
                        </h4>
                        <small class="text-muted">Daftar master komponen biaya operasional armada</small>
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <a href="{{ route('master.cost-component.export-excel') }}" target="_blank" id="export-excel"
                            class="btn btn-icon btn-sm bg-success-subtle" data-bs-toggle="tooltip" title="Export Excel">
                            <i class="mdi mdi-file-excel fs-14 text-success"></i>
                        </a>
                        <a href="{{ route('master.cost-component.export-pdf') }}" target="_blank" id="export-pdf"
                            class="btn btn-icon btn-sm bg-danger-subtle" data-bs-toggle="tooltip" title="Export PDF">
                            <i class="mdi mdi-file-pdf-box fs-14 text-danger"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-warning d-inline-flex align-items-center gap-1"
                            id="btnBulkUpdatePrice" title="Penyesuaian Harga"
                            style="border-radius: 8px; font-weight: 600; padding: 7px 14px;">
                            <i class="mdi mdi-cash-multiple"></i> Penyesuaian Harga
                        </button>
                        <a href="{{ route($view . 'create') }}"
                            class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1"
                            style="border-radius: 8px; font-weight: 600; padding: 7px 14px;">
                            <i class="mdi mdi-plus"></i> {{ __('general.add_data') }}
                        </a>
                    </div>
                </div>

                <div class="card-body p-4">
                    @include('partials.alert')

                    {{-- Filter Bar (§3 PHL, tertutup default) --}}
                    <div class="filter-card">
                        <button type="button" class="filter-card-header" data-bs-toggle="collapse"
                            data-bs-target="#costComponentFilterCollapse" aria-expanded="false"
                            aria-controls="costComponentFilterCollapse">
                            <span class="filter-card-heading">
                                <i class="mdi mdi-filter-variant"></i>
                                <strong>Filter Data</strong>
                                <small>Gunakan filter nama/kode, tipe komponen, atau tanggal dibuat</small>
                            </span>
                            <i class="mdi mdi-chevron-down filter-card-chevron"></i>
                        </button>

                        <div class="collapse filter-collapse" id="costComponentFilterCollapse">
                            <div class="filter-collapse-body">
                                <div id="filterForm">
                                    <div class="row g-3">
                                        <div class="col-xl-3 col-md-6">
                                            <label class="filter-label" for="keyword">Kode / Nama Komponen</label>
                                            <input class="form-control" name="keyword" id="keyword" type="text"
                                                placeholder="Cari kode atau nama komponen...">
                                        </div>
                                        <div class="col-xl-3 col-md-6">
                                            <label class="filter-label" for="type">Tipe Komponen</label>
                                            <select class="form-select select2-filter" name="type" id="type">
                                                <option value="">Semua Tipe</option>
                                                @foreach ($types as $t)
                                                    <option value="{{ $t->value }}">{{ ucfirst($t->value) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-xl-3 col-md-6">
                                            <label class="filter-label" for="startDate">Dari Tanggal</label>
                                            <input class="form-control filter-control" name="startDate" id="startDate"
                                                type="text" placeholder="Pilih tanggal mulai">
                                        </div>
                                        <div class="col-xl-3 col-md-6">
                                            <label class="filter-label" for="endDate">Sampai Tanggal</label>
                                            <input class="form-control filter-control" name="endDate" id="endDate"
                                                type="text" placeholder="Pilih tanggal akhir">
                                        </div>
                                        <div class="col-12 d-flex align-items-end justify-content-end gap-2">
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

                    <div class="table-responsive custom-scrollbar">
                        <table class="table align-middle w-100 mb-0" id="dt">
                            <thead>
                                <tr>
                                    <th style="width: 20px;"><input type="checkbox" class="form-check-input" id="checkAll"></th>
                                    <th class="text-center" style="width: 80px;">#</th>
                                    <th class="text-center" style="width: 60px;">No</th>
                                    <th>Nama Komponen</th>
                                    <th class="text-end">Harga</th>
                                    <th class="text-center">Tipe</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <form id="delete-form" method="post">
            @csrf
            @method('DELETE')
        </form>

        <!-- Modal Bulk Update Price -->
        <div class="modal fade" id="modalBulkUpdatePrice" tabindex="-1" aria-labelledby="modalBulkUpdatePriceLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalBulkUpdatePriceLabel">Penyesuaian Harga Masal</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formBulkUpdatePrice">
                            <div class="mb-3">
                                <label class="form-label">Tipe Penyesuaian</label>
                                <select class="form-select" id="bulkUpdateType" name="type" required>
                                    <option value="increase">Kenaikan</option>
                                    <option value="decrease">Penurunan</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Persentase (%)</label>
                                <input type="number" class="form-control" id="bulkUpdatePercentage" name="percentage" min="0.01" step="0.01" required placeholder="Contoh: 10">
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" id="btnSubmitBulkUpdate">Simpan Perubahan</button>
                    </div>
                </div>
            </div>
        </div>
@endsection

@push('script')
        <script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
        <script src="{{ asset('assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
        <script src="{{ asset('assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js') }}"></script>
        <script src="{{ asset('assets/libs/datatables.net-keytable-bs5/js/keyTable.bootstrap5.min.js') }}"></script>
        <script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
        <script src="{{ asset('assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>
        <script src="{{ asset('assets/libs/datatables.net-select/js/dataTables.select.min.js') }}"></script>
        <script src="{{ asset('assets/libs/datatables.net-select-bs5/js/select.bootstrap5.min.js') }}"></script>
        <script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>
        <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
        <script src="{{ asset('assets/js/select2/select2-custom.js') }}"></script>
        <script src="{{ asset('assets/libs/flatpickr/flatpickr.min.js') }}"></script>

        <script>
            const ROUTES = {
                costComponentIndex: @json(route('master.cost-component.index')),
                dtCostComponent: @json(route('dt.cost-component')),
                exportExcel: @json(route('master.cost-component.export-excel')),
                exportPdf: @json(route('master.cost-component.export-pdf')),
                bulkUpdatePrice: @json(route('ajax.cost-component.bulk-update-price'))
            };

            $(document).ready(function() {
                let filterStartPicker;
                let filterEndPicker;

                $('.select2-filter').select2({
                    placeholder: 'Semua Tipe',
                    allowClear: true,
                    width: '100%'
                });

                filterStartPicker = flatpickr('#startDate', {
                    dateFormat: 'Y-m-d',
                    allowInput: true,
                    onChange: function(selectedDates, dateStr) {
                        if (filterEndPicker) filterEndPicker.set('minDate', dateStr || null);
                    }
                });

                filterEndPicker = flatpickr('#endDate', {
                    dateFormat: 'Y-m-d',
                    allowInput: true,
                    onChange: function(selectedDates, dateStr) {
                        if (filterStartPicker) filterStartPicker.set('maxDate', dateStr || null);
                    }
                });

                function getFilters() {
                    return {
                        keyword: $('#keyword').val() || '',
                        type: $('#type').val() || '',
                        startDate: $('#startDate').val() || '',
                        endDate: $('#endDate').val() || ''
                    };
                }

                function syncExportUrls() {
                    const params = new URLSearchParams();
                    Object.entries(getFilters()).forEach(function(entry) {
                        if (entry[1]) params.set(entry[0], entry[1]);
                    });
                    const query = params.toString() ? '?' + params.toString() : '';
                    $('#export-excel').attr('href', ROUTES.exportExcel + query);
                    $('#export-pdf').attr('href', ROUTES.exportPdf + query);
                }

                const table = $('#dt').DataTable({
                    "processing": true,
                    "serverSide": true,
                    "destroy": true,
                    "ajax": {
                        "url": ROUTES.dtCostComponent,
                        "data": function(d) {
                            Object.assign(d, getFilters());
                        }
                    },
                    "columns": [
                        { "data": 'checkbox', "className": 'text-center align-middle', "orderable": false, "searchable": false },
                        { "data": 'action', "className": 'text-center align-middle' },
                        { "data": 'DT_RowIndex', "className": 'text-center align-middle' },
                        { "data": 'name', "className": 'align-middle fw-semibold text-dark' },
                        { "data": 'formatted_price', "className": 'text-end align-middle font-monospace fw-bold' },
                        {
                            "data": 'type',
                            "className": 'text-center align-middle',
                            "render": function(data) {
                                return data ? data.charAt(0).toUpperCase() + data.slice(1) : '-';
                            }
                        }
                    ],
                    "columnDefs": [
                        { "searchable": false, "targets": [0, 1, 2] },
                        { "orderable": false, "targets": [0, 1] }
                    ],
                    "order": [[3, 'asc']]
                });

                function reloadWithFilters() {
                    syncExportUrls();
                    table.ajax.reload(null, true);
                }

                $('#btnFilter').on('click', reloadWithFilters);
                $('#keyword').on('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        reloadWithFilters();
                    }
                });
                $('#btnResetFilter').on('click', function() {
                    $('#filterForm').find('input').val('');
                    $('.select2-filter').val('').trigger('change');
                    if (filterStartPicker) {
                        filterStartPicker.clear();
                        filterStartPicker.set('maxDate', null);
                    }
                    if (filterEndPicker) {
                        filterEndPicker.clear();
                        filterEndPicker.set('minDate', null);
                    }
                    reloadWithFilters();
                });

                syncExportUrls();

                // Handle Check All
                $('#checkAll').on('click', function() {
                    $('.component-checkbox').prop('checked', this.checked);
                });

                // Handle individual checkbox change
                $('#dt tbody').on('change', '.component-checkbox', function() {
                    if (!this.checked) {
                        $('#checkAll').prop('checked', false);
                    } else if ($('.component-checkbox:checked').length === $('.component-checkbox').length) {
                        $('#checkAll').prop('checked', true);
                    }
                });

                // Handle Bulk Update Button Click
                $('#btnBulkUpdatePrice').click(function() {
                    var selectedIds = [];
                    $('.component-checkbox:checked').each(function() {
                        selectedIds.push($(this).val());
                    });

                    if (selectedIds.length === 0) {
                        swal("Peringatan", "Pilih minimal satu komponen biaya untuk menyesuaikan harga.", "warning");
                        return;
                    }

                    $('#formBulkUpdatePrice')[0].reset();

                    $('#bulkUpdateType').select2({
                        dropdownParent: $('#modalBulkUpdatePrice'),
                        width: '100%',
                        minimumResultsForSearch: Infinity
                    });

                    $('#modalBulkUpdatePrice').modal('show');
                });

                // Handle Submit Bulk Update
                $('#btnSubmitBulkUpdate').click(function() {
                    var selectedIds = [];
                    $('.component-checkbox:checked').each(function() {
                        selectedIds.push($(this).val());
                    });

                    var type = $('#bulkUpdateType').val();
                    var percentage = $('#bulkUpdatePercentage').val();

                    if (!percentage || percentage <= 0) {
                        swal("Peringatan", "Masukkan persentase yang valid.", "warning");
                        return;
                    }

                    if (type === 'decrease' && percentage > 100) {
                        swal("Peringatan", "Persentase penurunan tidak boleh lebih dari 100% agar harga tidak menjadi negatif.", "warning");
                        return;
                    }

                    $(this).prop('disabled', true).text('Menyimpan...');

                    $.ajax({
                        url: ROUTES.bulkUpdatePrice,
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            ids: selectedIds,
                            type: type,
                            percentage: percentage
                        },
                        success: function(response) {
                            $('#btnSubmitBulkUpdate').prop('disabled', false).text('Simpan Perubahan');
                            if (response.success) {
                                $('#modalBulkUpdatePrice').modal('hide');
                                swal("Berhasil", response.message, "success");
                                table.ajax.reload(null, false);
                                $('#checkAll').prop('checked', false);
                            } else {
                                swal("Gagal", response.message, "error");
                            }
                        },
                        error: function() {
                            $('#btnSubmitBulkUpdate').prop('disabled', false).text('Simpan Perubahan');
                            swal("Error", "Terjadi kesalahan sistem.", "error");
                        }
                    });
                });
            });

            function deleteData(uuid) {
                var url = ROUTES.costComponentIndex + '/' + uuid;
                $('#delete-form').attr('action', url);

                swal({
                    title: "{{ __('general.are_you_sure') }}",
                    text: "{{ __('general.want_to_delete_this_data') }}",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        $('#delete-form').submit();
                    } else {
                        swal("{{ __('general.your_data_is_save') }}");
                    }
                });
            }
        </script>
@endpush
