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
@endpush

@section('content')
<div class="col-sm-12">
    <form class="card" method="POST" action="{{ route('operational.return-do.cancel-do') }}">
        @csrf
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>{{ $title }} Data</h4>

            {{-- <button type="submit" class="btn btn-primary" id="saveOrder">
                    {{ __('menu_return_do.cancel_return') }}
            </button> --}}

        </div>
        <div class="card-body">
            @include('partials.alert')
            <div class="table-responsive custom-scrollbar">
                <table class="table table-striped w-100 nowrap" id="dt">
                    <thead>
                        <tr>
                            <th>Detail</th>
                            <th>{{ __('menu_return_do.action') }}</th>
                            <th>{{ __('menu_return_do.no') }}</th>
                            <th>{{ __('menu_return_do.order_date') }}</th>
                            <th>{{ __('menu_return_do.shipment_no') }}</th>
                            <th>{{ __('menu_return_do.customer_name') }}</th>
                            <th>{{ __('menu_return_do.origin') }}</th>
                            <th>{{ __('menu_return_do.destination') }}</th>
                            <th>{{ __('menu_return_do.fleet') }}</th>
                            <th>{{ __('menu_return_do.driver') }}</th>
                            <th>{{ __('menu_return_do.return_date') }}</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </form>
</div>

<!-- Modal Detail On Charge Cost -->
<div class="modal fade" id="modalDetailCost" tabindex="-1" aria-labelledby="modalDetailCostLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetailCostLabel">Detail Biaya On Charge</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Shipment Number:</strong> <span id="modalShipmentNumber"></span></p>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Komponen Biaya</th>
                                <th>Nominal</th>
                            </tr>
                        </thead>
                        <tbody id="costTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Gallery Surat Jalan -->
<div class="modal fade" id="modalFileGallery" tabindex="-1" aria-labelledby="modalFileGalleryLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalFileGalleryLabel">
                    <i class="mdi mdi-file-image me-2"></i>Gallery Surat Jalan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Order Code:</strong> <span id="galleryOrderCode"></span></p>
                <div id="fileGalleryContainer" class="row g-3">
                    <!-- Files will be loaded here -->
                </div>
                <div id="galleryLoading" class="text-center py-5" style="display: none;">
                    <div class="spinner-border text-info" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Memuat file...</p>
                </div>
                <div id="galleryEmpty" class="text-center py-5" style="display: none;">
                    <i class="mdi mdi-file-alert" style="font-size: 48px; color: #ccc;"></i>
                    <p class="text-muted mt-2">Tidak ada file</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>

<!-- dataTables.bootstrap5 -->
<script src="{{ asset('assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>

<!-- dataTables.keyTable -->
<script src="{{ asset('assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-keytable-bs5/js/keyTable.bootstrap5.min.js') }}"></script>

<!-- dataTable.responsive -->
<script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>

<!-- dataTables.select -->
<script src="{{ asset('assets/libs/datatables.net-select/js/dataTables.select.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-select-bs5/js/select.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>
{{-- <script src="../assets/js/sweet-alert/app.js"></script> --}}

<script>
    $(document).ready(function() {
        $('#dt').DataTable({
            "processing": true,
            "serverSide": true,
            "destroy": true,
            "ajax": {
                "url": "{{ route('dt.return-do') }}",
            },
            "columns": [{
                    "data": 'detail'
                },
                {
                    "data": 'action'
                },
                {
                    "data": 'DT_RowIndex'
                },
                {
                    "data": 'orderDate'
                },
                {
                    "data": 'shipmentNumber'
                },
                {
                    "data": 'customer.name'
                },
                {
                    "data": 'route.originLocation.name'
                },
                {
                    "data": 'route.destinationLocation.name'
                },
                {
                    "data": 'fleet.plateNumber'
                },
                {
                    "data": 'driver.name'
                },
                {
                    "data": 'returnDate'
                }
            ],
            "columnDefs": [{
                    "searchable": false,
                    "targets": [0, 1, 2]
                },
                {
                    "orderable": false,
                    "targets": [0, 1, 2]
                }
            ],
            "order": [
                [3, 'asc']
            ]
        })
    });
    let selectedOrders = [];

    // Guard: only attach listener if the button exists (it may be commented out in the view)
    const saveOrderBtn = document.getElementById('saveOrder');
    if (saveOrderBtn) {
        saveOrderBtn.addEventListener('click', function(event) {
            // Get all checkboxes
            if (selectedOrders.length === 0) {
                event.preventDefault();
                swal({
                    title: "{{ __('general.warning') }}",
                    text: "Please select at least one item",
                    icon: "warning",
                });
                return;
            }

            // Tambahkan array ke form
            $('<input>').attr({
                type: 'hidden',
                name: 'selectedOrders',
                value: JSON.stringify(selectedOrders)
            }).appendTo('form');
        });
    }

    $(document).ready(function() {
        // Event handler untuk checkbox
        $(document).on('change', '.order-checkbox', function() {
            const orderId = $(this).val();
            if ($(this).is(':checked')) {
                if (!selectedOrders.includes(orderId)) {
                    selectedOrders.push(orderId);
                }
            } else {
                selectedOrders = selectedOrders.filter(id => id !== orderId);
            }

        });

        // Simpan state saat DataTable di-reload (misalnya saat pindah halaman)
        $('#dt').on('draw.dt', function() {
            $('.order-checkbox').each(function() {
                const orderId = $(this).val();
                if (selectedOrders.includes(orderId)) {
                    $(this).prop('checked', true);
                }
            });
        });

        // Event handler untuk tombol detail cost
        $(document).on('click', '.btn-detail-cost', function(e) {
            e.preventDefault();
            const costsData = $(this).data('costs');
            const shipmentNumber = $(this).data('shipment');

            // Set shipment number
            $('#modalShipmentNumber').text(shipmentNumber);

            // Clear table body
            $('#costTableBody').empty();

            // Populate table with costs data
            if (costsData && costsData.length > 0) {
                costsData.forEach(function(cost, index) {
                    const row = `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${cost.component}</td>
                                <td>${cost.nominal}</td>
                            </tr>
                        `;
                    $('#costTableBody').append(row);
                });
            } else {
                $('#costTableBody').append('<tr><td colspan="3" class="text-center">Tidak ada data biaya On Charge</td></tr>');
            }

            // Show modal
            $('#modalDetailCost').modal('show');
        });

        // Event handler untuk tombol view files
        $(document).on('click', '.btn-view-files', function(e) {
            e.preventDefault();
            const orderId = $(this).data('order-id');
            const orderCode = $(this).data('order-code');

            // Set order code
            $('#galleryOrderCode').text(orderCode);

            // Show loading
            $('#galleryLoading').show();
            $('#fileGalleryContainer').hide();
            $('#galleryEmpty').hide();

            // Show modal
            $('#modalFileGallery').modal('show');

            // Load files via AJAX
            $.ajax({
                url: "{{ route('operational.return-do.get-files', ['orderId' => ':orderId']) }}".replace(':orderId', orderId),
                type: 'GET',
                success: function(response) {
                    $('#galleryLoading').hide();
                    
                    if (response.success && response.files.length > 0) {
                        $('#fileGalleryContainer').show().empty();
                        
                        response.files.forEach(function(file) {
                            const fileExt = file.name.split('.').pop().toLowerCase();
                            const isPdf = fileExt === 'pdf';
                            
                            let cardContent = '';
                            if (isPdf) {
                                cardContent = `
                                    <div class="text-center py-4">
                                        <i class="mdi mdi-file-pdf" style="font-size: 80px; color: #dc3545;"></i>
                                        <p class="mt-2 mb-0"><small>${file.name}</small></p>
                                    </div>
                                `;
                            } else {
                                cardContent = `
                                    <img src="${file.url}" class="card-img-top" alt="${file.name}" style="height: 200px; object-fit: cover;">
                                `;
                            }
                            
                            const card = `
                                <div class="col-md-3 col-sm-6">
                                    <div class="card">
                                        ${cardContent}
                                        <div class="card-body">
                                            <p class="card-text small mb-1"><i class="mdi mdi-clock-outline me-1"></i>${file.uploaded_at}</p>
                                            <a href="${file.url}" target="_blank" class="btn btn-sm btn-primary w-100">
                                                <i class="mdi mdi-download me-1"></i>Download
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            `;
                            $('#fileGalleryContainer').append(card);
                        });
                    } else {
                        $('#galleryEmpty').show();
                    }
                },
                error: function(xhr) {
                    $('#galleryLoading').hide();
                    $('#galleryEmpty').show();
                    console.error('Error loading files:', xhr);
                }
            });
        });

        // ========== ROLLBACK STATUS FUNCTIONALITY ==========
        $(document).on('click', '.rollback-btn', function() {
            const id = $(this).data('id');
            const shipment = $(this).data('shipment');

            swal({
                title: "Apakah Anda yakin?",
                text: `Ingin mengembalikan status pesanan ${shipment} ke Not Return DO?`,
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willRollback) => {
                if (willRollback) {
                    window.location.href =
                        "{{ route('operational.return-do.rollback-status', ':id') }}".replace(':id', id);
                }
            });
        });

    });
</script>
@endpush